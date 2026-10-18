<?php
if(!defined('ABSPATH')){exit;}
include_once(__DIR__.'/cg-participant-mail-storage.php');
include_once(__DIR__.'/cg-participant-mail-entries.php');
include_once(__DIR__.'/cg-participant-mail-composer.php');

if(!function_exists('cg_participant_mail_is_pro')){
function cg_participant_mail_is_pro(){return function_exists('contest_gal1ery_key_check') && contest_gal1ery_key_check();}
}

if(!function_exists('cg_participant_mail_group')){
function cg_participant_mail_group($entries){
	$recipients = array();$missing = 0;
	foreach($entries as $entry){
		$email = trim($entry['email']);
		if(!is_email($email) || strlen($email)>254){$missing++;continue;}
		$key = strtolower($email);
		if(!isset($recipients[$key])){
			$recipients[$key] = array('email'=>$email,'user_id'=>$entry['user_id'],'first_name'=>$entry['first_name'],
				'last_name'=>$entry['last_name'],'entries'=>array(),'status'=>'pending','attempts'=>0);
		}elseif($recipients[$key]['user_id']!==$entry['user_id']){
			$recipients[$key]['user_id']=0;$recipients[$key]['first_name']='';$recipients[$key]['last_name']='';
		}
		$details=array('id'=>$entry['id'],'label'=>$entry['label'],'url'=>$entry['url']);
		foreach(array('file_type','thumbnail_url','thumbnail_width','thumbnail_height') as $field){if(isset($entry[$field])){$details[$field]=$entry[$field];}}
		$recipients[$key]['entries'][]=$details;
	}
	return array('recipients'=>array_values($recipients),'entry_count'=>count($entries),'missing_count'=>$missing);
}
}

if(!function_exists('cg_participant_mail_filters')){
function cg_participant_mail_filters($values,$galleryId){
	$filters=cg_backend_gallery_filters($values);$filters['category']='';$filters['recipient_keys']=array();
	if(isset($values['cg_pmail_category']) && $values['cg_pmail_category']!==''){
		$category=$values['cg_pmail_category'];
		if(!is_scalar($category) || !preg_match('/^[0-9]+$/D',(string)$category)){return new WP_Error('cg_pmail_category','Choose a valid category.');}
		$filters['category']=(string)absint($category);
		if($filters['category']!=='0'){
			global $wpdb;
			$exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_categories WHERE GalleryID=%d AND id=%d",$galleryId,$category));
			if($wpdb->last_error){return new WP_Error('cg_pmail_category','The category could not be loaded.');}
			if(!$exists){return new WP_Error('cg_pmail_category','This category is no longer available. Choose another category or reset the filters.');}
		}
	}
	if(isset($values['cg_pmail_recipients'])){
		$keys=is_string($values['cg_pmail_recipients']) ? json_decode($values['cg_pmail_recipients']) : null;
		if(!is_array($keys)){return new WP_Error('cg_pmail_recipients','Choose valid recipients or clear the individual selection.');}
		foreach($keys as $key){if(!is_string($key) || !preg_match('/^[a-f0-9]{32}$/D',$key)){return new WP_Error('cg_pmail_recipients','Choose valid recipients or clear the individual selection.');}}
		$filters['recipient_keys']=array_values(array_unique($keys));sort($filters['recipient_keys'],SORT_STRING);
	}
	return $filters;
}
}

if(!function_exists('cg_participant_mail_recipient_key')){
function cg_participant_mail_recipient_key($recipient){return md5(strtolower(trim($recipient['email'])));}
}

if(!function_exists('cg_participant_mail_choose')){
function cg_participant_mail_choose($selection,$filters){
	$keys=isset($filters['recipient_keys']) ? $filters['recipient_keys'] : array();
	if(!$keys){return $selection;}
	$remaining=array_fill_keys($keys,true);$recipients=array();$entryCount=0;
	foreach($selection['recipients'] as $recipient){
		$key=cg_participant_mail_recipient_key($recipient);
		if(isset($remaining[$key])){$recipients[]=$recipient;$entryCount+=count($recipient['entries']);unset($remaining[$key]);}
	}
	if($remaining){return new WP_Error('cg_pmail_recipients','Some selected recipients no longer match these filters. Clear the individual selection or choose recipients again.');}
	return array('recipients'=>$recipients,'entry_count'=>$entryCount,'missing_count'=>0);
}
}

