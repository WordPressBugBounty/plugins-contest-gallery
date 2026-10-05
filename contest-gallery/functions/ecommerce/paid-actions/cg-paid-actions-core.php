<?php

if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/cg-paid-actions-recovery.php';

if(!function_exists('cg_paid_actions_table')){
    function cg_paid_actions_table($name){
        global $wpdb;

        $tables = array(
            'settings' => $wpdb->prefix.'contest_gal1ery_paid_action_settings',
            'entitlements' => $wpdb->prefix.'contest_gal1ery_paid_action_entitlements',
            'ledger' => $wpdb->prefix.'contest_gal1ery_paid_action_ledger',
            'orders' => $wpdb->prefix.'contest_gal1ery_ecommerce_orders',
            'order_items' => $wpdb->prefix.'contest_gal1ery_ecommerce_orders_items',
            'options' => $wpdb->prefix.'contest_gal1ery_options',
            'pro_options' => $wpdb->prefix.'contest_gal1ery_pro_options',
            'ecommerce_options' => $wpdb->prefix.'contest_gal1ery_ecommerce_options'
        );

        return isset($tables[$name]) ? $tables[$name] : '';
    }
}

if(!function_exists('cg_paid_actions_types')){
    function cg_paid_actions_types(){
        return array('upload','vote','comment');
    }
}

if(!function_exists('cg_paid_actions_minimum_gallery_version')){
    function cg_paid_actions_minimum_gallery_version(){
        return 22;
    }
}

if(!function_exists('cg_paid_actions_get_gallery_version')){
    function cg_paid_actions_get_gallery_version($galleryId){
        global $wpdb;
        static $versions = array();

        $galleryId = absint($galleryId);
        if(!$galleryId){return 0;}
        if(isset($versions[$galleryId])){return $versions[$galleryId];}

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT Version, VersionDecimal FROM ".cg_paid_actions_table('options')." WHERE id = %d LIMIT 1",
            $galleryId
        ));
        if(empty($row)){$versions[$galleryId] = 0;return 0;}

        $version = isset($row->Version) ? (float)$row->Version : 0;
        if($version<=0 && isset($row->VersionDecimal)){$version = (float)$row->VersionDecimal;}
        $versions[$galleryId] = $version;
        return $versions[$galleryId];
    }
}

if(!function_exists('cg_paid_actions_gallery_is_supported')){
    function cg_paid_actions_gallery_is_supported($galleryId){
        return cg_paid_actions_get_gallery_version($galleryId)>=cg_paid_actions_minimum_gallery_version();
    }
}

if(!function_exists('cg_paid_actions_normalize_copied_values')){
    function cg_paid_actions_normalize_copied_values($values){
        if(!is_array($values)){return $values;}

        $wasPaidAction = !empty($values['IsPaidAction']) || !empty($values['IsPaidActionPreview']);
        $defaults = array(
            'IsPaidAction'=>0,
            'IsPaidActionPreview'=>0,
            'PaidActionSource'=>'',
            'PaidActionPriceUnitGross'=>0,
            'PaidActionCurrencyShort'=>'',
            'PaidActionCurrencyPosition'=>'',
            'PaidActionPriceDivider'=>'',
            'PaidActionParentOrder'=>0,
            'PaidActionOrderItem'=>0
        );
        foreach($defaults as $key=>$value){
            if(array_key_exists($key,$values)){$values[$key] = $value;}
        }
        if($wasPaidAction && array_key_exists('OrderItem',$values)){$values['OrderItem'] = 0;}
        return $values;
    }
}

if(!function_exists('cg_paid_actions_normalize_type')){
    function cg_paid_actions_normalize_type($actionType){
        $actionType = sanitize_key((string)$actionType);
        return in_array($actionType,cg_paid_actions_types(),true) ? $actionType : '';
    }
}

if(!function_exists('cg_paid_actions_type_label')){
    function cg_paid_actions_type_label($actionType,$plural = true){
        $labels = array(
            'upload' => $plural ? 'Uploads' : 'Upload',
            'vote' => $plural ? 'Votes' : 'Vote',
            'comment' => $plural ? 'Comments' : 'Comment'
        );
        return isset($labels[$actionType]) ? $labels[$actionType] : '';
    }
}

if(!function_exists('cg_paid_actions_now')){
    function cg_paid_actions_now(){
        return current_time('mysql');
    }
}

if(!function_exists('cg_paid_actions_request_preview_token')){
    function cg_paid_actions_request_preview_token(){
        $requestKeys = array('cg_paid_actions_preview','cg_paid_actions_sandbox');
        foreach($requestKeys as $requestKey){
            if(!empty($_REQUEST[$requestKey]) && is_scalar($_REQUEST[$requestKey])){
                return sanitize_text_field(wp_unslash((string)$_REQUEST[$requestKey]));
            }
        }
        return '';
    }
}

if(!function_exists('cg_paid_actions_preview_token_hash')){
    function cg_paid_actions_preview_token_hash($token){
        return hash_hmac('sha256',(string)$token,wp_salt('auth'));
    }
}

if(!function_exists('cg_paid_actions_preview_entitlement_key')){
    function cg_paid_actions_preview_entitlement_key($preview,$actionType){
        $token = !empty($preview['token']) ? (string)$preview['token'] : '';
        $actionType = cg_paid_actions_normalize_type($actionType);
        if($token==='' || $actionType===''){
            return '';
        }
        $scopeType = !empty($preview['shared']) ? 'shared' : 'local';
        $originGalleryId = !empty($preview['originGalleryId']) ? absint($preview['originGalleryId']) : 0;
        return cg_paid_actions_preview_entitlement_prefix($preview,$actionType).$scopeType.':'.$originGalleryId;
    }
}

if(!function_exists('cg_paid_actions_preview_entitlement_prefix')){
    function cg_paid_actions_preview_entitlement_prefix($preview,$actionType){
        $token = !empty($preview['token']) ? (string)$preview['token'] : '';
        $actionType = cg_paid_actions_normalize_type($actionType);
        if($token==='' || $actionType===''){
            return '';
        }
        return 'preview-credit:'.cg_paid_actions_preview_token_hash($token).':'.$actionType.':';
    }
}

if(!function_exists('cg_paid_actions_preview_legacy_entitlement_key')){
    function cg_paid_actions_preview_legacy_entitlement_key($preview,$actionType){
        $prefix = cg_paid_actions_preview_entitlement_prefix($preview,$actionType);
        return $prefix!=='' ? rtrim($prefix,':') : '';
    }
}

if(!function_exists('cg_paid_actions_preview_gallery_lock_name')){
    function cg_paid_actions_preview_gallery_lock_name($galleryId){
        return 'cgpa_preview_gallery_'.md5((string)absint($galleryId));
    }
}

if(!function_exists('cg_paid_actions_preview_gallery_locks')){
    function &cg_paid_actions_preview_gallery_locks(){
        static $locks = array();
        return $locks;
    }
}

if(!function_exists('cg_paid_actions_lock_preview_gallery')){
    function cg_paid_actions_lock_preview_gallery($galleryId,$timeout = 5){
        global $wpdb;
        $galleryId = absint($galleryId);
        if(!$galleryId){return false;}
        $locks =& cg_paid_actions_preview_gallery_locks();
        if(!empty($locks[$galleryId])){
            $locks[$galleryId]++;
            return true;
        }
        $locked = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,%d)',cg_paid_actions_preview_gallery_lock_name($galleryId),absint($timeout)));
        if((string)$locked!=='1'){return false;}
        $locks[$galleryId] = 1;
        return true;
    }
}

if(!function_exists('cg_paid_actions_unlock_preview_gallery')){
    function cg_paid_actions_unlock_preview_gallery($galleryId){
        global $wpdb;
        $galleryId = absint($galleryId);
        $locks =& cg_paid_actions_preview_gallery_locks();
        if(empty($locks[$galleryId])){return;}
        $locks[$galleryId]--;
        if($locks[$galleryId]<=0){
            unset($locks[$galleryId]);
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',cg_paid_actions_preview_gallery_lock_name($galleryId)));
        }
    }
}

if(!function_exists('cg_paid_actions_reservation_flags')){
    function cg_paid_actions_reservation_flags($reservation){
        return array(
            'isPaidAction'=>is_array($reservation) ? 1 : 0,
            'isPaidActionPreview'=>(is_array($reservation) && !empty($reservation['isPreview'])) ? 1 : 0
        );
    }
}

if(!function_exists('cg_paid_actions_is_current_request_preview')){
    function cg_paid_actions_is_current_request_preview($galleryId = 0){
        if(!empty($GLOBALS['cgPaidActionPreviewContext']) && is_array($GLOBALS['cgPaidActionPreviewContext'])){
            if(!empty($GLOBALS['cgPaidActionPreviewContext']['checkoutMode']) && $GLOBALS['cgPaidActionPreviewContext']['checkoutMode']==='sandbox'){
                return false;
            }
            if(!$galleryId || absint($GLOBALS['cgPaidActionPreviewContext']['targetGalleryId'])===absint($galleryId)){
                return true;
            }
        }
        return false;
    }
}

if(!function_exists('cg_paid_actions_is_pro_available')){
    function cg_paid_actions_is_pro_available(){
        return (
            function_exists('cg_get_version') &&
            cg_get_version()==='contest-gallery-pro' &&
            function_exists('contest_gal1ery_key_check') &&
            contest_gal1ery_key_check()
        );
    }
}

if(!function_exists('cg_paid_actions_action_requires_pro')){
    function cg_paid_actions_action_requires_pro($actionType){
        return cg_paid_actions_normalize_type($actionType)!=='';
    }
}

if(!function_exists('cg_paid_actions_action_license_available')){
    function cg_paid_actions_action_license_available($actionType,$readiness = array()){
        $actionType = cg_paid_actions_normalize_type($actionType);
        if($actionType===''){return false;}
        if(!cg_paid_actions_action_requires_pro($actionType)){return true;}
        if(isset($readiness['proAvailable'])){return !empty($readiness['proAvailable']);}
        return cg_paid_actions_is_pro_available();
    }
}

if(!function_exists('cg_paid_actions_action_table')){
    function cg_paid_actions_action_table($actionType){
        global $wpdb;

        $tables = array(
            'upload'=>$wpdb->prefix.'contest_gal1ery',
            'vote'=>$wpdb->prefix.'contest_gal1ery_ip',
            'comment'=>$wpdb->prefix.'contest_gal1ery_comments'
        );
        $actionType = cg_paid_actions_normalize_type($actionType);
        return isset($tables[$actionType]) ? $tables[$actionType] : '';
    }
}

if(!function_exists('cg_paid_actions_default_settings')){
    function cg_paid_actions_default_settings($galleryId){
        return (object)array(
            'id' => 0,
            'GalleryID' => absint($galleryId),
            'UploadMode' => 0,
            'VoteMode' => 0,
            'CommentMode' => 0,
            'UploadPrice' => '1.00',
            'VotePrice' => '1.00',
            'CommentPrice' => '1.00',
            'ShareCredits' => 0,
            'UpdatedBy' => 0,
            'UpdatedAt' => '0000-00-00 00:00:00'
        );
    }
}

if(!function_exists('cg_paid_actions_get_settings')){
    function cg_paid_actions_get_settings($galleryId){
        global $wpdb;

        $galleryId = absint($galleryId);
        if(!$galleryId){
            return cg_paid_actions_default_settings(0);
        }

        $table = cg_paid_actions_table('settings');
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE GalleryID = %d LIMIT 1",$galleryId));
        return !empty($row) ? $row : cg_paid_actions_default_settings($galleryId);
    }
}

if(!function_exists('cg_paid_actions_get_mode')){
    function cg_paid_actions_get_mode($settings,$actionType){
        $actionType = cg_paid_actions_normalize_type($actionType);
        $property = ucfirst($actionType).'Mode';
        return ($actionType!=='' && isset($settings->$property)) ? absint($settings->$property) : 0;
    }
}

if(!function_exists('cg_paid_actions_get_price')){
    function cg_paid_actions_get_price($settings,$actionType){
        $actionType = cg_paid_actions_normalize_type($actionType);
        $property = ucfirst($actionType).'Price';
        return ($actionType!=='' && isset($settings->$property)) ? round((float)$settings->$property,2) : 0;
    }
}

if(!function_exists('cg_paid_actions_sanitize_price')){
    function cg_paid_actions_sanitize_price($price){
        $price = str_replace(',','.',trim((string)$price));
        if(!is_numeric($price)){
            return false;
        }
        $price = round((float)$price,2);
        return $price > 0 ? number_format($price,2,'.','') : false;
    }
}

if(!function_exists('cg_paid_actions_price_divider_from_order_log')){
    function cg_paid_actions_price_divider_from_order_log($rawLog){
        $log = maybe_unserialize($rawLog);
        if(is_array($log) && !empty($log['PriceDivider']) && in_array($log['PriceDivider'],array('.',','),true)){
            return $log['PriceDivider'];
        }
        return '.';
    }
}

