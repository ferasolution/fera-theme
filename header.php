<?php
/**
 * Site header: document head, global overlays, navigation.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'loading' ); ?> data-route="<?php echo esc_attr( fera_route() ); ?>" data-nav="<?php echo esc_attr( fera_nav_key() ); ?>">
<?php wp_body_open(); ?>
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'fera-theme' ); ?></a>
<?php fera_partial_e( 'header' ); ?>
<span id="content" tabindex="-1"></span>
