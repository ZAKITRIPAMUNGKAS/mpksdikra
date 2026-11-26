@extends('layouts.app')

@section('title', ucfirst($tipe) . ' — ' . $event->nama_event)

@section('content')
@php
    $attemptTimestamp = isset($mulai) && $mulai->waktu_mulai ? $mulai->waktu_mulai->getTimestamp() : 0;
    $attemptStorageKey = 'arqam_cbt_v_' . $event->id . '_' . $tipe . '_' . auth()->id() . '_' . $attemptTimestamp;
@endphp
<div class="max-w-6xl mx-auto space-y-4 sm:space-y-6 select-none"
    style="-webkit-user-select: none; -webkit-touch-callout: none; user-select: none;"
    @contextmenu.prevent
    @copy.prevent="recordViolation('Mencoba menyalin teks soal ujian.')"
    @cut.prevent="recordViolation('Mencoba memotong teks soal ujian.')"
    @paste.prevent="recordViolation('Mencoba menempel teks dari luar saat ujian.')"
    @dragstart.prevent
    @selectstart.prevent
    x-data="cbtExam({
        total: {{ $questions->count() }},
        initialRemaining: {{ $remaining }},
        autosaveUrl: '{{ route('portal.tes.autosave', [$event, $tipe]) }}',
        savedAnswers: @js((object) $savedAnswers),
        storageKey: '{{ $attemptStorageKey }}'
    })">

    {{-- Top Sticky CBT Header & Timer Bar (Menempel rapi di bawah header di HP) --}}
    <div class="sticky top-14 lg:top-16 z-30 -mx-3.5 -mt-4 px-3.5 py-2.5 lg:mx-0 lg:mt-0 lg:p-4 bg-white/95 backdrop-blur-md border-b border-slate-200/90 lg:rounded-xl lg:border lg:shadow-xs transition-all">
        <div class="flex items-center justify-between gap-2.5 sm:gap-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider {{ $tipe === 'pretest' ? 'bg-sky-50 text-sky-800 border border-sky-200/80' : 'bg-amber-50 text-amber-900 border border-amber-300/80' }}">
                        {{ strtoupper($tipe) }}
                    </span>
                    <span class="text-xs font-semibold text-emerald-700 whitespace-nowrap" x-text="`${answeredCount}/${total} terjawab`"></span>
                    <span x-show="draftSaveFailed" style="display: none;" class="text-xs font-semibold text-rose-700" role="status">
                        Sebagian jawaban belum tersimpan.
                    </span>
                    <span x-show="violations > 0" style="display: none;"
                        class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-bold whitespace-nowrap"
                        x-text="`Peringatan ${violations}/2`"></span>
                </div>
                <h1 class="hidden sm:block text-base sm:text-lg font-bold text-slate-900 tracking-tight mt-1 truncate">{{ $event->nama_event }}</h1>
            </div>

            {{-- Timer Counter & Mobile Navigator Trigger --}}
            <div class="flex items-center gap-2 shrink-0">
                <div class="inline-flex items-center gap-1.5 px-2.5 sm:px-3.5 py-1.5 rounded-lg border font-mono transition-colors"
                    :class="remaining < 60 ? 'bg-rose-50 border-rose-300 text-rose-700 animate-pulse' : 'bg-slate-50 border-slate-200 text-slate-900'">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm sm:text-base font-bold tabular-nums" x-text="timerDisplay"></span>
                </div>

                {{-- Mobile Navigator Trigger --}}
                <button type="button" @click="mobileNavOpen = true" class="lg:hidden min-h-10 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Soal</span>
                </button>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-slate-100 rounded-full h-1 mt-2.5 overflow-hidden">
            <div class="bg-sky-600 h-1 rounded-full transition-all duration-300"
                :style="`width: ${(answeredCount / total) * 100}%`"></div>
        </div>
    </div>

    {{-- Main Grid: Soal List + Sidebar Navigator --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
        {{-- Left: List of Questions --}}
        <div class="lg:col-span-8 space-y-3.5 sm:space-y-5">
            <form method="POST" action="{{ route('portal.tes.submit', [$event, $tipe]) }}" id="tes-form" class="space-y-3.5 sm:space-y-5">
                @csrf
                <input type="hidden" name="auto_submitted" id="auto-submitted-input" value="0">
                <input type="hidden" name="timed_out" id="timed-out-input" value="0">

                @foreach($questions as $i => $s)
                <div id="soal-card-{{ $i + 1 }}" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 sm:p-6 transition-colors duration-150 scroll-mt-36">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg font-bold text-xs flex items-center justify-center shrink-0 transition-colors"
                                :class="answers['{{ $s->id }}'] ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-300'">
                                {{ $i + 1 }}
                            </span>
                            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-500">Soal {{ $i + 1 }} dari {{ $questions->count() }}</span>
                        </div>
                        <span x-show="answers['{{ $s->id }}']" style="display: none;" class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                            ✓ Terjawab
                        </span>
                    </div>

                    <p class="font-bold text-slate-900 text-[15px] sm:text-lg leading-snug mb-3.5">{{ $s->teks_soal }}</p>

                    <input type="hidden" name="answers[{{ $s->id }}][soal_id]" value="{{ $s->id }}">

                    <div class="space-y-2 sm:space-y-2.5">
                        @foreach($s->pilihan as $p)
                        <label class="group relative flex items-center gap-3 p-3 sm:p-3.5 rounded-xl border bg-white hover:border-sky-400 hover:bg-sky-50/40 cursor-pointer transition-all duration-150 min-h-[50px]"
                            :class="answers['{{ $s->id }}'] == '{{ $p->id }}' ? 'border-sky-600 ring-1 ring-sky-600 bg-sky-50/70 text-sky-950' : 'border-slate-200'">
                            
                            <input type="radio" name="answers[{{ $s->id }}][pilihan_id]" value="{{ $p->id }}"
                                @checked(($savedAnswers[$s->id] ?? null) == $p->id)
                                :checked="answers['{{ $s->id }}'] == '{{ $p->id }}'"
                                @change="registerAnswer('{{ $s->id }}', '{{ $p->id }}')"
                                class="sr-only">

                            {{-- Option Badge Letter --}}
                            <span class="w-8 h-8 rounded-lg text-xs sm:text-sm font-extrabold flex items-center justify-center shrink-0 transition-colors"
                                :class="answers['{{ $s->id }}'] == '{{ $p->id }}' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200 group-hover:bg-sky-100 group-hover:text-sky-900'">
                                {{ $p->label }}
                            </span>

                            {{-- Option Text --}}
                            <span class="text-sm sm:text-base font-medium text-slate-800 group-hover:text-slate-950 leading-snug flex-1">
                                {{ $p->teks }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- Bottom Mobile Fixed Submit Bar (Selalu menempel rapat di bawah layar) --}}
                <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 px-4 py-2.5 pb-safe bg-white/95 backdrop-blur-md border-t border-slate-200/90 shadow-[0_-4px_16px_rgba(15,23,42,0.06)] flex items-center justify-between gap-3">
                    <div class="text-xs text-slate-600 font-medium">
                        Terjawab <strong class="text-slate-900" x-text="`${answeredCount}/${total}`"></strong>
                    </div>
                    <button type="button" @click="confirmSubmit()"
                        class="min-h-11 px-5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold text-sm rounded-xl shadow-xs transition cursor-pointer">
                        Kumpulkan &rarr;
                    </button>
                </div>
            </form>
        </div>

        {{-- Right: Desktop Sticky Question Navigator --}}
        <div class="hidden lg:block lg:col-span-4 sticky top-44 space-y-4">
            <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md p-6">
                <div class="flex items-center justify-between pb-3 border-b-2 border-slate-100 mb-4">
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Navigator Soal</span>
                    </h2>
                    <span class="text-xs sm:text-sm font-bold text-sky-800 bg-sky-100 px-3 py-1 rounded-full border border-sky-200">
                        <span x-text="answeredCount"></span>/<span x-text="total"></span>
                    </span>
                </div>

                {{-- Navigator Grid --}}
                <div class="grid grid-cols-5 gap-2.5">
                    @foreach($questions as $i => $s)
                    <button type="button" @click="scrollToSoal({{ $i + 1 }})"
                        class="h-10 rounded-xl text-xs sm:text-sm font-black transition flex flex-col items-center justify-center border-2 shadow-xs"
                        :class="answers['{{ $s->id }}'] ? 'bg-sky-600 text-white border-sky-700 shadow-xs' : 'bg-slate-50 text-slate-800 border-slate-300 hover:bg-sky-50 hover:border-sky-300'">
                        <span>{{ $i + 1 }}</span>
                    </button>
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="pt-4 mt-4 border-t-2 border-slate-100 flex items-center justify-between text-xs sm:text-sm text-slate-600 font-medium">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-sky-600"></span>
                        <span>Sudah Diisi</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-slate-100 border-2 border-slate-300"></span>
                        <span>Belum Diisi</span>
                    </div>
                </div>

                {{-- Submit Action --}}
                <div class="mt-5 pt-4 border-t-2 border-slate-100">
                    <button type="button" @click="confirmSubmit()"
                        class="w-full min-h-[52px] bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-base rounded-2xl shadow-md transition">
                        Kumpulkan Jawaban &rarr;
                    </button>
                    <p class="text-xs text-slate-500 text-center mt-2 font-medium">Pastikan semua butir telah terjawab sebelum mengumpulkan.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Bottom Drawer Navigator Modal --}}
    <div x-show="mobileNavOpen" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end justify-center p-0 sm:p-4 lg:hidden" style="display: none;">
        <div @click.away="mobileNavOpen = false" class="w-full max-w-lg bg-white rounded-t-2xl sm:rounded-2xl p-6 shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto border-t-2 sm:border-2 border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b-2 border-slate-100">
                <h3 class="text-lg font-black text-slate-900">Daftar Nomor Soal</h3>
                <button type="button" @click="mobileNavOpen = false" class="p-2 rounded-xl text-slate-500 hover:text-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-5 gap-2.5">
                @foreach($questions as $i => $s)
                <button type="button" @click="scrollToSoal({{ $i + 1 }}); mobileNavOpen = false"
                    class="h-10 rounded-xl text-xs sm:text-sm font-black transition flex items-center justify-center border-2 shadow-xs"
                    :class="answers['{{ $s->id }}'] ? 'bg-sky-600 text-white border-sky-700' : 'bg-slate-50 text-slate-800 border-slate-300'">
                    <span>{{ $i + 1 }}</span>
                </button>
                @endforeach
            </div>

            <div class="pt-4 border-t-2 border-slate-100 flex justify-between items-center text-xs sm:text-sm text-slate-600">
                <span>Terisi: <strong class="text-slate-900 text-base" x-text="answeredCount"></strong> dari {{ $questions->count() }} soal</span>
                <button type="button" @click="mobileNavOpen = false; confirmSubmit()" class="min-h-[46px] px-5 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-sm">
                    Kumpulkan &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal before Submit --}}
    <div x-show="showConfirmModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showConfirmModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 sm:p-7 shadow-2xl space-y-4 border-2 border-slate-200">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                :class="unansweredCount > 0 ? 'bg-amber-100 text-amber-600' : 'bg-sky-100 text-sky-700'">
                <svg x-show="unansweredCount > 0" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <svg x-show="unansweredCount === 0" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>

            <div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight" x-text="unansweredCount > 0 ? 'Masih Ada Soal Kosong!' : 'Konfirmasi Kumpulkan Jawaban'"></h3>
                <p class="text-xs sm:text-sm text-slate-700 mt-2 leading-relaxed font-medium">
                    <template x-if="unansweredCount > 0">
                        <span>Anda telah menjawab <strong class="text-slate-900" x-text="answeredCount"></strong> soal, namun masih ada <strong class="text-amber-700" x-text="unansweredCount"></strong> butir soal yang belum terjawab. Yakin ingin mengumpulkan sekarang?</span>
                    </template>
                    <template x-if="unansweredCount === 0">
                        <span>Alhamdulillah, seluruh <strong>{{ $questions->count() }}</strong> butir soal telah terjawab lengkap. Apakah Anda ingin mengakhiri dan mengumpulkan jawaban ujian sekarang?</span>
                    </template>
                </p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-4 border-t-2 border-slate-100">
                <button type="button" @click="showConfirmModal = false"
                    class="w-full sm:w-auto min-h-12 px-5 rounded-xl border-2 border-slate-300 text-slate-700 font-bold text-sm hover:bg-slate-100 transition cursor-pointer">
                    Periksa Kembali
                </button>
                <button type="button" @click="submitFinal()"
                    class="w-full sm:w-auto min-h-12 px-5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-sm shadow-sm transition cursor-pointer">
                    Ya, Kumpulkan Sekarang
                </button>
            </div>
        </div>
    </div>
    {{-- Modal Peringatan Pelanggaran (1/2) --}}
    <div x-show="showViolationModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;" role="alertdialog" aria-modal="true" aria-labelledby="violation-modal-title">
        <div class="w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl space-y-4 border-2 border-rose-300">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-rose-100 text-rose-800 text-xs font-extrabold uppercase tracking-wider mb-1.5">
                    Peringatan Pelanggaran (<span x-text="violations"></span>/2)
                </span>
                <h3 id="violation-modal-title" class="text-lg font-black text-slate-900 tracking-tight">Dilarang Pindah Halaman atau Menyalin Soal!</h3>
                <p class="text-xs sm:text-sm text-slate-700 mt-1.5 leading-relaxed font-medium" x-text="violationReason"></p>
                <p class="text-xs text-rose-700 mt-2.5 font-bold bg-rose-50 border border-rose-200 rounded-xl p-3">
                    Jika Anda mengulangi pelanggaran 1 kali lagi (2/2), ujian akan langsung dikumpulkan secara otomatis!
                </p>
            </div>
            <button type="button" @click="acknowledgeViolation()"
                class="w-full min-h-12 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-sm shadow-sm transition cursor-pointer">
                Saya Mengerti &amp; Lanjutkan Ujian
            </button>
        </div>
    </div>

    {{-- Overlay Auto-Submit Saat Pelanggaran Ke-2 --}}
    <div x-show="autoSubmitting" x-transition.opacity class="fixed inset-0 z-[60] bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="w-full max-w-sm bg-white rounded-2xl p-6 text-center shadow-2xl border-2 border-rose-400 space-y-3">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto animate-pulse">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h3 class="text-base font-black text-slate-900">Batas Pelanggaran Tercapai (2/2)</h3>
            <p class="text-xs text-slate-600 leading-relaxed">Jawaban Anda sedang dikumpulkan otomatis karena pelanggaran berulang.</p>
        </div>
    </div>
