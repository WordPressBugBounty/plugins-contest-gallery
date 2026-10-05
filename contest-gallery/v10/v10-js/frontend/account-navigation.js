(function () {
    'use strict';
    if (typeof cgAccountNavigation === 'undefined') { return; }
    var options = cgAccountNavigation;
    function frontendUrl(value) {
        try {
            var url = new URL(value, window.location.href);
            if (url.origin !== window.location.origin || /\/(wp-admin(?:\/|$)|wp-login\.php(?:\/|$))/i.test(url.pathname)) { return ''; }
            ['cg_account_return', 'cg_account_logout', 'cgLogoutLink', '_wpnonce', 'cgkey'].forEach(function (key) { url.searchParams.delete(key); });
            return url.href;
        } catch (ignore) { return ''; }
    }
    function updateLink(link) {
        if (!link || !link.href) { return; }
        var url, profile;
        try { url = new URL(link.href); profile = new URL(options.profileUrl); } catch (ignore) { return; }
        if (url.origin !== window.location.origin) { return; }
        var isProfile = url.pathname === profile.pathname;
        var isLogout = /\/wp-login\.php$/.test(url.pathname) && url.searchParams.get('action') === 'logout';
        if (!isProfile && !(isLogout && (options.allLogoutLinks || url.searchParams.get('cg_account_logout') === '1'))) { return; }
        var origin = frontendUrl(options.frontend ? window.location.href : options.returnUrl);
        if (origin) { url.searchParams.set('cg_account_return', origin); link.href = url.href; }
    }
    function updateAll() { document.querySelectorAll('#wpadminbar a').forEach(updateLink); }
    // Capture the current AJAX URL, including middle-click/new-tab navigation.
    ['pointerdown', 'click', 'auxclick', 'focusin'].forEach(function (name) {
        document.addEventListener(name, function (event) {
            var element = event.target;
            while (element && element.tagName !== 'A') { element = element.parentElement; }
            updateLink(element);
        }, true);
    });
    updateAll();
    window.addEventListener('popstate', updateAll);
}());
