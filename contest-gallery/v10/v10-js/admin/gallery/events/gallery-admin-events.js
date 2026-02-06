jQuery(document).ready(function ($) {

    $(document).on('click', '#cgShowComments .cg_comment_delete', function(){
        if($(this).prop('checked')){
            $(this).closest('.cg_comment').find('.cg_comment_activate,.cg_comment_deactivate').prop('checked',false);
        }
    });

    $(document).on('click', '#cgShowComments .cg_comment_activate', function(){
        if($(this).prop('checked')){
            $(this).closest('.cg_comment').find('.cg_comment_delete,.cg_comment_deactivate').prop('checked',false);
        }
    });

    $(document).on('click', '#cgShowComments .cg_comment_deactivate', function(){
        if($(this).prop('checked')){
            $(this).closest('.cg_comment').find('.cg_comment_delete,.cg_comment_activate').prop('checked',false);
        }
    });


// View control ajax posts and similiar

    var removeCgActiveFromViewControl = function (){
        $('.cg_image_checkbox_container_view_control .cg_image_checkbox').removeClass('cg_active');
    }

    var cgChangedAndSearchedValueSelector = cgJsClassAdmin.gallery.vars.cgChangedAndSearchedValueSelector;

    $(document).on('click', '#cgSortable .cg_go_to_save_button', function (e) {
        e.preventDefault();
        $("html, body").animate({scrollTop: $(document).height()}, 0);
        var $cg_gallery_backend_submit = $('.cg_gallery_backend_submit');
        $cg_gallery_backend_submit.addClass('cg_blink');
        setTimeout(function (){
            $cg_gallery_backend_submit.removeClass('cg_blink');
        },2000);

    });

    $(document).on('mouseenter', '#cgGalleryBackendContainer .cg_backend_info_container.cg_searched_value', function () {
        $(this).parent().find('.cg-info-container').first().show();
    });

    $(document).on('mouseleave', '#cgGalleryBackendContainer .cg_backend_info_container.cg_searched_value', function () {
        $(this).parent().find('.cg-info-container').first().hide();
    });


    // send only values that are needed to send
    $(document).on('change', cgChangedAndSearchedValueSelector, function () {
        $(this).addClass('cg_value_changed');
    });


    $(document).on('click', '#cgGalleryBackendContainer #cgPicsPerSite .cg_step', function (e) {
        e.preventDefault();
        cgJsClassAdmin.gallery.functions.abortRequest();
debugger
        removeCgActiveFromViewControl();

        $('#cgPicsPerSite .cg_step').removeClass('cg_step_selected');

        // have to start from 0 again then
        $('#cgStepsChanged').prop('disabled', false);

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        removeCgActiveFromViewControl();

        cgJsClassAdmin.gallery.load.changeViewByControl($, $(this).addClass('cg_step_selected'));

    });

    $(document).on('click', '#cgGalleryBackendContainer .cg_steps_navigation .cg_step', function (e) {
        e.preventDefault();
        cgJsClassAdmin.gallery.functions.abortRequest();

        debugger

        removeCgActiveFromViewControl();

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        cgJsClassAdmin.gallery.load.changeViewByControl($, null, $(this));

        if ($(this).closest('.cg_steps_navigation').attr('id') == 'cgStepsNavigationBottom') {
            document.getElementById('cgGalleryForm').scrollIntoView();
        }

    });

    $(document).on('change', '#cgGalleryBackendContainer #cgOrderSelect', function () {
        cgJsClassAdmin.gallery.functions.abortRequest();

        var $selected = $(this).find(':selected');

        // reset to date desc in custom or further fields selected
        if($selected.closest('#cgOrderSelectCustomFields').length || $selected.closest('#cgOrderSelectFurtherFields').length){
            $('#cgOrderValue').val($selected.val());
        }else{
            $('#cgOrderValue').val($selected.val());
        }

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        removeCgActiveFromViewControl();

        cgJsClassAdmin.gallery.load.changeViewByControl($,false,false,false,false,false,$selected);

    });

    $(document).on('change', '#cgMainMenuTable #cgOrderSelect', function () {
        $(this).closest('#cgViewControl').find('.cg_load_main_menu_'+$(this).val()).click();
    });

    $(document).on('input', '#cgGalleryBackendContainer #cgSearchInput', function () {

        cgJsClassAdmin.gallery.functions.abortRequest();

        var $el = $(this);
        $el.val($el.val().trim());
        $('#cgStartValue').val('0');
        if ($el.val().length >= 1) {
            $el.addClass('cg_searched_value');
            $('#cgSearchInputButton').removeClass('cg_hide');
            $('#cgSearchInputClose').removeClass('cg_hide');
        } else {
            $el.removeClass('cg_searched_value');
            //$('#cgSearchInputButton').addClass('cg_hide');
            //$('#cgSearchInputClose').addClass('cg_hide');
        }

    });

    $(document).on('keypress', '#cgGalleryBackendContainer #cgSearchInput', function (e) {

        if (e.which == 13) {

            cgJsClassAdmin.gallery.functions.searchInputButtonClick();
            e.preventDefault();
            return false;

        }

    });

    $(document).on('click', '#cgSearchInputButton', function () {

        removeCgActiveFromViewControl();
        cgJsClassAdmin.gallery.functions.searchInputButtonClick();

    });

    $(document).on('click', '#cgShowOnlyWinnersCheckbox', function () {

        if($(this).prop('checked')){
            $(this).addClass('cg_searched_value_checkbox');
        }else{
            $(this).removeClass('cg_searched_value_checkbox');
        }


        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        removeCgActiveFromViewControl();

        cgJsClassAdmin.gallery.functions.abortRequest();
        cgJsClassAdmin.gallery.load.changeViewByControl($, null, null, null, true);

    });

    $(document).on('click', '#cgShowOnlyActiveCheckbox', function () {

        $('#cgShowOnlyInactiveCheckbox').prop('checked',false);

        if($(this).prop('checked')){
            $(this).addClass('cg_searched_value_checkbox');
        }else{
            $(this).removeClass('cg_searched_value_checkbox');
        }

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        removeCgActiveFromViewControl();

        cgJsClassAdmin.gallery.functions.abortRequest();
        cgJsClassAdmin.gallery.load.changeViewByControl($, null, null, null, true);

    });

    $(document).on('click', '#cgShowOnlyInactiveCheckbox', function () {

        $('#cgShowOnlyActiveCheckbox').prop('checked',false);

        if($(this).prop('checked')){
            $(this).addClass('cg_searched_value_checkbox');
        }else{
            $(this).removeClass('cg_searched_value_checkbox');
        }

        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        removeCgActiveFromViewControl();

        cgJsClassAdmin.gallery.functions.abortRequest();
        cgJsClassAdmin.gallery.load.changeViewByControl($, null, null, null, true);

    });

    $(document).on('submit', '#cgGalleryBackendContainer #cgGalleryForm', function (e) {
        e.preventDefault();
        cgJsClassAdmin.gallery.functions.abortRequest();
        jQuery('body').removeClass('cg_no_scroll');
        $('#cgViewControl .cg_image_checkbox').removeClass('cg_active');
        // disable all fields which were not changed!!!!
        $(cgChangedAndSearchedValueSelector).not(".cg_value_changed").prop('disabled', true);
        cgJsClassAdmin.gallery.load.changeViewByControl($, null, null, true,false,true);
    });

    $(document).on('click', '#cgGalleryBackendContainer #cgSearchInputClose', function (e) {
        e.preventDefault();
        cgJsClassAdmin.gallery.functions.abortRequest();

        var $cgSearchInput = $('#cgSearchInput');

        $cgSearchInput.removeClass('cg_searched_value');

        $(this).addClass('cg_hide');
        $('#cgSearchInputButton').addClass('cg_hide');

        $cgSearchInput.val('');

        localStorage.setItem('cgSearch_BG_' + gid, '');


        // to go simply sure that nothing will be deleted!!!
        $('#cgGalleryForm').find('.cg_delete').remove();

        cgJsClassAdmin.gallery.load.changeViewByControl($);
    });


// View control ajax posts and similiar -- END


    $(document).on('click', '#cgGalleryBackendContainer #CatWidget', function (e) {

        if ($(this).is(":checked")) {
            $("#ShowCatsUnchecked").removeClass("cg_disabled");
        }
        else {
            $("#ShowCatsUnchecked").addClass("cg_disabled");
        }

    });

    $(document).on('keypress', '#cgGalleryBackendContainer .cg_manipulate_plus_value .cg_manipulate_5_star_input', function (e) {
        //if the letter is not digit then display error and don't type anything
        if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            //display error message
            // $("#cg_options_errmsg").html("Only numbers are allowed").show().fadeOut("slow");
            return false;
        }
    });


    $(document).on('input', '#cgGalleryBackendContainer .cg_manipulate_counts_input', function (e) {

        if (parseInt(this.value) < 0) {
            this.value = 0;
        }

        var cgSortableDiv = $(this).closest('.cgSortableDiv');
        var $cg_backend_info_container = $(this).closest('.cg_backend_info_container');


        var cg_rating_value_text = cgSortableDiv.find('.cg_rating_value').text();
        cg_rating_value_width = cg_rating_value_text.length * 8;

        var originValue = parseInt(cgSortableDiv.find('.cg_value_origin').val());

        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
            var addValue = parseInt(this.value);
        }
        else {
            var addValue = parseInt(this.value);
        }

        if (isNaN(addValue)) {
            addValue = 0;
        }

        if (isNaN(originValue)) {
            originValue = 0;
        }

        var newValue = originValue + addValue;

        if (newValue < 1) {
            cgSortableDiv.find('.cg_rating_center img').attr('src', cgJsClassAdmin.gallery.vars.setStarOffSrc);
            cgSortableDiv.find('.cg_rating_center .cg_backend_star').removeClass('cg_backend_star_on').addClass('cg_backend_star_off');
            newValue = 0;
        }
        else {
            cgSortableDiv.find('.cg_rating_center .cg_backend_star').removeClass('cg_backend_star_off').addClass('cg_backend_star_on');
        }

        cgSortableDiv.find('.cg_rating_value').text(newValue);
        cgSortableDiv.find('.cg_value_add_one_star').val(addValue).removeClass('cg_disabled_send');

        if (addValue >= 1) {
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes').text(addValue).removeClass('cg_hide');
        } else {
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes').addClass('cg_hide');
        }

    });

    $(document).on('input', '#cgGalleryBackendContainer .cg_manipulate_5_star_input', function (e) {

        if (parseInt(this.value) < 0) {
            this.value = 0;
        }

        if (this.value.length > 7) {
            this.value = this.value.slice(0, 7);
            var addValue = this.value;
        }
        else {
            var addValue = this.value;
        }

        if (isNaN(addValue)) {
            addValue = 0;
        }

        $(this).removeClass('cg_disabled_send');

        var cgSortableDiv = $(this).closest('.cgSortableDiv');
        var $cg_backend_info_container = $(this).closest('.cg_backend_info_container');
        var dataStar = $(this).attr('data-star');

        var container = $(this).closest('.cgSortableDiv');
        var countRbefore = container.find('.cg_value_origin_5_star_count').val();

        var addedValue = 0;

        var ratingRnew = container.find('.cg_value_origin_5_star_rating').val();

        addValue = addValue.trim();

        countRbefore = countRbefore.trim();
        ratingRnew = ratingRnew.trim();

        addValue = parseInt(addValue);

        countRbefore = parseInt(countRbefore);
        ratingRnew = parseInt(ratingRnew);


        if ($(this).hasClass('cg_manipulate_1_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_1').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR1').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR1').text(valueCountR);
            container.find('.cg_rating_value_countR1').next().next().text(valueCountR*1);
        }


        if ($(this).hasClass('cg_manipulate_2_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_2').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR2').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR2').text(valueCountR);
            container.find('.cg_rating_value_countR2').next().next().text(valueCountR*2);

        }


        if ($(this).hasClass('cg_manipulate_3_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_3').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR3').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR3').text(valueCountR);
            container.find('.cg_rating_value_countR3').next().next().text(valueCountR*3);

        }


        if ($(this).hasClass('cg_manipulate_4_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_4').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR4').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR4').text(valueCountR);
            container.find('.cg_rating_value_countR4').next().next().text(valueCountR*4);

        }


        if ($(this).hasClass('cg_manipulate_5_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_5').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR5').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR5').text(valueCountR);
            container.find('.cg_rating_value_countR5').next().next().text(valueCountR*5);

        }


        if ($(this).hasClass('cg_manipulate_6_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_6').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR6').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR6').text(valueCountR);
            container.find('.cg_rating_value_countR6').next().next().text(valueCountR*6);

        }


        if ($(this).hasClass('cg_manipulate_7_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_7').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR7').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR7').text(valueCountR);
            container.find('.cg_rating_value_countR7').next().next().text(valueCountR*7);

        }


        if ($(this).hasClass('cg_manipulate_8_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_8').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR8').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR8').text(valueCountR);
            container.find('.cg_rating_value_countR8').next().next().text(valueCountR*8);

        }


        if ($(this).hasClass('cg_manipulate_9_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_9').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR9').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR9').text(valueCountR);
            container.find('.cg_rating_value_countR9').next().next().text(valueCountR*9);

        }


        if ($(this).hasClass('cg_manipulate_10_star_number')) {

            var originCountR = container.find('.cg_value_origin_5_only_value_10').val();
            originCountR = originCountR.trim();
            originCountR = parseInt(originCountR);

            if (isNaN(originCountR)) {
                originCountR = 0;
            }

            var valueCountR = originCountR + addValue;

            if (valueCountR < 0) {

                return false;

            }

            container.find('.cg_value_origin_5_star_addCountR10').val(addValue).removeClass('cg_disabled_send');

            container.find('.cg_rating_value_countR10').text(valueCountR);
            container.find('.cg_rating_value_countR10').next().next().text(valueCountR*10);

        }

        if (addValue >= 1) {
            var addValueMultiplicated = addValue * dataStar;
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes_' + dataStar + '').text(addValueMultiplicated).removeClass('cg_hide');
        } else {
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes_' + dataStar + '').addClass('cg_hide');
        }


        var addValue = 0;

        //  console.log('ratingRnew: '+ratingRnew);

        var addCountRtotal = 0;
        container.find('.cg_stars_overview .cg_rating_value_countR_additional_votes').each(function (index) {
            addCountRtotal = addCountRtotal + parseInt($(this).text());
        });

        /*        if (addCountRtotal > 0) {
                    container.find('.cg_rating_value_countR_additional_votes_total').removeClass('cg_hide').text(addCountRtotal);
                } else {
                    container.find('.cg_rating_value_countR_additional_votes_total').removeClass('cg_hide').text(0);
                }*/

        var $cg_value_origin_5_star_to_cumulate = container.find('.cg_value_origin_5_star_to_cumulate');
        var length = $cg_value_origin_5_star_to_cumulate.length;

        $cg_value_origin_5_star_to_cumulate.each(function (index) {

            var r = index + 1;

            if ($(this).val() == '') {

                var valueToAdd = 0;

            }
            else {

                var valueToAdd = parseInt($(this).val());

            }


            if ($(this).hasClass('cg_value_origin_5_star_addCountR1')) {
                //   console.log('ratingRnew1: '+valueToAdd);

                ratingRnew = ratingRnew + valueToAdd * 1;
                addedValue = addedValue + valueToAdd * 1;
                //   console.log('ratingRnew1ratingRnew: '+ratingRnew);


            }
            if ($(this).hasClass('cg_value_origin_5_star_addCountR2')) {

                ratingRnew = ratingRnew + valueToAdd * 2;
                addedValue = addedValue + valueToAdd * 2;

            }
            if ($(this).hasClass('cg_value_origin_5_star_addCountR3')) {

                ratingRnew = ratingRnew + valueToAdd * 3;
                addedValue = addedValue + valueToAdd * 3;

            }
            if ($(this).hasClass('cg_value_origin_5_star_addCountR4')) {

                ratingRnew = ratingRnew + valueToAdd * 4;
                addedValue = addedValue + valueToAdd * 4;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR5')) {

                ratingRnew = ratingRnew + valueToAdd * 5;
                addedValue = addedValue + valueToAdd * 5;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR6')) {

                ratingRnew = ratingRnew + valueToAdd * 6;
                addedValue = addedValue + valueToAdd * 6;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR7')) {

                ratingRnew = ratingRnew + valueToAdd * 7;
                addedValue = addedValue + valueToAdd * 7;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR8')) {

                ratingRnew = ratingRnew + valueToAdd * 8;
                addedValue = addedValue + valueToAdd * 8;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR9')) {

                ratingRnew = ratingRnew + valueToAdd * 9;
                addedValue = addedValue + valueToAdd * 9;

            }

            if ($(this).hasClass('cg_value_origin_5_star_addCountR10')) {

                ratingRnew = ratingRnew + valueToAdd * 10;
                addedValue = addedValue + valueToAdd * 10;

            }


            if (valueToAdd >= 1 || valueToAdd <= 1) {
                addValue = addValue + valueToAdd;
            }

            if (r == length) {

                cgJsClassAdmin.gallery.vars.addValue = addValue;
                cgJsClassAdmin.gallery.vars.ratingRnew = ratingRnew;

                return;
            }

        });

        var countRnew = countRbefore + parseInt(cgJsClassAdmin.gallery.vars.addValue);

        $cg_backend_info_container.find('.cg_rating_value_countR_div_cummulated').text(cgJsClassAdmin.gallery.vars.ratingRnew)
        if(addedValue){
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes_total').removeClass('cg_hide').text(addedValue);
        }else{
            $cg_backend_info_container.find('.cg_rating_value_countR_additional_votes_total').addClass('cg_hide');
        }

        if (countRnew < 1) {
            cgSortableDiv.find('.cg_rating_5_star_img_div_container > .cg_backend_star.cg_backend_five_star').removeClass('cg_backend_star_on').addClass('cg_backend_star_off');
            countRnew = 0;
        } else {
            cgSortableDiv.find('.cg_rating_5_star_img_div_container > .cg_backend_star.cg_backend_five_star').removeClass('cg_backend_star_off').addClass('cg_backend_star_on');
        }

        container.find('.cg_rating_value_countR_content').text(countRnew);

    });


    $(document).on('input', '#cgGalleryBackendContainer .cg_image_title, .cg_image_description, .cg_manipulate_plus_value, .cg_manipulate_5_star_input', function () {

        if (!cgJsClassAdmin.gallery.vars.inputsChanged) {
            cgJsClassAdmin.gallery.vars.inputsChanged = true;
        }

    });

    $(document).on('click', '#cgGalleryBackendContainer .cg_manipulate_adjust_five_star', function () {

        if($(this).hasClass('cg_manipulate_container_5_stars_visible')){
            $(this).removeClass('cg_manipulate_container_5_stars_visible');
            $(this).closest('.cg_manipulate_adjust').find('.cg_manipulate_container_5_stars').addClass('cg_hide');
        }else{
            $(this).addClass('cg_manipulate_container_5_stars_visible');
            $(this).closest('.cg_manipulate_adjust').find('.cg_manipulate_container_5_stars').removeClass('cg_hide');
        }


    });

    $(document).on('change', '#cgGalleryBackendContainer .cg_category_select', function () {

        if (!cgJsClassAdmin.gallery.vars.selectChanged) {
            cgJsClassAdmin.gallery.vars.selectChanged = true;
        }

    });

    // without cgGalleryBackendContainer this one!!!! because is over!
    /* $(document).on('click', '#cgBackendBackgroundDrop.cg_active:not(.cg_high_overlay)', function (e) {

         if ($(e.target).closest('.cg_background_drop_content').length || $(e.target).is('.cg_background_drop_content')) {

         } else {

             if(!$('#cgMultipleFilesForPostContainer').hasClass('cg_hide')){
                 $('#cg_multiple_files_file_for_post_submit_button').click();
             }else{
                 $('#cgBackendBackgroundDrop, .cg_background_drop_content').addClass('cg_hide').removeClass('cg_active');
             }

         }
     });*/

    // without cgGalleryBackendContainer this one!!!! because is over!
    $(document).on('click', '#cgBackendBackgroundDrop.cg_high_overlay, .cg_backend_action_container_high_overlay .cg_message_close', function (e) {
        debugger
        if ($(e.target).closest('.cg_background_drop_content').length || $(e.target).is('.cg_background_drop_content')) {

        } else {

            $('.cg_backend_action_container_high_overlay').addClass('cg_hide');
            $('#cgBackendBackgroundDrop').removeClass('cg_high_overlay');

        }
    });


    $(document).on('click', '#cgBackendBackgroundDrop.cg_active:not(.cg_high_overlay), .cg_backend_action_container .cg_message_close,' +
        '.cg_background_drop_content .cg_message_close', function (e) {// .cg_background_drop_content  is also notification message
        debugger

        if($(this).hasClass('cg_message_close_extra_processing') || $(this).hasClass('cg_high_overlay')){
            return;
        }

        $('body').removeClass('cg_overflow_hidden');

        var $cgMultipleFilesForPostContainer = $('#cgMultipleFilesForPostContainer');
        if($cgMultipleFilesForPostContainer.is(':visible')){
            var realId = $cgMultipleFilesForPostContainer.attr('data-cg-real-id');
            cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = cgJsClassAdmin.gallery.vars.multipleFilesForPostBeforeClone[realId];
            cgJsClassAdmin.gallery.vars.$sortableDiv.replaceWith(cgJsClassAdmin.gallery.vars.$sortableDivBeforeClone);
        }

        /*if(!$cgMultipleFilesForPostContainer.hasClass('cg_hide') && cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles ){
            $('#cgAskSaveChangesForMultipleFiles').removeClass('cg_hide').addClass('cg_active');
            $('#cgBackendBackgroundDrop').addClass('cg_high_overlay').removeClass('cg_hide');
        }else*/ if($(this).closest('#cgAskIfShouldBeRemovedFromSaleIfMultiple').length){
            $('#cgBackendBackgroundDrop').removeClass('cg_high_overlay');
            $('#cgAskIfShouldBeRemovedFromSaleIfMultiple').addClass('cg_hide');
        }else{
            if(!$cgMultipleFilesForPostContainer.hasClass('cg_hide') && cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles){
                // reset then
                //cgJsClassAdmin.gallery.vars.hasChangesForMultipleFiles = false;
            }
            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        }

    });


    $(document).on('click', '#cgGalleryBackendContainer .cg_fields_div_add_fields', function (e) {
        if (cgJsClassAdmin.gallery.vars.inputsChanged || cgJsClassAdmin.gallery.vars.selectChanged) {
            e.preventDefault();
            $('#cgAddFieldsPressedAfterContentModification').removeClass('cg_hide').addClass('cg_active');
            cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        }
    });

    // cgPreviewToDelete logic

    $(document).on('click', '.cg_gallery_backend_submit', function (e) {

        var $highlightedRemoveable = $(' .cg_sortable_div.highlightedRemoveable');

        if($highlightedRemoveable.length){
            e.preventDefault();

            // body only!
            jQuery('body').addClass('cg_no_scroll');

            var $cgPreviewImagesToDeleteContainer = $('#cgPreviewImagesToDeleteContainer');
            var $cgPreviewImagesToDeleteContainerFadeBackground = $('#cgPreviewImagesToDeleteContainerFadeBackground');
            $('#cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage').addClass('cg_hide');

            var $cg_preview_files_container = $cgPreviewImagesToDeleteContainer.find('.cg_preview_files_container');
            $cg_preview_files_container.empty();

            cgJsClassAdmin.gallery.vars.hasAdditionalFiles = false;
            cgJsClassAdmin.gallery.vars.hasPotentialPdfPreviews = false;
            var hasFilesToDelete = false;

            $highlightedRemoveable.find('.cg_backend_image_full_size_target').each(function (){

                if($(this).attr('data-file-type')!='con'){
                    hasFilesToDelete = true;
                }

                var hasAdditionalFilesLoop = false;
                var $cg_backend_info_container = $(this).closest('.cg_backend_info_container');

                if(!$cg_backend_info_container.find('.cg_manage_multiple_files_for_post').hasClass('cg_hide')){
                    hasAdditionalFilesLoop = true;
                    cgJsClassAdmin.gallery.vars.hasAdditionalFiles = true;
                }

                if($(this).attr('data-file-type')=='pdf'){
                    cgJsClassAdmin.gallery.vars.hasPotentialPdfPreviews = true;
                }

                var $element = $(this).clone();

                $element.removeAttr('style');

                $element.find('a').remove();

                var $divContainer = $('<div class="cg_backend_image_full_size_target_container"></div>');
                if(hasAdditionalFilesLoop){
                    $divContainer.append(
                        '<div class="cg_manage_multiple_files_for_post_prev"></div>' +
                        '<div class="cg_manage_multiple_files_for_post">'+($cg_backend_info_container.find('.cg_manage_multiple_files_for_post').text())+'</div>'
                    );
                    console.log('test')
                    console.log($cg_backend_info_container.find('.cg_manage_multiple_files_for_post').text());
                }
                var isVideo = false;
                /*                var alternativeFileTypesArray = ['zip','pdf','txt','doc','docx','xls','xlsx','csv','mp3','m4a','ogg','wav','ppt','pptx'];
                                for(var index in alternativeFileTypesArray){
                                    if(!alternativeFileTypesArray.hasOwnProperty(index)){
                                        break;
                                    }
                                    if(alternativeFileTypesArray[index]){
                                    }
                                }*/
                var $cg_backend_image_full_size_target_alternative_file_type = $(this).find('.cg_backend_image_full_size_target_alternative_file_type');

                if($cg_backend_image_full_size_target_alternative_file_type.length){
                    var fileType = $cg_backend_image_full_size_target_alternative_file_type.attr('data-cg-file-type');
                    $element.addClass('cg_backend_image_full_size_target_container_'+fileType+' cg_backend_image_full_size_target_container_alternative_file_type');
                    $divContainer.append($('<div class="cg_backend_image_full_size_target_name"></div>'));
                    if(hasAdditionalFilesLoop){
                        var NamePic = $(this).attr('data-name-pic');
                    }else{
                        var NamePic = $cg_backend_info_container.attr('data-cg-post_name');
                    }
                    $divContainer.find('.cg_backend_image_full_size_target_name').text(NamePic);
                    $divContainer.prepend($element);
                }else if($(this).find('video').length){
                    $divContainer.addClass('cg_backend_image_full_size_target_container_video cg_backend_image_full_size_target_container_video_'+$(this).attr('data-file-type'));
                    $element.addClass('cg_backend_image_full_size_target_video');
                    $divContainer.append($('<div class="cg_video_container"><video width="160" >' +
                        '<source src="'+$(this).attr('data-original-src')+'#t=0.001" type="video/mp4"/>' +
                        '<source src="'+$(this).attr('data-original-src')+'#t=0.001" type="video/'+$(this).attr('data-file-type')+'"/>' +
                        '</video></div>'));
                    isVideo = true;
                    $divContainer.prepend($element);
                }else{
                    $divContainer.prepend($element);
                    $divContainer.find('.cg_backend_image_full_size_target').css('background',$(this).find('.cg_backend_image').css('background'));
                }

                $cg_preview_files_container.append($divContainer);

                if(isVideo){
                    $divContainer.wrap('<a href="'+$(this).attr('data-original-src')+'" target="_blank" class="cg_backend_image_full_size_target_container_href"></a>');
                }

                if($element.attr('data-file-type')=='ytb'){
                    $element.find('iframe').css({
                        'width':'100%',
                        'height':'100%',
                    });
                }

            });

            $cgPreviewImagesToDeleteContainer.removeClass('cg_hide');$cgPreviewImagesToDeleteContainerFadeBackground.removeClass('cg_hide');
            $cgPreviewImagesToDeleteContainer.addClass('cg_active');$cgPreviewImagesToDeleteContainerFadeBackground.addClass('cg_active');
            $('#cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage').addClass('cg_hide');

            $('#cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckbox').prop('checked',false);

            $("#cgPreviewImagesToDeleteContainer").animate({scrollTop: jQuery('#cgPreviewImagesToDeleteButtonContinueWithDeletingOriginalSource').position().top}, 0);

            if(hasFilesToDelete){
                $cgPreviewImagesToDeleteContainer.find('.cg_files_to_delete_available').removeClass('cg_hide');
                $cgPreviewImagesToDeleteContainer.find('.cg_files_to_delete_not_available').addClass('cg_hide');
            }else{
                $cgPreviewImagesToDeleteContainer.find('.cg_files_to_delete_available').addClass('cg_hide');
                $cgPreviewImagesToDeleteContainer.find('.cg_files_to_delete_not_available').removeClass('cg_hide');
            }

        }else{
            e.preventDefault();
            $('#cgGalleryForm').submit();
        }

    });

    var getCgPreviewToDeleteBoxes = function (){

        var cgPreviewToDeleteBoxes = $('#cgPreviewImagesToDeleteContainer, #cgPreviewImagesToDeleteContainerFadeBackground');

        return cgPreviewToDeleteBoxes;

    }

    // without cgGalleryBackendContainer this one!!!! because is over!
    $(document).on('click', '#cgPreviewImagesToDeleteContainerFadeBackground.cg_active', function (e) {
        debugger
        if ($(e.target).closest('#cgPreviewImagesToDeleteContainer').length || $(e.target).is('#cgPreviewImagesToDeleteContainer')) {

        } else {

            getCgPreviewToDeleteBoxes().addClass('cg_hide');
            getCgPreviewToDeleteBoxes().removeClass('cg_active');

        }

        jQuery('body').removeClass('cg_no_scroll');

    });

    $(document).on('click', '#cgPreviewImagesToDeleteContainer .cg_message_close', function (e) {
        debugger
        getCgPreviewToDeleteBoxes().addClass('cg_hide');
        getCgPreviewToDeleteBoxes().removeClass('cg_active');
        jQuery('body').removeClass('cg_no_scroll');

    });

    $(document).on('click', '#cgPreviewImagesToDeleteButtonGoBackToEdit', function (e) {

        getCgPreviewToDeleteBoxes().addClass('cg_hide');
        getCgPreviewToDeleteBoxes().removeClass('cg_active');
        jQuery('body').removeClass('cg_no_scroll');

    });

    $(document).on('click', '#cgPreviewImagesToDeleteButtonContinue', function (e) {

        $('#cgGalleryForm').submit();
        getCgPreviewToDeleteBoxes().addClass('cg_hide');
        getCgPreviewToDeleteBoxes().removeClass('cg_active');

    });

    $(document).on('click', '#cgPreviewImagesToDeleteButtonContinueWithDeletingOriginalSource .cg_image_action_span', function (e) {

        if(!$('#cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckbox').prop('checked')){
            e.preventDefault();
            var $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage = $('#cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage');
            $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage.removeClass('cg_hide');
            if(cgJsClassAdmin.gallery.vars.hasAdditionalFiles){
                $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage.find('.cg_note').removeClass('cg_hide');
            }else{
                $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage.find('.cg_note').addClass('cg_hide');
            }
            if(cgJsClassAdmin.gallery.vars.hasPotentialPdfPreviews){
                $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage.find('.cg_note_pdf').removeClass('cg_hide');
            }else{
                $cgPreviewImagesToDeleteOriginalSourceDeleteConfirmCheckboxMessage.find('.cg_note_pdf').addClass('cg_hide');
            }
        }else{
            var $cgGalleryForm = $('#cgGalleryForm');
            $cgGalleryForm.prepend('<input type="hidden" name="cgDeleteOriginalImageSourceAlso" id="cgDeleteOriginalImageSourceAlso" value="true">');
            $cgGalleryForm.submit();

            setTimeout(function (){
                // to go sure remove it straight away
                $('#cgDeleteOriginalImageSourceAlso').remove();
            },10);

            getCgPreviewToDeleteBoxes().addClass('cg_hide');
            getCgPreviewToDeleteBoxes().removeClass('cg_active');
        }

    });

    // cgPreviewToDelete logic --- END

    $(document).on('change', '#cgGalleryBackendContainer .cg_long_text, #cgGalleryBackendContainer .cg_short_text, #cgGalleryBackendContainer .cg_category_select', function () {
        $(this).removeClass('cg_disabled_send');
    });


    $(document).on('click', '#cgGalleryBackendContainer .cg_title_icon', function () {

        var post_title = $(this).closest('.cg_image_title_container').find('.post_title').val().trim();
        if (post_title === '' || typeof post_title == 'undefined') {
            //$(this).parent().find('.cg_image_title').addClass('cg_value_changed');
            if ($(this).closest('.cg_image_title_container').find('.cg_image_title').val() == '') {
                $(this).closest('.cg_image_title_container').find('.cg_image_title').attr('placeholder', 'No WordPress title available');
            }
        }
        else {
            cgJsClassAdmin.gallery.vars.inputsChanged = true;
            var val = $(this).closest('.cg_image_title_container').find('.cg_image_title').val().trim();
            var valToInsert;
            if(val){
                valToInsert = val+' '+post_title;
            }else{
                valToInsert = post_title;
            }
            $(this).closest('.cg_image_title_container').find('.cg_image_title').val(valToInsert).addClass('cg_value_changed');
        }

        $(this).closest('.cg_image_title_container').find('.cg_image_title').removeClass('cg_disabled_send');

    });

    $(document).on('click', '#cgGalleryBackendContainer .cg_description_icon', function () {

        var post_description = $(this).closest('.cg_image_description_container').find('.post_description').val();
        post_description = post_description.replace(/(<([^>]+)>)/ig, "");

        if (post_description === '' || typeof post_description == 'undefined') {
            //$(this).parent().parent().find('.cg_image_description').addClass('cg_value_changed');
            if ($(this).closest('.cg_image_description_container').find('.cg_image_description').val() == '') {
                $(this).closest('.cg_image_description_container').find('.cg_image_description').attr('placeholder', 'No WordPress description available').addClass('cg_value_changed');
            }
        }
        else {
            cgJsClassAdmin.gallery.vars.inputsChanged = true;

            var val = $(this).closest('.cg_image_description_container').find('.cg_image_description').val();
            var valToInsert;
            if(val){
                valToInsert = val+' '+post_description;
            }else{
                valToInsert = post_description;
            }
            $(this).closest('.cg_image_description_container').find('.cg_image_description').val(valToInsert).addClass('cg_value_changed');
        }

        $(this).closest('.cg_image_description_container').find('.cg_image_description').removeClass('cg_disabled_send');


    });


    $(document).on('click', '#cgGalleryBackendContainer .cg_excerpt_icon', function () {

        var post_excerpt = $(this).closest('.cg_image_excerpt_container').find('.post_excerpt').val();

        if (post_excerpt === '' || typeof post_excerpt == 'undefined') {
            //$(this).parent().parent().find('.cg_image_excerpt').addClass('cg_value_changed');
            if ($(this).closest('.cg_image_excerpt_container').find('.cg_image_excerpt').val() == '') {
                $(this).closest('.cg_image_excerpt_container').find('.cg_image_excerpt').attr('placeholder', 'No WordPress excerpt available');
            }
        }
        else {
            cgJsClassAdmin.gallery.vars.inputsChanged = true;

            var val = $(this).closest('.cg_image_excerpt_container').find('.cg_image_excerpt').val();
            var valToInsert;
            if(val){
                valToInsert = val+' '+post_excerpt;
            }else{
                valToInsert = post_excerpt;
            }
            $(this).closest('.cg_image_excerpt_container').find('.cg_image_excerpt').val(valToInsert).addClass('cg_value_changed');
        }

        $(this).closest('.cg_image_excerpt_container').find('.cg_image_excerpt').removeClass('cg_disabled_send');

    });


