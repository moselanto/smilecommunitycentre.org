<?php
/**
 * Single job page (Jobs post type).
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

while ( have_posts() ) :
	the_post();
	$smile_j     = smile_job( get_the_ID() );
	$smile_left  = smile_job_days_left( $smile_j['closing'] );
	$smile_email = $smile_j['apply_email'] ? $smile_j['apply_email'] : smile_setting( 'email', 'info@smilecommunitycentre.org' );
	$smile_subj  = sprintf( /* translators: 1 role 2 reference */ __( 'Application: %1$s%2$s', 'smile-community' ), get_the_title(), $smile_j['reference'] ? ' (' . $smile_j['reference'] . ')' : '' );
	$smile_apply = $smile_j['apply_url'] ? $smile_j['apply_url'] : 'mailto:' . antispambot( $smile_email ) . '?subject=' . rawurlencode( $smile_subj );
	$smile_page  = get_page_by_path( 'careers' );
	$smile_back  = $smile_page ? get_permalink( $smile_page ) : home_url( '/careers/' );
	$smile_url   = get_permalink();
	?>
	<main id="content" <?php post_class( 'smile-jobpage' ); ?>>
		<header class="smile-jobhero">
			<div class="smile-container">
				<nav class="smile-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'smile-community' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'smile-community' ); ?></a> <span aria-hidden="true">/</span> <a href="<?php echo esc_url( $smile_back ); ?>"><?php esc_html_e( 'Careers', 'smile-community' ); ?></a> <span aria-hidden="true">/</span> <span aria-current="page"><?php the_title(); ?></span></nav>
				<div class="smile-jobhero__tags">
					<span class="smile-job__pill"><?php echo esc_html( $smile_j['type_label'] ); ?></span>
					<?php if ( $smile_j['department'] ) : ?><span class="smile-jobhero__dept"><?php echo esc_html( $smile_j['department'] ); ?></span><?php endif; ?>
					<?php if ( !$smile_j['open'] ) : ?><span class="smile-jobhero__closed"><?php esc_html_e( 'Closed', 'smile-community' ); ?></span><?php endif; ?>
				</div>
				<?php the_title( '<h1 class="smile-jobhero__title">', '</h1>' ); ?>
				<ul class="smile-jobhero__meta">
					<li><?php echo smile_job_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( $smile_j['location'] ); ?></li>
					<?php if ( $smile_j['closing'] ) : ?><li><?php echo smile_job_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Apply by %s', 'smile-community' ), smile_job_date( $smile_j['closing'] ) ) ); ?></li><?php endif; ?>
					<li><?php echo smile_job_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Posted %s', 'smile-community' ), get_the_date( 'j F Y' ) ) ); ?></li>
				</ul>
			</div>
		</header>

		<div class="smile-container smile-jobpage__grid">
			<article class="smile-jobpage__body">
				<?php if ( !$smile_j['open'] ) : ?>
					<div class="smile-jobpage__notice" role="status"><strong><?php esc_html_e( 'This role is now closed.', 'smile-community' ); ?></strong> <?php esc_html_e( 'Thank you for your interest. See current openings or join our talent community to hear about future roles.', 'smile-community' ); ?> <a href="<?php echo esc_url( $smile_back ); ?>"><?php esc_html_e( 'View open roles', 'smile-community' ); ?></a></div>
				<?php endif; ?>
				<?php if ( has_excerpt() ) : ?><p class="smile-jobpage__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				<div class="smile-prose smile-jobpage__content"><?php the_content(); ?></div>

				<?php if ( $smile_j['open'] ) : ?>
					<section class="smile-jobpage__apply" id="apply" aria-labelledby="smile-apply-title">
						<h2 id="smile-apply-title"><?php esc_html_e( 'How to apply', 'smile-community' ); ?></h2>
						<?php if ( $smile_j['apply_url'] ) : ?>
							<p><?php esc_html_e( 'Apply through the link below. Have your CV and a short cover letter ready.', 'smile-community' ); ?></p>
						<?php else : ?>
							<p><?php echo esc_html( sprintf( /* translators: 1 email 2 subject */ __( 'Email your CV and a one-page cover letter to %1$s with the subject "%2$s".', 'smile-community' ), antispambot( $smile_email ), $smile_subj ) ); ?></p>
						<?php endif; ?>
						<?php if ( $smile_j['closing'] ) : ?><p><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Applications close at midnight on %s. We will contact shortlisted candidates only.', 'smile-community' ), smile_job_date( $smile_j['closing'] ) ) ); ?></p><?php endif; ?>
						<a class="smile-btn smile-btn--sun" href="<?php echo esc_url( $smile_apply ); ?>"<?php echo $smile_j['apply_url'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php esc_html_e( 'Apply for this role', 'smile-community' ); ?></a>
					</section>
				<?php endif; ?>

				<aside class="smile-jobpage__safe" aria-label="<?php esc_attr_e( 'Safer recruitment', 'smile-community' ); ?>">
					<span class="smile-jobpage__safe-icon" aria-hidden="true"><?php echo smile_job_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
					<div><h2><?php esc_html_e( 'Our commitment to safeguarding', 'smile-community' ); ?></h2><p><?php esc_html_e( 'Smile Community Centre is committed to keeping children safe. All appointments are subject to two satisfactory references, a Certificate of Good Conduct and agreement to our safeguarding policy and code of conduct. We never ask for money at any stage of recruitment.', 'smile-community' ); ?></p></div>
				</aside>
			</article>

			<aside class="smile-jobpage__side">
				<div class="smile-jobcard">
					<h2 class="smile-jobcard__title"><?php esc_html_e( 'Role at a glance', 'smile-community' ); ?></h2>
					<dl class="smile-jobcard__list">
						<div><dt><?php esc_html_e( 'Type', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['type_label'] ); ?></dd></div>
						<?php if ( $smile_j['department'] ) : ?><div><dt><?php esc_html_e( 'Team', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['department'] ); ?></dd></div><?php endif; ?>
						<div><dt><?php esc_html_e( 'Location', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['location'] ); ?></dd></div>
						<?php if ( $smile_j['hours'] ) : ?><div><dt><?php esc_html_e( 'Hours', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['hours'] ); ?></dd></div><?php endif; ?>
						<?php if ( $smile_j['salary'] ) : ?><div><dt><?php esc_html_e( 'Pay', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['salary'] ); ?></dd></div><?php endif; ?>
						<?php if ( $smile_j['start'] ) : ?><div><dt><?php esc_html_e( 'Start', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['start'] ); ?></dd></div><?php endif; ?>
						<?php if ( $smile_j['closing'] ) : ?><div><dt><?php esc_html_e( 'Closing date', 'smile-community' ); ?></dt><dd><?php echo esc_html( smile_job_date( $smile_j['closing'] ) ); ?><?php if ( $smile_j['open'] && null !== $smile_left && $smile_left <= 14 ) : ?> <span class="smile-jobcard__left"><?php echo esc_html( 0 === $smile_left ? __( 'today', 'smile-community' ) : sprintf( /* translators: %d days */ _n( '%d day left', '%d days left', $smile_left, 'smile-community' ), $smile_left ) ); ?></span><?php endif; ?></dd></div><?php endif; ?>
						<?php if ( $smile_j['reference'] ) : ?><div><dt><?php esc_html_e( 'Reference', 'smile-community' ); ?></dt><dd><?php echo esc_html( $smile_j['reference'] ); ?></dd></div><?php endif; ?>
					</dl>
					<?php if ( $smile_j['open'] ) : ?>
						<a class="smile-btn smile-btn--sun smile-jobcard__apply" href="<?php echo esc_url( $smile_apply ); ?>"<?php echo $smile_j['apply_url'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php esc_html_e( 'Apply for this role', 'smile-community' ); ?></a>
					<?php else : ?>
						<a class="smile-btn smile-btn--teal smile-jobcard__apply" href="<?php echo esc_url( $smile_back . '#talent' ); ?>"><?php esc_html_e( 'Join our talent community', 'smile-community' ); ?></a>
					<?php endif; ?>
					<div class="smile-jobcard__share">
						<span><?php esc_html_e( 'Share this role', 'smile-community' ); ?></span>
						<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( get_the_title() . ' - ' . $smile_url ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'smile-community' ); ?></a>
						<a href="<?php echo esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $smile_url ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'LinkedIn', 'smile-community' ); ?></a>
						<button type="button" class="smile-copy" data-smile-copy="<?php echo esc_url( $smile_url ); ?>"><?php esc_html_e( 'Copy link', 'smile-community' ); ?></button>
					</div>
				</div>
				<a class="smile-jobpage__back" href="<?php echo esc_url( $smile_back ); ?>">&larr; <?php esc_html_e( 'All open roles', 'smile-community' ); ?></a>
			</aside>
		</div>
	</main>
	<?php
endwhile;
