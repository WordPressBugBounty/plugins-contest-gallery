<?php
if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/cg-member-entry-links.php';
require_once __DIR__.'/cg-member-replacement.php';
require_once __DIR__.'/cg-member-profile.php';
require_once __DIR__.'/cg-gallery-card-controls.php';
require_once __DIR__.'/cg-public-profile.php';
require_once __DIR__.'/cg-page-presentation.php';
require_once __DIR__.'/cg-participants.php';
require_once __DIR__.'/cg-galleries-participants.php';

// Read defaults without rewriting existing galleries or changing upload access.
function cg_member_options_from_source($data,$gid,$shortcode){
	$data = is_array($data) ? $data : array();
	$base = !empty($data[$gid]) ? $data[$gid] : $data;
	$suffixes = array('cg_gallery'=>'','cg_gallery_no_voting'=>'-nv','cg_gallery_user'=>'-u','cg_gallery_winner'=>'-w','cg_gallery_ecommerce'=>'-ec');
	$key = isset($suffixes[$shortcode]) ? $gid.$suffixes[$shortcode] : $gid;
	$options = !empty($data[$key]) ? $data[$key] : $base;
	$memberViews = array('cg_gallery'=>0,'cg_gallery_no_voting'=>1,'cg_gallery_user'=>2);
	$enabled = false;
	if(isset($memberViews[$shortcode])){
		// Earlier saves forced -u off. Only marker 2 records an independent user preference.
		$marker = isset($base['visual']['MemberAreaPerShortcode']) ? (int)$base['visual']['MemberAreaPerShortcode'] : 0;
		$stored = $shortcode==='cg_gallery' ? $base : (isset($data[$key]) ? $data[$key] : array());
		$enabled = $marker<$memberViews[$shortcode] || !isset($stored['visual']['MemberArea']) || !empty($stored['visual']['MemberArea']);
	}
	$options['visual']['MemberArea'] = $enabled ? 1 : 0;
	return $options;
}

function cg_member_options($gid,$shortcode = 'cg_gallery'){
	return cg_member_options_from_source(cg_member_replacement_source($gid),$gid,$shortcode);
}

// Member profile availability is separate from the stored owner display preferences.
function cg_member_profile_options($options,$gid,$shortcode){
	$options['visual']['ShowProfileEntry'] = cg_uploader_profile_entry_enabled($options,$shortcode) ? 1 : 0;
	$options['pro']['ShowNickname'] = $options['visual']['ShowProfileEntry'];
	$options['pro']['ShowProfileImage'] = $options['visual']['ShowProfileEntry'];
	$options['visual']['MemberArea'] = cg_member_enabled($gid,$shortcode) ? 1 : 0;
	if(!cg_member_features_available($gid) || !cg_gallery_card_profile_supported($shortcode)){$options['visual']['ShowProfileGallery'] = 0;}
	return $options;
}

// Availability follows the stored gallery generation and its actual page type.
// Custom slugs and shortcode embeddings do not change this decision.
function cg_member_features_available($gid){
	global $wpdb;
	static $rows = array();
	$gid = absint($gid);
	if(!$gid){return false;}
	if(!isset($rows[$gid])){
		$row = $wpdb->get_row($wpdb->prepare("SELECT Version,WpPageParent FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d",$gid));
		if(!$row){return false;}
		$rows[$gid] = $row;
	}
	$row = $rows[$gid];
	if((int)$row->Version<24 || empty($row->WpPageParent)){return false;}
	$page = get_post($row->WpPageParent);
	return $page && $page->post_type==='contest-g' && !in_array($page->post_status,array('trash','auto-draft'),true);
}

function cg_member_unavailable_note(){
	return 'Available for galleries using the new page structure. Copy this gallery to use these features.';
}

function cg_member_enabled($gid,$shortcode = ''){
	return cg_member_features_available($gid) && cg_member_configured($gid,$shortcode);
}

// A login return selects a view, never an account or an authorization context.
function cg_member_initial_own_view($gid,$instance,$shortcode,$entryId,$isOverview){
	if((defined('DOING_AJAX') && DOING_AJAX) || $entryId || $isOverview || !is_user_logged_in() || !in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return false;}
	$expected = array('cg_member_view'=>'own','cg_member_instance'=>(string)$instance,'cg_member_shortcode'=>$shortcode);
	foreach($expected as $key=>$value){
		if(!isset($_GET[$key]) || !is_string($_GET[$key]) || wp_unslash($_GET[$key])!==$value){return false;}
	}
	return cg_member_enabled($gid,$shortcode);
}