// Nicht löschen, wurde ursprünglich dazu markiert alle Felder auswählen zu lassen die im Slider gezeigt werden sollen, Logik könnte noch nützlich sein! --- ENDE


    //alert(allFieldClasses);

    function countChar(val) {
        var len = val.value.length;
        if (len >= 1000) {
            val.value = val.value.substring(0, 1000);
        } else {
            $('#charNum').text(1000 - len);
        }
    };


    $(document).on('click', '.clickMore', function () {
        // Zeigen oder Verstecken:

        $(this).next().slideDown('slow');
        $(this).next(".mehr").next(".clickBack").toggle();
        $(this).hide();


    });

    $(document).on('click', '.clickBack', function () {
        $(this).prev().slideUp('slow');
        $(this).prev(".mehr").prev(".clickMore").toggle();
        $(this).hide();


    });

    $(document).on('click', '.cg_image_checkbox_activate', function () {

        $(this).closest('.informdiv').find('.cg_image_checkbox_checkbox').prop('disabled', true);

        $('.cg_image_checkbox_deactivate_all, .cg_image_checkbox_delete_all, .cg_image_checkbox_move_all').removeClass('cg_active');
        $(this).closest('.informdiv').find('.cg_image_checkbox_deactivate, .cg_image_checkbox_delete, .cg_image_checkbox_move').removeClass('cg_active');

        cgJsClassAdmin.gallery.vars.selectChanged = true;

        $(this).closest('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        if ($(this).hasClass('cg_active')) {

            $(this).removeClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', true);

            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');

            $('.cg_image_checkbox_activate_all').removeClass('cg_active');

        } else {

            $(this).addClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', false);

            $(this).closest('.cgSortableDiv').addClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');

            if(!$('#cgSortable .cg_image_checkbox_move.cg_active').length){
                $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);
            }

        }

    });

    $(document).on('click', '.cg_image_checkbox_activate_all', function () {


        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        if (!$(this).hasClass('cg_active')) {// then activate all

            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');


            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate .cg_image_checkbox_checkbox').prop('disabled', false);

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate').addClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv.cg_sortable_div_inactive').addClass('highlightedActivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedDeactivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedRemoveable');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedMoveable');

            $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);

        } else {

            $(this).removeClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedActivate');

        }


    });

    $(document).on('click', '.cg_image_checkbox_deactivate', function () {

        $(this).closest('.informdiv').find('.cg_image_checkbox_checkbox').prop('disabled', true);

        $('.cg_image_checkbox_activate_all, .cg_image_checkbox_delete_all, .cg_image_checkbox_move_all').removeClass('cg_active');
        $(this).closest('.informdiv').find('.cg_image_checkbox_activate, .cg_image_checkbox_delete, .cg_image_checkbox_move').removeClass('cg_active');


        cgJsClassAdmin.gallery.vars.selectChanged = true;

        $(this).closest('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        if ($(this).hasClass('cg_active')) {

            $(this).removeClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', true);

            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');

            $('.cg_image_checkbox_deactivate_all').removeClass('cg_active');

        } else {

            $(this).addClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', false);

            $(this).closest('.cgSortableDiv').addClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');


            if(!$('#cgSortable .cg_image_checkbox_move.cg_active').length){
                $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);
            }

        }

    });

    $(document).on('click', '.cg_image_checkbox_deactivate_all', function () {

        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        if (!$(this).hasClass('cg_active')) {// then deactivate all

            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate .cg_image_checkbox_checkbox').prop('disabled', false);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate .cg_image_checkbox_checkbox').prop('disabled', true);

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate').addClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv.cg_sortable_div_active').addClass('highlightedDeactivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedActivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedRemoveable');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedMoveable');

            $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);

        } else {

            $(this).removeClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedDeactivate');

        }


    });

    // make winner

    $(document).on('click', '.cg_status_winner.cg_status_winner_false', function (e) {// then set to true

        e.preventDefault();
        if ($(this).hasClass('cg_active')) {
            $(this).removeClass('cg_active');
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_checkbox').prop('disabled', true);
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_visual').removeClass('cg_status_winner_true_temporary');

        } else {
            $(this).addClass('cg_active');
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_checkbox').prop('disabled', false);
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_visual').addClass('cg_status_winner_true_temporary');
        }

    });

    // make winner
    $(document).on('click', '.cg_status_winner.cg_status_winner_true', function (e) {// then set to true

        e.preventDefault();
        if ($(this).hasClass('cg_active')) {
            $(this).removeClass('cg_active');
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_checkbox').prop('disabled', true);
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_visual').removeClass('cg_status_winner_false_temporary');
        } else {
            $(this).addClass('cg_active');
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_checkbox').prop('disabled', false);
            $(this).closest('.cg_sortable_div').find('.cg_status_winner_visual').addClass('cg_status_winner_false_temporary');
        }

    });

    $(document).on('click', '.cg_image_checkbox_winner_all', function () {

        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        cgJsClassAdmin.gallery.vars.selectChanged = true;

        if (!$(this).hasClass('cg_active')) {
            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');
            //     $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true').removeClass('cg_active');
            //        $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false').addClass('cg_active');
            //    $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false .cg_status_winner_checkbox').prop('disabled', false);

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false:not(.cg_active)').each(function (){
                $(this).addClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', false);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true.cg_active').each(function (){
                $(this).removeClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', true);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual').each(function (){
                $(this).removeClass('cg_status_winner_false_temporary');
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual.cg_status_winner_false').each(function (){
                $(this).addClass('cg_status_winner_true_temporary');
            });


        } else {
            $(this).removeClass('cg_active');
            //          $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false').removeClass('cg_active');
            //        $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false .cg_status_winner_checkbox').prop('disabled', true);

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false.cg_active').each(function (){
                $(this).removeClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', true);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true:not(.cg_active)').each(function (){
                //$(this).addClass('cg_active');
                //$(this).find('.cg_status_winner_checkbox').prop('disabled', false);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual.cg_status_winner_false').each(function (){
                $(this).removeClass('cg_status_winner_true_temporary');
            });

        }

    });

    $(document).on('click', '.cg_image_checkbox_not_winner_all', function () {

        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        cgJsClassAdmin.gallery.vars.selectChanged = true;

        if (!$(this).hasClass('cg_active')) {
            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');
            //    $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true').addClass('cg_active');
            //       $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true .cg_status_winner_checkbox').prop('disabled', false);

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true:not(.cg_active)').each(function (){
                $(this).addClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', false);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false.cg_active').each(function (){
                $(this).removeClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', true);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual').each(function (){
                $(this).removeClass('cg_status_winner_true_temporary');
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual.cg_status_winner_true').each(function (){
                $(this).addClass('cg_status_winner_false_temporary');
            });

        } else {

            $(this).removeClass('cg_active');
            //      $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true').removeClass('cg_active');
            // $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true .cg_status_winner_checkbox').prop('disabled', true);

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_true.cg_active').each(function (){
                $(this).removeClass('cg_active');
                $(this).find('.cg_status_winner_checkbox').prop('disabled', true);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner.cg_status_winner_false:not(.cg_active)').each(function (){
                //$(this).addClass('cg_active');
                //$(this).find('.cg_status_winner_checkbox').prop('disabled', false);
            });

            $cgGalleryBackendContainer.find('.cg_sortable_div .cg_status_winner_visual.cg_status_winner_true').each(function (){
                $(this).removeClass('cg_status_winner_false_temporary');
            });

        }

    });

    $(document).on('click', '.cg_image_checkbox_delete', function () {

        if($(this).closest('.cgSortableDiv').find('.cg_ecommerce_entry').val()>=1){
            $('#cgEcommerceEntryCanNotBeDeleted').removeClass('cg_hide');
            cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
            return;
        }

        $(this).closest('.informdiv').find('.cg_image_checkbox_checkbox').prop('disabled', true);

        $('.cg_image_checkbox_activate_all, .cg_image_checkbox_deactivate_all, .cg_image_checkbox_move_all').removeClass('cg_active');
        $(this).closest('.informdiv').find('.cg_image_checkbox_activate, .cg_image_checkbox_deactivate, .cg_image_checkbox_move').removeClass('cg_active');

        cgJsClassAdmin.gallery.vars.selectChanged = true;

        $(this).closest('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        if ($(this).hasClass('cg_active')) {
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', true);
            $(this).removeClass('cg_active');

            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');
            $(this).closest('.cgSortableDiv').find('.ecommerceEntryAndDownload').prop('disabled',true);

            $('.cg_image_checkbox_delete_all').removeClass('cg_active');

        } else {

            $(this).addClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', false);

            $(this).closest('.cgSortableDiv').addClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');
            $(this).closest('.cgSortableDiv').find('.ecommerceEntryAndDownload').prop('disabled',false);

            if(!$('#cgSortable .cg_image_checkbox_move.cg_active').length){
                $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);
            }

        }

    });


    $(document).on('click', '.cg_image_checkbox_delete_all', function () {

        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        if (!$(this).hasClass('cg_active')) {// then delete all

            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv:not(.cg_is_for_sale)').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', false);
            $cgGalleryBackendContainer.find('.informdiv.cg_is_for_sale').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move .cg_image_checkbox_checkbox').prop('disabled', true);

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv:not(.cg_is_for_sale)').find('.cg_image_checkbox_delete').addClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv.cg_is_for_sale').find('.cg_image_checkbox_delete').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cgSortableDiv:not(.cg_is_for_sale)').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');
            $cgGalleryBackendContainer.find('.cgSortableDiv.cg_is_for_sale').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).removeClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv:not(.cg_is_for_sale)').addClass('highlightedRemoveable');
            $cgGalleryBackendContainer.find('.cgSortableDiv.cg_is_for_sale').removeClass('highlightedRemoveable');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedActivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedDeactivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedMoveable');

            $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);

            if($(this).closest('form').find('.cgSortableDiv').hasClass('cg_is_for_sale')){
                $('#cgEcommerceEntryCanNotBeDeleted').removeClass('cg_hide');
                cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
            }

        } else {

            $(this).removeClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedRemoveable');

        }

    });

    $(document).on('click', '.cg_image_checkbox_move', function () {

        $(this).closest('.informdiv').find('.cg_image_checkbox_checkbox').prop('disabled', true);

        $('.cg_image_checkbox_activate_all, .cg_image_checkbox_deactivate_all, .cg_image_checkbox_delete_all').removeClass('cg_active');
        $(this).closest('.informdiv').find('.cg_image_checkbox_activate, .cg_image_checkbox_deactivate, .cg_image_checkbox_delete').removeClass('cg_active');

        cgJsClassAdmin.gallery.vars.selectChanged = true;

        $(this).closest('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

        if ($(this).hasClass('cg_active')) {// then do not move
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', true);
            $(this).removeClass('cg_active');

            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').removeClass('highlightedMoveable');

            $('.cg_image_checkbox_move_all').removeClass('cg_active');

            if(!$('#cgSortable .cg_image_checkbox_move.cg_active').length){
                $('#cgMoveSelect').addClass('cg_hide').find('select').prop('disabled',true);
            }

        } else {

            $(this).addClass('cg_active');
            $(this).find('.cg_image_checkbox_checkbox').prop('disabled', false);

            $(this).closest('.cgSortableDiv').removeClass('highlightedActivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedDeactivate');
            $(this).closest('.cgSortableDiv').removeClass('highlightedRemoveable');
            $(this).closest('.cgSortableDiv').addClass('highlightedMoveable');

            $('#cgMoveSelect').removeClass('cg_hide').find('select').prop('disabled',false);

        }

    });


    $(document).on('click', '.cg_image_checkbox_move_all', function () {

        var $cgGalleryBackendContainer = $('#cgGalleryBackendContainer');

        if (!$(this).hasClass('cg_active')) {// then move all

            $(this).parent().find('.cg_image_checkbox').removeClass('cg_active');
            $(this).addClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move .cg_image_checkbox_checkbox').prop('disabled', false);

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_deactivate').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_activate').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_delete').removeClass('cg_active');
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move').addClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');

            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedActivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedDeactivate');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedRemoveable');
            $cgGalleryBackendContainer.find('.cgSortableDiv').addClass('highlightedMoveable');

            $('#cgMoveSelect').removeClass('cg_hide').find('select').prop('disabled',false);

        } else {

            $(this).removeClass('cg_active');

            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move .cg_image_checkbox_checkbox').prop('disabled', true);
            $cgGalleryBackendContainer.find('.informdiv').find('.cg_image_checkbox_move').removeClass('cg_active');

            cgJsClassAdmin.gallery.vars.selectChanged = true;

            $cgGalleryBackendContainer.find('.cg_sortable_div').find(cgJsClassAdmin.gallery.vars.cgChangedValueSelectorInTargetedSortableDiv).addClass('cg_value_changed');
            $cgGalleryBackendContainer.find('.cgSortableDiv').removeClass('highlightedMoveable');

        }

    });


