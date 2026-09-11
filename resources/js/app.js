import './bootstrap';
import Alpine from 'alpinejs';
import L from 'leaflet';

window.Alpine = Alpine;
window.L = L;
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
            } else {
                // Re-arm element when scrolled out of view to re-animate on every scroll
                const rect = entry.target.getBoundingClientRect();
                if (rect.top > window.innerHeight * 0.92 || rect.bottom < -150) {
                    entry.target.classList.remove('is-revealed');
                }
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initScrollReveal();
        initScrollProgress();
    });
} else {
    initScrollReveal();
    initScrollProgress();
}


