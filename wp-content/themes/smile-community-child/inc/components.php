<?php
/**
 * Reusable design components for Elementor's Shortcode widget.
 *
 * [smile_breadcrumbs]          Breadcrumb trail for page banners
 * [smile_icon name="heart"]    Inline SVG icon (decorative)
 * [smile_contact_cards]        Call / WhatsApp / Email / Visit cards
 * [smile_trust]                Donation reassurance strip
 * [smile_steps set="volunteer"] Numbered process steps
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * SVG paths for icons (24x24, stroke style).
 *
 * @return array
 */
function smile_icon_paths() {
	return array(
		'heart'    => '<path d="M12 20.5s-7.2-4.4-9.2-8.9C1.4 8.4 3.4 4.8 7 4.8c2 0 3.4 1 4.2 2.5.8-1.5 2.2-2.5 4.2-2.5 3.6 0 5.6 3.6 4.2 6.8-2 4.5-7.6 8.9-7.6 8.9z"/>',
		'book'     => '<path d="M4 5.5A2.5 2.5 0 016.5 3H20v15H6.5A2.5 2.5 0 004 20.5zM4 20.5V5.5M8 7h8"/>',
		'bowl'     => '<path d="M3 11h18a9 9 0 01-18 0zM8 7c0-1.5 1-2 1-3.5M12 7c0-1.5 1-2 1-3.5M16 7c0-1.5 1-2 1-3.5"/>',
		'home'     => '<path d="M3 11l9-7 9 7M5 9.5V20h14V9.5M10 20v-5h4v5"/>',
		'health'   => '<path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z"/>',
		'ball'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7l4 3-1.5 4.5h-5L8 10zM12 3v4M21 10l-5 0M3 10h5M7 20l2.5-5.5M17 20l-2.5-5.5"/>',
		'hands'    => '<path d="M7 11V6a1.5 1.5 0 013 0v4M10 10V4.5a1.5 1.5 0 013 0V10M13 10V5.5a1.5 1.5 0 013 0V12M16 9.5a1.5 1.5 0 013 0V14a7 7 0 01-7 7h-1a6 6 0 01-5-2.7L3.5 14a1.6 1.6 0 012.6-1.8L7 13.5"/>',
		'water'    => '<path d="M12 3s6 6.6 6 11a6 6 0 01-12 0c0-4.4 6-11 6-11z"/>',
		'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-6 6.5-6s5.9 2.4 6.5 6M16 4.8a3.5 3.5 0 010 6.4M18 14.4c2 .8 3.2 2.8 3.5 5.6"/>',
		'building' => '<path d="M4 21V5l8-3v19M12 8l8 3v10M8 8v.01M8 12v.01M8 16v.01M16 14v.01M16 17v.01M2 21h20"/>',
		'gift'     => '<path d="M4 11h16v10H4zM3 7h18v4H3zM12 7v14M12 7S10.5 3 8 3a2 2 0 000 4M12 7s1.5-4 4-4a2 2 0 010 4"/>',
		'pin'      => '<path d="M12 21s7-6.2 7-11.5A7 7 0 005 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'phone'    => '<path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 013 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.3 0 .7-.2 1z"/>',
		'chat'     => '<path d="M20 12a8 8 0 01-11.8 7L4 20l1.1-3.9A8 8 0 1120 12z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'shield'   => '<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
		'receipt'  => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6"/>',
		'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'star'     => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
	);
}

/**
 * Icon markup.
 *
 * @param string $name  Icon.
 * @param string $class Extra class.
 * @return string
 */
function smile_icon( $name, $class = '' ) {
	$paths = smile_icon_paths();
	if ( ! isset( $paths[ $name ] ) ) {
		$name = 'heart';
	}
	return '<span class="smile-icon ' . esc_attr( $class ) . '" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</svg></span>'; // Static markup.
}
add_shortcode(
	'smile_icon',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'name' => 'heart' ), $atts, 'smile_icon' );
		return smile_icon( sanitize_key( $atts['name'] ), 'smile-icon--card' );
	}
);

