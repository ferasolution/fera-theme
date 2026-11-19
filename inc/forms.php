<?php
if(!defined('ABSPATH'))exit;
require_once __DIR__.'/inquiry-welcome.php';
function ff_services(){return ['Software development','Web development','Cybersecurity','Digital marketing','Multiple services / Not sure yet'];}
function ff_inquiry_form($s=[]){
    echo '<form id="inquiry" method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ff_native_inquiry"><div class="form-heading"><span class="form-badge">LET’S BUILD SOMETHING GREAT</span><h3>'.esc_html($s['heading']??'Request a project quote').'</h3></div>';
    echo '<label>Inquiry type<select name="department"><option value="sales">New project / Sales</option><option value="support">Existing customer / Support</option><option value="contact">General inquiry</option></select></label><div class="form-row"><label>Your name<input name="name" autocomplete="name" required maxlength="150"></label><label>Email address<input type="email" name="email" autocomplete="email" required maxlength="254"></label></div><label>'.esc_html($s['phone_label']??'Phone number').' (required)<input type="tel" name="phone" inputmode="tel" autocomplete="tel" maxlength="32" required></label><label>Company (optional)<input name="company" autocomplete="organization" maxlength="150"></label><label>What can we help with?<select name="service">';
    foreach(ff_services() as $service){echo '<option '.selected($s['service']??'Multiple services / Not sure yet',$service,false).'>'.esc_html($service).'</option>';}
    echo '</select></label><label>Preferred Technology Stack (Optional)<select name="technology_preference"><option value="recommend">Recommend the best stack for my project</option><option value="specific">I have specific technology requirements</option></select></label><label>Preferred languages, frameworks, platforms or integrations (optional)<textarea name="preferred_technology" maxlength="500" rows="2"></textarea></label><label>Tell us about your project<textarea name="message" required maxlength="3000" rows="5" placeholder="'.esc_attr($s['message_placeholder']??'Your requirements, priorities and existing systems'). '"></textarea></label><label class="honey" aria-hidden="true">Leave empty<input name="website_url" autocomplete="off" tabindex="-1"></label><label class="consent"><input type="checkbox" name="consent" required> I agree to be contacted about this inquiry.</label><button type="submit" class="button">'.esc_html($s['button_label']??'Send project inquiry').'</button><p class="form-note">Avoid passwords or sensitive account information. If the form cannot send, email sales@ferasolution.com or call 337-252-4040.</p><p id="form-status" role="status" aria-live="polite"></p><p class="form-note">We use your details to respond to this inquiry. No separate lead database is added by this theme. Please review the <a href="'.esc_url(ff_url('privacy-policy')).'">Privacy Policy</a>.</p></form>';
}
function ff_validate_inquiry($data){
    if(!is_array($data))return new WP_Error('ff_input','Invalid request.',['status'=>400]);
    foreach(['name','email','phone','service','department','message','consent'] as $field){if(!isset($data[$field])||!is_string($data[$field]))return new WP_Error('ff_required','Complete all required fields, including phone number.',['status'=>400]);}
    foreach(['company','technology_preference','preferred_technology','website_url'] as $field){if(isset($data[$field])&&!is_string($data[$field]))return new WP_Error('ff_input','Please check the form fields.',['status'=>400]);}
    $phone=trim($data['phone']);$digits=preg_replace('/\D/','',$phone);
    if(strlen($phone)>32||!preg_match('/^\+?[0-9(][0-9() .-]{5,30}$/D',$phone)||strlen($digits)<7||strlen($digits)>15)return new WP_Error('ff_phone','Enter a valid phone number.',['status'=>400]);
    if(!empty($data['website_url'])||$data['consent']!=='on'||!is_email($data['email'])||strlen($data['email'])>254||strlen($data['name'])>600||strlen($data['message'])>12000||!trim($data['name'])||!trim($data['message'])||!in_array($data['service'],ff_services(),true)||!in_array($data['department'],['sales','support','contact'],true))return new WP_Error('ff_invalid','Please check the required details and consent.',['status'=>400]);
    $preference=$data['technology_preference']??'recommend';$technology=$data['preferred_technology']??'';
    if(!in_array($preference,['recommend','specific'],true)||strlen($technology)>2000||strlen($data['company']??'')>600)return new WP_Error('ff_stack','Check the optional technology details.',['status'=>400]);
    return ['name'=>sanitize_text_field($data['name']),'email'=>sanitize_email($data['email']),'phone'=>sanitize_text_field($phone),'company'=>sanitize_text_field($data['company']??''),'service'=>$data['service'],'department'=>$data['department'],'message'=>sanitize_textarea_field($data['message']),'technology_preference'=>$preference,'preferred_technology'=>sanitize_textarea_field($technology),'consent'=>'on','website_url'=>''];
}
function ff_gate($request){
    if(strlen($request->get_body()??'')>16000)return new WP_Error('ff_size','Request too large.',['status'=>413]);
    $nonce=$request->get_param('nonce');if(!is_string($nonce)||!wp_verify_nonce($nonce,'ff_public'))return new WP_Error('ff_nonce','Refresh the form and try again.',['status'=>403]);return true;
}
function ff_guard($kind,$callback){
    $lock='ff_busy_'.$kind;if(!add_option($lock,time(),'',false))return new WP_Error('ff_busy','Another request is being processed. Please try shortly.',['status'=>429]);
    try{
        $ip=hash('sha256',($_SERVER['REMOTE_ADDR']??'unknown').wp_salt('nonce'));$key='ff_rate_'.$kind.'_'.$ip;$ipcount=(int)get_transient($key);$max=$kind==='mail'?5:15;
        if($ipcount>=$max)return new WP_Error('ff_rate','Too many requests. Please contact us directly.',['status'=>429]);
        set_transient($key,$ipcount+1,$kind==='mail'?900:600);
        $usage=get_option('ff_usage_'.$kind,[]);if(!is_array($usage))$usage=[];$month=gmdate('Y-m');$day=gmdate('Y-m-d');
        if(($usage['month']??'')!==$month)$usage=['month'=>$month,'monthly'=>0,'reserved_cents'=>0];
        if(($usage['day']??'')!==$day){$usage['day']=$day;$usage['daily']=0;}
        $daily=$kind==='mail'?(defined('FERA_MAIL_DAILY_REQUESTS')?max(0,min(500,(int)FERA_MAIL_DAILY_REQUESTS)):50):(defined('FERA_AI_DAILY_REQUESTS')?max(0,min(100,(int)FERA_AI_DAILY_REQUESTS)):10);
        if((int)($usage['daily']??0)>=$daily)return new WP_Error('ff_allowance','Online requests reached their allowance. Please call or email us.',['status'=>429]);
        if($kind==='ai'){
            $monthly=defined('FERA_AI_MONTHLY_REQUESTS')?max(0,min(1000,(int)FERA_AI_MONTHLY_REQUESTS)):100;
            $budget=defined('FERA_AI_MONTHLY_BUDGET_CENTS')?max(0,min(5000,(int)FERA_AI_MONTHLY_BUDGET_CENTS)):500;
            if(($usage['monthly']??0)>=$monthly||($usage['reserved_cents']??0)+5>$budget)return new WP_Error('ff_budget','Assistant allowance reached. Please contact our team.',['status'=>429]);
            if((int)get_option('ff_circuit_until')>time())return new WP_Error('ff_circuit','Assistant is temporarily unavailable. Please contact our team.',['status'=>503]);
            $usage['reserved_cents']=($usage['reserved_cents']??0)+5;
        }
        $usage['daily']=(int)($usage['daily']??0)+1;$usage['monthly']=($usage['monthly']??0)+1;update_option('ff_usage_'.$kind,$usage,false);
        try{return $callback();}catch(Throwable $error){return new WP_Error('ff_unavailable','The connection is unavailable. Your request was not accepted. Please contact us directly.',['status'=>503]);}
    }finally{delete_option($lock);}
}
function ff_send_inquiry($request){
    $valid=ff_validate_inquiry($request->get_params());if(is_wp_error($valid))return $valid;
    if(get_option('ff_email')!=='1')return new WP_Error('ff_disabled','Email delivery is not connected. Please email sales@ferasolution.com.',['status'=>503]);
    return ff_guard('mail',function()use($valid){
        $body="Fera project inquiry\n";foreach(['name','email','phone','company','service','technology_preference','preferred_technology','message'] as $field){$body.=ucwords(str_replace('_',' ',$field)).': '.$valid[$field]."\n";}
        $sent=wp_mail($valid['department'].'@ferasolution.com','Fera website inquiry: '.$valid['service'],$body,['Content-Type: text/plain; charset=UTF-8','Reply-To: '.$valid['email']]);
        if(!$sent)return new WP_Error('ff_delivery','Email was not accepted. Your inquiry is not stored. Please retry or email us directly.',['status'=>503]);
        return ['message'=>'Your inquiry was accepted by the email connection for sending. Inbox delivery is not yet confirmed. If you do not receive a reply, please contact sales@ferasolution.com.','redirect'=>ff_issue_welcome_receipt($valid)];
    });
}
function ff_ai_key(){ $provider=get_option('ff_provider','openai');$name=$provider==='gemini'?'FERA_GEMINI_API_KEY':'FERA_OPENAI_API_KEY';return defined($name)?constant($name):getenv($name); }
function ff_assistant($request){
    $message=$request->get_param('message');$history=$request->get_param('history')??[];
    if(!is_string($message)||!trim($message)||strlen($message)>3200||!is_array($history)||count($history)>6)return new WP_Error('ff_chat_input','Please use a short question.',['status'=>400]);
    foreach($history as $item){if(!is_array($item)||!in_array($item['role']??'', ['user','assistant'],true)||!is_string($item['content']??null)||strlen($item['content'])>3200)return new WP_Error('ff_chat_input','Please start a new conversation.',['status'=>400]);}
    if(get_option('ff_ai')!=='1'||!ff_ai_key())return new WP_Error('ff_ai_disabled','AI is not connected. Please speak with our team.',['status'=>503]);
    return ff_guard('ai',function()use($message,$history){
        $instruction='You are Fera Solution’s website assistant. Discuss software development, websites, automation and confirmed cybersecurity services. Herndon VA USA, 337-252-4040, sales@ferasolution.com. Fera Solution offers human live chat on this website through tawk.to. To request a human agent, tell visitors to open Let’s talk and click Speak with our team in the chat panel. Do not say Fera Solution has no live chat, even if an earlier assistant message said so. You cannot check agent availability or open the human chat yourself: do not claim a transfer has happened, an agent is online, instant replies or 24/7 coverage. If agents are offline or live chat cannot connect, suggest the inquiry form, phone or email. Do not invent clients, prices, certifications or response times. Ask visitors to use the inquiry form for scope. Do not request sensitive information. Never claim to scan, book, pay or access external systems.';
        if(get_option('ff_provider','openai')==='gemini'){
            $model=defined('FERA_GEMINI_MODEL')?FERA_GEMINI_MODEL:'gemini-2.5-flash-lite';if(!in_array($model,['gemini-2.5-flash-lite','gemini-2.5-flash'],true))return new WP_Error('ff_model','Unsupported model configuration.',['status'=>503]);
            $contents=[];foreach($history as $item)$contents[]=['role'=>$item['role']==='assistant'?'model':'user','parts'=>[['text'=>$item['content']]]];$contents[]=['role'=>'user','parts'=>[['text'=>$message]]];
            $response=wp_remote_post('https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent',['timeout'=>30,'headers'=>['Content-Type'=>'application/json','x-goog-api-key'=>ff_ai_key()],'body'=>wp_json_encode(['systemInstruction'=>['parts'=>[['text'=>$instruction]]],'contents'=>$contents,'generationConfig'=>['maxOutputTokens'=>400]])]);
            $data=is_wp_error($response)?[]:json_decode(wp_remote_retrieve_body($response),true);$reply=$data['candidates'][0]['content']['parts'][0]['text']??'';
        }else{
            $model=defined('FERA_OPENAI_MODEL')?FERA_OPENAI_MODEL:'gpt-4.1-mini';if(!in_array($model,['gpt-4.1-mini','gpt-4.1-nano'],true))return new WP_Error('ff_model','Unsupported model configuration.',['status'=>503]);
            $messages=array_merge([['role'=>'system','content'=>$instruction]],$history,[['role'=>'user','content'=>$message]]);
            $response=wp_remote_post('https://api.openai.com/v1/chat/completions',['timeout'=>30,'headers'=>['Content-Type'=>'application/json','Authorization'=>'Bearer '.ff_ai_key()],'body'=>wp_json_encode(['model'=>$model,'messages'=>$messages,'max_tokens'=>400])]);
            $data=is_wp_error($response)?[]:json_decode(wp_remote_retrieve_body($response),true);$reply=$data['choices'][0]['message']['content']??'';
        }
        if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200||!is_string($reply)||!trim($reply)){$fails=(int)get_option('ff_ai_failures')+1;update_option('ff_ai_failures',$fails,false);if($fails>=3)update_option('ff_circuit_until',time()+600,false);return new WP_Error('ff_provider','Assistant unavailable. Please contact our team.',['status'=>503]);}
        update_option('ff_ai_failures',0,false);return ['reply'=>sanitize_textarea_field(wp_html_excerpt($reply,6000,''))];
    });
}
add_action('rest_api_init',function(){
    register_rest_route('fera-foundry/v1','/bootstrap',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>function(){$r=new WP_REST_Response(['nonce'=>wp_create_nonce('ff_public')]);$r->header('Cache-Control','no-store, private, max-age=0');return $r;}]);
    register_rest_route('fera-foundry/v1','/inquiry',['methods'=>'POST','permission_callback'=>'ff_gate','callback'=>'ff_send_inquiry']);
    register_rest_route('fera-foundry/v1','/assistant',['methods'=>'POST','permission_callback'=>'ff_gate','callback'=>'ff_assistant']);
});
add_action('admin_post_nopriv_ff_native_inquiry','ff_native_inquiry');add_action('admin_post_ff_native_inquiry','ff_native_inquiry');
function ff_native_inquiry(){
    if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){status_header(405);exit;}
    if((int)($_SERVER['CONTENT_LENGTH']??0)>64000){status_header(413);wp_die('Request too large.');}
    nocache_headers();$data=wp_unslash($_POST);$valid=ff_validate_inquiry($data);
    if(is_wp_error($valid)){status_header(400);wp_die(esc_html($valid->get_error_message()));}
    if(!isset($data['nonce'])){
        header('Content-Type: text/html; charset=utf-8');echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Confirm your Fera inquiry</title><body><main><h1>Confirm your inquiry</h1><p>Submit your entered project details to Fera Solution. Nothing has been sent or stored yet.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ff_native_inquiry">';
        foreach($valid as $key=>$value)echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        echo '<input type="hidden" name="nonce" value="'.esc_attr(wp_create_nonce('ff_public')).'"><button type="submit">Send inquiry</button></form><a href="'.esc_url(ff_url('contact')).'">Back to contact</a></main></body></html>';exit;
    }
    $request=new WP_REST_Request('POST');$request->set_body_params($data);$gate=ff_gate($request);$result=is_wp_error($gate)?$gate:ff_send_inquiry($request);
    if(is_wp_error($result)){
        status_header((int)($result->get_error_data()['status']??400));header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="en"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Fera inquiry needs attention</title><body><main><h1>Inquiry not accepted</h1><p role="alert">'.esc_html($result->get_error_message()).'</p><p>Your details are not stored. You can retry below or contact sales@ferasolution.com.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="ff_native_inquiry">';
        foreach($valid as $key=>$value)echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
        echo '<input type="hidden" name="nonce" value="'.esc_attr(wp_create_nonce('ff_public')).'"><button type="submit">Retry inquiry</button></form><details><summary>Your entered inquiry</summary><pre>'.esc_html($valid['message']).'</pre></details><a href="'.esc_url(ff_url('contact')).'">Back to contact</a></main></body></html>';exit;
    }
    wp_safe_redirect($result['redirect'],303);exit;
}