if(!function_exists('cg_participant_mail_selection')){
function cg_participant_mail_selection($galleryId,$values){
	$filters=cg_participant_mail_filters($values,$galleryId);if(is_wp_error($filters)){return $filters;}
	$selection=cg_participant_mail_collect($galleryId,$filters,false,false);if(is_wp_error($selection)){return $selection;}
	$chosen=cg_participant_mail_choose($selection,$filters);if(is_wp_error($chosen)){return $chosen;}
	$signatureFilters=$filters;unset($signatureFilters['recipient_keys']);
	$signature=md5(serialize(array($signatureFilters,$selection['recipients'])));
	$count=count($selection['recipients']);
	$result=array('recipient_count'=>count($chosen['recipients']),'available_recipient_count'=>$count,'entry_count'=>$chosen['entry_count'],'missing_count'=>$chosen['missing_count'],'selection_signature'=>$signature);
	if(!empty($values['include_recipients'])){
		$offset=isset($values['offset']) ? (int)(floor(absint($values['offset'])/50)*50) : 0;
		$previous=isset($values['selection_signature']) && is_string($values['selection_signature']) ? $values['selection_signature'] : '';
		$result['restart']=$offset>0 && $previous!==$signature;
		if($result['restart']){$offset=0;}
		$result['recipients_html']=cg_participant_mail_recipient_options_html(array_slice($selection['recipients'],$offset,50),$filters['recipient_keys']);
		$result['recipient_offset']=min($count,$offset+50);
	}
	return $result;
}
}

if(!function_exists('cg_participant_mail_collect')){
function cg_participant_mail_collect($galleryId,$filters,$includeEntryUrls=true,$applyRecipientSelection=true){
	global $wpdb;
	$query = cg_backend_gallery_filter_query($galleryId,$filters);
	if(isset($filters['category']) && $filters['category']!==''){$query['filter_sql'].=' AND cgGallery.Category = '.absint($filters['category']).' ';}
	$sql = "SELECT cgGallery.id,cgGallery.WpUserId,cgGallery.WpPage,cgGallery.WpUpload,cgGallery.NamePic,cgGallery.ImgType,cgGallery.PdfPreview
		FROM {$wpdb->prefix}contest_gal1ery AS cgGallery WHERE cgGallery.GalleryID=%d
		AND COALESCE(cgGallery.IsProfileImage,0)=0 {$query['filter_sql']} {$query['search_sql']} ORDER BY cgGallery.id ASC";
	$rows = $wpdb->get_results($wpdb->prepare($sql,array_merge(array($galleryId),$query['search_parameters'])),ARRAY_A);
	if($wpdb->last_error){return new WP_Error('cg_pmail_selection','The participants could not be loaded.');}
	$emailField = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_f_input WHERE GalleryID=%d AND Field_Type='email-f' ORDER BY Field_Order ASC,id ASC LIMIT 1",$galleryId));
	if($wpdb->last_error){return new WP_Error('cg_pmail_selection','The email field could not be loaded.');}
	$emails = array();
	if($emailField){
		$mailRows = $wpdb->get_results($wpdb->prepare("SELECT pid,Short_Text FROM {$wpdb->prefix}contest_gal1ery_entries WHERE GalleryID=%d AND f_input_id=%d ORDER BY id ASC",$galleryId,$emailField),ARRAY_A);
		if($wpdb->last_error){return new WP_Error('cg_pmail_selection','The email addresses could not be loaded.');}
		foreach($mailRows as $row){$emails[$row['pid']]=wp_specialchars_decode(stripslashes($row['Short_Text']),ENT_QUOTES);}
	}
	$users = array();$entries = array();
	$labels=$includeEntryUrls ? cg_participant_mail_entry_labels($galleryId) : array();
	if(is_wp_error($labels)){return $labels;}
	foreach($rows as $row){
		$userId = absint($row['WpUserId']);
		if(!array_key_exists($userId,$users)){
			$user = $userId ? get_userdata($userId) : false;
			$users[$userId] = $user ? array('email'=>$user->user_email,'id'=>$userId,
				'first_name'=>(string)get_user_meta($userId,'first_name',true),'last_name'=>(string)get_user_meta($userId,'last_name',true)) : false;
		}
		$user = $users[$userId];
		$pageId = absint($row['WpPage']);
		$url = $includeEntryUrls && $pageId && get_post_status($pageId)==='publish' ? get_permalink($pageId) : '';
		$entry = array('id'=>absint($row['id']),'email'=>$user ? $user['email'] : (isset($emails[$row['id']]) ? $emails[$row['id']] : ''),
			'user_id'=>$user ? $user['id'] : 0,'first_name'=>$user ? $user['first_name'] : '', 'last_name'=>$user ? $user['last_name'] : '',
			'label'=>'Entry #'.absint($row['id']),'url'=>$url ? esc_url_raw($url) : '');
		$entries[]=$includeEntryUrls ? array_merge($entry,cg_participant_mail_entry_presentation($row,$labels)) : $entry;
	}
	$selection=cg_participant_mail_group($entries);
	return $applyRecipientSelection ? cg_participant_mail_choose($selection,$filters) : $selection;
}
}