// Keep the stored preference separate from runtime eligibility, including copies.
function cg_member_configured($gid,$shortcode = ''){
	if($shortcode===''){return cg_member_configured($gid,'cg_gallery') || cg_member_configured($gid,'cg_gallery_no_voting') || cg_member_configured($gid,'cg_gallery_user');}
	$options = cg_member_options($gid,$shortcode);
	return !empty($options['visual']['MemberArea']);
}

// Keep flat legacy options and explicit shortcode variants consistent on save/copy.
function cg_member_store_options(&$options,$gid,$galleryEnabled,$noVotingEnabled,$userEnabled){
	if(!cg_member_features_available($gid)){
		// Disabled admin controls must not erase preferences or accept crafted saves.
		$stored = cg_member_replacement_source($gid);
		foreach(array('flat',$gid,$gid.'-nv',$gid.'-u',$gid.'-w',$gid.'-ec') as $key){
			if($key!=='flat' && empty($options[$key])){continue;}
			$previous = $key==='flat' ? $stored : (isset($stored[$key]) ? $stored[$key] : array());
			if($key==='flat'){$target =& $options;}else{$target =& $options[$key];}
			foreach(array('MemberArea','MemberAreaPerShortcode','ShowProfileGallery') as $field){
				if(isset($previous['visual'][$field])){$target['visual'][$field] = $previous['visual'][$field];}
				else{unset($target['visual'][$field]);}
			}
			unset($target);
		}
		return;
	}
	$options['visual']['MemberArea'] = $galleryEnabled ? 1 : 0;
	$options['visual']['MemberAreaPerShortcode'] = 2;
	foreach(array(''=>'cg_gallery','-nv'=>'cg_gallery_no_voting','-u'=>'cg_gallery_user','-w'=>'cg_gallery_winner','-ec'=>'cg_gallery_ecommerce') as $suffix=>$shortcode){
		$key = $gid.$suffix;
		if(empty($options[$key])){continue;}
		$options[$key]['visual']['MemberAreaPerShortcode'] = 2;
		$options[$key]['visual']['MemberArea'] = $suffix==='' ? ($galleryEnabled ? 1 : 0) : (($suffix==='-nv' && $noVotingEnabled) || ($suffix==='-u' && $userEnabled) ? 1 : 0);
	}
}

function cg_member_render_option($gid,$shortcode){
	$available = cg_member_features_available($gid);
	$suffix = $shortcode==='cg_gallery_no_voting' ? 'NoVoting' : ($shortcode==='cg_gallery_user' ? 'User' : '');
	$name = 'multiple-pics['.$shortcode.'][visual][MemberArea]';
	$description = $shortcode==='cg_gallery_user' ? 'A personal area for signed-in members to manage their own entries and profile. Other members and their entries are not shown.' : 'Let visitors register, sign in and manage their entries and profile. Uploader profiles in gallery and entry view are set separately.';
	$bottomRadius = $shortcode==='cg_gallery' ? '' : ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px';
	echo '<div class="cg_member_options_group cg_margin_bottom_30">';
	if(!$available){echo '<p class="cg-member-unavailable-note">'.esc_html(cg_member_unavailable_note()).'</p>';}
	echo '<fieldset'.(!$available ? ' disabled class="cg_disabled"' : '').' style="border:0;margin:0;padding:0;min-width:0;">';
	echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_view_option_checkbox_container cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px'.$bottomRadius.'" id="MemberArea'.$suffix.'Container"><div class="cg_view_option_title"><p>Member profile<br><span class="cg_view_option_title_note">'.esc_html($description).' Uses email PIN.<br>Can also be used with Google login. <a href="" class="cg_go_to_link cg_no_outline_and_shadow_on_focus" data-cg-go-to-link="GoogleSignInOptions">Configure Google login options...</a><br>Upload access is set separately. <a href="" class="cg_go_to_link cg_no_outline_and_shadow_on_focus" data-cg-go-to-link="CheckLoginUploadContainer">Configure sign-in for uploads...</a></span></p></div><div class="cg_view_option_checkbox"><input type="hidden" name="'.esc_attr($name).'" value="0"><input type="checkbox" aria-label="Member profile" id="MemberArea'.$suffix.'" name="'.esc_attr($name).'" value="1"'.(cg_member_enabled($gid,$shortcode) ? ' checked' : '').'></div></div></div>';
	if($shortcode==='cg_gallery'){cg_member_replacement_render_options($gid);}
	echo '</fieldset></div>';
}

