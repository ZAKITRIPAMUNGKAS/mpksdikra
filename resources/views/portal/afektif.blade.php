@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
@php
    $totalButir = $subAspek->sum(fn ($sa) => $sa->butir->count());
    $jawabanTerpilih = $subAspek->flatMap(fn ($sa) => $sa->butir)->mapWithKeys(function ($b) use ($existing) {
        $skor = old('jawaban.'.$b->id, $existing[$b->id] ?? null);

        return [$b->id => is_numeric($skor) && in_array((string) $skor, ['1', '2', '3', '4'], true) ? (int) $skor : null];
    });
@endphp
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6"
     x-data="{
         total: {{ $totalButir }},
         filled: {{ $jawabanTerpilih->filter()->count() }},
         recount() {
             this.filled = this.$root.querySelectorAll('input[type=radio]:checked').length;
         }
     }">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('portal.dashboard') }}" class="min-h-11 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
        @if($totalButir > 0)
        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700">
            Terisi <span class="text-[#005c8a] font-bold" x-text="filled"></span>/{{ $totalButir }}
        </span>
        @endif
    </div>

    {{-- Header Banner with Batik Corner --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-28 h-28 sm:w-36 sm:h-36 text-[#005c8a] opacity-[0.16]" />

        <div class="flex items-start gap-3 sm:gap-4 relative z-10">
            <div class="hidden sm:flex w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 items-center justify-center text-sky-600 shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-[11px] font-semibold text-sky-800 uppercase tracking-wider mb-1">
                    Evaluasi Sikap &amp; Karakter
                </span>
                <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">Penilaian Afektif Mandiri</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Pilih opsi yang paling menggambarkan diri Anda:
                </p>
                <div class="flex flex-wrap gap-1.5 mt-2 font-semibold text-slate-700">
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">STS: Sangat Tidak Setuju</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">TS: Tidak Setuju</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">S: Setuju</span>
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200 text-[11px]">SS: Sangat Setuju</span>
                </div>
            </div>
        </div>
    </div>

        @if(! empty($diisiKertas))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-900">
                Jawaban Anda untuk formulir ini sudah diisikan panitia dari <b>lembar kertas</b>, sehingga tidak perlu diisi lagi di sini.
                Hubungi panitia bila ada yang perlu diperbaiki.
            </div>
        @elseif(! empty($sudahDikirim))
            <div role="status" data-isian-terkunci class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 text-sm text-emerald-900">
                <p class="font-bold">&#10003; Kuesioner sikap (afektif) sudah Anda kirim.</p>
                <p class="mt-1">Jawaban tersimpan dan tidak dapat diubah lagi. Hubungi panitia bila ada kekeliruan.</p>
                <a href="{{ route('portal.dashboard') }}" class="mt-3 inline-flex items-center min-h-11 px-4 rounded-xl bg-white border border-emerald-300 text-emerald-800 font-bold text-xs">Kembali ke Beranda</a>
            </div>
        @else
    <form method="POST" action="{{ route('portal.afektif.save', $event) }}" class="space-y-4 sm:space-y-6">
        @csrf
        @if($errors->has('jawaban') || $errors->has('jawaban.*'))
            <div role="alert" class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-900">
                {{ $errors->first('jawaban') ?: $errors->first('jawaban.*') }}
            </div>
        @endif
        @forelse($subAspek as $sa)
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs">
            <div class="flex items-center gap-2 mb-3.5 pb-2.5 border-b border-slate-100">
                <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">{{ $sa->nama_sub_aspek }}</h2>
            </div>

            <div class="space-y-3">
                @foreach($sa->butir as $b)
                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50/70 border border-slate-200/90 hover:bg-white hover:border-sky-200 transition">
                    <p class="text-sm font-semibold text-slate-900 mb-3 leading-snug">{{ $b->teks_butir }}</p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([1 => 'STS', 2 => 'TS', 3 => 'S', 4 => 'SS'] as $val => $lbl)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $b->id }}]" value="{{ $val }}"
                                @change="recount()"
                                @checked($jawabanTerpilih[$b->id] === $val)
                                @required($loop->first)
                                class="peer sr-only">
                            <span class="min-h-12 flex items-center justify-center rounded-xl border-2 border-slate-200 bg-white text-xs sm:text-sm font-bold text-slate-700 peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white shadow-2xs hover:bg-sky-50/40 hover:border-sky-300 transition duration-150">
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

        @if($totalButir > 0)
        <div class="fixed bottom-[calc(3.5rem+max(0.5rem,env(safe-area-inset-bottom,0px)))] inset-x-0 z-20 lg:sticky lg:bottom-4 lg:inset-auto px-4 py-3 lg:p-4 bg-white/95 backdrop-blur-md lg:rounded-2xl border-t lg:border border-slate-200/90 shadow-[0_-4px_12px_rgba(15,23,42,0.06)] lg:shadow-md flex items-center justify-between gap-3">
            <div class="text-xs text-slate-600 font-medium">
                Terisi <strong class="text-slate-900" x-text="filled"></strong>/{{ $totalButir }} butir
            </div>
            <button type="submit" class="min-h-11 px-5 sm:px-6 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition cursor-pointer shrink-0">
                Simpan Jawaban Afektif &rarr;
            </button>
        </div>
        @endif
    </form>
        @endif
</div>
@endsection
