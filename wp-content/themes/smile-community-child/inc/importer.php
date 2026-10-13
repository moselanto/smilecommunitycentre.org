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
	if ( 0 === strpos( $step, 'enhance:' ) ) {
		wp_send_json_success( array( 'message' => smile_enhance_attachment( absint( substr( $step, 8 ) ) ) ) );
	}
	if ( 0 === strpos( $step, 'post:' ) ) {
		wp_send_json_success( array( 'message' => smile_demo_import_post( absint( substr( $step, 5 ) ) ) ) );
	}
	switch ( $step ) {
		case 'cleanup':
			$removed = array();
			$hello   = get_page_by_path( 'hello-world', OBJECT, 'post' );
			if ( $hello && ! get_post_meta( $hello->ID, '_smile_source_id', true ) ) {
				wp_delete_post( $hello->ID, true );
				$removed[] = 'Hello world post';
			}
			$sample = get_page_by_path( 'sample-page' );
			if ( $sample && ! get_post_meta( $sample->ID, '_smile_setup', true ) ) {
				wp_delete_post( $sample->ID, true );
				$removed[] = 'Sample Page';
			}
			$privacy_draft = get_posts( array( 'post_type' => 'page', 'post_status' => 'draft', 'name' => 'privacy-policy-2', 'fields' => 'ids' ) );
			foreach ( $privacy_draft as $pid ) {
				wp_delete_post( $pid, true );
			}
			wp_send_json_success( array( 'message' => $removed ? __( 'Removed WordPress sample content: ', 'smile-community' ) . implode( ', ', $removed ) : __( 'No sample content found', 'smile-community' ) ) );
			break;
		case 'sitetitle':
			update_option( 'blogname', "Smile Community Centre" );
			update_option( 'blogdescription', "Children's home in Soweto, Kayole, Nairobi" );
			wp_send_json_success( array( 'message' => __( 'Site title set to Smile Community Centre', 'smile-community' ) ) );
			break;
		case 'enhance-index':
			$queue = smile_enhance_queue();
			wp_send_json_success(
				array(
					'message'   => sprintf( /* translators: %d */ __( '%d photos to enhance', 'smile-community' ), count( $queue ) ),
					'add_steps' => array_map( function ( $id ) { return 'enhance:' . $id; }, $queue ),
				)
			);
			break;
		case 'slider':
			foreach ( smile_slider_defaults() as $n => $d ) {
				$id = smile_image_by_key( $d['img'] );
				if ( $id && ! get_theme_mod( 'smile_slide_' . $n . '_image' ) ) {
					set_theme_mod( 'smile_slide_' . $n . '_image', $id );
				}
			}
			wp_send_json_success( array( 'message' => __( 'Home slider photos set (edit in Customize > Smile: Home slider)', 'smile-community' ) ) );
			break;
		case 'tidy-stories':
			$fixed = 0;
			foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => 200, 'meta_key' => '_smile_source_id' ) ) as $story ) { // phpcs:ignore WordPress.DB.SlowDBQuery
				$t = smile_demo_fix_caps( $story->post_title );
				$e = smile_demo_clean_excerpt( $story->post_excerpt, $story->post_content );
				if ( $t !== $story->post_title || $e !== $story->post_excerpt ) {
					wp_update_post( array( 'ID' => $story->ID, 'post_title' => $t, 'post_excerpt' => $e ) );
					++$fixed;
				}
			}
			wp_send_json_success( array( 'message' => sprintf( /* translators: %d */ __( 'Tidied %d story titles and summaries', 'smile-community' ), $fixed ) ) );
			break;
		case 'posts-index':
			$ids = smile_demo_live_post_ids();
			if ( is_wp_error( $ids ) ) {
				wp_send_json_success( array( 'message' => 'FAILED reading posts from smilecommunitycentre.org: ' . $ids->get_error_message() ) );
			}
			wp_send_json_success(
				array(
					'message'   => sprintf( /* translators: %d */ __( 'Found %d stories to bring across', 'smile-community' ), count( $ids ) ),
					'add_steps' => array_map( function ( $id ) { return 'post:' . $id; }, $ids ),
				)
			);
			break;
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
			update_option( 'smile_design_version', SMILE_THEME_VERSION );
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
			'post_content' => wp_kses_post( smile_demo_fix_links( $html ) ),
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
			update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( smile_demo_fix_links( smile_demo_swap_images( $page['elementor'], $images ) ) ) ) );
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
		array( '_id' => 'primary', 'title' => 'Headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Newsreader', 'typography_font_weight' => '500' ),
		array( '_id' => 'secondary', 'title' => 'Sub-headings', 'typography_typography' => 'custom', 'typography_font_family' => 'Figtree', 'typography_font_weight' => '700' ),
		array( '_id' => 'text', 'title' => 'Body', 'typography_typography' => 'custom', 'typography_font_family' => 'Figtree', 'typography_font_weight' => '400' ),
		array( '_id' => 'accent', 'title' => 'Buttons', 'typography_typography' => 'custom', 'typography_font_family' => 'Figtree', 'typography_font_weight' => '700' ),
	);
	$s['container_width']   = array( 'unit' => 'px', 'size' => 1200 );
	$s['body_color']        = '#24312D';
	$s['link_normal_color'] = '#176B5B';
	update_post_meta( $kit_id, '_elementor_page_settings', $s );
	update_option( 'elementor_font_display', 'swap' );
	update_option( 'elementor_google_font', '1' );
	return __( 'Elementor colours and fonts applied', 'smile-community' );
}


