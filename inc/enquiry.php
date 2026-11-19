<?php
/**
 * Enquiry form endpoint: saves every enquiry under "Enquiries" in wp-admin
 * and emails the sales inbox (delivered through FluentSMTP when it is active).
 *
 * POST /wp-json/fera/v1/enquiry
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** Where enquiry emails go. Change under Settings → General ("Fera enquiries email"). */
function fera_leads_email() {
	$e = get_option( 'fera_leads_email' );
	return is_email( $e ) ? $e : 'sales@ferasolution.com';
}

/**
 * Math check ("what is 7 + 4?"). The answer is never sent to the browser: the
 * question comes with a signed token, and each token can be used only once.
 * Generated per request, so it also works behind the page cache.
 */
function fera_captcha_sign( $payload ) {
	return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) . 'fera-captcha' );
}

/** New question + token. */
function fera_captcha_new() {
	$op = wp_rand( 0, 2 );
	if ( 0 === $op ) {
		$a = wp_rand( 2, 15 );
		$b = wp_rand( 2, 12 );
		$q = $a . ' + ' . $b;
		$ans = $a + $b;
	} elseif ( 1 === $op ) {
		$a = wp_rand( 8, 20 );
		$b = wp_rand( 1, 7 );
		$q = $a . ' − ' . $b;
		$ans = $a - $b;
	} else {
		$a = wp_rand( 2, 9 );
		$b = wp_rand( 2, 5 );
		$q = $a . ' × ' . $b;
		$ans = $a * $b;
	}
	$exp   = time() + 30 * MINUTE_IN_SECONDS;
	$nonce = wp_generate_password( 12, false );
	$body  = $exp . '.' . $nonce;
	return array(
		'q' => $q,
		't' => $body . '.' . fera_captcha_sign( $ans . '|' . $body ),
	);
}

/** Check an answer against its token (single use). */
function fera_captcha_ok( $answer, $token ) {
	$parts = explode( '.', (string) $token );
	if ( 3 !== count( $parts ) || (int) $parts[0] < time() ) {
		return false;
	}
	$answer = preg_replace( '/[^0-9\-]/', '', (string) $answer );
	if ( '' === $answer ) {
		return false;
	}
	$body = $parts[0] . '.' . $parts[1];
	if ( ! hash_equals( fera_captcha_sign( (int) $answer . '|' . $body ), $parts[2] ) ) {
		return false;
	}
	$used = 'fera_cap_' . md5( $parts[1] );
	if ( get_transient( $used ) ) {
		return false;
	}
	set_transient( $used, 1, 35 * MINUTE_IN_SECONDS );
	return true;
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'fera/v1',
			'/captcha',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => static function () {
					$r = new WP_REST_Response( fera_captcha_new(), 200 );
					$r->header( 'Cache-Control', 'no-store, max-age=0' );
					return $r;
				},
			)
		);
		register_rest_route(
			'fera/v1',
			'/enquiry',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'fera_handle_enquiry',
			)
		);
	}
);

/**
 * Handle a submitted enquiry.
 *
 * @param WP_REST_Request $req Request.
 * @return WP_REST_Response
 */
