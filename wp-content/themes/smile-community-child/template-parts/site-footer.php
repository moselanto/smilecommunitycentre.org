<?php
/**
 * Built-in site footer markup. Shown by template-parts/footer.php when no
 * Elementor footer template is chosen, and by the "Smile: Site footer" Elementor widget.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_email     = smile_setting( 'email', 'info@smilecommunitycentre.org' );
$smile_phone     = smile_setting( 'phone_primary', '0721 318 869' );
$smile_address   = smile_setting( 'address', __( 'Mzesa, Soweto, Kayole (near Donholm Phase 8), Nairobi, Kenya', 'smile-community' ) );
$smile_postal    = smile_setting( 'postal_address', 'P.O. Box 196-00518, Nairobi' );
?>
<footer class="smile-footer" id="site-footer">
	<div class="smile-container smile-footer__grid">
		<div class="smile-footer__about">
			<div class="smile-footer__logo"><?php echo smile_brand_logo( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_brand_logo(). ?></div>
			<p><?php echo esc_html( get_theme_mod( 'smile_footer_tagline', __( 'A safe home, daily meals, education and care for orphaned and vulnerable children in Soweto, Kayole, Nairobi.', 'smile-community' ) ) ); ?></p>
			<a class="smile-btn smile-btn--sun" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php esc_html_e( 'Donate Now', 'smile-community' ); ?></a>
		</div>

		<nav class="smile-footer__col" aria-label="<?php esc_attr_e( 'Footer', 'smile-community' ); ?>">
			<h2 class="smile-footer__heading"><?php esc_html_e( 'Explore', 'smile-community' ); ?></h2>
			<?php wp_nav_menu( array( 'theme_location' => 'menu-2', 'container' => false, 'menu_class' => 'smile-footer__list', 'depth' => 1, 'fallback_cb' => false ) ); ?>
		</nav>

		<nav class="smile-footer__col" aria-label="<?php esc_attr_e( 'Programmes', 'smile-community' ); ?>">
			<h2 class="smile-footer__heading"><?php esc_html_e( 'Our Programmes', 'smile-community' ); ?></h2>
			<?php wp_nav_menu( array( 'theme_location' => 'smile-footer-programmes', 'container' => false, 'menu_class' => 'smile-footer__list', 'depth' => 1, 'fallback_cb' => false ) ); ?>
		</nav>

		<div class="smile-footer__col">
			<h2 class="smile-footer__heading"><?php esc_html_e( 'Contact', 'smile-community' ); ?></h2>
			<address class="smile-footer__contact">
				<span><?php echo esc_html( $smile_address ); ?></span>
				<span><?php echo esc_html( $smile_postal ); ?></span>
				<a href="<?php echo esc_url( smile_tel_href( $smile_phone ) ); ?>"><?php echo esc_html( $smile_phone ); ?></a>
				<a href="<?php echo esc_url( 'mailto:' . antispambot( $smile_email ) ); ?>"><?php echo esc_html( antispambot( $smile_email ) ); ?></a>
			</address>
			<div class="smile-footer__follow">
				<p class="smile-footer__follow-label"><?php esc_html_e( 'Follow us', 'smile-community' ); ?></p>
				<?php echo smile_social_icons( 'smile-footer__social' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_social_icons(). ?>
			</div>
		</div>
	</div>

	<div class="smile-container smile-footer__bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<?php wp_nav_menu( array( 'theme_location' => 'smile-footer-legal', 'container' => 'nav', 'container_attributes' => array(), 'menu_class' => 'smile-footer__legal', 'depth' => 1, 'fallback_cb' => false ) ); ?>
	</div>
</footer>
