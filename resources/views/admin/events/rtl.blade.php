@extends('layouts.app')

@section('title', 'RTL — ' . $event->nama_event)

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
            Manajemen Dokumen Aksi
        </span>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">Instrumen Rencana Tindak Lanjut (RTL)</h1>
        <p class="text-xs text-slate-500 mb-5 font-normal">Susun pertanyaan komitmen aksi dakwah pasca-perkaderan untuk dijawab oleh peserta.</p>

        <form method="POST" action="{{ route('admin.events.rtl.soal.store', $event) }}" class="flex gap-2 flex-wrap items-end mb-5">
            @csrf
            <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Pertanyaan / Tugas RTL</label>
                <input name="pertanyaan" required placeholder="Contoh: Rencana aksi dakwah di ranting dalam 3 bulan ke depan"
                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tipe Respon</label>
                <select name="tipe" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-sky-500/20 cursor-pointer transition">
                    <option value="essay">Uraian / Essay</option>
                    <option value="upload">Upload Berkas Bukti</option>
                </select>
            </div>
            <button class="px-4 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">+ Tambah Soal</button>
        </form>

        <div class="space-y-2">
            @forelse($soal as $s)
            <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-slate-50/70 border border-slate-200/70 shadow-2xs">
                <p class="text-xs text-slate-800 min-w-0 font-medium">
                    {{ $s->urutan }}. {{ $s->pertanyaan }}
                    <span class="text-xs font-medium uppercase tracking-wider ml-1.5 text-sky-800 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">
                        {{ $s->tipe }}
                    </span>
                </p>
                <form method="POST" action="{{ route('admin.events.rtl.soal.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus pertanyaan RTL ini?')">
                    @csrf @method('DELETE')
                    <button class="text-slate-400 hover:text-red-600 text-base font-bold px-2 shrink-0 transition" title="Hapus">&times;</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-4 font-normal">Belum ada pertanyaan RTL yang dibuat.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
    <h2 class="font-bold text-slate-900 text-base mb-3 tracking-tight">Pengumpulan RTL Peserta ({{ $rtls->count() }})</h2>
    <div class="space-y-2">
        @forelse($rtls as $r)
        <a href="{{ route('admin.events.rtl.show', [$event, $r]) }}" class="flex items-center justify-between gap-3 p-3 rounded-lg border border-slate-200/70 bg-slate-50/40 hover:border-sky-300 hover:bg-sky-50/30 transition">
            <div class="min-w-0">
                <p class="font-semibold text-xs sm:text-sm text-slate-900 truncate">{{ $r->anggota->nama_lengkap }}</p>
                <p class="text-xs text-slate-400 font-normal">Dikirim {{ $r->updated_at->diffForHumans() }}</p>
            </div>
            <span class="shrink-0 px-2.5 py-0.5 rounded-md text-xs font-medium uppercase tracking-wider shadow-2xs
                {{ $r->status === 'terkirim' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                {{ $r->status === 'disetujui' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                {{ $r->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                {{ $r->status === 'draf' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                {{ $r->status }}
            </span>
        </a>
        @empty
        <p class="text-xs text-slate-400 text-center py-6 font-normal">Belum ada peserta yang mengumpulkan RTL.</p>
        @endforelse
    </div>
</div>
@endsection
