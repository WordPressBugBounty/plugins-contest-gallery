<?php
if(!defined('ABSPATH')){exit;}

function cg_community_member_url($gid,$args){
	$gallery=cg_community_gallery($gid);
	if(!$gallery){
		global $wpdb;
		$ids=$wpdb->get_col("SELECT id FROM {$wpdb->prefix}contest_gal1ery_options WHERE Version>=24 ORDER BY id DESC");
		foreach($ids as $id){$gallery=cg_community_gallery($id);if($gallery){break;}}
	}
	return $gallery ? add_query_arg($args,$gallery['url']) : '';
}
function cg_community_message_preview($body){
	$text=trim(preg_replace('/\s+/u',' ',wp_check_invalid_utf8($body)));
	return mb_strlen($text,'UTF-8')>200 ? rtrim(mb_substr($text,0,199,'UTF-8')).'…' : $text;
}
function cg_community_notify($event){
	$settings=cg_community_settings();$type=$event['type'];
	if(empty($settings['enabled']) || empty($settings[$type]['enabled']) || cg_community_blocked($event['sender'],$event['recipient'])){return;}
	$recipient=cg_community_user($event['recipient']);$sender=cg_community_user($event['sender']);
	if(!$recipient || !$sender || !is_email($recipient->user_email)){return;}
	if($type==='friend'){
		$friend=cg_community_pair_row('friends',$event['sender'],$event['recipient']);
		if(!$friend || $friend->Status!=='pending' || (int)$friend->RequesterID!==$event['sender'] || (int)$friend->LastRequestedAt!==$event['event']){return;}
	}else{
		$thread=cg_community_thread($event['recipient'],$event['conversation']);
		$read=$thread && (int)$thread->UserLow===$event['recipient'] ? 'LowReadId' : 'HighReadId';
		if(!$thread || (int)$thread->$read>=$event['event']){return;}
	}
	$args=array('cg_member_tab'=>$type==='message' ? 'messages' : 'friends');
	if($type==='message'){$args['cg_member_conversation']=$event['conversation'];}
	$url=cg_community_member_url($event['gallery'],$args);if(!$url){return;}
	$template=$settings[$type];
	$values=array('$recipient$'=>$recipient->display_name,'$sender$'=>$sender->display_name,'$site$'=>get_bloginfo('name'),'$url$'=>$url);
	$subject=str_replace(array("\r","\n"),'',wp_strip_all_tags(strtr($template['subject'],$values)));
	$htmlValues=array_map('esc_html',$values);$htmlValues['$url$']=esc_url($url);
	$content=wp_kses_post($template['content']);
	$withPreview=$type==='message' && strpos($content,'$message_preview$')!==false;
	if($withPreview){
		global $wpdb;$messages=cg_community_table('messages');
		$message=$wpdb->get_row($wpdb->prepare("SELECT Body FROM $messages WHERE id=%d AND ConversationID=%d AND SenderID=%d AND RecipientID=%d",$event['event'],$event['conversation'],$event['sender'],$event['recipient']));
		if(!$message){return;}
		// Chat text may contain literal entities. Encode them too, so the preview stays plain text.
		$htmlValues['$message_preview$']=htmlspecialchars(cg_community_message_preview($message->Body),ENT_QUOTES,'UTF-8',true);
	}
	$body=strtr($content,$htmlValues);
	if($withPreview){$body=wp_kses_post($body);}
	$mail=cg_add_mail_from_reply_headers(array('Content-Type: text/html; charset=UTF-8'),$template['name'],$template['from'],$template['reply']);
	global $cgMailAction,$cgMailGalleryId;
	$oldAction=$cgMailAction;$oldGallery=$cgMailGalleryId;
	$cgMailAction=$type==='message' ? 'Member message notification' : 'Member friend request notification';$cgMailGalleryId=$event['gallery'];
	$hadHook=has_action('wp_mail_failed','cg_on_wp_mail_error');
	if($hadHook===false){add_action('wp_mail_failed','cg_on_wp_mail_error',10,1);}
	try{wp_mail($recipient->user_email,$subject,$body,$mail['headers']);}
	catch(Exception $error){if(function_exists('cg_on_wp_mail_error')){cg_on_wp_mail_error(new WP_Error('wp_mail_failed','Member notification could not be sent.'));}}
	finally{
		if($hadHook===false){remove_action('wp_mail_failed','cg_on_wp_mail_error',10);}
		$cgMailAction=$oldAction;$cgMailGalleryId=$oldGallery;
	}
}
