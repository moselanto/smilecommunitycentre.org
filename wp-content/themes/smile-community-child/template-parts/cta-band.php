<?php
/**
 * Reusable closing call to action.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

$smile_volunteer = get_page_by_path( 'volunteer' );
?>
<section class="smile-cta-band" aria-labelledby="smile-cta-title">
	<div class="smile-container smile-cta-band__inner">
		<h2 id="smile-cta-title"><?php esc_html_e( 'Help a child stay safe, fed and in school', 'smile-community' ); ?></h2>
		<p><?php esc_html_e( 'Your gift, your time or your organisation\'s support makes daily care possible at Smile Community Centre.', 'smile-community' ); ?></p>
		<div class="smile-btn-row">
			<a class="smile-btn smile-btn--sun" href="<?php echo esc_url( smile_donate_url() ); ?>"><?php esc_html_e( 'Donate Now', 'smile-community' ); ?></a>
			<?php if ( $smile_volunteer ) : ?>
				<a class="smile-btn smile-btn--ghost-light" href="<?php echo esc_url( get_permalink( $smile_volunteer ) ); ?>"><?php esc_html_e( 'Volunteer With Us', 'smile-community' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
