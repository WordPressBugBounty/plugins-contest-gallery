<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_network_normalize_host')){
	function cg_network_normalize_host($host){
		$host = strtolower(trim((string)$host));
		if(strpos($host,'www.')===0){
			$host = substr($host,4);
		}
		return $host;
	}
}

if(!function_exists('cg_network_normalize_site_url_for_signature')){
	function cg_network_normalize_site_url_for_signature($url){
		$parts = wp_parse_url((string)$url);
		if(empty($parts['host'])){
			return '';
		}
		$scheme = (!empty($parts['scheme'])) ? strtolower($parts['scheme']) : 'https';
		$host = cg_network_normalize_host($parts['host']);
		$path = (!empty($parts['path'])) ? rtrim($parts['path'],'/') : '';
		return $scheme.'://'.$host.$path.'/';
	}
}

if(!function_exists('cg_network_sort_recursive')){
	function cg_network_sort_recursive($value){
		if(!is_array($value)){
			return $value;
		}
		$isList = array_keys($value) === range(0,count($value)-1);
		foreach($value as $key => $item){
			$value[$key] = cg_network_sort_recursive($item);
		}
		if(!$isList){
			ksort($value);
		}
		return $value;
	}
}

if(!function_exists('cg_network_payload_hash_source')){
	function cg_network_payload_hash_source($payload){
		$verification = (!empty($payload['verification']) && is_array($payload['verification'])) ? $payload['verification'] : array();
		$source = array(
			'schema_version' => intval($payload['schema_version']),
			'export_mode' => (!empty($payload['export_mode'])) ? $payload['export_mode'] : '',
			'site' => (!empty($payload['site']) && is_array($payload['site'])) ? $payload['site'] : array(),
			'galleries' => (!empty($payload['galleries']) && is_array($payload['galleries'])) ? $payload['galleries'] : array(),
			'verification' => array(
				'url' => (!empty($verification['url'])) ? $verification['url'] : '',
				'fallback_url' => (!empty($verification['fallback_url'])) ? $verification['fallback_url'] : '',
				'public_key' => (!empty($verification['public_key'])) ? $verification['public_key'] : '',
			),
		);
		if(array_key_exists('action',$payload)){
			$source['action'] = $payload['action'];
		}
		if(array_key_exists('gallery_export_ids',$payload)){
			$source['gallery_export_ids'] = (is_array($payload['gallery_export_ids'])) ? $payload['gallery_export_ids'] : array();
		}
		return $source;
	}
}

if(!function_exists('cg_network_payload_hash')){
	function cg_network_payload_hash($payload){
		$source = cg_network_sort_recursive(cg_network_payload_hash_source($payload));
		return hash('sha256',json_encode($source));
	}
}

if(!function_exists('cg_network_get_keypair')){
	function cg_network_get_keypair(){
		$keypair = get_option('cg_network_keypair_v1');
		if(is_array($keypair) && !empty($keypair['private_key']) && !empty($keypair['public_key'])){
			return $keypair;
		}
		if(!function_exists('openssl_pkey_new')){
			return false;
		}
		$resource = openssl_pkey_new(array(
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		));
		if(!$resource){
			return false;
		}
		$privateKey = '';
		if(!openssl_pkey_export($resource,$privateKey)){
			return false;
		}
		$details = openssl_pkey_get_details($resource);
		if(empty($details['key'])){
			return false;
		}
		$keypair = array(
			'private_key' => $privateKey,
			'public_key' => $details['key'],
			'created_at' => time(),
		);
		update_option('cg_network_keypair_v1',$keypair,false);
		return $keypair;
	}
}

if(!function_exists('cg_network_get_endpoint_url')){
	function cg_network_get_endpoint_url($optionName,$constantName,$defaultUrl,$filterName){
		$custom = get_option($optionName);
		if(empty($custom) && defined($constantName)){
			$custom = constant($constantName);
		}
		$url = !empty($custom) ? $custom : $defaultUrl;
		$url = apply_filters($filterName,$url);
		$url = trim((string)$url);
		return !empty($url) ? esc_url_raw($url) : $defaultUrl;
	}
}

if(!function_exists('cg_network_get_submit_url')){
	function cg_network_get_submit_url(){
		return cg_network_get_endpoint_url('cg_network_submit_url','CG_NETWORK_SUBMIT_URL','https://www.contest-gallery.net/api/submit.php','cg_network_submit_url');
	}
}

if(!function_exists('cg_network_get_unpublish_url')){
	function cg_network_get_unpublish_url(){
		$defaultUrl = preg_replace('/submit\.php(\?.*)?$/','unpublish.php',cg_network_get_submit_url());
		return cg_network_get_endpoint_url('cg_network_unpublish_url','CG_NETWORK_UNPUBLISH_URL',$defaultUrl,'cg_network_unpublish_url');
	}
}

if(!function_exists('cg_network_append_endpoint_error_message')){
	function cg_network_append_endpoint_error_message($message,$body,$code,$endpointUrl){
		$reference = '';
		if(!empty($body['audit_id']) && !is_array($body['audit_id'])){
			$reference = ' Reference ID: '.sanitize_text_field($body['audit_id']).'.';
		}
		if(!empty($body['errors']) && is_array($body['errors'])){
			$cleanErrors = array();
			foreach($body['errors'] as $bodyError){
				$cleanErrors[] = sanitize_text_field($bodyError);
			}
			return $message.' '.implode(' ',$cleanErrors).$reference;
		}
		if($code){
			return $message.' Network endpoint returned HTTP '.intval($code).' for '.esc_url_raw($endpointUrl).'.'.$reference;
		}
		return $message.' Network endpoint did not return a valid response.'.$reference;
	}
}

if(!function_exists('cg_network_get_plugin_name')){
	function cg_network_get_plugin_name(){
		return cg_get_version()==='contest-gallery-pro' ? 'Contest Gallery PRO' : 'Contest Gallery';
	}
}

if(!function_exists('cg_network_get_publish_states')){
	function cg_network_get_publish_states(){
		$states = get_option('cg_network_publish_state');
		return is_array($states) ? $states : array();
	}
}

if(!function_exists('cg_network_has_auto_update_states')){
	function cg_network_has_auto_update_states($states = null){
		if($states===null){
			$states = cg_network_get_publish_states();
		}
		if(!is_array($states)){
			return false;
		}
		foreach($states as $state){
			if(!empty($state['published']) && !empty($state['auto_update_enabled'])){
				return true;
			}
		}
		return false;
	}
}

if(!function_exists('cg_network_schedule_auto_update')){
	function cg_network_schedule_auto_update(){
		if(!function_exists('wp_next_scheduled') || !function_exists('wp_schedule_event')){
			return;
		}
		if(!wp_next_scheduled('cg_network_auto_update_event')){
			wp_schedule_event(time()+3600,'daily','cg_network_auto_update_event');
		}
	}
}

if(!function_exists('cg_network_unschedule_auto_update')){
	function cg_network_unschedule_auto_update(){
		if(function_exists('wp_clear_scheduled_hook')){
			wp_clear_scheduled_hook('cg_network_auto_update_event');
		}
	}
}

if(!function_exists('cg_network_ensure_auto_update_schedule')){
	function cg_network_ensure_auto_update_schedule(){
		if(cg_network_has_auto_update_states()){
			cg_network_schedule_auto_update();
		}else{
			cg_network_unschedule_auto_update();
		}
	}
}

