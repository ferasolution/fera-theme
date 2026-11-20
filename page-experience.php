<?php
/**
 * Interactive demos hub (/experience/).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="svcPage" data-page="experience">
	<section class="s-hero pg-hero" data-scene="pg-galaxy">
		<nav class="crumbs mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fera-theme' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( fera_page_url( 'work' ) ); ?>">Work</a><span>/</span><b>Live demos</b></nav>
		<h1 class="s-title"><span class="line"><span>Try the work,</span></span><span class="line"><span><span class="serif red">live.</span></span></span></h1>
		<div class="s-row">
			<p class="s-lead" data-fade>Interactive demonstrations of the software, websites, automation, security and marketing tools we build, running right in your browser with sample data.</p>
		</div>
	</section>
	<section class="sec demo-sec" data-scene="svcbody">
		<?php echo fera_demo_gallery( 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in fera_demo_gallery(). ?>
	</section>
</main>
<?php
get_footer();
