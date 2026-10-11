<?php
if(!defined('ABSPATH')){exit;}

function cg_community_thread($viewer,$id){
	global $wpdb;$table=cg_community_table('threads');
	return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d AND (UserLow=%d OR UserHigh=%d)",$id,$viewer,$viewer));
}
function cg_community_other($thread,$viewer){return (int)$thread->UserLow===$viewer ? (int)$thread->UserHigh : (int)$thread->UserLow;}
// This proves that the contact was offered by an authorized public profile render.
// It carries no login authority and survives sign-in. Every use still requires the
// current account's nonce and checks the entry's current visibility again.
function cg_community_contact_proof($target,$gid,$shortcode,$entry){
	global $wpdb;
	$payload=base64_encode(wp_json_encode(array('user'=>absint($target),'gallery'=>absint($gid),'shortcode'=>$shortcode,'entry'=>absint($entry),'expires'=>time()+2*HOUR_IN_SECONDS,'site'=>$wpdb->prefix)));
	return $payload.'.'.hash_hmac('sha256','cg-community-contact|'.$payload,wp_salt('auth'));
}
function cg_community_verify_contact_proof($target,$request){
	global $wpdb;
	$parts=explode('.',isset($request['source']) ? $request['source'] : '');
	if(count($parts)!==2 || !cg1l_hash_equals(hash_hmac('sha256','cg-community-contact|'.$parts[0],wp_salt('auth')),$parts[1])){return false;}
	$data=json_decode(base64_decode($parts[0],true),true);
	return is_array($data) && isset($data['expires'],$data['site'],$data['user'],$data['gallery'],$data['entry'],$data['shortcode']) && $data['expires']>=time() && $data['site']===$wpdb->prefix && (int)$data['user']===$target && (int)$data['gallery']===$request['gallery'] && (int)$data['entry']===$request['entry'] && $data['shortcode']===$request['shortcode'];
}
function cg_community_contact_allowed($viewer,$target,$request){
	if(!$target || $viewer===$target || !cg_community_user($target)){return false;}
	if(cg_community_pair_row('threads',$viewer,$target)){return true;}
	$friend=cg_community_pair_row('friends',$viewer,$target);
	// Both sides of a pending request offer Contact in the member area.
	if($friend && in_array($friend->Status,array('accepted','pending'),true)){return true;}
	global $wpdb;
	if(cg_public_member_profile_friend_contact_allowed($viewer,$target,$request)){return true;}
	if(empty($request['entry']) || empty($request['gallery']) || !cg_community_verify_contact_proof($target,$request)){return false;}
	$entry=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_gal1ery WHERE id=%d AND GalleryID=%d AND WpUserId=%d",$request['entry'],$request['gallery'],$target));
	if(!$entry || (int)$entry->Active!==1 || !empty($entry->IsProfileImage) || !cg_participants_context(array('realGid'=>$request['gallery'],'shortcodeName'=>$request['shortcode']))){return false;}
	$upload=wp_upload_dir();$base=$upload['basedir'].'/contest-gallery/gallery-id-'.$request['gallery'].'/json/'.$request['gallery'];
	if(!is_readable($base.'-categories.json') || !is_readable($base.'-form-upload.json')){return false;}
	$categories=json_decode(file_get_contents($base.'-categories.json'),true);$form=json_decode(file_get_contents($base.'-form-upload.json'),true);
	return is_array($categories) && is_array($form) && cg1l_frontend_is_category_allowed_for_entry((array)$entry,$categories,cg_member_options($request['gallery'],$request['shortcode']),$form);
}
function cg_community_contact($viewer,$request){
	$thread=!empty($request['conversation']) ? cg_community_thread($viewer,$request['conversation']) : false;
	if(!empty($request['conversation']) && !$thread){return cg_community_error();}
	$target=$thread ? cg_community_other($thread,$viewer) : $request['target'];
	if(!cg_community_contact_allowed($viewer,$target,$request)){return cg_community_error('This member is no longer available.');}
	if(!$thread){$thread=cg_community_pair_row('threads',$viewer,$target);}
	$relationship=cg_community_relationship($viewer,$target);
	return array('person'=>cg_community_person($target),'relationship'=>$relationship,'conversation'=>$thread ? (int)$thread->id : 0,
		'profileFields'=>!empty($relationship['blocked']) ? '' : cg_public_profile_render_fields($target),
		'profile'=>cg_public_member_profile_contact_source($viewer,$target,$request['gallery'],$request['shortcode'],$thread ? (int)$thread->id : 0));
}
function cg_community_message_date($timestamp){
	return wp_date(get_option('date_format').' '.get_option('time_format'),(int)$timestamp);
}
function cg_community_threads($viewer,$request){
	global $wpdb;$table=cg_community_table('threads');$messages=cg_community_table('messages');
	$offset=(max(1,$request['page'])-1)*20;
	$rows=$wpdb->get_results($wpdb->prepare("SELECT t.*,m.Body FROM $table t INNER JOIN $messages m ON m.id=t.LastMessageId WHERE t.UserLow=%d OR t.UserHigh=%d ORDER BY t.UpdatedAt DESC,t.id DESC LIMIT 21 OFFSET %d",$viewer,$viewer,$offset));
	$more=count($rows)>20;$rows=array_slice($rows,0,20);$items=array();
	foreach($rows as $row){
		$read=(int)$row->UserLow===$viewer ? $row->LowReadId : $row->HighReadId;
		$unread=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $messages WHERE ConversationID=%d AND RecipientID=%d AND id>%d",$row->id,$viewer,$read));
		$items[]=array('id'=>(int)$row->id,'person'=>cg_community_person(cg_community_other($row,$viewer)),'preview'=>mb_substr($row->Body,0,160),'time'=>(int)$row->UpdatedAt,'dateText'=>cg_community_message_date($row->UpdatedAt),'unread'=>$unread);
	}
	return array('items'=>$items,'more'=>$more);
}
function cg_community_messages($viewer,$request){
	global $wpdb;$thread=cg_community_thread($viewer,$request['conversation']);
	if(!$thread){return cg_community_error();}
	$table=cg_community_table('messages');$where=$wpdb->prepare('ConversationID=%d',$thread->id);
	if($request['after']){$where.=$wpdb->prepare(' AND id>%d',$request['after']);}
	elseif($request['before']){$where.=$wpdb->prepare(' AND id<%d',$request['before']);}
	$rows=$wpdb->get_results("SELECT id,SenderID,Body,CreatedAt FROM $table WHERE $where ORDER BY id ".($request['after'] ? 'ASC' : 'DESC').' LIMIT 21');
	$more=count($rows)>20;$rows=array_slice($rows,0,20);
	if(!$request['after']){$rows=array_reverse($rows);}
	$items=array();
	foreach($rows as $row){$items[]=array('id'=>(int)$row->id,'mine'=>(int)$row->SenderID===$viewer,'body'=>$row->Body,'time'=>(int)$row->CreatedAt,'dateText'=>cg_community_message_date($row->CreatedAt));}
	return array('items'=>$items,'more'=>$more,'relationship'=>cg_community_relationship($viewer,cg_community_other($thread,$viewer)));
}
function cg_community_send($viewer,$request){
	global $wpdb;
	$body=trim(wp_check_invalid_utf8($request['body']));
	if($body==='' || mb_strlen($body)>5000 || strpos($body,"\0")!==false){return cg_community_error('Enter a message of 1 to 5,000 characters.',400);}
	if(!preg_match('/^[a-zA-Z0-9-]{16,64}$/D',$request['key'])){return cg_community_error('Please reopen the conversation and try again.',400);}
	$contact=cg_community_contact($viewer,$request);if(is_wp_error($contact)){return $contact;}
	$target=$contact['person']['id'];
	if($contact['relationship']['blocked']){return cg_community_error('Messaging is unavailable for this contact.');}
	$messages=cg_community_table('messages');$threads=cg_community_table('threads');
	$existing=$wpdb->get_row($wpdb->prepare("SELECT id,ConversationID,RecipientID,Body FROM $messages WHERE SenderID=%d AND RequestKey=%s",$viewer,$request['key']));
	if($existing){
		if((int)$existing->RecipientID!==$target || $existing->Body!==$body){return cg_community_error('This request has already been used.',409);}
		return array('conversation'=>(int)$existing->ConversationID,'id'=>(int)$existing->id);
	}
	if(!cg_security_rate_take_locked('community-minute',$viewer,10,60) || !cg_security_rate_take_locked('community-hour',$viewer,100,HOUR_IN_SECONDS)){
		return cg_community_error('Please wait before sending more messages.',429);
	}
	$thread=cg_community_pair_row('threads',$viewer,$target);$now=time();
	if(!$thread){
		$pair=cg_community_pair($viewer,$target);
		if(!$wpdb->insert($threads,array('UserLow'=>$pair[0],'UserHigh'=>$pair[1],'GalleryID'=>$request['gallery'],'CreatedAt'=>$now,'UpdatedAt'=>$now))){return cg_community_error('The message could not be saved. Please try again.',500);}
		$thread=cg_community_thread($viewer,(int)$wpdb->insert_id);
	}
	if(!$wpdb->insert($messages,array('ConversationID'=>$thread->id,'SenderID'=>$viewer,'RecipientID'=>$target,'Body'=>$body,'CreatedAt'=>$now,'RequestKey'=>$request['key']))){return cg_community_error('The message could not be saved. Please try again.',500);}
	$id=(int)$wpdb->insert_id;
	$notified=(int)$thread->UserLow===$target ? 'LowNotified' : 'HighNotified';
	$changes=array('LastMessageId'=>$id,'UpdatedAt'=>$now,$notified=>1);
	if($wpdb->update($threads,$changes,array('id'=>$thread->id))===false){return cg_community_error('The message could not be saved. Please try again.',500);}
	$result=array('conversation'=>(int)$thread->id,'id'=>$id);
	// Claim even while mail is disabled, so enabling it never replays historical unread episodes.
	if(empty($thread->$notified)){$result['_mail']=array('type'=>'message','sender'=>$viewer,'recipient'=>$target,'gallery'=>(int)$thread->GalleryID,'conversation'=>(int)$thread->id,'event'=>$id);}
	return $result;
}
function cg_community_read($viewer,$request){
	global $wpdb;$thread=cg_community_thread($viewer,$request['conversation']);
	if(!$thread){return cg_community_error();}
	$messages=cg_community_table('messages');$threads=cg_community_table('threads');$through=$request['after'];
	if(!$through || !$wpdb->get_var($wpdb->prepare("SELECT id FROM $messages WHERE id=%d AND ConversationID=%d",$through,$thread->id))){return cg_community_error();}
	$field=(int)$thread->UserLow===$viewer ? 'LowReadId' : 'HighReadId';$notified=(int)$thread->UserLow===$viewer ? 'LowNotified' : 'HighNotified';
	$read=max((int)$thread->$field,$through);
	$remaining=$wpdb->get_var($wpdb->prepare("SELECT id FROM $messages WHERE ConversationID=%d AND RecipientID=%d AND id>%d LIMIT 1",$thread->id,$viewer,$read));
	$changes=array($field=>$read);if(!$remaining){$changes[$notified]=0;}
	if($wpdb->update($threads,$changes,array('id'=>$thread->id))===false){return cg_community_error('Please try again.',500);}
	return array('read'=>$read);
}
