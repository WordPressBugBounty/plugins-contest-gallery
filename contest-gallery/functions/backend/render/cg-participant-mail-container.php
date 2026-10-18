<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_participant_mail_container')){
function cg_participant_mail_container($galleryId){
	$isPro=cg_participant_mail_is_pro();
	$user = wp_get_current_user();
	$sender = cg_resolve_mail_from_reply(get_option('admin_email'),get_option('admin_email'));
	echo '<div id="cgParticipantMailContainer" class="cg_backend_action_container cg_pmail_modal cg_hide" role="dialog" aria-modal="true" aria-labelledby="cgParticipantMailTitle" data-gallery-id="'.absint($galleryId).'" data-test-email="'.esc_attr($user->user_email).'" data-pro-placeholders="'.($isPro ? '1' : '0').'" data-draft-scope="'.esc_attr(md5(site_url().'|'.get_current_blog_id())).'" data-draft-user="'.absint($user->ID).'">';
	echo '<button type="button" class="cg_message_close cg_pmail_close" aria-label="Close participant emails"></button>';
	echo '<div class="cg_pmail_heading"><span class="cg_pmail_kicker">STAY IN TOUCH</span><h2 id="cgParticipantMailTitle">Email participants</h2><p>Keep participants informed about this contest. Each email address receives one message, even with several entries.</p></div>';
	echo '<section class="cg_pmail_card cg_pmail_opening cg_hide" role="status" aria-live="polite"><p class="cg_pmail_opening_text">Loading…</p><button type="button" data-cg-pmail-open-retry class="cg_hide">Retry</button></section>';
	echo '<div class="cg_pmail_compose cg_hide">';
	cg_participant_mail_selection_container($galleryId);
	echo '<section class="cg_pmail_card"><div class="cg_pmail_message_heading"><h3>Message</h3><div class="cg_pmail_draft_tools"><button type="button" data-cg-draft-action="history">Previous messages</button><button type="button" data-cg-draft-action="discard">Discard draft</button></div></div>';
	echo '<div class="cg_pmail_draft_state"><span class="cg_pmail_draft_status" role="status" aria-live="polite"></span><button type="button" class="cg_hide" data-cg-draft-action="retry">Retry</button></div>';
	echo '<div class="cg_pmail_draft_recovery cg_hide"><p>Unsaved changes are available in this browser.</p><button type="button" data-cg-draft-action="restore">Restore local changes</button><button type="button" data-cg-draft-action="dismiss">Use saved draft</button></div>';
	echo '<div class="cg_pmail_draft_conflict cg_hide"><p>This draft changed in another tab or device.</p><button type="button" data-cg-draft-action="load">Load saved draft</button><button type="button" data-cg-draft-action="keep">Keep my changes</button></div>';
	echo '<div class="cg_pmail_grid">';
	foreach(array('from_name'=>array('From name',get_option('blogname')),'from_email'=>array('From email',$sender['from']),
		'reply_name'=>array('Reply name',''),'reply_email'=>array('Reply email',$sender['reply_to'])) as $field=>$details){
		echo '<label>'.esc_html($details[0]).'<input data-cg-pmail-field="'.esc_attr($field).'" type="'.(strpos($field,'email')!==false ? 'email' : 'text').'" maxlength="256" value="'.esc_attr($details[1]).'"></label>';
	}
	echo '</div><label>Subject<input type="text" data-cg-pmail-field="subject" value=""></label>';
	echo '<label for="cgParticipantMailBody">Message</label><textarea id="cgParticipantMailBody" rows="10"></textarea>';
	echo '<div class="cg_pmail_feedback"><div class="cg_pmail_notice cg_hide" role="status" aria-live="polite"></div></div>';
	echo '<div class="'.($isPro ? 'cg_pmail_tokens_available' : 'cg_pmail_tokens_pro').'"><div class="cg_pmail_tokens_heading"><span>Insert into message:</span>'.($isPro ? '' : '<span class="cg_pmail_pro_badge">PRO</span>').'</div><div class="cg_pmail_tokens">';
	foreach(array('$wp_first_name$'=>'First name','$wp_last_name$'=>'Last name','$cg_entries_count$'=>'Entries count','$cg_entry_list$'=>'Entry list') as $token=>$label){
		echo '<button type="button" data-cg-pmail-token="'.esc_attr($token).'"'.($isPro ? '' : ' disabled aria-describedby="cgParticipantMailProHint"').'>'.esc_html($label).'</button>';
	}
	echo '</div>';
	if(!$isPro){echo '<p id="cgParticipantMailProHint" class="cg_pmail_pro_hint">Personalized placeholders are available with <a href="https://www.contest-gallery.com/pro-version/" target="_blank" rel="noopener noreferrer">Contest Gallery PRO</a>.</p>';}
	echo '</div><p class="cg_pmail_hint">Use this message for information about this contest. Your newsletter service manages general newsletters and marketing subscriptions.</p>';
	echo '<details class="cg_pmail_delivery_details"><summary><span aria-hidden="true">⚠️</span> Email delivery: check your setup</summary><p class="cg_pmail_hint">The setup test sends a neutral message to one address you choose. To test your actual participant message, use the test email in the review step.</p>';
	$recipientDescription='Selected participants from the recipient selection above';
	echo cg_mail_delivery_card('Participant emails',array(
		'from'=>array('[data-cg-pmail-field="from_email"]',$sender['from']),
		'reply'=>array('[data-cg-pmail-field="reply_email"]',$sender['reply_to']),
		'to'=>array('',$recipientDescription)
	),$recipientDescription,true);
	echo '</details></section>';
	echo '<div class="cg_pmail_preview_selection cg_hide"><p id="cgParticipantMailSelectionStatus" class="cg_pmail_hint" role="status" aria-live="polite"></p><button type="button" class="cg_hide" data-cg-pmail-selection-action="retry">Try again</button></div>';
	echo '<div class="cg_pmail_actions"><button type="button" class="cg_pmail_primary" data-cg-pmail-action="preview" aria-describedby="cgParticipantMailSelectionStatus">Preview message and recipients</button></div></div>';
	echo '<section class="cg_pmail_card cg_pmail_review cg_hide"><h3>Review your message</h3><p class="cg_pmail_summary"></p>';
	echo '<details class="cg_pmail_recipient_details"><summary>View recipients</summary><div class="cg_pmail_recipients"></div><button type="button" data-cg-pmail-action="recipients" class="cg_hide">Load more recipients</button></details>';
	echo '<div class="cg_pmail_test_selection"><label for="cgParticipantMailTestRecipient">Test recipient</label><select id="cgParticipantMailTestRecipient" aria-describedby="cgParticipantMailTestHint">'.cg_participant_mail_test_account_option_html().'</select><p id="cgParticipantMailTestHint" class="cg_pmail_hint" role="status" aria-live="polite"></p></div>';
	echo '<div class="cg_pmail_preview"></div><p class="cg_pmail_hint">The recipient list is saved for this message. A new preview refreshes it.</p>';
	echo '<div class="cg_pmail_feedback"></div><div class="cg_pmail_actions"><button type="button" data-cg-pmail-action="edit">Edit message</button><button type="button" data-cg-pmail-action="test">Send test email to '.esc_html($user->user_email).'</button><button type="button" class="cg_pmail_primary" data-cg-pmail-action="start">Send to participants</button></div></section>';
	echo '<section class="cg_pmail_card cg_pmail_progress cg_hide"><h3>Sending progress</h3><p class="cg_pmail_status"></p><progress max="1" value="0"></progress><div class="cg_pmail_counts"></div><p class="cg_pmail_unclear_note cg_hide">An interrupted sending attempt has an unclear result. These recipients will not be retried automatically.</p><p class="cg_pmail_hint">You can close this dialog. Sending continues in the background in small batches. Timing depends on WordPress Cron and your mail service. Mailer acceptance does not confirm delivery.</p>';
	echo '<div class="cg_pmail_feedback"></div><div class="cg_pmail_actions"><button type="button" data-cg-pmail-action="stop">Stop sending</button><button type="button" data-cg-pmail-action="retry" class="cg_hide">Retry failed recipients</button><button type="button" data-cg-pmail-action="new" class="cg_hide">Write another message</button></div></section></div>';
}
}

