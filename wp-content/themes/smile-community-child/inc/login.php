<?php
/**
 * Branded staff sign-in screen (wp-login.php).
 *
 * Split layout: a photo story panel on the left (brand, headline, what staff can do,
 * contact line) and a clean sign-in card on the right. Every wording is translatable,
 * so staff can change it in Smile Centre > Site text & photos. Turn the whole design
 * off with: add_filter( 'smile_login_design', '__return_false' );
 *
 * @package smile-community-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the branded sign-in design is on.
 *
 * @return bool
 */
function smile_login_enabled() {
	return (bool) apply_filters( 'smile_login_design', true );
}

/**
 * Styles and fonts for the sign-in screen.
 */
function smile_login_assets() {
	if ( ! smile_login_enabled() ) {
		return;
	}
	$min  = file_exists( SMILE_THEME_DIR . '/assets/build/login.min.css' );
	$file = $min ? '/assets/build/login.min.css' : '/assets/css/login.css';
	wp_enqueue_style( 'smile-login', SMILE_THEME_URI . $file, array( 'login' ), SMILE_THEME_VERSION );
	$photo = SMILE_THEME_URI . '/assets/images/slides/feeding-smiles-1600.webp';
	$small = SMILE_THEME_URI . '/assets/images/slides/feeding-smiles-960.webp';
	wp_add_inline_style(
		'smile-login',
		'.smile-login-aside{--smile-login-photo:url(' . esc_url( $photo ) . ')}@media (max-width:959px){.smile-login-aside{--smile-login-photo:url(' . esc_url( $small ) . ')}}'
	);
}
add_action( 'login_enqueue_scripts', 'smile_login_assets', 20 );

if ( function_exists( 'smile_local_fonts' ) ) {
	add_action( 'login_head', 'smile_local_fonts', 1 );
}

/**
 * Add a body class so every rule is scoped to the branded design.
 *
 * @param array $classes Body classes.
 * @return array
 */
function smile_login_body_class( $classes ) {
	if ( smile_login_enabled() ) {
		$classes[] = 'smile-login';
	}
	return $classes;
}
add_filter( 'login_body_class', 'smile_login_body_class' );

/**
 * The site is English only: hide the language drop-down under the card.
 *
 * @param bool $show Whether to show it.
 * @return bool
 */
function smile_login_language_dropdown( $show ) {
	return smile_login_enabled() ? false : $show;
}
add_filter( 'login_display_language_dropdown', 'smile_login_language_dropdown' );

/**
 * Small check-mark icon.
 *
 * @return string
 */
function smile_login_tick() {
	return '<svg aria-hidden="true" focusable="false" width="18" height="18" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="currentColor" opacity=".18"/><path d="M7 12.5l3.2 3.2L17 9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/**
 * Left story panel, printed before the sign-in box.
 */
function smile_login_aside() {
	if ( ! smile_login_enabled() ) {
		return;
	}
	$phone = function_exists( 'smile_setting' ) ? smile_setting( 'phone_primary', '0721 318 869' ) : '0721 318 869';
	$email = function_exists( 'smile_setting' ) ? smile_setting( 'email', 'info@smilecommunitycentre.org' ) : 'info@smilecommunitycentre.org';
	$items = array(
		__( 'Update pages, stories and photos', 'smile-community' ),
		__( 'Answer enquiries, volunteers and partners', 'smile-community' ),
		__( 'Post jobs and keep giving details current', 'smile-community' ),
	);
	?>
	<aside class="smile-login-aside" aria-label="<?php esc_attr_e( 'About this website', 'smile-community' ); ?>">
		<div class="smile-login-aside__inner">
			<a class="smile-login-aside__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="smile-login-aside__mark"><img src="<?php echo esc_url( smile_brand_url( 'icon-192.png' ) ); ?>" alt="" width="40" height="40"></span>
				<span><?php esc_html_e( 'Smile Community Centre', 'smile-community' ); ?></span>
			</a>
			<div class="smile-login-aside__body">
				<p class="smile-login-aside__eyebrow"><?php esc_html_e( 'Staff and volunteers', 'smile-community' ); ?></p>
				<h2 class="smile-login-aside__title"><?php esc_html_e( 'Every smile online starts with the work you do here.', 'smile-community' ); ?></h2>
				<p class="smile-login-aside__lead"><?php esc_html_e( 'Sign in to look after the website of Smile Community Centre Children\'s Home in Soweto, Kayole.', 'smile-community' ); ?></p>
				<ul class="smile-login-aside__list">
					<?php foreach ( $items as $item ) : ?>
						<li><?php echo smile_login_tick(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed SVG. ?><span><?php echo esc_html( $item ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<p class="smile-login-aside__foot">
				<span><?php esc_html_e( 'Need access or locked out?', 'smile-community' ); ?></span>
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				<?php if ( $email ) : ?>
					<span aria-hidden="true">&middot;</span>
					<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
			</p>
		</div>
	</aside>
	<main class="smile-login-main" id="smile-login-main">
	<?php
}
add_action( 'login_header', 'smile_login_aside' );

/**
 * Heading above the form (inside the card).
 *
 * @param string $message Existing login message.
 * @return string
 */
function smile_login_intro( $message ) {
	if ( ! smile_login_enabled() ) {
		return $message;
	}
	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	if ( 'lostpassword' === $action || 'retrievepassword' === $action ) {
		$title = __( 'Reset your password', 'smile-community' );
		$sub   = __( 'Enter your username or email and we will send you a reset link.', 'smile-community' );
	} elseif ( 'rp' === $action || 'resetpass' === $action ) {
		$title = __( 'Choose a new password', 'smile-community' );
		$sub   = __( 'Use at least 12 characters. A short sentence is easy to remember.', 'smile-community' );
	} elseif ( 'login' === $action ) {
		$title = __( 'Welcome back', 'smile-community' );
		$sub   = __( 'Sign in to manage the Smile Community Centre website.', 'smile-community' );
	} else {
		return $message;
	}
	return '<div class="smile-login-intro"><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $sub ) . '</p></div>' . $message;
}
add_filter( 'login_message', 'smile_login_intro', 5 );

/**
 * Trust line and footer, printed after the sign-in box.
 */
function smile_login_footer() {
	if ( ! smile_login_enabled() ) {
		return;
	}
	?>
	<p class="smile-login-trust">
		<svg aria-hidden="true" focusable="false" width="14" height="14" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/></svg>
		<?php esc_html_e( 'Secure connection. Repeated failed attempts are paused for 15 minutes.', 'smile-community' ); ?>
	</p>
	<p class="smile-login-copy">
		&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Smile Community Centre Children\'s Home, Nairobi', 'smile-community' ); ?>
	</p>
	</main>
	<?php
}
add_action( 'login_footer', 'smile_login_footer', 5 );

/**
 * Clearer labels and placeholders on the sign-in form (front-end only, no core change).
 */
function smile_login_gettext( $translation, $text, $domain ) {
	if ( 'default' !== $domain || ! smile_login_enabled() ) {
		return $translation;
	}
	if ( 'Log In' === $text ) {
		return __( 'Sign in', 'smile-community' );
	}
	return $translation;
}
add_action(
	'login_init',
	function () {
		add_filter( 'gettext', 'smile_login_gettext', 10, 3 );
	}
);
