<?php
if(!defined('ABSPATH'))exit;
function ff_seo_plugin(){foreach(['WPSEO_VERSION','RANK_MATH_VERSION','SEOPRESS_VERSION','AIOSEO_VERSION','THE_SEO_FRAMEWORK_VERSION'] as $constant)if(defined($constant))return true;return false;}
function ff_seo_data(){ $slug=ff_slug();$all=json_decode(file_get_contents(get_theme_file_path('/assets/seo.json')),true);return $all[$slug]??['title'=>get_the_title().' | Fera Solution','description'=>'Discuss tailored software, websites and security-minded technology with Fera Solution in Herndon, Northern Virginia.'];}
add_filter('pre_get_document_title',function($title){if(!ff_seo_plugin()&&(ff_slug()||is_front_page()))return ff_seo_data()['title'];return $title;});
add_action('wp',function(){if(!ff_seo_plugin()&&(ff_slug()||is_front_page()))remove_action('wp_head','rel_canonical');});
add_action('wp_head',function(){
    if(ff_seo_plugin()||!(ff_slug()||is_front_page()))return;
    $data=ff_seo_data();$slug=ff_slug();$url=ff_url($slug?:'home');$image=get_theme_file_uri('/assets/fera-orbit-v2.webp');
    echo '<meta name="description" content="'.esc_attr($data['description']).'"><link rel="canonical" href="'.esc_url($url).'">';
    foreach(['og:type'=>'website','og:title'=>$data['title'],'og:description'=>$data['description'],'og:url'=>$url,'og:image'=>$image,'og:image:alt'=>'Fera Solution titanium orbital artwork','og:site_name'=>'Fera Solution'] as $key=>$value)echo '<meta property="'.esc_attr($key).'" content="'.esc_attr($value).'">';
    foreach(['twitter:card'=>'summary_large_image','twitter:title'=>$data['title'],'twitter:description'=>$data['description'],'twitter:image'=>$image] as $key=>$value)echo '<meta name="'.esc_attr($key).'" content="'.esc_attr($value).'">';
    foreach(['ff_google_verification'=>'google-site-verification','ff_bing_verification'=>'msvalidate.01'] as $option=>$name){$token=get_option($option);if(is_string($token)&&preg_match('/^[A-Za-z0-9_-]{1,200}$/D',$token))echo '<meta name="'.esc_attr($name).'" content="'.esc_attr($token).'">';}
    $base=home_url('/');$org=['@type'=>'Organization','@id'=>$base.'#organization','name'=>'Fera Solution','foundingDate'=>'2025','founder'=>['@type'=>'Person','name'=>'Faizan Hasan'],'url'=>$base,'logo'=>get_theme_file_uri('/assets/fera-logo.png'),'telephone'=>'+1-337-252-4040','email'=>'contact@ferasolution.com','address'=>['@type'=>'PostalAddress','addressLocality'=>'Herndon','addressRegion'=>'VA','addressCountry'=>'US']];
    $graph=[$org,['@type'=>'WebSite','@id'=>$base.'#website','url'=>$base,'name'=>'Fera Solution','publisher'=>['@id'=>$base.'#organization']]];
    if(in_array($slug,['software-development','web-development','web-applications','cybersecurity','digital-marketing'],true))$graph[]=['@type'=>'Service','name'=>ff_titles()[$slug],'description'=>$data['description'],'url'=>$url,'provider'=>['@id'=>$base.'#organization'],'areaServed'=>['@type'=>'Country','name'=>'United States']];
    if($slug&&$slug!=='home')$graph[]=['@type'=>'BreadcrumbList','itemListElement'=>[['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>$base],['@type'=>'ListItem','position'=>2,'name'=>ff_titles()[$slug],'item'=>$url]]];
    echo '<script type="application/ld+json">'.wp_json_encode(['@context'=>'https://schema.org','@graph'=>$graph],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>';
});
add_filter('wp_robots',function($robots){if(wp_get_environment_type()!=='production'||is_search()||is_404()||get_post_meta(get_queried_object_id(),'_ff_demo',true)||get_post_meta(get_queried_object_id(),'_ff_demo_hub',true)){$robots['noindex']=true;unset($robots['index']);}return $robots;});
add_filter('wp_sitemaps_posts_query_args',function($args,$type){if($type==='page'){$args['post__not_in']=array_merge($args['post__not_in']??[],array_values(get_option('ff_demo_pages',[])),[(int)get_option('ff_demo_parent')]);}return $args;},10,2);
add_filter('robots_txt',function($text,$public){if(wp_get_environment_type()!=='production')return "User-agent: *\nDisallow: /\n";if($public&& !ff_seo_plugin())$text.="\nSitemap: ".home_url('/wp-sitemap.xml')."\n";return $text;},10,2);

// AIOSEO remains the single metadata owner; fill only an empty description.
add_filter('aioseo_description',function($description){if(ff_slug()&&(!is_string($description)||trim($description)===''))return ff_seo_data()['description'];return $description;});

add_filter('aioseo_sitemap_exclude_posts',function($ids){return array_values(array_unique(array_merge(is_array($ids)?$ids:[],array_map('intval',array_values(get_option('ff_demo_pages',[]))),[(int)get_option('ff_demo_parent')])));});
