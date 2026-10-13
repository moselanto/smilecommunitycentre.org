<?php
/**
 * Demo content importer, run in small AJAX steps so slow hosts do not time out.
 * Content lives in demo/*.json. Safe to run again: photos are reused by key and
 * pages built earlier are refreshed.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a demo JSON file.
 *
 * @param string $name File.
 * @return array
 */
function smile_demo_json( $name ) {
	$file = SMILE_THEME_DIR . '/demo/' . $name;
	if ( ! file_exists( $file ) ) {
		return array();
	}
	$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return is_array( $data ) ? $data : array();
}

/**
 * Ordered list of import steps.
 *
 * @param array $opts Options.
 * @return array
 */
function smile_demo_plan( $opts ) {
	$steps = array();
	if ( ! empty( $opts['images'] ) ) {
		foreach ( smile_demo_json( 'images.json' ) as $img ) {
			$steps[] = 'image:' . $img['key'];
		}
	}
	$steps[] = 'categories';
	$steps[] = 'pages';
	if ( ! empty( $opts['retire'] ) ) {
		$steps[] = 'retire';
	}
	$steps[] = 'reading';
	$steps[] = 'menus';
	$steps[] = 'logo';
	if ( ! empty( $opts['kit'] ) ) {
		$steps[] = 'kit';
	}
	$steps[] = 'finish';
	return $steps;
}

/**
 * Map of image key => attachment ID for already-imported photos.
 *
 * @return array
 */
function smile_demo_image_map() {
	$map = array();
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 200,
			'fields'         => 'ids',
			'meta_key'       => '_smile_img_key', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	foreach ( $ids as $id ) {
		$map[ get_post_meta( $id, '_smile_img_key', true ) ] = (int) $id;
	}
	return $map;
}

/**
 * AJAX: run one step.
 */
function smile_demo_ajax_step() {
	check_ajax_referer( 'smile_setup', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Not allowed.', 'smile-community' ) ), 403 );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	$step = isset( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : '';
	$opts = array(
		'overwrite' => ! empty( $_POST['overwrite'] ),
		'noindex'   => ! empty( $_POST['noindex'] ),
	);

	if ( 0 === strpos( $step, 'image:' ) ) {
		wp_send_json_success( array( 'message' => smile_demo_import_image( substr( $step, 6 ) ) ) );
	}
	switch ( $step ) {
		case 'categories':
			foreach ( array( 'Stories of Hope', 'News', 'Guides', 'Community' ) as $cat ) {
				if ( ! term_exists( $cat, 'category' ) ) {
					wp_insert_term( $cat, 'category' );
				}
			}
			wp_send_json_success( array( 'message' => __( 'Story categories ready', 'smile-community' ) ) );
			break;
		case 'pages':
			wp_send_json_success( array( 'message' => smile_demo_import_pages( $opts['overwrite'] ) ) );
			break;
		case 'retire':
			$done = array();
			foreach ( array( 'photos', 'blog', 'sitemap', 'hiv-and-the-community' ) as $old ) {
				$p = get_page_by_path( $old );
				if ( $p && ! get_post_meta( $p->ID, '_smile_setup', true ) && 'publish' === $p->post_status ) {
					wp_update_post( array( 'ID' => $p->ID, 'post_status' => 'draft' ) );
					$done[] = '/' . $old . '/';
				}
			}
			wp_send_json_success( array( 'message' => $done ? __( 'Old pages moved to Draft: ', 'smile-community' ) . implode( ', ', $done ) : __( 'No old pages to retire', 'smile-community' ) ) );
			break;
		case 'reading':
			$home    = get_page_by_path( 'home' );
			$stories = get_page_by_path( 'stories' );
			$privacy = get_page_by_path( 'privacy-policy' );
			if ( $home ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $home->ID );
			}
			if ( $stories ) {
				update_option( 'page_for_posts', $stories->ID );
			}
			if ( $privacy ) {
				update_option( 'wp_page_for_privacy_policy', $privacy->ID );
			}
			if ( '' === (string) get_option( 'permalink_structure' ) ) {
				update_option( 'permalink_structure', '/%postname%/' );
			}
			flush_rewrite_rules();
			update_option( 'blog_public', $opts['noindex'] ? '0' : '1' );
			wp_send_json_success( array( 'message' => $opts['noindex'] ? __( 'Home page set; search engines discouraged (staging)', 'smile-community' ) : __( 'Home page set; search engines allowed', 'smile-community' ) ) );
			break;
		case 'menus':
			wp_send_json_success( array( 'message' => smile_demo_import_menus() ) );
			break;
		case 'logo':
			$map = smile_demo_image_map();
			if ( isset( $map['logo'] ) && ! get_theme_mod( 'custom_logo' ) ) {
				set_theme_mod( 'custom_logo', $map['logo'] );
				wp_send_json_success( array( 'message' => __( 'Logo set', 'smile-community' ) ) );
			}
			wp_send_json_success( array( 'message' => __( 'Logo unchanged', 'smile-community' ) ) );
			break;
		case 'kit':
			wp_send_json_success( array( 'message' => smile_demo_import_kit() ) );
			break;
		case 'finish':
			if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			}
			update_option( 'smile_demo_imported', time() );
			wp_send_json_success( array( 'message' => __( 'All done', 'smile-community' ) ) );
			break;
	}
	wp_send_json_error( array( 'message' => __( 'Unknown step.', 'smile-community' ) ) );
}
add_action( 'wp_ajax_smile_demo_step', 'smile_demo_ajax_step' );

