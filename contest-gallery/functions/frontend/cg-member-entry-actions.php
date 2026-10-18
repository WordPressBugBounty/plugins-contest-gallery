<?php
if(!defined('ABSPATH')){exit;}
include_once __DIR__.'/cg-member-replacement-upload.php';
include_once __DIR__.'/cg-member-replacement-limits.php';
include_once __DIR__.'/cg-member-replacement-recovery.php';

if(!function_exists('cg_member_entry_action_strings')){
function cg_member_entry_action_strings(){
	return array('Entry actions','Replace image','Delete entry','Choose the image to replace.','Replace','Replacing…','This entry will need approval again after replacement.','Image replaced.','Image replacement is not allowed.','Entries offered for sale cannot be changed here.','This entry already has votes. Its images cannot be replaced.','The time allowed for replacing this image has ended.','The upload period is closed.','This entry has changed. Reload it and try again.','The image does not meet the gallery upload requirements.','Delete this entry and all its files?');
}
}

if(!function_exists('cg_member_entry_action_context')){
function cg_member_entry_action_context(){
	global $wpdb;
	if(!is_user_logged_in() || $_SERVER['REQUEST_METHOD']!=='POST'){return new WP_Error('access','Please reload the page and try again.');}
	$gid = isset($_POST['gid']) ? absint($_POST['gid']) : 0;
	if(!cg_member_features_available($gid)){return new WP_Error('access','This gallery is not available.');}
	$pid = isset($_POST['pid']) ? absint($_POST['pid']) : 0;
	$instance = isset($_POST['galeryIDuser']) ? sanitize_text_field(wp_unslash($_POST['galeryIDuser'])) : '';
	$hash = isset($_POST['galleryHash']) ? sanitize_text_field(wp_unslash($_POST['galleryHash'])) : '';
	if(!$gid || !$pid || !$instance || !$hash || !hash_equals(cg_hash_function('---cngl1---'.$instance,$hash),$hash)){return new WP_Error('access','Please reload the page and try again.');}
	$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery WHERE id=%d AND GalleryID=%d AND WpUserId=%d',$pid,$gid,get_current_user_id()));
	if(!$row || (empty($row->Active) && !cg_member_enabled($gid))){return new WP_Error('access','This gallery is not available.');}
	return $row;
}
}

if(!function_exists('cg_member_entry_revision')){
function cg_member_entry_revision($row){
	return wp_hash(implode('|',array($row->id,$row->GalleryID,$row->WpUserId,$row->WpUpload,$row->MultipleFiles,$row->Active,$row->EcommerceEntry)),'nonce');
}
}

if(!function_exists('cg_member_entry_files')){
function cg_member_entry_files($row){
	$files = maybe_unserialize($row->MultipleFiles);
	if(!is_array($files) || !$files){$files = array(0=>array('WpUpload'=>$row->WpUpload,'isRealIdSource'=>true));}
	return $files;
}
}

if(!function_exists('cg_member_entry_back_url')){
function cg_member_entry_back_url($row){
	// Always compute the destination before deletion removes the entry pages.
	$parent = !empty($row->WpPageUser) ? wp_get_post_parent_id($row->WpPageUser) : 0;
	return $parent ? get_permalink($parent) : home_url('/');
}
}

if(!function_exists('cg_member_replacement_auto_activate')){
function cg_member_replacement_auto_activate($gid){
	global $wpdb;
	return (int)$wpdb->get_var($wpdb->prepare('SELECT ActivateUpload FROM '.$wpdb->prefix.'contest_gal1ery_options WHERE id=%d',$gid))===1;
}
}

