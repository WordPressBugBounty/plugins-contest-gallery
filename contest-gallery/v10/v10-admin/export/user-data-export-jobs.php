<?php

if(!defined('ABSPATH')){exit;}

if(!defined('CG_USER_DATA_EXPORT_BATCH_SIZE')){
	define('CG_USER_DATA_EXPORT_BATCH_SIZE',100);
}

if(!function_exists('cg_user_data_export_error')){
	function cg_user_data_export_error($message,$status=400,$code='cg_user_data_export_error'){
		wp_send_json_error(array(
			'message' => $message,
			'code' => $code
		),$status);
	}
}

if(!function_exists('cg_user_data_export_require_ajax')){
	function cg_user_data_export_require_ajax(){
		if(!defined('DOING_AJAX') || !DOING_AJAX){
			cg_user_data_export_error('Invalid AJAX request.',400,'cg_user_data_export_invalid_ajax');
		}
		if(!current_user_can('manage_options')){
			cg_user_data_export_error('Registered users data export requires administrator rights.',403,'cg_user_data_export_missing_rights');
		}
		$cg_nonce = isset($_POST['cg_nonce']) ? sanitize_text_field(wp_unslash($_POST['cg_nonce'])) : '';
		if(empty($cg_nonce) || !wp_verify_nonce($cg_nonce,'cg_nonce')){
			cg_user_data_export_error('WP nonce security token not set or not valid anymore.',403,'cg_user_data_export_invalid_nonce');
		}
	}
}

if(!function_exists('cg_user_data_export_base_dir')){
	function cg_user_data_export_base_dir(){
		$dir = trailingslashit(get_temp_dir()).'contest-gallery-user-data-export';
		if(!is_dir($dir)){
			wp_mkdir_p($dir);
		}
		if(!is_dir($dir) || !is_writable($dir)){
			return '';
		}
		@chmod($dir,0700);
		$protection_files = array(
			'index.php' => "<?php\n// Silence is golden.\n",
			'.htaccess' => "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n",
			'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>\n"
		);
		foreach($protection_files as $file_name => $content){
			$file = trailingslashit($dir).$file_name;
			if(!file_exists($file)){
				@file_put_contents($file,$content,LOCK_EX);
				@chmod($file,0600);
			}
		}
		return $dir;
	}
}

if(!function_exists('cg_user_data_export_valid_job_id')){
	function cg_user_data_export_valid_job_id($job_id){
		return is_string($job_id) && preg_match('/^[0-9]{9,}-[a-zA-Z0-9]+$/',$job_id);
	}
}

if(!function_exists('cg_user_data_export_job_dir')){
	function cg_user_data_export_job_dir($job_id,$create=false){
		if(!cg_user_data_export_valid_job_id($job_id)){
			return '';
		}
		$base_dir = cg_user_data_export_base_dir();
		if(empty($base_dir)){
			return '';
		}
		$dir = trailingslashit($base_dir).$job_id;
		if($create && !is_dir($dir)){
			wp_mkdir_p($dir);
			if(is_dir($dir)){
				@chmod($dir,0700);
			}
		}
		return $dir;
	}
}

if(!function_exists('cg_user_data_export_state_file')){
	function cg_user_data_export_state_file($job_id){
		$dir = cg_user_data_export_job_dir($job_id,false);
		return empty($dir) ? '' : trailingslashit($dir).'state.json';
	}
}

if(!function_exists('cg_user_data_export_write_file_atomic')){
	function cg_user_data_export_write_file_atomic($file,$content){
		$dir = dirname($file);
		if(!is_dir($dir)){
			return false;
		}
		$tmp_file = $file.'.'.wp_generate_password(12,false,false).'.tmp';
		if(file_put_contents($tmp_file,$content,LOCK_EX)===false){
			@unlink($tmp_file);
			return false;
		}
		@chmod($tmp_file,0600);
		if(!@rename($tmp_file,$file)){
			@unlink($tmp_file);
			return false;
		}
		@chmod($file,0600);
		return true;
	}
}

if(!function_exists('cg_user_data_export_load_state')){
	function cg_user_data_export_load_state($job_id){
		$file = cg_user_data_export_state_file($job_id);
		if(empty($file) || !is_file($file)){
			return false;
		}
		$state = json_decode(file_get_contents($file),true);
		return is_array($state) ? $state : false;
	}
}

