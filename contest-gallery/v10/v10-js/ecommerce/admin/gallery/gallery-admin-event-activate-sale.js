jQuery(document).ready(function ($) {


    $(document).on('click','#cgActivateSaleOption',function () {
        var $cgSellContainer = $('#cgSellContainer');
        $cgSellContainer.find('#cgSellContainerSubmitButtonContainer').removeClass('cg_disabled_background_color_e0e0e0');
        setTimeout(function (){// has to be done with timeout so works correctly
            if($cgSellContainer.find('#cgActivateSale').prop('checked')){
                $cgSellContainer.find('.cg_sale_conf,#cgSellWatermarkPreview').removeClass('cg_disabled cg_disabled_background_color_e0e0e0');
                if($cgSellContainer.find('#IsAlternativeShipping').prop('checked')==true){
                    $cgSellContainer.find('#ShippingContainer').removeClass('cg_disabled cg_disabled_background_color_e0e0e0');
                }
            }else{
                $cgSellContainer.find('.cg_sale_conf,#ShippingContainer,#cgSellWatermarkPreview').addClass('cg_disabled cg_disabled_background_color_e0e0e0');
                if(!$('#cgServiceKeysFileRemoveNote').hasClass('cg_hide') && !$('#cgDownloadKeysFileRemoveNote').hasClass('cg_hide')){
                    $('#cgDownloadAndServiceKeyFilesWillBeDeleted').removeClass('cg_hide');
                }else if(!$('#cgDownloadKeysFileRemoveNote').hasClass('cg_hide')){
                    $('#cgDownloadKeyFilesWillBeDeleted').removeClass('cg_hide');
                }else if(!$('#cgServiceKeysFileRemoveNote').hasClass('cg_hide')){
                    $('#cgServiceKeyFilesWillBeDeleted').removeClass('cg_hide');
                }
                //$('#cgBackendBackgroundDrop').addClass('cg_high_overlay');
            }
        },100);
    });


    $(document).on('click','#cgSetForSell',function (e) {
        e.preventDefault();

        var $form = $(this).closest('form');

        var id = cgJsClassAdmin.gallery.vars.$sortableDiv.attr('data-cg-real-id');
        var TaxEntry = $form.find('#Tax').val();
        var realId = $form.find('#cgRealId').val();

        // required correction here
        $form.find('.cg_file_container.cg_is_embed .cg_file_checkbox.cg_checked').removeClass('cg_checked').addClass('cg_unchecked');

        if($form.find('.SaleType_download').prop('checked') && !$form.find('.cg_file_container:not(.cg_is_embed) .cg_file_checkbox.cg_checked').length  && $form.find('#cgActivateSale').prop('checked')){
            $('#cgNoFilesForSaleDownload').removeClass('cg_hide').addClass('cg_active');
            $('#cgBackendBackgroundDrop').addClass('cg_high_overlay');
            return;
        }

        var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
        var $cgSellContainer = $('#cgSellContainer');

        $sortableDiv.find('.Price').val($form.find('[name="PriceResult"]').val());
        $sortableDiv.find('.PriceDivider').val($form.find('[name="PriceResult"]').val());
        $sortableDiv.find('.WatermarkPosition').val($form.find('[name="WatermarkPosition"]').val());
        $sortableDiv.find('.WatermarkTitle').val($form.find('[name="WatermarkTitle"]').val());
        $sortableDiv.find('.WatermarkSize').val($form.find('[name="WatermarkSize"]').val());
        $sortableDiv.find('.SaleTitle').val($form.find('[name="SaleTitle"]').val());
        $sortableDiv.find('.SaleDescription').val($form.find('[name="SaleDescription"]').val());
        $sortableDiv.find('.CurrencyShort').val($form.find('[name="CurrencyShort"]').val());
        $sortableDiv.find('.CurrencyPosition').val($form.find('[name="CurrencyPosition"]').val());
        $sortableDiv.find('.MaxUploads').val($form.find('[name="MaxUploads"]').val());

        if($cgSellContainer.find('#wp-cgAllUploadsUsedText-wrap').hasClass('html-active')){
            //$cgSellContainer.find('#cgAllUploadsUsedText').val(tinymce.get('cgAllUploadsUsedText').getContent());
        }else{
            $cgSellContainer.find('#cgAllUploadsUsedText').val(tinymce.get('cgAllUploadsUsedText').getContent());
        }

        if($form.find('.SaleType_shipping').prop('checked')){
            $form.find('#DownloadKey,#ServiceKey').val('');
        }

        if(TaxEntry){
            $form.find('#Tax').val(TaxEntry);
        }else{
            $form.find('#Tax').val(0);
        }

        // have to be done manually here, don't know why
        //$('#cgSaleDescription').val(tinyMCE.get('cgSaleDescription').getContent());

        var $cgSellPrice = jQuery('#cgSellPrice');

        if($cgSellPrice.val()!=''){
            var input_val = $cgSellPrice.val();
            var decimal_pos = input_val.indexOf(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider);
            input_val = cgJsClassAdmin.gallery.currencyInputFunctions.correctInputVal(decimal_pos,input_val,true);
            $cgSellPrice.val(input_val);
        }

        var $Tax = jQuery('#Tax');

        if($Tax.val()!=''){
            var input_val = $Tax.val();
            var decimal_pos = input_val.indexOf(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider);
            input_val = cgJsClassAdmin.gallery.currencyInputFunctions.correctInputVal(decimal_pos,input_val,true);
            $Tax.val(input_val);
        }

        var $Shipping = jQuery('#Shipping');
        if($Shipping.val()!=''){
            var input_val = $Shipping.val();
            var decimal_pos = input_val.indexOf(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider);
            input_val = cgJsClassAdmin.gallery.currencyInputFunctions.correctInputVal(decimal_pos,input_val,true);
            $Shipping.val(input_val);
        }

        var form = $form.get(0);
        var formPostData = new FormData(form);

        var firstBase64OrFile;
        var firstBase64OrFileType;

        //  reset for sure in this cases
        if(!$form.find('.SaleType_download').prop('checked') || !$form.find('#cgActivateSale').prop('checked')){
            cgJsClassAdmin.gallery.vars.base64andAltFileValues = [];
            cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale = [];
            cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew = [];
            cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] = null;
        }
        // can be reseted anyway
        cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded = [];

        var order = 1;
        debugger
        if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && $form.find('.SaleType_download').prop('checked') && $form.find('#cgActivateSale').prop('checked')){
            for(var WpUploadId in cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId]){
                if(!cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId].hasOwnProperty(WpUploadId)){break;}
                firstBase64OrFile = cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId];
                firstBase64OrFileType  = cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId];
                if(cgJsClassAdmin.index.functions.isImageType(firstBase64OrFileType)){
                    formPostData.append('cgSellContainer[base64WatermarkedAndAltFiles]['+WpUploadId+']', cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId]);
                    formPostData.append('cgSellContainer[base64WatermarkedAndAltFileTypes]['+WpUploadId+']', firstBase64OrFileType);
                }
                order++;
            }
            debugger
            for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
                if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                    break;
                }
                var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload;
                // removedWpUploadIdsFromSale if images should not be watermarked anymore
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {
                    if(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] ){
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkTitle]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkPosition]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkSize]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize']);
                    }
                }
                debugger
                if($form.find('.SaleType_download').prop('checked') && cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(parseInt(WpUpload))>-1){
                    debugger
                    formPostData.append('cgSellContainer[WpUploadFilesForSale][]',WpUpload);
                }
            }
        }else{
            if($form.find('.SaleType_download').prop('checked') && $form.find('#cgActivateSale').prop('checked')){
                var WpUploadId = parseInt(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_upload_id').val());
                debugger
                if(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container').attr('data-cg-type')=='image' && cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container').attr('data-cg-pdf-preview') == 0){
                    formPostData.append('cgSellContainer[base64WatermarkedAndAltFiles]['+WpUploadId+']', cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId]);
                    formPostData.append('cgSellContainer[base64WatermarkedAndAltFileTypes]['+WpUploadId+']', cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId]);
                    formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkTitle]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadId]['WatermarkTitle']);
                    formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkPosition]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadId]['WatermarkPosition']);
                    formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkSize]', cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadId]['WatermarkSize']);
                }
                if($form.find('.SaleType_download').prop('checked') && cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUploadId)>-1){
                    formPostData.append('cgSellContainer[WpUploadFilesForSale][]',cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_upload_id').val());
                }
            }
        }

        cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.forEach(function (WpUploadValue){
            formPostData.append('cgSellContainer[removedWpUploadIdsFromSale][]', WpUploadValue);
        });

        $('#cgSellFormLoaderContainer').removeClass('cg_hide');
        $form.addClass('cg_hide');

        cgJsClassAdmin.gallery.vars.ecommerce = cgJsClassAdmin.gallery.vars.ecommerce || {};
        cgJsClassAdmin.gallery.vars.ecommerce.activateSale = cgJsClassAdmin.gallery.vars.ecommerce.activateSale || {};
        cgJsClassAdmin.gallery.vars.ecommerce.activateSale.id = id;

        cgJsClassAdmin.gallery.vars.ecommerce.activateSale.formPostData = formPostData;

        cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();

        $('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');

        // disable delete if set for delete
        cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_delete').prop('disabled',true);

        // loader here also
        $('#cgSellContainer .cg_message_close').click();
        cgJsClassAdmin.gallery.reload.entry(id,true,true);

    });

});