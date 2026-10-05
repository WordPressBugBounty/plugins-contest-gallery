<?php

if(!defined('ABSPATH')){exit;}
require_once dirname(__DIR__).'/cg-frontend-security-state.php';

if(!function_exists('cg1l_password_reset_lock')){

function cg1l_password_reset_request_allowed($email){
    // Apply identical limits before looking up the account. Never store the address or IP as a key.
    if(!cg_security_rate_take('reset-ip',cg_security_remote_address(),20,HOUR_IN_SECONDS)){return false;}
    return cg_security_rate_take('reset-email',strtolower(trim($email)),5,HOUR_IN_SECONDS,60);
}

function cg1l_password_reset_lock($userId){
    global $wpdb;
    $name = 'cg_password_'.md5($wpdb->usermeta.'|'.absint($userId));
    return ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)',$name,5)) === '1') ? $name : false;
}

function cg1l_password_reset_unlock($lock){
    global $wpdb;
    $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));
}

function cg1l_password_reset_user($userId){
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare("SELECT ID, user_pass FROM $wpdb->users WHERE ID = %d",$userId));
}

function cg1l_password_reset_record($userId){
    global $wpdb;
    // Authorization must not reuse a persistent user-meta cache.
    $rows = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key IN (%s, %s, %s)",$userId,'cgResetPasswordKey','cgLostPasswordMailTimestamp','cgResetPasswordAccountState'));
    $record = array();
    foreach((array)$rows as $row){
        if(isset($record[$row->meta_key])){return array();}
        $record[$row->meta_key] = $row->meta_value;
    }
    return $record;
}

function cg1l_password_reset_account_state($user){
    return hash_hmac('sha256',$user->ID.'|'.$user->user_pass,wp_salt('auth'));
}

function cg1l_password_reset_delete_record($userId){
    global $wpdb;
    $result = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id = %d AND meta_key IN (%s, %s, %s)",$userId,'cgResetPasswordKey','cgLostPasswordMailTimestamp','cgResetPasswordAccountState'));
    wp_cache_delete($userId,'user_meta');
    return $result !== false;
}

function cg1l_password_reset_issue($userId){
    $lock = cg1l_password_reset_lock($userId);
    if(!$lock){return new WP_Error('cg_reset_busy');}
    try{
        $user = cg1l_password_reset_user($userId);
        if(!$user){return new WP_Error('cg_reset_invalid');}
        $record = cg1l_password_reset_record($userId);
        if(isset($record['cgResetPasswordKey']) && cg1l_password_reset_valid($user,$record['cgResetPasswordKey'],$record)){
            // Resends keep the original ten-minute expiry and do not invalidate an earlier email.
            return $record['cgResetPasswordKey'];
        }
        $key = wp_generate_password(32,false,false);
        $timestamp = time();
        $state = cg1l_password_reset_account_state($user);
        wp_cache_delete($userId,'user_meta');
        update_user_meta($userId,'cgResetPasswordKey',$key);
        update_user_meta($userId,'cgLostPasswordMailTimestamp',$timestamp);
        update_user_meta($userId,'cgResetPasswordAccountState',$state);
        $record = cg1l_password_reset_record($userId);
        if(!isset($record['cgResetPasswordKey'],$record['cgLostPasswordMailTimestamp'],$record['cgResetPasswordAccountState']) ||
            $record['cgResetPasswordKey'] !== $key || intval($record['cgLostPasswordMailTimestamp']) !== $timestamp || $record['cgResetPasswordAccountState'] !== $state){
            cg1l_password_reset_delete_record($userId);
            return new WP_Error('cg_reset_storage');
        }
        return $key;
    }finally{
        cg1l_password_reset_unlock($lock);
    }
}

function cg1l_password_reset_valid($user,$key,$record){
    if(!$user || !is_string($key) || !preg_match('/^[A-Za-z0-9]{32}$/D',$key) ||
        empty($record['cgResetPasswordKey']) || empty($record['cgLostPasswordMailTimestamp'])){return false;}
    $timestamp = intval($record['cgLostPasswordMailTimestamp']);
    if($timestamp > time() || $timestamp + 600 <= time() || !hash_equals((string)$record['cgResetPasswordKey'],$key)){return false;}
    // Existing links keep their remaining ten-minute lifetime. New links also bind to the current password.
    return !isset($record['cgResetPasswordAccountState']) || hash_equals((string)$record['cgResetPasswordAccountState'],cg1l_password_reset_account_state($user));
}

