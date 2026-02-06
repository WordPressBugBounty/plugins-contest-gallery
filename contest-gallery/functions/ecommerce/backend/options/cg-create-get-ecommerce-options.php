<?php
if(!function_exists('cg_create_ecommerce_options')){
    function cg_create_ecommerce_options($i){

        global $wpdb;
        $tablename_ecommerce_options = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_options";
	    $tablename_ecommerce_invoice_options = $wpdb->base_prefix . "$i"."contest_gal1ery_ecommerce_invoice_options";

        $selectSQLecommerceOptions = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = '1' " );

	    if(empty($selectSQLecommerceOptions)){
		    // Determine email of blog admin and variables for email table
		    $OrderConfirmationMailHeader = get_option('blogname');
		    $OrderConfirmationMailReply = get_option('admin_email');
		    $OrderConfirmationMailSubject = 'Your order';
		    $OrderConfirmationMail = 'Dear Sir or Madam<br/>Thank for your order<br>$order$';
		    $AllowedCountries = serialize(['DE','US','ES','FR','GB','IT','NL','PL','PT']);
            $wpdb->query( $wpdb->prepare(
                "
					INSERT INTO $tablename_ecommerce_options
					( id, GeneralID,
					PayPalApiActive,PayPalTestActive,
					PayPalSandboxClientId,PayPalSandboxSecret,
					PayPalLiveClientId,PayPalLiveSecret,
					StripeApiActive,StripeTestActive,
					StripeSandboxClientId,StripeSandboxSecret,
					StripeLiveClientId,StripeLiveSecret,
					Environment,
					CurrencyShort,CurrencyPosition,PriceDivider,
					CreateInvoice,SendInvoice,
					BorderRadiusOrder,FeControlsStyleOrder,
                    SendOrderConfirmationMail,OrderConfirmationMailHeader,OrderConfirmationMailReply,
                    OrderConfirmationMailSubject,OrderConfirmationMail,
                    AllowedCountries, TaxPercentageDefault, ShippingGross,
                    RegUserPurchaseOnlyText, RegUserOrderSummaryOnlyText
					)
					VALUES (
					%s,%d,
					%d,%d,
					%s,%s,
					%s,%s,
					%d,%d,
					%s,%s,
					%s,%s,
			        %s,
					%s,%s,%s,
					%d,%d,
					%d,%s,
					%d,%s,%s,
					%s,%s,
					%s,%d,%d,
					%s,%s
					)",
                '',1,
                2,0,
                '','',
                '','',
                2,0,
                '','',
                '','',
                'sandbox',
                'USD','left','.',
                0,0,
                1,'white',
                0,$OrderConfirmationMailHeader,$OrderConfirmationMailReply,
                $OrderConfirmationMailSubject,$OrderConfirmationMail,
                $AllowedCountries,15,5,
                'You have to be registered and logged in to be able to purchase.','You have to be registered and logged in to see the order summary.',
            ) );
        }

	    $selectSQLecommerceInvoiceOptions = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_invoice_options WHERE GeneralID = '1' ");

	    if(empty($selectSQLecommerceInvoiceOptions)){
		    $wpdb->query( $wpdb->prepare(
			    "
					INSERT INTO $tablename_ecommerce_invoice_options
					( 
					 id,GeneralID,CreateAndSetInvoiceNumber,
					 InvoiceNumberLogicSelectTest,InvoiceNumberLogicOwnPrefixTest,InvoiceNumberLogicCustomNumberTest,
					 InvoiceNumberLogicSelectLive,InvoiceNumberLogicOwnPrefixLive,InvoiceNumberLogicCustomNumberLive,
					 InvoiceNote
					 )
					VALUES (
					%s,%d,%d,
					%s,%s,%s,
					%s,%s,%s,
					%s
					)",
			    '',1,1,
			    'year-month','CG','001',
			    'year-month','CG','001',
			    serialize(['default'=>'default note here'])
		    ) );
	    }
    }
}

if(!function_exists('cg_create_ecommerce_invoice_options')){
    function cg_create_ecommerce_invoice_options($GalleryID){

    }
}

if(!function_exists('cg_get_ecommerce_options')){
    function cg_get_ecommerce_options(){
        global $wpdb;

        $tablename_ecommerce_options = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
        $selectSQLecommerceOptions = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_options WHERE GeneralID = '1'" );

        return $selectSQLecommerceOptions;
    }
}

if(!function_exists('cg_get_ecommerce_invoice_options')){
    function cg_get_ecommerce_invoice_options(){
        global $wpdb;

        $tablename_ecommerce_invoice_options = $wpdb->prefix . "contest_gal1ery_ecommerce_invoice_options";
        $selectSQLecommerceInvoiceOptions = $wpdb->get_row( "SELECT * FROM $tablename_ecommerce_invoice_options WHERE GeneralID = '1'" );

        return $selectSQLecommerceInvoiceOptions;
    }
}


if(!function_exists('cg_get_ecommerce_currencies_array')){
    function cg_get_ecommerce_currencies_array(){
        $array = [
            ["short" => "AUD","symbol" => "$","long" => "Australian dollar - AUD ($)"],
            ["short" => "BRL","symbol" => "R$","long" => "Brazilian real 2 - BRL (R$)"],
            ["short" => "CAD","symbol" => "$","long" => "Canadian dollar - CAD ($)"],
            ["short" => "CNY","symbol" => "¥","long" => "Chinese Renmenbi 3 - CNY (¥)"],
            ["short" => "CZK","symbol" => "Kč","long" => "Czech koruna - CZK (Kč)"],
            ["short" => "DKK","symbol" => "Kr.","long" => "Danish krone - DKK (Kr.)"],
            ["short" => "EUR","symbol" => "€","long" => "Euro - EUR (€)"],
            ["short" => "HKD","symbol" => "$","long" => "Hong Kong - HKD ($)"],
            ["short" => "HUF","symbol" => "Ft","long" => "Hungarian forint 1 - HUF (Ft)"],
            ["short" => "ILS","symbol" => "₪","long" => "Israeli new shekel - ILS (₪)"],
            ["short" => "JPY","symbol" => "¥","long" => "Japanese yen 1 - JPY (¥)"],
            ["short" => "MYR","symbol" => "RM","long" => "Malaysian ringgit 3 - MYR (RM)"],
            ["short" => "MXN","symbol" => "$","long" => "Mexican peso - MXN ($)"],
            ["short" => "TWD","symbol" => "$","long" => "New Taiwan dollar 1 - TWD ($)"],
            ["short" => "NZD","symbol" => "$","long" => "New Zealand dollar - NZD ($)"],
            ["short" => "NOK","symbol" => "kr","long" => "Norwegian krone - NOK (kr)"],
            ["short" => "PLN","symbol" => "zł","long" => "Polish złoty - PLN (zł)"],
            ["short" => "GBP","symbol" => "£","long" => "Pound sterling - GBP (£)"],
            ["short" => "RUB","symbol" => "₽","long" => "Russian ruble - RUB (₽)"],
            ["short" => "SGD","symbol" => "$","long" => "Singapore dollar - SGD ($)"],
            ["short" => "SEK","symbol" => "kr","long" => "Swedish krona - SEK (kr)"],
            ["short" => "CHF","symbol" => "CHf","long" => "Swiss franc - CHF (CHf)"],
            ["short" => "THB","symbol" => "฿","long" => "Thai baht - THB (฿)"],
            ["short" => "USD","symbol" => "$","long" => "United States dollar - USD ($)"],
        ];
        return $array;
    }
}