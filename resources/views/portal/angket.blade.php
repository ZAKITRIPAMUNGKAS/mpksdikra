@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />
        <div class="relative z-10 flex items-start gap-4">
            <div class="w-11 h-11 rounded-lg bg-sky-50 border border-sky-200/80 flex items-center justify-center text-sky-700 shrink-0 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-xs font-semibold text-sky-800 uppercase tracking-wider mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                    Evaluasi Mutu Penyelenggaraan
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Angket Evaluasi Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 font-normal">
                    Evaluasi layanan, mutu materi, dan fasilitator untuk penyempurnaan Baitul Arqam berikutnya.
                    <span class="block mt-1 font-semibold text-slate-700">
                        Skala Penilaian: 1 (Sangat Kurang) &rarr; 5 (Sangat Baik)
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

    <form method="POST" action="{{ route('portal.angket.save', $event) }}" class="space-y-6">
        @csrf
        @forelse($items as $kategori => $list)
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                <h2 class="font-bold text-slate-900 text-base tracking-tight">Aspek {{ $kategori }}</h2>
            </div>

            <div class="space-y-4">
                @foreach($list as $item)
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/90 hover:bg-white hover:border-sky-200 transition">
                    <p class="text-sm font-semibold text-slate-900 mb-3">{{ $item->teks_item }}</p>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach([1, 2, 3, 4, 5] as $val)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $item->id }}]" value="{{ $val }}" required class="peer sr-only">
                            <span class="block text-center py-2 rounded-lg border border-slate-200/90 bg-white text-xs sm:text-sm font-semibold text-slate-700 peer-checked:bg-sky-600 peer-checked:border-sky-600 peer-checked:text-white peer-checked:shadow-2xs shadow-2xs hover:bg-sky-50/50 hover:border-sky-300 transition duration-150">
                                {{ $val }}
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
            Angket belum disiapkan panitia.
        </div>
        @endforelse

        @if($items->count())
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <label class="block text-sm font-bold text-slate-900 mb-1">Komentar, Saran, &amp; Kritik Konstruktif</label>
            <p class="text-xs text-slate-500 mb-3 font-normal">Tuliskan masukan Anda untuk panitia dan pimpinan MPKSDI Karanganyar.</p>
            <textarea name="komentar" rows="4" placeholder="Tuliskan masukan Anda di sini..."
                class="w-full px-3.5 py-2.5 border border-slate-200/90 rounded-lg outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition resize-none text-sm bg-white"></textarea>
        </div>

        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-slate-200/90 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">Angket ini bersifat anonim untuk evaluasi perkaderan.</p>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-xs sm:text-sm rounded-lg shadow-2xs transition">
                Kirim Angket Evaluasi
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
