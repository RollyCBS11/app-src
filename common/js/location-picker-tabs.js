// Combined tabbed location picker: presents an existing cascade of
// address fields (e.g. Province -> City -> District -> Village) as one
// widget with a tab per level instead of stacked fields.
//
// This is purely a presentation layer. It does NOT duplicate or replace
// any of the cascade/fetch logic already implemented in
// self-host-address-suggestion.js - it drives the very same underlying
// <select>/<input class="search"> elements (same id/name/class/attributes
// a shipping-address-*.php page already renders), just kept out of sight
// (see the CSS's visually-hidden rule) in favor of plain lists built from
// their own data: the province list is built here from #fields_state's own
// <option>s (still populated from states.js by the existing
// addStatesProvince()); city/district/village's lists are the very
// .self-suggestions boxes self-host-address-suggestion.js already renders,
// just restyled to sit inline instead of floating. Picking a province still
// sets the real select's value and fires its native "change" event; picking
// a city/district/village suggestion is still handled entirely by
// self-host-address-suggestion.js - this script only reacts to the visible
// side effects (the "disabled" attribute applyToEnableEl() toggles, and
// each field's own suggestions box closing with a value now in it) to
// decide which tab to show next and what to display in the summary input.
//
// Opt-in markup contract, see shipping/shipping-address-id.php:
//   <div class="location-picker" data-location-picker
//        data-field-order="fields_state fields_city fields_district fields_village">
//     <input class="location-picker-trigger" readonly>
//     <div class="location-picker-panel">
//       <div class="location-picker-tabs">
//         <button class="location-picker-tab" data-field="fields_state">Province</button>
//         ...
//       </div>
//       <div class="location-picker-body">
//         <div class="location-picker-pane" data-pane="fields_state">...</div>
//         ...
//       </div>
//     </div>
//   </div>
(function () {

    function addClass(el, name) {
        if ((' ' + el.className + ' ').indexOf(' ' + name + ' ') === -1) {
            el.className = (el.className + ' ' + name).replace(/^\s+/, '');
        }
    }

    function removeClass(el, name) {
        var re = new RegExp('(^|\\s)' + name + '(\\s|$)', 'g');
        el.className = el.className.replace(re, ' ').replace(/^\s+|\s+$/g, '');
    }

    function initLocationPicker(container) {
        // Forms are swapped in/out via AJAX (see integrated.js), so this can
        // fire repeatedly for freshly-inserted markup - guard against binding
        // the same node twice.
        if (container.dataset.locationPickerBound === '1') return;
        container.dataset.locationPickerBound = '1';

        var order = (container.getAttribute('data-field-order') || '').split(/\s+/).filter(Boolean);
        if (!order.length) return;

        var trigger = container.querySelector('.location-picker-trigger');
        var panel = container.querySelector('.location-picker-panel');
        if (!panel) return;

        var tabs = {};
        var panes = {};
        var i, id;
        for (i = 0; i < order.length; i++) {
            id = order[i];
            tabs[id] = container.querySelector('.location-picker-tab[data-field="' + id + '"]');
            panes[id] = container.querySelector('.location-picker-pane[data-pane="' + id + '"]');
        }

        var activeId = order[0];
        var observers = [];

        function fieldEl(fieldId) {
            return document.getElementById(fieldId);
        }

        function isEnabled(fieldId) {
            var el = fieldEl(fieldId);
            return !!el && !el.disabled;
        }

        function updateTabsEnabledState() {
            for (var j = 1; j < order.length; j++) {
                var fieldId = order[j];
                if (tabs[fieldId]) tabs[fieldId].disabled = !isEnabled(fieldId);
            }
        }

        function markActiveTab(id) {
            var j, fieldId;
            for (j = 0; j < order.length; j++) {
                fieldId = order[j];
                if (!tabs[fieldId]) continue;
                if (fieldId === id) {
                    addClass(tabs[fieldId], 'active');
                } else {
                    removeClass(tabs[fieldId], 'active');
                }
            }
        }

        // Collapses a non-active pane via "visibility" + zero height rather
        // than display:none/[hidden] - see the CSS file's note on
        // .location-picker-panel for why: the site's own validateForm()
        // decides whether an empty required field still needs flagging by
        // checking jQuery's :visible, which ignores "visibility" but treats
        // "display:none" as invisible. Collapsing this way means an
        // unfinished city/district/village stays just as catchable by that
        // check as it was in the original stacked-fields layout, no matter
        // which tab happens to be active when the shopper submits.
        function collapseOtherPanes(id) {
            var j, fieldId;
            for (j = 0; j < order.length; j++) {
                fieldId = order[j];
                if (fieldId === id || !panes[fieldId]) continue;
                addClass(panes[fieldId], 'lp-collapsed');
            }
        }

        // idx===0 (the first tab, e.g. province) is always reachable; every
        // other tab can only be switched to once its own field is enabled -
        // exactly the same rule self-host-address-suggestion.js's
        // toEnableEl/applyToEnableEl already enforces on the fields themselves.
        function activateTab(id, focusField) {
            var idx = order.indexOf(id);
            if (idx > 0 && !isEnabled(id)) return;

            // Expand the target pane and focus it BEFORE collapsing any
            // other pane. self-host-address-suggestion.js deliberately keeps
            // focus on the field a suggestion was just picked from (see its
            // onSuggestionsMousedown), so at this point that previous field
            // is usually still focused. Collapsing its pane first would
            // force the browser to blur it right as we're also trying to
            // focus the new field - two competing focus changes in the same
            // tick - and the forced blur's own fallback can win that race,
            // leaving focus stuck on <body> instead of the new field (which
            // then never fires the "focus" trigger that fetches its
            // suggestions). Moving focus normally to the new field first,
            // then collapsing the old one (already blurred by then, nothing
            // left to steal focus from), avoids the race entirely.
            if (panes[id]) removeClass(panes[id], 'lp-collapsed');
            markActiveTab(id);
            activeId = id;

            if (focusField !== false) {
                var el = fieldEl(id);
                if (el) el.focus();
            }

            collapseOtherPanes(id);
        }

        // The trigger is a <textarea> so a long breadcrumb can wrap onto
        // several lines instead of clipping - this grows/shrinks its height
        // to fit exactly that many lines (no scrollbar, nothing cut off).
        // Resetting height to 'auto' first is required for scrollHeight to
        // shrink back down when the text gets shorter (otherwise it only
        // ever reports at least the previously-set height).
        function resizeTrigger() {
            if (!trigger) return;
            trigger.style.height = 'auto';
            trigger.style.height = trigger.scrollHeight + 'px';
        }

        function recomputeBreadcrumb() {
            if (!trigger) return;
            var parts = [];
            for (var j = 0; j < order.length; j++) {
                var el = fieldEl(order[j]);
                if (!el) continue;
                var val = (el.tagName === 'SELECT')
                    ? (el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : '')
                    : el.value;
                val = val ? val.replace(/^\s+|\s+$/g, '') : '';
                if (val) parts.push(val);
            }
            trigger.value = parts.join(', ');
            resizeTrigger();
        }

        function openPanel() {
            addClass(container, 'open');
            // Deferred for the same request-sequence reason as every other
            // focus-triggering call in this file (see the tab-click handler
            // below): reopening the picker most often lands back on
            // city/district/village (whichever tab was active when it
            // closed - typically the last one, village, once the whole
            // cascade is filled in), and activateTab()'s focus() would
            // otherwise fetch that field's suggestions from within this
            // same trigger click, before the click finishes bubbling to
            // document - where that field's own document click listener
            // (bound once for every .search field, regardless of what was
            // actually clicked) bumps its request sequence again right
            // after, discarding the very response this fetch was for.
            setTimeout(function () { activateTab(activeId); }, 0);
        }

        function closePanel() {
            removeClass(container, 'open');
        }

        function isOpen() {
            return (' ' + container.className + ' ').indexOf(' open ') !== -1;
        }

        if (trigger) {
            trigger.addEventListener('click', function () {
                if (isOpen()) {
                    closePanel();
                } else {
                    openPanel();
                }
            });
        }

        // Deferred via setTimeout for the same reason as the "enabled"
        // branch of the MutationObserver further down: self-host-address-
        // suggestion.js binds a document-wide click listener per .search
        // field (city/district/village), each of which hides ITS OWN
        // suggestions and bumps ITS OWN request-sequence counter on any
        // click that isn't literally on that field - including a click on
        // one of these tab buttons. Calling activateTab (and its focus(),
        // which fetches this tab's suggestions) immediately, from within
        // the tab button's own click listener, runs it BEFORE the same
        // click finishes bubbling to document - so the field's fetch
        // captures its sequence number, then that field's own document
        // click listener bumps the sequence again moments later, and the
        // fetch's response gets silently discarded when it arrives.
        // Deferring to a new macrotask lets the click fully settle first.
        for (i = 0; i < order.length; i++) {
            (function (id) {
                if (!tabs[id]) return;
                tabs[id].addEventListener('click', function () {
                    setTimeout(function () { activateTab(id); }, 0);
                });
            })(order[i]);
        }

        // Uses composedPath() rather than container.contains(e.target):
        // self-host-address-suggestion.js's hideSuggestions() clears the
        // suggestions box (innerHTML='') as the last step of handling a
        // suggestion pick, detaching the very row that was clicked while
        // this same click is still bubbling toward document. By the time
        // this listener runs, contains() would check that already-detached
        // node against the current tree and (wrongly) say it's not inside
        // the picker, closing it right as a pick is being made.
        // composedPath() reflects the path the event actually bubbled
        // through, captured before that removal happened.
        document.addEventListener('click', function (e) {
            var path = (typeof e.composedPath === 'function') ? e.composedPath() : null;
            var inside = path ? path.indexOf(container) !== -1 : container.contains(e.target);
            if (!inside) closePanel();
        });

        document.addEventListener('keydown', function (e) {
            var code = e.keyCode || e.which;
            if (code === 27 && isOpen()) closePanel();
        });

        // Going enabled -> disabled (the upstream field was cleared/changed,
        // e.g. picking a different city resets district/village) falls back
        // to the nearest still-enabled tab. Forward advancing is NOT driven
        // from here (see the per-field suggestions-box watcher further down
        // for why) - just tab enabled/disabled bookkeeping and this one
        // fallback case.
        for (i = 1; i < order.length; i++) {
            (function (id, idx) {
                var el = fieldEl(id);
                if (!el) return;

                var observer = new MutationObserver(function () {
                    updateTabsEnabledState();
                    recomputeBreadcrumb();

                    if (el.disabled && activeId === id) {
                        for (var b = idx - 1; b >= 0; b--) {
                            if (b === 0 || isEnabled(order[b])) {
                                activateTab(order[b], false);
                                break;
                            }
                        }
                    }
                });
                observer.observe(el, { attributes: true, attributeFilter: ['disabled'] });
                observers.push(observer);
            })(order[i], i);
        }

        // The province <select> is populated from states.js's own States
        // data by the existing addStatesProvince() (see integrated.js) -
        // untouched here. What shows to the shopper instead of that native
        // select is a plain list built from its <option>s (see
        // renderProvinceOptions below); picking a row just sets the real
        // select's value and dispatches "change" like any native pick would.
        var firstEl = fieldEl(order[0]);
        var firstPane = panes[order[0]];
        var provinceList = firstPane ? firstPane.querySelector('[data-role="province-list"]') : null;

        // Returns whichever row ends up marked "lp-selected" (or null), so
        // callers that care - see firstEl's "change" listener below - can
        // scroll it into view without this function always doing so itself
        // (a plain rebuild, e.g. after addStatesProvince() repopulates the
        // options on a country switch, shouldn't yank the list's scroll
        // position).
        function renderProvinceOptions() {
            if (!provinceList || !firstEl || firstEl.tagName !== 'SELECT') return null;
            provinceList.innerHTML = '';
            var j, opt, row, selectedRow = null;
            for (j = 0; j < firstEl.options.length; j++) {
                opt = firstEl.options[j];
                if (!opt.value) continue; // skip the "-- Select --" placeholder
                row = document.createElement('div');
                row.textContent = opt.text;
                row.dataset.value = opt.value; // read back by the keyboard handler below to commit a highlighted row
                if (opt.value === firstEl.value) {
                    addClass(row, 'lp-selected');
                    selectedRow = row;
                }
                (function (value) {
                    row.addEventListener('click', function () {
                        firstEl.value = value;
                        firstEl.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                })(opt.value);
                provinceList.appendChild(row);
            }
            return selectedRow;
        }

        if (firstEl && provinceList) {
            renderProvinceOptions();
            // addStatesProvince() empties and rebuilds the <option> list
            // (on load, and again on every country switch) - rebuild the
            // visible list to match whenever that happens.
            var provinceOptionsObserver = new MutationObserver(renderProvinceOptions);
            provinceOptionsObserver.observe(firstEl, { childList: true });
            observers.push(provinceOptionsObserver);
        }

        // Left as a real, focusable <select> (see the CSS's visually-hidden
        // clip) so keyboard users have something to type into - but a
        // focused native <select> handles arrow keys and letter type-ahead
        // itself, changing its value and firing "change" (see the listener
        // below) on every single keystroke, before the shopper ever meant to
        // commit anything. That's fine for an actual <select> since its
        // native popup shows the highlight as you go, but this one has no
        // visible popup - the shopper only sees this custom list, so every
        // keystroke looked like it was auto-picking a province. Prevent the
        // browser's own handling here and re-implement the same
        // "highlight first, commit on Enter/Space/click" pattern the
        // city/district/village lists already use (see jumpToLetter in
        // self-host-address-suggestion.js) - just against these plain
        // <div> rows instead of <option>s, so the two lists behave alike.
        var provinceHighlightIndex = -1;

        function provinceRows() {
            return provinceList ? provinceList.children : [];
        }

        function highlightProvinceRow(idx, rows) {
            var k;
            for (k = 0; k < rows.length; k++) {
                if (k === idx) {
                    addClass(rows[k], 'active');
                } else {
                    removeClass(rows[k], 'active');
                }
            }
            provinceHighlightIndex = idx;
            if (rows[idx] && rows[idx].scrollIntoView) rows[idx].scrollIntoView({ block: 'nearest' });
        }

        function commitProvinceRow(idx, rows) {
            if (idx < 0 || !rows[idx]) return;
            firstEl.value = rows[idx].dataset.value;
            firstEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (firstEl && provinceList) {
            firstEl.addEventListener('keydown', function (e) {
                var rows = provinceRows();
                if (!rows.length) return;
                var code = e.keyCode || e.which;

                if (code === 40) { // ArrowDown
                    e.preventDefault();
                    highlightProvinceRow((provinceHighlightIndex + 1) % rows.length, rows);
                } else if (code === 38) { // ArrowUp
                    e.preventDefault();
                    highlightProvinceRow((provinceHighlightIndex - 1 + rows.length) % rows.length, rows);
                } else if (code === 13 || code === 32) { // Enter / Space
                    e.preventDefault();
                    commitProvinceRow(provinceHighlightIndex, rows);
                } else if (!e.ctrlKey && !e.metaKey && !e.altKey && e.key && e.key.length === 1) {
                    e.preventDefault();
                    var letter = e.key.toLowerCase();
                    var i, idx, text;
                    for (i = 1; i <= rows.length; i++) {
                        idx = (provinceHighlightIndex + i) % rows.length;
                        text = rows[idx].textContent ? rows[idx].textContent.replace(/^\s+/, '') : '';
                        if (text.charAt(0).toLowerCase() === letter) {
                            highlightProvinceRow(idx, rows);
                            break;
                        }
                    }
                }
            });

            // Leaving without committing (e.g. tabbing away mid-browse)
            // shouldn't leave a stray highlight behind for whoever reopens
            // this tab next - the actually-picked row (if any) keeps
            // showing via its own "lp-selected" class regardless.
            firstEl.addEventListener('blur', function () {
                var rows = provinceRows();
                for (var k = 0; k < rows.length; k++) removeClass(rows[k], 'active');
                provinceHighlightIndex = -1;
            });
        }

        // The first field (typically a plain <select>, e.g. province) has no
        // "disabled" transition of its own to hook into - track its native
        // change directly instead, both for the summary text and to advance
        // to the next tab once it enables one. Unlike a MutationObserver on
        // a downstream field's "disabled" attribute (which only fires the
        // FIRST time that field is unlocked), a native "change" fires again
        // every time the shopper picks a different province, so reselecting
        // an already-completed province still advances correctly.
        if (firstEl) {
            firstEl.addEventListener('change', function () {
                recomputeBreadcrumb();
                // Refresh which row shows as selected - fires for a row
                // clicked directly, or for the keyboard handler above
                // committing whichever row it had highlighted (Enter/Space).
                // Scrolled into view again in case "change" was dispatched
                // programmatically from somewhere else that isn't already
                // scrolled to it.
                var selectedRow = renderProvinceOptions();
                if (selectedRow && selectedRow.scrollIntoView) selectedRow.scrollIntoView({ block: 'nearest' });
                var nextId = order[1];
                if (nextId && isEnabled(nextId)) {
                    // Deferred for the same reason as the tab-click handler
                    // and the suggestions-box watcher below: self-host-
                    // address-suggestion.js's document-wide click listener
                    // for city (bound regardless of which element the
                    // native <select> click actually landed on) would
                    // otherwise bump city's own request sequence right after
                    // this same click's fetch captures it, discarding the
                    // response.
                    setTimeout(function () {
                        if (isEnabled(nextId)) activateTab(nextId);
                    }, 0);
                }
            });
        }

        // Advances to the next tab every time THIS field's own suggestions
        // box closes with a value present - covers a fresh pick AND a
        // reselect (picking a different value for a field that was already
        // filled in) alike, unlike watching the NEXT field's "disabled"
        // attribute above, which only ever fires the first time that next
        // field is unlocked. self-host-address-suggestion.js's
        // hideSuggestions() always sets style="display:none" on this box as
        // the last step of handling a pick (or a blur/escape/click-away
        // with nothing picked - the value check below is what tells those
        // apart), and does so on a node that's never removed, so watching
        // its "style" attribute is a reliable, reselect-safe signal - unlike
        // a delegated click listener, which can't be (see the
        // composedPath() note above).
        //
        // Clicking a tab, or picking a value on any tab before the last
        // one, never closes the picker - only finishing the last field, or
        // an explicit action (clicking outside, Escape, or the trigger
        // itself), does.
        // Marks whichever rendered suggestion row's text matches this
        // field's own current value - a case-insensitive comparison because
        // for ID, applySuggestionValue() (see self-host-address-
        // suggestion.js's selectSuggestion) title-cases the value it
        // actually stores (e.g. "Batang Hari"), while a freshly rendered
        // row's text is the suggestion's raw, all-caps API label (e.g.
        // "BATANG HARI").
        function highlightSelectedRow(suggestionsBox, field) {
            var value = field.value ? field.value.replace(/^\s+|\s+$/g, '').toLowerCase() : '';
            var rows = suggestionsBox.children;
            var k, rowText;
            for (k = 0; k < rows.length; k++) {
                rowText = rows[k].textContent ? rows[k].textContent.replace(/^\s+|\s+$/g, '').toLowerCase() : '';
                if (value && rowText === value) {
                    addClass(rows[k], 'lp-selected');
                } else {
                    removeClass(rows[k], 'lp-selected');
                }
            }
        }

        for (i = 1; i < order.length; i++) {
            (function (id, idx) {
                var el = fieldEl(id);
                var pane = panes[id];
                var suggestionsBox = pane ? pane.querySelector('.self-suggestions') : null;
                if (!el || !suggestionsBox) return;

                // Tracks this field's own value across mutations, so a
                // genuine pick can be told apart from this box merely
                // closing for some unrelated reason without depending on
                // "activeId" - which, at the exact moment this fires, can
                // still be stale: self-host-address-suggestion.js binds a
                // document-wide click listener per .search field that hides
                // ITS OWN suggestions on any click that isn't literally on
                // that field - including a click on some OTHER tab button -
                // and that runs synchronously as part of the SAME click
                // whose own activateTab() (the thing that actually updates
                // activeId) is deliberately deferred (see the tab-click
                // handler above). Relying on activeId here would (and, in
                // an earlier version of this file, did) misread "the
                // shopper clicked a different tab, which incidentally blurred
                // and hid this one" as "this field was just finished",
                // closing the whole picker on an unrelated tab switch.
                var lastValue = el.value;

                var observer = new MutationObserver(function () {
                    // Whatever changed (fresh rows rendered, or the box
                    // just closed), re-mark which row (if any) matches the
                    // field's current value - covers both a field whose
                    // list is showing for the first time and one being
                    // revisited (e.g. clicking back to an earlier tab, or
                    // reselecting the same tab) after already holding a
                    // value from before.
                    highlightSelectedRow(suggestionsBox, el);
                    recomputeBreadcrumb();
                    // This box's style also changes the OTHER way - going
                    // hidden -> visible when a fresh fetch renders results
                    // (e.g. reselecting this same tab re-fetches its list,
                    // which still holds its old value while loading) - only
                    // the visible -> hidden transition means a pick (or a
                    // blur/escape/click-away/tab-switch) just happened.
                    if (suggestionsBox.style.display !== 'none') return;

                    // A pick is the only thing that actually changes this
                    // field's value - the box closing for any other reason
                    // (blur, Escape, clicking outside, or the tab-switch
                    // case described above) leaves it exactly as it already
                    // was, so nothing here should react to that.
                    var valueChanged = el.value !== lastValue;
                    lastValue = el.value;
                    if (!valueChanged || !el.value) return;

                    var nextId = order[idx + 1];
                    if (nextId && isEnabled(nextId)) {
                        setTimeout(function () {
                            if (activeId === id && isEnabled(nextId)) activateTab(nextId);
                        }, 0);
                    } else if (!nextId) {
                        // Last field: a genuine pick here finishes the
                        // whole cascade, so close the picker - unlike every
                        // other tab, where picking (or just clicking
                        // around) keeps it open for the shopper to
                        // review/change other fields.
                        closePanel();
                    }
                });
                observer.observe(suggestionsBox, { attributes: true, attributeFilter: ['style'], childList: true });
                observers.push(observer);
            })(order[i], i);
        }

        // integrated.js's own validateForm()/inlineErrorMsg() insert a
        // <span class="error-message"> right after whichever field failed
        // validation (via jQuery's el.after(...)), same as for any other
        // field on the page - but state/city/district/village are hidden
        // now (see the CSS), so a message placed right next to one of them
        // would never be seen. The CSS hides those; this mirrors their
        // combined text into one message placed after the visible summary
        // input instead - deduplicating identical text (state and city, for
        // instance, can both fall back to the exact same generic "Required
        // Field." message) and comma-joining the rest.
        var summaryErrorEl = null;

        function errorMessageAfter(el) {
            var next = el && el.nextElementSibling;
            if (!next || next.tagName !== 'SPAN') return '';
            if ((' ' + next.className + ' ').indexOf(' error-message ') === -1) return '';
            return next.textContent || '';
        }

        function updateSummaryError() {
            if (!trigger || !trigger.parentNode) return;

            var seen = [];
            var parts = [];
            var j, text;
            for (j = 0; j < order.length; j++) {
                text = errorMessageAfter(fieldEl(order[j]));
                if (text && seen.indexOf(text) === -1) {
                    seen.push(text);
                    parts.push(text);
                }
            }

            if (!parts.length) {
                if (summaryErrorEl && summaryErrorEl.parentNode) {
                    summaryErrorEl.parentNode.removeChild(summaryErrorEl);
                }
                summaryErrorEl = null;
                removeClass(trigger, 'error');
                return;
            }

            if (!summaryErrorEl || !summaryErrorEl.parentNode) {
                summaryErrorEl = document.createElement('span');
                summaryErrorEl.className = 'error-message location-picker-summary-error';
                trigger.parentNode.insertBefore(summaryErrorEl, trigger.nextSibling);
            }
            summaryErrorEl.textContent = parts.join(', ');
            // Same "error" class the site's own inlineErrorMsg() callers add
            // directly to a field that failed validation (see
            // integrated.js) - applied here to the visible trigger instead,
            // since state/city/district/village themselves are hidden.
            addClass(trigger, 'error');
        }

        // inlineErrorMsg() always removes any previous error span before
        // (optionally) adding a fresh one, so this is a childList mutation
        // on the field's own parent every time - never just a text update.
        for (i = 0; i < order.length; i++) {
            (function (id) {
                var el = fieldEl(id);
                if (!el || !el.parentNode) return;
                var errorObserver = new MutationObserver(updateSummaryError);
                errorObserver.observe(el.parentNode, { childList: true });
                observers.push(errorObserver);
            })(order[i]);
        }
        updateSummaryError();

        // The trigger's wrapped line count depends on its own width, so a
        // viewport/container resize (rotating a phone, resizing the
        // window) can leave its cached height stale even with the same
        // text - not just a breadcrumb/error change.
        window.addEventListener('resize', resizeTrigger);

        container.locationPickerTeardown = function () {
            for (var j = 0; j < observers.length; j++) observers[j].disconnect();
            window.removeEventListener('resize', resizeTrigger);
        };

        updateTabsEnabledState();
        activateTab(activeId, false);
        recomputeBreadcrumb();
    }

    function forEachPicker(root, fn) {
        if (!root || root.nodeType !== 1) return;
        if (root.matches && root.matches('[data-location-picker]')) fn(root);
        if (root.querySelectorAll) {
            var found = root.querySelectorAll('[data-location-picker]');
            for (var i = 0; i < found.length; i++) fn(found[i]);
        }
    }

    forEachPicker(document.body, initLocationPicker);

    new MutationObserver(function (mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var added = mutations[i].addedNodes;
            for (var j = 0; j < added.length; j++) forEachPicker(added[j], initLocationPicker);
        }
    }).observe(document.body, { childList: true, subtree: true });
})();
