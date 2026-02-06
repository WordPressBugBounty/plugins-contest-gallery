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
$realIdsAndUnits = $_POST['realIdsAndUnits'];
$purchaseUnits = $_POST['purchaseUnitsOrEcommerceForSale'];

$StripeURL = '';
$ecommerceOptions = $wpdb->get_row("SELECT * FROM $tablenameEcommerceOptions WHERE GeneralID = '1'");

$StripeError = '';

/*
echo "<pre>";
    print_r($_POST['purchaseUnitsOrEcommerceForSale']);
echo "</pre>";
*/

if(isset($_POST['cgDataForStripe']) && !empty($ecommerceOptions->StripeApiActive)){
	$line_items = [];
	$meta_data = [];

	foreach($_POST['cgDataForStripe'] as $GalleryID => $data){

        //$realId = key($data);
        $realId = absint($data['realId']);
        $unit_amount = absint($data['Price']/0.01);

		$name = $data['name'];

        $line_items[] =  [
            // 'price' => $result['id'],
            //'price' => 'price_1QiFYiI0lc6JnfOnNDRuFAGQ',
            //  'quantity' => 10
            'quantity' => absint($data['units']),
            'price_data' => [
                'currency' => strtolower($ecommerceOptions->CurrencyShort),
                'unit_amount' => $unit_amount,
                'product_data' => [
                    'name' => $name,
                    //'description' => 'Product description only session no price 1',
                ]
            ]
        ];

        $meta_data['pid-'.$realId] = $realId;
        $meta_data['amount'] = serialize($purchaseUnits[0]['amount']);
        $meta_data['item-0'] = serialize($purchaseUnits[0]['items'][0]);

        /*
        echo "<pre>";
        print_r($meta_data);
        echo "</pre>";
        */

        if(!empty($line_items)){

            $referrer = wp_get_referer();

            $params = [
                //  'payment_method_types' => [
                //   'card', 'klarna'
                //  ],
                'line_items' => $line_items,
                'mode' => 'payment',
                'success_url' => $ecommerceOptions->ForwardAfterPurchaseUrl.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $referrer,
                'metadata' => $meta_data,
                /*'metadata' => [
                    'item 1' => '32.35',
                    'item 2' => '45.00'
                ]*/
            ];

            $ch = curl_init();

            if(!empty($_POST['isTestEnv'])){
                $secret = $ecommerceOptions->StripeSandboxSecret;
            }else{
                $secret = $ecommerceOptions->StripeLiveSecret;
            }

            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
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
	                $StripeURL = $result['url'];
                }
	        }

            /*
	        echo "<pre>";
	        print_r($result);
	        echo "</pre>";
            */

        }

	}

	?>
    <script data-cg-processing="true">
        cgJsClass.gallery.vars.ecommerce.StripeError = <?php echo json_encode($StripeError);?>;
        cgJsClass.gallery.vars.ecommerce.StripeURL = <?php echo json_encode($StripeURL);?>;
    </script>
	<?php
	return;

}