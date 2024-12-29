<?php

/*
echo "<pre>";
print_r($_POST);
echo "<pre>";
die;*/

// because of esc_html and esc_attr json strings has to be converted first here to array
foreach ($_POST['orderData']['purchase_units'][0]['items'] as $key => $item){
	$_POST['orderData']['purchase_units'][0]['items'][$key]['RawData'] = json_decode(stripslashes($item['RawData']),true);
}

$_POST = cg1l_sanitize_post($_POST);

$SENT_POST = $_POST;
$SENT_POST['ownKeys'] = [];

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

$year = date('Y');
$month = date('m');
$day = date('d');
$time = time();

if(is_user_logged_in()){
    $SENT_POST['WpUserId'] = get_current_user_id();
}
$_POST = $_POST['orderData'];

$selectSQLecommerceOptions = cg_get_ecommerce_options();
$CreateInvoice = $selectSQLecommerceOptions->CreateInvoice;
$SendInvoice = $selectSQLecommerceOptions->SendInvoice;
$PriceDivider = $selectSQLecommerceOptions->PriceDivider;
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

$InvoiceAddressForHtmlOutput = $InvoiceAddressFirstName.' '.$InvoiceAddressLastName;
$InvoiceAddressForHtmlOutput .= ($InvoiceAddressCompany) ? '<br>'.$InvoiceAddressCompany : '';
$InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressLine1;
$InvoiceAddressForHtmlOutput .= ($InvoiceAddressLine2) ? '<br>'.$InvoiceAddressLine2 : '';
if($InvoiceAddressStateShort){
	if($InvoiceAddressCountryShort=='US'  || $InvoiceAddressCountryShort=='CA'){
		$InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressStateShort.' '.$InvoiceAddressPostalCode;
	}else{
		$InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressStateTranslation.' '.$InvoiceAddressPostalCode;
	}
}else{
	if(in_array($InvoiceAddressCountryShort,$EUshortcodes)!==false){
		$InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressPostalCode.' '.$InvoiceAddressCity;
	}else{
		$InvoiceAddressForHtmlOutput .= '<br>'.$InvoiceAddressCity.' '.$InvoiceAddressPostalCode;
	}
}

$InvoiceAddressForHtmlOutput .= ($InvoiceAddressCountryTranslation) ? '<br>'.$InvoiceAddressCountryTranslation : '';
if($EcommerceTaxNr){
	$InvoiceAddressForHtmlOutput .=  '<br>'.$language_VatNumber.': '.$EcommerceTaxNr;
}

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
$TaxPercentageDefaultToShow = number_format(floatval($TaxPercentageDefault),2,$PriceDivider);
//var_dump('$TaxPercentageDefaultToShow');
//var_dump($TaxPercentageDefaultToShow);
$PayerEmail = $_POST['payer']['email_address'];

$WpUserId = 0;
if (is_user_logged_in()) {
    $WpUserId = get_current_user_id();
}
$IP = cg1l_sanitize_method(cg_get_user_ip());
$PayPalTransactionId = $_POST['id'];
$captureStatus = $_POST['status'];
$LogFilePath = serialize($_POST);
$OrderId = 0;

$PaymentType = 'paypal';
$PaymentOrderType = 'capture';

$IsTest = 0;
if($_POST['isSandbox']=='true'){// has to be checked this way because of mixed sent data (rawdata as string) the true is a string
    $Environment = 'sandbox';
    $IsTest = 1;
}else{
    $Environment = 'live';
}

