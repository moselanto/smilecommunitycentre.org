<?php
/**
 * Home page hero slider: [smile_hero_slider].
 * Slides are edited in Appearance > Customize > Smile: Home slider (image, text, buttons).
 * Accessible: pause control, keyboard and swipe, no autoplay with reduced motion,
 * inactive slides hidden from assistive tech.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default slide content (verified copy) and the photo key each uses.
 *
 * @return array
 */
function smile_slider_defaults() {
	return array(
		1 => array(
			'img'     => 'founder-serving',
			'eyebrow' => __( "Children's home in Soweto, Kayole, Nairobi", 'smile-community' ),
			'title'   => __( 'Every child deserves safety, care and a chance to thrive', 'smile-community' ),
			'text'    => __( 'We give orphaned, abandoned and vulnerable children a safe home, daily meals, schooling and access to healthcare.', 'smile-community' ),
			'b1'      => __( 'Donate to Support a Child', 'smile-community' ),
			'u1'      => '/donate/',
			'b2'      => __( 'Discover Our Work', 'smile-community' ),
			'u2'      => '/our-programmes/',
		),
		2 => array(
			'img'     => 'pupils',
			'eyebrow' => __( 'Education programme', 'smile-community' ),
			'title'   => __( 'Keep a child in school this term', 'smile-community' ),
			'text'    => __( 'Every child in our care is enrolled in school. Your gift pays fees, uniforms, books and the costs that keep them learning.', 'smile-community' ),
			'b1'      => __( 'Support Education', 'smile-community' ),
			'u1'      => '/donate/',
			'b2'      => __( 'Our Education Programme', 'smile-community' ),
			'u2'      => '/education-program/',
		),
		3 => array(
			'img'     => 'mealtime',
			'eyebrow' => __( 'Feeding programme', 'smile-community' ),
			'title'   => __( 'A warm meal, every single day', 'smile-community' ),
			'text'    => __( 'Daily meals at home, and a community feeding programme reaching more than 300 children in Soweto and Kayole.', 'smile-community' ),
			'b1'      => __( 'Give a Meal', 'smile-community' ),
			'u1'      => '/donate/',
			'b2'      => __( 'Partner With Smile', 'smile-community' ),
			'u2'      => '/partner-with-us/',
		),
	);
}

/**
 * Attachment ID imported for a photo key.
 *
 * @param string $key Key.
 * @return int
 */
function smile_image_by_key( $key ) {
	$ids = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_smile_img_key', 'meta_value' => $key ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $ids ? (int) $ids[0] : 0;
}

/**
 * Resolved slides.
 *
 * @return array
 */
function smile_slider_slides() {
	$slides = array();
	foreach ( smile_slider_defaults() as $n => $d ) {
		$get = function ( $field ) use ( $n, $d ) {
			return get_theme_mod( 'smile_slide_' . $n . '_' . $field, $d[ $field ] );
		};
		$title = trim( (string) $get( 'title' ) );
		if ( '' === $title ) {
			continue;
		}
		$img = absint( get_theme_mod( 'smile_slide_' . $n . '_image', 0 ) );
		if ( ! $img ) {
			$img = smile_image_by_key( $d['img'] );
		}
		$slides[] = array(
			'image'   => $img,
			'eyebrow' => $get( 'eyebrow' ),
			'title'   => $title,
			'text'    => $get( 'text' ),
			'b1'      => $get( 'b1' ),
			'u1'      => $get( 'u1' ),
			'b2'      => $get( 'b2' ),
			'u2'      => $get( 'u2' ),
		);
	}
	return $slides;
}

/**
 * Make /path links relative to this install (works in sub-folder staging).
 *
 * @param string $url URL.
 * @return string
 */
function smile_slider_url( $url ) {
	$url = (string) $url;
	return 0 === strpos( $url, '/' ) ? home_url( $url ) : $url;
}

/**
 * Render.
 *
 * @return string
 */
