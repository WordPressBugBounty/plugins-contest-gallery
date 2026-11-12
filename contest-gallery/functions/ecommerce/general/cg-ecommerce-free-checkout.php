<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_free_checkout_error')){
    function cg_ecommerce_free_checkout_error($message, $status = 409){
        wp_send_json_error(array('message'=>$message,'translationKey'=>cg_ecommerce_free_error_translation_key($message)),$status);
    }
}
if(!function_exists('cg_ecommerce_free_checkout_binding')){
    function cg_ecommerce_free_checkout_binding(){
        $session = cg1l_get_or_create_frontend_session_id(true);
        return $session ? hash_hmac('sha256','cg-free-order|'.get_current_user_id().'|'.$session,wp_salt('auth')) : '';
    }
}
if(!function_exists('cg_ecommerce_free_checkout_result')){
    function cg_ecommerce_free_checkout_result($hash){
        $options = cg_get_ecommerce_options();
        return array('orderHash'=>$hash,'url'=>add_query_arg(array('cg_order'=>$hash,'cg_is_after_purchase'=>'true'),$options->ForwardAfterPurchaseUrl));
    }
}
if(!function_exists('cg_ecommerce_free_checkout_complete')){
    function cg_ecommerce_free_checkout_complete($hash){
        $context = $GLOBALS['cgFreeCheckoutContext'];
        $context['state'] = 'complete';
        $context['order_hash'] = $hash;
        // Persist completion before notifications. A retry must never consume another key or send another mail.
        if(!set_transient($context['key'],$context,DAY_IN_SECONDS)){
            cg_ecommerce_free_checkout_error('The order was created, but its confirmation could not be saved. Please contact the site administrator.',500);
        }
    }
}
if(!function_exists('cg_ecommerce_free_checkout_cart')){
    function cg_ecommerce_free_checkout_cart($data, $options){
        global $wpdb;
        $units = isset($data['purchase_units']) && is_array($data['purchase_units']) ? $data['purchase_units'] : array();
        if(empty($units[0]['items']) || !is_array($units[0]['items'])){cg_ecommerce_free_checkout_error('Your cart is empty.');}
        $seen = array();
        foreach($units[0]['items'] as &$item){
            if(!is_array($item)){cg_ecommerce_free_checkout_error('Please reload the checkout.');}
            $gid = isset($item['realGid']) ? absint($item['realGid']) : 0;
            $pid = isset($item['realId']) ? absint($item['realId']) : 0;
            $shortcode = isset($item['SourceShortcodeName']) && is_string($item['SourceShortcodeName']) ? $item['SourceShortcodeName'] : '';
            $access = isset($item['RawDataAccessHash']) && is_string($item['RawDataAccessHash']) ? $item['RawDataAccessHash'] : '';
            if(!$pid || !$gid || isset($seen[$pid]) || !in_array($shortcode,array('cg_gallery','cg_gallery_user','cg_gallery_no_voting','cg_gallery_winner','cg_gallery_ecommerce'),true) || !cg_hash_equals(cg1l_get_ecommerce_raw_data_access_hash($gid,$pid,$shortcode,get_current_user_id()),$access)){
                cg_ecommerce_free_checkout_error('Please reload the gallery before ordering.');
            }
            $seen[$pid] = true;
            $entry = $wpdb->get_row($wpdb->prepare("SELECT Active, EcommerceEntry FROM {$wpdb->prefix}contest_gal1ery WHERE id=%d AND GalleryID=%d",$pid,$gid));
            if(!$entry || !$entry->Active || empty($item['EcommerceEntryID']) || intval($entry->EcommerceEntry)!==intval($item['EcommerceEntryID'])){
                cg_ecommerce_free_checkout_error('This product is no longer available.');
            }
            if(isset($item['RawData']) && is_string($item['RawData'])){$item['RawData'] = json_decode($item['RawData'],true);}
        }
        unset($item);
        $cart = cg_ecommerce_build_authoritative_checkout_cart($units,$options);
        if(empty($cart['success']) || $cart['cart_type']!=='products' || (float)$cart['PriceTotalGrossItemsWithShipping']!=0){
            cg_ecommerce_free_checkout_error('The cart has changed. Please reload the checkout.');
        }
        foreach($cart['purchase_units'][0]['items'] as $item){
            if(empty($item['IsFree']) || !in_array($item['SaleType'],array('download','service','shipping'),true)){
                cg_ecommerce_free_checkout_error('This product has not been offered for free.');
            }
        }
        $sandbox = !empty($data['isSandbox']) && in_array($data['isSandbox'],array(true,1,'1','true'),true);
        $environment = cg_ecommerce_resolve_checkout_environment($sandbox,'free',$cart);
        if(empty($environment['success'])){cg_ecommerce_free_checkout_error($environment['message']);}
        $available = cg_ecommerce_checkout_keys_available($cart);
        if(is_wp_error($available)){cg_ecommerce_free_checkout_error($available->get_error_message());}
        $cart['is_test'] = !empty($environment['is_test']);
        return $cart;
    }
}
if(!function_exists('post_cg_ecommerce_free_checkout')){
    function post_cg_ecommerce_free_checkout(){
        cg_check_frontend_nonce();
        global $wpdb;
        $options = cg_get_ecommerce_options();
        if(!empty($options->RegUserPurchaseOnly) && !is_user_logged_in()){
            cg_ecommerce_free_checkout_error('Please sign in before ordering.',403);
        }
        if(empty($options->ForwardAfterPurchaseUrl)){
            cg_ecommerce_free_checkout_error('The order summary page is not configured.');
        }
        if(!empty($options->SendOrderConfirmationMail) && (empty($options->OCMailOrderSummaryURL) || strpos($options->OrderConfirmationMail,'$order$')===false)){
            cg_ecommerce_free_checkout_error('The order confirmation email is not configured.');
        }
        $data = isset($_POST['orderData']) && is_array($_POST['orderData']) ? wp_unslash($_POST['orderData']) : array();
        $binding = cg_ecommerce_free_checkout_binding();
        if(!$binding){cg_ecommerce_free_checkout_error('Please enable cookies and reload the checkout.',403);}
        $phase = isset($_POST['phase']) && is_string($_POST['phase']) ? $_POST['phase'] : '';
        if($phase === 'prepare'){
            $cart = cg_ecommerce_free_checkout_cart($data,$options);
            $token = wp_generate_password(48,false,false);
            $key = 'cg_free_checkout_'.hash('sha256',$token);
            $context = array('key'=>$key,'binding'=>$binding,'cart_hash'=>$cart['cart_hash'],'is_test'=>$cart['is_test'],'expires'=>time()+HOUR_IN_SECONDS,'state'=>'ready');
            if(!set_transient($key,$context,DAY_IN_SECONDS)){cg_ecommerce_free_checkout_error('Checkout could not be started.',500);}
            wp_send_json_success(array('token'=>$token,'email'=>is_user_logged_in() ? wp_get_current_user()->user_email : ''));
        }
        $token = isset($_POST['checkoutToken']) && is_string($_POST['checkoutToken']) ? $_POST['checkoutToken'] : '';
        if($phase!=='complete' || !preg_match('/^[a-zA-Z0-9]{48}$/D',$token)){cg_ecommerce_free_checkout_error('Please reopen the checkout.');}
        $key = 'cg_free_checkout_'.hash('sha256',$token);
        $lock = 'cg_free_'.substr(hash('sha256',$wpdb->prefix.'|'.$key),0,48);
        if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,5)',$lock))!=='1'){
            cg_ecommerce_free_checkout_error('Your order is being processed. Please wait.');
        }
        register_shutdown_function('cg_ecommerce_release_payment_transaction_lock',$lock);
        $context = get_transient($key);
        if(!is_array($context) || !cg_hash_equals($context['binding'],$binding)){
            cg_ecommerce_free_checkout_error('Please reopen the checkout.',403);
        }
        if($context['state']==='complete'){
            wp_send_json_success(cg_ecommerce_free_checkout_result($context['order_hash']));
        }
        if($context['state']!=='ready'){
            cg_ecommerce_free_checkout_error('This order is already being processed. Please check your order confirmation before trying again.');
        }
        if($context['expires'] < time()){cg_ecommerce_free_checkout_error('Please reopen the checkout.');}
        $cart = cg_ecommerce_free_checkout_cart($data,$options);
        if(!cg_hash_equals($context['cart_hash'],$cart['cart_hash']) || $context['is_test']!==$cart['is_test']){
            cg_ecommerce_free_checkout_error('The cart has changed. Please reopen the checkout.');
        }
        $email = isset($data['freeEmail']) && is_string($data['freeEmail']) ? trim($data['freeEmail']) : '';
        if(!is_email($email)){cg_ecommerce_free_checkout_error('Enter a valid email address.',400);}
        for($i=1;$i<=3;$i++){
            $property = array(1=>'CheckoutAgreementOne',2=>'CheckoutAgreementTwo',3=>'CheckoutAgreementThree');
            if(!empty($options->{$property[$i]}) && empty($data['freeAgreements'][$i])){
                cg_ecommerce_free_checkout_error('Please accept the checkout agreements.',400);
            }
        }
        if(cg_ecommerce_check_item_has_shipping($cart)){
            foreach(array('FirstName','LastName','Line1','City','PostalCode','CountryShort') as $field){
                $name = 'cgShippingAddress'.$field;
                if(empty($data[$name]) || !is_string($data[$name]) || trim($data[$name])===''){
                    cg_ecommerce_free_checkout_error('Complete the shipping address.',400);
                }
            }
            $countries = cg_get_countries();
            $country = $data['cgShippingAddressCountryShort'];
            $allowed = !empty($options->AllowedCountries) ? maybe_unserialize($options->AllowedCountries) : array();
            if(!isset($countries[$country]) || (is_array($allowed) && count($allowed) && !in_array($country,$allowed,true))){
                cg_ecommerce_free_checkout_error('Select an allowed shipping country.',400);
            }
            $states = file_exists(__DIR__.'/country_codes_states_'.strtolower($country).'.csv') ? cg_get_country_states_codes($country) : array();
            if(!empty($states) && (empty($data['cgShippingAddressStateShort']) || !isset($states[$data['cgShippingAddressStateShort']]))){
                cg_ecommerce_free_checkout_error('Select a shipping state.',400);
            }
        }
        $data['usedGid'] = reset($cart['gallery_ids']);
        $context['state'] = 'processing';
        $context['lock_name'] = $lock;
        if(!set_transient($key,$context,DAY_IN_SECONDS)){cg_ecommerce_free_checkout_error('The order could not be started.',500);}
        $GLOBALS['cgFreeCheckoutContext'] = $context;
        $GLOBALS['cgFreeCheckoutContext']['email'] = sanitize_email($email);
        // The established finalizer sanitizes and rebuilds the cart again. No client flag can activate this branch.
        $_POST['orderData'] = wp_slash($data);
        include(__DIR__.'/../../../v10/v10-frontend/ecommerce/ecommerce-payment-processing.php');
        exit;
    }
}
add_action('wp_ajax_post_cg_ecommerce_free_checkout','post_cg_ecommerce_free_checkout');
add_action('wp_ajax_nopriv_post_cg_ecommerce_free_checkout','post_cg_ecommerce_free_checkout');