if(!function_exists('cg_user_data_export_save_state')){
	function cg_user_data_export_save_state($state){
		if(empty($state['job_id'])){
			return false;
		}
		$state['updated_at'] = time();
		$json = wp_json_encode($state);
		if($json===false){
			return false;
		}
		return cg_user_data_export_write_file_atomic(cg_user_data_export_state_file($state['job_id']),$json);
	}
}

if(!function_exists('cg_user_data_export_delete_dir')){
	function cg_user_data_export_delete_dir($dir){
		if(empty($dir) || !is_dir($dir)){
			return;
		}
		$base_dir_real = realpath(cg_user_data_export_base_dir());
		$dir_real = realpath($dir);
		if(empty($base_dir_real) || empty($dir_real) || dirname($dir_real)!==$base_dir_real){
			return;
		}
		$items = scandir($dir_real);
		if(is_array($items)){
			foreach($items as $item){
				if($item==='.' || $item==='..'){
					continue;
				}
				$path = $dir_real.'/'.$item;
				if(is_file($path) || is_link($path)){
					@unlink($path);
				}
			}
		}
		@rmdir($dir_real);
	}
}

if(!function_exists('cg_user_data_export_cleanup_stale_jobs')){
	function cg_user_data_export_cleanup_stale_jobs(){
		$base_dir = cg_user_data_export_base_dir();
		if(empty($base_dir) || !is_dir($base_dir)){
			return;
		}
		$items = scandir($base_dir);
		if(!is_array($items)){
			return;
		}
		$now = time();
		foreach($items as $item){
			if(!cg_user_data_export_valid_job_id($item)){
				continue;
			}
			$dir = trailingslashit($base_dir).$item;
			$state = cg_user_data_export_load_state($item);
			$updated_at = !empty($state['updated_at']) ? absint($state['updated_at']) : absint(@filemtime($dir));
			if(!empty($updated_at) && ($now-$updated_at)>DAY_IN_SECONDS){
				cg_user_data_export_delete_dir($dir);
			}
		}
	}
}
add_action('cg_user_data_export_cleanup_event','cg_user_data_export_cleanup_stale_jobs');

if(!function_exists('cg_user_data_export_cleanup_job')){
	function cg_user_data_export_cleanup_job($job_id){
		if(!cg_user_data_export_valid_job_id($job_id)){
			return;
		}
		$state = cg_user_data_export_load_state($job_id);
		if(empty($state)){
			return;
		}
		$updated_at = !empty($state['updated_at']) ? absint($state['updated_at']) : 0;
		if(!empty($updated_at) && (time()-$updated_at)<DAY_IN_SECONDS){
			wp_schedule_single_event($updated_at+DAY_IN_SECONDS,'cg_user_data_export_cleanup_job_event',array($job_id));
			return;
		}
		cg_user_data_export_delete_dir(cg_user_data_export_job_dir($job_id,false));
	}
}
add_action('cg_user_data_export_cleanup_job_event','cg_user_data_export_cleanup_job');

if(!function_exists('cg_user_data_export_owned_state')){
	function cg_user_data_export_owned_state($job_id){
		$state = cg_user_data_export_load_state($job_id);
		if(empty($state)){
			cg_user_data_export_error('Export job was not found or has expired.',404,'cg_user_data_export_job_missing');
		}
		if(empty($state['owner_id']) || absint($state['owner_id'])!==get_current_user_id()){
			cg_user_data_export_error('This export job belongs to another user.',403,'cg_user_data_export_wrong_owner');
		}
		return $state;
	}
}

if(!function_exists('cg_user_data_export_prepare_query')){
	function cg_user_data_export_prepare_query($query,$args){
		global $wpdb;
		return empty($args) ? $query : $wpdb->prepare($query,$args);
	}
}