if(!function_exists('cg_paid_actions_purchase_snapshot')){
    function cg_paid_actions_purchase_snapshot($parentOrder,$orderItem){
        global $wpdb;

        static $cache = array();
        $parentOrder = absint($parentOrder);
        $orderItem = absint($orderItem);
        $key = $parentOrder.'|'.$orderItem;
        if(isset($cache[$key])){return $cache[$key];}

        $snapshot = array(
            'source'=>'purchase','priceUnitGross'=>'0.00','currencyShort'=>'','currencyPosition'=>'',
            'priceDivider'=>'.','parentOrder'=>$parentOrder,'orderItem'=>$orderItem,
            'paymentStatus'=>'','orderNumber'=>'','orderGalleryId'=>0
        );
        if(!$parentOrder || !$orderItem){
            $cache[$key] = $snapshot;
            return $snapshot;
        }

        $orders = cg_paid_actions_table('orders');
        $items = cg_paid_actions_table('order_items');
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT item.PriceUnitGross, item.GalleryID AS ItemGalleryID,
                    sale.CurrencyShort, sale.CurrencyPosition, sale.PaymentStatus, sale.OrderNumber,
                    sale.GalleryID AS OrderGalleryID, sale.LogForDatabase
             FROM $items item
             INNER JOIN $orders sale ON sale.id = item.ParentOrder
             WHERE item.id = %d AND item.ParentOrder = %d LIMIT 1",
            $orderItem,$parentOrder
        ));
        if(!empty($row)){
            $snapshot['priceUnitGross'] = number_format((float)$row->PriceUnitGross,2,'.','');
            $snapshot['currencyShort'] = sanitize_text_field($row->CurrencyShort);
            $snapshot['currencyPosition'] = sanitize_key($row->CurrencyPosition)==='right' ? 'right' : 'left';
            $snapshot['priceDivider'] = cg_paid_actions_price_divider_from_order_log($row->LogForDatabase);
            $snapshot['paymentStatus'] = sanitize_text_field($row->PaymentStatus);
            $snapshot['orderNumber'] = sanitize_text_field($row->OrderNumber);
            $snapshot['orderGalleryId'] = absint($row->OrderGalleryID);
        }
        $cache[$key] = $snapshot;
        return $snapshot;
    }
}

if(!function_exists('cg_paid_actions_snapshot_from_allocation')){
    function cg_paid_actions_snapshot_from_allocation($allocation,$isPreview = false){
        $grantType = !empty($allocation['grantType']) ? sanitize_key($allocation['grantType']) : '';
        if($isPreview){
            return array(
                'source'=>'preview','priceUnitGross'=>'0.00','currencyShort'=>'','currencyPosition'=>'',
                'priceDivider'=>'','parentOrder'=>0,'orderItem'=>0
            );
        }
        if($grantType==='admin' || empty($allocation['orderItem'])){
            return array(
                'source'=>'admin','priceUnitGross'=>'0.00','currencyShort'=>'','currencyPosition'=>'',
                'priceDivider'=>'','parentOrder'=>0,'orderItem'=>0
            );
        }
        $snapshot = cg_paid_actions_purchase_snapshot($allocation['parentOrder'],$allocation['orderItem']);
        if($grantType==='sandbox_purchase'){$snapshot['source'] = 'sandbox_purchase';}
        return $snapshot;
    }
}

if(!function_exists('cg_paid_actions_update_object_snapshot')){
    function cg_paid_actions_update_object_snapshot($galleryId,$actionType,$objectId,$allocation,$isPreview = false){
        global $wpdb;

        $galleryId = absint($galleryId);
        $objectId = absint($objectId);
        $table = cg_paid_actions_action_table($actionType);
        if(!$galleryId || !$objectId || $table===''){return true;}

        $snapshot = cg_paid_actions_snapshot_from_allocation($allocation,$isPreview);
        $updated = $wpdb->update(
            $table,
            array(
                'PaidActionSource'=>$snapshot['source'],
                'PaidActionPriceUnitGross'=>$snapshot['priceUnitGross'],
                'PaidActionCurrencyShort'=>$snapshot['currencyShort'],
                'PaidActionCurrencyPosition'=>$snapshot['currencyPosition'],
                'PaidActionPriceDivider'=>$snapshot['priceDivider'],
                'PaidActionParentOrder'=>$snapshot['parentOrder'],
                'PaidActionOrderItem'=>$snapshot['orderItem']
            ),
            array('id'=>$objectId,'GalleryID'=>$galleryId),
            array('%s','%f','%s','%s','%s','%d','%d'),
            array('%d','%d')
        );
        return $updated!==false;
    }
}

if(!function_exists('cg_paid_actions_snapshot_from_database_row')){
    function cg_paid_actions_snapshot_from_database_row($row){
        $source = !empty($row->PaidActionSource) ? sanitize_key($row->PaidActionSource) : '';
        if($source===''){
            if(!empty($row->IsPaidActionPreview) || (!empty($row->EntitlementIsTest) && absint($row->EntitlementIsTest)===1)){
                $source = 'preview';
            }elseif(!empty($row->GrantType) && sanitize_key($row->GrantType)==='admin'){
                $source = 'admin';
            }elseif(!empty($row->GrantType) && sanitize_key($row->GrantType)==='sandbox_purchase'){
                $source = 'sandbox_purchase';
            }elseif(!empty($row->PaidActionOrderItem) || !empty($row->LedgerOrderItem)){
                $source = 'purchase';
            }else{
                $source = 'admin';
            }
        }

        $price = isset($row->PaidActionPriceUnitGross) ? $row->PaidActionPriceUnitGross : 0;
        if((float)$price<=0 && isset($row->OrderPriceUnitGross)){$price = $row->OrderPriceUnitGross;}
        $currencyShort = !empty($row->PaidActionCurrencyShort) ? $row->PaidActionCurrencyShort : (isset($row->OrderCurrencyShort) ? $row->OrderCurrencyShort : '');
        $currencyPosition = !empty($row->PaidActionCurrencyPosition) ? $row->PaidActionCurrencyPosition : (isset($row->OrderCurrencyPosition) ? $row->OrderCurrencyPosition : '');
        $priceDivider = !empty($row->PaidActionPriceDivider) ? $row->PaidActionPriceDivider : (isset($row->OrderLogForDatabase) ? cg_paid_actions_price_divider_from_order_log($row->OrderLogForDatabase) : '.');
        $parentOrder = !empty($row->PaidActionParentOrder) ? $row->PaidActionParentOrder : (isset($row->LedgerParentOrder) ? $row->LedgerParentOrder : 0);
        $orderItem = !empty($row->PaidActionOrderItem) ? $row->PaidActionOrderItem : (isset($row->LedgerOrderItem) ? $row->LedgerOrderItem : 0);

        return array(
            'objectId'=>absint($row->id),'source'=>$source,
            'priceUnitGross'=>number_format((float)$price,2,'.',''),
            'currencyShort'=>sanitize_text_field($currencyShort),
            'currencyPosition'=>sanitize_key($currencyPosition)==='right' ? 'right' : 'left',
            'priceDivider'=>in_array($priceDivider,array('.',','),true) ? $priceDivider : '.',
            'parentOrder'=>absint($parentOrder),'orderItem'=>absint($orderItem),
            'paymentStatus'=>isset($row->OrderPaymentStatus) ? sanitize_text_field($row->OrderPaymentStatus) : '',
            'orderNumber'=>isset($row->OrderNumber) ? sanitize_text_field($row->OrderNumber) : '',
            'orderGalleryId'=>isset($row->OrderGalleryID) ? absint($row->OrderGalleryID) : 0
        );
    }
}

if(!function_exists('cg_paid_actions_get_action_payment_details')){
    function cg_paid_actions_get_action_payment_details($galleryId,$actionType,$objectIds){
        global $wpdb;

        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $objectIds = array_values(array_unique(array_filter(array_map('absint',(array)$objectIds))));
        $table = cg_paid_actions_action_table($actionType);
        if(!$galleryId || $actionType==='' || empty($objectIds) || $table===''){return array();}

        $placeholders = implode(',',array_fill(0,count($objectIds),'%d'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id, IsPaidAction, IsPaidActionPreview, PaidActionSource, PaidActionPriceUnitGross,
                    PaidActionCurrencyShort, PaidActionCurrencyPosition, PaidActionPriceDivider,
                    PaidActionParentOrder, PaidActionOrderItem
             FROM $table
             WHERE GalleryID = %d AND id IN ($placeholders) AND IsPaidAction = 1",
            array_merge(array($galleryId),$objectIds)
        ));
        if(empty($rows)){return array();}

        $details = array();
        $fallbackIds = array();
        foreach($rows as $row){
            $details[absint($row->id)] = cg_paid_actions_snapshot_from_database_row($row);
            if(empty($row->PaidActionSource) || (in_array(sanitize_key($row->PaidActionSource),array('purchase','sandbox_purchase'),true) && (float)$row->PaidActionPriceUnitGross<=0)){
                $fallbackIds[] = absint($row->id);
            }
        }

        if(!empty($fallbackIds)){
            $ledger = cg_paid_actions_table('ledger');
            $entitlements = cg_paid_actions_table('entitlements');
            $items = cg_paid_actions_table('order_items');
            $orders = cg_paid_actions_table('orders');
            $fallbackPlaceholders = implode(',',array_fill(0,count($fallbackIds),'%d'));
            $fallbackRows = $wpdb->get_results($wpdb->prepare(
                "SELECT action_row.id, action_row.IsPaidAction, action_row.IsPaidActionPreview,
                        action_row.PaidActionSource, action_row.PaidActionPriceUnitGross,
                        action_row.PaidActionCurrencyShort, action_row.PaidActionCurrencyPosition,
                        action_row.PaidActionPriceDivider, action_row.PaidActionParentOrder, action_row.PaidActionOrderItem,
                        consume.ParentOrder AS LedgerParentOrder, consume.OrderItem AS LedgerOrderItem,
                        entitlement.GrantType, entitlement.IsTest AS EntitlementIsTest,
                        item.PriceUnitGross AS OrderPriceUnitGross,
                        sale.CurrencyShort AS OrderCurrencyShort, sale.CurrencyPosition AS OrderCurrencyPosition,
                        sale.PaymentStatus AS OrderPaymentStatus, sale.OrderNumber,
                        sale.GalleryID AS OrderGalleryID, sale.LogForDatabase AS OrderLogForDatabase
                 FROM $table action_row
                 INNER JOIN $ledger consume ON consume.TargetGalleryID = action_row.GalleryID
                    AND consume.ActionType = %s AND consume.EventType = 'consume' AND consume.ObjectId = action_row.id
                 LEFT JOIN $entitlements entitlement ON entitlement.id = consume.EntitlementId
                 LEFT JOIN $items item ON item.id = consume.OrderItem AND item.ParentOrder = consume.ParentOrder
                 LEFT JOIN $orders sale ON sale.id = consume.ParentOrder
                 WHERE action_row.GalleryID = %d AND action_row.id IN ($fallbackPlaceholders)
                 ORDER BY consume.id ASC",
                array_merge(array($actionType,$galleryId),$fallbackIds)
            ));
            foreach($fallbackRows as $fallbackRow){
                $objectId = absint($fallbackRow->id);
                if(!isset($details[$objectId]) || empty($details[$objectId]['source']) || (float)$details[$objectId]['priceUnitGross']<=0){
                    $details[$objectId] = cg_paid_actions_snapshot_from_database_row($fallbackRow);
                }
            }
        }

        $orderIds = array();
        foreach($details as $detail){
            if(!empty($detail['parentOrder'])){$orderIds[$detail['parentOrder']] = $detail['parentOrder'];}
        }
        if(!empty($orderIds)){
            $orders = cg_paid_actions_table('orders');
            $orderPlaceholders = implode(',',array_fill(0,count($orderIds),'%d'));
            $orderRows = $wpdb->get_results($wpdb->prepare(
                "SELECT id, GalleryID, PaymentStatus, OrderNumber FROM $orders WHERE id IN ($orderPlaceholders)",
                array_values($orderIds)
            ));
            $ordersById = array();
            foreach($orderRows as $orderRow){$ordersById[absint($orderRow->id)] = $orderRow;}
            foreach($details as $objectId=>$detail){
                if(!empty($detail['parentOrder']) && !empty($ordersById[$detail['parentOrder']])){
                    $orderRow = $ordersById[$detail['parentOrder']];
                    $details[$objectId]['paymentStatus'] = sanitize_text_field($orderRow->PaymentStatus);
                    $details[$objectId]['orderNumber'] = sanitize_text_field($orderRow->OrderNumber);
                    $details[$objectId]['orderGalleryId'] = absint($orderRow->GalleryID);
                }
            }
        }
        return $details;
    }
}

if(!function_exists('cg_paid_actions_format_payment_price')){
    function cg_paid_actions_format_payment_price($detail){
        if(empty($detail) || empty($detail['currencyShort']) || (float)$detail['priceUnitGross']<=0){return '';}
        $currencies = function_exists('cg_get_ecommerce_currencies_array_formatted_by_short_key') ? cg_get_ecommerce_currencies_array_formatted_by_short_key() : array();
        if(function_exists('cg_ecommerce_price_to_show') && isset($currencies[$detail['currencyShort']])){
            return cg_ecommerce_price_to_show(
                $currencies,$detail['currencyShort'],$detail['currencyPosition'],$detail['priceDivider'],(float)$detail['priceUnitGross']
            );
        }
        return number_format((float)$detail['priceUnitGross'],2,$detail['priceDivider'],$detail['priceDivider']===',' ? '.' : ',').' '.$detail['currencyShort'];
    }
}