if(!function_exists('cg_participant_mail_test_account_option_html')){
function cg_participant_mail_test_account_option_html(){
	return '<option value="self">My account – '.esc_html(wp_get_current_user()->user_email).'</option>';
}
}

if(!function_exists('cg_participant_mail_test_options_html')){
function cg_participant_mail_test_options_html($recipients,$offset){
	$html='';
	foreach($recipients as $recipient){
		$name=trim($recipient['first_name'].' '.$recipient['last_name']);
		$html.='<option value="'.esc_attr((string)$offset).'">'.esc_html(($name!=='' ? $name.' – ' : '').$recipient['email']).'</option>';$offset++;
	}
	return $html;
}
}

if(!function_exists('cg_participant_mail_recipients_html')){
function cg_participant_mail_recipients_html($recipients){
	if(!$recipients){return '<p>No recipients match this selection.</p>';}
	$html = '<table class="cg_pmail_recipient_table"><thead><tr><th>Name</th><th>Email</th><th>Entries</th></tr></thead><tbody>';
	foreach($recipients as $recipient){$name=trim($recipient['first_name'].' '.$recipient['last_name']);$html .= '<tr><td>'.esc_html($name!=='' ? $name : '—').'</td><td>'.esc_html($recipient['email']).'</td><td>'.count($recipient['entries']).'</td></tr>';}
	return $html.'</tbody></table>';
}
}

