cgJsClassAdmin.gallery.load = cgJsClassAdmin.gallery.functions || {};
cgJsClassAdmin.gallery.load = {
    init: function($,isAddImages,$formLinkObject,isImagesAdded,addedImagesResponse,cg_picture_id_to_scroll){
        debugger
        if(isAddImages){
            $('#cg_uploading_gif').hide();
            $('#cg_uploading_div').hide();
            $('#cgSortable').remove();
            $('#cgGallerySubmitTop').remove();
            $('#cgStepsNavigationTop').remove();
        }

        if(!document.getElementById('cgGalleryForm')){
            return;
        }

        cgJsClassAdmin.index.vars.isGalleryAreaLoaded = true;

        // reset here, cg_view_option can be init again
        // do only if sell container in header will be completely new loaded
        if(!isAddImages){
            cgJsClassAdmin.index.vars.isSellConfEventsLoaded = false;
        }

        // reset here because has to be initiated again
        cgJsClassAdmin.index.vars.isSellConfTinyMCEInitialized = false;
        cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id = null;

//    $('#cgViewControl').removeClass('cg_hide');
        $('.cg_steps_navigation').removeClass('cg_hide');
        $('#cgSortable').removeClass('cg_hide');

        cgJsClassAdmin.gallery.vars.setStarOnStarOffSrc();

// Add icon

        $( "#cg_server_power_info" ).hover(function() {
            //alert(3);
            $('#cg_answerPNG').toggle();
            $(this).css('cursor','pointer');
        });

        $( "#cg_adding_images_info" ).hover(function() {
            //alert(3);
            $('#cg_adding_images_answer').toggle();
            $(this).css('cursor','pointer');
        });

//Check if the current URL contains '#'

        // Verstecken weiterer Boxen

        $('.mehr').hide();
        $('.clickBack').hide();

        // moved from gallery_admin --- ENDE

        $('#chooseAll').prop('checked',false);

        if($formLinkObject){

            if($formLinkObject.hasClass('cg_load_backend_create_gallery')){

                $('#cgGalleryLoader').addClass('cg_hide');
                $('#cgSortable').addClass('cg_hide');
                $('#cgGallerySubmit').addClass('cg_hide');
                $(".cg-created-new-gallery").fadeOut(8000);
                $(".cg-created-new-gallery-br").fadeOut(8000);
                return;
            }
        }

        $('#cgGalleryLoader').removeClass('cg_hide');
        $('#cgViewControl').removeClass('cg_hide');


        $('#cgSortable').remove();// can be removed here on load
        $('#cgGallerySubmitTop').remove();// can be removed here on load
        $('#cgGallerySubmit').remove();// can be removed here on load
        $('#cgStepsNavigationBottom').remove();// can be removed here on load

        var gid = $('#cgBackendGalleryId').val();

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        // BG is for backend gallery
        var cgStart_BG = localStorage.getItem('cgStart_BG_'+gid);
        // check types also here because can be 0
        if(cgStart_BG || cgStart_BG===0 || cgStart_BG==='0'){$('#cgStartValue').val(cgStart_BG)}
        var cgStep_BG = localStorage.getItem('cgStep_BG_'+gid);
        if(cgStep_BG){$('#cgStepValue').val(cgStep_BG)}
        var cgOrder_BG = localStorage.getItem('cgOrder_BG_'+gid);

        // fallback to go sure if empty or old order options are activated
        if(!cgOrder_BG){
            cgOrder_BG = 'custom';
        }else if(cgOrder_BG=='rating_desc_average' || cgOrder_BG=='rating_asc_average' || cgOrder_BG=='rating_desc_average_with_manip' || cgOrder_BG=='rating_asc_average_with_manip'){
            cgOrder_BG = 'custom';
        }

        // check if generally available as option. If not date desc as fallback.
        // switching from one to multiple stars and the other way round might cause not existing order if order other kind of rating was selected
        if(!$('#cgOrderSelect option[value='+cgOrder_BG+']').length){
            cgOrder_BG = 'custom';
        }

        if(cgOrder_BG){
            if(cgOrder_BG.indexOf('_average')>-1 && $('#cgAllowRating').val()!=1){
                if(cgOrder_BG){$('#cgOrderValue').val('custom');}
            }else{
                if(cgOrder_BG){$('#cgOrderValue').val(cgOrder_BG);}
            }
        }else{
            if(cgOrder_BG){$('#cgOrderValue').val(cgOrder_BG);}
        }

        var cgSearch_BG = localStorage.getItem('cgSearch_BG_'+gid);
        if(cgSearch_BG){$('#cgSearchInput').val(cgSearch_BG)}

        var form = document.getElementById('cgGalleryForm');
        var formPostData = new FormData(form);

        // !IMPORTANT!!!! Do not remove otherwise recursion error! Needs to check first time new backend ajax version 10.9.9 null null is installed
        formPostData.append('cgBackendHash',$('#cgBackendHash').val());
        //formPostData.append('action', 'post_contest_gallery_action_ajax');


        // AJAX Call - Load when site load
        cgJsClassAdmin.gallery.functions.requests.push($.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function(response) {
debugger

            if(cgJsClassAdmin.index.functions.isInvalidNonce($,response)){
                return;
            }

            if(response=='newversion'){

                cgJsClassAdmin.index.functions.newVersionReload();

                return;

            }

            // to go sure remove it on every load
            $('#cgDeleteOriginalImageSourceAlso').remove();

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });

            if(cgJsClassAdmin.gallery.functions.missingRights($,response)){return;}

            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();

            if(addedImagesResponse && addedImagesResponse.indexOf('cg-pro-version-only')!=-1){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('One of the selected file types is PRO version only');
            }

            if(addedImagesResponse && addedImagesResponse.indexOf('cg-file-type-is-not-supported')!=-1){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('One of the selected file types is not supported');
            }

            cgJsClassAdmin.gallery.vars.selectChanged = false;
            cgJsClassAdmin.gallery.vars.inputsChanged = false;

            $('#cgGalleryLoader').addClass('cg_hide');

            var htmlDom = new DOMParser().parseFromString(response, 'text/html');
            var $cgSortable = $(htmlDom.getElementById('cgSortable'));
            cgJsClassAdmin.gallery.vars.$cgSortable = $cgSortable;
            var $cgGallerySubmitTop = $(htmlDom.getElementById('cgGallerySubmitTop'));
            $cgGallerySubmitTop.removeClass('cg_sticky').css({
                'top':'',
                'width':''
            });
            cgJsClassAdmin.gallery.vars.$cgGallerySubmitTop = $cgGallerySubmitTop;
            var $cgGallerySubmit = $(htmlDom.getElementById('cgGallerySubmit'));
            $('#cgStepsNavigationTop').remove(); // remove existing cgStepsNavigationTop first
            var $cgStepsNavigationTop = $(htmlDom.getElementById('cgStepsNavigationTop'));
            $cgStepsNavigationTop.removeClass('cg_hide');
            var $cgStepsNavigationBottom = $(htmlDom.getElementById('cgStepsNavigationBottom'));
            var cg_files_count_total = $(htmlDom.getElementById('cg_files_count_total')).val();

            if(cg_files_count_total>1){
                var cg_files_count_total = $('#cg_sort_files_form_button').removeClass('cg_hide');
            }else{
                var cg_files_count_total = $('#cg_sort_files_form_button').addClass('cg_hide');
            }

            $cgStepsNavigationTop.insertAfter($('#cgGalleryLoader'));
            $cgSortable.insertAfter($cgStepsNavigationTop);
            $cgGallerySubmitTop.insertBefore($cgSortable);

            var isNoImagesFound = false;

            if($cgSortable.find('.cg_sortable_div').length>=1){
                $cgGallerySubmit.insertAfter($cgSortable);
                $('#cgViewControl').removeClass('cg_hide');
                $cgSortable.find('#cgNoImagesFound').addClass('cg_hide');
            }else{
                isNoImagesFound = true;
                $cgSortable.find('#cgNoImagesFound').removeClass('cg_hide');
                //$cgSortable.addClass('cg_hide');
                $('#cgGallerySubmit').addClass('cg_hide');
                $cgGallerySubmitTop.addClass('cg_hide');
            }

            $cgStepsNavigationBottom.insertAfter($cgGallerySubmit);

            cgJsClassAdmin.gallery.functions.markSearchedValueFields($);

            $('#cgStepsChanged').prop('disabled',true);

            cgJsClassAdmin.gallery.functions.sortableInit($);
            cgJsClassAdmin.gallery.functions.markSortedByCustomFields($);
            cgJsClassAdmin.gallery.functions.initDateTimePicker($);

            if(isNoImagesFound){
                cgJsClassAdmin.gallery.functions.checkIfFurtherImagesAvailable($);
            }

            debugger
            if(isAddImages){
                if(isImagesAdded){// have to be done here, isAddImages condition has to be valid, so bottom condition not executed!
                    if(addedImagesResponse.indexOf('cg-pdf-previews-to-create')>-1){
                        cgJsClassAdmin.gallery.pdf.createAndSetPdfPreviewPrepare($,addedImagesResponse,false,$response);
                    }
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Entries added',true);
                    jQuery('html, body').animate({
                            scrollTop: jQuery('#cgSortable').offset().top - 150+'px'
                        }, 0, function () {
                    });
                }else{
                    var $changesSaved = $('#cgSortable').find('#cg_changes_saved');
                    if($changesSaved.length){
                        $changesSaved.remove();
                    }
                }
            }else{
                if($formLinkObject){

                    if($formLinkObject.hasClass('cg_load_backend_submit_form_submit') && !$formLinkObject.hasClass('cg_reset_all_informed')){
                        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Changes saved',true);
                    }
                    if($formLinkObject.hasClass('cg_reset_all_informed')){
                        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('All informed users are reseted<br>They will be informed again if entry will be activated again<br>Entry has to be deactivated before');
                    }

                }
            }

            if(cg_picture_id_to_scroll){
                var $cg_picture_id_to_scroll = jQuery('#div'+cg_picture_id_to_scroll);
                if($cg_picture_id_to_scroll.length){
                    $cg_picture_id_to_scroll.get(0).scrollIntoView();
                }
            }

            cgJsClassAdmin.index.functions.setCgNonce($);

            cgJsClassAdmin.gallery.functions.loadBlockquote($);

   //         $('#div3887').prepend('<blockquote class="twitter-tweet"><p lang="en" dir="ltr">Sunsets don&#39;t get much better than this one over <a href="https://twitter.com/GrandTetonNPS?ref_src=twsrctfw">@GrandTetonNPS</a>. <a href="https://twitter.com/hashtag/nature?src=hash&amp;ref_src=twsrctfw">#nature</a> <a href="https://twitter.com/hashtag/sunset?src=hash&amp;ref_src=twsrctfw">#sunset</a> <a href="http://t.co/YuKy2rcjyU">pic.twitter.com/YuKy2rcjyU</a></p>&mdash; US Department of the Interior (@Interior) <a href="https://twitter.com/Interior/status/463440424141459456?ref_src=twsrctfw">May 5, 2014</a></blockquote><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>');

        }).fail(function(xhr, status, error) {
            debugger

        }).always(function() {

        }));

    },
};
