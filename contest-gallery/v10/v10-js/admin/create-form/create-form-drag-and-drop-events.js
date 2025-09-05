jQuery(document).ready(function ($) {
    // ========= Smooth Auto-Scroll while dragging (top/bottom edges) =========
    (function () {
        // Tune these:
        var EDGE_THRESHOLD_PX = 80;     // how close to edge before scrolling starts
        var MAX_SPEED_PX_PER_S = 1100;  // maximum scroll speed
        var EASING = 0.28;              // 0..1; higher = snappier acceleration

        var rafId = null;
        var isDragging = false;
        var lastPointerY = 0;
        var scrollCtx = null; // {el, isWindow, top, bottom, height, speedY, lastTs}

        document.addEventListener('dragstart', function (e) {
            isDragging = true;
            // prime context immediately
            lastPointerY = (e.clientY != null ? e.clientY : 0);
            scrollCtx = computeScrollContext(e.target);
            if (!rafId) rafId = requestAnimationFrame(loop);
        });

        // 'dragover' fires continuously while dragging across the page
        document.addEventListener('dragover', function (e) {
            lastPointerY = e.clientY;
            // update target scroll container based on the current element under pointer
            scrollCtx = computeScrollContext(e.target);
            // Don't call preventDefault here; your dropzones handle that already.
        });

        // stop autoscroll on drop & dragend
        function stop() {
            isDragging = false;
            if (rafId) cancelAnimationFrame(rafId);
            rafId = null;
            scrollCtx = null;
        }
        document.addEventListener('drop', stop);
        document.addEventListener('dragend', stop);

        function computeScrollContext(fromEl) {
            var el = getScrollableAncestor(fromEl);
            var isWindow = (el === window);
            var rect = isWindow
                ? { top: 0, bottom: window.innerHeight, height: window.innerHeight }
                : el.getBoundingClientRect();
            return {
                el: el,
                isWindow: isWindow,
                top: rect.top,
                bottom: rect.bottom,
                height: rect.height,
                speedY: 0,
                lastTs: performance.now()
            };
        }

        function getScrollableAncestor(node) {
            var el = (node && node.nodeType === 1) ? node : (node && node.parentElement);
            while (el) {
                var style = getComputedStyle(el);
                var canScrollY = /(auto|scroll)/.test(style.overflowY);
                if (canScrollY && el.scrollHeight > el.clientHeight) return el;
                el = el.parentElement;
            }
            // fallback to window/page scroll
            return window;
        }

        function loop(ts) {
            if (!isDragging || !scrollCtx) { rafId = null; return; }

            var top = scrollCtx.top;
            var bottom = scrollCtx.bottom;
            var y = lastPointerY;

            // Distance into the edge zones
            var distTop = Math.max(0, EDGE_THRESHOLD_PX - (y - top));
            var distBot = Math.max(0, EDGE_THRESHOLD_PX - (bottom - y));

            var targetSpeed = 0;
            if (distTop > 0) {
                targetSpeed = -(distTop / EDGE_THRESHOLD_PX) * MAX_SPEED_PX_PER_S;
            } else if (distBot > 0) {
                targetSpeed =  (distBot / EDGE_THRESHOLD_PX) * MAX_SPEED_PX_PER_S;
            }

            // Smoothly approach target speed
            scrollCtx.speedY += (targetSpeed - scrollCtx.speedY) * EASING;

            // Time-based step (handles variable frame rates)
            var now = (typeof ts === 'number') ? ts : performance.now();
            var dt = Math.max(0, (now - scrollCtx.lastTs) / 1000);
            scrollCtx.lastTs = now;

            var delta = scrollCtx.speedY * dt;

            if (scrollCtx.isWindow) {
                window.scrollTo(window.scrollX, window.scrollY + delta);
            } else {
                scrollCtx.el.scrollTop += delta;
            }

            rafId = requestAnimationFrame(loop);
        }
    })();
    // ========= /Smooth Auto-Scroll =========

});