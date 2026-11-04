<?php
/**
 * Online donations with Paystack: one-time and monthly recurring gifts.
 *
 * How it works
 * ------------
 * 1. The donor fills in [smile_donate_form] (frequency, currency, amount, optional fee
 *    cover, name, email). The form posts to admin-post.php?action=smile_donate.
 * 2. The server validates everything again, records a pending donation and calls
 *    Paystack /transaction/initialize. Monthly gifts pass a Paystack plan (created once
 *    per currency and amount), so Paystack starts a subscription after the first payment.
 * 3. The donor pays on Paystack's own checkout page (cards, M-Pesa, bank and so on, as the
 *    account allows). Card details never touch this website, so PCI scope stays with Paystack.
 * 4. Paystack sends the donor back with ?reference=. The server verifies the payment with
 *    /transaction/verify and checks that the amount and currency match the record before
 *    marking it paid and sending a receipt.
 * 5. The webhook (/wp-json/smile/v1/paystack) is the source of truth for renewals,
 *    new subscriptions, cancellations and failed renewals. Every request is checked with
 *    the HMAC-SHA512 signature Paystack sends, and each reference is processed only once.
 * 6. [smile_manage_giving] lets donors ask Paystack to email them a secure link to update
 *    their card or cancel a monthly gift. The site never reveals whether an email has a gift.
 *
 * Keys live only in wp-config.php (never in the database or in Git):
 *   define( 'SMILE_PAYSTACK_SECRET_KEY', 'sk_live_...' );
 *   define( 'SMILE_PAYSTACK_TEST_SECRET_KEY', 'sk_test_...' );   // optional
 *   define( 'SMILE_PAYSTACK_MODE', 'test' );                      // optional; default live
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * 1. Configuration
 * ---------------------------------------------------------------------- */

/**
 * Current mode: 'test' or 'live'.
 *
 * @return string
 */
function smile_pay_mode() {
	return ( defined( 'SMILE_PAYSTACK_MODE' ) && 'test' === SMILE_PAYSTACK_MODE ) ? 'test' : 'live';
}

/**
 * Secret key for the current mode ('' when not configured).
 *
 * @return string
 */
function smile_pay_secret() {
	if ( 'test' === smile_pay_mode() ) {
		return defined( 'SMILE_PAYSTACK_TEST_SECRET_KEY' ) ? (string) SMILE_PAYSTACK_TEST_SECRET_KEY : '';
	}
	return defined( 'SMILE_PAYSTACK_SECRET_KEY' ) ? (string) SMILE_PAYSTACK_SECRET_KEY : '';
}

/**
 * Online giving is switched on and a key is present.
 *
 * @return bool
 */
function smile_pay_enabled() {
	return '1' === smile_core_get( 'paystack_enabled' ) && '' !== smile_pay_secret();
}

/**
 * Currency rules. Amounts are in major units (KES, USD).
 *
 * @return array
 */
function smile_pay_currencies() {
	$all = array(
		'KES' => array( 'min' => 100, 'max' => 1000000, 'decimals' => 0, 'fee' => (float) smile_core_get( 'paystack_fee_kes' ) ),
		'USD' => array( 'min' => 1, 'max' => 10000, 'decimals' => 2, 'fee' => (float) smile_core_get( 'paystack_fee_usd' ) ),
	);
	$on  = array_filter( array_map( 'trim', explode( ',', strtoupper( smile_core_get( 'paystack_currencies' ) ) ) ) );
	$out = array();
	foreach ( $on as $code ) {
		if ( isset( $all[ $code ] ) ) {
			$out[ $code ] = $all[ $code ];
		}
	}
	return $out ? $out : array( 'KES' => $all['KES'] );
}

/**
 * Preset amounts with an impact line: "amount|what it helps with" per line.
 *
 * @param string $currency Currency code.
 * @return array[] List of array( amount, impact ).
 */
function smile_pay_presets( $currency ) {
	$raw  = smile_core_get( 'paystack_amounts_' . strtolower( $currency ) );
	$list = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$parts  = array_map( 'trim', explode( '|', $line, 2 ) );
		$amount = isset( $parts[0] ) ? (float) str_replace( ',', '', $parts[0] ) : 0;
		if ( $amount > 0 ) {
			$list[] = array( $amount, isset( $parts[1] ) ? $parts[1] : '' );
		}
	}
	return array_slice( $list, 0, 6 );
}

/**
 * Format an amount for display, e.g. "KES 2,500" or "USD 25".
 *
 * @param float  $amount   Major units.
 * @param string $currency Currency code.
 * @return string
 */
function smile_pay_format( $amount, $currency ) {
	$amount   = (float) $amount;
	$decimals = ( 'KES' === $currency || abs( $amount - round( $amount ) ) < 0.005 ) ? 0 : 2;
	return $currency . ' ' . number_format( $amount, $decimals );
}

/**
 * Total to charge, in subunits (cents), optionally grossed up so the processing fee is
 * covered: total = base / (1 - rate). Rounded up so the charity never loses a cent.
 *
 * @param float $base    Gift in major units.
 * @param bool  $cover   Donor chose to cover the fee.
 * @param float $fee_pct Fee percentage (e.g. 3.8).
 * @return int
 */
function smile_pay_charge_subunits( $base, $cover, $fee_pct ) {
	$base_sub = (int) round( $base * 100 );
	if ( ! $cover || $fee_pct <= 0 || $fee_pct >= 50 ) {
		return $base_sub;
	}
	return (int) ceil( $base_sub / ( 1 - $fee_pct / 100 ) );
}

/* -------------------------------------------------------------------------
 * 2. Paystack API
 * ---------------------------------------------------------------------- */

/**
 * Call the Paystack API.
 *
 * @param string     $method GET or POST.
 * @param string     $path   Path such as /transaction/initialize.
 * @param array|null $body   JSON body for POST.
 * @return array|WP_Error Decoded "data" on success.
 */
