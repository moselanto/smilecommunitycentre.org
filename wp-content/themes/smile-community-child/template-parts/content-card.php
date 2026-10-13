<?php
/**
 * Story card used on archives and search.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'smile-card smile-card--story' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="smile-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'smile-card', array( 'alt' => '' ) ); ?>
		</a>
	<?php endif; ?>
	<div class="smile-card__body">
		<p class="smile-card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<h2 class="smile-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="smile-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
	</div>
</article>
