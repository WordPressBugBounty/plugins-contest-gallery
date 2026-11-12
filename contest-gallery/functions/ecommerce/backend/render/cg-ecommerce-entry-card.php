<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_ecommerce_entry_card')){
    function cg_ecommerce_entry_card($entryId,$saleType,$price,$options,$canDownload = true,$ecommerceEntryId = 0,$isFree = false,$draft = array(),$regularPrice = 0){
        $isSelling = $saleType !== '';
        if(!$isSelling && !empty($draft)){ $saleType = $draft['SaleType']; }
        $selectedType = ($isSelling || !empty($draft)) ? $saleType : ($canDownload ? 'download' : 'shipping');
        echo '<div class="cg_entry_sale_card" data-cg-entry-id="'.absint($entryId).'" data-cg-default-sale-type="'.($canDownload ? 'download' : 'shipping').'">';
        echo '<div class="cg_entry_sale_heading"><span class="dashicons dashicons-tag" aria-hidden="true"></span><h3>'.($isSelling ? 'Your product' : 'Sell this entry').'</h3></div>';
        echo '<div class="cg_entry_sale_types" role="list" aria-label="Sale type">';
        $types = array('download'=>'Download','shipping'=>'Shipping','service'=>'Service');
        if($saleType === 'upload'){$types['upload'] = 'Upload';}
        foreach($types as $type=>$label){
            $isCurrentType = $isSelling && $selectedType === $type;
            echo '<span role="listitem" class="cg_entry_sale_type'.($isCurrentType ? ' cg_entry_sale_type_current' : '').'"'.($isCurrentType ? ' aria-current="true"' : '').'>'.esc_html($label).'</span>';
        }
        echo '</div><button type="button" class="cg_entry_sale_setup cg_sale_settings">'.($isSelling ? 'Edit sale' : 'Set up sale').'<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span></button>';
        echo '</div>';
    }
}
