<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_participant_mail_entry_text')){
function cg_participant_mail_entry_text($value){
	$text=html_entity_decode(wp_unslash((string)$value),ENT_QUOTES,'UTF-8');
	return trim(preg_replace('/\s+/u',' ',wp_strip_all_tags(wp_check_invalid_utf8($text))));
}
}

// Read only the configured main gallery title, never unrelated private upload fields.
if(!function_exists('cg_participant_mail_entry_labels')){
function cg_participant_mail_entry_labels($galleryId){
	global $wpdb;
	$field=$wpdb->get_row($wpdb->prepare("SELECT f.id,f.Field_Type,f.Field_Content
		FROM {$wpdb->prefix}contest_gal1ery_options_visual v
		INNER JOIN {$wpdb->prefix}contest_gal1ery_f_input f ON f.GalleryID=v.GalleryID AND f.id=v.Field1IdGalleryView
		WHERE v.GalleryID=%d AND f.Active=1 LIMIT 1",$galleryId),ARRAY_A);
	if($wpdb->last_error){return new WP_Error('cg_pmail_titles','The entry titles could not be loaded.');}
	if(!$field){return array();}
	$labels=array();
	if($field['Field_Type']==='selectc-f'){
		$rows=$wpdb->get_results($wpdb->prepare("SELECT e.id,c.Name FROM {$wpdb->prefix}contest_gal1ery e
			LEFT JOIN {$wpdb->prefix}contest_gal1ery_categories c ON c.GalleryID=e.GalleryID AND c.id=e.Category
			WHERE e.GalleryID=%d",$galleryId),ARRAY_A);
		if($wpdb->last_error){return new WP_Error('cg_pmail_titles','The entry titles could not be loaded.');}
		$language=array('gallery'=>array());cg1l_load_language_file_categorized(__DIR__.'/../../../check-language.php','gallery',$language);
		foreach($rows as $row){$labels[$row['id']]=cg_participant_mail_entry_text($row['Name']!==null ? $row['Name'] : $language['gallery']['Other']);}
		return $labels;
	}
	$rows=$wpdb->get_results($wpdb->prepare("SELECT pid,Short_Text,Long_Text,InputDate FROM {$wpdb->prefix}contest_gal1ery_entries
		WHERE GalleryID=%d AND f_input_id=%d ORDER BY id ASC",$galleryId,$field['id']),ARRAY_A);
	if($wpdb->last_error){return new WP_Error('cg_pmail_titles','The entry titles could not be loaded.');}
	$definition=$field['Field_Type']==='date-f' ? maybe_unserialize($field['Field_Content']) : array();
	foreach($rows as $row){
		$value=$field['Field_Type']==='comment-f' ? $row['Long_Text'] : $row['Short_Text'];
		if($field['Field_Type']==='date-f' && $row['InputDate'] && $row['InputDate']!=='0000-00-00 00:00:00'){
			$format=is_array($definition) && !empty($definition['format']) ? str_replace(array('YYYY','MM','DD'),array('Y','m','d'),$definition['format']) : '';
			$date=DateTime::createFromFormat('Y-m-d H:i:s',$row['InputDate']);
			$value=$format!=='' && $date ? $date->format($format) : '';
		}
		$labels[$row['pid']]=cg_participant_mail_entry_text($value);
	}
	return $labels;
}
}

if(!function_exists('cg_participant_mail_entry_thumbnail')){
function cg_participant_mail_entry_thumbnail($attachmentId){
	$attachmentId=absint($attachmentId);if(!$attachmentId){return array();}
	$metadata=wp_get_attachment_metadata($attachmentId);
	if(!is_array($metadata) || empty($metadata['sizes']) || !is_array($metadata['sizes'])){return array();}
	$original=wp_get_attachment_url($attachmentId);$file=get_attached_file($attachmentId,true);$best=array();$area=PHP_INT_MAX;
	foreach($metadata['sizes'] as $name=>$size){
		if($name==='full' || empty($size['file']) || empty($size['width']) || empty($size['height']) || $size['width']<0 || $size['height']<0){continue;}
		$pixels=(float)$size['width']*(float)$size['height'];
		if($pixels<=0 || $pixels>=$area){continue;}
		// Offloaded attachments may have no local files. Local stale size records must not produce broken images.
		if($file && is_file($file) && !is_file(dirname($file).'/'.wp_basename($size['file']))){continue;}
		$image=wp_get_attachment_image_src($attachmentId,$name);
		// WordPress can silently return the original if a requested sub-size is missing.
		if(!$image || empty($image[3]) || $image[0]===$original || empty($image[1]) || empty($image[2])){continue;}
		$url=esc_url_raw($image[0],array('http','https'));
		if($url===''){continue;}
		$best=array('thumbnail_url'=>$url,'thumbnail_width'=>absint($image[1]),'thumbnail_height'=>absint($image[2]));$area=$pixels;
	}
	return $best;
}
}

if(!function_exists('cg_participant_mail_entry_presentation')){
function cg_participant_mail_entry_presentation($row,$labels){
	$label=isset($labels[$row['id']]) ? $labels[$row['id']] : '';
	$type=strtolower((string)$row['ImgType']);$attachment=absint($row['WpUpload']);
	$isVideo=cg_is_alternative_file_type_video($type);$isAudio=cg_is_alternative_file_type_audio($type);
	$isImage=in_array($type,array('jpg','jpeg','jpe','png','gif','webp','avif'),true);
	$fileType=!empty($row['PdfPreview']) || $type==='pdf' ? 'PDF' : ($isVideo ? 'VIDEO' : ($isAudio ? 'AUDIO' : ($type==='con' ? 'TEXT' : ($isImage ? 'IMAGE' : 'FILE'))));
	if($label===''){
		$name=cg_participant_mail_entry_text(wp_basename($row['NamePic']));
		$file=$attachment ? get_attached_file($attachment,true) : '';
		$extension=$file ? strtolower(pathinfo($file,PATHINFO_EXTENSION)) : '';
		if($name!=='' && $extension!=='' && strtolower(pathinfo($name,PATHINFO_EXTENSION))!==$extension){$name.='.'. $extension;}
		$label=$name!=='' ? $name : ($file ? cg_participant_mail_entry_text(wp_basename($file)) : '#'.absint($row['id']));
	}
	$media=!empty($row['PdfPreview']) ? absint($row['PdfPreview']) : $attachment;
	if($isVideo || $isAudio){$media=$attachment ? absint(get_post_thumbnail_id($attachment)) : 0;}
	return array_merge(array('label'=>$label,'file_type'=>$fileType),$media ? cg_participant_mail_entry_thumbnail($media) : array());
}
}

if(!function_exists('cg_participant_mail_entry_styles')){
function cg_participant_mail_entry_styles($styles){
	return array_values(array_unique(array_merge($styles,array('table-layout','overflow-wrap','word-wrap'))));
}
}

if(!function_exists('cg_participant_mail_entries_html')){
function cg_participant_mail_entries_html($entries){
	$html='<table class="cg_pmail_entry_cards" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="width:100%;max-width:600px;border-collapse:separate;table-layout:fixed;">';
	foreach($entries as $entry){
		$text=$entry['label'];
		if(!isset($entry['file_type']) && isset($entry['id']) && $text==='Entry #'.absint($entry['id'])){$text='#'.absint($entry['id']);}
		$label=esc_html($text);$url=esc_url($entry['url'],array('http','https'));
		$html.='<tr><td style="padding:0 0 10px 0;"><table class="cg_pmail_entry_card" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="width:100%;border:1px solid #dce5df;border-radius:10px;border-collapse:separate;table-layout:fixed;background-color:#ffffff;"><tr>';
		$thumbnail=!empty($entry['thumbnail_url']) ? esc_url($entry['thumbnail_url'],array('http','https')) : '';
		if($thumbnail!=='' && !empty($entry['thumbnail_width']) && !empty($entry['thumbnail_height'])){
			$scale=min(1,80/$entry['thumbnail_width'],80/$entry['thumbnail_height']);
			$width=max(1,(int)round($entry['thumbnail_width']*$scale));$height=max(1,(int)round($entry['thumbnail_height']*$scale));
			$image='<img src="'.$thumbnail.'" alt="'.esc_attr($text).'" width="'.$width.'" height="'.$height.'" border="0" style="display:block;width:'.$width.'px;height:'.$height.'px;max-width:80px;border:0;border-radius:6px;">';
			$html.='<td class="cg_pmail_entry_media" width="80" align="center" valign="middle" style="width:80px;padding:12px;">'.($url!=='' ? '<a href="'.$url.'" style="text-decoration:none;">'.$image.'</a>' : $image).'</td>';
		}elseif(!empty($entry['file_type'])){
			$html.='<td class="cg_pmail_entry_media" width="80" align="center" valign="middle" style="width:80px;padding:12px;"><table role="presentation" border="0" cellpadding="0" cellspacing="0" width="80" bgcolor="#f1f6f3" style="width:80px;border-radius:6px;background-color:#f1f6f3;"><tr><td height="80" align="center" valign="middle" style="height:80px;font-family:Arial,sans-serif;font-size:11px;font-weight:bold;color:#52695b;">'.esc_html($entry['file_type']).'</td></tr></table></td>';
		}
		$html.='<td valign="middle" style="padding:12px;font-family:Arial,sans-serif;font-size:14px;line-height:20px;color:#22362a;overflow-wrap:anywhere;word-wrap:break-word;"><strong>'.($url!=='' ? '<a href="'.$url.'" style="font-size:14px;font-weight:bold;color:#18884f;text-decoration:underline;">'.$label.'</a>' : $label).'</strong>';
		$html.='</td></tr></table></td></tr>';
	}
	return $html.'</table>';
}
}
