<?php
/**
 * Lean asset mode (2.47.0, opt-in): load less JavaScript and CSS on the public site.
 *
 * Turn on for testing on staging by adding this line to wp-config.php:
 *   define( 'SMILE_LEAN_ASSETS', true );
 * or with a filter: add_filter( 'smile_lean_assets', '__return_true' );
 *
 * What it does when on (only for visitors who are not signed in, never in wp-admin
 * or the Elementor editor/preview):
 *
 * 1. Elementor frontend JavaScript is skipped on pages that do not need it.
 *    The site's pages use only static Elementor widgets (heading, text, button,
 *    image, shortcode) plus the theme's own header/footer widgets, which work
 *    without Elementor's scripts. The page, its header and its footer template are
 *    checked element by element; if anything needs Elementor's scripts (accordion,
 *    tabs, carousels, entrance animations, motion effects, sticky, lightbox,
 *    background slideshows or videos, HTML widgets, or any widget not on the
 *    allow-list) the scripts load as normal. Today that is only the FAQ page
 *    (accordion). jQuery is skipped too when nothing else on the page needs it.
 *
 * 2. Critical CSS is inlined and the theme's large stylesheets load without
 *    blocking the first paint. assets/build/critical-*.min.css (built by
 *    scripts/build-critical-css.py) holds every rule for what is on screen before
 *    scrolling: base styles, fonts, top bar, header and menu, buttons, slider and
 *    page banner. The full theme stylesheets then load in the background, with a
 *    <noscript> fallback. Elementor's own CSS stays render-blocking on purpose: it
 *    lays out the page sections, so loading it late would make the layout jump.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether lean asset mode applies to this request.
 *
 * @return bool
 */
function smile_lean_assets_on() {
	if ( is_admin() || is_user_logged_in() || wp_doing_ajax() || is_customize_preview() ) {
		return false;
	}
	if ( function_exists( 'smile_is_elementor_preview' ) && smile_is_elementor_preview() ) {
		return false;
	}
	$on = defined( 'SMILE_LEAN_ASSETS' ) && SMILE_LEAN_ASSETS;
	return (bool) apply_filters( 'smile_lean_assets', $on );
}

/* -------------------------------------------------------------------------
 * 1. Elementor frontend JavaScript only where it is needed
 * ---------------------------------------------------------------------- */

/**
 * Widgets that render fully on the server and need no Elementor JavaScript.
 *
 * @return string[]
 */
function smile_lean_static_widgets() {
	return (array) apply_filters(
		'smile_lean_static_widgets',
		array( 'heading', 'text-editor', 'button', 'image', 'shortcode', 'spacer', 'divider', 'icon', 'icon-list', 'google_maps' )
	);
}

/**
 * Does one element (and its children) need Elementor's frontend scripts?
 *
 * @param array $el Element data from _elementor_data.
 * @return bool
 */
