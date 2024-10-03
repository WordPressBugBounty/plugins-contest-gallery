cgJsClassAdmin.gallery.watermarkSellPreview = "<div id='cgSellPreview' class='cgSellPreview cg_hide' ></div>";

cgJsClassAdmin.gallery.functions.initWatermarkSettings = function($,$cgSellContainer,$sortableDiv,realId){
    debugger
    // reset here
    var WatermarkTitle = (localStorage.getItem('cgWatermarkTitle')) ? localStorage.getItem('cgWatermarkTitle') : 'Contest Gallery';
    var WatermarkTitleDefault = WatermarkTitle;
    var WatermarkSize = (localStorage.getItem('cgWatermarkSize')) ? localStorage.getItem('cgWatermarkSize') : '128';
    var WatermarkSizeDefault = WatermarkSize;
    var WatermarkPosition = (localStorage.getItem('cgWatermarkPosition')) ? localStorage.getItem('cgWatermarkPosition') : 'center';
    var WatermarkPositionDefault = WatermarkPosition;

    if($sortableDiv.find('.WatermarkSettings').val()){
        var WatermarkSettings = JSON.parse($sortableDiv.find('.WatermarkSettings').val());
    }else{
        var WatermarkSettings = {};
    }
    debugger
    cgJsClassAdmin.gallery.vars.WatermarkSettings = {};

    var cg_multiple_files_for_post =  cgJsClassAdmin.gallery.functions.getMultipleFileForPost($sortableDiv,realId);
    if(cg_multiple_files_for_post){
        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                break;
            }
            var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'];
            var ImgType = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['ImgType'];
            if(cgJsClassAdmin.gallery.functions.isEmbed(ImgType)){
                continue;
            }

            cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload] = {};
            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {
                if(WatermarkSettings[WpUpload]){
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] = WatermarkSettings[WpUpload]['WatermarkTitle'];
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = WatermarkSettings[WpUpload]['WatermarkSize'];
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = WatermarkSettings[WpUpload]['WatermarkPosition'];
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
                }else {
                    // will be set when cg_file_container click
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] =WatermarkTitleDefault;
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = WatermarkSizeDefault;
                    cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = WatermarkPositionDefault;
                }
            }
        }
    }else{
        var WpUpload = parseInt($sortableDiv.find('.cg_wp_upload_id').val());
        var ImgType = parseInt($sortableDiv.find('.cg_image_type_to_show').val());
        if(cgJsClassAdmin.gallery.functions.isEmbed(ImgType)){
            return;
        }
        cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload] = {};
        if(WatermarkSettings[WpUpload]){
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] = WatermarkSettings[WpUpload]['WatermarkTitle'];
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = WatermarkSettings[WpUpload]['WatermarkSize'];
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = WatermarkSettings[WpUpload]['WatermarkPosition'];
                cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['on'] = true;
        }else {
            // will be set when cg_file_container click
            cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'] =WatermarkTitleDefault;
            cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'] = WatermarkSizeDefault;
            cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'] = WatermarkPositionDefault;
        }
        $cgSellContainer.find('#cgWatermarkSelectPosition').val(WatermarkPosition);
        $cgSellContainer.find('#cgWatermarkSelectSize').val(WatermarkSize);
        $cgSellContainer.find('#cgWatermarkInputTitle').val(WatermarkTitle);
    }
}

cgJsClassAdmin.gallery.getBackgroundUrl =  function ($cg_backend_info_container,realId,order,WpUploadId){
    debugger
    var timestamp = Math.floor(new Date().getTime()/1000);
    var backgroundUrl = '';
    if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length>1){
        if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){
            backgroundUrl=$cg_backend_info_container.attr('data-cg-url-image-large');
        }else{
            backgroundUrl=cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['large'];
        }
        if(cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] && cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId]){
            backgroundUrl = cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId];
            var $cgAddedEcommerceImageForDownload = jQuery('#cgAddedEcommerceImageForDownload'+realId+'_'+WpUploadId);
            if($cgAddedEcommerceImageForDownload.length){
                backgroundUrl = $cgAddedEcommerceImageForDownload.attr('src');
            }else{// then must be just added and will be watermarked
                backgroundUrl=cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['large'];
                backgroundUrl += '?time='+timestamp;
            }
        }else{
            backgroundUrl += '?time='+timestamp;
        }
    }else{
        backgroundUrl=$cg_backend_info_container.attr('data-cg-url-image-large');
        if(cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] && cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId]){
            backgroundUrl = cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId];
            var $cgAddedEcommerceImageForDownload = jQuery('#cgAddedEcommerceImageForDownload'+realId+'_'+WpUploadId);
            if($cgAddedEcommerceImageForDownload.length){
                backgroundUrl = $cgAddedEcommerceImageForDownload.attr('src');
            }else{// then must be just added and will be watermarked
                backgroundUrl += '?time='+timestamp;
            }
        }else{
            backgroundUrl += '?time='+timestamp;
        }
    }
    return backgroundUrl;
}

