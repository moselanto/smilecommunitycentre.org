<?php
/**
 * Smile Community Centre child theme.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

define( 'SMILE_THEME_VERSION', '1.3.0' );
define( 'SMILE_THEME_DIR', get_stylesheet_directory() );
define( 'SMILE_THEME_URI', get_stylesheet_directory_uri() );

require_once SMILE_THEME_DIR . '/inc/setup.php';
require_once SMILE_THEME_DIR . '/inc/template-tags.php';
require_once SMILE_THEME_DIR . '/inc/customizer.php';
require_once SMILE_THEME_DIR . '/inc/slider.php';
require_once SMILE_THEME_DIR . '/inc/images.php';
require_once SMILE_THEME_DIR . '/inc/components.php';

if ( is_admin() || wp_doing_ajax() ) {
	require_once SMILE_THEME_DIR . '/inc/plugins.php';
	require_once SMILE_THEME_DIR . '/inc/importer.php';
	require_once SMILE_THEME_DIR . '/inc/wizard.php';
}
