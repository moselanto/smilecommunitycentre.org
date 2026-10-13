<?php
/**
 * Accessible site header (used when no Elementor Theme Builder header exists).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_phone    = smile_setting( 'phone_primary', '0721 318 869' );
$smile_whatsapp = smile_setting( 'whatsapp', '254721318869' );
$smile_hours    = smile_setting( 'office_hours', __( 'Office: 9am - 5pm', 'smile-community' ) );
?>
<a class="smile-skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'smile-community' ); ?></a>

<?php if ( get_theme_mod( 'smile_show_topbar', true ) ) : ?>
<div class="smile-topbar">
	<div class="smile-container smile-topbar__inner">
		<span class="smile-topbar__hours"><?php echo esc_html( $smile_hours ); ?></span>
		<span class="smile-topbar__links">
			<a href="<?php echo esc_url( smile_tel_href( $smile_phone ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s phone */ __( 'Call %s', 'smile-community' ), $smile_phone ) ); ?></a>
			<?php if ( $smile_whatsapp ) : ?>
				<a href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', $smile_whatsapp ) ); ?>" rel="noopener"><?php esc_html_e( 'WhatsApp', 'smile-community' ); ?></a>
			<?php endif; ?>
		</span>
	</div>
</div>
<?php endif; ?>

<header class="smile-header" id="site-header">
	<div class="smile-container smile-header__inner">
		<div class="smile-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="smile-header__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<button class="smile-nav-toggle" type="button" aria-expanded="false" aria-controls="smile-primary-nav">
			<span class="smile-nav-toggle__bar" aria-hidden="true"></span>
			<span class="screen-reader-text smile-nav-toggle__label"><?php esc_html_e( 'Open menu', 'smile-community' ); ?></span>
		</button>

		<nav class="smile-nav" id="smile-primary-nav" aria-label="<?php esc_attr_e( 'Main', 'smile-community' ); ?>">
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
			<a class="smile-btn smile-btn--sun smile-nav__donate-mobile" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php echo esc_html( get_theme_mod( 'smile_donate_label', __( 'Donate', 'smile-community' ) ) ); ?></a>
		</nav>

		<a class="smile-btn smile-btn--sun smile-header__donate" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php echo esc_html( get_theme_mod( 'smile_donate_label', __( 'Donate', 'smile-community' ) ) ); ?></a>
	</div>
</header>
