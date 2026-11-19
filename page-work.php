<?php
/**
 * Work page (design template) + featured live demos.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();
echo '<main id="svcPage" data-page="work">';
fera_partial_e( 'page-work' );
if ( function_exists( 'fera_demo_gallery' ) && fera_demo_hub_id() ) :
	?>
	<section class="sec demo-sec" data-scene="svcbody" id="live-demos">
		<div class="inc-head"><div><p class="label mono"><b>(02)</b> Live demos</p>
			<h2 class="h2" data-split><span class="line"><span>Try it in your</span></span><span class="line"><span><span class="serif">browser.</span></span></span></h2></div>
			<p data-reveal>Interactive demonstrations of apps, websites, automation and security tools — with sample data. <a class="u-link" href="<?php echo esc_url( get_permalink( fera_demo_hub_id() ) ); ?>">See all demos →</a></p></div>
		<?php echo fera_demo_gallery( 'featured' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in fera_demo_gallery(). ?>
	</section>
	<?php
endif;
echo '</main>';
get_footer();
