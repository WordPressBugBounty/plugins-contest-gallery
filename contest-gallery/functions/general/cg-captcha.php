<?php
if(!defined('ABSPATH')){exit;}

// Secrets never belong to the public form tables/JSON. Legacy recognition is a one-time snapshot.
if(!function_exists('cg_captcha_tables')){
function cg_captcha_tables(){
    global $wpdb;
    return array('registry'=>array($wpdb->prefix.'contest_gal1ery_create_user_form','user-robot-recaptcha-field'),
        'upload'=>array($wpdb->prefix.'contest_gal1ery_f_input','caRoRe-f'));
}
}
if(!function_exists('cg_captcha_table_exists')){
function cg_captcha_table_exists($table){
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)))===$table;
}
}
if(!function_exists('cg_captcha_bootstrap')){
function cg_captcha_bootstrap(){
    if(get_option('cg_captcha_legacy_v1',false)!==false){return;}
    global $wpdb;
    $legacy = array();
    foreach(cg_captcha_tables() as $scope=>$definition){
        if(!cg_captcha_table_exists($definition[0])){return;}
        $rows = $wpdb->get_results($wpdb->prepare("SELECT id,ReCaKey FROM {$definition[0]} WHERE Field_Type=%s",$definition[1]));
        if($wpdb->last_error){return;}// A new installation may not have created its tables yet.
        foreach($rows as $row){if(trim($row->ReCaKey)!==''){$legacy[$scope.':'.$row->id] = hash('sha256',trim($row->ReCaKey));}}
    }
    add_option('cg_captcha_legacy_v1',$legacy,'',false);
}
}
add_action('init','cg_captcha_bootstrap',1);