add_shortcode(
	'smile_breadcrumbs',
	function () {
		ob_start();
		smile_breadcrumbs();
		return ob_get_clean();
	}
);

/**
 * Contact cards.
 *
 * @return string
 */
function smile_contact_cards() {
	$phone = smile_setting( 'phone_primary', '0721 318 869' );
	$wa    = preg_replace( '/\D/', '', smile_setting( 'whatsapp', '254721318869' ) );
	$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
	$map   = smile_setting( 'map_link', 'https://maps.app.goo.gl/6BvCCFYRq1DtkXNF9' );
	$cards = array(
		array( 'phone', __( 'Call us', 'smile-community' ), $phone, smile_tel_href( $phone ), smile_setting( 'office_hours', 'Office: Mon–Sat 9am–5pm · Sun from 2pm' ) ),
		array( 'chat', __( 'WhatsApp', 'smile-community' ), __( 'Message us', 'smile-community' ), $wa ? 'https://wa.me/' . $wa : '', __( 'Quick replies during office hours', 'smile-community' ) ),
		array( 'mail', __( 'Email', 'smile-community' ), antispambot( $email ), 'mailto:' . antispambot( $email ), __( 'We reply within a few working days', 'smile-community' ) ),
		array( 'pin', __( 'Visit', 'smile-community' ), __( 'Mzesa, Soweto, Kayole', 'smile-community' ), $map, __( 'By arrangement, near Donholm Phase 8', 'smile-community' ) ),
	);
	$out = '<div class="smile-ccards">';
	foreach ( $cards as $c ) {
		if ( ! $c[3] ) {
			continue;
		}
		$ext  = 0 === strpos( $c[3], 'http' ) ? ' rel="noopener"' : '';
		$out .= '<a class="smile-ccard" href="' . esc_url( $c[3] ) . '"' . $ext . '>' . smile_icon( $c[0] ) . '<span class="smile-ccard__label">' . esc_html( $c[1] ) . '</span><strong class="smile-ccard__value">' . esc_html( $c[2] ) . '</strong><span class="smile-ccard__hint">' . esc_html( $c[4] ) . '</span></a>';
	}
	return $out . '</div>';
}
add_shortcode( 'smile_contact_cards', 'smile_contact_cards' );

add_shortcode(
	'smile_trust',
	function () {
		$items = array(
			array( 'shield', __( 'Official M-Pesa Paybill', 'smile-community' ), __( 'Gifts go straight to the centre’s account.', 'smile-community' ) ),
			array( 'receipt', __( 'Confirmation on request', 'smile-community' ), __( 'Send your reference and we will confirm receipt.', 'smile-community' ) ),
			array( 'heart', __( 'Used where it matters', 'smile-community' ), __( 'Meals, school fees, water and medical care.', 'smile-community' ) ),
			array( 'chat', __( 'Questions welcome', 'smile-community' ), __( 'Call or WhatsApp the centre any weekday.', 'smile-community' ) ),
		);
		$out = '<ul class="smile-trust">';
		foreach ( $items as $i ) {
			$out .= '<li>' . smile_icon( $i[0] ) . '<span><strong>' . esc_html( $i[1] ) . '</strong>' . esc_html( $i[2] ) . '</span></li>';
		}
		return $out . '</ul>';
	}
);

