@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

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
            Evaluasi Layanan Perkaderan
        </span>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Angket Evaluasi Penyelenggaraan (Likert 1–5)</h1>
        <p class="text-xs text-slate-600 mb-5 font-medium">Instrumen evaluasi kualitas sarana, narasumber, dan kepanitiaan oleh kader</p>
        <form method="POST" action="{{ route('admin.events.angket.item.store', $event) }}" class="flex gap-2 flex-wrap items-end">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori</label>
                <input name="kategori" required maxlength="5" value="A"
                    class="w-20 px-3.5 py-2 bg-white border border-slate-200/90 rounded-xl text-xs font-black text-center outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            </div>
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pernyataan Evaluasi</label>
                <input name="teks_item" required placeholder="Contoh: Materi disampaikan dengan jelas dan aplikatif"
                    class="w-full px-4 py-2 bg-white border border-slate-200/90 rounded-xl text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            </div>
            <button class="px-5 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">+ Tambah Item</button>
        </form>
    </div>
</div>

@forelse($items as $kategori => $list)
<div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs mb-4 max-w-3xl card-lift-sm card-sheen hover:border-sky-400 transition">
    <h2 class="font-black text-slate-900 text-base mb-3 tracking-tight">Kategori {{ $kategori }} ({{ $list->count() }} Item)</h2>
    <div class="space-y-2">
        @foreach($list as $item)
        <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50/70 border border-slate-100 hover:bg-white hover:border-sky-200 transition">
            <p class="text-xs text-slate-800 min-w-0 font-medium">{{ $item->urutan }}. {{ $item->teks_item }}</p>
            <form method="POST" action="{{ route('admin.events.angket.item.destroy', [$event, $item]) }}" onsubmit="return confirm('Hapus item ini?')">
                @csrf @method('DELETE')
                <button class="text-slate-400 hover:text-red-600 text-base font-bold px-2 shrink-0 transition" title="Hapus Item">&times;</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="bg-white rounded-3xl border border-slate-200/80 p-10 text-center text-slate-400 text-xs max-w-3xl card-lift-sm">Belum ada item angket evaluasi yang dibuat.</div>
@endforelse
@endsection