function cg_member_t($text,$replacements = array()){
	return cg_pin_auth_t($text,$replacements);
}

// Project only display fields. Callers must authorize access to this entry first.
function cg_member_owner_entry_media($row,$publicShortcode = '',$fresh = false){
	$gid = absint($row->GalleryID); $id = absint($row->id);
	$existing = !$fresh && !empty($row->Active) ? cg1l_get_entry_main_data($gid,$id) : array();
	$data = isset($existing[$id]) ? $existing[$id] : array();
	if(!$data){
		// Only frontend fields are projected. Database rows also contain private tracking data.
		foreach(array('id','rowid','PositionNumber','Timestamp','NamePic','ImgType','GalleryID','Active','Winner','Informed','WpUpload','Width','Height','rSource','rThumb','Category','WpPage','WpPageUser','WpPageNoVoting','WpPageWinner','WpPageEcommerce','EcommerceEntry','PdfPreview') as $key){
			$data[$key] = isset($row->$key) ? $row->$key : 0;
		}
		$post = $row->WpUpload ? get_post($row->WpUpload) : null;
		foreach(array('post_date','post_content','post_title','post_name') as $key){$data[$key] = $post ? $post->$key : '';}
		$data['post_caption'] = $post ? $post->post_excerpt : '';
		$data['post_alt'] = get_post_meta($row->WpUpload,'_wp_attachment_image_alt',true);
		$data['display_name'] = '';
		foreach(array('thumbnail','medium','large','full') as $size){
			$image = wp_get_attachment_image_src(!empty($row->PdfPreview) ? $row->PdfPreview : $row->WpUpload,$size);
			$data[$size] = $image ? $image[0] : '';
		}
		$data['guid'] = wp_get_attachment_url($row->WpUpload);
		// Social sources are CG posts, so WordPress's attachment URL API returns false.
		$social = cg_member_replacement_social_source($row->WpUpload);
		if($social){
			$data['guid'] = $post->guid;
			$data['ImgType'] = $social['extension'];
			$data['post_mime_type'] = $post->post_mime_type;
			$data['type'] = '';
		}
		if(!$data['full']){$data['full'] = $data['guid'];}
		if(!empty($row->PdfPreview)){$data['PdfOriginal'] = $data['guid']; $data['ImgType'] = 'png';}
		if(cg_member_replacement_attachment_type($row->WpUpload)){$data = array_merge($data,cg_member_replacement_media_data($row->WpUpload,absint($row->PdfPreview)));}
	}
	$data['Active'] = absint($row->Active);
	$data['WpUserId'] = absint($row->WpUserId);
	$links = cg_member_entry_links($row,$publicShortcode);
	$data['ShareUrl'] = $links['ShareUrl'];
	$data['entryGuid'] = $data['MemberEntryGuid'] = $links['entryGuid'];
	$query = array('Exif'=>maybe_unserialize($row->Exif),'MultipleFiles'=>maybe_unserialize($row->MultipleFiles));
	if(empty($query['MultipleFiles']) || !is_array($query['MultipleFiles'])){$query['MultipleFiles'] = '';}
	if(!empty($query['MultipleFiles']) && function_exists('cg_entry_watermark_append_url_version_to_multiple_files')){$query['MultipleFiles'] = cg_entry_watermark_append_url_version_to_multiple_files($query['MultipleFiles']);}
	return array_merge($data,$query);
}

