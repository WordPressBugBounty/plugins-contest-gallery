jQuery(document).ready(function ($) {
    var $paidActionsCard = $('#cgPaidActionsCard');
    if ($paidActionsCard.length && cgJsClassAdmin.gallery && cgJsClassAdmin.gallery.currencyInputFunctions) {
        var paidActionsPriceDivider = $paidActionsCard.attr('data-price-divider') === ',' ? ',' : '.';
        cgJsClassAdmin.gallery.currencyInputFunctions.priceDivider = paidActionsPriceDivider;
        cgJsClassAdmin.gallery.currencyInputFunctions.thousandsDivider = paidActionsPriceDivider === ',' ? '.' : ',';
    }

    function canonicalPriceValue($card, $input) {
        var priceDivider = $card.attr('data-price-divider') === ',' ? ',' : '.';
        var thousandsDivider = priceDivider === ',' ? '.' : ',';
        var value = String($input.val() || '').trim().split(thousandsDivider).join('');
        return priceDivider === ',' ? value.replace(',', '.') : value;
    }

    function getState($card) {
        var state = $card.data('cgPaidActionsState');
        if (!state) {
            state = {
                saveTimer: null,
                saving: false,
                saveAgain: false,
                saveWaiters: [],
                saveStateTimer: null,
                activationNoticeTimer: null,
                pendingToggleChanges: {}
            };
            $card.data('cgPaidActionsState', state);
        }
        return state;
    }

    function values($card) {
        var data = {
            action: 'post_cg_paid_actions_save_settings',
            cg_nonce: CG1LBackendNonce.nonce,
            galleryId: $card.data('gallery-id'),
            galleryHash: $card.data('gallery-hash'),
            shareCredits: $card.find('.cg_paid_actions_share_input').prop('checked') ? 1 : 0
        };
        $card.find('.cg_paid_action_tile').each(function () {
            var $tile = $(this);
            var type = $tile.data('action-type');
            data[type + 'Enabled'] = $tile.find('.cg_paid_action_enabled').prop('checked') ? 1 : 0;
            data[type + 'Price'] = canonicalPriceValue($card, $tile.find('.cg_paid_action_price'));
        });
        return data;
    }

    function clearState($card) {
        var state = getState($card);
        window.clearTimeout(state.saveStateTimer);
        $card.find('.cg_paid_actions_save_state').stop(true, true).hide().removeClass('cg_paid_actions_save_error').text('').css('opacity', '');
    }

    function showSavedState($card) {
        var state = getState($card);
        var $state = $card.find('.cg_paid_actions_save_state');
        window.clearTimeout(state.saveStateTimer);
        $state.stop(true, true).removeClass('cg_paid_actions_save_error').text('Changes saved').css('opacity', 1).show();
        state.saveStateTimer = window.setTimeout(function () {
            $state.fadeOut(650, function () {
                $state.text('').css('opacity', '');
            });
        }, 1100);
    }

    function showErrorState($card, message) {
        var state = getState($card);
        var $state = $card.find('.cg_paid_actions_save_state');
        window.clearTimeout(state.saveStateTimer);
        $state.stop(true, true).addClass('cg_paid_actions_save_error').text(message).css('opacity', 1).show();
    }

    function showSandboxLinkDialog($card, responseData, actionType, isNewLink) {
        var $modal = $card.find('#cgPaidActionsSandboxLinkContainer');
        var $linkField = $modal.find('#cgPaidActionsSandboxLinkInput');
        var url = responseData && responseData.url ? responseData.url : '';
        if (!$modal.length || !url) return false;
        $modal.attr('data-action-type', actionType || 'upload');
        $linkField.val(url);
        $modal.find('.cg_paid_actions_sandbox_link_copy').prop('disabled', false);
        $modal.find('.cg_paid_actions_sandbox_link_open').attr('href', url).attr('aria-disabled', 'false');
        $modal.find('.cg_paid_actions_sandbox_link_status').removeClass('cg_paid_actions_sandbox_link_status_error').text(isNewLink ? 'New test link ready. Copy it into a new private or incognito window.' : 'Link ready. Copy it into a private or incognito window.');
        if (!isNewLink) {
            $('body').addClass('cg_no_scroll');
            if (cgJsClassAdmin.gallery.functions.showModal) {
                cgJsClassAdmin.gallery.functions.showModal('#cgPaidActionsSandboxLinkContainer');
            } else {
                $modal.removeClass('cg_hide').addClass('cg_active');
                $('#cgBackendBackgroundDrop').removeClass('cg_hide').addClass('cg_active');
            }
        }
        if ($linkField.is('textarea')) {
            $linkField.css('height', 'auto');
            $linkField.css('height', (Math.ceil($linkField.get(0).scrollHeight) + 2) + 'px').scrollTop(0);
        }
        $modal.find('.cg_paid_actions_sandbox_link_copy').trigger('focus');
        return true;
    }

    function createSandboxLink($card, $button, actionType, isNewLink) {
        var state = getState($card);
        var $modal = $card.find('#cgPaidActionsSandboxLinkContainer');
        var $status = $modal.find('.cg_paid_actions_sandbox_link_status');
        var $buttonText = $button.find('.cg_paid_actions_sandbox_link_create_text');
        var loadingClass = isNewLink ? 'cg_paid_actions_sandbox_link_create_loading' : 'cg_paid_actions_sandbox_checkout_loading';

        window.clearTimeout(state.saveTimer);
        state.saveTimer = null;
        $button.prop('disabled', true).attr('aria-busy', 'true').addClass(loadingClass);
        if (isNewLink) {
            $buttonText.text('Creating new test link...');
            $status.removeClass('cg_paid_actions_sandbox_link_status_error').text('Creating a separate test link for another account...');
        }

        save($card, function () {
            $.post(ajaxurl, {
                action: 'post_cg_paid_actions_create_sandbox_link',
                cg_nonce: CG1LBackendNonce.nonce,
                galleryId: $card.data('gallery-id'),
                galleryHash: $card.data('gallery-hash'),
                actionType: actionType
            }).done(function (response) {
                if (response && response.success && response.data.url) {
                    showSandboxLinkDialog($card, response.data, actionType, isNewLink);
                } else if (isNewLink) {
                    $status.addClass('cg_paid_actions_sandbox_link_status_error').text('A new test link could not be created. The current link is still available.');
                } else {
                    showErrorState($card, response && response.data && response.data.message ? response.data.message : 'Sandbox test link could not be created');
                }
            }).fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Sandbox test link could not be created';
                if (isNewLink) {
                    $status.addClass('cg_paid_actions_sandbox_link_status_error').text(message + ' The current link is still available.');
                } else {
                    showErrorState($card, message);
                }
            }).always(function () {
                $button.prop('disabled', false).removeAttr('aria-busy').removeClass(loadingClass);
                if (isNewLink) $buttonText.text('Create new test link');
            });
        }, function () {
            $button.prop('disabled', false).removeAttr('aria-busy').removeClass(loadingClass);
            if (isNewLink) {
                $buttonText.text('Create new test link');
                $status.addClass('cg_paid_actions_sandbox_link_status_error').text('Save the earning options before creating a new test link. The current link is still available.');
            }
        });
    }

    function copySandboxLink($input) {
        var value = $input.val() || '';
        if (!value) return $.Deferred().reject().promise();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(value);
        }
        $input.trigger('focus').trigger('select');
        try {
            return document.execCommand('copy') ? $.Deferred().resolve().promise() : $.Deferred().reject().promise();
        } catch (error) {
            return $.Deferred().reject().promise();
        }
    }

    function joinActionLabels(labels) {
        if (labels.length < 2) return labels.join('');
        if (labels.length === 2) return labels.join(' and ');
        return labels.slice(0, -1).join(', ') + ' and ' + labels[labels.length - 1];
    }

    function isActionReady(readiness, type) {
        return !!(readiness.actionReady && readiness.actionReady[type]);
    }

    function syncModeBanner($card, settings, readiness) {
        var labels = { upload: 'uploads', vote: 'votes', comment: 'comments' };
        var activeLabels = [];
        var liveLabels = [];
        var previewLabels = [];
        var blockedLabels = [];
        ['upload', 'vote', 'comment'].forEach(function (type) {
            var mode = parseInt(settings[type + 'Mode'], 10) || 0;
            if (mode) activeLabels.push(labels[type]);
            if (mode === 2 && isActionReady(readiness, type)) liveLabels.push(labels[type]);
            else if (mode === 2) blockedLabels.push(labels[type]);
            else if (mode === 1) previewLabels.push(labels[type]);
        });
        var $banner = $card.find('.cg_paid_actions_mode_banner');
        if (!activeLabels.length) {
            $banner.prop('hidden', true);
            return;
        }
        var tone = liveLabels.length ? 'live' : (blockedLabels.length ? 'warning' : 'preview');
        var eyebrow = tone === 'live' ? 'LIVE EARNING MODE' : (tone === 'warning' ? 'SALES NEED ATTENTION' : 'PRIVATE TEST MODE');
        var title = tone === 'live' ? 'Paid actions are live' : (tone === 'warning' ? 'Paid actions are configured' : 'Paid actions preview is active');
        var copy = '';
        if (tone === 'live') {
            copy = 'This gallery currently sells ' + joinActionLabels(liveLabels) + ' as credits.';
            if (previewLabels.length) copy += ' Private preview remains available for ' + joinActionLabels(previewLabels) + '.';
        } else if (tone === 'warning') {
            copy = 'Complete the required Ecommerce or PRO setup for ' + joinActionLabels(blockedLabels) + ' credits.';
        } else {
            copy = 'The secure test checkout is available for ' + joinActionLabels(activeLabels) + '.';
        }
        $banner.attr('class', 'cg_paid_actions_mode_banner cg_paid_actions_mode_banner_' + tone).prop('hidden', false);
        $banner.find('small').text(eyebrow);
        $banner.find('strong').text(title);
        $banner.find('em').text(copy);
    }

    function showActivationNotice($card, toggleChanges, settings, readiness) {
        var state = getState($card);
        var labels = { upload: 'uploads', vote: 'votes', comment: 'comments' };
        var activated = [];
        var live = [];
        var preview = [];
        ['upload', 'vote', 'comment'].forEach(function (type) {
            if (!toggleChanges.hasOwnProperty(type) || !toggleChanges[type]) return;
            var mode = parseInt(settings[type + 'Mode'], 10) || 0;
            if (!mode) return;
            activated.push(labels[type]);
            if (mode === 2 && isActionReady(readiness, type)) live.push(labels[type]);
            else preview.push(labels[type]);
        });
        if (!activated.length) return;
        var hasLive = live.length > 0;
        var headline = hasLive ? 'Gallery sales activated' : 'Private payment preview activated';
        var copy = hasLive ? 'This gallery can now sell credits for ' + joinActionLabels(live) + '.' : '';
        if (preview.length) copy += (copy ? ' ' : '') + 'The secure test checkout is ready for ' + joinActionLabels(preview) + '.';
        var $notice = $card.find('.cg_paid_actions_activation_notice');
        window.clearTimeout(state.activationNoticeTimer);
        $notice.stop(true, true).attr('class', 'cg_paid_actions_activation_notice cg_paid_actions_activation_notice_' + (hasLive ? 'live' : 'preview'))
            .html('<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><span><strong>' + headline + '</strong><em>' + copy + '</em></span>')
            .css('opacity', 1).prop('hidden', false).show();
        state.activationNoticeTimer = window.setTimeout(function () {
            $notice.fadeOut(650, function () {
                $notice.prop('hidden', true).css('opacity', '');
            });
        }, 3600);
    }

    function setExpanded($card, expanded) {
        $card.toggleClass('cg_paid_actions_card_is_open', expanded);
        $card.find('.cg_paid_actions_expand_button').attr('aria-expanded', expanded ? 'true' : 'false');
        $card.find('.cg_paid_actions_expand_button_text').text(expanded ? 'Hide earning options' : 'Show earning options');
        $card.find('.cg_paid_actions_card_body').prop('hidden', !expanded);
    }

    function syncActionControls($tile, enabled) {
        var type = $tile.data('action-type');
        var labels = { upload: 'uploads', vote: 'votes', comment: 'comments' };
        var $button = $tile.find('.cg_paid_action_preview');
        var $priceLabel = $tile.find('.cg_paid_action_price_label');
        var $priceInput = $tile.find('.cg_paid_action_price');
        $button.prop('disabled', !enabled).attr('aria-disabled', enabled ? 'false' : 'true');
        $priceLabel.toggleClass('cg_paid_action_price_label_disabled', !enabled).attr('aria-disabled', enabled ? 'false' : 'true');
        $priceInput.prop('disabled', !enabled).attr('aria-disabled', enabled ? 'false' : 'true');
        if (enabled) {
            $button.removeAttr('title');
            $priceInput.removeAttr('title');
        } else {
            $button.attr('title', 'Activate pay for ' + (labels[type] || type) + ' to try it now.');
            $priceInput.attr('title', 'Activate pay for ' + (labels[type] || type) + ' to set a price.');
        }
    }

	function syncShareStatus($card, settings, sharingStatus) {
		var enabled = parseInt(settings.shareCredits, 10) === 1;
		var eligibleGalleryCount = Math.max(0, parseInt(sharingStatus.eligibleGalleryCount, 10) || 0);
		var tone;
		var icon;
		var title;
		var copy;
		var $status = $card.find('.cg_paid_actions_share_status');
		if (!$status.length) return;
		if (!enabled) {
			tone = 'off';
			icon = 'dashicons-info-outline';
			title = 'New credits bought here stay local';
			copy = 'Enable this option to make future purchases from this gallery available automatically wherever the matching paid action is active.';
		} else if (!eligibleGalleryCount) {
			tone = 'ready';
			icon = 'dashicons-yes-alt';
			title = 'Global credits are enabled';
			copy = 'No other gallery currently has a matching paid action. Credits will work there automatically when one is activated.';
		} else {
			tone = 'ready';
			icon = 'dashicons-yes-alt';
			title = 'Global credits work in ' + eligibleGalleryCount + ' other paid-action ' + (eligibleGalleryCount === 1 ? 'gallery' : 'galleries');
			copy = 'No additional sharing setting is required in those galleries.';
		}
		$status.attr('class', 'cg_paid_actions_share_status cg_paid_actions_share_status_' + tone).attr('data-eligible-gallery-count', eligibleGalleryCount);
		$status.children('.dashicons').attr('class', 'dashicons ' + icon);
		$status.find('strong').first().text(title);
		$status.find('small').first().text(copy);
	}

    function applyResponse($card, data) {
        var settings = data.settings || {};
        var readiness = data.readiness || {};
        ['upload', 'vote', 'comment'].forEach(function (type) {
            var mode = parseInt(settings[type + 'Mode'], 10) || 0;
            var status = mode === 2 ? (isActionReady(readiness, type) ? 'Live' : 'Unavailable') : (mode === 1 ? 'Preview' : 'Off');
            var $tile = $card.find('.cg_paid_action_tile_' + type);
            var $badge = $tile.find('.cg_paid_action_status');
            $badge.attr('class', 'cg_paid_action_status cg_paid_action_status_' + status.toLowerCase()).text(status);
            syncActionControls($tile, $tile.find('.cg_paid_action_enabled').prop('checked'));
            var warning = data.priceWarnings && data.priceWarnings[type] ? data.priceWarnings[type] : '';
            var $warning = $tile.find('.cg_paid_action_price_warning');
            if (!$warning.length && warning) {
                $warning = $('<div class="cg_paid_action_price_warning"></div>').appendTo($tile);
            }
            $warning.text(warning).toggle(!!warning);
        });
        var $sandboxButton = $card.find('.cg_paid_actions_sandbox_checkout');
        if ($sandboxButton.length) {
            var $sandboxRequirement = $card.find('.cg_paid_actions_sandbox_requirement');
            var firstActiveType = '';
            ['upload', 'vote', 'comment'].some(function (type) {
                if ((parseInt(settings[type + 'Mode'], 10) || 0) > 0) {
                    firstActiveType = type;
                    return true;
                }
                return false;
            });
            var sandboxReady = !!(firstActiveType && readiness.sandboxPaymentReady);
            $sandboxButton.attr('data-action-type', firstActiveType).prop('disabled', !sandboxReady).attr('aria-disabled', sandboxReady ? 'false' : 'true');
            $sandboxButton.attr('title', !firstActiveType ? 'Activate at least one paid action first.' : (readiness.sandboxPaymentReady ? 'Create a visitor link for a real PayPal or Stripe sandbox checkout.' : 'Enable sandbox testing and add sandbox credentials in Ecommerce options first.'));
            $sandboxRequirement.prop('hidden', !!firstActiveType);
        }
        syncModeBanner($card, settings, readiness);
		syncShareStatus($card, settings, data.sharingStatus || {});
    }

    function save($card, done, failed) {
        var state = getState($card);
        if (done || failed) {
            state.saveWaiters.push({ done: done, failed: failed });
        }
        if (state.saving) {
            state.saveAgain = true;
            return;
        }
        state.saving = true;
        var didSave = false;
        var toggleChangesForRequest = $.extend({}, state.pendingToggleChanges);
        state.pendingToggleChanges = {};
        clearState($card);
        $.post(ajaxurl, values($card)).done(function (response) {
            if (!response || !response.success) {
                showErrorState($card, response && response.data && response.data.message ? response.data.message : 'Could not save');
                return;
            }
            applyResponse($card, response.data);
            showActivationNotice($card, toggleChangesForRequest, response.data.settings || {}, response.data.readiness || {});
            showSavedState($card);
            didSave = true;
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Could not save';
            showErrorState($card, message);
        }).always(function () {
            state.saving = false;
            if (state.saveAgain) {
                state.saveAgain = false;
                save($card);
            } else if (state.saveWaiters.length) {
                var waiters = state.saveWaiters.slice(0);
                state.saveWaiters = [];
                waiters.forEach(function (waiter) {
                    if (didSave && waiter.done) waiter.done();
                    if (!didSave && waiter.failed) waiter.failed();
                });
            }
        });
    }

    $(document).off('click.cgPaidActionsToggle', '#cgPaidActionsCard .cg_paid_actions_card_header').on('click.cgPaidActionsToggle', '#cgPaidActionsCard .cg_paid_actions_card_header', function (event) {
        if ($(event.target).closest('.cg_paid_actions_ecommerce_link').length) {
            return;
        }
        var $card = $(this).closest('#cgPaidActionsCard');
        setExpanded($card, !$card.hasClass('cg_paid_actions_card_is_open'));
    });

    $(document).off('change.cgPaidActions input.cgPaidActions', '#cgPaidActionsCard .cg_paid_action_enabled,#cgPaidActionsCard .cg_paid_action_price,#cgPaidActionsCard .cg_paid_actions_share_input').on('change.cgPaidActions input.cgPaidActions', '#cgPaidActionsCard .cg_paid_action_enabled,#cgPaidActionsCard .cg_paid_action_price,#cgPaidActionsCard .cg_paid_actions_share_input', function () {
        var $card = $(this).closest('#cgPaidActionsCard');
        var state = getState($card);
        var isPriceInput = $(this).hasClass('cg_paid_action_price');
        if ($(this).hasClass('cg_paid_action_enabled')) {
            var $tile = $(this).closest('.cg_paid_action_tile');
            var enabled = $(this).prop('checked');
            syncActionControls($tile, enabled);
            state.pendingToggleChanges[$tile.data('action-type')] = enabled;
        }
        window.clearTimeout(state.saveTimer);
        state.saveTimer = window.setTimeout(function () { save($card); }, isPriceInput ? 500 : 50);
    });

    $(document).off('click.cgPaidActionsPreview', '#cgPaidActionsCard .cg_paid_action_preview').on('click.cgPaidActionsPreview', '#cgPaidActionsCard .cg_paid_action_preview', function () {
        var $card = $(this).closest('#cgPaidActionsCard');
        var $tile = $(this).closest('.cg_paid_action_tile');
        if ($(this).prop('disabled') || !$tile.find('.cg_paid_action_enabled').prop('checked')) {
            return;
        }
        var state = getState($card);
        window.clearTimeout(state.saveTimer);
        state.saveTimer = null;
        var actionType = $tile.data('action-type');
        var previewWindow = window.open('', '_blank');
        save($card, function () {
            $.post(ajaxurl, {
                action: 'post_cg_paid_actions_create_preview',
                cg_nonce: CG1LBackendNonce.nonce,
                galleryId: $card.data('gallery-id'),
                galleryHash: $card.data('gallery-hash'),
                actionType: actionType
            }).done(function (response) {
                if (response && response.success && response.data.url) {
                    previewWindow.location = response.data.url;
                } else if (previewWindow) {
                    previewWindow.close();
                }
            }).fail(function (xhr) {
                if (previewWindow) {
                    previewWindow.close();
                }
                var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : 'Preview could not be opened';
                showErrorState($card, message);
            });
        }, function () {
            if (previewWindow) {
                previewWindow.close();
            }
        });
    });

    $(document).off('click.cgPaidActionsSandbox', '#cgPaidActionsCard .cg_paid_actions_sandbox_checkout').on('click.cgPaidActionsSandbox', '#cgPaidActionsCard .cg_paid_actions_sandbox_checkout', function () {
        var $button = $(this);
        var $card = $button.closest('#cgPaidActionsCard');
        if ($button.prop('disabled')) return;
        var actionType = $button.attr('data-action-type') || $card.find('.cg_paid_action_enabled:checked').first().closest('.cg_paid_action_tile').data('action-type');
        if (!actionType) return;
        createSandboxLink($card, $button, actionType, false);
    });

    $(document).off('click.cgPaidActionsSandboxCreate', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_create').on('click.cgPaidActionsSandboxCreate', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_create', function () {
        var $button = $(this);
        var $modal = $button.closest('#cgPaidActionsSandboxLinkContainer');
        var $card = $modal.closest('#cgPaidActionsCard');
        var actionType = $modal.attr('data-action-type') || $card.find('.cg_paid_actions_sandbox_checkout').attr('data-action-type') || 'upload';
        if ($button.prop('disabled')) return;
        createSandboxLink($card, $button, actionType, true);
    });

    $(document).off('click.cgPaidActionsSandboxCopy', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_copy').on('click.cgPaidActionsSandboxCopy', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_copy', function () {
        var $button = $(this);
        var $modal = $button.closest('#cgPaidActionsSandboxLinkContainer');
        var $status = $modal.find('.cg_paid_actions_sandbox_link_status');
        if ($button.prop('disabled')) return;
        $.when(copySandboxLink($modal.find('#cgPaidActionsSandboxLinkInput'))).done(function () {
            $status.removeClass('cg_paid_actions_sandbox_link_status_error').text('Test link copied. Open a private or incognito window and paste it there.');
        }).fail(function () {
            $status.addClass('cg_paid_actions_sandbox_link_status_error').text('Copying was blocked by the browser. Select and copy the link manually.');
            $modal.find('#cgPaidActionsSandboxLinkInput').trigger('focus').trigger('select');
        });
    });

    $(document).off('click.cgPaidActionsSandboxOpen', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_open').on('click.cgPaidActionsSandboxOpen', '#cgPaidActionsSandboxLinkContainer .cg_paid_actions_sandbox_link_open', function (event) {
        if ($(this).attr('aria-disabled') === 'true' || !$(this).attr('href') || $(this).attr('href') === '#') {
            event.preventDefault();
        }
    });
});
