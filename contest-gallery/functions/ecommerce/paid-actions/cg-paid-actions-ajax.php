<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_paid_actions_preview_transient_key')){
    function cg_paid_actions_preview_transient_key($token){
        return 'cg_paid_preview_'.md5((string)$token);
    }
}

if(!function_exists('cg_paid_actions_preview_lifetime')){
    function cg_paid_actions_preview_lifetime(){
        return DAY_IN_SECONDS;
    }
}

if(!function_exists('cg_paid_actions_sandbox_link_transient_key')){
    function cg_paid_actions_sandbox_link_transient_key($token){
        return 'cg_paid_sandbox_link_'.md5((string)$token);
    }
}

if(!function_exists('cg_paid_actions_sandbox_link_lifetime')){
    function cg_paid_actions_sandbox_link_lifetime(){
        return DAY_IN_SECONDS;
    }
}

if(!function_exists('cg_paid_actions_sandbox_user_index_transient_key')){
    function cg_paid_actions_sandbox_user_index_transient_key($userId){
        return 'cg_paid_sandbox_user_'.absint($userId);
    }
}

if(!function_exists('cg_paid_actions_sandbox_link_lock')){
    function cg_paid_actions_sandbox_link_lock($token,$timeout = 3){
        global $wpdb;
        $name = 'cgpasl_'.md5((string)$token);
        $locked = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,%d)',$name,absint($timeout)));
        return ((string)$locked==='1') ? $name : '';
    }
}

if(!function_exists('cg_paid_actions_sandbox_link_unlock')){
    function cg_paid_actions_sandbox_link_unlock($name){
        global $wpdb;
        if($name!==''){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$name));}
    }
}

if(!function_exists('cg_paid_actions_store_sandbox_link_for_user_index')){
    function cg_paid_actions_store_sandbox_link_for_user_index($userId,$token,$expires){
        $userId = absint($userId);
        $expires = absint($expires);
        if(!$userId || $token==='' || $expires<=time()){return false;}

        $indexKey = cg_paid_actions_sandbox_user_index_transient_key($userId);
        $links = get_transient($indexKey);
        $links = is_array($links) ? $links : array();
        $now = time();
        foreach($links as $transientKey=>$linkExpires){
            if(strpos((string)$transientKey,'cg_paid_sandbox_link_')!==0 || absint($linkExpires)<=$now){
                unset($links[$transientKey]);
            }
        }

        $transientKey = cg_paid_actions_sandbox_link_transient_key($token);
        $links[$transientKey] = $expires;
        $maxExpires = $expires;
        foreach($links as $linkExpires){
            $maxExpires = max($maxExpires,absint($linkExpires));
        }
        set_transient($indexKey,$links,max(1,$maxExpires-$now));
        $storedLinks = get_transient($indexKey);
        return is_array($storedLinks) && isset($storedLinks[$transientKey]);
    }
}

if(!function_exists('cg_paid_actions_register_sandbox_link_for_user')){
    function cg_paid_actions_register_sandbox_link_for_user($userId,$token,$expires){
        $userId = absint($userId);
        if(!$userId){return false;}
        $lock = cg_paid_actions_sandbox_link_lock('user-index-'.$userId);
        if($lock===''){return false;}
        $stored = cg_paid_actions_store_sandbox_link_for_user_index($userId,$token,$expires);
        cg_paid_actions_sandbox_link_unlock($lock);
        return $stored;
    }
}

if(!function_exists('cg_paid_actions_revoke_sandbox_links_for_user')){
    function cg_paid_actions_revoke_sandbox_links_for_user($userId){
        $userId = absint($userId);
        if(!$userId){return;}

        $lock = cg_paid_actions_sandbox_link_lock('user-index-'.$userId);
        if($lock===''){return;}
        $indexKey = cg_paid_actions_sandbox_user_index_transient_key($userId);
        $links = get_transient($indexKey);
        if(is_array($links)){
            foreach($links as $transientKey=>$linkExpires){
                if(strpos((string)$transientKey,'cg_paid_sandbox_link_')===0){
                    delete_transient($transientKey);
                }
            }
        }
        delete_transient($indexKey);
        cg_paid_actions_sandbox_link_unlock($lock);
    }
}
add_action('wp_logout','cg_paid_actions_revoke_sandbox_links_for_user',1,1);

