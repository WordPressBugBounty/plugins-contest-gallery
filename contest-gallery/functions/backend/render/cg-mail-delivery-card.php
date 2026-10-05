<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_mail_delivery_routing_note')){
	function cg_mail_delivery_routing_note($isTestResult = false){
		$resultNote = $isTestResult ? 'The email may still fail to arrive even when no immediate sending error is reported. ' : '';
		return '<div class="cg-mail-delivery-routing-note"><span class="cg-mail-delivery-routing-icon" aria-hidden="true">⚠️</span><div><strong>Emails to your website’s own domain may need special attention.</strong><p>'.$resultNote.'Some hosting providers restrict emails sent by websites. As a quick test, try a recipient address you can access at another provider, such as Gmail. If emails to your own domain do not arrive, your hosting provider can check for restrictions and incorrect mail settings.</p></div></div>';
	}
}

if(!function_exists('cg_mail_delivery_card')){
	function cg_mail_delivery_card($type,$fields,$recipient,$isMessage = false){
		$labels = array('from'=>'Configured From','reply'=>'Reply-To','to'=>'Recipient');
		$empty = array(
			'from'=>'Not set. Uses Reply-To if valid, otherwise WordPress mail defaults.',
			'reply'=>'Not set. Uses From if valid, otherwise no Reply-To header.',
			'to'=>'Not set.'
		);
		$attributes = '';
		$rows = '';
		foreach($labels as $key=>$label){
			$field = isset($fields[$key]) ? $fields[$key] : array('',$key==='to' ? $recipient : '');
			$value = trim(wp_specialchars_decode((string)$field[1],ENT_QUOTES));
			$attributes .= ' data-cg-mail-'.$key.'="'.esc_attr($field[0]).'"';
			$rows .= '<dt>'.esc_html($label).'</dt><dd data-cg-mail-value="'.$key.'" data-cg-mail-empty="'.esc_attr($empty[$key]).'">'.esc_html($value!=='' ? $value : $empty[$key]).'</dd>';
		}
		return '<div class="cg-mail-delivery-card" data-cg-mail-type="'.esc_attr($type).'" data-cg-mail-recipient="'.esc_attr($recipient).'" data-cg-mail-mode="'.($isMessage ? 'message' : 'options').'"'.$attributes.'>'
			.'<div class="cg-mail-delivery-title"><span aria-hidden="true">⚠️</span> Email delivery: check your setup</div>'
			.'<p>Before going live, test that these emails reach the recipient.</p>'
			.'<p>If an email does not arrive, check the spam folder and your WordPress sender and SMTP settings.</p>'
			.cg_mail_delivery_routing_note()
			.'<dl class="cg-mail-delivery-values">'.$rows.'</dl>'
			.'<p class="cg-mail-delivery-caption">'.($isMessage ? 'Current message values.' : 'Current form values.').'<span class="cg-mail-delivery-dirty" hidden> Unsaved changes.</span><br>A WordPress mail or SMTP plugin may override the sender.</p>'
			.(current_user_can('manage_options') ? '<div class="cg-mail-delivery-actions"><button type="button" class="cg-mail-delivery-test">Send test email</button></div><p class="cg-mail-delivery-caption">For delivery problems, send a test email. If you need further help, use "Copy diagnostic report" to share the result with hosting support.</p>' : '')
			.'</div>';
	}
}
