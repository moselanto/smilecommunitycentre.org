<?php
/**
 * Speed: first-screen photo on every page, lighter uploads, fewer front-end requests,
 * and Elementor's own speed settings kept switched on after every theme update.
 *
 * Target: main content and its photo visible in 1 to 3 seconds on a mobile connection
 * with page caching on. Each part can be switched off with the filter named beside it.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Inner pages: start downloading the banner photo at once (smile_preload_page_hero)
 * ---------------------------------------------------------------------- */

/**
 * Attachment ID (or URL) of the first section's background photo on an Elementor page.
 *
 * @param int $post_id Page ID.
 * @return array { id:int, url:string }
 */
function smile_page_hero_image( $post_id ) {
	$cache = wp_cache_get( 'hero_' . $post_id, 'smile' );
	if ( is_array( $cache ) ) {
		return $cache;
	}
	$out  = array( 'id' => 0, 'url' => '' );
	$data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( is_array( $data ) && isset( $data[0]['settings']['background_image'] ) && is_array( $data[0]['settings']['background_image'] ) ) {
		$bg         = $data[0]['settings']['background_image'];
		$out['id']  = isset( $bg['id'] ) ? absint( $bg['id'] ) : 0;
		$out['url'] = isset( $bg['url'] ) && 0 === strpos( (string) $bg['url'], 'http' ) ? (string) $bg['url'] : '';
	}
	wp_cache_set( 'hero_' . $post_id, $out, 'smile', HOUR_IN_SECONDS );
	return $out;
}

/**
 * Preload the banner photo of the current page (home uses the slider preload instead).
 */
function smile_preload_page_hero() {
	if ( !apply_filters( 'smile_preload_page_hero', true ) || is_front_page() || !is_singular() ) {
		return;
	}
	$id = 0;
	$url = '';
	if ( is_singular( 'post' ) ) {
		$id = (int) get_post_thumbnail_id();
	} else {
		$img = smile_page_hero_image( get_queried_object_id() );
		$id  = $img['id'];
		$url = $img['url'];
	}
	if ( $id ) {
		$size   = is_singular( 'post' ) ? 'large' : 'smile-hero';
		$src    = wp_get_attachment_image_url( $id, $size );
		$srcset = wp_get_attachment_image_srcset( $id, $size );
		if ( $src ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '"' . ( $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : '' ) . ' fetchpriority="high">' . "\n";
			return;
		}
	}
	if ( $url ) {
		echo '<link rel="preload" as="image" href="' . esc_url( $url ) . '" fetchpriority="high">' . "\n";
	}
}
add_action( 'wp_head', 'smile_preload_page_hero', 2 );

/**
 * Forget the cached banner photo when a page is saved.
 *
 * @param int $post_id Post ID.
 */
function smile_forget_page_hero( $post_id ) {
	wp_cache_delete( 'hero_' . $post_id, 'smile' );
}
add_action( 'save_post', 'smile_forget_page_hero' );

/* -------------------------------------------------------------------------
 * 2. Lighter photos for every new upload (smile_light_uploads)
 * ---------------------------------------------------------------------- */

/**
 * Phone and camera photos are scaled down to 1920 px on the long side when uploaded
 * (the site never shows them larger), instead of WordPress's 2560 px.
 *
 * @param int $threshold Pixels.
 * @return int
 */
function smile_big_image_threshold( $threshold ) {
	return apply_filters( 'smile_light_uploads', true ) ? 1920 : $threshold;
}
add_filter( 'big_image_size_threshold', 'smile_big_image_threshold' );

/**
 * PNG photos also get WebP copies (JPEG is handled in images.php). WebP keeps transparency.
 *
 * @param array $formats Map.
 * @return array
 */
function smile_png_webp_output( $formats ) {
	if ( apply_filters( 'smile_light_uploads', true ) && apply_filters( 'smile_webp_sizes', true ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/png'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'smile_png_webp_output', 11 );

/* -------------------------------------------------------------------------
 * 3. Fewer requests for visitors (smile_trim_frontend)
 * ---------------------------------------------------------------------- */

/**
 * Drop files visitors never need: the dashboard icon font, the embed helper
 * and the heartbeat script (all kept for signed-in editors).
 */
function smile_trim_frontend() {
	if ( is_admin() || !apply_filters( 'smile_trim_frontend', true ) || is_user_logged_in() ) {
		return;
	}
	wp_dequeue_style( 'dashicons' );
	wp_deregister_script( 'wp-embed' );
	wp_deregister_script( 'heartbeat' );
}
add_action( 'wp_enqueue_scripts', 'smile_trim_frontend', 100 );

remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );

/* -------------------------------------------------------------------------
 * 4. Elementor speed settings, re-applied after each theme update (smile_elementor_speed)
 * ---------------------------------------------------------------------- */

/**
 * Keep Elementor's speed options on. Runs once per theme version, for administrators.
 */
function smile_elementor_speed_settings() {
	if ( !apply_filters( 'smile_elementor_speed', true ) || !current_user_can( 'manage_options' ) || get_option( 'smile_speed_applied' ) === SMILE_THEME_VERSION ) {
		return;
	}
	$options = array(
		'elementor_css_print_method'               => 'external', // CSS files the browser can cache.
		'elementor_optimized_image_loading'        => '1',        // Correct lazy loading and fetch priority.
		'elementor_optimized_gutenberg_loading'    => '1',
		'elementor_font_display'                   => 'swap',
		'elementor_google_font'                    => '0',
		'elementor_lazy_load_background_images'    => '1',
		'elementor_experiment-e_font_icon_svg'     => 'active',
		'elementor_experiment-e_optimized_markup'  => 'active',
		'elementor_experiment-e_element_cache'     => 'active',
		'elementor_experiment-e_local_google_fonts' => 'active',
	);
	foreach ( $options as $key => $value ) {
		update_option( $key, $value );
	}
	update_option( 'smile_speed_applied', SMILE_THEME_VERSION, false );
	if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
add_action( 'admin_init', 'smile_elementor_speed_settings' );
