<?php
/**
 * Single post / page fallback (pages built in Elementor use the Elementor Full Width template instead).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

if ( is_singular( 'smile_job' ) ) {
	get_template_part( 'template-parts/job' );
	return;
}

while ( have_posts() ) :
	the_post();
	$smile_is_post = is_singular( 'post' );
	?>
	<main id="content" <?php post_class( 'smile-article' ); ?>>
		<header class="smile-article__header">
			<div class="smile-container smile-container--narrow">
				<?php smile_breadcrumbs(); ?>
				<?php if ( $smile_is_post ) : ?>
					<?php $smile_cats = get_the_category(); ?>
					<?php if ( $smile_cats ) : ?>
						<a class="smile-eyebrow" href="<?php echo esc_url( get_category_link( $smile_cats[0] ) ); ?>"><?php echo esc_html( $smile_cats[0]->name ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
				<?php the_title( '<h1 class="smile-article__title">', '</h1>' ); ?>
				<?php if ( $smile_is_post ) : ?>
					<p class="smile-article__meta">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span aria-hidden="true">&middot;</span>
						<?php echo esc_html( sprintf( /* translators: %d minutes */ _n( '%d min read', '%d min read', smile_reading_time(), 'smile-community' ), smile_reading_time() ) ); ?>
					</p>
				<?php endif; ?>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="smile-article__media smile-container">
				<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="smile-article__content smile-container smile-container--narrow smile-prose">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<nav class="smile-page-links" aria-label="' . esc_attr__( 'Article pages', 'smile-community' ) . '">', 'after' => '</nav>' ) ); ?>
		</div>

		<?php if ( $smile_is_post ) : ?>
			<div class="smile-container smile-container--narrow">
				<?php the_tags( '<p class="smile-tags">', ' ', '</p>' ); ?>
				<div class="smile-byline">
					<span class="smile-byline__mark" aria-hidden="true"><?php echo smile_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<p><strong><?php esc_html_e( 'Smile Community Centre', 'smile-community' ); ?></strong><?php esc_html_e( 'Stories from our home in Soweto, Kayole. Shared with care for every child’s privacy.', 'smile-community' ); ?></p>
				</div>
				<nav class="smile-post-nav" aria-label="<?php esc_attr_e( 'More stories', 'smile-community' ); ?>">
					<?php previous_post_link( '<span class="smile-post-nav__prev">%link</span>', '&larr; %title' ); ?>
					<?php next_post_link( '<span class="smile-post-nav__next">%link</span>', '%title &rarr;' ); ?>
				</nav>
			</div>
			<?php
			$smile_rel = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 3,
					'post__not_in'        => array( get_the_ID() ),
					'category__in'        => wp_get_post_categories( get_the_ID() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);
			if ( $smile_rel->have_posts() ) :
				?>
				<section class="smile-section smile-related-stories" aria-labelledby="smile-rel-title">
					<div class="smile-container">
						<h2 id="smile-rel-title"><?php esc_html_e( 'More stories', 'smile-community' ); ?></h2>
						<div class="smile-grid smile-grid--3">
							<?php
							while ( $smile_rel->have_posts() ) :
								$smile_rel->the_post();
								get_template_part( 'template-parts/content-card' );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</section>
			<?php endif; ?>
			<?php smile_cta_band(); ?>
		<?php endif; ?>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="smile-container smile-container--narrow"><?php comments_template(); ?></div>
		<?php endif; ?>
	</main>
	<?php
endwhile;
