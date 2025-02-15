jQuery(document).ready(function ($) {

    $(document).on('change','#UploadGallery',function (e) {
        var $cgSellContainer = $('#cgSellContainer');
        $cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLink').attr('href',$cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLinkPart').attr('href')+'&option_id='+$(this).val());
    });

    $(document).on('click','.cg_file_container',function (e) {
        e.preventDefault();
        if($(this).hasClass('cg_is_embed')){
            return;
        }

        var WpUpload = parseInt($(this).attr('data-cg-wp-upload'));
        var realId = parseInt($(this).attr('data-cg-real-id'));
        var order = parseInt($(this).attr('data-cg-order'));
        var $form = $(this).closest('form');
        var visibleWatermarkedImageOrder = $form.find('#cgSellPreview').attr('data-cg-order');

        debugger
        // order!=visibleWatermarkedImageOrder >>> was clicked first time, then not uncheck show first
        if(
            ($(this).find('.cg_checked').length && order==visibleWatermarkedImageOrder) ||
            ($(this).find('.cg_checked').length && !$(this).hasClass('cg_file_img_container'))
        ){// then unset
            $(this).find('.cg_checked').removeClass('cg_checked').addClass('cg_unchecked');
            if($(this).attr('data-cg-type')=='image' || $(this).attr('data-cg-is-image')=='1'){
                delete cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'];
                delete cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUpload];
                delete cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUpload];
            }
            debugger
            if(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1){
                cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.push(WpUpload);
            }
            // do not remove this condition,  otherwise might not work in some cases
            if(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1){
                cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.splice(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload), 1);
            }

            if($form.find('.cg_file_img_container .cg_checked').length){
                $form.find('.cg_file_img_container .cg_checked').first().removeClass('cg_checked').closest('.cg_file_img_container').click();
            }else{
                $form.find('#cgSellWatermarkPreview').addClass('cg_hide');
            }

        }else{// then set
            $(this).find('.cg_file_checkbox').removeClass('cg_unchecked').addClass('cg_checked');
            if($(this).attr('data-cg-type')=='image' || $(this).attr('data-cg-is-image')=='1'){
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
                $form.find('#cgWatermarkSelectPosition').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition']);
                $form.find('#cgWatermarkSelectSize').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize']);
                $form.find('#cgWatermarkInputTitle').val(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle']);
                debugger
                $form.find('#cgSellWatermarkPreview').removeClass('cg_hide');
                cgJsClassAdmin.gallery.watermark(cgJsClassAdmin.gallery.vars.backgroundUrlToSell,cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'],cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'],cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'],false,true,order,undefined,undefined);
            }
            debugger
            if(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1 && cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload)>-1){
                cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.splice(cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload), 1);
                if(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1){
                    cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.splice(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload), 1);
                }
            }else{
                if(cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)==-1 && cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)==-1){
                    cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.push(WpUpload);
                }
            }
        }
        debugger
        if(!$form.find('.cg_file_container[data-cg-type="image"] .cg_checked,.cg_file_container[data-cg-is-image="1"] .cg_checked').length){
            $form.find('#cgSellWatermarkPreview').addClass('cg_hide');
        }
    });

    $(document).on('click','#cgRemoveDownloadKeysFile',function (e) {
        e.preventDefault();
        $('#cgDownloadKeysFileRemoveNote').addClass('cg_hide');
        $('#cgDownloadKeysFileRemovedNote').removeClass('cg_hide');
        $('#cgRemoveDownloadKeysFileInput').val(1);
        $('#cgDownloadKeysFileRemoveWasClicked').val(1);
    });

    $(document).on('click','#cgRemoveServiceKeysFile',function (e) {
        e.preventDefault();
        $('#cgServiceKeysFileRemoveNote').addClass('cg_hide');
        $('#cgServiceKeysFileRemovedNote').removeClass('cg_hide');
        $('#cgRemoveServiceKeysFileInput').val(1);
        $('#cgServiceKeysFileRemoveWasClicked').val(1);
    });

    $(document).on('click','#DownloadKeysUrlName',function () {
        cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_download_keys_csv_url').click();
    });

    $(document).on('click','#ServiceKeysUrlName',function () {
        cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_service_keys_csv_url').click();
    });

    $(document).on('change','#DownloadKey,#ServiceKey',function () {
        debugger
        var $field = $(this);
        var type = ($(this).attr('id')=='DownloadKey') ? 'Download' : 'Service';

        var $cg_view_option = $(this).closest('.cg_view_option');
        $cg_view_option.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');

        var file = $field[0].files[0];

        if(!file){
            return;
        }

        var fileType = file.type;
        var fileSize = file.size;

        if(fileType!='text/csv'){
            $cg_view_option.find('.cg_key_upload_error_not_csv').removeClass('cg_hide');
            $field.removeAttr('name');
            if($('#cg'+type+'KeysFileRemoveWasClicked').val()!=1){
                $('#cgRemove'+type+'KeysFileInput').val(0);
            }
        }else if(fileSize>64000){
            $cg_view_option.find('.cg_key_upload_error_to_large').removeClass('cg_hide');
            $field.removeAttr('name');
            if($('#cg'+type+'KeysFileRemoveWasClicked').val()!=1){
                $('#cgRemove'+type+'KeysFileInput').val(0);
            }
        }else{
            $field.attr('name','cgSellContainer['+$field.attr('id')+']');
            if($field.val() && !$('#cg'+type+'KeysFileRemoveNote').hasClass('cg_hide')){
                $('#cgRemove'+type+'KeysFileInput').val(1);
            }else{
                if($('#cg'+type+'KeysFileRemoveWasClicked').val()!=1){
                    $('#cgRemove'+type+'KeysFileInput').val(0);
                }
            }

        }
    });

    $(document).on('click','#SaleType_download_option_container',function () {
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            var $cgSellContainer = $(this).closest('#cgSellContainer');
            $cgSellContainer.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');
            $cgSellContainer.find('#DownloadKey,#ServiceKey').val('');
            $cgSellContainer.find('#UploadContainerRow').addClass('cg_hide');

            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer);

            cgJsClassAdmin.gallery.functions.prepareDownloadDataToShow($cgSellContainer);
            var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
            var realId = $sortableDiv.attr('data-cg-real-id');

            cgJsClassAdmin.gallery.functions.initDownloadSale($,$cgSellContainer,$sortableDiv,realId);
            if(!cgJsClassAdmin.gallery.vars.WatermarkSettings){
                cgJsClassAdmin.gallery.functions.initWatermarkSettings($,$cgSellContainer,$sortableDiv,realId);
            }

            // will be done via click on cg_file_checkbox if firstCheckedImageOrder exists
            //cgJsClassAdmin.gallery.watermarkOnShow($,realId,cgJsClassAdmin.gallery.vars.$sortableDiv,undefined,$(this).closest('#cgSellContainer'));
        }
    });

    $(document).on('click','#SaleType_shipping_option_container',function () {
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            var $cgSellContainer = $('#cgSellContainer');
            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer,true);
            $cgSellContainer.find('#DownloadKey,#ServiceKey').val('');
            $cgSellContainer.find('#ShippingContainerRow').removeClass('cg_hide');
            $cgSellContainer.find('#SaleAmountContainer').removeClass('cg_hide');
            $cgSellContainer.find('#ServiceContainerRow').addClass('cg_hide');
            $cgSellContainer.find('#UploadContainerRow').addClass('cg_hide');
            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
            $cgSellContainer.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');
            $cgSellContainer.find('#DownloadContainerRow').addClass('cg_hide');
        }
    });

    $(document).on('click','#SaleType_service_option_container',function () {
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            var $cgSellContainer = $('#cgSellContainer');
            $cgSellContainer.find('#DownloadKey,#ServiceKey').val('');
            $cgSellContainer.find('#ShippingContainerRow').addClass('cg_hide');
            $cgSellContainer.find('#SaleAmountContainer').addClass('cg_hide');
            $cgSellContainer.find('#UploadContainerRow').addClass('cg_hide');
            $cgSellContainer.find('#ServiceContainerRow').removeClass('cg_hide');
            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
            $cgSellContainer.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');
            $cgSellContainer.find('#DownloadContainerRow').addClass('cg_hide');

            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer);
        }
    });

    $(document).on('click','#SaleType_upload_option_container',function () {
        debugger
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            var $cgSellContainer = $('#cgSellContainer');
            $cgSellContainer.find('#DownloadKey,#ServiceKey').val('');
            $cgSellContainer.find('#ServiceContainerRow').addClass('cg_hide');
            $cgSellContainer.find('#ShippingContainerRow').addClass('cg_hide');
            $cgSellContainer.find('#SaleAmountContainer').addClass('cg_hide');
            $cgSellContainer.find('#UploadContainerRow').removeClass('cg_hide');
            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
            $cgSellContainer.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');
            $cgSellContainer.find('#DownloadContainerRow').addClass('cg_hide');

            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer);

        }
    });

    $(document).on('click','#TaxFreeRadioContainerParent',function () {
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            $('#TaxContainerRow').addClass('cg_hide');
        }else{
            $('#TaxContainerRow').removeClass('cg_hide');
        }
    });

    $(document).on('click','#TaxRequiredRadioContainerParent',function () {
        if($(this).find('.cg_view_option_radio_multiple_input').hasClass('cg_view_option_checked')){
            $('#TaxContainerRow').removeClass('cg_hide');
            if($('#Tax').val()==0){
                $('#Tax').val($('#TaxDefault').val());
            }
        }else{
            $('#TaxContainerRow').addClass('cg_hide');
        }
    });

    $(document).on('click','#IsAlternativeShippingOption',function () {
        if($(this).find('#IsAlternativeShipping').prop('checked')){
            $('#ShippingContainer').removeClass('cg_disabled cg_disabled_background_color_e0e0e0');
        }else{
            $('#ShippingContainer').addClass('cg_disabled cg_disabled_background_color_e0e0e0');
        }
    });

    $(document).on('click','.cg_sell_conf_button,.cg_for_sale_price_container,.cg_sale_settings',function () {

        if($(this).hasClass('cg_go_to_sales')){
            return;
        }
        debugger
        var $cgSellContainer = $('#cgSellContainer');
        cgJsClassAdmin.gallery.vars.WatermarkSettings = null; // has to be completely  reseted here
        var $cgSellSelectFilesForSaleSelectContainer = $cgSellContainer.find('#cgSellSelectFilesForSaleSelectContainer');
        if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload && false){
            $cgSellContainer.addClass('cg_visibility_hidden_override');
        }else{
            $cgSellContainer.removeClass('cg_visibility_hidden_override');
        }
        $cgSellContainer.removeClass('cg_hide').addClass('cg_overflow_y_hidden');
        var $cgSellContainerForm = $cgSellContainer.find('form');
        $cgSellContainerForm.removeClass('cg_hide').addClass('cg_visibility_hidden');

        cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer);

        cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider = $('#cgPriceDivider').val();

        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale = [];
        cgJsClassAdmin.gallery.vars.cgSellContainer = $cgSellContainer;
        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgBase64WatermarkedImageNew').val('');
        cgJsClassAdmin.gallery.currencyInputFunctions.cgCurrencySelectSet();
        $('#cgSellContainerFadeBackground').removeClass('cg_hide').addClass('cg_active');
        cgJsClassAdmin.gallery.vars.backgroundUrlToSell = $(this).closest('.cg_sortable_div').find('.cg_image_src_large_to_show').val();
        cgJsClassAdmin.gallery.vars.ecommerceDownloadInitiated = false;
        cgJsClassAdmin.gallery.vars.ecommerceWatermarkInitiated = false;
        cgJsClassAdmin.gallery.vars.$sortableDiv = $(this).closest('.cg_sortable_div');
        var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
        $cgSellContainer.find('#UploadContainerRow').addClass('cg_hide');
        cgJsClassAdmin.gallery.functions.setMultipleFilesForPostBeforeClone(undefined,$sortableDiv.find('.cg_backend_info_container'));

        // add gallery ids to UploadGallery select
        $cgSellContainer.find('#UploadGallery').empty();
        var highestGalleryId = 0;
        for(var index in cgJsClassAdmin.gallery.vars.galleryIDs){
            if(!cgJsClassAdmin.gallery.vars.galleryIDs.hasOwnProperty(index)){
                break;
            }
            var id = cgJsClassAdmin.gallery.vars.galleryIDs[index].id;
            $cgSellContainer.find('#UploadGallery').append('<option value="'+id+'"  >Gallery ID '+id+'</option>');
            if(!highestGalleryId){
                highestGalleryId = id;
                $cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLink').attr('href',$cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLinkPart').attr('href')+'&option_id='+highestGalleryId);
            }
        }
        //$cgSellContainer.find('#UploadGallery').val($cgSellContainer.find('#UploadGallery option:first-child').val());

        // add allowed uploads to MaxUploads select
        for(var i=1;i<=100;i++){
            if(!cgJsClassAdmin.gallery.vars.galleryIDs.hasOwnProperty(index)){
                break;
            }
            $cgSellContainer.find('#MaxUploads').append('<option value="'+i+'">'+i+'</option>');
        }
        $cgSellContainer.find('#MaxUploads').append('<option value="unlimited">unlimited</option>');

        if($sortableDiv.find('.MaxUploads').val()>=1){
            $cgSellContainer.find('#MaxUploads').val($sortableDiv.find('.MaxUploads').val());
        }else{
            $cgSellContainer.find('#MaxUploads').val(1);
        }

        var optionId = $('#cg_gallery_id').val();
        if($sortableDiv.find('.UploadGallery').val()!='' && $sortableDiv.find('.UploadGallery').val()>0){
            optionId = $sortableDiv.find('.UploadGallery').val();
        }

        $cgSellContainer.find('#UploadGallery').val(optionId);

        $cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLink').attr('href',$cgSellContainer.find('#cgContactFormShortcodeConfigurationAreaLinkPart').attr('href')+'&option_id='+optionId);

        //CHECK if Ecommerce
        var priceDivider = $cgSellContainer.find('#cgPriceDivider').val();
        cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider = priceDivider;
        var CurrencyPosition = $cgSellContainer.find('#CurrencyPosition').val();
        cgJsClassAdmin.gallery.currencyInputFunctions.CurrencyPosition = CurrencyPosition;
        var CurrencySymbol = $cgSellContainer.find('#cgCurrencySymbol').val();
        cgJsClassAdmin.gallery.currencyInputFunctions.CurrencySymbol = CurrencySymbol;

        if(priceDivider=='.'){
            cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider = ',';
        }else{
            cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider = '.';
        }
        //CHECK if Ecommerce --- END

        var realId = $sortableDiv.find('.cg_real_id').val();
        var saleType = $sortableDiv.find('.SaleType').val();
        if(!saleType){
            saleType = 'shipping';
            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer,true);
        }else{
            cgJsClassAdmin.gallery.functions.addRemoveCgProFalse($cgSellContainer);
        }
        var saleAmountMin = $sortableDiv.find('.SaleAmountMin').val();
        var saleAmountMax = $sortableDiv.find('.SaleAmountMax').val();
        var Tax = $sortableDiv.find('.Tax').val();
        Tax = String(parseFloat(Tax).toFixed(2));
        var $Tax = $cgSellContainerForm.find('#Tax');
        var TaxDefault = $cgSellContainerForm.find('#TaxDefault').val();
        TaxDefault = String(parseFloat(TaxDefault).toFixed(2));
        var DefaultShipping = $cgSellContainerForm.find('#DefaultShipping').val();
        DefaultShipping = String(parseFloat(DefaultShipping).toFixed(2));
        var AlternativeShipping = $sortableDiv.find('.AlternativeShipping').val();
        debugger
        AlternativeShipping = String(parseFloat(AlternativeShipping).toFixed(2));
        var IsAlternativeShipping = $sortableDiv.find('.IsAlternativeShipping').val();
        var $Shipping = $cgSellContainerForm.find('#Shipping');
        $Shipping.val('');//reset here
        // reset SaleTypes
        $cgSellContainer.find('.SaleType').prop('checked',false).parent().addClass('cg_view_option_unchecked').removeClass('cg_view_option_checked');
        $cgSellContainer.find('#cgSellWatermarkPreview').addClass('cg_hide');
        var $IsAlternativeShipping = $cgSellContainerForm.find('#IsAlternativeShipping');

        $cgSellContainer.find('#cgSellWatermarkPreview').addClass('cg_hide');
        $cgSellContainer.find('#cgDownloadKeysFileRemovedNote').addClass('cg_hide');
        $cgSellContainer.find('#cgServiceKeysFileRemovedNote').addClass('cg_hide');
        $cgSellContainer.find('.cg_key_upload_error_not_csv, .cg_key_upload_error_to_large').addClass('cg_hide');//reset
        $cgSellContainer.find('#cgSellPreview').remove();
        $cgSellContainer.find('#cgSellPreviewContainerLoader').addClass('cg_hide');
        $cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_disabled cg_disabled_background_color_e0e0e0');

        $cgSellContainer.find('#cgWatermarkSelectPosition,#cgWatermarkInputTitle,#cgWatermarkSelectSize').attr('data-cg-real-id',realId);

        $cgSellContainer.find('#cgRemoveServiceKeysFileInput,#cgRemoveDownloadKeysFileInput').val(0);

        if($sortableDiv.find('.IsAlternativeShipping').val()=='1'){
            $IsAlternativeShipping.prop('checked',true);
        }else{
            $IsAlternativeShipping.prop('checked',false);
        }
        if($IsAlternativeShipping.prop('checked')){
            $cgSellContainerForm.find('#ShippingContainer').removeClass('cg_disabled');
        }else{
            $cgSellContainerForm.find('#ShippingContainer').addClass('cg_disabled');
        }



        // reset here
        // in current status always checked
        //$cgSellContainerForm.find('#cgWatermarkSettingsRow').addClass('cg_disabled_background_color_e0e0e0');
        //$cgSellContainerForm.find('#cgWatermarkOnDiv').addClass('cg_view_option_unchecked').removeClass('cg_view_option_checked');
        //$cgSellContainerForm.find('#cgWatermarkOn').prop('checked',false);
        if(!cgJsClassAdmin.index.vars.isSellConfEventsLoaded){
            // events should be initiated only one time!!!
            // best case to remove and set, so for sure always works when opened, otherwise might not work in some cases
            // listener will be removed before in initOptionsClickEvents
            setTimeout(function (){
                cgJsClassAdmin.options.functions.initOptionsClickEvents(false,true,$cgSellContainer,true);
            },100);
            cgJsClassAdmin.index.vars.isSellConfEventsLoaded = true;
        }

        $cgSellContainer.find('#defaultShippingPriceInput').val(DefaultShipping);
        $cgSellContainer.find('#defaultTaxInput').val(TaxDefault);

        cgJsClassAdmin.gallery.currencyInputFunctions.setCurNew($cgSellContainer.find('#defaultShippingPriceInput'),undefined,undefined,undefined);

        var CurrencyPosition = $cgSellContainer.find('#CurrencyPosition').val();
        var CurrencySymbol = $cgSellContainer.find('#CurrencySymbol').val();

        var DefaultShippingToShow = cgJsClassAdmin.gallery.functions.setDivider(DefaultShipping);
        DefaultShippingToShow = cgJsClassAdmin.gallery.functions.setSymbolByPosition(DefaultShippingToShow,CurrencySymbol);
        $cgSellContainer.find('#defaultShippingPriceText').text(DefaultShippingToShow);

        var TaxDefaultToShow = cgJsClassAdmin.gallery.functions.setDivider(TaxDefault);
        $cgSellContainer.find('#defaultTaxText').text(TaxDefaultToShow+'%');

        if(!cgJsClassAdmin.index.vars.isSellConfTinyMCEInitialized){
            $cgSellContainer.find('#cgSellFormLoaderContainer').removeClass('cg_hide');
            setTimeout(function (){
                $cgSellContainer.find('#cgSellFormLoaderContainer').addClass('cg_hide');
                $cgSellContainerForm.removeClass('cg_visibility_hidden');
                $cgSellContainer.removeClass('cg_overflow_y_hidden');
            },2000);
        }else{
            $cgSellContainer.find('#cgSellFormLoaderContainer').addClass('cg_hide');
            $cgSellContainerForm.removeClass('cg_visibility_hidden');
            $cgSellContainer.removeClass('cg_overflow_y_hidden');
        }

        var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
        var isNotActivatedForSale = false;

        if($sortableDiv.find('.cg_ecommerce_entry').val()!=0){
            $cgSellContainer.find('#cgActivateSaleOption .cg_view_option_checkbox').addClass('cg_view_option_checked').removeClass('cg_view_option_unchecked');// has to be done at the beginning
            $cgSellContainer.find('#cgActivateSaleOption #cgActivateSale').prop('checked',true);
            $cgSellContainer.find('.cg_sale_conf').removeClass('cg_disabled cg_disabled_background_color_e0e0e0');
            $cgSellContainer.find('#cgSellContainerSubmitButtonContainer').removeClass('cg_disabled_background_color_e0e0e0');
        }else{
            isNotActivatedForSale = true;
            $cgSellContainer.find('#cgActivateSaleOption .cg_view_option_checkbox').addClass('cg_view_option_unchecked').removeClass('cg_view_option_checked');// has to be done at the beginning
            $cgSellContainer.find('#cgActivateSaleOption #cgActivateSale').prop('checked',false);
            $cgSellContainer.find('.cg_sale_conf').addClass('cg_disabled cg_disabled_background_color_e0e0e0');
            $cgSellContainer.find('#cgSellContainerSubmitButtonContainer').addClass('cg_disabled_background_color_e0e0e0');
        }

        $cgSellContainer.find('#cgDownloadKeysFileRemoveWasClicked').val(0);
        if($sortableDiv.find('.cg_download_keys_csv_url').length){
            $cgSellContainer.find('#cgDownloadKeysFileRemoveNote').removeClass('cg_hide');
            $cgSellContainer.find('#cgDownloadKeysFileRemovedNote').addClass('cg_hide');
            $cgSellContainer.find('#cgShowDownloadKeysFile,#DownloadKeysUrlName').attr('href',$sortableDiv.find('.cg_download_keys_csv_url').attr('href'));
            $cgSellContainer.find('#cgShowDownloadKeysFile').text($sortableDiv.find('.cg_download_keys_csv_name').val());
        }else{
            $cgSellContainer.find('#cgDownloadKeysFileRemoveNote').addClass('cg_hide');
        }

        $cgSellContainer.find('#cgServiceKeysFileRemoveWasClicked').val(0);
        if($sortableDiv.find('.cg_service_keys_csv_url').length){
            $cgSellContainer.find('#cgServiceKeysFileRemoveNote').removeClass('cg_hide');
            $cgSellContainer.find('#cgServiceKeysFileRemovedNote').addClass('cg_hide');
            // <a href="" id="DownloadKeysUrlName"></a> <<< do it this way place it in DownloadKeysUrlContainer
            $cgSellContainer.find('#cgShowServiceKeysFile,#ServiceKeysUrlName').attr('href',$sortableDiv.find('.cg_service_keys_csv_url').attr('href'));
            $cgSellContainer.find('#cgShowServiceKeysFile').text($sortableDiv.find('.cg_service_keys_csv_name').val());
        }else{
            $cgSellContainer.find('#cgServiceKeysFileRemoveNote').addClass('cg_hide');
        }

        var placeholderValue = 49.99;
        if(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider == ','){
            placeholderValue = '49,99';
        }
        debugger
        if($sortableDiv.find('.Price').val()!='0'){
            if(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider=='.'){
                var valueToSet = $sortableDiv.find('.Price').val().split('.');
                valueToSet = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(valueToSet[0])+'.'+valueToSet[1];
            }else{
                var valueToSet = $sortableDiv.find('.Price').val().split('.');
                valueToSet = cgJsClassAdmin.gallery.currencyInputFunctions.codepenFormat(valueToSet[0])+','+valueToSet[1];
            }
            $cgSellContainer.find('#cgSellPrice').val(valueToSet);
        }else{
            $cgSellContainer.find('#cgSellPrice').val(placeholderValue);
        }
        debugger
        cgJsClassAdmin.gallery.currencyInputFunctions.setCurNew($cgSellContainer.find('#cgSellPrice'),true);

        $cgSellContainer.find('#cgSaleTitle').val(($sortableDiv.find('.SaleTitle').val().length) ? $sortableDiv.find('.SaleTitle').val() : '');
        $cgSellContainer.find('#cgSaleDescription').val(($sortableDiv.find('.SaleDescription').val().length) ? $sortableDiv.find('.SaleDescription').val() : '');

        $cgSellContainer.find('#cgWpUploadTitle').val(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_post_title').val());
        $cgSellContainer.find('#cgWpUploadName').val(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_post_name').val());
        $cgSellContainer.find('#cgWpUploadType').val(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_post_mime_type').val());
        $cgSellContainer.find('#cgSrcWpUploadId').val(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_upload_id').val());
        $cgSellContainer.find('#cgRealId').val(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_real_id').val());

        if(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.IsEcommerceSale').val()==1){
            $cgSellContainer.find('#cgSetForSell').text('Change settings');
        }else{
            $cgSellContainer.find('#cgSetForSell').text('Activate sale');
        }

        $cgSellContainer.find('.Tax_radio').prop(false);
        $cgSellContainer.find('#TaxContainerRow').addClass('cg_hide');
        $cgSellContainer.find('#TaxRequiredRadioContainer, #TaxFreeRadioContainer').removeClass('cg_view_option_checked');

        if(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.HasTax').val()==1 || isNotActivatedForSale==true){
            $cgSellContainer.find('#TaxRequired').prop('checked',true);
            $cgSellContainer.find('#TaxContainerRow').removeClass('cg_hide');
            $cgSellContainer.find('#TaxRequiredRadioContainer').addClass('cg_view_option_checked');
        }else{
            $cgSellContainer.find('#TaxRequired').prop('checked',false);
            $cgSellContainer.find('#TaxRequiredRadioContainer').removeClass('cg_view_option_checked');
            $cgSellContainer.find('#TaxFree').prop('checked',true);
            $cgSellContainer.find('#TaxFreeRadioContainer').addClass('cg_view_option_checked');
        }

        if(parseFloat(Tax)>0){
            var input_val_to_show = cgJsClassAdmin.gallery.functions.setDivider(Tax);
            $Tax.val(input_val_to_show);
        }else{
            var input_val_to_show = cgJsClassAdmin.gallery.functions.setDivider(TaxDefault);
            $Tax.val(input_val_to_show);
        }

        //if($sortableDiv.find('.IsImageType').val()=='1'){
        $cgSellContainer.find('#cgSellPreviewContainer').removeClass('cg_hide');
        $cgSellContainer.find('#cgSellTitlePreview').removeClass('cg_hide');

        $cgSellContainer.find('.cg_view_option_radio_multiple_input').addClass('cg_view_option_unchecked').removeClass('cg_view_option_unchecked');
        $cgSellContainer.find('.SaleType').prop('checked',false);

        $cgSellContainer.find('.SaleType_'+saleType).prop('checked',true).closest('.cg_view_option_radio_multiple_input').addClass('cg_view_option_checked');

        cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#SaleAmountContainer').addClass('cg_hide');
        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#IsAlternativeShippingCheckbox').addClass('cg_view_option_unchecked');
        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#IsAlternativeShipping').prop('checked',false);// reset here
        $cgSellContainer.find('#ShippingContainerRow').addClass('cg_hide');

        if(IsAlternativeShipping==1){
            var input_val = cgJsClassAdmin.gallery.functions.setDivider(AlternativeShipping);
            $Shipping.val(input_val);
        }else{
            var input_val = cgJsClassAdmin.gallery.functions.setDivider(DefaultShipping);
            $Shipping.val(input_val);
        }

        if(saleType=='service'){// will be removed later if required
            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
            jQuery('#SaleType_service_option_container').click();
            if($sortableDiv.find('.cg_service_keys_csv_name').length){
                $cgSellContainer.find('#ServiceKeysUrlName,#cgShowServiceKeysFile').text($sortableDiv.find('.cg_service_keys_csv_name').val());
            }
        } else if(saleType=='upload'){// will be removed later if required
            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
            debugger
            jQuery('#SaleType_upload_option_container').click();
        } else if(saleType=='download'){// will be removed later if required
            cgJsClassAdmin.gallery.functions.initDownloadSale($,$cgSellContainer,$sortableDiv,realId);
        }else if (saleType=='shipping'){
            jQuery('#SaleType_shipping_option_container').click();
            debugger
            if(saleAmountMin>=1){
                $cgSellContainer.find('#SaleAmountMin').val(saleAmountMin);
            }else{
                $cgSellContainer.find('#SaleAmountMin').val(1);
            }

            if(saleAmountMax>=1){
                $cgSellContainer.find('#SaleAmountMax').val(saleAmountMax);
            }else{
                $cgSellContainer.find('#SaleAmountMax').val(10);
            }

            $cgSellContainer.find('#SaleAmountContainer').removeClass('cg_hide');

            if($sortableDiv.find('.IsAlternativeShipping').val()==1){
                $cgSellContainer.find('#ShippingContainer').removeClass('cg_disabled');
                $cgSellContainer.find('#IsAlternativeShippingCheckbox').addClass('cg_view_option_checked').removeClass('cg_view_option_unchecked');
                $cgSellContainer.find('#IsAlternativeShipping').prop('checked',true);
            }else{
                $cgSellContainer.find('#ShippingContainer').addClass('cg_disabled');
            }
            $cgSellContainer.find('#ShippingContainerRow').removeClass('cg_hide');

            $cgSellContainer.find('.cg_download_sale').addClass('cg_hide');

        }

        $cgSellContainer.find('#cgSellArrowLeft').addClass('cg_hide');
        $cgSellContainer.find('#cgSellArrowRight').addClass('cg_hide');

        var $clone = $sortableDiv.find('.cg_backend_image_full_size_target').clone();
        $clone.find('input, .cg_for_sale_price_container').remove();

        $cgSellContainer.find('#cgSellBackendImageId').text(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_real_id').val());
        $cgSellContainer.find('#cgSellBackendImageContainer').empty().append($clone);
        $clone.find('> a > *').unwrap();// parent a tag has to be removed so all types will be displayed correctly
        $cgSellContainer.find('#cgSellBackendImageContainer').find('.cg_backend_image').removeClass('cg90degree  cg180degree  cg270degree ');

        // go from here
        if(saleType=='download'){
            cgJsClassAdmin.gallery.functions.initWatermarkSettings($,$cgSellContainer,$sortableDiv,realId);
        }

        if(false && cgJsClassAdmin.index.vars.isSellConfTinyMCEInitialized){// not required beeing used in the moment, cause broken load of editor
            $cgSellContainer.find('.cg_view_option_textarea').each(function (){
                var id = $(this).attr('id');
                tinymce.EditorManager.execCommand('mceRemoveEditor', true, id);
            });
        }

        if(!cgJsClassAdmin.index.vars.isSellConfTinyMCEInitialized){
            $cgSellContainer.find('.cg_view_option_textarea').each(function (){
                console.log('initializeEditor');
                console.log($(this).attr('id'));
                var id = $(this).attr('id');
                cgJsClassAdmin.index.functions.initializeEditor(id);
            });
            cgJsClassAdmin.index.vars.isSellConfTinyMCEInitialized = true;
            setTimeout(function (){
                tinymce.get('cgAllUploadsUsedText').setContent(($sortableDiv.find('.AllUploadsUsedText').val().length) ? $sortableDiv.find('.AllUploadsUsedText').val() : '');
            },2000);
            //$cgSellContainer.find('#cgAllUploadsUsedText').val(($sortableDiv.find('.AllUploadsUsedText').val().length) ? $sortableDiv.find('.AllUploadsUsedText').val() : '');
        }else{
            tinymce.get('cgAllUploadsUsedText').setContent(($sortableDiv.find('.AllUploadsUsedText').val().length) ? $sortableDiv.find('.AllUploadsUsedText').val() : '');
        }

        // will be done via click on cg_file_checkbox if firstCheckedImageOrder exists
        //cgJsClassAdmin.gallery.watermarkOnShow($,realId,$sortableDiv,firstImageWatermarkedWpUpload,WatermarkPosition,WatermarkTitle,WatermarkSize);

        // has to be done here at the end after whole processing
        if(saleType=='service' || saleType=='shipping'){
            cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cg_download_sale').addClass('cg_hide');
        }

        // has to be done here at the end
        jQuery('#ServiceKey,#DownloadKey').val('');
        if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload && false){
            if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceDownload){
                //cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Ecommerce images for download will be watermarked and download files will be moved to an inaccessible folder',true);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Processing',true);
            }else{
                //cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Ecommerce images for download will be watermarked',true);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Processing',true);
            }
            // isNewAddedEcommerceImageForDownload will be set to false when all watermarked
            //cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload = false;
        }
        if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceDownload && !cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload && false){
            cgJsClassAdmin.gallery.vars.isNewAddedEcommerceDownload = false;
            setTimeout(function (){
                //cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Download files will be moved to an inaccessible folder',true);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Processing',true);
                cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSetForSell').click();
            },100);
        }


    });

});