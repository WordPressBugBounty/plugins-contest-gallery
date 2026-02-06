<?php

if(!function_exists('cg_download_keys_file_will_be_deleted')){
	function cg_download_keys_file_will_be_deleted(){
		echo "<div id='cgDownloadKeyFilesWillBeDeleted' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgDownloadKeyFilesWillBeDeletedClose' class='cg_message_close cg_high_overlay'></span><p style='margin-top:30px;'>Download keys file will be deleted when deactivating</p></div>";
	}
}

if(!function_exists('cg_service_keys_file_will_be_deleted')){
	function cg_service_keys_file_will_be_deleted(){
		echo "<div id='cgServiceKeyFilesWillBeDeleted' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgServiceKeyFilesWillBeDeletedClose' class='cg_message_close cg_high_overlay'></span><p style='margin-top:30px;'>Service keys file will be deleted when deactivating</p></div>";
	}
}

if(!function_exists('cg_download_and_service_keys_files_will_be_deleted')){
	function cg_download_and_service_keys_files_will_be_deleted(){
		echo "<div id='cgDownloadAndServiceKeyFilesWillBeDeleted' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgDownloadAndServiceKeyFilesWillBeDeletedClose' class='cg_message_close cg_high_overlay'></span><p style='margin-top:30px;'>Download and service keys file will be deleted when deactivating</p></div>";
	}
}

if(!function_exists('cg_sell_ecommerce_entry_can_not_be_deleted')){
	function cg_sell_ecommerce_entry_can_not_be_deleted(){
		echo "<div id='cgEcommerceEntryCanNotBeDeleted' class='cg_hide cg_height_auto cg_backend_action_container   cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgEcommerceEntryCanNotBeDeletedClose' class='cg_message_close '></span><p style='margin-top:30px;'>Entries which are activated for selling can not be deleted.<br>Deactivate entries for sale in \"Sales settings\" area first.</p></div>";
	}
}

if(!function_exists('cg_sell_ecommerce_entry_can_not_be_moved')){
	function cg_sell_ecommerce_entry_can_not_be_moved(){
		echo "<div id='cgEcommerceEntryCanNotBeMoved' class='cg_hide cg_height_auto cg_backend_action_container   cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgEcommerceEntryCanNotBeDeletedClose' class='cg_message_close '></span><p style='margin-top:30px;'>Entries which are activated for selling can not be moved.<br>Deactivate entries for sale in \"Sales settings\" area first.</p></div>";
	}
}

if(!function_exists('cg_sell_ecommerce_download_can_not_be_removed')){
	function cg_sell_ecommerce_download_can_not_be_removed(){
		echo "<div id='cgEcommerceFileCanNotBeDeleted' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgEcommerceFileCanNotBeDeletedClose' class='cg_message_close cg_high_overlay'></span><p style='margin-top:30px;'>Files which are set as download for selling can't be removed.<br>Uncheck file as download for selling in \"Sales settings\" area of that entry, or deactivate the whole entry for selling, first.</p></div>";
	}
}

if(!function_exists('cg_sell_ecommerce_container_warnings')){
	function cg_sell_ecommerce_container_warnings(){
		echo "<div id='cgNoFilesForSaleDownload' class='cg_hide cg_height_auto cg_backend_action_container  cg_backend_action_container_high_overlay cg_overflow_y_hidden' style='z-index: 9999999;width: fit-content;'><span id='cgNoFilesForSaleDownloadClose' class='cg_message_close cg_high_overlay'></span><p style='margin-top:30px;'>No download files for selling selected</p></div>";
	}
}

