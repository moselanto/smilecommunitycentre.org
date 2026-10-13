<?php
/**
 * Home page section components. Headings and intro text around each component
 * are normal Elementor widgets (editable); these shortcodes render the richer
 * visual parts. Photos are looked up by their import key, so replacing a photo
 * in the Media Library (same attachment) updates the design everywhere.
 *
 * [smile_intro_media]  [smile_programmes]  [smile_needs]  [smile_journey]
 * [smile_priorities]   [smile_involve]     [smile_community]
 * [smile_stories]      [smile_cta_photo]
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Image tag for a photo key.
 *
 * @param string $key   Key.
 * @param string $size  Size.
 * @param string $class Class.
 * @param bool   $eager Eager load.
 * @return string
 */
function smile_img( $key, $size = 'large', $class = '', $eager = false ) {
	$id = smile_image_by_key( $key );
	if ( ! $id ) {
		return '<span class="smile-card__placeholder ' . esc_attr( $class ) . '">' . smile_icon( 'heart' ) . '</span>';
	}
	return wp_get_attachment_image( $id, $size, false, array( 'class' => $class, 'loading' => $eager ? 'eager' : 'lazy', 'decoding' => 'async' ) );
}

/**
 * Site link for a path.
 *
 * @param string $path Path like /donate/.
 * @return string
 */
function smile_link( $path ) {
	return esc_url( home_url( $path ) );
}

/* ---------- Intro: layered photos with badge ---------- */
add_shortcode(
	'smile_intro_media',
	function () {
		return '<div class="smile-intro smile-reveal">'
			. '<div class="smile-intro__main">' . smile_img( 'founder-school', 'large', 'smile-intro__img' ) . '</div>'
			. '<div class="smile-intro__small">' . smile_img( 'centre-new', 'medium_large', 'smile-intro__img' ) . '</div>'
			. '<div class="smile-intro__badge"><span class="smile-intro__year">2009</span><span>' . esc_html__( 'Serving Soweto and Kayole since', 'smile-community' ) . '</span></div>'
			. '</div>';
	}
);

/* ---------- Programmes: uniform editorial cards ---------- */
add_shortcode(
	'smile_programmes',
	function () {
		$items = array(
			array( 'schoolkids', 'book', __( 'Education', 'smile-community' ), __( 'Every child in our care goes to school, with fees, uniforms, books and mentoring.', 'smile-community' ), '/education-program/' ),
			array( 'feeding1', 'bowl', __( 'Feeding programme', 'smile-community' ), __( 'Daily meals at home, and community feeding for more than 300 children.', 'smile-community' ), '/feeding-program/' ),
			array( 'staff-serving', 'home', __( 'Shelter and child care', 'smile-community' ), __( 'A safe, stable home for children referred by police and children’s officers.', 'smile-community' ), '/child-care-and-shelter/' ),
			array( 'doctors', 'health', __( 'Health and wellbeing', 'smile-community' ), __( 'Medical care, counselling and support for children affected by HIV/AIDS.', 'smile-community' ), '/health-and-wellbeing/' ),
			array( 'football', 'ball', __( 'Sports and talent', 'smile-community' ), __( 'Football, basketball and play at Jacaranda Grounds to build confidence.', 'smile-community' ), '/sports/' ),
			array( 'founder-serving', 'hands', __( 'Community outreach', 'smile-community' ), __( 'Food support for widows, single parents and neighbouring families.', 'smile-community' ), '/partner-with-us/' ),
		);
		$out = '<div class="smile-progs">';
		foreach ( $items as $i => $it ) {
			$out .= '<a class="smile-prog smile-reveal" style="--i:' . (int) ( $i % 3 ) . '" href="' . smile_link( $it[4] ) . '">'
				. '<span class="smile-prog__media">' . smile_img( $it[0], 'smile-card', 'smile-prog__img' ) . '</span>'
				. '<span class="smile-prog__body">' . smile_icon( $it[1], 'smile-prog__icon' )
				. '<span class="smile-prog__title">' . esc_html( $it[2] ) . '</span>'
				. '<span class="smile-prog__text">' . esc_html( $it[3] ) . '</span>'
				. '<span class="smile-prog__more">' . esc_html__( 'Learn more', 'smile-community' ) . '<span aria-hidden="true">&rarr;</span></span></span></a>';
		}
		return $out . '</div>';
	}
);

