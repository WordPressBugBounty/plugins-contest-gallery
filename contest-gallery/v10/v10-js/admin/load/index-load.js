cgJsClassAdmin.index.functions.cgLoadBackendAjax = function (urlString,formPostData,$formLinkObject,submitMessage,cg_picture_id_to_scroll) {

    cgJsClassAdmin.index.vars.isCreateUploadAreaLoaded = false;
    cgJsClassAdmin.index.vars.isOptionsAreaLoaded = false;
    cgJsClassAdmin.index.vars.isCreateRegistryAreaLoaded = false;
    cgJsClassAdmin.index.vars.isGalleryAreaLoaded = false;

    var $ = jQuery;
    if(!$formLinkObject){
        $formLinkObject = $('<div></div>');
    }

    var version = cgJsClassAdmin.index.functions.cgGetVersionForUrlJs();

    if(urlString === '?page='+version+'/index.php'){// only set nonce at the beginning then
        var cg_nonce = $('#cg_nonce').val();
        urlString += '&cg_nonce='+cg_nonce;
    }

    cgJsClassAdmin.index.vars.$cg_main_container.addClass('cg_pointer_events_none');
    debugger
    // AJAX Call - Submit Form
    $.ajax({
        url: 'admin-ajax.php'+urlString,
        method: 'post',
        data: formPostData,
        dataType: null,
        contentType: false,
        processData: false
    }).done(function(response) {

        if(cgJsClassAdmin.index.functions.isInvalidNonce($,response)){
            return;
        }

        cgJsClassAdmin.options.vars.$wpadminbar = $('#wpadminbar');

        cgJsClassAdmin.index.vars.$cg_main_container.removeClass('cg_pointer_events_none');

        cgJsClassAdmin.index.functions.noteIfIsIE();

        // cgJsClassAdmin.gallery.vars.isHashJustChanged = true;
        //console.log('urlString');
        //console.log(urlString);
        var $response = $(new DOMParser().parseFromString(response, 'text/html'));
        var cgVersionCurrent = $response.find('#cgVersion').val();
        $response.find('#cgGallerySubmitTop').addClass('cg_hide');

        cgJsClassAdmin.index.functions.cgSetVersionForUrlJs($response.find('#cgGetVersionForUrlJs').val());

        if(cgJsClassAdmin.index.vars.cgVersion===0){
            cgJsClassAdmin.index.vars.cgVersion = cgVersionCurrent;
        }

        if(cgJsClassAdmin.index.vars.cgVersion !== 0 && (cgJsClassAdmin.index.vars.cgVersion!=cgVersionCurrent)){

            cgJsClassAdmin.index.functions.newVersionReload();

            return;

        }else{

            // set always this as backup! Before set in indexedDB

            // since 25.12.2020, simple version check, no localStorage or IndexedDB check anymore
            //localStorage.setItem(cgJsClassAdmin.index.vars.cgVersionLocalStorageName, cgVersionCurrent);

            // IMPORTANT!!!! Has to be set everytime here!!!!!
            // since 25.12.2020, simple version check, no localStorage or IndexedDB check anymore
            //cgJsClassAdmin.index.indexeddb.setAdminData(cgVersionCurrent);

            var $cg_main_container = $('#cg_main_container');
            cgJsClassAdmin.index.functions.cgMainContainerEmpty($cg_main_container);

            if(!$formLinkObject.hasClass('cg_load_backend_copy_gallery') && submitMessage){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(submitMessage,true);
            }

            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
            $cg_main_container.find('#cgBackendLoader').remove();

            $cg_main_container.append($response.find('body').html());// stats with html and contains body. Body content has to be inserted. Otherwise error because html can not be inserted in html.
            $('#cgGalleryLoader').addClass('cg_hide');

            if($formLinkObject.hasClass('cg_load_backend_copy_gallery')){

                if($cg_main_container.find('#cgProcessedImages').length){
                    $formLinkObject.find('.cg_copy_start').val($cg_main_container.find('#cgProcessedImages').val());
                    $formLinkObject.find('.option_id_next_gallery').val($cg_main_container.find('#cgNextIdGallery').val());
                    $('.cg_first_in_progress').remove();
                    cgJsClassAdmin.index.functions.cgLoadBackend($formLinkObject,true,true,true,true,true);
                }else{
                    if($cg_main_container.find('#cgGalleryBackendDataManagement').length){
                        cgJsClassAdmin.gallery.vars.isHashJustChanged = true;
                        location.hash = '#option_id='+$cg_main_container.find('#cgNextIdGallery').val()+'&edit_gallery=true&cg_nonce='+$('#cg_nonce').val();
                        $('.cg_first_in_progress').remove();
                        cgJsClassAdmin.gallery.load.init($,false,$formLinkObject,undefined,undefined,cg_picture_id_to_scroll);
                        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Gallery copied',true);
                    }
                }
            }else{

                // setTimeout removed since 14.0.4
                setTimeout(function () {
                    //$("#cg_main_options").addClass('cg_fade_in_0_2');
                    $("#cg_main_options").removeClass('cg_hidden');
                    $("#cg_save_all_options").removeClass('cg_hidden');
                },50);

                if($cg_main_container.find('#cgGalleryBackendDataManagement').length){
                    cgJsClassAdmin.gallery.load.init($,false,$formLinkObject,undefined,undefined,cg_picture_id_to_scroll);
                }

                if($cg_main_container.find('#cg_main_options').length){
                    cgJsClassAdmin.options.functions.loadOptionsArea($,$formLinkObject,$response);
                }

                if($cg_main_container.find('#ausgabe1.cg_create_upload').length){
                    cgJsClassAdmin.createUpload.functions.load($,$formLinkObject,$response);
                }

                if($cg_main_container.find('#ausgabe1.cg_registry_form_container').length){
                    cgJsClassAdmin.createRegistry.functions.load($,$formLinkObject,$response);
                }

                if($cg_main_container.find('#cgMainMenuTable').length){
                    cgJsClassAdmin.mainMenu.functions.load($,$formLinkObject,$response);
                }

                if($cg_main_container.find('#cgVotesImageVisualContent').length || $cg_main_container.find('#cgCommentsImageVisualContent').length){
                    $response.find('script[data-cg-processing="true"]').each(function () {
                        var script = jQuery(this).html();
                        eval(script);
                    });
                    cgJsClassAdmin.gallery.functions.loadBlockquote($);
                }

                if($cg_main_container.find('#cgImgThumbContainerMain').length){
                    $("#cg_rotate_image").addClass('cg_hidden');
                    setTimeout(function (){// is set to go sure content loaded when adding height to rotate container
                        cgJsClassAdmin.gallery.functions.cgRotateOnLoad($);
                        $("#cg_rotate_image").removeClass('cg_hidden');
                    },200);
                }

                $("#cg_changes_saved").fadeOut(4000);
                //cgJsClassAdmin.gallery.vars.cgLoadOptions($);
                //window.scrollTo(0,0);

            }

        }

        cgJsClassAdmin.index.functions.noteIfIsIE();

        cgJsClassAdmin.index.functions.resize(cgJsClassAdmin.index.vars.$wpBodyContent,cgJsClassAdmin.index.vars.$cg_main_container);

        var $cgCreatedNewGallery = $('#cgCreatedNewGallery');
        var $cgEditOptionsButton = $('#cgEditOptionsButton');
        var $cgDocumentation = $('#cgDocumentation');

        if($cgCreatedNewGallery.length){
            $cgCreatedNewGallery.get(0).scrollIntoView();
        }else if($cgEditOptionsButton.length){
            $cgEditOptionsButton.get(0).scrollIntoView();
        }else if($cgDocumentation.length){
            $cgDocumentation.get(0).scrollIntoView();
        }

       // setTimeout(function (){
            //$formLinkObject.remove();
       // },2000);

//       setTimeout(function (){
            // bind this event as next before anything else

        var $cg_nav_menu_row_container = jQuery('#cg_nav_menu_row_container');
        $cg_nav_menu_row_container.find('.cg_backend_button_general').removeClass('cg_active');

        if(location.href.indexOf('define_upload=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuContactForm .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('create_user_form=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuRegForm .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('users_management=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuUsersManagement .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('cg_edit_translations=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuEditTranslations .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('cg_edit_ecommerce=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuEditEcommerce .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('edit_options=true')>-1){
            $cg_nav_menu_row_container.find('#cgEditOptionsButton .cg_backend_button_general').addClass('cg_active');
        }else if(location.href.indexOf('cg_orders=true')>-1 || location.href.indexOf('cg_show_order=true')>-1){
            $cg_nav_menu_row_container.find('#cgNavMenuEcommerceOrders .cg_backend_button_general').addClass('cg_active');
        }

        if(location.href.indexOf('cg_comment_id=')>-1){
            var cg_comment_id = location.href.split('cg_comment_id=')[1];
            var $cg_comment = $('#cg_comment_id_'+cg_comment_id);
            $cg_comment.addClass('cg_blink').get(0).scrollIntoView();
            setTimeout(function (){
                $cg_comment.removeClass('cg_blink');
            },2000);
        }

        cgJsClassAdmin.index.functions.setCgNonce($);

    }).fail(function(xhr, status, error) {
        cgJsClassAdmin.index.functions.noteIfIsIE();
    }).always(function() {

    });
}