if(!function_exists('cg_paid_actions_get_preview_context')){
    function cg_paid_actions_get_preview_context($galleryId,$allowUnboundSandbox = false){
        $token = function_exists('cg_paid_actions_request_preview_token') ? cg_paid_actions_request_preview_token() : '';
        if($token===''){return false;}
        $data = get_transient(cg_paid_actions_preview_transient_key($token));
        $isVisitorSandbox = false;
        if(!is_array($data)){
            $data = get_transient(cg_paid_actions_sandbox_link_transient_key($token));
            $isVisitorSandbox = is_array($data);
        }
        $originGalleryId = is_array($data) ? (!empty($data['originGalleryId']) ? absint($data['originGalleryId']) : (!empty($data['galleryId']) ? absint($data['galleryId']) : 0)) : 0;
        if(!is_array($data) || empty($data['galleryId']) || empty($data['expires']) || !$originGalleryId || absint($data['expires'])<time()){
            return false;
        }

        if($isVisitorSandbox){
            $readiness = cg_paid_actions_get_ecommerce_readiness();
            if(
                empty($data['createdByUserId']) || empty($data['accessMode']) || $data['accessMode']!=='visitor_sandbox' ||
                empty($data['checkoutMode']) || $data['checkoutMode']!=='sandbox' || empty($readiness['proAvailable']) ||
                empty($readiness['sandboxPaymentReady'])
            ){
                return false;
            }
            $currentUserId = is_user_logged_in() ? get_current_user_id() : 0;
            $boundUserId = !empty($data['userId']) ? absint($data['userId']) : 0;
            $registeredDuringBinding = false;
            if($boundUserId && (!$currentUserId || $boundUserId!==$currentUserId)){
                return false;
            }
            if(!$boundUserId && $currentUserId){
                $userIndexLock = cg_paid_actions_sandbox_link_lock('user-index-'.$currentUserId);
                if($userIndexLock===''){return false;}
                $lock = cg_paid_actions_sandbox_link_lock($token);
                if($lock===''){
                    cg_paid_actions_sandbox_link_unlock($userIndexLock);
                    return false;
                }
                $latest = get_transient(cg_paid_actions_sandbox_link_transient_key($token));
                $latestBoundUserId = is_array($latest) && !empty($latest['userId']) ? absint($latest['userId']) : 0;
                if(
                    !is_array($latest) || empty($latest['expires']) || absint($latest['expires'])<time() ||
                    ($latestBoundUserId && $latestBoundUserId!==$currentUserId)
                ){
                    cg_paid_actions_sandbox_link_unlock($lock);
                    cg_paid_actions_sandbox_link_unlock($userIndexLock);
                    return false;
                }
                $latest['userId'] = $currentUserId;
                $latest['boundAt'] = time();
                $remaining = max(1,absint($latest['expires'])-time());
                $stored = set_transient(cg_paid_actions_sandbox_link_transient_key($token),$latest,$remaining);
                if($stored && !cg_paid_actions_store_sandbox_link_for_user_index($currentUserId,$token,$latest['expires'])){
                    delete_transient(cg_paid_actions_sandbox_link_transient_key($token));
                    $stored = false;
                }
                cg_paid_actions_sandbox_link_unlock($lock);
                cg_paid_actions_sandbox_link_unlock($userIndexLock);
                if(!$stored){return false;}
                $data = $latest;
                $registeredDuringBinding = true;
            }elseif(!$boundUserId && !$allowUnboundSandbox){
                return false;
            }
            $boundUserId = !empty($data['userId']) ? absint($data['userId']) : 0;
            if(!$registeredDuringBinding && $boundUserId && $boundUserId===$currentUserId && !cg_paid_actions_register_sandbox_link_for_user($currentUserId,$token,$data['expires'])){
                delete_transient(cg_paid_actions_sandbox_link_transient_key($token));
                return false;
            }
        }elseif(
            !is_user_logged_in() || empty($data['userId']) || absint($data['userId'])!==get_current_user_id() ||
            !cg_user_has_backend_access()
        ){
            return false;
        }

        $galleryId = absint($galleryId);
        if(
            !cg_paid_actions_gallery_is_supported($originGalleryId) ||
            !cg_paid_actions_gallery_is_supported($galleryId)
        ){
            return false;
        }
        if(array_key_exists('shared',$data)){
            $isShared = !empty($data['shared']) ? 1 : 0;
        }else{
            $originSettings = cg_paid_actions_get_settings($originGalleryId);
            $isShared = !empty($originSettings->ShareCredits) ? 1 : 0;
        }
        if($originGalleryId!==$galleryId && !$isShared){
            return false;
        }

        if(!$isVisitorSandbox){
            $previewLifetime = cg_paid_actions_preview_lifetime();
            $data['expires'] = time()+$previewLifetime;
            set_transient(cg_paid_actions_preview_transient_key($token),$data,$previewLifetime);
        }
        $data['shared'] = $isShared;
        $data['originGalleryId'] = $originGalleryId;
        $data['targetGalleryId'] = $galleryId;
        $data['checkoutMode'] = (!empty($data['checkoutMode']) && $data['checkoutMode']==='sandbox') ? 'sandbox' : 'simulation';
        $data['accessMode'] = $isVisitorSandbox ? 'visitor_sandbox' : 'admin_preview';
        $data['token'] = $token;
        return $data;
    }
}

if(!function_exists('cg_paid_actions_create_preview_context')){
    function cg_paid_actions_create_preview_context($galleryId,$actionType,$checkoutMode = 'simulation'){
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $checkoutMode = $checkoutMode==='sandbox' ? 'sandbox' : 'simulation';
        if(!$galleryId || $actionType===''){return new WP_Error('cg_paid_action_preview_invalid','The paid action preview could not be started.');}
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return new WP_Error(
                'cg_paid_actions_gallery_version_unsupported',
                'Earning options are available only for galleries created or copied with Contest Gallery 22.0.0 or later.'
            );
        }

        $settings = cg_paid_actions_get_settings($galleryId);
        $modeProperty = ucfirst($actionType).'Mode';
        if(empty($settings->$modeProperty)){
            return new WP_Error('cg_paid_action_preview_not_active','Activate pay for '.strtolower(cg_paid_actions_type_label($actionType)).' before opening its preview.');
        }
        if($checkoutMode==='sandbox'){
            $readiness = cg_paid_actions_get_ecommerce_readiness();
            if(!current_user_can('manage_options') || empty($readiness['proAvailable'])){
                return new WP_Error('cg_paid_action_sandbox_pro_required','A real sandbox checkout is available only to administrators in Contest Gallery PRO.');
            }
            if(empty($readiness['sandboxPaymentReady'])){
                return new WP_Error('cg_paid_action_sandbox_not_ready','Enable PayPal or Stripe sandbox testing and add its sandbox credentials in Ecommerce options first.');
            }
        }

        $token = wp_generate_password(40,false,false);
        $previewLifetime = cg_paid_actions_preview_lifetime();
        $expires = time()+$previewLifetime;
        set_transient(cg_paid_actions_preview_transient_key($token),array(
            'userId'=>get_current_user_id(),'galleryId'=>$galleryId,'originGalleryId'=>$galleryId,
            'actionType'=>$actionType,'checkoutMode'=>$checkoutMode,'expires'=>$expires,
            'shared'=>!empty($settings->ShareCredits) ? 1 : 0
        ),$previewLifetime);
        return array('token'=>$token,'expires'=>$expires,'checkoutMode'=>$checkoutMode);
    }
}

if(!function_exists('cg_paid_actions_create_sandbox_link_context')){
    function cg_paid_actions_create_sandbox_link_context($galleryId,$actionType){
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        if(!$galleryId || $actionType===''){
            return new WP_Error('cg_paid_action_sandbox_link_invalid','The sandbox test link could not be created.');
        }
        if(!current_user_can('manage_options') || !cg_paid_actions_is_pro_available()){
            return new WP_Error('cg_paid_action_sandbox_pro_required','A real sandbox checkout is available only to administrators in Contest Gallery PRO.');
        }
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return new WP_Error('cg_paid_actions_gallery_version_unsupported','Earning options are available only for galleries created or copied with Contest Gallery 22.0.0 or later.');
        }
        $settings = cg_paid_actions_get_settings($galleryId);
        $modeProperty = ucfirst($actionType).'Mode';
        if(empty($settings->$modeProperty)){
            return new WP_Error('cg_paid_action_preview_not_active','Activate pay for '.strtolower(cg_paid_actions_type_label($actionType)).' before creating a sandbox test link.');
        }
        $readiness = cg_paid_actions_get_ecommerce_readiness();
        if(empty($readiness['sandboxPaymentReady'])){
            return new WP_Error('cg_paid_action_sandbox_not_ready','Enable PayPal or Stripe sandbox testing and add its sandbox credentials in Ecommerce options first.');
        }

        $token = wp_generate_password(40,false,false);
        $lifetime = cg_paid_actions_sandbox_link_lifetime();
        $expires = time()+$lifetime;
        $stored = set_transient(cg_paid_actions_sandbox_link_transient_key($token),array(
            'userId'=>0,'createdByUserId'=>get_current_user_id(),'galleryId'=>$galleryId,'originGalleryId'=>$galleryId,
            'actionType'=>$actionType,'checkoutMode'=>'sandbox','accessMode'=>'visitor_sandbox','expires'=>$expires,
            'shared'=>!empty($settings->ShareCredits) ? 1 : 0
        ),$lifetime);
        if(!$stored){
            return new WP_Error('cg_paid_action_sandbox_link_store_failed','The sandbox test link could not be saved.');
        }
        return array('token'=>$token,'expires'=>$expires,'checkoutMode'=>'sandbox','accessMode'=>'visitor_sandbox');
    }
}