/* ---------- Needs list ---------- */
add_shortcode(
	'smile_needs',
	function () {
		$items = array(
			array( 'book', __( 'School fees and learning materials', 'smile-community' ), __( 'Some children risk missing school without support.', 'smile-community' ) ),
			array( 'bowl', __( 'Daily meals and supplies', 'smile-community' ), __( 'Food, uniforms and school supplies are always needed.', 'smile-community' ) ),
			array( 'water', __( 'Clean water', 'smile-community' ), __( 'Water reaches Soweto about once a week. About KES 2,500 fills both of our tanks.', 'smile-community' ) ),
			array( 'health', __( 'Medical care', 'smile-community' ), __( 'Some children arrive needing urgent treatment.', 'smile-community' ) ),
		);
		$out = '<ul class="smile-needs">';
		foreach ( $items as $it ) {
			$out .= '<li class="smile-reveal">' . smile_icon( $it[0] ) . '<span><strong>' . esc_html( $it[1] ) . '</strong>' . esc_html( $it[2] ) . '</span></li>';
		}
		return $out . '</ul>';
	}
);

/* ---------- Journey timeline ---------- */
add_shortcode(
	'smile_journey',
	function () {
		$items = array(
			array( '2009', __( 'A self-help group', 'smile-community' ), __( 'Margret registers Smile, training women and youth in beadwork, detergents and food products to feed street children.', 'smile-community' ) ),
			array( __( 'Early years', 'smile-community' ), __( 'An iron-sheet home', 'smile-community' ), __( 'Children live in a rented iron-sheet room near Sabasaba and study at night by kerosene lamp.', 'smile-community' ) ),
			array( '2016', __( 'Registered as a CBO', 'smile-community' ), __( 'Smile becomes a Community Based Organisation supporting orphaned and vulnerable children.', 'smile-community' ) ),
			array( __( 'Moving on', 'smile-community' ), __( 'From flood to safety', 'smile-community' ), __( 'A reclaimed site at Steppa Stage floods. With well-wishers and Thrive Kenya, the home moves to Mzesa, Soweto.', 'smile-community' ) ),
			array( __( 'Today', 'smile-community' ), __( 'Growing futures', 'smile-community' ), __( '300+ children reached by community feeding, and former residents studying at university.', 'smile-community' ) ),
		);
		$out = '<ol class="smile-journey">';
		foreach ( $items as $i => $it ) {
			$out .= '<li class="smile-journey__item smile-reveal" style="--i:' . (int) $i . '"><span class="smile-journey__dot" aria-hidden="true"></span><span class="smile-journey__when">' . esc_html( $it[0] ) . '</span><strong class="smile-journey__title">' . esc_html( $it[1] ) . '</strong><span class="smile-journey__text">' . esc_html( $it[2] ) . '</span></li>';
		}
		return $out . '</ol>';
	}
);

/* ---------- Priorities ---------- */
add_shortcode(
	'smile_priorities',
	function () {
		$items = array(
			array( 'schoolkids', 'book', __( 'Keep children in school', 'smile-community' ), __( 'Fees, learning materials and required school costs so learning is never interrupted.', 'smile-community' ), __( 'Support Education', 'smile-community' ), __( 'Urgent', 'smile-community' ) ),
			array( 'feeding2', 'water', __( 'Safe water and daily meals', 'smile-community' ), __( 'About KES 2,500 fills our 1,500-litre and 3,000-litre tanks for drinking, cooking and hygiene.', 'smile-community' ), __( 'Give for water and meals', 'smile-community' ), __( 'Every week', 'smile-community' ) ),
			array( 'doctors', 'health', __( 'Medical care', 'smile-community' ), __( 'Some children arrive needing urgent treatment. Gifts help us respond quickly.', 'smile-community' ), __( 'Give for medical care', 'smile-community' ), __( 'Ongoing', 'smile-community' ) ),
		);
		$out = '<div class="smile-prio">';
		foreach ( $items as $it ) {
			$out .= '<article class="smile-prio__card smile-reveal"><div class="smile-prio__media">' . smile_img( $it[0], 'smile-card', 'smile-prio__img' ) . '<span class="smile-prio__tag">' . esc_html( $it[5] ) . '</span></div>'
				. '<div class="smile-prio__body">' . smile_icon( $it[1], 'smile-prio__icon' ) . '<h3>' . esc_html( $it[2] ) . '</h3><p>' . esc_html( $it[3] ) . '</p>'
				. '<a class="smile-btn smile-btn--teal" href="' . esc_url( smile_donate_url() ) . '">' . esc_html( $it[4] ) . '</a></div></article>';
		}
		return $out . '</div>';
	}
);

