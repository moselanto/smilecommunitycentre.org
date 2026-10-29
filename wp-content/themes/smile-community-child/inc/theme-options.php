<?php
/**
 * Smile Theme Options (Redux Framework), the same pattern as ThemeForest
 * themes such as Ryse: one options screen for the whole theme.
 *
 *  - With the free Redux Framework plugin active (Smile Setup installs it in one
 *    click) the options appear under Appearance > Smile Theme Options with
 *    Redux's full interface.
 *  - Without Redux a simple built-in screen at the same place keeps the most
 *    important settings (header and footer templates, colours, custom CSS)
 *    working, so the site never depends on Redux to display correctly.
 *
 * Every value lives in one option, `smile_options`. Settings that also exist
 * in Appearance > Customize (top bar, Donate button, WhatsApp chat, footer line)
 * are kept in step both ways, so staff can use either screen.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

define( 'SMILE_OPT', 'smile_options' );

/**
 * Brand colours that can be changed, mapped to the theme's CSS variables.
 *
 * @return array id => array( css variable, default, label )
 */
function smile_opt_colours() {
	return array(
		'color_teal'   => array( '--smile-teal', '#146C3C', __( 'Main colour (teal)', 'smile-community' ) ),
		'color_forest' => array( '--smile-forest', '#0B4021', __( 'Dark colour (forest)', 'smile-community' ) ),
		'color_sun'    => array( '--smile-sun', '#FDDC05', __( 'Accent colour (sunshine)', 'smile-community' ) ),
		'color_cream'  => array( '--smile-cream', '#FFF9EF', __( 'Soft background (cream)', 'smile-community' ) ),
		'color_ink'    => array( '--smile-ink', '#24312D', __( 'Text colour', 'smile-community' ) ),
	);
}

/**
 * Customizer settings mirrored in the options screen.
 *
 * @return array option id => theme mod name
 */
function smile_opt_mod_map() {
	return array(
		'show_topbar'    => 'smile_show_topbar',
		'donate_label'   => 'smile_donate_label',
		'donate_url'     => 'smile_donate_url',
		'wa_chat'        => 'smile_wa_chat',
		'footer_tagline' => 'smile_footer_tagline',
	);
}

/**
 * All saved options.
 *
 * @return array
 */
function smile_options() {
	$o = get_option( SMILE_OPT, array() );
	return is_array( $o ) ? $o : array();
}

/**
 * One option with a default.
 *
 * @param string $key     Key.
 * @param mixed  $default Default.
 * @return mixed
 */
function smile_opt( $key, $default = '' ) {
	$o = smile_options();
	return ( isset( $o[ $key ] ) && '' !== $o[ $key ] && null !== $o[ $key ] ) ? $o[ $key ] : $default;
}

/**
 * Valid hex colour or ''.
 *
 * @param mixed $c Value (Redux colour fields may also give an array).
 * @return string
 */
function smile_opt_hex( $c ) {
	if ( is_array( $c ) ) {
		$c = isset( $c['color'] ) ? $c['color'] : ( isset( $c['rgba'] ) ? $c['rgba'] : '' );
	}
	$c = sanitize_hex_color( is_string( $c ) ? trim( $c ) : '' );
	return $c ? $c : '';
}

/* -------------------------------------------------------------------------
 * Front end: colours, width and custom CSS
 * ---------------------------------------------------------------------- */

/**
 * CSS built from the options (only what differs from the theme defaults).
 *
 * @return string
 */
function smile_opt_css() {
	$vars = array();
	foreach ( smile_opt_colours() as $id => $c ) {
		$v = smile_opt_hex( smile_opt( $id ) );
		if ( $v && strtolower( $v ) !== strtolower( $c[1] ) ) {
			$vars[] = $c[0] . ':' . $v;
		}
	}
	$width = absint( smile_opt( 'container_width', 0 ) );
	if ( $width >= 960 && $width <= 1920 ) {
		$vars[] = '--smile-container:' . $width . 'px';
	}
	$css = $vars ? ':root{' . implode( ';', $vars ) . '}' : '';
	$custom = (string) smile_opt( 'custom_css', '' );
	if ( '' !== trim( $custom ) ) {
		$css .= "\n" . wp_strip_all_tags( $custom );
	}
	return $css;
}

