<?php
/**
 * Theme setup: supports, menus, assets.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports and menu locations.
 */
function smile_theme_setup() {
	load_child_theme_textdomain( 'smile-community', SMILE_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// Hello Elementor registers menu-1 (header) and menu-2 (footer). Add two footer columns.
	register_nav_menus(
		array(
			'smile-footer-programmes' => __( 'Footer: Programmes', 'smile-community' ),
			'smile-footer-legal'      => __( 'Footer: Legal & Safeguarding', 'smile-community' ),
		)
	);

	add_image_size( 'smile-card', 720, 480, true );
	add_image_size( 'smile-hero', 1920, 1080, true );
	add_image_size( 'smile-hero-md', 1280, 720, true );
	add_image_size( 'smile-hero-sm', 768, 432, true );
}
add_action( 'after_setup_theme', 'smile_theme_setup', 20 );

/**
 * Hello Elementor must keep displaying a header and footer (our template-parts
 * override its markup). Its own header/footer stylesheet is not needed.
 */
add_filter( 'hello_elementor_header_footer', '__return_true' );
add_filter( 'hello_elementor_enqueue_theme_style', '__return_true' );
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_style( 'hello-elementor-header-footer' );
	},
	30
);

/**
 * Is this a page that uses the home components (slider, home sections)?
 *
 * @return bool
 */
function smile_is_home_layout() {
	return is_front_page();
}

/**
 * Asset URL: minified build when available (and SCRIPT_DEBUG is off), else source.
 *
 * @param string $rel  Path relative to the theme, e.g. assets/css/main.css.
 * @param string $min  Minified path.
 * @return array [url, version]
 */
function smile_asset( $rel, $min ) {
	$use_min = ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) && file_exists( SMILE_THEME_DIR . '/' . $min );
	$path    = $use_min ? $min : $rel;
	$file    = SMILE_THEME_DIR . '/' . $path;
	return array( SMILE_THEME_URI . '/' . $path, file_exists( $file ) ? SMILE_THEME_VERSION . '.' . filemtime( $file ) : SMILE_THEME_VERSION );
}

/**
 * Enqueue styles and scripts (lean: home-only assets load on the home page only).
 */
