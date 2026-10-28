jQuery(document).ready(function ($) {

    var $mediaFrame;

    var $mediaFrameContentSub;

    $(document).on('click','.cg_media_menu_item',function (){
        $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.removeClass('cg_media_assign_fields_mode');
        $mediaFrame.find('.media-toolbar-primary.search-form').addClass('cg_hide');
        $mediaFrame.find('.media-menu-item.active').removeClass('active').addClass('cg_active');
        $mediaFrame.find('.cg_media_menu_item.cg_active').removeClass('cg_active').attr('aria-pressed', 'false');
    });

    $(document).on('click','#cgAssignFields',function (event){
        event.preventDefault();
        var $tab = $(this);
        var $mediaFrame = $tab.closest('.media-frame.cg_backend_area');
        var $mediaFrameContent = $mediaFrame.find('.media-frame-content');
        var gid = $tab.attr('data-cg-gid');
        var $panel;

        $mediaFrame.find('.cg_media_assign_fields_tab_panel.cg_cloned').remove();
        $mediaFrameContentSub = $mediaFrameContent.children();
        $mediaFrameContentSub.addClass('cg_hide');
        $mediaFrameContent.removeAttr('style');
        $panel = cgJsClassAdmin.gallery.functions.createMediaAssignFieldsPanel(gid);

        if (!$panel.length) {
            $mediaFrame.find('#menu-item-browse').trigger('click');
            return;
        }

        $tab.addClass('cg_active').attr('aria-pressed', 'true');
        $mediaFrame.addClass('cg_media_assign_fields_mode');
        $mediaFrameContent.append($panel);
        $panel.find('.cg_media_assign_fields_tab_title').trigger('focus');
    });

    $(document).on('click','#cgYoutubeLibrary,#cgYoutubeLibraryLoadMoreButton',function (e){
        debugger
        $mediaFrame = $('.media-frame.cg_backend_area');
        $(this).addClass('cg_active');
        $mediaFrame.find('#cgYoutubeLibraryNewAddedCount').addClass('cg_hide').text('0');
        var $mediaFrameContent =  $mediaFrame.find('.media-frame-content');
        $mediaFrameContentSub =  $mediaFrame.find('.media-frame-content > *');
        $mediaFrameContentSub.addClass('cg_hide');
        var $cgYoutubeLibraryContainer = $('#cgYoutubeLibraryContainer.cg_cloned');
        if($cgYoutubeLibraryContainer.length){
            $cgYoutubeLibraryContainer.removeClass('cg_hide');
            var $cgYoutubeLibraryFooter = $mediaFrame.find('#cgYoutubeLibraryFooter');
            $cgYoutubeLibraryFooter.removeClass('cg_hide');
            $cgYoutubeLibraryContainer.find('#cgYoutubeLibraryLoadMore').remove();
        }else{
            $cgYoutubeLibraryContainer = $('#cgYoutubeLibraryContainer').clone().removeClass('cg_hide').addClass('cg_cloned');
            var $cgYoutubeLibraryFooter = $('#cgYoutubeLibraryFooter').clone().removeClass('cg_hide');
            $mediaFrameContent.append($cgYoutubeLibraryContainer);
            $mediaFrameContent.append($cgYoutubeLibraryFooter);
        }

        var $cgYoutubeLibraryLoader = $mediaFrame.find('#cgYoutubeLibraryLoader');
        $cgYoutubeLibraryLoader.removeClass('cg_hide');
        if($(this).attr('id')=='cgYoutubeLibrary'){
            $cgYoutubeLibraryContainer.find('.cg_ytb_media_container').remove();
            cgJsClassAdmin.gallery.vars.youtubePostsShowMore = false;
        }
        $cgYoutubeLibraryContainer.append($cgYoutubeLibraryLoader);
        var $cgYoutubeNoEntries = $mediaFrame.find('#cgYoutubeNoEntries');
        $cgYoutubeLibraryContainer.closest('.media-frame-content').css('overflow','visible');// has to be because of cgYoutubeLibraryFooter with add to gallery button

        e.preventDefault();
        var data = {};
        data['action'] = 'post_cg_social_platforms_query';
        data['cg_nonce'] = CG1LBackendNonce.nonce;
        data['gid'] = $(this).closest('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_start'] = 0;

        if($(this).attr('id')=='cgYoutubeLibraryLoadMoreButton'){
            data['cg_start'] = $(this).attr('data-cg-start');
            $(this).remove();
        }

        var isReplace = false;
        if($(this).closest('.cg_is_replace').length){
            isReplace = true;
        }

        if($(this).closest('.cg_add_additional_files').length){
            $cgYoutubeLibraryFooter.find('#cgAddYoutubeLibraryButton').addClass('cg_add_additional_files').val(isReplace ? 'Replace with selected' : 'Add selected');
        }

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

            $cgYoutubeLibraryLoader.addClass('cg_hide');

            if(cgJsClassAdmin.gallery.vars.youtubePosts.length){
                cgJsClassAdmin.gallery.vars.youtubePosts.forEach(function (object){
                    var $frame = '';
                    var cg_ytb_only = '';
                    if(object.post_mime_type=='contest-gallery-instagram'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.guid;
                        cg_ytb_only = 'cg_inst_only';
                        $frame = $('<iframe  class="cg_ytb_media" width="270px" height="202px" src="' + object['guid'] + '" ></iframe>');
                    }
                    if(object.post_mime_type=='contest-gallery-youtube'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.guid;
                        cg_ytb_only = 'cg_ytb_only';
                        $frame = $('<iframe  class="cg_ytb_media" width="270px" height="165px" src="' + object['guid'] + '" ></iframe>');
                    }
                    if(object.post_mime_type=='contest-gallery-twitter'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.blockquote;
                        $frame = $(object.blockquote);
                    }
                    if(object.post_mime_type=='contest-gallery-tiktok'){
                        cgJsClassAdmin.gallery.vars.embedById[object['ID']] = object.blockquote;
                        cg_ytb_only = 'cg_tkt_only';
                        $frame = $(object.blockquote);
                    }
                    var $cg_ytb_media_container = $('<div class="cg_ytb_media_container '+cg_ytb_only+'" data-cg-wp-upload-id="' + object['ID'] + '">' +
                        '<div class="cg_ytb_media_delete_select_container"><div class="cg_ytb_media_delete"  data-cg-wp-upload-id="' + object['ID'] + '"><span>Delete</span></div><div class="cg_ytb_media_select"  data-cg-wp-upload-id="' + object['ID'] + '" data-post-mime-type="' + object['post_mime_type'] + '"><span>'+(isReplace ? 'Replace' : 'Add')+'</span></div></div>' +
                        '</div>');
                    $cg_ytb_media_container.append($frame);
                    $cgYoutubeLibraryContainer.append($cg_ytb_media_container);
                });
                if(cgJsClassAdmin.gallery.vars.youtubePostsShowMore){
                    $cgYoutubeLibraryContainer.find('#cgYoutubeLibraryLoadMore').remove();
                    $cgYoutubeLibraryContainer.append('<div id="cgYoutubeLibraryLoadMore"><div>Showing <b>'+cgJsClassAdmin.gallery.vars.youtubePostsStep+'</b> of <b>'+cgJsClassAdmin.gallery.vars.youtubePostsCount+'</b> social media items</div><div id="cgYoutubeLibraryLoadMoreButton" data-cg-start="'+cgJsClassAdmin.gallery.vars.youtubePostsStep+'">Load more</div></div>');
                }
            }else{
                $cgYoutubeNoEntries.removeClass('cg_hide');
            }

        }).fail(function (xhr, status, error) {
            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);

            return;

        }).always(function () {

            var test = 1;

        });
    });

    var urlType = '';

    $(document).on('click','.cg_ytb_media_select:not(.cg_add)',function (e){

        if($(this).closest('.cg_is_replace').length){
            $(this).closest('#cgYoutubeLibraryContainer').find('.cg_ytb_media_select.cg_add').removeClass('cg_add');
        }
        $(this).addClass('cg_add');
        $(this).closest('.cg_ytb_media_container').find('.cg_ytb_media_delete').removeClass('cg_delete');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }

    });

    $(document).on('click','.cg_ytb_media_select.cg_add',function (e){
        $(this).removeClass('cg_add');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','.cg_ytb_media_delete:not(.cg_delete)',function (e){
        $(this).addClass('cg_delete');
        $(this).closest('.cg_ytb_media_container').find('.cg_ytb_media_select').removeClass('cg_add');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_add').length){
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgAddYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','.cg_ytb_media_delete.cg_delete',function (e){
        $(this).removeClass('cg_delete');
        if($(this).closest('#cgYoutubeLibraryContainer').find('.cg_delete').length){
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').removeClass('cg_disabled_one');
        }else{
            $(this).closest('.media-frame-content').find('#cgDeleteYoutubeLibraryButton').addClass('cg_disabled_one');
        }
    });

    $(document).on('click','#cgAddYoutubeLibraryButton',function (e){
        e.preventDefault();
        debugger
        var $mediaFrame = $(this).closest('.media-frame');
        var gid = $('#cg_gallery_id').val();
        $(this).addClass('cg_disabled_one');

        var formPostData = new FormData();
        formPostData.append('action', 'post_cg_social_platforms_add_to_gallery');
        formPostData.append('action2', parseInt($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid')));

        var data = {};
        data['action'] = 'post_cg_social_platforms_add_to_gallery';
        data['cg_nonce'] = CG1LBackendNonce.nonce;
        data['action2'] = $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_wp_post_ids'] = [];
        var cg_assign_category = 0;

        var cgAssignedFields = JSON.parse(localStorage.getItem('cgAssignedFields' + gid));
        if (cgAssignedFields && cgAssignedFields.category) {
            cg_assign_category = cgAssignedFields.category;
        }
        data['cg_assign_category'] = cg_assign_category;
        // reverse to get proper order same like in CG YouTube Library
        $($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').find('.cg_ytb_media_select.cg_add').get().reverse()).each(function (){
            formPostData.append('cg_wp_post_ids[]', parseInt($(this).attr('data-cg-wp-upload-id')));
            data['cg_wp_post_ids'].push(parseInt($(this).attr('data-cg-wp-upload-id')));
        });
        $mediaFrame.find('.cg_ytb_media_container').remove();
        $mediaFrame.find('#cgYoutubeLibraryLoader').removeClass('cg_hide');
        jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');

        if($mediaFrame.closest('.media-modal').hasClass('cg_add_additional_files')){
            var dataCopy = data;
            var file_frame = cgJsClassAdmin.gallery.vars.file_frame;
            var isReplace = !!file_frame.$el.hasClass('cg_is_replace');
            var WpUploadToReplace = file_frame.$el.attr('data-cg-wp-upload-to-replace') ? file_frame.$el.attr('data-cg-wp-upload-to-replace') : 0;
            var NewWpUploadWhichReplace = 0;
            var isDynamicMessageVisible = false;
            var newWpUpload = 0;
            var newImgType = '';
            var $cg_backend_info_container = cgJsClassAdmin.gallery.vars.$sortableDiv.find('.cg_backend_info_container');
            var realId = $cg_backend_info_container.attr('data-cg-real-id');
            var requiredData = cgJsClassAdmin.gallery.functions.getDataForAdditionalFiles(realId,$cg_backend_info_container);
            var data = requiredData.data;
            var order = requiredData.order;
            var attachment = [];
            var youtubePosts = cgJsClassAdmin.gallery.vars.youtubePosts;
            for (var index in youtubePosts) {
                if (!youtubePosts.hasOwnProperty(index)) {
                    break;
                }
                dataCopy['cg_wp_post_ids'].forEach(function (value){
                    youtubePosts[index]['date'] = youtubePosts[index]['post_date'];// has to be corrected here
                    youtubePosts[index]['id'] = youtubePosts[index]['ID'];// has to be corrected here
                    if(youtubePosts[index]['id']==value){
                        attachment.push(youtubePosts[index]);
                    }
                });
            }

            if(isReplace){
                //$mediaFrame.closest('.media-modal').addClass('cg_hide');
                cgJsClassAdmin.gallery.functions.removeUsedFrames();
                $('#cgMultipleFilesForPostContainer').addClass('cg_hide');
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden modal-open');
            }

            attachment = cgJsClassAdmin.gallery.functions.orderDescendAttachments(attachment);
            cgJsClassAdmin.gallery.functions.selectAddAdditionalFiles(attachment,file_frame,data,$cg_backend_info_container,isDynamicMessageVisible,realId,isReplace,newWpUpload,newImgType,NewWpUploadWhichReplace,order,WpUploadToReplace,jQuery);

            //  setTimeout(function (){
            if(!isReplace){
                $mediaFrame.closest('.media-modal').find('.media-modal-close').click();
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
            }
            //    },300);

        }else{

            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: data
            }).done(function (response) {

                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
                $mediaFrame.find('#cgYoutubeLibraryLoader').addClass('cg_hide');
                $('.media-modal-close').click();// have to be clicked that way is extra element

                cgJsClassAdmin.gallery.functions.reloadAfterAddingEntries($,response);

            }).fail(function (xhr, status, error) {
                jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');

                debugger
                console.log('response error');
                console.log(xhr);
                console.log('status error');
                console.log(status);
                console.log('error error');
                console.log(error);

                return;

            }).always(function () {

                var test = 1;

            });
        }

    });

    $(document).on('click','#cgDeleteYoutubeLibraryButton',function (e){
        e.preventDefault();
        debugger
        var $mediaFrame = $(this).closest('.media-frame');
        $(this).addClass('cg_disabled_one');
        var form = $(this).closest('.media-frame-content').find('#cgAddYoutubeToGalleryForm').get(0);
        var formPostData = new FormData();
        formPostData.append('action', 'post_cg_youtube_delete_from_library');
        formPostData.append('action2', parseInt($(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid')));
        var data = {};
        data['action'] = 'post_cg_youtube_delete_from_library';
        data['action2'] = $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').attr('data-cg-gid');
        data['cg_nonce'] = CG1LBackendNonce.nonce;
        data['cg_wp_post_ids'] = [];
        $mediaFrame.find('.cg_ytb_media_container').addClass('cg_hide');
        $mediaFrame.find('#cgYoutubeLibraryLoader').removeClass('cg_hide');
        $(this).closest('.media-frame-content').find('#cgYoutubeLibraryContainer').find('.cg_ytb_media_delete.cg_delete').each(function (){
            formPostData.append('cg_wp_post_ids[]', parseInt($(this).attr('data-cg-wp-upload-id')));
            data['cg_wp_post_ids'].push(parseInt($(this).attr('data-cg-wp-upload-id')));
            $(this).closest('.cg_ytb_media_container').remove();
        });
        jQuery('body').addClass('cg_pointer_events_none cg_overflow_y_hidden');
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: data
        }).done(function (response) {
            jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');
            $mediaFrame.find('#cgYoutubeLibraryLoader').addClass('cg_hide');
            if(!$mediaFrame.find('.cg_ytb_media_container').length){
                $mediaFrame.find('#cgYoutubeNoEntries').removeClass('cg_hide');
            }else{
                $mediaFrame.find('.cg_ytb_media_container').removeClass('cg_hide');
            }
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Successfully deleted from library',true);
        }).fail(function (xhr, status, error) {
            jQuery('body').removeClass('cg_pointer_events_none cg_overflow_y_hidden');

            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);

            return;

        }).always(function () {

            var test = 1;

        });
    });

    var isReclick = false;

    $(document).on('click','.media-frame.cg_backend_area #menu-item-browse.cg_active',function (){
        debugger
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        if($mediaFrame.find('.cg_media_menu_item.cg_active').length){
            $mediaFrame.find('.cg_media_menu_item.cg_active').removeClass('cg_active');
            if($mediaFrameContentSub){
                $mediaFrameContentSub.removeClass('cg_hide');
            }
            $(this).removeClass('cg_active').addClass('active');
        }
    });

    // has to be after 'click','.media-frame.cg_backend_area #menu-item-browse.cg_active'
    $(document).on('click','.media-frame.cg_backend_area .media-menu-item.cg_active',function (){
        $(this).removeClass('cg_active');
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.find('.media-frame-content').removeAttr('style');
    });

    $(document).on('click','.media-frame.cg_backend_area .media-menu-item',function (){
        var $mediaFrame = $(this).closest('.media-frame.cg_backend_area');
        $mediaFrame.removeClass('cg_media_assign_fields_mode');
        $mediaFrame.find('.media-toolbar-primary.search-form').removeClass('cg_hide');
        $mediaFrame.find('.cg_media_container').remove();
        $mediaFrame.find('.cg_media_menu_item').removeClass('cg_active').attr('aria-pressed', 'false');
    });

    cgJsClassAdmin.gallery.functions.loadAiCreateEvents($);
    cgJsClassAdmin.gallery.functions.loadAiEditEvents($);

    $(document).on('click','#menu-item-browse',function (){

        if($(this).closest('.media-frame').hasClass('cg_openai_edit_clicked')){
            $('.cg_upload_wp_images_button').removeClass('cg_openai_edit').click();
        }else{
            //$mediaFrame = $('.media-frame.cg_backend_area');
            $mediaFrame = $(this).closest('.media-frame');
            $mediaFrame.find('.media-frame-content').removeClass('cg_openai_edit');
            $mediaFrame.find('.media-frame-content .attachments-browser.has-load-more').removeClass('cg_openai_edit cg_hide');
            $(this).addClass('active');// is required when #cgEditViaOpenAI is clicked
            $mediaFrame.find('#cgOpenAiSelectToEdit').remove();
        }

    });

    $(document).on('click','#menu-item-upload',function (){

        if($(this).closest('.media-frame').hasClass('cg_openai_edit_clicked')){
            $('.cg_upload_wp_images_button').removeClass('cg_openai_edit').addClass('cg_openai_edit_upload_clicked').click();
        }else{
            $(this).addClass('active');
            $mediaFrame = $(this).closest('.media-frame');
            // might have class cg_hide
            $mediaFrame.find('.media-frame-content').removeClass('cg_openai_edit');
            $mediaFrame.find('.media-frame-content .uploader-inline').removeClass('cg_openai_edit cg_hide');
            $mediaFrame.find('#cgOpenAiSelectToEdit').addClass('cg_hide');// somehow might be still there when a lot of clicking
            $mediaFrame.find('#cgOpenAiSelectToEdit').remove();
        }});

});