/* ---------- Get involved ---------- */
add_shortcode(
	'smile_involve',
	function () {
		$paybill = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_paybill' ) : '247247';
		$account = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_account' ) : '147516';
		$ways    = array(
			array( 'hands', __( 'Volunteer', 'smile-community' ), __( 'Teach, mentor, coach or help with daily care.', 'smile-community' ), '/volunteer/' ),
			array( 'users', __( 'Partner with us', 'smile-community' ), __( 'Companies, schools, churches and groups.', 'smile-community' ), '/partner-with-us/' ),
			array( 'gift', __( 'Donate food and supplies', 'smile-community' ), __( 'Food, clothes, books and sports kit.', 'smile-community' ), '/contact-us/' ),
			array( 'pin', __( 'Visit the centre', 'smile-community' ), __( 'Arrange your visit by phone or WhatsApp.', 'smile-community' ), '/contact-us/' ),
			array( 'book', __( 'Sponsor education', 'smile-community' ), __( 'Help a child through primary and secondary school.', 'smile-community' ), '/education-program/' ),
			array( 'star', __( 'Share our story', 'smile-community' ), __( 'Follow us and tell friends about Smile.', 'smile-community' ), '/stories/' ),
		);
		$out  = '<div class="smile-involve">';
		$out .= '<div class="smile-involve__give smile-reveal"><p class="smile-involve__kicker">' . esc_html__( 'Give today', 'smile-community' ) . '</p><h3>' . esc_html__( 'Give by M-Pesa in under a minute', 'smile-community' ) . '</h3>'
			. '<div class="smile-involve__pay"><div><span>' . esc_html__( 'Paybill', 'smile-community' ) . '</span><strong>' . esc_html( $paybill ) . '</strong></div><button type="button" class="smile-copy" data-smile-copy="' . esc_attr( $paybill ) . '" aria-label="' . esc_attr__( 'Copy Paybill number', 'smile-community' ) . '">' . esc_html__( 'Copy', 'smile-community' ) . '</button></div>'
			. '<div class="smile-involve__pay"><div><span>' . esc_html__( 'Account', 'smile-community' ) . '</span><strong>' . esc_html( $account ) . '</strong></div><button type="button" class="smile-copy" data-smile-copy="' . esc_attr( $account ) . '" aria-label="' . esc_attr__( 'Copy account number', 'smile-community' ) . '">' . esc_html__( 'Copy', 'smile-community' ) . '</button></div>'
			. '<a class="smile-btn smile-btn--sun" href="' . esc_url( smile_donate_url() ) . '">' . esc_html__( 'More ways to give', 'smile-community' ) . '</a></div>';
		$out .= '<div class="smile-involve__ways">';
		foreach ( $ways as $wy ) {
			$out .= '<a class="smile-involve__way smile-reveal" href="' . smile_link( $wy[3] ) . '">' . smile_icon( $wy[0] ) . '<span><strong>' . esc_html( $wy[1] ) . '</strong>' . esc_html( $wy[2] ) . '</span><span class="smile-involve__arrow" aria-hidden="true">&rarr;</span></a>';
		}
		return $out . '</div></div>';
	}
);

