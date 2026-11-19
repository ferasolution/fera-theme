<?php
if(!defined('ABSPATH'))exit;
function ff_demos(){return json_decode(file_get_contents(get_theme_file_path('/content/demos.json')),true);}
function ff_install_demos(){
    $ids=get_option('ff_demo_pages',[]);$parent=get_option('ff_demo_parent');
    if(!$parent||!get_post_status($parent)||!get_post_status($parent)||get_post_status($parent)==='trash'||!get_post_meta($parent,'_ff_demo_hub',true)){$parent=wp_insert_post(['post_type'=>'page','post_title'=>'Fera Interactive Experiences','post_name'=>'experience','post_status'=>'publish'],true);if(is_wp_error($parent))return $parent;update_post_meta($parent,'_ff_demo_hub',1);update_option('ff_demo_parent',$parent,false);}
    foreach(ff_demos() as $demo){$key=$demo['id'];if(!empty($ids[$key])&&get_post_status($ids[$key])&&get_post_status($ids[$key])!=='trash'&&get_post_meta($ids[$key],'_ff_demo',true)===$key)continue;$token=$demo['slug'];$id=wp_insert_post(['post_type'=>'page','post_title'=>$demo['title'],'post_name'=>$token,'post_parent'=>$parent,'post_status'=>'publish'],true);if(is_wp_error($id))return $id;update_post_meta($id,'_ff_demo',$key);$ids[$key]=$id;update_option('ff_demo_pages',$ids,false);}update_option('ff_demo_pages',$ids,false);return $ids;
}
function ff_demo_url($key){$ids=get_option('ff_demo_pages',[]);return !empty($ids[$key])?get_permalink($ids[$key]):ff_url('portfolio');}
function ff_showcase($mode='all'){
    echo '<div data-showcase class="fera-showcase"><p class="studio-demo-notice">LIVE DEMONSTRATIONS · Synthetic data · No client endorsements</p>';
    if($mode==='all'){echo '<div class="showcase-filters" aria-label="Filter demonstrations">';foreach(['all'=>'All','software'=>'Software','websites'=>'Websites','workflow'=>'Automation','security'=>'Cybersecurity','marketing'=>'Marketing'] as $key=>$label){echo '<button type="button" data-filter="'.esc_attr($key).'" aria-pressed="'.($key==='all'?'true':'false').'">'.esc_html($label).'</button>';}echo '</div>';}
    echo '<div class="showcase-grid">';
    foreach(ff_demos() as $demo){
        if($mode==='featured'&&!in_array($demo['id'],['accounting','crm','corporate','professional','builder','vulnerabilities'],true))continue;
        if(!in_array($mode,['all','featured'],true)&&$demo['category']!==$mode)continue;
        $url=ff_demo_url($demo['id']);echo '<article class="showcase-project" data-category="'.esc_attr($demo['category']).'"><span class="project-category">'.esc_html($demo['category']).' / concept</span><div class="project-preview" aria-hidden="true"><div class="preview-toolbar"><i></i><i></i><i></i></div><div class="preview-content"><span>'.esc_html(strtoupper($demo['id'])).'</span><div class="preview-chart"></div><div class="preview-lines"></div></div></div><h3>'.esc_html($demo['title']).'</h3><p>'.esc_html($demo['description']).'</p><details><summary>Project brief</summary><p>Working JavaScript, HTML5 and CSS3 demonstration. Production hosting, authentication, databases and integrations require a separately agreed scope. We select a technology stack around requirements, security, scalability, budget and existing infrastructure.</p></details><button class="demo-open" type="button" data-src="'.esc_url($url).'" data-title="'.esc_attr($demo['title']).'">Launch Live Demo ↗</button><a class="demo-direct" aria-label="'.esc_attr('Open '.$demo['title'].' standalone').'" href="'.esc_url($url).'" target="_blank" rel="noopener">Open standalone demo</a></article>';
    }
    echo '</div><div class="demo-stage" hidden><div class="demo-stage-toolbar"><strong class="demo-title"></strong><button type="button" data-device="desktop" aria-pressed="true">Desktop</button><button type="button" data-device="mobile" aria-pressed="false">Mobile</button><button class="demo-close" type="button">Close preview</button></div><div class="demo-frame-wrap"><iframe title="Interactive demonstration" loading="lazy" sandbox="allow-scripts allow-forms allow-downloads allow-popups allow-popups-to-escape-sandbox" referrerpolicy="no-referrer"></iframe></div></div></div>';
}
function ff_render_demo($key){
    $demo=null;foreach(ff_demos() as $item){if($item['id']===$key){$demo=$item;break;}}if(!$demo){status_header(404);exit;}
    [$file,$query]=explode('?',$demo['source'],2);parse_str($query,$params);$value=reset($params);
    if(!in_array($file,['lab.html','app.html','website.html'],true)){status_header(404);exit;}
    $html=file_get_contents(get_theme_file_path('/assets/demos/'.$file));$base=esc_url(trailingslashit(get_theme_file_uri('/assets/demos')));
    $html=preg_replace_callback('/((?:src|href)=")([A-Za-z0-9._-]+\.(?:js|css))(\?[^"<>]*)?"/',function($match){$path=get_theme_file_path('/assets/demos/'.$match[2]);$url=get_theme_file_uri('/assets/demos/'.$match[2]);return $match[1].esc_url(add_query_arg('ver',is_file($path)?filemtime($path):wp_get_theme()->get('Version'),$url)).'"';},$html);
    $html=str_replace("base-uri &#39;none&#39;","base-uri &#39;self&#39;",$html);
    $title=$demo['title'].' | Fera Solution Demonstration';$url=ff_demo_url($key);
    $html=preg_replace('/<title>.*?<\/title>/s','<title>'.esc_html($title).'</title>',$html,1);
    $meta='<meta name="description" content="'.esc_attr($demo['description']).'"><link rel="canonical" href="'.esc_url($url).'"><meta property="og:type" content="website"><meta property="og:title" content="'.esc_attr($title).'"><meta property="og:description" content="'.esc_attr($demo['description']).'"><meta property="og:url" content="'.esc_url($url).'"><meta property="og:image" content="'.esc_url(get_theme_file_uri('/assets/fera-orbit-v2.webp')).'"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="'.esc_attr($title).'"><meta name="twitter:description" content="'.esc_attr($demo['description']).'">';
    $html=str_replace('<head>','<head>'.$meta.'<base href="'.$base.'"><meta name="fera-demo" content="'.esc_attr($value).'"><meta name="fera-contact" content="'.esc_url(ff_url('contact').'#inquiry').'">',$html);
    $html=str_replace(['href="lab.html"','href="app.html"'],'href="'.esc_url(ff_url('portfolio')).'"',$html);
    foreach(['crm'=>'legacy-crm','projects'=>'legacy-projects','inventory'=>'legacy-inventory','workflow'=>'workflow','security'=>'security'] as $mode=>$id){$html=str_replace('href="?app='.$mode.'"','href="'.esc_url(ff_demo_url($id)).'"'.($file==='app.html'&&$value===$mode?' aria-current="page"':''),$html);}$html=str_replace('../../../../../contact/',esc_url(ff_url('contact')),$html);
    header('Content-Type: text/html; charset=utf-8');header('X-Robots-Tag: noindex, nofollow');echo $html;
}
