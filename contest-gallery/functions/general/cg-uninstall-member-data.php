<?php
if(!defined('ABSPATH') || !defined('WP_UNINSTALL_PLUGIN')){exit;}

// Standalone uninstall helpers. Do not load the frontend or register its hooks.
function cg_uninstall_member_workers_inactive(){
	global $wpdb;
	$locks = array('cg_profile_'.md5($wpdb->users));
	$locks[] = 'cg-pmail-'.md5(DB_NAME.'|'.$wpdb->prefix);
	foreach(array(array('upload-prepare','site'),array('community','write')) as $scope){
		$locks[] = 'cg-sec-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.$scope[0].'|'.$scope[1]);
	}
	$jobs = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('_cg_upload_job_').'%'));
	if($wpdb->last_error){return false;}
	foreach($jobs as $key){
		if(preg_match('/^_cg_upload_job_([a-f0-9]{64})$/D',$key,$match)){
			$locks[] = 'cg-upload-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.$match[1]);
		}
	}
	// Classic replacements also hold a member lock without a durable upload job.
	$members = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key=%s",'_cg_member_replace_attempts_'.$wpdb->prefix));
	if($wpdb->last_error){return false;}
	foreach($members as $userId){$locks[] = 'cg-replace-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.absint($userId));}
	foreach($locks as $lock){
		$owner = $wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',$lock));
		if($owner!==null || $wpdb->last_error){return false;}
	}
	return true;
}

function cg_uninstall_member_site_data(){
	global $wpdb;
	foreach(array('cg_frontend_security_cleanup','cg_upload_jobs_cleanup','cg_gallery_transfer_cleanup_stale_jobs_event') as $hook){wp_clear_scheduled_hook($hook);}
	delete_option('cg_captcha_legacy_v1');
	delete_option('cg_registration_agreements');
	delete_option('cg_member_sign_in_text');
	wp_clear_scheduled_hook('cg_participant_mail_worker');wp_clear_scheduled_hook('cg_participant_mail_cleanup');
	wp_unschedule_hook('cg_participant_mail_erase_user');
	$participantMailKeys = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('cg_pmail_').'%'));
	foreach($participantMailKeys as $key){
		if(preg_match('/^cg_pmail_(?:index|rate|composer_[1-9][0-9]*_[1-9][0-9]*|history_(?:legacy_id|[1-9][0-9]*)|(?:job|stop)_[a-f0-9]{32}|chunk_[a-f0-9]{32}_[0-9]+)$/D',$key)){delete_option($key);}
	}
	delete_metadata('user',0,$wpdb->get_blog_prefix().'cg_registration_agreements','',true);
	foreach(array('_cg_member_replace_attempts_'.$wpdb->prefix,$wpdb->get_blog_prefix().'cg_member_admin_mail_note_dismissed',$wpdb->get_blog_prefix().'cg_member_communication_disabled') as $key){
		delete_metadata('user',0,$key,'',true);
	}
	foreach(array('_cg_member_replacement_owner','_cg_member_replacement_bytes','_cg_upload_job','_cg_upload_job_slot') as $key){
		delete_metadata('post',0,$key,'',true);
	}
	$keys = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like('_cg_security_v1_').'%',$wpdb->esc_like('_cg_upload_job_').'%',$wpdb->esc_like('_transient_cg1l_gravatar_exists_').'%',$wpdb->esc_like('_transient_timeout_cg1l_gravatar_exists_').'%',
		$wpdb->esc_like('cg_captcha_registry_').'%',$wpdb->esc_like('cg_captcha_upload_').'%'));
	foreach($keys as $key){
		if(preg_match('/^_cg_security_v1_[a-z0-9_-]+_[a-f0-9]{64}$/D',$key) || preg_match('/^_cg_upload_job_[a-f0-9]{64}$/D',$key) || preg_match('/^cg_captcha_(?:registry|upload)_[1-9][0-9]*$/D',$key)){
			delete_option($key);
		}elseif(preg_match('/^_transient_(?:timeout_)?(cg1l_gravatar_exists_[a-f0-9]{32})$/D',$key,$match)){
			delete_transient($match[1]);
		}
	}
}

function cg_uninstall_member_global_data(){
	foreach(array('cg_member_profile_image_override','cgGetLoggedInFrontendUserKey','cgResetPasswordKey','cgLostPasswordMailTimestamp','cgResetPasswordAccountState','cgGoogleSignInJustSignedIn') as $key){
		delete_metadata('user',0,$key,'',true);
	}
}

