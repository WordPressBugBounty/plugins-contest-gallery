jQuery(function ($) {
    'use strict';
    var state = {id: '', gallery: 0, job: null, timer: null, generation: 0, busy: false, busyCommand: '', recipientOffset: 0, revision: 0,
        opening: false, preparingCompose: false, messageScroll: false, testRecipient: 'self', testReady: false, testGeneration: 0, testXhr: null,
        selectionGeneration: 0, selectionXhr: null, selectionTimer: null, selectionReady: false, selectionFailed: false, selectionCount: 0, selectionSignature: '', liveOffset: 0, liveLoaded: false,
        selectedRecipients: [], legacySearch: '', userGeneration: 0, userXhr: null, userTimer: null};
    var draft = cgJsClassAdmin.participantMailDraft;
    var modal = function () { return $('#cgParticipantMailContainer'); };
    var visible = function () { return modal().length && !modal().hasClass('cg_hide'); };
    var stopPolling = function () { clearTimeout(state.timer); state.timer = null; };
    var syncButtons = function () {
        modal().find('[data-cg-pmail-action]').prop('disabled', state.busy);
        modal().find('[data-cg-pmail-action="preview"]').prop('disabled', state.busy || state.opening || draft.blocked() || !state.selectionReady || !state.selectionCount);
        var selectionMessage = state.selectionReady ? (state.selectionCount ? '' : 'No reachable participants match these filters. Change or reset the filters.') :
            (state.selectionFailed ? 'Recipients could not be loaded. Try again.' : 'Checking recipients…');
        modal().find('.cg_pmail_preview_selection').toggleClass('cg_hide', !selectionMessage).find('p').text(selectionMessage);
        modal().find('.cg_pmail_preview_selection [data-cg-pmail-selection-action="retry"]').toggleClass('cg_hide', !state.selectionFailed).prop('disabled', state.busy);
        modal().find('[data-cg-pmail-action="test"]').prop('disabled', state.busy || !state.testReady);
        $('#cgParticipantMailTestRecipient').prop('disabled', state.busy);
        var starting = state.busy && state.busyCommand === 'start', job = state.job;
        modal().find('[data-cg-pmail-action="start"]').text(starting ? 'Starting…' : (job ? 'Send to ' + job.recipient_count + (job.recipient_count === 1 ? ' participant' : ' participants') : 'Send to participants'));
        modal().find('.cg_pmail_review').attr('aria-busy', starting ? 'true' : null);
    };
    var stopSelection = function () {
        state.selectionGeneration++; clearTimeout(state.selectionTimer); state.selectionTimer = null;
        if (state.selectionXhr) { state.selectionXhr.abort(); state.selectionXhr = null; }
        modal().find('.cg_pmail_selection').removeAttr('aria-busy');
        modal().find('.cg_pmail_live_more').prop('disabled', false);
    };
    var stopUserSearch = function () {
        state.userGeneration++; clearTimeout(state.userTimer); state.userTimer = null;
        if (state.userXhr) { state.userXhr.abort(); state.userXhr = null; }
        modal().find('.cg_pmail_user_notice').empty();
    };
    var composing = function () { return visible() && !state.opening && !modal().find('.cg_pmail_compose').hasClass('cg_hide'); };
    var selecting = function () { return composing() || (visible() && state.opening && state.preparingCompose); };
    // Remember the explicit return to composing only for this finished campaign.
    var composerViews = {}, composerViewFallback = {};
    var composerViewKey = function () {
        return 'cg_pmail_compose_view:' + modal().attr('data-draft-scope') + ':' + modal().attr('data-draft-user') + ':' + state.gallery;
    };
    var prefersComposer = function (job) {
        if (!job) { return false; }
        var key = composerViewKey();
        if (composerViewFallback[key]) { return composerViews[key] === job.id; }
        try { return localStorage.getItem(key) === job.id; }
        catch (error) { return composerViews[key] === job.id; }
    };
    var rememberComposer = function (job) {
        var key = composerViewKey();
        if (job && job.status !== 'draft' && job.status !== 'running' && job.status !== 'stopping') {
            composerViews[key] = job.id;
            try { localStorage.setItem(key, job.id); delete composerViewFallback[key]; } catch (error) { composerViewFallback[key] = true; }
        } else if (!job) {
            delete composerViews[key];
            try { localStorage.removeItem(key); delete composerViewFallback[key]; } catch (error) { composerViewFallback[key] = true; }
        }
    };
    var scrollToMessage = function () {
        var $modal = modal(), $message = $modal.find('.cg_pmail_message_heading').closest('.cg_pmail_card');
        if (!composing() || !$message.length) { return false; }
        $modal.scrollTop($modal.scrollTop() + $message[0].getBoundingClientRect().top - $modal[0].getBoundingClientRect().top - 70);
        return $modal.scrollTop();
    };
    var showOpening = function () {
        modal().find('.cg_pmail_compose,.cg_pmail_review,.cg_pmail_progress').addClass('cg_hide');
        modal().find('.cg_pmail_opening').removeClass('cg_hide cg_pmail_opening_error');
        modal().find('.cg_pmail_opening_text').text('Loading…');
        modal().find('[data-cg-pmail-open-retry]').addClass('cg_hide');
        modal().attr('aria-busy', 'true').scrollTop(0);
    };
    var finishOpening = function () {
        state.opening = false; state.preparingCompose = false;
        modal().find('.cg_pmail_opening').addClass('cg_hide'); modal().removeAttr('aria-busy'); syncButtons();
    };
    var openingError = function (text) {
        state.preparingCompose = false;
        modal().find('.cg_pmail_compose,.cg_pmail_review,.cg_pmail_progress').addClass('cg_hide');
        modal().find('.cg_pmail_opening').removeClass('cg_hide').addClass('cg_pmail_opening_error');
        modal().find('.cg_pmail_opening_text').text(text);
        modal().find('[data-cg-pmail-open-retry]').removeClass('cg_hide');
        modal().removeAttr('aria-busy');
    };
    var notify = function (message, error) {
        var $modal = modal(), $notice = $modal.find('.cg_pmail_notice');
        var $feedback = $modal.find('.cg_pmail_compose:not(.cg_hide) .cg_pmail_feedback, .cg_pmail_review:not(.cg_hide) .cg_pmail_feedback, .cg_pmail_progress:not(.cg_hide) .cg_pmail_feedback').first();
        if ($feedback.length) { $notice.appendTo($feedback); }
        $notice.text(message).toggleClass('cg_pmail_error', !!error).toggleClass('cg_hide', !message);
        if (message && visible() && $notice.length) {
            var noticeRect = $notice[0].getBoundingClientRect(), modalRect = $modal[0].getBoundingClientRect();
            if (noticeRect.bottom > modalRect.bottom - 24) { $modal.scrollTop($modal.scrollTop() + noticeRect.bottom - modalRect.bottom + 24); }
            else if (noticeRect.top < modalRect.top + 24) { $modal.scrollTop($modal.scrollTop() + noticeRect.top - modalRect.top - 24); }
        }
    };
    var stopTestPreview = function () {
        state.testGeneration++; state.testReady = false;
        if (state.testXhr) { state.testXhr.abort(); state.testXhr = null; }
        modal().find('.cg_pmail_preview').removeAttr('aria-busy');
    };
    var applyTestPreview = function (data) {
        if (data.test_recipient !== state.testRecipient) { return; }
        state.testReady = !!data.test_available;
        modal().find('.cg_pmail_preview').html(data.preview_html).removeAttr('aria-busy');
        $('#cgParticipantMailTestHint').text(state.testReady ? data.test_hint : 'Your WordPress account needs a valid email address. Choose another test recipient.');
        modal().find('[data-cg-pmail-action="test"]').text('Send test email to ' + data.test_email);
        syncButtons();
    };
    var setReviewPreview = function (data) {
        stopTestPreview(); state.testRecipient = 'self';
        $('#cgParticipantMailTestRecipient').html(data.test_options_html).val('self');
        applyTestPreview(data);
    };
    var loadTestPreview = function () {
        stopTestPreview();
        var generation = state.generation, testGeneration = state.testGeneration, choice = state.testRecipient;
        var current = function () { return generation === state.generation && testGeneration === state.testGeneration && visible() && state.job && state.job.status === 'draft'; };
        $('#cgParticipantMailTestHint').text('Updating the test preview…');
        modal().find('.cg_pmail_preview').attr('aria-busy', 'true');
        modal().find('[data-cg-pmail-action="test"]').text('Updating test recipient…'); syncButtons();
        state.testXhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: {
            action: 'cg_participant_mail', command: 'test_preview', gallery_id: state.gallery, id: state.id,
            cg_nonce: CG1LBackendNonce.nonce, test_recipient: choice}})
            .done(function (response) {
                if (!current()) { return; }
                if (!response || !response.success) { notify(response && response.data && response.data.message || 'The test preview could not be loaded. Choose a test recipient again.', true); return; }
                applyTestPreview(response.data);
            }).fail(function (xhr, status) {
                if (current() && status !== 'abort') { notify(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message || 'The test preview could not be loaded. Choose a test recipient again.', true); }
            }).always(function () {
                if (!current()) { return; }
                state.testXhr = null; modal().find('.cg_pmail_preview').removeAttr('aria-busy');
                if (!state.testReady) {
                    $('#cgParticipantMailTestHint').text('The test preview is unavailable. Choose a test recipient again.');
                    modal().find('[data-cg-pmail-action="test"]').text('Test email unavailable');
                }
                syncButtons();
            });
    };
    var filters = function () {
        var status = $('#cgParticipantMailStatus').val();
        return {cg_search: state.legacySearch, cg_pmail_recipients: JSON.stringify(state.selectedRecipients.slice().sort()), cg_wp_user_id_filter: $('#cgParticipantMailUser').val() || '',
            cg_show_only_winners: $('#cgParticipantMailWinners').val() ? 'true' : '', cg_pmail_category: $('#cgParticipantMailCategory').val() || '',
            cg_show_only_active: status === 'active' || status === 'conflict' ? 'true' : '',
            cg_show_only_inactive: status === 'inactive' || status === 'conflict' ? 'true' : ''};
    };
    var filterSummary = function () {
        var labels = [];
        ['Status', 'Winners', 'Category', 'User'].forEach(function (field) {
            var $select = $('#cgParticipantMail' + field);
            if ($select.val() !== '') { labels.push($select.find('option:selected').text()); }
        });
        if (state.legacySearch) { labels.push('Saved search: ' + state.legacySearch); }
        modal().find('.cg_pmail_filter_summary').text(labels.join(' · ') || 'All participants');
    };
    var selectValue = function ($select, value, missingLabel) {
        $select.find('option[data-cg-saved]').remove();
        if (value !== '' && !$select.find('option').filter(function () { return this.value === value; }).length) {
            $select.append($('<option>').attr('data-cg-saved', 'true').val(value).text(missingLabel));
        }
        $select.val(value);
    };
    var applyFilters = function (saved) {
        saved = saved || {};
        $('#cgParticipantMailStatus').val(saved.active && saved.inactive ? 'conflict' : (saved.active ? 'active' : (saved.inactive ? 'inactive' : '')));
        $('#cgParticipantMailWinners').val(saved.winners ? '1' : '');
        selectValue($('#cgParticipantMailCategory'), saved.category === undefined ? '' : String(saved.category), 'Saved category #' + saved.category);
        selectValue($('#cgParticipantMailUser'), saved.user === undefined ? '' : String(saved.user), 'Participant #' + saved.user);
        state.legacySearch = saved.search || ''; state.selectedRecipients = (saved.recipient_keys || []).slice();
        filterSummary();
    };
    var selectionError = function (text) {
        if (state.opening && state.preparingCompose) { openingError(text); return; }
        state.selectionReady = false; state.selectionFailed = true; state.liveLoaded = false;
        modal().find('.cg_pmail_selection_count').text('Unavailable');
        modal().find('.cg_pmail_selection_hint').text('The recipient selection could not be verified. Try again or clear the individual selection.');
        modal().find('.cg_pmail_selection_totals').empty();
        modal().find('.cg_pmail_selection_notice').text(text).removeClass('cg_hide');
        modal().find('.cg_pmail_selection_retry').removeClass('cg_hide');
        modal().find('.cg_pmail_live_more').addClass('cg_hide'); syncButtons();
    };
    var loadSelection = function (append, countOnly) {
        stopSelection();
        if (!selecting()) { return; }
        var generation = state.selectionGeneration, snapshot = JSON.stringify(filters()), include = !countOnly;
        state.selectionReady = false; state.selectionFailed = false; syncButtons();
        modal().find('.cg_pmail_selection').attr('aria-busy', 'true');
        modal().find('.cg_pmail_selection_count').text('Updating…');
        modal().find('.cg_pmail_selection_notice,.cg_pmail_selection_retry').addClass('cg_hide');
        modal().find('.cg_pmail_live_more').prop('disabled', true);
        state.selectionXhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', timeout: 20000, data: $.extend({action: 'cg_participant_mail', command: 'selection',
            gallery_id: state.gallery, cg_nonce: CG1LBackendNonce.nonce, include_recipients: include ? '1' : '', offset: append ? state.liveOffset : 0,
            selection_signature: append ? state.selectionSignature : ''}, filters())});
        state.selectionXhr.done(function (response) {
            if (generation !== state.selectionGeneration || !selecting() || snapshot !== JSON.stringify(filters())) { return; }
            if (!response || !response.success) { selectionError(response && response.data && response.data.message || 'The recipients could not be loaded. Please try again.'); return; }
            var data = response.data;
            if (countOnly && state.selectionSignature && data.selection_signature !== state.selectionSignature) { refreshSelection(0); return; }
            state.selectionReady = true; state.selectionCount = data.recipient_count; state.selectionSignature = data.selection_signature;
            modal().find('.cg_pmail_selection_count').text(data.recipient_count);
            modal().find('.cg_pmail_selection_hint').text(state.selectedRecipients.length ? data.recipient_count + ' selected of ' + data.available_recipient_count + ' matching participants. Only selected recipients will receive this message.' : 'No individual selection. All ' + data.available_recipient_count + ' matching participants will receive this message.');
            var totals = data.entry_count + (data.entry_count === 1 ? ' matching entry' : ' matching entries');
            if (data.missing_count) { totals += ' · ' + data.missing_count + (data.missing_count === 1 ? ' entry' : ' entries') + ' without a valid email'; }
            modal().find('.cg_pmail_selection_totals').text(totals);
            if (!data.recipient_count) { modal().find('.cg_pmail_selection_notice').text('No reachable participants match these filters. Change or reset the filters.').removeClass('cg_hide'); }
            if (include && typeof data.recipients_html === 'string') {
                modal().find('.cg_pmail_live_recipients')[append && !data.restart ? 'append' : 'html'](data.recipients_html);
                state.liveOffset = data.recipient_offset; state.liveLoaded = true;
                modal().find('.cg_pmail_live_more').toggleClass('cg_hide', state.liveOffset >= data.available_recipient_count);
            }
            if (state.opening && state.preparingCompose) { modal().find('.cg_pmail_compose').removeClass('cg_hide'); finishOpening(); }
            // Re-align after recipient layout changes without undoing a manual scroll.
            if (typeof state.messageScroll === 'number' && Math.abs(modal().scrollTop() - state.messageScroll) < 2) { scrollToMessage(); }
            state.messageScroll = false;
        }).fail(function (xhr, status) {
            if (generation === state.selectionGeneration && selecting() && status !== 'abort') {
                selectionError(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message || 'The recipients could not be loaded. Please try again.');
            }
        }).always(function () {
            if (generation === state.selectionGeneration) {
                state.selectionXhr = null; modal().find('.cg_pmail_selection').removeAttr('aria-busy');
                modal().find('.cg_pmail_live_more').prop('disabled', false); syncButtons();
            }
        });
    };
    var refreshSelection = function (delay) {
        stopSelection(); state.selectionReady = false; state.selectionFailed = false; state.liveLoaded = false; state.liveOffset = 0; state.selectionSignature = '';
        modal().find('.cg_pmail_live_recipients,.cg_pmail_selection_totals').empty();
        modal().find('.cg_pmail_live_more,.cg_pmail_selection_notice,.cg_pmail_selection_retry').addClass('cg_hide');
        modal().find('.cg_pmail_selection_hint').text(state.selectedRecipients.length ? 'Checking individual selection…' : 'No individual selection. All matching participants will receive this message.');
        modal().find('.cg_pmail_selection_count').text('Updating…'); filterSummary(); syncButtons();
        if (selecting()) { state.selectionTimer = setTimeout(function () { loadSelection(false); }, delay || 0); }
    };
    var loadUserOptions = function (delay) {
        stopUserSearch();
        var generation = state.userGeneration;
        state.userTimer = setTimeout(function () {
            if (!composing()) { return; }
            var selected = $('#cgParticipantMailUser').val() || '';
            modal().find('.cg_pmail_user_notice').text('Loading participants…');
            state.userXhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: {
                action: 'cg_participant_mail', command: 'participant_options', gallery_id: state.gallery, cg_nonce: CG1LBackendNonce.nonce,
                selected_user: selected}})
                .done(function (response) {
                    if (generation !== state.userGeneration || !composing() || selected !== ($('#cgParticipantMailUser').val() || '')) { return; }
                    if (!response || !response.success) { modal().find('.cg_pmail_user_notice').text('The participants could not be loaded. Try searching again.'); return; }
                    var $select = $('#cgParticipantMailUser').empty();
                    (response.data.options || []).forEach(function (option) {
                        var value = String(option.value), label = value === '' ? 'All participants' : (value === '0' ? 'Uploads without an account' : option.label);
                        $select.append($('<option>').val(value).text(label));
                    });
                    selectValue($select, selected, 'Participant #' + selected + ' is no longer available');
                    modal().find('.cg_pmail_user_notice').text(''); filterSummary();
                }).fail(function (xhr, status) {
                    if (generation === state.userGeneration && composing() && status !== 'abort') { modal().find('.cg_pmail_user_notice').text('The participants could not be loaded. Try searching again.'); }
                });
        }, delay || 0);
    };
    var content = function () {
        var editor = window.tinymce && tinymce.get('cgParticipantMailBody');
        return editor && !editor.isHidden() ? editor.getContent() : $('#cgParticipantMailBody').val();
    };
    var setContent = function (value) {
        $('#cgParticipantMailBody').val(value || '');
        var editor = window.tinymce && tinymce.get('cgParticipantMailBody');
        if (editor) { editor.setContent(value || ''); }
    };
    var message = function () {
        var data = {body: content()};
        modal().find('[data-cg-pmail-field]').each(function () { data[$(this).attr('data-cg-pmail-field')] = $(this).val(); });
        return data;
    };
    var applyMessage = function (data) {
        modal().find('[data-cg-pmail-field]').each(function () { $(this).val(data[$(this).attr('data-cg-pmail-field')] || ''); });
        setContent(data.body);
        cgJsClassAdmin.mailDelivery.init(modal());
    };
    var request = function (command, extra, done, quiet, failed) {
        var generation = state.generation;
        if (!quiet) { state.busy = true; state.busyCommand = command; syncButtons(); }
        return $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: $.extend({action: 'cg_participant_mail', command: command,
            gallery_id: state.gallery, id: state.id, cg_nonce: CG1LBackendNonce.nonce}, extra || {})})
            .done(function (response) {
                if (generation !== state.generation || !visible()) { return; }
                if (!response || !response.success) {
                    var message = response && response.data && response.data.message || 'The mail action could not be completed.';
                    if (command === 'open') { draft.loadFailed(); openingError(message); } else { notify(message, true); }
                    if (failed) { failed(); } return;
                }
                if (quiet) { notify(''); }
                if (done) { done(response.data); }
            }).fail(function (xhr) {
                if (generation !== state.generation || !visible()) { return; }
                var data = xhr.responseJSON;
                var message = data && data.data && data.data.message || 'The mail action could not be completed. Try again.';
                if (command === 'open') { draft.loadFailed(); openingError(message); } else { notify(message, true); }
                if (failed) { failed(); }
            }).always(function () {
                if (generation !== state.generation) { return; }
                if (!quiet) {
                    if (state.busyCommand === command) { state.busy = false; state.busyCommand = ''; syncButtons(); }
                }
                else if (!state.timer && visible() && state.job && (state.job.status === 'running' || state.job.status === 'stopping')) { poll(); }
            });
    };
    var poll = function () {
        stopPolling();
        if (!visible() || !state.id) { return; }
        state.timer = setTimeout(function () {
            if (!visible()) { return; }
            request('status', {}, function (job) { renderJob(job); }, true);
        }, 4000);
    };
    var renderJob = function (job) {
        stopSelection(); stopUserSearch(); stopTestPreview(); state.testRecipient = 'self';
        state.id = job.id; state.job = job;
        var running = job.status === 'running' || job.status === 'stopping';
        var draft = job.status === 'draft';
        if (draft) { applyFilters(job.filters); }
        modal().find('.cg_pmail_compose').addClass('cg_hide');
        modal().find('.cg_pmail_review').toggleClass('cg_hide', !draft);
        modal().find('.cg_pmail_progress').toggleClass('cg_hide', draft);
        modal().find('.cg_pmail_summary').text(job.entry_count + (job.entry_count === 1 ? ' entry · ' : ' entries · ') + job.recipient_count + (job.recipient_count === 1 ? ' recipient · ' : ' recipients · ') + job.missing_count + (job.missing_count === 1 ? ' entry' : ' entries') + ' without a valid email');
        var counts = job.counts, complete = counts.accepted + counts.failed + counts.unknown + counts.cancelled;
        modal().find('progress').attr('max', Math.max(1, job.recipient_count)).val(complete);
        modal().find('.cg_pmail_counts').text(counts.accepted + ' accepted · ' + counts.failed + ' failed · ' + counts.unknown + ' unclear · ' + counts.cancelled + ' stopped · ' + (counts.pending + counts.sending) + ' pending');
        modal().find('.cg_pmail_unclear_note').toggleClass('cg_hide', !counts.unknown);
        var labels = {running: 'Sending in the background', stopping: 'Stopping after the current email', complete: 'Sending finished', cancelled: 'Sending stopped', error: 'Sending stopped because saved data could not be read'};
        modal().find('.cg_pmail_status').text(labels[job.status] || 'Ready to send');
        modal().find('[data-cg-pmail-action="stop"]').toggleClass('cg_hide', !running || job.status === 'stopping');
        modal().find('[data-cg-pmail-action="retry"]').toggleClass('cg_hide', running || !counts.failed || job.status === 'error');
        modal().find('[data-cg-pmail-action="new"]').toggleClass('cg_hide', running);
        if (running) { rememberComposer(null); poll(); } else { stopPolling(); }
        syncButtons();
    };
    var setRecipients = function (data, append) {
        modal().find('.cg_pmail_recipients')[append ? 'append' : 'html'](data.recipients_html);
        state.recipientOffset = data.recipient_offset;
        modal().find('[data-cg-pmail-action="recipients"]').toggleClass('cg_hide', state.recipientOffset >= data.recipient_count);
    };
    var reset = function (showForm) {
        stopPolling(); stopSelection(); stopUserSearch(); stopTestPreview(); state.busyCommand = ''; state.testRecipient = 'self'; state.id = ''; state.job = null; state.recipientOffset = 0; state.selectionReady = false; state.selectionFailed = false;
        state.liveLoaded = false; state.selectionCount = 0; state.liveOffset = 0; state.selectionSignature = '';
        state.messageScroll = false; applyFilters();
        modal().find('.cg_pmail_filters,.cg_pmail_delivery_details').prop('open', false);
        modal().find('.cg_pmail_live_recipients,.cg_pmail_selection_totals,.cg_pmail_user_notice').empty();
        modal().find('.cg_pmail_live_more,.cg_pmail_selection_notice,.cg_pmail_selection_retry').addClass('cg_hide');
        modal().find('.cg_pmail_selection_count').text('Loading…');
        modal().find('.cg_pmail_review,.cg_pmail_progress').addClass('cg_hide');
        modal().find('.cg_pmail_compose').toggleClass('cg_hide', showForm === false);
        modal().find('.cg_pmail_recipients,.cg_pmail_preview').empty();
        $('#cgParticipantMailTestRecipient').find('optgroup').remove(); $('#cgParticipantMailTestRecipient').val('self');
        $('#cgParticipantMailTestHint').empty();
        notify('');
        cgJsClassAdmin.mailDelivery.init(modal());
        syncButtons();
    };
    var invalidate = function () {
        state.revision++;
        if (state.job && state.job.status !== 'draft') { return; }
        stopTestPreview();
        state.id = ''; state.job = null;
        modal().find('.cg_pmail_review').addClass('cg_hide');
    };
    var bindEditor = function (editor) {
        if (editor && editor.id === 'cgParticipantMailBody' && !editor.cgParticipantMailBound) {
            editor.cgParticipantMailBound = true; editor.on('change input undo redo', function () { invalidate(); draft.changed(); });
        }
    };
    $(document).on('tinymce-editor-init', function (event, editor) { bindEditor(editor); });
    var showCompose = function () {
        rememberComposer(state.job); reset(); refreshSelection(0); state.messageScroll = scrollToMessage();
    };
    draft.configure({message: message, applyMessage: applyMessage, composing: composing, gallery: function () { return state.gallery; },
        sync: syncButtons, invalidate: invalidate, compose: showCompose});
    var openComposer = function (incoming, done) {
        var generation = ++state.generation, container = modal()[0];
        state.busy = true; state.busyCommand = 'open'; state.opening = true; state.preparingCompose = false;
        stopPolling(); stopSelection(); stopUserSearch(); stopTestPreview();
        // Flush the previous working draft before reset, then prepare the next view out of sight.
        draft.beforeOpen(function () {
            if (generation !== state.generation || container !== modal()[0]) { return; }
            state.gallery = parseInt($('#cgEmailParticipants').attr('data-gallery-id'), 10);
            reset(false); draft.loading();
            var editor = window.tinymce && tinymce.get('cgParticipantMailBody');
            if (editor && !document.contains(editor.getElement())) { editor.remove(); editor = null; }
            if (!editor) {
                cgJsClassAdmin.index.functions.initializeEditor('cgParticipantMailBody', true);
                editor = window.tinymce && tinymce.get('cgParticipantMailBody'); bindEditor(editor);
            }
            request('open', {}, function (data) {
                draft.loaded(data.draft);
                if (incoming) {
                    modal().find('.cg_pmail_compose').removeClass('cg_hide');
                    finishOpening();
                    var accepted = draft.reuse(incoming);
                    modal().css('visibility', '');
                    if (!accepted) { modal().addClass('cg_hide'); }
                    if (done) { done(accepted); }
                    return;
                }
                var resumeMessage = prefersComposer(data.job);
                if (data.job && (data.job.status === 'running' || data.job.status === 'stopping' || (!data.draft.message && data.job.status !== 'draft' && !resumeMessage))) {
                    renderJob(data.job); finishOpening();
                } else { state.preparingCompose = true; refreshSelection(0); }
            }, false, function () {
                if (incoming) { modal().css('visibility', '').addClass('cg_hide'); if (done) { done(false); } }
            });
        });
        // A pending autosave must not delay feedback or flash the previous view.
        if (generation !== state.generation) { return; }
        showOpening();
        if (incoming) { modal().removeClass('cg_hide').css('visibility', 'hidden'); }
        else { cgJsClassAdmin.gallery.functions.showModal('#cgParticipantMailContainer'); }
    };
    $(document).on('click', '#cgParticipantMailContainer [data-cg-pmail-open-retry]', function () {
        if (!state.busy) { openComposer(); }
    });
    var returnFromHistory = function (scroll) {
        cgJsClassAdmin.gallery.functions.showModal('#cgParticipantMailContainer');
        if (composing()) {
            if (!state.selectionReady) { loadSelection(false); }
            else { syncButtons(); }
        }
        if (typeof scroll === 'number') { modal().scrollTop(scroll); }
    };
    cgJsClassAdmin.participantMail = {
        returnFromHistory: returnFromHistory,
        openHistory: function () {
            var scroll = modal().scrollTop();
            draft.flush(); stopSelection(); stopUserSearch();
            cgJsClassAdmin.mailHistory.openForComposer(state.gallery, function () {
                returnFromHistory(scroll);
            });
            modal().addClass('cg_hide');
        },
        reuse: function (value, done) {
            if (cgJsClassAdmin.mailHistory.fromComposer()) {
                var accepted = draft.reuse(value); if (done) { done(accepted); }
            } else { openComposer(value, done); }
        }
    };
    $(document).on('click', '#cgEmailParticipants', function () { openComposer(); });
    $(document).on('input change', '#cgParticipantMailContainer [data-cg-pmail-field], #cgParticipantMailBody', function () { invalidate(); draft.changed(); });
    $(document).on('change', '#cgParticipantMailTestRecipient', function () {
        if (state.busy || !state.job || state.job.status !== 'draft') { return; }
        state.testRecipient = $(this).val(); notify(''); loadTestPreview();
    });
    $(document).on('change', '#cgParticipantMailStatus,#cgParticipantMailWinners,#cgParticipantMailCategory,#cgParticipantMailUser', function () {
        state.selectedRecipients = []; invalidate(); refreshSelection(0);
        if (this.id === 'cgParticipantMailUser') { loadUserOptions(0); }
    });
    document.addEventListener('toggle', function (event) {
        var $details = $(event.target);
        if (!event.target.open || !composing() || !$details.closest('#cgParticipantMailContainer').length) { return; }
        if ($details.hasClass('cg_pmail_filters')) { loadUserOptions(0); }
    }, true);
    $(document).on('click', '#cgParticipantMailContainer [data-cg-pmail-selection-action]', function () {
        var action = $(this).attr('data-cg-pmail-selection-action');
        if (action === 'reset') { stopUserSearch(); applyFilters(); invalidate(); refreshSelection(0); if (modal().find('.cg_pmail_filters').prop('open')) { loadUserOptions(0); } }
        else if (action === 'all') { state.selectedRecipients = []; modal().find('[data-cg-pmail-recipient]').prop('checked', false); invalidate(); loadSelection(false, state.liveLoaded); }
        else { loadSelection(action === 'more'); }
    });
    $(document).on('change', '#cgParticipantMailContainer [data-cg-pmail-recipient]', function () {
        var key = this.value, index = state.selectedRecipients.indexOf(key);
        if (this.checked && index === -1) { state.selectedRecipients.push(key); }
        else if (!this.checked && index !== -1) { state.selectedRecipients.splice(index, 1); }
        invalidate(); loadSelection(false, state.liveLoaded);
    });
    $(document).on('click', '#cgParticipantMailContainer [data-cg-pmail-token]', function () {
        if (modal().attr('data-pro-placeholders') !== '1' || this.disabled) { return; }
        var token = $(this).attr('data-cg-pmail-token'), editor = window.tinymce && tinymce.get('cgParticipantMailBody');
        if (editor) { editor.execCommand('mceInsertContent', false, token); } else { $('#cgParticipantMailBody').val(content() + token); }
        invalidate(); draft.changed();
    });
    $(document).on('click', '#cgParticipantMailContainer [data-cg-pmail-action]', function () {
        if (state.busy) { return; }
        var command = $(this).attr('data-cg-pmail-action'); notify('');
        if (command === 'new') { draft.resume(); return; }
        if (command === 'edit') { stopTestPreview(); modal().find('.cg_pmail_review').addClass('cg_hide'); modal().find('.cg_pmail_compose').removeClass('cg_hide'); modal().scrollTop(0); refreshSelection(0); if ($('#cgParticipantMailUser').val() !== '') { loadUserOptions(0); } return; }
        if (command === 'preview') {
            if (draft.blocked() || !state.selectionReady || !state.selectionCount) { return; }
            var selectedFilters = filters(), revision = state.revision, original = JSON.stringify(message()) + JSON.stringify(selectedFilters);
            var filtered = Object.keys(selectedFilters).some(function (key) { return selectedFilters[key] !== '' && selectedFilters[key] !== '[]'; });
            state.busy = true; state.busyCommand = 'saving'; syncButtons();
            draft.flush(function (ok) {
                state.busy = false; state.busyCommand = ''; syncButtons();
                if (!ok || !composing() || revision !== state.revision) { return; }
                request('preview', $.extend({}, selectedFilters, message(), {scope: filtered ? 'filtered' : 'all', draft_revision: draft.revision()}), function (data) {
                    if (revision !== state.revision || original !== JSON.stringify(message()) + JSON.stringify(filters())) { notify('Your message or recipients changed. Create a new preview before sending.', true); return; }
                    renderJob(data); setRecipients(data, false); setReviewPreview(data); modal().scrollTop(0);
                });
            });
        } else if (command === 'test') {
            if (!state.testReady) { return; }
            request('test', {test_recipient: state.testRecipient}, function (data) { notify(data.message); });
        } else if (command === 'recipients') {
            request('recipients', {offset: state.recipientOffset}, function (data) { setRecipients(data, true); });
        } else {
            state.generation++; stopPolling();
            var originalRevision = draft.revision();
            request(command, {}, function (data) {
                if (command === 'start') { draft.started(data.current_draft, originalRevision); }
                renderJob(data);
            });
        }
    });
    $(document).on('click', '#cgParticipantMailContainer .cg_message_close, #cgBackendBackgroundDrop', function () {
        if (visible() && !state.opening) { draft.flush(); }
        state.opening = false; state.preparingCompose = false; modal().removeAttr('aria-busy');
        state.generation++; state.busy = false; state.busyCommand = ''; stopPolling(); stopSelection(); stopUserSearch(); stopTestPreview();
    });
    document.addEventListener('click', function (event) {
        if (visible() && !state.opening && ($(event.target).closest('#cgParticipantMailContainer .cg_message_close').length || event.target.id === 'cgBackendBackgroundDrop')) { draft.flush(); }
    }, true);
    document.addEventListener('keydown', function (event) {
        if (!visible()) { return; }
        if (event.key === 'Escape') {
            event.preventDefault(); modal().find('.cg_message_close').trigger('click');
        }
    });
});