if(!function_exists('cg_paid_actions_preview_for_json')){
    function cg_paid_actions_preview_for_json($preview){
        if(empty($preview) || !is_array($preview)){
            return false;
        }
        return array(
            'actionType'=>cg_paid_actions_normalize_type(isset($preview['actionType']) ? $preview['actionType'] : ''),
            'expires'=>!empty($preview['expires']) ? absint($preview['expires']) : 0,
            'originGalleryId'=>!empty($preview['originGalleryId']) ? absint($preview['originGalleryId']) : 0,
            'targetGalleryId'=>!empty($preview['targetGalleryId']) ? absint($preview['targetGalleryId']) : 0,
            'shared'=>!empty($preview['shared']) ? 1 : 0,
            'checkoutMode'=>(!empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox') ? 'sandbox' : 'simulation',
            'accessMode'=>!empty($preview['accessMode']) ? sanitize_key($preview['accessMode']) : 'admin_preview',
            'userBound'=>!empty($preview['userId']) ? 1 : 0
        );
    }
}

if(!function_exists('cg_paid_actions_readiness_for_json')){
    function cg_paid_actions_readiness_for_json($readiness){
        $actionReady = array();
        foreach(cg_paid_actions_types() as $actionType){
            $actionReady[$actionType] = cg_paid_actions_can_sell_action($actionType,$readiness);
        }
        return array(
            'ready'=>!empty($readiness['ready']),
            'paymentReady'=>!empty($readiness['paymentReady']),
            'proAvailable'=>!empty($readiness['proAvailable']),
            'currencyReady'=>!empty($readiness['currencyReady']),
            'paypalReady'=>!empty($readiness['paypalReady']),
            'stripeReady'=>!empty($readiness['stripeReady']),
            'paypalSandboxReady'=>!empty($readiness['paypalSandboxReady']),
            'stripeSandboxReady'=>!empty($readiness['stripeSandboxReady']),
            'sandboxPaymentReady'=>!empty($readiness['sandboxPaymentReady']),
            'orderMailReady'=>!empty($readiness['orderMailReady']),
            'checks'=>!empty($readiness['checks']) && is_array($readiness['checks']) ? $readiness['checks'] : array(),
            'actionReady'=>$actionReady,
            'reasons'=>!empty($readiness['reasons']) ? array_values($readiness['reasons']) : array()
        );
    }
}

if(!function_exists('cg_paid_actions_settings_for_json')){
    function cg_paid_actions_settings_for_json($settings){
        return array(
            'galleryId'=>absint($settings->GalleryID),
            'uploadMode'=>absint($settings->UploadMode),
            'voteMode'=>absint($settings->VoteMode),
            'commentMode'=>absint($settings->CommentMode),
            'uploadPrice'=>number_format((float)$settings->UploadPrice,2,'.',''),
            'votePrice'=>number_format((float)$settings->VotePrice,2,'.',''),
            'commentPrice'=>number_format((float)$settings->CommentPrice,2,'.',''),
            'shareCredits'=>!empty($settings->ShareCredits) ? 1 : 0
        );
    }
}

if(!function_exists('cg_paid_actions_get_shared_price_warnings')){
    function cg_paid_actions_get_shared_price_warnings($galleryId){
        global $wpdb;

        $galleryId = absint($galleryId);
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return array();
        }
        $table = cg_paid_actions_table('settings');
        $current = cg_paid_actions_get_settings($galleryId);
        if(empty($current->ShareCredits)){
            return array();
        }
        $rows = $wpdb->get_results("SELECT GalleryID, UploadMode, VoteMode, CommentMode, UploadPrice, VotePrice, CommentPrice FROM $table WHERE GalleryID <> ".absint($galleryId));
        $warnings = array();
        foreach(cg_paid_actions_types() as $actionType){
            $uc = ucfirst($actionType);
            $modeProperty = $uc.'Mode';
            $priceProperty = $uc.'Price';
            if(empty($current->$modeProperty)){continue;}
            foreach($rows as $row){
                if(!cg_paid_actions_gallery_is_supported($row->GalleryID)){continue;}
                if(!empty($row->$modeProperty) && round((float)$row->$priceProperty,2)!==round((float)$current->$priceProperty,2)){
                    $warnings[$actionType] = 'Other paid-action galleries use a different '.strtolower(cg_paid_actions_type_label($actionType)).' price. Global credits bought here can also be used there.';
                    break;
                }
            }
        }
        return $warnings;
    }
}

if(!function_exists('cg_paid_actions_get_sharing_status')){
    function cg_paid_actions_get_sharing_status($galleryId){
        global $wpdb;

        $galleryId = absint($galleryId);
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return array('enabled'=>0,'eligibleGalleryCount'=>0);
        }
        $settings = cg_paid_actions_get_settings($galleryId);
        $eligibleGalleryCount = 0;
        $modeConditions = array();
        foreach(cg_paid_actions_types() as $actionType){
            $modeProperty = ucfirst($actionType).'Mode';
            if(!empty($settings->$modeProperty)){
                $modeConditions[] = $modeProperty.' > 0';
            }
        }
        if($galleryId && !empty($modeConditions)){
            $eligibleGalleryIds = $wpdb->get_col($wpdb->prepare(
                "SELECT GalleryID FROM ".cg_paid_actions_table('settings')." WHERE GalleryID <> %d AND (".implode(' OR ',$modeConditions).")",
                $galleryId
            ));
            foreach($eligibleGalleryIds as $eligibleGalleryId){
                if(cg_paid_actions_gallery_is_supported($eligibleGalleryId)){$eligibleGalleryCount++;}
            }
        }
        return array(
            'enabled'=>!empty($settings->ShareCredits) ? 1 : 0,
            'eligibleGalleryCount'=>$eligibleGalleryCount
        );
    }
}

