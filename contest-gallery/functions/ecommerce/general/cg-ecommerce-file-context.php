<?php
if(!defined('ABSPATH')){exit;}

// Public previews stay in uploads when originals are moved into protected sale storage.
if(!function_exists('cg_ecommerce_public_image_src')){
    function cg_ecommerce_public_image_src($uploadId,$size = 'large'){
        $image = wp_get_attachment_image_src($uploadId,$size);
        if($image){return $image;}
        if(get_post_type($uploadId) !== 'contest-gallery-ecom'){return false;}
        static $cache = array();
        $key = get_current_blog_id().':'.absint($uploadId).':'.(is_string($size) ? $size : 'full');
        if(array_key_exists($key,$cache)){return $cache[$key];}
        $cache[$key] = false;
        $uploads = wp_get_upload_dir();
        $base = realpath($uploads['basedir']);
        $file = get_attached_file($uploadId,true);
        $metadata = wp_get_attachment_metadata($uploadId);
        if(!$base || !$file){return false;}
        $candidates = array();
        if(is_string($size) && !empty($metadata['sizes'][$size]['file'])){
            $candidates[] = dirname($file).'/'.wp_basename($metadata['sizes'][$size]['file']);
        }
        $candidates[] = $file;
        foreach(array_unique($candidates) as $candidate){
            $real = realpath($candidate);
            if(!$real || is_link($candidate) || !is_file($real) || !is_readable($real) || strpos($real,$base.DIRECTORY_SEPARATOR) !== 0){continue;}
            $relative = wp_normalize_path(substr($real,strlen($base)+1));
            // Never turn plugin-managed protected storage into a public preview URL.
            if(strpos($relative,'contest-gallery/') === 0){continue;}
            $dimensions = @getimagesize($real);
            if(!$dimensions || strpos($dimensions['mime'],'image/') !== 0){continue;}
            $url = trailingslashit($uploads['baseurl']).implode('/',array_map('rawurlencode',explode('/',$relative)));
            $cache[$key] = array($url,(int)$dimensions[0],(int)$dimensions[1],$candidate !== $file);
            break;
        }
        return $cache[$key];
    }
}

