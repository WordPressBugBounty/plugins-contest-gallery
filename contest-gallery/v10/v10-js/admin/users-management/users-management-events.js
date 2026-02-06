jQuery(document).ready(function($){

    $(document).on('click','#cgRegistrationSearchReset',function () {
        debugger
        $('#cgUsersManagementForm .cg_search_user_name').val('');
        $('#cgRegistrationSearchSubmit').click();
    });

    $(document).on('click','#cgUnconfirmedSearchReset',function () {
        debugger
        $('#cgUnconfirmedManagementForm .cg_search_user_name').val('');
        $('#cgUnconfirmedSearchSubmit').click();
    });

    $(document).on('click','#cg_create_user_data_csv_submit',function () {
        debugger
        //e.preventDefault();// no prevent default here!!!!

        var $cgUsersManagementForm = $('#cgUsersManagementForm');
        $cgUsersManagementForm.removeClass('cg_load_backend_submit');
        $cgUsersManagementForm.find('#cg_create_user_data_csv_new_export').removeAttr('disabled');
        $cgUsersManagementForm.find('#cgRegistrationSearchSubmit').click();

        setTimeout(function () {
            $cgUsersManagementForm.find('#cg_create_user_data_csv_new_export').attr('disabled','disabled');
            $cgUsersManagementForm.addClass('cg_load_backend_submit');
        },100);

    });

    $(document).on('click','#cg_input_image_upload_file_to_delete_button',function () {
        $(this).remove();
        $('#cg_input_image_upload_file_preview').remove();
        $('#cg_input_image_upload_file_to_delete_wp_id').prop('disabled',false);
        $('#cg_profile_image_removed').removeClass('cg_hide');
    });

    $(document).on('click','.cg_confirm.cg_close',function () {
        $(this).closest('#cgBackendGalleryDynamicMessage').find('.cg_message_close').click();
    });

    $(document).on('click','#cgUnconfirmedManagementList .cg_delete',function () {
        var mail = $(this).closest('tr').find('.cg_mail_data').text();
        var isRegistered = $(this).closest('tr').find('.cg_registered_not_confirmed').length;
        var isRegisteredString = '';
        if(isRegistered){
            isRegisteredString = '<br><span style="font-weight: normal;">(the original WordPress user will not be deleted)</span>';
        }
        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('<div>Delete unconfirmed user entry?<br><span style="font-weight: normal;">'+mail+'</span>'+isRegisteredString+'<br><br><div style="display: flex;justify-content: center;gap: 50px;"><div class="cg_confirm cg_close">No</div><div id="cgConfirmDeleteUnconfirmed" class="cg_confirm" data-cg-mail="'+mail+'">Yes</div></div></div>', undefined,undefined,undefined,);
    });

    $(document).on('click','#cgConfirmDeleteUnconfirmed',function () {

        var $el = $(this);
        var mail = $(this).attr('data-cg-mail');

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(undefined, undefined,undefined,undefined,true);

        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action: 'post_cg1l_delete_unconfirmed_mail',
                cg_mail: mail,
                cg_nonce: CG1LBackendNonce.nonce
            },
        }).done(function (response) {
            $el.closest('#cgBackendGalleryDynamicMessage').find('.cg_message_close').click();
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Unconfirmed user entry deleted',true);
            $('#cgUnconfirmedUserStepsSelect').trigger('change');
        }).fail(function (xhr, status, error) {

            var msg = 'Error saving user data: ' + (error || status);
            if (xhr.responseText) {
                msg += ' — ' + xhr.responseText;
            }
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(msg);

        }).always(function () {

        });
    });

    $(document).on('click', '#cgBackToUsersManagementList', function (e) {
        $('#cgRegUserStepsSelect').trigger('change');
    });

    var $cgUsersManagementListLoaderClone;

    $(document).on('change click', '#cgRegUserStepsSelect, #cgUnconfirmedUserStepsSelect, #cgUsersListSteps .cg_step_nav a,#cgUsersManagementList .cg_entry', function (e) {
        debugger
        e.preventDefault();
        var $el = $(this);
        var classList = $el.attr('class');
        console.log($el);

        console.log(classList);
        if (($el.attr('id')=='cgRegUserStepsSelect' || $el.attr('id')=='cgUnconfirmedUserStepsSelect') && e.type=='change') {
            var url = $(this).find('option:selected').attr('data-cg-href');
        } else if (($el.attr('id')=='cgRegUserStepsSelect' || $el.attr('id')=='cgUnconfirmedUserStepsSelect') && e.type=='click') {
            return;
        } else {
            var url = $(this).attr('href');
        }

        //$(this).closest('.cg_user_list_step').find('.cg_step_selected').removeClass('cg_step_selected');
        //$(this).closest('.cg_step').addClass('cg_step_selected');
        $('body').addClass('cg_pointer_events_none');

        var params = cgJsClassAdmin.index.functions.getParamsFromUrl(url);
        params.cg_nonce = CG1LBackendNonce.nonce;

        if($el.closest('#cgUnconfirmedUsersStepNav').length || $el.attr('id')=='cgUnconfirmedUserStepsSelect'){
            var $box = $('#cgUnconfirmedManagementList');
            params.action   = 'post_cg1l_get_unconfirmed_users';
        }else{
            params.action   = 'post_cg1l_get_management_show_users';
            var $box = $('#cgUsersManagementList');
        }
        var isCloneLoader = false;
        debugger
        if($box.find('tbody').length){
            cgJsClassAdmin.gallery.functions.createCgUsersManagementListLoaderClone($,$box);
            $cgUsersManagementListLoaderClone = $box.clone();
        }else{
            $box.replaceWith($cgUsersManagementListLoaderClone);
            isCloneLoader = true;
        }

        //console.log('params');
        //console.log(params);

        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'POST',
            dataType: 'html',
            data: params
        })
            .done(function (response) {
                debugger
                $('body').removeClass('cg_pointer_events_none');
                var $response = $(new DOMParser().parseFromString(response, 'text/html'));
                //console.log('cgUsersManagementList length');
                //console.log($response.find('#cgUsersManagementList').length);
                if($el.hasClass('cg_entry')){
                    $box.empty().append($response.find('#cgManagementShowUsers'));
                }else if($el.closest('#cgUnconfirmedUsersStepNav').length || $el.attr('id')=='cgUnconfirmedUserStepsSelect'){
                    $box.replaceWith($response.find('#cgUnconfirmedManagementList'));
                    $el.closest('#cgUnconfirmedListStepsContainer').replaceWith($response.find('#cgUnconfirmedListStepsContainer'));
                }else{
                    if(isCloneLoader){
                        $cgUsersManagementListLoaderClone.replaceWith($response.find('#cgUsersManagementList'));
                    }else{
                        $box.replaceWith($response.find('#cgUsersManagementList'));
                    }
                    $el.closest('#cgUserListStepsContainer').replaceWith($response.find('#cgUserListStepsContainer'));
                }
            })
            .fail(function (xhr, status, error) {
                $('body').removeClass('cg_pointer_events_none');
                var msg = 'Error saving user data: ' + (error || status);
                if (xhr.responseText) {
                    msg += ' — ' + xhr.responseText;
                }
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(msg);
                $('body').removeClass('cg_pointer_events_none');
                $button.removeClass('cg_skeleton_loader_on_page_load');
            });

    });

    $(document).on('click', '#cgRegistrationSearchSubmit, #cgUnconfirmedSearchSubmit, #cgRegUserAdminFormSubmit', function (e) {
        debugger
        var $el = $(this);
        var isExportData = false;

        if($el.attr('id')=='cgRegistrationSearchSubmit'){
            if(!$el.closest('form').hasClass('cg_load_backend_submit')){
                isExportData = true;
            }
        }
        if(isExportData){return;}

        e.preventDefault();// has to be done here if export

        var formPostData = new FormData($el.closest('form').get(0));

        var $button = $el.find('button[type="submit"]');
        $('body').addClass('cg_pointer_events_none');
        $button.addClass('cg_skeleton_loader_on_page_load');

        var actionUrl = $el.closest('form').attr('action');

        if($el.attr('id')=='cgRegistrationSearchSubmit'){
            var $box = $('#cgUsersManagementList');
            cgJsClassAdmin.gallery.functions.createCgUsersManagementListLoaderClone($,$box);
        }else if($el.attr('id')=='cgUnconfirmedSearchSubmit'){
            var $box = $('#cgUnconfirmedManagementList');
            cgJsClassAdmin.gallery.functions.createCgUsersManagementListLoaderClone($,$box);
        }

        var params = cgJsClassAdmin.index.functions.getParamsFromUrl(actionUrl);

        // append them to the FormData
        Object.keys(params).forEach(function (key) {
            formPostData.append(key, params[key]);
        });

        if($el.attr('id')=='cgUnconfirmedSearchSubmit'){
            formPostData.append('action', 'post_cg1l_get_unconfirmed_users');
        }else{
            formPostData.append('action', 'post_cg1l_get_management_show_users');
        }

        formPostData.append('cg_nonce',CG1LBackendNonce.nonce);
        formPostData.append('option_id',$el.attr('data-cg-gid'));
        formPostData.append('cg_form_submit',true);

        debugger
        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'POST',
            dataType: 'html',
            data: formPostData,
            processData: false, // don't serialize
            contentType: false  // let browser handle
        })
            .done(function (response) {
                debugger
                // console.log(response);
                var $response = $(new DOMParser().parseFromString(response, 'text/html'));

                if($el.attr('id')=='cgRegistrationSearchSubmit'){
                    $box.replaceWith($response.find('#cgUsersManagementList'));
                    $('#cgUsersManagementForm').replaceWith($response.find('#cgUsersManagementForm'));
                    $('#cgRegUserStepsSelect').replaceWith($response.find('#cgRegUserStepsSelect'));
                    $('#cgRegUsersStepNav').replaceWith($response.find('#cgRegUsersStepNav'));
                    $('body').removeClass('cg_pointer_events_none');
                }else if($el.attr('id')=='cgUnconfirmedSearchSubmit'){
                    $box.replaceWith($response.find('#cgUnconfirmedManagementList'));
                    $('#cgUnconfirmedUserStepsSelect').replaceWith($response.find('#cgUnconfirmedUserStepsSelect'));
                    $('#cgUnconfirmedUsersStepNav').replaceWith($response.find('#cgUnconfirmedUsersStepNav'));
                    $('body').removeClass('cg_pointer_events_none');
                }else{
                    cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Changes saved',true);
                    $('#cgRegUserStepsSelect').trigger('change');
                    //$button.removeClass('cg_skeleton_loader_on_page_load');
                    //console.log('cgRegUserAdminForm submit done');
                    //$cgUsersManagementListLoaderClone.replaceWith($response.find('#cgUsersManagementList').length);
                }
            })
            .fail(function (xhr, status, error) {
                var msg = 'Error saving user data: ' + (error || status);
                if (xhr.responseText) {
                    msg += ' — ' + xhr.responseText;
                }
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(msg);
                $('body').removeClass('cg_pointer_events_none');
                $button.removeClass('cg_skeleton_loader_on_page_load');
            });

    });


    var $cg_send_custom_mail_button;
    var $row;

    $(document).on('click','.cg_table_list .cg_resend',function (e) {

        cgJsClassAdmin.gallery.functions.showModal('#cgMailTemplateContainer');
        var $box = $('#cgMailTemplateContainer');
        $box.find('.cg_title_text').text('Resend registry mail');
        $box.find('.cg_send_title').text('Resend');
        $cg_send_custom_mail_button = $(this);
        $row = $(this).closest('tr');
        $box.find('#cgMailToSend').val($row.find('.cg_mail_data').text());
        $box.find('#cgCreateTemplateFromBody').addClass('cg_hide');

        var cgFromNameToSend = localStorage.getItem('cgFromNameToSendUnconfirmed');
        var cgReplyNameToSend = localStorage.getItem('cgReplyNameToSendUnconfirmed');
        var cgFromMailToSend = localStorage.getItem('cgFromMailToSendUnconfirmed');
        var cgReplyMailToSend = localStorage.getItem('cgReplyMailToSendUnconfirmed');
        var cgCcToSend = localStorage.getItem('cgCcToSendUnconfirmed');
        var cgBccToSend = localStorage.getItem('cgBccToSendUnconfirmed');
        var cgSubjectToSend = localStorage.getItem('cgSubjectToSendUnconfirmed');
        var cgRegUrlUnconfirmed = localStorage.getItem('cgRegUrlUnconfirmed');

        if(cgFromNameToSend){
            $box.find('#cgFromNameToSend').val(cgFromNameToSend);
        }else{
            $box.find('#cgFromNameToSend').val($box.find('#cgFromName').val());
        }

        if(cgReplyNameToSend){
            $box.find('#cgReplyNameToSend').val(cgReplyNameToSend);
        }else{
            $box.find('#cgReplyNameToSend').val($box.find('#cgReplyName').val());
        }
        if(cgFromMailToSend){
            $box.find('#cgFromMailToSend').val(cgFromMailToSend);
        }else{
            $box.find('#cgFromMailToSend').val($box.find('#cgFromMail').val());
        }
        if(cgReplyMailToSend){
            $box.find('#cgReplyMailToSend').val(cgReplyMailToSend);
        }else{
            $box.find('#cgReplyMailToSend').val($box.find('#cgReplyMail').val());
        }
        if(cgCcToSend){
            $box.find('#cgCcToSend').val(cgCcToSend);
        }else{
            $box.find('#cgCcToSend').val($box.find('#cgCc').val());
        }
        if(cgBccToSend){
            $box.find('#cgBccToSend').val(cgBccToSend);
        }else{
            $box.find('#cgBccToSend').val($box.find('#cgBcc').val());
        }
        if(cgSubjectToSend){
            $box.find('#cgSubjectToSend').val(cgSubjectToSend);
        }else{
            $box.find('#cgSubjectToSend').val($box.find('#cgSubject').val());
        }
        if(cgRegUrlUnconfirmed){
            $box.find('#cgRegUrlUnconfirmed').val(cgRegUrlUnconfirmed);
        }

        var $cgMailBodyToSendContainer = $box.find('#cgMailBodyToSendContainer');
        var valueToSet = '&nbsp;';
        if(!cgJsClassAdmin.gallery.vars.cgMailBodyToSendTinyMceUnconfirmed){
            cgJsClassAdmin.gallery.vars.cgMailBodyToSendTinyMceUnconfirmed = true;
            cgJsClassAdmin.index.functions.initializeEditor('cgMailBodyToSendUnconfirmed',true);
        }

    });

    $(document).on('click','#cgMailTemplateContainer.cg_unconfirmed .cg_send',function (e) {

        var gid = $('#cgBackendGalleryId').val();
        var $box = $(this).closest('#cgMailTemplateContainer');
        $box.find('.cg_required').addClass('cg_hide');
        var pid = $box.attr('data-cg-pid');
        cgJsClassAdmin.gallery.vars.cgEntryMailSent = false;
        var body = tinymce.get('cgMailBodyToSendUnconfirmed').getContent();
        var $cgMailToSend = $box.find('#cgMailToSend');
        var $cgFromNameToSend = $box.find('#cgFromNameToSend');
        var $cgReplyNameToSend = $box.find('#cgReplyNameToSend');
        var $cgReplyMailToSend = $box.find('#cgReplyMailToSend');
        var $cgFromMailToSend = $box.find('#cgFromMailToSend');
        var $cgCcToSend = $box.find('#cgCcToSend');
        var $cgBccToSend = $box.find('#cgBccToSend');
        var $cgSubjectToSend = $box.find('#cgSubjectToSend');
        var $cgRegUrlUnconfirmed = $box.find('#cgRegUrlUnconfirmed');

        var mail = $cgMailToSend.val();
        if(mail.trim()==''){
            $cgMailToSend.parent().find('.cg_required').removeClass('cg_hide');
            $cgMailToSend.get(0).scrollIntoView();
            return;
        }
        var fromName = $cgFromNameToSend.val();
        if(fromName.trim()==''){
            $cgFromNameToSend.parent().find('.cg_required').removeClass('cg_hide');
            $cgFromNameToSend.get(0).scrollIntoView();
            return;
        }
        var fromMail = $cgFromMailToSend.val();
        if(fromMail.trim()==''){
            $cgFromMailToSend.parent().find('.cg_required').removeClass('cg_hide');
            $cgFromMailToSend.get(0).scrollIntoView();
            return;
        }
        var pageUrl = $cgRegUrlUnconfirmed.val();
        if(pageUrl.trim()==''){
            $cgRegUrlUnconfirmed.parent().find('.cg_required').removeClass('cg_hide');
            $cgRegUrlUnconfirmed.get(0).scrollIntoView();
            return;
        }

        var replyName = $cgReplyNameToSend.val();
        var replyMail = $cgReplyMailToSend.val();
        var cc = $cgCcToSend.val();
        var bcc = $cgBccToSend.val();
        var subject = $cgSubjectToSend.val();
        if(subject.trim()==''){
            $cgSubjectToSend.parent().find('.cg_required').removeClass('cg_hide');
            $cgSubjectToSend.get(0).scrollIntoView();
            return;
        }

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(undefined, undefined,undefined,undefined,true);

        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action: 'post_cg1l_resend_unconfirmed_mail_backend',
                cg_gid: gid,
                cg_page: pageUrl,
                cg_mail: mail,
                cg_reply_mail: replyMail,
                cg_from_name: fromName,
                cg_reply_name: replyName,
                cg_from_mail: fromMail,
                cg_cc: cc,
                cg_bcc: bcc,
                cg_body: body,
                cg_subject: subject,
                cg_nonce: CG1LBackendNonce.nonce
            },
        }).done(function (response) {

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });
            debugger
            if(cgJsClassAdmin.gallery.vars.cgEntryMailSent){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Mail sent',true);
                var $mails = $row.find('.cg_mails').removeClass('cg_hide');
                var $b = $mails.find('b');
                var count = parseInt($b.text(), 10);
                $b.text(count + 1);
            }else{
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Error: mail could not be sent');
            }

            $box.find('.cg_message_close').click();

        }).fail(function (xhr, status, error) {

            console.log('sending mail not possible');
            console.log(xhr);
            console.log(status);
            console.log(error);

            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Error: '+xhr.responseText);

        }).always(function () {

        });

    });


    $(document).on('click','.cg_table_list .cg_mails',function (e) {

        var mail = $(this).closest('tr').find('.cg_mail_data').text();

        var $box = cgJsClassAdmin.gallery.functions.showModal('#cgMailsList');
        $box.find('.cg_row:not(.cg_row_loader,.cg_row_error)').remove();
        $box.find('.cg_row_error').addClass('cg_hide');
        $box.find('.cg_row_loader').removeClass('cg_hide');

        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action: 'post_cg_list_unconfirmed_mails',
                cg_mail: mail,
                cg_nonce: CG1LBackendNonce.nonce
            },
        }).done(function (response) {

            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });

            $box.find('.cg_row_content').removeClass('cg_hide');
            $box.find('.cg_row_error').addClass('cg_hide');
            $box.find('.cg_row_loader').addClass('cg_hide');

            cgJsClassAdmin.gallery.vars.cg_unconfirmed_mails.forEach(function (mail,i){

                var DateTime = "        <div class='cg_row cg_row_date'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    "+mail.DateTime+"\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var FromName = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgFromNameToSend"+i+"\">From Name</label>\n" +
                    "                    <input type=\"text\" id=\"cgFromNameToSend"+i+"\" value=\""+mail.FromName+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var FromMail = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgFromMailToSend"+i+"\">From Mail</label>\n" +
                    "                    <input type=\"text\" id=\"cgFromMailToSend"+i+"\" value=\""+mail.FromMail+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var ReplyName = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgReplyNameToSend"+i+"\">Reply Name</label>\n" +
                    "                    <input type=\"text\" id=\"cgReplyNameToSend"+i+"\" value=\""+mail.ReplyName+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var ReplyMail = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgReplyMailToSend"+i+"\">Reply mail</label>\n" +
                    "                    <input type=\"text\" id=\"cgReplyMailToSend"+i+"\" value=\""+mail.ReplyMail+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var ReceiverMail = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgReceiverMailToSend"+i+"\">To</label>\n" +
                    "                    <input type=\"text\" id=\"cgReceiverMailToSend"+i+"\" value=\""+mail.ReceiverMail+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var Cc = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgCcToSend"+i+"\">Cc</label>\n" +
                    "                    <input type=\"text\" id=\"cgCcToSend"+i+"\" value=\""+mail.Cc+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var Bcc = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgBccToSend"+i+"\">Bcc</label>\n" +
                    "                    <input type=\"text\" id=\"cgBccToSend"+i+"\" value=\""+mail.Bcc+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var Subject = "        <div class='cg_row'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgSubjectToSend"+i+"\">Subject</label>\n" +
                    "                    <input type=\"text\" id=\"cgSubjectToSend"+i+"\" value=\""+mail.Subject+"\" readonly />\n" +
                    "                </div>\n" +
                    "        </div>\n";
                var Body = "        <div class='cg_row cg_row_body'>\n" +
                    "               <div class='cg_row_col '>\n" +
                    "                    <label for=\"cgBodyToSend"+i+"\">Body</label>\n" +
                    "                    <div id=\"cgBodyToSend"+i+"\" class='cg_body_selectable'>"+mail.Body+"</div>\n" +
                    "                </div>\n" +
                    "        </div>\n";
                $box.append(DateTime,FromName,FromMail,ReplyName,ReplyMail,ReceiverMail,Cc,Bcc,Subject,Body);
            });

        }).fail(function (xhr, status, error) {

            $box.find('.cg_row_loader').addClass('cg_hide');
            $box.find('.cg_row_error').removeClass('cg_hide');

            console.log('creating template not possible');
            console.log(xhr);
            console.log(status);
            console.log(error);

            $box.find('.cg_row_error_message').text(xhr.responseText);

        }).always(function () {

        });

    });

    $(document).on('input','#cgMailTemplate #cgRegUrlUnconfirmed',function (e) {
        var $this = $(this);
        clearTimeout(window._cgRegUrlUnconfirmed);
        window._cgRegUrlUnconfirmed = setTimeout(function () {
            localStorage.setItem('cgRegUrlUnconfirmed', $this.val());
        }, 300);
        if($this.val().trim()==''){
            $this.parent().find('.cg_required').removeClass('cg_hide');
        }else{
            $this.parent().find('.cg_required').addClass('cg_hide');
        }
    });

    $(document).on('input','#cgMailBodyToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cglSaveTimeout);
        window._cglSaveTimeout = setTimeout(function () {
            var content = $this.val();
            localStorage.setItem('cgMailBodyToSend', content);
        }, 300);
    });

});