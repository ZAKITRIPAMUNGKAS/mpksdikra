@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event' : 'Buat Event')

@section('content')
<div class="mb-4"><a href="{{ route('admin.events.index') }}" class="text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">&larr; Daftar Event</a></div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-8 max-w-2xl shadow-xs">
    <h1 class="text-xl font-bold text-slate-900 mb-6">{{ isset($event) ? 'Edit Event' : 'Buat Event Baru' }}</h1>

    <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" class="space-y-5">
        @csrf
        @isset($event) @method('PUT') @endisset

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Event <span class="text-red-500">*</span></label>
            <input name="nama_event" value="{{ old('nama_event', $event->nama_event ?? '') }}" required
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
            @error('nama_event')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($event) ? $event->tanggal_mulai?->format('Y-m-d') : '') }}" required
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
                @error('tanggal_mulai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($event) ? $event->tanggal_selesai?->format('Y-m-d') : '') }}" required
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
                @error('tanggal_selesai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi</label>
                <input name="lokasi" value="{{ old('lokasi', $event->lokasi ?? '') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kuota</label>
                <input type="number" name="kuota" min="1" value="{{ old('kuota', $event->kuota ?? '') }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
                @error('kuota')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition resize-none">{{ old('deskripsi', $event->deskripsi ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
            <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none transition">
                @foreach(['persiapan','berlangsung','selesai'] as $s)
                <option value="{{ $s }}" @selected(old('status', $event->status ?? 'persiapan') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="w-full py-3 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white font-bold text-sm rounded-lg shadow-xs transition">
            {{ isset($event) ? 'Simpan Perubahan' : 'Buat Event' }}
        </button>
    </form>
</div>
@endsection
