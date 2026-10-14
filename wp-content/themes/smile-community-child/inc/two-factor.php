<?php
/**
 * Two-step login (two-factor authentication) with the official "Two Factor" plugin.
 *
 * The plugin does the security work (authenticator app codes, recovery codes).
 * This file only helps people use it:
 *   - a dashboard reminder, with the setup steps, for every staff member who has not set it up;
 *   - a reminder to administrators to install the plugin if it is missing;
 *   - the code screen styled like the branded sign-in page.
 * Switch the reminders off with: add_filter( 'smile_2fa_reminder', '__return_false' );
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Two Factor plugin is active.
 *
 * @return bool
 */
function smile_2fa_available() {
	return class_exists( 'Two_Factor_Core' );
}

/**
 * Whether a user already uses two-step login.
 *
 * @param int $user_id User ID.
 * @return bool
 */
function smile_2fa_user_ready( $user_id ) {
	return smile_2fa_available() && method_exists( 'Two_Factor_Core', 'is_user_using_two_factor' ) && Two_Factor_Core::is_user_using_two_factor( $user_id );
}

/**
 * Dashboard reminders.
 */
function smile_2fa_notice() {
	if ( !apply_filters( 'smile_2fa_reminder', true ) || !current_user_can( 'edit_posts' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && in_array( $screen->id, array( 'profile', 'user-edit' ), true ) ) {
		return; // They are on the page where it is set up.
	}
	$user_id = get_current_user_id();
	$snooze  = (int) get_user_meta( $user_id, 'smile_2fa_snooze', true );
	if ( $snooze > time() ) {
		return;
	}

	if ( !smile_2fa_available() ) {
		if ( !current_user_can( 'install_plugins' ) ) {
			return;
		}
		$url = admin_url( 'themes.php?page=smile-setup&step=plugins' );
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Protect the website with two-step login.', 'smile-community' ) . '</strong> ' . esc_html__( 'Install the free, official Two Factor plugin so staff confirm each sign-in with a code from their phone.', 'smile-community' ) . ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Install Two Factor', 'smile-community' ) . '</a> ' . smile_2fa_snooze_link() . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- snooze link escaped in its function.
		return;
	}
	if ( smile_2fa_user_ready( $user_id ) ) {
		return;
	}
	$profile = admin_url( 'profile.php#two-factor-options' );
	?>
	<div class="notice notice-warning smile-2fa-notice">
		<p><strong><?php esc_html_e( 'Turn on two-step login for your account (about 5 minutes).', 'smile-community' ); ?></strong> <?php esc_html_e( 'Nothing changes until you set it up, and you will need your phone.', 'smile-community' ); ?></p>
		<ol>
			<li><?php esc_html_e( 'On your phone, install Google Authenticator or Microsoft Authenticator.', 'smile-community' ); ?></li>
			<li><?php esc_html_e( 'Open Users > Profile and scroll down to Two-Factor Options.', 'smile-community' ); ?></li>
			<li><?php esc_html_e( 'Tick Authenticator App. Scan the QR code with the app, type in the 6-digit code it shows, and click Submit.', 'smile-community' ); ?></li>
			<li><?php esc_html_e( 'Tick Recovery Codes and click Generate. Write the codes down or print them, and keep them somewhere safe away from your phone. If you ever lose your phone, these codes get you back in.', 'smile-community' ); ?></li>
			<li><?php esc_html_e( 'Make Authenticator App your main method, then click Update Profile at the bottom.', 'smile-community' ); ?></li>
			<li><?php esc_html_e( 'Log out and back in. After your password, WordPress will ask for the 6-digit code from the app.', 'smile-community' ); ?></li>
		</ol>
		<p><a class="button button-primary" href="<?php echo esc_url( $profile ); ?>"><?php esc_html_e( 'Set up two-step login', 'smile-community' ); ?></a> <?php echo smile_2fa_snooze_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in its function. ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'smile_2fa_notice' );

/**
 * "Remind me in a week" link (nonce protected).
 *
 * @return string
 */
function smile_2fa_snooze_link() {
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=smile_2fa_snooze' ), 'smile_2fa_snooze' );
	return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Remind me in a week', 'smile-community' ) . '</a>';
}

/**
 * Hide the reminder for 7 days for the current user.
 */
function smile_2fa_snooze() {
	if ( !is_user_logged_in() ) {
		wp_die( esc_html__( 'Please sign in.', 'smile-community' ), 403 );
	}
	check_admin_referer( 'smile_2fa_snooze' );
	update_user_meta( get_current_user_id(), 'smile_2fa_snooze', time() + WEEK_IN_SECONDS );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_smile_2fa_snooze', 'smile_2fa_snooze' );

/**
 * Show on Users > All Users whether each person uses two-step login.
 *
 * @param array $columns Columns.
 * @return array
 */
function smile_2fa_user_column( $columns ) {
	if ( smile_2fa_available() && current_user_can( 'list_users' ) && !isset( $columns['two-factor'] ) ) {
		$columns['smile_2fa'] = __( 'Two-step login', 'smile-community' );
	}
	return $columns;
}
add_filter( 'manage_users_columns', 'smile_2fa_user_column' );

/**
 * Column value.
 *
 * @param string $value   Value.
 * @param string $column  Column.
 * @param int    $user_id User ID.
 * @return string
 */
function smile_2fa_user_column_value( $value, $column, $user_id ) {
	if ( 'smile_2fa' !== $column ) {
		return $value;
	}
	return smile_2fa_user_ready( $user_id ) ? '<span style="color:#1a7f37;font-weight:600">' . esc_html__( 'On', 'smile-community' ) . '</span>' : '<span style="color:#b42318">' . esc_html__( 'Not set up', 'smile-community' ) . '</span>';
}
add_filter( 'manage_users_custom_column', 'smile_2fa_user_column_value', 10, 3 );
