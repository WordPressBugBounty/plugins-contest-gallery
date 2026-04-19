<?php
if(!function_exists('cg_ecommerce_has_tax_to_show')) {
    function cg_ecommerce_has_tax_to_show($LogForDatabase){
        $itemHasTax = false;
        $itemHasShipping = false;
        $TaxPercentageDefault = $LogForDatabase['TaxPercentageDefault'];
        $hasTaxToShow = true;
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){
            $TaxPercentage = 0;
            if(!empty($item['tax_percentage'])){
                $TaxPercentage = round(floatval($item['tax_percentage']),2);
            }
            if($TaxPercentage){
                $itemHasTax = true;
            }
            if(!empty($item['IsShipping'])){
                $itemHasShipping = true;
            }
        }
        if($itemHasTax || ($itemHasShipping && $TaxPercentageDefault)){
            $hasTaxToShow = true;
        }
        return $hasTaxToShow;
    }
}
if(!function_exists('cg_ecommerce_check_item_has_shipping')) {
    function cg_ecommerce_check_item_has_shipping($LogForDatabase){
        $itemHasShipping = false;
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){
            if(!empty($item['IsShipping'])){
                $itemHasShipping = true;
            }
        }
        return $itemHasShipping;
    }
}
if(!function_exists('cg_ecommerce_get_price_total_net_items')) {
    function cg_ecommerce_get_price_total_net_items($LogForDatabase){
        $PriceTotalNetItems = 0;
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){
            $PriceUnitNet = round(floatval($item['unit_amount']['value']),2);
            $PriceTotalNet = $PriceUnitNet*intval($item['quantity']);
            $PriceTotalNetItems = $PriceTotalNetItems+$PriceTotalNet;
        }
        return $PriceTotalNetItems;
    }
}


if(!function_exists('cg_ecommerce_check_item_has_default_shipping')) {
    function cg_ecommerce_check_item_has_default_shipping($LogForDatabase){
        $itemHasDefaultShipping = 0;
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){
            if(!empty($item['IsShipping'])){
                if(!empty($item['alternative_shipping_amount_value_gross'])){
                }else{
                    $itemHasDefaultShipping = true;
                }
            }
        }
        return $itemHasDefaultShipping;
    }
}

if(!function_exists('cg_ecommerce_format_money_value')) {
	function cg_ecommerce_format_money_value($value){
		return number_format(round(floatval($value),2),2,'.','');
	}
}

if(!function_exists('cg_ecommerce_get_reload_checkout_error')) {
	function cg_ecommerce_get_reload_checkout_error(){
		return 'The checkout data changed. Please reload the checkout and try again.';
	}
}

if(!function_exists('cg_ecommerce_get_stripe_reload_checkout_error')) {
	function cg_ecommerce_get_stripe_reload_checkout_error(){
		return cg_ecommerce_get_reload_checkout_error();
	}
}

if(!function_exists('cg_ecommerce_get_authoritative_ecommerce_entry_data')) {
	function cg_ecommerce_get_authoritative_ecommerce_entry_data($ecommerceEntryRow){

		$ecommerceEntryData = json_decode(json_encode($ecommerceEntryRow),true);

		$ecommerceEntryData['WpUploadFilesPosts'] = !empty($ecommerceEntryData['WpUploadFilesPosts']) ? unserialize($ecommerceEntryData['WpUploadFilesPosts']) : '';
		$ecommerceEntryData['WpUploadFilesPostMeta'] = !empty($ecommerceEntryData['WpUploadFilesPostMeta']) ? unserialize($ecommerceEntryData['WpUploadFilesPostMeta']) : '';
		$ecommerceEntryData['WpUploadFilesForSale'] = !empty($ecommerceEntryData['WpUploadFilesForSale']) ? unserialize($ecommerceEntryData['WpUploadFilesForSale']) : '';
		$ecommerceEntryData['WatermarkSettings'] = !empty($ecommerceEntryData['WatermarkSettings']) ? unserialize($ecommerceEntryData['WatermarkSettings']) : '';
		$ecommerceEntryData['AllUploadsUsedText'] = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerceEntryData['AllUploadsUsedText']);

		return $ecommerceEntryData;

	}
}

