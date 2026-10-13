<?php
/**
 * Site text & photos: lets the centre edit every piece of text and every photo
 * that the theme and the Smile Community Core plugin print, without code.
 *
 *  - Text: every visitor-facing sentence (589+) is listed by section in
 *    Smile Centre > Site text & photos. A changed wording is stored in the
 *    option `smile_text_overrides` and swapped in through WordPress's gettext
 *    filter, so the original stays as a safe default.
 *  - Photos: every photo used by the theme's sections (by its key) can be
 *    replaced with any image from the Media Library (option `smile_image_overrides`).
 *
 * Page layouts and their headings remain editable in Elementor as before.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Front end: apply text and photo changes
 * ---------------------------------------------------------------------- */

/**
 * Stable key for a string.
 *
 * @param string $domain Text domain.
 * @param string $text   Original text.
 * @return string
 */
function smile_text_key( $domain, $text ) {
	return substr( md5( $domain . '|' . $text ), 0, 16 );
}

/**
 * Saved wording changes.
 *
 * @return array key => text
 */
function smile_text_overrides() {
	static $cache = null;
	if ( null === $cache ) {
		$cache = get_option( 'smile_text_overrides', array() );
		$cache = is_array( $cache ) ? $cache : array();
	}
	return $cache;
}

/**
 * Swap in edited wording for theme and plugin text.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function smile_text_filter( $translation, $text, $domain ) {
	if ( 'smile-community' !== $domain && 'smile-core' !== $domain ) {
		return $translation;
	}
	$over = smile_text_overrides();
	if ( ! $over ) {
		return $translation;
	}
	$key = smile_text_key( $domain, $text );
	return isset( $over[ $key ] ) && '' !== $over[ $key ] ? $over[ $key ] : $translation;
}

/**
 * Only on the public site (and Elementor previews), never inside the editor screens.
 */
function smile_text_filter_init() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	if ( smile_text_overrides() ) {
		add_filter( 'gettext', 'smile_text_filter', 20, 3 );
	}
}
add_action( 'init', 'smile_text_filter_init', 1 );

/**
 * Photo replacements by key.
 *
 * @return array key => attachment ID
 */
function smile_image_overrides() {
	static $cache = null;
	if ( null === $cache ) {
		$cache = get_option( 'smile_image_overrides', array() );
		$cache = is_array( $cache ) ? array_map( 'absint', $cache ) : array();
	}
	return $cache;
}

/* -------------------------------------------------------------------------
 * Admin screen
 * ---------------------------------------------------------------------- */

/**
 * Friendly names for sections.
 *
 * @param string $section Function or shortcode name.
 * @return string
 */
function smile_text_section_label( $section ) {
	$labels = array(
		'header'                         => 'Header and top bar',
		'footer'                         => 'Footer',
		'cta-band'                       => 'Closing "Help a child" band',
		'archive'                        => 'Stories & News page',
		'content-card'                   => 'Story cards',
		'single'                         => 'Single story',
		'search'                         => 'Search results',
		'searchform'                     => 'Search box',
		'404'                            => 'Page not found',
		'smile_hero_slider'              => 'Home: slider controls',
		'smile_slider_defaults'          => 'Home: slider slides (default wording)',
		'smile_programmes'               => 'Programmes cards',
		'smile_programme_data'           => 'Programme pages (at a glance, pull quotes, ways to help)',
		'smile_needs'                    => 'Home: the need',
		'smile_priorities'               => 'Home: where help is needed now',
		'smile_journey'                  => 'Journey timeline',
		'smile_involve'                  => 'Ways to help (M-Pesa box and cards)',
		'smile_involve_nav'              => 'Get involved: page links bar',
		'smile_community'                => 'Testimonials and friends',
		'smile_gift_calc'                => 'Gift calculator',
		'smile_wall'                     => 'Gallery: wall of hope',
		'smile_stats'                    => 'Our Impact: figures',
		'smile_mv'                       => 'Mission and vision',
		'smile_objectives'               => 'Our Goals: objectives',
		'smile_dream'                    => 'Looking ahead',
		'smile_founder_quote'            => 'Founder quote (About)',
		'smile_founder_words'            => 'Our Founder: closing quote',
		'smile_founder_path'             => 'Our Founder: her journey',
		'smile_roles'                    => 'Volunteer: roles',
		'smile_lookfor'                  => 'Volunteer: what we look for',
		'smile_intl'                     => 'Volunteer: international volunteers',
		'smile_partner_types'            => 'Partner: who we work with',
		'smile_contact_hub'              => 'Contact: contact cards',
		'smile_contact_cards'            => 'Contact cards (older layout)',
		'smile_next_steps'               => 'Contact: what happens next',
		'smile_visit_expect'             => 'Contact: plan a visit',
		'smile_use'                      => 'Donate: how we use donations',
		'smile_trust'                    => 'Donate: trust strip',
		'smile_commitments'              => 'Safeguarding: commitments',
		'smile_urgent'                   => 'Safeguarding: urgent help',
		'smile_concern_contacts'         => 'Safeguarding: concern contacts',
		'smile_steps'                    => 'Step-by-step guides',
		'smile_mobile_bar'               => 'Phone bottom bar',
		'smile_core_form_fields'         => 'Forms: field labels and options',
		'smile_core_form_shortcode'      => 'Forms: messages and buttons',
		'smile_core_form_submit'         => 'Forms: validation messages',
		'smile_core_sc_mpesa'            => 'M-Pesa giving box',
		'smile_core_sc_contact'          => 'Contact details list',
		'smile_core_sc_directions'       => 'Directions',
	);
	if ( isset( $labels[ $section ] ) ) {
		return $labels[ $section ];
	}
	return ucfirst( str_replace( array( 'smile_core_sc_', 'smile_core_', 'smile_', '_', '-' ), array( '', '', '', ' ', ' ' ), $section ) );
}

