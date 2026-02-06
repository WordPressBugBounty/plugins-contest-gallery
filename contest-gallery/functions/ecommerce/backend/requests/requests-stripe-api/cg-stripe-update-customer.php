<?php
if(!function_exists('cg_stripe_update_customers')){
    function cg_stripe_update_customers($email,$secret,$id){

	    $InvoiceAddressFirstName = (isset($_POST['cgInvoiceAddressFirstName'])) ? $_POST['cgInvoiceAddressFirstName'] : '';
	    $InvoiceAddressLastName = (isset($_POST['cgInvoiceAddressLastName'])) ? $_POST['cgInvoiceAddressLastName'] : '';

	    $invoiceAddressArray = cg_stripe_get_invoice_address_array();
	    $ShippingAddressArray = cg_stripe_get_shipping_address_array();

	    $params = [
		    'name' => $InvoiceAddressFirstName.' '.$InvoiceAddressLastName,
		    'address' => $invoiceAddressArray,
	    ];

		if(!empty($ShippingAddressArray)){
			$ShippingAddressFirstName = (isset($_POST['cgShippingAddressFirstName'])) ? $_POST['cgShippingAddressFirstName'] : '';
			$ShippingAddressLastName = (isset($_POST['cgShippingAddressLastName'])) ? $_POST['cgShippingAddressLastName'] : '';
			$params['shipping']['name'] = $ShippingAddressFirstName . ' '. $ShippingAddressLastName;
			$params['shipping']['address'] = $ShippingAddressArray;
		}

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/customers/'.$id);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	    curl_setopt($ch, CURLOPT_USERPWD, $secret . ':' . '');

	    $headers = array();
	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = json_decode(curl_exec($ch),true);

	    if (curl_errno($ch)) {
		    echo 'Error cg_stripe_update_customers:' . curl_error($ch);
	    }else{
		    if(!empty($result['error']['message'])){
			    echo 'Error cg_stripe_update_customers:' . $result['error']['message'];
		    }
	    }

	    curl_close($ch);

		return $result;

    }
}