if(!function_exists('cg_participant_mail_placeholders_available')){
function cg_participant_mail_placeholders_available($message){
	return !preg_match('/\$(?:wp_first_name|wp_last_name|cg_entries_count|cg_entry_count|cg_entry_list)\$/i',$message['subject'].' '.$message['body']) || cg_participant_mail_is_pro();
}
}

if(!function_exists('cg_participant_mail_message')){
function cg_participant_mail_message($values){
	$message = array();
	foreach(array('subject','body','from_name','from_email','reply_name','reply_email') as $field){
		$value = isset($values[$field]) && is_string($values[$field]) ? wp_unslash($values[$field]) : '';
		$message[$field] = $field==='body' ? wp_kses_post($value) : sanitize_text_field($value);
	}
	if($message['subject']===''){return new WP_Error('cg_pmail_subject','Enter a subject.');}
	if(trim(wp_strip_all_tags(str_replace(array('&nbsp;',"\xc2\xa0"),' ',$message['body'])))==='' && !preg_match('/<img\b/i',$message['body'])){
		return new WP_Error('cg_pmail_body','Enter your message.');
	}
	if(!$message['from_name'] || strlen($message['from_name'])>256 || !is_email($message['from_email'])){
		return new WP_Error('cg_pmail_from','Enter a sender name and a valid sender email address.');
	}
	if($message['reply_email'] && !is_email($message['reply_email'])){return new WP_Error('cg_pmail_reply','Enter a valid reply email address.');}
	$allowed = array('$wp_first_name$','$wp_last_name$','$cg_entries_count$','$cg_entry_count$','$cg_entry_list$');
	foreach(array('subject','body') as $field){
		if(preg_match_all('/\$(?:id_[0-9]+|[a-z_][a-z0-9_]*)\$?/i',$message[$field],$matches)){
			foreach($matches[0] as $token){
				if(!in_array(strtolower($token),$allowed,true) || ($field==='subject' && strtolower($token)==='$cg_entry_list$')){
					return new WP_Error('cg_pmail_placeholder','This placeholder cannot be used here: '.$token.'. Use participant placeholders for this message.');
				}
			}
		}
	}
	if(!cg_participant_mail_placeholders_available($message)){return new WP_Error('cg_pmail_placeholders_pro','Personalized placeholders require Contest Gallery PRO. Remove the placeholders to send this message.');}
	return $message;
}
}

if(!function_exists('cg_participant_mail_resolve')){
function cg_participant_mail_resolve($message,$recipient){
	$list = cg_participant_mail_entries_html($recipient['entries']);
	$tokens = array('$wp_first_name$','$wp_last_name$','$cg_entries_count$','$cg_entry_count$','$cg_entry_list$');
	$plain = array($recipient['first_name'],$recipient['last_name'],(string)count($recipient['entries']),(string)count($recipient['entries']),'');
	$html = array(esc_html($recipient['first_name']),esc_html($recipient['last_name']),(string)count($recipient['entries']),(string)count($recipient['entries']),$list);
	$plainMap = array_combine($tokens,$plain);$htmlMap = array_combine($tokens,$html);
	$pattern = '/\$(?:wp_first_name|wp_last_name|cg_entries_count|cg_entry_count|cg_entry_list)\$/i';
	$subject = preg_replace_callback($pattern,function($match)use($plainMap){return $plainMap[strtolower($match[0])];},$message['subject']);
	$bodyTemplate = preg_replace('/<p\b[^>]*>\s*\$cg_entry_list\$\s*<\/p>/i','$cg_entry_list$',$message['body']);
	$body = preg_replace_callback($pattern,function($match)use($htmlMap){return $htmlMap[strtolower($match[0])];},$bodyTemplate);
	// Profile values may occur in attributes. Re-check HTML protocols after substitution.
	$entryStyles=preg_match('/\$cg_entry_list\$/i',$bodyTemplate) && has_filter('safe_style_css','cg_participant_mail_entry_styles')===false;
	if($entryStyles){add_filter('safe_style_css','cg_participant_mail_entry_styles');}
	try{$body=wp_kses_post($body);}
	finally{if($entryStyles){remove_filter('safe_style_css','cg_participant_mail_entry_styles');}}
	return array('subject'=>sanitize_text_field($subject),'body'=>$body);
}
}