if(!function_exists('cg_captcha_option')){
function cg_captcha_option($scope,$id){return 'cg_captcha_'.$scope.'_'.absint($id);}
}
if(!function_exists('cg_captcha_config')){
function cg_captcha_config($scope,$id,$siteKey){
    cg_captcha_bootstrap();
    $record = get_option(cg_captcha_option($scope,$id),array());
    $record = is_array($record) ? $record : array();
    $legacy = get_option('cg_captcha_legacy_v1',array());
    $key = $scope.':'.absint($id);
    $isLegacy = empty($record['enforce']) && isset($legacy[$key]) && hash_equals($legacy[$key],hash('sha256',trim($siteKey)));
    $secret = isset($record['siteKey'],$record['secret']) && $record['siteKey']===trim($siteKey) ? $record['secret'] : '';
    return array('legacy'=>$isLegacy,'secret'=>$secret,'enforce'=>!$isLegacy);
}
}
if(!function_exists('cg_captcha_prepare_config')){
function cg_captcha_prepare_config($scope,$id,$siteKey,$secret,$active){
    if(!is_string($siteKey) || !is_string($secret)){return new WP_Error('captcha_config','Invalid reCAPTCHA keys.');}
    $siteKey=trim(wp_unslash($siteKey));$secret=trim(wp_unslash($secret));
    if(strlen($siteKey)>200 || strlen($secret)>200){return new WP_Error('captcha_config','Invalid reCAPTCHA keys.');}
    $existing=cg_captcha_config($scope,$id,$siteKey);
    if($secret===''){$secret=$existing['secret'];}
    if($active && !$existing['legacy'] && ($siteKey==='' || $secret==='')){
        return new WP_Error('captcha_config','Enter both your reCAPTCHA site key and secret key before enabling this field.');
    }
    if($secret===''){return null;}// Unrelated saves must not migrate a legacy configuration.
    return array('siteKey'=>$siteKey,'secret'=>$secret,'enforce'=>1);
}
}
if(!function_exists('cg_captcha_store_config')){
function cg_captcha_store_config($scope,$id,$record){
    if(is_array($record) && $id){update_option(cg_captcha_option($scope,$id),$record,false);}
}
}
if(!function_exists('cg_captcha_secret_input')){
function cg_captcha_secret_input($scope,$id,$siteKey,$name){
    $config=cg_captcha_config($scope,$id,$siteKey);
    $note=$config['secret']!=='' ? 'A secret is saved. Leave empty to keep it.' : ($config['legacy'] ? 'Your existing form still works. Add the secret key to enable server-side bot protection.' : 'Required when enabling reCAPTCHA.');
    return '<label style="display:block;width:100%;margin-top:16px"><span style="display:block;font-weight:600;margin-bottom:8px">Your secret key</span><input type="password" class="cg_reca_secret" name="'.esc_attr($name).'" value="" autocomplete="new-password" maxlength="200" style="width:100%" /></label><span style="display:block;width:100%;margin-top:8px;text-align:center">'.esc_html($note).' Test keys are for testing only and do not block bots.</span>';
}
}
if(!function_exists('cg_captcha_admin_notice')){
function cg_captcha_admin_notice(){
    if(!current_user_can('manage_options')){return;}
    $screen=get_current_screen();
    if(!$screen || $screen->id!==get_plugin_page_hookname(cg_get_version().'/index.php','')){return;}
    global $wpdb;
    $links=array();
    foreach(cg_captcha_tables() as $scope=>$definition){
        if(!cg_captcha_table_exists($definition[0])){continue;}
        $rows=$wpdb->get_results($wpdb->prepare("SELECT id,GalleryID,ReCaKey FROM {$definition[0]} WHERE Field_Type=%s AND Active=1",$definition[1]));
        foreach($rows as $row){
            $config=cg_captcha_config($scope,$row->id,$row->ReCaKey);
            $test=trim($row->ReCaKey)==='6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
            if(!$config['legacy'] && $config['secret']!=='' && !$test){continue;}
            $gid=absint($row->GalleryID);
            if(!$gid && $scope==='registry' && cg_captcha_table_exists($wpdb->prefix.'contest_gal1ery_options')){$gid=absint($wpdb->get_var("SELECT id FROM {$wpdb->prefix}contest_gal1ery_options WHERE Version >= 14 ORDER BY id DESC LIMIT 1"));}
            // Notices sit outside the gallery container whose links receive a nonce in JavaScript.
            $url=add_query_arg(array('page'=>cg_get_version().'/index.php','option_id'=>$gid,$scope==='registry'?'create_user_form':'define_upload'=>'true','cg_nonce'=>wp_create_nonce('cg_nonce')),admin_url('admin.php'));
            $reason=$test ? 'A test key is active and does not protect against bots. Set your own Google reCAPTCHA site key and matching secret key.' : ($config['legacy'] ? 'Add the matching secret key to enable server-side bot protection. Your existing form remains available.' : 'The secret key is missing. Add it to enable form submissions.');
            $links[]='<a href="'.esc_url($url).'">'.esc_html(($scope==='registry'?'Registration':'Upload').' form'.($scope==='upload'?' #'.$gid:'')).'</a>: '.esc_html($reason);
        }
    }
    if($links){echo '<div class="notice notice-warning"><p><strong>Contest Gallery reCAPTCHA</strong><br>'.implode('<br>',$links).'</p></div>';}
}
}
add_action('admin_notices','cg_captcha_admin_notice');

