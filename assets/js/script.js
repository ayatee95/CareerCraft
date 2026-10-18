/*
 * CareerCraft - small, dependency-free UI helpers (Phase 5).
 * Everything here is progressive enhancement: the site still works without it.
 *   1. Mobile navigation (slide-in sidebar)
 *   2. Confirmation dialog for destructive actions   (forms with data-confirm)
 *   3. "Working..." state on submit buttons            (prevents double submit)
 *   4. File inputs: chosen name, size check, image preview
 */
(function () {
    'use strict';

    var body = document.body;

    /* ---------------------------------------------------------------- 1. NAV */
    var toggle = document.querySelector('[data-nav-toggle]');
    var sidebar = document.getElementById('sidebar');

    function setNav(open) {
        if (!sidebar || !toggle) { return; }
        body.classList.toggle('nav-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        if (open) {
            var first = sidebar.querySelector('a, button');
            if (first) { first.focus(); }
        } else {
            toggle.focus();
        }
    }

    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            setNav(!body.classList.contains('nav-open'));
        });
        Array.prototype.forEach.call(document.querySelectorAll('[data-nav-close]'), function (el) {
            el.addEventListener('click', function () {
                if (body.classList.contains('nav-open')) { setNav(false); }
            });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && body.classList.contains('nav-open')) { setNav(false); }
        });
        // Back on a wide screen: make sure the menu is not left "open"
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992 && body.classList.contains('nav-open')) {
                body.classList.remove('nav-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ------------------------------------------- helpers for form submission */
    // The server decides what to do from the NAME of the clicked button
    // (add_skill, delete_skill, ...). A disabled button is not sent, so we copy it
    // into a hidden field before we change anything.
    function mirrorSubmitter(form, submitter) {
        var btn = submitter || form.querySelector('button[name][type="submit"], button[name]:not([type])');
        if (!btn || !btn.name) { return; }
        var old = form.querySelector('input[data-mirror]');
        if (old) { old.parentNode.removeChild(old); }
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = btn.name;
        hidden.value = btn.value || '1';
        hidden.setAttribute('data-mirror', '1');
        form.appendChild(hidden);
    }

    function setBusy(form, submitter) {
        form.setAttribute('aria-busy', 'true');
        var buttons = form.querySelectorAll('button[type="submit"], button:not([type])');
        // Disable after the browser has collected the form data.
        window.setTimeout(function () {
            Array.prototype.forEach.call(buttons, function (b) {
                b.classList.add('is-loading');
                b.disabled = true;
            });
        }, 0);
    }

    function resetBusy() {
        Array.prototype.forEach.call(document.querySelectorAll('form[aria-busy="true"]'), function (form) {
            form.removeAttribute('aria-busy');
            Array.prototype.forEach.call(form.querySelectorAll('button.is-loading'), function (b) {
                b.classList.remove('is-loading');
                b.disabled = false;
            });
        });
    }
    // Coming back with the browser's Back button must not leave buttons disabled.
    window.addEventListener('pageshow', resetBusy);

    /* ------------------------------------------------ 2. CONFIRM DIALOG */
    var dialog = null;
    var pending = null;

    function buildDialog() {
        var d = document.createElement('dialog');
        d.className = 'modal';
        d.setAttribute('aria-labelledby', 'confirm-title');
        d.innerHTML =
            '<div class="modal-icon" aria-hidden="true">' +
            '<svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>' +
            '</div>' +
            '<h2 id="confirm-title">Are you sure?</h2>' +
            '<p data-confirm-text></p>' +
            '<div class="modal-actions">' +
            '<button type="button" class="btn btn-ghost" data-confirm-cancel>Cancel</button>' +
            '<button type="button" class="btn btn-solid-danger" data-confirm-ok>Delete</button>' +
            '</div>';
        document.body.appendChild(d);
        d.querySelector('[data-confirm-cancel]').addEventListener('click', function () { d.close(); });
        d.querySelector('[data-confirm-ok]').addEventListener('click', function () {
            var job = pending;
            pending = null;
            d.close();
            if (job) {
                mirrorSubmitter(job.form, job.submitter);
                setBusy(job.form, job.submitter);
                job.form.setAttribute('data-confirmed', '1');
                // HTMLFormElement.submit() does not fire "submit" again, so no loop.
                job.form.submit();
            }
        });
        d.addEventListener('close', function () { pending = null; });
        return d;
    }

    function askConfirm(form, submitter) {
        var message = form.getAttribute('data-confirm') || 'This cannot be undone.';
        if (typeof HTMLDialogElement === 'undefined' || typeof window.HTMLDialogElement.prototype.showModal !== 'function') {
            return window.confirm(message);              // old browsers: native dialog
        }
        if (!dialog) { dialog = buildDialog(); }
        dialog.querySelector('[data-confirm-text]').textContent = message;
        pending = { form: form, submitter: submitter };
        dialog.showModal();
        dialog.querySelector('[data-confirm-cancel]').focus();
        return null;                                      // answer comes later
    }

    /* --------------------------------------------- 3. SUBMIT HANDLING */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM' || form.getAttribute('data-confirmed') === '1') { return; }

        if (form.hasAttribute('data-confirm')) {
            var answer = askConfirm(form, e.submitter);
            if (answer === null) { e.preventDefault(); return; }   // waiting for the modal
            if (answer === false) { e.preventDefault(); return; }  // native confirm: cancelled
        }
        mirrorSubmitter(form, e.submitter);
        setBusy(form, e.submitter);
    });

    /* ------------------------------------------------- 4. FILE INPUTS */
    function formatSize(bytes) {
        if (bytes < 1024 * 1024) { return Math.max(1, Math.round(bytes / 1024)) + ' KB'; }
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    Array.prototype.forEach.call(document.querySelectorAll('input[type="file"]'), function (input) {
        var zone = input.closest('.file-drop');
        var chosen = zone ? zone.querySelector('.file-chosen') : null;
        var error = zone ? zone.querySelector('.file-error') : null;
        var maxMb = parseFloat(input.getAttribute('data-max-mb') || '0');
        var preview = document.querySelector(input.getAttribute('data-preview') || '___none___');

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (error) { error.textContent = ''; }
            if (!file) {
                if (chosen) { chosen.textContent = ''; }
                return;
            }
            if (maxMb && file.size > maxMb * 1024 * 1024) {
                if (error) { error.textContent = 'This file is ' + formatSize(file.size) + '. The limit is ' + maxMb + ' MB.'; }
                input.value = '';
                if (chosen) { chosen.textContent = ''; }
                return;
            }
            if (chosen) { chosen.textContent = file.name + ' (' + formatSize(file.size) + ')'; }

            if (preview && file.type && file.type.indexOf('image/') === 0 && window.URL && URL.createObjectURL) {
                var url = URL.createObjectURL(file);
                var img = preview.tagName === 'IMG' ? preview : preview.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.alt = 'Selected picture preview';
                    img.style.width = '100%'; img.style.height = '100%'; img.style.objectFit = 'cover';
                    preview.innerHTML = '';
                    preview.appendChild(img);
                }
                img.src = url;
            }
        });

        if (zone) {
            ['dragenter', 'dragover'].forEach(function (name) {
                zone.addEventListener(name, function () { zone.classList.add('is-drag'); });
            });
            ['dragleave', 'drop'].forEach(function (name) {
                zone.addEventListener(name, function () { zone.classList.remove('is-drag'); });
            });
        }
    });
})();
