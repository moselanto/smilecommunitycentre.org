<?php
/**
 * Search and AI crawler readiness.
 *
 * - robots.txt: explicit rules for search, user-requested and training crawlers
 *   (owner decision 11 Nov 2026: allow all, including GPTBot and Google-Extended),
 *   admin kept out, and the current sitemap address.
 * - /llms.txt: a plain-text guide to the site for AI tools (helpful, not a
 *   replacement for robots.txt, sitemaps or canonical tags).
 * - Feeds: X-Robots-Tag noindex, follow (Search Console listed 15 feed URLs as
 *   "Crawled - currently not indexed"; feeds are not pages to rank).
 *
 * Only verified facts are used (docs/SEO.md, management verification Nov 2026).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Address of the XML sitemap index for the active SEO plugin.
 *
 * @return string
 */
function smile_sitemap_url() {
	if ( class_exists( 'RankMath' ) ) {
		return home_url( '/sitemap_index.xml' );
	}
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return home_url( '/sitemaps.xml' );
	}
	return home_url( '/wp-sitemap.xml' );
}

/**
 * Crawlers named explicitly in robots.txt, grouped by purpose (documentation in
 * docs/crawler-access-matrix.md).
 *
 * @return array
 */
function smile_crawler_agents() {
	return apply_filters(
		'smile_crawler_agents',
		array(
			'Googlebot', 'Googlebot-Image', 'Bingbot', 'Applebot', 'DuckDuckBot',
			'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'Perplexity-User', 'Claude-SearchBot', 'Claude-User',
			'GPTBot', 'Google-Extended', 'ClaudeBot', 'Applebot-Extended', 'CCBot',
		)
	);
}

/**
 * Build the robots.txt body.
 *
 * @param string $output Existing output.
 * @param bool   $public Blog public setting.
 * @return string
 */
function smile_robots_txt( $output, $public ) {
	if ( ! $public ) {
		return $output; // Staging / discouraged: leave WordPress's block in place.
	}
	$rules  = "Disallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nDisallow: /?s=\nDisallow: /search/\n";
	$lines  = "# Smile Community Centre - search engines and AI assistants are welcome.\n";
	$lines .= "# Policy: docs/crawler-access-matrix.md in the website repository.\n\n";
	$lines .= "User-agent: *\n" . $rules . "\n";
	foreach ( smile_crawler_agents() as $agent ) {
		$lines .= 'User-agent: ' . $agent . "\n";
	}
	$lines .= $rules . "\n";
	$lines .= 'Sitemap: ' . smile_sitemap_url() . "\n";
	return $lines;
}
add_filter( 'robots_txt', 'smile_robots_txt', 999, 2 );

/**
 * Rank Math rewrites robots.txt from its own editor; keep the theme's version
 * authoritative so the policy lives in the repo.
 *
 * @param string $content Rank Math content.
 * @return string
 */
function smile_rank_math_robots( $content ) {
	return smile_robots_txt( $content, (bool) get_option( 'blog_public' ) );
}
add_filter( 'rank_math/robots_txt/content', 'smile_rank_math_robots', 999 );

/**
 * Feeds stay reachable for readers but are kept out of the index.
 */
function smile_feed_noindex() {
	if ( is_feed() && ! headers_sent() ) {
		header( 'X-Robots-Tag: noindex, follow', true );
	}
}
add_action( 'template_redirect', 'smile_feed_noindex', 0 );

/**
 * Plain-text body of /llms.txt.
 *
 * @return string
 */
function smile_llms_txt() {
	$u     = static function ( $slug ) {
		return home_url( '' === $slug ? '/' : '/' . $slug . '/' );
	};
	$phone = smile_setting( 'phone_primary', '0721 318 869' );
	$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
	$pages = array(
		'Key pages'          => array(
			array( 'About Smile Community Centre', 'about-us', 'Who we are, mission and history' ),
			array( 'Our founder', 'our-founder', 'Margret Nekesa Khaemba and how Smile began' ),
			array( 'Our programmes', 'our-programmes', 'Overview of all programmes' ),
			array( 'Our impact', 'our-impact', 'What support achieves' ),
			array( 'Safeguarding and child protection', 'safeguarding', 'How children are protected; how to report a concern' ),
			array( 'Contact us', 'contact-us', 'Location, phone, WhatsApp, email and visits' ),
			array( 'FAQ', 'faq-page', 'Common questions' ),
		),
		'Programmes'         => array(
			array( 'Child care and shelter', 'child-care-and-shelter', 'Safe care for children referred by children\'s officers, chiefs and police' ),
			array( 'Education programme', 'education-program', 'School fees, uniforms, books and learning materials' ),
			array( 'Feeding programme', 'feeding-program', 'Daily meals and community feeding when resources allow' ),
			array( 'Health and wellbeing', 'health-and-wellbeing', 'Access to healthcare, counselling and referrals' ),
			array( 'Sports and talent', 'sports', 'Football, basketball and activities' ),
		),
		'Ways to help'       => array(
			array( 'Donate', 'donate', 'M-Pesa, bank, online card and in-kind giving' ),
			array( 'Get involved', 'get-involved', 'All ways to support Smile' ),
			array( 'Volunteer', 'volunteer', 'Opportunities are considered periodically' ),
			array( 'Partner with us', 'partner-with-us', 'Companies, foundations, schools and groups' ),
		),
		'Stories and guides' => array(
			array( 'Stories & News', 'stories', 'Updates from the home and guides on supporting children in Kenya' ),
		),
	);
	$out  = "# Smile Community Centre\n\n";
	$out .= "> Smile Community Centre (Smile Community Centre Children's Home) is a Community Based Organisation (CBO) in Mzesa, Soweto, Kayole, Nairobi, Kenya. It cares for orphaned and vulnerable children and supports the surrounding community through shelter and care, education, daily meals, health and wellbeing, and sports. Founder: Margret Nekesa Khaemba.\n\n";
	$out .= "- Website: " . $u( '' ) . "\n- Phone and WhatsApp: " . $phone . "\n- Email: " . $email . "\n- Location: Mzesa, Soweto, Kayole, Nairobi, Kenya\n\n";
	$out .= "Children's privacy: this site never publishes children's names, case histories or identifying details. Please do not attempt to identify children from photographs.\n\n";
	foreach ( $pages as $section => $items ) {
		$out .= '## ' . $section . "\n\n";
		foreach ( $items as $item ) {
			$out .= '- [' . $item[0] . '](' . $u( $item[1] ) . '): ' . $item[2] . "\n";
		}
		$out .= "\n";
	}
	$out .= "## Optional\n\n- [XML sitemap](" . smile_sitemap_url() . "): every indexable page and story\n";
	return (string) apply_filters( 'smile_llms_txt', $out );
}

/**
 * Serve /llms.txt.
 */
function smile_serve_llms_txt() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$home = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( $home . '/llms.txt' !== $path || ! get_option( 'blog_public' ) ) {
		return;
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Cache-Control: public, max-age=3600' );
	echo smile_llms_txt(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text.
	exit;
}
add_action( 'init', 'smile_serve_llms_txt', 1 );