/* ---------- Community: testimonials + friends ---------- */
add_shortcode(
	'smile_community',
	function () {
		$quotes  = array(
			array( __( 'Such a beautiful place with beautiful people. From the management, to the kids here. So much love.', 'smile-community' ), 'Ruth Kamau', __( 'Volunteer', 'smile-community' ) ),
			array( __( 'We did a CSR as AFC fans Kayole branch and it was quite an encounter. Good reception and gestures from the staff and the children as well.', 'smile-community' ), 'Hawkins Isabwa', __( 'AFC Leopards Fans, Kayole Branch', 'smile-community' ) ),
			array( __( 'No matter how small you are willing to give, these kids need it because it will make a difference and put a smile on them.', 'smile-community' ), 'Maureen Syokau', __( 'Supporter', 'smile-community' ) ),
		);
		$friends = array( 'Lift the Children', 'Thrive Kenya', 'Rotary Club', 'AFC Leopards Fans Kayole', 'IEBC', __( 'Volunteer doctors', 'smile-community' ), __( 'University volunteers', 'smile-community' ), __( "Children's officers", 'smile-community' ), __( 'Local chiefs and police', 'smile-community' ) );
		$out     = '<div class="smile-voices">';
		foreach ( $quotes as $i => $q ) {
			$initials = implode( '', array_map( function ( $p ) { return mb_substr( $p, 0, 1 ); }, explode( ' ', $q[1] ) ) );
			$out     .= '<figure class="smile-voice' . ( 0 === $i ? ' smile-voice--lead' : '' ) . ' smile-reveal"><div class="smile-voice__stars" aria-label="' . esc_attr__( 'Google review', 'smile-community' ) . '">' . str_repeat( smile_icon( 'star' ), 5 ) . '</div><blockquote>' . esc_html( $q[0] ) . '</blockquote><figcaption><span class="smile-voice__avatar" aria-hidden="true">' . esc_html( $initials ) . '</span><span><strong>' . esc_html( $q[1] ) . '</strong>' . esc_html( $q[2] ) . ' &middot; ' . esc_html__( 'Google review', 'smile-community' ) . '</span></figcaption></figure>';
		}
		$out .= '</div><div class="smile-friends"><p class="smile-friends__label">' . esc_html__( 'With thanks to friends who have stood with us', 'smile-community' ) . '</p><ul>';
		foreach ( $friends as $f ) {
			$out .= '<li>' . esc_html( $f ) . '</li>';
		}
		return $out . '</ul></div>';
	}
);

/* ---------- Stories magazine ---------- */
add_shortcode(
	'smile_stories',
	function () {
		$q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
		if ( ! $q->have_posts() ) {
			return '';
		}
		$out = '<div class="smile-mag">';
		$i   = 0;
		while ( $q->have_posts() ) {
			$q->the_post();
			$cats = get_the_category();
			$lead = 0 === $i;
			$out .= '<article class="smile-mag__item' . ( $lead ? ' smile-mag__item--lead' : '' ) . ' smile-reveal"><a class="smile-mag__media" href="' . esc_url( get_permalink() ) . '" tabindex="-1" aria-hidden="true">' . smile_story_image( get_the_ID() ) . '</a><div class="smile-mag__body">'
				. ( $cats ? '<span class="smile-mag__cat">' . esc_html( $cats[0]->name ) . '</span>' : '' )
				. '<h3 class="smile-mag__title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>'
				. ( $lead ? '<p class="smile-mag__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 30 ) ) . '</p>' : '' )
				. '<span class="smile-mag__meta"><time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time> &middot; ' . esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ) . '</span></div></article>';
			++$i;
		}
		wp_reset_postdata();
		return $out . '</div>';
	}
);

/* ---------- Full-bleed photo CTA ---------- */
add_shortcode(
	'smile_cta_photo',
	function () {
		$paybill = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_paybill' ) : '247247';
		return '<section class="smile-ctaphoto">' . smile_img( 'highschool', 'smile-hero', 'smile-ctaphoto__img' ) . '<span class="smile-ctaphoto__shade" aria-hidden="true"></span>'
			. '<div class="smile-container smile-ctaphoto__inner smile-reveal"><p class="smile-ctaphoto__kicker">' . esc_html__( 'Be part of their story', 'smile-community' ) . '</p>'
			. '<h2>' . esc_html__( 'Give a child a reason to smile today', 'smile-community' ) . '</h2>'
			. '<p>' . esc_html__( 'Every gift, big or small, helps provide a safe home, meals, schooling and care. Thank you for standing with the children of Soweto.', 'smile-community' ) . '</p>'
			. '<div class="smile-btn-row"><a class="smile-btn smile-btn--sun" href="' . esc_url( smile_donate_url() ) . '">' . esc_html__( 'Donate Now', 'smile-community' ) . '</a><a class="smile-btn smile-btn--ghost-light" href="' . smile_link( '/volunteer/' ) . '">' . esc_html__( 'Volunteer With Us', 'smile-community' ) . '</a></div>'
			. '<p class="smile-ctaphoto__pay">' . esc_html( sprintf( /* translators: %s paybill */ __( 'M-Pesa Paybill %s', 'smile-community' ), $paybill ) ) . '</p></div></section>';
	}
);


