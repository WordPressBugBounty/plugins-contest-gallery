<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_upload_validate_file')){
function cg_upload_validate_file($file,$gid,$options,$stored = false,$rules = null){
	$error = new WP_Error('upload','The file does not meet the gallery upload requirements.');
	if(!is_array($file) || !isset($file['error'],$file['tmp_name'],$file['name']) || !is_string($file['tmp_name']) || !is_string($file['name']) || !is_int($file['error'])){return $error;}
	if($rules===null){$rules = cg_member_replacement_upload_rules($gid,false);}
	$extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
	$typeError = cg_member_replacement_upload_error('ThisFileTypeIsNotAllowed','This file type is not allowed');
	if(empty($rules['mimes'][$extension])){return $typeError;}
	$image = in_array($extension,array('jpg','jpeg','jpe','png','gif','ico'),true);
	$general = isset($options['general']) ? $options['general'] : array();
	$limit = cg_member_replacement_upload_limit($options,$image);
	$sizeError = cg_member_replacement_upload_error('TheFileYouChoosedIsToBigMaxAllowedSize','The selected file is too large, max allowed size',$limit/1000000,'MB');
	if($file['error']===UPLOAD_ERR_INI_SIZE || $file['error']===UPLOAD_ERR_FORM_SIZE){return $sizeError;}
	if($file['error']!==UPLOAD_ERR_OK || !($stored && cg_upload_job_phase('finalize') ? is_file($file['tmp_name']) : is_uploaded_file($file['tmp_name']))){return $error;}
	$bytes = filesize($file['tmp_name']);
	if(!$bytes){return $error;}
	if($bytes>$limit){return $sizeError;}
	$checked = wp_check_filetype_and_ext($file['tmp_name'],$file['name'],$rules['mimes']);
	$checked = cg_member_replacement_check_icon($checked,$file['tmp_name'],$file['name'],$rules['mimes']);
	// Do not accept WordPress's extension correction for a disguised image.
	if(empty($checked['type']) || empty($checked['ext']) || strtolower($checked['ext'])!==$extension || !empty($checked['proper_filename'])){return $typeError;}
	if($image){
		$size = @getimagesize($file['tmp_name']);
		$mime = $checked['type']==='image/x-icon' ? 'image/vnd.microsoft.icon' : $checked['type'];
		if(!$size || ($size['mime']!==$mime && $size['mime']!==$checked['type'])){return $error;}
		// Browser Image dimensions include EXIF rotation. Use the actual file at
		// both receive and finalize, not potentially normalized attachment metadata.
		if($mime==='image/jpeg' && cg_member_replacement_jpeg_orientation($file['tmp_name'])>=5){
			$width=$size[0]; $size[0]=$size[1]; $size[1]=$width;
		}
		$type = strtoupper(in_array($extension,array('jpeg','jpe'),true) ? 'jpg' : $extension);
		foreach(array('width'=>0,'height'=>1) as $axis=>$index){
			foreach(array('MinRes','MaxRes') as $prefix){
				$key = $prefix.$type;
				if(!empty($general[$key.'on']) && !empty($general[$key.$axis]) && (($prefix==='MinRes' && $size[$index]<$general[$key.$axis]) || ($prefix==='MaxRes' && $size[$index]>$general[$key.$axis]))){
					$languageKey = ($prefix==='MinRes' ? 'MinimumRequired' : 'MaximumAllowed').ucfirst($axis).'For'.$type.'sIs';
					$text = ($prefix==='MinRes' ? 'Minimum required ' : 'Maximum allowed ').$axis.' for '.$type.' is';
					return cg_member_replacement_upload_error($languageKey,$text,(int)$general[$key.$axis],'px');
				}
			}
		}
	}
	if($extension==='pdf' && strpos(file_get_contents($file['tmp_name'],false,null,0,1024),'%PDF-')!==0){return $error;}
	$rules['extension']=$extension;
	$rules['pdfPreview']=$extension==='pdf' && $rules['pdfPreview'];
	$rules['name']=sanitize_file_name($file['name']);
	$rules['type']=$checked['type'];
	$rules['size']=$bytes;
	return $rules;
}
}

