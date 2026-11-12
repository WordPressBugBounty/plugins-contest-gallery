<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_key_inventory_error')){
    function cg_ecommerce_key_inventory_error(){
        return new WP_Error('cg_key_unavailable','This product is no longer available.');
    }
}

if(!function_exists('cg_ecommerce_key_inventory_path')){
    function cg_ecommerce_key_inventory_path($galleryId,$realId,$filename,$type){
        if(!absint($galleryId) || !absint($realId) || !is_string($filename) || !preg_match('~\A(?!\.)[^\\\\/\x00-\x1f\x7f]+\.csv\z~i',$filename) || !in_array($type,array('download','service'),true)){return false;}
        $uploads = wp_upload_dir();
        $root = realpath($uploads['basedir']);
        if($root === false){return false;}
        $path = $root.'/contest-gallery/gallery-id-'.absint($galleryId).'/ecommerce/real-id-'.absint($realId).'/'.$type.'-keys/'.$filename;
        if(is_link($path) || !is_file($path) || wp_normalize_path(realpath($path)) !== wp_normalize_path($path)){return false;}
        return $path;
    }
}

if(!function_exists('cg_ecommerce_lock_key_file')){
    function cg_ecommerce_lock_key_file($path){
        // A separate, stable inode also serializes readers that waited across an atomic rename.
        $lockPath = dirname($path).'/.cg-key-'.hash('sha256',basename($path)).'.lock';
        if(is_link($lockPath)){return false;}
        $handle = @fopen($lockPath,'c');
        if(!$handle){return false;}
        $until = microtime(true)+5;
        do{
            if(flock($handle,LOCK_EX|LOCK_NB)){return $handle;}
            usleep(50000);
        }while(microtime(true)<$until);
        fclose($handle);
        return false;
    }
}

if(!function_exists('cg_ecommerce_unlock_key_file')){
    function cg_ecommerce_unlock_key_file($handle){
        if(is_resource($handle)){flock($handle,LOCK_UN);fclose($handle);}
    }
}

if(!function_exists('cg_ecommerce_write_key_rows')){
    function cg_ecommerce_write_key_rows($path,$rows){
        $buffer = fopen('php://temp','w+');
        if(!$buffer){return false;}
        $temporary = '';
        try{
            foreach(cg_neutralize_csv_array($rows) as $row){
                $written = fputcsv($buffer,$row,';');
                if($written === false || $written === 0){return false;}
            }
            if(!rewind($buffer)){return false;}
            $content = stream_get_contents($buffer);
            if($content === false){return false;}
            $temporary = tempnam(dirname($path),'.cg-keys-');
            if(!$temporary || dirname($temporary) !== dirname($path)){return false;}
            if(@file_put_contents($temporary,$content,LOCK_EX) !== strlen($content)){return false;}
            $mode = fileperms($path);
            if(!@chmod($temporary,$mode === false ? 0640 : ($mode & 0777))){return false;}
            if(!@rename($temporary,$path)){return false;}
            $temporary = '';
            return true;
        }finally{
            fclose($buffer);
            if($temporary && is_file($temporary)){@unlink($temporary);}
        }
    }
}

if(!function_exists('cg_ecommerce_read_or_claim_key')){
    function cg_ecommerce_read_or_claim_key($path,$claim=false,$email='',$timestamp=0,$orderNumber=''){
        if(!$path || !is_writable(dirname($path)) || ($claim && trim((string)$orderNumber)==='')){return cg_ecommerce_key_inventory_error();}
        $lock = cg_ecommerce_lock_key_file($path);
        if(!$lock){return cg_ecommerce_key_inventory_error();}
        $stream = false;
        try{
            $stream = @fopen($path,'r');
            if(!$stream){return cg_ecommerce_key_inventory_error();}
            $rows = array();
            $usedKeys = array();
            while(($row=fgetcsv($stream,0,';')) !== false){
                $row = array_map('trim',array_map('strval',array_pad(array_slice($row,0,4),4,'')));
                if($row[0] === ''){continue;}
                $rows[] = $row;
                if($row[1] !== '' || $row[3] !== ''){
                    $usedKeys[$row[0]] = true;
                    if($claim && $row[3] === (string)$orderNumber){return $row[0];}
                }
            }
            if(!feof($stream)){return cg_ecommerce_key_inventory_error();}
            foreach($rows as $index=>$row){
                if($row[1] !== '' || $row[3] !== '' || isset($usedKeys[$row[0]])){continue;}
                if(!$claim){return true;}
                $rows[$index][1] = cg_get_time_based_on_wp_timezone_conf($timestamp ? $timestamp : time(),'Y-M-d H:i:s');
                $rows[$index][2] = trim((string)$email);
                $rows[$index][3] = (string)$orderNumber;
                if(!cg_ecommerce_write_key_rows($path,$rows)){return cg_ecommerce_key_inventory_error();}
                return $row[0];
            }
            return cg_ecommerce_key_inventory_error();
        }finally{
            if(is_resource($stream)){fclose($stream);}
            cg_ecommerce_unlock_key_file($lock);
        }
    }
}

if(!function_exists('cg_ecommerce_checkout_keys_available')){
    function cg_ecommerce_checkout_keys_available($cart){
        if(empty($cart['purchase_units'][0]['items'])){return cg_ecommerce_key_inventory_error();}
        foreach($cart['purchase_units'][0]['items'] as $item){
            $type = !empty($item['IsDownload']) ? 'download' : (!empty($item['IsService']) ? 'service' : '');
            $field = $type === 'download' ? 'DownloadKeysCsvName' : 'ServiceKeysCsvName';
            if($type === '' || empty($item[$field])){continue;}
            $path = cg_ecommerce_key_inventory_path($item['realGid'],$item['realId'],$item[$field],$type);
            $available = cg_ecommerce_read_or_claim_key($path);
            if(is_wp_error($available)){return $available;}
        }
        return true;
    }
}

if(!function_exists('post_cg_ecommerce_checkout_preflight')){
    function post_cg_ecommerce_checkout_preflight(){
        cg_check_frontend_nonce();
        $options = cg_get_ecommerce_options();
        if(!empty($options->RegUserPurchaseOnly) && !is_user_logged_in()){wp_send_json_error(array('message'=>'Please sign in before ordering.'),403);}
        $units = isset($_POST['cgPurchaseUnits']) && is_array($_POST['cgPurchaseUnits']) ? wp_unslash($_POST['cgPurchaseUnits']) : array();
        $cart = cg_ecommerce_build_authoritative_checkout_cart($units,$options);
        if(empty($cart['success'])){wp_send_json_error(array('message'=>cg_ecommerce_get_reload_checkout_error()),409);}
        $environment = cg_ecommerce_resolve_checkout_environment(!empty($_POST['isTestEnv']),'paypal',$cart);
        if(empty($environment['success'])){wp_send_json_error(array('message'=>$environment['message']),409);}
        $available = cg_ecommerce_checkout_keys_available($cart);
        if(is_wp_error($available)){wp_send_json_error(array('message'=>$available->get_error_message()),409);}
        wp_send_json_success();
    }
    add_action('wp_ajax_post_cg_ecommerce_checkout_preflight','post_cg_ecommerce_checkout_preflight');
    add_action('wp_ajax_nopriv_post_cg_ecommerce_checkout_preflight','post_cg_ecommerce_checkout_preflight');
}