if(!function_exists('cg_paid_actions_backfill_action_snapshots')){
    function cg_paid_actions_backfill_action_snapshots(){
        global $wpdb;

        $ledger = cg_paid_actions_table('ledger');
        $entitlements = cg_paid_actions_table('entitlements');
        $items = cg_paid_actions_table('order_items');
        $orders = cg_paid_actions_table('orders');
        $updatedCount = 0;

        foreach(cg_paid_actions_types() as $actionType){
            $table = cg_paid_actions_action_table($actionType);
            $lastObjectId = 0;
            while($table!==''){
                $rows = $wpdb->get_results($wpdb->prepare(
                    "SELECT action_row.id, action_row.GalleryID, action_row.IsPaidAction, action_row.IsPaidActionPreview,
                            action_row.PaidActionSource, action_row.PaidActionPriceUnitGross,
                            action_row.PaidActionCurrencyShort, action_row.PaidActionCurrencyPosition,
                            action_row.PaidActionPriceDivider, action_row.PaidActionParentOrder, action_row.PaidActionOrderItem,
                            consume.ParentOrder AS LedgerParentOrder, consume.OrderItem AS LedgerOrderItem,
                            entitlement.GrantType, entitlement.IsTest AS EntitlementIsTest,
                            item.PriceUnitGross AS OrderPriceUnitGross,
                            sale.CurrencyShort AS OrderCurrencyShort, sale.CurrencyPosition AS OrderCurrencyPosition,
                            sale.PaymentStatus AS OrderPaymentStatus, sale.OrderNumber,
                            sale.GalleryID AS OrderGalleryID, sale.LogForDatabase AS OrderLogForDatabase
                     FROM $table action_row
                     INNER JOIN $ledger consume ON consume.TargetGalleryID = action_row.GalleryID
                        AND consume.ActionType = %s AND consume.EventType = 'consume' AND consume.ObjectId = action_row.id
                     LEFT JOIN $entitlements entitlement ON entitlement.id = consume.EntitlementId
                     LEFT JOIN $items item ON item.id = consume.OrderItem AND item.ParentOrder = consume.ParentOrder
                     LEFT JOIN $orders sale ON sale.id = consume.ParentOrder
                     WHERE action_row.id > %d AND action_row.IsPaidAction = 1 AND action_row.PaidActionSource = ''
                     ORDER BY action_row.id ASC, consume.id ASC LIMIT 500",
                    $actionType,$lastObjectId
                ));
                if(empty($rows)){break;}

                $processed = array();
                foreach($rows as $row){
                    $objectId = absint($row->id);
                    $lastObjectId = max($lastObjectId,$objectId);
                    if(!$objectId || isset($processed[$objectId])){continue;}
                    $processed[$objectId] = true;
                    $snapshot = cg_paid_actions_snapshot_from_database_row($row);
                    $updated = $wpdb->update(
                        $table,
                        array(
                            'PaidActionSource'=>$snapshot['source'],
                            'PaidActionPriceUnitGross'=>$snapshot['priceUnitGross'],
                            'PaidActionCurrencyShort'=>$snapshot['currencyShort'],
                            'PaidActionCurrencyPosition'=>$snapshot['currencyPosition'],
                            'PaidActionPriceDivider'=>$snapshot['priceDivider'],
                            'PaidActionParentOrder'=>in_array($snapshot['source'],array('purchase','sandbox_purchase'),true) ? $snapshot['parentOrder'] : 0,
                            'PaidActionOrderItem'=>in_array($snapshot['source'],array('purchase','sandbox_purchase'),true) ? $snapshot['orderItem'] : 0
                        ),
                        array('id'=>$objectId,'GalleryID'=>absint($row->GalleryID)),
                        array('%s','%f','%s','%s','%s','%d','%d'),
                        array('%d','%d')
                    );
                    if($updated!==false){$updatedCount++;}
                }
                if(count($rows)<500){break;}
            }
        }
        return $updatedCount;
    }
}

if(!function_exists('cg_paid_actions_get_ecommerce_readiness')){
    function cg_paid_actions_get_ecommerce_readiness(){
        global $wpdb;

        $result = array(
            'ready' => false,
            'paymentReady' => false,
            'proAvailable' => cg_paid_actions_is_pro_available(),
            'currencyReady' => false,
            'paypalReady' => false,
            'stripeReady' => false,
            'paypalSandboxReady' => false,
            'stripeSandboxReady' => false,
            'sandboxPaymentReady' => false,
            'orderMailReady' => false,
            'reasons' => array(),
            'options' => false,
            'checks' => array(
                'currency'=>array('state'=>'missing','value'=>''),
                'tax'=>array('state'=>'unavailable','value'=>'0.00'),
                'paypal'=>array('state'=>'unavailable'),
                'stripe'=>array('state'=>'unavailable'),
                'paypalSandbox'=>array('state'=>'unavailable'),
                'stripeSandbox'=>array('state'=>'unavailable'),
                'orderMail'=>array('state'=>'unavailable'),
                'invoice'=>array('state'=>'unavailable')
            )
        );

        $table = cg_paid_actions_table('ecommerce_options');
        $options = $wpdb->get_row("SELECT * FROM $table WHERE GeneralID = 1 LIMIT 1");
        $result['options'] = $options;
        if(empty($options)){
            $result['reasons'][] = 'Ecommerce options are not available.';
            return $result;
        }

        $currency = !empty($options->CurrencyShort) ? sanitize_text_field($options->CurrencyShort) : '';
        $result['currencyReady'] = ($currency!=='');
        $result['checks']['currency'] = array(
            'state'=>$result['currencyReady'] ? 'ready' : 'missing',
            'value'=>$currency
        );
        $result['checks']['tax'] = array(
            'state'=>'configured',
            'value'=>number_format(round(isset($options->TaxPercentageDefault) ? (float)$options->TaxPercentageDefault : 0,2),2,'.','')
        );

        if(!$result['currencyReady']){
            $result['reasons'][] = 'Select an Ecommerce currency.';
        }

        $paypalEnabled = isset($options->PayPalApiActive) && intval($options->PayPalApiActive)===1;
        $paypalCredentialsReady = !empty($options->PayPalLiveClientId) && !empty($options->PayPalLiveSecret);
        $result['paypalReady'] = $paypalEnabled && $paypalCredentialsReady;
        $result['checks']['paypal'] = array(
            'state'=>$result['paypalReady'] ? 'ready' : ($paypalEnabled ? 'needs_keys' : 'off')
        );
        $paypalSandboxActive = !empty($options->PayPalTestActive);
        $paypalSandboxCredentialsReady = !empty($options->PayPalSandboxClientId) && !empty($options->PayPalSandboxSecret);
        $result['paypalSandboxReady'] = $paypalEnabled && $paypalSandboxActive && $paypalSandboxCredentialsReady;
        $result['checks']['paypalSandbox'] = array(
            'state'=>$result['paypalSandboxReady'] ? 'ready' : (!$paypalEnabled ? 'off' : (!$paypalSandboxActive ? 'testing_disabled' : 'needs_keys'))
        );

        $stripeEnabled = isset($options->StripeApiActive) && intval($options->StripeApiActive)===1;
        $stripeCredentialsReady = !empty($options->StripeLiveClientId) && !empty($options->StripeLiveSecret);
        $stripeHttpsReady = is_ssl() || stripos(home_url('/'),'https://')===0;
        $result['stripeReady'] = $stripeEnabled && $stripeCredentialsReady && $stripeHttpsReady;
        $stripeState = 'off';
        if($stripeEnabled && !$stripeCredentialsReady){
            $stripeState = 'needs_keys';
        }elseif($stripeEnabled && !$stripeHttpsReady){
            $stripeState = 'needs_https';
        }elseif($result['stripeReady']){
            $stripeState = 'ready';
        }
        $result['checks']['stripe'] = array('state'=>$stripeState);
        $stripeSandboxActive = !empty($options->StripeTestActive);
        $stripeSandboxCredentialsReady = !empty($options->StripeSandboxClientId) && !empty($options->StripeSandboxSecret);
        $result['stripeSandboxReady'] = $stripeEnabled && $stripeSandboxActive && $stripeSandboxCredentialsReady && $stripeHttpsReady;
        $stripeSandboxState = 'off';
        if($stripeEnabled && !$stripeSandboxActive){
            $stripeSandboxState = 'testing_disabled';
        }elseif($stripeEnabled && !$stripeSandboxCredentialsReady){
            $stripeSandboxState = 'needs_keys';
        }elseif($stripeEnabled && !$stripeHttpsReady){
            $stripeSandboxState = 'needs_https';
        }elseif($result['stripeSandboxReady']){
            $stripeSandboxState = 'ready';
        }
        $result['checks']['stripeSandbox'] = array('state'=>$stripeSandboxState);

        if(!$result['paypalReady'] && !$result['stripeReady']){
            $result['reasons'][] = 'Configure at least one live PayPal or Stripe payment method.';
        }

        $orderMailActive = !empty($options->SendOrderConfirmationMail);
        $result['orderMailReady'] = !$orderMailActive || (
            !empty($options->OCMailOrderSummaryURL) &&
            strpos((string)$options->OrderConfirmationMail,'$order$')!==false
        );
        $result['checks']['orderMail'] = array(
            'state'=>$orderMailActive ? ($result['orderMailReady'] ? 'ready' : 'needs_setup') : 'off'
        );
        if(!$result['orderMailReady']){
            $result['reasons'][] = 'Complete the active order confirmation mail configuration.';
        }

        $invoiceActive = !empty($options->CreateInvoice);
        $result['checks']['invoice'] = array(
            'state'=>$invoiceActive ? (!empty($options->SendInvoice) ? 'email' : 'download') : 'off'
        );

        $result['paymentReady'] = (
            $result['currencyReady'] &&
            ($result['paypalReady'] || $result['stripeReady']) &&
            $result['orderMailReady'] &&
            empty($result['reasons'])
        );
        $result['ready'] = $result['paymentReady'];
        $result['sandboxPaymentReady'] = (
            $result['proAvailable'] &&
            $result['currencyReady'] &&
            ($result['paypalSandboxReady'] || $result['stripeSandboxReady']) &&
            $result['orderMailReady']
        );

        return $result;
    }
}

if(!function_exists('cg_paid_actions_can_sell_action')){
    function cg_paid_actions_can_sell_action($actionType,$readiness = false){
        if(!is_array($readiness)){$readiness = cg_paid_actions_get_ecommerce_readiness();}
        return !empty($readiness['paymentReady']) && cg_paid_actions_action_license_available($actionType,$readiness);
    }
}

if(!function_exists('cg_paid_actions_get_gate_status')){
    function cg_paid_actions_get_gate_status($galleryId,$actionType){
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return array(
                'status'=>'off','enforce'=>false,
                'settings'=>cg_paid_actions_default_settings($galleryId),
                'readiness'=>cg_paid_actions_get_ecommerce_readiness()
            );
        }
        $settings = cg_paid_actions_get_settings($galleryId);
        $mode = cg_paid_actions_get_mode($settings,$actionType);
        $readiness = cg_paid_actions_get_ecommerce_readiness();

        if($mode===0){
            return array('status'=>'off','enforce'=>false,'settings'=>$settings,'readiness'=>$readiness);
        }
        if($mode===1){
            return array('status'=>'draft','enforce'=>false,'settings'=>$settings,'readiness'=>$readiness);
        }
        if(!cg_paid_actions_can_sell_action($actionType,$readiness)){
            return array('status'=>'blocked','enforce'=>true,'settings'=>$settings,'readiness'=>$readiness);
        }
        return array('status'=>'live','enforce'=>true,'settings'=>$settings,'readiness'=>$readiness);
    }
}

if(!function_exists('cg_paid_actions_get_gallery_runtime')){
    function cg_paid_actions_get_gallery_runtime($galleryId,$userId = 0,$environment = 'live'){
        $environment = cg_paid_actions_normalize_environment($environment);
        $isSupported = cg_paid_actions_gallery_is_supported($galleryId);
        $settings = $isSupported ? cg_paid_actions_get_settings($galleryId) : cg_paid_actions_default_settings($galleryId);
        $readiness = cg_paid_actions_get_ecommerce_readiness();
        $actions = array();

		foreach(cg_paid_actions_types() as $actionType){
			$mode = cg_paid_actions_get_mode($settings,$actionType);
			$status = $mode===0 ? 'off' : ($mode===1 ? 'draft' : (cg_paid_actions_can_sell_action($actionType,$readiness) ? 'live' : 'blocked'));
            $balance = $userId ? cg_paid_actions_get_balance($userId,$galleryId,$actionType,$settings,$environment) : array('local'=>0,'shared'=>0,'total'=>0);
            $actions[$actionType] = array(
				'mode' => $mode,
				'status' => $status,
                'liveAllowed' => cg_paid_actions_can_sell_action($actionType,$readiness) ? 1 : 0,
                'price' => cg_paid_actions_get_price($settings,$actionType),
                'balance' => $balance
            );
        }

        return array(
            'galleryId' => absint($galleryId),
            'shareCredits' => ($isSupported && !empty($settings->ShareCredits)) ? 1 : 0,
            'proAvailable' => !empty($readiness['proAvailable']),
            'checkoutReady' => !empty($readiness['paymentReady']),
            'actions' => $actions
        );
    }
}

if(!function_exists('cg_paid_actions_is_purchase_cart')){
    function cg_paid_actions_is_purchase_cart($purchaseUnits){
        return (
            !empty($purchaseUnits[0]['items'][0]) &&
            is_array($purchaseUnits[0]['items'][0]) &&
            !empty($purchaseUnits[0]['items'][0]['PaidActionType'])
        );
    }
}