if(!function_exists('cg_ecommerce_file_owners')){
    function cg_ecommerce_file_owners($fresh = false){
        global $wpdb;
        static $cache = array();
        $key = $wpdb->prefix;
        if(!$fresh && isset($cache[$key])){return $cache[$key];}
        $rows = $wpdb->get_results("SELECT s.id,s.pid,s.GalleryID,s.WpUploadFilesForSale FROM {$wpdb->prefix}contest_gal1ery_ecommerce_entries s
            INNER JOIN {$wpdb->prefix}contest_gal1ery e ON e.EcommerceEntry=s.id AND e.id=s.pid AND e.GalleryID=s.GalleryID
            WHERE s.IsDownload=1 OR s.SaleType='download'");
        if($wpdb->last_error){return new WP_Error('cg_sale_ownership_unavailable','The download file assignments could not be checked. Please try again.');}
        $owners = array();
        foreach($rows as $row){
            $ids = maybe_unserialize($row->WpUploadFilesForSale);
            if(!is_array($ids)){continue;}
            foreach(array_unique(array_map('absint',$ids)) as $id){
                if(!$id){continue;}
                $owners[$id][] = array('gallery_id'=>(int)$row->GalleryID,'entry_id'=>(int)$row->pid,'sale_id'=>(int)$row->id);
            }
        }
        $cache[$key] = $owners;
        return $owners;
    }
}

if(!function_exists('cg_ecommerce_file_context')){
    function cg_ecommerce_file_context($uploadId,$entryId = 0){
        $uploadId = absint($uploadId);
        $owners = cg_ecommerce_file_owners();
        $context = array('locked'=>false,'managed'=>false,'owner'=>null);
        if(is_wp_error($owners)){$context['locked']=true;return $context;}
        $matches = isset($owners[$uploadId]) ? $owners[$uploadId] : array();
        if(count($matches) === 1){
            $context['managed'] = true;
            $context['owner'] = $matches[0];
            $context['locked'] = $matches[0]['entry_id'] !== absint($entryId);
        }elseif(count($matches) > 1 || get_post_type($uploadId) === 'contest-gallery-ecom' || get_post_meta($uploadId,'contest_gallery_ecommerce_sale',true)){
            $context['managed'] = true;
            $context['locked'] = true;
        }
        if($context['owner']){
            $context['owner']['url'] = add_query_arg(array('page'=>cg_get_version().'/index.php'),admin_url('admin.php')).
                '#option_id='.$context['owner']['gallery_id'].'&edit_gallery=true&cg_nonce='.wp_create_nonce('cg_nonce').'&cg_sale_entry='.$context['owner']['entry_id'];
        }
        return $context;
    }
}

if(!function_exists('cg_ecommerce_assert_file_ownership')){
    function cg_ecommerce_assert_file_ownership($uploadIds,$entryId){
        $owners = cg_ecommerce_file_owners(true);
        if(is_wp_error($owners)){cg_backend_ajax_error_json($owners->get_error_message(),503,$owners->get_error_code());}
        cg_ecommerce_assert_sale_files_lock();
        foreach(array_unique(array_map('absint',$uploadIds)) as $uploadId){
            $context = cg_ecommerce_file_context($uploadId,$entryId);
            if(!$context['locked']){continue;}
            $message = $context['owner'] ? 'This file is already managed by gallery '.$context['owner']['gallery_id'].', entry '.$context['owner']['entry_id'].'. Open that sale to manage it.' : 'The existing sale assignment of this file could not be resolved. Check its sale before continuing.';
            cg_backend_ajax_error_json($message,409,'cg_sale_file_owned_elsewhere');
        }
    }
}

if(!function_exists('cg_ecommerce_sale_files_lock_state')){
    function &cg_ecommerce_sale_files_lock_state(){static $state = array('depth'=>0,'name'=>'','connection'=>0);return $state;}
}
if(!function_exists('cg_ecommerce_release_sale_files_lock')){
    function cg_ecommerce_release_sale_files_lock(){
        global $wpdb;
        $state =& cg_ecommerce_sale_files_lock_state();
        if($state['name'] !== ''){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$state['name']));}
        $state = array('depth'=>0,'name'=>'','connection'=>0);
    }
}
if(!function_exists('cg_ecommerce_assert_sale_files_lock')){
    function cg_ecommerce_assert_sale_files_lock(){
        global $wpdb;
        $state =& cg_ecommerce_sale_files_lock_state();
        if(!$state['depth']){return;}
        $owner = (int)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',$state['name']));
        $connection = (int)$wpdb->get_var('SELECT CONNECTION_ID()');
        if(!$owner || $owner !== $state['connection'] || $connection !== $state['connection']){
            cg_backend_ajax_error_json('The sale file lock was lost. Reload the page and try again.',503,'cg_sale_files_lock_lost');
        }
    }
}
if(!function_exists('cg_ecommerce_with_sale_files_lock')){
    function cg_ecommerce_with_sale_files_lock($callback,$args = array()){
        global $wpdb;
        $state =& cg_ecommerce_sale_files_lock_state();
        if(!$state['depth']){
            $name = 'cg_sale_files_'.md5(DB_NAME.':'.$wpdb->prefix);
            if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,5)',$name)) !== '1'){
                cg_backend_ajax_error_json('Another sale is updating its files. Please try again.',409,'cg_sale_files_busy');
            }
            $state['name'] = $name;
            $state['connection'] = (int)$wpdb->get_var('SELECT CONNECTION_ID()');
            // wp_die()/exit do not execute finally. Release our connection-owned lock on shutdown too.
            static $registered = false;
            if(!$registered){register_shutdown_function('cg_ecommerce_release_sale_files_lock');$registered=true;}
        }
        $state['depth']++;
        try{
            cg_ecommerce_assert_sale_files_lock();
            return call_user_func_array($callback,$args);
        }finally{
            $state['depth']--;
            if(!$state['depth']){cg_ecommerce_release_sale_files_lock();}
        }
    }
}
