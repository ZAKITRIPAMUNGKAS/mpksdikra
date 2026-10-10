import './bootstrap';
import Alpine from 'alpinejs';
import L from 'leaflet';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.L = L;
window.Chart = Chart;
Alpine.start();

// Unified Dynamic Scroll Animation & Reveal Engine
function initScrollReveal() {
    const selector = '.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-pop, .reveal-top, .reveal-fade';
    const revealElements = document.querySelectorAll(selector);
    if (!revealElements.length) return;

    if (!('IntersectionObserver' in window)) {
        revealElements.forEach(el => el.classList.add('is-revealed'));
        return;
    }

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.04,
        rootMargin: '10px 0px -20px 0px'
    });

    revealElements.forEach(el => {
        revealObserver.observe(el);
    });
}

// Global Scroll Progress Bar & Dynamic Header Elevation
function initScrollProgress() {
    const progressBar = document.getElementById('scroll-progress-bar');
    const header = document.querySelector('header');

    window.addEventListener('scroll', () => {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        
        if (progressBar && docHeight > 0) {
            const progress = (scrollTop / docHeight) * 100;
            progressBar.style.width = `${progress}%`;
        }

        if (header) {
            if (scrollTop > 15) {
                header.classList.add('shadow-sm', 'bg-white/95');
                header.classList.remove('bg-white');
            } else {
                header.classList.remove('shadow-sm');
            }
        }
    }, { passive: true });
}

// Desktop nav scroll-spy: highlight section link as it enters view
function initScrollSpy() {
    const links = document.querySelectorAll('header nav a[href^="#"]');
    if (!links.length) return;

    const map = new Map();
    links.forEach(a => {
        const el = document.querySelector(a.getAttribute('href'));
        if (el) map.set(el, a);
    });
    if (!map.size) return;

    const activate = (a) => {
        links.forEach(l => {
            const active = l === a;
            l.classList.toggle('text-sky-600', active);
            l.classList.toggle('font-bold', active);
            l.setAttribute('aria-current', active ? 'true' : 'false');
        });
    };

    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) activate(map.get(e.target));
        });
    }, { rootMargin: '-30% 0px -60% 0px' });

    map.forEach((_, el) => obs.observe(el));
}

// Global Page Transition & Smooth Loading Screen Engine
function initPageLoader() {
    const loader = document.getElementById('page-loader');
    if (!loader) return;

    let timeoutId = null;

    const showLoader = () => {
        if (timeoutId) clearTimeout(timeoutId);
        loader.classList.remove('opacity-0', 'pointer-events-none');
        loader.classList.add('opacity-100', 'pointer-events-auto');
        loader.setAttribute('aria-hidden', 'false');

        // Safety fallback: auto-hide after 8s if navigation is slow or blocked
        timeoutId = setTimeout(() => {
            hideLoader();
        }, 8000);
    };

    const hideLoader = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
        loader.classList.remove('opacity-100', 'pointer-events-auto');
        loader.classList.add('opacity-0', 'pointer-events-none');
        loader.setAttribute('aria-hidden', 'true');
    };

    // 1. Initial page load finished -> smooth fade out
    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader, { once: true });
    }

    // 2. Handle browser Back/Forward Cache (bfcache)
    window.addEventListener('pageshow', () => {
        hideLoader();
    });

    // 3. Intercept normal link clicks for smooth page transition
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link || !link.href) return;

        // Skip modified clicks (new tab / new window)
        if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
            return;
        }

        // Skip target blank or download attribute
        if (link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        // Skip explicit opt-out
        if (link.classList.contains('no-loader') || link.dataset.noLoader === 'true') {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return;
        }

        // Parse target URL
        let url;
        try {
            url = new URL(link.href, window.location.href);
        } catch (_) {
            return;
        }

        // Only handle same-origin navigation
        if (url.origin !== window.location.origin) {
            return;
        }

        // Same page anchor link
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
            return;
        }

        // Skip file downloads based on file extension
        if (/\.(pdf|xlsx|xls|csv|zip|png|jpg|jpeg|docx)(\?.*)?$/i.test(url.pathname)) {
            return;
        }

        // Skip known download / export route segments
        const downloadSegments = ['download', 'export', 'batch', 'cetak-lembar', 'template-excel', 'berita-acara', 'outbound-pdf', 'report'];
        const pathnameLower = url.pathname.toLowerCase();
        if (downloadSegments.some(seg => pathnameLower.includes(seg))) {
            return;
        }

        // All checks passed -> trigger smooth circular loading screen
        showLoader();
    });

    // 4. Intercept standard form submissions
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form || e.defaultPrevented) return;

        if (form.target === '_blank' || form.classList.contains('no-loader') || form.dataset.noLoader === 'true') {
            return;
        }

        // Don't show loader for HTML5 dialog forms
        if (form.getAttribute('method') === 'dialog') {
            return;
        }

        showLoader();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initPageLoader();
        initScrollReveal();
        initScrollProgress();
        initScrollSpy();
    });
} else {
    initPageLoader();
    initScrollReveal();
    initScrollProgress();
    initScrollSpy();
}