if(!function_exists('cg_paid_actions_build_authoritative_cart')){
    function cg_paid_actions_build_authoritative_cart($purchaseUnits,$ecommerceOptions){
        $failed = array('success'=>false,'message'=>cg_paid_actions_t('CheckoutChanged'));
        if(!is_user_logged_in() || empty($purchaseUnits[0]['items']) || !is_array($purchaseUnits[0]['items'])){
            return $failed;
        }
        $currency = !empty($ecommerceOptions->CurrencyShort) ? sanitize_text_field($ecommerceOptions->CurrencyShort) : '';
        if($currency===''){return $failed;}

        $defaultTax = round((float)$ecommerceOptions->TaxPercentageDefault,2);
        $normalized = array();
        $signature = array();
        $grossTotal = 0;
        $netTotal = 0;
        $taxTotal = 0;
        $usedGalleryId = 0;
        $sortIndex = 0;
        $seenTypes = array();

        foreach($purchaseUnits[0]['items'] as $item){
            $sortIndex++;
            $galleryId = !empty($item['realGid']) ? absint($item['realGid']) : 0;
            $actionType = cg_paid_actions_normalize_type(isset($item['PaidActionType']) ? $item['PaidActionType'] : '');
            $quantity = isset($item['quantity']) ? absint($item['quantity']) : 0;
            if(!$galleryId || $actionType==='' || isset($seenTypes[$actionType]) || !$quantity || $quantity>10000 || ($usedGalleryId && $usedGalleryId!==$galleryId)){
                return $failed;
            }
			if(!cg_paid_actions_gallery_is_supported($galleryId)){
				return array(
					'success'=>false,
					'message'=>cg_paid_actions_t('GalleryUnsupported')
				);
			}
			$seenTypes[$actionType] = true;
            $gate = cg_paid_actions_get_gate_status($galleryId,$actionType);
            $sandboxContext = false;
            if(cg_paid_actions_request_preview_token()!=='' && function_exists('cg_paid_actions_get_preview_context')){
                $sandboxContext = cg_paid_actions_get_preview_context($galleryId);
                if(empty($sandboxContext['checkoutMode']) || $sandboxContext['checkoutMode']!=='sandbox'){$sandboxContext = false;}
            }
            $isSandboxCheckout = !empty($sandboxContext) && !empty($_REQUEST['cg_paid_actions_checkout_mode']) && sanitize_key(wp_unslash($_REQUEST['cg_paid_actions_checkout_mode']))==='sandbox';
            $sandboxModeRequested = !empty($_REQUEST['cg_paid_actions_checkout_mode']) && sanitize_key(wp_unslash($_REQUEST['cg_paid_actions_checkout_mode']))==='sandbox';
            if(!empty($sandboxContext) && !$sandboxModeRequested){return $failed;}
            if($sandboxModeRequested && !$isSandboxCheckout){return $failed;}
            if($isSandboxCheckout){
                $readiness = cg_paid_actions_get_ecommerce_readiness();
                $provider = !empty($_REQUEST['cg_paid_actions_checkout_provider']) ? sanitize_key(wp_unslash($_REQUEST['cg_paid_actions_checkout_provider'])) : '';
                $providerReady = ($provider==='paypal' && !empty($readiness['paypalSandboxReady'])) || ($provider==='stripe' && !empty($readiness['stripeSandboxReady']));
                if(!$providerReady || cg_paid_actions_get_mode($gate['settings'],$actionType)===0){return $failed;}
                $GLOBALS['cgPaidActionsSandboxCheckoutContext'] = $sandboxContext;
            }elseif($gate['status']!=='live'){
                return $failed;
            }
            $priceGross = cg_paid_actions_get_price($gate['settings'],$actionType);
            if($priceGross<=0){return $failed;}
            $priceNet = $defaultTax ? round($priceGross/(1+($defaultTax/100)),2) : $priceGross;
            $taxUnit = round($priceGross-$priceNet,2);
			$scope = !empty($gate['settings']->ShareCredits) ? 'shared' : 'local';
			$creditLabelKeys = array(
				'upload'=>$quantity===1 ? 'UploadCredit' : 'UploadCredits',
				'vote'=>$quantity===1 ? 'VotingCredit' : 'VotingCredits',
				'comment'=>$quantity===1 ? 'CommentCredit' : 'CommentCredits'
			);
			$typeLabelKeys = array('upload'=>'Uploads','vote'=>'Votes','comment'=>'Comments');
			$name = $quantity.' '.cg_paid_actions_t($creditLabelKeys[$actionType]);
            $row = array(
                'EcommerceEntryID'=>0,'gid'=>$galleryId,'realGid'=>$galleryId,'realId'=>0,'quantity'=>$quantity,
                'timestampMilliseconds'=>(time()*1000)+$sortIndex,
                'PaidActionType'=>$actionType,'PaidActionScope'=>$scope,'name'=>$name,
				'description'=>cg_paid_actions_t('CreditsForTypeGallery',array('type'=>strtolower(cg_paid_actions_t($typeLabelKeys[$actionType])),'number'=>$galleryId)),
                'priceGross'=>cg_ecommerce_format_money_value($priceGross),
                'unit_amount'=>array('currency_code'=>$currency,'value'=>cg_ecommerce_format_money_value($priceNet)),
                'tax_percentage'=>cg_ecommerce_format_money_value($defaultTax),
                'IsDownload'=>0,'IsShipping'=>0,'IsService'=>0,'IsUpload'=>0,'IsAlternativeShipping'=>0,
                'alternative_shipping_amount_value_gross'=>'0.00','alternative_shipping_amount_value_net'=>'0.00',
                'DownloadKeysCsvName'=>'','ServiceKeysCsvName'=>'','SaleType'=>'paid-action',
                'RawData'=>array('realGid'=>$galleryId,'id'=>0,'ecommerceData'=>array()),'WpUploads'=>array()
            );
            if($taxUnit){
                $row['tax'] = array('currency_code'=>$currency,'value'=>cg_ecommerce_format_money_value($taxUnit));
            }
            $normalized[] = $row;
            $grossTotal = round($grossTotal+($priceGross*$quantity),2);
            $netTotal = round($netTotal+($priceNet*$quantity),2);
            $taxTotal = round($taxTotal+($taxUnit*$quantity),2);
            $signature[] = implode(':',array($galleryId,$actionType,$scope,$quantity,cg_ecommerce_format_money_value($priceGross),cg_ecommerce_format_money_value($defaultTax)));
            $usedGalleryId = $galleryId;
        }
        if(empty($normalized)){return $failed;}
        sort($signature,SORT_STRING);

        $unit = !empty($purchaseUnits[0]) && is_array($purchaseUnits[0]) ? $purchaseUnits[0] : array();
        $unit['items'] = $normalized;
        $unit['amount'] = array(
            'currency_code'=>$currency,'value'=>cg_ecommerce_format_money_value($grossTotal),
            'breakdown'=>array(
                'item_total'=>array('currency_code'=>$currency,'value'=>cg_ecommerce_format_money_value($netTotal)),
                'tax_total'=>array('currency_code'=>$currency,'value'=>cg_ecommerce_format_money_value($taxTotal)),
                'shipping'=>array('currency_code'=>$currency,'value'=>'0.00')
            )
        );
        $hash = cg_hash_function('---cgPaidActionsCart---'.wp_json_encode(array('currency'=>$currency,'items'=>$signature)));

        return array(
            'success'=>true,'message'=>'','purchase_units'=>array($unit),'cart_hash'=>$hash,
            'amount_cents'=>absint(round($grossTotal*100)),'currency_short'=>$currency,'item_count'=>count($normalized),
            'DefaultShipping'=>0,'TaxPercentageDefault'=>$defaultTax,'ShippingNet'=>0,'ShippingGross'=>0,'ShippingTaxValue'=>0,
            'alternativeShippingTotal'=>0,'alternativeShippingTotalTaxValue'=>0,'priceTotalUnformatted'=>$grossTotal,
            'priceTotalWithShippingTotalUnformatted'=>$grossTotal,'priceTotalWithShipping'=>$grossTotal,'defaultShipping'=>0,
            'PriceTotalNetItems'=>$netTotal,'PriceTotalNetItemsWithShipping'=>$netTotal,'PriceTotalGrossItems'=>$grossTotal,
            'PriceTotalGrossItemsWithShipping'=>$grossTotal,'TaxValueTotalItems'=>$taxTotal,'ShippingTotal'=>0,
            'usedGalleryId'=>$usedGalleryId
        );
    }
}

if(!function_exists('cg_paid_actions_settings_array_for_database')){
    function cg_paid_actions_settings_array_for_database($galleryId,$current,$values){
        $readiness = cg_paid_actions_get_ecommerce_readiness();
        $actionValues = array();

        foreach(cg_paid_actions_types() as $actionType){
            $uc = ucfirst($actionType);
            $enabled = !empty($values[$actionType.'Enabled']);
            $price = cg_paid_actions_sanitize_price(isset($values[$actionType.'Price']) ? $values[$actionType.'Price'] : '');
            if($price===false && $enabled){
                return new WP_Error('cg_paid_action_invalid_price','Every enabled paid action requires a positive price.');
            }
            if($price===false){
                $currentPriceProperty = $uc.'Price';
                $price = cg_paid_actions_sanitize_price(isset($current->$currentPriceProperty) ? $current->$currentPriceProperty : '1.00');
                if($price===false){$price = '1.00';}
            }

            if(!$enabled){
                $mode = 0;
            }else{
                $mode = cg_paid_actions_can_sell_action($actionType,$readiness) ? 2 : 1;
            }

            $actionValues[$uc.'Mode'] = $mode;
            $actionValues[$uc.'Price'] = $price;
        }

        return array(
            'GalleryID' => absint($galleryId),
            'UploadMode' => $actionValues['UploadMode'],
            'VoteMode' => $actionValues['VoteMode'],
            'CommentMode' => $actionValues['CommentMode'],
            'UploadPrice' => $actionValues['UploadPrice'],
            'VotePrice' => $actionValues['VotePrice'],
            'CommentPrice' => $actionValues['CommentPrice'],
            'ShareCredits' => !empty($values['shareCredits']) ? 1 : 0,
            'UpdatedBy' => get_current_user_id(),
            'UpdatedAt' => cg_paid_actions_now()
        );
    }
}

if(!function_exists('cg_paid_actions_update_options_json')){
    function cg_paid_actions_update_options_json($galleryId,$changes){
        $galleryId = absint($galleryId);
        $uploadDir = wp_upload_dir();
        $file = $uploadDir['basedir'].'/contest-gallery/gallery-id-'.$galleryId.'/json/'.$galleryId.'-options.json';
        if(!file_exists($file) || !is_readable($file) || !is_writable($file)){
            return false;
        }

        $data = json_decode(file_get_contents($file),true);
        if(!is_array($data)){
            return false;
        }

        $key = isset($data[$galleryId]) ? $galleryId : null;
        $target =& $data;
        if($key!==null){
            $target =& $data[$key];
        }

        if(!empty($changes['upload'])){
            if(empty($target['pro']) || !is_array($target['pro'])){$target['pro'] = array();}
            $target['pro']['GalleryUpload'] = 1;
            $target['pro']['RegUserUploadOnly'] = 1;
        }
        if(!empty($changes['vote'])){
            if(empty($target['general']) || !is_array($target['general'])){$target['general'] = array();}
            if(empty($target['general']['AllowRating'])){$target['general']['AllowRating'] = 2;}
            $target['general']['CheckLogin'] = 1;
            $target['general']['CheckIp'] = 0;
        }
        if(!empty($changes['comment'])){
            if(empty($target['general']) || !is_array($target['general'])){$target['general'] = array();}
            if(empty($target['pro']) || !is_array($target['pro'])){$target['pro'] = array();}
            $target['general']['AllowComments'] = 1;
            $target['pro']['CheckLoginComment'] = 1;
        }

        $encoded = wp_json_encode($data);
        if($encoded===false){
            return false;
        }
        return file_put_contents($file,$encoded,LOCK_EX)!==false;
    }
}

if(!function_exists('cg_paid_actions_apply_live_prerequisites')){
    function cg_paid_actions_apply_live_prerequisites($galleryId,$previous,$saved){
        global $wpdb;

        $changes = array('upload'=>false,'vote'=>false,'comment'=>false);
        foreach(cg_paid_actions_types() as $actionType){
            $property = ucfirst($actionType).'Mode';
            $changes[$actionType] = (
                isset($saved[$property]) && absint($saved[$property])===2 &&
                (!isset($previous->$property) || absint($previous->$property)!==2)
            );
        }
        if(!in_array(true,$changes,true)){
            return true;
        }

        $galleryId = absint($galleryId);
        if($changes['upload']){
            $wpdb->update(cg_paid_actions_table('pro_options'),array('GalleryUpload'=>1,'RegUserUploadOnly'=>1),array('GalleryID'=>$galleryId),array('%d','%d'),array('%d'));
        }
        if($changes['vote']){
            $optionsTable = cg_paid_actions_table('options');
            $wpdb->query($wpdb->prepare("UPDATE $optionsTable SET AllowRating = IF(AllowRating=0,2,AllowRating), CheckLogin = 1, CheckIp = 0 WHERE id = %d",$galleryId));
        }
        if($changes['comment']){
            $wpdb->update(cg_paid_actions_table('options'),array('AllowComments'=>1),array('id'=>$galleryId),array('%d'),array('%d'));
        }

        cg_paid_actions_update_options_json($galleryId,$changes);
        return true;
    }
}

