<?php
/**
 * Smile Community Centre child theme.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

define( 'SMILE_THEME_VERSION', '2.39.0' );
define( 'SMILE_THEME_DIR', get_stylesheet_directory() );
define( 'SMILE_THEME_URI', get_stylesheet_directory_uri() );

require_once SMILE_THEME_DIR . '/inc/security.php';
require_once SMILE_THEME_DIR . '/inc/setup.php';
require_once SMILE_THEME_DIR . '/inc/brand.php';
require_once SMILE_THEME_DIR . '/inc/content-editor.php';
require_once SMILE_THEME_DIR . '/inc/seo.php';
require_once SMILE_THEME_DIR . '/inc/template-tags.php';
require_once SMILE_THEME_DIR . '/inc/customizer.php';
require_once SMILE_THEME_DIR . '/inc/slider.php';
require_once SMILE_THEME_DIR . '/inc/images.php';
require_once SMILE_THEME_DIR . '/inc/components.php';
require_once SMILE_THEME_DIR . '/inc/home-sections.php';
require_once SMILE_THEME_DIR . '/inc/whatsapp-chat.php';
require_once SMILE_THEME_DIR . '/inc/careers.php';
require_once SMILE_THEME_DIR . '/inc/login.php';
require_once SMILE_THEME_DIR . '/inc/performance.php';
require_once SMILE_THEME_DIR . '/inc/two-factor.php';
require_once SMILE_THEME_DIR . '/inc/theme-options.php';
require_once SMILE_THEME_DIR . '/inc/elementor/builder.php';
require_once SMILE_THEME_DIR . '/inc/elementor/widgets.php';

if ( is_admin() || wp_doing_ajax() ) {
	require_once SMILE_THEME_DIR . '/inc/plugins.php';
	require_once SMILE_THEME_DIR . '/inc/importer.php';
	require_once SMILE_THEME_DIR . '/inc/wizard.php';
}