cgJsClassAdmin.gallery.watermarkOnChange =  function (isSetOriginal){
    cgJsClassAdmin.gallery.watermark(cgJsClassAdmin.gallery.vars.backgroundUrlToSell,cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectPosition').val(),cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkInputTitle').val(),cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgWatermarkSelectSize').val(),false,true,cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cgSellPreview.active').attr('data-cg-order'),undefined,isSetOriginal);
}

cgJsClassAdmin.gallery.watermarkOnShow =  function ($,realId,$sortableDiv,firstImageWatermarkedWpUpload,position,text,pxSize){
    debugger
        cgJsClassAdmin.gallery.watermark(cgJsClassAdmin.gallery.vars.backgroundUrlToSell,position,text,pxSize,true,undefined,undefined,firstImageWatermarkedWpUpload);
}

cgJsClassAdmin.gallery.watermarkMultipleFiles =  function (backgroundUrl,position,text,pxSize,pxSizeExtraToSet,$cgSellPreview,$sortableDiv,realId,order,WpUploadId,fileType,imagesTotal,counterImage,isFromLoad,indexForDataCgOrder,isFromChange,activeOrder,firstImageWatermarkedWpUpload,isSetOriginal){
    console.log('order123');
    console.log(order);
    debugger
    //console.trace();
    //debugger
    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainerLoader').removeClass('cg_hide');// should be done here because watermark processing can be initiated through input again and again and it will be required in that scope
    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowLeft').addClass('cg_hide');
    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowRight').addClass('cg_hide');
    // https://brianium.github.io/watermarkjs/docs.html
    debugger
    watermark([backgroundUrl])
        .image(watermark.text[position](text, pxSize+'px serif', '#fff', 0.5, pxSizeExtraToSet))
        .then(function (img) {
            var base64src = jQuery(img).attr('src');
            debugger
            if(indexForDataCgOrder==1){
                $cgSellPreview.addClass('active');
                if((isFromLoad && !firstImageWatermarkedWpUpload) || isSetOriginal){
                    $sortableDiv.find('.cg_backend_image').css('background-image',backgroundUrl);
                    jQuery(img).attr('src',backgroundUrl);
                    delete cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId];
                    delete cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId];
                }else{
                    $sortableDiv.find('.cg_backend_image').css('background-image',base64src);
                    cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId] = base64src;
                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId] = fileType;
                }
            }else{
                if(!cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadId]['on']){
                    jQuery(img).attr('src',backgroundUrl);
                    delete cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId];
                    delete cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId];
                }else{
                    cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId] = base64src;
                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId] = fileType;
                }
            }

            // new >>> set only then
            if(order==activeOrder){
                $cgSellPreview.get(0).appendChild(img);
                $cgSellPreview.attr({
                    'data-cg-order':order,
                    'data-cg-wp-upload':WpUploadId
                });
                $cgSellPreview.parent().find('.cgSellPreview').addClass('cg_hide').removeClass('active');
                $cgSellPreview.removeClass('cg_hide').addClass('active');
                $cgSellPreview.closest('form').find('#cgSellPreviewContainerLoader').addClass('cg_hide');
                $cgSellPreview.closest('form').find('#cgSellPreviewContainer').removeClass('cg_hide');
            }


            if((counterImage!=undefined && imagesTotal!=undefined) &&  (counterImage==imagesTotal)){
                cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainerLoader').addClass('cg_hide');
                cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainer').removeClass('cg_hide');

                cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowLeft').removeClass('cg_hide');
                cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowRight').removeClass('cg_hide');

                if(isFromLoad){
                    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowLeft').addClass('cg_hide');
                    if(imagesTotal==1){
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowRight').addClass('cg_hide');
                    }
                }else {
                    if(!isFromChange){
                        activeOrder = cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreview.active').attr('data-cg-order');
                    }

                    console.log('order');
                    console.log(activeOrder);

                    if(activeOrder==1){
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowLeft').addClass('cg_hide');
                    }

                    if(activeOrder == Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length){
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowRight').addClass('cg_hide');
                    }

                    if(isFromChange){
                        debugger
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cgSellPreview').addClass('cg_hide').removeClass('active');
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('.cgSellPreview[data-cg-order="'+activeOrder+'"]').removeClass('cg_hide').addClass('active');
                    }
                }
                if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload){
                    cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload = false;
                    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSetForSell').click();
                }
            }
        });
}
cgJsClassAdmin.gallery.setWidthForPreview  = function (){

    var imgSrcFullWidth = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.imgSrcFullWidth').val();
    var imgSrcFullHeight = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.imgSrcFullHeight').val();
    var $cgSellPreview = jQuery('#cgSellPreview');
    var cgSellPreviewWidth = jQuery('#cgSellPreview').width();
    $cgSellPreview.find('img').removeAttr('style');

    if(imgSrcFullWidth*300/imgSrcFullHeight>cgSellPreviewWidth){
        var cgSellPreviewHeight = imgSrcFullHeight*cgSellPreviewWidth/imgSrcFullWidth;
        $cgSellPreview.find('img').css('height',cgSellPreviewHeight+'px');
    }

}
cgJsClassAdmin.gallery.watermark =  function (backgroundUrl,position,text,pxSize,isFromLoad,isFromChange,activeOrder,firstImageWatermarkedWpUpload,isSetOriginal){
    console.trace();
    debugger

    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainerLoader').removeClass('cg_hide');
    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainer').addClass('cg_hide');

    var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
    var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container');
    var realId = $sortableDiv.find('.cg_real_id').val();

    var pxSizeExtraToSet = pxSize;

    if(position=='lowerRight' || position=='lowerLeft'){
        pxSizeExtraToSet = undefined;// only then will be set right for both position types
    }

    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreview img').remove();

   // if(isFromLoad && $sortableDiv.find('.IsEcommerceSale').val()>='1'){
        //cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreview').append('<img src="'+$sortableDiv.find('.cg_backend_image_full_size_target_inner_wrap img').attr('src')+'" />');
        //cgJsClassAdmin.gallery.setWidthForPreview();
    //}else{

    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview').addClass('cg_hide');

    if(!cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId]){
        cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId] = {};
        cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId] = {};
    }


    if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){

        var imagesTotal = 0;

        // check how many files are image
        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){break;}
            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {
                imagesTotal++;
            }
        }

        var counterImage = 0;
        var indexForDataCgOrder = 0;

        for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){

            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){break;}
            if(order==activeOrder){
                debugger

                var backgroundUrl;
                var WpUploadId;
                var fileType;
                var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'];

                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){
                    WpUploadId = parseInt($sortableDiv.find('.cg_wp_upload_id').val());
                    fileType = $cg_backend_info_container.attr('data-cg-type-short');
                }else{
                    WpUploadId=cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'];
                    fileType=cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['ImgType'];
                }


                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type=='image') {

                    if(!counterImage){
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowRight').addClass('cg_hide').attr('data-cg-real-id',realId);
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellArrowLeft').attr('data-cg-real-id',realId);
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
                    }

                    counterImage++;

                    var $cgSellPreview = jQuery(cgJsClassAdmin.gallery.watermarkSellPreview);

                    if(counterImage==1){
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreview').remove();
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreviewContainer').append($cgSellPreview.removeClass('cg_hide'));
                    }else{
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreviewContainer').append($cgSellPreview);
                    }

                    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');

                    //  get backgroundurl here
                    var backgroundUrl = cgJsClassAdmin.gallery.getBackgroundUrl($cg_backend_info_container,realId,order,WpUploadId);

                    indexForDataCgOrder++;
                    if(isFromLoad && (WpUploadId == firstImageWatermarkedWpUpload)){
                        position = position;
                        text = text;
                        pxSize = pxSize;
                    }else{
                        position = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkPosition'];
                        text = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkTitle'];
                        pxSize = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUpload]['WatermarkSize'];
                    }
                    cgJsClassAdmin.gallery.watermarkMultipleFiles(backgroundUrl,position,text,pxSize,pxSizeExtraToSet,$cgSellPreview,$sortableDiv,realId,order,WpUploadId,fileType,imagesTotal,counterImage,isFromLoad,indexForDataCgOrder,isFromChange,activeOrder,firstImageWatermarkedWpUpload,isSetOriginal);

                }else{

                    // watermarkedFiles contains also simple full guid of not images, they are of course no kind of watermarked
                    cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].guid;
                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId] = fileType;
                }
            }
        }

    }else{

        var WpUploadId = parseInt($sortableDiv.find('.cg_wp_upload_id').val());
        var fileType = $cg_backend_info_container.attr('data-cg-type-short');

        if($cg_backend_info_container.attr('data-cg-type')=='image'){
            var timestamp = Math.floor(new Date().getTime()/1000);
            if(cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] && cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId]){

                backgroundUrl = cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUploadId];
                var $cgAddedEcommerceImageForDownload = jQuery('#cgAddedEcommerceImageForDownload'+realId+'_'+WpUploadId);
                if($cgAddedEcommerceImageForDownload.length){
                    backgroundUrl = $cgAddedEcommerceImageForDownload.attr('src');
                }else{
                    backgroundUrl += '?time='+timestamp;
                }
            }else{
                backgroundUrl += '?time='+timestamp;
            }

            cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
            var $cgSellPreview = jQuery(cgJsClassAdmin.gallery.watermarkSellPreview);
            cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreview').remove();
            cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreviewContainer').append($cgSellPreview.removeClass('cg_hide'));
            cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview').removeClass('cg_hide');
            // https://brianium.github.io/watermarkjs/docs.html
            debugger
            watermark([backgroundUrl])
                .image(watermark.text[position](text, pxSize+'px serif', '#fff', 0.5, pxSizeExtraToSet))
                .then(function (img) {
                    debugger
                    var base64src = jQuery(img).attr('src');
                    $cgSellPreview.get(0).appendChild(img);
                    var fileType = $cg_backend_info_container.attr('data-cg-type-short');
                    if((isFromLoad && !firstImageWatermarkedWpUpload) || isSetOriginal){
                        $sortableDiv.find('.cg_backend_image').css('background-image',backgroundUrl);
                        delete cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][$sortableDiv.find('.cg_wp_upload_id').val()];
                        delete cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][$sortableDiv.find('.cg_wp_upload_id').val()];
                    }else{
                        $sortableDiv.find('.cg_backend_image').css('background-image',base64src);
                        cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][$sortableDiv.find('.cg_wp_upload_id').val()] = base64src;
                        cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][$sortableDiv.find('.cg_wp_upload_id').val()] = fileType;
                        $cgSellPreview.parent().find('.cgSellPreview').addClass('cg_hide').removeClass('active');
                        $cgSellPreview.removeClass('cg_hide').addClass('active');
                        $cgSellPreview.closest('form').find('#cgSellPreviewContainerLoader').addClass('cg_hide');
                        $cgSellPreview.closest('form').find('#cgSellPreviewContainer').removeClass('cg_hide');
                        $cgSellPreview.attr('data-cg-order',1);
                        $cgSellPreview.attr('data-cg-wp-upload',WpUploadId);
                        // append for sure here again, might be removed because of latency processing
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellWatermarkPreview #cgSellPreviewContainer').append($cgSellPreview.removeClass('cg_hide'));
                    }
                    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainerLoader').addClass('cg_hide');
                    cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSellPreviewContainer').removeClass('cg_hide');
                    cgJsClassAdmin.gallery.setWidthForPreview();
                    if(cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload){
                        cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload = false;
                        cgJsClassAdmin.gallery.vars.cgSellContainer.find('#cgSetForSell').click();
                    }
                });
        }else{
            // watermarkedFiles contains also simple full guid of not images, they are of course no kind of watermarked
            cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][WpUploadId] = $cg_backend_info_container.attr('data-cg-original-source');
            cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][WpUploadId] = fileType;
        }

    }

   // }

}