/* ---------- Related programmes (programme pages) ---------- */
add_shortcode(
	'smile_related',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'current' => '' ), $atts, 'smile_related' );
		$items = array(
			'education-program'      => array( 'pupils', 'book', __( 'Education', 'smile-community' ) ),
			'feeding-program'        => array( 'feeding1', 'bowl', __( 'Feeding programme', 'smile-community' ) ),
			'child-care-and-shelter' => array( 'staff-serving', 'home', __( 'Shelter and care', 'smile-community' ) ),
			'health-and-wellbeing'   => array( 'doctors', 'health', __( 'Health and wellbeing', 'smile-community' ) ),
			'sports'                 => array( 'football', 'ball', __( 'Sports and talent', 'smile-community' ) ),
		);
		$out = '<div class="smile-related">';
		foreach ( $items as $slug => $it ) {
			if ( $slug === $atts['current'] ) {
				continue;
			}
			$out .= '<a class="smile-related__item smile-reveal" href="' . smile_link( '/' . $slug . '/' ) . '">' . smile_img( $it[0], 'smile-card', 'smile-related__img' ) . '<span class="smile-related__shade" aria-hidden="true"></span><span class="smile-related__label">' . smile_icon( $it[1] ) . esc_html( $it[2] ) . '</span></a>';
		}
		return $out . '</div>';
	}
);

/* ---------- Founder quote ---------- */
add_shortcode(
	'smile_founder_quote',
	function () {
		return '<figure class="smile-founder smile-reveal"><div class="smile-founder__photo">' . smile_img( 'founder-serving', 'medium_large', 'smile-founder__img' ) . '</div>'
			. '<div class="smile-founder__body"><blockquote>' . esc_html__( 'A simple smile, joined with practical care, can change a child’s world. That belief is why Smile exists.', 'smile-community' ) . '</blockquote>'
			. '<figcaption><span class="smile-signature">Margret Nekesa</span><span class="smile-founder__role">' . esc_html__( 'Founder, Smile Community Centre', 'smile-community' ) . '</span></figcaption>'
			. '<a class="smile-btn smile-btn--ghost" href="' . smile_link( '/our-founder/' ) . '">' . esc_html__( 'Read Margret’s story', 'smile-community' ) . '</a></div></figure>';
	}
);

/* ---------- Help card (FAQ, sidebars) ---------- */
add_shortcode(
	'smile_help_card',
	function () {
		$phone = smile_setting( 'phone_primary', '0721 318 869' );
		$wa    = preg_replace( '/\D/', '', smile_setting( 'whatsapp', '254721318869' ) );
		$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
		$out   = '<aside class="smile-help"><p class="smile-hand">' . esc_html__( 'Still have a question?', 'smile-community' ) . '</p><p>' . esc_html__( 'Our team is happy to help. Reach us during office hours, Monday to Friday.', 'smile-community' ) . '</p><ul>';
		$out  .= '<li><a href="' . esc_url( smile_tel_href( $phone ) ) . '">' . smile_icon( 'phone' ) . esc_html( $phone ) . '</a></li>';
		if ( $wa ) {
			$out .= '<li><a href="' . esc_url( 'https://wa.me/' . $wa ) . '" rel="noopener">' . smile_icon( 'chat' ) . esc_html__( 'WhatsApp us', 'smile-community' ) . '</a></li>';
		}
		$out .= '<li><a href="' . esc_url( 'mailto:' . antispambot( $email ) ) . '">' . smile_icon( 'mail' ) . esc_html( antispambot( $email ) ) . '</a></li></ul>';
		$out .= '<a class="smile-btn smile-btn--teal" href="' . smile_link( '/contact-us/' ) . '">' . esc_html__( 'Send us a message', 'smile-community' ) . '</a></aside>';
		return $out;
	}
);


/* ---------- Programme page components ---------- */
/**
 * Verified content per programme.
 *
 * @return array
 */
