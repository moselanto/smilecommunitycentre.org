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
 * Enqueue styles and scripts.
 */
function smile_theme_assets() {
	if ( apply_filters( 'smile_load_google_fonts', true ) ) {
		wp_enqueue_style(
			'smile-fonts',
			'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		);
	}
	$css = SMILE_THEME_DIR . '/assets/css/main.css';
	$js  = SMILE_THEME_DIR . '/assets/js/main.js';

	wp_enqueue_style(
		'smile-community',
		SMILE_THEME_URI . '/assets/css/main.css',
		array( 'hello-elementor' ),
		file_exists( $css ) ? (string) filemtime( $css ) : SMILE_THEME_VERSION
	);

	wp_enqueue_script(
		'smile-community',
		SMILE_THEME_URI . '/assets/js/main.js',
		array(),
		file_exists( $js ) ? (string) filemtime( $js ) : SMILE_THEME_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_localize_script(
		'smile-community',
		'smileTheme',
		array(
			'copied'   => __( 'Copied', 'smile-community' ),
			'copyFail' => __( 'Press and hold to copy', 'smile-community' ),
			'menuOpen' => __( 'Open menu', 'smile-community' ),
			'menuClose'=> __( 'Close menu', 'smile-community' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'smile_theme_assets', 20 );

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
