/* Fullscreen navigation menu: toggles .menu-opened on the widget root when the hamburger checkbox changes. */
(function () {
    'use strict';
    function init() {
        document.querySelectorAll('.checkbox-toggle').forEach(function (cb) {
            var root = cb.closest('div[id^="uc_fullscreen_navigation_menu"]');
            if (!root) { return; }
            var sync = function () {
                root.classList.toggle('menu-opened', cb.checked);
                document.documentElement.style.overflow = cb.checked ? 'hidden' : '';
            };
            cb.addEventListener('change', sync);
            root.querySelectorAll('.ue_menu a').forEach(function (a) {
                a.addEventListener('click', function () { cb.checked = false; sync(); });
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && cb.checked) { cb.checked = false; sync(); } });
        });
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
