<?php
if(!function_exists('cg_ecommerce_get_orders_prepare_query')){
    function cg_ecommerce_get_orders_prepare_query($wpdb, $query, $args = array()){
        if(empty($args)){
            return $query;
        }

        return call_user_func_array(array($wpdb, 'prepare'), array_merge(array($query), $args));
    }
}

if(!function_exists('cg_ecommerce_get_orders_get_post_text')){
    function cg_ecommerce_get_orders_get_post_text($key){
        if(!isset($_POST[$key]) || is_array($_POST[$key])){
            return '';
        }

        return trim(sanitize_text_field(wp_unslash($_POST[$key])));
    }
}

if(!function_exists('cg_ecommerce_get_orders_get_post_ids')){
    function cg_ecommerce_get_orders_get_post_ids($key){
        $ids = array();

        if(!isset($_POST[$key]) || is_array($_POST[$key])){
            return $ids;
        }

        $searchValue = trim(sanitize_text_field(wp_unslash($_POST[$key])));

        if($searchValue === ''){
            return $ids;
        }

        $searchValuesExploded = preg_split('/[^0-9]+/', $searchValue);

        if(empty($searchValuesExploded)){
            return $ids;
        }

        foreach($searchValuesExploded as $searchValueExploded){
            $searchValueExploded = absint($searchValueExploded);

            if(!empty($searchValueExploded)){
                $ids[$searchValueExploded] = $searchValueExploded;
            }
        }

        return array_values($ids);
    }
}

if(!function_exists('cg_ecommerce_get_orders')){
    function cg_ecommerce_get_orders($start,$step,$isFindAll = false){

		// because all found orders has always to be exported
		if($isFindAll){
			$start = 0;
			$step = 9000000;
		}

        $start = absint($start);
        $step = absint($step);

        if(empty($step)){
            $step = 50;
        }

        global $wpdb;

        $tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
        $tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";

        if(!empty($_GET['cg_order_id'])){

            $saleOrders = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $tablename_ecommerce_orders WHERE id = %d",
                    absint($_GET['cg_order_id'])
                )
            );

            $rows = $wpdb->get_var(
                "
		SELECT COUNT(*) AS NumberOfRows
		FROM $tablename_ecommerce_orders
     WHERE id >= 0 
		"
            );

        }else{

            $PayPalTransactionId = cg_ecommerce_get_orders_get_post_text('cg_paypal_transaction_id');
            $PayerEmail = cg_ecommerce_get_orders_get_post_text('cg_payer_email');
            $OrderNumber = cg_ecommerce_get_orders_get_post_text('cg_order_number');
            $ItemIdsSearchValue = cg_ecommerce_get_orders_get_post_text('cg_item_ids');
            $GalleryIdsSearchValue = cg_ecommerce_get_orders_get_post_text('cg_gallery_ids');
            $ItemIds = cg_ecommerce_get_orders_get_post_ids('cg_item_ids');
            $GalleryIds = cg_ecommerce_get_orders_get_post_ids('cg_gallery_ids');

            if($PayPalTransactionId !== '' || $ItemIdsSearchValue !== ''
               || $PayerEmail !== '' || $OrderNumber !== '' || $GalleryIdsSearchValue !== ''
            ){
                $WhereClauses = array();
                $WhereValues = array();
                $JoinOrdersItems = false;

                if($PayPalTransactionId !== ''){
                    $PayPalTransactionIdLike = '%' . $wpdb->esc_like($PayPalTransactionId) . '%';
                    $WhereClauses[] = "($tablename_ecommerce_orders.PayPalTransactionId LIKE %s OR $tablename_ecommerce_orders.StripePiId LIKE %s)";
                    $WhereValues[] = $PayPalTransactionIdLike;
                    $WhereValues[] = $PayPalTransactionIdLike;
                }

                if($PayerEmail !== ''){
                    $PayerEmailLike = '%' . $wpdb->esc_like($PayerEmail) . '%';
                    $WhereClauses[] = "$tablename_ecommerce_orders.PayerEmail LIKE %s";
                    $WhereValues[] = $PayerEmailLike;
                }

                if($ItemIdsSearchValue !== ''){
                    if(!empty($ItemIds)){
                        $JoinOrdersItems = true;
                        $Placeholders = implode(',', array_fill(0, count($ItemIds), '%d'));
                        $WhereClauses[] = "$tablename_ecommerce_orders_items.pid IN ($Placeholders)";

                        foreach($ItemIds as $ItemId){
                            $WhereValues[] = $ItemId;
                        }
                    }else{
                        $WhereClauses[] = '1 = 0';
                    }
                }

				if($OrderNumber !== ''){
                    $OrderNumberLike = '%' . $wpdb->esc_like($OrderNumber) . '%';
					$WhereClauses[] = "$tablename_ecommerce_orders.OrderNumber LIKE %s";
                    $WhereValues[] = $OrderNumberLike;
	            }

	            if($GalleryIdsSearchValue !== ''){
                    if(!empty($GalleryIds)){
                        $JoinOrdersItems = true;
                        $Placeholders = implode(',', array_fill(0, count($GalleryIds), '%d'));
                        $WhereClauses[] = "$tablename_ecommerce_orders_items.GalleryID IN ($Placeholders)";

                        foreach($GalleryIds as $GalleryId){
                            $WhereValues[] = $GalleryId;
                        }
                    }else{
                        $WhereClauses[] = '1 = 0';
                    }
	            }

                $OrdersFromQuery = $tablename_ecommerce_orders;
                if($JoinOrdersItems){
                    $OrdersFromQuery .= " INNER JOIN $tablename_ecommerce_orders_items ON ($tablename_ecommerce_orders.id = $tablename_ecommerce_orders_items.ParentOrder)";
                }

                $OrdersWhereQuery = '';
                if(!empty($WhereClauses)){
                    $OrdersWhereQuery = ' WHERE (' . implode(' OR ', $WhereClauses) . ')';
                }

                $saleOrdersQuery = "SELECT DISTINCT $tablename_ecommerce_orders.* FROM $OrdersFromQuery $OrdersWhereQuery ORDER BY $tablename_ecommerce_orders.id DESC LIMIT %d, %d";
                $saleOrdersValues = array_merge($WhereValues, array($start, $step));
                $saleOrders = $wpdb->get_results(cg_ecommerce_get_orders_prepare_query($wpdb, $saleOrdersQuery, $saleOrdersValues));

                $rowsQuery = "SELECT COUNT(DISTINCT $tablename_ecommerce_orders.id) AS NumberOfRows FROM $OrdersFromQuery $OrdersWhereQuery";
                $rows = $wpdb->get_var(cg_ecommerce_get_orders_prepare_query($wpdb, $rowsQuery, $WhereValues));

            }else{

                $saleOrders = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $tablename_ecommerce_orders WHERE id > 0 ORDER BY id DESC LIMIT %d, %d",
                        $start,
                        $step
                    )
                );

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
