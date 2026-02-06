<?php

/*
echo "<pre>";
print_r($_POST);
echo "<pre>";
die;
*/


// because of esc_html and esc_attr json strings has to be converted first here to array
foreach ($_POST['orderData']['purchase_units'][0]['items'] as $key => $item){
	$_POST['orderData']['purchase_units'][0]['items'][$key]['RawData'] = json_decode(stripslashes($item['RawData']),true);
}

$_POST = cg1l_sanitize_post($_POST);

$SENT_POST = $_POST;
$SENT_POST['ownKeys'] = [];

// returns $SENT_POST as $beforeFilter if function does not exists
$beforeFilter = apply_filters( 'cg_filter_before_ecommerce_payment_processing', $SENT_POST);

/*
echo "<pre>";
print_r($_POST);
echo "<pre>";
die;*/

global $wpdb;
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";// will be get bottom via function
$tablename_ecommerce_invoice_options = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";
$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

$wp_upload_dir = wp_upload_dir();
$IsFullPaid = false;

$VersionDb = floatval(cg_get_db_version());
$VersionForScripts = cg_get_version_for_scripts();

$year = date('Y');
$month = date('m');
$day = date('d');
$time = time();

if(is_user_logged_in()){
    $SENT_POST['WpUserId'] = get_current_user_id();
}
$_POST = $_POST['orderData'];

$IsTest = 0;
if($_POST['isSandbox']=='true'){// has to be checked this way because of mixed sent data (rawdata as string) the true is a string
	$Environment = 'sandbox';
	$IsTest = 1;
}else{
	$Environment = 'live';
}

$Error = '';

$PaymentStatus = '';
$IsFullPaid = false;
$StripePiPaymentMethodName = false;
$StripeEmail = '';
$StripePiClientSecret = '';
$StripePiId = '';
$StripePiPaymentMethodId = '';
$StripePiPaymentMethodConfDetailsId = '';

$PaymentType = (!empty($_POST['StripePiClientSecret'])) ? 'stripe' : 'paypal';

$ecommerce_options = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = 1");
$ecommerce_options_array = json_decode(json_encode($ecommerce_options),true);