add_shortcode(
	'smile_steps',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'set' => 'volunteer' ), $atts, 'smile_steps' );
		$sets = array(
			'volunteer' => array(
				array( __( 'Send your application', 'smile-community' ), __( 'Use the form on this page to tell us about yourself, your skills and your dates.', 'smile-community' ) ),
				array( __( 'We get in touch', 'smile-community' ), __( 'Our team replies to discuss a suitable role and what we need from you.', 'smile-community' ) ),
				array( __( 'Sign the volunteer agreement', 'smile-community' ), __( 'Every volunteer signs our agreement and code of conduct before starting.', 'smile-community' ) ),
			),
			'partner'   => array(
				array( __( 'Share your idea', 'smile-community' ), __( 'Tell us what your organisation would like to support.', 'smile-community' ) ),
				array( __( 'Plan together', 'smile-community' ), __( 'We agree what is most useful for the children and how it will work.', 'smile-community' ) ),
				array( __( 'Make it happen', 'smile-community' ), __( 'Your support reaches the children, and we keep you updated.', 'smile-community' ) ),
			),
			'concern'   => array(
				array( __( 'Tell us', 'smile-community' ), __( 'Call, WhatsApp or email the centre, or use the form. Share only what you know.', 'smile-community' ) ),
				array( __( 'We listen in confidence', 'smile-community' ), __( 'The centre’s management reviews every concern promptly and privately.', 'smile-community' ) ),
				array( __( 'We act to protect the child', 'smile-community' ), __( 'Where a child may be at risk, we work with the children’s office and police, as we do for every referral.', 'smile-community' ) ),
			),
			'donate'    => array(
				array( __( 'Open M-Pesa', 'smile-community' ), __( 'Choose Lipa na M-Pesa, then Pay Bill.', 'smile-community' ) ),
				array( __( 'Enter our details', 'smile-community' ), __( 'Paybill and account number are shown on this page.', 'smile-community' ) ),
				array( __( 'Send and keep the message', 'smile-community' ), __( 'Your M-Pesa confirmation is your record.', 'smile-community' ) ),
			),
		);
		$set  = isset( $sets[ $atts['set'] ] ) ? $sets[ $atts['set'] ] : $sets['volunteer'];
		$out  = '<ol class="smile-steps">';
		foreach ( $set as $n => $s ) {
			$out .= '<li><span class="smile-steps__n" aria-hidden="true">' . esc_html( $n + 1 ) . '</span><strong>' . esc_html( $s[0] ) . '</strong><span>' . esc_html( $s[1] ) . '</span></li>';
		}
		return $out . '</ol>';
	}
);

/**
 * Mobile bottom navigation (static tab bar): Home, Programmes, Donate, Contact, Menu.
 */
function smile_mobile_bar() {
	$pages = array(
		'home'       => array( home_url( '/' ), 'home', __( 'Home', 'smile-community' ), is_front_page() ),
		'programmes' => array( home_url( '/our-programmes/' ), 'book', __( 'Programmes', 'smile-community' ), is_page( array( 'our-programmes', 'education-program', 'feeding-program', 'child-care-and-shelter', 'health-and-wellbeing', 'sports' ) ) ),
		'donate'     => array( smile_donate_url(), 'heart', __( 'Donate', 'smile-community' ), is_page( 'donate' ) ),
		'contact'    => array( home_url( '/contact-us/' ), 'chat', __( 'Contact', 'smile-community' ), is_page( 'contact-us' ) ),
	);
	echo '<nav class="smile-tabbar" aria-label="' . esc_attr__( 'Quick navigation', 'smile-community' ) . '">';
	foreach ( $pages as $key => $p ) {
		$cls = 'smile-tabbar__item smile-tabbar__item--' . $key . ( $p[3] ? ' is-current' : '' );
		echo '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $p[0] ) . '"' . ( $p[3] ? ' aria-current="page"' : '' ) . '>' . smile_icon( $p[1] ) . '<span>' . esc_html( $p[2] ) . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static icon.
	}
	echo '<button type="button" class="smile-tabbar__item smile-tabbar__item--menu" data-smile-open-menu aria-controls="smile-primary-nav" aria-expanded="false"><span class="smile-tabbar__burger" aria-hidden="true"><i></i><i></i><i></i></span><span>' . esc_html__( 'Menu', 'smile-community' ) . '</span></button>';
	echo '</nav>';
}
add_action( 'wp_footer', 'smile_mobile_bar', 5 );

/**
 * Fallback card image for stories without a featured image: first image in the content.
 *
 * @param int $post_id Post.
 * @return string Image HTML or ''.
 */
