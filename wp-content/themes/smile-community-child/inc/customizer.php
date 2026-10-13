<?php
/**
 * Customizer options (header donate button, top bar).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Customizer settings.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function smile_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'smile_header',
		array(
			'title'    => __( 'Smile: Header & Footer', 'smile-community' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting( 'smile_donate_label', array( 'default' => __( 'Donate', 'smile-community' ), 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'smile_donate_label', array( 'label' => __( 'Donate button text', 'smile-community' ), 'section' => 'smile_header', 'type' => 'text' ) );

	$wp_customize->add_setting( 'smile_donate_url', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control( 'smile_donate_url', array( 'label' => __( 'Donate button link (leave empty to use the Donate page)', 'smile-community' ), 'section' => 'smile_header', 'type' => 'url' ) );

	$wp_customize->add_setting( 'smile_show_topbar', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'smile_show_topbar', array( 'label' => __( 'Show the slim contact bar above the header', 'smile-community' ), 'section' => 'smile_header', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'smile_footer_tagline', array( 'default' => __( 'A safe home, daily meals, education and care for orphaned and vulnerable children in Soweto, Kayole, Nairobi.', 'smile-community' ), 'sanitize_callback' => 'sanitize_textarea_field' ) );
	$wp_customize->add_control( 'smile_footer_tagline', array( 'label' => __( 'Footer mission line', 'smile-community' ), 'section' => 'smile_header', 'type' => 'textarea' ) );
}
add_action( 'customize_register', 'smile_customize_register' );
