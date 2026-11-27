<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Presentasi Evaluasi &amp; Yudisium – {{ $event->nama_event }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            background-color: #001c2b;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(14px) scale(0.985);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #ffffff;
            overflow-y: auto;
            z-index: 0;
            pointer-events: none;
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            z-index: 10;
            pointer-events: auto;
        }

        @media (prefers-reduced-motion: no-preference) {
            .slide {
                transition: opacity 0.38s cubic-bezier(0.16, 1, 0.3, 1),
                            transform 0.38s cubic-bezier(0.16, 1, 0.3, 1),
                            visibility 0.38s;
            }
            .anim-item {
                opacity: 0;
                transform: translateY(10px);
                transition: opacity 0.42s cubic-bezier(0.16, 1, 0.3, 1),
                            transform 0.42s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .slide.active .anim-item {
                opacity: 1;
                transform: translateY(0);
            }
            .d-1 { transition-delay: 0.06s; }
            .d-2 { transition-delay: 0.12s; }
            .d-3 { transition-delay: 0.18s; }
            .d-4 { transition-delay: 0.24s; }
        }

        .slide-bg-accent {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 96% 4%, rgba(0, 92, 138, 0.07), transparent 45%),
                radial-gradient(circle at 4% 96%, rgba(248, 187, 0, 0.05), transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        @media print {
            html, body {
                overflow: visible !important;
                height: auto !important;
                background: #ffffff !important;
            }
            #deck-shell {
                display: block !important;
                height: auto !important;
            }
            #presentation-stage {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }
            .slide {
                position: relative !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                pointer-events: auto !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                height: 100vh !important;
                width: 100% !important;
                overflow: hidden !important;
                box-shadow: none !important;
            }
            .slide:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
            .anim-item {
                opacity: 1 !important;
                transform: none !important;
            }
            #top-controls, #bottom-controls, #toc-drawer {
                display: none !important;
            }
        }
    </style>
