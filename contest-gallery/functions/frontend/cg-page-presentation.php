<?php
if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/cg-page-statistics.php';

function cg_page_text($value){
	return trim(wp_strip_all_tags(html_entity_decode(stripslashes((string)$value),ENT_QUOTES,'UTF-8')));
}

function cg_page_gallery_title($options,$gid){
	foreach(array('GalleryName','MainTitleGalleriesView') as $key){
		$group = $key==='GalleryName' ? 'general' : 'pro';
		$title = isset($options[$group][$key]) ? cg_page_text($options[$group][$key]) : '';
		if($title!=='' && !in_array($title,array('Gallery ID '.$gid,'Main title gallery '.$gid),true)){return $title;}
	}
	return cg_member_t('Explore the gallery');
}

function cg_page_gallery_header_texts($options,$gid){
	$texts = array('GalleryHeaderLabel'=>cg_member_t('Gallery'),'GalleryHeaderTitle'=>cg_page_gallery_title($options,$gid),'GalleryHeaderSubtitle'=>cg_member_t('Explore the entries in this gallery.'));
	$subtitle = isset($options['pro']['SubTitleGalleriesView']) ? cg_page_text($options['pro']['SubTitleGalleriesView']) : '';
	if($subtitle!=='' && $subtitle!=='Sub title gallery '.$gid){$texts['GalleryHeaderSubtitle'] = $subtitle;}
	foreach($texts as $key=>$default){
		// A missing option keeps the previous text. An explicitly empty option hides the line.
		if(isset($options['visual'][$key]) && is_string($options['visual'][$key])){$texts[$key] = $options['visual'][$key];}
	}
	return $texts;
}

function cg_page_galleries_header_texts($options){
	$texts = array('GalleriesHeaderLabel'=>cg_member_t('Galleries'),'GalleriesHeaderTitle'=>cg_member_t('Discover galleries'),'GalleriesHeaderSubtitle'=>cg_member_t('Explore galleries and their entries.'));
	foreach($texts as $key=>$default){
		if(isset($options[$key]) && is_string($options[$key])){$texts[$key] = $options[$key];}
	}
	return $texts;
}

// Only an explicit saved choice changes the previous, enabled-by-default header.
function cg_page_header_option_value($options,$key,$default = 1){
	return isset($options[$key]) && in_array($options[$key],array(0,1,'0','1'),true) ? (int)$options[$key] : (int)$default;
}

function cg_page_gallery_header_enabled($gid,$shortcode,$stored = null){
	if(!in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return false;}
	if($stored===null){$stored = cg_member_replacement_source($gid);}
	// A missing No-voting choice must not inherit the normal gallery's off switch.
	if($shortcode==='cg_gallery_no_voting'){
		$options = isset($stored[$gid.'-nv']) ? $stored[$gid.'-nv'] : array();
	}else{
		$options = isset($stored[$gid]) ? $stored[$gid] : $stored;
	}
	return (bool)cg_page_header_option_value(isset($options['visual']) ? $options['visual'] : array(),'ShowGalleryHeader');
}

// Small presentation hints let AJAX navigation show the destination's skeleton immediately.
function cg_page_gallery_navigation_presentation($gid,$shortcode,$stored,$active){
	$options = cg_member_options_from_source($stored,$gid,$shortcode);
	$hasHeader = $active && in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true) && !empty($options['general']['Version']) && (int)$options['general']['Version']>=24;
	return array(
		'style'=>!empty($options['visual']['FeControlsStyle']) && $options['visual']['FeControlsStyle']==='black' ? 'black' : 'white',
		'showHeader'=>$hasHeader && cg_page_gallery_header_enabled($gid,$shortcode,$stored)
	);
}

function cg_page_galleries_navigation_presentation($shortcode){
	$options = cg_galleries_options($shortcode);
	return array(
		'style'=>!empty($options['FeControlsStyle']) && $options['FeControlsStyle']==='black' ? 'black' : 'white',
		'showHeader'=>in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true) && (bool)cg_page_header_option_value($options,'ShowGalleriesHeader')
	);
}