/**
 * Print the option CSS after the theme stylesheet.
 */
function smile_opt_enqueue_css() {
	$css = smile_opt_css();
	if ( '' === $css ) {
		return;
	}
	if ( wp_style_is( 'smile-community', 'enqueued' ) ) {
		wp_add_inline_style( 'smile-community', $css );
	} else {
		wp_register_style( 'smile-options', false, array(), SMILE_THEME_VERSION );
		wp_enqueue_style( 'smile-options' );
		wp_add_inline_style( 'smile-options', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'smile_opt_enqueue_css', 30 );

/* -------------------------------------------------------------------------
 * Keep the Customizer and the options screen in step
 * ---------------------------------------------------------------------- */

/**
 * After the options are saved (Redux or the built-in screen), copy the shared
 * settings into the Customizer's theme mods, which the templates read.
 *
 * @param array $o Options.
 */
function smile_opt_sync_to_mods( $o = null ) {
	$o = is_array( $o ) ? $o : smile_options();
	foreach ( smile_opt_mod_map() as $id => $mod ) {
		if ( ! array_key_exists( $id, $o ) ) {
			continue;
		}
		$v = $o[ $id ];
		if ( in_array( $id, array( 'show_topbar', 'wa_chat' ), true ) ) {
			$v = (bool) $v;
		} elseif ( 'donate_url' === $id ) {
			$v = esc_url_raw( (string) $v );
		} elseif ( 'footer_tagline' === $id ) {
			$v = sanitize_textarea_field( (string) $v );
		} else {
			$v = sanitize_text_field( (string) $v );
		}
		if ( '' === $v && 'donate_url' !== $id ) {
			remove_theme_mod( $mod );
		} else {
			set_theme_mod( $mod, $v );
		}
	}
}
add_action( 'redux/options/' . SMILE_OPT . '/saved', 'smile_opt_sync_to_mods' );

/**
 * After the Customizer saves, copy the shared settings into the options.
 */
function smile_opt_sync_from_mods() {
	$o = smile_options();
	foreach ( smile_opt_mod_map() as $id => $mod ) {
		$v = get_theme_mod( $mod, null );
		if ( null !== $v ) {
			$o[ $id ] = is_bool( $v ) ? ( $v ? 1 : 0 ) : $v;
		}
	}
	update_option( SMILE_OPT, $o );
}
add_action( 'customize_save_after', 'smile_opt_sync_from_mods' );

/**
 * Current value of a shared setting for the options screen's default.
 *
 * @param string $id      Option id.
 * @param mixed  $default Default.
 * @return mixed
 */
function smile_opt_mod_default( $id, $default ) {
	$map = smile_opt_mod_map();
	$v   = isset( $map[ $id ] ) ? get_theme_mod( $map[ $id ], null ) : null;
	if ( null === $v ) {
		return $default;
	}
	return is_bool( $v ) ? ( $v ? 1 : 0 ) : $v;
}

/* -------------------------------------------------------------------------
 * Redux configuration
 * ---------------------------------------------------------------------- */

/**
 * Register the Redux options panel (only when Redux is active).
 */
function smile_opt_redux_config() {
	if ( ! class_exists( 'Redux' ) ) {
		return;
	}
	$set_args    = method_exists( 'Redux', 'set_args' ) ? 'set_args' : 'setArgs';
	$set_section = method_exists( 'Redux', 'set_section' ) ? 'set_section' : 'setSection';
	if ( ! method_exists( 'Redux', $set_args ) || ! method_exists( 'Redux', $set_section ) ) {
		return;
	}

	$theme = wp_get_theme();
	call_user_func(
		array( 'Redux', $set_args ),
		SMILE_OPT,
		array(
			'opt_name'            => SMILE_OPT,
			'display_name'        => __( 'Smile Theme Options', 'smile-community' ),
			'display_version'     => $theme->get( 'Version' ),
			'menu_type'           => 'submenu',
			'page_parent'         => 'themes.php',
			'menu_title'          => __( 'Smile Theme Options', 'smile-community' ),
			'page_title'          => __( 'Smile Theme Options', 'smile-community' ),
			'page_slug'           => 'smile-theme-options',
			'page_permissions'    => 'manage_options',
			'admin_bar'           => true,
			'admin_bar_icon'      => 'dashicons-admin-appearance',
			'admin_bar_priority'  => 60,
			'dev_mode'            => false,
			'disable_tracking'    => true,
			'update_notice'       => false,
			'customizer'          => false,
			'save_defaults'       => true,
			'default_show'        => false,
			'show_import_export'  => true,
			'show_options_object' => false,
			'use_cdn'             => false,
			'ajax_save'           => true,
			'async_typography'    => false,
			'google_api_key'      => '',
			'output'              => false,
			'output_tag'          => false,
			'footer_credit'       => ' ',
			'database'            => '',
			'transient_time'      => HOUR_IN_SECONDS,
		)
	);

	$templates = function_exists( 'smile_builder_template_choices' ) ? smile_builder_template_choices() : array();
	$global    = array( '0' => __( 'Theme header / footer (built in)', 'smile-community' ) ) + $templates;

	$starter_url = wp_nonce_url( admin_url( 'admin-post.php?action=smile_builder_starters' ), 'smile_builder_starters' );

	call_user_func(
		array( 'Redux', $set_section ),
		SMILE_OPT,
		array(
			'title'  => __( 'Header & Footer', 'smile-community' ),
			'id'     => 'smile-header-footer',
			'icon'   => 'el el-website',
			'fields' => array(
				array(
					'id'      => 'builder_info',
					'type'    => 'info',
					'style'   => 'info',
					'title'   => __( 'Design the header and footer in Elementor', 'smile-community' ),
					'desc'    => sprintf(
						/* translators: 1: link to Elementor templates, 2: link that creates the starter templates. */
						__( 'Build a header or footer in <a href="%1$s">Templates > Saved Templates</a> (type "Section"), then choose it below. Every page can override this choice under Elementor > Page settings > Smile header & footer. <a href="%2$s">Create starter header and footer templates</a> (copies of the current design, ready to edit).', 'smile-community' ),
						esc_url( admin_url( 'edit.php?post_type=elementor_library&tabs_group=library' ) ),
						esc_url( $starter_url )
					),
				),
				array(
					'id'      => 'header_template',
					'type'    => 'select',
					'title'   => __( 'Site header', 'smile-community' ),
					'subtitle'=> __( 'Shown on every page unless a page chooses its own.', 'smile-community' ),
					'options' => $global,
					'default' => '0',
				),
				array(
					'id'      => 'footer_template',
					'type'    => 'select',
					'title'   => __( 'Site footer', 'smile-community' ),
					'subtitle'=> __( 'Shown on every page unless a page chooses its own.', 'smile-community' ),
					'options' => $global,
					'default' => '0',
				),
			),
		)
	);

	call_user_func(
		array( 'Redux', $set_section ),
		SMILE_OPT,
		array(
			'title'  => __( 'Top bar & buttons', 'smile-community' ),
			'id'     => 'smile-topbar',
			'icon'   => 'el el-heart',
			'desc'   => __( 'The same settings as Appearance > Customize > Smile: Header & Footer. Changes in either place show in both.', 'smile-community' ),
			'fields' => array(
				array(
					'id'      => 'show_topbar',
					'type'    => 'switch',
					'title'   => __( 'Slim contact bar above the header', 'smile-community' ),
					'default' => smile_opt_mod_default( 'show_topbar', 1 ),
				),
				array(
					'id'      => 'donate_label',
					'type'    => 'text',
					'title'   => __( 'Donate button text', 'smile-community' ),
					'default' => smile_opt_mod_default( 'donate_label', __( 'Donate', 'smile-community' ) ),
				),
				array(
					'id'       => 'donate_url',
					'type'     => 'text',
					'title'    => __( 'Donate button link', 'smile-community' ),
					'subtitle' => __( 'Leave empty to use the Donate page.', 'smile-community' ),
					'validate' => 'url',
					'default'  => smile_opt_mod_default( 'donate_url', '' ),
				),
				array(
					'id'      => 'wa_chat',
					'type'    => 'switch',
					'title'   => __( 'WhatsApp chat button on every page', 'smile-community' ),
					'default' => smile_opt_mod_default( 'wa_chat', 1 ),
				),
				array(
					'id'      => 'footer_tagline',
					'type'    => 'textarea',
					'title'   => __( 'Footer mission line', 'smile-community' ),
					'default' => smile_opt_mod_default( 'footer_tagline', __( 'A safe home, daily meals, education and care for orphaned and vulnerable children in Soweto, Kayole, Nairobi.', 'smile-community' ) ),
				),
			),
		)
	);

	$colour_fields = array();
	foreach ( smile_opt_colours() as $id => $c ) {
		$colour_fields[] = array(
			'id'          => $id,
			'type'        => 'color',
			'title'       => $c[2],
			'default'     => $c[1],
			'transparent' => false,
			'validate'    => 'color',
		);
	}
	$colour_fields[] = array(
		'id'      => 'container_width',
		'type'    => 'slider',
		'title'   => __( 'Content width (px)', 'smile-community' ),
		'default' => 1200,
		'min'     => 960,
		'max'     => 1600,
		'step'    => 10,
	);
	call_user_func(
		array( 'Redux', $set_section ),
		SMILE_OPT,
		array(
			'title'  => __( 'Colours & layout', 'smile-community' ),
			'id'     => 'smile-colours',
			'icon'   => 'el el-brush',
			'desc'   => __( 'Brand colours used across the site, the header, footer and every Smile widget.', 'smile-community' ),
			'fields' => $colour_fields,
		)
	);

	call_user_func(
		array( 'Redux', $set_section ),
		SMILE_OPT,
		array(
			'title'  => __( 'Custom CSS', 'smile-community' ),
			'id'     => 'smile-custom-css',
			'icon'   => 'el el-css',
			'fields' => array(
				array(
					'id'       => 'custom_css',
					'type'     => 'ace_editor',
					'mode'     => 'css',
					'theme'    => 'chrome',
					'title'    => __( 'Extra CSS', 'smile-community' ),
					'subtitle' => __( 'Added after the theme styles on every page.', 'smile-community' ),
					'default'  => '',
				),
			),
		)
	);
}
add_action( 'after_setup_theme', 'smile_opt_redux_config', 5 );

/**
 * Keep Redux's promotional notices and tracking out of the dashboard.
 */
add_filter( 'redux/' . SMILE_OPT . '/aDBW_filter', '__return_false' );

/* -------------------------------------------------------------------------
 * Built-in screen when Redux is not installed
 * ---------------------------------------------------------------------- */

/**
 * Add the fallback screen (Appearance > Smile Theme Options).
 */
function smile_opt_fallback_menu() {
	if ( class_exists( 'Redux' ) || class_exists( 'ReduxFramework' ) ) {
		return;
	}
	add_theme_page(
		__( 'Smile Theme Options', 'smile-community' ),
		__( 'Smile Theme Options', 'smile-community' ),
		'manage_options',
		'smile-theme-options',
		'smile_opt_fallback_page'
	);
}
add_action( 'admin_menu', 'smile_opt_fallback_menu' );

/**
 * Render the fallback screen.
 */
function smile_opt_fallback_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$choices = array( '0' => __( 'Theme header / footer (built in)', 'smile-community' ) );
	if ( function_exists( 'smile_builder_template_choices' ) ) {
		$choices += smile_builder_template_choices();
	}
	$o = smile_options();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Smile Theme Options', 'smile-community' ); ?></h1>
		<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only. ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Options saved.', 'smile-community' ); ?></p></div>
		<?php endif; ?>
		<div class="notice notice-info inline"><p>
			<?php esc_html_e( 'Install the free Redux Framework plugin (Appearance > Smile Setup > Plugins) for the full options panel. These basic settings work without it.', 'smile-community' ); ?>
		</p></div>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="smile_save_options">
			<?php wp_nonce_field( 'smile_save_options' ); ?>
			<h2><?php esc_html_e( 'Header & Footer', 'smile-community' ); ?></h2>
			<p>
				<?php esc_html_e( 'Choose an Elementor template (type "Section") for the site header and footer. Each page can override this in Elementor > Page settings.', 'smile-community' ); ?>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=smile_builder_starters' ), 'smile_builder_starters' ) ); ?>"><?php esc_html_e( 'Create starter header and footer templates', 'smile-community' ); ?></a>
			</p>
			<table class="form-table" role="presentation">
				<?php foreach ( array( 'header_template' => __( 'Site header', 'smile-community' ), 'footer_template' => __( 'Site footer', 'smile-community' ) ) as $id => $label ) : ?>
				<tr>
					<th scope="row"><label for="smile-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td><select id="smile-<?php echo esc_attr( $id ); ?>" name="smile[<?php echo esc_attr( $id ); ?>]">
						<?php foreach ( $choices as $val => $name ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( (string) ( isset( $o[ $id ] ) ? $o[ $id ] : '0' ), (string) $val ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<h2><?php esc_html_e( 'Colours & layout', 'smile-community' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php foreach ( smile_opt_colours() as $id => $c ) : ?>
				<tr>
					<th scope="row"><label for="smile-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $c[2] ); ?></label></th>
					<td><input type="color" id="smile-<?php echo esc_attr( $id ); ?>" name="smile[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( smile_opt_hex( smile_opt( $id, $c[1] ) ) ? smile_opt_hex( smile_opt( $id, $c[1] ) ) : $c[1] ); ?>"></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><label for="smile-container_width"><?php esc_html_e( 'Content width (px)', 'smile-community' ); ?></label></th>
					<td><input type="number" min="960" max="1600" step="10" id="smile-container_width" name="smile[container_width]" value="<?php echo esc_attr( absint( smile_opt( 'container_width', 1200 ) ) ); ?>"></td>
				</tr>
			</table>
			<h2><?php esc_html_e( 'Custom CSS', 'smile-community' ); ?></h2>
			<textarea name="smile[custom_css]" rows="10" class="large-text code"><?php echo esc_textarea( (string) smile_opt( 'custom_css', '' ) ); ?></textarea>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Save the fallback screen.
 */
function smile_opt_fallback_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to do that.', 'smile-community' ), 403 );
	}
	check_admin_referer( 'smile_save_options' );
	$in = isset( $_POST['smile'] ) && is_array( $_POST['smile'] ) ? wp_unslash( $_POST['smile'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.
	$o  = smile_options();
	foreach ( array( 'header_template', 'footer_template' ) as $id ) {
		if ( isset( $in[ $id ] ) ) {
			$o[ $id ] = (string) absint( $in[ $id ] );
		}
	}
	foreach ( smile_opt_colours() as $id => $c ) {
		if ( isset( $in[ $id ] ) ) {
			$o[ $id ] = smile_opt_hex( $in[ $id ] );
		}
	}
	if ( isset( $in['container_width'] ) ) {
		$o['container_width'] = min( 1600, max( 960, absint( $in['container_width'] ) ) );
	}
	if ( isset( $in['custom_css'] ) ) {
		$o['custom_css'] = wp_strip_all_tags( (string) $in['custom_css'] );
	}
	update_option( SMILE_OPT, $o );
	wp_safe_redirect( admin_url( 'themes.php?page=smile-theme-options&saved=1' ) );
	exit;
}
add_action( 'admin_post_smile_save_options', 'smile_opt_fallback_save' );