if(!function_exists('cg_paid_actions_save_settings')){
    function cg_paid_actions_save_settings($galleryId,$values){
        global $wpdb;

        $galleryId = absint($galleryId);
        $optionsTable = cg_paid_actions_table('options');
        if(!$galleryId || !$wpdb->get_var($wpdb->prepare("SELECT id FROM $optionsTable WHERE id = %d LIMIT 1",$galleryId))){
            return new WP_Error('cg_paid_action_gallery_missing','Gallery could not be found.');
        }
        if(!cg_paid_actions_gallery_is_supported($galleryId)){
            return new WP_Error(
                'cg_paid_actions_gallery_version_unsupported',
                'Earning options are available only for galleries created or copied with Contest Gallery 22.0.0 or later.'
            );
        }

        $current = cg_paid_actions_get_settings($galleryId);
        $data = cg_paid_actions_settings_array_for_database($galleryId,$current,$values);
        if(is_wp_error($data)){
            return $data;
        }

        $table = cg_paid_actions_table('settings');
        if(!empty($current->id)){
            $result = $wpdb->update($table,$data,array('GalleryID'=>$galleryId),array('%d','%d','%d','%d','%s','%s','%s','%d','%d','%s'),array('%d'));
        }else{
            $result = $wpdb->insert($table,$data,array('%d','%d','%d','%d','%s','%s','%s','%d','%d','%s'));
        }
        if($result===false){
            return new WP_Error('cg_paid_action_save_failed','Paid action settings could not be saved.');
        }

        cg_paid_actions_apply_live_prerequisites($galleryId,$current,$data);
        return cg_paid_actions_get_settings($galleryId);
    }
}

if(!function_exists('cg_paid_actions_available_sql')){
    function cg_paid_actions_available_sql(){
        return 'GREATEST(Granted - Reserved - Consumed - Revoked,0)';
    }
}

if(!function_exists('cg_paid_actions_normalize_environment')){
    function cg_paid_actions_normalize_environment($environment){
        return $environment==='sandbox' ? 'sandbox' : 'live';
    }
}

if(!function_exists('cg_paid_actions_environment_where_sql')){
    function cg_paid_actions_environment_where_sql($environment,$tableAlias = ''){
        $environment = cg_paid_actions_normalize_environment($environment);
        $tableAlias = preg_replace('/[^A-Za-z0-9_]/','',(string)$tableAlias);
        $prefix = $tableAlias!=='' ? $tableAlias.'.' : '';
        if($environment==='sandbox'){
            return $prefix."IsTest = 1 AND ".$prefix."GrantType IN ('sandbox_purchase','sandbox_admin')";
        }
        return $prefix.'IsTest = 0';
    }
}

if(!function_exists('cg_paid_actions_get_scope_balance')){
    function cg_paid_actions_get_scope_balance($userId,$galleryId,$actionType,$scopeType,$environment = 'live'){
        global $wpdb;

        $userId = absint($userId);
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $scopeType = $scopeType==='shared' ? 'shared' : 'local';
        if(!$userId || !$galleryId || $actionType===''){
            return 0;
        }

        $table = cg_paid_actions_table('entitlements');
        $availableSql = cg_paid_actions_available_sql();
        $environmentWhere = cg_paid_actions_environment_where_sql($environment);
        if($scopeType==='shared'){
            return absint($wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM($availableSql),0) FROM $table WHERE WpUserId = %d AND ActionType = %s AND ScopeType = 'shared' AND $environmentWhere",
                $userId,$actionType
            )));
        }
        return absint($wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM($availableSql),0) FROM $table WHERE WpUserId = %d AND OriginGalleryID = %d AND ActionType = %s AND ScopeType = 'local' AND $environmentWhere",
            $userId,$galleryId,$actionType
        )));
    }
}

if(!function_exists('cg_paid_actions_get_balance')){
    function cg_paid_actions_get_balance($userId,$galleryId,$actionType,$settings = null,$environment = 'live'){
        global $wpdb;

        $userId = absint($userId);
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $result = array('local'=>0,'shared'=>0,'total'=>0);
        if(!$userId || !$galleryId || $actionType===''){
            return $result;
        }

        $table = cg_paid_actions_table('entitlements');
        $availableSql = cg_paid_actions_available_sql();
        $environmentWhere = cg_paid_actions_environment_where_sql($environment);
        $result['local'] = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM($availableSql),0) FROM $table WHERE WpUserId = %d AND OriginGalleryID = %d AND ActionType = %s AND ScopeType = 'local' AND $environmentWhere",
            $userId,$galleryId,$actionType
        )));

        $result['shared'] = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM($availableSql),0) FROM $table WHERE WpUserId = %d AND ActionType = %s AND ScopeType = 'shared' AND $environmentWhere",
            $userId,$actionType
        )));
        $result['total'] = $result['local']+$result['shared'];
        return $result;
    }
}

if(!function_exists('cg_paid_actions_lock')){
    function cg_paid_actions_lock($userId,$actionType,$timeout = 5){
        global $wpdb;
        $name = 'cgpa_'.md5(absint($userId).'|'.cg_paid_actions_normalize_type($actionType));
        $locked = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,%d)',$name,absint($timeout)));
        return ((string)$locked==='1') ? $name : '';
    }
}

if(!function_exists('cg_paid_actions_unlock')){
    function cg_paid_actions_unlock($name){
        global $wpdb;
        if($name!==''){
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$name));
        }
    }
}

if(!function_exists('cg_paid_actions_insert_ledger')){
    function cg_paid_actions_insert_ledger($data){
        global $wpdb;

        $defaults = array(
            'WpUserId'=>0,'OriginGalleryID'=>0,'TargetGalleryID'=>0,'ActionType'=>'','ScopeType'=>'local',
            'Delta'=>0,'EventType'=>'','ParentOrder'=>0,'OrderItem'=>0,'EntitlementId'=>0,'ObjectId'=>0,'ReferenceLedgerId'=>0,
            'AdminUserId'=>0,'IdempotencyKey'=>'','MetaData'=>'','CreatedAt'=>cg_paid_actions_now()
        );
        $data = array_merge($defaults,$data);
        if(is_array($data['MetaData']) || is_object($data['MetaData'])){
            $data['MetaData'] = maybe_serialize($data['MetaData']);
        }
        return $wpdb->insert(
            cg_paid_actions_table('ledger'),
            $data,
            array('%d','%d','%d','%s','%s','%d','%s','%d','%d','%d','%d','%d','%d','%s','%s','%s')
        );
    }
}

if(!function_exists('cg_paid_actions_pending_reservations')){
    function &cg_paid_actions_pending_reservations(){
        static $reservations = array();
        return $reservations;
    }
}

if(!function_exists('cg_paid_actions_release_pending_reservations')){
    function cg_paid_actions_release_pending_reservations(){
        $reservations =& cg_paid_actions_pending_reservations();
        if(empty($reservations)){return;}
        foreach($reservations as $reservation){
            $consume = !empty($reservation['shutdownConsume']) ? absint($reservation['shutdownConsume']) : 0;
            $objectIds = !empty($reservation['shutdownObjectIds']) ? $reservation['shutdownObjectIds'] : array();
            cg_paid_actions_settle_reservation($reservation,$consume,$objectIds,true);
        }
        $reservations = array();
    }
}

if(!function_exists('cg_paid_actions_mark_reservation_progress')){
    function cg_paid_actions_mark_reservation_progress($reservation,$consumeQuantity,$objectIds = array()){
        if(!is_array($reservation) || empty($reservation['token'])){return false;}
        $pending =& cg_paid_actions_pending_reservations();
        if(empty($pending[$reservation['token']])){return false;}
        $pending[$reservation['token']]['shutdownConsume'] = min(absint($consumeQuantity),absint($reservation['quantity']));
        $pending[$reservation['token']]['shutdownObjectIds'] = array_values(array_filter(array_map('absint',(array)$objectIds)));
        return true;
    }
}

if(!function_exists('cg_paid_actions_get_preview_balance')){
    function cg_paid_actions_get_preview_balance($preview,$actionType){
        global $wpdb;
        $prefix = cg_paid_actions_preview_entitlement_prefix($preview,$actionType);
        $legacyKey = cg_paid_actions_preview_legacy_entitlement_key($preview,$actionType);
        $targetGalleryId = !empty($preview['targetGalleryId']) ? absint($preview['targetGalleryId']) : 0;
        if($prefix==='' || !$targetGalleryId){return array('local'=>0,'shared'=>0,'total'=>0,'consumed'=>0);}
        $table = cg_paid_actions_table('entitlements');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT OriginGalleryID, ScopeType, Consumed, ".cg_paid_actions_available_sql()." AS Available
             FROM $table
             WHERE WpUserId = %d AND IsTest = 1 AND GrantType = 'preview' AND (IdempotencyKey = %s OR IdempotencyKey LIKE %s)",
            absint($preview['userId']),$legacyKey,$wpdb->esc_like($prefix).'%'
        ));
        $balance = array('local'=>0,'shared'=>0,'total'=>0,'consumed'=>0);
        foreach($rows as $row){
            $available = max(0,intval($row->Available));
            if($row->ScopeType==='local'){
                if(absint($row->OriginGalleryID)!==$targetGalleryId){continue;}
            }elseif($row->ScopeType==='shared'){
                // Shared preview credits are valid for every participating target gallery.
            }else{
                continue;
            }
            $balance['consumed'] += max(0,intval($row->Consumed));
            if(!$available){continue;}
            if($row->ScopeType==='local'){$balance['local'] += $available;}
            if($row->ScopeType==='shared'){$balance['shared'] += $available;}
        }
        $balance['total'] = $balance['local']+$balance['shared'];
        return $balance;
    }
}

if(!function_exists('cg_paid_actions_get_preview_usage')){
    function cg_paid_actions_get_preview_usage($galleryId){
        global $wpdb;
        $galleryId = absint($galleryId);
        $result = array('upload'=>0,'vote'=>0,'comment'=>0);
        if(!$galleryId){return $result;}
        $ledger = cg_paid_actions_table('ledger');
        $entitlements = cg_paid_actions_table('entitlements');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT consume.ActionType, COUNT(*) AS UsedCount
             FROM $ledger consume
             INNER JOIN $entitlements entitlement ON entitlement.id = consume.EntitlementId AND entitlement.IsTest = 1 AND entitlement.GrantType = 'preview'
             LEFT JOIN $ledger reset_event ON reset_event.EventType = 'preview_reset' AND reset_event.ReferenceLedgerId = consume.id
             WHERE consume.TargetGalleryID = %d AND consume.EventType = 'consume' AND reset_event.id IS NULL
             GROUP BY consume.ActionType",
            $galleryId
        ));
        foreach($rows as $row){
            $type = cg_paid_actions_normalize_type($row->ActionType);
            if($type!==''){$result[$type] = absint($row->UsedCount);}
        }
        return $result;
    }
}

if(!function_exists('cg_paid_actions_get_preview_stored_counts')){
    function cg_paid_actions_get_preview_stored_counts($galleryId){
        global $wpdb;
        $galleryId = absint($galleryId);
        $result = array('upload'=>0,'vote'=>0,'comment'=>0);
        if(!$galleryId){return $result;}
        $result['upload'] = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}contest_gal1ery WHERE GalleryID = %d AND IsPaidActionPreview = 1",$galleryId
        )));
        $result['vote'] = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}contest_gal1ery_ip WHERE GalleryID = %d AND IsPaidActionPreview = 1",$galleryId
        )));
        $result['comment'] = absint($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}contest_gal1ery_comments WHERE GalleryID = %d AND IsPaidActionPreview = 1",$galleryId
        )));
        return $result;
    }
}

