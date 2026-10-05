<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_mail_test_container')){
	function cg_mail_test_container(){
		if(!current_user_can('manage_options')){return;}
		global $wp_version;
		$user = wp_get_current_user();
		echo '<div id="cgMailTestBackdrop" class="cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty" hidden></div><div id="cgMailTestContainer" class="cg_backend_action_container cg_hide cg_do_not_remove_when_ajax_load cg_do_not_remove_when_main_empty" role="dialog" aria-modal="true" aria-labelledby="cgMailTestTitle" aria-describedby="cgMailTestDescription" tabindex="-1" data-nonce="'.esc_attr(wp_create_nonce('cg_send_test_mail')).'" data-recipient="'.esc_attr($user->user_email).'" data-site="'.esc_attr(home_url('/')).'" data-wordpress="'.esc_attr($wp_version).'" data-php="'.esc_attr(PHP_VERSION).'">'
			.'<button type="button" class="cg-mail-test-close cg-mail-test-x" aria-label="Close test email">&times;</button>'
			.'<h2 id="cgMailTestTitle" tabindex="-1">Send a test email</h2>'
			.'<p id="cgMailTestDescription">Send one neutral message through your WordPress mail setup. No registration or PIN is created. These values are used only for this test.</p>'
			.'<p class="cg-mail-test-context"></p>'
			.'<form id="cgMailTestForm" novalidate>'
			.'<label for="cgMailTestFrom">From <span class="cg-mail-test-required">(Required)</span></label><input id="cgMailTestFrom" name="from" type="email" maxlength="254" autocomplete="off" required aria-describedby="cgMailTestFromHint cgMailTestFromError"><p id="cgMailTestFromError" class="cg_input_error cg-mail-test-field-error" hidden></p><small id="cgMailTestFromHint">Use the sender address configured for your website or SMTP service. A mail plugin may override it.</small>'
			.'<label for="cgMailTestReply">Reply-To <span class="cg-mail-test-required">(Required)</span></label><input id="cgMailTestReply" name="reply" type="email" maxlength="254" autocomplete="off" required aria-describedby="cgMailTestReplyHint cgMailTestReplyError"><p id="cgMailTestReplyError" class="cg_input_error cg-mail-test-field-error" hidden></p><small id="cgMailTestReplyHint">Replies to this test email will go to this address.</small>'
			.'<label for="cgMailTestTo">Recipient <span class="cg-mail-test-required">(Required)</span></label><input id="cgMailTestTo" name="to" type="email" maxlength="254" autocomplete="email" required aria-describedby="cgMailTestToHint cgMailTestToError"><p id="cgMailTestToError" class="cg_input_error cg-mail-test-field-error" hidden></p><small id="cgMailTestToHint">Enter a mailbox you can access. Check your inbox and spam folder after sending.</small>'
			.'<p class="cg-mail-test-preview">Subject: Contest Gallery test email [test ID]<br>The message contains your website, a unique test ID and the sending time. No attachments or CC/BCC.</p>'
			.'<div class="cg-mail-test-actions"><button type="button" class="cg-mail-test-close">Close</button><button type="submit" class="cg-mail-test-send">Send test email</button></div>'
			.'</form>'
			.'<p class="cg-mail-test-status" role="status" aria-live="polite" hidden></p>'
			.'<section class="cg-mail-test-result" hidden><h3 class="cg-mail-test-result-title"></h3><p class="cg-mail-test-summary"></p>'
			.cg_mail_delivery_routing_note(true)
			.'<p>Check your inbox and spam folder for the test ID below.</p>'
			.'<ul class="cg-mail-test-tips"></ul><details open><summary>Technical details</summary><pre class="cg-mail-test-details"></pre></details>'
			.'<button type="button" class="cg-mail-test-copy">Copy diagnostic report</button><span class="cg-mail-test-copy-status" role="status"></span>'
			.'<label class="cg-mail-test-copy-fallback" hidden>Copy this report manually:<textarea readonly rows="10"></textarea></label></section>'
			.'</div>';
	}
}
