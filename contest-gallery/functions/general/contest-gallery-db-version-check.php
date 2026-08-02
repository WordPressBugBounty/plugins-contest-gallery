<?php
if(!function_exists('cg_database_install_pending_option_name')){
    function cg_database_install_pending_option_name(){
        return 'p_cgal1ery_database_install_pending';
    }
}

if(!function_exists('cg_database_install_lock_option_name')){
    function cg_database_install_lock_option_name(){
        return 'p_cgal1ery_database_install_lock';
    }
}

if(!function_exists('cg_database_install_get_current_table_suffix')){
    function cg_database_install_get_current_table_suffix(){
        if(!is_multisite()){
            return '';
        }

        global $wpdb;

        $blogPrefix = $wpdb->get_blog_prefix(get_current_blog_id());
        if(strpos($blogPrefix,$wpdb->base_prefix)!==0){
            return '';
        }

        return substr($blogPrefix,strlen($wpdb->base_prefix));
    }
}

if(!function_exists('cg_database_install_get_pending')){
    function cg_database_install_get_pending(){
        $pending = get_option(cg_database_install_pending_option_name());
        if(!is_array($pending) || empty($pending['token']) || !is_string($pending['token'])){
            return false;
        }
        return $pending;
    }
}

if(!function_exists('cg_database_install_is_pending')){
    function cg_database_install_is_pending(){
        return (bool)cg_database_install_get_pending();
    }
}

if(!function_exists('cg_database_install_pending_token_is_current')){
    function cg_database_install_pending_token_is_current($token){
        global $wpdb;
        $rawPending = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            cg_database_install_pending_option_name()
        ));
        $pending = ($rawPending !== null) ? maybe_unserialize($rawPending) : false;
        return is_array($pending) && !empty($pending['token']) && is_string($token) && hash_equals((string)$pending['token'],$token);
    }
}

if(!function_exists('cg_database_install_schedule')){
    function cg_database_install_schedule($token){
        $args = array($token);
        if(!wp_next_scheduled('cg_complete_database_install_event',$args)){
            wp_schedule_single_event(time(),'cg_complete_database_install_event',$args);
        }
    }
}

if(!function_exists('cg_database_install_defaults_exist')){
    function cg_database_install_defaults_exist($i){
        global $wpdb;

        $prefix = $wpdb->base_prefix . "$i" . 'contest_gal1ery';
        $googleOptions = $wpdb->get_var("SELECT id FROM {$prefix}_google_options WHERE GeneralID = 1 LIMIT 1");
        $ecommerceOptions = $wpdb->get_var("SELECT id FROM {$prefix}_ecommerce_options WHERE GeneralID = 1 LIMIT 1");
        $invoiceOptions = $wpdb->get_var("SELECT id FROM {$prefix}_ecommerce_invoice_options WHERE GeneralID = 1 LIMIT 1");

        return !empty($googleOptions) && !empty($ecommerceOptions) && !empty($invoiceOptions);
    }
}

if(!function_exists('cg_database_install_acquire_lock')){
    function cg_database_install_acquire_lock(){
        $optionName = cg_database_install_lock_option_name();
        $owner = wp_generate_password(32,false,false);
        $lock = array('owner'=>$owner,'created_at'=>time());

        if(add_option($optionName,$lock,'','no')){
            return $owner;
        }

        $existingLock = get_option($optionName);
        if(!is_array($existingLock) || empty($existingLock['created_at']) || (time()-absint($existingLock['created_at']))>300){
            delete_option($optionName);
            if(add_option($optionName,$lock,'','no')){
                return $owner;
            }
        }

        return false;
    }
}

if(!function_exists('cg_database_install_release_lock')){
    function cg_database_install_release_lock($owner){
        $optionName = cg_database_install_lock_option_name();
        $lock = get_option($optionName);
        if(is_array($lock) && !empty($lock['owner']) && is_string($owner) && hash_equals((string)$lock['owner'],$owner)){
            delete_option($optionName);
        }
    }
}

if(!function_exists('cg_is_fresh_install_before_create_table')){
    function cg_is_fresh_install_before_create_table($i,$p_cgal1ery_db_installed_ver){

        if($p_cgal1ery_db_installed_ver){
            return false;
        }

        global $wpdb;

        $tablename_prefix = $wpdb->base_prefix . "$i"."contest_gal1ery";
        $table_like = $wpdb->esc_like($tablename_prefix).'%';
        $existing = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_like));

        return empty($existing);

    }
}