function cg1l_password_reset_fields(){
    global $wpdb;
    $table = $wpdb->prefix.'contest_gal1ery_create_user_form';
    $fields = array();
    foreach(array('password','password-confirm') as $type){
        $field = $wpdb->get_row($wpdb->prepare("SELECT Min_Char, Max_Char FROM $table WHERE GeneralID = 1 AND Field_Type = %s ORDER BY Field_Order ASC LIMIT 1",$type));
        if(!$field || !is_numeric($field->Min_Char) || !is_numeric($field->Max_Char) || intval($field->Min_Char) < 0 || intval($field->Max_Char) < max(1,intval($field->Min_Char))){
            return new WP_Error('cg_reset_configuration');
        }
        $fields[$type] = $field;
    }
    if(max(intval($fields['password']->Min_Char),intval($fields['password-confirm']->Min_Char)) > min(intval($fields['password']->Max_Char),intval($fields['password-confirm']->Max_Char))){
        return new WP_Error('cg_reset_configuration');
    }
    return $fields;
}

function cg1l_password_reset_length($password){
    // Match the UTF-16 length used by JavaScript and HTML maxlength, including accented characters.
    $characters = preg_split('//u',$password,-1,PREG_SPLIT_NO_EMPTY);
    if($characters === false){return false;}
    $length = 0;
    foreach($characters as $character){$length += strlen($character) === 4 ? 2 : 1;}
    return $length;
}

function cg1l_password_reset_apply($userId,$key,$password){
    global $wpdb;
    $lock = cg1l_password_reset_lock($userId);
    if(!$lock){return new WP_Error('cg_reset_busy');}
    try{
        $user = cg1l_password_reset_user($userId);
        if(!cg1l_password_reset_valid($user,$key,cg1l_password_reset_record($userId))){return new WP_Error('cg_reset_invalid');}
        $hash = wp_hash_password($password);
        if(!is_string($hash) || $hash === '' || $hash === '*'){return new WP_Error('cg_reset_storage');}
        // A concurrent WordPress password change must not be overwritten. Clear its reset key in the same write.
        $updated = $wpdb->query($wpdb->prepare("UPDATE $wpdb->users SET user_pass = %s, user_activation_key = '' WHERE ID = %d AND BINARY user_pass = %s",$hash,$userId,$user->user_pass));
        if($updated !== 1){return new WP_Error('cg_reset_storage');}
        $deleted = cg1l_password_reset_delete_record($userId);
        clean_user_cache($userId);
    }finally{
        cg1l_password_reset_unlock($lock);
    }
    // Do not nest advisory locks (older MySQL versions only support one per connection).
    cg1l_revoke_login_handoff($userId);
    return $deleted ? true : new WP_Error('cg_reset_uncertain');
}

function cg1l_password_reset_revoke($userId){
    $userId = absint($userId);
    if(!$userId){return;}
    $lock = cg1l_password_reset_lock($userId);
    if(!$lock){return;}
    try{cg1l_password_reset_delete_record($userId);}
    finally{cg1l_password_reset_unlock($lock);}
}

function cg1l_password_reset_profile_updated($userId,$oldUser){
    $user = cg1l_password_reset_user($userId);
    if($user && isset($oldUser->user_pass) && $user->user_pass !== $oldUser->user_pass){cg1l_password_reset_revoke($userId);}
}

function cg1l_password_reset_password_set($password,$userId,$oldUser = null){
    // Newer wp_update_user() also fires this hook before writing. profile_update handles that case.
    if($oldUser){cg1l_password_reset_profile_updated($userId,$oldUser);}
    else{cg1l_password_reset_revoke($userId);}
}

function cg1l_password_reset_after_core_reset($user,$password){
    $current = cg1l_password_reset_user($user->ID);
    if($current && $current->user_pass !== $user->user_pass){cg1l_password_reset_revoke($user->ID);}
}

function cg1l_password_flow_error($target,$message,$translationId = '',$suffix = ''){
    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    ?>
    <script data-cg-processing="true" data-cg-password-result="error">
        (function () {
            var target = document.getElementById(<?php echo json_encode($target,$flags); ?>);
            var translation = document.getElementById(<?php echo json_encode($translationId,$flags); ?>);
            if (target) {
                target.textContent = (translation ? translation.value : <?php echo json_encode($message,$flags); ?>) + <?php echo json_encode($suffix,$flags); ?>;
                target.classList.remove('cg_hide');
            }
        }());
    </script>
    <?php
}

add_action('profile_update','cg1l_password_reset_profile_updated',20,2);
add_action('wp_set_password','cg1l_password_reset_password_set',20,3);
add_action('after_password_reset','cg1l_password_reset_after_core_reset',20,2);

}
