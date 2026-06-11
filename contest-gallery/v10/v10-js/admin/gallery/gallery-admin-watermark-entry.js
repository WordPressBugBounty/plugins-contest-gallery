(function($){

    cgJsClassAdmin.gallery.entryWatermark = cgJsClassAdmin.gallery.entryWatermark || {};

    cgJsClassAdmin.gallery.entryWatermark.state = {
        files: {},
        order: [],
        activeWpUpload: 0,
        previewTimer: null,
        previewRequestId: 0,
        bulkAfterSave: null,
        bulkJob: null
    };

    cgJsClassAdmin.gallery.entryWatermark.isProVersion = function(){
        return $('#cgWatermarkContainer').attr('data-cg-is-pro') === '1';
    };

    cgJsClassAdmin.gallery.entryWatermark.normalizeTitle = function(title){
        title = String(title || '').trim();
        if(!title){
            title = 'Contest Gallery';
        }
        if(title.length > 256){
            title = title.substring(0, 256);
        }
        return title;
    };

    cgJsClassAdmin.gallery.entryWatermark.normalizeSettings = function(settings){
        settings = $.extend({}, settings || {});
        settings.WatermarkTitle = cgJsClassAdmin.gallery.entryWatermark.normalizeTitle(settings.WatermarkTitle);
        if(!cgJsClassAdmin.gallery.entryWatermark.isProVersion()){
            settings.WatermarkPosition = 'center';
            settings.WatermarkSize = (String(settings.WatermarkSize) === '256') ? '256' : '512';
        }
        return settings;
    };

    cgJsClassAdmin.gallery.entryWatermark.defaults = function(){
        return cgJsClassAdmin.gallery.entryWatermark.normalizeSettings({
            WatermarkTitle: localStorage.getItem('cgWatermarkTitle') || 'Contest Gallery',
            WatermarkPosition: localStorage.getItem('cgWatermarkPosition') || 'center',
            WatermarkSize: localStorage.getItem('cgWatermarkSize') || (cgJsClassAdmin.gallery.entryWatermark.isProVersion() ? '64' : '512')
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.getSettingsFromControls = function(){
        return cgJsClassAdmin.gallery.entryWatermark.normalizeSettings({
            WatermarkTitle: $('#cgEntryWatermarkInputTitle').val() || 'Contest Gallery',
            WatermarkPosition: $('#cgEntryWatermarkSelectPosition').val() || 'center',
            WatermarkSize: $('#cgEntryWatermarkSelectSize').val() || '64'
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.applySettingsToControls = function(settings){
        settings = cgJsClassAdmin.gallery.entryWatermark.normalizeSettings($.extend({}, cgJsClassAdmin.gallery.entryWatermark.defaults(), settings || {}));
        $('#cgEntryWatermarkInputTitle').val(settings.WatermarkTitle);
        $('#cgEntryWatermarkSelectPosition').val(settings.WatermarkPosition);
        $('#cgEntryWatermarkSelectSize').val(settings.WatermarkSize);
    };

    cgJsClassAdmin.gallery.entryWatermark.storeSettings = function(settings){
        settings = cgJsClassAdmin.gallery.entryWatermark.normalizeSettings(settings);
        localStorage.setItem('cgWatermarkTitle', settings.WatermarkTitle);
        localStorage.setItem('cgWatermarkPosition', settings.WatermarkPosition);
        localStorage.setItem('cgWatermarkSize', settings.WatermarkSize);
    };

    cgJsClassAdmin.gallery.entryWatermark.escapeHtml = function(value){
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    cgJsClassAdmin.gallery.entryWatermark.getNonce = function(){
        return (typeof CG1LBackendNonce !== 'undefined' && CG1LBackendNonce.nonce) ? CG1LBackendNonce.nonce : '';
    };

    cgJsClassAdmin.gallery.entryWatermark.addPreviewCacheBuster = function(url){
        if(!url){
            return '';
        }
        return url + (url.indexOf('?') === -1 ? '?' : '&') + 'time=' + Math.floor(new Date().getTime()/1000);
    };

    cgJsClassAdmin.gallery.entryWatermark.getPreviewSourceUrl = function(file){
        if(!file){
            return '';
        }

        if(file.watermarked){
            var $container = $('#cgWatermarkContainer');
            return cgJsClassAdmin.gallery.entryWatermark.addPreviewCacheBuster(
                'admin-ajax.php?action=post_cg_get_entry_watermark_preview_source' +
                '&cg_nonce=' + encodeURIComponent(cgJsClassAdmin.gallery.entryWatermark.getNonce()) +
                '&GalleryID=' + encodeURIComponent($container.find('#cgEntryWatermarkGalleryID').val()) +
                '&realId=' + encodeURIComponent($container.find('#cgEntryWatermarkRealId').val()) +
                '&WpUpload=' + encodeURIComponent(file.WpUpload) +
                '&cgGalleryHash=' + encodeURIComponent($container.find('#cgEntryWatermarkGalleryHash').val())
            );
        }

        return cgJsClassAdmin.gallery.entryWatermark.addPreviewCacheBuster(file.preview_url);
    };

    cgJsClassAdmin.gallery.entryWatermark.showRawPreviewImage = function($preview,url){
        $preview.empty();
        if(url){
            $preview.append($('<img>', {
                src: url,
                alt: ''
            })).removeClass('cg_hide');
        }
    };

    cgJsClassAdmin.gallery.entryWatermark.setLoading = function(isLoading){
        var $container = $('#cgWatermarkContainer');
        if(isLoading){
            $container.find('#cgEntryWatermarkFormLoaderContainer').removeClass('cg_hide');
            $container.find('#cgEntryWatermarkFormContent').addClass('cg_hide');
        }else{
            $container.find('#cgEntryWatermarkFormLoaderContainer').addClass('cg_hide');
            $container.find('#cgEntryWatermarkFormContent').removeClass('cg_hide');
        }
    };

    cgJsClassAdmin.gallery.entryWatermark.setSaving = function(isSaving){
        var $container = $('#cgWatermarkContainer');
        cgJsClassAdmin.gallery.entryWatermark.setLoading(isSaving);
        $container.find('#cgEntryWatermarkSave').prop('disabled', isSaving);
    };

    cgJsClassAdmin.gallery.entryWatermark.handleAjaxError = function(xhr, fallbackMessage){
        var data = {};
        if(xhr && xhr.responseJSON && xhr.responseJSON.data){
            data = xhr.responseJSON.data;
        }

        if(data.code && data.code === 'cg_nonce_invalid'){
            var version = data.version ? data.version : cgJsClassAdmin.index.functions.cgGetVersionForUrlJs();
            cgJsClassAdmin.index.functions.isInvalidNonce($,'###cg_version###'+version+'###cg_version######cg_nonce_invalid###');
            return;
        }

        if(xhr && xhr.responseText && cgJsClassAdmin.index.functions.isInvalidNonce($,xhr.responseText)){
            return;
        }

        var message = data.message ? data.message : '';
        if(!message && xhr && xhr.responseText){
            message = xhr.responseText;
        }
        if(!message){
            message = fallbackMessage || 'Watermark could not be saved';
        }

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(message);
    };

    cgJsClassAdmin.gallery.entryWatermark.getNoFilesDefaultMessage = function(){
        return 'No JPG, PNG or GIF image files available for watermarking';
    };

    cgJsClassAdmin.gallery.entryWatermark.getSellingLockedMessage = function(){
        return 'Already prepared for selling - deactivate sale download first';
    };

    cgJsClassAdmin.gallery.entryWatermark.getNoSelectableFilesMessage = function(){
        var hasSupportedLocked = false;
        var hasSupportedUnlocked = false;
        var hasRestoreOnlyUnlocked = false;
        var firstUnsupportedReason = '';

        for(var i=0; i<cgJsClassAdmin.gallery.entryWatermark.state.order.length; i++){
            var WpUpload = cgJsClassAdmin.gallery.entryWatermark.state.order[i];
            var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
            if(!file){
                continue;
            }

            if(file.supported && file.ecommerce_locked){
                hasSupportedLocked = true;
            }else if(file.supported){
                hasSupportedUnlocked = true;
            }else if(file.can_restore && !file.ecommerce_locked){
                hasRestoreOnlyUnlocked = true;
            }else if(file.unsupported_reason && !firstUnsupportedReason && !file.ecommerce_locked){
                firstUnsupportedReason = file.unsupported_reason;
            }
        }

        if(hasSupportedLocked && !hasSupportedUnlocked && !hasRestoreOnlyUnlocked){
            return cgJsClassAdmin.gallery.entryWatermark.getSellingLockedMessage();
        }

        if(firstUnsupportedReason && !hasSupportedUnlocked && !hasRestoreOnlyUnlocked){
            return firstUnsupportedReason;
        }

        if(hasRestoreOnlyUnlocked && !hasSupportedUnlocked){
            return 'Uncheck restore-only files to restore original';
        }

        return cgJsClassAdmin.gallery.entryWatermark.getNoFilesDefaultMessage();
    };

    cgJsClassAdmin.gallery.entryWatermark.showNoFilesMessage = function(message){
        $('#cgEntryWatermarkNoFiles').text(message || cgJsClassAdmin.gallery.entryWatermark.getNoSelectableFilesMessage()).removeClass('cg_hide');
    };

    cgJsClassAdmin.gallery.entryWatermark.hideNoFilesMessage = function(){
        $('#cgEntryWatermarkNoFiles').text(cgJsClassAdmin.gallery.entryWatermark.getNoFilesDefaultMessage()).addClass('cg_hide');
    };

    cgJsClassAdmin.gallery.entryWatermark.normalizeBulkMode = function(mode){
        return mode === 'restore' ? 'restore' : 'watermark';
    };

    cgJsClassAdmin.gallery.entryWatermark.getBulkLabels = function(mode){
        mode = cgJsClassAdmin.gallery.entryWatermark.normalizeBulkMode(mode);
        if(mode === 'restore'){
            return {
                mode: 'restore',
                title: 'Unwatermark entries',
                save: 'Unwatermark entries',
                noEntries: 'No entries available for unwatermarking',
                prepareTitle: 'Checking watermarked image files...',
                prepareStatus: 'Preparing files to unwatermark',
                processingStatus: 'Restoring original files',
                finishedStatus: 'Unwatermark finished',
                noJobsTitle: 'No watermarked image files to unwatermark',
                noJobsMeta: 'No watermarked image files to unwatermark',
                countSingular: ' image file to unwatermark',
                countPlural: ' image files to unwatermark',
                countReadySingular: ' watermarked image file',
                countReadyPlural: ' watermarked image files',
                savePreparedSingular: 'Unwatermark 1 file',
                savePreparedPlural: 'Unwatermark {count} files',
                couldNotPrepare: 'Bulk unwatermark could not be prepared',
                couldNotSave: 'Bulk unwatermark could not be saved',
                couldNotRefresh: 'Unwatermark frontend data could not be refreshed',
                finishedMessage: 'Bulk unwatermark finished',
                savedMessage: 'Bulk unwatermark saved',
                stoppedMeta: 'Unwatermark stopped before all files were processed'
            };
        }

        return {
            mode: 'watermark',
            title: 'Watermark entries',
            save: 'Watermark entries',
            noEntries: 'No entries available for watermarking',
            prepareTitle: 'Preparing image files...',
            prepareStatus: 'Preparing watermark files',
            processingStatus: 'Processing watermark files',
            finishedStatus: 'Watermark finished',
            noJobsTitle: 'No new image files to watermark',
            noJobsMeta: 'No JPG, PNG or GIF image files available for watermarking',
            countSingular: ' image file to watermark',
            countPlural: ' image files to watermark',
            couldNotPrepare: 'Bulk watermark could not be prepared',
            couldNotSave: 'Bulk watermark could not be saved',
            couldNotRefresh: 'Watermark frontend data could not be refreshed',
            finishedMessage: 'Bulk watermark finished',
            savedMessage: 'Bulk watermark saved',
            stoppedMeta: 'Watermark stopped before all files were processed'
        };
    };

    cgJsClassAdmin.gallery.entryWatermark.collectVisibleEntryIds = function(){
        var ids = [];
        var idsMap = {};

        $('#cgSortable .cgSortableDiv').each(function(){
            var $entry = $(this);
            var realId = parseInt($entry.attr('data-cg-real-id') || $entry.find('.cg_real_id').val(), 10);
            if(realId && !idsMap[realId]){
                idsMap[realId] = true;
                ids.push(realId);
            }
        });

        return ids;
    };

    cgJsClassAdmin.gallery.entryWatermark.prepareBulkAfterSave = function(mode){
        mode = cgJsClassAdmin.gallery.entryWatermark.normalizeBulkMode(mode);
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(mode);
        var entryIds = cgJsClassAdmin.gallery.entryWatermark.collectVisibleEntryIds();
        if(!entryIds.length){
            cgJsClassAdmin.gallery.entryWatermark.state.bulkAfterSave = null;
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.noEntries);
            return false;
        }

		cgJsClassAdmin.gallery.entryWatermark.state.bulkAfterSave = {
			entryIds: entryIds,
            mode: mode
		};
		return true;
	};

    cgJsClassAdmin.gallery.entryWatermark.openPendingBulkAfterSave = function(){
        var pending = cgJsClassAdmin.gallery.entryWatermark.state.bulkAfterSave;
        if(!pending || !pending.entryIds || !pending.entryIds.length){
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.state.bulkAfterSave = null;

        var visibleIds = cgJsClassAdmin.gallery.entryWatermark.collectVisibleEntryIds();
        var visibleMap = {};
        var entryIds = [];
        for(var i=0; i<visibleIds.length; i++){
            visibleMap[visibleIds[i]] = true;
        }
        for(var j=0; j<pending.entryIds.length; j++){
            if(visibleMap[pending.entryIds[j]]){
                entryIds.push(pending.entryIds[j]);
            }
        }

        if(!entryIds.length){
            var noEntriesLabels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(pending.mode);
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(noEntriesLabels.noEntries);
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.openBulk(entryIds, pending.mode);
    };

    cgJsClassAdmin.gallery.entryWatermark.resetBulkProgress = function(total, mode){
        total = parseInt(total, 10) || 0;
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(mode);
        var $container = $('#cgWatermarkContainer');
        $container.find('#cgEntryWatermarkBulkProgress').addClass('cg_hide');
        $container.find('#cgEntryWatermarkBulkProgressStatus').text(labels.prepareStatus);
        $container.find('#cgEntryWatermarkBulkProgressPercent').text('0%');
        $container.find('#cgEntryWatermarkBulkProgressBar').css('width', '0%');
        $container.find('#cgEntryWatermarkBulkProgressMeta').text('0 / '+total+' files processed');
    };

    cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress = function(processed, total, status, metaSuffix){
        processed = parseInt(processed, 10) || 0;
        total = parseInt(total, 10) || 0;
        var percent = total ? Math.floor((processed / total) * 100) : 0;
        if(percent > 100){
            percent = 100;
        }

        var meta = processed+' / '+total+' files processed';
        if(metaSuffix){
            meta += ' - '+metaSuffix;
        }

        var $container = $('#cgWatermarkContainer');
        $container.find('#cgEntryWatermarkBulkProgress').removeClass('cg_hide');
        $container.find('#cgEntryWatermarkBulkProgressStatus').text(status || 'Processing watermark files');
        $container.find('#cgEntryWatermarkBulkProgressPercent').text(percent+'%');
        $container.find('#cgEntryWatermarkBulkProgressBar').css('width', percent+'%');
        $container.find('#cgEntryWatermarkBulkProgressMeta').text(meta);
    };

    cgJsClassAdmin.gallery.entryWatermark.completeBulkProgress = function(status, meta){
        var $container = $('#cgWatermarkContainer');
        $container.find('#cgEntryWatermarkBulkProgress').removeClass('cg_hide');
        $container.find('#cgEntryWatermarkBulkProgressStatus').text(status || 'Watermark finished');
        $container.find('#cgEntryWatermarkBulkProgressPercent').text('100%');
        $container.find('#cgEntryWatermarkBulkProgressBar').css('width', '100%');
        $container.find('#cgEntryWatermarkBulkProgressMeta').text(meta || 'Processing finished');
    };

    cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText = function(job){
        var parts = [];
        if(job.skippedSame){
            parts.push(job.skippedSame+' same settings');
        }
        if(job.skippedUnsupported){
            parts.push(job.skippedUnsupported+' unsupported');
        }
        if(job.skippedLocked){
            parts.push(job.skippedLocked+' selling locked');
        }
        if(job.skippedMissing){
            parts.push(job.skippedMissing+' missing');
        }
        if(job.skippedNotWatermarked){
            parts.push(job.skippedNotWatermarked+' not watermarked');
        }
        return parts.length ? parts.join(', ')+' skipped' : '';
    };

    cgJsClassAdmin.gallery.entryWatermark.getEntryCountText = function(entryCount){
        entryCount = parseInt(entryCount, 10) || 0;
        return entryCount + (entryCount === 1 ? ' entry on this page' : ' entries on this page');
    };

    cgJsClassAdmin.gallery.entryWatermark.getPreparedRestoreCountText = function(job){
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels('restore');
        var total = job ? (parseInt(job.total, 10) || 0) : 0;
        return total + (total === 1 ? labels.countReadySingular : labels.countReadyPlural);
    };

    cgJsClassAdmin.gallery.entryWatermark.setRestoreSummary = function(title, meta, show){
        var $container = $('#cgWatermarkContainer');
        $container.find('.cg_entry_watermark_restore_summary_row').toggleClass('cg_hide', !show);
        $container.find('#cgEntryWatermarkRestoreSummaryTitle').text(title || '');
        $container.find('#cgEntryWatermarkRestoreSummaryMeta').text(meta || '');
    };

    cgJsClassAdmin.gallery.entryWatermark.applyBulkPrepareResponseToJob = function(job, data){
        if(!job){
            return;
        }
        data = data || {};
        job.jobs = data.jobs || [];
        job.total = parseInt(data.total, 10) || job.jobs.length;
        job.skippedSame = parseInt(data.skipped_same, 10) || 0;
        job.skippedUnsupported = parseInt(data.skipped_unsupported, 10) || 0;
        job.skippedLocked = parseInt(data.skipped_locked, 10) || 0;
        job.skippedMissing = parseInt(data.skipped_missing, 10) || 0;
        job.skippedNotWatermarked = parseInt(data.skipped_not_watermarked, 10) || 0;
        job.settings = data.settings || job.settings || {};
    };

    cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState = function(){
        var $container = $('#cgWatermarkContainer');
        var $controls = $container.find('#cgEntryWatermarkInputTitle,#cgEntryWatermarkSelectPosition,#cgEntryWatermarkSelectSize');
        var $settingsPanel = $container.find('.cg_entry_watermark_settings_panel');
        var isProcessing = $container.hasClass('cg_entry_watermark_processing');
        var isBulkMode = $container.hasClass('cg_entry_watermark_bulk_mode');
        var isRestoreBulkMode = $container.hasClass('cg_entry_watermark_restore_bulk_mode');
        var isDisabled = isProcessing;

        if(isBulkMode){
            isDisabled = isDisabled || isRestoreBulkMode;
        }else{
            var WpUpload = cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload;
            var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
            isDisabled = isDisabled || !file || !file.enabled;
        }

        $controls.prop('disabled', isDisabled);
        $settingsPanel.toggleClass('cg_disabled_watermark', isDisabled).attr('aria-disabled', isDisabled ? 'true' : 'false');
    };

    cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing = function(isProcessing){
        var $container = $('#cgWatermarkContainer');
        $container.toggleClass('cg_entry_watermark_processing', !!isProcessing);
        $container.find('#cgEntryWatermarkSave').prop('disabled', !!isProcessing);
        cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState();
    };

    cgJsClassAdmin.gallery.entryWatermark.prepareRestoreBulk = function(startAfterPrepare){
        var $container = $('#cgWatermarkContainer');
        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob;
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels('restore');
        if(!job || job.mode !== 'restore' || !job.entryIds || !job.entryIds.length){
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.noEntries);
            return;
        }

        if(job.preparing){
            return;
        }

        job.preparing = true;
        job.prepared = false;
        job.processed = 0;
        job.total = 0;
        job.jobs = [];
        job.changedEntryIds = {};
        job.skippedSame = 0;
        job.skippedUnsupported = 0;
        job.skippedLocked = 0;
        job.skippedMissing = 0;
        job.skippedNotWatermarked = 0;

        $container.find('#cgEntryWatermarkTitleLabel').text(labels.prepareTitle);
        $container.find('#cgEntryWatermarkEntryIdTitle').text('');
        cgJsClassAdmin.gallery.entryWatermark.setRestoreSummary(labels.prepareTitle, cgJsClassAdmin.gallery.entryWatermark.getEntryCountText(job.entryCount), true);
        cgJsClassAdmin.gallery.entryWatermark.resetBulkProgress(0, 'restore');
        $container.find('#cgEntryWatermarkSave').val('Checking...').prop('disabled', true);

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_prepare_bulk_entry_unwatermark',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val(),
                entryIds: job.entryIds
            }
        }).done(function(response){
            if(!response || !response.success || !response.data){
                job.preparing = false;
                $container.find('#cgEntryWatermarkSave').val(labels.save).prop('disabled', false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.couldNotPrepare);
                return;
            }

            job.preparing = false;
            job.prepared = true;
            cgJsClassAdmin.gallery.entryWatermark.applyBulkPrepareResponseToJob(job, response.data);
            var skipText = cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job);

            if(!job.total){
                $container.find('#cgEntryWatermarkTitleLabel').text(labels.noJobsTitle);
                $container.find('#cgEntryWatermarkEntryIdTitle').text('');
                cgJsClassAdmin.gallery.entryWatermark.setRestoreSummary(labels.noJobsTitle, skipText || cgJsClassAdmin.gallery.entryWatermark.getEntryCountText(job.entryCount), true);
                $container.find('#cgEntryWatermarkSave').val(labels.save).prop('disabled', true);
                return;
            }

            var countText = cgJsClassAdmin.gallery.entryWatermark.getPreparedRestoreCountText(job);
            var meta = 'from '+cgJsClassAdmin.gallery.entryWatermark.getEntryCountText(job.entryCount);
            if(skipText){
                meta += ' - '+skipText;
            }
            $container.find('#cgEntryWatermarkTitleLabel').text(countText);
            $container.find('#cgEntryWatermarkEntryIdTitle').text('');
            cgJsClassAdmin.gallery.entryWatermark.setRestoreSummary(countText, meta, true);
            $container.find('#cgEntryWatermarkSave').val(job.total === 1 ? labels.savePreparedSingular : labels.savePreparedPlural.replace('{count}', job.total)).prop('disabled', false);

            if(startAfterPrepare){
                cgJsClassAdmin.gallery.entryWatermark.saveBulk();
            }
        }).fail(function(xhr){
            job.preparing = false;
            $container.find('#cgEntryWatermarkSave').val(labels.save).prop('disabled', false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, labels.couldNotPrepare);
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.hasUnsavedEntryChanges = function($sortableDiv){
        if(!$sortableDiv || !$sortableDiv.length){
            return false;
        }

        return !!(
            $sortableDiv.find('.cg_value_changed').length ||
            $sortableDiv.find('.cg_category_select:not(.cg_disabled_send)').length ||
            $sortableDiv.find('.cg_rThumb:not(.cg_disabled_send)').length ||
            $sortableDiv.find('.cg_multiple_files_for_post:not(.cg_disabled_send)').length
        );
    };

    cgJsClassAdmin.gallery.entryWatermark.showSaveChangesFirst = function($sortableDiv){
        $('#cgWatermarkContainer').addClass('cg_hide').removeClass('cg_active');
        $('#cgBackendBackgroundDrop').removeClass('cg_active cg_high_overlay cg_pointer_events_none').addClass('cg_hide');
        $('body').removeClass('cg_no_scroll cg_overflow_hidden cg_pointer_events_none cg_overflow_y_hidden');

        cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Save changes first before watermarking this entry');
    };

    cgJsClassAdmin.gallery.entryWatermark.saveEntryChangesAndOpenWatermark = function($sortableDiv){
        if(!$sortableDiv || !$sortableDiv.length){
            return;
        }

        var realId = parseInt($sortableDiv.find('.cg_real_id').val(), 10);
        if(!realId){
            cgJsClassAdmin.gallery.entryWatermark.showSaveChangesFirst($sortableDiv);
            return;
        }

        cgJsClassAdmin.gallery.vars.$sortableDiv = $sortableDiv;

        if(cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv){
            cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
        }else{
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Changes will be saved', true);
        }

        cgJsClassAdmin.gallery.reload.entry(realId, true, false, undefined, undefined, undefined, false, undefined, function(savedRealId){
            var $freshSortableDiv = $('#div'+savedRealId);

            if(!$freshSortableDiv.length){
                cgJsClassAdmin.gallery.entryWatermark.showSaveChangesFirst($sortableDiv);
                return;
            }

            cgJsClassAdmin.gallery.entryWatermark.open($freshSortableDiv, true);
        }, [realId], true);
    };

    cgJsClassAdmin.gallery.entryWatermark.openBulk = function(entryIds, mode){
        var $container = $('#cgWatermarkContainer');
        mode = cgJsClassAdmin.gallery.entryWatermark.normalizeBulkMode(mode);
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(mode);
        entryIds = $.isArray(entryIds) ? entryIds : [];

        cgJsClassAdmin.gallery.vars.$sortableDiv = null;
        cgJsClassAdmin.gallery.entryWatermark.state.files = {};
        cgJsClassAdmin.gallery.entryWatermark.state.order = [];
        cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload = 0;
        cgJsClassAdmin.gallery.entryWatermark.state.bulkJob = {
            mode: mode,
            entryIds: entryIds,
            entryCount: entryIds.length,
            jobs: [],
            processed: 0,
            total: 0,
            prepared: false,
            preparing: false,
            skippedSame: 0,
            skippedUnsupported: 0,
            skippedLocked: 0,
            skippedMissing: 0,
            skippedNotWatermarked: 0,
            changedEntryIds: {}
        };

        var entryCountText = cgJsClassAdmin.gallery.entryWatermark.getEntryCountText(entryIds.length);
        $container.find('#cgEntryWatermarkRealId').val('');
        $container.find('.cg_shortcode_conf_title_main').text(labels.title);
        $container.find('#cgEntryWatermarkTitleLabel').text(mode === 'restore' ? labels.prepareTitle : entryCountText);
        $container.find('#cgEntryWatermarkEntryIdTitle').text('');
        $container.find('#cgEntryWatermarkFiles').empty();
        $container.find('#cgEntryWatermarkPreviewImage').empty();
        cgJsClassAdmin.gallery.entryWatermark.hideNoFilesMessage();
        $container.find('#cgEntryWatermarkSave').val(labels.save).prop('disabled', false);
        if(mode === 'watermark'){
            cgJsClassAdmin.gallery.entryWatermark.applySettingsToControls();
        }
        cgJsClassAdmin.gallery.entryWatermark.setRestoreSummary('', '', false);
        cgJsClassAdmin.gallery.entryWatermark.resetBulkProgress(0, mode);
        cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);

        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        $container.addClass('cg_entry_watermark_bulk_mode').toggleClass('cg_entry_watermark_restore_bulk_mode', mode === 'restore').removeClass('cg_hide cg_entry_watermark_finished').addClass('cg_active');
        cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState();
        cgJsClassAdmin.gallery.entryWatermark.setLoading(false);
        if(mode === 'restore'){
            cgJsClassAdmin.gallery.entryWatermark.prepareRestoreBulk(false);
        }
    };

    cgJsClassAdmin.gallery.entryWatermark.normalizeFile = function(file){
        var defaults = cgJsClassAdmin.gallery.entryWatermark.defaults();
        var settings = $.extend({}, defaults, file.settings || {});
        if(!file.watermarked){
            settings = $.extend({}, defaults);
        }
        file.settings = settings;
        file.enabled = !!file.watermarked;
        file.WpUpload = parseInt(file.WpUpload, 10);
        file.supported = !!file.supported;
        file.type_supported = !!file.type_supported;
        file.gd_supported = !!file.gd_supported;
        file.can_restore = !!file.can_restore;
        file.unsupported_reason = file.unsupported_reason || '';
        return file;
    };

    cgJsClassAdmin.gallery.entryWatermark.open = function($sortableDiv, isAfterAutoSave){
        var $container = $('#cgWatermarkContainer');
        var realId = parseInt($sortableDiv.find('.cg_real_id').val(), 10);

        if(cgJsClassAdmin.gallery.entryWatermark.hasUnsavedEntryChanges($sortableDiv)){
            if(!isAfterAutoSave){
                cgJsClassAdmin.gallery.entryWatermark.saveEntryChangesAndOpenWatermark($sortableDiv);
            }else{
                cgJsClassAdmin.gallery.entryWatermark.showSaveChangesFirst($sortableDiv);
            }
            return;
        }

        cgJsClassAdmin.gallery.vars.$sortableDiv = $sortableDiv;
        $container.removeClass('cg_entry_watermark_bulk_mode cg_entry_watermark_restore_bulk_mode cg_entry_watermark_processing cg_entry_watermark_finished');
        $container.find('#cgEntryWatermarkRealId').val(realId);
        $container.find('.cg_shortcode_conf_title_main').text('Watermark');
        $container.find('#cgEntryWatermarkTitleLabel').text('Entry ID');
        $container.find('#cgEntryWatermarkEntryIdTitle').text(realId);
        $container.find('#cgEntryWatermarkSave').val('Save').prop('disabled', false);
        $container.find('#cgEntryWatermarkFiles').empty();
        $container.find('#cgEntryWatermarkPreviewImage').empty();
        cgJsClassAdmin.gallery.entryWatermark.resetBulkProgress(0);
        cgJsClassAdmin.gallery.entryWatermark.hideNoFilesMessage();
        cgJsClassAdmin.gallery.entryWatermark.applySettingsToControls();
        cgJsClassAdmin.gallery.entryWatermark.state.files = {};
        cgJsClassAdmin.gallery.entryWatermark.state.order = [];
        cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload = 0;
        cgJsClassAdmin.gallery.entryWatermark.state.bulkJob = null;
        cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState();

        cgJsClassAdmin.gallery.functions.showCgBackendBackgroundDrop();
        $container.removeClass('cg_hide').addClass('cg_active');
        cgJsClassAdmin.gallery.entryWatermark.setLoading(true);

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_get_entry_watermark_state',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                realId: realId,
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val()
            }
        }).done(function(response){
            if(!response || !response.success || !response.data){
                cgJsClassAdmin.gallery.entryWatermark.setLoading(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Watermark data could not be loaded');
                return;
            }
            cgJsClassAdmin.gallery.entryWatermark.renderFiles(response.data.files || []);
            cgJsClassAdmin.gallery.entryWatermark.setLoading(false);
        }).fail(function(xhr){
            cgJsClassAdmin.gallery.entryWatermark.setLoading(false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, 'Watermark data could not be loaded');
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.renderFiles = function(files){
        var $files = $('#cgEntryWatermarkFiles');
        var firstSelectable = 0;
        var firstEnabled = 0;
        var selectableCount = 0;

        $files.empty();

        for(var i=0; i<files.length; i++){
            var file = cgJsClassAdmin.gallery.entryWatermark.normalizeFile(files[i]);
            cgJsClassAdmin.gallery.entryWatermark.state.files[file.WpUpload] = file;
            cgJsClassAdmin.gallery.entryWatermark.state.order.push(file.WpUpload);

            var canSelect = (file.supported || file.can_restore) && !file.ecommerce_locked;
            if(canSelect){
                selectableCount++;
                if(!firstSelectable){
                    firstSelectable = file.WpUpload;
                }
                if(file.enabled && !firstEnabled){
                    firstEnabled = file.WpUpload;
                }
            }

            var disabledClass = (!canSelect) ? ' cg_disabled' : '';
            var checkedClass = file.enabled ? 'cg_checked' : 'cg_unchecked';
            var imageClass = (file.type_supported || file.supported || file.can_restore) ? 'cg_file_img' : 'cg_file_' + file.type;
            var $tile = $('<div class="cg_entry_watermark_file_container'+disabledClass+'" data-cg-wp-upload="'+file.WpUpload+'">' +
                '<div class="cg_file '+imageClass+'"></div>' +
                '<span class="cg_post_title">'+cgJsClassAdmin.gallery.entryWatermark.escapeHtml(file.name)+'</span>' +
                '<span class="cg_entry_watermark_file_status"></span>' +
                '<div class="cg_file_checkbox '+checkedClass+'"></div>' +
                '</div>');

            if(file.preview_url && (file.type_supported || file.supported || file.can_restore)){
                $tile.find('.cg_file').css('background-image', 'url('+file.preview_url+')');
            }

            $files.append($tile);
            cgJsClassAdmin.gallery.entryWatermark.updateTile(file.WpUpload);
        }

        if(!selectableCount){
            cgJsClassAdmin.gallery.entryWatermark.showNoFilesMessage();
            $('#cgEntryWatermarkSave').prop('disabled', true);
            cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState();
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.hideNoFilesMessage();
        $('#cgEntryWatermarkSave').prop('disabled', false);
        if(firstEnabled){
            cgJsClassAdmin.gallery.entryWatermark.selectFile(firstEnabled);
        }else if(firstSelectable){
            cgJsClassAdmin.gallery.entryWatermark.selectFile(firstSelectable);
        }
    };

    cgJsClassAdmin.gallery.entryWatermark.updateTile = function(WpUpload){
        var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
        if(!file){
            return;
        }

        var $tile = $('#cgEntryWatermarkFiles .cg_entry_watermark_file_container[data-cg-wp-upload="'+WpUpload+'"]');
        var $checkbox = $tile.find('.cg_file_checkbox');
        var $status = $tile.find('.cg_entry_watermark_file_status');

        $checkbox.removeClass('cg_checked cg_unchecked').addClass(file.enabled ? 'cg_checked' : 'cg_unchecked');

        if(file.ecommerce_locked){
            $status.text('');
        }else if(!file.supported){
            if(file.can_restore && !file.enabled){
                $status.text('Will restore original');
            }else if(file.can_restore){
                $status.text('Restore only');
            }else{
                $status.text(file.unsupported_reason || 'JPG/PNG/GIF only');
            }
        }else if(file.watermarked && !file.enabled){
            $status.text('Will restore original');
        }else if(file.watermarked && file.enabled){
            $status.text('Watermark active');
        }else if(file.enabled){
            $status.text('Will watermark');
        }else{
            $status.text('No watermark');
        }
    };

    cgJsClassAdmin.gallery.entryWatermark.selectFile = function(WpUpload){
        var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
        if(!file || file.ecommerce_locked || (!file.supported && !file.can_restore)){
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.updateActiveSettings();
        if(!file.watermarked){
            file.settings = $.extend({}, cgJsClassAdmin.gallery.entryWatermark.defaults());
        }

        cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload = WpUpload;
        $('#cgEntryWatermarkFiles .cg_entry_watermark_file_container').removeClass('cg_active');
        $('#cgEntryWatermarkFiles .cg_entry_watermark_file_container[data-cg-wp-upload="'+WpUpload+'"]').addClass('cg_active');

        cgJsClassAdmin.gallery.entryWatermark.applySettingsToControls(file.settings);
        cgJsClassAdmin.gallery.entryWatermark.syncSettingsControlsState();

        cgJsClassAdmin.gallery.entryWatermark.renderPreview();
    };

    cgJsClassAdmin.gallery.entryWatermark.toggleFile = function(WpUpload){
        var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
        if(!file || file.ecommerce_locked || (!file.supported && !file.can_restore)){
            return;
        }

        file.enabled = !file.enabled;
        cgJsClassAdmin.gallery.entryWatermark.updateTile(WpUpload);
        cgJsClassAdmin.gallery.entryWatermark.selectFile(WpUpload);
    };

    cgJsClassAdmin.gallery.entryWatermark.updateActiveSettings = function(){
        var WpUpload = cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload;
        var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
        if(!file){
            return;
        }

        file.settings = cgJsClassAdmin.gallery.entryWatermark.getSettingsFromControls();
        cgJsClassAdmin.gallery.entryWatermark.storeSettings(file.settings);
        return file.settings;
    };

    cgJsClassAdmin.gallery.entryWatermark.renderPreview = function(){
        var WpUpload = cgJsClassAdmin.gallery.entryWatermark.state.activeWpUpload;
        var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
        var $preview = $('#cgEntryWatermarkPreviewImage');
        var $previewContainer = $('#cgEntryWatermarkPreviewImageContainer');
        var $loader = $('#cgEntryWatermarkPreviewLoader');
        var state = cgJsClassAdmin.gallery.entryWatermark.state;
        state.previewRequestId++;
        var previewRequestId = state.previewRequestId;

        if(!file || (!file.preview_url && !file.watermarked)){
            $preview.empty();
            $previewContainer.removeClass('cg_preview_loading');
            $loader.addClass('cg_hide');
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.updateActiveSettings();

        var settings = file.settings;
        var fallbackUrl = cgJsClassAdmin.gallery.entryWatermark.addPreviewCacheBuster(file.preview_url);
        var backgroundUrl = cgJsClassAdmin.gallery.entryWatermark.getPreviewSourceUrl(file);
        if(!backgroundUrl){
            backgroundUrl = fallbackUrl;
        }

        if(!file.enabled){
            cgJsClassAdmin.gallery.entryWatermark.showRawPreviewImage($preview, backgroundUrl);
            $loader.addClass('cg_hide');
            $previewContainer.removeClass('cg_preview_loading');
            return;
        }

        var position = settings.WatermarkPosition;
        var pxSize = settings.WatermarkSize;

        $loader.removeClass('cg_hide');
        $previewContainer.addClass('cg_preview_loading');

        if(!cgJsClassAdmin.gallery.functions.renderWatermarkImage){
            cgJsClassAdmin.gallery.entryWatermark.showRawPreviewImage($preview, backgroundUrl);
            $loader.addClass('cg_hide');
            $previewContainer.removeClass('cg_preview_loading');
            return;
        }

        cgJsClassAdmin.gallery.functions.renderWatermarkImage(backgroundUrl, settings.WatermarkTitle, position, pxSize, '#fff', 0.5)
            .then(function(img){
                if(previewRequestId !== state.previewRequestId){
                    return;
                }
                $preview.empty().append(img).removeClass('cg_hide');
                $loader.addClass('cg_hide');
                $previewContainer.removeClass('cg_preview_loading');
            }, function(){
                if(previewRequestId !== state.previewRequestId){
                    return;
                }
                cgJsClassAdmin.gallery.entryWatermark.showRawPreviewImage($preview, fallbackUrl);
                $loader.addClass('cg_hide');
                $previewContainer.removeClass('cg_preview_loading');
            });
    };

    cgJsClassAdmin.gallery.entryWatermark.schedulePreview = function(){
        clearTimeout(cgJsClassAdmin.gallery.entryWatermark.state.previewTimer);
        cgJsClassAdmin.gallery.entryWatermark.state.previewTimer = setTimeout(function(){
            cgJsClassAdmin.gallery.entryWatermark.renderPreview();
        }, 250);
    };

    cgJsClassAdmin.gallery.entryWatermark.save = function(){
        var $container = $('#cgWatermarkContainer');
        var $sortableDiv = cgJsClassAdmin.gallery.vars.$sortableDiv;
        var formPostData = new FormData();
        var hasAnySubmitted = false;

        if(cgJsClassAdmin.gallery.entryWatermark.hasUnsavedEntryChanges($sortableDiv)){
            cgJsClassAdmin.gallery.entryWatermark.showSaveChangesFirst($sortableDiv);
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.updateActiveSettings();

        formPostData.append('action', 'post_cg_save_entry_watermark');
        formPostData.append('cg_nonce', cgJsClassAdmin.gallery.entryWatermark.getNonce());
        formPostData.append('GalleryID', $container.find('#cgEntryWatermarkGalleryID').val());
        formPostData.append('realId', $container.find('#cgEntryWatermarkRealId').val());
        formPostData.append('cgGalleryHash', $container.find('#cgEntryWatermarkGalleryHash').val());

        for(var i=0; i<cgJsClassAdmin.gallery.entryWatermark.state.order.length; i++){
            var WpUpload = cgJsClassAdmin.gallery.entryWatermark.state.order[i];
            var file = cgJsClassAdmin.gallery.entryWatermark.state.files[WpUpload];
            if(!file || file.ecommerce_locked){
                continue;
            }

            if(!file.supported){
                if(file.can_restore && !file.enabled){
                    hasAnySubmitted = true;
                    formPostData.append('files['+WpUpload+'][enabled]', '0');
                }
                continue;
            }

            hasAnySubmitted = true;
            formPostData.append('files['+WpUpload+'][enabled]', file.enabled ? '1' : '0');
            formPostData.append('files['+WpUpload+'][settings][WatermarkTitle]', file.settings.WatermarkTitle);
            formPostData.append('files['+WpUpload+'][settings][WatermarkPosition]', file.settings.WatermarkPosition);
            formPostData.append('files['+WpUpload+'][settings][WatermarkSize]', file.settings.WatermarkSize);
        }

        if(!hasAnySubmitted){
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(cgJsClassAdmin.gallery.entryWatermark.getNoSelectableFilesMessage());
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.setSaving(true);
        if(cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv){
            cgJsClassAdmin.gallery.functions.insertLoaderBeforeSortableDiv();
        }

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            data: formPostData,
            dataType: 'json',
            contentType: false,
            processData: false
        }).done(function(response){
            if(!response || !response.success || !response.data){
                cgJsClassAdmin.gallery.entryWatermark.setSaving(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage('Watermark could not be saved');
                return;
            }

            var message = response.data.message || 'Watermark saved';
            var realId = response.data.realId || $container.find('#cgEntryWatermarkRealId').val();
            cgJsClassAdmin.gallery.reload.entry(realId,false,false,undefined,undefined,undefined,undefined,undefined,undefined,undefined,false,message,true);
        }).fail(function(xhr){
            cgJsClassAdmin.gallery.entryWatermark.setSaving(false);
            if(cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone){
                cgJsClassAdmin.gallery.vars.$cgReloadEntryLoaderClone.remove();
            }
            if(cgJsClassAdmin.gallery.vars.$sortableDiv){
                cgJsClassAdmin.gallery.vars.$sortableDiv.removeClass('cg_hide');
            }
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, 'Watermark could not be saved');
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.getChangedBulkEntryIds = function(){
        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob;
        var entryIds = [];
        if(!job || !job.changedEntryIds){
            return entryIds;
        }

        for(var realId in job.changedEntryIds){
            if(job.changedEntryIds.hasOwnProperty(realId)){
                entryIds.push(job.changedEntryIds[realId]);
            }
        }
        return entryIds;
    };

    cgJsClassAdmin.gallery.entryWatermark.finalizeBulk = function(){
        var $container = $('#cgWatermarkContainer');
        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob;
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(job && job.mode);
        var entryIds = cgJsClassAdmin.gallery.entryWatermark.getChangedBulkEntryIds();
        var skipText = cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job || {});
        var meta = (job ? job.processed+' / '+job.total+' files processed' : 'Processing finished');
        if(skipText){
            meta += ' - '+skipText;
        }

        if(!entryIds.length){
            cgJsClassAdmin.gallery.entryWatermark.completeBulkProgress(labels.finishedStatus, meta);
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.finishedMessage, true);
            return;
        }

        cgJsClassAdmin.gallery.entryWatermark.completeBulkProgress('Refreshing frontend data', meta);
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_finalize_bulk_entry_watermark',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val(),
                entryIds: entryIds
            }
        }).done(function(response){
            if(!response || !response.success){
                cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.couldNotRefresh);
                return;
            }

            if(job){
                $container.addClass('cg_entry_watermark_finished');
            }
            cgJsClassAdmin.gallery.entryWatermark.completeBulkProgress(labels.finishedStatus, meta);
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.savedMessage, true);
            setTimeout(function(){
                cgJsClassAdmin.gallery.load.changeViewByControl(jQuery, null, null, false, true);
            }, 700);
        }).fail(function(xhr){
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, labels.couldNotRefresh);
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.stopBulkAfterError = function(xhr, fallbackMessage){
        var $container = $('#cgWatermarkContainer');
        var entryIds = cgJsClassAdmin.gallery.entryWatermark.getChangedBulkEntryIds();

        if(!entryIds.length){
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, fallbackMessage);
            return;
        }

        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob || {};
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(job.mode);
        cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress(job.processed || 0, job.total || 0, 'Refreshing processed entries', labels.stoppedMeta);
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: 'post_cg_finalize_bulk_entry_watermark',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val(),
                entryIds: entryIds
            }
        }).always(function(){
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, fallbackMessage);
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.processBulkNextChunk = function(){
        var $container = $('#cgWatermarkContainer');
        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob;
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(job && job.mode);
        if(!job || !job.jobs || job.processed >= job.total){
            cgJsClassAdmin.gallery.entryWatermark.finalizeBulk();
            return;
        }

        var chunk = job.jobs.slice(job.processed, job.processed + 1);
        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: job.mode === 'restore' ? 'post_cg_process_bulk_entry_unwatermark' : 'post_cg_process_bulk_entry_watermark',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val(),
                settings: job.settings,
                jobs: chunk
            }
        }).done(function(response){
            if(!response || !response.success || !response.data){
                cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.couldNotSave);
                return;
            }

            var processed = parseInt(response.data.processed, 10) || chunk.length;
            job.processed += processed;
            if(job.processed > job.total){
                job.processed = job.total;
            }
            if(response.data.changedEntryIds && response.data.changedEntryIds.length){
                for(var i=0; i<response.data.changedEntryIds.length; i++){
                    var realId = parseInt(response.data.changedEntryIds[i], 10);
                    if(realId){
                        job.changedEntryIds[realId] = realId;
                    }
                }
            }

            cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress(job.processed, job.total, labels.processingStatus, cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job));
            cgJsClassAdmin.gallery.entryWatermark.processBulkNextChunk();
        }).fail(function(xhr){
            cgJsClassAdmin.gallery.entryWatermark.stopBulkAfterError(xhr, labels.couldNotSave);
        });
    };

    cgJsClassAdmin.gallery.entryWatermark.saveBulk = function(){
        var $container = $('#cgWatermarkContainer');
        var job = cgJsClassAdmin.gallery.entryWatermark.state.bulkJob;
        var labels = cgJsClassAdmin.gallery.entryWatermark.getBulkLabels(job && job.mode);
        if(!job || !job.entryIds || !job.entryIds.length){
            cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.noEntries);
            return;
        }

        if(job.mode === 'restore'){
            if(job.preparing){
                return;
            }
            if(!job.prepared){
                cgJsClassAdmin.gallery.entryWatermark.prepareRestoreBulk(true);
                return;
            }
            if(!job.total || !job.jobs || !job.jobs.length){
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.noJobsMeta);
                return;
            }

            job.processed = 0;
            job.changedEntryIds = {};
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(true);
            cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress(0, job.total, labels.processingStatus, cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job));
            cgJsClassAdmin.gallery.entryWatermark.processBulkNextChunk();
            return;
        }

        var settings = {};
        if(job.mode !== 'restore'){
            settings = cgJsClassAdmin.gallery.entryWatermark.getSettingsFromControls();
            cgJsClassAdmin.gallery.entryWatermark.storeSettings(settings);
        }
        job.settings = settings;
        job.jobs = [];
        job.processed = 0;
        job.total = 0;
        job.skippedSame = 0;
        job.skippedUnsupported = 0;
        job.skippedLocked = 0;
        job.skippedMissing = 0;
        job.skippedNotWatermarked = 0;
        job.changedEntryIds = {};

        cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(true);
        $container.find('#cgEntryWatermarkTitleLabel').text(labels.prepareTitle);
        $container.find('#cgEntryWatermarkEntryIdTitle').text('');
        cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress(0, 0, labels.prepareStatus);

        $.ajax({
            url: 'admin-ajax.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: job.mode === 'restore' ? 'post_cg_prepare_bulk_entry_unwatermark' : 'post_cg_prepare_bulk_entry_watermark',
                cg_nonce: cgJsClassAdmin.gallery.entryWatermark.getNonce(),
                GalleryID: $container.find('#cgEntryWatermarkGalleryID').val(),
                cgGalleryHash: $container.find('#cgEntryWatermarkGalleryHash').val(),
                entryIds: job.entryIds,
                settings: settings
            }
        }).done(function(response){
            if(!response || !response.success || !response.data){
                cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.couldNotPrepare);
                return;
            }

            job.jobs = response.data.jobs || [];
            job.total = parseInt(response.data.total, 10) || job.jobs.length;
            job.skippedSame = parseInt(response.data.skipped_same, 10) || 0;
            job.skippedUnsupported = parseInt(response.data.skipped_unsupported, 10) || 0;
            job.skippedLocked = parseInt(response.data.skipped_locked, 10) || 0;
            job.skippedMissing = parseInt(response.data.skipped_missing, 10) || 0;
            job.skippedNotWatermarked = parseInt(response.data.skipped_not_watermarked, 10) || 0;
            job.settings = response.data.settings || settings;

            if(!job.total){
                $container.find('#cgEntryWatermarkTitleLabel').text(labels.noJobsTitle);
                $container.find('#cgEntryWatermarkEntryIdTitle').text('');
                cgJsClassAdmin.gallery.entryWatermark.completeBulkProgress(labels.finishedStatus, cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job) || labels.noJobsMeta);
                cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
                cgJsClassAdmin.gallery.functions.setAndAppearBackendGalleryDynamicMessage(labels.finishedMessage, true);
                return;
            }

            $container.find('#cgEntryWatermarkTitleLabel').text(job.total + (job.total === 1 ? labels.countSingular : labels.countPlural));
            $container.find('#cgEntryWatermarkEntryIdTitle').text('');
            cgJsClassAdmin.gallery.entryWatermark.updateBulkProgress(0, job.total, labels.processingStatus, cgJsClassAdmin.gallery.entryWatermark.getBulkSkipText(job));
            cgJsClassAdmin.gallery.entryWatermark.processBulkNextChunk();
        }).fail(function(xhr){
            cgJsClassAdmin.gallery.entryWatermark.setBulkProcessing(false);
            cgJsClassAdmin.gallery.entryWatermark.handleAjaxError(xhr, labels.couldNotPrepare);
        });
    };

    $(document).on('click', '.cg_entry_watermark', function(){
        cgJsClassAdmin.gallery.entryWatermark.open($(this).closest('.cgSortableDiv'));
    });

    $(document).on('click', '#cgEntryWatermarkFiles .cg_entry_watermark_file_container', function(){
        cgJsClassAdmin.gallery.entryWatermark.toggleFile(parseInt($(this).attr('data-cg-wp-upload'), 10));
    });

    $(document).on('click', '#cgEntryWatermarkFiles .cg_file_checkbox', function(e){
        e.stopPropagation();
        var $tile = $(this).closest('.cg_entry_watermark_file_container');
        cgJsClassAdmin.gallery.entryWatermark.toggleFile(parseInt($tile.attr('data-cg-wp-upload'), 10));
    });

    $(document).on('input change', '#cgEntryWatermarkInputTitle,#cgEntryWatermarkSelectPosition,#cgEntryWatermarkSelectSize', function(){
        var settings = cgJsClassAdmin.gallery.entryWatermark.updateActiveSettings();
        if(settings && !cgJsClassAdmin.gallery.entryWatermark.isProVersion() && $(this).is('#cgEntryWatermarkSelectPosition,#cgEntryWatermarkSelectSize')){
            cgJsClassAdmin.gallery.entryWatermark.applySettingsToControls(settings);
        }
        cgJsClassAdmin.gallery.entryWatermark.schedulePreview();
    });

    $(document).on('submit', '#cgEntryWatermarkForm', function(e){
        e.preventDefault();
        if($('#cgWatermarkContainer').hasClass('cg_entry_watermark_bulk_mode')){
            cgJsClassAdmin.gallery.entryWatermark.saveBulk();
        }else{
            cgJsClassAdmin.gallery.entryWatermark.save();
        }
    });

})(jQuery);