function smile_programme_data() {
	return array(
		'education' => array(
			'glance' => array( array( 'book', __( 'Every child in our care is enrolled in school', 'smile-community' ) ), array( 'calendar', __( 'Support through primary and secondary school', 'smile-community' ) ), array( 'star', __( 'Former residents now at Kenyan universities', 'smile-community' ) ) ),
			'quote'  => array( 'highschool', __( 'Education is light to all people in the world.', 'smile-community' ), __( 'From the centre’s founding vision', 'smile-community' ) ),
			'help'   => array(
				array( 'gift', __( 'Pay towards school fees', 'smile-community' ), __( 'Fees, uniforms and the costs that keep a child in class.', 'smile-community' ), __( 'Give for education', 'smile-community' ), 'donate' ),
				array( 'book', __( 'Donate books and stationery', 'smile-community' ), __( 'Textbooks, exercise books, pens and school bags.', 'smile-community' ), __( 'Arrange a donation', 'smile-community' ), '/contact-us/' ),
				array( 'hands', __( 'Tutor or mentor', 'smile-community' ), __( 'Help with homework, reading or career guidance.', 'smile-community' ), __( 'Volunteer', 'smile-community' ), '/volunteer/' ),
				array( 'users', __( 'Support life after school', 'smile-community' ), __( 'Help young people into college, training and work.', 'smile-community' ), __( 'Partner with us', 'smile-community' ), '/partner-with-us/' ),
			),
		),
		'feeding'   => array(
			'glance' => array( array( 'bowl', __( 'Daily meals for every child at home', 'smile-community' ) ), array( 'users', __( '300+ children reached by community feeding', 'smile-community' ) ), array( 'home', __( 'Run with homes in Kayole, Donholm and Soweto', 'smile-community' ) ) ),
			'quote'  => array( 'mealtime', __( 'No child should learn or grow on an empty stomach.', 'smile-community' ), __( 'Smile Community Centre', 'smile-community' ) ),
			'help'   => array(
				array( 'bowl', __( 'Give for meals and water', 'smile-community' ), __( 'About KES 2,500 fills both of our water tanks.', 'smile-community' ), __( 'Give now', 'smile-community' ), 'donate' ),
				array( 'gift', __( 'Donate food', 'smile-community' ), __( 'Flour, cereals and cooking essentials.', 'smile-community' ), __( 'Arrange a donation', 'smile-community' ), '/contact-us/' ),
				array( 'users', __( 'Host a feeding day', 'smile-community' ), __( 'Bring your group, church or company to serve a meal.', 'smile-community' ), __( 'Plan with us', 'smile-community' ), '/partner-with-us/' ),
			),
		),
		'care'      => array(
			'glance' => array( array( 'shield', __( 'Placements authorised by police, courts or children’s office', 'smile-community' ) ), array( 'home', __( 'Shelter, meals, clothing and medical care', 'smile-community' ) ), array( 'heart', __( 'Reunited with family where it is safe', 'smile-community' ) ) ),
			'quote'  => array( 'gift', __( 'Every child deserves a safe place to sleep, eat, learn and heal.', 'smile-community' ), __( 'Smile Community Centre', 'smile-community' ) ),
			'help'   => array(
				array( 'heart', __( 'Give for daily care', 'smile-community' ), __( 'Food, hygiene and safe care for children in our home.', 'smile-community' ), __( 'Give now', 'smile-community' ), 'donate' ),
				array( 'gift', __( 'Donate clothing and bedding', 'smile-community' ), __( 'Clean clothes, blankets, mattresses and shoes.', 'smile-community' ), __( 'Arrange a donation', 'smile-community' ), '/contact-us/' ),
				array( 'hands', __( 'Volunteer alongside staff', 'smile-community' ), __( 'Under supervision and our code of conduct.', 'smile-community' ), __( 'Volunteer', 'smile-community' ), '/volunteer/' ),
			),
		),
		'health'    => array(
			'glance' => array( array( 'health', __( 'Medical care and referrals for every child', 'smile-community' ) ), array( 'heart', __( 'HIV/AIDS counselling and support', 'smile-community' ) ), array( 'water', __( 'Clean water stored in 1,500L and 3,000L tanks', 'smile-community' ) ) ),
			'quote'  => array( 'doctors', __( 'Caring for body and mind, for every child in our home.', 'smile-community' ), __( 'Smile Community Centre', 'smile-community' ) ),
			'help'   => array(
				array( 'health', __( 'Give for medical care', 'smile-community' ), __( 'Treatment for children who arrive needing urgent care.', 'smile-community' ), __( 'Give now', 'smile-community' ), 'donate' ),
				array( 'water', __( 'Give for clean water', 'smile-community' ), __( 'About KES 2,500 fills both tanks for a week.', 'smile-community' ), __( 'Give for water', 'smile-community' ), 'donate' ),
				array( 'hands', __( 'Volunteer your skills', 'smile-community' ), __( 'Doctors, nurses and counsellors are always welcome.', 'smile-community' ), __( 'Volunteer', 'smile-community' ), '/volunteer/' ),
			),
		),
		'sports'    => array(
			'glance' => array( array( 'ball', __( 'Football and basketball', 'smile-community' ) ), array( 'calendar', __( 'Weekend matches at Jacaranda Grounds', 'smile-community' ) ), array( 'gift', __( 'Kit and equipment always welcome', 'smile-community' ) ) ),
			'quote'  => array( 'skipping', __( 'Even half an hour of play can make a difference in a child’s growth.', 'smile-community' ), __( 'From the centre’s sports programme', 'smile-community' ) ),
			'help'   => array(
				array( 'ball', __( 'Donate sports kit', 'smile-community' ), __( 'Footballs, gloves, kits and boots.', 'smile-community' ), __( 'Arrange a donation', 'smile-community' ), '/contact-us/' ),
				array( 'hands', __( 'Volunteer as a coach', 'smile-community' ), __( 'Share your game and your time on weekends.', 'smile-community' ), __( 'Volunteer', 'smile-community' ), '/volunteer/' ),
				array( 'users', __( 'Sponsor a team day', 'smile-community' ), __( 'Bring your club or company for a match day.', 'smile-community' ), __( 'Partner with us', 'smile-community' ), '/partner-with-us/' ),
			),
		),
	);
}