/**
 * Live site used as the source of stories.
 *
 * @return string
 */
function smile_demo_live_site() {
	return apply_filters( 'smile_demo_live_site', 'https://smilecommunitycentre.org' );
}

/**
 * Story decisions from the content audit.
 *
 * @return array slug => 'skip' | 'draft'
 */
function smile_demo_post_rules() {
	return array(
		'a-journey-cut-short-reflections-on-the-titanic-submarine-experience'                                         => 'skip',
		'empowering-kenyan-youth-the-significance-of-the-hustler-fund-in-informal-settlements'                        => 'skip',
		'building-bridges-the-shared-commitment-of-comoros-and-kenya-in-supporting-orphaned-and-vulnerable-children' => 'skip',
		'smile-community-centre-childrens-home-announcement-in-regards-to-the-fifteenth-15th-presidential-address-on-the-coronavirus-pandemic' => 'skip',
		'did-you-know-children-account-for-half-of-the-worlds-extreme-poor'                                          => 'skip',
		'a-day-in-the-life-of-our-founder-margrets-work-routine-in-slums'                                            => 'draft',
		'real-life-stories-from-smile-community-centre-childrens-home'                                               => 'draft',
		'life-as-it-is'                                                                                              => 'draft',
		'what-vulnerability-feels-like'                                                                              => 'draft',
	);
}

/**
 * IDs of posts on the live site that should be brought across.
 *
 * @return int[]|WP_Error
 */
function smile_demo_live_post_ids() {
	$res = wp_remote_get( smile_demo_live_site() . '/wp-json/wp/v2/posts?per_page=100&_fields=id,slug', array( 'timeout' => 30 ) );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$rows = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $rows ) ) {
		return new WP_Error( 'smile_posts', 'Unexpected response' );
	}
	$rules = smile_demo_post_rules();
	$ids   = array();
	foreach ( $rows as $row ) {
		if ( isset( $rules[ $row['slug'] ] ) && 'skip' === $rules[ $row['slug'] ] ) {
			continue;
		}
		$ids[] = (int) $row['id'];
	}
	return $ids;
}

/**
 * Sideload a live-site image once (deduplicated by source URL).
 *
 * @param string $url      Source URL.
 * @param string $filename Neutral filename.
 * @param string $alt      Alt text.
 * @return int Attachment ID or 0.
 */
function smile_demo_sideload_once( $url, $filename, $alt ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_smile_source', 'meta_value' => esc_url_raw( $url ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $found ) {
		return (int) $found[0];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = download_url( $url, 45 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => $filename, 'tmp_name' => $tmp ), 0 );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.WP.AlternativeFunctions
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	update_post_meta( $id, '_smile_source', esc_url_raw( $url ) );
	update_post_meta( $id, '_smile_consent', 'pending-review' );
	wp_update_post( array( 'ID' => $id, 'post_content' => '', 'post_excerpt' => '' ) );
	return (int) $id;
}

/**
 * Bring one story across from the live site, with its images.
 *
 * @param int $live_id Post ID on the live site.
 * @return string
 */
