<?php
if(!defined('ABSPATH')){exit;}

function cg_member_public_shortcode($gid,$shortcode = ''){
	if(in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true)){return $shortcode;}
	if(isset($_GET['cg_member_view']) && $_GET['cg_member_view']==='no_voting'){return 'cg_gallery_no_voting';}
	return cg_member_enabled($gid,'cg_gallery') ? 'cg_gallery' : 'cg_gallery_no_voting';
}

// Resolve from database-owned page IDs, never by rewriting a permalink slug.
function cg_member_entry_links($row,$shortcode = ''){
	$personal = $shortcode==='cg_gallery_user';
	$shortcode = cg_member_public_shortcode($row->GalleryID,$shortcode);
	$field = $shortcode==='cg_gallery_no_voting' ? 'WpPageNoVoting' : 'WpPage';
	$userUrl = !empty($row->WpPageUser) ? get_permalink($row->WpPageUser) : '';
	$publicUrl = !empty($row->$field) && get_post_status($row->$field)==='publish' ? get_permalink($row->$field) : '';
	if($userUrl && $shortcode==='cg_gallery_no_voting'){$userUrl = add_query_arg('cg_member_view','no_voting',$userUrl);}
	return array('entryGuid'=>$personal ? $userUrl : ($publicUrl ? $publicUrl : $userUrl),'ShareUrl'=>!empty($row->Active) ? $publicUrl : '');
}

function cg_member_entry_url_variants($row){
	$urls = array();
	foreach(array('cg_gallery','cg_gallery_no_voting','cg_gallery_user') as $shortcode){$urls[$shortcode]=cg_member_entry_links($row,$shortcode);}
	return $urls;
}

// A reload of an unpublished member entry keeps the requested gallery URL,
// but uses the existing private owner runtime and its data-access checks.
add_action('template_redirect','cg_member_prepare_private_entry',1);
function cg_member_prepare_private_entry(){
	if(!is_user_logged_in() || !is_singular(array('contest-g','contest-g-no-voting'))){return;}
	$shortcode = get_post_type(get_queried_object_id())==='contest-g-no-voting' ? 'cg_gallery_no_voting' : 'cg_gallery';
	$field = $shortcode==='cg_gallery_no_voting' ? 'WpPageNoVoting' : 'WpPage';
	global $wpdb;
	$row = $wpdb->get_row($wpdb->prepare('SELECT id,GalleryID FROM '.$wpdb->prefix.'contest_gal1ery WHERE '.$field.'=%d AND WpUserId=%d AND Active=0',get_queried_object_id(),get_current_user_id()));
	if(!$row || !cg_member_enabled($row->GalleryID,$shortcode)){return;}
	$GLOBALS['cgMemberPrivateEntryContext'] = array('gid'=>absint($row->GalleryID),'entryId'=>absint($row->id),'shortcode'=>$shortcode);
	if(!defined('DONOTCACHEPAGE')){define('DONOTCACHEPAGE',true);}
	nocache_headers();
}

function cg_member_can_manage_entry($gid,$shortcode,$entry,$users = array()){
	if(!empty($GLOBALS['cgParticipantRendering'])){return false;}
	$viewer = get_current_user_id();
	$owner = !empty($entry['WpUserId']) ? absint($entry['WpUserId']) : (!empty($users['mainDataWpUserIds'][$entry['id']]) ? absint($users['mainDataWpUserIds'][$entry['id']]) : 0);
	return $viewer && $viewer===$owner && ($shortcode==='cg_gallery_user' || (in_array($shortcode,array('cg_gallery','cg_gallery_no_voting'),true) && cg_member_enabled($gid,$shortcode)));
}

// Old personal bookmarks remain usable after publication. Other viewers retain
// the existing user-gallery access checks and never receive private redirects.
add_action('template_redirect','cg_member_redirect_published_entry',1);
function cg_member_redirect_published_entry(){
	if(!is_user_logged_in() || !is_singular('contest-g-user')){return;}
	global $wpdb;
	$row = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'contest_gal1ery WHERE WpPageUser=%d AND WpUserId=%d AND Active=1',get_queried_object_id(),get_current_user_id()));
	if(!$row || !cg_member_enabled($row->GalleryID) || cg_member_enabled($row->GalleryID,'cg_gallery_user')){return;}
	$links = cg_member_entry_links($row);
	if(!$links['ShareUrl']){return;}
	$url = $links['ShareUrl'];
	// Only the established navigation parameters survive the temporary redirect.
	foreach(array('cgl_from_gallery_page','cgl_from_galleries_page','cgl_origin_page_id','cgl_gallery') as $key){
		if(isset($_GET[$key]) && is_scalar($_GET[$key])){$url = add_query_arg($key,absint($_GET[$key]),$url);}
	}
	nocache_headers();
	wp_safe_redirect($url,302);
	exit;
}
