<?php
if(!function_exists('cg_ecommerce_payment_processing_create_invoice')) {
    function cg_ecommerce_payment_processing_create_invoice($id) {

        global $wpdb;
        $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
        $tablename_ecommerce_invoice_options = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";

        include(__DIR__ ."/../../../check-language-ecommerce.php");

        $wp_upload_dir = wp_upload_dir();

        $selectSQLecommerceOptions = cg_get_ecommerce_options();

        $order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$id'");

        $LogForDatabase = unserialize($order->LogForDatabase);
        $PaymentType = $order->PaymentType;

	    if(!empty($LogForDatabase['PriceDivider'])){
            $PriceDivider = $LogForDatabase['PriceDivider'];
        }else{
            $PriceDivider = $selectSQLecommerceOptions->PriceDivider;
        }
        $ShippingNet = round(floatval($LogForDatabase['ShippingNet']),2);
        $TaxPercentageDefault = $LogForDatabase['TaxPercentageDefault'];
        $TaxPercentageDefaultToShow = number_format(floatval($TaxPercentageDefault),2,$PriceDivider);
        $PriceTotalNetItems = cg_ecommerce_get_price_total_net_items($LogForDatabase);
        $alternativeShippingTotal = round(floatval($LogForDatabase['alternativeShippingTotal']),2);
        $alternativeShippingTotalTaxValue = round(floatval($LogForDatabase['alternativeShippingTotalTaxValue']),2);
        $PriceTotalNetItemsWithShipping = $PriceTotalNetItems+($alternativeShippingTotal-$alternativeShippingTotalTaxValue);
        $PriceTotalGrossItemsWithShipping = round(floatval($LogForDatabase['purchase_units'][0]['amount']['value']),2);
        $DefaultShipping = round(floatval($LogForDatabase['DefaultShipping']),2);
        $DefaultTax = round(floatval($LogForDatabase['TaxPercentageDefault']),2);
        $ShippingTaxValue = round(floatval($LogForDatabase['ShippingTaxValue']),2);
        $ShippingGross = round(floatval($LogForDatabase['ShippingGross']),2);

        $time = $order->Tstamp;
        $CreatedMonth = cg_get_time_based_on_wp_timezone_conf($time,'n');
        $IsTest = $order->IsTest;
        $OrderId = $id;
        $OrderIdHash = $order->OrderIdHash;
        $InvoiceAddressCountryShort = $order->InvoiceAddressCountryShort;
        $InvoiceAddressFirstName = $order->InvoiceAddressFirstName;
        $InvoiceAddressLastName = $order->InvoiceAddressLastName;
        $InvoiceAddressCompany = $order->InvoiceAddressCompany;
        $InvoiceAddressLine1 = $order->InvoiceAddressLine1;
        $InvoiceAddressLine2 = $order->InvoiceAddressLine2;
        $InvoiceAddressStateShort = $order->InvoiceAddressStateShort;
        $InvoiceAddressCity = $order->InvoiceAddressCity;
        $InvoiceAddressPostalCode = $order->InvoiceAddressPostalCode;
        $InvoiceAddressStateTranslation = $order->InvoiceAddressStateTranslation;
        $InvoiceAddressCountryTranslation = $order->InvoiceAddressCountryTranslation;
        $EcommerceTaxNr = $order->TaxNr;
        $EUshortcodes = cg_get_eu_countries_shortcodes();
        $hasTaxToShow = cg_ecommerce_has_tax_to_show($LogForDatabase);
        $itemHasShipping = cg_ecommerce_check_item_has_shipping($LogForDatabase);
        $itemHasDefaultShipping = cg_ecommerce_check_item_has_default_shipping($LogForDatabase);
        $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
        $CurrencyShort = $LogForDatabase['purchase_units'][0]['amount']['currency_code'];
        $CurrencyPosition = $LogForDatabase['CurrencyPosition'];

        $InvoiceAddressForHtmlOutput = cg_ecommerce_create_invoice_address_for_html_output($InvoiceAddressFirstName,$InvoiceAddressLastName,$InvoiceAddressCompany,$InvoiceAddressLine1,$InvoiceAddressLine2,$InvoiceAddressStateShort,$InvoiceAddressCountryShort,$InvoiceAddressCity,$InvoiceAddressPostalCode,$InvoiceAddressStateTranslation,$EUshortcodes,$InvoiceAddressCountryTranslation,$EcommerceTaxNr,$language_VatNumber);

        $CreatedMonthWith0 = cg_get_time_based_on_wp_timezone_conf($time,'m');
        $CreatedYear = cg_get_time_based_on_wp_timezone_conf($time,'Y');
        $day = date('d',$time);

	    $PayerEmail = $order->PayerEmail;

	    $invoiceName = 'invoice-'.$CreatedYear.$CreatedMonthWith0.$day.'-'.date('H').date('i').date('s').'-'.$PayerEmail;

        if($IsTest){
            $databaseToSaveInvoiceFilePath = '/contest-gallery/ecommerce/test-environment/invoices/'.$CreatedYear.'/'.$CreatedMonthWith0;
        }else{
            $databaseToSaveInvoiceFilePath = '/contest-gallery/ecommerce/live-environment/invoices/'.$CreatedYear.'/'.$CreatedMonthWith0;
        }

        $ecommerceInvoicesFolder = $wp_upload_dir['basedir'].$databaseToSaveInvoiceFilePath;

        if(!is_dir($ecommerceInvoicesFolder)){
            mkdir($ecommerceInvoicesFolder, 0755, true);
        }

        $ecommerceInvoicesHtaccess = $ecommerceInvoicesFolder.'/.htaccess';

        if(!file_exists($ecommerceInvoicesHtaccess)){
            $denyFromAllContent = <<<HEREDOC
<Files "*">
order deny, allow
deny from all
</Files>
HEREDOC;
            file_put_contents($ecommerceInvoicesHtaccess,$denyFromAllContent);
            chmod($ecommerceInvoicesHtaccess, 0640);// no read for others!!!
        }


        $pdfAuthor = get_option('blogname');
        $blogName = get_option('blogname');
        $databaseToSaveInvoiceFilePath = $databaseToSaveInvoiceFilePath.'/'.$invoiceName;
        $invoiceFilePath = $ecommerceInvoicesFolder."/".$invoiceName.'.pdf';

        $selectSQLecommerceInvoiceOptions = cg_get_ecommerce_invoice_options();
        $CreateAndSetInvoiceNumber = $selectSQLecommerceInvoiceOptions->CreateAndSetInvoiceNumber;

        if($IsTest){
            //var_dump('$Environment123');
            //var_dump($Environment);
            $lastSale = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id > 0 AND IsTest = 1 AND id != $id ORDER BY id DESC LIMIT 1");
            $lastSaleWithCustomNumber = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id > 0 AND IsTest = 1 AND id != $id AND InvoiceNumber !='' AND InvoiceNumberLogicCustomNumber IS NOT NULL AND TRIM(InvoiceNumberLogicCustomNumber) <> '' ORDER BY id DESC LIMIT 1");
            //var_dump('$lastSale 4445556666');
            //var_dump($lastSale);
            $ResetInvoiceNumberNextInvoice = $selectSQLecommerceInvoiceOptions->ResetCustomNumberNextInvoiceTest;
            $InvoiceNumberLogicSelect = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicSelectTest;
            $InvoiceNumberLogicOwnPrefix = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicOwnPrefixTest;
            $InvoiceNumberLogicCustomNumber = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicCustomNumberTest;
        }else{
            $lastSale = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id > 0 AND IsTest = 0 AND id != $id  ORDER BY id DESC LIMIT 1");
            $lastSaleWithCustomNumber = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id > 0 AND IsTest = 0 AND id != $id AND InvoiceNumber !='' AND InvoiceNumberLogicCustomNumber IS NOT NULL AND TRIM(InvoiceNumberLogicCustomNumber) <> '' ORDER BY id DESC LIMIT 1");

            $ResetInvoiceNumberNextInvoice = $selectSQLecommerceInvoiceOptions->ResetCustomNumberNextInvoiceLive;
            $InvoiceNumberLogicSelect = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicSelectLive;
            $InvoiceNumberLogicOwnPrefix = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicOwnPrefixLive;
            $InvoiceNumberLogicCustomNumber = $selectSQLecommerceInvoiceOptions->InvoiceNumberLogicCustomNumberLive;
        }

        $InvoiceNumber = '';
//var_dump('00000 212312312');

//var_dump('$lastSale here');
//var_dump($lastSale);
//var_dump('$lastSale id');
//var_dump($lastSale->id);
//var_dump('$lastSale $lastSale->InvoiceNumber');
//var_dump($lastSale->InvoiceNumber);

//var_dump('$CreateAndSetInvoiceNumber');
//var_dump($CreateAndSetInvoiceNumber);

        if(!empty($CreateAndSetInvoiceNumber)){
            $InvoiceNumber = '';
            if((empty($lastSale) || empty($lastSale->InvoiceNumber)) && empty($lastSaleWithCustomNumber) ){
                //var_dump('sdfasdf 1123 32234');
                if($InvoiceNumberLogicSelect=='timestamp'){
                    $InvoiceNumber = $time;
                }else {
                    if((!empty($InvoiceNumberLogicSelect) && $InvoiceNumberLogicSelect!='unset') || !empty($InvoiceNumberLogicOwnPrefix) || !empty($InvoiceNumberLogicCustomNumber)){
                        $InvoiceNumber = '';
                        if(!empty($InvoiceNumberLogicSelect) && $InvoiceNumberLogicSelect!='unset'){
                            if($InvoiceNumberLogicSelect=='year'){
                                $InvoiceNumber .= $CreatedYear.'-';
                            }
                            if($InvoiceNumberLogicSelect=='month'){
                                $InvoiceNumber .= $CreatedMonthWith0.'-';
                            }
                            if($InvoiceNumberLogicSelect=='year-month'){
                                $InvoiceNumber .= $CreatedYear.'-'.$CreatedMonthWith0.'-';
                            }
                        }
                        if(!empty($InvoiceNumberLogicOwnPrefix)){
                            $InvoiceNumber .= $InvoiceNumberLogicOwnPrefix.'-';
                        }
                        if(!empty($InvoiceNumberLogicCustomNumber)){
                            $InvoiceNumber .= $InvoiceNumberLogicCustomNumber;
                        }
                    }
                }
            }else if(!empty($lastSale) && (!empty($lastSale->InvoiceNumber) || !empty($lastSaleWithCustomNumber))){// $lastSale->InvoiceNumber has to be not empty then because of the condition above
                //var_dump('1123 32234');
                //file_put_contents(__DIR__.'/0.txt','0');
                if($InvoiceNumberLogicSelect=='timestamp'){
                    //var_dump('44545 9877987');
                    $InvoiceNumber = $time;
                }else if($InvoiceNumberLogicSelect=='unset') {
                    if(!empty($InvoiceNumberLogicOwnPrefix)){
                        //var_dump('unset $InvoiceNumberLogicOwnPrefix');
                        //var_dump($InvoiceNumberLogicOwnPrefix);
                        $InvoiceNumber .= $InvoiceNumberLogicOwnPrefix.'-';
                    }
                    //var_dump(44444);
                    //var_dump($lastSaleWithCustomNumber->CreatedYear);
                    //var_dump($CreatedYear);
                    if(!empty($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber) && $ResetInvoiceNumberNextInvoice!=1 && $lastSaleWithCustomNumber->CreatedYear==$CreatedYear){// reset every year for unset also, this why year check
                        //var_dump(22222233333);
                        if($lastSaleWithCustomNumber->CreatedYear === $CreatedYear){ // then increase
                            $cg_increase_number_result = cg_increase_number($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber,$InvoiceNumber);
                            $InvoiceNumber = $cg_increase_number_result['InvoiceNumber'];
                            $InvoiceNumberLogicCustomNumber = $cg_increase_number_result['InvoiceNumberLogicCustomNumber'];
                        }else{// then start new
                            $InvoiceNumber = $InvoiceNumber.$InvoiceNumberLogicCustomNumber;
                            $InvoiceNumberLogicCustomNumber = $InvoiceNumberLogicCustomNumber;
                        }
                    }else{
                        //var_dump(3333);
                        //var_dump($lastSaleWithCustomNumber->CreatedYear);
                        //var_dump($CreatedYear);
                        $InvoiceNumber = $InvoiceNumber.$InvoiceNumberLogicCustomNumber;
                        $InvoiceNumberLogicCustomNumber = $InvoiceNumberLogicCustomNumber;
                    }
                }else {
                    //var_dump('invoicenumber parts');
                    //var_dump($lastSale->InvoiceNumberLogicSelect);
                    //var_dump($lastSale->InvoiceNumberLogicOwnPrefix);
                    //var_dump($lastSale->InvoiceNumberLogicCustomNumber);

                    //var_dump('new prccessing');
                    $InvoiceNumber = '';
                    if($InvoiceNumberLogicSelect=='year'){
                        $InvoiceNumber .= $CreatedYear.'-';
                    }
                    if($InvoiceNumberLogicSelect=='month'){
                        $InvoiceNumber .= $CreatedMonthWith0.'-';
                    }
                    if($InvoiceNumberLogicSelect=='year-month'){
                        $InvoiceNumber .= $CreatedYear.'-'.$CreatedMonthWith0.'-';
                    }
                    if(!empty($InvoiceNumberLogicOwnPrefix)){
                        $InvoiceNumber .= $InvoiceNumberLogicOwnPrefix.'-';
                    }
                    if(!empty($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber) && $ResetInvoiceNumberNextInvoice!=1){
                        // #toDo continue from here with $InvoiceNumberLogicCustomNumber
                        //var_dump('$lastSale->InvoiceNumberLogicCustomNumber ');
                        //var_dump($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber);
                        //var_dump('new prccessingInvoiceNumberLogicCustomNumber ');
                        if($InvoiceNumberLogicSelect=='year' && $lastSaleWithCustomNumber->CreatedYear==$CreatedYear){
                            $cg_increase_number_result = cg_increase_number($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber,$InvoiceNumber);
                            $InvoiceNumber = $cg_increase_number_result['InvoiceNumber'];
                            $InvoiceNumberLogicCustomNumber = $cg_increase_number_result['InvoiceNumberLogicCustomNumber'];
                        }else if(($InvoiceNumberLogicSelect=='month' || $InvoiceNumberLogicSelect=='year-month') && $lastSaleWithCustomNumber->CreatedMonth==$CreatedMonth  && $lastSaleWithCustomNumber->CreatedYear==$CreatedYear){
                            $cg_increase_number_result = cg_increase_number($lastSaleWithCustomNumber->InvoiceNumberLogicCustomNumber,$InvoiceNumber);
                            $InvoiceNumber = $cg_increase_number_result['InvoiceNumber'];
                            $InvoiceNumberLogicCustomNumber = $cg_increase_number_result['InvoiceNumberLogicCustomNumber'];
                        }else{
                            //var_dump('$InvoiceNumberLogicCustomNumber if new month or year');
                            //var_dump($InvoiceNumberLogicCustomNumber);
                            //	file_put_contents(__DIR__.'/1.txt','1');
                            $InvoiceNumberLogicCustomNumber = $InvoiceNumberLogicCustomNumber;
                        }
                        //var_dump('$InvoiceNumber new generatetd');
                        //var_dump($cg_increase_number_result);
                    }else if(!empty($InvoiceNumberLogicCustomNumber)){
                        //		file_put_contents(__DIR__.'/2.txt','2');
                        $InvoiceNumber .= $InvoiceNumberLogicCustomNumber;
                        $InvoiceNumberLogicCustomNumber = $InvoiceNumberLogicCustomNumber;
                    }
                }
            }
        }
//var_dump('$InvoiceNumber after');
//var_dump($InvoiceNumber);
        if($ResetInvoiceNumberNextInvoice==1){
            if($IsTest){
                $wpdb->update(
                    "$tablename_ecommerce_invoice_options",
                    array('ResetCustomNumberNextInvoiceTest' => 0),
                    array('GeneralID' => 1),
                    array('%d'),
                    array('%d')
                );
            }else{
                $wpdb->update(
                    "$tablename_ecommerce_invoice_options",
                    array('ResetCustomNumberNextInvoiceLive' => 0),
                    array('GeneralID' => 1),
                    array('%d'),
                    array('%d')
                );
            }
        }

        require_once(__DIR__.'/../../../v10/v10-admin/ecommerce/libs/tcpdf/tcpdf.php');

// Erstellung des PDF Dokuments
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $invoiceName = $InvoiceNumber.'.pdf';
        $InvoicePartNumber = $InvoiceNumber;
        $language_Nr_Part = $language_Nr.':';
        if($CreateAndSetInvoiceNumber==0){
            $language_Nr_Part = '';
        }

        $InvoicerHeaderData = contest_gal1ery_convert_for_html_output($selectSQLecommerceInvoiceOptions->InvoicerHeaderData);
        $InvoicePartInvoicer = $InvoicerHeaderData;
        $headerContestGallery = "$InvoicerHeaderData<br><br>";


        if(!empty($selectSQLecommerceInvoiceOptions->InvoiceNote)){
            $InvoiceNote = unserialize($selectSQLecommerceInvoiceOptions->InvoiceNote);
            //var_dump('$BillingAddressCountry');
            //var_dump($InvoiceAddressCountryShort);
            //echo "<pre>";
            //print_r($InvoiceNote);
            //echo "</pre>";
            $InvoiceNote = (!empty($InvoiceNote[$InvoiceAddressCountryShort])) ? $InvoiceNote[$InvoiceAddressCountryShort] : $InvoiceNote['default'];
        }else{
            $InvoiceNote = '';
        }

	    $InvoiceNote = contest_gal1ery_convert_for_html_output_without_nl2br($InvoiceNote);

        $country_codes_paypal_csv = __DIR__.'/../../../../functions/ecommerce/general/country_codes_paypal.csv';
        $countriesArray = [];
        $countriesArray['default'] = 'Default';

        foreach ($countriesArray as $country_code => $country){
            // can be used now here if required
            //echo "<option value='".$country_code."' >".$country."</option>";
        }

        $date = date("Y-m-d");

        $InvoicePartRecipient = $InvoiceAddressForHtmlOutput;

		$translationPaidType = $language_PayPal;
		if($PaymentType == 'stripe'){
			$translationPaidType = $language_Stripe;
		}

        $InvoicePartInfo = $language_InvoiceDate.': '.$date.' (YYYY-MM-DD)<br>
'.$language_PaymentDate.': '.$date.' (YYYY-MM-DD)<br>
'.$language_OrderNumber.': '.$OrderIdHash.'<br>
'.$language_PaidVia.': '.$translationPaidType;

        $html = '';

        include(__DIR__.'/../../../v10/v10-frontend/ecommerce/invoice-parts/top.php');

//var_dump('$html123');
//echo $html;
//var_dump('4444');

        $htmlMain = '';

        $netPriceForSummery = 0;
        $taxColumnHeader = ($hasTaxToShow || true) ? '<td style="text-align:right;"><b>'.$language_TaxRate.'</b></td>' : '';
        $htmlMain .= '
<table cellpadding="5" cellspacing="0" style="width: 100%;" border="0" style="width: 100%;table-layout: fixed;">
 <tr style="background-color: #cccccc; padding:5px;">
     <td style="text-align:left;width:85px;"><b>'.$language_Quantity.'</b></td>
     <td style="text-align:left;width:170px;"><b>'.$language_Title.'</b></td>
     '.$taxColumnHeader.'
     <td style="text-align:right;"><b>'.$language_PriceUnitNet.'</b></td>
     <td style="text-align:right;"><b>'.$language_PriceTotalNet.'</b></td>
 </tr>';


        foreach ($LogForDatabase['purchase_units'][0]['items'] as $item){

            $priceStringToShowSingle = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$item['PriceUnitNet']);// is already net price

            $priceStringToShowTotal = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$item['PriceTotalNet']);

            $taxColumnToShow = ($hasTaxToShow || true) ? '<td  style="text-align:right;">'.$item['TaxPercentageToShow'].'</td>' : '';

            $freeShippingStringAlternativeString = '';
            if(!empty($item['AlternativeShippingGross']) && $item['AlternativeShippingGross']>0){
                $freeShippingStringAlternativeString = "<br>($language_FreeShipping)";
            }

            $htmlMain .= '<tr>
     <td style="text-align:left;">'.$item['quantity'].'</td>
     <td style="text-align:left;">'.contest_gal1ery_convert_for_html_output_without_nl2br($item['name']).$freeShippingStringAlternativeString.'</td>
     '.$taxColumnToShow.'
     <td  style="text-align:right;">'.$priceStringToShowSingle.'</td>
     <td  style="text-align:right;">'.$priceStringToShowTotal.'</td>
    </tr>';

        }

        $totalShipping = 0;
        $DefaultShippingGrossNetDifference = 0;

        $isDefaultShippingProcessed = false;
        $isFreeShipping = true;

        foreach ($LogForDatabase['purchase_units'][0]['items'] as $item){
            if(!empty($item['IsShipping']) && !$isDefaultShippingProcessed && empty($item['alternative_shipping_amount_value_gross']) && intval($ShippingNet)!=0){
                $isDefaultShippingProcessed = true;

                $totalShipping = $totalShipping + $DefaultShipping;

                $taxToShow = ($hasTaxToShow) ? "$DefaultTax%" : "0%";

                $DefaultShippingGross = $DefaultShipping;
                //var_dump('$DefaultShippingGross');
                //var_dump($DefaultShippingGross);
                $DefaultShippingTax = round($DefaultShippingGross/100*$DefaultTax,2);
                //var_dump('$DefaultShippingTax');
                //var_dump($DefaultShippingTax);
                $DefaultShippingNet = $DefaultShippingGross - $DefaultShippingTax;
                //var_dump('$DefaultShippingNet');
                //var_dump($DefaultShippingNet);
                $netPriceForSummery = $netPriceForSummery + $DefaultShippingNet;

                $DefaultShippingNetToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$ShippingNet);

                $taxColumnToShow = ($hasTaxToShow || true) ? '<td style="text-align:right;">'.$TaxPercentageDefaultToShow.'%</td>' : '';
                $isFreeShipping = false;

                $htmlMain .= '<tr>
         <td style="text-align:left;">1</td>
         <td style="text-align:left;">'.$language_Shipping.'</td>
         '.$taxColumnToShow.'
         <td style="text-align:right;">'.$DefaultShippingNetToShow.'</td>
         <td style="text-align:right;">'.$DefaultShippingNetToShow.'</td>
        </tr>';

            }
        }

        if($itemHasShipping){
            foreach ($LogForDatabase['purchase_units'][0]['items'] as $item){
                // gross not net here because might be 100% tax theoretically
                if(!empty($item['AlternativeShippingGross']) && intval($item['AlternativeShippingGross'])!=0){

                    $alternativeShippingValueNetToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$item['AlternativeShippingNet']);

                    $taxColumnToShow = ($hasTaxToShow || true) ? '<td style="text-align:right;">'.$TaxPercentageDefaultToShow.'%</td>' : '';

                    $htmlMain .= '<tr>
     <td style="text-align:left;">1</td>
     <td style="text-align:left;">'.$language_AlternativeShipping.':<br>'.$item['name'].'</td>
     '.$taxColumnToShow.'
     <td style="text-align:right;">'.$alternativeShippingValueNetToShow.'</td>
     <td style="text-align:right;">'.$alternativeShippingValueNetToShow.'</td>
    </tr>';
                }
            }
        }

        $htmlMain .= '</table>';

        $PriceTotalNetItemsToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$PriceTotalNetItemsWithShipping);

        $htmlMain .= '