function smile_demo_import_post( $live_id ) {
	$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'fields' => 'ids', 'posts_per_page' => 1, 'meta_key' => '_smile_source_id', 'meta_value' => $live_id ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( $existing ) {
		return sprintf( /* translators: %s title */ __( 'Story already imported: %s', 'smile-community' ), get_the_title( $existing[0] ) );
	}
	$res = wp_remote_get( smile_demo_live_site() . '/wp-json/wp/v2/posts/' . $live_id . '?_embed=wp:featuredmedia,wp:term', array( 'timeout' => 30 ) );
	if ( is_wp_error( $res ) ) {
		return 'FAILED story ' . $live_id . ': ' . $res->get_error_message();
	}
	$p = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $p ) || empty( $p['slug'] ) ) {
		return 'FAILED story ' . $live_id . ': unexpected response';
	}
	if ( get_page_by_path( $p['slug'], OBJECT, 'post' ) ) {
		return sprintf( 'Story with this address already exists, skipped: %s', $p['slug'] );
	}
	$rules   = smile_demo_post_rules();
	$status  = isset( $rules[ $p['slug'] ] ) && 'draft' === $rules[ $p['slug'] ] ? 'draft' : 'publish';
	$title   = smile_demo_fix_caps( wp_strip_all_tags( html_entity_decode( $p['title']['rendered'], ENT_QUOTES, 'UTF-8' ) ) );
	$content = (string) $p['content']['rendered'];
	$short   = substr( sanitize_title( $p['slug'] ), 0, 40 );
	$live    = preg_quote( wp_parse_url( smile_demo_live_site(), PHP_URL_HOST ), '#' );

	// Copy inline images into this site's Media Library.
	$n = 0;
	if ( preg_match_all( '#<img[^>]+>#i', $content, $tags ) ) {
		foreach ( $tags[0] as $tag ) {
			if ( ! preg_match( '#src="(https?://(?:www\.)?' . $live . '/wp-content/uploads/[^"]+)"#i', $tag, $m ) ) {
				continue;
			}
			$alt = preg_match( '#alt="([^"]*)"#i', $tag, $a ) ? html_entity_decode( $a[1], ENT_QUOTES, 'UTF-8' ) : '';
			$src = preg_replace( '#-\d+x\d+(\.[a-z]+)$#i', '$1', $m[1] ); // Prefer the original size.
			++$n;
			$ext = strtolower( pathinfo( wp_parse_url( $src, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$id  = smile_demo_sideload_once( $src, 'story-' . $short . '-' . $n . '.' . ( $ext ? $ext : 'jpg' ), $alt );
			if ( ! $id && $src !== $m[1] ) {
				$id = smile_demo_sideload_once( $m[1], 'story-' . $short . '-' . $n . '.' . ( $ext ? $ext : 'jpg' ), $alt );
			}
			if ( $id ) {
				$new_tag = preg_replace( '#\s(srcset|sizes)="[^"]*"#i', '', $tag );
				$new_tag = str_replace( $m[1], (string) wp_get_attachment_image_url( $id, 'large' ), $new_tag );
				$new_tag = preg_replace( '#wp-image-\d+#', 'wp-image-' . $id, $new_tag );
				$content = str_replace( $tag, $new_tag, $content );
			}
		}
	}
	// Point internal links at this site.
	$content = preg_replace( '#https?://(?:www\.)?' . $live . '/(?!wp-content)#i', trailingslashit( home_url() ), $content );

	$post_id = wp_insert_post(
		wp_slash(
			array(
				'post_type'     => 'post',
				'post_status'   => $status,
				'post_title'    => $title,
				'post_name'     => $p['slug'],
				'post_content'  => wp_kses_post( $content ),
				'post_excerpt'  => smile_demo_clean_excerpt( $p['excerpt']['rendered'], (string) $p['content']['rendered'] ),
				'post_date'     => $p['date'],
				'post_date_gmt' => isset( $p['date_gmt'] ) ? $p['date_gmt'] : '',
				'post_author'   => get_current_user_id(),
			)
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		return 'FAILED story ' . $title . ': ' . $post_id->get_error_message();
	}
	update_post_meta( $post_id, '_smile_source_id', $live_id );
	update_post_meta( $post_id, '_smile_source', esc_url_raw( $p['link'] ) );

	// Categories.
	$cats = array();
	if ( ! empty( $p['_embedded']['wp:term'][0] ) ) {
		foreach ( $p['_embedded']['wp:term'][0] as $term ) {
			if ( 'uncategorized' === $term['slug'] ) {
				continue;
			}
			$cats[] = (int) wp_create_category( html_entity_decode( $term['name'], ENT_QUOTES, 'UTF-8' ) );
		}
	}
	if ( ! $cats ) {
		$guide  = preg_match( '/^(how|what|why|best|debunking|meeting|the importance|the mental|5 things|is there)/i', $title );
		$cats[] = (int) wp_create_category( $guide ? 'Guides' : 'Stories of Hope' );
	}
	wp_set_post_categories( $post_id, array_filter( $cats ) );

	// Featured image (stock photos are left out on purpose).
	if ( ! empty( $p['_embedded']['wp:featuredmedia'][0]['source_url'] ) ) {
		$fm  = $p['_embedded']['wp:featuredmedia'][0];
		$url = $fm['source_url'];
		if ( false === stripos( $url, 'pexels' ) ) {
			$ext = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
			$id  = smile_demo_sideload_once( $url, 'story-' . $short . '-cover.' . ( $ext ? $ext : 'jpg' ), isset( $fm['alt_text'] ) ? $fm['alt_text'] : '' );
			if ( $id ) {
				set_post_thumbnail( $post_id, $id );
			}
		}
	}
	return ( 'draft' === $status ? __( 'Story imported as DRAFT for safeguarding review: ', 'smile-community' ) : __( 'Story imported: ', 'smile-community' ) ) . $title;
}


/**
 * Make site-relative links (/donate/) work when WordPress lives in a sub-folder
 * (e.g. staging at /smilescommunity/). Walks Elementor data and HTML strings.
 *
 * @param mixed $node Elementor data or HTML.
 * @return mixed
 */
function smile_demo_fix_links( $node ) {
	$home = untrailingslashit( home_url() );
	if ( is_string( $node ) ) {
		return preg_replace( '#href="/(?!/)#', 'href="' . $home . '/', $node );
	}
	if ( is_array( $node ) ) {
		if ( isset( $node['url'] ) && is_string( $node['url'] ) && 0 === strpos( $node['url'], '/' ) && 0 !== strpos( $node['url'], '//' ) && isset( $node['is_external'] ) ) {
			$node['url'] = $home . $node['url'];
		}
		foreach ( $node as $k => $v ) {
			$node[ $k ] = smile_demo_fix_links( $v );
		}
	}
	return $node;
}

/**
 * Title-case a heading that was typed in ALL CAPS.
 *
 * @param string $title Title.
 * @return string
 */
function smile_demo_fix_caps( $title ) {
	$letters = preg_replace( '/[^A-Za-z]/', '', $title );
	if ( strlen( $letters ) < 6 || strtoupper( $letters ) !== $letters ) {
		return $title;
	}
	$small = array( 'a', 'an', 'and', 'at', 'by', 'for', 'in', 'of', 'on', 'or', 'the', 'to', 'your', 'with' );
	$words = explode( ' ', mb_strtolower( $title ) );
	foreach ( $words as $i => $word ) {
		if ( 0 === $i || ! in_array( $word, $small, true ) ) {
			$words[ $i ] = mb_strtoupper( mb_substr( $word, 0, 1 ) ) . mb_substr( $word, 1 );
		}
	}
	return str_replace( array( "Children's", 'Children’s' ), array( "Children's", 'Children’s' ), implode( ' ', $words ) );
}

/**
 * A clean excerpt: the live site's excerpt, unless it is just image alt text.
 *
 * @param string $rendered_excerpt Excerpt HTML.
 * @param string $content          Post content HTML.
 * @return string
 */
function smile_demo_clean_excerpt( $rendered_excerpt, $content ) {
	$ex = trim( wp_strip_all_tags( html_entity_decode( $rendered_excerpt, ENT_QUOTES, 'UTF-8' ) ) );
	$ex = trim( preg_replace( '/\s*(Read More|\[&hellip;\]|\[…\]|…)\s*$/i', '', $ex ) );
	if ( str_word_count( $ex ) < 12 ) {
		$text = preg_replace( '#<figure.*?</figure>#is', '', $content );
		$text = preg_replace( '#<img[^>]*>#i', '', $text );
		$ex   = wp_trim_words( wp_strip_all_tags( html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) ), 32, '…' );
	}
	return $ex;
}
