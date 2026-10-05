(function () {
    'use strict';
    var config = JSON.parse(document.getElementById('cgEntryOpenConfig').textContent);
    var card = document.querySelector('.cg-entry-open-card'), title = document.getElementById('cgEntryOpenTitle');
    var status = document.getElementById('cgEntryOpenStatus'), retry = document.getElementById('cgEntryOpenRetry');
    function fail(message, canRetry) {
        card.setAttribute('aria-busy', 'false');
        card.classList.add('cg-entry-open-error');
        title.textContent = 'Entry could not be opened';
        status.textContent = message;
        retry.hidden = !canRetry;
    }
    function check() {
        retry.hidden = true;
        card.classList.remove('cg-entry-open-error');
        card.setAttribute('aria-busy', 'true');
        title.textContent = 'Checking current entry URL…';
        status.textContent = 'Opening your entry.';
        var request = new XMLHttpRequest();
        request.open('POST', config.ajaxUrl, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.timeout = 10000;
        request.onload = function () {
            var response;
            try { response = JSON.parse(request.responseText); } catch (ignore) {}
            if (!response || !response.success) {
                var message = response && response.data && response.data.message;
                if (response === 0) { fail('Please sign in again and reopen this link from the gallery backend.', false); return; }
                fail(message || 'The current entry URL could not be checked. Please try again.', request.status !== 403);
                return;
            }
            var data = response.data;
            if (!data || !data.statuses || !data.permalinks) { fail('The current entry URL could not be checked. Please try again.', true); return; }
            var pageStatus = data.statuses[config.shortcode], target = data.permalinks[config.shortcode];
            if (pageStatus !== 'publish' || !target) {
                fail(pageStatus === 'trash' ? 'This entry page has been moved to the trash. Restore it from the gallery backend.' : 'This entry page is no longer available. Reload the gallery in the backend.', true);
                return;
            }
            try {
                var url = new URL(target), site = new URL(config.siteUrl);
                if (url.origin !== site.origin || !/^https?:$/.test(url.protocol)) { throw new Error('Invalid entry URL'); }
                if (config.shortcode === 'cg_gallery_ecommerce' && config.test) { url.searchParams.set('test', 'true'); }
                window.location.replace(url.toString());
            } catch (ignore) { fail('The current entry URL could not be opened. Reload the gallery in the backend.', true); }
        };
        request.onerror = request.ontimeout = function () { fail('The connection failed. Please try again.', true); };
        request.send('action=post_cg_get_current_permalinks&response_format=json&cgRealId=' + encodeURIComponent(config.entryId) + '&cg_nonce=' + encodeURIComponent(config.nonce));
    }
    retry.addEventListener('click', check);
    if (config.error) { fail(config.error, false); }
    else { check(); }
}());