// Read-only owner projection. Never write unpublished entries into public JSON segments.
function cg_member_owner_data($gid,$publicShortcode = ''){
	global $wpdb;
	$result = array('main'=>array(),'info'=>array(),'query'=>array(),'urls'=>array(),'stats'=>array(),'comments'=>array());
	if(!is_user_logged_in() || !cg_member_enabled($gid)){return $result;}
	$rows = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery WHERE GalleryID=%d AND WpUserId=%d ORDER BY id DESC',$gid,get_current_user_id()));
	$fields = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery_f_input WHERE GalleryID=%d',$gid),OBJECT_K);
	foreach($rows as $row){
		$id = absint($row->id);
		$data = cg_member_owner_entry_media($row,$publicShortcode);
		$query = array('Exif'=>$data['Exif'],'MultipleFiles'=>$data['MultipleFiles']);
		$stats = cg1l_return_stats_placeholder_values(true);
		$liveStats = !empty($row->Active) ? cg1l_get_entry_stats_data($gid,$id) : array();
		if(isset($liveStats[$id])){$stats = $liveStats[$id];}
		$result['main'][$id] = array_merge($data,$stats);
		$result['query'][$id] = $query;
		$result['stats'][$id] = $stats;
		$comments = cg1l_get_entry_comments_data($gid,$id);
		if(isset($comments[$id])){$result['comments'][$id] = $comments[$id];}
		$result['urls'][$id] = array('entryGuid'=>$data['entryGuid'],'MemberEntryGuid'=>$data['entryGuid'],'ShareUrl'=>$data['ShareUrl']);
		$result['info'][$id] = array();
		$entries = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery_entries WHERE GalleryID=%d AND pid=%d',$gid,$id));
		foreach($entries as $entry){
			if(empty($fields[$entry->f_input_id])){continue;}
			$field = $fields[$entry->f_input_id];
			$content = maybe_unserialize($field->Field_Content);
			$value = $field->Field_Type==='comment-f' ? $entry->Long_Text : $entry->Short_Text;
			if($field->Field_Type==='date-f' && !empty($entry->InputDate) && $entry->InputDate!=='0000-00-00 00:00:00'){
				$value = date(str_replace(array('YYYY','MM','DD'),array('Y','m','d'),!empty($content['format']) ? $content['format'] : 'Y-m-d'),strtotime($entry->InputDate));
			}
			$result['info'][$id][$field->id] = array('field-type'=>$field->Field_Type,'field-title'=>!empty($content['titel']) ? $content['titel'] : '','field-content'=>$value);
		}
	}
	return $result;
}

function cg_member_client_strings(){
	$strings = array(
		'Please try again.',
		'The connection failed. Please try again.',
		'Please reload the page and try again.',
		'This gallery is not available.',
		'Check your email for your code.',
		'Please wait {seconds} seconds before requesting another code.',
		'Take a look at this entry',
		'Link copied',
		'Copy this link:',
		'Copy link',
		'More apps…',
		'Sharing is unavailable. You can copy the link instead.',
		'Share this entry',
		'Close',
		'Not published',
		'All entries',
		'Loading…',
		'Back to gallery',
		'Back to galleries',
		'Cancel',
		'Comments',
		'ratings',
		'Ratings and comments',
		'Vote'
	);
	$result = array();
	foreach(array_merge($strings,cg_member_interface_strings(),cg_member_voting_translation_strings(),cg_member_entry_action_strings(),cg_member_profile_strings(),array('Edit profile','Remove profile image','Close'),cg1l_share_strings()) as $string){$result[$string] = cg_member_t($string);}
	return $result;
}

function cg_member_admin_mail_note_dismissed(){
	global $wpdb;
	return get_user_meta(get_current_user_id(),$wpdb->get_blog_prefix().'cg_member_admin_mail_note_dismissed',true)==='1';
}

function cg_member_render_admin_mail_note($gid){
	static $rendered = false;
	if($rendered || !current_user_can('manage_options') || cg_member_admin_mail_note_dismissed()){return;}
	$rendered = true;
	$url = add_query_arg(array('page'=>cg_get_version().'/index.php','edit_options'=>'true','option_id'=>absint($gid),'cg_go_to'=>'cgRegistrationMailOptions','cg_nonce'=>wp_create_nonce('cg_nonce')),admin_url('admin.php'));
	echo '<div class="cg-member-admin-mail-note"><button type="button" class="cg-member-admin-mail-note-close" aria-label="'.esc_attr(cg_member_t('Close')).'" title="'.esc_attr(cg_member_t('Close')).'"><span aria-hidden="true">&times;</span></button><strong><span aria-hidden="true">⚠️</span> Administrator note: test registration emails</strong><p>Test sign-in in a private browser window and confirm that the PIN email arrives.</p><p>The PIN is sent to the email address entered there.</p><a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">Registration email settings</a><p class="cg-member-admin-mail-note-error" role="alert" hidden></p></div>';
}

