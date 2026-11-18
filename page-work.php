<?php
/**
 * Work page (design template).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="svcPage" data-page="work">';
fera_partial_e( 'page-work' );
echo '</main>';
get_footer();