add_action('wp_ajax_post_cg_paid_actions_save_settings','post_cg_paid_actions_save_settings');
if(!function_exists('post_cg_paid_actions_save_settings')){
    function post_cg_paid_actions_save_settings(){
        cg_backend_ajax_require_access_json();
        if(!current_user_can('manage_options')){
            cg_backend_ajax_error_json('Only administrators can change monetization settings.',403,'cg_paid_actions_missing_rights');
        }
        $galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
        $galleryHash = !empty($_POST['galleryHash']) ? sanitize_text_field(wp_unslash($_POST['galleryHash'])) : '';
        cg_backend_ajax_validate_gallery_hash_json($galleryId,$galleryHash);

        $values = array(
            'uploadEnabled'=>!empty($_POST['uploadEnabled']),
            'voteEnabled'=>!empty($_POST['voteEnabled']),
            'commentEnabled'=>!empty($_POST['commentEnabled']),
            'uploadPrice'=>isset($_POST['uploadPrice']) ? wp_unslash($_POST['uploadPrice']) : '',
            'votePrice'=>isset($_POST['votePrice']) ? wp_unslash($_POST['votePrice']) : '',
            'commentPrice'=>isset($_POST['commentPrice']) ? wp_unslash($_POST['commentPrice']) : '',
            'shareCredits'=>!empty($_POST['shareCredits'])
        );
        $saved = cg_paid_actions_save_settings($galleryId,$values);
        if(is_wp_error($saved)){
            cg_backend_ajax_error_json($saved->get_error_message(),400,$saved->get_error_code());
        }
        wp_send_json_success(array(
            'settings'=>cg_paid_actions_settings_for_json($saved),
            'readiness'=>cg_paid_actions_readiness_for_json(cg_paid_actions_get_ecommerce_readiness()),
            'priceWarnings'=>cg_paid_actions_get_shared_price_warnings($galleryId),
            'sharingStatus'=>cg_paid_actions_get_sharing_status($galleryId),
            'message'=>'Monetization settings saved.'
        ));
    }
}

add_action('wp_ajax_post_cg_paid_actions_create_preview','post_cg_paid_actions_create_preview');
if(!function_exists('post_cg_paid_actions_create_preview')){
    function post_cg_paid_actions_create_preview(){
        cg_backend_ajax_require_access_json();
        $galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
        $galleryHash = !empty($_POST['galleryHash']) ? sanitize_text_field(wp_unslash($_POST['galleryHash'])) : '';
        cg_backend_ajax_validate_gallery_hash_json($galleryId,$galleryHash);
        $actionType = cg_paid_actions_normalize_type(isset($_POST['actionType']) ? $_POST['actionType'] : '');
        if($actionType===''){$actionType = 'upload';}
		if(!cg_paid_actions_gallery_is_supported($galleryId)){
			cg_backend_ajax_error_json(
				'Earning options are available only for galleries created or copied with Contest Gallery 22.0.0 or later.',
				400,
				'cg_paid_actions_gallery_version_unsupported'
			);
		}
		$settings = cg_paid_actions_get_settings($galleryId);
		$modeProperty = ucfirst($actionType).'Mode';
		if(empty($settings->$modeProperty)){
			cg_backend_ajax_error_json('Activate pay for '.strtolower(cg_paid_actions_type_label($actionType)).' before opening its preview.',400,'cg_paid_action_preview_not_active');
		}

        $checkoutMode = !empty($_POST['checkoutMode']) && sanitize_key(wp_unslash($_POST['checkoutMode']))==='sandbox' ? 'sandbox' : 'simulation';
        $created = cg_paid_actions_create_preview_context($galleryId,$actionType,$checkoutMode);
        if(is_wp_error($created)){
            cg_backend_ajax_error_json($created->get_error_message(),400,$created->get_error_code());
        }

        $fallbackUrl = home_url('/');
        $galleryUrl = function_exists('cgl_get_gallery_parent_page_url') ? cgl_get_gallery_parent_page_url($galleryId,'cg_gallery',$fallbackUrl) : $fallbackUrl;
        $urlArgs = array('cg_paid_actions_preview'=>$created['token']);
        if($checkoutMode==='sandbox'){$urlArgs['test'] = 'true';}
        $url = add_query_arg($urlArgs,$galleryUrl);
        wp_send_json_success(array('url'=>$url,'expires'=>$created['expires'],'checkoutMode'=>$checkoutMode));
    }
}

add_action('wp_ajax_post_cg_paid_actions_create_sandbox_link','post_cg_paid_actions_create_sandbox_link');
if(!function_exists('post_cg_paid_actions_create_sandbox_link')){
    function post_cg_paid_actions_create_sandbox_link(){
        cg_backend_ajax_require_access_json();
        $galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
        $galleryHash = !empty($_POST['galleryHash']) ? sanitize_text_field(wp_unslash($_POST['galleryHash'])) : '';
        cg_backend_ajax_validate_gallery_hash_json($galleryId,$galleryHash);
        $actionType = cg_paid_actions_normalize_type(isset($_POST['actionType']) ? $_POST['actionType'] : '');
        if($actionType===''){$actionType = 'upload';}
        $created = cg_paid_actions_create_sandbox_link_context($galleryId,$actionType);
        if(is_wp_error($created)){
            cg_backend_ajax_error_json($created->get_error_message(),400,$created->get_error_code());
        }

        $fallbackUrl = home_url('/');
        $galleryUrl = function_exists('cgl_get_gallery_parent_page_url') ? cgl_get_gallery_parent_page_url($galleryId,'cg_gallery',$fallbackUrl) : $fallbackUrl;
        $url = add_query_arg(array('cg_paid_actions_sandbox'=>$created['token'],'test'=>'true'),$galleryUrl);
        wp_send_json_success(array(
            'url'=>$url,'expires'=>$created['expires'],'checkoutMode'=>'sandbox','accessMode'=>'visitor_sandbox',
            'message'=>'Sandbox test link created. It is valid for 24 hours.'
        ));
    }
}