function cg_member_render_header($gid,$instance,$shortcode,$options,$publicInstance = '',$publicShortcode = '',$wpUserIdsData = array(),$initialOwnPage = 0){
	if(!in_array($shortcode,array('cg_gallery','cg_gallery_no_voting','cg_gallery_user'),true)){return;}
	global $wpdb;
	static $participantsNavigationClaimed = false;
	$user = wp_get_current_user();
	$own = $shortcode==='cg_gallery_user';
	if(!$publicShortcode){$publicShortcode = $shortcode;}
	$personal = $publicShortcode==='cg_gallery_user';
	if(!cg_member_enabled($gid,$publicShortcode)){return;}
	$publicInstance = $publicInstance ? $publicInstance : $instance;
	// Public member galleries expose participants on direct pages and in embedded shortcodes.
	$showParticipants = !$personal;
	$participantsNavigation = cg_participants_navigation_args();
	$navigationGallery = isset($_GET['cgl_gallery']) && is_scalar($_GET['cgl_gallery']) ? absint($_GET['cgl_gallery']) : 0;
	// Match the first eligible toolbar restored by participants.sourceArea(), including profile links.
	$participantsActive = $showParticipants && !$participantsNavigationClaimed && !empty($participantsNavigation) && (!$navigationGallery || $navigationGallery===absint($gid));
	if($participantsActive){$participantsNavigationClaimed = true;}
	$token = cg1l_create_runtime_context_token(array('realGid'=>$gid,'gid'=>$publicInstance,'shortcodeName'=>$publicShortcode,'pagePresentation'=>$showParticipants));
	$count = $user->ID ? absint($wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->prefix.'contest_gal1ery WHERE GalleryID=%d AND WpUserId=%d',$gid,$user->ID))) : 0;
	$participantsCount = $showParticipants ? cg_participants_count($gid,$publicShortcode) : 0;
	echo '<section class="cg-member-area" data-cg-gid="'.esc_attr($instance).'" data-cg-real-gid="'.absint($gid).'" data-cg-public-instance="'.esc_attr($publicInstance).'" data-cg-public-shortcode="'.esc_attr($publicShortcode).'" data-cg-member-token="'.esc_attr($token).'" data-cg-community="'.(cg_community_enabled() ? '1' : '0').'" data-cg-member-user="'.absint($user->ID).'" data-cg-member-view="'.($own ? 'own' : 'all').'"'.($initialOwnPage ? ' data-cg-member-initial-own="1" data-public-step="'.absint($initialOwnPage).'"' : '').'>';
	echo '<div class="cg-member-toolbar">';
	if(!$user->ID && $participantsActive){echo '<button type="button" class="cg-participants-all">'.esc_html(cg_member_t('All entries')).'</button>';}
	if($user->ID){
		echo '<div class="cg-member-tabs" role="group" aria-label="'.esc_attr(cg_member_t('Gallery view')).'">';
		if(!$personal){echo '<button type="button" data-cg-member-view="all" aria-pressed="'.(!$own && !$participantsActive ? 'true' : 'false').'">'.esc_html(cg_member_t('All entries')).'</button>';}
		echo '<button type="button" data-cg-member-view="own" aria-pressed="'.($own && !$participantsActive ? 'true' : 'false').'">'.esc_html(cg_member_t('My entries')).' <span class="cg-member-count">'.$count.'</span></button>';
		if($showParticipants){echo '<button type="button" class="cg-participants-toggle" aria-pressed="'.($participantsActive ? 'true' : 'false').'">'.esc_html(cg_member_t('Participants')).' <span class="cg-member-count">'.$participantsCount.'</span></button>';}
		echo '</div>';
		$profileImage = cg1l_frontend_get_preferred_wp_user_avatar_url($user->ID,$wpUserIdsData);
		echo '<div class="cg-member-account">';
		echo '<button type="button" class="cg-member-profile" data-cg-wp-user-id="'.absint($user->ID).'" title="'.esc_attr(cg_member_t(cg_community_enabled() ? 'Member area' : 'Edit profile')).'" aria-label="'.esc_attr(cg_member_t(cg_community_enabled() ? 'Member area' : 'Edit profile').': '.$user->display_name).'" aria-haspopup="dialog"><img class="cg-member-avatar skip-lazy" data-skip-lazy="1" loading="eager"'.($profileImage ? ' src="'.esc_url($profileImage).'"' : ' hidden').' alt=""><span class="cg-member-name">'.esc_html($user->display_name).'</span><span class="cg-member-profile-edit" aria-hidden="true">&#9998;</span><span class="cg-community-badge" hidden></span></button>';
		echo '</div>';
	}else{
		echo '<button type="button" class="cg-member-login">'.esc_html(cg_member_t('Register / Sign in')).'</button><span class="cg-member-intro">'.esc_html(cg_member_t('Join and show your own entries.')).'</span>';
	}
	if(!$user->ID && $showParticipants){echo '<button type="button" class="cg-participants-toggle" aria-pressed="'.($participantsActive ? 'true' : 'false').'">'.esc_html(cg_member_t('Participants')).' <span class="cg-member-count">'.$participantsCount.'</span></button>';}
	echo '</div><div class="cg-member-message" role="status" aria-live="polite"></div>';
	cg_member_render_admin_mail_note($gid);
	echo '</section>';
	return $own && !$count;
}

