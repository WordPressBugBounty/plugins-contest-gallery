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

        cgJsClassAdmin.options.vars.cg_create_upload_registry_container_offset = $('#ausgabe1.cg_create_upload').offset().top;
        cgJsClassAdmin.options.vars.$cgUploadFieldsSelect = $('#cgUploadFieldsSelect');
        cgJsClassAdmin.options.vars.$imageUploadField = $('#ausgabe1 .imageUploadField');
        cgJsClassAdmin.options.vars.$wpadminbar = $('#wpadminbar');
        cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea = $('#cgCreateUploadSortableArea');
        cgJsClassAdmin.options.vars.wpadminbarHeight  = cgJsClassAdmin.options.vars.$wpadminbar.height();

        // setDragAndDropEvents
        cgJsClassAdmin.createUpload.functions.setDragAndDropEvents($);

        cgJsClassAdmin.index.vars.$cgCreateUploadSortableArea = cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea;
        cgJsClassAdmin.index.vars.$ausgabe1 = $('#ausgabe1');
        cgJsClassAdmin.index.vars.$cgRightSide = $('#cgRightSide');

        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);

    },
    getVisibleHeightFixedInParent: function ($el) {
        const rect = $el[0].getBoundingClientRect();
        const winH = window.innerHeight || document.documentElement.clientHeight;

        const visibleTop = Math.max(rect.top, 0);
        const visibleBottom = Math.min(rect.bottom, winH);

        return Math.max(0, visibleBottom - visibleTop);
    },
    cgCreateUploadSortableAreaScroll: function ($) {

        var $cgCreateUploadSortableArea = cgJsClassAdmin.index.vars.$cgCreateUploadSortableArea;
        var $ausgabe1 = cgJsClassAdmin.index.vars.$ausgabe1;
        var windowScrollTop = $(window).scrollTop();

        // console.log('windowScrollTop');
        // console.log(windowScrollTop);

        if((windowScrollTop+50)>cgJsClassAdmin.options.vars.cg_create_upload_registry_container_offset){
            //$cgCreateUploadSortableArea.addClass('cg_sticky').css({'border-right':'unset','top':cgJsClassAdmin.options.vars.wpadminbarHeight+'px','width':(cgJsClassAdmin.index.vars.$cg_main_container.width()-1)+'px'});
            $cgCreateUploadSortableArea.addClass('cg_sticky').css({'top':cgJsClassAdmin.options.vars.wpadminbarHeight+'px','width':(cgJsClassAdmin.index.vars.$cg_main_container.width())+'px'});
            $cgCreateUploadSortableArea.css('width',$ausgabe1.width()+'px');

            // Example usage
            //const visibleHeight = getVisibleHeightInParent($('.child'), $('.parent'));
            //const visibleHeight = getVisibleHeightInParent(cgJsClassAdmin.index.vars.$cgCreateUploadSortableArea, cgJsClassAdmin.index.vars.$ausgabe1);
            var visibleHeight = cgJsClassAdmin.createUpload.functions.getVisibleHeightFixedInParent(cgJsClassAdmin.index.vars.$ausgabe1);

            //console.log('visibleHeight');
            //console.log(visibleHeight);
            $cgCreateUploadSortableArea.css('height',visibleHeight-50+'px');
            cgJsClassAdmin.index.vars.resizeLeftSideIsActive = true;

        }else{
            //$cgCreateUploadSortableArea.removeClass('cg_sticky').css({'top':'','border-right':''} );
            $cgCreateUploadSortableArea.removeClass('cg_sticky');
            $cgCreateUploadSortableArea.css('width','');
            $cgCreateUploadSortableArea.css('height','');
            cgJsClassAdmin.index.vars.resizeLeftSideIsActive = false;

        }

    },
    setDragAndDropEvents: function ($) {
        $('#cgRightSide .cg_row_col.cg_el.cg_drag_drop').each(function () {
            var $el = $(this);
            var $temp = $("<div " + cgJsClassAdmin.gallery.vars.dragAndDropEvents + " " + cgJsClassAdmin.gallery.vars.draggableDragStartDragEnd + "></div>");
            $.each($temp[0].attributes, function () {
                $el.attr(this.name, this.value);
            });
        });
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

                $element.closest('.cg_categories_arena').find('.ui-state-highlight').addClass($element.get(0).classList.value).html($element.html());

            },
            stop: function (event, ui) {

                var $element = $(ui.item);

            }
        });

    },
    addUploadFieldOrder: function ($) {

        var v = 1;

        $("#ausgabe1.cg_create_upload .formField").each(function (i) {

            $(this).find('.fieldOrder').val(v);

            v++;

        });

    },
    rowLayouts: {'1': [100], '1-1': [50, 50], '2-1': [66, 33], '1-2': [33, 66], '1-1-1': [33, 33, 33]},
    rowLayoutLabels: {'1': '1/1', '1-1': '1/2 + 1/2', '2-1': '2/3 + 1/3', '1-2': '1/3 + 2/3', '1-1-1': '1/3 + 1/3 + 1/3'},
    normalizeRowLayout: function ($row) {
        var count = $row.children('.cg_row_col').length;
        var layout = $row.attr('data-cg-row-layout');
        if(!Object.prototype.hasOwnProperty.call(this.rowLayouts, layout) || this.rowLayouts[layout].length !== count){
            layout = count === 3 ? '1-1-1' : (count === 2 ? '1-1' : '1');
        }
        $row.attr('data-cg-row-layout', layout);
        return layout;
    },
    refreshUploadRowTools: function ($) {
        var self = this;
        var $rightSide = $('#cgCreateUploadForm #cgRightSide, #cg_registry_form_container_parent #cgRightSide');
        var $rows = $rightSide.children('.cg_row:not(.cg_add_row):not(.cg_image):not(.cg_drag_drop_temporary_row)');
        $rows.each(function (index) {
            var $row = $(this);
            if(!$row.children('.cg_upload_row_tools').length){
                $row.children('.cg_add_col').remove();
                $row.prepend($('#cgUploadRowToolsTemplate').children().clone());
            }
            var layout = self.normalizeRowLayout($row);
            $row.children('.cg_upload_row_tools').find('.cg_upload_row_layout_label').text(self.rowLayoutLabels[layout]);
            $row.find('.cg_upload_row_move[data-cg-direction="up"]').prop('disabled', index === 0);
            $row.find('.cg_upload_row_move[data-cg-direction="down"]').prop('disabled', index === $rows.length - 1);
            $row.find('.cg_add_col').toggleClass('cg_hide', $row.children('.cg_row_col').length >= 3);
            $row.children('.cg_row_col.cg_el').each(function () {
                if(!$(this).children('.cg_upload_field_handle').length){
                    $(this).prepend($('<span class="cg_upload_field_handle" aria-hidden="true" title="Drag to move or swap field"></span>').text('\u22ee\u22ee'));
                }
            });
        });
        $rightSide.children('.cg_add_row').attr({role: 'button', tabindex: '0', title: 'Add row'}).text('+ Add row');
    },
    setRowLayout: function ($, $row, layout) {
        if(!$row || !$row.closest('#cgCreateUploadForm, #cg_registry_form_container_parent').length || !Object.prototype.hasOwnProperty.call(this.rowLayouts, layout)){return;}
        var columns = this.rowLayouts[layout].length;
        var $slots = $row.children('.cg_row_col');
        var $after = $row;
        // Keep occupied slots in order. Move only fields beyond the new row capacity.
        $slots.slice(columns).each(function () {
            var $slot = $(this);
            if($slot.hasClass('cg_el')){
                var $newRow = $('<div class="cg_row" data-cg-row-layout="1"><div class="cg_add_col" title="Add column"></div></div>');
                $newRow.append($slot).insertAfter($after);
                $after = $newRow;
            }else{
                $slot.remove();
            }
        });
        while($row.children('.cg_row_col').length < columns){$row.append(this.getAddElCol());}
        $row.attr('data-cg-row-layout', layout);
        cgJsClassAdmin.createUpload.dragAndDrop.normalizeAddRows($row.closest('#cgRightSide'));
        this.addRightFieldOrderAndAddRowsAndColumns($);
    },
    addRowsAndColumns: function ($) {

        var v = 1;
        var fieldOrder = cgJsClassAdmin.index.vars.isCreateUploadAreaLoaded ? 1 : 0;
        var $cgCreateUploadSortableArea = cgJsClassAdmin.options.vars.$cgCreateUploadSortableArea;
        var $cgRightSide = cgJsClassAdmin.index.vars.$cgRightSide;
        var $imageField = $cgCreateUploadSortableArea.find('#40');
        $imageField.find('.fieldOrder,.RowNumber,.ColNumber,.RowCols').val(1);
        if($imageField.length && !$imageField.find('.RowLayout').length){
            $('<input type="hidden" class="RowLayout">').attr('name',$imageField.find('.RowCols').attr('name').replace('RowCols','RowLayout')).appendTo($imageField);
        }
        $imageField.find('.RowLayout').val('1');

        $cgRightSide.find("> .cg_row:not(.cg_add_row)").each(function (i) {

            if(cgJsClassAdmin.index.vars.isCreateUploadAreaLoaded){
                if(v==1){
                    v++;
                    return;// then continue simply, because is image
                }
            }

            var $row = $(this);
            var rowCols = $row.find('.cg_row_col').length;
            var rowElCols = $row.find('.cg_row_col.cg_el').length;

            var isUploadLayout = $row.closest('#cgCreateUploadForm, #cg_registry_form_container_parent').length > 0;
            var layout = isUploadLayout ? cgJsClassAdmin.createUpload.functions.normalizeRowLayout($row) : '';
            $row.find('.cg_row_col').removeClass('cg_33 cg_50 cg_66 cg_100');

            if(rowCols==1){
                $row.find('.cg_row_col').addClass('cg_100');
            }else if(rowCols==2){
                $row.find('.cg_row_col').addClass('cg_50');
            }else if(rowCols==3){
                $row.find('.cg_row_col').addClass('cg_33');
            }

            if(isUploadLayout){
                var widths = cgJsClassAdmin.createUpload.functions.rowLayouts[layout];
                $row.children('.cg_row_col').each(function (index) {
                    $(this).removeClass('cg_33 cg_50 cg_66 cg_100').addClass('cg_' + widths[index]);
                });
            }

            if(rowElCols){
                $row.removeClass('cg_empty');
                $(this).find('.cg_row_col').each(function (i) {
                    if($(this).attr('id')){
                        fieldOrder++;
                        var id = $(this).attr('id').split('right')[1];
                        var $div = $cgCreateUploadSortableArea.find('#'+id);
                        if(!$div.length){return;}
                        $div.find('.RowNumber').val(v);
                        $div.find('.ColNumber').val(i+1);
                        $div.find('.RowCols').val(rowCols);
                        if(isUploadLayout){
                            var $layout = $div.find('.RowLayout');
                            if(!$layout.length){
                                $layout = $('<input type="hidden" class="RowLayout">').attr('name', $div.find('.RowCols').attr('name').replace('RowCols', 'RowLayout')).appendTo($div);
                            }
                            $layout.val(layout);
                        }
                        $div.find('.fieldOrder,.Field_Order').val(fieldOrder);
                    }
                });
                v++;
            }else{
                $row.addClass('cg_empty');
            }

        });

    },
    addRightFieldOrderAndAddRowsAndColumns: function ($) {
        if(cgJsClassAdmin.index.vars.isCreateRegistryAreaLoaded){
            cgJsClassAdmin.createRegistry.functions.cgSortOrder($);
        }else{
            this.addUploadFieldOrder($);
        }
        this.addRowsAndColumns($);
        this.refreshUploadRowTools($);
    },
    fDeleteFieldAndDataModifyRowsAndColumns: function ($,fieldContainerId, idToDelete, categoryField) {
        $("#cgCreateUploadSortableArea #" + fieldContainerId).remove();
        if (categoryField) {
            $("#ausgabe1.cg_create_upload").append("<input type='hidden' name='deleteFieldnumber[deleteCategoryField]' value=" + idToDelete + ">");
        }
        else {
            $("#ausgabe1.cg_create_upload").append("<input type='hidden' name='deleteFieldnumber[normalFields]["+idToDelete+"]' value=" + idToDelete + ">");
        }

        // Recalculate only after the matching preview has also been removed.
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
        var $addRow = $('#cgRightSide .cg_row.cg_add_row.cg_clicked').first();
        var $addCol = $('#cgRightSide .cg_row .cg_add_col.cg_clicked').first();
        if($div.length){
            $div.replaceWith($clone);
        }else if($addRow.length){
            var $newRow = $('<div class="cg_row"><div class="cg_add_col" title="Add column"></div></div>');
            var $addRowClone = $addRow.clone().removeClass('cg_clicked');
            $newRow.append($clone);
            if($addRow.prev().hasClass('cg_image')){
                $newRow.insertAfter($addRow);
                $addRowClone.insertAfter($newRow);
            }else{
                $newRow.insertBefore($addRow);
                $addRowClone.insertBefore($newRow);
            }
        }else if($addCol.length){
            var $row = $addCol.closest('.cg_row');
            if($row.find('.cg_row_col').length>=3){
                $addCol.addClass('cg_hide');
                $('#cgCreateUploadSortableArea').find('#'+newId).remove();
                return;
            }
            $row.append($clone);
            if($row.find('.cg_row_col').length>=3){
                $addCol.addClass('cg_hide');
            }
        }else{
            $('#cgCreateUploadSortableArea').find('#'+newId).remove();
            return;
        }
        $('#cgRightSide').find('.cg_upl_add,.cg_row.cg_add_row,.cg_add_col').removeClass('cg_clicked');
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
        return '<div class="cg_row_col cg_upl_add" title="Add field" '+cgJsClassAdmin.gallery.vars.dragAndDropEvents+'><button type="button" class="cg_upl_add_container" aria-haspopup="dialog"><span class="cg_upl_add_icon" aria-hidden="true"></span><span class="cg_upl_add_text"><span class="cg_upl_add_label">Add field</span><span class="cg_upl_add_hint">Choose field type</span></span></button><div class="cg_upl_del" title="Delete column"></div></div>';
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
    },
    removeRightSideFieldElement: function ($,$el) {
        var $row = $el.closest('.cg_row');
        var $remainingFieldCols;

        $remainingFieldCols = $row.find('.cg_row_col.cg_el,.cg_row_col[id]:not(.cg_upl_add)').not($el);

        if(!$remainingFieldCols.length){
            $el.remove();
            $row.remove();
            cgJsClassAdmin.createUpload.functions.removeAddRows($);
            return;
        }

        $el.replaceWith($(cgJsClassAdmin.createUpload.functions.getAddElCol()));

        if($row.find('.cg_row_col').length<3){
            $row.find('.cg_add_col').removeClass('cg_hide');
        }else{
            $row.find('.cg_add_col').addClass('cg_hide');
        }
    },
    removeFieldRightSide: function ($,fieldContainerId) {
        var $el = $('#cgRightSide').find('#right'+fieldContainerId);
        cgJsClassAdmin.createUpload.functions.removeRightSideFieldElement($,$el);
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
    }
};