if(!function_exists('cg_database_install_start_fresh')){
    function cg_database_install_start_fresh($i,$networkWide = false){
        contest_gal1ery_create_table($i,false,'core');
        if(!cg_contest_gallery_required_tables_exist($i,true)){
            return false;
        }

        $pending = array(
            'token'=>wp_generate_password(32,false,false),
            'created_at'=>time(),
            'network_wide'=>(bool)$networkWide
        );
        if(!add_option(cg_database_install_pending_option_name(),$pending,'','no')){
            update_option(cg_database_install_pending_option_name(),$pending);
        }
        $storedPending = cg_database_install_get_pending();
        if(!$storedPending){
            return false;
        }

        cg_database_install_schedule($storedPending['token']);
        return true;
    }
}

if(!function_exists('cg_complete_database_install')){
    function cg_complete_database_install($token){
        $pending = cg_database_install_get_pending();
        if(!$pending){
            return array('status'=>'complete');
        }
        if(!is_string($token) || !hash_equals((string)$pending['token'],$token)){
            return array('status'=>'invalid');
        }

        $lockOwner = cg_database_install_acquire_lock();
        if(!$lockOwner){
            return array('status'=>'in_progress');
        }
        register_shutdown_function('cg_database_install_release_lock',$lockOwner);

        if(!cg_database_install_pending_token_is_current($token)){
            cg_database_install_release_lock($lockOwner);
            return array('status'=>'cancelled');
        }

        $i = cg_database_install_get_current_table_suffix();
        contest_gal1ery_create_table($i,false,'all');

        if(!cg_contest_gallery_required_tables_exist($i) || !cg_database_install_defaults_exist($i)){
            cg_database_install_release_lock($lockOwner);
            return array('status'=>'failed');
        }

        if(!cg_database_install_pending_token_is_current($token)){
            cg_database_install_release_lock($lockOwner);
            return array('status'=>'cancelled');
        }

        $currentVersion = cg_get_db_version();
        if(get_option('p_cgal1ery_db_version') !== false){
            update_option('p_cgal1ery_db_version',$currentVersion);
        }else{
            add_option('p_cgal1ery_db_version',$currentVersion);
        }
        $wp_upload_dir = wp_upload_dir();
        $rewriteRulesChangedFilePath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/rewrite-rules-changed-do-not-edit-or-remove.txt';
        file_put_contents($rewriteRulesChangedFilePath,'changed');
        delete_option(cg_database_install_pending_option_name());
        cg_database_install_release_lock($lockOwner);
        wp_clear_scheduled_hook('cg_complete_database_install_event',array($token));

        return array('status'=>'complete');
    }
}

if(!function_exists('cg_complete_database_install_event_callback')){
    function cg_complete_database_install_event_callback($token){
        cg_complete_database_install($token);
    }
}
add_action('cg_complete_database_install_event','cg_complete_database_install_event_callback',10,1);

if(!function_exists('cg_database_install_is_network_active')){
    function cg_database_install_is_network_active(){
        if(!is_multisite()){
            return false;
        }
        if(!function_exists('is_plugin_active_for_network')){
            require_once(ABSPATH.'wp-admin/includes/plugin.php');
        }

        $pluginFile = dirname(dirname(__DIR__)).'/index.php';
        return is_plugin_active_for_network(plugin_basename($pluginFile));
    }
}

if(!function_exists('cg_database_install_current_user_can_complete')){
    function cg_database_install_current_user_can_complete(){
        if(!is_user_logged_in()){
            return false;
        }
        if(current_user_can('activate_plugins')){
            return true;
        }

        $pending = cg_database_install_get_pending();
        return is_multisite() &&
            !empty($pending['network_wide']) &&
            cg_database_install_is_network_active() &&
            current_user_can('manage_options');
    }
}

if(!function_exists('post_cg_complete_database_install')){
    function post_cg_complete_database_install(){
        if(!cg_database_install_current_user_can_complete()){
            wp_send_json_error(array('status'=>'forbidden'));
        }
        if(!check_ajax_referer('cg_complete_database_install','cg_nonce',false)){
            wp_send_json_error(array('status'=>'invalid_request'));
        }

        $pending = cg_database_install_get_pending();
        if(!$pending){
            wp_send_json_success(array('status'=>'complete'));
        }

        $result = cg_complete_database_install($pending['token']);
        if($result['status']==='complete' || $result['status']==='in_progress'){
            wp_send_json_success($result);
        }
        wp_send_json_error(array('status'=>'failed'));
    }
}
add_action('wp_ajax_post_cg_complete_database_install','post_cg_complete_database_install');

if(!function_exists('cg_run_update_check_after_create_table')){
    function cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver,$p_cgal1ery_db_new_version){

        if(cg_contest_gallery_db_check_was_run($i,$p_cgal1ery_db_new_version)){
            return;
        }

        $isFreshInstall = cg_is_fresh_install_before_create_table($i,$p_cgal1ery_db_installed_ver);

        contest_gal1ery_create_table($i);

        if(!$isFreshInstall){
            include(__DIR__."/../../update/update-check-new.php");
        }

    }
}