if(!function_exists('cg_network_prepare_export_settings')){
	function cg_network_prepare_export_settings($galleryId,$settings){
		$settings = is_array($settings) ? $settings : array();
		$galleryId = intval($galleryId);
		$title = (!empty($settings['title'])) ? cg_network_trim_text($settings['title'],180) : '';
		$description = (!empty($settings['description'])) ? cg_network_trim_text($settings['description'],360) : '';
		$linkMode = (!empty($settings['link_mode']) && $settings['link_mode']==='custom') ? 'custom' : 'gallery';
		$customUrl = (!empty($settings['custom_url'])) ? esc_url_raw($settings['custom_url']) : '';
		return array(
			'title' => $title,
			'description' => $description,
			'link_mode' => $linkMode,
			'custom_url' => $customUrl,
			'page_type' => 'gallery',
			'source_mode' => ($linkMode==='custom') ? 'custom_network_url' : 'custom_network_modal',
			'gallery_id' => $galleryId,
		);
	}
}

if(!function_exists('cg_network_export_settings_to_overrides')){
	function cg_network_export_settings_to_overrides($settings){
		$settings = is_array($settings) ? $settings : array();
		$linkMode = (!empty($settings['link_mode']) && $settings['link_mode']==='custom') ? 'custom' : 'gallery';
		return array(
			'title' => (!empty($settings['title'])) ? $settings['title'] : '',
			'description' => (!empty($settings['description'])) ? $settings['description'] : '',
			'page_type' => 'gallery',
			'contest_url' => ($linkMode==='custom' && !empty($settings['custom_url'])) ? $settings['custom_url'] : '',
			'source_mode' => ($linkMode==='custom') ? 'custom_network_url' : 'custom_network_modal',
		);
	}
}

if(!function_exists('cg_network_public_slug_from_text')){
	function cg_network_public_slug_from_text($text,$fallback){
		$text = strtolower(cg_network_trim_text($text,90));
		$slug = preg_replace('/[^a-z0-9]+/','-',$text);
		$slug = trim($slug,'-');
		if($slug===''){
			$slug = sanitize_title($fallback);
		}
		if($slug===''){
			$slug = 'gallery';
		}
		return substr($slug,0,70);
	}
}

if(!function_exists('cg_network_derive_gallery_detail_url')){
	function cg_network_derive_gallery_detail_url($galleryId,$state){
		$galleryId = intval($galleryId);
		if(!$galleryId || empty($state['site_detail_url'])){
			return '';
		}
		$settings = (!empty($state['export_settings']) && is_array($state['export_settings'])) ? $state['export_settings'] : array();
		$title = (!empty($settings['title'])) ? $settings['title'] : 'gallery-'.$galleryId;
		$titleSlug = cg_network_public_slug_from_text($title,'gallery-'.$galleryId);
		return trailingslashit(esc_url_raw($state['site_detail_url'])).'gallery-'.$galleryId.'-'.$titleSlug.'/';
	}
}

if(!function_exists('cg_network_format_publish_time')){
	function cg_network_format_publish_time($publishedAtGmt){
		$publishedAtGmt = trim((string)$publishedAtGmt);
		if($publishedAtGmt===''){
			return '';
		}
		$format = trim(get_option('date_format').' '.get_option('time_format'));
		if(function_exists('get_date_from_gmt')){
			return get_date_from_gmt($publishedAtGmt,$format);
		}
		return $publishedAtGmt;
	}
}

if(!function_exists('cg_network_next_auto_update_at_gmt')){
	function cg_network_next_auto_update_at_gmt(){
		if(!function_exists('wp_next_scheduled')){
			return '';
		}
		$next = wp_next_scheduled('cg_network_auto_update_event');
		if(!$next){
			return '';
		}
		return gmdate('Y-m-d H:i:s',intval($next));
	}
}

if(!function_exists('cg_network_prepare_publish_state_for_ui')){
	function cg_network_prepare_publish_state_for_ui($galleryId,$state){
		if(empty($state) || !is_array($state) || empty($state['published'])){
			return array('published' => 0);
		}
		$publishedAtGmt = (!empty($state['published_at_gmt'])) ? $state['published_at_gmt'] : '';
		$lastAutoUpdateAtGmt = (!empty($state['last_auto_update_at_gmt'])) ? $state['last_auto_update_at_gmt'] : '';
		$lastAutoUpdateFailedAtGmt = (!empty($state['last_auto_update_failed_at_gmt'])) ? $state['last_auto_update_failed_at_gmt'] : '';
		$lastNetworkUpdateAtGmt = (!empty($state['last_network_update_at_gmt'])) ? $state['last_network_update_at_gmt'] : (($lastAutoUpdateAtGmt!=='') ? $lastAutoUpdateAtGmt : $publishedAtGmt);
		$nextAutoUpdateAtGmt = cg_network_next_auto_update_at_gmt();
		$exportSettings = (!empty($state['export_settings']) && is_array($state['export_settings'])) ? cg_network_prepare_export_settings($galleryId,$state['export_settings']) : array();
		$galleryDetailUrl = (!empty($state['gallery_detail_url'])) ? esc_url_raw($state['gallery_detail_url']) : cg_network_derive_gallery_detail_url($galleryId,$state);
		return array(
			'published' => 1,
			'gallery_id' => intval($galleryId),
			'published_at_gmt' => $publishedAtGmt,
			'published_at_text' => cg_network_format_publish_time($publishedAtGmt),
			'last_network_update_at_gmt' => $lastNetworkUpdateAtGmt,
			'last_network_update_at_text' => ($lastNetworkUpdateAtGmt!=='') ? cg_network_format_publish_time($lastNetworkUpdateAtGmt) : '',
			'site_detail_url' => (!empty($state['site_detail_url'])) ? esc_url_raw($state['site_detail_url']) : '',
			'gallery_detail_url' => $galleryDetailUrl,
			'site_slug' => (!empty($state['site_slug'])) ? sanitize_text_field($state['site_slug']) : '',
			'accepted_galleries' => (!empty($state['accepted_galleries']) && is_array($state['accepted_galleries'])) ? array_values(array_map('sanitize_text_field',$state['accepted_galleries'])) : array(),
			'auto_update_enabled' => !empty($state['auto_update_enabled']) ? 1 : 0,
			'auto_update_interval' => (!empty($state['auto_update_interval'])) ? sanitize_text_field($state['auto_update_interval']) : 'daily',
			'last_auto_update_at_gmt' => $lastAutoUpdateAtGmt,
			'last_auto_update_at_text' => ($lastAutoUpdateAtGmt!=='') ? cg_network_format_publish_time($lastAutoUpdateAtGmt) : '',
			'last_auto_update_failed_at_gmt' => $lastAutoUpdateFailedAtGmt,
			'last_auto_update_failed_at_text' => ($lastAutoUpdateFailedAtGmt!=='') ? cg_network_format_publish_time($lastAutoUpdateFailedAtGmt) : '',
			'last_auto_update_error' => (!empty($state['last_auto_update_error'])) ? sanitize_text_field($state['last_auto_update_error']) : '',
			'next_auto_update_at_gmt' => $nextAutoUpdateAtGmt,
			'next_auto_update_at_text' => ($nextAutoUpdateAtGmt!=='') ? cg_network_format_publish_time($nextAutoUpdateAtGmt) : '',
			'export_settings' => $exportSettings,
		);
	}
}

if(!function_exists('cg_network_get_publish_state')){
	function cg_network_get_publish_state($galleryId){
		$states = cg_network_get_publish_states();
		$key = (string)intval($galleryId);
		return (!empty($states[$key]) && is_array($states[$key])) ? cg_network_prepare_publish_state_for_ui($galleryId,$states[$key]) : array('published' => 0);
	}
}

