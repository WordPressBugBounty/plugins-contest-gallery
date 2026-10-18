<?php
if(!defined('ABSPATH')){exit;}
require_once dirname(__DIR__).'/general/cg-frontend-security-state.php';

/* Durable upload state. Only the original submit may supply files/form fields.
 * Subsequent requests operate on server-owned attachments and stored context. */
if(!function_exists('cg_upload_job_owner')){
function cg_upload_job_owner(){
	$session = cg1l_get_or_create_frontend_session_id();
	return hash_hmac('sha256',get_current_user_id().'|'.$session,wp_salt('auth'));
}
}
if(!function_exists('cg_upload_job_key')){
function cg_upload_job_key($id){return '_cg_upload_job_'.$id;}
}
if(!function_exists('cg_upload_job_read')){
function cg_upload_job_read($id){
	global $wpdb;
	if(!preg_match('/^[a-f0-9]{64}$/',(string)$id)){return false;}
	$value = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",cg_upload_job_key($id)));
	return $value ? json_decode($value,true) : false;
}
}
if(!function_exists('cg_upload_job_save')){
function cg_upload_job_save(&$job){
	global $wpdb;
	$key = cg_upload_job_key($job['id']);
	$previous=cg_upload_job_read($job['id']);
	$job['revision']=$previous && isset($previous['revision']) ? (int)$previous['revision']+1 : 1;
	$json = wp_json_encode($job);
	if($json===false){throw new Exception('Upload state could not be saved.');}
	$result = $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,%s,'no') ON DUPLICATE KEY UPDATE option_value=VALUES(option_value),autoload='no'",$key,$json));
	wp_cache_delete($key,'options');
	if($result===false){throw new Exception('Upload state could not be saved.');}
	$GLOBALS['cgUploadJob'] = $job;
}
}
if(!function_exists('cg_upload_job_lock_name')){
function cg_upload_job_lock_name($id){global $wpdb; return 'cg-upload-'.md5(DB_NAME.'|'.$wpdb->prefix.'|'.$id);}
}
if(!function_exists('cg_upload_job_lock')){
function cg_upload_job_lock($id){global $wpdb; return (string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',cg_upload_job_lock_name($id)))==='1';}
}
if(!function_exists('cg_upload_job_unlock')){
function cg_upload_job_unlock($id){global $wpdb; $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',cg_upload_job_lock_name($id)));}
}
if(!function_exists('cg_upload_job_running')){
function cg_upload_job_running($id){global $wpdb; return !empty($wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)',cg_upload_job_lock_name($id))));}
}
if(!function_exists('cg_upload_job_token')){
function cg_upload_job_token($job){return hash_hmac('sha256',$job['id'].'|'.$job['owner'],wp_salt('nonce'));}
}
if(!function_exists('cg_upload_job_phase')){
function cg_upload_job_phase($phase){return !empty($GLOBALS['cgUploadJobPhase']) && $GLOBALS['cgUploadJobPhase']===$phase;}
}
if(!function_exists('cg_upload_job_error_text')){
function cg_upload_job_error_text(){return 'Image processing could not be completed. Please reduce the image dimensions and file size and upload it again.';}
}
if(!function_exists('cg_upload_job_wait_text')){
function cg_upload_job_wait_text(){return 'Image processing is taking longer than expected. Please check the status again.';}
}
if(!function_exists('cg_upload_job_form_error')){
function cg_upload_job_form_error($html){
	$text=trim(wp_strip_all_tags(preg_replace('~<script\b[^>]*>.*?</script>~is','',$html)));
	return $text!=='' ? sanitize_text_field(substr($text,0,1000)) : 'The file or form does not meet the gallery upload requirements.';
}
}
if(!function_exists('cg_upload_job_context')){
function cg_upload_job_context(){return isset($GLOBALS['cgUploadJob']) ? $GLOBALS['cgUploadJob'] : false;}
}
if(!function_exists('cg_upload_job_storage_ready')){
function cg_upload_job_storage_ready($strict = true){
	global $wpdb;
	// A rollback must include the job, media, credits and gallery entry together.
	$tables=array($wpdb->options,$wpdb->posts,$wpdb->postmeta,$wpdb->usermeta,$wpdb->prefix.'contest_gal1ery',$wpdb->prefix.'contest_gal1ery_entries',$wpdb->prefix.'contest_gal1ery_paid_action_entitlements',$wpdb->prefix.'contest_gal1ery_paid_action_ledger');
	foreach($tables as $table){
		$row=$wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS WHERE Name=%s',$table));
		if(!$row || strtolower($row->Engine)!=='innodb'){
			if($strict){throw new Exception('This upload cannot be continued because the database configuration has changed. Please contact the gallery administrator.');}
			return false;
		}
	}
	return true;
}
}
// Older MySQL releases keep only one named lock per connection. Probe before
// starting a job, while this request holds no application lock of its own.
if(!function_exists('cg_upload_job_multiple_locks_available')){
function cg_upload_job_multiple_locks_available(){
	global $wpdb;
	$name='cg-probe-'.str_replace('-','',wp_generate_uuid4());
	$first=$name.'a'; $second=$name.'b';
	try{
		if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$first))!=='1'){return false;}
		if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$second))!=='1'){return false;}
		return (string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)=CONNECTION_ID()',$first))==='1';
	}finally{
		$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$second));
		$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$first));
	}
}
}
if(!function_exists('cg_upload_job_commit_admission')){
function cg_upload_job_commit_admission(){
	global $wpdb;
	if(!empty($GLOBALS['cgUploadJobTransaction'])){
		if(!empty($GLOBALS['cgUploadJobRollback']) || $wpdb->query('COMMIT')===false){throw new Exception('Upload admission could not be saved.');}
		unset($GLOBALS['cgUploadJobTransaction'],$GLOBALS['cgUploadJobRollback']);
	}
}
}
if(!function_exists('cg_upload_job_pending_entries')){
function cg_upload_job_pending_entries($gid,$order = 0,$user = 0,$cookie = '',$ip = '',$category = null){
	global $wpdb;
	$current=cg_upload_job_context(); $count=0;
	$rows=$wpdb->get_col($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('_cg_upload_job_').'%'));
	foreach($rows as $raw){
		$job=json_decode($raw,true);
		if(!$job || $job['id']===($current ? $current['id'] : '') || $job['gid']!=(int)$gid || empty($job['admitted']) || in_array($job['state'],array('complete','failed'),true)){continue;}
		if($order && (int)$job['order']!==(int)$order){continue;}
		if(!$order && (($user && $job['user']!=(int)$user) || ($cookie!=='' && $job['cookie']!==$cookie) || ($ip!=='' && $job['ip']!==$ip))){continue;}
		// Jobs admitted before category reservations existed conservatively reserve every slot.
		$count += $category!==null && isset($job['categoryCounts']) ? (isset($job['categoryCounts'][$category]) ? (int)$job['categoryCounts'][$category] : 0) : (int)$job['reservedEntries'];
	}
	return $count;
}
}
if(!function_exists('cg_upload_job_replacement_usage')){
function cg_upload_job_replacement_usage($user){
	global $wpdb;
	$current=cg_upload_job_context(); $usage=array('count'=>0,'bytes'=>0);
	$rows=$wpdb->get_col($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s",$wpdb->esc_like('_cg_upload_job_').'%'));
	foreach($rows as $raw){
		$job=json_decode($raw,true);
		if(!$job || $job['mode']!=='replace' || $job['user']!=(int)$user || empty($job['admitted']) || ($current && $job['id']===$current['id']) || $job['state']==='complete'){continue;}
		// Failed originals still occupy storage until the scoped cleanup removes them.
		if(empty($job['files'])){if($job['state']!=='failed'){$usage['count']+=$job['replace']['valid']['pdfPreview'] ? 2 : 1;$usage['bytes']+=$job['replace']['bytes'];}continue;}
		foreach($job['files'] as $file){
			if(empty($file['attachment']) || !get_post($file['attachment']) || get_post_meta($file['attachment'],'_cg_member_replacement_owner',true)){continue;}
			$usage['count']++;$usage['bytes']+=cg_member_replacement_attachment_bytes($file['attachment']);
		}
	}
	return $usage;
}
}

/* Respect an outer upload transaction instead of implicitly committing it. */
if(!function_exists('cg_upload_job_db_transaction')){
function cg_upload_job_db_transaction($sql){
	global $wpdb;
	if(!empty($GLOBALS['cgUploadJobTransaction'])){
		if($sql==='ROLLBACK'){$GLOBALS['cgUploadJobRollback']=true;}
		return 1;
	}
	return $wpdb->query($sql);
}
}
if(!function_exists('cg_upload_job_assert_authorized')){
function cg_upload_job_assert_authorized($job){
	if(!$job || !hash_equals($job['owner'],cg_upload_job_owner()) || $job['expires']<time()){
		throw new Exception('This upload session has expired. Please reload the page and try again.');
	}
	$token = isset($_POST['jobToken']) && is_string($_POST['jobToken']) ? $_POST['jobToken'] : '';
	if(!hash_equals(cg_upload_job_token($job),$token)){throw new Exception('Upload authorization failed. Please reload the page and try again.');}
}
}
if(!function_exists('cg_upload_job_prepare_gallery')){
function cg_upload_job_prepare_gallery($gid){
	global $wpdb;
	$hash = isset($_POST['check']) && is_string($_POST['check']) ? sanitize_text_field(wp_unslash($_POST['check'])) : '';
	$order = !empty($_POST['cg_order_item_id']) && is_scalar($_POST['cg_order_item_id']) ? absint($_POST['cg_order_item_id']) : 0;
	if($order){
		$access=cg1l_get_order_upload_authorization($gid,$order,$hash);
		$authorized=!empty($access['authorized']);
	}else{$authorized=cg1l_validate_upload_access_hash($hash,$gid);}
	if(!$authorized){throw new Exception('Upload authorization failed. Please reload the page and try again.');}
	$general=$wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d",$gid));
	$pro=$wpdb->get_row($wpdb->prepare("SELECT RegUserUploadOnly FROM {$wpdb->prefix}contest_gal1ery_pro_options WHERE GalleryID=%d",$gid));
	if(!$general || !$pro){throw new Exception('This gallery is not available.');}
	if(!$order && absint($pro->RegUserUploadOnly)===1 && !is_user_logged_in()){throw new Exception('Please log in before uploading.');}
	$upload=wp_upload_dir();
	$path=$upload['basedir'].'/contest-gallery/gallery-id-'.$gid.'/json/'.$gid.'-options.json';
	$options=is_file($path) ? json_decode(file_get_contents($path),true) : false;
	if(!is_array($options) || !$options){throw new Exception('Upload form configuration could not be read. Please reload the page and try again.');}
	if(!$order){
		$interval=cg_shortcode_interval_check($gid,$options,'cg_users_upload');
		if(empty($interval['shortcodeIsActive'])){throw new Exception('This gallery is not available.');}
	}
}
}
if(!function_exists('cg_upload_job_prepare')){
function cg_upload_job_prepare(){
	$owner = cg_upload_job_owner();
	$request = isset($_POST['requestKey']) && is_string($_POST['requestKey']) ? $_POST['requestKey'] : '';
	if(!preg_match('/^[a-zA-Z0-9-]{20,80}$/D',$request)){throw new Exception('Please reload the page and try again.');}
	$mode = isset($_POST['uploadMode']) && $_POST['uploadMode']==='replace' ? 'replace' : 'gallery';
	$gid = isset($_POST['gid']) && is_scalar($_POST['gid']) ? absint($_POST['gid']) : 0;
	if(!$gid){throw new Exception('This gallery is not available.');}
	if($mode==='replace'){
		$row = cg_member_entry_action_context();
		if(is_wp_error($row)){throw new Exception($row->get_error_message());}
	}else{cg_upload_job_prepare_gallery($gid);}
	$id = hash_hmac('sha256',$owner.'|'.$gid.'|'.$mode.'|'.$request,wp_salt('auth'));
	$existing = cg_upload_job_read($id);
	if($existing){if($existing['owner']!==$owner || $existing['expires']<time()){throw new Exception('This upload session has expired.');} return $existing;}
	// A fallback is a pre-transfer decision, never a retry of an existing job.
	if(!cg_upload_job_storage_ready(false) || !cg_upload_job_multiple_locks_available()){return array('transport'=>'classic');}
	// A short common admission lock protects both owner counts and IP quotas.
	// No per-job lock is nested: older MySQL supports only one named lock per connection.
	$lock=cg_security_state_lock('upload-prepare','site');
	if(!$lock){throw new Exception('Please try again.');}
	try{
		$existing = cg_upload_job_read($id);
		if($existing){if($existing['owner']!==$owner || $existing['expires']<time()){throw new Exception('This upload session has expired.');} return $existing;}
		global $wpdb;
		$active=0;
		$owned=$wpdb->get_col($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value LIKE %s",$wpdb->esc_like('_cg_upload_job_').'%', '%'.$wpdb->esc_like('"owner":"'.$owner.'"').'%'));
		foreach($owned as $raw){$pending=json_decode($raw,true);if($pending && $pending['owner']===$owner && $pending['expires']>=time() && !in_array($pending['state'],array('complete','failed'),true)){$active++;}}
		if($active>=20){throw new Exception('Too many pending uploads. Please try again later.');}
		if(!cg_security_rate_take_locked('upload-job-ip',cg_security_remote_address(),120,HOUR_IN_SECONDS)){throw new Exception('Too many upload requests. Please try again later.');}
		$job = array('id'=>$id,'owner'=>$owner,'user'=>get_current_user_id(),'gid'=>$gid,'pid'=>isset($_POST['pid']) ? absint($_POST['pid']) : 0,'mode'=>$mode,'created'=>time(),'expires'=>time()+DAY_IN_SECONDS,'state'=>'receiving','files'=>array(),'post'=>array(),'result'=>null,'message'=>'','entries'=>array(),'mails'=>array(),'reservation'=>null,'additional'=>false,'admitted'=>false,'order'=>0,'cookie'=>'','ip'=>'','reservedEntries'=>0);
		cg_upload_job_save($job);
	}finally{cg_security_state_unlock($lock);}
	if(!wp_next_scheduled('cg_upload_jobs_cleanup')){wp_schedule_event(time()+HOUR_IN_SECONDS,'hourly','cg_upload_jobs_cleanup');}
	return $job;
}
}
if(!function_exists('cg_upload_job_file_array')){
function cg_upload_job_file_array($files,$key){
	$file = array();
	foreach(array('name','type','tmp_name','error','size') as $part){
		$value = isset($files[$part][$key]) ? $files[$part][$key] : '';
		$file[$part] = is_array($value) ? reset($value) : $value;
	}
	return $file;
}
}
if(!function_exists('cg_upload_job_attach')){
function cg_upload_job_attach($file,$slot,$mimes = null){
	$job = cg_upload_job_context();
	$slot = (string)$slot;
	if(isset($job['files'][$slot]) && !empty($job['files'][$slot]['attachment'])){return $job['files'][$slot]['attachment'];}
	$record = array('name'=>sanitize_file_name($file['name']),'type'=>$file['type'],'size'=>(int)$file['size'],'attachment'=>0,'attempts'=>0,'started'=>0,'state'=>'pending','expected'=>null,'error'=>'');
	$job['files'][$slot] = $record;
	cg_upload_job_save($job);
	$directory = '/'.gmdate('Y/m',$job['created']).'/cg-'.$job['id'].'-'.preg_replace('/[^a-zA-Z0-9_-]/','',$slot);
	$filter = function($upload) use ($directory){
		$upload['subdir']=$directory; $upload['path']=$upload['basedir'].$directory; $upload['url']=$upload['baseurl'].$directory; return $upload;
	};
	add_filter('upload_dir',$filter);
	try{
		$overrides = array('test_form'=>false);
		if(is_array($mimes)){$overrides['mimes']=$mimes;}
		$uploaded = wp_handle_upload($file,$overrides,gmdate('Y/m',$job['created']));
	}finally{remove_filter('upload_dir',$filter);}
	if(isset($uploaded['error'])){throw new Exception($uploaded['error']);}
	$job = cg_upload_job_context();
	$job['files'][$slot]['path']=$uploaded['file'];
	$job['files'][$slot]['url']=$uploaded['url'];
	$job['files'][$slot]['type']=$uploaded['type'];
	cg_upload_job_save($job);
	$title = pathinfo($record['name'],PATHINFO_FILENAME);
	$meta = wp_read_image_metadata($uploaded['file']);
	$attachment = array('post_title'=>$title,'post_mime_type'=>$uploaded['type'],'guid'=>$uploaded['url'],'post_author'=>$job['user'],'meta_input'=>array('_cg_upload_job'=>$job['id'],'_cg_upload_job_slot'=>$slot));
	if($meta){
		if(!empty($meta['title']) && !is_numeric(sanitize_title($meta['title']))){$attachment['post_title']=$meta['title'];}
		if(!empty($meta['caption'])){$attachment['post_excerpt']=$meta['caption'];}
	}
	$id = wp_insert_attachment($attachment,$uploaded['file'],0,true);
	if(is_wp_error($id)){throw new Exception($id->get_error_message());}
	$job['files'][$slot]['attachment']=$id;
	cg_upload_job_save($job);
	return $id;
}
}

/* Called only after the existing gallery validator has accepted the full form. */
if(!function_exists('cg_upload_job_accept_gallery')){
function cg_upload_job_accept_gallery($context){
	$job = cg_upload_job_context();
	$job['additional']=!empty($context['proOptions']->AdditionalFiles);
	$job['post']=$GLOBALS['cgUploadJobOriginalPost'];
	unset($job['post']['jobToken'],$job['post']['cg_nonce']);
	$job['contact']=!empty($context['isOnlyContactEntry']);
	$job['order']=!empty($context['OrderItem']) ? absint($context['OrderItem']) : 0;
	$job['admitted']=true; $job['reservedEntries']=$job['additional'] || $job['contact'] ? 1 : count($context['files']['name']);
	if(isset($context['cgUploadCategories']) && is_array($context['cgUploadCategories'])){$job['categoryCounts']=array_count_values($context['cgUploadCategories']);}
	$job['cookie']=$context['CookieId']; $job['ip']=isset($context['userIP']) ? $context['userIP'] : '';
	$job['reservation']=$context['cgPaidActionUploadReservation'];
	cg_upload_job_save($job);
	cg_upload_job_commit_admission();
	if(is_array($job['reservation'])){
		$pending =& cg_paid_actions_pending_reservations(); unset($pending[$job['reservation']['token']]);
		unset($GLOBALS['cgPaidActionUploadReservation']);
	}
	if(!$job['contact']){
		$files = $context['files'];
		foreach($files['name'] as $key=>$unused){
			$file=cg_upload_job_file_array($files,$key);
			// Keep the existing optional customer filename prefix.
			$prefix='';
			if(!empty($context['CustomImageName']) && !empty($context['CustomImageNamePath'])){
				$values=array('GalleryId'=>$job['gid'],'GalleryName'=>$context['GalleryName'],'WpUserId'=>$context['WpUserId'],'WpUserName'=>$context['WpUserName']);
				foreach(explode('-',$context['CustomImageNamePath']) as $name){if(!empty($values[$name])){$prefix.=$values[$name].'-';}}
			}
			$file['name']=$prefix.$file['name'];
			try{cg_upload_job_attach($file,(string)$key);}
			catch(Exception $error){
				$job=cg_upload_job_context();
				if($job['additional']){throw $error;}
				$job['files'][(string)$key]['state']='failed';$job['files'][(string)$key]['error']=$error->getMessage();cg_upload_job_save($job);
			}
		}
	}
	$job=cg_upload_job_context(); $job['state']='processing'; cg_upload_job_save($job);
	$GLOBALS['cgUploadJobAccepted']=true;
}
}
if(!function_exists('cg_upload_job_accept_replace')){
function cg_upload_job_accept_replace($row,$slot,$revision,$valid,$budget){
	$job=cg_upload_job_context();
	$job['post']=$GLOBALS['cgUploadJobOriginalPost'];
	unset($job['post']['jobToken'],$job['post']['cg_nonce']);
	$job['replace']=array('pid'=>absint($row->id),'slot'=>$slot,'revision'=>$revision,'valid'=>$valid,'budget'=>$budget,'bytes'=>filesize($_FILES['replacement']['tmp_name'])+($valid['pdfPreview'] ? filesize($_FILES['replacement_preview']['tmp_name']) : 0));
	$job['admitted']=true;
	cg_upload_job_save($job);
	cg_upload_job_commit_admission();
	add_filter('wp_check_filetype_and_ext','cg_member_replacement_check_icon',10,4);
	try{cg_upload_job_attach($_FILES['replacement'],'replacement',$valid['mimes']);}
	finally{remove_filter('wp_check_filetype_and_ext','cg_member_replacement_check_icon',10);}
	if(!empty($valid['pdfPreview'])){cg_upload_job_attach($_FILES['replacement_preview'],'preview',array('png'=>'image/png'));}
	$job=cg_upload_job_context(); $job['state']='processing'; cg_upload_job_save($job);
	$GLOBALS['cgUploadJobAccepted']=true;
	return array('staged'=>true);
}
}
if(!function_exists('cg_upload_job_gallery_attachment')){
function cg_upload_job_gallery_attachment($key){
	$job=cg_upload_job_context();
	return isset($job['files'][(string)$key]['attachment']) ? absint($job['files'][(string)$key]['attachment']) : 0;
}
}
// Replacement validation must use the submitted original, not a WordPress
// derivative whose size or orientation may already have changed.
if(!function_exists('cg_upload_job_original_path')){
function cg_upload_job_original_path($attachment){
	$path = function_exists('wp_get_original_image_path') ? wp_get_original_image_path($attachment) : false;
	return $path ? $path : get_attached_file($attachment);
}
}
if(!function_exists('cg_upload_job_restore_files')){
function cg_upload_job_restore_files($job,$onlyReady = false){
	$data=array('name'=>array(),'type'=>array(),'tmp_name'=>array(),'error'=>array(),'size'=>array());
	foreach($job['files'] as $slot=>$file){
		if($onlyReady && $file['state']!=='ready'){continue;}
		$values=array('name'=>$file['name'],'type'=>$file['type'],'tmp_name'=>cg_upload_job_original_path($file['attachment']),'error'=>0,'size'=>$file['size']);
		foreach($values as $part=>$value){$data[$part][$slot]=array($value);}
	}
	return array('data'=>$data);
}
}
if(!function_exists('cg_upload_job_stored_file')){
function cg_upload_job_stored_file($slot){
	$job=cg_upload_job_context();
	if(!$job || !isset($job['files'][$slot])){return null;}
	$file=$job['files'][$slot];
	if(!$file['attachment'] || get_post_meta($file['attachment'],'_cg_upload_job',true)!==$job['id']){return null;}
	return array('name'=>$file['name'],'tmp_name'=>cg_upload_job_original_path($file['attachment']),'error'=>UPLOAD_ERR_OK,'type'=>$file['type'],'size'=>$file['size']);
}
}
if(!function_exists('cg_upload_job_process')){
function cg_upload_job_process(&$job){
	require_once ABSPATH.'wp-admin/includes/image.php';
	foreach($job['files'] as $slot=>$file){
		if($file['state']==='ready' || $file['state']==='failed'){continue;}
		if($file['attempts']>=3 || ($file['started'] && time()>=$file['started']+180)){
			$job['files'][$slot]['state']='failed'; $job['files'][$slot]['error']=cg_upload_job_error_text(); cg_upload_job_save($job); continue;
		}
		if(empty($file['attachment']) || !is_file(get_attached_file($file['attachment']))){
			$job['files'][$slot]['state']='failed'; $job['files'][$slot]['error']='The uploaded file could not be found.'; cg_upload_job_save($job); continue;
		}
		$file['attempts']++; $file['state']='processing';
		if(!$file['started']){$file['started']=time();}
		$job['files'][$slot]=$file; cg_upload_job_save($job);
		$id=$file['attachment'];
		$expectedFilter=function($sizes,$meta,$attachment) use ($id,$slot){
			if((int)$attachment!==$id){return $sizes;}
			$current=cg_upload_job_context();
			if($current['files'][$slot]['expected']===null){$current['files'][$slot]['expected']=$sizes; cg_upload_job_save($current);}
			return array_intersect_key($sizes,$current['files'][$slot]['expected']);
		};
		$missingFilter=function($sizes,$meta,$attachment) use ($id,$slot){
			$current=cg_upload_job_context();
			return (int)$attachment===$id && is_array($current['files'][$slot]['expected']) ? array_intersect_key($sizes,$current['files'][$slot]['expected']) : $sizes;
		};
		add_filter('intermediate_image_sizes_advanced',$expectedFilter,PHP_INT_MAX,3);
		add_filter('wp_get_missing_image_subsizes',$missingFilter,PHP_INT_MAX,3);
		try{
			// This hook has no request-controlled switches. Local tests may interrupt a worker here.
			do_action('cg_upload_job_before_image_processing',$job['id'],$slot,$file['attempts']);
			if(wp_attachment_is_image($id) && $file['type']!=='image/x-icon'){
				$meta=wp_update_image_subsizes($id);
				if(is_wp_error($meta)){throw new Exception($meta->get_error_message());}
				if(empty($meta['width']) || empty($meta['height']) || wp_get_missing_image_subsizes($id)){throw new Exception(cg_upload_job_error_text());}
			}else{
				$meta=wp_generate_attachment_metadata($id,get_attached_file($id));
				if(is_wp_error($meta)){throw new Exception($meta->get_error_message());}
				wp_update_attachment_metadata($id,$meta);
			}
			$job=cg_upload_job_context(); $job['files'][$slot]['state']='ready'; $job['files'][$slot]['error']='';
		}catch(Exception $error){
			$job=cg_upload_job_context(); $job['files'][$slot]['state']=$file['attempts']>=3 || time()>=$file['started']+180 ? 'failed' : 'pending';
			$job['files'][$slot]['error']=cg_upload_job_error_text();
			error_log('Contest Gallery upload '.$job['id'].' image processing: '.$error->getMessage());
		}finally{
			remove_filter('intermediate_image_sizes_advanced',$expectedFilter,PHP_INT_MAX);
			remove_filter('wp_get_missing_image_subsizes',$missingFilter,PHP_INT_MAX);
		}
		cg_upload_job_save($job);
		return;
	}
	cg_upload_job_processing_state($job);
}
}
if(!function_exists('cg_upload_job_processing_state')){
function cg_upload_job_processing_state(&$job){
	$failed=0; $ready=0; $pending=false; $changed=false;
	foreach($job['files'] as $slot=>$file){
		if($file['state']!=='ready' && $file['state']!=='failed'){
			// The last thumbnail may have been saved immediately before an interrupted response.
			if($file['state']==='processing' && is_array($file['expected']) && wp_attachment_is_image($file['attachment'])){
				require_once ABSPATH.'wp-admin/includes/image.php';
				$meta=wp_get_attachment_metadata($file['attachment']);
				if(!empty($meta['width']) && !empty($meta['height']) && !array_intersect_key(wp_get_missing_image_subsizes($file['attachment']),$file['expected'])){$job['files'][$slot]['state']='ready';$job['files'][$slot]['error']='';$changed=true;continue;}
			}
			if($file['attempts']>=3 || ($file['started'] && time()>=$file['started']+180)){$job['files'][$slot]['state']='failed';$job['files'][$slot]['error']=cg_upload_job_error_text();$changed=true;}
			else{$pending=true;}
		}
	}
	if($pending){if($changed){cg_upload_job_save($job);}return;}
	foreach($job['files'] as $file){if($file['state']==='failed'){$failed++;} if($file['state']==='ready'){$ready++;}}
	if($failed && ($job['additional'] || $job['mode']==='replace' || !$ready)){$job['state']='failed';$job['message']=cg_upload_job_error_text();}
	else{$job['state']='ready';}
	cg_upload_job_save($job);
}
}

if(!function_exists('cg_upload_job_mail')){
function cg_upload_job_mail($to,$subject,$message,$headers = '',$attachments = array()){
	if(!cg_upload_job_phase('finalize')){return wp_mail($to,$subject,$message,$headers,$attachments);}
	$job=cg_upload_job_context();
	$job['mails'][]=array('to'=>$to,'subject'=>$subject,'message'=>$message,'headers'=>$headers,'attachments'=>$attachments,'state'=>'queued');
	cg_upload_job_save($job);
	return true;
}
}
if(!function_exists('cg_upload_job_send_mails')){
function cg_upload_job_send_mails(&$job){
	foreach($job['mails'] as $index=>$mail){
		if($mail['state']!=='queued'){continue;}
		$job['mails'][$index]['state']='sending'; cg_upload_job_save($job);
		$sent=wp_mail($mail['to'],$mail['subject'],$mail['message'],$mail['headers'],$mail['attachments']);
		$job['mails'][$index]['state']=$sent ? 'sent' : 'failed'; cg_upload_job_save($job);
	}
}
}
if(!function_exists('cg_upload_job_journal_path')){
function cg_upload_job_journal_path($job){
	$upload=wp_upload_dir();
	return $upload['basedir'].'/contest-gallery/gallery-id-'.$job['gid'].'/json/upload-job-'.$job['id'].'.json';
}
}
if(!function_exists('cg_upload_job_record_entry')){
function cg_upload_job_record_entry($id){
	if(!cg_upload_job_phase('finalize')){return;}
	$job=cg_upload_job_context();$path=cg_upload_job_journal_path($job);
	$ids=is_file($path) ? json_decode(file_get_contents($path),true) : array();
	$ids=is_array($ids) ? $ids : array();$ids[]=absint($id);
	// Files survive a database rollback. Record only IDs, never form data or tokens.
	if(!cg1l_write_atomic_file_payload($path,wp_json_encode(array_values(array_unique($ids))))){throw new Exception('The gallery entry could not be saved.');}
}
}
if(!function_exists('cg_upload_job_reconcile_journal')){
function cg_upload_job_reconcile_journal($job){
	global $wpdb;
	$path=cg_upload_job_journal_path($job);
	if(!is_file($path) || is_link($path)){return;}
	if($job['state']==='failed'){
		$ids=json_decode(file_get_contents($path),true);$changed=false;
		foreach((array)$ids as $id){
			$id=absint($id);if(!$id || $wpdb->get_var($wpdb->prepare('SELECT id FROM '.$wpdb->prefix.'contest_gal1ery WHERE id=%d',$id))){continue;}
			foreach(array('image-data','image-info','image-stats') as $kind){
				$file=dirname($path).'/'.$kind.'/'.$kind.'-'.$id.'.json';
				if(is_file($file) && !is_link($file)){wp_delete_file($file);}
			}
			cg1l_push_recent_id_file_all_types($job['gid'],$id,true);$changed=true;
		}
		if($changed){cg1l_create_last_updated_time_file_all($job['gid']);}
	}
	if(in_array($job['state'],array('complete','failed'),true)){wp_delete_file($path);}
}
}
if(!function_exists('cg_upload_job_publish_replace')){
function cg_upload_job_publish_replace(&$job){
	if($job['mode']!=='replace' || $job['state']!=='complete' || !empty($job['published'])){return;}
	if(empty($job['result']['active'])){cg_deactivate_images($job['gid'],wp_upload_dir(),array($job['pid']=>$job['pid']));}
	else{cg_entry_watermark_invalidate($job['gid'],$job['pid']);}
	$job['published']=true;cg_upload_job_save($job);
}
}
if(!function_exists('cg_upload_job_restore_replace_cache')){
function cg_upload_job_restore_replace_cache($job){
	global $wpdb;
	if($job['mode']!=='replace' || !array_key_exists('cacheBackup',$job) || $job['cacheBackup']===false){return;}
	$row=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery WHERE id=%d AND GalleryID=%d',$job['pid'],$job['gid']));
	if($row){cg_entry_page_clear_cache($row);}
	if(!$row || !hash_equals(cg_member_entry_revision($row),$job['replace']['revision'])){return;}
	$upload=wp_upload_dir();$path=$upload['basedir'].'/contest-gallery/gallery-id-'.$job['gid'].'/json/image-data/image-data-'.$job['pid'].'.json';
	cg1l_write_atomic_file_payload($path,$job['cacheBackup']);
	cg1l_push_recent_id_file_all_types($job['gid'],$job['pid']);cg1l_create_last_updated_time_file_all($job['gid']);
}
}
if(!function_exists('cg_upload_job_finish')){
function cg_upload_job_finish(&$job){
	global $wpdb;
	if($job['state']==='complete'){cg_upload_job_reconcile_journal($job);cg_upload_job_publish_replace($job);cg_upload_job_send_mails($job);return;}
	if($job['state']!=='ready'){return;}
	$originalPost=$_POST; $originalFiles=$_FILES;
	if($job['mode']==='replace'){
		$upload=wp_upload_dir();$path=$upload['basedir'].'/contest-gallery/gallery-id-'.$job['gid'].'/json/image-data/image-data-'.$job['pid'].'.json';
		$job['cacheBackup']=is_file($path) ? file_get_contents($path) : false;
	}
	$job['state']='finalizing'; cg_upload_job_save($job);
	$GLOBALS['cgUploadJobPhase']='finalize';
	$GLOBALS['cgUploadJobRollback']=false;
	$GLOBALS['cgUploadJobTransaction']=true;
	$wpdb->query('START TRANSACTION');
	ob_start(); $level=ob_get_level();
	try{
		$_POST=$job['post']; $_POST['cg_nonce']=$originalPost['cg_nonce'];
		if($job['mode']==='replace'){
			$row=cg_member_entry_action_context();
			if(is_wp_error($row)){throw new Exception($row->get_error_message());}
			$_FILES=array();
			$result=cg_member_replace_file($row,$job['replace']['slot'],$job['replace']['revision']);
			if(is_wp_error($result)){throw new Exception($result->get_error_message());}
			$job=cg_upload_job_context(); $job['result']=$result;
		}else{
			$_FILES=cg_upload_job_restore_files($job,true);
			include __DIR__.'/../../v10/v10-frontend/user_upload/users-upload-check.php';
			if(empty($GLOBALS['cgUploadJobFinalized'])){throw new Exception(cg_upload_job_form_error(ob_get_contents()));}
			$job=cg_upload_job_context(); $job['result']=ob_get_contents();
		}
		if(!empty($GLOBALS['cgUploadJobRollback'])){throw new Exception('The gallery entry could not be saved. Please try again.');}
		$job['state']='complete'; cg_upload_job_save($job);
		if($wpdb->query('COMMIT')===false){throw new Exception('The gallery entry could not be saved.');}
	}catch(Exception $error){
		$wpdb->query('ROLLBACK');
		$job=cg_upload_job_read($job['id']); $job['state']='failed'; $job['message']=$error->getMessage(); cg_upload_job_save($job);
		cg_upload_job_restore_replace_cache($job);
		cg_upload_job_reconcile_journal($job);
	}finally{
		while(ob_get_level()>=$level){ob_end_clean();}
		unset($GLOBALS['cgUploadJobTransaction'],$GLOBALS['cgUploadJobRollback'],$GLOBALS['cgUploadJobPhase']);
		$_POST=$originalPost; $_FILES=$originalFiles;
	}
	if($job['state']==='complete'){cg_upload_job_reconcile_journal($job);cg_upload_job_publish_replace($job);cg_upload_job_send_mails($job);}
}
}
if(!function_exists('cg_upload_job_release')){
function cg_upload_job_release(&$job){
	if(!empty($job['reservation'])){
		global $wpdb;
		$wpdb->query('START TRANSACTION');$GLOBALS['cgUploadJobTransaction']=true;$GLOBALS['cgUploadJobRollback']=false;
		try{
			if(!cg_paid_actions_settle_reservation($job['reservation'],0,array()) || !empty($GLOBALS['cgUploadJobRollback'])){throw new Exception('Upload credits could not be released.');}
			$job['reservation']=null;cg_upload_job_save($job);$wpdb->query('COMMIT');
		}catch(Exception $error){$wpdb->query('ROLLBACK');$job=cg_upload_job_read($job['id']);}
		finally{unset($GLOBALS['cgUploadJobTransaction'],$GLOBALS['cgUploadJobRollback']);}
	}
}
}
if(!function_exists('cg_upload_job_shutdown')){
function cg_upload_job_shutdown(){
	unset($GLOBALS['cgUploadJobEmergencyMemory']);
	if(empty($GLOBALS['cgUploadJobActive'])){return;}
	global $wpdb;
	$id=$GLOBALS['cgUploadJobActive'];
	if(!empty($GLOBALS['cgUploadJobTransaction'])){$wpdb->query('ROLLBACK');unset($GLOBALS['cgUploadJobTransaction']);}
	$job=cg_upload_job_read($id);
	if(!$job){return;}
	if($job['state']==='complete'){cg_upload_job_unlock($id);return;}
	$phase=isset($GLOBALS['cgUploadJobPhase']) ? $GLOBALS['cgUploadJobPhase'] : '';
	if($phase==='receive' || $phase==='finalize'){
		$job['state']='failed';
		$job['message']=$phase==='receive' ? 'The file or form could not be accepted. Please check the upload requirements and try again.' : 'The gallery update was interrupted. Please contact the gallery administrator.';
		try{cg_upload_job_save($job);cg_upload_job_restore_replace_cache($job);cg_upload_job_reconcile_journal($job);cg_upload_job_release($job);}catch(Exception $ignored){}
	}
	// Image workers keep their durable checkpoints. A later request may retry them.
	cg_upload_job_unlock($id);
}
}
if(!function_exists('cg_upload_job_public')){
function cg_upload_job_public($job,$running = false){
	$files=array(); $ready=0; $expired=false;
	foreach($job['files'] as $slot=>$file){
		if($file['state']==='ready' || $file['state']==='failed'){$ready++;}
		if($file['state']==='processing' && $file['started'] && time()>=$file['started']+180){$expired=true;}
		$files[]=array('slot'=>(string)$slot,'name'=>$file['name'],'state'=>$file['state'],'attempt'=>$file['attempts'],'deadline'=>$file['started'] ? $file['started']+180 : 0,'error'=>$file['error']);
	}
	return array('id'=>$job['id'],'token'=>cg_upload_job_token($job),'revision'=>isset($job['revision']) ? $job['revision'] : 0,'serverTime'=>time(),'state'=>$job['state'],'running'=>$running,'waiting'=>$expired && $running,'files'=>$files,'percent'=>$job['state']==='complete' ? 100 : ($job['state']==='receiving' ? 0 : 90+(count($files) ? floor(9*$ready/count($files)) : 0)),'message'=>$job['message'],'result'=>$job['state']==='complete' ? $job['result'] : null);
}
}
if(!function_exists('cg_upload_job_ajax')){
function cg_upload_job_ajax(){
	nocache_headers(); cg1l_ajax_reject_bad_origin(); cg_check_frontend_nonce();
	$op=isset($_POST['operation']) ? sanitize_key($_POST['operation']) : '';
	$id=isset($_POST['jobId']) && is_string($_POST['jobId']) ? $_POST['jobId'] : '';
	$locked=false; $capacity=false; $authorized=false; $buffer=ob_get_level();
	try{
		if($op==='prepare'){
			$job=cg_upload_job_prepare();
			if(isset($job['transport']) && $job['transport']==='classic'){wp_send_json_success($job);}
		}
		else{
			$job=cg_upload_job_read($id); cg_upload_job_assert_authorized($job); $authorized=true;
			$GLOBALS['cgUploadJob']=$job;
			if($op!=='status'){
				cg_upload_job_storage_ready();
				$locked=cg_upload_job_lock($id);
				if(!$locked){wp_send_json_success(cg_upload_job_public($job,true));}
				$job=cg_upload_job_read($id); $GLOBALS['cgUploadJob']=$job;
				$GLOBALS['cgUploadJobActive']=$id;
				$GLOBALS['cgUploadJobEmergencyMemory']=str_repeat('x',262144);
				register_shutdown_function('cg_upload_job_shutdown');
			}
			if($op==='receive' && $job['state']==='receiving'){
				if((int)(isset($_POST['GalleryID']) ? $_POST['GalleryID'] : (isset($_POST['gid']) ? $_POST['gid'] : 0))!==$job['gid'] || ($job['mode']==='replace' && absint($_POST['pid'])!==$job['pid'])){throw new Exception('Upload authorization failed.');}
				if(!empty($job['files'])){throw new Exception('The previous file transfer was interrupted. Please start a new upload.');}
				$capacity='capacity:'.$job['gid'];
				if(!cg_upload_job_lock($capacity)){throw new Exception('Another upload is being accepted. Please try again.');}
				// Member admission already holds its own per-user lock. Keep its rate counter
				// committed even when subsequent file validation rejects this submission.
				if($job['mode']==='gallery'){
					global $wpdb; $wpdb->query('START TRANSACTION'); $GLOBALS['cgUploadJobTransaction']=true; $GLOBALS['cgUploadJobRollback']=false;
				}
				require_once ABSPATH.'wp-admin/includes/image.php'; require_once ABSPATH.'wp-admin/includes/file.php'; require_once ABSPATH.'wp-admin/includes/media.php';
				$GLOBALS['cgUploadJobPhase']='receive'; $GLOBALS['cgUploadJobOriginalPost']=$_POST;
				ob_start();
				if($job['mode']==='replace'){
					$row=cg_member_entry_action_context(); if(is_wp_error($row)){throw new Exception($row->get_error_message());}
					$result=cg_member_replace_file($row,isset($_POST['slot']) ? sanitize_text_field(wp_unslash($_POST['slot'])) : '',isset($_POST['revision']) ? sanitize_text_field(wp_unslash($_POST['revision'])) : '');
					if(is_wp_error($result)){throw new Exception($result->get_error_message());}
				}else{include __DIR__.'/../../v10/v10-frontend/user_upload/users-upload-check.php';}
				if(empty($GLOBALS['cgUploadJobAccepted'])){throw new Exception(cg_upload_job_form_error(ob_get_contents()));}
				ob_end_clean(); $job=cg_upload_job_context();
			}elseif($op==='process' && $job['state']==='processing'){
				$GLOBALS['cgUploadJobPhase']='process';
				cg_upload_job_process($job);
			}elseif($op==='finish'){
				$capacity='capacity:'.$job['gid'];
				if(!cg_upload_job_lock($capacity)){throw new Exception('Another upload is being completed. Please try again.');}
				cg_upload_job_finish($job);
			}
			elseif($op!=='status' && $op!=='receive' && $op!=='process'){throw new Exception('Unknown upload operation.');}
			if($op==='status' && cg_upload_job_lock($id)){
				// Re-read after taking the lock. A worker may have just committed.
				try{
					$job=cg_upload_job_read($id); $GLOBALS['cgUploadJob']=$job;
					if($job['state']==='processing'){cg_upload_job_processing_state($job);}
					if($job['state']==='finalizing' || ($job['state']==='receiving' && $job['admitted'])){
						$job['message']='The upload was interrupted. Please contact the gallery administrator.'; $job['state']='failed';cg_upload_job_save($job);cg_upload_job_restore_replace_cache($job);
					}
					if($job['state']==='failed'){cg_upload_job_reconcile_journal($job);cg_upload_job_release($job);}
					if($job['state']==='complete'){cg_upload_job_reconcile_journal($job);cg_upload_job_publish_replace($job);cg_upload_job_send_mails($job);}
				}finally{cg_upload_job_unlock($id);}
			}
			if($job['state']==='failed' && $locked){cg_upload_job_release($job);}
		}
		while(ob_get_level()>$buffer){ob_end_clean();}
		unset($GLOBALS['cgUploadJobActive']);
		if($capacity){cg_upload_job_unlock($capacity);}
		if($locked){cg_upload_job_unlock($id);$locked=false;}
		wp_send_json_success(cg_upload_job_public($job,cg_upload_job_running($job['id'])));
	}catch(Exception $error){
		while(ob_get_level()>$buffer){ob_end_clean();}
		if(!empty($GLOBALS['cgUploadJobTransaction'])){global $wpdb;$wpdb->query('ROLLBACK');unset($GLOBALS['cgUploadJobTransaction']);}
		if($locked && isset($job) && $op==='receive'){$job=cg_upload_job_read($id);$job['state']='failed';$job['message']=$error->getMessage();cg_upload_job_save($job);cg_upload_job_release($job);}
		unset($GLOBALS['cgUploadJobActive']);
		if($capacity){cg_upload_job_unlock($capacity);}
		if($locked){cg_upload_job_unlock($id);}
		$committed=$id ? cg_upload_job_read($id) : false;
		if($authorized && $committed && $committed['state']==='complete'){
			// A post-commit mail/cache error must never invite a second upload.
			wp_send_json_success(cg_upload_job_public($committed,false));
		}
		$failure = array('message'=>$error->getMessage());
		if(!empty($GLOBALS['cgUploadCategoryRejection'])){$failure = $GLOBALS['cgUploadCategoryRejection'];}
		wp_send_json_error($failure);
	}
}
}
add_action('wp_ajax_cg_upload_job','cg_upload_job_ajax');
add_action('wp_ajax_nopriv_cg_upload_job','cg_upload_job_ajax');

if(!function_exists('cg_upload_job_strings')){
function cg_upload_job_strings(){
	$strings=array();
	foreach(array('Processing…','Continuing image processing…','Check status','Upload completed.','Refresh','Please try again.','The connection failed. Please try again.') as $text){$strings[$text]=cg_member_t($text);}
	return $strings;
}
}
if(!function_exists('cg_upload_job_attachment_referenced')){
function cg_upload_job_attachment_referenced($id,$jobDirectory = true){
	global $wpdb;
	if($wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery WHERE WpUpload=%d OR PdfPreview=%d OR MultipleFiles LIKE %s LIMIT 1",$id,$id,'%'.$wpdb->esc_like('i:'.$id.';').'%'))){return true;}
	if($wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE post_id<>%d AND (meta_value=%s OR meta_value LIKE %s) LIMIT 1",$id,(string)$id,'%'.$wpdb->esc_like('i:'.$id.';').'%'))){return true;}
	if($wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value=%s OR meta_value LIKE %s LIMIT 1",(string)$id,'%'.$wpdb->esc_like('i:'.$id.';').'%'))){return true;}
	$post=get_post($id);
	if(!$post || $post->post_parent){return true;}
	$url=wp_get_attachment_url($id);
	// Each attachment has its own job directory, including every thumbnail URL.
	$prefix=$url ? ($jobDirectory ? trailingslashit(dirname($url)) : $url) : '';
	if($prefix && $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_value LIKE %s LIMIT 1",$id,'%'.$wpdb->esc_like($prefix).'%'))){return true;}
	return $prefix && $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE ID<>%d AND (post_content LIKE %s OR post_content LIKE %s) LIMIT 1",$id,'%'.$wpdb->esc_like($prefix).'%','%wp-image-'.$id.'%'));
}
}
if(!function_exists('cg_upload_job_cleanup_valid')){
function cg_upload_job_cleanup_valid($job,$id){
	if(!is_array($job) || !isset($job['id'],$job['gid'],$job['user'],$job['state'],$job['files'],$job['created'],$job['expires'])){return false;}
	if($job['id']!==$id || !preg_match('/^[a-f0-9]{64}$/D',$id) || !is_string($job['state']) || !is_array($job['files'])){return false;}
	foreach(array('gid','user','created','expires') as $key){
		if(!is_scalar($job[$key]) || !ctype_digit((string)$job[$key])){return false;}
	}
	if(!$job['gid'] || !$job['created'] || !$job['expires']){return false;}
	foreach($job['files'] as $file){
		if(!is_array($file) || !isset($file['attachment']) || !is_scalar($file['attachment']) || !ctype_digit((string)$file['attachment'])){return false;}
	}
	return true;
}
}
if(!function_exists('cg_upload_jobs_cleanup')){
function cg_upload_jobs_cleanup(){
	global $wpdb;
	$rows=$wpdb->get_results($wpdb->prepare("SELECT option_name,option_value FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id ASC LIMIT 100",$wpdb->esc_like('_cg_upload_job_').'%'));
	$cleaned=0;
	foreach($rows as $row){
		$jobId=substr($row->option_name,strlen(cg_upload_job_key('')));
		$job=json_decode($row->option_value,true);
		if(!cg_upload_job_cleanup_valid($job,$jobId) || $job['expires']>=time() || !cg_upload_job_lock($jobId)){continue;}
		try{
			// Another cleanup may have removed the job after the initial SELECT.
			$job=cg_upload_job_read($jobId);
			if(!cg_upload_job_cleanup_valid($job,$jobId) || $job['expires']>=time()){continue;}
			if($job['state']!=='complete'){$job['state']='failed';}
			cg_upload_job_reconcile_journal($job);
			cg_upload_job_release($job);
			if(!cg_upload_job_cleanup_valid($job,$jobId) || !empty($job['reservation'])){continue;}
			// Recover an attachment created just before the worker lost its response/checkpoint.
			$ids=$wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_author=%d AND guid LIKE %s",$job['user'],'%/cg-'.$wpdb->esc_like($job['id']).'-%'));
			foreach($ids as $id){if(!cg_upload_job_attachment_referenced($id)){wp_delete_attachment($id,true);}}
			foreach($job['files'] as $file){
				$id=absint($file['attachment']);
				if($id && get_post_meta($id,'_cg_upload_job',true)===$job['id'] && !cg_upload_job_attachment_referenced($id)){wp_delete_attachment($id,true);}
			}
			$upload=wp_upload_dir();
			foreach($job['files'] as $slot=>$file){
				$relative='/'.gmdate('Y/m',$job['created']).'/cg-'.$job['id'].'-'.preg_replace('/[^a-zA-Z0-9_-]/','',(string)$slot);
				$folder=$upload['basedir'].$relative;
				if(!is_dir($folder) || is_link($folder) || $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE guid LIKE %s OR post_content LIKE %s LIMIT 1",'%'.$wpdb->esc_like($relative).'/%','%'.$wpdb->esc_like($relative).'/%'))){continue;}
				foreach(scandir($folder) as $name){if($name!=='.' && $name!=='..' && is_file($folder.'/'.$name) && !is_link($folder.'/'.$name)){wp_delete_file($folder.'/'.$name);}}
				@rmdir($folder);
			}
			delete_option(cg_upload_job_key($job['id']));
			$cleaned++;if($cleaned>=100){break;}
		}finally{cg_upload_job_unlock($jobId);}
	}
}
}
add_action('cg_upload_jobs_cleanup','cg_upload_jobs_cleanup');
