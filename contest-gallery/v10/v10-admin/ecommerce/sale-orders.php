<?php

$GalleryID = absint($_GET['option_id']);
$cgSearchGalleryId = $GalleryID;
$transactionId = '';
if(!empty($_POST['transactionId'])){
    $transactionId = sanitize_text_field($_POST['transactionId']);
}

global $wpdb;

$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablenameOptions = $wpdb->prefix . "contest_gal1ery_options";

$options = $wpdb->get_var("SELECT * FROM $tablenameOptions WHERE id = '$GalleryID'");
$sales = $wpdb->get_var("SELECT * FROM $tablename_ecommerce_orders WHERE GalleryID = '$GalleryID'");

include(dirname(__FILE__) . "/../nav-menu.php");

    include("show-orders.php");

