<?php
if (!defined('ABSPATH')) { exit; }
foreach (['setup','render','demos','forms','seo','settings','services'] as $module) { require_once __DIR__.'/inc/'.$module.'.php'; }
add_action('after_setup_theme',function(){
 add_theme_support('title-tag');add_theme_support('post-thumbnails');add_theme_support('custom-logo');add_theme_support('html5',['search-form','gallery','caption','style','script']);register_nav_menus(['foundry_primary'=>'Fera primary navigation']);
});
add_filter('body_class',function($classes){$classes[]='ff-theme';return $classes;});
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('fera-foundry',get_theme_file_uri('/assets/main.min.css'),[],filemtime(get_theme_file_path('/assets/main.min.css')));
 wp_enqueue_style('fera-motion',get_theme_file_uri('/assets/motion.min.css'),['fera-foundry'],filemtime(get_theme_file_path('/assets/motion.min.css')));
 wp_enqueue_script('fera-foundry',get_theme_file_uri('/assets/main.min.js'),[],filemtime(get_theme_file_path('/assets/main.min.js')),['strategy'=>'defer','in_footer'=>true]);
 wp_add_inline_script('fera-foundry','window.FERA_FOUNDRY='.wp_json_encode(['bootstrap'=>rest_url('fera-foundry/v1/bootstrap'),'inquiry'=>rest_url('fera-foundry/v1/inquiry'),'assistant'=>rest_url('fera-foundry/v1/assistant'),'aiEnabled'=>get_option('ff_ai')==='1','tawkProperty'=>get_option('ff_tawk_property',''),'tawkWidget'=>get_option('ff_tawk_widget',''),'tawkOwner'=>get_option('ff_tawk_owner','none')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';','before');
 if(in_array(ff_slug(),['home','software-development','web-development','web-applications','cybersecurity','digital-marketing','about'],true))wp_enqueue_script('fera-atmosphere',get_theme_file_uri('/assets/backgrounds.min.js'),[],filemtime(get_theme_file_path('/assets/backgrounds.min.js')),['strategy'=>'defer','in_footer'=>true]);
});
add_action('elementor/widgets/register',function($manager){require_once __DIR__.'/inc/elementor.php';foreach(['FF_Opening','FF_Visual','FF_Inquiry','FF_Replay','FF_Showcase'] as $class)$manager->register(new $class());});
add_action('send_headers',function(){
 if(is_admin()||headers_sent())return;
 $existing=[];
 foreach(headers_list() as $line){$existing[strtolower(trim(explode(':',$line,2)[0]))]=true;}
 $defaults=['X-Content-Type-Options'=>'nosniff','Referrer-Policy'=>'strict-origin-when-cross-origin','X-Frame-Options'=>'SAMEORIGIN','Content-Security-Policy'=>"frame-ancestors 'self'; object-src 'none'; base-uri 'self'"];
 foreach($defaults as $name=>$value){if(!isset($existing[strtolower($name)]))header($name.': '.$value);}
});
add_filter('elementor/widget/render_content',function($content,$widget){if($widget->get_name()!=='accordion'||!class_exists('WP_HTML_Tag_Processor'))return $content;$tags=new WP_HTML_Tag_Processor($content);while($tags->next_tag()){if($tags->get_attribute('role')==='button'&&$tags->get_attribute('aria-selected')!==null)$tags->remove_attribute('aria-selected');}return $tags->get_updated_html();},10,2);

// Bundled Manrope is the approved font; unrelated pages retain their own typography.
add_filter('elementor/frontend/print_google_fonts',function($print){return ff_slug()?false:$print;});
add_action('wp_head',function(){if(!ff_slug())return;echo '<link rel="preload" href="'.esc_url(get_theme_file_uri('/assets/manrope-latin.woff2')).'" as="font" type="font/woff2" crossorigin>';},2);
add_action('wp',function(){if(!ff_slug())return;remove_action('wp_head','print_emoji_detection_script',7);remove_action('wp_print_styles','print_emoji_styles');});
add_action('wp_enqueue_scripts',function(){if(!ff_slug())return;$id=get_queried_object_id();$source=get_post_field('post_content',$id).' '.get_post_meta($id,'_elementor_data',true);if(!preg_match('/contact-form-7|wpcf7|cf7/i',$source)){wp_dequeue_style('contact-form-7');wp_dequeue_script('contact-form-7');wp_dequeue_script('wpcf7-recaptcha');}},100);