// Validate the complete batch before any media or credit mutation. Keep multipart
// integer errors and temporary paths intact, accepting only flat or [slot][0] files.
if(!function_exists('cg_upload_validate_files')){
function cg_upload_validate_files($files,$gid,$options,$stored = false){
	if(!is_array($files) || empty($files['name']) || !is_array($files['name'])){return new WP_Error('upload','No files were received.');}
	$rules = cg_member_replacement_upload_rules($gid,false);
	// Normal uploads have historically excluded ICO from their supported formats.
	unset($rules['mimes']['ico']);
	foreach($files['name'] as $slot=>$unused){
		$file = array();
		$nested = is_array($unused);
		foreach(array('name','type','tmp_name','error','size') as $part){
			if(!isset($files[$part]) || !is_array($files[$part]) || !array_key_exists($slot,$files[$part])){return new WP_Error('upload','The file does not meet the gallery upload requirements.');}
			$value = $files[$part][$slot];
			if($nested){
				if(!is_array($value) || array_keys($value)!==array(0)){return new WP_Error('upload','The file does not meet the gallery upload requirements.');}
				$value=$value[0];
			}elseif(is_array($value)){return new WP_Error('upload','The file does not meet the gallery upload requirements.');}
			$file[$part]=$value;
		}
		$valid = cg_upload_validate_file($file,$gid,$options,$stored,$rules);
		if(is_wp_error($valid)){return $valid;}
		foreach(array('name','type','size') as $part){$files[$part][$slot]=$nested ? array($valid[$part]) : $valid[$part];}
	}
	return $files;
}
}

// Keep stored PRO choices across edition switches, but never enforce them in FREE.
if(!function_exists('cg_upload_category_limit')){
function cg_upload_category_limit($value){
	return cg_get_version()==='contest-gallery-pro' && is_scalar($value) ? max(0,(int)$value) : 0;
}
}

// A fresh viewer-bound snapshot also releases slots after deletion or category edits.
if(!function_exists('cg_upload_category_state')){
function cg_upload_category_state($gid){
	global $wpdb;
	if(cg_get_version()!=='contest-gallery-pro'){return null;}
	$options = $wpdb->get_row($wpdb->prepare("SELECT RegUserMaxUploadPerCategory, RegUserUploadOnly FROM {$wpdb->prefix}contest_gal1ery_pro_options WHERE GalleryID=%d",$gid));
	if(!$options || !cg_upload_category_limit($options->RegUserMaxUploadPerCategory)){return null;}
	$mode = (int)$options->RegUserUploadOnly;
	if(!in_array($mode,array(1,2,3),true)){return null;}
	$column = $mode===1 ? 'WpUserId' : ($mode===2 ? 'CookieId' : 'IP');
	$identity = $mode===1 ? get_current_user_id() : ($mode===2 ? cg_get_valid_frontend_cookie($gid,'upload') : cg1l_sanitize_method(cg_get_user_ip()));
	if(!$identity || $identity==='unknown'){return null;}
	$rows = $wpdb->get_results($wpdb->prepare("SELECT Category, COUNT(*) AS Amount FROM {$wpdb->prefix}contest_gal1ery WHERE GalleryID=%d AND $column=%s GROUP BY Category",$gid,$identity));
	if($wpdb->last_error){return null;}
	$state = array('total'=>0,'categories'=>array());
	foreach($rows as $row){$state['categories'][(int)$row->Category]=(int)$row->Amount;$state['total']+=(int)$row->Amount;}
	return $state;
}
}

