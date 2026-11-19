<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8fafc]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi: {{ $sesi->nama_sesi }} — {{ $event->nama_event }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/logo-mpksdi.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.97); }
            100% { opacity: 1; transform: scale(1); }
        }
        .animate-confirm {
            animation: fadeInScale 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .scan-card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .scan-card-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        .scan-stream-list {
            max-height: calc(100vh - 430px);
            min-height: 280px;
        }
        @media (max-height: 800px) {
            .scan-stream-list {
                max-height: calc(100vh - 460px);
                min-height: 220px;
            }
        }
        .scan-stream-list::-webkit-scrollbar {
            width: 6px;
        }
        .scan-stream-list::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .scan-stream-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .scan-stream-list::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="min-h-full flex flex-col font-['Plus_Jakarta_Sans',sans-serif] antialiased text-slate-800 bg-[#f8fafc] selection:bg-[#063A60] selection:text-white relative overflow-x-hidden"
      x-data="attendanceDisplayApp()"
      @click="refocusInput($event)"
      @keydown="handleGlobalKey($event)">

    {{-- Global Page Transition & Smooth Preloader --}}
    <x-page-loader />

    {{-- Clean Enterprise Background with Subtle Radial Ambient --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden bg-gradient-to-br from-slate-50 via-[#f8fafc] to-sky-50/40" aria-hidden="true"></div>

    {{-- Main Container (16:9 Projector / Live Display Friendly) --}}
    <div class="relative z-10 w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex-1 flex flex-col gap-6">

        {{-- 1. HEADER SECTION (EXECUTIVE INSTITUTIONAL COMMAND BAR) --}}
        <header class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 sm:p-6 transition-all">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                {{-- Left: Session Identity & Event Context --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/70 text-[11px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Presensi Peserta
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="truncate font-medium text-slate-600 max-w-xl">{{ $event->nama_event }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-black text-[#063A60] tracking-tight leading-snug">
                        {{ $sesi->nama_sesi }}
                    </h1>

                    <div class="mt-2.5 flex flex-wrap items-center gap-2.5 text-xs text-slate-600">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 text-[#063A60] border border-sky-200 font-bold">
                            Sesi {{ sprintf('%02d', $sesi->urutan) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold capitalize">
                            {{ $sesi->jenis }}
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="font-medium text-slate-500">
                            Total Terdaftar: <strong class="text-slate-800">{{ $totalPeserta }}</strong> Kader
                        </span>
                    </div>
                </div>

                {{-- Right: Live Indicators & Action Toolbar --}}
                <div class="flex flex-col sm:flex-row sm:items-center lg:flex-col lg:items-end gap-3 shrink-0">
                    {{-- Status Pills --}}
                    <div class="flex flex-wrap items-center gap-2">
                        {{-- Live Indicator --}}
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Sesi berlangsung</span>
                        </div>

                        {{-- Digital Clock --}}
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-emerald-400 font-mono font-bold text-xs sm:text-sm tracking-wider shadow-inner"
                             x-text="clockText">
                            --:--:-- WIB
                        </div>
                    </div>

                    {{-- Actions Button Group --}}
                    <div class="flex items-center gap-2">
                        {{-- Mode Checklist Manual --}}
                        <a href="{{ route('admin.events.absensi.manual-page', [$event, $sesi]) }}"
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm hover:shadow transition"
                           title="Beralih ke Presensi Checklist Manual">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Checklist Manual</span>
                        </a>

                        {{-- Fullscreen Toggle --}}
                        <button type="button"
                                @click="toggleFullscreen"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs shadow-2xs transition"
                                title="Beralih ke Layar Penuh">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'">Layar Penuh</span>
                        </button>

                        {{-- Back Button --}}
                        <a href="{{ route('admin.events.show', $event) }}"
                           class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 font-semibold text-xs shadow-2xs transition"
                           title="Kembali ke Detail Event">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        {{-- 2. MAIN WORKSPACE (EQUAL HEIGHT TWO-COLUMN ARCHITECTURE) --}}
        <div class="grid flex-1 grid-cols-1 gap-6 lg:grid-cols-12 lg:items-stretch">
            
            {{-- COLUMN LEFT: SCANNER TERMINAL HERO (7 COLS) --}}
            <main class="min-w-0 lg:col-span-7 flex flex-col">
                {{-- CARD CONTAINER WITH FULL HEIGHT --}}
                <div class="flex-1 flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm relative">
                    
                    {{-- Ambient Top Glow --}}
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-gradient-to-b from-sky-100/50 to-transparent rounded-full blur-3xl pointer-events-none"></div>

                    {{-- Card Header Bar (Symmetrical with Right Card) --}}
                    <div class="scan-card-header px-6 py-4 flex items-center justify-between shrink-0 relative z-10">
                        <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#063A60]">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Terminal Scanner Presensi</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 bg-white border border-slate-200/80 px-2.5 py-1 rounded-lg shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            <span>Auto-Focus Scanner Aktif</span>
                        </span>
                    </div>

                    {{-- Interactive Scanner Workspace Area --}}
                    <div class="flex-1 flex flex-col justify-center p-6 relative z-10">
                        {{-- STATE A: IDLE / SCANNER HERO --}}
                        <div x-show="feedbackState === 'idle'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-98"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="flex-1 flex flex-col justify-center items-center text-center">

                            {{-- Viewfinder Target Box --}}
                            <div class="max-w-md w-full mx-auto rounded-3xl border-2 border-dashed border-sky-300 bg-gradient-to-b from-sky-50/70 via-sky-50/30 to-white p-5 relative shadow-inner">
                                {{-- Target Corner Accents --}}
                                <div class="absolute top-2.5 left-2.5 w-4 h-4 border-t-2 border-l-2 border-[#063A60] rounded-tl-md"></div>
                                <div class="absolute top-2.5 right-2.5 w-4 h-4 border-t-2 border-r-2 border-[#063A60] rounded-tr-md"></div>
                                <div class="absolute bottom-2.5 left-2.5 w-4 h-4 border-b-2 border-l-2 border-[#063A60] rounded-bl-md"></div>
                                <div class="absolute bottom-2.5 right-2.5 w-4 h-4 border-b-2 border-r-2 border-[#063A60] rounded-br-md"></div>

                                {{-- Icon --}}
                                <div class="mx-auto mb-2.5 flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#063A60] shadow-sm border border-sky-100 ring-4 ring-sky-50">
                                    <svg class="h-6 w-6 text-[#063A60]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-[#063A60]">
                                    Pindai QR peserta
                                </h2>

                                <p class="mx-auto mt-1 text-xs leading-relaxed text-slate-500 max-w-xs">
                                    Arahkan scanner ke kartu peserta atau ketik kode ID / NBM manual di bawah.
                                </p>

                                {{-- High-Visibility Input Box Inside Target Focal Area --}}
                                <div class="mt-4">
                                    <div class="flex items-center overflow-hidden rounded-2xl border-2 border-[#063A60]/30 bg-white shadow-sm transition-all duration-200 focus-within:border-[#063A60] focus-within:ring-4 focus-within:ring-[#063A60]/10">
                                        <div class="shrink-0 border-r border-slate-200 bg-slate-50 px-3.5 py-2.5 text-[#063A60]">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <input type="text"
                                               x-ref="scanInput"
                                               x-model="qrCode"
                                               @keydown.enter.prevent="processScan"
                                               autofocus
                                               autocomplete="off"
                                               spellcheck="false"
                                               placeholder="Scan QR atau ketik kode peserta..."
                                               aria-label="Pindai atau masukkan kode peserta"
                                               class="h-11 w-full border-none bg-transparent px-3 text-sm sm:text-base font-mono font-medium text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0">
                                        
                                        <div x-show="isProcessing" class="pr-3.5 shrink-0" style="display: none;">
                                            <svg class="w-5 h-5 text-[#063A60] animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex items-center justify-center gap-2 text-xs font-semibold text-emerald-800">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Scanner aktif & siap menerima input</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Functional Quick Instruction Cards Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 max-w-md w-full mx-auto mt-4 text-left">
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-sky-100 text-[#063A60] flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold text-slate-800">Barcode Gun</div>
                                        <div class="text-[10px] text-slate-500 leading-tight">Auto-scan langsung</div>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold text-slate-800">Input Manual</div>
                                        <div class="text-[10px] text-slate-500 leading-tight">NBM/ID lalu Enter</div>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-900 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-bold text-slate-800">Offline Safe</div>
                                        <div class="text-[10px] text-slate-500 leading-tight">Sinkron otomatis</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- STATE B: SUCCESS CONFIRMATION (PROMINENT OVERLAY / CENTER PANEL) --}}
                    <div x-show="feedbackState === 'success'"
                         x-cloak
                         class="animate-confirm my-auto max-w-xl mx-auto w-full bg-white rounded-3xl border-2 border-emerald-500 shadow-2xl p-8 sm:p-12 text-center space-y-4">
                        <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 border-2 border-emerald-200 mx-auto flex items-center justify-center shadow-md">
                            <svg class="w-12 h-12 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>

                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-black uppercase tracking-widest">
                            <span>PRESENSI BERHASIL</span>
                        </div>

                        {{-- Prominent Participant Name --}}
                        <div class="text-3xl sm:text-4xl font-black text-[#063A60] tracking-tight leading-tight"
                             x-text="feedback.nama">
                            Nama Peserta
                        </div>

                        <p class="text-sm sm:text-base text-slate-600 max-w-md mx-auto">
                            Terima kasih, kehadiran Anda telah berhasil diverifikasi pada sesi ini.
                        </p>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-3 text-xs sm:text-sm text-slate-500 font-semibold">
                            <span>{{ $sesi->nama_sesi }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-mono text-emerald-700 font-bold" x-text="feedback.waktu + ' WIB'">--:-- WIB</span>
                        </div>
                    </div>

                    {{-- STATE C: DUPLICATE ATTENDANCE (GENTLE AMBER/ORANGE) --}}
                    <div x-show="feedbackState === 'duplicate'"
                         x-cloak
                         class="animate-confirm my-auto max-w-xl mx-auto w-full bg-white rounded-3xl border-2 border-amber-400 shadow-2xl p-8 sm:p-12 text-center space-y-4">
                        <div class="w-20 h-20 rounded-full bg-amber-50 text-amber-600 border-2 border-amber-200 mx-auto flex items-center justify-center shadow-md">
                            <svg class="w-12 h-12 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>

                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-widest">
                            <span>SUDAH MELAKUKAN PRESENSI</span>
                        </div>

                        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight"
                             x-text="feedback.nama">
                            Nama Peserta
                        </div>

                        <p class="text-sm sm:text-base text-slate-600">
                            Peserta ini sudah tercatat hadir sebelumnya pada sesi ini.
                        </p>

                        <div class="pt-4 border-t border-slate-100 text-xs sm:text-sm text-amber-800 font-medium">
                            Tercatat pada: <span class="font-mono font-bold" x-text="feedback.waktu + ' WIB'">--:-- WIB</span>
                        </div>
                    </div>

                    {{-- STATE D: INVALID QR (CLEAN SUBTLE RED) --}}
                    <div x-show="feedbackState === 'invalid'"
                         x-cloak
                         class="animate-confirm my-auto max-w-xl mx-auto w-full bg-white rounded-3xl border-2 border-rose-300 shadow-2xl p-8 sm:p-10 text-center space-y-4">
                        <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center shadow-xs">
                            <svg class="w-8 h-8 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>

                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-900 text-xs font-black uppercase tracking-widest">
                            <span>QR CODE TIDAK DIKENALI</span>
                        </div>

                        <p class="text-sm sm:text-base text-slate-600" x-text="feedback.pesan || 'Silakan coba scan kembali atau periksa ID peserta.'">
                            Silakan coba scan kembali atau periksa ID peserta.
                        </p>
                    </div>

                    {{-- End Interactive Scanner Workspace Area --}}
                    </div>

                    {{-- Bottom Quick Advice Bar (Symmetrical with Right Card Footer) --}}
                    <div class="scan-card-footer px-6 py-3 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 shrink-0 relative z-10">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Perlu presensi cepat banyak peserta?</span>
                        </div>
                        <a href="{{ route('admin.events.absensi.manual-page', [$event, $sesi]) }}" class="font-bold text-amber-700 hover:text-amber-800 hover:underline">
                            Buka Mode Checklist Massal &rarr;
                        </a>
                    </div>

                </div>
            </main>

            {{-- COLUMN RIGHT: LIVE ATTENDANCE STREAM & PROGRESS (5 COLS) --}}
            <section class="min-w-0 lg:col-span-5 flex flex-col">
                <div class="flex-1 flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                    
                    {{-- Stream Card Header --}}
                    <div class="scan-card-header p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-black text-slate-900">
                                    Peserta yang sudah hadir
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Daftar kehadiran real-time sesi ini
                                </p>
                            </div>
                            <div class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-100 text-[#063A60] font-black text-xs">
                                <span x-text="totalHadir">0</span>
                                <span>/</span>
                                <span>{{ $totalPeserta }}</span>
                                <span class="font-normal text-sky-700 ml-1">Hadir</span>
                            </div>
                        </div>

                        {{-- Attendance Progress Bar --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
                                <span>Tingkat Kehadiran</span>
                                <span x-text="attendancePercent + '%'">0%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-[#063A60] rounded-full transition-all duration-500"
                                     :style="'width: ' + attendancePercent + '%'"></div>
                            </div>
                        </div>

                        {{-- Search Filter in Stream --}}
                        <div class="mt-3 relative">
                            <input type="text"
                                   x-model="searchQuery"
                                   placeholder="Cari nama peserta hadir..."
                                   class="w-full h-9 pl-8 pr-3 text-xs rounded-lg border border-slate-200 bg-white placeholder:text-slate-400 focus:outline-none focus:border-[#063A60] focus:ring-1 focus:ring-[#063A60]">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Stream Card Content (Scrollable) --}}
                    <div class="flex-1 scan-stream-list overflow-y-auto divide-y divide-slate-100" style="max-height: calc(100vh - 430px); min-height: 320px;">
                        {{-- Empty State --}}
                        <template x-if="filteredRecords.length === 0">
                            <div class="flex min-h-[300px] flex-col items-center justify-center p-8 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-sky-50 text-[#063A60] flex items-center justify-center mb-3 shadow-inner">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800" x-text="records.length === 0 ? 'Belum Ada Peserta Hadir' : 'Tidak Ditemukan'">
                                    Belum Ada Peserta Hadir
                                </h4>
                                <p class="text-xs text-slate-400 max-w-xs mt-1" x-text="records.length === 0 ? 'Peserta yang berhasil scan QR akan langsung muncul di daftar ini.' : 'Tidak ada nama yang cocok dengan kata kunci pencarian.'">
                                    Peserta yang berhasil scan QR akan langsung muncul di daftar ini.
                                </p>
                            </div>
                        </template>

                        {{-- Scanned Attendees List --}}
                        <template x-for="(item, index) in filteredRecords" :key="item.id || index">
                            <div class="px-5 py-3 flex items-center justify-between gap-3.5 transition duration-200"
                                 :class="item.isNew ? 'bg-emerald-50/90' : 'hover:bg-slate-50/80'">
                                
                                {{-- Left: Number & Avatar --}}
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200"
                                         x-text="item.nama ? item.nama.charAt(0).toUpperCase() : '?'">
                                        A
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="item.nama">
                                            Nama Peserta
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono" x-text="item.waktu + ' WIB'">
                                            08:15 WIB
                                        </div>
                                    </div>
                                </div>

                                {{-- Right: Status Chip --}}
                                <div class="shrink-0 flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Hadir</span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Bottom Summary Footer --}}
                    <div class="scan-card-footer p-3 text-center text-[11px] text-slate-500 font-medium">
                        Menampilkan <strong x-text="filteredRecords.length">0</strong> dari <strong x-text="totalHadir">0</strong> kehadiran tercatat
                    </div>

                </div>
            </section>

        </div>

        {{-- Offline Notification (Subtle at bottom) --}}
        <div x-show="!isOnline" x-cloak class="mt-4 max-w-3xl mx-auto w-full text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span>Mode Offline: Presensi tetap dicatat di penyimpanan lokal dan otomatis terkirim saat internet kembali terhubung.</span>
            </span>
        </div>
    </div>

    {{-- Reactive Controller Script --}}
    <script>
    function attendanceDisplayApp() {
        return {
            qrCode: '',
            isProcessing: false,
            feedbackState: 'idle', // 'idle' | 'success' | 'duplicate' | 'invalid'
            feedback: {
                nama: '',
                waktu: '',
                pesan: ''
            },
            resetTimer: null,
            totalHadir: {{ (int) $totalHadir }},
            records: [],
            searchQuery: '',
            isOnline: navigator.onLine,
            isFullscreen: false,
            clockText: '',
            clockInterval: null,
            storageKey: 'arqam_attendance_queue_{{ $sesi->id }}',

            get attendancePercent() {
                const total = {{ max(1, (int) $totalPeserta) }};
                return Math.min(100, Math.round((this.totalHadir / total) * 100));
            },

            get filteredRecords() {
                if (!this.searchQuery.trim()) return this.records;
                const q = this.searchQuery.toLowerCase();
                return this.records.filter(r => (r.nama || '').toLowerCase().includes(q));
            },

            init() {
                this.updateClock();
                this.clockInterval = setInterval(() => this.updateClock(), 1000);

                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.syncOfflineQueue();
                });
                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });

                document.addEventListener('fullscreenchange', () => {
                    this.isFullscreen = !!document.fullscreenElement;
                });

                this.loadRecent();
                this.syncOfflineQueue();

                // Initial autofocus
                this.$nextTick(() => {
                    this.focusInput();
                });
            },

            updateClock() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                this.clockText = `${h}:${m}:${s} WIB`;
            },

            focusInput() {
                if (this.$refs.scanInput) {
                    this.$refs.scanInput.focus();
                }
            },

            refocusInput(e) {
                // If user clicks anywhere that is not a button or link, refocus scanner input
                if (e.target.closest('button, a, input')) return;
                this.focusInput();
            },

            handleGlobalKey(e) {
                // If not typing in input, focus input automatically on alphanumeric keypress
                if (document.activeElement !== this.$refs.scanInput && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    this.focusInput();
                }
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            },

            async processScan() {
                const token = this.qrCode.trim();
                if (!token || this.isProcessing) return;

                this.isProcessing = true;
                const scannedToken = token;
                this.qrCode = '';

                // Handle Offline State
                if (!navigator.onLine) {
                    this.handleOfflineRecord(scannedToken);
                    this.isProcessing = false;
                    return;
                }

                try {
                    const response = await fetch('{{ route('admin.events.absensi.process', $event) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            qr_code: scannedToken,
                            sesi_id: {{ $sesi->id }}
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.status === 'success') {
                        this.showSuccess(data);
                    } else if (response.ok && data.status === 'duplicate') {
                        this.showDuplicate(data);
                    } else {
                        this.showInvalid(data.message || 'QR Code tidak dikenali');
                    }
                } catch (err) {
                    // Fallback to offline queue if network fails
                    this.handleOfflineRecord(scannedToken);
                } finally {
                    this.isProcessing = false;
                }
            },

            showSuccess(data) {
                clearTimeout(this.resetTimer);
                const waktuFormatted = this.formatTime(data.waktu_scan);

                this.feedback = {
                    nama: data.nama,
                    waktu: waktuFormatted,
                    pesan: 'Terima kasih, Anda sudah melakukan presensi pada sesi ini.'
                };
                this.feedbackState = 'success';

                // Add to live records if not already in front
                const existingIndex = this.records.findIndex(r => r.nama === data.nama);
                if (existingIndex === -1) {
                    this.records.unshift({
                        id: Date.now(),
                        nama: data.nama,
                        waktu: waktuFormatted,
                        isNew: true
                    });
                    this.totalHadir++;

                    // Clear highlight after 1.5s
                    setTimeout(() => {
                        if (this.records.length > 0) {
                            this.records[0].isNew = false;
                        }
                    }, 1500);
                }

                // Return to scanner state after 3.5s
                this.resetTimer = setTimeout(() => {
                    this.feedbackState = 'idle';
                    this.$nextTick(() => this.focusInput());
                }, 3500);
            },

            showDuplicate(data) {
                clearTimeout(this.resetTimer);
                const waktuFormatted = this.formatTime(data.waktu_scan);

                this.feedback = {
                    nama: data.nama,
                    waktu: waktuFormatted,
                    pesan: 'Anda sudah melakukan presensi pada sesi ini.'
                };
                this.feedbackState = 'duplicate';

                this.resetTimer = setTimeout(() => {
                    this.feedbackState = 'idle';
                    this.$nextTick(() => this.focusInput());
                }, 3500);
            },

            showInvalid(message) {
                clearTimeout(this.resetTimer);
                this.feedback = {
                    nama: '',
                    waktu: '',
                    pesan: message || 'Silakan coba scan kembali.'
                };
                this.feedbackState = 'invalid';

                this.resetTimer = setTimeout(() => {
                    this.feedbackState = 'idle';
                    this.$nextTick(() => this.focusInput());
                }, 2500);
            },

            formatTime(raw) {
                if (!raw) {
                    const now = new Date();
                    return String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                }
                // If contains 'H:i:s' or 'H:i'
                const match = raw.match(/(\d{2}:\d{2})/);
                return match ? match[1] : raw;
            },

            handleOfflineRecord(token) {
                try {
                    const queue = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                    queue.push({
                        qr_code: token,
                        sesi_id: {{ $sesi->id }},
                        timestamp: new Date().toISOString()
                    });
                    localStorage.setItem(this.storageKey, JSON.stringify(queue));
                } catch (e) {}

                this.showSuccess({
                    nama: 'Peserta (Offline)',
                    waktu_scan: this.formatTime(null)
                });
            },

            async syncOfflineQueue() {
                if (!navigator.onLine) return;
                let queue = [];
                try {
                    queue = JSON.parse(localStorage.getItem(this.storageKey) || '[]');
                } catch (e) {
                    queue = [];
                }

                if (queue.length === 0) return;

                const remaining = [];
                for (const item of queue) {
                    try {
                        await fetch('{{ route('admin.events.absensi.process', $event) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(item)
                        });
                    } catch (e) {
                        remaining.push(item);
                    }
                }

                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(remaining));
                } catch (e) {}

                this.loadRecent();
            },

            async loadRecent() {
                if (!navigator.onLine) return;
                try {
                    const res = await fetch('{{ route('admin.events.absensi.recent', [$event, $sesi]) }}');
                    const data = await res.json();
                    if (data.records) {
                        this.records = data.records.map(r => ({
                            id: r.id,
                            nama: r.nama,
                            waktu: this.formatTime(r.waktu),
                            isNew: false
                        }));
                    }
                    if (typeof data.total === 'number') {
                        this.totalHadir = data.total;
                    }
                } catch (e) {}
            }
        };
    }
    </script>
</body>
</html>
