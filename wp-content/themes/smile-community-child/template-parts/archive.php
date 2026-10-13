<?php
/**
 * Stories & News archive (blog index, categories, tags, dates).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_title = is_home() ? single_post_title( '', false ) : wp_strip_all_tags( get_the_archive_title() );
if ( ! $smile_title ) {
	$smile_title = __( 'Stories & News', 'smile-community' );
}
?>
<main id="content" class="smile-archive">
	<header class="smile-page-hero smile-page-hero--archive">
		<div class="smile-container">
			<?php smile_breadcrumbs(); ?>
			<h1><?php echo esc_html( $smile_title ); ?></h1>
			<?php the_archive_description( '<div class="smile-page-hero__lead">', '</div>' ); ?>
			<div class="smile-archive__tools">
				<?php get_search_form(); ?>
				<?php
				$smile_cats = get_categories( array( 'hide_empty' => true ) );
				if ( $smile_cats ) :
					?>
					<nav aria-label="<?php esc_attr_e( 'Story categories', 'smile-community' ); ?>">
						<ul class="smile-chips">
							<?php foreach ( $smile_cats as $smile_cat ) : ?>
								<li><a class="smile-chip<?php echo is_category( $smile_cat->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $smile_cat ) ); ?>"><?php echo esc_html( $smile_cat->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<div class="smile-container smile-section">
		<?php if ( have_posts() ) : ?>
			<?php
			$smile_first = ! is_paged();
			if ( $smile_first && have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'smile-feature' ); ?>>
					<a class="smile-feature__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo smile_story_image( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<div class="smile-feature__body">
						<p class="smile-eyebrow"><?php esc_html_e( 'Latest story', 'smile-community' ); ?></p>
						<h2 class="smile-feature__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="smile-card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &middot; <?php echo esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ); ?></p>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></p>
						<a class="smile-btn smile-btn--teal" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read the story', 'smile-community' ); ?></a>
					</div>
				</article>
			<?php endif; ?>
			<div class="smile-grid smile-grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content-card' );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'Newer', 'smile-community' ), 'next_text' => __( 'Older', 'smile-community' ) ) ); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No stories have been published here yet.', 'smile-community' ); ?></p>
		<?php endif; ?>
	</div>
</main>
