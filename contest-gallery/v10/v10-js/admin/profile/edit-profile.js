jQuery(document).ready(function($){


    var cgCheckingNickname = false;
    var nickname = $("#nickname").val();

    $(document).on('input',"#nickname",function (e) {
        var $el = $(this);
        clearTimeout(window._cgInputNickname);
        window._cgInputNickname = setTimeout(function () {
            var $form = $el.closest("form");
            $form.find('#submit').prop("disabled", false);
            $form.find('#cg_input_error_ThisNicknameAlreadyExists').addClass("cg_hide");
        }, 300);
    });

    $(document).on('focusout',"#nickname",function (e) {

        var errorMessage = $('#cg_language_ThisNicknameAlreadyExists').val();
        var $errorMessageContainer = $('<p class="cg_input_error" id="cg_input_error_ThisNicknameAlreadyExists">'+errorMessage+'</p>');
        var value = $(this).val();

        var $form = $('#your-profile');
        $form.find('input[name="action"]').prop('disabled',true);// all not required other action posts will be removed this way
        $form.find('#post_cg_check_nickname_edit_profile').prop('disabled',false);
        $form.find('#cg_input_error_ThisNicknameAlreadyExists').remove();

        var form = document.getElementById('your-profile');
        var formPostData = new FormData(form);

        // 🔐 start: validation running
        cgCheckingNickname = true;

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: null,
            contentType: false,
            processData: false
        }).done(function (response) {

            $form.find('input[name="action"]').prop('disabled',false);
            $form.find('#post_cg_check_nickname_edit_profile').prop('disabled',true);

            if(response.indexOf('nickname-exists') > - 1){
                $form.find('#submit').prop('disabled',true);
                $errorMessageContainer.insertAfter($form.find('#nickname'));
                $('#display_name option').each(function () {
                    if ($(this).text() === value) {
                        $(this).remove();
                        return false; // stop loop early
                    }
                });
            }else{
                $form.find('#submit').prop('disabled',false);
            }

        }).fail(function (xhr, status, error) {

            $form.find('input[name="action"]').prop('disabled',false);
            $form.find('#post_cg_check_nickname_edit_profile').prop('disabled',true);
            $form.find('#submit').prop('disabled',false);

        }).always(function () {
            // 🔓 end: validation finished
            cgCheckingNickname = false;
        });

    });

    $(document).on('click', '#submit', function (e) {
        var currentValue = $("#nickname").val().trim();
        if (cgCheckingNickname && nickname != currentValue && currentValue != '') {
            e.preventDefault();
        }
    });


    $(document).on('click',"#cgShowExistingProfileImage",function (e) {
        $(this).addClass('cg_hide');
        $('.cg_input_image_upload_file_preview_img_existing').removeClass('cg_hide');
        $('.cg_input_image_upload_file_preview_img:not(.cg_input_image_upload_file_preview_img_existing)').addClass('cg_hide');
        $('#cg_input_image_upload_file_preview').removeClass('cg_hide');
        $('#cg_input_image_upload_file_to_delete_button').removeClass('cg_hide');
        $('#cg_input_error_ChooseYourImage').remove();
        $('#submit').prop('disabled',false);
        $('#cg_input_image_upload_file_to_delete_wp_id').prop('disabled',true);
        $('#cg_input_image_upload_file').val('').parent().find('.cg_input_error').remove();
        cgJsClass.profileImage.cancel($('#cg_input_image_upload_file')[0]);
        cgProfileImageError('');
        $('#your-profile #submit').prop('disabled',false);
    });

    var cg_input_image_upload_file_changed = false;

    $(document).on('click',"#cg_input_image_upload_file_to_delete_button",function (e) {
        cg_input_image_upload_file_changed = true;
        $('#cg_input_image_upload_file_to_delete_button').addClass('cg_hide');
        
        var $cg_input_image_upload_file_preview_img_existing = $('.cg_input_image_upload_file_preview_img_existing');
        if($cg_input_image_upload_file_preview_img_existing.length){
            $('#cgShowExistingProfileImage').removeClass('cg_hide');
        }
        $cg_input_image_upload_file_preview_img_existing.addClass('cg_hide');
        $('#cg_input_image_upload_file_to_delete_wp_id').prop('disabled',false);
        $('#cg_input_image_upload_file_preview_img').remove();
        $('#cg_input_image_upload_file_preview').addClass('cg_hide');
        $('#cg_input_image_upload_file').val('');
        cgJsClass.profileImage.cancel($('#cg_input_image_upload_file')[0]);
        cgProfileImageError('');
        $('#your-profile #submit').prop('disabled',false);
    });

    function cgProfileImageError(message) {
        var $input = $('#cg_input_image_upload_file'), $error = $('#cg_profile_image_error');
        if (!$error.length) { $error = $('<p class="cg_input_error" id="cg_profile_image_error" role="status" aria-live="polite">').insertAfter($input); }
        $error.text(message || '').toggle(!!message);
        $input.attr('aria-invalid', message ? 'true' : null).attr('aria-describedby', message ? 'cg_profile_image_error' : null);
    }

    $(document).on('change', '#cg_input_image_upload_file', function () {
        cg_input_image_upload_file_changed = true;
        var input = this, $form = $(input).closest('form'), $preview = $('#cg_input_image_upload_file_preview');
        cgProfileImageError('');
        $preview.find('.cg_input_image_upload_file_preview_img:not(.cg_input_image_upload_file_preview_img_existing)').remove();
        $preview.find('.cg_input_image_upload_file_preview_img_existing').removeClass('cg_hide');
        $form.find('#submit').prop('disabled', true);
        cgJsClass.profileImage.check(input).done(function (result) {
            if (result.stale || !document.contains(input)) { return; }
            var message = cgJsClass.profileImage.message(input, result.validation, result.message);
            cgProfileImageError(message);
            $form.find('#submit').prop('disabled', !!message || !!$form.find('.cg_input_error:visible').length);
            if (message || !result.file) { return; }
            var reader = new FileReader();
            reader.onload = function () {
                if (cgJsClass.profileImage.state(input) !== result) { return; }
                $preview.find('.cg_input_image_upload_file_preview_img_existing').addClass('cg_hide');
                $preview.append($('<div class="cg_input_image_upload_file_preview_img">').css('background', 'url("' + reader.result + '") no-repeat center center')).removeClass('cg_hide');
                $('#cgShowExistingProfileImage,#cg_input_image_upload_file_to_delete_button').removeClass('cg_hide');
                $('#cg_input_image_upload_file_to_delete_wp_id').prop('disabled', false);
            };
            reader.readAsDataURL(result.file);
        });
    });

    // not required to use this logic in the moment
   /* if(localStorage.getItem('cg_input_image_upload_file')=='changed'){

        var $cg_input_image_upload_file_preview = $('#cg_input_image_upload_file_preview');

        $cg_input_image_upload_file_preview.append(
                $(
                    '<div id="cg_input_image_upload_file_preview_skeleton_box">' +
                    '</div>'
                )
        ).removeClass('cg_hide');

            var i = 0;
            var interval = setInterval(function() {
                i = i+1;
                if(localStorage.getItem('cg_input_image_upload_file_src')){
                    $cg_input_image_upload_file_preview.find('#cg_input_image_upload_file_preview_skeleton_box').remove();
                    $cg_input_image_upload_file_preview.append(
                        jQuery('<div id="cg_input_image_upload_file_preview_img" />').css({
                            'background': 'url("' + localStorage.getItem('cg_input_image_upload_file_src') + '") no-repeat center center',
                            'display': 'none'
                        })
                    );
                    $cg_input_image_upload_file_preview.find('#cg_input_image_upload_file_preview_img').fadeIn();
                    localStorage.removeItem('cg_input_image_upload_file');
                    localStorage.removeItem('cg_input_image_upload_file_src')
                    clearInterval(interval);
                }else if(i>=10){
                    $cg_input_image_upload_file_preview.find('#cg_input_image_upload_file_preview_skeleton_box').remove();
                    localStorage.removeItem('cg_input_image_upload_file');
                    localStorage.removeItem('cg_input_image_upload_file_src');
                    clearInterval(interval);
                }
            },1000);
    }if(localStorage.getItem('cg_input_image_upload_file')=='deleted'){
        localStorage.removeItem('cg_input_image_upload_file');
        localStorage.removeItem('cg_input_image_upload_file_src');
    }*/

    function cgProfileFieldError($field) {
        var choices = $field.hasClass('cg_check') || $field.hasClass('cg_radio');
        var value = choices ? ($field.find('input:checked').length ? 'selected' : '') : String($field.val() || '').trim();
        if (!value) { return $field.hasClass('cg_input_field_required') ? $('#cg_language_required').val() : ''; }
        if (choices) { return ''; }
        var length = Array.from ? Array.from(value).length : value.length;
        var min = Number($field.attr('data-min-length')), max = Number($field.attr('data-max-length'));
        return min && length < min ? $field.attr('data-min-message') : (max && length > max ? $field.attr('data-max-message') : '');
    }

    $(document).on('change input','.cg_input_field_required,[data-min-length],[data-max-length]',function () {
        var $field = $(this), $form = $field.closest('form');
        $field.parent().find('.cg_input_error').remove();
        var message = cgProfileFieldError($field);
        if (message) { $('<p class="cg_input_error">').text(message).insertAfter($field); }
        $form.find('#submit').prop('disabled',!!$form.find('.cg_input_error:visible').length);
    });

    $(document).on('submit',"#your-profile",function (e) {

        var $form = $('#your-profile');
        var imageInput = $form.find('#cg_input_image_upload_file')[0];
        if (imageInput && imageInput.files.length) {
            var imageState = cgJsClass.profileImage.state(imageInput);
            if (!imageState) { $(imageInput).trigger('change'); imageState = cgJsClass.profileImage.state(imageInput); }
            var imageMessage = imageState.pending ? cgJsClass.profileImage.message(imageInput, {languageKey: 'pending'}) : cgJsClass.profileImage.message(imageInput, imageState.validation, imageState.message);
            if (imageMessage) {
                e.preventDefault(); cgProfileImageError(imageMessage);
                $form.find('.submit .spinner').removeClass('is-active');
                return;
            }
        }
        $form.find('.cg_input_error').remove();

        var hasError = false;

        // has to be done here already
        // remove not required action post, only one is always allowed
        $form.find('#post_cg_check_nickname_edit_profile').remove();

        $form.find('.cg_input_field_required,[data-min-length],[data-max-length]').each(function () {
            var message = cgProfileFieldError($(this));
            if (message) { $('<p class="cg_input_error">').text(message).insertAfter($(this)); hasError = true; }
        });

        if($('#cg_input_image_upload_file_required').length && ($('.cg_input_image_upload_file_preview_img_existing').hasClass('cg_hide') ||  !$('.cg_input_image_upload_file_preview_img_existing').length)){
            var $cg_input_image_upload_file = $( '#cg_input_image_upload_file' );
            var filename = $cg_input_image_upload_file.val().split('\\').pop();
            if(filename == 0){
                var errorMessage = $('#cg_language_ChooseYourImage').val();
                var $errorMessageContainer = $('<p class="cg_input_error" id="cg_input_error_ChooseYourImage">'+errorMessage+'</p>');
                if($('#cgShowExistingProfileImage').length){
                    $errorMessageContainer.insertAfter($('#cgShowExistingProfileImage'));
                }else{
                    $errorMessageContainer.insertAfter($( '#cg_input_image_upload_file' ));
                }
                hasError = true;
            }
        }
        debugger
        if(hasError){
            e.preventDefault();
            $form.find('#submit').prop('disabled',true);
            $form.find('.submit .spinner').removeClass('is-active');
            return;
        }

        if(cg_input_image_upload_file_changed){
            if($form.find('#cg_input_image_upload_file').val().length){
             //   localStorage.setItem('cg_input_image_upload_file','changed');
            }else{
         //       localStorage.setItem('cg_input_image_upload_file','deleted');
            }

            $form.find('input[name="action"]').prop('disabled',true);
            $form.find('#post_cg_backend_image_upload').prop('disabled',false);

            var form = document.getElementById('your-profile');
            var formPostData = new FormData(form);

            function imageUploadFailed(data) {
                e.preventDefault();
                $form.find('input[name="action"]').prop('disabled',false);
                $form.find('.submit .spinner').removeClass('is-active');
                var fallback = data && data.message || cgJsClass.profileImage.message(imageInput, {languageKey: 'invalid'});
                cgProfileImageError(cgJsClass.profileImage.message(imageInput, data && data.uploadValidation, fallback));
            }
            $.ajax({
                url: 'admin-ajax.php', method: 'post', data: formPostData, dataType: 'json',
                contentType: false, processData: false,
                async: false // The existing core profile submit follows only a successful image save.
            }).done(function (response) {
                if (!response || !response.success) { imageUploadFailed(response && response.data); return; }
                cgPrepareForSubmitProfileImageDependant($form);
            }).fail(function (xhr) {
                imageUploadFailed(xhr.responseJSON && xhr.responseJSON.data);
            });

        }else{
            cgPrepareForSubmitProfileImageDependant($form);
            //localStorage.removeItem('cg_input_image_upload_file');
        }

    });

    function cgPrepareForSubmitProfileImageDependant($form){
        $form.find('input[name="action"]').prop('disabled',false);// not forget to do this, might be disable after check!
        $form.find('#post_cg_backend_image_upload').remove();
        $form.find('#cg_input_image_upload_file').removeAttr('name').prop('disabled',true);// only disabled true because if true remove then visible
    }


    /*var $nickname      = $('#nickname');
    var $displayName   = $('#display_name');
    var originalDisplay = $('#cg1l_original_display_name').val();

    if (!originalDisplay) {
        return;
    }

    // Check if the nickname row is marked as invalid (WordPress style)
    var $nicknameRow = $nickname.closest('tr');
    var hasNicknameError = $nicknameRow.hasClass('form-invalid');

    // Optional: also detect inline error message text, if you want to be extra safe
    // var hasNicknameError = hasNicknameError || $nicknameRow.find('.error').length > 0;

    if (hasNicknameError) {
        // Restore original display_name instead of the temporary "invalid nickname" value
        $displayName.val(originalDisplay);
    }*/

});