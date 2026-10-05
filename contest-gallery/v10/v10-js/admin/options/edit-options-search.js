/* Search descriptions. Only the translations page may also index its translation fields. */
(function ($) {
    'use strict';

    var search = cgJsClassAdmin.options.search = {};
    var entries = [], rankedResults = [], results = [];
    var active = -1, current = -1, selected = null;
    var lastQuery = null, navigating = false, timer, navigateTimer;
    var scope = 'options';
    var $root, $input, $popup, $list, $status, $content, $highlight;
    var translationFields = 'input[type="text"][name^="translations["], textarea[name^="translations["]';
    // Preserve non-Latin text in older browsers without Unicode property escapes, too.
    var nonSearchCharacters = /[^a-z0-9_\u00c0-\u1fff\u2c00-\ud7ff\uf900-\ufdcf\ufdf0-\uffef]+/g;
    try { nonSearchCharacters = new RegExp('[^\\p{L}\\p{N}\\p{M}_]+', 'gu'); } catch (error) {}
    var variantSelector = '.cg_short_code_multiple_pics_configuration_container, .cg_short_code_single_pic_configuration_container, .cg_short_code_galleries_configuration_container';
    var synonyms = [
        ['photo', 'photos', 'image', 'images', 'picture', 'pictures'],
        ['vote', 'votes', 'voting', 'rating', 'ratings'],
        ['email', 'mail', 'emails', 'mails'],
        ['registration', 'register', 'signup'],
        ['login', 'signin']
    ];

    function normalize(text) {
        if (text.normalize) { text = text.normalize('NFC'); }
        return text.toLowerCase().replace(/e-mail/g, 'email').replace(/sign[ -]+in/g, 'signin')
            .replace(/sign[ -]+up/g, 'signup').replace(nonSearchCharacters, ' ').trim();
    }

    function description($node, titleOnly) {
        var $copy = $node.clone();
        $copy.find('input, select, textarea, script, style, iframe, .wp-editor-wrap, .cg_options_search_notice, .cg_options_search_ignore').remove();
        if (titleOnly) {
            $copy.find('.cg_view_option_title_note, .cg_view_options_rows_container_title_note, .cgPreselectSortMessage, .cgAllowSortDependsOnMessage, .cg_hide, .cg_hide_override, .cg-info-icon, .cg-info-container').remove();
        }
        $copy.find('br').replaceWith(' ');
        return $copy.text().replace(/\s+/g, ' ').trim();
    }

    function variant($target) {
        var $container = $target.closest(variantSelector);
        var match = ($container.attr('class') || '').match(/\b(cg_short_code_(?:multiple_pics|single_pic|galleries)_configuration_(cg_gallery(?:_user|_no_voting|_winner|_ecommerce)?))_container\b/);
        return match ? {container: $container, button: '.' + match[1], name: match[2].replace('cg_gallery', /_galleries_/.test(match[1]) ? 'cg_galleries' : 'cg_gallery')} : null;
    }

    // This row is available only when its existing navigation mode is selected.
    // Unknown hidden rows remain excluded, including retired fullscreen options.
    function dependency($target) {
        if ($target.closest('.ForwardToWpPageEntryInNewTabContainer').length) {
            var $control = $target.closest('.cg_view').find('.ForwardToWpPageEntry:not(.cg_shortcode_checkbox_clone)').first().closest('.cg_view_option');
            if ($control.length) {
                return {target: $control, message: 'Requires “Forward to entry landing page”. Select that option to use this setting.'};
            }
        }
        return null;
    }

    function reachable($target, context, dependent) {
        var reachable = true;
        $target.parentsUntil($content).addBack().each(function () {
            if (context && this === context.container[0]) { return; }
            if (dependent && $(this).hasClass('ForwardToWpPageEntryInNewTabContainer')) { return; }
            if (this.hidden || window.getComputedStyle(this).display === 'none') {
                reachable = false;
                return false;
            }
        });
        return reachable;
    }

    function addEntry($target, title, detail, path, context, $tab, dependent) {
        if (!title || !reachable($target, context, dependent)) { return; }
        var $translation = scope === 'translations' && $target.closest('.cg_view_container_translations').length ? $target.find(translationFields).first() : $();
        var original = '';
        if ($translation.length) {
            var name = $translation.attr('name');
            original = name.replace(/^translations\[(?:(?:general|ecommerce|paidActions)\]\[)?/, '').slice(0, -1);
        }
        entries.push({target: $target, title: title, titleText: normalize(title), detail: normalize(detail),
            translationField: $translation, originalText: normalize(original), translationValue: '', translationText: '',
            path: path, pathText: normalize(path), variant: context, tab: $tab, dependency: dependent,
            pro: !!($target.closest('.cg-pro-false, .cg-pro-false-container').length || $target.find('input.cg-pro-false, select.cg-pro-false, textarea.cg-pro-false').length),
            order: entries.length});
    }

    function buildIndex() {
        entries = [];
        $content.children('.cg_view_header').each(function () {
            var $header = $(this);
            var $tab = $('#cg_main_options_tab .cg_view_select').filter(function () {
                return $(this).attr('cg-data-view') === '#' + $header.attr('id');
            }).first();
            if (!$tab.length || !$tab.is(':visible')) { return; }
            var section = description($tab.find('.cg_view_select_link'), true);
            addEntry($header, section, description($header, false), section, null, $tab, null);
            $header.nextUntil('.cg_view_header').find('.cg_view_option').each(function () {
                var $target = $(this);
                var $title = $target.children('.cg_view_option_title');
                var context = variant($target);
                var $group = $target.closest('.cg_view_options_rows_container').children('.cg_view_options_rows_container_title').first();
                if (scope === 'ecommerce') {
                    $group = $target.closest('.cg_view_options_rows_container').children('.cg_view_options_rows_container_title').filter(function () {
                        return !!(this.compareDocumentPosition($target[0]) & 4) && !$(this).find('.cg_note_label').length;
                    }).last();
                }
                var group = description($group, true);
                var path = section + (context ? ' → ' + context.name : '') + (group ? ' → ' + group : '');
                if (!$title.length) {
                    // Some grouped settings, such as real watermarks, use ordinary labels.
                    $target.find('label').each(function () {
                        addEntry($target, description($(this), true), '', path, context, $tab, dependency($target));
                    });
                    return;
                }
                var detail = description($title, false) + ' ' + description($target.find('.cg_view_option_radio_multiple_title'), true);
                addEntry($target, description($title, true), detail, path, context, $tab, dependency($target));
            });
        });
    }

    function alternatives(word) {
        for (var i = 0; i < synonyms.length; i++) {
            if (synonyms[i].indexOf(word) !== -1) { return synonyms[i]; }
        }
        return [word];
    }

    function rank(entry, query, words) {
        var score = 0;
        var titles = [entry.titleText, entry.originalText, entry.translationText];
        var titleWords = titles.join(' ');
        for (var i = 0; i < words.length; i++) {
            var word = words[i], best = 0;
            if (titleWords.indexOf(word) !== -1) { best = 30; }
            else if (entry.pathText.indexOf(word) !== -1) { best = 15; }
            else if (entry.detail.indexOf(word) !== -1) { best = 10; }
            if (!best) {
                var aliases = alternatives(word);
                for (var j = 0; j < aliases.length; j++) {
                    if ((titleWords + ' ' + entry.detail + ' ' + entry.pathText).indexOf(aliases[j]) !== -1) { best = 5; break; }
                }
            }
            if (!best) { return 0; }
            score += best;
        }
        if (titles.indexOf(query) !== -1) { score += 200; }
        else if (titles.some(function (title) { return title.indexOf(query) !== -1; })) { score += 100; }
        return score;
    }

    function close() {
        // Finish a pending query without reopening the popup after Escape, Tab or blur.
        if (timer && $root) { refreshResults(); }
        clearTimeout(timer);
        timer = null;
        if (!$root) { return; }
        $popup.prop('hidden', true);
        $input.attr('aria-expanded', 'false').removeAttr('aria-activedescendant');
        active = -1;
    }

    function choose(index) {
        active = index;
        $list.children().attr('aria-selected', 'false');
        var $choice = $list.children().eq(active).attr('aria-selected', 'true');
        $input.attr('aria-activedescendant', $choice.attr('id'));
        var top = $choice[0].offsetTop, bottom = top + $choice.outerHeight();
        if (top < $list.scrollTop()) { $list.scrollTop(top); }
        else if (bottom > $list.scrollTop() + $list.innerHeight()) { $list.scrollTop(bottom - $list.innerHeight()); }
    }

    function cancelNavigation() {
        clearTimeout(navigateTimer);
        navigateTimer = null;
        if (navigating) { selected = null; current = -1; }
        navigating = false;
    }

    function updateNavigation() {
        $root.find('#cgOptionsSearchNavigation').prop('hidden', !results.length);
        $root.find('#cgOptionsSearchCount').text(current < 0 ? results.length + (results.length === 1 ? ' result' : ' results') : (current + 1) + ' / ' + results.length);
        $root[0].style.setProperty('--cg-options-search-actions-width', Math.max(44, $root.find('.cg_options_search_actions').outerWidth() + 12) + 'px');
        var message = '';
        if (lastQuery && lastQuery.length >= 2) {
            if (current >= 0) {
                message = 'Result ' + (current + 1) + ' of ' + results.length + '. ' + selected.title + '. ' + selected.path;
                if ($highlight && selected.dependency && selected.target[0] !== $highlight[0]) { message += '. ' + selected.dependency.message; }
            } else {
                message = results.length ? results.length + ' matching ' + (scope === 'translations' ? 'translations.' : 'options.') : $root.find('#cgOptionsSearchEmpty').text();
            }
        }
        $status.text(message);
    }

    function refreshResults() {
        clearTimeout(timer);
        timer = null;
        var query = normalize($input.val());
        var translationsChanged = false;
        entries.forEach(function (entry) {
            if (!entry.translationField.length) { return; }
            var value = entry.translationField.val();
            if (value !== entry.translationValue) {
                entry.translationValue = value;
                entry.translationText = normalize(value);
                translationsChanged = true;
            }
        });
        $root.find('#cgOptionsSearchClear').prop('hidden', !$input.val().length);
        if (query !== lastQuery || translationsChanged) {
            cancelNavigation();
            lastQuery = query;
            var words = query.split(' ');
            rankedResults = [];
            if (query.length >= 2) {
                entries.forEach(function (entry) {
                    var score = rank(entry, query, words);
                    if (score) {
                        rankedResults.push({entry: entry, score: score, current: entry.variant && $(entry.variant.button).hasClass('cg_active') ? 1 : 0});
                    }
                });
                rankedResults.sort(function (a, b) { return b.score - a.score || b.current - a.current || a.entry.order - b.entry.order; });
            }
        }
        // Keep the query's ranked order while rechecking which options are reachable.
        results = rankedResults.filter(function (result) { return reachable(result.entry.target, result.entry.variant, result.entry.dependency); });
        current = -1;
        results.forEach(function (result, index) {
            if (result.entry === selected) { current = index; }
        });
        if (current < 0) { selected = null; clearHighlight(); }
        updateNavigation();
    }

    function render() {
        refreshResults();
        if (lastQuery.length < 2) { $list.empty(); close(); return; }
        $list.empty();
        var fragment = document.createDocumentFragment();
        results.forEach(function (result, index) {
            var entry = result.entry;
            var $item = $('<div>', {id: 'cgOptionsSearchResult' + index, role: 'option', 'aria-selected': 'false', 'data-index': index, 'class': 'cg_options_search_result'});
            $('<span>', {'class': 'cg_options_search_result_title'}).text(entry.title).appendTo($item);
            if (entry.translationValue.trim()) {
                $('<span>', {'class': 'cg_options_search_result_translation'}).text('Translation: ' + entry.translationValue).appendTo($item);
            }
            if (entry.pro) { $('<span>', {'class': 'cg_options_search_pro', text: 'PRO'}).appendTo($item); }
            $('<span>', {'class': 'cg_options_search_result_path'}).text(entry.path).appendTo($item);
            fragment.appendChild($item[0]);
        });
        $list.append(fragment).scrollTop(0);
        $root.find('#cgOptionsSearchEmpty').prop('hidden', !!results.length);
        $popup.prop('hidden', false);
        $input.attr('aria-expanded', 'true').removeAttr('aria-activedescendant');
        active = -1;
        positionPopup();
    }

    function positionPopup() {
        if (!$popup || $popup.prop('hidden')) { return; }
        var overflow = $popup[0].getBoundingClientRect().bottom - window.innerHeight + 12;
        var spaceAbove = $root[0].getBoundingClientRect().top - ($('#wpadminbar').outerHeight() || 0) - 12;
        if (overflow > 0 && spaceAbove > 0) {
            $(window).scrollTop($(window).scrollTop() + Math.min(overflow, spaceAbove));
        }
    }

    function clearHighlight() {
        if ($highlight) {
            $highlight.removeClass('cg_options_search_highlight');
            if ($highlight.data('cg-search-tabindex')) { $highlight.removeAttr('tabindex').removeData('cg-search-tabindex'); }
            $highlight = null;
        }
        $('.cg_options_search_notice').remove();
    }

    function openResult(index, keepFocus) {
        if (!results[index]) { return; }
        var entry = results[index].entry;
        close();
        clearHighlight();
        cancelNavigation();
        selected = entry;
        current = index;
        navigating = true;
        updateNavigation();
        if (entry.variant) { $(entry.variant.button).first().trigger('click'); }
        entry.tab.trigger('click');
        // Shortcode navigation animates for 300 ms. Lazy editors can also change the offset.
        navigateTimer = setTimeout(function () {
            if (!document.documentElement.contains(entry.target[0])) { cancelNavigation(); updateNavigation(); return; }
            var $target = entry.target;
            var $notice = $();
            if (!$target.is(':visible') && entry.dependency) {
                $target = entry.dependency.target;
                $notice = $('<p>', {'class': 'cg_options_search_notice', role: 'status'}).text(entry.dependency.message).insertBefore($target.closest('.cg_view_options_row'));
            }
            if (!$target.is(':visible')) { cancelNavigation(); updateNavigation(); return; }
            navigating = false;
            $highlight = $target.addClass('cg_options_search_highlight');
            updateNavigation();
            if (!$target.is('[tabindex]')) { $target.attr('tabindex', '-1').data('cg-search-tabindex', true); }
            if (!keepFocus) { $target[0].focus({preventScroll: true}); }
            function alignTarget() {
                var offset = ($('#wpadminbar').outerHeight() || 0) + ($('#cg_main_options_tab').outerHeight() || 0);
                if (entry.variant) {
                    var $buttons = $(entry.variant.button).first().parent().parent();
                    offset += $buttons.outerHeight() || 0;
                }
                var $anchor = $notice.length ? $notice : $target;
                $(window).scrollTop(Math.max(0, $anchor.offset().top - offset - 24));
            }
            alignTarget();
            // The existing scroll handler fixes/unfixes the shortcode bar on this scroll.
            // Re-measure after that layout change so the target and prerequisite stay visible.
            navigateTimer = setTimeout(function () {
                alignTarget();
            }, 100);
        }, 350);
    }

    search.destroy = function () {
        close();
        clearTimeout(timer);
        cancelNavigation();
        clearHighlight();
        $(document).off('.cgOptionsSearch');
        $(window).off('.cgOptionsSearch');
        $('#cg_main_options_tab').removeClass('cg_options_search_enabled');
        if ($root) { $root.off('.cgOptionsSearch'); }
        if ($content) { $content.off('.cgOptionsSearch'); }
        entries = []; rankedResults = []; results = []; active = -1; current = -1; selected = null; lastQuery = null;
        $root = null;
        scope = 'options';
        $input = $popup = $list = $status = $content = null;
    };

    search.init = function () {
        search.destroy();
        $root = $('#cgOptionsSearch');
        if (!$root.length) { $root = null; return; }
        scope = $root.attr('data-cg-search-scope') || 'options';
        $input = $root.find('#cgOptionsSearchInput');
        $popup = $root.find('#cgOptionsSearchPopup');
        $list = $root.find('#cgOptionsSearchResults');
        $status = $root.find('#cgOptionsSearchStatus');
        $content = $('#cg_main_options_content');
        $('#cg_main_options_tab').addClass('cg_options_search_enabled');
        function positionShortcodes() {
            var mainOptions = document.getElementById('cg_main_options');
            if (!mainOptions || !$content || !$content.length || !document.documentElement.contains($content[0])) { return; }
            var adminHeight = $('#wpadminbar').outerHeight() || 0;
            var top = adminHeight + ($('#cg_main_options_tab').outerHeight() || 0);
            mainOptions.style.setProperty('--cg-options-search-admin-top', adminHeight + 'px');
            $content[0].style.setProperty('--cg-options-search-sticky-top', top + 'px');
            updateNavigation();
            positionPopup();
        }
        positionShortcodes();
        $(window).on('resize.cgOptionsSearch', positionShortcodes);
        buildIndex();
        if (scope === 'translations') {
            $content.on('input.cgOptionsSearch change.cgOptionsSearch', translationFields, function () {
                if (!$(this).closest('.cg_view_container_translations').length) { return; }
                // Update unsaved text and its count without interrupting the edit.
                cancelNavigation();
                refreshResults();
                close();
            });
        }
        $root.on('input.cgOptionsSearch', '#cgOptionsSearchInput', function () {
            clearTimeout(timer);
            if (normalize(this.value) !== lastQuery) { cancelNavigation(); }
            if (!this.value.trim()) { render(); return; }
            timer = setTimeout(render, 150);
        }).on('focus.cgOptionsSearch', '#cgOptionsSearchInput', function () {
            render();
        }).on('keydown.cgOptionsSearch', '#cgOptionsSearchInput', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault(); event.stopPropagation();
                if (timer) { render(); }
                if (!$popup.prop('hidden')) { openResult(active < 0 ? 0 : active); }
            } else if (event.key === 'Escape') {
                event.preventDefault(); event.stopPropagation(); close();
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (timer || $popup.prop('hidden')) { render(); }
                if (results.length) { choose(active < 0 ? (event.key === 'ArrowDown' ? 0 : results.length - 1) : (active + (event.key === 'ArrowDown' ? 1 : results.length - 1)) % results.length); }
            } else if (event.key === 'Tab') { close(); }
        }).on('click.cgOptionsSearch', '#cgOptionsSearchClear', function () {
            $input.val('').trigger('focus'); render();
        }).on('click.cgOptionsSearch', '.cg_options_search_step', function (event) {
            event.preventDefault(); event.stopPropagation();
            refreshResults();
            if (!results.length) { close(); return; }
            var step = this.id === 'cgOptionsSearchNext' ? 1 : -1;
            var index = current < 0 ? (step > 0 ? 0 : results.length - 1) : (current + step + results.length) % results.length;
            this.focus({preventScroll: true});
            openResult(index, true);
        }).on('mousedown.cgOptionsSearch', '.cg_options_search_result', function (event) {
            event.preventDefault();
        }).on('click.cgOptionsSearch', '.cg_options_search_result', function () {
            if (timer || normalize($input.val()) !== lastQuery) { render(); return; }
            openResult(parseInt($(this).attr('data-index'), 10));
        });
        $(document).on('click.cgOptionsSearch', function (event) {
            if (!$(event.target).closest('#cgOptionsSearch').length) { close(); }
        });
    };
}(jQuery));
