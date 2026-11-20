<?php
/**
 * Standard page / post layout (blog posts, legal pages, Elementor pages…).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$fera_builder = class_exists( '\Elementor\Plugin' ) && 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true );

	if ( $fera_builder ) :
		?>
		<main id="svcPage" class="elementor-page">
			<?php the_content(); ?>
		</main>
		<?php
		continue;
	endif;
	?>
	<main id="svcPage">
		<article <?php post_class(); ?>>
			<section class="s-hero pg-hero wp-hero" data-scene="pg-logo">
				<nav class="crumbs mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fera-theme' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'fera-theme' ); ?></a><span>/</span>
					<?php if ( is_singular( 'post' ) && get_option( 'page_for_posts' ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'Blog', 'fera-theme' ); ?></a><span>/</span>
					<?php endif; ?>
					<b><?php echo esc_html( wp_trim_words( get_the_title(), 8, '…' ) ); ?></b>
				</nav>
				<h1 class="s-title"><span class="line"><span><?php the_title(); ?></span></span></h1>
				<?php
				if ( is_singular( 'post' ) ) {
					$fera_cats = get_the_category_list( ', ' );
					fera_hero_tail(
						has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 26, '…' ),
						array( get_the_date() . ' · ' . get_the_author(), $fera_cats ? $fera_cats : __( 'Insights', 'fera-theme' ) )
					);
				} else {
					fera_hero_tail(
						has_excerpt() ? get_the_excerpt() : __( 'Fera Solution LLC, a USA-based digital agency in Herndon, Virginia, serving clients worldwide.', 'fera-theme' ),
						array( __( 'Fera Solution LLC', 'fera-theme' ), sprintf( /* translators: %s: date */ __( 'Updated %s', 'fera-theme' ), get_the_modified_date() ) )
					);
				}
				?>
			</section>
			<div class="prose-wrap" data-scene="pg-prose">
				<?php
				if ( has_post_thumbnail() && is_singular( 'post' ) ) {
					the_post_thumbnail( 'large', array( 'class' => 'post-thumb', 'loading' => 'eager', 'fetchpriority' => 'high' ) );
				}
				?>
				<div class="prose">
					<?php
					the_content();
					wp_link_pages();
					?>
				</div>
				<?php
				if ( is_singular( 'post' ) && ( comments_open() || get_comments_number() ) ) {
					comments_template();
				}
				?>
			</div>
		</article>
	</main>
	<?php
endwhile;

get_footer();
