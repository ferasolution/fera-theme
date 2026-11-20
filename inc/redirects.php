<?php
/**
 * 301 redirects from the old site's URLs to the new pages.
 * Only fires when the old URL no longer resolves (404), e.g. after the old
 * page is moved to Draft, so nothing changes while the old page is published.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** Old path => new path. Filterable with 'fera_redirects'. */
function fera_redirect_map() {
	return apply_filters(
		'fera_redirects',
		array(
			'cybersecurity'            => 'services/cybersecurity',
			'digital-marketing'        => 'services/digital-marketing',
			'software-development'     => 'services/software-development',
			'web-applications'         => 'services/software-development',
			'web-development'          => 'services/web-development',
			'graphic-design'           => 'services/graphic-design',
			'content-writing'          => 'services/content-writing',
			'ecommerce'                => 'services/ecommerce',
			'e-commerce'               => 'services/ecommerce',
			'social-media'             => 'services/social-media',
			'portfolio'                => 'work',
			'spectrum-partner-program' => 'services',
		)
	);
}

add_action(
	'template_redirect',
	static function () {
		if ( ! is_404() ) {
			return;
		}
		$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
		$home = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
		if ( $home && 0 === strpos( $path, $home . '/' ) ) {
			$path = substr( $path, strlen( $home ) + 1 );
		}
		$map = fera_redirect_map();
		if ( isset( $map[ $path ] ) ) {
			wp_safe_redirect( home_url( '/' . $map[ $path ] . '/' ), 301, 'Fera Theme' );
			exit;
		}
	},
	1
);
