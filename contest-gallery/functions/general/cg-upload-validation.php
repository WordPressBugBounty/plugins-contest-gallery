<?php
if(!defined('ABSPATH')){exit;}

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

// Validate the complete batch before any media or credit mutation. Keep multipart
// integer errors and temporary paths intact, accepting only flat or [slot][0] files.
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
