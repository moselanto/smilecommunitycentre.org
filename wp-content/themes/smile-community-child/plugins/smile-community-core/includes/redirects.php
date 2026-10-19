<?php
/**
 * 301 redirects from old URLs. Runs only when WordPress would otherwise show a 404,
 * so it can never hide a real page. Editable at Smile Centre > Redirects.
 *
 * Every address listed in the old site's sitemaps (smilecommunitycentre.org,
 * audited 19 Oct 2026: 28 posts, 16 pages, 12 categories) is covered here, so
 * nothing Google or other websites link to breaks when the new site replaces it.
 *
 * @package SmileCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Built-in redirect map from the old-site URL audit. Always active, so the old
 * addresses keep working even if the editable list is cleared. Rules saved at
 * Smile Centre > Redirects take precedence over these.
 *
 * Old category archives (/charity/, /children/ ...) are handled separately by
 * smile_core_category_redirect(), and old posts keep their slug, so they only
 * appear here when the post no longer exists on the new site.
 *
 * @return array old path => new path
 */
function smile_core_builtin_redirects() {
	$map = array(
		// Old pages replaced by new ones.
		'/blog/'                  => '/stories/',
		'/photos/'                => '/gallery/',
		'/sitemap/'               => '/',
		'/hiv-and-the-community/' => '/health-and-wellbeing/',
		'/education/'             => '/category/education/',

		// Old posts not carried over: closest matching new page.
		'/a-day-in-the-life-of-our-founder-margrets-work-routine-in-slums/' => '/our-founder/',
		'/did-you-know-children-account-for-half-of-the-worlds-extreme-poor/' => '/our-impact/',
		'/real-life-stories-from-smile-community-centre-childrens-home/' => '/stories/',
		'/what-vulnerability-feels-like/' => '/stories/',
		'/life-as-it-is/'                 => '/stories/',
		'/a-journey-cut-short-reflections-on-the-titanic-submarine-experience/' => '/stories/',
		'/empowering-kenyan-youth-the-significance-of-the-hustler-fund-in-informal-settlements/' => '/stories/',
		'/building-bridges-the-shared-commitment-of-comoros-and-kenya-in-supporting-orphaned-and-vulnerable-children/' => '/stories/',
		'/smile-community-centre-childrens-home-announcement-in-regards-to-the-fifteenth-15th-presidential-address-on-the-coronavirus-pandemic/' => '/category/announcements/',

		// Old SEOPress sitemap files: point to the WordPress sitemap.
		'/sitemaps.xml'             => '/wp-sitemap.xml',
		'/sitemap.xml'              => '/wp-sitemap.xml',
		'/sitemap_index.xml'        => '/wp-sitemap.xml',
		'/post-sitemap1.xml'        => '/wp-sitemap-posts-post-1.xml',
		'/page-sitemap1.xml'        => '/wp-sitemap-posts-page-1.xml',
		'/category-sitemap1.xml'    => '/wp-sitemap-taxonomies-category-1.xml',
	);

	/**
	 * Filter the built-in redirect map.
	 *
	 * @param array $map old path => new path.
	 */
	return (array) apply_filters( 'smile_core_builtin_redirects', $map );
}

/**
 * Default text for the editable list (kept for the Redirects screen and activation).
 *
 * @return string One rule per line: /old-path/ /new-path/
 */
function smile_core_default_redirects() {
	$lines = array();
	foreach ( smile_core_builtin_redirects() as $old => $new ) {
		$lines[] = $old . ' ' . $new;
	}
	return implode( "\n", $lines );
}

/**
 * Normalise a path into a lookup key.
 *
 * @param string $path Path.
 * @return string
 */
function smile_core_redirect_key( $path ) {
	return untrailingslashit( strtolower( (string) $path ) );
}

/**
 * Parse rules: built-in map first, then the editable list (which wins).
 *
 * @return array
 */
function smile_core_redirect_rules() {
	$rules = array();
	foreach ( smile_core_builtin_redirects() as $old => $new ) {
		$rules[ smile_core_redirect_key( $old ) ] = $new;
	}
	foreach ( explode( "\n", (string) get_option( 'smile_core_redirects', '' ) ) as $line ) {
		$parts = preg_split( '/\s+/', trim( $line ) );
		if ( count( $parts ) < 2 || 0 !== strpos( $parts[0], '/' ) ) {
			continue;
		}
		$rules[ smile_core_redirect_key( $parts[0] ) ] = $parts[1];
	}
	return $rules;
}

/**
 * Old site had no "category" base: /charity/, /charity/page/2/, /charity/feed/.
 * Send those to the matching category archive on this site.
 *
 * @param string $path Request path relative to the site root.
 * @return string Target URL or ''.
 */
