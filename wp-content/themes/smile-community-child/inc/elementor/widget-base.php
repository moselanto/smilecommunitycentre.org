<?php
/**
 * Base class for the Smile Elementor widgets, plus the small header/footer
 * building blocks (logo, menu, social icons, Donate button).
 *
 * Loaded only from the elementor/widgets/register action, so Elementor's
 * classes always exist here.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Shared style controls for all Smile widgets.
 */
trait Smile_Elementor_Style_Controls {

	/**
	 * Alignment, colours and typography.
	 *
	 * @param bool $buttons Add button colours.
	 */
	protected function smile_style_controls( $buttons = true ) {
		$this->start_controls_section(
			'smile_style_text',
			array(
				'label' => __( 'Text', 'smile-community' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'smile_align',
			array(
				'label'     => __( 'Alignment', 'smile-community' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array( 'title' => __( 'Left', 'smile-community' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Centre', 'smile-community' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => __( 'Right', 'smile-community' ), 'icon' => 'eicon-text-align-right' ),
				),
				'selectors' => array( '{{WRAPPER}} .elementor-widget-container' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'smile_heading_color',
			array(
				'label'     => __( 'Heading colour', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3, {{WRAPPER}} h4' => 'color: {{VALUE}};' ),
			)
		);
		if ( class_exists( '\Elementor\Group_Control_Typography' ) ) {
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'smile_heading_typo',
					'label'    => __( 'Heading typography', 'smile-community' ),
					'selector' => '{{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3, {{WRAPPER}} h4',
				)
			);
		}
		$this->add_control(
			'smile_text_color',
			array(
				'label'     => __( 'Text colour', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} p, {{WRAPPER}} li, {{WRAPPER}} span, {{WRAPPER}} dd, {{WRAPPER}} dt, {{WRAPPER}} label' => 'color: {{VALUE}};' ),
			)
		);
		if ( class_exists( '\Elementor\Group_Control_Typography' ) ) {
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'     => 'smile_text_typo',
					'label'    => __( 'Text typography', 'smile-community' ),
					'selector' => '{{WRAPPER}} p, {{WRAPPER}} li',
				)
			);
		}
		$this->add_control(
			'smile_link_color',
			array(
				'label'     => __( 'Link colour', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} a:not(.smile-btn)' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		if ( ! $buttons ) {
			return;
		}
		$this->start_controls_section(
			'smile_style_buttons',
			array(
				'label' => __( 'Buttons', 'smile-community' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'smile_btn_bg',
			array(
				'label'     => __( 'Button background', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .smile-btn, {{WRAPPER}} button[type="submit"]' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'smile_btn_color',
			array(
				'label'     => __( 'Button text colour', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .smile-btn, {{WRAPPER}} button[type="submit"]' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'smile_btn_radius',
			array(
				'label'      => __( 'Button corner radius', 'smile-community' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 50 ) ),
				'selectors'  => array( '{{WRAPPER}} .smile-btn, {{WRAPPER}} button[type="submit"]' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}
}

/**
 * A Smile section as an Elementor widget. Subclasses only set TAG.
 */
abstract class Smile_Elementor_Section_Widget extends Widget_Base {

	use Smile_Elementor_Style_Controls;

	const TAG = '';

	/**
	 * Registry entry.
	 *
	 * @return array
	 */
	protected function smile_entry() {
		return smile_el_entry( static::TAG );
	}

	/**
	 * Widget name, for example smile-mpesa.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'smile-' . str_replace( '_', '-', substr( static::TAG, strlen( 'smile_' ) ) );
	}

	/**
	 * Title in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		$e = $this->smile_entry();
		return 'Smile: ' . $e['title'];
	}

	/**
	 * Icon in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		$icons = array(
			'Giving'             => 'eicon-price-table',
			'Careers'            => 'eicon-person',
			'Contact and visits' => 'eicon-map-pin',
			'Get involved'       => 'eicon-call-to-action',
			'Safeguarding'       => 'eicon-lock-user',
			'About and story'    => 'eicon-testimonial',
			'Header and footer'  => 'eicon-header',
		);
		$e = $this->smile_entry();
		if ( 'smile_site_footer' === static::TAG ) {
			return 'eicon-footer';
		}
		if ( in_array( static::TAG, array( 'smile_form', 'smile_careers_talent' ), true ) ) {
			return 'eicon-form-horizontal';
		}
		return isset( $icons[ $e['group'] ] ) ? $icons[ $e['group'] ] : 'eicon-heart';
	}

	/**
	 * Panel category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'smile' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		$e = $this->smile_entry();
		return array_values( array_unique( array_merge( array( 'smile', strtolower( $e['group'] ) ), explode( '_', substr( static::TAG, strlen( 'smile_' ) ) ) ) ) );
	}

	/**
	 * Smile widgets render on the server.
	 *
	 * @return bool
	 */
	public function is_reload_preview_required() {
		return false;
	}

	/**
	 * Choices for known attributes.
	 *
	 * @param string $name Attribute.
	 * @return array|null
	 */
	protected function smile_att_options( $name ) {
		$tag = static::TAG;
		if ( 'smile_form' === $tag && 'type' === $name ) {
			return array(
				'contact'   => __( 'Contact', 'smile-community' ),
				'visit'     => __( 'Book a visit', 'smile-community' ),
				'volunteer' => __( 'Volunteer application', 'smile-community' ),
				'partner'   => __( 'Partnership enquiry', 'smile-community' ),
				'donation'  => __( 'Donation / pledge', 'smile-community' ),
				'removal'   => __( 'Photo removal request', 'smile-community' ),
				'careers'   => __( 'Talent community (Careers)', 'smile-community' ),
			);
		}
		if ( 'smile_steps' === $tag && 'set' === $name ) {
			return array(
				'volunteer' => __( 'Volunteer', 'smile-community' ),
				'partner'   => __( 'Partner', 'smile-community' ),
				'concern'   => __( 'Raise a concern', 'smile-community' ),
			);
		}
		if ( in_array( $tag, array( 'smile_glance', 'smile_pull', 'smile_help_ways' ), true ) && 'p' === $name && function_exists( 'smile_programme_data' ) ) {
			$out = array();
			foreach ( array_keys( smile_programme_data() ) as $k ) {
				$out[ $k ] = ucwords( str_replace( array( '-', '_' ), ' ', $k ) );
			}
			return $out;
		}
		if ( 'smile_icon' === $tag && 'name' === $name && function_exists( 'smile_icon_paths' ) ) {
			$keys = array_keys( smile_icon_paths() );
			return array_combine( $keys, array_map( 'ucfirst', $keys ) );
		}
		if ( 'smile_gift_calc' === $tag && 'cta' === $name ) {
			return array( 'yes' => __( 'Show', 'smile-community' ), 'no' => __( 'Hide', 'smile-community' ) );
		}
		return null;
	}

	/**
	 * Attributes the widget exposes (from shortcode_atts plus programme pickers).
	 *
	 * @return array list of array( n, v )
	 */
	protected function smile_atts() {
		$e    = $this->smile_entry();
		$atts = isset( $e['atts'] ) ? $e['atts'] : array();
		if ( in_array( static::TAG, array( 'smile_glance', 'smile_pull', 'smile_help_ways' ), true ) ) {
			$atts = array( array( 'n' => 'p', 'v' => 'education' ) );
		}
		return $atts;
	}

	/**
	 * Controls: Settings, Texts, Photos, Style.
	 */
	protected function register_controls() {
		$e = $this->smile_entry();

		$this->start_controls_section(
			'smile_settings',
			array(
				'label' => __( 'Settings', 'smile-community' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		$atts = $this->smile_atts();
		foreach ( $atts as $a ) {
			$options = $this->smile_att_options( $a['n'] );
			$label   = ucwords( str_replace( '_', ' ', $a['n'] ) );
			if ( 'p' === $a['n'] ) {
				$label = __( 'Programme', 'smile-community' );
			}
			if ( $options ) {
				$this->add_control(
					'att_' . $a['n'],
					array(
						'label'   => $label,
						'type'    => Controls_Manager::SELECT,
						'options' => $options,
						'default' => isset( $options[ $a['v'] ] ) ? $a['v'] : (string) key( $options ),
					)
				);
			} elseif ( 'count' === $a['n'] ) {
				$this->add_control(
					'att_' . $a['n'],
					array(
						'label'   => __( 'How many', 'smile-community' ),
						'type'    => Controls_Manager::NUMBER,
						'min'     => 1,
						'max'     => 12,
						'default' => absint( $a['v'] ) ? absint( $a['v'] ) : 3,
					)
				);
			} else {
				$this->add_control(
					'att_' . $a['n'],
					array(
						'label'       => $label,
						'type'        => Controls_Manager::TEXT,
						'default'     => $a['v'],
						'label_block' => true,
						'dynamic'     => array( 'active' => true ),
					)
				);
			}
		}
		if ( 'smile_site_header' === static::TAG ) {
			$this->add_control(
				'mod_smile_show_topbar',
				array(
					'label'   => __( 'Slim contact bar', 'smile-community' ),
					'type'    => Controls_Manager::SELECT,
					'options' => array(
						''    => __( 'Site setting', 'smile-community' ),
						'yes' => __( 'Show', 'smile-community' ),
						'no'  => __( 'Hide', 'smile-community' ),
					),
					'default' => '',
				)
			);
			$this->add_control(
				'mod_smile_donate_label',
				array(
					'label'       => __( 'Donate button text', 'smile-community' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Site setting', 'smile-community' ),
				)
			);
			$this->add_control(
				'mod_smile_donate_url',
				array(
					'label'       => __( 'Donate button link', 'smile-community' ),
					'type'        => Controls_Manager::TEXT,
					'placeholder' => __( 'Site setting', 'smile-community' ),
					'label_block' => true,
				)
			);
		}
		if ( 'smile_site_footer' === static::TAG ) {
			$this->add_control(
				'mod_smile_footer_tagline',
				array(
					'label'       => __( 'Footer mission line', 'smile-community' ),
					'type'        => Controls_Manager::TEXTAREA,
					'placeholder' => __( 'Site setting', 'smile-community' ),
				)
			);
		}
		$this->add_control(
			'smile_settings_note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<p class="smile-el-note">' . esc_html(
					( $atts || in_array( static::TAG, array( 'smile_site_header', 'smile_site_footer' ), true ) )
						? __( 'Phone numbers, M-Pesa, email and address come from Smile Centre > Centre details. Menus are edited in Appearance > Menus.', 'smile-community' )
						: __( 'This section has no extra settings. Change its wording in Texts and its photos in Photos below; contact and payment details come from Smile Centre > Centre details.', 'smile-community' )
				) . '</p>',
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->end_controls_section();

		if ( ! empty( $e['texts'] ) ) {
			$this->start_controls_section(
				'smile_texts',
				array(
					/* translators: %d: number of texts. */
					'label' => sprintf( __( 'Texts (%d)', 'smile-community' ), count( $e['texts'] ) ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);
			$this->add_control(
				'smile_texts_note',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => '<p class="smile-el-note">' . esc_html__( 'Type new wording for this widget only. An empty box keeps the wording from Smile Centre > Site text & photos. Keep any %s or %d markers.', 'smile-community' ) . '</p>',
					'content_classes' => 'elementor-descriptor',
				)
			);
			foreach ( $e['texts'] as $t ) {
				$plain = wp_strip_all_tags( $t['t'] );
				$label = function_exists( 'mb_strimwidth' ) ? mb_strimwidth( $plain, 0, 48, '...' ) : substr( $plain, 0, 48 );
				$this->add_control(
					't_' . $t['k'],
					array(
						'label'       => esc_html( $label ),
						'type'        => strlen( $t['t'] ) > 70 ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT,
						'placeholder' => $plain,
						'default'     => '',
						'label_block' => true,
						'rows'        => 3,
					)
				);
			}
			$this->end_controls_section();
		}

		if ( ! empty( $e['images'] ) ) {
			$this->start_controls_section(
				'smile_photos',
				array(
					/* translators: %d: number of photos. */
					'label' => sprintf( __( 'Photos (%d)', 'smile-community' ), count( $e['images'] ) ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);
			$this->add_control(
				'smile_photos_note',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => '<p class="smile-el-note">' . esc_html__( 'Choose a photo for this widget only. Leave empty to keep the photo from Smile Centre > Site text & photos. Only use photos the centre has consent to publish.', 'smile-community' ) . '</p>',
					'content_classes' => 'elementor-descriptor',
				)
			);
			foreach ( $e['images'] as $key ) {
				$this->add_control(
					'img_' . str_replace( '-', '_', $key ),
					array(
						'label'   => ucfirst( str_replace( '-', ' ', $key ) ),
						'type'    => Controls_Manager::MEDIA,
						'default' => array( 'url' => '', 'id' => '' ),
					)
				);
			}
			$this->end_controls_section();
		}

		$this->smile_style_controls( ! in_array( static::TAG, array( 'smile_icon', 'smile_setting', 'smile_breadcrumbs' ), true ) );
	}

	/**
	 * Output: the section's own markup, with this widget's changes applied.
	 */
	protected function render() {
		$e   = $this->smile_entry();
		$s   = $this->get_settings_for_display();
		$tag = static::TAG;

		$texts = array();
		foreach ( (array) $e['texts'] as $t ) {
			$v = isset( $s[ 't_' . $t['k'] ] ) ? (string) $s[ 't_' . $t['k'] ] : '';
			if ( '' !== trim( $v ) ) {
				$texts[ $t['k'] ] = wp_kses_post( $v );
			}
		}
		$images = array();
		foreach ( (array) $e['images'] as $key ) {
			$m = isset( $s[ 'img_' . str_replace( '-', '_', $key ) ] ) ? $s[ 'img_' . str_replace( '-', '_', $key ) ] : null;
			if ( is_array( $m ) && ! empty( $m['id'] ) && wp_attachment_is_image( (int) $m['id'] ) ) {
				$images[ $key ] = (int) $m['id'];
			}
		}
		$mods = array();
		if ( isset( $s['mod_smile_show_topbar'] ) && '' !== $s['mod_smile_show_topbar'] ) {
			$mods['smile_show_topbar'] = ( 'yes' === $s['mod_smile_show_topbar'] );
		}
		foreach ( array( 'smile_donate_label', 'smile_footer_tagline' ) as $m ) {
			if ( isset( $s[ 'mod_' . $m ] ) && '' !== trim( (string) $s[ 'mod_' . $m ] ) ) {
				$mods[ $m ] = sanitize_textarea_field( (string) $s[ 'mod_' . $m ] );
			}
		}
		if ( isset( $s['mod_smile_donate_url'] ) && '' !== trim( (string) $s['mod_smile_donate_url'] ) ) {
			$mods['smile_donate_url'] = esc_url_raw( (string) $s['mod_smile_donate_url'] );
		}

		$atts = array();
		foreach ( $this->smile_atts() as $a ) {
			$v = isset( $s[ 'att_' . $a['n'] ] ) ? $s[ 'att_' . $a['n'] ] : $a['v'];
			$atts[ $a['n'] ] = is_scalar( $v ) ? sanitize_text_field( (string) $v ) : $a['v'];
		}

		smile_el_overrides( 'push', array( 'texts' => $texts, 'images' => $images, 'mods' => $mods ) );
		$html = '';
		try {
			if ( ! empty( $e['part'] ) ) {
				ob_start();
				get_template_part( $e['part'] );
				$html = (string) ob_get_clean();
			} else {
				global $shortcode_tags;
				if ( isset( $shortcode_tags[ $tag ] ) && is_callable( $shortcode_tags[ $tag ] ) ) {
					$html = (string) call_user_func( $shortcode_tags[ $tag ], $atts, '', $tag );
				}
			}
		} catch ( \Throwable $err ) {
			if ( ob_get_level() > 0 && ! empty( $e['part'] ) ) {
				ob_end_clean();
			}
			$html = '';
		} finally {
			smile_el_overrides( 'pop' );
		}

		if ( '' === trim( $html ) ) {
			if ( $this->smile_is_editor() ) {
				echo '<div class="smile-el-empty" style="padding:16px;border:1px dashed #E4DCCB;border-radius:8px;color:#5D6A64;font-size:14px">';
				if ( empty( $e['part'] ) && ! shortcode_exists( $tag ) ) {
					echo esc_html__( 'This section needs the Smile Community Core plugin. Activate it in Appearance > Smile Setup > Plugins.', 'smile-community' );
				} else {
					/* translators: %s: widget title. */
					echo esc_html( sprintf( __( '%s has nothing to show yet (for example no stories, jobs or details saved). It will appear on the site when there is content.', 'smile-community' ), $this->get_title() ) );
				}
				echo '</div>';
			}
			return;
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the theme's own section markup, escaped where it is built.
	}

	/**
	 * Inside the Elementor editor?
	 *
	 * @return bool
	 */
	protected function smile_is_editor() {
		$plugin = \Elementor\Plugin::$instance;
		return ( isset( $plugin->editor ) && $plugin->editor->is_edit_mode() ) || ( isset( $plugin->preview ) && $plugin->preview->is_preview_mode() );
	}
}

/**
 * Base for the small header/footer blocks.
 */
abstract class Smile_Elementor_Block_Widget extends Widget_Base {

	use Smile_Elementor_Style_Controls;

	/**
	 * Panel category.
	 *
	 * @return array
	 */
	public function get_categories() {
		return array( 'smile' );
	}

	/**
	 * Keywords.
	 *
	 * @return array
	 */
	public function get_keywords() {
		return array( 'smile', 'header', 'footer' );
	}
}

/**
 * Smile: Logo.
 */
final class Smile_EW_Block_Logo extends Smile_Elementor_Block_Widget {

	/** Name. */
	public function get_name() {
		return 'smile-block-logo';
	}

	/** Title. */
	public function get_title() {
		return __( 'Smile: Logo', 'smile-community' );
	}

	/** Icon. */
	public function get_icon() {
		return 'eicon-site-logo';
	}

	/** Controls. */
	protected function register_controls() {
		$this->start_controls_section( 'smile_logo', array( 'label' => __( 'Logo', 'smile-community' ) ) );
		$this->add_control(
			'context',
			array(
				'label'   => __( 'Size', 'smile-community' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'header' => __( 'Header size', 'smile-community' ),
					'footer' => __( 'Footer size', 'smile-community' ),
				),
				'default' => 'header',
			)
		);
		$this->add_responsive_control(
			'width',
			array(
				'label'      => __( 'Width', 'smile-community' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 40, 'max' => 400 ) ),
				'selectors'  => array( '{{WRAPPER}} img' => 'width: {{SIZE}}{{UNIT}}; height: auto;' ),
			)
		);
		$this->end_controls_section();
	}

	/** Output. */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( function_exists( 'smile_brand_logo' ) ) {
			echo smile_brand_logo( 'footer' === $s['context'] ? 'footer' : 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_brand_logo().
		}
	}
}

/**
 * Smile: Menu (a WordPress menu as a simple link list).
 */
final class Smile_EW_Block_Menu extends Smile_Elementor_Block_Widget {

	/** Name. */
	public function get_name() {
		return 'smile-block-menu';
	}

	/** Title. */
	public function get_title() {
		return __( 'Smile: Menu links', 'smile-community' );
	}

	/** Icon. */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/** Controls. */
	protected function register_controls() {
		$menus = array( '' => __( 'Choose a menu', 'smile-community' ) );
		foreach ( wp_get_nav_menus() as $m ) {
			$menus[ (string) $m->term_id ] = $m->name;
		}
		$this->start_controls_section( 'smile_menu', array( 'label' => __( 'Menu', 'smile-community' ) ) );
		$this->add_control(
			'menu',
			array(
				'label'       => __( 'Menu', 'smile-community' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $menus,
				'default'     => '',
				'description' => __( 'Menus are edited in Appearance > Menus. For the main menu with the phone drawer, use the "Smile: Site header" widget.', 'smile-community' ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'smile-community' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'row'    => __( 'In a row', 'smile-community' ),
					'column' => __( 'Stacked', 'smile-community' ),
				),
				'default' => 'row',
			)
		);
		$this->add_control(
			'heading',
			array(
				'label' => __( 'Heading (optional)', 'smile-community' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Space between links', 'smile-community' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array( 'size' => 18, 'unit' => 'px' ),
				'selectors'  => array( '{{WRAPPER}} .smile-el-menu ul' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
		$this->smile_style_controls( false );
	}

	/** Output. */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( empty( $s['menu'] ) ) {
			return;
		}
		$dir = 'column' === $s['layout'] ? 'column' : 'row';
		echo '<nav class="smile-el-menu" aria-label="' . esc_attr( $s['heading'] ? $s['heading'] : __( 'Menu', 'smile-community' ) ) . '">';
		if ( ! empty( $s['heading'] ) ) {
			echo '<h2 class="smile-footer__heading">' . esc_html( $s['heading'] ) . '</h2>';
		}
		wp_nav_menu(
			array(
				'menu'        => absint( $s['menu'] ),
				'container'   => false,
				'depth'       => 1,
				'fallback_cb' => false,
				'items_wrap'  => '<ul style="display:flex;flex-wrap:wrap;flex-direction:' . esc_attr( $dir ) . ';list-style:none;margin:0;padding:0">%3$s</ul>',
			)
		);
		echo '</nav>';
	}
}

/**
 * Smile: Social icons.
 */
final class Smile_EW_Block_Social extends Smile_Elementor_Block_Widget {

	/** Name. */
	public function get_name() {
		return 'smile-block-social';
	}

	/** Title. */
	public function get_title() {
		return __( 'Smile: Social icons', 'smile-community' );
	}

	/** Icon. */
	public function get_icon() {
		return 'eicon-social-icons';
	}

	/** Controls. */
	protected function register_controls() {
		$this->start_controls_section( 'smile_social', array( 'label' => __( 'Social icons', 'smile-community' ) ) );
		$this->add_control(
			'note',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => '<p class="smile-el-note">' . esc_html__( 'Instagram, Facebook and LinkedIn links come from Smile Centre > Centre details.', 'smile-community' ) . '</p>',
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon colour', 'smile-community' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} a' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/** Output. */
	protected function render() {
		if ( function_exists( 'smile_social_icons' ) ) {
			echo smile_social_icons( 'smile-footer__social' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in smile_social_icons().
		}
	}
}

/**
 * Smile: Donate button.
 */
final class Smile_EW_Block_Donate extends Smile_Elementor_Block_Widget {

	/** Name. */
	public function get_name() {
		return 'smile-block-donate';
	}

	/** Title. */
	public function get_title() {
		return __( 'Smile: Donate button', 'smile-community' );
	}

	/** Icon. */
	public function get_icon() {
		return 'eicon-button';
	}

	/** Controls. */
	protected function register_controls() {
		$this->start_controls_section( 'smile_donate', array( 'label' => __( 'Button', 'smile-community' ) ) );
		$this->add_control(
			'label',
			array(
				'label'       => __( 'Text', 'smile-community' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Site setting', 'smile-community' ),
			)
		);
		$this->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'smile-community' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => __( 'Site setting (the Donate page)', 'smile-community' ),
			)
		);
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'smile-community' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'sun'  => __( 'Sunshine', 'smile-community' ),
					'teal' => __( 'Teal', 'smile-community' ),
				),
				'default' => 'sun',
			)
		);
		$this->end_controls_section();
		$this->smile_style_controls( true );
	}

	/** Output. */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$label = ! empty( $s['label'] ) ? $s['label'] : get_theme_mod( 'smile_donate_label', __( 'Donate', 'smile-community' ) );
		$url   = ! empty( $s['link']['url'] ) ? $s['link']['url'] : ( function_exists( 'smile_donate_url' ) ? smile_donate_url() : home_url( '/donate/' ) );
		$new   = ! empty( $s['link']['is_external'] );
		printf(
			'<a class="smile-btn smile-btn--%1$s" href="%2$s"%3$s>%4$s</a>',
			esc_attr( 'teal' === $s['style'] ? 'teal' : 'sun' ),
			esc_url( $url ),
			$new ? ' target="_blank" rel="noopener noreferrer"' : '',
			esc_html( $label )
		);
	}
}