function smile_hero_slider() {
	$slides = smile_slider_slides();
	if ( ! $slides ) {
		return '';
	}
	$count  = count( $slides );
	$paybill = function_exists( 'smile_core_get' ) ? smile_core_get( 'mpesa_paybill' ) : '247247';
	$phone   = function_exists( 'smile_core_get' ) ? smile_core_get( 'phone_primary' ) : '0721 318 869';
	$volunteer = get_page_by_path( 'volunteer' );
	$contact   = get_page_by_path( 'contact-us' );

	ob_start();
	?>
	<section class="smile-slider" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Highlights from Smile Community Centre', 'smile-community' ); ?>" data-count="<?php echo esc_attr( $count ); ?>">
		<div class="smile-slider__track" aria-live="off">
			<?php foreach ( $slides as $i => $s ) : ?>
				<div class="smile-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1 current 2 total */ __( '%1$d of %2$d', 'smile-community' ), $i + 1, $count ) ); ?>"<?php echo 0 === $i ? '' : ' aria-hidden="true" inert'; ?>>
					<div class="smile-slide__media">
						<?php
						if ( $s['image'] ) {
							echo wp_get_attachment_image(
								$s['image'],
								'smile-hero',
								false,
								array(
									'class'         => 'smile-slide__img',
									'sizes'         => '100vw',
									'loading'       => 0 === $i ? 'eager' : 'lazy',
									'fetchpriority' => 0 === $i ? 'high' : 'low',
									'decoding'      => 0 === $i ? 'sync' : 'async',
								)
							);
						}
						?>
					</div>
					<div class="smile-slide__shade" aria-hidden="true"></div>
					<div class="smile-container smile-slide__content">
						<p class="smile-slide__eyebrow"><span class="smile-slide__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( $s['eyebrow'] ); ?></p>
						<?php $tag = 0 === $i ? 'h1' : 'h2'; ?>
						<<?php echo esc_html( $tag ); ?> class="smile-slide__title"><?php echo esc_html( $s['title'] ); ?></<?php echo esc_html( $tag ); ?>>
						<?php if ( $s['text'] ) : ?>
							<p class="smile-slide__text"><?php echo esc_html( $s['text'] ); ?></p>
						<?php endif; ?>
						<div class="smile-btn-row">
							<?php if ( $s['b1'] ) : ?>
								<a class="smile-btn smile-btn--sun" href="<?php echo esc_url( smile_slider_url( $s['u1'] ) ); ?>"><?php echo esc_html( $s['b1'] ); ?></a>
							<?php endif; ?>
							<?php if ( $s['b2'] ) : ?>
								<a class="smile-btn smile-btn--ghost-light" href="<?php echo esc_url( smile_slider_url( $s['u2'] ) ); ?>"><?php echo esc_html( $s['b2'] ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $count > 1 ) : ?>
			<div class="smile-container smile-slider__controls">
				<div class="smile-slider__dots" role="group" aria-label="<?php esc_attr_e( 'Choose a slide', 'smile-community' ); ?>">
					<?php foreach ( $slides as $i => $s ) : ?>
						<button type="button" class="smile-slider__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-go="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1 number 2 title */ __( 'Slide %1$d: %2$s', 'smile-community' ), $i + 1, $s['title'] ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>><span></span></button>
					<?php endforeach; ?>
				</div>
				<div class="smile-slider__arrows">
					<button type="button" class="smile-slider__btn" data-dir="-1" aria-label="<?php esc_attr_e( 'Previous slide', 'smile-community' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2"/></svg></button>
					<button type="button" class="smile-slider__btn smile-slider__pause" aria-pressed="false" aria-label="<?php esc_attr_e( 'Pause slideshow', 'smile-community' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true" class="i-pause"><path d="M8 5v14M16 5v14" fill="none" stroke="currentColor" stroke-width="2.4"/></svg><svg viewBox="0 0 24 24" aria-hidden="true" class="i-play"><path d="M8 5l11 7-11 7z" fill="currentColor"/></svg></button>
					<button type="button" class="smile-slider__btn" data-dir="1" aria-label="<?php esc_attr_e( 'Next slide', 'smile-community' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2"/></svg></button>
				</div>
			</div>
			<div class="smile-slider__progress" aria-hidden="true"><span></span></div>
		<?php endif; ?>
	</section>

	<div class="smile-quick">
		<div class="smile-container smile-quick__grid">
			<a class="smile-quick__item" href="<?php echo esc_url( smile_donate_url() ); ?>">
				<span class="smile-quick__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s-7.5-4.6-9.6-9.3C.9 8.3 3 4.5 6.7 4.5c2 0 3.5 1.1 4.3 2.6.8-1.5 2.3-2.6 4.3-2.6 3.7 0 5.8 3.8 4.3 7.2C19.5 16.4 12 21 12 21z" fill="currentColor"/></svg></span>
				<span><strong><?php esc_html_e( 'Give by M-Pesa', 'smile-community' ); ?></strong><span><?php echo esc_html( sprintf( /* translators: %s paybill */ __( 'Paybill %s', 'smile-community' ), $paybill ) ); ?></span></span>
			</a>
			<?php if ( $volunteer ) : ?>
			<a class="smile-quick__item" href="<?php echo esc_url( get_permalink( $volunteer ) ); ?>">
				<span class="smile-quick__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="3.5" fill="currentColor"/><path d="M4.5 20c.6-4 3.6-6.5 7.5-6.5s6.9 2.5 7.5 6.5z" fill="currentColor"/></svg></span>
				<span><strong><?php esc_html_e( 'Volunteer with us', 'smile-community' ); ?></strong><span><?php esc_html_e( 'Teaching, mentoring, sports and more', 'smile-community' ); ?></span></span>
			</a>
			<?php endif; ?>
			<a class="smile-quick__item" href="<?php echo esc_url( $contact ? get_permalink( $contact ) : smile_tel_href( $phone ) ); ?>">
				<span class="smile-quick__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2.5c-3.9 0-7 3-7 6.9 0 5 7 12.1 7 12.1s7-7.1 7-12.1c0-3.9-3.1-6.9-7-6.9zm0 9.4a2.5 2.5 0 110-5 2.5 2.5 0 010 5z" fill="currentColor"/></svg></span>
				<span><strong><?php esc_html_e( 'Visit the centre', 'smile-community' ); ?></strong><span><?php echo esc_html( sprintf( /* translators: %s phone */ __( 'Call or WhatsApp %s', 'smile-community' ), $phone ) ); ?></span></span>
			</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'smile_hero_slider', 'smile_hero_slider' );