if(!function_exists('cg_user_data_export_user_where')){
	function cg_user_data_export_user_where($search,$gallery_id,$max_user_id=0,$after_user_id=0){
		global $wpdb;
		$entries_table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
		$where = array('1=1');
		$args = array();
		if(!empty($max_user_id)){
			$where[] = 'u.ID <= %d';
			$args[] = absint($max_user_id);
		}
		if(!empty($after_user_id)){
			$where[] = 'u.ID > %d';
			$args[] = absint($after_user_id);
		}
		if($search!==''){
			$like = '%'.$wpdb->esc_like($search).'%';
			$where[] = '(u.user_login LIKE %s OR u.user_email LIKE %s)';
			$args[] = $like;
			$args[] = $like;
		}
		if(!empty($gallery_id)){
			$where[] = "EXISTS (SELECT 1 FROM $entries_table cg_export_entry WHERE cg_export_entry.wp_user_id = u.ID AND cg_export_entry.GalleryID = %d)";
			$args[] = absint($gallery_id);
		}
		return array(
			'sql' => implode(' AND ',$where),
			'args' => $args
		);
	}
}

if(!function_exists('cg_user_data_export_excluded_field_types_sql')){
	function cg_user_data_export_excluded_field_types_sql(){
		return "'main-user-name','main-nick-name','main-mail','wpfn','wpln','password','password-confirm','user-robot-field','user-robot-recaptcha-field'";
	}
}

if(!function_exists('cg_user_data_export_headers')){
	function cg_user_data_export_headers($search,$gallery_id,$max_user_id){
		global $wpdb;
		$form_table = $wpdb->prefix.'contest_gal1ery_create_user_form';
		$entries_table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
		$options_table = $wpdb->prefix.'contest_gal1ery_options';
		$users_table = $wpdb->base_prefix.'users';
		$excluded = cg_user_data_export_excluded_field_types_sql();

		$general_fields = $wpdb->get_results(
			"SELECT id, GalleryID, GeneralID, Field_Type, Field_Name, Field_Order
			FROM $form_table
			WHERE GeneralID = 1 AND Field_Type NOT IN ($excluded)
			ORDER BY GeneralID ASC, Field_Order DESC"
		);

		$where = array(
			"f.Field_Type NOT IN ($excluded)",
			'e.wp_user_id >= 1',
			'u.ID <= %d'
		);
		$args = array(absint($max_user_id));
		if($search!==''){
			$like = '%'.$wpdb->esc_like($search).'%';
			$where[] = '(u.user_login LIKE %s OR u.user_email LIKE %s)';
			$args[] = $like;
			$args[] = $like;
		}
		if(!empty($gallery_id)){
			$where[] = 'e.GalleryID = %d';
			$args[] = absint($gallery_id);
		}
		$query = "SELECT DISTINCT f.id, f.GalleryID, f.GeneralID, f.Field_Type, f.Field_Name, f.Field_Order
			FROM $form_table f
			INNER JOIN $entries_table e ON f.id = e.f_input_id
			INNER JOIN $options_table o ON f.GalleryID = o.id
			INNER JOIN $users_table u ON u.ID = e.wp_user_id
			WHERE ".implode(' AND ',$where)."
			ORDER BY f.GalleryID ASC, f.Field_Order DESC";
		$gallery_fields = $wpdb->get_results(cg_user_data_export_prepare_query($query,$args));

		$fields = array();
		$known_ids = array();
		$profile_field_id = 0;
		foreach(array_merge((array)$general_fields,(array)$gallery_fields) as $field){
			$field_id = absint($field->id);
			if(empty($field_id) || isset($known_ids[$field_id])){
				continue;
			}
			$known_ids[$field_id] = true;
			$label = (string)$field->Field_Name;
			if(!empty($field->GalleryID)){
				$label .= ' (gallery id = '.absint($field->GalleryID).')';
			}
			$fields[] = array(
				'id' => $field_id,
				'label' => $label,
				'field_type' => (string)$field->Field_Type
			);
			if($field->Field_Type==='profile-image' && !empty($field->GeneralID) && empty($profile_field_id)){
				$profile_field_id = $field_id;
			}
		}
		return array(
			'fields' => $fields,
			'profile_field_id' => $profile_field_id
		);
	}
}

