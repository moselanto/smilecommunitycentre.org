<?php
/**
 * One-off content clean-ups, run once in wp-admin (admin_init) and then skipped.
 *
 * 2.53.0: posts imported from the staging site still linked to
 * https://topnotchmall.co.ke/smilescommunity/... (Donate, About, Feeding, Contact and
 * some image URLs), and two Resources articles showed the writer's internal labels
 * ("Quick Answer (Featured Snippet Target)", "Donation Call to Action") to visitors.
 * Every post, page and Elementor template is checked; only matching ones are saved
 * (wp_update_post, so a revision is kept). Result is stored in the option below.
 *
 * @package Smile_Community_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite staging URLs to the live site and drop internal editorial labels.
 *
 * @param string $text  Post content or Elementor JSON.
 * @param bool   $json  True when $text is JSON with escaped slashes.
 * @return string
 */
function smile_fix_content_text( $text, $json = false ) {
	$live = untrailingslashit( home_url() );
	foreach ( array( 'https://topnotchmall.co.ke/smilescommunity', 'http://topnotchmall.co.ke/smilescommunity', 'https://www.topnotchmall.co.ke/smilescommunity', '//topnotchmall.co.ke/smilescommunity' ) as $old ) {
		$new  = ( 0 === strpos( $old, '//' ) ) ? preg_replace( '#^https?:#', '', $live ) : $live;
		$text = str_replace( $old, $new, $text );
		if ( $json ) {
			$text = str_replace( str_replace( '/', '\/', $old ), str_replace( '/', '\/', $new ), $text );
		}
	}
	// Internal labels the writer left in (bold lead-in text inside callout tables).
	$text = preg_replace( '#<(strong|b)>\s*Quick Answer \(Featured Snippet Target\)\s*:?\s*</\1>\s*:?\s*#i', '', $text );
	$text = preg_replace( '#<(strong|b)>\s*Donation Call to Action\s*:?\s*</\1>\s*:?\s*#i', '', $text );
	$text = preg_replace( '#\s*\((?:Featured Snippet Target|Snippet Target)\)#i', '', $text );
	return $text;
}

/**
 * Run the clean-up once.
 */
function smile_fix_staging_content_2530() {
	if ( get_option( 'smile_content_fix_2530' ) || ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	global $wpdb;
	$like = '%' . $wpdb->esc_like( 'topnotchmall.co.ke' ) . '%';
	$ids  = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','attachment') AND post_status IN ('publish','draft','pending','private','future') AND ( post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s )",
			$like,
			'%Featured Snippet Target%',
			'%Donation Call to Action%'
		)
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE %s", '%topnotchmall.co.ke%' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$log = array( 'content' => array(), 'elementor' => array() );
	foreach ( array_map( 'intval', (array) $ids ) as $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			continue;
		}
		$new = smile_fix_content_text( $post->post_content );
		if ( $new !== $post->post_content ) {
			wp_update_post( wp_slash( array( 'ID' => $id, 'post_content' => $new ) ) );
			$log['content'][] = $id;
		}
	}
	foreach ( array_map( 'intval', (array) $meta_ids ) as $id ) {
		$raw = get_post_meta( $id, '_elementor_data', true );
		if ( ! is_string( $raw ) ) {
			continue;
		}
		$new = smile_fix_content_text( $raw, true );
		if ( $new !== $raw && null !== json_decode( $new ) ) {
			update_post_meta( $id, '_elementor_data', wp_slash( $new ) );
			delete_post_meta( $id, '_elementor_css' );
			$log['elementor'][] = $id;
		}
	}
	if ( class_exists( '\Elementor\Plugin' ) && ! empty( $log['elementor'] ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}
	update_option( 'smile_content_fix_2530', array( 'time' => time(), 'fixed' => $log ), false );
}
add_action( 'admin_init', 'smile_fix_staging_content_2530', 30 );
