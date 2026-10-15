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

        // Progress bar visual cue
        const progressBar = document.getElementById('scroll-progress-bar');
        if (progressBar) {
            progressBar.style.width = '75%';
        }

        // Safety fallback: auto-hide after 6s if navigation is slow or blocked
        timeoutId = setTimeout(() => {
            hideLoader();
        }, 6000);
    };

    const hideLoader = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
        loader.classList.remove('opacity-100', 'pointer-events-auto');
        loader.classList.add('opacity-0', 'pointer-events-none');
        loader.setAttribute('aria-hidden', 'true');

        const progressBar = document.getElementById('scroll-progress-bar');
        if (progressBar && progressBar.style.width === '75%') {
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 180);
        }
    };

    // 1. Initial page load finished -> smooth & instant fade out as soon as DOM is ready
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        hideLoader();
    } else {
        document.addEventListener('DOMContentLoaded', hideLoader, { once: true });
    }
    window.addEventListener('load', hideLoader, { once: true });

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

// Lightweight Instant Link Prefetch Engine (Speeds up internal page navigation)
function initLinkPrefetch() {
    const prefetched = new Set();
    const canPrefetch = 'connection' in navigator ? !navigator.connection.saveData : true;
    if (!canPrefetch) return;

    const prefetch = (href) => {
        if (prefetched.has(href)) return;
        prefetched.add(href);
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = href;
        link.as = 'document';
        document.head.appendChild(link);
    };

    document.addEventListener('pointerenter', (e) => {
        const link = e.target.closest('a');
        if (!link || !link.href) return;
        if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noPrefetch === 'true') return;

        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return;
        }

        try {
            const url = new URL(link.href, window.location.href);
            if (url.origin === window.location.origin && url.pathname !== window.location.pathname) {
                const lower = url.pathname.toLowerCase();
                const skip = ['logout', 'download', 'export', 'batch', 'scan', 'cetak', 'delete'];
                if (!skip.some(s => lower.includes(s))) {
                    prefetch(url.href);
                }
            }
        } catch (_) {}
    }, { passive: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initPageLoader();
        initLinkPrefetch();
        initScrollReveal();
        initScrollProgress();
        initScrollSpy();
    });
} else {
    initPageLoader();
    initLinkPrefetch();
    initScrollReveal();
    initScrollProgress();
    initScrollSpy();
}

// ID Card Cutout Engine (MediaPipe + pure mask algorithms)
import { FilesetResolver, ImageSegmenter } from '@mediapipe/tasks-vision';
import {
    applyThreshold,
    findLargestConnectedComponent,
    featherAlpha,
    calculateTransparencyRatio,
    compositeCutout
} from './idcard-mask.js';

let segmenterInstance = null;

async function getSegmenter() {
    if (!segmenterInstance) {
        try {
            const vision = await FilesetResolver.forVisionTasks('/vendor/mediapipe/wasm');
            segmenterInstance = await ImageSegmenter.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: '/models/selfie_segmenter.tflite',
                },
                runningMode: 'IMAGE',
                outputCategoryMask: false,
                outputConfidenceMasks: true,
            });
        } catch (err) {
            console.warn('Failed to initialize MediaPipe ImageSegmenter:', err);
            return null;
        }
    }
    return segmenterInstance;
}

async function generateCutout(canvas) {
    try {
        const segmenter = await getSegmenter();
        if (!segmenter) return null;

        const segmentationResult = await segmenter.segment(canvas);
        const confidenceMasks = segmentationResult.confidenceMasks;
        if (!confidenceMasks || confidenceMasks.length < 2) {
            return null;
        }

        const fgMask = confidenceMasks[1];
        const maskData = fgMask.getAsFloat32Array();
        const width = fgMask.width;
        const height = fgMask.height;

        const binaryMask = applyThreshold(maskData, 0.5);
        const cleanedMask = findLargestConnectedComponent(binaryMask, width, height);
        const alphaMask = featherAlpha(cleanedMask, width, height, 2);
        const transRatio = calculateTransparencyRatio(alphaMask, 15);

        if (transRatio < 0.05 || transRatio > 0.90) {
            console.warn(`Cutout transparency ratio ${transRatio.toFixed(3)} outside acceptable range (5%-90%)`);
            return null;
        }

        const cutoutCanvas = document.createElement('canvas');
        cutoutCanvas.width = canvas.width;
        cutoutCanvas.height = canvas.height;
        const ctx = cutoutCanvas.getContext('2d');
        ctx.drawImage(canvas, 0, 0);

        if (width === canvas.width && height === canvas.height) {
            const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            compositeCutout(imgData, alphaMask);
            ctx.putImageData(imgData, 0, 0);
        } else {
            const maskCanvas = document.createElement('canvas');
            maskCanvas.width = width;
            maskCanvas.height = height;
            const mCtx = maskCanvas.getContext('2d');
            const mImgData = mCtx.createImageData(width, height);
            for (let i = 0; i < alphaMask.length; i++) {
                mImgData.data[i * 4 + 0] = 0;
                mImgData.data[i * 4 + 1] = 0;
                mImgData.data[i * 4 + 2] = 0;
                mImgData.data[i * 4 + 3] = alphaMask[i];
            }
            mCtx.putImageData(mImgData, 0, 0);

            ctx.globalCompositeOperation = 'destination-in';
            ctx.drawImage(maskCanvas, 0, 0, canvas.width, canvas.height);
            ctx.globalCompositeOperation = 'source-over';
        }

        return cutoutCanvas.toDataURL('image/png');
    } catch (err) {
        console.warn('Cutout generation failed or unsupported in this browser:', err);
        return null;
    }
}

window.IdCardCutout = {
    getSegmenter,
    generateCutout,
    applyThreshold,
    findLargestConnectedComponent,
    featherAlpha,
    calculateTransparencyRatio,
    compositeCutout
};