if(!function_exists('cg_user_data_export_write_csv_rows')){
	function cg_user_data_export_write_csv_rows($file,$rows,$with_bom=false){
		$tmp_file = $file.'.'.wp_generate_password(12,false,false).'.tmp';
		$fp = @fopen($tmp_file,'wb');
		if($fp===false){
			return false;
		}
		if($with_bom){
			fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
		}
		foreach($rows as $row){
			$row = cg_neutralize_csv_array($row);
			if(fputcsv($fp,$row,';')===false){
				fclose($fp);
				@unlink($tmp_file);
				return false;
			}
		}
		fclose($fp);
		@chmod($tmp_file,0600);
		if(!@rename($tmp_file,$file)){
			@unlink($tmp_file);
			return false;
		}
		@chmod($file,0600);
		return true;
	}
}

if(!function_exists('cg_user_data_export_download_url')){
	function cg_user_data_export_download_url($job_id){
		return add_query_arg(array(
			'action' => 'cg_user_data_export_download',
			'job_id' => $job_id,
			'download_nonce' => wp_create_nonce('cg_user_data_export_download_'.$job_id)
		),admin_url('admin-post.php'));
	}
}

if(!function_exists('cg_user_data_export_public_state')){
	function cg_user_data_export_public_state($state){
		$total = isset($state['total']) ? absint($state['total']) : 0;
		$processed = isset($state['processed']) ? absint($state['processed']) : 0;
		$done = !empty($state['status']) && $state['status']==='done';
		$percent = $total>0 ? min(100,(int)floor(($processed/$total)*100)) : ($done ? 100 : 0);
		$return = array(
			'job_id' => $state['job_id'],
			'status' => $state['status'],
			'total' => $total,
			'processed' => $processed,
			'percent' => $percent,
			'done' => $done
		);
		if($done){
			$return['download_url'] = cg_user_data_export_download_url($state['job_id']);
		}
		return $return;
	}
}

if(!function_exists('post_cg_user_data_export_start')){
	function post_cg_user_data_export_start(){
		cg_user_data_export_require_ajax();
		global $wpdb;
		cg_user_data_export_cleanup_stale_jobs();
		$search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
		$gallery_id = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
		$users_table = $wpdb->base_prefix.'users';
		$where_data = cg_user_data_export_user_where($search,$gallery_id);
		$max_query = "SELECT MAX(u.ID) FROM $users_table u WHERE ".$where_data['sql'];
		$max_user_id = absint($wpdb->get_var(cg_user_data_export_prepare_query($max_query,$where_data['args'])));
		$where_data = cg_user_data_export_user_where($search,$gallery_id,$max_user_id);
		$count_query = "SELECT COUNT(*) FROM $users_table u WHERE ".$where_data['sql'];
		$total = absint($wpdb->get_var(cg_user_data_export_prepare_query($count_query,$where_data['args'])));
		$header_data = cg_user_data_export_headers($search,$gallery_id,$max_user_id);

		$job_id = time().'-'.wp_generate_password(24,false,false);
		$job_dir = cg_user_data_export_job_dir($job_id,true);
		if(empty($job_dir)){
			cg_user_data_export_error('A secure temporary export directory could not be created.',500,'cg_user_data_export_temp_dir');
		}

		$header = array(
			'WP user id',
			'WP username',
			'WP nickname',
			'WP first name',
			'WP last name',
			'WP email',
			'WP role'
		);
		foreach($header_data['fields'] as $field){
			$header[] = $field['label'];
		}
		if(!cg_user_data_export_write_csv_rows(trailingslashit($job_dir).'header.csvpart',array($header),true)){
			cg_user_data_export_delete_dir($job_dir);
			cg_user_data_export_error('The CSV header could not be written.',500,'cg_user_data_export_header_write');
		}

		$state = array(
			'job_id' => $job_id,
			'owner_id' => get_current_user_id(),
			'created_at' => time(),
			'updated_at' => time(),
			'status' => $total>0 ? 'processing' : 'done',
			'search' => $search,
			'gallery_id' => $gallery_id,
			'max_user_id' => $max_user_id,
			'last_user_id' => 0,
			'total' => $total,
			'processed' => 0,
			'chunk_index' => 0,
			'fields' => $header_data['fields'],
			'profile_field_id' => $header_data['profile_field_id']
		);
		if(!cg_user_data_export_save_state($state)){
			cg_user_data_export_delete_dir($job_dir);
			cg_user_data_export_error('The export job state could not be saved.',500,'cg_user_data_export_state_write');
		}
		wp_schedule_single_event(time()+DAY_IN_SECONDS,'cg_user_data_export_cleanup_job_event',array($job_id));
		wp_send_json_success(cg_user_data_export_public_state($state));
	}
}
add_action('wp_ajax_post_cg_user_data_export_start','post_cg_user_data_export_start');

