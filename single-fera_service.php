<?php
/**
 * Single service page: /services/<slug>/
 * Uses the designed layout for the eight core services; any new service
 * added in wp-admin falls back to the standard page layout with its own content.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

$fera_slug = get_post_field( 'post_name', get_queried_object_id() );
$fera_svc  = fera_service( $fera_slug );

if ( ! $fera_svc || ! fera_has_partial( 'service-' . $fera_slug ) ) {
	get_template_part( 'template-parts/content', 'page' );
	return;
}

get_header();
printf( '<main id="svcPage" data-page="service" data-icon="%s">', esc_attr( $fera_svc['icon'] ) );
fera_partial_e( 'service-' . $fera_slug );
echo '</main>';
get_footer();
