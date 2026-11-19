<?php
/**
 * Helpers: URLs, partial rendering, service data, current route.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** The four core pages the design ships with (slug => title). */
function fera_core_pages() {
	return array(
		'about'    => 'About',
		'services' => 'Services',
		'work'     => 'Work',
		'contact'  => 'Contact',
	);
}

/** Service data shared with the design (data/services.json). */
function fera_services() {
	static $data = null;
	if ( null === $data ) {
		$json = file_get_contents( FERA_DIR . '/data/services.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$data = json_decode( (string) $json, true );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
	}
	return $data;
}

/** Service data by slug, or null. */
function fera_service( $slug ) {
	foreach ( fera_services() as $s ) {
		if ( $s['slug'] === $slug ) {
			return $s;
		}
	}
	return null;
}

/** Find a top-level page by slug (pages only — never media attachments). */
function fera_get_page( $slug, $status = 'publish' ) {
	$q = get_posts(
		array(
			'post_type'        => 'page',
			'name'             => $slug,
			'post_parent'      => 0,
			'post_status'      => $status,
			'posts_per_page'   => 1,
			'suppress_filters' => true,
		)
	);
	return $q ? $q[0] : null;
}

/** URL of a core page (falls back to /slug/ before the page exists). */
function fera_page_url( $slug ) {
	if ( 'home' === $slug ) {
		return home_url( '/' );
	}
	$page = fera_get_page( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Find a service post by slug. (get_page_by_path() would also match media
 * attachments with the same name, e.g. an image called "content-writing".)
 */
function fera_get_service_post( $slug, $status = array( 'publish', 'draft', 'pending', 'private', 'future' ) ) {
	$q = get_posts(
		array(
			'post_type'        => 'fera_service',
			'name'             => $slug,
			'post_status'      => $status,
			'posts_per_page'   => 1,
			'suppress_filters' => true,
		)
	);
	return $q ? $q[0] : null;
}

/** URL of a service page. */
function fera_service_url( $slug ) {
	$post = fera_get_service_post( $slug, 'publish' );
	return $post ? get_permalink( $post ) : home_url( '/services/' . $slug . '/' );
}

/** Replace {{TOKENS}} in a design partial. */
function fera_fill( $html ) {
	return preg_replace_callback(
		'/\{\{(URL:svc:[a-z0-9-]+|URL:[a-z0-9-]+|IMG|YEAR)\}\}/',
		static function ( $m ) {
			$t = $m[1];
			if ( 'IMG' === $t ) {
				return esc_url( FERA_URI . '/assets/img' );
			}
			if ( 'YEAR' === $t ) {
				return esc_html( wp_date( 'Y' ) );
			}
			if ( 0 === strpos( $t, 'URL:svc:' ) ) {
				return esc_url( fera_service_url( substr( $t, 8 ) ) );
			}
			return esc_url( fera_page_url( substr( $t, 4 ) ) );
		},
		$html
	);
}

/** Read a design partial from /partials. Files are shipped with the theme (trusted markup). */
function fera_partial( $name ) {
	$name = sanitize_file_name( $name );
	$file = FERA_DIR . '/partials/' . $name . '.html';
	if ( ! is_readable( $file ) ) {
		return '';
	}
	return fera_fill( file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

/** Echo a design partial. */
function fera_partial_e( $name ) {
	echo fera_partial( $name ); // phpcs:ignore WordPress.Security.EscapeOutput -- theme-owned markup, tokens escaped in fera_fill().
}

/** Does a partial exist? */
function fera_has_partial( $name ) {
	return is_readable( FERA_DIR . '/partials/' . sanitize_file_name( $name ) . '.html' );
}

/** Route key used by the front-end script (data-route on <body>). */
function fera_route() {
	if ( is_front_page() ) {
		return 'home';
	}
	foreach ( array_keys( fera_core_pages() ) as $slug ) {
		if ( is_page( $slug ) ) {
			return $slug;
		}
	}
	if ( is_singular( 'fera_service' ) ) {
		return 'service';
	}
	return 'page';
}

/** Which main-nav item is active. */
function fera_nav_key() {
	$r = fera_route();
	if ( 'service' === $r ) {
		return 'services';
	}
	return in_array( $r, array( 'home', 'about', 'services', 'work', 'contact' ), true ) ? $r : '';
}

/** Is the current view rendered by the design templates (not a plain WP page/post)? */
function fera_is_design_view() {
	$r = fera_route();
	return 'page' !== $r && ! ( 'service' === $r && ! fera_has_partial( 'service-' . get_post_field( 'post_name', get_queried_object_id() ) ) );
}

/**
 * Shared hero footer used by WordPress-driven pages (blog, archives, posts, legal pages)
 * so they match the designed pages: lead + CTAs row, then the four-column hero bar.
 *
 * @param string   $lead  Lead text (plain) or HTML when $raw is true.
 * @param string[] $bar   Up to three labels shown after "Scroll".
 * @param bool     $raw   Treat $lead as trusted HTML (e.g. a search form).
 */
function fera_hero_tail( $lead, $bar = array(), $raw = false ) {
	$arrow = '<span class="arr"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>';
	echo '<div class="s-row">';
	if ( $raw ) {
		echo '<div class="s-lead" data-fade>' . $lead . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted markup from core functions.
	} else {
		echo '<p class="s-lead" data-fade>' . esc_html( $lead ) . '</p>';
	}
	echo '<div class="hero-ctas" data-fade>';
	echo '<button type="button" class="btn btn-red magnetic" data-open-contact data-cursor="' . esc_attr__( "Let's talk", 'fera-theme' ) . '"><span class="roll"><span data-t="' . esc_attr__( 'Start a project', 'fera-theme' ) . '">' . esc_html__( 'Start a project', 'fera-theme' ) . '</span></span>' . $arrow . '</button>'; // phpcs:ignore
	echo '<a href="' . esc_url( fera_page_url( 'services' ) ) . '" class="btn btn-line magnetic"><span class="roll"><span data-t="' . esc_attr__( 'Our services', 'fera-theme' ) . '">' . esc_html__( 'Our services', 'fera-theme' ) . '</span></span>' . $arrow . '</a>'; // phpcs:ignore
	echo '</div></div>';
	$bar = array_slice( array_values( array_filter( $bar ) ), 0, 2 );
	while ( count( $bar ) < 2 ) {
		$bar[] = '';
	}
	echo '<div class="hero-bar mono" data-fade><span class="scroll-cue"><b></b>' . esc_html__( 'Scroll', 'fera-theme' ) . '</span>';
	foreach ( $bar as $item ) {
		echo '<span>' . wp_kses_post( $item ) . '</span>';
	}
	echo '<span>' . esc_html__( 'Herndon, VA · USA', 'fera-theme' ) . '</span></div>';
}