if(!function_exists('cg_user_data_export_placeholders')){
	function cg_user_data_export_placeholders($count,$placeholder='%d'){
		return implode(',',array_fill(0,absint($count),$placeholder));
	}
}

if(!function_exists('cg_user_data_export_registry_value')){
	function cg_user_data_export_registry_value($entry){
		if(!empty($entry->Field_Type) && $entry->Field_Type==='user-check-agreement-field'){
			return ($entry->Checked==1 || empty($entry->Version)) ? 'checked' : 'not checked';
		}
		if(!empty($entry->Field_Content)){
			return $entry->Field_Content;
		}
		return '';
	}
}

if(!function_exists('cg_user_data_export_meta_value')){
	function cg_user_data_export_meta_value($value){
		if(strpos($value,'yes(cg-user-checked) ---')!==false){
			return 'checked';
		}
		if(strpos($value,'yes(cg-user-not-checked) ---')!==false){
			return 'not checked';
		}
		return $value;
	}
}

if(!function_exists('post_cg_user_data_export_step')){
	function post_cg_user_data_export_step(){
		cg_user_data_export_require_ajax();
		global $wpdb;
		$job_id = isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
		$state = cg_user_data_export_owned_state($job_id);
		$job_dir = cg_user_data_export_job_dir($job_id,false);
		$lock = @fopen(trailingslashit($job_dir).'job.lock','c');
		if($lock===false || !flock($lock,LOCK_EX|LOCK_NB)){
			if(is_resource($lock)){
				fclose($lock);
			}
			cg_user_data_export_error('This export job is already processing a step.',409,'cg_user_data_export_busy');
		}
		@chmod(trailingslashit($job_dir).'job.lock',0600);
		$state = cg_user_data_export_owned_state($job_id);
		if($state['status']==='done'){
			flock($lock,LOCK_UN);
			fclose($lock);
			wp_send_json_success(cg_user_data_export_public_state($state));
		}

		$users_table = $wpdb->base_prefix.'users';
		$where_data = cg_user_data_export_user_where($state['search'],$state['gallery_id'],$state['max_user_id'],$state['last_user_id']);
		$query = "SELECT u.ID, u.user_login, u.user_nicename, u.user_email
			FROM $users_table u
			WHERE ".$where_data['sql']."
			ORDER BY u.ID ASC
			LIMIT ".absint(CG_USER_DATA_EXPORT_BATCH_SIZE);
		$users = $wpdb->get_results(cg_user_data_export_prepare_query($query,$where_data['args']));
		if(empty($users)){
			$state['status'] = 'done';
			$state['total'] = absint($state['processed']);
			cg_user_data_export_save_state($state);
			flock($lock,LOCK_UN);
			fclose($lock);
			wp_send_json_success(cg_user_data_export_public_state($state));
		}

		$user_ids = array();
		foreach($users as $user){
			$user_ids[] = absint($user->ID);
		}
		$user_placeholders = cg_user_data_export_placeholders(count($user_ids));
		$user_values = array();
		$roles = array();
		$capabilities_key = $wpdb->prefix.'capabilities';
		$usermeta_table = $wpdb->base_prefix.'usermeta';
		$meta_args = array_merge($user_ids,array('first_name','last_name',$capabilities_key,'%cg_custom_field_id_%'));
		$meta_query = "SELECT user_id, meta_key, meta_value
			FROM $usermeta_table
			WHERE user_id IN ($user_placeholders)
			AND (meta_key IN (%s,%s,%s) OR meta_key LIKE %s)
			ORDER BY user_id ASC";
		$meta_rows = $wpdb->get_results(cg_user_data_export_prepare_query($meta_query,$meta_args));
		$wp_roles = wp_roles();
		foreach((array)$meta_rows as $meta){
			$user_id = absint($meta->user_id);
			if(!isset($user_values[$user_id])){
				$user_values[$user_id] = array(
					'first_name' => '',
					'last_name' => '',
					'fields' => array()
				);
			}
			if($meta->meta_key==='first_name' || $meta->meta_key==='last_name'){
				$user_values[$user_id][$meta->meta_key] = $meta->meta_value;
			}elseif($meta->meta_key===$capabilities_key){
				$capabilities = maybe_unserialize($meta->meta_value);
				if(is_array($capabilities)){
					foreach($capabilities as $role_key => $enabled){
						if($enabled && isset($wp_roles->roles[$role_key])){
							$roles[$user_id] = $wp_roles->roles[$role_key]['name'];
							break;
						}
					}
				}
			}elseif(preg_match('/cg_custom_field_id_([0-9]+)/',$meta->meta_key,$matches)){
				$field_id = absint($matches[1]);
				if(!empty($meta->meta_value)){
					$user_values[$user_id]['fields'][$field_id] = cg_user_data_export_meta_value($meta->meta_value);
				}
			}
		}

		$field_ids = array();
		foreach($state['fields'] as $field){
			$field_ids[] = absint($field['id']);
		}
		if(!empty($field_ids)){
			$entries_table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
			$field_placeholders = cg_user_data_export_placeholders(count($field_ids));
			$entry_args = array_merge($user_ids,$field_ids);
			$entry_query = "SELECT wp_user_id, f_input_id, Field_Type, Field_Content, Checked, Version
				FROM $entries_table
				WHERE wp_user_id IN ($user_placeholders)
				AND f_input_id IN ($field_placeholders)
				ORDER BY wp_user_id ASC";
			$entry_rows = $wpdb->get_results(cg_user_data_export_prepare_query($entry_query,$entry_args));
			foreach((array)$entry_rows as $entry){
				$user_id = absint($entry->wp_user_id);
				if(!isset($user_values[$user_id])){
					$user_values[$user_id] = array('first_name'=>'','last_name'=>'','fields'=>array());
				}
				$user_values[$user_id]['fields'][absint($entry->f_input_id)] = cg_user_data_export_registry_value($entry);
			}
		}

		if(!empty($state['profile_field_id'])){
			$gallery_table = $wpdb->prefix.'contest_gal1ery';
			$profile_query = "SELECT id, WpUserId, WpUpload
				FROM $gallery_table
				WHERE WpUserId IN ($user_placeholders) AND IsProfileImage = 1
				ORDER BY id ASC";
			$profile_rows = $wpdb->get_results(cg_user_data_export_prepare_query($profile_query,$user_ids));
			$attachment_ids = array();
			foreach((array)$profile_rows as $profile){
				$attachment_ids[] = absint($profile->WpUpload);
			}
			$attachment_ids = array_values(array_unique(array_filter($attachment_ids)));
			if(!empty($attachment_ids)){
				get_posts(array(
					'post_type' => 'attachment',
					'post__in' => $attachment_ids,
					'posts_per_page' => -1,
					'post_status' => 'inherit',
					'orderby' => 'post__in',
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false
				));
			}
			foreach((array)$profile_rows as $profile){
				$image = wp_get_attachment_image_src(absint($profile->WpUpload),'large');
				if(!empty($image[0])){
					$user_id = absint($profile->WpUserId);
					if(!isset($user_values[$user_id])){
						$user_values[$user_id] = array('first_name'=>'','last_name'=>'','fields'=>array());
					}
					$user_values[$user_id]['fields'][absint($state['profile_field_id'])] = $image[0];
				}
			}
		}

		$rows = array();
		foreach($users as $user){
			$user_id = absint($user->ID);
			$values = isset($user_values[$user_id]) ? $user_values[$user_id] : array('first_name'=>'','last_name'=>'','fields'=>array());
			$row = array(
				$user_id,
				$user->user_login,
				$user->user_nicename,
				$values['first_name'],
				$values['last_name'],
				$user->user_email,
				isset($roles[$user_id]) ? $roles[$user_id] : ''
			);
			foreach($state['fields'] as $field){
				$field_id = absint($field['id']);
				$row[] = isset($values['fields'][$field_id]) ? $values['fields'][$field_id] : '';
			}
			$rows[] = $row;
		}

		$chunk_index = absint($state['chunk_index']);
		$chunk_file = trailingslashit($job_dir).sprintf('chunk-%06d.csvpart',$chunk_index);
		if(!cg_user_data_export_write_csv_rows($chunk_file,$rows,false)){
			flock($lock,LOCK_UN);
			fclose($lock);
			cg_user_data_export_error('The next CSV part could not be written.',500,'cg_user_data_export_chunk_write');
		}
		$last_user = end($users);
		$state['last_user_id'] = absint($last_user->ID);
		$state['processed'] = absint($state['processed'])+count($users);
		$state['chunk_index'] = $chunk_index+1;
		if(count($users)<CG_USER_DATA_EXPORT_BATCH_SIZE || $state['processed']>=$state['total']){
			$state['status'] = 'done';
			$state['total'] = absint($state['processed']);
		}
		if(!cg_user_data_export_save_state($state)){
			flock($lock,LOCK_UN);
			fclose($lock);
			cg_user_data_export_error('The export progress could not be saved.',500,'cg_user_data_export_state_write');
		}
		flock($lock,LOCK_UN);
		fclose($lock);
		wp_send_json_success(cg_user_data_export_public_state($state));
	}
}
add_action('wp_ajax_post_cg_user_data_export_step','post_cg_user_data_export_step');