<br><br><table cellpadding="5" cellspacing="0" style="width: 100%;" border="0">
 <tr>
     <td style="padding:5px;text-align: right;"><b>'.$language_TotalNet.'</b></td>
     <td style="padding:5px;text-align: right;"><b>'.$PriceTotalNetItemsToShow.'</b></td>
 </tr>';

// cummulation tax from items here
        $TaxesArray = [];
        $TaxesArray[$TaxPercentageDefaultToShow] = 0;

        if($itemHasDefaultShipping && intval($ShippingGross)){
            $TaxesArray[$TaxPercentageDefaultToShow] = $ShippingTaxValue;
        }

//var_dump('$TaxesArray first print');
//var_dump($TaxesArray);

//var_dump("$ post purchase items");
        /*echo "<pre>";
        print_r($LogForDatabase['purchase_units'][0]['items']);
        echo "</pre>";*/

// has to be named $itemValue otherwise strange php error cause usage of $item again like before
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $itemValue){

            /*var_dump("item print");
            echo "<pre>";
            print_r($itemValue);
            echo "</pre>";

            var_dump('$key1');
            var_dump($key);*/

            if(!isset($TaxesArray[$itemValue['TaxPercentageToShow']])){
                $TaxesArray[$itemValue['TaxPercentageToShow']] = 0;
            }
// have to be done with key, $LogForDatabase['purchase_units'][0]['items'][$key] always
            if(!empty($LogForDatabase['purchase_units'][0]['items'][$key]['tax']) && !empty($LogForDatabase['purchase_units'][0]['items'][$key]['tax']['value'])){
                //var_dump('$item name');
                //var_dump($LogForDatabase['purchase_units'][0]['items'][$key]['name']);
                //var_dump('$key');
                //var_dump($key);
                //var_dump('$item[tax value]');
                //var_dump($LogForDatabase['purchase_units'][0]['items'][$key]['tax']['value']);
                //var_dump('$itemValue["TaxPercentageToShow"]');
                //var_dump($itemValue['TaxPercentageToShow']);
                //var_dump('$itemValue["TaxValueTotal"]');
                //var_dump($itemValue['TaxValueTotal']);
                $TaxesArray[$itemValue['TaxPercentageToShow']] = $TaxesArray[$itemValue['TaxPercentageToShow']] +  $itemValue['TaxValueTotal'];
                //var_dump('$TaxesArray');
                //var_dump($TaxesArray);
            }
//var_dump('$TaxesArray 123');
            /*echo "<pre>";
                print_r($TaxesArray);
            echo "</pre>";*/

            foreach ($TaxesArray as $key => $value){
                //var_dump('$key');
                //var_dump($key);
            }


            //var_dump('$TaxPercentageDefaultToShow 123');
            //var_dump($TaxPercentageDefaultToShow);
            //var_dump(123);
            //var_dump($TaxesArray[$TaxPercentageDefaultToShow]);
            //var_dump(346);
            //var_dump('AlternateShippingTaxValue 123');
            //var_dump($itemValue['AlternateShippingTaxValue']);
            if(!empty($itemValue['AlternateShippingTaxValue'])){
                $TaxesArray[$TaxPercentageDefaultToShow] = $TaxesArray[$TaxPercentageDefaultToShow] + $itemValue['AlternateShippingTaxValue'];
            }

        }

