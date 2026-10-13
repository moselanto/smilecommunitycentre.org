<?php
/**
 * Security hardening for the Smile Community Centre site.
 *
 * Everything here is site-wide, has no settings screen, and can be switched off
 * with a filter if a host or plugin needs different behaviour:
 *
 *   add_filter( 'smile_security_headers', '__return_false' );      // HTTP security headers
 *   add_filter( 'smile_security_force_https', '__return_false' );  // HTTP to HTTPS redirect
 *   add_filter( 'smile_security_disable_xmlrpc', '__return_false' ); // keep XML-RPC on
 *   add_filter( 'smile_security_login_limit', '__return_false' );  // login throttling
 *
 * Sections:
 *   1. HTTP security headers
 *   2. HTTPS everywhere
 *   3. Reduce information leaks (version, user enumeration, XML-RPC)
 *   4. Login protection (throttling, generic errors)
 *   5. Sessions and passwords
 *   6. REST API CORS
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

/**
 * Keyed hash of the visitor IP. The raw IP is never stored.
 *
 * @return string
 */
function smile_security_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	return substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 32 );
}

/* -------------------------------------------------------------------------
 * 1. HTTP security headers
 * ---------------------------------------------------------------------- */

/**
 * Header set used on the front end, the login screen and the dashboard.
 *
 * The Content-Security-Policy is deliberately limited to directives that cannot
 * break Elementor or WordPress (no script-src): it blocks plugins/Flash objects,
 * <base> hijacking, framing by other sites and form posts to other sites.
 *
 * @return array
 */
function smile_security_header_list() {
	$headers = array(
		'X-Content-Type-Options'       => 'nosniff',
		'X-Frame-Options'              => 'SAMEORIGIN',
		'Referrer-Policy'              => 'strict-origin-when-cross-origin',
		'Permissions-Policy'           => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
		'Cross-Origin-Opener-Policy'   => 'same-origin-allow-popups',
		'X-Permitted-Cross-Domain-Policies' => 'none',
		'Content-Security-Policy'      => "object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self' https://wa.me https://api.whatsapp.com" . ( is_ssl() ? '; upgrade-insecure-requests' : '' ),
	);
	if ( is_ssl() ) {
		// One year. "preload" is left out on purpose: only add it once the whole domain is HTTPS-only.
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
	}
	return apply_filters( 'smile_security_header_values', $headers );
}

/**
 * Add the headers to front-end responses.
 *
 * @param array $headers Headers WordPress is about to send.
 * @return array
 */
function smile_security_wp_headers( $headers ) {
	if ( ! apply_filters( 'smile_security_headers', true ) ) {
		return $headers;
	}
	return array_merge( $headers, smile_security_header_list() );
}
add_filter( 'wp_headers', 'smile_security_wp_headers' );

/**
 * Send the same headers on wp-login.php and in wp-admin (wp_headers does not run there).
 */
function smile_security_admin_headers() {
	if ( headers_sent() || ! apply_filters( 'smile_security_headers', true ) ) {
		return;
	}
	foreach ( smile_security_header_list() as $name => $value ) {
		if ( 'Content-Security-Policy' === $name ) {
			continue; // The dashboard and Elementor editor load many third-party tools; keep CSP to the public site.
		}
		header( $name . ': ' . $value );
	}
}
add_action( 'login_init', 'smile_security_admin_headers' );
add_action( 'admin_init', 'smile_security_admin_headers' );

/* -------------------------------------------------------------------------
 * 2. HTTPS everywhere
 * ---------------------------------------------------------------------- */

/**
 * If the site address is https://, send any plain-http front-end request to https://.
 */
function smile_security_force_https() {
	if ( is_ssl() || ! apply_filters( 'smile_security_force_https', true ) ) {
		return;
	}
	if ( 0 !== strpos( home_url(), 'https://' ) || ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() ) {
		return;
	}
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) === strtolower( $host ) ) {
		wp_safe_redirect( 'https://' . $host . $uri, 301, 'Smile security' );
		exit;
	}
}
add_action( 'template_redirect', 'smile_security_force_https', 0 );

/* -------------------------------------------------------------------------
 * 3. Reduce information leaks
 * ---------------------------------------------------------------------- */

// Do not advertise the WordPress version or legacy editing endpoints.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Turn off XML-RPC (an old remote-publishing API that is a common brute-force target).
 * Nothing on this site uses it; the block editor, Elementor and the REST API are unaffected.
 *
 * @param bool $enabled Current state.
 * @return bool
 */
function smile_security_xmlrpc( $enabled ) {
	return apply_filters( 'smile_security_disable_xmlrpc', true ) ? false : $enabled;
}
add_filter( 'xmlrpc_enabled', 'smile_security_xmlrpc' );

/**
 * Remove the X-Pingback header that points at XML-RPC.
 *
 * @param array $headers Headers.
 * @return array
 */
function smile_security_remove_pingback( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'smile_security_remove_pingback', 20 );

/**
 * Stop "?author=1" from revealing login names to visitors.
 */
