<?php
/**
 * Floating WhatsApp chat: a small branded chat card that opens WhatsApp
 * with the visitor's message ready to send. No third-party scripts.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the chat widget in the footer.
 */
function smile_whatsapp_chat() {
	if ( is_admin() || ! get_theme_mod( 'smile_wa_chat', true ) ) {
		return;
	}
	$number = preg_replace( '/\D/', '', smile_setting( 'whatsapp', '254721318869' ) );
	if ( '' === $number ) {
		return;
	}
	$avatar = SMILE_THEME_URI . '/assets/images/brand/icon-180.png';
	$topics = array(
		__( 'I would like to donate', 'smile-community' ),
		__( 'I want to volunteer', 'smile-community' ),
		__( 'I would like to sponsor a child', 'smile-community' ),
		__( 'I would like to visit the centre', 'smile-community' ),
		__( 'I have a question', 'smile-community' ),
	);
	$wa_icon = '<svg viewBox="0 0 32 32" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 3.2A12.8 12.8 0 0 0 5 22.5L3.2 28.8l6.5-1.7A12.8 12.8 0 1 0 16 3.2zm0 23.3c-2 0-3.9-.5-5.5-1.5l-.4-.2-3.9 1 1-3.8-.2-.4A10.5 10.5 0 1 1 16 26.5zm5.8-7.9c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2l-1 1.2c-.2.2-.4.2-.7.1-.3-.2-1.4-.5-2.6-1.6-1-.9-1.6-1.9-1.8-2.2-.2-.3 0-.5.1-.7l.5-.6.3-.5c.1-.2 0-.4 0-.6l-1-2.4c-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.2 1.4 3.5c.2.2 2.4 3.6 5.7 5 .8.4 1.4.6 1.9.7.8.2 1.5.2 2.1.1.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>';
	?>
	<div class="smile-wa" data-smile-wa data-number="<?php echo esc_attr( $number ); ?>" data-open-text="<?php esc_attr_e( 'Online now, usually replies within minutes', 'smile-community' ); ?>" data-closed-text="<?php esc_attr_e( 'We will reply when the office opens', 'smile-community' ); ?>">
		<div class="smile-wa__teaser" hidden>
			<button type="button" class="smile-wa__teaser-close" aria-label="<?php esc_attr_e( 'Dismiss', 'smile-community' ); ?>">&times;</button>
			<p><strong><?php esc_html_e( 'Hello there', 'smile-community' ); ?></strong> <?php esc_html_e( 'Questions about giving, visiting or volunteering? Chat with us on WhatsApp.', 'smile-community' ); ?></p>
		</div>
		<section class="smile-wa__panel" id="smile-wa-panel" role="dialog" aria-modal="false" aria-labelledby="smile-wa-title" hidden>
			<header class="smile-wa__head">
				<span class="smile-wa__avatar"><img src="<?php echo esc_url( $avatar ); ?>" alt="" width="44" height="44" loading="lazy" decoding="async"><i class="smile-wa__presence" aria-hidden="true"></i></span>
				<span class="smile-wa__who">
					<strong id="smile-wa-title"><?php esc_html_e( 'Smile Community Centre', 'smile-community' ); ?></strong>
					<span class="smile-wa__status"><?php esc_html_e( 'Usually replies during office hours', 'smile-community' ); ?></span>
				</span>
				<button type="button" class="smile-wa__close" aria-label="<?php esc_attr_e( 'Close chat', 'smile-community' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></button>
			</header>
			<div class="smile-wa__body">
				<div class="smile-wa__bubble">
					<p><?php esc_html_e( 'Hello and welcome. Thank you for caring about the children at Smile Community Centre.', 'smile-community' ); ?></p>
					<p><?php esc_html_e( 'How can we help you today?', 'smile-community' ); ?></p>
					<span class="smile-wa__time" aria-hidden="true"></span>
				</div>
				<p class="smile-wa__label"><?php esc_html_e( 'Pick a topic or type your own message', 'smile-community' ); ?></p>
				<div class="smile-wa__chips">
					<?php foreach ( $topics as $topic ) : ?>
						<button type="button" class="smile-wa__chip"><?php echo esc_html( $topic ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>
			<form class="smile-wa__form" action="<?php echo esc_url( 'https://wa.me/' . $number ); ?>" method="get" target="_blank">
				<label class="screen-reader-text" for="smile-wa-text"><?php esc_html_e( 'Your message', 'smile-community' ); ?></label>
				<textarea id="smile-wa-text" name="text" rows="1" maxlength="800" placeholder="<?php esc_attr_e( 'Type a message', 'smile-community' ); ?>"></textarea>
				<button type="submit" class="smile-wa__send" aria-label="<?php esc_attr_e( 'Send on WhatsApp', 'smile-community' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3.4 20.4l17.5-7.5a1 1 0 000-1.8L3.4 3.6a.9.9 0 00-1.3 1l1.8 6.1 9 1.3-9 1.3-1.8 6.1a.9.9 0 001.3 1z"/></svg></button>
			</form>
			<p class="smile-wa__note"><?php esc_html_e( 'Opens WhatsApp. Please do not share private details about a child here.', 'smile-community' ); ?></p>
		</section>
		<button type="button" class="smile-wa__fab" aria-controls="smile-wa-panel" aria-expanded="false">
			<span class="smile-wa__fab-icon"><?php echo $wa_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
			<span class="smile-wa__fab-label"><?php esc_html_e( 'Chat with us', 'smile-community' ); ?></span>
		</button>
	</div>
	<?php
}
add_action( 'wp_footer', 'smile_whatsapp_chat', 6 );
