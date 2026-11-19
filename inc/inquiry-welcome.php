<?php
if(!defined('ABSPATH'))exit;
// A short-lived signed receipt contains no inquiry data and is not authentication.
function ff_issue_welcome_receipt($inquiry=[]){
    $expires=time()+600;$value=$expires.'.'.hash_hmac('sha256','ff_welcome:'.$expires,wp_salt('auth'));
    setcookie('ff_inquiry_received',$value,['expires'=>$expires,'path'=>'/','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']);
    // Personalization is encrypted, authenticated, short-lived and never placed in a URL.
    if(function_exists('openssl_encrypt')&&in_array('aes-256-gcm',openssl_get_cipher_methods(),true)){
        $iv=random_bytes(12);$tag='';
        $payload=wp_json_encode(['expires'=>$expires,'name'=>$inquiry['name']??'','email'=>$inquiry['email']??'']);
        $cipher=openssl_encrypt($payload,'aes-256-gcm',hash('sha256',wp_salt('auth').'ff_welcome_details',true),OPENSSL_RAW_DATA,$iv,$tag,$value);
        if(is_string($cipher))setcookie('ff_inquiry_details',base64_encode($iv.$tag.$cipher),['expires'=>$expires,'path'=>'/','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']);
    }
    return add_query_arg('ff_welcome','1',home_url('/'));
}
function ff_welcome_details(){
    if(!ff_has_welcome_receipt()||!function_exists('openssl_decrypt'))return [];
    $raw=$_COOKIE['ff_inquiry_details']??'';if(!is_string($raw)||strlen($raw)>3000)return [];
    $packed=base64_decode($raw,true);if(!is_string($packed)||strlen($packed)<29)return [];
    $plain=openssl_decrypt(substr($packed,28),'aes-256-gcm',hash('sha256',wp_salt('auth').'ff_welcome_details',true),OPENSSL_RAW_DATA,substr($packed,0,12),substr($packed,12,16),$_COOKIE['ff_inquiry_received']);
    $data=is_string($plain)?json_decode($plain,true):null;
    if(!is_array($data)||!is_int($data['expires']??null)||$data['expires']<=time()||$data['expires']>time()+600||!is_string($data['name']??null)||strlen($data['name'])>600||!is_string($data['email']??null)||!is_email($data['email']))return [];
    return ['name'=>sanitize_text_field($data['name']),'email'=>sanitize_email($data['email'])];
}
function ff_has_welcome_receipt(){
    $value=$_COOKIE['ff_inquiry_received']??'';
    if(!is_string($value)||!preg_match('/^(\d{10})\.([a-f0-9]{64})$/D',$value,$parts))return false;
    $expires=(int)$parts[1];
    return $expires>time()&&$expires<=time()+600&&hash_equals(hash_hmac('sha256','ff_welcome:'.$expires,wp_salt('auth')),$parts[2]);
}
add_filter('query_vars',function($vars){$vars[]='ff_welcome';return $vars;});
add_action('template_redirect',function(){
    if(get_query_var('ff_welcome')!=='1')return;
    if(!defined('DONOTCACHEPAGE'))define('DONOTCACHEPAGE',true);
    nocache_headers();header('X-Robots-Tag: noindex, nofollow');
    add_filter('wp_robots',function($robots){return ['noindex'=>true,'nofollow'=>true];});
    add_filter('pre_get_document_title',fn()=>'Welcome | Fera Solution',999);
    // Keep the existing theme layout; a direct visit without a receipt is neutral.
    $received=ff_has_welcome_receipt();$details=ff_welcome_details();get_header();
    $heading=$received?(!empty($details['name'])?'Thank you, '.$details['name'].'!':'Thank you for contacting us!'):'Welcome to Fera Solution';
    echo '<main id="main" class="elementor ff-page"><section class="studio-section e-con"><span class="eyebrow">FERA SOLUTION</span><h1>'.esc_html($heading).'</h1>';
    if($received){
        echo '<p>Thank you for submitting your inquiry. Your inquiry was accepted for email delivery.</p><p>Our team will review your request and contact you shortly'.(!empty($details['email'])?' at <strong>'.esc_html($details['email']).'</strong>':'').'.</p><p>Inbox delivery is not yet confirmed; if you do not hear from us, please contact sales@ferasolution.com.</p>';
    }else{
        echo '<p>No recent successful inquiry can be confirmed in this browser. Please use our contact form if you would like to discuss a project.</p><a class="button" href="'.esc_url(ff_url('contact').'#inquiry').'">Contact our team</a> ';
    }
    echo '<a class="button" href="'.esc_url(ff_url('home')).'">Return to homepage</a></section></main>';
    get_footer();exit;
},0);
