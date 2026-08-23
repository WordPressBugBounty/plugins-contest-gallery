<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_paid_actions_sandbox_link_container')){
	function cg_paid_actions_sandbox_link_container(){
		echo "<div id='cgPaidActionsSandboxLinkContainer' class='cg_backend_action_container cg_hide cg_height_auto cg_paid_actions_sandbox_link_modal' role='dialog' aria-modal='true' aria-labelledby='cgPaidActionsSandboxLinkTitle' aria-describedby='cgPaidActionsSandboxLinkDescription'>";
		echo "<button type='button' class='cg_message_close cg_paid_actions_sandbox_link_x' aria-label='Close sandbox test link dialog'></button>";
		echo "<div class='cg_paid_actions_sandbox_link_content'>";
		echo "<span class='cg_paid_actions_sandbox_link_icon'><span class='dashicons dashicons-admin-links' aria-hidden='true'></span></span>";
		echo "<div class='cg_paid_actions_sandbox_link_kicker'>VISITOR SANDBOX TEST</div>";
		echo "<h2 id='cgPaidActionsSandboxLinkTitle'>Test the credit checkout in incognito mode</h2>";
		echo "<p id='cgPaidActionsSandboxLinkDescription'>Copy this link and paste it into a private or incognito browser window. The visitor starts signed out and can test Google or PIN login before completing a real PayPal or Stripe sandbox payment.</p>";
		echo "<div class='cg_paid_actions_sandbox_link_facts'><span><strong>24 hours</strong><small>Link validity</small></span><span><strong>Sandbox only</strong><small>No live payment</small></span><span><strong>Persistent</strong><small>Test-order credits</small></span></div>";
		echo "<label class='cg_paid_actions_sandbox_link_field' for='cgPaidActionsSandboxLinkInput'><span>Sandbox test link</span><textarea id='cgPaidActionsSandboxLinkInput' rows='3' readonly spellcheck='false' wrap='soft'></textarea></label>";
		echo "<div class='cg_paid_actions_sandbox_link_actions'><button type='button' class='cg_paid_actions_sandbox_link_copy' disabled='disabled'><span class='dashicons dashicons-clipboard' aria-hidden='true'></span> Copy test link</button><a class='cg_paid_actions_sandbox_link_open' href='#' target='_blank' rel='noopener noreferrer' aria-disabled='true'><span class='dashicons dashicons-external' aria-hidden='true'></span> Open checkout</a><button type='button' class='cg_paid_actions_sandbox_link_create'><span class='dashicons dashicons-update' aria-hidden='true'></span><span class='cg_paid_actions_sandbox_link_create_text'>Create new test link</span></button></div>";
		echo "<div class='cg_paid_actions_sandbox_link_note'><span class='dashicons dashicons-info-outline' aria-hidden='true'></span><span>Each link belongs to the first test user who signs in. Create a new link for another account. Existing links keep working until they expire or their test user logs out.</span></div>";
		echo "<div class='cg_paid_actions_sandbox_link_status' role='status' aria-live='polite'></div>";
		echo "</div></div>";
	}
}

if(!function_exists('cg_paid_actions_ecommerce_status_pill')){
	function cg_paid_actions_ecommerce_status_pill($label,$value,$tone,$icon){
		echo "<div class='cg_paid_actions_setup_pill cg_paid_actions_setup_pill_".esc_attr($tone)."'>";
		echo "<span class='dashicons ".esc_attr($icon)."' aria-hidden='true'></span>";
		echo "<span><small>".esc_html($label)."</small><strong>".esc_html($value)."</strong></span>";
		echo "</div>";
	}
}

