<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_mail_history_container')){
function cg_mail_history_container(){
	echo '<div id="cgMailHistoryContainer" class="cg_backend_action_container cg_mail_history_modal cg_hide" role="dialog" aria-modal="true" aria-labelledby="cgMailHistoryTitle">';
	echo '<button type="button" class="cg_message_close cg_mail_history_close" aria-label="Close email history"></button>';
	echo '<button type="button" class="cg_mail_history_return cg_hide">Back to message</button><div class="cg_mail_history_heading"><h2 id="cgMailHistoryTitle">Email history</h2><p class="cg_mail_history_context"></p></div>';
	echo '<p class="cg_mail_history_notice cg_hide" role="status" aria-live="polite"></p><div class="cg_mail_history_content"></div></div>';
}
}

if(!function_exists('cg_mail_history_type')){
function cg_mail_history_type($type){
	$labels=array('participants-campaign'=>'Participant email','entry-backend'=>'Entry email','entry-backend-custom'=>'Custom entry email',
		'entry-backend-custom-activation'=>'Custom activation email','entry-backend-custom-deactivation'=>'Custom deactivation email',
		'entry-frontend'=>'Upload activation email','entry-backend-inform-image-activation'=>'Entry activation notification',
		'registry-frontend'=>'Registration or PIN email','registry-resend-frontend'=>'Resent registration email','registry-resend-backend'=>'Resent registration email',
		'user-comment-notification'=>'Comment notification','user-vote-notification'=>'Vote notification');
	return isset($labels[$type]) ? $labels[$type] : ($type ? ucfirst(str_replace('-',' ',$type)) : 'Email');
}
}

if(!function_exists('cg_mail_history_status')){
function cg_mail_history_status($status){
	$labels=array('running'=>'Sending','stopping'=>'Stopping','complete'=>'Finished','cancelled'=>'Stopped','error'=>'Processing error',
		'pending'=>'Pending','sending'=>'Sending','accepted'=>'Accepted by mailer','failed'=>'Failed','unknown'=>'Unclear result','recorded'=>'Recorded','unconfirmed'=>'Sending not confirmed');
	return isset($labels[$status]) ? $labels[$status] : 'Recorded';
}
}

if(!function_exists('cg_mail_history_date')){
function cg_mail_history_date($time){return $time ? cg_get_time_based_on_wp_timezone_conf($time,'d-M-Y H:i:s') : 'Not recorded';}
}

if(!function_exists('cg_mail_history_button')){
function cg_mail_history_button($label,$view,$attributes=''){
	return '<button type="button" data-cg-history-view="'.esc_attr($view).'" '.$attributes.'>'.esc_html($label).'</button>';
}
}

if(!function_exists('cg_mail_history_pager')){
function cg_mail_history_pager($page,$more,$view,$attributes=''){
	$html='<div class="cg_mail_history_pager"><span>Page '.($page+1).'</span>';
	if($page){$html.=cg_mail_history_button('Previous',$view,'data-page="'.($page-1).'" '.$attributes);}
	if($more){$html.=cg_mail_history_button('Next',$view,'data-page="'.($page+1).'" '.$attributes);}
	return $html.'</div>';
}
}