if(!function_exists('cg_ecommerce_build_authoritative_checkout_cart')) {
	function cg_ecommerce_build_authoritative_checkout_cart($purchaseUnits, $ecommerceOptions){

		global $wpdb;
		$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

		$return = [
			'success' => false,
			'message' => cg_ecommerce_get_reload_checkout_error()
		];

		if(empty($purchaseUnits[0]['items']) || !is_array($purchaseUnits[0]['items'])){
			return $return;
		}

		$currencyShort = !empty($ecommerceOptions->CurrencyShort) ? sanitize_text_field($ecommerceOptions->CurrencyShort) : '';
		if(empty($currencyShort)){
			return $return;
		}

		$defaultShippingGross = round(floatval($ecommerceOptions->ShippingGross),2);
		$defaultTax = round(floatval($ecommerceOptions->TaxPercentageDefault),2);

		$entryIds = [];
		foreach ($purchaseUnits[0]['items'] as $item){
			if(empty($item['EcommerceEntryID'])){
				return $return;
			}
			$entryIds[] = absint($item['EcommerceEntryID']);
		}

		$entryIds = array_values(array_filter(array_unique($entryIds)));
		if(empty($entryIds)){
			return $return;
		}

		$collectedIds = implode(',', $entryIds);
		$ecommerceEntryRows = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_entries WHERE id IN ($collectedIds)");

		if(count($ecommerceEntryRows) !== count($entryIds)){
			return $return;
		}

		$ecommerceEntryRowsById = [];
		foreach ($ecommerceEntryRows as $ecommerceEntryRow){
			$ecommerceEntryRowsById[intval($ecommerceEntryRow->id)] = $ecommerceEntryRow;
		}

		$itemHasDefaultShipping = false;
		$priceTotalGrossItems = 0;
		$taxValueTotalItems = 0;
		$alternativeShippingTotal = 0;
		$alternativeShippingTotalTaxValue = 0;
		$normalizedItems = [];
		$cartSignatureItems = [];

		foreach ($purchaseUnits[0]['items'] as $key => $item){

			if(
				!isset($item['EcommerceEntryID']) ||
				!isset($item['realGid']) ||
				!isset($item['realId']) ||
				!isset($item['quantity'])
			){
				return $return;
			}

			$ecommerceEntryId = absint($item['EcommerceEntryID']);
			$realGid = absint($item['realGid']);
			$realId = absint($item['realId']);
			$quantity = absint($item['quantity']);

			if(empty($quantity) || empty($ecommerceEntryRowsById[$ecommerceEntryId])){
				return $return;
			}

			$ecommerceEntryRow = $ecommerceEntryRowsById[$ecommerceEntryId];
			$authoritativeGalleryId = intval($ecommerceEntryRow->GalleryID);
			$authoritativeRealId = intval($ecommerceEntryRow->pid);

			if($authoritativeGalleryId !== $realGid || $authoritativeRealId !== $realId){
				return $return;
			}

			$saleAmountMin = max(1, absint($ecommerceEntryRow->SaleAmountMin));
			$saleAmountMax = absint($ecommerceEntryRow->SaleAmountMax);
			if($saleAmountMax < $saleAmountMin){
				$saleAmountMax = $saleAmountMin;
			}

			if((!empty($ecommerceEntryRow->IsDownload) || !empty($ecommerceEntryRow->IsService)) && $quantity !== 1){
				return $return;
			}

			if($quantity < $saleAmountMin || $quantity > $saleAmountMax){
				return $return;
			}

			$priceGross = round(floatval($ecommerceEntryRow->Price),2);
			$taxPercentage = round(floatval($ecommerceEntryRow->TaxPercentage),2);
			$taxValueUnit = 0;
			if($taxPercentage){
				$taxValueUnit = round($priceGross / 100 * $taxPercentage,2);
			}
			$priceUnitNet = round($priceGross - $taxValueUnit,2);

			$isDownload = !empty($ecommerceEntryRow->IsDownload) ? 1 : 0;
			$isShipping = !empty($ecommerceEntryRow->IsShipping) ? 1 : 0;
			$isService = !empty($ecommerceEntryRow->IsService) ? 1 : 0;
			$isUpload = !empty($ecommerceEntryRow->IsUpload) ? 1 : 0;
			$isAlternativeShipping = (!empty($ecommerceEntryRow->IsAlternativeShipping) && $isShipping) ? 1 : 0;

			$alternativeShippingGross = 0;
			$alternativeShippingNet = 0;
			if($isAlternativeShipping){
				$alternativeShippingGross = round(floatval($ecommerceEntryRow->AlternativeShipping),2);
				$alternativeShippingNet = $alternativeShippingGross;
				if($defaultTax){
					$alternativeShippingNet = round($alternativeShippingGross - ($alternativeShippingGross / 100 * $defaultTax),2);
				}
				$alternativeShippingTotal = round($alternativeShippingTotal + $alternativeShippingGross,2);
				$alternativeShippingTotalTaxValue = round($alternativeShippingTotalTaxValue + ($alternativeShippingGross - $alternativeShippingNet),2);
			}elseif($isShipping){
				$itemHasDefaultShipping = true;
			}

			$priceTotalGrossItems = round($priceTotalGrossItems + ($priceGross * $quantity),2);
			$taxValueTotalItems = round($taxValueTotalItems + ($taxValueUnit * $quantity),2);

			$normalizedItem = $item;
			$normalizedItem['EcommerceEntryID'] = $ecommerceEntryId;
			$normalizedItem['realGid'] = $authoritativeGalleryId;
			$normalizedItem['realId'] = $authoritativeRealId;
			$normalizedItem['quantity'] = $quantity;
			$normalizedItem['priceGross'] = cg_ecommerce_format_money_value($priceGross);
			$normalizedItem['unit_amount'] = [
				'currency_code' => $currencyShort,
				'value' => cg_ecommerce_format_money_value($priceUnitNet)
			];
			if($taxPercentage){
				$normalizedItem['tax'] = [
					'currency_code' => $currencyShort,
					'value' => cg_ecommerce_format_money_value($taxValueUnit)
				];
			}else{
				unset($normalizedItem['tax']);
			}
			$normalizedItem['tax_percentage'] = cg_ecommerce_format_money_value($taxPercentage);
			$normalizedItem['IsDownload'] = $isDownload;
			$normalizedItem['IsShipping'] = $isShipping;
			$normalizedItem['IsService'] = $isService;
			$normalizedItem['IsUpload'] = $isUpload;
			$normalizedItem['IsAlternativeShipping'] = $isAlternativeShipping;
			$normalizedItem['alternative_shipping_amount_value_gross'] = cg_ecommerce_format_money_value($alternativeShippingGross);
			$normalizedItem['alternative_shipping_amount_value_net'] = cg_ecommerce_format_money_value($alternativeShippingNet);
			$normalizedItem['DownloadKeysCsvName'] = $ecommerceEntryRow->DownloadKeysCsvName;
			$normalizedItem['ServiceKeysCsvName'] = $ecommerceEntryRow->ServiceKeysCsvName;
			$normalizedItem['SaleType'] = $ecommerceEntryRow->SaleType;
			if(empty($normalizedItem['name'])){
				$normalizedItem['name'] = !empty($ecommerceEntryRow->SaleTitle) ? $ecommerceEntryRow->SaleTitle : 'product-title-'.$authoritativeRealId;
			}
			if(!isset($normalizedItem['description']) || $normalizedItem['description'] === ''){
				$normalizedItem['description'] = !empty($ecommerceEntryRow->SaleDescription) ? $ecommerceEntryRow->SaleDescription : 'product-description-'.$authoritativeRealId;
			}

			$authoritativeEcommerceData = cg_ecommerce_get_authoritative_ecommerce_entry_data($ecommerceEntryRow);
			if(empty($normalizedItem['RawData']) || !is_array($normalizedItem['RawData'])){
				$normalizedItem['RawData'] = [];
			}
			$normalizedItem['RawData']['realGid'] = $authoritativeGalleryId;
			$normalizedItem['RawData']['id'] = $authoritativeRealId;
			$normalizedItem['RawData']['ecommerceData'] = $authoritativeEcommerceData;

			if(empty($normalizedItem['WpUploads']) || !is_array($normalizedItem['WpUploads'])){
				$normalizedItem['WpUploads'] = [];
			}

			$normalizedItems[] = $normalizedItem;

			$cartSignatureItems[] = implode(':', [
				$ecommerceEntryId,
				$authoritativeGalleryId,
				$authoritativeRealId,
				$quantity,
				cg_ecommerce_format_money_value($priceGross),
				cg_ecommerce_format_money_value($taxPercentage),
				$isDownload,
				$isShipping,
				$isService,
				$isUpload,
				$isAlternativeShipping,
				cg_ecommerce_format_money_value($alternativeShippingGross)
			]);

		}

		sort($cartSignatureItems, SORT_STRING);

		$shippingGross = $itemHasDefaultShipping ? $defaultShippingGross : 0;
		$shippingNet = $shippingGross;
		$shippingTaxValue = 0;
		if($shippingGross && $defaultTax){
			$shippingNet = round($shippingGross - ($shippingGross / 100 * $defaultTax),2);
			$shippingTaxValue = round($shippingGross - $shippingNet,2);
		}

		$shippingTotal = round($shippingGross + $alternativeShippingTotal,2);
		$priceTotalGrossItemsWithShipping = round($priceTotalGrossItems + $shippingTotal,2);
		$priceTotalNetItems = round($priceTotalGrossItems - $taxValueTotalItems,2);
		$priceTotalNetItemsWithShipping = round($priceTotalNetItems + $shippingNet + ($alternativeShippingTotal - $alternativeShippingTotalTaxValue),2);

		$normalizedPurchaseUnit = !empty($purchaseUnits[0]) && is_array($purchaseUnits[0]) ? $purchaseUnits[0] : [];
		$normalizedPurchaseUnit['items'] = $normalizedItems;
		$normalizedPurchaseUnit['amount'] = [
			'currency_code' => $currencyShort,
			'value' => cg_ecommerce_format_money_value($priceTotalGrossItemsWithShipping),
			'breakdown' => [
				'item_total' => [
					'currency_code' => $currencyShort,
					'value' => cg_ecommerce_format_money_value($priceTotalNetItems)
				],
				'tax_total' => [
					'currency_code' => $currencyShort,
					'value' => cg_ecommerce_format_money_value($taxValueTotalItems)
				],
				'shipping' => [
					'currency_code' => $currencyShort,
					'value' => cg_ecommerce_format_money_value($shippingTotal)
				]
			]
		];

		$cartHashPayload = [
			'currency' => $currencyShort,
			'shipping_gross' => cg_ecommerce_format_money_value($shippingGross),
			'shipping_net' => cg_ecommerce_format_money_value($shippingNet),
			'shipping_tax' => cg_ecommerce_format_money_value($shippingTaxValue),
			'alternative_shipping_total' => cg_ecommerce_format_money_value($alternativeShippingTotal),
			'alternative_shipping_tax' => cg_ecommerce_format_money_value($alternativeShippingTotalTaxValue),
			'items' => $cartSignatureItems
		];

		$cartHash = cg_hash_function('---cgStripeCart---'.wp_json_encode($cartHashPayload));
		$amountCents = absint(round($priceTotalGrossItemsWithShipping * 100));

		return [
			'success' => true,
			'message' => '',
			'purchase_units' => [$normalizedPurchaseUnit],
			'cart_hash' => $cartHash,
			'amount_cents' => $amountCents,
			'currency_short' => $currencyShort,
			'item_count' => count($normalizedItems),
			'DefaultShipping' => $defaultShippingGross,
			'TaxPercentageDefault' => $defaultTax,
			'ShippingNet' => $shippingNet,
			'ShippingGross' => $shippingGross,
			'ShippingTaxValue' => $shippingTaxValue,
			'alternativeShippingTotal' => $alternativeShippingTotal,
			'alternativeShippingTotalTaxValue' => $alternativeShippingTotalTaxValue,
			'priceTotalUnformatted' => $priceTotalGrossItems,
			'priceTotalWithShippingTotalUnformatted' => $priceTotalGrossItemsWithShipping,
			'priceTotalWithShipping' => $priceTotalGrossItemsWithShipping,
			'defaultShipping' => $shippingGross,
			'PriceTotalNetItems' => $priceTotalNetItems,
			'PriceTotalNetItemsWithShipping' => $priceTotalNetItemsWithShipping,
			'PriceTotalGrossItems' => $priceTotalGrossItems,
			'PriceTotalGrossItemsWithShipping' => $priceTotalGrossItemsWithShipping,
			'TaxValueTotalItems' => $taxValueTotalItems,
			'ShippingTotal' => $shippingTotal
		];

	}
}