function smile_pay_api( $method, $path, $body = null ) {
	$secret = smile_pay_secret();
	if ( '' === $secret ) {
		return new WP_Error( 'smile_pay_nokey', 'Paystack is not configured.' );
	}
	$args = array(
		'method'  => $method,
		'timeout' => 20,
		'headers' => array(
			'Authorization' => 'Bearer ' . $secret,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		),
	);
	if ( null !== $body ) {
		$args['body'] = wp_json_encode( $body );
	}
	$res = wp_remote_request( 'https://api.paystack.co' . $path, $args );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$json = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $json ) || empty( $json['status'] ) ) {
		$msg = is_array( $json ) && isset( $json['message'] ) ? (string) $json['message'] : 'Unexpected response';
		return new WP_Error( 'smile_pay_api', $msg );
	}
	return isset( $json['data'] ) ? $json['data'] : array();
}

/**
 * Plan code for a monthly gift of this amount and currency (created once, then reused).
 *
 * @param int    $subunits Amount in subunits.
 * @param string $currency Currency.
 * @return string|WP_Error
 */
function smile_pay_plan_code( $subunits, $currency ) {
	$plans = get_option( 'smile_paystack_plans', array() );
	$plans = is_array( $plans ) ? $plans : array();
	$key   = smile_pay_mode() . '_' . $currency . '_' . (int) $subunits;
	if ( ! empty( $plans[ $key ] ) ) {
		return $plans[ $key ];
	}
	$data = smile_pay_api(
		'POST',
		'/plan',
		array(
			'name'     => sprintf( 'Monthly gift %s', smile_pay_format( $subunits / 100, $currency ) ),
			'interval' => 'monthly',
			'amount'   => (int) $subunits,
			'currency' => $currency,
		)
	);
	if ( is_wp_error( $data ) ) {
		return $data;
	}
	if ( empty( $data['plan_code'] ) ) {
		return new WP_Error( 'smile_pay_plan', 'Plan not created.' );
	}
	$plans[ $key ] = sanitize_text_field( $data['plan_code'] );
	update_option( 'smile_paystack_plans', $plans, false );
	return $plans[ $key ];
}

/* -------------------------------------------------------------------------
 * 3. Donation records (private post type)
 * ---------------------------------------------------------------------- */

/**
 * Register the private donations list (Smile Centre > Donations).
 */
