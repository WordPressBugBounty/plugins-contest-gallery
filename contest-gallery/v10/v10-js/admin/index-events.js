jQuery(document).ready(function($){

    var $wpBodyContent = jQuery('#wpbody-content');
    var $cg_main_container = jQuery('#cg_main_container');
    cgJsClassAdmin.index.vars.$cg_main_container = $cg_main_container;
    var $cgGoTopOptions = jQuery('#cgGoTopOptions');

    cgJsClassAdmin.index.functions.cgSetVersionForUrlJs($('#cgGetVersionForUrlJs').val());

    cgJsClassAdmin.index.vars.$wpBodyContent = $wpBodyContent;
    cgJsClassAdmin.index.vars.$cg_main_container = $cg_main_container;
    cgJsClassAdmin.index.vars.$cgGoTopOptions = $cgGoTopOptions;
    cgJsClassAdmin.index.vars.windowHeight = $(window).height();
    cgJsClassAdmin.index.functions.resize(cgJsClassAdmin.index.vars.$wpBodyContent,cgJsClassAdmin.index.vars.$cg_main_container);

    $( window ).resize(function(e) {
        cgJsClassAdmin.index.vars.windowHeight = $(window).height();
        cgJsClassAdmin.index.functions.resize(cgJsClassAdmin.index.vars.$wpBodyContent,cgJsClassAdmin.index.vars.$cg_main_container);
        if(cgJsClassAdmin.index.vars.resizeLeftSideIsActive){
            cgJsClassAdmin.index.vars.$cgCreateUploadSortableArea.css('width',cgJsClassAdmin.index.vars.$ausgabe1.width()+'px');
        }
    });

    // if user click on wordpress collapse menu
    $( document ).on('click','#collapse-menu',function (e) {
        cgJsClassAdmin.index.vars.windowHeight = $(window).height();
        cgJsClassAdmin.index.functions.resize(cgJsClassAdmin.index.vars.$wpBodyContent,cgJsClassAdmin.index.vars.$cg_main_container);
    });

        // since 25.12.2020, simple version check, no localStorage or IndexedDB check anymore
    //cgJsClassAdmin.index.vars.cgVersionLocalStorageName = 'cgVersionLocalStorage'+'/'+location.hostname+location.pathname;

    cgJsClassAdmin.index.functions.checkIfIsIE();

    // since 25.12.2020, simple version check, no localStorage or IndexedDB check anymore
    //cgJsClassAdmin.index.indexeddb.init();

    cgJsClassAdmin.index.vars.wpVersion = cgJsClassAdmin.index.functions.getWpVersionAsInteger();

    if(location.search.indexOf('index.php&')>-1){
        var searchToReplace = location.search.replace('index.php&','index.php#');
        window.history.replaceState(null, null, searchToReplace);
    }

    if(location.hash!='' && location.hash!='#' && location.hash!='#main'){// then must be browser reload by user

        var cgBackendHashVal = cgJsClassAdmin.index.functions.cgLoadBackendLoader();

        var formPostData = new FormData();
        formPostData.append('action', 'post_contest_gallery_action_ajax');
        formPostData.append('cgBackendHash',cgBackendHashVal);
        debugger
        cgJsClassAdmin.index.functions.cgLoadBackendAjax('?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php&'+location.hash.split('#')[1],formPostData);

    }else{// then must be main menu load

        var cgBackendHashVal = cgJsClassAdmin.index.functions.cgLoadBackendLoader();

        var formPostData = new FormData();
        formPostData.append('action', 'post_contest_gallery_action_ajax');
        formPostData.append('cgBackendHash',cgBackendHashVal);
        debugger
        if(location.search.indexOf('option_id') >= 0 && location.search.indexOf('index.php&') >= 0){
            cgJsClassAdmin.gallery.vars.isHashJustChanged = true;
            cgJsClassAdmin.index.functions.cgLoadBackendAjax('?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php&'+location.search.split('index.php&')[1],formPostData);
        }else{
            cgJsClassAdmin.index.functions.cgLoadBackendAjax('?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php',formPostData);
        }

    }

    window.onhashchange = function() {
        if(cgJsClassAdmin.gallery.vars.isHashJustChanged){
            cgJsClassAdmin.gallery.vars.isHashJustChanged = false;
            return;
        }else{
            debugger
            var formPostData = new FormData();
            formPostData.append('action', 'post_contest_gallery_action_ajax');
            formPostData.append('cgBackendHash',$('#cgBackendHash').val());
            if(location.hash.split('#')[1]){
                cgJsClassAdmin.index.functions.cgLoadBackendAjax('?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php&'+location.hash.split('#')[1],formPostData);
            }else{
                cgJsClassAdmin.index.functions.cgLoadBackendAjax('?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php',formPostData);
            }
        }
    };

    $(document).on('submit','.cg_load_backend_submit',function (e) {
        e.preventDefault();
        $(window).scrollTop(0);
        cgJsClassAdmin.index.functions.cgLoadBackend($(this),$(this).hasClass('cg_load_backend_submit_save_data'));

    });

    $(document).on('click','.cg_load_backend_link',function (e) {
        e.preventDefault();
        cgJsClassAdmin.index.functions.cgLoadBackend($(this));
    });

    tinymce.on('AddEditor', function(e) {
        if (e.editor.id === 'cgMailBodyToSend') {
            e.editor.on('init', function() {
                var content = localStorage.getItem('cgMailBodyToSend');
                if(content){
                    e.editor.setContent(content);
                }else{
                    e.editor.setContent('Hello!');
                }
                e.editor.theme.resizeTo(null, 200);
                $('#cgMailBodyToSendContainer').css('visibility', 'visible').css('min-height', 'unset');
                $('#cgMailSendButtonContainer').css('visibility', 'visible');
            });
            // When user types or content changes
            e.editor.on('input paste change SetContent Undo Redo', function () {
                clearTimeout(e.editor._cgSaveTimeout);
                e.editor._cgSaveTimeout = setTimeout(function () {
                    var content = e.editor.getContent();
                    localStorage.setItem('cgMailBodyToSend', content);
                }, 300);
            });
        }
        if (e.editor.id === 'cgMailBodyToSendUnconfirmed') {
            e.editor.on('init', function() {
                var content = localStorage.getItem('cgMailBodyToSendUnconfirmed');
                if(content){
                    e.editor.setContent(content);
                }else{
                    e.editor.setContent($('#cgMailBodyConfirmationText').val());
                }
                e.editor.theme.resizeTo(null, 200);
                $('#cgMailBodyToSendContainer').css('visibility', 'visible').css('min-height', 'unset');
                $('#cgMailSendButtonContainer').css('visibility', 'visible');
            });
            // When user types or content changes
            e.editor.on('input paste change SetContent Undo Redo', function () {
                clearTimeout(e.editor._cgSaveTimeout);
                e.editor._cgSaveTimeout = setTimeout(function () {
                    var content = e.editor.getContent();
                    localStorage.setItem('cgMailBodyToSendUnconfirmed', content);
                }, 300);
            });
        }
    });


});