jQuery(document).ready(function ($) {


    $(document).on('click','#cgDeactivateSale',function () {
        var $container = $('#cgSellContainer');
        if($container.find('#cgSellForm').attr('data-cg-was-active-for-sale') !== '1'){return;}
        var $entry = cgJsClassAdmin.gallery.vars.$sortableDiv;
        $container.find('#cgSellDeactivateDownloadNote').toggleClass('cg_hide',$entry.find('.SaleType').val() !== 'download');
        $container.find('#cgSellDeactivateKeysNote').toggleClass('cg_hide',!$entry.find('.cg_download_keys_csv_name,.cg_service_keys_csv_name').length);
        $container.find('#cgSellActions').addClass('cg_hide');
        $container.find('#cgSellDeactivateConfirmation').removeClass('cg_hide');
        $(this).attr('aria-expanded','true');
        $container.find('#cgCancelDeactivateSale').trigger('focus');
        $container.stop(true).scrollTop($container[0].scrollHeight);
    });
    $(document).on('click','#cgCancelDeactivateSale',function () {
        var $container = $('#cgSellContainer');
        $container.find('#cgSellDeactivateConfirmation').addClass('cg_hide');
        $container.find('#cgSellActions').removeClass('cg_hide');
        $container.find('#cgDeactivateSale').attr('aria-expanded','false').trigger('focus');
    });
    $(document).on('click','#cgConfirmDeactivateSale',function () {
        var $form = $(this).closest('form');
        if($form.attr('data-cg-was-active-for-sale') !== '1' || $form.find('#cgSellDeactivateConfirmation').hasClass('cg_hide')){return;}
        // Deactivation needs only identity and authorization, never incomplete product fields.
        var formPostData = new FormData();
        ['action','cgGalleryHash','cgSellContainer[GalleryID]','cgSellContainer[realId]'].forEach(function(name){
            formPostData.append(name,$form.find('[name="'+name+'"]').val());
        });
        formPostData.append('cgSellContainer[saleAction]','deactivate');
        cgJsClassAdmin.gallery.functions.submitSaleForm($form,formPostData);
    });
    $(document).on('submit','#cgSellForm',function(e){
        e.preventDefault();
        if($(this).find('#cgSellDeactivateConfirmation').hasClass('cg_hide')){
            $(this).find('#cgSetForSell').trigger('click');
        }
    });

    $(document).on('click','#cgSetForSell',function (e) {
        e.preventDefault();

        var $form = $(this).closest('form');
        if(!$form.find('#cgSellDeactivateConfirmation').hasClass('cg_hide')){return;}
        $form.find('#cgSellActionError,#cgSellPriceError,#cgSellFilesError').addClass('cg_hide');
        var freeOffer = $form.find('#cgOfferForFree').prop('checked');
        var priceInput = $.trim($form.find('#cgSellPrice').val());
        var divider = cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider;
        var normalizedPrice = priceInput.split(divider === ',' ? '.' : ',').join('').replace(',','.');
        if(!freeOffer && (!/^\d+(?:\.\d{1,2})?$/.test(normalizedPrice) || !(parseFloat(normalizedPrice) > 0))){
            cgJsClassAdmin.gallery.functions.showSaleError('Enter a price greater than 0 or choose Offer for free.','#cgSellPriceError');
            return;
        }
        var reducedOffer = $form.find('#cgOfferReducedPrice').prop('checked');
        var normalizedReducedPrice = $.trim($form.find('#cgSellReducedPrice').val()).split(divider === ',' ? '.' : ',').join('').replace(',','.');
        if(normalizedReducedPrice === '' || /^0+(?:\.0{1,2})?$/.test(normalizedReducedPrice)){
            reducedOffer = false;
        }
        if(reducedOffer && (freeOffer || !/^\d+(?:\.\d{1,2})?$/.test(normalizedReducedPrice) || !(Number(normalizedReducedPrice) > 0 && Number(normalizedReducedPrice) < Number(normalizedPrice)))){
            var errorText = 'Enter a reduced price greater than 0 and lower than the regular price.';
            cgJsClassAdmin.gallery.functions.showSaleError(errorText,'#cgSellPriceError');
            return;
        }
        $form.find('.cg_key_upload:visible').trigger('change');
        if($form.find('.cg_key_upload_option .cg_error:visible').length){
            cgJsClassAdmin.gallery.functions.scrollToSaleError();
            return;
        }
        if(freeOffer){$form.find('#cgSellPrice').val('0');}
        $form.find('#cgSellAction').val('activate');

        var TaxEntry = $form.find('#Tax').val();
        var realId = $form.find('#cgRealId').val();
        var isDownloadSaleActive = ($form.find('.SaleType_download').prop('checked'));
        var base64andAltFileValues = cgJsClassAdmin.gallery.vars.base64andAltFileValues || {};
        var base64andAltFileTypes = cgJsClassAdmin.gallery.vars.base64andAltFileTypes || {};
        var base64andAltFileValuesForRealId = base64andAltFileValues[realId] || {};
        var base64andAltFileTypesForRealId = base64andAltFileTypes[realId] || {};
        var WatermarkSettings = cgJsClassAdmin.gallery.vars.WatermarkSettings || {};
        var WpUploadFilesForSaleArrayNew = cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew || [];

        // required correction here
        $form.find('.cg_file_container.cg_is_embed .cg_file_checkbox.cg_checked').removeClass('cg_checked').addClass('cg_unchecked');

        if(isDownloadSaleActive && !$form.find('.cg_file_container:not(.cg_is_embed):not(.cg_is_entry_watermarked):not(.cg_is_sale_owned_elsewhere) .cg_file_checkbox.cg_checked').length){
            cgJsClassAdmin.gallery.functions.showSaleError('Select at least one available download file. Add a file or remove its existing watermark if needed.','#cgSellFilesError');
            return;
        }

        if(isDownloadSaleActive){
            var vars = cgJsClassAdmin.gallery.vars;
            var failed = false, pending = false;
            $form.find('.cg_file_container .cg_file_checkbox.cg_checked').each(function(){
                var uploadId = $(this).closest('.cg_file_container').attr('data-cg-wp-upload');
                failed = failed || !!(vars.salePreviewFailures && vars.salePreviewFailures[uploadId]);
                pending = pending || !!(vars.salePreviewJobs && vars.salePreviewJobs[uploadId]);
            });
            if(failed || pending){
                cgJsClassAdmin.gallery.functions.showSaleError(pending ? 'The watermark preview is still processing. Wait a moment, then save again.' : 'The watermark preview could not be prepared. Reopen Sales settings and try again.','#cgSellFilesError');
                return;
            }
            var isWatermarkProcessing = !$form.find('#cgSellWatermarkPreview').hasClass('cg_hide') && !$form.find('#cgSellPreviewContainerLoader').hasClass('cg_hide');
            var isNewWatermarkDataMissing = false;

            $form.find('.cg_file_container[data-cg-watermark-supported="1"] .cg_file_checkbox.cg_checked').each(function (){
                var WpUploadId = parseInt($(this).closest('.cg_file_container').attr('data-cg-wp-upload'));
                if(
                    WpUploadFilesForSaleArrayNew.indexOf(WpUploadId)>-1 &&
                    (!base64andAltFileValuesForRealId[WpUploadId] || !base64andAltFileTypesForRealId[WpUploadId])
                ){
                    isNewWatermarkDataMissing = true;
                    return false;
                }
            });

            if(isWatermarkProcessing || cgJsClassAdmin.gallery.vars.saleDraftWatermarkPending){
                cgJsClassAdmin.gallery.functions.showSaleError('The watermark preview is still processing. Wait a moment, then save again.','#cgSellFilesError');
                return;
            }
            if(isNewWatermarkDataMissing){
                cgJsClassAdmin.gallery.functions.showSaleError('The watermark preview could not be prepared. Reopen Sales settings and try again.','#cgSellFilesError');
                return;
            }
        }

        // Keep the rendered entry unchanged until the server saves and reloads it.
        var $cgSellContainer = $('#cgSellContainer');

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
        // Normalize request values without changing the displayed inputs after a failed save.
        formPostData.set('cgSellContainer[PriceResult]',freeOffer ? '0' : normalizedPrice);
        formPostData.set('cgSellContainer[HasReducedPrice]',reducedOffer ? '1' : '0');
        formPostData.set('cgSellContainer[ReducedPrice]',reducedOffer ? normalizedReducedPrice : '');
        var firstBase64OrFile;
        var firstBase64OrFileType;

        //  reset for sure in this cases
        if(!isDownloadSaleActive){
            delete base64andAltFileValues[realId];
            delete base64andAltFileTypes[realId];
            cgJsClassAdmin.gallery.vars.base64andAltFileValues = base64andAltFileValues;
            cgJsClassAdmin.gallery.vars.base64andAltFileTypes = base64andAltFileTypes;
            cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale = [];
            cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew = [];
            cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] = null;
        }
        // can be reseted anyway
        cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded = [];

        var order = 1;
        debugger
        if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && isDownloadSaleActive){
            for(var WpUploadId in base64andAltFileValuesForRealId){
                if(!base64andAltFileValuesForRealId.hasOwnProperty(WpUploadId)){break;}
                firstBase64OrFile = base64andAltFileValuesForRealId[WpUploadId];
                firstBase64OrFileType  = base64andAltFileTypesForRealId[WpUploadId];
                if(cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType(firstBase64OrFileType)){
                    formPostData.append('cgSellContainer[base64WatermarkedAndAltFiles]['+WpUploadId+']', firstBase64OrFile);
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
                var fileType = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].ImgType;
                // removedWpUploadIdsFromSale if images should not be watermarked anymore
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image' && cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType(fileType)) {
                    if(WatermarkSettings[WpUpload] && WatermarkSettings[WpUpload]['on'] ){
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkTitle]', WatermarkSettings[WpUpload]['WatermarkTitle']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkPosition]', WatermarkSettings[WpUpload]['WatermarkPosition']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUpload+'][WatermarkSize]', WatermarkSettings[WpUpload]['WatermarkSize']);
                    }
                }
                debugger
                if(WpUploadFilesForSaleArrayNew.indexOf(parseInt(WpUpload))>-1){
                    debugger
                    formPostData.append('cgSellContainer[WpUploadFilesForSale][]',WpUpload);
                }
            }
        }else{
            if(isDownloadSaleActive){
                var WpUploadId = parseInt(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_upload_id').val());
                debugger
                var fileType = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container').attr('data-cg-type-short');
                if(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container').attr('data-cg-type')=='image' && cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container').attr('data-cg-pdf-preview') == 0 && cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType(fileType)){
                    if(base64andAltFileValuesForRealId[WpUploadId] && base64andAltFileTypesForRealId[WpUploadId]){
                        formPostData.append('cgSellContainer[base64WatermarkedAndAltFiles]['+WpUploadId+']', base64andAltFileValuesForRealId[WpUploadId]);
                        formPostData.append('cgSellContainer[base64WatermarkedAndAltFileTypes]['+WpUploadId+']', base64andAltFileTypesForRealId[WpUploadId]);
                    }
                    if(WatermarkSettings[WpUploadId]){
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkTitle]', WatermarkSettings[WpUploadId]['WatermarkTitle']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkPosition]', WatermarkSettings[WpUploadId]['WatermarkPosition']);
                        formPostData.append('cgSellContainer[WatermarkSettings]['+WpUploadId+'][WatermarkSize]', WatermarkSettings[WpUploadId]['WatermarkSize']);
                    }
                }
                if(WpUploadFilesForSaleArrayNew.indexOf(WpUploadId)>-1){
                    formPostData.append('cgSellContainer[WpUploadFilesForSale][]',cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_wp_upload_id').val());
                }
            }
        }

        cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.forEach(function (WpUploadValue){
            formPostData.append('cgSellContainer[removedWpUploadIdsFromSale][]', WpUploadValue);
        });

        cgJsClassAdmin.gallery.functions.submitSaleForm($form,formPostData);

    });

});
