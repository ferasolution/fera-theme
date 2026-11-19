<?php
/**
 * Fera Theme — bootstrap.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'FERA_THEME_VERSION', '1.0.2' );
define( 'FERA_DIR', get_template_directory() );
define( 'FERA_URI', get_template_directory_uri() );

require FERA_DIR . '/inc/helpers.php';
require FERA_DIR . '/inc/setup.php';
require FERA_DIR . '/inc/assets.php';
require FERA_DIR . '/inc/seo.php';
require FERA_DIR . '/inc/seo-meta.php';
require FERA_DIR . '/inc/enquiry.php';
require FERA_DIR . '/inc/ai-chat.php';
require FERA_DIR . '/inc/redirects.php';
require FERA_DIR . '/inc/demos.php';
