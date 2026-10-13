<?php
/**
 * Setup wizard: Appearance > Smile Setup.
 * Step 1 install plugins, step 2 import content, step 3 ready.
 * Opens automatically right after the theme is activated.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flag the redirect on activation.
 */
function smile_wizard_on_activate() {
	set_transient( 'smile_wizard_redirect', 1, 60 );
}
add_action( 'after_switch_theme', 'smile_wizard_on_activate' );

/**
 * Redirect once to the wizard.
 */
function smile_wizard_maybe_redirect() {
	if ( ! get_transient( 'smile_wizard_redirect' ) || wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_transient( 'smile_wizard_redirect' );
	if ( isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	wp_safe_redirect( admin_url( 'themes.php?page=smile-setup' ) );
	exit;
}
add_action( 'admin_init', 'smile_wizard_maybe_redirect' );

/**
 * Menu.
 */
function smile_wizard_menu() {
	add_theme_page( __( 'Smile Setup', 'smile-community' ), __( 'Smile Setup', 'smile-community' ), 'manage_options', 'smile-setup', 'smile_wizard_render' );
}
add_action( 'admin_menu', 'smile_wizard_menu' );

/**
 * Assets.
 *
 * @param string $hook Hook.
 */
function smile_wizard_assets( $hook ) {
	if ( 'appearance_page_smile-setup' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'smile-wizard', SMILE_THEME_URI . '/assets/admin/wizard.css', array(), SMILE_THEME_VERSION );
	wp_enqueue_script( 'smile-wizard', SMILE_THEME_URI . '/assets/admin/wizard.js', array(), SMILE_THEME_VERSION, true );
	wp_localize_script(
		'smile-wizard',
		'smileWizard',
		array(
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'smile_setup' ),
			'images'  => wp_list_pluck( smile_demo_json( 'images.json' ), 'key' ),
			'next2'   => admin_url( 'themes.php?page=smile-setup&step=content' ),
			'next3'   => admin_url( 'themes.php?page=smile-setup&step=ready' ),
			'i18n'    => array(
				'working'  => __( 'Working...', 'smile-community' ),
				'failed'   => __( 'Failed', 'smile-community' ),
				'reqFirst' => __( 'Install the required plugins before importing content.', 'smile-community' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'smile_wizard_assets' );

/**
 * Render.
 */
function smile_wizard_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$steps = array(
		'plugins' => __( 'Plugins', 'smile-community' ),
		'content' => __( 'Content', 'smile-community' ),
		'ready'   => __( 'Ready', 'smile-community' ),
	);
	$step  = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : 'plugins'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $steps[ $step ] ) ) {
		$step = 'plugins';
	}
	?>
	<div class="wrap smile-wizard">
		<header class="smile-wizard__head">
			<h1><?php esc_html_e( 'Welcome to the Smile Community Centre theme', 'smile-community' ); ?></h1>
			<p><?php esc_html_e( 'Three short steps and your website is ready.', 'smile-community' ); ?></p>
			<ol class="smile-wizard__steps">
				<?php
				$i = 0;
				foreach ( $steps as $key => $label ) :
					++$i;
					?>
					<li class="<?php echo $key === $step ? 'is-current' : ''; ?>"><a href="<?php echo esc_url( admin_url( 'themes.php?page=smile-setup&step=' . $key ) ); ?>"><span><?php echo esc_html( $i ); ?></span> <?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</header>

		<div class="smile-wizard__card">
		<?php if ( 'plugins' === $step ) : ?>
			<h2><?php esc_html_e( 'Step 1: Install plugins', 'smile-community' ); ?></h2>
			<p><?php esc_html_e( 'Required plugins are ticked. Recommended ones are optional but advised.', 'smile-community' ); ?></p>
			<table class="widefat smile-wizard__plugins">
				<thead><tr><th class="check-column"></th><th><?php esc_html_e( 'Plugin', 'smile-community' ); ?></th><th><?php esc_html_e( 'Type', 'smile-community' ); ?></th><th><?php esc_html_e( 'Status', 'smile-community' ); ?></th></tr></thead>
				<tbody>
				<?php
				$labels = array(
					'missing'  => __( 'Not installed', 'smile-community' ),
					'inactive' => __( 'Installed, not active', 'smile-community' ),
					'update'   => __( 'Update available', 'smile-community' ),
					'active'   => __( 'Active', 'smile-community' ),
				);
				foreach ( smile_theme_plugins() as $slug => $p ) :
					$status = smile_theme_plugin_status( $slug );
					?>
					<tr data-slug="<?php echo esc_attr( $slug ); ?>" data-status="<?php echo esc_attr( $status ); ?>">
						<td><input type="checkbox" id="smile-p-<?php echo esc_attr( $slug ); ?>" <?php checked( 'active' !== $status && ( $p['required'] || 'update' === $status ) ); ?> <?php disabled( 'active', $status ); ?>></td>
						<td><label for="smile-p-<?php echo esc_attr( $slug ); ?>"><strong><?php echo esc_html( $p['name'] ); ?></strong></label><br><span class="description"><?php echo esc_html( $p['why'] ); ?></span></td>
						<td><?php echo $p['required'] ? '<span class="smile-tag smile-tag--req">' . esc_html__( 'Required', 'smile-community' ) . '</span>' : '<span class="smile-tag">' . esc_html__( 'Recommended', 'smile-community' ) . '</span>'; ?></td>
						<td class="smile-wizard__status" aria-live="polite"><?php echo esc_html( $labels[ $status ] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="smile-wizard__actions">
				<button type="button" class="button button-primary button-hero" id="smile-install-plugins"><?php esc_html_e( 'Install & activate', 'smile-community' ); ?></button>
				<a class="button button-hero" href="<?php echo esc_url( admin_url( 'themes.php?page=smile-setup&step=content' ) ); ?>" id="smile-next-content"><?php esc_html_e( 'Next: content', 'smile-community' ); ?></a>
			</p>

		<?php elseif ( 'content' === $step ) : ?>
			<h2><?php esc_html_e( 'Step 2: Import the website content', 'smile-community' ); ?></h2>
			<?php if ( ! smile_theme_required_ready() ) : ?>
				<div class="notice notice-error inline"><p><?php esc_html_e( 'Install the required plugins first.', 'smile-community' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php?page=smile-setup&step=plugins' ) ); ?>"><?php esc_html_e( 'Go to step 1', 'smile-community' ); ?></a></p></div>
			<?php else : ?>
				<p><?php echo esc_html( sprintf( /* translators: 1 pages 2 photos */ __( 'This creates %1$d pages built with Elementor, imports %2$d approved photos from smilecommunitycentre.org (renamed, location data removed, with alt text), builds the menus and sets the home page.', 'smile-community' ), count( smile_demo_json( 'pages.json' ) ), count( smile_demo_json( 'images.json' ) ) ) ); ?></p>
				<?php if ( get_option( 'smile_demo_imported' ) ) : ?>
					<div class="notice notice-info inline"><p><?php esc_html_e( 'Content was imported before. Running again refreshes the pages it created and reuses photos already in the Media Library.', 'smile-community' ); ?></p></div>
				<?php endif; ?>
				<div class="notice notice-warning inline"><p><?php esc_html_e( 'Take a backup first if this site already has content.', 'smile-community' ); ?></p></div>
				<fieldset class="smile-wizard__opts">
					<label><input type="checkbox" id="smile-o-images" checked> <?php esc_html_e( 'Import photographs', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-enhance" checked> <?php esc_html_e( 'Enhance photos: enlarge small originals, correct colour and contrast, sharpen. The untouched original is kept alongside.', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-posts" checked> <?php esc_html_e( 'Bring across the stories (blog posts) from smilecommunitycentre.org, with their photos. Off-topic posts are skipped; posts naming children are imported as drafts for review.', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-cleanup" checked> <?php esc_html_e( 'Remove the WordPress sample post and sample page', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-title" checked> <?php esc_html_e( 'Set the site title to "Smile Community Centre"', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-overwrite"> <?php esc_html_e( 'Replace existing pages with the same address (old content kept as a revision). Tick this if the site is a copy of the old website.', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-retire" checked> <?php esc_html_e( 'Move replaced old pages (Photos, Blog, Sitemap, HIV/AIDS) to Draft so redirects work', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-kit" checked> <?php esc_html_e( 'Apply Smile colours and fonts to Elementor', 'smile-community' ); ?></label>
					<label><input type="checkbox" id="smile-o-noindex" checked> <?php esc_html_e( 'Hide from search engines (keep ticked on staging, untick at launch)', 'smile-community' ); ?></label>
				</fieldset>
				<div class="smile-wizard__progress" hidden>
					<div class="smile-wizard__bar"><span style="width:0"></span></div>
					<p class="smile-wizard__now" aria-live="polite"></p>
					<details><summary><?php esc_html_e( 'Details', 'smile-community' ); ?></summary><ol class="smile-wizard__log"></ol></details>
				</div>
				<p class="smile-wizard__actions">
					<button type="button" class="button button-primary button-hero" id="smile-import"><?php esc_html_e( 'Import content', 'smile-community' ); ?></button>
				</p>
			<?php endif; ?>

		<?php else : ?>
			<h2><?php esc_html_e( 'Step 3: Your website is ready', 'smile-community' ); ?></h2>
			<?php $home = (int) get_option( 'page_on_front' ); ?>
			<p class="smile-wizard__actions">
				<a class="button button-primary button-hero" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View website', 'smile-community' ); ?></a>
				<?php if ( $home && did_action( 'elementor/loaded' ) ) : ?>
					<a class="button button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $home . '&action=elementor' ) ); ?>"><?php esc_html_e( 'Edit home page with Elementor', 'smile-community' ); ?></a>
				<?php endif; ?>
			</p>
			<h3><?php esc_html_e( 'Finish off', 'smile-community' ); ?></h3>
			<ol>
				<li><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=smile_slider' ) ); ?>"><?php esc_html_e( 'Review the home slider', 'smile-community' ); ?></a> <?php esc_html_e( '(photos, headlines and buttons).', 'smile-community' ); ?></li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=smile-centre' ) ); ?>"><?php esc_html_e( 'Check centre details', 'smile-community' ); ?></a> <?php esc_html_e( '(phones, M-Pesa, where form emails go).', 'smile-community' ); ?></li>
				<li><?php esc_html_e( 'Set up email delivery in WP Mail SMTP, then send a test from the Contact form.', 'smile-community' ); ?></li>
				<li><a href="<?php echo esc_url( admin_url( 'edit.php?post_status=draft&post_type=post' ) ); ?>"><?php esc_html_e( 'Review the draft stories', 'smile-community' ); ?></a> <?php esc_html_e( '(they name children or adults; remove names or keep them unpublished).', 'smile-community' ); ?></li>
				<li><?php esc_html_e( 'Elementor > Settings > Advanced: load Google Fonts locally.', 'smile-community' ); ?></li>
				<li><?php esc_html_e( 'At launch: untick "Discourage search engines" in Settings > Reading.', 'smile-community' ); ?></li>
			</ol>
		<?php endif; ?>
		</div>
	</div>
	<?php
}


/**
 * Prompt to apply the latest page designs after a theme update.
 */
function smile_design_update_notice() {
	if ( ! current_user_can( 'manage_options' ) || ! get_option( 'smile_demo_imported' ) ) {
		return;
	}
	if ( get_option( 'smile_design_version' ) === SMILE_THEME_VERSION ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_smile-setup' === $screen->id ) {
		return;
	}
	echo '<div class="notice notice-info" style="border-left-color:#176B5B;padding:12px 14px"><p style="font-size:14px"><strong>' . esc_html( sprintf( /* translators: %s version */ __( 'Smile theme %s: new page designs are ready.', 'smile-community' ), SMILE_THEME_VERSION ) ) . '</strong> ' . esc_html__( 'Apply them to update the home page, inner pages, menus and photos. Pages you created yourself are not touched.', 'smile-community' ) . '</p><p><a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=smile-setup&step=content&apply=1' ) ) . '">' . esc_html__( 'Apply latest design', 'smile-community' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'smile_design_update_notice' );