if(!function_exists('cg_member_entry_action_bootstrap')){
function cg_member_entry_action_bootstrap($row){
	$gid = absint($row->GalleryID);
	$rules = cg_member_replacement_options($gid);
	$reason = cg_member_replacement_policy($row,$rules,cg_member_replacement_source($gid));
	$files = array();
	$uploadRules = cg_member_replacement_upload_rules($gid);
	$options = cg_member_options($gid);
	$uploadMaxBytes = array('image'=>cg_member_replacement_upload_limit($options,true),'file'=>cg_member_replacement_upload_limit($options,false));
	foreach(cg_member_entry_files($row) as $slot=>$file){
		$id = !empty($file['isRealIdSource']) ? absint($row->WpUpload) : absint($file['WpUpload']);
		$social = cg_member_replacement_social_source($id);
		if($social){
			$files[] = array_merge(array('slot'=>(string)$slot,'attachment'=>$id,'src'=>''),$social);
			continue;
		}
		$type = cg_member_replacement_attachment_type($id);
		if(!$type){continue;}
		$preview = !empty($file['isRealIdSource']) ? $row->PdfPreview : (isset($file['PdfPreview']) ? $file['PdfPreview'] : 0);
		$src = wp_get_attachment_image_src($preview ? $preview : $id,'medium');
		$files[] = array('slot'=>(string)$slot,'attachment'=>$id,'src'=>$src ? $src[0] : '', 'name'=>wp_basename(get_attached_file($id)), 'extension'=>$type,'label'=>'');
	}
	if(!$reason && !$uploadRules['mimes']){$reason = 'The file does not meet the gallery upload requirements.';}
	return array('enabled'=>$rules['mode']!=='off' && !empty($files),'allowed'=>$reason==='' && !empty($files),'reason'=>$reason ? cg_member_t($reason) : '', 'files'=>$files,'accept'=>implode(',',array_map('cg_member_replacement_accept_extension',array_keys($uploadRules['mimes']))),'pdfPreview'=>$uploadRules['pdfPreview'],'uploadMaxBytes'=>$uploadMaxBytes,'revision'=>cg_member_entry_revision($row),'approval'=>!empty($row->Active) && !cg_member_replacement_auto_activate($gid),'backUrl'=>cg_member_entry_back_url($row),'canDelete'=>empty($row->EcommerceEntry));
}
}

if(!function_exists('cg_member_replace_file')){
function cg_member_replace_file($initial,$slot,$revision){
	global $wpdb;
	$user = get_current_user_id();
	$lock = 'cg-replace-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.$user);
	if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,5)',$lock))!=='1'){return new WP_Error('busy','Please try again.');}
	try{
		// A second request may have waited while the first one changed this entry.
		$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery WHERE id=%d AND GalleryID=%d AND WpUserId=%d',$initial->id,$initial->GalleryID,$user));
		if(!$row){return new WP_Error('access','This gallery is not available.');}
		return cg_member_replace_file_locked($row,$slot,$revision);
	}finally{$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));}
}
}