add_action('wp_ajax_post_cg_paid_actions_state','post_cg_paid_actions_state');
add_action('wp_ajax_nopriv_post_cg_paid_actions_state','post_cg_paid_actions_state');
if(!function_exists('post_cg_paid_actions_state')){
	function post_cg_paid_actions_state(){
		cg_check_frontend_nonce();
		$galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
		if(!$galleryId){wp_send_json_error(array('message'=>cg_paid_actions_t('GalleryUnavailable'),'code'=>'cg_paid_action_gallery_missing'),400);}
		$userId = is_user_logged_in() ? get_current_user_id() : 0;
		$bootstrapToken = '';
		if(
			empty($_REQUEST['cg_paid_actions_preview']) && !empty($_POST['sandboxBootstrap']) &&
			is_user_logged_in() && current_user_can('manage_options') && cg_paid_actions_is_pro_available()
		){
			$settings = cg_paid_actions_get_settings($galleryId);
			$bootstrapType = '';
			foreach(cg_paid_actions_types() as $type){
				$property = ucfirst($type).'Mode';
				if(!empty($settings->$property)){$bootstrapType = $type;break;}
			}
			if($bootstrapType!==''){
				$created = cg_paid_actions_create_preview_context($galleryId,$bootstrapType,'sandbox');
				if(!is_wp_error($created)){
					$bootstrapToken = $created['token'];
					$_REQUEST['cg_paid_actions_preview'] = $bootstrapToken;
					$_POST['cg_paid_actions_preview'] = $bootstrapToken;
				}
			}
		}
		$preview = cg_paid_actions_get_preview_context($galleryId,true);
		if(!$preview && !empty($_POST['sandboxRequest']) && cg_paid_actions_request_preview_token()!==''){
			wp_send_json_error(array(
				'message'=>cg_paid_actions_t('SandboxLinkInvalid'),
				'code'=>'cg_paid_action_sandbox_link_invalid'
			),403);
		}
		$isSandbox = !empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox';
		$runtime = cg_paid_actions_get_gallery_runtime($galleryId,$userId,$isSandbox ? 'sandbox' : 'live');
		if($preview && !$isSandbox){
			foreach($runtime['actions'] as $type=>$action){
				if(absint($action['mode'])>0){
					$runtime['actions'][$type]['status'] = 'draft';
					$runtime['actions'][$type]['balance'] = cg_paid_actions_get_preview_balance($preview,$type);
				}
			}
		}elseif(!$preview){
			foreach($runtime['actions'] as $type=>$action){
				if(absint($action['mode'])===1){
					$runtime['actions'][$type]['mode'] = 0;
					$runtime['actions'][$type]['status'] = 'off';
				}
			}
		}
		$googleOptions = function_exists('cg_get_google_options') ? cg_get_google_options() : false;
		$googleClientId = (!empty($googleOptions) && !empty($googleOptions->ClientId)) ? contest_gal1ery_convert_for_html_output_without_nl2br($googleOptions->ClientId) : '';
		$googleButtonStyle = (!empty($googleOptions) && !empty($googleOptions->ButtonStyle) && $googleOptions->ButtonStyle==='dark') ? 'dark' : 'bright';
		$previewJson = cg_paid_actions_preview_for_json($preview);
		if($previewJson && !$isSandbox){
			$previewJson['used'] = cg_paid_actions_get_preview_usage($galleryId);
			$previewJson['stored'] = cg_paid_actions_get_preview_stored_counts($galleryId);
			$previewJson['canReset'] = cg_user_has_backend_access() ? 1 : 0;
		}
		wp_send_json_success(array(
			'runtime'=>$runtime,
			'isLoggedIn'=>!empty($userId),
			'userEmail'=>$userId ? wp_get_current_user()->user_email : '',
			'nonce'=>wp_create_nonce('cg1l_action'),
			'preview'=>$previewJson,
			'previewToken'=>$bootstrapToken,
			'googleClientId'=>$googleClientId,
			'googleButtonStyle'=>$googleButtonStyle,
			'authHtml'=>cg_pin_auth_render_paid_actions_auth($isSandbox,!empty($googleClientId))
		));
	}
}

