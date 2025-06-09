jQuery(document).ready(function ($) {

    var $cg_backend_info_container;
    cgJsClassAdmin.gallery.vars.openedFileFrame = null;
    var cg_media_uploader_set_to_post_id = 0; // Set this

    jQuery(document).on('click', '.cg_media_note_manage_additional_files', function (event) {
        $(this).closest('.media-modal').find('.media-modal-close').click();
        cgJsClassAdmin.gallery.functions.manageMultipleFilesForPost($, $cg_backend_info_container.find('.cg_manage_multiple_files_for_post'), false);
    });

    jQuery(document).on('click', '.media-modal.cg_add_additional_files .media-modal-close, .media-modal.cg_add_additional_files + .media-modal-backdrop', function (event) {
        event.preventDefault();
        debugger
        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
        $cgMultipleFilesForPostContainer.removeClass('cg_hide');
    });

    jQuery(document).on('click', '.media-modal.cg_add_files_to_gallery + .media-modal-backdrop,.media-modal.cg_add_files_to_gallery .media-modal-close', function (event) {
        debugger
        if(cgJsClassAdmin.gallery.vars.cgNewAiImageToGalleryAdded){
            cgJsClassAdmin.gallery.functions.reloadAfterAddingEntries($,'',1);
        }
    });

    // Media uploader multiple images to a post
    jQuery(document).on('click', '#cgSortable .cg_add_multiple_files_to_post_text,#cgSortable .cg_add_multiple_files_to_post, #cgSortable .cg_add_multiple_files_to_post_prev, #cgMultipleFilesForPostContainer .cg_backend_image_add_files_label, #cgMultipleFilesForPostContainer .cg_replace', function (event) {
        event.preventDefault();
        cgJsClassAdmin.gallery.functions.removeUsedFrames();

        //    $(document).on('click','#cgMultipleFilesForPostContainer .cg_backend_image_add_files_label',function () {
       //     cgJsClassAdmin.gallery.vars.$cg_backend_info_container.find('.cg_add_multiple_files_to_post').click();
        //});

        var isReplace = false;
        var isMultiple = true;
        var WpUploadToReplace = 0;
        var NewWpUploadWhichReplace = 0;
        debugger
        if($(this).hasClass('cg_replace') || $(this).hasClass('cg_backend_image_add_files_label')){
            $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container');
            if($(this).hasClass('cg_replace') ){
                isReplace = true;
                isMultiple = false;
                WpUploadToReplace = $(this).closest('.cg_backend_image_full_size_target_container').attr('data-cg-wp-upload-id');
            }
            var file_frame;
            var realId = $(this).closest('#cgMultipleFilesForPostContainer').attr('data-cg-real-id');
        }else{
            var file_frame;
            $cg_backend_info_container = $(this).closest('.cg_backend_info_container');
            var realId = $cg_backend_info_container.attr('data-cg-real-id');
            // if cg_add_multiple_files_to_post then already must be set through opening dynamic message
            if ($(this).closest('.cg_backend_info_container').length) {
                cgJsClassAdmin.gallery.vars.$sortableDiv = $(this).closest('.cgSortableDiv');
            }
        }

        cgJsClassAdmin.gallery.functions.hideMultipleFilesContainerForPost($);

        // If the media frame already exists, reopen it.
        if (file_frame) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param('post_id', cg_media_uploader_set_to_post_id);
            // Open frame
            file_frame.open();
            try {// do it here for sure, file_frame.open has to be done before
                //      wp.media.frame.content.get('gallery').collection.props.set({ignore: (+ new Date())});// <<< old code, reload, because images might be deleted (or added) from storage
                //https://wordpress.stackexchange.com/questions/325886/update-media-library-attachments
                // this forces a refresh of the content
                wp.media.frame.content.get().collection._requery(true);
                // optional: reset selection
                wp.media.frame.content.get().options.selection.reset();
            } catch (e) {
                console.log(e);
            }
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = cg_media_uploader_set_to_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: isMultiple  // Set to true to allow multiple files to be selected
        });

        debugger

        if (!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]) {
            var cg_multiple_files_for_post = $cg_backend_info_container.find('.cg_multiple_files_for_post').val();
            if (cg_multiple_files_for_post) {
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = JSON.parse(cg_multiple_files_for_post);
            }
        }

        var requiredData = cgJsClassAdmin.gallery.functions.getDataForAdditionalFiles(realId,$cg_backend_info_container);
        var data = requiredData.data;
        var order = requiredData.order;

        file_frame.on('open', function () { // alert(2);
            debugger
            file_frame.$el.addClass('cg_backend_area cg_add_additional_files');
            file_frame.$el.closest('.media-modal').addClass('cg_add_additional_files');

            cgJsClassAdmin.gallery.functions.addSocialTabs(file_frame,isReplace,WpUploadToReplace);

            if(!isReplace){
                file_frame.$el.find('#media-frame-title').html(
                    '<div class="cg_media_note"><div class="cg_media_note_explanation">Add additional files and click through them in frontend like in Instagram</div></div>'
                );
                if (location.search.indexOf('page=contest-gallery-pro') > -1) {
                    if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length > 1)) {// >1 because file might be added and then origin deleted
                        file_frame.$el.find('#media-frame-title .cg_media_note').append(
                            '<div class="cg_media_note_actions"><div class="cg_media_note_pro_hint">Max additional files: <span>9</span></div>' +
                            '<div class="cg_media_note_manage_additional_files">Manage already added additional files (' + (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length - 1) + ')</div>' +
                            '</div>'
                        );
                    } else {
                        file_frame.$el.find('#media-frame-title .cg_media_note').append(
                            '<div class="cg_media_note_actions"><div class="cg_media_note_pro_hint">Max additional files: <span>9</span></div>' +
                            '</div>'
                        );
                    }
                } else {
                    if (cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] && (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length > 1)) {// >1 because file might be added and then origin deleted
                        file_frame.$el.find('#media-frame-title .cg_media_note').append(
                            '<div class="cg_media_note_actions"><div class="cg_media_note_pro_hint">Max additional files: <span>2</span> <span class="cg-pro-false">+9</span></div>' +
                            '<div class="cg_media_note_manage_additional_files">Manage already added additional files (' + (Object.keys(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]).length - 1) + ')</div>' +
                            '</div>'
                        );
                    } else {
                        file_frame.$el.find('#media-frame-title .cg_media_note').append(
                            '<div class="cg_media_note_actions"><div class="cg_media_note_pro_hint">Max additional files: <span>2</span> <span class="cg-pro-false">+9</span></div>' +
                            '</div>'
                        );
                    }
                }
            }
            debugger
            console.log(file_frame);
            console.log(this);
            console.log('opened');
        });
        debugger
        var isDynamicMessageVisible = false;
        cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = true;
        var newWpUpload = 0;
        var newImgType = '';

        // When an image is selected, run a callback.
        file_frame.on('select', function () {
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').toJSON();
            attachment = cgJsClassAdmin.gallery.functions.orderDescendAttachments(attachment);
            cgJsClassAdmin.gallery.functions.selectAddAdditionalFiles(attachment,file_frame,data,$cg_backend_info_container,isDynamicMessageVisible,realId,isReplace,newWpUpload,newImgType,NewWpUploadWhichReplace,order,WpUploadToReplace,jQuery);
        });

        // Finally, open the modal
        file_frame.open();
        try {// do it here for sure, file_frame.open has to be done before
            //          wp.media.frame.content.get('gallery').collection.props.set({ignore: (+ new Date())});// <<< old code, reload, because images might be deleted (or added) from storage
            //https://wordpress.stackexchange.com/questions/325886/update-media-library-attachments
            // this forces a refresh of the content
            wp.media.frame.content.get().collection._requery(true);
            // optional: reset selection
            wp.media.frame.content.get().options.selection.reset();
        } catch (e) {
            console.log(e);
        }

    });

    // Media uploader general

    // Uploading files
    //var file_frame;
    //var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id

    jQuery(document).on('click', '.cg_upload_wp_images_button', function (event) {
        cgJsClassAdmin.gallery.functions.removeUsedFrames();

        var file_frame;
        event.preventDefault();

        var gid = $(this).attr('data-cg-gid');

        // If the media frame already exists, reopen it.
        if (file_frame) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param('post_id', cg_media_uploader_set_to_post_id);
            // Open frame
            file_frame.open();
            try {// do it here for sure, file_frame.open has to be done before
                //      wp.media.frame.content.get('gallery').collection.props.set({ignore: (+ new Date())});// <<< old code, reload, because images might be deleted (or added) from storage
                //https://wordpress.stackexchange.com/questions/325886/update-media-library-attachments
                // this forces a refresh of the content
                wp.media.frame.content.get().collection._requery(true);
                // optional: reset selection
                wp.media.frame.content.get().options.selection.reset();
            } catch (e) {
                console.log(e);
            }
            return;
        } else {
            // Set the wp.media post id so the uploader grabs the ID we want when initialised
            wp.media.model.settings.post.id = cg_media_uploader_set_to_post_id;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            /*'library': {
                cg_post: '123'// example if require to set own parameters, is $_REQUEST['query']['cg_post'] then
            },*/
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });

        cgJsClassAdmin.gallery.vars.openedFileFrame = file_frame;

        file_frame.on('open', function () { // alert(2);

            file_frame.$el.closest('.media-modal').addClass('cg_add_files_to_gallery');
            cgJsClassAdmin.gallery.vars.cgNewAiImageToGalleryAdded = 0; // reset here
            file_frame.$el.addClass('cg_backend_area');
            //file_frame.$el.addClass('cg_backend_area cg_add_additional_files');

            cgJsClassAdmin.gallery.functions.addSocialTabs(file_frame);

            var cgAssignedFields = JSON.parse(localStorage.getItem('cgAssignedFields' + gid));

            var hasFieldsToAdd = false;
            for (var index in cgJsClassAdmin.gallery.vars.upload_form_inputs) {
                if (!cgJsClassAdmin.gallery.vars.upload_form_inputs.hasOwnProperty(index)) {
                    break;
                }
                if (
                    cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'selectc-f' ||
                    cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'text-f' ||
                    cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'comment-f'
                ) {
                    hasFieldsToAdd = true;
                }
            }
            if (hasFieldsToAdd) {
                file_frame.$el.find('#media-frame-title').html(
                    '<div class="cg_media_assigned_fields_container_main">' +
                    '<div class="cg_media_assigned_field_container_label">' +
                    '<span class="cg_media_assigned_field_container_label_main" data-cg-gid="' + gid + '">' +
                    'Assign fields' +
                    '</span>' +
                    '<span class="cg_media_assigned_field_container_label_sub">' +
                    'Assign fields content<br>to added files' +
                    '</span>' +
                    '</div>' +
                    '<div class="cg_media_assigned_fields_container" data-cg-gid="' + gid + '">' +
                    '</div>' +
                    '</div>'
                );
                for (var index in cgJsClassAdmin.gallery.vars.upload_form_inputs) {
                    if (!cgJsClassAdmin.gallery.vars.upload_form_inputs.hasOwnProperty(index)) {
                        break;
                    }
                    var id = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['id'];
                    var title = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Content']['titel'];
                    if (
                        cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'selectc-f' ||
                        cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'text-f' ||
                        cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'comment-f'
                    ) {
                        var cg_media_assigned_field_element_category = '';
                        var input_id = '';
                        var text = '';
                        if (cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'selectc-f') {
                            cg_media_assigned_field_element_category = 'cg_media_assigned_field_element_category';
                            if (cgAssignedFields && cgAssignedFields.category && cgJsClassAdmin.gallery.vars.categories && cgJsClassAdmin.gallery.vars.categories[cgAssignedFields.category]) {
                                text = cgJsClassAdmin.gallery.vars.categories[cgAssignedFields.category];
                            }
                        } else {
                            if (cgAssignedFields && cgAssignedFields.input && cgAssignedFields.input[id]) {
                                if (cgAssignedFields.input[id] == 'alt') {
                                    text = '(WP) Alternative text';
                                }
                                if (cgAssignedFields.input[id] == 'title') {
                                    text = '(WP) Title';
                                }
                                if (cgAssignedFields.input[id] == 'caption') {
                                    text = '(WP) Caption';
                                }
                                if (cgAssignedFields.input[id] == 'description') {
                                    text = '(WP) Description';
                                }
                            }
                            input_id = id;
                        }

                        var $content = $('<div class="cg_media_assigned_field">' +
                            '<span class="cg_media_assigned_field_title">' + title + '</span>' +
                            ' : ' +
                            '<span class="cg_media_assigned_field_element ' + cg_media_assigned_field_element_category + '" id="inputId' + input_id + '">' + text + '</span>' +
                            '</div>');
                        //if(cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type']=='selectc-f'){
                        //file_frame.$el.find('#media-frame-title').find('.cg_media_assigned_fields_container').prepend($content);
                        //}else{
                        file_frame.$el.find('#media-frame-title').find('.cg_media_assigned_fields_container').append($content);
                        //}
                    }
                }
            }

        });

        // When an image is selected, run a callback.
        file_frame.on('select', function () { // alert(2);
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').toJSON();
            attachment = cgJsClassAdmin.gallery.functions.orderDescendAttachments(attachment,true);
            cgMediaUploadGallery(attachment, file_frame);
            //$("#cg_wp_upload_ids").append("<input class='cg_wp_upload_id' value="+attachment.id+" />");
        });

        // Finally, open the modal
        file_frame.open();
        try {// do it here for sure, file_frame.open has to be done before
            //          wp.media.frame.content.get('gallery').collection.props.set({ignore: (+ new Date())});// <<< old code, reload, because images might be deleted (or added) from storage
            //https://wordpress.stackexchange.com/questions/325886/update-media-library-attachments
            // this forces a refresh of the content
            wp.media.frame.content.get().collection._requery(true);
            // optional: reset selection
            wp.media.frame.content.get().options.selection.reset();
        } catch (e) {
            console.log(e);
        }

        // have to be done because of possible wordpress media library bug
        setTimeout(function () {
            $('.button.load-more').removeClass('hidden');
            $('.load-more-jump').removeClass('hidden');
        }, 3000);

    });

    jQuery(document).on('change', '.cg_media_assign_category_select select', function (event) {
        var gid = $(this).attr('data-cg-gid');

        var cgAssignedFields = {};
        var $cgMediaAssignFieldsContainer = $('#cgMediaAssignFieldsContainer');
        var text = '-';
        if ($(this).val()) {
            cgAssignedFields.category = $(this).val();
            text = $(this).find(':selected').text();
        }
        cgAssignedFields.input = {};
        $cgMediaAssignFieldsContainer.find('.cg_media_assign_fields select').each(function () {
            if ($(this).val()) {
                cgAssignedFields.input[$(this).attr('data-cg-input-id')] = $(this).val();
            }
        });
        localStorage.setItem('cgAssignedFields' + gid, JSON.stringify(cgAssignedFields));
        $('.cg_media_assigned_field_element.cg_media_assigned_field_element_category').text(text);
    });

    jQuery(document).on('change', '.cg_media_assign_field_select select', function (event) {
        var gid = $(this).attr('data-cg-gid');
        var inputId = $(this).attr('data-cg-input-id');
        var cgAssignedFields = {};
        var $cgMediaAssignFieldsContainer = $('#cgMediaAssignFieldsContainer');
        var $catSelect = $cgMediaAssignFieldsContainer.find('.cg_media_assign_category_select select');
        if ($catSelect.length && $catSelect.val()) {
            cgAssignedFields.category = $catSelect.val();
        }
        var text = '-';
        if ($(this).val()) {
            text = $(this).find(':selected').text();
        }
        cgAssignedFields.input = {};
        $cgMediaAssignFieldsContainer.find('.cg_media_assign_fields select').each(function () {
            if ($(this).val()) {
                cgAssignedFields.input[$(this).attr('data-cg-input-id')] = $(this).val();
            }
        });

        localStorage.setItem('cgAssignedFields' + gid, JSON.stringify(cgAssignedFields));
        // last() has to be done because doubled in some cases, has to be researched
        $('.cg_media_assigned_field_element#inputId' + inputId).last().text(text);
    });

    jQuery(document).on('click', '#cgMediaAssignFieldsSubmitContainerButton', function (event) {
        $(this).closest('#cgBackendGalleryDynamicMessage').find('.cg_message_close').click();
    });

    jQuery(document).on('click', '.cg_media_assigned_field_container_label_main, .cg_media_assigned_fields_container', function (event) {

        var gid = $(this).attr('data-cg-gid');

        var cgAssignedFields = JSON.parse(localStorage.getItem('cgAssignedFields' + gid));

        var $cgMediaAssignFieldsContainer = $('<div id="cgMediaAssignFieldsContainer">' +
            '<div id="cgMediaAssignFieldsTitle">Added files will contain assigned content</div>' +
            '<div id="cgMediaAssignFieldsTitleSub">Added files can contain here preselected field content from the original post.<br><b>WordPress post fields</b> can be assigned to <b>Input</b> and <b>Textarea</b> field types.' +
            '<br>Input and Textarea field types can be added in <b>"Edit contact form"</b>.' +
            '<br>Files can be also assigned to a category.' +
            '<br><b>Categories</b> can be edit in "Edit contact form".</div>' +
            '<div class="cg_media_assign_category cg_hide">' +
            '<div class="cg_media_assign_category_title">' +
            'Assign to category' +
            '</div>' +
            '<div class="cg_media_assign_category_select">' +
            '<select  data-cg-gid="' + gid + '"><option value="">Please select</option></select>' +
            '</div>' +
            '</div>' +
            '<div class="cg_media_assign_fields_overview cg_hide">' +
            '<div class="cg_media_assign_fields"></div>' +
            '<div class="cg_media_assign_fields_image"><div class="cg_media_assign_fields_image_title">Example</div><img src="' + cgJsClassAdmin.gallery.vars.assign_fields_png + '" /></div>' +
            '</div>' +
            '<div  id="cgMediaAssignFieldsSubmitContainer" >' +
            '<div  id="cgMediaAssignFieldsSubmitContainerButton" class="cg_backend_button_gallery_action">Close and go select</div>' +
            '</div>' +
            '</div>');

        for (var index in cgJsClassAdmin.gallery.vars.upload_form_inputs) {

            if (!cgJsClassAdmin.gallery.vars.upload_form_inputs.hasOwnProperty(index)) {
                break;
            }

            var id = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['id'];
            var title = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Content']['titel'];
            var Field_Type = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'];

            if (cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'selectc-f') {
                $cgMediaAssignFieldsContainer.find('.cg_media_assign_category').removeClass('cg_hide');
                for (var categoryId in cgJsClassAdmin.gallery.vars.categories) {
                    if (!cgJsClassAdmin.gallery.vars.categories.hasOwnProperty(categoryId)) {
                        break;
                    }
                    var selected = '';
                    var Name = cgJsClassAdmin.gallery.vars.categories[categoryId];
                    if (cgAssignedFields && cgAssignedFields.category && cgAssignedFields.category == categoryId) {
                        selected = 'selected';
                    }
                    $cgMediaAssignFieldsContainer.find('.cg_media_assign_category select').append('<option value="' + categoryId + '" ' + selected + '>' + Name + '</option>');
                }
            }

            if (cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'text-f' || cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'] == 'comment-f') {
                $cgMediaAssignFieldsContainer.find('.cg_media_assign_fields_overview').removeClass('cg_hide');
                var selectedAlt = '';
                var selectedTitle = '';
                var selectedCaption = '';
                var selectedDescription = '';
                if (cgAssignedFields && cgAssignedFields.input && cgAssignedFields.input[id] && cgAssignedFields.input[id] == 'alt') {
                    var selectedAlt = 'selected';
                }
                if (cgAssignedFields && cgAssignedFields.input && cgAssignedFields.input[id] && cgAssignedFields.input[id] == 'title') {
                    var selectedTitle = 'selected';
                }
                if (cgAssignedFields && cgAssignedFields.input && cgAssignedFields.input[id] && cgAssignedFields.input[id] == 'caption') {
                    var selectedCaption = 'selected';
                }
                if (cgAssignedFields && cgAssignedFields.input && cgAssignedFields.input[id] && cgAssignedFields.input[id] == 'description') {
                    var selectedDescription = 'selected';
                }

                var $selectDiv = $('<div class="cg_media_assign_field"><div class="cg_media_assign_field_title">' + title + '</div><div class="cg_media_assign_field_select"><select data-cg-field-type="' + Field_Type + '" data-cg-input-id="' + id + '"  data-cg-gid="' + gid + '"  >' +
                    '<option value="">Please select</option>' +
                    '<option value="alt" ' + selectedAlt + '>(WP) Alternative text</option>' +
                    '<option value="title" ' + selectedTitle + '>(WP) Title</option>' +
                    '<option value="caption" ' + selectedCaption + '>(WP) Caption</option>' +
                    '<option value="description" ' + selectedDescription + '>(WP) Description</option>' +
                    '</select></div></div>');
                $cgMediaAssignFieldsContainer.find('.cg_media_assign_fields_overview').removeClass('cg_hide').find('.cg_media_assign_fields').removeClass('cg_hide').append($selectDiv);
            }

        }

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage($cgMediaAssignFieldsContainer, undefined, 'cgMediaAssignProcess cg_overflow_y_scroll');

    });

    // Restore the main ID when the add media button is pressed
    //  jQuery('a.add_media').on('click', function() {
    //wp.media.model.settings.post.id = wp_media_post_id;
    //	alert(wp_media_post_id);
    //  });

    /*	   $(document).on('click', '.media-modal-close', function(e){


var file_frame;

return false;	

});*/

    function cgMediaUploadGallery(attachment, file_frame) {

        //	console.log('upload');
        var cg_gallery_id = $("#cg_gallery_id").val();
        var gid = cg_gallery_id;
        var cg_admin_url = $("#cg_admin_url").val();

        $("#cg_uploading_count").css("display", "none");

        if (attachment.length == 0) {
            return false;
        }

        $("#cg_uploading_gif").css("display", "block");
        $("#cg_uploading_div").css("display", "block");
        $("#cgAddImagesWpUploader").css("display", "none");

        var i = 0;
        var attachmentIds = [];
        while (i < attachment.length) {
            attachmentIds.push(attachment[i].id);
            i++;
        }

        var cg_assign_category = 0;
        var cg_assign_fields = {};
        /*        cg_assign_fields['alt'] = {};
                cg_assign_fields['caption'] = {};
                cg_assign_fields['title'] = {};
                cg_assign_fields['description'] = {};*/

        var cgAssignedFields = JSON.parse(localStorage.getItem('cgAssignedFields' + gid));

        if (cgAssignedFields && cgAssignedFields.category) {
            cg_assign_category = cgAssignedFields.category;
        }

        if (cgAssignedFields && cgAssignedFields.input && Object.keys(cgAssignedFields.input).length) {
            for (var inputId in cgAssignedFields.input) {
                if (!cgAssignedFields.input.hasOwnProperty(inputId)) {
                    break;
                }
                for (var index in cgJsClassAdmin.gallery.vars.upload_form_inputs) {
                    if (!cgJsClassAdmin.gallery.vars.upload_form_inputs.hasOwnProperty(index)) {
                        break;
                    }
                    if (cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['id'] == inputId) {
                        cg_assign_fields[inputId] = {};
                        cg_assign_fields[inputId]['wp-type'] = cgAssignedFields.input[inputId];
                        cg_assign_fields[inputId]['Field_Type'] = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Type'];
                        cg_assign_fields[inputId]['Field_Order'] = cgJsClassAdmin.gallery.vars.upload_form_inputs[index]['Field_Order'];
                    }
                }
            }
        }

        /*        if(file_frame.$el.find('#cgAssignAltSelectDiv select').val()){
                    cg_assign_fields['alt']['id'] = file_frame.$el.find('#cgAssignAltSelectDiv select').val();
                    cg_assign_fields['alt']['Field_Type'] = file_frame.$el.find('#cgAssignAltSelectDiv select option:selected').attr('data-cg-field-type');
                }
                if(file_frame.$el.find('#cgAssignCaptionSelectDiv select').val()){
                    cg_assign_fields['caption']['id'] = file_frame.$el.find('#cgAssignCaptionSelectDiv select').val();
                    cg_assign_fields['caption']['Field_Type'] = file_frame.$el.find('#cgAssignCaptionSelectDiv select option:selected').attr('data-cg-field-type');
                }
                if(file_frame.$el.find('#cgAssignTitleSelectDiv select').val()){
                    cg_assign_fields['title']['id'] = file_frame.$el.find('#cgAssignTitleSelectDiv select').val();
                    cg_assign_fields['title']['Field_Type'] = file_frame.$el.find('#cgAssignTitleSelectDiv select option:selected').attr('data-cg-field-type');
                }
                if(file_frame.$el.find('#cgAssignDescriptionSelectDiv select').val()){
                    cg_assign_fields['description']['id'] = file_frame.$el.find('#cgAssignDescriptionSelectDiv select').val();
                    cg_assign_fields['description']['Field_Type'] = file_frame.$el.find('#cgAssignDescriptionSelectDiv select option:selected').attr('data-cg-field-type');
                }*/

        jQuery.ajax({
            url: cg_admin_url + "admin-ajax.php",
            type: 'post',
            data: {
                action: 'cg_check_wp_admin_upload_v10',
                action1: attachmentIds,
                action2: cg_gallery_id,
                cg_assign_category: cg_assign_category,
                cg_assign_fields: cg_assign_fields
            },
        }).done(function (response) {

            cgJsClassAdmin.gallery.functions.reloadAfterAddingEntries($,response);

        }).fail(function (xhr, status, error) {

            console.log(xhr);
            console.log(status);
            console.log(error);

        }).always(function () {

        });


    }


});