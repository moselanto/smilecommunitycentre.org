<?php
/**
 * Content shortcodes for Elementor's Shortcode widget.
 *
 * [smile_mpesa]            M-Pesa Paybill block with copy buttons
 * [smile_bank]             Bank transfer block (hidden until details are entered)
 * [smile_online_giving]    Online giving button (hidden until a link is entered)
 * [smile_giving_tiers]     Giving examples (estimates)
 * [smile_contact]          Contact list (phones, WhatsApp, email, address, hours)
 * [smile_directions]       Step-by-step directions
 * [smile_map]              Embedded Google map (no API key, lazy-loaded)
 * [smile_latest_posts count="3" category=""]  Latest stories from WordPress
 * [smile_setting key="phone_primary"]         Any single setting
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Format a phone number into a tel: link.
 *
 * @param string $number Number.
 * @return string
 */
function smile_core_tel( $number ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $number );
	if ( 0 === strpos( $digits, '0' ) ) {
		$digits = '+254' . substr( $digits, 1 );
	}
	return 'tel:' . $digits;
}

/**
 * M-Pesa block.
 *
 * @param array $atts Attributes.
 * @return string
 */
function smile_core_sc_mpesa( $atts ) {
	$atts    = shortcode_atts( array( 'title' => __( 'Give with M-Pesa', 'smile-core' ) ), $atts, 'smile_mpesa' );
	$paybill = smile_core_get( 'mpesa_paybill' );
	$account = smile_core_get( 'mpesa_account' );
	$name    = smile_core_get( 'mpesa_account_name' );
	if ( ! $paybill || ! $account ) {
		return '';
	}
	ob_start();
	?>
	<div class="smile-pay smile-pay--mpesa">
		<h3 class="smile-pay__title"><?php echo esc_html( $atts['title'] ); ?></h3>
		<ol class="smile-pay__steps">
			<li><?php esc_html_e( 'Open M-Pesa on your phone and choose Lipa na M-Pesa, then Pay Bill.', 'smile-core' ); ?></li>
			<li><?php esc_html_e( 'Enter the business number and account number below.', 'smile-core' ); ?></li>
			<li><?php esc_html_e( 'Enter the amount you wish to give and your M-Pesa PIN.', 'smile-core' ); ?></li>
			<li><?php esc_html_e( 'Check the name on the confirmation screen before you send, then keep the M-Pesa message as your record.', 'smile-core' ); ?></li>
		</ol>
		<div class="smile-pay__row">
			<div><span class="smile-pay__label"><?php esc_html_e( 'Business number (Paybill)', 'smile-core' ); ?></span><span class="smile-pay__value"><?php echo esc_html( $paybill ); ?></span></div>
			<button type="button" class="smile-copy" data-smile-copy="<?php echo esc_attr( $paybill ); ?>" aria-label="<?php esc_attr_e( 'Copy business number', 'smile-core' ); ?>"><?php esc_html_e( 'Copy', 'smile-core' ); ?></button>
		</div>
		<div class="smile-pay__row">
			<div><span class="smile-pay__label"><?php esc_html_e( 'Account number', 'smile-core' ); ?></span><span class="smile-pay__value"><?php echo esc_html( $account ); ?></span></div>
			<button type="button" class="smile-copy" data-smile-copy="<?php echo esc_attr( $account ); ?>" aria-label="<?php esc_attr_e( 'Copy account number', 'smile-core' ); ?>"><?php esc_html_e( 'Copy', 'smile-core' ); ?></button>
		</div>
		<?php if ( $name ) : ?>
			<p class="smile-pay__note"><?php echo esc_html( sprintf( /* translators: %s account name */ __( 'The confirmation screen should show: %s', 'smile-core' ), $name ) ); ?></p>
		<?php endif; ?>
		<p class="smile-pay__note"><?php esc_html_e( 'Would you like us to confirm we received your gift? Send us the M-Pesa reference using the form on this page, or by WhatsApp.', 'smile-core' ); ?></p>
		<div id="smile-live" class="screen-reader-text" aria-live="polite"></div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'smile_mpesa', 'smile_core_sc_mpesa' );

/**
 * Bank block.
 *
 * @return string
 */