function smile_story_image( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, 'smile-card', array( 'alt' => '', 'loading' => 'lazy' ) );
	}
	$content = (string) get_post_field( 'post_content', $post_id );
	if ( preg_match( '/wp-image-(\d+)/', $content, $m ) && wp_attachment_is_image( (int) $m[1] ) ) {
		return wp_get_attachment_image( (int) $m[1], 'smile-card', false, array( 'alt' => '', 'loading' => 'lazy' ) );
	}
	if ( function_exists( 'smile_image_by_key' ) ) {
		$pool = array( 'mealtime', 'pupils', 'feeding1', 'schoolkids', 'feeding3', 'education', 'highschool', 'staff-serving', 'football', 'feeding2', 'skipping', 'founder-school' );
		$key  = $pool[ absint( $post_id ) % count( $pool ) ];
		$img  = smile_image_by_key( $key );
		if ( $img ) {
			return wp_get_attachment_image( $img, 'smile-card', false, array( 'alt' => '', 'loading' => 'lazy', 'class' => 'is-fallback' ) );
		}
	}
	return '<span class="smile-card__placeholder">' . smile_icon( 'heart' ) . '</span>';
}

/**
 * Category label in clean title case (fixes ALL CAPS or all-lowercase names).
 *
 * @param string $name Raw category name.
 * @return string
 */
function smile_cat_label( $name ) {
	$letters = preg_replace( '/[^A-Za-z]/', '', $name );
	if ( '' === $letters || ( strtoupper( $letters ) !== $letters && strtolower( $letters ) !== $letters ) ) {
		return $name;
	}
	$small = array( 'a', 'an', 'and', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to' );
	$words = explode( ' ', mb_strtolower( $name ) );
	foreach ( $words as $i => $w ) {
		if ( 0 === $i || ! in_array( $w, $small, true ) ) {
			$words[ $i ] = mb_strtoupper( mb_substr( $w, 0, 1 ) ) . mb_substr( $w, 1 );
		}
	}
	return implode( ' ', $words );
}

/**
 * First real category of a post (skips Uncategorized).
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function smile_primary_cat( $post_id = 0 ) {
	foreach ( (array) get_the_category( $post_id ) as $c ) {
		if ( 'uncategorized' !== $c->slug ) {
			return $c;
		}
	}
	return null;
}


/**
 * Display-time tidy-ups so older imported stories look right without re-importing:
 * ALL CAPS titles become title case; summaries that are only image captions are
 * rebuilt from the story text.
 */
function smile_tidy_title( $title, $post_id = 0 ) {
	if ( is_admin() || ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		return $title;
	}
	$letters = preg_replace( '/[^A-Za-z]/', '', $title );
	if ( strlen( $letters ) < 6 || strtoupper( $letters ) !== $letters ) {
		return $title;
	}
	$small = array( 'a', 'an', 'and', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'your', 'with' );
	$words = explode( ' ', mb_strtolower( $title ) );
	foreach ( $words as $i => $word ) {
		if ( 0 === $i || ! in_array( $word, $small, true ) ) {
			$words[ $i ] = mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_substr( $word, 1 );
		}
	}
	return implode( ' ', $words );
}
add_filter( 'the_title', 'smile_tidy_title', 10, 2 );

/**
 * Rebuild weak excerpts.
 *
 * @param string  $excerpt Excerpt.
 * @param WP_Post $post    Post.
 * @return string
 */
function smile_tidy_excerpt( $excerpt, $post = null ) {
	if ( is_admin() ) {
		return $excerpt;
	}
	$clean = trim( preg_replace( '/\s*(Read More|\[&hellip;\]|\[…\]|…)\s*$/i', '', wp_strip_all_tags( (string) $excerpt ) ) );
	if ( str_word_count( $clean ) >= 12 ) {
		return $clean;
	}
	$post = get_post( $post );
	if ( ! $post ) {
		return $clean;
	}
	$text = preg_replace( array( '#<figure.*?</figure>#is', '#<img[^>]*>#i', '#<figcaption.*?</figcaption>#is' ), '', (string) $post->post_content );
	return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $text ) ), 32, '…' );
}
add_filter( 'get_the_excerpt', 'smile_tidy_excerpt', 20, 2 );


