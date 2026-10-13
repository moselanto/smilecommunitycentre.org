<?php
/**
 * Careers: Jobs post type, job details, open-roles listing, single job page,
 * Google for Jobs structured data and the Careers page sections.
 *
 * Staff add a role under Jobs > Add New Job. A role shows on the Careers page
 * while it is published and its closing date has not passed; after that it
 * stays online marked "closed" and drops off the list automatically.
 *
 * @package SmileCommunity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Employment types: key => label (keys match Google's JobPosting values).
 *
 * @return array
 */
function smile_job_types() {
	return array(
		'FULL_TIME'  => __( 'Full-time', 'smile-community' ),
		'PART_TIME'  => __( 'Part-time', 'smile-community' ),
		'CONTRACTOR' => __( 'Contract', 'smile-community' ),
		'TEMPORARY'  => __( 'Temporary', 'smile-community' ),
		'INTERN'     => __( 'Internship / attachment', 'smile-community' ),
		'VOLUNTEER'  => __( 'Volunteer role', 'smile-community' ),
	);
}

/**
 * Job detail fields: key => [label, type, hint].
 *
 * @return array
 */
function smile_job_fields() {
	return array(
		'type'       => array( __( 'Employment type', 'smile-community' ), 'select', '' ),
		'department' => array( __( 'Team or area', 'smile-community' ), 'text', __( 'For example: Child care, Education, Kitchen, Administration', 'smile-community' ) ),
		'location'   => array( __( 'Location', 'smile-community' ), 'text', __( 'Default: Soweto, Kayole, Nairobi', 'smile-community' ) ),
		'hours'      => array( __( 'Working hours', 'smile-community' ), 'text', __( 'For example: Monday to Friday, 8am to 5pm', 'smile-community' ) ),
		'salary'     => array( __( 'Pay (optional)', 'smile-community' ), 'text', __( 'For example: KES 25,000 to 30,000 per month. Leave empty to hide.', 'smile-community' ) ),
		'start'      => array( __( 'Start date (optional)', 'smile-community' ), 'text', __( 'For example: January 2027, or As soon as possible', 'smile-community' ) ),
		'closing'    => array( __( 'Closing date', 'smile-community' ), 'date', __( 'The role leaves the open list after this day. Leave empty to keep it open until you unpublish it.', 'smile-community' ) ),
		'reference'  => array( __( 'Reference (optional)', 'smile-community' ), 'text', __( 'For example: SCC/CC/2027/01', 'smile-community' ) ),
		'apply_email'=> array( __( 'Apply by email to', 'smile-community' ), 'email', __( 'Leave empty to use the centre email from Centre details.', 'smile-community' ) ),
		'apply_url'  => array( __( 'Or apply through this link (optional)', 'smile-community' ), 'url', __( 'For example a Google Form. If filled, the Apply button opens this link.', 'smile-community' ) ),
	);
}

/**
 * Register the Jobs post type.
 */
