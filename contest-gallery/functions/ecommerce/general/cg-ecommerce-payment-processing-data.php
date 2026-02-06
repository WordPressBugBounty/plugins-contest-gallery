<?php
if(!function_exists('cg_ecommerce_payment_processing_data')) {
    function cg_ecommerce_payment_processing_data($LogForDatabase, $OrderIdHash,$PayerEmail,$PayPalTransactionId,$time,$PriceDivider,$PaymentStatus,$ParentOrder, $PriceTotalGrossItemsWithShipping,$isWithSaveToDatabase,$isGenerateKeyAfterPurchase,$SaleOrderId,$isFullPaid,$OrderNumber){

        $itemHasDefaultShipping = cg_ecommerce_check_item_has_default_shipping($LogForDatabase);

        $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
        $CurrencyShort = $LogForDatabase['purchase_units'][0]['amount']['currency_code'];
        $CurrencyPosition = $LogForDatabase['CurrencyPosition'];
	    $itemHasShipping = false;
	    $hasDownload = false;
	    $itemHasTax = false;

	    global $wpdb;
        $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
        $tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";// will be get bottom via function
        $tablename_ecommerce_invoice_options = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";
        $tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
        $tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";

        include(__DIR__ ."/../../../check-language-ecommerce.php");

        $ordersummaryandpage = '<table>';
        $ordersummaryandpage .= '<tr><td><b>'.$language_OrderNumber.':</b><br><br></td><td style="padding-left:30px;">'.$OrderIdHash.'<br><br></td></tr>';

        foreach ($LogForDatabase['purchase_units'][0]['items'] as $key => $item){

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
            }elseif($item['IsService']){
                //var_dump('$IsService123');
                //var_dump($IsService);
                $IsService = true;
            }elseif($item['IsUpload']){
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

	        if(!empty($DownloadKeysCsvName) && $IsDownload && ($isWithSaveToDatabase || $isGenerateKeyAfterPurchase) && $isFullPaid){

	            $DownloadKey = cg_get_set_key($GalleryID,$realId,$DownloadKeysCsvName,'',$PayerEmail,$PayPalTransactionId,$time,$OrderNumber);

	            if($isGenerateKeyAfterPurchase){// then was not full paid before at ecommerce-payment-processing.php!!!
					$itemId = $LogForDatabase['purchase_units'][0]['items'][$key]['itemId'];
					$wpdb->update(
						"$tablename_ecommerce_orders_items",
						array('DownloadKey' => $DownloadKey),
						array('id' => $itemId),
						array('%s'),
						array('%d')
					);
				}

            }elseif($IsDownload && ($isWithSaveToDatabase || $isGenerateKeyAfterPurchase) && $isFullPaid){
				//var_dump('before cg_filter_get_own_key');
	            $filterData = apply_filters( 'cg_filter_get_own_key', $LogForDatabase, $realId);

				//var_dump('$filterData');
				//var_dump($filterData);

				if(!empty($filterData['cgOwnKey'])){
					$DownloadKey = $filterData['cgOwnKey'];
				}

	            if($isGenerateKeyAfterPurchase){// then was not full paid before at ecommerce-payment-processing.php!!!
		            $itemId = $LogForDatabase['purchase_units'][0]['items'][$key]['itemId'];
		            $wpdb->update(
			            "$tablename_ecommerce_orders_items",
			            array('DownloadKey' => $DownloadKey),
			            array('id' => $itemId),
			            array('%s'),
			            array('%d')
		            );
	            }

            }

            if(!empty($ServiceKeysCsvName) && $IsService && ($isWithSaveToDatabase || $isGenerateKeyAfterPurchase) && $isFullPaid){
                $ServiceKey = cg_get_set_key($GalleryID,$realId,'',$ServiceKeysCsvName,$PayerEmail,$PayPalTransactionId,$time,$OrderNumber);
	            if($isGenerateKeyAfterPurchase){// then was not full paid before at ecommerce-payment-processing.php!!!
		            $itemId = $LogForDatabase['purchase_units'][0]['items'][$key]['itemId'];
		            $wpdb->update(
			            "$tablename_ecommerce_orders_items",
			            array('ServiceKey' => $ServiceKey),
			            array('id' => $itemId),
			            array('%s'),
			            array('%d')
		            );
	            }
            }elseif($IsService && ($isWithSaveToDatabase || $isGenerateKeyAfterPurchase) && $isFullPaid){

	            $filterData = apply_filters( 'cg_filter_get_own_key', $LogForDatabase, $realId);

	            if(!empty($filterData['cgOwnKey'])){
		            $ServiceKey = $filterData['cgOwnKey'];
	            }

	            if($isGenerateKeyAfterPurchase){// then was not full paid before at ecommerce-payment-processing.php!!!
		            $itemId = $LogForDatabase['purchase_units'][0]['items'][$key]['itemId'];
		            $wpdb->update(
			            "$tablename_ecommerce_orders_items",
			            array('ServiceKey' => $ServiceKey),
			            array('id' => $itemId),
			            array('%s'),
			            array('%d')
		            );
	            }

            }
	        $TaxValue = 0;
	        if(!empty($item['tax'])){
		        $TaxValue = round(floatval($item['tax']['value']),2);
	        }

            $PriceUnitNet = round(floatval($item['unit_amount']['value']),2);
            $LogForDatabase['purchase_units'][0]['items'][$key]['PriceUnitNet'] = $PriceUnitNet;
            $PriceTotalNet = $PriceUnitNet*intval($item['quantity']);
            $LogForDatabase['purchase_units'][0]['items'][$key]['PriceTotalNet'] = $PriceTotalNet;
            $PriceUnitGross = $PriceUnitNet + round(floatval($TaxValue),2);
            $PriceTotalGross = $PriceUnitGross * intval($item['quantity']);
            $LogForDatabase['purchase_units'][0]['items'][$key]['PriceUnitGross'] = $PriceUnitGross;
            $LogForDatabase['purchase_units'][0]['items'][$key]['PriceTotalGross'] = $PriceTotalGross;
            $TaxValueUnit = round(floatval($TaxValue),2);
            $LogForDatabase['purchase_units'][0]['items'][$key]['TaxValueUnit'] = $TaxValueUnit;
            $TaxValueTotal = $TaxValueUnit * intval($item['quantity']);
            $LogForDatabase['purchase_units'][0]['items'][$key]['TaxValueTotal'] = $TaxValueTotal;

            $LogForDatabase['purchase_units'][0]['items'][$key]['AlternativeShippingGross'] = $AlternativeShippingGross;
            $LogForDatabase['purchase_units'][0]['items'][$key]['AlternativeShippingNet'] = $AlternativeShippingNet;
            $LogForDatabase['purchase_units'][0]['items'][$key]['AlternateShippingTaxValue'] = $AlternateShippingTaxValue;
            $LogForDatabase['purchase_units'][0]['items'][$key]['TaxValue'] = $TaxValue;
            $LogForDatabase['purchase_units'][0]['items'][$key]['TaxPercentage'] = $TaxPercentage;

            $LogForDatabase['purchase_units'][0]['items'][$key]['TaxPercentageToShow']=cg_ecommerce_price_to_show($currenciesArray,'%','right',$PriceDivider,$TaxPercentage);

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
	        $nameForMailToShow = contest_gal1ery_convert_for_html_output_without_nl2br($nameForMailToShow);

            $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.':</b> </td><td style="padding-left:30px;">'.$PriceTotalGrossToShow.'</td></tr>';
            if(!empty($DownloadKey) && $IsDownload && ($PaymentStatus=='COMPLETED' || $PaymentStatus=='succeeded') && empty($beforeFilter['ownKeys'][$realId]['DownloadKey'])){
                $SENT_POST['ownKeys'][$realId] = $DownloadKey;
                $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$DownloadKey.'</td></tr>';
            }elseif(!empty($ServiceKey) && $IsService && ($PaymentStatus=='COMPLETED' || $PaymentStatus=='succeeded') && empty($beforeFilter['ownKeys'][$realId]['ServiceKey'])){
                $SENT_POST['ownKeys'][$realId] = $ServiceKey;
                $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$ServiceKey.'</td></tr>';
            }elseif(!empty($beforeFilter['ownKeys'][$realId]['DownloadKey'])){
                $DownloadKey = $beforeFilter['ownKeys'][$realId]['DownloadKey'];
                $SENT_POST['ownKeys'][$realId] = $DownloadKey;
                $ordersummaryandpage .= '<tr><td><b>'.$nameForMailToShow.' '.$language_Key.':</b> </td><td style="padding-left:30px;">'.$DownloadKey.'</td></tr>';
            }elseif(!empty($beforeFilter['ownKeys'][$realId]['ServiceKey'])){
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

            if($isWithSaveToDatabase){
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
                //$wpdb->show_errors(); //setting the Show or Display errors option to true
                //var_dump('$wpdb->print_error();');
                //var_dump($wpdb->print_error());
                $itemId = $wpdb->insert_id;
	            $LogForDatabase['purchase_units'][0]['items'][$key]['itemId'] = $itemId;
            }


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

        return [
            'hasDownload' => $hasDownload,
            'itemHasShipping' => $itemHasShipping,
            'itemHasTax' => $itemHasTax,
            'ordersummaryandpage' => $ordersummaryandpage,
            'LogForDatabase' => $LogForDatabase
        ];

    }
}

?>