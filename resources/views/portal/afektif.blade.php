@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-mpksdi-50 border border-mpksdi-200 flex items-center justify-center text-mpksdi-700 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Penilaian Afektif Mandiri</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Isi kuesioner sikap dan komitmen perkaderan. Pilih opsi yang paling menggambarkan diri Anda:
                    <span class="inline-flex flex-wrap gap-1.5 mt-2 font-semibold text-slate-700">
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs">STS = Sangat Tidak Setuju</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs">TS = Tidak Setuju</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs">S = Setuju</span>
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-xs">SS = Sangat Setuju</span>
                    </span>
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.afektif.save', $event) }}" class="space-y-6">
        @csrf
        @forelse($subAspek as $sa)
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-gold-400"></span>
                <h2 class="font-bold text-slate-900 text-base tracking-tight">{{ $sa->nama_sub_aspek }}</h2>
            </div>

            <div class="space-y-5">
                @foreach($sa->butir as $b)
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/80">
                    <p class="text-sm font-semibold text-slate-900 mb-3">{{ $b->teks_butir }}</p>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach([1 => 'STS', 2 => 'TS', 3 => 'S', 4 => 'SS'] as $val => $lbl)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $b->id }}]" value="{{ $val }}"
                                @checked((int) ($existing[$b->id] ?? 0) === $val)
                                class="peer sr-only">
                            <span class="block text-center py-2.5 rounded-lg border border-slate-300 bg-white text-xs sm:text-sm font-bold text-slate-700 peer-checked:bg-mpksdi-600 peer-checked:border-mpksdi-600 peer-checked:text-white peer-checked:shadow-2xs shadow-2xs hover:bg-slate-50 transition duration-150">
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
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-400 text-sm shadow-xs">
            Kuesioner afektif belum disiapkan panitia.
        </div>
        @endforelse

        @if($subAspek->count())
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-slate-200 shadow-md flex items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">Pastikan semua butir telah terisi sebelum menyimpan.</p>
            <button type="submit" class="px-6 py-2.5 bg-gold-400 hover:bg-gold-500 active:bg-gold-600 text-mpksdi-950 font-extrabold text-sm rounded-lg border border-gold-500 shadow-2xs transition">
                Simpan Jawaban Afektif
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
