/* Small replacement for the Elementor / Unlimited Elements front-end scripts. */
(function () {
    'use strict';
    var ready = function (fn) { document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); };

    ready(function () {
        // Broken / not-yet-uploaded images fall back to a branded placeholder instead of a broken icon.
        document.querySelectorAll('img').forEach(function (img) {
            var fix = function () {
                if (img.dataset.fb) { return; }
                img.dataset.fb = '1';
                var logo = /Group-1000004294|logo/i.test(img.src) || (img.closest('.elementor-widget-theme-site-logo'));
                img.src = logo ? '/site/img/logo-placeholder.svg' : '/site/img/placeholder.svg';
            };
            img.addEventListener('error', fix);
            if (img.complete && img.naturalWidth === 0) { fix(); }
        });

        // "Personal" button: members go to the community, everyone else to the login page.
        var personal = document.getElementById('personal-btn');
        if (personal) {
            personal.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = document.body.classList.contains('logged-in') ? '/community' : '/login';
            });
        }

        // Elementor background lazy-load flag.
        document.querySelectorAll('.e-con.e-parent').forEach(function (el) { el.classList.add('e-lazyloaded'); });

        // Entrance animations (elementor-invisible + data-settings._animation).
        var invisible = document.querySelectorAll('.elementor-invisible');
        var show = function (el) {
            var s = {};
            try { s = JSON.parse(el.getAttribute('data-settings') || '{}'); } catch (e) {}
            var anim = s._animation || s.animation;
            var delay = s._animation_delay || s.animation_delay || 0;
            setTimeout(function () {
                el.classList.remove('elementor-invisible');
                if (anim) { el.classList.add('animated', anim); }
            }, delay);
        };
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) { if (en.isIntersecting) { show(en.target); io.unobserve(en.target); } });
            }, { threshold: 0.15 });
            invisible.forEach(function (el) { io.observe(el); });
        } else { invisible.forEach(show); }

        // Accordion (Disclaimer / User's manual).
        document.querySelectorAll('.uc_material_accordion .uc_trigger').forEach(function (t) {
            t.addEventListener('click', function () {
                var box = t.closest('.uc_ac_box');
                var wrap = t.closest('.uc_material_accordion');
                var open = box.classList.contains('uc_active_box') || box.classList.contains('active');
                if (wrap.getAttribute('data-closeothers') === 'true') {
                    wrap.querySelectorAll('.uc_ac_box').forEach(function (b) { b.classList.remove('uc_active_box', 'active'); b.querySelector('.uc_content') && (b.querySelector('.uc_content').style.display = 'none'); });
                }
                box.classList.toggle('uc_active_box', !open);
                box.classList.toggle('active', !open);
                var c = box.querySelector('.uc_content');
                if (c) { c.style.display = open ? 'none' : 'block'; }
            });
        });

        // Community: reveal inline reply forms.
        document.querySelectorAll('[data-reply-toggle]').forEach(function (b) {
            b.addEventListener('click', function () {
                var f = document.getElementById(b.getAttribute('data-reply-toggle'));
                if (f) { f.classList.toggle('open'); var t = f.querySelector('textarea'); t && t.focus(); }
            });
        });
        document.querySelectorAll('[data-confirm]').forEach(function (f) {
            f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) { e.preventDefault(); } });
        });
    });
})();

/* Professional page: degrees carousel (4 per view, dots, autoplay) + "Learn More" contact popup */
(function () {
    'use strict';
    var ready = function (fn) { document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn); };
    ready(function () {
        var wrap = document.querySelector('.elementor-image-carousel-wrapper');
        if (wrap) {
            var track = wrap.querySelector('.elementor-image-carousel');
            var slides = track.querySelectorAll('.swiper-slide');
            var dots = wrap.querySelector('.swiper-pagination');
            var idx = 0, timer;
            var perView = function () { return window.innerWidth < 1025 ? 2 : 4; };
            var pages = function () { return Math.max(1, slides.length - perView() + 1); };
            var go = function (i) {
                idx = (i + pages()) % pages();
                var w = slides[0].getBoundingClientRect().width + 20;
                track.style.transform = 'translateX(' + (-idx * w) + 'px)';
                dots.querySelectorAll('span').forEach(function (d, n) { d.classList.toggle('on', n === idx); });
            };
            var build = function () {
                dots.innerHTML = '';
                for (var n = 0; n < pages(); n++) { (function (n) { var s = document.createElement('span'); s.onclick = function () { go(n); }; dots.appendChild(s); })(n); }
                go(0);
            };
            build();
            window.addEventListener('resize', build);
            var start = function () { timer = setInterval(function () { go(idx + 1); }, 5000); };
            wrap.addEventListener('mouseenter', function () { clearInterval(timer); });
            wrap.addEventListener('mouseleave', start);
            start();
            // open certificates in a new tab instead of a missing lightbox
            wrap.querySelectorAll('a').forEach(function (a) { a.target = '_blank'; a.rel = 'noopener'; });
        }

        var pop = document.querySelector('.elementor-location-popup');
        if (pop) {
            var inner = pop.querySelector('.e-con-inner');
            var close = document.createElement('button');
            close.className = 'pop-close'; close.type = 'button'; close.setAttribute('aria-label', 'Close'); close.innerHTML = '&times;';
            inner.appendChild(close);
            var hide = function () { pop.classList.remove('open'); document.documentElement.style.overflow = ''; };
            close.addEventListener('click', hide);
            pop.addEventListener('click', function (e) { if (e.target === pop) { hide(); } });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { hide(); } });
            document.querySelectorAll('a[href*="action%3Apopup"], a[href*="action:popup"]').forEach(function (a) {
                a.addEventListener('click', function (e) { e.preventDefault(); pop.classList.add('open'); document.documentElement.style.overflow = 'hidden'; });
            });
        }
    });
})();
