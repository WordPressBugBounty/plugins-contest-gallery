<?php

if(!function_exists('cgl1_domain_error_text')){
	function cgl1_domain_error_text(){
		return '⚠️ Generally, you can’t send emails to your own domain address, as most mail servers block same-domain messages to prevent mail loops. Using an SMTP configuration with an external provider like Gmail can resolve this.';
	}
}

if(!function_exists('cgl1_reply_name_same_as_form_name')){
	function cgl1_reply_name_same_as_form_name(){
		return '💡 The "Reply name" can be the same as the "From name"';
	}
}

if(!function_exists('cgl1_reply_mail_same_as_form_mail')){
	function cgl1_reply_mail_same_as_form_mail(){
		return '💡 The "Reply mail" can be the same as the "From mail"';
	}
}

if(!function_exists('cgl1_domain_error_test_text')){
	function cgl1_domain_error_test_text(){
        $domain = parse_url( get_site_url(), PHP_URL_HOST );
		return '<span class="cg_domain_error_test_text">⚠️ For testing purposes, you can’t send emails to your own domain address,<br>as most mail servers block same-domain messages to prevent mail loops.<br>For example, sending to <span class="cg_domain_error_url">contact@'.$domain.'</span> won’t work.<br>Using an external SMTP provider like Gmail can fix this.</';
	}
}

if(!function_exists('cgl1_domain_error_test_text_old')){
	function cgl1_domain_error_test_text_old($cgYourDomainName){
		return '<span class="cg_color_red">NOTE:</span> relating testing - e-mail where is send to should not contain '.$cgYourDomainName.'.<br>Many servers can not send to own domain.';
	}
}


?>