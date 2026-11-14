/* HTML widget: Visual / HTML editor for textareas marked [data-html-widget]. No dependencies. */
(function () {
    'use strict';
    var BUTTONS = [
        ['bold', 'B', 'Bold'], ['italic', 'I', 'Italic'], ['underline', 'U', 'Underline'],
        ['h2', 'H2', 'Heading'], ['h3', 'H3', 'Subheading'], ['p', 'P', 'Paragraph'],
        ['ul', '\u2022 List', 'Bullet list'], ['ol', '1. List', 'Numbered list'], ['quote', '\u201C Quote', 'Quote'],
        ['link', 'Link', 'Insert link'], ['image', 'Image', 'Insert image by URL'], ['clear', 'Clear', 'Remove formatting']
    ];

    function init(ta) {
        var wrap = document.createElement('div');
        wrap.className = 'hw';
        wrap.innerHTML =
            '<div class="hw-bar"><div class="hw-tabs">' +
            '<button type="button" data-mode="visual" class="on">Visual</button><button type="button" data-mode="html">HTML</button></div>' +
            '<div class="hw-tools"></div></div>' +
            '<div class="hw-visual cm-body" contenteditable="true" spellcheck="true"></div>';
        ta.parentNode.insertBefore(wrap, ta);
        wrap.appendChild(ta);

        var visual = wrap.querySelector('.hw-visual'), tools = wrap.querySelector('.hw-tools'), mode = 'visual';
        var required = ta.hasAttribute('required');
        ta.removeAttribute('required');
        ta.classList.add('hw-html');
        ta.style.display = 'none';
        visual.innerHTML = ta.value;

        BUTTONS.forEach(function (b) {
            var btn = document.createElement('button');
            btn.type = 'button'; btn.dataset.cmd = b[0]; btn.textContent = b[1]; btn.title = b[2];
            tools.appendChild(btn);
        });

        function sync() { if (mode === 'visual') { ta.value = visual.innerHTML === '<br>' ? '' : visual.innerHTML; } }
        function setMode(m) {
            if (m === mode) { return; }
            if (m === 'html') { sync(); ta.style.display = 'block'; visual.style.display = 'none'; tools.style.visibility = 'hidden'; }
            else { visual.innerHTML = ta.value; ta.style.display = 'none'; visual.style.display = 'block'; tools.style.visibility = 'visible'; }
            mode = m;
            wrap.querySelectorAll('[data-mode]').forEach(function (t) { t.classList.toggle('on', t.dataset.mode === m); });
        }

        wrap.querySelector('.hw-tabs').addEventListener('click', function (e) { if (e.target.dataset.mode) { setMode(e.target.dataset.mode); } });
        tools.addEventListener('mousedown', function (e) { e.preventDefault(); });
        tools.addEventListener('click', function (e) {
            var c = e.target.dataset.cmd; if (!c || mode !== 'visual') { return; }
            visual.focus();
            if (c === 'h2' || c === 'h3' || c === 'p') { document.execCommand('formatBlock', false, c); }
            else if (c === 'quote') { document.execCommand('formatBlock', false, 'blockquote'); }
            else if (c === 'ul') { document.execCommand('insertUnorderedList'); }
            else if (c === 'ol') { document.execCommand('insertOrderedList'); }
            else if (c === 'clear') { document.execCommand('removeFormat'); document.execCommand('formatBlock', false, 'p'); }
            else if (c === 'link') { var u = prompt('Link URL (https://...)'); if (u && /^(https?:|mailto:|\/)/i.test(u)) { document.execCommand('createLink', false, u); } }
            else if (c === 'image') { var s = prompt('Image URL (https://... or /path)'); if (s && /^(https?:|\/)/i.test(s)) { document.execCommand('insertImage', false, s); } }
            else { document.execCommand(c); }
            sync();
        });
        visual.addEventListener('input', sync);
        visual.addEventListener('paste', function (e) {
            e.preventDefault();
            var t = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, t);
        });

        var form = ta.form;
        if (form) {
            form.addEventListener('submit', function (e) {
                sync();
                if (required && !ta.value.replace(/<[^>]*>|&nbsp;|\s/g, '').length && !/<img/i.test(ta.value)) {
                    e.preventDefault(); setMode('visual'); visual.focus(); wrap.classList.add('hw-invalid');
                }
            });
        }
    }
    document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('textarea[data-html-widget]').forEach(init); });
})();
