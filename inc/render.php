<?php
if(!defined('ABSPATH'))exit;
function ff_render_nodes($nodes){
    foreach($nodes as $node){
        $s=$node['settings']??[];$classes=$s['css_classes']??$s['_css_classes']??'';$classes=implode(' ',array_map('sanitize_html_class',preg_split('/\s+/',$classes)));
        if(($node['elType']??'')==='container'){
            $tag=in_array($s['html_tag']??'', ['section','article','aside','div'],true)?$s['html_tag']:'div';
            $id=empty($s['_element_id'])?'':' id="'.esc_attr($s['_element_id']).'"';
            echo '<'.$tag.$id.' class="e-con '.$classes.' '.(($s['flex_direction']??'')==='row'?'ff-row':'ff-column').'">';
            ff_render_nodes($node['elements']??[]);echo '</'.$tag.'>';continue;
        }
        $type=$node['widgetType']??'';echo '<div class="elementor-widget elementor-widget-'.esc_attr($type).' '.$classes.'"><div class="elementor-widget-container">';
        if($type==='heading'){$tag=in_array($s['header_size']??'', ['h1','h2','h3','h4','h5','h6'],true)?$s['header_size']:'h2';echo '<'.$tag.' class="elementor-heading-title">'.wp_kses_post($s['title']??'').'</'.$tag.'>';}
        elseif($type==='text-editor'){echo wp_kses_post($s['editor']??'');}
        elseif($type==='button'){echo '<a class="elementor-button" href="'.esc_url($s['link']['url']??ff_url('contact')).'">'.esc_html($s['text']??'Discuss your project').'</a>';}
        elseif($type==='image'){echo '<img src="'.esc_url($s['image']['url']??'').'" alt="'.esc_attr($s['image']['alt']??'Illustrative Fera service diagram').'" width="600" height="360" loading="lazy">';}
        elseif($type==='accordion'){foreach($s['tabs']??[] as $tab){echo '<details class="ff-accordion"><summary>'.esc_html($tab['tab_title']).'</summary>'.wp_kses_post($tab['tab_content']).'</details>';}}
        elseif(str_starts_with($type,'fera-')||str_starts_with($type,'foundry-')){ff_component(preg_replace('/^(?:fera-|foundry-)/','',$type),$s);}
        echo '</div></div>';
    }
}
function ff_diagram($preset){
    $allowed=['home'=>'home-shield','cybersecurity'=>'cybersecurity','web-development'=>'web-development','software-development'=>'software-development','digital-marketing'=>'digital-marketing'];
    $file=$allowed[$preset]??'home-shield';return file_get_contents(get_theme_file_path('/assets/'.$file.'.svg'));
}
function ff_brand_hero($page){
    $support=['privacy-policy'=>['privacy-hero.svg','PRIVACY / PURPOSE / TRUST'],'terms-and-conditions'=>['terms-hero.svg','CLEAR SCOPE / SHARED EXPECTATIONS'],'contact'=>['contact-hero.svg','LET’S CONNECT / BUILD WITH PURPOSE']];
    if(isset($support[$page])){
        echo '<div class="ff-support-scene ff-support-'.esc_attr($page).'">'.file_get_contents(get_theme_file_path('/assets/'.$support[$page][0])).'<p class="ff-visual-caption">'.esc_html($support[$page][1]).'</p></div>';return;
    }
    if($page==='home'){
        echo '<div class="ff-logo-scene"><svg class="ff-logo-orbits" viewBox="0 0 600 500" fill="none" aria-hidden="true"><g class="ff-orbit-ring ff-orbit-outer"><ellipse cx="300" cy="250" rx="268" ry="155" stroke="#98616b"/></g><g class="ff-orbit-ring ff-orbit-inner"><ellipse cx="300" cy="250" rx="268" ry="155" transform="rotate(58 300 250)" stroke="#b8495b"/></g><g class="ff-orbit-ring ff-orbit-third"><ellipse cx="300" cy="250" rx="268" ry="155" transform="rotate(-58 300 250)" stroke="#605c66"/></g><circle cx="78" cy="164" r="5" fill="#ffb2bf"/><circle cx="523" cy="337" r="5" fill="#ffb2bf"/></svg><div class="ff-logo-plate"><img class="ff-logo-art" src="'.esc_url(get_theme_file_uri('/assets/fera-logo.png')).'" width="618" height="622" alt="Fera Solution — original company logo" fetchpriority="high" decoding="async"></div><p class="ff-visual-caption">YOUR IDEAS. OUR ENGINEERING.</p></div>';return;
    }
    if($page==='about'){
        echo '<div class="ff-story-scene"><svg viewBox="0 0 600 440" fill="none" role="img" aria-label="Fera Solution grew from freelance work into a company in 2025, with a vision for practical business technology."><g class="ff-story-orbit ff-orbit-outer"><circle cx="300" cy="212" r="180" stroke="#50515b"/><circle cx="480" cy="212" r="3" fill="#ba7a87"/></g><g class="ff-story-orbit ff-orbit-inner"><circle cx="300" cy="212" r="142" stroke="#7b4856" stroke-dasharray="4 12"/></g><path class="ff-story-path" d="M90 270L240 140L390 270L520 150" stroke="#dd8898" stroke-width="2"/><g fill="#171b20" stroke="#ba7a87" stroke-width="2"><circle cx="90" cy="270" r="18"/><circle cx="240" cy="140" r="28"/><circle cx="390" cy="270" r="18"/><circle cx="520" cy="150" r="12"/></g><circle cx="240" cy="140" r="9" fill="#ffb2bf"/><g fill="#f4f1e9" font-family="Manrope,Arial,sans-serif" font-size="23"><text x="48" y="325">START</text><text x="200" y="87">2025</text><text x="340" y="325">BUILD</text><text x="446" y="105">EVOLVE</text></g></svg><p class="ff-visual-caption">Freelance foundation → Company in 2025 → Thoughtful solutions</p></div>';return;
    }
    echo '<div class="ff-work-scene"><svg viewBox="0 0 600 440" fill="none" role="img" aria-label="Illustration of application, website and automation work connected through the live demonstration gallery."><g class="ff-work-orbit ff-orbit-outer"><ellipse cx="300" cy="218" rx="260" ry="172" stroke="#69414c" stroke-dasharray="150 24"/></g><g class="ff-work-orbit ff-orbit-inner"><ellipse cx="300" cy="218" rx="208" ry="136" stroke="#8b5363" stroke-dasharray="4 16"/></g><path d="M130 135L300 218L472 320" stroke="#b4818d" stroke-width="2" stroke-dasharray="5 8"/><g class="ff-work-layer ff-work-back"><rect x="36" y="34" width="330" height="210" rx="12" fill="#15191f" stroke="#68646f"/><path d="M36 73H366" stroke="#68646f"/><circle cx="58" cy="53" r="4" fill="#df8698"/><rect x="65" y="101" width="75" height="105" rx="5" fill="#37212d"/><path d="M164 119H329M164 144H302M164 169H321" stroke="#9d818a" stroke-width="7"/></g><g class="ff-work-layer ff-work-middle"><rect x="151" y="137" width="330" height="210" rx="12" fill="#1b2027" stroke="#c48795"/><path d="M151 176H481" stroke="#c48795"/><circle cx="173" cy="156" r="4" fill="#ffb2bf"/><path d="M185 304L232 259L279 276L325 230L368 246L438 205" stroke="#eea3b3" stroke-width="3"/><path d="M185 221H271" stroke="#737785" stroke-width="6"/></g><g class="ff-work-layer ff-work-front"><rect x="358" y="281" width="200" height="122" rx="12" fill="#15191f" stroke="#bb8091"/><path d="M378 318H538M378 343H516M378 368H532" stroke="#aaa1ad" stroke-width="6"/></g></svg><p class="ff-visual-caption">Applications. Websites. Automation. Explore the actual interactive work.</p></div>';
}
function ff_component($type,$s=[]){
    if($type==='showcase'){ff_showcase($s['mode']??'all');return;}
    if($type==='inquiry'){ff_inquiry_form($s);return;}
    if($type==='replay'){
        // Marketing uses the shared footer replay control, like the other service pages.
        if(ff_slug()==='digital-marketing')return;
        echo '<button class="watch-service-intro" type="button">'.esc_html($s['label']??'Replay opening').'</button>';return;
    }
    if($type==='opening'){
        if(isset($GLOBALS['ff_opening_state']))return;
        $GLOBALS['ff_opening_state']=($s['enabled']??'yes')==='yes'?'rendered':'disabled';
        $preset=$s['preset']??'home';echo '<span hidden data-fera-background="'.esc_attr($s['background']??'net').'"></span>';
        if(($s['enabled']??'yes')!=='yes')return;
        echo '<section id="welcome-intro" class="welcome-intro '.($preset==='home'?'':'service-opening opening-'.esc_attr($preset)).'" role="dialog" aria-modal="true" aria-labelledby="welcome-title" hidden tabindex="-1"><div class="intro-atmosphere" aria-hidden="true"></div><div class="intro-brand">FERA <span>SOLUTION</span></div><button class="intro-skip" type="button">Skip intro</button><div class="intro-center"><p class="intro-kicker">'.esc_html($s['tagline']??'BUILD. CONNECT. EVOLVE.').'</p><div class="'.($preset==='home'?'shield-stage':'service-intro-graphic').'" aria-hidden="true">'.ff_diagram($preset).'<div class="shield-halo"></div></div><h2 id="welcome-title">'.wp_kses_post($s['title']??'Ideas become working systems.').'</h2><div class="intro-status" role="status"><p>'.esc_html($s['status']??'Ready to explore.').'</p></div></div><div class="intro-bottom">'.esc_html($s['footer_label']??'SOFTWARE / WEBSITES / SECURITY').'</div></section>';return;
    }
    if($type==='visual'){
        $preset=$s['preset']??'home';if($preset==='home'){
            $page=ff_slug();if(in_array($page,['home','about','portfolio','privacy-policy','terms-and-conditions','contact'],true)){ff_brand_hero($page);return;}
            $image=$s['image']['url']??get_theme_file_uri('/assets/fera-orbit-v2.webp');$responsive='';
            if(strpos($image,'fera-orbit-v2.webp')!==false){$image=get_theme_file_uri('/assets/fera-orbit-v2-768.webp');$sources=[];foreach([480,768,1100,1536] as $width)$sources[]=get_theme_file_uri('/assets/fera-orbit-v2'.($width===1536?'':'-'.$width).'.webp').' '.$width.'w';$responsive=' srcset="'.esc_attr(implode(', ',$sources)).'" sizes="(max-width: 760px) 90vw, (max-width: 1440px) 42vw, 600px"';}
            echo '<img class="orbit-art" src="'.esc_url($image).'"'.$responsive.' width="1536" height="1024" alt="'.esc_attr($s['alt']??'Titanium orbital sculpture and illuminated red core').'" loading="eager" decoding="async" fetchpriority="high"><canvas id="network" aria-hidden="true"></canvas><div class="art-label top">'.esc_html($s['overline']??'CONNECTED BY DESIGN').'</div><div class="art-chip chip-one">'.esc_html($s['label_one']??'SECURITY BY DESIGN').'</div><div class="art-chip chip-two">'.esc_html($s['label_two']??'BUILT FOR YOUR BUSINESS').'</div><div class="art-label bottom">'.esc_html($s['caption']??'BUILD / CONNECT / EVOLVE').'</div>';
        }else{echo '<div class="hero-diagram" aria-hidden="true">'.ff_diagram($preset).'</div><p class="diagram-caption">'.esc_html($s['caption']??'').'</p>';}return;
    }
}
