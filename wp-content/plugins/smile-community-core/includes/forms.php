<?php
/**
 * Public enquiry forms: [smile_form type="contact|visit|volunteer|partner|donation|removal"].
 *
 * Security: nonce, honeypot, signed minimum fill time, per-IP rate limit,
 * server-side validation and sanitisation, PRG redirect, honest status messages.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions per form.
 *
 * @param string $type Form type.
 * @return array key => [label, type, required, options/hint]
 */
function smile_core_form_fields( $type ) {
	$name  = array( __( 'Full name', 'smile-core' ), 'text', true );
	$email = array( __( 'Email address', 'smile-core' ), 'email', true );
	$phone = array( __( 'Phone or WhatsApp', 'smile-core' ), 'tel', false );

	switch ( $type ) {
		case 'visit':
			return array(
				'name'       => $name,
				'email'      => $email,
				'phone'      => array( __( 'Phone or WhatsApp', 'smile-core' ), 'tel', true ),
				'group_size' => array( __( 'Number of visitors', 'smile-core' ), 'text', true ),
				'date'       => array( __( 'Preferred date', 'smile-core' ), 'date', false ),
				'message'    => array( __( 'Purpose of your visit and anything you plan to bring', 'smile-core' ), 'textarea', true ),
			);
		case 'volunteer':
			return array(
				'name'        => $name,
				'email'       => $email,
				'phone'       => $phone,
				'country'     => array( __( 'Country of residence', 'smile-core' ), 'text', true ),
				'age_confirm' => array( __( 'I am 18 years or older', 'smile-core' ), 'checkbox', true ),
				'dates'       => array( __( 'When would you like to volunteer, and for how long?', 'smile-core' ), 'text', true ),
				'skills'      => array( __( 'Skills or experience you would like to share (for example teaching, counselling, sports, cooking, fundraising, administration)', 'smile-core' ), 'textarea', true ),
				'message'     => array( __( 'Why would you like to volunteer with Smile?', 'smile-core' ), 'textarea', false ),
				'conduct'     => array( __( 'I have read the volunteer conduct and safeguarding expectations on this page and agree to follow them', 'smile-core' ), 'checkbox', true ),
			);
		case 'partner':
			return array(
				'name'         => $name,
				'organisation' => array( __( 'Organisation', 'smile-core' ), 'text', true ),
				'email'        => $email,
				'phone'        => $phone,
				'interest'     => array( __( 'Area of interest', 'smile-core' ), 'select', true, array( 'Education and school fees', 'Feeding programme and food donations', 'Essential supplies', 'Corporate social responsibility (CSR) day', 'Employee or skills-based volunteering', 'Recurring support', 'Other' ) ),
				'message'      => array( __( 'Tell us about your idea', 'smile-core' ), 'textarea', true ),
			);
		case 'donation':
			return array(
				'name'     => $name,
				'email'    => $email,
				'phone'    => $phone,
				'area'     => array( __( 'What would you like to support?', 'smile-core' ), 'select', true, array( 'Where the need is greatest', 'School fees and learning materials', 'Daily meals and water', 'Medical care', 'Food, clothing or supplies (in kind)' ) ),
				'method'   => array( __( 'How are you giving?', 'smile-core' ), 'select', true, array( 'M-Pesa (already sent)', 'M-Pesa (planning to send)', 'Bank transfer', 'In-kind donation (goods)', 'I have a question first' ) ),
				'reference'=> array( __( 'M-Pesa or bank reference (optional, helps us confirm your gift)', 'smile-core' ), 'text', false ),
				'message'  => array( __( 'Message', 'smile-core' ), 'textarea', false ),
			);
		case 'removal':
			return array(
				'name'         => $name,
				'email'        => $email,
				'phone'        => $phone,
				'relationship' => array( __( 'Your relationship to the person concerned', 'smile-core' ), 'select', true, array( 'I am the person in the photo or story', 'Parent or guardian', 'Smile staff member', 'Social worker or children\'s officer', 'Other concern' ) ),
				'page_url'     => array( __( 'Link to the page or image (if known)', 'smile-core' ), 'url', false ),
				'message'      => array( __( 'What should we review or remove, and why?', 'smile-core' ), 'textarea', true ),
			);
		case 'contact':
		default:
			return array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => $phone,
				'subject' => array( __( 'Subject', 'smile-core' ), 'select', true, array( 'General question', 'Donations', 'Visiting the centre', 'Volunteering', 'Partnerships', 'Media', 'Other' ) ),
				'message' => array( __( 'Message', 'smile-core' ), 'textarea', true ),
			);
	}
}

