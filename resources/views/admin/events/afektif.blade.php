@extends('layouts.app')

@section('title', 'Afektif — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs mb-6 max-w-3xl">
    <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-1">Kuesioner Afektif (Likert 1–4)</h1>
    <p class="text-xs text-slate-500 mb-5">Peserta mengisi mandiri; nilai dikonversi ke skala 0–100. Butir negatif dihitung terbalik secara otomatis.</p>

    <form method="POST" action="{{ route('admin.events.afektif.sub.store', $event) }}" class="flex gap-2 mb-6">
        @csrf
        <input name="nama_sub_aspek" required placeholder="Nama sub-aspek (contoh: Kedisiplinan, Integritas)"
            class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        <button class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">+ Sub-aspek</button>
    </form>

    <div class="space-y-4">
        @forelse($subAspek as $sa)
        <div class="border border-slate-200 rounded-lg p-5 bg-slate-50/30">
            <h3 class="font-bold text-slate-900 text-sm mb-3">{{ $sa->nama_sub_aspek }}</h3>
            <div class="space-y-2 mb-4">
                @foreach($sa->butir as $b)
                <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-white border border-slate-200/80 shadow-2xs">
                    <p class="text-xs text-slate-800 min-w-0">
                        {{ $b->teks_butir }}
                        <span class="text-[10px] font-bold uppercase tracking-wider ml-1 {{ $b->is_positif ? 'text-mpksdi-700 bg-mpksdi-50 px-1.5 py-0.5 rounded border border-mpksdi-200' : 'text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200' }}">
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
                    class="flex-1 min-w-[200px] px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-mpksdi-500 transition">
                <select name="is_positif" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-mpksdi-500 cursor-pointer transition">
                    <option value="1">Arah: Positif</option>
                    <option value="0">Arah: Negatif</option>
                </select>
                <button class="px-4 py-2 border border-mpksdi-300 text-mpksdi-700 text-xs font-bold rounded-lg hover:bg-mpksdi-50 transition">+ Tambah Butir</button>
            </form>
        </div>
        @empty
        <p class="text-xs text-slate-400 text-center py-6">Belum ada sub-aspek afektif yang dibuat.</p>
        @endforelse
    </div>
</div>
@endsection
