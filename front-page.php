<?php
/**
 * Home page.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="top">';
fera_partial_e( 'home' );
echo '</main>';
get_footer();