/**
 * Register the screen under Smile Centre (or Appearance if the plugin is off).
 */
function smile_content_menu() {
	global $admin_page_hooks;
	$parent = isset( $admin_page_hooks['smile-centre'] ) ? 'smile-centre' : 'themes.php';
	add_submenu_page( $parent, __( 'Site text & photos', 'smile-community' ), __( 'Site text & photos', 'smile-community' ), 'edit_pages', 'smile-site-content', 'smile_content_screen' );
}
add_action( 'admin_menu', 'smile_content_menu', 30 );

/**
 * Media picker for the Photos tab.
 *
 * @param string $hook Screen hook.
 */
function smile_content_assets( $hook ) {
	if ( false === strpos( (string) $hook, 'smile-site-content' ) ) {
		return;
	}
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'smile_content_assets' );

/**
 * Render the screen.
 */
function smile_content_screen() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'You do not have permission to edit site text.', 'smile-community' ) );
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab switch only.
	$tab = isset( $_GET['tab'] ) && 'photos' === sanitize_key( wp_unslash( $_GET['tab'] ) ) ? 'photos' : 'text';
	$url = admin_url( 'admin.php?page=smile-site-content' );
	?>
	<div class="wrap smile-content">
		<h1><?php esc_html_e( 'Site text & photos', 'smile-community' ); ?></h1>
		<p><?php esc_html_e( 'Change any wording or photo on the website. Leave a box empty to keep the original. Page layouts and headings placed in Elementor are edited in Elementor as usual.', 'smile-community' ); ?></p>
		<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only. ?>
		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved. Clear your page cache if the change does not show straight away.', 'smile-community' ); ?></p></div>
		<?php endif; ?>
		<nav class="nav-tab-wrapper">
			<a class="nav-tab<?php echo 'text' === $tab ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Text', 'smile-community' ); ?></a>
			<a class="nav-tab<?php echo 'photos' === $tab ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', 'photos', $url ) ); ?>"><?php esc_html_e( 'Photos', 'smile-community' ); ?></a>
		</nav>
		<style>
			.smile-content details{background:#fff;border:1px solid #dcdcde;border-radius:8px;margin:12px 0;max-width:1100px}
			.smile-content summary{padding:12px 16px;font-weight:600;cursor:pointer}
			.smile-content summary span{color:#646970;font-weight:400;margin-left:6px}
			.smile-content table{width:100%;border-collapse:collapse}
			.smile-content td{padding:8px 16px;border-top:1px solid #f0f0f1;vertical-align:top}
			.smile-content td.orig{width:42%;color:#50575e}
			.smile-content textarea{width:100%;min-height:38px}
			.smile-content tr.is-changed td.orig{color:#8a2424}
			.smile-content .smile-search{margin:14px 0;max-width:420px;width:100%}
			.smile-content .smile-photos{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;max-width:1100px;margin-top:14px}
			.smile-content .smile-photo{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:10px}
			.smile-content .smile-photo img{width:100%;height:130px;object-fit:cover;border-radius:6px;background:#f0f0f1;display:block}
			.smile-content .smile-photo code{display:block;margin:8px 0 6px;font-size:11px}
		</style>
		<?php
		if ( 'photos' === $tab ) {
			smile_content_photos_tab();
		} else {
			smile_content_text_tab();
		}
		?>
	</div>
	<?php
}

/**
 * Text tab.
 */
function smile_content_text_tab() {
	$file    = SMILE_THEME_DIR . '/demo/strings.json';
	$strings = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$over    = smile_text_overrides();
	$groups  = array();
	foreach ( (array) $strings as $row ) {
		$groups[ smile_text_section_label( $row['section'] ) ][] = $row;
	}
	ksort( $groups );
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="smile-text-form">
		<input type="hidden" name="action" value="smile_save_text">
		<?php wp_nonce_field( 'smile_save_text' ); ?>
		<input type="search" class="smile-search" id="smile-text-search" placeholder="<?php esc_attr_e( 'Search the text, for example "M-Pesa" or "volunteer"', 'smile-community' ); ?>">
		<p><?php echo esc_html( sprintf( /* translators: 1 strings 2 changed */ __( '%1$d pieces of text, %2$d changed.', 'smile-community' ), count( (array) $strings ), count( $over ) ) ); ?></p>
		<?php foreach ( $groups as $label => $rows ) : ?>
			<?php
			$changed = 0;
			foreach ( $rows as $r ) {
				if ( isset( $over[ smile_text_key( $r['domain'], $r['text'] ) ] ) ) {
					++$changed;
				}
			}
			?>
			<details class="smile-group">
				<summary><?php echo esc_html( $label ); ?><span><?php echo esc_html( sprintf( /* translators: 1 count 2 changed */ __( '%1$d items, %2$d changed', 'smile-community' ), count( $rows ), $changed ) ); ?></span></summary>
				<table>
					<?php foreach ( $rows as $r ) : ?>
						<?php
						$key = smile_text_key( $r['domain'], $r['text'] );
						$val = isset( $over[ $key ] ) ? $over[ $key ] : '';
						?>
						<tr class="<?php echo '' !== $val ? 'is-changed' : ''; ?>" data-search="<?php echo esc_attr( strtolower( $r['text'] . ' ' . $val ) ); ?>">
							<td class="orig"><?php echo esc_html( $r['text'] ); ?></td>
							<td><textarea rows="<?php echo strlen( $r['text'] ) > 90 ? 3 : 1; ?>" data-key="<?php echo esc_attr( $key ); ?>" placeholder="<?php esc_attr_e( 'Same as original', 'smile-community' ); ?>"><?php echo esc_textarea( $val ); ?></textarea></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</details>
		<?php endforeach; ?>
		<?php submit_button( __( 'Save text', 'smile-community' ) ); ?>
	</form>
	<script>
	( function () {
		var form = document.getElementById( 'smile-text-form' );
		form.querySelectorAll( 'textarea[data-key]' ).forEach( function ( t ) {
			t.addEventListener( 'input', function () { t.name = 'smile_text[' + t.getAttribute( 'data-key' ) + ']'; } );
		} );
		var box = document.getElementById( 'smile-text-search' );
		box.addEventListener( 'input', function () {
			var q = box.value.trim().toLowerCase();
			form.querySelectorAll( 'details.smile-group' ).forEach( function ( d ) {
				var any = false;
				d.querySelectorAll( 'tr[data-search]' ).forEach( function ( tr ) {
					var hit = '' === q || tr.getAttribute( 'data-search' ).indexOf( q ) > -1;
					tr.style.display = hit ? '' : 'none';
					any = any || hit;
				} );
				d.style.display = any ? '' : 'none';
				d.open = '' !== q && any;
			} );
		} );
	}() );
	</script>
	<?php
}

/**
 * Save text changes. Only boxes the editor touched are posted.
 */
function smile_save_text() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'You do not have permission to edit site text.', 'smile-community' ), 403 );
	}
	check_admin_referer( 'smile_save_text' );
	$over  = smile_text_overrides();
	$input = isset( $_POST['smile_text'] ) && is_array( $_POST['smile_text'] ) ? wp_unslash( $_POST['smile_text'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
	foreach ( $input as $key => $value ) {
		$key   = preg_replace( '/[^a-f0-9]/', '', (string) $key );
		$value = trim( sanitize_textarea_field( (string) $value ) );
		if ( 16 !== strlen( $key ) ) {
			continue;
		}
		if ( '' === $value ) {
			unset( $over[ $key ] );
		} else {
			$over[ $key ] = mb_substr( $value, 0, 2000 );
		}
	}
	update_option( 'smile_text_overrides', $over, true );
	wp_safe_redirect( add_query_arg( array( 'page' => 'smile-site-content', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_smile_save_text', 'smile_save_text' );

/**
 * Photos tab.
 */
function smile_content_photos_tab() {
	$file   = SMILE_THEME_DIR . '/demo/images.json';
	$images = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$over   = smile_image_overrides();
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="smile_save_photos">
		<?php wp_nonce_field( 'smile_save_photos' ); ?>
		<p><?php esc_html_e( 'Click "Choose photo" to replace a photo everywhere it appears. "Use original" puts the theme photo back. Only use photos the centre has permission to publish.', 'smile-community' ); ?></p>
		<div class="smile-photos">
			<?php foreach ( (array) $images as $img ) : ?>
				<?php
				if ( empty( $img['key'] ) ) {
					continue;
				}
				$key = sanitize_key( $img['key'] );
				$id  = isset( $over[ $key ] ) ? (int) $over[ $key ] : 0;
				$cur = $id ? $id : smile_image_by_key( $key );
				$src = $cur ? wp_get_attachment_image_url( $cur, 'medium' ) : '';
				?>
				<div class="smile-photo">
					<img src="<?php echo esc_url( $src ); ?>" alt="" data-preview="<?php echo esc_attr( $key ); ?>">
					<code><?php echo esc_html( $key ); ?><?php echo $id ? ' ' . esc_html__( '(replaced)', 'smile-community' ) : ''; ?></code>
					<input type="hidden" name="smile_photo[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $id ? $id : '' ); ?>">
					<button type="button" class="button smile-pick" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Choose photo', 'smile-community' ); ?></button>
					<button type="button" class="button-link smile-reset" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Use original', 'smile-community' ); ?></button>
				</div>
			<?php endforeach; ?>
		</div>
		<?php submit_button( __( 'Save photos', 'smile-community' ) ); ?>
	</form>
	<script>
	( function () {
		document.querySelectorAll( '.smile-pick' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				var key = b.getAttribute( 'data-key' );
				var frame = wp.media( { title: 'Choose a photo', button: { text: 'Use this photo' }, library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					document.querySelector( 'input[name="smile_photo[' + key + ']"]' ).value = a.id;
					var sizes = a.sizes || {};
					document.querySelector( 'img[data-preview="' + key + '"]' ).src = ( sizes.medium || sizes.full || a ).url;
				} );
				frame.open();
			} );
		} );
		document.querySelectorAll( '.smile-reset' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				document.querySelector( 'input[name="smile_photo[' + b.getAttribute( 'data-key' ) + ']"]' ).value = '';
				b.textContent = 'Original will be used after saving';
			} );
		} );
	}() );
	</script>
	<?php
}

/**
 * Save photo replacements.
 */
function smile_save_photos() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		wp_die( esc_html__( 'You do not have permission to change photos.', 'smile-community' ), 403 );
	}
	check_admin_referer( 'smile_save_photos' );
	$input = isset( $_POST['smile_photo'] ) && is_array( $_POST['smile_photo'] ) ? wp_unslash( $_POST['smile_photo'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below.
	$over  = array();
	foreach ( $input as $key => $id ) {
		$key = sanitize_key( $key );
		$id  = absint( $id );
		if ( $key && $id && wp_attachment_is_image( $id ) ) {
			$over[ $key ] = $id;
		}
	}
	update_option( 'smile_image_overrides', $over, true );
	if ( function_exists( 'smile_flush_image_map' ) ) {
		smile_flush_image_map();
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'smile-site-content', 'tab' => 'photos', 'saved' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_smile_save_photos', 'smile_save_photos' );
