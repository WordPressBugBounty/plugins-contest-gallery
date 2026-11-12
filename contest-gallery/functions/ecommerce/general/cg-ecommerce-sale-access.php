<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_products_pro_available')){
    function cg_ecommerce_products_pro_available(){
        return cg_get_version() === 'contest-gallery-pro' && contest_gal1ery_key_check();
    }
}
if(!function_exists('cg_ecommerce_clear_legacy_sale_test_mode')){
    function cg_ecommerce_clear_legacy_sale_test_mode($ecommerceEntryId){
        if($ecommerceEntryId > 0){delete_option('cg_ecommerce_test_only_'.absint($ecommerceEntryId));}
    }
}
if(!function_exists('cg_ecommerce_sale_requires_test')){
    function cg_ecommerce_sale_requires_test($ecommerceEntryId,$saleType){
        // The page selects Live/Sandbox. Product availability depends only on the edition.
        return in_array($saleType,array('download','service'),true) && !cg_ecommerce_products_pro_available();
    }
}
if(!function_exists('cg_ecommerce_test_only_message')){
    function cg_ecommerce_test_only_message(){
        return 'Downloads and services are available in test mode with FREE. Live purchases require PRO. Remove these products from the live cart or open the test page.';
    }
}

if(!function_exists('cg_ecommerce_product_price_is_valid')){
    function cg_ecommerce_product_price_is_valid($data){
        $data = (array)$data;
        $price = isset($data['Price']) ? (float)$data['Price'] : -1;
        return !empty($data['IsFree']) ? $price == 0 : $price > 0;
    }
}
if(!function_exists('cg_ecommerce_parse_sale_price')){
    function cg_ecommerce_parse_sale_price($input, $isFree){
        if($isFree){return 0;}
        if(!is_scalar($input)){return false;}
        $input = trim((string)$input);
        if(!preg_match('/^[0-9]+(?:[.,][0-9]{1,2})?$/D',$input)){return false;}
        $price = round((float)str_replace(',','.',$input),2);
        return is_finite($price) && $price > 0 ? $price : false;
    }
}