function cg_page_store_galleries_header_options(&$options,$previous){
	foreach(array('g','nv','u','w','ec') as $type){
		if(in_array($type,array('g','nv'),true)){
			$default = cg_page_header_option_value(isset($previous[$type]) ? $previous[$type] : array(),'ShowGalleriesHeader');
			$options[$type]['ShowGalleriesHeader'] = cg_page_header_option_value(isset($options[$type]) ? $options[$type] : array(),'ShowGalleriesHeader',$default);
		}else{
			unset($options[$type]['ShowGalleriesHeader']);
		}
		foreach(array_keys(cg_page_galleries_header_texts(array())) as $key){
			if(isset($options[$type][$key]) && is_string($options[$type][$key])){
				$options[$type][$key] = sanitize_text_field(wp_unslash($options[$type][$key]));
			}elseif(isset($previous[$type][$key]) && is_string($previous[$type][$key])){
				$options[$type][$key] = $previous[$type][$key];
			}else{
				unset($options[$type][$key]);
			}
		}
	}
}

function cg_page_store_header_options(&$options,$gid,$input){
	$stored = cg_member_replacement_source($gid);
	$base = isset($stored[$gid]) ? $stored[$gid] : $stored;
	$available = !empty($base['general']['Version']) && (int)$base['general']['Version']>=24;
	foreach(array(''=>'cg_gallery','-nv'=>'cg_gallery_no_voting') as $suffix=>$shortcode){
		$previous = isset($stored[$gid.$suffix]) ? $stored[$gid.$suffix] : $base;
		$default = cg_page_gallery_header_enabled($gid,$shortcode,$stored) ? 1 : 0;
		$visual = $available && isset($input[$shortcode]['visual']) ? $input[$shortcode]['visual'] : array();
		$enabled = cg_page_header_option_value($visual,'ShowGalleryHeader',$default);
		$options[$gid.$suffix]['visual']['ShowGalleryHeader'] = $enabled;
		if($suffix===''){$options['visual']['ShowGalleryHeader'] = $enabled;}
		foreach(array('GalleryHeaderLabel','GalleryHeaderTitle','GalleryHeaderSubtitle') as $key){
			if($available && isset($input[$shortcode]['visual'][$key]) && is_string($input[$shortcode]['visual'][$key])){
				$value = sanitize_text_field(wp_unslash($input[$shortcode]['visual'][$key]));
			}elseif(isset($previous['visual'][$key]) && is_string($previous['visual'][$key])){
				$value = $previous['visual'][$key];
			}else{
				unset($options[$gid.$suffix]['visual'][$key]);
				if($suffix===''){unset($options['visual'][$key]);}
				continue;
			}
			$options[$gid.$suffix]['visual'][$key] = $value;
			if($suffix===''){$options['visual'][$key] = $value;}
		}
	}
}

// Recognize only the shipped explanatory copy. Custom introductory content stays intact.
function cg_page_is_factory_intro($value){
	$original = '<p  style="text-align: center;">This is <strong>"General text on entry landing page before an activated entry"</strong>, is only visible on an entry landing page of an entry and can be configured for every cg_gallery shortcode type in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong>the cg_gallery... shortcode with entry_id added to this page can be placed on any other of your pages<br><strong>NOTE:</strong> social media share icons can be configured in <strong>"Edit options" >>> "Entry view"</strong><br><strong>NOTE:</strong> "Back to gallery button custom URL" can be configured in <strong>"Edit options" >>> "Entry view"</strong></p>';
	return cg_page_text($value)===cg_page_text($original);
}

