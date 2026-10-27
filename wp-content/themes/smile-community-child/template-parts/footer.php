<?php
/**
 * Site footer: an Elementor template chosen in Smile Theme Options (or in the
 * page's own settings), otherwise the built-in footer (template-parts/site-footer.php).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'smile_builder_render' ) && smile_builder_render( 'footer' ) ) {
	return;
}
get_template_part( 'template-parts/site-footer' );
