<?php

global $wpdb;

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_ecommerce_orders = $wpdb->prefix . "contest_gal1ery_ecommerce_orders";
$tablename_ecommerce_orders_items = $wpdb->prefix . "contest_gal1ery_ecommerce_orders_items";
$tablename_contest_gal1ery_options = $wpdb->prefix . "contest_gal1ery_options";
$tablename_contest_gal1ery_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";
$userFormShort = $tablename_contest_gal1ery_create_user_form;
$tablename_contest_gal1ery_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
$tablename_contest_gal1ery_google_users = $wpdb->prefix."contest_gal1ery_google_users";
$table_usermeta = $wpdb->base_prefix . "usermeta";
$entriesShort = $tablename_contest_gal1ery_create_user_entries;
$wpUsers = $wpdb->base_prefix . "users";

$_POST = cg1l_sanitize_post($_POST);

// will be inserted if create csv
echo "<div  id='cg_create_sale_orders_csv_container'>";
echo "<input type='hidden' name='cg_create_sale_orders_csv' id='cg_create_sale_orders_csv' value='true' />";
echo "</div>";

    $start = 0; // Startwert setzen (0 = 1. Zeile)
    $step = 50;

    if (isset($_GET["start"])) {
        $muster = "/^[0-9]+$/";
        if (preg_match($muster, $_GET["start"]) == 0) {
            $start = 0;
        } else {
            $start = $_GET["start"];
        }
    }

    if (isset($_GET["step"])) {
        $muster = "/^[0-9]+$/"; // reg. Ausdruck für Zahlen
        if (preg_match($muster, $_GET["start"]) == 0) {
            $step = 50; // Bei Manipulation Rückfall auf 0
        } else {
            $step = $_GET["step"];
        }
    }

$cgSearchGalleryId = '';
$cgSearchGalleryIdParam = '';

if(!empty($_POST['cg-search-gallery-id']) OR !empty($_GET['cg-search-gallery-id'])){
    $cgSearchGalleryId = (!empty($_POST['cg-search-gallery-id'])) ? intval($_POST['cg-search-gallery-id']) : intval($_GET['cg-search-gallery-id']);
    $cgSearchGalleryIdParam = '&cg-search-gallery-id='.$cgSearchGalleryId;
}

$cgUserName = '';
$cgUserNameGetParam = '';

$toSelect = "$wpUsers.ID, $wpUsers.user_login, $wpUsers.user_email";

if(!empty($_GET['cg_item_ids'])){
    $_GET['cg_item_ids'] = absint($_GET['cg_item_ids']);
    $_POST['cg_item_ids'] = $_GET['cg_item_ids'];
}

$return = cg_ecommerce_get_orders($start,$step);
$saleOrders = $return['saleOrders'];
$rows = $return['rows'];

$versionColor = "#444";

echo "<div id='cgRegistrationSearch'>";/*
echo"&nbsp;&nbsp;Show users per Site:";
*/

echo "<form id='cgSaleOrdersForm' method='POST' action='?page=".cg_get_version()."/index.php&cg_orders=true&option_id=$GalleryID#cg-search-results-container' class='cg_load_backend_submit'>";

echo "<input type='hidden' disabled name='cg_ecommerce_export_orders' id='cg_ecommerce_export_orders' value='true' />";

echo "<input type='hidden' name='cg_start'  value='$start' />";
echo "<input type='hidden' name='cg_step'  value='$step' />";

echo '<input  type="text" placeholder="Order number"  name="cg_order_number" value="'.(isset($_POST['cg_order_number'])  ? $_POST['cg_order_number'] : '').'" />';

echo '<input  type="text" placeholder="PayPal/Stripe ID"  name="cg_paypal_transaction_id" value="'.(isset($_POST['cg_paypal_transaction_id'])  ? $_POST['cg_paypal_transaction_id'] : '').'" />';

echo '<input  type="text" placeholder="Entry IDs - example: 25 26"  name="cg_item_ids" value="'.(isset($_POST['cg_item_ids'])  ? $_POST['cg_item_ids'] : '').'" />';

echo '<input  type="text" placeholder="Gallery IDs - example: 1 2"  name="cg_gallery_ids" value="'.(isset($_POST['cg_gallery_ids'])  ? $_POST['cg_gallery_ids'] : '').'" />';

echo '<input  type="text" placeholder="Payer email"  name="cg_payer_email" value="'.(isset($_POST['cg_payer_email'])  ? $_POST['cg_payer_email'] : '').'" style="margin-right:16px;" />';

