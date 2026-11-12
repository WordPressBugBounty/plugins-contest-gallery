cgJsClassAdmin.gallery.saleEdit = {
    changed: false,
    saving: false,
    generation: 0,
    afterClose: null,
    returnFocus: null,
    reset: function(){
        this.changed = false;
        this.saving = false;
        this.generation++;
        this.afterClose = null;
        this.returnFocus = null;
        jQuery('#cgSellDiscardConfirmation').addClass('cg_hide');
        jQuery('#cgSellContainer').removeAttr('inert');
        if(window.tinymce){
            jQuery('#cgSellForm .cg_view_option_textarea').each(function(){
                var editor = tinymce.get(this.id);
                if(editor){editor.setDirty(false);}
            });
        }
    },
    markChanged: function(){
        if(jQuery('#cgSellForm').is(':visible') && !jQuery('#cgSellForm').hasClass('cg_visibility_hidden')){
            this.changed = true;
        }
    },
    selectionState: function(){
        var $ = jQuery;
        // Custom option/file cards also change state without a native change event.
        return JSON.stringify({
            options: $('#cgSellForm input:checkbox,#cgSellForm input:radio').map(function(){return this.checked;}).get(),
            files: $('#cgSellForm .cg_file_container .cg_file_checkbox').map(function(){return $(this).hasClass('cg_checked');}).get(),
            downloadKeysRemoved: $('#cgRemoveDownloadKeysFileInput').val(),
            serviceKeysRemoved: $('#cgRemoveServiceKeysFileInput').val()
        });
    },
    hasChanges: function(){
        var changed = this.changed;
        if(window.tinymce){
            jQuery('#cgSellForm .cg_view_option_textarea').each(function(){
                var editor = tinymce.get(this.id);
                if(editor && editor.isDirty()){changed = true;}
            });
        }
        return changed;
    },
    close: function(force,afterClose){
        var $ = jQuery;
        if(!force && this.saving){return;}
        if(!force && this.hasChanges()){
            if($('#cgSellDiscardConfirmation').hasClass('cg_hide')){
                this.afterClose = afterClose;
                this.returnFocus = document.activeElement;
                $('#cgSellDiscardConfirmation').removeClass('cg_hide');
                $('#cgSellContainer').attr('inert','');
                $('#cgKeepEditingSale').trigger('focus');
            }
            return;
        }
        this.reset();
        $('#cgSellContainerFadeBackground').addClass('cg_hide').removeClass('cg_active');
        $('body').removeClass('cg_overflow_hidden');
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        if(afterClose){afterClose();}
    },
    keepEditing: function(){
        jQuery('#cgSellDiscardConfirmation').addClass('cg_hide');
        jQuery('#cgSellContainer').removeAttr('inert');
        this.afterClose = null;
        if(this.returnFocus && document.documentElement.contains(this.returnFocus)){
            this.returnFocus.focus({preventScroll:true});
        }
        this.returnFocus = null;
    }
};
cgJsClassAdmin.gallery.functions.scrollToSaleError = function(){
    var $container = jQuery('#cgSellContainer');
    var $error = $container.find('.cg_error:visible').first();
    if(!$error.length){return;}
    $container.stop(true).scrollTop(Math.max(0,$container.scrollTop()+$error.offset().top-$container.offset().top-20));
    $error.attr('tabindex','-1')[0].focus({preventScroll:true});
};
cgJsClassAdmin.gallery.functions.showSaleError = function(message,target){
    var $container = jQuery('#cgSellContainer');
    var $error = $container.find(target || '#cgSellActionError').text(message).removeClass('cg_hide');
    if(!$error.is(':visible')){$container.find('#cgSellActionError').text(message).removeClass('cg_hide');}
    cgJsClassAdmin.gallery.functions.scrollToSaleError();
};
cgJsClassAdmin.gallery.functions.restoreSaleAfterError = function(xhr,isSaved){
    var $ = jQuery;
    var data = xhr && xhr.responseJSON && xhr.responseJSON.data || {};
    var isNonceError = data.code === 'cg_nonce_invalid' || xhr && typeof xhr.responseText === 'string' &&
        (xhr.responseText.indexOf('cg_nonce_invalid') > -1 || $.trim(xhr.responseText) === '-1');
    var message = isNonceError ? 'Your session has expired. Reload this page before saving again.' :
        typeof data.message === 'string' && data.message ? data.message :
        xhr && xhr.status === 0 ? 'Connection lost. Your changes are still here. Please try again.' :
        'Sale settings could not be saved. Your changes are still here. Please try again.';
    if(isSaved){message = 'Sale settings were saved, but the gallery could not be refreshed. Reload this page.';}
    var vars = cgJsClassAdmin.gallery.vars;
    vars.newPreviewForEcommerceSale = '';
    $('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
    if(vars.$cgReloadEntryLoaderClone){vars.$cgReloadEntryLoaderClone.remove();}
    if(vars.$sortableDiv){
        vars.$sortableDiv.removeClass('cg_hide').find('.cg_delete').prop('disabled',false);
        cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded(vars.$sortableDiv);
    }
    $('#cgSellFormLoaderContainer').addClass('cg_hide');
    $('#cgSellForm').removeClass('cg_hide cg_visibility_hidden');
    $('#cgSellContainer').removeClass('cg_hide cg_overflow_y_hidden');
    cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
    cgJsClassAdmin.gallery.saleEdit.saving = false;
    if(isSaved){cgJsClassAdmin.gallery.saleEdit.changed = false;}
    var target = isSaved ? '#cgSellActionError' : data.code === 'cg_sale_price_invalid' || data.code === 'cg_sale_reduced_price_invalid' ? '#cgSellPriceError' :
        /^cg_sale_file/.test(data.code || '') || data.code === 'cg_entry_watermark_ecommerce_conflict' ? '#cgSellFilesError' : '#cgSellActionError';
    cgJsClassAdmin.gallery.functions.showSaleError(message,target);
};
cgJsClassAdmin.gallery.functions.checkSaleErrorResponse = function(response,isSaved){
    if(response && typeof response === 'object' && response.success === false){
        cgJsClassAdmin.gallery.functions.restoreSaleAfterError({responseJSON:response},isSaved);
        return true;
    }
    if(typeof response === 'string' && (response.indexOf('cg_nonce_invalid') > -1 || /^(?:0|-1)$/.test(jQuery.trim(response)))){
        cgJsClassAdmin.gallery.functions.restoreSaleAfterError({responseText:response},isSaved);
        return true;
    }
    return false;
};
cgJsClassAdmin.gallery.functions.setForSellButtonLabel = function($cgSellContainer){
    $cgSellContainer = $cgSellContainer || jQuery('#cgSellContainer');
    var wasActiveForSale = ($cgSellContainer.find('#cgSellForm').attr('data-cg-was-active-for-sale') == '1');
    $cgSellContainer.find('#cgSetForSell').val(wasActiveForSale ? 'Save changes' : 'Activate sale');
    $cgSellContainer.find('#cgDeactivateSale').toggleClass('cg_hide',!wasActiveForSale);
    cgJsClassAdmin.gallery.functions.syncSaleFileAvailability($cgSellContainer);
};
cgJsClassAdmin.gallery.functions.submitSaleForm = function($form,formPostData){
    var $ = jQuery;
    if(cgJsClassAdmin.gallery.saleEdit.saving){return;}
    cgJsClassAdmin.gallery.saleEdit.saving = true;
    var id = cgJsClassAdmin.gallery.vars.$sortableDiv.attr('data-cg-real-id');
    if(typeof formPostData.set === 'function'){
        formPostData.set('cg_nonce',CG1LBackendNonce.nonce);
    }else{
        formPostData.append('cg_nonce',CG1LBackendNonce.nonce);
    }
    $('#cgSellFormLoaderContainer').removeClass('cg_hide');
    $form.addClass('cg_hide');
    cgJsClassAdmin.gallery.vars.ecommerce = cgJsClassAdmin.gallery.vars.ecommerce || {};
    cgJsClassAdmin.gallery.vars.ecommerce.activateSale = {id:id,formPostData:formPostData};
    cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
    $('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');
    cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_delete').prop('disabled',true);
    $('#cgSellContainer').scrollTop(0);
    cgJsClassAdmin.gallery.reload.entry(id,true,true);
};
cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType = function(fileType){
    fileType = String(fileType || '').toLowerCase();
    return (fileType === 'jpg' || fileType === 'jpeg' || fileType === 'png');
}
cgJsClassAdmin.gallery.functions.setEntryWatermarkSellConflictNotice = function($cgSellContainer,hasConflict){
    var $notice = $cgSellContainer.find('#cgSellEntryWatermarkConflictNotice');
    if(!$notice.length){
        return;
    }
    if(hasConflict){
        $notice.removeClass('cg_hide');
    }else{
        $notice.addClass('cg_hide');
    }
}
cgJsClassAdmin.gallery.functions.addRemoveCgProFalse = function($cgSellContainer,isShipping){
    cgJsClassAdmin.gallery.functions.setForSellButtonLabel($cgSellContainer);
    var saleType = $cgSellContainer.find('.SaleType:checked').val();
    if($cgSellContainer.hasClass('cg-pro-false-sell-container') && saleType === 'upload' && !isShipping){
        $cgSellContainer.find('#TaxRadioContainerRow .cg_view_option').addClass('cg-pro-false');
        $cgSellContainer.find('#TaxContainerRow .cg_view_option').addClass('cg-pro-false');
        $cgSellContainer.find('#cgSellContainerSubmitButtonContainer').addClass('cg-pro-false');
    }else{
        $cgSellContainer.find('#TaxRadioContainerRow .cg_view_option').removeClass('cg-pro-false');
        $cgSellContainer.find('#TaxContainerRow .cg_view_option').removeClass('cg-pro-false');
        $cgSellContainer.find('#cgSellContainerSubmitButtonContainer').removeClass('cg-pro-false');
    }
}
cgJsClassAdmin.gallery.functions.initDownloadSale = function($,$cgSellContainer,$sortableDiv,realId){
    debugger
    // reset here
    if(!cgJsClassAdmin.gallery.vars.ecommerceDownloadInitiated){
        cgJsClassAdmin.gallery.vars.ecommerceDownloadInitiated = true;
        cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded = [];
        cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew = [];
        cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale = [];
    }

    var WpUploadFilesForSaleArrayLoaded = cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded($sortableDiv);
    var EntryWatermarkWpUploads = cgJsClassAdmin.gallery.functions.getEntryWatermarkWpUploads($sortableDiv);

    var cg_multiple_files_for_post =  cgJsClassAdmin.gallery.functions.getMultipleFileForPost($sortableDiv,realId);

    $cgSellContainer.find('.cg_download_sale:not(#cgSellWatermarkPreview)').removeClass('cg_hide');
    debugger
    cgJsClassAdmin.gallery.functions.prepareDownloadDataToShow($cgSellContainer);
    if($sortableDiv.find('.cg_download_keys_csv_name').length){
        $cgSellContainer.find('#cgShowDownloadKeysFile').text($sortableDiv.find('.cg_download_keys_csv_name').val());
    }
    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').empty();
    var hasEntryWatermarkConflict = false;
    var contexts = cgJsClassAdmin.gallery.functions.getSaleFileContexts($sortableDiv);

    if(cg_multiple_files_for_post){
        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                break;
            }
            var WpUpload = parseInt(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload']);
            var fileType = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['ImgType'];
            var NamePic = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['NamePic'];
            var PdfPreview = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['PdfPreview'];
            var PdfPreviewImageLarge = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['PdfPreviewImageLarge'];
            var isEntryWatermarked = EntryWatermarkWpUploads.indexOf(WpUpload)>-1;
            var isWatermarkSupported = cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType(fileType);
            var cg_file_check = 'cg_unchecked';
            if((WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1 || cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1) && cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload)===-1){
                cg_file_check = 'cg_checked';
            }
            if(isEntryWatermarked){
                cg_file_check = 'cg_unchecked';
                hasEntryWatermarkConflict = true;
            }
            var entryWatermarkLockedClass = isEntryWatermarked ? ' cg_is_entry_watermarked cg_disabled' : '';
            var entryWatermarkLockedText = isEntryWatermarked ? '<span class="cg_entry_watermark_ecommerce_note">Unwatermark first to watermark for selling</span>' : '';
            var watermarkUnsupportedClass = (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image' && !(PdfPreview > 0) && !isWatermarkSupported) ? ' cg_watermark_unsupported' : '';
            var watermarkUnsupportedText = watermarkUnsupportedClass ? '<span class="cg_entry_watermark_ecommerce_note">JPG/PNG watermark only</span>' : '';
            var watermarkSupportedAttr = ' data-cg-watermark-supported="'+(isWatermarkSupported ? '1' : '0')+'"';

            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {
                if(cg_file_check=='cg_checked'){
                    //$cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
                }
                if(PdfPreview > 0){// has to be tested that way: .PdfPreview > 0
                    var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,order,WpUpload,PdfPreviewImageLarge);
                    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container '+entryWatermarkLockedClass+'" data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="pdf" data-cg-watermark-supported="0"><div class="cg_file cg_file_pdf" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div>'+entryWatermarkLockedText+'</div>'));
                }else{
                    var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,order,WpUpload);
                    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container cg_file_img_container'+entryWatermarkLockedClass+watermarkUnsupportedClass+'" data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type+'"'+watermarkSupportedAttr+'><div class="cg_file cg_file_img" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div>'+entryWatermarkLockedText+watermarkUnsupportedText+'</div>'));
                }


            }else{
                var cg_is_embed = '';
                if(fileType=='inst' || fileType=='tkt' || fileType=='twt' || fileType=='ytb'){
                    cg_is_embed = 'cg_is_embed';
                    NamePic = 'Social embed not selectable';
                }
                var cg_file_video = '';
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='video') {cg_file_video = 'cg_file_video';}
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container '+cg_is_embed+entryWatermarkLockedClass+'" data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type+'" data-cg-watermark-supported="0"><div class="cg_file cg_file_'+fileType+' '+cg_file_video+' " ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div><span class="cg_post_title"></span>'+entryWatermarkLockedText+'</div>').find('.cg_post_title').text(NamePic).end());
            }

        }
    }else{
        var WpUpload = parseInt($sortableDiv.find('.cg_wp_upload_id').val());
        var IsImageType = $sortableDiv.find('.IsImageType').val();
        var fileType = $sortableDiv.find('.cg_image_type_to_show').val();
        var cg_type = $sortableDiv.find('.cg_type').val();
        var cg_wp_post_title = $sortableDiv.find('.cg_wp_post_title').val();
        var cg_file_preview = $sortableDiv.find('.cg_file_preview').val();
        var PdfPreviewImageLarge = $sortableDiv.find('.cgPdfPreviewImageLarge').val();
        var isEntryWatermarked = EntryWatermarkWpUploads.indexOf(WpUpload)>-1;
        var isWatermarkSupported = cgJsClassAdmin.gallery.functions.isWatermarkSellSupportedType(fileType);

        var cg_file_check = 'cg_unchecked';
        debugger
        if((WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1 || cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1) && cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload)===-1){
            cg_file_check = 'cg_checked';
        }
        if(isEntryWatermarked){
            cg_file_check = 'cg_unchecked';
            hasEntryWatermarkConflict = true;
        }
        var entryWatermarkLockedClass = isEntryWatermarked ? ' cg_is_entry_watermarked cg_disabled' : '';
        var entryWatermarkLockedText = isEntryWatermarked ? '<span class="cg_entry_watermark_ecommerce_note">Unwatermark first to watermark for selling</span>' : '';
        var watermarkUnsupportedClass = (IsImageType=='1' && !isWatermarkSupported) ? ' cg_watermark_unsupported' : '';
        var watermarkUnsupportedText = watermarkUnsupportedClass ? '<span class="cg_entry_watermark_ecommerce_note">JPG/PNG watermark only</span>' : '';
        var watermarkSupportedAttr = ' data-cg-watermark-supported="'+(isWatermarkSupported ? '1' : '0')+'"';

        if(IsImageType=='1' || cg_file_preview > 1) {
            if(cg_file_check=='cg_checked'){
                //$cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
            }
            if(PdfPreviewImageLarge){
                var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,1,WpUpload,PdfPreviewImageLarge);
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container'+entryWatermarkLockedClass+'" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-is-image="0" data-cg-type="pdf" data-cg-watermark-supported="0"><div class="cg_file cg_file_pdf" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div>'+entryWatermarkLockedText+'</div>'));
            }else{
                var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,1,WpUpload);
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container cg_file_img_container'+entryWatermarkLockedClass+watermarkUnsupportedClass+'" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-is-image="'+IsImageType+'" data-cg-type="'+fileType+'"'+watermarkSupportedAttr+'><div class="cg_file cg_file_img" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div>'+entryWatermarkLockedText+watermarkUnsupportedText+'</div>'));
            }
        }else{
            var cg_file_video = '';
            if(cg_type=='video') {cg_file_video = 'cg_file_video';}
            var cg_is_embed = cgJsClassAdmin.gallery.functions.isEmbed(fileType) ? ' cg_is_embed' : '';
            $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container'+cg_is_embed+entryWatermarkLockedClass+'" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+fileType+'" data-cg-is-image="'+IsImageType+'" data-cg-watermark-supported="0"><div class="cg_file cg_file_'+fileType+'  '+cg_file_video+' " ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div><span class="cg_post_title"></span>'+entryWatermarkLockedText+'</div>').find('.cg_post_title').text(cg_wp_post_title).end());
        }

    }
    $cgSellContainer.find('.cg_file_container').each(function(){
        var $tile = $(this), context = contexts[$tile.attr('data-cg-wp-upload')];
        if(!context || !context.locked){return;}
        $tile.addClass('cg_is_sale_owned_elsewhere').attr('aria-disabled','true');
        $tile.find('.cg_file_checkbox').remove();
        var $note = $('<span class="cg_entry_watermark_ecommerce_note cg_sale_owner_note"></span>').text(context.owner ? 'Managed in another sale. ' : 'Sale assignment needs review. ').appendTo($tile);
        cgJsClassAdmin.gallery.functions.appendSaleOwnerLink($note,context);
    });
    cgJsClassAdmin.gallery.functions.syncSaleFileAvailability($cgSellContainer);
    cgJsClassAdmin.gallery.functions.setEntryWatermarkSellConflictNotice($cgSellContainer,hasEntryWatermarkConflict);
}
cgJsClassAdmin.gallery.functions.ensureDownloadSaleFile = function($container){
    var $files = $container.find('.cg_file_container:not(.cg_is_embed):not(.cg_is_entry_watermarked):not(.cg_is_sale_owned_elsewhere)');
    if($container.find('.SaleType_download').prop('checked') && !$files.find('.cg_file_checkbox.cg_checked').length){
        // Run after watermark settings and any saved draft selections have been restored.
        $files.first().trigger('click');
    }else if($container.find('.SaleType_download').prop('checked')){
        cgJsClassAdmin.gallery.functions.showSavedSaleWatermarkPreview($files.filter('[data-cg-watermark-supported="1"]').find('.cg_file_checkbox.cg_checked').first().closest('.cg_file_container'));
    }
};
cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded = function($sortableDiv){
    var WpUploadFilesForSaleArrayLoaded = [];
    if($sortableDiv.find('.WpUploadFilesForSale').val()){
        var WpUploadFilesForSale = JSON.parse($sortableDiv.find('.WpUploadFilesForSale').val());
        for(var index in WpUploadFilesForSale){
            if(!WpUploadFilesForSale.hasOwnProperty(index)){
                break;
            }
            WpUploadFilesForSaleArrayLoaded.push(parseInt(WpUploadFilesForSale[index]));
        }
    }
    cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayLoaded = WpUploadFilesForSaleArrayLoaded;
    return WpUploadFilesForSaleArrayLoaded;
}
cgJsClassAdmin.gallery.functions.getEntryWatermarkWpUploads = function($sortableDiv){
    var EntryWatermarkWpUploads = [];
    if($sortableDiv.find('.cgEntryWatermarkWpUploads').val()){
        try{
            var parsed = JSON.parse($sortableDiv.find('.cgEntryWatermarkWpUploads').val());
        }catch(e){
            var parsed = [];
        }
        for(var index in parsed){
            if(!parsed.hasOwnProperty(index)){
                break;
            }
            EntryWatermarkWpUploads.push(parseInt(parsed[index]));
        }
    }
    return EntryWatermarkWpUploads;
}
cgJsClassAdmin.gallery.functions.setDivider = function (value) {
    var PriceDivider = cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider
    if(PriceDivider==','){
        value = value.replace('.',',');
    }
    return value;
}
cgJsClassAdmin.gallery.functions.setSymbolByPosition = function (value,Symbol) {
    var CurrencyPosition = cgJsClassAdmin.gallery.currencyInputFunctions.CurrencyPosition;
    if(CurrencyPosition=='left'){
        value = Symbol+value;
    }else if(CurrencyPosition=='right'){
        value = value+'\u00A0'+Symbol;
    }
    return value;
}
cgJsClassAdmin.gallery.functions.inputCurrency = function($,$element,e){

    var inputVal = String($element.val());

    var selectionStart = e.target.selectionStart;
    var selectionEnd = e.target.selectionEnd;

    //from: https://codepen.io/559wade/pen/LRzEjj

    if(e.originalEvent.data==cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider && $element.hasClass('cg_currency_input_decimal_disallowed')){
        $element.val(inputVal.replace(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider, ''));
        e.target.selectionStart = selectionStart-1;
        e.target.selectionEnd = selectionEnd-1;
        return;
    //}else if($element.hasClass('cg_currency_input_tax') && parseInt($element.val())>=100){
    }else if(e.originalEvent.data==cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider && !$element.hasClass('cg_currency_input_decimal_disallowed') && (inputVal.match(/,/g) || []).length > 1){
        console.log('selectionStart');
        console.log(selectionStart);
        var regex = /,/gi, result, indices = [];
        var indexOccurrence = 1;
        var firstOccurrence = false;
        var lastOccurrence = false;
        while ((result = regex.exec(inputVal)) ) {
            //indices.push(result.index);
            console.log('pos');
            console.log(result.index);
            if((result.index+1)==selectionStart && indexOccurrence==1){
                firstOccurrence = true;
            }
            if((result.index+1)==selectionStart && indexOccurrence==2){
                lastOccurrence = true;
            }
            indexOccurrence++;
        }
        console.log('firstOccurrence')
        console.log(firstOccurrence)
        console.log('lastOccurrence')
        console.log(lastOccurrence)
        if(firstOccurrence){// Remove the first one
            var lastIndexOfL = inputVal.lastIndexOf(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider);
            var newInputVal = inputVal.slice(0, lastIndexOfL) +inputVal.slice(lastIndexOfL + 1);
            console.log('newInputVal');
            console.log(newInputVal);
            $element.val(newInputVal);
        }
        if(lastOccurrence){
            $element.val(inputVal.replace(cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider, ''));
        }
        e.target.selectionStart = selectionStart-1;
        e.target.selectionEnd = selectionEnd-1;
    }

    if(inputVal.length>=50){
        $element.val(inputVal.slice(0, -1));
        e.target.selectionStart = selectionStart-1;
        e.target.selectionEnd = selectionEnd-1;
        cgJsClassAdmin.gallery.currencyInputFunctions.setCurNew($element,undefined,undefined,e);
        return;
    }

    console.log('$element.val()');
    console.log($element.val());

    cgJsClassAdmin.gallery.currencyInputFunctions.setCurNew($element,undefined,undefined,e);

}
cgJsClassAdmin.gallery.functions.removeEcommerceFromMultipleFile = function($,$element){

    var realId = $element.attr('data-cg-real-id');
    var WpUploadId = $element.attr('data-cg-wp-upload-id');
    var $cg_preview_files_container = $element.closest('.cg_preview_files_container');
    var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$cg_backend_info_container;

    $element.closest('.cg_backend_image_full_size_target_container').remove();
    var order = 1;
    var newOrderedData = {};
    var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
    var isRealIdSourceDeleted = true;

    for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
        if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
            break;
        }
        if(WpUploadId == cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload &&
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isEcommerceEntryAndDownload){
            var ecommerceEntryAndDownload = $cg_backend_info_container.find('.ecommerceEntryAndDownload').val();
            if(ecommerceEntryAndDownload && Object.keys(ecommerceEntryAndDownload).length){
                var ecommerceEntryAndDownloadJson = JSON.parse(ecommerceEntryAndDownload);
                ecommerceEntryAndDownloadJson[(Object.keys(ecommerceEntryAndDownloadJson).length-1)] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload;
            }else{
                var ecommerceEntryAndDownloadJsonString = JSON.stringify({0:cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload});
                $cg_backend_info_container.find('.ecommerceEntryAndDownload').val(ecommerceEntryAndDownloadJsonString);
            }

            var $removeEcommerceEntryWpUploadIds = $cg_backend_info_container.find('.removeEcommerceEntryWpUploadIds');
            debugger
            $removeEcommerceEntryWpUploadIds.prop('disabled',false);
            if($removeEcommerceEntryWpUploadIds.val()){
                var removeEcommerceEntryWpUploadIdsArray = JSON.parse($removeEcommerceEntryWpUploadIds.val());
                removeEcommerceEntryWpUploadIdsArray.push(WpUploadId);
            }else{
                var removeEcommerceEntryWpUploadIdsArray = [];
                removeEcommerceEntryWpUploadIdsArray.push(WpUploadId);
            }
            $removeEcommerceEntryWpUploadIds.val(JSON.stringify(removeEcommerceEntryWpUploadIdsArray));

        }
    }

    $cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container .cg_backend_image_full_size_target_container_position').each(function (){
        if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][$(this).text()].isRealIdSource){isRealIdSourceDeleted=false;}
        newOrderedData[order] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][$(this).text()];
        $cg_backend_info_container.find('.ecommerceEntryAndDownload').prop('disabled',false);
        $(this).text(order);
        order++;
    });

    cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = newOrderedData;
    cgJsClassAdmin.gallery.functions.resortMultipleFiles(realId);

    console.log('new data');
    console.log(newOrderedData);

    if($cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container').length>1){
        $cg_backend_info_container.find('.cg_manage_multiple_files_for_post').text('+'+($cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container').length-1));
    }

    if($cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container').length==1){
        $cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container_remove').remove();
        /*            $cgMultipleFilesForPostContainer.find('.cg_message_close').click();
                    $cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container_remove').addClass('cg_hide');
                    $cg_backend_info_container.find('.cg_manage_multiple_files_for_post, .cg_manage_multiple_files_for_post_prev').addClass('cg_hide');*/
    }

    //  can be done so or so
    $cg_backend_info_container.find('.cg_add_multiple_files_to_post, .cg_add_multiple_files_to_post_prev').removeClass('cg_hide');

    cgJsClassAdmin.gallery.functions.hideShowAddAdditionalFilesLabel($cg_preview_files_container,realId,$cg_backend_info_container);

}
cgJsClassAdmin.gallery.functions.prepareDownloadDataToShow = function ($cgSellContainer){
    $cgSellContainer.find('#ShippingContainerRow,#SaleAmountContainer,#ServiceContainerRow,#cgSellWatermarkPreview').addClass('cg_hide');
    debugger
    $cgSellContainer.find('#cgSellSelectFilesForSale,#DownloadContainerRow').removeClass('cg_hide');
}
cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv = function (){
    // cgReloadEntryLoaderClone might already exist through save new download ecommerce files from multiple files view
    if(!jQuery('#cgReloadEntryLoaderClone').length){
        var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
        var sortableDivHeight = $sortableDiv.outerHeight();
        var sortableDivMarginBottom = parseInt($sortableDiv.css('margin-bottom'),10) || 0;
        var $cgReloadEntryLoaderClone = jQuery('#cgReloadEntryLoader').clone().removeClass('cg_hide').removeAttr('id');
        $cgReloadEntryLoaderClone.attr('id','cgReloadEntryLoaderClone');
        $cgReloadEntryLoaderClone.css({
            width: '100%',
            'box-sizing': 'border-box',
            'min-height': sortableDivHeight + 'px',
            'margin-bottom': sortableDivMarginBottom + 'px'
        });
        $sortableDiv.addClass('cg_hide');
        cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone = $cgReloadEntryLoaderClone;
        $cgReloadEntryLoaderClone.insertBefore($sortableDiv);
    }
}
cgJsClassAdmin.gallery.functions.setForSellAjaxProcessing = function (formPostData){
    var $ = jQuery;
    var id = cgJsClassAdmin.gallery.vars.ecommerce.activateSale.id;
/*    $.ajax({
        url: 'admin-ajax.php',
        method: 'post',
        data: formPostData,
        dataType: null,
        contentType: false,
        processData: false
    }).done(function (response) {*/

    $.ajax({
        url: 'admin-ajax.php',
        method: 'post',
        data: formPostData,
        dataType: null,
        contentType: false,
        processData: false
    }).done(function (response) {
        if(cgJsClassAdmin.gallery.functions.checkSaleErrorResponse(response)){return;}
        cgJsClassAdmin.gallery.saleEdit.close(true);
        cgJsClassAdmin.gallery.reload.entry(id,false,true);
    }).fail(function (xhr, status, error) {
        cgJsClassAdmin.gallery.functions.restoreSaleAfterError(xhr);
    });
}

// Draft values only prefill the admin form. They never mark an entry as actively for sale.
cgJsClassAdmin.gallery.functions.applySaleDraft = function($entry){
    var draft = {};
    if($entry.find('.cg_ecommerce_entry').val() == '0'){
        try{draft = JSON.parse($entry.find('.cgSaleDraft').val() || '{}');}catch(e){draft = {};}
    }
    var fields = ['Price','RegularPrice','IsFree','SaleType','SaleTitle','SaleDescription','HasTax','IsAlternativeShipping','AlternativeShipping','SaleAmountMin','SaleAmountMax','UploadGallery','MaxUploads','AllUploadsUsedText'];
    fields.forEach(function(field){if(draft[field] !== undefined){$entry.find('.'+field).val(draft[field]);}});
    if(draft.TaxPercentage !== undefined){$entry.find('.Tax').val(draft.TaxPercentage);}
    if(draft.WatermarkSettings){$entry.find('.WatermarkSettings').val(JSON.stringify(draft.WatermarkSettings));}
    return draft;
};
cgJsClassAdmin.gallery.functions.prepareSaleDraftFiles = function($,$container,$entry,realId){
    var vars = cgJsClassAdmin.gallery.vars;
    if(vars.saleDraftFilesPrepared || $entry.find('.cg_ecommerce_entry').val() != '0'){return;}
    var draft = cgJsClassAdmin.gallery.functions.applySaleDraft($entry);
    if(!draft.WpUploadFilesForSale || !draft.WpUploadFilesForSale.length){return;}
    vars.saleDraftFilesPrepared = true;
    vars.base64andAltFileValues[realId] = {};
    vars.base64andAltFileTypes[realId] = {};
    var generation = vars.salePreviewGeneration;
    var jobs = [];
    draft.WpUploadFilesForSale.forEach(function(uploadId){
        uploadId = parseInt(uploadId);
        var $tile = $container.find('.cg_file_container[data-cg-wp-upload="'+uploadId+'"]').not('.cg_is_entry_watermarked,.cg_is_embed,.cg_is_sale_owned_elsewhere');
        if(!$tile.length){return;}
        $tile.find('.cg_file_checkbox').removeClass('cg_unchecked').addClass('cg_checked');
        if(vars.WpUploadFilesForSaleArrayNew.indexOf(uploadId)<0){vars.WpUploadFilesForSaleArrayNew.push(uploadId);}
        if($tile.attr('data-cg-watermark-supported') !== '1'){return;}
        var settings = vars.WatermarkSettings[uploadId];
        settings.on = true;
        var order = $tile.attr('data-cg-order');
        var fileType = vars.multipleFilesForPost[realId] ? vars.multipleFilesForPost[realId][order].ImgType : $entry.find('.cg_image_type_to_show').val();
        var source = cgJsClassAdmin.gallery.getBackgroundUrl($entry.find('.cg_backend_info_container'),realId,order,uploadId);
        jobs.push(cgJsClassAdmin.gallery.functions.renderWatermarkImage(source,settings.WatermarkTitle,settings.WatermarkPosition,settings.WatermarkSize,'#fff',0.5).then(function(img){
            if(vars.salePreviewGeneration !== generation){return;}
            vars.base64andAltFileValues[realId][uploadId] = $(img).attr('src');
            vars.base64andAltFileTypes[realId][uploadId] = fileType;
        }));
    });
    vars.saleDraftWatermarkPending = jobs.length > 0;
    Promise.all(jobs).then(function(){
        if(vars.salePreviewGeneration !== generation){return;}
        vars.saleDraftWatermarkPending = false;
        $container.find('.cg_file_container[data-cg-watermark-supported="1"] .cg_checked').first().closest('.cg_file_container').trigger('click');
    }).catch(function(){
        if(vars.salePreviewGeneration !== generation){return;}
        vars.saleDraftWatermarkPending = false;
        if(!Object.keys(vars.salePreviewJobs || {}).length){$container.find('#cgSellPreviewContainerLoader').addClass('cg_hide');}
        cgJsClassAdmin.gallery.functions.showSaleError('A preview could not be loaded. Reopen Sales settings and try again.','#cgSellFilesError');
    });
};

// Ownership is rendered by PHP. This data controls presentation, never server authorization.
cgJsClassAdmin.gallery.functions.getSaleFileContexts = function($entry){
    try{return JSON.parse($entry.find('.cgSaleFileContexts').val() || '{}');}catch(e){return {};}
};
cgJsClassAdmin.gallery.functions.appendSaleOwnerLink = function($target,context){
    if(context && context.owner){
        jQuery('<a class="cg_sale_owner_link" target="_blank" rel="noopener"></a>')
            .attr('href',context.owner.url).text('Gallery '+context.owner.gallery_id+', entry '+context.owner.entry_id).appendTo($target);
    }
};
cgJsClassAdmin.gallery.functions.syncSaleFileAvailability = function($container){
    var download = $container.find('.SaleType_download').prop('checked');
    var blocked = download && $container.find('.cg_is_sale_owned_elsewhere').length > 0 &&
        !$container.find('.cg_file_container:not(.cg_is_embed):not(.cg_is_entry_watermarked):not(.cg_is_sale_owned_elsewhere)').length;
    $container.find('#cgSetForSell').prop('disabled',!!blocked);
    $container.find('#cgSellFileOwnershipNotice').toggleClass('cg_hide',!blocked);
};
cgJsClassAdmin.gallery.functions.beginSalePreview = function(uploadId){
    var vars = cgJsClassAdmin.gallery.vars;
    vars.salePreviewJobs = vars.salePreviewJobs || {};
    vars.salePreviewFailures = vars.salePreviewFailures || {};
    var ticket = {uploadId:uploadId,generation:vars.salePreviewGeneration};
    vars.salePreviewJobs[uploadId] = ticket;
    delete vars.salePreviewFailures[uploadId];
    return ticket;
};
cgJsClassAdmin.gallery.functions.finishSalePreview = function(ticket,failed){
    var vars = cgJsClassAdmin.gallery.vars;
    if(vars.salePreviewGeneration !== ticket.generation || !vars.salePreviewJobs || vars.salePreviewJobs[ticket.uploadId] !== ticket){return false;}
    delete vars.salePreviewJobs[ticket.uploadId];
    if(failed){
        vars.salePreviewFailures[ticket.uploadId] = true;
        var id = vars.$sortableDiv.find('.cg_real_id').val();
        if(vars.base64andAltFileValues[id]){delete vars.base64andAltFileValues[id][ticket.uploadId];}
        if(vars.base64andAltFileTypes[id]){delete vars.base64andAltFileTypes[id][ticket.uploadId];}
        vars.cgSellContainer.find('#cgSellPreviewContainerLoader').toggleClass('cg_hide',!Object.keys(vars.salePreviewJobs).length);
        cgJsClassAdmin.gallery.functions.showSaleError('A preview could not be loaded. Reopen Sales settings and try again.','#cgSellFilesError');
    }
    return true;
};
