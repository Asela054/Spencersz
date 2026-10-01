/* ==========================================================================
   SIDEBAR SUBMENU FLYOUT (HOVER)
   --------------------------------------------------------------------------
   Load this AFTER jQuery and Bootstrap's JS.

   Works with either markup shape:
   - Wrapped:    <div class="sidenav-item"><a data-toggle="collapse">...</a>
                   <div class="collapse" id="...">...</div></div>
   - Unwrapped:  <a data-toggle="collapse" data-target="#foo">...</a>
                 <div class="collapse" id="foo">...</div>
   In both cases the target collapse is found via the anchor's data-target,
   so the script doesn't care which shape a given project uses.

   Behaviour:
   - Sidebar EXPANDED: does nothing — grouped links behave like a normal
     Bootstrap accordion (click to expand inline).
   - Sidebar COLLAPSED (icon-only rail) AND viewport >= 992px: hovering a
     TOP-LEVEL grouped icon opens its submenu as a small floating panel to
     the right of the icon/row. Moving the mouse into the panel keeps it
     open; moving away closes it after a short delay (so crossing the gap
     between icon and panel doesn't close it prematurely).
   - A toggle that's nested INSIDE another submenu (e.g. "Service Info"
     inside "Vehicle Management", or "Service Order Inquiry" inside
     "Service Info") is left alone — it just expands/collapses inline
     within its parent flyout via Bootstrap's normal behaviour, rather
     than trying to cascade another flyout sideways.
   - Below 992px the sidebar always shows full labels, so this script
     doesn't engage.

   Only one flyout is ever open at a time, tracked directly in `openPair`
   rather than re-derived from the DOM — simpler and avoids ambiguity
   across the two markup shapes.

   Note: this does NOT hijack clicks, so it never fights with Bootstrap's
   own data-toggle="collapse" handler.
   ========================================================================== */
