<?php
if(!function_exists('cg_stripe_attach_customer_to_payment_method')){
    function cg_stripe_attach_customer_to_payment_method($secret,$paymentId,$customerId){

	    $params = [
		    'customer' =>$customerId,
	    ];

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods/'.$paymentId.'/attach');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	    curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	    $headers = array();
	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = json_decode(curl_exec($ch),true);

	    if (curl_errno($ch)) {
		    echo 'Error cg_stripe_attach_customer_to_payment_method:' . curl_error($ch);
	    }else{
		    if(!empty($result['error']['message'])){
			    echo 'Error cg_stripe_attach_customer_to_payment_method:' . $result['error']['message'];
		    }
	    }
	    curl_close($ch);

	    return $result;

    }
}
