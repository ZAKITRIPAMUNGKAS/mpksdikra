@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event' : 'Buat Event')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Daftar Event</span>
    </a>
</div>

<div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 max-w-2xl shadow-xs card-lift-sm relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
            Manajemen Formulir Event
        </span>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-6">{{ isset($event) ? 'Edit Kegiatan Baitul Arqam' : 'Buat Kegiatan Baitul Arqam Baru' }}</h1>

        <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" class="space-y-5">
            @csrf
            @isset($event) @method('PUT') @endisset

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Event <span class="text-red-500">*</span></label>
                <input name="nama_event" value="{{ old('nama_event', $event->nama_event ?? '') }}" required
                    class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                @error('nama_event')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($event) ? $event->tanggal_mulai?->format('Y-m-d') : '') }}" required
                        class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                    @error('tanggal_mulai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($event) ? $event->tanggal_selesai?->format('Y-m-d') : '') }}" required
                        class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                    @error('tanggal_selesai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi</label>
                    <input name="lokasi" value="{{ old('lokasi', $event->lokasi ?? '') }}"
                        class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kuota</label>
                    <input type="number" name="kuota" min="1" value="{{ old('kuota', $event->kuota ?? '') }}"
                        class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                    @error('kuota')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                    class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition resize-none">{{ old('deskripsi', $event->deskripsi ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="w-full px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                    @foreach(['persiapan','berlangsung','selesai'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $event->status ?? 'persiapan') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full py-3 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-black text-sm rounded-full shadow-xs transition hover:scale-[1.01]">
                {{ isset($event) ? 'Simpan Perubahan' : 'Buat Event' }}
            </button>
        </form>
    </div>
</div>
@endsection
