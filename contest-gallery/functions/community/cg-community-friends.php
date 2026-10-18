<?php
if(!defined('ABSPATH')){exit;}

// Counts and paginated lists select the same available website accounts.
if(!function_exists('cg_community_friends_from_sql')){
function cg_community_friends_from_sql($viewer,$list='accepted'){
	global $wpdb;$table=cg_community_table('friends');
	$status=in_array($list,array('incoming','outgoing'),true) ? 'pending' : 'accepted';
	$from=$wpdb->prepare("FROM $table f INNER JOIN {$wpdb->users} u ON u.ID=IF(f.UserLow=%d,f.UserHigh,f.UserLow)",$viewer);
	$where=$wpdb->prepare('(f.UserLow=%d OR f.UserHigh=%d) AND f.Status=%s AND u.user_status=0',$viewer,$viewer,$status);
	if($status==='pending'){$where.=$wpdb->prepare(' AND f.RequesterID'.($list==='incoming' ? '<>' : '=').'%d',$viewer);}
	if(is_multisite()){$where.=$wpdb->prepare(" AND u.spam=0 AND u.deleted=0 AND EXISTS (SELECT 1 FROM {$wpdb->usermeta} um WHERE um.user_id=u.ID AND um.meta_key=%s)",$wpdb->get_blog_prefix().'capabilities');}
	return $from.' WHERE '.$where;
}
}
if(!function_exists('cg_community_friend_count')){
function cg_community_friend_count($viewer){
	global $wpdb;
	return (int)$wpdb->get_var('SELECT COUNT(*) '.cg_community_friends_from_sql($viewer));
}
}

if(!function_exists('cg_community_friends')){
function cg_community_friends($viewer,$request){
	global $wpdb;$list=$request['list'];$offset=(max(1,$request['page'])-1)*20;$items=array();
	if($list==='blocked'){
		$table=cg_community_table('blocks');$rows=$wpdb->get_results($wpdb->prepare("SELECT BlockedID AS Target FROM $table WHERE BlockerID=%d ORDER BY id DESC LIMIT 21 OFFSET %d",$viewer,$offset));
	}else{
		$from=cg_community_friends_from_sql($viewer,$list);
		$rows=$wpdb->get_results($wpdb->prepare("SELECT u.ID AS Target $from ORDER BY f.id DESC LIMIT 21 OFFSET %d",$offset));
	}
	$more=count($rows)>20;
	foreach(array_slice($rows,0,20) as $row){
		if(!cg_community_user($row->Target)){continue;}
		$item=array('person'=>cg_community_person($row->Target),'relationship'=>cg_community_relationship($viewer,(int)$row->Target));
		if($list!=='blocked'){$item['profile']=cg_public_member_profile_request_source($viewer,(int)$row->Target,!empty($request['gallery']) ? absint($request['gallery']) : 0,isset($request['shortcode']) ? $request['shortcode'] : 'cg_gallery');}
		$items[]=$item;
	}
	return array('items'=>$items,'more'=>$more);
}
}
if(!function_exists('cg_community_friend_action')){
function cg_community_friend_action($viewer,$request){
	global $wpdb;$target=$request['target'];$action=$request['operation'];$table=cg_community_table('friends');
	if(!$target || $viewer===$target || !cg_community_user($target) || cg_community_blocked($viewer,$target)){return cg_community_error();}
	$row=cg_community_pair_row('friends',$viewer,$target);$now=time();
	if($action==='request'){
		if(!cg_community_contact_allowed($viewer,$target,$request)){return cg_community_error();}
		if($row && in_array($row->Status,array('pending','accepted'),true)){return array('relationship'=>cg_community_relationship($viewer,$target));}
		if(!cg_security_rate_take_locked('community-friend',$viewer,10,HOUR_IN_SECONDS)){return cg_community_error('Please wait before sending more requests.',429);}
		$data=array('RequesterID'=>$viewer,'GalleryID'=>$request['gallery'],'Status'=>'pending','UpdatedAt'=>$now,'LastRequestedAt'=>$now);
		if($row){$saved=$wpdb->update($table,$data,array('id'=>$row->id));}
		else{$pair=cg_community_pair($viewer,$target);$data['UserLow']=$pair[0];$data['UserHigh']=$pair[1];$data['CreatedAt']=$now;$saved=$wpdb->insert($table,$data);}
		if($saved===false){return cg_community_error('Please try again.',500);}
		$result=array('relationship'=>cg_community_relationship($viewer,$target));
		$result['_mail']=array('type'=>'friend','sender'=>$viewer,'recipient'=>$target,'gallery'=>$request['gallery'],'event'=>$now);
		return $result;
	}
	if(!$row){return cg_community_error();}
	$incoming=(int)$row->RequesterID!==$viewer;
	$statuses=array('accept'=>'accepted','decline'=>'declined','cancel'=>'cancelled','remove'=>'removed');
	if(!isset($statuses[$action])){return cg_community_error();}
	if($row->Status===$statuses[$action]){return array('relationship'=>cg_community_relationship($viewer,$target));}
	if($action==='remove' ? $row->Status!=='accepted' : ($row->Status!=='pending' || ($action==='cancel' ? $incoming : !$incoming))){return cg_community_error();}
	if($wpdb->update($table,array('Status'=>$statuses[$action],'UpdatedAt'=>$now),array('id'=>$row->id))===false){return cg_community_error('Please try again.',500);}
	return array('relationship'=>cg_community_relationship($viewer,$target));
}
}
if(!function_exists('cg_community_block_action')){
function cg_community_block_action($viewer,$request){
	global $wpdb;$target=$request['target'];$table=cg_community_table('blocks');
	if(!$target || $target===$viewer || !cg_community_user($target)){return cg_community_error();}
	if($request['operation']==='unblock'){
		$saved=$wpdb->delete($table,array('BlockerID'=>$viewer,'BlockedID'=>$target));
	}else{
		if($request['operation']!=='block' || !cg_community_contact_allowed($viewer,$target,$request)){return cg_community_error();}
		$saved=$wpdb->query($wpdb->prepare("INSERT INTO $table (BlockerID,BlockedID,CreatedAt) VALUES (%d,%d,%d) ON DUPLICATE KEY UPDATE BlockerID=VALUES(BlockerID)",$viewer,$target,time()));
		$friend=cg_community_pair_row('friends',$viewer,$target);
		if($friend && $wpdb->update(cg_community_table('friends'),array('Status'=>'removed','UpdatedAt'=>time()),array('id'=>$friend->id))===false){return cg_community_error('Please try again.',500);}
	}
	if($saved===false){return cg_community_error('Please try again.',500);}
	return array('relationship'=>cg_community_relationship($viewer,$target));
}
}