function smile_core_category_redirect( $path ) {
	$segments = array_values( array_filter( explode( '/', strtolower( $path ) ), 'strlen' ) );
	if ( empty( $segments ) ) {
		return '';
	}
	$cat = get_category_by_slug( sanitize_title( $segments[0] ) );
	if ( ! $cat ) {
		return '';
	}
	$rest = array_slice( $segments, 1 );
	$tail = '';
	if ( 2 === count( $rest ) && 'page' === $rest[0] && ctype_digit( $rest[1] ) ) {
		$tail = 'page/' . (int) $rest[1] . '/';
	} elseif ( 1 === count( $rest ) && 'feed' === $rest[0] ) {
		$tail = 'feed/';
	} elseif ( ! empty( $rest ) ) {
		return '';
	}
	$link = get_category_link( $cat->term_id );
	if ( ! $link || is_wp_error( $link ) ) {
		return '';
	}
	$target = trailingslashit( $link ) . $tail;
	// Never redirect a URL to itself (e.g. if the category base is removed).
	$target_path = (string) wp_parse_url( $target, PHP_URL_PATH );
	$home_path   = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( smile_core_redirect_key( $home_path . $path ) === smile_core_redirect_key( $target_path ) ) {
		return '';
	}
	return $target;
}

/**
 * Old archive addresses that have no direct equivalent: old tag pages
 * (/tag/impact/, with /page/N/ or /feed/) and the old blog's later pages
 * (/blog/page/2/). Both go to Stories. Only runs on a 404, so a tag that
 * exists on the new site still opens normally.
 *
 * @param string $path Request path relative to the site root.
 * @return string Target URL or ''.
 */
function smile_core_archive_redirect( $path ) {
	$path = strtolower( $path );
	if ( preg_match( '#^/tag/[a-z0-9\-_%]+(/(page/\d+|feed))?/?$#', $path ) || preg_match( '#^/blog/page/\d+/?$#', $path ) ) {
		$stories = get_page_by_path( 'stories' );
		return $stories ? get_permalink( $stories ) : home_url( '/stories/' );
	}
	return '';
}

/**
 * Perform redirect on 404.
 */
function smile_core_maybe_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$path = (string) $path;
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( $home && '/' !== $home && 0 === strpos( $path, untrailingslashit( $home ) ) ) {
		$path = substr( $path, strlen( untrailingslashit( $home ) ) ); // Support sub-folder installs (staging).
	}
	$key = smile_core_redirect_key( $path );
	if ( '' === $key ) {
		return;
	}
	$rules  = smile_core_redirect_rules();
	$target = '';
	if ( isset( $rules[ $key ] ) ) {
		$target = $rules[ $key ];
		$target = 0 === strpos( $target, 'http' ) ? $target : home_url( $target );
	} else {
		$target = smile_core_category_redirect( $path );
	}
	if ( '' === $target ) {
		return;
	}
	wp_safe_redirect( $target, 301, 'Smile Community Core' );
	exit;
}
add_action( 'template_redirect', 'smile_core_maybe_redirect', 1 );

/**
 * Keep post addresses identical to the old site (/post-name/, not /2021/04/24/post-name/).
 * Runs once per core version for an administrator. Switch off with the
 * smile_core_postname_permalinks filter.
 */
function smile_core_postname_permalinks() {
	if ( ! current_user_can( 'manage_options' ) || ! apply_filters( 'smile_core_postname_permalinks', true ) ) {
		return;
	}
	if ( get_option( 'smile_core_permalinks_checked' ) === SMILE_CORE_VERSION ) {
		return;
	}
	update_option( 'smile_core_permalinks_checked', SMILE_CORE_VERSION );
	if ( '/%postname%/' !== (string) get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}
}
add_action( 'admin_init', 'smile_core_postname_permalinks' );

/**
 * Save redirects.
 */
function smile_core_save_redirects() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'smile-core' ), 403 );
	}
	check_admin_referer( 'smile_core_redirects' );
	$raw = isset( $_POST['smile_redirects'] ) ? sanitize_textarea_field( wp_unslash( $_POST['smile_redirects'] ) ) : '';
	update_option( 'smile_core_redirects', $raw );
	wp_safe_redirect( add_query_arg( array( 'page' => 'smile-redirects', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_smile_core_save_redirects', 'smile_core_save_redirects' );

/**
 * Render redirects screen.
 */
function smile_core_render_redirects() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Redirects from old addresses', 'smile-core' ); ?></h1>
		<p><?php esc_html_e( 'One rule per line: the old path, a space, then the new path or full URL. Rules only apply when the old address no longer exists, so they never hide a live page.', 'smile-core' ); ?></p>
		<p><?php esc_html_e( 'All addresses from the old website are already redirected automatically, including old category pages such as /charity/. Rules you add here take priority.', 'smile-core' ); ?></p>
		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'smile-core' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="smile_core_save_redirects" />
			<?php wp_nonce_field( 'smile_core_redirects' ); ?>
			<label class="screen-reader-text" for="smile-redirects"><?php esc_html_e( 'Redirect rules', 'smile-core' ); ?></label>
			<textarea id="smile-redirects" name="smile_redirects" class="large-text code" rows="18"><?php echo esc_textarea( (string) get_option( 'smile_core_redirects', '' ) ); ?></textarea>
			<?php submit_button( __( 'Save redirects', 'smile-core' ) ); ?>
		</form>
		<h2><?php esc_html_e( 'Built-in redirects', 'smile-core' ); ?></h2>
		<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:320px;overflow:auto;white-space:pre-wrap"><?php echo esc_html( smile_core_default_redirects() ); ?></pre>
	</div>
	<?php
}
