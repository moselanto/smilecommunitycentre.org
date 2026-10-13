<?php
/**
 * One-click staging builder (Smile Centre > Site setup).
 *
 * Imports the approved photographs from the live site into the Media Library
 * (neutral filenames, alt text, metadata stripped), creates every page as an
 * Elementor page from content/pages.json, builds the menus, sets the front and
 * posts pages, applies the Elementor global colours/fonts and sets the logo.
 *
 * Safe to run more than once: images are reused by key, and existing pages are
 * only replaced when you tick the box. Run it on STAGING, never on production
 * without a verified backup.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register screen.
 */
function smile_core_setup_menu() {
	add_submenu_page( 'smile-centre', __( 'Site setup', 'smile-core' ), __( 'Site setup', 'smile-core' ), 'manage_options', 'smile-setup', 'smile_core_render_setup' );
}
add_action( 'admin_menu', 'smile_core_setup_menu', 20 );

/**
 * Load a JSON file from content/.
 *
 * @param string $name File name.
 * @return array
 */
function smile_core_setup_json( $name ) {
	$file = SMILE_CORE_DIR . 'content/' . $name;
	if ( ! file_exists( $file ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return is_array( $data ) ? $data : array();
}

/**
 * Render screen.
 */
function smile_core_render_setup() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$log = get_transient( 'smile_core_setup_log' );
	delete_transient( 'smile_core_setup_log' );
	$elementor = did_action( 'elementor/loaded' );
	$theme     = wp_get_theme();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Smile Centre: site setup', 'smile-core' ); ?></h1>
		<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Use on the staging site.', 'smile-core' ); ?></strong> <?php esc_html_e( 'This creates and can replace pages, menus and the home page. Take a backup first.', 'smile-core' ); ?></p></div>
		<h2><?php esc_html_e( 'Checks', 'smile-core' ); ?></h2>
		<ul style="list-style:disc;padding-left:1.4em">
			<li><?php echo $elementor ? esc_html__( 'Elementor is active.', 'smile-core' ) : '<strong style="color:#b32d2e">' . esc_html__( 'Elementor is not active. Install and activate Elementor first.', 'smile-core' ) . '</strong>'; ?></li>
			<li><?php echo 'smile-community-child' === $theme->get_stylesheet() ? esc_html__( 'The Smile Community Centre theme is active.', 'smile-core' ) : '<strong style="color:#b32d2e">' . esc_html__( 'Activate the "Smile Community Centre" theme (it needs Hello Elementor installed).', 'smile-core' ) . '</strong>'; ?></li>
			<li><?php echo esc_html( sprintf( /* translators: %d */ __( '%d pages and %d photos are ready to import.', 'smile-core' ), count( smile_core_setup_json( 'pages.json' ) ), count( smile_core_setup_json( 'images.json' ) ) ) ); ?></li>
		</ul>
		<?php if ( is_array( $log ) ) : ?>
			<h2><?php esc_html_e( 'Last run', 'smile-core' ); ?></h2>
			<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:420px;overflow:auto;white-space:pre-wrap"><?php echo esc_html( implode( "\n", $log ) ); ?></pre>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="smile_core_run_setup" />
			<?php wp_nonce_field( 'smile_core_run_setup' ); ?>
			<p><label><input type="checkbox" name="smile_images" value="1" checked> <?php esc_html_e( 'Import the approved photographs from smilecommunitycentre.org', 'smile-core' ); ?></label></p>
			<p><label><input type="checkbox" name="smile_overwrite" value="1"> <?php esc_html_e( 'Replace the content of existing pages that have the same address (old content is kept as a revision)', 'smile-core' ); ?></label></p>
			<p><label><input type="checkbox" name="smile_retire" value="1" checked> <?php esc_html_e( 'Move replaced old pages (Photos, Blog, Sitemap, HIV/AIDS) to Draft so their redirects work', 'smile-core' ); ?></label></p>
			<p><label><input type="checkbox" name="smile_menus" value="1" checked> <?php esc_html_e( 'Build the main and footer menus', 'smile-core' ); ?></label></p>
			<p><label><input type="checkbox" name="smile_kit" value="1" checked> <?php esc_html_e( 'Apply Smile colours and fonts to Elementor global settings', 'smile-core' ); ?></label></p>
			<p><label><input type="checkbox" name="smile_noindex" value="1" checked> <?php esc_html_e( 'Discourage search engines from indexing this site (keep ticked on staging)', 'smile-core' ); ?></label></p>
			<?php submit_button( __( 'Build the site', 'smile-core' ), 'primary', 'submit', true, $elementor ? array() : array( 'disabled' => 'disabled' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Run.
 */
function smile_core_run_setup() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'smile-core' ), 403 );
	}
	check_admin_referer( 'smile_core_run_setup' );
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 600 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$log    = array();
	$images = array();

	// 1. Images.
	foreach ( smile_core_setup_json( 'images.json' ) as $img ) {
		$existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_smile_img_key', 'meta_value' => $img['key'], 'fields' => 'ids', 'posts_per_page' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $existing ) {
			$images[ $img['key'] ] = (int) $existing[0];
			continue;
		}
		if ( empty( $_POST['smile_images'] ) ) {
			continue;
		}
		$tmp = download_url( $img['source'], 60 );
		if ( is_wp_error( $tmp ) ) {
			$log[] = 'IMAGE FAILED ' . $img['key'] . ': ' . $tmp->get_error_message();
			continue;
		}
		$id = media_handle_sideload( array( 'name' => $img['filename'], 'tmp_name' => $tmp ), 0, $img['title'] );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
			$log[] = 'IMAGE FAILED ' . $img['key'] . ': ' . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $img['alt'] ) );
		update_post_meta( $id, '_smile_img_key', $img['key'] );
		update_post_meta( $id, '_smile_source', esc_url_raw( $img['source'] ) );
		update_post_meta( $id, '_smile_consent', 'pending-review' );
		wp_update_post( array( 'ID' => $id, 'post_content' => '', 'post_excerpt' => '' ) );
		$images[ $img['key'] ] = (int) $id;
		$log[]                 = 'Image imported: ' . $img['filename'];
	}

	// 2. Categories for stories.
	foreach ( array( 'Stories of Hope', 'News', 'Guides', 'Community' ) as $cat ) {
		if ( ! term_exists( $cat, 'category' ) ) {
			wp_insert_term( $cat, 'category' );
		}
	}

	// 3. Pages.
	$overwrite = ! empty( $_POST['smile_overwrite'] );
	$page_ids  = array();
	foreach ( smile_core_setup_json( 'pages.json' ) as $page ) {
		$existing = get_page_by_path( $page['slug'] );
		$data     = isset( $page['elementor'] ) ? smile_core_setup_swap_images( $page['elementor'], $images ) : null;
		$postarr  = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => isset( $page['html'] ) ? wp_kses_post( smile_core_setup_swap_html( $page['html'], $images ) ) : '',
			'menu_order'   => isset( $page['order'] ) ? (int) $page['order'] : 0,
		);
		if ( $existing ) {
			$built = get_post_meta( $existing->ID, '_smile_setup', true );
			if ( ! $built && ! $overwrite ) {
				$page_ids[ $page['slug'] ] = $existing->ID;
				$log[]                     = 'Kept existing page (not replaced): /' . $page['slug'] . '/';
				continue;
			}
			if ( ! $built ) {
				update_post_meta( $existing->ID, '_smile_previous_content', $existing->post_content );
			}
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( wp_slash( $postarr ), true );
		} else {
			$id = wp_insert_post( wp_slash( $postarr ), true );
		}
		if ( is_wp_error( $id ) ) {
			$log[] = 'PAGE FAILED /' . $page['slug'] . '/: ' . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_smile_setup', '1' );
		if ( null !== $data ) {
			update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
			update_post_meta( $id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
			update_post_meta( $id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		}
		if ( ! empty( $page['seo_title'] ) ) {
			update_post_meta( $id, '_seopress_titles_title', sanitize_text_field( $page['seo_title'] ) );
		}
		if ( ! empty( $page['seo_desc'] ) ) {
			update_post_meta( $id, '_seopress_titles_desc', sanitize_text_field( $page['seo_desc'] ) );
		}
		if ( ! empty( $page['image'] ) && isset( $images[ $page['image'] ] ) ) {
			set_post_thumbnail( $id, $images[ $page['image'] ] );
		}
		$page_ids[ $page['slug'] ] = (int) $id;
		$log[]                     = ( $existing ? 'Updated' : 'Created' ) . ' page: /' . $page['slug'] . '/';
	}

	// 4. Retire old pages.
	if ( ! empty( $_POST['smile_retire'] ) ) {
		foreach ( array( 'photos', 'blog', 'sitemap', 'hiv-and-the-community' ) as $old ) {
			$p = get_page_by_path( $old );
			if ( $p && ! get_post_meta( $p->ID, '_smile_setup', true ) && 'publish' === $p->post_status ) {
				wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
				$log[] = 'Moved old page to Draft: /' . $old . '/';
			}
		}
	}

	// 5. Reading settings.
	if ( isset( $page_ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['home'] );
	}
	if ( isset( $page_ids['stories'] ) ) {
		update_option( 'page_for_posts', $page_ids['stories'] );
	}
	if ( isset( $page_ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );
	}
	if ( '' === (string) get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();
	update_option( 'blog_public', empty( $_POST['smile_noindex'] ) ? '1' : '0' );
	$log[] = empty( $_POST['smile_noindex'] ) ? 'Search engines: allowed.' : 'Search engines: discouraged (staging).';

	// 6. Menus.
	if ( ! empty( $_POST['smile_menus'] ) ) {
		smile_core_setup_menus( $page_ids, $log );
	}

	// 7. Logo.
	if ( isset( $images['logo'] ) && ! get_theme_mod( 'custom_logo' ) ) {
		set_theme_mod( 'custom_logo', $images['logo'] );
		$log[] = 'Logo set.';
	}

	// 8. Elementor kit.
	if ( ! empty( $_POST['smile_kit'] ) ) {
		smile_core_setup_kit( $log );
	}

	if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		$log[] = 'Elementor CSS cache cleared.';
	}

	$log[] = 'Done. Next: review each page, then run the QA checklist in docs/qa-checklist.md.';
	set_transient( 'smile_core_setup_log', $log, HOUR_IN_SECONDS );
	wp_safe_redirect( admin_url( 'admin.php?page=smile-setup' ) );
	exit;
}
add_action( 'admin_post_smile_core_run_setup', 'smile_core_run_setup' );

/**
 * Replace smile-img:key placeholders in Elementor data.
 *
 * @param mixed $node   Data.
 * @param array $images key => attachment ID.
 * @return mixed
 */
function smile_core_setup_swap_images( $node, $images ) {
	if ( is_array( $node ) ) {
		if ( isset( $node['url'] ) && is_string( $node['url'] ) && 0 === strpos( $node['url'], 'smile-img:' ) ) {
			$key = substr( $node['url'], 10 );
			if ( isset( $images[ $key ] ) ) {
				$node['id']  = $images[ $key ];
				$node['url'] = wp_get_attachment_url( $images[ $key ] );
				$node['alt'] = get_post_meta( $images[ $key ], '_wp_attachment_image_alt', true );
			} else {
				$node['id']  = '';
				$node['url'] = '';
			}
			return $node;
		}
		foreach ( $node as $k => $v ) {
			$node[ $k ] = smile_core_setup_swap_images( $v, $images );
		}
	}
	return $node;
}

/**
 * Replace placeholders in fallback HTML.
 *
 * @param string $html   HTML.
 * @param array  $images Images.
 * @return string
 */
function smile_core_setup_swap_html( $html, $images ) {
	return preg_replace_callback(
		'/smile-img:([a-z0-9-]+)/',
		function ( $m ) use ( $images ) {
			return isset( $images[ $m[1] ] ) ? (string) wp_get_attachment_url( $images[ $m[1] ] ) : '';
		},
		$html
	);
}

/**
 * Build menus.
 *
 * @param array $ids  slug => page ID.
 * @param array $log  Log (by reference).
 */
function smile_core_setup_menus( $ids, &$log ) {
	$menus = smile_core_setup_json( 'menus.json' );
	$locs  = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $menu ) {
		$obj = wp_get_nav_menu_object( $menu['name'] );
		if ( $obj ) {
			foreach ( (array) wp_get_nav_menu_items( $obj->term_id ) as $item ) {
				wp_delete_post( $item->ID, true );
			}
			$menu_id = $obj->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $menu['name'] );
		}
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		smile_core_setup_menu_items( $menu_id, $menu['items'], 0, $ids );
		$locs[ $menu['location'] ] = $menu_id;
		$log[]                     = 'Menu built: ' . $menu['name'];
	}
	set_theme_mod( 'nav_menu_locations', $locs );
}

