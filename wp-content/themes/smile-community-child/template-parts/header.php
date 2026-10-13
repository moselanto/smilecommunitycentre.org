<?php
/**
 * Site header (used when no Elementor Theme Builder header exists).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_phone     = smile_setting( 'phone_primary', '0721 318 869' );
$smile_whatsapp  = smile_setting( 'whatsapp', '254721318869' );
$smile_hours     = smile_setting( 'office_hours', __( 'Office: 9am - 5pm', 'smile-community' ) );
$smile_email     = smile_setting( 'email', 'info@smilecommunitycentre.org' );
$smile_instagram = smile_setting( 'instagram_url', 'https://www.instagram.com/smile_kenya_/' );
$smile_donate    = get_theme_mod( 'smile_donate_label', __( 'Donate', 'smile-community' ) );
$smile_heart     = '<svg class="smile-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2 0 3.5 1.1 4.3 2.6.8-1.5 2.3-2.6 4.3-2.6 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z" fill="currentColor"/></svg>';
?>
<a class="smile-skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'smile-community' ); ?></a>

<?php if ( get_theme_mod( 'smile_show_topbar', true ) ) : ?>
<div class="smile-topbar">
	<div class="smile-container smile-topbar__inner">
		<span class="smile-topbar__left">
			<span><svg class="smile-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5c-3.9 0-7 3-7 6.9 0 5 7 12.1 7 12.1s7-7.1 7-12.1c0-3.9-3.1-6.9-7-6.9zm0 9.4a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" fill="currentColor"/></svg><?php esc_html_e( 'Soweto, Kayole, Nairobi', 'smile-community' ); ?></span>
			<span class="smile-topbar__hours"><svg class="smile-ico" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2"/></svg><?php echo esc_html( $smile_hours ); ?></span>
		</span>
		<span class="smile-topbar__links">
			<a href="<?php echo esc_url( smile_tel_href( $smile_phone ) ); ?>"><svg class="smile-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 006.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 013 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.3 0 .7-.2 1z" fill="currentColor"/></svg><?php echo esc_html( $smile_phone ); ?></a>
			<?php if ( $smile_whatsapp ) : ?>
				<a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $smile_whatsapp ) ); ?>" rel="noopener"><?php esc_html_e( 'WhatsApp', 'smile-community' ); ?></a>
			<?php endif; ?>
			<a class="smile-topbar__email" href="<?php echo esc_url( 'mailto:' . antispambot( $smile_email ) ); ?>"><?php echo esc_html( antispambot( $smile_email ) ); ?></a>
			<?php if ( $smile_instagram ) : ?>
				<a href="<?php echo esc_url( $smile_instagram ); ?>" rel="noopener" aria-label="<?php esc_attr_e( 'Instagram', 'smile-community' ); ?>"><svg class="smile-ico" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/></svg></a>
			<?php endif; ?>
		</span>
	</div>
</div>
<?php endif; ?>

<header class="smile-header" id="site-header">
	<div class="smile-container smile-header__inner">
		<div class="smile-header__brand">
			<?php echo smile_brand_logo( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_brand_logo(). ?>
		</div>

		<nav class="smile-nav" id="smile-primary-nav" aria-label="<?php esc_attr_e( 'Main', 'smile-community' ); ?>">
			<div class="smile-nav__head"><?php echo smile_brand_logo( 'drawer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_brand_logo(). ?></div>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-1',
					'container'      => false,
					'menu_class'     => 'smile-nav__list',
					'fallback_cb'    => 'wp_page_menu',
					'depth'          => 2,
				)
			);
			?>
			<div class="smile-nav__extra">
				<a class="smile-btn smile-btn--sun smile-nav__donate-mobile" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php echo $smile_heart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( $smile_donate ); ?></a>
				<div class="smile-nav__contact">
					<a href="<?php echo esc_url( smile_tel_href( $smile_phone ) ); ?>"><span><?php esc_html_e( 'Call us', 'smile-community' ); ?></span><strong><?php echo esc_html( $smile_phone ); ?></strong></a>
					<?php if ( $smile_whatsapp ) : ?>
						<a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $smile_whatsapp ) ); ?>" rel="noopener"><span><?php esc_html_e( 'WhatsApp', 'smile-community' ); ?></span><strong><?php esc_html_e( 'Message us', 'smile-community' ); ?></strong></a>
					<?php endif; ?>
				</div>
				<p class="smile-nav__hours"><?php echo esc_html( $smile_hours ); ?> &middot; <?php esc_html_e( 'Soweto, Kayole, Nairobi', 'smile-community' ); ?></p>
			</div>
		</nav>

		<a class="smile-btn smile-btn--sun smile-header__donate" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php echo $smile_heart; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( $smile_donate ); ?></a>

		<button class="smile-nav-toggle" type="button" aria-expanded="false" aria-controls="smile-primary-nav">
			<span class="smile-nav-toggle__bar" aria-hidden="true"></span>
			<span class="screen-reader-text smile-nav-toggle__label"><?php esc_html_e( 'Open menu', 'smile-community' ); ?></span>
		</button>
	</div>
</header>
