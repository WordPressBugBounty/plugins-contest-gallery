// Shared by the gallery, registration form and the standalone WP profile script.
(function ($) {
    'use strict';
    window.cgJsClass = window.cgJsClass || {};
    if (cgJsClass.profileImage) { return; }
    function config(input) {
        try { return JSON.parse($(input).attr('data-cg-profile-image')) || {}; } catch (ignore) { return {}; }
    }
    function message(input, details, fallback, gid) {
        if (!details) { return fallback || ''; }
        var rules = config(input), key = details && details.languageKey;
        var language = gid !== undefined && cgJsClass.gallery && cgJsClass.gallery.language && cgJsClass.gallery.language[gid] || {};
        var text = key && (language[key] || (rules.messages || {})[key]);
        if (!text) { return fallback || (rules.messages || {}).invalid || ''; }
        return text + (details.value !== undefined ? ': ' + details.value + (details.unit || '') : '');
    }
    function state(input) {
        var current = $(input).data('cg-profile-image-state');
        return current && current.file === (input.files[0] || null) ? current : null;
    }
    function cancel(input) {
        var old = $(input).data('cg-profile-image-state');
        $(input).removeData('cg-profile-image-state');
        if (old && old.cancel) { old.cancel(); }
    }
    function check(input) {
        var current = state(input);
        if (current) { return current.promise; }
        cancel(input);
        var rules = config(input), file = input.files[0] || null, deferred = $.Deferred(), url, image, timer;
        var result = {file: file, pending: true, validation: null, message: '', promise: deferred.promise()};
        $(input).data('cg-profile-image-state', result);
        function clean() {
            clearTimeout(timer);
            if (image) { image.onload = image.onerror = null; }
            if (url) { URL.revokeObjectURL(url); url = null; }
        }
        function finish(details, stale) {
            if (!result.pending) { return; }
            clean(); result.pending = false;
            result.stale = !!stale || state(input) !== result;
            result.validation = details || null;
            result.message = details ? message(input, details) : '';
            deferred.resolve(result);
        }
        result.cancel = function () { finish(null, true); };
        if (!file) { finish(); return result.promise; }
        var extension = file.name.split('.').pop().toLowerCase(), mime = (rules.mimes || {})[extension];
        if (!mime || (file.type && file.type !== mime)) { finish({languageKey: 'ThisFileTypeIsNotAllowed'}); return result.promise; }
        if (file.size > rules.maxBytes) {
            finish({languageKey: 'TheFileYouChoosedIsToBigMaxAllowedSize', value: rules.maxBytes / 1000000, unit: 'MB'});
            return result.promise;
        }
        image = new Image();
        image.onload = function () {
            var type = mime === 'image/jpeg' ? 'JPG' : (mime === 'image/png' ? 'PNG' : 'GIF');
            if (!image.naturalWidth || !image.naturalHeight) { finish({languageKey: 'invalid'}); }
            else if (image.naturalWidth > rules.maxWidth) { finish({languageKey: 'MaximumAllowedWidthFor' + type + 'sIs', value: rules.maxWidth, unit: 'px'}); }
            else if (image.naturalHeight > rules.maxHeight) { finish({languageKey: 'MaximumAllowedHeightFor' + type + 'sIs', value: rules.maxHeight, unit: 'px'}); }
            else { finish(); }
        };
        image.onerror = function () { finish({languageKey: 'invalid'}); };
        timer = setTimeout(function () { finish({languageKey: 'invalid'}); }, 15000);
        try { url = URL.createObjectURL(file); image.src = url; } catch (error) { finish({languageKey: 'invalid'}); }
        return result.promise;
    }
    cgJsClass.profileImage = {check: check, state: state, cancel: cancel, message: message};
}(jQuery));
