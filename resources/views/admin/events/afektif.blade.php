@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs mb-6 relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-sky-50 text-sky-800 border border-sky-200 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
            Instrumen Sikap &amp; Ideologi
        </span>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">Kuesioner Afektif (Likert 1–4)</h1>
        <p class="text-xs text-slate-500 mb-5 font-normal">Peserta mengisi mandiri; nilai dikonversi ke skala 0–100. Butir negatif dihitung terbalik secara otomatis.</p>

        <form method="POST" action="{{ route('admin.events.afektif.clone', $event) }}" class="flex items-center gap-1.5 flex-wrap mb-3"
              onsubmit="return confirm('Salin instrumen penilaian sikap dari kegiatan yang dipilih? Butir yang sudah ada tidak akan diduplikasi.')">
            @csrf
            <select name="source_event_id" required aria-label="Kegiatan sumber" class="min-w-0 flex-1 sm:flex-none px-3 py-1.5 bg-slate-50 border border-slate-200/90 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20">
                <option value="">-- Salin dari Kegiatan Lain --</option>
                @foreach($sumberLain as $oe)
                    <option value="{{ $oe->id }}">{{ $oe->nama_event }} ({{ $oe->jumlah_butir }} butir)</option>
                @endforeach
            </select>
            <button type="submit" @disabled($sumberLain->isEmpty()) class="px-3 py-1.5 bg-slate-100 hover:bg-sky-50 text-sky-800 border border-slate-200 hover:border-sky-300 text-xs font-semibold rounded-lg transition shadow-2xs disabled:opacity-50">
                Salin Penilaian Sikap
            </button>
            @if($sumberLain->isEmpty())
                <span class="text-[11px] text-slate-400">Belum ada kegiatan lain yang punya instrumen sikap.</span>
            @endif
        </form>

        <form method="POST" action="{{ route('admin.events.afektif.sub.store', $event) }}" class="flex gap-2 mb-6">
            @csrf
            <input name="nama_sub_aspek" required placeholder="Nama sub-aspek (contoh: Kedisiplinan, Integritas)"
                class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            <button class="px-4 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">+ Sub-aspek</button>
        </form>

        <div class="space-y-4">
            @forelse($subAspek as $sa)
            <div class="border border-slate-200/80 rounded-xl p-5 bg-slate-50/40 shadow-2xs">
                <h3 class="font-bold text-slate-900 text-sm mb-3 tracking-tight">{{ $sa->nama_sub_aspek }}</h3>
                <div class="space-y-2 mb-4">
                    @foreach($sa->butir as $b)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-white border border-slate-200/70">
                        <p class="text-xs text-slate-800 min-w-0 font-medium">
                            {{ $b->teks_butir }}
                            <span class="text-xs font-medium uppercase tracking-wider ml-1.5 {{ $b->is_positif ? 'text-sky-800 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200' : 'text-amber-900 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-300' }}">
                                {{ $b->is_positif ? 'Positif' : 'Negatif' }}
                            </span>
                        </p>
                        <form method="POST" action="{{ route('admin.events.afektif.butir.destroy', [$event, $b]) }}" onsubmit="return confirm('Hapus butir ini?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-600 text-base font-bold px-2 shrink-0 transition" title="Hapus Butir">&times;</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.events.afektif.butir.store', [$event, $sa]) }}" class="flex gap-2 flex-wrap">
                    @csrf
                    <input name="teks_butir" required placeholder="Teks butir pernyataan afektif…"
                        class="flex-1 min-w-[200px] px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    <select name="is_positif" class="px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-sky-500/20 cursor-pointer transition">
                        <option value="1">Arah: Positif</option>
                        <option value="0">Arah: Negatif</option>
                    </select>
                    <button class="px-3.5 py-2 border border-sky-300 bg-white text-sky-700 text-xs font-semibold rounded-lg hover:bg-sky-50 transition shadow-2xs">+ Tambah Butir</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-6 font-normal">Belum ada sub-aspek afektif yang dibuat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