// A missing file can still have a stale attachment. Never follow a symlink or .. .
function cg_uninstall_member_safe_file($file,$base){
	if(!is_string($file) || strpos($file,"\0")!==false){return false;}
	$file = wp_normalize_path($file);
	$base = rtrim(wp_normalize_path($base),'/');
	if(strpos($file,$base.'/')!==0 || !realpath($base) || is_link($base)){return false;}
	$relative = substr($file,strlen($base)+1);
	$path = $base;
	foreach(explode('/',$relative) as $part){
		if($part==='' || $part==='.' || $part==='..'){return false;}
		$path .= '/'.$part;
		if(is_link($path)){return false;}
		$real = realpath($path);
		if($real!==false && strpos(wp_normalize_path($real),rtrim(wp_normalize_path(realpath($base)),'/').'/')!==0){return false;}
	}
	return !file_exists($file) || is_file($file);
}

// Return only files the WordPress deletion API could remove, and their URLs.
function cg_uninstall_member_image_files($id){
	global $wpdb;
	$post = get_post($id);
	if(!$post || $post->post_type!=='attachment' || $post->post_content!=='Contest Gallery user profile image' || $post->post_parent || strpos($post->post_mime_type,'image/')!==0){return false;}
	$upload = wp_upload_dir(null,false);
	if(!empty($upload['error'])){return false;}
	$file = get_attached_file($id,true);
	if(!$file || get_attached_file($id)!==$file){return false;}
	$meta = wp_get_attachment_metadata($id);
	$backups = get_post_meta($id,'_wp_attachment_backup_sizes',true);
	if($wpdb->last_error || ($meta && !is_array($meta)) || ($backups && !is_array($backups))){return false;}
	$files = array($file);
	if(!empty($meta['file'])){
		if(!is_string($meta['file'])){return false;}
		$metaFile = $upload['basedir'].'/'.$meta['file'];
		if(wp_normalize_path($metaFile)!==wp_normalize_path($file)){return false;}
		$files[] = $metaFile;
	}
	foreach(array('thumb','original_image','source_image','animated_video','animated_video_poster') as $key){
		if(!empty($meta[$key])){
			if(!is_string($meta[$key])){return false;}
			$files[] = dirname($file).'/'.$meta[$key];
		}
	}
	foreach(array(isset($meta['sizes']) ? $meta['sizes'] : array(),$backups ? $backups : array()) as $sizes){
		if(!is_array($sizes)){return false;}
		foreach($sizes as $size){
			if(!is_array($size) || empty($size['file']) || !is_string($size['file'])){return false;}
			$files[] = dirname($file).'/'.$size['file'];
		}
	}
	$needles = array();
	foreach(array_unique($files) as $path){
		if(!cg_uninstall_member_safe_file($path,$upload['basedir'])){return false;}
		$relative = substr(wp_normalize_path($path),strlen(rtrim(wp_normalize_path($upload['basedir']),'/'))+1);
		// Match other hostnames/schemes and JSON-escaped or URL-encoded references too.
		// Some attachment/plugin metadata stores only a shared thumbnail filename.
		foreach(array('/'.$relative,'/'.implode('/',array_map('rawurlencode',explode('/',$relative))),basename($path),rawurlencode(basename($path))) as $needle){
			$needles[$needle] = true;
			$needles[str_replace('/','\\/',$needle)] = true;
			$needles[htmlspecialchars($needle,ENT_QUOTES,'UTF-8',true)] = true;
		}
	}
	$url = wp_get_attachment_url($id);
	$expected = rtrim($upload['baseurl'],'/').'/'.substr(wp_normalize_path($file),strlen(rtrim(wp_normalize_path($upload['basedir']),'/'))+1);
	if(!$url || preg_replace('~^https?:~','',$url)!==preg_replace('~^https?:~','',$expected)){return false;}
	return array_keys($needles);
}

function cg_uninstall_member_reference_condition($column,$id,$needles){
	global $wpdb;
	$parts = array();
	// Conservative token matching also covers serialized IDs and JSON arrays.
	if($id){
		$pattern = '(^|[^0-9])'.absint($id).'([^0-9]|$)';
		$serverInfo = method_exists($wpdb,'db_server_info') ? $wpdb->db_server_info() : '';
		$version = method_exists($wpdb,'db_version') ? $wpdb->db_version() : '';
		// Avoid MySQL 8 ICU timeouts on large values without changing token boundaries.
		if(is_string($serverInfo) && $serverInfo!=='' && stripos($serverInfo,'MariaDB')===false && is_string($version) && version_compare($version,'8.0','>=')){
			$pattern = '(?<![0-9])'.absint($id).'(?![0-9])';
		}
		$parts[] = $column.$wpdb->prepare(' REGEXP %s',$pattern);
	}
	foreach($needles as $needle){$parts[] = $column.$wpdb->prepare(' LIKE %s','%'.$wpdb->esc_like($needle).'%');}
	return '('.implode(' OR ',$parts).')';
}

