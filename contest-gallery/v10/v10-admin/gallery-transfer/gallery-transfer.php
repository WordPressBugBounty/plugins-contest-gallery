<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_gallery_transfer_require_manage_options')){
	function cg_gallery_transfer_require_manage_options(){
		if(!defined('DOING_AJAX') || !DOING_AJAX){
			cg_backend_ajax_error_json('Invalid AJAX request.',400,'cg_gallery_transfer_invalid_ajax');
		}
		if(!current_user_can('manage_options')){
			cg_backend_ajax_error_json('Gallery export/import requires administrator rights.',403,'cg_gallery_transfer_missing_rights');
		}
		$cg_nonce = '';
		if(isset($_POST['cg_nonce'])){
			$cg_nonce = sanitize_text_field($_POST['cg_nonce']);
		}elseif(isset($_GET['cg_nonce'])){
			$cg_nonce = sanitize_text_field($_GET['cg_nonce']);
		}
		if(empty($cg_nonce) || !wp_verify_nonce($cg_nonce,'cg_nonce')){
			wp_send_json_error(array(
				'message' => 'WP nonce security token not set or not valid anymore.',
				'code' => 'cg_nonce_invalid',
				'version' => cg_get_version()
			),403);
		}
	}
}

if(!function_exists('cg_gallery_transfer_base_dir')){
	function cg_gallery_transfer_base_dir(){
		$wp_upload_dir = wp_upload_dir();
		$dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-transfer';
		if(!is_dir($dir)){
			wp_mkdir_p($dir);
		}
		return $dir;
	}
}

if(!function_exists('cg_gallery_transfer_jobs_dir')){
	function cg_gallery_transfer_jobs_dir(){
		$dir = cg_gallery_transfer_base_dir().'/jobs';
		if(!is_dir($dir)){
			wp_mkdir_p($dir);
		}
		return $dir;
	}
}

if(!function_exists('cg_gallery_transfer_job_id')){
	function cg_gallery_transfer_job_id(){
		return time().'-'.wp_generate_password(16,false,false);
	}
}

if(!function_exists('cg_gallery_transfer_job_dir')){
	function cg_gallery_transfer_job_dir($job_id){
		$job_id = preg_replace('/[^a-zA-Z0-9_-]/','',(string)$job_id);
		if($job_id===''){
			return '';
		}
		return cg_gallery_transfer_jobs_dir().'/'.$job_id;
	}
}

if(!function_exists('cg_gallery_transfer_state_file')){
	function cg_gallery_transfer_state_file($job_id){
		$dir = cg_gallery_transfer_job_dir($job_id);
		if(empty($dir)){
			return '';
		}
		return $dir.'/state.json';
	}
}

if(!function_exists('cg_gallery_transfer_write_json_file')){
	function cg_gallery_transfer_write_json_file($file,$data){
		$dir = dirname($file);
		if(!is_dir($dir)){
			wp_mkdir_p($dir);
		}
		$json = wp_json_encode($data);
		if($json===false){
			return false;
		}
		return file_put_contents($file,$json) !== false;
	}
}

if(!function_exists('cg_gallery_transfer_read_json_file')){
	function cg_gallery_transfer_read_json_file($file){
		if(empty($file) || !file_exists($file)){
			return false;
		}
		$data = json_decode(file_get_contents($file),true);
		if(!is_array($data)){
			return false;
		}
		return $data;
	}
}

if(!function_exists('cg_gallery_transfer_load_state')){
	function cg_gallery_transfer_load_state($job_id){
		return cg_gallery_transfer_read_json_file(cg_gallery_transfer_state_file($job_id));
	}
}

if(!function_exists('cg_gallery_transfer_save_state')){
	function cg_gallery_transfer_save_state($state){
		if(empty($state['job_id'])){
			return false;
		}
		return cg_gallery_transfer_write_json_file(cg_gallery_transfer_state_file($state['job_id']),$state);
	}
}

if(!function_exists('cg_gallery_transfer_delete_dir')){
	function cg_gallery_transfer_delete_dir($dir){
		if(empty($dir) || !is_dir($dir)){
			return;
		}
		$items = scandir($dir);
		foreach($items as $item){
			if($item==='.' || $item==='..'){
				continue;
			}
			$path = $dir.'/'.$item;
			if(is_dir($path)){
				cg_gallery_transfer_delete_dir($path);
			}else{
				@unlink($path);
			}
		}
		@rmdir($dir);
	}
}

if(!function_exists('cg_gallery_transfer_cleanup_stale_jobs')){
	function cg_gallery_transfer_cleanup_stale_jobs($max_age_seconds=86400){
		$max_age_seconds = absint($max_age_seconds);
		if(empty($max_age_seconds)){
			$max_age_seconds = 86400;
		}
		$jobs_dir = cg_gallery_transfer_jobs_dir();
		if(empty($jobs_dir) || !is_dir($jobs_dir) || !is_readable($jobs_dir)){
			return 0;
		}
		$jobs_dir_real = realpath($jobs_dir);
		if(empty($jobs_dir_real)){
			return 0;
		}
		$deleted = 0;
		$now = time();
		$items = scandir($jobs_dir);
		if(!is_array($items)){
			return 0;
		}
		foreach($items as $item){
			if($item==='.' || $item==='..'){
				continue;
			}
			if(!preg_match('/^[0-9]{9,}-[a-zA-Z0-9_-]+$/',$item)){
				continue;
			}
			$job_dir = $jobs_dir.'/'.$item;
			if(!is_dir($job_dir)){
				continue;
			}
			$job_dir_real = realpath($job_dir);
			if(empty($job_dir_real) || dirname($job_dir_real)!==$jobs_dir_real){
				continue;
			}
			$dash_position = strpos($item,'-');
			$last_activity = ($dash_position!==false) ? intval(substr($item,0,$dash_position)) : 0;
			$dir_mtime = @filemtime($job_dir_real);
			if(!empty($dir_mtime)){
				$last_activity = max($last_activity,intval($dir_mtime));
			}
			$state_mtime = @filemtime($job_dir_real.'/state.json');
			if(!empty($state_mtime)){
				$last_activity = max($last_activity,intval($state_mtime));
			}
			if(empty($last_activity) || ($now-$last_activity)<$max_age_seconds){
				continue;
			}
			cg_gallery_transfer_delete_dir($job_dir_real);
			$deleted++;
		}
		return $deleted;
	}
}

add_action('cg_gallery_transfer_cleanup_stale_jobs_event','cg_gallery_transfer_cleanup_stale_jobs_event');
if(!function_exists('cg_gallery_transfer_cleanup_stale_jobs_event')){
	function cg_gallery_transfer_cleanup_stale_jobs_event(){
		cg_gallery_transfer_cleanup_stale_jobs();
	}
}

add_action('init','cg_gallery_transfer_schedule_cleanup_event');
if(!function_exists('cg_gallery_transfer_schedule_cleanup_event')){
	function cg_gallery_transfer_schedule_cleanup_event(){
		if(!wp_next_scheduled('cg_gallery_transfer_cleanup_stale_jobs_event')){
			wp_schedule_event(time()+3600,'daily','cg_gallery_transfer_cleanup_stale_jobs_event');
		}
	}
}

if(!function_exists('cg_gallery_transfer_table_columns')){
	function cg_gallery_transfer_table_columns($table){
		static $cache = array();
		if(isset($cache[$table])){
			return $cache[$table];
		}
		global $wpdb;
		$rows = $wpdb->get_results("SHOW COLUMNS FROM $table");
		$columns = array();
		foreach($rows as $row){
			$columns[$row->Field] = true;
		}
		$cache[$table] = $columns;
		return $columns;
	}
}

if(!function_exists('cg_gallery_transfer_table_exists')){
	function cg_gallery_transfer_table_exists($table){
		global $wpdb;
		$table_like = $wpdb->esc_like($table);
		$existing = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',array($table_like)));
		return $existing === $table;
	}
}

