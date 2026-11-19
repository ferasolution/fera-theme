<?php
/**
 * Blog, archives and search results.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_search() ) {
	/* translators: %s: search query. */
	$fera_title = sprintf( __( 'Results for “%s”', 'fera-theme' ), get_search_query() );
} elseif ( is_archive() ) {
	$fera_title = wp_strip_all_tags( get_the_archive_title() );
} else {
	$fera_title = get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Insights', 'fera-theme' );
}
?>
<main id="svcPage">
	<?php $fera_is_blog_home = ! is_search() && ! is_archive(); ?>
	<section class="s-hero pg-hero<?php echo $fera_is_blog_home ? '' : ' wp-hero'; ?>" data-scene="pg-wave">
		<nav class="crumbs mono" aria-label="<?php esc_attr_e( 'Breadcrumb', 'fera-theme' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'fera-theme' ); ?></a><span>/</span><b><?php echo esc_html( $fera_title ); ?></b></nav>
		<?php if ( $fera_is_blog_home ) : ?>
			<h1 class="s-title"><span class="line"><span><?php esc_html_e( 'Ideas worth', 'fera-theme' ); ?></span></span><span class="line hl-line"><span><span class="hl-wrap" data-text="<?php esc_attr_e( 'sharing.', 'fera-theme' ); ?>"><span class="hl"><?php esc_html_e( 'sharing.', 'fera-theme' ); ?></span></span></span></span></h1>
		<?php else : ?>
			<h1 class="s-title"><span class="line"><span><?php echo esc_html( $fera_title ); ?></span></span></h1>
		<?php endif; ?>
		<?php
		global $wp_query;
		$fera_count = (int) $wp_query->found_posts;
		/* translators: %d: number of articles. */
		$fera_count_label = sprintf( _n( '%d article', '%d articles', $fera_count, 'fera-theme' ), $fera_count );
		if ( is_search() ) {
			fera_hero_tail( get_search_form( array( 'echo' => false ) ), array( __( 'Search results', 'fera-theme' ), $fera_count_label ), true );
		} elseif ( is_archive() ) {
			$fera_desc = wp_strip_all_tags( get_the_archive_description() );
			fera_hero_tail( $fera_desc ? $fera_desc : __( 'Articles, guides and news from the Fera Solution team.', 'fera-theme' ), array( __( 'Insights · Guides · News', 'fera-theme' ), $fera_count_label ) );
		} else {
			fera_hero_tail( __( 'Practical insights on cybersecurity, websites, software and digital growth — written by the Fera Solution team for businesses worldwide.', 'fera-theme' ), array( __( 'Insights · Guides · News', 'fera-theme' ), $fera_count_label ) );
		}
		?>
	</section>
	<div class="post-list">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<a class="post-card" href="<?php the_permalink(); ?>" data-cursor="Read">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) );
					}
					?>
					<span class="mono"><?php echo esc_html( get_the_date() ); ?></span>
					<h2><?php the_title(); ?></h2>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
				</a>
				<?php
			endwhile;
			$fera_links = paginate_links( array( 'type' => 'plain' ) );
			if ( $fera_links ) :
				echo '<nav class="pager" aria-label="' . esc_attr__( 'Pagination', 'fera-theme' ) . '">' . wp_kses_post( $fera_links ) . '</nav>';
			endif;
		else :
			echo '<p class="prose">' . esc_html__( 'Nothing found yet.', 'fera-theme' ) . '</p>';
		endif;
		?>
	</div>
</main>
<?php
get_footer();