if(!function_exists('cg_ecommerce_build_authoritative_stripe_cart')) {
	function cg_ecommerce_build_authoritative_stripe_cart($purchaseUnits, $ecommerceOptions){
		return cg_ecommerce_build_authoritative_checkout_cart($purchaseUnits, $ecommerceOptions);
	}
}

if(!function_exists('cg_ecommerce_apply_authoritative_checkout_cart_to_post')) {
	function cg_ecommerce_apply_authoritative_checkout_cart_to_post(&$post, $authoritativeCart, $ecommerceOptions){

		$post['purchase_units'] = $authoritativeCart['purchase_units'];
		$post['DefaultShipping'] = $authoritativeCart['DefaultShipping'];
		$post['TaxPercentageDefault'] = $authoritativeCart['TaxPercentageDefault'];
		$post['ShippingNet'] = $authoritativeCart['ShippingNet'];
		$post['ShippingGross'] = $authoritativeCart['ShippingGross'];
		$post['ShippingTaxValue'] = $authoritativeCart['ShippingTaxValue'];
		$post['alternativeShippingTotal'] = $authoritativeCart['alternativeShippingTotal'];
		$post['alternativeShippingTotalTaxValue'] = $authoritativeCart['alternativeShippingTotalTaxValue'];
		$post['priceTotalUnformatted'] = $authoritativeCart['priceTotalUnformatted'];
		$post['priceTotalWithShippingTotalUnformatted'] = $authoritativeCart['priceTotalWithShippingTotalUnformatted'];
		$post['priceTotalWithShipping'] = $authoritativeCart['priceTotalWithShipping'];
		$post['defaultShipping'] = $authoritativeCart['defaultShipping'];
		$post['CurrencyShort'] = $authoritativeCart['currency_short'];
		$post['CurrencyPosition'] = $ecommerceOptions->CurrencyPosition;

	}
}


