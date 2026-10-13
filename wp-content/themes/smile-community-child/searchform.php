<?php
/**
 * Accessible search form.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
$smile_id = wp_unique_id( 'smile-search-' );
?>
<form role="search" method="get" class="smile-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $smile_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'Search stories and pages', 'smile-community' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $smile_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search stories and pages', 'smile-community' ); ?>" />
	<button type="submit" class="smile-btn smile-btn--teal"><?php esc_html_e( 'Search', 'smile-community' ); ?></button>
</form>