// cummulation tax from shipping here
        /*$TaxesArray = [];
        $IsDefaultShippingOneTimeSet = false;
        foreach ($LogForDatabase['purchase_units'][0]['items'] as $item){

            if(!isset($TaxesArray[$DefaultTax])){
                $TaxesArray[$DefaultTax] = 0;
            }

            if(!empty($item['extra_shipping_amount_value'])){
                $TaxesArray[$DefaultTax] = $TaxesArray[$DefaultTax] + $item['extra_shipping_amount_value'];
            }else if(empty($item['extra_shipping_amount_value']) && !empty($item['IsShipping']) && !$IsDefaultShippingOneTimeSet){
                $IsDefaultShippingOneTimeSet = true;
                $TaxesArray[$DefaultTax] = $TaxesArray[$DefaultTax] + $DefaultShipping;
            }

        }*/

        /*foreach ($LogForDatabase['purchase_units'][0]['items'] as $item){
            $totalPrice = $totalPrice + $item['unit_amount']['value'];
        }

        $totalPrice = $totalPrice + $totalShipping;*/
        $totalPrice = $netPriceForSummery;
//var_dump('$TaxesArray');
        /*echo "<pre>";
        print_r($TaxesArray);
        echo "</pre>";*/
        foreach ($TaxesArray as $TaxPercentage => $TaxValueSum){
            $variants = ['0','0.0','0.00','0,0','0,00'];
            if(in_array($TaxValueSum,$variants)===false){
                $totalPrice = $totalPrice+$TaxValueSum;
                $TaxValueSumToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$TaxValueSum);
                //var_dump('$TaxValueSumToShow');
                //var_dump($TaxValueSumToShow);
                $htmlMain .= '
 <tr>
     <td style="padding:5px;text-align: right;">+'.$language_TaxRate.' '.$TaxPercentage.'%</td>
     <td style="padding:5px;text-align: right;">'.$TaxValueSumToShow.'</td>
 </tr>';
            }
        }

        $totalPriceToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$PriceTotalGrossItemsWithShipping);

	    $filterData = apply_filters( 'cg_custom_total_price_to_show', $PriceTotalGrossItemsWithShipping,$totalPriceToShow);

	    if(!empty($filterData['cgCustomTotalPriceToShow'])){
		    $totalPriceToShow = $filterData['cgCustomTotalPriceToShow'];
	    }

        $freeShippingString = '';
        if($itemHasDefaultShipping && $isFreeShipping){
            $freeShippingString = "<br>($language_FreeShipping)";
        }

        $htmlMain .= '
