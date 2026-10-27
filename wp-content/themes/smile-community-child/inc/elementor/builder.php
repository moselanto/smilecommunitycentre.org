<?php
/**
 * Elementor header and footer builder (free Elementor, no Pro needed).
 *
 * The same approach as Ryse: a header or footer is an ordinary Elementor
 * template (Templates > Saved Templates, type "Section"). Smile Theme Options
 * chooses the site-wide header and footer; any page can choose its own (or
 * hide them) in Elementor > Page settings > Smile header & footer, or in the
 * "Smile header & footer" box in the WordPress editor.
 *
 * Safe fallback: when nothing is chosen, Elementor is off, the chosen template
 * is missing, unpublished or empty, the theme's built-in header and footer are
 * shown exactly as before. Elementor Pro Theme Builder headers still win.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is Elementor loaded and able to render templates?
 *
 * @return bool
 */
function smile_builder_ready() {
	return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend );
}

/**
 * Published Elementor templates that can be used as a header or footer.
 *
 * @return array id => title
 */
function smile_builder_template_choices() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$cache = array();
	$posts = get_posts(
		array(
			'post_type'        => 'elementor_library',
			'post_status'      => 'publish',
			'posts_per_page'   => 200,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'no_found_rows'    => true,
			'suppress_filters' => true,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin screens only, small table.
			'meta_query'       => array(
				array(
					'key'     => '_elementor_template_type',
					'value'   => array( 'section', 'container', 'page', 'header', 'footer' ),
					'compare' => 'IN',
				),
			),
		)
	);
	foreach ( $posts as $p ) {
		$cache[ (string) $p->ID ] = $p->post_title ? $p->post_title : sprintf( '#%d', $p->ID );
	}
	return $cache;
}

/**
 * Per-page choices: '' = site setting, 'theme' = built-in, 'none' = hide, or a template ID.
 *
 * @return array
 */
function smile_builder_page_choices() {
	return array(
		''      => __( 'Use the site setting', 'smile-community' ),
		'theme' => __( 'Theme header / footer (built in)', 'smile-community' ),
		'none'  => __( 'Hide on this page', 'smile-community' ),
	) + smile_builder_template_choices();
}

/**
 * A page's own choice for the header or footer.
 *
 * @param int    $post_id Post ID.
 * @param string $where   header|footer.
 * @return string
 */
function smile_builder_page_choice( $post_id, $where ) {
	$settings = get_post_meta( $post_id, '_elementor_page_settings', true );
	$key      = 'smile_' . $where;
	if ( is_array( $settings ) && isset( $settings[ $key ] ) && '' !== (string) $settings[ $key ] ) {
		return (string) $settings[ $key ];
	}
	return (string) get_post_meta( $post_id, '_' . $key, true );
}

/**
 * Template to show: >0 template ID, 0 theme default, -1 hidden.
 *
 * @param string $where header|footer.
 * @return int
 */
function smile_builder_resolve( $where ) {
	$id = absint( function_exists( 'smile_opt' ) ? smile_opt( $where . '_template', 0 ) : 0 );
	if ( is_singular() ) {
		$choice = smile_builder_page_choice( get_queried_object_id(), $where );
		if ( 'none' === $choice ) {
			$id = -1;
		} elseif ( 'theme' === $choice ) {
			$id = 0;
		} elseif ( absint( $choice ) ) {
			$id = absint( $choice );
		}
	}
	/**
	 * Filter the header/footer template. Return 0 for the built-in design, -1 to hide.
	 *
	 * @param int    $id    Template ID.
	 * @param string $where header|footer.
	 */
	$id = (int) apply_filters( 'smile_builder_template', $id, $where );
	if ( $id > 0 ) {
		$post = get_post( $id );
		if ( ! $post || 'elementor_library' !== $post->post_type || 'publish' !== $post->post_status ) {
			$id = 0;
		}
	}
	return $id;
}

