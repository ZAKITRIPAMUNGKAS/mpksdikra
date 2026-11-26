@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

@section('content')
@php
    $totalAngketItem = $items->flatten()->count();
@endphp
<div class="max-w-3xl mx-auto space-y-4 sm:space-y-6"
     x-data="{
         total: {{ $totalAngketItem }},
         filled: {{ count($jawabanLama) }},
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
        @if($totalAngketItem > 0)
        <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700">
            Terisi <span class="text-[#005c8a] font-bold" x-text="filled"></span>/{{ $totalAngketItem }}
        </span>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-28 h-28 sm:w-36 sm:h-36 text-[#005c8a] opacity-[0.16]" />
        <div class="relative z-10 flex items-start gap-3 sm:gap-4">
            <div class="hidden sm:flex w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 items-center justify-center text-sky-700 shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-[11px] font-semibold text-sky-800 uppercase tracking-wider mb-1">
                    Evaluasi Mutu Penyelenggaraan
                </span>
                <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">Angket Evaluasi Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Evaluasi layanan, mutu materi, dan fasilitator untuk penyempurnaan berikutnya.
                    <span class="block mt-1 font-semibold text-slate-700">
                        Skala: 1 (Sangat Kurang) &rarr; 5 (Sangat Baik)
                    </span>
                </p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="px-4 py-3 rounded-lg bg-red-50 border-l-4 border-red-600 text-red-950 text-sm shadow-2xs">
            {{ $errors->first() }}
        </div>
    @endif

        @if(! empty($diisiKertas))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-900">
                Jawaban Anda untuk formulir ini sudah diisikan panitia dari <b>lembar kertas</b>, sehingga tidak perlu diisi lagi di sini.
                Hubungi panitia bila ada yang perlu diperbaiki.
            </div>
        @else
    <form method="POST" action="{{ route('portal.angket.save', $event) }}" class="space-y-4 sm:space-y-6">
        @csrf
        @forelse($items as $kategori => $list)
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs">
            <div class="flex items-center gap-2 mb-3.5 pb-2.5 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">Aspek {{ $kategori }}</h2>
            </div>

            <div class="space-y-3.5">
                @foreach($list as $item)
                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 border border-slate-200 hover:bg-white hover:border-sky-300 transition">
                    <p class="text-sm sm:text-base font-bold text-slate-900 mb-2.5 leading-snug">{{ $item->teks_item }}</p>
                    <div class="sm:hidden flex justify-between text-[10px] font-semibold text-slate-500 mb-1.5 px-0.5">
                        <span>1 = Sangat Kurang</span>
                        <span>5 = Sangat Baik</span>
                    </div>
                    <div class="grid grid-cols-5 gap-1.5 sm:gap-2.5">
                        @foreach([1, 2, 3, 4, 5] as $val)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $item->id }}]" value="{{ $val }}" @change="recount()" @checked(($jawabanLama[$item->id] ?? null) === $val) required class="peer sr-only">
                            <span class="flex flex-col items-center justify-center min-h-12 sm:min-h-14 py-2 rounded-xl border-2 border-slate-300 bg-white text-base sm:text-lg font-black text-slate-800 peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white peer-checked:ring-2 peer-checked:ring-sky-300 shadow-2xs hover:bg-sky-50 hover:border-sky-400 transition active:scale-[0.98]">
                                <span>{{ $val }}</span>
                                @if($val === 1)
                                    <span class="text-[10px] font-bold opacity-80 hidden sm:block">Kurang</span>
                                @elseif($val === 5)
                                    <span class="text-[10px] font-bold opacity-80 hidden sm:block">Sangat Baik</span>
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border-2 border-dashed border-slate-300 p-10 text-center text-slate-500 text-base shadow-xs">
            Angket belum disiapkan panitia.
        </div>
        @endforelse

        @if($items->count())
        {{-- PERTANYAAN TERAKHIR (ISIAN): PERASAAN MENGIKUTI PERKADERAN --}}
        <div class="bg-white rounded-2xl border-2 border-emerald-200/90 p-4 sm:p-6 shadow-2xs">
            <div class="flex items-center gap-2 mb-2.5 pb-2.5 border-b border-emerald-100">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-[11px] font-bold text-emerald-800 uppercase tracking-wider">
                    Pertanyaan Terakhir (Isian Terbuka)
                </span>
            </div>

            <label for="perasaan-input" class="block text-sm sm:text-lg font-black text-slate-900 mb-1">
                Bagaimana perasaan Anda setelah mengikuti perkaderan Baitul Arqam ini?
            </label>
            <p class="text-xs sm:text-sm text-slate-600 mb-3 font-medium leading-relaxed">
                Ceritakan perasaan, kesan, pengalaman batin, atau perubahan motivasi ber-Muhammadiyah yang Anda rasakan.
            </p>
            <textarea id="perasaan-input" name="perasaan" rows="4" placeholder="Tuliskan perasaan, kesan, dan refleksi Anda selama mengikuti perkaderan di sini..."
                class="w-full px-3.5 py-3 border-2 border-slate-300 rounded-xl outline-none focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100 transition resize-none text-base bg-white font-medium placeholder:text-slate-400 mb-4">{{ old('perasaan', $komentarLama->perasaan ?? '') }}</textarea>

            <div class="pt-3.5 border-t border-slate-100">
                <label for="komentar-input" class="block text-sm font-bold text-slate-900 mb-1">
                    Komentar, Saran, &amp; Kritik Konstruktif (Opsional)
                </label>
                <p class="text-xs text-slate-500 mb-2 font-medium">
                    Masukan terkait sarana, konsumsi, atau penyelenggaraan berikutnya.
                </p>
                <textarea id="komentar-input" name="komentar" rows="3" placeholder="Tuliskan masukan tambahan Anda di sini..."
                    class="w-full px-3.5 py-3 border-2 border-slate-200 rounded-xl outline-none focus:border-sky-600 focus:ring-4 focus:ring-sky-100 transition resize-none text-base sm:text-sm bg-white font-medium placeholder:text-slate-400">{{ old('komentar', $komentarLama->komentar ?? '') }}</textarea>
            </div>
        </div>

        <div class="fixed bottom-[calc(3.5rem+max(0.5rem,env(safe-area-inset-bottom,0px)))] inset-x-0 z-20 lg:sticky lg:bottom-4 lg:inset-auto px-4 py-3 lg:p-4 bg-white/95 backdrop-blur-md lg:rounded-2xl border-t lg:border border-slate-200/90 shadow-[0_-4px_12px_rgba(15,23,42,0.06)] lg:shadow-md flex items-center justify-between gap-3">
            <div class="text-xs text-slate-600 font-medium">
                Terisi <strong class="text-slate-900" x-text="filled"></strong>/{{ $totalAngketItem }} butir
                <span class="hidden sm:inline text-slate-400">&bull; Anonim</span>
            </div>
            <button type="submit" class="min-h-11 px-5 sm:px-7 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                <span>Kirim Angket Evaluasi</span>
                <span>&rarr;</span>
            </button>
        </div>
        @endif
    </form>
        @endif
</div>
@endsection
