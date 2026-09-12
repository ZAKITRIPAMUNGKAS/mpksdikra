@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event — ' . $event->nama_event : 'Buat Kegiatan Baitul Arqam Baru')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumbs Navigation --}}
    <div>
        <a href="{{ isset($event) ? route('admin.events.show', $event) : route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke {{ isset($event) ? $event->nama_event : 'Daftar Event' }}</span>
        </a>
    </div>

    {{-- 2-Column Workstation Grid --}}
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: Event Form (8 of 12 columns) --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs relative overflow-hidden">
            <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.20] pointer-events-none" />
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#005c8a] animate-pulse"></span>
                        Manajemen Konfigurasi Event
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">
                    {{ isset($event) ? 'Edit Kegiatan Baitul Arqam' : 'Buat Kegiatan Baitul Arqam Baru' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 font-normal">
                    Lengkapi parameter kegiatan, kuota kader, jadwal pelaksanaan, dan pola nomor sertifikat resmi.
                </p>

                <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" class="space-y-4">
                    @csrf
                    @isset($event) @method('PUT') @endisset

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Event <span class="text-red-500">*</span></label>
                        <input name="nama_event" value="{{ old('nama_event', $event->nama_event ?? '') }}" required placeholder="Contoh: Baitul Arqam Pimpinan Cabang & Ranting PCM Tasikmadu"
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        @error('nama_event')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($event) ? $event->tanggal_mulai?->format('Y-m-d') : '') }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('tanggal_mulai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($event) ? $event->tanggal_selesai?->format('Y-m-d') : '') }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('tanggal_selesai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Kegiatan</label>
                            <input name="lokasi" value="{{ old('lokasi', $event->lokasi ?? '') }}" placeholder="Contoh: Gedung Dakwah Muhammadiyah Karanganyar"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Kapasitas Kuota (Orang)</label>
                            <input type="number" name="kuota" min="1" value="{{ old('kuota', $event->kuota ?? '') }}" placeholder="Kosongkan jika tanpa batas"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('kuota')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Singkat Kegiatan</label>
                        <textarea name="deskripsi" rows="3" placeholder="Informasi sasaran kader, tema perkaderan, atau catatan pelaksanaan..."
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition resize-none shadow-2xs">{{ old('deskripsi', $event->deskripsi ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Status Kegiatan <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs font-medium cursor-pointer">
                                @foreach(['persiapan','berlangsung','selesai'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $event->status ?? 'persiapan') === $s)>
                                    {{ ucfirst($s) }} {{ $s === 'persiapan' ? '(Pendaftaran/Persiapan)' : ($s === 'berlangsung' ? '(Pelaksanaan Aktif)' : '(Kegiatan Rampung)') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Format Nomor Sertifikat</label>
                            <input name="format_nomor_sertifikat" value="{{ old('format_nomor_sertifikat', $event->format_nomor_sertifikat ?? '{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}') }}"
                                placeholder="{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ isset($event) ? route('admin.events.show', $event) : route('admin.events.index') }}"
                           class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-600 transition shadow-2xs">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white font-semibold text-xs sm:text-sm rounded-lg shadow-2xs transition">
                            {{ isset($event) ? 'Simpan Perubahan' : 'Buat Kegiatan Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right: Guidance & Best Practices Sidebar (4 of 12 columns) --}}
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
            {{-- Workflow Status Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Siklus Hidup Event</span>
                <ul class="space-y-3 text-xs">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">1</span>
                        <div>
                            <strong class="text-slate-800 block">Persiapan:</strong>
                            <p class="text-slate-500 font-normal">Fase pendaftaran kader, penentuan soal CBT, rubrik psikomotor, dan penugasan fasilitator.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">2</span>
                        <div>
                            <strong class="text-slate-800 block">Berlangsung:</strong>
                            <p class="text-slate-500 font-normal">Sesi materi berjalan, scanner presensi QR aktif, ujian pretest/posttest dibuka, dan observasi dicatat.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">3</span>
                        <div>
                            <strong class="text-slate-800 block">Selesai:</strong>
                            <p class="text-slate-500 font-normal">Nilai akhir dikunci, peringkat diterbitkan, serta laporan PDF dan sertifikat resmi dapat diunduh.</p>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- Certificate Format Tags Cheat Sheet --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Tag Format Sertifikat</span>
                <p class="text-xs text-slate-500 mb-3 font-normal">Gunakan tag dinamis berikut dalam format penomoran:</p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{nomor}</code>
                        <span class="text-slate-500">Urutan 3 digit (001, 002)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{bulan_romawi}</code>
                        <span class="text-slate-500">Bulan Romawi (I s.d. XII)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{bulan}</code>
                        <span class="text-slate-500">Bulan 2 digit (01 s.d. 12)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{tahun}</code>
                        <span class="text-slate-500">Tahun 4 digit ({{ date('Y') }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
