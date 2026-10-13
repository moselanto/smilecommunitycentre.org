<?php
/**
 * Dashboard quick guide for staff.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register widget.
 */
function smile_core_dashboard_widget() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	wp_add_dashboard_widget( 'smile_core_guide', __( 'Smile website: quick guide', 'smile-core' ), 'smile_core_render_dashboard' );
}
add_action( 'wp_dashboard_setup', 'smile_core_dashboard_widget' );

/**
 * Render widget.
 */
function smile_core_render_dashboard() {
	$new = 0;
	if ( current_user_can( SMILE_CORE_CAP ) ) {
		$q   = new WP_Query(
			array(
				'post_type'      => 'smile_inquiry',
				'post_status'    => 'private',
				'meta_key'       => '_smile_handled', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => '0', // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		$new = (int) $q->found_posts;
	}
	echo '<ul style="list-style:disc;padding-left:1.2em">';
	if ( current_user_can( SMILE_CORE_CAP ) ) {
		/* translators: %d count */
		echo '<li><a href="' . esc_url( admin_url( 'edit.php?post_type=smile_inquiry' ) ) . '">' . esc_html( sprintf( _n( '%d new enquiry', '%d new enquiries', $new, 'smile-core' ), $new ) ) . '</a></li>';
	}
	echo '<li>' . esc_html__( 'Edit a page: Pages > hover the page > Edit with Elementor.', 'smile-core' ) . '</li>';
	echo '<li>' . esc_html__( 'Publish a story: Posts > Add New. Add a featured image and a category.', 'smile-core' ) . '</li>';
	echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=smile-centre' ) ) . '">' . esc_html__( 'Update phone numbers, M-Pesa or bank details', 'smile-core' ) . '</a></li>';
	echo '<li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Change the menu', 'smile-core' ) . '</a></li>';
	echo '</ul>';
}
