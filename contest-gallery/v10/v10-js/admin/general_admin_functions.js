jQuery(document).ready(function($){

    // copy show tooltip

    $(document).on('mouseenter','.cg_tooltip',function () {

        if($(this).closest('.cg_shortcode_parent').attr('id')=='cgDeleteZipFileHintContainer'){
            $(this).append('<span class="cg_tooltiptext" style="width: 140px;margin-left:-71px;">Copy zip file link</span>');
        }else if($(this).hasClass('td_gallery_info_name_edit')){
            $(this).append('<span class="cg_tooltiptext">Edit gallery name</span>');
        }else if($(this).hasClass('td_gallery_translation_edit')){
            $(this).append('<span class="cg_tooltiptext">Copy translation</span>');
        }else if($(this).hasClass('td_gallery_info_shortcode_conf')){
            $(this).append('<span class="cg_tooltiptext">Shortcode interval configuration</span>');
        }else if($(this).hasClass('td_gallery_info_shortcode_conf_status_on')){
            $(this).append('<span class="cg_tooltiptext">Shortcode is on within interval</span>');
        }else if($(this).hasClass('td_gallery_info_shortcode_conf_status_off')){
            $(this).append('<span class="cg_tooltiptext">Shortcode is off out of interval</span>');
        }else if($(this).hasClass('cg_copy_param')){
            $(this).append('<span class="cg_tooltiptext">Copy</span>');
        }else{
            $(this).append('<span class="cg_tooltiptext">Copy shortcode</span>');
        }

        debugger

    });

    $(document).on('mouseleave','.cg_tooltip',function () {
        $(this).find('.cg_tooltiptext').remove();
    });

    $(document).on('click','.cg_tooltip:not(.td_gallery_info_shortcode_conf)',function (e) {
        debugger
        if($(this).hasClass('td_gallery_translation_edit')){
            var $containerWithToCopyValue = $(this).parent().clone();
            $containerWithToCopyValue.find('span,br').remove();
        } else if($(this).closest('.cg_entry_pages').length){
            var $containerWithToCopyValue = $(this).parent();
        } else if($(this).is('.td_gallery_info_name_span, .td_gallery_info_name_span_box, .cg_copy_param')){
            var $containerWithToCopyValue = $(this);
        } else if($(this).hasClass('cg_shortcode_copy_mail_confirm')){
            var $containerWithToCopyValue = $(this).parent().clone();
            $containerWithToCopyValue.find('.cg_shortcode_copy').remove();
        } else if($(this).closest('.td_gallery_info_shortcode').length){
            var $containerWithToCopyValue = $(this).closest('.td_gallery_info_shortcode').find('.td_gallery_info_name_span');
        }else if($(this).is('code')){
            var $containerWithToCopyValue = $(this).find('span:first-child');
        }else{
            var $containerWithToCopyValue = $(this).parent().find('.cg_shortcode_copy_text');
        }

        if($containerWithToCopyValue.is('input')){
            var copyText = $containerWithToCopyValue.val().trim();
        } else if($containerWithToCopyValue.attr('data-cg-shortcode')){
            var copyText = $containerWithToCopyValue.attr('data-cg-shortcode').trim();
        }else if($containerWithToCopyValue.is('.td_gallery_info_name_span, .td_gallery_info_name_span_box, .cg_copy_param')){
            var copyText = $containerWithToCopyValue.get(0).firstChild.nodeValue;
        }else{
            var copyText = $containerWithToCopyValue.text().trim();
        }

        var el = document.createElement('textarea');
        el.value = copyText;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);

        if(!$(this).hasClass('td_gallery_info_name_edit')){// otherwise will be forwarded to edit options page
            $(this).find('.cg_tooltiptext').text('Copied');
        }

    });


    var cgCopyTextToClipboard = function(text, done, failed){
        var textarea;

        if(navigator.clipboard && navigator.clipboard.writeText){
            navigator.clipboard.writeText(text).then(done, failed);
            return;
        }

        textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', 'readonly');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.top = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try{
            if(document.execCommand('copy')){
                done();
            }else{
                failed();
            }
        }catch(e){
            failed();
        }

        document.body.removeChild(textarea);
    };

    cgJsClassAdmin.mailDelivery = {
        copy: cgCopyTextToClipboard,
        update: function($card, reset){
            var $scope = $card.closest('.cg_view_options_rows_container, #cgMailTemplateContainer');
            var values = [];
            $.each(['from', 'reply', 'to'], function(index, key){
                var selector = $card.attr('data-cg-mail-' + key);
                var value = selector ? ($scope.find(selector).first().val() || '') : ($card.attr('data-cg-mail-recipient') || '');
                var $row = $card.find('[data-cg-mail-value="' + key + '"]');
                values.push(value);
                $row.text($.trim(value) || $row.attr('data-cg-mail-empty'));
            });
            var snapshot = JSON.stringify(values);
            if(reset || typeof $card.data('cgMailInitial') === 'undefined'){
                $card.data('cgMailInitial', snapshot);
            }
            var dirty = $card.attr('data-cg-mail-mode') === 'options' && snapshot !== $card.data('cgMailInitial');
            $card.find('.cg-mail-delivery-dirty').prop('hidden', !dirty);
        },
        init: function($scope){
            $scope.find('.cg-mail-delivery-card').each(function(){
                var $card = $(this);
                cgJsClassAdmin.mailDelivery.update($card, true);
            });
        }
    };

    $(document).on('input change', '#cg_main_options_content input, #cgMailTemplateContainer input', function(){
        var input = this;
        $(this).closest('#cg_main_options_content, #cgMailTemplateContainer').find('.cg-mail-delivery-card').each(function(){
            var $card = $(this);
            var $scope = $card.closest('.cg_view_options_rows_container, #cgMailTemplateContainer');
            var affected = false;
            $.each(['from', 'reply', 'to'], function(index, key){
                var selector = $card.attr('data-cg-mail-' + key);
                if(selector && $scope.find(selector).is(input)){affected = true;}
            });
            if(affected){
                cgJsClassAdmin.mailDelivery.update($card);
            }
        });
    });

    $(document).on('click', '.cg_nginx_protection_toggle', function(){
        var $button = $(this);
        var $notice = $button.closest('.cg_nginx_protection_notice');
        var $details = $notice.find('.cg_nginx_protection_details').first();
        var isOpen = $details.hasClass('cg_hide');

        $details.toggleClass('cg_hide', !isOpen);
        $button.attr('aria-expanded', isOpen ? 'true' : 'false');
    });

    $(document).on('click', '.cg_nginx_protection_copy_rule', function(e){
        var $button = $(this);
        var $notice = $button.closest('.cg_nginx_protection_notice');
        var $status = $notice.find('.cg_nginx_protection_copy_status').first();
        var originalText = $button.text();
        var rule = $notice.find('.cg_nginx_protection_rule').first().text();

        e.preventDefault();
        $button.prop('disabled', true).text('Copying...');
        $status.text('');

        cgCopyTextToClipboard(rule, function(){
            $button.text('Copied');
            $status.text('Nginx rule copied');
            setTimeout(function(){
                $button.prop('disabled', false).text(originalText);
                $status.text('');
            }, 1400);
        }, function(){
            $button.prop('disabled', false).text(originalText);
            $status.text('Copy failed');
        });
    });


    // show cg-info

    $(document).on('mouseenter','.cg-info-icon',function () {
        $(this).parent().find('.cg-info-container').first().show();
    });

    $(document).on('mouseleave','.cg-info-icon',function () {

        $(this).parent().find('.cg-info-container').first().hide();

    });



});
