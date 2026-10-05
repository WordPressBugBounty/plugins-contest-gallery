<?php

if(!function_exists('cgl1_domain_error_text')){
	function cgl1_domain_error_text(){
		return 'Email delivery depends on your WordPress mail setup. If emails do not arrive, check your sender address and SMTP configuration. For recipients on your website’s domain, also ask your hosting provider to check mail routing.';
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
		return '<span class="cg_mail_delivery_note">'.esc_html(cgl1_domain_error_text()).'</span>';
	}
}

if(!function_exists('cgl1_domain_error_test_text_old')){
	function cgl1_domain_error_test_text_old($cgYourDomainName){
		return cgl1_domain_error_test_text();
	}
}


?>