if(!function_exists('cg_participant_mail_send')){
function cg_participant_mail_send($message,$recipient,$test=false,$testEmail=''){
	if(!cg_participant_mail_placeholders_available($message)){return false;}
	$email=$test ? ($testEmail!=='' ? $testEmail : wp_get_current_user()->user_email) : $recipient['email'];
	if(!is_email($email)){return false;}
	$resolved = cg_participant_mail_resolve($message,$recipient);
	$headers = cg_add_mail_from_reply_headers(array('Content-Type: text/html; charset=UTF-8'),$message['from_name'],$message['from_email'],$message['reply_email'],$message['reply_name']);
	return wp_mail($email,($test ? '[Test] ' : '').$resolved['subject'],$resolved['body'],$headers['headers']);
}
}

if(!function_exists('cg_participant_mail_test_recipient')){
function cg_participant_mail_test_recipient($job,$choice){
	if($job['status']!=='draft'){return new WP_Error('cg_pmail_test','Test emails are only available before sending starts.');}
	if(!is_string($choice) || ($choice!=='self' && (!preg_match('/^(?:0|[1-9][0-9]*)$/D',$choice) || strlen($choice)>strlen((string)$job['recipient_count']) || (int)$choice>=$job['recipient_count']))){
		return new WP_Error('cg_pmail_test_recipient','Choose a test recipient from the saved recipient list.');
	}
	$position=$choice==='self' ? 0 : (int)$choice;
	$number=(int)floor($position/50);$chunk=cg_participant_mail_read(cg_participant_mail_chunk_key($job['id'],$number));
	if(!is_array($chunk) || !isset($chunk[$position%50]) || !is_email($chunk[$position%50]['email'])){
		return new WP_Error('cg_pmail_storage','The saved recipient list is incomplete. Create a new preview.');
	}
	$recipient=$chunk[$position%50];
	return array('recipient'=>$recipient,'email'=>$choice==='self' ? wp_get_current_user()->user_email : $recipient['email'],'choice'=>$choice);
}
}

if(!function_exists('cg_participant_mail_test_preview')){
function cg_participant_mail_test_preview($job,$choice){
	$target=cg_participant_mail_test_recipient($job,$choice);if(is_wp_error($target)){return $target;}
	if(!cg_participant_mail_placeholders_available($job['message'])){return new WP_Error('cg_pmail_placeholders_pro','Personalized placeholders require Contest Gallery PRO. Create a message without placeholders to send it.');}
	return array('test_recipient'=>$target['choice'],'test_email'=>$target['email'],'test_available'=>(bool)is_email($target['email']),
		'test_hint'=>$choice==='self' ? 'The test goes to your account. The example uses participant data from '.$target['recipient']['email'].'.' : 'The test will be sent to this participant using their saved data.',
		'preview_html'=>cg_participant_mail_preview_html($job['message'],$target['recipient']));
}
}

if(!function_exists('cg_participant_mail_test_options')){
function cg_participant_mail_test_options($job){
	$html=cg_participant_mail_test_account_option_html().'<optgroup label="Participants">';$count=0;
	for($number=0;$number<$job['chunks'];$number++){
		$chunk=cg_participant_mail_read(cg_participant_mail_chunk_key($job['id'],$number));
		if(!is_array($chunk) || count($chunk)!==min(50,$job['recipient_count']-$count)){
			return new WP_Error('cg_pmail_storage','The saved recipient list is incomplete. Create a new preview.');
		}
		$html.=cg_participant_mail_test_options_html($chunk,$count);$count+=count($chunk);
	}
	if($count!==$job['recipient_count']){return new WP_Error('cg_pmail_storage','The saved recipient list is incomplete. Create a new preview.');}
	return $html.'</optgroup>';
}
}

if(!function_exists('cg_participant_mail_create')){
function cg_participant_mail_create($galleryId,$scope,$filters,$selection,$message,$composerRevision=0){
	if(!cg_participant_mail_lock()){return new WP_Error('cg_pmail_busy','Mail processing is busy. Try again shortly.');}
	try{
		$id = md5(wp_generate_password(64,false,false));
		$chunks = array_chunk($selection['recipients'],50);
		$job = array('id'=>$id,'gallery_id'=>$galleryId,'created_by'=>get_current_user_id(),'created_at'=>time(),'updated_at'=>time(),
			'status'=>'draft','scope'=>$scope,'filters'=>$filters,'entry_count'=>$selection['entry_count'],'missing_count'=>$selection['missing_count'],
			'recipient_count'=>count($selection['recipients']),'chunks'=>count($chunks),'message'=>$message,'composer_revision'=>$composerRevision);
		foreach($chunks as $number=>$chunk){
			if(!cg_participant_mail_store(cg_participant_mail_chunk_key($id,$number),$chunk)){cg_participant_mail_delete_job($job);return new WP_Error('cg_pmail_storage','The recipients could not be saved.');}
		}
		$index = cg_participant_mail_read('cg_pmail_index',array());$index[$id]=$galleryId;
		if(!cg_participant_mail_save_job($job) || !cg_participant_mail_store('cg_pmail_index',$index)){
			cg_participant_mail_delete_job($job);return new WP_Error('cg_pmail_storage','The message could not be saved.');
		}
		if(!wp_next_scheduled('cg_participant_mail_cleanup')){wp_schedule_event(time()+DAY_IN_SECONDS,'daily','cg_participant_mail_cleanup');}
		return $job;
	}finally{cg_participant_mail_unlock();}
}
}