function fera_handle_enquiry( WP_REST_Request $req ) {
	$p = (array) $req->get_json_params();

	// Bots: honeypot filled or form submitted impossibly fast. Pretend success, store nothing.
	if ( ! empty( $p['website_hp'] ) || ( isset( $p['elapsed'] ) && (int) $p['elapsed'] < 2500 ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	// Rate limit: 8 attempts per 10 minutes per visitor IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'fera_rl_' . md5( $ip . wp_salt( 'nonce' ) );
	$hits = (int) get_transient( $key );
	if ( $hits >= 8 ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => 'Too many requests. Please email sales@ferasolution.com.' ), 429 );
	}
	set_transient( $key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$f = array(
		'name'     => sanitize_text_field( $p['name'] ?? '' ),
		'email'    => sanitize_email( $p['email'] ?? '' ),
		'phone'    => sanitize_text_field( $p['phone'] ?? '' ),
		'company'  => sanitize_text_field( $p['company'] ?? '' ),
		'message'  => sanitize_textarea_field( $p['message'] ?? '' ),
		'budget'   => sanitize_text_field( $p['budget'] ?? '' ),
		'services' => array_slice( array_map( 'sanitize_text_field', (array) ( $p['services'] ?? array() ) ), 0, 12 ),
		'page'     => esc_url_raw( $p['page'] ?? '' ),
	);
	foreach ( array( 'name' => 120, 'phone' => 60, 'company' => 200, 'message' => 5000, 'budget' => 60 ) as $k => $max ) {
		$f[ $k ] = mb_substr( $f[ $k ], 0, $max );
	}
	$missing = array();
	if ( ! $f['services'] ) {
		$missing[] = 'services';
	}
	if ( ! empty( $p['needs_budget'] ) && '' === $f['budget'] ) {
		$missing[] = 'budget';
	}
	if ( '' === $f['name'] ) {
		$missing[] = 'name';
	}
	if ( ! is_email( $f['email'] ) ) {
		$missing[] = 'email';
	}
	if ( strlen( preg_replace( '/\D/', '', $f['phone'] ) ) < 7 ) {
		$missing[] = 'phone';
	}
	if ( '' === $f['company'] ) {
		$missing[] = 'company';
	}
	if ( mb_strlen( trim( $f['message'] ) ) < 10 ) {
		$missing[] = 'msg';
	}
	if ( $missing ) {
		return new WP_REST_Response( array( 'ok' => false, 'fields' => $missing, 'message' => 'Please complete all required fields.' ), 400 );
	}
	if ( ! fera_captcha_ok( $p['cap'] ?? '', $p['cap_t'] ?? '' ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'fields' => array( 'cap' ), 'captcha' => fera_captcha_new(), 'message' => 'The answer to the quick check was not right. Please try the new question.' ), 400 );
	}

	$lines = array(
		'Name: ' . $f['name'],
		'Email: ' . $f['email'],
		'Phone: ' . ( $f['phone'] ? $f['phone'] : '—' ),
		'Company / website: ' . ( $f['company'] ? $f['company'] : '—' ),
		'Services: ' . ( $f['services'] ? implode( ', ', $f['services'] ) : '—' ),
		'Budget: ' . ( $f['budget'] ? $f['budget'] : '—' ),
		'Page: ' . $f['page'],
		'',
		'Message:',
		$f['message'] ? $f['message'] : '—',
	);
	$body = implode( "\n", $lines );

	$lead_id = wp_insert_post(
		array(
			'post_type'    => 'fera_lead',
			'post_status'  => 'private',
			'post_title'   => $f['name'] . ( $f['services'] ? ' — ' . implode( ', ', $f['services'] ) : '' ),
			'post_content' => $body,
		),
		true
	);
	if ( ! is_wp_error( $lead_id ) ) {
		foreach ( $f as $k => $v ) {
			update_post_meta( $lead_id, '_fera_' . $k, is_array( $v ) ? implode( ', ', $v ) : $v );
		}
	}

	$sent = wp_mail(
		fera_leads_email(),
		'New enquiry from ' . $f['name'] . ' — ferasolution.com',
		$body,
		array( 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>' )
	);

	if ( is_wp_error( $lead_id ) && ! $sent ) {
		return new WP_REST_Response( array( 'ok' => false, 'message' => 'Sorry, that didn\'t go through. Please email sales@ferasolution.com.' ), 500 );
	}
	return new WP_REST_Response( array( 'ok' => true, 'message' => 'Thank you! Your enquiry has been sent — we\'ll reply within one business day.' ), 200 );
}

/* ---------- Admin: enquiry list columns + settings field ---------- */
add_filter(
	'manage_fera_lead_posts_columns',
	static function ( $cols ) {
		return array(
			'cb'           => $cols['cb'],
			'title'        => __( 'Enquiry', 'fera-theme' ),
			'fera_email'   => __( 'Email', 'fera-theme' ),
			'fera_phone'   => __( 'Phone', 'fera-theme' ),
			'fera_budget'  => __( 'Budget', 'fera-theme' ),
			'date'         => $cols['date'],
		);
	}
);
add_action(
	'manage_fera_lead_posts_custom_column',
	static function ( $col, $id ) {
		$map = array( 'fera_email' => '_fera_email', 'fera_phone' => '_fera_phone', 'fera_budget' => '_fera_budget' );
		if ( isset( $map[ $col ] ) ) {
			$v = get_post_meta( $id, $map[ $col ], true );
			if ( 'fera_email' === $col && $v ) {
				printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $v ), esc_html( $v ) );
			} else {
				echo esc_html( $v ? $v : '—' );
			}
		}
	},
	10,
	2
);
add_action(
	'admin_init',
	static function () {
		register_setting(
			'general',
			'fera_leads_email',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
				'default'           => 'sales@ferasolution.com',
			)
		);
		add_settings_field(
			'fera_leads_email',
			__( 'Fera enquiries email', 'fera-theme' ),
			static function () {
				printf( '<input type="email" class="regular-text" name="fera_leads_email" value="%s"><p class="description">%s</p>', esc_attr( fera_leads_email() ), esc_html__( 'Website enquiry forms are emailed here and saved under Enquiries.', 'fera-theme' ) );
			},
			'general'
		);
	}
);