if(!function_exists('cg_network_set_publish_state')){
	function cg_network_set_publish_state($galleryId,$body,$exportSettings = array(),$isAutoUpdate = false){
		$galleryId = intval($galleryId);
		$states = cg_network_get_publish_states();
		$key = (string)$galleryId;
		$previous = (!empty($states[$key]) && is_array($states[$key])) ? $states[$key] : array();
		if(empty($exportSettings) && !empty($previous['export_settings']) && is_array($previous['export_settings'])){
			$exportSettings = $previous['export_settings'];
		}
		$exportSettings = cg_network_prepare_export_settings($galleryId,$exportSettings);
		$galleryDetailUrl = '';
		if(!empty($body['gallery_detail_url']) && !is_array($body['gallery_detail_url'])){
			$galleryDetailUrl = esc_url_raw($body['gallery_detail_url']);
		}elseif(!empty($body['gallery_detail_urls']) && is_array($body['gallery_detail_urls']) && !empty($body['gallery_detail_urls'][0]) && !is_array($body['gallery_detail_urls'][0])){
			$galleryDetailUrl = esc_url_raw($body['gallery_detail_urls'][0]);
		}elseif(!empty($previous['gallery_detail_url'])){
			$galleryDetailUrl = esc_url_raw($previous['gallery_detail_url']);
		}
		$nowGmt = current_time('mysql',true);
		$states[$key] = array(
			'published' => 1,
			'published_at_gmt' => (!empty($previous['published_at_gmt'])) ? $previous['published_at_gmt'] : $nowGmt,
			'last_network_update_at_gmt' => $nowGmt,
			'site_detail_url' => (!empty($body['site_detail_url']) && !is_array($body['site_detail_url'])) ? esc_url_raw($body['site_detail_url']) : '',
			'gallery_detail_url' => $galleryDetailUrl,
			'site_slug' => (!empty($body['site_slug']) && !is_array($body['site_slug'])) ? sanitize_text_field($body['site_slug']) : '',
			'accepted_galleries' => (!empty($body['accepted_galleries']) && is_array($body['accepted_galleries'])) ? array_values(array_map('sanitize_text_field',$body['accepted_galleries'])) : array(),
			'auto_update_enabled' => 1,
			'auto_update_interval' => 'daily',
			'last_auto_update_at_gmt' => $isAutoUpdate ? $nowGmt : ((!empty($previous['last_auto_update_at_gmt'])) ? $previous['last_auto_update_at_gmt'] : ''),
			'last_auto_update_failed_at_gmt' => '',
			'last_auto_update_error' => '',
			'export_settings' => $exportSettings,
		);
		update_option('cg_network_publish_state',$states,false);
		cg_network_schedule_auto_update();
		return cg_network_prepare_publish_state_for_ui($galleryId,$states[$key]);
	}
}

if(!function_exists('cg_network_set_publish_state_error')){
	function cg_network_set_publish_state_error($galleryId,$message){
		$states = cg_network_get_publish_states();
		$key = (string)intval($galleryId);
		if(isset($states[$key]) && is_array($states[$key])){
			$states[$key]['last_auto_update_error'] = cg_network_trim_text($message,240);
			$states[$key]['last_auto_update_failed_at_gmt'] = current_time('mysql',true);
			update_option('cg_network_publish_state',$states,false);
		}
	}
}

if(!function_exists('cg_network_delete_publish_state')){
	function cg_network_delete_publish_state($galleryId){
		$states = cg_network_get_publish_states();
		$key = (string)intval($galleryId);
		if(isset($states[$key])){
			unset($states[$key]);
			update_option('cg_network_publish_state',$states,false);
		}
		cg_network_ensure_auto_update_schedule();
		return array('published' => 0);
	}
}

if(!function_exists('cg_network_trim_text')){
	function cg_network_trim_text($value,$limit){
		$value = trim(wp_strip_all_tags(html_entity_decode((string)$value,ENT_QUOTES,'UTF-8')));
		$value = preg_replace('/\s+/',' ',$value);
		if(strlen($value)>$limit){
			$value = substr($value,0,$limit);
		}
		return $value;
	}
}

if(!function_exists('cg_network_slug')){
	function cg_network_slug($value,$fallback){
		$value = strtolower(cg_network_trim_text($value,120));
		$value = preg_replace('/[^a-z0-9]+/','-',$value);
		$value = trim($value,'-');
		if($value===''){
			$value = $fallback;
		}
		return substr($value,0,90);
	}
}

if(!function_exists('cg_network_read_gallery_options_json')){
	function cg_network_read_gallery_options_json($galleryId){
		$wpUploadDir = wp_upload_dir();
		$file = $wpUploadDir['basedir'].'/contest-gallery/gallery-id-'.$galleryId.'/json/'.$galleryId.'-options.json';
		if(!file_exists($file)){
			return array();
		}
		$options = json_decode(file_get_contents($file),true);
		if(!is_array($options)){
			return array();
		}
		if(!empty($options[$galleryId]) && is_array($options[$galleryId])){
			return $options[$galleryId];
		}
		return $options;
	}
}

if(!function_exists('cg_network_get_gallery_tags')){
	function cg_network_get_gallery_tags($galleryId,$title){
		global $wpdb;
		$tableCategories = $wpdb->prefix.'contest_gal1ery_categories';
		$tags = array();
		$rows = $wpdb->get_results($wpdb->prepare("SELECT Name FROM $tableCategories WHERE GalleryID = %d AND Active = 1 ORDER BY Field_Order ASC",array($galleryId)));
		foreach($rows as $row){
			if(!empty($row->Name)){
				$tags[] = cg_network_trim_text($row->Name,60);
			}
		}
		foreach(preg_split('/\s+/',strtolower($title)) as $word){
			$word = preg_replace('/[^a-z0-9-]/','',$word);
			if(strlen($word)>=4){
				$tags[] = $word;
			}
		}
		$tags = array_values(array_unique(array_filter($tags)));
		return array_slice($tags,0,12);
	}
}

if(!function_exists('cg_network_gallery_text_defaults')){
	function cg_network_gallery_text_defaults($galleryId,$optionsRow){
		return array(
			'title' => cg_network_trim_text('Contest Gallery '.$galleryId,180),
			'description' => cg_network_trim_text('Sub title contest gallery '.$galleryId,360),
		);
	}
}

