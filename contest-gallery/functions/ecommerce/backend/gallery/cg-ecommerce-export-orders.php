<?php

if(!defined('CG_ECOMMERCE_ORDERS_EXPORT_BATCH_SIZE')){
	define('CG_ECOMMERCE_ORDERS_EXPORT_BATCH_SIZE',100);
}

if(!function_exists('cg_ecommerce_export_orders_get_batch')){
	function cg_ecommerce_export_orders_get_batch($beforeOrderId,$batchSize){
		global $wpdb;

		$ordersTable = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
		$itemsTable = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
		$orderColumns = "sale_orders.id,
			sale_orders.OrderNumber,
			sale_orders.Tstamp,
			sale_orders.PayPalTransactionId,
			sale_orders.StripePiId,
			sale_orders.PayerEmail,
			sale_orders.InvoiceAddressFirstName,
			sale_orders.InvoiceAddressLastName,
			sale_orders.InvoiceAddressCompany,
			sale_orders.InvoiceAddressLine1,
			sale_orders.InvoiceAddressLine2,
			sale_orders.InvoiceAddressCity,
			sale_orders.InvoiceAddressPostalCode,
			sale_orders.InvoiceAddressStateShort,
			sale_orders.InvoiceAddressStateTranslation,
			sale_orders.InvoiceAddressCountryShort,
			sale_orders.InvoiceAddressCountryTranslation,
			sale_orders.TaxNr,
			sale_orders.ShippingAddressFirstName,
			sale_orders.ShippingAddressLastName,
			sale_orders.ShippingAddressCompany,
			sale_orders.ShippingAddressLine1,
			sale_orders.ShippingAddressLine2,
			sale_orders.ShippingAddressCity,
			sale_orders.ShippingAddressPostalCode,
			sale_orders.ShippingAddressStateShort,
			sale_orders.ShippingAddressStateTranslation,
			sale_orders.ShippingAddressCountryShort,
			sale_orders.ShippingAddressCountryTranslation,
			sale_orders.ShippingNet,
			sale_orders.ShippingGross,
			sale_orders.PriceTotalNetItemsWithShipping,
			sale_orders.PriceTotalGrossItemsWithShipping,
			sale_orders.CurrencyShort,
			sale_orders.CurrencyPosition,
			sale_orders.LogForDatabase,
			sale_orders.IsTest";

		if(!empty($_GET['cg_order_id'])){
			return $wpdb->get_results($wpdb->prepare(
				"SELECT $orderColumns
				FROM $ordersTable AS sale_orders
				WHERE sale_orders.id = %d",
				absint($_GET['cg_order_id'])
			));
		}

		$whereClauses = array();
		$whereValues = array();
		$filterClauses = array();
		$filterValues = array();

		$PayPalTransactionId = cg_ecommerce_get_orders_get_post_text('cg_paypal_transaction_id');
		$PayerEmail = cg_ecommerce_get_orders_get_post_text('cg_payer_email');
		$OrderNumber = cg_ecommerce_get_orders_get_post_text('cg_order_number');
		$ItemIdsSearchValue = cg_ecommerce_get_orders_get_post_text('cg_item_ids');
		$GalleryIdsSearchValue = cg_ecommerce_get_orders_get_post_text('cg_gallery_ids');
		$ItemIds = cg_ecommerce_get_orders_get_post_ids('cg_item_ids');
		$GalleryIds = cg_ecommerce_get_orders_get_post_ids('cg_gallery_ids');

		if($PayPalTransactionId!==''){
			$PayPalTransactionIdLike = '%'.$wpdb->esc_like($PayPalTransactionId).'%';
			$filterClauses[] = "(sale_orders.PayPalTransactionId LIKE %s OR sale_orders.StripePiId LIKE %s)";
			$filterValues[] = $PayPalTransactionIdLike;
			$filterValues[] = $PayPalTransactionIdLike;
		}

		if($PayerEmail!==''){
			$filterClauses[] = "sale_orders.PayerEmail LIKE %s";
			$filterValues[] = '%'.$wpdb->esc_like($PayerEmail).'%';
		}

		if($ItemIdsSearchValue!==''){
			if(count($ItemIds)){
				$ItemPlaceholders = implode(',',array_fill(0,count($ItemIds),'%d'));
				$filterClauses[] = "EXISTS (
					SELECT 1
					FROM $itemsTable AS item_filter
					WHERE item_filter.ParentOrder = sale_orders.id
						AND item_filter.pid IN ($ItemPlaceholders)
				)";
				foreach($ItemIds as $ItemId){
					$filterValues[] = $ItemId;
				}
			}else{
				$filterClauses[] = '1 = 0';
			}
		}

		if($OrderNumber!==''){
			$filterClauses[] = "sale_orders.OrderNumber LIKE %s";
			$filterValues[] = '%'.$wpdb->esc_like($OrderNumber).'%';
		}

		if($GalleryIdsSearchValue!==''){
			if(count($GalleryIds)){
				$GalleryPlaceholders = implode(',',array_fill(0,count($GalleryIds),'%d'));
				$filterClauses[] = "EXISTS (
					SELECT 1
					FROM $itemsTable AS gallery_filter
					WHERE gallery_filter.ParentOrder = sale_orders.id
						AND gallery_filter.GalleryID IN ($GalleryPlaceholders)
				)";
				foreach($GalleryIds as $GalleryId){
					$filterValues[] = $GalleryId;
				}
			}else{
				$filterClauses[] = '1 = 0';
			}
		}

		$whereClauses[] = 'sale_orders.id > 0';
		if(count($filterClauses)){
			$whereClauses[] = '('.implode(' OR ',$filterClauses).')';
			$whereValues = array_merge($whereValues,$filterValues);
		}
		if(!empty($beforeOrderId)){
			$whereClauses[] = 'sale_orders.id < %d';
			$whereValues[] = absint($beforeOrderId);
		}

		$query = "SELECT $orderColumns
			FROM $ordersTable AS sale_orders
			WHERE ".implode(' AND ',$whereClauses)."
			ORDER BY sale_orders.id DESC
			LIMIT %d";
		$whereValues[] = absint($batchSize);

		return $wpdb->get_results(cg_ecommerce_get_orders_prepare_query($wpdb,$query,$whereValues));
	}
}