if(!function_exists('cg_participant_mail_job_response')){
function cg_participant_mail_job_response($job){
	$counts = cg_participant_mail_counts($job);
	if($counts===false){return new WP_Error('cg_pmail_storage','The saved recipient list is incomplete.');}
	$stopped = cg_participant_mail_stopped($job['id']);
	return array('id'=>$job['id'],'gallery_id'=>$job['gallery_id'],'status'=>$stopped && $job['status']==='running' ? 'stopping' : $job['status'],
		'recipient_count'=>$job['recipient_count'],'entry_count'=>$job['entry_count'],'missing_count'=>$job['missing_count'],'counts'=>$counts,
		'scope'=>$job['scope'],'filters'=>array_merge(array('category'=>'','recipient_keys'=>array()),$job['filters']),'message'=>$job['message']);
}
}

if(!function_exists('cg_participant_mail_latest')){
function cg_participant_mail_latest($galleryId){
	$latest = false;
	$index = cg_participant_mail_read('cg_pmail_index',array());
	foreach($index as $id=>$gid){
		if((int)$gid!==$galleryId){continue;}
		$job = cg_participant_mail_job($id);
		if(!$job){continue;}
		if($job['status']==='running'){return $job;}
		if(!$latest || $job['created_at']>=$latest['created_at']){$latest=$job;}
	}
	$history=cg_participant_mail_read(cg_participant_mail_history_key($galleryId),array());$summary=false;
	foreach($history as $item){if(!$summary || $item['created_at']>$summary['created_at']){$summary=$item;}}
	if($summary && (!$latest || $summary['created_at']>$latest['created_at'])){$archived=cg_participant_mail_job($summary['id']);if($archived){$latest=$archived;}}
	return $latest;
}
}

if(!function_exists('cg_participant_mail_transition')){
function cg_participant_mail_transition($id,$retry=false){
	if(!cg_participant_mail_lock()){return new WP_Error('cg_pmail_busy','Mail processing is busy. Try again shortly.');}
	try{
		$job = cg_participant_mail_job($id);
		if(!$job){return new WP_Error('cg_pmail_missing','This message no longer exists.');}
		if(!cg_participant_mail_placeholders_available($job['message'])){return new WP_Error('cg_pmail_placeholders_pro','Personalized placeholders require Contest Gallery PRO. Create a message without placeholders to send it.');}
		if($job['status']==='running'){cg_participant_mail_schedule(time());return $job;}
		if(!$retry && $job['status']!=='draft'){return new WP_Error('cg_pmail_started','This message has already been started.');}
		if($retry){
			if(!in_array($job['status'],array('complete','cancelled'),true)){return new WP_Error('cg_pmail_retry','This message cannot be retried.');}
			$counts = cg_participant_mail_counts($job);
			if(!$counts || !$counts['failed']){return new WP_Error('cg_pmail_retry','There are no failed recipients to retry.');}
			// Persist running and its continuation first. A partial retry reset remains resumable.
			delete_option('cg_pmail_stop_'.$id);
			$job['status']='running';
			if(!cg_participant_mail_save_job($job)){return new WP_Error('cg_pmail_storage','The retry could not be started.');}
			cg_participant_mail_schedule(time());
			$count = 0;
			for($i=0;$i<$job['chunks'];$i++){
				$key = cg_participant_mail_chunk_key($id,$i);$chunk = cg_participant_mail_read($key);
				if(!is_array($chunk)){return new WP_Error('cg_pmail_storage','The saved recipient list is incomplete.');}
				foreach($chunk as &$recipient){if($recipient['status']==='failed'){$recipient['status']='pending';$count++;}}
				unset($recipient);
				if(!cg_participant_mail_store($key,$chunk)){return new WP_Error('cg_pmail_storage','The retry could not be saved.');}
			}
			if(!$count){return new WP_Error('cg_pmail_retry','There are no failed recipients to retry.');}
		}
		$job['status']='running';
		if(!cg_participant_mail_save_job($job)){return new WP_Error('cg_pmail_storage','The message could not be started.');}
		cg_participant_mail_schedule(time());return $job;
	}finally{cg_participant_mail_unlock();}
}
}

