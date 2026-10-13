<?php
/**
 * Plugin Name:       Smile Community Core
 * Plugin URI:        https://smilecommunitycentre.org/
 * Description:       Site functionality for Smile Community Centre Children's Home: editable contact and donation details, M-Pesa donation blocks, secure enquiry forms with a private inbox, redirects from old URLs, photo metadata stripping, and a one-click staging site builder for the Elementor pages.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Smile Community Centre
 * License:           GPL-2.0-or-later
 * Text Domain:       smile-core
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

define( 'SMILE_CORE_VERSION', '1.0.0' );
define( 'SMILE_CORE_FILE', __FILE__ );
define( 'SMILE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMILE_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'SMILE_CORE_CAP', 'smile_manage_inquiries' );

require_once SMILE_CORE_DIR . 'includes/settings.php';
require_once SMILE_CORE_DIR . 'includes/inquiries.php';
require_once SMILE_CORE_DIR . 'includes/forms.php';
require_once SMILE_CORE_DIR . 'includes/shortcodes.php';
require_once SMILE_CORE_DIR . 'includes/redirects.php';
require_once SMILE_CORE_DIR . 'includes/media-privacy.php';
require_once SMILE_CORE_DIR . 'includes/dashboard.php';
require_once SMILE_CORE_DIR . 'includes/site-setup.php';

/**
 * Activation: capabilities, default settings, retention cron.
 */
function smile_core_activate() {
	foreach ( array( 'administrator', 'editor' ) as $role_name ) {
		$role = get_role( $role_name );
		if ( $role ) {
			$role->add_cap( SMILE_CORE_CAP );
		}
	}
	if ( false === get_option( 'smile_core_settings', false ) ) {
		add_option( 'smile_core_settings', smile_core_defaults() );
	}
	if ( false === get_option( 'smile_core_redirects', false ) ) {
		add_option( 'smile_core_redirects', smile_core_default_redirects() );
	}
	if ( ! wp_next_scheduled( 'smile_core_retention' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'smile_core_retention' );
	}
	smile_core_register_inquiry_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'smile_core_activate' );

/**
 * Deactivation: stop cron. Data and settings are kept.
 */
function smile_core_deactivate() {
	wp_clear_scheduled_hook( 'smile_core_retention' );
}
register_deactivation_hook( __FILE__, 'smile_core_deactivate' );