if(!function_exists('cg_ecommerce_processing_afterwards')) {
    function cg_ecommerce_processing_afterwards($Order,$OrderIdHash,$status,$ecommerce_options){

	    global $wpdb;
	    $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";

	    include(__DIR__ ."/../../../check-language-ecommerce.php");

	    $Order->IsFullPaid = 1;
	    $isFullPaid = 1;
	    $LogForDatabase = unserialize($Order->LogForDatabase);
	    $CreateInvoice = $ecommerce_options->CreateInvoice;
	    $SendInvoice = $ecommerce_options->SendInvoice;
	    $SendOrderConfirmationMail = $ecommerce_options->SendOrderConfirmationMail;

	    $wpdb->update(
		    "$tablename_ecommerce_orders",
		    array('IsFullPaid' => 1,'PaymentStatus' => $status),// PaymentStatus must be not completed or succeded before
		    array('id' => $Order->id),
		    array('%d','%s'),
		    array('%d')
	    );

	    $TaxPercentageDefault = $LogForDatabase['TaxPercentageDefault'];
	    $payer_email = $Order->PayerEmail;
	    $PayerEmail = $Order->PayerEmail;
	    $OrderNumber = $Order->OrderNumber;

	    $PriceTotalGrossItemsWithShipping = round(floatval($LogForDatabase['purchase_units'][0]['amount']['value']),2);

	    $processingData = cg_ecommerce_payment_processing_data($LogForDatabase,$OrderIdHash,$PayerEmail,$Order->PayPalTransactionId,$Order->Tstamp,$LogForDatabase['PriceDivider'],$status,$Order->id, $PriceTotalGrossItemsWithShipping,false,true,$Order->id,$isFullPaid,$OrderNumber);

	    $itemHasTax = $processingData['itemHasTax'];
	    $hasDownload = $processingData['hasDownload'];
	    $itemHasShipping = $processingData['itemHasShipping'];
	    $ordersummaryandpage = $processingData['ordersummaryandpage'];

	    if($itemHasTax || ($itemHasShipping && $TaxPercentageDefault)){
		    $hasTaxToShow = true;
	    }
	    $cgProVersion = contest_gal1ery_key_check();

	    $invoiceData = [
		    'InvoiceNumber' => '',
		    'ecommerceInvoicesFolder' => '',
		    'invoiceFilePath' => ''
	    ];

	    // UPDATE FULL PAID ALS ERSTES EINFÜGEN
	    if($ecommerce_options->CreateInvoice && $cgProVersion && cg_get_version()=='contest-gallery-pro' && $Order->IsFullPaid){
		    $invoiceDataCreated = cg_ecommerce_payment_processing_create_invoice($Order->id);
		    if(is_array($invoiceDataCreated)){
			    $invoiceData = array_merge($invoiceData,$invoiceDataCreated);
		    }
	    }
	    // to do here
	    $mailData = cg_ecommerce_prepare_payment_mail($ecommerce_options, $Order->id, $OrderIdHash, $ordersummaryandpage,$hasDownload,$language_FullOrderDetails,$language_Download);

	    cg_ecommerce_payment_processing_mail($CreateInvoice, $SendInvoice, $SendOrderConfirmationMail, $Order->IsFullPaid,$invoiceData['InvoiceNumber'],$invoiceData['ecommerceInvoicesFolder'],$invoiceData['invoiceFilePath'],$payer_email, $mailData['subject'], $mailData['Msg'], $mailData['headers']);

    }
}