/**
 * Are we inside Elementor's own editing or template preview, where the site
 * header and footer must stay the simple built-in ones?
 *
 * @return bool
 */
function smile_builder_is_template_screen() {
	return is_singular( 'elementor_library' );
}

/**
 * Will an Elementor header or footer be shown on this request?
 *
 * @return bool
 */
function smile_builder_active() {
	if ( ! smile_builder_ready() || smile_builder_is_template_screen() ) {
		return false;
	}
	return smile_builder_resolve( 'header' ) > 0 || smile_builder_resolve( 'footer' ) > 0;
}

/**
 * Render the header or footer.
 *
 * @param string $where header|footer.
 * @return bool True when handled (rendered or hidden); false to use the built-in design.
 */
function smile_builder_render( $where ) {
	if ( ! smile_builder_ready() || smile_builder_is_template_screen() ) {
		return false;
	}
	$id = smile_builder_resolve( $where );
	if ( -1 === $id ) {
		if ( 'header' === $where ) {
			echo '<span id="site-header" class="smile-el-hidden" hidden></span>';
		}
		return true;
	}
	if ( $id < 1 ) {
		return false;
	}
	static $depth = 0;
	if ( $depth > 0 ) {
		return false; // A header template must never include itself.
	}
	++$depth;
	try {
		$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, false );
	} catch ( \Throwable $e ) {
		$html = '';
	}
	--$depth;
	if ( ! is_string( $html ) || '' === trim( $html ) ) {
		return false;
	}
	if ( 'header' === $where ) {
		if ( false === strpos( $html, 'smile-skip-link' ) ) {
			echo '<a class="smile-skip-link" href="#content">' . esc_html__( 'Skip to content', 'smile-community' ) . '</a>';
		}
		echo '<div class="smile-el-header" id="smile-el-header" data-template="' . esc_attr( $id ) . '">';
	} else {
		echo '<div class="smile-el-footer" id="smile-el-footer" data-template="' . esc_attr( $id ) . '">';
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor's rendered template.
	echo '</div>';
	return true;
}

/**
 * Load Elementor's styles and the templates' CSS in the <head>, so a header
 * template never flashes unstyled, even on pages not built with Elementor.
 */
