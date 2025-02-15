<?php

$GalleryID = absint($_GET['option_id']);

include(dirname(__FILE__) . "/../nav-menu.php");

// Tabellennamen ermitteln, GalleryID wurde als Shortcode bereits �bermittelt.
global $wpdb;

$table_posts = $wpdb->prefix."posts";
$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

// please provide sales id should be visible if not provided
// ales id sanitize $_GET und dann verarbeiten

$OrderId = absint($_GET['cg_order_id']);
//$optionsNormal = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id='$OrderId'");

$Order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$OrderId' LIMIT 1");

if(empty($Order)){
	echo "<div id='mainCGdivOrderContainer' class='mainCGdivOrderContainer' >
<div style='display:flex; text-align: center; justify-content: space-between;  max-width: 600px;margin: 0 auto;'>
    <p style='text-align: center;width:100%;'><h2>Order not found</h2></p>
</div>
</div>";
}else{

	$OrderIdHash = $Order->OrderIdHash;
	$OrderNumber = $Order->OrderNumber;
	$LogForDatabase = unserialize($Order->LogForDatabase);
	$wp_upload_dir = wp_upload_dir();
	$hasToBeCompleted = false;
	$PayPalOrderResponse = [];

	$StripePiId = $Order->StripePiId;
	$PayPalTransactionId = $Order->PayPalTransactionId;
	$PayerEmail = $Order->PayerEmail;

	$OrderItems = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders_items WHERE ParentOrder = '$OrderId' ");

	$RawData = [];
	$ecommerceFilesDataArray = [];
	$GalleryIDs = [];
	$uploadedEntries = [];
	$uploadedEntriesData = [];
	$status = '';

	$StripePiPaymentMethodConfDetailsId = '';
    $StripePiPaymentMethodId = '';
    $StripePiId = '';
    $StripePiClientSecret = '';

	foreach ($OrderItems as $OrderItem){
		if(in_array($OrderItem->GalleryID,$GalleryIDs)===false){
			$GalleryIDs[] = $OrderItem->GalleryID;
		}
	}

	$jsonInfoData = [];
	$dataForOrder = [];
	$queryDataArray = [];
	$options = [];
	foreach ($GalleryIDs as $GalleryID){
		$dataForOrder[$GalleryID] = cg_get_query_data_and_options($wp_upload_dir,$GalleryID);
		$queryDataArray[$GalleryID]  = $dataForOrder[$GalleryID]['queryDataArray'];
		$options[$GalleryID]  = $dataForOrder[$GalleryID]['options'];
	}

	$OrderItemsByEntryPid = [];

	foreach ($OrderItems as $OrderItem){

		$OrderItemID = $OrderItem->id;
		$OrderItemsByEntryPid[$OrderItem->pid] = $OrderItem;
		$RawDataWhenBuyed[$OrderItem->pid] = unserialize($OrderItem->RawData);
		$dataForOrder = cg_get_data_for_order($wp_upload_dir,$OrderItem,$RawData);

		if(!empty($OrderItem->Uploaded)){
			$uploaded = $wpdb->get_results("SELECT * FROM $tablename WHERE OrderItem = '$OrderItemID' ORDER BY id DESC");
			foreach ($uploaded as $entry){
				if(!isset($uploadedEntries[$OrderItemID])){
					$uploadedEntries[$OrderItemID] = [];
				}
				$uploadedEntries[$OrderItemID][] = $entry->id;
			}
		}

		if(empty($dataForOrder['RawData'])){// then gallery must be deleted
			$jsonInfoData[$OrderItem->pid]  = [];
			$RawData[$OrderItem->pid] = $RawDataWhenBuyed[$OrderItem->pid] ;
			$RawData[$OrderItem->pid]['isRemovedRealIdOrGallery'] = true;
			$RawDataWhenBuyed[$OrderItem->pid]['isRemovedRealIdOrGallery'] = true;// also important to set will be set for shipping or service in cgJsClass.gallery.ecommerce.functions.showSaleOrder condition
		}else{
			if(!empty($dataForOrder['RawData'][$OrderItem->pid])){
				$RawData[$OrderItem->pid] = $dataForOrder['RawData'][$OrderItem->pid];
			}
			if(!empty($dataForOrder['jsonInfoData'][$OrderItem->pid])){
				$jsonInfoData[$OrderItem->pid] = $dataForOrder['jsonInfoData'][$OrderItem->pid];
			}else{
				$jsonInfoData[$OrderItem->pid]  = [];
			}
		}

		$ecommerceFilesData = cg_get_ecommerce_files_data($OrderItem->GalleryID,$OrderItem->pid);
		if(!empty($ecommerceFilesData[$OrderItem->pid])){
			$ecommerceFilesDataArray[$OrderItem->pid] = $ecommerceFilesData[$OrderItem->pid];
		}

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

	if(!empty($uploadedEntries)){
		$uploadedEntriesData = cg_get_data_for_order_items($uploadedEntries);
	}

	if(!empty($LogForDatabase)){

		$ecommerce_options = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = 1");
		$ecommerceOptions = json_decode(json_encode($ecommerce_options),true);

		if($Order->PaymentType == 'stripe'){
			if($Order->IsTest){
				$secret = $ecommerceOptions['StripeSandboxSecret'];
			}else{
				$secret = $ecommerceOptions['StripeLiveSecret'];
			}

			$params = [
				'expand' => ['latest_charge']//  to check refund
			];

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents/'.$Order->StripePiId);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

			$headers = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch),true);

			if (curl_errno($ch)) {
				$Error = 'Error:' . curl_error($ch);
			}else{
				if(!empty($result['error']['message'])){
					$Error = 'Error:' . $result['error']['message'];
				}
			}

			if(!empty($Error)){
				echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer' >
<p style='text-align: center;margin:10px;'>$Error</p>
</div>";
				return;
			}else{
				$status = $result['status'];

                if(!empty($result['latest_charge']['amount_refunded']) && $result['latest_charge']['amount_refunded'] == $result['latest_charge']['amount_refunded']){
	                $status = 'refunded';
                }else if(!empty($result['latest_charge']['amount_refunded']) && $result['latest_charge']['amount_refunded'] != $result['latest_charge']['amount_refunded']){
	                $status = 'partially refunded';
                }

				$explanation = 'Status unknown';
				// status explanation source // https://docs.stripe.com/payments/paymentintents/lifecycle
				if($status=='succeeded'){
					if(!$Order->IsFullPaid && $Order->VersionDb >= 26){
						$hasToBeCompleted = true;
					}
					$explanation = 'the funds for this captured payment were credited to the payee\'s Stripe account';
				}
				if($status=='requires_action'){
					$explanation = 'checkout wasn\'t completed';
				}
				if($status=='requires_payment_method'){
					$explanation = 'payment method failed';
				}
				if($status=='processing'){
					$explanation = 'your order is beeing processed.';
				}
				if($status=='canceled'){
					$explanation = 'your order was canceled';
				}
				if($status=='requires_action'){
					$explanation = 'order required additional action';
				}
				if($status=='requires_capture'){
					$explanation = 'order has to be captured';
				}

				if($status=='partially refunded'){
					$explanation = 'an amount less than this captured payment\'s amount was partially refunded or is in the process being refunded to the payer';
				}

				if($status=='refunded'){
					$explanation = 'an amount greater than or equal to this captured payment\'s amount was refunded or is in the process being refunded to the payer';
				}

				if($Order->VersionDb >= 26 && $Order->PaymentStatus != $status){
					$wpdb->update(
						"$tablename_ecommerce_orders",
						array('PaymentStatus' => cg1l_sanitize_method($status)),
						array('id' => $OrderId),
						array('%s'),
						array('%d')
					);
				}

				if($status!='succeeded'){
					echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer'>
<p style='text-align: center;'><b>Status:</b> $status ($explanation)</p>
</div>"; return;
				}

			}
		}else if($Order->PaymentType == 'paypal'){

            if($Order->IsTest){
                $accessToken = cg_paypal_get_access_token($ecommerceOptions['PayPalSandboxClientId'],$ecommerceOptions['PayPalSandboxSecret'],true);
            }else{
                $accessToken = cg_paypal_get_access_token($ecommerceOptions['PayPalLiveClientId'],$ecommerceOptions['PayPalLiveSecret']);
            }

            if($accessToken=='no-internet'){
                // Access Token could not be created. Wrong client id or wrong secret
                echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer'>
    <p style='text-align: center;margin:10px;'>No internet connection</p>
    </div>"; return;
            }else if($accessToken=='error'){
                //Access Token could not be created. Wrong client id or wrong secret.
                echo "<div  id='mainCGdivOrderContainer' class='mainCGdivOrderContainer' >
    <p style='text-align: center;margin:10px;'>PayPal client authentication failed</p>
    </div>"; return;
            }

            $PayPalOrderResponse = cg_get_paypal_order($accessToken,$Order->PayPalTransactionId,$Order->IsTest);
            if(empty($PayPalOrderResponse['status'])){
                echo "<div  id='mainCGdivOrderContainer' class='mainCGdivOrderContainer'>
    <p style='text-align: center;margin-bottom: 0;'><b>Error getting PayPal order. Reload this page to try again.</b></p>
    </div>"; return;
            }
            $status = $PayPalOrderResponse['status'];
            $explanation = 'Status unknown';
            // status explanation source // https://developer.paypal.com/docs/api/payments/v2/
            if($status=='COMPLETED'){
                if(!$Order->IsFullPaid && $Order->VersionDb >= 26){
                    $hasToBeCompleted = true;
                }
                $explanation = 'the funds for this captured payment were credited to the payee\'s PayPal account';
            }
            if($status=='DECLINED'){
                $explanation = 'the funds could not be captured';
            }
            if($status=='PARTIALLY_REFUNDED'){
                $explanation = 'an amount less than this captured payment\'s amount was partially refunded to the payer';
            }
            if($status=='PENDING'){
                $explanation = 'the funds for this captured payment was not yet credited to the payee\'s PayPal account. For more information, see status.details';
            }
            if($status=='REFUNDED'){
                $explanation = 'an amount greater than or equal to this captured payment\'s amount was refunded to the payer';
            }
            if($status=='FAILED'){
                $explanation = 'there was an error while capturing payment';
            }

			if($Order->VersionDb >= 26 && $Order->PaymentStatus != $status){
				$wpdb->update(
					"$tablename_ecommerce_orders",
					array('PaymentStatus' => cg1l_sanitize_method($status)),
					array('id' => $OrderId),
					array('%s'),
					array('%d')
				);
			}

			if($status!='COMPLETED'){
				$isPayPalResponseError = true;
				echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer'>
<p style='text-align: center;'><b>Status:</b> $status ($explanation)</p>
</div>"; return;
			}

    }else{
			echo "<div  id='mainCGdivOrderContainer' class='mainCGdivOrderContainer mainCGdiv'>
Payment type not set
</div>"; return;
    }

		if($hasToBeCompleted){
	        cg_ecommerce_processing_afterwards($Order,$OrderIdHash,$status,$ecommerce_options);
	        $LogForDatabase = cg_ecommerce_get_log_modified($LogForDatabase,$OrderId);
            // order might be modified, by adding invoice
			$Order = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$OrderId' LIMIT 1");
		}

		unset($ecommerceOptions['PayPalLiveSecret']);
		unset($ecommerceOptions['PayPalSandboxSecret']);
        unset($ecommerceOptions['StripeLiveSecret']);
		unset($ecommerceOptions['StripeSandboxSecret']);

		$currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
		$ecommerceCountries = cg_get_countries();


		?>
        <script data-cg-processing="true">

            if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work
                cgJsData = {};
            }

            cgJsClass.gallery = cgJsClass.gallery || {};
            cgJsClass.gallery.vars = cgJsClass.gallery.vars || {};
            cgJsClass.gallery.vars.ecommerce = cgJsClass.gallery.vars.ecommerce || {};
            cgJsClass.gallery.ecommerce = cgJsClass.gallery.ecommerce || {};
            cgJsClass.gallery.ecommerce.functions = cgJsClass.gallery.ecommerce.functions || {};

            cgJsClass.gallery.vars.ecommerce.cgOrderId = <?php echo json_encode($OrderId); ?>;
            cgJsClass.gallery.vars.ecommerce.cgOrderIdHash = <?php echo json_encode($OrderIdHash); ?>;
            cgJsClass.gallery.vars.ecommerce.OrderNumber = <?php echo json_encode($OrderNumber); ?>;
            cgJsClass.gallery.vars.ecommerce.OrderItemsByEntryPid = <?php echo json_encode($OrderItemsByEntryPid); ?>;

            cgJsClass.gallery.vars.ecommerce.PayPalOrderResponse = <?php echo json_encode($PayPalOrderResponse); ?>;

            cgJsClass.gallery.vars.ecommerce.options = <?php echo json_encode($options);?>;// by gid
            cgJsClass.gallery.vars.ecommerce.rawData = <?php echo json_encode($RawData);?>;// by realId
            cgJsClass.gallery.vars.ecommerce.jsonInfoData = <?php echo json_encode($jsonInfoData);?>;// by realId
            cgJsClass.gallery.vars.ecommerce.queryDataArray = <?php echo json_encode($queryDataArray);?>;// by gid
            cgJsClass.gallery.vars.ecommerce.ecommerceFilesData = <?php echo json_encode($ecommerceFilesDataArray);?>;// by realId
            cgJsClass.gallery.vars.ecommerce.RawDataWhenBuyed = <?php echo json_encode($RawDataWhenBuyed);?>;
            cgJsClass.gallery.vars.ecommerce.uploadedEntriesData = <?php echo json_encode($uploadedEntriesData);?>;

            debugger
            for(var realId in cgJsClass.gallery.vars.ecommerce.rawData){
                if(!cgJsClass.gallery.vars.ecommerce.rawData.hasOwnProperty(realId)){
                    break;
                }
                var gid = cgJsClass.gallery.vars.ecommerce.rawData[realId]['realGid'];

                if(typeof cgJsData[gid] == 'undefined' ){
                    cgJsData[gid] = {};
                }

                if(typeof cgJsData[gid].options == 'undefined' ){
                    cgJsData[gid].options = cgJsClass.gallery.vars.ecommerce.options[gid];
                }

                if(typeof cgJsData[gid].vars == 'undefined' ){
                    cgJsData[gid].vars = {};
                }

                if(typeof cgJsData[gid].vars.rawData == 'undefined' ){
                    cgJsData[gid].vars.rawData = {};
                }

                if(typeof cgJsData[gid].vars.rawDataPreProcessed == 'undefined' ){
                    cgJsData[gid].vars.rawDataPreProcessed = {};
                }

                if(typeof cgJsData[gid].vars.ecommerceFilesData == 'undefined' ){
                    cgJsData[gid].vars.ecommerceFilesData = {};
                }

                if(typeof cgJsData[gid].vars.info == 'undefined' ){
                    cgJsData[gid].vars.info = {};
                }

                cgJsData[gid].vars.rawData[realId] = cgJsClass.gallery.vars.ecommerce.rawData[realId];
                cgJsData[gid].vars.rawDataPreProcessed[realId] = cgJsData[gid].vars.rawData[realId];
                debugger
                cgJsData[gid].vars.ecommerceFilesData[realId] = cgJsClass.gallery.vars.ecommerce.ecommerceFilesData[realId];

                if(typeof cgJsData[gid].vars.queryDataArray == 'undefined' ){
                    cgJsData[gid].vars.queryDataArray = {};
                    cgJsData[gid].vars.queryDataArray = cgJsClass.gallery.vars.ecommerce.queryDataArray[gid];
                }

                if(cgJsClass.gallery.vars.ecommerce.jsonInfoData[realId]){
                    cgJsData[gid].vars.info[realId] = cgJsClass.gallery.vars.ecommerce.jsonInfoData[realId];
                }
                cgJsData[gid].vars.isOnlyGalleryEcommerce = true;

            }
            debugger
            cgJsClass.gallery.vars.ecommerce.isShowSaleOrder = true;
            cgJsClass.gallery.vars.ecommerce.isForAdmin = true;
            cgJsClass.gallery.vars.ecommerce.currenciesArray = <?php echo json_encode($currenciesArray); ?>;
            cgJsClass.gallery.vars.ecommerce.ecommerceOptions = <?php echo json_encode($ecommerceOptions); ?>;
            cgJsClass.gallery.vars.ecommerce.ecommerceCountries = <?php echo json_encode($ecommerceCountries); ?>;
            cgJsClass.gallery.vars.ecommerce.LogForDatabase = <?php echo json_encode($LogForDatabase);?>;
        </script>
		<?php
	} else {
		?>
        <script data-cg-processing="true">
            var gid = <?php echo json_encode($GalleryID);?>;
            cgJsClass.gallery.function.message.show(gid,'Order id could not be found');
        </script>
		<?php
		return;
	}

    $environment = ' (live environment)';
	if($Order->IsTest){
		$environment = ' (test environment)';
	}
    $TransactionIdText = "<b>PayPal Transaction ID$environment</b><br>$PayPalTransactionId";
    $paymentStatusTextAndUrl = "All possible captured payment statuses can be found <a target='_blank' href='https://developer.paypal.com/docs/api/payments/v2/#definition-capture_status'>...developer.paypal.com/docs/api/payments...</a>";

	if($Order->PaymentType == 'stripe'){
		$paymentStatusTextAndUrl = "All possible payment intents statuses can be found <a target='_blank' href='https://docs.stripe.com/payments/paymentintents/lifecycle'>...docs.stripe.com/payments/paymentintents/lifecycle...</a>";
		$StripePiId = $Order->StripePiId;
		$StripePiPaymentMethodId = $Order->StripePiPaymentMethodId;
		$StripePiPaymentMethodConfDetailsId = $Order->StripePiPaymentMethodConfDetailsId;
		$StripePiClientSecret = $Order->StripePiClientSecret;
		$TransactionIdText = "<b>Stripe Payment Intent ID$environment</b><br>$StripePiId";
	}


	echo "<div style='visibility: hidden;' id='mainCGdivOrderContainer' class='mainCGdivOrderContainer' >
