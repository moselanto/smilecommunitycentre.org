<?php
/**
 * Story card used on archives and search.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
$smile_cats = get_the_category();
?>
<article <?php post_class( 'smile-card smile-card--story' ); ?>>
	<a class="smile-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php echo smile_story_image( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?>
		<?php if ( $smile_cats ) : ?><span class="smile-card__tag"><?php echo esc_html( $smile_cats[0]->name ); ?></span><?php endif; ?>
	</a>
	<div class="smile-card__body">
		<p class="smile-card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> &middot; <?php echo esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ); ?></p>
		<h2 class="smile-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="smile-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<span class="smile-card__more" aria-hidden="true"><?php esc_html_e( 'Read story', 'smile-community' ); ?> &rarr;</span>
	</div>
</article>