function smile_pay_register_type() {
	register_post_type(
		'smile_donation',
		array(
			'labels'              => array(
				'name'          => __( 'Donations', 'smile-core' ),
				'singular_name' => __( 'Donation', 'smile-core' ),
				'menu_name'     => __( 'Donations', 'smile-core' ),
				'edit_item'     => __( 'Donation', 'smile-core' ),
				'search_items'  => __( 'Search donations', 'smile-core' ),
				'not_found'     => __( 'No donations yet.', 'smile-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => 'smile-centre',
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'capabilities'        => array(
				'edit_post'          => SMILE_CORE_CAP,
				'read_post'          => SMILE_CORE_CAP,
				'delete_post'        => 'manage_options',
				'edit_posts'         => SMILE_CORE_CAP,
				'edit_others_posts'  => SMILE_CORE_CAP,
				'delete_posts'       => 'manage_options',
				'publish_posts'      => 'do_not_allow',
				'read_private_posts' => SMILE_CORE_CAP,
				'create_posts'       => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'smile_pay_register_type' );

/**
 * Donation IDs with a meta value.
 *
 * @param string $key   Meta key.
 * @param string $value Value.
 * @param int    $limit Max results.
 * @return int[]
 */
function smile_pay_by_meta( $key, $value, $limit = 50 ) {
	if ( '' === (string) $value ) {
		return array();
	}
	return array_map(
		'intval',
		get_posts(
			array(
				'post_type'      => 'smile_donation',
				'post_status'    => 'any',
				'posts_per_page' => $limit,
				'fields'         => 'ids',
				'meta_key'       => $key,   // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $value, // phpcs:ignore WordPress.DB.SlowDBQuery
				'no_found_rows'  => true,
			)
		)
	);
}

/**
 * Find a donation by Paystack reference.
 *
 * @param string $reference Reference.
 * @return int Post ID or 0.
 */
function smile_pay_find( $reference ) {
	$ids = smile_pay_by_meta( '_sd_reference', $reference, 1 );
	return $ids ? $ids[0] : 0;
}

/**
 * Create a donation record.
 *
 * @param array $d Fields: reference, name, email, base, charged (subunits), currency, frequency, cover, status, parent, plan.
 * @return int
 */
function smile_pay_create( $d ) {
	$id = wp_insert_post(
		array(
			'post_type'   => 'smile_donation',
			'post_status' => 'private',
			'post_title'  => trim( sprintf( '%s - %s %s', $d['name'] ? $d['name'] : $d['email'], smile_pay_format( $d['charged'] / 100, $d['currency'] ), 'monthly' === $d['frequency'] ? '(monthly)' : '' ) ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	$map = array(
		'_sd_reference' => $d['reference'],
		'_sd_name'      => $d['name'],
		'_sd_email'     => strtolower( $d['email'] ),
		'_sd_base'      => (string) $d['base'],
		'_sd_charged'   => (string) (int) $d['charged'],
		'_sd_currency'  => $d['currency'],
		'_sd_frequency' => $d['frequency'],
		'_sd_cover'     => $d['cover'] ? '1' : '0',
		'_sd_status'    => $d['status'],
		'_sd_mode'      => smile_pay_mode(),
	);
	if ( ! empty( $d['parent'] ) ) {
		$map['_sd_parent'] = (string) (int) $d['parent'];
	}
	if ( ! empty( $d['plan'] ) ) {
		$map['_sd_plan'] = $d['plan'];
	}
	foreach ( $map as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	return (int) $id;
}

/**
 * Mark a donation paid once (idempotent), then send the receipt and staff notice.
 *
 * @param int   $id   Donation ID.
 * @param array $data Paystack transaction data.
 */
function smile_pay_mark_paid( $id, $data ) {
	if ( 'paid' === get_post_meta( $id, '_sd_status', true ) ) {
		return;
	}
	update_post_meta( $id, '_sd_status', 'paid' );
	update_post_meta( $id, '_sd_paid_at', gmdate( 'c' ) );
	if ( ! empty( $data['channel'] ) ) {
		update_post_meta( $id, '_sd_channel', sanitize_key( $data['channel'] ) );
	}
	if ( ! empty( $data['customer']['customer_code'] ) ) {
		update_post_meta( $id, '_sd_customer', sanitize_text_field( $data['customer']['customer_code'] ) );
	}
	smile_pay_send_receipt( $id );
}

/* -------------------------------------------------------------------------
 * 4. Donation form
 * ---------------------------------------------------------------------- */

/**
 * [smile_donate_form] - the donation form.
 *
 * @return string
 */
function smile_pay_form_shortcode() {
	if ( ! smile_pay_enabled() ) {
		return '';
	}
	$currencies = smile_pay_currencies();
	$first      = (string) key( $currencies );
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only.
	$status = isset( $_GET['smile_donation'] ) ? sanitize_key( wp_unslash( $_GET['smile_donation'] ) ) : '';
	$ref    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
	// phpcs:enable
	$return = get_permalink();

	ob_start();
	echo smile_pay_styles(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS.
	echo '<section class="smile-give" id="give" aria-labelledby="smile-give-title">';

	if ( 'thanks' === $status && $ref ) {
		$id = smile_pay_find( $ref );
		if ( $id && 'paid' === get_post_meta( $id, '_sd_status', true ) ) {
			$amount  = smile_pay_format( (int) get_post_meta( $id, '_sd_charged', true ) / 100, get_post_meta( $id, '_sd_currency', true ) );
			$monthly = 'monthly' === get_post_meta( $id, '_sd_frequency', true );
			echo '<div class="smile-give__done" role="status" tabindex="-1"><span class="smile-give__done-icon" aria-hidden="true">' . smile_pay_icon( 'check' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<h2>' . esc_html__( 'Thank you for your gift', 'smile-core' ) . '</h2>';
			/* translators: %s: amount */
			$line = $monthly ? __( 'Your monthly gift of %s is set up. A receipt is on its way to your email.', 'smile-core' ) : __( 'We have received your gift of %s. A receipt is on its way to your email.', 'smile-core' );
			echo '<p>' . esc_html( sprintf( $line, $amount ) ) . '</p>';
			/* translators: %s: reference */
			echo '<p class="smile-give__small">' . esc_html( sprintf( __( 'Reference: %s', 'smile-core' ), $ref ) ) . '</p></div>';
		} else {
			echo '<div class="smile-give__notice" role="status">' . esc_html__( 'We are confirming your payment with Paystack. If it went through, you will receive a receipt by email within a few minutes.', 'smile-core' ) . '</div>';
		}
	} elseif ( 'error' === $status || 'failed' === $status ) {
		$msg = 'failed' === $status
			? __( 'The payment was not completed and no money was taken. Please try again, or use M-Pesa below.', 'smile-core' )
			: __( 'We could not start the payment. Please check the details and try again, or use M-Pesa below.', 'smile-core' );
		echo '<div class="smile-give__notice smile-give__notice--error" role="alert">' . esc_html( $msg ) . '</div>';
	}

	echo '<form class="smile-give__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="smile_donate"><input type="hidden" name="return" value="' . esc_url( $return ) . '">';
	wp_nonce_field( 'smile_donate', 'smile_donate_nonce' );
	echo '<p class="smile-give__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>';

	echo '<div class="smile-give__head"><h2 id="smile-give-title">' . esc_html__( 'Give a smile', 'smile-core' ) . '</h2><p class="smile-give__secure">' . smile_pay_icon( 'lock' ) . esc_html__( 'Secure payment by Paystack', 'smile-core' ) . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	// Frequency.
	echo '<fieldset class="smile-give__seg"><legend class="smile-give__label">' . esc_html__( 'How often?', 'smile-core' ) . '</legend>';
	echo '<label><input type="radio" name="frequency" value="once" checked><span>' . esc_html__( 'Give once', 'smile-core' ) . '</span></label>';
	echo '<label><input type="radio" name="frequency" value="monthly"><span>' . esc_html__( 'Monthly', 'smile-core' ) . ' <em>' . esc_html__( 'Most impact', 'smile-core' ) . '</em></span></label></fieldset>';

	// Currency.
	if ( count( $currencies ) > 1 ) {
		echo '<fieldset class="smile-give__seg smile-give__seg--small"><legend class="smile-give__label">' . esc_html__( 'Currency', 'smile-core' ) . '</legend>';
		foreach ( $currencies as $code => $c ) {
			echo '<label><input type="radio" name="currency" value="' . esc_attr( $code ) . '"' . checked( $code, $first, false ) . '><span>' . esc_html( $code ) . '</span></label>';
		}
		echo '</fieldset>';
	} else {
		echo '<input type="hidden" name="currency" value="' . esc_attr( $first ) . '">';
	}

	// Amounts: one group per currency. CSS shows only the selected currency, even without JavaScript.
	foreach ( $currencies as $code => $c ) {
		$lc      = strtolower( $code );
		$presets = smile_pay_presets( $code );
		echo '<fieldset class="smile-give__amounts smile-give__amounts--' . esc_attr( $lc ) . '" data-currency="' . esc_attr( $code ) . '" data-fee="' . esc_attr( $c['fee'] ) . '"><legend class="smile-give__label">' . esc_html__( 'Choose an amount', 'smile-core' ) . '</legend><div class="smile-give__grid">';
		foreach ( $presets as $i => $p ) {
			echo '<label class="smile-give__tile"><input type="radio" name="amount_' . esc_attr( $lc ) . '" value="' . esc_attr( $p[0] ) . '"' . checked( 1, $i, false ) . '><span class="smile-give__amt">' . esc_html( smile_pay_format( $p[0], $code ) ) . '</span>' . ( $p[1] ? '<span class="smile-give__impact">' . esc_html( $p[1] ) . '</span>' : '' ) . '</label>';
		}
		/* translators: 1: currency 2: minimum amount */
		$aria = sprintf( __( 'Other amount in %1$s (minimum %2$s)', 'smile-core' ), $code, smile_pay_format( $c['min'], $code ) );
		echo '<label class="smile-give__tile smile-give__tile--other"><input type="radio" name="amount_' . esc_attr( $lc ) . '" value="other"><span class="smile-give__amt">' . esc_html__( 'Other amount', 'smile-core' ) . '</span>';
		echo '<span class="smile-give__custom"><span aria-hidden="true">' . esc_html( $code ) . '</span><input type="number" inputmode="decimal" min="' . esc_attr( $c['min'] ) . '" max="' . esc_attr( $c['max'] ) . '" step="' . ( $c['decimals'] ? '0.01' : '1' ) . '" name="custom_' . esc_attr( $lc ) . '" aria-label="' . esc_attr( $aria ) . '" placeholder="' . esc_attr( number_format( $c['min'] ) . '+' ) . '"></span></label>';
		echo '</div></fieldset>';
	}

	// Fee cover (off by default).
	echo '<label class="smile-give__check"><input type="checkbox" name="cover_fee" value="1"><span class="smile-give__fee-text">' . esc_html__( 'Add a little to cover the payment processing fee, so 100% of my gift reaches the children.', 'smile-core' ) . '</span></label>';

	// Donor.
	echo '<div class="smile-give__fields">';
	echo '<p><label for="smile-give-name">' . esc_html__( 'Full name', 'smile-core' ) . '</label><input id="smile-give-name" type="text" name="donor_name" autocomplete="name" required minlength="2" maxlength="100"></p>';
	echo '<p><label for="smile-give-email">' . esc_html__( 'Email (for your receipt)', 'smile-core' ) . '</label><input id="smile-give-email" type="email" name="donor_email" autocomplete="email" required maxlength="190"></p>';
	echo '</div>';

	echo '<button type="submit" class="smile-btn smile-give__submit"><span class="smile-give__submit-text">' . esc_html__( 'Continue to secure payment', 'smile-core' ) . '</span>' . smile_pay_icon( 'arrow' ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p class="smile-give__small">' . esc_html__( 'You pay on Paystack\'s secure page by card, M-Pesa or the other options available in your country. Monthly gifts renew automatically, and you can update or cancel them at any time.', 'smile-core' ) . '</p>';
	echo '</form></section>';
	echo smile_pay_script(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static JS.
	return (string) ob_get_clean();
}
add_shortcode( 'smile_donate_form', 'smile_pay_form_shortcode' );

/**
 * Small inline SVG icons.
 *
 * @param string $name Icon.
 * @return string
 */
function smile_pay_icon( $name ) {
	$paths = array(
		'lock'  => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		'check' => '<path d="M20 6 9 17l-5-5"/>',
		'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
	);
	return '<svg class="smile-give__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( isset( $paths[ $name ] ) ? $paths[ $name ] : '' ) . '</svg>';
}

/**
 * Form styles, printed once with the form so there is no late-loading flash.
 *
 * @return string
 */
function smile_pay_styles() {
	static $done = false;
	if ( $done ) {
		return '';
	}
	$done = true;
	$file = SMILE_CORE_DIR . 'assets/donate.css';
	return is_readable( $file ) ? '<style id="smile-give-css">' . str_ireplace( '</style', '', (string) file_get_contents( $file ) ) . '</style>' : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
}

/**
 * Progressive enhancement: live button label and fee amount. The form works without it.
 *
 * @return string
 */
function smile_pay_script() {
	static $done = false;
	if ( $done ) {
		return '';
	}
	$done = true;
	$file = SMILE_CORE_DIR . 'assets/donate.js';
	return is_readable( $file ) ? '<script id="smile-give-js">' . str_ireplace( '</script', '', (string) file_get_contents( $file ) ) . '</script>' : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local plugin file.
}

/* -------------------------------------------------------------------------
 * 5. Start a payment
 * ---------------------------------------------------------------------- */

/**
 * Send the donor back to the form with a status.
 *
 * @param string $return Return URL.
 * @param string $status Status key.
 * @param string $ref    Reference.
 */
function smile_pay_back( $return, $status, $ref = '' ) {
	$args = array( 'smile_donation' => $status );
	if ( $ref ) {
		$args['ref'] = $ref;
	}
	wp_safe_redirect( add_query_arg( $args, $return ) . '#give' );
	exit;
}

/**
 * Handle the donation form.
 */
function smile_pay_submit() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified just below.
	$return = isset( $_POST['return'] ) ? esc_url_raw( wp_unslash( $_POST['return'] ) ) : home_url( '/donate/' );
	$return = wp_validate_redirect( $return, home_url( '/donate/' ) );
	$nonce  = isset( $_POST['smile_donate_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['smile_donate_nonce'] ) ) : '';
	if ( ! smile_pay_enabled() || ! wp_verify_nonce( $nonce, 'smile_donate' ) || ! empty( $_POST['website'] ) ) {
		smile_pay_back( $return, 'error' );
	}

	// Rate limit: 10 payment starts per hour per visitor.
	$rl    = 'smile_give_' . smile_core_ip_hash();
	$count = (int) get_transient( $rl );
	if ( $count >= (int) apply_filters( 'smile_pay_rate_limit', 10 ) ) {
		smile_pay_back( $return, 'error' );
	}
	set_transient( $rl, $count + 1, HOUR_IN_SECONDS );

	$currencies = smile_pay_currencies();
	$currency   = isset( $_POST['currency'] ) ? strtoupper( sanitize_key( wp_unslash( $_POST['currency'] ) ) ) : '';
	$frequency  = ( isset( $_POST['frequency'] ) && 'monthly' === $_POST['frequency'] ) ? 'monthly' : 'once';
	$cover      = ! empty( $_POST['cover_fee'] );
	$name       = isset( $_POST['donor_name'] ) ? sanitize_text_field( wp_unslash( $_POST['donor_name'] ) ) : '';
	$email      = isset( $_POST['donor_email'] ) ? sanitize_email( wp_unslash( $_POST['donor_email'] ) ) : '';
	if ( ! isset( $currencies[ $currency ] ) || ! is_email( $email ) || strlen( $name ) < 2 || strlen( $name ) > 100 ) {
		smile_pay_back( $return, 'error' );
	}
	$c      = $currencies[ $currency ];
	$lc     = strtolower( $currency );
	$choice = isset( $_POST[ 'amount_' . $lc ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'amount_' . $lc ] ) ) : '';
	$custom = isset( $_POST[ 'custom_' . $lc ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'custom_' . $lc ] ) ) : '';
	// phpcs:enable
	$amount = 'other' === $choice ? (float) str_replace( ',', '', $custom ) : (float) $choice;
	$amount = round( $amount, $c['decimals'] );
	if ( $amount < $c['min'] || $amount > $c['max'] ) {
		smile_pay_back( $return, 'error' );
	}

	$charged   = smile_pay_charge_subunits( $amount, $cover, $c['fee'] );
	$reference = 'SCC-' . strtoupper( wp_generate_password( 14, false, false ) );
	$plan      = '';
	if ( 'monthly' === $frequency ) {
		$plan = smile_pay_plan_code( $charged, $currency );
		if ( is_wp_error( $plan ) ) {
			smile_pay_back( $return, 'error' );
		}
	}
	$id = smile_pay_create(
		array(
			'reference' => $reference,
			'name'      => $name,
			'email'     => $email,
			'base'      => $amount,
			'charged'   => $charged,
			'currency'  => $currency,
			'frequency' => $frequency,
			'cover'     => $cover,
			'status'    => 'pending',
			'plan'      => $plan,
		)
	);
	if ( ! $id ) {
		smile_pay_back( $return, 'error' );
	}
	$body = array(
		'email'        => $email,
		'amount'       => $charged,
		'currency'     => $currency,
		'reference'    => $reference,
		'callback_url' => add_query_arg( 'smile_donation', 'return', $return ),
		'metadata'     => array(
			'donation_id'   => $id,
			'frequency'     => $frequency,
			'cover_fee'     => $cover ? 1 : 0,
			'cancel_action' => add_query_arg( 'smile_donation', 'failed', $return ),
			'custom_fields' => array(
				array(
					'display_name'  => 'Donor',
					'variable_name' => 'donor',
					'value'         => $name,
				),
				array(
					'display_name'  => 'Gift',
					'variable_name' => 'gift',
					'value'         => 'monthly' === $frequency ? 'Monthly' : 'One-time',
				),
			),
		),
	);
	if ( $plan ) {
		$body['plan'] = $plan;
	}
	$data = smile_pay_api( 'POST', '/transaction/initialize', $body );
	$url  = ( is_array( $data ) && ! empty( $data['authorization_url'] ) ) ? (string) $data['authorization_url'] : '';
	if ( is_wp_error( $data ) || 'checkout.paystack.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
		update_post_meta( $id, '_sd_status', 'failed' );
		smile_pay_back( $return, 'error' );
	}
	wp_redirect( esc_url_raw( $url ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- host checked above.
	exit;
}
add_action( 'admin_post_nopriv_smile_donate', 'smile_pay_submit' );
add_action( 'admin_post_smile_donate', 'smile_pay_submit' );

/* -------------------------------------------------------------------------
 * 6. Return from Paystack: verify on the server
 * ---------------------------------------------------------------------- */

/**
 * Verify a transaction against our record. Returns true when it is paid and matches.
 *
 * @param string $reference Reference.
 * @return bool
 */
function smile_pay_verify( $reference ) {
	$id = smile_pay_find( $reference );
	if ( ! $id ) {
		return false;
	}
	if ( 'paid' === get_post_meta( $id, '_sd_status', true ) ) {
		return true;
	}
	$data = smile_pay_api( 'GET', '/transaction/verify/' . rawurlencode( $reference ) );
	if ( is_wp_error( $data ) || ! is_array( $data ) ) {
		return false;
	}
	$ok = isset( $data['status'], $data['amount'], $data['currency'] )
		&& 'success' === $data['status']
		&& (int) $data['amount'] === (int) get_post_meta( $id, '_sd_charged', true )
		&& strtoupper( (string) $data['currency'] ) === get_post_meta( $id, '_sd_currency', true );
	if ( $ok ) {
		smile_pay_mark_paid( $id, $data );
	} elseif ( isset( $data['status'] ) && in_array( $data['status'], array( 'failed', 'abandoned', 'reversed' ), true ) ) {
		update_post_meta( $id, '_sd_status', sanitize_key( $data['status'] ) );
	}
	return $ok;
}

/**
 * Handle ?smile_donation=return&reference=... and send the donor to a clean thank-you URL.
 * Pages showing a donation result are never cached.
 */
function smile_pay_handle_return() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only, verified with Paystack.
	if ( empty( $_GET['smile_donation'] ) && empty( $_GET['smile_manage'] ) ) {
		return;
	}
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
	if ( empty( $_GET['smile_donation'] ) || 'return' !== $_GET['smile_donation'] ) {
		return;
	}
	$reference = isset( $_GET['reference'] ) ? sanitize_text_field( wp_unslash( $_GET['reference'] ) ) : '';
	// phpcs:enable
	if ( ! preg_match( '/^SCC-[A-Z0-9]{14}$/', $reference ) ) {
		return;
	}
	$paid = smile_pay_verify( $reference );
	$url  = remove_query_arg( array( 'smile_donation', 'reference', 'trxref' ) );
	wp_safe_redirect( add_query_arg( array( 'smile_donation' => $paid ? 'thanks' : 'failed', 'ref' => $reference ), $url ) . '#give' );
	exit;
}
add_action( 'template_redirect', 'smile_pay_handle_return', 0 );

/* -------------------------------------------------------------------------
 * 7. Webhook
 * ---------------------------------------------------------------------- */

/**
 * Register POST /wp-json/smile/v1/paystack.
 */
function smile_pay_routes() {
	register_rest_route(
		'smile/v1',
		'/paystack',
		array(
			'methods'             => 'POST',
			'callback'            => 'smile_pay_webhook',
			'permission_callback' => '__return_true', // Authenticated by the HMAC signature in smile_pay_webhook().
		)
	);
}
add_action( 'rest_api_init', 'smile_pay_routes' );

/**
 * Is this webhook body signed by Paystack?
 *
 * @param string $body      Raw body.
 * @param string $signature x-paystack-signature header.
 * @param string $secret    Secret key.
 * @return bool
 */
function smile_pay_valid_signature( $body, $signature, $secret ) {
	if ( '' === $secret || '' === $signature ) {
		return false;
	}
	return hash_equals( hash_hmac( 'sha512', $body, $secret ), strtolower( $signature ) );
}

/**
 * Handle a Paystack event.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function smile_pay_webhook( $request ) {
	$body = (string) $request->get_body();
	if ( strlen( $body ) > 200000 || ! smile_pay_valid_signature( $body, (string) $request->get_header( 'x-paystack-signature' ), smile_pay_secret() ) ) {
		return new WP_REST_Response( array( 'ok' => false ), 401 );
	}
	$event = json_decode( $body, true );
	$type  = isset( $event['event'] ) ? (string) $event['event'] : '';
	$data  = ( isset( $event['data'] ) && is_array( $event['data'] ) ) ? $event['data'] : array();
	$email = isset( $data['customer']['email'] ) ? strtolower( sanitize_email( $data['customer']['email'] ) ) : '';
	$plan  = isset( $data['plan']['plan_code'] ) ? sanitize_text_field( $data['plan']['plan_code'] ) : '';

	switch ( $type ) {
		case 'charge.success':
			$ref = isset( $data['reference'] ) ? sanitize_text_field( $data['reference'] ) : '';
			if ( smile_pay_find( $ref ) ) {
				smile_pay_verify( $ref ); // Re-check with the API; never trust the event body alone.
			} elseif ( $plan && $email && $ref ) {
				smile_pay_record_renewal( $ref, $email, $plan );
			}
			break;

		case 'subscription.create':
			$parent = ( $email && $plan ) ? smile_pay_subscription_parent( $email, $plan ) : 0;
			if ( $parent ) {
				update_post_meta( $parent, '_sd_subscription', sanitize_text_field( isset( $data['subscription_code'] ) ? $data['subscription_code'] : '' ) );
				update_post_meta( $parent, '_sd_sub_status', 'active' );
			}
			break;

		case 'subscription.disable':
		case 'subscription.not_renew':
		case 'invoice.payment_failed':
			$code = '';
			if ( isset( $data['subscription_code'] ) ) {
				$code = $data['subscription_code'];
			} elseif ( isset( $data['subscription']['subscription_code'] ) ) {
				$code = $data['subscription']['subscription_code'];
			}
			$code = sanitize_text_field( (string) $code );
			if ( $code ) {
				$status = 'invoice.payment_failed' === $type ? 'payment_failed' : ( 'subscription.not_renew' === $type ? 'ending' : 'cancelled' );
				foreach ( smile_pay_by_meta( '_sd_subscription', $code ) as $sid ) {
					update_post_meta( $sid, '_sd_sub_status', $status );
				}
				if ( 'invoice.payment_failed' === $type ) {
					smile_pay_notify_staff( sprintf( 'A monthly gift renewal failed (subscription %s). Paystack will retry and email the donor.', $code ) );
				}
			}
			break;
	}
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/**
 * Record a monthly renewal (Paystack creates a new reference for each renewal charge).
 * Verified with the API before it is recorded; each reference is recorded only once.
 *
 * @param string $ref   Reference.
 * @param string $email Donor email.
 * @param string $plan  Plan code.
 */
function smile_pay_record_renewal( $ref, $email, $plan ) {
	$parent = smile_pay_subscription_parent( $email, $plan );
	if ( ! $parent || smile_pay_find( $ref ) ) {
		return;
	}
	$verify = smile_pay_api( 'GET', '/transaction/verify/' . rawurlencode( $ref ) );
	if ( is_wp_error( $verify ) || ! is_array( $verify ) || ! isset( $verify['status'], $verify['amount'], $verify['currency'] ) || 'success' !== $verify['status'] ) {
		return;
	}
	$new = smile_pay_create(
		array(
			'reference' => $ref,
			'name'      => get_post_meta( $parent, '_sd_name', true ),
			'email'     => $email,
			'base'      => get_post_meta( $parent, '_sd_base', true ),
			'charged'   => (int) $verify['amount'],
			'currency'  => strtoupper( (string) $verify['currency'] ),
			'frequency' => 'monthly',
			'cover'     => '1' === get_post_meta( $parent, '_sd_cover', true ),
			'status'    => 'pending',
			'parent'    => $parent,
			'plan'      => $plan,
		)
	);
	if ( $new ) {
		smile_pay_mark_paid( $new, $verify );
	}
}

/**
 * The first monthly donation for an email and plan (the "parent" of renewals).
 *
 * @param string $email Email.
 * @param string $plan  Plan code.
 * @return int
 */
function smile_pay_subscription_parent( $email, $plan ) {
	$ids = get_posts(
		array(
			'post_type'      => 'smile_donation',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => '_sd_email',
					'value' => strtolower( $email ),
				),
				array(
					'key'   => '_sd_plan',
					'value' => $plan,
				),
				array(
					'key'     => '_sd_parent',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

/* -------------------------------------------------------------------------
 * 8. Manage a monthly gift
 * ---------------------------------------------------------------------- */

/**
 * [smile_manage_giving] - ask Paystack to email a secure manage/cancel link.
 *
 * @return string
 */
function smile_pay_manage_shortcode() {
	if ( ! smile_pay_enabled() ) {
		return '';
	}
	$sent = isset( $_GET['smile_manage'] ) && 'sent' === $_GET['smile_manage']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$out  = smile_pay_styles() . '<section class="smile-give smile-give--manage" id="manage" aria-labelledby="smile-manage-title"><h2 id="smile-manage-title">' . esc_html__( 'Manage your monthly gift', 'smile-core' ) . '</h2>';
	if ( $sent ) {
		$out .= '<div class="smile-give__notice" role="status">' . esc_html__( 'If we found a monthly gift for that email address, Paystack has sent it a secure link to update your card or cancel. Please check your inbox.', 'smile-core' ) . '</div>';
	}
	$out .= '<p>' . esc_html__( 'Enter the email you used to give. Paystack will email you a secure link to change your card or cancel your monthly gift.', 'smile-core' ) . '</p>';
	$out .= '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="smile-give__inline"><input type="hidden" name="action" value="smile_manage_giving"><input type="hidden" name="return" value="' . esc_url( get_permalink() ) . '">' . wp_nonce_field( 'smile_manage_giving', 'smile_manage_nonce', false, false );
	$out .= '<label class="screen-reader-text" for="smile-manage-email">' . esc_html__( 'Email address', 'smile-core' ) . '</label><input id="smile-manage-email" type="email" name="email" required autocomplete="email" placeholder="' . esc_attr__( 'Your email address', 'smile-core' ) . '"><button type="submit" class="smile-btn smile-give__ghost">' . esc_html__( 'Email me a link', 'smile-core' ) . '</button></form></section>';
	return $out;
}
add_shortcode( 'smile_manage_giving', 'smile_pay_manage_shortcode' );

/**
 * Handle the manage form. Always shows the same message, so it never reveals donors.
 */
function smile_pay_manage_submit() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified below.
	$return = isset( $_POST['return'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['return'] ) ), home_url( '/donate/' ) ) : home_url( '/donate/' );
	$nonce  = isset( $_POST['smile_manage_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['smile_manage_nonce'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';
	// phpcs:enable
	$rl    = 'smile_manage_' . smile_core_ip_hash();
	$count = (int) get_transient( $rl );
	if ( smile_pay_enabled() && wp_verify_nonce( $nonce, 'smile_manage_giving' ) && is_email( $email ) && $count < 5 ) {
		set_transient( $rl, $count + 1, HOUR_IN_SECONDS );
		foreach ( smile_pay_by_meta( '_sd_email', $email ) as $id ) {
			$code = get_post_meta( $id, '_sd_subscription', true );
			if ( $code && 'cancelled' !== get_post_meta( $id, '_sd_sub_status', true ) ) {
				smile_pay_api( 'POST', '/subscription/' . rawurlencode( $code ) . '/manage/email' );
			}
		}
	}
	wp_safe_redirect( add_query_arg( 'smile_manage', 'sent', $return ) . '#manage' );
	exit;
}
add_action( 'admin_post_nopriv_smile_manage_giving', 'smile_pay_manage_submit' );
add_action( 'admin_post_smile_manage_giving', 'smile_pay_manage_submit' );

/* -------------------------------------------------------------------------
 * 9. Emails
 * ---------------------------------------------------------------------- */

/**
 * Email the donor a receipt (once) and tell staff.
 *
 * @param int $id Donation ID.
 */
function smile_pay_send_receipt( $id ) {
	if ( get_post_meta( $id, '_sd_receipt_sent', true ) ) {
		return;
	}
	$email   = get_post_meta( $id, '_sd_email', true );
	$name    = get_post_meta( $id, '_sd_name', true );
	$amount  = smile_pay_format( (int) get_post_meta( $id, '_sd_charged', true ) / 100, get_post_meta( $id, '_sd_currency', true ) );
	$monthly = 'monthly' === get_post_meta( $id, '_sd_frequency', true );
	$renewal = (bool) get_post_meta( $id, '_sd_parent', true );
	$org     = smile_core_get( 'org_name' );
	$ref     = get_post_meta( $id, '_sd_reference', true );
	$manage  = smile_core_get( 'paystack_manage_url' );
	$subject = $renewal ? sprintf( 'Your monthly gift to %s - thank you', $org ) : sprintf( 'Thank you for your gift to %s', $org );
	$rows    = array(
		'Amount'    => $amount . ( $monthly ? ' every month' : '' ),
		'Date'      => wp_date( get_option( 'date_format' ) ),
		'Reference' => $ref,
	);
	$table   = '';
	foreach ( $rows as $k => $v ) {
		$table .= '<tr><td style="padding:6px 16px 6px 0;color:#5D6A64">' . esc_html( $k ) . '</td><td style="padding:6px 0;font-weight:600;color:#0B4021">' . esc_html( $v ) . '</td></tr>';
	}
	$html  = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;padding:24px;color:#1F2A26;line-height:1.6">';
	$html .= '<p style="margin:0 0 4px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#146C3C">' . esc_html( $org ) . '</p>';
	$html .= '<h1 style="font-size:22px;color:#0B4021;margin:0 0 12px">' . esc_html( sprintf( 'Thank you, %s', $name ? $name : 'friend' ) ) . '</h1>';
	$html .= '<p>' . esc_html( $monthly ? 'Your monthly gift helps us plan ahead: daily meals, school fees and safe care for the children at Smile, month after month.' : 'Your gift helps provide daily meals, schooling and safe care for the children at Smile.' ) . '</p>';
	$html .= '<table style="border-collapse:collapse;margin:16px 0">' . $table . '</table>';
	if ( $monthly && $manage ) {
		$html .= '<p style="font-size:14px;color:#5D6A64">Need to update your card or cancel? <a href="' . esc_url( $manage ) . '" style="color:#146C3C">Manage your monthly gift</a>.</p>';
	}
	$html .= '<p style="font-size:13px;color:#5D6A64;border-top:1px solid #E4DCCB;padding-top:12px;margin-top:24px">' . esc_html( $org . ' | ' . smile_core_get( 'postal_address' ) . ' | ' . smile_core_get( 'email' ) ) . '</p></div>';
	$sent  = wp_mail( $email, $subject, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
	if ( $sent ) {
		update_post_meta( $id, '_sd_receipt_sent', gmdate( 'c' ) );
	}
	$kind = $renewal ? 'monthly renewal' : ( $monthly ? 'new monthly' : 'one-time' );
	smile_pay_notify_staff( sprintf( "New %s donation: %s from %s <%s>.\nReference: %s\n%s", $kind, $amount, $name, $email, $ref, admin_url( 'post.php?post=' . $id . '&action=edit' ) ) );
}

/**
 * Plain-text notice to the donations contacts.
 *
 * @param string $text Message.
 */
function smile_pay_notify_staff( $text ) {
	$to = smile_core_get( 'paystack_notify_email' );
	$to = $to ? $to : smile_core_get( 'notify_email' );
	$to = array_filter( array_map( 'trim', explode( ',', $to ) ), 'is_email' );
	if ( $to ) {
		wp_mail( $to, '[Smile website] Donation', $text, array( 'Content-Type: text/plain; charset=UTF-8' ) );
	}
}

/* -------------------------------------------------------------------------
 * 10. Admin: list columns, CSV export, key status
 * ---------------------------------------------------------------------- */

/**
 * Columns.
 *
 * @param array $cols Columns.
 * @return array
 */
function smile_pay_columns( $cols ) {
	return array(
		'cb'           => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'        => __( 'Donor', 'smile-core' ),
		'sd_amount'    => __( 'Amount', 'smile-core' ),
		'sd_frequency' => __( 'Type', 'smile-core' ),
		'sd_status'    => __( 'Status', 'smile-core' ),
		'sd_reference' => __( 'Reference', 'smile-core' ),
		'date'         => __( 'Date', 'smile-core' ),
	);
}
add_filter( 'manage_smile_donation_posts_columns', 'smile_pay_columns' );

/**
 * Column values.
 *
 * @param string $col     Column.
 * @param int    $post_id Post.
 */
function smile_pay_column_values( $col, $post_id ) {
	switch ( $col ) {
		case 'sd_amount':
			echo esc_html( smile_pay_format( (int) get_post_meta( $post_id, '_sd_charged', true ) / 100, get_post_meta( $post_id, '_sd_currency', true ) ) );
			break;
		case 'sd_frequency':
			$f = get_post_meta( $post_id, '_sd_frequency', true );
			echo esc_html( 'monthly' === $f ? ( get_post_meta( $post_id, '_sd_parent', true ) ? 'Monthly (renewal)' : 'Monthly' ) : 'One-time' );
			$s = get_post_meta( $post_id, '_sd_sub_status', true );
			if ( $s ) {
				echo '<br><small>' . esc_html( 'Subscription: ' . $s ) . '</small>';
			}
			break;
		case 'sd_status':
			echo esc_html( ucfirst( (string) get_post_meta( $post_id, '_sd_status', true ) ) . ( 'test' === get_post_meta( $post_id, '_sd_mode', true ) ? ' (test)' : '' ) );
			break;
		case 'sd_reference':
			echo '<code>' . esc_html( get_post_meta( $post_id, '_sd_reference', true ) ) . '</code>';
			break;
	}
}
add_action( 'manage_smile_donation_posts_custom_column', 'smile_pay_column_values', 10, 2 );

/**
 * "Export CSV" button above the list.
 *
 * @param string $which Top or bottom.
 */
function smile_pay_export_button( $which ) {
	global $typenow;
	if ( 'smile_donation' !== $typenow || 'top' !== $which || ! current_user_can( SMILE_CORE_CAP ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=smile_donations_export' ), 'smile_donations_export' );
	echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Export CSV', 'smile-core' ) . '</a>';
}
add_action( 'manage_posts_extra_tablenav', 'smile_pay_export_button' );

/**
 * One CSV cell, safe from spreadsheet formula injection.
 *
 * @param mixed $v Value.
 * @return string
 */
function smile_pay_csv_cell( $v ) {
	$v = (string) $v;
	return preg_match( '/^[=+\-@\t\r]/', $v ) ? "'" . $v : $v;
}

/**
 * Stream the donations as CSV.
 */
function smile_pay_export() {
	if ( ! current_user_can( SMILE_CORE_CAP ) ) {
		wp_die( esc_html__( 'Not allowed.', 'smile-core' ), 403 );
	}
	check_admin_referer( 'smile_donations_export' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=smile-donations-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fputcsv( $out, array( 'Date', 'Name', 'Email', 'Amount', 'Currency', 'Gift', 'Fee covered', 'Status', 'Subscription', 'Reference', 'Mode' ) );
	$ids = get_posts(
		array(
			'post_type'      => 'smile_donation',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	foreach ( $ids as $id ) {
		$row = array(
			get_the_date( 'Y-m-d H:i', $id ),
			get_post_meta( $id, '_sd_name', true ),
			get_post_meta( $id, '_sd_email', true ),
			number_format( (int) get_post_meta( $id, '_sd_charged', true ) / 100, 2, '.', '' ),
			get_post_meta( $id, '_sd_currency', true ),
			get_post_meta( $id, '_sd_parent', true ) ? 'monthly renewal' : get_post_meta( $id, '_sd_frequency', true ),
			'1' === get_post_meta( $id, '_sd_cover', true ) ? 'yes' : 'no',
			get_post_meta( $id, '_sd_status', true ),
			get_post_meta( $id, '_sd_sub_status', true ),
			get_post_meta( $id, '_sd_reference', true ),
			get_post_meta( $id, '_sd_mode', true ),
		);
		fputcsv( $out, array_map( 'smile_pay_csv_cell', $row ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_smile_donations_export', 'smile_pay_export' );

/**
 * Key status line for the settings screen (never prints the key).
 *
 * @return string
 */
function smile_pay_key_status() {
	return sprintf(
		/* translators: 1: mode 2: key status 3: webhook URL */
		__( 'Paystack mode: %1$s. Secret key in wp-config.php: %2$s. Webhook URL for Paystack > Settings > API Keys & Webhooks: %3$s', 'smile-core' ),
		smile_pay_mode(),
		'' !== smile_pay_secret() ? __( 'found', 'smile-core' ) : __( 'missing (online giving stays hidden)', 'smile-core' ),
		rest_url( 'smile/v1/paystack' )
	);
}