if(!function_exists('cg_contest_gallery_db_check_was_run')){
    function cg_contest_gallery_db_check_was_run($i,$p_cgal1ery_db_new_version){
        static $checked = array();

        $key = $i.'|'.$p_cgal1ery_db_new_version;

        if(!empty($checked[$key])){
            return true;
        }

        $checked[$key] = true;

        return false;
    }
}

if(!function_exists('cg_database_install_activation')){
    function cg_database_install_activation($networkWide = false){
        if(is_multisite() && $networkWide){
            $siteIds = get_sites(array(
                'fields'=>'ids',
                'number'=>0,
                'orderby'=>'id',
                'order'=>'ASC'
            ));

            foreach($siteIds as $siteId){
                $siteId = absint($siteId);
                if(!$siteId){
                    continue;
                }
                switch_to_blog($siteId);
                cg_database_install_current_site_check(true,true);
                restore_current_blog();
            }
            return;
        }

        cg_database_install_current_site_check(true,false);
    }
}

if(!function_exists('cg_database_install_clear_current_site_runtime')){
    function cg_database_install_clear_current_site_runtime(){
        wp_unschedule_hook('cg_complete_database_install_event');

        $lock = get_option(cg_database_install_lock_option_name());
        if(!is_array($lock) || empty($lock['created_at']) || (time()-absint($lock['created_at']))>300){
            delete_option(cg_database_install_lock_option_name());
        }
    }
}

if(!function_exists('cg_database_install_deactivation')){
    function cg_database_install_deactivation($networkWide = false){
        if(is_multisite() && $networkWide){
            $siteIds = get_sites(array('fields'=>'ids','number'=>0));
            foreach($siteIds as $siteId){
                $siteId = absint($siteId);
                if(!$siteId){
                    continue;
                }
                switch_to_blog($siteId);
                cg_database_install_clear_current_site_runtime();
                restore_current_blog();
            }
            return;
        }

        cg_database_install_clear_current_site_runtime();
    }
}

if(!function_exists('cg_database_install_current_site_check')){
    function cg_database_install_current_site_check($isActivation = false,$networkWide = false){

        global $p_cgal1ery_db_new_version;
        $p_cgal1ery_db_new_version = cg_get_db_version();

        if(!get_option("p_cgal1ery_install_date")){
            add_option("p_cgal1ery_install_date",date('Y-m-d'));
        }

        $p_cgal1ery_db_installed_ver = get_option( "p_cgal1ery_db_version" );
        $pending = cg_database_install_get_pending();
        if($pending){
            if($networkWide && empty($pending['network_wide'])){
                $pending['network_wide'] = true;
                update_option(cg_database_install_pending_option_name(),$pending);
            }
            if($isActivation){
                cg_database_install_schedule($pending['token']);
            }
            return false;
        }

        if ( $p_cgal1ery_db_installed_ver != $p_cgal1ery_db_new_version ) {
            $i = cg_database_install_get_current_table_suffix();
            if($isActivation && cg_is_fresh_install_before_create_table($i,$p_cgal1ery_db_installed_ver)){
                return cg_database_install_start_fresh($i,$networkWide);
            }
            if(function_exists('contest_gal1ery_create_table')){
                cg_run_update_check_after_create_table($i,$p_cgal1ery_db_installed_ver,$p_cgal1ery_db_new_version);
            }

            if($p_cgal1ery_db_installed_ver){update_option( "p_cgal1ery_db_version", $p_cgal1ery_db_new_version );}
            else{add_option( "p_cgal1ery_db_version", $p_cgal1ery_db_new_version );}

            $wp_upload_dir = wp_upload_dir();
            $rewriteRulesChangedFilePath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/rewrite-rules-changed-do-not-edit-or-remove.txt';
            file_put_contents($rewriteRulesChangedFilePath,'changed');// register_post_type has to be executed in register_post_type.php, which will be executed on init, after register_post_type()

        }

        return true;
    }
}

if(!function_exists('contest_gal1ery_db_check')){
    function contest_gal1ery_db_check($isActivation = false){
        return cg_database_install_current_site_check($isActivation,false);
    }
}

if(!function_exists('cg_database_install_initialize_network_site')){
    function cg_database_install_initialize_network_site($newSite){
        if(!cg_database_install_is_network_active() || !is_object($newSite) || empty($newSite->blog_id)){
            return;
        }

        $siteId = absint($newSite->blog_id);
        if(!$siteId){
            return;
        }

        switch_to_blog($siteId);
        cg_database_install_current_site_check(true,true);
        restore_current_blog();
    }
}
add_action('wp_initialize_site','cg_database_install_initialize_network_site',200,1);

/**###NORMAL###**/
if(!function_exists('contest_gal1ery_key_check')){
    function contest_gal1ery_key_check(){

        return false;

    }
}
/**###NORMAL---END###**/
