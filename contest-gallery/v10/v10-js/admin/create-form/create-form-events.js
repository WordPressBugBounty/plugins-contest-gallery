jQuery(document).ready(function ($) {

    $(document).on('mouseenter','#cgRightSide .cg_row > .cg_row_col',function (e) {
        //console.log('hover in');
        if(!$(this).hasClass('cg_upl_add')) {
       //     if($(e.target).hasClass('cg_row_col') || $(e.target).closest('.cg_row_col').length) {
            $(this).addClass('cg_edit');
      //     }
       }
    });

    $(document).on('mouseleave','#cgRightSide .cg_row > .cg_row_col',function (e) {
     //   console.log($(e.target));
      //  if(!$(e.target).closest('.cg_row_col').length) {
           $(this).removeClass('cg_edit');
 //       }
    });

    $(document).on('mouseenter','#cgRightSide .cg_upl_del',function (e) {
        $(this).parent().addClass('cg_del');
    });

    $(document).on('mouseleave','#cgRightSide .cg_upl_del',function (e) {
        $(this).parent().removeClass('cg_del');
    });

    $(document).on('mouseenter','#cgRightSide .cg_upl_settings',function (e) {
        $(this).parent().addClass('cg_configure');
    });

    $(document).on('mouseleave','#cgRightSide .cg_upl_settings',function (e) {
        $(this).parent().removeClass('cg_configure');
    });

    $(document).on('click','#cgRightSide .cg_upl_settings',function (e) {
        $(this).closest('#cgRightSide').find('.cg_upl_settings').removeClass('cg_clicked');
        $(this).addClass('cg_clicked');
        cgJsClassAdmin.gallery.vars.$clickedRow = $(this).parent();
        cgJsClassAdmin.gallery.functions.showModal('#cgUplCols');
    });

    var row = '' +
        '<div class="cg_row">' +
         //   '<div class="cg_upl_settings" title="Row settings"></div>' +
            '<div class="cg_add_col" title="Add column"></div>' +
       //     '<div class="cg_del_row" title="Delete row"></div>' +
        '</div>' +
    '';

   $(document).on('click','#cgUplCols #cgUplColsOne',function (e) {
       var count = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row').find('.cg_row_col:not(.cg_upl_add)').length;
       debugger
       var $currentRow = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row');
       if(count==0){
           var $newRow = $(row);
           $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
           $currentRow.replaceWith($newRow);
       }else if(count==1){
           // do nothing
           $currentRow.find('.cg_row_col.cg_upl_add').remove();
       }else if(count==2){
           var $newRow = $(row);
           $newRow.append($currentRow.find('.cg_row_col.cg_el').first().next());
           $newRow.insertAfter($currentRow);
       }else if(count==3){
           var $newRowOne = $(row);
           $newRowOne.append($currentRow.find('.cg_row_col.cg_el').first().next());
           $newRowOne.insertAfter($currentRow);
           var $newRowTwo = $(row);
           $newRowTwo.append($currentRow.find('.cg_row_col.cg_el').last());
           $newRowTwo.insertAfter($newRowOne);
       }
       cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
       $('#cgUplCols .cg_message_close').click();
    });

   $(document).on('click','#cgUplCols #cgUplColsTwo',function (e) {
        var count = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row').find('.cg_row_col:not(.cg_upl_add)').length;
       debugger
       var $currentRow = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row');
        if(count==0){
            var $newRow = $(row);
            $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
            $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
            $currentRow.replaceWith($newRow);
        }else if(count==1){
            $currentRow.find('.cg_row_col.cg_upl_add').remove();
            $currentRow.append(cgJsClassAdmin.createUpload.functions.getAddElCol());
        }else if(count==2){
            // do nothing
        }else if(count==3){
            var $newRow = $(row);
            $newRow.append($currentRow.find('.cg_row_col.cg_el').last());
            $newRow.insertAfter($currentRow);
        }
       cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
       $('#cgUplCols .cg_message_close').click();
    });

   $(document).on('click','#cgUplCols #cgUplColsThree',function (e) {
        var count = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row').find('.cg_row_col:not(.cg_upl_add)').length;
       debugger
       var $currentRow = $('#cgRightSide').find('.cg_upl_settings.cg_clicked').closest('.cg_row');
        if(count==0){
            var $newRow = $(row);
            $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
            $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
            $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
            $currentRow.replaceWith($newRow);
        }else if(count==1){
            $currentRow.find('.cg_row_col.cg_upl_add').remove();
            $currentRow.append(cgJsClassAdmin.createUpload.functions.getAddElCol());
            $currentRow.append(cgJsClassAdmin.createUpload.functions.getAddElCol());
        }else if(count==2){
            $currentRow.find('.cg_row_col.cg_upl_add').remove();
            $currentRow.append(cgJsClassAdmin.createUpload.functions.getAddElCol());
        }else if(count==3){
            // do nothing
        }
       cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);

       $('#cgUplCols .cg_message_close').click();
    });

    $(document).on('click','#cgRightSide .cg_upl_add',function (e) {
        $(this).closest('#cgRightSide').find('.cg_upl_add').removeClass('cg_clicked');
        $(this).addClass('cg_clicked');
        if($(this).closest('#cg_registry_form_container_parent').length){
            cgJsClassAdmin.gallery.functions.showModal('#cgAddRegField');
        }else{
            cgJsClassAdmin.gallery.functions.showModal('#cgAddUplField');
        }
    });

    $(document).on('click','#cgRightSide .cg_row_col .cg_upl_del',function (e) {
        e.stopPropagation();
        var $col = $(this).closest('.cg_row_col');
        var $row = $(this).closest('.cg_row');
        if($col.attr('id')){
            var id = $col.attr('id').split('right')[1];
            $('#cgUplEditFields').addClass('cg_hide');
            $('#40').addClass('cg_hide'); // image field
            $('#cgRightSide').find('.cg_row_col.cg_edit_left').removeClass('cg_edit cg_edit_left');// hide all possible to be clicked to being edited
            var $cgCreateUploadSortableArea = $('#cgCreateUploadSortableArea');
            $cgCreateUploadSortableArea.find('> div').addClass('cg_hide');
            var $div = $cgCreateUploadSortableArea.find('#'+id);
            debugger
            if($div.find('.cg_remove:not(.cg_remove_new)').length){
                $div.removeClass('cg_hide');
                setTimeout(function(){// the removed class element appears before alert message
                    $div.find('.cg_remove').click();
                },100);
            }else if($div.find('.cg_remove_new').length){
                var $row = $(this).closest('.cg_row');
                $(this).closest('.cg_row_col.cg_el').replaceWith($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
                $div.find('.cg_remove_new').click();
                if(!$row.find('.cg_row_col.cg_el').length){
               //     $row.find('.cg_del_row').removeClass('cg_hide');
                }
            }
        }else{
            $col.closest('.cg_row').find('.cg_add_col').removeClass('cg_hide');
            $col.remove();
            if(!$row.find('.cg_row_col').length){
                $row.remove();
                cgJsClassAdmin.createUpload.functions.removeAddRows($);
            }
        }
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
    });


    $(document).on('click','#cgRightSide .cg_row .cg_add_col',function (e) {
        var $row = $(this).closest('.cg_row');
        if($row.find('.cg_row_col').length<3){
            $row.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
        }
        if($row.find('.cg_row_col').length>=3){
            $row.find('.cg_add_col').addClass('cg_hide');
        }
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
    });

    $(document).on('click','#cgRightSide .cg_row_col.cg_el',function (e) {
        var id = $(this).attr('id').split('right')[1];
        $('#cgUplEditFields').addClass('cg_hide');
        $('#40').addClass('cg_hide'); // image field
        $('#cgRightSide').find('.cg_row_col.cg_edit_left').removeClass('cg_edit cg_edit_left');// hide all possible to be clicked to being edited
        $(this).addClass('cg_edit_left');
        var $cgCreateUploadSortableArea = $('#cgCreateUploadSortableArea');
        $cgCreateUploadSortableArea.find('> div').addClass('cg_hide');
        debugger
        if(id==40){
            $('#40').removeClass('cg_hide');
        }else{
            $cgCreateUploadSortableArea.find('#'+id).removeClass('cg_hide');
        }

    });


    $(document).on('click','#cgRightSide .cg_row .cg_del_row',function (e) {
        $(this).closest('.cg_row').remove();
        cgJsClassAdmin.createUpload.functions.removeAddRows($);
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
    });

    $(document).on('click','#cgAddUplField .cg_field,#cgAddRegField .cg_field',function (e) {
        $(this).closest('#cgAddUplField,#cgAddRegField').find('.cg_message_close').click();
        var type = $(this).attr('data-cg-field');
        $('#dauswahl').val(type);
        $('#cg_create_upload_add_field.cg_upload_dauswahl,#cg_create_upload_add_field.cg_registry_dauswahl').click();
    });

    $(document).on('click', '.cg_row.cg_add_row',function (e) {
        var $newRow = $(row);
        var $clone = $(this).clone();
        $newRow.append($(cgJsClassAdmin.createUpload.functions.getAddElCol()));
        if ($(this).prev().hasClass('cg_image')) {
            $newRow.insertAfter($(this));
            $clone.insertAfter($newRow);
        } else {
            $newRow.insertBefore($(this));
            $clone.insertBefore($newRow);
        }
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);
    });

    $(document).on('input', '#ausgabe1 .cg_view_option_input_field_title',function (e) {
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        debugger
        if($(this).closest('.formField').hasClass('imageUploadField')){
            $cgRightSide.find('#right'+id).find('.cg_upl_img_title').text($(this).val());
        }else{
            $cgRightSide.find('#right'+id).find('.cg_upl_title').contents().first()[0].nodeValue = $(this).val();
        }
    });

    $(document).on('input', '#cgCreateUploadSortableArea .cg_title_placeholder,#cgCreateUploadSortableArea .cg_textarea_placeholder',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        var $cg_upl_placeholder = $cgRightSide.find('#right'+id).find('.cg_upl_content .cg_upl_placeholder');
        if($(this).hasClass('cg_title_placeholder')) {
            $cg_upl_placeholder.attr('placeholder',$(this).val());
        }else if($(this).hasClass('cg_textarea_placeholder')) {
            $cg_upl_placeholder.attr('placeholder',$(this).val());
        }
    });

    $(document).on('input', '#cgCreateUploadSortableArea .cg_radio_placeholder',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        var $cg_upl_content = $cgRightSide.find('#right'+id).find('.cg_upl_content');

        var raw = $(this).val();

        $cg_upl_content.empty();

        if(raw.trim()){
            // Split by any newline style (Windows \r\n, Unix \n, old Mac \r)
            var lines = raw.split(/\r\n|\r|\n/);
            // Trim each line and remove empties
            lines = lines.map(l => l.trim()).filter(l => l.length > 0);
            // Example: iterate (forEach) to do something with each line
            // Here we’ll render a preview list
            $.each(lines, function (i, line) {
                $cg_upl_content.append($('<div class="cg_upl_radio_button">' +
                    '<div class="cg_upl_radio_button_text">'+line+'</div>' +
                    '<input type="radio" />' +
                    '</div>'));
            });
        }else{
            $cg_upl_content.append('<div class="cg_upl_add_radio">No radio buttons added</div>');
        }

    });

    $(document).on('input', '#cgCreateUploadSortableArea .cg_check_placeholder',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        var $cg_upl_content = $cgRightSide.find('#right'+id).find('.cg_upl_content');

        var raw = $(this).val();

        $cg_upl_content.empty();

        if(raw.trim()){
            // Split by any newline style (Windows \r\n, Unix \n, old Mac \r)
            var lines = raw.split(/\r\n|\r|\n/);
            // Trim each line and remove empties
            lines = lines.map(l => l.trim()).filter(l => l.length > 0);
            // Example: iterate (forEach) to do something with each line
            // Here we’ll render a preview list
            $.each(lines, function (i, line) {
                // your per-line logic here
                $cg_upl_content.append($('<div class="cg_upl_check_button">' +
                    '<div class="cg_upl_check_button_text">'+line+'</div>' +
                    '<input type="checkbox" />' +
                    '</div>'));
            });
        }else{
            $cg_upl_content.append('<div class="cg_upl_add_check">No checkboxes added</div>');
        }

    });

    $(document).on('click', '#cgCreateUploadSortableArea .cg_view_option_required',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_checked')) {
            $cgRightSide.find('#right'+id).find('.cg_upl_title').addClass('cg_upl_required');
        }else if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_unchecked')) {
            $cgRightSide.find('#right'+id).find('.cg_upl_title').removeClass('cg_upl_required');
        }
    });

    $(document).on('click', '#ausgabe1 .cg_view_option_image_required',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        debugger
        if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_checked')) {
            $cgRightSide.find('#right'+id).find('.cg_upl_img_title').addClass('cg_upl_required');
        }else if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_unchecked')) {
            $cgRightSide.find('#right'+id).find('.cg_upl_img_title').removeClass('cg_upl_required');
        }
    });

    $(document).on('click', '#cgCreateUploadSortableArea .cg_view_option_hide_upload_field',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_checked')) {
            $cgRightSide.find('#right'+id).addClass('cg_hide_upload_field');
        }else if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_unchecked')) {
            $cgRightSide.find('#right'+id).removeClass('cg_hide_upload_field');
        }
    });

    $(document).on('click', '#cgCreateUploadSortableArea .cg_pin_field_check',function (e) {
        debugger
        var $cgRightSide = $(this).closest('.cgCreateUploadContainer').find('#cgRightSide');
        var id = $(this).closest('.formField').attr('id');
        if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_checked')) {
            $('<div class=\'cg_pin_field\' title="Available in [cg_users_pin] form"></div>').insertAfter($cgRightSide.find('#right'+id+' .cg_upl_title'));
        }else if($(this).find('.cg_view_option_checkbox').hasClass('cg_view_option_unchecked')) {
            $cgRightSide.find('#right'+id+' .cg_pin_field').remove();
        }
    });

    $(document).on('click', '#cgUplCols .cg_row_col_row_del',function (e) {
        $('#cgUplCols').find('.cg_message_close').click();
        if(cgJsClassAdmin.gallery.vars.$clickedRow.find('.cg_row_col.cg_el.cg_el_saved').length){
            cgJsClassAdmin.gallery.functions.showModal('#cgDelExistingFieldsFirst');
        }else{
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Row removed',true);
            cgJsClassAdmin.gallery.vars.$clickedRow.remove();
            cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns($);
        }
    });

    $(document).on('input keypress', '#cgCreateUploadSortableArea .Min_Char,#cgCreateUploadSortableArea .Max_Char',function (e) {
        var $input = $(this);

        // --- Handle keypress (block non-numeric keys) ---
        if (e.type === 'keypress') {
            var char = String.fromCharCode(e.which);
            // Allow control keys (backspace, delete, arrows)
            if (e.which === 0 || e.which === 8) return true;
            // Block anything that's not 0–9
            if (!/[0-9]/.test(char)) {
                e.preventDefault();
                return false;
            }
        }

        // --- Handle input (clean up and default 0) ---
        if (e.type === 'input') {
            var value = $input.val().replace(/[^0-9]/g, '');
            if (value === '') {
                value = '0';
                $input.val(value);

                // ✅ Select the "0" so user can type right away
                setTimeout(function() {
                    var inputEl = $input.get(0);
                    if (inputEl.setSelectionRange) {
                        inputEl.setSelectionRange(0, inputEl.value.length);
                    } else if (inputEl.createTextRange) { // IE fallback
                        var range = inputEl.createTextRange();
                        range.select();
                    }
                }, 0);
            } else {
                $input.val(value);
            }
        }
    });

});