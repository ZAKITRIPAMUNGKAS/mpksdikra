@extends('layouts.app')

@section('title', 'RTL — ' . $event->nama_event)

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
    <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-4">Instrumen Rencana Tindak Lanjut (RTL)</h1>
    <form method="POST" action="{{ route('admin.events.rtl.soal.store', $event) }}" class="flex gap-2 flex-wrap items-end mb-5">
        @csrf
        <div class="flex-1 min-w-[240px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pertanyaan / Tugas RTL</label>
            <input name="pertanyaan" required placeholder="Contoh: Rencana aksi dakwah di ranting dalam 3 bulan ke depan"
                class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tipe Respon</label>
            <select name="tipe" class="px-4 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-mpksdi-500 cursor-pointer transition">
                <option value="essay">Uraian / Essay</option>
                <option value="upload">Upload Berkas Bukti</option>
            </select>
        </div>
        <button class="px-5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">+ Tambah Soal</button>
    </form>

    <div class="space-y-2">
        @forelse($soal as $s)
        <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-slate-50/70 border border-slate-100">
            <p class="text-xs text-slate-800 min-w-0 font-medium">
                {{ $s->urutan }}. {{ $s->pertanyaan }}
                <span class="text-[10px] font-bold uppercase tracking-wider ml-1 text-mpksdi-700 bg-mpksdi-50 px-1.5 py-0.5 rounded border border-mpksdi-200">
                    {{ $s->tipe }}
                </span>
            </p>
            <form method="POST" action="{{ route('admin.events.rtl.soal.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus pertanyaan RTL ini?')">
                @csrf @method('DELETE')
                <button class="text-slate-400 hover:text-red-600 text-base font-bold px-2 shrink-0 transition" title="Hapus">&times;</button>
            </form>
        </div>
        @empty
        <p class="text-xs text-slate-400 text-center py-4">Belum ada pertanyaan RTL yang dibuat.</p>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs max-w-3xl">
    <h2 class="font-bold text-slate-900 text-sm mb-4">Pengumpulan RTL Peserta ({{ $rtls->count() }})</h2>
    <div class="space-y-2">
        @forelse($rtls as $r)
        <a href="{{ route('admin.events.rtl.show', [$event, $r]) }}" class="flex items-center justify-between gap-3 p-3.5 rounded-lg border border-slate-200/80 bg-slate-50/30 hover:border-mpksdi-400 hover:bg-mpksdi-50/30 transition">
            <div class="min-w-0">
                <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $r->anggota->nama_lengkap }}</p>
                <p class="text-[11px] text-slate-400">Dikirim {{ $r->updated_at->diffForHumans() }}</p>
            </div>
            <span class="shrink-0 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                {{ $r->status === 'terkirim' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                {{ $r->status === 'disetujui' ? 'bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200' : '' }}
                {{ $r->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                {{ $r->status === 'draf' ? 'bg-slate-100 text-slate-500 border border-slate-200' : '' }}">
                {{ $r->status }}
            </span>
        </a>
        @empty
        <p class="text-xs text-slate-400 text-center py-6">Belum ada peserta yang mengumpulkan RTL.</p>
        @endforelse
    </div>
</div>
@endsection
