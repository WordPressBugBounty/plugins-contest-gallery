<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_paid_actions_get_stripe_return_request_context')){
	function cg_paid_actions_get_stripe_return_request_context(){
		static $context = null;
		static $checked = false;
		if($checked){return $context;}
		$checked = true;

		$returnValue = isset($_GET['cg_paid_action_return']) ? sanitize_key(wp_unslash($_GET['cg_paid_action_return'])) : '';
		$paymentIntent = isset($_GET['payment_intent']) ? sanitize_text_field(wp_unslash($_GET['payment_intent'])) : '';
		if($returnValue!=='1' || strlen($paymentIntent)>255 || !preg_match('/^pi_[A-Za-z0-9]+$/',$paymentIntent)){
			$context = false;
			return $context;
		}

		$mode = isset($_GET['cg_paid_action_return_mode']) ? sanitize_key(wp_unslash($_GET['cg_paid_action_return_mode'])) : '';
		$theme = isset($_GET['cg_paid_action_return_theme']) ? sanitize_key(wp_unslash($_GET['cg_paid_action_return_theme'])) : '';
		$context = array(
			'mode'=>$mode==='sandbox' ? 'sandbox' : 'live',
			'theme'=>$theme==='dark' ? 'dark' : 'bright'
		);
		return $context;
	}
}

if(!function_exists('cg_paid_actions_stripe_return_body_classes')){
	function cg_paid_actions_stripe_return_body_classes($classes){
		if(cg_paid_actions_get_stripe_return_request_context()){
			$classes[] = 'cg-paid-actions-return-pending';
		}
		return $classes;
	}
}

if(!function_exists('cg_paid_actions_render_stripe_return_critical_head')){
	function cg_paid_actions_render_stripe_return_critical_head(){
		static $rendered = false;
		$context = cg_paid_actions_get_stripe_return_request_context();
		if($rendered || empty($context)){return;}
		$rendered = true;
		$background = $context['theme']==='dark' ? '#0b0d0c' : '#f7faf8';
		echo "<script>document.documentElement.className += ' cg-paid-actions-return-document-pending';</script>";
		echo "<style id='cgPaidActionsReturnCriticalStyle'>html.cg-paid-actions-return-document-pending body{overflow:hidden!important;background:".esc_attr($background)."!important}html.cg-paid-actions-return-document-pending body>*:not(.cg-paid-actions-return-screen){visibility:hidden!important}html.cg-paid-actions-return-document-pending .cg-paid-actions-return-screen{visibility:visible!important}</style>";
	}
}

if(!function_exists('cg_paid_actions_render_stripe_return_processing_screen')){
	function cg_paid_actions_render_stripe_return_processing_screen(){
		static $rendered = false;
		$context = cg_paid_actions_get_stripe_return_request_context();
		if($rendered || empty($context)){return;}
		$rendered = true;

		$isSandbox = $context['mode']==='sandbox';
		$styleClass = $context['theme']==='dark' ? 'cg_fe_controls_style_black' : 'cg_fe_controls_style_white';
		$kicker = $isSandbox ? cg_paid_actions_t('SandboxPayment') : cg_paid_actions_t('SecurePayment');
		$title = $isSandbox ? cg_paid_actions_t('FinalizingSandboxCredits') : cg_paid_actions_t('FinalizingCredits');
		$message = $isSandbox
			? cg_paid_actions_t('FinalizingSandboxDescription')
			: cg_paid_actions_t('FinalizingDescription');

		echo "<div id='cgPaidActionsReturnProcessing' class='cg-paid-actions-return-screen ".esc_attr($styleClass)."' data-cg-return-mode='".esc_attr($context['mode'])."' role='status' aria-live='polite' aria-busy='true' aria-labelledby='cgPaidActionsReturnProcessingTitle'>";
		echo "<div class='cg-paid-actions-return-card'>";
		echo "<span class='cg-paid-actions-return-spinner' aria-hidden='true'></span>";
		echo "<div class='cg-paid-actions-modal-kicker'>".esc_html($kicker)."</div>";
		echo "<h1 id='cgPaidActionsReturnProcessingTitle'>".esc_html($title)."</h1>";
		echo "<p class='cg-paid-actions-return-message'>".esc_html($message)."</p>";
		echo "<div class='cg-paid-actions-return-failure-actions cg_hide'>";
		echo "<p class='cg-paid-actions-return-safety'>".esc_html(cg_paid_actions_t('DoNotStartPayment'))."</p>";
		echo "<button type='button' class='cg-paid-actions-return-retry'>".esc_html(cg_paid_actions_t('TryFinalizingAgain'))."</button>";
		echo "<a class='cg-paid-actions-return-gallery' href='#'>".esc_html(cg_paid_actions_t('ReturnToGallery'))."</a>";
		echo "</div></div></div>";
		echo "<script>(function(){var screen=document.getElementById('cgPaidActionsReturnProcessing');if(document.body&&screen&&screen.parentNode!==document.body){document.body.appendChild(screen);}if(document.body){document.body.classList.add('cg-paid-actions-return-pending');}})();</script>";
	}
}

