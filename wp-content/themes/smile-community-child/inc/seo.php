<?php
/**
 * Search engine optimisation: one connected schema.org graph (JSON-LD) per page,
 * sensible indexing rules, and fallback meta tags when no SEO plugin is active.
 *
 * Graph (stable @id values, so Google sees one organisation across the site):
 *   #organization  NGO (Smile Community Centre)  <- founder, address, contact, logo
 *   #website       WebSite (+ site search)
 *   <url>#webpage  WebPage / AboutPage / ContactPage / CollectionPage
 *   <url>#breadcrumb BreadcrumbList
 *   <url>#article  BlogPosting (stories)
 *
 * Only facts already published on the site are used. Nothing is invented:
 * no ratings, reviews, registration numbers or statistics.
 *
 * Off switch: add_filter( 'smile_seo_schema', '__return_false' );
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */

/**
 * Is a dedicated SEO plugin handling titles and descriptions?
 *
 * @return bool
 */
function smile_seo_plugin_active() {
	return defined( 'SEOPRESS_VERSION' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * SEOPress "Knowledge Graph" already prints an Organization; avoid a duplicate.
 *
 * @return bool
 */
function smile_seo_plugin_prints_org() {
	$social = get_option( 'seopress_social_option_name' );
	return is_array( $social ) && ! empty( $social['seopress_social_knowledge_enable'] );
}

/**
 * Site base URL with trailing slash.
 *
 * @return string
 */
function smile_seo_home() {
	return trailingslashit( home_url( '/' ) );
}

/**
 * Canonical URL of the current view.
 *
 * @return string
 */
function smile_seo_current_url() {
	if ( is_singular() ) {
		return (string) get_permalink();
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return (string) get_term_link( get_queried_object() );
	}
	return smile_seo_home();
}

/**
 * Short description of the current view (SEO plugin value first, then excerpt).
 *
 * @return string
 */
function smile_seo_description() {
	$resolved = smile_seo_resolve( 'desc' );
	if ( '' !== $resolved ) {
		return $resolved;
	}
	$id   = get_queried_object_id();
	$desc = '';
	if ( $id && ( is_singular() || is_home() ) ) {
		$desc = (string) get_post_meta( $id, '_seopress_titles_desc', true );
		if ( '' === $desc && is_singular( 'post' ) ) {
			$desc = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 30, '' );
		}
	}
	if ( '' === $desc && ( is_front_page() || ! $id ) ) {
		$desc = __( 'Smile Community Centre Children\'s Home in Soweto, Kayole, Nairobi gives orphaned and vulnerable children shelter, daily meals, education and care. Donate, volunteer or partner with us.', 'smile-community' );
	}
	if ( '' === $desc && ( is_category() || is_tag() ) ) {
		$desc = wp_strip_all_tags( term_description() );
	}
	return trim( preg_replace( '/\s+/', ' ', $desc ) );
}

/* -------------------------------------------------------------------------
 * Entities
 * ---------------------------------------------------------------------- */

/**
 * The organisation. Facts come from Centre details (Smile Centre > Centre details)
 * with the published defaults as fallback.
 *
 * @return array
 */
function smile_seo_organization() {
	$home  = smile_seo_home();
	$phone = smile_setting( 'phone_primary', '0721 318 869' );
	$intl  = preg_replace( '/[^0-9+]/', '', $phone );
	if ( 0 === strpos( $intl, '0' ) ) {
		$intl = '+254' . substr( $intl, 1 );
	}
	$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
	$same  = array_values( array_filter( wp_list_pluck( smile_social_profiles(), 'url' ) ) );

	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = function_exists( 'smile_brand_logo_url' ) && apply_filters( 'smile_use_brand_logo', true ) ? smile_brand_logo_url() : ( $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '' );

	$org = array(
		'@type'         => array( 'NGO', 'Organization' ),
		'@id'           => $home . '#organization',
		'name'          => __( 'Smile Community Centre', 'smile-community' ),
		'alternateName' => array( 'Smile Community Centre Children\'s Home', 'Smile Children\'s Home Kayole' ),
		'url'           => $home,
		'description'   => __( 'A children\'s home and community organisation in Soweto, Kayole, Nairobi, Kenya, giving orphaned and vulnerable children shelter, daily meals, education and care since 2009.', 'smile-community' ),
		'slogan'        => __( 'A safe haven in the heart of Soweto, Kayole', 'smile-community' ),
		'foundingDate'  => '2009',
		'founder'       => array(
			'@type' => 'Person',
			'@id'   => $home . '#founder',
			'name'  => __( 'Margret Nekesa Khaemba', 'smile-community' ),
		),
		'address'       => array(
			'@type'               => 'PostalAddress',
			'streetAddress'       => 'Mzesa, Soweto, Kayole (near Donholm Phase 8)',
			'addressLocality'     => 'Nairobi',
			'addressRegion'       => 'Nairobi County',
			'postOfficeBoxNumber' => '156',
			'postalCode'          => '00518',
			'addressCountry'      => 'KE',
		),
		'areaServed'    => array(
			array( '@type' => 'City', 'name' => 'Nairobi' ),
			array( '@type' => 'Country', 'name' => 'Kenya' ),
		),
		'telephone'     => $intl,
		'email'         => $email,
		'contactPoint'  => array(
			array(
				'@type'             => 'ContactPoint',
				'contactType'       => 'customer support',
				'telephone'         => $intl,
				'email'             => $email,
				'areaServed'        => 'KE',
				'availableLanguage' => array( 'English', 'Swahili' ),
			),
		),
		'knowsAbout'    => array( 'Children\'s home', 'Orphan care', 'Child protection', 'Education support', 'School fees support', 'Feeding programme', 'Community development', 'Volunteering in Kenya' ),
		'potentialAction' => array(
			'@type'  => 'DonateAction',
			'name'   => __( 'Donate to Smile Community Centre', 'smile-community' ),
			'target' => esc_url_raw( smile_donate_url() ),
			'recipient' => array( '@id' => $home . '#organization' ),
		),
	);
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	if ( $logo_url ) {
		$org['logo']  = array(
			'@type'   => 'ImageObject',
			'@id'     => $home . '#logo',
			'url'     => $logo_url,
			'caption' => 'Smile Community Centre',
		);
		$org['image'] = array( '@id' => $home . '#logo' );
	}
	return apply_filters( 'smile_seo_organization', $org );
}

/**
 * The website.
 *
 * @return array
 */
function smile_seo_website() {
	$home = smile_seo_home();
	return array(
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => 'Smile Community Centre',
		'description'     => __( 'Children\'s home in Nairobi, Kenya', 'smile-community' ),
		'inLanguage'      => 'en-KE',
		'publisher'       => array( '@id' => $home . '#organization' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $home . '?s={search_term_string}',
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Page type by slug.
 *
 * @return string
 */
function smile_seo_page_type() {
	if ( is_home() || is_category() || is_tag() || is_search() ) {
		return 'CollectionPage';
	}
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$map  = array(
			'about-us'     => 'AboutPage',
			'our-founder'  => 'AboutPage',
			'our-goals'    => 'AboutPage',
			'contact-us'   => 'ContactPage',
			'faq-page'     => 'FAQPage',
			'gallery'      => 'CollectionPage',
		);
		if ( isset( $map[ $slug ] ) ) {
			return 'faq-page' === $slug ? 'WebPage' : $map[ $slug ]; // FAQ rich results are limited to authoritative sites; plain WebPage is accurate.
		}
	}
	return 'WebPage';
}

/**
 * Breadcrumb trail matching the visible breadcrumbs.
 *
 * @return array list of [name, url]
 */
function smile_seo_trail() {
	$trail = array( array( __( 'Home', 'smile-community' ), smile_seo_home() ) );
	if ( is_front_page() ) {
		return array();
	}
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( is_singular( 'post' ) || is_category() || is_tag() ) {
		if ( $blog_id ) {
			$trail[] = array( get_the_title( $blog_id ), get_permalink( $blog_id ) );
		}
		if ( is_singular( 'post' ) ) {
			$trail[] = array( wp_strip_all_tags( get_the_title() ), get_permalink() );
		} else {
			$trail[] = array( single_term_title( '', false ), smile_seo_current_url() );
		}
	} elseif ( is_home() && $blog_id ) {
		$trail[] = array( get_the_title( $blog_id ), get_permalink( $blog_id ) );
	} elseif ( is_page() ) {
		$id = get_queried_object_id();
		foreach ( array_reverse( get_post_ancestors( $id ) ) as $parent ) {
			$trail[] = array( get_the_title( $parent ), get_permalink( $parent ) );
		}
		$trail[] = array( wp_strip_all_tags( get_the_title( $id ) ), get_permalink( $id ) );
	} else {
		return array();
	}
	return $trail;
}

/**
 * Build the graph for the current view.
 *
 * @return array
 */
function smile_seo_graph() {
	$home  = smile_seo_home();
	$url   = smile_seo_current_url();
	$graph = array();

	if ( ! smile_seo_plugin_prints_org() ) {
		$graph[] = smile_seo_organization();
	}
	$graph[] = smile_seo_website();

	$page = array(
		'@type'      => smile_seo_page_type(),
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => wp_strip_all_tags( wp_get_document_title() ),
		'isPartOf'   => array( '@id' => $home . '#website' ),
		'about'      => array( '@id' => $home . '#organization' ),
		'inLanguage' => 'en-KE',
	);
	$desc = smile_seo_description();
	if ( $desc ) {
		$page['description'] = $desc;
	}
	if ( is_singular() ) {
		$page['datePublished'] = get_post_time( 'c', true );
		$page['dateModified']  = get_post_modified_time( 'c', true );
		$thumb = get_the_post_thumbnail_url( null, 'large' );
		if ( $thumb ) {
			$page['primaryImageOfPage'] = array( '@type' => 'ImageObject', 'url' => $thumb );
		}
	}
	$trail = smile_seo_trail();
	if ( $trail ) {
		$page['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	}
	$graph[] = $page;

	if ( $trail ) {
		$items = array();
		foreach ( $trail as $i => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $crumb[0],
				'item'     => $crumb[1],
			);
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $items,
		);
	}

	if ( is_singular( 'post' ) ) {
		$article = array(
			'@type'            => 'BlogPosting',
			'@id'              => $url . '#article',
			'headline'         => mb_substr( wp_strip_all_tags( get_the_title() ), 0, 110 ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'datePublished'    => get_post_time( 'c', true ),
			'dateModified'     => get_post_modified_time( 'c', true ),
			'author'           => array( '@id' => $home . '#organization' ),
			'publisher'        => array( '@id' => $home . '#organization' ),
			'inLanguage'       => 'en-KE',
		);
		$cats = get_the_category();
		if ( $cats ) {
			$article['articleSection'] = wp_list_pluck( $cats, 'name' );
		}
		$thumb = get_the_post_thumbnail_url( null, 'large' );
		if ( $thumb ) {
			$article['image'] = $thumb;
		}
		if ( $desc ) {
			$article['description'] = $desc;
		}
		$graph[] = $article;
	}

	return apply_filters( 'smile_seo_graph', $graph );
}

/**
 * Print the JSON-LD graph.
 */
function smile_seo_print_schema() {
	if ( is_admin() || is_404() || ! apply_filters( 'smile_seo_schema', true ) ) {
		return;
	}
	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => smile_seo_graph(),
	);
	echo "\n" . '<script type="application/ld+json" class="smile-schema">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded, tags hex-escaped.
}
add_action( 'wp_head', 'smile_seo_print_schema', 30 );

/* -------------------------------------------------------------------------
 * Fallback meta (only when no SEO plugin is active)
 * ---------------------------------------------------------------------- */

/**
 * Meta description, Open Graph and Twitter card fallback.
 */
function smile_seo_fallback_meta() {
	if ( smile_seo_plugin_handles_meta() || is_admin() ) {
		return;
	}
	$desc  = smile_seo_description();
	$url   = smile_seo_current_url();
	$title = wp_get_document_title();
	$img   = is_singular() ? get_the_post_thumbnail_url( null, 'large' ) : '';
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	echo '<meta property="og:locale" content="en_KE">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:site_name" content="Smile Community Centre">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'smile_seo_fallback_meta', 1 );

/* -------------------------------------------------------------------------
 * Indexing rules
 * ---------------------------------------------------------------------- */

/**
 * Keep thin pages out of Google: search results, tag archives and paginated
 * comment pages are "noindex, follow" (links are still followed).
 *
 * @param array $robots Robots directives.
 * @return array
 */
function smile_seo_robots( $robots ) {
	if ( is_search() || is_tag() || is_author() || is_date() || is_attachment() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	if ( ! isset( $robots['noindex'] ) || ! $robots['noindex'] ) {
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = '-1';
	}
	return $robots;
}
add_filter( 'wp_robots', 'smile_seo_robots' );

/**
 * Attachment pages are thin: send them to the file's parent post (or the image).
 */
function smile_seo_attachment_redirect() {
	if ( ! is_attachment() ) {
		return;
	}
	$parent = wp_get_post_parent_id( get_queried_object_id() );
	wp_safe_redirect( $parent ? get_permalink( $parent ) : wp_get_attachment_url( get_queried_object_id() ), 301 );
	exit;
}
add_action( 'template_redirect', 'smile_seo_attachment_redirect', 5 );


/* -------------------------------------------------------------------------
 * Keep the theme's SEO titles and descriptions up to date
 * ---------------------------------------------------------------------- */

/**
 * Once per theme version, copy the SEO title and description from demo/pages.json
 * into SEOPress for the pages the setup wizard created. A value is only replaced
 * when it is empty or still one the theme wrote earlier (demo/seo-history.json),
 * so anything the centre has typed in by hand is never overwritten.
 */
function smile_seo_sync_meta() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'smile_seo_synced' ) === SMILE_THEME_VERSION ) {
		return;
	}
	$pages = json_decode( (string) file_get_contents( SMILE_THEME_DIR . '/demo/pages.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	$hist  = json_decode( (string) file_get_contents( SMILE_THEME_DIR . '/demo/seo-history.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	if ( ! is_array( $pages ) ) {
		return;
	}
	$old_titles = isset( $hist['titles'] ) ? (array) $hist['titles'] : array();
	$old_descs  = isset( $hist['descs'] ) ? (array) $hist['descs'] : array();
	foreach ( $pages as $page ) {
		if ( empty( $page['slug'] ) ) {
			continue;
		}
		$post = get_page_by_path( $page['slug'] );
		if ( ! $post || '1' !== get_post_meta( $post->ID, '_smile_setup', true ) ) {
			continue;
		}
		foreach ( array( '_seopress_titles_title' => array( 'seo_title', $old_titles ), '_seopress_titles_desc' => array( 'seo_desc', $old_descs ) ) as $meta => $cfg ) {
			if ( empty( $page[ $cfg[0] ] ) ) {
				continue;
			}
			$current = (string) get_post_meta( $post->ID, $meta, true );
			if ( '' === $current || in_array( $current, $cfg[1], true ) ) {
				update_post_meta( $post->ID, $meta, sanitize_text_field( $page[ $cfg[0] ] ) );
			}
		}
	}
	update_option( 'smile_seo_synced', SMILE_THEME_VERSION, false );
}
add_action( 'admin_init', 'smile_seo_sync_meta' );

/**
 * Without an SEO plugin, use the same titles for the browser tab and Google.
 *
 * @param string $title Title.
 * @return string
 */
function smile_seo_document_title( $title ) {
	$t = smile_seo_resolve( 'title' );
	return '' !== $t ? $t : $title;
}
add_filter( 'pre_get_document_title', 'smile_seo_document_title', 99 );


/* -------------------------------------------------------------------------
 * Title and description for every page, story and topic
 * ---------------------------------------------------------------------- */

/**
 * Is SEOPress (or another SEO plugin) actively printing titles and meta descriptions?
 *
 * @return bool
 */
function smile_seo_plugin_handles_meta() {
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		$toggle = get_option( 'seopress_toggle' );
		return is_array( $toggle ) && ! empty( $toggle['toggle-titles'] );
	}
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Theme titles and descriptions keyed by page slug, from demo/pages.json.
 *
 * @return array slug => [title, desc]
 */
function smile_seo_theme_map() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map  = array();
	$file = SMILE_THEME_DIR . '/demo/pages.json';
	$rows = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
	foreach ( (array) $rows as $row ) {
		if ( ! empty( $row['slug'] ) ) {
			$map[ $row['slug'] ] = array( (string) ( $row['seo_title'] ?? '' ), (string) ( $row['seo_desc'] ?? '' ) );
		}
	}
	return $map;
}

/**
 * Older theme-written values (safe to replace).
 *
 * @param string $field title|desc.
 * @return array
 */
function smile_seo_old_values( $field ) {
	static $hist = null;
	if ( null === $hist ) {
		$hist = json_decode( (string) file_get_contents( SMILE_THEME_DIR . '/demo/seo-history.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
		$hist = is_array( $hist ) ? $hist : array();
	}
	$key = 'title' === $field ? 'titles' : 'descs';
	return isset( $hist[ $key ] ) ? (array) $hist[ $key ] : array();
}

/**
 * Shorten to a limit on a word boundary.
 *
 * @param string $text  Text.
 * @param int    $limit Characters.
 * @return string
 */
function smile_seo_clip( $text, $limit ) {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' ) ) ) );
	if ( mb_strlen( $text ) <= $limit ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, $limit - 1 );
	$cut = preg_replace( '/\s+\S*$/u', '', $cut );
	return rtrim( $cut, ' ,;:-' ) . '…';
}

/**
 * Best title or description for the current view.
 *
 * Order: a value typed by hand in SEOPress wins; then the theme's keyword-mapped
 * value for its pages; then a pattern for stories and topics.
 *
 * @param string $field title|desc.
 * @return string Empty when WordPress should use its default.
 */
function smile_seo_resolve( $field ) {
	if ( is_admin() || is_feed() ) {
		return '';
	}
	$meta_key = 'title' === $field ? '_seopress_titles_title' : '_seopress_titles_desc';
	$site     = 'Smile Community Centre';

	// Pages (including the front page and the Stories page).
	$page_id = 0;
	if ( is_front_page() && 'page' === get_option( 'show_on_front' ) ) {
		$page_id = (int) get_option( 'page_on_front' );
	} elseif ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );
	} elseif ( is_page() ) {
		$page_id = get_queried_object_id();
	}
	if ( $page_id ) {
		$custom = trim( (string) get_post_meta( $page_id, $meta_key, true ) );
		if ( '' !== $custom && ! in_array( $custom, smile_seo_old_values( $field ), true ) && false === strpos( $custom, '%%' ) ) {
			return $custom;
		}
		$slug = get_post_field( 'post_name', $page_id );
		if ( is_front_page() ) {
			$slug = 'home';
		}
		$map = smile_seo_theme_map();
		if ( isset( $map[ $slug ] ) ) {
			$value = 'title' === $field ? $map[ $slug ][0] : $map[ $slug ][1];
			if ( '' !== $value ) {
				return $value;
			}
		}
		if ( 'title' === $field ) {
			return smile_seo_clip( get_the_title( $page_id ) . ' | ' . $site, 60 );
		}
		return smile_seo_clip( get_post_field( 'post_excerpt', $page_id ) ? get_post_field( 'post_excerpt', $page_id ) : __( 'Smile Community Centre Children\'s Home in Soweto, Kayole, Nairobi, Kenya.', 'smile-community' ), 158 );
	}

	// Stories.
	if ( is_singular( 'post' ) ) {
		$id     = get_queried_object_id();
		$custom = trim( (string) get_post_meta( $id, $meta_key, true ) );
		if ( '' !== $custom && false === strpos( $custom, '%%' ) ) {
			return $custom;
		}
		if ( 'title' === $field ) {
			$title = wp_strip_all_tags( get_the_title( $id ) );
			return mb_strlen( $title . ' | Smile' ) <= 60 ? $title . ' | Smile' : smile_seo_clip( $title, 60 );
		}
		$excerpt = get_post_field( 'post_excerpt', $id );
		if ( '' === trim( (string) $excerpt ) ) {
			$excerpt = get_post_field( 'post_content', $id );
		}
		return smile_seo_clip( strip_shortcodes( (string) $excerpt ), 158 );
	}

	// Topics.
	if ( is_category() ) {
		$name = function_exists( 'smile_cat_label' ) ? smile_cat_label( single_cat_title( '', false ) ) : single_cat_title( '', false );
		if ( 'title' === $field ) {
			return smile_seo_clip( $name . ' | Stories from Smile Community Centre', 60 );
		}
		$desc = wp_strip_all_tags( term_description() );
		/* translators: %s topic */
		return smile_seo_clip( '' !== trim( $desc ) ? $desc : sprintf( __( 'Stories and guides about %s from Smile Community Centre Children\'s Home in Soweto, Kayole, Nairobi, Kenya.', 'smile-community' ), mb_strtolower( $name ) ), 158 );
	}

	return '';
}

/**
 * Feed the same values to SEOPress, so its title tag, meta description and
 * social tags all match.
 *
 * @param string $value SEOPress value.
 * @return string
 */
function smile_seo_seopress_title( $value ) {
	$t = smile_seo_resolve( 'title' );
	return '' !== $t ? $t : $value;
}
add_filter( 'seopress_titles_title', 'smile_seo_seopress_title', 99 );

/**
 * SEOPress meta description.
 *
 * @param string $value SEOPress value.
 * @return string
 */
function smile_seo_seopress_desc( $value ) {
	$d = smile_seo_resolve( 'desc' );
	return '' !== $d ? $d : $value;
}
add_filter( 'seopress_titles_desc', 'smile_seo_seopress_desc', 99 );