if(!function_exists('cg_gallery_transfer_create_mails_table_if_required')){
	function cg_gallery_transfer_create_mails_table_if_required(){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_mails';
		if(cg_gallery_transfer_table_exists($table)){
			return;
		}
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $table (
		id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		pid INT(20) NOT NULL DEFAULT 0,
		GalleryID INT(11) NOT NULL DEFAULT 0,
		WpUserId INT(11) NOT NULL DEFAULT 0,
		ReceiverMail VARCHAR(256) NOT NULL DEFAULT '',
		FromName VARCHAR(256) NOT NULL DEFAULT '',
		FromMail VARCHAR(256) NOT NULL DEFAULT '',
		ReplyName VARCHAR(256) NOT NULL DEFAULT '',
		ReplyMail VARCHAR(256) NOT NULL DEFAULT '',
		Cc TEXT NOT NULL,
		Bcc TEXT NOT NULL,
		Subject VARCHAR(1000) NOT NULL DEFAULT '',
		Body TEXT NOT NULL,
		MailType VARCHAR(100) NOT NULL DEFAULT '',
		Tstamp INT(11) NOT NULL DEFAULT 0,
        KEY pid (pid),
        KEY WpUserId (WpUserId),
        KEY ReceiverMail (ReceiverMail),
        KEY MailType (MailType),
        KEY Tstamp (Tstamp),
        KEY GalleryID (GalleryID)
		) $charset_collate;";
		require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

if(!function_exists('cg_gallery_transfer_filter_insert_row')){
	function cg_gallery_transfer_filter_insert_row($table,$row,$overrides,$skip_id){
		$columns = cg_gallery_transfer_table_columns($table);
		$data = array();
		foreach($row as $key => $value){
			if($skip_id && $key==='id'){
				continue;
			}
			if(isset($columns[$key])){
				$data[$key] = $value;
			}
		}
		foreach($overrides as $key => $value){
			if(isset($columns[$key])){
				$data[$key] = $value;
			}
		}
		return $data;
	}
}

if(!function_exists('cg_gallery_transfer_insert_row')){
	function cg_gallery_transfer_insert_row($table,$row,$overrides=array(),$skip_id=true){
		global $wpdb;
		$data = cg_gallery_transfer_filter_insert_row($table,$row,$overrides,$skip_id);
		if(empty($data)){
			return 0;
		}
		$formats = array();
		foreach($data as $value){
			$formats[] = '%s';
		}
		$result = $wpdb->insert($table,$data,$formats);
		if($result===false){
			return 0;
		}
		return intval($wpdb->insert_id);
	}
}

if(!function_exists('cg_gallery_transfer_get_rows')){
	function cg_gallery_transfer_get_rows($table,$where_sql,$params=array()){
		global $wpdb;
		if(!empty($params)){
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE $where_sql",$params),ARRAY_A);
		}
		return $wpdb->get_results("SELECT * FROM $table WHERE $where_sql",ARRAY_A);
	}
}

if(!function_exists('cg_gallery_transfer_is_new_slug_gallery')){
	function cg_gallery_transfer_is_new_slug_gallery($gallery_id){
		global $wpdb;
		$gallery_id = absint($gallery_id);
		if(empty($gallery_id)){
			return false;
		}
		$table_options = $wpdb->prefix.'contest_gal1ery_options';
		$options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_options WHERE id = %d",array($gallery_id)));
		if(empty($options) || empty($options->WpPageParent)){
			return false;
		}
		$required = array(
			'WpPageParent' => 'contest-g',
			'WpPageParentUser' => 'contest-g-user',
			'WpPageParentNoVoting' => 'contest-g-no-voting',
			'WpPageParentWinner' => 'contest-g-winner',
		);
		foreach($required as $field => $post_type){
			if(empty($options->$field)){
				return false;
			}
			$post = get_post($options->$field);
			if(empty($post) || $post->post_type!==$post_type){
				return false;
			}
		}
		if(!empty($options->WpPageParentEcommerce)){
			$post = get_post($options->WpPageParentEcommerce);
			if(empty($post) || $post->post_type!=='contest-g-ecommerce'){
				return false;
			}
		}
		return true;
	}
}

if(!function_exists('cg_gallery_transfer_export_button_html')){
	function cg_gallery_transfer_export_button_html($gallery_id){
		$gallery_id = absint($gallery_id);
		$is_allowed = cg_gallery_transfer_is_new_slug_gallery($gallery_id);
		if($is_allowed){
			return '<input class="cg_backend_button cg_gallery_transfer_export_button" type="button" value="Export gallery ZIP" data-cg-gallery-id="'.$gallery_id.'" />';
		}
		$title = 'Export not available yet';
		$message = 'Gallery ZIP export requires a gallery page with the new /contest-galleries/ URL structure. Copy this gallery first. The copied gallery gets the new URL structure and can then be exported.';
		return '<input class="cg_backend_button cg_gallery_transfer_export_unavailable_button" type="button" value="'.esc_attr($title).'" data-cg-gallery-id="'.$gallery_id.'" data-cg-title="'.esc_attr($title).'" data-cg-message="'.esc_attr($message).'" title="'.esc_attr($message).'" />';
	}
}

if(!function_exists('cg_gallery_transfer_import_box_html')){
	function cg_gallery_transfer_import_box_html(){
		return '<div id="cgGalleryTransferContainer">'
			.'<div class="cg-gallery-transfer-action cg-gallery-transfer-import cg_gallery_backend_action_block cg_gallery_backend_action_block_zip">'
				.'<div class="cg-gallery-transfer-title">Import gallery ZIP</div>'
				.'<form id="cgGalleryTransferImportForm" class="cg_gallery_backend_action_form" enctype="multipart/form-data">'
					.'<input type="file" id="cgGalleryTransferImportFile" name="cg_gallery_transfer_zip" accept=".zip" />'
					.'<input class="cg_backend_button cg_backend_button_gallery_action" type="submit" value="Upload ZIP" />'
				.'</form>'
			.'</div>'
			.'<div id="cgGalleryTransferProgress" class="cg_hide"></div>'
			.'<div id="cgGalleryTransferImportPreview" class="cg_hide"></div>'
			.'</div>';
	}
}

if(!function_exists('cg_gallery_transfer_export_modal_html')){
	function cg_gallery_transfer_export_modal_html(){
		return '<div id="cgGalleryTransferExportModalContainer" class="cg_backend_action_container cg_hide cg_gallery_transfer_export_modal" data-cg-job-id="">'
			.'<span class="cg_message_close cg_gallery_transfer_export_close"></span>'
			.'<div id="cgGalleryTransferExportModal" class="cg-gallery-transfer-export-modal">'
				.'<div class="cg-gallery-transfer-export-kicker">Gallery ZIP export</div>'
				.'<h2 id="cgGalleryTransferExportHeadline">Preparing export</h2>'
				.'<p id="cgGalleryTransferExportText">The export job is being prepared.</p>'
				.'<div class="cg-gallery-transfer-export-progressbar"><span id="cgGalleryTransferExportProgressBar"></span></div>'
				.'<div id="cgGalleryTransferExportMeta" class="cg-gallery-transfer-export-meta">'
					.'<span>Progress: <strong id="cgGalleryTransferExportPercent">0%</strong></span>'
					.'<span>Entries: <strong id="cgGalleryTransferExportEntries">0/0</strong></span>'
					.'<span id="cgGalleryTransferExportSkippedWrap" class="cg_hide">Skipped sale entries: <strong id="cgGalleryTransferExportSkipped">0</strong></span>'
				.'</div>'
				.'<div id="cgGalleryTransferExportWarnings" class="cg-gallery-transfer-export-result cg-gallery-transfer-export-result-warning cg_hide"></div>'
				.'<div id="cgGalleryTransferExportError" class="cg-gallery-transfer-export-result cg-gallery-transfer-export-result-error cg_hide"></div>'
				.'<div id="cgGalleryTransferExportDownloadInfo" class="cg-gallery-transfer-export-download-info cg_hide">The temporary ZIP will be deleted after the download request starts.</div>'
				.'<div class="cg-gallery-transfer-export-actions">'
					.'<button type="button" id="cgGalleryTransferExportCancel" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-ghost">Cancel export</button>'
					.'<a id="cgGalleryTransferExportDownload" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-primary cg_hide" href="#">Download ZIP</a>'
					.'<button type="button" id="cgGalleryTransferExportCloseDelete" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-ghost cg_hide">Close and delete ZIP</button>'
				.'</div>'
			.'</div>'
			.'</div>';
	}
}

if(!function_exists('cg_gallery_transfer_import_modal_html')){
	function cg_gallery_transfer_import_modal_html(){
		return '<div id="cgGalleryTransferImportModalContainer" class="cg_backend_action_container cg_hide cg_gallery_transfer_import_modal" data-cg-job-id="">'
			.'<span class="cg_message_close cg_gallery_transfer_import_close"></span>'
			.'<div id="cgGalleryTransferImportModal" class="cg-gallery-transfer-export-modal cg-gallery-transfer-import-modal">'
				.'<div class="cg-gallery-transfer-export-kicker">Gallery ZIP import</div>'
				.'<h2 id="cgGalleryTransferImportHeadline">Preparing import</h2>'
				.'<p id="cgGalleryTransferImportText">The ZIP file is being prepared.</p>'
				.'<div id="cgGalleryTransferImportProgressArea" class="cg-gallery-transfer-import-progress-area">'
					.'<div class="cg-gallery-transfer-export-progressbar"><span id="cgGalleryTransferImportProgressBar"></span></div>'
					.'<div id="cgGalleryTransferImportMeta" class="cg-gallery-transfer-export-meta">'
						.'<span>Progress: <strong id="cgGalleryTransferImportPercent">0%</strong></span>'
						.'<span>Entries: <strong id="cgGalleryTransferImportEntries">0/0</strong></span>'
						.'<span id="cgGalleryTransferImportNewGalleryWrap" class="cg_hide">New gallery: <strong id="cgGalleryTransferImportNewGallery">0</strong></span>'
					.'</div>'
				.'</div>'
				.'<div id="cgGalleryTransferImportPackage" class="cg-gallery-transfer-import-panel cg_hide">'
					.'<div class="cg-gallery-transfer-import-panel-title">Package</div>'
					.'<div class="cg-gallery-transfer-import-package-summary">Gallery <strong id="cgGalleryTransferImportPackageGallery">0</strong> from <strong id="cgGalleryTransferImportPackageDomain"></strong></div>'
					.'<div class="cg-gallery-transfer-import-counts">'
						.'<span>Entries <strong id="cgGalleryTransferImportPackageEntries">0</strong></span>'
						.'<span>Votes <strong id="cgGalleryTransferImportPackageVotes">0</strong></span>'
						.'<span>Comments <strong id="cgGalleryTransferImportPackageComments">0</strong></span>'
						.'<span>Media files <strong id="cgGalleryTransferImportPackageMedia">0</strong></span>'
						.'<span>Skipped sale entries <strong id="cgGalleryTransferImportPackageSkipped">0</strong></span>'
						.'<span>Source users <strong id="cgGalleryTransferImportPackageUsers">0</strong></span>'
					.'</div>'
				.'</div>'
				.'<fieldset id="cgGalleryTransferImportMapping" class="cg-gallery-transfer-import-panel cg-gallery-transfer-import-mapping cg_hide">'
					.'<legend>User assignment</legend>'
					.'<label class="cg-gallery-transfer-import-option" for="cgGalleryTransferUserModeNone">'
						.'<input type="radio" id="cgGalleryTransferUserModeNone" name="cg_gallery_transfer_user_mode" value="none" checked>'
						.'<span><b>No user assignment</b><em>Imported entries, votes and comments will not be assigned to WordPress users.</em></span>'
					.'</label>'
					.'<label class="cg-gallery-transfer-import-option" for="cgGalleryTransferUserModeSingle">'
						.'<input type="radio" id="cgGalleryTransferUserModeSingle" name="cg_gallery_transfer_user_mode" value="single">'
						.'<span><b>Assign entries to one user</b><em>Entry owners are mapped to the selected user.</em></span>'
					.'</label>'
					.'<label id="cgGalleryTransferSingleUserWrap" class="cg-gallery-transfer-import-select-wrap" for="cgGalleryTransferSingleUser">'
						.'<span>Target user</span>'
						.'<select id="cgGalleryTransferSingleUser"></select>'
					.'</label>'
					.'<label class="cg-gallery-transfer-import-option" for="cgGalleryTransferUserModeEmail">'
						.'<input type="radio" id="cgGalleryTransferUserModeEmail" name="cg_gallery_transfer_user_mode" value="email">'
						.'<span><b>Match users by email</b><em>Entries, comments and votes are mapped when the same email exists on this site.</em></span>'
					.'</label>'
				.'</fieldset>'
				.'<input type="hidden" id="cgGalleryTransferImportJobId" value="">'
				.'<div id="cgGalleryTransferImportWarnings" class="cg-gallery-transfer-export-result cg-gallery-transfer-export-result-warning cg_hide"></div>'
				.'<div id="cgGalleryTransferImportError" class="cg-gallery-transfer-export-result cg-gallery-transfer-export-result-error cg_hide"></div>'
				.'<div class="cg-gallery-transfer-export-actions">'
					.'<button type="button" id="cgGalleryTransferImportCloseDelete" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-ghost cg_hide">Cancel import</button>'
					.'<button type="button" id="cgGalleryTransferImportStart" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-primary cg_hide">Start import</button>'
					.'<a id="cgGalleryTransferImportOpenGallery" class="cg_backend_button cg-gallery-transfer-export-button cg-gallery-transfer-export-button-primary cg_hide" href="#">Open imported gallery</a>'
				.'</div>'
			.'</div>'
			.'</div>';
	}
}

if(!function_exists('cg_gallery_transfer_safe_unserialize')){
	function cg_gallery_transfer_safe_unserialize($value){
		if(empty($value) || $value==='""'){
			return array();
		}
		$data = @unserialize($value);
		return (is_array($data)) ? $data : array();
	}
}

if(!function_exists('cg_gallery_transfer_add_user_ref')){
	function cg_gallery_transfer_add_user_ref(&$state,$wp_user_id){
		$wp_user_id = absint($wp_user_id);
		if(empty($wp_user_id) || !empty($state['user_refs'][$wp_user_id])){
			return;
		}
		$user = get_user_by('id',$wp_user_id);
		if(empty($user)){
			return;
		}
		$state['user_refs'][$wp_user_id] = array(
			'source_wp_user_id' => $wp_user_id,
			'user_email' => $user->user_email,
			'user_login' => $user->user_login,
			'display_name' => $user->display_name,
		);
	}
}

if(!function_exists('cg_gallery_transfer_media_basename')){
	function cg_gallery_transfer_media_basename($wp_upload_id,$file){
		$name = basename($file);
		if($name===''){
			$name = 'attachment-'.$wp_upload_id;
		}
		return $wp_upload_id.'-'.sanitize_file_name($name);
	}
}

if(!function_exists('cg_gallery_transfer_add_media_ref')){
	function cg_gallery_transfer_add_media_ref(&$state,$wp_upload_id){
		$wp_upload_id = absint($wp_upload_id);
		if(empty($wp_upload_id) || !empty($state['data']['media'][$wp_upload_id])){
			return true;
		}
		$file = get_attached_file($wp_upload_id);
		if(empty($file) || !file_exists($file) || !is_readable($file)){
			$state['warnings'][] = 'Media file missing for attachment ID '.$wp_upload_id;
			return false;
		}
		$media_dir = $state['job_dir'].'/media';
		if(!is_dir($media_dir)){
			wp_mkdir_p($media_dir);
		}
		$relative = 'media/'.cg_gallery_transfer_media_basename($wp_upload_id,$file);
		$target = $state['job_dir'].'/'.$relative;
		if(!file_exists($target)){
			if(!copy($file,$target)){
				$state['warnings'][] = 'Media file could not be copied for attachment ID '.$wp_upload_id;
				return false;
			}
		}
		$post = get_post($wp_upload_id);
		$mime = get_post_mime_type($wp_upload_id);
		$state['data']['media'][$wp_upload_id] = array(
			'old_wp_upload_id' => $wp_upload_id,
			'relative_path' => $relative,
			'filename' => basename($file),
			'post_title' => (!empty($post)) ? $post->post_title : '',
			'post_content' => (!empty($post)) ? $post->post_content : '',
			'post_excerpt' => (!empty($post)) ? $post->post_excerpt : '',
			'post_mime_type' => $mime,
			'alt' => get_post_meta($wp_upload_id,'_wp_attachment_image_alt',true),
		);
		return true;
	}
}

if(!function_exists('cg_gallery_transfer_collect_tables')){
	function cg_gallery_transfer_collect_tables($gallery_id){
		global $wpdb;
		$tables = array(
			'options' => array($wpdb->prefix.'contest_gal1ery_options','id = %d',array($gallery_id)),
			'options_input' => array($wpdb->prefix.'contest_gal1ery_options_input','GalleryID = %d',array($gallery_id)),
			'options_visual' => array($wpdb->prefix.'contest_gal1ery_options_visual','GalleryID = %d',array($gallery_id)),
			'pro_options' => array($wpdb->prefix.'contest_gal1ery_pro_options','GalleryID = %d',array($gallery_id)),
			'f_input' => array($wpdb->prefix.'contest_gal1ery_f_input','GalleryID = %d ORDER BY Field_Order ASC, id ASC',array($gallery_id)),
			'f_output' => array($wpdb->prefix.'contest_gal1ery_f_output','GalleryID = %d ORDER BY Field_Order ASC, id ASC',array($gallery_id)),
			'categories' => array($wpdb->prefix.'contest_gal1ery_categories','GalleryID = %d ORDER BY Field_Order ASC, id ASC',array($gallery_id)),
			'mail' => array($wpdb->prefix.'contest_gal1ery_mail','GalleryID = %d',array($gallery_id)),
			'mail_admin' => array($wpdb->prefix.'contest_gal1ery_mail_admin','GalleryID = %d',array($gallery_id)),
			'mail_user_upload' => array($wpdb->prefix.'contest_gal1ery_mail_user_upload','GalleryID = %d',array($gallery_id)),
			'mail_user_comment' => array($wpdb->prefix.'contest_gal1ery_mail_user_comment','GalleryID = %d',array($gallery_id)),
			'mail_user_vote' => array($wpdb->prefix.'contest_gal1ery_mail_user_vote','GalleryID = %d',array($gallery_id)),
			'mail_confirmation' => array($wpdb->prefix.'contest_gal1ery_mail_confirmation','GalleryID = %d',array($gallery_id)),
			'comments_notification_options' => array($wpdb->prefix.'contest_gal1ery_comments_notification_options','GalleryID = %d',array($gallery_id)),
		);
		$data = array();
		foreach($tables as $key => $args){
			$data[$key] = cg_gallery_transfer_get_rows($args[0],$args[1],$args[2]);
		}
		return $data;
	}
}

if(!function_exists('cg_gallery_transfer_collect_json_files')){
	function cg_gallery_transfer_collect_json_files(&$state,$gallery_id){
		$wp_upload_dir = wp_upload_dir();
		$json_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json';
		$files = array(
			'options' => array($gallery_id.'-options.json','json',true),
			'translations' => array($gallery_id.'-translations.json','json',false),
			'cg_switched' => array('cg-switched.txt','raw',false),
		);
		$data = array();
		foreach($files as $key => $file_data){
			$path = $json_dir.'/'.$file_data[0];
			if(!file_exists($path) || !is_readable($path)){
				if(!empty($file_data[2])){
					$state['warnings'][] = 'Required JSON file missing: '.$file_data[0];
				}
				continue;
			}
			$content = file_get_contents($path);
			if($file_data[1]==='json'){
				$decoded = json_decode($content,true);
				if(is_array($decoded)){
					$data[$key] = $decoded;
				}elseif(!empty($file_data[2])){
					$state['warnings'][] = 'Required JSON file could not be decoded: '.$file_data[0];
				}
			}else{
				$data[$key] = $content;
			}
		}
		return $data;
	}
}

if(!function_exists('cg_gallery_transfer_get_domain_slug')){
	function cg_gallery_transfer_get_domain_slug(){
		$host = wp_parse_url(home_url('/'),PHP_URL_HOST);
		if(empty($host)){
			$host = 'domain';
		}
		return sanitize_file_name(strtolower($host));
	}
}

if(!function_exists('cg_gallery_transfer_zip_queue')){
	function cg_gallery_transfer_zip_queue($dir){
		$files = array();
		$items = scandir($dir);
		foreach($items as $item){
			if($item==='.' || $item==='..' || $item==='state.json'){
				continue;
			}
			$path = $dir.'/'.$item;
			if(is_dir($path)){
				$sub = cg_gallery_transfer_zip_queue($path);
				foreach($sub as $sub_path){
					$files[] = $sub_path;
				}
			}else{
				$files[] = $path;
			}
		}
		return $files;
	}
}

if(!function_exists('cg_gallery_transfer_relative_path')){
	function cg_gallery_transfer_relative_path($base,$path){
		$base = rtrim(str_replace('\\','/',$base),'/').'/';
		$path = str_replace('\\','/',$path);
		if(strpos($path,$base)===0){
			return substr($path,strlen($base));
		}
		return basename($path);
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_export_start','post_cg_gallery_transfer_export_start');
if(!function_exists('post_cg_gallery_transfer_export_start')){
	function post_cg_gallery_transfer_export_start(){
		cg_gallery_transfer_require_manage_options();
		cg_gallery_transfer_cleanup_stale_jobs();
		if(!class_exists('ZipArchive')){
			cg_backend_ajax_error_json('ZipArchive is not available on this server.',500,'cg_gallery_transfer_zip_missing');
		}
		$gallery_id = (!empty($_POST['gallery_id'])) ? absint($_POST['gallery_id']) : 0;
		if(empty($gallery_id) || !cg_gallery_transfer_is_new_slug_gallery($gallery_id)){
			cg_backend_ajax_error_json('Only galleries with the contest-galleries slug system can be exported.',400,'cg_gallery_transfer_gallery_not_allowed');
		}
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery';
		$entry_ids = $wpdb->get_col($wpdb->prepare("SELECT id FROM $table WHERE GalleryID = %d AND (EcommerceEntry IS NULL OR EcommerceEntry = 0) ORDER BY id ASC",array($gallery_id)));
		$sale_entries = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE GalleryID = %d AND EcommerceEntry > 0",array($gallery_id))));
		$job_id = cg_gallery_transfer_job_id();
		$job_dir = cg_gallery_transfer_job_dir($job_id);
		wp_mkdir_p($job_dir);
		wp_mkdir_p($job_dir.'/data');
		wp_mkdir_p($job_dir.'/media');
		$state = array(
			'job_id' => $job_id,
			'job_dir' => $job_dir,
			'type' => 'export',
			'status' => 'running',
			'stage' => 'entries',
			'gallery_id' => $gallery_id,
			'offset' => 0,
			'batch_size' => 20,
			'entry_ids' => array_map('intval',$entry_ids),
			'total_entries' => count($entry_ids),
			'skipped_sale_entries' => $sale_entries,
			'zip_index' => 0,
			'zip_queue' => array(),
			'warnings' => array(),
			'user_refs' => array(),
			'data' => cg_gallery_transfer_collect_tables($gallery_id),
		);
		$state['data']['json_files'] = cg_gallery_transfer_collect_json_files($state,$gallery_id);
		$state['data']['entries'] = array();
		$state['data']['entry_fields'] = array();
		$state['data']['votes'] = array();
		$state['data']['comments'] = array();
		$state['data']['mails'] = array();
		$state['data']['media'] = array();
		cg_gallery_transfer_save_state($state);
		wp_send_json_success(array(
			'job_id' => $job_id,
			'total_entries' => $state['total_entries'],
			'skipped_sale_entries' => $sale_entries,
			'message' => 'Export started.'
		));
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_export_step','post_cg_gallery_transfer_export_step');
if(!function_exists('post_cg_gallery_transfer_export_step')){
	function post_cg_gallery_transfer_export_step(){
		cg_gallery_transfer_require_manage_options();
		$job_id = (!empty($_POST['job_id'])) ? sanitize_text_field($_POST['job_id']) : '';
		$state = cg_gallery_transfer_load_state($job_id);
		if(empty($state) || empty($state['type']) || $state['type']!=='export'){
			cg_backend_ajax_error_json('Export job not found.',404,'cg_gallery_transfer_job_missing');
		}
		global $wpdb;
		$gallery_id = absint($state['gallery_id']);
		$table_entries = $wpdb->prefix.'contest_gal1ery';
		$table_entry_fields = $wpdb->prefix.'contest_gal1ery_entries';
		$table_votes = $wpdb->prefix.'contest_gal1ery_ip';
		$table_comments = $wpdb->prefix.'contest_gal1ery_comments';
		$table_mails = $wpdb->prefix.'contest_gal1ery_mails';
		if($state['stage']==='entries'){
			$ids = array_slice($state['entry_ids'],intval($state['offset']),intval($state['batch_size']));
			if(!empty($ids)){
				$placeholders = implode(',',array_fill(0,count($ids),'%d'));
				$entry_rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_entries WHERE id IN ($placeholders) ORDER BY id ASC",$ids),ARRAY_A);
				foreach($entry_rows as $row){
					$state['data']['entries'][] = $row;
					cg_gallery_transfer_add_user_ref($state,$row['WpUserId']);
					cg_gallery_transfer_add_media_ref($state,$row['WpUpload']);
					$multiple_files = cg_gallery_transfer_safe_unserialize($row['MultipleFiles']);
					foreach($multiple_files as $file_data){
						if(!empty($file_data['WpUpload'])){
							cg_gallery_transfer_add_media_ref($state,$file_data['WpUpload']);
						}
					}
				}
				$params = array_merge(array($gallery_id),$ids);
				$state['data']['entry_fields'] = array_merge($state['data']['entry_fields'],$wpdb->get_results($wpdb->prepare("SELECT * FROM $table_entry_fields WHERE GalleryID = %d AND pid IN ($placeholders) ORDER BY pid ASC, id ASC",$params),ARRAY_A));
				$state['data']['votes'] = array_merge($state['data']['votes'],$wpdb->get_results($wpdb->prepare("SELECT * FROM $table_votes WHERE GalleryID = %d AND pid IN ($placeholders) ORDER BY pid ASC, id ASC",$params),ARRAY_A));
				$comment_rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_comments WHERE GalleryID = %d AND pid IN ($placeholders) ORDER BY pid ASC, id ASC",$params),ARRAY_A);
				foreach($comment_rows as $comment_row){
					cg_gallery_transfer_add_user_ref($state,$comment_row['WpUserId']);
					$state['data']['comments'][] = $comment_row;
				}
				foreach($state['data']['votes'] as $vote_row){
					if(!empty($vote_row['WpUserId'])){
						cg_gallery_transfer_add_user_ref($state,$vote_row['WpUserId']);
					}
				}
				if(cg_gallery_transfer_table_exists($table_mails)){
					$mail_rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_mails WHERE GalleryID = %d AND pid IN ($placeholders) ORDER BY pid ASC, id ASC",$params),ARRAY_A);
					foreach($mail_rows as $mail_row){
						cg_gallery_transfer_add_user_ref($state,$mail_row['WpUserId']);
						$state['data']['mails'][] = $mail_row;
					}
				}
				$state['offset'] = intval($state['offset']) + count($ids);
			}
			if(intval($state['offset']) >= intval($state['total_entries'])){
				$state['stage'] = 'write_files';
			}
			cg_gallery_transfer_save_state($state);
			wp_send_json_success(cg_gallery_transfer_export_status_payload($state));
		}
		if($state['stage']==='write_files'){
			$manifest = array(
				'format' => 'cg_gallery_transfer_v1',
				'source_domain' => home_url('/'),
				'source_gallery_id' => $gallery_id,
				'created_at' => time(),
				'plugin_version' => cg_get_version_for_scripts(),
				'db_version' => cg_get_db_version(),
				'slug_system' => 'contest-galleries',
				'counts' => array(
					'entries' => count($state['data']['entries']),
					'entry_fields' => count($state['data']['entry_fields']),
					'votes' => count($state['data']['votes']),
					'comments' => count($state['data']['comments']),
					'mails' => count($state['data']['mails']),
					'users' => count($state['user_refs']),
					'media' => count($state['data']['media']),
					'json_files' => (!empty($state['data']['json_files'])) ? count($state['data']['json_files']) : 0,
					'skipped_sale_entries' => intval($state['skipped_sale_entries']),
				),
			);
			cg_gallery_transfer_write_json_file($state['job_dir'].'/manifest.json',$manifest);
			cg_gallery_transfer_write_json_file($state['job_dir'].'/data/gallery.json',$state['data']);
			cg_gallery_transfer_write_json_file($state['job_dir'].'/data/users.json',array_values($state['user_refs']));
			$zip_name = cg_gallery_transfer_get_domain_slug().'-gallery-'.$gallery_id.'.zip';
			$state['zip_file'] = $state['job_dir'].'/'.$zip_name;
			$state['zip_name'] = $zip_name;
			$state['zip_queue'] = cg_gallery_transfer_zip_queue($state['job_dir']);
			$state['zip_index'] = 0;
			$state['stage'] = 'zip';
			cg_gallery_transfer_save_state($state);
			wp_send_json_success(cg_gallery_transfer_export_status_payload($state));
		}
		if($state['stage']==='zip'){
			$zip = new ZipArchive();
			if($zip->open($state['zip_file'],ZipArchive::CREATE)!==true){
				cg_backend_ajax_error_json('Export ZIP could not be created.',500,'cg_gallery_transfer_zip_create_failed');
			}
			$batch = 60;
			$index = intval($state['zip_index']);
			$end = min($index+$batch,count($state['zip_queue']));
			for($i=$index;$i<$end;$i++){
				$file = $state['zip_queue'][$i];
				if($file===$state['zip_file']){
					continue;
				}
				if(file_exists($file)){
					$zip->addFile($file,cg_gallery_transfer_relative_path($state['job_dir'],$file));
				}
			}
			$zip->close();
			$state['zip_index'] = $end;
			if($state['zip_index'] >= count($state['zip_queue'])){
				$state['status'] = 'done';
				$state['stage'] = 'done';
			}
			cg_gallery_transfer_save_state($state);
			wp_send_json_success(cg_gallery_transfer_export_status_payload($state));
		}
		wp_send_json_success(cg_gallery_transfer_export_status_payload($state));
	}
}

if(!function_exists('cg_gallery_transfer_export_status_payload')){
	function cg_gallery_transfer_export_status_payload($state){
		$total = max(1,intval($state['total_entries']));
		$entry_percent = min(80,round((intval($state['offset'])/$total)*80));
		$zip_percent = 0;
		if(!empty($state['zip_queue'])){
			$zip_percent = round((intval($state['zip_index'])/max(1,count($state['zip_queue'])))*20);
		}
		$percent = ($state['stage']==='done') ? 100 : min(99,$entry_percent+$zip_percent);
		$download_url = '';
		if(!empty($state['status']) && $state['status']==='done'){
			$download_url = admin_url('admin-ajax.php?action=post_cg_gallery_transfer_download&job_id='.rawurlencode($state['job_id']).'&cg_nonce='.rawurlencode(wp_create_nonce('cg_nonce')));
		}
		return array(
			'job_id' => $state['job_id'],
			'status' => $state['status'],
			'stage' => $state['stage'],
			'percent' => $percent,
			'processed_entries' => intval($state['offset']),
			'total_entries' => intval($state['total_entries']),
			'skipped_sale_entries' => intval($state['skipped_sale_entries']),
			'warnings' => $state['warnings'],
			'download_url' => $download_url,
		);
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_download','post_cg_gallery_transfer_download');
if(!function_exists('post_cg_gallery_transfer_download')){
	function post_cg_gallery_transfer_download(){
		if(!current_user_can('manage_options')){
			status_header(403);
			exit;
		}
		$cg_nonce = (!empty($_GET['cg_nonce'])) ? sanitize_text_field($_GET['cg_nonce']) : '';
		if(empty($cg_nonce) || !wp_verify_nonce($cg_nonce,'cg_nonce')){
			status_header(403);
			exit;
		}
		$job_id = (!empty($_GET['job_id'])) ? sanitize_text_field($_GET['job_id']) : '';
		$state = cg_gallery_transfer_load_state($job_id);
		if(empty($state) || empty($state['zip_file']) || !file_exists($state['zip_file']) || !is_readable($state['zip_file'])){
			status_header(404);
			exit;
		}
		clearstatcache(true,$state['zip_file']);
		$zip_size = filesize($state['zip_file']);
		if($zip_size===false || intval($zip_size)<1){
			status_header(404);
			exit;
		}
		$zip_handle = fopen($state['zip_file'],'rb');
		if(empty($zip_handle)){
			status_header(500);
			exit;
		}
		if(function_exists('set_time_limit')){
			@set_time_limit(0);
		}
		if(!empty($_GET['delete_after'])){
			$job_dir = (!empty($state['job_dir'])) ? $state['job_dir'] : cg_gallery_transfer_job_dir($job_id);
			if(!empty($job_dir)){
				register_shutdown_function('cg_gallery_transfer_delete_dir',$job_dir);
			}
		}
		while(ob_get_level()>0){
			if(!@ob_end_clean()){
				break;
			}
		}
		nocache_headers();
		$zip_name = (!empty($state['zip_name'])) ? basename($state['zip_name']) : basename($state['zip_file']);
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="'.$zip_name.'"');
		header('Content-Length: '.$zip_size);
		$chunk_size = 1048576;
		while(!feof($zip_handle)){
			$buffer = fread($zip_handle,$chunk_size);
			if($buffer===false || $buffer===''){
				break;
			}
			echo $buffer;
			flush();
		}
		fclose($zip_handle);
		exit;
	}
}

if(!function_exists('cg_gallery_transfer_import_prepare_completed_zip')){
	function cg_gallery_transfer_import_prepare_completed_zip($job_id,$job_dir,$zip_file){
		if(!class_exists('ZipArchive')){
			cg_backend_ajax_error_json('ZipArchive is not available on this server.',500,'cg_gallery_transfer_zip_missing');
		}
		if(function_exists('set_time_limit')){
			@set_time_limit(0);
		}
		$zip = new ZipArchive();
		if($zip->open($zip_file)!==true){
			cg_backend_ajax_error_json('ZIP file could not be opened.',400,'cg_gallery_transfer_zip_invalid');
		}
		for($i=0;$i<$zip->numFiles;$i++){
			$name = $zip->getNameIndex($i);
			$normalized = str_replace('\\','/',$name);
			if(strpos($normalized,'..')!==false || strpos($normalized,'/')===0 || preg_match('/^[a-zA-Z]:/',$normalized)){
				$zip->close();
				cg_backend_ajax_error_json('ZIP contains unsafe paths.',400,'cg_gallery_transfer_zip_unsafe');
			}
		}
		$extract_dir = $job_dir.'/extracted';
		wp_mkdir_p($extract_dir);
		if(!$zip->extractTo($extract_dir)){
			$zip->close();
			cg_backend_ajax_error_json('ZIP file could not be extracted.',500,'cg_gallery_transfer_zip_extract_failed');
		}
		$zip->close();
		$manifest = cg_gallery_transfer_read_json_file($extract_dir.'/manifest.json');
		$data = cg_gallery_transfer_read_json_file($extract_dir.'/data/gallery.json');
		$users = cg_gallery_transfer_read_json_file($extract_dir.'/data/users.json');
		if(empty($manifest) || empty($data) || empty($manifest['format']) || $manifest['format']!=='cg_gallery_transfer_v1'){
			cg_backend_ajax_error_json('ZIP is not a Contest Gallery transfer v1 package.',400,'cg_gallery_transfer_manifest_invalid');
		}
		if(empty($manifest['slug_system']) || $manifest['slug_system']!=='contest-galleries'){
			cg_backend_ajax_error_json('Only contest-galleries slug system exports can be imported.',400,'cg_gallery_transfer_slug_invalid');
		}
		if(!is_array($users)){
			$users = array();
		}
		$target_users = array();
		$wp_users = get_users(array('number'=>500,'fields'=>array('ID','display_name','user_email','user_login'),'orderby'=>'display_name','order'=>'ASC'));
		foreach($wp_users as $wp_user){
			$target_users[] = array(
				'ID' => intval($wp_user->ID),
				'display_name' => $wp_user->display_name,
				'user_email' => $wp_user->user_email,
				'user_login' => $wp_user->user_login,
			);
		}
		$state = array(
			'job_id' => $job_id,
			'job_dir' => $job_dir,
			'type' => 'import',
			'status' => 'ready',
			'stage' => 'ready',
			'extract_dir' => $extract_dir,
			'manifest' => $manifest,
			'offset' => 0,
			'batch_size' => 10,
			'new_gallery_id' => 0,
			'maps' => array(
				'entries' => array(),
				'fields' => array(),
				'categories' => array(),
				'media' => array(),
				'users' => array(),
			),
			'warnings' => array(),
		);
		cg_gallery_transfer_save_state($state);
		return array(
			'job_id' => $job_id,
			'manifest' => $manifest,
			'users' => $users,
			'target_users' => $target_users,
		);
	}
}

if(!function_exists('cg_gallery_transfer_import_chunk_size')){
	function cg_gallery_transfer_import_chunk_size(){
		$chunk_size = 1048576;
		$wp_max_upload_size = wp_max_upload_size();
		if(!empty($wp_max_upload_size)){
			$limit_chunk_size = intval(floor($wp_max_upload_size*0.75));
			if($limit_chunk_size<1024){
				$limit_chunk_size = intval($wp_max_upload_size);
			}
			$chunk_size = min($chunk_size,$limit_chunk_size);
		}
		return max(1024,intval($chunk_size));
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_import_upload_start','post_cg_gallery_transfer_import_upload_start');
if(!function_exists('post_cg_gallery_transfer_import_upload_start')){
	function post_cg_gallery_transfer_import_upload_start(){
		cg_gallery_transfer_require_manage_options();
		cg_gallery_transfer_cleanup_stale_jobs();
		if(!class_exists('ZipArchive')){
			cg_backend_ajax_error_json('ZipArchive is not available on this server.',500,'cg_gallery_transfer_zip_missing');
		}
		$file_name = (!empty($_POST['file_name'])) ? sanitize_file_name($_POST['file_name']) : '';
		$file_size = (!empty($_POST['file_size'])) ? absint($_POST['file_size']) : 0;
		if(empty($file_name) || strtolower(pathinfo($file_name,PATHINFO_EXTENSION))!=='zip'){
			cg_backend_ajax_error_json('Select a Contest Gallery ZIP file first.',400,'cg_gallery_transfer_zip_file_required');
		}
		if(empty($file_size)){
			cg_backend_ajax_error_json('ZIP file size could not be detected.',400,'cg_gallery_transfer_zip_size_missing');
		}
		$job_id = cg_gallery_transfer_job_id();
		$job_dir = cg_gallery_transfer_job_dir($job_id);
		wp_mkdir_p($job_dir);
		$chunk_size = cg_gallery_transfer_import_chunk_size();
		$state = array(
			'job_id' => $job_id,
			'job_dir' => $job_dir,
			'type' => 'import',
			'status' => 'uploading',
			'stage' => 'upload',
			'file_name' => $file_name,
			'file_size' => $file_size,
			'uploaded_bytes' => 0,
			'chunk_size' => $chunk_size,
			'expected_chunk_index' => 0,
			'warnings' => array(),
		);
		cg_gallery_transfer_save_state($state);
		wp_send_json_success(array(
			'job_id' => $job_id,
			'status' => 'uploading',
			'stage' => 'upload',
			'file_name' => $file_name,
			'file_size' => $file_size,
			'uploaded_bytes' => 0,
			'chunk_size' => $chunk_size,
			'percent' => 0,
			'next_chunk_index' => 0,
		));
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_import_upload_chunk','post_cg_gallery_transfer_import_upload_chunk');
if(!function_exists('post_cg_gallery_transfer_import_upload_chunk')){
	function post_cg_gallery_transfer_import_upload_chunk(){
		cg_gallery_transfer_require_manage_options();
		$job_id = (!empty($_POST['job_id'])) ? sanitize_text_field($_POST['job_id']) : '';
		$state = cg_gallery_transfer_load_state($job_id);
		if(empty($state) || empty($state['type']) || $state['type']!=='import' || empty($state['stage']) || $state['stage']!=='upload'){
			cg_backend_ajax_error_json('Import upload job not found.',404,'cg_gallery_transfer_upload_job_missing');
		}
		$chunk_index = (isset($_POST['chunk_index'])) ? absint($_POST['chunk_index']) : 0;
		$chunk_offset = (isset($_POST['chunk_offset'])) ? absint($_POST['chunk_offset']) : 0;
		$total_chunks = (!empty($_POST['total_chunks'])) ? absint($_POST['total_chunks']) : 0;
		$file_size = (!empty($_POST['file_size'])) ? absint($_POST['file_size']) : 0;
		$expected_index = (!empty($state['expected_chunk_index'])) ? absint($state['expected_chunk_index']) : 0;
		$uploaded_bytes = (!empty($state['uploaded_bytes'])) ? absint($state['uploaded_bytes']) : 0;
		$expected_file_size = (!empty($state['file_size'])) ? absint($state['file_size']) : 0;
		if(empty($total_chunks) || empty($file_size) || $file_size!==$expected_file_size){
			cg_backend_ajax_error_json('ZIP upload metadata is invalid.',400,'cg_gallery_transfer_upload_metadata_invalid');
		}
		if($chunk_index!==$expected_index || $chunk_offset!==$uploaded_bytes){
			cg_backend_ajax_error_json('ZIP upload chunks arrived out of order.',409,'cg_gallery_transfer_upload_chunk_order_invalid');
		}
		if(empty($_FILES['cg_gallery_transfer_chunk']) || !isset($_FILES['cg_gallery_transfer_chunk']['error']) || intval($_FILES['cg_gallery_transfer_chunk']['error'])!==UPLOAD_ERR_OK || empty($_FILES['cg_gallery_transfer_chunk']['tmp_name'])){
			cg_backend_ajax_error_json('ZIP upload chunk was rejected by the server upload limits.',400,'cg_gallery_transfer_upload_chunk_missing');
		}
		$tmp_name = $_FILES['cg_gallery_transfer_chunk']['tmp_name'];
		$chunk_bytes = filesize($tmp_name);
		if($chunk_bytes===false || $chunk_bytes<1){
			cg_backend_ajax_error_json('ZIP upload chunk is empty.',400,'cg_gallery_transfer_upload_chunk_empty');
		}
		$max_chunk_size = (!empty($state['chunk_size'])) ? absint($state['chunk_size']) : cg_gallery_transfer_import_chunk_size();
		if($chunk_bytes>$max_chunk_size){
			cg_backend_ajax_error_json('ZIP upload chunk is larger than allowed.',400,'cg_gallery_transfer_upload_chunk_too_large');
		}
		if(($uploaded_bytes+$chunk_bytes)>$expected_file_size){
			cg_backend_ajax_error_json('ZIP upload chunk exceeds the expected file size.',400,'cg_gallery_transfer_upload_size_invalid');
		}
		$job_dir = (!empty($state['job_dir'])) ? $state['job_dir'] : cg_gallery_transfer_job_dir($job_id);
		if(empty($job_dir) || !is_dir($job_dir)){
			cg_backend_ajax_error_json('Import upload job directory is missing.',404,'cg_gallery_transfer_upload_dir_missing');
		}
		$partial_file = $job_dir.'/import.zip.part';
		if($chunk_index===0 && $uploaded_bytes===0 && file_exists($partial_file)){
			@unlink($partial_file);
		}
		$current_size = file_exists($partial_file) ? filesize($partial_file) : 0;
		if($current_size===false){
			$current_size = 0;
		}
		if(intval($current_size)!==$uploaded_bytes){
			cg_backend_ajax_error_json('ZIP upload temporary file size is inconsistent.',409,'cg_gallery_transfer_upload_size_mismatch');
		}
		$source = fopen($tmp_name,'rb');
		$target = fopen($partial_file,'ab');
		if(empty($source) || empty($target)){
			if(!empty($source)){fclose($source);}
			if(!empty($target)){fclose($target);}
			cg_backend_ajax_error_json('ZIP upload chunk could not be stored.',500,'cg_gallery_transfer_upload_chunk_store_failed');
		}
		$copied_bytes = stream_copy_to_stream($source,$target);
		fclose($source);
		fclose($target);
		if($copied_bytes===false || intval($copied_bytes)!==intval($chunk_bytes)){
			cg_backend_ajax_error_json('ZIP upload chunk could not be written completely.',500,'cg_gallery_transfer_upload_chunk_write_incomplete');
		}
		clearstatcache(true,$partial_file);
		$new_uploaded_bytes = filesize($partial_file);
		if($new_uploaded_bytes===false){
			cg_backend_ajax_error_json('ZIP upload temporary file size could not be checked.',500,'cg_gallery_transfer_upload_size_check_failed');
		}
		$state['uploaded_bytes'] = intval($new_uploaded_bytes);
		$state['expected_chunk_index'] = $chunk_index+1;
		cg_gallery_transfer_save_state($state);
		$is_complete = (intval($new_uploaded_bytes)===$expected_file_size || ($chunk_index+1)>=$total_chunks);
		if($is_complete){
			if(intval($new_uploaded_bytes)!==$expected_file_size){
				cg_backend_ajax_error_json('ZIP upload is incomplete.',400,'cg_gallery_transfer_upload_incomplete');
			}
			$zip_file = $job_dir.'/import.zip';
			if(file_exists($zip_file)){
				@unlink($zip_file);
			}
			if(!rename($partial_file,$zip_file)){
				cg_backend_ajax_error_json('ZIP upload could not be finalized.',500,'cg_gallery_transfer_upload_finalize_failed');
			}
			wp_send_json_success(cg_gallery_transfer_import_prepare_completed_zip($job_id,$job_dir,$zip_file));
		}
		$percent = round((intval($new_uploaded_bytes)/max(1,$expected_file_size))*100);
		wp_send_json_success(array(
			'job_id' => $job_id,
			'status' => 'uploading',
			'stage' => 'upload',
			'uploaded_bytes' => intval($new_uploaded_bytes),
			'file_size' => $expected_file_size,
			'percent' => min(99,intval($percent)),
			'next_chunk_index' => $chunk_index+1,
		));
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_import_start','post_cg_gallery_transfer_import_start');
if(!function_exists('post_cg_gallery_transfer_import_start')){
	function post_cg_gallery_transfer_import_start(){
		cg_gallery_transfer_require_manage_options();
		cg_gallery_transfer_cleanup_stale_jobs();
		if(empty($_FILES['cg_gallery_transfer_zip']) || empty($_FILES['cg_gallery_transfer_zip']['tmp_name'])){
			cg_backend_ajax_error_json('No ZIP file uploaded.',400,'cg_gallery_transfer_no_upload');
		}
		$job_id = cg_gallery_transfer_job_id();
		$job_dir = cg_gallery_transfer_job_dir($job_id);
		wp_mkdir_p($job_dir);
		$zip_file = $job_dir.'/import.zip';
		if(!move_uploaded_file($_FILES['cg_gallery_transfer_zip']['tmp_name'],$zip_file)){
			cg_backend_ajax_error_json('ZIP upload could not be stored.',500,'cg_gallery_transfer_upload_failed');
		}
		wp_send_json_success(cg_gallery_transfer_import_prepare_completed_zip($job_id,$job_dir,$zip_file));
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_import_step','post_cg_gallery_transfer_import_step');
if(!function_exists('post_cg_gallery_transfer_import_step')){
	function post_cg_gallery_transfer_import_step(){
		cg_gallery_transfer_require_manage_options();
		$job_id = (!empty($_POST['job_id'])) ? sanitize_text_field($_POST['job_id']) : '';
		$state = cg_gallery_transfer_load_state($job_id);
		if(empty($state) || empty($state['type']) || $state['type']!=='import'){
			cg_backend_ajax_error_json('Import job not found.',404,'cg_gallery_transfer_job_missing');
		}
		$data = cg_gallery_transfer_read_json_file($state['extract_dir'].'/data/gallery.json');
		$users = cg_gallery_transfer_read_json_file($state['extract_dir'].'/data/users.json');
		if(empty($data)){
			cg_backend_ajax_error_json('Import data missing.',400,'cg_gallery_transfer_data_missing');
		}
		if(!is_array($users)){
			$users = array();
		}
		$mode = (!empty($_POST['user_mapping_mode'])) ? sanitize_key($_POST['user_mapping_mode']) : 'none';
		$single_user_id = (!empty($_POST['single_user_id'])) ? absint($_POST['single_user_id']) : 0;
		if($state['stage']==='ready'){
			cg_gallery_transfer_import_initialize($state,$data,$users,$mode,$single_user_id);
			cg_gallery_transfer_save_state($state);
			wp_send_json_success(cg_gallery_transfer_import_status_payload($state,$data));
		}
		if($state['stage']==='entries'){
			cg_gallery_transfer_import_entries_step($state,$data,$mode,$single_user_id);
			cg_gallery_transfer_save_state($state);
			wp_send_json_success(cg_gallery_transfer_import_status_payload($state,$data));
		}
		wp_send_json_success(cg_gallery_transfer_import_status_payload($state,$data));
	}
}

if(!function_exists('cg_gallery_transfer_import_status_payload')){
	function cg_gallery_transfer_import_status_payload($state,$data){
		$total = (!empty($data['entries'])) ? count($data['entries']) : 0;
		$percent = ($state['stage']==='done') ? 100 : min(99,round((intval($state['offset'])/max(1,$total))*100));
		return array(
			'job_id' => $state['job_id'],
			'status' => $state['status'],
			'stage' => $state['stage'],
			'percent' => $percent,
			'processed_entries' => intval($state['offset']),
			'total_entries' => $total,
			'new_gallery_id' => intval($state['new_gallery_id']),
			'warnings' => $state['warnings'],
		);
	}
}

if(!function_exists('cg_gallery_transfer_import_user_map')){
	function cg_gallery_transfer_import_user_map($users,$mode,$single_user_id){
		$map = array();
		if($mode==='email'){
			foreach($users as $source_user){
				if(empty($source_user['source_wp_user_id']) || empty($source_user['user_email'])){
					continue;
				}
				$target = get_user_by('email',$source_user['user_email']);
				$map[intval($source_user['source_wp_user_id'])] = (!empty($target)) ? intval($target->ID) : 0;
			}
		}elseif($mode==='single' && !empty($single_user_id)){
			foreach($users as $source_user){
				if(!empty($source_user['source_wp_user_id'])){
					$map[intval($source_user['source_wp_user_id'])] = intval($single_user_id);
				}
			}
		}
		return $map;
	}
}

if(!function_exists('cg_gallery_transfer_map_user_id')){
	function cg_gallery_transfer_map_user_id($state,$source_user_id,$context){
		$source_user_id = absint($source_user_id);
		if(empty($source_user_id)){
			return 0;
		}
		if($context!=='entry' && !empty($state['user_mapping_mode']) && $state['user_mapping_mode']==='single'){
			return 0;
		}
		if(!empty($state['maps']['users'][$source_user_id])){
			return absint($state['maps']['users'][$source_user_id]);
		}
		return 0;
	}
}

if(!function_exists('cg_gallery_transfer_import_initialize')){
	function cg_gallery_transfer_import_initialize(&$state,$data,$users,$mode,$single_user_id){
		global $wpdb;
		$mode = in_array($mode,array('none','single','email'),true) ? $mode : 'none';
		$state['user_mapping_mode'] = $mode;
		$state['single_user_id'] = $single_user_id;
		$state['maps']['users'] = cg_gallery_transfer_import_user_map($users,$mode,$single_user_id);
		$table_options = $wpdb->prefix.'contest_gal1ery_options';
		$options_row = (!empty($data['options'][0])) ? $data['options'][0] : array();
		if(empty($options_row)){
			cg_backend_ajax_error_json('Gallery options missing in import data.',400,'cg_gallery_transfer_options_missing');
		}
		$gallery_name = (!empty($options_row['GalleryName'])) ? $options_row['GalleryName'].' - import' : 'Imported gallery';
		$new_gallery_id = cg_gallery_transfer_insert_row($table_options,$options_row,array(
			'GalleryName' => $gallery_name,
			'Version' => cg_get_version_for_scripts(),
			'VersionDecimal' => floatval(cg_get_db_version()),
			'WpPageParent' => 0,
			'WpPageParentUser' => 0,
			'WpPageParentNoVoting' => 0,
			'WpPageParentWinner' => 0,
			'WpPageParentEcommerce' => 0,
		),true);
		if(empty($new_gallery_id)){
			cg_backend_ajax_error_json('Gallery options could not be imported.',500,'cg_gallery_transfer_gallery_create_failed');
		}
		$state['new_gallery_id'] = $new_gallery_id;
		cg_gallery_transfer_prepare_gallery_folders($new_gallery_id);
		cg_create_slug_name_galleries_posts_if_required();
		$pages = cg_gallery_transfer_create_parent_pages($new_gallery_id);
		$wpdb->update($table_options,$pages,array('id'=>$new_gallery_id),array('%d','%d','%d','%d','%d'),array('%d'));
		cg_gallery_transfer_import_categories($state,$data);
		cg_gallery_transfer_import_form_fields($state,$data);
		cg_gallery_transfer_import_option_tables($state,$data);
		cg_gallery_transfer_write_categories_json($state);
		cg_gallery_transfer_write_options_json($state,$data,$pages,$gallery_name);
		cg_gallery_transfer_write_static_json_files($state,$data);
		do_action('cg_json_upload_form',$new_gallery_id);
		do_action('cg_json_single_view_order',$new_gallery_id);
		$state['stage'] = 'entries';
		$state['status'] = 'running';
		$state['offset'] = 0;
	}
}

if(!function_exists('cg_gallery_transfer_prepare_gallery_folders')){
	function cg_gallery_transfer_prepare_gallery_folders($gallery_id){
		$wp_upload_dir = wp_upload_dir();
		$dirs = array(
			$wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id,
			$wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json',
			$wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json/image-data',
			$wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json/image-info',
			$wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json/image-comments',
		);
		foreach($dirs as $dir){
			if(!is_dir($dir)){
				wp_mkdir_p($dir);
			}
		}
		file_put_contents($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$gallery_id.'/json/do not remove json or txt files manually.txt','Removing json or txt files manually will break functionality of your gallery');
	}
}

if(!function_exists('cg_gallery_transfer_create_parent_pages')){
	function cg_gallery_transfer_create_parent_pages($gallery_id){
		$pages = array();
		$types = array(
			'WpPageParent' => '',
			'WpPageParentUser' => 'user',
			'WpPageParentNoVoting' => 'no-voting',
			'WpPageParentWinner' => 'winner',
			'WpPageParentEcommerce' => 'ecommerce',
		);
		foreach($types as $field => $type){
			$array = cg_post_type_parent_galleries_array($gallery_id,$type);
			$page_id = wp_insert_post($array);
			$pages[$field] = intval($page_id);
			cg_insert_into_contest_gal1ery_wp_pages($page_id);
		}
		return $pages;
	}
}

if(!function_exists('cg_gallery_transfer_import_categories')){
	function cg_gallery_transfer_import_categories(&$state,$data){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_categories';
		if(empty($data['categories'])){
			return;
		}
		foreach($data['categories'] as $row){
			$old_id = intval($row['id']);
			$new_id = cg_gallery_transfer_insert_row($table,$row,array('GalleryID'=>$state['new_gallery_id']),true);
			if(!empty($new_id)){
				$state['maps']['categories'][$old_id] = $new_id;
			}
		}
	}
}

if(!function_exists('cg_gallery_transfer_import_form_fields')){
	function cg_gallery_transfer_import_form_fields(&$state,$data){
		global $wpdb;
		$table_input = $wpdb->prefix.'contest_gal1ery_f_input';
		$table_output = $wpdb->prefix.'contest_gal1ery_f_output';
		if(!empty($data['f_input'])){
			foreach($data['f_input'] as $row){
				$old_id = intval($row['id']);
				$new_id = cg_gallery_transfer_insert_row($table_input,$row,array('GalleryID'=>$state['new_gallery_id']),true);
				if(!empty($new_id)){
					$state['maps']['fields'][$old_id] = $new_id;
				}
			}
		}
		if(!empty($data['f_output'])){
			foreach($data['f_output'] as $row){
				$old_field_id = intval($row['f_input_id']);
				$new_field_id = (!empty($state['maps']['fields'][$old_field_id])) ? $state['maps']['fields'][$old_field_id] : 0;
				cg_gallery_transfer_insert_row($table_output,$row,array(
					'GalleryID' => $state['new_gallery_id'],
					'f_input_id' => $new_field_id,
				),true);
			}
		}
	}
}

if(!function_exists('cg_gallery_transfer_map_visual_field')){
	function cg_gallery_transfer_map_visual_field($state,$value){
		$value = absint($value);
		return (!empty($state['maps']['fields'][$value])) ? $state['maps']['fields'][$value] : 0;
	}
}

if(!function_exists('cg_gallery_transfer_import_option_tables')){
	function cg_gallery_transfer_import_option_tables(&$state,$data){
		global $wpdb;
		$new_gallery_id = $state['new_gallery_id'];
		$simple_tables = array(
			'options_input' => $wpdb->prefix.'contest_gal1ery_options_input',
			'pro_options' => $wpdb->prefix.'contest_gal1ery_pro_options',
			'mail' => $wpdb->prefix.'contest_gal1ery_mail',
			'mail_admin' => $wpdb->prefix.'contest_gal1ery_mail_admin',
			'mail_user_upload' => $wpdb->prefix.'contest_gal1ery_mail_user_upload',
			'mail_user_comment' => $wpdb->prefix.'contest_gal1ery_mail_user_comment',
			'mail_user_vote' => $wpdb->prefix.'contest_gal1ery_mail_user_vote',
			'mail_confirmation' => $wpdb->prefix.'contest_gal1ery_mail_confirmation',
			'comments_notification_options' => $wpdb->prefix.'contest_gal1ery_comments_notification_options',
		);
		if(!empty($data['options_visual'])){
			$table_visual = $wpdb->prefix.'contest_gal1ery_options_visual';
			foreach($data['options_visual'] as $row){
				$overrides = array('GalleryID'=>$new_gallery_id);
				foreach(array('Field1IdGalleryView','Field1IdFullWindowBlogView','Field2IdGalleryView','Field3IdGalleryView') as $field){
					if(isset($row[$field])){
						$overrides[$field] = cg_gallery_transfer_map_visual_field($state,$row[$field]);
					}
				}
				cg_gallery_transfer_insert_row($table_visual,$row,$overrides,true);
			}
		}
		foreach($simple_tables as $key => $table){
			if(empty($data[$key])){
				continue;
			}
			foreach($data[$key] as $row){
				cg_gallery_transfer_insert_row($table,$row,array('GalleryID'=>$new_gallery_id),true);
			}
		}
	}
}

if(!function_exists('cg_gallery_transfer_write_categories_json')){
	function cg_gallery_transfer_write_categories_json($state){
		global $wpdb;
		$new_gallery_id = absint($state['new_gallery_id']);
		$table = $wpdb->prefix.'contest_gal1ery_categories';
		$rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE GalleryID = %d ORDER BY Field_Order ASC, id ASC",array($new_gallery_id)));
		$categories = array();
		foreach($rows as $row){
			$categories[$row->id] = array(
				'id' => $row->id,
				'GalleryID' => $row->GalleryID,
				'Name' => $row->Name,
				'Field_Order' => $row->Field_Order,
				'Active' => $row->Active,
			);
		}
		$wp_upload_dir = wp_upload_dir();
		cg_gallery_transfer_write_json_file($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$new_gallery_id.'/json/'.$new_gallery_id.'-categories.json',$categories);
	}
}

if(!function_exists('cg_gallery_transfer_option_variant_by_key')){
	function cg_gallery_transfer_option_variant_by_key($options,$key_to_find){
		foreach($options as $key => $value){
			if((string)$key===(string)$key_to_find){
				return $value;
			}
		}
		return false;
	}
}

if(!function_exists('cg_gallery_transfer_is_source_option_variant_key')){
	function cg_gallery_transfer_is_source_option_variant_key($key,$source_gallery_id){
		$key = (string)$key;
		$source_gallery_id = (string)$source_gallery_id;
		$keys = array($source_gallery_id,$source_gallery_id.'-u',$source_gallery_id.'-nv',$source_gallery_id.'-w',$source_gallery_id.'-ec');
		return in_array($key,$keys,true);
	}
}

if(!function_exists('cg_gallery_transfer_map_option_field_id')){
	function cg_gallery_transfer_map_option_field_id($state,$value){
		$old_id = absint($value);
		if(empty($old_id)){
			return 0;
		}
		return (!empty($state['maps']['fields'][$old_id])) ? intval($state['maps']['fields'][$old_id]) : 0;
	}
}

if(!function_exists('cg_gallery_transfer_adapt_options_variant')){
	function cg_gallery_transfer_adapt_options_variant($variant,$state,$pages,$gallery_name){
		if(!is_array($variant)){
			$variant = array();
		}
		$new_gallery_id = absint($state['new_gallery_id']);
		if(empty($variant['general']) || !is_array($variant['general'])){
			$variant['general'] = array();
		}
		$variant['general']['GalleryName'] = $gallery_name;
		$variant['general']['Version'] = cg_get_version_for_scripts();
		$variant['general']['VersionDecimal'] = floatval(cg_get_db_version());
		if(isset($variant['general']['id'])){
			$variant['general']['id'] = $new_gallery_id;
		}
		if(isset($variant['general']['GalleryID'])){
			$variant['general']['GalleryID'] = $new_gallery_id;
		}
		foreach(array('WpPageParent','WpPageParentUser','WpPageParentNoVoting','WpPageParentWinner','WpPageParentEcommerce') as $page_field){
			if(isset($pages[$page_field])){
				$variant['general'][$page_field] = intval($pages[$page_field]);
			}
		}
		if(!empty($variant['input']) && is_array($variant['input'])){
			if(isset($variant['input']['GalleryID'])){
				$variant['input']['GalleryID'] = $new_gallery_id;
			}
		}
		if(!empty($variant['pro']) && is_array($variant['pro'])){
			if(isset($variant['pro']['GalleryID'])){
				$variant['pro']['GalleryID'] = $new_gallery_id;
			}
		}
		if(!empty($variant['visual']) && is_array($variant['visual'])){
			if(isset($variant['visual']['GalleryID'])){
				$variant['visual']['GalleryID'] = $new_gallery_id;
			}
			$field_keys = array(
				'Field1IdGalleryView',
				'Field1IdFullWindowBlogView',
				'Field2IdGalleryView',
				'Field3IdGalleryView',
				'SubTitle',
				'ThirdTitle',
				'IsForWpPageTitleID',
				'EcommerceTitle',
				'EcommerceDescription',
			);
			foreach($field_keys as $field_key){
				if(isset($variant['visual'][$field_key])){
					$variant['visual'][$field_key] = cg_gallery_transfer_map_option_field_id($state,$variant['visual'][$field_key]);
				}
			}
		}
		return $variant;
	}
}

if(!function_exists('cg_gallery_transfer_build_fallback_options_json')){
	function cg_gallery_transfer_build_fallback_options_json($state,$pages,$gallery_name){
		global $wpdb;
		$new_gallery_id = absint($state['new_gallery_id']);
		$base = array(
			'general' => array(),
			'visual' => array(),
			'input' => array(),
			'pro' => array(),
			'interval' => array(),
		);
		$tables = array(
			'general' => array($wpdb->prefix.'contest_gal1ery_options','id = %d'),
			'visual' => array($wpdb->prefix.'contest_gal1ery_options_visual','GalleryID = %d'),
			'input' => array($wpdb->prefix.'contest_gal1ery_options_input','GalleryID = %d'),
			'pro' => array($wpdb->prefix.'contest_gal1ery_pro_options','GalleryID = %d'),
		);
		foreach($tables as $key => $args){
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$args[0]." WHERE ".$args[1]." LIMIT 1",array($new_gallery_id)),ARRAY_A);
			if(is_array($row)){
				$base[$key] = $row;
			}
		}
		$base = cg_gallery_transfer_adapt_options_variant($base,$state,$pages,$gallery_name);
		$options = $base;
		$options[$new_gallery_id] = $base;
		$options[$new_gallery_id.'-u'] = $base;
		$options[$new_gallery_id.'-nv'] = $base;
		$options[$new_gallery_id.'-w'] = $base;
		$options[$new_gallery_id.'-ec'] = $base;
		return $options;
	}
}

if(!function_exists('cg_gallery_transfer_write_options_json')){
	function cg_gallery_transfer_write_options_json(&$state,$data,$pages,$gallery_name){
		$new_gallery_id = absint($state['new_gallery_id']);
		$source_gallery_id = 0;
		if(!empty($state['manifest']['source_gallery_id'])){
			$source_gallery_id = absint($state['manifest']['source_gallery_id']);
		}elseif(!empty($data['options'][0]['id'])){
			$source_gallery_id = absint($data['options'][0]['id']);
		}
		$source_options = array();
		if(!empty($data['json_files']['options']) && is_array($data['json_files']['options'])){
			$source_options = $data['json_files']['options'];
		}
		if(empty($source_options) || empty($source_gallery_id)){
			$state['warnings'][] = 'Source options JSON was missing. A fallback options JSON was generated from imported database rows.';
			$options = cg_gallery_transfer_build_fallback_options_json($state,$pages,$gallery_name);
		}else{
			$options = array();
			foreach($source_options as $key => $value){
				if(cg_gallery_transfer_is_source_option_variant_key($key,$source_gallery_id)){
					continue;
				}
				$options[$key] = $value;
			}
			$options = cg_gallery_transfer_adapt_options_variant($options,$state,$pages,$gallery_name);
			$suffixes = array('','-u','-nv','-w','-ec');
			foreach($suffixes as $suffix){
				$source_key = $source_gallery_id.$suffix;
				$target_key = $new_gallery_id.$suffix;
				$source_variant = cg_gallery_transfer_option_variant_by_key($source_options,$source_key);
				if(!is_array($source_variant)){
					$source_variant = cg_gallery_transfer_option_variant_by_key($source_options,$source_gallery_id);
				}
				if(!is_array($source_variant)){
					$source_variant = $options;
				}
				$options[$target_key] = cg_gallery_transfer_adapt_options_variant($source_variant,$state,$pages,$gallery_name);
			}
		}
		$wp_upload_dir = wp_upload_dir();
		cg_gallery_transfer_write_json_file($wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$new_gallery_id.'/json/'.$new_gallery_id.'-options.json',$options);
	}
}

if(!function_exists('cg_gallery_transfer_write_static_json_files')){
	function cg_gallery_transfer_write_static_json_files($state,$data){
		$new_gallery_id = absint($state['new_gallery_id']);
		$wp_upload_dir = wp_upload_dir();
		$json_dir = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$new_gallery_id.'/json';
		$translations = array();
		if(!empty($data['json_files']['translations']) && is_array($data['json_files']['translations'])){
			$translations = $data['json_files']['translations'];
		}
		cg_gallery_transfer_write_json_file($json_dir.'/'.$new_gallery_id.'-translations.json',$translations);
		if(isset($data['json_files']['cg_switched'])){
			file_put_contents($json_dir.'/cg-switched.txt',$data['json_files']['cg_switched']);
		}
	}
}

if(!function_exists('cg_gallery_transfer_import_attachment')){
	function cg_gallery_transfer_import_attachment(&$state,$data,$old_wp_upload_id){
		$old_wp_upload_id = absint($old_wp_upload_id);
		if(empty($old_wp_upload_id)){
			return 0;
		}
		if(!empty($state['maps']['media'][$old_wp_upload_id])){
			return intval($state['maps']['media'][$old_wp_upload_id]);
		}
		$media_key = $old_wp_upload_id;
		if(empty($data['media'][$media_key]) && !empty($data['media'][(string)$old_wp_upload_id])){
			$media_key = (string)$old_wp_upload_id;
		}
		if(empty($data['media'][$media_key])){
			$state['warnings'][] = 'Media reference missing in package for attachment ID '.$old_wp_upload_id;
			return 0;
		}
		$media = $data['media'][$media_key];
		$source = $state['extract_dir'].'/'.$media['relative_path'];
		if(!file_exists($source) || !is_readable($source)){
			$state['warnings'][] = 'Media file missing in package for attachment ID '.$old_wp_upload_id;
			return 0;
		}
		$wp_upload_dir = wp_upload_dir();
		if(!is_dir($wp_upload_dir['path'])){
			wp_mkdir_p($wp_upload_dir['path']);
		}
		$filename = wp_unique_filename($wp_upload_dir['path'],sanitize_file_name($media['filename']));
		$target = $wp_upload_dir['path'].'/'.$filename;
		if(!copy($source,$target)){
			$state['warnings'][] = 'Media file could not be copied for attachment ID '.$old_wp_upload_id;
			return 0;
		}
		$filetype = wp_check_filetype($filename,null);
		$mime = (!empty($filetype['type'])) ? $filetype['type'] : ((!empty($media['post_mime_type'])) ? $media['post_mime_type'] : 'application/octet-stream');
		$attachment = array(
			'guid' => $wp_upload_dir['url'].'/'.$filename,
			'post_mime_type' => $mime,
			'post_title' => (!empty($media['post_title'])) ? $media['post_title'] : preg_replace('/\.[^.]+$/','',$filename),
			'post_content' => (!empty($media['post_content'])) ? $media['post_content'] : '',
			'post_excerpt' => (!empty($media['post_excerpt'])) ? $media['post_excerpt'] : '',
			'post_status' => 'inherit',
		);
		$attach_id = wp_insert_attachment($attachment,$target);
		if(empty($attach_id)){
			$state['warnings'][] = 'Attachment could not be created for '.$filename;
			return 0;
		}
		require_once(ABSPATH.'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata($attach_id,$target);
		if(!empty($attach_data)){
			wp_update_attachment_metadata($attach_id,$attach_data);
		}
		if(!empty($media['alt'])){
			update_post_meta($attach_id,'_wp_attachment_image_alt',$media['alt']);
		}
		$state['maps']['media'][$old_wp_upload_id] = intval($attach_id);
		return intval($attach_id);
	}
}

if(!function_exists('cg_gallery_transfer_map_multiple_files')){
	function cg_gallery_transfer_map_multiple_files(&$state,$data,$multiple_files_serialized){
		$multiple_files = cg_gallery_transfer_safe_unserialize($multiple_files_serialized);
		if(empty($multiple_files)){
			return '';
		}
		foreach($multiple_files as $order => $file_data){
			if(!empty($file_data['WpUpload'])){
				$multiple_files[$order]['WpUpload'] = cg_gallery_transfer_import_attachment($state,$data,$file_data['WpUpload']);
			}
			if(isset($multiple_files[$order]['PdfPreview'])){
				$multiple_files[$order]['PdfPreview'] = 0;
			}
			if(!empty($file_data['Category'])){
				$old_category = intval($file_data['Category']);
				$multiple_files[$order]['Category'] = (!empty($state['maps']['categories'][$old_category])) ? $state['maps']['categories'][$old_category] : 0;
			}
		}
		return serialize($multiple_files);
	}
}

if(!function_exists('cg_gallery_transfer_import_entries_step')){
	function cg_gallery_transfer_import_entries_step(&$state,$data,$mode,$single_user_id){
		global $wpdb;
		$new_gallery_id = absint($state['new_gallery_id']);
		$table_entries = $wpdb->prefix.'contest_gal1ery';
		$table_entry_fields = $wpdb->prefix.'contest_gal1ery_entries';
		$table_votes = $wpdb->prefix.'contest_gal1ery_ip';
		$table_comments = $wpdb->prefix.'contest_gal1ery_comments';
		$table_mails = $wpdb->prefix.'contest_gal1ery_mails';
		$entries = (!empty($data['entries'])) ? $data['entries'] : array();
		$batch = array_slice($entries,intval($state['offset']),intval($state['batch_size']));
		$options = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."contest_gal1ery_options WHERE id = %d",array($new_gallery_id)));
		$wp_upload_dir = wp_upload_dir();
		foreach($batch as $entry_row){
			$old_entry_id = intval($entry_row['id']);
			$new_wp_upload = cg_gallery_transfer_import_attachment($state,$data,$entry_row['WpUpload']);
			$old_category = intval($entry_row['Category']);
			$multiple_files = cg_gallery_transfer_map_multiple_files($state,$data,$entry_row['MultipleFiles']);
			$overrides = array(
				'GalleryID' => $new_gallery_id,
				'WpUpload' => $new_wp_upload,
				'WpUserId' => cg_gallery_transfer_map_user_id($state,$entry_row['WpUserId'],'entry'),
				'Category' => (!empty($state['maps']['categories'][$old_category])) ? $state['maps']['categories'][$old_category] : 0,
				'MultipleFiles' => $multiple_files,
				'Mails' => 0,
				'PdfPreview' => 0,
				'EcommerceEntry' => 0,
				'OrderItem' => 0,
				'WpPage' => 0,
				'WpPageUser' => 0,
				'WpPageNoVoting' => 0,
				'WpPageWinner' => 0,
				'WpPageEcommerce' => 0,
				'Version' => cg_get_version_for_scripts(),
			);
			$new_entry_id = cg_gallery_transfer_insert_row($table_entries,$entry_row,$overrides,true);
			if(empty($new_entry_id)){
				$state['warnings'][] = 'Entry '.$old_entry_id.' could not be imported.';
				continue;
			}
			$state['maps']['entries'][$old_entry_id] = $new_entry_id;
			if(!empty($new_wp_upload)){
				cg_create_exif_data_and_add_to_database($new_entry_id,$new_wp_upload);
			}
			$post_title = $entry_row['NamePic'];
			if(!empty($new_wp_upload)){
				$post = get_post($new_wp_upload);
				if(!empty($post) && $post->post_title!==''){
					$post_title = $post->post_title;
				}
			}
			if(!empty($options)){
				cg_create_wp_pages($new_gallery_id,$new_entry_id,$post_title,$options,cg_get_db_version());
			}
			cg_gallery_transfer_import_entry_related_rows($state,$data,$old_entry_id,$new_entry_id,$table_entry_fields,$table_votes,$table_comments,$table_mails);
			$imported_row = $wpdb->get_row($wpdb->prepare("
				SELECT ".$wpdb->prefix."posts.*, $table_entries.*
				FROM $table_entries
				LEFT JOIN ".$wpdb->prefix."posts ON $table_entries.WpUpload = ".$wpdb->prefix."posts.ID
				WHERE $table_entries.id = %d
				LIMIT 1
			",array($new_entry_id)));
			if(!empty($imported_row) && intval($imported_row->Active)===1){
				if(!isset($imported_row->post_date)){$imported_row->post_date = '';}
				if(!isset($imported_row->post_content)){$imported_row->post_content = '';}
				if(!isset($imported_row->post_title)){$imported_row->post_title = '';}
				if(!isset($imported_row->post_name)){$imported_row->post_name = '';}
				if(!isset($imported_row->post_excerpt)){$imported_row->post_excerpt = '';}
				cg_create_json_files_when_activating($new_gallery_id,$imported_row,array(),$wp_upload_dir,null,0,array(),array(),true);
				cg_json_upload_form_info_data_files_new($new_gallery_id,array($new_entry_id),false,false,true,true);
				cg_create_comments_json_file_when_activating_image($wp_upload_dir,$new_gallery_id,$new_entry_id);
			}
		}
		$state['offset'] = intval($state['offset']) + count($batch);
		if($state['offset'] >= count($entries)){
			cg_gallery_transfer_finish_import($state,$data);
		}
	}
}

if(!function_exists('cg_gallery_transfer_import_entry_related_rows')){
	function cg_gallery_transfer_import_entry_related_rows(&$state,$data,$old_entry_id,$new_entry_id,$table_entry_fields,$table_votes,$table_comments,$table_mails){
		$new_gallery_id = absint($state['new_gallery_id']);
		if(!empty($data['entry_fields'])){
			foreach($data['entry_fields'] as $row){
				if(intval($row['pid'])!==intval($old_entry_id)){
					continue;
				}
				$old_field = intval($row['f_input_id']);
				cg_gallery_transfer_insert_row($table_entry_fields,$row,array(
					'GalleryID' => $new_gallery_id,
					'pid' => $new_entry_id,
					'f_input_id' => (!empty($state['maps']['fields'][$old_field])) ? $state['maps']['fields'][$old_field] : 0,
				),true);
			}
		}
		if(!empty($data['votes'])){
			foreach($data['votes'] as $row){
				if(intval($row['pid'])!==intval($old_entry_id)){
					continue;
				}
				$old_category = (!empty($row['Category'])) ? intval($row['Category']) : 0;
				cg_gallery_transfer_insert_row($table_votes,$row,array(
					'GalleryID' => $new_gallery_id,
					'pid' => $new_entry_id,
					'WpUserId' => cg_gallery_transfer_map_user_id($state,$row['WpUserId'],'vote'),
					'Category' => (!empty($state['maps']['categories'][$old_category])) ? $state['maps']['categories'][$old_category] : 0,
				),true);
			}
		}
		if(!empty($data['comments'])){
			foreach($data['comments'] as $row){
				if(intval($row['pid'])!==intval($old_entry_id)){
					continue;
				}
				cg_gallery_transfer_insert_row($table_comments,$row,array(
					'GalleryID' => $new_gallery_id,
					'pid' => $new_entry_id,
					'WpUserId' => cg_gallery_transfer_map_user_id($state,$row['WpUserId'],'comment'),
				),true);
			}
		}
		if(!empty($data['mails'])){
			cg_gallery_transfer_create_mails_table_if_required();
			$mail_count = 0;
			foreach($data['mails'] as $row){
				if(intval($row['pid'])!==intval($old_entry_id)){
					continue;
				}
				$inserted = cg_gallery_transfer_insert_row($table_mails,$row,array(
					'GalleryID' => $new_gallery_id,
					'pid' => $new_entry_id,
					'WpUserId' => cg_gallery_transfer_map_user_id($state,$row['WpUserId'],'entry'),
				),true);
				if(!empty($inserted)){
					$mail_count++;
				}
			}
			if($mail_count>0){
				global $wpdb;
				$table_entries = $wpdb->prefix.'contest_gal1ery';
				$wpdb->update($table_entries,array('Mails'=>$mail_count),array('id'=>$new_entry_id),array('%d'),array('%d'));
			}
		}
	}
}

if(!function_exists('cg_gallery_transfer_finish_import')){
	function cg_gallery_transfer_finish_import(&$state,$data){
		$new_gallery_id = absint($state['new_gallery_id']);
		$new_ids = array_values($state['maps']['entries']);
		if(!empty($new_ids)){
			cg_json_upload_form_info_data_files_new($new_gallery_id,$new_ids,false,false,true,true);
		}
		do_action('cg_json_upload_form',$new_gallery_id);
		do_action('cg_json_single_view_order',$new_gallery_id);
		if(function_exists('cg1l_migrate_image_stats_to_folder')){
			cg1l_migrate_image_stats_to_folder($new_gallery_id,true,false);
		}
		if(function_exists('cg1l_create_last_updated_time_file_all')){
			cg1l_create_last_updated_time_file_all($new_gallery_id);
		}
		if(function_exists('cg_network_delete_publish_state')){
			cg_network_delete_publish_state($new_gallery_id);
		}
		$state['stage'] = 'done';
		$state['status'] = 'done';
	}
}

add_action('wp_ajax_post_cg_gallery_transfer_cancel','post_cg_gallery_transfer_cancel');
if(!function_exists('post_cg_gallery_transfer_cancel')){
	function post_cg_gallery_transfer_cancel(){
		cg_gallery_transfer_require_manage_options();
		$job_id = (!empty($_POST['job_id'])) ? sanitize_text_field($_POST['job_id']) : '';
		$dir = cg_gallery_transfer_job_dir($job_id);
		if(!empty($dir) && is_dir($dir)){
			cg_gallery_transfer_delete_dir($dir);
		}
		wp_send_json_success(array('message'=>'Job cancelled.'));
	}
}
