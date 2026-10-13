<?php
/**
 * Editable organisation details (Smile Centre > Settings).
 *
 * Values here feed the header, footer, donation blocks and forms, so staff can
 * update a phone number or Paybill once and it changes everywhere.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values, taken from the live website audit (13 Oct 2026).
 * Anything not verified on the live site is left empty on purpose.
 *
 * @return array
 */
function smile_core_defaults() {
	return array(
		// Contact.
		'org_name'           => "Smile Community Centre Children's Home",
		'phone_primary'      => '0721 318 869',
		'phone_others'       => "0722 412 676\n0720 809 885",
		'whatsapp'           => '254721318869',
		'email'              => 'info@smilecommunitycentre.org',
		'address'            => 'Mzesa, Soweto, Kayole (near Donholm Phase 8), Nairobi, Kenya',
		'postal_address'     => 'P.O. Box 196-00518, Nairobi, Kenya',
		'office_hours'       => 'Office: 9am - 5pm',
		'map_link'           => 'https://maps.app.goo.gl/6BvCCFYRq1DtkXNF9',
		'map_query'          => "Smile Community Centre Children's Home, Soweto, Kayole, Nairobi",
		'directions'         => "From Nairobi town, go to the Ambassadeur bus stage and board a 19C Double M bus (Donholm - Savannah - Jacaranda route).\nGet off at the Jacaranda roundabout stage.\nWalk along the road next to Jacaranda grounds towards Soweto.\nAt the junction, turn right towards Embakasi Garrison Road.\nContinue until you reach Soweto Mzesa, just before the riverbank bridge. The centre is on the left side of the road, opposite the Edelvale Primary School fence.",
		'instagram_url'      => 'https://www.instagram.com/smile_kenya_/',
		'facebook_url'       => '',
		// Donations.
		'mpesa_paybill'      => '247247',
		'mpesa_account'      => '147516',
		'mpesa_account_name' => '',
		'bank_details'       => '',
		'online_giving_url'  => '',
		'online_giving_label'=> 'Give online',
		'tiers'              => "500|5|Daily meals and clean water for 3 children|\n2500|25|A week of food, hygiene and safe care|\n8000|80|Meals, education and medical care together|featured\n15000|150|School fees or medical treatment for one child|",
		'tiers_note'         => 'These are estimates of what a gift typically covers, not a guarantee of a specific outcome. Gifts are used where the need is greatest unless you tell us otherwise.',
		// Volunteering.
		'volunteer_open'     => '0',
		'volunteer_notice'   => 'Volunteer places are currently full. You are welcome to send an expression of interest and we will contact you when a place opens.',
		// Forms.
		'notify_email'       => get_option( 'admin_email' ),
		'retention_days'     => '365',
		'safeguarding_email' => '',
	);
}

/**
 * Get one setting.
 *
 * @param string $key Setting key.
 * @return string
 */
