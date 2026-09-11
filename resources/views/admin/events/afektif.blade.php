@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs mb-6 max-w-3xl relative overflow-hidden card-lift-sm">
    <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
            Instrumen Sikap &amp; Ideologi
        </span>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Kuesioner Afektif (Likert 1–4)</h1>
        <p class="text-xs text-slate-600 mb-5 font-medium">Peserta mengisi mandiri; nilai dikonversi ke skala 0–100. Butir negatif dihitung terbalik secara otomatis.</p>

        <form method="POST" action="{{ route('admin.events.afektif.sub.store', $event) }}" class="flex gap-2 mb-6">
            @csrf
            <input name="nama_sub_aspek" required placeholder="Nama sub-aspek (contoh: Kedisiplinan, Integritas)"
                class="flex-1 px-4 py-2.5 bg-white border border-slate-200/90 rounded-xl text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            <button class="px-5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">+ Sub-aspek</button>
        </form>

        <div class="space-y-4">
            @forelse($subAspek as $sa)
            <div class="border border-slate-200/80 rounded-2xl p-5 bg-white shadow-xs">
                <h3 class="font-black text-slate-900 text-sm mb-3 tracking-tight">{{ $sa->nama_sub_aspek }}</h3>
                <div class="space-y-2 mb-4">
                    @foreach($sa->butir as $b)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50/70 border border-slate-100">
                        <p class="text-xs text-slate-800 min-w-0 font-medium">
                            {{ $b->teks_butir }}
                            <span class="text-[10px] font-bold uppercase tracking-wider ml-1 {{ $b->is_positif ? 'text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200' : 'text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200' }}">
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
                        class="flex-1 min-w-[200px] px-3.5 py-2 bg-slate-50/60 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                    <select name="is_positif" class="px-3.5 py-2 bg-slate-50/60 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:ring-2 focus:ring-sky-500/20 cursor-pointer transition">
                        <option value="1">Arah: Positif</option>
                        <option value="0">Arah: Negatif</option>
                    </select>
                    <button class="px-4 py-2 border border-sky-300 text-sky-700 text-xs font-bold rounded-full hover:bg-sky-50 transition shadow-2xs">+ Tambah Butir</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-6">Belum ada sub-aspek afektif yang dibuat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
