<?php
/**
 * Stories & News archive (blog index, categories, tags, dates).
 *
 * Editorial layout: masthead with search, sticky topic bar, lead story with
 * "also new" list, story grid with a support card, and pagination.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_is_index = is_home();
$smile_title    = $smile_is_index ? '' : wp_strip_all_tags( get_the_archive_title() );
if ( is_category() ) {
	$smile_title = smile_cat_label( single_cat_title( '', false ) );
}
$smile_total  = (int) $GLOBALS['wp_query']->found_posts;
$smile_paged  = max( 1, (int) get_query_var( 'paged' ) );
$smile_cats   = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'exclude'    => array( (int) get_option( 'default_category' ) ),
	)
);
$smile_blog   = get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/stories/' );
$smile_lead   = ( 1 === $smile_paged && $smile_total > 3 );
?>
<main id="content" class="smile-archive smile-stories">
	<header class="smile-stories__hero">
		<div class="smile-container smile-stories__hero-inner">
			<div class="smile-stories__intro">
				<?php smile_breadcrumbs(); ?>
				<p class="smile-stories__kicker"><span></span><?php echo $smile_is_index ? esc_html__( 'The Smile journal', 'smile-community' ) : esc_html__( 'Stories & News', 'smile-community' ); ?></p>
				<?php if ( $smile_is_index ) : ?>
					<h1><?php esc_html_e( 'Stories from', 'smile-community' ); ?> <em><?php esc_html_e( 'Smile', 'smile-community' ); ?></em></h1>
					<p class="smile-stories__lead"><?php esc_html_e( 'News, practical guides and everyday moments from our children\'s home in Soweto, Kayole.', 'smile-community' ); ?></p>
				<?php else : ?>
					<h1><?php echo esc_html( $smile_title ); ?></h1>
					<?php the_archive_description( '<div class="smile-stories__lead">', '</div>' ); ?>
				<?php endif; ?>
			</div>
			<div class="smile-stories__side">
				<?php get_search_form(); ?>
				<p class="smile-stories__count">
					<?php
					/* translators: %s: number of stories */
					echo esc_html( sprintf( _n( '%s story', '%s stories', $smile_total, 'smile-community' ), number_format_i18n( $smile_total ) ) );
					if ( $smile_cats && $smile_is_index ) {
						/* translators: %s: number of topics */
						echo ' <span aria-hidden="true">&middot;</span> ' . esc_html( sprintf( _n( '%s topic', '%s topics', count( $smile_cats ), 'smile-community' ), number_format_i18n( count( $smile_cats ) ) ) );
					}
					?>
				</p>
			</div>
		</div>
	</header>

	<?php if ( $smile_cats ) : ?>
		<?php
		$smile_main_cats = array_slice( $smile_cats, 0, 5 );
		$smile_more_cats = array_slice( $smile_cats, 5 );
		$smile_more_on   = false;
		foreach ( $smile_more_cats as $smile_mc ) {
			if ( is_category( $smile_mc->term_id ) ) {
				$smile_more_on = true;
			}
		}
		?>
		<nav class="smile-topics" aria-label="<?php esc_attr_e( 'Story topics', 'smile-community' ); ?>">
			<div class="smile-container smile-topics__inner">
				<p class="smile-topics__label"><?php esc_html_e( 'Browse by topic', 'smile-community' ); ?></p>
				<ul class="smile-topics__list">
					<li><a class="smile-topics__chip<?php echo $smile_is_index ? ' is-active' : ''; ?>" href="<?php echo esc_url( $smile_blog ); ?>"<?php echo $smile_is_index ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All stories', 'smile-community' ); ?></a></li>
					<?php
					foreach ( $smile_main_cats as $smile_cat ) :
						$smile_on = is_category( $smile_cat->term_id );
						?>
						<li><a class="smile-topics__chip<?php echo $smile_on ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $smile_cat ) ); ?>"<?php echo $smile_on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( smile_cat_label( $smile_cat->name ) ); ?><span class="smile-topics__n"><?php echo (int) $smile_cat->count; ?></span></a></li>
					<?php endforeach; ?>
					<?php if ( $smile_more_cats ) : ?>
						<li class="smile-topics__more-item">
							<details class="smile-topics__more">
								<summary class="smile-topics__chip smile-topics__chip--more<?php echo $smile_more_on ? ' is-active' : ''; ?>">
									<?php
									/* translators: %d: number of extra topics */
									echo esc_html( sprintf( __( 'More topics (%d)', 'smile-community' ), count( $smile_more_cats ) ) );
									?>
									<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</summary>
								<ul class="smile-topics__panel">
									<?php
									foreach ( $smile_more_cats as $smile_cat ) :
										$smile_on = is_category( $smile_cat->term_id );
										?>
										<li><a class="<?php echo $smile_on ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $smile_cat ) ); ?>"<?php echo $smile_on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( smile_cat_label( $smile_cat->name ) ); ?><span class="smile-topics__n"><?php echo (int) $smile_cat->count; ?></span></a></li>
									<?php endforeach; ?>
								</ul>
							</details>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</nav>
	<?php endif; ?>

	<div class="smile-container smile-stories__wrap">
		<?php if ( have_posts() ) : ?>
			<?php if ( $smile_lead ) : ?>
				<div class="smile-stories__head smile-stories__head--top">
					<h2><?php esc_html_e( 'Top stories', 'smile-community' ); ?></h2>
					<p>
						<?php
						/* translators: %s: date of the latest story */
						echo esc_html( sprintf( __( 'Updated %s', 'smile-community' ), get_the_date( 'j F Y', $GLOBALS['wp_query']->posts[0] ) ) );
						?>
					</p>
				</div>
				<section class="smile-lead" aria-label="<?php esc_attr_e( 'Latest stories', 'smile-community' ); ?>">
					<?php
					the_post();
					$smile_cat = smile_primary_cat( get_the_ID() );
					?>
					<article <?php post_class( 'smile-lead__main' ); ?>>
						<div class="smile-lead__media"><?php echo smile_story_image( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="smile-lead__body">
							<p class="smile-lead__chips"><span class="smile-lead__label"><?php esc_html_e( 'Latest story', 'smile-community' ); ?></span><?php if ( $smile_cat ) : ?><span class="smile-lead__cat"><?php echo esc_html( smile_cat_label( $smile_cat->name ) ); ?></span><?php endif; ?></p>
							<h2 class="smile-lead__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="smile-lead__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 34 ) ); ?></p>
							<p class="smile-story__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time><span aria-hidden="true">&middot;</span><?php echo esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ); ?></p>
							<span class="smile-lead__cta" aria-hidden="true"><?php esc_html_e( 'Read the story', 'smile-community' ); ?> &rarr;</span>
						</div>
					</article>
					<aside class="smile-lead__aside" aria-label="<?php esc_attr_e( 'Also new', 'smile-community' ); ?>">
						<p class="smile-lead__aside-title"><?php esc_html_e( 'Also new', 'smile-community' ); ?></p>
						<ol class="smile-lead__list">
							<?php
							$smile_i = 0;
							while ( $smile_i < 3 && have_posts() ) :
								the_post();
								++$smile_i;
								?>
								<?php $smile_icat = smile_primary_cat( get_the_ID() ); ?>
								<li <?php post_class( 'smile-lead__item' ); ?>>
									<div class="smile-lead__thumb"><?php echo smile_story_image( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
									<div class="smile-lead__text">
										<?php if ( $smile_icat ) : ?><span class="smile-lead__kicker"><?php echo esc_html( smile_cat_label( $smile_icat->name ) ); ?></span><?php endif; ?>
										<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
										<p class="smile-story__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></time><span aria-hidden="true">&middot;</span><?php echo esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ); ?></p>
									</div>
								</li>
							<?php endwhile; ?>
						</ol>
					</aside>
				</section>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="smile-stories__head">
					<h2><?php echo $smile_lead ? esc_html__( 'More stories', 'smile-community' ) : esc_html__( 'Stories', 'smile-community' ); ?></h2>
					<?php if ( $smile_paged > 1 ) : ?>
						<p>
							<?php
							/* translators: %d: page number */
							echo esc_html( sprintf( __( 'Page %d', 'smile-community' ), $smile_paged ) );
							?>
						</p>
					<?php endif; ?>
				</div>
				<div class="smile-stories__grid">
					<?php
					$smile_n = 0;
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content-card' );
						++$smile_n;
						if ( 3 === $smile_n && 1 === $smile_paged ) :
							?>
							<aside class="smile-support-card" aria-label="<?php esc_attr_e( 'Support Smile', 'smile-community' ); ?>">
								<p class="smile-support-card__kicker"><?php esc_html_e( 'Be part of the next story', 'smile-community' ); ?></p>
								<p class="smile-support-card__title"><?php esc_html_e( 'Every story here starts with a child who is safe, fed and in school.', 'smile-community' ); ?></p>
								<div class="smile-support-card__row">
									<a class="smile-btn smile-btn--sun" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php esc_html_e( 'Donate Now', 'smile-community' ); ?></a>
									<?php $smile_gi = get_page_by_path( 'get-involved' ); ?>
									<?php if ( $smile_gi ) : ?><a class="smile-support-card__link" href="<?php echo esc_url( get_permalink( $smile_gi ) ); ?>"><?php esc_html_e( 'Other ways to help', 'smile-community' ); ?> &rarr;</a><?php endif; ?>
								</div>
							</aside>
							<?php
						endif;
					endwhile;
					?>
				</div>
			<?php endif; ?>

			<?php
			the_posts_pagination(
				array(
					'mid_size'           => 1,
					'prev_text'          => '&larr; ' . __( 'Newer', 'smile-community' ),
					'next_text'          => __( 'Older', 'smile-community' ) . ' &rarr;',
					'screen_reader_text' => __( 'More stories', 'smile-community' ),
					'class'              => 'smile-pager',
				)
			);
			?>
		<?php else : ?>
			<div class="smile-stories__empty">
				<h2><?php esc_html_e( 'No stories here yet', 'smile-community' ); ?></h2>
				<p><?php esc_html_e( 'Try another topic, or search all stories.', 'smile-community' ); ?></p>
				<a class="smile-btn smile-btn--teal" href="<?php echo esc_url( $smile_blog ); ?>"><?php esc_html_e( 'See all stories', 'smile-community' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
	<section class="smile-touch" aria-labelledby="smile-touch-title">
		<div class="smile-container smile-touch__inner">
			<div>
				<p class="smile-touch__kicker"><?php esc_html_e( 'Stay close to Smile', 'smile-community' ); ?></p>
				<h2 id="smile-touch-title" class="smile-touch__title"><?php esc_html_e( 'Follow everyday life at the centre', 'smile-community' ); ?></h2>
				<p class="smile-touch__text"><?php esc_html_e( 'Photos and updates from Soweto, Kayole, shared with care for the children\'s privacy.', 'smile-community' ); ?></p>
			</div>
			<div class="smile-touch__actions">
				<?php $smile_ig = smile_setting( 'instagram_url', 'https://www.instagram.com/smile_kenya_/' ); ?>
				<?php if ( $smile_ig ) : ?><a class="smile-btn smile-btn--sun" href="<?php echo esc_url( $smile_ig ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Follow on Instagram', 'smile-community' ); ?></a><?php endif; ?>
				<?php $smile_ct = get_page_by_path( 'contact-us' ); ?>
				<?php if ( $smile_ct ) : ?><a class="smile-btn smile-btn--ghost-light" href="<?php echo esc_url( get_permalink( $smile_ct ) ); ?>"><?php esc_html_e( 'Share a story idea', 'smile-community' ); ?></a><?php endif; ?>
			</div>
		</div>
	</section>
</main>