function smile_core_get( $key ) {
	$settings = get_option( 'smile_core_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}
	if ( array_key_exists( $key, $settings ) ) {
		return (string) $settings[ $key ];
	}
	$defaults = smile_core_defaults();
	return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
}

/**
 * Field definitions for the settings screen.
 *
 * @return array
 */
function smile_core_fields() {
	return array(
		'contact'   => array(
			'title'  => __( 'Contact details', 'smile-core' ),
			'fields' => array(
				'org_name'       => array( 'Organisation name', 'text' ),
				'phone_primary'  => array( 'Main phone (also used for calls in the header)', 'text' ),
				'phone_others'   => array( 'Other phone numbers (one per line)', 'textarea' ),
				'whatsapp'       => array( 'WhatsApp number in international format, digits only (e.g. 254721318869). Leave empty to hide.', 'text' ),
				'email'          => array( 'Public email address', 'email' ),
				'address'        => array( 'Physical location', 'text' ),
				'postal_address' => array( 'Postal address', 'text' ),
				'office_hours'   => array( 'Office hours', 'text' ),
				'map_link'       => array( 'Google Maps link', 'url' ),
				'map_query'      => array( 'Place name used for the embedded map', 'text' ),
				'directions'     => array( 'Directions by matatu (one step per line)', 'textarea' ),
				'instagram_url'  => array( 'Instagram URL', 'url' ),
				'facebook_url'   => array( 'Facebook URL (leave empty to hide)', 'url' ),
			),
		),
		'donations' => array(
			'title'  => __( 'Donation details', 'smile-core' ),
			'fields' => array(
				'mpesa_paybill'       => array( 'M-Pesa Paybill (business number)', 'text' ),
				'mpesa_account'       => array( 'M-Pesa account number', 'text' ),
				'mpesa_account_name'  => array( 'Name the donor sees on the M-Pesa confirmation (shown so donors can check they are paying the right account). Leave empty until confirmed.', 'text' ),
				'bank_details'        => array( 'Bank transfer details (one line each, e.g. "Bank: ...", "Account name: ..."). Leave empty to hide the bank block.', 'textarea' ),
				'online_giving_url'   => array( 'Online giving link for international donors (only a link the centre controls and has tested). Leave empty to hide.', 'url' ),
				'online_giving_label' => array( 'Online giving button text', 'text' ),
				'tiers'               => array( 'Giving examples, one per line: KES|USD|What it helps with|featured (optional)', 'textarea' ),
				'tiers_note'          => array( 'Note shown under the giving examples', 'textarea' ),
			),
		),
		'volunteer' => array(
			'title'  => __( 'Volunteering', 'smile-core' ),
			'fields' => array(
				'volunteer_open'   => array( 'Volunteer places are open', 'checkbox' ),
				'volunteer_notice' => array( 'Notice shown when places are full', 'textarea' ),
			),
		),
		'forms'     => array(
			'title'  => __( 'Forms and privacy', 'smile-core' ),
			'fields' => array(
				'notify_email'       => array( 'Send new enquiries to (comma-separated emails)', 'text' ),
				'safeguarding_email' => array( 'Send safeguarding and photo-removal requests to (leave empty to use the address above)', 'text' ),
				'retention_days'     => array( 'Delete enquiries automatically after this many days (0 = keep)', 'number' ),
			),
		),
	);
}

/**
 * Admin menu.
 */
function smile_core_admin_menu() {
	add_menu_page(
		__( 'Smile Centre', 'smile-core' ),
		__( 'Smile Centre', 'smile-core' ),
		'edit_pages',
		'smile-centre',
		'smile_core_render_settings',
		'dashicons-heart',
		3
	);
	add_submenu_page( 'smile-centre', __( 'Centre details', 'smile-core' ), __( 'Centre details', 'smile-core' ), 'edit_pages', 'smile-centre', 'smile_core_render_settings' );
	add_submenu_page( 'smile-centre', __( 'Redirects', 'smile-core' ), __( 'Redirects', 'smile-core' ), 'manage_options', 'smile-redirects', 'smile_core_render_redirects' );
}
add_action( 'admin_menu', 'smile_core_admin_menu' );

/**
 * Save settings (editors may update contact/donation details; only admins change form routing).
 */
function smile_core_save_settings() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'You are not allowed to change these settings.', 'smile-core' ), 403 );
	}
	check_admin_referer( 'smile_core_settings' );

	$current = get_option( 'smile_core_settings', array() );
	$current = is_array( $current ) ? $current : array();
	$input   = isset( $_POST['smile'] ) && is_array( $_POST['smile'] ) ? wp_unslash( $_POST['smile'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.

	foreach ( smile_core_fields() as $group_key => $group ) {
		if ( 'forms' === $group_key && ! current_user_can( 'manage_options' ) ) {
			continue;
		}
		foreach ( $group['fields'] as $key => $def ) {
			$type = $def[1];
			$raw  = isset( $input[ $key ] ) ? $input[ $key ] : '';
			switch ( $type ) {
				case 'checkbox':
					$current[ $key ] = empty( $raw ) ? '0' : '1';
					break;
				case 'textarea':
					$current[ $key ] = sanitize_textarea_field( $raw );
					break;
				case 'url':
					$current[ $key ] = esc_url_raw( trim( $raw ) );
					break;
				case 'email':
					$current[ $key ] = sanitize_email( $raw );
					break;
				case 'number':
					$current[ $key ] = (string) absint( $raw );
					break;
				default:
					$current[ $key ] = sanitize_text_field( $raw );
			}
		}
	}
	if ( isset( $current['whatsapp'] ) ) {
		$current['whatsapp'] = preg_replace( '/\D/', '', $current['whatsapp'] );
	}
	if ( isset( $current['notify_email'] ) ) {
		$current['notify_email'] = implode( ',', array_filter( array_map( 'sanitize_email', explode( ',', $current['notify_email'] ) ) ) );
	}
	if ( isset( $current['safeguarding_email'] ) ) {
		$current['safeguarding_email'] = implode( ',', array_filter( array_map( 'sanitize_email', explode( ',', $current['safeguarding_email'] ) ) ) );
	}

	update_option( 'smile_core_settings', $current );
	wp_safe_redirect( add_query_arg( array( 'page' => 'smile-centre', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_smile_core_save_settings', 'smile_core_save_settings' );

/**
 * Render settings screen.
 */
function smile_core_render_settings() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Smile Centre: centre details', 'smile-core' ); ?></h1>
		<p><?php esc_html_e( 'These details appear across the website (header, footer, Donate, Contact and forms). Change them here once and every page updates.', 'smile-core' ); ?></p>
		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'smile-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="smile_core_save_settings" />
			<?php wp_nonce_field( 'smile_core_settings' ); ?>
			<?php foreach ( smile_core_fields() as $group_key => $group ) : ?>
				<?php
				if ( 'forms' === $group_key && ! current_user_can( 'manage_options' ) ) {
					continue;
				}
				?>
				<h2><?php echo esc_html( $group['title'] ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $group['fields'] as $key => $def ) : ?>
						<?php
						$id    = 'smile-' . $key;
						$value = smile_core_get( $key );
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $def[0] ); ?></label></th>
							<td>
								<?php if ( 'textarea' === $def[1] ) : ?>
									<textarea class="large-text" rows="5" id="<?php echo esc_attr( $id ); ?>" name="smile[<?php echo esc_attr( $key ); ?>]"><?php echo esc_textarea( $value ); ?></textarea>
								<?php elseif ( 'checkbox' === $def[1] ) : ?>
									<input type="checkbox" id="<?php echo esc_attr( $id ); ?>" name="smile[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( '1', $value ); ?> />
								<?php else : ?>
									<input class="regular-text" type="<?php echo esc_attr( 'number' === $def[1] ? 'number' : ( 'url' === $def[1] ? 'url' : 'text' ) ); ?>" id="<?php echo esc_attr( $id ); ?>" name="smile[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( __( 'Save details', 'smile-core' ) ); ?>
		</form>
	</div>
	<?php
}
