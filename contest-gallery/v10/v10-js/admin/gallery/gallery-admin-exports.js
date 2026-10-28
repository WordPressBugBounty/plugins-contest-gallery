jQuery(function ($) {
    'use strict';
    var modalOpen = false, job = '', type = '', gallery = 0, busy = false, cancelling = false, retry = null, trigger = null, url = '', timer = null, run = 0;
    var titles = {files_zip: 'Zip all files', votes_csv: 'Export all votes', fields_csv: 'Export fields & ratings'};
    var modal = function () { return $('#cgGalleryExportModalContainer'); };
    var guard = function (active) {
        busy = active;
        modal().toggleClass('cg-gallery-export-processing', active);
        $('#cgBackendBackgroundDrop').toggleClass('cg_message_close_extra_processing', active);
    };
    var message = function (xhr) {
        return xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'The export request could not be completed. Please try again.';
    };
    var fail = function (text, again, terminal) {
        if (terminal) { window.clearTimeout(timer);run++;cancelling = false; }
        guard(false);
        $('#cgGalleryExportHeadline').text(terminal ? 'Export stopped' : (cancelling ? 'Cancellation paused' : 'Export paused'));
        $('#cgGalleryExportText').text(terminal ? 'Reload this page before starting another export.' : 'Your completed steps remain available.');
        if (terminal) { $('#cgGalleryExportNote').text('Temporary export files are removed automatically after 24 hours of inactivity.'); }
        $('#cgGalleryExportError').removeClass('cg_hide').text(text);
        retry = terminal ? null : again;
        $('#cgGalleryExportRetry').toggleClass('cg_hide', !retry);
        $('#cgGalleryExportCancel').toggleClass('cg_hide', !job || !!terminal).prop('disabled', false);
        $('#cgGalleryExportClose, .cg-gallery-export-close-icon').toggleClass('cg_hide', !!job && !terminal);
    };
    var request = function (action, data, done, again) {
        var requestRun = run;
        $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: $.extend({action: 'post_cg_gallery_export_' + action, cg_nonce: CG1LBackendNonce.nonce}, data)})
            .done(function (response) {
                if (requestRun !== run) { return; }
                if (!response || !response.success || !response.data) {
                    fail(response && response.data && response.data.message ? response.data.message : 'The export response was invalid.', again);
                    return;
                }
                done(response.data);
            }).fail(function (xhr) {
                if (requestRun !== run) { return; }
                if (cancelling && action === 'step') { return; }
                fail(message(xhr), again, [401, 403, 404].indexOf(xhr.status) !== -1);
            });
    };
    var progress = function (data) {
        var percent = Math.max(0, Math.min(100, parseInt(data.percent, 10) || 0));
        $('#cgGalleryExportProgress').attr('aria-valuenow', percent).find('span').css('width', percent + '%');
        $('#cgGalleryExportPercent').text(percent + '%');
        $('#cgGalleryExportCount').text((data.processed || 0) + ' / ' + (data.total || 0));
        var text = data.phase === 'collect' ? 'Finding original files.' : (data.phase === 'pack' ? 'Creating the ZIP archive.' : (data.phase === 'publish' ? 'Preparing the download.' : 'Preparing your CSV file.'));
        $('#cgGalleryExportText').text(text);
    };
    var showExisting = function (download) {
        $('.cg-gallery-export-existing[data-gallery-id="' + gallery + '"]').removeClass('cg_hide').find('a').attr('href', download);
        $('.cg-gallery-export-existing[data-gallery-id="' + gallery + '"] .cg-gallery-export-copy-link').attr('data-url', download);
    };
    var finished = function (data) {
        guard(false);retry = null;url = data.download_url;
        progress(data);
        $('#cgGalleryExportHeadline').text('Export ready');
        $('#cgGalleryExportText').text(data.skipped ? 'Your export is ready. Unavailable files skipped: ' + data.skipped + '.' : 'Your export is ready to download.');
        $('#cgGalleryExportCancel, #cgGalleryExportRetry, #cgGalleryExportError').addClass('cg_hide');
        $('#cgGalleryExportClose, .cg-gallery-export-close-icon').removeClass('cg_hide');
        $('#cgGalleryExportDownload').attr('href', url).text(type === 'files_zip' ? 'Download ZIP' : 'Download CSV').removeClass('cg_hide');
        $('#cgGalleryExportCopy, #cgGalleryExportDelete').toggleClass('cg_hide', type !== 'files_zip');
        $('#cgGalleryExportNote').text(type === 'files_zip' ? 'Anyone with the download link can download this ZIP until you delete it.' : 'CSV columns are separated by semicolons. The temporary export is removed after 24 hours of inactivity.');
        if (type === 'files_zip') { showExisting(url); }
    };
    var cancelled = function () {
        cancelling = false;guard(false);job = '';retry = null;
        $('#cgGalleryExportHeadline').text('Export cancelled');
        $('#cgGalleryExportText').text('Temporary export files have been removed.');
        $('#cgGalleryExportCancel, #cgGalleryExportRetry, #cgGalleryExportError').addClass('cg_hide');
        $('#cgGalleryExportClose, .cg-gallery-export-close-icon').removeClass('cg_hide');
    };
    var cancelPoll = function () {
        request('status', {job_id: job}, function (data) {
            if (data.cancelled) { cancelled(); } else { timer = window.setTimeout(cancelPoll, 500); }
        }, cancelPoll);
    };
    var cancel = function () {
        cancelling = true;window.clearTimeout(timer);guard(true);
        $('#cgGalleryExportCancel').prop('disabled', true);
        $('#cgGalleryExportRetry, #cgGalleryExportError').addClass('cg_hide');
        $('#cgGalleryExportHeadline').text('Cancelling export');
        request('cancel', {job_id: job}, function (data) {
            if (data.cancelled) { cancelled(); } else { timer = window.setTimeout(cancelPoll, 500); }
        }, cancel);
    };
    var step = function () {
        if (cancelling) { return; }
        guard(true);
        $('#cgGalleryExportError, #cgGalleryExportRetry').addClass('cg_hide');
        $('#cgGalleryExportHeadline').text('Export in progress');
        request('step', {job_id: job}, function (data) {
            if (cancelling) { return; }
            if (data.cancelled) { cancelled();return; }
            if (data.done) { finished(data);return; }
            progress(data);
            timer = window.setTimeout(step, 0);
        }, step);
    };
    var start = function () {
        guard(true);
        $('#cgGalleryExportError, #cgGalleryExportRetry').addClass('cg_hide');
        request('start', {gallery_id: gallery, export_type: type}, function (data) {
            job = data.job_id;
            $('#cgGalleryExportCancel').prop('disabled', false);
            if (data.done) { finished(data); } else { progress(data);step(); }
        }, start);
    };
    var open = function ($button) {
        if (busy || (modal().length && !modal().hasClass('cg_hide'))) { return; }
        run++;
        modalOpen = true;
        trigger = $button[0];type = $button.attr('data-export-type');
        gallery = parseInt($button.closest('[data-gallery-id]').attr('data-gallery-id'), 10);
        job = '';url = '';cancelling = false;retry = null;
        $('#cgGalleryExportLabel').text(titles[type]);
        $('#cgGalleryExportHeadline').text('Preparing export');
        $('#cgGalleryExportText').text('The export is being prepared.');
        $('#cgGalleryExportNote').text(type === 'files_zip' ? 'Keep this page open while the export is running.' : 'CSV columns are separated by semicolons. Keep this page open while the export is running.');
        $('#cgGalleryExportDownload, #cgGalleryExportCopy, #cgGalleryExportDelete, #cgGalleryExportRetry, #cgGalleryExportClose, #cgGalleryExportError, .cg-gallery-export-close-icon').addClass('cg_hide');
        $('#cgGalleryExportCancel').removeClass('cg_hide').prop('disabled', true);
        progress({percent: 0, phase: type === 'files_zip' ? 'collect' : 'export'});
        cgJsClassAdmin.gallery.functions.showModal('#cgGalleryExportModalContainer');
        $('#cgGalleryExportHeadline').trigger('focus');start();
    };
    var close = function () {
        if (busy || (job && retry)) { return; }
        modalOpen = false;
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        guard(false);job = '';retry = null;
        if (trigger && document.contains(trigger)) { trigger.focus(); }
    };
    var copy = function (link, $status) {
        var done = function () { $status.text('Link copied.'); };
        var fallback = function () {
            var $input = $('<textarea>').val(link).css({position: 'fixed', left: '-9999px'}).appendTo(document.body);
            $input[0].select();
            try { $status.text(document.execCommand('copy') ? 'Link copied.' : 'Copy the link from the download button.'); }
            catch (e) { $status.text('Copy the link from the download button.'); }
            $input.remove();
        };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(link).then(done, fallback); } else { fallback(); }
    };
    var deleteZip = function (id, $status, $button) {
        $button.prop('disabled', true);
        $.ajax({url: window.ajaxurl || 'admin-ajax.php', type: 'POST', dataType: 'json', data: {action: 'post_cg_gallery_export_delete_zip', gallery_id: id, cg_nonce: CG1LBackendNonce.nonce}})
            .done(function (response) {
                if (!response || !response.success) { $status.text(response && response.data && response.data.message ? response.data.message : 'The ZIP could not be deleted.');return; }
                $('.cg-gallery-export-existing[data-gallery-id="' + id + '"]').addClass('cg_hide');
                $status.text('ZIP deleted.');
                if (id === gallery && !modal().hasClass('cg_hide')) { $('#cgGalleryExportDownload, #cgGalleryExportCopy, #cgGalleryExportDelete').addClass('cg_hide'); }
            }).fail(function (xhr) { $status.text(message(xhr)); }).always(function () { $button.prop('disabled', false); });
    };
    $(document).on('click', '.cg-gallery-export-button', function () { open($(this)); });
    $(document).on('click', '#cgGalleryExportCancel', cancel);
    $(document).on('click', '#cgGalleryExportRetry', function () { if (retry) { retry(); } });
    $(document).on('click', '#cgGalleryExportClose, .cg-gallery-export-close-icon', close);
    $(document).on('click', '#cgGalleryExportCopy', function () { copy(url, $('#cgGalleryExportNote')); });
    $(document).on('click', '.cg-gallery-export-copy-link', function () { copy($(this).attr('data-url'), $(this).siblings('.cg-gallery-export-existing-message')); });
    $(document).on('click', '#cgGalleryExportDelete', function () { deleteZip(gallery, $('#cgGalleryExportNote'), $(this)); });
    $(document).on('click', '.cg-gallery-export-delete', function () { deleteZip(parseInt($(this).parent().attr('data-gallery-id'), 10), $(this).siblings('.cg-gallery-export-existing-message'), $(this)); });
    $(document).on('keydown', function (e) {
        if (!modal().length || modal().hasClass('cg_hide')) { return; }
        if (e.key === 'Escape') { e.preventDefault();close(); }
        if (e.key === 'Tab') {
            var $items = modal().find('button:not(:disabled), a[href]').filter(':visible'), first = $items[0], last = $items[$items.length - 1];
            if (!first) { e.preventDefault();return; }
            if (e.shiftKey && (document.activeElement === first || document.activeElement.id === 'cgGalleryExportHeadline')) { e.preventDefault();last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault();first.focus(); }
        }
    });
    document.addEventListener('click', function (e) {
        if (e.target.id === 'cgBackendBackgroundDrop' && modal().length && !modal().hasClass('cg_hide')) {
            e.preventDefault();e.stopImmediatePropagation();close();
        }
    }, true);
    window.addEventListener('beforeunload', function (e) { if (busy) { e.preventDefault();e.returnValue = ''; } });
    var auto = function () {
        // The gallery's initial image request can finish after its header buttons are usable.
        if (modalOpen && modal().hasClass('cg_hide')) {
            cgJsClassAdmin.gallery.functions.showModal('#cgGalleryExportModalContainer');
            guard(busy);
        }
        $('.cg-gallery-export-buttons[data-auto-export]').each(function () {
            var selected = $(this).attr('data-auto-export');
            if (!selected) { return; }
            $(this).attr('data-auto-export', '');
            $(this).find('[data-export-type="' + selected + '"]:not(:disabled)').trigger('click');
        });
    };
    $(document).ajaxComplete(auto);auto();
});
