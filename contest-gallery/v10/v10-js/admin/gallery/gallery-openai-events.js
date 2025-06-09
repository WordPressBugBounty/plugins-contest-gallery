//jQuery(document).ready(function ($) {
cgJsClassAdmin.gallery.functions.loadAiEvents = function($){

    var $mediaFrame;
    var status;
    var $cgOpenAiContainer;
    var $mediaFrameContentSub;
    var statusSelected;

    $(document).on('click','#cgCreateViaOpenAI',function (){
        debugger
        $mediaFrame = $('.media-frame.cg_backend_area');

        $(this).addClass('cg_active');
        var $mediaFrameContent =  $mediaFrame.find('.media-frame-content');
        $mediaFrameContentSub =  $mediaFrame.find('.media-frame-content > *');
        $mediaFrameContentSub.addClass('cg_hide');
        $cgOpenAiContainer = $('#cgOpenAiContainer.cg_cloned');
        if($cgOpenAiContainer.length){
            $cgOpenAiContainer.removeClass('cg_hide');
        }else{
            $cgOpenAiContainer = $('#cgOpenAiContainer').clone().removeClass('cg_hide').addClass('cg_cloned');
            $mediaFrameContent.append($cgOpenAiContainer);
        }

        $cgOpenAiContainer.find('.cg_openai_main').addClass('cg_hide');
        $cgOpenAiContainer.find('#cgOpenAiGenLoaderContainer').addClass('cg_hide');

        $cgOpenAiContainer.closest('.media-frame-content').css('overflow','visible');// has to otherwise scroll appears twice
        debugger
        if(!status || status=='key-verified-and-overview'){
            $cgOpenAiContainer.find('.cg_openai_main#cgOpenAiOverview').removeClass('cg_hide');
        }else if(statusSelected){
            $cgOpenAiContainer.find('.cg_openai_main#cgOpenAiSelected').removeClass('cg_hide');
        }

        $cgOpenAiContainer.find('#cgOpenAiPromptInput').val(cgJsClassAdmin.gallery.vars.openAIPrompt);

        if(cgJsClassAdmin.gallery.vars.openAIPrompt){
            $cgOpenAiContainer.find('#cgOpenAiPromptSubmit').removeClass('cg_disabled_one');
        }else{
            $cgOpenAiContainer.find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
        }

    });

    $(document).on('click','.cg_openai_model',function (){

        if($(this).closest('#cgOpenAiSelected').length){
            return;
        }

        var cgOpenAiKey = $('#cgOpenAiKeyOption').val();
        statusSelected = $(this).attr('data-ai-model-name');
        $cgOpenAiContainer.find('.cg_openai_main').addClass('cg_hide');

        debugger
    //if(cgOpenAiKey && status == 'key-verified-and-overview' || !cgOpenAiKey){
  //  if(cgOpenAiKey && status == 'key-verified-and-overview' || !cgOpenAiKey){
        var $cgOpenAiSelected = $cgOpenAiContainer.find('#cgOpenAiSelected');
        $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer,#cgOpenAiAddToWpErrorContainer,#cgOpenAiAddToWpSuccessContainer').addClass('cg_hide');
        $cgOpenAiSelected.removeClass('cg_hide');
        $cgOpenAiSelected.find('.cg_openai_model').addClass('cg_hide');
        $cgOpenAiSelected.find('.cg_openai_model[data-ai-model-name="'+statusSelected+'"]').removeClass('cg_hide');
        $cgOpenAiSelected.find('#cgOpenAiSupportedLanguages').addClass('cg_hide');
        $cgOpenAiSelected.find('#cgOpenAiHideMoreSupportedLanguages').addClass('cg_hide');
        if(cgOpenAiKey){
            $cgOpenAiSelected.find('#cgOpenAiPromptContainer').removeClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').addClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiKeyContainer').addClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiShowMoreSupportedLanguages').removeClass('cg_hide');
        }else{
            var $cgOpenAiKeyContainer = $cgOpenAiContainer.find('#cgOpenAiKeyContainer');
            $cgOpenAiSelected.find('#cgOpenAiPromptContainer').addClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiGenImageContainer').addClass('cg_hide');
            $cgOpenAiSelected.find('.cg_openai_resolutions_container').addClass('cg_hide');
            $cgOpenAiSelected.find('#cgOpenAiModelGpt1Explanation').addClass('cg_hide');
            $cgOpenAiKeyContainer.removeClass('cg_hide');
            $cgOpenAiKeyContainer.find('#cgOpenAiKeyError').addClass('cg_hide');
        }
//     }else if(status == 'key-not-valid'){
        //var $cgOpenAiKeyNotValid = $cgOpenAiContainer.find('#cgOpenAiKeyNotValid');
        //$cgOpenAiKeyNotValid.removeClass('cg_hide');
//     }

    });

    $(document).on('click','#cgOpenAiBackToModels',function (){
        var $cgOpenAiContainer = $(this).closest('#cgOpenAiContainer');
        $cgOpenAiContainer.find('#cgOpenAiOverview').removeClass('cg_hide');
        $cgOpenAiContainer.find('#cgOpenAiSelected').addClass('cg_hide');
    });

    $(document).on('click','#cgOpenAiKeySubmit',function (){

        var $cgOpenAiKey = $(this).closest('#cgOpenAiKey');
        var data = {};
        data['action'] = 'post_cg_check_open_ai_key';
        data['cgOpenAiKeyInput'] = $(this).parent().find('#cgOpenAiKeyInput').val();

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

            if(cgJsClassAdmin.gallery.vars.cgOpenAiKeyIsValid){
                $cgOpenAiContainer.find('.cg_openai_main').addClass('cg_hide');
                var $cgOpenAiSelected = $cgOpenAiContainer.find('#cgOpenAiSelected');
                $cgOpenAiSelected.removeClass('cg_hide');
                $cgOpenAiSelected.find('.cg_openai_model[data-ai-model-name="'+statusSelected+'"]').removeClass('cg_hide');
            }else{
                $cgOpenAiKey.find('#cgOpenAiKeyError').html(cgJsClassAdmin.gallery.vars.cgOpenAiKeyErrorMessage).removeClass('cg_hide');
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

    $(document).on('click','#cgOpenAiPromptSubmit',function (){

        var $cgOpenAiSelected = $(this).closest('#cgOpenAiSelected');
        var $cgOpenAiGenImageContainer = $cgOpenAiSelected.find('#cgOpenAiGenImageContainer');
        $cgOpenAiGenImageContainer.find('#cgOpenAiImageFields .cg_text').val('');
        $cgOpenAiGenImageContainer.addClass('cg_hide').removeClass('cg_horizontal_loader cg_vertical_loader');
        $cgOpenAiSelected.find('#cgOpenAiAddToWpLoaderContainer,#cgOpenAiAddToWpErrorContainer,#cgOpenAiAddToWpSuccessContainer,#cgOpenAiGenError').addClass('cg_hide');
        $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer').removeClass('cg_hide').get(0).scrollIntoView();
        $cgOpenAiSelected.find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
        var $cgOpenAiModel = $cgOpenAiSelected.find('.cg_openai_model:not(.cg_hide)');
        var $cg_openai_res_quality = $cgOpenAiModel.find('.cg_openai_res_quality.cg_selected');
        var $cgOpenAiGenLoader = $cgOpenAiSelected.find('#cgOpenAiGenLoader');
        $cgOpenAiGenLoader.removeClass('cg_horizontal_loader cg_vertical_loader');
        var data = {};
        data['action'] = 'post_cg_generate_open_ai_image';
        data['cg_ai_model'] = $cgOpenAiModel.attr('data-ai-model-name');
        var $cg_openai_res_size = $cgOpenAiModel.find('.cg_openai_res:not(.cg_openai_res_quality).cg_selected .cg_openai_res_size');
        data['cg_ai_res'] = $cg_openai_res_size.attr('data-cg-res');
        var orientation = $cg_openai_res_size.attr('data-cg-orientation');
        debugger

        if($cg_openai_res_quality.length){
            data['cg_ai_quality'] = $cg_openai_res_quality.attr('data-cg-quality');
        }

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

            }else{
                $cgOpenAiSelected.find('#cgOpenAiGenLoaderContainer').addClass('cg_hide');
                $cgOpenAiGenImageContainer.addClass('cg_hide');
                $cgOpenAiSelected.find('#cgOpenAiGenError').html(cgJsClassAdmin.gallery.vars.cgOpenAiGenErrorMessage).removeClass('cg_hide');
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

    $(document).on('click','#cgOpenAiAddToWpLib,#cgOpenAiAddToWpLibAndGallery',function (){

        var $cgOpenAiSelected = $(this).closest('#cgOpenAiSelected');
        var data = {};
        data['action'] = 'post_cg_add_open_ai_image';
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
                $cgOpenAiSelected.find('#cgOpenAiAddToWpErrorContainer').removeClass('cg_hide').find('#cgOpenAiAddToWpError').html(cgJsClassAdmin.gallery.vars.cgAddOpenAiImageErrorMessage);
            }

            // Media Library because new WP image added
            wp.media.frame.content.get().collection._requery(true);
            wp.media.frame.content.get().options.selection.reset();

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

    $(document).on('input','#cgOpenAiPromptInput',function (){
        if($(this).val().trim() == ''){
            $(this).closest('.cg_openai_button_container').find('#cgOpenAiPromptSubmit').addClass('cg_disabled_one');
        }else{
            $(this).closest('.cg_openai_button_container').find('#cgOpenAiPromptSubmit').removeClass('cg_disabled_one');
        }
        cgJsClassAdmin.gallery.vars.openAIPrompt = $(this).val();
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

    $(document).on('click','#cgOpenAiModels .cg_openai_resolutions .cg_openai_res',function (e){
        e.preventDefault();
        $(this).closest('.cg_openai_model').find('.cg_openai_res:not(.cg_openai_res_quality)').removeClass('cg_selected');
        $(this).closest('.cg_openai_model').find('.cg_openai_res.cg_openai_res_quality').removeClass('cg_selected');
        $(this).closest('.cg_openai_resolutions').find('.cg_openai_res.cg_openai_res_quality').addClass('cg_selected');
        $(this).addClass('cg_selected');
    });

    $(document).on('click','#cgOpenAiModels .cg_openai_resolutions .cg_openai_res.cg_openai_res_quality',function (e){
        e.preventDefault();
        $(this).closest('.cg_openai_model').find('.cg_openai_res.cg_openai_res_quality').removeClass('cg_selected');
        $(this).closest('.cg_openai_model').find('.cg_openai_res:not(.cg_openai_res_quality)').removeClass('cg_selected');
        $(this).closest('.cg_openai_resolutions').find('.cg_openai_res:not(.cg_openai_res_quality)').first().addClass('cg_selected');
        $(this).addClass('cg_selected');
    });

    $(document).on('click','#cgOpenAiSelected .cg_clear',function (e){
        e.preventDefault();
        $(this).parent().find('.cg_text,#cgOpenAiPromptInput').val('');
        if($(this).attr('id')=='cgOpenAiPromptInputClear'){
            cgJsClassAdmin.gallery.vars.openAIPrompt = '';
        }
    });

};