<tr>
     <td style="padding:5px;text-align: right;"><b>'.$language_TotalGross.'</b>'.$freeShippingString.'</td>
     <td style="padding:5px;text-align: right;"><b>'.$totalPriceToShow.'</b></td>
 </tr>';

        $htmlMain .= '</table>';

        $InvoicePartMain = $htmlMain;

        $html .= $htmlMain;

        /*
        var_dump('$html444');
        echo $html;
        var_dump('555');
         *
         * $html .= '<tr style="text-align: center;">
                 <td>1</td>
                 <td>'.$translationDefaultShipping.'</td>
                 '.$taxColumnField.'
                 <td>'.$DefaultShippingToShow.'</td>
                </tr>';
        $html .= '</table>';

        $html .= '
        <br><br><table cellpadding="5" cellspacing="0" style="width: 100%;" border="0">
         <tr>
             <td style="padding:5px;text-align: right;"><b>Total Net</b></td>
             <td style="padding:5px;text-align: right;"><b>'.$netPriceForSummery.'</b></td>
         </tr>';
        $html .= '</table>';*/

        $InvoicePartNote = $InvoiceNote;

        if(!empty($InvoiceNote)){
            include(__DIR__.'/../../../v10/v10-frontend/ecommerce/invoice-parts/bottom.php');
        }