if(!function_exists('cg_paid_actions_grant_preview_credits')){
    function cg_paid_actions_grant_preview_credits($preview,$quantities,$requestKey){
        global $wpdb;
        if(empty($preview['userId']) || empty($preview['originGalleryId']) || empty($preview['targetGalleryId']) || empty($preview['token'])){
            return new WP_Error('cg_paid_action_preview_invalid',cg_paid_actions_t('PreviewEnded'));
        }
        $requestKey = preg_replace('/[^a-zA-Z0-9_-]/','',(string)$requestKey);
        if($requestKey===''){$requestKey = wp_generate_uuid4();}
        $entitlements = cg_paid_actions_table('entitlements');
        $settings = cg_paid_actions_get_settings($preview['targetGalleryId']);

        foreach(cg_paid_actions_types() as $actionType){
            $quantity = isset($quantities[$actionType]) ? min(10000,absint($quantities[$actionType])) : 0;
            if(!$quantity){continue;}
            if(cg_paid_actions_get_mode($settings,$actionType)===0){
				$typeLabelKeys = array('upload'=>'UploadsLower','vote'=>'VotesLower','comment'=>'CommentsLower');
				return new WP_Error('cg_paid_action_preview_not_active',cg_paid_actions_t('ActivateActionBeforeTestCredits',array('type'=>cg_paid_actions_t($typeLabelKeys[$actionType]))));
            }
            $entitlementKey = cg_paid_actions_preview_entitlement_key($preview,$actionType);
            $grantLedgerKey = 'preview-grant:'.cg_paid_actions_preview_token_hash($preview['token']).':'.$requestKey.':'.$actionType;
            $lock = cg_paid_actions_lock($preview['userId'],$actionType);
            if($lock===''){return new WP_Error('cg_paid_action_busy',cg_paid_actions_t('TestCreditsUpdating'));}
            cg_upload_job_db_transaction('START TRANSACTION');
            if($wpdb->get_var($wpdb->prepare("SELECT id FROM ".cg_paid_actions_table('ledger')." WHERE IdempotencyKey = %s LIMIT 1",$grantLedgerKey))){
                cg_upload_job_db_transaction('COMMIT');
                cg_paid_actions_unlock($lock);
                continue;
            }
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $entitlements WHERE WpUserId = %d AND IsTest = 1 AND GrantType = 'preview' AND IdempotencyKey = %s LIMIT 1 FOR UPDATE",$preview['userId'],$entitlementKey));
            if(empty($row)){
                $inserted = $wpdb->insert($entitlements,array(
                    'WpUserId'=>absint($preview['userId']),'OriginGalleryID'=>absint($preview['originGalleryId']),'ActionType'=>$actionType,
                    'ScopeType'=>!empty($preview['shared']) ? 'shared' : 'local','ParentOrder'=>0,'OrderItem'=>0,'GrantType'=>'preview',
                    'Granted'=>$quantity,'Reserved'=>0,'Consumed'=>0,'Revoked'=>0,'IsTest'=>1,'IdempotencyKey'=>$entitlementKey,
                    'CreatedAt'=>cg_paid_actions_now(),'UpdatedAt'=>cg_paid_actions_now()
                ),array('%d','%d','%s','%s','%d','%d','%s','%d','%d','%d','%d','%d','%s','%s','%s'));
                $entitlementId = $inserted ? absint($wpdb->insert_id) : 0;
            }else{
                $updated = $wpdb->query($wpdb->prepare("UPDATE $entitlements SET Granted = Granted + %d, UpdatedAt = %s WHERE id = %d",$quantity,cg_paid_actions_now(),$row->id));
                $entitlementId = ($updated===1) ? absint($row->id) : 0;
            }
            $ledgerInserted = $entitlementId ? cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$preview['userId'],'OriginGalleryID'=>$preview['originGalleryId'],'TargetGalleryID'=>$preview['targetGalleryId'],
                'ActionType'=>$actionType,'ScopeType'=>!empty($preview['shared']) ? 'shared' : 'local','Delta'=>$quantity,
                'EventType'=>'preview_grant','EntitlementId'=>$entitlementId,'IdempotencyKey'=>$grantLedgerKey,
                'MetaData'=>array('isPaidActionPreview'=>1)
            )) : false;
            if(!$entitlementId || $ledgerInserted!==1){
                cg_upload_job_db_transaction('ROLLBACK');
                cg_paid_actions_unlock($lock);
                return new WP_Error('cg_paid_action_preview_grant_failed',cg_paid_actions_t('DemoCreditsAddFailed'));
            }
            cg_upload_job_db_transaction('COMMIT');
            cg_paid_actions_unlock($lock);
        }
        return true;
    }
}

if(!function_exists('cg_paid_actions_reserve_preview')){
    function cg_paid_actions_reserve_preview($preview,$galleryId,$actionType,$quantity,$contextKey = ''){
        global $wpdb;
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $quantity = absint($quantity);
        if(!$galleryId || $actionType==='' || !$quantity || empty($preview['userId']) || empty($preview['token'])){
            return new WP_Error('cg_paid_action_preview_invalid',cg_paid_actions_t('PreviewEnded'));
        }
        $settings = cg_paid_actions_get_settings($galleryId);
        if(cg_paid_actions_get_mode($settings,$actionType)===0){return null;}
        if(!cg_paid_actions_lock_preview_gallery($galleryId,5)){
            return new WP_Error('cg_paid_action_busy',cg_paid_actions_t('PreviewActionsResetting'));
        }
        $lock = cg_paid_actions_lock($preview['userId'],$actionType);
        if($lock===''){
            cg_paid_actions_unlock_preview_gallery($galleryId);
            return new WP_Error('cg_paid_action_busy',cg_paid_actions_t('TestCreditsUpdating'));
        }
        $table = cg_paid_actions_table('entitlements');
        $prefix = cg_paid_actions_preview_entitlement_prefix($preview,$actionType);
        $legacyKey = cg_paid_actions_preview_legacy_entitlement_key($preview,$actionType);
        $availableSql = cg_paid_actions_available_sql();
        $allocations = array();
        $reservationToken = wp_generate_uuid4();
        $recovery = cg_paid_actions_recovery_start($preview['userId'],$actionType,$reservationToken);
        if(is_wp_error($recovery)){
            cg_paid_actions_unlock($lock);
            cg_paid_actions_unlock_preview_gallery($galleryId);
            return $recovery;
        }
        cg_paid_actions_recovery_transaction('START TRANSACTION',$recovery);
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE WpUserId = %d AND IsTest = 1 AND GrantType = 'preview' AND ActionType = %s
             AND (IdempotencyKey = %s OR IdempotencyKey LIKE %s)
             AND ((ScopeType = 'local' AND OriginGalleryID = %d) OR ScopeType = 'shared')
             AND $availableSql > 0
             ORDER BY CASE WHEN ScopeType = 'local' THEN 0 ELSE 1 END, id ASC
             FOR UPDATE",
            $preview['userId'],$actionType,$legacyKey,$wpdb->esc_like($prefix).'%', $galleryId
        ));
        $remaining = $quantity;
        foreach($rows as $row){
            if($remaining<=0){break;}
            $available = max(0,intval($row->Granted)-intval($row->Reserved)-intval($row->Consumed)-intval($row->Revoked));
            if(!$available){continue;}
            $take = min($available,$remaining);
            $updated = $wpdb->query($wpdb->prepare("UPDATE $table SET Reserved = Reserved + %d, UpdatedAt = %s WHERE id = %d",$take,cg_paid_actions_now(),$row->id));
            if($updated!==1){
                cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
                cg_paid_actions_unlock($lock);
                cg_paid_actions_unlock_preview_gallery($galleryId);
                return new WP_Error('cg_paid_action_reserve_failed',cg_paid_actions_t('DemoCreditsReserveFailed'));
            }
            $allocations[] = array(
                'entitlementId'=>absint($row->id),'quantity'=>$take,'originGalleryId'=>absint($row->OriginGalleryID),
                'scopeType'=>$row->ScopeType,'parentOrder'=>0,'orderItem'=>0,'grantType'=>'preview'
            );
            $remaining -= $take;
        }
        if($remaining>0){
            cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
            cg_paid_actions_unlock($lock);
            cg_paid_actions_unlock_preview_gallery($galleryId);
            return new WP_Error('cg_paid_action_credit_required',cg_paid_actions_t('NotEnoughDemoCredits'));
        }
        foreach($allocations as $allocation){
            $ledgerInserted = cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$preview['userId'],'OriginGalleryID'=>$allocation['originGalleryId'],'TargetGalleryID'=>$galleryId,
                'ActionType'=>$actionType,'ScopeType'=>$allocation['scopeType'],'Delta'=>-$allocation['quantity'],'EventType'=>'reserve',
                'EntitlementId'=>$allocation['entitlementId'],'IdempotencyKey'=>'reserve:'.$reservationToken.':'.$allocation['entitlementId'],
                'MetaData'=>array('context'=>$contextKey,'isPaidActionPreview'=>1)
            ));
            if($ledgerInserted!==1){
                cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
                cg_paid_actions_unlock($lock);
                cg_paid_actions_unlock_preview_gallery($galleryId);
                return new WP_Error('cg_paid_action_reserve_failed',cg_paid_actions_t('DemoCreditsReserveFailed'));
            }
        }
        if(cg_paid_actions_recovery_transaction('COMMIT',$recovery)===false){cg_paid_actions_unlock($lock);cg_paid_actions_unlock_preview_gallery($galleryId);return new WP_Error('cg_paid_action_save_failed',cg_paid_actions_t('CreditsReserveFailed'));}
        cg_paid_actions_unlock($lock);
        $reservation = array(
            'token'=>$reservationToken,'userId'=>absint($preview['userId']),'galleryId'=>$galleryId,'actionType'=>$actionType,
            'quantity'=>$quantity,'isPreview'=>1,'previewGalleryLock'=>1,
            'allocations'=>$allocations,'settled'=>false,'shutdownConsume'=>0,'shutdownObjectIds'=>array()
        );
        $pending =& cg_paid_actions_pending_reservations();
        $pending[$reservationToken] = $reservation;
        if(count($pending)===1){register_shutdown_function('cg_paid_actions_release_pending_reservations');}
        return $reservation;
    }
}

if(!function_exists('cg_paid_actions_maybe_reserve')){
    function cg_paid_actions_maybe_reserve($galleryId,$actionType,$quantity,$contextKey = ''){
        $previewToken = cg_paid_actions_request_preview_token();
        if($previewToken!==''){
            $preview = function_exists('cg_paid_actions_get_preview_context') ? cg_paid_actions_get_preview_context($galleryId) : false;
            if(!$preview){
                return new WP_Error('cg_paid_action_preview_expired',cg_paid_actions_t('PreviewEnded'));
            }
            $GLOBALS['cgPaidActionPreviewContext'] = $preview;
            if(cg_paid_actions_get_mode(cg_paid_actions_get_settings($galleryId),$actionType)===0){
                return null;
            }
            if(!empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox'){
                return cg_paid_actions_reserve(get_current_user_id(),$galleryId,$actionType,$quantity,$contextKey,true,'sandbox');
            }
            return cg_paid_actions_reserve_preview($preview,$galleryId,$actionType,$quantity,$contextKey);
        }
        $gate = cg_paid_actions_get_gate_status($galleryId,$actionType);
        if($gate['status']==='off' || $gate['status']==='draft'){
            return null;
        }
        if($gate['status']==='blocked'){
            return new WP_Error('cg_paid_action_unavailable',cg_paid_actions_t('ActionPurchaseUnavailable'));
        }
        if(!is_user_logged_in()){
            return new WP_Error('cg_paid_action_login_required',cg_paid_actions_t('SignInPurchaseCredits'));
        }
        return cg_paid_actions_reserve(get_current_user_id(),$galleryId,$actionType,$quantity,$contextKey);
    }
}

if(!function_exists('cg_paid_actions_release_reservation')){
    function cg_paid_actions_release_reservation($reservation){
        if(is_array($reservation) && !empty($reservation['token'])){
            return cg_paid_actions_settle_reservation($reservation,0);
        }
        return true;
    }
}

if(!function_exists('cg_paid_actions_get_frontend_error_balance')){
    function cg_paid_actions_get_frontend_error_balance($galleryIdUser,$actionType,$status){
        $emptyBalance = array('local'=>0,'shared'=>0,'total'=>0);
        $galleryId = absint($galleryIdUser);
        $actionType = cg_paid_actions_normalize_type($actionType);
        if($status!=='cg_paid_action_credit_required' || !$galleryId || $actionType===''){
            return false;
        }

        if(cg_paid_actions_request_preview_token()!==''){
            $preview = !empty($GLOBALS['cgPaidActionPreviewContext']) && is_array($GLOBALS['cgPaidActionPreviewContext']) ? $GLOBALS['cgPaidActionPreviewContext'] : false;
            if(!$preview && function_exists('cg_paid_actions_get_preview_context')){
                $preview = cg_paid_actions_get_preview_context($galleryId);
            }
            if($preview && !empty($preview['checkoutMode']) && $preview['checkoutMode']==='sandbox'){
                return cg_paid_actions_get_balance(get_current_user_id(),$galleryId,$actionType,cg_paid_actions_get_settings($galleryId),'sandbox');
            }
            if($preview){
                return cg_paid_actions_get_preview_balance($preview,$actionType);
            }
            return $emptyBalance;
        }

        if(is_user_logged_in()){
            return cg_paid_actions_get_balance(get_current_user_id(),$galleryId,$actionType,cg_paid_actions_get_settings($galleryId));
        }

        return $emptyBalance;
    }
}

if(!function_exists('cg_paid_actions_output_frontend_error')){
    function cg_paid_actions_output_frontend_error($galleryIdUser,$actionType,$error){
        $status = is_wp_error($error) ? $error->get_error_code() : 'cg_paid_action_credit_required';
        $message = is_wp_error($error) ? $error->get_error_message() : cg_paid_actions_t('PurchaseCreditContinue');
        $balance = cg_paid_actions_get_frontend_error_balance($galleryIdUser,$actionType,$status);
        ?>
        <script data-cg-processing="true">
            if(cgJsClass.gallery.paidActions && cgJsClass.gallery.paidActions.handleServerRequired){
                cgJsClass.gallery.paidActions.handleServerRequired(<?php echo wp_json_encode($galleryIdUser); ?>,<?php echo wp_json_encode($actionType); ?>,<?php echo wp_json_encode($status); ?>,<?php echo wp_json_encode($message); ?>,<?php echo wp_json_encode($balance); ?>);
            }
        </script>
        <?php
    }
}

