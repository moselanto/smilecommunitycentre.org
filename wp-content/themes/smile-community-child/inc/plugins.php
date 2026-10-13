<?php
/**
 * Required / recommended plugins (ThemeForest-style).
 *
 * - Elementor, SEOPress and WP Mail SMTP install from WordPress.org.
 * - Smile Community Core ships inside the theme (plugins/smile-community-core)
 *   and is copied into wp-content/plugins, so content and enquiries survive a
 *   future theme change.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin list.
 *
 * @return array
 */
function smile_theme_plugins() {
	return array(
		'elementor'            => array(
			'name'     => 'Elementor',
			'file'     => 'elementor/elementor.php',
			'source'   => 'repo',
			'required' => true,
			'why'      => __( 'Visual editor for every page.', 'smile-community' ),
		),
		'smile-community-core' => array(
			'name'     => 'Smile Community Core',
			'file'     => 'smile-community-core/smile-community-core.php',
			'source'   => 'bundled',
			'required' => true,
			'why'      => __( 'Centre details, M-Pesa block, forms and enquiries inbox, redirects, photo privacy. Included with the theme.', 'smile-community' ),
		),
		'wp-seopress'          => array(
			'name'     => 'SEOPress',
			'file'     => 'wp-seopress/seopress.php',
			'source'   => 'repo',
			'required' => false,
			'why'      => __( 'SEO titles, descriptions and XML sitemap (already used on the live site).', 'smile-community' ),
		),
		'wp-super-cache'       => array(
			'name'     => 'WP Super Cache',
			'file'     => 'wp-super-cache/wp-cache.php',
			'source'   => 'repo',
			'required' => false,
			'why'      => __( 'Page caching: serves ready-made pages so the site opens in about a second. Skip if your host or WP Rocket already caches pages.', 'smile-community' ),
		),
		'wp-mail-smtp'         => array(
			'name'     => 'WP Mail SMTP',
			'file'     => 'wp-mail-smtp/wp_mail_smtp.php',
			'source'   => 'repo',
			'required' => false,
			'why'      => __( 'Makes sure form emails are actually delivered.', 'smile-community' ),
		),
	);
}

/**
 * Version of the bundled core plugin.
 *
 * @return string
 */
function smile_theme_bundled_core_version() {
	$file = SMILE_THEME_DIR . '/plugins/smile-community-core/smile-community-core.php';
	if ( ! file_exists( $file ) ) {
		return '';
	}
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$data = get_plugin_data( $file, false, false );
	return isset( $data['Version'] ) ? $data['Version'] : '';
}

/**
 * Status of one plugin: missing | inactive | update | active.
 *
 * @param string $slug Slug.
 * @return string
 */
function smile_theme_plugin_status( $slug ) {
	$plugins = smile_theme_plugins();
	if ( ! isset( $plugins[ $slug ] ) ) {
		return 'missing';
	}
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$file = $plugins[ $slug ]['file'];
	if ( ! file_exists( WP_PLUGIN_DIR . '/' . $file ) ) {
		return 'missing';
	}
	if ( 'bundled' === $plugins[ $slug ]['source'] ) {
		$installed = get_plugin_data( WP_PLUGIN_DIR . '/' . $file, false, false );
		if ( version_compare( smile_theme_bundled_core_version(), $installed['Version'], '>' ) ) {
			return 'update';
		}
	}
	return is_plugin_active( $file ) ? 'active' : 'inactive';
}

/**
 * Are all required plugins active?
 *
 * @return bool
 */
function smile_theme_required_ready() {
	foreach ( smile_theme_plugins() as $slug => $p ) {
		if ( $p['required'] && 'active' !== smile_theme_plugin_status( $slug ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Admin notice until required plugins are active.
 */
function smile_theme_plugins_notice() {
	if ( ! current_user_can( 'install_plugins' ) || smile_theme_required_ready() ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_smile-setup' === $screen->id ) {
		return;
	}
	$missing = array();
	foreach ( smile_theme_plugins() as $slug => $p ) {
		if ( $p['required'] && 'active' !== smile_theme_plugin_status( $slug ) ) {
			$missing[] = $p['name'];
		}
	}
	echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Smile Community Centre theme', 'smile-community' ) . ':</strong> ' . esc_html( sprintf( /* translators: %s plugin names */ __( 'requires these plugins: %s.', 'smile-community' ), implode( ', ', $missing ) ) ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=smile-setup&step=plugins' ) ) . '">' . esc_html__( 'Begin installing plugins', 'smile-community' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'smile_theme_plugins_notice' );

/**
 * AJAX: install and/or activate one plugin.
 */
function smile_theme_ajax_install_plugin() {
	check_ajax_referer( 'smile_setup', 'nonce' );
	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'smile-community' ) ), 403 );
	}
	$slug    = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
	$plugins = smile_theme_plugins();
	if ( ! isset( $plugins[ $slug ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Unknown plugin.', 'smile-community' ) ) );
	}
	$p      = $plugins[ $slug ];
	$status = smile_theme_plugin_status( $slug );

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	if ( in_array( $status, array( 'missing', 'update' ), true ) ) {
		if ( 'direct' !== get_filesystem_method() ) {
			wp_send_json_error( array( 'message' => __( 'This server needs FTP details to install plugins. Install it from Plugins > Add New instead.', 'smile-community' ) ) );
		}
		WP_Filesystem();
		global $wp_filesystem;

		if ( 'bundled' === $p['source'] ) {
			$src  = SMILE_THEME_DIR . '/plugins/' . $slug;
			$dest = WP_PLUGIN_DIR . '/' . $slug;
			if ( ! $wp_filesystem->is_dir( $dest ) && ! $wp_filesystem->mkdir( $dest, FS_CHMOD_DIR ) ) {
				wp_send_json_error( array( 'message' => __( 'Could not create the plugin folder. Check wp-content/plugins permissions.', 'smile-community' ) ) );
			}
			$copied = copy_dir( $src, $dest );
			if ( is_wp_error( $copied ) ) {
				wp_send_json_error( array( 'message' => $copied->get_error_message() ) );
			}
			wp_clean_plugins_cache();
		} else {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}
			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}
			if ( ! $result ) {
				$errors = $upgrader->skin->get_errors();
				wp_send_json_error( array( 'message' => is_wp_error( $errors ) && $errors->has_errors() ? $errors->get_error_message() : __( 'Installation failed.', 'smile-community' ) ) );
			}
		}
	}

	if ( ! is_plugin_active( $p['file'] ) ) {
		$activated = activate_plugin( $p['file'], '', false, true );
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
		}
	}

	// Keep plugins from hijacking the wizard with their own welcome screens.
	delete_transient( 'elementor_activation_redirect' );
	delete_transient( '_wp_mail_smtp_activation_redirect' );
	update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
	delete_option( 'seopress_activated' );

	wp_send_json_success( array( 'message' => __( 'Installed and active', 'smile-community' ) ) );
}
add_action( 'wp_ajax_smile_install_plugin', 'smile_theme_ajax_install_plugin' );
