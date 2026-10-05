jQuery(document).ready(function($){
    var $modal = $('#cgMailTestContainer');
    if(!$modal.length){return;}
    var $backdrop = $('#cgMailTestBackdrop'), opener, pending = false, report = '', validationStarted = false;
    var fieldLabels = {from: 'From', reply: 'Reply-To', to: 'Recipient'};

    function setStatus(message, state){
        $modal.find('.cg-mail-test-status').text(message).attr('data-status', state || 'info').prop('hidden', !message);
    }

    function clearResult(){
        report = '';
        $modal.find('.cg-mail-test-result').prop('hidden', true).removeAttr('data-status');
        $modal.find('.cg-mail-test-result-title, .cg-mail-test-summary, .cg-mail-test-tips, .cg-mail-test-details, .cg-mail-test-copy-status').empty();
        $modal.find('.cg-mail-test-copy-fallback').prop('hidden', true).find('textarea').val('');
    }

    function fieldError(input){
        var raw = input.value, value = $.trim(raw), parts = value.split('@');
        if(!value){return fieldLabels[input.name] + ' is required.';}
        // Match WordPress email syntax and reject malformed local parts and overlong domain labels.
        var valid = value.length >= 6 && raw.length <= 254 && !/[\r\n\x00]/.test(raw) &&
            /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+$/.test(value);
        if(valid){
            valid = parts[0].length <= 64 && !/^\.|\.$|\.\./.test(parts[0]);
            $.each(parts[1].split('.'), function(i, label){
                if(label.length > 63 || /^-|-$/.test(label)){valid = false;}
            });
        }
        return valid ? '' : fieldLabels[input.name] + ': enter one valid email address (name@example.com).';
    }

    function markField(input, message){
        $(input).attr('aria-invalid', message ? 'true' : 'false');
        $modal.find('#' + input.id + 'Error').text(message).prop('hidden', !message);
    }

    function showServerError(data){
        clearResult();
        validationStarted = true;
        $.each(fieldLabels, function(field){
            var message = data.fields && data.fields[field];
            if(data.field === field && !message){message = data.message;}
            if(typeof message === 'string'){
                markField($modal.find('[name="' + field + '"]').get(0), message);
            }
        });
        setStatus(data.message || 'Test email not sent. Please check the marked fields.', 'error');
    }

    function close(){
        if(pending){return;}
        $modal.addClass('cg_hide').removeClass('cg_active');
        $backdrop.prop('hidden', true);
        $('body').removeClass('cg-mail-test-open');
        if(opener && document.documentElement.contains(opener)){opener.focus();}
    }

    function showResult(data){
        $modal.find('.cg-mail-test-result').prop('hidden', false).attr('data-status', data.status);
        $modal.find('.cg-mail-test-result-title').text(data.title);
        $modal.find('.cg-mail-test-summary').text(data.summary);
        var $tips = $modal.find('.cg-mail-test-tips').empty();
        $.each(data.tips || [], function(i, tip){$('<li>').text(tip).appendTo($tips);});
        var lines = [];
        $.each(data.details || {}, function(key, value){lines.push(key + ': ' + value);});
        $modal.find('.cg-mail-test-details').text(lines.join('\n'));
        report = ['I tried to send one test email through WordPress from ' + $modal.attr('data-site') + '. Please help me investigate this attempt.', '',
            'Sending result: ' + data.title, data.summary, '',
            'The application cannot confirm whether the message reached the recipient\u2019s inbox or spam folder.', '',
            lines.join('\n'), '',
            'Please trace this attempt in your mail logs using the time, recipient and available message identifiers. Was the message accepted, rejected or passed to another mail service? Please explain any failure and which settings need to be corrected.', '',
            'If the recipient\u2019s email address is on this website\u2019s domain, please also check whether delivery should be local or routed to an external mailbox provider. If another provider handles delivery, please identify that provider.'].join('\n');
        $modal.find('.cg-mail-test-copy-fallback textarea').val(report);
        $modal.find('.cg-mail-test-copy-fallback').prop('hidden', true);
        $modal.find('.cg-mail-test-copy-status').text('');
    }

    $(document).on('click', '.cg-mail-delivery-test', function(e){
        e.preventDefault();
        if(pending){return;}
        opener = this;
        var $card = $(this).closest('.cg-mail-delivery-card');
        var $scope = $card.closest('.cg_view_options_rows_container, #cgMailTemplateContainer');
        $.each(['from', 'reply'], function(i, field){
            var selector = $card.attr('data-cg-mail-' + field);
            $modal.find('[name="' + field + '"]').val(selector ? ($scope.find(selector).first().val() || '') : '');
        });
        $modal.find('[name="to"]').val($modal.attr('data-recipient'));
        validationStarted = false;
        $modal.find('input[type="email"]').removeAttr('aria-invalid');
        $modal.find('.cg-mail-test-field-error').prop('hidden', true).empty();
        $modal.find('.cg-mail-test-context').text('Settings from: ' + $card.attr('data-cg-mail-type') + '. Current form values are copied without saving.');
        clearResult();
        setStatus('');
        $modal.removeClass('cg_hide').addClass('cg_active').scrollTop(0);
        $backdrop.prop('hidden', false);
        $('body').addClass('cg-mail-test-open');
        $modal.find('#cgMailTestTitle').trigger('focus');
    });

    $modal.on('input change', 'input[type="email"]', function(){
        if(pending){return;}
        clearResult();
        if(validationStarted || $(this).attr('aria-invalid') === 'true'){markField(this, fieldError(this));}
        var hasErrors = $modal.find('input[aria-invalid="true"]').length;
        setStatus(hasErrors ? 'Test email not sent. Please correct the marked fields.' : '', hasErrors ? 'error' : 'info');
    });

    $modal.on('click', '.cg-mail-test-close', close);
    var backdropPressed = false;
    $backdrop.on('mousedown', function(){backdropPressed = true;}).on('click', function(){
        if(backdropPressed){close();}
        backdropPressed = false;
    });
    $(document).on('mouseup', function(){setTimeout(function(){backdropPressed = false;}, 0);});

    // Keep keyboard focus in this dialog without closing an underlying mail composer.
    document.addEventListener('keydown', function(e){
        if($modal.hasClass('cg_hide')){return;}
        if(e.key === 'Escape' || e.keyCode === 27){e.preventDefault(); e.stopImmediatePropagation(); close();}
        if(e.key === 'Tab' || e.keyCode === 9){
            var $focusable = $modal.find('button, input, textarea, summary').filter(':visible:not(:disabled)');
            var first = $focusable.get(0), last = $focusable.get($focusable.length - 1);
            if(!$modal.get(0).contains(document.activeElement) || (e.shiftKey && document.activeElement === first)){
                e.preventDefault(); if(e.shiftKey && last){last.focus();}else if(first){first.focus();}
            }else if(!e.shiftKey && document.activeElement === last){e.preventDefault(); if(first){first.focus();}}
        }
    }, true);

    $modal.on('submit', '#cgMailTestForm', function(e){
        e.preventDefault();
        if(pending){return;}
        clearResult();
        setStatus('');
        validationStarted = true;
        var values = {}, invalid;
        $modal.find('input[type="email"]').each(function(){
            values[this.name] = $.trim(this.value);
            var message = fieldError(this);
            markField(this, message);
            if(message && !invalid){invalid = this;}
        });
        if(invalid){
            setStatus('Test email not sent. Please correct the marked fields.', 'error');
            invalid.focus(); return;
        }
        pending = true;
        $modal.find('input[type="email"], .cg-mail-test-send, .cg-mail-test-close').prop('disabled', true);
        setStatus('Sending one test email. Please wait...', 'info');
        var started = new Date().toISOString();
        values.action = 'post_cg_send_test_mail';
        values.nonce = $modal.attr('data-nonce');
        function unknownResult(httpStatus){
            showResult({status: 'unknown', title: 'Sending result is unknown',
                summary: 'The connection ended before a usable response arrived. The email may still have been sent. No automatic retry was made.',
                details: {'Request started (browser time, UTC)': started, 'Website': $modal.attr('data-site'),
                    'WordPress': $modal.attr('data-wordpress'), 'PHP': $modal.attr('data-php'),
                    'Requested From': values.from, 'Requested Reply-To': values.reply, 'Requested recipient': values.to,
                    'HTTP status': httpStatus || 'No response'},
                tips: ['Check your mailbox and mail provider logs before sending again.']});
            setStatus('No confirmed sending result is available.', 'unknown');
        }
        $.ajax({url: ajaxurl, type: 'POST', data: values, dataType: 'json', timeout: 90000}).done(function(response){
            if(response && response.success && response.data && response.data.details){
                showResult(response.data);
                if(response.data.status === 'accepted'){
                    setStatus('Test completed. Check your inbox and spam folder.', 'accepted');
                }else if(response.data.status === 'failed'){
                    setStatus('Test email could not be sent. See the details below.', 'error');
                }else{setStatus('No confirmed sending result is available.', 'unknown');}
            }else if(response && response.success === false && response.data && response.data.message){
                showServerError(response.data);
            }else{unknownResult(200);}
        }).fail(function(xhr){
            var data = xhr.responseJSON && xhr.responseJSON.data;
            if(data && data.message && (xhr.status === 400 || xhr.status === 403)){
                showServerError(data);
            }else{unknownResult(xhr.status);}
        }).always(function(){
            pending = false;
            $modal.find('input[type="email"], .cg-mail-test-send, .cg-mail-test-close').prop('disabled', false);
            if(!$modal.find('.cg-mail-test-result').prop('hidden')){
                $modal.find('.cg-mail-test-result-title').attr('tabindex', '-1').trigger('focus');
            }else{$modal.find('[aria-invalid="true"]').first().trigger('focus');}
        });
    });

    $modal.on('click', '.cg-mail-test-copy', function(){
        cgJsClassAdmin.mailDelivery.copy(report, function(){
            $modal.find('.cg-mail-test-copy-status').text('Copied');
        }, function(){
            $modal.find('.cg-mail-test-copy-status').text('Select and copy the report below.');
            $modal.find('.cg-mail-test-copy-fallback').prop('hidden', false).find('textarea').trigger('focus').get(0).select();
        });
    });
});