add_action('wp_ajax_post_cg_paid_actions_preview_grant','post_cg_paid_actions_preview_grant');
if(!function_exists('post_cg_paid_actions_preview_grant')){
	function post_cg_paid_actions_preview_grant(){
		cg_check_frontend_nonce();
		$galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
		$preview = cg_paid_actions_get_preview_context($galleryId);
		if(!$preview || (!empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox')){
			wp_send_json_error(array('message'=>cg_paid_actions_t('PreviewEnded'),'code'=>'cg_paid_action_preview_expired'),403);
		}
		$quantities = array();
		foreach(cg_paid_actions_types() as $type){
			$quantities[$type] = isset($_POST[$type]) ? min(10000,absint($_POST[$type])) : 0;
		}
		$requestKey = isset($_POST['requestKey']) ? sanitize_text_field(wp_unslash($_POST['requestKey'])) : '';
		$result = cg_paid_actions_grant_preview_credits($preview,$quantities,$requestKey);
		if(is_wp_error($result)){
			wp_send_json_error(array('message'=>$result->get_error_message(),'code'=>$result->get_error_code()),400);
		}
		$runtime = cg_paid_actions_get_gallery_runtime($galleryId,get_current_user_id());
		foreach($runtime['actions'] as $type=>$action){
			if(absint($action['mode'])>0){
				$runtime['actions'][$type]['status'] = 'draft';
				$runtime['actions'][$type]['balance'] = cg_paid_actions_get_preview_balance($preview,$type);
			}
		}
		$previewJson = cg_paid_actions_preview_for_json($preview);
		$previewJson['used'] = cg_paid_actions_get_preview_usage($galleryId);
		$previewJson['stored'] = cg_paid_actions_get_preview_stored_counts($galleryId);
		$previewJson['canReset'] = 1;
		wp_send_json_success(array('runtime'=>$runtime,'preview'=>$previewJson,'nonce'=>wp_create_nonce('cg1l_action')));
	}
}

if(!function_exists('cg_paid_actions_reset_preview_feedback')){
	function cg_paid_actions_reset_preview_feedback($galleryId){
		global $wpdb;
		$galleryId = absint($galleryId);
		$votesTable = $wpdb->prefix.'contest_gal1ery_ip';
		$commentsTable = $wpdb->prefix.'contest_gal1ery_comments';
		$imagesTable = $wpdb->prefix.'contest_gal1ery';
		$removed = array('vote'=>0,'comment'=>0);
		$pids = $wpdb->get_col($wpdb->prepare(
			"SELECT pid FROM $votesTable WHERE GalleryID = %d AND IsPaidActionPreview = 1
			 UNION SELECT pid FROM $commentsTable WHERE GalleryID = %d AND IsPaidActionPreview = 1",
			$galleryId,$galleryId
		));
		foreach($pids as $pictureId){
			$pictureId = absint($pictureId);
			if(!$pictureId){continue;}
			$voteIds = array_values(array_filter(array_map('absint',$wpdb->get_col($wpdb->prepare(
				"SELECT id FROM $votesTable WHERE GalleryID = %d AND pid = %d AND IsPaidActionPreview = 1",$galleryId,$pictureId
			)))));
			$commentIds = array_values(array_filter(array_map('absint',$wpdb->get_col($wpdb->prepare(
				"SELECT id FROM $commentsTable WHERE GalleryID = %d AND pid = %d AND IsPaidActionPreview = 1",$galleryId,$pictureId
			)))));
			$statsLockFp = false;
			$commentsLockFp = false;
			$commentDeletionPlan = false;
			$commentCounts = false;
			$isActiveEntry = absint($wpdb->get_var($wpdb->prepare("SELECT Active FROM $imagesTable WHERE GalleryID = %d AND id = %d",$galleryId,$pictureId)))===1;
			$ratingFileData = false;
			if($isActiveEntry){
				cg1l_migrate_image_stats_to_folder($galleryId,true,true);
				$ratingFileData = cg1l_get_stats_for_update($galleryId,$pictureId,$statsLockFp);
				if($ratingFileData===false){
					cg1l_release_stats_lock($statsLockFp);
					return new WP_Error('cg_paid_action_preview_reset_stats',cg_paid_actions_t('PreviewResetFailed'));
				}
			}
			if(!empty($commentIds)){
				$commentDeletionPlan = cg1l_prepare_comment_file_deletion($galleryId,$pictureId,$commentIds,$commentsLockFp);
				if($commentDeletionPlan===false || !cg1l_apply_comment_file_deletion($commentDeletionPlan)){
					cg1l_release_comment_lock($commentsLockFp);
					cg1l_release_stats_lock($statsLockFp);
					return new WP_Error('cg_paid_action_preview_reset_comments',cg_paid_actions_t('PreviewResetFailed'));
				}
				$commentCounts = cg1l_get_comment_counts_from_data($commentDeletionPlan['comments_data']);
			}
			$wpdb->query('START TRANSACTION');
			$failed = false;
			if(!empty($commentIds)){
				$placeholders = implode(',',array_fill(0,count($commentIds),'%d'));
				$commentValues = array_merge($commentIds,array($galleryId,$pictureId));
				$cleared = $wpdb->query($wpdb->prepare(
					"UPDATE $votesTable SET CommentId = 0 WHERE CommentId IN ($placeholders) AND GalleryID = %d AND pid = %d",$commentValues
				));
				$deletedComments = $wpdb->query($wpdb->prepare(
					"DELETE FROM $commentsTable WHERE id IN ($placeholders) AND GalleryID = %d AND pid = %d AND IsPaidActionPreview = 1",$commentValues
				));
				if($cleared===false || $deletedComments===false){$failed = true;}else{$removed['comment'] += absint($deletedComments);}
			}
			if(!$failed && !empty($voteIds)){
				$placeholders = implode(',',array_fill(0,count($voteIds),'%d'));
				$voteValues = array_merge($voteIds,array($galleryId,$pictureId));
				$deletedVotes = $wpdb->query($wpdb->prepare(
					"DELETE FROM $votesTable WHERE id IN ($placeholders) AND GalleryID = %d AND pid = %d AND IsPaidActionPreview = 1",$voteValues
				));
				if($deletedVotes===false){$failed = true;}else{$removed['vote'] += absint($deletedVotes);}
			}
			if($failed){
				$wpdb->query('ROLLBACK');
				if($commentDeletionPlan!==false){cg1l_restore_comment_file_deletion($commentDeletionPlan);}
				cg1l_release_comment_lock($commentsLockFp);
				cg1l_release_stats_lock($statsLockFp);
				return new WP_Error('cg_paid_action_preview_reset_database',cg_paid_actions_t('PreviewResetFailed'));
			}
			$wpdb->query('COMMIT');
			cg1l_release_comment_lock($commentsLockFp);
			$correctedRatingCounts = cg_correct_entry_count($pictureId,$galleryId);
			if($correctedRatingCounts===false){
				cg1l_release_stats_lock($statsLockFp);
				return new WP_Error('cg_paid_action_preview_reset_counts',cg_paid_actions_t('PreviewResetFailed'));
			}
			if(is_array($commentCounts)){
				$wpdb->update($imagesTable,array('CountC'=>intval($commentCounts['total']),'CountCtoReview'=>intval($commentCounts['to_review'])),array('id'=>$pictureId,'GalleryID'=>$galleryId),array('%d','%d'),array('%d','%d'));
			}
			if($isActiveEntry){
				foreach($correctedRatingCounts as $ratingKey=>$ratingValue){$ratingFileData[$ratingKey] = intval($ratingValue);}
				if(is_array($commentCounts)){
					$ratingFileData['CountC'] = intval($commentCounts['total']);
					$ratingFileData['CountCtoReview'] = intval($commentCounts['hidden']);
				}
				$ratingFileData = cg1l_set_stats_with_lock($galleryId,$pictureId,$ratingFileData,$statsLockFp);
				cg1l_release_stats_lock($statsLockFp);
				if($ratingFileData===false){return new WP_Error('cg_paid_action_preview_reset_stats',cg_paid_actions_t('PreviewResetFailed'));}
			}else{
				cg1l_release_stats_lock($statsLockFp);
			}
			cg1l_mark_vote_comment_data_updated($galleryId,$pictureId);
		}
		return $removed;
	}
}

if(!function_exists('cg_paid_actions_get_preview_upload_deletion_data')){
	function cg_paid_actions_get_preview_upload_deletion_data($galleryId){
		global $wpdb;
		$imagesTable = $wpdb->prefix.'contest_gal1ery';
		$uploadIds = array();
		$multipleFilesToDelete = array();
		$uploadRows = $wpdb->get_results($wpdb->prepare(
			"SELECT id, MultipleFiles FROM $imagesTable WHERE GalleryID = %d AND IsPaidActionPreview = 1 ORDER BY id ASC",absint($galleryId)
		));
		foreach($uploadRows as $uploadRow){
			$uploadId = absint($uploadRow->id);
			if(!$uploadId){continue;}
			$uploadIds[] = $uploadId;
			if(!empty($uploadRow->MultipleFiles) && $uploadRow->MultipleFiles!=='""'){
				$multipleFiles = maybe_unserialize($uploadRow->MultipleFiles);
				if(is_array($multipleFiles) && !empty($multipleFiles)){
					$multipleFilesToDelete[$uploadId] = $multipleFiles;
				}
			}
		}
		return array('uploadIds'=>$uploadIds,'multipleFilesToDelete'=>$multipleFilesToDelete);
	}
}

add_action('wp_ajax_post_cg_paid_actions_preview_reset','post_cg_paid_actions_preview_reset');
if(!function_exists('post_cg_paid_actions_preview_reset')){
	function post_cg_paid_actions_preview_reset(){
		cg_check_frontend_nonce();
		$galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
		$preview = cg_paid_actions_get_preview_context($galleryId);
		if(!$preview || (!empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox') || !cg_user_has_backend_access()){
			wp_send_json_error(array('message'=>cg_paid_actions_t('PreviewEnded'),'code'=>'cg_paid_action_preview_expired'),403);
		}
		if(!cg_paid_actions_lock_preview_gallery($galleryId,10)){
			wp_send_json_error(array('message'=>cg_paid_actions_t('PreviewActionRunning'),'code'=>'cg_paid_action_preview_busy'),409);
		}
		global $wpdb;
		$imagesTable = $wpdb->prefix.'contest_gal1ery';
		$storedBefore = cg_paid_actions_get_preview_stored_counts($galleryId);
		$uploadDeletionData = cg_paid_actions_get_preview_upload_deletion_data($galleryId);
		$uploadIds = $uploadDeletionData['uploadIds'];
		$removed = $storedBefore;
		if(!empty($uploadIds)){
			$deletedWpUploads = cg_delete_images($galleryId,$uploadIds,array(),true,false,$uploadDeletionData['multipleFilesToDelete']);
			if(!empty($deletedWpUploads)){cg_delete_images_of_deleted_wp_uploads($deletedWpUploads);}
			$remainingUploads = absint($wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM $imagesTable WHERE GalleryID = %d AND IsPaidActionPreview = 1",$galleryId
			)));
			if($remainingUploads){
				cg_paid_actions_unlock_preview_gallery($galleryId);
				wp_send_json_error(array('message'=>cg_paid_actions_t('PreviewUploadsRemoveFailed'),'code'=>'cg_paid_action_preview_reset_uploads'),500);
			}
		}
		$feedbackRemoved = cg_paid_actions_reset_preview_feedback($galleryId);
		if(is_wp_error($feedbackRemoved)){
			cg_paid_actions_unlock_preview_gallery($galleryId);
			wp_send_json_error(array('message'=>$feedbackRemoved->get_error_message(),'code'=>$feedbackRemoved->get_error_code()),500);
		}
		$refunded = cg_paid_actions_refund_preview_usage($galleryId,get_current_user_id());
		if(is_wp_error($refunded)){
			cg_paid_actions_unlock_preview_gallery($galleryId);
			wp_send_json_error(array('message'=>$refunded->get_error_message(),'code'=>$refunded->get_error_code()),500);
		}
		cg_paid_actions_unlock_preview_gallery($galleryId);
		$runtime = cg_paid_actions_get_gallery_runtime($galleryId,get_current_user_id());
		foreach($runtime['actions'] as $type=>$action){
			if(absint($action['mode'])>0){
				$runtime['actions'][$type]['status'] = 'draft';
				$runtime['actions'][$type]['balance'] = cg_paid_actions_get_preview_balance($preview,$type);
			}
		}
		$previewJson = cg_paid_actions_preview_for_json($preview);
		$previewJson['used'] = cg_paid_actions_get_preview_usage($galleryId);
		$previewJson['stored'] = cg_paid_actions_get_preview_stored_counts($galleryId);
		$previewJson['canReset'] = 1;
		wp_send_json_success(array(
			'removed'=>$removed,'refunded'=>$refunded,'runtime'=>$runtime,'preview'=>$previewJson,
			'nonce'=>wp_create_nonce('cg1l_action'),'message'=>cg_paid_actions_t('PreviewResetComplete')
		));
	}
}

