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
        body {
            margin: 0;
            background-color: #001f30;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            overflow: hidden;
            height: 100vh;
            width: 100vw;
            user-select: none;
        }

        #presentation-stage {
            width: 100vw;
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transform: scale(0.96) translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.6s cubic-bezier(0.16, 1, 0.3, 1),
                        visibility 0.6s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4.5rem 3.5rem 4rem 3.5rem;
            z-index: 0;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: scale(1) translateY(0);
            z-index: 10;
        }

        .slide-pattern {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 45%;
            background: radial-gradient(circle at top right, rgba(0, 92, 138, 0.08), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* Slide Transition Animations */
        .anim-fade { opacity: 0; transform: translateY(16px); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        .slide.active .anim-fade { opacity: 1; transform: translateY(0); }
        .d-1 { transition-delay: 0.1s; }
        .d-2 { transition-delay: 0.2s; }
        .d-3 { transition-delay: 0.3s; }
        .d-4 { transition-delay: 0.4s; }

        /* Print Media Styles for Export PDF */
        @media print {
            body {
                overflow: visible !important;
                height: auto !important;
                background: white !important;
            }
            #presentation-stage {
                display: block !important;
                height: auto !important;
                width: 100% !important;
            }
            .slide {
                position: relative !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                height: 100vh !important;
                width: 100vw !important;
                box-shadow: none !important;
                padding: 3rem !important;
            }
            #top-controls, #bottom-controls, #toc-drawer {
                display: none !important;
            }
        }
    </style>
</head>
<body x-data="livePresentation()" x-init="init()" @keydown.window="handleKeydown($event)">

    {{-- Global Page Transition & Smooth Preloader --}}
    <x-page-loader />

    {{-- TOP PRESENTATION TOOLBAR --}}
    <header id="top-controls" class="fixed top-0 left-0 right-0 z-50 px-4 py-2.5 flex items-center justify-between bg-slate-950/85 backdrop-blur-md border-b border-white/10 text-white transition-opacity duration-300 hover:opacity-100 opacity-80">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('admin.events.show', $event) }}" class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Kembali ke Dashboard Event">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
            <div class="h-4 w-px bg-white/20"></div>
            <div class="min-w-0">
                <h1 class="text-xs font-bold text-white truncate max-w-md sm:max-w-xl">{{ $event->nama_event }}</h1>
                <p class="text-[10px] text-amber-300 truncate">Live PPT &bull; Rekapitulasi Evaluasi &amp; Yudisium Perkaderan</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Auto Play Toggle --}}
            <button type="button" @click="toggleAutoPlay()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold transition" :class="isAutoPlay ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white/10 hover:bg-white/20 text-slate-200'" title="Putar Otomatis (Timer 8 detik)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="hidden sm:inline" x-text="isAutoPlay ? 'Auto ON' : 'Auto'"></span>
            </button>

            {{-- Table of Contents Drawer Toggle --}}
            <button type="button" @click="toggleDrawer()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Daftar Slide (Shortcut: O)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span class="hidden sm:inline">Daftar Slide</span>
            </button>

            {{-- Print / Save PDF --}}
            <button type="button" @click="printPdf()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 transition" title="Cetak / Simpan Slide ke PDF (Shortcut: P)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span class="hidden sm:inline">Cetak PDF</span>
            </button>

            {{-- Fullscreen Toggle --}}
            <button type="button" @click="toggleFullscreen()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-[#f8bb00] hover:bg-amber-400 active:bg-amber-500 text-slate-950 font-bold text-xs transition shadow-xs" title="Layar Penuh (Shortcut: F)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="hidden sm:inline">Fullscreen</span>
            </button>
        </div>
    </header>

    {{-- MAIN SLIDESHOW STAGE (16:9 RATIO FIT) --}}
    <main id="presentation-stage">

        {{-- ============================================================== --}}
        {{-- SLIDE 1: COVER TITLE SLIDE                                     --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 1 }" id="slide-1" style="background: linear-gradient(135deg, #001f30 0%, #003652 60%, #004d73 100%); color: white;">
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center font-black text-amber-300 text-xl shadow-lg">
                        M
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-widest font-black text-amber-300 block">MPKSDI PDM KARANGANYAR</span>
                        <span class="text-[11px] text-slate-300">Majelis Pembinaan Kader &amp; Sumber Daya Insani</span>
                    </div>
                </div>
                <div class="px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-slate-200">
                    Sistem Pelatihan Terpadu Arqam
                </div>
            </div>

            <div class="relative z-10 my-auto py-6 max-w-4xl anim-fade d-1">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 border border-amber-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                    <span class="w-2 h-2 rounded-full bg-[#f8bb00] animate-pulse"></span>
                    Laporan Yudisium &amp; Evaluasi Pelaksanaan
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight tracking-tight">
                    {{ $event->nama_event }}
                </h1>
                <p class="text-sm sm:text-base text-slate-200 mt-4 leading-relaxed font-normal max-w-3xl">
                    Rekapitulasi pencapaian kompetensi kader Muhammadiyah mencakup aspek kognitif, evaluasi praktik ibadah, akhlakul karimah, keaktifan forum, dan komitmen penugasan tindak lanjut.
                </p>
            </div>

            <div class="relative z-10 pt-4 border-t border-white/15 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-300 anim-fade d-2">
                <div class="flex items-center gap-6 flex-wrap">
                    @if($event->tanggal_mulai)
                    <div>
                        <span class="text-slate-400 block text-[10px]">Waktu Pelaksanaan</span>
                        <span class="font-bold text-white">{{ $event->tanggal_mulai->translatedFormat('d M Y') }} @if($event->tanggal_selesai && $event->tanggal_selesai != $event->tanggal_mulai)- {{ $event->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                    </div>
                    @endif
                    @if($event->lokasi)
                    <div>
                        <span class="text-slate-400 block text-[10px]">Lokasi Penyelenggaraan</span>
                        <span class="font-bold text-white">{{ $event->lokasi }}</span>
                    </div>
                    @endif
                    <div>
                        <span class="text-slate-400 block text-[10px]">Total Peserta Terdaftar</span>
                        <span class="font-bold text-amber-300">{{ $totalPeserta }} Kader Persyarikatan</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 block">Status Pelatihan</span>
                    <span class="font-extrabold uppercase px-2 py-0.5 rounded text-[11px] {{ $event->status === 'selesai' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-sky-500/20 text-sky-300 border border-sky-400/30' }}">{{ $event->status }}</span>
                </div>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 2: DEMOGRAFI & PROFIL KADER PESERTA                      --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 2 }" id="slide-2">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN I</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Demografi &amp; Profil Kader Peserta</h2>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded bg-slate-100 text-slate-600">Total: <b>{{ $totalPeserta }} Kader</b></span>
            </div>

            <div class="relative z-10 grid lg:grid-cols-3 gap-6 my-auto py-4">
                {{-- Gender Breakdown --}}
                <div class="p-6 rounded-2xl bg-gradient-to-br from-sky-50 to-indigo-50/50 border border-sky-100 shadow-xs flex flex-col justify-between anim-fade d-1">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Komposisi Jenis Kelamin</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                    <span>Laki-laki (Ikhwan)</span>
                                    <span class="text-sky-800">{{ $lakiLaki }} ({{ $persenLaki }}%)</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-sky-600 h-3 rounded-full" style="width: {{ $persenLaki }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-700 mb-1">
                                    <span>Perempuan (Akhwat)</span>
                                    <span class="text-indigo-800">{{ $perempuan }} ({{ $persenPerempuan }}%)</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ $persenPerempuan }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-200/80 text-xs text-slate-500">
                        Pemberdayaan kader yang proporsional bagi penguatan kepemimpinan dakwah di seluruh lini persyarikatan.
                    </div>
                </div>

                {{-- Sebaran Cabang PCM --}}
                <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-xs anim-fade d-2">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Sebaran Asal Pimpinan Cabang (PCM) / Wilayah</h3>
                    <div class="grid sm:grid-cols-3 gap-3">
                        @forelse($cabangList->take(6) as $cabangName => $count)
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-2xs">
                            <span class="text-[11px] text-slate-500 block truncate">{{ $cabangName }}</span>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xl font-black text-slate-900">{{ $count }}</span>
                                <span class="text-[10px] font-bold text-sky-700">{{ $totalPeserta > 0 ? round(($count / $totalPeserta) * 100) : 0 }}%</span>
                            </div>
                        </div>
                        @empty
                        <div class="sm:col-span-3 text-center py-8 text-slate-400 text-xs">
                            Data domisili cabang belum tersedia.
                        </div>
                        @endforelse
                    </div>
                    <div class="mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-xs text-amber-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Seluruh peserta telah terverifikasi melalui basis data terpadu Sistem Informasi Master Anggota MPKSDI.</span>
                    </div>
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Dokumen Presentasi Resmi MPKSDI Karanganyar</span>
                <span>Slide 2 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 3: AGENDA SESI & TINGKAT PRESENSI                        --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 3 }" id="slide-3">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN II</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Kedisiplinan &amp; Kehadiran Sesi Pembelajaran</h2>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Rata-rata Presensi Sesi</span>
                    <span class="text-lg font-black text-sky-700">{{ $avgPresensiPersen }}%</span>
                </div>
            </div>

            <div class="relative z-10 my-auto py-4 space-y-2.5 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[55vh] overflow-y-auto pr-1">
                    @forelse($sesiList as $s)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-white transition flex flex-col justify-between anim-fade d-1">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="px-2 py-0.2 rounded text-[10px] font-bold uppercase {{ $s->jenis === 'materi' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $s->jenis }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-500">{{ $s->jam_mulai ? $s->jam_mulai.' WIB' : '' }}</span>
                            </div>
                            <h4 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-1" title="{{ $s->nama_sesi }}">
                                {{ $s->urutan }}. {{ $s->nama_sesi }}
                            </h4>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                @if($s->pemateri)<b>Narasumber:</b> {{ $s->pemateri }}@else Materi Perkaderan @endif
                            </p>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-600">Kehadiran:</span>
                            <span class="font-bold text-slate-900">{{ $s->absensi_count }} / {{ $totalPeserta }} ({{ $totalPeserta > 0 ? round(($s->absensi_count / $totalPeserta) * 100) : 0 }}%)</span>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-2 text-center py-12 text-slate-400 text-sm">
                        Belum ada jadwal sesi yang dicatat pada pelatihan ini.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Pemindaian Presensi Berbasis QR Dinamis &amp; Verifikasi Fasilitator</span>
                <span>Slide 3 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 4: EVALUASI KOGNITIF (PRETEST VS POSTTEST)               --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 4 }" id="slide-4">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN III</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Evaluasi Aspek Kognitif &amp; Peningkatan N-Gain</h2>
                </div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">
                    Peningkatan: +{{ $peningkatanNilai }} Poin
                </span>
            </div>

            <div class="relative z-10 grid lg:grid-cols-3 gap-6 my-auto py-4">
                {{-- Card Pretest --}}
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center anim-fade d-1 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rata-rata Pretest Awal</span>
                        <div class="my-4">
                            <span class="text-5xl font-black text-slate-800">{{ number_format($avgPretest, 1) }}</span>
                            <span class="text-slate-400 text-sm block mt-1">Skala 100</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Mengukur pemahaman awal materi ideologi kemuhammadiyahan, fikih ibadah tarjih, dan manhaj perjuangan kader sebelum sesi pelatihan dimulai.
                    </p>
                </div>

                {{-- Card Posttest --}}
                <div class="p-6 rounded-2xl bg-sky-50/70 border border-sky-200 text-center anim-fade d-2 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-sky-800 uppercase tracking-wider">Rata-rata Posttest Akhir</span>
                        <div class="my-4">
                            <span class="text-5xl font-black text-sky-900">{{ number_format($avgPosttest, 1) }}</span>
                            <span class="text-sky-600 text-sm block mt-1">Skala 100</span>
                        </div>
                    </div>
                    <p class="text-xs text-sky-800 leading-relaxed">
                        Pengujian menyeluruh berbasis Computer Assisted Test (CAT) setelah kader menerima pemaparan dan materi intensif narasumber.
                    </p>
                </div>

                {{-- Card N-Gain / Peningkatan --}}
                <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white text-center anim-fade d-3 flex flex-col justify-between shadow-lg">
                    <div>
                        <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Daya Serap &amp; N-Gain</span>
                        <div class="my-4">
                            <span class="text-5xl font-black text-white">+{{ $peningkatanNilai }}</span>
                            <span class="text-emerald-200 text-sm block mt-1">Kenaikan Skor Rata-rata</span>
                        </div>
                    </div>
                    <p class="text-xs text-emerald-100 leading-relaxed">
                        Menunjukkan efektivitas materi kurikulum Baitul Arqam dalam memperkaya wawasan keislaman dan kemuhammadiyahan kader peserta.
                    </p>
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Instrumen Soal Terstandarisasi Lembaga Uji CAT MPKSDI</span>
                <span>Slide 4 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 5: EVALUASI PSIKOMOTORIK & AFEKTIF                       --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 5 }" id="slide-5">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN IV</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Evaluasi Praktik Ibadah &amp; Pembentukan Akhlak</h2>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Penilaian Otentik Fasilitator
                </span>
            </div>

            <div class="relative z-10 grid lg:grid-cols-3 gap-6 my-auto py-4">
                {{-- Psikomotorik Card --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-xs anim-fade d-1 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center font-bold mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Praktik Ibadah (Psikomotorik)</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Penguasaan bacaan dan tata cara shalat, thaharah, serta tajwid sesuai Himpunan Putusan Tarjih (HPT).</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-baseline justify-between">
                            <span class="text-xs text-slate-500">Rerata Nilai:</span>
                            <span class="text-3xl font-black text-sky-800">{{ number_format($avgPsikomotor, 1) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Afektif Card --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-xs anim-fade d-2 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Sikap &amp; Akhlak (Afektif)</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Integritas kedisiplinan, ukhuwah islamiyah, adab pergaulan, dan komitmen qiyamul lail berjamaah.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-baseline justify-between">
                            <span class="text-xs text-slate-500">Rerata Nilai:</span>
                            <span class="text-3xl font-black text-emerald-800">{{ number_format($avgAfektif, 1) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Keaktifan Card --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/90 shadow-xs anim-fade d-3 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-bold mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Keaktifan Forum &amp; Diskusi</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Partisipasi kader dalam tanya jawab kritis, dinamika kelompok outbound, dan pemberian kultum.</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-baseline justify-between">
                            <span class="text-xs text-slate-500">Rerata Nilai:</span>
                            <span class="text-3xl font-black text-amber-800">{{ number_format($avgKeaktifan, 1) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Rubrik Penilaian Holistik Integratif MPKSDI</span>
                <span>Slide 5 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 6: YUDISIUM HASIL KELULUSAN KADER                        --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 6 }" id="slide-6">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN V</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Keputusan Yudisium &amp; Status Kelulusan Peserta</h2>
                </div>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full">
                    Tingkat Kelulusan: {{ $lulusPersen + $bersyaratPersen }}%
                </span>
            </div>

            <div class="relative z-10 grid lg:grid-cols-3 gap-6 my-auto py-4">
                {{-- Lulus Murni --}}
                <div class="p-6 rounded-2xl bg-emerald-50/70 border-2 border-emerald-300 shadow-xs text-center anim-fade d-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-emerald-950 uppercase tracking-tight">LULUS MURNI</h3>
                        <p class="text-xs text-emerald-800 mt-1">Kehadiran 100%, nilai akademik kompeten{{ $event->rtlSyaratKelulusan() ? ', dan berkas tugas RTL tuntas' : '' }}.</p>
                        <div class="my-4">
                            <span class="text-6xl font-black text-emerald-900">{{ $lulusCount }}</span>
                            <span class="text-sm font-bold text-emerald-700 block mt-1">{{ $lulusPersen }}% dari Total Peserta</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-800 bg-emerald-200/50 py-1.5 px-3 rounded-lg block">Berhak Sertifikat Penuh</span>
                </div>

                {{-- Lulus Bersyarat --}}
                <div class="p-6 rounded-2xl bg-amber-50/70 border-2 border-amber-300 shadow-xs text-center anim-fade d-2 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 mx-auto rounded-full bg-amber-500 text-white flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-amber-950 uppercase tracking-tight">LULUS BERSYARAT</h3>
                        <p class="text-xs text-amber-800 mt-1">Kehadiran 75-99%{{ $event->rtlSyaratKelulusan() ? ' atau masih menyelesaikan tugas perbaikan RTL' : ' atau Posttest belum dikerjakan' }}.</p>
                        <div class="my-4">
                            <span class="text-6xl font-black text-amber-900">{{ $bersyaratCount }}</span>
                            <span class="text-sm font-bold text-amber-700 block mt-1">{{ $bersyaratPersen }}% dari Total Peserta</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-amber-900 bg-amber-200/50 py-1.5 px-3 rounded-lg block">Sertifikat Ditunda s.d. Syarat Lengkap</span>
                </div>

                {{-- Tidak Lulus --}}
                <div class="p-6 rounded-2xl bg-red-50/70 border-2 border-red-200 shadow-xs text-center anim-fade d-3 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 mx-auto rounded-full bg-red-500 text-white flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                        <h3 class="text-lg font-black text-red-950 uppercase tracking-tight">TIDAK LULUS</h3>
                        <p class="text-xs text-red-800 mt-1">Kehadiran di bawah 75% atau tidak memenuhi ketentuan minimal.</p>
                        <div class="my-4">
                            <span class="text-6xl font-black text-red-900">{{ $tidakLulusCount }}</span>
                            <span class="text-sm font-bold text-red-700 block mt-1">{{ $tidakLulusPersen }}% dari Total Peserta</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold text-red-900 bg-red-200/50 py-1.5 px-3 rounded-lg block">Direkomendasikan Mengulang Angkatan Depan</span>
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Rapat Yudisium Pimpinan MPKSDI dan Tim Fasilitator Perkaderan</span>
                <span>Slide 6 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 7: TOP 5 KADER TERBAIK (PENGHARGAAN)                     --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 7 }" id="slide-7">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN VI</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Penganugerahan 5 Kader Peserta Terbaik</h2>
                </div>
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-extrabold text-xs flex items-center gap-1">
                    <span>★</span> Leaderboard Teladan
                </span>
            </div>

            <div class="relative z-10 grid grid-cols-1 sm:grid-cols-5 gap-3.5 my-auto py-4">
                @forelse($top5 as $idx => $best)
                <div class="p-4 rounded-2xl border transition shadow-xs flex flex-col justify-between anim-fade {{ $idx === 0 ? 'bg-gradient-to-b from-amber-50 to-white border-amber-300 ring-2 ring-amber-400/50' : 'bg-white border-slate-200' }}">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black
                                {{ $idx === 0 ? 'bg-amber-400 text-slate-950 shadow-md' : ($idx === 1 ? 'bg-slate-300 text-slate-800' : ($idx === 2 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-700')) }}">
                                #{{ $idx + 1 }}
                            </span>
                            @if($idx === 0)
                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">Juara I</span>
                            @endif
                        </div>
                        <h4 class="font-extrabold text-xs sm:text-sm text-slate-900 line-clamp-2 leading-snug" title="{{ $best->anggota->nama_lengkap }}">
                            {{ $best->anggota->nama_lengkap }}
                        </h4>
                        <p class="text-[11px] text-slate-500 truncate mt-1">
                            {{ $best->anggota->pcm ?: ($best->anggota->kecamatan ?: '-') }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                        <span class="text-2xl font-black text-sky-800 block font-mono">{{ number_format($best->nilai_akhir, 1) }}</span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Nilai Akhir</span>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $best->status_kelulusan === 'Lulus' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $best->status_kelulusan }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-5 text-center py-12 text-slate-400 text-xs">
                    Data peringkat belum terhitung.
                </div>
                @endforelse
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Kalkulasi Berbobot Kognitif, Praktik Ibadah, Sikap, &amp; Kehadiran</span>
                <span>Slide 7 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 8: RENCANA TINDAK LANJUT (RTL)                          --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 8 }" id="slide-8">
            <div class="slide-pattern"></div>
            <div class="relative z-10 flex items-center justify-between pb-3 border-b border-slate-200">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-sky-700 block">BAGIAN VII</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Rencana Tindak Lanjut (RTL) &amp; Komitmen Gerakan</h2>
                </div>
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Amanah Pasca Perkaderan
                </span>
            </div>

            <div class="relative z-10 grid lg:grid-cols-3 gap-6 my-auto py-4">
                <div class="p-6 rounded-2xl bg-indigo-50/60 border border-indigo-100 anim-fade d-1 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold mb-3">
                            1
                        </div>
                        <h3 class="text-base font-bold text-indigo-950">Infiltrasi Nilai Al-Islam &amp; Kemuhammadiyahan</h3>
                        <p class="text-xs text-indigo-900/80 mt-2 leading-relaxed">
                            Menerapkan etos kerja dakwah dan nilai-nilai Islami dalam melayani masyarakat di Amal Usaha Muhammadiyah (AUM) tempat bertugas.
                        </p>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-sky-50/60 border border-sky-100 anim-fade d-2 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold mb-3">
                            2
                        </div>
                        <h3 class="text-base font-bold text-sky-950">Penguatan Jamaah &amp; Ranting</h3>
                        <p class="text-xs text-sky-900/80 mt-2 leading-relaxed">
                            Kader menjadi penggerak aktif kajian keislaman, shalat subuh berjamaah, dan kegiatan ranting di lingkungan tempat tinggal masing-masing.
                        </p>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-emerald-50/60 border border-emerald-100 anim-fade d-3 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold mb-3">
                            3
                        </div>
                        <h3 class="text-base font-bold text-emerald-950">Pelaporan &amp; Monitoring Berkala</h3>
                        <p class="text-xs text-emerald-900/80 mt-2 leading-relaxed">
                            Mengunggah dokumen bukti realisasi RTL ke dalam Portal Kader Arqam untuk ditinjau oleh instruktur dan Majelis MPKSDI Daerah.
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 pt-3 border-t border-slate-200 text-xs text-slate-500 flex justify-between">
                <span>Tindak Lanjut Dimonitoring Melalui Akun Portal Kader</span>
                <span>Slide 8 / 9</span>
            </div>
        </section>

        {{-- ============================================================== --}}
        {{-- SLIDE 9: PENUTUP & KUTIPAN GERAKAN                            --}}
        {{-- ============================================================== --}}
        <section class="slide" :class="{ 'active': currentSlide === 9 }" id="slide-9" style="background: linear-gradient(135deg, #001f30 0%, #002b40 60%, #004d73 100%); color: white;">
            <div class="relative z-10 flex items-center justify-between">
                <span class="text-xs uppercase tracking-widest font-black text-amber-300">PENUTUP BAITUL ARQAM</span>
                <span class="text-xs text-slate-300">MPKSDI PDM Karanganyar</span>
            </div>

            <div class="relative z-10 my-auto py-8 max-w-3xl text-center mx-auto anim-fade d-1">
                <blockquote class="text-2xl sm:text-3xl lg:text-4xl font-black text-white leading-snug tracking-tight">
                    &ldquo;Hidup-hidupilah Muhammadiyah, dan jangan mencari hidup di Muhammadiyah.&rdquo;
                </blockquote>
                <p class="text-sm font-bold text-amber-300 mt-4 uppercase tracking-widest">&mdash; K.H. Ahmad Dahlan</p>
                <div class="mt-8 pt-6 border-t border-white/15 max-w-xl mx-auto">
                    <p class="text-xs sm:text-sm text-slate-200 font-medium">
                        Selamat kepada seluruh kader peserta {{ $event->nama_event }}. Teruslah bergerak menebar kemanfaatan bagi persyarikatan, umat, dan bangsa.
                    </p>
                </div>
            </div>

            <div class="relative z-10 pt-4 border-t border-white/15 flex items-center justify-between text-xs text-slate-300">
                <span>Alhamdulillahirabbil'alamin</span>
                <span>Slide 9 / 9 &bull; Selesai</span>
            </div>
        </section>

    </main>

    {{-- BOTTOM CONTROLS & TIMELINE BAR --}}
    <footer id="bottom-controls" class="fixed bottom-0 left-0 right-0 z-50 px-4 py-2 flex items-center justify-between bg-slate-950/85 backdrop-blur-md border-t border-white/10 text-white transition-opacity duration-300 hover:opacity-100 opacity-80">
        {{-- Nav Buttons --}}
        <div class="flex items-center gap-2">
            <button type="button" @click="prevSlide()" :disabled="currentSlide <= 1" class="px-2.5 py-1 rounded bg-white/10 hover:bg-white/20 disabled:opacity-30 disabled:cursor-not-allowed text-xs font-semibold flex items-center gap-1 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Sebelumnya</span>
            </button>
            <button type="button" @click="nextSlide()" :disabled="currentSlide >= totalSlides" class="px-3 py-1 rounded bg-[#f8bb00] hover:bg-amber-400 active:bg-amber-500 disabled:opacity-30 disabled:cursor-not-allowed text-slate-950 font-bold text-xs flex items-center gap-1 transition">
                <span>Berikutnya</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Progress Dots / Indicator --}}
        <div class="hidden sm:flex items-center gap-1.5">
            <template x-for="i in totalSlides" :key="i">
                <button type="button" @click="goToSlide(i)" class="h-2 rounded-full transition-all" :class="currentSlide === i ? 'w-6 bg-[#f8bb00]' : 'w-2 bg-white/30 hover:bg-white/60'"></button>
            </template>
        </div>

        {{-- Slide Counter --}}
        <div class="text-xs text-slate-300 font-mono">
            Slide <span class="font-bold text-amber-300" x-text="currentSlide"></span> / <span x-text="totalSlides"></span>
        </div>
    </footer>

    {{-- TABLE OF CONTENTS DRAWER --}}
    <aside id="toc-drawer" x-show="isDrawerOpen" @click.away="isDrawerOpen = false" x-transition class="fixed top-0 right-0 bottom-0 w-80 bg-slate-900/95 backdrop-blur-xl border-l border-white/10 z-50 text-white p-5 flex flex-col justify-between shadow-2xl">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-4">
                <h3 class="font-bold text-sm text-white">Daftar Slide Presentasi</h3>
                <button type="button" @click="isDrawerOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <div class="space-y-1.5 text-xs">
                <template x-for="(sTitle, sIdx) in slideTitles" :key="sIdx">
                    <button type="button" @click="goToSlide(sIdx + 1); isDrawerOpen = false" class="w-full text-left px-3 py-2 rounded-lg transition flex items-center justify-between" :class="currentSlide === (sIdx + 1) ? 'bg-[#f8bb00] text-slate-950 font-bold' : 'hover:bg-white/10 text-slate-300'">
                        <span class="truncate" x-text="(sIdx + 1) + '. ' + sTitle"></span>
                        <span class="text-[10px] opacity-60" x-show="currentSlide === (sIdx + 1)">&bull; Aktif</span>
                    </button>
                </template>
            </div>
        </div>
        <div class="pt-4 border-t border-white/10 text-[11px] text-slate-400">
            Gunakan panah keyboard (kiri/kanan) atau clicker PPT untuk berpindah slide.
        </div>
    </aside>

    <script>
        function livePresentation() {
            return {
                currentSlide: 1,
                totalSlides: 9,
                isAutoPlay: false,
                autoPlayTimer: null,
                isDrawerOpen: false,
                slideTitles: [
                    'Cover Judul Pelatihan',
                    'Demografi & Profil Kader',
                    'Agenda & Presensi Sesi',
                    'Evaluasi Kognitif (Pre/Post)',
                    'Praktik Ibadah & Sikap',
                    'Keputusan Yudisium',
                    'Top 5 Peserta Terbaik',
                    'Rencana Tindak Lanjut (RTL)',
                    'Penutup & Pesan Gerakan'
                ],
                init() {
                    // Cek parameter slide di URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const initial = parseInt(urlParams.get('slide'));
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
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },
                printPdf() {
                    window.print();
                },
                handleKeydown(e) {
                    if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ' || e.key === 'Enter') {
                        e.preventDefault();
                        this.nextSlide();
                    } else if (e.key === 'ArrowLeft' || e.key === 'PageUp' || e.key === 'Backspace') {
                        e.preventDefault();
                        this.prevSlide();
                    } else if (e.key === 'f' || e.key === 'F') {
                        e.preventDefault();
                        this.toggleFullscreen();
                    } else if (e.key === 'o' || e.key === 'O' || e.key === 'm' || e.key === 'M') {
                        e.preventDefault();
                        this.toggleDrawer();
                    } else if (e.key === 'p' || e.key === 'P') {
                        if (e.ctrlKey || e.metaKey || e.key === 'p') {
                            // Cetak PDF
                            e.preventDefault();
                            this.printPdf();
                        }
                    } else if (e.key === 'Escape') {
                        this.isDrawerOpen = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