if(!function_exists('cg_ecommerce_prepare_payment_mail')) {
    function cg_ecommerce_prepare_payment_mail($ecommerce_options, $OrderId, $OrderIdHash, $ordersummaryandpage,$hasDownload,$language_FullOrderDetails,$language_Download){

        $header = $ecommerce_options->OrderConfirmationMailHeader;
        $reply = $ecommerce_options->OrderConfirmationMailReply;
        $cc = $ecommerce_options->OrderConfirmationMailCc;
        $bcc = $ecommerce_options->OrderConfirmationMailBcc;

        $subject = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerce_options->OrderConfirmationMailSubject);
        $Msg = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerce_options->OrderConfirmationMail);
        $url = trim(sanitize_text_field($ecommerce_options->OCMailOrderSummaryURL));

        $replacePosUrl = '$order$';

        $url = !empty($url) ? $url : $ecommerce_options->ForwardAfterPurchaseUrl;

        $ordersummaryandpage .= '<br><a href="'.$url.'?cg_order='.$OrderIdHash.'" target="_blank" >'.$language_FullOrderDetails.(($hasDownload) ? ' ('.$language_Download.')' : '').'</a><br>';

        if(stripos($Msg,$replacePosUrl)!==false){
            $Msg = str_ireplace($replacePosUrl, $ordersummaryandpage, $Msg);
        }

        $headers = array();
        $headers[] = "From: $header <". html_entity_decode(strip_tags($reply)) . ">\r\n";
        $headers[] = "Reply-To: ". strip_tags($reply) . "\r\n";

        if(strpos($cc,';')){
            $cc = explode(';',$cc);
            foreach($cc as $ccValue){
                $ccValue = trim($ccValue);
                $headers[] = "CC: $ccValue\r\n";
            }
        }else{
            $headers[] = "CC: $cc\r\n";
        }

        if(strpos($bcc,';')){
            $bcc = explode(';',$bcc);
            foreach($bcc as $bccValue){
                $bccValue = trim($bccValue);
                $headers[] = "BCC: $bccValue\r\n";
            }
        }
        else{
            $headers[] = "BCC: $bcc\r\n";
        }

        $headers[] = "MIME-Version: 1.0\r\n";
        $headers[] = "Content-Type: text/html; charset=utf-8\r\n";

        return [
            'subject' => $subject,
            'Msg' => $Msg,
            'headers'=> $headers
        ];

    }
}

