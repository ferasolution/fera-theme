<?php
/**
 * Interactive demos ("Fera Interactive Experiences").
 *
 * The /experience/ hub and its 29 demo pages were created by the previous
 * theme (pages marked with _ff_demo_hub / _ff_demo meta). Their apps live in
 * assets/demos/*.html and are rendered here so the existing pages keep working.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** Demo catalogue (data/demos.json). */
function fera_demos() {
	static $d = null;
	if ( null === $d ) {
		$d = json_decode( (string) file_get_contents( FERA_DIR . '/data/demos.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$d = is_array( $d ) ? $d : array();
	}
	return $d;
}

/** Page ID for a demo key (prefers the mapping saved by the previous theme). */
function fera_demo_page_id( $key ) {
	$ids = (array) get_option( 'ff_demo_pages', array() );
	if ( ! empty( $ids[ $key ] ) && 'publish' === get_post_status( $ids[ $key ] ) ) {
		return (int) $ids[ $key ];
	}
	$q = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'meta_key'       => '_ff_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	return $q ? (int) $q[0] : 0;
}

/** Public URL of a demo. */
function fera_demo_url( $key ) {
	$id = fera_demo_page_id( $key );
	return $id ? get_permalink( $id ) : fera_page_url( 'work' );
}

/** The demo hub page (/experience/). */
function fera_demo_hub_id() {
	$id = (int) get_option( 'ff_demo_parent' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return $id;
	}
	$p = fera_get_page( 'experience' );
	return $p ? (int) $p->ID : 0;
}

/** Is the current request a demo page / the hub? */
function fera_current_demo_key() {
	if ( ! is_page() ) {
		return '';
	}
	return (string) get_post_meta( get_queried_object_id(), '_ff_demo', true );
}
function fera_is_demo_hub() {
	return is_page() && ( get_post_meta( get_queried_object_id(), '_ff_demo_hub', true ) || ( fera_demo_hub_id() && get_queried_object_id() === fera_demo_hub_id() ) );
}

/** Render a standalone demo app and stop. */
function fera_render_demo( $key ) {
	$demo = null;
	foreach ( fera_demos() as $item ) {
		if ( $item['id'] === $key ) {
			$demo = $item;
			break;
		}
	}
	if ( ! $demo ) {
		return;
	}
	$parts = explode( '?', $demo['source'], 2 );
	$file  = $parts[0];
	$value = '';
	if ( isset( $parts[1] ) ) {
		parse_str( $parts[1], $params );
		$value = (string) reset( $params );
	}
	if ( ! in_array( $file, array( 'lab.html', 'app.html', 'website.html' ), true ) ) {
		return;
	}
	$dir  = FERA_DIR . '/assets/demos/';
	$uri  = FERA_URI . '/assets/demos/';
	$html = (string) file_get_contents( $dir . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	// Version local assets by file time.
	$html = preg_replace_callback(
		'/((?:src|href)=")([A-Za-z0-9._-]+\.(?:js|css))(\?[^"<>]*)?"/',
		static function ( $m ) use ( $dir, $uri ) {
			$v = is_file( $dir . $m[2] ) ? filemtime( $dir . $m[2] ) : FERA_THEME_VERSION;
			return $m[1] . esc_url( add_query_arg( 'ver', $v, $uri . $m[2] ) ) . '"';
		},
		$html
	);
	$html  = str_replace( 'base-uri &#39;none&#39;', 'base-uri &#39;self&#39;', $html );
	$title = $demo['title'] . ' | Fera Solution Demonstration';
	$url   = fera_demo_url( $key );
	$html  = preg_replace( '/<title>.*?<\/title>/s', '<title>' . esc_html( $title ) . '</title>', $html, 1 );
	$meta  = '<meta name="robots" content="noindex, follow"><meta name="description" content="' . esc_attr( $demo['description'] ) . '"><link rel="canonical" href="' . esc_url( $url ) . '"><meta property="og:title" content="' . esc_attr( $title ) . '"><meta property="og:description" content="' . esc_attr( $demo['description'] ) . '"><meta property="og:image" content="' . esc_url( FERA_URI . '/assets/img/og-image.jpg' ) . '">';
	$html  = str_replace( '<head>', '<head>' . $meta . '<base href="' . esc_url( $uri ) . '"><meta name="fera-demo" content="' . esc_attr( $value ) . '"><meta name="fera-contact" content="' . esc_url( fera_page_url( 'contact' ) ) . '">', $html );
	$html  = str_replace( array( 'href="lab.html"', 'href="app.html"' ), 'href="' . esc_url( fera_demo_hub_id() ? get_permalink( fera_demo_hub_id() ) : fera_page_url( 'work' ) ) . '"', $html );
	foreach ( array( 'crm' => 'legacy-crm', 'projects' => 'legacy-projects', 'inventory' => 'legacy-inventory', 'workflow' => 'workflow', 'security' => 'security' ) as $mode => $id ) {
		$html = str_replace( 'href="?app=' . $mode . '"', 'href="' . esc_url( fera_demo_url( $id ) ) . '"' . ( 'app.html' === $file && $value === $mode ? ' aria-current="page"' : '' ), $html );
	}
	$html = str_replace( '../../../../../contact/', esc_url( fera_page_url( 'contact' ) ), $html );

	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, follow' );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- theme-shipped demo document.
	exit;
}

add_action(
	'template_redirect',
	static function () {
		$key = fera_current_demo_key();
		if ( $key ) {
			fera_render_demo( $key );
		}
	},
	5
);

/** Gallery markup for the hub (all) or Work page (featured). */
function fera_demo_gallery( $mode = 'all' ) {
	$featured = array( 'accounting', 'crm', 'corporate', 'professional', 'builder', 'vulnerabilities' );
	$cats     = array(
		'all'       => 'All',
		'software'  => 'Software',
		'websites'  => 'Websites',
		'workflow'  => 'Automation',
		'security'  => 'Cybersecurity',
		'marketing' => 'Marketing',
	);
	ob_start();
	echo '<div class="demo-gallery" data-demo-gallery>';
	if ( 'all' === $mode ) {
		echo '<div class="demo-filters" role="group" aria-label="Filter demos">';
		foreach ( $cats as $k => $label ) {
			printf( '<button type="button" data-filter="%1$s" aria-pressed="%2$s">%3$s</button>', esc_attr( $k ), 'all' === $k ? 'true' : 'false', esc_html( $label ) );
		}
		echo '</div>';
	}
	echo '<div class="demo-grid">';
	$seen = array();
	foreach ( fera_demos() as $d ) {
		if ( 'featured' === $mode && ! in_array( $d['id'], $featured, true ) ) {
			continue;
		}
		if ( isset( $seen[ $d['title'] ] ) ) {
			continue; // collapse duplicate concepts.
		}
		$seen[ $d['title'] ] = true;
		$url = fera_demo_url( $d['id'] );
		printf(
			'<article class="demo-card" data-category="%1$s" data-reveal><span class="mono demo-cat">%2$s / live demo</span><h3>%3$s</h3><p>%4$s</p><div class="demo-actions"><button type="button" class="demo-open" data-src="%5$s" data-title="%6$s">Launch demo</button><a class="demo-new" href="%5$s" target="_blank" rel="noopener" aria-label="%7$s">Open in new tab ↗</a></div></article>',
			esc_attr( $d['category'] ),
			esc_html( $cats[ $d['category'] ] ?? ucfirst( $d['category'] ) ),
			esc_html( $d['title'] ),
			esc_html( $d['description'] ),
			esc_url( $url ),
			esc_attr( $d['title'] ),
			esc_attr( 'Open ' . $d['title'] . ' in a new tab' )
		);
	}
	echo '</div>';
	echo '<div class="demo-stage" hidden><div class="demo-bar"><strong class="demo-title"></strong><div class="demo-bar-actions"><button type="button" data-device="desktop" aria-pressed="true">Desktop</button><button type="button" data-device="mobile" aria-pressed="false">Mobile</button><button type="button" class="demo-close">Close</button></div></div><div class="demo-frame"><iframe title="Interactive demonstration" loading="lazy" sandbox="allow-scripts allow-forms allow-downloads allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer"></iframe></div></div>';
	echo '<p class="mono demo-note">Live demonstrations · Synthetic sample data · No client endorsements</p>';
	echo '</div>';
	return ob_get_clean();
}

/** Route the hub to its template. */
add_filter(
	'template_include',
	static function ( $template ) {
		if ( fera_is_demo_hub() ) {
			return FERA_DIR . '/page-experience.php';
		}
		return $template;
	},
	100
);

/** Demo pages are thin, interactive tools: keep them out of search results. */
add_filter(
	'wp_robots',
	static function ( $robots ) {
		if ( fera_current_demo_key() ) {
			$robots['noindex'] = true;
			unset( $robots['index'] );
		}
		return $robots;
	}
);
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array( 'key' => '_ff_demo', 'compare' => 'NOT EXISTS' ),
			);
		}
		return $args;
	},
	10,
	2
);
