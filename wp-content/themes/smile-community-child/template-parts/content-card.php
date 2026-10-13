<?php
/**
 * Story card used on archives and search.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;
$smile_cat = smile_primary_cat( get_the_ID() );
?>
<article <?php post_class( 'smile-card smile-card--story smile-story' ); ?>>
	<div class="smile-story__media">
		<?php echo smile_story_image( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?>
		<?php if ( $smile_cat ) : ?><span class="smile-story__tag"><?php echo esc_html( smile_cat_label( $smile_cat->name ) ); ?></span><?php endif; ?>
	</div>
	<div class="smile-story__body">
		<p class="smile-story__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></time><span aria-hidden="true">&middot;</span><?php echo esc_html( sprintf( /* translators: %d */ __( '%d min read', 'smile-community' ), smile_reading_time() ) ); ?></p>
		<h3 class="smile-story__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p class="smile-story__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
		<span class="smile-story__more" aria-hidden="true"><?php esc_html_e( 'Read story', 'smile-community' ); ?><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
	</div>
</article>