if(!function_exists('cg_paid_actions_gallery_card')){
	function cg_paid_actions_gallery_card($galleryId,$ecommerceOptions){
		$galleryId = absint($galleryId);
		if(!$galleryId || !current_user_can('manage_options')){return;}
		if(!function_exists('cg_paid_actions_gallery_is_supported') || !cg_paid_actions_gallery_is_supported($galleryId)){
			$galleryOverviewUrl = add_query_arg('page',cg_get_version().'/index.php',admin_url('admin.php'));
			echo "<section id='cgPaidActionsCard' class='cg_paid_actions_card cg_paid_actions_card_unsupported'>";
			echo "<div class='cg_paid_actions_card_header'><div class='cg_paid_actions_header_content'>";
			echo "<div class='cg_paid_actions_eyebrow'>MODERN GALLERY REQUIRED</div>";
			echo "<h2>Earning options require a newer gallery</h2>";
			echo "<p>Earning options are available for galleries created or copied with Contest Gallery 22.0.0 or later.</p>";
			echo "<p>Copy this gallery to create the current gallery URLs and make earning options available in the new gallery.</p>";
			echo "<a class='button button-primary' href='".esc_url($galleryOverviewUrl)."'><span class='dashicons dashicons-admin-page' aria-hidden='true'></span> Open gallery overview</a>";
			echo "</div></div></section>";
			return;
		}

		$settings = cg_paid_actions_get_settings($galleryId);
		$readiness = cg_paid_actions_get_ecommerce_readiness();
		$warnings = cg_paid_actions_get_shared_price_warnings($galleryId);
		$sharingStatus = cg_paid_actions_get_sharing_status($galleryId);
		$galleryHash = md5(wp_salt('auth').'---cngl1---'.$galleryId);
		$getProUrl = 'https://www.contest-gallery.com/pro-version/';
		$ecommerceOptionsForStatus = !empty($readiness['options']) ? $readiness['options'] : $ecommerceOptions;
		$currency = (is_object($ecommerceOptionsForStatus) && !empty($ecommerceOptionsForStatus->CurrencyShort)) ? $ecommerceOptionsForStatus->CurrencyShort : '';
		$priceDivider = (is_object($ecommerceOptionsForStatus) && isset($ecommerceOptionsForStatus->PriceDivider) && in_array($ecommerceOptionsForStatus->PriceDivider,array('.',','),true)) ? $ecommerceOptionsForStatus->PriceDivider : '.';
		$currencies = function_exists('cg_get_ecommerce_currencies_array_formatted_by_short_key') ? cg_get_ecommerce_currencies_array_formatted_by_short_key() : array();
		$currencyCharacter = ($currency!=='' && !empty($currencies[$currency])) ? $currencies[$currency] : $currency;
		$currencyCharacterForHeader = $currencyCharacter!=='' ? $currencyCharacter : '?';
		$currencyTitle = $currency!=='' ? 'Current ecommerce currency: '.$currency : 'Ecommerce currency is not configured';
		$checks = !empty($readiness['checks']) && is_array($readiness['checks']) ? $readiness['checks'] : array();
		$editEcommerceUrl = '?page='.cg_get_version().'/index.php&edit_options=true&option_id='.$galleryId.'&cg_edit_ecommerce=true';
		$types = array(
			'upload'=>array('title'=>'Pay for uploads','text'=>'Sell entries for this gallery. One credit covers one gallery entry, including its additional files.','icon'=>'dashicons-upload'),
			'vote'=>array('title'=>'Pay for voting','text'=>'Let visitors buy voting credits while all configured voting limits continue to apply.','icon'=>'dashicons-star-filled'),
			'comment'=>array('title'=>'Pay for commenting','text'=>'Turn meaningful gallery engagement into individually purchasable comment credits.','icon'=>'dashicons-admin-comments')
		);
		$activeActionLabels = array();
		$liveActionLabels = array();
		$previewActionLabels = array();
		$blockedActionLabels = array();
		foreach($types as $type=>$data){
			$modeProperty = ucfirst($type).'Mode';
			$mode = absint($settings->$modeProperty);
			$label = $type==='vote' ? 'votes' : $type.'s';
			if($mode){
				$activeActionLabels[] = $label;
			}
			if($mode===2 && cg_paid_actions_can_sell_action($type,$readiness)){
				$liveActionLabels[] = $label;
			}elseif($mode===2){
				$blockedActionLabels[] = $label;
			}elseif($mode===1){
				$previewActionLabels[] = $label;
			}
		}
		$hasActiveAction = !empty($activeActionLabels);
		$isFreeEdition = function_exists('cg_get_version') && cg_get_version()==='contest-gallery';
		$firstActiveAction = '';
		foreach($types as $type=>$data){
			$property = ucfirst($type).'Mode';
			if(!empty($settings->$property)){$firstActiveAction = $type;break;}
		}
		$modeTone = !empty($liveActionLabels) ? 'live' : (!empty($blockedActionLabels) ? 'warning' : 'preview');
		$modeEyebrow = $modeTone==='live' ? 'LIVE EARNING MODE' : ($modeTone==='warning' ? 'SALES NEED ATTENTION' : 'PRIVATE TEST MODE');
		$modeTitle = $modeTone==='live' ? 'Paid actions are live' : ($modeTone==='warning' ? 'Paid actions are configured' : 'Paid actions preview is active');
		if($modeTone==='live'){
			$modeText = 'This gallery currently sells '.implode(', ',$liveActionLabels).' as credits.';
			if(!empty($previewActionLabels)){$modeText .= ' Private preview remains available for '.implode(', ',$previewActionLabels).'.';}
		}elseif($modeTone==='warning'){
			$modeText = 'Complete the required Ecommerce or PRO setup for '.implode(', ',$blockedActionLabels).' credits.';
		}else{
			$modeText = 'The secure test checkout is available for '.implode(', ',$activeActionLabels).'.';
		}
		$modeHidden = $hasActiveAction ? '' : " hidden='hidden'";

		echo "<section id='cgPaidActionsCard' class='cg_paid_actions_card' data-gallery-id='".esc_attr($galleryId)."' data-gallery-hash='".esc_attr($galleryHash)."' data-price-divider='".esc_attr($priceDivider)."'>";
		echo "<div class='cg_paid_actions_card_header'>";
		echo "<div class='cg_paid_actions_header_content'>";
		echo "<div class='cg_paid_actions_eyebrow'><span>MONETIZE YOUR GALLERY</span>".($isFreeEdition ? "<span class='cg_paid_actions_free_preview_badge'>FREE PREVIEW</span>" : '')."</div><h2>Earn <span class='cg_paid_actions_currency_badge' title='".esc_attr($currencyTitle)."'>".esc_html($currencyCharacterForHeader)."</span> with uploads, votes and comments</h2><p>Set a gross unit price here. Checkout, taxes, currency, invoices and payment methods use your general Ecommerce options.".($isFreeEdition ? " <span class='cg_paid_actions_free_preview_copy'>Test the complete flow with simulated checkout. Live sales require PRO.</span>" : '')."</p>";
		echo "<div class='cg_paid_actions_header_controls'>";
		echo "<button type='button' class='cg_paid_actions_expand_button' aria-expanded='false' aria-controls='cgPaidActionsCardBody'><span class='dashicons dashicons-arrow-down-alt2 cg_paid_actions_header_chevron' aria-hidden='true'></span><span class='cg_paid_actions_expand_button_text'>Show earning options</span></button>";
		echo "<div class='cg_paid_actions_mode_banner cg_paid_actions_mode_banner_".esc_attr($modeTone)."'".$modeHidden."><span class='dashicons dashicons-money-alt' aria-hidden='true'></span><span><small>".esc_html($modeEyebrow)."</small><strong>".esc_html($modeTitle)."</strong><em>".esc_html($modeText)."</em></span></div>";
		echo "</div>";
		echo "<div class='cg_paid_actions_activation_notice' aria-live='polite' hidden='hidden'></div>";
		echo "</div>";
		echo "</div>";
		echo "<div id='cgPaidActionsCardBody' class='cg_paid_actions_card_body' hidden='hidden'>";

		echo "<div class='cg_paid_actions_tiles'>";
		foreach($types as $type=>$data){
			$modeProperty = ucfirst($type).'Mode';
			$priceProperty = ucfirst($type).'Price';
			$mode = absint($settings->$modeProperty);
			$checked = $mode ? " checked='checked'" : '';
			$previewDisabled = $mode ? '' : " disabled='disabled'";
			$previewAriaDisabled = $mode ? 'false' : 'true';
			$previewTitle = $mode ? '' : " title='Activate pay for ".esc_attr($type)."s to try it now.'";
			$priceLabelDisabledClass = $mode ? '' : ' cg_paid_action_price_label_disabled';
			$priceTitle = $mode ? '' : " title='Activate pay for ".esc_attr($type)."s to set a price.'";
			$status = $mode===2 ? (cg_paid_actions_can_sell_action($type,$readiness) ? 'Live' : 'Unavailable') : ($mode===1 ? 'Preview' : 'Off');
			$statusClass = strtolower($status);
			echo "<article class='cg_paid_action_tile cg_paid_action_tile_".esc_attr($type)."' data-action-type='".esc_attr($type)."'>";
			echo "<div class='cg_paid_action_tile_top'><span class='dashicons ".esc_attr($data['icon'])."'></span><span class='cg_paid_action_status cg_paid_action_status_".esc_attr($statusClass)."'>".esc_html($status)."</span></div>";
			echo "<h3>".esc_html($data['title'])."</h3><p>".esc_html($data['text'])."</p>";
			echo "<label class='cg_paid_action_switch'><input type='checkbox' class='cg_paid_action_enabled'".$checked."><span class='cg_paid_action_switch_control'></span><span class='cg_paid_action_switch_copy'><strong>Activate pay for ".esc_html($type)."s</strong><small>Activate and try it instantly.</small></span></label>";
			echo "<label class='cg_paid_action_price_label".esc_attr($priceLabelDisabledClass)."' aria-disabled='".esc_attr($previewAriaDisabled)."'><span>Gross price per ".esc_html($type)."</span><span class='cg_paid_action_price_field'><input type='text' inputmode='decimal' maxlength='20' class='cg_paid_action_price cg_currency_input' value='".esc_attr(number_format((float)$settings->$priceProperty,2,$priceDivider,''))."' aria-disabled='".esc_attr($previewAriaDisabled)."'".$previewDisabled.$priceTitle."><em>".esc_html($currency!=='' ? $currency : '—')."</em></span></label>";
			echo "<button type='button' class='button cg_paid_action_preview' aria-disabled='".esc_attr($previewAriaDisabled)."'".$previewDisabled.$previewTitle."><span class='dashicons dashicons-visibility'></span> Try it now</button>";
			if(empty($readiness['proAvailable']) && cg_paid_actions_action_requires_pro($type)){
				echo "<div class='cg_paid_action_license_note'><span class='cg_paid_actions_pro_badge'><span class='dashicons dashicons-lock' aria-hidden='true'></span> PRO ONLY</span><strong>Live sales require Contest Gallery PRO</strong><span>This feature is limited to private testing until PRO is active.</span></div>";
			}
			if(!empty($warnings[$type])){echo "<div class='cg_paid_action_price_warning'>".esc_html($warnings[$type])."</div>";}
			echo "</article>";
		}
		echo "</div>";

		if(!empty($readiness['paymentReady']) && !empty($readiness['proAvailable'])){
			$setupTone = 'ready';
			$setupIcon = 'dashicons-yes-alt';
			$setupTitle = 'Ready to sell';
			$setupText = 'Your checkout can accept live credit purchases.';
		}elseif(!empty($readiness['paymentReady'])){
			$setupTone = 'pro';
			$setupIcon = 'dashicons-lock';
			$setupTitle = 'PRO required to start selling';
			$setupText = 'Real paid sales for uploads, votes and comments are available only with an active Contest Gallery PRO license. Without PRO, this area is a private test environment.';
		}else{
			$setupTone = 'warning';
			$setupIcon = 'dashicons-warning';
			$setupTitle = 'Complete your Ecommerce setup';
			$setupText = 'Resolve the items marked Needs setup before live sales can start.';
		}

		$currencyValue = $currency!=='' ? ($currencyCharacter===$currency ? $currency : trim($currencyCharacter.' '.$currency)) : 'Select currency';
		$taxConfigured = !empty($checks['tax']['state']) && $checks['tax']['state']==='configured';
		$taxValue = $taxConfigured && isset($checks['tax']['value']) ? $checks['tax']['value'] : '0.00';
		$taxValue = rtrim(rtrim((string)$taxValue,'0'),'.');
		if($taxValue===''){$taxValue = '0';}

		$paypalState = !empty($checks['paypal']['state']) ? $checks['paypal']['state'] : 'unavailable';
		$paypalValue = $paypalState==='ready' ? 'Live ready' : ($paypalState==='needs_keys' ? 'Live keys missing' : ($paypalState==='off' ? 'Optional · Off' : 'Needs setup'));
		$paypalTone = $paypalState==='ready' ? 'ready' : ($paypalState==='off' ? 'muted' : 'warning');
		$paypalIcon = $paypalState==='ready' ? 'dashicons-yes-alt' : ($paypalState==='off' ? 'dashicons-minus' : 'dashicons-warning');

		$stripeState = !empty($checks['stripe']['state']) ? $checks['stripe']['state'] : 'unavailable';
		$stripeValue = $stripeState==='ready' ? 'Live ready' : ($stripeState==='needs_keys' ? 'Live keys missing' : ($stripeState==='needs_https' ? 'HTTPS required' : ($stripeState==='off' ? 'Optional · Off' : 'Needs setup')));
		$stripeTone = $stripeState==='ready' ? 'ready' : ($stripeState==='off' ? 'muted' : 'warning');
		$stripeIcon = $stripeState==='ready' ? 'dashicons-yes-alt' : ($stripeState==='off' ? 'dashicons-minus' : 'dashicons-warning');
		$sandboxMethods = array();
		if(!empty($readiness['paypalSandboxReady'])){$sandboxMethods[] = 'PayPal';}
		if(!empty($readiness['stripeSandboxReady'])){$sandboxMethods[] = 'Stripe';}
		$sandboxValue = !empty($sandboxMethods) ? implode(' + ',$sandboxMethods).' ready' : 'Enable provider test mode';
		$sandboxTone = !empty($readiness['sandboxPaymentReady']) ? 'ready' : 'warning';
		$sandboxIcon = !empty($readiness['sandboxPaymentReady']) ? 'dashicons-yes-alt' : 'dashicons-warning';

		$orderMailState = !empty($checks['orderMail']['state']) ? $checks['orderMail']['state'] : 'unavailable';
		$orderMailValue = $orderMailState==='ready' ? 'Active' : ($orderMailState==='off' ? 'Optional · Off' : 'Needs setup');
		$orderMailTone = $orderMailState==='ready' ? 'ready' : ($orderMailState==='off' ? 'muted' : 'warning');
		$orderMailIcon = $orderMailState==='ready' ? 'dashicons-yes-alt' : ($orderMailState==='off' ? 'dashicons-minus' : 'dashicons-warning');

		$invoiceState = !empty($checks['invoice']['state']) ? $checks['invoice']['state'] : 'unavailable';
		$invoiceValue = $invoiceState==='email' ? 'Email attachment' : ($invoiceState==='download' ? 'Download only' : ($invoiceState==='off' ? 'Optional · Off' : 'Needs setup'));
		$invoiceTone = ($invoiceState==='email' || $invoiceState==='download') ? 'ready' : ($invoiceState==='off' ? 'muted' : 'warning');
		$invoiceIcon = ($invoiceState==='email' || $invoiceState==='download') ? 'dashicons-yes-alt' : ($invoiceState==='off' ? 'dashicons-minus' : 'dashicons-warning');

		echo "<div class='cg_paid_actions_setup cg_paid_actions_setup_".esc_attr($setupTone)."'>";
		echo "<div class='cg_paid_actions_setup_header'>";
		echo "<div class='cg_paid_actions_setup_intro'><span class='dashicons ".esc_attr($setupIcon)." cg_paid_actions_setup_main_icon' aria-hidden='true'></span><div><div class='cg_paid_actions_setup_eyebrow_row'><div class='cg_paid_actions_setup_eyebrow'>SELLING SETUP</div>".(!empty($readiness['proAvailable']) ? '' : "<span class='cg_paid_actions_pro_badge'><span class='dashicons dashicons-lock' aria-hidden='true'></span> PRO ONLY</span>")."</div><h3>".esc_html($setupTitle)."</h3><p>".esc_html($setupText)."</p></div></div>";
		echo "<div class='cg_paid_actions_setup_actions'>";
		if(empty($readiness['proAvailable'])){
			echo "<a class='cg_paid_actions_get_pro_link' href='".esc_url($getProUrl)."' target='_blank' rel='noopener noreferrer'><span class='dashicons dashicons-star-filled' aria-hidden='true'></span> Get PRO</a>";
		}
		if(!empty($readiness['proAvailable'])){
			$sandboxReady = !empty($readiness['sandboxPaymentReady']) && $firstActiveAction!=='';
			$sandboxDisabled = $sandboxReady ? '' : " disabled='disabled' aria-disabled='true'";
			$sandboxTitle = $firstActiveAction==='' ? 'Activate at least one paid action first.' : (!empty($readiness['sandboxPaymentReady']) ? 'Create a visitor link for a real PayPal or Stripe sandbox checkout.' : 'Enable sandbox testing and add sandbox credentials in Ecommerce options first.');
			$sandboxRequirementHidden = $firstActiveAction==='' ? '' : " hidden='hidden'";
			echo "<button type='button' class='cg_paid_actions_sandbox_checkout' data-action-type='".esc_attr($firstActiveAction)."' title='".esc_attr($sandboxTitle)."'".$sandboxDisabled."><span class='dashicons dashicons-admin-links' aria-hidden='true'></span> Create sandbox test link</button>";
			echo "<span class='cg_paid_actions_sandbox_requirement' role='status' aria-live='polite'".$sandboxRequirementHidden."><span class='dashicons dashicons-info-outline' aria-hidden='true'></span><span>Activate at least one paid action first.</span></span>";
		}
		echo "<a class='cg_paid_actions_ecommerce_link' href='".esc_url($editEcommerceUrl)."'><span class='dashicons dashicons-admin-generic'></span> Edit Ecommerce options</a>";
		echo "</div>";
		echo "</div>";
		echo "<div class='cg_paid_actions_setup_pills'>";
		cg_paid_actions_ecommerce_status_pill('Currency',$currencyValue,!empty($readiness['currencyReady']) ? 'ready' : 'warning',!empty($readiness['currencyReady']) ? 'dashicons-yes-alt' : 'dashicons-warning');
		cg_paid_actions_ecommerce_status_pill('Tax',$taxConfigured ? $taxValue.'% configured' : 'Needs setup',$taxConfigured ? 'ready' : 'warning',$taxConfigured ? 'dashicons-yes-alt' : 'dashicons-warning');
		cg_paid_actions_ecommerce_status_pill('PayPal',$paypalValue,$paypalTone,$paypalIcon);
		cg_paid_actions_ecommerce_status_pill('Stripe',$stripeValue,$stripeTone,$stripeIcon);
		if(!empty($readiness['proAvailable'])){cg_paid_actions_ecommerce_status_pill('Sandbox checkout',$sandboxValue,$sandboxTone,$sandboxIcon);}
		cg_paid_actions_ecommerce_status_pill('Order emails',$orderMailValue,$orderMailTone,$orderMailIcon);
		cg_paid_actions_ecommerce_status_pill('Invoices',$invoiceValue,$invoiceTone,$invoiceIcon);
		echo "</div>";
		if(empty($readiness['paypalReady']) && empty($readiness['stripeReady'])){
			echo "<div class='cg_paid_actions_setup_hint'><span class='dashicons dashicons-warning' aria-hidden='true'></span> Set up PayPal or Stripe to start selling.</div>";
		}
		echo "<div class='cg_paid_actions_setup_global_note'>These Ecommerce settings are general and apply to all galleries.</div>";
		echo "</div>";

		$shareChecked = !empty($settings->ShareCredits) ? " checked='checked'" : '';
		$eligibleGalleryCount = !empty($sharingStatus['eligibleGalleryCount']) ? absint($sharingStatus['eligibleGalleryCount']) : 0;
		if(empty($settings->ShareCredits)){
			$shareStatusTone = 'off';
			$shareStatusIcon = 'dashicons-info-outline';
			$shareStatusTitle = 'New credits bought here stay local';
			$shareStatusText = 'Enable this option to make future purchases from this gallery available automatically wherever the matching paid action is active.';
		}elseif(!$eligibleGalleryCount){
			$shareStatusTone = 'ready';
			$shareStatusIcon = 'dashicons-yes-alt';
			$shareStatusTitle = 'Global credits are enabled';
			$shareStatusText = 'No other gallery currently has a matching paid action. Credits will work there automatically when one is activated.';
		}else{
			$shareStatusTone = 'ready';
			$shareStatusIcon = 'dashicons-yes-alt';
			$shareStatusTitle = 'Global credits work in '.$eligibleGalleryCount.' other paid-action '.($eligibleGalleryCount===1 ? 'gallery' : 'galleries');
			$shareStatusText = 'No additional sharing setting is required in those galleries.';
		}
		echo "<div class='cg_paid_actions_footer'>";
		echo "<div class='cg_paid_actions_share_block'>";
		echo "<label class='cg_paid_actions_share'><input type='checkbox' class='cg_paid_actions_share_input'".$shareChecked."><span><strong>Let newly purchased credits work in all other paid-action galleries</strong><span class='cg_paid_actions_share_rule'><b>Enable it once in this gallery.</b> Other galleries do not need to enable sharing.</span><small>Uploads, votes and comments remain separate. Credits work only where the matching paid action is active. Existing local credits stay local. Already shared credits stay shared.</small></span></label>";
		echo "<div class='cg_paid_actions_share_status cg_paid_actions_share_status_".esc_attr($shareStatusTone)."' data-eligible-gallery-count='".esc_attr($eligibleGalleryCount)."' aria-live='polite'><span class='dashicons ".esc_attr($shareStatusIcon)."' aria-hidden='true'></span><span><strong>".esc_html($shareStatusTitle)."</strong><small>".esc_html($shareStatusText)."</small></span></div>";
		echo "</div>";
		echo "<div class='cg_paid_actions_save_state' aria-live='polite'></div>";
		echo "</div></div>";
		if(!empty($readiness['proAvailable'])){cg_paid_actions_sandbox_link_container();}
		echo "</section>";
	}
}
