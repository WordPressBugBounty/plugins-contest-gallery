//jQuery(document).ready(function ($) {
cgJsClassAdmin.gallery.functions.loadAiEditEvents = function($){

    var $mediaFrame;
    var status;
    var $cgOpenAiContainer;
    var $mediaFrameContentSub;
    var statusSelected;

    $(document).on('click','#cgEditViaOpenAI',function (){
        debugger
        $mediaFrame = $('.media-frame.cg_backend_area');
        var $element = $(this);
        var $mediaFrameContent =  $mediaFrame.find('.media-frame-content');
        $mediaFrameContent.css('overflow','');// happens when #cgCreateViaOpenAI is clicked
        $mediaFrameContentSub =  $mediaFrame.find('.media-frame-content > *');
        $mediaFrameContentSub.addClass('cg_hide');
        if($element.hasClass('cg_select_openai_images')) {
             // continue here
            var toEdit = cgJsClassAdmin.gallery.vars.openAIImagesToEdit;
            cgJsClassAdmin.gallery.vars.openAIImagesToEditById = null;// reset first
            var hasPngJpg = false;
            for(var index in toEdit){
                if(!toEdit.hasOwnProperty(index)){
                    break;
                }
                if(toEdit[index]['mime'] == 'image/png' || toEdit[index]['mime'] == 'image/jpg' || toEdit[index]['mime'] == 'image/jpeg'){
                    hasPngJpg = true;
                    if(!cgJsClassAdmin.gallery.vars.openAIImagesToEditById){
                        cgJsClassAdmin.gallery.vars.openAIImagesToEditById = {};
                    }
                    cgJsClassAdmin.gallery.vars.openAIImagesToEditById[toEdit[index]['id']] = toEdit[index];
                }
            }
            if(hasPngJpg){
                $mediaFrameContent.prepend(
                    '<div id="cgOpenAiSelectedToEdit">Selected media files to be edited by OpenAI</div>'
                );
                $('#cgCreateViaOpenAI').click().removeClass('cg_active');
                $mediaFrameContent.find('#cgOpenAiModelGpt1').click();
                var $cgOpenAiEditImages = $('<div id="cgOpenAiEditImages"></div>');
                $cgOpenAiEditImages.insertBefore($mediaFrameContent.find('#cgOpenAiPromptContainer'));
                var toEditById = cgJsClassAdmin.gallery.vars.openAIImagesToEditById;
                for(var id in toEditById){
                    if(!toEditById.hasOwnProperty(id)){
                        break;
                    }
                    var large = toEditById[id]['sizes']['full']['url'];
                    var mime = toEditById[id]['mime'];
                    var filename = toEditById[id]['filename'];
                    var id = toEditById[id]['id'];
                    if(toEditById[id]['sizes']['large']){
                        large = toEditById[id]['sizes']['large']['url'];
                    }
                    $cgOpenAiEditImages.append('<div class="cg_openai_image_to_edit" style="background: url('+large+')" data-cg-id="'+toEditById[id]['id']+'" data-cg-full="'+toEditById[id]['sizes']['full']['url']+'"  data-cg-large="'+large+'" data-cg-mime="'+mime+'"  data-cg-filename="'+filename+'" ></div>');

                    // required when submitting prompt later
                    var img = new Image();
                    img.src = large;
                    img.onload = function() {
                        // calculate here the required width
                        var width = 491;
                        var height = this.height * width / this.width;
                        cgJsClassAdmin.gallery.vars.openAIImagesToEditWidth = width;
                        cgJsClassAdmin.gallery.vars.openAIImagesToEditHeight = height;
                    };

                }
                $mediaFrameContent.find('#cgOpenAiEditDesc,#cgOpenAiPrompts').removeClass('cg_hide');
                $mediaFrameContent.find('.cg_openai_create_desc').addClass('cg_hide');
            }else{
                $mediaFrameContent.prepend(
                    '<div id="cgOpenAiNoImagesSelected">Only JPG and PNG files are allowed</div>'
                );
            }
            $element.removeClass('cg_select_openai_images');

            if (location.search.indexOf('page=contest-gallery-pro') === -1) {
                $mediaFrameContent.find('.cg_openai_res.cg_openai_res_quality[data-cg-quality="high"]').addClass('cg-pro-false');
            }

        }else{
            // only one image allowed to select for edit
            //cgJsClassAdmin.gallery.vars.openedFileFrame.multiple = false;
            // add class click, set multiple false depends on lick
            //$('.cg_upload_wp_images_button').click();
            $('.cg_upload_wp_images_button').addClass('cg_openai_edit').click();
            $mediaFrame = cgJsClassAdmin.gallery.vars.openedFileFrame.$el;
            $mediaFrame.addClass('cg_openai_edit_clicked');
            //$mediaFrame.find('#menu-item-browse').click().removeClass('active');
            $mediaFrame.find('#menu-item-browse').removeClass('active');
            var $mediaFrameContent = $mediaFrame.find('.media-frame-content');
            $element = $mediaFrame.find('#cgEditViaOpenAI');
            var $cgOpenAiSelectToEdit = $mediaFrameContent.find('#cgOpenAiSelectToEdit');
            if($cgOpenAiSelectToEdit.length){
                $cgOpenAiSelectToEdit.removeClass('cg_hide');
            }else{
                $mediaFrameContent.addClass('cg_openai_edit').prepend(
                    '<div id="cgOpenAiSelectToEdit">' +
                        '<input class="cg_hide" type="button" id="cgOpenAiEditSelectedImages" value="Edit previous selected image" style="margin-bottom: 20px;  background-color: white; color: black; display: block; width: auto;">' +
                    'Select an image file to be edited by OpenAI' +
                    '</div>'
                );
            }
            if(cgJsClassAdmin.gallery.vars.openAIImagesToEditById){
                $mediaFrameContent.find('#cgOpenAiEditSelectedImages').removeClass('cg_hide');
            }else{
                $mediaFrameContent.find('#cgOpenAiEditSelectedImages').addClass('cg_hide');
            }
        }
        $mediaFrameContent.addClass('cg_openai_edit');
        $element.addClass('cg_active');
    });

    $(document).on('click','#cgOpenAiEditSelectImages',function (){
        $('#cgEditViaOpenAI').click();
    });

    $(document).on('click','#cgOpenAiEditSelectedImages',function (){
        $('#cgEditViaOpenAI').addClass('cg_select_openai_images').click();
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
        $cgOpenAiContainer.find('#cgOpenAiPromptInput').val(text).get(0).scrollIntoView();
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