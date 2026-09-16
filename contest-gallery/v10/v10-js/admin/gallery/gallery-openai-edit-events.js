cgJsClassAdmin.gallery.functions.loadAiEditEvents = function($){

    $(document).on('click', '#cgEditViaOpenAI', function(){
        var $element = $(this);
        var $mediaFrame = $element.closest('.media-frame');
        var $content = $mediaFrame.find('.media-frame-content');
        if ($content.find('#cgOpenAiContainer').data('request-pending')) { return; }
        if ($element.hasClass('cg_select_openai_images')) {
            var selected = cgJsClassAdmin.gallery.vars.openAIImagesToEdit || [];
            var images = {};
            selected.forEach(function(item){
                if (item.mime === 'image/png' || item.mime === 'image/jpg' || item.mime === 'image/jpeg') {
                    images[item.id] = item;
                }
            });
            cgJsClassAdmin.gallery.vars.openAIImagesToEditById = images;
            var $container = cgJsClassAdmin.gallery.functions.openAiShowModels($mediaFrame, true);
            var $images = $container.find('#cgOpenAiEditImages').empty();
            Object.keys(images).forEach(function(id){
                var item = images[id];
                var preview = item.sizes.large || item.sizes.full;
                var $image = $('<div class="cg_openai_image_to_edit"></div>').attr({
                    'data-cg-id': item.id, 'data-cg-filename': item.filename, 'data-cg-mime': item.mime
                });
                $('<img>').attr({src: preview.url, alt: item.filename}).appendTo($image);
                $images.append($image);
                var width = Math.min(491, preview.width || 491);
                cgJsClassAdmin.gallery.vars.openAIImagesToEditWidth = width;
                cgJsClassAdmin.gallery.vars.openAIImagesToEditHeight = (preview.height || width) * width / (preview.width || width);
            });
            if (!Object.keys(images).length) {
                $container.find('#cgOpenAiOverview').addClass('cg_hide');
                $images.text('Only JPG and PNG images can be edited here. Select another image.');
            }
            $element.removeClass('cg_select_openai_images');
        } else {
            $('.cg_upload_wp_images_button').first().addClass('cg_openai_edit').click();
            $mediaFrame = cgJsClassAdmin.gallery.vars.openedFileFrame.$el;
            $mediaFrame.addClass('cg_openai_edit_clicked');
            $content = $mediaFrame.find('.media-frame-content').addClass('cg_openai_edit');
            $element = $mediaFrame.find('#cgEditViaOpenAI');
            var $select = $content.find('#cgOpenAiSelectToEdit');
            if (!$select.length) {
                $select = $('#cgOpenAiSelectToEditTemplate').children().first().clone();
                $content.prepend($select);
            }
            $select.removeClass('cg_hide');
            $select.find('#cgOpenAiEditSelectedImages').toggleClass('cg_hide', !cgJsClassAdmin.gallery.vars.openAIImagesToEditById || !Object.keys(cgJsClassAdmin.gallery.vars.openAIImagesToEditById).length);
        }
        $content.addClass('cg_openai_edit');
        $element.addClass('cg_active');
    });

    $(document).on('click', '#cgOpenAiEditSelectImages', function(){
        $(this).closest('.media-frame').find('#cgEditViaOpenAI').click();
    });

    $(document).on('click', '#cgOpenAiEditSelectedImages', function(){
        $(this).closest('.media-frame').find('#cgEditViaOpenAI').addClass('cg_select_openai_images').click();
    });

    $(document).on('click','#cgOpenAiShowMore',function (){
        debugger
        $(this).addClass('cg_hide');
        var $cgOpenAiContainer = $(this).closest('#cgOpenAiContainer');
        if($cgOpenAiContainer.find('.cg_openai_prompt').length){
            $cgOpenAiContainer.find('#cgOpenAiPromptsEntered').removeClass('cg_hide');
            $cgOpenAiContainer.find('#cgOpenAiPromptsNotEntered').addClass('cg_hide');
        }else{
            $cgOpenAiContainer.find('#cgOpenAiPromptsEntered').addClass('cg_hide');
            $cgOpenAiContainer.find('#cgOpenAiPromptsNotEntered').removeClass('cg_hide');
        }
        $cgOpenAiContainer.find('#cgOpenAiPromptsContainer').removeClass('cg_hide');
        $cgOpenAiContainer.css('overflow','hidden');
        //$cgOpenAiContainer.css('overflow-x','hidden');
        //$cgOpenAiContainer.css('overflow-y','scroll');
        $cgOpenAiContainer.find('#cgOpenAiPrompts').css('height','100%');
        $cgOpenAiContainer.find('#cgOpenAiShowLess').removeClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiShowLess',function (){
        debugger
        $(this).addClass('cg_hide');
        var $cgOpenAiContainer = $(this).closest('#cgOpenAiContainer');
        $cgOpenAiContainer.find('#cgOpenAiPromptsContainer').addClass('cg_hide');
        $cgOpenAiContainer.css('overflow','');
        $cgOpenAiContainer.find('#cgOpenAiPrompts').css('height','');
        $cgOpenAiContainer.find('#cgOpenAiShowMore').removeClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiContainer.cg_media_container.cg_cloned .cg_openai_prompt',function (){
        debugger
        var $cgOpenAiContainer = $(this).closest('#cgOpenAiContainer');
        var text = $(this).find('.cg_openai_prompt_text').text();
        $cgOpenAiContainer.find('#cgOpenAiPromptInput').val(text).trigger('input').get(0).scrollIntoView();
        if(text && text.length){
            $cgOpenAiContainer.find('#cgOpenAiPromptSubmit').removeClass('cg_disabled_one');
        }
        $cgOpenAiContainer.find('#cgOpenAiShowLess').click();
    });

    $(document).on('click','#cgOpenAiMorePrompts',function (){
        debugger
        cgJsClassAdmin.gallery.functions.getAiPrompts($,$(this).closest('#cgOpenAiPromptsEntered').find('.cg_openai_prompt').length,true);
    });

};