/**
 * Hash of the visitor IP (we never store the raw IP).
 *
 * @return string
 */
function smile_core_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	return hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
}

/**
 * Render a form.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function smile_core_form_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'type' => 'contact', 'button' => '' ), $atts, 'smile_form' );
	$types = smile_core_form_types();
	$type  = isset( $types[ $atts['type'] ] ) ? $atts['type'] : 'contact';
	$uid   = 'smile-form-' . $type;

	// Read back state after redirect.
	$state  = array( 'values' => array(), 'errors' => array() );
	$status = '';
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display state.
	if ( isset( $_GET['smile_form'], $_GET['smile_status'] ) && $type === sanitize_key( wp_unslash( $_GET['smile_form'] ) ) ) {
		$status = sanitize_key( wp_unslash( $_GET['smile_status'] ) );
		if ( isset( $_GET['smile_token'] ) ) {
			$token = sanitize_key( wp_unslash( $_GET['smile_token'] ) );
			$saved = get_transient( 'smile_form_' . $token );
			if ( is_array( $saved ) ) {
				$state = $saved;
				delete_transient( 'smile_form_' . $token );
			}
		}
	}
	// phpcs:enable

	$out = '<div class="smile-form-wrap" id="' . esc_attr( $uid ) . '">';
	$out .= '<div id="smile-live" class="screen-reader-text" aria-live="polite"></div>';

	if ( 'ok' === $status ) {
		$out .= '<div class="smile-form__status smile-form__status--ok" role="status" tabindex="-1"><p><strong>' . esc_html__( 'Thank you. Your message has been received.', 'smile-core' ) . '</strong></p><p>' . esc_html__( 'A member of the Smile team will reply as soon as possible, usually within a few working days.', 'smile-core' ) . '</p></div></div>';
		return $out;
	}
	if ( 'saved' === $status ) {
		$out .= '<div class="smile-form__status smile-form__status--warn" role="status" tabindex="-1"><p><strong>' . esc_html__( 'Your message has been saved, but our email notification did not go through.', 'smile-core' ) . '</strong></p><p>' . sprintf(
			/* translators: %s phone */
			esc_html__( 'Our team checks saved messages regularly. If it is urgent, please also call or WhatsApp %s.', 'smile-core' ),
			esc_html( smile_core_get( 'phone_primary' ) )
		) . '</p></div></div>';
		return $out;
	}
	if ( 'limit' === $status ) {
		$out .= '<div class="smile-form__status smile-form__status--error" role="alert" tabindex="-1"><p>' . esc_html__( 'We have received several messages from this connection in the last hour. Please wait a little and try again, or contact us by phone or email.', 'smile-core' ) . '</p></div>';
	}
	if ( 'expired' === $status ) {
		$out .= '<div class="smile-form__status smile-form__status--error" role="alert" tabindex="-1"><p>' . esc_html__( 'The form expired before it was sent. Please check your details and send it again.', 'smile-core' ) . '</p></div>';
	}
	if ( 'invalid' === $status && ! empty( $state['errors'] ) ) {
		$out .= '<div class="smile-form__status smile-form__status--error" role="alert" tabindex="-1"><p><strong>' . esc_html__( 'Please correct the following:', 'smile-core' ) . '</strong></p><ul>';
		foreach ( $state['errors'] as $key => $msg ) {
			$out .= '<li><a href="#' . esc_attr( $uid . '-' . $key ) . '">' . esc_html( $msg ) . '</a></li>';
		}
		$out .= '</ul></div>';
	}

	$out .= '<form class="smile-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" novalidate>';
	$out .= '<input type="hidden" name="action" value="smile_form_submit" />';
	$out .= '<input type="hidden" name="smile_type" value="' . esc_attr( $type ) . '" />';
	$out .= '<input type="hidden" name="smile_return" value="' . esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ) . '" />';
	$t    = time();
	$out .= '<input type="hidden" name="smile_ts" value="' . esc_attr( $t . '.' . hash_hmac( 'sha256', $t . $type, wp_salt( 'nonce' ) ) ) . '" />';
	$out .= wp_nonce_field( 'smile_form_' . $type, 'smile_nonce', false, false );
	$out .= '<div class="smile-form__hp" aria-hidden="true"><label for="' . esc_attr( $uid ) . '-website">Website</label><input type="text" id="' . esc_attr( $uid ) . '-website" name="smile_website" tabindex="-1" autocomplete="off" /></div>';

	$out .= '<p class="smile-form__hint">' . esc_html__( 'Fields marked * are required.', 'smile-core' ) . '</p>';

	foreach ( smile_core_form_fields( $type ) as $key => $def ) {
		list( $label, $ftype, $required ) = $def;
		$id    = $uid . '-' . $key;
		$val   = isset( $state['values'][ $key ] ) ? $state['values'][ $key ] : '';
		$err   = isset( $state['errors'][ $key ] ) ? $state['errors'][ $key ] : '';
		$aria  = $err ? ' aria-invalid="true" aria-describedby="' . esc_attr( $id ) . '-err"' : '';
		$req   = $required ? ' required aria-required="true"' : '';
		$star  = $required ? ' <span class="smile-req" aria-hidden="true">*</span>' : '';
		$auto  = array( 'name' => 'name', 'email' => 'email', 'phone' => 'tel', 'organisation' => 'organization', 'country' => 'country-name' );
		$ac    = isset( $auto[ $key ] ) ? ' autocomplete="' . esc_attr( $auto[ $key ] ) . '"' : '';

		$out .= '<div class="smile-form__field">';
		if ( 'checkbox' === $ftype ) {
			$out .= '<label class="smile-form__check" for="' . esc_attr( $id ) . '"><input type="checkbox" id="' . esc_attr( $id ) . '" name="smile_f[' . esc_attr( $key ) . ']" value="1"' . checked( '1', $val, false ) . $req . $aria . ' /><span>' . esc_html( $label ) . $star . '</span></label>';
		} else {
			$out .= '<label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . $star . '</label>';
			if ( 'textarea' === $ftype ) {
				$out .= '<textarea id="' . esc_attr( $id ) . '" name="smile_f[' . esc_attr( $key ) . ']" maxlength="3000"' . $req . $aria . '>' . esc_textarea( $val ) . '</textarea>';
			} elseif ( 'select' === $ftype ) {
				$out .= '<select id="' . esc_attr( $id ) . '" name="smile_f[' . esc_attr( $key ) . ']"' . $req . $aria . '><option value="">' . esc_html__( 'Please choose', 'smile-core' ) . '</option>';
				foreach ( $def[3] as $opt ) {
					$out .= '<option value="' . esc_attr( $opt ) . '"' . selected( $val, $opt, false ) . '>' . esc_html( $opt ) . '</option>';
				}
				$out .= '</select>';
			} else {
				$out .= '<input type="' . esc_attr( $ftype ) . '" id="' . esc_attr( $id ) . '" name="smile_f[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" maxlength="200"' . $ac . $req . $aria . ' />';
			}
		}
		if ( $err ) {
			$out .= '<p class="smile-form__error" id="' . esc_attr( $id ) . '-err">' . esc_html( $err ) . '</p>';
		}
		$out .= '</div>';
	}

	$privacy = get_privacy_policy_url();
	$out    .= '<p class="smile-form__privacy">' . esc_html__( 'We use your details only to respond to this message. They are stored securely, seen only by authorised Smile staff, never sold or shared, and deleted after a set period.', 'smile-core' );
	if ( $privacy ) {
		$out .= ' <a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Privacy Policy', 'smile-core' ) . '</a>';
	}
	if ( 'removal' !== $type ) {
		$out .= ' ' . esc_html__( 'Please do not include sensitive personal details about children in this form.', 'smile-core' );
	}
	$out .= '</p>';

	$label = $atts['button'] ? $atts['button'] : ( 'volunteer' === $type ? __( 'Send application', 'smile-core' ) : __( 'Send message', 'smile-core' ) );
	$out  .= '<div><button type="submit" class="smile-btn smile-btn--teal">' . esc_html( $label ) . '</button></div>';
	$out  .= '</form></div>';
	return $out;
}
add_shortcode( 'smile_form', 'smile_core_form_shortcode' );