function cg_uninstall_member_has_row($query){
	global $wpdb;
	$value = $wpdb->get_var($query);
	// SQL/legacy-schema failures must preserve the image.
	return $value!==null || !empty($wpdb->last_error);
}

function cg_uninstall_member_reference_path($file,$base){
	if(!is_string($file) || $file===''){return false;}
	$file = wp_normalize_path($file);
	if(substr($file,0,1)!=='/' && !preg_match('~^[a-zA-Z]:/~',$file)){$file=$base.'/'.$file;}
	if(!cg_uninstall_member_safe_file($file,$base)){return false;}
	return substr($file,strlen($base));
}

function cg_uninstall_member_strip_dimensions(&$data){
	foreach(array('width','height','filesize') as $key){
		if(array_key_exists($key,$data) && !is_numeric($data[$key])){return false;}
		unset($data[$key]);
	}
	return true;
}

// Refine only known attachment metadata. Other matching fields remain protective.
function cg_uninstall_member_attachment_reference($row,$id,$needles,$prefix,$base){
	global $wpdb;
	if($row->post_type!=='attachment' || !in_array($row->meta_key,array('_wp_attached_file','_wp_attachment_metadata','_wp_attachment_backup_sizes'),true)){return true;}
	if($row->meta_key==='_wp_attached_file'){
		$path=cg_uninstall_member_reference_path($row->meta_value,$base);
		if($path===false){return true;}
		foreach($needles as $needle){if(strcasecmp($path,$needle)===0){return true;}}
		return false;
	}
	$raw=$row->meta_value;
	// PHP 5.6 has no allowed_classes option. Reject objects/references before decoding.
	if(!is_string($raw) || !preg_match('/^a:[0-9]+:\{/',$raw) || preg_match('/(?:[OC]:[0-9]+:|[Rr]:[0-9]+;)/',$raw)){return true;}
	$data=@unserialize($raw);
	if(!is_array($data) || serialize($data)!==$raw){return true;}
	$attached=$wpdb->get_col($wpdb->prepare('SELECT meta_value FROM '.$prefix.'postmeta WHERE post_id=%d AND meta_key=%s LIMIT 2',$row->post_id,'_wp_attached_file'));
	if($wpdb->last_error || count($attached)!==1){return true;}
	$main=cg_uninstall_member_reference_path($attached[0],$base);
	if($main===false){return true;}
	$files=array($main);$remaining=$data;$directory=dirname($base.$main);
	if($row->meta_key==='_wp_attachment_metadata'){
		if(empty($data['file']) || cg_uninstall_member_reference_path($data['file'],$base)!==$main || !cg_uninstall_member_strip_dimensions($remaining)){return true;}
		unset($remaining['file']);
		foreach(array('thumb','original_image','source_image','animated_video','animated_video_poster') as $key){
			if(isset($data[$key])){
				if(!is_string($data[$key]) || $data[$key]===''){return true;}
				$path=cg_uninstall_member_reference_path($directory.'/'.$data[$key],$base);
				if($path===false){return true;}
				$files[]=$path;unset($remaining[$key]);
			}
		}
		$sizes=isset($data['sizes']) ? $data['sizes'] : array();
		if(!is_array($sizes)){return true;}
	}else{$sizes=$data;}
	foreach($sizes as $key=>$size){
		if(!is_array($size) || empty($size['file']) || !is_string($size['file']) || !cg_uninstall_member_strip_dimensions($size)){return true;}
		$path=cg_uninstall_member_reference_path($directory.'/'.$size['file'],$base);
		if($path===false){return true;}
		$files[]=$path;unset($size['file']);
		if($row->meta_key==='_wp_attachment_metadata'){$remaining['sizes'][$key]=$size;}
		else{$remaining[$key]=$size;}
	}
	foreach($files as $path){foreach($needles as $needle){if(strcasecmp($path,$needle)===0){return true;}}}
	// Keep the existing SQL collation and conservative matching for unknown fields.
	return cg_uninstall_member_has_row('SELECT 1 WHERE '.cg_uninstall_member_reference_condition($wpdb->prepare('%s',serialize($remaining)),$id,$needles));
}

