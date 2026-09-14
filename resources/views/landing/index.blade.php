<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MPKSDI Karanganyar — Sistem Perkaderan Terpadu Baitul Arqam</title>
    <meta name="description" content="Pusat standardisasi pelatihan ideologis persyarikatan dan sistem pangkalan data kader resmi MPKSDI PDM Karanganyar.">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/logo-mpksdi.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        .leaflet-popup-content-wrapper {
            border-radius: 1rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e2e8f0 !important;
            padding: 2px !important;
        }
        .leaflet-popup-content {
            margin: 10px 14px !important;
            line-height: 1.4 !important;
        }
        .leaflet-popup-tip {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .mpksdi-map-marker {
            background: transparent !important;
            border: none !important;
        }
        .cabang-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .cabang-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .cabang-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .cabang-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="antialiased bg-white text-slate-800 selection:bg-sky-500 selection:text-white">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[1000] focus:px-4 focus:py-2 focus:bg-sky-600 focus:text-white focus:rounded-full focus:text-sm focus:font-bold">Lewati ke konten utama</a>

    {{-- Global Scroll Progress Indicator --}}
    <div id="scroll-progress-bar" class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-sky-500 via-cyan-400 to-[#f8bb00] z-[999] transition-[width] duration-75 ease-out pointer-events-none" style="width: 0%;"></div>

    {{-- SECTION 1: Clean Minimalist Navbar --}}
    <header class="sticky top-0 z-[100] bg-white/95 backdrop-blur-md border-b border-slate-100" 
            x-data="{ mobileOpen: false }"
            @click.outside="mobileOpen = false"
            @keydown.escape.window="mobileOpen = false">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            
            {{-- Logo Brand --}}
            <a href="{{ route('landing') }}" class="flex items-center gap-3 shrink-0 reveal-left reveal-delay-50">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 w-auto">
                <div class="flex flex-col">
                    <span class="font-extrabold text-lg tracking-tight text-slate-900 leading-none">MPKSDI<span class="text-sky-500">.</span></span>
                    <span class="text-xs text-slate-400 font-medium tracking-wide uppercase mt-0.5">PDM Karanganyar</span>
                </div>
            </a>

            {{-- Center Navigation Links (Minimalist with Dropdown) --}}
            <nav class="hidden lg:flex items-center gap-7 text-xs font-semibold text-slate-600 reveal-top reveal-delay-100">
                <a href="#beranda" class="text-sky-600 hover:text-sky-700 transition">Beranda</a>

                {{-- Dropdown Perkaderan --}}
                <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            :aria-expanded="open" 
                            aria-haspopup="true"
                            class="flex items-center gap-1.5 hover:text-sky-600 transition focus:outline-hidden cursor-pointer"
                            :class="open ? 'text-sky-600 font-bold' : ''">
                        <span>Perkaderan</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 pointer-events-none" :class="open ? 'rotate-180 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute top-full left-0 mt-0.5 w-64 rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/60 p-2 z-50 space-y-1">
                        
                        <a href="#program" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-sky-100/60 text-sky-600 flex items-center justify-center shrink-0 group-hover:bg-sky-500 group-hover:text-white transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Program BA</span>
                                <span class="block text-[10px] text-slate-400 font-normal">4 jenjang pelatihan Baitul Arqam</span>
                            </div>
                        </a>

                        <a href="#keunggulan" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-amber-100/60 text-amber-600 flex items-center justify-center shrink-0 group-hover:bg-[#f8bb00] group-hover:text-slate-950 transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Keunggulan Mutu</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Standar mutu & profil pimpinan</span>
                            </div>
                        </a>

                        <a href="#tahapan" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100/60 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Alur Peserta</span>
                                <span class="block text-[10px] text-slate-400 font-normal">4 alur pendaftaran s/d lulus</span>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Direct Link Peta 17 Cabang --}}
                <a href="#sebaran" class="hover:text-sky-600 transition flex items-center gap-1">
                    <span>Peta 17 Cabang</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                </a>

                {{-- Dropdown Pusat Informasi --}}
                <div class="relative py-2" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button" 
                            @click="open = !open" 
                            :aria-expanded="open" 
                            aria-haspopup="true"
                            class="flex items-center gap-1.5 hover:text-sky-600 transition focus:outline-hidden cursor-pointer"
                            :class="open ? 'text-sky-600 font-bold' : ''">
                        <span>Pusat Informasi</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 pointer-events-none" :class="open ? 'rotate-180 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute top-full left-0 mt-0.5 w-64 rounded-2xl bg-white border border-slate-100 shadow-xl shadow-slate-200/60 p-2 z-50 space-y-1">
                        
                        <a href="{{ route('cek-status') }}" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-sky-100/60 text-sky-600 flex items-center justify-center shrink-0 group-hover:bg-sky-500 group-hover:text-white transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Cek Status Pendaftaran</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Cek verifikasi pendaftaran mandiri</span>
                            </div>
                        </a>

                        <a href="#faq" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100/60 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-500 group-hover:text-white transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Tanya Jawab (FAQ)</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Informasi & pertanyaan umum BA</span>
                            </div>
                        </a>

                        <a href="#kontak" @click="open = false" class="group flex items-center gap-3 p-2 rounded-xl hover:bg-sky-50/60 transition">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100/60 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-sky-600 transition">Kontak & Sekretariat</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Helpdesk WhatsApp & Gedung PDM</span>
                            </div>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Right CTA Pill Buttons --}}
            <div class="hidden sm:flex items-center gap-2.5 reveal-right reveal-delay-150">
                @auth
                    <a href="{{ auth()->user()->isAdmin() || auth()->user()->isFasilitator() ? route('admin.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('portal.dashboard')) }}"
                       class="px-5 py-2 rounded-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 rounded-full border border-slate-300 text-slate-700 hover:border-slate-400 hover:bg-slate-50 text-xs font-semibold transition">
                        Masuk Portal
                    </a>
                    <a href="{{ route('anggota.register') }}" class="px-5 py-2 rounded-full bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold shadow-xs transition">
                        Daftar Kader
                    </a>
                @endauth
            </div>

            {{-- Mobile Hamburger Trigger --}}
            <button type="button" 
                    @click.stop="mobileOpen = !mobileOpen" 
                    :aria-expanded="mobileOpen ? 'true' : 'false'" 
                    aria-label="Buka menu navigasi" 
                    class="lg:hidden relative z-10 p-2.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 active:bg-slate-200 transition focus:outline-none focus:ring-2 focus:ring-sky-500/30 cursor-pointer select-none">
                <svg class="w-6 h-6 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          :d="mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'" />
                </svg>
            </button>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div x-show="mobileOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-slate-100 bg-white/98 backdrop-blur-xl px-5 py-4 space-y-3 text-xs font-semibold shadow-xl max-h-[calc(100vh-4.5rem)] overflow-y-auto">
            <a href="#beranda" @click="mobileOpen = false" class="block py-1.5 text-slate-700 hover:text-sky-600">Beranda</a>
            
            <div class="pt-2 pb-1 border-t border-slate-100">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">Perkaderan</span>
                <div class="space-y-1 pl-2 border-l-2 border-sky-100">
                    <a href="#program" @click="mobileOpen = false" class="block py-1 text-slate-600 hover:text-sky-600">Program BA</a>
                    <a href="#keunggulan" @click="mobileOpen = false" class="block py-1 text-slate-600 hover:text-sky-600">Keunggulan Mutu</a>
                    <a href="#tahapan" @click="mobileOpen = false" class="block py-1 text-slate-600 hover:text-sky-600">Alur Peserta</a>
                </div>
            </div>

            <a href="#sebaran" @click="mobileOpen = false" class="flex items-center justify-between py-1.5 text-slate-700 hover:text-sky-600">
                <span>Peta 17 Cabang PCM</span>
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
            </a>

            <div class="pt-2 pb-1 border-t border-slate-100">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-1">Pusat Informasi</span>
                <div class="space-y-1 pl-2 border-l-2 border-sky-100">
                    <a href="{{ route('cek-status') }}" @click="mobileOpen = false" class="block py-1 text-sky-600 font-bold">Cek Status Pendaftaran</a>
                    <a href="#faq" @click="mobileOpen = false" class="block py-1 text-slate-600 hover:text-sky-600">Tanya Jawab (FAQ)</a>
                    <a href="#kontak" @click="mobileOpen = false" class="block py-1 text-slate-600 hover:text-sky-600">Kontak & Sekretariat</a>
                </div>
            </div>

            <div class="pt-3 pb-6 border-t border-slate-100 flex gap-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() || auth()->user()->isFasilitator() ? route('admin.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('portal.dashboard')) }}"
                       class="flex-1 text-center py-2.5 rounded-full bg-slate-900 text-xs font-bold text-white shadow-xs">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex-1 text-center py-2 rounded-full border border-slate-300 text-xs font-semibold text-slate-700">Masuk</a>
                    <a href="{{ route('anggota.register') }}" class="flex-1 text-center py-2 rounded-full bg-sky-500 text-xs font-bold text-white">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="main-content" class="space-y-16 sm:space-y-20 pb-20">

        {{-- SECTION 2: Hero Banner Melengkung Proporsional dengan Foto Gedung Dakwah Siluet Kanan-ke-Kiri --}}
        <section id="beranda" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8">
            <div class="bg-[#f3f5f8] rounded-3xl p-5 sm:p-10 lg:p-14 relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-6 sm:gap-8 min-h-[340px] sm:min-h-[380px] reveal">
                
                {{-- Batik Solo Surya Pattern Corners --}}
                <x-batik-corner position="top-left" class="w-36 h-36 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.34] reveal-fade reveal-delay-100" />
                <x-batik-corner position="bottom-right" class="w-32 h-32 sm:w-48 sm:h-48 text-sky-600 opacity-[0.30] reveal-fade reveal-delay-150" />
                
                {{-- Background Visual: Foto Gedung Dakwah PDM Karanganyar dengan Siluet Fade dari Kanan ke Kiri --}}
                <div class="absolute right-0 top-0 bottom-0 w-full lg:w-7/12 h-full overflow-hidden pointer-events-none select-none z-0 reveal-right reveal-delay-200">
                    <img src="{{ asset('aset/gedung-dakwah-vector.jpg') }}"
                         alt="Gedung Dakwah PDM Karanganyar"
                         fetchpriority="high" decoding="async"
                         class="w-full h-full object-cover object-right">
                    {{-- Siluet Gradient: Kanan tajam, kiri memudar halus menyatu ke warna latar hero --}}
                    <div class="absolute inset-0 bg-gradient-to-r from-[#f3f5f8] via-[#f3f5f8]/80 via-35% to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-[#f3f5f8]/60 via-transparent to-transparent"></div>
                </div>

                {{-- Left Text Content --}}
                <div class="max-w-xl z-10 space-y-4 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/90 border border-slate-200/80 text-[11px] font-bold text-slate-700 shadow-2xs reveal reveal-delay-100">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="sm:hidden">Pangkalan Data Resmi MPKSDI</span>
                        <span class="hidden sm:inline">Pangkalan Data Resmi MPKSDI PDM Karanganyar</span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight drop-shadow-xs reveal reveal-delay-150">
                        Program Perkaderan <br class="hidden sm:inline">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 via-sky-700 to-slate-900">Baitul Arqam</span>
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed max-w-lg font-medium reveal reveal-delay-200">
                        Pusat standardisasi pelatihan ideologis persyarikatan dan sistem pangkalan data kader resmi MPKSDI PDM Karanganyar. Terintegrasi di 17 Cabang se-Kabupaten Karanganyar.
                    </p>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 reveal reveal-delay-250">
                        <a href="{{ route('anggota.register') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-full bg-sky-500 hover:bg-sky-600 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                            <span>Daftar sebagai Kader</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="#sebaran" class="w-full sm:w-auto px-5 py-2.5 rounded-full bg-white/95 hover:bg-white text-slate-800 font-semibold text-xs border border-slate-200 transition shadow-2xs flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Jelajahi Peta 17 Cabang</span>
                        </a>
                    </div>

                    {{-- Mobile Inline Micro-Chips (Ramping & Proporsional di Ponsel) --}}
                    <div class="lg:hidden pt-2 flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-700">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/85 border border-slate-200/80 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Gedung Dakwah PDM</span>
                        </div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/85 border border-slate-200/80 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>{{ number_format($totalKader) }} Kader (17 Cabang)</span>
                        </div>
                    </div>
                </div>

                {{-- Right Floating Info Badges di atas Foto Gedung (Tampil Khusus Layar Besar lg: ke atas) --}}
                <div class="hidden lg:flex relative w-full lg:w-[380px] flex-col justify-end items-start lg:items-end gap-3 z-10 self-center lg:self-end mt-4 lg:mt-0 reveal-right reveal-delay-300">
                    
                    {{-- Badge 1: Gedung Dakwah PDM --}}
                    <div class="group bg-white/95 backdrop-blur-md rounded-2xl p-3 sm:p-3.5 shadow-sm border border-white/80 flex items-center gap-3 card-lift-sm animate-card-float hover:border-sky-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-sky-500 text-white flex items-center justify-center font-bold shrink-0 shadow-2xs group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block leading-none">Pusat Dakwah &amp; Perkaderan</span>
                            <span class="text-xs font-black text-slate-900 leading-tight group-hover:text-sky-600 transition-colors">Gedung Dakwah PDM Karanganyar</span>
                        </div>
                    </div>

                    {{-- Badge 2: Pangkalan Data Terintegrasi --}}
                    <div class="group bg-white/95 backdrop-blur-md rounded-2xl p-3 sm:p-3.5 shadow-sm border border-white/80 flex items-center gap-3 card-lift-sm animate-card-float-delayed hover:border-amber-300 hover:shadow-md transition-all">
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0 shadow-2xs group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block leading-none">Pangkalan Data Terintegrasi</span>
                            <span class="text-xs font-black text-slate-900 leading-tight group-hover:text-amber-600 transition-colors">{{ number_format($totalKader) }} Kader di 17 Cabang</span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- SECTION 3: 8 Category Icons Horizontal Row (Disesuaikan Konteks Perkaderan Muhammadiyah) --}}
            <div class="mt-8 bg-slate-50/60 rounded-3xl p-4 sm:p-5 border border-slate-100 reveal">
                <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 sm:gap-4 text-center">
                    
                    {{-- 1. Ideologi (Kitab Suci Al-Quran / Rehal Terbuka) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-100 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 group-hover:bg-amber-100 border border-amber-200/60 flex items-center justify-center text-amber-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:rotate-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9h3M6 12h3M15 9h3M15 12h3"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-amber-700 transition-colors">Ideologi</span>
                    </div>

                    {{-- 2. Ke-Muhammadiyahan (Matahari Bersinar 12 Sinar - Lambang Persyarikatan) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-150 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 group-hover:bg-sky-100 border border-sky-200/60 flex items-center justify-center text-sky-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="4.2" stroke-width="1.8"/>
                                <circle cx="12" cy="12" r="1.6" fill="currentColor"/>
                                <path stroke-linecap="round" stroke-width="1.8" d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.93 4.93l1.8 1.8M17.27 17.27l1.8 1.8M4.93 19.07l1.8-1.8M17.27 6.73l1.8-1.8M7.2 3.2l1 2M15.8 18.8l1 2M3.2 16.8l2-1M18.8 8.2l2-1"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-sky-700 transition-colors">Ke-Muhammadiyahan</span>
                    </div>

                    {{-- 3. Kepemimpinan (Kompas Arah Kemajuan & Bintang Panduan) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-200 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 group-hover:bg-indigo-100 border border-indigo-200/60 flex items-center justify-center text-indigo-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:rotate-45 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                <polygon points="16 8 13.5 13.5 8 16 10.5 10.5 16 8" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.18"/>
                                <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-indigo-700 transition-colors">Kepemimpinan</span>
                    </div>

                    {{-- 4. Fikih Ibadah (Kubah & Mihrab Shalat Sesuai Tarjih) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-250 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 group-hover:bg-emerald-100 border border-emerald-200/60 flex items-center justify-center text-emerald-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 0C8.5 7.5 7 10 7 13v7h10v-7c0-3-1.5-5.5-5-8z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 20v-3.5a2 2 0 114 0V20"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 20h18"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 14v6M20 14v6"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-emerald-700 transition-colors">Fikih Ibadah</span>
                    </div>

                    {{-- 5. Ujian CBT (Layar Evaluasi Digital & Checklist Lembar Ujian) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-300 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-50 group-hover:bg-cyan-100 border border-cyan-200/60 flex items-center justify-center text-cyan-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="13" rx="2" stroke-width="1.8"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 20h8m-4-4v4"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8l1.8 1.8 3.2-3.2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 7.5h3M7 12h10"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-cyan-700 transition-colors">Ujian CBT</span>
                    </div>

                    {{-- 6. Psikomotor (Target Keterampilan Praktis & Simulasi Lapangan) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-350 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 group-hover:bg-teal-100 border border-teal-200/60 flex items-center justify-center text-teal-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                <circle cx="12" cy="12" r="5" stroke-width="1.8"/>
                                <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 9l5-5m0 0h-3.5m3.5 0V7.5"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-teal-700 transition-colors">Psikomotor</span>
                    </div>

                    {{-- 7. Keteladanan (Perisai Integritas & Bintang Moralitas Akhlak) --}}
                    <div class="group flex flex-col items-center reveal-scale reveal-delay-400 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 group-hover:bg-rose-100 border border-rose-200/60 flex items-center justify-center text-rose-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 2l7 3.5v6c0 5.25-3.5 10.15-7 11.5-3.5-1.35-7-6.25-7-11.5v-6L12 2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 7l1.2 2.5 2.8.4-2 2 .5 2.8-2.5-1.3-2.5 1.3.5-2.8-2-2 2.8-.4L12 7z" fill="currentColor" fill-opacity="0.22"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-rose-700 transition-colors">Keteladanan</span>
                    </div>

                    {{-- 8. 17 Cabang (Peta Spasial Berjejaring PCM se-Karanganyar) — scroll ke peta --}}
                    <a href="#sebaran" class="group flex flex-col items-center reveal-scale reveal-delay-450 p-1.5 rounded-2xl">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 group-hover:bg-blue-100 border border-blue-200/60 flex items-center justify-center text-blue-600 transition-all duration-300 group-hover:scale-110 group-hover:-translate-y-1 shadow-2xs group-hover:shadow-md">
                            <svg class="w-5 h-5 group-hover:rotate-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="8" y1="2" x2="8" y2="18" stroke-width="1.8" stroke-linecap="round"/>
                                <line x1="16" y1="6" x2="16" y2="22" stroke-width="1.8" stroke-linecap="round"/>
                                <circle cx="12" cy="11" r="2.2" stroke-width="1.8" fill="currentColor" fill-opacity="0.25"/>
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs font-semibold text-slate-700 mt-2 group-hover:text-blue-700 transition-colors">17 Cabang</span>
                    </a>

                </div>
            </div>

        </section>

        {{-- SECTION 4: Keunggulan Standar Mutu & Second Card (Tokoh H. Zainudin Ahpandi, S.E) --}}
        <section id="keunggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                
                {{-- Left: Text & 2x2 Feature Grid with Solid Blue Checkmarks --}}
                <div class="lg:col-span-7 space-y-6">
                    <div class="space-y-2.5">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-sky-50 text-sky-700 text-[11px] font-bold border border-sky-100 mb-1 reveal-pop reveal-delay-100">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                            </span>
                            <span>Standardisasi Mutu Terakreditasi</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight reveal-left reveal-delay-150">
                            Keunggulan Standar Mutu <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 via-[#005c8a] to-slate-900">Perkaderan MPKSDI.</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-2 max-w-lg leading-relaxed reveal-left reveal-delay-200">
                            Penyelenggaraan Baitul Arqam di PDM Karanganyar menerapkan tata kelola modern berbasis kompetensi ideologis dan integrasi digital persyarikatan.
                        </p>
                    </div>

                    {{-- 2x2 Grid Modern Cards with Dynamic Reveal & Interactive Micro-Animations --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        
                        {{-- 1: Kurikulum Baku PP --}}
                        <div class="group p-4 rounded-2xl bg-white border border-slate-200/80 card-lift-sm hover:border-sky-400 hover:shadow-xl hover:shadow-sky-500/10 transition-all duration-300 flex items-start gap-3.5 shadow-2xs reveal-pop reveal-delay-200">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-[#005c8a] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs feature-badge-icon">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 group-hover:translate-x-0.5 transition-all duration-200">Kurikulum Baku PP</h3>
                                    <span class="text-[9px] font-bold text-slate-400 group-hover:text-sky-500 transition-colors">01</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed group-hover:text-slate-700 transition-colors">
                                    Materi ideologi, manhaj tarjih, dan kepemimpinan selaras pedoman baku MPKSDI PP Muhammadiyah.
                                </p>
                            </div>
                        </div>

                        {{-- 2: Penilaian 4 Format --}}
                        <div class="group p-4 rounded-2xl bg-white border border-slate-200/80 card-lift-sm hover:border-sky-400 hover:shadow-xl hover:shadow-sky-500/10 transition-all duration-300 flex items-start gap-3.5 shadow-2xs reveal-pop reveal-delay-300">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-[#005c8a] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs feature-badge-icon">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 group-hover:translate-x-0.5 transition-all duration-200">Penilaian 4 Format</h3>
                                    <span class="text-[9px] font-bold text-slate-400 group-hover:text-sky-500 transition-colors">02</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed group-hover:text-slate-700 transition-colors">
                                    Evaluasi terpadu: Kognitif CBT (40%), Praktik Ibadah (35%), Afektif (25%), dan Presensi 100%.
                                </p>
                            </div>
                        </div>

                        {{-- 3: Instruktur Tersertifikasi --}}
                        <div class="group p-4 rounded-2xl bg-white border border-slate-200/80 card-lift-sm hover:border-sky-400 hover:shadow-xl hover:shadow-sky-500/10 transition-all duration-300 flex items-start gap-3.5 shadow-2xs reveal-pop reveal-delay-400">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-[#005c8a] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs feature-badge-icon">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 group-hover:translate-x-0.5 transition-all duration-200">Instruktur Tersertifikasi</h3>
                                    <span class="text-[9px] font-bold text-slate-400 group-hover:text-sky-500 transition-colors">03</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed group-hover:text-slate-700 transition-colors">
                                    Didampingi tim master of training dan instruktur ahli persyarikatan PDM Karanganyar.
                                </p>
                            </div>
                        </div>

                        {{-- 4: ID Card & E-Sertifikat QR --}}
                        <div class="group p-4 rounded-2xl bg-white border border-slate-200/80 card-lift-sm hover:border-sky-400 hover:shadow-xl hover:shadow-sky-500/10 transition-all duration-300 flex items-start gap-3.5 shadow-2xs reveal-pop reveal-delay-500">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-sky-500 to-[#005c8a] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs feature-badge-icon">
                                <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 group-hover:translate-x-0.5 transition-all duration-200">ID Card &amp; E-Sertifikat QR</h3>
                                    <span class="text-[9px] font-bold text-slate-400 group-hover:text-sky-500 transition-colors">04</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed group-hover:text-slate-700 transition-colors">
                                    Dokumen resmi digital dengan verifikasi QR Code instan terhubung database pimpinan.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Right: Visual Tokoh H. Zainudin Ahpandi, S.E (Elegan, Proporsional & Presisi) --}}
                <div class="lg:col-span-5 flex justify-center reveal-right reveal-delay-200">
                    <div class="relative w-full max-w-[340px] sm:max-w-[380px]">
                        
                        {{-- Subtle Soft Blue Layered Depth Shadow --}}
                        <div class="absolute inset-0 bg-sky-200/50 rounded-3xl translate-x-2.5 translate-y-2.5 -z-10 blur-[1px]"></div>
                        
                        {{-- Foreground Content Card with Portrait --}}
                        <div class="group bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-lg space-y-4 overflow-hidden relative card-lift hover:border-sky-300 transition-all duration-500">
                            
                            {{-- Batik Solo Surya Pattern Corners --}}
                            <x-batik-corner position="top-right" class="w-32 h-32 text-sky-800 opacity-[0.28]" />
                            <x-batik-corner position="bottom-left" class="w-28 h-28 text-sky-700 opacity-[0.25]" />
                            
                            {{-- Foto Tokoh H. Zainudin Ahpandi, S.E --}}
                            <div class="relative rounded-2xl overflow-hidden aspect-[4/5] bg-gradient-to-b from-sky-50 via-slate-50 to-slate-100 border border-slate-100 shadow-inner">
                                <img src="{{ asset('aset/ketua-mpksdi-vector.jpg') }}"
                                     alt="H. Zainudin Ahpandi, S.E - Ketua MPKSDI Karanganyar"
                                     loading="lazy" decoding="async"
                                     class="w-full h-full object-cover object-top group-hover:scale-104 transition duration-500">
                                
                                {{-- Top Role Tag --}}
                                <div class="absolute top-3.5 left-3.5 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white/95 text-slate-900 shadow-sm border border-slate-100 backdrop-blur-md animate-pulse-ring">
                                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                        <span>Pimpinan Majelis</span>
                                    </span>
                                </div>
                            </div>

                            {{-- Identity & Official Quote --}}
                            <div class="text-center space-y-2 pt-1">
                                <div>
                                    <h3 class="font-extrabold text-slate-900 text-lg sm:text-xl leading-tight group-hover:text-sky-700 transition-colors">H. Zainudin Ahpandi, S.E</h3>
                                    <p class="text-xs font-bold text-sky-600 uppercase tracking-wide mt-1">Ketua MPKSDI PDM Karanganyar</p>
                                </div>

                                {{-- Quotation Box --}}
                                <div class="bg-slate-50 group-hover:bg-sky-50/50 group-hover:border-sky-200/60 rounded-2xl p-3.5 border border-slate-100/90 text-left relative mt-2 transition-colors duration-300">
                                    <p class="text-xs text-slate-600 leading-relaxed italic text-center">
                                         &ldquo;Membina ideologi, meneguhkan komitmen, dan menggerakkan persyarikatan menuju kader berkemajuan.&rdquo;
                                    </p>
                                </div>

                                <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-slate-400 pt-0.5">
                                    <span>Periode 2023 &ndash; 2027</span>
                                    <span>&bull;</span>
                                    <span>PDM Karanganyar</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        {{-- SECTION 5: Program Perkaderan / Course Cards (Sesuai Wadah Biru Langit Upskill) --}}
        <section id="program" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#ebf4fa] rounded-3xl p-6 sm:p-10 lg:p-12 border border-sky-100 space-y-8 relative overflow-hidden reveal">
                
                {{-- Batik Solo Surya Pattern Corners --}}
                <x-batik-corner position="top-right" class="w-44 h-44 sm:w-64 sm:h-64 text-[#005c8a] opacity-[0.36] reveal-fade reveal-delay-100" />
                <x-batik-corner position="bottom-left" class="w-40 h-40 sm:w-56 sm:h-56 text-sky-700 opacity-[0.32] reveal-fade reveal-delay-150" />
                
                {{-- Title --}}
                <div class="text-center max-w-xl mx-auto space-y-1.5 reveal-top reveal-delay-100">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Program Perkaderan</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Pilihan jenjang pelatihan Baitul Arqam sesuai dengan amanah dan jenjang kepemimpinan.</p>
                </div>

                {{-- 4 Course Cards Grid with Distinctive Thematic Vector Illustrations --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    {{-- Card 1: BA Pimpinan (Cabang & Ranting) --}}
                    <div class="group bg-white rounded-3xl border border-slate-200/80 shadow-xs card-lift card-sheen hover:border-sky-400 hover:shadow-2xl hover:shadow-sky-500/15 transition-all duration-300 flex flex-col justify-between overflow-hidden reveal-scale reveal-delay-100">
                        <div>
                            {{-- Visual Header with Thematic Leadership & Syura Illustration --}}
                            <div class="h-44 sm:h-40 relative bg-gradient-to-br from-[#003d5c] via-[#005c8a] to-[#01b0f5] p-4 flex flex-col justify-between overflow-hidden">
                                {{-- Background Geometric Watermark --}}
                                <svg aria-hidden="true" class="absolute -right-8 -top-8 w-44 h-44 text-white/10 pointer-events-none group-hover:rotate-12 transition-transform duration-700 ease-out" viewBox="0 0 100 100" fill="currentColor">
                                    <polygon points="50,0 61,35 98,35 68,57 79,91 50,70 21,91 32,57 2,35 39,35" />
                                    <circle cx="50" cy="50" r="28" fill="none" stroke="currentColor" stroke-width="4" stroke-dasharray="3 3"/>
                                </svg>

                                {{-- Thematic Foreground Illustration: Musyawarah Pimpinan & Strategi Cabang --}}
                                <div aria-hidden="true" class="absolute right-2 bottom-2 w-32 h-28 pointer-events-none group-hover:scale-110 group-hover:-translate-y-2 transition-transform duration-500 ease-out">
                                    <svg viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-md">
                                        {{-- Sinar Matahari 12 Radiasi --}}
                                        <g opacity="0.4" stroke="#f8bb00" stroke-width="2">
                                            <line x1="130" y1="25" x2="130" y2="10" />
                                            <line x1="130" y1="25" x2="130" y2="40" />
                                            <line x1="130" y1="25" x2="115" y2="25" />
                                            <line x1="130" y1="25" x2="145" y2="25" />
                                            <line x1="130" y1="25" x2="120" y2="15" />
                                            <line x1="130" y1="25" x2="140" y2="35" />
                                            <line x1="130" y1="25" x2="140" y2="15" />
                                            <line x1="130" y1="25" x2="120" y2="35" />
                                        </g>
                                        <circle cx="130" cy="25" r="8" fill="#f8bb00" opacity="0.9" />

                                        {{-- Meja Konferensi / Syura Isometrik --}}
                                        <ellipse cx="80" cy="108" rx="65" ry="24" fill="#002b40" opacity="0.6"/>
                                        <ellipse cx="80" cy="104" rx="62" ry="22" fill="#024566" stroke="#38bdf8" stroke-width="1.5"/>

                                        {{-- Dokumen Blueprint & Grafik Strategis di Atas Meja --}}
                                        <rect x="62" y="93" width="22" height="15" rx="2" transform="rotate(-8 62 93)" fill="#ffffff" opacity="0.95"/>
                                        <line x1="65" y1="98" x2="77" y2="96" stroke="#005c8a" stroke-width="1.5" stroke-linecap="round"/>
                                        <line x1="66" y1="102" x2="75" y2="100.5" stroke="#005c8a" stroke-width="1.5" stroke-linecap="round"/>

                                        {{-- Mini Bar Chart Icon --}}
                                        <rect x="90" y="94" width="18" height="14" rx="2" transform="rotate(6 90 94)" fill="#0369a1" stroke="#bae6fd" stroke-width="1"/>
                                        <rect x="93" y="101" width="2.5" height="5" fill="#f8bb00"/>
                                        <rect x="97" y="98" width="2.5" height="8" fill="#38bdf8"/>
                                        <rect x="101" y="96" width="2.5" height="10" fill="#ffffff"/>

                                        {{-- Siluet 3 Tokoh Pemimpin Bermusyawarah (Peci & Batik) --}}
                                        {{-- Tokoh Kiri --}}
                                        <path d="M42 98C42 88 48 83 56 83C64 83 70 88 70 98" fill="#0f172a" opacity="0.85"/>
                                        <circle cx="56" cy="74" r="7" fill="#fbcfe8"/>
                                        <rect x="50" y="67" width="12" height="6" rx="2" fill="#020617"/>

                                        {{-- Tokoh Kanan --}}
                                        <path d="M90 98C90 88 96 83 104 83C112 83 118 88 118 98" fill="#0f172a" opacity="0.85"/>
                                        <circle cx="104" cy="74" r="7" fill="#fde68a"/>
                                        <rect x="98" y="67" width="12" height="6" rx="2" fill="#020617"/>

                                        {{-- Tokoh Tengah (Ketua Pimpinan - Lebih Tinggi & Berwibawa) --}}
                                        <path d="M66 94C66 82 73 76 80 76C87 76 94 82 94 94" fill="#ffffff" opacity="0.95"/>
                                        <circle cx="80" cy="65" r="8" fill="#fcd34d"/>
                                        <rect x="73" y="57" width="14" height="7" rx="2" fill="#0f172a"/>
                                        {{-- Detail Baju Batik Tengah --}}
                                        <line x1="80" y1="76" x2="80" y2="94" stroke="#0284c7" stroke-width="2"/>
                                    </svg>
                                </div>

                                {{-- Top Floating Tag with Glassmorphism --}}
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white backdrop-blur-md border border-white/25 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-300"></span>
                                        Tingkat Pimpinan
                                    </span>
                                </div>

                                {{-- Bottom Title Overlay --}}
                                <div class="relative z-10">
                                    <span class="text-xs text-sky-200 font-bold tracking-wide uppercase block">Cabang &amp; Ranting</span>
                                    <h3 class="font-extrabold text-sm sm:text-base leading-tight text-white drop-shadow-xs">Baitul Arqam Pimpinan</h3>
                                </div>

                                {{-- Dark Readability Gradient Overlay at Bottom --}}
                                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>
                            </div>

                            {{-- Body Content --}}
                            <div class="p-5 space-y-3">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-sky-700 transition">
                                        Penguatan Ideologi &amp; Manajerial Cabang
                                    </h4>
                                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span>Oleh: Korps Instruktur MPKSDI</span>
                                    </p>
                                </div>

                                {{-- Competency Chips --}}
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 text-xs font-semibold border border-sky-100">Manhaj Tarjih</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-xs font-medium border border-slate-100">Manajemen PCM</span>
                                </div>
                                
                                {{-- Rating & Duration --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <svg class="w-3.5 h-3.5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-extrabold text-slate-800 text-xs">{{ number_format($totalLulus) }} <span class="text-slate-500 font-semibold">Lulus Tersertifikasi</span></span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        32 JP
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="p-5 pt-0">
                            <a href="{{ route('anggota.register') }}" class="group/btn w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-sky-600 hover:text-white hover:border-sky-600 text-slate-700 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition duration-200 shadow-2xs hover:shadow-sm">
                                <span>Daftar Kepesertaan</span>
                                <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Card 2: BA Guru AUM (Pendidik & Sekolah) --}}
                    <div class="group bg-white rounded-3xl border border-slate-200/80 shadow-xs card-lift card-sheen hover:border-amber-400 hover:shadow-2xl hover:shadow-amber-500/15 transition-all duration-300 flex flex-col justify-between overflow-hidden reveal-scale reveal-delay-200">
                        <div>
                            {{-- Visual Header with Thematic Islamic Education Illustration --}}
                            <div class="h-44 sm:h-40 relative bg-gradient-to-br from-[#853704] via-[#d97706] to-[#f59e0b] p-4 flex flex-col justify-between overflow-hidden">
                                {{-- Background Octagram Islamic Geometric Watermark --}}
                                <svg aria-hidden="true" class="absolute -right-8 -top-8 w-44 h-44 text-white/10 pointer-events-none group-hover:rotate-45 transition-transform duration-700 ease-out" viewBox="0 0 100 100" fill="currentColor">
                                    <rect x="25" y="25" width="50" height="50" rx="4" />
                                    <rect x="25" y="25" width="50" height="50" rx="4" transform="rotate(45 50 50)" />
                                    <circle cx="50" cy="50" r="18" fill="none" stroke="currentColor" stroke-width="3"/>
                                </svg>

                                {{-- Thematic Foreground Illustration: Kitab Al-Qur'an Rehal, Toga & Lentera Ilmu --}}
                                <div aria-hidden="true" class="absolute right-2 bottom-2 w-32 h-28 pointer-events-none group-hover:scale-110 group-hover:-translate-y-2 transition-transform duration-500 ease-out">
                                    <svg viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-md">
                                        {{-- Cahaya Pencerahan / Aura Kuning --}}
                                        <circle cx="85" cy="85" r="45" fill="#fef08a" opacity="0.25"/>

                                        {{-- Meja / Rehal Kayu Silang Terbuka --}}
                                        <path d="M48 114L85 92L122 114" stroke="#78350f" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M48 92L85 114L122 92" stroke="#92400e" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>

                                        {{-- Kitab Suci Al-Qur'an Terbuka Berkilau --}}
                                        <path d="M50 94C66 84 80 87 85 92C90 87 104 84 120 94L117 76C102 67 89 70 85 75C81 70 68 67 53 76L50 94Z" fill="#ffffff" stroke="#fcd34d" stroke-width="2"/>
                                        {{-- Lembar Kaligrafi Mushaf Emas --}}
                                        <line x1="58" y1="80" x2="78" y2="76" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                                        <line x1="57" y1="85" x2="77" y2="81" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                                        <line x1="92" y1="76" x2="112" y2="80" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                                        <line x1="93" y1="81" x2="113" y2="85" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                                        <circle cx="85" cy="82" r="3" fill="#f59e0b"/>

                                        {{-- Topi Toga Wisuda / Morartboard Akademik --}}
                                        <g transform="translate(92, 28)">
                                            <polygon points="25,5 45,15 25,25 5,15" fill="#1e293b"/>
                                            <polygon points="25,6 42,15 25,23 8,15" stroke="#fcd34d" stroke-width="1"/>
                                            <path d="M14 20V32C14 36 36 36 36 32V20" fill="#0f172a"/>
                                            {{-- Tali Toga Kuning Emas --}}
                                            <path d="M25 15C32 20 34 26 34 33" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/>
                                            <circle cx="34" cy="34" r="2" fill="#d97706"/>
                                        </g>

                                        {{-- Bintang & Kilau Inspirasi Pendidik --}}
                                        <path d="M42 45L44 51L50 53L44 55L42 61L40 55L34 53L40 51Z" fill="#fef08a"/>
                                        <path d="M72 32L73 36L77 37L73 38L72 42L71 38L67 37L71 36Z" fill="#ffffff"/>
                                    </svg>
                                </div>

                                {{-- Top Floating Tag with Glassmorphism --}}
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white backdrop-blur-md border border-white/25 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-200"></span>
                                        Pendidik &amp; AUM
                                    </span>
                                </div>

                                {{-- Bottom Title Overlay --}}
                                <div class="relative z-10">
                                    <span class="text-xs text-amber-100 font-bold tracking-wide uppercase block">Guru SD / SMP / SMA</span>
                                    <h3 class="font-extrabold text-sm sm:text-base leading-tight text-white drop-shadow-xs">Baitul Arqam Guru AUM</h3>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>
                            </div>

                            {{-- Body Content --}}
                            <div class="p-5 space-y-3">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-amber-600 transition">
                                        Internalisasi Nilai ISMUBA di Sekolah
                                    </h4>
                                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        <span>Oleh: MPKSDI &amp; Dikdasmen</span>
                                    </p>
                                </div>

                                {{-- Competency Chips --}}
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 text-xs font-semibold border border-amber-100">Filosofi ISMUBA</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-xs font-medium border border-slate-100">Etika Pendidik</span>
                                </div>
                                
                                {{-- Rating & Duration --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-extrabold text-slate-800 text-xs">{{ number_format($totalAktif) }} <span class="text-slate-500 font-semibold">Kader Aktif</span></span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        28 JP
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="p-5 pt-0">
                            <a href="{{ route('anggota.register') }}" class="group/btn w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-amber-600 hover:text-white hover:border-amber-600 text-slate-700 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition duration-200 shadow-2xs hover:shadow-sm">
                                <span>Daftar Kepesertaan</span>
                                <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Card 3: BA Tenaga Medis PKU (Kesehatan PKU Muhammadiyah) --}}
                    <div class="group bg-white rounded-3xl border border-slate-200/80 shadow-xs card-lift card-sheen hover:border-teal-400 hover:shadow-2xl hover:shadow-teal-500/15 transition-all duration-300 flex flex-col justify-between overflow-hidden reveal-scale reveal-delay-300">
                        <div>
                            {{-- Visual Header with Thematic Healthcare & PKU Crescent Illustration --}}
                            <div class="h-44 sm:h-40 relative bg-gradient-to-br from-[#064e3b] via-[#0d9488] to-[#10b981] p-4 flex flex-col justify-between overflow-hidden">
                                {{-- Background Medical Cross Geometric Pattern --}}
                                <svg aria-hidden="true" class="absolute -right-8 -top-8 w-44 h-44 text-white/10 pointer-events-none group-hover:scale-110 transition-transform duration-700 ease-out" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="M38 15H62V38H85V62H62V85H38V62H15V38H38V15Z" />
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="4 4"/>
                                </svg>

                                {{-- Thematic Foreground Illustration: Bulan Sabit PKU, Stetoskop Hati & EKG --}}
                                <div aria-hidden="true" class="absolute right-2 bottom-2 w-32 h-28 pointer-events-none group-hover:scale-110 group-hover:-translate-y-2 transition-transform duration-500 ease-out">
                                    <svg viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-md">
                                        {{-- Garis Detak Jantung EKG Berkilau --}}
                                        <path d="M10 110L45 110L52 95L60 125L68 85L76 118L84 106L90 110L150 110" stroke="#a7f3d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>

                                        {{-- Bulan Sabit Hijau Emas PKU Muhammadiyah --}}
                                        <path d="M110 32C94 32 82 46 82 62C82 78 94 92 110 92C118 92 125 89 130 84C116 82 106 72 106 62C106 52 116 42 130 40C125 35 118 32 110 32Z" fill="#ffffff" stroke="#fcd34d" stroke-width="2"/>
                                        <circle cx="110" cy="62" r="5" fill="#f59e0b"/>

                                        {{-- Perisai Medis / MDMC Tanggap Kebencanaan --}}
                                        <g transform="translate(38, 48)">
                                            <path d="M22 6L40 12V28C40 40 22 48 22 48C22 48 4 40 4 28V12L22 6Z" fill="#042f2e" stroke="#34d399" stroke-width="2"/>
                                            {{-- Palang Putih Medis --}}
                                            <rect x="18" y="18" width="8" height="18" rx="1.5" fill="#ffffff"/>
                                            <rect x="13" y="23" width="18" height="8" rx="1.5" fill="#ffffff"/>
                                        </g>

                                        {{-- Stetoskop Modern Melingkar Lembut --}}
                                        <path d="M68 64C68 76 78 86 92 86C104 86 112 78 112 70" stroke="#f0fdf4" stroke-width="3" stroke-linecap="round"/>
                                        <circle cx="68" cy="62" r="4" fill="#a7f3d0" stroke="#ffffff" stroke-width="1.5"/>
                                        <circle cx="112" cy="68" r="6" fill="#38bdf8" stroke="#ffffff" stroke-width="1.5"/>

                                        {{-- Daun Penyembuh / Simbol Shifa --}}
                                        <path d="M136 78C142 74 146 66 142 60C136 62 132 70 136 78Z" fill="#86efac"/>
                                    </svg>
                                </div>

                                {{-- Top Floating Tag with Glassmorphism --}}
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white backdrop-blur-md border border-white/25 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-200"></span>
                                        Kesehatan PKU
                                    </span>
                                </div>

                                {{-- Bottom Title Overlay --}}
                                <div class="relative z-10">
                                    <span class="text-xs text-teal-100 font-bold tracking-wide uppercase block">RS PKU Muhammadiyah</span>
                                    <h3 class="font-extrabold text-sm sm:text-base leading-tight text-white drop-shadow-xs">Baitul Arqam Nakes</h3>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>
                            </div>

                            {{-- Body Content --}}
                            <div class="p-5 space-y-3">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-teal-600 transition">
                                        Pelayanan Islami &amp; Fikih Kebencanaan
                                    </h4>
                                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                        <span>Oleh: MPKU &amp; MPKSDI</span>
                                    </p>
                                </div>

                                {{-- Competency Chips --}}
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-teal-50 text-teal-800 text-xs font-semibold border border-teal-100">Fikih Pasien</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-xs font-medium border border-slate-100">Tanggap MDMC</span>
                                </div>
                                
                                {{-- Rating & Duration --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <svg class="w-3.5 h-3.5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-extrabold text-slate-800 text-xs">{{ number_format($totalEvent) }} <span class="text-slate-500 font-semibold">Event Terselenggara</span></span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        24 JP
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="p-5 pt-0">
                            <a href="{{ route('anggota.register') }}" class="group/btn w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-teal-600 hover:text-white hover:border-teal-600 text-slate-700 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition duration-200 shadow-2xs hover:shadow-sm">
                                <span>Daftar Kepesertaan</span>
                                <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Card 4: TOT Instruktur (Pelatihan Instruktur BA) --}}
                    <div class="group bg-white rounded-3xl border border-slate-200/80 shadow-xs card-lift card-sheen hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/15 transition-all duration-300 flex flex-col justify-between overflow-hidden reveal-scale reveal-delay-400">
                        <div>
                            {{-- Visual Header with Thematic Master Trainer Podium & Soundwave Illustration --}}
                            <div class="h-44 sm:h-40 relative bg-gradient-to-br from-[#2e1065] via-[#4f46e5] to-[#7c3aed] p-4 flex flex-col justify-between overflow-hidden">
                                {{-- Background Soundwave / Radar Geometric Watermark --}}
                                <svg aria-hidden="true" class="absolute -right-8 -top-8 w-44 h-44 text-white/10 pointer-events-none group-hover:scale-110 transition-transform duration-700 ease-out" viewBox="0 0 100 100" fill="none" stroke="currentColor">
                                    <circle cx="50" cy="50" r="15" stroke-width="2"/>
                                    <circle cx="50" cy="50" r="28" stroke-width="2" stroke-dasharray="4 4"/>
                                    <circle cx="50" cy="50" r="40" stroke-width="2"/>
                                    <circle cx="50" cy="50" r="52" stroke-width="2" stroke-dasharray="2 4"/>
                                </svg>

                                {{-- Thematic Foreground Illustration: Podium Fasilitator, Dual Mic & Medali Instruktur --}}
                                <div aria-hidden="true" class="absolute right-2 bottom-2 w-32 h-28 pointer-events-none group-hover:scale-110 group-hover:-translate-y-2 transition-transform duration-500 ease-out">
                                    <svg viewBox="0 0 160 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-md">
                                        {{-- Sorot Cahaya Spotlight Panggung --}}
                                        <polygon points="40,10 130,10 150,130 20,130" fill="#a5b4fc" opacity="0.15"/>

                                        {{-- Podium Pidato / Meja Fasilitator Modern --}}
                                        <path d="M65 80L95 80L90 120L70 120Z" fill="#1e1b4b" stroke="#818cf8" stroke-width="2"/>
                                        <rect x="58" y="74" width="44" height="8" rx="2" fill="#312e81" stroke="#c7d2fe" stroke-width="1.5"/>

                                        {{-- Mikrofon Ganda Goose-Neck Podium --}}
                                        <path d="M68 74C68 62 74 58 76 50" stroke="#e0e7ff" stroke-width="2" stroke-linecap="round"/>
                                        <ellipse cx="76" cy="48" rx="3" ry="4" fill="#f8bb00"/>
                                        <path d="M92 74C92 62 86 58 84 50" stroke="#e0e7ff" stroke-width="2" stroke-linecap="round"/>
                                        <ellipse cx="84" cy="48" rx="3" ry="4" fill="#f8bb00"/>

                                        {{-- Gelombang Suara Orasi / Fasilitasi Andragogi --}}
                                        <path d="M96 46C100 48 104 52 104 56" stroke="#c084fc" stroke-width="2" stroke-linecap="round" opacity="0.8"/>
                                        <path d="M102 42C108 45 114 51 114 58" stroke="#e879f9" stroke-width="2" stroke-linecap="round" opacity="0.7"/>

                                        {{-- Medali Kehormatan Master of Training Emas --}}
                                        <g transform="translate(110, 68)">
                                            {{-- Pita Medali Ungu Emas --}}
                                            <polygon points="8,0 16,16 0,16" fill="#f43f5e"/>
                                            <polygon points="16,0 24,16 8,16" fill="#e11d48"/>
                                            {{-- Koin Medali Emas Bintang --}}
                                            <circle cx="12" cy="24" r="11" fill="#f59e0b" stroke="#fef08a" stroke-width="2"/>
                                            <polygon points="12,18 14,22 18,23 15,26 16,30 12,28 8,30 9,26 6,23 10,22" fill="#ffffff"/>
                                        </g>

                                        {{-- Papan Flipchart Rubrik Asesmen di Kiri --}}
                                        <g transform="translate(24, 62)">
                                            <rect x="0" y="0" width="26" height="34" rx="2" fill="#ffffff" stroke="#cbd5e1" stroke-width="1.5"/>
                                            <rect x="4" y="5" width="18" height="3" rx="1" fill="#4f46e5"/>
                                            <line x1="4" y1="12" x2="16" y2="12" stroke="#64748b" stroke-width="1.5" stroke-linecap="round"/>
                                            <line x1="4" y1="17" x2="20" y2="17" stroke="#64748b" stroke-width="1.5" stroke-linecap="round"/>
                                            <line x1="4" y1="22" x2="14" y2="22" stroke="#64748b" stroke-width="1.5" stroke-linecap="round"/>
                                            <circle cx="20" cy="22" r="2" fill="#22c55e"/>
                                        </g>
                                    </svg>
                                </div>

                                {{-- Top Floating Tag with Glassmorphism --}}
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-white/20 text-white backdrop-blur-md border border-white/25 shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-200"></span>
                                        TOT Instruktur
                                    </span>
                                </div>

                                {{-- Bottom Title Overlay --}}
                                <div class="relative z-10">
                                    <span class="text-xs text-indigo-100 font-bold tracking-wide uppercase block">Korps Instruktur</span>
                                    <h3 class="font-extrabold text-sm sm:text-base leading-tight text-white drop-shadow-xs">Pelatihan Instruktur BA</h3>
                                </div>

                                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>
                            </div>

                            {{-- Body Content --}}
                            <div class="p-5 space-y-3">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug group-hover:text-indigo-600 transition">
                                        Metodologi Fasilitasi &amp; Asesmen
                                    </h4>
                                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/></svg>
                                        <span>Oleh: MPKSDI PWM &amp; PDM</span>
                                    </p>
                                </div>

                                {{-- Competency Chips --}}
                                <div class="flex flex-wrap gap-1.5 pt-0.5">
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-800 text-xs font-semibold border border-indigo-100">Andragogi Modern</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-600 text-xs font-medium border border-slate-100">Rubrik Asesmen</span>
                                </div>
                                
                                {{-- Rating & Duration --}}
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="font-extrabold text-slate-800 text-xs">{{ number_format($totalKader) }} <span class="text-slate-500 font-semibold">Kader Terdata</span></span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        40 JP
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="p-5 pt-0">
                            <a href="{{ route('anggota.register') }}" class="group/btn w-full py-2.5 px-4 rounded-xl border border-slate-200 bg-slate-50/80 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 text-slate-700 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition duration-200 shadow-2xs hover:shadow-sm">
                                <span>Daftar Kepesertaan</span>
                                <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- SECTION 6: Alur Peserta Perkaderan (Dengan Dashed Line Connector Sesuai Desain Modern) --}}
        <section id="tahapan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-xl mx-auto mb-12 space-y-1.5 reveal-top reveal-delay-100">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Alur Peserta Perkaderan</h2>
                <p class="text-xs sm:text-sm text-slate-500">Empat tahapan terstruktur dari pendaftaran hingga penetapan kelulusan resmi.</p>
            </div>

            <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                
                {{-- Decorative Connecting Line (Desktop Only) --}}
                <div class="hidden lg:block absolute top-9 left-[14%] right-[14%] h-0.5 border-t-2 border-dashed border-slate-200 z-0 pointer-events-none reveal-fade reveal-delay-200"></div>

                {{-- Step 1 --}}
                <div class="group relative z-10 text-center space-y-3 reveal-pop reveal-delay-100 card-lift-sm p-3 sm:p-4 rounded-3xl hover:bg-white hover:shadow-md hover:border hover:border-sky-100 transition-all duration-300">
                    <div class="w-18 h-18 mx-auto rounded-2xl bg-sky-50 border-2 border-white shadow-sm flex items-center justify-center text-sky-600 relative group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-sky-500 text-white font-extrabold text-[11px] flex items-center justify-center shadow-xs group-hover:scale-115 group-hover:rotate-6 transition-transform duration-300">1</span>
                        <svg class="w-8 h-8 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 transition-colors">Registrasi Mandiri</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Kader mengisi formulir biodata lengkap, riwayat organisasi, dan mengunggah pasfoto resmi.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="group relative z-10 text-center space-y-3 reveal-pop reveal-delay-200 card-lift-sm p-3 sm:p-4 rounded-3xl hover:bg-white hover:shadow-md hover:border hover:border-indigo-100 transition-all duration-300">
                    <div class="w-18 h-18 mx-auto rounded-2xl bg-indigo-50 border-2 border-white shadow-sm flex items-center justify-center text-indigo-600 relative group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-indigo-500 text-white font-extrabold text-[11px] flex items-center justify-center shadow-xs group-hover:scale-115 group-hover:rotate-6 transition-transform duration-300">2</span>
                        <svg class="w-8 h-8 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-indigo-600 transition-colors">Verifikasi PDM</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Admin MPKSDI memvalidasi NBM, data AUM, dan domisili cabang sebelum akun diaktifkan.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="group relative z-10 text-center space-y-3 reveal-pop reveal-delay-300 card-lift-sm p-3 sm:p-4 rounded-3xl hover:bg-white hover:shadow-md hover:border hover:border-amber-100 transition-all duration-300">
                    <div class="w-18 h-18 mx-auto rounded-2xl bg-amber-50 border-2 border-white shadow-sm flex items-center justify-center text-amber-600 relative group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-amber-500 text-white font-extrabold text-[11px] flex items-center justify-center shadow-xs group-hover:scale-115 group-hover:rotate-6 transition-transform duration-300">3</span>
                        <svg class="w-8 h-8 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-amber-600 transition-colors">Pelaksanaan BA</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Mengikuti materi terpadu, presensi QR Code tiap sesi, simulasi outbound, dan ujian CBT.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="group relative z-10 text-center space-y-3 reveal-pop reveal-delay-400 card-lift-sm p-3 sm:p-4 rounded-3xl hover:bg-white hover:shadow-md hover:border hover:border-emerald-100 transition-all duration-300">
                    <div class="w-18 h-18 mx-auto rounded-2xl bg-emerald-50 border-2 border-white shadow-sm flex items-center justify-center text-emerald-600 relative group-hover:scale-110 group-hover:shadow-md transition-all duration-300">
                        <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-emerald-500 text-white font-extrabold text-[11px] flex items-center justify-center shadow-xs group-hover:scale-115 group-hover:rotate-6 transition-transform duration-300">4</span>
                        <svg class="w-8 h-8 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-emerald-600 transition-colors">Sertifikat &amp; RTL</h3>
                    <p class="text-xs text-slate-500 leading-relaxed px-2">
                        Penetapan kelulusan, unduh e-sertifikat ber-QR Code, dan komitmen Rencana Tindak Lanjut.
                    </p>
                </div>

            </div>
        </section>

        {{-- SECTION 7: Display Sebaran 17 Cabang PCM dengan Peta Interaktif Leaflet --}}
        <section id="sebaran" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
                 x-data="petaCabang(@js($sebaranCabang), {{ (int)$totalKader }}, {{ (int)$maxKader }})"
                 x-init="initMap()">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-9 space-y-6 relative overflow-hidden reveal">
                
                {{-- Batik Solo Surya Pattern Corners --}}
                <x-batik-corner position="top-left" class="w-36 h-36 sm:w-56 sm:h-56 text-sky-900 opacity-[0.32] reveal-fade reveal-delay-100" />
                <x-batik-corner position="bottom-right" class="w-32 h-32 sm:w-48 sm:h-48 text-[#005c8a] opacity-[0.30] reveal-fade reveal-delay-150" />
                
                {{-- Header Section: Title, Stats Chips & Subtitle --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 border-b border-slate-100 pb-6">
                    <div class="space-y-1.5 reveal-left reveal-delay-100">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 text-sky-700 text-[11px] font-bold border border-sky-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Peta Spasial Perkaderan</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Peta Sebaran 17 Cabang Muhammadiyah</h2>
                        <p class="text-xs sm:text-sm text-slate-500 max-w-2xl leading-relaxed">
                            Visualisasi persebaran kader, basis pimpinan cabang (PCM), dan jangkauan dakwah persyarikatan di seluruh Kabupaten Karanganyar.
                        </p>
                    </div>

                    {{-- Quick Aggregate Chips --}}
                    <div class="flex flex-wrap items-center gap-2.5 reveal-right reveal-delay-150">
                        <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center gap-2 card-lift-sm">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span class="text-xs font-bold text-slate-800">17 Cabang</span>
                        </div>
                        <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-center gap-2 card-lift-sm">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-bold text-slate-800">{{ $totalRanting ?? 178 }} Ranting PRM</span>
                        </div>
                        <div class="px-4 py-2 rounded-2xl bg-sky-500 text-white flex items-center gap-2 shadow-xs card-lift-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span class="text-xs font-black">{{ number_format($totalKader) }} Kader</span>
                        </div>
                    </div>
                </div>

                {{-- Filter Zona & Search Bar Row --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                    {{-- Filter Tabs --}}
                    <div class="flex flex-wrap gap-1 p-1 bg-slate-100 rounded-full self-start reveal-left reveal-delay-200">
                        <button type="button" @click="setFilter('semua')"
                            :class="filterZona === 'semua' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition">
                            Semua (17)
                        </button>
                        <button type="button" @click="setFilter('Pusat & Barat')"
                            :class="filterZona === 'Pusat & Barat' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition">
                            Pusat &amp; Barat (6)
                        </button>
                        <button type="button" @click="setFilter('Lereng Lawu & Utara')"
                            :class="filterZona === 'Lereng Lawu & Utara' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition">
                            Lereng Lawu &amp; Timur (7)
                        </button>
                        <button type="button" @click="setFilter('Wilayah Selatan')"
                            :class="filterZona === 'Wilayah Selatan' ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition">
                            Selatan (4)
                        </button>
                    </div>

                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-72 reveal-right reveal-delay-200">
                        <input type="text"
                                x-model="searchQuery"
                                placeholder="Cari Cabang / Kecamatan..."
                                class="w-full pl-9 pr-4 py-2 rounded-full border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 bg-slate-50/50">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                {{-- Split View: Map Canvas (Left 7-8 cols) & Directory Panel (Right 4-5 cols) - Compact & Flush Bottom --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                    
                    {{-- Interactive Leaflet Map Container (Compact Height: 380px - 440px) --}}
                    <div class="lg:col-span-7 xl:col-span-8 flex flex-col h-full reveal-left reveal-delay-250">
                        <div class="bg-slate-100 rounded-3xl border border-slate-200 overflow-hidden relative isolate z-10 shadow-inner flex-1 min-h-[380px] sm:min-h-[440px]">
                            {{-- Map Canvas --}}
                            <div id="map-karanganyar" class="w-full h-full min-h-[380px] sm:min-h-[440px] z-0"></div>

                            {{-- Floating Action: Reset Map Focus / Fit Bounds --}}
                            <div class="absolute top-3 right-3 z-[400]">
                                <button type="button" 
                                        @click="resetMapView()"
                                        class="px-3 py-1.5 rounded-xl bg-white/95 backdrop-blur-xs text-slate-800 hover:bg-white text-xs font-bold shadow-md border border-slate-200 flex items-center gap-1.5 transition hover:border-sky-300">
                                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Fit Seluruh Peta</span>
                                </button>
                            </div>

                            {{-- Integrated GIS Map Dashboard Bottom Bar --}}
                            <div class="absolute bottom-0 inset-x-0 z-[400] bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-3.5 py-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-[11px] font-semibold text-slate-700 shadow-xs">
                                {{-- Left: Zona Wilayah Legend --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Zona:</span>
                                    <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200/70 text-xs">
                                        <span class="w-2 h-2 rounded-full bg-[#005c8a] border border-[#f8bb00] shrink-0"></span>
                                        <span>Pusat &amp; Barat <span class="text-slate-400 font-normal">(6)</span></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200/70 text-xs">
                                        <span class="w-2 h-2 rounded-full bg-[#0284c7] border border-[#f8bb00] shrink-0"></span>
                                        <span>Lereng Lawu <span class="text-slate-400 font-normal">(7)</span></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200/70 text-xs">
                                        <span class="w-2 h-2 rounded-full bg-[#d97706] border border-[#f8bb00] shrink-0"></span>
                                        <span>Selatan <span class="text-slate-400 font-normal">(4)</span></span>
                                    </div>
                                </div>

                                {{-- Right: GIS Attribution --}}
                                <div class="flex items-center gap-2 text-xs text-slate-400 shrink-0">
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-500">
                                        <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        GIS 17 PCM
                                    </span>
                                    <span class="text-slate-300">&bull;</span>
                                    <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="hover:text-sky-600 transition">
                                        &copy; OpenStreetMap
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Selected Cabang Card + Filtered Directory List (Compact & Exactly 5 Visible) --}}
                    <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-3.5 h-full reveal-right reveal-delay-250">
                        
                        {{-- Active / Selected Cabang Details Card (Compact) --}}
                        <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-4 shadow-sm space-y-2.5 relative overflow-hidden shrink-0 card-lift hover:border hover:border-sky-500/50 hover:shadow-xl transition-all duration-300"
                             x-show="selectedCabang">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-white/10 text-sky-300 border border-white/10 uppercase tracking-wider mb-0.5"
                                          x-text="selectedCabang?.zona"></span>
                                    <h3 class="text-base font-black text-white" x-text="'PCM ' + selectedCabang?.nama"></h3>
                                    <p class="text-[11px] text-slate-300 flex items-center gap-1 mt-0.5" x-text="selectedCabang?.alamat"></p>
                                </div>
                                <button type="button" @click="focusCabang(selectedCabang)" 
                                        class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition shrink-0" title="Zoom ke lokasi ini di peta">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-white/10">
                                <div class="bg-white/5 rounded-xl p-2">
                                    <span class="text-xs text-slate-400 uppercase font-bold block">Kader Terdaftar</span>
                                    <span class="text-lg font-extrabold text-white" x-text="selectedCabang?.total"></span>
                                    <span class="text-xs text-sky-400 block font-medium"
                                          x-text="totalKaderKabupaten > 0 ? (Math.round((selectedCabang?.total / totalKaderKabupaten) * 100) + '% dari total') : '0%'"></span>
                                </div>
                                <div class="bg-white/5 rounded-xl p-2">
                                    <span class="text-xs text-slate-400 uppercase font-bold block">Ranting PRM</span>
                                    <span class="text-lg font-extrabold text-amber-300" x-text="selectedCabang?.ranting"></span>
                                    <span class="text-xs text-slate-400 block font-medium">Binaan Cabang</span>
                                </div>
                            </div>

                            <div class="pt-0.5 flex items-center gap-2">
                                <a :href="'{{ route('anggota.register') }}?cabang=' + encodeURIComponent(selectedCabang?.key || '')"
                                   class="flex-1 py-1.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold text-center transition shadow-xs hover:scale-[1.02] active:scale-[0.98]">
                                    Daftar dari Cabang Ini
                                </a>
                                <button type="button" @click="focusCabang(selectedCabang)"
                                        class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition hover:scale-[1.02] active:scale-[0.98]">
                                    Fokus Peta
                                </button>
                            </div>
                        </div>

                        {{-- Scrollable List of 17 Cabang: Exactly 5 Items Visible Window with Scroll Down --}}
                        <div class="border border-slate-200/80 rounded-2xl overflow-hidden bg-white shadow-2xs flex-1 flex flex-col min-h-0">
                            {{-- List Header --}}
                            <div class="px-3.5 py-2.5 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between text-xs font-bold text-slate-700 shrink-0">
                                <div class="flex items-center gap-2">
                                    <span>Daftar Cabang</span>
                                    <span class="text-xs px-1.5 py-0.2 rounded-full bg-sky-100 text-sky-700 font-bold" x-text="filteredCabang.length"></span>
                                </div>
                                <span class="text-xs text-slate-400 font-normal">Klik untuk zoom di peta</span>
                            </div>
                            
                            {{-- Exactly 5 Items Visible Window (h-[235px]) with smooth scrolling --}}
                            <div class="divide-y divide-slate-100 overflow-y-auto h-[235px] max-h-[235px] cabang-scrollbar flex-1 min-h-0">
                                <template x-for="cabang in filteredCabang" :key="cabang.nama">
                                    <button type="button"
                                            @click="selectCabang(cabang)"
                                            :class="selectedCabang?.nama === cabang.nama ? 'bg-sky-50/90 border-l-4 border-l-sky-500' : 'hover:bg-slate-50 hover:pl-4.5'"
                                            class="w-full text-left px-3.5 py-2 flex items-center justify-between gap-3 transition-all duration-200">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <h4 class="font-bold text-slate-900 text-xs truncate" x-text="'PCM ' + cabang.nama"></h4>
                                                <span class="text-xs px-1.5 py-0.2 rounded font-semibold bg-slate-100 text-slate-500" x-text="cabang.zona.split(' ')[0]"></span>
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <div class="w-20 bg-slate-200 rounded-full h-1 overflow-hidden">
                                                    <div class="bg-sky-500 h-1 rounded-full"
                                                         :style="'width: ' + Math.max(6, Math.round((cabang.total / maxKaderCabang) * 100)) + '%'"></div>
                                                </div>
                                                <span class="text-xs text-slate-400" x-text="cabang.ranting + ' PRM'"></span>
                                            </div>
                                        </div>

                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-black text-slate-800" x-text="cabang.total"></span>
                                            <span class="text-xs text-slate-400 block leading-none">Kader</span>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            {{-- Footer Indicator for Scroll Down --}}
                            <div class="px-3.5 py-2 bg-slate-50/90 border-t border-slate-200/70 text-xs text-slate-500 flex items-center justify-between font-medium shrink-0">
                                <span class="text-slate-400">Menampilkan 5 dari <span class="font-bold text-slate-700" x-text="filteredCabang.length"></span> cabang</span>
                                <span class="inline-flex items-center gap-1 text-xs text-sky-600 font-bold bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200/60">
                                    <span>Scroll ke bawah</span>
                                    <svg class="w-3 h-3 text-sky-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        {{-- SECTION 8: FAQ (Redesain Tematik Selaras Wadah Program & Identitas MPKSDI) --}}
        <section id="faq" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ active: 1 }">
            <div class="bg-[#ebf4fa] rounded-3xl p-6 sm:p-10 lg:p-12 border border-sky-100 relative overflow-hidden reveal">
                {{-- Batik Solo Surya Pattern Corners --}}
                <x-batik-corner position="top-left" class="w-44 h-44 sm:w-64 sm:h-64 text-[#005c8a] opacity-[0.34] reveal-fade reveal-delay-100" />
                <x-batik-corner position="bottom-right" class="w-36 h-36 sm:w-52 sm:h-52 text-sky-600 opacity-[0.30] reveal-fade reveal-delay-150" />

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start relative z-10">
                    
                    {{-- Left Column: Section Heading & Helpdesk Contact Card --}}
                    <div class="lg:col-span-5 space-y-6">
                        <div class="reveal-left reveal-delay-100">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100/80 text-sky-800 text-[11px] font-bold border border-sky-200 mb-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                <span>Pusat Bantuan &amp; Informasi</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                                Pertanyaan Umum Seputar Perkaderan.
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-600 mt-2.5 leading-relaxed">
                                Temukan jawaban atas hal-hal yang sering ditanyakan mengenai keikutsertaan, standar penilaian, hingga penerbitan e-sertifikat resmi Baitul Arqam di Karanganyar.
                            </p>
                        </div>

                        {{-- Helpdesk Card --}}
                        <div class="group p-5 sm:p-6 rounded-2xl bg-white border border-sky-200/80 shadow-xs space-y-4 card-lift-sm hover:border-sky-300 hover:shadow-md transition-all duration-300 reveal-left reveal-delay-200">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#005c8a] to-[#01b0f5] text-white flex items-center justify-center font-bold text-sm shadow-xs shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-sky-700 transition-colors">Butuh Konsultasi Lanjutan?</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Sekretariat MPKSDI PDM Karanganyar</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Belum menemukan solusi yang Anda cari? Tim admin dan instruktur kami siap membantu verifikasi atau pertanyaan seputar perkaderan.
                            </p>
                            <a href="https://wa.me/6281226309576" target="_blank"
                               class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-[#005c8a] to-[#01b0f5] hover:from-[#004b73] hover:to-[#0284c7] text-white font-bold text-xs shadow-xs hover:shadow-md transition duration-200 hover:scale-[1.02] active:scale-[0.98]">
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.966.526 1.761.807 2.796.807 3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.767-5.768-5.767zm0 10.373c-.933 0-1.636-.263-2.428-.733l-.174-.103-1.782.467.476-1.737-.113-.181c-.53-.847-.809-1.579-.808-2.502.001-2.535 2.064-4.597 4.599-4.597 2.534 0 4.596 2.062 4.597 4.597 0 2.536-2.062 4.598-4.597 4.598zm5.736-12.355c-2.433-2.435-5.67-3.778-9.117-3.78-7.098 0-12.875 5.776-12.878 12.877-.001 2.27.6 4.484 1.739 6.459l-1.849 6.757 6.91-1.813c1.905 1.039 4.053 1.587 6.24 1.588h.005c7.097 0 12.875-5.776 12.878-12.878.001-3.441-1.34-6.678-3.774-9.11z"/></svg>
                                <span>Hubungi WhatsApp Helpdesk</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Interactive Accordion Cards --}}
                    <div class="lg:col-span-7 space-y-3">
                        
                        {{-- FAQ 1 --}}
                        <div class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden reveal-right reveal-delay-100 card-lift-sm"
                             :class="active === 1 ? 'border-sky-400 shadow-md ring-2 ring-sky-100' : 'border-slate-200/80 shadow-2xs hover:border-sky-300'">
                            <button type="button" @click="active = (active === 1 ? null : 1)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3.5 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center shrink-0 transition-colors duration-200"
                                          :class="active === 1 ? 'bg-sky-500 text-white shadow-2xs' : 'bg-sky-50 text-sky-700'">
                                        01
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug"
                                          :class="active === 1 ? 'text-sky-900' : ''">
                                        Siapa saja yang wajib mengikuti perkaderan Baitul Arqam?
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                                     :class="active === 1 ? 'bg-sky-100 text-sky-600 rotate-180' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div x-show="active === 1" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-xs sm:text-[13px] text-slate-600 leading-relaxed border-t border-slate-100/80 space-y-2">
                                <p>
                                    Baitul Arqam wajib diikuti oleh seluruh pimpinan persyarikatan di semua jenjang (PDM, 17 Cabang PCM, dan Ranting PRM), pimpinan Organisasi Otonom (Ortom), serta guru, dosen, tenaga medis, dan karyawan Amal Usaha Muhammadiyah (AUM) se-Kabupaten Karanganyar.
                                </p>
                                <p class="text-slate-500 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <span aria-hidden="true">💡</span> <strong>Catatan:</strong> Pelatihan ini merupakan prasyarat pembinaan ideologis, komitmen manhaj persyarikatan, serta standardisasi kepemimpinan berkemajuan.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 2 --}}
                        <div class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden reveal-right reveal-delay-180 card-lift-sm"
                             :class="active === 2 ? 'border-sky-400 shadow-md ring-2 ring-sky-100' : 'border-slate-200/80 shadow-2xs hover:border-sky-300'">
                            <button type="button" @click="active = (active === 2 ? null : 2)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3.5 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center shrink-0 transition-colors duration-200"
                                          :class="active === 2 ? 'bg-sky-500 text-white shadow-2xs' : 'bg-sky-50 text-sky-700'">
                                        02
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug"
                                          :class="active === 2 ? 'text-sky-900' : ''">
                                        Bagaimana kriteria penilaian dan penentuan kelulusan?
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                                     :class="active === 2 ? 'bg-sky-100 text-sky-600 rotate-180' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div x-show="active === 2" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-xs sm:text-[13px] text-slate-600 leading-relaxed border-t border-slate-100/80 space-y-2.5">
                                <p>
                                    Kelulusan peserta dinilai secara obyektif melalui 4 pilar mutu baku PP Muhammadiyah:
                                </p>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-sky-50/70 p-2.5 rounded-xl border border-sky-100">
                                        <span class="font-bold text-sky-900 block">Kognitif CBT (40%)</span>
                                        <span class="text-[11px] text-slate-500">Post-test pemahaman materi</span>
                                    </div>
                                    <div class="bg-emerald-50/70 p-2.5 rounded-xl border border-emerald-100">
                                        <span class="font-bold text-emerald-900 block">Praktik Ibadah (35%)</span>
                                        <span class="text-[11px] text-slate-500">Thaharah &amp; Shalat HPT</span>
                                    </div>
                                    <div class="bg-amber-50/70 p-2.5 rounded-xl border border-amber-100">
                                        <span class="font-bold text-amber-900 block">Afektif (25%)</span>
                                        <span class="text-[11px] text-slate-500">Kedisiplinan &amp; integritas</span>
                                    </div>
                                    <div class="bg-indigo-50/70 p-2.5 rounded-xl border border-indigo-100">
                                        <span class="font-bold text-indigo-900 block">Presensi (100%)</span>
                                        <span class="text-[11px] text-slate-500">Syarat mutlak kelulusan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FAQ 3 --}}
                        <div class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden reveal-right reveal-delay-260 card-lift-sm"
                             :class="active === 3 ? 'border-sky-400 shadow-md ring-2 ring-sky-100' : 'border-slate-200/80 shadow-2xs hover:border-sky-300'">
                            <button type="button" @click="active = (active === 3 ? null : 3)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3.5 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center shrink-0 transition-colors duration-200"
                                          :class="active === 3 ? 'bg-sky-500 text-white shadow-2xs' : 'bg-sky-50 text-sky-700'">
                                        03
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug"
                                          :class="active === 3 ? 'text-sky-900' : ''">
                                        Kapan E-Sertifikat resmi dan ID Card peserta dapat diunduh?
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                                     :class="active === 3 ? 'bg-sky-100 text-sky-600 rotate-180' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div x-show="active === 3" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-xs sm:text-[13px] text-slate-600 leading-relaxed border-t border-slate-100/80 space-y-2">
                                <p>
                                    <strong>ID Card Digital:</strong> Dapat langsung diunduh secara mandiri dalam format PDF siap cetak dengan QR Code absensi seketika setelah admin mendaftarkan (meng-enroll) Anda ke dalam jadwal event.
                                </p>
                                <p>
                                    <strong>E-Sertifikat Kelulusan:</strong> Otomatis tersedia di portal kader segera setelah rekapitulasi nilai akhir diputuskan dan dokumen Rencana Tindak Lanjut (RTL) disetujui instruktur pendamping.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 4 --}}
                        <div class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden reveal-right reveal-delay-340 card-lift-sm"
                             :class="active === 4 ? 'border-sky-400 shadow-md ring-2 ring-sky-100' : 'border-slate-200/80 shadow-2xs hover:border-sky-300'">
                            <button type="button" @click="active = (active === 4 ? null : 4)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3.5 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center shrink-0 transition-colors duration-200"
                                          :class="active === 4 ? 'bg-sky-500 text-white shadow-2xs' : 'bg-sky-50 text-sky-700'">
                                        04
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug"
                                          :class="active === 4 ? 'text-sky-900' : ''">
                                        Bagaimana jika status pendaftaran ditolak atau butuh revisi?
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                                     :class="active === 4 ? 'bg-sky-100 text-sky-600 rotate-180' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div x-show="active === 4" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-xs sm:text-[13px] text-slate-600 leading-relaxed border-t border-slate-100/80 space-y-2">
                                <p>
                                    Jika permohonan pendaftaran Anda memerlukan perbaikan (misalnya pasfoto belum berlatar formal atau penulisan NBM/NIK keliru), admin akan menyertakan catatan alasan revisi yang muncul di layar saat Anda login.
                                </p>
                                <p>
                                    Anda cukup mengunggah perbaikan data melalui form yang tersedia tanpa perlu membuat akun dari awal.
                                </p>
                            </div>
                        </div>

                        {{-- FAQ 5 --}}
                        <div class="bg-white rounded-2xl border transition-all duration-200 overflow-hidden reveal-right reveal-delay-420 card-lift-sm"
                             :class="active === 5 ? 'border-sky-400 shadow-md ring-2 ring-sky-100' : 'border-slate-200/80 shadow-2xs hover:border-sky-300'">
                            <button type="button" @click="active = (active === 5 ? null : 5)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-3.5 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl font-black text-xs flex items-center justify-center shrink-0 transition-colors duration-200"
                                          :class="active === 5 ? 'bg-sky-500 text-white shadow-2xs' : 'bg-sky-50 text-sky-700'">
                                        05
                                    </span>
                                    <span class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug"
                                          :class="active === 5 ? 'text-sky-900' : ''">
                                        Apakah kader yang belum memiliki NBM tetap dapat mendaftar?
                                    </span>
                                </div>
                                <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 transition-all duration-200"
                                     :class="active === 5 ? 'bg-sky-100 text-sky-600 rotate-180' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </button>
                            <div x-show="active === 5" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 pt-1 text-xs sm:text-[13px] text-slate-600 leading-relaxed border-t border-slate-100/80 space-y-2">
                                <p>
                                    <strong>Bisa.</strong> Kader atau pegawai baru AUM yang NBM resminya masih dalam proses pengurusan di Pimpinan Pusat tetap dapat mendaftarkan akun menggunakan 16 digit NIK KTP yang sah.
                                </p>
                                <p>
                                    Nomor Baku Muhammadiyah (NBM) dapat diperbarui kapan saja di menu profil portal anggota setelah kartu fisik atau nomor digital Anda resmi diterbitkan.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </section>

    </main>

    {{-- SECTION 9: Multi-Column Themed Footer --}}
    <footer id="kontak" class="bg-slate-50 border-t border-slate-200/80 pt-14 pb-20 lg:pb-10 text-slate-600 relative overflow-hidden reveal">
        {{-- Batik Solo Surya Pattern Corners --}}
        <x-batik-corner position="top-right" class="w-48 h-48 sm:w-64 sm:h-64 text-sky-900 opacity-[0.28] reveal-fade reveal-delay-100" />
        <x-batik-corner position="bottom-left" class="w-40 h-40 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.26] reveal-fade reveal-delay-150" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Main Themed Grid (12 Columns) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-10 pb-10 border-b border-slate-200/80">
                
                {{-- Col 1: Brand & Kontak Resmi (5 cols) --}}
                <div class="md:col-span-2 lg:col-span-5 space-y-4 reveal-left reveal-delay-100">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 w-auto">
                        <div class="flex flex-col">
                            <span class="font-extrabold text-xl text-slate-900 tracking-tight leading-none">MPKSDI<span class="text-sky-500">.</span></span>
                            <span class="text-xs text-slate-400 font-bold tracking-wider uppercase mt-1">PDM Kabupaten Karanganyar</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed max-w-md">
                        Majelis Pembinaan Kader dan Sumber Daya Insani Pimpinan Daerah Muhammadiyah Karanganyar. Mengawal perkaderan ideologis, standardisasi Baitul Arqam, dan digitalisasi data kader di Bumi Intanpari.
                    </p>

                    {{-- Contact Cards with Clean SVG Badges --}}
                    <div class="pt-1 flex flex-col sm:flex-row gap-2.5">
                        <a href="https://wa.me/6281226309576" target="_blank" rel="noopener noreferrer"
                           class="group inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 border border-slate-200/80 hover:border-emerald-300 text-xs text-slate-700 hover:text-emerald-700 transition-all duration-300 shadow-2xs card-lift-sm">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <span class="text-xs text-slate-400 uppercase font-bold block leading-none">WhatsApp</span>
                                <span class="font-bold text-slate-800 group-hover:text-emerald-700 text-xs">0812-2630-9576</span>
                            </div>
                        </a>

                        <a href="mailto:mpksdi@pdmkaranganyar.org"
                           class="group inline-flex items-center gap-2.5 px-3.5 py-2 rounded-xl bg-white hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 text-xs text-slate-700 hover:text-sky-700 transition-all duration-300 shadow-2xs card-lift-sm">
                            <div class="w-6 h-6 rounded-lg bg-[#005c8a] text-white flex items-center justify-center shrink-0 shadow-xs group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <span class="text-xs text-slate-400 uppercase font-bold block leading-none">Email</span>
                                <span class="font-bold text-slate-800 group-hover:text-sky-700 text-xs">mpksdi@pdmkaranganyar.org</span>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Col 2: Organisasi (2 cols) --}}
                <div class="lg:col-span-2 space-y-3 reveal-top reveal-delay-150">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-3 rounded-full bg-sky-500"></span>
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Organisasi</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-500 font-medium">
                        <li><a href="#beranda" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> Tentang MPKSDI</a></li>
                        <li><a href="#keunggulan" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> Visi &amp; Misi</a></li>
                        <li><a href="#sebaran" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> 17 Cabang PCM</a></li>
                        <li><a href="#kontak" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> Gedung Dakwah</a></li>
                    </ul>
                </div>

                {{-- Col 3: Perkaderan (2 cols) --}}
                <div class="lg:col-span-2 space-y-3 reveal-top reveal-delay-200">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-3 rounded-full bg-amber-400"></span>
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Perkaderan</h4>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-500 font-medium">
                        <li><a href="#program" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> BA Pimpinan Cabang</a></li>
                        <li><a href="#program" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> BA Guru AUM</a></li>
                        <li><a href="#program" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> BA Nakes PKU</a></li>
                        <li><a href="#program" class="hover:text-sky-600 hover:pl-1 transition-all flex items-center gap-1.5"><span class="text-slate-300">&rsaquo;</span> TOT Instruktur</a></li>
                    </ul>
                </div>

                {{-- Col 4: Sekretariat & Portal (3 cols) --}}
                <div class="lg:col-span-3 space-y-3 reveal-right reveal-delay-250">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-3 rounded-full bg-[#005c8a]"></span>
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Sekretariat</h4>
                    </div>
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs space-y-2.5 card-lift-sm hover:border-sky-300 hover:shadow-md transition-all duration-300">
                        <div class="flex items-start gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="leading-relaxed text-[11px] text-slate-500">
                                Gedung Dakwah Muhammadiyah Karanganyar, Jl. Lawu, Karanganyar, Jawa Tengah 57712.
                            </p>
                        </div>
                        <a href="{{ route('anggota.register') }}" 
                           class="flex items-center justify-center gap-2 w-full py-2 rounded-xl bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold transition shadow-xs hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span>Daftar Kader Sekarang</span>
                        </a>
                    </div>
                </div>

            </div>

            {{-- Sub-Footer: Clean, Integrated, Single-Row Responsive Layout --}}
            <div class="pt-6 flex flex-col lg:flex-row items-center justify-between gap-4 text-xs">
                {{-- Left: Copyright & Tagline --}}
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5 text-slate-500 text-center lg:text-left reveal-left reveal-delay-150">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <p class="font-semibold text-slate-700 whitespace-nowrap">&copy; {{ date('Y') }} MPKSDI PDM Karanganyar.</p>
                    </div>
                    <span class="hidden sm:inline text-slate-300">&bull;</span>
                    <span class="text-slate-400 text-[11px] whitespace-nowrap">Sistem Perkaderan Terpadu Baitul Arqam &bull; Bumi Intanpari</span>
                </div>

                {{-- Right: Policy Links, Developed by gemala.dev, and Back to Top --}}
                <div class="flex flex-wrap items-center justify-center gap-3.5 text-[11px] text-slate-500 font-medium reveal-right reveal-delay-200">
                    <a href="#beranda" class="hover:text-sky-600 transition">Kebijakan Privasi</a>
                    <span class="text-slate-300">&bull;</span>
                    <a href="#beranda" class="hover:text-sky-600 transition">Ketentuan Layanan</a>
                    <span class="text-slate-300">&bull;</span>
                    
                    {{-- Developed by gemala.dev (href to Instagram) --}}
                    <a href="https://instagram.com/gemala.dev" target="_blank" rel="noopener noreferrer"
                       class="group inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 transition-all text-slate-600 hover:text-sky-700 shadow-2xs">
                        <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Developed by</span>
                        <div class="flex items-center gap-1 font-bold text-[11px] text-slate-800 group-hover:text-sky-600 whitespace-nowrap">
                            <img src="{{ asset('aset/logo-gemaladev.png') }}" alt="Logo gemala.dev" class="h-3.5 w-3.5 object-contain group-hover:scale-110 transition-transform">
                            <span>gemala.dev</span>
                        </div>
                    </a>

                    {{-- Back to Top Button --}}
                    <button type="button" 
                            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" 
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white hover:bg-sky-50 text-slate-600 hover:text-sky-600 font-semibold transition border border-slate-200/80 shadow-2xs shrink-0 text-xs">
                        <span>Ke Atas</span>
                        <svg class="w-3 h-3 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    </button>
                </div>
            </div>

    </footer>

    {{-- Leaflet Map Initialization Script dengan Alpine.js Data Store --}}
    <script>
        function petaCabang(cabangList, totalKader, maxKader) {
            return {
                cabangList: cabangList || [],
                totalKaderKabupaten: totalKader || 0,
                maxKaderCabang: maxKader || 1,
                filterZona: 'semua',
                searchQuery: '',
                selectedCabang: cabangList && cabangList.length > 0 ? cabangList[0] : null,
                map: null,
                markers: {},

                get filteredCabang() {
                    return this.cabangList.filter(c => {
                        const matchZona = this.filterZona === 'semua' || c.zona === this.filterZona;
                        const matchQuery = this.searchQuery.trim() === '' || 
                            c.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                            c.zona.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            (c.alamat && c.alamat.toLowerCase().includes(this.searchQuery.toLowerCase()));
                        return matchZona && matchQuery;
                    });
                },

                initMap() {
                    this.$nextTick(() => {
                        const initLeaflet = () => {
                            if (typeof window.L === 'undefined') {
                                setTimeout(initLeaflet, 100);
                                return;
                            }

                            const mapEl = document.getElementById('map-karanganyar');
                            if (!mapEl || this.map) return;

                            // Inisialisasi Map (Attribution handled cleanly in our bottom bar)
                            this.map = window.L.map('map-karanganyar', {
                                scrollWheelZoom: false,
                                zoomControl: false,
                                attributionControl: false,
                            });

                            // Zoom Control di pojok kiri atas
                            window.L.control.zoom({ position: 'topleft' }).addTo(this.map);

                            // OpenStreetMap Resmi: 100% Bebas & Tanpa API Key
                            window.L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> kontributor',
                                maxZoom: 18,
                                minZoom: 9
                            }).addTo(this.map);

                            // Tambahkan marker kustom untuk 17 PCM
                            this.cabangList.forEach(cabang => {
                                const isPusat = cabang.zona === 'Pusat & Barat';
                                const isLawu = cabang.zona === 'Lereng Lawu & Utara';
                                const color = isPusat ? '#005c8a' : (isLawu ? '#0284c7' : '#d97706');

                                const iconHtml = `
                                    <div class="relative group cursor-pointer" style="transform: translate(-50%, -100%);">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-black text-xs shadow-md border-2 border-[#f8bb00]"
                                             style="background-color: ${color};">
                                            ${cabang.total}
                                        </div>
                                        <div class="w-2 h-2 mx-auto rotate-45 -mt-1 shadow-xs" style="background-color: ${color};"></div>
                                        <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 whitespace-nowrap bg-slate-900/95 text-white text-xs font-bold px-2 py-0.5 rounded-md shadow pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity z-50">
                                            PCM ${cabang.nama}
                                        </div>
                                    </div>
                                `;

                                const customIcon = window.L.divIcon({
                                    className: 'mpksdi-map-marker',
                                    html: iconHtml,
                                    iconSize: [32, 40],
                                    iconAnchor: [16, 36],
                                    popupAnchor: [0, -36]
                                });

                                const marker = window.L.marker([cabang.lat, cabang.lng], { icon: customIcon }).addTo(this.map);
                                
                                const popupContent = `
                                    <div class="p-1 min-w-[190px] font-sans">
                                        <div class="text-xs font-bold uppercase text-sky-600 tracking-wider">${cabang.zona}</div>
                                        <div class="font-extrabold text-sm text-slate-900 leading-tight">PCM ${cabang.nama}</div>
                                        <div class="text-[11px] text-slate-500 mt-1">${cabang.alamat}</div>
                                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                            <span class="font-black text-slate-800">${cabang.total} Kader Aktif</span>
                                            <span class="text-slate-400 font-medium">${cabang.ranting} PRM</span>
                                        </div>
                                    </div>
                                `;
                                marker.bindPopup(popupContent, { autoPan: false });

                                marker.on('click', () => {
                                    this.selectedCabang = cabang;
                                });

                                this.markers[cabang.nama] = marker;
                            });

                            // Fit Bounds ke seluruh 17 Cabang Karanganyar agar semua pin pas dalam layar
                            this.fitAllCabang();

                            // Re-render saat kanvas Leaflet telah terpasang penuh
                            setTimeout(() => {
                                if (this.map) {
                                    this.map.invalidateSize();
                                    this.fitAllCabang();
                                }
                            }, 350);
                        };

                        initLeaflet();
                    });
                },

                fitAllCabang() {
                    if (!this.map || !this.cabangList || this.cabangList.length === 0) return;
                    const allPoints = this.cabangList.map(c => [c.lat, c.lng]);
                    const bounds = window.L.latLngBounds(allPoints);
                    this.map.fitBounds(bounds, {
                        paddingTopLeft: [25, 25],
                        paddingBottomRight: [25, 45],
                        maxZoom: 12
                    });
                },

                selectCabang(cabang) {
                    this.selectedCabang = cabang;
                    this.focusCabang(cabang);
                },

                focusCabang(cabang) {
                    if (!this.map || !cabang) return;
                    this.map.flyTo([cabang.lat, cabang.lng], 13, { duration: 1.2 });
                    if (this.markers[cabang.nama]) {
                        this.markers[cabang.nama].openPopup();
                    }
                },

                setFilter(zona) {
                    this.filterZona = zona;
                    if (!this.map) return;

                    const visible = this.filteredCabang;
                    if (visible.length > 0) {
                        const group = visible.map(c => [c.lat, c.lng]);
                        this.map.fitBounds(window.L.latLngBounds(group), { padding: [40, 40], maxZoom: 13 });
                        this.selectedCabang = visible[0];
                    } else {
                        this.resetMapView();
                    }
                },

                resetMapView() {
                    if (!this.map) return;
                    this.filterZona = 'semua';
                    this.searchQuery = '';
                    this.fitAllCabang();
                    if (this.cabangList.length > 0) {
                        this.selectedCabang = this.cabangList[0];
                    }
                }
            };
        }
    </script>

    {{-- Floating Mobile Bottom Navigation Bar (Proporsional & Ergonomis) --}}
    <nav aria-label="Navigasi Bawah Seluler"
         x-data="{
             activeSection: 'beranda',
             init() {
                 const sections = ['beranda', 'program', 'sebaran'];
                 const onScroll = () => {
                     const scrollPos = window.scrollY + 220;
                     for (let i = sections.length - 1; i >= 0; i--) {
                         const s = sections[i];
                         const el = document.getElementById(s);
                         if (el && scrollPos >= el.offsetTop) {
                             this.activeSection = s;
                             return;
                         }
                     }
                     if (window.scrollY < 200) this.activeSection = 'beranda';
                 };
                 window.addEventListener('scroll', onScroll, { passive: true });
                 onScroll();
             }
          }"
         class="lg:hidden fixed bottom-4 left-1/2 -translate-x-1/2 w-[calc(100%-2rem)] max-w-[340px] sm:max-w-sm z-[100] bg-white/92 backdrop-blur-xl rounded-full border border-slate-200/80 shadow-[0_10px_28px_-6px_rgba(15,23,42,0.12),0_2px_8px_-2px_rgba(15,23,42,0.04)] p-1.5 transition-all">
        
        <div class="grid {{ auth()->check() ? 'grid-cols-4' : 'grid-cols-5' }} items-center gap-1">
            
            {{-- 1. Beranda --}}
            <a href="#beranda"
               @click="activeSection = 'beranda'"
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full transition-all duration-200 group"
               :class="activeSection === 'beranda' ? 'text-sky-600 font-bold bg-sky-50' : 'text-slate-500 hover:text-slate-800 font-medium'">
                <svg class="w-4.5 h-4.5 transition-transform group-active:scale-90" :class="activeSection === 'beranda' ? 'text-sky-600 stroke-[2.2]' : 'text-slate-500 stroke-[1.8]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] leading-none mt-1 tracking-tight">Beranda</span>
            </a>

            {{-- 2. Program --}}
            <a href="#program"
               @click="activeSection = 'program'"
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full transition-all duration-200 group"
               :class="activeSection === 'program' ? 'text-sky-600 font-bold bg-sky-50' : 'text-slate-500 hover:text-slate-800 font-medium'">
                <svg class="w-4.5 h-4.5 transition-transform group-active:scale-90" :class="activeSection === 'program' ? 'text-sky-600 stroke-[2.2]' : 'text-slate-500 stroke-[1.8]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span class="text-[10px] leading-none mt-1 tracking-tight">Program</span>
            </a>

            {{-- 3. Peta Cabang --}}
            <a href="#sebaran"
               @click="activeSection = 'sebaran'"
               class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full transition-all duration-200 group"
               :class="activeSection === 'sebaran' ? 'text-sky-600 font-bold bg-sky-50' : 'text-slate-500 hover:text-slate-800 font-medium'">
                <svg class="w-4.5 h-4.5 transition-transform group-active:scale-90" :class="activeSection === 'sebaran' ? 'text-sky-600 stroke-[2.2]' : 'text-slate-500 stroke-[1.8]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-[10px] leading-none mt-1 tracking-tight">Peta PCM</span>
            </a>

            @auth
                {{-- 4. Dashboard (Logged in - Harmonious Pill Accent) --}}
                <a href="{{ auth()->user()->isAdmin() || auth()->user()->isFasilitator() ? route('admin.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('portal.dashboard')) }}" 
                   class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full text-sky-700 bg-sky-100/70 hover:bg-sky-100 font-bold transition-all duration-200 group active:scale-95">
                    <svg class="w-4.5 h-4.5 text-sky-600 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span class="text-[10px] leading-none mt-1 tracking-tight">Dashboard</span>
                </a>
            @else
                {{-- 4. Masuk --}}
                <a href="{{ route('login') }}" 
                   class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-50 font-medium transition-all duration-200 group active:scale-95">
                    <svg class="w-4.5 h-4.5 text-slate-500 group-hover:text-slate-800 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-[10px] leading-none mt-1 tracking-tight">Masuk</span>
                </a>

                {{-- 5. Daftar (Primary Action - Harmonious Pill Accent) --}}
                <a href="{{ route('anggota.register') }}" 
                   class="flex flex-col items-center justify-center py-1.5 px-1 rounded-full text-sky-700 bg-sky-100/70 hover:bg-sky-100 font-bold transition-all duration-200 group active:scale-95">
                    <svg class="w-4.5 h-4.5 text-sky-600 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="text-[10px] leading-none mt-1 tracking-tight">Daftar</span>
                </a>
            @endauth

        </div>
    </nav>

</body>
</html>
