<?php
if(!function_exists('cg_test_stripe_keys')){
    function cg_test_stripe_keys($clientId,$secret){


	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/tokens");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":");

	    $response = json_decode(curl_exec($ch),true);

	    if( curl_errno($ch) ){
		    echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);

	    if(substr($response["error"]["message"],0, 24 ) == "Invalid API Key provided"){
		    //echo "Invalid API Key provided";
		    return 'client not working';
	    }

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/tokens");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, $secret . ":");

	    $response = json_decode(curl_exec($ch),true);

	    if( curl_errno($ch) ){
		    echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);

	    if(substr($response["error"]["message"],0, 24 ) == "Invalid API Key provided"){
		    //echo "Invalid API Key provided";
		    return 'secret not working';
	    }

	    return '';

   }
}
