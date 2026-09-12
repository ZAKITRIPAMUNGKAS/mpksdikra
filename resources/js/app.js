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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initScrollReveal();
        initScrollProgress();
        initScrollSpy();
    });
} else {
    initScrollReveal();
    initScrollProgress();
    initScrollSpy();
}


