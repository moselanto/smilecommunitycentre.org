<?php
/**
 * Photo quality: gentle enhancement of imported photos (upscale small
 * originals with Lanczos, mild contrast and colour, light sharpening), and
 * WebP output for resized copies where the server supports it.
 *
 * Runs only on photos imported by the setup wizard (meta _smile_source),
 * once per photo, and keeps a backup of the untouched original.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

define( 'SMILE_ENHANCE_VERSION', '1' );

/**
 * Serve resized JPEG copies as WebP when supported.
 *
 * @param array $formats Map.
 * @return array
 */
function smile_images_webp_output( $formats ) {
	if ( apply_filters( 'smile_webp_sizes', true ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'smile_images_webp_output' );

/**
 * Quality for resized copies.
 *
 * @param int    $quality Quality.
 * @param string $mime    Mime.
 * @return int
 */
function smile_images_quality( $quality, $mime = '' ) {
	return in_array( $mime, array( 'image/jpeg', 'image/webp' ), true ) ? 80 : $quality;
}
add_filter( 'wp_editor_set_quality', 'smile_images_quality', 10, 2 );

/**
 * Enhance one attachment in place and regenerate its sizes.
 *
 * @param int $id Attachment ID.
 * @return string Message.
 */
function smile_enhance_attachment( $id ) {
	if ( get_post_meta( $id, '_smile_enhanced', true ) === SMILE_ENHANCE_VERSION ) {
		return 'Photo already enhanced: ' . get_the_title( $id );
	}
	$file = get_attached_file( $id );
	if ( ! $file || ! file_exists( $file ) ) {
		return 'FAILED enhancing ' . $id . ': file missing';
	}
	$mime = get_post_mime_type( $id );
	if ( ! in_array( $mime, array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
		return 'Skipped (not a photo): ' . get_the_title( $id );
	}
	if ( 'logo' === (string) get_post_meta( $id, '_smile_img_key', true ) ) {
		update_post_meta( $id, '_smile_enhanced', SMILE_ENHANCE_VERSION );
		return 'Logo left untouched';
	}

	// Keep the untouched original once.
	$backup = preg_replace( '/(\.[a-z0-9]+)$/i', '-original$1', $file );
	if ( ! file_exists( $backup ) ) {
		copy( $file, $backup ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$min_width = 1920;
	$done      = false;

	if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
		try {
			$im = new Imagick( $backup );
			if ( method_exists( $im, 'autoOrient' ) ) {
				$im->autoOrient();
			}
			$w = $im->getImageWidth();
			$h = $im->getImageHeight();
			if ( $w < $min_width ) {
				$scale = min( 2.2, $min_width / max( 1, $w ) );
				$im->resizeImage( (int) round( $w * $scale ), (int) round( $h * $scale ), Imagick::FILTER_LANCZOS, 0.9 );
				$w = $im->getImageWidth();
				$h = $im->getImageHeight();
			}
			$range = $im->getQuantumRange();
			$q     = $range['quantumRangeLong'];
			$px    = $w * $h;
			$im->contrastStretchImage( 0.003 * $px, 0.997 * $px );
			$im->sigmoidalContrastImage( true, 2.2, 0.5 * $q );
			$im->modulateImage( 101, 112, 100 );
			$im->unsharpMaskImage( 0, 1.0, 0.7, 0.02 );
			$im->stripImage();
			if ( 'image/png' !== $mime ) {
				$im->setImageCompressionQuality( 88 );
				$im->setInterlaceScheme( Imagick::INTERLACE_PLANE );
			}
			$im->writeImage( $file );
			$im->clear();
			$done = true;
		} catch ( Exception $e ) {
			$done = false;
		}
	}

	if ( ! $done && function_exists( 'imagecreatefromstring' ) ) {
		$src = @imagecreatefromstring( (string) file_get_contents( $backup ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		if ( $src ) {
			$w = imagesx( $src );
			$h = imagesy( $src );
			if ( $w < $min_width ) {
				$scale = min( 2.2, $min_width / max( 1, $w ) );
				$nw    = (int) round( $w * $scale );
				$nh    = (int) round( $h * $scale );
				$dst   = imagecreatetruecolor( $nw, $nh );
				imagecopyresampled( $dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h );
				imagedestroy( $src );
				$src = $dst;
			}
			imagefilter( $src, IMG_FILTER_CONTRAST, -6 );
			imagefilter( $src, IMG_FILTER_BRIGHTNESS, 3 );
			imageconvolution( $src, array( array( 0, -1, 0 ), array( -1, 8, -1 ), array( 0, -1, 0 ) ), 4, 0 );
			if ( 'image/png' === $mime ) {
				imagepng( $src, $file, 6 );
			} elseif ( 'image/webp' === $mime && function_exists( 'imagewebp' ) ) {
				imagewebp( $src, $file, 86 );
			} else {
				imagejpeg( $src, $file, 88 );
			}
			imagedestroy( $src );
			$done = true;
		}
	}

	if ( ! $done ) {
		return 'FAILED enhancing ' . get_the_title( $id ) . ': no image library on server';
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_generate_attachment_metadata( $id, $file );
	if ( $meta ) {
		wp_update_attachment_metadata( $id, $meta );
	}
	update_post_meta( $id, '_smile_enhanced', SMILE_ENHANCE_VERSION );
	return 'Photo enhanced: ' . get_the_title( $id );
}

/**
 * IDs of imported photos still to enhance.
 *
 * @return int[]
 */
function smile_enhance_queue() {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/webp' ),
			'fields'         => 'ids',
			'posts_per_page' => 300,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
				array( 'key' => '_smile_source', 'compare' => 'EXISTS' ),
				array( 'key' => '_smile_enhanced', 'compare' => 'NOT EXISTS' ),
			),
		)
	);
	return array_map( 'intval', $ids );
}
