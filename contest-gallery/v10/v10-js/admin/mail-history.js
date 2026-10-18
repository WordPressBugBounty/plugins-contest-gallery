jQuery(function ($) {
    'use strict';
    cgJsClassAdmin.mailHistory = {context: '', gallery: 0, user: 0, generation: 0, xhr: null};
    var state = cgJsClassAdmin.mailHistory, returnComposer = null, matchComposerSize = false;
    function modal() { return $('#cgMailHistoryContainer'); }
    function syncComposerSize() {
        if (!matchComposerSize) { return; }
        var $composer = $('#cgParticipantMailContainer');
        if (!$composer.length) { return; }
        var hidden = $composer.hasClass('cg_hide'), visibility = $composer[0].style.visibility;
        // Measure the responsive composer while history is visible, without painting a second dialog.
        if (hidden) { $composer.css('visibility', 'hidden').removeClass('cg_hide'); }
        var bounds = $composer[0].getBoundingClientRect();
        if (hidden) { $composer.addClass('cg_hide').css('visibility', visibility); }
        modal().css({width: bounds.width, height: bounds.height});
    }
    function resetComposerSize() {
        matchComposerSize = false; modal().css({width: '', height: ''});
    }
    function notice(message, error) {
        modal().find('.cg_mail_history_notice').text(message || '').toggleClass('cg_hide', !message).toggleClass('cg_mail_history_error', !!error);
    }
    function load(values) {
        var $modal = modal(), generation = ++state.generation;
        if (state.xhr) { state.xhr.abort(); }
        notice('Loading…', false);
        $modal.find('.cg_mail_history_content').attr('aria-busy', 'true').find('button').prop('disabled', true);
        state.xhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: $.extend({action: 'cg_mail_history',
            cg_nonce: CG1LBackendNonce.nonce, context: state.context, gallery_id: state.gallery, user_id: state.user}, values)});
        state.xhr.done(function (response) {
            if (generation !== state.generation || $modal.hasClass('cg_hide') || !document.contains($modal[0])) { return; }
            if (!response || !response.success) { notice(response && response.data && response.data.message || 'The email history could not be loaded.', true); return; }
            if (values.view === 'reuse') {
                if (!cgJsClassAdmin.participantMail) { notice('Open a gallery to reuse this message.', true); return; }
                cgJsClassAdmin.participantMail.reuse(response.data.message, function (accepted) {
                    if (accepted) { closeHistory(true); }
                    else {
                        if (!returnComposer) { returnComposer = function () { cgJsClassAdmin.participantMail.returnFromHistory(); }; $modal.find('.cg_mail_history_return').removeClass('cg_hide'); }
                        notice('Your current draft was kept. Use Back to message to continue editing.', true);
                    }
                });
                return;
            }
            if (response.data.title) { $modal.find('.cg_mail_history_context').text(response.data.title); }
            $modal.find('.cg_mail_history_content').html(response.data.html);
            $modal.scrollTop(0); notice('');
        }).fail(function (xhr, status) {
            if (generation === state.generation && status !== 'abort' && !$modal.hasClass('cg_hide')) {
                notice(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message || 'The connection failed. Please try again.', true);
                $modal.scrollTop(0);
            }
        }).always(function () {
            if (generation === state.generation) { $modal.find('.cg_mail_history_content').removeAttr('aria-busy').find('button').prop('disabled', false); state.xhr = null; }
        });
    }
    function closeHistory(showComposer) {
        state.generation++; if (state.xhr) { state.xhr.abort(); state.xhr = null; }
        modal().addClass('cg_hide').removeClass('cg_active');
        resetComposerSize();
        var callback = returnComposer; returnComposer = null;
        if (callback) { callback(); }
        else if (showComposer) { cgJsClassAdmin.gallery.functions.showModal('#cgParticipantMailContainer'); }
    }
    function open(context, gallery, user) {
        state.context = context; state.gallery = gallery; state.user = user;
        modal().find('.cg_mail_history_context,.cg_mail_history_content').empty();
        modal().find('.cg_mail_history_return').toggleClass('cg_hide', !returnComposer);
        cgJsClassAdmin.gallery.functions.showModal('#cgMailHistoryContainer'); load({view: 'list', page: 0});
    }
    state.openForComposer = function (gallery, callback) {
        returnComposer = callback; matchComposerSize = true; syncComposerSize(); open('gallery', gallery, 0);
    };
    state.fromComposer = function () { return !!returnComposer; };
    $(window).on('resize.cgMailHistory', function () {
        if (!modal().hasClass('cg_hide')) { syncComposerSize(); }
    });
    document.addEventListener('click', function (event) {
        if (!returnComposer || modal().hasClass('cg_hide')) { return; }
        if ($(event.target).closest('#cgMailHistoryContainer .cg_message_close, #cgMailHistoryContainer .cg_mail_history_return').length || event.target.id === 'cgBackendBackgroundDrop') {
            event.preventDefault(); event.stopImmediatePropagation(); closeHistory(true);
        }
    }, true);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && returnComposer && !modal().hasClass('cg_hide')) {
            event.preventDefault(); event.stopImmediatePropagation(); closeHistory(true);
        }
    }, true);
    $(document).on('click.cgMailHistory', '.cg_mail_history_open', function (event) {
        event.preventDefault();
        if ($(this).hasClass('cg-pro-false')) { return; }
        returnComposer = null; resetComposerSize();
        open($(this).attr('data-context'), parseInt($(this).attr('data-gallery-id'), 10) || 0, parseInt($(this).attr('data-user-id'), 10) || 0);
    });
    $(document).on('click.cgMailHistory', '#cgMailHistoryContainer [data-cg-history-view]', function () {
        var $button = $(this);
        load({view: $button.attr('data-cg-history-view'), page: $button.attr('data-page') || 0, campaign: $button.attr('data-campaign') || '',
            recipient: $button.attr('data-recipient') || 0, source: $button.attr('data-source') || '', mail_id: $button.attr('data-mail-id') || 0});
    });
});