if(!function_exists('post_cg_user_data_export_cancel')){
	function post_cg_user_data_export_cancel(){
		cg_user_data_export_require_ajax();
		$job_id = isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
		cg_user_data_export_owned_state($job_id);
		$job_dir = cg_user_data_export_job_dir($job_id,false);
		$lock = @fopen(trailingslashit($job_dir).'job.lock','c');
		if($lock!==false){
			flock($lock,LOCK_EX);
		}
		wp_clear_scheduled_hook('cg_user_data_export_cleanup_job_event',array($job_id));
		cg_user_data_export_delete_dir($job_dir);
		if($lock!==false){
			flock($lock,LOCK_UN);
			fclose($lock);
		}
		wp_send_json_success(array('cancelled'=>true));
	}
}
add_action('wp_ajax_post_cg_user_data_export_cancel','post_cg_user_data_export_cancel');

if(!function_exists('cg_user_data_export_download')){
	function cg_user_data_export_download(){
		if(!current_user_can('manage_options')){
			wp_die('Registered users data export requires administrator rights.','',array('response'=>403));
		}
		$job_id = isset($_GET['job_id']) ? sanitize_text_field(wp_unslash($_GET['job_id'])) : '';
		if(!cg_user_data_export_valid_job_id($job_id)){
			wp_die('Invalid export job.','',array('response'=>400));
		}
		check_admin_referer('cg_user_data_export_download_'.$job_id,'download_nonce');
		$state = cg_user_data_export_load_state($job_id);
		if(empty($state) || empty($state['owner_id']) || absint($state['owner_id'])!==get_current_user_id()){
			wp_die('Export job was not found or belongs to another user.','',array('response'=>403));
		}
		if(empty($state['status']) || $state['status']!=='done'){
			wp_die('Export job is not finished yet.','',array('response'=>409));
		}
		$job_dir = cg_user_data_export_job_dir($job_id,false);
		$files = array(trailingslashit($job_dir).'header.csvpart');
		for($i=0;$i<absint($state['chunk_index']);$i++){
			$files[] = trailingslashit($job_dir).sprintf('chunk-%06d.csvpart',$i);
		}
		nocache_headers();
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="wordpress-users-export-from-contest-gallery.csv"');
		header('X-Content-Type-Options: nosniff');
		header('X-Accel-Buffering: no');
		while(ob_get_level()){
			ob_end_clean();
		}
		foreach($files as $file){
			if(!is_file($file)){
				continue;
			}
			$fp = fopen($file,'rb');
			if($fp===false){
				continue;
			}
			while(!feof($fp)){
				echo fread($fp,1048576);
				flush();
			}
			fclose($fp);
		}
		exit;
	}
}
add_action('admin_post_cg_user_data_export_download','cg_user_data_export_download');

?>