/* ---------- Safeguarding components ---------- */
add_shortcode(
	'smile_commitments',
	function () {
		$items = array(
			array( 'shield', __( 'Authorised care only', 'smile-community' ), __( 'We care for a child only with the authorisation required by the police, the courts or the children’s office.', 'smile-community' ) ),
			array( 'users', __( 'Privacy protected', 'smile-community' ), __( 'We never publish children’s full names, personal histories, schools or details that could identify them.', 'smile-community' ) ),
			array( 'star', __( 'Photos with permission', 'smile-community' ), __( 'We share photographs only with permission and remove location data from every image on this website.', 'smile-community' ) ),
			array( 'home', __( 'No public child profiles', 'smile-community' ), __( 'There are no child profiles on this website, and no way to contact a child directly through it.', 'smile-community' ) ),
			array( 'hands', __( 'Supervised visits', 'smile-community' ), __( 'Visitors and volunteers follow a code of conduct and are supervised by staff at all times.', 'smile-community' ) ),
			array( 'heart', __( 'Dignity in every story', 'smile-community' ), __( 'We do not use distressing images or stories to pressure anyone to give.', 'smile-community' ) ),
		);
		$out = '<ul class="smile-commit">';
		foreach ( $items as $i => $it ) {
			$out .= '<li class="smile-reveal" style="--i:' . (int) ( $i % 3 ) . '">' . smile_icon( $it[0] ) . '<h3>' . esc_html( $it[1] ) . '</h3><p>' . esc_html( $it[2] ) . '</p></li>';
		}
		return $out . '</ul>';
	}
);

add_shortcode(
	'smile_urgent',
	function () {
		$phone = smile_setting( 'phone_primary', '0721 318 869' );
		return '<div class="smile-urgent" role="note"><div class="smile-urgent__lead">' . smile_icon( 'shield' ) . '<div><p class="smile-urgent__kicker">' . esc_html__( 'If a child is in immediate danger', 'smile-community' ) . '</p><p class="smile-urgent__title">' . esc_html__( 'Call for help now. Do not wait to fill in a form.', 'smile-community' ) . '</p></div></div>'
			. '<div class="smile-urgent__actions">'
			. '<a class="smile-urgent__btn smile-urgent__btn--main" href="tel:116"><strong>116</strong><span>' . esc_html__( 'Childline Kenya, free, 24 hours', 'smile-community' ) . '</span></a>'
			. '<a class="smile-urgent__btn" href="tel:999"><strong>999 / 112</strong><span>' . esc_html__( 'Police emergency', 'smile-community' ) . '</span></a>'
			. '<a class="smile-urgent__btn" href="' . esc_url( smile_tel_href( $phone ) ) . '"><strong>' . esc_html( $phone ) . '</strong><span>' . esc_html__( 'Smile Community Centre', 'smile-community' ) . '</span></a>'
			. '</div></div>';
	}
);

add_shortcode(
	'smile_concern_contacts',
	function () {
		$phone = smile_setting( 'phone_primary', '0721 318 869' );
		$wa    = preg_replace( '/\D/', '', smile_setting( 'whatsapp', '254721318869' ) );
		$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
		$rows  = array(
			array( 'phone', __( 'Call the centre', 'smile-community' ), $phone, smile_tel_href( $phone ) ),
			array( 'chat', __( 'WhatsApp', 'smile-community' ), __( 'Message us privately', 'smile-community' ), $wa ? 'https://wa.me/' . $wa : '' ),
			array( 'mail', __( 'Email', 'smile-community' ), antispambot( $email ), 'mailto:' . antispambot( $email ) ),
		);
		$out = '<ul class="smile-cc">';
		foreach ( $rows as $r ) {
			if ( ! $r[3] ) {
				continue;
			}
			$out .= '<li><a href="' . esc_url( $r[3] ) . '"' . ( 0 === strpos( $r[3], 'http' ) ? ' rel="noopener"' : '' ) . '>' . smile_icon( $r[0] ) . '<span><small>' . esc_html( $r[1] ) . '</small><strong>' . esc_html( $r[2] ) . '</strong></span><span class="smile-cc__arrow" aria-hidden="true">&rarr;</span></a></li>';
		}
		return $out . '</ul><p class="smile-cc__note">' . smile_icon( 'shield' ) . esc_html__( 'Concerns are handled confidentially by the centre’s management.', 'smile-community' ) . '</p>';
	}
);
