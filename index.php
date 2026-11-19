<?php
if(!defined('ABSPATH'))exit;
// Saved Elementor layouts need a singular content loop, not archive excerpts.
if(is_singular('elementor_library')){
    get_header();echo '<main id="main" class="elementor ff-page">';
    while(have_posts()){the_post();the_content();}
    echo '</main>';get_footer();return;
}
get_header();?><main id="main" class="studio-section e-con"><h1><?php echo is_search()?'Search results':(is_archive()?esc_html(get_the_archive_title()):'Updates'); ?></h1><?php if(have_posts()){while(have_posts()){the_post();?><article><h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2><?php the_excerpt();?></article><?php }the_posts_pagination();}else{echo '<p>No matching posts. Explore our services or contact the Fera team.</p><a href="'.esc_url(ff_url('contact')).'">Discuss your project</a>';}?></main><?php get_footer();