// Resolve the selected file's dimensions, not the original upload's dimensions.
function cg_page_preview_image_size($entry,$url){
	static $sizes = array();
	$path = wp_parse_url($url,PHP_URL_PATH);
	foreach(array('PdfPreview','WpUpload') as $key){
		$id = !empty($entry[$key]) ? absint($entry[$key]) : 0;
		if(!$id){continue;}
		if(!isset($sizes[$id])){
			$sizes[$id] = array();
			foreach(array('large','medium','thumbnail','full') as $size){
				$image = wp_get_attachment_image_src($id,$size);
				if($image){$sizes[$id][wp_parse_url($image[0],PHP_URL_PATH)] = array(absint($image[1]),absint($image[2]));}
			}
		}
		if(isset($sizes[$id][$path])){return $sizes[$id][$path];}
	}
	// Unknown files use scale-down until the browser knows their actual pixel size.
	return array(0,0);
}

function cg_page_preview_media($entry){
	if(!empty($entry['isCGalleriesNoGalleryEntries'])){return false;}
	$type = isset($entry['ImgType']) ? strtolower($entry['ImgType']) : '';
	$imageTypes = array('jpg','jpeg','jpe','png','gif','webp','avif');
	$isVideo = cg_is_alternative_file_type_video($type);
	if(!$isVideo && $type!=='pdf' && !in_array($type,$imageTypes,true)){return false;}
	if($isVideo && !empty($entry['EcommerceEntry'])){
		// Match the gallery's download-sale restriction before exposing a video source.
		static $sales = array();
		$gid = !empty($entry['GalleryID']) ? absint($entry['GalleryID']) : 0;
		$pid = !empty($entry['id']) ? absint($entry['id']) : 0;
		if(!$gid || !$pid || empty($entry['WpUpload'])){return false;}
		if(!isset($sales[$gid])){$sales[$gid] = cg_get_ecommerce_files_data($gid);}
		if(empty($sales[$gid][$pid])){return false;}
		$sale = $sales[$gid][$pid];
		if(!empty($sale['IsDownload']) && !empty($sale['WpUploadFilesForSale']) && in_array(absint($entry['WpUpload']),array_map('absint',(array)$sale['WpUploadFilesForSale']),true)){return false;}
	}
	$urls = array();
	$width = !empty($entry['Width']) ? absint($entry['Width']) : 0;
	$height = !empty($entry['Height']) ? absint($entry['Height']) : 0;
	$keys = $isVideo ? array('guid','full') : array('large','medium','thumbnail');
	if($type==='pdf'){
		$keys = array('PdfPreviewImageLarge','PdfPreviewImage','large','medium','thumbnail');
		if(!empty($entry['PdfPreview'])){
			$preview = wp_get_attachment_image_src(absint($entry['PdfPreview']),'large');
			if($preview){$urls[] = $preview[0];$width = absint($preview[1]);$height = absint($preview[2]);}
		}
	}
	foreach($keys as $key){if(!empty($entry[$key]) && is_string($entry[$key])){$urls[] = $entry[$key];}}
	$uploads = wp_upload_dir();
	foreach($urls as $url){
		if(strpos($url,trailingslashit($uploads['baseurl']))!==0){continue;}
		$extension = strtolower(pathinfo((string)wp_parse_url($url,PHP_URL_PATH),PATHINFO_EXTENSION));
		if($isVideo ? cg_is_alternative_file_type_video($extension) : in_array($extension,$imageTypes,true)){
			if(!$isVideo){list($width,$height) = cg_page_preview_image_size($entry,$url);}
			return array('url'=>$url,'width'=>$width,'height'=>$height,'video'=>$isVideo,'pdf'=>!$isVideo && ($type==='pdf' || !empty($entry['PdfPreview']) || !empty($entry['PdfOriginal'])));
		}
	}
	return false;
}