if(!function_exists('cg_paid_actions_reserve')){
    function cg_paid_actions_reserve($userId,$galleryId,$actionType,$quantity,$contextKey = '',$allowSandboxDraft = false,$environment = 'live'){
        global $wpdb;

        $userId = absint($userId);
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $quantity = absint($quantity);
        $environment = cg_paid_actions_normalize_environment($environment);
        if(!$userId || !$galleryId || $actionType==='' || !$quantity){
            return new WP_Error('cg_paid_action_invalid_reservation',cg_paid_actions_t('InvalidCreditReservation'));
        }

        $gate = cg_paid_actions_get_gate_status($galleryId,$actionType);
        if($gate['status']==='blocked' && !$allowSandboxDraft){
            return new WP_Error('cg_paid_action_unavailable',cg_paid_actions_t('PaidActionUnavailable'));
        }
        if($gate['status']!=='live' && (!$allowSandboxDraft || cg_paid_actions_get_mode($gate['settings'],$actionType)===0)){
            return new WP_Error('cg_paid_action_not_live',cg_paid_actions_t('ActionDoesNotRequireCredits'));
        }

        $lock = cg_paid_actions_lock($userId,$actionType);
        if($lock===''){
            return new WP_Error('cg_paid_action_busy',cg_paid_actions_t('CreditsUpdating'));
        }

        $table = cg_paid_actions_table('entitlements');
        $availableSql = cg_paid_actions_available_sql();
        $environmentWhere = cg_paid_actions_environment_where_sql($environment);
        $reservationToken = wp_generate_uuid4();
        $allocations = array();

        $recovery = cg_paid_actions_recovery_start($userId,$actionType,$reservationToken);
        if(is_wp_error($recovery)){
            cg_paid_actions_unlock($lock);
            return $recovery;
        }
        cg_paid_actions_recovery_transaction('START TRANSACTION',$recovery);
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE WpUserId = %d AND ActionType = %s AND $environmentWhere
             AND ((ScopeType = 'local' AND OriginGalleryID = %d) OR ScopeType = 'shared')
             AND $availableSql > 0
             ORDER BY CASE WHEN ScopeType = 'local' THEN 0 WHEN OriginGalleryID = %d THEN 1 ELSE 2 END, id ASC
             FOR UPDATE",
            $userId,$actionType,$galleryId,$galleryId
        ));

        $remaining = $quantity;
        foreach($rows as $row){
            if($remaining<=0){break;}
            $available = max(0,intval($row->Granted)-intval($row->Reserved)-intval($row->Consumed)-intval($row->Revoked));
            if(!$available){continue;}
            $take = min($available,$remaining);
            $updated = $wpdb->query($wpdb->prepare(
                "UPDATE $table SET Reserved = Reserved + %d, UpdatedAt = %s WHERE id = %d",
                $take,cg_paid_actions_now(),$row->id
            ));
            if($updated!==1){
                cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
                cg_paid_actions_unlock($lock);
                return new WP_Error('cg_paid_action_reserve_failed',cg_paid_actions_t('CreditsReserveFailed'));
            }
            $allocations[] = array(
                'entitlementId'=>absint($row->id),
                'quantity'=>$take,
                'originGalleryId'=>absint($row->OriginGalleryID),
                'scopeType'=>$row->ScopeType,
                'parentOrder'=>absint($row->ParentOrder),
                'orderItem'=>absint($row->OrderItem),
                'grantType'=>sanitize_key($row->GrantType)
            );
            $ledgerInserted = cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$userId,'OriginGalleryID'=>$row->OriginGalleryID,'TargetGalleryID'=>$galleryId,
                'ActionType'=>$actionType,'ScopeType'=>$row->ScopeType,'Delta'=>-$take,'EventType'=>'reserve',
                'ParentOrder'=>$row->ParentOrder,'OrderItem'=>$row->OrderItem,'EntitlementId'=>$row->id,
                'IdempotencyKey'=>'reserve:'.$reservationToken.':'.$row->id,
                'MetaData'=>array('context'=>$contextKey)
            ));
			if($ledgerInserted!==1){
				cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
				cg_paid_actions_unlock($lock);
				return new WP_Error('cg_paid_action_audit_failed',cg_paid_actions_t('CreditsReserveFailed'));
			}
            $remaining -= $take;
        }

        if($remaining>0){
            cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
            cg_paid_actions_unlock($lock);
            return new WP_Error('cg_paid_action_credit_required',cg_paid_actions_t('NotEnoughCredits'));
        }

        if(cg_paid_actions_recovery_transaction('COMMIT',$recovery)===false){cg_paid_actions_unlock($lock);return new WP_Error('cg_paid_action_save_failed',cg_paid_actions_t('CreditsReserveFailed'));}
        cg_paid_actions_unlock($lock);
        $reservation = array(
            'token'=>$reservationToken,'userId'=>$userId,'galleryId'=>$galleryId,'actionType'=>$actionType,
            'quantity'=>$quantity,'allocations'=>$allocations,'settled'=>false,'shutdownConsume'=>0,'shutdownObjectIds'=>array()
        );
        $pending =& cg_paid_actions_pending_reservations();
        $pending[$reservationToken] = $reservation;
        if(count($pending)===1){
            register_shutdown_function('cg_paid_actions_release_pending_reservations');
        }
        return $reservation;
    }
}

if(!function_exists('cg_paid_actions_settle_reservation')){
    function cg_paid_actions_settle_reservation($reservation,$consumeQuantity,$objectIds = array(),$isShutdown = false){
        global $wpdb;

        if(empty($reservation['token']) || empty($reservation['userId']) || empty($reservation['actionType']) || empty($reservation['allocations'])){
            if(!empty($reservation['previewGalleryLock']) && !empty($reservation['galleryId'])){
                cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);
            }
            return false;
        }
        $consumeQuantity = min(absint($consumeQuantity),absint($reservation['quantity']));
        $lock = cg_paid_actions_lock($reservation['userId'],$reservation['actionType'],$isShutdown ? 1 : 5);
        if($lock===''){
            if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
            return false;
        }

        $table = cg_paid_actions_table('entitlements');
        $remainingConsume = $consumeQuantity;
        $objectIds = array_values(array_filter(array_map('absint',(array)$objectIds)));
        $objectIndex = 0;
        $recovery = cg_paid_actions_recovery_start($reservation['userId'],$reservation['actionType'],$reservation['token'],$objectIds);
        if(is_wp_error($recovery)){
            cg_paid_actions_unlock($lock);
            if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
            return false;
        }
        cg_paid_actions_recovery_transaction('START TRANSACTION',$recovery);

        foreach($reservation['allocations'] as $allocation){
            $reserved = absint($allocation['quantity']);
            $consume = min($reserved,$remainingConsume);
            $release = $reserved-$consume;
            $updated = $wpdb->query($wpdb->prepare(
                "UPDATE $table SET Reserved = GREATEST(Reserved - %d,0), Consumed = Consumed + %d, UpdatedAt = %s WHERE id = %d AND Reserved >= %d",
                $reserved,$consume,cg_paid_actions_now(),$allocation['entitlementId'],$reserved
            ));
            if($updated!==1){
                cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
                cg_paid_actions_unlock($lock);
                if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
                return false;
            }
            if($consume){
				for($consumeIndex=0;$consumeIndex<$consume;$consumeIndex++){
					$objectId = isset($objectIds[$objectIndex]) ? $objectIds[$objectIndex] : 0;
					$ledgerInserted = cg_paid_actions_insert_ledger(array(
						'WpUserId'=>$reservation['userId'],'OriginGalleryID'=>$allocation['originGalleryId'],'TargetGalleryID'=>$reservation['galleryId'],
						'ActionType'=>$reservation['actionType'],'ScopeType'=>$allocation['scopeType'],'Delta'=>0,'EventType'=>'consume',
						'ParentOrder'=>$allocation['parentOrder'],'OrderItem'=>$allocation['orderItem'],'EntitlementId'=>$allocation['entitlementId'],
						'ObjectId'=>$objectId,'IdempotencyKey'=>'consume:'.$reservation['token'].':'.$allocation['entitlementId'].':'.$consumeIndex,
						'MetaData'=>!empty($reservation['isPreview']) ? array('isPaidActionPreview'=>1) : ''
					));
					if($ledgerInserted!==1){
						cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
						cg_paid_actions_unlock($lock);
						if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
						return false;
					}
					if(!cg_paid_actions_update_object_snapshot(
						$reservation['galleryId'],$reservation['actionType'],$objectId,$allocation,!empty($reservation['isPreview'])
					)){
						cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
						cg_paid_actions_unlock($lock);
						if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
						return false;
					}
					$objectIndex++;
				}
            }
            if($release){
				$ledgerInserted = cg_paid_actions_insert_ledger(array(
                    'WpUserId'=>$reservation['userId'],'OriginGalleryID'=>$allocation['originGalleryId'],'TargetGalleryID'=>$reservation['galleryId'],
                    'ActionType'=>$reservation['actionType'],'ScopeType'=>$allocation['scopeType'],'Delta'=>$release,'EventType'=>'release',
                    'ParentOrder'=>$allocation['parentOrder'],'OrderItem'=>$allocation['orderItem'],'EntitlementId'=>$allocation['entitlementId'],
                    'IdempotencyKey'=>'release:'.$reservation['token'].':'.$allocation['entitlementId'],
                    'MetaData'=>!empty($reservation['isPreview']) ? array('isPaidActionPreview'=>1) : ''
                ));
				if($ledgerInserted!==1){
					cg_paid_actions_recovery_transaction('ROLLBACK',$recovery);
					cg_paid_actions_unlock($lock);
					if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
					return false;
				}
            }
            $remainingConsume -= $consume;
        }

        if(cg_paid_actions_recovery_transaction('COMMIT',$recovery)===false){cg_paid_actions_unlock($lock);if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}return false;}
        cg_paid_actions_unlock($lock);
        $pending =& cg_paid_actions_pending_reservations();
        unset($pending[$reservation['token']]);
        if(!empty($reservation['previewGalleryLock'])){cg_paid_actions_unlock_preview_gallery($reservation['galleryId']);}
        return true;
    }
}

if(!function_exists('cg_paid_actions_refund_preview_usage')){
    function cg_paid_actions_refund_preview_usage($galleryId,$adminUserId){
        global $wpdb;
        $galleryId = absint($galleryId);
        $adminUserId = absint($adminUserId);
        $refunded = array('upload'=>0,'vote'=>0,'comment'=>0);
        if(!$galleryId){return $refunded;}
        $ledger = cg_paid_actions_table('ledger');
        $entitlements = cg_paid_actions_table('entitlements');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT consume.*
             FROM $ledger consume
             INNER JOIN $entitlements entitlement ON entitlement.id = consume.EntitlementId AND entitlement.IsTest = 1 AND entitlement.GrantType = 'preview'
             LEFT JOIN $ledger reset_event ON reset_event.EventType = 'preview_reset' AND reset_event.ReferenceLedgerId = consume.id
             WHERE consume.TargetGalleryID = %d AND consume.EventType = 'consume' AND reset_event.id IS NULL
             ORDER BY consume.id ASC",
            $galleryId
        ));
        foreach($rows as $consume){
            $actionType = cg_paid_actions_normalize_type($consume->ActionType);
            if($actionType===''){continue;}
            $resetKey = 'preview-reset:'.absint($consume->id);
            cg_upload_job_db_transaction('START TRANSACTION');
            if($wpdb->get_var($wpdb->prepare("SELECT id FROM $ledger WHERE IdempotencyKey = %s LIMIT 1",$resetKey))){
                cg_upload_job_db_transaction('COMMIT');
                continue;
            }
            $entitlement = $wpdb->get_row($wpdb->prepare("SELECT * FROM $entitlements WHERE id = %d AND IsTest = 1 AND GrantType = 'preview' FOR UPDATE",$consume->EntitlementId));
            if(empty($entitlement)){
                cg_upload_job_db_transaction('ROLLBACK');
                return new WP_Error('cg_paid_action_preview_refund_failed',cg_paid_actions_t('PreviewResetFailed'));
            }
            $creditToRestore = intval($entitlement->Consumed)>0 ? 1 : 0;
            if($creditToRestore){
                $updated = $wpdb->query($wpdb->prepare("UPDATE $entitlements SET Consumed = GREATEST(Consumed - 1,0), UpdatedAt = %s WHERE id = %d",cg_paid_actions_now(),$entitlement->id));
                if($updated!==1){
                    cg_upload_job_db_transaction('ROLLBACK');
                    return new WP_Error('cg_paid_action_preview_refund_failed',cg_paid_actions_t('PreviewResetFailed'));
                }
            }
            $inserted = cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$consume->WpUserId,'OriginGalleryID'=>$consume->OriginGalleryID,'TargetGalleryID'=>$galleryId,
                'ActionType'=>$actionType,'ScopeType'=>$consume->ScopeType,'Delta'=>$creditToRestore,'EventType'=>'preview_reset',
                'EntitlementId'=>$consume->EntitlementId,'ObjectId'=>$consume->ObjectId,'ReferenceLedgerId'=>$consume->id,
                'AdminUserId'=>$adminUserId,'IdempotencyKey'=>$resetKey,
                'MetaData'=>array('isPaidActionPreview'=>1,'removedObjectId'=>absint($consume->ObjectId))
            ));
            if($inserted!==1){
                cg_upload_job_db_transaction('ROLLBACK');
                return new WP_Error('cg_paid_action_preview_refund_failed',cg_paid_actions_t('PreviewResetFailed'));
            }
            cg_upload_job_db_transaction('COMMIT');
            $refunded[$actionType]++;
        }
        return $refunded;
    }
}

if(!function_exists('cg_paid_actions_sandbox_order_transient_key')){
    function cg_paid_actions_sandbox_order_transient_key($orderId){
        return 'cg_paid_sandbox_order_'.absint($orderId);
    }
}

if(!function_exists('cg_paid_actions_mark_sandbox_order')){
    function cg_paid_actions_mark_sandbox_order($orderId,$context){
        $orderId = absint($orderId);
        if(!$orderId || empty($context) || empty($context['checkoutMode']) || $context['checkoutMode']!=='sandbox'){
            return false;
        }
        set_transient(cg_paid_actions_sandbox_order_transient_key($orderId),array(
            'userId'=>absint($context['userId']),
            'originGalleryId'=>absint($context['originGalleryId']),
            'created'=>time()
        ),30*DAY_IN_SECONDS);
        return true;
    }
}

