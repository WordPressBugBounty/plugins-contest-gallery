<?php

global $wpdb;

$table_posts = $wpdb->prefix."posts";
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablenameEcommerceOptions = $wpdb->prefix . "contest_gal1ery_ecommerce_options";
$tablename_ecommerce_sale_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

// please provide sales id should be visible if not provided
// ales id sanitize $_GET und dann verarbeiten

$sale_id = absint($_POST['cg_order_id']);

//var_dump('$sale_id');
//var_dump($sale_id);

$SaleOrder = $wpdb->get_row("SELECT * FROM $tablename_ecommerce_orders WHERE id = '$sale_id' LIMIT 1");
//var_dump('$SaleOrder');
//var_dump($SaleOrder);
$ecommerceOptions = $wpdb->get_row("SELECT * FROM $tablenameEcommerceOptions WHERE GeneralID = '1'");

$PayPalTransactionId = $SaleOrder->PayPalTransactionId;

//var_dump('$SaleOrder->IsTest');
//var_dump($SaleOrder->IsTest);
//die;
if($SaleOrder->IsTest){
    $accessToken = cg_paypal_get_access_token($ecommerceOptions->PayPalSandboxClientId,$ecommerceOptions->PayPalSandboxSecret,true);
}else{
    $accessToken = cg_paypal_get_access_token($ecommerceOptions->PayPalLiveClientId,$ecommerceOptions->PayPalLiveSecret);
}

$order = cg_get_paypal_order($accessToken,$SaleOrder->PayPalTransactionId,$SaleOrder->IsTest);

?>
<script data-cg-processing="true">
    cgJsClass.gallery.vars.ecommerce.payPalResponse = <?php echo json_encode($order) ?>;
</script>