function cg_member_render_empty(){
	echo '<div class="cg-member-empty"><strong>'.esc_html(cg_member_t('Show your first entry')).'</strong><p>'.esc_html(cg_member_t('Your uploads will appear here.')).'</p></div>';
}

// Standalone upload sign-in does not require a member area or a modern gallery page.
function cg_member_upload_auth_enabled($gid){
	$source = cg_member_replacement_source($gid);
	$options = cg_member_options_from_source($source,$gid,'cg_users_upload');
	if(empty($options['pro']['RegUserUploadOnly']) || (int)$options['pro']['RegUserUploadOnly']!==1 || empty($options['visual']['ShowPinFormUploading'])){return false;}
	$interval = cg_shortcode_interval_check($gid,$source,'cg_users_upload');
	if(!$interval['shortcodeIsActive']){return false;}
	// Galleries before 21.1 also use the former contest start/end controls.
	$general = $options['general'];
	if(floatval($general['Version'])<21.1){
		if((int)$general['ContestEnd']===2 || ((int)$general['ContestEnd']===1 && time()>=(int)$general['ContestEndTime']) || ((int)$general['ContestStart']===1 && time()<(int)$general['ContestStartTime'])){return false;}
	}
	return true;
}

function cg_member_render_upload_access($gid,$instance,$options){
	$token = cg1l_create_runtime_context_token(array('realGid'=>$gid,'gid'=>$instance,'shortcodeName'=>'cg_users_upload'));
	$titleId = 'cgUploadAuthTitle'.preg_replace('/[^A-Za-z0-9_-]/','',(string)$instance);
	echo '<div class="cg-upload-auth-card cg-paid-actions-upload-access" data-cg-gid="'.esc_attr($instance).'" data-cg-member-token="'.esc_attr($token).'" aria-labelledby="'.esc_attr($titleId).'">';
	echo '<span class="cg-paid-actions-upload-access-icon"><span class="cg-paid-actions-balance-icon cg-paid-actions-balance-icon-upload" aria-hidden="true"></span></span>';
	echo '<span class="cg-paid-actions-upload-access-kicker">'.esc_html(cg_paid_actions_t('UploadAccessUpper')).'</span>';
	echo '<strong class="cg-paid-actions-upload-access-title" id="'.esc_attr($titleId).'">'.esc_html(cg_paid_actions_t('SignInToUpload')).'</strong>';
	echo '<div class="cg-paid-actions-upload-access-description">'.contest_gal1ery_convert_for_html_output_without_nl2br($options['pro']['RegUserUploadOnlyText']).'</div>';
	echo '<button type="button" class="cg-paid-actions-primary cg-upload-auth-action" aria-haspopup="dialog">'.esc_html(cg_paid_actions_t('SignInToContinue')).'</button>';
	echo '<div class="cg-upload-auth-message" role="status" aria-live="polite"></div></div>';
}

function cg_member_ajax_context($allowUploadAuth = false){
	nocache_headers();
	header('Cache-Control: no-store, private, max-age=0');
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	$token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
	$context = cg1l_verify_runtime_context_token($token);
	if($allowUploadAuth && $context && $context['shortcodeName']==='cg_users_upload'){
		if(!preg_match('/^'.absint($context['realGid']).'-cf(?:-ext-\d+)?$/',$context['gid']) || !cg_member_upload_auth_enabled($context['realGid'])){
			wp_send_json_error(array('message'=>cg_member_t('This gallery is not available.')));
		}
		return $context;
	}
	$suffix = $context && $context['shortcodeName']==='cg_gallery_no_voting' ? '-nv' : ($context && $context['shortcodeName']==='cg_gallery_user' ? '-u' : '');
	if(!$context || !in_array($context['shortcodeName'],array('cg_gallery','cg_gallery_no_voting','cg_gallery_user'),true) || !preg_match('/^'.absint($context['realGid']).$suffix.'(?:-ext-\d+)?$/',$context['gid']) || !cg_member_enabled($context['realGid'],$context['shortcodeName'])){
		wp_send_json_error(array('message'=>cg_member_t('This gallery is not available.')));
	}
	return $context;
}