// Dokumenteninformationen
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($pdfAuthor);
        $pdf->SetTitle($InvoiceNumber);
        $pdf->SetSubject($InvoiceNumber);


// Header und Footer Informationen
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Auswahl des Font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Auswahl der MArgins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Automatisches Autobreak der Seiten
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Image Scale
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Schriftart
        $pdf->SetFont('dejavusans', '', 10);

// Neue Seite
        $pdf->AddPage();

// Fügt den HTML Code in das PDF Dokument ein
        $pdf->writeHTML($html, true, false, true, false, '');

//Ausgabe der PDF
//Variante 1: PDF direkt an den Benutzer senden:
        $pdf->Output($invoiceFilePath, 'F');// Mit I nur output!!!!

//var_dump('output file path');
//var_dump($invoiceFilePath);

//sender
        /*$reply = $ecommerce_options->OrderConfirmationMailReply;
        $cc = $ecommerce_options->OrderConfirmationMailCc;
        $bcc = $ecommerce_options->OrderConfirmationMailBcc;
        $header = $ecommerce_options->OrderConfirmationMailHeader;
        $subject = $ecommerce_options->OrderConfirmationMailSubject;
        $Msg = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerce_options->OrderConfirmationMail);

    //email body content
        /*    $htmlContent = '<h1>PHP Email with Attachment by CodexWorld</h1>
            <p>This email has sent from PHP script with attachment.</p>';*/