(function ($) {
    'use strict';

    var DESKTOP_MIN_WIDTH = 992;
    var OPEN_DELAY = 80;    // ms — small delay avoids flicker when just passing over icons
    var CLOSE_DELAY = 250;  // ms — enough time to move the mouse into the flyout
    var GAP = 12;           // px between the rail and the flyout

    var openTimer = null;
    var closeTimer = null;
    var openPair = null; // { $trigger, $collapse } for the currently-open flyout, or null

    function isDesktop() {
        return window.innerWidth >= DESKTOP_MIN_WIDTH;
    }

    // Different projects use different toggle conventions for the
    // icon-only rail — check the ones we know about.
    function isSidebarCollapsed() {
        return $('.sidenav').hasClass('sidenav-collapsed')
            || $('body').hasClass('sidenav-toggled')
            || $('body').hasClass('sidenav-collapsed');
    }

    function active() {
        return isDesktop() && isSidebarCollapsed();
    }

    // A toggle counts as "top-level" if it has no .collapse ancestor —
    // i.e. it isn't itself sitting inside another submenu already.
    function isTopLevel($a) {
        return $a.closest('.collapse').length === 0;
    }

    // Resolve an anchor's target .collapse via data-target (works
    // regardless of whether it's a sibling or wrapped together).
    function getCollapse($a) {
        var target = $a.attr('data-target') || $a.data('target');
        if (!target) {
            return $();
        }
        try {
            return $(target);
        } catch (e) {
            return $();
        }
    }

    // The element whose position we anchor the flyout to, and the one we
    // toggle the "open" class on — the .sidenav-item wrapper if this
    // project uses one, otherwise the anchor itself.
    function getTrigger($a) {
        var $item = $a.closest('.sidenav-item');
        return $item.length ? $item : $a;
    }

    function extractTitle($a) {
        var $clone = $a.clone();
        $clone.find('.nav-link-icon, .sidenav-collapse-arrow').remove();
        return $.trim($clone.text());
    }

    function ensureHeader($trigger, $a, $collapse) {
        if ($collapse.find('> .qp-popover-header').length > 0) {
            return;
        }
        var title = $trigger.attr('data-flyout-title') || extractTitle($a);
        var $header = $('<div class="qp-popover-header"><span></span></div>');
        $header.find('span').text(title);
        $collapse.prepend($header);
    }

    function positionFlyout($trigger, $collapse) {
        var rect = $trigger[0].getBoundingClientRect();
        var top = rect.top;
        var left = rect.right + GAP;

        var h = $collapse.outerHeight() || 0;
        var vh = window.innerHeight;
        if (top + h > vh - 12) {
            top = Math.max(12, vh - h - 12);
        }

        $collapse.css({
            position: 'fixed',
            top: top + 'px',
            left: left + 'px'
        });
    }

    function closeOpenPair() {
        if (!openPair) {
            return;
        }
        openPair.$trigger.removeClass('qp-popover-open');
        openPair.$collapse.removeClass('show qp-popover-open')
            .css({ position: '', top: '', left: '' });
        openPair = null;
    }

    function openPairFor($a) {
        var $collapse = getCollapse($a);
        if ($collapse.length === 0) {
            return; // no submenu target found, nothing to flyout
        }
        var $trigger = getTrigger($a);

        if (openPair && openPair.$trigger[0] === $trigger[0]) {
            // already open for this item — just reposition in case content changed
            positionFlyout($trigger, $collapse);
            return;
        }
        closeOpenPair();

        ensureHeader($trigger, $a, $collapse);
        $trigger.addClass('qp-popover-open');
        $collapse.addClass('show qp-popover-open');
        positionFlyout($trigger, $collapse);
        openPair = { $trigger: $trigger, $collapse: $collapse };
    }

    function scheduleOpen($a) {
        clearTimeout(closeTimer);
        clearTimeout(openTimer);
        openTimer = setTimeout(function () {
            openPairFor($a);
        }, OPEN_DELAY);
    }

    function scheduleClose() {
        clearTimeout(openTimer);
        clearTimeout(closeTimer);
        closeTimer = setTimeout(closeOpenPair, CLOSE_DELAY);
    }

    // Hovering a TOP-LEVEL grouped link opens its flyout.
    $(document).on('mouseenter', '.sidenav-menu a.nav-link[data-toggle="collapse"]', function () {
        if (!active()) {
            return;
        }
        var $a = $(this);
        if (!isTopLevel($a)) {
            return; // nested toggle — let it behave as a normal inline accordion
        }
        scheduleOpen($a);
    });

    $(document).on('mouseleave', '.sidenav-menu a.nav-link[data-toggle="collapse"]', function () {
        if (!active()) {
            return;
        }
        if (!isTopLevel($(this))) {
            return;
        }
        clearTimeout(openTimer);
        scheduleClose();
    });

    // The flyout panel itself isn't necessarily a DOM descendant of its
    // trigger in the unwrapped markup shape, so it needs its own
    // enter/leave handlers to stay open while hovered.
    $(document).on('mouseenter', '.collapse.qp-popover-open', function () {
        clearTimeout(closeTimer);
    });
    $(document).on('mouseleave', '.collapse.qp-popover-open', function () {
        if (!active()) {
            return;
        }
        clearTimeout(openTimer);
        scheduleClose();
    });

    // Clicking a real (non-toggle) link inside an open flyout closes it
    // right away, so it doesn't flash open again on the next page load.
    $(document).on('click', '.collapse.qp-popover-open .sidenav-menu-nested .nav-link:not([data-toggle="collapse"])', function () {
        closeOpenPair();
    });

    // Reposition the open flyout on resize/scroll (e.g. its height
    // changed because a nested accordion inside it was expanded).
    $(window).on('scroll resize', function () {
        if (openPair) {
            positionFlyout(openPair.$trigger, openPair.$collapse);
        }
    });

    // Also reposition if a nested toggle inside the flyout expands/
    // collapses and changes its height.
    $(document).on('shown.bs.collapse hidden.bs.collapse', '.collapse.qp-popover-open .collapse', function () {
        if (openPair) {
            positionFlyout(openPair.$trigger, openPair.$collapse);
        }
    });

    // Drop the open flyout if we leave collapsed/desktop mode
    $(window).on('resize', function () {
        if (!active()) {
            closeOpenPair();
        }
    });
    $(document).on('click', '#sidebarToggle, #sidenavToggleBtn', function () {
        setTimeout(closeOpenPair, 0);
    });

    // ------------------------------------------------------------------
    // Block Bootstrap's own click-to-toggle on TOP-LEVEL links while the
    // rail is collapsed (icon-only) — we want hover-only behavior there.
    // Must run in the capture phase so it fires BEFORE Bootstrap's own
    // bubble-phase document click handler (which is what actually toggles
    // the .show class and fights with the flyout).
    // ------------------------------------------------------------------
    document.addEventListener('click', function (e) {
        if (!active()) {
            return; // expanded sidebar or mobile — let Bootstrap behave normally
        }
        var target = e.target.closest ? e.target.closest('a.nav-link[data-toggle="collapse"]') : null;
        if (!target) {
            return;
        }
        var $a = $(target);
        if (!isTopLevel($a)) {
            return; // nested toggle (e.g. "Service Info") — still fine to click
        }
        e.preventDefault();
        e.stopImmediatePropagation();
    }, true);

})(jQuery);