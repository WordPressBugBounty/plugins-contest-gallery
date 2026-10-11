<?php
if(!defined('ABSPATH')){exit;}
function cg_member_admin_threads_html($userId,$rows,$selected=0){
	$html='';
	foreach($rows as $row){
		$other=cg_community_other($row,$userId);
		$html.='<button type="button" class="cg-member-admin-thread" data-conversation="'.absint($row->id).'" aria-pressed="'.((int)$row->id===(int)$selected ? 'true' : 'false').'"><strong>'.esc_html(cg_member_admin_messages_name($other)).'</strong><time datetime="'.esc_attr(gmdate('c',$row->UpdatedAt)).'">'.esc_html(cg_community_message_date($row->UpdatedAt)).'</time></button>';
	}
	return $html;
}
function cg_member_admin_messages_html($userId,$rows){
	$html='';
	foreach($rows as $row){
		$html.='<article class="cg-member-admin-message'.((int)$row->SenderID===(int)$userId ? ' is-selected-user' : '').'" data-message-id="'.absint($row->id).'"><strong>'.esc_html(cg_member_admin_messages_name($row->SenderID)).'</strong><p>'.esc_html($row->Body).'</p><time datetime="'.esc_attr(gmdate('c',$row->CreatedAt)).'">'.esc_html(cg_community_message_date($row->CreatedAt)).'</time></article>';
	}
	return $html;
}
function cg_member_admin_messages_page($userId){
	if(!current_user_can('manage_options')){echo '<div class="notice notice-error"><p>Administrator rights are required.</p></div>';return;}
	$user=cg_member_admin_messages_user($userId);
	if(!$user){echo '<div class="notice notice-error"><p>This user is no longer available.</p></div>';return;}
	$data=cg_member_admin_threads_data($userId,1);
	if(is_wp_error($data)){echo '<div class="notice notice-error"><p>'.esc_html($data->get_error_message()).'</p></div>';return;}
	$selected=count($data['items']) ? (int)$data['items'][0]->id : 0;
	$history=$selected ? cg_member_admin_messages_data($userId,$selected) : false;
	if(is_wp_error($history)){echo '<div class="notice notice-error"><p>'.esc_html($history->get_error_message()).'</p></div>';return;}
	echo '<section id="cgMemberAdminMessages" data-user="'.absint($userId).'" data-conversation="'.$selected.'"><header class="cg-member-admin-header"><div><span class="cg-member-admin-kicker">Users management</span><h1>Messages — '.esc_html(cg_member_admin_messages_name($userId)).'</h1><p>Review conversations on this website. Reading here does not mark messages as read.</p></div><div class="cg-member-admin-communication">'.cg_member_admin_communication_html($userId).'</div></header><div class="cg-member-admin-columns"><aside class="cg-member-admin-conversations"><h2>Conversations</h2><div class="cg-member-admin-thread-list">';
	echo count($data['items']) ? cg_member_admin_threads_html($userId,$data['items'],$selected) : '<p class="cg-member-admin-empty">No conversations yet.</p>';
	echo '</div><button type="button" class="button cg-member-admin-more-threads" data-page="2"'.(!$data['more'] ? ' hidden' : '').'>Load more</button></aside><section class="cg-member-admin-conversation"><h2 class="cg-member-admin-conversation-heading">'.($history ? esc_html(cg_member_admin_messages_name(cg_community_other($history['thread'],$userId))) : 'Messages').'</h2><div class="cg-member-admin-history">';
	echo $history && count($history['items']) ? cg_member_admin_messages_html($userId,$history['items']) : '<p class="cg-member-admin-empty">No messages yet.</p>';
	echo '</div><button type="button" class="button cg-member-admin-older" data-before="'.($history ? $history['before'] : 0).'"'.(!$history || !$history['more'] ? ' hidden' : '').'>Earlier messages</button></section></div><p class="cg-member-admin-status" role="status" aria-live="polite"></p></section>';
}

function cg_member_admin_communication_html($id){
	$disabled=cg_community_communication_disabled($id);
	$html='<strong class="cg-member-admin-communication-status">'.($disabled ? 'Communication disabled' : 'Communication enabled').'</strong>';
	if(current_user_can('edit_user',$id)){
		$name=cg_member_admin_messages_name($id);
		$confirm=($disabled ? 'Enable' : 'Disable').' outgoing messages and friend requests for '.$name.' on this website?';
		$html.='<button type="button" class="button cg-member-admin-set-communication" data-disabled="'.($disabled ? '0' : '1').'" data-confirm="'.esc_attr($confirm).'">'.($disabled ? 'Enable communication' : 'Disable communication').'</button>';
	}
	return $html.'<p>Controls outgoing messages and new friend requests on this website. Existing conversations remain available.</p>';
}
