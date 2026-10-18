<?php
if(!defined('ABSPATH')){exit;}

// Evaluate the source gallery, never the gallery containing the member toolbar.
if(!function_exists('cg_community_gallery')){
function cg_community_gallery($gid,$shortcode=''){
	global $wpdb;
	static $cache=array();
	$key=get_current_blog_id().':'.get_current_user_id().':'.absint($gid).':'.$shortcode;
	if(array_key_exists($key,$cache)){return $cache[$key];}
	$cache[$key]=false;
	$gallery=$wpdb->get_row($wpdb->prepare("SELECT id,GalleryName,WpPageParent,WpPageParentNoVoting FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d",$gid));
	if(!$gallery || !cg_member_features_available($gid)){return false;}
	$source=cg_member_replacement_source($gid);
	if(!$source || !is_array($source)){return false;}
	$candidates=$shortcode!=='' ? array($shortcode) : array('cg_gallery','cg_gallery_no_voting');
	foreach($candidates as $candidate){
		if(!in_array($candidate,array('cg_gallery','cg_gallery_no_voting'),true) || !cg_member_enabled($gid,$candidate)){continue;}
		$options=cg_member_options_from_source($source,$gid,$candidate);
		if(!is_user_logged_in() && !empty($options['pro']['RegUserGalleryOnly'])){continue;}
		$interval=cg_shortcode_interval_check($gid,$source,$candidate);
		if(empty($interval['shortcodeIsActive'])){continue;}
		$pageId=$candidate==='cg_gallery' ? $gallery->WpPageParent : $gallery->WpPageParentNoVoting;
		$page=get_post($pageId);
		if(!$page || $page->post_status!=='publish' || post_password_required($page)){continue;}
		$upload=wp_upload_dir();$base=$upload['basedir'].'/contest-gallery/gallery-id-'.absint($gid).'/json/'.absint($gid);
		if(!is_readable($base.'-categories.json') || !is_readable($base.'-form-upload.json')){continue;}
		$categories=is_file($base.'-categories.json') ? json_decode(file_get_contents($base.'-categories.json'),true) : array();
		$form=is_file($base.'-form-upload.json') ? json_decode(file_get_contents($base.'-form-upload.json'),true) : array();
		// Missing configuration must not turn a private category into an unrestricted entry.
		if(!is_array($categories) || !is_array($form)){continue;}
		$cache[$key]=array('id'=>absint($gid),'name'=>wp_specialchars_decode(wp_strip_all_tags($gallery->GalleryName),ENT_QUOTES),'url'=>get_permalink($pageId),'shortcode'=>$candidate,'options'=>$options,'categories'=>$categories,'form'=>$form);
		return $cache[$key];
	}
	return false;
}
}
if(!function_exists('cg_community_visible_entry')){
function cg_community_visible_entry($entry,$shortcode=''){
	if((int)$entry->Active!==1 || !empty($entry->IsProfileImage) || empty($entry->WpUserId) || !cg_community_user($entry->WpUserId)){return false;}
	$gallery=cg_community_gallery($entry->GalleryID,$shortcode);
	if(!$gallery || !cg1l_frontend_is_category_allowed_for_entry((array)$entry,$gallery['categories'],$gallery['options'],$gallery['form'])){return false;}
	$field=$gallery['shortcode']==='cg_gallery' ? 'WpPage' : 'WpPageNoVoting';
	$page=!empty($entry->$field) ? get_post($entry->$field) : null;
	return $page && $page->post_status==='publish' && !post_password_required($page) ? $gallery : false;
}
}
// No-voting profile origins must not fall through to the normal voting variant.
// Normal browsing retains its existing no-voting-only fallback.
if(!function_exists('cg_community_feed_shortcode')){
function cg_community_feed_shortcode($request){
	return isset($request['shortcode']) && $request['shortcode']==='cg_gallery_no_voting' ? 'cg_gallery_no_voting' : '';
}
}
if(!function_exists('cg_community_feed_where')){
function cg_community_feed_where($viewer,$request){
	global $wpdb;$blocks=cg_community_table('blocks');
	$target=!empty($request['target']) ? absint($request['target']) : $viewer;
	if($target!==$viewer){
		$contactRequest=$request;
		// The signed profile origin is independent of the selected feed gallery.
		$contactRequest['gallery']=!empty($request['sourceGallery']) ? absint($request['sourceGallery']) : 0;
		if(cg_community_blocked($viewer,$target) || !cg_community_contact_allowed($viewer,$target,$contactRequest)){return cg_community_error('This member is no longer available.');}
	}
	$where=$wpdb->prepare("e.Active=1 AND e.IsProfileImage=0 AND e.WpUserId=%d AND NOT EXISTS (SELECT 1 FROM $blocks b WHERE (b.BlockerID=%d AND b.BlockedID=e.WpUserId) OR (b.BlockedID=%d AND b.BlockerID=e.WpUserId))",$target,$viewer,$viewer);
	if(!empty($request['gallery'])){$where.=$wpdb->prepare(' AND e.GalleryID=%d',$request['gallery']);}
	return $where;
}
}
// Use the same configured captions as the public gallery, including category fields.
if(!function_exists('cg_community_entry_titles')){
function cg_community_entry_titles($entry,$gallery){
	static $languageNames=null;
	if($languageNames===null){
		$languageNames=array('gallery'=>array());
		cg1l_load_language_file_categorized(__DIR__.'/../../check-language.php','gallery',$languageNames);
	}
	$info=cg1l_get_entry_info_data($entry->GalleryID,$entry->id);
	$captions=cg1l_frontend_get_info_data_gallery((array)$entry,$gallery['form'],$gallery['categories'],$languageNames,$gallery['options'],$info);
	$titles=array();
	foreach(array('mainTitle','subTitle','thirdTitle') as $key){
		$text=trim(str_replace("\xc2\xa0",' ',wp_strip_all_tags(html_entity_decode((string)$captions[$key],ENT_QUOTES,'UTF-8'))));
		if($text!==''){$titles[]=$text;break;}
	}
	if(!$titles){
		$name=!empty($entry->NamePic) ? (string)$entry->NamePic : '';
		$file=!empty($entry->WpUpload) ? get_attached_file($entry->WpUpload,true) : '';
		$extension=!empty($entry->PdfOriginal) ? 'pdf' : ($file ? pathinfo($file,PATHINFO_EXTENSION) : '');
		if($name!=='' && $extension!=='' && strtolower(pathinfo($name,PATHINFO_EXTENSION))!==strtolower($extension)){$name.='.'.strtolower($extension);}
		if($name===''){$name=$file ? wp_basename($file) : (!empty($entry->WpUpload) ? wp_strip_all_tags(get_the_title($entry->WpUpload)) : '');}
		if($name!==''){$titles[]=$name;}
	}
	return $titles;
}
}
if(!function_exists('cg_community_feed_item')){
function cg_community_feed_item($entry,$gallery){
	$field=$gallery['shortcode']==='cg_gallery' ? 'WpPage' : 'WpPageNoVoting';
	$fileType=strtolower((string)$entry->ImgType);
	$isVideo=cg_is_alternative_file_type_video($fileType);
	$videoUrl='';
	$media=!empty($entry->PdfPreview) ? $entry->PdfPreview : $entry->WpUpload;
	$thumb=wp_get_attachment_image_src($media,'medium');
	$image=$thumb ? $thumb[0] : '';
	if($image==='' || $isVideo){
		// Reuse public gallery media, including image previews of CG download-sale posts.
		$data=cg1l_get_entry_main_data($entry->GalleryID,$entry->id);
		$preview=false;
		if(isset($data[$entry->id])){
			// Authorize cached video URLs with the current sale and attachment identifiers.
			$previewData=array_merge($data[$entry->id],array('id'=>$entry->id,'GalleryID'=>$entry->GalleryID,'WpUpload'=>$entry->WpUpload,'EcommerceEntry'=>$entry->EcommerceEntry,'ImgType'=>$fileType));
			$preview=cg_page_preview_media($previewData);
		}
		if($preview){
			if($isVideo && !empty($preview['video'])){$videoUrl=$preview['url'];}
			elseif($image==='' && empty($preview['video'])){$image=$preview['url'];}
		}
	}
	$titles=cg_community_entry_titles($entry,$gallery);
	$dateFormat=!empty($gallery['options']['visual']['ShowDateFormat']) ? $gallery['options']['visual']['ShowDateFormat'] : 'modern';
	return array('id'=>(int)$entry->id,'person'=>cg_community_person($entry->WpUserId),'time'=>(int)$entry->Timestamp,'galleryId'=>(int)$entry->GalleryID,'gallery'=>$gallery['name'],'url'=>get_permalink($entry->$field),'image'=>$image,'fileType'=>$fileType,'videoUrl'=>$videoUrl, 'title'=>$titles ? $titles[0] : '', 'titles'=>$titles, 'dateFormat'=>$dateFormat);
}
}
if(!function_exists('cg_community_feed')){
function cg_community_feed($viewer,$request,$galleries=false,$where=null){
	global $wpdb;$table=$wpdb->prefix.'contest_gal1ery';
	if($where===null){$where=cg_community_feed_where($viewer,$request);}if(is_wp_error($where)){return $where;}
	if($galleries){return cg_community_gallery_list($viewer,$request,$where);}
	$cursor=isset($request['cursor']) ? $request['cursor'] : '';$items=array();$hasMore=false;$scanned=0;
	if($cursor!=='' && !preg_match('/^([0-9]{1,12}):([0-9]{1,20})$/D',$cursor)){return cg_community_error('Invalid page.',400);}
	while(count($items)<20 && $scanned<1000){
		$next=$where;
		if($cursor!==''){$parts=explode(':',$cursor);$next.=$wpdb->prepare(' AND (e.Timestamp<%d OR (e.Timestamp=%d AND e.id<%d))',$parts[0],$parts[0],$parts[1]);}
		$rows=$wpdb->get_results("SELECT e.* FROM $table e WHERE $next ORDER BY e.Timestamp DESC,e.id DESC LIMIT 100");
		if(!$rows){$hasMore=false;break;}
		foreach($rows as $entry){
			$scanned++;$cursor=(int)$entry->Timestamp.':'.(int)$entry->id;
			$gallery=cg_community_visible_entry($entry,cg_community_feed_shortcode($request));
			if($gallery){$items[]=cg_community_feed_item($entry,$gallery);}
			if(count($items)===20){$hasMore=true;break;}
		}
		if(count($rows)<100 && count($items)<20){$hasMore=false;break;}
		$hasMore=true;
	}
	return array('items'=>$items,'cursor'=>$hasMore ? $cursor : '','more'=>$hasMore);
}
}
// Use public card captions, including a visible fallback, instead of the internal gallery name.
if(!function_exists('cg_community_gallery_titles')){
function cg_community_gallery_titles($options,$gid){
	$titles=array();
	foreach(array('name'=>'MainTitleGalleriesView','subtitle'=>'SubTitleGalleriesView','thirdTitle'=>'ThirdTitleGalleriesView') as $key=>$field){
		$value=isset($options['pro'][$field]) ? $options['pro'][$field] : '';
		$titles[$key]=trim(str_replace("\xc2\xa0",' ',cg_page_text($value)));
	}
	if($titles['name']===''){$titles['name']=cg_member_t('Gallery').' '.absint($gid);}
	return $titles;
}
}
if(!function_exists('cg_community_gallery_list')){
function cg_community_gallery_list($viewer,$request,$where){
	global $wpdb;$table=$wpdb->prefix.'contest_gal1ery';$items=array();$cursor=absint($request['cursor']);$scanned=0;$more=false;
	$member=cg_community_person(!empty($request['target']) ? $request['target'] : $viewer);
	while(count($items)<20 && $scanned<100){
		$ids=$wpdb->get_col($wpdb->prepare("SELECT DISTINCT e.GalleryID FROM $table e WHERE $where AND e.GalleryID>%d ORDER BY e.GalleryID LIMIT 20",$cursor));
		if(!$ids){$more=false;break;}
		foreach($ids as $gid){
			$cursor=(int)$gid;$scanned++;$gallery=cg_community_gallery($gid,cg_community_feed_shortcode($request));
			if(!$gallery){continue;}
			$condition=$where.$wpdb->prepare(' AND e.GalleryID=%d',$gid);
			if(cg1l_frontend_has_active_category_upload_field($gallery['form'])){
				$allowed=array();if(!empty($gallery['options']['pro']['ShowOther'])){$allowed[]=0;}
				foreach($gallery['categories'] as $id=>$category){if(!empty($category['Active'])){$allowed[]=absint($id);}}
				if(!$allowed){continue;}
				$condition.=' AND e.Category IN ('.implode(',',$allowed).')';
			}
			$field=$gallery['shortcode']==='cg_gallery' ? 'WpPage' : 'WpPageNoVoting';
			// Restrict counts and covers as well as cards. Protected entry pages are omitted.
			$join=" INNER JOIN {$wpdb->posts} p ON p.ID=e.$field AND p.post_status='publish' AND p.post_password='' INNER JOIN {$wpdb->users} u ON u.ID=e.WpUserId";
			$count=(int)$wpdb->get_var("SELECT COUNT(*) FROM $table e $join WHERE $condition");
			if(!$count){continue;}
			$cover=$wpdb->get_row("SELECT e.* FROM $table e $join WHERE $condition ORDER BY e.Timestamp DESC,e.id DESC LIMIT 1");
			if(!$cover || !cg_community_visible_entry($cover,$gallery['shortcode'])){continue;}
			$item=cg_community_feed_item($cover,$gallery);
			$items[]=cg_community_gallery_titles($gallery['options'],$gid)+array('id'=>(int)$gid,'url'=>$gallery['url'],'count'=>$count,'image'=>$item['image'],'fileType'=>$item['fileType'],'videoUrl'=>$item['videoUrl'],
				'memberCountLabel'=>cg_member_t('{entries} by {name}',array('entries'=>$count===1 ? cg_member_t('1 entry') : sprintf(cg_member_t('%s entries'),number_format_i18n($count)),'name'=>$member['name'])),
				'statisticsHtml'=>cg_page_gallery_card_statistics($gid,$gallery['shortcode']));
			if(count($items)===20){$more=true;break;}
		}
		if(count($ids)<20 && count($items)<20){$more=false;break;}
		$more=true;
	}
	return array('items'=>$items,'cursor'=>$more ? (string)$cursor : '','more'=>$more);
}
}