function cg_member_ajax_dismiss_admin_mail_note(){
	if(!current_user_can('manage_options') || $_SERVER['REQUEST_METHOD']!=='POST'){
		wp_send_json_error(array('message'=>cg_member_t('Please reload the page and try again.')),403);
	}
	cg_member_ajax_context();
	global $wpdb;
	// The authenticated user and current website alone determine the preference key.
	update_user_meta(get_current_user_id(),$wpdb->get_blog_prefix().'cg_member_admin_mail_note_dismissed',1);
	if(!cg_member_admin_mail_note_dismissed()){
		wp_send_json_error(array('message'=>cg_member_t('Please try again.')),500);
	}
	wp_send_json_success();
}
add_action('wp_ajax_cg_member_dismiss_admin_mail_note','cg_member_ajax_dismiss_admin_mail_note');

function cg_member_browser_binding(){
	$session = cg1l_get_or_create_frontend_session_id(true);
	return $session ? hash_hmac('sha256','cg-member|'.$session,wp_salt('auth')) : '';
}

function cg_member_ajax_auth(){
	$context = cg_member_ajax_context(true);
	$mode = isset($_POST['mode']) ? sanitize_key($_POST['mode']) : '';
	if($context['shortcodeName']==='cg_users_upload' && !in_array($mode,array('bootstrap','request','resend','verify'),true)){
		wp_send_json_error(array('message'=>cg_member_t('This gallery is not available.')));
	}
	if($mode==='logout'){
        $target = cg_account_navigation_logout_target(wp_get_current_user(),cg_account_navigation_return_url(),true);
		wp_logout();
		wp_send_json_success(array('reload'=>true,'redirect'=>$target));
	}
	if(is_user_logged_in()){wp_send_json_success(array('reload'=>true));}
	if($mode==='bootstrap'){
		$google = cg_get_google_options();
		$clientId = !empty($google->ClientId) ? contest_gal1ery_convert_for_html_output_without_nl2br($google->ClientId) : '';
		wp_send_json_success(array('authHtml'=>cg_pin_auth_render_auth('gallery-member',false,$clientId!==''),'googleClientId'=>$clientId,'googleButtonStyle'=>!empty($google->ButtonStyle) && $google->ButtonStyle==='dark' ? 'dark' : 'bright'));
	}
	$binding = cg_member_browser_binding();
	if(!$binding){wp_send_json_error(array('message'=>cg_member_t('Please allow cookies to sign in.')));}
	if($mode==='request'){
		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$result = cg_pin_auth_request($email,'gallery-member',array('galleryId'=>$context['realGid'],'gid'=>$context['gid'],'shortcodeName'=>$context['shortcodeName'],'browserBinding'=>$binding));
	}else{
		$key = isset($_POST['requestKey']) ? sanitize_text_field(wp_unslash($_POST['requestKey'])) : '';
		$challenge = cg_pin_auth_get_challenge($key);
        if(!$challenge && $mode==='resend'){
            // This is a new request, never verification of an expired challenge.
            $email = isset($_POST['email']) && is_string($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
            $result = cg_pin_auth_request($email,'gallery-member',array('galleryId'=>$context['realGid'],'gid'=>$context['gid'],'shortcodeName'=>$context['shortcodeName'],'browserBinding'=>$binding));
        }else{
		if(!$challenge || $challenge['source']!=='gallery-member' || empty($challenge['context']['browserBinding']) || !cg1l_hash_equals($binding,$challenge['context']['browserBinding']) || absint($challenge['context']['galleryId'])!==absint($context['realGid']) || empty($challenge['context']['gid']) || $challenge['context']['gid']!==$context['gid'] || $challenge['context']['shortcodeName']!==$context['shortcodeName']){
			wp_send_json_error(array('message'=>cg_member_t('The PIN is invalid or expired.')));
		}
		if($mode==='resend'){
			$result = cg_pin_auth_resend($key,array('browserBinding'=>$binding,'galleryId'=>$context['realGid'],'gid'=>$context['gid'],'shortcodeName'=>$context['shortcodeName']));
		}elseif($mode==='verify'){
			$pin = isset($_POST['pin']) ? sanitize_text_field(wp_unslash($_POST['pin'])) : '';
			$result = cg_pin_auth_verify($key,$pin,'gallery-member',array('browserBinding'=>$binding,'galleryId'=>$context['realGid'],'gid'=>$context['gid'],'shortcodeName'=>$context['shortcodeName']));
			if(is_array($result) && !empty($result['userId']) && cg1l_auth_eligible_user($result['userId'],$result['email'])){
				// PIN and browser binding were verified. Reload before any gallery request uses the new cookie.
				wp_set_current_user($result['userId']);
				wp_set_auth_cookie($result['userId'],true,is_ssl());
				wp_send_json_success(array('reload'=>true));
			}
			if(is_array($result)){$result = false;}
		}else{$result = false;}
        }
	}
	if(is_wp_error($result)){
		$details = $result->get_error_data();
		wp_send_json_error(array('message'=>$result->get_error_message(),'retryAfter'=>is_array($details) && !empty($details['retryAfter']) ? absint($details['retryAfter']) : 0));
	}
	if(!$result){wp_send_json_error(array('message'=>cg_member_t('The PIN is invalid or expired.')));}
	wp_send_json_success($result);
}
add_action('wp_ajax_cg_member_auth','cg_member_ajax_auth');
add_action('wp_ajax_nopriv_cg_member_auth','cg_member_ajax_auth');

// Embedded My entries may use the signed public gallery's voting context.
function cg_member_is_public_voting_context($gid,$instance,$entryId,$token){
	if(!is_user_logged_in() || !is_string($token) || $token===''){return false;}
	$context = cg1l_verify_runtime_context_token($token);
	if(!$context || absint($context['realGid'])!==absint($gid) || $context['shortcodeName']!=='cg_gallery' ||
		!preg_match('/^'.absint($gid).'(?:-ext-\d+)?$/',$context['gid']) ||
		$instance!==absint($gid).'-u-ext-member-'.substr(md5($context['gid']),0,8) || !cg_member_enabled($gid,'cg_gallery')){return false;}
	global $wpdb;
	return (bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery WHERE id = %d AND GalleryID = %d AND WpUserId = %d AND Active = 1",absint($entryId),absint($gid),get_current_user_id()));
}

function cg_member_ajax_gallery(){
	$context = cg_member_ajax_context();
	if(!empty($_POST['countsOnly'])){
		global $wpdb;
		$userId = get_current_user_id();
		$ownCount = $userId ? absint($wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$wpdb->prefix.'contest_gal1ery WHERE GalleryID=%d AND WpUserId=%d',$context['realGid'],$userId))) : 0;
		wp_send_json_success(array('participantCount'=>cg_participants_count($context['realGid'],$context['shortcodeName']),'ownCount'=>$ownCount));
	}
	$GLOBALS['cgPagePresentation'] = !empty($context['pagePresentation']);
	$own = !empty($_POST['view']) && $_POST['view']==='own';
	$personal = $context['shortcodeName']==='cg_gallery_user';
	// The signed personal context never authorizes the public gallery.
	if($personal && !$own){wp_send_json_error(array('message'=>cg_member_t('This gallery is not available.')));}
	if($own && !is_user_logged_in()){wp_send_json_error(array('message'=>cg_member_t('Please sign in.')));}
	if(!empty($_POST['ownerDataOnly'])){
		// A private snapshot for the current owner. Never render or cache it as public gallery data.
		if(!$own || !is_user_logged_in()){wp_send_json_error(array('message'=>cg_member_t('Please sign in.')));}
		wp_send_json_success(array('ownerData'=>cg_member_owner_data($context['realGid'],$context['shortcodeName'])));
	}
	// Render the same page that the replacement member runtime will initialize.
	set_query_var('cgl_page',$own ? 1 : max(1,absint(isset($_POST['page']) ? $_POST['page'] : 1)));
	$galeryID = absint($context['realGid']);
	$cgMemberPublicInstance = $context['gid'];
	$cgMemberPublicShortcode = $context['shortcodeName'];
	$cgMemberInstanceId = $own && !$personal ? $context['realGid'].'-u-ext-member-'.substr(md5($context['gid']),0,8) : $context['gid'];
	$shortcode_name = $own ? 'cg_gallery_user' : $cgMemberPublicShortcode;
	$entryId = 0;
	$isCgRuntimeFresh = true;
	$isCGalleries = false;
	$wp_upload_dir = wp_upload_dir();
	$file = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$galeryID.'/json/'.$galeryID.'-options.json';
	$options = json_decode(file_get_contents($file),true);
	$frontend_gallery = '';
	include(__DIR__.'/../../v10/include-scripts-v10.php');
	wp_send_json_success(array('html'=>$frontend_gallery,'gid'=>$cgMemberInstanceId));
}
add_action('wp_ajax_cg_member_gallery','cg_member_ajax_gallery');
add_action('wp_ajax_nopriv_cg_member_gallery','cg_member_ajax_gallery');