// Match the entry writer's flat field tuples, using database-owned category fields.
if(!function_exists('cg_upload_entry_categories')){
function cg_upload_entry_categories($gid,$input,$names,$additional){
	global $wpdb;
	$fields = $wpdb->get_results($wpdb->prepare("SELECT id, Field_Content FROM {$wpdb->prefix}contest_gal1ery_f_input WHERE GalleryID=%d AND Field_Type='selectc-f' AND Active=1",$gid),OBJECT_K);
	$allowed = array_map('intval',$wpdb->get_col($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_categories WHERE GalleryID=%d AND Active=1",$gid)));
	$error = new WP_Error('upload','Please select a valid category.');
	$result = array();
	foreach($names as $slot=>$name){
		$values = is_array($name) ? (isset($input[$slot]) ? $input[$slot] : array()) : $input;
		if(!is_array($values)){return $error;}
		$values = array_values($values); $category = 0; $seen = array();
		for($i=0;$i<count($values);){
			if(!isset($values[$i+1],$values[$i+2]) || !is_scalar($values[$i]) || !is_scalar($values[$i+1])){return $error;}
			$type = $values[$i]; $field = absint($values[$i+1]);
			// Legacy unchecked checkboxes submit their three metadata values only.
			$length = $type==='cb' && (!isset($values[$i+3]) || $values[$i+3]!=='checked') ? 3 : 4;
			if($type==='sec' || isset($fields[$field])){
				if($type!=='sec' || !isset($fields[$field]) || $seen || !isset($values[$i+3]) || !is_scalar($values[$i+3]) || !preg_match('/^[0-9]+$/',(string)$values[$i+3])){return $error;}
				$category = (int)$values[$i+3]; $seen[$field] = true;
				if($category && !in_array($category,$allowed,true)){return $error;}
			}
			$i += $length;
		}
		foreach($fields as $field=>$row){
			$content = maybe_unserialize($row->Field_Content);
			if(!empty($content['mandatory']) && $content['mandatory']==='on' && (empty($seen[$field]) || !$category)){return $error;}
		}
		$result[$slot] = $category;
		if($additional){break;}
	}
	return $result;
}
}

if(!function_exists('cg_upload_check_category_limit')){
function cg_upload_check_category_limit($gid,$limit,$mode,$categories,$user,$cookie,$ip){
	global $wpdb;
	$limit = cg_upload_category_limit($limit);
	if(!$limit || !$categories || !in_array((int)$mode,array(1,2,3),true)){return true;}
	$column = $mode==1 ? 'WpUserId' : ($mode==2 ? 'CookieId' : 'IP');
	$identity = $mode==1 ? $user : ($mode==2 ? $cookie : $ip);
	if(!$identity || $identity==='unknown'){return new WP_Error('upload','Upload recognition failed. Please reload the page and try again.');}
	// Jobs already hold this lock. Classic uploads share it through request completion.
	$capacity = 'capacity:'.absint($gid);
	$ownsLock = $wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s) = CONNECTION_ID()',cg_upload_job_lock_name($capacity)));
	if((string)$ownsLock!=='1'){
		if(cg_upload_job_phase('receive') || cg_upload_job_phase('finalize') || !cg_upload_job_lock($capacity)){return new WP_Error('upload','Another upload is being accepted. Please try again.');}
		register_shutdown_function('cg_upload_job_unlock',$capacity);
	}
	foreach(array_count_values($categories) as $category=>$requested){
		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}contest_gal1ery WHERE GalleryID=%d AND $column=%s AND Category=%d",$gid,$identity,$category));
		if($count===null){return new WP_Error('upload','Please try again.');}
		$count += cg_upload_job_pending_entries($gid,0,$mode==1 ? $user : 0,$mode==2 ? $cookie : '',$mode==3 ? $ip : '',$category);
		if($count+$requested>$limit){
			$error = cg_member_replacement_upload_error('MaximumAmountOfUploadsPerCategoryIs','Maximum number of uploads for this category',$limit);
			$data = $error->get_error_data(); $data['categoryUploadRejected']=true; $data['categoryUploadState']=cg_upload_category_state($gid);
			$error->add_data($data); return $error;
		}
	}
	return true;
}
}

// Check a move before any other entry fields are written, under the upload capacity lock.
if(!function_exists('cg_upload_check_category_change')){
function cg_upload_check_category_change($gid,$pid,$category,$user){
	global $wpdb;
	if(cg_get_version()!=='contest-gallery-pro'){return true;}
	$options = $wpdb->get_row($wpdb->prepare("SELECT RegUserMaxUploadPerCategory, RegUserUploadOnly FROM {$wpdb->prefix}contest_gal1ery_pro_options WHERE GalleryID=%d",$gid));
	if(!$options || !cg_upload_category_limit($options->RegUserMaxUploadPerCategory) || !in_array((int)$options->RegUserUploadOnly,array(1,2,3),true)){return true;}
	$capacity = 'capacity:'.absint($gid);
	if(!cg_upload_job_lock($capacity)){return new WP_Error('upload','Another upload is being accepted. Please try again.');}
	register_shutdown_function('cg_upload_job_unlock',$capacity);
	$row = $wpdb->get_row($wpdb->prepare("SELECT Category, WpUserId, CookieId, IP, OrderItem FROM {$wpdb->prefix}contest_gal1ery WHERE GalleryID=%d AND id=%d AND WpUserId=%d",$gid,$pid,$user));
	if(!$row){return new WP_Error('upload','Please reload the page and try again.');}
	if((int)$row->Category===$category || !empty($row->OrderItem)){return true;}
	if($category && !$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_categories WHERE id=%d AND GalleryID=%d AND Active=1",$category,$gid))){return new WP_Error('upload','Please select a valid category.');}
	$mode = (int)$options->RegUserUploadOnly;
	// A move retains the original uploader identity. It does not adopt this browser's cookie/IP.
	if(($mode===2 && !$row->CookieId) || ($mode===3 && (!$row->IP || $row->IP==='unknown'))){return true;}
	return cg_upload_check_category_limit($gid,$options->RegUserMaxUploadPerCategory,$mode,array($category),$row->WpUserId,$row->CookieId,$row->IP);
}
}
