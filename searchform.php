<?php
/**
 * Search form.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr" for="fera-s"><?php esc_html_e( 'Search', 'fera-theme' ); ?></label>
	<input id="fera-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'fera-theme' ); ?>">
	<button type="submit" class="btn btn-red"><span class="roll"><span data-t="Search"><?php esc_html_e( 'Search', 'fera-theme' ); ?></span></span></button>
</form>