add_filter('body_class','cg_paid_actions_stripe_return_body_classes');
add_action('wp_head','cg_paid_actions_render_stripe_return_critical_head',0);
add_action('wp_body_open','cg_paid_actions_render_stripe_return_processing_screen',0);
add_action('wp_footer','cg_paid_actions_render_stripe_return_processing_screen',0);

if(!function_exists('cg_paid_actions_is_action_active_for_request')){
	function cg_paid_actions_is_action_active_for_request($galleryId,$actionType){
		$galleryId = absint($galleryId);
		$actionType = sanitize_key($actionType);
		if(!$galleryId || !in_array($actionType,array('upload','vote','comment'),true) || !function_exists('cg_paid_actions_get_settings')){return false;}
		if(!function_exists('cg_paid_actions_gallery_is_supported') || !cg_paid_actions_gallery_is_supported($galleryId)){return false;}

		$settings = cg_paid_actions_get_settings($galleryId);
		$property = ucfirst($actionType).'Mode';
		$mode = isset($settings->$property) ? absint($settings->$property) : 0;
		if($mode===2){return true;}
		if($mode<1 || !function_exists('cg_paid_actions_get_preview_context')){return false;}

		return !empty(cg_paid_actions_get_preview_context($galleryId,true));
	}
}

if(!function_exists('cg_paid_actions_render_frontend_bar_shell')){
	function cg_paid_actions_render_frontend_bar_shell($galleryId,$galleryInstanceId,$visualOptions = array(),$allowedTypes = array()){
		$galleryId = absint($galleryId);
		$safeInstanceId = preg_replace('/[^A-Za-z0-9_-]/','',(string)$galleryInstanceId);
		$allowedTypes = is_array($allowedTypes) ? array_values(array_intersect(array('upload','vote','comment'),$allowedTypes)) : array();
		cg_paid_actions_render_stripe_return_processing_screen();
		if(!$galleryId || $safeInstanceId==='' || !function_exists('cg_paid_actions_get_settings')){return;}
		if(!function_exists('cg_paid_actions_gallery_is_supported') || !cg_paid_actions_gallery_is_supported($galleryId)){return;}

		$settings = cg_paid_actions_get_settings($galleryId);
		$modes = array(
			'upload'=>absint($settings->UploadMode),
			'vote'=>absint($settings->VoteMode),
			'comment'=>absint($settings->CommentMode)
		);
		$configuredTypes = array();
		foreach($modes as $type=>$mode){
			if(!empty($allowedTypes) && !in_array($type,$allowedTypes,true)){continue;}
			if($mode>0){$configuredTypes[] = $type;}
		}
		if(empty($configuredTypes)){return;}

		$preview = function_exists('cg_paid_actions_get_preview_context') ? cg_paid_actions_get_preview_context($galleryId,true) : false;
		$isSandbox = !empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox';
		$isPreview = !empty($preview) && !$isSandbox;
		$hasCheckoutContext = !empty($preview);
		$initialTypes = array();
		foreach($configuredTypes as $type){
			if($hasCheckoutContext || $modes[$type]===2){$initialTypes[] = $type;}
		}

		$labels = array('upload'=>cg_paid_actions_t('Uploads'),'vote'=>cg_paid_actions_t('Votes'),'comment'=>cg_paid_actions_t('Comments'));
		$voteIconClass = (!empty($visualOptions['FeVotingIconType']) && $visualOptions['FeVotingIconType']==='heart') ? 'cg-paid-actions-balance-icon-heart' : 'cg-paid-actions-balance-icon-star';
		$iconClasses = array(
			'upload'=>'cg-paid-actions-balance-icon-upload',
			'vote'=>'cg-paid-actions-balance-icon-vote '.$voteIconClass,
			'comment'=>'cg-paid-actions-balance-icon-comment'
		);
		$isUploadOnly = count($allowedTypes)===1 && $allowedTypes[0]==='upload';
		$barClasses = 'cg-paid-actions-bar cg-paid-actions-bar-shell cg-paid-actions-bar-loading'.($isUploadOnly ? ' cg-paid-actions-bar-upload-only' : '').($isPreview ? ' cg-paid-actions-bar-preview' : '').($isSandbox ? ' cg-paid-actions-bar-sandbox' : '');
		$barHidden = empty($initialTypes) ? " hidden='hidden'" : '';
		$title = $isPreview ? cg_paid_actions_t('PrivatePaymentPreview') : ($isSandbox ? cg_paid_actions_t('SandboxCheckoutTest') : ($isUploadOnly ? cg_paid_actions_t('YourUploadCredits') : cg_paid_actions_t('YourGalleryCredits')));
		$subtitle = $isPreview ? cg_paid_actions_t('PreviewBarDescription') : ($isSandbox ? cg_paid_actions_t('SandboxBarDescription') : ($isUploadOnly ? cg_paid_actions_t('UploadCreditUsage') : cg_paid_actions_t('LiveBarDescription')));
		$buttonText = $isPreview ? cg_paid_actions_t('AddTestCredits') : ($isSandbox ? cg_paid_actions_t('SandboxCheckout') : cg_paid_actions_t('GetCredits'));
		$buttonLabel = $isPreview ? cg_paid_actions_t('AddTestCredits') : ($isSandbox ? cg_paid_actions_t('BuyCreditsInSandbox') : cg_paid_actions_t('BuyGalleryCredits'));

		echo "<div id='cgPaidActionsBar".esc_attr($safeInstanceId)."' class='".esc_attr($barClasses)."' data-cg-gid='".esc_attr($galleryInstanceId)."' data-cg-real-gid='".esc_attr($galleryId)."' data-cg-action-scope='".esc_attr(implode(',',$allowedTypes))."' data-cg-server-preview='".esc_attr($hasCheckoutContext ? 1 : 0)."' data-cg-checkout-mode='".esc_attr($isSandbox ? 'sandbox' : ($isPreview ? 'simulation' : ''))."' data-cg-share-credits='".esc_attr(!empty($settings->ShareCredits) ? 1 : 0)."' data-cg-upload-mode='".esc_attr($modes['upload'])."' data-cg-vote-mode='".esc_attr($modes['vote'])."' data-cg-comment-mode='".esc_attr($modes['comment'])."' aria-busy='true'".$barHidden.">";
		echo "<div class='cg-paid-actions-bar-title'><strong>".esc_html($title)."</strong><span>".esc_html($subtitle)."</span></div>";
		echo "<div class='cg-paid-actions-balances'>";
		foreach($configuredTypes as $type){
			$typeHidden = in_array($type,$initialTypes,true) ? '' : " hidden='hidden'";
			$usedHidden = $isPreview ? '' : " hidden='hidden'";
			echo "<button type='button' class='cg-paid-actions-balance' data-action-type='".esc_attr($type)."' disabled='disabled'".$typeHidden.">";
			echo "<span class='cg-paid-actions-balance-icon ".esc_attr($iconClasses[$type])."' aria-hidden='true'></span>";
			echo "<span><b class='cg-paid-actions-balance-number' aria-label='".esc_attr(cg_paid_actions_t('Loading'))."'>0</b><small>".esc_html($labels[$type])."</small><em class='cg-paid-actions-balance-used'".$usedHidden.">".esc_html(cg_paid_actions_t('UsedHere',array('count'=>0)))."</em></span>";
			echo "</button>";
		}
		echo "</div>";
		echo "<button type='button' class='cg-paid-actions-buy' aria-label='".esc_attr($buttonLabel)."' disabled='disabled'><span>+</span> ".esc_html($buttonText)."</button>";
		if(!$isUploadOnly){
			echo "<button type='button' class='cg-paid-actions-reset' disabled='disabled'".($isPreview ? '' : " hidden='hidden'").">".esc_html(cg_paid_actions_t('ResetTestActions'))."</button>";
		}
		echo "</div>";
	}
}