if(!function_exists('cg_mail_history_list_html')){
function cg_mail_history_list_html($rows,$context,$view,$page){
	$more=count($rows)>50;$rows=array_slice($rows,0,50);$campaigns=$context==='gallery' && $view==='list';$html='';
	if($context!=='gallery'){$html.='<p>Previously recorded emails and notification logs. Some email types were not archived. A log does not confirm delivery.</p>';}
	if(!$rows){return $html.'<div class="cg_mail_history_empty">No emails recorded yet.</div>'.cg_mail_history_pager($page,false,$view);}
	$html.='<div class="cg_mail_history_table_wrap"><table class="cg_mail_history_table"><thead><tr><th>Date</th><th>'.($campaigns ? 'Subject' : 'Email').'</th><th>'.($campaigns ? 'Recipients' : ($context==='gallery' ? 'To' : 'Gallery')).'</th><th>Status</th></tr></thead><tbody>';
	foreach($rows as $row){
		if($campaigns){
			$subject=$row['subject'];$title=cg_mail_history_button($subject,'campaign','data-campaign="'.esc_attr($row['id']).'"');
			$date=$row['created_at'];$third=$row['recipient_count'];$status=cg_mail_history_status($row['status']);
		}else{
			if($row['Source']==='mail' && $row['MailType']!=='participants-campaign'){$row['Subject']=contest_gal1ery_convert_for_html_output_without_nl2br($row['Subject']);}
			$subject=$row['Subject']!=='' ? $row['Subject'] : 'Subject not recorded';
			$title=cg_mail_history_button($subject,'message','data-source="'.esc_attr($row['Source']).'" data-mail-id="'.absint($row['id']).'" data-page="'.$page.'"');
			$title.='<small>'.esc_html(cg_mail_history_type($row['MailType'])).'</small>';$date=$row['Tstamp'];
			$third=$context==='gallery' ? $row['ReceiverMail'] : ($row['GalleryID'] ? '#'.$row['GalleryID'] : 'General');
			$status=cg_mail_history_status($row['Source']==='mail' ? ($row['MailType']==='participants-campaign' ? 'accepted' : 'recorded') : 'unconfirmed');
		}
		$html.='<tr><td>'.esc_html(cg_mail_history_date($date)).'</td><td>'.$title.'</td><td>'.esc_html($third).'</td><td><span class="cg_mail_history_badge">'.esc_html($status).'</span></td></tr>';
	}
	return $html.'</tbody></table></div>'.cg_mail_history_pager($page,$more,$view);
}
}

if(!function_exists('cg_mail_history_campaign_html')){
function cg_mail_history_campaign_html($job,$recipients,$page){
	$html=cg_mail_history_button('Back to email history','list').'<section class="cg_mail_history_card"><h3>'.esc_html($job['message']['subject']).'</h3>';
	$html.='<button type="button" data-cg-history-view="reuse" data-campaign="'.esc_attr($job['id']).'">Use as new message</button><p>'.esc_html(cg_mail_history_date($job['created_at'])).' · '.absint($job['recipient_count']).' recipients · '.esc_html(cg_mail_history_status($job['status'])).'</p><p>Mailer acceptance does not confirm delivery.</p>';
	$html.='<h4>Original message</h4><p>Placeholders appear as saved. Select a recipient below to view their personalized email.</p>';
	if(isset($job['message']['body']) && is_string($job['message']['body']) && trim($job['message']['body'])!==''){
		$html.=cg_mail_history_frame($job['message']['body']);
	}else{
		$html.='<p>Message content was not recorded.</p>';
	}
	$html.='</section>';
	$html.='<div class="cg_mail_history_table_wrap"><table class="cg_mail_history_table"><thead><tr><th>Recipient</th><th>Entries</th><th>Status</th></tr></thead><tbody>';
	foreach($recipients as $offset=>$recipient){
		$title=!empty($recipient['erased']) ? 'Deleted recipient' : cg_mail_history_button($recipient['email'],'recipient','data-campaign="'.esc_attr($job['id']).'" data-recipient="'.($page*50+$offset).'"');
		$html.='<tr><td>'.$title.'</td><td>'.count($recipient['entries']).'</td><td>'.esc_html(cg_mail_history_status($recipient['status'])).'</td></tr>';
	}
	return $html.'</tbody></table></div>'.cg_mail_history_pager($page,($page+1)<$job['chunks'],'campaign','data-campaign="'.esc_attr($job['id']).'"');
}
}