</head>
<body x-data="livePresentation()" x-init="init()" @keydown.window="handleKeydown($event)">

    <x-page-loader />

    <div id="deck-shell" class="h-screen w-screen flex flex-col bg-[#001c2b]">

        {{-- TOP TOOLBAR --}}
        <header id="top-controls" x-show="!hideChrome" class="shrink-0 z-40 px-3 sm:px-5 py-2 flex items-center justify-between gap-3 bg-[#001724] border-b border-white/10 text-white">
            <div class="flex items-center gap-2.5 min-w-0">
                <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-100 transition shrink-0" title="Kembali ke Dashboard Event">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
                <div class="h-4 w-px bg-white/15 shrink-0"></div>
                <div class="min-w-0">
                    <h1 class="text-xs sm:text-sm font-bold text-white truncate">{{ $event->nama_event }}</h1>
                    <p class="text-[10px] text-[#f8bb00] font-medium truncate">Live PPT &bull; Laporan Evaluasi &amp; Yudisium Kader</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <button type="button" @click="toggleAutoPlay()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition" :class="isAutoPlay ? 'bg-[#f8bb00] text-slate-950 font-bold' : 'bg-white/10 hover:bg-white/20 text-slate-200'" title="Putar Otomatis tiap 8 detik">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="hidden md:inline" x-text="isAutoPlay ? 'Jeda' : 'Auto'"></span>
                </button>

                <button type="button" @click="toggleDrawer()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Daftar Slide (Tombol O)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    <span class="hidden md:inline">Daftar Slide</span>
                </button>

                <button type="button" @click="printPdf()" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Cetak / Simpan PDF (Tombol P)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span class="hidden md:inline">Cetak PDF</span>
                </button>

                <button type="button" @click="hideChrome = true" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Mode Bersih (Tombol H)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>

                <button type="button" @click="toggleFullscreen()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#f8bb00] hover:bg-amber-400 text-slate-950 font-bold text-xs transition shadow-xs" title="Layar Penuh (Tombol F)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    <span class="hidden sm:inline">Fullscreen</span>
                </button>
            </div>
        </header>

        {{-- Floating button saat chrome disembunyikan --}}
        <button type="button" x-show="hideChrome" @click="hideChrome = false" class="fixed top-3 right-3 z-50 px-3 py-1.5 rounded-full bg-slate-900/75 hover:bg-slate-900 text-white text-xs font-semibold backdrop-blur-md border border-white/15 shadow-lg transition">
            Tampilkan Kontrol (H)
        </button>

        {{-- STAGE 10 SLIDE --}}
        <main id="presentation-stage"
              class="flex-1 relative overflow-hidden"
              @touchstart="handleTouchStart($event)"
              @touchend="handleTouchEnd($event)">

            {{-- ============================================================== --}}
            {{-- SLIDE 1: COVER JUDUL PELATIHAN                                 --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-6 sm:px-12 sm:py-9" :class="{ 'active': currentSlide === 1 }" id="slide-1" style="background: linear-gradient(135deg, #001f30 0%, #003552 55%, #005c8a 100%); color: white;">
                <div class="relative z-10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-white p-1.5 shadow-lg flex items-center justify-center shrink-0">
                            <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="MPKSDI" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-widest font-black text-[#f8bb00] block">MPKSDI PDM Karanganyar</span>
                            <span class="text-xs text-slate-300">Majelis Pembinaan Kader &amp; Sumber Daya Insani</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline-flex px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-slate-100">
                        Sistem Pelatihan Terpadu Arqam
                    </span>
                </div>

                <div class="relative z-10 my-auto py-4 max-w-4xl anim-item d-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#f8bb00]/15 text-[#f8bb00] border border-[#f8bb00]/30 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-2 h-2 rounded-full bg-[#f8bb00]"></span>
                        Laporan Lengkap Evaluasi &amp; Yudisium
                    </div>
                    <h1 class="text-3xl sm:text-5xl lg:text-[3.25rem] font-black text-white leading-[1.12] tracking-tight">
                        {{ $event->nama_event }}
                    </h1>
                    <p class="text-sm sm:text-base text-slate-200 mt-4 leading-relaxed max-w-3xl">
                        Rekapitulasi menyeluruh capaian kader Muhammadiyah meliputi kehadiran sesi, kompetensi kognitif, praktik ibadah, sikap akhlakul karimah, keaktifan forum, ketuntasan RTL, dan evaluasi penyelenggaraan.
                    </p>
                </div>

                <div class="relative z-10 pt-4 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs anim-item d-2">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Waktu Pelaksanaan</span>
                        <span class="font-bold text-white mt-0.5 block">
                            @if($event->tanggal_mulai)
                                {{ $event->tanggal_mulai->translatedFormat('d M Y') }}@if($event->tanggal_selesai && $event->tanggal_selesai != $event->tanggal_mulai) &ndash; {{ $event->tanggal_selesai->translatedFormat('d M Y') }}@endif
                            @else
                                Belum dijadwalkan
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Lokasi</span>
                        <span class="font-bold text-white mt-0.5 block truncate">{{ $event->lokasi ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Peserta Aktif</span>
                        <span class="font-bold text-[#f8bb00] mt-0.5 block">{{ $totalPeserta }} Kader</span>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-slate-400 block text-[11px]">Status Kegiatan</span>
                        <span class="inline-block font-extrabold uppercase px-2.5 py-0.5 rounded-md text-[11px] mt-0.5 {{ $event->status === 'selesai' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-sky-400/20 text-sky-200 border border-sky-300/30' }}">
                            {{ $event->status }}
                        </span>
                    </div>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 2: RINGKASAN EKSEKUTIF & BOBOT PENILAIAN                 --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 2 }" id="slide-2">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian I &bull; Ikhtisar</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Ringkasan Eksekutif &amp; Skema Penilaian</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-sky-50 border border-sky-200 text-[#005c8a] text-xs font-bold shrink-0">
                        {{ $totalSesi }} Sesi &bull; {{ $totalPeserta }} Kader
                    </span>
                </div>

                <div class="relative z-10 my-auto py-3 space-y-5">
                    {{-- 6 KPI Utama --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 anim-item d-1">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90">
                            <span class="text-[11px] font-semibold text-slate-500 block">Peserta Aktif</span>
                            <span class="text-3xl font-black text-slate-900 mt-1 block">{{ $totalPeserta }}</span>
                            <span class="text-[11px] text-slate-500">{{ $lakiLaki }}L &bull; {{ $perempuan }}P</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-sky-50/70 border border-sky-200">
                            <span class="text-[11px] font-semibold text-sky-800 block">Rerata Kehadiran</span>
                            <span class="text-3xl font-black text-[#005c8a] mt-1 block">{{ $avgPresensiPersen }}%</span>
                            <span class="text-[11px] text-sky-700">{{ $hadirPenuhCount }} hadir 100%</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200">
                            <span class="text-[11px] font-semibold text-indigo-800 block">Rerata Nilai Akhir</span>
                            <span class="text-3xl font-black text-indigo-900 mt-1 block">{{ number_format($avgNilaiAkhir, 1) }}</span>
                            <span class="text-[11px] text-indigo-700">Maks {{ number_format($maxNilaiAkhir, 1) }}</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                            <span class="text-[11px] font-semibold text-emerald-800 block">Lulus Murni</span>
                            <span class="text-3xl font-black text-emerald-700 mt-1 block">{{ $lulusPersen }}%</span>
                            <span class="text-[11px] text-emerald-700">{{ $lulusCount }} dari {{ $totalPeserta }} kader</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                            <span class="text-[11px] font-semibold text-amber-800 block">Ketuntasan RTL</span>
                            <span class="text-3xl font-black text-amber-700 mt-1 block">{{ $rtlPersenTuntas }}%</span>
                            <span class="text-[11px] text-amber-700">{{ $rtlTuntas }} berkas tuntas</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-teal-50/70 border border-teal-200">
                            <span class="text-[11px] font-semibold text-teal-800 block">Kepuasan Angket</span>
                            <span class="text-3xl font-black text-teal-800 mt-1 block">{{ number_format($angketRerataTotal, 2) }}<span class="text-sm font-bold text-teal-600">/5</span></span>
                            <span class="text-[11px] text-teal-700">{{ $angketResponden }} responden</span>
                        </div>
                    </div>

                    {{-- Skema Bobot Komposisi Nilai --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 anim-item d-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Komposisi Bobot Nilai Akhir Kegiatan</h3>
                                <p class="text-xs text-slate-500">Acuan resmi kalkulasi yudisium &amp; peringkat kader</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-600">
                                RTL {{ $rtlSyaratKelulusan ? 'Wajib Syarat Lulus' : 'Opsional / Pendamping' }}
                            </span>
                        </div>

                        <div class="w-full h-4 rounded-full overflow-hidden flex bg-slate-200">
                            <div class="bg-[#005c8a] h-full" style="width: {{ round($bobot['kognitif'] * 100) }}%"></div>
                            <div class="bg-amber-500 h-full" style="width: {{ round($bobot['psikomotor'] * 100) }}%"></div>
                            <div class="bg-emerald-600 h-full" style="width: {{ round($bobot['afektif'] * 100) }}%"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 text-xs">
                            <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full bg-[#005c8a]"></span>Kognitif</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ round($bobot['posttest_dalam_kognitif'] * 100) }}% Posttest + {{ round($bobot['ngain_dalam_kognitif'] * 100) }}% N-Gain</span>
                                </div>
                                <span class="text-lg font-black text-[#005c8a]">{{ round($bobot['kognitif'] * 100) }}%</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Psikomotor &amp; Forum</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Praktik ibadah + poin keaktifan</span>
                                </div>
                                <span class="text-lg font-black text-amber-700">{{ round($bobot['psikomotor'] * 100) }}%</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between">
                                <div>
                                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>Sikap &amp; Akhlak</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">Penilaian perilaku &amp; komitmen kader</span>
                                </div>
                                <span class="text-lg font-black text-emerald-700">{{ round($bobot['afektif'] * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>MPKSDI PDM Karanganyar &bull; {{ $event->nama_event }}</span>
                    <span class="font-semibold" x-text="'Slide 2 / ' + totalSlides">Slide 2 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 3: DEMOGRAFI & PROFIL KADER PESERTA                      --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 3 }" id="slide-3">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian II &bull; Profil Kader</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Demografi, Sebaran PCM &amp; Latar Belakang</h2>
                    </div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700">Total {{ $totalPeserta }} Kader</span>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-5 my-auto py-3">
                    {{-- Gender & Pendidikan --}}
                    <div class="lg:col-span-4 space-y-4 anim-item d-1">
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-sky-50 to-slate-50 border border-sky-100">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Komposisi Gender</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                        <span>Laki-laki (Ikhwan)</span>
                                        <span class="text-[#005c8a]">{{ $lakiLaki }} ({{ $persenLaki }}%)</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-[#005c8a] h-2.5 rounded-full" style="width: {{ $persenLaki }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                        <span>Perempuan (Akhwat)</span>
                                        <span class="text-indigo-700">{{ $perempuan }} ({{ $persenPerempuan }}%)</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $persenPerempuan }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5">Pendidikan Terakhir</h3>
                            <div class="space-y-1.5">
                                @forelse($pendidikanList->take(4) as $edu => $cnt)
                                    <div class="flex items-center justify-between text-xs bg-white px-3 py-1.5 rounded-lg border border-slate-200/70">
                                        <span class="font-medium text-slate-700 truncate">{{ $edu }}</span>
                                        <span class="font-bold text-slate-900">{{ $cnt }}</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Belum ada data jenjang pendidikan.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Sebaran PCM & AUM --}}
                    <div class="lg:col-span-8 p-5 rounded-2xl bg-slate-50 border border-slate-200 anim-item d-2 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Sebaran Pimpinan Cabang (PCM) / Wilayah</h3>
                                <span class="text-[11px] font-semibold text-slate-500">{{ $cabangList->count() }} wilayah tercatat</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @forelse($cabangList->take(8) as $cabangName => $count)
                                    @php $pct = $totalPeserta > 0 ? round(($count / $totalPeserta) * 100) : 0; @endphp
                                    <div class="p-3 rounded-xl bg-white border border-slate-200/80">
                                        <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                            <span class="font-bold text-slate-800 truncate" title="{{ $cabangName }}">{{ $cabangName }}</span>
                                            <span class="font-black text-[#005c8a] shrink-0">{{ $count }} <span class="text-[10px] font-semibold text-slate-400">({{ $pct }}%)</span></span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-[#005c8a] h-1.5 rounded-full" style="width: {{ max(4, $pct) }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="sm:col-span-2 text-center py-6 text-slate-400 text-xs">Data domisili cabang belum tersedia.</div>
                                @endforelse
                            </div>

                            @if($cabangList->count() > 8)
                                <p class="text-[11px] text-slate-500 mt-2">
                                    + {{ $cabangList->skip(8)->sum() }} peserta lainnya dari {{ $cabangList->count() - 8 }} cabang/wilayah lain.
                                </p>
                            @endif
                        </div>

                        @if($aumList->isNotEmpty())
                            <div class="mt-4 pt-3 border-t border-slate-200/80">
                                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-1.5">Utusan AUM Terbanyak ({{ $aumList->count() }} Instansi)</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($aumList->take(5) as $aumName => $aumCount)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-[11px] font-semibold text-slate-700">
                                            <span class="truncate max-w-[180px]">{{ $aumName }}</span>
                                            <span class="text-[#005c8a] font-black">&times;{{ $aumCount }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Terintegrasi dengan Pangkalan Data Kader MPKSDI PDM Karanganyar</span>
                    <span class="font-semibold" x-text="'Slide 3 / ' + totalSlides">Slide 3 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 4: AGENDA SESI & KEDISIPLINAN PRESENSI                   --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 4 }" id="slide-4">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian III &bull; Kedisiplinan</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Realisasi Agenda Sesi &amp; Presensi Kader</h2>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="text-right hidden sm:block">
                            <span class="text-[11px] text-slate-500 block">Hadir Penuh 100%</span>
                            <span class="text-sm font-black text-emerald-700">{{ $hadirPenuhCount }} Kader</span>
                        </div>
                        <div class="px-3.5 py-1.5 rounded-xl bg-sky-50 border border-sky-200 text-right">
                            <span class="text-[10px] text-sky-700 font-semibold block">Rerata Presensi</span>
                            <span class="text-lg font-black text-[#005c8a] leading-none">{{ $avgPresensiPersen }}%</span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 my-auto py-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 anim-item d-1">
                        @forelse($sesiList->take(9) as $s)
                            @php $hadirPct = $totalPeserta > 0 ? round(($s->absensi_count / $totalPeserta) * 100) : 0; @endphp
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $s->jenis === 'materi' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $s->jenis }}
                                        </span>
                                        <span class="text-[11px] font-mono text-slate-500">{{ $s->jam_mulai ? $s->jam_mulai.' WIB' : 'Sesi #'.$s->urutan }}</span>
                                    </div>
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-1" title="{{ $s->nama_sesi }}">
                                        {{ $s->urutan }}. {{ $s->nama_sesi }}
                                    </h4>
                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                        {{ $s->pemateri ? 'Pemateri: '.$s->pemateri : 'Kegiatan / Agenda Bersama' }}
                                    </p>
                                </div>
                                <div class="mt-2.5 pt-2 border-t border-slate-200/70">
                                    <div class="flex justify-between text-[11px] mb-1">
                                        <span class="text-slate-600">Hadir: <b>{{ $s->absensi_count }}/{{ $totalPeserta }}</b></span>
                                        <span class="font-bold {{ $hadirPct >= 90 ? 'text-emerald-700' : 'text-[#005c8a]' }}">{{ $hadirPct }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="{{ $hadirPct >= 90 ? 'bg-emerald-500' : 'bg-[#005c8a]' }} h-1.5 rounded-full" style="width: {{ min(100, $hadirPct) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-10 text-slate-400 text-sm">
                                Belum ada jadwal sesi yang dicatat pada pelatihan ini.
                            </div>
                        @endforelse
                    </div>
                    @if($sesiList->count() > 9)
                        <p class="text-xs text-slate-500 mt-2.5 text-center">
                            Menampilkan 9 sesi pertama dari total <b>{{ $totalSesi }} sesi</b> ({{ $sesiMateriCount }} materi, {{ $sesiLainCount }} non-materi).
                        </p>
                    @endif
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Pemindaian Presensi QR Kamera &amp; Checklist Sesi Fasilitator</span>
                    <span class="font-semibold" x-text="'Slide 4 / ' + totalSlides">Slide 4 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 5: EVALUASI KOGNITIF (PRETEST, POSTTEST & N-GAIN)        --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 5 }" id="slide-5">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian IV &bull; Aspek Kognitif</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Capaian Pretest, Posttest &amp; Indeks N-Gain</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold shrink-0 {{ $peningkatanNilai >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        Delta Skor: {{ $peningkatanNilai >= 0 ? '+' : '' }}{{ $peningkatanNilai }} Poin
                    </span>
                </div>

                <div class="relative z-10 my-auto py-3 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 anim-item d-1">
                        {{-- Pretest --}}
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-bold text-slate-500 uppercase">
                                    <span>Rata-rata Pretest</span>
                                    <span>{{ $pretestPesertaCount }}/{{ $totalPeserta }} selesai</span>
                                </div>
                                <div class="my-4 flex items-baseline gap-2">
                                    <span class="text-5xl font-black text-slate-900">{{ number_format($avgPretest, 1) }}</span>
                                    <span class="text-xs font-semibold text-slate-500">/ 100 (Maks {{ $maxPretest }})</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-slate-600 h-2.5 rounded-full" style="width: {{ min(100, $avgPretest) }}%"></div>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 mt-4 leading-relaxed">Pemahaman awal materi ideologi kemuhammadiyahan, HPT, dan manhaj gerakan sebelum pelatihan.</p>
                        </div>

                        {{-- Posttest --}}
                        <div class="p-5 rounded-2xl bg-sky-50/80 border border-sky-200 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-bold text-[#005c8a] uppercase">
                                    <span>Rata-rata Posttest</span>
                                    <span>{{ $posttestPesertaCount }}/{{ $totalPeserta }} selesai</span>
                                </div>
                                <div class="my-4 flex items-baseline gap-2">
                                    <span class="text-5xl font-black text-[#004166]">{{ number_format($avgPosttest, 1) }}</span>
                                    <span class="text-xs font-semibold text-sky-700">/ 100 (Maks {{ $maxPosttest }})</span>
                                </div>
                                <div class="w-full bg-sky-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-[#005c8a] h-2.5 rounded-full" style="width: {{ min(100, $avgPosttest) }}%"></div>
                                </div>
                            </div>
                            <p class="text-xs text-sky-900/80 mt-4 leading-relaxed">Penguasaan materi akhir pasca seluruh sesi pembelajaran via ujian CBT maupun LJK OMR.</p>
                        </div>

                        {{-- N-Gain --}}
                        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-800 text-white shadow-md flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs font-bold text-emerald-100 uppercase">
                                    <span>Indeks N-Gain Aktual</span>
                                    <span class="px-2 py-0.5 rounded bg-white/15 text-white text-[10px]">Efektivitas: {{ $kategoriNGain }}</span>
                                </div>
                                <div class="my-4 flex items-baseline gap-3">
                                    <span class="text-5xl font-black text-white">{{ number_format($avgNGain, 2) }}</span>
                                    <span class="text-xs font-semibold text-emerald-100">(&Delta; {{ $peningkatanNilai >= 0 ? '+' : '' }}{{ $peningkatanNilai }} poin)</span>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-50/90 mt-2 leading-relaxed">
                                Pengukuran peningkatan ternormalisasi (Skala Hake: &ge;0,70 Tinggi, &ge;0,30 Sedang, &lt;0,30 Rendah).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Kalkulasi Kognitif: {{ round($bobot['posttest_dalam_kognitif'] * 100) }}% Posttest + {{ round($bobot['ngain_dalam_kognitif'] * 100) }}% N-Gain</span>
                    <span class="font-semibold" x-text="'Slide 5 / ' + totalSlides">Slide 5 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 6: PRAKTIK IBADAH, SIKAP & KEAKTIFAN FORUM               --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 6 }" id="slide-6">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian V &bull; Penilaian Autentik</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Praktik Ibadah, Akhlak &amp; Keaktifan Forum</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold shrink-0">
                        {{ $keaktifanTotal }} Aksi Keaktifan
                    </span>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-4 my-auto py-3">
                    {{-- Psikomotorik --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between anim-item d-1">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-extrabold text-slate-900">Praktik Ibadah (Psikomotor)</h3>
                                <span class="text-xl font-black text-[#005c8a]">{{ number_format($avgPsikomotor, 1) }}</span>
                            </div>
                            <div class="space-y-2">
                                @forelse($psikomotorRincian->take(5) as $pr)
                                    <div>
                                        <div class="flex justify-between text-[11px] font-medium text-slate-700 mb-0.5">
                                            <span class="truncate pr-2">{{ $pr['nama'] }}</span>
                                            <span class="font-bold shrink-0">{{ $pr['avg_skor'] }}/{{ $pr['skor_maks'] }} ({{ $pr['persen'] }}%)</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-[#005c8a] h-1.5 rounded-full" style="width: {{ min(100, $pr['persen']) }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Rubrik praktik ibadah sesuai Himpunan Putusan Tarjih Muhammadiyah.</p>
                                @endforelse
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 mt-4 pt-2.5 border-t border-slate-200/80 block">Rerata capaian praktik ibadah &amp; outbound kader</span>
                    </div>

                    {{-- Afektif --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between anim-item d-2">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-extrabold text-slate-900">Sikap &amp; Akhlak (Afektif)</h3>
                                <span class="text-xl font-black text-emerald-700">{{ number_format($avgAfektif, 1) }}</span>
                            </div>
                            <div class="space-y-2">
                                @forelse($afektifRincian->take(5) as $ar)
                                    <div>
                                        <div class="flex justify-between text-[11px] font-medium text-slate-700 mb-0.5">
                                            <span class="truncate pr-2">{{ $ar['nama'] }}</span>
                                            <span class="font-bold text-emerald-700 shrink-0">{{ $ar['persen'] }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $ar['persen']) }}%"></div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Penilaian integritas sikap, adab, dan komitmen qiyamul lail.</p>
                                @endforelse
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 mt-4 pt-2.5 border-t border-slate-200/80 block">Observasi perilaku kader oleh instrumen &amp; fasilitator</span>
                    </div>

                    {{-- Keaktifan --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between anim-item d-3">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-extrabold text-slate-900">Keaktifan Forum</h3>
                                <span class="text-xl font-black text-amber-700">{{ number_format($avgKeaktifan, 1) }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($keaktifanRincian as $kr)
                                    <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between text-xs">
                                        <span class="text-slate-600 truncate pr-1">{{ $kr['label'] }}</span>
                                        <span class="font-black text-amber-700">{{ $kr['total'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <span class="text-[11px] text-slate-500 mt-4 pt-2.5 border-t border-slate-200/80 block">Total {{ $keaktifanTotal }} poin partisipasi tercatat di forum &amp; lapangan</span>
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Penilaian Terpadu Aspek Psikomotorik, Afektif, dan Keaktifan</span>
                    <span class="font-semibold" x-text="'Slide 6 / ' + totalSlides">Slide 6 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 7: YUDISIUM KELULUSAN & DISTRIBUSI PREDIKAT              --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 7 }" id="slide-7">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian VI &bull; Keputusan Rapat</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Yudisium Kelulusan &amp; Sebaran Predikat</h2>
                    </div>
                    <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full shrink-0">
                        Kelulusan Gabungan: {{ $lulusPersen + $bersyaratPersen }}%
                    </span>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-5 my-auto py-3 items-center">
                    {{-- Donut SVG + Sebaran Grade --}}
                    <div class="lg:col-span-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col items-center text-center anim-item d-1">
                        @php
                            $r = 42;
                            $c = 2 * pi() * $r;
                            $dashLulus = $totalPeserta > 0 ? ($lulusCount / $totalPeserta) * $c : 0;
                            $dashBersyarat = $totalPeserta > 0 ? ($bersyaratCount / $totalPeserta) * $c : 0;
                            $dashTidak = max(0, $c - $dashLulus - $dashBersyarat);
                        @endphp
                        <div class="relative w-36 h-36">
                            <svg viewBox="0 0 110 110" class="w-full h-full -rotate-90">
                                <circle cx="55" cy="55" r="{{ $r }}" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                                @if($totalPeserta > 0)
                                    <circle cx="55" cy="55" r="{{ $r }}" fill="none" stroke="#10b981" stroke-width="14"
                                        stroke-dasharray="{{ $dashLulus }} {{ $c }}" stroke-dashoffset="0"/>
                                    <circle cx="55" cy="55" r="{{ $r }}" fill="none" stroke="#f59e0b" stroke-width="14"
                                        stroke-dasharray="{{ $dashBersyarat }} {{ $c }}" stroke-dashoffset="-{{ $dashLulus }}"/>
                                    <circle cx="55" cy="55" r="{{ $r }}" fill="none" stroke="#ef4444" stroke-width="14"
                                        stroke-dasharray="{{ $dashTidak }} {{ $c }}" stroke-dashoffset="-{{ $dashLulus + $dashBersyarat }}"/>
                                @endif
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-2xl font-black text-slate-900">{{ $totalPeserta }}</span>
                                <span class="text-[10px] font-bold uppercase text-slate-500">Kader</span>
                            </div>
                        </div>

                        <div class="w-full mt-4 pt-3 border-t border-slate-200 grid grid-cols-3 gap-2 text-xs">
                            <div class="p-2 rounded-lg bg-white border border-slate-200">
                                <span class="text-[10px] font-bold text-slate-500 block">Nilai &ge;85 (A)</span>
                                <span class="text-base font-black text-emerald-700">{{ $gradeA }}</span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-200">
                                <span class="text-[10px] font-bold text-slate-500 block">70&ndash;84 (B)</span>
                                <span class="text-base font-black text-[#005c8a]">{{ $gradeB }}</span>
                            </div>
                            <div class="p-2 rounded-lg bg-white border border-slate-200">
                                <span class="text-[10px] font-bold text-slate-500 block">&lt;70 (C)</span>
                                <span class="text-base font-black text-slate-700">{{ $gradeC }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3 Kartu Keputusan Kelulusan --}}
                    <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-3.5 anim-item d-2">
                        <div class="p-5 rounded-2xl bg-emerald-50/80 border-2 border-emerald-300 text-center flex flex-col justify-between">
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-600 text-white text-[10px] font-extrabold uppercase">Sertifikat Penuh</span>
                                <h3 class="text-base font-black text-emerald-950 uppercase mt-2">LULUS MURNI</h3>
                                <div class="my-4">
                                    <span class="text-5xl font-black text-emerald-900">{{ $lulusCount }}</span>
                                    <span class="text-xs font-bold text-emerald-700 block mt-1">{{ $lulusPersen }}% peserta</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-emerald-900/80 leading-relaxed">Hadir 100%, posttest selesai{{ $rtlSyaratKelulusan ? ', dan RTL tuntas' : '' }}.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-amber-50/80 border-2 border-amber-300 text-center flex flex-col justify-between">
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[10px] font-extrabold uppercase">Perbaikan Syarat</span>
                                <h3 class="text-base font-black text-amber-950 uppercase mt-2">LULUS BERSYARAT</h3>
                                <div class="my-4">
                                    <span class="text-5xl font-black text-amber-900">{{ $bersyaratCount }}</span>
                                    <span class="text-xs font-bold text-amber-700 block mt-1">{{ $bersyaratPersen }}% peserta</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-amber-900/80 leading-relaxed">Hadir 75&ndash;99%{{ $rtlSyaratKelulusan ? ' atau melengkapi RTL/posttest' : ' atau posttest tertunda' }}.</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-red-50/80 border-2 border-red-200 text-center flex flex-col justify-between">
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-600 text-white text-[10px] font-extrabold uppercase">Mengulang</span>
                                <h3 class="text-base font-black text-red-950 uppercase mt-2">TIDAK LULUS</h3>
                                <div class="my-4">
                                    <span class="text-5xl font-black text-red-900">{{ $tidakLulusCount }}</span>
                                    <span class="text-xs font-bold text-red-700 block mt-1">{{ $tidakLulusPersen }}% peserta</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-red-900/80 leading-relaxed">Kehadiran &lt;75% atau belum memenuhi syarat minimal.</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Rentang Nilai Akhir: {{ $minNilaiAkhir }} &ndash; {{ $maxNilaiAkhir }} (Rerata {{ $avgNilaiAkhir }})</span>
                    <span class="font-semibold" x-text="'Slide 7 / ' + totalSlides">Slide 7 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 8: PENGHARGAAN KADER TERBAIK (TOP 3 + TOP 10)            --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 8 }" id="slide-8">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian VII &bull; Apresiasi</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Kader Peserta Terbaik &amp; Peringkat 10 Besar</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-extrabold text-xs shrink-0">&#9733; Leaderboard Yudisium</span>
                </div>

                <div class="relative z-10 my-auto py-3 space-y-4">
                    {{-- Podium Top 3 --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 anim-item d-1">
                        @forelse($top5->take(3) as $idx => $best)
                            <div class="p-4 rounded-2xl border flex flex-col justify-between {{ $idx === 0 ? 'bg-gradient-to-br from-amber-50 via-white to-amber-50/40 border-amber-300 ring-2 ring-[#f8bb00]/50 shadow-sm' : 'bg-slate-50 border-slate-200' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-black shrink-0 {{ $idx === 0 ? 'bg-[#f8bb00] text-slate-950' : ($idx === 1 ? 'bg-slate-300 text-slate-900' : 'bg-amber-700 text-white') }}">
                                            #{{ $idx + 1 }}
                                        </span>
                                        <div class="min-w-0">
                                            <h4 class="font-extrabold text-sm text-slate-900 truncate" title="{{ $best->anggota?->nama_lengkap }}">{{ $best->anggota?->nama_lengkap ?? '-' }}</h4>
                                            <p class="text-[11px] text-slate-500 truncate">{{ $best->anggota?->pcm ?: ($best->anggota?->kecamatan ?: '-') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-2xl font-black text-[#005c8a] font-mono leading-none">{{ number_format($best->nilai_akhir, 1) }}</span>
                                        @if($best->grade)
                                            <span class="block text-[10px] font-bold text-emerald-700 mt-0.5">Grade {{ $best->grade }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 pt-2.5 border-t border-slate-200/70 grid grid-cols-4 gap-1 text-center text-[10px]">
                                    <div><span class="text-slate-400 block">Posttest</span><b class="text-slate-800">{{ number_format($best->nilai_posttest, 0) }}</b></div>
                                    <div><span class="text-slate-400 block">Psiko</span><b class="text-slate-800">{{ number_format($best->nilai_psikomotor, 0) }}</b></div>
                                    <div><span class="text-slate-400 block">Afektif</span><b class="text-slate-800">{{ number_format($best->nilai_afektif, 0) }}</b></div>
                                    <div><span class="text-slate-400 block">Hadir</span><b class="text-emerald-700">{{ number_format($best->nilai_kehadiran, 0) }}%</b></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-8 text-slate-400 text-xs">Data peringkat belum tersedia.</div>
                        @endforelse
                    </div>

                    {{-- Peringkat #4 s.d. #10 --}}
                    @if($top10->count() > 3)
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden anim-item d-2">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px]">
                                    <tr>
                                        <th class="px-3.5 py-2 w-12">Rank</th>
                                        <th class="px-3.5 py-2">Nama Kader</th>
                                        <th class="px-3.5 py-2 hidden sm:table-cell">Asal Cabang / PCM</th>
                                        <th class="px-3.5 py-2 text-right">Hadir</th>
                                        <th class="px-3.5 py-2 text-right">Nilai Akhir</th>
                                        <th class="px-3.5 py-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($top10->skip(3) as $idx => $runner)
                                        <tr>
                                            <td class="px-3.5 py-1.5 font-black text-slate-500">#{{ $idx + 1 }}</td>
                                            <td class="px-3.5 py-1.5 font-bold text-slate-900">{{ $runner->anggota?->nama_lengkap ?? '-' }}</td>
                                            <td class="px-3.5 py-1.5 text-slate-500 hidden sm:table-cell">{{ $runner->anggota?->pcm ?: ($runner->anggota?->kecamatan ?: '-') }}</td>
                                            <td class="px-3.5 py-1.5 text-right text-slate-600">{{ number_format($runner->nilai_kehadiran, 0) }}%</td>
                                            <td class="px-3.5 py-1.5 text-right font-black font-mono text-[#005c8a]">{{ number_format($runner->nilai_akhir, 1) }}</td>
                                            <td class="px-3.5 py-1.5 text-right">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $runner->status_kelulusan === 'Lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $runner->status_kelulusan }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Peringkat disusun berdasarkan Nilai Akhir &amp; Posttest tertinggi</span>
                    <span class="font-semibold" x-text="'Slide 8 / ' + totalSlides">Slide 8 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 9: PROGRES RTL & EVALUASI PENYELENGGARAAN (ANGKET)       --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-5 sm:px-10 sm:py-7" :class="{ 'active': currentSlide === 9 }" id="slide-9">
                <div class="slide-bg-accent"></div>

                <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200 gap-4">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#005c8a] block">Bagian VIII &bull; Tindak Lanjut &amp; Feedback</span>
                        <h2 class="text-xl sm:text-3xl font-black text-slate-900 tracking-tight">Progres RTL &amp; Indeks Kepuasan Penyelenggaraan</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-900 font-bold text-xs shrink-0">
                        Skor Angket: {{ number_format($angketRerataTotal, 2) }} / 5.00
                    </span>
                </div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-5 my-auto py-3">
                    {{-- Kiri: Realisasi RTL --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between anim-item d-1">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-extrabold text-slate-900">Realisasi Rencana Tindak Lanjut (RTL)</h3>
                                <span class="text-lg font-black text-[#005c8a]">{{ $rtlPersenTuntas }}% Tuntas</span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center mb-4">
                                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200">
                                    <span class="text-2xl font-black text-emerald-700 block">{{ $rtlDisetujui }}</span>
                                    <span class="text-[10px] font-bold text-emerald-800 uppercase">Disetujui</span>
                                </div>
                                <div class="p-3 rounded-xl bg-sky-50 border border-sky-200">
                                    <span class="text-2xl font-black text-[#005c8a] block">{{ $rtlTerkirim }}</span>
                                    <span class="text-[10px] font-bold text-sky-800 uppercase">Terkirim</span>
                                </div>
                                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                                    <span class="text-2xl font-black text-amber-700 block">{{ $rtlDraf + $rtlDitolak }}</span>
                                    <span class="text-[10px] font-bold text-amber-800 uppercase">Draf/Revisi</span>
                                </div>
                                <div class="p-3 rounded-xl bg-white border border-slate-200">
                                    <span class="text-2xl font-black text-slate-500 block">{{ $rtlBelum }}</span>
                                    <span class="text-[10px] font-bold text-slate-500 uppercase">Belum Isi</span>
                                </div>
                            </div>

                            <div class="space-y-1.5 text-xs text-slate-600">
                                <p>&bull; <b>Dakwah AUM:</b> Internalisasi nilai Al-Islam &amp; Kemuhammadiyahan di amal usaha.</p>
                                <p>&bull; <b>Penguatan Ranting:</b> Penggerak jamaah, shalat berjamaah, dan kajian ranting.</p>
                                <p>&bull; <b>Monitoring Berkala:</b> Pelaporan realisasi pasca kegiatan via Portal Kader.</p>
                            </div>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 mt-3 pt-2.5 border-t border-slate-200/80 block">
                            Kebijakan event: {{ $rtlSyaratKelulusan ? 'RTL menjadi syarat kelulusan murni' : 'RTL bersifat komitmen pendamping' }}
                        </span>
                    </div>

                    {{-- Kanan: Kepuasan Angket & Suara Kader --}}
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col justify-between anim-item d-2">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-extrabold text-slate-900">Evaluasi Penyelenggaraan ({{ $angketResponden }} Responden)</h3>
                                <span class="text-xs font-bold text-teal-800 bg-teal-100 px-2.5 py-0.5 rounded-full">Skala 1 &ndash; 5</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach($angketKategoriStats as $kat)
                                    <div>
                                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                                            <span>{{ $kat['kode'] }}. {{ $kat['label'] }}</span>
                                            <span class="font-black text-[#005c8a]">{{ number_format($kat['avg'], 2) }} <span class="text-slate-400 font-normal">({{ $kat['persen'] }}%)</span></span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-teal-600 h-2 rounded-full" style="width: {{ min(100, $kat['persen']) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($angketKomentarList->isNotEmpty())
                                <div class="mt-4 pt-3 border-t border-slate-200/80 space-y-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Sorotan Kesan &amp; Masukan Kader</span>
                                    @foreach($angketKomentarList->take(2) as $kom)
                                        <blockquote class="text-[11px] text-slate-700 bg-white px-3 py-2 rounded-xl border border-slate-200/80 italic line-clamp-2">
                                            &ldquo;{{ $kom->komentar }}&rdquo;
                                            <span class="not-italic font-bold text-slate-500 ml-1">&mdash; {{ $kom->anggota?->nama_lengkap ?? 'Peserta' }}</span>
                                        </blockquote>
                                    @endforeach
                                </div>
                            @elseif($angketPerasaanList->isNotEmpty())
                                <div class="mt-4 pt-3 border-t border-slate-200/80 flex flex-wrap gap-1.5">
                                    @foreach($angketPerasaanList->take(4) as $rasa)
                                        <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-[11px] font-semibold text-slate-700">{{ $rasa->perasaan }} ({{ $rasa->total }})</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-slate-400 mt-4">Belum ada komentar tertulis dari angket evaluasi.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-2.5 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                    <span>Data RTL dan Angket Penyelenggaraan Terintegrasi (Digital &amp; Kertas)</span>
                    <span class="font-semibold" x-text="'Slide 9 / ' + totalSlides">Slide 9 / 10</span>
                </div>
            </section>

            {{-- ============================================================== --}}
            {{-- SLIDE 10: PENUTUP & AMANAH GERAKAN                             --}}
            {{-- ============================================================== --}}
            <section class="slide px-6 py-6 sm:px-12 sm:py-9" :class="{ 'active': currentSlide === 10 }" id="slide-10" style="background: linear-gradient(135deg, #001c2b 0%, #002e47 60%, #005c8a 100%); color: white;">
                <div class="relative z-10 flex items-center justify-between">
                    <span class="text-xs uppercase tracking-widest font-black text-[#f8bb00]">Penutup Baitul Arqam</span>
                    <span class="text-xs text-slate-300">MPKSDI PDM Karanganyar</span>
                </div>

                <div class="relative z-10 my-auto py-6 max-w-3xl text-center mx-auto anim-item d-1">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 border border-white/15 mb-5 p-2">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="MPKSDI" class="w-full h-full object-contain">
                    </div>
                    <blockquote class="text-2xl sm:text-4xl font-black text-white leading-snug tracking-tight">
                        &ldquo;Hidup-hidupilah Muhammadiyah, dan jangan mencari hidup di Muhammadiyah.&rdquo;
                    </blockquote>
                    <p class="text-sm font-bold text-[#f8bb00] mt-4 uppercase tracking-widest">&mdash; K.H. Ahmad Dahlan</p>
                    <div class="mt-7 pt-5 border-t border-white/15 max-w-xl mx-auto">
                        <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                            Selamat kepada seluruh peserta <b>{{ $event->nama_event }}</b>. Semoga ilmu, ukhuwah, dan komitmen dakwah yang terbangun senantiasa memberi kemanfaatan bagi persyarikatan, umat, dan bangsa.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between text-xs text-slate-300">
                    <span>Nashrun minallahi wa fathun qarib</span>
                    <span class="font-semibold" x-text="'Slide 10 / ' + totalSlides">Slide 10 / 10</span>
                </div>
            </section>

        </main>

        {{-- BOTTOM CONTROLS & PROGRESS BAR --}}
        <footer id="bottom-controls" x-show="!hideChrome" class="shrink-0 z-40 px-3 sm:px-5 py-2 flex items-center justify-between gap-3 bg-[#001724] border-t border-white/10 text-white">
            <div class="flex items-center gap-2">
                <button type="button" @click="prevSlide()" :disabled="currentSlide <= 1" class="px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:cursor-not-allowed text-xs font-semibold flex items-center gap-1 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Sebelumnya</span>
                </button>
                <button type="button" @click="nextSlide()" :disabled="currentSlide >= totalSlides" class="px-3.5 py-1.5 rounded-lg bg-[#f8bb00] hover:bg-amber-400 disabled:opacity-30 disabled:cursor-not-allowed text-slate-950 font-bold text-xs flex items-center gap-1 transition">
                    <span>Berikutnya</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <div class="hidden sm:flex items-center gap-1.5">
                <template x-for="i in totalSlides" :key="i">
                    <button type="button" @click="goToSlide(i)" :title="i + '. ' + slideTitles[i - 1]" class="h-2 rounded-full transition-all" :class="currentSlide === i ? 'w-6 bg-[#f8bb00]' : 'w-2 bg-white/25 hover:bg-white/60'"></button>
                </template>
            </div>

            <div class="text-xs text-slate-300 font-mono shrink-0">
                Slide <span class="font-bold text-[#f8bb00]" x-text="currentSlide"></span> / <span x-text="totalSlides"></span>
            </div>
        </footer>
    </div>

    {{-- TABLE OF CONTENTS DRAWER --}}
    <aside id="toc-drawer" x-show="isDrawerOpen" @click.away="isDrawerOpen = false" x-transition class="fixed top-0 right-0 bottom-0 w-80 max-w-full bg-slate-950/95 backdrop-blur-xl border-l border-white/10 z-50 text-white p-5 flex flex-col justify-between shadow-2xl">
        <div class="overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-3">
                <h3 class="font-bold text-sm text-white">Daftar Slide Presentasi</h3>
                <button type="button" @click="isDrawerOpen = false" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white">&times;</button>
            </div>
            <div class="space-y-1 text-xs">
                <template x-for="(sTitle, sIdx) in slideTitles" :key="sIdx">
                    <button type="button" @click="goToSlide(sIdx + 1); isDrawerOpen = false" class="w-full text-left px-3 py-2 rounded-lg transition flex items-center justify-between gap-2" :class="currentSlide === (sIdx + 1) ? 'bg-[#f8bb00] text-slate-950 font-bold' : 'hover:bg-white/10 text-slate-300'">
                        <span class="truncate" x-text="(sIdx + 1) + '. ' + sTitle"></span>
                        <span class="text-[10px] opacity-75 shrink-0" x-show="currentSlide === (sIdx + 1)">Aktif</span>
                    </button>
                </template>
            </div>
        </div>
        <div class="pt-3 border-t border-white/10 text-[11px] text-slate-400 space-y-1">
            <p>Navigasi: Panah Kiri/Kanan, Spasi, Geser Layar (Swipe).</p>
            <p>Pintasan: <b>F</b> Layar Penuh &bull; <b>O</b> Daftar Slide &bull; <b>H</b> Bersih &bull; <b>P</b> Cetak PDF.</p>
        </div>
    </aside>

    <script>
        function livePresentation() {
            return {
                currentSlide: 1,
                totalSlides: 10,
                isAutoPlay: false,
                autoPlayTimer: null,
                isDrawerOpen: false,
                hideChrome: false,
                touchStartX: 0,
                slideTitles: [
                    'Sampul Judul Kegiatan',
                    'Ringkasan Eksekutif & Bobot',
                    'Demografi & Sebaran PCM',
                    'Agenda Sesi & Kehadiran',
                    'Kognitif, Posttest & N-Gain',
                    'Praktik Ibadah, Sikap & Forum',
                    'Yudisium & Sebaran Predikat',
                    'Kader Terbaik & Top 10',
                    'Progres RTL & Evaluasi Angket',
                    'Penutup & Amanah Gerakan',
                ],
                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const initial = parseInt(urlParams.get('slide'), 10);
                    if (initial && initial >= 1 && initial <= this.totalSlides) {
                        this.currentSlide = initial;
                    }
                },
                nextSlide() {
                    if (this.currentSlide < this.totalSlides) {
                        this.currentSlide++;
                    }
                },
                prevSlide() {
                    if (this.currentSlide > 1) {
                        this.currentSlide--;
                    }
                },
                goToSlide(num) {
                    if (num >= 1 && num <= this.totalSlides) {
                        this.currentSlide = num;
                    }
                },
                toggleAutoPlay() {
                    this.isAutoPlay = !this.isAutoPlay;
                    if (this.isAutoPlay) {
                        this.autoPlayTimer = setInterval(() => {
                            if (this.currentSlide < this.totalSlides) {
                                this.nextSlide();
                            } else {
                                this.goToSlide(1);
                            }
                        }, 8000);
                    } else {
                        clearInterval(this.autoPlayTimer);
                        this.autoPlayTimer = null;
                    }
                },
                toggleDrawer() {
                    this.isDrawerOpen = !this.isDrawerOpen;
                },
                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                },
                printPdf() {
                    window.print();
                },
                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0]?.screenX ?? 0;
                },
                handleTouchEnd(e) {
                    const endX = e.changedTouches[0]?.screenX ?? 0;
                    const delta = endX - this.touchStartX;
                    if (Math.abs(delta) > 45) {
                        if (delta < 0) this.nextSlide();
                        else this.prevSlide();
                    }
                },
                handleKeydown(e) {
                    if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') {
                        e.preventDefault();
                        this.nextSlide();
                    } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                        e.preventDefault();
                        this.prevSlide();
                    } else if (e.key === 'f' || e.key === 'F') {
                        e.preventDefault();
                        this.toggleFullscreen();
                    } else if (e.key === 'h' || e.key === 'H') {
                        e.preventDefault();
                        this.hideChrome = !this.hideChrome;
                    } else if (e.key === 'o' || e.key === 'O' || e.key === 'm' || e.key === 'M') {
                        e.preventDefault();
                        this.toggleDrawer();
                    } else if (e.key === 'p' || e.key === 'P') {
                        e.preventDefault();
                        this.printPdf();
                    } else if (e.key === 'Escape') {
                        this.isDrawerOpen = false;
                        this.hideChrome = false;
                    }
                },
            };
        }
    </script>
</body>
</html>
