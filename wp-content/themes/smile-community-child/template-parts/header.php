<?php
/**
 * Site header: an Elementor template chosen in Smile Theme Options (or in the
 * page's own settings), otherwise the built-in header (template-parts/site-header.php).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'smile_builder_render' ) && smile_builder_render( 'header' ) ) {
	return;
}
get_template_part( 'template-parts/site-header' );