add_action('wp_ajax_post_cg_paid_actions_email_code_request','post_cg_paid_actions_email_code_request');
add_action('wp_ajax_nopriv_post_cg_paid_actions_email_code_request','post_cg_paid_actions_email_code_request');
if(!function_exists('post_cg_paid_actions_email_code_request')){
    function post_cg_paid_actions_email_code_request(){
        cg_check_frontend_nonce();
        if(is_user_logged_in()){
            wp_send_json_success(array('alreadyLoggedIn'=>true,'nonce'=>wp_create_nonce('cg1l_action')));
        }
        $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
        $galleryId = !empty($_POST['galleryId']) ? absint($_POST['galleryId']) : 0;
        $actionType = cg_paid_actions_normalize_type(isset($_POST['actionType']) ? $_POST['actionType'] : '');
        if(!is_email($email) || !$galleryId || $actionType===''){
            wp_send_json_error(array('message'=>cg_paid_actions_t('ValidEmailRequired'),'code'=>'cg_paid_action_invalid_email'),400);
        }
		if(!cg_paid_actions_gallery_is_supported($galleryId)){
            wp_send_json_error(array(
                'message'=>cg_paid_actions_t('GalleryUnsupported'),
                'code'=>'cg_paid_actions_gallery_version_unsupported'
			),400);
		}
		$preview = false;
		if(cg_paid_actions_request_preview_token()!==''){
			$preview = cg_paid_actions_get_preview_context($galleryId,true);
			if(!$preview){
				wp_send_json_error(array('message'=>cg_paid_actions_t('SandboxLinkInvalid'),'code'=>'cg_paid_action_sandbox_link_invalid'),403);
			}
		}
		$checkoutMode = (!empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox') ? 'sandbox' : 'live';

		$result = cg_pin_auth_request($email,'paid-actions',array(
			'source'=>'paid-actions','galleryId'=>$galleryId,'actionType'=>$actionType,'checkoutMode'=>$checkoutMode
		));
		if(is_wp_error($result)){
			$status = $result->get_error_code()==='cg_pin_rate_limited' ? 429 : 500;
			$errorData = $result->get_error_data();
			wp_send_json_error(array(
				'message'=>$result->get_error_message(),'code'=>$result->get_error_code(),
				'retryAfter'=>is_array($errorData) && !empty($errorData['retryAfter']) ? absint($errorData['retryAfter']) : 0
			),$status);
		}
		wp_send_json_success(array(
			'requestKey'=>$result['requestKey'],'expiresIn'=>$result['expiresIn'],'retryAfter'=>absint($result['retryAfter']),
			'message'=>cg_paid_actions_t('SixDigitPinSent')
		));
	}
}

add_action('wp_ajax_post_cg_paid_actions_email_code_verify','post_cg_paid_actions_email_code_verify');
add_action('wp_ajax_nopriv_post_cg_paid_actions_email_code_verify','post_cg_paid_actions_email_code_verify');
if(!function_exists('post_cg_paid_actions_email_code_verify')){
    function post_cg_paid_actions_email_code_verify(){
        cg_check_frontend_nonce();
        if(is_user_logged_in()){
            wp_send_json_success(array('reload'=>true,'sessionChanged'=>true));
        }
        $requestKey = isset($_POST['requestKey']) ? sanitize_text_field(wp_unslash($_POST['requestKey'])) : '';
        $code = isset($_POST['code']) ? preg_replace('/[^0-9]/','',(string)wp_unslash($_POST['code'])) : '';
		$result = cg_pin_auth_verify($requestKey,$code,'paid-actions');
		if($result===false){
			wp_send_json_error(array('message'=>cg_paid_actions_t('PinInvalidExpired'),'code'=>'cg_pin_invalid'),400);
		}
		if(is_wp_error($result)){
			$status = $result->get_error_code()==='cg_pin_attempts' ? 429 : 400;
			wp_send_json_error(array('message'=>$result->get_error_message(),'code'=>$result->get_error_code()),$status);
		}
		if(!empty($result['nextStep'])){wp_send_json_success($result);}
		cg_paid_actions_pin_login_response($result);
	}
}

if(!function_exists('cg_paid_actions_pin_login_response')){
function cg_paid_actions_pin_login_response($result){
	$context = !empty($result['context']) && is_array($result['context']) ? $result['context'] : array();
	if(empty($context['galleryId']) || !cg_paid_actions_gallery_is_supported($context['galleryId'])){
		wp_send_json_error(array(
			'message'=>cg_paid_actions_t('GalleryUnsupported'),
			'code'=>'cg_paid_actions_gallery_version_unsupported'
		),400);
	}
	$userId = absint($result['userId']);
	if(!cg1l_auth_eligible_user($userId,$result['email'])){
		wp_send_json_error(array('message'=>cg_paid_actions_t('PinInvalidExpired'),'code'=>'cg_pin_invalid'),400);
	}
	$environment = !empty($context['checkoutMode']) && $context['checkoutMode']==='sandbox' ? 'sandbox' : 'live';
	wp_set_current_user($userId);
	wp_set_auth_cookie($userId,true,is_ssl());
	wp_send_json_success(array(
		'userId'=>$userId,'email'=>$result['email'],'nonce'=>wp_create_nonce('cg1l_action'),
		'runtime'=>cg_paid_actions_get_gallery_runtime($context['galleryId'],$userId,$environment),
		'confirmationHtml'=>cg_pin_auth_render_paid_actions_confirmation()
	));
}
}

add_action('wp_ajax_post_cg_paid_actions_email_code_resend','post_cg_paid_actions_email_code_resend');
add_action('wp_ajax_nopriv_post_cg_paid_actions_email_code_resend','post_cg_paid_actions_email_code_resend');
if(!function_exists('post_cg_paid_actions_email_code_resend')){
	function post_cg_paid_actions_email_code_resend(){
		cg_check_frontend_nonce();
		$requestKey = isset($_POST['requestKey']) ? sanitize_text_field(wp_unslash($_POST['requestKey'])) : '';
		$result = cg_pin_auth_resend($requestKey);
		if(is_wp_error($result)){
			$status = $result->get_error_code()==='cg_pin_rate_limited' ? 429 : 400;
			$errorData = $result->get_error_data();
			wp_send_json_error(array(
				'message'=>$result->get_error_message(),'code'=>$result->get_error_code(),
				'retryAfter'=>is_array($errorData) && !empty($errorData['retryAfter']) ? absint($errorData['retryAfter']) : 0
			),$status);
		}
		wp_send_json_success(array(
			'requestKey'=>$result['requestKey'],'expiresIn'=>$result['expiresIn'],'retryAfter'=>absint($result['retryAfter']),
			'message'=>cg_paid_actions_t('NewSixDigitPinSent')
		));
	}
}

add_action('wp_ajax_post_cg_paid_actions_set_order_balance','post_cg_paid_actions_set_order_balance');
if(!function_exists('post_cg_paid_actions_set_order_balance')){
    function post_cg_paid_actions_set_order_balance(){
        cg_backend_ajax_require_access_json();
        if(!current_user_can('manage_options')){
            cg_backend_ajax_error_json('Only administrators can change customer credits.',403,'cg_paid_actions_missing_rights');
        }
        global $wpdb;
        $orderId = !empty($_POST['orderId']) ? absint($_POST['orderId']) : 0;
        $itemId = !empty($_POST['itemId']) ? absint($_POST['itemId']) : 0;
        $newBalance = isset($_POST['balance']) ? absint($_POST['balance']) : 0;
        $orders = cg_paid_actions_table('orders');
        $items = cg_paid_actions_table('order_items');
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT o.*, i.id AS CreditItemId, i.GalleryID AS CreditGalleryID, i.PaidActionType, i.PaidActionScope
             FROM $orders o INNER JOIN $items i ON i.ParentOrder = o.id
             WHERE o.id = %d AND i.id = %d LIMIT 1",
            $orderId,$itemId
        ));
        if(empty($row) || empty($row->IsFullPaid) || empty($row->WpUserId) || cg_paid_actions_normalize_type($row->PaidActionType)===''){
            cg_backend_ajax_error_json('This paid credit order cannot be adjusted.',400,'cg_paid_action_order_invalid');
        }
        $grantType = $wpdb->get_var($wpdb->prepare(
            "SELECT GrantType FROM ".cg_paid_actions_table('entitlements')." WHERE OrderItem = %d AND ParentOrder = %d LIMIT 1",
            $itemId,$orderId
        ));
        $isAdjustableSandbox = !empty($row->IsTest) && $grantType==='sandbox_purchase';
        if(!empty($row->IsTest) && !$isAdjustableSandbox){
            cg_backend_ajax_error_json('This paid credit order cannot be adjusted.',400,'cg_paid_action_order_invalid');
        }
        $status = strtolower((string)$row->PaymentStatus);
        if($status==='refunded'){
            cg_backend_ajax_error_json('Fully refunded orders cannot grant credits.',400,'cg_paid_action_order_refunded');
        }
        $balance = cg_paid_actions_set_scope_balance(
            $row->WpUserId,$row->CreditGalleryID,$row->PaidActionType,$row->PaidActionScope,
            $newBalance,$orderId,get_current_user_id(),$isAdjustableSandbox ? 'sandbox' : 'live'
        );
        if(is_wp_error($balance)){
            cg_backend_ajax_error_json($balance->get_error_message(),400,$balance->get_error_code());
        }
        wp_send_json_success(array('balance'=>$balance,'rows'=>cg_paid_actions_get_order_credit_rows($orderId)));
    }
}