function smile_core_sc_bank() {
	$details = trim( smile_core_get( 'bank_details' ) );
	if ( ! $details ) {
		return '';
	}
	$lines = array_filter( array_map( 'trim', explode( "\n", $details ) ) );
	$out   = '<div class="smile-pay smile-pay--bank"><h3 class="smile-pay__title">' . esc_html__( 'Bank transfer', 'smile-core' ) . '</h3><ul class="smile-contact-list">';
	foreach ( $lines as $line ) {
		$out .= '<li>' . esc_html( $line ) . '</li>';
	}
	$out .= '</ul></div>';
	return $out;
}
add_shortcode( 'smile_bank', 'smile_core_sc_bank' );

/**
 * Online giving button.
 *
 * @return string
 */
function smile_core_sc_online() {
	$url = smile_core_get( 'online_giving_url' );
	if ( ! $url ) {
		return '';
	}
	return '<p><a class="smile-btn smile-btn--teal" href="' . esc_url( $url ) . '" rel="noopener">' . esc_html( smile_core_get( 'online_giving_label' ) ) . '</a></p>';
}
add_shortcode( 'smile_online_giving', 'smile_core_sc_online' );

/**
 * Giving tiers.
 *
 * @return string
 */
function smile_core_sc_tiers() {
	$lines = array_filter( array_map( 'trim', explode( "\n", smile_core_get( 'tiers' ) ) ) );
	if ( ! $lines ) {
		return '';
	}
	$out = '<ul class="smile-tiers">';
	foreach ( $lines as $line ) {
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) < 3 ) {
			continue;
		}
		$featured = isset( $p[3] ) && 'featured' === $p[3];
		$out     .= '<li class="smile-tier' . ( $featured ? ' is-featured' : '' ) . '">';
		if ( $featured ) {
			$out .= '<span class="smile-tier__badge">' . esc_html__( 'Suggested', 'smile-core' ) . '</span>';
		}
		$out .= '<span class="smile-tier__amount">KES ' . esc_html( number_format_i18n( (float) $p[0] ) ) . '</span>';
		if ( '' !== $p[1] ) {
			$out .= '<span class="smile-tier__usd">' . esc_html( sprintf( /* translators: %s USD */ __( 'about US$%s', 'smile-core' ), $p[1] ) ) . '</span>';
		}
		$out .= '<p>' . esc_html( $p[2] ) . '</p></li>';
	}
	$out .= '</ul>';
	$note = smile_core_get( 'tiers_note' );
	if ( $note ) {
		$out .= '<p class="smile-tiers-note">' . esc_html( $note ) . '</p>';
	}
	return $out;
}
add_shortcode( 'smile_giving_tiers', 'smile_core_sc_tiers' );

/**
 * Contact list.
 *
 * @return string
 */
