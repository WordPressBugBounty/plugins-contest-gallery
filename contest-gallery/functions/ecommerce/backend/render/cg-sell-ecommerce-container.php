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
    function cg_sell_ecommerce_container($GalleryID, $payPalOptions, $cgProFalse, $galleryVersion = 0){

	    $wp_upload_dir = wp_upload_dir();
        $CurrencyShort = $payPalOptions->CurrencyShort;
        $CurrencyPosition = $payPalOptions->CurrencyPosition;
        $PriceDivider = $payPalOptions->PriceDivider;
        $Tax = $payPalOptions->TaxPercentageDefault;
        $Shipping = $payPalOptions->ShippingGross;
        $siteURL = get_site_url();
	    $isLegacyUploadSaleExistingOnly = (float)$galleryVersion >= 33;
	    $legacyUploadSaleOptionClass = $isLegacyUploadSaleExistingOnly ? ' cg_hide' : '';
	    $saleTypeOptionsClass = $isLegacyUploadSaleExistingOnly ? ' cg_sale_type_options_three_columns' : '';
	    $legacyUploadSaleTitle = $isLegacyUploadSaleExistingOnly ? 'Legacy upload package' : 'Upload';
	    $legacyUploadSaleNote = $isLegacyUploadSaleExistingOnly ? '<b>LEGACY:</b> available only for an existing upload package. Use Pay for uploads for new sales.' : '<b>NOTE:</b> charge users for upload';

	    $cgProFalseSellContainer = '';
		if(!cg_ecommerce_products_pro_available()){
			$cgProFalseSellContainer = 'cg-pro-false-sell-container';
		}

// Sell PayPal container START
        echo "<div id='cgSellContainer'  class='cg_backend_action_container $cgProFalseSellContainer cg_hide'>
<span class='cg_message_close cg_message_close_extra_processing'></span>";



        echo '<div id="cgSellFormLoaderContainer" class="cg_hide" role="status" aria-live="polite" aria-busy="true" aria-label="Loading sales settings">';
            echo '<div id="cgSellFormLoader" class="cg_sell_form_skeleton">';
                echo '<div class="cg_sell_form_skeleton_header">';
                    echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_preview"></div>';
                    echo '<div class="cg_sell_form_skeleton_header_content">';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_title"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_control"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_line cg_sell_form_skeleton_line_short"></div>';
                    echo '</div>';
                echo '</div>';
                echo '<div class="cg_sell_form_skeleton_options">';
                    echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_option"></div>';
                    echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_option"></div>';
                    echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_option"></div>';
                echo '</div>';
                echo '<div class="cg_sell_form_skeleton_content">';
                    echo '<div class="cg_sell_form_skeleton_panel">';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_line cg_sell_form_skeleton_line_wide"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_field"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_field"></div>';
                    echo '</div>';
                    echo '<div class="cg_sell_form_skeleton_panel">';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_line"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_field"></div>';
                        echo '<div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_line cg_sell_form_skeleton_line_short"></div>';
                    echo '</div>';
                echo '</div>';
                echo '<div class="cg_sell_form_skeleton_footer"><div class="cg_skeleton_loader_on_page_load cg_sell_form_skeleton_button"></div></div>';
            echo '</div>';
        echo '</div>';

        echo <<<HEREDOC
   <div class="cg_shortcode_conf_title_container cg_hide" style="margin-top: 25px;margin-bottom: 15px;"><div class="cg_shortcode_conf_title_main">Voting gallery</div><div class="cg_shortcode_conf_title_sub">[cg_gallery id="56"]</div></div>
HEREDOC;

	        // form start has to be done after get data!!!
	        echo "<form id='cgSellForm' action='?page=".cg_get_version()."/index.php' method='POST'>";
	        echo "<input type='hidden' name='cgSellContainer[GalleryID]' value='$GalleryID'>";
	        echo "<input type='hidden' name='cgGalleryHash' value='".md5(wp_salt( 'auth').'---cngl1---'.$GalleryID)."'>";
	// !IMPORTANT Has to be here for action call!!!!
	        echo "<input type='hidden' name='action' value='post_cg_set_for_paypal_sell'>";
	        echo "<input type='hidden' id='TaxDefault'  value='$Tax'>";

        echo '<div id="cgProEcomFalseContainer"  style="border-radius:8px;" >';

        echo "<div class='cg_main_options cg_sell_options'>";
        echo '<div class="cg_sell_header">';
        echo '<div id="cgSellBackendImageContainerOption"><div id="cgSellBackendImageContainer"></div></div>';
        echo '<div class="cg_sell_header_text"><h2>Sales settings</h2><span>Entry ID <span id="cgSellBackendImageId"></span></span></div>';
        echo '</div>';
        echo '<p id="cgSellActionError" class="cg_error cg_hide" role="alert"></p>';

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

			if(function_exists('cg_render_nginx_upload_protection_notice') && function_exists('cg_get_nginx_upload_deny_rule')){
				cg_render_nginx_upload_protection_notice(
					'Using Nginx? Add this server rule to protect ecommerce files.',
					'Selling works without this rule, but on Nginx invoices, logs, sold downloads and ecommerce data are only protected when the server configuration blocks this path. Ask your server admin to add this rule inside the matching server block and reload Nginx.',
					cg_get_nginx_upload_deny_rule('contest-gallery/ecommerce'),
					'cg_ecommerce_nginx_notice cg_ecommerce_global_nginx_notice'
				);
			}

	        echo <<<HEREDOC
<div class='cg_view_options_row cg_sell_type_row'>
                <div class='cg_view_option cg_sale_conf cg_view_option_full_width cg_border_top_bottom_none' >
                    <div class='cg_view_option_title cg_view_option_title_full_width'>
                    <p>Sale type</p>
                    </div>
                    <div class='cg_view_option_radio_multiple cg_sale_type_options$saleTypeOptionsClass'>
                        <div class='cg_view_option_radio_multiple_container cg_sale_type_option cg_flex_flow_column' id="SaleType_download_option_container">
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_heading' id="SaleType_download_option">
                                <b>Download</b>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_download cg_view_option_radio_multiple_input_field"  value="download"/>
                            </div>
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_note' id="SaleType_download_option">
                                <span class="cg_view_option_title_note">Sell selected files.</span><br><span class="cg_view_option_title_note cg_hide" id="cgDownloadKeysFileRemoveNote">
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
                        <div class='cg_view_option_radio_multiple_container cg_sale_type_option cg_flex_flow_column' id="SaleType_shipping_option_container">
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_heading' id="SaleType_shipping_option">
                                <b>Shipping</b>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_shipping cg_view_option_radio_multiple_input_field"  value="shipping">
                            </div>
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_note' id="SaleType_shipping_option">
                                <span class="cg_view_option_title_note">Default shipping: <span id="defaultShippingPriceText" style="font-weight: bold;" ></span> <input type="text" id="defaultShippingPriceInput" class="cg_hide"  /> </span>
                            </div>
                        </div>
                        <div class='cg_view_option_radio_multiple_container cg_sale_type_option cg_flex_flow_column' id="SaleType_service_option_container">
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_heading' id="SaleType_service_option">
                                <b>Service</b>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_service cg_view_option_radio_multiple_input_field"  value="service">
                            </div>
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_note' id="SaleType_service_option">
                                <span class="cg_view_option_title_note">Offer a service.</span><br>
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
                        <div class='cg_view_option_radio_multiple_container cg_sale_type_option cg_flex_flow_column$legacyUploadSaleOptionClass' id="SaleType_upload_option_container" data-cg-existing-only="{$isLegacyUploadSaleExistingOnly}">
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_heading' id="SaleType_upload_option">
                                <b>$legacyUploadSaleTitle</b>
                            </div>
                            <div class='cg_view_option_radio_multiple_input'>
                                <input type="radio" name="cgSellContainer[SaleType]" class="SaleType SaleType_upload cg_view_option_radio_multiple_input_field"  value="upload">
                            </div>
                            <div class='cg_view_option_radio_multiple_title cg_sale_type_option_note' id="SaleType_upload_option">
                                <span class="cg_view_option_title_note">$legacyUploadSaleNote</span>
                            </div>
                        </div>
                </div>
            </div>
</div> 
HEREDOC;
        $saleCurrencyLabel = esc_html($CurrencyLong);
        $salePriceDividerLabel = esc_html($PriceDivider);
        $saleCurrencyPositionLabel = esc_html(ucfirst($CurrencyPosition));
        $saleCurrencyOptionsUrl = esc_url(add_query_arg(array(
            'page' => cg_get_version().'/index.php',
            'edit_options' => 'true',
            'option_id' => absint($GalleryID),
            'cg_edit_ecommerce' => 'true'
        ), admin_url('admin.php')));
        $saleMembersOptionsUrl = esc_url(add_query_arg(array(
            'page' => cg_get_version().'/index.php', 'edit_options' => 'true',
            'option_id' => absint($GalleryID), 'cg_edit_ecommerce' => 'true',
            'cg_go_to' => 'RegUserPurchaseOnlyContainer'
        ),admin_url('admin.php')));
        echo <<<HEREDOC
    <div class="cg_sell_pricing">
        <div class="cg_sell_pricing_cards">
            <div class="cg_sell_pricing_card cg_sell_price_card cg_sale_conf">
                <div class="cg_sell_price_fields">
                    <div class="cg_sell_price_field">
                        <label class="cg_sell_pricing_label" id="cgSellMainPriceLabel" for="cgSellPrice">Price</label>
                        <input type="text" id="cgSellPrice" name="cgSellContainer[PriceResult]" class="cg-long-input cg_currency_input cg_text_align_center" inputmode="decimal" value="" maxlength="20">
                        <strong id="cgSellFreeValue" class="cg_hide">Free</strong>
                    </div>
                    <div id="cgSellReducedPriceField" class="cg_sell_price_field cg_hide">
                        <label class="cg_sell_pricing_label" for="cgSellReducedPrice">Reduced price</label>
                        <input type="text" id="cgSellReducedPrice" name="cgSellContainer[ReducedPrice]" class="cg-long-input cg_currency_input cg_text_align_center" inputmode="decimal" value="" maxlength="20">
                    </div>
                </div>
                <div class="cg_sell_price_options">
                    <label class="cg_offer_free_label"><input type="checkbox" role="switch" id="cgOfferForFree" name="cgSellContainer[IsFree]" value="1">Offer for free</label>
                    <label class="cg_offer_free_label cg_offer_reduced_label"><input type="checkbox" role="switch" id="cgOfferReducedPrice" name="cgSellContainer[HasReducedPrice]" value="1" aria-controls="cgSellReducedPriceField">Add discount</label>
                </div>
            </div>
            <div class="cg_sell_pricing_card cg_sell_currency_card">
                <span class="cg_sell_pricing_label">Currency</span>
                <strong class="cg_sell_pricing_value">$saleCurrencyLabel</strong>
            </div>
            <div class="cg_sell_pricing_card">
                <span class="cg_sell_pricing_label">Decimal separator</span>
                <strong class="cg_sell_pricing_value">$salePriceDividerLabel</strong>
            </div>
            <div class="cg_sell_pricing_card">
                <span class="cg_sell_pricing_label">Currency position</span>
                <strong class="cg_sell_pricing_value">$saleCurrencyPositionLabel</strong>
            </div>
        </div>
        <p id="cgSellPriceError" class="cg_error cg_hide" role="alert">Enter a price greater than 0 or choose Offer for free.</p>
        <p id="cgSellFreeMembersNote" class="cg_sell_currency_options_note cg_hide">Want to offer this only to members? <a href="$saleMembersOptionsUrl" target="_blank" rel="noopener">Allow only registered users to buy.</a></p>
        <p class="cg_sell_currency_options_note">Change currency, decimal separator and currency position in <a href="$saleCurrencyOptionsUrl" target="_blank" rel="noopener">General ecommerce options</a> (opens in a new tab).<br>After saving changes, reload this page and reopen Sales settings.</p>
    </div>
HEREDOC;

$cgContactFormShortcodeConfigurationAreaLinkPart = '?page='.cg_get_version().'/index.php&edit_options=true&cg_go_to=cgContactFormShortcodeConfigurationArea';
$saleKeysExampleUrl = esc_url(add_query_arg('contest-gallery-download-keys-example', 'true', $siteURL));
echo <<<HEREDOC
        <div class="cg_view_options_row cg_sale_keys_row cg_hide" id="DownloadContainerRow">
                <div class="cg_view_option cg_key_upload_option cg_sale_conf cg_sale_keys_card">
                    <div class="cg_sale_keys_intro">
                        <label class="cg_sale_keys_label" for="DownloadKey">Upload download keys</label>
                        <p>Customers receive a download key after purchase.</p>
                        <a href="$saleKeysExampleUrl" target="_blank" rel="noopener">Download example CSV</a>
                    </div>
                    <div class="cg_sale_keys_file">
                        <input id="DownloadKey" class="cg_key_upload" type="file" name="cgSellContainer[DownloadKey]" accept=".csv,text/csv" aria-describedby="cgDownloadKeyFormat cgDownloadKeyTrialNote" />
                        <p class="cg_sale_keys_format" id="cgDownloadKeyFormat">CSV file &middot; Max. 64 KB</p>
                        <p class="cg_sale_test_keys_note" id="cgDownloadKeyTrialNote">Use test keys for trial orders.</p>
                    </div>
                    <p class="cg_error cg_hide cg_key_upload_error_not_csv" role="alert">Please select a CSV file.</p>
                    <p class="cg_error cg_hide cg_key_upload_error_to_large" role="alert">The CSV file must be 64 KB or smaller.</p>
                </div>
        </div>
        <div class="cg_view_options_row cg_sale_keys_row cg_hide" id="ServiceContainerRow">
                <div class="cg_view_option cg_key_upload_option cg_sale_conf cg_sale_keys_card">
                    <div class="cg_sale_keys_intro">
                        <label class="cg_sale_keys_label" for="ServiceKey">Upload service keys</label>
                        <p>Customers receive a service key after purchase.</p>
                        <a href="$saleKeysExampleUrl" target="_blank" rel="noopener">Download example CSV</a>
                    </div>
                    <div class="cg_sale_keys_file">
                        <input id="ServiceKey" class="cg_key_upload" type="file" name="cgSellContainer[ServiceKey]" accept=".csv,text/csv" aria-describedby="cgServiceKeyFormat cgServiceKeyTrialNote" />
                        <p class="cg_sale_keys_format" id="cgServiceKeyFormat">CSV file &middot; Max. 64 KB</p>
                        <p class="cg_sale_test_keys_note" id="cgServiceKeyTrialNote">Use test keys for trial orders.</p>
                    </div>
                    <p class="cg_error cg_hide cg_key_upload_error_not_csv" role="alert">Please select a CSV file.</p>
                    <p class="cg_error cg_hide cg_key_upload_error_to_large" role="alert">The CSV file must be 64 KB or smaller.</p>
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
		                <p>Text if all uploads were used<br><span class="cg_view_option_title_note"><span class="cg_note_label">NOTE:</span> <span class="cg_note_text">"Confirmation text after upload" or "Forward to another URL after upload"<br>are still valid if configured for cg_users_upload shortcode form</span><br><span class="cg_note_label">NOTE:</span> <span class="cg_note_text">If all uploads were used this text will be shown instead of "Confirmation after upload" configuration of upload form</span></span></p>
		            </div>
		            <div class='cg_view_option_html cg_view_option_input_full_width' >
		                 <div class='cg-wp-editor-container' data-wp-editor-id="cgAllUploadsUsedText"  >
		                    <textarea class='cg-wp-editor-template cg_view_option_textarea'   id='cgAllUploadsUsedText' name="cgSellContainer[AllUploadsUsedText]"></textarea>
		                </div>
		            </div>
        		</div>
        </div>
        <div class='cg_view_options_row cg_sale_shipping_row cg_hide' id="ShippingContainerRow">
                <div class='cg_view_option cg_sale_conf cg_sale_shipping_cell' id="IsAlternativeShippingOption">
                    <div class="cg_sale_shipping_heading">Shipping</div>
                    <div class="cg_sale_shipping_toggle">
                        <div class="cg_view_option_checkbox" id="IsAlternativeShippingCheckbox">
                            <input id="IsAlternativeShipping" type="checkbox" name="cgSellContainer[IsAlternativeShipping]" aria-labelledby="cgCustomShippingLabel" aria-describedby="cgCustomShippingNote">
                        </div>
                        <span id="cgCustomShippingLabel">Use custom shipping costs</span>
                    </div>
                    <p class="cg_sale_shipping_note" id="cgCustomShippingNote">Set a separate shipping cost for this product. Shipping uses the default tax.</p>
                </div>
                <div class='cg_view_option cg_sale_shipping_cell' id="ShippingContainer">
                    <label class="cg_sale_shipping_heading" for="Shipping">Custom shipping costs</label>
                    <div class="cg_view_option_input">
                        <input type="text" inputmode="decimal" name="cgSellContainer[Shipping]" id="Shipping" maxlength="20" class="cg_currency_input cg_currency_input_shipping cg_text_align_center" aria-describedby="cgShippingCostNote">
                    </div>
                    <p class="cg_sale_shipping_note" id="cgShippingCostNote">Enter 0 for free shipping.</p>
                </div>
        </div>
HEREDOC;

        echo <<<HEREDOC
        <div class='cg_view_options_row cg_sale_quantity_row cg_hide' id="SaleAmountContainer">
            <div class="cg_view_option cg_sale_conf cg_sale_shipping_cell">
                <label class="cg_sale_shipping_heading" for="SaleAmountMin">Minimum quantity</label>
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
            <div class="cg_view_option cg_sale_conf cg_sale_shipping_cell">
                <label class="cg_sale_shipping_heading" for="SaleAmountMax">Maximum quantity</label>
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
<div class="cg_sale_tax_panel">
<div class="cg_view_options_row" id="TaxRadioContainerRow">
                <div class="cg_view_option cg_sale_conf cg_view_option_full_width cg_sale_tax_choice">
                    <div class="cg_sale_tax_heading">Tax</div>
                    <div class="cg_sale_tax_default">
                        <span>Default tax in ecommerce options:</span>
                        <strong id="defaultTaxText"></strong>
                        <input type="text" id="defaultTaxInput" class="cg_hide" />
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
                <div class="cg_view_option cg_sale_conf cg_view_option_full_width cg_sale_tax_amount" id="TaxContainer">
                    <label class="cg_sale_tax_heading" for="Tax">Tax percentage</label>
                    <div class="cg_view_option_input">
                        <input type="text" name="cgSellContainer[Tax]" id="Tax" maxlength="20" class="cg_currency_input cg_currency_input_tax cg_text_align_center" >
                    </div>
                </div>
        </div>
</div>
HEREDOC;
echo <<<HEREDOC
	        <div id='cgSellSelectFilesForSale' class='cg_hide cg_sale_conf cg_download_sale'>
	                <div class="cg_sale_files_heading" id="cgSellTitlePreview">Download files</div>
	                <p class="cg_sale_files_description">Choose the files customers receive after purchase.</p>
	                <p id="cgSellFilesError" class="cg_error cg_hide" role="alert"></p>
                    <p id="cgSellFileOwnershipNotice" class="cg_error cg_hide" role="status">These files are managed by another sale. Open the linked sale or add a different file for this download.</p>
	                <div class="cg_sale_files_layout">
	                    <div class="cg_sale_files_notes">
	                        <div class="cg_sale_files_note">
	                            <strong>Protected files</strong>
	                            <p>Selected files are stored in a protected folder and become available on the customer's order page after purchase.</p>
	                        </div>
	                        <div class="cg_sale_files_note">
	                            <strong>Image previews</strong>
	                            <p>Image watermarks are managed using the settings below. Customers download the original files.</p>
	                        </div>
	                        <div class="cg_sale_files_note">
	                            <strong>Past purchases</strong>
	                            <p>Deleting this entry stops downloads for past buyers. Files added later are also included in their orders.</p>
	                        </div>
HEREDOC;

	if(function_exists('cg_render_nginx_upload_protection_notice') && function_exists('cg_get_nginx_upload_deny_rule')){
		cg_render_nginx_upload_protection_notice(
			'Using Nginx? Add this server rule to protect selling download files.',
			'Selling download files are moved into this gallery ecommerce folder. On Nginx they are only protected when the server configuration blocks this path. Ask your server admin to add this rule inside the matching server block and reload Nginx.',
			cg_get_nginx_upload_deny_rule('contest-gallery/gallery-id-'.absint($GalleryID).'/ecommerce'),
			'cg_ecommerce_nginx_notice cg_sell_files_nginx_notice'
		);
	}

	echo <<<HEREDOC
	                    </div>
	                    <div class="cg_sale_files_picker">
	                        <strong class="cg_sale_files_picker_title">Select files for sale</strong>
	                        <p class="cg_sale_files_picker_hint">Keep at least one file selected. To replace it, select another file first.</p>
	                        <div id="cgSellEntryWatermarkConflictNotice" class="cg_entry_watermark_ecommerce_conflict_notice cg_hide">
	                            <div class="cg_entry_watermark_ecommerce_conflict_text">Some files are already watermarked. Unwatermark them before selecting them for sale.</div>
	                            <input type="button" id="cgSellEntryWatermarkConflictButton" class="cg_action_button" value="Unwatermark first">
	                        </div>
	                        <div id="cgSellSelectFilesForSaleSelectContainer"></div>
	                    </div>
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
                <input id="cgWatermarkInputTitle" type="text" name="cgSellContainer[WatermarkTitle]" value="Contest Gallery" maxlength="256" style="width: 100%;" >
            </div>
        </div>
    <div class="cg_view_option cg_sale_conf  cg_view_option_flex_flow_column cg_border_none">
            <div class="cg_view_option_title cg_view_option_title_full_width">
                <p style="text-align: left;">Watermark size</p>
            </div>
	            <div class="cg_view_option_select" style="justify-content: left;">
	                <select name='cgSellContainer[WatermarkSize]' id='cgWatermarkSelectSize' style="width: 100%;" >
HEREDOC;

		foreach(array(512,256,128,64,32,16,8) as $size){
			$sizeLabel = function_exists('cg_get_watermark_size_label') ? cg_get_watermark_size_label($size) : $size;
			$sizeLabel = $size.' px ('.$sizeLabel.($size === 8 ? ', very small' : '').')';
			echo "<option value='".esc_attr($size)."'>".esc_html($sizeLabel)."</option>";
		}

		echo <<<HEREDOC
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
	        echo "<input type='hidden' name='cgSellContainer[saleAction]' id='cgSellAction' value='' >";

        //echo "<hr>";

        echo '<div id="cgSellContainerSubmitButtonContainer">
            <div id="cgSellActions" class="cg_sell_actions">
                <button type="button" id="cgDeactivateSale" class="cg_sell_secondary cg_hide" aria-expanded="false" aria-controls="cgSellDeactivateConfirmation">Deactivate sale</button>
                <input type="submit" id="cgSetForSell" class="cg_backend_button_gallery_action" value="Activate sale">
            </div>
            <div id="cgSellDeactivateConfirmation" class="cg_sell_deactivate_confirmation cg_hide" role="group" aria-labelledby="cgSellDeactivateTitle">
                <strong id="cgSellDeactivateTitle">Deactivate this sale?</strong>
                <p id="cgSellDeactivateDownloadNote" class="cg_hide">This stops the sale. Download files return to the media library.</p>
                <p id="cgSellDeactivateKeysNote" class="cg_hide">Uploaded key files stay protected.</p>
                <div class="cg_sell_confirmation_actions"><button type="button" id="cgCancelDeactivateSale" class="cg_sell_secondary">Cancel</button>
                <button type="button" id="cgConfirmDeactivateSale" class="cg_sell_secondary cg_sell_destructive">Deactivate sale</button></div>
            </div>
        </div>';
        echo '</div>';

        echo "</form>";
        echo "</div>";
        echo '<div id="cgSellDiscardConfirmation" class="cg_hide">
            <div class="cg_sell_discard_dialog" role="alertdialog" aria-modal="true" aria-labelledby="cgSellDiscardTitle" aria-describedby="cgSellDiscardDescription">
                <h2 id="cgSellDiscardTitle">Discard sale changes?</h2>
                <p id="cgSellDiscardDescription">You have unsaved changes. Close sales settings and discard your edits?</p>
                <div class="cg_sell_discard_actions">
                    <button type="button" id="cgKeepEditingSale">Keep editing</button>
                    <button type="button" id="cgDiscardSaleChanges">Discard changes</button>
                </div>
            </div>
        </div>';

// Sell PayPal container END

    }
}

?>