echo "<input type='hidden' name='cg_create_sale_orders_csv_new_export' id='cg_create_sale_orders_csv_new_export' value='true' />";
echo '<input type="hidden" name="cg-search-gallery-id-original" value="'.$cgSearchGalleryId.'" />';

$selected = '';

echo '<button type="submit" id="cgSaleOrderSearchSubmit" class="cg_backend_button_gallery_action">Search</button>';

echo '</form>';

echo '<div id="cgSaleOrderSearchReset" title="Reset search"></div>';

echo "</div>";

echo "<div id='cgCreateSaleOrdersDataCSVdiv' class='cg_export_data_div' >";
echo "<input type='button' class='cg_backend_button_gallery_action' id='cg_create_sale_orders_csv_submit' value='Export orders data' style='text-align:center;width:210px;margin: 1px auto;' />";
echo "<div style='padding-top:2px;position: relative;'><span class=\"cg-info-icon\" style='font-weight:bold;margin-bottom:10px;'>info</span>
    <span class=\"cg-info-container cg-info-container-gallery-user\" style=\"top: 25px; margin-left: 270px; display: none;\">CSV file will be exported separated by semicolon ( ; )<br></span>
    </div>";
echo "</div>";

$nr1 = $start + 1;
echo "<div class='cg_pics_per_site' style='margin-top:2px;width: 100%;'>";
for ($i = 0; $rows > $i; $i = $i + $step) {

    $anf = $i + 1;
    $end = $i + $step;

    if ($end > $rows) {
        $end = $rows;
    }


    if ($anf == $nr1 AND ($start+$step) > $rows AND $start==0) {
        continue;
        echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$anf-$end</a></div>";
    }

    elseif ($anf == $nr1 AND ($start+$step) > $rows AND $anf==$end) {

        echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$end</a></div>";
    }


    elseif ($anf == $nr1 AND ($start+$step) > $rows) {

        echo "<div class='cg_step cg_step_selected'><a href=\?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$anf-$end</a></div>";
    }

    elseif ($anf == $nr1) {
        echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$anf-$end</a></div>";
    }

    elseif ($anf == $end) {
        echo "<div class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$end</a></div>";
    }

    else {
        echo "<div class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&cg_orders=true\">$anf-$end</a></div>";
    }
}
echo "</div>";