if(!function_exists('cg_paid_actions_grant_order')){
    function cg_paid_actions_grant_order($orderId){
        global $wpdb;

        $orderId = absint($orderId);
        $orders = cg_paid_actions_table('orders');
        $items = cg_paid_actions_table('order_items');
        $entitlements = cg_paid_actions_table('entitlements');
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $orders WHERE id = %d LIMIT 1",$orderId));
        if(empty($order) || empty($order->IsFullPaid) || empty($order->WpUserId)){
            return false;
        }
        $isSandboxOrder = !empty($order->IsTest);
        $sandboxMarker = $isSandboxOrder ? get_transient(cg_paid_actions_sandbox_order_transient_key($orderId)) : false;
        if($isSandboxOrder && (empty($sandboxMarker) || absint($sandboxMarker['userId'])!==absint($order->WpUserId))){return false;}
        $grantType = $isSandboxOrder ? 'sandbox_purchase' : 'purchase';

        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $items WHERE ParentOrder = %d AND PaidActionType <> ''",$orderId));
        foreach($rows as $row){
            $actionType = cg_paid_actions_normalize_type($row->PaidActionType);
            $quantity = absint($row->Units);
            if($actionType==='' || !$quantity){continue;}
            $key = $grantType.':item:'.absint($row->id);
            if($wpdb->get_var($wpdb->prepare("SELECT id FROM $entitlements WHERE IdempotencyKey = %s LIMIT 1",$key))){continue;}

            $scope = $row->PaidActionScope==='shared' ? 'shared' : 'local';
            $lock = cg_paid_actions_lock($order->WpUserId,$actionType);
            if($lock===''){return false;}
            cg_upload_job_db_transaction('START TRANSACTION');
			if($wpdb->get_var($wpdb->prepare("SELECT id FROM $entitlements WHERE IdempotencyKey = %s LIMIT 1 FOR UPDATE",$key))){
				cg_upload_job_db_transaction('COMMIT');
				cg_paid_actions_unlock($lock);
				continue;
			}
            $inserted = $wpdb->insert($entitlements,array(
                'WpUserId'=>absint($order->WpUserId),'OriginGalleryID'=>absint($row->GalleryID),'ActionType'=>$actionType,
                'ScopeType'=>$scope,'ParentOrder'=>$orderId,'OrderItem'=>absint($row->id),'GrantType'=>$grantType,
                'Granted'=>$quantity,'Reserved'=>0,'Consumed'=>0,'Revoked'=>0,'IsTest'=>$isSandboxOrder ? 1 : 0,'IdempotencyKey'=>$key,
                'CreatedAt'=>cg_paid_actions_now(),'UpdatedAt'=>cg_paid_actions_now()
            ),array('%d','%d','%s','%s','%d','%d','%s','%d','%d','%d','%d','%d','%s','%s','%s'));
            if(!$inserted){
                cg_upload_job_db_transaction('ROLLBACK');
                cg_paid_actions_unlock($lock);
                return false;
            }
            $entitlementId = $wpdb->insert_id;
            $ledgerInserted = cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$order->WpUserId,'OriginGalleryID'=>$row->GalleryID,'TargetGalleryID'=>$row->GalleryID,
                'ActionType'=>$actionType,'ScopeType'=>$scope,'Delta'=>$quantity,'EventType'=>$isSandboxOrder ? 'sandbox_purchase' : 'purchase',
                'ParentOrder'=>$orderId,'OrderItem'=>$row->id,'EntitlementId'=>$entitlementId,
                'IdempotencyKey'=>'ledger:'.$key
            ));
			if($ledgerInserted!==1){
				cg_upload_job_db_transaction('ROLLBACK');
				cg_paid_actions_unlock($lock);
				return false;
			}
            cg_upload_job_db_transaction('COMMIT');
            cg_paid_actions_unlock($lock);
        }
        if($isSandboxOrder){delete_transient(cg_paid_actions_sandbox_order_transient_key($orderId));}
        return true;
    }
}

if(!function_exists('cg_paid_actions_revoke_refunded_order')){
    function cg_paid_actions_revoke_refunded_order($orderId){
        global $wpdb;

        $orderId = absint($orderId);
        $table = cg_paid_actions_table('entitlements');
        $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE ParentOrder = %d AND GrantType IN ('purchase','sandbox_purchase')",$orderId));
        foreach($rows as $row){
            $lock = cg_paid_actions_lock($row->WpUserId,$row->ActionType);
            if($lock===''){continue;}
            cg_upload_job_db_transaction('START TRANSACTION');
            $currentRow = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d FOR UPDATE",$row->id));
            $available = !empty($currentRow) ? max(0,intval($currentRow->Granted)-intval($currentRow->Reserved)-intval($currentRow->Consumed)-intval($currentRow->Revoked)) : 0;
            if(!$available){
                cg_upload_job_db_transaction('COMMIT');
                cg_paid_actions_unlock($lock);
                continue;
            }
			$updated = $wpdb->query($wpdb->prepare("UPDATE $table SET Revoked = Revoked + %d, UpdatedAt = %s WHERE id = %d",$available,cg_paid_actions_now(),$row->id));
			$ledgerInserted = $updated===1 ? cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$row->WpUserId,'OriginGalleryID'=>$row->OriginGalleryID,'TargetGalleryID'=>$row->OriginGalleryID,
                'ActionType'=>$row->ActionType,'ScopeType'=>$row->ScopeType,'Delta'=>-$available,'EventType'=>'refund',
                'ParentOrder'=>$orderId,'OrderItem'=>$row->OrderItem,'EntitlementId'=>$row->id,
                'IdempotencyKey'=>'refund:entitlement:'.$row->id
			)) : false;
			if($updated!==1 || $ledgerInserted!==1){
				cg_upload_job_db_transaction('ROLLBACK');
				cg_paid_actions_unlock($lock);
				continue;
			}
            cg_upload_job_db_transaction('COMMIT');
            cg_paid_actions_unlock($lock);
        }
        return true;
    }
}

if(!function_exists('cg_paid_actions_get_order_credit_rows')){
    function cg_paid_actions_get_order_credit_rows($orderId){
        global $wpdb;

        $orderId = absint($orderId);
        $orders = cg_paid_actions_table('orders');
        $items = cg_paid_actions_table('order_items');
        $entitlements = cg_paid_actions_table('entitlements');
        $order = $wpdb->get_row($wpdb->prepare("SELECT WpUserId, IsTest FROM $orders WHERE id = %d LIMIT 1",$orderId));
        $environment = !empty($order->IsTest) ? 'sandbox' : 'live';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT oi.*, e.id AS EntitlementId, e.GrantType, e.Granted, e.Reserved, e.Consumed, e.Revoked
             FROM $items oi LEFT JOIN $entitlements e ON e.OrderItem = oi.id AND e.GrantType IN ('purchase','sandbox_purchase')
             WHERE oi.ParentOrder = %d AND oi.PaidActionType <> '' ORDER BY oi.id ASC",
            $orderId
        ));
        foreach($rows as $row){
            $row->CurrentScopeBalance = cg_paid_actions_get_scope_balance(
                !empty($order->WpUserId) ? $order->WpUserId : 0,
                $row->GalleryID,$row->PaidActionType,$row->PaidActionScope,$environment
            );
        }
        return $rows;
    }
}

if(!function_exists('cg_paid_actions_set_scope_balance')){
    function cg_paid_actions_set_scope_balance($userId,$galleryId,$actionType,$scopeType,$newBalance,$parentOrder,$adminUserId,$environment = 'live'){
        global $wpdb;

        $userId = absint($userId);
        $galleryId = absint($galleryId);
        $actionType = cg_paid_actions_normalize_type($actionType);
        $scopeType = $scopeType==='shared' ? 'shared' : 'local';
        $newBalance = max(0,absint($newBalance));
        $environment = cg_paid_actions_normalize_environment($environment);
        if(!$userId || !$galleryId || $actionType===''){
            return new WP_Error('cg_paid_action_invalid_adjustment','Invalid credit adjustment.');
        }

        $lock = cg_paid_actions_lock($userId,$actionType);
        if($lock===''){
            return new WP_Error('cg_paid_action_busy','Credits are being updated. Please try again.');
        }
        $table = cg_paid_actions_table('entitlements');
        $whereScope = $scopeType==='shared' ? "ScopeType = 'shared'" : $wpdb->prepare("ScopeType = 'local' AND OriginGalleryID = %d",$galleryId);
        $environmentWhere = cg_paid_actions_environment_where_sql($environment);
        $reserved = absint($wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(Reserved),0) FROM $table WHERE WpUserId = %d AND ActionType = %s AND $whereScope AND $environmentWhere",$userId,$actionType)));
        if($reserved){
            cg_paid_actions_unlock($lock);
            return new WP_Error('cg_paid_action_busy','A user action is currently reserving these credits.');
        }

        $current = cg_paid_actions_get_scope_balance($userId,$galleryId,$actionType,$scopeType,$environment);
        $delta = $newBalance-$current;
        if($delta===0){
            cg_paid_actions_unlock($lock);
            return $newBalance;
        }

        cg_upload_job_db_transaction('START TRANSACTION');
        $adjustmentToken = wp_generate_uuid4();
        if($delta>0){
            $key = 'admin:grant:'.$adjustmentToken;
			$grantType = $environment==='sandbox' ? 'sandbox_admin' : 'admin';
			$inserted = $wpdb->insert($table,array(
                'WpUserId'=>$userId,'OriginGalleryID'=>$galleryId,'ActionType'=>$actionType,'ScopeType'=>$scopeType,
                'ParentOrder'=>absint($parentOrder),'OrderItem'=>0,'GrantType'=>$grantType,'Granted'=>$delta,'Reserved'=>0,
                'Consumed'=>0,'Revoked'=>0,'IsTest'=>$environment==='sandbox' ? 1 : 0,'IdempotencyKey'=>$key,'CreatedAt'=>cg_paid_actions_now(),'UpdatedAt'=>cg_paid_actions_now()
            ),array('%d','%d','%s','%s','%d','%d','%s','%d','%d','%d','%d','%d','%s','%s','%s'));
			if($inserted!==1){
				cg_upload_job_db_transaction('ROLLBACK');
				cg_paid_actions_unlock($lock);
				return new WP_Error('cg_paid_action_adjustment_failed','The requested balance could not be applied.');
			}
            $entitlementId = $wpdb->insert_id;
			$ledgerInserted = cg_paid_actions_insert_ledger(array(
                'WpUserId'=>$userId,'OriginGalleryID'=>$galleryId,'TargetGalleryID'=>$galleryId,'ActionType'=>$actionType,
                'ScopeType'=>$scopeType,'Delta'=>$delta,'EventType'=>'admin_grant','ParentOrder'=>$parentOrder,
                'EntitlementId'=>$entitlementId,'AdminUserId'=>$adminUserId,'IdempotencyKey'=>'ledger:'.$key,
                'MetaData'=>array('balanceBefore'=>$current,'balanceAfter'=>$newBalance)
            ));
			if($ledgerInserted!==1){
				cg_upload_job_db_transaction('ROLLBACK');
				cg_paid_actions_unlock($lock);
				return new WP_Error('cg_paid_action_audit_failed','The requested balance could not be audited.');
			}
        }else{
            $toRevoke = absint($delta);
            $availableSql = cg_paid_actions_available_sql();
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE WpUserId = %d AND ActionType = %s AND $whereScope AND $environmentWhere AND $availableSql > 0
                 ORDER BY CASE WHEN GrantType IN ('admin','sandbox_admin') THEN 0 ELSE 1 END, id DESC FOR UPDATE",
                $userId,$actionType
            ));
            foreach($rows as $row){
                if($toRevoke<=0){break;}
                $available = max(0,intval($row->Granted)-intval($row->Consumed)-intval($row->Revoked));
                $take = min($available,$toRevoke);
                if(!$take){continue;}
				$updated = $wpdb->query($wpdb->prepare("UPDATE $table SET Revoked = Revoked + %d, UpdatedAt = %s WHERE id = %d",$take,cg_paid_actions_now(),$row->id));
				$ledgerInserted = $updated===1 ? cg_paid_actions_insert_ledger(array(
                    'WpUserId'=>$userId,'OriginGalleryID'=>$row->OriginGalleryID,'TargetGalleryID'=>$galleryId,'ActionType'=>$actionType,
                    'ScopeType'=>$scopeType,'Delta'=>-$take,'EventType'=>'admin_revoke','ParentOrder'=>$parentOrder,
                    'OrderItem'=>$row->OrderItem,'EntitlementId'=>$row->id,'AdminUserId'=>$adminUserId,
                    'IdempotencyKey'=>'admin:revoke:'.$adjustmentToken.':'.$row->id,
                    'MetaData'=>array('balanceBefore'=>$current,'balanceAfter'=>$newBalance)
				)) : false;
				if($updated!==1 || $ledgerInserted!==1){
					cg_upload_job_db_transaction('ROLLBACK');
					cg_paid_actions_unlock($lock);
					return new WP_Error('cg_paid_action_audit_failed','The requested balance could not be audited.');
				}
                $toRevoke -= $take;
            }
            if($toRevoke>0){
                cg_upload_job_db_transaction('ROLLBACK');
                cg_paid_actions_unlock($lock);
                return new WP_Error('cg_paid_action_adjustment_failed','The requested balance could not be applied.');
            }
        }

        cg_upload_job_db_transaction('COMMIT');
        cg_paid_actions_unlock($lock);
        return cg_paid_actions_get_scope_balance($userId,$galleryId,$actionType,$scopeType,$environment);
    }
}