<p style='text-align: center;margin-bottom: 0;'><b>Status:</b> $status ($explanation)<br>
$paymentStatusTextAndUrl</p>
<div style='display:flex; text-align: center; justify-content: space-around;  max-width: 600px;margin: 0 auto;'>
    <div>
    <p>$TransactionIdText</p>
    </div>
    <div>
    <p ><b>Payer email</b><br>$PayerEmail</p>
    </div>
</div>
</div>";

	cg_ecommerce_show_api_response();

	$galeryIDuserForJs = $GalleryID;

	include(__DIR__ . "/../../../check-language.php");
	include(__DIR__ . "/../../../check-language-ecommerce.php");

	include(__DIR__.'/../../../v10/v10-frontend/data/check-language-javascript.php');
	include(__DIR__.'/../../../v10/v10-frontend/data/check-language-javascript-ecommerce.php');

	?>

    <script data-cg-processing="true">
        cgJsClass.gallery.vars.ecommerce.cgVersion = <?php echo json_encode(cg_get_version());?>;
        cgJsClass.gallery.vars.ecommerce.site_url = <?php echo json_encode(get_site_url());?>;
        cgJsClass.gallery.vars.ecommerce.ForwardAfterPurchaseUrl = <?php echo json_encode($ecommerceOptions['ForwardAfterPurchaseUrl']);?>;
        cgJsClass.gallery.vars.ecommerce.Order = <?php echo json_encode($Order);?>;
        cgJsClass.gallery.vars.ecommerce.OrderId = <?php echo json_encode($OrderId);?>;
        cgJsClass.gallery.vars.ecommerce.OrderIdHash = <?php echo json_encode($OrderIdHash);?>;
        cgJsClass.gallery.vars.ecommerce.admin_url = <?php echo json_encode(admin_url());?>;
        cgJsClass.gallery.vars.ecommerce.EUshortcodes = <?php echo json_encode(cg_get_eu_countries_shortcodes());?>;
        cgJsClass.gallery.vars.ecommerce.isShowSaleOrder = true;
        cgJsClass.gallery.vars.ecommerce.is_admin = <?php echo json_encode(is_admin()); ?>;
        cgJsClass.gallery.vars.ecommerce.StripePiClientSecret = <?php echo json_encode($StripePiClientSecret);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiId = <?php echo json_encode($StripePiId);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiPaymentMethodId = <?php echo json_encode($StripePiPaymentMethodId);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiPaymentMethodConfDetailsId = <?php echo json_encode($StripePiPaymentMethodConfDetailsId);?>;

        cgJsClass.gallery.language.ecommerce.OrderSuccessfulYouWillBeRedirected = <?php echo json_encode($language_OrderSuccessfulYouWillBeRedirected); ?>;
        cgJsClass.gallery.language.ecommerce.OrderFailedContactAdministrator = <?php echo json_encode($language_OrderFailedContactAdministrator); ?>;

        if(!typeof cgJsClassEcommerceShowSaleOrderLoaded != 'undefined' ){
            cgJsClass.gallery.ecommerce.functions.showSaleOrder();
        }
    </script>
	<?php


}
?>