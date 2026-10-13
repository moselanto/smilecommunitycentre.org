<?php
/**
 * Small template helpers.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a value saved in the Smile Community Core plugin settings, with a fallback.
 *
 * @param string $key      Setting key.
 * @param string $fallback Fallback value.
 * @return string
 */
function smile_setting( $key, $fallback = '' ) {
	if ( function_exists( 'smile_core_get' ) ) {
		$value = smile_core_get( $key );
		if ( '' !== $value && null !== $value ) {
			return (string) $value;
		}
	}
	return (string) $fallback;
}

/**
 * URL of the donate page (Customizer override, else page with slug "donate").
 *
 * @return string
 */
function smile_donate_url() {
	$custom = get_theme_mod( 'smile_donate_url', '' );
	if ( $custom ) {
		return $custom;
	}
	$page = get_page_by_path( 'donate' );
	return $page ? get_permalink( $page ) : home_url( '/donate/' );
}

/**
 * Format a Kenyan phone number into a tel: href.
 *
 * @param string $number Phone number as displayed.
 * @return string
 */
function smile_tel_href( $number ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $number );
	if ( 0 === strpos( $digits, '0' ) ) {
		$digits = '+254' . substr( $digits, 1 );
	}
	return 'tel:' . $digits;
}

/**
 * Article reading time in minutes.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function smile_reading_time( $post_id = 0 ) {
	$content = get_post_field( 'post_content', $post_id ? $post_id : get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( (string) $content ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Output a simple, accessible breadcrumb trail. Skipped if SEOPress breadcrumbs are in use.
 */
function smile_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	if ( function_exists( 'seopress_display_breadcrumbs' ) && apply_filters( 'smile_use_seopress_breadcrumbs', false ) ) {
		seopress_display_breadcrumbs();
		return;
	}
	$items   = array();
	$items[] = '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'smile-community' ) . '</a>';
	if ( is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			$items[] = '<a href="' . esc_url( get_permalink( $posts_page ) ) . '">' . esc_html( get_the_title( $posts_page ) ) . '</a>';
		}
	}
	if ( is_singular() ) {
		$items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_archive() ) {
		$items[] = '<span aria-current="page">' . esc_html( wp_strip_all_tags( get_the_archive_title() ) ) . '</span>';
	} elseif ( is_search() ) {
		$items[] = '<span aria-current="page">' . esc_html__( 'Search results', 'smile-community' ) . '</span>';
	}
	echo '<nav class="smile-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'smile-community' ) . '"><ol><li>' . implode( '</li><li>', $items ) . '</li></ol></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- items escaped above.
}

/**
 * Donation call-to-action band used at the end of articles and on 404/search.
 */
function smile_cta_band() {
	get_template_part( 'template-parts/cta-band' );
}


/**
 * Stories and topic pages show 12 stories, so the grid always fills complete rows
 * (page 1: 1 lead + 3 "Also new" + 8 cards + 1 support card; later pages: 12 cards).
 *
 * @param WP_Query $q Query.
 */
function smile_stories_per_page( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_category() || $q->is_tag() ) {
		$q->set( 'posts_per_page', 12 );
	}
}
add_action( 'pre_get_posts', 'smile_stories_per_page' );
