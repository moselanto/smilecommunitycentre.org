<?php
/**
 * 301 redirects from old URLs. Runs only when WordPress would otherwise show a 404,
 * so it can never hide a real page. Editable at Smile Centre > Redirects.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default redirect map (old path => new path), from the URL audit.
 *
 * @return string One rule per line: /old-path/ /new-path/
 */
function smile_core_default_redirects() {
	return implode(
		"\n",
		array(
			'/blog/ /stories/',
			'/photos/ /gallery/',
			'/sitemap/ /',
			'/hiv-and-the-community/ /health-and-wellbeing/',
			'/a-journey-cut-short-reflections-on-the-titanic-submarine-experience/ /stories/',
			'/empowering-kenyan-youth-the-significance-of-the-hustler-fund-in-informal-settlements/ /stories/',
			'/building-bridges-the-shared-commitment-of-comoros-and-kenya-in-supporting-orphaned-and-vulnerable-children/ /stories/',
			'/smile-community-centre-childrens-home-announcement-in-regards-to-the-fifteenth-15th-presidential-address-on-the-coronavirus-pandemic/ /stories/',
		)
	);
}

/**
 * Parse rules.
 *
 * @return array
 */
function smile_core_redirect_rules() {
	$rules = array();
	foreach ( explode( "\n", (string) get_option( 'smile_core_redirects', '' ) ) as $line ) {
		$parts = preg_split( '/\s+/', trim( $line ) );
		if ( count( $parts ) < 2 || 0 !== strpos( $parts[0], '/' ) ) {
			continue;
		}
		$rules[ untrailingslashit( strtolower( $parts[0] ) ) ] = $parts[1];
	}
	return $rules;
}

/**
 * Perform redirect on 404.
 */
function smile_core_maybe_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home && '/' !== $home && 0 === strpos( $path, untrailingslashit( $home ) ) ) {
		$path = substr( $path, strlen( untrailingslashit( $home ) ) ); // Support sub-folder installs (staging).
	}
	$key   = untrailingslashit( strtolower( (string) $path ) );
	$rules = smile_core_redirect_rules();
	if ( '' === $key || ! isset( $rules[ $key ] ) ) {
		return;
	}
	$target = $rules[ $key ];
	$target = 0 === strpos( $target, 'http' ) ? $target : home_url( $target );
	wp_safe_redirect( $target, 301, 'Smile Community Core' );
	exit;
}
add_action( 'template_redirect', 'smile_core_maybe_redirect', 1 );

/**
 * Save redirects.
 */
function smile_core_save_redirects() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'smile-core' ), 403 );
	}
	check_admin_referer( 'smile_core_redirects' );
	$raw = isset( $_POST['smile_redirects'] ) ? sanitize_textarea_field( wp_unslash( $_POST['smile_redirects'] ) ) : '';
	update_option( 'smile_core_redirects', $raw );
	wp_safe_redirect( add_query_arg( array( 'page' => 'smile-redirects', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_smile_core_save_redirects', 'smile_core_save_redirects' );

/**
 * Render redirects screen.
 */
function smile_core_render_redirects() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Redirects from old addresses', 'smile-core' ); ?></h1>
		<p><?php esc_html_e( 'One rule per line: the old path, a space, then the new path or full URL. Rules only apply when the old address no longer exists, so they never hide a live page.', 'smile-core' ); ?></p>
		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'smile-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="smile_core_save_redirects" />
			<?php wp_nonce_field( 'smile_core_redirects' ); ?>
			<label class="screen-reader-text" for="smile-redirects"><?php esc_html_e( 'Redirect rules', 'smile-core' ); ?></label>
			<textarea id="smile-redirects" name="smile_redirects" class="large-text code" rows="18"><?php echo esc_textarea( (string) get_option( 'smile_core_redirects', '' ) ); ?></textarea>
			<?php submit_button( __( 'Save redirects', 'smile-core' ) ); ?>
		</form>
	</div>
	<?php
}
