jQuery(document).ready(function ($) {

   var $cg_send_custom_mail_button;
   var $cgSortableDiv;

    $(document).on('click','.cgSortableDiv .cg_send_custom_mail, .cgSortableDiv .cg_activate_send_custom_mail, .cgSortableDiv .cg_deactivate_send_custom_mail',function (e) {
        cgJsClassAdmin.gallery.functions.showModal('#cgMailTemplateContainer');
        debugger
        $cg_send_custom_mail_button = $(this);
        var $galleryForm = $(this).closest('form');
        $cgSortableDiv = $(this).closest('.cgSortableDiv');
        var $box = $('#cgMailTemplateContainer');
        $box.attr('data-cg-pid',$cgSortableDiv.attr('data-cg-real-id'));
        $box.find('.cg_entry_id').text('('+$cgSortableDiv.find('.cg_entry_id').text()+')');

        var cgFromNameToSend = localStorage.getItem('cgFromNameToSend');
        var cgReplyNameToSend = localStorage.getItem('cgReplyNameToSend');
        var cgFromMailToSend = localStorage.getItem('cgFromMailToSend');
        var cgReplyMailToSend = localStorage.getItem('cgReplyMailToSend');
        var cgCcToSend = localStorage.getItem('cgCcToSend');
        var cgBccToSend = localStorage.getItem('cgBccToSend');
        var cgSubjectToSend = localStorage.getItem('cgSubjectToSend');

        if($cg_send_custom_mail_button.hasClass('cg_send_custom_mail')){
            $box.find('.cg_title_text').text('Send custom mail');
            $box.find('.cg_send_title').text('Send');
        }

        if($cg_send_custom_mail_button.hasClass('cg_activate_send_custom_mail')){
            $box.find('.cg_title_text').text('Activate and send custom mail');
            $box.find('.cg_send_title').text('Activate and send');
        }
        if($cg_send_custom_mail_button.hasClass('cg_deactivate_send_custom_mail')){
            $box.find('.cg_title_text').text('Deactivate and send custom mail');
            $box.find('.cg_send_title').text('Deactivate and send');
        }

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
        }
        if(cgBccToSend){
            $box.find('#cgBccToSend').val(cgBccToSend);
        }
        if(cgSubjectToSend){
            $box.find('#cgSubjectToSend').val(cgSubjectToSend);
        }

        var $cgMailBodyToSendContainer = $box.find('#cgMailBodyToSendContainer');
        var valueToSet = '&nbsp;';
        if(!cgJsClassAdmin.gallery.vars.cgMailBodyToSendTinyMce){
            cgJsClassAdmin.gallery.vars.cgMailBodyToSendTinyMce = true;
            cgJsClassAdmin.index.functions.initializeEditor('cgMailBodyToSend',true);
        }
        var $cgMailFields = $box.find('#cgMailFields');
        $cgMailFields.empty();
        if($cgSortableDiv.find('.cg_wp_user_id').length){
            $cgMailFields.append('<div id="cgMailFirstName"><div><span class="cg_field_param">$wp_first_name$</span> First Name</div></div>');
            $cgMailFields.append('<div id="cgMailLastName"><div><span class="cg_field_param">$wp_last_name$</span> Last Name</div></div>');
        }
        var mailTo = $cgSortableDiv.find('.cg_email_to_send').val();
        var $cgMailToSend = $box.find('#cgMailToSend');
        cgJsClassAdmin.gallery.functions.mailDomainCheck($,$cgMailToSend);
        $cgMailToSend.val(mailTo);
        $cgSortableDiv.find('.cg_fields_div > div:not(.cg_exif_data_container)').each(function () {
            var textString = '';
            var textNode = $(this).contents().filter(function() {
                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
            }).first();
            if (textNode.length) {
                textString = $.trim(textNode[0].nodeValue);
                textString = textString.slice(0, -1);
            }
           if($(this).hasClass('cg_image_title_container')){
               var valueToSet = $(this).find('.cg_input_vars_count').val();
               if($(this).find('.cg_category_select').length){
                   valueToSet = $(this).find('.cg_category_select option:selected').text();
               }
               $cgMailFields.append('<div><div><span class="cg_field_param">$id_'+$(this).attr('data-cg-field-id')+'$</span> '+textString+'</div></div>');
           }
           if($(this).hasClass('cg_image_description_container')){
               var content = $(this).find('.cg_input_vars_count').text().replace(/\n/g, '<br>');
               $cgMailFields.append('<div><div><span class="cg_field_param">$id_'+$(this).attr('data-cg-field-id')+'$</span> '+textString+'</div></div>');
           }
        });

        if($cgSortableDiv.find('.cg_entry_pages').length){
            $cgMailFields.append('<div><div><span class="cg_field_param">$cg_gallery$</span> Entry URL</div></div>');
            $cgMailFields.append('<div><div><span class="cg_field_param">$cg_gallery_user$</span> Entry URL</div></div>');
            $cgMailFields.append('<div><div><span class="cg_field_param">$cg_gallery_no_voting$</span> Entry URL</div></div>');
            $cgMailFields.append('<div><div><span class="cg_field_param">$cg_gallery_winner$</span> Entry URL</div></div>');
            $cgMailFields.append('<div><div><span class="cg_field_param">$cg_gallery_ecommerce$</span> Entry URL</div></div>');
        }

        if(!cgJsClassAdmin.gallery.vars.cgMailTemplatesLoaded){
            jQuery.ajax({
                url: 'admin-ajax.php',
                type: 'post',
                data: {
                    action: 'post_cg_list_mail_templates',
                    cg_wp_user_id: WpUserId,
                    cg_nonce: CG1LBackendNonce.nonce
                },
            }).done(function (response) {

                var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

                $response.find('script[data-cg-processing="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });

                var $cgMailTemplates = $('#cgMailTemplates');

                cgJsClassAdmin.gallery.vars.cg_mail_templates.forEach(function(template){
                    $cgMailTemplates.append(cgJsClassAdmin.gallery.functions.createMailTemplateButton(template.id,template.Name));
                });

            }).fail(function (xhr, status, error) {

                console.log(xhr);
                console.log(status);
                console.log(error);

            }).always(function () {

            });
        }

        return;

        var WpUserId = 0;
        if($cgSortableDiv.find('.cg_wp_user_id').length){
            WpUserId = $cgSortableDiv.find('.cg_wp_user_id').val();
        }
        if(WpUserId && cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId]){
            $cgMailFields.find('#cgMailFirstName').text(cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].firstName);
            $cgMailFields.find('#cgMailLastName').text(cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].lastName);
        }else if(WpUserId && !cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId]){

            jQuery.ajax({
                url: 'admin-ajax.php',
                type: 'post',
                data: {
                    action: 'post_cg_get_wp_user_meta',
                    cg_wp_user_id: WpUserId,
                    cg_nonce: CG1LBackendNonce.nonce
                },
            }).done(function (response) {

                var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

                $response.find('script[data-cg-processing="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });

                $cgMailFields.find('#cgMailFirstName, #cgMailLastName').removeClass('cg_skeleton');

                $cgMailFields.find('#cgMailFirstName>div:nth-child(2)').text(cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].firstName);
                $cgMailFields.find('#cgMailLastName>div:nth-child(2)').text(cgJsClassAdmin.gallery.vars.WpUsersData[WpUserId].lastName);

            }).fail(function (xhr, status, error) {

                console.log(xhr);
                console.log(status);
                console.log(error);

            }).always(function () {

            });

        }else{
            $cgMailFields.find('#cgMailFirstName, #cgMailLastName').removeClass('cg_skeleton').addClass('cg_disabled_background_color_e0e0e0').find('>div:nth-child(2)').text('Not available');
        }
    });

    var $cgMailBodyToSend = null;

    // Track which textarea is focused
    $(document).on('focus', '#cgMailBodyToSend', function() {
        $cgMailBodyToSend = $(this);
    });

    // When clicking on one of your "insert fields" on the right
    $(document).on('click', '#cgMailFields > div', function(e) {

        var insertText = $(this).find('.cg_field_param').text(); // or .data('value') if you store the token
        var $editor = tinymce.get('cgMailBodyToSend');

        if ($editor && !$editor.isHidden()) {
            // Focus the TinyMCE editor
            $editor.focus();

            // Insert text exactly where the cursor is
            $editor.selection.setContent(insertText);

            // Keep cursor after inserted text
            var rng = $editor.selection.getRng();
            rng.setStart(rng.endContainer, rng.endOffset);
            $editor.selection.setRng(rng);
        }else{

            if (!$cgMailBodyToSend || !$cgMailBodyToSend.length) {
                $cgMailBodyToSend = $(this).closest('#cgMailTemplateContainer').find('#cgMailBodyToSend');
            }

            var textarea = $cgMailBodyToSend.get(0);

            // Get current cursor position
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var value = $cgMailBodyToSend.val();

            // Insert text at cursor
            $cgMailBodyToSend.val(value.substring(0, start) + insertText + value.substring(end));

            // Move cursor after inserted text
            textarea.selectionStart = textarea.selectionEnd = start + insertText.length;

            // Refocus the textarea
            textarea.focus();
        }
        clearTimeout(window._cgSaveTimeout);
        window._cgSaveTimeout = setTimeout(function () {
            var content = '';
            if ($editor && !$editor.isHidden()) {
                content = $editor.getContent();
            } else if ($cgMailBodyToSend && $cgMailBodyToSend.length) {
                content = $cgMailBodyToSend.val();
            }
            localStorage.setItem('cgMailBodyToSend', content);
        }, 300);
    });

    $(document).on('click', '#cgCreateTemplateFromBody', function() {
        var $box = $('#cgCreateTemplateFromBodyContainer');
        cgJsClassAdmin.gallery.functions.showModal('#'+$box.attr('id'),true);
        $box.find('.cg_sel_title.cg_new').removeClass('cg_hide');
        $box.find('.cg_sel_title.cg_edit_template').addClass('cg_hide');
        $box.find('.cg_row_error,.cg_row_loader').addClass('cg_hide');
        $box.find('.cg_template_id').remove();
        $box.find('#cgTemplateName').val('');
        $box.find('.cg_send').text('Create');
    });

    var $cg_template;

    $(document).on('click', '#cgMailTemplates .cg_edit_template', function() {
        var $box = $('#cgCreateTemplateFromBodyContainer');
        $cg_template = $(this).closest('.cg_template');
        var id = $cg_template.attr('data-cg-id');
        cgJsClassAdmin.gallery.functions.showModal('#'+$box.attr('id'),true);
        $box.find('.cg_sel_title.cg_new').addClass('cg_hide');
        $box.find('.cg_sel_title.cg_edit_template').removeClass('cg_hide');
        $box.find('#cgTemplateName').val($(this).closest('.cg_template').find('>div:nth-child(1)').text());
        $box.find('.cg_row_error,.cg_row_loader').addClass('cg_hide');
        $box.find('.cg_template_id').remove();
        $box.prepend('<input type="hidden" class="cg_template_id" value='+id+' />');
        $box.find('.cg_send').text('Save');
    });

    $(document).on('click', '#cgMailTemplates .cg_delete', function() {
        var $templateButtonBox = $(this).closest('.cg_template');
        var id = $templateButtonBox.attr('data-cg-id');
        debugger
        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(undefined, undefined,undefined,undefined,true);

        setTimeout(function (){
            jQuery.ajax({
                url: 'admin-ajax.php',
                type: 'post',
                data: {
                    action: 'post_cg_delete_mail_template',
                    cg_id: id,
                    cg_nonce: CG1LBackendNonce.nonce
                },
            }).done(function (response) {

                var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

                $response.find('script[data-cg-processing="true"]').each(function () {
                    var script = jQuery(this).html();
                    eval(script);
                });
                debugger
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Template deleted',true);
                $templateButtonBox.remove();

            }).fail(function (xhr, status, error) {

                console.log('deleting template not possible');
                console.log(xhr);
                console.log(status);
                console.log(error);

                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Error: '+xhr.responseText);

            }).always(function () {

            });
        },800);

    });

    $(document).on('click','#cgCreateTemplateFromBodyContainer .cg_send',function (e) {
        debugger
        var gid = $('#cgBackendGalleryId').val();
        var $mail = $('#cgMailTemplateContainer');
        var pid = $mail.attr('data-cg-pid');
        var fromName = $mail.find('#cgFromNameToSend').val();
        var replyName = $mail.find('#cgReplyNameToSend').val();
        var replyMail = $mail.find('#cgReplyMailToSend').val();
        var fromMail = $mail.find('#cgFromMailToSend').val();
        var mail = $mail.find('#cgMailToSend').val();
        var cc = $mail.find('#cgCcToSend').val();
        var bcc = $mail.find('#cgBccToSend').val();
        var subject = $mail.find('#cgSubjectToSend').val();
        var body = tinymce.get('cgMailBodyToSend').getContent();

        var $box = $(this).closest('#cgCreateTemplateFromBodyContainer');
        var name = $box.find('#cgTemplateName').val();
        var id = 0;
        if($box.find('.cg_template_id').length){
            id = $box.find('.cg_template_id').val();
        }
        $box.find('.cg_row_content').addClass('cg_hide');
        $box.find('.cg_row_error').addClass('cg_hide');
        $box.find('.cg_row_loader').removeClass('cg_hide');
        debugger
        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action: 'post_cg_create_or_update_mail_template',
                cg_template_id: id,
                cg_gid: gid,
                cg_pid: pid,
                cg_name: name,
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
            debugger
            var $response = jQuery(new DOMParser().parseFromString(response, 'text/html'));

            $response.find('script[data-cg-processing="true"]').each(function () {
                var script = jQuery(this).html();
                eval(script);
            });

            $box.find('.cg_row_content').removeClass('cg_hide');
            $box.find('.cg_row_error').addClass('cg_hide');
            $box.find('.cg_row_loader').addClass('cg_hide');

            var $newTemplate = cgJsClassAdmin.gallery.functions.createMailTemplateButton(cgJsClassAdmin.gallery.vars.cg_mail_template_id,name,true);
            var message = 'Template created';

            if(id){
                $cg_template.replaceWith($newTemplate);
                message = 'Template edited';
            }else{
                $('#cgMailTemplates').prepend($newTemplate);
            }

            $box.find('.cg_message_close').click();

            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(message,true);

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

    $(document).on('click','#cgMailTemplateContainer:not(.cg_unconfirmed) .cg_send',function (e) {
        debugger
        var gid = $('#cgBackendGalleryId').val();
        var $cgMailTemplateContainer = $(this).closest('#cgMailTemplateContainer');
        $cgMailTemplateContainer.find('.cg_required').addClass('cg_hide');
        var pid = $cgMailTemplateContainer.attr('data-cg-pid');
        cgJsClassAdmin.gallery.vars.cgEntryMailSent = false;
        var body = tinymce.get('cgMailBodyToSend').getContent();
        var $cgMailToSend = $cgMailTemplateContainer.find('#cgMailToSend');
        var $cgFromNameToSend = $cgMailTemplateContainer.find('#cgFromNameToSend');
        var $cgReplyNameToSend = $cgMailTemplateContainer.find('#cgReplyNameToSend');
        var $cgReplyMailToSend = $cgMailTemplateContainer.find('#cgReplyMailToSend');
        var $cgFromMailToSend = $cgMailTemplateContainer.find('#cgFromMailToSend');
        var $cgCcToSend = $cgMailTemplateContainer.find('#cgCcToSend');
        var $cgBccToSend = $cgMailTemplateContainer.find('#cgBccToSend');
        var $cgSubjectToSend = $cgMailTemplateContainer.find('#cgSubjectToSend');

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

        var mailType = 0;

        if($cg_send_custom_mail_button.hasClass('cg_activate_send_custom_mail')){
            mailType = 1;
            $cgSortableDiv.find('.cg_image_checkbox_activate').click();
        }
        if($cg_send_custom_mail_button.hasClass('cg_deactivate_send_custom_mail')){
            mailType = 2;
            $cgSortableDiv.find('.cg_image_checkbox_deactivate').click();
        }

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(undefined, undefined,undefined,undefined,true);

        cgJsClassAdmin.gallery.reload.entry(pid,true, undefined, undefined,undefined,undefined,undefined,undefined,cgJsClassAdmin.gallery.functions.callBackSendCustomMail,[gid,pid,mail,replyName,replyMail,fromName,fromMail,cc,bcc,body,subject,mailType],true,undefined,true);

    });

    $(document).on('click','.cgSortableDiv .cg_sent_mails',function (e) {

        var $cgSortableDiv = $(this).closest('.cgSortableDiv');
        var pid = $cgSortableDiv.attr('data-cg-real-id');

        var $box = cgJsClassAdmin.gallery.functions.showModal('#cgMailsList');
        $box.find('.cg_entry_id').text('('+$cgSortableDiv.find('.cg_entry_id').text()+')');
        $box.find('.cg_row:not(.cg_row_loader,.cg_row_error)').remove();
        $box.find('.cg_row_error').addClass('cg_hide');
        $box.find('.cg_row_loader').removeClass('cg_hide');

        jQuery.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            data: {
                action: 'post_cg_list_entry_mails',
                cg_pid: pid,
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

            cgJsClassAdmin.gallery.vars.cg_entry_mails.forEach(function (mail,i){

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

    $(document).on('click','#cgMailTemplateContainer .cg_template .cg_select',function (e) {

        var $box = $(this).closest('#cgMailTemplateContainer');
        var id = $(this).attr('data-cg-id');
        var row = cgJsClassAdmin.gallery.vars.cg_mail_templates.find(t => t.id == id);

        $box.find('#cgFromNameToSend').val(row.FromName);
        $box.find('#cgReplyNameToSend').val(row.ReplyName);
        $box.find('#cgFromMailToSend').val(row.FromMail);
        $box.find('#cgReplyMailToSend').val(row.ReplyMail);
        $box.find('#cgCcToSend').val(row.Cc);
        $box.find('#cgBccToSend').val(row.Bcc);
        $box.find('#cgSubjectToSend').val(row.Subject);
        tinymce.get('cgMailBodyToSend').setContent(row.Body);

        $box.find('#cgFromNameToSend, #cgReplyNameToSend, #cgFromMailToSend,' +
            ' #cgReplyMailToSend, #cgCcToSend, #cgBccToSend, #cgSubjectToSend').trigger('input');

    });

    $(document).on('input','#cgMailTemplate #cgFromNameToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgFromNameToSend);
        window._cgFromNameToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgFromNameToSend'+suffix, $this.val());
        }, 300);
        if($this.val().trim()==''){
            $this.parent().find('.cg_required').removeClass('cg_hide');
        }else{
            $this.parent().find('.cg_required').addClass('cg_hide');
        }
    });

    $(document).on('input','#cgMailTemplate #cgFromMailToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgFromMailToSend);
        window._cgFromMailToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgFromMailToSend'+suffix, $this.val());
        }, 300);
        if($this.val().trim()==''){
            $this.parent().find('.cg_required').removeClass('cg_hide');
        }else{
            $this.parent().find('.cg_required').addClass('cg_hide');
        }
    });

    $(document).on('input','#cgMailTemplate #cgReplyNameTo.Send',function (e) {
        var $this = $(this);
        clearTimeout(window._cgReplyNameToSend);
        window._cgReplyNameToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgReplyNameToSend'+suffix, $this.val());
        }, 300);
    });

    $(document).on('input','#cgMailTemplate #cgReplyMailToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgReplyMailToSend);
        window._cgReplyMailToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgReplyMailToSend'+suffix, $this.val());
        }, 300);
    });

    $(document).on('input','#cgMailTemplate #cgCcToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgCcToSend);
        window._cgCcToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgCcToSend'+suffix, $this.val());
        }, 300);
    });

    $(document).on('input','#cgMailTemplate #cgBccToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgBccToSend);
        window._cgBccToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgBccToSend'+suffix, $this.val());
        }, 300);
    });

    $(document).on('input','#cgMailTemplate #cgSubjectToSend',function (e) {
        var $this = $(this);
        clearTimeout(window._cgSubjectToSend);
        window._cgSubjectToSend = setTimeout(function () {
            var suffix = '';
            if($this.closest('.cg_unconfirmed').length){
                suffix = 'Unconfirmed';
            }
            localStorage.setItem('cgSubjectToSend'+suffix, $this.val());
        }, 300);
        if($this.val().trim()==''){
            $this.parent().find('.cg_required').removeClass('cg_hide');
        }else{
            $this.parent().find('.cg_required').addClass('cg_hide');
        }
    });

    $(document).on('input','#cgMailTemplate #cgMailToSend',function (e) {
        var $this = $(this);
        cgJsClassAdmin.gallery.functions.mailDomainCheck($,$(this));
        if($this.val().trim()==''){
            $this.parent().find('.cg_required').removeClass('cg_hide');
        }else{
            $this.parent().find('.cg_required').addClass('cg_hide');
        }
    });

    $(document).on('input','#cgMailBodyToSendUnconfirmed',function (e) {
        var $this = $(this);
        clearTimeout(window._cglSaveTimeout);
        window._cglSaveTimeout = setTimeout(function () {
            var content = $this.val();
            localStorage.setItem('cgMailBodyToSendUnconfirmed', content);
        }, 300);
    });

});