cgJsClassAdmin.gallery.functions = cgJsClassAdmin.gallery.functions || {};

if(!cgJsClassAdmin.gallery.functions.renderWatermarkImage){
    cgJsClassAdmin.gallery.functions.renderWatermarkImage = function(backgroundUrl,text,position,pxSize,color,opacity){
        return new Promise(function(resolve,reject){
            var sourceImage = new Image();
            var link = document.createElement('a');

            if(!backgroundUrl){
                reject();
                return;
            }

            link.href = backgroundUrl;
            if(backgroundUrl.indexOf('data:') !== 0 && (link.protocol+'//'+link.host) !== (window.location.protocol+'//'+window.location.host)){
                sourceImage.crossOrigin = 'anonymous';
            }

            sourceImage.onload = function(){
                try{
                    var canvas = document.createElement('canvas');
                    var width = sourceImage.naturalWidth || sourceImage.width;
                    var height = sourceImage.naturalHeight || sourceImage.height;
                    var context = canvas.getContext('2d');
                    var fontSize = parseInt(pxSize, 10);
                    var textToDraw = text || 'Contest Gallery';
                    var positionToUse = position || 'center';
                    var minFontSize = 8;
                    var textSize;
                    var margin;
                    var x;
                    var y;
                    var renderedImage;

                    if(!fontSize){
                        fontSize = 64;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    context.drawImage(sourceImage, 0, 0, width, height);

                    fontSize = Math.max(minFontSize, Math.min(512, fontSize));

                    var getTextSize = function(size){
                        var metrics;
                        var minX;
                        var maxX;
                        var minY;
                        var maxY;
                        context.font = size+'px serif';
                        metrics = context.measureText(textToDraw);
                        minX = (typeof metrics.actualBoundingBoxLeft === 'number') ? -metrics.actualBoundingBoxLeft : 0;
                        maxX = (typeof metrics.actualBoundingBoxRight === 'number') ? metrics.actualBoundingBoxRight : metrics.width;
                        minY = (typeof metrics.actualBoundingBoxAscent === 'number') ? -metrics.actualBoundingBoxAscent : -size;
                        maxY = (typeof metrics.actualBoundingBoxDescent === 'number') ? metrics.actualBoundingBoxDescent : Math.ceil(size * 0.25);
                        return {
                            width: Math.ceil(maxX - minX),
                            height: Math.ceil(maxY - minY),
                            drawX: -minX,
                            drawY: -minY
                        };
                    };

                    var getMargin = function(size){
                        return Math.max(10, Math.floor(size * 0.08));
                    };

                    textSize = getTextSize(fontSize);
                    margin = getMargin(fontSize);
                    if(positionToUse === 'upperLeft'){
                        x = margin;
                        y = margin;
                    }else if(positionToUse === 'upperRight'){
                        x = width - textSize.width - margin;
                        y = margin;
                    }else if(positionToUse === 'lowerRight'){
                        x = width - textSize.width - margin;
                        y = height - textSize.height - margin;
                    }else if(positionToUse === 'lowerLeft'){
                        x = margin;
                        y = height - textSize.height - margin;
                    }else{
                        x = Math.floor((width - textSize.width) / 2);
                        y = Math.floor((height - textSize.height) / 2);
                    }

                    context.save();
                    context.globalAlpha = (opacity === 0 || opacity) ? opacity : 0.5;
                    context.fillStyle = color || '#fff';
                    context.font = fontSize+'px serif';
                    context.textBaseline = 'alphabetic';
                    context.translate(x, y);
                    context.fillText(textToDraw, textSize.drawX, textSize.drawY);
                    context.restore();

                    renderedImage = new Image();
                    renderedImage.alt = '';
                    renderedImage.src = canvas.toDataURL('image/png');
                    resolve(renderedImage);
                }catch(e){
                    reject(e);
                }
            };

            sourceImage.onerror = function(){
                reject();
            };

            sourceImage.src = backgroundUrl;
        });
    };
}

cgJsClassAdmin.gallery.reload = {
    entry: function (id,isWithSaveChanges,isReloadAfterSaleSettingsSave,NewWpUploadWhichReplace,WpUploadToReplace,newImgType,isAfterWatermark,$cg_backend_info_container,callback,callbackParams,isReloadAfterSaveChangesOrCallback,customMessage,closeEverything) {

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
                            pxSize = cgJsClassAdmin.gallery.vars.WatermarkSettings[WpUploadToReplace]['WatermarkSize'];
                            pxSizeExtraToSet = pxSize;
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
                            cgJsClassAdmin.gallery.functions.renderWatermarkImage(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderNewWpUpload]['large'], title, position, pxSize, '#fff', 0.5)
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

        var $cgGalleryForm = $('#cgGalleryForm');

        var $cgGalleryFormClone = $('#cgGalleryForm').clone();

        if(isWithSaveChanges){
            $cgGalleryFormClone.find('#cgGalleryFormSubmit').prop('disabled',false);
        }

        $cgGalleryFormClone.find('.cgSortableDiv').each(function (){
            if($(this).attr('data-cg-real-id')==id){
                $(this).find('input.cg_delete').remove();
            }else{
                $(this).remove();
            }
        });

        // copy current values/states from original -> clone, required for select especial, because cloning does not kee select values
        $cgGalleryForm.find('select').each(function(){
            var name = $(this).attr('name');
            if (!name) return; // skip selects without name
            $cgGalleryFormClone.find('select[name="'+name+'"]').val($(this).val());
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

            cgJsClassAdmin.gallery.functions.refreshBackendDashboardFromResponse($, $response);

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

                var message = 'Changes saved';
                if(!isReloadAfterSaveChangesOrCallback && customMessage){
                    message = customMessage;
                }
                if(!isReloadAfterSaveChangesOrCallback){
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(message,true);
                }
                $('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');

                if(cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone){
                    cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone.remove();
                }
                cgJsClassAdmin.gallery.functions.loadBlockquote($,realId);
            }

            cgJsClassAdmin.index.functions.setCgNonce($);

            var cgPdfPreviewsToCreateString = $response.find('#cgPdfPreviewsToCreateString').val();
            debugger
            console.log('cgPdfPreviewsToCreateString');
            console.log(cgPdfPreviewsToCreateString);

            if(cgPdfPreviewsToCreateString.indexOf('cg-pdf-previews-to-create')>-1){
                cgJsClassAdmin.gallery.pdf.createAndSetPdfPreviewPrepare($,cgPdfPreviewsToCreateString,true,$response);
            }
            debugger
            if(callback){
                if(!callbackParams){
                    callbackParams = [];
                }
                if(isReloadAfterSaveChangesOrCallback){
                    callback.apply(null, callbackParams.concat(function() {
                        cgJsClassAdmin.gallery.reload.entry(
                            id, false, isReloadAfterSaleSettingsSave, NewWpUploadWhichReplace,
                            WpUploadToReplace, newImgType, isAfterWatermark,
                            $cg_backend_info_container, undefined, undefined, false, customMessage,closeEverything
                        );
                    }));
                    //if(closeEverything){closeEverything has to be executed if exists
                        //closeEverything = undefined;// because was already forwarded in callback.apply above
                    //}
                }else{
                    callback.apply(null, callbackParams);
                }
            }

            if(closeEverything){
                $('#cgBackendBackgroundDrop.cg_active').click();
            }

            return; // so far not neeeded

            // get current permalinks
            var cg_admin_url = $("#cg_admin_url").val();
            var $cgSortableDiv  = $('#div'+id);
            var realId = $cgSortableDiv.find('.cg_backend_info_container').attr('data-cg-real-id');
            $cgSortableDiv.find('.cg_entry_page_url').addClass('cg_pointer_events_none');

            $.ajax({
                url: cg_admin_url + "admin-ajax.php",
                type: 'post',
                data: {
                    action: 'post_cg_get_current_permalinks',
                    cgRealId: realId,
                },
            }).done(function (response) {

                var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

                cgJsClassAdmin.gallery.vars.entryPermalinks = {};// reset first
                $response.find('script[data-cg-processing-get-current-permalinks="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });
                var permaLinks = cgJsClassAdmin.gallery.vars.entryPermalinks;
                for(var shortcode in permaLinks){
                    if(!permaLinks.hasOwnProperty(shortcode)){
                        break;
                    }
                    $cgSortableDiv.find('.cg_entry_page_url.'+shortcode).attr('href',permaLinks[shortcode]);
               }
                $cgSortableDiv.find('.cg_entry_page_url').removeClass('cg_pointer_events_none');

            }).fail(function (xhr, status, error) {

                console.log(xhr);
                console.log(status);
                console.log(error);

            }).always(function () {

            });

        }).fail(function(xhr, status, error) {
            cgJsClassAdmin.index.functions.noteIfIsIE();
        }).always(function() {

        });

    }
}
