<?php
if(!function_exists('cg_paypal_get_access_token')){
    function cg_paypal_get_access_token($clientId,$secret,$isSandbox = false){

        $ch = curl_init();
        if($isSandbox){
            curl_setopt($ch, CURLOPT_URL, "https://api.sandbox.paypal.com/v1/oauth2/token");
        }else{
            curl_setopt($ch, CURLOPT_URL, "https://api-m.paypal.com/v1/oauth2/token");
        }
	    //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);// has to be set, especially for localhost otherwise "HTTP/2 stream 0 was not closed cleanly: INTERNAL_ERROR (err 2)" error appeared
	    //curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Expect:') );
	    //curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);// no-cache
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION , 6); //NEW ADDITION
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $clientId.":".$secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $result = curl_exec($ch);
	    curl_close($ch);
	    $error_msg = curl_error($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
     /*   echo "Error: <br>";

        echo "<pre>";
        print_r(json_decode($error_msg,true));
        echo "</pre>";*/

        $result = json_decode($result,true);
        /*curl_close($ch);

        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "Result:<br>";


        echo "<pre>";
        print_r($result);
        echo "</pre>";

        die;*/

        if(!empty($result['access_token'])){
            $accessToken = $result['access_token'];
        }else if(!empty($result['error'])){
            $accessToken = 'error';
        }else{// then must be empty result, happens if no internet
            $accessToken = 'no-internet';
        }

        return $accessToken;

   }
}
