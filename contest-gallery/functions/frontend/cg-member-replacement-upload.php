<?php
if(!defined('ABSPATH')){exit;}
require_once dirname(__DIR__).'/general/cg-upload-validation.php';

if(!function_exists('cg_member_replacement_file_types')){
function cg_member_replacement_file_types(){
	return array('jpg'=>'image/jpeg','jpeg'=>'image/jpeg','jpe'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','ico'=>'image/x-icon','pdf'=>'application/pdf','zip'=>'application/zip','txt'=>'text/plain','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','csv'=>'text/csv','mp3'=>'audio/mpeg','m4a'=>'audio/mpeg','ogg'=>'audio/ogg','wav'=>'audio/wav','mp4'=>'video/mp4','mov'=>'video/quicktime','webm'=>'video/webm','ppt'=>'application/vnd.ms-powerpoint','pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation');
}
}

if(!function_exists('cg_member_replacement_accept_extension')){
function cg_member_replacement_accept_extension($extension){return '.'.$extension;}
}

if(!function_exists('cg_member_replacement_attachment_type')){
function cg_member_replacement_attachment_type($attachment){
	$post = get_post($attachment);
	if(!$post || $post->post_type!=='attachment'){return '';}
	$extension = strtolower(pathinfo(get_attached_file($attachment),PATHINFO_EXTENSION));
	$types = cg_member_replacement_file_types();
	return isset($types[$extension]) ? $extension : '';
}
}

// Existing backend-created embeds are replacement sources, never upload MIME types.
if(!function_exists('cg_member_replacement_social_source')){
function cg_member_replacement_social_source($id){
	$id = absint($id);
	if(!$id){return false;}
	$post = get_post($id);
	if(!$post || $post->post_type!=='contest-gallery'){return false;}
	$types = array(
		'contest-gallery-youtube'=>array('extension'=>'ytb','label'=>'YouTube'),
		'contest-gallery-instagram'=>array('extension'=>'inst','label'=>'Instagram'),
		'contest-gallery-tiktok'=>array('extension'=>'tkt','label'=>'TikTok'),
		'contest-gallery-twitter'=>array('extension'=>'twt','label'=>'X')
	);
	if(!isset($types[$post->post_mime_type])){return false;}
	$source = $types[$post->post_mime_type];
	$title = trim(wp_strip_all_tags($post->post_title));
	$source['name'] = $source['label'].($title!=='' ? ': '.$title : '');
	return $source;
}
}

if(!function_exists('cg_member_replacement_upload_rules')){
function cg_member_replacement_upload_rules($gid,$replacement = true){
	global $wpdb;
	// Use the same stored upload field and image switches as the ordinary frontend upload.
	$field = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT Field_Content FROM ".$wpdb->prefix."contest_gal1ery_f_input WHERE GalleryID=%d AND Field_Type='image-f' LIMIT 1",$gid)));
	$field = is_array($field) ? $field : array();
	$pro = $wpdb->get_row($wpdb->prepare('SELECT AllowUploadJPG,AllowUploadPNG,AllowUploadGIF,AllowUploadICO,PdfPreviewFrontend FROM '.$wpdb->prefix.'contest_gal1ery_pro_options WHERE GalleryID=%d',$gid),ARRAY_A);
	$pro = is_array($pro) ? $pro : array();
	$mimes = array();
	$wordpress = get_allowed_mime_types();
	foreach(cg_member_replacement_file_types() as $extension=>$mime){
		$image = in_array($extension,array('jpg','jpeg','jpe','png','gif','ico'),true);
		if($image){
			$key = 'AllowUpload'.strtoupper(in_array($extension,array('jpeg','jpe'),true) ? 'jpg' : $extension);
			if(empty($field['file-type-img']) || $field['file-type-img']!=='img' || (isset($pro[$key]) && !$pro[$key])){continue;}
		}else{
			if(empty($field['alternative-file-type-'.$extension])){continue;}
			// Match the formats available in the FREE upload-field editor, also after a downgrade.
			if($replacement && cg_get_version()==='contest-gallery' && !in_array($extension,array('txt','doc','xls','csv','ppt'),true)){continue;}
		}
		$checked = wp_check_filetype('file.'.$extension,$wordpress);
		if(!empty($checked['type'])){$mimes[$extension]=$checked['type'];}
	}
	return array('mimes'=>$mimes,'pdfPreview'=>cg_get_version()!=='contest-gallery' && !empty($pro['PdfPreviewFrontend']));
}
}

// PHP/fileinfo can report Microsoft's ICO alias while WordPress allows image/x-icon.
if(!function_exists('cg_member_replacement_check_icon')){
function cg_member_replacement_check_icon($checked,$path,$name,$mimes){
	if(strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='ico' || empty($mimes['ico'])){return $checked;}
	$size = @getimagesize($path);
	if($size && $size[2]===IMAGETYPE_ICO && $size['mime']==='image/vnd.microsoft.icon'){
		return array('ext'=>'ico','type'=>$mimes['ico'],'proper_filename'=>false);
	}
	return $checked;
}
}

if(!function_exists('cg_member_replacement_upload_limit')){
function cg_member_replacement_upload_limit($options,$image){
	$general = isset($options['general']) ? $options['general'] : array();
	$suffix = $image ? '' : 'file';
	$limit = wp_max_upload_size();
	if(!empty($general['ActivatePostMaxMB'.$suffix]) && !empty($general['PostMaxMB'.$suffix])){$limit = min($limit,(float)$general['PostMaxMB'.$suffix]*1000000);}
	return $limit;
}
}

// Reuse the ordinary upload's translated messages, including gallery-specific overrides in JS.
if(!function_exists('cg_member_replacement_upload_error')){
function cg_member_replacement_upload_error($key,$text,$value = null,$unit = ''){
	$validation = array('languageKey'=>$key);
	$message = cg_member_t($text);
	if($value!==null){$validation['value']=$value; $validation['unit']=$unit; $message .= ': '.$value.$unit;}
	return new WP_Error('upload',$message,array('uploadValidation'=>$validation));
}
}

// Read only the original JPEG's IFD0 orientation, also when PHP's EXIF extension
// is unavailable. Segment lengths bound memory use; never follow TIFF pointers
// outside the current EXIF segment or decode the image for this check.
if(!function_exists('cg_member_replacement_jpeg_orientation')){
function cg_member_replacement_jpeg_orientation($path){
	$handle = @fopen($path,'rb');
	if(!$handle){return 1;}
	$orientation = 1;
	if(fread($handle,2)==="\xFF\xD8"){
		while(!feof($handle)){
			if(fread($handle,1)!=="\xFF"){break;}
			do{$marker=fread($handle,1);}while($marker==="\xFF");
			if($marker==='' || $marker==="\xDA" || $marker==="\xD9"){break;}
			$lengthBytes=fread($handle,2);
			if(strlen($lengthBytes)!==2){break;}
			$length=unpack('nvalue',$lengthBytes);
			$length=$length['value']-2;
			if($length<0){break;}
			if($marker!=="\xE1"){
				if(fseek($handle,$length,SEEK_CUR)!==0){break;}
				continue;
			}
			$data=$length ? fread($handle,$length) : '';
			if(strlen($data)!==$length){break;}
			if(substr($data,0,6)!=="Exif\x00\x00"){continue;}
			$data=substr($data,6);
			$size=strlen($data);
			$order=substr($data,0,2);
			if($size<8 || ($order!=='II' && $order!=='MM')){continue;}
			$short=$order==='II' ? 'vvalue' : 'nvalue';
			$long=$order==='II' ? 'Vvalue' : 'Nvalue';
			$magic=unpack($short,substr($data,2,2));
			$offset=unpack($long,substr($data,4,4));
			$offset=$offset['value'];
			if($magic['value']!==42 || $offset<8 || $offset>$size-2){continue;}
			$count=unpack($short,substr($data,$offset,2));
			$offset+=2;
			if($count['value']>floor(($size-$offset)/12)){continue;}
			for($i=0;$i<$count['value'];$i++,$offset+=12){
				$tag=unpack($short,substr($data,$offset,2));
				if($tag['value']!==274){continue;}
				$type=unpack($short,substr($data,$offset+2,2));
				$values=unpack($long,substr($data,$offset+4,4));
				$value=unpack($short,substr($data,$offset+8,2));
				if($type['value']===3 && $values['value']===1 && $value['value']>=1 && $value['value']<=8){
					$orientation=$value['value'];
					break 2;
				}
			}
		}
	}
	fclose($handle);
	return $orientation;
}
}

if(!function_exists('cg_member_replacement_validate_upload')){
function cg_member_replacement_validate_upload($file,$gid,$options,$stored = false){
	$rules = cg_upload_validate_file($file,$gid,$options,$stored,cg_member_replacement_upload_rules($gid));
	if(is_wp_error($rules)){return $rules;}
	$error = new WP_Error('upload','The file does not meet the gallery upload requirements.');
	if($rules['pdfPreview']){
		$preview = isset($_FILES['replacement_preview']) ? $_FILES['replacement_preview'] : array();
		if(empty($preview['tmp_name']) || !is_string($preview['tmp_name']) || !isset($preview['error']) || $preview['error']!==UPLOAD_ERR_OK || !($stored && cg_upload_job_phase('finalize') ? is_file($preview['tmp_name']) : is_uploaded_file($preview['tmp_name'])) || filesize($preview['tmp_name'])>5*1024*1024){return new WP_Error('preview','The PDF preview could not be created. Please try again.');}
		$size = @getimagesize($preview['tmp_name']);
		if(!$size || $size['mime']!=='image/png' || $size[0]>1600 || $size[1]>1600){return $error;}
	}
	return $rules;
}
}

if(!function_exists('cg_member_replacement_create_pdf_preview')){
function cg_member_replacement_create_pdf_preview($attachment){
	$error = new WP_Error('preview','The PDF preview could not be created. Please try again.');
	// Re-encode the bounded PNG. Never associate a client-supplied attachment or path.
	$editor = wp_get_image_editor($_FILES['replacement_preview']['tmp_name']);
	if(is_wp_error($editor)){return $error;}
	$temp = wp_tempnam('cg-pdf-preview.png');
	if(!$temp){return $error;}
	$saved = $editor->save($temp,'image/png');
	if(is_wp_error($saved)){@unlink($temp);return $error;}
	try{
		return media_handle_sideload(array('name'=>get_post_field('post_name',$attachment).'-cg-pdf-preview.png','tmp_name'=>$saved['path']),0,null,array('post_author'=>get_current_user_id()));
	}finally{
		if(is_file($saved['path'])){@unlink($saved['path']);}
		if(is_file($temp)){@unlink($temp);}
	}
}
}

if(!function_exists('cg_member_replacement_media_data')){
function cg_member_replacement_media_data($attachment,$preview = 0){
	$post = get_post($attachment);
	$extension = cg_member_replacement_attachment_type($attachment);
	$image = cg_is_is_image($extension) || $extension==='jpe';
	$metadata = wp_get_attachment_metadata($attachment);
	$full = ($image || $preview) ? wp_get_attachment_image_src($preview ? $preview : $attachment,'full') : false;
	if($image && (!$full || !$full[1] || !$full[2])){
		$size = @getimagesize(get_attached_file($attachment));
		if($size){$full = array(wp_get_attachment_url($attachment),$size[0],$size[1]);}
	}
	$data = array('WpUpload'=>$attachment,'NamePic'=>$post->post_name,'ImgType'=>$extension==='jpe' ? 'jpg' : $extension,'Width'=>$full ? $full[1] : (!empty($metadata['width']) ? $metadata['width'] : 0),'Height'=>$full ? $full[2] : (!empty($metadata['height']) ? $metadata['height'] : 0),'rThumb'=>0,'PdfPreview'=>$preview,'PdfOriginal'=>'','PdfPreviewImage'=>'','PdfPreviewImageLarge'=>'','guid'=>wp_get_attachment_url($attachment),'post_title'=>$post->post_title,'post_name'=>$post->post_name,'post_content'=>$post->post_content,'post_excerpt'=>$post->post_excerpt,'post_caption'=>$post->post_excerpt,'post_date'=>$post->post_date,'post_alt'=>get_post_meta($attachment,'_wp_attachment_image_alt',true),'post_mime_type'=>$post->post_mime_type,'type'=>$image ? 'image' : (cg_is_alternative_file_type_video($extension) ? 'video' : (cg_is_alternative_file_type_audio($extension) ? 'audio' : 'file')));
	foreach(array('thumbnail','medium','large','full') as $size){
		$src = ($image || $preview) ? wp_get_attachment_image_src($preview ? $preview : $attachment,$size) : false;
		$data[$size] = $src ? $src[0] : ($size==='full' ? $data['guid'] : '');
	}
	if($preview){
		$data['PdfOriginal']=$data['guid']; $data['guid']=$data['full'];
		$data['PdfPreviewImage']=$data['full']; $data['PdfPreviewImageLarge']=$data['large'];
		$data['ImgType']='png'; $data['post_mime_type']='image/png'; $data['type']='image';
	}
	$data['Exif'] = $image ? cg_create_exif_data($attachment) : array();
	$data['IsExifDataChecked'] = true;
	return $data;
}
}