/**
 * Customizer: edit slides.
 *
 * @param WP_Customize_Manager $c Manager.
 */
function smile_slider_customize( $c ) {
	$c->add_section( 'smile_slider', array( 'title' => __( 'Smile: Home slider', 'smile-community' ), 'priority' => 31, 'description' => __( 'Up to three slides. Leave a slide title empty to hide that slide. Use bright, landscape photos at least 1600px wide.', 'smile-community' ) ) );
	$labels = array(
		'eyebrow' => __( 'Small label', 'smile-community' ),
		'title'   => __( 'Headline', 'smile-community' ),
		'text'    => __( 'Short text', 'smile-community' ),
		'b1'      => __( 'Main button text', 'smile-community' ),
		'u1'      => __( 'Main button link', 'smile-community' ),
		'b2'      => __( 'Second button text', 'smile-community' ),
		'u2'      => __( 'Second button link', 'smile-community' ),
	);
	foreach ( smile_slider_defaults() as $n => $d ) {
		/* translators: %d slide number */
		$prefix = sprintf( __( 'Slide %d: ', 'smile-community' ), $n );
		$c->add_setting( 'smile_slide_' . $n . '_image', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$c->add_control( new WP_Customize_Media_Control( $c, 'smile_slide_' . $n . '_image', array( 'label' => $prefix . __( 'Photo', 'smile-community' ), 'section' => 'smile_slider', 'mime_type' => 'image' ) ) );
		foreach ( $labels as $field => $label ) {
			$is_url = in_array( $field, array( 'u1', 'u2' ), true );
			$c->add_setting( 'smile_slide_' . $n . '_' . $field, array( 'default' => $d[ $field ], 'sanitize_callback' => $is_url ? 'smile_sanitize_link' : ( 'text' === $field ? 'sanitize_textarea_field' : 'sanitize_text_field' ) ) );
			$c->add_control( 'smile_slide_' . $n . '_' . $field, array( 'label' => $prefix . $label, 'section' => 'smile_slider', 'type' => 'text' === $field ? 'textarea' : 'text' ) );
		}
	}
}
add_action( 'customize_register', 'smile_slider_customize' );

/**
 * Allow relative (/donate/) or absolute links.
 *
 * @param string $v Value.
 * @return string
 */
function smile_sanitize_link( $v ) {
	$v = trim( (string) $v );
	if ( 0 === strpos( $v, '/' ) ) {
		return '/' . ltrim( sanitize_text_field( $v ), '/' );
	}
	return esc_url_raw( $v );
}