</div>

<script>
function cbtExam(config) {
    const draftKey = config.storageKey + '_draft';

    return {
        total: config.total,
        remaining: config.initialRemaining,
        timerDisplay: '00:00',
        deadline: Date.now() + config.initialRemaining * 1000,
        answers: config.savedAnswers,
        answeredCount: Object.keys(config.savedAnswers).length,
        unansweredCount: config.total - Object.keys(config.savedAnswers).length,
        pendingAnswers: new Map(),
        savingDraft: false,
        draftSaveFailed: false,
        mobileNavOpen: false,
        showConfirmModal: false,
        showViolationModal: false,
        autoSubmitting: false,
        isSubmitting: false,
        violations: 0,
        maxViolations: 2,
        violationReason: '',
        lastViolationAt: 0,
        armedAt: Date.now() + 800,

        init() {
            const saved = Number(sessionStorage.getItem(config.storageKey) || '0');
            if (saved > 0) {
                this.violations = Math.min(saved, this.maxViolations);
            }

            for (const [soalId, pilihanId] of JSON.parse(sessionStorage.getItem(draftKey) || '[]')) {
                const options = document.getElementsByName(`answers[${soalId}][pilihan_id]`);
                if (Array.from(options).some(input => input.value === pilihanId)) {
                    this.answers[soalId] = pilihanId;
                    this.pendingAnswers.set(soalId, pilihanId);
                }
            }
            this.updateAnswerCount();
            this.tick();
            this.setupExamGuards();
            if (this.pendingAnswers.size > 0) this.saveDraft();
        },

        setupExamGuards() {
            // Kunci riwayat agar tombol Back HP/browser tidak langsung keluar
            try {
                history.pushState({ examLock: true }, '', window.location.href);
            } catch (e) {}

            this._onBeforeUnload = (e) => {
                if (this.isSubmitting) return;
                e.preventDefault();
                e.returnValue = '';
            };

            this._onPopState = () => {
                if (this.isSubmitting) return;
                try {
                    history.pushState({ examLock: true }, '', window.location.href);
                } catch (e) {}
                this.recordViolation('Anda mencoba meninggalkan halaman ujian melalui tombol kembali.');
            };

            this._onVisibilityChange = () => {
                if (this.isSubmitting || Date.now() < this.armedAt) return;
                if (document.visibilityState === 'hidden') {
                    this.recordViolation('Anda berpindah tab atau keluar dari layar browser saat ujian berlangsung.');
                }
            };

            this._onBlur = () => {
                if (this.isSubmitting || Date.now() < this.armedAt) return;
                this.recordViolation('Anda berpindah jendela atau membuka aplikasi lain saat ujian berlangsung.');
            };

            this._onKeyDown = (e) => {
                if (this.isSubmitting) return;
                const key = (e.key || '').toLowerCase();
                const mod = e.ctrlKey || e.metaKey;

                if (e.key === 'F12' || (mod && e.shiftKey && ['i', 'j', 'c', 'k'].includes(key))) {
                    e.preventDefault();
                    this.recordViolation('Akses inspeksi atau shortcut pengembang dilarang selama ujian.');
                    return;
                }

                if (mod && ['c', 'x', 'v'].includes(key)) {
                    e.preventDefault();
                    this.recordViolation('Shortcut salin/tempel (Ctrl/Cmd+' + key.toUpperCase() + ') dilarang selama ujian.');
                    return;
                }

                if (mod && ['a', 'u', 's', 'p'].includes(key)) {
                    e.preventDefault();
                }
            };

            window.addEventListener('beforeunload', this._onBeforeUnload);
            window.addEventListener('popstate', this._onPopState);
            document.addEventListener('visibilitychange', this._onVisibilityChange);
            window.addEventListener('blur', this._onBlur);
            document.addEventListener('keydown', this._onKeyDown);
        },

        recordViolation(reason) {
            if (this.isSubmitting) return;
            const now = Date.now();
            if (now - this.lastViolationAt < 1200) {
                return;
            }
            this.lastViolationAt = now;
            this.violations++;
            sessionStorage.setItem(config.storageKey, String(this.violations));
            this.violationReason = reason;

            if (this.violations >= this.maxViolations) {
                this.triggerAutoSubmitViolation();
                return;
            }

            this.mobileNavOpen = false;
            this.showConfirmModal = false;
            this.showViolationModal = true;
        },

        acknowledgeViolation() {
            this.showViolationModal = false;
        },

        triggerAutoSubmitViolation() {
            this.showViolationModal = false;
            this.showConfirmModal = false;
            this.mobileNavOpen = false;
            this.autoSubmitting = true;

            const autoInput = document.getElementById('auto-submitted-input');
            if (autoInput) {
                autoInput.value = '1';
            }

            this.cleanupAndSubmit();
        },

        cleanupAndSubmit() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            window.removeEventListener('beforeunload', this._onBeforeUnload);
            sessionStorage.removeItem(config.storageKey);
            document.getElementById('tes-form')?.submit();
        },

        tick() {
            if (this.isSubmitting) return;
            this.remaining = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));
            const m = Math.floor(this.remaining / 60);
            const s = this.remaining % 60;
            this.timerDisplay = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            if (this.remaining <= 0) {
                document.getElementById('timed-out-input').value = '1';
                this.cleanupAndSubmit();
                return;
            }

            setTimeout(() => this.tick(), 1000);
        },

        registerAnswer(soalId, pilihanId) {
            if (this.isSubmitting) return;
            this.answers[soalId] = pilihanId;
            this.updateAnswerCount();
            this.pendingAnswers.set(soalId, pilihanId);
            sessionStorage.setItem(draftKey, JSON.stringify([...this.pendingAnswers]));
            this.saveDraft();
        },

        updateAnswerCount() {
            this.answeredCount = Object.keys(this.answers).length;
            this.unansweredCount = this.total - this.answeredCount;
        },

        async saveDraft() {
            if (this.savingDraft) return;
            this.savingDraft = true;
            const token = document.querySelector('#tes-form [name="_token"]').value;

            while (this.pendingAnswers.size > 0 && !this.isSubmitting) {
                const [soalId, pilihanId] = this.pendingAnswers.entries().next().value;
                try {
                    const response = await fetch(config.autosaveUrl, {
                        method: 'POST',
                        headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token},
                        body: JSON.stringify({soal_id: soalId, pilihan_id: pilihanId}),
                        keepalive: true,
                    });
                    if (!response.ok) break;

                    if (this.pendingAnswers.get(soalId) === pilihanId) {
                        this.pendingAnswers.delete(soalId);
                        if (this.pendingAnswers.size > 0) {
                            sessionStorage.setItem(draftKey, JSON.stringify([...this.pendingAnswers]));
                        } else {
                            sessionStorage.removeItem(draftKey);
                        }
                    }
                } catch {
                    break;
                }
            }

            this.draftSaveFailed = this.pendingAnswers.size > 0;
            this.savingDraft = false;
        },

        scrollToSoal(num) {
            const el = document.getElementById('soal-card-' + num);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },

        confirmSubmit() {
            this.showConfirmModal = true;
        },

        submitFinal() {
            this.cleanupAndSubmit();
        }
    }
}
</script>
@endsection

