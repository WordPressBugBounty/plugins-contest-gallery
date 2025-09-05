cgJsClassAdmin.createUpload.vars = {
    isChecked: 0,
    cgRecaptchaIconUrl: null,
    cgDragIcon: null,
    countChildren: 0
};
cgJsClassAdmin.createUpload.functions = {
    load: function ($, $formLinkObject, $response) {

        cgJsClassAdmin.index.vars.isCreateUploadAreaLoaded = true;

        cgJsClassAdmin.createUpload.vars.cgRecaptchaIconUrl = $("#cgRecaptchaIconUrl").val();

        cgJsClassAdmin.createUpload.vars.cgDragIcon = $("#cgDragIcon").val();

        cgJsClassAdmin.index.functions.setEditors($, $response.find('#ausgabe1.cg_create_upload .cg-wp-editor-template'));

        $('#cgSelectFileTypeRealContainer').append($('#cgSelectFileType'));

        $('#ausgabe1.cg_create_upload .switch-tmce:visible').click();// !IMPORTANT: click only unvisible otherwise breaks functionality of further elements

        $("#ausgabe1.cg_create_upload .cg_sortable_area").sortable({
            placeholder: "ui-state-highlight",
            handle: ".cg_drag_area",
            cursor: "move",
            htmlClone: null,
            delay: 0,
            start: function (event, ui) {
                var $element = ui.item;

                $element.css('height','unset');
                $element.find('.cg_view_options_row:not(.cg_view_options_row_title)').addClass('cg_hide');
                $element.find('.cg_view_options_row_title').addClass('cg_border_bottom_thin_solid_default_color');
                $element.closest('.formField').find('.cg_view_options_row_marker').removeClass('cg_hide').find('.cg_view_options_row_marker_content').text($element.closest('.formField').find('.cg_view_option_input_field_title').val());
                $element.css('height',$element.height()+'px');
                // condition for html fields. Deactivate by start first and reinitinalize by stop again later
                var $cgWpEditorContainer = $element.find('.cg-wp-editor-container');
                if ($cgWpEditorContainer.length) {

                    if(cgJsClassAdmin.index.vars.wpVersion>=cgJsClassAdmin.index.vars.wpVersionForTinyMCE){
                        tinymce.EditorManager.execCommand('mceRemoveEditor', true, $cgWpEditorContainer.attr('data-wp-editor-id'));
                    }

                }
                // condition for html fields. Deactivate by start first and reinitinalize by stop again later --- END
                var $placeholder =  ui.placeholder;
                $placeholder.height($element.height()).addClass($element.get(0).classList.value).html($element.html());

            },
            stop: function (event, ui) {

                var $element = $(ui.item);
                $element.css('height','');
                if($('#cgCollapse').hasClass('cg_uncollapsed')){
                    $element.find('.cg_view_options_row:not(.cg_view_options_row_title)').removeClass('cg_hide');
                    $element.find('.cg_view_options_row_title').removeClass('cg_border_bottom_thin_solid_default_color');
                    $element.closest('.formField').find('.cg_view_options_row_marker').addClass('cg_hide');
                }
                // condition for html fields. Reinitialize after deactivating when start
                var $cgWpEditorContainer = $element.find('.cg-wp-editor-container');
                if(!cgJsClassAdmin.index.vars.isShortcodeIntervalDatetpickerLoaded){
                    if(cgJsClassAdmin.index.vars.wpVersion>=cgJsClassAdmin.index.vars.wpVersionForTinyMCE){
                        tinymce.EditorManager.execCommand('mceAddEditor', true, $cgWpEditorContainer.attr('id'));
                    }
                }
                // condition for html fields. Reinitialize after deactivating when start--- END

                setTimeout(function () {
                    cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);
                },10);

            }

        });


        // sortable categories field here
        cgJsClassAdmin.createUpload.functions.setSortableCategoriesArena($,$("#ausgabe1.cg_create_upload .cg_categories_arena"));

        $("#ausgabe1.cg_create_upload .cg-active input[type=\"checkbox\"]").each(function () {
            if ($(this).prop('checked') == true) {
                $(this).closest('.formField').addClass('cg_disable');
            }
            else {
                $(this).closest('.formField').removeClass('cg_disable');
            }
        });

        if ($('#ausgabe1.cg_create_upload .cg_categories_arena').length >= 1) {
            var cg_categories_arena = $('#ausgabe1.cg_create_upload .cg_categories_arena');
            cg_categories_arena.find('.cg_category_field_div').find('.cg_category_change_order').removeClass('cg_hide');
            cg_categories_arena.find('.cg_category_field_div').first().find('.cg_move_view_to_top').addClass('cg_hide');
            if (cg_categories_arena.find('.cg_category_field_div').length == 1) {
                cg_categories_arena.find('.cg_category_field_div').first().find('.cg_move_view_to_bottom').addClass('cg_hide');
            }
            if (cg_categories_arena.find('.cg_category_field_div').length >= 2) {
                cg_categories_arena.find('.cg_category_field_div').last().find('.cg_move_view_to_bottom').addClass('cg_hide');
            }
        }

        if(location.hash.indexOf('cgSelectCategoriesField') >= 0 || location.search.indexOf('cgSelectCategoriesField') >= 0){
            var $selectCategoriesField = jQuery('#cgCreateUploadSortableArea .selectCategoriesField');
            $('#right'+$selectCategoriesField.attr('id')).click();
            cgJsClassAdmin.index.functions.cgGoTo($selectCategoriesField,undefined,undefined,200);
        }

        if(location.hash.indexOf('cgSales') >= 0 || location.search.indexOf('cgSales') >= 0){
            var $selectCategoriesField = jQuery('#cgSales');
            cgJsClassAdmin.index.functions.cgGoTo($selectCategoriesField,undefined,undefined,200);
        }

        $('#cgCreateUploadSortableArea .formField, .imageUploadField.formField').each(function () {
            if($(this).find('.cg_view_option_hide_upload_field .cg_view_option_checkbox input').prop('checked')){
                $(this).addClass('cg_form_field_disabled');
                $(this).find('.cg_view_option:not(.cg_view_option_not_disable,.cg_view_option_hide_upload_field)').addClass('cg_disabled');
                $(this).find('.cg_view_option_watermark_position').addClass('cg_disabled');// have to be done in extra line, not cg_view_option_not_disable would avoid it
            }else{
                $(this).removeClass('cg_form_field_disabled');
                $(this).find('.cg_view_option:not(.cg_view_option_not_disable,.cg_view_option_hide_upload_field,.cg_view_option_disable_watermark_on_load )').removeClass('cg_disabled');
            }
        });

        if(localStorage.getItem('cg_remove_category')){
            var $selectCategoriesField = $('#cgCreateUploadSortableArea .selectCategoriesField');
            if($selectCategoriesField.length){
                $selectCategoriesField.get(0).scrollIntoView();
                localStorage.removeItem('cg_remove_category');
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Category field removed',true);
            }else{
                localStorage.removeItem('cg_remove_category');
            }
        }

        cgJsClassAdmin.options.vars.cg_create_upload_container_offset = $('#ausgabe1.cg_create_upload').offset().top;
        cgJsClassAdmin.options.vars.$cgUploadFieldsSelect = $('#cgUploadFieldsSelect');
        cgJsClassAdmin.options.vars.$imageUploadField = $('#ausgabe1 .imageUploadField');
        cgJsClassAdmin.options.vars.$wpadminbar = $('#wpadminbar');
        cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea = $('#cgCreateUploadSortableArea');
        cgJsClassAdmin.options.vars.wpadminbarHeight  = cgJsClassAdmin.options.vars.$wpadminbar.height();

        $('#cgRightSide .cg_row_col.cg_el.cg_drag_drop').each(function () {
            var $el = $(this);
            var $temp = $("<div " + cgJsClassAdmin.gallery.vars.dragAndDropEvents + " " + cgJsClassAdmin.gallery.vars.draggableDragStartDragEnd + "></div>");
            $.each($temp[0].attributes, function () {
                $el.attr(this.name, this.value);
            });
        });

        cgJsClassAdmin.index.vars.$cgCreateUploadSortableArea = $('#cgCreateUploadSortableArea');
        cgJsClassAdmin.index.vars.$ausgabe1 = $('#ausgabe1');
        cgJsClassAdmin.index.vars.$cgRightSide = $('#cgRightSide');

        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);

    },
    collapseFields: function () {
        var $cgCreateUploadSortableArea = cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea;
        $cgCreateUploadSortableArea.find('.formField .cg_view_options_row:not(.cg_view_options_row_title)').addClass('cg_hide');
        $cgCreateUploadSortableArea.find('.formField .cg_view_options_row_title').addClass('cg_border_bottom_thin_solid_default_color cg_view_options_row_uncollapse').removeClass('cg_view_options_row_collapse').attr('title','Uncollapse');
        $cgCreateUploadSortableArea.find('.cg_view_option_input_field_title').each(function (){
            jQuery(this).closest('.formField').find('.cg_view_options_row_marker').removeClass('cg_hide').find('.cg_view_options_row_marker_content').text(jQuery(this).val());
        });
        var $imageUploadField = cgJsClassAdmin.options.vars.$imageUploadField;
        $imageUploadField.find('.cg_view_options_row:not(.cg_view_options_row_title)').addClass('cg_hide');
        $imageUploadField.find('.cg_view_options_row_title').addClass('cg_border_bottom_thin_solid_default_color cg_view_options_row_uncollapse').removeClass('cg_view_options_row_collapse').attr('title','Uncollapse');
        $imageUploadField.find('.cg_view_options_row_marker').removeClass('cg_hide').find('.cg_view_options_row_marker_content').text($imageUploadField.find('.cg_view_option_input_field_title').val());
    },
    uncollapseFields: function () {
        var $cgCreateUploadSortableArea = cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea;
        $cgCreateUploadSortableArea.find('.formField .cg_view_options_row:not(.cg_view_options_row_title)').removeClass('cg_hide');
        $cgCreateUploadSortableArea.find('.formField .cg_view_options_row_title').removeClass('cg_border_bottom_thin_solid_default_color cg_view_options_row_uncollapse').addClass('cg_view_options_row_collapse').attr('title','Collapse');
        $cgCreateUploadSortableArea.find('.formField .cg_view_options_row_marker').addClass('cg_hide');
        var $imageUploadField = cgJsClassAdmin.options.vars.$imageUploadField;
        $imageUploadField.find('.cg_view_options_row:not(.cg_view_options_row_title)').removeClass('cg_hide');
        $imageUploadField.find('.cg_view_options_row_title').removeClass('cg_border_bottom_thin_solid_default_color cg_view_options_row_uncollapse').addClass('cg_view_options_row_collapse').attr('title','Collapse');
        $imageUploadField.find('.cg_view_options_row_marker').addClass('cg_hide');
    },
    setSortableCategoriesArena: function ($,$cg_categories_arena) {

        $cg_categories_arena.sortable({
            placeholder: "ui-state-highlight",
            handle: ".cg_drag_area_1",
            cursor: "move",
            htmlClone: null,
            scrollSpeed: 5,
            start: function (event, ui) {

                var $element = $(ui.item);

                console.log('dragging start');

                $element.closest('.cg_categories_arena').find('.ui-state-highlight').addClass($element.get(0).classList.value).html($element.html());

            },
            stop: function (event, ui) {

                var $element = $(ui.item);

                console.log('dragging stop');

            }
        });

    },
    addRightFieldOrder: function ($) {

        var v = 1;

        $("#ausgabe1.cg_create_upload .formField").each(function (i) {

            $(this).find('.fieldOrder').val(v);

            v++;

        });

    },
    addRowsAndColumns: function ($) {

        var v = 1;
        var fieldOrder = 1;
        var $cgCreateUploadSortableArea = cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea;
        var $cgRightSide = cgJsClassAdmin.index.vars.$cgRightSide;
        $cgCreateUploadSortableArea.find('#40').find('.fieldOrder').val(fieldOrder);

        $cgRightSide.find("> .cg_row:not(.cg_add_row)").each(function (i) {

            if(v==1){
                v++;
                return;// then continue simply, because is image
            }

            var $row = $(this);
            var rowCols = $row.find('.cg_row_col').length;
            var rowElCols = $row.find('.cg_row_col.cg_el').length;

            $row.find('.cg_row_col').removeClass('cg_33 cg_50 cg_100');

            if(rowCols==1){
                $row.find('.cg_row_col').addClass('cg_100');
            }else if(rowCols==2){
                $row.find('.cg_row_col').addClass('cg_50');
            }else if(rowCols==3){
                $row.find('.cg_row_col').addClass('cg_33');
            }

            if(rowElCols){
                $row.removeClass('cg_empty');
                $(this).find('.cg_row_col').each(function (i) {
                    if($(this).attr('id')){
                        fieldOrder++;
                        var id = $(this).attr('id').split('right')[1];
                        var $div = $cgCreateUploadSortableArea.find('#'+id);
                        $div.find('.RowNumber').val(v);
                        $div.find('.ColNumber').val(i+1);
                        $div.find('.RowCols').val(rowCols);
                        $div.find('.fieldOrder').val(fieldOrder);
                    }
                });
                v++;
            }else{
                $row.addClass('cg_empty');
            }

        });

    },
    addRightFieldOrderAndAddRowsAndColumns: function ($) {
        this.addRightFieldOrder($);
        this.addRowsAndColumns($);
    },
    fDeleteFieldAndDataModifyRowsAndColumns: function ($,fieldContainerId, idToDelete, categoryField) {
        debugger
        $("#cgCreateUploadSortableArea #" + idToDelete).remove();
        if (categoryField) {
            $("#ausgabe1.cg_create_upload").append("<input type='hidden' name='deleteFieldnumber[deleteCategoryField]' value=" + idToDelete + ">");
        }
        else {
            $("#ausgabe1.cg_create_upload").append("<input type='hidden' name='deleteFieldnumber[normalFields]["+idToDelete+"]' value=" + idToDelete + ">");
        }

        this.addRightFieldOrderAndAddRowsAndColumns($);
        $('#cgUplEditFields').removeClass('cg_hide');

        //$('#submitForm').click();

    },
    goToField: function ($,isAddNewField) {

        var toSubstract = 0;

        if(isAddNewField){
            toSubstract = 70;
        }

      //  $("html, body").animate({ scrollTop: $('#dauswahl').offset().top-toSubstract }, 0);
    },
    appendCloneRight: function ($,fieldType,newId) {
        debugger
        var $clones = $('#cgFieldsToCloneAndAppendCloneRight');
        var $clone = $clones.find('[data-cg-field="'+fieldType+'"]').clone();
        $clone.attr('id','right'+newId);
        var $div = $('#cgRightSide .cg_row_col.cg_upl_add.cg_clicked');
        $div.replaceWith($clone);
        $clone.click();
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
        setTimeout(function (){
            if(fieldType!='ht' && fieldType!='caRoRe'){
                var $newDiv = $('#'+newId);
                debugger
                var value = $newDiv.find('.cg_view_option_input_field_title').val();
                //var newValue = value+' new';
                var newValue = value;
                //$newDiv.find('.cg_view_option_input_field_title').val(newValue).focus();
                $newDiv.find('.cg_view_option_input_field_title').val(newValue);
                //$('#cgRightSide .cg_row_col.cg_el.cg_edit_left .cg_upl_title').contents().first()[0].nodeValue = newValue;
                $('#cgRightSide .cg_row_col.cg_el.cg_edit_left .cg_upl_title').text(newValue);
            }
            $('#cgRightSide .cg_row_col.cg_el.cg_edit_left').parent().find('.cg_del_row').addClass('cg_hide');
        },10);
    },
    getAddElCol: function () {
        return '<div class="cg_row_col cg_upl_add" title="Add field" '+cgJsClassAdmin.gallery.vars.dragAndDropEvents+'><div class="cg_upl_add_container"></div><div class="cg_upl_del" title="Delete column"></div></div>';
    },
    showDelRow: function () {
        jQuery('#cgRightSide').find('.cg_row_col.cg_upl_add').each(function (i) {
            var $row = jQuery(this).parent();
            if(!$row.find('.cg_row_col.cg_el').length){
             //   $row.find('.cg_del_row').removeClass('cg_hide');
            }else{
                $row.find('.cg_del_row').addClass('cg_hide');
            }
        });
    },
    removeAddRows: function ($) {
        // correct double cg_add_row
        var hasAddRow = false;
        $('#cgRightSide .cg_row').each(function (){
            if($(this).hasClass('cg_add_row') && hasAddRow){
                $(this).remove();
                hasAddRow = false;
            }else if($(this).hasClass('cg_add_row')){
                hasAddRow = true;
            }else{
                hasAddRow = false
            }
        });
    }
};