function cg_uninstall_member_postmeta_referenced($id,$needles,$prefix){
	global $wpdb;
	$last=0;$upload=wp_upload_dir(null,false);
	if(!empty($upload['error']) || empty($upload['basedir'])){return true;}
	$base=rtrim(wp_normalize_path($upload['basedir']),'/');
	do{
		$query=$wpdb->prepare('SELECT m.meta_id,m.post_id,m.meta_key,m.meta_value,p.post_type FROM '.$prefix.'postmeta m LEFT JOIN '.$prefix.'posts p ON p.ID=m.post_id WHERE m.post_id<>%d AND m.meta_id>%d AND ',$id,$last);
		$rows=$wpdb->get_results($query.cg_uninstall_member_reference_condition('m.meta_value',$id,$needles).' ORDER BY m.meta_id ASC LIMIT 100');
		if($wpdb->last_error || !is_array($rows)){return true;}
		foreach($rows as $row){
			$next=(int)$row->meta_id;
			if($next<=$last){return true;}
			$last=$next;
			if(cg_uninstall_member_attachment_reference($row,$id,$needles,$prefix,$base)){return true;}
		}
	}while(count($rows)===100);
	return false;
}

function cg_uninstall_member_image_referenced($id,$needles,$blogIds){
	global $wpdb;
	$ownerBlog = get_current_blog_id();
	foreach($blogIds as $blogId){
		$prefix = $wpdb->get_blog_prefix($blogId);
		$localId = (int)$blogId===(int)$ownerBlog ? $id : 0;
		$exclude = $localId ? $wpdb->prepare('ID<>%d AND ',$id) : '';
		if(cg_uninstall_member_has_row('SELECT ID FROM '.$prefix.'posts WHERE '.$exclude.cg_uninstall_member_reference_condition('post_content',$localId,$needles).' LIMIT 1')){return true;}
		$exclude = $localId ? $wpdb->prepare('post_id<>%d AND ',$id) : '';
		if($localId){
			if(cg_uninstall_member_postmeta_referenced($id,$needles,$prefix)){return true;}
		}elseif(cg_uninstall_member_has_row('SELECT meta_id FROM '.$prefix.'postmeta WHERE '.$exclude.cg_uninstall_member_reference_condition('meta_value',$localId,$needles).' LIMIT 1')){return true;}
		if(cg_uninstall_member_has_row('SELECT option_id FROM '.$prefix.'options WHERE '.cg_uninstall_member_reference_condition('option_value',$localId,$needles).' LIMIT 1')){return true;}
		$table = $prefix.'contest_gal1ery';
		$exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like($table)));
		if($wpdb->last_error){return true;}
		if($exists===$table){
			$parts = array(cg_uninstall_member_reference_condition('MultipleFiles',$localId,$needles));
			if($localId){$parts[] = $wpdb->prepare('WpUpload=%d OR PdfPreview=%d',$id,$id);}
			if(cg_uninstall_member_has_row('SELECT id FROM '.$table.' WHERE COALESCE(IsProfileImage,0)<>1 AND ('.implode(' OR ',$parts).') LIMIT 1')){return true;}
		}
	}
	// Global avatar metadata has no reliable owning blog. Ambiguous IDs stay safe.
	if(cg_uninstall_member_has_row('SELECT umeta_id FROM '.$wpdb->usermeta.' WHERE '.cg_uninstall_member_reference_condition('meta_value',$id,$needles).' LIMIT 1')){return true;}
	if(is_multisite() && cg_uninstall_member_has_row('SELECT meta_id FROM '.$wpdb->sitemeta.' WHERE '.cg_uninstall_member_reference_condition('meta_value',0,$needles).' LIMIT 1')){return true;}
	return false;
}

// Snapshot protection across all sites before any gallery table disappears.
function cg_uninstall_member_image_inventory($blogIds){
	global $wpdb;
	$inventory = array();
	foreach($blogIds as $blogId){
		if(is_multisite()){switch_to_blog($blogId);}
		try{
			$last = 0;
			do{
				$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID>%d AND post_type='attachment' AND post_content=%s ORDER BY ID ASC LIMIT 100",$last,'Contest Gallery user profile image'));
				if($wpdb->last_error){break;}
				foreach($ids as $id){
					$last = absint($id);
					$needles = cg_uninstall_member_image_files($id);
					if($needles && !cg_uninstall_member_image_referenced($id,$needles,$blogIds)){$inventory[$blogId][] = absint($id);}
				}
			}while(count($ids)===100);
		}finally{if(is_multisite()){restore_current_blog();}}
	}
	return $inventory;
}

function cg_uninstall_member_delete_images($ids,$blogIds){
	foreach($ids as $id){
		$needles = cg_uninstall_member_image_files($id);
		if($needles && !cg_uninstall_member_image_referenced($id,$needles,$blogIds)){
			wp_delete_attachment($id,true);
		}
	}
}
