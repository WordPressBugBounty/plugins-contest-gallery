<?php
if(!function_exists('cg_ecommerce_change_options_and_sizes')){
    function cg_ecommerce_change_options_and_sizes($GalleryID){

        global $wpdb;

        $tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
        $tablenameEcommerceInvoiceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";

        $unsavingValues = $wpdb->get_row("SELECT * FROM $tablenameEcommerceOptions WHERE GeneralID = '1'");
        $PayPalSandboxClientId = $unsavingValues->PayPalSandboxClientId;
        $PayPalSandboxSecret = $unsavingValues->PayPalSandboxSecret;
        $PayPalLiveClientId = $unsavingValues->PayPalLiveClientId;
        $PayPalLiveSecret = $unsavingValues->PayPalLiveSecret;
	    $StripeSandboxClientId = $unsavingValues->StripeSandboxClientId;
	    $StripeSandboxSecret = $unsavingValues->StripeSandboxSecret;
	    $StripeLiveClientId = $unsavingValues->StripeLiveClientId;
	    $StripeLiveSecret = $unsavingValues->StripeLiveSecret;
        $CurrencyShort = $unsavingValues->CurrencyShort;
        $CurrencyPosition = $unsavingValues->CurrencyPosition;
        $PriceDivider = $unsavingValues->PriceDivider;
        $Environment = $unsavingValues->Environment;
        $TaxPercentageDefault = $unsavingValues->TaxPercentageDefault;
        $ShippingGross = $unsavingValues->ShippingGross;
        $RegUserPurchaseOnlyText = $unsavingValues->RegUserPurchaseOnlyText;
        $RegUserOrderSummaryOnlyText = $unsavingValues->RegUserOrderSummaryOnlyText;
        $CheckoutAgreementOne = $unsavingValues->CheckoutAgreementOne;
        $CheckoutAgreementTwo = $unsavingValues->CheckoutAgreementTwo;
        $CheckoutAgreementThree = $unsavingValues->CheckoutAgreementThree;
        $CheckoutNoteTop = $unsavingValues->CheckoutNoteTop;
        $CheckoutNoteBottom = $unsavingValues->CheckoutNoteBottom;
        $OrderConfirmationMail = $unsavingValues->OrderConfirmationMail;
        $OrderConfirmationMailHeader = $unsavingValues->OrderConfirmationMailHeader;
        $OrderConfirmationMailReply = $unsavingValues->OrderConfirmationMailReply;
        $OrderConfirmationMailBcc = $unsavingValues->OrderConfirmationMailBcc;
        $OrderConfirmationMailCc = $unsavingValues->OrderConfirmationMailCc;
        $OrderConfirmationMailSubject = $unsavingValues->OrderConfirmationMailSubject;
        $OCMailOrderSummaryURL = $unsavingValues->OCMailOrderSummaryURL;
	    $AllowedCountriesTranslations = $unsavingValues->AllowedCountriesTranslations;

        $ForwardAfterPurchaseUrl = $unsavingValues->ForwardAfterPurchaseUrl;
        $AllowedCountries = $unsavingValues->AllowedCountries;

        if(!empty($_POST['AllowedCountries'])){
            $AllowedCountriesArray = [];
            foreach ($_POST['AllowedCountries'] as $country_code => $country){
                $country_code = sanitize_text_field($country_code);
                $country = sanitize_text_field($country);
                $AllowedCountriesArray[$country_code] = $country;
            }
            $AllowedCountries = serialize($AllowedCountriesArray);
        }else{
            $AllowedCountries = '';
        }

        $TaxPercentageDefault = isset($_POST['TaxPercentageDefault']) ? $_POST['TaxPercentageDefault'] : $TaxPercentageDefault;

        $ShippingGross = isset($_POST['ShippingGross']) ? $_POST['ShippingGross'] : $ShippingGross;

        $unsavingValues = $wpdb->get_row("SELECT * FROM $tablenameEcommerceInvoiceOptions WHERE GeneralID = '1'");
        $InvoicerHeaderData = $unsavingValues->InvoicerHeaderData;
	    $InvoiceNumberLogicCustomNumberTest = $unsavingValues->InvoiceNumberLogicCustomNumberTest;
	    $InvoiceNumberLogicCustomNumberLive = $unsavingValues->InvoiceNumberLogicCustomNumberLive;

	    $PayPalApiActive = isset($_POST['PayPalApiActive']) ? 1 : 2;
	    $PayPalTestActive = !empty($_POST['PayPalTestActive']) ? 1 : 0;
	    $PayPalSandboxClientId = sanitize_text_field(isset($_POST['PayPalSandboxClientId']) ? $_POST['PayPalSandboxClientId'] : $PayPalSandboxClientId);
        $PayPalSandboxSecret = sanitize_text_field(isset($_POST['PayPalSandboxSecret']) ? $_POST['PayPalSandboxSecret'] : $PayPalSandboxSecret);
        $PayPalLiveClientId = sanitize_text_field(isset($_POST['PayPalLiveClientId']) ? $_POST['PayPalLiveClientId'] : $PayPalLiveClientId);
        $PayPalLiveSecret = sanitize_text_field(isset($_POST['PayPalLiveSecret']) ? $_POST['PayPalLiveSecret'] : $PayPalLiveSecret);

	    $StripeApiActive = isset($_POST['StripeApiActive']) ? 1 : 2;
	    $StripeTestActive = !empty($_POST['StripeTestActive']) ? 1 : 0;
	    $StripeSandboxClientId = sanitize_text_field(isset($_POST['StripeSandboxClientId']) ? $_POST['StripeSandboxClientId'] : $StripeSandboxClientId);
        $StripeSandboxSecret = sanitize_text_field(isset($_POST['StripeSandboxSecret']) ? $_POST['StripeSandboxSecret'] : $StripeSandboxSecret);
        $StripeLiveClientId = sanitize_text_field(isset($_POST['StripeLiveClientId']) ? $_POST['StripeLiveClientId'] : $StripeLiveClientId);
        $StripeLiveSecret = sanitize_text_field(isset($_POST['StripeLiveSecret']) ? $_POST['StripeLiveSecret'] : $StripeLiveSecret);
		
        $CurrencyShort = sanitize_text_field(isset($_POST['CurrencyShort']) ? $_POST['CurrencyShort'] : $CurrencyShort);
        $CurrencyPosition = sanitize_text_field(isset($_POST['CurrencyPosition']) ? $_POST['CurrencyPosition'] : $CurrencyPosition);
        $PriceDivider = sanitize_text_field(isset($_POST['PriceDivider']) ? $_POST['PriceDivider'] : $PriceDivider);
        $Environment = sanitize_text_field(isset($_POST['Environment']) ? $_POST['Environment'] : $Environment);
        $BorderRadiusOrder = isset($_POST['BorderRadiusOrder']) ? 1 : 0;
        $ForwardAfterPurchaseUrl = sanitize_text_field(isset($_POST['ForwardAfterPurchaseUrl']) ? $_POST['ForwardAfterPurchaseUrl'] : $ForwardAfterPurchaseUrl);

	    $RegUserPurchaseOnly = isset($_POST['RegUserPurchaseOnly']) ? 1 : 0;
	    $RegUserOrderSummaryOnly = isset($_POST['RegUserOrderSummaryOnly']) ? 1 : 0;

	    $RegUserPurchaseOnlyText = (isset($_POST['RegUserPurchaseOnlyText'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['RegUserPurchaseOnlyText']) : $RegUserPurchaseOnlyText;

        $RegUserOrderSummaryOnlyText = (isset($_POST['RegUserOrderSummaryOnlyText'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['RegUserOrderSummaryOnlyText']) : $RegUserOrderSummaryOnlyText;

        $CheckoutAgreementOne = (isset($_POST['CheckoutAgreementOne'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['CheckoutAgreementOne']) : $CheckoutAgreementOne;
        $CheckoutAgreementTwo = (isset($_POST['CheckoutAgreementTwo'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['CheckoutAgreementTwo']) : $CheckoutAgreementTwo;
        $CheckoutAgreementThree = (isset($_POST['CheckoutAgreementThree'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['CheckoutAgreementThree']) : $CheckoutAgreementThree;
        $CheckoutNoteTop = (isset($_POST['CheckoutNoteTop'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['CheckoutNoteTop']) : $CheckoutNoteTop;
        $CheckoutNoteBottom = (isset($_POST['CheckoutNoteBottom'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['CheckoutNoteBottom']) : $CheckoutNoteBottom;

        $FeControlsStyleOrder = (!empty($_POST['FeControlsStyleWhiteOrder'])) ? 'white' : 'black';

        $SendOrderConfirmationMail = isset($_POST['SendOrderConfirmationMail']) ? 1 : 0;
        $OrderConfirmationMailHeader = sanitize_text_field(isset($_POST['OrderConfirmationMailHeader']) ? $_POST['OrderConfirmationMailHeader'] : $OrderConfirmationMailHeader);
        $OrderConfirmationMailReply = sanitize_text_field(isset($_POST['OrderConfirmationMailReply']) ? $_POST['OrderConfirmationMailReply'] : $OrderConfirmationMailReply);
        $OrderConfirmationMailBcc = sanitize_text_field(isset($_POST['OrderConfirmationMailBcc']) ? $_POST['OrderConfirmationMailBcc'] : $OrderConfirmationMailBcc);
        $OrderConfirmationMailCc = sanitize_text_field(isset($_POST['OrderConfirmationMailCc']) ? $_POST['OrderConfirmationMailCc'] : $OrderConfirmationMailCc);
        $OrderConfirmationMailSubject = sanitize_text_field(isset($_POST['OrderConfirmationMailSubject']) ? $_POST['OrderConfirmationMailSubject'] : $OrderConfirmationMailSubject);
        $OCMailOrderSummaryURL = sanitize_text_field(isset($_POST['OCMailOrderSummaryURL']) ? $_POST['OCMailOrderSummaryURL'] : $OCMailOrderSummaryURL);
        $OrderConfirmationMail = (isset($_POST['OrderConfirmationMail'])) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['OrderConfirmationMail']) : $OrderConfirmationMail;

        $CreateInvoice = isset($_POST['CreateInvoice']) ? 1 : 0;
        $SendInvoice = isset($_POST['SendInvoice']) ? 1 : 0;
        $UseNewInvoiceNumberLogic = isset($_POST['UseNewInvoiceNumberLogic']) ? 1 : 0;
        $CreateAndSetInvoiceNumber = isset($_POST['CreateAndSetInvoiceNumber']) ? 1 : 0;
        $ResetCustomNumberNextInvoiceTest = isset($_POST['ResetCustomNumberNextInvoiceTest']) ? 1 : 0;
        $ResetCustomNumberNextInvoiceLive = isset($_POST['ResetCustomNumberNextInvoiceLive']) ? 1 : 0;

        $InvoiceNote = [];
        if(!empty($_POST['InvoiceNote'])){
            foreach ($_POST['InvoiceNote'] as $countryCode => $country){
                $InvoiceNote[sanitize_text_field($countryCode)] = sanitize_textarea_field($country);
            }
        }
	    $InvoiceNote = serialize($InvoiceNote);
        $InvoicerHeaderData = sanitize_textarea_field(isset($_POST['InvoicerHeaderData']) ? $_POST['InvoicerHeaderData'] : $InvoicerHeaderData);

        $InvoiceNumberLogicSelectTest = sanitize_text_field($_POST['InvoiceNumberLogicSelectTest']);
        $InvoiceNumberLogicOwnPrefixTest = sanitize_text_field($_POST['InvoiceNumberLogicOwnPrefixTest']);

        $InvoiceNumberLogicSelectLive = sanitize_text_field($_POST['InvoiceNumberLogicSelectLive']);
        $InvoiceNumberLogicOwnPrefixLive = sanitize_text_field($_POST['InvoiceNumberLogicOwnPrefixLive']);

	    if(isset($_POST['InvoiceNumberLogicCustomNumberTest'])){
		    $InvoiceNumberLogicCustomNumberTest = sanitize_text_field($_POST['InvoiceNumberLogicCustomNumberTest']);
	    }else{
		    /*$lastInvoiceNumberLogicCustomNumberTest = $wpdb->get_var($wpdb->prepare( "SELECT InvoiceNumberLogicCustomNumber FROM $tablename_ecommerce_orders WHERE id > %d AND IsTest = 1 AND InvoiceNumberLogicCustomNumber IS NOT NULL AND TRIM(InvoiceNumberLogicCustomNumber) <> '' ORDER BY id DESC LIMIT 1",[0]));
			if(empty($lastInvoiceNumberLogicCustomNumberTest)){
				$InvoiceNumberLogicCustomNumberTest = $InvoiceNumberLogicCustomNumberTest;
			}else{
				$InvoiceNumberLogicCustomNumberTest = cg_increase_number($lastInvoiceNumberLogicCustomNumberTest);
			}*/
	    }

		if(isset($_POST['InvoiceNumberLogicCustomNumberLive'])){
			$InvoiceNumberLogicCustomNumberLive = sanitize_text_field($_POST['InvoiceNumberLogicCustomNumberLive']);
		}else{
			/*$lastInvoiceNumberLogicCustomNumberLive = $wpdb->get_var($wpdb->prepare( "SELECT InvoiceNumberLogicCustomNumber FROM $tablename_ecommerce_orders WHERE id > %d AND IsLive = 1 AND InvoiceNumberLogicCustomNumber IS NOT NULL AND TRIM(InvoiceNumberLogicCustomNumber) <> '' ORDER BY id DESC LIMIT 1",[0]));
			if(empty($lastInvoiceNumberLogicCustomNumberLive)){
				$InvoiceNumberLogicCustomNumberLive = $InvoiceNumberLogicCustomNumberLive;
			}else{
				$InvoiceNumberLogicCustomNumberLive = cg_increase_number($lastInvoiceNumberLogicCustomNumberLive);
			}*/
		}

        $PayPalDisableFunding = sanitize_text_field($_POST['PayPalDisableFunding']);

		if(!empty($_POST['AllowedCountriesTranslations'])){
			$AllowedCountriesTranslations = cg1l_sanitize_post($_POST['AllowedCountriesTranslations']);
			$AllowedCountriesTranslations = serialize($AllowedCountriesTranslations);
		}

	    $wpdb->update(
            $tablenameEcommerceOptions,
            array(
                'PayPalApiActive' => $PayPalApiActive,'PayPalTestActive' => $PayPalTestActive,
                'PayPalSandboxClientId' => $PayPalSandboxClientId,'PayPalSandboxSecret' => $PayPalSandboxSecret,
                'PayPalLiveClientId' => $PayPalLiveClientId, 'PayPalLiveSecret' => $PayPalLiveSecret,
                'StripeApiActive' => $StripeApiActive,'StripeTestActive' => $StripeTestActive,
                'StripeSandboxClientId' => $StripeSandboxClientId,'StripeSandboxSecret' => $StripeSandboxSecret,
                'StripeLiveClientId' => $StripeLiveClientId, 'StripeLiveSecret' => $StripeLiveSecret,
                'CurrencyShort' => $CurrencyShort, 'CurrencyPosition' => $CurrencyPosition, 'PriceDivider' => $PriceDivider,
                'CreateInvoice' => $CreateInvoice, 'SendInvoice' => $SendInvoice,'Environment' => $Environment,
                 'TaxPercentageDefault' => $TaxPercentageDefault,'ShippingGross'=>$ShippingGross,
                'CheckoutAgreementOne'=>$CheckoutAgreementOne,'CheckoutAgreementTwo'=>$CheckoutAgreementTwo,
                'CheckoutAgreementThree'=>$CheckoutAgreementThree,'CheckoutNoteBottom'=>$CheckoutNoteBottom,'CheckoutNoteTop'=>$CheckoutNoteTop,
                'SendOrderConfirmationMail'=>$SendOrderConfirmationMail,'OrderConfirmationMailHeader'=>$OrderConfirmationMailHeader,'OrderConfirmationMailReply'=>$OrderConfirmationMailReply,'OrderConfirmationMailBcc'=>$OrderConfirmationMailBcc,'OrderConfirmationMailCc'=>$OrderConfirmationMailCc,'OrderConfirmationMailSubject'=>$OrderConfirmationMailSubject,'OCMailOrderSummaryURL'=>$OCMailOrderSummaryURL,'OrderConfirmationMail'=>$OrderConfirmationMail,
                'ForwardAfterPurchaseUrl'=>$ForwardAfterPurchaseUrl,'AllowedCountriesTranslations'=>$AllowedCountriesTranslations,
                'AllowedCountries'=>$AllowedCountries,'BorderRadiusOrder'=>$BorderRadiusOrder,'FeControlsStyleOrder'=>$FeControlsStyleOrder,'PayPalDisableFunding'=>$PayPalDisableFunding,
                'RegUserPurchaseOnlyText'=>$RegUserPurchaseOnlyText,'RegUserOrderSummaryOnlyText'=>$RegUserOrderSummaryOnlyText,
                'RegUserPurchaseOnly'=>$RegUserPurchaseOnly,'RegUserOrderSummaryOnly'=>$RegUserOrderSummaryOnly
            ),
            array('GeneralID' => 1),
            array(
                '%d','%d',
                '%s','%s',
                '%s', '%s',
                '%d','%d',
                '%s','%s',
                '%s', '%s',
                '%s', '%s', '%s',
                '%d', '%d', '%s',
                '%f', '%f',
                '%s', '%s',
                '%s', '%s', '%s',
                '%d','%s','%s','%s','%s','%s','%s','%s',
                '%s','%s',
                '%s', '%d', '%s', '%s',
                '%s', '%s',
                '%d', '%d'
            ),
            array('%d')
        );

        $wpdb->update(
            $tablenameEcommerceInvoiceOptions,
            array(
                'ResetCustomNumberNextInvoiceTest' => $ResetCustomNumberNextInvoiceTest,'ResetCustomNumberNextInvoiceLive' => $ResetCustomNumberNextInvoiceLive,'CreateAndSetInvoiceNumber' => $CreateAndSetInvoiceNumber,
                'InvoiceNumberLogicSelectTest' => $InvoiceNumberLogicSelectTest,'InvoiceNumberLogicOwnPrefixTest' => $InvoiceNumberLogicOwnPrefixTest,'InvoiceNumberLogicCustomNumberTest' => $InvoiceNumberLogicCustomNumberTest,
                'InvoiceNumberLogicSelectLive' => $InvoiceNumberLogicSelectLive,'InvoiceNumberLogicOwnPrefixLive' => $InvoiceNumberLogicOwnPrefixLive,'InvoiceNumberLogicCustomNumberLive' => $InvoiceNumberLogicCustomNumberLive,
                 'InvoicerHeaderData' => $InvoicerHeaderData, 'InvoiceNote' => $InvoiceNote
            ),
            array('GeneralID' => 1),
            array(
                '%d','%d','%d',
                '%s','%s','%s',
                '%s','%s','%s',
                '%s','%s'
            ),
            array('%d')
        );

        /*$unsavingValues = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_upload_options WHERE GalleryID = $GalleryID");

        $SaleTitle = $unsavingValues->SaleTitle;
        $SaleDescription = $unsavingValues->SaleDescription;
        $Price = $unsavingValues->Price;

        $Price = sanitize_text_field(isset($_POST['Price']) ? $_POST['Price'] : $Price);
        $SaleTitle = sanitize_text_field(isset($_POST['SaleTitleEcommerceUpload']) ? $_POST['SaleTitleEcommerceUpload'] : $SaleTitle);
        $SaleDescription = sanitize_text_field(isset($_POST['SaleDescriptionEcommerceUpload']) ? contest_gal1ery_htmlentities_and_preg_replace($_POST['SaleDescriptionEcommerceUpload']) : $SaleDescription);

        $wpdb->update(
            $tablename_ecommerce_upload_options,
            array(
                'Price' => $Price,
                'SaleTitle' => $SaleTitle, 'SaleDescription' => $SaleDescription,
            ),
            array('GalleryID' => $GalleryID),
            array(
                '%d',
                '%s', '%s',
            ),
            array('%d')
        );*/

    }
}