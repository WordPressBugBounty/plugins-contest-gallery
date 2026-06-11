cgJsClassAdmin.createUpload.dragAndDrop = {}
cgJsClassAdmin.createUpload.dragAndDrop = {
    temporaryDropRowsTimeout: null,
    drag: function(ev) {
        var dragged = this.getDraggedElementFromEvent(ev);

        if (!dragged || !dragged.id) return;

        ev.dataTransfer.setData("text", dragged.id);
        ev.dataTransfer.effectAllowed = "move";
        dragged.classList.add('cg_dragging');
        cgJsClassAdmin.gallery.vars.dragEl = dragged;

        var $dragged = jQuery(dragged);
        if($dragged.hasClass('cg_el')) {
            //console.log('$dragged clicked');
            //console.log($dragged);
            $dragged.click();
        }
        this.queueTemporaryDropRows($dragged.closest('#cgRightSide'), $dragged);
    },
    dragEnd: function(ev) {
        var dragged = this.getDraggedElementFromEvent(ev);
        var $rightSide = dragged ? jQuery(dragged).closest('#cgRightSide') : jQuery(ev.currentTarget).closest('#cgRightSide');

        if(dragged){
            dragged.classList.remove('cg_dragging');
        }

        this.clearTemporaryDropRowsTimeout();
        this.removeHovered(ev);
        this.removeTemporaryDropRows($rightSide);
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);

    },
    drop: function(ev) {
        ev.preventDefault();
        var target = ev.currentTarget; // drop target
        var $rightSide = jQuery(target).closest('#cgRightSide');
        var targetIsField = target.classList.contains('cg_el');
        var targetIsTemporaryDropTarget = target.classList.contains('cg_drag_drop_temporary_target');
        this.clearTemporaryDropRowsTimeout();
        cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(ev);

        var draggedId = ev.dataTransfer.getData("text");
        var dragged = document.getElementById(draggedId);
        var $sourceRow;

        // Ensure drag started from .cg_upl_add
        if (!dragged || !dragged.classList.contains('cg_el')) {
            this.removeTemporaryDropRows($rightSide);
            return;
        }

        // Target must be either .cg_el or .cg_upl_add
        if (!target.matches('.cg_el, .cg_upl_add')) {
            this.removeTemporaryDropRows($rightSide);
            return;
        }
        if (dragged === target) {
            this.removeTemporaryDropRows($rightSide);
            return;
        }

        $sourceRow = jQuery(dragged).closest('.cg_row');

        cgJsClassAdmin.createUpload.dragAndDrop.makeTemporaryDropRowPermanent(target);

        var targetClone = targetIsTemporaryDropTarget ? jQuery(cgJsClassAdmin.createUpload.functions.getAddElCol()).get(0) : target.cloneNode(true);
        var draggedClone = dragged.cloneNode(true);

        jQuery(draggedClone).insertAfter(dragged);
        jQuery(target).replaceWith(dragged);
        jQuery(draggedClone).replaceWith(targetClone);

        /*
        // --- Clone-based safe swap ---
        // 1) Snapshot original position of dragged
        var parentA = dragged.parentNode;
        var nextA = dragged.nextSibling;

        // 2) Placeholder for the target
        var placeholder = target.cloneNode(true);
        if (placeholder.id) placeholder.removeAttribute('id');
        target.parentNode.replaceChild(placeholder, target);

        // 3) Move ORIGINAL target to dragged’s old position
        if (nextA) {
            parentA.insertBefore(target, nextA);
        } else {
            parentA.appendChild(target);
        }

        // 4) Move dragged into target’s old position
        placeholder.parentNode.replaceChild(dragged, placeholder);

        */

        // 5) Restore handlers
        cgJsClassAdmin.createUpload.dragAndDrop.makeInteractive(dragged);
        if (targetIsField) {
            cgJsClassAdmin.createUpload.dragAndDrop.makeInteractive(targetClone); // tops stay draggable/droppable
        } else {
            cgJsClassAdmin.createUpload.dragAndDrop.makeBottomInteractive(targetClone); // bottoms droppable only
        }

        this.removeHovered(ev);
        this.removeTemporaryDropRows($rightSide);
        this.removeSourceRowIfEmpty($sourceRow);
        this.normalizeAddRows($rightSide);
        cgJsClassAdmin.createUpload.functions.showDelRow();
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);
    },
    removeSourceRowIfEmpty: function($sourceRow) {
        if(!$sourceRow || !$sourceRow.length || !$sourceRow.parent().length){
            return;
        }

        if($sourceRow.hasClass('cg_image')){
            return;
        }

        if($sourceRow.find('.cg_row_col.cg_el').length){
            return;
        }

        $sourceRow.remove();
        cgJsClassAdmin.createUpload.functions.removeAddRows(jQuery);
    },
    createAddRow: function() {
        return jQuery('<div class="cg_row cg_add_row" title="Add row"></div>');
    },
    normalizeAddRows: function($rightSide) {
        var self = this;

        if(!$rightSide || !$rightSide.length){
            $rightSide = jQuery('#cgRightSide');
        }

        $rightSide.children('.cg_row.cg_add_row').each(function() {
            var $addRow = jQuery(this);

            if($addRow.next('.cg_row.cg_image').length){
                $addRow.remove();
            }
        });

        $rightSide.children('.cg_row:not(.cg_add_row)').each(function() {
            var $row = jQuery(this);

            if(!$row.hasClass('cg_image') && !$row.prev('.cg_row.cg_add_row').length){
                self.createAddRow().insertBefore($row);
            }

            if(!$row.next('.cg_row.cg_add_row').length){
                self.createAddRow().insertAfter($row);
            }
        });

        this.removeDuplicateAddRows($rightSide);
    },
    removeDuplicateAddRows: function($rightSide) {
        var hasAddRow = false;

        $rightSide.children('.cg_row').each(function() {
            var $row = jQuery(this);

            if($row.hasClass('cg_add_row')){
                if(hasAddRow){
                    $row.remove();
                    return;
                }
                hasAddRow = true;
            }else{
                hasAddRow = false;
            }
        });
    },
    getDraggedElementFromEvent: function(ev) {
        if(ev.currentTarget && ev.currentTarget.classList && ev.currentTarget.classList.contains('cg_el')){
            return ev.currentTarget;
        }

        var $dragged = jQuery(ev.target).closest('.cg_el');

        if($dragged.length){
            return $dragged.get(0);
        }

        return null;
    },
    clearTemporaryDropRowsTimeout: function() {
        if(this.temporaryDropRowsTimeout){
            clearTimeout(this.temporaryDropRowsTimeout);
            this.temporaryDropRowsTimeout = null;
        }
    },
    queueTemporaryDropRows: function($rightSide, $dragged) {
        var self = this;

        this.clearTemporaryDropRowsTimeout();

        if(!$rightSide || !$rightSide.length){
            return;
        }

        this.temporaryDropRowsTimeout = setTimeout(function() {
            var dragged = $dragged && $dragged.length ? $dragged.get(0) : null;
            var scrollContainer = dragged ? self.getScrollContainer(dragged) : null;
            var draggedTopBefore = dragged ? dragged.getBoundingClientRect().top : null;

            self.temporaryDropRowsTimeout = null;
            self.insertTemporaryDropRows($rightSide, dragged);
            self.restoreDraggedVisualPosition(dragged, scrollContainer, draggedTopBefore);
        }, 0);
    },
    restoreDraggedVisualPosition: function(dragged, scrollContainer, draggedTopBefore) {
        var draggedTopAfter;
        var scrollTop;
        var delta;

        if(!dragged || !scrollContainer || draggedTopBefore === null){
            return;
        }

        draggedTopAfter = dragged.getBoundingClientRect().top;
        delta = draggedTopAfter - draggedTopBefore;

        if(Math.abs(delta) < 1){
            return;
        }

        scrollTop = this.getScrollTop(scrollContainer);
        this.setScrollTop(scrollContainer, scrollTop + delta);
    },
    getScrollContainer: function(el) {
        var current = el && el.parentElement ? el.parentElement : null;
        var style;

        while(current){
            style = window.getComputedStyle(current);

            if(/(auto|scroll)/.test(style.overflowY) && current.scrollHeight > current.clientHeight){
                return current;
            }

            current = current.parentElement;
        }

        return window;
    },
    getScrollTop: function(scrollContainer) {
        if(scrollContainer === window){
            return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
        }

        return scrollContainer.scrollTop;
    },
    setScrollTop: function(scrollContainer, scrollTop) {
        if(scrollContainer === window){
            window.scrollTo(window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0, scrollTop);
        }else{
            scrollContainer.scrollTop = scrollTop;
        }
    },
    createTemporaryDropRow: function() {
        var $row = jQuery('<div class="cg_row cg_empty cg_drag_drop_temporary_row"><div class="cg_add_col" title="Add column"></div></div>');
        var $addCol = jQuery('<div class="cg_row_col cg_upl_add cg_drag_drop_temporary_target cg_100" title="Drop field"><div class="cg_drag_drop_temporary_target_inner"></div></div>');
        $row.append($addCol);
        cgJsClassAdmin.createUpload.dragAndDrop.makeBottomInteractive($addCol.get(0));
        return $row;
    },
    isSingleFullwidthDragSource: function($sourceRow, dragged) {
        var $columns;

        if(!$sourceRow || !$sourceRow.length || !dragged){
            return false;
        }

        $columns = $sourceRow.children('.cg_row_col');

        if($columns.length !== 1){
            return false;
        }

        if($sourceRow.children('.cg_row_col.cg_upl_add').length){
            return false;
        }

        return $columns.get(0) === dragged && $columns.hasClass('cg_el');
    },
    isSourceAdjacentTemporaryDropPosition: function($previousRow, $nextRow, $sourceRow) {
        if(!$sourceRow || !$sourceRow.length){
            return false;
        }

        return ($previousRow.length && $previousRow.get(0) === $sourceRow.get(0)) || ($nextRow.length && $nextRow.get(0) === $sourceRow.get(0));
    },
    insertTemporaryDropRows: function($rightSide, dragged) {
        var self = this;
        var $sourceRow = dragged ? jQuery(dragged).closest('.cg_row') : jQuery();
        var skipSourceAdjacentDropRows = this.isSingleFullwidthDragSource($sourceRow, dragged);
        var isRegistryRightSide = $rightSide && $rightSide.closest('#cg_registry_form_container_parent').length ? true : false;

        if(!$rightSide || !$rightSide.length){
            return;
        }

        this.removeTemporaryDropRows($rightSide);

        $rightSide.children('.cg_row.cg_add_row').each(function () {
            var $addRow = jQuery(this);
            var $previousRow = $addRow.prevAll('.cg_row:not(.cg_add_row):not(.cg_drag_drop_temporary_row)').first();
            var $nextRow = $addRow.nextAll('.cg_row:not(.cg_add_row):not(.cg_drag_drop_temporary_row)').first();
            var $temporaryRow;

            if(skipSourceAdjacentDropRows && self.isSourceAdjacentTemporaryDropPosition($previousRow, $nextRow, $sourceRow)){
                return;
            }

            if(!$previousRow.length){
                if(!isRegistryRightSide || !$nextRow.length || $nextRow.hasClass('cg_image') || $nextRow.hasClass('cg_empty')){
                    return;
                }

                if(!$nextRow.find('.cg_row_col.cg_el').length){
                    return;
                }
            }else if(!$nextRow.length){
                if($previousRow.hasClass('cg_image') || $previousRow.hasClass('cg_empty')){
                    return;
                }

                if(!$previousRow.find('.cg_row_col.cg_el').length){
                    return;
                }
            }else{
                if($nextRow.hasClass('cg_image') || $nextRow.hasClass('cg_empty')){
                    return;
                }

                if($previousRow.hasClass('cg_empty')){
                    return;
                }

                if(!$previousRow.find('.cg_row_col.cg_el').length || !$nextRow.find('.cg_row_col.cg_el').length){
                    return;
                }
            }

            $temporaryRow = self.createTemporaryDropRow();
            $temporaryRow.insertBefore($addRow);
        });
    },
    makeTemporaryDropRowPermanent: function(target) {
        jQuery(target).closest('.cg_drag_drop_temporary_row').removeClass('cg_drag_drop_temporary_row');
    },
    removeTemporaryDropRows: function($rightSide) {
        if(!$rightSide || !$rightSide.length){
            $rightSide = jQuery('#cgRightSide');
        }
        $rightSide.children('.cg_row.cg_drag_drop_temporary_row').remove();
    },
    removeHovered: function(ev) {
        var $rightSide = jQuery(ev.currentTarget).closest('#cgRightSide');
        if(!$rightSide.length){
            $rightSide = jQuery('#cgRightSide');
        }
        $rightSide.find('.cg_hovered').removeClass('cg_hovered');
    },
    removeHoveredCreateUploadContainer: function(ev) {
        jQuery(ev.currentTarget).find('.cg_hovered').removeClass('cg_hovered');
    },
    makeInteractive: function(el) {
        el.setAttribute('draggable', 'true');
        el.setAttribute('ondragstart', 'cgJsClassAdmin.createUpload.dragAndDrop.drag(event)');
        el.setAttribute('ondragend', 'cgJsClassAdmin.createUpload.dragAndDrop.dragEnd(event)');
        el.setAttribute('ondrop', 'cgJsClassAdmin.createUpload.dragAndDrop.drop(event)');
        el.setAttribute('ondragover', 'cgJsClassAdmin.createUpload.dragAndDrop.allowDrop(event)');
        el.setAttribute('ondragenter', 'cgJsClassAdmin.createUpload.dragAndDrop.hoverOn(event)');
        el.setAttribute('ondragleave', 'cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(event)');
    },
    makeBottomInteractive: function(el) {
        el.removeAttribute('draggable');
        el.setAttribute('ondrop', 'cgJsClassAdmin.createUpload.dragAndDrop.drop(event)');
        el.setAttribute('ondragover', 'cgJsClassAdmin.createUpload.dragAndDrop.allowDrop(event)');
        el.setAttribute('ondragenter', 'cgJsClassAdmin.createUpload.dragAndDrop.hoverOn(event)');
        el.setAttribute('ondragleave', 'cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(event)');
    },
    allowDrop: function(ev) {
        ev.preventDefault();
    },
    hoverOn: function(ev) {
        this.removeHovered(ev);
        if(!ev.currentTarget.classList.contains('cg_dragging')){
            ev.currentTarget.classList.add('cg_hovered');
        }
        cgJsClassAdmin.gallery.vars.hoverOnEl = ev.currentTarget;
    },
    hoverOff: function(ev) {
        if(ev.currentTarget.classList.contains('cg_dragging')){
            ev.currentTarget.classList.remove('cg_hovered');
        }
    },
    ondragoverCreateUploadContainer: function(ev) {
        if(!jQuery(ev.target).closest('.cg_hovered').length){
            this.removeHoveredCreateUploadContainer(ev);
        }
    }
};
