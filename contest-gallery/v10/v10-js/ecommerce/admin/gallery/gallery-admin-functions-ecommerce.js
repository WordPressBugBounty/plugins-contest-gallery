cgJsClassAdmin.gallery.functions.addRemoveCgProFalse = function($cgSellContainer,isShipping){
    if($cgSellContainer.hasClass('cg-pro-false-sell-container') && !isShipping){
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

    var cg_multiple_files_for_post =  cgJsClassAdmin.gallery.functions.getMultipleFileForPost($sortableDiv,realId);

    $cgSellContainer.find('.cg_download_sale:not(#cgSellWatermarkPreview)').removeClass('cg_hide');
    debugger
    cgJsClassAdmin.gallery.functions.prepareDownloadDataToShow($cgSellContainer);
    if($sortableDiv.find('.cg_download_keys_csv_name').length){
        $cgSellContainer.find('#cgShowDownloadKeysFile').text($sortableDiv.find('.cg_download_keys_csv_name').val());
    }
    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').empty();

    if(cg_multiple_files_for_post){
        var firstCheckedImageOrder = 0;
        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                break;
            }
            var WpUpload = parseInt(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload']);
            var fileType = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['ImgType'];
            var NamePic = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['NamePic'];
            var PdfPreview = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['PdfPreview'];
            var PdfPreviewImageLarge = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['PdfPreviewImageLarge'];
            var cg_file_check = 'cg_unchecked';
            if((WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1 || cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1) && cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload)===-1){
                cg_file_check = 'cg_checked';
            }

            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {
                if(!firstCheckedImageOrder && cg_file_check=='cg_checked' && !(PdfPreview > 0)){// has to be tested that way: .PdfPreview > 0
                    firstCheckedImageOrder = order;
                }
                if(cg_file_check=='cg_checked'){
                    //$cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
                }
                if(PdfPreview > 0){// has to be tested that way: .PdfPreview > 0
                    var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,order,WpUpload,PdfPreviewImageLarge);
                    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container " data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="pdf"><div class="cg_file cg_file_pdf" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div></div>'));
                }else{
                    var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,order,WpUpload);
                    $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container cg_file_img_container" data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type+'"><div class="cg_file cg_file_img" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div></div>'));
                }


            }else{
                var cg_is_embed = '';
                if(fileType=='inst' || fileType=='tkt' || fileType=='twt' || fileType=='ytb'){
                    cg_is_embed = 'cg_is_embed';
                    NamePic = 'Social embed not selectable';
                }
                var cg_file_video = '';
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='video') {cg_file_video = 'cg_file_video';}
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container '+cg_is_embed+'" data-cg-real-id="'+realId+'" data-cg-order="'+order+'"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type+'"><div class="cg_file cg_file_'+fileType+' '+cg_file_video+' " ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div><span class="cg_post_title">'+NamePic+'</span></div>'));
            }

        }
        debugger
        if(firstCheckedImageOrder){
            setTimeout(function (){
                debugger
                $cgSellContainer.find('.cg_file_container[data-cg-order="'+firstCheckedImageOrder+'"] .cg_file_checkbox').removeClass('cg_checked').closest('.cg_file_img_container').click();
            },100);
        }
    }else{
        var WpUpload = parseInt($sortableDiv.find('.cg_wp_upload_id').val());
        var IsImageType = $sortableDiv.find('.IsImageType').val();
        var fileType = $sortableDiv.find('.cg_image_type_to_show').val();
        var cg_type = $sortableDiv.find('.cg_type').val();
        var cg_wp_post_title = $sortableDiv.find('.cg_wp_post_title').val();
        var cg_file_preview = $sortableDiv.find('.cg_file_preview').val();
        var PdfPreviewImageLarge = $sortableDiv.find('.cgPdfPreviewImageLarge').val();

        var cg_file_check = 'cg_unchecked';
        debugger
        if((WpUploadFilesForSaleArrayLoaded.indexOf(WpUpload)>-1 || cgJsClassAdmin.gallery.vars.WpUploadFilesForSaleArrayNew.indexOf(WpUpload)>-1) && cgJsClassAdmin.gallery.vars.removedWpUploadIdsFromSale.indexOf(WpUpload)===-1){
            cg_file_check = 'cg_checked';
        }

        if(IsImageType=='1' || cg_file_preview > 1) {
            if(cg_file_check=='cg_checked'){
                //$cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
            }
            if(PdfPreviewImageLarge){
                var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,1,WpUpload,PdfPreviewImageLarge);
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-is-image="0" data-cg-type="pdf"><div class="cg_file cg_file_pdf" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div></div>'));
            }else{
                var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($sortableDiv.find('.cg_backend_info_container'),realId,1,WpUpload);
                $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container cg_file_img_container" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-is-image="'+IsImageType+'" data-cg-type="'+fileType+'"><div class="cg_file cg_file_img" style="background-image: url('+backgroundUrl+')" ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div></div>'));
            }
        }else{
            var cg_file_video = '';
            if(cg_type=='video') {cg_file_video = 'cg_file_video';}
            $cgSellContainer.find( '#cgSellSelectFilesForSaleSelectContainer').append($('<div class="cg_file_container" data-cg-real-id="'+realId+'" data-cg-order="1"  data-cg-wp-upload="'+WpUpload+'" data-cg-type="'+fileType+'" data-cg-is-image="'+IsImageType+'" ><div class="cg_file cg_file_'+fileType+'  '+cg_file_video+' " ></div><div  class="cg_file_checkbox '+cg_file_check+'"></div><span class="cg_post_title">'+cg_wp_post_title+'</span></div>'));
        }

        if(cg_file_check=='cg_checked'){
            setTimeout(function (){
                // has to be done with timeout because watermark settings has to be initiated before
                // noo need to click append already checked
                //$cgSellContainer.find('.cg_file_container[data-cg-order="1"] .cg_file_checkbox').removeClass('cg_checked').closest('.cg_file_img_container').click();
            },100);
        }

    }
}
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
        value = value+Symbol;
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
        var $cgReloadEntryLoaderClone = jQuery('#cgReloadEntryLoader').clone().removeClass('cg_hide').removeAttr('id');
        $cgReloadEntryLoaderClone.attr('id','cgReloadEntryLoaderClone');
        cgJsClassAdmin.gallery.vars.$sortableDiv.addClass('cg_hide');
        cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone = $cgReloadEntryLoaderClone;
        $cgReloadEntryLoaderClone.insertBefore(cgJsClassAdmin.gallery.vars.$sortableDiv);
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
        cgJsClassAdmin.gallery.reload.entry(id);
    }).fail(function (xhr, status, error) {

        debugger

        var test = 1;

        cgJsClassAdmin.gallery.vars.newPreviewForEcommerceSale = '';

    }).always(function () {

        var test = 1;

    });
}