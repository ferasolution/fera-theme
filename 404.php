<?php
/**
 * Not found.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="svcPage">
	<section class="s-hero pg-hero wp-hero" data-scene="pg-galaxy">
		<nav class="crumbs mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fera-theme' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'fera-theme' ); ?></a><span>/</span><b>404</b></nav>
		<h1 class="s-title"><span class="line"><span><?php esc_html_e( 'Page not', 'fera-theme' ); ?></span></span><span class="line"><span><span class="serif red"><?php esc_html_e( 'found.', 'fera-theme' ); ?></span></span></span></h1>
		<div class="s-row">
			<p class="s-lead" data-fade><?php esc_html_e( 'The page you are looking for has moved or no longer exists.', 'fera-theme' ); ?></p>
			<div class="hero-ctas" data-fade>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-red magnetic"><span class="roll"><span data-t="Back to home"><?php esc_html_e( 'Back to home', 'fera-theme' ); ?></span></span></a>
				<a href="<?php echo esc_url( fera_page_url( 'services' ) ); ?>" class="btn btn-line magnetic"><span class="roll"><span data-t="Our services"><?php esc_html_e( 'Our services', 'fera-theme' ); ?></span></span></a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