if(!function_exists('cg_participant_mail_worker')){
function cg_participant_mail_worker(){
	if(!cg_participant_mail_lock()){return;}
	try{cg_participant_mail_worker_locked();}finally{cg_participant_mail_unlock();}
}
}

if(!function_exists('cg_participant_mail_worker_locked')){
function cg_participant_mail_worker_locked(){
	global $wpdb;
	if(!cg_participant_mail_history_bootstrap()){return;}
	$started = microtime(true);
	$rate = cg_participant_mail_read('cg_pmail_rate',array('start'=>time(),'used'=>0));
	if($rate['start']<=time()-60){$rate=array('start'=>time(),'used'=>0);}
	// Reserve a continuation before any transport call. A fatal/timeout still leaves work discoverable.
	cg_participant_mail_schedule(max(time()+60,$rate['start']+60));
	$remaining = false;
	foreach(cg_participant_mail_read('cg_pmail_index',array()) as $id=>$galleryId){
		$job = cg_participant_mail_job($id);
		if(!$job || $job['status']!=='running'){continue;}
		if(!cg_participant_mail_placeholders_available($job['message'])){$job['status']='error';cg_participant_mail_save_job($job);continue;}
		$galleryExists = cg_participant_mail_gallery_exists($job['gallery_id']);
		for($i=0;$i<$job['chunks'];$i++){
			$key = cg_participant_mail_chunk_key($id,$i);$chunk = cg_participant_mail_read($key);
			if(!is_array($chunk)){$job['status']='error';cg_participant_mail_save_job($job);break;}
			if(!$galleryExists || cg_participant_mail_stopped($id)){
				foreach($chunk as &$cancelledRecipient){
					if($cancelledRecipient['status']==='pending'){$cancelledRecipient['status']='cancelled';}
					elseif($cancelledRecipient['status']==='sending'){$cancelledRecipient['status']='unknown';}
				}
				unset($cancelledRecipient);
				if(!cg_participant_mail_store($key,$chunk)){return;}continue;
			}
			foreach($chunk as $offset=>$recipient){
				if(!cg_participant_mail_has_lock()){return;}
				if($recipient['status']==='sending'){
					// The previous worker died or lost its connection. Its outcome cannot be inferred.
					$chunk[$offset]['status']='unknown';
					if(!cg_participant_mail_store($key,$chunk)){return;}
					continue;
				}
				if($recipient['status']!=='pending'){continue;}
				if($recipient['user_id']){
					// A delete request may be waiting for this worker's lock. Do not send to that account.
					$exists=$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->users} WHERE ID=%d",$recipient['user_id']));
					if($wpdb->last_error){return;}
					if(!$exists){cg_participant_mail_history_erase_recipient($chunk[$offset]);if(!cg_participant_mail_store($key,$chunk)){return;}continue;}
				}
				if(!$galleryExists || cg_participant_mail_stopped($id)){
					$chunk[$offset]['status']='cancelled';if(!cg_participant_mail_store($key,$chunk)){return;}continue;
				}
				if($rate['used']>=10 || microtime(true)-$started>20){$remaining=true;continue;}
				$rate['used']++;
				if(!cg_participant_mail_store('cg_pmail_rate',$rate)){return;}
				$chunk[$offset]['status']='sending';$chunk[$offset]['attempts']++;
				if(!cg_participant_mail_store($key,$chunk)){return;}
				try{
					$accepted = cg_participant_mail_send($job['message'],$recipient);
					$chunk[$offset]['status']=$accepted ? 'accepted' : 'failed';
				}catch(Exception $exception){$chunk[$offset]['status']='unknown';}
				$chunk[$offset]['attempted_at']=time();
				if(!cg_participant_mail_store($key,$chunk)){return;}
				if($chunk[$offset]['status']==='accepted'){
					$resolved=cg_participant_mail_resolve($job['message'],$recipient);$message=$job['message'];
					$mailId=cg_save_sent_mail($job['gallery_id'],0,$recipient['user_id'],$recipient['email'],$message['reply_name'],$message['reply_email'],
						$message['from_name'],$message['from_email'],'','',$resolved['subject'],$resolved['body'],'participants-campaign');
					if($mailId){$chunk[$offset]['mail_id']=$mailId;if(!cg_participant_mail_store($key,$chunk)){return;}}
				}
			}
		}
		if($job['status']==='error'){continue;}
		$counts = cg_participant_mail_counts($job);
		if($counts===false){$job['status']='error';}
		elseif(!$counts['pending'] && !$counts['sending']){$job['status']=cg_participant_mail_stopped($id) || !$galleryExists ? 'cancelled' : 'complete';}
		else{$remaining=true;}
		if(!cg_participant_mail_save_job($job)){return;}
	}
	if(!$remaining && cg_participant_mail_has_lock()){wp_clear_scheduled_hook('cg_participant_mail_worker');}
}
}