$captureId = isset($_POST['id']) ? $_POST['id'] : 'no-capture-id' ;
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
order deny, allow
deny from all
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

    $isCaptureIdExists = $wpdb->get_var("SELECT COUNT(*) as NumberOfRows FROM $tablename_ecommerce_orders WHERE PayPalTransactionId = '$captureId' LIMIT 1");

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
                 PaymentType, PaymentOrderType, PayPalTransactionId,LogFilePath,
                 LogForDatabase,OrderIdHash,Tstamp,
                 PayerEmail,TaxNr,
                InvoiceAddressFirstName,InvoiceAddressLastName,InvoiceAddressCompany,InvoiceAddressLine1,InvoiceAddressLine2,InvoiceAddressCity,InvoiceAddressPostalCode,InvoiceAddressStateShort,InvoiceAddressStateTranslation,InvoiceAddressCountryShort,InvoiceAddressCountryTranslation,
                ShippingAddressFirstName,ShippingAddressLastName,ShippingAddressCompany,ShippingAddressLine1,ShippingAddressLine2,ShippingAddressCity,ShippingAddressPostalCode,ShippingAddressStateShort,ShippingAddressStateTranslation,ShippingAddressCountryShort,ShippingAddressCountryTranslation,
                Version,CreatedMonth,CreatedYear,CreatedDateWP
                )
                VALUES ( %s,%d,%f,%f,%f,%f,%f,
                        %f,%f,%f,%f,
                        %s,%s,%d,%s,%d,
                        %s,%s,%s,%s,
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
        $PaymentType, $PaymentOrderType, $PayPalTransactionId,'WP_UPLOAD_DIR'.$databaseToSaveLogFilePath,
        serialize($LogForDatabase),'',$time,
        $PayerEmail,$EcommerceTaxNr,
	    $InvoiceAddressFirstName,$InvoiceAddressLastName,$InvoiceAddressCompany,$InvoiceAddressLine1,$InvoiceAddressLine2,$InvoiceAddressCity,$InvoiceAddressPostalCode,$InvoiceAddressStateShort,$InvoiceAddressStateTranslation,$InvoiceAddressCountryShort,$InvoiceAddressCountryTranslation,
	    $ShippingAddressFirstName,$ShippingAddressLastName,$ShippingAddressCompany,$ShippingAddressLine1,$ShippingAddressLine2,$ShippingAddressCity,$ShippingAddressPostalCode,$ShippingAddressStateShort,$ShippingAddressStateTranslation,$ShippingAddressCountryShort,$ShippingAddressCountryTranslation,
	    $Version,$CreatedMonth,$CreatedYear,$CreatedDateWP
    ));

	//var_dump('$InvoiceAddressFirstName after insert');
	//var_dump($InvoiceAddressFirstName);

	 //  $wpdb->show_errors(); //setting the Show or Display errors option to true
	//	var_dump(3344555);
	   //var_dump('$wpdb-> ORDER print_error();');
	// var_dump($wpdb->print_error());

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

    $payer_email = $_POST['payer']['email_address'];

    //
    //if(true){

        $itemHasTax = false;
        $itemHasShipping = false;
        $hasDownload = false;

	    $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
	    $CurrencySymbol = $currenciesArray[$CurrencyShort];

	    $ordersummaryandpage = '<table>';
	    $ordersummaryandpage .= '<tr><td><b>'.$language_OrderNumber.':</b><br><br></td><td style="padding-left:30px;">'.$OrderIdHash.'<br><br></td></tr>';

        foreach ($_POST['purchase_units'][0]['items'] as $key => $item){

	        //var_dump('$item');
            /*echo "<pre>";
                print_r($item);
            echo "</pre>";*/

	        $IsService = false;
            $IsShipping = false;
            $IsAlternativeShipping = false;
            $IsDownload = false;
	        $IsUpload = false;
            $Shipping = 0;
            $TaxValue = 0;
            $DownloadKey = '';
            $ServiceKey = '';
            $entryId = intval($item['EcommerceEntryID']);
            $WpUploads = serialize($item['WpUploads']);

	        $WpUploadFilesForSale = $item['RawData']['ecommerceData']['WpUploadFilesForSale'];
	        //var_dump('MultipleFilesParsed123');
	        //var_dump('$WpUploadFilesForSale');
	        //var_dump($WpUploadFilesForSale);
            if(!empty($WpUploadFilesForSale)){
	            $WpUploadFilesForSale = serialize($WpUploadFilesForSale);
            }else{
	            $WpUploadFilesForSale = '';
            }
	        //var_dump('raw data after');
	        /*echo "<pre>";
	        print_r($item['RawData']);
	        echo "</pre>";*/

	        //var_dump('realGid123');
	        //var_dump($item['RawData']['realGid']);

	        $GalleryID = intval($item['RawData']['realGid']);
	        $RawData = serialize($item['RawData']);

	        $AlternativeShippingGross = 0;
	        $AlternativeShippingNet = 0;
	        $AlternateShippingTaxValue = 0;

            if(!empty($item['IsShipping'])){
                $IsShipping = true;
	            $itemHasShipping = true;
                if(!empty($item['alternative_shipping_amount_value_gross'])){
                    $IsAlternativeShipping = true;
	                $AlternativeShippingGross = round(floatval($item['alternative_shipping_amount_value_gross']),2);
	                $AlternativeShippingNet = round(floatval($item['alternative_shipping_amount_value_net']),2);
	                //var_dump('$IsAlternativeShipping');
	                //var_dump($IsAlternativeShipping);
	                //var_dump($AlternativeShippingGross);
	                //var_dump($AlternativeShippingNet);
                    $AlternateShippingTaxValue = $AlternativeShippingGross - $AlternativeShippingNet;
                }else{
	                if(!empty($item['IsAlternativeShipping'])){// might be IsAlternativeShipping with alternative_shipping_amount_value_gross 0
		                $IsAlternativeShipping = true;
	                }
                }
            }else if($item['IsService']){
	            //var_dump('$IsService123');
	            //var_dump($IsService);
	            $IsService = true;
            }else if($item['IsUpload']){
	            //var_dump('$IsService123');
	            //var_dump($IsService);
	            $IsUpload = true;
            }else{
	            $hasDownload = true;
                $IsDownload = true;
            }

            if(!empty($item['tax'])){
                $TaxValue = round(floatval($item['tax']['value']),2);
            }

	        $TaxPercentage = 0;

	        if(!empty($item['tax_percentage'])){
                $TaxPercentage = round(floatval($item['tax_percentage']),2);
            }

            $EcommerceEntryID = $item['EcommerceEntryID'];

            $ecommerceEntryRow = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_entries WHERE id = '$EcommerceEntryID'  ORDER BY id DESC LIMIT 1");

            $DownloadKeysCsvName = $ecommerceEntryRow->DownloadKeysCsvName;
            $ServiceKeysCsvName = $ecommerceEntryRow->ServiceKeysCsvName;
            $realId = $ecommerceEntryRow->pid;

            if(!empty($DownloadKeysCsvName) && $IsDownload){
                $DownloadKey = cg_get_set_key($GalleryID,$realId,$DownloadKeysCsvName,'',$PayerEmail,$captureId,$time);
            }

            if(!empty($ServiceKeysCsvName) && $IsService){
                $ServiceKey = cg_get_set_key($GalleryID,$realId,'',$ServiceKeysCsvName,$PayerEmail,$captureId,$time);
            }

	        $PriceUnitNet = round(floatval($item['unit_amount']['value']),2);
	        $_POST['purchase_units'][0]['items'][$key]['PriceUnitNet'] = $PriceUnitNet;
	        $PriceTotalNet = $PriceUnitNet*intval($item['quantity']);
	        $_POST['purchase_units'][0]['items'][$key]['PriceTotalNet'] = $PriceTotalNet;
	        $PriceUnitGross = $PriceUnitNet + round(floatval($item['tax']['value']),2);
	        $PriceTotalGross = $PriceUnitGross * intval($item['quantity']);
	        $_POST['purchase_units'][0]['items'][$key]['PriceUnitGross'] = $PriceUnitGross;
	        $_POST['purchase_units'][0]['items'][$key]['PriceTotalGross'] = $PriceTotalGross;
	        $TaxValueUnit = round(floatval($item['tax']['value']),2);
	        $_POST['purchase_units'][0]['items'][$key]['TaxValueUnit'] = $TaxValueUnit;
	        $TaxValueTotal = $TaxValueUnit * intval($item['quantity']);
	        $_POST['purchase_units'][0]['items'][$key]['TaxValueTotal'] = $TaxValueTotal;

	        $_POST['purchase_units'][0]['items'][$key]['AlternativeShippingGross'] = $AlternativeShippingGross;
	        $_POST['purchase_units'][0]['items'][$key]['AlternativeShippingNet'] = $AlternativeShippingNet;
	        $_POST['purchase_units'][0]['items'][$key]['AlternateShippingTaxValue'] = $AlternateShippingTaxValue;
	        $_POST['purchase_units'][0]['items'][$key]['TaxValue'] = $TaxValue;
	        $_POST['purchase_units'][0]['items'][$key]['TaxPercentage'] = $TaxPercentage;

	        $_POST['purchase_units'][0]['items'][$key]['TaxPercentageToShow']=cg_ecommerce_price_to_show($currenciesArray,'%','right',$PriceDivider,$TaxPercentage);

	        if($TaxPercentage){
	            $itemHasTax = true;
            }

	        //var_dump('$currenciesArray 123');
	        //var_dump($currenciesArray);
	        //var_dump('$PriceTotalGross 123');
	        //var_dump($PriceTotalGross);
	        //var_dump('$CurrencyShort 123');
	        //var_dump($CurrencyShort);
	        //var_dump('$CurrencyPosition 123');
	        //var_dump($CurrencyPosition);
	        //var_dump('$PriceDivider 123');
	        //var_dump($PriceDivider);

	        $PriceTotalGrossToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$PriceTotalGross);

            if(intval($item['quantity'])>1){
	            $PriceUnitNetToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$PriceUnitNet);
	            $PriceTotalGrossToShow .= ' ('.$item['quantity'] .'*'. $PriceUnitNetToShow .')';
            }

	        //var_dump('$PriceTotalGrossToShow 123');
	        //var_dump($PriceTotalGrossToShow);

            $nameForMailToShow = ((strlen($item['name'])>70) ? substr($item['name'],0,70).'...' : $item['name']);

	        $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.':</b> </td><td style="padding-left:30px;">'.$PriceTotalGrossToShow.'</td></tr>';
            if(!empty($DownloadKey) && $IsDownload && $captureStatus=='COMPLETED' && empty($beforeFilter['ownKeys'][$realId]['DownloadKey'])){
	            $SENT_POST['ownKeys'][$realId] = $DownloadKey;
	            $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$DownloadKey.'</td></tr>';
            }else if(!empty($ServiceKey) && $IsService && $captureStatus=='COMPLETED' && empty($beforeFilter['ownKeys'][$realId]['ServiceKey'])){
	            $SENT_POST['ownKeys'][$realId] = $ServiceKey;
	            $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$ServiceKey.'</td></tr>';
            }else if(!empty($beforeFilter['ownKeys'][$realId]['DownloadKey'])){
	            $DownloadKey = $beforeFilter['ownKeys'][$realId]['DownloadKey'];
	            $SENT_POST['ownKeys'][$realId] = $DownloadKey;
	            $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$DownloadKey.'</td></tr>';
            }else if(!empty($beforeFilter['ownKeys'][$realId]['ServiceKey'])){
	            $ServiceKey = $beforeFilter['ownKeys'][$realId]['ServiceKey'];
	            $SENT_POST['ownKeys'][$realId] = $ServiceKey;
	            $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow .' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$ServiceKey.'</td></tr>';
            }

			if(!empty($AlternativeShippingGross)){
				$AlternativeShippingGrossToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$AlternativeShippingGross);
				$ordersummaryandpage .= '<tr><td><b>'.$language_AlternativeShipping.':</b> </td><td style="padding-left:30px;">'.$AlternativeShippingGrossToShow.'</td></tr>';
			}

	        $ordersummaryandpage .= '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';

            $SaleTitle = contest_gal1ery_htmlentities_and_preg_replace($item['name']);
            $SaleDescription = contest_gal1ery_htmlentities_and_preg_replace($item['description']);

	        $wpdb->query( $wpdb->prepare(
                "
					INSERT INTO $tablename_ecommerce_orders_items  
					(
					 id, pid, GalleryID, ParentOrder,TaxValueUnit,TaxValueTotal,
					 PriceUnitNet, PriceUnitGross, PriceTotalNet, PriceTotalGross, 
					 Units, SaleTitle,SaleDescription,IsShipping,IsAlternativeShipping,IsDownload,IsService,IsUpload,
					 AlternativeShippingGross,AlternativeShippingNet,AlternateShippingTaxValue,
					 TaxPercentage,
					 DownloadKey,ServiceKey,WpUploads,RawData,WpUploadFilesForSale)
					VALUES ( 
					        %s,%d,%d,%d,%f,%f,
					        %f,%f,%f,%f,
					        %d,%s,%s,%d,%d,%d,%d,%d,
					        %f,%f,%f,
					        %f,
					        %s,%s,%s,%s,%s)
				",
                '',$realId,$GalleryID,$ParentOrder,$TaxValueUnit,$TaxValueTotal,
                $PriceUnitNet, $PriceUnitGross, $PriceTotalNet, $PriceTotalGross,
                intval($item['quantity']),$SaleTitle,$SaleDescription,$IsShipping,$IsAlternativeShipping,$IsDownload,$IsService,$IsUpload,
                $AlternativeShippingGross,$AlternativeShippingNet,$AlternateShippingTaxValue,
                $TaxPercentage,
                $DownloadKey,$ServiceKey,$WpUploads,$RawData,$WpUploadFilesForSale
            ));

           $wpdb->show_errors(); //setting the Show or Display errors option to true

	        //var_dump('$wpdb->print_error();');
	        //var_dump($wpdb->print_error());

            $itemId = $wpdb->insert_id;

	        //var_dump('$itemId');
	        //var_dump($itemId);

	        //var_dump('$IsDownload');
	        //var_dump($IsDownload);

            // copy sold wp uploads to sold folder so always available
	        /*
            if($IsDownload){
                foreach (unserialize($WpUploads) as $WpUpload){
	                //var_dump('$WpUpload sold');
	                //var_dump($WpUpload);
                    $soldDownloadsFolderWpUpload = $soldDownloadsFolder.'/wp-upload-id-'.$WpUpload;
	                //var_dump('$soldDownloadsFolderWpUpload');
	                //var_dump($soldDownloadsFolderWpUpload);
                    if(!is_dir($soldDownloadsFolderWpUpload)){// then must already exist for wp upload id
	                    mkdir($soldDownloadsFolderWpUpload,0755,true);
	                    $ecommerceFileFolder = $wp_upload_dir['basedir'].'/contest-gallery/gallery-id-'.$GalleryID.'/ecommerce/real-id-'.$realId.'/wp-upload-id-'.$WpUpload;
	                    //var_dump('$ecommerceFileFolder');
	                    //var_dump($ecommerceFileFolder);
                        $folderData = scandir($ecommerceFileFolder);
                        foreach ($folderData as $filename){
                            if($filename!='.' && $filename!='..'){
	                            //var_dump('copy');
                                copy($ecommerceFileFolder.'/'.$filename, $soldDownloadsFolderWpUpload.'/'.$filename);
                            }
                        }
                    }
                }
            }*/
        }

	    if(!empty($ShippingGross) && $itemHasDefaultShipping){
		    $ShippingGrossToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$ShippingGross);
		    $ordersummaryandpage .= '<tr><td><b>'.$language_StandardShippingCosts.':</b> </td><td style="padding-left:30px;">'.$ShippingGrossToShow.'</td></tr>';
	    }

	    $totalPriceToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$PriceTotalGrossItemsWithShipping);

	    $ordersummaryandpage .= '<tr><td><br><b>'.$language_TotalGross.':</b></td><td style="padding-left:30px;"><br>'.$totalPriceToShow.'</td></tr>';

	    $ordersummaryandpage .= '</table>';

        if($itemHasTax || ($itemHasShipping && $TaxPercentageDefault)){
	        $hasTaxToShow = true;
        }

        $invoiceFilePath = '';
        $HasInvoice = false;
	    $cgProVersion = contest_gal1ery_key_check();

	    ###PRO###
	    if($CreateInvoice && $cgProVersion && cg_get_version()=='contest-gallery-pro'){
		    $HasInvoice = true;
		    include ('ecommerce-payment-processing-create-invoice.php');
	    }
	    ###PRO-END###

	    //var_dump(        'sale id sale item invoice and mail');
	    //var_dump(        '$OrderId');
	    //var_dump(        $OrderId);

	    $ecommerce_options = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = 1");

	    $SendOrderConfirmationMail = $ecommerce_options->SendOrderConfirmationMail;
        $header = $ecommerce_options->OrderConfirmationMailHeader;
        $reply = $ecommerce_options->OrderConfirmationMailReply;
        $cc = $ecommerce_options->OrderConfirmationMailCc;
        $bcc = $ecommerce_options->OrderConfirmationMailBcc;
        $CreateInvoice = $ecommerce_options->CreateInvoice;
        $subject = contest_gal1ery_convert_for_html_output($ecommerce_options->OrderConfirmationMailSubject);
        $Msg = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerce_options->OrderConfirmationMail);
        $url = trim(sanitize_text_field($ecommerce_options->OCMailOrderSummaryURL))."?order_id=$OrderId";

        $replacePosUrl = '$order$';

	    $ordersummaryandpage .= '<br><a href="'.$ecommerce_options->ForwardAfterPurchaseUrl.'?cg_order='.$OrderIdHash.'" target="_blank" >'.$language_FullOrderDetails.(($hasDownload) ? ' ('.$language_Download.')' : '').'</a><br>';

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

        global $cgMailAction;
        global $cgMailGalleryId;
        global $cgIsGeneral;
        $cgMailAction = "Order confirmation e-mail";
        $cgMailGalleryId = 0;
        $cgIsGeneral = true;
        add_action( 'wp_mail_failed', 'cg_on_wp_mail_error', 10, 1 );

	    if($CreateInvoice && $SendInvoice && $SendOrderConfirmationMail == 1){
            if($InvoiceNumber){
	            $invoiceFilePathForUser = $ecommerceInvoicesFolder."/invoice-".$InvoiceNumber.'.pdf';
	            copy($invoiceFilePath, $invoiceFilePathForUser);
	            $attachments = array($invoiceFilePathForUser);
            }else{
	            $attachments = array($invoiceFilePath);
            }
		    //var_dump('before wp mail');
            wp_mail($payer_email, $subject, $Msg, $headers, $attachments);
		    if($InvoiceNumber){
                unlink($invoiceFilePathForUser);
		    }
        }else{
		    if($SendOrderConfirmationMail == 1){
			    wp_mail($payer_email, $subject, $Msg, $headers);
		    }
        }

        //$attachments = array(ABSPATH . '/uploads/abc.png');
        //wp_mail($email, 'Testing Attachment' , 'This is subscription','This is for header',$attachments);
	    //var_dump('ForwardAfterPurchaseUrl');
	    //var_dump($ecommerce_options->ForwardAfterPurchaseUrl."?cg_order=$OrderIdHash");

	    apply_filters( 'cg_filter_after_ecommerce_payment_processing', $SENT_POST);

		echo "###ORDERIDHASH###$OrderIdHash###ORDERIDHASH###";

    }
}


?>