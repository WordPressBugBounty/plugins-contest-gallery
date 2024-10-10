<pre>
    <noscript>
        <div style="border: 1px solid purple; padding: 10px">
            <span style="color:red">Enable JavaScript to show the sale order</span>
        </div>
    </noscript>
</pre>
<?php
if (!defined('ABSPATH')) {
	exit;
}

// simply some gallery id here and it will work
$GalleryID = 1;

$galeryIDuserForJs = $GalleryID;

$is_frontend = true;
include(__DIR__ . "/../../../check-language.php");
include(__DIR__ . "/../../../check-language-general.php");
include(__DIR__ . "/../../../check-language-ecommerce.php");

//$_GET['order_id'] = 1;
if(empty($_GET['cg_order'])){
	echo "<p style='text-align: center;'><b>No order ID provided</b></p>";
	return;
}

global $wpdb;
$table_posts = $wpdb->prefix."posts";
$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_ecommerce_entries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";

// please provide sales id should be visible if not provided
// ales id sanitize $_GET und dann verarbeiten

$OrderIdHash = sanitize_text_field($_GET['cg_order']);
//$optionsNormal = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id='$OrderId'");

$SaleOrder = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE OrderIdHash = '$OrderIdHash' LIMIT 1");

if(empty($SaleOrder)){
	echo "<p style='text-align: center;'><b>Order not found</b></p>";
}else{

	$wp_upload_dir = wp_upload_dir();

	$OrderId = $SaleOrder->id;
	$OrderNumber = $SaleOrder->OrderNumber;
	$InvoiceFilePath = $SaleOrder->InvoiceFilePath;
	$LogForDatabase = unserialize($SaleOrder->LogForDatabase);
//$RawData = unserialize($SaleOrder->RawData);

	$RawData = [];
	$RawDataWhenBuyed = [];
	$options = [];

	$OrderItems = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders_items WHERE ParentOrder = '$OrderId' ");

	$RawData = [];
	$ecommerceFilesDataArray = [];
	$GalleryIDs = [];

	foreach ($OrderItems as $OrderItem){
		if(in_array($OrderItem->GalleryID,$GalleryIDs)===false){
			$GalleryIDs[] = $OrderItem->GalleryID;
		}
	}

	$jsonInfoData = [];
	$dataForOrder = [];
	$queryDataArray = [];
	$uploadedEntries = [];
	$uploadedEntriesData = [];
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
        $pid = $OrderItem->pid;

        if(!empty($OrderItem->Uploaded)){
	        $uploaded = $wpdb->get_results("SELECT * FROM $tablename WHERE OrderItem = '$OrderItemID' ORDER BY id DESC");
            foreach ($uploaded as $entry){
                if(!isset($uploadedEntries[$OrderItemID])){
	                $uploadedEntries[$OrderItemID] = [];
                }
	            $uploadedEntries[$OrderItemID][] = $entry->id;
            }
        }

		$dataForOrder = cg_get_data_for_order($wp_upload_dir,$OrderItem,$RawData);
		if($OrderItem->IsUpload){
			$hasUploadSell = true;
			$UploadGallery = $wpdb->get_var("SELECT UploadGallery FROM $tablename_ecommerce_entries WHERE pid = '$pid' LIMIT 1");
			$UploadGalleries[$OrderItem->id] = $UploadGallery;
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

		$ecommerceOptions = $wpdb->get_row("SELECT * FROM $tablenameEcommerceOptions WHERE GeneralID = '1'");
		$ecommerceOptions = json_decode(json_encode($ecommerceOptions),true);

		$FeControlsStyleOrder = ($ecommerceOptions['FeControlsStyleOrder'] == 'white') ? 'cg_fe_controls_style_white' : '';
		$BorderRadiusOrder = ($ecommerceOptions['BorderRadiusOrder'] == '1') ? 'cg_border_radius_controls_and_containers' : '';

		$WpUserIdOrder = $SaleOrder->WpUserId;
		$WpUserIdLoggedIn = get_current_user_id();

		$user = wp_get_current_user();

		$isAllowedUser = false;

		if (
			is_super_admin($user->ID) ||
			in_array( 'administrator', (array) $user->roles ) ||
			in_array( 'editor', (array) $user->roles ) ||
			in_array( 'author', (array) $user->roles )
		) {
			$isAllowedUser = true;
		}

		if (is_user_logged_in() && $WpUserIdOrder && $WpUserIdLoggedIn) {
			$isAllowedUser = true;
		}

		if(
			($ecommerceOptions['RegUserOrderSummaryOnly']==1 && !is_user_logged_in())
			||
			(is_user_logged_in() && !$isAllowedUser)
		){
			$RegUserOrderSummaryOnlyText = contest_gal1ery_convert_for_html_output_without_nl2br($ecommerceOptions['RegUserOrderSummaryOnlyText']);
			echo "<div  id='mainCGdivOrderContainer' class='mainCGdivOrderContainer mainCGdiv $FeControlsStyleOrder $BorderRadiusOrder'>
$RegUserOrderSummaryOnlyText
</div>"; return;
		}
		if($SaleOrder->IsTest){
		    $accessToken = cg_paypal_get_access_token($ecommerceOptions['PayPalSandboxClientId'],$ecommerceOptions['PayPalSandboxSecret'],true);
		}else{
		    $accessToken = cg_paypal_get_access_token($ecommerceOptions['PayPalLiveClientId'],$ecommerceOptions['PayPalLiveSecret']);
		}
		if($accessToken=='no-internet'){
			$isPayPalResponseError = true;// wil be defined in cg_order_summary
			echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer mainCGdiv $FeControlsStyleOrder $BorderRadiusOrder' >
<p style='text-align: center;margin:10px;'>No internet connection</p>
</div>"; return;
		}else if($accessToken=='error'){
			$isPayPalResponseError = true;
			//Access Token could not be created. Wrong client id or wrong secret.
			echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer mainCGdiv $FeControlsStyleOrder $BorderRadiusOrder'>
<p style='text-align: center;margin:10px;'>PayPal client authentication failed</p>
</div>"; return;
		}

		$PayPalOrderResponse = cg_get_paypal_order($accessToken,$SaleOrder->PayPalTransactionId,$SaleOrder->IsTest);
		if(empty($PayPalOrderResponse['status'])){
			$isPayPalResponseError = true;
			echo "<div  id='mainCGdivOrderContainer' class='mainCGdivOrderContainer mainCGdiv $FeControlsStyleOrder $BorderRadiusOrder'>
<p style='text-align: center;margin:10px;'><b>Error getting PayPal order. Reload this page to try again.</b></p>
</div>"; return;
		}

		if(!empty($_GET['cg_is_after_purchase'])){
			?>
            <pre>
            <script data-cg-processing="true">
                var domain = <?php echo json_encode(get_bloginfo('wpurl')); ?>;
                localStorage.removeItem('cgEcomCustomerProducts_for_'+domain);
            </script>
        </pre>
			<?php
		}

	//	$status = $PayPalOrderResponse['status'];
		$status = 'COMPLETED';
		$explanation = 'Status unknown';
		// status explanation source // https://developer.paypal.com/docs/api/payments/v2/
		if($status=='COMPLETED'){
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
		if($status!='COMPLETED'){
			$isPayPalResponseError = true;
			echo "<div  id='mainCGdivOrderContainer'  class='mainCGdivOrderContainer mainCGdiv $FeControlsStyleOrder $BorderRadiusOrder'>
<p style='text-align: center;'><b>Status:</b> $status ($explanation)</p>
</div>"; return;
		}

		unset($ecommerceOptions['PayPalLiveSecret']);
		unset($ecommerceOptions['PayPalSandboxSecret']);
		$currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();
		$ecommerceCountries = cg_get_countries();

		?>
        <pre>
    <script data-cg-processing="true">
        if(typeof cgJsData == 'undefined' ){ // required in JavaScript for first initialisation cgJsData = cgJsData || {}; would not work
            cgJsData = {};
        }

        if(typeof cgJsClass == 'undefined' ){ // required in JavaScript for first initialisation cgJsClass = cgJsClass || {}; would not work
            cgJsClass = {};
        }

        cgJsClass.gallery = cgJsClass.gallery || {};
        cgJsClass.gallery.vars = cgJsClass.gallery.vars || {};
        cgJsClass.gallery.vars.ecommerce = cgJsClass.gallery.vars.ecommerce || {};
        cgJsClass.gallery.ecommerce = cgJsClass.gallery.ecommerce || {};
        cgJsClass.gallery.ecommerce.functions = cgJsClass.gallery.ecommerce.functions || {};

        cgJsClass.gallery.vars.ecommerce.OrderItemsByEntryPid = <?php echo json_encode($OrderItemsByEntryPid); ?>;
        cgJsClass.gallery.vars.ecommerce.cgOrderId = <?php echo json_encode($OrderId); ?>;
        cgJsClass.gallery.vars.ecommerce.cgOrderIdHash = <?php echo json_encode($OrderIdHash); ?>;

        cgJsClass.gallery.vars.ecommerce.options = <?php echo json_encode($options);?>;
        cgJsClass.gallery.vars.ecommerce.rawData = <?php echo json_encode($RawData);?>;
        cgJsClass.gallery.vars.ecommerce.jsonInfoData = <?php echo json_encode($jsonInfoData);?>;
        cgJsClass.gallery.vars.ecommerce.queryDataArray = <?php echo json_encode($queryDataArray);?>;
        cgJsClass.gallery.vars.ecommerce.ecommerceFilesData = <?php echo json_encode($ecommerceFilesDataArray);?>;
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

        cgJsClass.gallery.vars.ecommerce.isShowSaleOrder = true;
        cgJsClass.gallery.vars.ecommerce.Order = <?php echo json_encode($SaleOrder); ?>;
        cgJsClass.gallery.vars.ecommerce.currenciesArray = <?php echo json_encode($currenciesArray); ?>;
        cgJsClass.gallery.vars.localeLang = <?php echo json_encode(get_locale()); ?>;
        cgJsClass.gallery.vars.ecommerce.ecommerceOptions = <?php echo json_encode($ecommerceOptions); ?>;
        cgJsClass.gallery.vars.ecommerce.ecommerceCountries = <?php echo json_encode($ecommerceCountries); ?>;
        cgJsClass.gallery.vars.ecommerce.LogForDatabase = <?php echo json_encode($LogForDatabase);?>;
        cgJsClass.gallery.vars.ecommerce.site_url = <?php echo json_encode(get_site_url());?>;
        cgJsClass.gallery.vars.ecommerce.OrderId = <?php echo json_encode($OrderId);?>;
        cgJsClass.gallery.vars.ecommerce.OrderNumber = <?php echo json_encode($OrderNumber);?>;
        cgJsClass.gallery.vars.ecommerce.is_admin = <?php echo json_encode(is_admin()); ?>;
        cgJsClass.gallery.vars.ecommerce.EUshortcodes = <?php echo json_encode(cg_get_eu_countries_shortcodes());?>;
        cgJsClassEcommerceShowSaleOrderLoaded = true;

        debugger

    </script>
    </pre>

		<?php


	} else {
		?>
        <pre>
        <script data-cg-processing="true">
            var gid = <?php echo json_encode($GalleryID);?>;
            cgJsClass.gallery.function.message.show(gid,'Order id could not be found');
        </script>
    </pre>
		<?php
		$isPayPalResponseError = true;
		return;
	}

// will be determined in initSaleOrder
	$cgFeControlsStyle = '';
	$BorderRadiusClass = '';

	echo "<div style='visibility: hidden;' id='mainCGdivOrderContainer' class='mainCGdivOrderContainer mainCGdiv' ></div>";

	include(__DIR__.'/../gallery/cg-messages.php');

	include(__DIR__.'/../data/check-language-javascript.php');
	include(__DIR__.'/../data/check-language-javascript-general.php');
	include(__DIR__.'/../data/check-language-javascript-ecommerce.php');

	?>
    <pre>
<script data-cg-processing="true">
    if(typeof cgJsClassEcommerceShowSaleOrderLoaded != 'undefined' ){
        //cgJsClass.gallery.ecommerce.functions.showSaleOrder(); // does not work here in this moment
    }
</script>
        </pre>
	<?php
}
?>