// Match layoutPagePreviews() before the browser loads or decodes the selected media.
function cg_page_preview_layout($previews){
	$layout = array('class'=>'','style'=>'');
	if(!$previews || count($previews)>2){return $layout;}
	$ratios = array();$portrait = false;
	foreach($previews as $preview){
		if(empty($preview['width']) || empty($preview['height'])){return $layout;}
		$ratio = $preview['width']/$preview['height'];
		$ratios[] = $ratio;$portrait = $portrait || $ratio<1;
	}
	if(!$portrait){return $layout;}
	$total = array_sum($ratios);$columns = array();
	foreach($ratios as $ratio){$columns[] = number_format(100*$ratio/$total,6,'.','').'fr';}
	$layout['class'] = ' cg-page-collage-natural';
	$layout['style'] = '--cg-preview-ratio:'.number_format($total,6,'.','').';--cg-preview-columns:'.implode(' ',$columns).';--cg-preview-gap-count:'.(count($previews)-1);
	return $layout;
}

// Keep destinations tied to WordPress-owned IDs, not the displayed thumbnail URL.
function cg_page_preview_navigation($entry,$shortcode){
	$gid = !empty($entry['GalleryIdToCheck']) ? absint($entry['GalleryIdToCheck']) : (!empty($entry['GalleryID']) ? absint($entry['GalleryID']) : (!empty($entry['gid']) ? absint($entry['gid']) : 0));
	$map = cgl_get_shortcode_page_map($shortcode);
	$page = !empty($entry[$map['entryField']]) ? absint($entry[$map['entryField']]) : 0;
	static $galleryUrls = array();
	$key = $gid.':'.$shortcode;
	if(!isset($galleryUrls[$key])){$galleryUrls[$key] = $gid ? cgl_get_gallery_parent_page_url($gid,$shortcode) : '';}
	return array(
		'entry_id'=>!empty($entry['id']) ? absint($entry['id']) : 0,
		'gallery_id'=>$gid,
		'entry_url'=>$page && get_post_status($page)==='publish' ? get_permalink($page) : '',
		'gallery_url'=>$galleryUrls[$key],
		'label'=>!empty($entry['post_title']) ? cg_page_text($entry['post_title']) : ''
	);
}

// Overview covers are selected separately from the public gallery dataset.
// Keep their existing selection, but authorize every preview before emitting it.
function cg_page_overview_preview_entries($covers,$shortcode){
	$entries = array();
	foreach($covers as $id=>$cover){
		$gid = !empty($cover['GalleryIdToCheck']) ? absint($cover['GalleryIdToCheck']) : (!empty($cover['gid']) ? absint($cover['gid']) : 0);
		if(!$gid || !empty($cover['isCGalleriesNoGalleryEntries'])){continue;}
		$data = cg_page_stats_current_gallery($gid,$shortcode,true);
		if(!$data || empty($data['entries'][$id])){continue;}
		$entries[$id] = array_merge($cover,$data['entries'][$id],array('GalleryID'=>$gid,'id'=>absint($id)));
	}
	return $entries;
}

// The live refresh uses the same media rules as the initial header.
function cg_page_select_previews($entries,$entryId = 0,$owner = 0,$gid = 0,$limit = 3,$shortcode = 'cg_gallery'){
	$previews = array();
	$seen = array();
	$limit = max(1,min(24,absint($limit)));
	if($entryId && !$owner){return $previews;}
	// Sample before loading individual media records. Only a bounded pool reaches the browser.
	$ids = array_keys($entries);
	shuffle($ids);
	foreach($ids as $id){
		$entry = $entries[$id];
		if(($entryId && (int)$id===(int)$entryId) || empty($entry['Active']) || (int)$entry['Active']!==1 || !empty($entry['IsProfileImage'])){continue;}
		if($entryId && (empty($entry['WpUserId']) || absint($entry['WpUserId'])!==absint($owner))){continue;}
		if($gid){
			$main = cg1l_get_entry_main_data($gid,$id);
			if(empty($main[$id])){continue;}
			$entry = array_merge($main[$id],$entry,array('GalleryID'=>$gid,'id'=>$id));
		}
		$preview = cg_page_preview_media($entry);
		if($preview && empty($seen[$preview['url']])){
			$seen[$preview['url']] = true;
			$entry['id'] = $id;
			$previews[] = array_merge($preview,cg_page_preview_navigation($entry,$shortcode),array('owner'=>!empty($entry['WpUserId']) ? absint($entry['WpUserId']) : 0));
			if(count($previews)>=$limit){break;}
		}
	}
	return $previews;
}

