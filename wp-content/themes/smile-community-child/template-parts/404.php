<?php
/**
 * Not found.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
$smile_programmes = get_page_by_path( 'our-programmes' );
$smile_contact    = get_page_by_path( 'contact-us' );
?>
<main id="content" class="smile-404">
	<div class="smile-container smile-container--narrow smile-section">
		<p class="smile-eyebrow"><?php esc_html_e( 'Page not found', 'smile-community' ); ?></p>
		<h1><?php esc_html_e( 'We could not find that page', 'smile-community' ); ?></h1>
		<p><?php esc_html_e( 'The page may have moved when our website was updated. You can search below or use one of these links.', 'smile-community' ); ?></p>
		<?php get_search_form(); ?>
		<ul class="smile-link-list">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smile-community' ); ?></a></li>
			<?php if ( $smile_programmes ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $smile_programmes ) ); ?>"><?php esc_html_e( 'Our Programmes', 'smile-community' ); ?></a></li>
			<?php endif; ?>
			<li><a href="<?php echo esc_url( smile_donate_url() ); ?>"><?php esc_html_e( 'Donate', 'smile-community' ); ?></a></li>
			<?php if ( $smile_contact ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $smile_contact ) ); ?>"><?php esc_html_e( 'Contact the Centre', 'smile-community' ); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>
</main>
