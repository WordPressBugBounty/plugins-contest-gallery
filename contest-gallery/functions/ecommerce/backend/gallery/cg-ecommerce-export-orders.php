<?php
if(!function_exists('cg_ecommerce_export_orders')){
	function cg_ecommerce_export_orders(){

		if(!current_user_can('manage_options')){
			echo "Logged in user have to be able to manage_options to execute export.";die;
		}

		global $wpdb;

		$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

		if (isset($_POST["cg_start"])) {
			$muster = "/^[0-9]+$/";
			if (preg_match($muster, $_POST["cg_start"]) == 0) {
				$start = 0;
			} else {
				$start = $_POST["cg_start"];
			}
		}

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

			$csvData = array();

			$i=0;
			$r=0;

			$csvData[$i][0]="Order";
			$csvData[$i][1]="Purchase date";
			$csvData[$i][2]="PayPal transaction ID";
			$csvData[$i][3]="Payer email";
			$csvData[$i][4]="Invoice address first name ";
			$csvData[$i][5]="Invoice address last name ";
			$csvData[$i][6]="Invoice address company";
			$csvData[$i][7]="Invoice address address line 1";
			$csvData[$i][8]="Invoice address address line 2";
			$csvData[$i][9]="Invoice address city";
			$csvData[$i][10]="Invoice address postal code";
			$csvData[$i][11]="Invoice address state short";
			$csvData[$i][12]="Invoice address state";
			$csvData[$i][13]="Invoice address country short";
			$csvData[$i][14]="Invoice address country";
			$csvData[$i][15]="VAT Number";
			$csvData[$i][16]="Shipping address first name ";
			$csvData[$i][17]="Shipping address last name ";
			$csvData[$i][18]="Shipping address company";
			$csvData[$i][19]="Shipping address address line 1";
			$csvData[$i][20]="Shipping address address line 2";
			$csvData[$i][21]="Shipping address city";
			$csvData[$i][22]="Shipping address postal code";
			$csvData[$i][23]="Shipping address state short";
			$csvData[$i][24]="Shipping address state";
			$csvData[$i][25]="Shipping address country short";
			$csvData[$i][26]="Shipping address country";
			$csvData[$i][27]="Shipping default net";
			$csvData[$i][28]="Shipping default gross";
			$csvData[$i][29]="Total net (with shipping if exists)";
			$csvData[$i][30]="Total gross (with shipping if exists)";
			$csvData[$i][31]="Quantity";
			$csvData[$i][32]="Type";
			$csvData[$i][33]="Title";
			$csvData[$i][34]="Price unit net";
			$csvData[$i][35]="Price total net";
			$csvData[$i][36]="Tax percentage";
			$csvData[$i][37]="Tax total";
			$csvData[$i][38]="Price total gross";
			$csvData[$i][39]="Shipping alternative net";
			$csvData[$i][40]="Shipping alternative gross";
			$csvData[$i][41]="Entry ID";
			$csvData[$i][42]="Gallery ID";
			$csvData[$i][43]="Environment";

			/*echo "<pre>";
			print_r($saleItemsArray);
			echo "</pre>";

			die;*/

			// Simple amount of orders
			$order = 0;

			foreach($saleOrders as $saleOrder){
				$i++;
				$order++;
				$purchaseTime = cg_get_time_based_on_wp_timezone_conf($saleOrder->Tstamp,'d-M-Y H:i:s');
				$PayPalTransactionId =  $saleOrder->PayPalTransactionId;
				$TaxNr =  $saleOrder->TaxNr;
				$PayerEmail =  $saleOrder->PayerEmail;
				$LogForDatabase =  unserialize($saleOrder->LogForDatabase);
				$PriceDivider = $LogForDatabase['PriceDivider'];
				$CurrencyShort = $saleOrder->CurrencyShort;
				$CurrencyPosition = $saleOrder->CurrencyPosition;

				$csvData[$i][0]=$order;
				$csvData[$i][1]=$purchaseTime;
				$csvData[$i][2]=$PayPalTransactionId;
				$csvData[$i][3]=$PayerEmail;
				$csvData[$i][4]=$saleOrder->InvoiceAddressFirstName;
				$csvData[$i][5]=$saleOrder->InvoiceAddressLastName;
				$csvData[$i][6]=$saleOrder->InvoiceAddressCompany;
				$csvData[$i][7]=$saleOrder->InvoiceAddressLine1;
				$csvData[$i][8]=$saleOrder->InvoiceAddressLine2;
				$csvData[$i][9]=$saleOrder->InvoiceAddressCity;
				$csvData[$i][10]=$saleOrder->InvoiceAddressPostalCode;
				$csvData[$i][11]=$saleOrder->InvoiceAddressStateShort;
				$csvData[$i][12]=$saleOrder->InvoiceAddressStateTranslation;
				$csvData[$i][13]=$saleOrder->InvoiceAddressCountryShort;
				$csvData[$i][14]=$saleOrder->InvoiceAddressCountryTranslation;
				$csvData[$i][15]=$TaxNr;
				$csvData[$i][16]=$saleOrder->ShippingAddressFirstName;
				$csvData[$i][17]=$saleOrder->ShippingAddressLastName;
				$csvData[$i][18]=$saleOrder->ShippingAddressCompany;
				$csvData[$i][19]=$saleOrder->ShippingAddressLine1;
				$csvData[$i][20]=$saleOrder->ShippingAddressLine2;
				$csvData[$i][21]=$saleOrder->ShippingAddressCity;
				$csvData[$i][22]=$saleOrder->ShippingAddressPostalCode;
				$csvData[$i][23]=$saleOrder->ShippingAddressStateShort;
				$csvData[$i][24]=$saleOrder->ShippingAddressStateTranslation;
				$csvData[$i][25]=$saleOrder->ShippingAddressCountryShort;
				$csvData[$i][26]=$saleOrder->ShippingAddressCountryTranslation;

				$hasDefaultShipping = false;
				// check if has alternative shipping only
				foreach($saleItemsArray[$saleOrder->id] as $saleOrderItem){
					if($saleOrderItem->IsShipping && !$saleOrderItem->IsAlternativeShipping){
						$hasDefaultShipping = true;
					}
				}

				if($hasDefaultShipping){
					$csvData[$i][27]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->ShippingNet);
				}else{
					$csvData[$i][27]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,0);
				}

				if($hasDefaultShipping){
					$csvData[$i][28]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->ShippingGross);
				}else{
					$csvData[$i][28]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,0);
				}

				$csvData[$i][29]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalNetItemsWithShipping);
				$csvData[$i][30]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalGrossItemsWithShipping);

				foreach($saleItemsArray[$saleOrder->id] as $saleOrderItem){
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
					$csvData[$i][0]='';
					$csvData[$i][1]='';
					$csvData[$i][2]='';
					$csvData[$i][3]='';
					$csvData[$i][4]='';
					$csvData[$i][5]='';
					$csvData[$i][6]='';
					$csvData[$i][7]='';
					$csvData[$i][8]='';
					$csvData[$i][9]='';
					$csvData[$i][10]='';
					$csvData[$i][11]='';
					$csvData[$i][12]='';
					$csvData[$i][13]='';
					$csvData[$i][14]='';
					$csvData[$i][15]='';
					$csvData[$i][16]='';
					$csvData[$i][17]='';
					$csvData[$i][18]='';
					$csvData[$i][19]='';
					$csvData[$i][20]='';
					$csvData[$i][21]='';
					$csvData[$i][22]='';
					$csvData[$i][23]='';
					$csvData[$i][24]='';
					$csvData[$i][25]='';
					$csvData[$i][26]='';
					$csvData[$i][27]='';
					$csvData[$i][28]='';
					$csvData[$i][29]='';
					$csvData[$i][30]='';
					$csvData[$i][31]=$saleOrderItem->Units;
					$csvData[$i][32]=($type=='shipping' && $saleOrderItem->AlternativeShippingNet>0) ? 'shipping alternative' : $type;
					$csvData[$i][33]=contest_gal1ery_convert_for_html_output_without_nl2br($saleOrderItem->SaleTitle);
					$csvData[$i][34]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceUnitNet);
					$csvData[$i][35]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalNet);
					$csvData[$i][36]=cg_ecommerce_price_to_show($currenciesArray,'%','right',$PriceDivider,$saleOrderItem->TaxPercentage);
					$csvData[$i][37]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->TaxValueTotal);
					$csvData[$i][38]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalGross);
					$csvData[$i][39]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingNet);
					$csvData[$i][40]=cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingGross);
					$csvData[$i][41]=$saleOrderItem->pid;
					$csvData[$i][42]=$saleOrderItem->GalleryID;
					$csvData[$i][43]=($saleOrder->IsTest) ? 'test' : 'live';
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