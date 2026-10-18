<?php
if(!defined('ABSPATH')){exit;}

function cg_registration_details_agreements($userId){
	if(!current_user_can('manage_options') || !get_userdata($userId) || (is_multisite() && !is_user_member_of_blog($userId))){return;}
	$receipt = get_user_meta($userId,cg_registration_agreements_meta_key(),true);
	echo '<section class="cg-registration-details-card" aria-labelledby="cgRegistrationAgreementsTitle"><h2 id="cgRegistrationAgreementsTitle">Registration agreements</h2>';
	if(!is_array($receipt) || empty($receipt['agreements']) || !is_array($receipt['agreements'])){
		echo '<p class="cg-registration-details-empty">No registration agreements recorded for this user.</p></section>';
		return;
	}
	$date = '';
	if(isset($receipt['recordedAt']) && is_string($receipt['recordedAt']) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D',$receipt['recordedAt'])){
		$date = get_date_from_gmt(str_replace(array('T','Z'),array(' ',''),$receipt['recordedAt']),get_option('date_format').' '.get_option('time_format').' T');
	}
	$sources = array('gallery-member'=>'Gallery / upload','users-pin-access'=>'Email PIN','paid-actions'=>'Paid Actions','google'=>'Google');
	$source = isset($receipt['source']) && is_string($receipt['source']) && isset($sources[$receipt['source']]) ? $sources[$receipt['source']] : 'Unknown';
	echo '<dl class="cg-registration-details-summary"><div><dt>Recorded</dt><dd>'.esc_html($date!=='' ? $date : 'Unknown').'</dd></div><div><dt>Registration method</dt><dd>'.esc_html($source).'</dd></div>';
	if(!empty($receipt['galleryId']) && is_scalar($receipt['galleryId'])){
		echo '<div><dt>Gallery ID</dt><dd>'.absint($receipt['galleryId']).'</dd></div>';
	}
	echo '</dl>';
	foreach(array(1,2,3) as $slot){
		if(empty($receipt['agreements'][$slot]) || !is_array($receipt['agreements'][$slot])){continue;}
		$row = $receipt['agreements'][$slot];
		$html = isset($row['html']) && is_string($row['html']) ? wp_kses_post($row['html']) : '';
		$accepted = !empty($row['accepted']);
		echo '<article class="cg-registration-details-agreement"><div class="cg-registration-details-agreement-heading"><h3>Registration agreement '.absint($slot).'</h3><div class="cg-registration-details-badges"><span class="cg-registration-details-status'.($accepted ? ' is-accepted' : '').'">'.($accepted ? 'Accepted' : 'Not accepted').'</span><span class="cg-registration-details-requirement">'.(!empty($row['required']) ? 'Required' : 'Optional').'</span></div></div><div class="cg-registration-details-agreement-text">'.$html.'</div></article>';
	}
	echo '<p class="cg-registration-details-note">These are the agreements shown when this account was created.</p></section>';
}
