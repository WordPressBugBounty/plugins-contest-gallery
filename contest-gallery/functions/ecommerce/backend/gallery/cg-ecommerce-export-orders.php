<?php
if(!function_exists('cg_ecommerce_export_orders')){
	function cg_ecommerce_export_orders(){

		if(!current_user_can('manage_options')){
			echo "Logged in user have to be able to manage_options to execute export.";die;
		}

		global $wpdb;

		$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

        $start = 0;
		if (isset($_POST["cg_start"])) {
			$muster = "/^[0-9]+$/";
			if (preg_match($muster, $_POST["cg_start"]) == 0) {
				$start = 0;
			} else {
				$start = $_POST["cg_start"];
			}
		}

        $step = 50;
		if (isset($_POST["cg_step"])) {
			$muster = "/^[0-9]+$/"; // reg. Ausdruck für Zahlen
			if (preg_match($muster, $_POST["cg_start"]) == 0) {
				$step = 50;
			} else {
				$step = $_POST["cg_step"];
			}
		}

		$return = cg_ecommerce_get_orders($start,$step,true);
		$saleOrders = $return['saleOrders'];

		$currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();

		$saleItemsIdsByOrderIdArray = [];
		$saleItemsCollectedOrderIds = '';

		foreach($saleOrders as $saleOrder){
			$OrderId = $saleOrder->id;
			if(!$saleItemsCollectedOrderIds){
				$saleItemsCollectedOrderIds .= "ParentOrder = $OrderId";
			}else{
				$saleItemsCollectedOrderIds .= " or ParentOrder = $OrderId";
			}
		}

		$saleItems = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders_items WHERE ($saleItemsCollectedOrderIds)");
		$saleItemsArray = [];

		foreach($saleItems as $saleItem){
			if(!isset($saleItemsIdsByOrderIdArray[$saleItem->ParentOrder])){
				$saleItemsArray[$saleItem->ParentOrder] = [];
				$saleItemsIdsByOrderIdArray[$saleItem->ParentOrder] = [];
			}
			$saleItemsIdsByOrderIdArray[$saleItem->ParentOrder][] = $saleItem->pid;
			$saleItemsArray[$saleItem->ParentOrder][] = $saleItem;
		}

		if(!empty($saleOrders)){
			$k = 0;

			$csvData = array();

			$i=0;
			$r=0;

			$csvData[$i][$k]="Order number";
			$k++;
			$csvData[$i][$k]="Purchase date";
			$k++;
			$csvData[$i][$k]="PayPal transaction ID";
			$k++;
			$csvData[$i][$k]="Stripe Payment Intent ID";
			$k++;
			$csvData[$i][$k]="Payer email";
			$k++;
			$csvData[$i][$k]="Invoice address first name ";
			$k++;
			$csvData[$i][$k]="Invoice address last name ";
			$k++;
			$csvData[$i][$k]="Invoice address company";
			$k++;
			$csvData[$i][$k]="Invoice address address line 1";
			$k++;
			$csvData[$i][$k]="Invoice address address line 2";
			$k++;
			$csvData[$i][$k]="Invoice address city";
			$k++;
			$csvData[$i][$k]="Invoice address postal code";
			$k++;
			$csvData[$i][$k]="Invoice address state short";
			$k++;
			$csvData[$i][$k]="Invoice address state";
			$k++;
			$csvData[$i][$k]="Invoice address country short";
			$k++;
			$csvData[$i][$k]="Invoice address country";
			$k++;
			$csvData[$i][$k]="VAT Number";
			$k++;
			$csvData[$i][$k]="Shipping address first name ";
			$k++;
			$csvData[$i][$k]="Shipping address last name ";
			$k++;
			$csvData[$i][$k]="Shipping address company";
			$k++;
			$csvData[$i][$k]="Shipping address address line 1";
			$k++;
			$csvData[$i][$k]="Shipping address address line 2";
			$k++;
			$csvData[$i][$k]="Shipping address city";
			$k++;
			$csvData[$i][$k]="Shipping address postal code";
			$k++;
			$csvData[$i][$k]="Shipping address state short";
			$k++;
			$csvData[$i][$k]="Shipping address state";
			$k++;
			$csvData[$i][$k]="Shipping address country short";
			$k++;
			$csvData[$i][$k]="Shipping address country";
			$k++;
			$csvData[$i][$k]="Shipping default net";
			$k++;
			$csvData[$i][$k]="Shipping default gross";
			$k++;
			$csvData[$i][$k]="Total net (with shipping if exists)";
			$k++;
			$csvData[$i][$k]="Total gross (with shipping if exists)";
			$k++;
			$csvData[$i][$k]="Quantity";
			$k++;
			$csvData[$i][$k]="Type";
			$k++;
			$csvData[$i][$k]="Title";
			$k++;
			$csvData[$i][$k]="Price unit net";
			$k++;
			$csvData[$i][$k]="Price total net";
			$k++;
			$csvData[$i][$k]="Tax percentage";
			$k++;
			$csvData[$i][$k]="Tax total";
			$k++;
			$csvData[$i][$k]="Price total gross";
			$k++;
			$csvData[$i][$k]="Shipping alternative net";
			$k++;
			$csvData[$i][$k]="Shipping alternative gross";
			$k++;
			$csvData[$i][$k]="Entry ID";
			$k++;
			$csvData[$i][$k]="Gallery ID";
			$k++;
			$csvData[$i][$k]="Environment";
			$k++;

			/*echo "<pre>";
			print_r($saleItemsArray);
			echo "</pre>";

			die;*/

			// Simple amount of orders
			$order = 0;

			foreach($saleOrders as $saleOrder){
				$i++;
				$k = 0;
				$order++;
				$purchaseTime = cg_get_time_based_on_wp_timezone_conf($saleOrder->Tstamp,'d-M-Y H:i:s');
				$TaxNr =  $saleOrder->TaxNr;
				$PayerEmail =  $saleOrder->PayerEmail;
				$LogForDatabase =  unserialize($saleOrder->LogForDatabase);
				$PriceDivider = $LogForDatabase['PriceDivider'];
				$CurrencyShort = $saleOrder->CurrencyShort;
				$CurrencyPosition = $saleOrder->CurrencyPosition;

				$csvData[$i][$k]=$saleOrder->OrderNumber;
				$k++;
				$csvData[$i][$k]=$purchaseTime;
				$k++;
				$csvData[$i][$k]=$saleOrder->PayPalTransactionId;
				$k++;
				$csvData[$i][$k]=$saleOrder->StripePiId;
				$k++;
				$csvData[$i][$k]=$PayerEmail;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressFirstName;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressLastName;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressCompany;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressLine1;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressLine2;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressCity;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressPostalCode;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressStateShort;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressStateTranslation;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressCountryShort;
				$k++;
				$csvData[$i][$k]=$saleOrder->InvoiceAddressCountryTranslation;
				$k++;
				$csvData[$i][$k]=$TaxNr;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressFirstName;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressLastName;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressCompany;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressLine1;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressLine2;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressCity;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressPostalCode;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressStateShort;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressStateTranslation;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressCountryShort;
				$k++;
				$csvData[$i][$k]=$saleOrder->ShippingAddressCountryTranslation;
				$k++;

				$hasDefaultShipping = false;
				// check if has alternative shipping only
				foreach($saleItemsArray[$saleOrder->id] as $saleOrderItem){
					if($saleOrderItem->IsShipping && !$saleOrderItem->IsAlternativeShipping){
						$hasDefaultShipping = true;
					}
				}

				if($hasDefaultShipping){
					$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->ShippingNet);
				}else{
					$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,0);
				}

				$k++;

				if($hasDefaultShipping){
					$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->ShippingGross);
				}else{
					$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,0);
				}

				$k++;
				$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalNetItemsWithShipping);
				$k++;
				$csvData[$i][$k]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalGrossItemsWithShipping);
				$k++;

				foreach($saleItemsArray[$saleOrder->id] as $saleOrderItem){
					$koi = 0;// key order item
					/*var_dump('$saleOrder->id');
					var_dump($saleOrder->id);
					echo "<pre>";
						print_r($saleOrderItem);
					echo "</pre>";
					die;*/
					$i++;
					$type = '';
					if($saleOrderItem->IsDownload){$type='download';}
					if($saleOrderItem->IsShipping){$type='shipping';}
					if($saleOrderItem->IsUpload){$type='upload';}
					//$csvData[$i][0]="Purchase Date";
					//$csvData[$i][1]="PayPal Transaction ID";
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]='';
					$koi++;
					$csvData[$i][$koi]=$saleOrderItem->Units;
					$koi++;
					$csvData[$i][$koi]=($type=='shipping' && $saleOrderItem->AlternativeShippingNet>0) ? 'shipping alternative' : $type;
					$koi++;
					$csvData[$i][$koi]=contest_gal1ery_convert_for_html_output_without_nl2br($saleOrderItem->SaleTitle);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceUnitNet);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalNet);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,'%','right',$PriceDivider,$saleOrderItem->TaxPercentage);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->TaxValueTotal);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalGross);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingNet);
					$koi++;
					$csvData[$i][$koi]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingGross);
					$koi++;
					$csvData[$i][$koi]=$saleOrderItem->pid;
					$koi++;
					$csvData[$i][$koi]=$saleOrderItem->GalleryID;
					$koi++;
					$csvData[$i][$koi]=($saleOrder->IsTest) ? 'test' : 'live';
					$koi++;
				}
			}

		}else{
			echo "<b style='font-size: 22px;'><b>No data selected</b></p>";
			die;
		}

		if(!empty($_GET['cg_order_id'])){
			$filename = "cg-order-".absint($_GET['cg_order_id']).".csv";
		}else{
			$exportTime = cg_get_time_based_on_wp_timezone_conf(time(),'d-M-Y H:i:s');
			$filename = "cg-orders-".$exportTime.".csv";
		}

        $csvData = cg_neutralize_csv_array($csvData);

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$filename");

		ob_start();

		$fp = fopen("php://output", 'w');
		fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
		foreach ($csvData as $fields) {
			fputcsv($fp, $fields, ";");
		}
		fclose($fp);
		$masterReturn = ob_get_clean();
		echo $masterReturn;
		die();


	}
}

?>