if(!function_exists('cg_member_replace_file_locked')){
function cg_member_replace_file_locked($initial,$slot,$revision){
	global $wpdb;
	$gid = absint($initial->GalleryID); $pid = absint($initial->id);
	$table = $wpdb->prefix.'contest_gal1ery';
	$options = cg_member_options($gid);
	$reason = cg_member_replacement_policy($initial,cg_member_replacement_options($gid),cg_member_replacement_source($gid));
	if($reason){return new WP_Error('policy',$reason);}
	$files = cg_member_entry_files($initial);
	if(!isset($files[$slot]) || !hash_equals(cg_member_entry_revision($initial),$revision)){return new WP_Error('stale','This entry has changed. Reload it and try again.');}
	$oldId = !empty($files[$slot]['isRealIdSource']) ? $initial->WpUpload : $files[$slot]['WpUpload'];
	if(!cg_member_replacement_attachment_type($oldId) && !cg_member_replacement_social_source($oldId)){return new WP_Error('type','Image replacement is not allowed.');}
	$cgUploadJob = cg_upload_job_context();
	$budget = cg_upload_job_phase('finalize') ? array('limits'=>cg_member_replacement_limits(get_current_user_id()),'usage'=>cg_member_replacement_usage(get_current_user_id())) : cg_member_replacement_admit(get_current_user_id());
	if(is_wp_error($budget)){return $budget;}
	if(cg_upload_job_phase('finalize')){$_FILES['replacement']=cg_upload_job_stored_file('replacement');$_FILES['replacement_preview']=cg_upload_job_stored_file('preview');}
	$valid = cg_member_replacement_validate_upload(isset($_FILES['replacement']) ? $_FILES['replacement'] : null,$gid,$options,cg_upload_job_phase('finalize'));
	if(is_wp_error($valid)){return $valid;}
	$uploadBytes = cg_upload_job_phase('finalize') ? 0 : filesize($_FILES['replacement']['tmp_name']) + ($valid['pdfPreview'] ? filesize($_FILES['replacement_preview']['tmp_name']) : 0);
	if($budget['usage']['bytes']+$uploadBytes>$budget['limits']['bytes'] || $budget['usage']['count']+($valid['pdfPreview'] ? 2 : 1)>$budget['limits']['attachments']){return cg_member_replacement_storage_error();}
	require_once ABSPATH.'wp-admin/includes/image.php';
	require_once ABSPATH.'wp-admin/includes/file.php';
	require_once ABSPATH.'wp-admin/includes/media.php';
	if(cg_upload_job_phase('receive')){return cg_upload_job_accept_replace($initial,$slot,$revision,$valid,$budget);}
	add_filter('wp_check_filetype_and_ext','cg_member_replacement_check_icon',10,4);
	try{
		$attachment = cg_upload_job_phase('finalize') ? cg_upload_job_gallery_attachment('replacement') : media_handle_upload('replacement',0,array('post_author'=>get_current_user_id()),array('test_form'=>false,'mimes'=>$valid['mimes']));
	}finally{remove_filter('wp_check_filetype_and_ext','cg_member_replacement_check_icon',10);}
	if(is_wp_error($attachment)){return new WP_Error('upload','The file does not meet the gallery upload requirements.');}
	$preview = 0; $committed = false; $transaction = false; $statsLock = false; $cachePath = ''; $cacheBackup = null;
	$recovery = array('row'=>null,'changes'=>array(),'updated'=>false,'pages'=>array(),'pagesAfter'=>array());
	try{
		if($valid['pdfPreview']){
			$preview = cg_upload_job_phase('finalize') ? cg_upload_job_gallery_attachment('preview') : cg_member_replacement_create_pdf_preview($attachment);
			if(is_wp_error($preview)){return $preview;}
		}
		$watermark = cg_entry_watermark_get_frontend_upload_settings($gid,(object)(isset($options['visual']) ? $options['visual'] : array()));
		if($watermark){
			$watermark = cg_entry_watermark_resolve_frontend_upload_settings($gid,$pid,$watermark);
			$result = cg_entry_watermark_apply_frontend_upload($gid,$pid,$attachment,pathinfo(get_attached_file($attachment),PATHINFO_EXTENSION),$watermark);
			if(is_wp_error($result)){return new WP_Error('upload','The file does not meet the gallery upload requirements.');}
		}
		$tracked = cg_member_replacement_track_storage(get_current_user_id(),$attachment,$preview,$budget);
		if(is_wp_error($tracked)){return $tracked;}
		$media = cg_member_replacement_media_data($attachment,$preview);
		// Vote writers use this same lock. Take it before the database row lock.
		if(!empty($initial->Active)){cg1l_get_stats_for_update($gid,$pid,$statsLock); if(!$statsLock){return new WP_Error('busy','Please try again.');}}
		cg_upload_job_db_transaction('START TRANSACTION'); $transaction = true;
		$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$table.' WHERE id=%d AND GalleryID=%d AND WpUserId=%d FOR UPDATE',$pid,$gid,get_current_user_id()));
		if(!$row || !hash_equals(cg_member_entry_revision($row),$revision)){return new WP_Error('stale','This entry has changed. Reload it and try again.');}
		$recovery['row']=clone $row;
		$reason = cg_member_replacement_policy($row,cg_member_replacement_options($gid),cg_member_replacement_source($gid));
		if($reason){return new WP_Error('policy',$reason);}
		$files = cg_member_entry_files($row);
		$primary = !empty($files[$slot]['isRealIdSource']);
		$changes = array();
		if($primary){
			foreach(array('WpUpload','NamePic','ImgType','Width','Height','rThumb','PdfPreview') as $key){$changes[$key] = $media[$key];}
			// Database type identifies the original file. The runtime may display a PDF preview.
			$changes['ImgType'] = $valid['extension']==='jpe' ? 'jpg' : $valid['extension'];
			$changes['Exif'] = serialize($media['Exif']);
			$files[$slot] = array('WpUpload'=>$attachment,'isRealIdSource'=>true);
		}else{$files[$slot] = $media;}
		$changes['MultipleFiles'] = count($files)>1 ? serialize($files) : '';
		$active = !empty($row->Active) && cg_member_replacement_auto_activate($gid) ? 1 : 0;
		$changes['Active'] = $active;
		// Preserve legacy vote columns and use an optimistic condition as well as the row lock.
		$where = array('id'=>$pid,'GalleryID'=>$gid,'WpUserId'=>get_current_user_id(),'WpUpload'=>$row->WpUpload,'MultipleFiles'=>$row->MultipleFiles,'Active'=>$row->Active,'EcommerceEntry'=>$row->EcommerceEntry);
		foreach(cg_member_replacement_vote_columns() as $column){if(isset($row->$column)){$where[$column]=$row->$column;}}
		$main = cg1l_get_entry_main_data($gid,$pid);
		if($active && empty($main[$pid])){return new WP_Error('data','Please reload the page and try again.');}
		// Prepare the published media cache before committing. A failed write rolls back the association.
		if($active && $primary){
			foreach(array('WpUpload','NamePic','ImgType','Width','Height','rThumb','PdfPreview','guid','thumbnail','medium','large','full','post_title','post_name','post_content','post_caption','post_date','post_alt','post_mime_type','type','PdfOriginal','PdfPreviewImage','PdfPreviewImageLarge') as $key){$main[$pid][$key]=$media[$key];}
			$upload = wp_upload_dir();
			$cachePath = $upload['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/image-data/image-data-'.$pid.'.json';
			$cacheBackup = file_get_contents($cachePath);
			if(!cg1l_write_atomic_file_payload($cachePath,wp_json_encode($main[$pid]))){return new WP_Error('cache','Please try again.');}
		}
		if($preview && $wpdb->insert($wpdb->prefix.'contest_gal1ery_pdf_previews',array('WpUpload'=>$attachment,'WpUploadPreview'=>$preview),array('%d','%d'))===false){return new WP_Error('save','Please try again.');}
		$recovery['changes']=$changes;
		if($wpdb->update($table,$changes,$where)!==1){return new WP_Error('stale','This entry has changed. Reload it and try again.');}
		$recovery['updated']=true;
		foreach($changes as $key=>$value){$row->$key = $value;}
		if($primary){
			$recovery['pages']=cg_member_replacement_page_snapshot($row);
			$updatedUrls = cg_entry_page_update_replacement_urls($row);
			$recovery['pagesAfter']=cg_member_replacement_page_snapshot($row);
			if(is_wp_error($updatedUrls)){return $updatedUrls;}
		}
		if(cg_upload_job_db_transaction('COMMIT')===false){return new WP_Error('save','Please try again.');}
		$transaction = false; $committed = true;
		if(!cg_upload_job_phase('finalize')){
			if(!$active){cg_deactivate_images($gid,wp_upload_dir(),array($pid=>$pid));}
			else{cg_entry_watermark_invalidate($gid,$pid);}
		}
		$savedRow = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$table.' WHERE id=%d AND GalleryID=%d AND WpUserId=%d',$pid,$gid,get_current_user_id()));
		if($savedRow){$row = $savedRow;}
		else{foreach($changes as $key=>$value){$row->$key = $value;}}
		$shortcode = isset($_POST['publicShortcode']) ? sanitize_key($_POST['publicShortcode']) : '';
		$links = cg_member_entry_links($row,$shortcode);
		return array('pid'=>$pid,'active'=>$active,'attachment'=>$attachment,'entryUrl'=>$links['entryGuid'],'entryUrls'=>cg_member_entry_url_variants($row),'message'=>cg_member_t('File replaced.'),'slot'=>(string)$slot,'entry'=>cg_member_owner_entry_media($row,$shortcode,true),'state'=>cg_member_entry_action_bootstrap($row));
	}finally{
		if($transaction){cg_upload_job_db_transaction('ROLLBACK');}
		$restored=true;
		if(!$committed && !cg_upload_job_phase('finalize')){$restored=cg_member_replacement_restore($recovery,$table,$pid);}
		if(!$committed){cg_entry_page_clear_cache($initial);}
		cg_member_replacement_cleanup($attachment,$preview,$cachePath,$cacheBackup,!$committed && $restored,$statsLock);
	}
}
}

