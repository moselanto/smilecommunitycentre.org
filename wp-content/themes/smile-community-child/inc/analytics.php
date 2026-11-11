<?php
/**
 * Google Analytics 4 and Google Tag Manager.
 *
 * GA4 (gtag.js) and the GTM container are printed by the theme, so tracking
 * does not depend on any SEO plugin. Logged-in administrators and editors are
 * not tracked. While SEOPress is still active (it printed these tags before the
 * Rank Math switch in 2.49.0) the theme stays silent to avoid double counting.
 *
 * IDs are public identifiers, not secrets. Change them with the filters
 * smile_ga4_id / smile_gtm_id (return '' to switch one off), or in
 * Smile Centre > Centre details (ga4_id, gtm_id) when those fields exist.
 *
 * GA4 is loaded directly, not through GTM: the GTM container must NOT contain
 * a GA4 Configuration/Google tag for G-DJQ2PBW3YN, or page views count twice.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * GA4 measurement ID.
 *
 * @return string
 */
function smile_ga4_id() {
	$id = (string) apply_filters( 'smile_ga4_id', smile_setting( 'ga4_id', 'G-DJQ2PBW3YN' ) );
	return preg_match( '/^G-[A-Z0-9]{4,16}$/', $id ) ? $id : '';
}

/**
 * GTM container ID.
 *
 * @return string
 */
function smile_gtm_id() {
	$id = (string) apply_filters( 'smile_gtm_id', smile_setting( 'gtm_id', 'GTM-TDZHTDQF' ) );
	return preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $id ) ? $id : '';
}

/**
 * Should tags print on this request?
 *
 * @return bool
 */
function smile_analytics_enabled() {
	if ( is_admin() || is_feed() || is_preview() || is_customize_preview() || wp_doing_ajax() ) {
		return false;
	}
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return false; // SEOPress still prints GA4/GTM until it is removed.
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return false; // Staff visits are not visitor data.
	}
	return (bool) apply_filters( 'smile_analytics_enabled', true );
}

/**
 * Print GTM and GA4 in the head, deferred (2.52.0).
 *
 * The dataLayer, gtag() and the GA4 config are set up at once (tiny, inline), so
 * nothing is lost; the Google scripts themselves (about 260 KB) are only fetched
 * on the visitor's first interaction (scroll, tap, key, mouse move) or when the
 * browser is idle after the page has loaded (at most 4 s after load), whichever
 * comes first. This takes them out of the first-paint and LCP window on phones.
 * Switch back to immediate loading: add_filter( 'smile_analytics_defer', '__return_false' ).
 */
function smile_analytics_head() {
	if ( ! smile_analytics_enabled() ) {
		return;
	}
	$gtm   = smile_gtm_id();
	$ga4   = smile_ga4_id();
	$defer = (bool) apply_filters( 'smile_analytics_defer', true );
	$srcs  = array();
	$js    = 'window.dataLayer=window.dataLayer||[];';
	if ( $gtm ) {
		$js    .= "dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});";
		$srcs[] = 'https://www.googletagmanager.com/gtm.js?id=' . $gtm;
	}
	if ( $ga4 ) {
		$js    .= "function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" . esc_js( $ga4 ) . "');";
		$srcs[] = 'https://www.googletagmanager.com/gtag/js?id=' . $ga4;
	}
	if ( ! $srcs ) {
		return;
	}
	$list = wp_json_encode( array_map( 'esc_url_raw', $srcs ) );
	if ( $defer ) {
		$js .= '(function(){var s=' . $list . ',d=0,ev=["scroll","pointerdown","keydown","mousemove","touchstart"];'
			. 'function go(){if(d)return;d=1;ev.forEach(function(e){removeEventListener(e,go,{passive:true});});'
			. 's.forEach(function(u){var j=document.createElement("script");j.async=true;j.src=u;document.head.appendChild(j);});}'
			. 'ev.forEach(function(e){addEventListener(e,go,{passive:true,once:true});});'
			. 'addEventListener("load",function(){var t=function(){("requestIdleCallback" in window)?requestIdleCallback(go,{timeout:4000}):setTimeout(go,2000);};setTimeout(t,1500);});})();';
	} else {
		$js .= '(function(){' . $list . '.forEach(function(u){var j=document.createElement("script");j.async=true;j.src=u;document.head.appendChild(j);});})();';
	}
	echo "<!-- Google Tag Manager + GA4 (deferred, see inc/analytics.php) -->\n<script>" . $js . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- IDs validated, URLs JSON-encoded.
}
add_action( 'wp_head', 'smile_analytics_head', 2 );

/**
 * GTM noscript fallback right after <body>.
 */
function smile_analytics_body() {
	if ( ! smile_analytics_enabled() ) {
		return;
	}
	$gtm = smile_gtm_id();
	if ( $gtm ) {
		echo '<noscript><iframe src="' . esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $gtm ) . '" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>' . "\n";
	}
}
add_action( 'wp_body_open', 'smile_analytics_body', 1 );