/**
 * Import one photo.
 *
 * @param string $key Image key.
 * @return string Message.
 */
function smile_demo_import_image( $key ) {
	$map = smile_demo_image_map();
	if ( isset( $map[ $key ] ) ) {
		return sprintf( /* translators: %s key */ __( 'Photo already in library: %s', 'smile-community' ), $key );
	}
	$img = null;
	foreach ( smile_demo_json( 'images.json' ) as $i ) {
		if ( $i['key'] === $key ) {
			$img = $i;
		}
	}
	if ( ! $img ) {
		return __( 'Photo not in manifest', 'smile-community' );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $img['source'], 60 );
	if ( is_wp_error( $tmp ) ) {
		return 'FAILED ' . $img['filename'] . ': ' . $tmp->get_error_message();
	}
	$id = media_handle_sideload( array( 'name' => $img['filename'], 'tmp_name' => $tmp ), 0, $img['title'] );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		return 'FAILED ' . $img['filename'] . ': ' . $id->get_error_message();
	}
	update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $img['alt'] ) );
	update_post_meta( $id, '_smile_img_key', $img['key'] );
	update_post_meta( $id, '_smile_source', esc_url_raw( $img['source'] ) );
	update_post_meta( $id, '_smile_consent', 'pending-review' );
	wp_update_post( array( 'ID' => $id, 'post_content' => '', 'post_excerpt' => '' ) );
	return sprintf( /* translators: %s file */ __( 'Photo imported: %s', 'smile-community' ), $img['filename'] );
}

/**
 * Replace smile-img:key placeholders in Elementor data.
 *
 * @param mixed $node   Data.
 * @param array $images key => ID.
 * @return mixed
 */
function smile_demo_swap_images( $node, $images ) {
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
			$node[ $k ] = smile_demo_swap_images( $v, $images );
		}
	}
	return $node;
}

/**
 * Create or refresh all pages.
 *
 * @param bool $overwrite Replace existing pages not built by the importer.
 * @return string
 */
function smile_demo_import_pages( $overwrite ) {
	$images  = smile_demo_image_map();
	$created = 0;
	$updated = 0;
	$kept    = array();
	foreach ( smile_demo_json( 'pages.json' ) as $page ) {
		$existing = get_page_by_path( $page['slug'] );
		$html     = isset( $page['html'] ) ? preg_replace_callback(
			'/smile-img:([a-z0-9-]+)/',
			function ( $m ) use ( $images ) {
				return isset( $images[ $m[1] ] ) ? (string) wp_get_attachment_url( $images[ $m[1] ] ) : '';
			},
			$page['html']
		) : '';
		$postarr  = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => wp_kses_post( $html ),
			'menu_order'   => isset( $page['order'] ) ? (int) $page['order'] : 0,
		);
		if ( $existing ) {
			$built = get_post_meta( $existing->ID, '_smile_setup', true );
			if ( ! $built && ! $overwrite ) {
				$kept[] = '/' . $page['slug'] . '/';
				continue;
			}
			if ( ! $built ) {
				update_post_meta( $existing->ID, '_smile_previous_content', $existing->post_content );
			}
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( wp_slash( $postarr ), true );
			++$updated;
		} else {
			$id = wp_insert_post( wp_slash( $postarr ), true );
			++$created;
		}
		if ( is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_smile_setup', '1' );
		if ( ! empty( $page['elementor'] ) ) {
			update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
			update_post_meta( $id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $id, '_elementor_template_type', 'wp-page' );
			update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( smile_demo_swap_images( $page['elementor'], $images ) ) ) );
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
	}
	$msg = sprintf( /* translators: 1 created 2 updated */ __( 'Pages: %1$d created, %2$d updated', 'smile-community' ), $created, $updated );
	if ( $kept ) {
		$msg .= '. ' . __( 'Kept existing (tick "Replace existing pages" to rebuild): ', 'smile-community' ) . implode( ', ', $kept );
	}
	return $msg;
}