add_action('wp_ajax_cg_member_entry_action','cg_member_entry_action_ajax');
if(!function_exists('cg_member_entry_action_ajax')){
function cg_member_entry_action_ajax(){
	nocache_headers();
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	$row = cg_member_entry_action_context();
	if(is_wp_error($row)){wp_send_json_error(array('message'=>cg_member_t($row->get_error_message())));}
	$mode = isset($_POST['mode']) ? sanitize_key($_POST['mode']) : 'bootstrap';
	if($mode==='bootstrap'){
		$state = cg_member_entry_action_bootstrap($row);
		if(!empty($_POST['includeEntry'])){
			$shortcode = isset($_POST['publicShortcode']) ? sanitize_key($_POST['publicShortcode']) : '';
			$state['entry'] = cg_member_owner_entry_media($row,$shortcode,true);
			$state['entryUrls'] = cg_member_entry_url_variants($row);
		}
		wp_send_json_success($state);
	}
	if($mode!=='replace'){wp_send_json_error(array('message'=>cg_member_t('Please try again.')));}
	$slot = isset($_POST['slot']) ? sanitize_text_field(wp_unslash($_POST['slot'])) : '';
	$revision = isset($_POST['revision']) ? sanitize_text_field(wp_unslash($_POST['revision'])) : '';
	$result = cg_member_replace_file($row,$slot,$revision);
	if(is_wp_error($result)){
		$error = array('message'=>cg_member_t($result->get_error_message()));
		$details = $result->get_error_data();
		if(is_array($details) && isset($details['uploadValidation'])){$error['uploadValidation']=$details['uploadValidation'];}
		wp_send_json_error($error);
	}
	wp_send_json_success($result);
}
}