if(!function_exists('cg_ecommerce_payment_processing_mail')) {
    function cg_ecommerce_payment_processing_mail($CreateInvoice, $SendInvoice, $SendOrderConfirmationMail, $IsFullPaid,$InvoiceNumber,$ecommerceInvoicesFolder,$invoiceFilePath,$payer_email, $subject, $Msg, $headers){

        global $cgMailAction;
        global $cgMailGalleryId;
        global $cgIsGeneral;
        $cgMailAction = "Order confirmation e-mail";
        $cgMailGalleryId = 0;
        $cgIsGeneral = true;
        add_action( 'wp_mail_failed', 'cg_on_wp_mail_error', 10, 1 );

        if($CreateInvoice && $SendInvoice && $SendOrderConfirmationMail == 1 && $IsFullPaid && !empty($invoiceFilePath) && file_exists($invoiceFilePath) && is_readable($invoiceFilePath)){
            $invoiceFilePathForUser = '';
            $attachments = array($invoiceFilePath);
            if($InvoiceNumber && !empty($ecommerceInvoicesFolder) && is_dir($ecommerceInvoicesFolder) && is_writable($ecommerceInvoicesFolder)){
                $invoiceFilePathForUser = $ecommerceInvoicesFolder."/invoice-".$InvoiceNumber.'.pdf';
                if(copy($invoiceFilePath, $invoiceFilePathForUser)){
                    $attachments = array($invoiceFilePathForUser);
                }else{
                    $invoiceFilePathForUser = '';
                }
            }
            wp_mail($payer_email, $subject, $Msg, $headers, $attachments);
            if(!empty($invoiceFilePathForUser) && file_exists($invoiceFilePathForUser)){
                unlink($invoiceFilePathForUser);
            }
        }else{
            if($SendOrderConfirmationMail == 1 && $IsFullPaid){
	            //var_dump(2222);
	            wp_mail($payer_email, $subject, $Msg, $headers);
            }
        }

    }
}