/**
 * Programme data by key.
 *
 * @param array $atts Attributes.
 * @return array
 */
function smile_programme( $atts ) {
	$atts = shortcode_atts( array( 'p' => 'education' ), $atts );
	$all  = smile_programme_data();
	return isset( $all[ $atts['p'] ] ) ? $all[ $atts['p'] ] : $all['education'];
}

add_shortcode(
	'smile_glance',
	function ( $atts ) {
		$d   = smile_programme( $atts );
		$out = '<ul class="smile-glance">';
		foreach ( $d['glance'] as $i => $g ) {
			$out .= '<li class="smile-reveal" style="--i:' . (int) $i . '">' . smile_icon( $g[0] ) . '<span>' . esc_html( $g[1] ) . '</span></li>';
		}
		return $out . '</ul>';
	}
);

add_shortcode(
	'smile_pull',
	function ( $atts ) {
		$d = smile_programme( $atts );
		$q = $d['quote'];
		return '<figure class="smile-pull">' . smile_img( $q[0], 'smile-hero', 'smile-pull__img' ) . '<span class="smile-pull__shade" aria-hidden="true"></span>'
			. '<div class="smile-container smile-pull__inner smile-reveal"><span class="smile-pull__mark" aria-hidden="true">&ldquo;</span><blockquote>' . esc_html( $q[1] ) . '</blockquote><figcaption>' . esc_html( $q[2] ) . '</figcaption></div></figure>';
	}
);

add_shortcode(
	'smile_help_ways',
	function ( $atts ) {
		$d   = smile_programme( $atts );
		$out = '<div class="smile-ways smile-ways--' . count( $d['help'] ) . '">';
		foreach ( $d['help'] as $i => $h ) {
			$url  = 'donate' === $h[4] ? smile_donate_url() : home_url( $h[4] );
			$main = 'donate' === $h[4];
			$out .= '<article class="smile-way' . ( $main ? ' smile-way--main' : '' ) . ' smile-reveal" style="--i:' . (int) $i . '">' . smile_icon( $h[0] ) . '<h3>' . esc_html( $h[1] ) . '</h3><p>' . esc_html( $h[2] ) . '</p><a class="smile-btn ' . ( $main ? 'smile-btn--sun' : 'smile-btn--ghost' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $h[3] ) . '</a></article>';
		}
		return $out . '</div>';
	}
);


