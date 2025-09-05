cgJsClassAdmin.createUpload.dragAndDrop = {}
cgJsClassAdmin.createUpload.dragAndDrop = {
    drag: function(ev) {
        if (!ev.target.classList.contains('cg_el')) return;
        ev.dataTransfer.setData("text", ev.target.id);
        ev.dataTransfer.effectAllowed = "move";
        ev.target.classList.add('cg_dragging');
        cgJsClassAdmin.gallery.vars.dragEl = ev.currentTarget;
        var $dragged = jQuery(ev.currentTarget);
        if($dragged.hasClass('cg_el')) {
            //console.log('$dragged clicked');
            //console.log($dragged);
            $dragged.click();
        }
    },
    dragEnd: function(ev) {
        ev.target.classList.remove('cg_dragging');
        this.removeHovered(ev);
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);

    },
    drop: function(ev) {
        debugger
        ev.preventDefault();
        var target = ev.currentTarget; // drop target
        cgJsClassAdmin.createUpload.dragAndDrop.hoverOff(ev);

        var draggedId = ev.dataTransfer.getData("text");
        var dragged = document.getElementById(draggedId);

        // Ensure drag started from .cg_upl_add
        if (!dragged || !dragged.classList.contains('cg_el')) return;

        // Target must be either .cg_el or .cg_upl_add
        if (!target.matches('.cg_el, .cg_upl_add')) return;
        if (dragged === target) return;

        var targetClone = target.cloneNode(true);
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
        if (target.classList.contains('cg_el')) {
            cgJsClassAdmin.createUpload.dragAndDrop.makeInteractive(target); // tops stay draggable/droppable
        } else {
            cgJsClassAdmin.createUpload.dragAndDrop.makeBottomInteractive(target); // bottoms droppable only
        }

        this.removeHovered(ev);
        cgJsClassAdmin.createUpload.functions.showDelRow();
        cgJsClassAdmin.createUpload.functions.addRightFieldOrderAndAddRowsAndColumns(jQuery);
    },
    removeHovered: function(ev) {
        jQuery(ev.currentTarget).closest('#cgRightSide').find('.cg_hovered').removeClass('cg_hovered');
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