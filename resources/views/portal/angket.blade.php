@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-center text-sky-700 shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-sky-50 border border-sky-200 text-[11px] font-bold text-sky-800 uppercase tracking-wider mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                    Evaluasi Mutu Penyelenggaraan
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Angket Evaluasi Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Evaluasi layanan, mutu materi, dan fasilitator untuk penyempurnaan Baitul Arqam berikutnya.
                    <span class="block mt-2 font-semibold text-slate-700">
                        Skala Penilaian: 1 (Sangat Kurang) &rarr; 5 (Sangat Baik)
                    </span>
                </p>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="px-5 py-4 rounded-2xl bg-red-50 border-l-4 border-red-600 text-red-950 text-sm shadow-xs card-lift-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('portal.angket.save', $event) }}" class="space-y-6">
        @csrf
        @forelse($items as $kategori => $list)
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-[#f8bb00] animate-pulse"></span>
                <h2 class="font-extrabold text-slate-900 text-base tracking-tight">Aspek {{ $kategori }}</h2>
            </div>

            <div class="space-y-5">
                @foreach($list as $item)
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-sky-200 transition">
                    <p class="text-sm font-bold text-slate-900 mb-3">{{ $item->teks_item }}</p>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach([1, 2, 3, 4, 5] as $val)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $item->id }}]" value="{{ $val }}" required class="peer sr-only">
                            <span class="block text-center py-2.5 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm font-bold text-slate-700 peer-checked:bg-sky-500 peer-checked:border-sky-500 peer-checked:text-white peer-checked:shadow-xs shadow-xs hover:bg-sky-50/50 hover:border-sky-300 transition duration-150">
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
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center text-slate-400 text-sm shadow-xs card-lift-sm">
            Angket belum disiapkan panitia.
        </div>
        @endforelse

        @if($items->count())
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <label class="block text-sm font-black text-slate-900 mb-1">Komentar, Saran, &amp; Kritik Konstruktif</label>
            <p class="text-xs text-slate-500 mb-3">Tuliskan masukan Anda untuk panitia dan pimpinan MPKSDI Karanganyar.</p>
            <textarea name="komentar" rows="4" placeholder="Tuliskan masukan Anda di sini..."
                class="w-full px-4 py-3 border border-slate-200/90 rounded-2xl outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition resize-none text-sm bg-white"></textarea>
        </div>

        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4 card-lift-sm">
            <p class="text-xs text-slate-500 font-medium">Angket ini bersifat anonim untuk evaluasi perkaderan.</p>
            <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-[#f8bb00] hover:bg-amber-400 active:bg-amber-500 text-slate-950 font-black text-xs sm:text-sm rounded-full border border-amber-400 shadow-md transition hover:scale-[1.01]">
                Kirim Angket Evaluasi
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
