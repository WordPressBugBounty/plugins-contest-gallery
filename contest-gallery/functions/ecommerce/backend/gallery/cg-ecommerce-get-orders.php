<?php
if(!function_exists('cg_ecommerce_get_orders')){
    function cg_ecommerce_get_orders($start,$step,$isFindAll = false){

		// because all found orders has always to be exported
		if($isFindAll){
			$start = 0;
			$step = 9000000;
		}

        global $wpdb;

        $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
        $tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

        if(!empty($_GET['cg_order_id'])){

            $saleOrders = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders WHERE id = '".absint($_GET['cg_order_id'])."'");

            $rows = $wpdb->get_var(
                "
		SELECT COUNT(*) AS NumberOfRows WHERE id = > 0 
		FROM $tablename_ecommerce_orders"
            );

        }else{

            if(!empty($_POST['cg_paypal_transaction_id']) || !empty($_POST['cg_item_ids'])
               || !empty($_POST['cg_payer_email']) || !empty($_POST['cg_order_number']) || !empty($_POST['cg_gallery_ids'])
            ){
                $PayPalTransactionIdWhereQuery = '';
                $PayerEmailWhereQuery = '';
                $OrderNumberWhereQuery = '';
                $ItemIdsWhereQuery = '';
                $GalleryIdsWhereQuery = '';
                $OpenBracket = '';
                $CloseBracket = '';
                if(!empty($_POST['cg_paypal_transaction_id'])){
                    $OpenBracket = '&& (';
                    $CloseBracket = ')';
                    $PayPalTransactionId = sanitize_text_field($_POST['cg_paypal_transaction_id']);
                    $PayPalTransactionIdWhereQuery = "$tablename_ecommerce_orders.PayPalTransactionId LIKE '%$PayPalTransactionId%'";
                }
                if(!empty($_POST['cg_payer_email'])){
                    $OpenBracket = '&& (';
                    $CloseBracket = ')';
                    $PayerEmail = sanitize_text_field($_POST['cg_payer_email']);
                    if(!empty($_POST['cg_paypal_transaction_id'])){
                        $PayerEmailWhereQuery .= "OR  ";
                    }
                    $PayerEmailWhereQuery .= "$tablename_ecommerce_orders.PayerEmail LIKE '%$PayerEmail%'";
                }
                if(!empty($_POST['cg_item_ids'])){
                    $OpenBracket = '&& (';
                    $CloseBracket = ')';
                    $cg_item_ids_query = '';
                    $cg_item_ids_exploded = explode(' ',sanitize_text_field($_POST['cg_item_ids']));
                    foreach ($cg_item_ids_exploded as $cg_item_id){
	                    $cg_item_id = absint($cg_item_id);
                        if(!empty($cg_item_id)){
                            if(!$cg_item_ids_query){
                                $cg_item_ids_query .= "$tablename_ecommerce_orders_items.pid = $cg_item_id";
                            }else{
                                $cg_item_ids_query .= " or $tablename_ecommerce_orders_items.pid = $cg_item_id";
                            }
                        }
                    }
                    if(!empty($_POST['cg_paypal_transaction_id']) || !empty($_POST['cg_payer_email'])){
                        $ItemIdsWhereQuery .= "OR ";
                    }
                    $ItemIdsWhereQuery .= "($cg_item_ids_query)";
                }
				if(!empty($_POST['cg_order_number'])){
		            $OpenBracket = '&& (';
		            $CloseBracket = ')';
		            $OrderNumber = sanitize_text_field($_POST['cg_order_number']);
					if(!empty($_POST['cg_paypal_transaction_id']) || !empty($_POST['cg_payer_email']) || !empty($_POST['cg_item_ids'])){
			            $OrderNumberWhereQuery .= "OR  ";
		            }
					$OrderNumberWhereQuery .= "$tablename_ecommerce_orders.OrderNumber LIKE '%$OrderNumber%'";
	            }
	            if(!empty($_POST['cg_gallery_ids'])){
		            $OpenBracket = '&& (';
		            $CloseBracket = ')';
		            $cg_gallery_ids_query = '';
		            $cg_gallery_ids_exploded = explode(' ',sanitize_text_field($_POST['cg_gallery_ids']));
		            foreach ($cg_gallery_ids_exploded as $cg_gallery_id){
			            $cg_gallery_id = absint($cg_gallery_id);
			            if(!empty($cg_gallery_id)){
				            if(!$cg_gallery_ids_query){
					            $cg_gallery_ids_query .= "$tablename_ecommerce_orders_items.GalleryID = $cg_gallery_id";
				            }else{
					            $cg_gallery_ids_query .= " or $tablename_ecommerce_orders_items.GalleryID = $cg_gallery_id";
				            }
			            }
		            }
		            if(!empty($_POST['cg_paypal_transaction_id']) || !empty($_POST['cg_payer_email']) || !empty($_POST['cg_item_ids']) || !empty($_POST['cg_order_number'])){
			            $GalleryIdsWhereQuery .= "OR ";
		            }
		            $GalleryIdsWhereQuery .= "($cg_gallery_ids_query)";
	            }

                $saleOrders = $wpdb->get_results("SELECT $tablename_ecommerce_orders.* FROM $tablename_ecommerce_orders, $tablename_ecommerce_orders_items WHERE ($tablename_ecommerce_orders.id = $tablename_ecommerce_orders_items.ParentOrder)  $OpenBracket $PayPalTransactionIdWhereQuery $PayerEmailWhereQuery  $ItemIdsWhereQuery $OrderNumberWhereQuery $GalleryIdsWhereQuery $CloseBracket GROUP BY $tablename_ecommerce_orders.id ORDER BY $tablename_ecommerce_orders.id DESC LIMIT $start, $step");

                $rows = $wpdb->get_var("SELECT COUNT(*) AS NumberOfRows FROM $tablename_ecommerce_orders, $tablename_ecommerce_orders_items WHERE ($tablename_ecommerce_orders.id = $tablename_ecommerce_orders_items.ParentOrder)  $OpenBracket $PayPalTransactionIdWhereQuery $PayerEmailWhereQuery  $ItemIdsWhereQuery $OrderNumberWhereQuery $GalleryIdsWhereQuery  $CloseBracket GROUP BY $tablename_ecommerce_orders.id ORDER BY $tablename_ecommerce_orders.id DESC");

            }else{

                $saleOrders = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders WHERE id > 0  ORDER BY id DESC LIMIT $start, $step");

                $rows = $wpdb->get_var(
                    "
    SELECT COUNT(*) AS NumberOfRows 
    FROM $tablename_ecommerce_orders WHERE id > 0"
                );
            }

        }

        return [
            'saleOrders' => $saleOrders,
            'rows' => $rows
        ];

   }
}

