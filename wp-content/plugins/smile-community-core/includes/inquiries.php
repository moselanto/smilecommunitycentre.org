<?php
/**
 * Private enquiries inbox (non-public post type), retention and privacy tools.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form types and their labels.
 *
 * @return array
 */
function smile_core_form_types() {
	return array(
		'contact'   => __( 'General enquiry', 'smile-core' ),
		'visit'     => __( 'Visit request', 'smile-core' ),
		'volunteer' => __( 'Volunteer application', 'smile-core' ),
		'partner'   => __( 'Partnership enquiry', 'smile-core' ),
		'donation'  => __( 'Donation / pledge', 'smile-core' ),
		'removal'   => __( 'Safeguarding / photo removal', 'smile-core' ),
	);
}

/**
 * Register the private enquiry post type.
 */
function smile_core_register_inquiry_type() {
	register_post_type(
		'smile_inquiry',
		array(
			'labels'              => array(
				'name'          => __( 'Enquiries', 'smile-core' ),
				'singular_name' => __( 'Enquiry', 'smile-core' ),
				'menu_name'     => __( 'Enquiries', 'smile-core' ),
				'edit_item'     => __( 'View enquiry', 'smile-core' ),
				'search_items'  => __( 'Search enquiries', 'smile-core' ),
				'not_found'     => __( 'No enquiries yet.', 'smile-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'smile-centre',
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'capabilities'        => array(
				'edit_post'          => SMILE_CORE_CAP,
				'read_post'          => SMILE_CORE_CAP,
				'delete_post'        => SMILE_CORE_CAP,
				'edit_posts'         => SMILE_CORE_CAP,
				'edit_others_posts'  => SMILE_CORE_CAP,
				'delete_posts'       => SMILE_CORE_CAP,
				'publish_posts'      => 'do_not_allow',
				'read_private_posts' => SMILE_CORE_CAP,
				'create_posts'       => 'do_not_allow',
			),
			'map_meta_cap'        => false,
		)
	);

	register_taxonomy(
		'smile_inquiry_type',
		'smile_inquiry',
		array(
			'labels'            => array( 'name' => __( 'Enquiry types', 'smile-core' ) ),
			'public'            => false,
			'show_ui'           => false,
			'show_admin_column' => true,
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms' => SMILE_CORE_CAP,
				'edit_terms'   => SMILE_CORE_CAP,
				'delete_terms' => SMILE_CORE_CAP,
				'assign_terms' => SMILE_CORE_CAP,
			),
		)
	);
}
add_action( 'init', 'smile_core_register_inquiry_type' );

/**
 * Read-only details box on the enquiry screen.
 */
function smile_core_inquiry_metabox() {
	add_meta_box( 'smile-inquiry-details', __( 'Submitted details', 'smile-core' ), 'smile_core_render_inquiry_metabox', 'smile_inquiry', 'normal', 'high' );
	remove_meta_box( 'submitdiv', 'smile_inquiry', 'side' );
	add_meta_box( 'smile-inquiry-status', __( 'Status', 'smile-core' ), 'smile_core_render_inquiry_status', 'smile_inquiry', 'side', 'high' );
}
add_action( 'add_meta_boxes_smile_inquiry', 'smile_core_inquiry_metabox' );

/**
 * Render submitted fields.
 *
 * @param WP_Post $post Enquiry.
 */
function smile_core_render_inquiry_metabox( $post ) {
	$fields = get_post_meta( $post->ID, '_smile_fields', true );
	$mail   = get_post_meta( $post->ID, '_smile_mail_sent', true );
	$type   = get_post_meta( $post->ID, '_smile_type', true );
	$types  = smile_core_form_types();
	echo '<p><strong>' . esc_html__( 'Form:', 'smile-core' ) . '</strong> ' . esc_html( isset( $types[ $type ] ) ? $types[ $type ] : $type ) . '</p>';
	echo '<p><strong>' . esc_html__( 'Received:', 'smile-core' ) . '</strong> ' . esc_html( get_the_date( 'j F Y, H:i', $post ) ) . '</p>';
	echo '<p><strong>' . esc_html__( 'Email notification:', 'smile-core' ) . '</strong> ' . ( '1' === $mail ? esc_html__( 'sent', 'smile-core' ) : '<span style="color:#b32d2e">' . esc_html__( 'NOT sent - check email delivery settings', 'smile-core' ) . '</span>' ) . '</p>';
	if ( is_array( $fields ) ) {
		echo '<table class="widefat striped"><tbody>';
		foreach ( $fields as $label => $value ) {
			echo '<tr><th style="width:30%">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( $value ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	$email = get_post_meta( $post->ID, '_smile_email', true );
	if ( $email ) {
		echo '<p><a class="button button-primary" href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html__( 'Reply by email', 'smile-core' ) . '</a></p>';
	}
}

/**
 * Handled / open status box.
 *
 * @param WP_Post $post Enquiry.
 */
function smile_core_render_inquiry_status( $post ) {
	$handled = get_post_meta( $post->ID, '_smile_handled', true );
	wp_nonce_field( 'smile_inquiry_status', 'smile_inquiry_status_nonce' );
	echo '<label><input type="checkbox" name="smile_handled" value="1" ' . checked( '1', $handled, false ) . '> ' . esc_html__( 'Handled', 'smile-core' ) . '</label>';
	echo '<p><textarea name="smile_note" class="widefat" rows="4" placeholder="' . esc_attr__( 'Internal note (not sent to anyone)', 'smile-core' ) . '">' . esc_textarea( get_post_meta( $post->ID, '_smile_note', true ) ) . '</textarea></p>';
	submit_button( __( 'Save', 'smile-core' ), 'primary', 'save', false );
	echo ' <a class="submitdelete deletion" href="' . esc_url( get_delete_post_link( $post->ID, '', true ) ) . '">' . esc_html__( 'Delete permanently', 'smile-core' ) . '</a>';
}

/**
 * Save status/note.
 *
 * @param int $post_id Post ID.
 */
function smile_core_save_inquiry_status( $post_id ) {
	if ( ! isset( $_POST['smile_inquiry_status_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smile_inquiry_status_nonce'] ) ), 'smile_inquiry_status' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( SMILE_CORE_CAP ) ) {
		return;
	}
	update_post_meta( $post_id, '_smile_handled', empty( $_POST['smile_handled'] ) ? '0' : '1' );
	update_post_meta( $post_id, '_smile_note', isset( $_POST['smile_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['smile_note'] ) ) : '' );
}
add_action( 'save_post_smile_inquiry', 'smile_core_save_inquiry_status' );

/**
 * List columns.
 *
 * @param array $cols Columns.
 * @return array
 */
function smile_core_inquiry_columns( $cols ) {
	return array(
		'cb'           => $cols['cb'],
		'title'        => __( 'From', 'smile-core' ),
		'smile_type'   => __( 'Form', 'smile-core' ),
		'smile_status' => __( 'Status', 'smile-core' ),
		'date'         => __( 'Received', 'smile-core' ),
	);
}
add_filter( 'manage_smile_inquiry_posts_columns', 'smile_core_inquiry_columns' );

/**
 * Column values.
 *
 * @param string $col     Column.
 * @param int    $post_id Post.
 */
function smile_core_inquiry_column_values( $col, $post_id ) {
	if ( 'smile_type' === $col ) {
		$types = smile_core_form_types();
		$type  = get_post_meta( $post_id, '_smile_type', true );
		echo esc_html( isset( $types[ $type ] ) ? $types[ $type ] : $type );
	}
	if ( 'smile_status' === $col ) {
		echo '1' === get_post_meta( $post_id, '_smile_handled', true ) ? esc_html__( 'Handled', 'smile-core' ) : '<strong>' . esc_html__( 'New', 'smile-core' ) . '</strong>';
	}
}
add_action( 'manage_smile_inquiry_posts_custom_column', 'smile_core_inquiry_column_values', 10, 2 );

/**
 * Daily retention clean-up.
 */
function smile_core_run_retention() {
	$days = absint( smile_core_get( 'retention_days' ) );
	if ( $days < 1 ) {
		return;
	}
	$old = get_posts(
		array(
			'post_type'      => 'smile_inquiry',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 200,
			'date_query'     => array( array( 'before' => gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS ), 'column' => 'post_date_gmt' ) ),
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( $id, true );
	}
}
add_action( 'smile_core_retention', 'smile_core_run_retention' );

/**
 * Personal data exporter / eraser (Tools > Export/Erase Personal Data).
 *
 * @param array $exporters Exporters.
 * @return array
 */
function smile_core_register_exporter( $exporters ) {
	$exporters['smile-core'] = array(
		'exporter_friendly_name' => __( 'Smile Centre enquiries', 'smile-core' ),
		'callback'               => 'smile_core_export_personal_data',
	);
	return $exporters;
}
add_filter( 'wp_privacy_personal_data_exporters', 'smile_core_register_exporter' );

/**
 * Find enquiries for an email.
 *
 * @param string $email Email.
 * @return int[]
 */
function smile_core_inquiries_for_email( $email ) {
	return get_posts(
		array(
			'post_type'      => 'smile_inquiry',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => 500,
			'meta_key'       => '_smile_email', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => strtolower( $email ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Exporter callback.
 *
 * @param string $email Email.
 * @return array
 */
function smile_core_export_personal_data( $email ) {
	$items = array();
	foreach ( smile_core_inquiries_for_email( $email ) as $id ) {
		$data   = array();
		$fields = get_post_meta( $id, '_smile_fields', true );
		if ( is_array( $fields ) ) {
			foreach ( $fields as $label => $value ) {
				$data[] = array( 'name' => $label, 'value' => $value );
			}
		}
		$items[] = array(
			'group_id'    => 'smile-inquiries',
			'group_label' => __( 'Enquiries', 'smile-core' ),
			'item_id'     => 'smile-inquiry-' . $id,
			'data'        => $data,
		);
	}
	return array( 'data' => $items, 'done' => true );
}

/**
 * Register eraser.
 *
 * @param array $erasers Erasers.
 * @return array
 */
function smile_core_register_eraser( $erasers ) {
	$erasers['smile-core'] = array(
		'eraser_friendly_name' => __( 'Smile Centre enquiries', 'smile-core' ),
		'callback'             => 'smile_core_erase_personal_data',
	);
	return $erasers;
}
add_filter( 'wp_privacy_personal_data_erasers', 'smile_core_register_eraser' );

/**
 * Eraser callback.
 *
 * @param string $email Email.
 * @return array
 */
function smile_core_erase_personal_data( $email ) {
	$ids = smile_core_inquiries_for_email( $email );
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	return array( 'items_removed' => count( $ids ) > 0, 'items_retained' => false, 'messages' => array(), 'done' => true );
}