function smile_security_block_author_scan() {
	if ( is_user_logged_in() || is_admin() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check, no state change.
	if ( isset( $_GET['author'] ) || is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'smile_security_block_author_scan', 1 );

/**
 * Hide the public REST user list (/wp-json/wp/v2/users) from anyone who cannot list users.
 *
 * @param array $endpoints REST endpoints.
 * @return array
 */
function smile_security_rest_users( $endpoints ) {
	if ( current_user_can( 'list_users' ) ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'smile_security_rest_users' );

/**
 * Keep login names out of the public user sitemap.
 *
 * @param WP_Sitemaps_Provider $provider Provider.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function smile_security_sitemap_users( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'smile_security_sitemap_users', 10, 2 );

/* -------------------------------------------------------------------------
 * 4. Login protection
 * ---------------------------------------------------------------------- */

/**
 * Settings for login throttling: 5 failed attempts per 15 minutes per connection.
 *
 * @return array
 */
function smile_security_login_rules() {
	return apply_filters(
		'smile_security_login_rules',
		array(
			'max_attempts' => 5,
			'window'       => 15 * MINUTE_IN_SECONDS,
		)
	);
}

/**
 * Block sign-in while the connection is locked out.
 *
 * @param WP_User|WP_Error|null $user User or error so far.
 * @return WP_User|WP_Error|null
 */
function smile_security_login_gate( $user ) {
	if ( ! apply_filters( 'smile_security_login_limit', true ) ) {
		return $user;
	}
	$rules = smile_security_login_rules();
	$tries = (int) get_transient( 'smile_login_' . smile_security_ip_hash() );
	if ( $tries >= $rules['max_attempts'] ) {
		return new WP_Error(
			'smile_locked',
			sprintf(
				/* translators: %d minutes */
				__( 'Too many failed sign-in attempts. Please wait %d minutes and try again.', 'smile-community' ),
				(int) ceil( $rules['window'] / MINUTE_IN_SECONDS )
			)
		);
	}
	return $user;
}
add_filter( 'authenticate', 'smile_security_login_gate', 30 );

/**
 * Count a failed sign-in.
 */
function smile_security_login_failed() {
	if ( ! apply_filters( 'smile_security_login_limit', true ) ) {
		return;
	}
	$rules = smile_security_login_rules();
	$key   = 'smile_login_' . smile_security_ip_hash();
	set_transient( $key, (int) get_transient( $key ) + 1, $rules['window'] );
}
add_action( 'wp_login_failed', 'smile_security_login_failed' );

/**
 * Clear the counter after a successful sign-in.
 */
function smile_security_login_ok() {
	delete_transient( 'smile_login_' . smile_security_ip_hash() );
}
add_action( 'wp_login', 'smile_security_login_ok' );

/**
 * One generic message for a wrong username or a wrong password, so the login form
 * does not confirm which accounts exist. The lockout message is kept as it is.
 *
 * @param string $error Error HTML.
 * @return string
 */
function smile_security_login_errors( $error ) {
	if ( false !== strpos( (string) $error, 'Too many failed sign-in attempts' ) ) {
		return $error;
	}
	if ( '' === trim( wp_strip_all_tags( (string) $error ) ) ) {
		return $error;
	}
	return '<strong>' . esc_html__( 'Error:', 'smile-community' ) . '</strong> ' . esc_html__( 'The username, email address or password is not correct.', 'smile-community' );
}
add_filter( 'login_errors', 'smile_security_login_errors' );

/* -------------------------------------------------------------------------
 * 5. Sessions and passwords
 * ---------------------------------------------------------------------- */

/**
 * Shorter sign-in sessions: 12 hours, or 7 days with "Remember me"
 * (WordPress defaults are 2 and 14 days).
 *
 * @param int  $seconds  Default length.
 * @param int  $user_id  User ID.
 * @param bool $remember Remember me ticked.
 * @return int
 */
function smile_security_session_length( $seconds, $user_id, $remember ) {
	$length = $remember ? 7 * DAY_IN_SECONDS : 12 * HOUR_IN_SECONDS;
	return (int) apply_filters( 'smile_security_session_length', $length, $user_id, $remember );
}
add_filter( 'auth_cookie_expiration', 'smile_security_session_length', 10, 3 );

/**
 * Minimum password length of 12 characters for new or changed passwords.
 * WordPress itself stores passwords as salted hashes (bcrypt from WordPress 6.8).
 *
 * @param WP_Error $errors Errors.
 * @param mixed    $user   User data (object on profile update, WP_User on reset).
 */
function smile_security_password_rules( $errors, $user = null ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WordPress verifies the nonce for these screens.
	$pass = '';
	if ( isset( $_POST['pass1'] ) ) {
		$pass = (string) wp_unslash( $_POST['pass1'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords must not be altered.
	}
	// phpcs:enable
	$min = (int) apply_filters( 'smile_security_password_min', 12 );
	if ( '' !== $pass && strlen( $pass ) < $min ) {
		/* translators: %d minimum length */
		$errors->add( 'smile_pass_short', sprintf( __( 'Please use a password of at least %d characters.', 'smile-community' ), $min ) );
	}
}
add_action( 'user_profile_update_errors', 'smile_security_password_rules', 10, 2 );
add_action( 'validate_password_reset', 'smile_security_password_rules', 10, 2 );

/* -------------------------------------------------------------------------
 * 6. REST API CORS
 * ---------------------------------------------------------------------- */

/**
 * Only this site's own origin may make cross-origin REST calls with cookies.
 * (WordPress by default echoes back any Origin.) Public, cookie-less GET requests still work.
 */
function smile_security_rest_cors() {
	remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	add_filter(
		'rest_pre_serve_request',
		function ( $value ) {
			$origin  = get_http_origin();
			$allowed = array_map( 'untrailingslashit', apply_filters( 'smile_security_cors_origins', array( home_url(), site_url() ) ) );
			$origin_host = $origin ? untrailingslashit( set_url_scheme( $origin, 'https' ) ) : '';
			foreach ( $allowed as $a ) {
				if ( $origin && ( untrailingslashit( $origin ) === $a || untrailingslashit( set_url_scheme( $a, 'https' ) ) === $origin_host ) ) {
					header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
					header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
					header( 'Access-Control-Allow-Credentials: true' );
					header( 'Vary: Origin', false );
					break;
				}
			}
			return $value;
		}
	);
}
add_action( 'rest_api_init', 'smile_security_rest_cors', 15 );