function smile_builder_enqueue() {
	if ( ! smile_builder_ready() || smile_builder_is_template_screen() ) {
		return;
	}
	$ids = array();
	foreach ( array( 'header', 'footer' ) as $where ) {
		$id = smile_builder_resolve( $where );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	if ( ! $ids ) {
		return;
	}
	$frontend = \Elementor\Plugin::$instance->frontend;
	if ( method_exists( $frontend, 'enqueue_styles' ) ) {
		$frontend->enqueue_styles();
	}
	if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
		foreach ( $ids as $id ) {
			try {
				\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
			} catch ( \Throwable $e ) {
				continue; // Elementor will print the CSS with the content instead.
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'smile_builder_enqueue', 20 );

/**
 * Body classes so styles can tell which header/footer is in use.
 *
 * @param array $classes Classes.
 * @return array
 */
function smile_builder_body_class( $classes ) {
	if ( smile_builder_ready() && ! smile_builder_is_template_screen() ) {
		foreach ( array( 'header', 'footer' ) as $where ) {
			$id = smile_builder_resolve( $where );
			if ( $id > 0 ) {
				$classes[] = 'smile-el-' . $where . '-on';
			} elseif ( -1 === $id ) {
				$classes[] = 'smile-no-' . $where;
			}
		}
	}
	return $classes;
}
add_filter( 'body_class', 'smile_builder_body_class' );

/* -------------------------------------------------------------------------
 * Per-page choice inside Elementor (Page settings panel)
 * ---------------------------------------------------------------------- */

/**
 * Add "Smile header & footer" to Elementor's Page settings.
 *
 * @param object $document Elementor document.
 */
function smile_builder_document_controls( $document ) {
	if ( ! class_exists( '\Elementor\Controls_Manager' ) || ! is_object( $document ) || ! method_exists( $document, 'start_controls_section' ) ) {
		return;
	}
	$post = method_exists( $document, 'get_main_post' ) ? $document->get_main_post() : null;
	if ( ! $post || 'elementor_library' === $post->post_type ) {
		return;
	}
	$document->start_controls_section(
		'smile_layout_section',
		array(
			'label' => __( 'Smile header & footer', 'smile-community' ),
			'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
		)
	);
	$choices = smile_builder_page_choices();
	foreach ( array( 'header' => __( 'Header', 'smile-community' ), 'footer' => __( 'Footer', 'smile-community' ) ) as $where => $label ) {
		$document->add_control(
			'smile_' . $where,
			array(
				'label'   => $label,
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $choices,
				'default' => '',
			)
		);
	}
	$document->add_control(
		'smile_layout_note',
		array(
			'type'            => \Elementor\Controls_Manager::RAW_HTML,
			'raw'             => esc_html__( 'Save, then reload the preview to see a different header or footer. Templates are made in Templates > Saved Templates (type Section).', 'smile-community' ),
			'content_classes' => 'elementor-descriptor',
		)
	);
	$document->end_controls_section();
}
add_action( 'elementor/documents/register_controls', 'smile_builder_document_controls' );

/* -------------------------------------------------------------------------
 * Per-page choice in the WordPress editor (for pages not built in Elementor)
 * ---------------------------------------------------------------------- */

/**
 * Post types that show the box.
 *
 * @return array
 */
function smile_builder_post_types() {
	$types = get_post_types( array( 'public' => true ), 'names' );
	unset( $types['attachment'], $types['elementor_library'] );
	return array_values( $types );
}

/**
 * Register the meta box.
 */
function smile_builder_meta_box() {
	add_meta_box( 'smile-layout', __( 'Smile header & footer', 'smile-community' ), 'smile_builder_meta_box_html', smile_builder_post_types(), 'side', 'low' );
}
add_action( 'add_meta_boxes', 'smile_builder_meta_box' );

/**
 * Meta box contents.
 *
 * @param WP_Post $post Post.
 */
function smile_builder_meta_box_html( $post ) {
	wp_nonce_field( 'smile_layout_save', 'smile_layout_nonce' );
	$choices = smile_builder_page_choices();
	foreach ( array( 'header' => __( 'Header', 'smile-community' ), 'footer' => __( 'Footer', 'smile-community' ) ) as $where => $label ) {
		$current = smile_builder_page_choice( $post->ID, $where );
		echo '<p><label for="smile-layout-' . esc_attr( $where ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		echo '<select style="width:100%" id="smile-layout-' . esc_attr( $where ) . '" name="smile_layout[' . esc_attr( $where ) . ']">';
		foreach ( $choices as $val => $name ) {
			echo '<option value="' . esc_attr( $val ) . '"' . selected( $current, (string) $val, false ) . '>' . esc_html( $name ) . '</option>';
		}
		echo '</select></p>';
	}
}

/**
 * Save the meta box into the same place Elementor's Page settings use, so the
 * two screens never disagree.
 *
 * @param int $post_id Post ID.
 */
function smile_builder_meta_box_save( $post_id ) {
	if ( ! isset( $_POST['smile_layout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smile_layout_nonce'] ) ), 'smile_layout_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in       = isset( $_POST['smile_layout'] ) && is_array( $_POST['smile_layout'] ) ? wp_unslash( $_POST['smile_layout'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated against the allowed list below.
	$allowed  = array_map( 'strval', array_keys( smile_builder_page_choices() ) );
	$settings = get_post_meta( $post_id, '_elementor_page_settings', true );
	$settings = is_array( $settings ) ? $settings : array();
	foreach ( array( 'header', 'footer' ) as $where ) {
		$v = isset( $in[ $where ] ) ? sanitize_key( (string) $in[ $where ] ) : '';
		$v = in_array( $v, $allowed, true ) ? $v : '';
		update_post_meta( $post_id, '_smile_' . $where, $v );
		if ( '' === $v ) {
			unset( $settings[ 'smile_' . $where ] );
		} else {
			$settings[ 'smile_' . $where ] = $v;
		}
	}
	if ( $settings ) {
		update_post_meta( $post_id, '_elementor_page_settings', $settings );
	} else {
		delete_post_meta( $post_id, '_elementor_page_settings' );
	}
}
add_action( 'save_post', 'smile_builder_meta_box_save' );

/* -------------------------------------------------------------------------
 * Starter templates: the current header and footer as Elementor templates
 * ---------------------------------------------------------------------- */

/**
 * Elementor data for a starter template (one section holding one widget).
 *
 * @param string $widget Widget name.
 * @param string $seed   Unique seed for element IDs.
 * @return array
 */
function smile_builder_starter_data( $widget, $seed ) {
	$id = static function ( $s ) use ( $seed ) {
		return substr( md5( $seed . $s ), 0, 7 );
	};
	return array(
		array(
			'id'       => $id( 'section' ),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => array(
				'layout'  => 'full_width',
				'gap'     => 'no',
				'padding' => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
			),
			'elements' => array(
				array(
					'id'       => $id( 'column' ),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => array(
						'_column_size' => 100,
						'padding'      => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
					),
					'elements' => array(
						array(
							'id'         => $id( 'widget' ),
							'elType'     => 'widget',
							'widgetType' => $widget,
							'isInner'    => false,
							'settings'   => array(),
							'elements'   => array(),
						),
					),
				),
			),
		),
	);
}

/**
 * Starter templates shipped with the theme.
 *
 * @return array slug => array( title, widget )
 */
function smile_builder_starters() {
	return array(
		'smile-starter-header' => array( __( 'Smile header (starter)', 'smile-community' ), 'smile-site-header' ),
		'smile-starter-footer' => array( __( 'Smile footer (starter)', 'smile-community' ), 'smile-site-footer' ),
	);
}

/**
 * Create the starter templates (skips any that already exist).
 *
 * @return array Created or existing template IDs by slug.
 */
function smile_builder_create_starters() {
	$ids = array();
	foreach ( smile_builder_starters() as $slug => $s ) {
		$existing = get_page_by_path( $slug, OBJECT, 'elementor_library' );
		if ( $existing ) {
			$ids[ $slug ] = (int) $existing->ID;
			continue;
		}
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'elementor_library',
				'post_status' => 'publish',
				'post_title'  => $s[0],
				'post_name'   => $slug,
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'section' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( smile_builder_starter_data( $s[1], $slug ) ) ) );
		if ( taxonomy_exists( 'elementor_library_type' ) ) {
			wp_set_object_terms( $post_id, 'section', 'elementor_library_type' );
		}
		$ids[ $slug ] = (int) $post_id;
	}
	return $ids;
}

/**
 * admin-post handler for the "Create starter header and footer templates" link.
 */
function smile_builder_starters_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'smile-community' ), 403 );
	}
	check_admin_referer( 'smile_builder_starters' );
	if ( ! post_type_exists( 'elementor_library' ) ) {
		wp_die( esc_html__( 'Activate Elementor first, then try again.', 'smile-community' ) );
	}
	smile_builder_create_starters();
	wp_safe_redirect( admin_url( 'edit.php?post_type=elementor_library&tabs_group=library&smile_starters=1' ) );
	exit;
}
add_action( 'admin_post_smile_builder_starters', 'smile_builder_starters_action' );

/**
 * Confirmation after the starters are created.
 */
function smile_builder_starters_notice() {
	if ( empty( $_GET['smile_starters'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		return;
	}
	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Starter header and footer templates are ready. Edit them with Elementor, then choose them in Appearance > Smile Theme Options > Header & Footer.', 'smile-community' ) . '</p></div>';
}
add_action( 'admin_notices', 'smile_builder_starters_notice' );