if(!function_exists('cg_ecommerce_export_orders_get_items')){
	function cg_ecommerce_export_orders_get_items($orderIds){
		global $wpdb;

		if(empty($orderIds)){
			return array();
		}

		$itemsTable = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
		$placeholders = implode(',',array_fill(0,count($orderIds),'%d'));
		$query = "SELECT
				ParentOrder,
				Units,
				IsDownload,
				IsShipping,
				IsUpload,
				IsAlternativeShipping,
				SaleTitle,
				PriceUnitNet,
				PriceTotalNet,
				TaxPercentage,
				TaxValueTotal,
				PriceTotalGross,
				AlternativeShippingNet,
				AlternativeShippingGross,
				pid,
				GalleryID
			FROM $itemsTable
			WHERE ParentOrder IN ($placeholders)
			ORDER BY id ASC";

		return $wpdb->get_results(cg_ecommerce_get_orders_prepare_query($wpdb,$query,$orderIds));
	}
}

if(!function_exists('cg_ecommerce_export_orders_write_csv_row')){
	function cg_ecommerce_export_orders_write_csv_row($fp,$row){
		return fputcsv($fp,cg_neutralize_csv_array($row),';');
	}
}

if(!function_exists('cg_ecommerce_export_orders')){
	function cg_ecommerce_export_orders(){

		if(!current_user_can('manage_options')){
			echo "Logged in user have to be able to manage_options to execute export.";die;
		}

		$currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();

		if(!empty($_GET['cg_order_id'])){
			$filename = "cg-order-".absint($_GET['cg_order_id']).".csv";
		}else{
			$exportTime = cg_get_time_based_on_wp_timezone_conf(time(),'d-M-Y H:i:s');
			$filename = "cg-orders-".$exportTime.".csv";
		}

		nocache_headers();
		header("Content-type: text/csv; charset=UTF-8");
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$fp = fopen("php://output",'w');
		if($fp===false){
			echo "CSV export could not be created.";
			die;
		}

		fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));

		$header = array(
			"Order number",
			"Purchase date",
			"PayPal transaction ID",
			"Stripe Payment Intent ID",
			"Payer email",
			"Invoice address first name ",
			"Invoice address last name ",
			"Invoice address company",
			"Invoice address address line 1",
			"Invoice address address line 2",
			"Invoice address city",
			"Invoice address postal code",
			"Invoice address state short",
			"Invoice address state",
			"Invoice address country short",
			"Invoice address country",
			"VAT Number",
			"Shipping address first name ",
			"Shipping address last name ",
			"Shipping address company",
			"Shipping address address line 1",
			"Shipping address address line 2",
			"Shipping address city",
			"Shipping address postal code",
			"Shipping address state short",
			"Shipping address state",
			"Shipping address country short",
			"Shipping address country",
			"Shipping default net",
			"Shipping default gross",
			"Total net (with shipping if exists)",
			"Total gross (with shipping if exists)",
			"Quantity",
			"Type",
			"Title",
			"Price unit net",
			"Price total net",
			"Tax percentage",
			"Tax total",
			"Price total gross",
			"Shipping alternative net",
			"Shipping alternative gross",
			"Entry ID",
			"Gallery ID",
			"Environment"
		);
		cg_ecommerce_export_orders_write_csv_row($fp,$header);

		$beforeOrderId = 0;
		do{
			$saleOrders = cg_ecommerce_export_orders_get_batch($beforeOrderId,CG_ECOMMERCE_ORDERS_EXPORT_BATCH_SIZE);
			if(empty($saleOrders)){
				break;
			}

			$orderIds = array();
			$saleItemsArray = array();
			$hasDefaultShippingByOrder = array();
			foreach($saleOrders as $saleOrder){
				$orderId = absint($saleOrder->id);
				$orderIds[] = $orderId;
				$saleItemsArray[$orderId] = array();
				$hasDefaultShippingByOrder[$orderId] = false;
			}

			$saleItems = cg_ecommerce_export_orders_get_items($orderIds);
			foreach($saleItems as $saleItem){
				$orderId = absint($saleItem->ParentOrder);
				if(!isset($saleItemsArray[$orderId])){
					continue;
				}
				$saleItemsArray[$orderId][] = $saleItem;
				if(!empty($saleItem->IsShipping) && empty($saleItem->IsAlternativeShipping)){
					$hasDefaultShippingByOrder[$orderId] = true;
				}
			}

			foreach($saleOrders as $saleOrder){
				$orderId = absint($saleOrder->id);
				$purchaseTime = cg_get_time_based_on_wp_timezone_conf($saleOrder->Tstamp,'d-M-Y H:i:s');
				$LogForDatabase = maybe_unserialize($saleOrder->LogForDatabase);
				$PriceDivider = (is_array($LogForDatabase) && isset($LogForDatabase['PriceDivider']))
					? $LogForDatabase['PriceDivider']
					: '.';
				$CurrencyShort = $saleOrder->CurrencyShort;
				$CurrencyPosition = $saleOrder->CurrencyPosition;
				$hasDefaultShipping = !empty($hasDefaultShippingByOrder[$orderId]);

				$orderRow = array(
					$saleOrder->OrderNumber,
					$purchaseTime,
					$saleOrder->PayPalTransactionId,
					$saleOrder->StripePiId,
					$saleOrder->PayerEmail,
					$saleOrder->InvoiceAddressFirstName,
					$saleOrder->InvoiceAddressLastName,
					$saleOrder->InvoiceAddressCompany,
					$saleOrder->InvoiceAddressLine1,
					$saleOrder->InvoiceAddressLine2,
					$saleOrder->InvoiceAddressCity,
					$saleOrder->InvoiceAddressPostalCode,
					$saleOrder->InvoiceAddressStateShort,
					$saleOrder->InvoiceAddressStateTranslation,
					$saleOrder->InvoiceAddressCountryShort,
					$saleOrder->InvoiceAddressCountryTranslation,
					$saleOrder->TaxNr,
					$saleOrder->ShippingAddressFirstName,
					$saleOrder->ShippingAddressLastName,
					$saleOrder->ShippingAddressCompany,
					$saleOrder->ShippingAddressLine1,
					$saleOrder->ShippingAddressLine2,
					$saleOrder->ShippingAddressCity,
					$saleOrder->ShippingAddressPostalCode,
					$saleOrder->ShippingAddressStateShort,
					$saleOrder->ShippingAddressStateTranslation,
					$saleOrder->ShippingAddressCountryShort,
					$saleOrder->ShippingAddressCountryTranslation,
					cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$hasDefaultShipping ? $saleOrder->ShippingNet : 0),
					cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$hasDefaultShipping ? $saleOrder->ShippingGross : 0),
					cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalNetItemsWithShipping),
					cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrder->PriceTotalGrossItemsWithShipping)
				);
				cg_ecommerce_export_orders_write_csv_row($fp,$orderRow);

				foreach($saleItemsArray[$orderId] as $saleOrderItem){
					$type = '';
					if($saleOrderItem->IsDownload){$type='download';}
					if($saleOrderItem->IsShipping){$type='shipping';}
					if($saleOrderItem->IsUpload){$type='upload';}

					$itemRow = array_fill(0,32,'');
					$itemRow[] = $saleOrderItem->Units;
					$itemRow[] = ($type==='shipping' && $saleOrderItem->AlternativeShippingNet>0) ? 'shipping alternative' : $type;
					$itemRow[] = contest_gal1ery_convert_for_html_output_without_nl2br($saleOrderItem->SaleTitle);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceUnitNet);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalNet);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,'%','right',$PriceDivider,$saleOrderItem->TaxPercentage);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->TaxValueTotal);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->PriceTotalGross);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingNet);
					$itemRow[] = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$saleOrderItem->AlternativeShippingGross);
					$itemRow[] = $saleOrderItem->pid;
					$itemRow[] = $saleOrderItem->GalleryID;
					$itemRow[] = ($saleOrder->IsTest) ? 'test' : 'live';
					cg_ecommerce_export_orders_write_csv_row($fp,$itemRow);
				}
			}

			$lastOrder = end($saleOrders);
			$beforeOrderId = absint($lastOrder->id);
			$isFinished = !empty($_GET['cg_order_id']) || count($saleOrders)<CG_ECOMMERCE_ORDERS_EXPORT_BATCH_SIZE;
			unset($saleOrders,$saleItems,$saleItemsArray,$hasDefaultShippingByOrder);
		}while(!$isFinished);

		fclose($fp);
		die();
	}
}

?>
