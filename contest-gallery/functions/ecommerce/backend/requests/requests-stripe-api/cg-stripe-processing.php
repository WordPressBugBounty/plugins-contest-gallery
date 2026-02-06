<?php
if(!function_exists('cg_stripe_get_shipping_address_array')){
    function cg_stripe_get_shipping_address_array(){

	    $ShippingAddressCompany =(isset( $_POST['cgShippingAddressCompany'])) ? $_POST['cgShippingAddressCompany'] : '';
	    $ShippingAddressLine1 = (isset($_POST['cgShippingAddressLine1'])) ? $_POST['cgShippingAddressLine1'] : '';
	    $ShippingAddressLine2 = (isset($_POST['cgShippingAddressLine2'])) ? $_POST['cgShippingAddressLine2'] : '';
	    $ShippingAddressStateShort =(isset($_POST['cgShippingAddressStateShort'])) ? $_POST['cgShippingAddressStateShort'] : '';
	    $ShippingAddressStateTranslation =(isset($_POST['cgShippingAddressStateTranslation'])) ? $_POST['cgShippingAddressStateTranslation'] : '';
	    $ShippingAddressCity = (isset($_POST['cgShippingAddressCity'])) ? $_POST['cgShippingAddressCity'] : '';
	    $ShippingAddressPostalCode = (isset($_POST['cgShippingAddressPostalCode'])) ? $_POST['cgShippingAddressPostalCode'] : '';
	    $ShippingAddressCountryShort = (isset($_POST['cgShippingAddressCountryShort'])) ? $_POST['cgShippingAddressCountryShort'] : '';
	    $ShippingAddressCountryTranslation = (isset($_POST['cgShippingAddressCountryTranslation'])) ? $_POST['cgShippingAddressCountryTranslation'] : '';

		if(!empty($ShippingAddressLine1)){
			return [
				'city' => $ShippingAddressCity,
				'country' => $ShippingAddressCountryShort,
				'line1' => $ShippingAddressLine1,
				'line2' => $ShippingAddressLine2,
				'postal_code' => $ShippingAddressPostalCode,
				'state' => $ShippingAddressStateTranslation,
			];
		}else{
			return [];
		}

    }
}

if(!function_exists('cg_stripe_get_invoice_address_array')){
    function cg_stripe_get_invoice_address_array(){

	    $InvoiceAddressCompany = (isset($_POST['cgInvoiceAddressCompany'])) ? $_POST['cgInvoiceAddressCompany'] : '';
	    $InvoiceAddressLine1 = (isset($_POST['cgInvoiceAddressLine1'])) ? $_POST['cgInvoiceAddressLine1'] : '';
	    $InvoiceAddressLine2 = (isset($_POST['cgInvoiceAddressLine2'])) ? $_POST['cgInvoiceAddressLine2'] : '';
	    $InvoiceAddressStateShort = (isset($_POST['cgInvoiceAddressStateShort'])) ? $_POST['cgInvoiceAddressStateShort'] : '';
	    $InvoiceAddressStateTranslation = (isset($_POST['cgInvoiceAddressStateTranslation'])) ? $_POST['cgInvoiceAddressStateTranslation'] : '';
	    $InvoiceAddressCity = (isset($_POST['cgInvoiceAddressCity'])) ? $_POST['cgInvoiceAddressCity'] : '';
	    $InvoiceAddressPostalCode = (isset($_POST['cgInvoiceAddressPostalCode'])) ? $_POST['cgInvoiceAddressPostalCode'] : '';
	    $InvoiceAddressCountryShort = (isset($_POST['cgInvoiceAddressCountryShort'])) ? $_POST['cgInvoiceAddressCountryShort'] : '';
	    $InvoiceAddressCountryTranslation = (isset($_POST['cgInvoiceAddressCountryTranslation'])) ? $_POST['cgInvoiceAddressCountryTranslation'] : '';
		return [
			    'city' => $InvoiceAddressCity,
			    'country' => $InvoiceAddressCountryShort,
			    'line1' => $InvoiceAddressLine1,
			    'line2' => $InvoiceAddressLine2,
			    'postal_code' => $InvoiceAddressPostalCode,
			    'state' => $InvoiceAddressStateTranslation,
		];
    }
}