function smile_theme_assets() {
	if ( apply_filters( 'smile_load_google_fonts', true ) ) {
		// Two families, five files. Loaded without blocking first paint (see smile_async_fonts).
		wp_enqueue_style(
			'smile-fonts',
			'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,500;1,6..72,500&family=Figtree:wght@400;500;600&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		);
		if ( is_page( array( 'about-us', 'our-founder', 'faq-page' ) ) ) {
			wp_enqueue_style( 'smile-fonts-hand', 'https://fonts.googleapis.com/css2?family=Caveat:wght@600&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}

	list( $css_url, $css_ver ) = smile_asset( 'assets/css/main.css', 'assets/build/main.min.css' );
	wp_enqueue_style( 'smile-community', $css_url, array(), $css_ver );

	list( $js_url, $js_ver ) = smile_asset( 'assets/js/main.js', 'assets/build/main.min.js' );
	wp_enqueue_script( 'smile-community', $js_url, array(), $js_ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	// Section components (programme cards, timeline, community, related rows) are used on many pages,
	// so their styles load everywhere; only the slider script is home-only.
	list( $h_url, $h_ver ) = smile_asset( 'assets/css/home.css', 'assets/build/home.min.css' );
	wp_enqueue_style( 'smile-home', $h_url, array( 'smile-community' ), $h_ver );
	if ( ! file_exists( SMILE_THEME_DIR . '/assets/build/home.min.css' ) || ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
		wp_enqueue_style( 'smile-slider', SMILE_THEME_URI . '/assets/css/slider.css', array( 'smile-community' ), SMILE_THEME_VERSION );
	}
	if ( smile_is_home_layout() ) {
		list( $s_url, $s_ver ) = smile_asset( 'assets/js/slider.js', 'assets/build/slider.min.js' );
		wp_enqueue_script( 'smile-slider', $s_url, array(), $s_ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	wp_localize_script(
		'smile-community',
		'smileTheme',
		array(
			'copied'    => __( 'Copied', 'smile-community' ),
			'copyFail'  => __( 'Press and hold to copy', 'smile-community' ),
			'menuOpen'  => __( 'Open menu', 'smile-community' ),
			'menuClose' => __( 'Close menu', 'smile-community' ),
		)
	);

	// Not needed with the Smile header/footer and Elementor pages.
	wp_dequeue_style( 'hello-elementor-theme-style' );
	wp_dequeue_script( 'hello-theme-frontend' );
	if ( is_page() || is_front_page() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'smile_theme_assets', 20 );

/**
 * Load Google Fonts without blocking rendering (text shows immediately in fallback fonts).
 *
 * @param string $tag    Link tag.
 * @param string $handle Handle.
 * @return string
 */
function smile_async_fonts( $tag, $handle ) {
	if ( in_array( $handle, array( 'smile-fonts', 'smile-fonts-hand' ), true ) ) {
		$noscript = '<noscript>' . $tag . '</noscript>';
		$tag      = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $tag );
		return $tag . $noscript;
	}
	return $tag;
}
add_filter( 'style_loader_tag', 'smile_async_fonts', 10, 2 );

/**
 * Drop jQuery Migrate on the front end (Elementor and the theme do not need it).
 *
 * @param WP_Scripts $scripts Scripts.
 */
function smile_drop_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
}
add_action( 'wp_default_scripts', 'smile_drop_jquery_migrate' );

/**
 * Preload the first slider photo (the largest element on the home page) so it starts downloading at once.
 */
function smile_preload_lcp() {
	if ( ! smile_is_home_layout() || ! function_exists( 'smile_slider_slides' ) ) {
		return;
	}
	$slides = smile_slider_slides();
	if ( empty( $slides[0]['image'] ) ) {
		return;
	}
	$id     = (int) $slides[0]['image'];
	$src    = wp_get_attachment_image_url( $id, 'smile-hero' );
	$srcset = wp_get_attachment_image_srcset( $id, 'smile-hero' );
	if ( ! $src ) {
		return;
	}
	echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '"' . ( $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="100vw"' : '' ) . ' fetchpriority="high">' . "\n";
}
add_action( 'wp_head', 'smile_preload_lcp', 2 );

/**
 * Remove head clutter that costs bytes and requests on every page.
 */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_filter( 'emoji_svg_url', '__return_false' );

/**
 * Preconnect to Google Fonts.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Relation.
 * @return array
 */
function smile_theme_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && apply_filters( 'smile_load_google_fonts', true ) ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'smile_theme_resource_hints', 10, 2 );

/**
 * Editor styles so Gutenberg long-form posts look like the front end.
 */
function smile_theme_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
}
add_action( 'after_setup_theme', 'smile_theme_editor_styles', 30 );

/**
 * Block editor colour palette matching the design tokens.
 */
function smile_theme_palette() {
	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Teal', 'smile-community' ), 'slug' => 'smile-teal', 'color' => '#176B5B' ),
			array( 'name' => __( 'Forest', 'smile-community' ), 'slug' => 'smile-forest', 'color' => '#164B3F' ),
			array( 'name' => __( 'Sunshine', 'smile-community' ), 'slug' => 'smile-sun', 'color' => '#F4C95D' ),
			array( 'name' => __( 'Cream', 'smile-community' ), 'slug' => 'smile-cream', 'color' => '#FFF9EF' ),
			array( 'name' => __( 'White', 'smile-community' ), 'slug' => 'smile-white', 'color' => '#FFFFFF' ),
			array( 'name' => __( 'Charcoal', 'smile-community' ), 'slug' => 'smile-ink', 'color' => '#24312D' ),
			array( 'name' => __( 'Grey green', 'smile-community' ), 'slug' => 'smile-muted', 'color' => '#6B7872' ),
		)
	);
}
add_action( 'after_setup_theme', 'smile_theme_palette', 30 );

/**
 * Lazy-load and async-decode content images by default (WordPress core handles most cases).
 */
add_filter( 'wp_img_tag_add_decoding_attr', '__return_true' );

/**
 * Remove emoji scripts (not needed, saves a request on slow connections).
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );


/**
 * Speed: Elementor's own Google Fonts (Roboto etc.) are never shown, because the
 * theme sets Newsreader and Figtree everywhere. Skipping them saves several requests.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );
