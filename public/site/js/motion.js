/* Motion layer (GSAP + ScrollTrigger) for the community, auth and admin screens.
   Public Elementor pages are intentionally untouched so the original design stays exact. */
(function () {
    'use strict';
    if (!window.gsap) { return; }
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    if (reduce) { return; }
    if (window.ScrollTrigger) { gsap.registerPlugin(ScrollTrigger); }

    function reveal(sel, opts) {
        var els = $$(sel);
        if (!els.length) { return; }
        gsap.set(els, { opacity: 0, y: 28 });
        var run = function (batch) { gsap.to(batch, Object.assign({ opacity: 1, y: 0, duration: 0.7, ease: 'power3.out', stagger: 0.09 }, opts || {})); };
        if (window.ScrollTrigger) {
            ScrollTrigger.batch(els, { start: 'top 92%', once: true, onEnter: run });
        } else { run(els); }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Page intro
        gsap.from('.cm-title, .ad-main > h1, .auth-wrap .elementor-heading-title', { opacity: 0, y: -18, duration: 0.7, ease: 'power3.out' });
        gsap.from('.cm-nav > *', { opacity: 0, y: -10, duration: 0.5, stagger: 0.06, delay: 0.1, ease: 'power2.out' });

        // Lists + panels
        reveal('.cm-card');
        reveal('.cm-panel, .cm-article');
        reveal('.cm-comment', { y: 16, stagger: 0.06 });
        reveal('.auth-card', { y: 36, duration: 0.9 });
        reveal('.auth-intro');
        reveal('.card, .stat');
        gsap.from('.auth-field', { opacity: 0, y: 14, duration: 0.5, stagger: 0.05, delay: 0.35, ease: 'power2.out', clearProps: 'all' });

        // Admin stat count-up
        $$('.stat b').forEach(function (el) {
            var end = parseInt(el.textContent, 10);
            if (isNaN(end)) { return; }
            var o = { v: 0 };
            gsap.to(o, { v: end, duration: 1.2, ease: 'power2.out', delay: 0.2, onUpdate: function () { el.textContent = Math.round(o.v); } });
        });

        // Hover lift on cards, press feel on buttons
        $$('.cm-card, .stat').forEach(function (c) {
            c.addEventListener('mouseenter', function () { gsap.to(c, { y: -4, duration: 0.25, ease: 'power2.out' }); });
            c.addEventListener('mouseleave', function () { gsap.to(c, { y: 0, duration: 0.3, ease: 'power2.out' }); });
        });
        $$('.cm-btn, .auth-btn, .btn').forEach(function (b) {
            b.addEventListener('mousedown', function () { gsap.to(b, { scale: 0.96, duration: 0.1 }); });
            ['mouseup', 'mouseleave'].forEach(function (ev) { b.addEventListener(ev, function () { gsap.to(b, { scale: 1, duration: 0.25, ease: 'back.out(2)' }); }); });
        });

        // Flash messages
        gsap.from('.cm-flash, .flash, .auth-alert, .cm-errors, .errors', { opacity: 0, y: -12, scale: 0.98, duration: 0.5, ease: 'back.out(1.6)' });

        // Soft parallax on card images
        if (window.ScrollTrigger) {
            $$('.cm-card img, .cm-hero').forEach(function (img) {
                gsap.fromTo(img, { scale: 1.08 }, { scale: 1, ease: 'none', scrollTrigger: { trigger: img, start: 'top bottom', end: 'bottom top', scrub: true } });
            });
        }
    });
})();