if(!function_exists('cg_participant_mail_recipient_options_html')){
function cg_participant_mail_recipient_options_html($recipients,$selectedKeys){
	if(!$recipients){return '<p>No recipients match these filters.</p>';}
	$html='';
	foreach($recipients as $recipient){
		$key=cg_participant_mail_recipient_key($recipient);$name=trim($recipient['first_name'].' '.$recipient['last_name']);$entries=count($recipient['entries']);
		$html.='<label class="cg_pmail_recipient_option"><input type="checkbox" data-cg-pmail-recipient value="'.esc_attr($key).'"'.(in_array($key,$selectedKeys,true) ? ' checked' : '').'><span class="cg_pmail_recipient_identity"><strong>'.esc_html($name!=='' ? $name : $recipient['email']).'</strong>';
		if($name!==''){$html.='<span>'.esc_html($recipient['email']).'</span>';}
		$html.='</span><span class="cg_pmail_recipient_entries">'.$entries.' '.($entries===1 ? 'entry' : 'entries').'</span></label>';
	}
	return $html;
}
}

if(!function_exists('cg_participant_mail_selection_container')){
function cg_participant_mail_selection_container($galleryId){
	global $wpdb;
	$categories=$wpdb->get_results($wpdb->prepare("SELECT id,Name FROM {$wpdb->prefix}contest_gal1ery_categories WHERE GalleryID=%d ORDER BY id ASC",$galleryId));
	echo '<section class="cg_pmail_card cg_pmail_selection"><div class="cg_pmail_selection_heading"><h3>Recipients</h3><strong class="cg_pmail_selection_count" aria-live="polite">Loading…</strong></div>';
	echo '<p class="cg_pmail_selection_totals"></p><p class="cg_pmail_filter_summary">All participants</p><div class="cg_pmail_selection_notice cg_hide" role="status" aria-live="polite"></div>';
	echo '<button type="button" class="cg_pmail_selection_retry cg_hide" data-cg-pmail-selection-action="retry">Try again</button>';
	echo '<details class="cg_pmail_filters"><summary>Filter recipients</summary><div class="cg_pmail_grid cg_pmail_filter_grid">';
	echo '<label>Entry status<select id="cgParticipantMailStatus"><option value="">All entries</option><option value="active">Active entries</option><option value="inactive">Inactive entries</option><option value="conflict" hidden>No matching entry status</option></select></label>';
	echo '<label>Winners<select id="cgParticipantMailWinners"><option value="">All participants</option><option value="1">Winners only</option></select></label>';
	echo '<label>Category<select id="cgParticipantMailCategory"><option value="">All categories</option><option value="0">Without category</option>';
	foreach($categories as $category){echo '<option value="'.absint($category->id).'">'.esc_html(contest_gal1ery_convert_for_html_output_without_nl2br($category->Name)).'</option>';}
	echo '</select></label><div class="cg_pmail_participant_filter"><label for="cgParticipantMailUser">Participant</label><select id="cgParticipantMailUser"><option value="">All participants</option><option value="0">Uploads without an account</option></select><span class="cg_pmail_user_notice" role="status"></span></div>';
	echo '</div><button type="button" data-cg-pmail-selection-action="reset">Reset filters</button></details>';
	echo '<p class="cg_pmail_selection_hint" aria-live="polite">No individual selection. All matching participants will receive this message.</p><div class="cg_pmail_recipient_scroll" role="group" aria-label="Choose recipients"><div class="cg_pmail_live_recipients"></div><button type="button" class="cg_pmail_live_more cg_hide" data-cg-pmail-selection-action="more">Load more</button></div><button type="button" class="cg_pmail_clear_recipients" data-cg-pmail-selection-action="all">Clear individual selection</button></section>';
}
}

if(!function_exists('cg_participant_mail_preview_html')){
function cg_participant_mail_preview_html($message,$recipient){
	$resolved = cg_participant_mail_resolve($message,$recipient);
	$document = '<!doctype html><html><head><meta charset="utf-8"><style>body{font:15px/1.6 Arial,sans-serif;padding:16px;color:#25382e;overflow-wrap:anywhere}img{max-width:100%;height:auto}</style></head><body>'.$resolved['body'].'</body></html>';
	return '<p class="cg_pmail_sample">Example for '.esc_html($recipient['email']).'</p><strong class="cg_pmail_subject">'.esc_html($resolved['subject']).'</strong><iframe class="cg_pmail_preview_frame" title="Participant email preview" sandbox="" srcdoc="'.esc_attr($document).'"></iframe>';
}
}