/**
 * Build menus.
 *
 * @return string
 */
function smile_demo_import_menus() {
	$locs  = get_theme_mod( 'nav_menu_locations', array() );
	$names = array();
	foreach ( smile_demo_json( 'menus.json' ) as $menu ) {
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
		smile_demo_menu_items( $menu_id, $menu['items'], 0 );
		$locs[ $menu['location'] ] = $menu_id;
		$names[]                   = $menu['name'];
	}
	set_theme_mod( 'nav_menu_locations', $locs );
	return __( 'Menus built: ', 'smile-community' ) . implode( ', ', $names );
}

/**
 * Add menu items recursively.
 *
 * @param int   $menu_id Menu.
 * @param array $items   Items.
 * @param int   $parent  Parent item.
 */
function smile_demo_menu_items( $menu_id, $items, $parent ) {
	foreach ( $items as $item ) {
		$args = array(
			'menu-item-title'     => $item['title'],
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent,
		);
		$page = ! empty( $item['page'] ) ? get_page_by_path( $item['page'] ) : null;
		if ( $page ) {
			$args['menu-item-object-id'] = $page->ID;
			$args['menu-item-object']    = 'page';
			$args['menu-item-type']      = 'post_type';
		} else {
			$args['menu-item-url']  = home_url( isset( $item['url'] ) ? $item['url'] : '/' );
			$args['menu-item-type'] = 'custom';
		}
		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $item_id ) && ! empty( $item['children'] ) ) {
			smile_demo_menu_items( $menu_id, $item['children'], $item_id );
		}
	}
}

/**
 * Elementor global colours and fonts.
 *
 * @return string
 */
function smile_demo_import_kit() {
	$kit_id = (int) get_option( 'elementor_active_kit' );
	if ( ! $kit_id ) {
		return __( 'Elementor kit not found; global styles skipped', 'smile-community' );
	}
	$s = get_post_meta( $kit_id, '_elementor_page_settings', true );
	$s = is_array( $s ) ? $s : array();

	$s['system_colors']     = array(
		array( '_id' => 'primary', 'title' => 'Teal', 'color' => '#176B5B' ),
		array( '_id' => 'secondary', 'title' => 'Forest', 'color' => '#164B3F' ),
		array( '_id' => 'text', 'title' => 'Charcoal text', 'color' => '#24312D' ),
		array( '_id' => 'accent', 'title' => 'Sunshine', 'color' => '#F4C95D' ),
	);
	$s['custom_colors']     = array(
		array( '_id' => 'smcream', 'title' => 'Cream', 'color' => '#FFF9EF' ),
		array( '_id' => 'smsand', 'title' => 'Sand', 'color' => '#F6EEDC' ),
		array( '_id' => 'smmuted', 'title' => 'Muted grey-green', 'color' => '#5D6A64' ),
		array( '_id' => 'smwhite', 'title' => 'White', 'color' => '#FFFFFF' ),
	);
	$s['system_typography'] = array(
		array( '_id' => 'primary', 'title' => 'Headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Fraunces', 'typography_font_weight' => '600' ),
		array( '_id' => 'secondary', 'title' => 'Sub-headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '700' ),
		array( '_id' => 'text', 'title' => 'Body', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '400' ),
		array( '_id' => 'accent', 'title' => 'Buttons', 'typography_typography' => 'custom', 'typography_font_family' => 'Inter', 'typography_font_weight' => '700' ),
	);
	$s['container_width']   = array( 'unit' => 'px', 'size' => 1200 );
	$s['body_color']        = '#24312D';
	$s['link_normal_color'] = '#176B5B';
	update_post_meta( $kit_id, '_elementor_page_settings', $s );
	update_option( 'elementor_font_display', 'swap' );
	update_option( 'elementor_google_font', '1' );
	return __( 'Elementor colours and fonts applied', 'smile-community' );
}
