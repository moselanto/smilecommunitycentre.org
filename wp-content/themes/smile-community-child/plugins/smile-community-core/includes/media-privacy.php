<?php
/**
 * Photo privacy: strip EXIF/GPS metadata from uploaded JPEG/WebP originals and
 * warn editors about identifying filenames. Children's locations must never leak
 * through image metadata.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Strip metadata after upload or sideload.
 *
 * @param array $upload Upload data (file, url, type).
 * @return array
 */
function smile_core_strip_image_metadata( $upload ) {
	if ( empty( $upload['file'] ) || empty( $upload['type'] ) ) {
		return $upload;
	}
	if ( ! in_array( $upload['type'], array( 'image/jpeg', 'image/webp', 'image/png' ), true ) ) {
		return $upload;
	}
	$file = $upload['file'];

	if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
		try {
			$im = new Imagick( $file );
			if ( method_exists( $im, 'autoOrient' ) ) {
				$im->autoOrient();
			}
			$profiles = $im->getImageProfiles( 'icc', true );
			$im->stripImage();
			if ( ! empty( $profiles['icc'] ) ) {
				$im->profileImage( 'icc', $profiles['icc'] ); // Keep colour profile only.
			}
			$im->writeImage( $file );
			$im->clear();
			return $upload;
		} catch ( Exception $e ) {
			// Fall through to GD.
			unset( $e );
		}
	}

	if ( 'image/jpeg' === $upload['type'] && function_exists( 'imagecreatefromjpeg' ) ) {
		$exif = function_exists( 'exif_read_data' ) ? @exif_read_data( $file ) : array(); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$img  = @imagecreatefromjpeg( $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( $img ) {
			if ( ! empty( $exif['Orientation'] ) ) {
				$map = array( 3 => 180, 6 => -90, 8 => 90 );
				if ( isset( $map[ $exif['Orientation'] ] ) ) {
					$rot = imagerotate( $img, $map[ $exif['Orientation'] ], 0 );
					if ( $rot ) {
						imagedestroy( $img );
						$img = $rot;
					}
				}
			}
			imagejpeg( $img, $file, 88 ); // GD never writes EXIF.
			imagedestroy( $img );
		}
	}
	return $upload;
}
add_filter( 'wp_handle_upload', 'smile_core_strip_image_metadata' );
add_filter( 'wp_handle_sideload', 'smile_core_strip_image_metadata' );

/**
 * Do not copy camera/caption metadata into the attachment record.
 */
add_filter( 'wp_read_image_metadata', function ( $meta ) {
	if ( is_array( $meta ) ) {
		foreach ( array( 'camera', 'caption', 'copyright', 'credit', 'title', 'keywords' ) as $k ) {
			if ( isset( $meta[ $k ] ) ) {
				$meta[ $k ] = is_array( $meta[ $k ] ) ? array() : '';
			}
		}
	}
	return $meta;
} );

/**
 * Reminder on the Media screen.
 */
function smile_core_media_notice() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'upload', 'media' ), true ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Safeguarding reminder:', 'smile-core' ) . '</strong> ' . esc_html__( 'Only upload photos the centre has permission to publish. Rename files before uploading (for example feeding-programme-2026-01.jpg, never a child\'s name). Do not put children\'s full names, schools or personal circumstances in titles, captions or alt text. Location data is removed automatically.', 'smile-core' ) . '</p></div>';
}
add_action( 'admin_notices', 'smile_core_media_notice' );
