<?php
/**
 * Search results.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
?>
<main id="content" class="smile-archive">
	<header class="smile-page-hero">
		<div class="smile-container">
			<?php smile_breadcrumbs(); ?>
			<h1><?php echo esc_html( sprintf( /* translators: %s search term */ __( 'Search results for "%s"', 'smile-community' ), get_search_query() ) ); ?></h1>
			<?php get_search_form(); ?>
		</div>
	</header>
	<div class="smile-container smile-section">
		<?php if ( have_posts() ) : ?>
			<div class="smile-grid smile-grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing matched your search. Try different words, or explore our programmes and stories below.', 'smile-community' ); ?></p>
		<?php endif; ?>
	</div>
	<?php smile_cta_band(); ?>
</main>
