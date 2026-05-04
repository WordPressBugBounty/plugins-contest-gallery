cgJsClassAdmin.general.time.init();

jQuery(document).ready(function ($) {

    var cgGalleryTransferEscapeHtml = function (value) {
        return $('<div/>').text(value === undefined || value === null ? '' : value).html();
    };

    var cgNetworkExportActiveButton = $();
    var cgNetworkExportFormBaseline = null;
    var cgNetworkExportCurrentPublishState = {published:0};

    var cgNetworkExportParseConfig = function ($button) {
        var raw = $button.attr('data-cg-network') || '{}';
        try {
            return JSON.parse(raw);
        } catch (e) {
            return {};
        }
    };

    var cgNetworkExportGetGalleryUrl = function (pages) {
        if(!pages || !pages.length){
            return '';
        }
        for(var i=0;i<pages.length;i++){
            if(pages[i].type === 'gallery' && pages[i].url){
                return pages[i].url;
            }
        }
        return pages[0].url || '';
    };

    var cgNetworkExportNormalizeHost = function (url) {
        if(!/^https?:\/\//i.test(url || '')){
            return '';
        }
        var parser = document.createElement('a');
        parser.href = url;
        var host = (parser.hostname || '').toLowerCase();
        if(host.indexOf('www.') === 0){
            host = host.substr(4);
        }
        return host;
    };

    var cgNetworkExportIsValidCustomUrl = function (customUrl, defaultUrl) {
        if(!/^https?:\/\//i.test(customUrl || '')){
            return false;
        }
        var customHost = cgNetworkExportNormalizeHost(customUrl);
        var defaultHost = cgNetworkExportNormalizeHost(defaultUrl);
        return customHost !== '' && defaultHost !== '' && customHost === defaultHost;
    };

    var cgNetworkExportNormalizeState = function (state) {
        state = state && typeof state === 'object' ? state : {};
        if(parseInt(state.published,10) !== 1){
            return {published:0};
        }
        return {
            published: 1,
            gallery_id: parseInt(state.gallery_id,10) || 0,
            published_at_gmt: state.published_at_gmt || '',
            published_at_text: state.published_at_text || '',
            last_network_update_at_gmt: state.last_network_update_at_gmt || state.last_auto_update_at_gmt || state.published_at_gmt || '',
            last_network_update_at_text: state.last_network_update_at_text || state.last_auto_update_at_text || state.published_at_text || '',
            site_detail_url: state.site_detail_url || '',
            gallery_detail_url: state.gallery_detail_url || '',
            site_slug: state.site_slug || '',
            accepted_galleries: $.isArray(state.accepted_galleries) ? state.accepted_galleries : [],
            auto_update_enabled: parseInt(state.auto_update_enabled,10) === 1 ? 1 : 0,
            auto_update_interval: state.auto_update_interval || 'daily',
            last_auto_update_at_gmt: state.last_auto_update_at_gmt || '',
            last_auto_update_at_text: state.last_auto_update_at_text || '',
            last_auto_update_failed_at_gmt: state.last_auto_update_failed_at_gmt || '',
            last_auto_update_failed_at_text: state.last_auto_update_failed_at_text || '',
            last_auto_update_error: state.last_auto_update_error || '',
            next_auto_update_at_gmt: state.next_auto_update_at_gmt || '',
            next_auto_update_at_text: state.next_auto_update_at_text || '',
            export_settings: cgNetworkExportNormalizeFormState(state.export_settings || {})
        };
    };

    var cgNetworkExportNormalizeFormState = function (state) {
        state = state && typeof state === 'object' ? state : {};
        var linkMode = state.link_mode === 'custom' ? 'custom' : 'gallery';
        return {
            title: $.trim(state.title || ''),
            description: $.trim(state.description || ''),
            link_mode: linkMode,
            custom_url: linkMode === 'custom' ? $.trim(state.custom_url || '') : ''
        };
    };

    var cgNetworkExportCurrentFormState = function () {
        return cgNetworkExportNormalizeFormState({
            title: $('#cgNetworkExportTitle').val() || '',
            description: $('#cgNetworkExportDescription').val() || '',
            link_mode: $('#cgNetworkCustomPageToggle').is(':checked') ? 'custom' : 'gallery',
            custom_url: $('#cgNetworkCustomUrl').val() || ''
        });
    };

    var cgNetworkExportFormStatesAreEqual = function (first,second) {
        first = cgNetworkExportNormalizeFormState(first);
        second = cgNetworkExportNormalizeFormState(second);
        return first.title === second.title &&
            first.description === second.description &&
            first.link_mode === second.link_mode &&
            first.custom_url === second.custom_url;
    };

    var cgNetworkExportStoredFormState = function (config,publishState) {
        publishState = cgNetworkExportNormalizeState(publishState);
        var settings = publishState.export_settings || {};
        var linkMode = settings.link_mode === 'custom' ? 'custom' : 'gallery';
        var galleryId = parseInt(config.gallery_id,10) || 0;
        return cgNetworkExportNormalizeFormState({
            title: publishState.published && settings.title ? settings.title : (config.title || (galleryId ? 'Contest Gallery '+galleryId : '')),
            description: publishState.published && settings.description ? settings.description : (config.description || (galleryId ? 'Sub title contest gallery '+galleryId : '')),
            link_mode: publishState.published ? linkMode : 'gallery',
            custom_url: publishState.published && linkMode === 'custom' ? (settings.custom_url || '') : ''
        });
    };

    var cgNetworkExportSetFormState = function (formState,galleryUrl) {
        formState = cgNetworkExportNormalizeFormState(formState);
        $('#cgNetworkExportTitle').val(formState.title);
        $('#cgNetworkExportDescription').val(formState.description);
        $('#cgNetworkCustomPageToggle').prop('checked',formState.link_mode === 'custom');
        $('#cgNetworkCustomUrl')
            .val(formState.link_mode === 'custom' ? formState.custom_url : '')
            .attr('placeholder',galleryUrl || 'https://example.com/contest/')
            .prop('disabled',formState.link_mode !== 'custom');
        $('#cgNetworkCustomPagePanel').toggleClass('cgnet-admin-custom-url-panel--disabled',formState.link_mode !== 'custom');
    };

    var cgNetworkExportApplyUnsavedChangesState = function () {
        var state = cgNetworkExportNormalizeState(cgNetworkExportCurrentPublishState || {});
        var hasUnsavedChanges = state.published && cgNetworkExportFormBaseline !== null && !cgNetworkExportFormStatesAreEqual(cgNetworkExportCurrentFormState(),cgNetworkExportFormBaseline);
        $('#cgNetworkUnsavedChangesNotice').toggleClass('cgnet-admin-is-hidden',!hasUnsavedChanges);
        $('#cgNetworkRunAutoUpdateNow')
            .prop('disabled',hasUnsavedChanges || !state.published)
            .toggleClass('cgnet-admin-button--disabled-by-unsaved',hasUnsavedChanges)
            .text(hasUnsavedChanges ? 'Save changes first' : 'Run update now');
        $('#cgNetworkExportSubmit')
            .toggleClass('cgnet-admin-button--needs-save',hasUnsavedChanges)
            .text(hasUnsavedChanges ? 'Save and update network listing' : cgNetworkExportModalButtonText(state));
    };

    var cgNetworkExportAutoUpdateStatusText = function (state) {
        state = cgNetworkExportNormalizeState(state);
        if(!state.published){
            return '';
        }
        if(state.last_network_update_at_text){
            if(state.next_auto_update_at_text){
                return 'Last network update '+state.last_network_update_at_text+' - Next WP-Cron '+state.next_auto_update_at_text;
            }
            return 'Last network update '+state.last_network_update_at_text;
        }
        if(state.next_auto_update_at_text){
            return 'Last network update pending - Next WP-Cron '+state.next_auto_update_at_text;
        }
        return 'Last network update pending';
    };

    var cgNetworkExportListingUrl = function (state) {
        state = cgNetworkExportNormalizeState(state);
        return state.gallery_detail_url || state.site_detail_url || '';
    };

    var cgNetworkExportModalButtonText = function (state) {
        state = cgNetworkExportNormalizeState(state);
        return state.published ? 'Update network listing' : 'Publish and keep updated';
    };

    var cgNetworkExportRowButtonText = function (state) {
        state = cgNetworkExportNormalizeState(state);
        return state.published ? 'Update network listing' : 'Publish to network';
    };

    var cgNetworkExportRenderRowStateHtml = function (state) {
        state = cgNetworkExportNormalizeState(state);
        if(!state.published){
            return '';
        }
        var html = '<span>Published since '+cgGalleryTransferEscapeHtml(state.published_at_text || '')+'</span>';
        html += '<span class="cg_network_auto_update_state">'+cgGalleryTransferEscapeHtml(cgNetworkExportAutoUpdateStatusText(state))+'</span>';
        if(state.last_auto_update_error){
            html += '<span class="cg_network_auto_update_error">Auto update error: '+cgGalleryTransferEscapeHtml(state.last_auto_update_error)+'</span>';
        }
        var listingUrl = cgNetworkExportListingUrl(state);
        if(listingUrl){
            html += '<a href="'+cgGalleryTransferEscapeHtml(listingUrl)+'" target="_blank" rel="noopener noreferrer">View listing</a>';
        }
        return html;
    };

    var cgNetworkExportSetButtonText = function ($button,text) {
        if(!$button || !$button.length){
            return;
        }
        if($button.is('input')){
            $button.val(text);
        }else{
            $button.text(text);
        }
    };

    var cgNetworkExportUpdateRowState = function (galleryId,state) {
        galleryId = parseInt(galleryId,10) || 0;
        state = cgNetworkExportNormalizeState(state);
        if(!galleryId){
            return;
        }
        var $buttons = $('.cg_network_export_button[data-cg-gallery-id="'+galleryId+'"]');
        if(cgNetworkExportActiveButton.length && parseInt(cgNetworkExportActiveButton.attr('data-cg-gallery-id'),10) === galleryId && !$buttons.is(cgNetworkExportActiveButton)){
            $buttons = $buttons.add(cgNetworkExportActiveButton);
        }
        if(!$buttons.length){
            return;
        }
        $buttons.each(function () {
            var $button = $(this);
            var config = cgNetworkExportParseConfig($button);
            var rawContext = $button.attr('data-cg-network-context') || '';
            var context = rawContext.replace(/[^A-Za-z0-9]/g,'');
            var contextId = context ? context.charAt(0).toUpperCase()+context.slice(1).toLowerCase() : '';
            config.publish_state = state;
            $button.attr('data-cg-network',JSON.stringify(config));
            cgNetworkExportSetButtonText($button,cgNetworkExportRowButtonText(state));

            var stateSelector = '.cg_network_publish_state[data-cg-gallery-id="'+galleryId+'"]';
            var stateContextFilter = function () {
                return ($(this).attr('data-cg-network-context') || '') === rawContext;
            };
            var $action = $button.closest('.cg_button_network_export, .cg_gallery_backend_network_action');
            var $scope = $button.closest('.td_gallery_buttons_export, .cg_gallery_backend_network_area');
            if(!$scope.length){
                $scope = $action.length ? $action : $button.parent();
            }
            var $actionStates = $action.length ? $action.find(stateSelector).filter(stateContextFilter) : $();
            var $scopeStates = $scope.find(stateSelector).filter(stateContextFilter);
            var $state = $actionStates.first();
            if(!$state.length){
                $state = $scopeStates.first();
            }
            if(!$state.length){
                $state = $('<div/>',{
                    id: 'cgNetworkPublishState'+galleryId+contextId,
                    class: 'cg_network_publish_state cg_hide',
                    'data-cg-gallery-id': galleryId,
                    'data-cg-network-context': rawContext
                });
                $button.after($state);
            }else if(!$state.prev().is($button)){
                $button.after($state);
            }
            $scopeStates.add($actionStates).not($state).remove();
            $state
                .attr('id','cgNetworkPublishState'+galleryId+contextId)
                .attr('data-cg-gallery-id',galleryId)
                .attr('data-cg-network-context',rawContext);
            if($action.length && !$state.parent().is($action)){
                $button.after($state);
            }
            if(state.published){
                $state.html(cgNetworkExportRenderRowStateHtml(state)).removeClass('cg_hide');
            }else{
                $state.empty().addClass('cg_hide');
            }
        });
    };

    var cgNetworkExportSetModalState = function (state) {
        state = cgNetworkExportNormalizeState(state);
        cgNetworkExportCurrentPublishState = state;
        var $status = $('#cgNetworkPublishStatus');
        var $submit = $('#cgNetworkExportSubmit');
        var $unpublish = $('#cgNetworkUnpublishSubmit');
        var $runNow = $('#cgNetworkRunAutoUpdateNow');
        var buttonText = cgNetworkExportModalButtonText(state);
        $submit.attr('data-cg-default-text',buttonText).text(buttonText);
        $unpublish.prop('disabled',false).text('Unpublish from Network');
        $runNow.prop('disabled',!state.published).removeClass('cgnet-admin-button--disabled-by-unsaved').text('Run update now');
        if(!state.published){
            $status.addClass('cgnet-admin-is-hidden');
            $('#cgNetworkPublishStatusTime').text('');
            $('#cgNetworkPublishAutoUpdateStatus').text('');
            $('#cgNetworkPublishAutoUpdateError').addClass('cgnet-admin-is-hidden').text('');
            $('#cgNetworkPublishStatusLink').attr('href','#').addClass('cgnet-admin-is-hidden');
            return;
        }
        $('#cgNetworkPublishStatusTime').text(state.published_at_text || '');
        $('#cgNetworkPublishAutoUpdateStatus').text(cgNetworkExportAutoUpdateStatusText(state));
        if(state.last_auto_update_error){
            $('#cgNetworkPublishAutoUpdateError')
                .removeClass('cgnet-admin-is-hidden')
                .text('Last auto update error: '+state.last_auto_update_error);
        }else{
            $('#cgNetworkPublishAutoUpdateError').addClass('cgnet-admin-is-hidden').text('');
        }
        var listingUrl = cgNetworkExportListingUrl(state);
        if(listingUrl){
            $('#cgNetworkPublishStatusLink').attr('href',listingUrl).removeClass('cgnet-admin-is-hidden');
        }else{
            $('#cgNetworkPublishStatusLink').attr('href','#').addClass('cgnet-admin-is-hidden');
        }
        $status.removeClass('cgnet-admin-is-hidden');
    };

    var cgNetworkExportOpenModal = function (config,$sourceButton) {
        var mode = 'single';
        var isEligible = parseInt(config.eligible,10) === 1;
        var pages = config.pages || [];
        var galleryId = config.gallery_id || 0;
        var title = config.title || (galleryId ? 'Contest Gallery '+galleryId : '');
        var description = config.description || (galleryId ? 'Sub title contest gallery '+galleryId : '');
        var eligibilityMessage = config.eligibility_message || '';
        var galleryUrl = cgNetworkExportGetGalleryUrl(pages);
        var publishState = cgNetworkExportNormalizeState(config.publish_state || {});
        var storedFormState = cgNetworkExportStoredFormState(config,publishState);
        var $modal = $('#cgNetworkExportModalContainer');
        if(!$modal.length){
            return;
        }
        cgNetworkExportActiveButton = ($sourceButton && $sourceButton.length) ? $sourceButton : $();

        $modal
            .attr('data-cg-mode',mode)
            .attr('data-cg-gallery-id',galleryId)
            .attr('data-cg-default-url',galleryUrl || '');

        $('#cgNetworkDefaultUrl').text(galleryUrl || 'No eligible gallery URL found');
        cgNetworkExportSetFormState({
            title: storedFormState.title || title,
            description: storedFormState.description || description,
            link_mode: storedFormState.link_mode,
            custom_url: storedFormState.custom_url
        },galleryUrl);
        $('#cgNetworkAutoExportToggle').prop('checked',true);
        $('#cgNetworkPrivacyConfirm').prop('checked',false).closest('.cgnet-admin-check').removeClass('cgnet-admin-check--checked');
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden').text('Please confirm the privacy policy before publishing.');
        $('#cgNetworkExportResult').addClass('cgnet-admin-is-hidden').removeClass('cgnet-admin-result--error cgnet-admin-result--success cgnet-admin-result--pending').empty();
        $('.cgnet-admin-single-only').removeClass('cgnet-admin-is-hidden');
        $('#cgNetworkIneligibleMessage').toggleClass('cgnet-admin-is-hidden',isEligible).find('p').text(eligibilityMessage);
        cgNetworkExportSetModalState(publishState);
        $('#cgNetworkExportSubmit')
            .prop('disabled',!isEligible)
            .text(cgNetworkExportModalButtonText(publishState));
        cgNetworkExportFormBaseline = storedFormState;
        cgNetworkExportApplyUnsavedChangesState();

        cgJsClassAdmin.gallery.functions.showModal('#cgNetworkExportModalContainer');
    };

    var cgNetworkExportSetMessage = function (message,isError,detailUrl) {
        var html = cgGalleryTransferEscapeHtml(message);
        if(detailUrl){
            html += '<div class="cgnet-admin-result-link"><a href="'+cgGalleryTransferEscapeHtml(detailUrl)+'" target="_blank" rel="noopener noreferrer">View listing on Contest Gallery Network</a></div>';
        }
        $('#cgNetworkExportResult')
            .removeClass('cgnet-admin-is-hidden cgnet-admin-result--error cgnet-admin-result--success cgnet-admin-result--pending')
            .addClass(isError ? 'cgnet-admin-result--error' : 'cgnet-admin-result--success')
            .html(html);
    };

    var cgNetworkExportSetValidation = function (message) {
        $('#cgNetworkExportValidation').removeClass('cgnet-admin-is-hidden').html(cgGalleryTransferEscapeHtml(message));
    };

    var cgNetworkExportSetPendingMessage = function (message) {
        $('#cgNetworkExportResult')
            .removeClass('cgnet-admin-is-hidden cgnet-admin-result--error cgnet-admin-result--success')
            .addClass('cgnet-admin-result--pending')
            .text(message);
    };

    var cgNetworkExportRestoreSubmitButton = function () {
        $('#cgNetworkExportSubmit')
            .removeClass('cgnet-admin-button--loading')
            .prop('disabled',false)
            .text($('#cgNetworkExportSubmit').attr('data-cg-default-text') || 'Publish and keep updated');
        cgNetworkExportApplyUnsavedChangesState();
    };

    var cgGalleryTransferExportJobId = '';
    var cgGalleryTransferExportIsProcessing = false;
    var cgGalleryTransferExportPollTimer = 0;

    var cgGalleryTransferGetErrorMessage = function (response,fallbackMessage) {
        var message = fallbackMessage || 'Request failed.';
        if(response && response.responseJSON && response.responseJSON.data){
            if(response.responseJSON.data.code === 'cg_nonce_invalid'){
                cgJsClassAdmin.index.functions.isInvalidNonce($,'###cg_version###'+response.responseJSON.data.version+'###cg_version######cg_nonce_invalid###');
                return false;
            }
            if(response.responseJSON.data.message){
                message = response.responseJSON.data.message;
            }
        }
        return message;
    };

    var cgGalleryTransferHandleError = function (response) {
        var message = cgGalleryTransferGetErrorMessage(response,'Request failed.');
        if(message === false){
            return;
        }
        $('#cgGalleryTransferProgress').removeClass('cg_hide').html('<span style="color:#b00000;">'+cgGalleryTransferEscapeHtml(message)+'</span>');
    };

    var cgNetworkExportHandleError = function (response) {
        var message = 'Request failed.';
        if(response && response.responseJSON && response.responseJSON.data){
            if(response.responseJSON.data.code === 'cg_nonce_invalid'){
                cgNetworkExportRestoreSubmitButton();
                cgJsClassAdmin.index.functions.isInvalidNonce($,'###cg_version###'+response.responseJSON.data.version+'###cg_version######cg_nonce_invalid###');
                return;
            }
            if(response.responseJSON.data.message){
                message = response.responseJSON.data.message;
            }
        }
        cgNetworkExportSetMessage(message,true);
        cgNetworkExportRestoreSubmitButton();
    };

    var cgGalleryTransferExportModal = function () {
        return $('#cgGalleryTransferExportModalContainer');
    };

    var cgGalleryTransferExportClearTimer = function () {
        if(cgGalleryTransferExportPollTimer){
            window.clearTimeout(cgGalleryTransferExportPollTimer);
            cgGalleryTransferExportPollTimer = 0;
        }
    };

    var cgGalleryTransferExportSetMode = function (mode) {
        var $modal = cgGalleryTransferExportModal();
        $modal.removeClass('cg-gallery-transfer-export-processing cg-gallery-transfer-export-done cg-gallery-transfer-export-error cg-gallery-transfer-export-cancelled cg-gallery-transfer-export-unavailable');
        if(mode){
            $modal.addClass('cg-gallery-transfer-export-'+mode);
        }
    };

    var cgGalleryTransferExportSetProgressBar = function (percent) {
        percent = parseInt(percent,10);
        if(isNaN(percent)){
            percent = 0;
        }
        percent = Math.max(0,Math.min(100,percent));
        $('#cgGalleryTransferExportProgressBar').css('width',percent+'%');
        $('#cgGalleryTransferExportPercent').text(percent+'%');
    };

    var cgGalleryTransferExportDownloadUrl = function (url) {
        if(!url){
            return '#';
        }
        if(url.indexOf('delete_after=') > -1){
            return url;
        }
        return url+(url.indexOf('?') > -1 ? '&' : '?')+'delete_after=1';
    };

    var cgGalleryTransferExportResetModal = function (galleryId) {
        var $modal = cgGalleryTransferExportModal();
        if(!$modal.length){
            return false;
        }
        cgGalleryTransferExportClearTimer();
        cgGalleryTransferExportJobId = '';
        cgGalleryTransferExportIsProcessing = true;
        $modal.attr('data-cg-job-id','');
        cgGalleryTransferExportSetMode('processing');
        $('#cgGalleryTransferExportHeadline').text('Preparing export');
        $('#cgGalleryTransferExportText').text('Export for gallery '+galleryId+' is starting.');
        cgGalleryTransferExportSetProgressBar(0);
        $('#cgGalleryTransferExportEntries').text('0/0');
        $('#cgGalleryTransferExportSkipped').text('0');
        $('#cgGalleryTransferExportSkippedWrap').addClass('cg_hide');
        $('#cgGalleryTransferExportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferExportError').addClass('cg_hide').empty();
        $('#cgGalleryTransferExportDownloadInfo').addClass('cg_hide').text('The temporary ZIP will be deleted after the download request starts.');
        $('#cgGalleryTransferExportCancel').removeClass('cg_hide').prop('disabled',false).text('Cancel export');
        $('#cgGalleryTransferExportDownload').addClass('cg_hide').attr('href','#');
        $('#cgGalleryTransferExportCloseDelete').addClass('cg_hide').prop('disabled',false).text('Close and delete ZIP');
        cgJsClassAdmin.gallery.functions.showModal('#cgGalleryTransferExportModalContainer');
        return true;
    };

    var cgGalleryTransferExportSetUnavailable = function (title,message) {
        var $modal = cgGalleryTransferExportModal();
        if(!$modal.length){
            return false;
        }
        cgGalleryTransferExportClearTimer();
        cgGalleryTransferExportJobId = '';
        cgGalleryTransferExportIsProcessing = false;
        $modal.attr('data-cg-job-id','');
        cgGalleryTransferExportSetMode('unavailable');
        $('#cgGalleryTransferExportHeadline').text(title || 'Export not available yet');
        $('#cgGalleryTransferExportText').text(message || 'Gallery ZIP export requires a gallery page with the new /contest-galleries/ URL structure. Copy this gallery first. The copied gallery gets the new URL structure and can then be exported.');
        cgGalleryTransferExportSetProgressBar(0);
        $('#cgGalleryTransferExportEntries').text('0/0');
        $('#cgGalleryTransferExportSkipped').text('0');
        $('#cgGalleryTransferExportSkippedWrap').addClass('cg_hide');
        $('#cgGalleryTransferExportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferExportError').addClass('cg_hide').empty();
        $('#cgGalleryTransferExportDownloadInfo').addClass('cg_hide');
        $('#cgGalleryTransferExportCancel').addClass('cg_hide').prop('disabled',false).text('Cancel export');
        $('#cgGalleryTransferExportDownload').addClass('cg_hide').attr('href','#');
        $('#cgGalleryTransferExportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Close');
        cgJsClassAdmin.gallery.functions.showModal('#cgGalleryTransferExportModalContainer');
        return true;
    };

    var cgGalleryTransferExportSetProgress = function (data) {
        data = data || {};
        var percent = data.percent || 0;
        var processed = data.processed_entries || 0;
        var total = data.total_entries || 0;
        var skipped = data.skipped_sale_entries || 0;
        var text = 'The export is collecting entries, votes, comments, mails and original media files.';
        if(data.stage === 'zip'){
            text = 'The ZIP file is being generated.';
        }
        $('#cgGalleryTransferExportHeadline').text('Export in progress');
        $('#cgGalleryTransferExportText').text(text);
        cgGalleryTransferExportSetProgressBar(percent);
        $('#cgGalleryTransferExportEntries').text(processed+'/'+total);
        $('#cgGalleryTransferExportSkipped').text(skipped);
        $('#cgGalleryTransferExportSkippedWrap').toggleClass('cg_hide',skipped < 1);
    };

    var cgGalleryTransferExportSetDone = function (data) {
        data = data || {};
        cgGalleryTransferExportIsProcessing = false;
        cgGalleryTransferExportClearTimer();
        cgGalleryTransferExportSetMode('done');
        cgGalleryTransferExportSetProgress(data);
        cgGalleryTransferExportSetProgressBar(100);
        $('#cgGalleryTransferExportHeadline').text('Export completed.');
        $('#cgGalleryTransferExportText').text('The ZIP package is ready for download.');
        $('#cgGalleryTransferExportCancel').addClass('cg_hide').prop('disabled',false);
        $('#cgGalleryTransferExportDownload')
            .removeClass('cg_hide')
            .attr('href',cgGalleryTransferExportDownloadUrl(data.download_url || '#'));
        $('#cgGalleryTransferExportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Close and delete ZIP');
        $('#cgGalleryTransferExportDownloadInfo').removeClass('cg_hide');
        if(data.warnings && data.warnings.length){
            $('#cgGalleryTransferExportWarnings').removeClass('cg_hide').text('Warnings: '+data.warnings.join(' | '));
        }
    };

    var cgGalleryTransferExportSetError = function (message) {
        cgGalleryTransferExportIsProcessing = false;
        cgGalleryTransferExportClearTimer();
        cgGalleryTransferExportSetMode('error');
        $('#cgGalleryTransferExportHeadline').text('Export stopped.');
        $('#cgGalleryTransferExportText').text('No download was created.');
        $('#cgGalleryTransferExportError').removeClass('cg_hide').text(message);
        $('#cgGalleryTransferExportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferExportDownloadInfo').addClass('cg_hide');
        $('#cgGalleryTransferExportCancel').addClass('cg_hide').prop('disabled',false);
        $('#cgGalleryTransferExportDownload').addClass('cg_hide').attr('href','#');
        $('#cgGalleryTransferExportCloseDelete').removeClass('cg_hide').prop('disabled',false).text(cgGalleryTransferExportJobId ? 'Close and delete temporary files' : 'Close');
    };

    var cgGalleryTransferHandleExportError = function (response) {
        var message = cgGalleryTransferGetErrorMessage(response,'Export request failed.');
        if(message === false){
            return;
        }
        cgGalleryTransferExportSetError(message);
    };

    var cgGalleryTransferExportCleanupJob = function (callback) {
        var $modal = cgGalleryTransferExportModal();
        var jobId = cgGalleryTransferExportJobId || $modal.attr('data-cg-job-id') || '';
        if(!jobId){
            if(callback){
                callback();
            }
            return;
        }
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_cancel',
                job_id: jobId,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).always(function () {
            cgGalleryTransferExportJobId = '';
            $modal.attr('data-cg-job-id','');
            if(callback){
                callback();
            }
        });
    };

    var cgGalleryTransferExportHideModal = function () {
        cgGalleryTransferExportClearTimer();
        cgGalleryTransferExportIsProcessing = false;
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        cgGalleryTransferExportSetMode('');
    };

    var cgGalleryTransferExportStep = function (jobId) {
        if(!cgGalleryTransferExportIsProcessing){
            return;
        }
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_export_step',
                job_id: jobId,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).done(function (response) {
            if(!cgGalleryTransferExportIsProcessing){
                return;
            }
            if(!response || !response.success){
                cgGalleryTransferHandleExportError({responseJSON: response});
                return;
            }
            var data = response.data;
            if(data.status === 'done'){
                cgGalleryTransferExportSetDone(data);
                return;
            }
            cgGalleryTransferExportSetProgress(data);
            cgGalleryTransferExportPollTimer = window.setTimeout(function () {
                cgGalleryTransferExportStep(jobId);
            },700);
        }).fail(cgGalleryTransferHandleExportError);
    };

    $(document).on('click','.cg_gallery_transfer_export_unavailable_button',function (e) {
        e.preventDefault();
        var $button = $(this);
        var title = $button.attr('data-cg-title') || 'Export not available yet';
        var message = $button.attr('data-cg-message') || 'Gallery ZIP export requires a gallery page with the new /contest-galleries/ URL structure. Copy this gallery first. The copied gallery gets the new URL structure and can then be exported.';
        if(!cgGalleryTransferExportSetUnavailable(title,message)){
            $('#cgGalleryTransferProgress').removeClass('cg_hide').html('<span style="color:#b00000;">'+cgGalleryTransferEscapeHtml(title+': '+message)+'</span>');
        }
        $('#cgGalleryTransferImportPreview').addClass('cg_hide').empty();
    });

    $(document).on('click','.cg_gallery_transfer_export_button',function (e) {
        e.preventDefault();
        var galleryId = $(this).attr('data-cg-gallery-id');
        if(!cgGalleryTransferExportResetModal(galleryId)){
            $('#cgGalleryTransferProgress').removeClass('cg_hide').text('Starting export...');
        }else{
            $('#cgGalleryTransferProgress').addClass('cg_hide').empty();
        }
        $('#cgGalleryTransferImportPreview').addClass('cg_hide').empty();
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_export_start',
                gallery_id: galleryId,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).done(function (response) {
            if(!cgGalleryTransferExportIsProcessing){
                if(response && response.success && response.data && response.data.job_id){
                    cgGalleryTransferExportJobId = response.data.job_id;
                    cgGalleryTransferExportModal().attr('data-cg-job-id',response.data.job_id);
                    cgGalleryTransferExportCleanupJob();
                }
                return;
            }
            if(!response || !response.success){
                cgGalleryTransferHandleExportError({responseJSON: response});
                return;
            }
            cgGalleryTransferExportJobId = response.data.job_id;
            cgGalleryTransferExportModal().attr('data-cg-job-id',response.data.job_id);
            cgGalleryTransferExportSetProgress({
                percent: 0,
                processed_entries: 0,
                total_entries: response.data.total_entries || 0,
                skipped_sale_entries: response.data.skipped_sale_entries || 0
            });
            cgGalleryTransferExportStep(response.data.job_id);
        }).fail(cgGalleryTransferHandleExportError);
    });

    $(document).on('click','#cgGalleryTransferExportCancel',function (e) {
        e.preventDefault();
        if(!cgGalleryTransferExportIsProcessing){
            return;
        }
        cgGalleryTransferExportIsProcessing = false;
        cgGalleryTransferExportClearTimer();
        $('#cgGalleryTransferExportCancel').prop('disabled',true).text('Cancelling ...');
        $('#cgGalleryTransferExportHeadline').text('Cancelling export');
        $('#cgGalleryTransferExportText').text('Temporary export files are being deleted.');
        cgGalleryTransferExportCleanupJob(function () {
            cgGalleryTransferExportSetMode('cancelled');
            $('#cgGalleryTransferExportHeadline').text('Export cancelled.');
            $('#cgGalleryTransferExportText').text('Temporary export files were deleted.');
            $('#cgGalleryTransferExportCancel').addClass('cg_hide').prop('disabled',false).text('Cancel export');
            $('#cgGalleryTransferExportDownload').addClass('cg_hide').attr('href','#');
            $('#cgGalleryTransferExportDownloadInfo').addClass('cg_hide');
            $('#cgGalleryTransferExportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Close');
        });
    });

    $(document).on('click','#cgGalleryTransferExportCloseDelete, #cgGalleryTransferExportModalContainer .cg_message_close',function (e) {
        e.preventDefault();
        if(cgGalleryTransferExportIsProcessing){
            return;
        }
        if(!cgGalleryTransferExportJobId && !cgGalleryTransferExportModal().attr('data-cg-job-id')){
            cgGalleryTransferExportHideModal();
            return;
        }
        $('#cgGalleryTransferExportCloseDelete').prop('disabled',true).text('Deleting ...');
        cgGalleryTransferExportCleanupJob(function () {
            $('#cgGalleryTransferExportCloseDelete').prop('disabled',false).text('Close and delete ZIP');
            cgGalleryTransferExportHideModal();
        });
    });

    $(document).on('click','#cgGalleryTransferExportDownload',function () {
        $('#cgGalleryTransferExportDownloadInfo').text('Download request started. The server will delete the temporary ZIP after streaming starts.');
    });

    if(document.addEventListener){
        document.addEventListener('click',function (e) {
            var modal = document.getElementById('cgGalleryTransferExportModalContainer');
            if(!modal){
                return;
            }
            var modalClass = ' '+modal.className+' ';
            if(modalClass.indexOf(' cg_active ') === -1 || modalClass.indexOf(' cg_hide ') > -1){
                return;
            }
            var isProcessing = modalClass.indexOf(' cg-gallery-transfer-export-processing ') > -1;
            var target = e.target;
            var isBackdrop = target && target.id === 'cgBackendBackgroundDrop';
            var isClose = false;
            var isInExportModal = false;
            var node = target;
            while(node && node !== document){
                if(node.className && typeof node.className === 'string' && (' '+node.className+' ').indexOf(' cg_message_close ') > -1){
                    isClose = true;
                }
                if(node.id === 'cgGalleryTransferExportModalContainer'){
                    isInExportModal = true;
                    break;
                }
                node = node.parentNode;
            }
            if(isProcessing && (isBackdrop || (isClose && isInExportModal))){
                e.preventDefault();
                e.stopPropagation();
                if(e.stopImmediatePropagation){
                    e.stopImmediatePropagation();
                }
                return;
            }
            if(!isProcessing && (isBackdrop || (isClose && isInExportModal))){
                e.preventDefault();
                e.stopPropagation();
                if(e.stopImmediatePropagation){
                    e.stopImmediatePropagation();
                }
                $('#cgGalleryTransferExportCloseDelete').prop('disabled',true).text('Deleting ...');
                cgGalleryTransferExportCleanupJob(function () {
                    $('#cgGalleryTransferExportCloseDelete').prop('disabled',false).text('Close and delete ZIP');
                    cgGalleryTransferExportHideModal();
                });
            }
        },true);
    }

    $(document).on('click','.cg_network_export_button',function (e) {
        e.preventDefault();
        cgNetworkExportOpenModal(cgNetworkExportParseConfig($(this)),$(this));
    });

    $(document).on('change','#cgNetworkPrivacyConfirm',function () {
        var checked = $(this).is(':checked');
        $(this).closest('.cgnet-admin-check').toggleClass('cgnet-admin-check--checked',checked);
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden');
    });

    $(document).on('change','#cgNetworkCustomPageToggle',function () {
        var checked = $(this).is(':checked');
        var $panel = $('#cgNetworkCustomPagePanel');
        var $input = $('#cgNetworkCustomUrl');
        $panel.toggleClass('cgnet-admin-custom-url-panel--disabled',!checked);
        $input.prop('disabled',!checked);
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden');
        if(checked){
            $input.focus();
        }
        cgNetworkExportApplyUnsavedChangesState();
    });

    $(document).on('input change','#cgNetworkExportTitle,#cgNetworkExportDescription,#cgNetworkCustomUrl',function () {
        cgNetworkExportApplyUnsavedChangesState();
    });

    $(document).on('click','#cgNetworkExportCancel',function (e) {
        e.preventDefault();
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
    });

    $(document).on('click','#cgNetworkExportSubmit',function (e) {
        e.preventDefault();
        var $modal = $('#cgNetworkExportModalContainer');
        if(!$modal.length){
            return;
        }
        if(!$('#cgNetworkPrivacyConfirm').is(':checked')){
            cgNetworkExportSetValidation('Please confirm the privacy policy before publishing.');
            return;
        }
        var linkMode = $('#cgNetworkCustomPageToggle').is(':checked') ? 'custom' : 'gallery';
        var customUrl = $.trim($('#cgNetworkCustomUrl').val() || '');
        var defaultUrl = $modal.attr('data-cg-default-url') || '';
        if($modal.attr('data-cg-mode') === 'single'){
            if(linkMode === 'custom' && !cgNetworkExportIsValidCustomUrl(customUrl,defaultUrl)){
                cgNetworkExportSetValidation('Enter a valid public URL on the same domain as the default gallery URL.');
                return;
            }
            if(linkMode !== 'custom' && !defaultUrl){
                cgNetworkExportSetValidation('No eligible gallery URL found for this export.');
                return;
            }
        }
        var $button = $(this);
        var galleryId = parseInt($modal.attr('data-cg-gallery-id'),10) || 0;
        $button.addClass('cgnet-admin-button--loading').prop('disabled',true).text('Reviewing listing...');
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden');
        cgNetworkExportSetPendingMessage('One moment please. Contest Gallery Network reviews the submitted public listing data and preview images before publishing. No personal data is submitted.');
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_network_export',
                cg_nonce: CG1LBackendNonce.nonce,
                cg_network_privacy_confirmed: '1',
                cg_network_export_mode: $modal.attr('data-cg-mode') || 'single',
                cg_network_gallery_id: $modal.attr('data-cg-gallery-id') || 0,
                cg_network_title: $('#cgNetworkExportTitle').val() || '',
                cg_network_description: $('#cgNetworkExportDescription').val() || '',
                cg_network_link_mode: linkMode,
                cg_network_custom_url: customUrl
            }
        }).done(function (response) {
            if(!response || !response.success){
                cgNetworkExportHandleError({responseJSON: response});
                return;
            }
            var publishState = response.data && response.data.publish_state ? response.data.publish_state : {
                published: 1,
                gallery_id: galleryId,
                site_detail_url: response.data && response.data.site_detail_url ? response.data.site_detail_url : '',
                gallery_detail_url: response.data && response.data.gallery_detail_url ? response.data.gallery_detail_url : ''
            };
            cgNetworkExportSetModalState(publishState);
            cgNetworkExportUpdateRowState(galleryId,publishState);
            publishState = cgNetworkExportNormalizeState(publishState);
            cgNetworkExportFormBaseline = publishState.export_settings || {};
            if(!cgNetworkExportFormBaseline.title && !cgNetworkExportFormBaseline.description && !cgNetworkExportFormBaseline.custom_url){
                cgNetworkExportFormBaseline = cgNetworkExportCurrentFormState();
            }
            cgNetworkExportSetFormState(cgNetworkExportFormBaseline,defaultUrl);
            cgNetworkExportSetMessage(
                response.data && response.data.message ? response.data.message : 'Contest Gallery Network export published.',
                false,
                response.data && response.data.gallery_detail_url ? response.data.gallery_detail_url : (response.data && response.data.site_detail_url ? response.data.site_detail_url : '')
            );
            $button.removeClass('cgnet-admin-button--loading').prop('disabled',false).text(cgNetworkExportModalButtonText(publishState));
            cgNetworkExportApplyUnsavedChangesState();
        }).fail(cgNetworkExportHandleError);
    });

    $(document).on('click','#cgNetworkRunAutoUpdateNow',function (e) {
        e.preventDefault();
        var $modal = $('#cgNetworkExportModalContainer');
        var galleryId = parseInt($modal.attr('data-cg-gallery-id'),10) || 0;
        if(!galleryId){
            return;
        }
        if(cgNetworkExportFormBaseline !== null && !cgNetworkExportFormStatesAreEqual(cgNetworkExportCurrentFormState(),cgNetworkExportFormBaseline)){
            cgNetworkExportApplyUnsavedChangesState();
            cgNetworkExportSetValidation('Save the listing changes before running an update.');
            return;
        }
        var $button = $(this);
        $button.prop('disabled',true).text('Updating ...');
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden');
        $('#cgNetworkExportResult').addClass('cgnet-admin-is-hidden').empty();
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_network_auto_update_now',
                cg_nonce: CG1LBackendNonce.nonce,
                cg_network_gallery_id: galleryId
            }
        }).done(function (response) {
            if(!response || !response.success){
                var errorState = response && response.data && response.data.publish_state ? response.data.publish_state : false;
                if(errorState){
                    cgNetworkExportSetModalState(errorState);
                    cgNetworkExportUpdateRowState(galleryId,errorState);
                }
                cgNetworkExportHandleError({responseJSON: response});
                $button.prop('disabled',false).text('Run update now');
                cgNetworkExportApplyUnsavedChangesState();
                return;
            }
            var publishState = response.data && response.data.publish_state ? response.data.publish_state : {};
            cgNetworkExportSetModalState(publishState);
            cgNetworkExportUpdateRowState(galleryId,publishState);
            cgNetworkExportSetMessage(
                response.data && response.data.message ? response.data.message : 'Contest Gallery Network auto update completed.',
                false,
                response.data && response.data.gallery_detail_url ? response.data.gallery_detail_url : (response.data && response.data.site_detail_url ? response.data.site_detail_url : '')
            );
            $button.prop('disabled',false).text('Run update now');
            cgNetworkExportApplyUnsavedChangesState();
        }).fail(function (response) {
            var publishState = response && response.responseJSON && response.responseJSON.data && response.responseJSON.data.publish_state ? response.responseJSON.data.publish_state : false;
            if(publishState){
                cgNetworkExportSetModalState(publishState);
                cgNetworkExportUpdateRowState(galleryId,publishState);
            }
            cgNetworkExportHandleError(response);
            $button.prop('disabled',false).text('Run update now');
            cgNetworkExportApplyUnsavedChangesState();
        });
    });

    $(document).on('click','#cgNetworkUnpublishSubmit',function (e) {
        e.preventDefault();
        var $modal = $('#cgNetworkExportModalContainer');
        var galleryId = parseInt($modal.attr('data-cg-gallery-id'),10) || 0;
        if(!galleryId){
            return;
        }
        if(!window.confirm('Remove this gallery listing from Contest Gallery Network?')){
            return;
        }
        var $button = $(this);
        $button.prop('disabled',true).text('Unpublishing ...');
        $('#cgNetworkExportValidation').addClass('cgnet-admin-is-hidden');
        $('#cgNetworkExportResult').addClass('cgnet-admin-is-hidden').empty();
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_network_unpublish',
                cg_nonce: CG1LBackendNonce.nonce,
                cg_network_gallery_id: galleryId
            }
        }).done(function (response) {
            if(!response || !response.success){
                var message = cgGalleryTransferGetErrorMessage({responseJSON: response},'Request failed.');
                if(message === false){
                    return;
                }
                cgNetworkExportSetMessage(message,true);
                $button.prop('disabled',false).text('Unpublish from Network');
                return;
            }
            var publishState = response.data && response.data.publish_state ? response.data.publish_state : {published:0};
            cgNetworkExportSetModalState(publishState);
            cgNetworkExportUpdateRowState(galleryId,publishState);
            cgNetworkExportFormBaseline = cgNetworkExportCurrentFormState();
            cgNetworkExportApplyUnsavedChangesState();
            cgNetworkExportSetMessage(
                response.data && response.data.message ? response.data.message : 'Contest Gallery Network listing unpublished.',
                false
            );
        }).fail(function (response) {
            var message = cgGalleryTransferGetErrorMessage(response,'Request failed.');
            if(message === false){
                return;
            }
            cgNetworkExportSetMessage(message,true);
            $button.prop('disabled',false).text('Unpublish from Network');
        });
    });

    var cgGalleryTransferImportJobId = '';
    var cgGalleryTransferImportIsUploading = false;
    var cgGalleryTransferImportIsProcessing = false;
    var cgGalleryTransferImportPollTimer = 0;
    var cgGalleryTransferImportUploadXhr = null;
    var cgGalleryTransferImportUploadAbortRequested = false;

    var cgGalleryTransferImportModal = function () {
        return $('#cgGalleryTransferImportModalContainer');
    };

    var cgGalleryTransferImportClearTimer = function () {
        if(cgGalleryTransferImportPollTimer){
            window.clearTimeout(cgGalleryTransferImportPollTimer);
            cgGalleryTransferImportPollTimer = 0;
        }
    };

    var cgGalleryTransferImportAbortUpload = function () {
        cgGalleryTransferImportUploadAbortRequested = true;
        if(cgGalleryTransferImportUploadXhr && cgGalleryTransferImportUploadXhr.readyState !== 4){
            cgGalleryTransferImportUploadXhr.abort();
        }
        cgGalleryTransferImportUploadXhr = null;
    };

    var cgGalleryTransferImportSetMode = function (mode) {
        var $modal = cgGalleryTransferImportModal();
        $modal.removeClass('cg-gallery-transfer-import-uploading cg-gallery-transfer-import-ready cg-gallery-transfer-import-processing cg-gallery-transfer-import-done cg-gallery-transfer-import-error cg-gallery-transfer-import-cancelled');
        if(mode){
            $modal.addClass('cg-gallery-transfer-import-'+mode);
        }
    };

    var cgGalleryTransferImportSetProgressBar = function (percent) {
        percent = parseInt(percent,10);
        if(isNaN(percent)){
            percent = 0;
        }
        percent = Math.max(0,Math.min(100,percent));
        $('#cgGalleryTransferImportProgressBar').css('width',percent+'%');
        $('#cgGalleryTransferImportPercent').text(percent+'%');
    };

    var cgGalleryTransferImportSetUploadProgress = function (fileName, uploadedBytes, fileSize, percent) {
        uploadedBytes = parseInt(uploadedBytes,10);
        fileSize = parseInt(fileSize,10);
        if(isNaN(uploadedBytes)){
            uploadedBytes = 0;
        }
        if(isNaN(fileSize)){
            fileSize = 0;
        }
        $('#cgGalleryTransferImportHeadline').text('Uploading import ZIP');
        $('#cgGalleryTransferImportText').text(fileName ? 'Uploading '+fileName+' in small parts.' : 'Uploading the ZIP file in small parts.');
        $('#cgGalleryTransferImportProgressArea').removeClass('cg_hide');
        cgGalleryTransferImportSetProgressBar(percent || 0);
        $('#cgGalleryTransferImportEntries').text('0/0');
    };

    var cgGalleryTransferGetFileSlice = function (file, start, end) {
        var slice = file.slice || file.webkitSlice || file.mozSlice;
        if(!slice){
            return false;
        }
        return slice.call(file,start,end);
    };

    var cgGalleryTransferImportToggleSingleUser = function () {
        var isSingle = $('input[name="cg_gallery_transfer_user_mode"]:checked').val() === 'single';
        $('#cgGalleryTransferSingleUser').prop('disabled',!isSingle);
        $('#cgGalleryTransferSingleUserWrap').toggleClass('cg-gallery-transfer-import-select-wrap-disabled',!isSingle);
    };

    var cgGalleryTransferImportResetModal = function (fileName) {
        var $modal = cgGalleryTransferImportModal();
        if(!$modal.length){
            return false;
        }
        cgGalleryTransferImportClearTimer();
        cgGalleryTransferImportUploadXhr = null;
        cgGalleryTransferImportUploadAbortRequested = false;
        cgGalleryTransferImportJobId = '';
        cgGalleryTransferImportIsUploading = true;
        cgGalleryTransferImportIsProcessing = false;
        $modal.attr('data-cg-job-id','');
        cgGalleryTransferImportSetMode('uploading');
        $('#cgGalleryTransferImportHeadline').text('Uploading import ZIP');
        $('#cgGalleryTransferImportText').text(fileName ? 'Uploading '+fileName+' in small parts.' : 'Uploading the ZIP file in small parts.');
        $('#cgGalleryTransferImportProgressArea').removeClass('cg_hide');
        cgGalleryTransferImportSetProgressBar(0);
        $('#cgGalleryTransferImportEntries').text('0/0');
        $('#cgGalleryTransferImportNewGallery').text('0');
        $('#cgGalleryTransferImportNewGalleryWrap').addClass('cg_hide');
        $('#cgGalleryTransferImportPackage').addClass('cg_hide');
        $('#cgGalleryTransferImportMapping').addClass('cg_hide');
        $('#cgGalleryTransferImportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferImportError').addClass('cg_hide').empty();
        $('#cgGalleryTransferImportJobId').val('');
        $('#cgGalleryTransferSingleUser').empty().prop('disabled',true);
        $('#cgGalleryTransferImportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Cancel upload');
        $('#cgGalleryTransferImportStart').addClass('cg_hide').prop('disabled',false).text('Start import');
        $('#cgGalleryTransferImportOpenGallery').addClass('cg_hide').attr('href','#').text('Open imported gallery');
        cgJsClassAdmin.gallery.functions.showModal('#cgGalleryTransferImportModalContainer');
        return true;
    };

    var cgGalleryTransferImportSetReady = function (data) {
        data = data || {};
        var manifest = data.manifest || {};
        var counts = manifest.counts || {};
        var users = data.users || [];
        var targetUsers = data.target_users || [];
        var $select = $('#cgGalleryTransferSingleUser');
        var hasTargetUsers = targetUsers.length > 0;
        cgGalleryTransferImportJobId = data.job_id || '';
        cgGalleryTransferImportIsUploading = false;
        cgGalleryTransferImportIsProcessing = false;
        cgGalleryTransferImportUploadXhr = null;
        cgGalleryTransferImportUploadAbortRequested = false;
        cgGalleryTransferImportClearTimer();
        cgGalleryTransferImportSetMode('ready');
        cgGalleryTransferImportModal().attr('data-cg-job-id',cgGalleryTransferImportJobId);
        $('#cgGalleryTransferImportJobId').val(cgGalleryTransferImportJobId);
        $('#cgGalleryTransferImportHeadline').text('ZIP loaded.');
        $('#cgGalleryTransferImportText').text('Choose how imported entries should be assigned before starting the import.');
        $('#cgGalleryTransferImportProgressArea').addClass('cg_hide');
        $('#cgGalleryTransferImportPackageGallery').text(manifest.source_gallery_id || '0');
        $('#cgGalleryTransferImportPackageDomain').text(manifest.source_domain || '');
        $('#cgGalleryTransferImportPackageEntries').text(counts.entries || 0);
        $('#cgGalleryTransferImportPackageVotes').text(counts.votes || 0);
        $('#cgGalleryTransferImportPackageComments').text(counts.comments || 0);
        $('#cgGalleryTransferImportPackageMedia').text(counts.media || 0);
        $('#cgGalleryTransferImportPackageSkipped').text(counts.skipped_sale_entries || 0);
        $('#cgGalleryTransferImportPackageUsers').text(users.length || 0);
        $('#cgGalleryTransferImportPackage').removeClass('cg_hide');
        $('#cgGalleryTransferImportMapping').removeClass('cg_hide');
        $('#cgGalleryTransferImportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferImportError').addClass('cg_hide').empty();
        $select.empty();
        for(var i=0;i<targetUsers.length;i++){
            $('<option/>')
                .val(targetUsers[i].ID)
                .text(targetUsers[i].display_name+' ('+targetUsers[i].user_email+')')
                .appendTo($select);
        }
        if(!hasTargetUsers){
            $('<option/>').val('0').text('No users found').appendTo($select);
        }
        $('#cgGalleryTransferUserModeSingle').prop('disabled',!hasTargetUsers);
        $('input[name="cg_gallery_transfer_user_mode"][value="none"]').prop('checked',true);
        cgGalleryTransferImportToggleSingleUser();
        $('#cgGalleryTransferImportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Cancel import');
        $('#cgGalleryTransferImportStart').removeClass('cg_hide').prop('disabled',false).text('Start import');
        $('#cgGalleryTransferImportOpenGallery').addClass('cg_hide').attr('href','#');
    };

    var cgGalleryTransferImportSetProgress = function (data) {
        data = data || {};
        var percent = data.percent || 0;
        var processed = data.processed_entries || 0;
        var total = data.total_entries || 0;
        var newGalleryId = data.new_gallery_id || 0;
        $('#cgGalleryTransferImportHeadline').text('Import in progress');
        $('#cgGalleryTransferImportText').text(newGalleryId ? 'Gallery '+newGalleryId+' is being populated with entries and media.' : 'The new gallery is being created.');
        $('#cgGalleryTransferImportProgressArea').removeClass('cg_hide');
        $('#cgGalleryTransferImportPackage').addClass('cg_hide');
        $('#cgGalleryTransferImportMapping').addClass('cg_hide');
        cgGalleryTransferImportSetProgressBar(percent);
        $('#cgGalleryTransferImportEntries').text(processed+'/'+total);
        $('#cgGalleryTransferImportNewGallery').text(newGalleryId || '0');
        $('#cgGalleryTransferImportNewGalleryWrap').toggleClass('cg_hide',!newGalleryId);
    };

    var cgGalleryTransferImportSetDone = function (data) {
        data = data || {};
        var newGalleryId = data.new_gallery_id || 0;
        var openUrl = '?page='+cgJsClassAdmin.index.functions.cgGetVersionForUrlJs()+'/index.php&option_id='+newGalleryId+'&edit_gallery=true';
        openUrl = cgJsClassAdmin.index.functions.addCgNonceToUrl(openUrl,CG1LBackendNonce.nonce);
        cgGalleryTransferImportIsUploading = false;
        cgGalleryTransferImportIsProcessing = false;
        cgGalleryTransferImportUploadXhr = null;
        cgGalleryTransferImportUploadAbortRequested = false;
        cgGalleryTransferImportClearTimer();
        cgGalleryTransferImportSetMode('done');
        cgGalleryTransferImportSetProgress(data);
        cgGalleryTransferImportSetProgressBar(100);
        $('#cgGalleryTransferImportHeadline').text('Import completed.');
        $('#cgGalleryTransferImportText').text('New gallery ID: '+newGalleryId);
        $('#cgGalleryTransferImportCloseDelete').removeClass('cg_hide').prop('disabled',false).text('Close and delete temporary files');
        $('#cgGalleryTransferImportStart').addClass('cg_hide').prop('disabled',false).text('Start import');
        $('#cgGalleryTransferImportOpenGallery').removeClass('cg_hide').attr('href',openUrl).text('Open imported gallery');
        if(data.warnings && data.warnings.length){
            $('#cgGalleryTransferImportWarnings').removeClass('cg_hide').text('Warnings: '+data.warnings.join(' | '));
        }
    };

    var cgGalleryTransferImportSetError = function (message) {
        cgGalleryTransferImportIsUploading = false;
        cgGalleryTransferImportIsProcessing = false;
        cgGalleryTransferImportUploadXhr = null;
        cgGalleryTransferImportUploadAbortRequested = false;
        cgGalleryTransferImportClearTimer();
        cgGalleryTransferImportSetMode('error');
        $('#cgGalleryTransferImportHeadline').text('Import stopped.');
        $('#cgGalleryTransferImportText').text('The import could not be completed.');
        $('#cgGalleryTransferImportError').removeClass('cg_hide').text(message);
        $('#cgGalleryTransferImportWarnings').addClass('cg_hide').empty();
        $('#cgGalleryTransferImportStart').addClass('cg_hide').prop('disabled',false).text('Start import');
        $('#cgGalleryTransferImportOpenGallery').addClass('cg_hide').attr('href','#');
        $('#cgGalleryTransferImportCloseDelete').removeClass('cg_hide').prop('disabled',false).text(cgGalleryTransferImportJobId ? 'Close and delete temporary files' : 'Close');
    };

    var cgGalleryTransferImportCleanupJob = function (callback) {
        var $modal = cgGalleryTransferImportModal();
        var jobId = cgGalleryTransferImportJobId || $modal.attr('data-cg-job-id') || $('#cgGalleryTransferImportJobId').val() || '';
        if(cgGalleryTransferImportIsUploading){
            cgGalleryTransferImportAbortUpload();
        }
        if(!jobId){
            if(callback){
                callback();
            }
            return;
        }
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_cancel',
                job_id: jobId,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).always(function () {
            cgGalleryTransferImportJobId = '';
            $modal.attr('data-cg-job-id','');
            $('#cgGalleryTransferImportJobId').val('');
            if(callback){
                callback();
            }
        });
    };

    var cgGalleryTransferImportHideModal = function () {
        cgGalleryTransferImportClearTimer();
        if(cgGalleryTransferImportIsUploading){
            cgGalleryTransferImportAbortUpload();
        }
        cgGalleryTransferImportIsUploading = false;
        cgGalleryTransferImportIsProcessing = false;
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
        cgGalleryTransferImportSetMode('');
    };

    var cgGalleryTransferHandleImportError = function (response, fallbackMessage) {
        if(fallbackMessage === 'abort' || cgGalleryTransferImportUploadAbortRequested){
            return;
        }
        if(typeof fallbackMessage !== 'string' || fallbackMessage === 'error' || fallbackMessage === 'timeout' || fallbackMessage === 'parsererror'){
            fallbackMessage = 'Import request failed.';
        }
        var message = cgGalleryTransferGetErrorMessage(response,fallbackMessage || 'Import request failed.');
        if(message === false){
            return;
        }
        if(cgGalleryTransferImportModal().length){
            cgGalleryTransferImportSetError(message);
            return;
        }
        $('#cgGalleryTransferProgress').removeClass('cg_hide').html('<span style="color:#b00000;">'+cgGalleryTransferEscapeHtml(message)+'</span>');
    };

    var cgGalleryTransferImportUploadChunk = function (file, jobId, chunkSize, chunkIndex, uploadedBytes, totalChunks) {
        if(!cgGalleryTransferImportIsUploading || cgGalleryTransferImportUploadAbortRequested){
            return;
        }
        var start = parseInt(uploadedBytes,10);
        if(isNaN(start)){
            start = 0;
        }
        var end = Math.min(start+chunkSize,file.size);
        var chunk = cgGalleryTransferGetFileSlice(file,start,end);
        if(chunk===false){
            cgGalleryTransferHandleImportError(null,'Your browser does not support chunked ZIP uploads.');
            return;
        }
        var formData = new FormData();
        formData.append('action','post_cg_gallery_transfer_import_upload_chunk');
        formData.append('cg_nonce',CG1LBackendNonce.nonce);
        formData.append('job_id',jobId);
        formData.append('chunk_index',chunkIndex);
        formData.append('chunk_offset',start);
        formData.append('total_chunks',totalChunks);
        formData.append('file_size',file.size);
        formData.append('cg_gallery_transfer_chunk',chunk,file.name+'.part');
        cgGalleryTransferImportSetUploadProgress(file.name,start,file.size,Math.round((start/Math.max(1,file.size))*100));
        cgGalleryTransferImportUploadXhr = $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: formData,
            contentType: false,
            processData: false
        }).done(function (response) {
            if(!cgGalleryTransferImportIsUploading || cgGalleryTransferImportUploadAbortRequested){
                return;
            }
            if(!response || !response.success){
                cgGalleryTransferHandleImportError({responseJSON: response},'Import ZIP chunk upload failed.');
                return;
            }
            var data = response.data || {};
            if(data.manifest){
                cgGalleryTransferImportSetReady(data);
                return;
            }
            var nextUploadedBytes = parseInt(data.uploaded_bytes,10);
            var nextChunkIndex = parseInt(data.next_chunk_index,10);
            if(isNaN(nextUploadedBytes)){
                nextUploadedBytes = end;
            }
            if(isNaN(nextChunkIndex)){
                nextChunkIndex = chunkIndex+1;
            }
            cgGalleryTransferImportSetUploadProgress(file.name,nextUploadedBytes,file.size,data.percent || Math.round((nextUploadedBytes/Math.max(1,file.size))*100));
            cgGalleryTransferImportUploadChunk(file,jobId,chunkSize,nextChunkIndex,nextUploadedBytes,totalChunks);
        }).fail(function (response, textStatus) {
            if(textStatus === 'abort' || cgGalleryTransferImportUploadAbortRequested){
                return;
            }
            cgGalleryTransferHandleImportError(response,'Import ZIP chunk upload failed. The server rejected one upload chunk. Please check server upload/security limits.');
        });
    };

    var cgGalleryTransferImportStartChunkedUpload = function (file) {
        if(!window.FormData){
            cgGalleryTransferHandleImportError(null,'Your browser does not support ZIP uploads in this admin screen.');
            return;
        }
        if(!file || !file.size){
            cgGalleryTransferHandleImportError(null,'ZIP file size could not be detected.');
            return;
        }
        if(cgGalleryTransferGetFileSlice(file,0,1)===false){
            cgGalleryTransferHandleImportError(null,'Your browser does not support chunked ZIP uploads.');
            return;
        }
        cgGalleryTransferImportUploadAbortRequested = false;
        cgGalleryTransferImportUploadXhr = $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_import_upload_start',
                file_name: file.name || 'import.zip',
                file_size: file.size,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).done(function (response) {
            if(!cgGalleryTransferImportIsUploading || cgGalleryTransferImportUploadAbortRequested){
                return;
            }
            if(!response || !response.success){
                cgGalleryTransferHandleImportError({responseJSON: response},'Import ZIP upload could not be started.');
                return;
            }
            var data = response.data || {};
            var chunkSize = parseInt(data.chunk_size,10);
            if(isNaN(chunkSize) || chunkSize<1){
                chunkSize = 1048576;
            }
            var jobId = data.job_id || '';
            if(!jobId){
                cgGalleryTransferHandleImportError(null,'Import upload job could not be started.');
                return;
            }
            cgGalleryTransferImportJobId = jobId;
            cgGalleryTransferImportModal().attr('data-cg-job-id',jobId);
            $('#cgGalleryTransferImportJobId').val(jobId);
            var totalChunks = Math.ceil(file.size/chunkSize);
            cgGalleryTransferImportUploadChunk(file,jobId,chunkSize,0,0,totalChunks);
        }).fail(function (response, textStatus) {
            if(textStatus === 'abort' || cgGalleryTransferImportUploadAbortRequested){
                return;
            }
            cgGalleryTransferHandleImportError(response,'Import ZIP upload could not be started.');
        });
    };

    $(document).on('change','#cgGalleryTransferImportFile',function () {
        var fileInput = $(this).get(0);
        if(fileInput && fileInput.files && fileInput.files.length){
            $('#cgGalleryTransferProgress').addClass('cg_hide').empty();
        }
    });

    $(document).on('submit','#cgGalleryTransferImportForm',function (e) {
        e.preventDefault();
        var fileInput = $('#cgGalleryTransferImportFile').get(0);
        if(!fileInput || !fileInput.files || !fileInput.files.length){
            $('#cgGalleryTransferProgress').removeClass('cg_hide').html('<span style="color:#b00000;">Select a ZIP file first.</span>');
            return;
        }
        if(!cgGalleryTransferImportResetModal(fileInput.files[0].name || '')){
            $('#cgGalleryTransferProgress').removeClass('cg_hide').text('Uploading and reading ZIP...');
        }else{
            $('#cgGalleryTransferProgress').addClass('cg_hide').empty();
        }
        $('#cgGalleryTransferImportPreview').addClass('cg_hide').empty();
        cgGalleryTransferImportStartChunkedUpload(fileInput.files[0]);
    });

    var cgGalleryTransferImportStep = function (jobId, mode, singleUserId) {
        if(!cgGalleryTransferImportIsProcessing){
            return;
        }
        $.ajax({
            url: 'admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_gallery_transfer_import_step',
                job_id: jobId,
                user_mapping_mode: mode,
                single_user_id: singleUserId,
                cg_nonce: CG1LBackendNonce.nonce
            }
        }).done(function (response) {
            if(!cgGalleryTransferImportIsProcessing){
                return;
            }
            if(!response || !response.success){
                cgGalleryTransferHandleImportError({responseJSON: response});
                return;
            }
            var data = response.data;
            if(data.status === 'done'){
                cgGalleryTransferImportSetDone(data);
                return;
            }
            cgGalleryTransferImportSetProgress(data);
            cgGalleryTransferImportPollTimer = window.setTimeout(function () {
                cgGalleryTransferImportStep(jobId,mode,singleUserId);
            },700);
        }).fail(cgGalleryTransferHandleImportError);
    };

    $(document).on('click','#cgGalleryTransferImportStart',function (e) {
        e.preventDefault();
        var jobId = $('#cgGalleryTransferImportJobId').val();
        var mode = $('input[name="cg_gallery_transfer_user_mode"]:checked').val() || 'none';
        var singleUserId = $('#cgGalleryTransferSingleUser').val() || 0;
        cgGalleryTransferImportJobId = jobId;
        cgGalleryTransferImportModal().attr('data-cg-job-id',jobId);
        cgGalleryTransferImportIsUploading = false;
        cgGalleryTransferImportIsProcessing = true;
        cgGalleryTransferImportSetMode('processing');
        cgGalleryTransferImportSetProgress({percent:0,processed_entries:0,total_entries:0,new_gallery_id:0});
        $('#cgGalleryTransferImportStart').prop('disabled',true).text('Starting ...');
        $('#cgGalleryTransferImportCloseDelete').addClass('cg_hide');
        cgGalleryTransferImportStep(jobId,mode,singleUserId);
    });

    $(document).on('change','input[name="cg_gallery_transfer_user_mode"]',function () {
        cgGalleryTransferImportToggleSingleUser();
    });

    $(document).on('click','#cgGalleryTransferImportCloseDelete, #cgGalleryTransferImportModalContainer .cg_message_close',function (e) {
        e.preventDefault();
        if(cgGalleryTransferImportIsProcessing){
            return;
        }
        $('#cgGalleryTransferImportCloseDelete').prop('disabled',true).text(cgGalleryTransferImportIsUploading ? 'Cancelling ...' : 'Deleting ...');
        cgGalleryTransferImportCleanupJob(function () {
            $('#cgGalleryTransferImportCloseDelete').prop('disabled',false).text('Cancel import');
            cgGalleryTransferImportHideModal();
        });
    });

    $(document).on('click','#cgGalleryTransferImportOpenGallery',function (e) {
        e.preventDefault();
        var href = $(this).attr('href') || '#';
        if(href === '#'){
            return;
        }
        $(this).text('Opening ...');
        cgGalleryTransferImportCleanupJob(function () {
            window.location.href = href;
        });
    });

    if(document.addEventListener){
        document.addEventListener('click',function (e) {
            var modal = document.getElementById('cgGalleryTransferImportModalContainer');
            if(!modal){
                return;
            }
            var modalClass = ' '+modal.className+' ';
            if(modalClass.indexOf(' cg_active ') === -1 || modalClass.indexOf(' cg_hide ') > -1){
                return;
            }
            var isUploading = modalClass.indexOf(' cg-gallery-transfer-import-uploading ') > -1;
            var isBlocked = modalClass.indexOf(' cg-gallery-transfer-import-processing ') > -1;
            var target = e.target;
            var isBackdrop = target && target.id === 'cgBackendBackgroundDrop';
            var isClose = false;
            var isInImportModal = false;
            var node = target;
            while(node && node !== document){
                if(node.className && typeof node.className === 'string' && (' '+node.className+' ').indexOf(' cg_message_close ') > -1){
                    isClose = true;
                }
                if(node.id === 'cgGalleryTransferImportModalContainer'){
                    isInImportModal = true;
                    break;
                }
                node = node.parentNode;
            }
            if(isBlocked && (isBackdrop || (isClose && isInImportModal))){
                e.preventDefault();
                e.stopPropagation();
                if(e.stopImmediatePropagation){
                    e.stopImmediatePropagation();
                }
                return;
            }
            if(!isBlocked && (isBackdrop || (isClose && isInImportModal))){
                e.preventDefault();
                e.stopPropagation();
                if(e.stopImmediatePropagation){
                    e.stopImmediatePropagation();
                }
                $('#cgGalleryTransferImportCloseDelete').prop('disabled',true).text(isUploading ? 'Cancelling ...' : 'Deleting ...');
                cgGalleryTransferImportCleanupJob(function () {
                    $('#cgGalleryTransferImportCloseDelete').prop('disabled',false).text('Cancel import');
                    cgGalleryTransferImportHideModal();
                });
            }
        },true);
    }

    $(document).on('click','body.cg_upload_modal_opened',function (e) {

        if($(e.target).closest('#cgCopyMessageContainer').length==1){
            return;
        }else{
            $('body').removeClass('cg_upload_modal_opened');
            $('#cgCopyMessageContainer').addClass('cg_hide');
        }
    });

    $(document).on('click','#cgCopyMessageClose',function (e) {
        $('body').removeClass('cg_upload_modal_opened');
        $('#cgCopyMessageContainer').addClass('cg_hide');
        cgJsClassAdmin.gallery.functions.hideCgBackendBackgroundDropAndContainer();
    });

    $(document).on('click','.cg_copy_submit',function (e) {

        if($(this).hasClass('cg_submitted')){return;}
        e.preventDefault();

        cgJsClassAdmin.mainMenu.vars.formLinkObject = $(this).closest('form');

        var $cgCopyMessageContainer = $('#cgCopyMessageContainer');
        var $table_gallery_info = $(this).closest('.table_gallery_info');
        var left = $('.table_gallery_info').width()/2-$cgCopyMessageContainer.width()/2+$('.table_gallery_info').offset().left;

        $cgCopyMessageContainer.css('left',left+'px');
        $cgCopyMessageContainer.removeClass('cg_hide');
        // otherwise instant initiating and off click
        $('body').addClass('cg_upload_modal_opened');
        $('#cgCopyMessageSubmit').attr('data-cg-copy-id',$(this).attr('data-cg-copy-id'));
        $('#cgCopyMessageContentHeader').text('Copy gallery ID '+$(this).attr('data-cg-copy-id')+' ?');

        // only everything is possible to copy for old versions!
        if(parseInt($(this).attr('data-cg-version-to-copy'))<10){
            $cgCopyMessageContainer.find('.cg_copy_type_options_container,.cg_copy_type_options_and_images_container').addClass('cg_hide');
            $cgCopyMessageContainer.find('#cg_copy_type_all').prop('checked',true);
        }else{
            $cgCopyMessageContainer.find('.cg_copy_type_options_container,.cg_copy_type_options_and_images_container').removeClass('cg_hide');
        }

        if($(this).closest('form').find('.cg_ecommerce_entries_count').val()>=1){
            $cgCopyMessageContainer.find('#cg_copy_without_ecom_entries').removeClass('cg_hide');
        }else{
            $cgCopyMessageContainer.find('#cg_copy_without_ecom_entries').addClass('cg_hide');
        }

        $cgCopyMessageContainer.find('.cg-copy-prev-7-text').remove();

        if($table_gallery_info.find('.cg-copy-prev-7-text').length){

            $table_gallery_info.find('.cg-copy-prev-7-text').clone().removeClass('cg_hide').insertBefore($cgCopyMessageContainer.find('#cgCopyMessageSubmitContainer'));

        }else{
            $cgCopyMessageContainer.find('.cg-copy-prev-7-text').remove();
        }

        if($(this).attr('data-cg-copy-fb-on')==1){
            $('#cg_copy_type_all_fb_hint').removeClass('cg_hide');
        }else{
            $('#cg_copy_type_all_fb_hint').addClass('cg_hide');
        }

        if($(this).attr('data-cg-copy-for-v14-explanation')==1){
            $('#cg_copy_for_v14_explanation').removeClass('cg_hide');
        }else{
            $('#cg_copy_for_v14_explanation').addClass('cg_hide');
        }

        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();

    });

    $(document).on('click','#cgCopyMessageSubmit',function (e) {
        e.preventDefault(e);
        var $form = $('#cgCopySubmit'+$(this).attr('data-cg-copy-id')).closest('form');
        var $cg_copy_type = $('.cg_copy_type:checked').clone().addClass('cg_hide');
        $form.prepend($cg_copy_type);
        $('#cgCopyMessageContainer').addClass('cg_hide');
        $('#mainTable').addClass('cg_hide');
        $('#cgCopyInProgressOnSubmit').removeClass('cg_hide');
        cgJsClassAdmin.index.functions.cgLoadBackend(cgJsClassAdmin.mainMenu.vars.formLinkObject.clone(),true, undefined, undefined, true);
    });

    $(document).on('click','#cgPurchaseLinkAndProVersionKeyEnterButton',function (e) {
        e.preventDefault(e);
        var $cgPurchaseLinkAndProVersionKeyEnter = $('#cgPurchaseLinkAndProVersionKeyEnter');
        if($cgPurchaseLinkAndProVersionKeyEnter.hasClass('cg_hide')){
            $cgPurchaseLinkAndProVersionKeyEnter.removeClass('cg_hide');
            $('#cgSwitchKeyToAnotherDomainFormContainer').addClass('cg_hide');
        }else{
            $cgPurchaseLinkAndProVersionKeyEnter.addClass('cg_hide');
        }
    });

    $(document).on('click','#cgSwitchKeyToAnotherDomainFormButton',function (e) {
        e.preventDefault(e);
        var $cgSwitchKeyToAnotherDomainFormContainer = $('#cgSwitchKeyToAnotherDomainFormContainer');
        if($cgSwitchKeyToAnotherDomainFormContainer.hasClass('cg_hide')){
            $cgSwitchKeyToAnotherDomainFormContainer.removeClass('cg_hide');
            $('#cgPurchaseLinkAndProVersionKeyEnter').addClass('cg_hide');
        }else{
            $cgSwitchKeyToAnotherDomainFormContainer.addClass('cg_hide');
        }
    });

    $(document).on('click','#cgSubmitDomainSwitch',function (e) {
        var $cgSubmitDomainSwitchCheck = $('#cgSubmitDomainSwitchCheck');
        if(!$cgSubmitDomainSwitchCheck.prop('checked')){
            e.preventDefault(e);
            $('#cgSubmitDomainSwitchCheckError').removeClass('cg_hide');
        }
    });

    $(document).on('click','#cgSubmitDomainSwitchCheck',function (e) {
        if($(this).prop('checked')){
            $('#cgSubmitDomainSwitchCheckError').addClass('cg_hide');
        } else{
            $('#cgSubmitDomainSwitchCheckError').removeClass('cg_hide');
        }
    });

});