function smile_core_sc_contact() {
	$out  = '<ul class="smile-contact-list">';
	$main = smile_core_get( 'phone_primary' );
	if ( $main ) {
		$out .= '<li><strong>' . esc_html__( 'Call', 'smile-core' ) . '</strong><a href="' . esc_url( smile_core_tel( $main ) ) . '">' . esc_html( $main ) . '</a>';
		foreach ( array_filter( array_map( 'trim', explode( "\n", smile_core_get( 'phone_others' ) ) ) ) as $n ) {
			$out .= ' &middot; <a href="' . esc_url( smile_core_tel( $n ) ) . '">' . esc_html( $n ) . '</a>';
		}
		$out .= '</li>';
	}
	$wa = smile_core_get( 'whatsapp' );
	if ( $wa ) {
		$out .= '<li><strong>' . esc_html__( 'WhatsApp', 'smile-core' ) . '</strong><a href="' . esc_url( 'https://wa.me/' . $wa ) . '" rel="noopener">' . esc_html__( 'Message us on WhatsApp', 'smile-core' ) . '</a></li>';
	}
	$email = smile_core_get( 'email' );
	if ( $email ) {
		$out .= '<li><strong>' . esc_html__( 'Email', 'smile-core' ) . '</strong><a href="' . esc_url( 'mailto:' . antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></li>';
	}
	$out .= '<li><strong>' . esc_html__( 'Location', 'smile-core' ) . '</strong>' . esc_html( smile_core_get( 'address' ) ) . '</li>';
	$out .= '<li><strong>' . esc_html__( 'Postal address', 'smile-core' ) . '</strong>' . esc_html( smile_core_get( 'postal_address' ) ) . '</li>';
	$out .= '<li><strong>' . esc_html__( 'Hours', 'smile-core' ) . '</strong>' . esc_html( smile_core_get( 'office_hours' ) ) . '</li>';
	$out .= '</ul>';
	return $out;
}
add_shortcode( 'smile_contact', 'smile_core_sc_contact' );

/**
 * Directions.
 *
 * @return string
 */
function smile_core_sc_directions() {
	$steps = array_filter( array_map( 'trim', explode( "\n", smile_core_get( 'directions' ) ) ) );
	$out   = '';
	$link  = smile_core_get( 'map_link' );
	if ( $link ) {
		$out .= '<p><a class="smile-btn smile-btn--ghost" href="' . esc_url( $link ) . '" rel="noopener">' . esc_html__( 'Open in Google Maps', 'smile-core' ) . '</a></p>';
	}
	$out .= '<p>' . esc_html__( 'By Uber or Bolt: search for "Smile Community Centre Children\'s Home".', 'smile-core' ) . '</p>';
	if ( $steps ) {
		$out .= '<p><strong>' . esc_html__( 'By matatu from Nairobi town', 'smile-core' ) . '</strong></p><ol>';
		foreach ( $steps as $s ) {
			$out .= '<li>' . esc_html( $s ) . '</li>';
		}
		$out .= '</ol>';
	}
	return $out;
}
add_shortcode( 'smile_directions', 'smile_core_sc_directions' );

/**
 * Map embed.
 *
 * @return string
 */
function smile_core_sc_map() {
	$q = smile_core_get( 'map_query' );
	if ( ! $q ) {
		return '';
	}
	$src = 'https://maps.google.com/maps?q=' . rawurlencode( $q ) . '&z=15&output=embed';
	return '<div class="smile-map"><iframe src="' . esc_url( $src ) . '" title="' . esc_attr__( 'Map showing the location of Smile Community Centre in Soweto, Kayole, Nairobi', 'smile-core' ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
}
add_shortcode( 'smile_map', 'smile_core_sc_map' );

/**
 * Latest posts as cards (works without Elementor Pro).
 *
 * @param array $atts Attributes.
 * @return string
 */
function smile_core_sc_posts( $atts ) {
	$atts = shortcode_atts( array( 'count' => 3, 'category' => '' ), $atts, 'smile_latest_posts' );
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => min( 12, max( 1, absint( $atts['count'] ) ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $atts['category'] ) {
		$args['category_name'] = sanitize_title( $atts['category'] );
	}
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) {
		return '';
	}
	$out = '<div class="smile-posts">';
	while ( $q->have_posts() ) {
		$q->the_post();
		$out .= '<article class="smile-card smile-card--story">';
		if ( has_post_thumbnail() ) {
			$out .= '<a class="smile-card__media" href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . get_the_post_thumbnail( null, 'smile-card', array( 'alt' => '', 'loading' => 'lazy' ) ) . '</a>';
		}
		$out .= '<div class="smile-card__body"><p class="smile-card__meta"><time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time></p>';
		$out .= '<h3 class="smile-card__title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		$out .= '<p class="smile-card__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 22 ) ) . '</p></div></article>';
	}
	wp_reset_postdata();
	$out .= '</div>';
	return $out;
}
add_shortcode( 'smile_latest_posts', 'smile_core_sc_posts' );

/**
 * Single setting.
 *
 * @param array $atts Attributes.
 * @return string
 */
function smile_core_sc_setting( $atts ) {
	$atts    = shortcode_atts( array( 'key' => '' ), $atts, 'smile_setting' );
	$allowed = array( 'org_name', 'phone_primary', 'email', 'address', 'postal_address', 'office_hours', 'mpesa_paybill', 'mpesa_account' );
	if ( ! in_array( $atts['key'], $allowed, true ) ) {
		return '';
	}
	$v = smile_core_get( $atts['key'] );
	return 'email' === $atts['key'] ? esc_html( antispambot( $v ) ) : esc_html( $v );
}
add_shortcode( 'smile_setting', 'smile_core_sc_setting' );

/**
 * Volunteer availability notice.
 *
 * @return string
 */
function smile_core_sc_volunteer_notice() {
	if ( '1' === smile_core_get( 'volunteer_open' ) ) {
		return '<div class="smile-notice"><p>' . esc_html__( 'Volunteer places are open. Apply using the form on this page.', 'smile-core' ) . '</p></div>';
	}
	return '<div class="smile-notice"><p>' . esc_html( smile_core_get( 'volunteer_notice' ) ) . '</p></div>';
}
add_shortcode( 'smile_volunteer_notice', 'smile_core_sc_volunteer_notice' );