if(!function_exists('cg_mail_history_frame')){
function cg_mail_history_frame($body){
	$html='<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="default-src \'none\'; img-src data: https: http:; style-src \'unsafe-inline\';"><style>body{font:15px/1.6 Arial,sans-serif;color:#25382e;padding:16px;overflow-wrap:anywhere}img{max-width:100%;height:auto}</style></head><body>'.wp_kses_post($body).'</body></html>';
	return '<iframe class="cg_mail_history_frame" title="Saved email content" sandbox="" srcdoc="'.esc_attr($html).'"></iframe>';
}
}

if(!function_exists('cg_mail_history_recipient_html')){
function cg_mail_history_recipient_html($job,$recipient,$page){
	global $wpdb;
	$html=cg_mail_history_button('Back to recipients','campaign','data-campaign="'.esc_attr($job['id']).'" data-page="'.$page.'"');
	if(!empty($recipient['erased'])){return $html.'<p>The recipient data was removed.</p>';}
	$resolved=cg_participant_mail_resolve($job['message'],$recipient);
	if(!empty($recipient['mail_id']) && cg_mail_history_table_exists('contest_gal1ery_mails')){
		$record=$wpdb->get_row($wpdb->prepare("SELECT Subject,Body FROM {$wpdb->prefix}contest_gal1ery_mails WHERE id=%d AND GalleryID=%d AND ReceiverMail=%s AND MailType='participants-campaign'",$recipient['mail_id'],$job['gallery_id'],$recipient['email']),ARRAY_A);
		if($record){$resolved=array('subject'=>$record['Subject'],'body'=>$record['Body']);}
	}
	$html.='<section class="cg_mail_history_card"><h3>'.esc_html($resolved['subject']).'</h3><dl><dt>To</dt><dd>'.esc_html($recipient['email']).'</dd><dt>Status</dt><dd>'.esc_html(cg_mail_history_status($recipient['status'])).'</dd><dt>Last attempt</dt><dd>'.esc_html(cg_mail_history_date(isset($recipient['attempted_at']) ? $recipient['attempted_at'] : 0)).'</dd></dl>';
	$html.='<dl><dt>From name</dt><dd>'.esc_html($job['message']['from_name']).'</dd><dt>From email</dt><dd>'.esc_html($job['message']['from_email']).'</dd><dt>Reply name</dt><dd>'.esc_html($job['message']['reply_name']).'</dd><dt>Reply email</dt><dd>'.esc_html($job['message']['reply_email']).'</dd></dl>';
	$html.='<p>This is the saved message for this recipient. Mailer acceptance does not confirm delivery.</p></section>';
	return $html.cg_mail_history_frame($resolved['body']);
}
}

if(!function_exists('cg_mail_history_message_html')){
function cg_mail_history_message_html($row,$back,$page){
	$subject=$row['Subject']!=='' ? $row['Subject'] : 'Subject not recorded';
	$html=cg_mail_history_button('Back to email history',$back,'data-page="'.$page.'"').'<section class="cg_mail_history_card"><h3>'.esc_html($subject).'</h3><p>'.esc_html(cg_mail_history_type($row['MailType'])).' · '.esc_html(cg_mail_history_date($row['Tstamp'])).'</p><dl>';
	foreach(array('ReceiverMail'=>'To','FromName'=>'From name','FromMail'=>'From email','ReplyName'=>'Reply name','ReplyMail'=>'Reply email','Cc'=>'Cc','Bcc'=>'Bcc','GalleryID'=>'Gallery','pid'=>'Entry') as $key=>$label){
		if(isset($row[$key]) && (string)$row[$key]!=='' && (string)$row[$key]!=='0'){$html.='<dt>'.esc_html($label).'</dt><dd>'.esc_html($row[$key]).'</dd>';}
	}
	$html.='</dl><p>'.($row['Source']==='mail' ? 'A saved record does not confirm delivery.' : 'This notification log does not confirm successful sending. Missing original headers and subject were not recorded.').'</p></section>';
	return $html.cg_mail_history_frame($row['Body']);
}
}
