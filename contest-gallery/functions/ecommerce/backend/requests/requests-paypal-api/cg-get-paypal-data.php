<?php
if(!function_exists('cg_get_paypal_order')){
    function cg_get_paypal_order($accessToken,$id,$isSandbox = false){

	    $id = trim(sanitize_text_field((string)$id));
	    if($id === ''){
		    return array();
	    }
	    $idForUrl = rawurlencode($id);

	    if($isSandbox){
		    $url = "https://api-m.sandbox.paypal.com/v2/checkout/orders/".$idForUrl;
	    }else{
		    $url = "https://api-m.paypal.com/v2/checkout/orders/".$idForUrl;
	    }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer ".$accessToken,
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $resp = curl_exec($curl);
        $curlErrorNumber = curl_errno($curl);
        curl_close($curl);

        if($curlErrorNumber || $resp === false){
            return array();
        }

        $result = json_decode($resp,true);

        return (is_array($result)) ? $result : array();

    }
}

if(!function_exists('cg_get_paypal_data')){
    function cg_get_paypal_data(){

        global $wpdb;
        $tablenamePayPalOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";

        $payPalOptions = $wpdb->get_row("SELECT * FROM $tablenamePayPalOptions WHERE GeneralID = '1'");
        if($_POST['cg_paypal_environment']=='sandbox'){
            $clientId = $payPalOptions->ClientIdSandbox;
            $secret = $payPalOptions->SecretSandbox;
        } elseif($_POST['cg_paypal_environment']=='live'){
            $clientId = $payPalOptions->ClientIdLive;
            $secret = $payPalOptions->SecretLive;
        }
        $accessToken = cg_paypal_get_access_token($clientId,$secret);

        echo "<div id='PayPalInvoiceResult' >";
            if($_POST['cg_invoicing_type']=='invoice-list'){
                cg_paypal_invoice_list($accessToken);
            }
            if($_POST['cg_invoicing_type']=='template-list'){
                cg_paypal_invoice_template_list($accessToken);
            }
            if($_POST['cg_invoicing_type']=='transaction-list'){
                cg_paypal_transaction_list($accessToken);
            }
            if($_POST['cg_invoicing_type']=='invoice-search'){
                cg_paypal_invoice_search($accessToken);
            }
            if($_POST['cg_invoicing_type']=='invoice-delete'){
                cg_paypal_invoice_delete($accessToken);
            }
            if($_POST['cg_invoicing_type']=='invoice-generate-number'){
                cg_paypal_invoice_generate_number($accessToken);
            }
            if($_POST['cg_invoicing_type']=='invoice-new-test'){
                cg_paypal_invoice_draft_create($accessToken);
            }

        if($_POST['cg_invoicing_type']=='invoice-generate-number'){
            cg_paypal_invoice_generate_number($accessToken);
        }
        echo "</div>";
        die;

    }
}
