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
    </style>
</head>
<body class="min-h-full flex flex-col font-['Plus_Jakarta_Sans',sans-serif] antialiased text-slate-800 bg-[#f8fafc] selection:bg-[#063A60] selection:text-white relative overflow-x-hidden"
      x-data="attendanceDisplayApp()"
      @click="refocusInput($event)"
      @keydown="handleGlobalKey($event)">

    {{-- Global Page Transition & Smooth Preloader --}}
    <x-page-loader />

    {{-- Subtle Institutional Watermark Pattern (Opacity 2.5%) --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden opacity-[0.025]" aria-hidden="true">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="muh-pattern" width="80" height="80" patternUnits="userSpaceOnUse">
                    <circle cx="40" cy="40" r="14" fill="none" stroke="#063A60" stroke-width="1.5" />
                    <circle cx="40" cy="40" r="6" fill="#063A60" />
                    <path d="M40 10 L40 22 M40 58 L40 70 M10 40 L22 40 M58 40 L70 40 M19 19 L27 27 M53 53 L61 61 M19 61 L27 53 M53 27 L61 19" stroke="#063A60" stroke-width="1.5" stroke-linecap="round"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#muh-pattern)"/>
        </svg>
    </div>

    {{-- Main Container (16:9 Projector / Live Display Friendly) --}}
    <div class="relative z-10 w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex-1 flex flex-col gap-5">

        {{-- 1. HEADER SECTION (MINIMALIST & INSTITUTIONAL) --}}
        <header class="grid gap-4 border-b border-slate-200/80 pb-5 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
            <div class="min-w-0">
                <div class="flex min-w-0 items-center gap-2 text-[11px] sm:text-xs font-bold uppercase tracking-[0.08em] text-slate-500">
                    <span class="shrink-0 text-emerald-700">Presensi peserta</span>
                    <span class="text-slate-300">/</span>
                    <span class="truncate">
                    {{ $event->nama_event }}
                    </span>
                </div>
                <h1 class="mt-2 max-w-4xl text-2xl sm:text-3xl font-extrabold text-[#063A60] tracking-tight leading-tight">
                    {{ $sesi->nama_sesi }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs sm:text-sm font-medium text-slate-500">
                    <span class="inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 font-semibold text-[#063A60]">
                        Sesi {{ sprintf('%02d', $sesi->urutan) }}
                    </span>
                    <span>{{ ucfirst($sesi->jenis) }}</span>
                </div>
            </div>

            {{-- Top-Right Controls & Status --}}
            <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center lg:justify-end">
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Session Live Indicator --}}
                    <div class="inline-flex min-h-10 items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sesi berlangsung</span>
                    </div>

                    {{-- Live Digital Clock --}}
                    <div class="inline-flex min-h-10 items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs sm:text-sm font-mono font-bold text-slate-700"
                         x-text="clockText">
                        --:--:-- WIB
                    </div>
                </div>

                {{-- Action Controls (Subtle, Projector Friendly) --}}
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Mode Checklist Manual --}}
                    <a href="{{ route('admin.events.absensi.manual-page', [$event, $sesi]) }}"
                       class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-100"
                       title="Beralih ke Presensi Checklist Manual">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Checklist Manual</span>
                    </a>

                    {{-- Fullscreen Mode Toggle --}}
                    <button type="button"
                            @click="toggleFullscreen"
                            class="inline-flex min-h-10 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-slate-900"
                            title="Beralih ke Layar Penuh">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Layar Penuh'">Layar Penuh</span>
                    </button>

                    {{-- Back Link (Subtle) --}}
                    <a href="{{ route('admin.events.show', $event) }}"
                       class="inline-flex min-h-10 items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                       title="Kembali ke Halaman Event">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="grid flex-1 grid-cols-1 gap-5 lg:grid-cols-12 lg:items-stretch">
        {{-- 2. HERO SCANNER & CONFIRMATION SECTION (FOCAL POINT) --}}
        <main class="min-w-0 py-4 sm:py-6 lg:col-span-7 lg:flex lg:items-center lg:py-0">
            {{-- STATE A: IDLE / SCANNER HERO --}}
            <div x-show="feedbackState === 'idle'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-98" x-transition:enter-end="opacity-100 scale-100" class="w-full rounded-3xl border border-slate-200 bg-white p-5 text-center shadow-sm sm:p-8 lg:p-10">
                <div>
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-[#063A60] ring-1 ring-sky-100">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4zM15 15h2v2h-2zM19 15v5M15 19h2M11 4v2m0 3v2m4 2h2m3 0h1M11 15v5m3-5h-3"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                        Pindai QR peserta
                    </h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-relaxed text-slate-500 sm:text-base">
                        Arahkan QR pada kartu peserta ke scanner, atau masukkan kode secara manual.
                    </p>
                </div>

                {{-- Prominent Input (Auto-focused, No Submit Button) --}}
                <div class="relative mx-auto mt-6 max-w-2xl">
                    <div class="flex items-center overflow-hidden rounded-2xl border-2 border-slate-300 bg-white transition-all duration-200 focus-within:border-[#063A60] focus-within:ring-4 focus-within:ring-[#063A60]/10">
                        <div class="shrink-0 border-r border-slate-200 bg-slate-50 px-4 py-4 text-[#063A60] sm:px-5">
                            <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <input type="text"
                               x-ref="scanInput"
                               x-model="qrCode"
                               @keydown.enter.prevent="processScan"
                               autofocus
                               autocomplete="off"
                               spellcheck="false"
                               placeholder="Scan QR atau masukkan kode peserta..."
                               aria-label="Pindai atau masukkan kode peserta"
                               class="h-14 w-full border-none bg-transparent px-4 text-base font-mono text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0 sm:h-16 sm:text-lg">
                        <div x-show="isProcessing" class="pr-4 sm:pr-5 shrink-0" style="display: none;">
                            <svg class="w-5 h-5 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-2 text-xs font-semibold text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span>Scanner siap menerima QR</span>
                    </div>
                </div>
            </div>

            {{-- STATE B: SUCCESS CONFIRMATION (PROMINENT OVERLAY / CENTER PANEL) --}}
            <div x-show="feedbackState === 'success'"
                 x-cloak
                 class="animate-confirm max-w-2xl mx-auto bg-white rounded-2xl border-2 border-emerald-500/80 shadow-xl p-8 sm:p-12 text-center space-y-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 mx-auto flex items-center justify-center shadow-xs">
                    <svg class="w-9 h-9 sm:w-11 sm:h-11 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-emerald-700">
                    PRESENSI BERHASIL
                </div>

                {{-- Prominent Participant Name (36–48px) --}}
                <div class="text-3xl sm:text-4xl lg:text-5xl font-black text-[#063A60] tracking-tight leading-tight"
                     x-text="feedback.nama">
                    Nama Peserta
                </div>

                <p class="text-sm sm:text-base text-slate-600 max-w-lg mx-auto">
                    Terima kasih, Anda sudah melakukan presensi pada sesi ini.
                </p>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-center gap-3 text-xs sm:text-sm text-slate-500 font-medium">
                    <span>{{ $sesi->nama_sesi }}</span>
                    <span>&bull;</span>
                    <span class="font-mono font-semibold text-slate-700" x-text="feedback.waktu + ' WIB'">--:-- WIB</span>
                </div>
            </div>

            {{-- STATE C: DUPLICATE ATTENDANCE (GENTLE AMBER/ORANGE) --}}
            <div x-show="feedbackState === 'duplicate'"
                 x-cloak
                 class="animate-confirm max-w-2xl mx-auto bg-white rounded-2xl border-2 border-amber-400 shadow-xl p-8 sm:p-12 text-center space-y-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-amber-50 text-amber-600 border border-amber-200 mx-auto flex items-center justify-center shadow-xs">
                    <svg class="w-9 h-9 sm:w-11 sm:h-11 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-amber-700">
                    PRESENSI SUDAH TERCATAT
                </div>

                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 tracking-tight leading-tight"
                     x-text="feedback.nama">
                    Nama Peserta
                </div>

                <p class="text-sm sm:text-base text-slate-600">
                    Anda sudah melakukan presensi pada sesi ini.
                </p>

                <div class="pt-2 border-t border-slate-100 text-xs sm:text-sm text-amber-800 font-medium">
                    Terakhir tercatat: <span class="font-mono font-bold" x-text="feedback.waktu + ' WIB'">--:-- WIB</span>
                </div>
            </div>

            {{-- STATE D: INVALID QR (CLEAN SUBTLE RED) --}}
            <div x-show="feedbackState === 'invalid'"
                 x-cloak
                 class="animate-confirm max-w-2xl mx-auto bg-white rounded-2xl border-2 border-red-300 shadow-xl p-8 sm:p-10 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-red-50 text-red-600 border border-red-200 mx-auto flex items-center justify-center shadow-xs">
                    <svg class="w-8 h-8 stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <div class="text-xs sm:text-sm font-extrabold uppercase tracking-widest text-red-700">
                    QR CODE TIDAK DIKENALI
                </div>

                <p class="text-sm sm:text-base text-slate-600" x-text="feedback.pesan || 'Silakan coba scan kembali.'">
                    Silakan coba scan kembali.
                </p>
            </div>
        </main>

        {{-- 3. LIVE ATTENDANCE LIST SECTION --}}
        <section class="min-w-0 w-full lg:col-span-5">
            <div class="flex h-full min-h-[360px] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                {{-- List Header --}}
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4 sm:px-6">
                    <h3 class="text-sm font-bold text-slate-900 sm:text-base">
                        Peserta yang sudah hadir
                    </h3>
                    <div class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        <span class="font-extrabold text-[#063A60]" x-text="totalHadir">0</span>
                        <span>hadir</span>
                    </div>
                </div>

                {{-- List Content (Scrollable, 3 Simple Columns) --}}
                <div class="min-h-[280px] max-h-[55vh] flex-1 divide-y divide-slate-100 overflow-y-auto lg:max-h-[calc(100vh-290px)]">
                    {{-- Empty State --}}
                    <template x-if="records.length === 0">
                        <div class="flex min-h-[280px] flex-col items-center justify-center px-6 py-10 text-center">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 01-4 4H8a4 4 0 110-8h2a4 4 0 014 4z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">Belum ada peserta hadir</p>
                            <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-500">Hasil scan akan muncul di sini secara langsung.</p>
                        </div>
                    </template>

                    {{-- Rows --}}
                    <template x-for="item in records" :key="item.id">
                        <div class="px-5 sm:px-6 py-3 flex items-center justify-between gap-4 transition duration-300"
                             :class="item.isNew ? 'bg-emerald-50/70' : 'hover:bg-slate-50/70'">
                            {{-- Col 1: Time --}}
                            <div class="w-16 sm:w-20 shrink-0 font-mono text-xs sm:text-sm text-slate-500 font-normal"
                                 x-text="item.waktu">
                                00:00
                            </div>

                            {{-- Col 2: Participant Name --}}
                            <div class="flex-1 min-w-0 font-medium sm:font-semibold text-slate-900 text-xs sm:text-sm truncate"
                                 x-text="item.nama">
                                Nama Peserta
                            </div>

                            {{-- Col 3: Success Status --}}
                            <div class="shrink-0 flex items-center gap-1 text-xs sm:text-sm font-semibold text-emerald-700">
                                <span>Berhasil</span>
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </template>
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
            isOnline: navigator.onLine,
            isFullscreen: false,
            clockText: '',
            clockInterval: null,
            storageKey: 'arqam_attendance_queue_{{ $sesi->id }}',

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