// Duplicate email to a hidden field for form


    $(document).on('change', '.email', function () {

        var email = $(this).val();
        $(this).closest('.cg_sortable_div').find(".email-clone").val(email);

    });

// Duplicate email to a hidden field for form -- END 


    $(document).on('click', 'div input #activate', function () {
        $("input #inform").prop("disabled", this.checked);
    });

    /*function informAll(){

    //alert(arg);
    alert(arg1);

    if($("#informAll").is( ":checked" )){
    $( "input[class*=inform]").removeAttr("checked",true);
    $( "input[class*=inform]").click();
    }

    else{
    $( "input[class*=inform]").click();

    }

    }*/


// show exif data

    /*    $(document).on('click','.cg-exif-container button',function () {

            $(this).closest('.cg-exif-container').find('.cg-exif-append').show();

        });*/


// show exif data --- ENDE

    $(document).on('click', '.cg_category_checkbox_images_area input[type="checkbox"]', function () {

        var $element = $(this);

        //   setTimeout(function () {
        if (!$element.prop('checked') == true) {
            $element.addClass('cg_checked');
        } else {
            $element.removeClass('cg_checked');
        }
        //    },1000);
    });

    // save category changes
    $(document).on('click', '.cg_save_categories_form', function () {

        if(!$(this).hasClass('cg_save_categories_form_continue_saving') &&
            $('#cgCategoryTotalActiveImagesValue').text().trim()==0 &&
            jQuery('#cgCategoriesCheckContainer .cg-categories-check').length != jQuery('#cgCategoriesCheckContainer .cg-categories-check:checked').length &&
            jQuery('#cgActivatedImagesCount').val() >= 1
        ){
            $('#cgTotalActivatedImagesShownInFrontendZero').removeClass('cg_hide');
            cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
            return;
        }

        if($(this).hasClass('cg_save_categories_form_continue_saving')){
            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        }

        var form = document.getElementById('cgCategoriesForm');
        var formPostData = new FormData(form);

        $('#cgSaveCategoriesLoader').removeClass('cg_hide');

        setTimeout(function () {

            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: formPostData,
                dataType: null,
                contentType: false,
                processData: false
            }).done(function (response) {
                $('#cgSaveCategoriesLoader').addClass('cg_hide');
                $("#cg_changes_saved_categories").show().fadeOut(4000);

                var totalVisibleActivatedImagesCount = cgJsClassAdmin.gallery.functions.countTotalVisibleActivatedImagesCountForCategories();

                if(totalVisibleActivatedImagesCount==0 && $('#cgSortable .cg_status_activated').length){
                    $('#cgUncheckedCategoriesMessageAboveGallery').removeClass('cg_hide');
                }else{
                    $('#cgUncheckedCategoriesMessageAboveGallery').addClass('cg_hide');
                }

                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Categories changes saved',true);

            }).fail(function (xhr, status, error) {

                $('#cgSaveCategoriesLoader').addClass('cg_hide');
                var test = 1;

            }).always(function () {

                var test = 1;

            });

        }, 1000);

    });

    // check active images count by categories

    $(document).on('click', '.cg-categories-check', function () {

        var totalVisibleActivatedImagesCount = cgJsClassAdmin.gallery.functions.countTotalVisibleActivatedImagesCountForCategories();

        if(totalVisibleActivatedImagesCount==0){
            $('#cgCategoryTotalActiveImagesValue').text(totalVisibleActivatedImagesCount).css('color','red');
        }else{
            $('#cgCategoryTotalActiveImagesValue').text(totalVisibleActivatedImagesCount).css('color','green');
        }

    });

    // sort gallery files
    $(document).on('click', '#cg_sort_files_form_button,#cg_sort_files_form_submit_button', function (e) {

        e.preventDefault();
        var $button = $(this);
        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var $cgSortGalleryFilesContainer = $('#cgSortGalleryFilesContainer');
        $cgSortGalleryFilesContainer.removeClass('cg_hide').find('.cg-lds-dual-ring-gallery-hide').removeClass('cg_hide');
        $cgSortGalleryFilesContainer.find('#cg_sort_files_form_submit_button_container').addClass('cg_hide');

        var form = document.getElementById('cg_sort_files_form');
        var formPostData = new FormData(form);

        if($button.attr('id')=='cg_sort_files_form_submit_button'){
            $cgSortGalleryFilesContainer.find('.cg_position').each(function (){
                //console.log($(this).attr('data-cg-real-id'));
                //console.log($(this).val());
                formPostData.append('cg_position['+$(this).attr('data-cg-real-id')+']', $(this).val());
            });
        }

        $cgSortGalleryFilesContainer.find('.cg_preview_files_container').remove();
        var $cg_sort_files_form = $(form);

        setTimeout(function () {

            $.ajax({
                url: 'admin-ajax.php',
                method: 'post',
                data: formPostData,
                dataType: null,
                contentType: false,
                processData: false
            }).done(function (response) {
                console.log('response success');
                //console.log(response);
                if($button.attr('id')=='cg_sort_files_form_button'){
                    var htmlDom = new DOMParser().parseFromString(response, 'text/html');
                    var $cgSortGalleryFilesContent = $(htmlDom.getElementById('cgSortGalleryFilesContent'));
                    $cg_sort_files_form.append($cgSortGalleryFilesContent.html());
                    $cgSortGalleryFilesContainer.find('.cg-lds-dual-ring-gallery-hide').addClass('cg_hide');
                    $cg_sort_files_form.removeClass('cg_hide');
                    $cgSortGalleryFilesContainer.find('#cg_sort_files_form_submit_button_container').removeClass('cg_hide');

                    var heightToSubstract =
                        parseInt($cgSortGalleryFilesContainer.css('padding-top')) +
                        parseInt($cgSortGalleryFilesContainer.css('padding-bottom')) +
                        parseInt($cgSortGalleryFilesContainer.find('#cgSortGalleryFilesExplanation').outerHeight(true)) +
                        parseInt($cgSortGalleryFilesContainer.find('#cg_sort_files_form_submit_button_container').outerHeight(true));

                    var maxHeightInPercent = parseInt($cgSortGalleryFilesContainer.css('max-height'));
                    var windowHeight = $(window).height();
                    var heightTotal = windowHeight/100*maxHeightInPercent;
                    var heightPreviewFilesContainer = heightTotal - heightToSubstract;
                    $cgSortGalleryFilesContainer.find('.cg_preview_files_container').css('max-height',heightPreviewFilesContainer+'px');

                    $cgSortGalleryFilesContainer.find('#cg_sort_files_form_submit_button_container').removeClass('cg_hide');

                    $cg_sort_files_form.find(".cg_preview_files_container").sortable({
                        /*                        items: ".cg_backend_image_full_size_target_container:not(.cg_backend_image_add_files_label)",
                                                handle: ".cg_backend_image_full_size_target_container_drag",
                                                cursor: "move",
                                                placeholder: "ui-state-highlight",*/
                        start: function (event, ui) {
                            var $element = $(ui.item);
                            $cgSortGalleryFilesContainer.find('.ui-state-highlight').addClass($element.get(0).classList.value).html($element.html());
                        },
                        stop: function () {
                            $cgSortGalleryFilesContainer.find('.cg_position').each(function (index){
                                $(this).val(index+1);
                            });
                        }
                    });
                }else{
                    var $cgOrderSelect = $('#cgOrderSelect');
                    $cgOrderSelect.find('option').removeAttr('selected');
                    var $selected = $cgOrderSelect.find('#cg_custom');
                    $selected.attr('selected','selected');
                    $('#cgOrderValue').val('custom');
                    $('#cgSearchInput').val('').removeClass('cg_searched_value');
                    $('#cgSearchInputButton').addClass('cg_hide');
                    $('#cgSearchInputClose').addClass('cg_hide');
                    cgJsClassAdmin.gallery.load.changeViewByControl(jQuery, null, null, null, false,false,$selected,true);
                }

            }).fail(function (xhr, status, error) {
                console.log('response cg_sort_files_form_button');
                console.log(xhr);
                console.log(status);
                console.log(error);
            }).always(function () {

                var test = 1;

            });

        }, 1000);

    });

    // attach to another user open select
    $(document).on('click', '#cgSortable .cg_attach_to_another_user', function (e) {
        debugger
        e.preventDefault();
        var cgEntryId = $(this).closest('.cg_backend_info_container').attr('data-cg-real-id');
        var $cg_backend_info_user_link_container = $(this).closest('.cg_backend_info_user_link_container');
        cgJsClassAdmin.gallery.vars.$cg_backend_info_user_link_container = $cg_backend_info_user_link_container;
        var gid = $('#cgBackendGalleryId').val();
        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        var $cgAttachToAnotherUserContainer = $('#cgAttachToAnotherUserContainer');
        $cgAttachToAnotherUserContainer.removeClass('cg_hide').find('.cg-lds-dual-ring-gallery-hide').removeClass('cg_hide');
        $cgAttachToAnotherUserContainer.find('#cgAttachToAnotherUserSubmitButtonContainer').addClass('cg_hide');
        $cgAttachToAnotherUserContainer.find('#cgAttachToAnotherUserExplanationEntryId').text(cgEntryId);

        var form = document.getElementById('cgAttachToAnotherUserForm');
        var $cgAttachToAnotherUserForm = $(form);
        $cgAttachToAnotherUserForm.find('input[name="action"]').val('post_cg_attach_to_another_user_select');
        var formPostData = new FormData(form);
        var cg_nonce = CG1LBackendNonce.nonce;

        $cgAttachToAnotherUserForm.find('input[name="cgEntryId"]').val(cgEntryId);
        $cgAttachToAnotherUserForm.addClass('cg_hide');

        if($cg_backend_info_user_link_container.find('.cg_added_by_username').length){
            var current_user_login = $cg_backend_info_user_link_container.find('.cg_added_by_username').text();
            $cgAttachToAnotherUserForm.find('#cgAttachToAnotherUserDetachButton').text('Detach from '+current_user_login).removeClass('cg_hide');
        }else{
            $cgAttachToAnotherUserForm.find('#cgAttachToAnotherUserDetachButton').addClass('cg_hide');
        }

        if($cgAttachToAnotherUserForm.find('#cgAttachToAnotherUserSelect').length){
            $cgAttachToAnotherUserContainer.find('.cg-lds-dual-ring-gallery-hide').addClass('cg_hide');
            $cgAttachToAnotherUserContainer.find('#cgAttachToAnotherUserSubmitButtonContainer').removeClass('cg_hide');
            $cgAttachToAnotherUserForm.removeClass('cg_hide');
            return;
        }

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function (response) {
            console.log('response success');
            console.log(response);
            var htmlDom = new DOMParser().parseFromString(response, 'text/html');
            var $cgAttachToAnotherUserSelect = $(htmlDom.getElementById('cgAttachToAnotherUserSelect'));
            $cgAttachToAnotherUserForm.prepend($cgAttachToAnotherUserSelect);
            $cgAttachToAnotherUserForm.removeClass('cg_hide');
            $cgAttachToAnotherUserContainer.find('.cg-lds-dual-ring-gallery-hide').addClass('cg_hide');
            $cgAttachToAnotherUserContainer.find('#cgAttachToAnotherUserSubmitButtonContainer').removeClass('cg_hide');
        }).fail(function (xhr, status, error) {
            console.log('response error cg_attach_to_another_user');
            console.log(xhr);
            console.log(status);
            console.log(error);
        }).always(function () {
            var test = 1;
        });
    });

    // attach to another user

    $(document).on('click','#cgAttachToAnotherUserSubmitButton,#cgAttachToAnotherUserDetachButton',function (e){
        debugger
        e.preventDefault();
        var $el = $(this);
        var $cgAttachToAnotherUserForm = $el.closest('form');
        $cgAttachToAnotherUserForm.find('input[name="action"]').val('post_cg_attach_to_another_user');
        var $cgAttachToAnotherUserContainer = $(this).closest('#cgAttachToAnotherUserContainer');
        var $cg_backend_info_user_link_container = cgJsClassAdmin.gallery.vars.$cg_backend_info_user_link_container;
        var formPostData = new FormData($cgAttachToAnotherUserForm.get(0));
        $cgAttachToAnotherUserContainer.find('.cg-lds-dual-ring-gallery-hide').removeClass('cg_hide');
        $cgAttachToAnotherUserForm.addClass('cg_hide');
        var $option = $cgAttachToAnotherUserForm.find('select option:selected');
        var WpUserId = $option.val();
        var text = $option.text();
        var user_login = $option.attr('data-user_login');
        var user_email = $option.attr('data-user_email');
        var cg_nonce = CG1LBackendNonce.nonce;
        var gid = $('#cgBackendGalleryId').val();

        if($el.attr('id')=='cgAttachToAnotherUserDetachButton'){
            formPostData.append('cgAttachToAnotherUserId',0);
        }

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function (response) {

            $cgAttachToAnotherUserContainer.find('.cg-lds-dual-ring-gallery-hide').addClass('cg_hide');
            $cgAttachToAnotherUserContainer.addClass('cg_hide');
            cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();

            if($el.attr('id')=='cgAttachToAnotherUserDetachButton'){
                text = $cg_backend_info_user_link_container.find('.cg_added_by_username').text();
                $cg_backend_info_user_link_container.find('.cg_added_by, .cg_image_action_href.cg_load_backend_link, .cg_attach_to_another_user').remove();
                var $cg_attach_to_another_user = $('<div class="cg_action_button cg_attach_to_another_user ">Attach to registered user</div>');
                $cg_backend_info_user_link_container.prepend($cg_attach_to_another_user);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Entry dettached from <br><b style="color: black !important;">'+text+'</b>',true);
            }else{
                var $cg_added_by = $('<span class="cg_added_by">Added by (username)</span>');
                var $cg_action_href = $('<a class="cg_image_action_href cg_load_backend_link" title="'+user_email+'" href="?page=contest-gallery-pro/index.php&users_management=true&option_id='+gid+'&wp_user_id='+WpUserId+'&cg_nonce='+cg_nonce+'">'+
                    '<div class="cg_image_action_span cg_for_id_wp_username_by_search_sort cg_added_by_username">'+user_login+'</div></a>');
                var $cg_attach_to_another_user = $('<div class="cg_action_button cg_attach_to_another_user">Attach to another user<br>or detach<br>from user</div>');
                $cg_backend_info_user_link_container.find('.cg_added_by, .cg_image_action_href.cg_load_backend_link, .cg_attach_to_another_user').remove();
                $cg_backend_info_user_link_container.prepend($cg_attach_to_another_user);
                $cg_backend_info_user_link_container.prepend($cg_action_href);
                $cg_backend_info_user_link_container.prepend($cg_added_by);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Entry attached to <br><b style="color: black !important;">'+text+'</b>',true);
            }


        }).fail(function (xhr, status, error) {
            console.log('response error cgAttachToAnotherUserForm');
            console.log(xhr);
            console.log(status);
            console.log(error);
        }).always(function () {

        });

    });

    // init date time fields

    $(document).on('keydown', '.cg_input_date_class', function (e) {

        e.preventDefault();

        if (e.which == 46 || e.which == 8) {// back, delete
            this.value = '';
        }

    });



    // rotate events
    $(document).on('click','#cgRotateSource',function () {
        if($('#cgImgThumbContainerMain').length){
            //   cgSameHeightDivImage();
            if(!$('#cgImgSource').hasClass('cg90degree') && !$('#cgImgSource').hasClass('cg180degree') && !$('#cgImgSource').hasClass('cg270degree')){
                $('#cgImgSource').addClass('cg90degree');
                $('#rSource').val(90);
            }
            else if($('#cgImgSource').hasClass('cg90degree')){
                $('#cgImgSource').removeClass('cg90degree');
                $('#cgImgSource').addClass('cg180degree');
                $('#rSource').val(180);
            }
            else if($('#cgImgSource').hasClass('cg180degree')){
                $('#cgImgSource').removeClass('cg180degree');
                $('#cgImgSource').addClass('cg270degree');
                $('#rSource').val(270);
            }
            else if($('#cgImgSource').hasClass('cg270degree')){
                $('#cgImgSource').removeClass('cg270degree');
                $('#rSource').val(0);
            }
        }
    });

    $(document).on('click','#cgResetSource',function () {
        if($('#cgImgThumbContainerMain').length){
            cgJsClassAdmin.gallery.functions.cgRotateSameHeightDivImage($);
            $('#cgImgSource').removeClass('cg90degree');
            $('#cgImgSource').removeClass('cg180degree');
            $('#cgImgSource').removeClass('cg270degree');
            $('#rSource').val(0);
        }
    });

    $(document).on('click','#cgRotateThumb',function () {
        if($('#cgImgThumbContainerMain').length){
            if(!$('#cgImgThumb').hasClass('cg90degree') && !$('#cgImgThumb').hasClass('cg180degree') && !$('#cgImgThumb').hasClass('cg270degree')){
                $('#cgImgThumb').addClass('cg90degree');
                $('#rThumb').val(90);
            }
            else if($('#cgImgThumb').hasClass('cg90degree')){
                $('#cgImgThumb').removeClass('cg90degree');
                $('#cgImgThumb').addClass('cg180degree');
                $('#rThumb').val(180);
            }
            else if($('#cgImgThumb').hasClass('cg180degree')){
                $('#cgImgThumb').removeClass('cg180degree');
                $('#cgImgThumb').addClass('cg270degree');
                $('#rThumb').val(270);
            }
            else if($('#cgImgThumb').hasClass('cg270degree')){
                $('#cgImgThumb').removeClass('cg270degree');
                $('#rThumb').val(0);
            }
        }
    });

    $(document).on('click','#cgResetThumb',function () {
        if($('#cgImgThumbContainerMain').length){
            $('#cgImgThumb').removeClass('cg90degree');
            $('#cgImgThumb').removeClass('cg180degree');
            $('#cgImgThumb').removeClass('cg270degree');
            $('#rThumb').val(0);
        }
    });

    // rotate events end

    $(document).on('click','.media-menu-item',function () {
        // have to be done because of possible wordpress media library bug
        setTimeout(function (){
            $('.button.load-more').removeClass('hidden');
            $('.load-more-jump').removeClass('hidden');
        },1000);
    });

    $(document).on('click','#cgSortable .cg_rotate_image_backend',function () {
        var $cg_backend_info_container = $(this).closest('.cg_backend_info_container');
        var realId = $cg_backend_info_container.attr('data-cg-real-id');
        var $cg_backend_image = $cg_backend_info_container.find('.cg_backend_image');
        if(!$(this).attr('data-cg-rThumb') || $(this).attr('data-cg-rThumb')==0){
            $cg_backend_image.removeClass('cg180degree  cg270degree').addClass('cg90degree');
            $(this).attr('data-cg-rThumb',90);
        } else if($(this).attr('data-cg-rThumb')==90){
            $cg_backend_image.removeClass('cg90degree  cg270degree').addClass('cg180degree');
            $(this).attr('data-cg-rThumb',180);
        } else if($(this).attr('data-cg-rThumb')==180){
            $cg_backend_image.removeClass('cg90degree  cg180degree').addClass('cg270degree');
            $(this).attr('data-cg-rThumb',270);
        } else if($(this).attr('data-cg-rThumb')==270){
            $cg_backend_image.removeClass('cg90degree cg180degree cg270degree');
            $(this).attr('data-cg-rThumb',0);
        }

        $cg_backend_info_container.find('.cg_rotate_info').removeClass('cg_hide');

        var cg_multiple_files_for_post = $cg_backend_info_container.find('.cg_multiple_files_for_post').val();

        if(cg_multiple_files_for_post){
            if(!cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId]){
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId] = JSON.parse(cg_multiple_files_for_post);
            }
            if(cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].isRealIdSource){
                $cg_backend_info_container.find('.cg_rThumb').val($(this).attr('data-cg-rThumb')).removeClass('cg_disabled_send');
            }else{
                cgJsClassAdmin.gallery.vars.multipleFilesForPost[realId][1].rThumb = $(this).attr('data-cg-rThumb');
                cgJsClassAdmin.gallery.functions.setSimpleDataRealIdSource(realId,$cg_backend_info_container);
            }
        }else{
            $cg_backend_info_container.find('.cg_rThumb').val($(this).attr('data-cg-rThumb')).removeClass('cg_disabled_send');
        }
    });

    $( window ).scroll(function() {

        var windowScrollTop = $(window).scrollTop();
        var $cgSortable = cgJsClassAdmin.gallery.vars.$cgSortable;

        if(cgJsClassAdmin.index.vars.isGalleryAreaLoaded && $cgSortable && $cgSortable.length){
            var offsetTop = cgJsClassAdmin.gallery.vars.$cgSortable.offset().top;
            var $cgGallerySubmitTop = cgJsClassAdmin.gallery.vars.$cgGallerySubmitTop;
            if(cgJsClassAdmin.options.vars.$wpadminbar.css('position')=='fixed'){
                var height = cgJsClassAdmin.options.vars.$wpadminbar.height();
            }else{
                var height = 0;
            }
            if(windowScrollTop>offsetTop){
                $cgGallerySubmitTop.width($cgSortable.width()+20);// +20 because of padding
                $cgGallerySubmitTop.addClass('cg_sticky').css('top',height+'px');
            }else{
                $cgGallerySubmitTop.css('width','');
                $cgGallerySubmitTop.removeClass('cg_sticky').css('top','');
            }
        }

    });

    $(document).on('click','.cg_move_to_another_gallery',function (e) {
        e.preventDefault();

        if($(this).closest('.cgSortableDiv').find('.cg_ecommerce_entry').val()>=1){
            $('#cgEcommerceEntryCanNotBeMoved').removeClass('cg_hide');
            cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
            return;
        }

        var $element = $(this);
        var $cgSortableDiv  = $element.closest('.cgSortableDiv');
        cgJsClassAdmin.gallery.vars.$cgSortableDiv = $cgSortableDiv;

        $('#cgMoveToAnotherGalleryContainer').removeClass('cg_hide');
        $('#cgBackendBackgroundDrop').removeClass('cg_hide');
        $('body').addClass('cg_overflow_hidden');
        $('#cgMoveToAnotherGalleryLoader').removeClass('cg_hide');
        $('#cgMoveToAnotherGalleryContent').addClass('cg_hide');
        if(!cgJsClassAdmin.gallery.vars.contact_forms_by_gallery_id){
            var data = {};
            data['action'] = 'post_cg_move_to_another_gallery_get_inputs';

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

                cgJsClassAdmin.gallery.functions.showMoveToGalleryContent($,$element);

            }).fail(function (xhr, status, error) {
                debugger
                console.log('response error');
                console.log(xhr);
                console.log('status error');
                console.log(status);
                console.log('error error');
                console.log(error);

            }).always(function () {

                var test = 1;

            });

        }else{
            cgJsClassAdmin.gallery.functions.showMoveToGalleryContent($,$element);
        }

    });


    $(document).on('click','#cgMoveToAnotherGallerySubmit',function (e) {
        e.preventDefault();
        debugger
        var $element = $(this);
        $('#cgMoveToAnotherGalleryContainer').removeClass('cg_hide');
        $('#cgBackendBackgroundDrop').removeClass('cg_hide');
        $('body').addClass('cg_overflow_hidden');
        $('#cgMoveToAnotherGalleryLoader').removeClass('cg_hide');
        $('#cgMoveToAnotherGalleryContent').addClass('cg_hide');

        var cg_in_gallery_id_to_move = $('#cg_in_gallery_id_to_move_select').val();

        var data = {};
        data['action'] = 'post_cg_move_to_another_gallery';
        data['cg_in_gallery_id_to_move'] = cg_in_gallery_id_to_move;
        data['cgMoveRealId'] = $('#cgMoveEntryId').text();
        data['cgMoveFromGalleryID'] = $('#cg_gallery_id').val();
        data['cgMoveCategory'] = 0;
        data['cgMoveAssigns'] = null;
        if($('#cgMoveToAnotherGalleryCompare .cg_action_select').length){
            $('#cgMoveToAnotherGalleryCompare').find('> div').each(function (){
                if($(this).find('> .cg_action_select').val() && !$(this).find('> .cg_action_select_category').length){
                    if(!data['cgMoveAssigns']){// have to be added that way, most secure way for all cases
                        data['cgMoveAssigns'] = {};
                    }
                    data['cgMoveAssigns'][$(this).find('> span').attr('data-cg-from')] = $(this).find('> .cg_action_select').val();
                }else if($(this).find('> .cg_action_select').val() && $(this).find('> .cg_action_select_category').length){
                    data['cgMoveCategory'] = $(this).find('> .cg_action_select').val();
                }
            });
        }
        debugger
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: data
        }).done(function (response) {
            debugger
            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));
            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });
            $('body').removeClass('cg_overflow_hidden');

            if($('#cg_in_gallery_id_to_move_go_checkbox').prop('checked')){
                var url = $('#cgMoveUrlAfterMove').attr('href')+'&option_id='+cg_in_gallery_id_to_move;
                debugger
                location.href = url;
            }else{
                cgJsClassAdmin.gallery.vars.$cgSortableDiv.remove();
                if(!$('#cgSortable .cg_sortable_div').length){
                    $('#cgGallerySubmitTop').addClass('cg_hide');
                    $('#cgGallerySubmit').addClass('cg_hide');
                    $('#cgNoImagesFound').removeClass('cg_hide');
                }
                $('#cgMoveToAnotherGalleryContainer').addClass('cg_hide');
                $('#cgBackendBackgroundDrop').addClass('cg_hide');
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Moved to gallery '+cg_in_gallery_id_to_move,true);
            }

        }).fail(function (xhr, status, error) {
            debugger
            console.log('response error');
            console.log(xhr);
            console.log('status error');
            console.log(status);
            console.log('error error');
            console.log(error);
            $('body').removeClass('cg_overflow_hidden');

            $('#cgMoveToAnotherGalleryContainer').addClass('cg_hide');
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(error,true);

        }).always(function () {

            var test = 1;

        });

    });

    var previousValue = '';

    $(document).on('focus','#cg_in_gallery_id_to_move_select',function (e) {
        previousValue = $(this).val();
    });

    $(document).on('change','#cg_in_gallery_id_to_move_select',function (e) {
        var $cgMoveToAnotherGalleryCompare = $('#cgMoveToAnotherGalleryCompare');
        $cgMoveToAnotherGalleryCompare.empty();
        var gid = $('#cg_gallery_id').val();
        var value = $(this).val();
        cgJsClassAdmin.gallery.vars.moveSelected = value;
        cgJsClassAdmin.gallery.functions.createMoveSelectAssignArea($,$cgMoveToAnotherGalleryCompare,gid,$(this).val());
        if(previousValue!=''){
            var type = $(this).closest('.cg_move_type').find('data-cg-type');
            $(this).closest('#cgMoveToAnotherGalleryCompare').find('.cg_move_type[data-cg-type="'+type+'"] .cg_action_select').each(function (){
                $(this).find('option[value="'+previousValue+'"]').removeAttr('disabled')
            });
        }
        if(value!=''){
            var type = $(this).closest('.cg_move_type').find('data-cg-type');
            $(this).closest('#cgMoveToAnotherGalleryCompare').find('.cg_move_type[data-cg-type="'+type+'"] .cg_action_select').each(function (){
                $(this).find('option[value="'+previousValue+'"]').attr('disabled','disabled')
            });
        }
    });

    $(document).on('change','#cgMoveToAnotherGalleryCompare .cg_action_select',function (e) {

        var gallery_id_to_move = $('#cg_in_gallery_id_to_move_select').val();

        if(!cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[gallery_id_to_move]){
            cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[gallery_id_to_move] = {};
        }

        var field_id_to_move = $(this).closest('.cg_move_type').find('.cg_move_field_id').attr('data-cg-from');

        console.log('field_id_to_move')
        console.log(field_id_to_move)

        if($(this).val()){
            cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[gallery_id_to_move][field_id_to_move] = $(this).val();
        }else{
            delete cgJsClassAdmin.gallery.vars.moveContactFieldsAssigns[gallery_id_to_move][field_id_to_move];
        }

    });

    $(document).on('change','#cgCommentsDeactivateAll',function (e) {
        var $cgShowComments = $(this).closest('#cgShowComments');
        $cgShowComments.find('#cgCommentsActivateAll').prop('checked',false);
        $cgShowComments.find('#cgCommentsDeleteAll').prop('checked',false);
        if($(this).prop('checked')){
            $cgShowComments.find('.cg_comment_activate').prop('checked',false);
            $cgShowComments.find('.cg_comment_delete').prop('checked',false);
            $cgShowComments.find('.cg_comment_deactivate:not(:disabled)').prop('checked',true);
        }else{
            $cgShowComments.find('.cg_comment_deactivate:not(:disabled)').prop('checked',false);
        }
    });

    $(document).on('change','#cgCommentsActivateAll',function (e) {
        var $cgShowComments = $(this).closest('#cgShowComments');
        $cgShowComments.find('#cgCommentsDeactivateAll').prop('checked',false);
        $cgShowComments.find('#cgCommentsDeleteAll').prop('checked',false);
        if($(this).prop('checked')){
            $cgShowComments.find('.cg_comment_deactivate').prop('checked',false);
            $cgShowComments.find('.cg_comment_delete').prop('checked',false);
            $cgShowComments.find('.cg_comment_activate:not(:disabled)').prop('checked',true);
        }else{
            $cgShowComments.find('.cg_comment_activate:not(:disabled)').prop('checked',false);
        }
    });

    $(document).on('change','#cgCommentsDeleteAll',function (e) {
        var $cgShowComments = $(this).closest('#cgShowComments');
        $cgShowComments.find('#cgCommentsActivateAll').prop('checked',false);
        $cgShowComments.find('#cgCommentsDeactivateAll').prop('checked',false);
        if($(this).prop('checked')){
            $cgShowComments.find('.cg_comment_activate').prop('checked',false);
            $cgShowComments.find('.cg_comment_deactivate').prop('checked',false);
            $cgShowComments.find('.cg_comment_delete:not(:disabled)').prop('checked',true);
        }else{
            $cgShowComments.find('.cg_comment_delete:not(:disabled)').prop('checked',false);
        }
    });

});