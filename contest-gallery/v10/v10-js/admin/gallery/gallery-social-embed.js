jQuery(function ($) {
    'use strict';
    var names = {youtube: 'YouTube', instagram: 'Instagram', tiktok: 'TikTok', twitter: 'X (Twitter)'};
    var panelNumber = 0;
    var panelSelector = '.media-frame .cg_social_embed_panel';

    function show($node, visible) { $node.prop('hidden', !visible); }
    function status($panel, message, error) {
        $panel.find('.cg_social_embed_status').text(message).toggleClass('cg_social_embed_error', !!error).prop('hidden', !message);
    }
    function buttons($panel, state) {
        var unavailable = state.busy || !state.preview || state.uncertain;
        $panel.find('#cgAddYoutubeSubmit').prop('disabled', unavailable || (state.libraryOnly && !!state.postId));
        $panel.find('.cg_social_embed_save').prop('disabled', unavailable || !!state.postId);
        $panel.find('.cg_social_embed_url, .cg_social_embed_use').prop('disabled', state.busy);
        $panel.find('#cgAddYoutubeForm').attr('aria-busy', state.busy ? 'true' : 'false');
        $panel.find('.cg_social_embed_submit_label').text(state.busy ? 'Saving…' : (state.libraryOnly ? 'Save to library' : 'Add to gallery'));
    }
    function requestError(xhr) {
        var data = xhr.responseJSON && xhr.responseJSON.data;
        if (data && data.code === 'cg_nonce_invalid') { return 'Your session has expired. Reload the gallery and try again.'; }
        if (data && data.message) { return data.message; }
        if (xhr.status === 403) { return 'The request was blocked or your session has expired. Reload the gallery and try again.'; }
        return 'The request could not be completed. Check your connection and try again.';
    }
    function request(action, data) {
        return $.ajax({url: typeof ajaxurl !== 'undefined' ? ajaxurl : 'admin-ajax.php', method: 'POST', dataType: 'json', timeout: 25000,
            data: $.extend({action: action, cg_nonce: CG1LBackendNonce.nonce}, data)});
    }
    function current($panel, state, version) {
        return state.version === version && $.contains(document, $panel[0]);
    }
    function renderPreview($panel, data) {
        var $preview = $panel.find('#cgAddYoutubePreview').empty().attr('data-provider', data.provider);
        if (data.provider === 'youtube' || data.provider === 'instagram') {
            $('<iframe>', {src: data.embed_url, title: data.title || names[data.provider] + ' preview', allow: 'encrypted-media; fullscreen; picture-in-picture', allowfullscreen: 'allowfullscreen', referrerpolicy: 'strict-origin-when-cross-origin'}).appendTo($preview);
        } else {
            // Markup comes only from the server's fixed provider endpoints and HTML allowlist.
            $preview.html(data.html);
        }
        show($preview, true);
        $panel.find('.cg_social_embed_provider').text(names[data.provider]).prop('hidden', false);
        $panel.find('.cg_social_embed_preview_caption h4').text(data.title);
        $panel.find('.cg_social_embed_preview_caption p').text(data.author_name || '');
        show($panel.find('.cg_social_embed_preview_caption'), true);
    }
    function preview($panel) {
        var state = $panel.data('socialState');
        if (state.busy) { return; }
        state.version++;
        var version = state.version;
        clearTimeout(state.timer);
        if (state.previewRequest) { state.previewRequest.abort(); }
        state.preview = null;
        state.postId = 0;
        state.uncertain = false;
        state.url = $.trim($panel.find('.cg_social_embed_url').val());
        status($panel, '', false);
        show($panel.find('.cg_social_embed_retry, .cg_social_embed_reload, .cg_social_embed_open_library, .cg_social_embed_provider, .cg_social_embed_preview_caption'), false);
        $panel.find('#cgAddYoutubePreview').empty().prop('hidden', true);
        show($panel.find('.cg_social_embed_empty'), !state.url);
        show($panel.find('.cg_social_embed_loading'), !!state.url);
        buttons($panel, state);
        if (!state.url) { return; }
        state.timer = setTimeout(function () {
            state.previewRequest = request('post_cg_social_platform_preview', {source_url: state.url});
            state.previewRequest.done(function (response) {
                if (!current($panel, state, version)) { return; }
                if (!response || !response.success || !response.data || !names[response.data.provider]) {
                    status($panel, 'The preview could not be loaded. Please check the URL.', true);
                    show($panel.find('.cg_social_embed_retry'), true);
                    return;
                }
                state.preview = response.data;
                renderPreview($panel, state.preview);
            }).fail(function (xhr, reason) {
                if (reason !== 'abort' && current($panel, state, version)) {
                    status($panel, requestError(xhr), true);
                    show($panel.find('.cg_social_embed_retry'), xhr.status !== 400 && xhr.status !== 403);
                    show($panel.find('.cg_social_embed_reload'), xhr.status === 403);
                }
            }).always(function () {
                if (!current($panel, state, version)) { return; }
                show($panel.find('.cg_social_embed_loading'), false);
                show($panel.find('.cg_social_embed_empty'), !state.preview);
                buttons($panel, state);
            });
        }, 400);
    }
    function saved($panel, state) {
        var $frame = $panel.closest('.media-frame');
        var $count = $frame.find('#cgYoutubeLibraryNewAddedCount');
        var count = parseInt($count.text().replace('+', ''), 10) || 0;
        $count.text('+' + (count + 1)).removeClass('cg_hide');
        $frame.find('#cgYoutubeNoEntries').addClass('cg_hide');
        show($panel.find('.cg_social_embed_open_library'), true);
        status($panel, 'Saved to your social embed library.', false);
    }
    function addToGallery($panel, state) {
        var gid = parseInt($panel.attr('data-cg-gid'), 10), assigned = {};
        try { assigned = JSON.parse(localStorage.getItem('cgAssignedFields' + gid)) || {}; } catch (ignore) {}
        var data = {action: 'post_cg_social_platforms_add_to_gallery', cg_nonce: CG1LBackendNonce.nonce, action2: gid,
            cg_wp_post_ids: [state.postId], cg_assign_category: assigned.category || 0, cg_social_direct: 1};
        status($panel, 'Saved to your library. Adding to the gallery…', false);
        $.ajax({url: typeof ajaxurl !== 'undefined' ? ajaxurl : 'admin-ajax.php', method: 'POST', data: data, timeout: 45000})
            .done(function (response) {
                if (typeof response === 'string' && response.indexOf('cg-images-added') !== -1) {
                    if ($.contains(document, $panel[0])) {
                        $panel.closest('.media-modal').find('.media-modal-close').trigger('click');
                        cgJsClassAdmin.gallery.functions.reloadAfterAddingEntries($, response);
                    }
                } else {
                    state.uncertain = true;
                    status($panel, 'Saved to your library. The gallery result could not be confirmed. Reload the gallery before adding this post again.', true);
                    show($panel.find('.cg_social_embed_reload'), true);
                }
            }).fail(function (xhr) {
                state.uncertain = !(xhr.responseJSON && xhr.responseJSON.success === false);
                status($panel, 'Saved to your library. ' + (state.uncertain ? 'The gallery result could not be confirmed. Reload the gallery before trying again.' : requestError(xhr)), true);
                show($panel.find('.cg_social_embed_reload'), state.uncertain || xhr.status === 403);
            }).always(function () { state.busy = false; buttons($panel, state); });
    }
    function save($panel, toGallery) {
        var state = $panel.data('socialState');
        if (state.busy || !state.preview || state.uncertain) { return; }
        toGallery = toGallery && !state.libraryOnly;
        if (state.postId && !toGallery) { return; }
        state.busy = true;
        buttons($panel, state);
        status($panel, state.postId ? 'Adding to the gallery…' : 'Saving to your social embed library…', false);
        if (state.postId) { addToGallery($panel, state); return; }
        request('post_cg_social_platform_input', {source_url: state.preview.source_url}).done(function (response) {
            if (!response || !response.success || !response.data || !parseInt(response.data.post_id, 10)) {
                state.uncertain = true;
                status($panel, 'The save result could not be confirmed. Check the social embed library before trying again.', true);
                show($panel.find('.cg_social_embed_open_library'), true);
                return;
            }
            state.postId = parseInt(response.data.post_id, 10);
            saved($panel, state);
            if (toGallery) { addToGallery($panel, state); }
        }).fail(function (xhr) {
            state.uncertain = !(xhr.responseJSON && xhr.responseJSON.success === false) && xhr.status !== 403 && xhr.status !== 400;
            status($panel, state.uncertain ? 'The save result could not be confirmed. Check the social embed library before trying again.' : requestError(xhr), true);
            show($panel.find('.cg_social_embed_open_library'), state.uncertain);
            show($panel.find('.cg_social_embed_reload'), xhr.status === 403);
        }).always(function () {
            if (!toGallery || !state.postId) { state.busy = false; buttons($panel, state); }
        });
    }

    $(document).on('click', '.media-frame .cg_media_menu_item, .media-frame .media-menu-item', function () {
        var $frame = $(this).closest('.media-frame');
        $frame.removeClass('cg_social_embed_mode');
        if (this.id !== 'cgAddYoutube') {
            $frame.find('.cg_social_embed_panel').each(function () {
                var state = $(this).data('socialState');
                if (state) { clearTimeout(state.timer); state.version++; if (state.previewRequest) { state.previewRequest.abort(); } }
            });
        }
    });
    $(document).on('click', '#cgAddYoutube', function () {
        var $frame = $(this).closest('.media-frame');
        var $existing = $frame.find('.cg_social_embed_panel');
        if ($existing.length) {
            $frame.find('.media-frame-content > *').addClass('cg_hide');
            $existing.removeClass('cg_hide');
            var state = $existing.data('socialState');
            if (!state.busy && !state.preview) { preview($existing); }
        } else {
            var $template = $('.cg_social_embed_panel').filter(function () { return !$(this).closest('.media-frame').length; }).first();
            var $panel = $template.clone().removeClass('cg_hide');
            var inputId = 'cgSocialPostUrl' + (++panelNumber);
            $panel.find('.cg_social_embed_url').attr({id: inputId, 'aria-describedby': inputId + 'Help'});
            $panel.find('label').attr('for', inputId);
            $panel.find('#cgSocialUrlHelp').attr('id', inputId + 'Help');
            var libraryOnly = $frame.closest('.media-modal').hasClass('cg_add_additional_files') || $frame.hasClass('cg_add_additional_files');
            $panel.data('socialState', {version: 0, busy: false, preview: null, postId: 0, uncertain: false, libraryOnly: libraryOnly});
            $frame.find('.media-frame-content > *').addClass('cg_hide');
            $frame.find('.media-frame-content').append($panel);
            if (libraryOnly) {
                show($panel.find('.cg_social_embed_save'), false);
                $panel.find('.cg_social_embed_destination').text('Save the post, then select it from the social embed library for this entry.');
            }
            buttons($panel, $panel.data('socialState'));
        }
        $frame.addClass('cg_social_embed_mode');
        $(this).addClass('cg_active').attr('aria-pressed', 'true');
        $frame.find('.cg_social_embed_header h2').trigger('focus');
    });
    $(document).on('input', panelSelector + ' .cg_social_embed_url', function () { preview($(this).closest('.cg_social_embed_panel')); });
    $(document).on('submit', panelSelector + ' #cgAddYoutubeForm', function (event) { event.preventDefault(); save($(this).closest('.cg_social_embed_panel'), true); });
    $(document).on('click', panelSelector + ' .cg_social_embed_save', function () { save($(this).closest('.cg_social_embed_panel'), false); });
    $(document).on('click', panelSelector + ' .cg_social_embed_use', function () {
        $(this).closest('.cg_social_embed_panel').find('.cg_social_embed_url').val($(this).attr('data-cg-url')).trigger('input').trigger('focus');
    });
    $(document).on('click', panelSelector + ' .cg_social_embed_open_library', function () { $(this).closest('.media-frame').find('#cgYoutubeLibrary').trigger('click'); });
    $(document).on('click', panelSelector + ' .cg_social_embed_retry', function () { preview($(this).closest('.cg_social_embed_panel')); });
    $(document).on('click', panelSelector + ' .cg_social_embed_reload', function () { window.location.reload(); });
    $(document).on('click', panelSelector + ' .cg_social_embed_copy', function () {
        var $button = $(this), url = $button.attr('data-cg-url'), $panel = $button.closest('.cg_social_embed_panel');
        var done = function () {
            var $label = $button.find('span:last-child'), original = 'Copy URL';
            $label.text('Copied');
            $panel.find('.cg_social_embed_copy_status').text('Example URL copied.');
            setTimeout(function () { $label.text(original); }, 1800);
        };
        var fallback = function () {
            var node = document.createElement('textarea');
            node.value = url; node.style.position = 'fixed'; node.style.left = '-9999px'; document.body.appendChild(node); node.select();
            var copied = false;
            try { copied = document.execCommand('copy'); } catch (ignore) {}
            node.parentNode.removeChild(node); $button.trigger('focus');
            if (copied) { done(); } else { $panel.find('.cg_social_embed_copy_status').text('Copying was unavailable. Select the example URL and copy it manually.'); }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(url).then(done, fallback); } else { fallback(); }
    });
});
