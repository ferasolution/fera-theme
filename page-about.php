<?php
/**
 * About page (design template).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="svcPage" data-page="about">';
fera_partial_e( 'page-about' );
echo '</main>';
get_footer();