/**
 * Redirect back to the form with a status.
 *
 * @param string $return Return URL.
 * @param string $type   Form type.
 * @param string $status Status.
 * @param string $token  Optional state token.
 */
function smile_core_form_redirect( $return, $type, $status, $token = '' ) {
	$args = array( 'smile_form' => $type, 'smile_status' => $status );
	if ( $token ) {
		$args['smile_token'] = $token;
	}
	wp_safe_redirect( add_query_arg( $args, $return ) . '#smile-form-' . $type, 303 );
	exit;
}

/**
 * Handle a submission.
 */
function smile_core_form_submit() {
	$types  = smile_core_form_types();
	$type   = isset( $_POST['smile_type'] ) ? sanitize_key( wp_unslash( $_POST['smile_type'] ) ) : '';
	$return = isset( $_POST['smile_return'] ) ? esc_url_raw( wp_unslash( $_POST['smile_return'] ) ) : home_url( '/' );
	$return = wp_validate_redirect( $return, home_url( '/' ) );
	$return = remove_query_arg( array( 'smile_form', 'smile_status', 'smile_token' ), $return );

	if ( ! isset( $types[ $type ] ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	// Honeypot: pretend success, store nothing.
	if ( ! empty( $_POST['smile_website'] ) ) {
		smile_core_form_redirect( $return, $type, 'ok' );
	}

	// Nonce.
	$nonce = isset( $_POST['smile_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['smile_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'smile_form_' . $type ) ) {
		smile_core_form_redirect( $return, $type, 'expired' );
	}

	// Signed timestamp: at least 3 seconds, at most 1 day.
	$ts_raw = isset( $_POST['smile_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['smile_ts'] ) ) : '';
	$parts  = explode( '.', $ts_raw, 2 );
	$ts     = isset( $parts[0] ) ? absint( $parts[0] ) : 0;
	$sig    = isset( $parts[1] ) ? $parts[1] : '';
	if ( ! $ts || ! hash_equals( hash_hmac( 'sha256', $ts . $type, wp_salt( 'nonce' ) ), $sig ) || ( time() - $ts ) > DAY_IN_SECONDS ) {
		smile_core_form_redirect( $return, $type, 'expired' );
	}
	if ( ( time() - $ts ) < 3 ) {
		smile_core_form_redirect( $return, $type, 'ok' ); // Bot speed: silently drop.
	}

	// Rate limit: 5 per hour per IP hash.
	$rl_key = 'smile_rl_' . substr( smile_core_ip_hash(), 0, 32 );
	$count  = (int) get_transient( $rl_key );
	if ( $count >= 5 ) {
		smile_core_form_redirect( $return, $type, 'limit' );
	}

	// Validate.
	$input  = isset( $_POST['smile_f'] ) && is_array( $_POST['smile_f'] ) ? wp_unslash( $_POST['smile_f'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field.
	$values = array();
	$errors = array();
	foreach ( smile_core_form_fields( $type ) as $key => $def ) {
		list( $label, $ftype, $required ) = $def;
		$raw = isset( $input[ $key ] ) && is_string( $input[ $key ] ) ? $input[ $key ] : '';
		switch ( $ftype ) {
			case 'email':
				$val = sanitize_email( $raw );
				if ( $raw && ! is_email( $val ) ) {
					$errors[ $key ] = __( 'Enter a valid email address, like name@example.com.', 'smile-core' );
				}
				break;
			case 'textarea':
				$val = mb_substr( sanitize_textarea_field( $raw ), 0, 3000 );
				break;
			case 'url':
				$val = esc_url_raw( $raw );
				break;
			case 'checkbox':
				$val = empty( $raw ) ? '' : '1';
				break;
			case 'select':
				$val = sanitize_text_field( $raw );
				if ( $val && ! in_array( $val, $def[3], true ) ) {
					$val = '';
				}
				break;
			case 'tel':
				$val = preg_replace( '/[^0-9+()\s-]/', '', sanitize_text_field( $raw ) );
				break;
			case 'date':
				$val = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
				break;
			default:
				$val = mb_substr( sanitize_text_field( $raw ), 0, 200 );
		}
		$values[ $key ] = $val;
		if ( $required && '' === $val && ! isset( $errors[ $key ] ) ) {
			/* translators: %s field label */
			$errors[ $key ] = 'checkbox' === $ftype ? sprintf( __( 'Please confirm: %s', 'smile-core' ), $label ) : sprintf( __( '%s is required.', 'smile-core' ), $label );
		}
	}

	if ( $errors ) {
		$token = wp_generate_password( 20, false, false );
		$token = strtolower( $token );
		set_transient( 'smile_form_' . $token, array( 'values' => $values, 'errors' => $errors ), 15 * MINUTE_IN_SECONDS );
		smile_core_form_redirect( $return, $type, 'invalid', $token );
	}

	set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );

	// Store.
	$fields = array();
	foreach ( smile_core_form_fields( $type ) as $key => $def ) {
		$v = $values[ $key ];
		if ( 'checkbox' === $def[1] ) {
			$v = $v ? __( 'Yes', 'smile-core' ) : __( 'No', 'smile-core' );
		}
		$fields[ $def[0] ] = $v;
	}
	$title   = sprintf( '%s - %s', $values['name'], $types[ $type ] );
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'smile_inquiry',
			'post_status' => 'private',
			'post_title'  => $title,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		smile_core_form_redirect( $return, $type, 'expired' );
	}
	update_post_meta( $post_id, '_smile_type', $type );
	update_post_meta( $post_id, '_smile_fields', $fields );
	update_post_meta( $post_id, '_smile_email', strtolower( $values['email'] ) );
	update_post_meta( $post_id, '_smile_handled', '0' );
	wp_set_object_terms( $post_id, $type, 'smile_inquiry_type' );

	// Notify.
	$to = smile_core_get( 'notify_email' );
	if ( 'removal' === $type && smile_core_get( 'safeguarding_email' ) ) {
		$to = smile_core_get( 'safeguarding_email' );
	}
	$to   = array_filter( array_map( 'trim', explode( ',', $to ) ), 'is_email' );
	$sent = false;
	if ( $to ) {
		$body = sprintf( "New %s from the website.\n\n", $types[ $type ] );
		foreach ( $fields as $label => $v ) {
			$body .= $label . ":\n" . $v . "\n\n";
		}
		$body   .= "View in WordPress: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . "\n";
		$headers = array( 'Reply-To: ' . $values['name'] . ' <' . $values['email'] . '>' );
		/* translators: 1: form type 2: name */
		$sent = wp_mail( $to, sprintf( __( '[Smile website] %1$s from %2$s', 'smile-core' ), $types[ $type ], $values['name'] ), $body, $headers );
	}
	update_post_meta( $post_id, '_smile_mail_sent', $sent ? '1' : '0' );

	smile_core_form_redirect( $return, $type, $sent ? 'ok' : 'saved' );
}
add_action( 'admin_post_nopriv_smile_form_submit', 'smile_core_form_submit' );
add_action( 'admin_post_smile_form_submit', 'smile_core_form_submit' );

/*
 * Caching note: WP Rocket does not cache URLs with unknown query strings, so the
 * smile_status / smile_token result URLs are always served fresh. Nonces stay
 * valid for 12-24 hours; keep the page cache lifespan at 10 hours or less.
 */