if($PaymentType == 'paypal'){
	if($IsTest){
		$accessToken = cg_paypal_get_access_token($ecommerce_options_array['PayPalSandboxClientId'],$ecommerce_options_array['PayPalSandboxSecret'],true);
	}else{
		$accessToken = cg_paypal_get_access_token($ecommerce_options_array['PayPalLiveClientId'],$ecommerce_options_array['PayPalLiveSecret']);
	}

	$PayPalOrderResponse = cg_get_paypal_order($accessToken,$_POST['id'],$IsTest);

	if(empty($PayPalOrderResponse['id'])){
		?>
        <script data-cg-processing="true">
            cgJsClass.gallery.vars.ecommerce.TransactionError = <?php echo json_encode($PayPalOrderResponse['message']);?>;
        </script>
		<?php
		die;
	}

	if(!empty($PayPalOrderResponse['status'])){
		$PaymentStatus = $PayPalOrderResponse['status'];
	}

	if(!empty($PayPalOrderResponse['status']) && $PayPalOrderResponse['status']=='COMPLETED'){
		$IsFullPaid = true;
	}

}elseif($PaymentType == 'stripe'){

	$StripePiClientSecret = (isset($_POST['StripePiClientSecret'])) ? $_POST['StripePiClientSecret'] : '';
	$StripePiId = (isset($_POST['StripePiId'])) ? $_POST['StripePiId'] : '';
	$StripeEmail = (isset($_POST['StripeEmail'])) ? $_POST['StripeEmail'] : '';
	$StripePiPaymentMethodId = '';
	$StripePiPaymentMethodConfDetailsId = '';

	if($IsTest){
		$secret = $ecommerce_options->StripeSandboxSecret;
	}else{
		$secret = $ecommerce_options->StripeLiveSecret;
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'.$StripePiId);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	$headers = array();
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = json_decode(curl_exec($ch),true);
	if (curl_errno($ch)) {
		$Error = 'Error payment_intent check:' . curl_error($ch);
	}else{
		if(!empty($result['error']['message'])){
			$Error = 'Error payment_intent check:' . $result['error']['message'];
		}else{
			$StripePiPaymentMethodId = $result['payment_method'];
			$StripePiPaymentMethodConfDetailsId = $result['payment_method_configuration_details']['id'];
        }



	}

	curl_close($ch);

	if(!empty($Error)){
		?>
        <script data-cg-processing="true">
            cgJsClass.gallery.vars.ecommerce.TransactionError = <?php echo json_encode($Error);?>;
        </script>
		<?php
		die;
	}else{
		$PaymentStatus = $result['status'];
		if($PaymentStatus=='succeeded'){
			$IsFullPaid = true;
		}
	}

	$StripeCustomer = cg_stripe_list_customers($StripeEmail,$secret);
    if(empty($StripeCustomer)){
        // create customer
	    $StripeCustomer = cg_stripe_create_customer($StripeEmail,$secret);
    }else{
	    // update customers address
	    $StripeCustomer = cg_stripe_update_customers($StripeEmail,$secret,$StripeCustomer['id']);
    }

	/*echo "<pre>";
	print_r($StripeCustomer);
	echo "</pre>";
	die;*/

	//cg_stripe_attach_customer_to_payment_method($secret,$StripePiPaymentMethodId,$StripeCustomer['id']);
//	$StripePiPaymentMethodName = cg_stripe_update_payment_method($secret,$StripePiPaymentMethodId,$StripeEmail);
  //  if(empty($StripePiPaymentMethodName)){// then must have been stripe light error, see cg_stripe_update_payment_method
   //     var_dump(1234);
	    $StripePiPaymentMethodName = cg_stripe_get_payment_method($secret,$StripePiPaymentMethodId);
 //   }

   // var_dump($StripePiPaymentMethodName);

  //  var_dump('ready123');

    //die;

}else{
	?>
    <script data-cg-processing="true">
        cgJsClass.gallery.vars.ecommerce.TransactionError = <?php echo json_encode("Not a PayPal and not a Stripe payment");?>;
    </script>
	<?php
	die;
}

//$PaymentStatus = 'processing';
//$IsFullPaid = false;

$selectSQLecommerceOptions = cg_get_ecommerce_options();
$CreateInvoice = $selectSQLecommerceOptions->CreateInvoice;
$SendInvoice = $selectSQLecommerceOptions->SendInvoice;
$PriceDivider = $selectSQLecommerceOptions->PriceDivider;
$_POST['PriceDivider'] = $PriceDivider;
$CurrencyPosition = $selectSQLecommerceOptions->CurrencyPosition;
$TaxPercentage = $selectSQLecommerceOptions->TaxPercentageDefault;

$EUshortcodes = cg_get_eu_countries_shortcodes();

include(__DIR__ ."/../../../check-language-ecommerce.php");

$hasTaxToShow = false;

$InvoiceAddressFirstName = (isset($_POST['cgInvoiceAddressFirstName'])) ? $_POST['cgInvoiceAddressFirstName'] : '';
$InvoiceAddressLastName = (isset($_POST['cgInvoiceAddressLastName'])) ? $_POST['cgInvoiceAddressLastName'] : '';
$InvoiceAddressCompany = (isset($_POST['cgInvoiceAddressCompany'])) ? $_POST['cgInvoiceAddressCompany'] : '';
$InvoiceAddressLine1 = (isset($_POST['cgInvoiceAddressLine1'])) ? $_POST['cgInvoiceAddressLine1'] : '';
$InvoiceAddressLine2 = (isset($_POST['cgInvoiceAddressLine2'])) ? $_POST['cgInvoiceAddressLine2'] : '';
$InvoiceAddressStateShort = (isset($_POST['cgInvoiceAddressStateShort'])) ? $_POST['cgInvoiceAddressStateShort'] : '';
$InvoiceAddressStateTranslation = (isset($_POST['cgInvoiceAddressStateTranslation'])) ? $_POST['cgInvoiceAddressStateTranslation'] : '';
$InvoiceAddressCity = (isset($_POST['cgInvoiceAddressCity'])) ? $_POST['cgInvoiceAddressCity'] : '';
$InvoiceAddressPostalCode = (isset($_POST['cgInvoiceAddressPostalCode'])) ? $_POST['cgInvoiceAddressPostalCode'] : '';
$InvoiceAddressCountryShort = (isset($_POST['cgInvoiceAddressCountryShort'])) ? $_POST['cgInvoiceAddressCountryShort'] : '';
$InvoiceAddressCountryTranslation = (isset($_POST['cgInvoiceAddressCountryTranslation'])) ? $_POST['cgInvoiceAddressCountryTranslation'] : '';

$EcommerceTaxNr = (isset($_POST['cgEcommerceTaxNr'])) ? $_POST['cgEcommerceTaxNr'] : '';

$InvoiceAddressForHtmlOutput = cg_ecommerce_create_invoice_address_for_html_output($InvoiceAddressFirstName,$InvoiceAddressLastName,$InvoiceAddressCompany,$InvoiceAddressLine1,$InvoiceAddressLine2,$InvoiceAddressStateShort,$InvoiceAddressCountryShort,$InvoiceAddressCity,$InvoiceAddressPostalCode,$InvoiceAddressStateTranslation,$EUshortcodes,$InvoiceAddressCountryTranslation,$EcommerceTaxNr,$language_VatNumber);

$ShippingAddressFirstName = (isset($_POST['cgShippingAddressFirstName'])) ? $_POST['cgShippingAddressFirstName'] : '';
$ShippingAddressLastName = (isset($_POST['cgShippingAddressLastName'])) ? $_POST['cgShippingAddressLastName'] : '';
$ShippingAddressCompany =(isset( $_POST['cgShippingAddressCompany'])) ? $_POST['cgShippingAddressCompany'] : '';
$ShippingAddressLine1 = (isset($_POST['cgShippingAddressLine1'])) ? $_POST['cgShippingAddressLine1'] : '';
$ShippingAddressLine2 = (isset($_POST['cgShippingAddressLine2'])) ? $_POST['cgShippingAddressLine2'] : '';
$ShippingAddressStateShort =(isset($_POST['cgShippingAddressStateShort'])) ? $_POST['cgShippingAddressStateShort'] : '';
$ShippingAddressStateTranslation =(isset($_POST['cgShippingAddressStateTranslation'])) ? $_POST['cgShippingAddressStateTranslation'] : '';
$ShippingAddressCity = (isset($_POST['cgShippingAddressCity'])) ? $_POST['cgShippingAddressCity'] : '';
$ShippingAddressPostalCode = (isset($_POST['cgShippingAddressPostalCode'])) ? $_POST['cgShippingAddressPostalCode'] : '';
$ShippingAddressCountryShort = (isset($_POST['cgShippingAddressCountryShort'])) ? $_POST['cgShippingAddressCountryShort'] : '';
$ShippingAddressCountryTranslation = (isset($_POST['cgShippingAddressCountryTranslation'])) ? $_POST['cgShippingAddressCountryTranslation'] : '';

$DefaultShipping = round(floatval($_POST['DefaultShipping']),2);
$DefaultTax = round(floatval($_POST['TaxPercentageDefault']),2);
$PriceTotalGrossItemsWithShipping = round(floatval($_POST['purchase_units'][0]['amount']['value']),2);
$TaxValueTotalItems = round(floatval($_POST['purchase_units'][0]['amount']['breakdown']['tax_total']['value']),2);
$ShippingNet = round(floatval($_POST['ShippingNet']),2);
$ShippingGross = round(floatval($_POST['ShippingGross']),2);
$ShippingTaxValue = round(floatval($_POST['ShippingTaxValue']),2);
$alternativeShippingTotal = round(floatval($_POST['alternativeShippingTotal']),2);
$alternativeShippingTotalTaxValue = round(floatval($_POST['alternativeShippingTotalTaxValue']),2);
//$PriceTotalNet = $PriceTotalGrossItemsWithShipping - $TaxValueTotalItems;

$itemHasDefaultShipping = false;
$PriceTotalNetItems = 0;
foreach ($_POST['purchase_units'][0]['items'] as $key => $item){
	if(!empty($item['IsShipping'])){
		if(!empty($item['alternative_shipping_amount_value_gross'])){
		}else{
			$itemHasDefaultShipping = true;
		}
	}
	$PriceUnitNet = round(floatval($item['unit_amount']['value']),2);
	$PriceTotalNet = $PriceUnitNet*intval($item['quantity']);
	$PriceTotalNetItems = $PriceTotalNetItems+$PriceTotalNet;
}

if($itemHasDefaultShipping){
    //var_dump('$alternativeShippingTotalTaxValue');
	//var_dump($alternativeShippingTotalTaxValue);
	//var_dump('$ShippingTaxValue');
	//var_dump($ShippingTaxValue);
 // $PriceTotalNetItems = $PriceTotalGrossItemsWithShipping - ($alternativeShippingTotal-$alternativeShippingTotalTaxValue) - $ShippingNet;
}else{// $alternativeShippingTotalTaxValue will sent as 0 if not existing
  //$PriceTotalNetItems = $PriceTotalGrossItemsWithShipping - ($alternativeShippingTotal-$alternativeShippingTotalTaxValue);
}

//var_dump('$PriceTotalNetItems');
//var_dump($PriceTotalNetItems);

//var_dump('$alternativeShippingTotal');
//var_dump($alternativeShippingTotal);

//var_dump('$alternativeShippingTotalTaxValue');
//var_dump($alternativeShippingTotalTaxValue);

//var_dump('$ShippingNet');
//var_dump($ShippingNet);

//var_dump('$alternativeShippingTotal');
//var_dump($alternativeShippingTotal);

//var_dump('($alternativeShippingTotal-$alternativeShippingTotalTaxValue)');
//var_dump(($alternativeShippingTotal-$alternativeShippingTotalTaxValue));

//var_dump('($alternativeShippingTotal)');
//var_dump(($alternativeShippingTotal));

$PriceTotalNetItemsWithShipping = $PriceTotalNetItems+($alternativeShippingTotal-$alternativeShippingTotalTaxValue);
$PriceTotalGrossItems = $PriceTotalGrossItemsWithShipping - $alternativeShippingTotal;

if($itemHasDefaultShipping){
	$PriceTotalNetItemsWithShipping = $PriceTotalNetItemsWithShipping+$ShippingNet;
	$PriceTotalGrossItems = $PriceTotalGrossItems - $ShippingGross;
}

//var_dump('$PriceTotalNetItemsWithShipping');
//var_dump($PriceTotalNetItemsWithShipping);

//var_dump('$PriceTotalGrossItems');
//var_dump($PriceTotalGrossItems);

//var_dump('$PriceTotalGrossItemsWithShipping');
//var_dump($PriceTotalGrossItemsWithShipping);

//var_dump('$TaxValueTotalItems');
//var_dump($TaxValueTotalItems);
//var_dump('$PriceTotalNetItems');
//var_dump($PriceTotalNetItems);

$ShippingTotal = round(floatval($_POST['purchase_units'][0]['amount']['breakdown']['shipping']['value']),2);
$CurrencyShort = $_POST['purchase_units'][0]['amount']['currency_code'];
$CurrencyPosition = $_POST['CurrencyPosition'];
$TaxPercentageDefault = $_POST['TaxPercentageDefault'];
$ThousandsSeparator = '.';
if($PriceDivider=='.'){
    $ThousandsSeparator = ',';
}
$TaxPercentageDefaultToShow = number_format(floatval($TaxPercentageDefault),2,$PriceDivider,$ThousandsSeparator);
//var_dump('$TaxPercentageDefaultToShow');
//var_dump($TaxPercentageDefaultToShow);
if($StripeEmail){
	$PayerEmail = $StripeEmail;
}else{
	$PayerEmail = $_POST['payer']['email_address'];
}

$WpUserId = 0;
if (is_user_logged_in()) {
    $WpUserId = get_current_user_id();
}
$IP = cg1l_sanitize_method(cg_get_user_ip());
if(empty($_POST['id'])){// id is PayPalTransactionId
	$_POST['id'] = '';
}
$PayPalTransactionId = $_POST['id'];
$LogFilePath = serialize($_POST);
$OrderId = 0;

$PaymentOrderType = 'capture';

$captureId = $_POST['id'].time();
if($Environment=='sandbox'){
    $databaseToSaveLogFilePathPart = '/contest-gallery/ecommerce/test-environment/logs/'.$year.'/'.$month;
}

if($Environment=='live'){
    $databaseToSaveLogFilePathPart = '/contest-gallery/ecommerce/live-environment/logs/'.$year.'/'.$month;
}

$ecommerceLogsFolder = $wp_upload_dir['basedir'].$databaseToSaveLogFilePathPart;

if(!is_dir($ecommerceLogsFolder)){
    mkdir($ecommerceLogsFolder, 0755, true);
}

$ecommerceLogsHtaccess = $ecommerceLogsFolder.'/.htaccess';

if(!file_exists($ecommerceLogsHtaccess)){
    $denyFromAllContent = <<<HEREDOC
<Files "*">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</Files>
HEREDOC;
    file_put_contents($ecommerceLogsHtaccess,$denyFromAllContent);
    chmod($ecommerceLogsHtaccess, 0640);// no read for others!!!
}

//$logName = 'log-'.$year.$month.$day.'-'.date('H').date('i').date('s').'.json';
$logName = 'log-'.$year.$month.$day.'-'.date('H').date('i').date('s').'-'.$PayerEmail;
$databaseToSaveLogFilePath = $databaseToSaveLogFilePathPart.'/'.$logName.'-'.$captureId.'.json';
$logFilePath = $ecommerceLogsFolder.'/'.$logName.'-'.$captureId.'.json';
$soldDownloadsFolder = $wp_upload_dir['basedir'].'/contest-gallery/ecommerce/sold-downloads';

if(file_exists($logFilePath)){
    $i = 2;
    do{
        $logFilePath = $ecommerceLogsFolder.'/'.$logName.'-'.$captureId.'-'.$i.'.json';
        $databaseToSaveLogFilePath = $databaseToSaveLogFilePathPart .'/'.$logName.'-'.$captureId.'-'.$i.'.json';
        $i++;
    }while(file_exists($logFilePath));
}
// LOGS DOCH IN DER DATENBANK speichern zwecks adresse anzeigen dann nützlich, in der datenbank ist es immer und alles kann nachgebaut werden daraus
//var_dump('$logFilePath');
//var_dump($logFilePath);

$LogForDatabase = $_POST;

file_put_contents($logFilePath,json_encode($_POST));

//var_dump(123);

//if(!empty($captureId)){
if(true){

    //$OrderId = 1;

	//var_dump('done');

    // to go sure that ecommerce processing is not done twice
    //if(empty($isCaptureIdExists)){
    if(true){

        /*
    echo "<pre>";
    print_r($_POST['purchase_units'][0]['items']);
    echo "</pre>";*/


    $Version = cg_get_version_for_scripts();
    $CreatedDateWP = cg_get_time_based_on_wp_timezone_conf($time,'Y-m-d H:i:s');
	    //var_dump('$CreatedDateWP');
	    //var_dump($CreatedDateWP);
    $CreatedMonth = cg_get_time_based_on_wp_timezone_conf($time,'n');
    $CreatedMonthWith0 = cg_get_time_based_on_wp_timezone_conf($time,'m');
    $CreatedYear = cg_get_time_based_on_wp_timezone_conf($time,'Y');

    // client
    $wpdb->query( $wpdb->prepare(
        "
                INSERT INTO $tablename_ecommerce_orders  
                ( id, GalleryID, PriceTotalNetItems, PriceTotalNetItemsWithShipping,PriceTotalGrossItems, PriceTotalGrossItemsWithShipping, ShippingTotal, 
                 ShippingNet, ShippingGross, ShippingTaxValue,TaxPercentageDefault,
                 CurrencyShort, CurrencyPosition, WpUserId, IP, IsTest, 
                 StripePiClientSecret, StripePiId, StripePiPaymentMethodId,StripePiPaymentMethodConfDetailsId,StripePiPaymentMethodName,
                 IsFullPaid,VersionDb,VersionScripts,
                 PaymentType, PaymentStatus, PaymentOrderType, PayPalTransactionId,LogFilePath,
                 LogForDatabase,OrderIdHash,Tstamp, 
                 PayerEmail,TaxNr,
                InvoiceAddressFirstName,InvoiceAddressLastName,InvoiceAddressCompany,InvoiceAddressLine1,InvoiceAddressLine2,InvoiceAddressCity,InvoiceAddressPostalCode,InvoiceAddressStateShort,InvoiceAddressStateTranslation,InvoiceAddressCountryShort,InvoiceAddressCountryTranslation,
                ShippingAddressFirstName,ShippingAddressLastName,ShippingAddressCompany,ShippingAddressLine1,ShippingAddressLine2,ShippingAddressCity,ShippingAddressPostalCode,ShippingAddressStateShort,ShippingAddressStateTranslation,ShippingAddressCountryShort,ShippingAddressCountryTranslation,
                Version,CreatedMonth,CreatedYear,CreatedDateWP
                )
                VALUES ( %s,%d,%f,%f,%f,%f,%f,
                        %f,%f,%f,%f,
                        %s,%s,%d,%s,%d,
                        %s,%s,%s,%s,%s,
                        %d,%f,%s,
                        %s,%s,%s,%s,%s,
                        %s,%s,%d,
                        %s,%s,
                        %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
                        %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
                        %s,%d,%d,%s
                        )
            ",
        '',intval($_POST['usedGid']),$PriceTotalNetItems, $PriceTotalNetItemsWithShipping, $PriceTotalGrossItems, $PriceTotalGrossItemsWithShipping, $ShippingTotal,
	    $ShippingNet, $ShippingGross, $ShippingTaxValue, $TaxPercentageDefault,
	    $CurrencyShort,$CurrencyPosition,$WpUserId,$IP, $IsTest,
	    $StripePiClientSecret, $StripePiId, $StripePiPaymentMethodId,$StripePiPaymentMethodConfDetailsId,$StripePiPaymentMethodName,
	    $IsFullPaid,$VersionDb,$VersionForScripts,
        $PaymentType, $PaymentStatus, $PaymentOrderType, $PayPalTransactionId,'WP_UPLOAD_DIR'.$databaseToSaveLogFilePath,
        serialize($LogForDatabase),'',$time,
        $PayerEmail,$EcommerceTaxNr,
	    $InvoiceAddressFirstName,$InvoiceAddressLastName,$InvoiceAddressCompany,$InvoiceAddressLine1,$InvoiceAddressLine2,$InvoiceAddressCity,$InvoiceAddressPostalCode,$InvoiceAddressStateShort,$InvoiceAddressStateTranslation,$InvoiceAddressCountryShort,$InvoiceAddressCountryTranslation,
	    $ShippingAddressFirstName,$ShippingAddressLastName,$ShippingAddressCompany,$ShippingAddressLine1,$ShippingAddressLine2,$ShippingAddressCity,$ShippingAddressPostalCode,$ShippingAddressStateShort,$ShippingAddressStateTranslation,$ShippingAddressCountryShort,$ShippingAddressCountryTranslation,
	    $Version,$CreatedMonth,$CreatedYear,$CreatedDateWP
    ));

	//var_dump('$InvoiceAddressFirstName after insert');
	//var_dump($InvoiceAddressFirstName);
/*
	  $wpdb->show_errors(); //setting the Show or Display errors option to true
		var_dump(3344555);
	  var_dump('$wpdb-> ORDER print_error();');
 var_dump($wpdb->print_error());
*/

	    $OrderId = $wpdb->insert_id;
    $ParentOrder = $OrderId;
    $createdOrderId =  $OrderId;

    $OrderIdHash = md5($PayPalTransactionId.uniqid(time()).time());

    $wpdb->update(
        "$tablename_ecommerce_orders",
        array('OrderIdHash' => $OrderIdHash, 'OrderNumber' => $OrderIdHash),
        array('id' => $OrderId),
        array('%s','%s'),
        array('%d')
    );

    if($PaymentType=='stripe'){
	    $_POST['payer']['email_address'] = $StripeEmail;
    }

    $payer_email = $_POST['payer']['email_address'];

    //
    //if(true){

        $hasDownload = false;

	    $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
	    $CurrencySymbol = $currenciesArray[$CurrencyShort];

        $processingData = cg_ecommerce_payment_processing_data($LogForDatabase, $OrderIdHash,$PayerEmail,$PayPalTransactionId,$time,$PriceDivider,$PaymentStatus,$ParentOrder, $PriceTotalGrossItemsWithShipping,true,false,0,$IsFullPaid,$OrderIdHash);

	    $wpdb->update(
		    "$tablename_ecommerce_orders",
		    array('LogForDatabase' => serialize($processingData['LogForDatabase'])),// LogForDatabase was modified and has to be updated after processing
		    array('id' => $OrderId),
		    array('%s'),
		    array('%d')
	    );

	    $hasDownload = $processingData['hasDownload'];
        $itemHasShipping = $processingData['itemHasShipping'];
        $ordersummaryandpage = $processingData['ordersummaryandpage'];

        $invoiceFilePath = '';
	    $cgProVersion = contest_gal1ery_key_check();

        $invoiceData = [
            'InvoiceNumber' => '',
            'ecommerceInvoicesFolder' => '',
            'invoiceFilePath' => ''
        ];

	    ###PRO###
	    if($CreateInvoice && $cgProVersion && cg_get_version()=='contest-gallery-pro' && $IsFullPaid){
            $invoiceData = cg_ecommerce_payment_processing_create_invoice($createdOrderId);
		    // order might be modified, by adding invoice
		    $Order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$OrderId' LIMIT 1");
	    }
	    ###PRO-END###

	    //var_dump(        'sale id sale item invoice and mail');
	    //var_dump(        '$OrderId');
	    //var_dump(        $OrderId);

	    $SendOrderConfirmationMail = $ecommerce_options->SendOrderConfirmationMail;

        $CreateInvoice = $ecommerce_options->CreateInvoice;

        // to do here
        $mailData = cg_ecommerce_prepare_payment_mail($ecommerce_options, $OrderId, $OrderIdHash, $ordersummaryandpage,$hasDownload,$language_FullOrderDetails,$language_Download);

        cg_ecommerce_payment_processing_mail($CreateInvoice, $SendInvoice, $SendOrderConfirmationMail, $IsFullPaid,$invoiceData['InvoiceNumber'],$invoiceData['ecommerceInvoicesFolder'],$invoiceData['invoiceFilePath'],$payer_email, $mailData['subject'], $mailData['Msg'], $mailData['headers']);

        //$attachments = array(ABSPATH . '/uploads/abc.png');
        //wp_mail($email, 'Testing Attachment' , 'This is subscription','This is for header',$attachments);
	    //var_dump('ForwardAfterPurchaseUrl');
	    //var_dump($ecommerce_options->ForwardAfterPurchaseUrl."?cg_order=$OrderIdHash");

	    apply_filters( 'cg_filter_after_ecommerce_payment_processing', $SENT_POST);

		echo "###ORDERIDHASH###$OrderIdHash###ORDERIDHASH###";

    }
}


?>