if(!function_exists('cg_network_get_gallery_activity')){
	function cg_network_get_gallery_activity($galleryId){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery';
		$row = $wpdb->get_row($wpdb->prepare("
			SELECT COUNT(*) AS entries,
				COALESCE(SUM(CountC),0) AS comments,
				COALESCE(SUM(CountR + CountS + addCountS + addCountR1 + addCountR2 + addCountR3 + addCountR4 + addCountR5 + addCountR6 + addCountR7 + addCountR8 + addCountR9 + addCountR10),0) AS votes
			FROM $table
			WHERE GalleryID = %d AND Active = 1
		",array($galleryId)));
		return array(
			'entries' => (!empty($row->entries)) ? intval($row->entries) : 0,
			'votes' => (!empty($row->votes)) ? intval($row->votes) : 0,
			'comments' => (!empty($row->comments)) ? intval($row->comments) : 0,
		);
	}
}

if(!function_exists('cg_network_gallery_page_is_allowed')){
	function cg_network_gallery_page_is_allowed($url,$slug){
		if(empty($url)){
			return false;
		}
		$homeHost = cg_network_normalize_host(wp_parse_url(home_url('/'),PHP_URL_HOST));
		$urlHost = cg_network_normalize_host(wp_parse_url($url,PHP_URL_HOST));
		if($homeHost==='' || $urlHost==='' || $homeHost!==$urlHost){
			return false;
		}
		$path = wp_parse_url($url,PHP_URL_PATH);
		$path = '/'.ltrim((string)$path,'/');
		$expected = '/'.trim($slug,'/').'/';
		return (strpos($path,$expected)===0 || rtrim($path,'/')===rtrim($expected,'/'));
	}
}

if(!function_exists('cg_network_custom_contest_url_is_allowed')){
	function cg_network_custom_contest_url_is_allowed($url){
		if(empty($url) || !filter_var($url,FILTER_VALIDATE_URL)){
			return false;
		}
		$homeHost = cg_network_normalize_host(wp_parse_url(home_url('/'),PHP_URL_HOST));
		$urlHost = cg_network_normalize_host(wp_parse_url($url,PHP_URL_HOST));
		return ($homeHost!=='' && $urlHost!=='' && $homeHost===$urlHost);
	}
}

if(!function_exists('cg_network_get_gallery_page_choices')){
	function cg_network_get_gallery_page_choices($optionsRow){
		$choices = array();
		$map = array(
			'gallery' => array(
				'field' => 'WpPageParent',
				'slug' => 'contest-galleries',
				'label' => 'Gallery page',
			),
		);
		foreach($map as $type => $config){
			$pageId = (!empty($optionsRow->{$config['field']})) ? intval($optionsRow->{$config['field']}) : 0;
			if(!$pageId){
				continue;
			}
			$url = get_permalink($pageId);
			if(!$url || !cg_network_gallery_page_is_allowed($url,$config['slug'])){
				continue;
			}
			$choices[] = array(
				'type' => $type,
				'label' => $config['label'],
				'url' => $url,
				'page_id' => $pageId,
			);
		}
		return $choices;
	}
}

if(!function_exists('cg_network_get_gallery_export_context')){
	function cg_network_get_gallery_export_context($galleryId){
		global $wpdb;
		$tableOptions = $wpdb->prefix.'contest_gal1ery_options';
		$optionsRow = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableOptions WHERE id = %d",array($galleryId)));
		if(empty($optionsRow)){
			return array(false,'Gallery could not be found.',array());
		}
		$defaults = cg_network_gallery_text_defaults($galleryId,$optionsRow);
		$pages = cg_network_get_gallery_page_choices($optionsRow);
		if(!count($pages)){
			return array(false,'Contest Gallery Network export requires a gallery page with the new /contest-galleries/ URL structure. Copy this gallery first. The copied gallery gets the new URL structure and can then be published to the Network.',array(
				'gallery_id' => intval($galleryId),
				'title' => $defaults['title'],
				'description' => $defaults['description'],
				'pages' => array(),
			));
		}
		return array(true,'',array(
			'gallery_id' => intval($galleryId),
			'title' => $defaults['title'],
			'description' => $defaults['description'],
			'pages' => $pages,
		));
	}
}

if(!function_exists('cg_network_get_gallery_page_url')){
	function cg_network_get_gallery_page_url($optionsRow,$pageType){
		$choices = cg_network_get_gallery_page_choices($optionsRow);
		foreach($choices as $choice){
			if($choice['type']===$pageType){
				return $choice['url'];
			}
		}
		if(!empty($choices[0]['url'])){
			return $choices[0]['url'];
		}
		return '';
	}
}

if(!function_exists('cg_network_get_preview_images')){
	function cg_network_get_preview_images($galleryId){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery';
		$rows = $wpdb->get_results($wpdb->prepare("
			SELECT id, WpUpload, NamePic, CountC, CountR, CountS, addCountS
			FROM $table
			WHERE GalleryID = %d AND Active = 1 AND WpUpload > 0 AND ImgType IN ('jpg','jpeg','png','gif')
			ORDER BY (CountC + CountR + CountS + addCountS) DESC, id DESC
			LIMIT 4
		",array($galleryId)));
		$images = array();
		foreach($rows as $row){
			$src = wp_get_attachment_image_src($row->WpUpload,'medium');
			if(empty($src[0])){
				$src = wp_get_attachment_image_src($row->WpUpload,'thumbnail');
			}
			if(!empty($src[0])){
				$images[] = array(
					'src' => $src[0],
					'alt' => cg_network_trim_text($row->NamePic,120),
					'width' => (!empty($src[1])) ? intval($src[1]) : 0,
					'height' => (!empty($src[2])) ? intval($src[2]) : 0,
				);
			}
		}
		return $images;
	}
}

if(!function_exists('cg_network_get_gallery_category_names')){
	function cg_network_get_gallery_category_names($galleryId){
		global $wpdb;
		$tableCategories = $wpdb->prefix.'contest_gal1ery_categories';
		$rows = $wpdb->get_results($wpdb->prepare("SELECT id, Name FROM $tableCategories WHERE GalleryID = %d AND Active = 1",array($galleryId)));
		$categories = array();
		foreach($rows as $row){
			$categories[intval($row->id)] = cg_network_trim_text($row->Name,80);
		}
		return $categories;
	}
}

if(!function_exists('cg_network_featured_entry_votes_sql')){
	function cg_network_featured_entry_votes_sql(){
		return '(CountR + CountS + addCountS + addCountR1 + addCountR2 + addCountR3 + addCountR4 + addCountR5 + addCountR6 + addCountR7 + addCountR8 + addCountR9 + addCountR10)';
	}
}

if(!function_exists('cg_network_featured_entry_from_row')){
	function cg_network_featured_entry_from_row($row,$categories){
		$image = wp_get_attachment_image_src($row->WpUpload,'medium_large');
		if(empty($image[0])){
			$image = wp_get_attachment_image_src($row->WpUpload,'medium');
		}
		if(empty($image[0])){
			$image = wp_get_attachment_image_src($row->WpUpload,'thumbnail');
		}
		if(empty($image[0])){
			return false;
		}
		$title = cg_network_trim_text($row->NamePic,140);
		if($title===''){
			$title = 'Entry '.$row->id;
		}
		$categoryId = intval($row->Category);
		$category = (!empty($categories[$categoryId])) ? $categories[$categoryId] : '';
		$url = '';
		if(!empty($row->WpPage) && get_post_status($row->WpPage)!=='trash'){
			$permalink = get_permalink($row->WpPage);
			if($permalink){
				if(function_exists('cgl_build_entry_context_url')){
					$url = cgl_build_entry_context_url($permalink,array('gallery_id'=>intval($row->GalleryID),'from_gallery_page_number'=>1));
				}else{
					$url = add_query_arg('cgl_from_gallery_page',1,$permalink);
				}
			}
		}
		return array(
			'id' => 'entry-'.intval($row->id),
			'title' => $title,
			'category' => $category,
			'image' => array(
				'src' => $image[0],
				'alt' => $title,
				'width' => (!empty($image[1])) ? intval($image[1]) : 0,
				'height' => (!empty($image[2])) ? intval($image[2]) : 0,
			),
			'url' => $url,
			'activity' => array(
				'votes' => intval($row->Votes),
				'comments' => intval($row->CountC),
			),
			'created_at' => (!empty($row->Timestamp)) ? gmdate('c',intval($row->Timestamp)) : '',
			'winner' => (!empty($row->Winner)) ? 1 : 0,
		);
	}
}

if(!function_exists('cg_network_get_featured_entries')){
	function cg_network_get_featured_entries($galleryId){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery';
		$votesSql = cg_network_featured_entry_votes_sql();
		$select = "SELECT id, GalleryID, WpUpload, WpPage, NamePic, CountC, Category, Timestamp, Winner, $votesSql AS Votes
			FROM $table
			WHERE GalleryID = %d AND Active = 1 AND WpUpload > 0 AND ImgType IN ('jpg','jpeg','png','gif')";
		$topRows = $wpdb->get_results($wpdb->prepare($select." ORDER BY Votes DESC, CountC DESC, id DESC LIMIT 8",array($galleryId)));
		$newRows = $wpdb->get_results($wpdb->prepare($select." ORDER BY id DESC LIMIT 8",array($galleryId)));
		$categories = cg_network_get_gallery_category_names($galleryId);
		$featured = array();
		$used = array();
		$appendRows = function($rows,$limit) use (&$featured,&$used,$categories){
			foreach($rows as $row){
				if(count($featured)>=$limit){
					break;
				}
				$id = intval($row->id);
				if(!empty($used[$id])){
					continue;
				}
				$entry = cg_network_featured_entry_from_row($row,$categories);
				if($entry===false){
					continue;
				}
				$used[$id] = true;
				$featured[] = $entry;
			}
		};
		$appendRows($topRows,4);
		$appendRows($newRows,8);
		$appendRows($topRows,8);
		return $featured;
	}
}

if(!function_exists('cg_network_get_gallery_contest_url')){
	function cg_network_get_gallery_contest_url($optionsRow,$pageType = ''){
		$url = cg_network_get_gallery_page_url($optionsRow,$pageType);
		if($url){
			return $url;
		}
		return home_url('/');
	}
}

if(!function_exists('cg_network_build_gallery_payload')){
	function cg_network_build_gallery_payload($galleryId,$override = array()){
		global $wpdb;
		$tableOptions = $wpdb->prefix.'contest_gal1ery_options';
		$optionsRow = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tableOptions WHERE id = %d",array($galleryId)));
		if(empty($optionsRow)){
			return false;
		}
		$choices = cg_network_get_gallery_page_choices($optionsRow);
		if(!count($choices)){
			return false;
		}
		$defaults = cg_network_gallery_text_defaults($galleryId,$optionsRow);
		$title = $defaults['title'];
		$description = $defaults['description'];
		$pageType = (!empty($override['page_type'])) ? sanitize_key($override['page_type']) : '';
		$contestUrl = '';
		if(!empty($override['contest_url'])){
			$overrideContestUrl = esc_url_raw($override['contest_url']);
			if(cg_network_custom_contest_url_is_allowed($overrideContestUrl)){
				$contestUrl = $overrideContestUrl;
			}
		}
		if(!empty($override['title'])){
			$title = cg_network_trim_text($override['title'],180);
		}
		if(!empty($override['description'])){
			$description = cg_network_trim_text($override['description'],360);
		}
		if($title===''){
			$title = $defaults['title'];
		}
		if($description===''){
			$description = $defaults['description'];
		}
		if($contestUrl===''){
			$contestUrl = cg_network_get_gallery_contest_url($optionsRow,$pageType);
		}
		return array(
			'id' => 'gallery-'.$galleryId,
			'gallery_id' => intval($galleryId),
			'slug' => cg_network_slug($title,'gallery-'.$galleryId),
			'title' => $title,
			'description' => $description,
			'tags' => cg_network_get_gallery_tags($galleryId,$title),
			'contest_url' => $contestUrl,
			'preview_images' => cg_network_get_preview_images($galleryId),
			'featured_entries' => cg_network_get_featured_entries($galleryId),
			'activity' => cg_network_get_gallery_activity($galleryId),
			'source_mode' => (!empty($override['source_mode'])) ? cg_network_trim_text($override['source_mode'],60) : 'gallery_defaults',
		);
	}
}

if(!function_exists('cg_network_get_all_gallery_ids')){
	function cg_network_get_all_gallery_ids(){
		global $wpdb;
		$tableOptions = $wpdb->prefix.'contest_gal1ery_options';
		$ids = $wpdb->get_col("SELECT id FROM $tableOptions ORDER BY id ASC");
		$result = array();
		foreach($ids as $id){
			$result[] = intval($id);
		}
		return $result;
	}
}

if(!function_exists('cg_network_get_exportable_gallery_ids')){
	function cg_network_get_exportable_gallery_ids($galleryIds){
		$result = array();
		foreach($galleryIds as $galleryId){
			list($eligible) = cg_network_get_gallery_export_context(intval($galleryId));
			if($eligible){
				$result[] = intval($galleryId);
			}
		}
		return $result;
	}
}

if(!function_exists('cg_network_get_rest_verify_url')){
	function cg_network_get_rest_verify_url(){
		if(function_exists('rest_url')){
			return rest_url('contest-gallery-network/v1/verify');
		}
		return add_query_arg('rest_route','/contest-gallery-network/v1/verify',home_url('/'));
	}
}

if(!function_exists('cg_network_get_query_verify_url')){
	function cg_network_get_query_verify_url(){
		return add_query_arg('cg_network_verify','1',home_url('/'));
	}
}

if(!function_exists('cg_network_get_site_logo_url')){
	function cg_network_get_site_logo_url(){
		if(!function_exists('get_site_icon_url')){
			return '';
		}
		$logoUrl = get_site_icon_url(192);
		return !empty($logoUrl) ? esc_url_raw($logoUrl) : '';
	}
}

if(!function_exists('cg_network_build_payload')){
	function cg_network_build_payload($galleryIds,$mode,$overrides = array()){
		$keypair = cg_network_get_keypair();
		if(!$keypair){
			return array(false,'OpenSSL keypair could not be created.',array());
		}
		$galleries = array();
		foreach($galleryIds as $galleryId){
			$galleryOverride = (!empty($overrides[$galleryId]) && is_array($overrides[$galleryId])) ? $overrides[$galleryId] : array();
			$gallery = cg_network_build_gallery_payload($galleryId,$galleryOverride);
			if($gallery){
				$galleries[] = $gallery;
			}
		}
		if(!count($galleries)){
			return array(false,'No galleries could be exported.',array());
		}
		$siteUrl = home_url('/');
		$payload = array(
			'schema_version' => 1,
			'action' => 'publish',
			'export_mode' => $mode,
			'site' => array(
				'title' => cg_network_trim_text(get_bloginfo('name'),160),
				'description' => cg_network_trim_text(get_bloginfo('description'),360),
				'url' => $siteUrl,
				'logo_url' => cg_network_get_site_logo_url(),
				'plugin' => array(
					'name' => cg_network_get_plugin_name(),
					'version' => cg_get_version(),
				),
			),
			'galleries' => $galleries,
			'verification' => array(
				'url' => cg_network_get_rest_verify_url(),
				'fallback_url' => cg_network_get_query_verify_url(),
				'public_key' => $keypair['public_key'],
			),
		);
		$payload['payload_hash'] = cg_network_payload_hash($payload);
		return array(true,'',$payload);
	}
}

if(!function_exists('cg_network_build_unpublish_payload')){
	function cg_network_build_unpublish_payload($galleryId){
		$keypair = cg_network_get_keypair();
		if(!$keypair){
			return array(false,'OpenSSL keypair could not be created.',array());
		}
		$galleryId = intval($galleryId);
		if(!$galleryId){
			return array(false,'Gallery ID missing.',array());
		}
		$siteUrl = home_url('/');
		$payload = array(
			'schema_version' => 1,
			'action' => 'unpublish',
			'export_mode' => 'single',
			'site' => array(
				'title' => cg_network_trim_text(get_bloginfo('name'),160),
				'description' => cg_network_trim_text(get_bloginfo('description'),360),
				'url' => $siteUrl,
				'logo_url' => cg_network_get_site_logo_url(),
				'plugin' => array(
					'name' => cg_network_get_plugin_name(),
					'version' => cg_get_version(),
				),
			),
			'galleries' => array(),
			'gallery_export_ids' => array('gallery-'.$galleryId),
			'verification' => array(
				'url' => cg_network_get_rest_verify_url(),
				'fallback_url' => cg_network_get_query_verify_url(),
				'public_key' => $keypair['public_key'],
			),
		);
		$payload['payload_hash'] = cg_network_payload_hash($payload);
		return array(true,'',$payload);
	}
}

if(!function_exists('cg_network_create_verify_response')){
	function cg_network_create_verify_response($challenge,$payloadHash){
		$challenge = sanitize_text_field($challenge);
		$payloadHash = sanitize_text_field($payloadHash);
		if($challenge==='' || $payloadHash==='' || !preg_match('/^[a-f0-9]{64}$/',$payloadHash)){
			return array(400,array('error'=>'invalid_request'));
		}
		$keypair = cg_network_get_keypair();
		if(!$keypair || !function_exists('openssl_sign')){
			return array(500,array('error'=>'signing_unavailable'));
		}
		$siteUrl = home_url('/');
		$data = $challenge."\n".$payloadHash."\n".cg_network_normalize_site_url_for_signature($siteUrl);
		$signature = '';
		if(!openssl_sign($data,$signature,$keypair['private_key'],OPENSSL_ALGO_SHA256)){
			return array(500,array('error'=>'signing_failed'));
		}
		return array(200,array(
			'challenge' => $challenge,
			'payload_hash' => $payloadHash,
			'site_url' => $siteUrl,
			'signature' => base64_encode($signature),
		));
	}
}

if(!function_exists('cg_network_rest_verify_endpoint')){
	function cg_network_rest_verify_endpoint($request){
		$challenge = method_exists($request,'get_param') ? $request->get_param('challenge') : '';
		$payloadHash = method_exists($request,'get_param') ? $request->get_param('payload_hash') : '';
		list($status,$body) = cg_network_create_verify_response($challenge,$payloadHash);
		return new WP_REST_Response($body,$status);
	}
}

if(!function_exists('cg_network_register_rest_routes')){
	function cg_network_register_rest_routes(){
		register_rest_route('contest-gallery-network/v1','/verify',array(
			'methods' => 'GET',
			'callback' => 'cg_network_rest_verify_endpoint',
			'permission_callback' => '__return_true',
		));
	}
}
add_action('rest_api_init','cg_network_register_rest_routes');

if(!function_exists('cg_network_verify_endpoint')){
	function cg_network_verify_endpoint(){
		if(empty($_GET['cg_network_verify'])){
			return;
		}
		header('Content-Type: application/json; charset=utf-8');
		$challenge = (!empty($_GET['challenge'])) ? sanitize_text_field($_GET['challenge']) : '';
		$payloadHash = (!empty($_GET['payload_hash'])) ? sanitize_text_field($_GET['payload_hash']) : '';
		list($status,$body) = cg_network_create_verify_response($challenge,$payloadHash);
		status_header($status);
		echo wp_json_encode($body);
		exit;
	}
}
add_action('init','cg_network_verify_endpoint',1);

if(!function_exists('cg_network_set_notice')){
	function cg_network_set_notice($type,$message){
		set_transient('cg_network_export_notice_'.get_current_user_id(),array(
			'type' => $type,
			'message' => $message,
		),120);
	}
}

if(!function_exists('cg_network_render_admin_notice')){
	function cg_network_render_admin_notice(){
		$notice = get_transient('cg_network_export_notice_'.get_current_user_id());
		if(empty($notice) || !is_array($notice)){
			return;
		}
		delete_transient('cg_network_export_notice_'.get_current_user_id());
		$color = ($notice['type']==='success') ? '#0b7a32' : '#b42318';
		echo '<div class="cg_do_not_remove_when_ajax_load" style="max-width:940px;margin:12px auto;padding:12px 16px;border-left:5px solid '.$color.';background:#fff;box-shadow:0 2px 10px rgba(0,0,0,.08);font-weight:bold;">'.esc_html($notice['message']).'</div>';
	}
}

if(!function_exists('cg_network_submit_payload')){
	function cg_network_submit_payload($payload,$timeout = 60){
		$endpointUrl = cg_network_get_submit_url();
		$response = wp_remote_post($endpointUrl,array(
			'timeout' => $timeout,
			'headers' => array('Content-Type'=>'application/json'),
			'body' => wp_json_encode($payload),
		));
		if(is_wp_error($response)){
			return array(false,'Network export failed: '.$response->get_error_message(),array());
		}
		$code = wp_remote_retrieve_response_code($response);
		$body = json_decode(wp_remote_retrieve_body($response),true);
		if($code>=200 && $code<300 && !empty($body['published'])){
			return array(true,'Contest Gallery Network export published '.intval($body['count']).' gallery export(s).',$body);
		}
		$message = 'Contest Gallery Network export was not published.';
		$message = cg_network_append_endpoint_error_message($message,is_array($body) ? $body : array(),$code,$endpointUrl);
		return array(false,$message,is_array($body) ? $body : array());
	}
}

if(!function_exists('cg_network_unpublish_payload')){
	function cg_network_unpublish_payload($payload,$timeout = 60){
		$endpointUrl = cg_network_get_unpublish_url();
		$response = wp_remote_post($endpointUrl,array(
			'timeout' => $timeout,
			'headers' => array('Content-Type'=>'application/json'),
			'body' => wp_json_encode($payload),
		));
		if(is_wp_error($response)){
			return array(false,'Network unpublish failed: '.$response->get_error_message(),array());
		}
		$code = wp_remote_retrieve_response_code($response);
		$body = json_decode(wp_remote_retrieve_body($response),true);
		if($code>=200 && $code<300 && !empty($body['unpublished'])){
			return array(true,'Contest Gallery Network listing unpublished.',$body);
		}
		$message = 'Contest Gallery Network listing was not unpublished.';
		$message = cg_network_append_endpoint_error_message($message,is_array($body) ? $body : array(),$code,$endpointUrl);
		return array(false,$message,is_array($body) ? $body : array());
	}
}

if(!function_exists('cg_network_auto_update_gallery')){
	function cg_network_auto_update_gallery($galleryId,$state = null){
		$galleryId = intval($galleryId);
		if(!$galleryId){
			return array(false,'Invalid gallery ID.',array());
		}
		if($state===null){
			$states = cg_network_get_publish_states();
			$key = (string)$galleryId;
			$state = (!empty($states[$key]) && is_array($states[$key])) ? $states[$key] : array();
		}
		if(empty($state) || !is_array($state) || empty($state['published']) || empty($state['auto_update_enabled'])){
			return array(false,'This gallery is not published with daily Network auto update.',cg_network_get_publish_state($galleryId));
		}
		list($eligible,$eligibilityError) = cg_network_get_gallery_export_context($galleryId);
		if(!$eligible){
			cg_network_set_publish_state_error($galleryId,$eligibilityError);
			return array(false,$eligibilityError,cg_network_get_publish_state($galleryId));
		}
		$settings = (!empty($state['export_settings']) && is_array($state['export_settings'])) ? $state['export_settings'] : array();
		$overrides = array(
			$galleryId => cg_network_export_settings_to_overrides($settings),
		);
		list($ok,$error,$payload) = cg_network_build_payload(array($galleryId),'single',$overrides);
		if(!$ok){
			cg_network_set_publish_state_error($galleryId,$error);
			return array(false,$error,cg_network_get_publish_state($galleryId));
		}
		list($published,$message,$body) = cg_network_submit_payload($payload,60);
		if($published){
			$publishState = cg_network_set_publish_state($galleryId,$body,$settings,true);
			return array(true,$message,$publishState);
		}
		cg_network_set_publish_state_error($galleryId,$message);
		return array(false,$message,cg_network_get_publish_state($galleryId));
	}
}

if(!function_exists('cg_network_auto_update_event')){
	function cg_network_auto_update_event(){
		$states = cg_network_get_publish_states();
		if(!is_array($states) || !count($states)){
			cg_network_unschedule_auto_update();
			return;
		}
		foreach($states as $galleryId => $state){
			$galleryId = intval($galleryId);
			if(!$galleryId || empty($state['published']) || empty($state['auto_update_enabled'])){
				continue;
			}
			cg_network_auto_update_gallery($galleryId,$state);
		}
		cg_network_ensure_auto_update_schedule();
	}
}
add_action('cg_network_auto_update_event','cg_network_auto_update_event');
add_action('init','cg_network_ensure_auto_update_schedule',20);

if(!function_exists('cg_network_add_privacy_policy_content')){
	function cg_network_add_privacy_policy_content(){
		if(!function_exists('wp_add_privacy_policy_content')){
			return;
		}
		$content = '<p>When an administrator explicitly publishes a gallery to Contest Gallery Network, public website and gallery metadata is sent to www.contest-gallery.net so the contest can be listed publicly. Submitted data includes website title and URL, gallery title, description, tags, contest URL, preview image URLs and public activity numbers such as entries, votes and comments. No IP addresses, email addresses, usernames, registration data or comment text are submitted.</p>';
		$content .= '<p>Published listings are refreshed once daily by WordPress Cron while the plugin remains active. If www.contest-gallery.net does not receive an update for 14 days, the listing is removed from the public Network index. Administrators can unpublish a listing from the Contest Gallery Network publish dialog.</p>';
		wp_add_privacy_policy_content('Contest Gallery Network',$content);
	}
}
add_action('admin_init','cg_network_add_privacy_policy_content');

if(!function_exists('cg_network_export_admin_post')){
	function cg_network_export_admin_post(){
		if(!current_user_can('manage_options')){
			wp_die('Contest Gallery Network export requires manage_options.');
		}
		check_admin_referer('cg_network_export');
		$mode = (!empty($_POST['cg_network_export_mode']) && $_POST['cg_network_export_mode']==='all') ? 'all' : 'single';
		if($mode==='all'){
			cg_network_set_notice('error','Bulk publishing is not available in this version. Publish galleries individually.');
			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page='.cg_get_version().'/index.php'));
			exit;
		}
		$galleryId = (!empty($_POST['cg_network_gallery_id'])) ? absint($_POST['cg_network_gallery_id']) : 0;
		list($eligible,$eligibilityError) = cg_network_get_gallery_export_context($galleryId);
		if(!$eligible){
			cg_network_set_notice('error',$eligibilityError);
			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page='.cg_get_version().'/index.php'));
			exit;
		}
		$galleryIds = array($galleryId);
		if(!count($galleryIds)){
			cg_network_set_notice('error','No galleries with the required /contest-galleries/ URL structure could be exported.');
			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page='.cg_get_version().'/index.php'));
			exit;
		}
		list($ok,$error,$payload) = cg_network_build_payload($galleryIds,$mode);
		if(!$ok){
			cg_network_set_notice('error',$error);
			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page='.cg_get_version().'/index.php'));
			exit;
		}
		list($published,$message,$body) = cg_network_submit_payload($payload,60);
		if($published){
			cg_network_set_publish_state($galleryId,$body,array(),false);
		}
		cg_network_set_notice($published ? 'success' : 'error',$message);
		wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url('admin.php?page='.cg_get_version().'/index.php'));
		exit;
	}
}
add_action('admin_post_cg_network_export','cg_network_export_admin_post');

if(!function_exists('cg_network_export_ajax')){
	function cg_network_export_ajax(){
		if(!current_user_can('manage_options')){
			wp_send_json_error(array('message'=>'Contest Gallery Network export requires manage_options.'),403);
		}
		$cgNonce = (!empty($_POST['cg_nonce'])) ? sanitize_text_field($_POST['cg_nonce']) : '';
		if(empty($cgNonce) || !wp_verify_nonce($cgNonce,'cg_nonce')){
			wp_send_json_error(array(
				'code' => 'cg_nonce_invalid',
				'version' => cg_get_version(),
				'message' => 'WP nonce security token not set or not valid anymore.',
			),403);
		}
		if(empty($_POST['cg_network_privacy_confirmed']) || $_POST['cg_network_privacy_confirmed']!=='1'){
			wp_send_json_error(array('message'=>'Please confirm that you accept the Contest Gallery Network privacy policy.'),400);
		}
		$mode = (!empty($_POST['cg_network_export_mode']) && $_POST['cg_network_export_mode']==='all') ? 'all' : 'single';
		$galleryIds = array();
		$overrides = array();
		if($mode==='all'){
			wp_send_json_error(array('message'=>'Bulk publishing is not available in this version. Publish galleries individually.'),400);
		}
		$galleryId = (!empty($_POST['cg_network_gallery_id'])) ? absint($_POST['cg_network_gallery_id']) : 0;
		list($eligible,$eligibilityError,$context) = cg_network_get_gallery_export_context($galleryId);
		if(!$eligible){
			wp_send_json_error(array('message'=>$eligibilityError),400);
		}
		$linkMode = (!empty($_POST['cg_network_link_mode']) && $_POST['cg_network_link_mode']==='custom') ? 'custom' : 'gallery';
		$customContestUrl = '';
		if($linkMode==='custom'){
			$customUrlInput = (!empty($_POST['cg_network_custom_url']) && !is_array($_POST['cg_network_custom_url'])) ? wp_unslash($_POST['cg_network_custom_url']) : '';
			$customContestUrl = esc_url_raw(trim($customUrlInput));
			if(!cg_network_custom_contest_url_is_allowed($customContestUrl)){
				wp_send_json_error(array('message'=>'Enter a valid public URL on the same domain as this website.'),400);
			}
		}
		$titleInput = (!empty($_POST['cg_network_title']) && !is_array($_POST['cg_network_title'])) ? wp_unslash($_POST['cg_network_title']) : '';
		$descriptionInput = (!empty($_POST['cg_network_description']) && !is_array($_POST['cg_network_description'])) ? wp_unslash($_POST['cg_network_description']) : '';
		$title = ($titleInput!=='') ? cg_network_trim_text($titleInput,180) : $context['title'];
		$description = ($descriptionInput!=='') ? cg_network_trim_text($descriptionInput,360) : $context['description'];
		$galleryIds = array($galleryId);
		$exportSettings = array(
			'title' => $title,
			'description' => $description,
			'link_mode' => $linkMode,
			'custom_url' => $customContestUrl,
		);
		$overrides[$galleryId] = array(
			'title' => $title,
			'description' => $description,
			'page_type' => 'gallery',
			'contest_url' => $customContestUrl,
			'source_mode' => ($linkMode==='custom') ? 'custom_network_url' : 'custom_network_modal',
		);
		list($ok,$error,$payload) = cg_network_build_payload($galleryIds,$mode,$overrides);
		if(!$ok){
			wp_send_json_error(array('message'=>$error),400);
		}
		list($published,$message,$body) = cg_network_submit_payload($payload,90);
		if(!$published){
			wp_send_json_error(array('message'=>$message),422);
		}
		$publishState = cg_network_set_publish_state($galleryId,$body,$exportSettings,false);
		wp_send_json_success(array(
				'message' => $message,
				'count' => (!empty($body['count'])) ? intval($body['count']) : count($galleryIds),
				'site_detail_url' => (!empty($body['site_detail_url']) && !is_array($body['site_detail_url'])) ? esc_url_raw($body['site_detail_url']) : '',
				'gallery_detail_url' => (!empty($publishState['gallery_detail_url'])) ? esc_url_raw($publishState['gallery_detail_url']) : '',
				'site_slug' => (!empty($body['site_slug']) && !is_array($body['site_slug'])) ? sanitize_text_field($body['site_slug']) : '',
				'accepted_galleries' => (!empty($body['accepted_galleries']) && is_array($body['accepted_galleries'])) ? $body['accepted_galleries'] : array(),
				'publish_state' => $publishState,
		));
	}
}
add_action('wp_ajax_post_cg_network_export','cg_network_export_ajax');

if(!function_exists('cg_network_unpublish_ajax')){
	function cg_network_unpublish_ajax(){
		if(!current_user_can('manage_options')){
			wp_send_json_error(array('message'=>'Contest Gallery Network unpublish requires manage_options.'),403);
		}
		$cgNonce = (!empty($_POST['cg_nonce'])) ? sanitize_text_field($_POST['cg_nonce']) : '';
		if(empty($cgNonce) || !wp_verify_nonce($cgNonce,'cg_nonce')){
			wp_send_json_error(array(
				'code' => 'cg_nonce_invalid',
				'version' => cg_get_version(),
				'message' => 'WP nonce security token not set or not valid anymore.',
			),403);
		}
		$galleryId = (!empty($_POST['cg_network_gallery_id'])) ? absint($_POST['cg_network_gallery_id']) : 0;
		list($ok,$error,$payload) = cg_network_build_unpublish_payload($galleryId);
		if(!$ok){
			wp_send_json_error(array('message'=>$error),400);
		}
		list($unpublished,$message,$body) = cg_network_unpublish_payload($payload,60);
		if(!$unpublished){
			wp_send_json_error(array('message'=>$message),422);
		}
		$publishState = cg_network_delete_publish_state($galleryId);
		wp_send_json_success(array(
			'message' => $message,
			'removed_count' => (!empty($body['removed_count'])) ? intval($body['removed_count']) : 0,
			'publish_state' => $publishState,
		));
	}
}
add_action('wp_ajax_post_cg_network_unpublish','cg_network_unpublish_ajax');

if(!function_exists('cg_network_auto_update_now_ajax')){
	function cg_network_auto_update_now_ajax(){
		if(!current_user_can('manage_options')){
			wp_send_json_error(array('message'=>'Contest Gallery Network auto update requires manage_options.'),403);
		}
		$cgNonce = (!empty($_POST['cg_nonce'])) ? sanitize_text_field($_POST['cg_nonce']) : '';
		if(empty($cgNonce) || !wp_verify_nonce($cgNonce,'cg_nonce')){
			wp_send_json_error(array(
				'code' => 'cg_nonce_invalid',
				'version' => cg_get_version(),
				'message' => 'WP nonce security token not set or not valid anymore.',
			),403);
		}
		$galleryId = (!empty($_POST['cg_network_gallery_id'])) ? absint($_POST['cg_network_gallery_id']) : 0;
		list($updated,$message,$publishState) = cg_network_auto_update_gallery($galleryId);
		if(!$updated){
			wp_send_json_error(array(
				'message' => $message,
				'publish_state' => $publishState,
			),422);
		}
		wp_send_json_success(array(
			'message' => 'Contest Gallery Network auto update completed.',
			'publish_state' => $publishState,
			'site_detail_url' => (!empty($publishState['site_detail_url'])) ? esc_url_raw($publishState['site_detail_url']) : '',
			'gallery_detail_url' => (!empty($publishState['gallery_detail_url'])) ? esc_url_raw($publishState['gallery_detail_url']) : '',
		));
	}
}
add_action('wp_ajax_post_cg_network_auto_update_now','cg_network_auto_update_now_ajax');

if(!function_exists('cg_network_publish_state_html')){
	function cg_network_publish_state_html($galleryId,$state,$context = ''){
		$galleryId = intval($galleryId);
		$isPublished = !empty($state['published']);
		$contextId = preg_replace('/[^A-Za-z0-9]/','',(string)$context);
		$contextId = ($contextId!=='') ? ucfirst(strtolower($contextId)) : '';
		$stateId = 'cgNetworkPublishState'.$galleryId.$contextId;
		$class = 'cg_network_publish_state'.($isPublished ? '' : ' cg_hide');
		$html = '<div id="'.$stateId.'" class="'.$class.'" data-cg-gallery-id="'.$galleryId.'" data-cg-network-context="'.esc_attr($context).'">';
		if($isPublished){
			$html .= '<span>Published since '.esc_html($state['published_at_text']).'</span>';
			if(!empty($state['last_network_update_at_text'])){
				$networkUpdateText = 'Last network update '.$state['last_network_update_at_text'];
				if(!empty($state['next_auto_update_at_text'])){
					$networkUpdateText .= ' - Next WP-Cron '.$state['next_auto_update_at_text'];
				}
				$html .= '<span class="cg_network_auto_update_state">'.esc_html($networkUpdateText).'</span>';
			}elseif(!empty($state['next_auto_update_at_text'])){
				$html .= '<span class="cg_network_auto_update_state">Last network update pending - Next WP-Cron '.esc_html($state['next_auto_update_at_text']).'</span>';
			}else{
				$html .= '<span class="cg_network_auto_update_state">Last network update pending</span>';
			}
			if(!empty($state['last_auto_update_error'])){
				$html .= '<span class="cg_network_auto_update_error">Auto update error: '.esc_html($state['last_auto_update_error']).'</span>';
			}
			$listingUrl = !empty($state['gallery_detail_url']) ? $state['gallery_detail_url'] : (!empty($state['site_detail_url']) ? $state['site_detail_url'] : '');
			if(!empty($listingUrl)){
				$html .= '<a href="'.esc_url($listingUrl).'" target="_blank" rel="noopener noreferrer">View listing</a>';
			}
		}
		$html .= '</div>';
		return $html;
	}
}

if(!function_exists('cg_network_export_button_html')){
	function cg_network_export_button_html($mode,$galleryId,$label,$buttonContext = ''){
		if($mode!=='single' || !$galleryId){
			return '';
		}
		$mode = 'single';
		$data = array(
			'mode' => $mode,
			'gallery_id' => intval($galleryId),
			'title' => '',
			'description' => '',
			'eligible' => 1,
			'eligibility_message' => '',
			'pages' => array(),
			'publish_state' => cg_network_get_publish_state($galleryId),
		);
		if($mode==='single' && $galleryId){
			list($eligible,$message,$galleryContext) = cg_network_get_gallery_export_context($galleryId);
			$data['eligible'] = $eligible ? 1 : 0;
			$data['eligibility_message'] = $message;
			if(!empty($galleryContext)){
				$data['title'] = (!empty($galleryContext['title'])) ? $galleryContext['title'] : '';
				$data['description'] = (!empty($galleryContext['description'])) ? $galleryContext['description'] : '';
				$data['pages'] = (!empty($galleryContext['pages']) && is_array($galleryContext['pages'])) ? $galleryContext['pages'] : array();
			}
		}
		$buttonLabel = (!empty($data['publish_state']['published'])) ? 'Update network listing' : $label;
		$html = '<input class="cg_backend_button cg_network_export_button" type="button" value="'.esc_attr($buttonLabel).'" data-cg-gallery-id="'.intval($galleryId).'"';
		$html .= ' data-cg-network-context="'.esc_attr($buttonContext).'"';
		$html .= ' data-cg-network="'.esc_attr(wp_json_encode($data)).'">';
		$html .= cg_network_publish_state_html($galleryId,$data['publish_state'],$buttonContext);
		return $html;
	}
}

if(!function_exists('cg_network_export_action_block_html')){
	function cg_network_export_action_block_html($galleryId,$context = 'main-menu'){
		$galleryId = intval($galleryId);
		if(!$galleryId){
			return '';
		}
		if($context==='edit-gallery'){
			list($eligible,$message) = cg_network_get_gallery_export_context($galleryId);
			$html = '<div class="cg_gallery_backend_meta_network_column">';
			$html .= '<div class="cg_gallery_backend_network_area">';
			$html .= '<div class="cg_gallery_backend_upload_intro cg_gallery_backend_network_intro">';
			$html .= '<div class="cg_gallery_backend_upload_intro_title">Publish to Contest Gallery Network</div>';
			$html .= '<div class="cg_gallery_backend_upload_intro_text">Make this gallery discoverable on www.contest-gallery.net with preview images, activity signals and a verified backlink to your public gallery page.</div>';
			$html .= '</div>';
			$html .= '<div class="cg_gallery_backend_network_action">'.cg_network_export_button_html('single',$galleryId,'Publish to network','edit-gallery').'</div>';
			if(!$eligible && !empty($message)){
				$html .= '<div class="cg_gallery_backend_network_ineligible_note">'.esc_html($message).'</div>';
			}
			$html .= '</div>';
			$html .= '</div>';
			return $html;
		}
		return '<div class="cg_button_edit cg_button_network_export">'.cg_network_export_button_html('single',$galleryId,'Publish to network').'</div>';
	}
}
