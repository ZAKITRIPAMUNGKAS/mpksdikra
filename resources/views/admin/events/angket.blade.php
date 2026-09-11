@extends('layouts.app')

@section('title', 'Angket — ' . $event->nama_event)

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
    <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-2">Angket Evaluasi Penyelenggaraan (Likert 1–5)</h1>
    <p class="text-xs text-slate-500 mb-5">Instrumen evaluasi kualitas sarana, narasumber, dan kepanitiaan oleh kader</p>
    <form method="POST" action="{{ route('admin.events.angket.item.store', $event) }}" class="flex gap-2 flex-wrap items-end">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
            <input name="kategori" required maxlength="5" value="A"
                class="w-20 px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-center outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        </div>
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-semibold text-slate-600 mb-1">Pernyataan Evaluasi</label>
            <input name="teks_item" required placeholder="Contoh: Materi disampaikan dengan jelas dan aplikatif"
                class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        </div>
        <button class="px-5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">+ Tambah Item</button>
    </form>
</div>

@forelse($items as $kategori => $list)
<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs mb-4 max-w-3xl">
    <h2 class="font-bold text-slate-900 text-sm mb-3">Kategori {{ $kategori }} ({{ $list->count() }} Item)</h2>
    <div class="space-y-2">
        @foreach($list as $item)
        <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-slate-50/70 border border-slate-100">
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
<div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-slate-400 text-xs max-w-3xl">Belum ada item angket evaluasi yang dibuat.</div>
@endforelse
@endsection