function smile_register_jobs() {
	register_post_type(
		'smile_job',
		array(
			'labels'          => array(
				'name'               => __( 'Jobs', 'smile-community' ),
				'singular_name'      => __( 'Job', 'smile-community' ),
				'menu_name'          => __( 'Jobs', 'smile-community' ),
				'add_new'            => __( 'Add New Job', 'smile-community' ),
				'add_new_item'       => __( 'Add New Job', 'smile-community' ),
				'edit_item'          => __( 'Edit Job', 'smile-community' ),
				'new_item'           => __( 'New Job', 'smile-community' ),
				'view_item'          => __( 'View Job', 'smile-community' ),
				'search_items'       => __( 'Search Jobs', 'smile-community' ),
				'not_found'          => __( 'No jobs yet. Click "Add New Job" when you have a role to advertise.', 'smile-community' ),
				'not_found_in_trash' => __( 'No jobs in the bin.', 'smile-community' ),
				'all_items'          => __( 'All Jobs', 'smile-community' ),
			),
			'public'          => true,
			'has_archive'     => false,
			'show_in_rest'    => true,
			'menu_position'   => 22,
			'menu_icon'       => 'dashicons-id-alt',
			'supports'        => array( 'title', 'editor', 'excerpt', 'revisions' ),
			'rewrite'         => array( 'slug' => 'careers/jobs', 'with_front' => false ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'smile_register_jobs' );

/**
 * Refresh permalinks once after the Jobs type first appears or changes.
 */
function smile_jobs_flush_once() {
	if ( get_option( 'smile_jobs_rewrite' ) !== '1' ) {
		flush_rewrite_rules( false );
		update_option( 'smile_jobs_rewrite', '1' );
	}
}
add_action( 'init', 'smile_jobs_flush_once', 99 );

/**
 * Read a job's details with sensible defaults.
 *
 * @param int $id Job ID.
 * @return array
 */
function smile_job( $id ) {
	$d = array();
	foreach ( array_keys( smile_job_fields() ) as $k ) {
		$d[ $k ] = (string) get_post_meta( $id, '_smile_job_' . $k, true );
	}
	if ( '' === $d['type'] ) {
		$d['type'] = 'FULL_TIME';
	}
	if ( '' === $d['location'] ) {
		$d['location'] = __( 'Soweto, Kayole, Nairobi', 'smile-community' );
	}
	$types           = smile_job_types();
	$d['type_label'] = isset( $types[ $d['type'] ] ) ? $types[ $d['type'] ] : $types['FULL_TIME'];
	$d['open']       = smile_job_is_open( $id );
	return $d;
}

/**
 * Today's date in Nairobi (Y-m-d).
 *
 * @return string
 */
function smile_job_today() {
	return wp_date( 'Y-m-d', null, new DateTimeZone( 'Africa/Nairobi' ) );
}

/**
 * Whether a job is open (published and closing date not passed).
 *
 * @param int $id Job ID.
 * @return bool
 */
function smile_job_is_open( $id ) {
	if ( 'publish' !== get_post_status( $id ) ) {
		return false;
	}
	$closing = (string) get_post_meta( $id, '_smile_job_closing', true );
	return '' === $closing || $closing >= smile_job_today();
}

/**
 * Format a Y-m-d date for people.
 *
 * @param string $ymd Date.
 * @return string
 */
function smile_job_date( $ymd ) {
	$ts = strtotime( $ymd . ' 12:00:00' );
	return $ts ? wp_date( 'j F Y', $ts, new DateTimeZone( 'Africa/Nairobi' ) ) : '';
}

/**
 * Days left until closing, or null.
 *
 * @param string $ymd Closing date.
 * @return int|null
 */
function smile_job_days_left( $ymd ) {
	if ( '' === $ymd ) {
		return null;
	}
	$a = new DateTime( smile_job_today() );
	$b = new DateTime( $ymd );
	return (int) $a->diff( $b )->format( '%r%a' );
}

/**
 * Published open jobs, newest first.
 *
 * @return WP_Post[]
 */
function smile_open_jobs() {
	$q = new WP_Query(
		array(
			'post_type'      => 'smile_job',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery -- small set.
				'relation' => 'OR',
				array( 'key' => '_smile_job_closing', 'compare' => '!EXISTS' ),
				array( 'key' => '_smile_job_closing', 'value' => '' ),
				array( 'key' => '_smile_job_closing', 'value' => smile_job_today(), 'compare' => '>=', 'type' => 'DATE' ),
			),
		)
	);
	return $q->posts;
}

/* ---------- Admin: details box, columns, notices ---------- */

/**
 * Add the Job details box.
 */
function smile_job_metabox() {
	add_meta_box( 'smile-job-details', __( 'Job details', 'smile-community' ), 'smile_job_metabox_render', 'smile_job', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'smile_job_metabox' );

/**
 * Render the Job details box.
 *
 * @param WP_Post $post Post.
 */
function smile_job_metabox_render( $post ) {
	wp_nonce_field( 'smile_job_save', 'smile_job_nonce' );
	echo '<p style="margin-top:0">' . esc_html__( 'Write the full role description in the editor above (about the role, responsibilities, what we are looking for, and anything else applicants should know). Use the Excerpt box for a one or two sentence summary shown on the Careers page.', 'smile-community' ) . '</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( smile_job_fields() as $key => $f ) {
		$id  = 'smile-job-' . $key;
		$val = (string) get_post_meta( $post->ID, '_smile_job_' . $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'select' === $f[1] ) {
			echo '<select id="' . esc_attr( $id ) . '" name="smile_job[' . esc_attr( $key ) . ']">';
			foreach ( smile_job_types() as $k => $label ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $val ? $val : 'FULL_TIME', $k, false ) . '>' . esc_html( $label ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input class="regular-text" type="' . esc_attr( $f[1] ) . '" id="' . esc_attr( $id ) . '" name="smile_job[' . esc_attr( $key ) . ']" value="' . esc_attr( $val ) . '" />';
		}
		if ( $f[2] ) {
			echo '<p class="description">' . esc_html( $f[2] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Save job details.
 *
 * @param int $post_id Post ID.
 */
function smile_job_save( $post_id ) {
	if ( !isset( $_POST['smile_job_nonce'] ) || !wp_verify_nonce( sanitize_key( wp_unslash( $_POST['smile_job_nonce'] ) ), 'smile_job_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$in = isset( $_POST['smile_job'] ) && is_array( $_POST['smile_job'] ) ? wp_unslash( $_POST['smile_job'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field below.
	foreach ( smile_job_fields() as $key => $f ) {
		$v = isset( $in[ $key ] ) ? (string) $in[ $key ] : '';
		switch ( $f[1] ) {
			case 'email':
				$v = sanitize_email( $v );
				break;
			case 'url':
				$v = esc_url_raw( $v );
				break;
			case 'date':
				$v = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
				break;
			case 'select':
				$v = array_key_exists( $v, smile_job_types() ) ? $v : 'FULL_TIME';
				break;
			default:
				$v = sanitize_text_field( $v );
		}
		if ( '' === $v ) {
			delete_post_meta( $post_id, '_smile_job_' . $key );
		} else {
			update_post_meta( $post_id, '_smile_job_' . $key, $v );
		}
	}
}
add_action( 'save_post_smile_job', 'smile_job_save' );

/**
 * Admin list columns.
 *
 * @param array $cols Columns.
 * @return array
 */
function smile_job_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['smile_type']    = __( 'Type', 'smile-community' );
			$out['smile_closing'] = __( 'Closing date', 'smile-community' );
			$out['smile_status']  = __( 'On Careers page', 'smile-community' );
		}
	}
	return $out;
}
add_filter( 'manage_smile_job_posts_columns', 'smile_job_columns' );

/**
 * Admin list column values.
 *
 * @param string $col Column.
 * @param int    $id  Post ID.
 */
function smile_job_column_value( $col, $id ) {
	$j = smile_job( $id );
	if ( 'smile_type' === $col ) {
		echo esc_html( $j['type_label'] );
	} elseif ( 'smile_closing' === $col ) {
		echo esc_html( $j['closing'] ? smile_job_date( $j['closing'] ) : __( 'No closing date', 'smile-community' ) );
	} elseif ( 'smile_status' === $col ) {
		echo $j['open'] ? '<span style="color:#1a7f37;font-weight:600">' . esc_html__( 'Open', 'smile-community' ) . '</span>' : '<span style="color:#8a8a8a">' . esc_html__( 'Not shown (closed or not published)', 'smile-community' ) . '</span>';
	}
}
add_action( 'manage_smile_job_posts_custom_column', 'smile_job_column_value', 10, 2 );

/* ---------- Careers page sections ---------- */

/**
 * Small icon helper that falls back gracefully.
 *
 * @param string $name Icon.
 * @return string
 */
function smile_job_icon( $name ) {
	return function_exists( 'smile_icon' ) ? smile_icon( $name ) : '';
}

/**
 * [smile_careers] Open roles, or a polished "no openings" state.
 *
 * @return string
 */
function smile_sc_careers() {
	$jobs = smile_open_jobs();
	if ( !$jobs ) {
		$li  = smile_setting( 'linkedin_url', 'https://www.linkedin.com/company/smile-community-centre/' );
		$out = '<div class="smile-jobs-empty smile-reveal">'
			. '<span class="smile-jobs-empty__icon" aria-hidden="true">' . smile_job_icon( 'users' ) . '</span>'
			. '<div class="smile-jobs-empty__body"><p class="smile-jobs-empty__kicker">' . esc_html__( 'Open roles', 'smile-community' ) . '</p>'
			. '<h3>' . esc_html__( 'There are no open positions right now', 'smile-community' ) . '</h3>'
			. '<p>' . esc_html__( 'We are not recruiting at the moment. New roles are always posted on this page first, with a clear closing date and how to apply. Leave your details with our talent community and we will get in touch when a suitable role opens.', 'smile-community' ) . '</p>'
			. '<div class="smile-btn-row"><a class="smile-btn smile-btn--teal" href="#talent">' . esc_html__( 'Join our talent community', 'smile-community' ) . '</a>'
			. ( $li ? '<a class="smile-btn smile-btn--ghost" href="' . esc_url( $li ) . '" target="_blank" rel="noopener">' . esc_html__( 'Follow us on LinkedIn', 'smile-community' ) . '</a>' : '' )
			. '</div></div></div>';
		return $out;
	}
	$types = array();
	foreach ( $jobs as $p ) {
		$j                    = smile_job( $p->ID );
		$types[ $j['type'] ] = $j['type_label'];
	}
	$count = count( $jobs );
	$out   = '<div class="smile-jobs" data-smile-jobs>';
	$out  .= '<div class="smile-jobs__bar"><p class="smile-jobs__count" aria-live="polite">' . esc_html( sprintf( /* translators: %d number of roles */ _n( '%d open role', '%d open roles', $count, 'smile-community' ), $count ) ) . '</p>';
	if ( count( $types ) > 1 ) {
		$out .= '<div class="smile-jobs__filters" role="group" aria-label="' . esc_attr__( 'Filter roles by type', 'smile-community' ) . '"><button type="button" class="smile-jobs__chip is-active" data-filter="all" aria-pressed="true">' . esc_html__( 'All', 'smile-community' ) . '</button>';
		foreach ( $types as $k => $label ) {
			$out .= '<button type="button" class="smile-jobs__chip" data-filter="' . esc_attr( $k ) . '" aria-pressed="false">' . esc_html( $label ) . '</button>';
		}
		$out .= '</div>';
	}
	$out .= '</div><ul class="smile-jobs__list">';
	foreach ( $jobs as $p ) {
		$j    = smile_job( $p->ID );
		$left = smile_job_days_left( $j['closing'] );
		$sum  = has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 28 );
		$out .= '<li class="smile-job smile-reveal" data-type="' . esc_attr( $j['type'] ) . '"><a class="smile-job__link" href="' . esc_url( get_permalink( $p ) ) . '">'
			. '<span class="smile-job__main"><span class="smile-job__tags"><span class="smile-job__pill">' . esc_html( $j['type_label'] ) . '</span>'
			. ( $j['department'] ? '<span class="smile-job__dept">' . esc_html( $j['department'] ) . '</span>' : '' )
			. ( null !== $left && $left <= 7 ? '<span class="smile-job__soon">' . esc_html( 0 === $left ? __( 'Closes today', 'smile-community' ) : sprintf( /* translators: %d days */ _n( 'Closes in %d day', 'Closes in %d days', $left, 'smile-community' ), $left ) ) . '</span>' : '' )
			. '</span><span class="smile-job__title">' . esc_html( get_the_title( $p ) ) . '</span>'
			. ( $sum ? '<span class="smile-job__sum">' . esc_html( $sum ) . '</span>' : '' )
			. '<span class="smile-job__meta"><span>' . smile_job_icon( 'pin' ) . esc_html( $j['location'] ) . '</span>'
			. ( $j['closing'] ? '<span>' . smile_job_icon( 'calendar' ) . esc_html( sprintf( /* translators: %s date */ __( 'Apply by %s', 'smile-community' ), smile_job_date( $j['closing'] ) ) ) . '</span>' : '' )
			. ( $j['salary'] ? '<span>' . smile_job_icon( 'receipt' ) . esc_html( $j['salary'] ) . '</span>' : '' )
			. '</span></span><span class="smile-job__cta">' . esc_html__( 'View role', 'smile-community' ) . ' <span aria-hidden="true">&rarr;</span></span></a></li>';
	}
	return $out . '</ul></div>';
}
add_shortcode( 'smile_careers', 'smile_sc_careers' );

/**
 * [smile_careers_values] Why work at Smile.
 *
 * @return string
 */
function smile_sc_careers_values() {
	$items = array(
		array( 'heart', __( 'Work that matters', 'smile-community' ), __( 'Every shift helps a child eat well, stay in school and feel safe. You will see the difference your work makes, day after day.', 'smile-community' ) ),
		array( 'users', __( 'A close, caring team', 'smile-community' ), __( 'We are a small team that works side by side with children, families, volunteers and partners in Soweto, Kayole.', 'smile-community' ) ),
		array( 'book', __( 'Room to learn and grow', 'smile-community' ), __( 'Induction, on-the-job mentoring and safeguarding training help you build your skills and your career.', 'smile-community' ) ),
		array( 'shield', __( 'Safe by design', 'smile-community' ), __( 'Clear policies, safer recruitment and a culture where anyone can raise a concern protect children and staff alike.', 'smile-community' ) ),
	);
	$out = '<div class="smile-cvalues">';
	foreach ( $items as $i => $it ) {
		$out .= '<div class="smile-cvalue smile-reveal"><span class="smile-cvalue__icon" aria-hidden="true">' . smile_job_icon( $it[0] ) . '</span><h3>' . esc_html( $it[1] ) . '</h3><p>' . esc_html( $it[2] ) . '</p></div>';
	}
	return $out . '</div>';
}
add_shortcode( 'smile_careers_values', 'smile_sc_careers_values' );

/**
 * [smile_careers_process] How we hire.
 *
 * @return string
 */
function smile_sc_careers_process() {
	$steps = array(
		array( __( 'Apply', 'smile-community' ), __( 'Send your CV and a short cover letter as described in the role. Applications close on the date shown.', 'smile-community' ), __( 'Any time before the closing date', 'smile-community' ) ),
		array( __( 'Shortlist and interview', 'smile-community' ), __( 'Shortlisted candidates are invited to an interview at the centre or by phone. We contact everyone we shortlist.', 'smile-community' ), __( 'Usually within 2 weeks of closing', 'smile-community' ) ),
		array( __( 'References and checks', 'smile-community' ), __( 'Before any offer we take up two references and ask for a Certificate of Good Conduct, as part of safer recruitment.', 'smile-community' ), __( 'About 1 to 2 weeks', 'smile-community' ) ),
		array( __( 'Welcome and induction', 'smile-community' ), __( 'You sign our safeguarding and code of conduct policies, meet the team and settle in with a guided induction.', 'smile-community' ), __( 'Your first weeks with us', 'smile-community' ) ),
	);
	$out = '<ol class="smile-hire">';
	foreach ( $steps as $i => $s ) {
		$out .= '<li class="smile-hire__step smile-reveal"><span class="smile-hire__num" aria-hidden="true">' . ( $i + 1 ) . '</span><h3>' . esc_html( $s[0] ) . '</h3><p>' . esc_html( $s[1] ) . '</p><span class="smile-hire__time">' . esc_html( $s[2] ) . '</span></li>';
	}
	return $out . '</ol>';
}
add_shortcode( 'smile_careers_process', 'smile_sc_careers_process' );

/**
 * [smile_careers_talent] Talent community form (falls back to email if the
 * Smile Community Core plugin has not been updated yet).
 *
 * @return string
 */
function smile_sc_careers_talent() {
	if ( function_exists( 'smile_core_form_types' ) && array_key_exists( 'careers', smile_core_form_types() ) ) {
		return do_shortcode( '[smile_form type="careers" button="' . esc_attr__( 'Join the talent community', 'smile-community' ) . '"]' );
	}
	$email = smile_setting( 'email', 'info@smilecommunitycentre.org' );
	$href  = 'mailto:' . antispambot( $email ) . '?subject=' . rawurlencode( __( 'Talent community: my CV', 'smile-community' ) );
	return '<div class="smile-talent-fallback"><p>' . esc_html__( 'Email your CV and a few lines about the kind of role you are interested in. We keep your details on file for up to 12 months.', 'smile-community' ) . '</p><a class="smile-btn smile-btn--teal" href="' . esc_url( $href ) . '">' . esc_html__( 'Email your CV', 'smile-community' ) . '</a></div>';
}
add_shortcode( 'smile_careers_talent', 'smile_sc_careers_talent' );

/**
 * [smile_careers_faq] Common questions, including a recruitment fraud warning.
 *
 * @return string
 */
function smile_sc_careers_faq() {
	$items = array(
		array( __( 'Do you charge a fee to apply or to be hired?', 'smile-community' ), __( 'Never. Smile Community Centre does not ask for money at any stage of recruitment. If anyone asks you to pay for an application, interview, medical or job offer in our name, it is a scam. Please report it to us.', 'smile-community' ) ),
		array( __( 'Where are jobs advertised?', 'smile-community' ), __( 'Every genuine role is posted on this page, with a closing date and the way to apply. We may also share it on our Facebook and LinkedIn pages, always linking back here.', 'smile-community' ) ),
		array( __( 'Can I send my CV when there are no openings?', 'smile-community' ), __( 'Yes. Join the talent community on this page. We keep your details for up to 12 months and contact you if a suitable role opens.', 'smile-community' ) ),
		array( __( 'Do you offer internships or college attachments?', 'smile-community' ), __( 'From time to time, in areas such as social work, education and administration. They are posted here as "Internship / attachment" roles.', 'smile-community' ) ),
		array( __( 'I would like to help but not as an employee.', 'smile-community' ), __( 'You are very welcome to volunteer, partner with us or give. See the Get Involved pages for all the ways to help.', 'smile-community' ) ),
	);
	$out = '<div class="smile-cfaq">';
	foreach ( $items as $it ) {
		$out .= '<details class="smile-cfaq__item"><summary>' . esc_html( $it[0] ) . '</summary><p>' . esc_html( $it[1] ) . '</p></details>';
	}
	return $out . '</div>';
}
add_shortcode( 'smile_careers_faq', 'smile_sc_careers_faq' );

/* ---------- Single job: structured data for Google for Jobs ---------- */

/**
 * JobPosting JSON-LD on an open job.
 */
function smile_job_schema() {
	if ( !is_singular( 'smile_job' ) ) {
		return;
	}
	$id = get_queried_object_id();
	$j  = smile_job( $id );
	if ( !$j['open'] ) {
		return;
	}
	$data = array(
		'@context'           => 'https://schema.org',
		'@type'              => 'JobPosting',
		'title'              => get_the_title( $id ),
		'description'        => wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core filter.
		'datePosted'         => get_the_date( 'c', $id ),
		'employmentType'     => $j['type'],
		'hiringOrganization' => array(
			'@type'  => 'NGO',
			'name'   => get_bloginfo( 'name' ),
			'sameAs' => home_url( '/' ),
		),
		'jobLocation'        => array(
			'@type'   => 'Place',
			'address' => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => smile_setting( 'address', 'Mzesa, Soweto, Kayole' ),
				'addressLocality' => 'Nairobi',
				'addressCountry'  => 'KE',
			),
		),
		'directApply'        => false,
	);
	if ( $j['closing'] ) {
		$data['validThrough'] = $j['closing'] . 'T23:59:59+03:00';
	}
	if ( $j['reference'] ) {
		$data['identifier'] = array( '@type' => 'PropertyValue', 'name' => get_bloginfo( 'name' ), 'value' => $j['reference'] );
	}
	if ( function_exists( 'smile_brand_logo_url' ) ) {
		$data['hiringOrganization']['logo'] = smile_brand_logo_url();
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'smile_job_schema', 30 );

/**
 * Keep closed roles out of search results.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function smile_job_robots( $robots ) {
	if ( is_singular( 'smile_job' ) && !smile_job_is_open( get_queried_object_id() ) ) {
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'smile_job_robots' );

/* ---------- One-time setup: create the Careers page and menu links ---------- */

/**
 * Create the Careers page (from demo/pages.json) and add it to the menus once,
 * so it appears without running "Apply latest design".
 */
function smile_careers_setup() {
	if ( '2' === get_option( 'smile_careers_setup' ) || !current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( !function_exists( 'smile_demo_json' ) || !function_exists( 'smile_demo_swap_images' ) ) {
		return;
	}
	$page = get_page_by_path( 'careers' );
	if ( !$page ) {
		$def = null;
		foreach ( smile_demo_json( 'pages.json' ) as $p ) {
			if ( 'careers' === $p['slug'] ) {
				$def = $p;
			}
		}
		if ( !$def ) {
			return;
		}
		$images = function_exists( 'smile_demo_image_map' ) ? smile_demo_image_map() : array();
		$id     = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $def['title'],
					'post_name'    => 'careers',
					'post_content' => wp_kses_post( $def['html'] ),
					'menu_order'   => (int) $def['order'],
				)
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return;
		}
		update_post_meta( $id, '_smile_setup', '1' );
		update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.20.0' );
		$data = smile_demo_swap_images( $def['elementor'], $images );
		if ( function_exists( 'smile_demo_fix_links' ) ) {
			$data = smile_demo_fix_links( $data );
		}
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $id, '_elementor_page_settings', array( 'hide_title' => 'yes' ) );
		update_post_meta( $id, '_seopress_titles_title', sanitize_text_field( $def['seo_title'] ) );
		update_post_meta( $id, '_seopress_titles_desc', sanitize_text_field( $def['seo_desc'] ) );
		$page = get_post( $id );
	}
	// Menu links: under "Get Involved" in the main menu, and in the footer menu.
	$locs = get_nav_menu_locations();
	foreach ( array( 'menu-1', 'menu-2' ) as $loc ) {
		if ( empty( $locs[ $loc ] ) ) {
			continue;
		}
		$items  = (array) wp_get_nav_menu_items( $locs[ $loc ] );
		$parent = 0;
		$found  = false;
		foreach ( $items as $it ) {
			if ( (int) $it->object_id === (int) $page->ID && 'page' === $it->object ) {
				$found = true;
			}
			$gi = get_page_by_path( 'get-involved' );
			if ( 'menu-1' === $loc && $gi && (int) $it->object_id === (int) $gi->ID && 0 === (int) $it->menu_item_parent ) {
				$parent = (int) $it->ID;
			}
		}
		if ( $found || ( 'menu-1' === $loc && !$parent ) ) {
			continue;
		}
		wp_update_nav_menu_item(
			$locs[ $loc ],
			0,
			array(
				'menu-item-title'     => __( 'Careers', 'smile-community' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			)
		);
	}
	update_option( 'smile_careers_setup', '2' );
}
add_action( 'admin_init', 'smile_careers_setup', 20 );
