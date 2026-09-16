<?php

if(!defined('ABSPATH')){exit;}

// All issuers and consumers share one lock, including across multisite blogs.
if(!function_exists('cg1l_login_handoff_lock')){
    function cg1l_login_handoff_lock($userId){
        global $wpdb;
        $name = 'cg_login_'.md5($wpdb->usermeta.'|'.absint($userId));
        return ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)',$name,5)) === '1') ? $name : false;
    }
}

if(!function_exists('cg1l_login_handoff_unlock')){
    function cg1l_login_handoff_unlock($lock){
        global $wpdb;
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));
    }
}

if(!function_exists('cg1l_login_handoff_account_state')){
    function cg1l_login_handoff_account_state($userId){
        global $wpdb;
        // Read credentials from the database, not a possibly stale WP_User cache.
        $row = $wpdb->get_row($wpdb->prepare("SELECT user_pass, user_email, user_status FROM $wpdb->users WHERE ID = %d",$userId));
        if(empty($row) || !empty($row->user_status)){
            return '';
        }
        return hash_hmac('sha256',wp_json_encode(array($userId,$row->user_pass,$row->user_email,$row->user_status)),wp_salt('auth'));
    }
}

if(!function_exists('cg1l_issue_login_handoff')){
    function cg1l_issue_login_handoff($userId){
        global $wpdb;
        $userId = absint($userId);
        // Do not create a session here: that would invalidate the caller's guest nonce.
        $binding = cg1l_get_registry_activation_browser_binding();
        if(!$userId || !$binding || (is_user_logged_in() && get_current_user_id() !== $userId)){
            return '';
        }
        $lock = cg1l_login_handoff_lock($userId);
        if(!$lock){return '';}
        try{
            $state = cg1l_login_handoff_account_state($userId);
            if(!$state){return '';}
            $key = wp_generate_password(64,false,false);
            $record = array(
                'version' => 1,
                'digest' => hash_hmac('sha256',$key,wp_salt('auth')),
                'expires' => time() + 120,
                'browser' => $binding,
                'account' => $state
            );
            // Replace all legacy/duplicate rows under the same lock. Never store the bearer key.
            if(false === $wpdb->delete($wpdb->usermeta,array('user_id'=>$userId,'meta_key'=>'cgGetLoggedInFrontendUserKey'),array('%d','%s'))){return '';}
            wp_cache_delete($userId,'user_meta');
            return add_user_meta($userId,'cgGetLoggedInFrontendUserKey',$record,true) ? $key : '';
        }finally{
            cg1l_login_handoff_unlock($lock);
        }
    }
}

if(!function_exists('cg1l_consume_login_handoff')){
    function cg1l_consume_login_handoff($userId,$key){
        global $wpdb;
        $userId = absint($userId);
        if(!$userId || !is_string($key) || !preg_match('/^[A-Za-z0-9]{64}$/',$key) || is_user_logged_in()){
            return false;
        }
        $binding = cg1l_get_registry_activation_browser_binding();
        if(!$binding){return false;}
        $lock = cg1l_login_handoff_lock($userId);
        if(!$lock){return false;}
        try{
            $rows = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d AND meta_key = %s",$userId,'cgGetLoggedInFrontendUserKey'));
            if(count($rows) !== 1){return false;}
            $record = maybe_unserialize($rows[0]);
            $state = cg1l_login_handoff_account_state($userId);
            if(!is_array($record) || !isset($record['version'],$record['expires'],$record['digest'],$record['browser'],$record['account']) ||
                $record['version'] !== 1 || $record['expires'] <= time() || !$state ||
                !hash_equals((string)$record['digest'],hash_hmac('sha256',$key,wp_salt('auth'))) ||
                !hash_equals((string)$record['browser'],$binding) || !hash_equals((string)$record['account'],$state)){
                return false;
            }
            clean_user_cache($userId);
            $user = get_userdata($userId);
            if(!$user || !empty($user->spam) || !empty($user->deleted)){return false;}
            $deleted = $wpdb->delete($wpdb->usermeta,array('user_id'=>$userId,'meta_key'=>'cgGetLoggedInFrontendUserKey'),array('%d','%s'));
            wp_cache_delete($userId,'user_meta');
            if($deleted !== 1){return false;}
            // Consume before authentication, independently of any browser follow-up request.
            wp_set_current_user($userId);
            wp_set_auth_cookie($userId,true);
            return $user;
        }finally{
            cg1l_login_handoff_unlock($lock);
        }
    }
}

if(!function_exists('cg1l_revoke_login_handoff')){
    function cg1l_revoke_login_handoff($userId = 0){
        global $wpdb;
        $userId = absint($userId);
        if(!$userId){return false;}
        $lock = cg1l_login_handoff_lock($userId);
        if(!$lock){return false;}
        try{
            $deleted = $wpdb->delete($wpdb->usermeta,array('user_id'=>$userId,'meta_key'=>'cgGetLoggedInFrontendUserKey'),array('%d','%s'));
            wp_cache_delete($userId,'user_meta');
            return $deleted !== false;
        }finally{
            cg1l_login_handoff_unlock($lock);
        }
    }
}

if(!function_exists('cg1l_revoke_login_handoff_password')){
    function cg1l_revoke_login_handoff_password($password,$userId){
        cg1l_revoke_login_handoff($userId);
    }
}

if(!function_exists('cg1l_revoke_login_handoff_reset')){
    function cg1l_revoke_login_handoff_reset($user){
        if(!empty($user->ID)){cg1l_revoke_login_handoff($user->ID);}
    }
}

add_action('wp_logout','cg1l_revoke_login_handoff',1,1);
add_action('profile_update','cg1l_revoke_login_handoff',10,1);
add_action('wp_set_password','cg1l_revoke_login_handoff_password',10,2);
add_action('after_password_reset','cg1l_revoke_login_handoff_reset',10,1);
