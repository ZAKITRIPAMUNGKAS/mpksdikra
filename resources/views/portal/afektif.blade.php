@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header Banner with Batik Corner --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />

        <div class="flex items-start gap-4 relative z-10">
            <div class="w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 flex items-center justify-center text-sky-600 shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-xs font-semibold text-sky-800 uppercase tracking-wider mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                    Evaluasi Sikap &amp; Karakter
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Penilaian Afektif Mandiri</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Isi kuesioner sikap dan komitmen perkaderan. Pilih opsi yang paling menggambarkan diri Anda:
                    <span class="inline-flex flex-wrap gap-1.5 mt-2 font-semibold text-slate-700">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-xs">STS = Sangat Tidak Setuju</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-xs">TS = Tidak Setuju</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-xs">S = Setuju</span>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-xs">SS = Sangat Setuju</span>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.afektif.save', $event) }}" class="space-y-6">
        @csrf
        @forelse($subAspek as $sa)
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                <h2 class="font-bold text-slate-900 text-base tracking-tight">{{ $sa->nama_sub_aspek }}</h2>
            </div>

            <div class="space-y-4">
                @foreach($sa->butir as $b)
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/90 hover:bg-white hover:border-sky-200 transition">
                    <p class="text-sm font-semibold text-slate-900 mb-3">{{ $b->teks_butir }}</p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([1 => 'STS', 2 => 'TS', 3 => 'S', 4 => 'SS'] as $val => $lbl)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $b->id }}]" value="{{ $val }}"
                                @checked((int) ($existing[$b->id] ?? 0) === $val)
                                class="peer sr-only">
                            <span class="block text-center py-2 rounded-lg border border-slate-200/90 bg-white text-xs sm:text-sm font-semibold text-slate-700 peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white peer-checked:shadow-2xs shadow-2xs hover:bg-sky-50/40 hover:border-sky-300 transition duration-150">
                                {{ $lbl }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-400 text-sm shadow-2xs">
            Kuesioner afektif belum disiapkan panitia.
        </div>
        @endforelse

        @if($subAspek->count())
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">Pastikan semua butir telah terisi sebelum menyimpan.</p>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-xs sm:text-sm rounded-lg shadow-2xs transition">
                Simpan Jawaban Afektif &rarr;
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