/**
 * Add menu items recursively.
 *
 * @param int   $menu_id Menu.
 * @param array $items   Items.
 * @param int   $parent  Parent item ID.
 * @param array $ids     Page IDs.
 */
function smile_core_setup_menu_items( $menu_id, $items, $parent, $ids ) {
	foreach ( $items as $item ) {
		$args = array(
			'menu-item-title'     => $item['title'],
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent,
		);
		if ( ! empty( $item['page'] ) && isset( $ids[ $item['page'] ] ) ) {
			$args['menu-item-object-id'] = $ids[ $item['page'] ];
			$args['menu-item-object']    = 'page';
			$args['menu-item-type']      = 'post_type';
		} else {
			$args['menu-item-url']  = home_url( isset( $item['url'] ) ? $item['url'] : '/' );
			$args['menu-item-type'] = 'custom';
		}
		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $item_id ) && ! empty( $item['children'] ) ) {
			smile_core_setup_menu_items( $menu_id, $item['children'], $item_id, $ids );
		}
	}
}

/**
 * Elementor global colours and typography on the active kit.
 *
 * @param array $log Log (by reference).
 */
function smile_core_setup_kit( &$log ) {
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( ! $kit_id ) {
		$log[] = 'Elementor kit not found; skipped global styles.';
		return;
	}
	$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
	$settings = is_array( $settings ) ? $settings : array();

	$settings['system_colors'] = array(
		array( '_id' => 'primary', 'title' => 'Teal', 'color' => '#176B5B' ),
		array( '_id' => 'secondary', 'title' => 'Forest', 'color' => '#164B3F' ),
		array( '_id' => 'text', 'title' => 'Charcoal text', 'color' => '#24312D' ),
		array( '_id' => 'accent', 'title' => 'Sunshine', 'color' => '#F4C95D' ),
	);
	$settings['custom_colors'] = array(
		array( '_id' => 'smcream', 'title' => 'Cream', 'color' => '#FFF9EF' ),
		array( '_id' => 'smsand', 'title' => 'Sand', 'color' => '#F6EEDC' ),
		array( '_id' => 'smmuted', 'title' => 'Muted grey-green', 'color' => '#5D6A64' ),
		array( '_id' => 'smwhite', 'title' => 'White', 'color' => '#FFFFFF' ),
	);
	$settings['system_typography'] = array(
		array( '_id' => 'primary', 'title' => 'Headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Fraunces', 'typography_font_weight' => '600' ),
		array( '_id' => 'secondary', 'title' => 'Sub-headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '700' ),
		array( '_id' => 'text', 'title' => 'Body', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '400' ),
		array( '_id' => 'accent', 'title' => 'Buttons', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '700' ),
	);
	$settings['container_width']   = array( 'unit' => 'px', 'size' => 1200 );
	$settings['body_color']        = '#24312D';
	$settings['link_normal_color'] = '#176B5B';
	update_post_meta( $kit_id, '_elementor_page_settings', $settings );
	$log[] = 'Elementor global colours and fonts applied.';
}
