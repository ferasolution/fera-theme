<?php
/**
 * Services page (design template).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="svcPage" data-page="services">';
fera_partial_e( 'page-services' );
echo '</main>';
get_footer();
