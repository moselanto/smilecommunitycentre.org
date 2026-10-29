<?php
/**
 * Smile widgets for Elementor (free Elementor, no Pro needed).
 *
 * Every section of the site (57 of them, from the M-Pesa box to the Careers
 * FAQ) plus the site header and footer is an Elementor widget in the "Smile"
 * panel. Each widget offers:
 *   - Settings: the section's options (form type, number of stories ...);
 *   - Texts: every sentence the section prints, editable for this one
 *     widget (an empty box keeps the wording from Site text & photos);
 *   - Photos: every photo the section uses, replaceable for this one widget;
 *   - Style: alignment, colours and typography, plus Elementor's own Advanced tab.
 *
 * The widget list comes from inc/elementor/registry.php, generated from the
 * shortcodes by scripts/build-elementor-widgets.py. Shortcodes keep working,
 * so existing pages are unaffected.
 *
 * Nothing here touches Elementor until Elementor has loaded, so the site works
 * with Elementor switched off.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget registry, keyed by shortcode tag.
 *
 * @return array
 */
function smile_el_registry() {
	static $reg = null;
	if ( null === $reg ) {
		$reg  = array();
		$list = require SMILE_THEME_DIR . '/inc/elementor/registry.php';
		foreach ( (array) $list as $w ) {
			$reg[ $w['tag'] ] = $w;
		}
	}
	return $reg;
}

/**
 * One registry entry.
 *
 * @param string $tag Shortcode tag.
 * @return array
 */
function smile_el_entry( $tag ) {
	$reg = smile_el_registry();
	return isset( $reg[ $tag ] ) ? $reg[ $tag ] : array( 'tag' => $tag, 'title' => $tag, 'group' => 'Sections', 'atts' => array(), 'texts' => array(), 'images' => array() );
}

/* -------------------------------------------------------------------------
 * Per-widget wording, photo and setting changes, applied while a widget renders
 * ---------------------------------------------------------------------- */

/**
 * Stack of active widget overrides.
 *
 * @param string     $op   push|pop|top.
 * @param array|null $item Overrides: texts (key => text), images (key => id), mods (name => value).
 * @return array|null
 */
function smile_el_overrides( $op = 'top', $item = null ) {
	static $stack = array();
	if ( 'push' === $op ) {
		$stack[] = $item;
		return null;
	}
	if ( 'pop' === $op ) {
		array_pop( $stack );
		return null;
	}
	return $stack ? $stack[ count( $stack ) - 1 ] : null;
}

/**
 * Wording changed in the widget.
 *
 * @param string $translation Translation.
 * @param string $text        Original.
 * @param string $domain      Domain.
 * @return string
 */
function smile_el_text_override( $translation, $text, $domain ) {
	if ( 'smile-community' !== $domain && 'smile-core' !== $domain ) {
		return $translation;
	}
	$top = smile_el_overrides();
	if ( ! $top || empty( $top['texts'] ) ) {
		return $translation;
	}
	$key = substr( md5( $domain . '|' . $text ), 0, 16 );
	return isset( $top['texts'][ $key ] ) ? $top['texts'][ $key ] : $translation;
}
add_filter( 'gettext', 'smile_el_text_override', 30, 3 );

/**
 * Photo changed in the widget.
 *
 * @param int    $id  Attachment ID (0 = none).
 * @param string $key Photo key.
 * @return int
 */
function smile_el_image_override( $id, $key ) {
	$top = smile_el_overrides();
	if ( $top && ! empty( $top['images'][ $key ] ) ) {
		return (int) $top['images'][ $key ];
	}
	return $id;
}
add_filter( 'smile_image_key_override', 'smile_el_image_override', 10, 2 );

/**
 * Theme setting changed in a header/footer widget (top bar, Donate text ...).
 *
 * @param mixed $value Value.
 * @return mixed
 */
function smile_el_mod_override( $value ) {
	$top  = smile_el_overrides();
	$name = substr( current_filter(), strlen( 'theme_mod_' ) );
	if ( $top && isset( $top['mods'] ) && array_key_exists( $name, $top['mods'] ) ) {
		return $top['mods'][ $name ];
	}
	return $value;
}
foreach ( array( 'smile_show_topbar', 'smile_donate_label', 'smile_donate_url', 'smile_footer_tagline' ) as $smile_el_mod ) {
	add_filter( 'theme_mod_' . $smile_el_mod, 'smile_el_mod_override', 20 );
}
unset( $smile_el_mod );

/* -------------------------------------------------------------------------
 * Elementor registration
 * ---------------------------------------------------------------------- */

/**
 * "Smile" category at the top of the Elementor panel.
 *
 * @param object $elements_manager Elementor elements manager.
 */
function smile_el_category( $elements_manager ) {
	if ( is_object( $elements_manager ) && method_exists( $elements_manager, 'add_category' ) ) {
		$elements_manager->add_category(
			'smile',
			array(
				'title' => __( 'Smile Community Centre', 'smile-community' ),
				'icon'  => 'eicon-heart',
			)
		);
	}
}
add_action( 'elementor/elements/categories_registered', 'smile_el_category' );

/**
 * Register every Smile widget.
 *
 * @param object $widgets_manager Elementor widgets manager.
 */
function smile_el_register_widgets( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) || ! is_object( $widgets_manager ) ) {
		return;
	}
	require_once SMILE_THEME_DIR . '/inc/elementor/widget-base.php';
	require_once SMILE_THEME_DIR . '/inc/elementor/widget-classes.php';
	$register = method_exists( $widgets_manager, 'register' ) ? 'register' : 'register_widget_type';
	foreach ( smile_el_registry() as $tag => $w ) {
		$class = 'Smile_EW_' . substr( $tag, strlen( 'smile_' ) );
		if ( class_exists( $class ) ) {
			$widgets_manager->$register( new $class() );
		}
	}
	foreach ( array( 'Smile_EW_Block_Logo', 'Smile_EW_Block_Menu', 'Smile_EW_Block_Social', 'Smile_EW_Block_Donate' ) as $class ) {
		if ( class_exists( $class ) ) {
			$widgets_manager->$register( new $class() );
		}
	}
}
add_action( 'elementor/widgets/register', 'smile_el_register_widgets' );

/**
 * Small editor style for the Smile panel and notes.
 */
function smile_el_editor_styles() {
	wp_register_style( 'smile-el-editor', false, array(), SMILE_THEME_VERSION );
	wp_enqueue_style( 'smile-el-editor' );
	wp_add_inline_style(
		'smile-el-editor',
		'.elementor-panel .elementor-element .icon .eicon-heart,.elementor-panel [class*="smile-"] .icon i{color:#146C3C}.smile-el-note{font-size:12px;line-height:1.5;color:#5D6A64}'
	);
}
add_action( 'elementor/editor/after_enqueue_styles', 'smile_el_editor_styles' );
