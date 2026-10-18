jQuery(function ($) {
    'use strict';
    var api, saved, baseline = '', ready = false, dirty = false, clearing = false, timer, xhr, conflict, recovery, waiters = [], writeId = '', prefix = '', localKey = '', suppressed = false;
    var defaults, sourceModal, restoredBackup = null, localAvailable = true;
    function modal() { return $('#cgParticipantMailContainer'); }
    function id() { return 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'.replace(/x/g, function () { return Math.floor(Math.random() * 16).toString(16); }); }
    function signature(value) {
        return JSON.stringify(['subject', 'body', 'from_name', 'from_email', 'reply_name', 'reply_email'].map(function (field) { return value && value[field] || ''; }));
    }
    function status(text, retry) {
        modal().find('.cg_pmail_draft_status').text(text);
        modal().find('[data-cg-draft-action="retry"]').toggleClass('cg_hide', !retry);
    }
    function finish(ok) { var callbacks = waiters.splice(0); callbacks.forEach(function (callback) { callback(ok); }); }
    function removeLocal(key, expected) {
        try {
            var value = JSON.parse(localStorage.getItem(key) || 'null');
            if (value && (!expected || value.write_id === expected)) { localStorage.removeItem(key); }
        } catch (error) { localAvailable = false; }
    }
    function backup() {
        if (!ready || !dirty) { return; }
        try {
            localStorage.setItem(localKey, JSON.stringify({revision: saved.revision, write_id: writeId, message: clearing ? false : api.message(), updated_at: Date.now()}));
        } catch (error) { localAvailable = false; }
    }
    function apply(value) {
        suppressed = true; api.applyMessage(value || defaults); suppressed = false;
        baseline = signature(api.message());
    }
    function showConflict(value) {
        conflict = value; clearTimeout(timer); backup();
        modal().find('.cg_pmail_draft_conflict').removeClass('cg_hide');
        status('Draft changed elsewhere. Choose which version to keep.'); api.sync(); finish(false);
    }
    function failure(response) {
        var data = response && response.data;
        if (data && data.code === 'cg_pmail_draft_conflict' && data.draft) { showConflict(data.draft); return; }
        status(localAvailable ? 'Draft not saved to the server. A browser backup is available.' : 'Draft not saved. Please retry before leaving this page.', true);
        finish(false);
    }
    function send() {
        clearTimeout(timer);
        if (!ready || conflict || recovery) { finish(false); return; }
        if (xhr) { return; }
        if (!dirty) { finish(true); return; }
        var value = clearing ? false : api.message(), original = signature(api.message()), originalId = writeId, clear = clearing;
        backup(); status('Saving…');
        xhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: $.extend({action: 'cg_participant_mail',
            command: clear ? 'draft_discard' : 'draft_save', gallery_id: api.gallery(), cg_nonce: CG1LBackendNonce.nonce,
            draft_revision: saved.revision, draft_write_id: originalId}, value || {})});
        var accepted = false;
        xhr.done(function (response) {
            if (!response || !response.success || !response.data.draft) { failure(response); return; }
            accepted = true; saved = response.data.draft;
            removeLocal(localKey, originalId);
            if (restoredBackup && restoredBackup.writeId === originalId) { removeLocal(restoredBackup.key, restoredBackup.value.write_id); restoredBackup = null; }
            if (writeId === originalId && clearing === clear && signature(api.message()) === original) {
                dirty = false; baseline = original;
                status('Saved');
            } else { backup(); }
        }).fail(function (request) { failure(request.responseJSON); }).always(function () {
            xhr = null; api.sync();
            if (accepted && dirty && !conflict && !recovery) { send(); }
            else if (accepted) { finish(true); }
        });
    }
    function changed() {
        if (!ready || suppressed || !api.composing()) { return; }
        if (!dirty && signature(api.message()) === baseline) { return; }
        clearing = false; dirty = true; writeId = id();
        backup(); clearTimeout(timer); status('Unsaved changes');
        timer = setTimeout(send, 1000); api.sync();
    }
    function findBackup() {
        recovery = null;
        try {
            for (var i = 0; i < localStorage.length; i++) {
                var key = localStorage.key(i);
                if (key.indexOf(prefix) !== 0) { continue; }
                var value;
                try { value = JSON.parse(localStorage.getItem(key)); } catch (invalid) { continue; }
                if (!value || !/^[a-f0-9]{32}$/.test(value.write_id) || typeof value.revision !== 'number' || (value.message !== false && (!value.message || typeof value.message !== 'object'))) { continue; }
                if (value.write_id === saved.write_id || (value.message !== false && signature(value.message) === signature(saved.message))) { removeLocal(key, value.write_id); i--; continue; }
                if (!recovery || value.updated_at > recovery.value.updated_at) { recovery = {key: key, value: value}; }
            }
        } catch (error) { localAvailable = false; }
        modal().find('.cg_pmail_draft_recovery').toggleClass('cg_hide', !recovery);
    }
    function reload(done) {
        if (xhr) { if (done) { done(false); } return; }
        status('Loading saved draft…');
        modal().find('.cg_pmail_compose').attr('inert', '');
        xhr = $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: {action: 'cg_participant_mail', command: 'draft_load',
            gallery_id: api.gallery(), cg_nonce: CG1LBackendNonce.nonce}}).done(function (response) {
            if (!response || !response.success) { failure(response); if (done) { done(false); } return; }
            removeLocal(localKey); saved = response.data.draft; conflict = null; dirty = false; clearing = false;
            modal().find('.cg_pmail_draft_conflict').addClass('cg_hide');
            apply(saved.message); api.invalidate(); status(saved.message ? 'Saved' : ''); api.sync(); if (done) { done(true); }
        }).fail(function (request) { failure(request.responseJSON); if (done) { done(false); } }).always(function () { xhr = null; modal().find('.cg_pmail_compose').removeAttr('inert'); });
    }
    cgJsClassAdmin.participantMailDraft = {
        configure: function (options) { api = options; },
        beforeOpen: function (done) { if (ready && (dirty || xhr)) { this.flush(function () { done(); }); } else { done(); } },
        loading: function () { if (sourceModal !== modal()[0]) { sourceModal = modal()[0]; defaults = api.message(); } ready = false; modal().find('.cg_pmail_compose').attr('inert', '').attr('aria-busy', 'true'); status('Loading draft…'); },
        loadFailed: function () { modal().find('.cg_pmail_compose').removeAttr('inert aria-busy'); status('Draft could not be loaded. Close this dialog and retry.'); },
        loaded: function (draft) {
            saved = draft; ready = true; dirty = false; clearing = false; conflict = null;
            prefix = 'cg_pmail_draft:' + modal().attr('data-draft-scope') + ':' + modal().attr('data-draft-user') + ':' + api.gallery() + ':';
            if (!localKey || localKey.indexOf(prefix) !== 0) { localKey = prefix + id(); }
            modal().find('.cg_pmail_compose').removeAttr('inert aria-busy');
            modal().find('.cg_pmail_draft_conflict').addClass('cg_hide');
            apply(saved.message); status(saved.message ? 'Saved' : ''); findBackup(); api.sync();
        },
        changed: changed,
        flush: function (done) { if (done) { waiters.push(done); } backup(); send(); },
        revision: function () { return saved ? saved.revision : 0; },
        blocked: function () { return !ready || !!conflict || !!recovery; },
        resume: function () { if (ready) { api.compose(); status(dirty ? 'Unsaved changes' : (saved.message ? 'Saved' : '')); } },
        reuse: function (value) {
            if (!ready || conflict || recovery) { status('Resolve the draft notice before replacing this message.'); return false; }
            if ((dirty || saved.message) && signature(api.message()) !== signature(value) && !window.confirm('Replace your current draft with this previous message?')) { return false; }
            api.compose(); apply(value); api.invalidate(); dirty = true; clearing = false; writeId = id(); backup(); send(); return true;
        },
        started: function (draft, originalRevision) {
            if (!draft || !saved || saved.revision !== originalRevision || dirty || xhr) { return; }
            saved = draft; dirty = false; clearing = false; removeLocal(localKey); apply(false); status('');
        }
    };
    $(document).on('click', '#cgParticipantMailContainer [data-cg-draft-action]', function () {
        var action = $(this).attr('data-cg-draft-action');
        if (action === 'history') { cgJsClassAdmin.participantMail.openHistory(); return; }
        if (action === 'retry') { if (!ready) { return; } send(); return; }
        if (!ready) { return; }
        if (action === 'restore' && recovery) {
            if (dirty && !window.confirm('Replace your current changes with the browser backup?')) { return; }
            var recovered = recovery; recovery = null; modal().find('.cg_pmail_draft_recovery').addClass('cg_hide');
            apply(recovered.value.message); api.invalidate(); clearing = recovered.value.message === false; dirty = true;
            // Keep the source backup until the server acknowledges this exact restored version.
            writeId = id(); restoredBackup = {key: recovered.key, value: recovered.value, writeId: writeId}; backup(); send();
        } else if (action === 'dismiss' && recovery) {
            removeLocal(recovery.key, recovery.value.write_id); recovery = null; modal().find('.cg_pmail_draft_recovery').addClass('cg_hide');
            if (dirty) { send(); } api.sync();
        } else if (action === 'load' && conflict) { reload(); }
        else if (action === 'keep' && conflict) {
            saved = conflict; conflict = null; modal().find('.cg_pmail_draft_conflict').addClass('cg_hide');
            dirty = true; writeId = id(); backup(); send();
        } else if (action === 'discard' && window.confirm('Discard your saved draft and current changes?')) {
            if (recovery) { removeLocal(recovery.key, recovery.value.write_id); recovery = null; modal().find('.cg_pmail_draft_recovery').addClass('cg_hide'); }
            apply(false); api.invalidate(); clearing = true; dirty = true; writeId = id(); backup(); send();
        }
    });
    document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden' && ready) { backup(); send(); } });
    window.addEventListener('pagehide', backup);
});
