cgJsClassAdmin.gallery.functions = cgJsClassAdmin.gallery.functions || {};
cgJsClassAdmin.gallery.functions = {
    abortRequest: function () {

        for (var index in cgJsClassAdmin.gallery.functions.requests) {
            cgJsClassAdmin.gallery.functions.requests[index].abort();
            delete cgJsClassAdmin.gallery.functions.requests[index];
        }

    },
    addSocialTabs: function (file_frame, isReplace, WpUploadToReplace,isAddFilesOrReplaceFile) {
        cgJsClassAdmin.gallery.vars.file_frame = file_frame;
        // should not be named same name as official wordpress class "media-menu-item" otherwise media library from wordpress not loaded,
        // if trick around with same class names then site freeze might happen
        file_frame.$el.find('.media-frame-router').append('<button class="cg_media_menu_item" id="cgAddYoutube" >Add social embed</button>');
        file_frame.$el.find('.media-frame-router').append('<button class="cg_media_menu_item" id="cgYoutubeLibrary" >Social embed library<span id="cgYoutubeLibraryNewAddedCount" class="cg_hide" >0</span></button>');

        if(!isAddFilesOrReplaceFile){// has only to work when .cg_upload_wp_images_button is clicked.
            // because of many dependencies by clicking #cgCreateViaOpenAI, #cgEditViaOpenAI and #menu-item-browse
            file_frame.$el.find('.media-frame-router').append('<button class="cg_media_menu_item" id="cgCreateViaOpenAI" >Create via OpenAI</button>');
            file_frame.$el.find('.media-frame-router').append('<button class="cg_media_menu_item" id="cgEditViaOpenAI" >Edit via OpenAI</button>');
        }

        if (isReplace) {
            file_frame.$el.addClass('cg_is_replace');
        }
        if (WpUploadToReplace) {
            file_frame.$el.attr('data-cg-wp-upload-to-replace', WpUploadToReplace);
        }
    },
    cgRotateOnLoad: function ($) {
        if ($('#cgImgSource').height() >= $('#cgImgSource').width()) {
            //console.log(0);
            $('#cgImgSourceContainerMain').height($('#cgImgSource').height());
        } else {//console.log(1);
            $('#cgImgSourceContainerMain').height($('#cgImgSource').width());
        }
        if ($('#cgImgThumb').height() >= $('#cgImgThumb').width()) {//console.log(2);
            $('#cgImgThumbContainerMain').height($('#cgImgThumb').height());
        } else {//console.log(3);
            $('#cgImgThumbContainerMain').height($('#cgImgThumb').width());
        }
    },
    cgRotateSameHeightDivImage: function ($) {
        if ($('#cgImgThumbContainerMain').length) {
            $('#cgSortable .cg_short_text').removeClass('cg_by_search_sorted');
            $('#cgSortable .cg_category_select').removeClass('cg_by_search_sorted');
            $('#cgSortable .cg_for_id_wp_username_by_search_sort').removeClass('cg_by_search_sorted');

            var $cgOrderSelectCustomFieldsSelectedInput = $('#cgOrderSelectCustomFields option:selected,#cgOrderSelectFurtherFields option:selected');

            if ($cgOrderSelectCustomFieldsSelectedInput.length) {
                $('#cgSortable .' + $cgOrderSelectCustomFieldsSelectedInput.attr('data-cg-input-fields-class')).addClass('cg_by_search_sorted');
            }
        }

    },
    checkIfFurtherImagesAvailable: function ($) {

        if ($('#cgStepsNavigationTop .cg_step').length) {// happens when images in last step were deleted!!!
            $('#cgStepsNavigationTop .cg_step:last-child').click();
        }

    },
    countTotalVisibleActivatedImagesCountForCategories: function () {

        var totalVisibleActivatedImagesCount = 0;

        jQuery('.cg-categories-check').each(function () {
            if (jQuery(this).prop('checked')) {
                totalVisibleActivatedImagesCount = totalVisibleActivatedImagesCount + parseInt(jQuery(this).attr('data-cg-images-in-category-count'));
            }
        });

        return totalVisibleActivatedImagesCount;

    },

    createMoveSelectAssignArea: function ($, $cgMoveToAnotherGalleryCompare, gid, selectedGid) {
        $('#cgMoveToAnotherGalleryId').text(selectedGid);
        if (!cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid]) {
            cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid] = {};
        }
        var fieldTypes = ['date-f', 'text-f', 'url-f', 'email-f', 'comment-f', 'date-f', 'select-f', 'selectc-f'];
        var hasFieldsToAssign = false;
        cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id[gid].forEach(function (field) {
            if (fieldTypes.indexOf(field.Field_Type) > -1) {
                var $compare = $('<div data-cg-type="' + field.Field_Type + '" class="cg_move_type" ><span class="cg_move_field_id"  data-cg-from="' + field.id + '" >' + field.Field_Content.titel + '</span></div>');
                var $toCompareSelect = '';
                for (var index in cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id[selectedGid]) {
                    if (!cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id[selectedGid].hasOwnProperty(index)) {
                        break;
                    }
                    var fieldToCompare = cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id[selectedGid][index];
                    var type = fieldToCompare.Field_Type;
                    var title = fieldToCompare.Field_Content.titel;
                    debugger
                    if (field.Field_Type == type && type != 'selectc-f') {
                        hasFieldsToAssign = true;
                        if (!$toCompareSelect) {
                            $toCompareSelect = $('<select class="cg_action_select" ><option value="">Please select</option></select>');
                        }
                        var selected = '';
                        if (
                            cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid][field.id] &&
                            cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid][field.id] == fieldToCompare.id
                        ) {
                            selected = 'selected';
                        }
                        $toCompareSelect.append('<option value="' + fieldToCompare.id + '" ' + selected + ' >' + title + '</option>');
                    } else if (field.Field_Type == type && type == 'selectc-f') {
                        hasFieldsToAssign = true;
                        if (!$toCompareSelect) {
                            $toCompareSelect = $('<select class="cg_action_select cg_action_select_category" ><option value="">Please select</option></select>');
                        }
                        for (var categoryId in cgJsClassAdmin.gallery.vars.allCategoriesByGalleryID[selectedGid]) {
                            if (!cgJsClassAdmin.gallery.vars.allCategoriesByGalleryID[selectedGid].hasOwnProperty(categoryId)) {
                                break;
                            }
                            var categoryObject = cgJsClassAdmin.gallery.vars.allCategoriesByGalleryID[selectedGid][categoryId];
                            var selected = '';
                            if (
                                cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid][field.id] &&
                                cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[selectedGid][field.id] == categoryObject.id
                            ) {
                                selected = 'selected';
                            }
                            $toCompareSelect.append('<option value="' + categoryObject.id + '"  ' + selected + ' >' + categoryObject.name + '</option>');
                        }
                    }
                }
                $cgMoveToAnotherGalleryCompare.append($compare);
                if ($toCompareSelect) {
                    $compare.append($toCompareSelect);
                } else {
                    $compare.append('<span style="font-size: 12px;line-height: 14px;">No such type of field in gallery ' + selectedGid + '</span>');
                }
            }
        });
        if (!hasFieldsToAssign) {
            $cgMoveToAnotherGalleryCompare.empty();
            if (cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id[gid].length == 1) {
                $cgMoveToAnotherGalleryCompare.append('Current gallery ' + gid + ' has no fields to assign');
            } else {
                $cgMoveToAnotherGalleryCompare.append('Gallery ' + selectedGid + ' has no fields to assign');
            }
        }
    },
    deleteCookie: function (cookieName) {
        document.cookie = cookieName + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    },
    getCookie: function (cookieName) {
        var v = document.cookie.match('(^|;) ?' + cookieName + '=([^;]*)(;|$)');
        var toReturn = v ? v[2] : null;
        if (toReturn != null) {
            cgJsClass.gallery.vars.cookiesNotAllowed = false;
        }
        if (typeof toReturn == 'string') {
            if (toReturn == 'undefined') {
                toReturn = undefined;
            } else if (toReturn.toLowerCase() == 'null') {
                toReturn = null;
            }
        }
        return toReturn;
    },
    getDataForAdditionalFiles: function (realId, $cg_backend_info_container) {

        var data;
        var order;

        if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
            data = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId];
            for (var orderMultipleFilesForPost in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
                if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(orderMultipleFilesForPost)) {
                    break;
                }
                if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderMultipleFilesForPost].isRealIdSource) {
                    data[orderMultipleFilesForPost] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer($cg_backend_info_container, true);
                }
            }
            order = Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length;
            order++;
        } else {
            data = {};
            order = 1;
            data[order] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer($cg_backend_info_container, true);
            order++;
        }

        return {
            'order': order,
            'data': data
        }

    },
    getMultipleFileForPost: function ($sortableDiv, realId) {
        var cg_multiple_files_for_post = $sortableDiv.find('.cg_backend_info_container .cg_multiple_files_for_post').val();
        if (cg_multiple_files_for_post) {
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = JSON.parse(cg_multiple_files_for_post);
            for (var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
                if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)) {
                    break;
                }
                if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isRealIdSource) {
                    cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer(cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container'), realId);
                }
            }
        } else {
            delete cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId];
        }
        return cg_multiple_files_for_post;
    },
    hideCgBackendBackgroundDropAndContainer: function (isHideSlow) {
        jQuery('.cg_backend_action_container').addClass('cg_hide');
        jQuery('#cg_main_container').removeClass('cg_active cg_pointer_events_none');
        var $cgBackendGalleryDynamicMessage = jQuery('#cgBackendGalleryDynamicMessage');
        $cgBackendGalleryDynamicMessage.removeClass('cgMediaAssignProcess cg_overflow_y_scroll');// remove potential set classes before
        if ($cgBackendGalleryDynamicMessage.hasClass('cg_no_action_message')) {
            if (isHideSlow) {
                jQuery('#cgBackendBackgroundDrop').addClass('cg_hide').removeClass('cg_active cg_pointer_events_none');
            } else {
                jQuery('#cgBackendBackgroundDrop').addClass('cg_hide').removeClass('cg_active cg_pointer_events_none');
            }
        } else {
            if (isHideSlow) {
                jQuery('#cgBackendBackgroundDrop,.cg_background_drop_content').addClass('cg_hide').removeClass('cg_active cg_pointer_events_none');
            } else {
                jQuery('#cgBackendBackgroundDrop,.cg_background_drop_content').addClass('cg_hide').removeClass('cg_active cg_pointer_events_none');
            }
        }
        jQuery('body').removeClass('cg_no_scroll');
    },
    hideMultipleFilesContainerForPost: function ($) {
        $('#cgMultipleFilesForPostContainer').addClass('cg_hide');
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
    },
    hideShowAddAdditionalFilesLabel: function ($cg_preview_files_container, realId, $cg_backend_info_container) {

        if (!$cg_preview_files_container.find('.cg_backend_image_add_files_label').length) {
            $cg_preview_files_container.append('<div class="cg_hover_effect cg_backend_image_add_files_label cg_hide"></div>');
        }

        if (location.search.indexOf('page=contest-gallery-pro') > -1 && Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length < 10) {
            $cg_preview_files_container.find('.cg_backend_image_add_files_label').removeClass('cg_hide');
        } else {
            if (location.search.indexOf('page=contest-gallery-pro') == -1 && Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length < 3) {
                $cg_preview_files_container.find('.cg_backend_image_add_files_label').removeClass('cg_hide');
            }
        }

        if (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length == 1) {
            $cg_backend_info_container.find('.cg_manage_multiple_files_for_post, .cg_manage_multiple_files_for_post_prev').addClass('cg_hide');
        }

    },
    initDateTimePicker: function ($) {

        $(".cg_input_date_class").datepicker({
            beforeShow: function (input, inst) {
                $('#ui-datepicker-div').addClass('cg_admin_images_area_form');
                //$('#ui-datepicker-div').addClass($('#cg_fe_controls_style_user_upload_form_shortcode').val()); no style check in the moment
                $('#ui-datepicker-div.cg_upload_form_container .ui-datepicker-next').attr('title', '');
            },
            changeMonth: true,
            changeYear: true,
            monthNames: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
            monthNamesShort: ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"],
            yearRange: "-100:+100",
            //   option: {dateFormat:"dd.mm.yy"}
        });

        $(".cg_input_date_class").each(function () {
            var cgDateFormat = $(this).closest('.cg_image_title_container').find('.cg_date_format').val().toLowerCase().replace('yyyy', 'yy');
            var value = $(this).val();
            // have to be done in extra row here
            $(this).datepicker("option", "dateFormat", cgDateFormat);
            $(this).val(value);// value has to be set again after format is set!
        });

        $("#ui-datepicker-div").hide();

    },
    isEmbed: function (ImgType) {
        if (ImgType == 'inst' || ImgType == 'ytb' || ImgType == 'tkt' || ImgType == 'twt') {
            return true;
        }
        return false;
    },
    loadBlockquote: function ($, realIdReloadEntry) {

        var $cgSortable = $('#cgSortable');
        var $cgVotesImageVisualContent = $('#cgVotesImageVisualContent');
        var votesRealId = 0;
        if ($cgVotesImageVisualContent.length) {
            votesRealId = $('#cg_picture_id').val();
        }
        var $cgCommentsImageVisualContent = $('#cgCommentsImageVisualContent');
        var commentsRealId = 0;
        debugger
        if ($cgCommentsImageVisualContent.length) {
            commentsRealId = $('#cg_picture_id_comments').val();
        }
        debugger
        if (typeof cg_twitter_blockquotes != 'undefined') {
            for (var realId in cg_twitter_blockquotes) {
                if (typeof realIdReloadEntry != 'undefined' && realId != realIdReloadEntry) {
                    continue;
                }
                if (!cg_twitter_blockquotes.hasOwnProperty(realId)) {
                    break;
                }
                for (var WpUpload in cg_twitter_blockquotes[realId]) {
                    if (!cg_twitter_blockquotes[realId].hasOwnProperty(WpUpload)) {
                        break;
                    }
                    if ($cgSortable.length) {
                        var $cg_backend_info_container = $cgSortable.find('.cg_backend_info_container[data-cg-real-id="' + realId + '"][data-cg-wp-upload="' + WpUpload + '"]');
                        if ($cg_backend_info_container.length) {
                            $cgSortable.find('#cg_backend_image_twt' + realId).empty().append($(cg_twitter_blockquotes[realId][WpUpload]));
                        }
                    }
                    if (votesRealId && votesRealId == realId) {
                        $cgVotesImageVisualContent.find('.cg_backend_image_twt').empty().append($(cg_twitter_blockquotes[realId]));
                    }
                    if (commentsRealId && commentsRealId == realId) {
                        $cgCommentsImageVisualContent.find('.cg_backend_image_twt').empty().append($(cg_twitter_blockquotes[realId]));
                    }
                }
            }
        }

        if (typeof cg_tiktok_blockquotes != 'undefined') {
            for (var realId in cg_tiktok_blockquotes) {
                if (typeof realIdReloadEntry != 'undefined' && realId != realIdReloadEntry) {
                    continue;
                }
                if (!cg_tiktok_blockquotes.hasOwnProperty(realId)) {
                    break;
                }
                for (var WpUpload in cg_tiktok_blockquotes[realId]) {
                    if (!cg_tiktok_blockquotes[realId].hasOwnProperty(WpUpload)) {
                        break;
                    }
                    if ($cgSortable.length) {
                        var $cg_backend_info_container = $cgSortable.find('.cg_backend_info_container[data-cg-real-id="' + realId + '"][data-cg-wp-upload="' + WpUpload + '"]');
                        if ($cg_backend_info_container.length) {
                            $cgSortable.find('#cg_backend_image_tkt' + realId).empty().append($(cg_tiktok_blockquotes[realId][WpUpload]));
                        }
                    }
                    if (votesRealId && votesRealId == realId) {
                        $cgVotesImageVisualContent.find('.cg_backend_image_tkt').empty().append($(cg_tiktok_blockquotes[realId]));
                    }
                    if (commentsRealId && commentsRealId == realId) {
                        $cgCommentsImageVisualContent.find('.cg_backend_image_tkt').empty().append($(cg_tiktok_blockquotes[realId]));
                    }
                }
            }
        }

    },
    manageMultipleFilesForPost: function ($, $button, isFromSelect) {
        debugger
        /*if(isFromClick){
            // if is click from sortable div then there were no changes before and previous changes can be reseted
            cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = false;
        }*/

        var $cg_backend_info_container = $button.closest('.cg_backend_info_container');
        cgJsClassAdmin.gallery.vars.$cg_backend_info_container = $cg_backend_info_container;
        cgJsClassAdmin.gallery.vars.$sortableDiv = $cg_backend_info_container.closest('.cgSortableDiv');
        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var realId = $cg_backend_info_container.attr('data-cg-real-id');
        var cg_multiple_files_for_post = $cg_backend_info_container.find('.cg_multiple_files_for_post').val();

        if (!cg_multiple_files_for_post) {
            data = {};
            order = 1;
            data[order] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer($cg_backend_info_container, true);
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = data;
        } else {
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = JSON.parse(cg_multiple_files_for_post);
        }

        console.log('cg_manage_multiple_files_for_post first time');
        console.log(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]);

        var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
        $cgMultipleFilesForPostContainer.attr('data-cg-real-id', realId);
        var $cgMultipleFilesForPostContainerFadeBackground = $('#cgMultipleFilesForPostContainerFadeBackground');
        $cgMultipleFilesForPostContainer.removeClass('cg_hide');
        $cgMultipleFilesForPostContainerFadeBackground.removeClass('cg_hide').addClass('cg_active');
        $cgMultipleFilesForPostContainer.find('#cg_multiple_files_file_for_post_ecommerce_processing').addClass('cg_hide');
        $cgMultipleFilesForPostContainer.find('#cg_multiple_files_file_for_post_ecommerce_download_files_removed_from_sale').addClass('cg_hide');

        var $cg_preview_files_container = $cgMultipleFilesForPostContainer.find('.cg_preview_files_container');
        $cg_preview_files_container.empty();

        // body only!
        jQuery('body').addClass('cg_no_scroll');

        for (var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {

            if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)) {
                break;
            }

            var WpUpload = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'];

            var cg_data_is_real_id_source = '';
            var cg_hide_remove_button = '';
            if (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length === 1) {
                cg_hide_remove_button = 'cg_hide';
            }

            if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isRealIdSource) {
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order] = cgJsClassAdmin.gallery.functions.setMultipleFileForPostFromBackendInfoContainer($cg_backend_info_container, realId, cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isEcommerceEntryAndDownload);
                cg_data_is_real_id_source = 'data-cg-is-real-id-source="true"';
            }

            var WpUploadFilesForSaleArrayLoaded = cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded(cgJsClassAdmin.gallery.vars.$sortableDiv);

            var cg_is_new_ecommerce_image_class = '';
            var cg_is_new_ecommerce_download_class = '';
            var cg_replace = '';
            debugger
            if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].newAddedEcommerceImageForDownload) {
                $cgMultipleFilesForPostContainer.find('#cg_multiple_files_file_for_post_ecommerce_processing').removeClass('cg_hide');
                cg_is_new_ecommerce_image_class = 'cg_is_new_ecommerce_image_class';
            } else {
                // then must be some other download file as image
                if (cgJsClassAdmin.gallery.vars.$sortableDiv.find('.SaleType').val() == 'download' && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].type != 'image' && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].newAddedEcommerceDownload) {
                    $cgMultipleFilesForPostContainer.find('#cg_multiple_files_file_for_post_ecommerce_processing').removeClass('cg_hide');
                    cg_is_new_ecommerce_download_class = 'cg_is_new_ecommerce_download_class';
                }
                if (cgJsClassAdmin.gallery.vars.multipleFilesNewAdded[realId] && cgJsClassAdmin.gallery.vars.multipleFilesNewAdded[realId].indexOf(parseInt(WpUpload)) > -1 && isFromSelect) {

                } else {// replace should be shown if already an added one
                    cg_replace = '<div class="cg_replace">Replace</div>';
                }
            }

            var data = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order];
            /*data.ExifParsed = (data.Exif) ? (JSON.parse(data.Exif)) : '';*/

            var isEcommerceEntryAndDownload = 0;
            if (data.isEcommerceEntryAndDownload || (cgJsClassAdmin.gallery.vars.$sortableDiv.find('.SaleType').val() == 'download' && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isRealIdSource)) {
                isEcommerceEntryAndDownload = 1;
            }

            var cg_is_for_sale = '';
            var for_sale_div = '';
            var cg_for_sale_div_container = '';
            debugger
            if (WpUploadFilesForSaleArrayLoaded.indexOf(parseInt(data.WpUpload)) > -1) {
                cg_is_for_sale = 'cg_is_for_sale';
                cg_for_sale_div_container = 'cg_for_sale_div_container';
                for_sale_div = '<div class="cg_for_sale_div">Is for sale</div>';
            }

            var $divElement = $('<div ' + cg_data_is_real_id_source + '  data-cg-real-id="' + realId + '" data-cg-wp-upload-id="' + data.WpUpload + '"  class=" ' + cg_is_for_sale + ' cg_backend_image_full_size_target_container ' + cg_for_sale_div_container + ' ' + cg_is_new_ecommerce_image_class + ' ' + cg_is_new_ecommerce_download_class + '">' +
                '<div class="cg_hover_effect cg_backend_image_full_size_target_container_rotate cg_hide" data-cg-real-id="' + realId + '"></div>' +
                for_sale_div +
                '<div class="cg_backend_image_full_size_target_container_drag"></div>' +
                '<div class="cg_hover_effect cg_backend_image_full_size_target_container_remove  ' + cg_hide_remove_button + ' ' + cg_is_for_sale + '"  data-cg-wp-upload-id="' + data.WpUpload + '" data-isecommerceentryanddownload="' + isEcommerceEntryAndDownload + '" data-cg-real-id="' + realId + '"></div>' +
                '<div class="cg_backend_image_full_size_target_container_position">' + order + '</div>' +
                '<div class="cg_backend_image_full_size_target cg_backend_image_full_size_target_container_' + data.ImgType + '" ' +
                'data-file-type="' + data.ImgType + '" data-file-name="' + data.name + '" data-original-src="' + data.guid + '"></div>' +
                cg_replace +
                '</div>');

            if (data.WpUploadRemoved) {
                $divElement.addClass('cg_backend_image_full_size_target_container_empty');
            }

            debugger

            if (data.type == 'image') {
                if(data.PdfPreview > 0){// has to be tested that way: .PdfPreview > 0
                    var rThumb = 0;
                    $divElement.find('.cg_backend_image_full_size_target').attr('style',
                        'background: url("' + data.PdfPreviewImageLarge + '?time=' + (new Date().getTime() - 1000) + '") center center no-repeat;background-size: contain !important;'
                    ).addClass('cg' + rThumb + 'degree').attr('data-file-name');
                    $divElement.find('.cg_backend_image_full_size_target_container_rotate').addClass('cg_hide').attr('data-cg-rThumb', rThumb);
                    if(cg_is_for_sale){
                        $divElement.find('.cg_backend_image_full_size_target').wrap('<a href="' + data.PdfPreviewImageLarge + '?time=' + (new Date().getTime() - 1000) + '" target="_blank" ></a>');
                    }else{
                        $divElement.find('.cg_backend_image_full_size_target').wrap('<a href="' + data.PdfOriginal + '?time=' + (new Date().getTime() - 1000) + '" target="_blank" ></a>');
                    }
                }else{
                    var rThumb = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['rThumb'];
                    $divElement.find('.cg_backend_image_full_size_target').css('background', 'url("' + data.large + '?time=' + (new Date().getTime() - 1000) + '") center center no-repeat').addClass('cg' + rThumb + 'degree').attr('data-file-name');
                    $divElement.find('.cg_backend_image_full_size_target_container_rotate').removeClass('cg_hide').attr('data-cg-rThumb', rThumb);
                    $divElement.find('.cg_backend_image_full_size_target').wrap('<a href="' + data.guid + '?time=' + (new Date().getTime() - 1000) + '" target="_blank" ></a>');
                }
            } else if (data.type == 'video') {
                $divElement.addClass('cg_backend_image_full_size_target_container_video cg_backend_image_full_size_target_container_video_' + data.ImgType);
                $divElement.find('.cg_backend_image_full_size_target').addClass('cg_backend_image_full_size_target_video');
                if (WpUploadFilesForSaleArrayLoaded.indexOf(parseInt(data.WpUpload)) > -1) {
                    $divElement.append('<div class="cg_backend_image_full_size_target_name" >' + data.NamePic + '</div>');
                } else {
                    $divElement.append($('<div style="overflow: hidden;max-height: 100%;"><a href="' + data.guid + '" target="_blank" ><div class="cg_video_container"><video width="160" >' +
                        '<source src="' + data.guid + '#t=0.001" type="video/mp4"/>' +
                        '<source src="' + data.guid + '#t=0.001" type="video/' + data.ImgType + '"/>' +
                        '</video></div></a></div>'));
                }
            } else if (data.type == 'embed') {
                $divElement.addClass('cg_backend_image_full_size_target_container_embed cg_backend_image_full_size_target_container_embed_' + data.ImgType);
                $divElement.find('.cg_backend_image_full_size_target').addClass('cg_backend_image_full_size_target_embed');
                if (data.ImgType == 'ytb' || data.ImgType == 'inst') {
                    $divElement.find('.cg_backend_image_full_size_target').append($('<div style="overflow: hidden;max-height: 100%;"><iframe width="160" src="' + data.guid + '">' +
                        '</iframe></div>'));
                } else if (data.ImgType == 'tkt') {
                    $divElement.find('.cg_backend_image_full_size_target').append($('<div class="cg_backend_image cg_backend_image_tkt" ></div>'));
                    var blockquote = (cgJsClassAdmin.gallery.vars.embedById[WpUpload]) ? cgJsClassAdmin.gallery.vars.embedById[WpUpload] : cg_tiktok_blockquotes[realId][WpUpload];
                    $divElement.find('.cg_backend_image_tkt').append($(blockquote));
                } else if (data.ImgType == 'twt') {
                    $divElement.find('.cg_backend_image_full_size_target').append($('<div class="cg_backend_image cg_backend_image_twt" ></div>'));
                    var blockquote = (cgJsClassAdmin.gallery.vars.embedById[WpUpload]) ? cgJsClassAdmin.gallery.vars.embedById[WpUpload] : cg_twitter_blockquotes[realId][WpUpload];
                    $divElement.find('.cg_backend_image_twt').append($(blockquote));
                }
                //$divElement.append('<div class="cg_backend_image_full_size_target_name" >'+data.NamePic+'</div>');
            } else {
                if(data.PdfPreview > 0){// has to be tested that way: .PdfPreview > 0
                    var rThumb = 0;
                    $divElement.find('.cg_backend_image_full_size_target').attr('style',
                            'background: url("' + data.PdfPreviewImageLarge + '?time=' + (new Date().getTime() - 1000) + '") center center no-repeat;background-size: contain !important;'
                    ).addClass('cg' + rThumb + 'degree').attr('data-file-name');
                    $divElement.find('.cg_backend_image_full_size_target_container_rotate').addClass('cg_hide').attr('data-cg-rThumb', rThumb);
                    $divElement.find('.cg_backend_image_full_size_target').wrap('<a href="' + data.PdfOriginal + '?time=' + (new Date().getTime() - 1000) + '" target="_blank" ></a>');
                }else{
                    $divElement.addClass('cg_backend_image_full_size_target_container_alternative_file_type cg_backend_image_full_size_target_container_' + data.ImgType);
                    $divElement.append('<div class="cg_backend_image_full_size_target_name" >' + data.NamePic + '</div>');
                    if (data.ImgType == 'pdf' && $('#cgPdfPreviewBackend').val()==1) {
                        $divElement.append('<div class="cg_pdf_preview_to_create" >PDF preview will be added</div>');
                    }
                    $divElement.find('.cg_backend_image_full_size_target').wrap('<a href="' + data.guid + '" target="_blank" ></a>');
                }

            }
            $cg_preview_files_container.append($divElement);
        }

        console.log('cg_manage_multiple_files_for_post ready time');
        console.log(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]);

        $("#cgMultipleFilesForPostContainer .cg_preview_files_container").sortable({
            items: ".cg_backend_image_full_size_target_container:not(.cg_backend_image_add_files_label)",
            handle: ".cg_backend_image_full_size_target_container_drag",
            cursor: "move",
            placeholder: "ui-state-highlight",
            start: function (event, ui) {
                cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
                var $element = $(ui.item);
                $element.closest('#cgMultipleFilesForPostContainer').find('.ui-state-highlight').addClass($element.get(0).classList.value).html($element.html());
            },
            stop: function () {
                setTimeout(function () {
                    cgJsClassAdmin.gallery.functions.resortMultipleFiles(realId);
                }, 10);
            }
        });

        cgJsClassAdmin.gallery.functions.hideShowAddAdditionalFilesLabel($cg_preview_files_container, realId, $cg_backend_info_container);

    },
    markSearchedValueFields: function ($) {

        var $cgSearchInput = $('#cgSearchInput');
        var cgSearchInputValue = $cgSearchInput.val().trim();
        if (cgSearchInputValue) {

            $('#cgSearchInputClose').removeClass('cg_hide');
            $('#cgSearchInputButton').removeClass('cg_hide');
            var cgSearchedValueHiddenFieldsSelector = '#cgSortable .cg_wp_user_display_name,#cgSortable .cg_wp_user_email,#cgSortable .cg_wp_user_nicename,#cgSortable .cg_wp_user_login,' +
                '#cgSortable .cg_wp_post_content, #cgSortable .cg_wp_post_name, #cgSortable .cg_wp_post_title, #cgSortable .cg_image_id,#cgSortable .cg_cookie_id_or_ip';
            var $inputFieldsWithValue = $(cgJsClassAdmin.gallery.vars.cgChangedAndSearchedValueSelector).filter(function () {
                return $(this).val().toLowerCase().indexOf(cgSearchInputValue.toLowerCase()) != -1;
            });
            var $inputHiddenFieldsWithValue = $(cgSearchedValueHiddenFieldsSelector).filter(function () {
                return $(this).val().toLowerCase().indexOf(cgSearchInputValue.toLowerCase()) != -1;
            });
            $cgSearchInput.addClass('cg_searched_value');
            $inputHiddenFieldsWithValue.closest('.cg_backend_info_container').addClass('cg_searched_value');
            $inputFieldsWithValue.addClass('cg_searched_value');

            var $cgCategorySelects = $('#cgSortable .cg_category_select option:selected').filter(function () {
                return $(this).html().toLowerCase() === cgSearchInputValue.toLowerCase();
            }).closest('.cg_category_select');
            $cgCategorySelects.addClass('cg_searched_value');

            $('#cgStepsNavigationTop .cg_step, #cgStepsNavigationBottom .cg_step').not('.cg_step_selected').addClass('cg_searched_value');
        }

        var $cgOrderSelect = $('#cgOrderSelect');
        var cgOrderSelectValue = $cgOrderSelect.val();
        if (cgOrderSelectValue != 'custom') {
            $cgOrderSelect.addClass('cg_searched_value');
        } else {
            $cgOrderSelect.removeClass('cg_searched_value');
        }

        if (cgOrderSelectValue == 'comments_desc') {
            $('.cg_image_action_comments').addClass('cg_searched_value');
        } else {
            $('.cg_image_action_comments').removeClass('cg_searched_value');
        }

        $('#cgSortable .cg-exif-text').each(function () {
            if (cgSearchInputValue.trim() != '') {
                if ($(this).text().toLowerCase().indexOf(cgSearchInputValue.toLowerCase()) != -1) {
                    $(this).addClass('cg_searched_value');
                }
            }
        });

    },
    markSortedByCustomFields: function ($) {

        $('#cgSortable .cg_short_text').removeClass('cg_by_search_sorted');
        $('#cgSortable .cg_category_select').removeClass('cg_by_search_sorted');
        $('#cgSortable .cg_for_id_wp_username_by_search_sort').removeClass('cg_by_search_sorted');

        var $cgOrderSelectCustomFieldsSelectedInput = $('#cgOrderSelectCustomFields option:selected,#cgOrderSelectFurtherFields option:selected');

        if ($cgOrderSelectCustomFieldsSelectedInput.length) {
            $('#cgSortable .' + $cgOrderSelectCustomFieldsSelectedInput.attr('data-cg-input-fields-class')).addClass('cg_by_search_sorted');
        }

    },
    missingRights: function ($, response) {
        if (response.indexOf('MISSINGRIGHTS') >= 0) {
            response = response.split('MISSINGRIGHTS')[1];
            var htmlDom = $.parseHTML(response);
            $(htmlDom).insertAfter(jQuery('#cgViewControl'));
            $('#cgGalleryLoader').addClass('cg_hide');
            return true;
        } else {
            return false;
        }
    },
    orderDescendAttachments: function (attachment, isForGalleryAdd) {
        var arrReverse = [];
        var iAdd = 0;
        var idToCheck = 0;
        var hasToReverse = false;// so always looks like in media library from left to right
        attachment.forEach(function (value, index) {
            if (!idToCheck) {
                idToCheck = value['id'];
            } else {
                if (isForGalleryAdd) {
                    if (parseInt(idToCheck) > parseInt(value['id'])) {
                        hasToReverse = true;
                    }
                } else {// then must be for add additional files
                    if (parseInt(idToCheck) < parseInt(value['id'])) {
                        hasToReverse = true;
                    }
                }
            }
        });
        if (hasToReverse) {
            for (var i = attachment.length - 1; i >= 0; i--) {
                arrReverse[iAdd] = attachment[i];
                iAdd++;
            }
            return arrReverse;
        } else {
            return attachment;
        }

    },
    priceToShow: function (Price, PriceDivider, cgCurrencySelectPosition, cgCurrencySymbol) {
        var priceToShow = Price;
        if (cgCurrencySelectPosition == 'left') {
            priceToShow = cgCurrencySymbol + priceToShow;
        } else if (cgCurrencySelectPosition == 'right') {
            priceToShow = priceToShow + cgCurrencySymbol;
        }

        return priceToShow;
    },
    priceToShowAll: function ($cgSortable) {

        $cgSortable.find('.cg_for_sale_price_container:not(.cg_hide)').each(function () {
            var $cg_backend_image_full_size_target = jQuery(this).closest('.cg_backend_image_full_size_target');
            var Price = $cg_backend_image_full_size_target.find('.Price').val();

            var PriceDivider = $cg_backend_image_full_size_target.find('.PriceDivider').val();
            var CurrencyShort = $cg_backend_image_full_size_target.find('.CurrencyShort').val();
            var CurrencySymbol = $cg_backend_image_full_size_target.find('.CurrencySymbol').val();
            var CurrencyPosition = $cg_backend_image_full_size_target.find('.CurrencyPosition').val();

            var priceToShow = cgJsClassAdmin.gallery.functions.priceToShow(Price, PriceDivider, CurrencyPosition, CurrencySymbol);

            $cg_backend_image_full_size_target.find('.cg_for_sale_price').text(priceToShow);
        });
    },
    reloadAfterAddingEntries: function ($, response, isImagesAdded) {

        $("#cgAddImagesWpUploader").css("display", "block");
        var gid = $('#cgBackendGalleryId').val();
        $('#cgOrderSelect #cg_custom').prop('selected', true);
        localStorage.setItem('cgOrder_BG_' + gid, $('#cgOrderSelect').val());
        var $cgSearchInput = $('#cgSearchInput');
        $cgSearchInput.val('');
        $cgSearchInput.removeClass('cg_searched_value');
        localStorage.setItem('cgSearch_BG_' + gid, $cgSearchInput.val());

        if ($('#cgStepsNavigationTop .cg_step').length) {// reset start value to 0 then here!
            $('#cgStartValue').val(0);// input field for start value
            localStorage.setItem('cgStart_BG_' + gid, 0);
        }

        $('#cgViewControl').find('.cg_image_checkbox').removeClass('cg_active');
        $('#cgSearchInputClose').addClass('cg_hide');
        $('#cgSearchInputButton').addClass('cg_hide');
        $('#cgShowOnlyWinnersCheckbox').removeClass('cg_searched_value_checkbox').prop('checked', false);

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        if (response.indexOf('cg-images-added') != -1) {
            isImagesAdded = true;
        }

        cgJsClassAdmin.gallery.load.init($, true, null, isImagesAdded, response);

    },
    removeUsedFrames: function () {
        var $usedFrames = jQuery('.supports-drag-drop[id*="wp-uploader-id-"]');
        $usedFrames.remove();
    },
    requests: [],
    resortMultipleFiles: function (realId) {
        console.log('stop sortable');
        console.log(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]);
        var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$cg_backend_info_container;

        var order = 1;
        var newOrderedData = {};
        jQuery("#cgMultipleFilesForPostContainer .cg_preview_files_container .cg_backend_image_full_size_target_container_position").each(function () {
            newOrderedData[order] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][jQuery(this).text()];
            jQuery(this).text(order);
            order++;
        });
        cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = newOrderedData;
        cgJsClassAdmin.gallery.functions.setSimpleDataRealIdSource(realId, $cg_backend_info_container);

        $cg_backend_info_container.find('.cg_backend_image').removeClass('cg_hide');

        $cg_backend_info_container.find('.cg_backend_image_full_size_target > a').attr('href', 'url(' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '?time=' + (new Date().getTime() - 1000) + ') no-repeat center');
        var $cg_backend_image_full_size_target = $cg_backend_info_container.find('.cg_backend_image_full_size_target');
        $cg_backend_image_full_size_target.attr({
            'data-file-type': cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['ImgType'],
            'data-name-pic': cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['NamePic'],
            'data-original-src': cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid']
        });
        $cg_backend_image_full_size_target.empty();
        console.log('sortable');
        console.log(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]);

        if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['type'] == 'image') {
            $cg_backend_image_full_size_target.append('<a href="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '" target="_blank" title="Show full size" alt="Show full size">\n' +
                '<div class="cg' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['rThumb'] + 'degree cg_backend_image" style="background: url(' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['large'] + '?time=' + (new Date().getTime() - 1000) + ') no-repeat center"></div></a>');
            $cg_backend_info_container.find('.cg_rotate_image_backend').removeClass('cg_hide');
        } else if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['type'] == 'video') {
            $cg_backend_image_full_size_target.append('<a href="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '" target="_blank" title="Show file" alt="Show file">\n' +
                '<video width="160" height="106">' +
                '<source src="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '#t=0.001" type="video/mp4"/>' +
                '<source src="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '#t=0.001" type="video/' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['ImgType'] + '"/>' +
                '</video></a>');
            $cg_backend_info_container.find('.cg_rotate_image_backend,.cg_backend_rotate_css_based').addClass('cg_hide');
        } else { // then alternative file type
            $cg_backend_image_full_size_target.append('<a href="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['guid'] + '" target="_blank" title="Show file" alt="Show file">\n' +
                '<div class="cg_backend_image_full_size_target_' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['ImgType'] + ' cg_backend_image_full_size_target_alternative_file_type" data-cg-file-type="' + cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1]['ImgType'] + '"></div></a>');
            $cg_backend_info_container.find('.cg_rotate_image_backend,.cg_backend_rotate_css_based').addClass('cg_hide');
        }

        var $cg_sortable_div = $cg_backend_info_container.closest('.cg_sortable_div');
        $cg_sortable_div.find('.cg-center-image-exif-data-not-checked').addClass('cg_hide');

        if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].Exif && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].IsExifDataChecked) {
            var data = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1];
            if (typeof data.Exif == 'string') {
                data.Exif = JSON.parse(data.Exif);
            }
            $cg_sortable_div.find('.cg_exif_data_container').removeClass('cg_hide');
            $cg_sortable_div.find('.cg-exif').addClass('cg_hide');
            $cg_sortable_div.find('.cg-center-image-exif-no-data').addClass('cg_hide');
            if (data.Exif.DateTimeOriginal) {
                $cg_sortable_div.find('.cg-exif-date-time-original').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-date-time-original-text').text(data.Exif.DateTimeOriginal.split(' ')[0].replaceAll(':', '-'));
            }
            if (data.Exif.MakeAndModel) {
                $cg_sortable_div.find('.cg-exif-model').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-model-text').text(data.Exif.MakeAndModel);
            } else if (data.Exif.Model) {
                $cg_sortable_div.find('.cg-exif-model').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-model-text').text(data.Exif.Model);
            }
            if (data.Exif.ApertureFNumber) {
                $cg_sortable_div.find('.cg-exif-aperturefnumber').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-aperturefnumber-text').text(data.Exif.ApertureFNumber);
            }
            if (data.Exif.ExposureTime) {
                $cg_sortable_div.find('.cg-exif-exposuretime').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-exposuretime-text').text(data.Exif.ExposureTime);
            }
            if (data.Exif.ISOSpeedRatings) {
                $cg_sortable_div.find('.cg-exif-isospeedratings').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-isospeedratings-text').text(data.Exif.ISOSpeedRatings);
            }
            if (data.Exif.FocalLength) {
                $cg_sortable_div.find('.cg-exif-focallength').removeClass('cg_hide');
                $cg_sortable_div.find('.cg-exif-focallength-text').text(data.Exif.FocalLength);
            }
        } else if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].IsExifDataChecked && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].type == 'image') {
            $cg_sortable_div.find('.cg_exif_data_container').removeClass('cg_hide');
            $cg_sortable_div.find('.cg-exif').addClass('cg_hide');
            $cg_sortable_div.find('.cg-center-image-exif-no-data').removeClass('cg_hide');
        } else if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].IsExifDataChecked && cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].type == 'image') {
            $cg_sortable_div.find('.cg_exif_data_container').removeClass('cg_hide');
            $cg_sortable_div.find('.cg-exif').addClass('cg_hide');
            $cg_sortable_div.find('.cg-center-image-exif-no-data').addClass('cg_hide');
            $cg_sortable_div.find('.cg-center-image-exif-data-not-checked').removeClass('cg_hide');
        } else {
            $cg_sortable_div.find('.cg_exif_data_container').addClass('cg_hide');
        }

    },
    searchInputButtonClick: function () {

        cgJsClassAdmin.gallery.functions.abortRequest();

        // to go simply sure that nothing will be deleted!!!
        jQuery('#cgGalleryForm').find('.cg_delete').remove();

        cgJsClassAdmin.gallery.load.changeViewByControl(jQuery, null, null, null, true);

    },
    selectAddAdditionalFiles: function (attachment, file_frame, data, $cg_backend_info_container, isDynamicMessageVisible, realId, isReplace, newWpUpload, newImgType, NewWpUploadWhichReplace, order, WpUploadToReplace, $) {
        debugger
        var i = 0;
        var countAdded = 0;
        var isBreakDone = false;
        var isEcommerceEntryAndDownload = false;
        debugger
        while (i < attachment.length) {

            var hasRealIdAlready = false;

            for (var orderToCheck in data) {
                if (!data.hasOwnProperty(orderToCheck)) {
                    break;
                }
                if (data[orderToCheck].WpUpload == attachment[i].id || $cg_backend_info_container.attr('data-cg-wp-upload') == attachment[i].id) {
                    hasRealIdAlready = true;
                    break;
                }
            }

            if (hasRealIdAlready) {
                isDynamicMessageVisible = true;
                if (isReplace) {
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('The selected item for replace is already added');
                } else {
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('One of the items you trying to add is already added');
                }
                // will be autoclosed when message appear so originally sortableDiv has to be replaced with the clone
                cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
                i++;
                continue;
            }

            if (location.search.indexOf('page=contest-gallery-pro') > -1 && Object.keys(data).length >= 10 && !isReplace) {
                isDynamicMessageVisible = true;
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Maxim allowed additional files: <b>9</b>');
                // will be autoclosed when message appear so originally sortableDiv has to be replaced with the clone
                cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
                break;
            } else {
                if (location.search.indexOf('page=contest-gallery-pro') == -1 && Object.keys(data).length >= 3 && !isReplace) {
                    isBreakDone = true;
                    isDynamicMessageVisible = true;
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('<span style="font-weight: normal;">Maxim allowed additional files<br>in normal version: </span><b>2</b><br>' +
                        '<span style="font-weight: normal;">Maxim allowed additional files<br>in PRO version: </span><b>9</b>');
                    // will be autoclosed when message appear so originally sortableDiv has to be replaced with the clone
                    cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
                    break;
                }
            }

            console.log(attachment[i]);

            if (attachment[i].post_type === 'contest-gallery') {// then must be embed
                var ImgType = '';
                if (attachment[i].post_mime_type === 'contest-gallery-instagram') {
                    ImgType = 'inst';
                }
                if (attachment[i].post_mime_type === 'contest-gallery-tiktok') {
                    ImgType = 'tkt';
                }
                if (attachment[i].post_mime_type === 'contest-gallery-twitter') {
                    ImgType = 'twt';
                }
                if (attachment[i].post_mime_type === 'contest-gallery-youtube') {
                    ImgType = 'ytb';
                }
            } else {
                var ImgType = attachment[i].url.split('.')[attachment[i].url.split('.').length - 1].toLowerCase()// short version of type (subtype in wordpress), relevant for classes search
            }

            if (cgJsClassAdmin.gallery.vars.allowedFileEndings.indexOf(ImgType) == -1) {
                isDynamicMessageVisible = true;
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('One of the file types you trying to add is not allowed');
                i++;
                // will be autoclosed when message appear so originally sortableDiv has to be replaced with the clone
                cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
                continue;
            } else {
                if (location.search.indexOf('page=contest-gallery-pro') == -1 && ['pdf', 'zip', 'wav', 'mp3', 'mp4', 'mov'].indexOf(ImgType) > -1) {
                    isDynamicMessageVisible = true;
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('One of the file types you trying to add is only allowed to add in PRO version in backend');
                    i++;
                    // will be autoclosed when message appear so originally sortableDiv has to be replaced with the clone
                    cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
                    continue;
                }
            }
            countAdded++;

            if (typeof attachment[i].date == 'number') {// then must be new added and time * 1000
                attachment[i].date = new Date(attachment[i].date * 1000);
            }

            var id = attachment[i].id;
            var type = attachment[i].type;
            var name = attachment[i].name;
            var url = attachment[i].url;
            var alt = attachment[i].alt;
            var title = attachment[i].title;
            var description = attachment[i].description;
            var caption = attachment[i].caption;
            var mime = attachment[i].mime;

            if (attachment[i].post_type === 'contest-gallery') {// then must be embed
                var post_date = attachment[i].post_date;
                id = attachment[i].ID;
                type = 'embed';
                name = attachment[i].post_name;
                url = attachment[i].guid;
                alt = attachment[i].post_alt;
                title = attachment[i].post_title;
                name = attachment[i].post_name;
                description = attachment[i].post_content;
                caption = attachment[i].post_excerpt;
                mime = attachment[i].post_mime_type;
            } else {

                var hours = "0" + attachment[i].date.getHours();
                var minutes = "0" + attachment[i].date.getMinutes();
                var seconds = "0" + attachment[i].date.getSeconds();
                var timeReadable = hours.substr(-2) + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);

                var cgFormatDate = function (date) {
                    var d = new Date(date);
                    var month = '' + (d.getMonth() + 1);
                    var day = '' + d.getDate();
                    var year = d.getFullYear();
                    if (month.length < 2)
                        month = '0' + month;
                    if (day.length < 2)
                        day = '0' + day;
                    return [year, month, day].join('-');
                }

                var post_date = cgFormatDate(attachment[i].date) + ' ' + timeReadable;

            }

            newWpUpload = attachment[i].id;
            newImgType = ImgType;

            if (isReplace) {
                NewWpUploadWhichReplace = attachment[i].id;
            }

            data[order] = {
                WpUpload: id,
                order: order,
                rThumb: 0,
                Exif: '',
                post_alt: alt,
                post_title: title,
                post_name: name,
                post_content: description,
                post_excerpt: caption,
                post_mime_type: mime,
                post_date: post_date,
                NamePic: name,
                Width: 0,// should be simply set already here to avoid undefined array keys
                Height: 0,// should be simply set already here to avoid undefined array keys
                medium: '',// should be simply set already here to avoid undefined array keys
                large: '',// should be simply set already here to avoid undefined array keys
                full: url,
                guid: url,
                type: type,// WordPress type
                ImgType: ImgType
            }

            cgJsClassAdmin.gallery.vars.multipleFilesNewAdded[realId].push(parseInt(attachment[i].id));

            debugger

            // WordPress type
            if (attachment[i].type == 'image' && attachment[i].sizes) {
                data[order]['medium'] = (attachment[i].sizes.medium && attachment[i].sizes.medium.url) ? attachment[i].sizes.medium.url : attachment[i].url;// if medium not exists then image must be small, without generated medium
                data[order]['large'] = (attachment[i].sizes.large && attachment[i].sizes.large.url) ? attachment[i].sizes.large.url : attachment[i].url;// if large not exists then image must be small, without generated large
            } else {
                if (attachment[i].type == 'image') {
                    data[order]['large'] = attachment[i].url;
                }
            }
            if ((attachment[i].type == 'image' || attachment[i].type == 'video') && attachment[i].height) {
                data[order]['Width'] = attachment[i].width;
                data[order]['Height'] = attachment[i].height;
            }
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = data;
            if (attachment[i].type == 'image' && $cg_backend_info_container.find('.isEcommerceEntryAndDownload').val() == 1) {
                isEcommerceEntryAndDownload = true;
                // will be unseted in PHP
                data[order]['newAddedEcommerceImageForDownload'] = 1;
            } else if ($cg_backend_info_container.find('.isEcommerceEntryAndDownload').val() == 1) {
                data[order]['newAddedEcommerceDownload'] = 1;
            }
            i++;
            order++;
        }

        if (countAdded == 0) {
            return;
        }

        console.log('cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]');
        console.log(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]);

        var calculateFirst = Object.keys(data).length - 1;
        var toStringFirst = '+' + calculateFirst;

        $cg_backend_info_container.find('.cg_manage_multiple_files_for_post_prev, .cg_manage_multiple_files_for_post').removeClass('cg_hide');
        $cg_backend_info_container.find('.cg_manage_multiple_files_for_post').text(toStringFirst);

        cgJsClassAdmin.gallery.functions.setSimpleDataRealIdSource(realId, $cg_backend_info_container, WpUploadToReplace, NewWpUploadWhichReplace);

        if (location.search.indexOf('page=contest-gallery-pro') > -1 && Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length >= 10) {
            $cg_backend_info_container.find('.cg_add_multiple_files_to_post, .cg_add_multiple_files_to_post_prev').addClass('cg_hide');
        } else {
            if (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length >= 10) {
                $cg_backend_info_container.find('.cg_add_multiple_files_to_post, .cg_add_multiple_files_to_post_prev').addClass('cg_hide');
            }
        }

        $cg_backend_info_container.find('.cg_manage_multiple_files_for_post, .cg_manage_multiple_files_for_post_prev').addClass('cg_blink');
        debugger
        setTimeout(function () {
            $cg_backend_info_container.find('.cg_manage_multiple_files_for_post, .cg_manage_multiple_files_for_post_prev').removeClass('cg_blink');
        }, 2000);
        debugger
        if (!isDynamicMessageVisible && !isReplace) {
            cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
            cgJsClassAdmin.gallery.functions.manageMultipleFilesForPost($, $cg_backend_info_container.find('.cg_manage_multiple_files_for_post'), true);
        } else if (isReplace) {
            debugger
            $('#cgWpUploadToReplace').val(WpUploadToReplace);
            $('#cgNewWpUploadWhichReplace').val(NewWpUploadWhichReplace);
            cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
            cgJsClassAdmin.gallery.vars.multipleFilesNewAdded[realId] = [];// has to be reseted
            debugger
            cgJsClassAdmin.gallery.reload.entry(realId, true, false, NewWpUploadWhichReplace, WpUploadToReplace, newImgType, false, $cg_backend_info_container, WpUploadToReplace);
        }
        /*            if(countAdded && !isBreakDone){// seems to be better without it for understanding in the moment
                        $cg_backend_info_container.find('.cg_manage_multiple_files_for_post').click();
                    }*/
    },
    setAndAppearBackendGalleryDynamicMessage: function (message, isNoActionMessage, classToAdd, isBackendActionContainer, isShowLoader) {
        var $cgBackendBackgroundDrop = jQuery('#cgBackendBackgroundDrop');
        var $cgBackendGalleryDynamicMessage = jQuery('#cgBackendGalleryDynamicMessage');
        var wasLoader = false;
        if (!$cgBackendGalleryDynamicMessage.find('.cg-lds-dual-ring-div-gallery-hide').hasClass('cg_hide')) {
            wasLoader = true;
            $cgBackendGalleryDynamicMessage.find('.cg-lds-dual-ring-div-gallery-hide').addClass('cg_hide');
        }

        if (message && !$cgBackendGalleryDynamicMessage.hasClass('cg_hide') && wasLoader) {
            $cgBackendGalleryDynamicMessage.find('.cg_notification_message_dynamic_content').html(message);
        } else if ($cgBackendBackgroundDrop.hasClass('cg_hide') && !isNoActionMessage) {
            $cgBackendBackgroundDrop.removeClass('cg_hide').addClass('cg_active');
            if (isBackendActionContainer) {
                $cgBackendGalleryDynamicMessage.addClass('cg_backend_action_container');
            } else {
                $cgBackendGalleryDynamicMessage.removeClass('cg_backend_action_container');
            }
            $cgBackendGalleryDynamicMessage.removeClass('cg_hide_slow cg_no_action_message cg_hide').find('.cg_notification_message_dynamic_content').html(message);
            if (classToAdd) {
                $cgBackendGalleryDynamicMessage.addClass(classToAdd);
            }
        }
        if (isNoActionMessage) {
            //   $cgBackendBackgroundDrop.removeClass('cg_hide_slow_1_sec cg_hide');
            if (isBackendActionContainer) {
                $cgBackendGalleryDynamicMessage.addClass('cg_backend_action_container');
            } else {
                $cgBackendGalleryDynamicMessage.removeClass('cg_backend_action_container');
            }
            $cgBackendGalleryDynamicMessage.addClass('cg_no_action_message').removeClass('cg_hide_slow cg_hide').find('.cg_notification_message_dynamic_content').html(message);
            setTimeout(function () {
                //$cgBackendBackgroundDrop.addClass('cg_hide_slow_1_sec');
                $cgBackendGalleryDynamicMessage.addClass('cg_hide_slow');
            }, 1000);
        }

        if (isShowLoader) {
            $cgBackendGalleryDynamicMessage.find('.cg-lds-dual-ring-div-gallery-hide').removeClass('cg_hide');
        }

    },
    setMultipleFileForPostFromBackendInfoContainer: function ($cg_backend_info_container, isRealIdSource, isEcommerceEntryAndDownload) {
        var data = {
            WpUpload: $cg_backend_info_container.attr('data-cg-wp-upload'),
            post_title: $cg_backend_info_container.attr('data-cg-post_title'),
            post_name: $cg_backend_info_container.attr('data-cg-post_name'),
            post_content: $cg_backend_info_container.attr('data-cg-post_content'),
            post_excerpt: $cg_backend_info_container.attr('data-cg-post_excerpt'),
            post_mime_type: $cg_backend_info_container.attr('data-cg-post_mime_type'),
            medium: $cg_backend_info_container.attr('data-cg-url-image-medium'),
            large: $cg_backend_info_container.attr('data-cg-url-image-large'),
            full: $cg_backend_info_container.attr('data-cg-original-source'),
            guid: $cg_backend_info_container.attr('data-cg-original-source'),
            type: $cg_backend_info_container.attr('data-cg-type'),// WordPress type
            NamePic: $cg_backend_info_container.attr('data-cg-post_name'),
            ImgType: $cg_backend_info_container.attr('data-cg-type-short'),
            Width: $cg_backend_info_container.attr('data-cg-file-width'),
            Height: $cg_backend_info_container.attr('data-cg-file-height'),
            rThumb: $cg_backend_info_container.find('.cg_rThumb').val(),
            Exif: $cg_backend_info_container.attr('data-cg-exif'),
            PdfPreview: $cg_backend_info_container.attr('data-cg-pdf-preview'),
            PdfOriginal: $cg_backend_info_container.attr('data-cg-pdf-original'),
            PdfPreviewImage: $cg_backend_info_container.attr('data-cg-pdf-preview-image'),
            PdfPreviewImageLarge: $cg_backend_info_container.attr('data-cg-pdf-preview-image-large'),
            ExifParsed: (this.Exif) ? (JSON.parse(this.Exif)) : '',
            IsExifDataChecked: true,
            isEcommerceEntryAndDownload: isEcommerceEntryAndDownload
        }
        if (isRealIdSource) {
            data.isRealIdSource = true;
        }
        return data;
    },
    setMultipleFilesForPostBeforeClone: function ($element, $cg_backend_info_container) {
        if ($element) {
            $cg_backend_info_container = $element.closest('.cg_backend_info_container');
        }
        var realId = $cg_backend_info_container.attr('data-cg-real-id');
        var cg_multiple_files_for_post = $cg_backend_info_container.find('.cg_multiple_files_for_post').val();
        var hasEmbedPostContent = false;
        var hasEmbedPostTitle = false;

        if (cg_multiple_files_for_post) {
            //cg_multiple_files_for_post = cg_multiple_files_for_post.replace(/&quot;/ig,'"');
            cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId] = JSON.parse(cg_multiple_files_for_post);
            var clone = cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone;
            for (var order in clone[realId]) {
                if (!clone[realId].hasOwnProperty(order)) {
                    break;
                }
                var ImgType = clone[realId][order]['ImgType'];
                var WpUpload = clone[realId][order]['WpUpload'];
                if (cgJsClassAdmin.gallery.functions.isEmbed(ImgType)) {
                    if (cg_embed_post_contents[realId] && cg_embed_post_contents[realId][WpUpload]) {
                        cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId][order]['post_content'] = cg_embed_post_contents[realId][WpUpload];// correct json_encode will be set here, otherwise not possible to display
                        hasEmbedPostContent = true;
                    }
                    if (cg_embed_post_titles[realId] && cg_embed_post_titles[realId][WpUpload]) {
                        cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId][order]['post_title'] = cg_embed_post_titles[realId][WpUpload];// correct json_encode will be set here, otherwise not possible to display
                        hasEmbedPostTitle = true;
                    }
                }
            }
        } else {
            cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId] = null;
        }
        if (hasEmbedPostContent || hasEmbedPostTitle) {// has to be done in that case because will be unset in gallery.php so JSON.parse(cg_multiple_files_for_post); working
            $cg_backend_info_container.find('.cg_multiple_files_for_post').val(JSON.stringify(cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId]));
        }
        cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone = $cg_backend_info_container.closest('.cgSortableDiv').clone();
    },
    setSimpleDataRealIdSource: function (realId, $cg_backend_info_container, WpUploadToReplace, NewWpUploadWhichReplace) {

        if (WpUploadToReplace) {
            var orderToReplace = 0;
            var orderToReplaceWith = 0;
            for (var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
                if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)) {
                    break;
                }
                if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'] == WpUploadToReplace) {
                    orderToReplace = order;
                }
                if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order]['WpUpload'] == NewWpUploadWhichReplace) {
                    orderToReplaceWith = order;
                }
            }
            debugger
            if (orderToReplace && orderToReplaceWith) {
                debugger
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderToReplace] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderToReplaceWith];
                delete cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][orderToReplaceWith];
            }
        }

        var WpUploadFilesForSaleArrayLoaded = cgJsClassAdmin.gallery.functions.getWpUploadFilesForSaleArrayLoaded;

        var dataWithSimpleRealIdSource = {};
        for (var order in cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
            if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId].hasOwnProperty(order)) {
                break;
            }
            dataWithSimpleRealIdSource[order] = {};
            if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].isRealIdSource) {
                var data = {
                    isRealIdSource: true,
                    WpUpload: cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].WpUpload
                };
                dataWithSimpleRealIdSource[order] = data;
            } else {
                dataWithSimpleRealIdSource[order] = cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order];
                if (dataWithSimpleRealIdSource[order].ExifParsed) {
                    delete dataWithSimpleRealIdSource[order].ExifParsed;
                }
            }

            // newAddedEcommerceImageForDownload is not required to be saved
            if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].newAddedEcommerceImageForDownload) {
                delete cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][order].newAddedEcommerceImageForDownload;
            }

        }

        console.log('dataWithSimpleRealIdSource');
        console.log(dataWithSimpleRealIdSource);

        // correct order for sure
        var i = 1;
        var dataWithSimpleRealIdSourceNew = {};
        for (var order in dataWithSimpleRealIdSource) {
            if (!dataWithSimpleRealIdSource.hasOwnProperty(order)) {
                break;
            }
            dataWithSimpleRealIdSourceNew[i] = dataWithSimpleRealIdSource[order];
            i++;
        }

        console.log('dataWithSimpleRealIdSourceNew');
        console.log(dataWithSimpleRealIdSourceNew);

        var dataJSONstring = JSON.stringify(dataWithSimpleRealIdSourceNew);

        $cg_backend_info_container.find('.cg_multiple_files_for_post').val(dataJSONstring).removeClass('cg_disabled_send');
        $cg_backend_info_container.find('.cg_backend_save_changes').removeClass('cg_hide');
    },
    showCgBackendBackgroundDrop: function (isAllowScroll) {
        // console.trace();
        jQuery('#cgBackendBackgroundDrop').removeClass('cg_high_overlay cg_hide').addClass('cg_active');
        if (!isAllowScroll) {
            jQuery('body').addClass('cg_no_scroll');
        }
    },
    showModal: function (id) {
        jQuery(id).removeClass('cg_hide').addClass('cg_active');
        jQuery('#cgBackendBackgroundDrop').removeClass('cg_hide').addClass('cg_active');
    },
    showMoveToGalleryContent: function ($, $element) {
        var $cgMoveToAnotherGalleryCompare = $('#cgMoveToAnotherGalleryCompare');
        var $cgMoveToAnotherGalleryContainer = $('#cgMoveToAnotherGalleryContainer');
        var $cgMoveToAnotherGalleryContent = $('#cgMoveToAnotherGalleryContent');
        var $cgMoveToAnotherGalleryLoader = $('#cgMoveToAnotherGalleryLoader');
        var $cg_in_gallery_id_to_move_select = $('#cg_in_gallery_id_to_move_select');
        var $cg_in_gallery_id_to_move_go_checkbox = $('#cg_in_gallery_id_to_move_go_checkbox_container');
        var $cgMoveToAnotherGallerySubmit = $('#cgMoveToAnotherGallerySubmit');
        var $cgBackendBackgroundDrop = $('#cgBackendBackgroundDrop');
        var gid = $('#cg_gallery_id').val();
        var realId = $element.closest('.cg_backend_info_container').attr('data-cg-real-id');
        $cgMoveToAnotherGalleryLoader.addClass('cg_hide');
        $cgMoveToAnotherGalleryContent.removeClass('cg_hide');
        $cgMoveToAnotherGalleryCompare.empty();
        $('#cgMoveEntryId').text(realId);
        $('#cgMoveRealId').val(realId);
        $cgBackendBackgroundDrop.removeClass('cg_hide').addClass('cg_active');
        // add gallery ids to UploadGallery select
        $cg_in_gallery_id_to_move_select.empty();
        $cg_in_gallery_id_to_move_select.prop('disabled', true);
        $cg_in_gallery_id_to_move_select.append('<option>No other galleries available</option>');
        if (cgJsClassAdmin.gallery.vars.galleryIDs.length > 1) {
            $cg_in_gallery_id_to_move_select.removeClass('cg_disabled_background_color_e0e0e0');
            $cg_in_gallery_id_to_move_go_checkbox.removeClass('cg_disabled_background_color_e0e0e0');
            $cgMoveToAnotherGallerySubmit.removeClass('cg_disabled_background_color_e0e0e0');

            $cg_in_gallery_id_to_move_select.empty();
            $cg_in_gallery_id_to_move_select.prop('disabled', false);

            var selectedGid = 0;
            for (var index in cgJsClassAdmin.gallery.vars.galleryIDs) {
                if (!cgJsClassAdmin.gallery.vars.galleryIDs.hasOwnProperty(index)) {
                    break;
                }
                var id = cgJsClassAdmin.gallery.vars.galleryIDs[index].id;
                if (gid != id) {
                    if (!selectedGid) {
                        selectedGid = id
                    }
                    var selected = '';
                    if (cgJsClassAdmin.gallery.vars.moveSelected == id) {
                        selected = 'selected';
                        selectedGid = cgJsClassAdmin.gallery.vars.moveSelected;
                    }
                    $cg_in_gallery_id_to_move_select.append('<option value="' + id + '" ' + selected + ' >Gallery ID ' + id + '</option>');
                }
            }

            cgJsClassAdmin.gallery.functions.createMoveSelectAssignArea($, $cgMoveToAnotherGalleryCompare, gid, selectedGid);

        } else {

            $('#cgMoveAssignFields').addClass('cg_hide');
            $('#cgMoveToAnotherGalleryCompare').addClass('cg_hide');
            $('#cg_in_gallery_id_to_move_go_checkbox_container').addClass('cg_hide');
            $('#cgMoveToAnotherGallerySubmitContainer').addClass('cg_hide');

        }
    },
    sortableInit: function ($) {

        return;

        //Sortieren der Galerie

        var $i = 0;

        var rowid = [];

        if ($i == 0) {

            $(".cgSortableDiv").each(function (i) {

                var rowidValue = $(this).find('.rowId').val();


                rowid.push(rowidValue);

            });

            $i++;

        }
        $(function () {
            $("#cgSortable").sortable({
                cursor: "move", handle: '.cg_drag_area', placeholder: "ui-state-highlight",
                stop: function (event, ui) {

                    if (document.readyState === "complete") {

                        var v = 0;

                        $(".cgSortableDiv").each(function (i) {


                            $(this).find('.rowId').val(rowid[v]).addClass('cg_value_changed').prop('disabled', false);
                            v++;

                        });

                        v = 0;

                    }

                },
                start: function (event, ui) {

                    var $element = $(ui.item);

                    $element.closest('#cgSortable').find('.ui-state-highlight').addClass($element.get(0).classList.value).html($element.html());

                }
            });
        });

    },
    getAiPrompts: function ($,cg_start,isShowMore) {
        debugger
        if(!cgJsClassAdmin.gallery.vars.openAiPromptsChecked || isShowMore){
            cgJsClassAdmin.gallery.vars.openAiPromptsChecked = true;
            var $cgOpenAiShowMoreLoader = $('#cgOpenAiContainer.cg_media_container.cg_cloned #cgOpenAiShowMoreLoader');
            var $cgOpenAiMorePrompts = $('#cgOpenAiContainer.cg_media_container.cg_cloned #cgOpenAiMorePrompts');

            if(isShowMore){
                //$cgOpenAiShowMoreLoader.removeClass('cg_hide').get(0).scrollIntoView();
                $cgOpenAiShowMoreLoader.removeClass('cg_hide');
                $cgOpenAiMorePrompts.addClass('cg_hide');
            }

            var data = {};
            data['action'] = 'post_cg_get_openai_prompts';
            if(!cg_start || cg_start < 0){
                cg_start = 0;
            }
            data['cg_start'] = cg_start;

            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: data
            }).done(function (response) {

                var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
                $response.find('script[data-cg-processing="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });

                // without cg_cloned in this moment, because might be not cloned, and doesn't matter if...
                var $cgOpenAiMorePrompts = $('#cgOpenAiContainer.cg_media_container #cgOpenAiMorePrompts');

                if(isShowMore){
                    $cgOpenAiShowMoreLoader.addClass('cg_hide');
                    cgJsClassAdmin.gallery.vars.openAiMorePrompts.forEach(function(item){
                        cgJsClassAdmin.gallery.functions.appendAiPrompt($cgOpenAiMorePrompts.closest('#cgOpenAiContainer'),item,isShowMore);
                    });
                }

                if (cgJsClassAdmin.gallery.vars.openAiMorePromptsCount) {
                    $cgOpenAiMorePrompts.removeClass('cg_hide');
                }else{
                    $cgOpenAiMorePrompts.addClass('cg_hide');
                }

            }).fail(function (xhr, status, error) {
                debugger
                console.log('response error get openai prompts');
                console.log(xhr);
                console.log('status error get openai prompts');
                console.log(status);
                console.log('error error get openai prompts');
                console.log(error);

                return;

            }).always(function () {

                var test = 1;

            });


        }

    },
    appendAiPrompt: function ($cgOpenAiContainer,item,isShowMore) {
        item.Prompt = jQuery('<textarea />').html(item.Prompt).text();
        var content = '<div class="cg_openai_prompt" data-cg-Tstamp="'+item.Tstamp+'"  title="Select prompt"><div class="cg_openai_prompt_date">'+item.date+' (WP Timezone: UTC'+item.gmt_offset+')</div><div class="cg_openai_prompt_text">'+item.Prompt+'</div></div>';
        debugger
        if(!$cgOpenAiContainer.find('.cg_openai_prompt[data-cg-Tstamp="'+item.Tstamp+'"]').length){
            if(isShowMore){
                jQuery(content).insertBefore($cgOpenAiContainer.find('#cgOpenAiShowMoreLoader'));
            }else{
                $cgOpenAiContainer.find('#cgOpenAiPromptsEntered').prepend(content);
            }
        }
    }
};