//header for sender info
        /*$headers = "From: $header"." <".$reply.">";

    //boundary
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x".$semi_rand."x";

    //headers for attachment
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"".$mime_boundary."\"";

    //multipart boundary
        $message = "--".$mime_boundary."\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $Msg . "\n\n";

    //preparing attachment
        if(!empty($file) > 0){
            if(is_file($file)){
                $message .= "--".$mime_boundary."\n";
                $fp =    @fopen($file,"rb");
                $data =  @fread($fp,filesize($file));

                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n" .
                    "Content-Description: ".basename($file)."\n" .
                    "Content-Disposition: attachment;\n" . " filename=\"".basename($file)."\"; size=".filesize($file).";\n" .
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
        $message .= "--".$mime_boundary."--";
        $returnpath = "-f" . $reply;

        // Bcc email
        $headers .= "\nReply-To: ".$reply;
        if($cc){
            $headers .= "\nBcc: ".$cc;
        }
        if($bcc){
            $headers .= "\nBcc: ".$bcc;
        }

    //send email
        $mail = mail($payer_email, $subject, $message, $headers, $returnpath);*/

//var_dump('$InvoiceNumberLogicSelect');
//var_dump($InvoiceNumberLogicSelect);

        if($CreateAndSetInvoiceNumber!=1){
            $InvoiceNumber = '';
            $InvoiceNumberLogicOwnPrefix = '';
            $InvoiceNumberLogicCustomNumber = '';
            $InvoiceNumberLogicSelect = '';
        }else if($InvoiceNumberLogicSelect=='timestamp'){
            $InvoiceNumberLogicOwnPrefix = '';
            $InvoiceNumberLogicCustomNumber = '';
        }

