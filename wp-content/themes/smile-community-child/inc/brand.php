<?php
/**
 * Brand assets: the Smile Community Centre logo and site icons.
 *
 * The logo ships with the theme (assets/images/brand/) in several sizes, so it is
 * always sharp on normal, Retina and Full HD screens and never depends on an
 * old Media Library upload. Turn it off to use the WordPress "Site Logo" instead:
 *   add_filter( 'smile_use_brand_logo', '__return_false' );
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of a file in the brand folder.
 *
 * @param string $file File name.
 * @return string
 */
function smile_brand_url( $file ) {
	return SMILE_THEME_URI . '/assets/images/brand/' . $file . '?v=' . rawurlencode( SMILE_THEME_VERSION );
}

/**
 * Logo markup: WebP with PNG fallback, 1x / 2x / 3x sources, fixed dimensions
 * (no layout shift). The logo (Logo Kit v1.2, primary full-colour lockup) is 2.68 : 1.
 * The footer sits on Forest Green, so it uses the approved reversed colourway.
 *
 * @param string $context header|footer.
 * @return string
 */
function smile_brand_logo( $context = 'header' ) {
	if ( ! apply_filters( 'smile_use_brand_logo', true ) ) {
		return has_custom_logo() ? get_custom_logo() : '<a class="smile-header__name" href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
	}
	// Logo Kit minimum for the full lockup with tagline is 150 px wide: 60 px tall = 161 px.
	$height = 'footer' === $context ? 72 : 60;
	$width  = (int) round( $height * 3820 / 1433 );
	$alt    = __( 'Smile Community Centre: a smile today, a brighter tomorrow', 'smile-community' );
	$file   = 'footer' === $context ? 'smile-logo-reversed' : 'smile-logo';
	$webp   = smile_brand_url( $file . '-120.webp' ) . ' 2x, ' . smile_brand_url( $file . '-180.webp' ) . ' 3x';
	$png    = smile_brand_url( $file . '-60.png' ) . ' 1x, ' . smile_brand_url( $file . '-120.png' ) . ' 2x, ' . smile_brand_url( $file . '-180.png' ) . ' 3x';

	$out  = '<a class="smile-brand smile-brand--' . esc_attr( $context ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home" aria-label="' . esc_attr__( 'Smile Community Centre home', 'smile-community' ) . '">';
	$out .= '<picture>';
	$out .= '<source type="image/webp" srcset="' . esc_attr( smile_brand_url( $file . '-120.webp' ) . ' 1x, ' . $webp ) . '">';
	$out .= '<img class="smile-brand__img" src="' . esc_url( smile_brand_url( $file . '-120.png' ) ) . '" srcset="' . esc_attr( $png ) . '" width="' . (int) $width . '" height="' . (int) $height . '" alt="' . esc_attr( $alt ) . '"' . ( 'header' === $context ? ' fetchpriority="high"' : ' loading="lazy"' ) . ' decoding="async">';
	$out .= '</picture></a>';
	return $out;
}

/**
 * Site icons (browser tab, phone home screen). Replaces WordPress's own icon tags
 * so the new emblem is used everywhere.
 */
function smile_brand_icons() {
	if ( ! apply_filters( 'smile_use_brand_logo', true ) ) {
		return;
	}
	echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( smile_brand_url( 'icon-32.png' ) ) . '">' . "\n";
	echo '<link rel="icon" type="image/png" sizes="192x192" href="' . esc_url( smile_brand_url( 'icon-192.png' ) ) . '">' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( smile_brand_url( 'icon-180.png' ) ) . '">' . "\n";
	echo '<meta name="theme-color" content="#0B4021">' . "\n";
}
add_action(
	'init',
	function () {
		if ( apply_filters( 'smile_use_brand_logo', true ) ) {
			remove_action( 'wp_head', 'wp_site_icon', 99 );
			remove_action( 'login_head', 'wp_site_icon', 99 );
		}
	}
);
add_action( 'wp_head', 'smile_brand_icons', 3 );
add_action( 'login_head', 'smile_brand_icons', 3 );

/**
 * New logo on the WordPress sign-in screen.
 */
function smile_brand_login_logo() {
	if ( ! apply_filters( 'smile_use_brand_logo', true ) ) {
		return;
	}
	echo '<style>#login h1 a{background-image:url(' . esc_url( smile_brand_url( 'smile-logo-180.png' ) ) . ');background-size:contain;background-position:center;width:300px;height:112px;}</style>' . "\n";
}
add_action( 'login_enqueue_scripts', 'smile_brand_login_logo' );
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return function_exists( 'smile_setting' ) ? smile_setting( 'org_name', get_bloginfo( 'name' ) ) : get_bloginfo( 'name' ); } );

/**
 * Full-resolution logo URL (for schema and sharing).
 *
 * @return string
 */
function smile_brand_logo_url() {
	return SMILE_THEME_URI . '/assets/images/brand/smile-logo.png';
}
