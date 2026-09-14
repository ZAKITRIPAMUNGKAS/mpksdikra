@extends('layouts.app')

@section('title', ucfirst($tipe) . ' — ' . $event->nama_event)

@section('content')
<div class="max-w-6xl mx-auto space-y-6"
    x-data="cbtExam({
        total: {{ $questions->count() }},
        initialRemaining: {{ $remaining }},
        submitUrl: '{{ route('portal.tes.submit', [$event, $tipe]) }}'
    })">

    {{-- Top Sticky CBT Header & Timer Bar --}}
    <div class="sticky top-16 z-30 bg-white/95 backdrop-blur-md rounded-xl border border-slate-200/90 shadow-xs p-4 transition-all">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase tracking-wider {{ $tipe === 'pretest' ? 'bg-sky-50 text-sky-800 border border-sky-200/80' : 'bg-amber-50 text-amber-900 border border-amber-300/80' }}">
                        {{ strtoupper($tipe) }}
                    </span>
                    <span class="text-xs text-slate-300">&bull;</span>
                    <span class="text-xs font-medium text-slate-600">{{ $questions->count() }} Butir Soal</span>
                    <span class="text-xs text-slate-300">&bull;</span>
                    <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span x-text="answeredCount"></span> Terjawab (<span x-text="Math.round((answeredCount / total) * 100)"></span>%)
                    </span>
                </div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight mt-1">{{ $event->nama_event }}</h1>
            </div>

            {{-- Timer Counter --}}
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <div class="text-right">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg border font-mono shadow-2xs transition-colors"
                        :class="remaining < 60 ? 'bg-rose-50 border-rose-300 text-rose-700 animate-pulse' : 'bg-slate-50 border-slate-200 text-slate-900'">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-base sm:text-lg font-bold tabular-nums" x-text="timerDisplay"></span>
                    </div>
                    <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Sisa Waktu</p>
                </div>

                {{-- Mobile Navigator Trigger --}}
                <button type="button" @click="mobileNavOpen = true" class="lg:hidden px-3 py-1.5 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 font-semibold text-xs flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Daftar Soal</span>
                </button>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3 overflow-hidden">
            <div class="bg-sky-500 h-1.5 rounded-full transition-all duration-300"
                :style="`width: ${(answeredCount / total) * 100}%`"></div>
        </div>
    </div>

    {{-- Main Grid: Soal List + Sidebar Navigator --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left: List of Questions --}}
        <div class="lg:col-span-8 space-y-5">
            <form method="POST" action="{{ route('portal.tes.submit', [$event, $tipe]) }}" id="tes-form" class="space-y-5">
                @csrf

                @foreach($questions as $i => $s)
                <div id="soal-card-{{ $i + 1 }}" class="bg-white rounded-2xl border-2 border-slate-200 shadow-sm p-5 sm:p-7 transition-all duration-150 scroll-mt-36 hover:border-sky-400">
                    <div class="flex items-start gap-3.5 mb-5">
                        <span class="w-9 h-9 rounded-xl font-black text-sm flex items-center justify-center shrink-0 shadow-xs transition-colors"
                            :class="answers['{{ $s->id }}'] ? 'bg-sky-600 text-white border-2 border-sky-700' : 'bg-slate-100 text-slate-800 border-2 border-slate-300'">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1">
                            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-500 block mb-1">Butir Soal #{{ $i + 1 }}</span>
                            <p class="font-extrabold text-slate-900 text-base sm:text-lg leading-relaxed">{{ $s->teks_soal }}</p>
                        </div>
                    </div>

                    <input type="hidden" name="answers[{{ $s->id }}][soal_id]" value="{{ $s->id }}">

                    <div class="space-y-3 pl-0 sm:pl-12">
                        @foreach($s->pilihan as $p)
                        <label class="group relative flex items-start gap-3.5 p-4 sm:p-5 rounded-2xl border-2 border-slate-200 bg-white hover:border-sky-400 hover:bg-sky-50/40 cursor-pointer transition-all duration-150 shadow-xs min-h-[56px]"
                            :class="answers['{{ $s->id }}'] == '{{ $p->id }}' ? 'border-sky-600 bg-sky-50 text-sky-950 ring-2 ring-sky-400/30' : ''">
                            
                            {{-- Radio Option Indicator --}}
                            <div class="pt-1 shrink-0">
                                <input type="radio" name="answers[{{ $s->id }}][pilihan_id]" value="{{ $p->id }}"
                                    @change="registerAnswer('{{ $s->id }}', '{{ $p->id }}')"
                                    class="w-5 h-5 border-2 border-slate-300 text-sky-600 focus:ring-sky-500 mt-0.5">
                            </div>

                            {{-- Option Badge Letter --}}
                            <span class="w-8 h-8 rounded-xl text-sm font-black flex items-center justify-center shrink-0 transition-colors"
                                :class="answers['{{ $s->id }}'] == '{{ $p->id }}' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-800 border-2 border-slate-300 group-hover:bg-sky-100 group-hover:text-sky-900'">
                                {{ $p->label }}
                            </span>

                            {{-- Option Text --}}
                            <span class="text-base sm:text-lg font-semibold text-slate-900 group-hover:text-black leading-relaxed flex-1 pt-0.5">
                                {{ $p->teks }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- Bottom Mobile Submit Button --}}
                <div class="lg:hidden p-5 bg-white rounded-2xl border-2 border-slate-200 shadow-md flex items-center justify-between gap-4">
                    <div class="text-xs sm:text-sm text-slate-600 font-medium">
                        Terjawab: <strong class="text-slate-900 text-base"><span x-text="answeredCount"></span> / {{ $questions->count() }}</strong>
                    </div>
                    <button type="button" @click="confirmSubmit()"
                        class="min-h-[50px] px-6 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-sm sm:text-base rounded-2xl shadow-sm transition">
                        Kumpulkan Ujian &rarr;
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

            <div class="flex items-center justify-end gap-3 pt-5 border-t-2 border-slate-100">
                <button type="button" @click="showConfirmModal = false"
                    class="min-h-[48px] px-5 rounded-2xl border-2 border-slate-300 text-slate-700 font-bold text-xs sm:text-sm hover:bg-slate-100 transition">
                    Periksa Kembali
                </button>
                <button type="button" @click="submitFinal()"
                    class="min-h-[48px] px-5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs sm:text-sm shadow-sm transition">
                    Ya, Kumpulkan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function cbtExam(config) {
    return {
        total: config.total,
        remaining: config.initialRemaining,
        timerDisplay: '00:00',
        answers: {},
        answeredCount: 0,
        unansweredCount: config.total,
        mobileNavOpen: false,
        showConfirmModal: false,

        init() {
            this.tick();
        },

        tick() {
            const m = Math.floor(this.remaining / 60);
            const s = this.remaining % 60;
            this.timerDisplay = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            if (this.remaining <= 0) {
                document.getElementById('tes-form')?.submit();
                return;
            }

            this.remaining--;
            setTimeout(() => this.tick(), 1000);
        },

        registerAnswer(soalId, pilihanId) {
            this.answers[soalId] = pilihanId;
            this.answeredCount = Object.keys(this.answers).length;
            this.unansweredCount = this.total - this.answeredCount;
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
            document.getElementById('tes-form')?.submit();
        }
    }
}
</script>
@endsection

