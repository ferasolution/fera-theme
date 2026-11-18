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
				<?php if ( has_excerpt() ) : ?>
					<div class="s-row"><p class="s-lead" data-fade><?php echo esc_html( get_the_excerpt() ); ?></p></div>
				<?php endif; ?>
				<?php if ( is_singular( 'post' ) ) : ?>
					<div class="post-meta mono" data-fade>
						<span><?php echo esc_html( get_the_date() ); ?></span>
						<span><?php echo esc_html( get_the_author() ); ?></span>
						<?php
						$fera_cats = get_the_category_list( ', ' );
						if ( $fera_cats ) {
							echo '<span>' . wp_kses_post( $fera_cats ) . '</span>';
						}
						?>
					</div>
				<?php endif; ?>
			</section>
			<div class="prose-wrap">
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