function smile_lean_element_needs_js( $el ) {
	if ( ! is_array( $el ) ) {
		return false;
	}
	$settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
	foreach ( $settings as $key => $value ) {
		if ( '' === $value || null === $value || array() === $value || 'none' === $value ) {
			continue;
		}
		// Entrance animations, motion effects, sticky, parallax.
		if ( preg_match( '/(^|_)animation($|_)|motion_fx|^sticky|parallax/', $key ) && 'no' !== $value ) {
			return true;
		}
		// Background slideshow or video.
		if ( preg_match( '/background(_hover)?_background$/', $key ) && in_array( $value, array( 'slideshow', 'video' ), true ) ) {
			return true;
		}
		// Image opens in Elementor's lightbox.
		if ( ( 'open_lightbox' === $key && 'no' !== $value ) || ( 'link_to' === $key && 'file' === $value ) ) {
			return true;
		}
	}
	if ( isset( $el['elType'] ) && 'widget' === $el['elType'] ) {
		$type = isset( $el['widgetType'] ) ? (string) $el['widgetType'] : '';
		if ( 0 !== strpos( $type, 'smile-' ) && ! in_array( $type, smile_lean_static_widgets(), true ) ) {
			return true; // Unknown or interactive widget: keep the scripts.
		}
	}
	if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
		foreach ( $el['elements'] as $child ) {
			if ( smile_lean_element_needs_js( $child ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Does this request need Elementor's frontend scripts?
 * Checks the current page plus the Elementor header and footer templates.
 *
 * @return bool
 */
function smile_lean_needs_elementor_js() {
	$ids = array();
	if ( is_singular() ) {
		$ids[] = (int) get_queried_object_id();
	} elseif ( ! is_front_page() ) {
		return true; // Archives, search, 404: keep the default behaviour.
	}
	if ( function_exists( 'smile_builder_resolve' ) ) {
		$ids[] = (int) smile_builder_resolve( 'header' );
		$ids[] = (int) smile_builder_resolve( 'footer' );
	}
	foreach ( array_filter( array_unique( $ids ) ) as $id ) {
		$raw = get_post_meta( $id, '_elementor_data', true );
		if ( ! $raw ) {
			continue;
		}
		$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
		if ( ! is_array( $data ) ) {
			return true; // Cannot read it: be safe.
		}
		foreach ( $data as $el ) {
			if ( smile_lean_element_needs_js( $el ) ) {
				return true;
			}
		}
	}
	return (bool) apply_filters( 'smile_lean_needs_elementor_js', false );
}

/**
 * Remove Elementor's frontend scripts (and jQuery when unused) just before the
 * footer scripts print. Elementor enqueues late, so this runs at wp_footer 19
 * (footer scripts print at 20).
 */
function smile_lean_trim_scripts() {
	if ( ! smile_lean_assets_on() || smile_lean_needs_elementor_js() ) {
		return;
	}
	$drop = array( 'elementor-frontend', 'elementor-frontend-modules', 'elementor-webpack-runtime', 'jquery-ui-core' );
	foreach ( $drop as $handle ) {
		wp_dequeue_script( $handle );
	}
	// Keep jQuery if any other queued script depends on it.
	$scripts = wp_scripts();
	foreach ( $scripts->queue as $handle ) {
		if ( in_array( $handle, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) {
			continue;
		}
		$deps = isset( $scripts->registered[ $handle ] ) ? (array) $scripts->registered[ $handle ]->deps : array();
		if ( array_intersect( $deps, array( 'jquery', 'jquery-core' ) ) ) {
			return;
		}
	}
	wp_dequeue_script( 'jquery' );
}
add_action( 'wp_footer', 'smile_lean_trim_scripts', 19 );

/* -------------------------------------------------------------------------
 * 2. Critical CSS inline, theme stylesheets non-blocking
 * ---------------------------------------------------------------------- */

/**
 * Theme stylesheet handles that may load without blocking the first paint.
 *
 * @return string[]
 */
function smile_lean_async_styles() {
	return (array) apply_filters( 'smile_lean_async_styles', array( 'smile-community', 'smile-home', 'smile-slider' ) );
}

/**
 * Path of the critical CSS file for this request, or '' when none.
 *
 * @return string
 */
function smile_lean_critical_file() {
	$name = function_exists( 'smile_needs_full_css' ) && ! smile_needs_full_css() ? 'critical-inner.min.css' : 'critical-home.min.css';
	$file = SMILE_THEME_DIR . '/assets/build/' . $name;
	return is_readable( $file ) ? $file : '';
}

/**
 * Print the critical CSS at the top of <head>.
 */
function smile_lean_print_critical() {
	if ( ! smile_lean_assets_on() || ! smile_lean_critical_file() ) {
		return;
	}
	$css = (string) file_get_contents( smile_lean_critical_file() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	// The file is built from the theme's own stylesheets; strip any "</style" so it can never close the tag early.
	$css = str_ireplace( '</style', '', $css );
	echo '<style id="smile-critical-css">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme CSS, see above.
}
add_action( 'wp_head', 'smile_lean_print_critical', 1 );

/**
 * Load the theme stylesheets without blocking rendering: preload, then switch to a
 * stylesheet once downloaded. A <noscript> copy keeps them working without JavaScript.
 *
 * @param string $tag    Link tag.
 * @param string $handle Style handle.
 * @param string $href   Stylesheet URL.
 * @param string $media  Media attribute.
 * @return string
 */
function smile_lean_style_tag( $tag, $handle, $href, $media ) {
	if ( ! in_array( $handle, smile_lean_async_styles(), true ) || ! smile_lean_assets_on() || ! smile_lean_critical_file() ) {
		return $tag;
	}
	$media = $media ? $media : 'all';
	return sprintf(
		'<link rel="preload" as="style" id="%1$s-css" href="%2$s" media="%3$s" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n" . '<noscript>%4$s</noscript>' . "\n",
		esc_attr( $handle ),
		esc_url( $href ),
		esc_attr( $media ),
		trim( $tag )
	);
}
add_filter( 'style_loader_tag', 'smile_lean_style_tag', 10, 4 );