if(!function_exists('cg_sell_ecommerce_container')){
    function cg_sell_ecommerce_container($GalleryID, $payPalOptions, $cgProFalse){

	    $wp_upload_dir = wp_upload_dir();
        $CurrencyShort = $payPalOptions->CurrencyShort;
        $CurrencyPosition = $payPalOptions->CurrencyPosition;
        $PriceDivider = $payPalOptions->PriceDivider;
        $Tax = $payPalOptions->TaxPercentageDefault;
        $Shipping = $payPalOptions->ShippingGross;
        $siteURL = get_site_url();

	    $cgProFalseSellContainer = '';
		if($cgProFalse){
			$cgProFalseSellContainer = 'cg-pro-false-sell-container';
		}

// Sell PayPal container START
        echo "<div id='cgSellContainer'  class='cg_backend_action_container $cgProFalseSellContainer cg_hide'>
<span class='cg_message_close'></span>";



        echo '<div id="cgSellFormLoaderContainer" class="cg_hide">';
            echo '<div id="cgSellFormLoader" class="cg-lds-dual-ring-gallery-hide" style="height: 300px;margin: 0;padding-top: 80px;max-height: 220px;"></div>';
        echo '</div>';

        echo <<<HEREDOC
   <div class="cg_shortcode_conf_title_container cg_hide" style="margin-top: 25px;margin-bottom: 15px;"><div class="cg_shortcode_conf_title_main">Voting gallery</div><div class="cg_shortcode_conf_title_sub">[cg_gallery id="56"]</div></div>
HEREDOC;

        // form start has to be done after get data!!!
        echo "<form id='cgSellForm' action='?page=".cg_get_version()."/index.php' method='POST'>";
        echo "<input type='hidden' name='cgSellContainer[GalleryID]' value='$GalleryID'>";
// !IMPORTANT Has to be here for action call!!!!
        echo "<input type='hidden' name='action' value='post_cg_set_for_paypal_sell'>";
        echo "<input type='hidden' id='TaxDefault'  value='$Tax'>";

        echo '<div id="cgProEcomFalseContainer"  style="border-radius:8px;" >';

        echo "<div class='cg_main_options' style='margin-bottom: 0;margin-top: 40px; box-shadow: unset;'>";

        echo <<<HEREDOC
    <div class='cg_view_options_row' >
    
                    <div  id="cgSellBackendImageContainerOption" class='cg_view_option_33_percent  cg_border_border_top_left_radius_8_px cg_view_option   cg_entry_page_description cg_border_right_none  cg_border_bottom_none '>
            <div class="cg_view_option_image_display" id="cgSellBackendImage" style="margin-left: auto; ">
                    <div id='cgSellBackendImageContainer'>
                    </div>
                </div>
        </div>
        <div  class='cg_view_option cg_view_option_67_percent cg_border_left_none cg_entry_page_description  cg_border_bottom_none cg_border_border_top_right_radius_8_px' id="cgActivateSaleOption">
            <div class='cg_view_option_title  cg_view_option_title_full_width' style="transform: translateX(-4px);">
                <p>
                    Activate selling for ID (<span id="cgSellBackendImageId"></span>)
                </p>
            </div>
            <div class="cg_view_option_checkbox">
                  <input id="cgActivateSale"  type="checkbox"  name="cgSellContainer[isActivateSale]"  >
            </div>
        </div>
    </div>
HEREDOC;


        $currencies_array = cg_get_ecommerce_currencies_array();
        $CurrencyLong = '';
        foreach ($currencies_array as $arrayElement){
            if($CurrencyShort == $arrayElement['short']){
                $CurrencyLong = $arrayElement['long'];
                $CurrencySymbol = $arrayElement['symbol'];
            }
        }

        echo "<input type='hidden' id='DefaultShipping' value='$Shipping' >";
        echo "<input type='hidden' id='CurrencyShort' value='$CurrencyShort' >";
        echo "<input type='hidden' id='CurrencySymbol' value='$CurrencySymbol' >";
        echo "<input type='hidden' id='cgCurrencySelectPosition' value='$CurrencyPosition' >";
        echo "<input type='hidden' id='cgCurrencySymbol' value='$CurrencySymbol' >";
        echo "<input type='hidden' id='cgPriceDivider' value='$PriceDivider' >";
        echo "<input type='hidden' id='CurrencyPosition' value='$CurrencyPosition' >";

        echo <<<HEREDOC
    <div class='cg_view_options_row' >
        <div  class='cg_sale_conf cg_view_option cg_entry_page_description cg_view_option_full_width cg_border_bottom_none '>
            <div class='cg_view_option_title  cg_view_option_title_full_width'>
                <p>
                    Currency configuration<br>
                    <span class="cg_view_option_title_note">
                        <span style="margin-right:10px;"><b>Price decimal separator:</b> $PriceDivider</span>
                        <span style="margin-right:10px;"><b>Currency:</b> $CurrencyLong</span>
                        <span><b>Currency position:</b> $CurrencyPosition</span>
                    </span>
                </p>
            </div>
        </div>
    </div>
HEREDOC;

        echo <<<HEREDOC
    <div class='cg_view_options_row' >
        <div  class='cg_view_option cg_sale_conf  cg_entry_page_description cg_view_option_full_width cg_border_top_none cg_border_bottom_none '>
            <div class='cg_view_option_title  cg_view_option_title_full_width'>
                <p>
                    Price configuration
                </p>
            </div>
            <div class="cg_view_option_input" style="width: 40%;margin-left: auto; margin-right: auto;">
                    <input type="text" id="cgSellPrice" name='cgSellContainer[PriceResult]'  class="cg-long-input cg_currency_input cg_text_align_center" value='38.99' maxlength="20">
            </div>
        </div>
    </div>
HEREDOC;

        echo <<<HEREDOC
<div class='cg_view_options_row '  >
                <div class='cg_view_option cg_sale_conf cg_view_option_full_width cg_border_top_bottom_none' >
                    <div class='cg_view_option_title cg_hide'>
                    <p>Sale type</p>
                    </div>
                    <div class='cg_view_option_radio_multiple cg_align_items_baseline'>
                        <div class='cg_view_option_radio_multiple_container cg_one_third_width cg_flex_flow_column' id="SaleType_shipping_option_container">
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_shipping_option">
                                <b>Shipping</b><p style="height:10px;">&nbsp;</p>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_shipping cg_view_option_radio_multiple_input_field"  value="shipping">
                            </div>
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_shipping_option">
                                <p style="height:10px;">&nbsp;</p><span class="cg_view_option_title_note"><b>NOTE:</b> the default shipping<br>which is set in ecommerce options<br>is currently <span id="defaultShippingPriceText" style="font-weight: bold;" ></span> <input type="text" id="defaultShippingPriceInput" class="cg_hide"  /> </span>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container cg_one_third_width cg_flex_flow_column' id="SaleType_download_option_container" style="height:100%;">
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_download_option">
                                <b>Download</b><p style="height:10px;">&nbsp;</p>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_download cg_view_option_radio_multiple_input_field"  value="download"/>
                            </div>
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_download_option">
                                <p style="height:10px;">&nbsp;</p><span class="cg_view_option_title_note" style="visibility: hidden;"><b>NOTE:</b> required placeholder</span><br><span class="cg_view_option_title_note cg_hide" id="cgDownloadKeysFileRemoveNote">
                                <a  id="cgRemoveDownloadKeysFile" href="" class="cg_event_link">Remove</a>
                                uploaded file<br><a href="" id="cgShowDownloadKeysFile"  class="cg_event_link" >filename</a>
                                <input type="hidden" id="cgRemoveDownloadKeysFileInput" name="cgSellContainer[RemoveDownloadKeysFile]" value="0" />
                                <input type="hidden" id="cgDownloadKeysFileRemoveWasClicked"  value="0" />
                                </span>
                                <span class="cg_view_option_title_note cg_hide" id="cgDownloadKeysFileRemovedNote">
                                    Uploaded file will be removed, "Save changes" to complete
                                </span>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container cg_one_third_width cg_flex_flow_column cg_flex_flow_column' id="SaleType_service_option_container" style="height:100%;" >
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_service_option">
                                <b>Service</b><p style="height:10px;">&nbsp;</p>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_service cg_view_option_radio_multiple_input_field"  value="service">
                            </div>
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_service_option">
                                <p style="height:10px;">&nbsp;</p><span class="cg_view_option_title_note"><b>NOTE:</b> some service you can describe</span><br>
                                <span class="cg_view_option_title_note cg_hide" id="cgServiceKeysFileRemoveNote">
                                <a  id="cgRemoveServiceKeysFile" href="" class="cg_event_link">Remove</a> uploaded file<br><a href="" id="cgShowServiceKeysFile"  class="cg_event_link" >filename</a>
                                <input type="hidden" id="cgRemoveServiceKeysFileInput" name="cgSellContainer[RemoveServiceKeysFile]" value="0" />
                                <input type="hidden" id="cgServiceKeysFileRemoveWasClicked"  value="0" />
                                </span>
                                <span class="cg_view_option_title_note cg_hide" id="cgServiceKeysFileRemovedNote">
                                    Uploaded file will be removed, "Save changes" to complete
                                </span>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container cg_one_third_width cg_flex_flow_column' id="SaleType_upload_option_container" style="height:100%;" >
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_upload_option">
                                <b>Upload</b><p style="height:10px;">&nbsp;</p>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_upload cg_view_option_radio_multiple_input_field"  value="upload">
                            </div>
                            <div class='cg_view_option_radio_multiple_title' id="SaleType_upload_option">
                                <p style="height:10px;">&nbsp;</p><span class="cg_view_option_title_note"><b>NOTE:</b> charge users for upload</span>
                            </div>
                        </div>
                </div>
            </div>
</div> 
HEREDOC;
$cgContactFormShortcodeConfigurationAreaLinkPart = '?page='.cg_get_version().'/index.php&edit_options=true&cg_go_to=cgContactFormShortcodeConfigurationArea';
echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide' id="DownloadContainerRow">
                  <div  class='cg_view_option $cgProFalse cg_key_upload_option cg_sale_conf cg_view_option_full_width cg_border_bottom_none  cg_border_top_none' >
                    <div class='cg_view_option_title  cg_view_option_title_full_width' >
                        <p>
                            Upload "Download keys"<br><span class="cg_view_option_title_note"><b>NOTE:</b> customer is able to get key for the download after purchase<br>
                                Download <a target="_blank" href="$siteURL?contest-gallery-download-keys-example=true">example CSV file</a></span>
                        </p>
                    </div>
                    <div class="cg_view_option_input" >
                          <input id="DownloadKey" class="cg_key_upload"  type="file" name="cgSellContainer[DownloadKey]" style="width: fit-content;font-size: 13px;margin-bottom: 10px;border-radius: unset !important;"  />
                    </div>
                    <p class="cg_error cg_hide cg_key_upload_error_not_csv" >Not a CSV file</p>
                    <p class="cg_error cg_hide cg_key_upload_error_to_large" >CSV file to large (64000Byte = 64kb)</p>
                </div>
        </div>
        <div class='cg_view_options_row cg_hide' id="ServiceContainerRow">
                  <div  class='cg_view_option $cgProFalse cg_key_upload_option cg_sale_conf cg_view_option_full_width   cg_border_bottom_none  cg_border_top_none' >
                    <div class='cg_view_option_title  cg_view_option_title_full_width' >
                        <p>
                            Upload "Service key"<br><span class="cg_view_option_title_note">
                                <b>NOTE:</b> customer is able to get key for the service after purchase<br>
                                Download <a target="_blank" href="$siteURL?contest-gallery-download-keys-example=true">example CSV file</a>
                            </span>
                        </p>
                    </div>
                    <div class="cg_view_option_input" >
                          <input id="ServiceKey"  class="cg_key_upload"   type="file" name="cgSellContainer[ServiceKey]" style="width: fit-content;font-size: 13px;margin-bottom: 10px;border-radius: unset !important;"  >
                    </div>
                    <p class="cg_error cg_hide cg_key_upload_error_not_csv" >Not a CSV file</p>
                    <p class="cg_error cg_hide cg_key_upload_error_to_large" >CSV file to large (64000Byte = 64kb) <span ></span></p>
                </div>
        </div>
        <div class='cg_view_options_row cg_hide' id="UploadContainerRow">
                  <div  class='cg_view_option $cgProFalse cg_key_upload_option cg_sale_conf cg_view_option_full_width   cg_border_bottom_none  cg_border_top_none cg_view_option_flex_flow_column' >
                    <div class='cg_view_option_title  cg_view_option_title_full_width' >
                        <p>
                            <span class="cg_view_option_title_note">Select which gallery upload form user should use after purchase<br>After purchase <b>user will be forwarded to order page where the upload form will be visible and useable</b><br>
                                <b class="cg_color_red">NOTE:</b> <b>settings of the cg_users_upload shortcode upload form will be valid</b>
                            </span>
                        </p>
                    </div>
                    <div class="cg_view_option_select" >
                    	<select id="UploadGallery" name="cgSellContainer[UploadGallery]"></select>
                    </div>
                    <div class='cg_view_option_title  cg_view_option_title_full_width' style="margin-top:5px;">
                        <p>
                            <span class="cg_view_option_title_note">
                                <b class="cg_color_red">NOTE:</b> <b>reload this page if you just created a gallery in another tab, to be able to select that gallery</b><br><br>
                            	<a class=" cg_no_outline_and_shadow_on_focus" id="cgContactFormShortcodeConfigurationAreaLink" href="$cgContactFormShortcodeConfigurationAreaLinkPart&option_id=$GalleryID" target="_blank">Go to cg_users_upload form settings</a>
                            </span>
                            <span class="cg_hide"><a href="$cgContactFormShortcodeConfigurationAreaLinkPart"  id="cgContactFormShortcodeConfigurationAreaLinkPart" ></a></span>
                        </p>
                    </div>                   
                    <div class='cg_view_option_title  cg_view_option_title_full_width' style="margin-top:5px;">
                        <p>
                            <span class="cg_view_option_title_note">Select how many uploads (entries) a user can upload after purchase
                            </span>
                        </p>
                    </div>
                    <div class="cg_view_option_select" >
                    	<select id="MaxUploads" name="cgSellContainer[MaxUploads]" style="margin-top: -5px;"></select>
                    </div>
                </div>
                <div class='cg_view_option $cgProFalse cg_view_option_full_width cg_border_top_none' id="wp-cgAllUploadsUsedText-wrap-Container">
		            <div class='cg_view_option_title'>
		                <p>Text if all uploads were used<br><span class="cg_view_option_title_note"><b>NOTE:</b> "Confirmation text after upload" or "Forward to another URL after upload"<br>are still valid if configured for cg_users_upload shortcode form<br><b>NOTE:</b> If all uploads were used this text will be shown instead of "Confirmation after upload" configuration of upload form</span></p>
		            </div>
		            <div class='cg_view_option_html cg_view_option_input_full_width' >
		                 <div class='cg-wp-editor-container' data-wp-editor-id="cgAllUploadsUsedText"  >
		                    <textarea class='cg-wp-editor-template cg_view_option_textarea'   id='cgAllUploadsUsedText' name="cgSellContainer[AllUploadsUsedText]"></textarea>
		                </div>
		            </div>
        		</div>
        </div>
        <div class='cg_view_options_row cg_hide' id="ShippingContainerRow">
                  <div  class='cg_view_option cg_sale_conf cg_border_bottom_none cg_view_option_two_third_width cg_view_option_two_third_width_checkbox cg_border_top_none cg_border_right_none' id="IsAlternativeShippingOption" >
                    <div class='cg_view_option_title  cg_view_option_title_full_width' style="margin-top: 35px;">
                        <p>
                            Is alternative shipping<br><span class="cg_view_option_title_note"><b>NOTE:</b> if checked then shipping for this product<br>will be calculated alternative<br>shipping and alternative shipping always uses the default tax</span>
                        </p>
                    </div>
                    <div class="cg_view_option_checkbox" id="IsAlternativeShippingCheckbox">
                          <input id="IsAlternativeShipping"  type="checkbox"  name="cgSellContainer[IsAlternativeShipping]"  >
                    </div>
                </div>
                <div class='cg_view_option cg_view_option_flex_flow_column cg_border_left_none cg_border_bottom_none cg_border_top_none cg_view_option_one_third_width' id="ShippingContainer" >
                    <div class='cg_view_option_title cg_view_option_title_full_width'  style="margin-top: 35px;">
                        <p>Alternative shipping costs<br><span class="cg_view_option_title_note"><b>NOTE:</b> if <b>"0"</b> then <b>"Free shipping"</b></span></p>
                    </div>
                    <div class='cg_view_option_input'>
                        <input type="text" name="cgSellContainer[Shipping]" id="Shipping" maxlength="20" class="cg_currency_input cg_currency_input_shipping cg_text_align_center" style="width: 30%;margin-left: auto; margin-right: auto;"  >
                    </div>
                </div>
        </div>
HEREDOC;

        echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide' id="SaleAmountContainer">
            <div class="cg_view_option cg_border_top_none  cg_sale_conf cg_view_option_50_percent cg_border_right_none cg_view_option_flex_flow_column cg_border_bottom_none" >
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select min amount to buy</p>
                </div>
                <div class="cg_view_option_select">
                    <select name='cgSellContainer[SaleAmountMin]' id='SaleAmountMin' class="SaleAmount">
HEREDOC;
        for($i=1;$i<=999;$i++){
            echo "<option value='$i'>$i</option>";
        }
        echo <<<HEREDOC
                    </select>
                </div>
            </div>
            <div class="cg_view_option cg_border_left_none cg_border_top_none cg_sale_conf cg_view_option_50_percent cg_view_option_flex_flow_column cg_border_bottom_none" >
                <div class="cg_view_option_title cg_view_option_title_full_width">
                    <p>Select max amount to buy</p>
                </div>
                <div class="cg_view_option_select">
                    <select name='cgSellContainer[SaleAmountMax]' id='SaleAmountMax' class="SaleAmount">
HEREDOC;
        for($i=1;$i<=999;$i++){
            echo "<option value='$i'>$i</option>";
        }
        echo <<<HEREDOC
                    </select>
                </div>
            </div>
        </div>
HEREDOC;

        echo <<<HEREDOC
<div class='cg_view_options_row '  id="TaxRadioContainerRow">
                <div class='cg_view_option cg_sale_conf cg_view_option_full_width cg_border_bottom_none'>
                    <div class='cg_view_option_title'>
                    <p>Tax <br><span class="cg_view_option_title_note"><b>NOTE:</b> the default tax<br>which is set in ecommerce options<br>is currently <span id="defaultTaxText" style="font-weight: bold;" ></span> <input type="text" id="defaultTaxInput" class="cg_hide"  /> </span></p>
                    </div>
                    <div class='cg_view_option_radio_multiple'>
                        <div class='cg_view_option_radio_multiple_container' id="TaxFreeRadioContainerParent">
                            <div class='cg_view_option_radio_multiple_title' id="Tax_free_option">
                                No tax
                            </div>
                            <div class='cg_view_option_radio_multiple_input' id="TaxFreeRadioContainer">
                                <input type="radio" id="TaxFree" name="cgSellContainer[HasTax]" class="Tax_radio Tax_free cg_view_option_radio_multiple_input_field"  value="0"/>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container' id="TaxRequiredRadioContainerParent">
                            <div class='cg_view_option_radio_multiple_title' id="Tax_required_option">
                                Tax
                            </div>
                            <div class='cg_view_option_radio_multiple_input' id="TaxRequiredRadioContainer">
                                <input type="radio" id="TaxRequired"  name="cgSellContainer[HasTax]" class="Tax_radio Tax_required cg_view_option_radio_multiple_input_field"  value="1">
                            </div>
                        </div>
                </div>
            </div>
</div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_hide' id="TaxContainerRow">
                <div class='cg_view_option cg_sale_conf cg_view_option_full_width cg_border_top_none cg_border_bottom_none ' id="TaxContainer" >
                    <div class='cg_view_option_title'>
                        <p>Tax percentage</p>
                    </div>
                    <div class='cg_view_option_input' style="width: 20%;margin-left: auto; margin-right: auto;">
                        <input type="text" name="cgSellContainer[Tax]" id="Tax" maxlength="20" class="cg_currency_input cg_currency_input_tax cg_text_align_center" >
                    </div>
                </div>
        </div>
HEREDOC;
echo <<<HEREDOC
        <div id='cgSellSelectFilesForSale' class='cg_hide cg_sale_conf cg_download_sale $cgProFalse'>
                <div class='cg_sell_title' id='cgSellTitlePreview'>Select download files for selling</div>
                <p style="margin:10px auto -10px;"><b>NOTE:</b> images can be watermarked</p>
                <p style="margin:10px auto;"><b>NOTE:</b> selected files will be moved to inaccessible folder in <br><b>.../wp-content/uploads/contest-gallery/...</b><br> after purchase a customer will be able to download selected files for sale</p>
                     <p style="margin:10px auto;max-width: 600px;"><b class="cg_color_red">NOTE:</b> if this entry was sold the selected download files for selling will be downloadable for the customer on the order summary page. This entry will be connected to purchased order.<br>If you remove this entry after purchase then customers will  not be able to download the files on the order summary page. If you add further files to this entry the customers will be able to download the further files on order sammary page. <br><b>In short:</b> this entry and all connected files to this entry will be connected to the purchased orders of this entry.</p>
                <div id='cgSellSelectFilesForSaleSelectContainer'>
				</div>
        </div>
HEREDOC;

echo <<<HEREDOC
        <div id='cgSellWatermarkPreview' class='cg_hide cg_download_sale'>
            <div id='cgSellWatermarkPreviewImage' style='user-select:none;' >
                <div class='cg_sell_title' id='cgSellTitlePreview'>Watermark image files for selling</div>
                <p style="margin:10px auto;"><b>NOTE:</b> after purchase a customer will be able to download original files</p>
                 <div id="cgSellPreviewAndLoader" style="height: 300px;display: flex;margin-bottom: 15px;">
                <div id="cgSellPreviewContainerLoader" class="cg-lds-dual-ring-gallery-hide" style="height: 300px;margin: 0 auto;padding-top: 80px;max-height: 220px;"></div>
                <div id='cgSellPreviewContainer' class='cg_hide'>
                <div id='cgSellArrowLeft' class='cgSellArrowLeft cg_hide cg_hide_override'>
                </div>
                <div id='cgSellArrowRight' class='cgSellArrowRight cg_hide cg_hide_override'>
                </div>
                </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
<div class='cg_sell_conf_container'>
    <div class='cg_view_options_row' id="cgWatermarkSettingsRow">
HEREDOC;
        echo <<<HEREDOC
 	<div  class='cg_view_option cg_border_none cg_hide_override' id="cgWatermarkImage"  style="margin-bottom:20px;">
            <div class='cg_view_option_title cg_view_option_title_full_width cg_border_none' >
                <p style="text-align:left;">
                    Watermark image
                </p>
            </div>
            <div  style="justify-content:left;" class="cg_view_option_checkbox" id="cgWatermarkImageCheckboxContainer"  >
                  <input id="cgWatermarkImageCheckbox"  type="checkbox" style="width: 100%;"    >
            </div>
    </div>
    <div class="cg_view_option cg_sale_conf cg_view_option_flex_flow_column   cg_border_none" style="margin-bottom:25px;">
            <div class="cg_view_option_title cg_view_option_title_full_width ">
                <p style="text-align:left;">Watermark position</p>
            </div>
            <div  style="justify-content:left;" class="cg_view_option_select">
                <select name='cgSellContainer[WatermarkPosition]' id='cgWatermarkSelectPosition' style="width: 100%;" >
                    <option value='center' >Center</option>
                    <option value='upperLeft' >Top left</option>
                    <option value='upperRight' >Top right</option>
                    <option value='lowerRight' >Bottom right</option>
                    <option value='lowerLeft' >Bottom left</option>
                </select>
            </div>
        </div>
HEREDOC;
        echo <<<HEREDOC
    <div class="cg_view_option cg_sale_conf  cg_view_option_flex_flow_column    cg_border_none" style="margin-bottom: 25px;">
            <div class="cg_view_option_title cg_view_option_title_full_width ">
                <p style="text-align:left;">Watermark title</p>
            </div>
            <div  style="justify-content:left;" class='cg_view_option_input'>
                <input id="cgWatermarkInputTitle" type="text" name="cgSellContainer[WatermarkTitle]" value="Contest Gallery" maxlength="40" style="width: 100%;" >
            </div>
        </div>
    <div class="cg_view_option cg_sale_conf  cg_view_option_flex_flow_column cg_border_none">
            <div class="cg_view_option_title cg_view_option_title_full_width">
                <p style="text-align: left;">Watermark size</p>
            </div>
            <div class="cg_view_option_select" style="justify-content: left;">
                <select name='cgSellContainer[WatermarkSize]' id='cgWatermarkSelectSize' style="width: 100%;" >
                    <option value='8' >XXS</option>
                    <option value='16' >XS</option>
                    <option value='32' >S</option>
                    <option value='64' >M</option>
                    <option value='128' >L</option>
                    <option value='256' >XL</option>
                    <option value='512' >XXL</option>
                </select>
            </div>
        </div>
HEREDOC;
        echo <<<HEREDOC
        </div>
       
    </div>
HEREDOC;
        echo "</div>";

        echo "</div>";

        echo "<div class='cg_main_options cg_hide' style='margin-bottom: 0;margin-top: 0;'>";

        echo <<<HEREDOC
    <div class='cg_view_options_row' >
        <div  class='cg_view_option cg_sale_conf  cg_entry_page_description cg_view_option_full_width cg_border_bottom_none '>
            <div class='cg_view_option_title  cg_view_option_title_full_width'>
                <p>
                    Product title
                </p>
            </div>
            <div class="cg_view_option_input">
                    <input type="text" name="cgSellContainer[SaleTitle]" id="cgSaleTitle" class="cg-long-input" maxlength="1000">
            </div>
        </div>
    </div>
HEREDOC;


        echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_sale_conf  cg_view_option_full_width cg_border_top_none' id="wp-cgSaleDescription-wrap-Container">
            <div class='cg_view_option_title'>
                <p>Product description</p>
            </div>
            <div class='cg_view_option_html cg_view_option_input_full_width' >
                 <div class='cg-wp-editor-container' data-wp-editor-id="cgSaleDescription"  >
                    <textarea class='cg-wp-editor-template cg_view_option_textarea' id='cgSaleDescription' name="cgSellContainer[SaleDescription]"></textarea>
                </div>
            </div>
        </div>
    </div>
HEREDOC;
        echo "</div>";

        //echo "<div class='cg_sell_title'>Product title</div>";
        //echo "<div class='cg_sell_conf'>";
        //echo "<input type='text' id='cgSaleTitle' name='SaleTitle'  value='' style='width: 75% !important;'  >";
        //echo "</div>";

        //echo "<div class='cg_sell_title'>Product description</div>";
        //echo "<div class='cg_sell_conf'>";
        //echo "<textarea id='cgSaleDescription' name='SaleDescription' rows='10' style='width: 75% !important;font-size:20px;'  ></textarea>";
        //echo "</div>";

        echo "<input type='hidden' name='cgSellContainer[realId]' id='cgRealId' value='' >";
        echo "<input type='hidden' name='isDeactivateSale' id='cgIsDeactivateSale' value='0' >";

        //echo "<hr>";

        echo '<div id="cgSellContainerSubmitButtonContainer" >
            <input type="submit" id="cgSetForSell" class="cg_backend_button_gallery_action" value="Save changes">
        </div>';
        echo '</div>';

        echo "</form>";
        echo "</div>";

// Sell PayPal container END

    }
}

?>