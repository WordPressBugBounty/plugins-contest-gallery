/* Administrator-only reader. Never use the frontend read/send endpoints here. */
jQuery(function ($) {
    'use strict';
    function request($root, action, data) {
        return $.ajax({url: ajaxurl, type: 'POST', dataType: 'json', cache: false,
            data: $.extend({action: action, user_id: $root.attr('data-user'), cg_nonce: CG1LBackendNonce.nonce}, data)}).then(function (response) {
            return response && response.success ? response.data : $.Deferred().reject(response && response.data && response.data.message || 'Please try again.').promise();
        }, function (xhr) { return $.Deferred().reject(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message || 'The connection failed. Please try again.').promise(); });
    }
    function status($root, text, error) { $root.find('.cg-member-admin-status').text(text || '').attr('role', error ? 'alert' : 'status'); }
    $(document).on('click.cgMemberAdminMessages', '.cg-member-admin-thread, .cg-member-admin-older', function () {
        var $button = $(this), $root = $button.closest('#cgMemberAdminMessages'), older = $button.hasClass('cg-member-admin-older');
        var conversation = older ? $root.attr('data-conversation') : $button.attr('data-conversation');
        var generation = ($root.data('generation') || 0) + 1; $root.data('generation', generation);
        $root.attr('data-conversation', conversation).find('.cg-member-admin-thread').attr('aria-pressed', 'false').filter('[data-conversation="' + conversation + '"]').attr('aria-pressed', 'true');
        var $history = $root.find('.cg-member-admin-history'), before = older ? $button.attr('data-before') : 0, oldHeight = $history[0].scrollHeight, oldScroll = $history.scrollTop();
        if (!older) { $history.empty(); $root.find('.cg-member-admin-conversation-heading').text('Loading…'); }
        $root.find('.cg-member-admin-older').prop('disabled', true); $history.attr('aria-busy', 'true'); status($root, 'Loading…');
        request($root, 'post_cg_member_admin_messages', {conversation_id: conversation, before: before}).done(function (data) {
            if (!document.contains($root[0]) || $root.data('generation') !== generation) { return; }
            $root.find('.cg-member-admin-conversation-heading').text(data.heading);
            if (older) { $history.prepend(data.html).scrollTop(oldScroll + $history[0].scrollHeight - oldHeight); }
            else { $history.html(data.html || '<p class="cg-member-admin-empty">No messages yet.</p>').scrollTop(0); }
            $root.find('.cg-member-admin-older').attr('data-before', data.before).prop('hidden', !data.more); status($root, '');
        }).fail(function (text) { if ($root.data('generation') === generation) { status($root, text, true); } }).always(function () {
            if ($root.data('generation') === generation) { $history.removeAttr('aria-busy'); $root.find('.cg-member-admin-older').prop('disabled', false); }
        });
    });
    $(document).on('click.cgMemberAdminMessages', '.cg-member-admin-more-threads', function () {
        var $button = $(this), $root = $button.closest('#cgMemberAdminMessages'); $button.prop('disabled', true); status($root, 'Loading…');
        request($root, 'post_cg_member_admin_threads', {page: $button.attr('data-page')}).done(function (data) {
            if (!document.contains($root[0])) { return; }
            var $rows = $(data.html); $rows.filter('[data-conversation="' + $root.attr('data-conversation') + '"]').attr('aria-pressed', 'true');
            $root.find('.cg-member-admin-thread-list').append($rows); $button.attr('data-page', data.page + 1).prop('hidden', !data.more); status($root, '');
        }).fail(function (text) { status($root, text, true); }).always(function () { $button.prop('disabled', false); });
    });
    $(document).on('click.cgMemberAdminMessages', '.cg-member-admin-set-communication', function () {
        var $button = $(this), $root = $button.closest('#cgMemberAdminMessages');
        if (!window.confirm($button.attr('data-confirm'))) { return; }
        $button.prop('disabled', true); status($root, 'Saving…');
        request($root, 'post_cg_member_admin_communication', {disabled: $button.attr('data-disabled')}).done(function (data) {
            if (!document.contains($root[0])) { return; }
            $root.find('.cg-member-admin-communication').html(data.html); status($root, 'Saved');
        }).fail(function (text) { status($root, text, true); }).always(function () { $button.prop('disabled', false); });
    });
});
