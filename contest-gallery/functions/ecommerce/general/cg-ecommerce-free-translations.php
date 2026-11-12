<?php
if(!defined('ABSPATH')){exit;}

// Shared by the translation editor, PHP previews and the frontend language payload.
if(!function_exists('cg_ecommerce_free_language_definitions')){
    function cg_ecommerce_free_language_definitions(){
        return array(
            'Free'=>array('default'=>'Free','translated'=>__('Free','contest-gallery')),
            'GetForFree'=>array('default'=>'Get for free','translated'=>__('Get for free','contest-gallery')),
            'OrderNow'=>array('default'=>'Order now','translated'=>__('Order now','contest-gallery')),
            'PlaceFreeOrder'=>array('default'=>'Place free order','translated'=>__('Place free order','contest-gallery')),
            'PlacingOrder'=>array('default'=>'Placing order…','translated'=>__('Placing order…','contest-gallery')),
            'EmailAddress'=>array('default'=>'Email address','translated'=>__('Email address','contest-gallery')),
            'FreeOrderFailed'=>array('default'=>'The order could not be completed. Please try again.','translated'=>__('The order could not be completed. Please try again.','contest-gallery')),
            'FreeOrderRequiredFields'=>array('default'=>'Enter a valid email address and complete the required fields and agreements.','translated'=>__('Enter a valid email address and complete the required fields and agreements.','contest-gallery')),
            'FreeOrderConfirmationNotSaved'=>array('default'=>'The order was created, but its confirmation could not be saved. Please contact the site administrator.','translated'=>__('The order was created, but its confirmation could not be saved. Please contact the site administrator.','contest-gallery')),
            'FreeOrderCartEmpty'=>array('default'=>'Your cart is empty.','translated'=>__('Your cart is empty.','contest-gallery')),
            'FreeOrderReloadCheckout'=>array('default'=>'Please reload the checkout.','translated'=>__('Please reload the checkout.','contest-gallery')),
            'FreeOrderReloadGallery'=>array('default'=>'Please reload the gallery before ordering.','translated'=>__('Please reload the gallery before ordering.','contest-gallery')),
            'FreeOrderProductUnavailable'=>array('default'=>'This product is no longer available.','translated'=>__('This product is no longer available.','contest-gallery')),
            'FreeOrderCartChanged'=>array('default'=>'The cart has changed. Please reload the checkout.','translated'=>__('The cart has changed. Please reload the checkout.','contest-gallery')),
            'FreeOrderProductNotFree'=>array('default'=>'This product has not been offered for free.','translated'=>__('This product has not been offered for free.','contest-gallery')),
            'FreeOrderSignIn'=>array('default'=>'Please sign in before ordering.','translated'=>__('Please sign in before ordering.','contest-gallery')),
            'FreeOrderSummaryMissing'=>array('default'=>'The order summary page is not configured.','translated'=>__('The order summary page is not configured.','contest-gallery')),
            'FreeOrderMailMissing'=>array('default'=>'The order confirmation email is not configured.','translated'=>__('The order confirmation email is not configured.','contest-gallery')),
            'FreeOrderCookies'=>array('default'=>'Please enable cookies and reload the checkout.','translated'=>__('Please enable cookies and reload the checkout.','contest-gallery')),
            'FreeOrderStartFailed'=>array('default'=>'Checkout could not be started.','translated'=>__('Checkout could not be started.','contest-gallery')),
            'FreeOrderReopenCheckout'=>array('default'=>'Please reopen the checkout.','translated'=>__('Please reopen the checkout.','contest-gallery')),
            'FreeOrderProcessing'=>array('default'=>'Your order is being processed. Please wait.','translated'=>__('Your order is being processed. Please wait.','contest-gallery')),
            'FreeOrderAlreadyProcessing'=>array('default'=>'This order is already being processed. Please check your order confirmation before trying again.','translated'=>__('This order is already being processed. Please check your order confirmation before trying again.','contest-gallery')),
            'FreeOrderInvalidEmail'=>array('default'=>'Enter a valid email address.','translated'=>__('Enter a valid email address.','contest-gallery')),
            'FreeOrderAcceptAgreements'=>array('default'=>'Please accept the checkout agreements.','translated'=>__('Please accept the checkout agreements.','contest-gallery')),
            'FreeOrderShippingAddress'=>array('default'=>'Complete the shipping address.','translated'=>__('Complete the shipping address.','contest-gallery')),
            'FreeOrderShippingCountry'=>array('default'=>'Select an allowed shipping country.','translated'=>__('Select an allowed shipping country.','contest-gallery')),
            'FreeOrderShippingState'=>array('default'=>'Select a shipping state.','translated'=>__('Select a shipping state.','contest-gallery')),
            'FreeOrderSandboxOnly'=>array('default'=>'Sandbox checkout is available only to administrators or through a valid Paid Actions sandbox link.','translated'=>__('Sandbox checkout is available only to administrators or through a valid Paid Actions sandbox link.','contest-gallery')),
            'FreeOrderTestOnly'=>array('default'=>'Available in test mode. Live purchases require PRO.','translated'=>__('Available in test mode. Live purchases require PRO.','contest-gallery'))
        );
    }
}

if(!function_exists('cg_ecommerce_free_error_translation_key')){
    function cg_ecommerce_free_error_translation_key($message){
        $aliases = array(
            'The cart has changed. Please reopen the checkout.'=>'FreeOrderCartChanged',
            'The order could not be started.'=>'FreeOrderStartFailed',
            'The checkout data changed. Please reload the checkout and try again.'=>'FreeOrderCartChanged',
            'Downloads and services are available in test mode with FREE. Live purchases require PRO. Remove these products from the live cart or open the test page.'=>'FreeOrderTestOnly'
        );
        if(isset($aliases[$message])){return $aliases[$message];}
        foreach(cg_ecommerce_free_language_definitions() as $key=>$definition){
            if($definition['default'] === $message){return $key;}
        }
        return 'FreeOrderFailed';
    }
}

if(!function_exists('cg_ecommerce_purchase_action_key')){
    function cg_ecommerce_purchase_action_key($sale,$options){
        if(empty($sale['IsFree'])){return 'BuyNow';}
        if(!empty($sale['IsShipping']) || (!empty($sale['SaleType']) && $sale['SaleType'] === 'shipping')){
            $shipping = !empty($sale['IsAlternativeShipping']) ? (isset($sale['AlternativeShipping']) ? $sale['AlternativeShipping'] : null) : (isset($options['ShippingGross']) ? $options['ShippingGross'] : null);
            // Only promise a free order when the applicable shipping amount is known to be zero.
            if(!is_numeric($shipping) || (float)$shipping != 0){return 'OrderNow';}
        }
        return 'GetForFree';
    }
}
