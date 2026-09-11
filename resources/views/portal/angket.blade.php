@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-mpksdi-50 border border-mpksdi-200 flex items-center justify-center text-mpksdi-700 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Angket Evaluasi Penyelenggaraan</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Evaluasi layanan, mutu materi, dan kinerja fasilitator untuk penyempurnaan Baitul Arqam berikutnya.
                    <span class="block mt-2 font-semibold text-slate-700">
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
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-gold-400"></span>
                <h2 class="font-bold text-slate-900 text-base tracking-tight">Aspek {{ $kategori }}</h2>
            </div>

            <div class="space-y-5">
                @foreach($list as $item)
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/80">
                    <p class="text-sm font-semibold text-slate-900 mb-3">{{ $item->teks_item }}</p>
                    <div class="grid grid-cols-5 gap-2">
                        @foreach([1, 2, 3, 4, 5] as $val)
                        <label class="cursor-pointer select-none">
                            <input type="radio" name="jawaban[{{ $item->id }}]" value="{{ $val }}" required class="peer sr-only">
                            <span class="block text-center py-2.5 rounded-lg border border-slate-300 bg-white text-xs sm:text-sm font-bold text-slate-700 peer-checked:bg-mpksdi-600 peer-checked:border-mpksdi-600 peer-checked:text-white peer-checked:shadow-2xs shadow-2xs hover:bg-slate-50 transition duration-150">
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
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-400 text-sm shadow-xs">
            Angket belum disiapkan panitia.
        </div>
        @endforelse

        @if($items->count())
        <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
            <label class="block text-sm font-bold text-slate-900 mb-1">Komentar, Saran, &amp; Kritik Konstruktif</label>
            <p class="text-xs text-slate-500 mb-3">Tuliskan masukan Anda untuk panitia dan pimpinan MPKSDI Karanganyar.</p>
            <textarea name="komentar" rows="4" placeholder="Tuliskan masukan Anda di sini..."
                class="w-full px-4 py-3 border border-slate-300 rounded-lg outline-none focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 transition resize-none text-sm"></textarea>
        </div>

        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md p-4 rounded-xl border border-slate-200 shadow-md flex items-center justify-between gap-4">
            <p class="text-xs text-slate-500 font-medium">Angket ini bersifat anonim untuk evaluasi perkaderan.</p>
            <button type="submit" class="px-6 py-2.5 bg-gold-400 hover:bg-gold-500 active:bg-gold-600 text-mpksdi-950 font-extrabold text-sm rounded-lg border border-gold-500 shadow-2xs transition">
                Kirim Angket Evaluasi
            </button>
        </div>
        @endif
    </form>
</div>
@endsection
