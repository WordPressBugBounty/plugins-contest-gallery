jQuery(document).ready(function ($) {

    $(document).on('click','#cgMultipleFilesForPostContainer .cg_backend_image_full_size_target_container_rotate',function () {
        cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
        var realId = $(this).attr('data-cg-real-id');
        var order = $(this).closest('.cg_backend_image_full_size_target_container').find('.cg_backend_image_full_size_target_container_position').text();
        var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$cg_backend_info_container;
        var $cg_backend_image_full_size_target = $(this).closest('.cg_backend_image_full_size_target_container').find('.cg_backend_image_full_size_target');
        var isAdditionalFileChanged = false;
        if(!$(this).attr('data-cg-rThumb') || $(this).attr('data-cg-rThumb')==0){
            $(this).attr('data-cg-rThumb',0) ;
            $cg_backend_image_full_size_target.removeClass('cg180degree  cg270degree').addClass('cg90degree');
            $(this).attr('data-cg-rThumb',90);
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['rThumb'] = 90;
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){isAdditionalFileChanged=true;}
        } else if($(this).attr('data-cg-rThumb')==90){
            $cg_backend_image_full_size_target.removeClass('cg90degree  cg270degree').addClass('cg180degree');
            $(this).attr('data-cg-rThumb',180);
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['rThumb'] = 180;
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){isAdditionalFileChanged=true;}
        } else if($(this).attr('data-cg-rThumb')==180){
            $cg_backend_image_full_size_target.removeClass('cg90degree  cg180degree').addClass('cg270degree');
            $(this).attr('data-cg-rThumb',270);
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['rThumb'] = 270;
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){isAdditionalFileChanged=true;}
        } else if($(this).attr('data-cg-rThumb')==270){
            $cg_backend_image_full_size_target.removeClass('cg90degree cg180degree cg270degree');
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['rThumb'] = 0;
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['isRealIdSource']){isAdditionalFileChanged=true;}
            $(this).attr('data-cg-rThumb',0);
        }
        cgJsClassAdmin.gallery.vars.$cg_backend_info_container.find('.cg_backend_save_changes,.cg_backend_rotate_css_based').removeClass('cg_hide');
        if(isAdditionalFileChanged){
            cgJsClassAdmin.gallery.functions.setSimpleDataRealIdSource(realId,cgJsClassAdmin.gallery.vars.$cg_backend_info_container);
        }else{
            cgJsClassAdmin.gallery.vars.$cg_backend_info_container.find('.cg_rThumb').val($(this).attr('data-cg-rThumb')).removeClass('cg_disabled_send');
        }

        if(order==1){
            cgJsClassAdmin.gallery.vars.$cg_backend_info_container.find('.cg_backend_image').removeClass('cg0degree cg90degree cg180degree  cg270degree').addClass('cg'+$(this).attr('data-cg-rThumb')+'degree');
        }

    });

    $(document).on('click','#cgMultipleFilesForPostContainer .cg_backend_image_full_size_target_container_remove',function () {
        debugger
        if($(this).hasClass('cg_is_for_sale')){
            $('#cgEcommerceFileCanNotBeDeleted').removeClass('cg_hide').addClass('cg_active');
            $('#cgBackendBackgroundDrop').addClass('cg_high_overlay');
        }else if($(this).attr('data-isecommerceentryanddownload')=='1'  && false){// will be not used anymore
            $('#cgAskIfShouldBeRemovedFromSaleIfMultiple').removeClass('cg_hide').addClass('cg_active');
            $('#cgAskIfShouldBeRemovedFromSaleIfMultipleButton').data('ecommerceentryanddownload-remove-button',$(this));
            $('#cgBackendBackgroundDrop').addClass('cg_high_overlay');
        }else{
            cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
            var realId = $(this).attr('data-cg-real-id');
            var $cg_preview_files_container = $(this).closest('.cg_preview_files_container');
            var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$cg_backend_info_container;
            var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
            var order = $(this).closest('.cg_backend_image_full_size_target_container').find('.cg_backend_image_full_size_target_container_position').text();
            var $cg_backend_image_full_size_target_container = $(this).closest('.cg_backend_image_full_size_target_container').remove();
            if($cg_backend_image_full_size_target_container.hasClass('cg_is_new_ecommerce_download_class')){
                if(!$cgMultipleFilesForPostContainer.find('.cg_is_new_ecommerce_download_class').length){
                    $cgMultipleFilesForPostContainer.find('.cg_multiple_files_file_for_post_ecommerce_processing').addClass('cg_hide');
                }
            }
            if($cg_backend_image_full_size_target_container.hasClass('cg_is_new_ecommerce_image_class')){
                if(!$cgMultipleFilesForPostContainer.find('.cg_is_new_ecommerce_image_class').length){
                    $cgMultipleFilesForPostContainer.find('.cg_multiple_files_file_for_post_ecommerce_processing').addClass('cg_hide');
                }
            }
            $cg_backend_image_full_size_target_container.remove();
            var order = 1;
            var newOrderedData = {};
            var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
            var isRealIdSourceDeleted = true;
            $cgMultipleFilesForPostContainer.find('.cg_backend_image_full_size_target_container .cg_backend_image_full_size_target_container_position').each(function (){
                if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][$(this).text()].isRealIdSource){isRealIdSourceDeleted=false;}
                newOrderedData[order] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][$(this).text()];
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

    });

    $(document).on('click','#cgAskIfShouldBeRemovedFromSaleIfMultipleButton',function (e) {
        e.preventDefault();
        debugger
        cgJsClassAdmin.gallery.functions.removeEcommerceFromMultipleFile($,$(this).data('ecommerceentryanddownload-remove-button'));
        $('#cgBackendBackgroundDrop').removeClass('cg_high_overlay');
        $('#cgAskIfShouldBeRemovedFromSaleIfMultiple').addClass('cg_hide');
        $('#cgMultipleFilesForPostContainer #cg_multiple_files_file_for_post_ecommerce_download_files_removed_from_sale').removeClass('cg_hide');
        cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
    });

    $(document).on('click','#cgAskIfShouldBeRemovedFromSaleIfMultipleClose',function (e) {
        $('#cgAskIfShouldBeRemovedFromSaleIfMultiple').addClass('cg_hide').removeClass('cg_active');
        $('#cgBackendBackgroundDrop').removeClass('cg_high_overlay');
    });

    $(document).on('click','#cgAskSaveChangesForMultipleFilesClose',function (e) {
        $('#cgAskSaveChangesForMultipleFiles').addClass('cg_hide').removeClass('cg_active');
        $('#cgBackendBackgroundDrop').removeClass('cg_high_overlay');
    });

    $(document).on('click','#cg_multiple_files_file_for_post_submit_button',function (e) {
        e.preventDefault();

        // reset then
        //cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = false;
        debugger
        if(!cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles){
            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        }else{

            var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');

            var realId = $cgMultipleFilesForPostContainer.attr('data-cg-real-id');
            var hasNewEcommerceDownloadEntry = false;
            debugger
            for(var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
                if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)){
                    break;
                }
                if($cgMultipleFilesForPostContainer.find('.cg_is_new_ecommerce_image_class').length){
                    hasNewEcommerceDownloadEntry = true;
                    var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'];
                    if(!cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId]){
                        cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId] = [];
                    }
                    cgJsClassAdmin.gallery.vars.addedEcommerceImageForDownload[realId][WpUpload] = 'base64';
                    cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload = true;
                }
                if($cgMultipleFilesForPostContainer.find('.cg_is_new_ecommerce_download_class').length){
                    hasNewEcommerceDownloadEntry = true;
                    // will be not done anymore
                    //cgJsClassAdmin.gallery.vars.isNewAddedEcommerceDownload = true;
                }
            }

            debugger
            // no watermark now before
            if(hasNewEcommerceDownloadEntry && false){
                cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
                jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');
                setTimeout(function (){
                    cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
                    // cgJsClassAdmin.gallery.vars.isNewAddedEcommerceImageForDownload
                    // and
                    // cgJsClassAdmin.gallery.vars.isNewAddedEcommerceDownload
                    // will be used there
                    cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_sale_settings').click();
                },100);
                cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
            }else{
                cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
                jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');
                cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
                //cgJsClassAdmin.gallery.reload.entry(cgJsClassAdmin.gallery.vars.$sortableDiv.attr('data-cg-real-id'),true,false);
                debugger
                cgJsClassAdmin.gallery.reload.entry(cgJsClassAdmin.gallery.vars.$sortableDiv.attr('data-cg-real-id'),true,false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Changes will be saved',true);
            }
        }

    });

    $(document).on('click','#cgSortable .cg_manage_multiple_files_for_post,#cgSortable .cg_manage_multiple_files_for_post_prev,#cgSortable .cg_manage_multiple_files',function () {
        cgJsClassAdmin.gallery.functions.removeUsedFrames();
        var realId = $(this).closest('.cgSortableDiv').attr('data-cg-real-id');
        cgJsClassAdmin.gallery.vars.multipleFilesNewAdded[realId] = [];
        cgJsClassAdmin.gallery.functions.setMultipleFilesForPostBeforeClone($(this));
        cgJsClassAdmin.gallery.functions.manageMultipleFilesForPost($,$(this),true);
    });


    $(document).on('click','.cg_download_multiple_files_zip',function () {
        var $element = $(this);
        if(cgJsClassAdmin.gallery.functions.getCookie($element.attr('data-cg-download-cookie'))){
            cgJsClassAdmin.gallery.functions.deleteCookie($element.attr('data-cg-download-cookie'));
        }
        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Zip will be created',undefined,undefined,undefined,true);
        jQuery('body').addClass('cg_pointer_events_none cg_no_scroll');
        cgJsClassAdmin.gallery.vars.checkDownloadCookieInterval = setInterval(function() {
            console.log('interval running');
            if(cgJsClassAdmin.gallery.functions.getCookie($element.attr('data-cg-download-cookie'))){
                cgJsClassAdmin.gallery.functions.deleteCookie($element.attr('data-cg-download-cookie'));
                cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
                jQuery('body').removeClass('cg_pointer_events_none cg_no_scroll');
                clearInterval(cgJsClassAdmin.gallery.vars.checkDownloadCookieInterval);
            }
        },1000);
    });

});