// update main table
        $wpdb->update(
            "$tablename_ecommerce_orders",
            array('HasInvoice' => 1,'InvoiceNumber' => $InvoiceNumber,'InvoiceNumberLogicSelect' => $InvoiceNumberLogicSelect,'InvoiceNumberLogicOwnPrefix' => $InvoiceNumberLogicOwnPrefix,'InvoiceNumberLogicCustomNumber' => $InvoiceNumberLogicCustomNumber, 'InvoiceFilePath' => 'WP_UPLOAD_DIR'.$databaseToSaveInvoiceFilePath.'.pdf',
                'InvoicePartNumber' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartNumber)),'InvoicePartInvoicer' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartInvoicer)),'InvoicePartRecipient' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartRecipient)),'InvoicePartMain' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartMain)),'InvoicePartInfo' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartInfo)),'InvoicePartNote' => contest_gal1ery_htmlentities_and_preg_replace(cg_remove_all_line_breaks($InvoicePartNote))),
            array('id' => $OrderId),
            array(
                '%d','%s','%s','%s','%s','%s',
                '%s','%s','%s','%s','%s','%s'
            ),
            array('%d')
        );


        $wpdb->show_errors(); //setting the Show or Display errors option to true

        return [
            'invoiceFilePath' => $invoiceFilePath,
            'InvoiceNumber' => $InvoiceNumber,
            'ecommerceInvoicesFolder' => $ecommerceInvoicesFolder
        ];

//var_dump('$wpdb-> ORDER print_error();');
//var_dump($wpdb->print_error());

    }
}


?>