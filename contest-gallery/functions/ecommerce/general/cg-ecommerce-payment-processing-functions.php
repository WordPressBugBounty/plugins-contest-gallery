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
	    $invoiceFilePath = '';
	    $HasInvoice = false;
	    $cgProVersion = contest_gal1ery_key_check();

	    $invoiceData = [
		    'InvoiceNumber' => '',
		    'ecommerceInvoicesFolder' => ''
	    ];

	    // UPDATE FULL PAID ALS ERSTES EINFÜGEN
	    if($ecommerce_options->CreateInvoice && $cgProVersion && cg_get_version()=='contest-gallery-pro' && $Order->IsFullPaid){
		    $invoiceData = cg_ecommerce_payment_processing_create_invoice($Order->id);
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

        if($CreateInvoice && $SendInvoice && $SendOrderConfirmationMail == 1 && $IsFullPaid){
            if($InvoiceNumber){
                $invoiceFilePathForUser = $ecommerceInvoicesFolder."/invoice-".$InvoiceNumber.'.pdf';
                copy($invoiceFilePath, $invoiceFilePathForUser);
                $attachments = array($invoiceFilePathForUser);
            }else{
                $attachments = array($invoiceFilePath);
            }
	        wp_mail($payer_email, $subject, $Msg, $headers, $attachments);
            if($InvoiceNumber){
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