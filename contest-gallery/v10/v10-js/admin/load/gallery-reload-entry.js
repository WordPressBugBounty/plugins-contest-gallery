cgJsClassAdmin.gallery.reload = {
    entry: function (id,isWithSaveChanges,isReloadAfterSaleSettingsSave,NewWpUploadWhichReplace,WpUploadToReplace,newImgType,isAfterWatermark,$cg_backend_info_container) {

        var realId = id;
        debugger
        if(NewWpUploadWhichReplace && !isAfterWatermark && cgJsClassAdmin.index.functions.isImageType(newImgType)){

            var cg_multiple_files_for_post = $cg_backend_info_container.find('.cg_multiple_files_for_post').val();
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[id] = JSON.parse(cg_multiple_files_for_post);

            // get type
            // get last watermark settings if image
            for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
                if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                    break;
                }
                // no check for type in condition!
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'] == NewWpUploadWhichReplace){
                    var WpUploadFilesForSaleArrayLoaded = cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded(cgJsClassAdmin.gallery.vars.$sortableDiv);
                    if(WpUploadFilesForSaleArrayLoaded.indexOf(parseInt(WpUploadToReplace))>-1){

                        var WatermarkSettings = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.WatermarkSettings').val();
                        if(WatermarkSettings){
                            cgJsClassAdmin.gallery.vars.WatermarkSettings = JSON.parse(WatermarkSettings);
                        }else{
                            cgJsClassAdmin.gallery.vars.WatermarkSettings = {};
                        }

                        var title = 'Contest Gallery';
                        var pxSize = '128';
                        var position = 'center';

                        var pxSizeExtraToSet = pxSize;

                        if(position=='lowerRight' || position=='lowerLeft'){
                            pxSizeExtraToSet = undefined;// only then will be set right for both position types
                        }

                        if(cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace] && cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]['WatermarkSize']){
                            title = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]['WatermarkTitle'];
                            pxSizeExtraToSet = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]['WatermarkSize'];
                            position = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]['WatermarkPosition'];
                            delete cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace];
                        }

                        cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace] = {};
                        cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkTitle'] = title;
                        cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkSize'] = pxSizeExtraToSet;
                        cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkPosition'] = position;

                        if(position=='lowerRight' || position=='lowerLeft'){
                            pxSizeExtraToSet = undefined;// only then will be set right for both position types
                        }

                        // determine orderNewWpUpload
                        for(var orderNewWpUpload in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
                            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(orderNewWpUpload)){
                                break;
                            }
                            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderNewWpUpload]['WpUpload'] === NewWpUploadWhichReplace){
                                debugger
                                break;
                            }
                        }

                        cgJsClassAdmin.gallery.vars.$sortableDiv.find('.WatermarkSettings').val(JSON.stringify(cgJsClassAdmin.gallery.vars.WatermarkSettings));

                        debugger
                        if(cgJsClassAdmin.index.functions.isImageType(newImgType)){
                            debugger
                            watermark([cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderNewWpUpload]['large']])
                                .image(watermark.text[position](title, pxSize+'px serif', '#fff', 0.5, pxSizeExtraToSet))
                                .then(function (img) {
                                    debugger
                                    var base64src = jQuery(img).attr('src');
                                    cgJsClassAdmin.gallery.vars.base64andAltFileValues = {};
                                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes = {};
                                    cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId] = {};
                                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId] = {};
                                    cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][NewWpUploadWhichReplace] = base64src;
                                    cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][NewWpUploadWhichReplace] = newImgType;
                                    debugger
                                    cgJsClassAdmin.gallery.reload.entry(id,isWithSaveChanges,isReloadAfterSaleSettingsSave,NewWpUploadWhichReplace,WpUploadToReplace,newImgType,true);
                                });
                            debugger
                            return;
                        }

                    }
                }
            }
            // watermark if image
            // create sell container array for form
        }else{
            // maybe an image was replaced with a pdf file then it has to be to replaced
            // cgJsClassAdmin.gallery.vars.WatermarkSettings check is important, could not exists in this moment
            if(cgJsClassAdmin.gallery.vars.WatermarkSettings && cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]){
                delete cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace];
            }
        }

        var $ = jQuery;

        var $cgGalleryFormClone = $('#cgGalleryForm').clone();

        if(isWithSaveChanges){
            $cgGalleryFormClone.find('#cgGalleryFormSubmit').prop('disabled',false);
        }
        debugger
        $cgGalleryFormClone.find('.cgSortableDiv').each(function (){
            if($(this).attr('data-cg-real-id')==id){
                $(this).find('input.cg_delete').remove();
            }else{
                $(this).remove();
            }
        });

        var form = $cgGalleryFormClone.get(0);
        var formPostData = new FormData(form);

        if(isAfterWatermark){
            formPostData.append('cgSellContainer[base64WatermarkedAndAltFiles]['+NewWpUploadWhichReplace+']', cgJsClassAdmin.gallery.vars.base64andAltFileValues[realId][NewWpUploadWhichReplace]);
            formPostData.append('cgSellContainer[base64WatermarkedAndAltFileTypes]['+NewWpUploadWhichReplace+']', cgJsClassAdmin.gallery.vars.base64andAltFileTypes[realId][NewWpUploadWhichReplace]);
            formPostData.append('cgSellContainer[WatermarkSettings]['+NewWpUploadWhichReplace+'][WatermarkTitle]', cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkTitle']);
            formPostData.append('cgSellContainer[WatermarkSettings]['+NewWpUploadWhichReplace+'][WatermarkPosition]', cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkPosition']);
            formPostData.append('cgSellContainer[WatermarkSettings]['+NewWpUploadWhichReplace+'][WatermarkSize]', cgJsClassAdmin.gallery.vars.WatermarkSettings[NewWpUploadWhichReplace]['WatermarkSize']);
        }

        formPostData.append('cgBackendHash',$('#cgBackendHash').val());

        if(isWithSaveChanges){
            formPostData.append('cgIsRealFormSubmit',1);
        }
        debugger
        // AJAX Call - Submit Form
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function(response) {
            debugger
            if(NewWpUploadWhichReplace){
                $('#cgWpUploadToReplace').val('');// reset again
                $('#cgNewWpUploadWhichReplace').val('');// reset again
            }

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

            var cgVersionCurrent = $response.find('#cgVersionScripts').val();

            cgJsClassAdmin.index.functions.cgSetVersionForUrlJs($response.find('#cgGetVersionForUrlJs').val());

            if(cgJsClassAdmin.index.vars.cgVersion===0){
                cgJsClassAdmin.index.vars.cgVersion = cgVersionCurrent;
            }

            if(cgJsClassAdmin.index.vars.cgVersion !== 0 && (cgJsClassAdmin.index.vars.cgVersion!=cgVersionCurrent)){

                cgJsClassAdmin.index.functions.newVersionReload();
                return;

            }

            if(isWithSaveChanges && isReloadAfterSaleSettingsSave){
                cgJsClassAdmin.gallery.functions.setForSellAjaxProcessing(cgJsClassAdmin.gallery.vars.ecommerce.activateSale.formPostData);
            }else{

                // take care of order... this has to be done as first before replaceWith
                if(!cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry){
                    cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry = [];
                }

                cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry[realId] = null;
                debugger
                // cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry[realId] will be set here
                $response.find('script[data-cg-processing-for-reload-entry="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });

                // has to be done so correct watermark actualization especially first time
                if(cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry[realId]){
                    cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] = cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownloadForReloadEntry[realId];
                }else{
                    cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] = null;
                }

                $('#cgGalleryForm').find('#div'+id).replaceWith($response.find('#div'+id));
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Changes saved',true);
                cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone.remove();
                $('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
                debugger
                cgJsClassAdmin.gallery.functions.loadBlockquote($,realId);

            }

            cgJsClassAdmin.index.functions.setCgNonce($);

        }).fail(function(xhr, status, error) {
            cgJsClassAdmin.index.functions.noteIfIsIE();
        }).always(function() {

        });

    }
}