function cg_page_render_header($args){
	$gid = absint($args['gallery_id']);
	$options = $args['options'];
	$entryId = absint($args['entry_id']);
	// Participant entry lists keep the original gallery header and its preview selection.
	if(!$entryId && !empty($args['participant_id'])){return;}
	if($entryId && !cg_member_enabled($gid,$args['shortcode'])){return;}
	$isOverview = !empty($args['is_overview']);
	if(!$isOverview && isset($args['shortcode_active']) && !$args['shortcode_active']){return;}
	$overviewOptions = $isOverview ? cg_galleries_options($args['shortcode']) : array();
	if(!$entryId && !$isOverview && !cg_page_gallery_header_enabled($gid,$args['shortcode'])){return;}
	if(!$entryId && $isOverview && in_array($args['shortcode'],array('cg_gallery','cg_gallery_no_voting'),true)){
		if(!cg_page_header_option_value($overviewOptions,'ShowGalleriesHeader')){return;}
	}
	$entries = $args['entries'];
	$galleryTitle = cg_page_gallery_title($options,$gid);
	$title = $isOverview ? cg_member_t('Discover galleries') : $galleryTitle;
	$description = cg_member_t($isOverview ? 'Explore galleries and their entries.' : 'Explore the entries in this gallery.');
	$kicker = cg_member_t($isOverview ? 'Galleries' : 'Gallery');
	if($isOverview){
		$texts = cg_page_galleries_header_texts($overviewOptions);
		$kicker = $texts['GalleriesHeaderLabel'];
		$title = $texts['GalleriesHeaderTitle'];
		$description = $texts['GalleriesHeaderSubtitle'];
	}
	$caption = '';
	$owner = 0;
	if(!$isOverview && !empty($options['pro']['SubTitleGalleriesView'])){
		$subtitle = cg_page_text($options['pro']['SubTitleGalleriesView']);
		if($subtitle!=='' && $subtitle!=='Sub title gallery '.$gid){$description = $subtitle;}
	}
	if(!$isOverview && !$entryId && in_array($args['shortcode'],array('cg_gallery','cg_gallery_no_voting'),true)){
		$texts = cg_page_gallery_header_texts($options,$gid);
		$kicker = $texts['GalleryHeaderLabel'];
		$title = $texts['GalleryHeaderTitle'];
		$description = $texts['GalleryHeaderSubtitle'];
	}
	if($entryId){
		$entry = isset($entries[$entryId]) ? $entries[$entryId] : array();
		if(empty($entry['Active']) || (int)$entry['Active']!==1 || empty($entry['WpUserId']) || !get_userdata($entry['WpUserId'])){return;}
		$owner = absint($entry['WpUserId']);
		$profile = cg_member_profile_data($owner);
		$title = $description = $kicker = '';
	}
	$scope = '';
	if($isOverview){
		$statistics = cg_page_stats_overview($entries,$args['shortcode']);
		// An accessible empty gallery still counts. An entirely blocked scope does not.
		if(empty($statistics['galleries'])){return;}
	}else{
		$statEntries = $entries;
		if($entryId){
			$scope = cg_member_t('This participant in this gallery');
		}
		$comments = isset($args['comments']) ? $args['comments'] : array();
		list($statEntries,$comments) = cg_page_stats_recent($gid,$statEntries,$comments);
		$statistics = cg_page_stats_count($statEntries,$comments,$options,$args['shortcode'],!$entryId && cg_member_enabled($gid,$args['shortcode']),$owner);
	}
	$previewEntries = $isOverview ? cg_page_overview_preview_entries($entries,$args['shortcode']) : $entries;
	$previewPool = cg_page_select_previews($previewEntries,$entryId,$owner,!empty($args['member_gallery_header']) ? $gid : 0,24,$args['shortcode']);
	$previews = array_slice($previewPool,0,3);
	$classes = 'cg-page-hero'.($entryId ? ' cg-page-hero-entry' : '').(!$previews ? ' cg-page-hero-text' : '');
	if(!empty($args['contained'])){
		$classes .= ' cg-page-hero-contained';
		if(!empty($options['visual']['FeControlsStyle']) && $options['visual']['FeControlsStyle']==='black'){$classes .= ' cg-page-dark';}
	}
	$heading = !empty($args['embedded']) ? 'h2' : 'h1';
	echo '<header class="'.$classes.'" data-cg-page-gid="'.esc_attr($args['instance']).'" data-cg-preview-caption="'.esc_attr($caption).'" data-cg-preview-pool="'.esc_attr(wp_json_encode($previewPool)).'"'.(!empty($args['member_gallery_header']) ? ' data-cg-member-gallery-header="1"' : '').($isOverview ? ' data-cg-preview-overview="1"' : '').cg_page_stats_live_attributes($args,$owner).'><div class="cg-page-hero-copy">';
	if($kicker!==''){echo '<span class="cg-page-eyebrow">'.esc_html($kicker).'</span>';}
	if($title!==''){echo '<'.$heading.'>'.esc_html($title).'</'.$heading.'>';}
	if($description!==''){echo '<p>'.esc_html($description).'</p>';}
	if($entryId){
		echo '<div class="cg-participant-profile-title"><div class="cg-participant-photo">'.cg_participants_profile_image($profile).'</div><h2>'.esc_html($profile['name']).'</h2></div>';
		echo '<div class="cg-page-entry-member">';
	}
	cg_page_render_statistics($statistics,$scope,$options);
	if($entryId){
		echo '</div>';
		echo cg_participants_entry_link($gid,$args['shortcode'],$owner,isset($args['back_url']) ? $args['back_url'] : '');
	}
	echo '</div>';
	if($previews){
		$previewLayout = cg_page_preview_layout($previews);
		echo '<div class="cg-page-hero-visual"><div class="cg-page-collage cg-page-collage-'.count($previews).$previewLayout['class'].'" style="'.esc_attr($previewLayout['style']).'" aria-hidden="true">';
		foreach($previews as $preview){
			$source = $preview['video'] ? 'data-cg-preview-video' : 'src';
			echo '<div data-cg-preview-entry="'.esc_attr($preview['entry_id']).'">';
			// Native image loading on desktop, without downloading the hidden collage on mobile.
			if(!$preview['video']){echo '<picture><source media="(max-width: 640px)" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';}
			$dimensions = $preview['width'] && $preview['height'] ? ' width="'.absint($preview['width']).'" height="'.absint($preview['height']).'"' : '';
			$sizeStyle = $dimensions ? ' style="--cg-preview-native-width:'.absint($preview['width']).'px;--cg-preview-native-height:'.absint($preview['height']).'px"' : '';
			echo '<img class="skip-lazy'.($dimensions ? ' cg-page-preview-sized' : '').($preview['video'] ? ' cg-page-preview-pending' : '').($preview['pdf'] ? ' cg-page-preview-pdf' : '').'" data-cg-preview-url="'.esc_url($preview['url']).'" data-skip-lazy="1" loading="eager" '.$source.'="'.esc_url($preview['url']).'"'.$dimensions.$sizeStyle.' alt="" decoding="async" draggable="false">';
			if(!$preview['video']){echo '</picture>';}
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
	}
	echo '</header>';
	return true;
}
