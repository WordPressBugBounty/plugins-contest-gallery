<?php
if(!function_exists('cg_stripe_update_payment_method')){
    function cg_stripe_update_payment_method($secret,$StripePiPaymentMethodId,$email){

	    $InvoiceAddressFirstName = (isset($_POST['cgInvoiceAddressFirstName'])) ? $_POST['cgInvoiceAddressFirstName'] : '';
	    $InvoiceAddressLastName = (isset($_POST['cgInvoiceAddressLastName'])) ? $_POST['cgInvoiceAddressLastName'] : '';

	    $invoiceAddressArray = cg_stripe_get_invoice_address_array();

	    $params = [
		    'billing_details' => [
			    'name' => $InvoiceAddressFirstName.' '.$InvoiceAddressLastName,
			    'email' => $email,
				'address' => $invoiceAddressArray
		    ]
	    ];

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods/'.$StripePiPaymentMethodId);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	    curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	    $headers = array();
	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = json_decode(curl_exec($ch),true);

	    if (curl_errno($ch)) {
		    echo 'Error cg_stripe_update_payment_method:' . curl_error($ch);
	    }else{
		    if(!empty($result['error']['message'])){
				// update not possible for PayPal, Debit and some other methods
			    // error message then:
			    // PaymentMethods of type paypal cannot be updated at this time
			    //echo 'Error cg_stripe_update_payment_method:' . $result['error']['message'];
		    }
	    }
	    curl_close($ch);

		//var_dump('$result444555');
		//var_dump($result);

		if(!empty($result['type'])){
			return $result['type'];
		}else{
			return '';
		}

    }
}

if(!function_exists('cg_stripe_get_payment_method')){
    function cg_stripe_get_payment_method($secret,$paymentId){

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_methods/'.$paymentId);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 0);
	    curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	    $headers = array();
	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = json_decode(curl_exec($ch),true);

	    if (curl_errno($ch)) {
		    echo 'Error cg_stripe_get_payment_method:' . curl_error($ch);
	    }else{
		    if(!empty($result['error']['message'])){
			    echo 'Error cg_stripe_get_payment_method:' . $result['error']['message'];
		    }
	    }
	    curl_close($ch);

		if(!empty($result['type'])){
			return $result['type'];
		}else{
			return '';
		}

    }
}
