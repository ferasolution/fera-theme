<?php
/**
 * Contact page (design template).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="svcPage" data-page="contact">';
fera_partial_e( 'page-contact' );
echo '</main>';
get_footer();