if(!function_exists('cg_participant_mail_gallery_exists')){
function cg_participant_mail_gallery_exists($galleryId){
	global $wpdb;
	return (bool)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d",$galleryId));
}
}

if(!function_exists('cg_participant_mail_ajax')){
function cg_participant_mail_ajax(){
	cg_backend_ajax_require_access_json();
	$command = isset($_POST['command']) && is_string($_POST['command']) ? $_POST['command'] : '';
	$galleryId = isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
	if(!$galleryId || !cg_participant_mail_gallery_exists($galleryId)){cg_backend_ajax_error_json('This gallery no longer exists.',404,'cg_pmail_gallery');}
	$id = isset($_POST['id']) && is_string($_POST['id']) ? $_POST['id'] : '';
	$job = $id ? cg_participant_mail_job($id) : false;
	if($id && (!$job || $job['gallery_id']!==$galleryId)){cg_backend_ajax_error_json('This message does not belong to this gallery.',404,'cg_pmail_missing');}
	$result = false;
	if($command==='open'){
		$latest=cg_participant_mail_latest($galleryId);
		if($latest && $latest['status']==='running'){cg_participant_mail_schedule(time());}
		$response=$latest ? cg_participant_mail_job_response($latest) : false;
		$draft=cg_participant_mail_composer_open($galleryId,get_current_user_id());
		$result=is_wp_error($draft) ? $draft : (is_wp_error($response) ? $response : array('job'=>$response,'draft'=>$draft));
	}elseif($command==='draft_load'){
		$draft=cg_participant_mail_composer_read($galleryId,get_current_user_id());$result=is_wp_error($draft) ? $draft : array('draft'=>$draft);
	}elseif($command==='draft_save' || $command==='draft_discard'){
		$revision=isset($_POST['draft_revision']) && is_string($_POST['draft_revision']) && preg_match('/^(?:0|[1-9][0-9]{0,14})$/D',$_POST['draft_revision']) ? (int)$_POST['draft_revision'] : -1;
		$writeId=isset($_POST['draft_write_id']) ? $_POST['draft_write_id'] : '';
		$message=$command==='draft_discard' ? false : cg_participant_mail_composer_message($_POST);
		$draft=is_wp_error($message) ? $message : cg_participant_mail_composer_write($galleryId,get_current_user_id(),$revision,$message,$writeId);
		if(is_wp_error($draft) && $draft->get_error_code()==='cg_pmail_draft_conflict'){$conflict=$draft->get_error_data();wp_send_json_error(array('message'=>$draft->get_error_message(),'code'=>$draft->get_error_code(),'draft'=>$conflict['draft']),409);}
		$result=is_wp_error($draft) ? $draft : array('draft'=>$draft);
	}elseif($command==='selection'){
		$result=cg_participant_mail_selection($galleryId,wp_unslash($_POST));
	}elseif($command==='participant_options'){
		$search=isset($_POST['participant_search']) && is_string($_POST['participant_search']) ? sanitize_text_field(wp_unslash($_POST['participant_search'])) : '';
		$selected=isset($_POST['selected_user']) && is_scalar($_POST['selected_user']) ? (string)$_POST['selected_user'] : '';
		$result=cg_backend_gallery_user_filter_options($galleryId,$search,$selected,true);
	}elseif($command==='preview'){
		$scope = isset($_POST['scope']) && $_POST['scope']==='filtered' ? 'filtered' : 'all';
		$values=wp_unslash($_POST);$allValues=array();
		if(isset($values['cg_pmail_recipients'])){$allValues['cg_pmail_recipients']=$values['cg_pmail_recipients'];}
		$filters = cg_participant_mail_filters($scope==='filtered' ? $values : $allValues,$galleryId);
		$message = cg_participant_mail_message($_POST);
		if(is_wp_error($filters)){$result=$filters;}
		elseif(is_wp_error($message)){$result=$message;}
		else{
			$selection=cg_participant_mail_collect($galleryId,$filters);
			if(is_wp_error($selection)){$result=$selection;}
			elseif(!$selection['recipients']){$result=new WP_Error('cg_pmail_empty',$selection['entry_count'].' entries match this selection. None have a valid participant email address.');}
			else{
				$composerRevision=0;$draft=cg_participant_mail_composer_read($galleryId,get_current_user_id());
				if(!is_wp_error($draft) && isset($_POST['draft_revision']) && is_string($_POST['draft_revision']) && (string)$draft['revision']===(string)$_POST['draft_revision'] && $draft['message']===$message){$composerRevision=$draft['revision'];}
				$job=cg_participant_mail_create($galleryId,$scope,$filters,$selection,$message,$composerRevision);
				if(is_wp_error($job)){$result=$job;}
				else{
					$result=cg_participant_mail_job_response($job);
					if(!is_wp_error($result)){
						$test=cg_participant_mail_test_preview($job,'self');$options=cg_participant_mail_test_options($job);
						if(is_wp_error($test)){$result=$test;}
						elseif(is_wp_error($options)){$result=$options;}
						else{
							$result=array_merge($result,$test);$result['test_options_html']=$options;
							$result['recipients_html']=cg_participant_mail_recipients_html(array_slice($selection['recipients'],0,50));$result['recipient_offset']=min(50,count($selection['recipients']));
						}
					}
				}
			}
		}
	}elseif(!$job){$result=new WP_Error('cg_pmail_missing','Preview your message and recipients first.');}
	elseif($command==='recipients'){
		$offset=isset($_POST['offset']) ? absint($_POST['offset']) : 0;
		$number=(int)floor($offset/50);$chunk=$number<$job['chunks'] ? cg_participant_mail_read(cg_participant_mail_chunk_key($id,$number)) : array();
		if(!is_array($chunk)){$result=new WP_Error('cg_pmail_storage','The saved recipient list is incomplete.');}
		else{
			$result=array('recipients_html'=>cg_participant_mail_recipients_html($chunk),'recipient_count'=>$job['recipient_count'],
				'recipient_offset'=>min($job['recipient_count'],($number+1)*50));
			if($offset===0 && !empty($chunk[0])){
				if($job['status']==='draft'){
					$test=cg_participant_mail_test_preview($job,'self');$options=cg_participant_mail_test_options($job);
					if(is_wp_error($test)){$result=$test;}
					elseif(is_wp_error($options)){$result=$options;}
					else{$result=array_merge($result,$test);$result['test_options_html']=$options;}
				}else{$result['preview_html']=cg_participant_mail_preview_html($job['message'],$chunk[0]);}
			}
		}
	}
	elseif($command==='test_preview'){
		$result=cg_participant_mail_test_preview($job,isset($_POST['test_recipient']) ? $_POST['test_recipient'] : 'self');
	}
	elseif($command==='status'){
		if($job['status']==='running' && !wp_next_scheduled('cg_participant_mail_worker')){cg_participant_mail_schedule(time());}
		$result=cg_participant_mail_job_response($job);
	}
	elseif($command==='start' || $command==='retry'){
		$job=cg_participant_mail_transition($id,$command==='retry');$result=is_wp_error($job) ? $job : cg_participant_mail_job_response($job);
		if($command==='start' && !is_wp_error($job) && !is_wp_error($result)){cg_participant_mail_composer_clear_started($job);$currentDraft=cg_participant_mail_composer_read($galleryId,get_current_user_id());$result['current_draft']=is_wp_error($currentDraft) ? false : $currentDraft;}
	}elseif($command==='stop'){
		if($job['status']!=='running'){$result=cg_participant_mail_job_response($job);}
		elseif(!cg_participant_mail_request_stop($id)){$result=new WP_Error('cg_pmail_stop','The stop request could not be saved.');}
		else{cg_participant_mail_schedule(time());$result=cg_participant_mail_job_response($job);}
	}elseif($command==='test'){
		$target=cg_participant_mail_test_recipient($job,isset($_POST['test_recipient']) ? $_POST['test_recipient'] : 'self');
		if(is_wp_error($target)){$result=$target;}
		elseif(!is_email($target['email'])){$result=new WP_Error('cg_pmail_test','Your WordPress account needs a valid email address.');}
		else{
			$result=cg_participant_mail_send($job['message'],$target['recipient'],true,$target['email'])
				? array('message'=>'Test email to '.$target['email'].' accepted by the mailer.') : new WP_Error('cg_pmail_test','The test email could not be sent.');
		}
	}else{$result=new WP_Error('cg_pmail_command','Invalid mail action.');}
	if(is_wp_error($result)){cg_backend_ajax_error_json($result->get_error_message(),400,$result->get_error_code());}
	wp_send_json_success($result);
}
}

add_action('wp_ajax_cg_participant_mail','cg_participant_mail_ajax');
add_action('cg_participant_mail_worker','cg_participant_mail_worker');
