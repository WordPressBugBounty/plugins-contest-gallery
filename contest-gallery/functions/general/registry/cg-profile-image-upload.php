<?php
if(!defined('ABSPATH')){exit;}

// One policy for new profile uploads. Existing attachments are not revalidated.
function cg_profile_image_upload_config(){
	$messages = array(
		'ThisFileTypeIsNotAllowed'=>'This file type is not allowed',
		'TheFileYouChoosedIsToBigMaxAllowedSize'=>'The selected file is too large, max allowed size',
		'invalid'=>'The profile image could not be saved.',
		'pending'=>'Processing…'
	);
	foreach(array('JPG','PNG','GIF') as $type){
		foreach(array('width','height') as $axis){
			$messages['MaximumAllowed'.ucfirst($axis).'For'.$type.'sIs']='Maximum allowed '.$axis.' for '.$type.' is';
		}
	}
	return array('maxBytes'=>min(2000000,wp_max_upload_size()),'maxWidth'=>2000,'maxHeight'=>2000,
		'mimes'=>array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','jpe'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif'),
		'messages'=>array_map('cg_member_t',$messages));
}

function cg_profile_image_upload_attributes(){
	return ' accept="image/jpeg,image/png,image/gif" data-cg-profile-image="'.esc_attr(wp_json_encode(cg_profile_image_upload_config())).'"';
}

function cg_profile_image_upload_hint(){
	$config = cg_profile_image_upload_config();
	$size = rtrim(rtrim(number_format($config['maxBytes']/1000000,6,'.',''),'0'),'.');
	return esc_html(str_replace('{size}',$size,cg_member_t('JPG, PNG or GIF. Maximum {size} MB.'))).' '.esc_html(cg_member_t('Maximum resolution')).': 2000 × 2000 px.';
}

function cg_profile_image_upload_file($files){
	if(!is_array($files)){return null;}
	// Registry/WP profile use [0], the gallery profile uses a native flat file.
	if(isset($files['name']) && is_array($files['name'])){
		$file = array();
		foreach(array('name','type','tmp_name','error','size') as $part){
			if(!isset($files[$part]) || !is_array($files[$part]) || array_keys($files[$part])!==array(0)){return null;}
			$file[$part]=$files[$part][0];
		}
		return $file;
	}
	return $files;
}

function cg_profile_image_upload_error($error){
	$data = $error->get_error_data();
	if(!is_array($data)){$data=array();}
	$message = isset($data['uploadValidation']) ? $error->get_error_message() : cg_member_t('The profile image could not be saved.');
	$data['fieldErrors']=array('profile_image'=>$message);
	return new WP_Error('profile_image',$message,$data);
}

function cg_profile_image_validate_upload($file){
	$config = cg_profile_image_upload_config();
	$general = array('ActivatePostMaxMB'=>1,'PostMaxMB'=>$config['maxBytes']/1000000);
	foreach(array('JPG','PNG','GIF') as $type){
		$general['MaxRes'.$type.'on']=1;
		$general['MaxRes'.$type.'width']=$config['maxWidth'];
		$general['MaxRes'.$type.'height']=$config['maxHeight'];
	}
	$result = cg_upload_validate_file($file,0,array('general'=>$general),false,array('mimes'=>$config['mimes'],'pdfPreview'=>false));
	return is_wp_error($result) ? cg_profile_image_upload_error($result) : $result;
}

function cg_profile_image_upload_response($error){
	$data = $error->get_error_data();
	return array('message'=>$error->get_error_message(),
		'fieldErrors'=>array('profile_image'=>$error->get_error_message()),
		'uploadValidation'=>is_array($data) && isset($data['uploadValidation']) ? $data['uploadValidation'] : null);
}

function cg_profile_image_registry_error($error){
	// Keep the registry's existing HTML/script response contract and block redirect.
	echo '<script data-cg-processing="true" data-cg-processing-error="true">cgJsClass.gallery.registry.functions.profileImageError(cgJsClass.gallery.vars.$regFormContainer,'.wp_json_encode(cg_profile_image_upload_response($error)).');</script>';
}
