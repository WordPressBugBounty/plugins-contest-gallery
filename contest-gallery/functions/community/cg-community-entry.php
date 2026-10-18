<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_community_entry')){
function cg_community_entry($viewer,$request,$where=null){
	global $wpdb;
	if($where===null){$where=cg_community_feed_where($viewer,$request);}
	if(is_wp_error($where)){return $where;}
	$entryId=!empty($request['entryId']) ? absint($request['entryId']) : 0;
	if(!$entryId){return cg_community_error('This entry is no longer available.');}
	$entry=$wpdb->get_row($wpdb->prepare("SELECT e.* FROM {$wpdb->prefix}contest_gal1ery e WHERE $where AND e.id=%d",$entryId));
	$gallery=$entry ? cg_community_visible_entry($entry,cg_community_feed_shortcode($request)) : false;
	if(!$gallery){return cg_community_error('This entry is no longer available.');}
	$galeryID=absint($entry->GalleryID);
	$shortcode_name=$gallery['shortcode'];
	$options=cg_member_replacement_source($galeryID);
	if(!is_array($options)){return cg_community_error('This entry is no longer available.');}
	$field=$shortcode_name==='cg_gallery_no_voting' ? 'WpPageNoVoting' : 'WpPage';
	$paidActions=array('vote'=>false,'comment'=>false);
	if(function_exists('cg_paid_actions_is_action_active_for_request')){
		$paidActions['vote']=$shortcode_name==='cg_gallery' && cg_paid_actions_is_action_active_for_request($galeryID,'vote');
		$paidActions['comment']=cg_paid_actions_is_action_active_for_request($galeryID,'comment');
	}
	$entryUrl=esc_url_raw(get_permalink($entry->$field));
	// A public entry instance must never replace the gallery already on the page.
	$cgCommunityEntryInstanceId=$galeryID.($shortcode_name==='cg_gallery_no_voting' ? '-nv' : '').'-ext-'.wp_rand(1000000,999999999);
	$cgCommunityEntryDisplayTheme=cg_community_display_theme(isset($request['displayTheme']) ? $request['displayTheme'] : '');
	$isCGalleries=false;
	$isCgRuntimeFresh=true;
	$isAjax=true;
	$wp_upload_dir=wp_upload_dir();
	$frontend_gallery='';
	include(__DIR__.'/../../v10/include-scripts-v10.php');
	return array('html'=>$frontend_gallery,'gid'=>$cgCommunityEntryInstanceId,'entryId'=>$entryId,
		'entryUrl'=>$entryUrl,'paidActions'=>$paidActions);
}
}