if(!empty($_GET['cg_show_order']) AND !empty($_GET['cg_order_id'])){

    include("show-order.php");

}else{

    $saleOrdersTableHtmlStart = <<<HEREDOC

                <div id="cgSales" class="cg_table_list" >

                        <table class="wp-list-table widefat fixed striped users" style="box-shadow: unset;">
                            <thead>
                            <tr>
                                <th scope="col" id="username"
                                    class="manage-column column-username" style="max-width:170px;">Date</th>
                                <th scope="col" id="cgColumHeaderTotalValue" class="manage-column" style="width:90px;">Total value</th>
                                <th scope="col" id="cgColumnHeaderTransactionId" class="manage-column">Order number</th>
                                <th scope="col" id="cgColumnHeaderTransactionId" class="manage-column">PayPal Transaction ID/Stripe Payment Intent ID</th>
                                <th scope="col" id="cgColumHeaderItemIds" class="manage-column">Entry IDs</th>
                                <th scope="col" id="cgColumHeaderItemIds" class="manage-column">Gallery IDs</th>
                                <th scope="col" id="cgColumnHeaderPayerEmail" class="manage-column">Payer email</th>
                            </tr>
                            </thead>

                            <tbody id="the-list" data-wp-lists="list:user">
                            




HEREDOC;

    $usersTableHtmlEnd = <<<HEREDOC

                            </tbody>


                        </table>


                    <br class="clear">
                </div>


HEREDOC;

    $wp_roles = wp_roles();

    echo $saleOrdersTableHtmlStart;

    $currenciesArray = cg_get_ecommerce_currencies_array_formatted_by_short_key();

    $saleItemsIdsByParentOrderArray = [];
    $saleItemsGalleryIDsByParentOrderArray = [];
    $saleItemsCollectedParentOrders = '';

    foreach($saleOrders as $saleOrder){
        $ParentOrder = $saleOrder->id;
        if(!$saleItemsCollectedParentOrders){
            $saleItemsCollectedParentOrders .= "ParentOrder = $ParentOrder";
        }else{
            $saleItemsCollectedParentOrders .= " or ParentOrder = $ParentOrder";
        }
    }


    $saleItems = $wpdb->get_results("SELECT * FROM $tablename_ecommerce_orders_items WHERE ($saleItemsCollectedParentOrders)");

    foreach($saleItems as $saleItem){
        if(!isset($saleItemsIdsByParentOrderArray[$saleItem->ParentOrder])){
            $saleItemsIdsByParentOrderArray[$saleItem->ParentOrder] = [];
        }
        $saleItemsIdsByParentOrderArray[$saleItem->ParentOrder][] = $saleItem->pid;
        if(!isset($saleItemsGalleryIDsByParentOrderArray[$saleItem->ParentOrder])){
	        $saleItemsGalleryIDsByParentOrderArray[$saleItem->ParentOrder] = [];
        }
		if(in_array($saleItem->GalleryID,$saleItemsGalleryIDsByParentOrderArray[$saleItem->ParentOrder])===false){
			$saleItemsGalleryIDsByParentOrderArray[$saleItem->ParentOrder][] = $saleItem->GalleryID;
		}
    }

    if(!empty($saleOrders)){

        foreach($saleOrders as $saleOrder){

	        if(empty($saleItemsIdsByParentOrderArray[$saleOrder->id])){// can happen only on test systems, if it is missing
		        continue;
	        }

	        $LogForDatabase =  unserialize($saleOrder->LogForDatabase);
	        if(empty($LogForDatabase['purchase_units'][0]['amount']['value'])){// can happen only on test systems, if it is missing
		        continue;
	        }

	        $purchaseTime = cg_get_time_based_on_wp_timezone_conf($saleOrder->Tstamp,'Y-M-d H:i:s');
            $StripePiId =  $saleOrder->StripePiId;
            $PayPalTransactionId =  $saleOrder->PayPalTransactionId;
            $PayerEmail =  $saleOrder->PayerEmail;
            $ParentOrder =  $saleOrder->id;
            $OrderNumber =  $saleOrder->OrderNumber;
            $TotalValue = $LogForDatabase['purchase_units'][0]['amount']['value'];
            $PriceDivider = $LogForDatabase['PriceDivider'];
            $CurrencyShort = $LogForDatabase['CurrencyShort'];
            $CurrencyPosition = $LogForDatabase['CurrencyPosition'];

            $currencyChar = $currenciesArray[$CurrencyShort];

	        $totalValueStringToShow = cg_ecommerce_price_to_show($currenciesArray,$CurrencyShort,$CurrencyPosition,$PriceDivider,$TotalValue);

            $itemIds = implode(' ',$saleItemsIdsByParentOrderArray[$saleOrder->id]);
            $GalleryIDs = implode(' ',$saleItemsGalleryIDsByParentOrderArray[$saleOrder->id]);

			$environment = '<br>(live environment)';
			if($saleOrder->IsTest){
				$environment = '<br>(test environment)';
			}

			$TransactionIdText = 'PayPal:<br>'.$PayPalTransactionId;
			if(!empty($StripePiId)){
				$TransactionIdText = 'Stripe:<br>'.$StripePiId;
			}

            echo "<tr>";
                echo "<td style=\"max-width:170px;\">$purchaseTime$environment</td>";
                echo "<td>$totalValueStringToShow</td>";
                echo "<td>$OrderNumber</td>";
                echo "
                <td class='cg_td_button cg_flex_flow_column'><span class='cg_td_button_text'>$TransactionIdText</span>
                <div style='margin-top: 7px;margin-bottom: 5px;'><a class='cg_image_action_href' href='?page=".cg_get_version()."/index.php&cg_show_order=true&cg_order_id=$ParentOrder&option_id=$GalleryID' style='display: inline-block;margin-bottom:10px;' target='_blank'><span class='cg_image_action_span'>Show order</span></a><br>
                <a class='cg_image_action_href ' href='".get_site_url()."?cg_ecommerce_export_order=true&cg_order_id=$ParentOrder&option_id=$GalleryID' style='margin-top:10px;margin-bottom:10px;'><span class='cg_image_action_span' style='background-color: #f1f1f1;'>Export order</span></a></div>
                </td>";
                echo "<td>$itemIds</td>";
                echo "<td>$GalleryIDs</td>";
                echo "
                <td>$PayerEmail
                </td>";
            echo "</tr>";

        }
    }else{
	    echo '<tr style="background-color: white;"><td colspan="7"><p style="
    text-align: center;
    margin-top: 30px;
    font-size: 20px;
">No order could be found</p></td></tr>';
    }

    echo $usersTableHtmlEnd;

}


