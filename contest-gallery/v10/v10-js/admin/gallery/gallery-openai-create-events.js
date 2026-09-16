cgJsClassAdmin.gallery.functions.loadAiCreateEvents = function($){

    cgJsClassAdmin.gallery.functions.openAiShowModels = function($mediaFrame, isEdit){
        var $content = $mediaFrame.find('.media-frame-content');
        if ($content.find('#cgOpenAiContainer.cg_cloned').data('request-pending')) { return; }
        $content.children().addClass('cg_hide');
        $content.toggleClass('cg_openai_edit', isEdit).css('overflow', 'visible');
        var $container = $content.find('#cgOpenAiContainer.cg_cloned');
        if (!$container.length) {
            $container = $('#cgOpenAiContainer:not(.cg_cloned)').first().clone().addClass('cg_cloned');
            $content.append($container);
        }
        $container.removeClass('cg_hide cg_openai_model_selected').toggleClass('cg_openai_edit_mode', isEdit).data('operation', isEdit ? 'edit' : 'create');
        $container.find('.cg_openai_main').addClass('cg_hide');
        $container.find('#cgOpenAiOverview').removeClass('cg_hide');
        $container.find('.cg_openai_edit_sources, .cg_openai_edit_desc').toggleClass('cg_hide', !isEdit);
        $container.find('.cg_openai_create_desc').toggleClass('cg_hide', isEdit);
        $container.find('#cgOpenAiGenLoaderContainer, #cgOpenAiGenError, .cg_openai_quality_changed').addClass('cg_hide');
        $container.find('#cgOpenAiPromptInput').val(cgJsClassAdmin.gallery.vars.openAiPrompt || '');
        return $container;
    };

    $(document).on('click', '#cgCreateViaOpenAI', function(){
        $(this).addClass('cg_active');
        cgJsClassAdmin.gallery.functions.openAiShowModels($(this).closest('.media-frame'), false);
    });

    $(document).on('click', '#cgOpenAiContainer.cg_cloned .cg_openai_model_card', function(){
        var $container = $(this).closest('#cgOpenAiContainer');
        var isEdit = $container.data('operation') === 'edit';
        var isFree = $container.attr('data-cg-free') === '1';
        var model = $(this).attr('data-ai-model-name');
        var $selected = $container.find('#cgOpenAiSelected');
        var $model = $selected.find('.cg_openai_model').filter(function(){
            return $(this).attr('data-ai-model-name') === model;
        });
        var quality = $container.data('quality') || 'medium';
        var $quality = $model.find('.cg_openai_quality');
        $quality.find('option').each(function(){
            var locked = isEdit && isFree && $(this).attr('data-cg-pro-edit') === '1';
            $(this).prop('disabled', locked).text($(this).attr('data-cg-label') + (locked ? ' — PRO' : ''));
        });
        var supported = $quality.find('option').filter(function(){ return this.value === quality && !this.disabled; }).length;
        $container.find('.cg_openai_quality_changed').toggleClass('cg_hide', !!supported);
        if (!supported) { quality = 'medium'; }
        $quality.val(quality);
        $container.data('quality', quality);
        $model.find('.cg_openai_size').val($container.data('size') || '1024x1024');
        $selected.find('.cg_openai_model').addClass('cg_hide');
        $model.removeClass('cg_hide');
        $container.find('#cgOpenAiOverview').addClass('cg_hide');
        $selected.removeClass('cg_hide');
        $container.addClass('cg_openai_model_selected');
        $selected.find('#cgOpenAiGenImageContainer, #cgOpenAiGenError, #cgOpenAiAddToWpLoaderContainer, #cgOpenAiAddToWpErrorContainer, #cgOpenAiAddToWpSuccessContainer').addClass('cg_hide');
        var hasKey = $('#cgOpenAiKeyOption').val() === '1';
        $selected.find('#cgOpenAiKeyContainer').toggleClass('cg_hide', hasKey);
        $selected.find('#cgOpenAiPromptContainer').removeClass('cg_hide');
        $selected.find('#cgOpenAiPromptInput').prop('disabled', !hasKey).toggleClass('cg_disabled_one', !hasKey);
        $selected.find('#cgOpenAiPromptSubmit').prop('disabled', !hasKey || !$selected.find('#cgOpenAiPromptInput').val().trim()).toggleClass('cg_disabled_one', !hasKey || !$selected.find('#cgOpenAiPromptInput').val().trim());
        $selected.find('#cgOpenAiPromptShowCurrentBalance').toggleClass('cg_hide', !hasKey);
        $selected.find('#cgOpenAiSupportedLanguages, #cgOpenAiHideMoreSupportedLanguages').addClass('cg_hide');
        $selected.find('#cgOpenAiShowMoreSupportedLanguages').removeClass('cg_hide');
        $container.find('#cgOpenAiPrompts').removeClass('cg_hide');
        var prompts = cgJsClassAdmin.gallery.vars.openAiPrompts || [];
        $container.find('#cgOpenAiPromptsEntered').toggleClass('cg_hide', !prompts.length);
        $container.find('#cgOpenAiPromptsNotEntered').toggleClass('cg_hide', !!prompts.length);
        prompts.slice().reverse().forEach(function(item){
            cgJsClassAdmin.gallery.functions.appendAiPrompt($container, item);
        });
        $container.find('#cgOpenAiMorePrompts').toggleClass('cg_hide', !!cgJsClassAdmin.gallery.vars.openAiPromptsAllGot);
    });

    $(document).on('click', '#cgOpenAiBackToModels', function(){
        var $container = $(this).closest('#cgOpenAiContainer');
        $container.find('#cgOpenAiOverview').removeClass('cg_hide');
        $container.find('#cgOpenAiSelected, #cgOpenAiPrompts').addClass('cg_hide');
        $container.removeClass('cg_openai_model_selected');
    });

    $(document).on('change', '#cgOpenAiContainer.cg_cloned .cg_openai_quality, #cgOpenAiContainer.cg_cloned .cg_openai_size', function(){
        var key = $(this).hasClass('cg_openai_quality') ? 'quality' : 'size';
        $(this).closest('#cgOpenAiContainer').data(key, $(this).val()).find('.cg_openai_quality_changed').addClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiPromptSubmit',function (){
        var $cgOpenAiContainer = $(this).closest('#cgOpenAiContainer');
        var $mediaFrame = $(this).closest('.media-frame');
        if ($(this).prop('disabled') || $(this).hasClass('cg_disabled_one') || $cgOpenAiContainer.data('request-pending')) { return; }
        $cgOpenAiContainer.data('request-pending', true);
        $(this).prop('disabled', true);
        $cgOpenAiContainer.find('.cg_openai_model_card, #cgOpenAiBackToModels, #cgOpenAiEditSelectImages, .cg_openai_quality, .cg_openai_size, #cgOpenAiPromptInput').prop('disabled', true);
        cgJsClassAdmin.gallery.vars.cgOpenAiGenIsValid = false;
        cgJsClassAdmin.gallery.vars.cgOpenAiGenErrorMessage = 'OpenAI did not return a valid response. Please try again later.';
        cgJsClassAdmin.gallery.vars.cgOpenAiGenUrl = '';
        cgJsClassAdmin.gallery.vars.cgOpenAiLastPrompt = false;


        var $cgOpenAiSelected = $(this).closest('#cgOpenAiSelected');
        var $cgOpenAiGenImageContainer = $cgOpenAiSelected.find('#cgOpenAiGenImageContainer');
        $cgOpenAiGenImageContainer.find('#cgOpenAiImageFields .cg_text').val('');
        $cgOpenAiGenImageContainer.addClass('cg_hide').removeClass('cg_horizontal_loader cg_vertical_loader');
        $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer,#cgOpenAiAddToWpErrorContainer,#cgOpenAiAddToWpSuccessContainer,#cgOpenAiGenError').addClass('cg_hide');
        var $cgOpenAiGenLoaderContainer = $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer');
        var $cg_skeleton_loader_on_page_load_container = $cgOpenAiSelected.find('.cg_skeleton_loader_on_page_load_container');
        $cg_skeleton_loader_on_page_load_container.css({
            'width':'',
            'height':''
        });
        $cgOpenAiSelected.find('.cg_openai_img').css({
            'width': '',
            'height': ''
        });
        $cgOpenAiGenLoaderContainer.removeClass('cg_hide').get(0).scrollIntoView();
        $cgOpenAiSelected.find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
        var $cgOpenAiModel = $cgOpenAiSelected.find('.cg_openai_model:not(.cg_hide)');
        var quality = $cgOpenAiModel.find('.cg_openai_quality').val();
        var $cgOpenAiGenLoader = $cgOpenAiSelected.find('#cgOpenAiGenLoader');
        $cgOpenAiGenLoader.removeClass('cg_horizontal_loader cg_vertical_loader');
        var data = {};
        debugger

        if($mediaFrame.find('.media-frame-content.cg_openai_edit').length){

            data['action'] = 'post_cg_edit_openai_image';
            data['cg_nonce'] = CG1LBackendNonce.nonce;
            data['cg_images'] = [];

            $mediaFrame.find('.cg_openai_image_to_edit').each(function () {
                data['cg_images'].push(
                    {
                        'id':$(this).attr('data-cg-id'),
                        'filename':$(this).attr('data-cg-filename'),
                        'mime':$(this).attr('data-cg-mime'),
                    }
                );
            });

            $cg_skeleton_loader_on_page_load_container.css({
                'width': cgJsClassAdmin.gallery.vars.openAIImagesToEditWidth,
                'height': cgJsClassAdmin.gallery.vars.openAIImagesToEditHeight
            });

        }else{
            data['action'] = 'post_cg_generate_openai_image';
            data['cg_nonce'] = CG1LBackendNonce.nonce;
        }

        data['cg_ai_model'] = $cgOpenAiModel.attr('data-ai-model-name');
        var size = $cgOpenAiModel.find('.cg_openai_size').val();
        data['cg_ai_res'] = $cgOpenAiContainer.data('operation') === 'edit' ? 'auto' : size;
        data['cg_ai_quality'] = quality;
        var orientation = size === '1536x1024' ? 'horizontal' : (size === '1024x1536' ? 'vertical' : '');

        if(orientation=='horizontal'){
            $cgOpenAiGenLoader.addClass('cg_horizontal_loader');
            $cgOpenAiGenImageContainer.addClass('cg_horizontal_loader');
        }else if(orientation=='vertical'){
            $cgOpenAiGenLoader.addClass('cg_vertical_loader');
            $cgOpenAiGenImageContainer.addClass('cg_vertical_loader');
        }
        data['cgOpenAiPromptInput'] = $(this).parent().find('#cgOpenAiPromptInput').val();

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

            debugger

            if(cgJsClassAdmin.gallery.vars.cgOpenAiGenIsValid){
                // so URL will be already loaded
                $cgOpenAiGenImageContainer.css({
                        'height':'0',
                        'overflow':'hidden'
                }).removeClass('cg_hide');

                if($mediaFrame.find('.media-frame-content.cg_openai_edit').length){
                    $cgOpenAiSelected.find('.cg_openai_img').css({
                        'width': cgJsClassAdmin.gallery.vars.openAIImagesToEditWidth,
                        'height': cgJsClassAdmin.gallery.vars.openAIImagesToEditHeight
                    });
                }

                $cgOpenAiSelected.find('#cgOpenAiImg').attr('src',cgJsClassAdmin.gallery.vars.cgOpenAiGenUrl);

                setTimeout(function () {
                    debugger
                    $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer').addClass('cg_hide');
                    $cgOpenAiGenImageContainer.css({
                            'height':'',
                            'overflow':''
                     }).get(0).scrollIntoView();
                },2000);

                $cgOpenAiGenImageContainer.find('#cgOpenAiAddToWpLib,#cgOpenAiAddToWpLibAndGallery').addClass('cg_disabled_one');

                if(cgJsClassAdmin.gallery.vars.cgOpenAiLastPrompt){
                    cgJsClassAdmin.gallery.vars.openAiPrompts.unshift(cgJsClassAdmin.gallery.vars.cgOpenAiLastPrompt);
                    cgJsClassAdmin.gallery.functions.appendAiPrompt($cgOpenAiContainer,cgJsClassAdmin.gallery.vars.cgOpenAiLastPrompt);
                }

            }else{
                $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer').addClass('cg_hide');
                $cgOpenAiGenImageContainer.addClass('cg_hide');
                $cgOpenAiSelected.find('#cgOpenAiGenError').text(cgJsClassAdmin.gallery.vars.cgOpenAiGenErrorMessage).removeClass('cg_hide');
            }

            $cgOpenAiGenImageContainer.find('#cgOpenAiAltText, #cgOpenAiCaption, #cgOpenAiDescription').val(data['cgOpenAiPromptInput']);

            var urlName = data['cgOpenAiPromptInput'].split('.')[0];
            var postTitle = data['cgOpenAiPromptInput'].split('.')[0];

            if(urlName.length >= 50){
                urlName = urlName.substr(0,50);
                $cgOpenAiGenImageContainer.find('#cgOpenAiImageName').val(urlName);
            }else{// then there must be simply a title
                $cgOpenAiGenImageContainer.find('#cgOpenAiImageName').val(urlName);
            }

            if(data['cgOpenAiPromptInput'].indexOf('.') > -1){
                postTitle += '.';
            }

            $cgOpenAiGenImageContainer.find('#cgOpenAiTitle').val(postTitle);
            $cgOpenAiSelected.find('#cgOpenAiPromptSubmit,#cgOpenAiAddToWpLib').removeClass('cg_disabled_one');

        }).fail(function (xhr, status) {
            $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').addClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiGenError').text(status === 'timeout'
                ? 'The request timed out. Check whether OpenAI completed the request before trying again.'
                : 'The request could not be completed. Check your connection and login before trying again.').removeClass('cg_hide');
        }).always(function () {
            $cgOpenAiContainer.data('request-pending', false);
            $cgOpenAiContainer.find('.cg_openai_model_card, #cgOpenAiBackToModels, #cgOpenAiEditSelectImages, .cg_openai_quality, .cg_openai_size, #cgOpenAiPromptInput').prop('disabled', false);
            $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer').addClass('cg_hide');
            var disabled = !$cgOpenAiSelected.find('#cgOpenAiPromptInput').val().trim();
            $cgOpenAiSelected.find('#cgOpenAiPromptSubmit').prop('disabled', disabled).toggleClass('cg_disabled_one', disabled);
        });

    });

    $(document).on('click','#cgOpenAiAddToWpLib,#cgOpenAiAddToWpLibAndGallery',function (){
        if ($(this).hasClass('cg_disabled_one')) { return; }
        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid = false;
        cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage = 'The image could not be saved. Please check your media library before trying again.';

        var $cgOpenAiSelected = $(this).closest('#cgOpenAiSelected');
        var data = {};
        data['action'] = 'post_cg_add_openai_image';
        data['cg_nonce'] = CG1LBackendNonce.nonce;
        //data['cg_openai_image_url'] = $cgOpenAiSelected.find('#cgOpenAiImg').attr('src');
        data['cg_openai_image_url'] = cgJsClassAdmin.gallery.vars.cgOpenAiGenUrl;
        data['cg_openai_image_name'] = $cgOpenAiSelected.find('#cgOpenAiImageName').val().trim();
        data['cgOpenAiAltText'] = $cgOpenAiSelected.find('#cgOpenAiAltText').val().trim();
        data['cgOpenAiTitle'] = $cgOpenAiSelected.find('#cgOpenAiTitle').val().trim();
        data['cgOpenAiCaption'] = $cgOpenAiSelected.find('#cgOpenAiCaption').val().trim();
        data['cgOpenAiDescription'] = $cgOpenAiSelected.find('#cgOpenAiDescription').val().trim();
        data['cgGalleryID'] = $('#cgBackendGalleryId').val();
        data['cg_openai_image_is_add_to_gallery'] = 0;
        if($(this).attr('id')=='cgOpenAiAddToWpLibAndGallery'){
            data['cg_openai_image_is_add_to_gallery'] = 1;
        }
        $cgOpenAiSelected.find('#cgOpenAiAddToWpErrorContainer, #cgOpenAiAddToWpSuccessContainer').addClass('cg_hide');
        $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').addClass('cg_hide');
        $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer').removeClass('cg_hide').get(0).scrollIntoView();

        debugger

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

            $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer').addClass('cg_hide');

            debugger

            if(cgJsClassAdmin.gallery.vars.cgAddOpenAiImageIsValid){
                if(data['cg_openai_image_is_add_to_gallery']){
                    cgJsClassAdmin.gallery.vars.cgNewAiImageToGalleryAdded = 1;
                }
                $cgOpenAiSelected.find('#cgOpenAiAddToWpSuccessContainer').removeClass('cg_hide');
            }else{
                $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').removeClass('cg_hide');
                $cgOpenAiSelected.find('#cgOpenAiAddToWpErrorContainer').removeClass('cg_hide').find('#cgOpenAiAddToWpError').html(cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage);
            }

            // Media Library because new WP image added
            wp.media.frame.content.get().collection._requery(true);
            wp.media.frame.content.get().options.selection.reset();

        }).fail(function () {
            $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').removeClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiAddToWpErrorContainer').removeClass('cg_hide').find('#cgOpenAiAddToWpError').text('The save request could not be completed. Check your WordPress media library before trying again.');
        }).always(function () {
            $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer').addClass('cg_hide');

        });

    });

    $(document).on('input','#cgOpenAiPromptInput',function (){
        var $container = $(this).closest('#cgOpenAiContainer');
        $container.find('#cgOpenAiPromptSubmit').prop('disabled', $(this).prop('disabled') || !$(this).val().trim() || !!$container.data('request-pending'));

        var $cg_openai_button_container = $(this).closest('.cg_openai_button_container');
        if($(this).val().trim() == ''){
            $cg_openai_button_container.find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
            $cg_openai_button_container.find('#cgOpenAiPromptInputClear').addClass('cg_hide');
        }else{
            $cg_openai_button_container.find('#cgOpenAiPromptSubmit').removeClass('cg_disabled_one');
            $cg_openai_button_container.find('#cgOpenAiPromptInputClear').removeClass('cg_hide');
        }
        cgJsClassAdmin.gallery.vars.openAiPrompt = $(this).val();
    });

    $(document).on('input','#cgOpenAiImageName',function (){
        if($(this).val().trim() == ''){
            $(this).closest('#cgOpenAiGenImageContainer').find('#cgOpenAiAddToWpLib, #cgOpenAiAddToWpLibAndGallery').addClass('cg_disabled_one');
        }else{
            $(this).closest('#cgOpenAiGenImageContainer').find('#cgOpenAiAddToWpLib, #cgOpenAiAddToWpLibAndGallery').removeClass('cg_disabled_one');
        }
    });

    $(document).on('click','#cgOpenAiShowMoreSupportedLanguages',function (e){
        e.preventDefault();
        $(this).addClass('cg_hide');
        $(this).parent().find('#cgOpenAiSupportedLanguages').removeClass('cg_hide');
        $(this).parent().find('#cgOpenAiHideMoreSupportedLanguages').removeClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiHideMoreSupportedLanguages',function (e){
        e.preventDefault();
        $(this).addClass('cg_hide');
        $(this).parent().find('#cgOpenAiSupportedLanguages').addClass('cg_hide');
        $(this).parent().find('#cgOpenAiShowMoreSupportedLanguages').removeClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiSelected .cg_clear',function (e){
        e.preventDefault();
        $(this).parent().find('.cg_text,#cgOpenAiPromptInput').val('').focus();
        if($(this).attr('id')=='cgOpenAiPromptInputClear'){
            cgJsClassAdmin.gallery.vars.openAiPrompt = '';
            $(this).closest('.cg_openai_button_container').find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
        }
        $(this).addClass('cg_hide');
    });

    $(document).on('input','#cgOpenAiImageFields .cg_text',function (){
        var $cg_clear_container = $(this).closest('.cg_clear_container');
        if($(this).val().trim() == ''){
            $cg_clear_container.find('.cg_clear').addClass('cg_hide');
        }else{
            $cg_clear_container.find('.cg_clear').removeClass('cg_hide');
        }
    });

};