if(!function_exists('cg_captcha_registry_signature')){
function cg_captcha_registry_signature($gid,$pin){return hash_hmac('sha256','cg-registry-captcha|'.absint($gid).'|'.($pin?'pin':'register'),wp_salt('auth'));}
}
if(!function_exists('cg_captcha_registry_mode')){
function cg_captcha_registry_mode($gid,$post){
    $pin=!empty($post['cg_users_pin']);
    $signature=isset($post['cg_captcha_form']) && is_string($post['cg_captcha_form']) ? $post['cg_captcha_form'] : '';
    // Old regular registration markup stays compatible. PIN mode cannot be asserted by a posted flag.
    if(($pin || $signature!=='') && !cg1l_hash_equals(cg_captcha_registry_signature($gid,$pin),$signature)){
        return new WP_Error('captcha_form','Please reload the page and try again.');
    }
    return $pin;
}
}
if(!function_exists('cg_captcha_validate')){
function cg_captcha_validate($scope,$gid,$post,$pin=false,$version=100){
    global $wpdb;
    if($scope==='registry'){
        $table=$wpdb->prefix.'contest_gal1ery_create_user_form';
        $where=(intval($version)>=14 || !$gid) ? 'GeneralID=1' : $wpdb->prepare('GalleryID=%d',$gid);
        $rows=$wpdb->get_results("SELECT id,Field_Type,ReCaKey,PinField FROM $table WHERE $where AND Active=1 AND Field_Type IN ('user-robot-field','user-robot-recaptcha-field')");
    }else{
        $rows=$wpdb->get_results($wpdb->prepare("SELECT id,Field_Type,ReCaKey FROM {$wpdb->prefix}contest_gal1ery_f_input WHERE GalleryID=%d AND Active=1 AND Field_Type IN ('caRo-f','caRoRe-f')",$gid));
    }
    if($wpdb->last_error){return new WP_Error('captcha_unavailable','Please try again.');}
    foreach($rows as $row){
        if($scope==='registry' && $pin && empty($row->PinField)){continue;}
        if(in_array($row->Field_Type,array('user-robot-field','caRo-f'),true)){
            if(!isset($post['cg_captcha_simple']) || $post['cg_captcha_simple']!=='1'){return new WP_Error('captcha_required','Please confirm that you are not a robot.');}
            continue;
        }
        $config=cg_captcha_config($scope,$row->id,$row->ReCaKey);
        if($config['legacy']){continue;}
        $token=isset($post['cg_captcha_response']) && is_string($post['cg_captcha_response']) ? trim(wp_unslash($post['cg_captcha_response'])) : '';
        if($config['secret']==='' || trim($row->ReCaKey)===''){return new WP_Error('captcha_config','The form is temporarily unavailable. Please contact the administrator.');}
        if($token==='' || strlen($token)>8192){return new WP_Error('captcha_required','Please confirm that you are not a robot.');}
        $reply=wp_remote_post('https://www.google.com/recaptcha/api/siteverify',array('timeout'=>8,'redirection'=>0,'body'=>array('secret'=>$config['secret'],'response'=>$token)));
        if(is_wp_error($reply) || wp_remote_retrieve_response_code($reply)!==200){return new WP_Error('captcha_unavailable','The verification could not be completed. Please try again.');}
        $data=json_decode(wp_remote_retrieve_body($reply),true);
        $test=trim($row->ReCaKey)==='6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' && $config['secret']==='6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
        $host=is_array($data) && !empty($data['hostname']) ? strtolower($data['hostname']) : '';
        $hosts=array(strtolower((string)parse_url(home_url(),PHP_URL_HOST)),strtolower((string)parse_url(site_url(),PHP_URL_HOST)));
        if(!is_array($data) || !isset($data['success']) || $data['success']!==true || (!$test && !in_array($host,$hosts,true))){return new WP_Error('captcha_invalid','Please confirm that you are not a robot again.');}
    }
    return true;
}
}
if(!function_exists('cg_captcha_render_widget')){
function cg_captcha_render_widget($siteKey,$language,$registry=false){
    echo '<div class="'.($registry?'cg_recaptcha_reg_form':'cg_recaptcha_in_gallery_form cg_recaptcha_form').' cg-captcha-widget" data-sitekey="'.esc_attr(trim($siteKey)).'" data-language="'.esc_attr($language).'" ></div>';
    echo '<p class="cg_input_error cg_hide '.($registry?'cg_recaptcha_not_valid_reg_form_error':'cg_recaptcha_not_valid_in_gallery_form_error').'"></p>';
}
}
