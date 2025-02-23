<?php

$_POST = cg1l_sanitize_post($_POST);

global $wpdb;
$tablenameEcommerceEntries = $wpdb->prefix . "contest_gal1ery_ecommerce_entries";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";

$wp_upload_dir = wp_upload_dir();

$RawDataEachGallery = [];
$InfoDataEachGallery = [];
$OptionsDataEachGallery = [];
$EcommerceFilesDataEachGallery = [];

$StripePiKey = '';
$StripePiId = '';
$ecommerceOptions = $wpdb->get_row("SELECT * FROM $tablenameEcommerceOptions WHERE GeneralID = '1'");

$StripeError = '';

if(isset($_POST['cgPurchaseUnits']) && !empty($ecommerceOptions->StripeApiActive)){

    $data = $_POST['cgPurchaseUnits'];

	/*echo "<pre>";
	print_r($data);
	echo "</pre>";*/

	$currency = strtolower($data[0]['amount']['breakdown']['item_total']['currency_code']);
	//$currency = 'eur';
	$amount = absint($data[0]['amount']['value']/0.01);

    //var_dump($data[0]['amount']['value']);

    $metaData = [];
	$metaData['currency_code'] =  $data[0]['amount']['currency_code'];
	$metaData['total_value'] =  $data[0]['amount']['value'];
	$metaData['items_total_value'] = $data[0]['amount']['breakdown']['item_total']['value'];
	$metaData['shipping'] = !empty($data[0]['amount']['breakdown']['shipping']['value']) ? $data[0]['amount']['breakdown']['shipping']['value'] : '0.00';

    foreach ($data[0]['items'] as $key => $item){
	    $metaData['item_'.($key+1)] = substr('name: '.substr($item['name'],0,60).';quantity: '.$item['quantity'].';priceGross: '.$item['priceGross'].';unit_amount: '.$item['unit_amount']['value'].';tax : '.$item['tax']['value'].';tax_percentage : '.$item['tax_percentage'].';SaleType : '.$item['SaleType'].';Entry ID : '.$item['realId'].';Gallery ID : '.$item['realGid'],0,499);
    }

	$params = [
		'currency' => $currency,
		'amount' => $amount,
		//'amount' => '1000',// 1 = 0.01
		//'description' => 'Description here',
	//	'off_session' => 'true',
		//'confirm' => 'true',
		//'setup_future_usage' => 'off_session',// so customer id can be attached later to  payment method
		'metadata' => $metaData
	];

	$ch = curl_init();

    //var_dump('isTestEnv');
    //var_dump($_POST['isTestEnv']);

	// to be checked this way, because string might sent
	if(!empty($_POST['isTestEnv']) && $_POST['isTestEnv'] == 1){
		$secret = $ecommerceOptions->StripeSandboxSecret;
	}else{
		$secret = $ecommerceOptions->StripeLiveSecret;
	}

    //var_dump('$secret');
    //var_dump($secret);

	curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	$headers = array();
	$headers[] = 'Content-Type: application/x-www-form-urlencoded';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = json_decode(curl_exec($ch),true);
	if (curl_errno($ch)) {
		$StripeError = 'Error:' . curl_error($ch);
	}else{
		if(!empty($result['error']['message'])){
			$StripeError = 'Error:' . $result['error']['message'];
		}else{
			$StripePiId = $result['id'];
			$StripePiKey = $result['client_secret'];
		}
	}

	curl_close($ch);

	/*echo "<pre>";
	print_r($result);
	echo "</pre>";*/

	?>
    <script data-cg-processing="true">
        cgJsClass.gallery.vars.ecommerce.StripeError = <?php echo json_encode($StripeError);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiId = <?php echo json_encode($StripePiId);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiKey = <?php echo json_encode($StripePiKey);?>;
        cgJsClass.gallery.vars.ecommerce.StripePiResult = <?php echo json_encode($result);?>;
    </script>
	<?php
	return;

}