if(!function_exists('cg_ecommerce_get_log_modified')) {
	function cg_ecommerce_get_log_modified($LogForDatabase,$OrderId){

		global $wpdb;
		$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

		// $OrderItems, has to be get here again, DownloadKey or ServiceKey might get added
		$OrderItems = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders_items WHERE ParentOrder = '$OrderId' ");
		$OrderItemsByEntryPid = [];
		foreach ($OrderItems as $OrderItem){
			$OrderItemsByEntryPid[$OrderItem->pid] = $OrderItem;
			foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){
				if($OrderItem->pid==$item['realId']){
					if(!empty($OrderItem->DownloadKey)){
						$LogForDatabase['purchase_units'][0]['items'][$key]['DownloadKey'] = $OrderItem->DownloadKey;
					}
					if(!empty($OrderItem->ServiceKey)){
						$LogForDatabase['purchase_units'][0]['items'][$key]['ServiceKey'] = $OrderItem->ServiceKey;
					}
					if(!empty($OrderItem->WpUploadFilesForSale)){
						$LogForDatabase['purchase_units'][0]['items'][$key]['WpUploadFilesForSale'] = unserialize($OrderItem->WpUploadFilesForSale);
					}
				}
			}
		}

		return $LogForDatabase;
	}
}

?>