/* ---------- Gift calculator: see what your gift can do ---------- */
add_shortcode(
	'smile_gift_calc',
	function ( $atts ) {
		$atts    = shortcode_atts( array( 'cta' => 'yes' ), $atts, 'smile_gift_calc' );
		$paybill = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_paybill' ) : '247247';
		$account = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_account' ) : '147516';
		$amounts = array( 500, 1000, 2500, 5000, 8000, 15000 );
		$uid     = wp_unique_id( 'smile-calc-' );
		ob_start();
		?>
		<div class="smile-calc smile-reveal" data-meal="500" data-water="2500" data-fees="15000" data-usd="100">
			<div class="smile-calc__pick">
				<p class="smile-calc__label" id="<?php echo esc_attr( $uid ); ?>-l"><?php esc_html_e( 'Choose an amount', 'smile-community' ); ?></p>
				<div class="smile-calc__chips" role="group" aria-labelledby="<?php echo esc_attr( $uid ); ?>-l">
					<?php foreach ( $amounts as $a ) : ?>
						<button type="button" class="smile-calc__chip<?php echo 2500 === $a ? ' is-active' : ''; ?>" data-amount="<?php echo esc_attr( $a ); ?>" aria-pressed="<?php echo 2500 === $a ? 'true' : 'false'; ?>">KES <?php echo esc_html( number_format_i18n( $a ) ); ?></button>
					<?php endforeach; ?>
				</div>
				<label class="smile-calc__label" for="<?php echo esc_attr( $uid ); ?>-in"><?php esc_html_e( 'Or type your own amount (KES)', 'smile-community' ); ?></label>
				<div class="smile-calc__input"><span aria-hidden="true">KES</span><input id="<?php echo esc_attr( $uid ); ?>-in" type="number" inputmode="numeric" min="50" step="50" value="2500"></div>
				<p class="smile-calc__note"><?php esc_html_e( 'Estimates from the centre’s own giving guide. Gifts are used where the need is greatest unless you tell us otherwise.', 'smile-community' ); ?></p>
			</div>
			<div class="smile-calc__result" aria-live="polite">
				<p class="smile-calc__amount"><span data-out="amount">KES 2,500</span><small data-out="usd">about US$25</small></p>
				<ul class="smile-calc__list">
					<li><?php echo smile_icon( 'bowl' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong data-out="meals">5</strong> <?php esc_html_e( 'days of meals and clean water for 3 children', 'smile-community' ); ?></span></li>
					<li><?php echo smile_icon( 'water' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'or', 'smile-community' ); ?> <strong data-out="water">1</strong> <span data-out="water-unit"><?php esc_html_e( 'week', 'smile-community' ); ?></span> <?php esc_html_e( 'of clean water for the whole home', 'smile-community' ); ?></span></li>
					<li class="smile-calc__big" data-out="fees" hidden><?php echo smile_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Enough to help cover school fees or medical treatment for one child', 'smile-community' ); ?></span></li>
				</ul>
				<div class="smile-calc__pay">
					<div><span><?php esc_html_e( 'Paybill', 'smile-community' ); ?></span><strong><?php echo esc_html( $paybill ); ?></strong><button type="button" class="smile-copy" data-smile-copy="<?php echo esc_attr( $paybill ); ?>"><?php esc_html_e( 'Copy', 'smile-community' ); ?></button></div>
					<div><span><?php esc_html_e( 'Account', 'smile-community' ); ?></span><strong><?php echo esc_html( $account ); ?></strong><button type="button" class="smile-copy" data-smile-copy="<?php echo esc_attr( $account ); ?>"><?php esc_html_e( 'Copy', 'smile-community' ); ?></button></div>
					<div><span><?php esc_html_e( 'Amount', 'smile-community' ); ?></span><strong data-out="amount-plain">2,500</strong><button type="button" class="smile-copy" data-out="amount-copy" data-smile-copy="2500"><?php esc_html_e( 'Copy', 'smile-community' ); ?></button></div>
				</div>
				<?php if ( 'yes' === $atts['cta'] ) : ?>
					<a class="smile-btn smile-btn--sun smile-calc__cta" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php esc_html_e( 'Give by M-Pesa', 'smile-community' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
);
