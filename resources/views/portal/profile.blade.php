@extends('layouts.app')

@section('title', 'Biodata Kader — ' . ($anggota?->nama_lengkap ?? auth()->user()->name))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ tab: 'identitas' }">
    {{-- Header Banner with Avatar & Status --}}
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                @if($anggota?->foto)
                    <img src="{{ route('foto.anggota', $anggota) }}" alt="{{ $anggota->nama_lengkap }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-sky-500 shadow-xs shrink-0">
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-slate-950 to-[#005c8a] border-2 border-sky-400 flex items-center justify-center text-[#f8bb00] text-2xl font-black shrink-0 shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $anggota?->nama_lengkap ?? auth()->user()->name }}</h1>
                        @if($anggota?->catatan_verifikasi)
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
                                Catatan Verifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Terdaftar di MPKSDI
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                        <span>NBM: <strong class="text-slate-700 font-mono">{{ $anggota?->nbm ?: 'Belum diisi' }}</strong></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Cabang: <strong class="text-slate-700">{{ $anggota?->kecamatan ?: '-' }}</strong></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Status: <strong class="text-sky-700 font-semibold">{{ $anggota?->status_pernikahan ?: '-' }}</strong></span>
                    </p>
                </div>
            </div>

            <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-slate-700 bg-white hover:bg-sky-50 hover:text-sky-800 border border-slate-200/90 transition shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Portal</span>
            </a>
        </div>

        @if($anggota?->catatan_verifikasi)
            <div class="mt-4 p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold">Catatan Verifikasi Admin:</span> {{ $anggota->catatan_verifikasi }}
                </div>
            </div>
        @endif
    </div>

    @if($anggota)
    {{-- Tab Selector --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 p-1.5 flex gap-1.5 shadow-xs">
        <button type="button" @click="tab = 'identitas'"
            :class="tab === 'identitas' ? 'bg-sky-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
            class="flex-1 py-2.5 px-3 text-xs sm:text-sm rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>1. Identitas &amp; Domisili</span>
        </button>
        <button type="button" @click="tab = 'agama'"
            :class="tab === 'agama' ? 'bg-sky-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
            class="flex-1 py-2.5 px-3 text-xs sm:text-sm rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>2. Ibadah &amp; Keagamaan</span>
        </button>
        <button type="button" @click="tab = 'kemuhammadiyahan'"
            :class="tab === 'kemuhammadiyahan' ? 'bg-sky-500 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 font-medium'"
            class="flex-1 py-2.5 px-3 text-xs sm:text-sm rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>3. Kemuhammadiyahan</span>
        </button>
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Tab 1: Identitas & Domisili --}}
        <div x-show="tab === 'identitas'" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-bold text-slate-900">Identitas Pribadi &amp; Kontak</h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi dasar kader untuk arsip administrasi resmi persyarikatan.</p>
            </div>

            {{-- Pasfoto Upload & Preview --}}
            <div x-data="{ photoPreview: null }" class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-5">
                <div class="shrink-0">
                    <template x-if="photoPreview">
                        <img :src="photoPreview" class="w-20 h-24 rounded-xl object-cover border-2 border-mpksdi-500 shadow-sm">
                    </template>
                    <template x-if="!photoPreview">
                        @if($anggota->foto)
                            <img src="{{ route('foto.anggota', $anggota) }}" class="w-20 h-24 rounded-xl object-cover border border-slate-300 shadow-2xs">
                        @else
                            <div class="w-20 h-24 rounded-xl bg-slate-200 border border-slate-300 flex flex-col items-center justify-center text-slate-400 text-xs">
                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Pasfoto</span>
                            </div>
                        @endif
                    </template>
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Ganti Pasfoto Resmi</label>
                    <p class="text-xs text-slate-500 mb-3">Format JPG/PNG, ukuran maksimal 2MB. Disarankan foto formal berpakaian rapi.</p>
                    <input type="file" name="foto" accept="image/jpeg,image/png"
                        @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }"
                        class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-mpksdi-600 file:text-white hover:file:bg-mpksdi-700 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap (beserta gelar) <span class="text-red-500">*</span></label>
                <input name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required
                    class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input name="tempat_lahir" value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota->tanggal_lahir?->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pernikahan <span class="text-red-500">*</span></label>
                    <select name="status_pernikahan" required class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        @foreach(config('arqam.opsi_form.status_pernikahan') as $v)
                        <option value="{{ $v }}" @selected(old('status_pernikahan', $anggota->status_pernikahan)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Lengkap Domisili <span class="text-red-500">*</span></label>
                <textarea name="alamat_rumah" required rows="2"
                    class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none resize-none transition">{{ old('alamat_rumah', $anggota->alamat_rumah) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Desa / Kelurahan <span class="text-red-500">*</span></label>
                    <input name="desa_kelurahan" value="{{ old('desa_kelurahan', $anggota->desa_kelurahan) }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kecamatan <span class="text-red-500">*</span></label>
                    <input name="kecamatan" value="{{ old('kecamatan', $anggota->kecamatan) }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kabupaten <span class="text-red-500">*</span></label>
                    <input name="kabupaten" value="{{ old('kabupaten', $anggota->kabupaten) }}" required
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Amanah / Jabatan di AUM</label>
                    <input name="jabatan_aum" value="{{ old('jabatan_aum', $anggota->jabatan_aum) }}" placeholder="Contoh: Guru SMK Muhammadiyah 1 Karanganyar"
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pendidikan Terakhir</label>
                    <input name="pendidikan_sd" value="{{ old('pendidikan_sd', $anggota->pendidikan_sd) }}" placeholder="Contoh: S1 Pendidikan Agama Islam"
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="button" @click="tab = 'agama'"
                    class="px-5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                    <span>Lanjut ke Data Keagamaan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Tab 2: Ibadah & Pemahaman Agama --}}
        <div x-show="tab === 'agama'" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6" style="display: none;">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-bold text-slate-900">Pembiasaan Ibadah &amp; Pemahaman Keagamaan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pemetaan kompetensi keislaman untuk standarisasi kurikulum Baitul Arqam.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kemampuan Baca Al-Qur'an</label>
                    <select name="kemampuan_baca_quran" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        <option value="">-- Pilih Kemampuan --</option>
                        @foreach(config('arqam.opsi_form.kemampuan_baca_quran') as $v)
                        <option value="{{ $v }}" @selected(old('kemampuan_baca_quran', $anggota->kemampuan_baca_quran)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hafalan Al-Qur'an</label>
                    <select name="hafalan_quran" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        <option value="">-- Pilih Capaian Hafalan --</option>
                        @foreach(config('arqam.opsi_form.hafalan_quran') as $v)
                        <option value="{{ $v }}" @selected(old('hafalan_quran', $anggota->hafalan_quran)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sholat Berjamaah di Masjid</label>
                    <select name="aktivitas_sholat_masjid" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        <option value="">-- Frekuensi --</option>
                        @foreach(config('arqam.opsi_form.aktivitas_sholat_masjid') as $v)
                        <option value="{{ $v }}" @selected(old('aktivitas_sholat_masjid', $anggota->aktivitas_sholat_masjid)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kehadiran Kajian Keagamaan</label>
                    <select name="aktivitas_kajian_agama" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        <option value="">-- Frekuensi --</option>
                        @foreach(config('arqam.opsi_form.aktivitas_kajian_agama') as $v)
                        <option value="{{ $v }}" @selected(old('aktivitas_kajian_agama', $anggota->aktivitas_kajian_agama)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Koleksi Buku Agama di Rumah</label>
                    <select name="jumlah_buku_agama" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 outline-none transition">
                        <option value="">-- Jumlah Buku --</option>
                        @foreach(config('arqam.opsi_form.jumlah_buku_agama') as $v)
                        <option value="{{ $v }}" @selected(old('jumlah_buku_agama', $anggota->jumlah_buku_agama)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="tab = 'identitas'"
                    class="px-4 py-2 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition text-xs flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali</span>
                </button>
                <button type="button" @click="tab = 'kemuhammadiyahan'"
                    class="px-5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                    <span>Lanjut ke Kemuhammadiyahan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Tab 3: Kemuhammadiyahan & Pengabdian Kemasyarakatan --}}
        {{-- Tab 3: Kemuhammadiyahan & Pengabdian Kemasyarakatan (Ramah Usia 40+) --}}
        <div x-show="tab === 'kemuhammadiyahan'" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6" style="display: none;"
             x-data="{ 
                 isTakmir: '{{ old('keaktifan_takmir', $anggota->keaktifan_takmir ?? 'Tidak') }}',
                 takmirRole: '{{ addslashes(old('jabatan_takmir', $anggota->jabatan_takmir ?? '')) }}',
                 masjidText: '{{ addslashes(old('aktifitas_jamaah_masjid', $anggota->aktifitas_jamaah_masjid ?? '')) }}',
                 masyarakatText: '{{ addslashes(old('keaktifan_kemasyarakatan', $anggota->keaktifan_kemasyarakatan ?? '')) }}',
                 
                 masjidPresets: [
                     'Sholat Berjamaah 5 Waktu',
                     'Sholat Berjamaah Subuh / Isya',
                     'Pengajian Rutin Subuh / Malam',
                     'Pengajian Ahad Pagi',
                     'Tadarus / Kajian Al-Qur\'an',
                     'Panitia Kegiatan Masjid / PHBI'
                 ],
                 takmirPresets: [
                     'Ketua Takmir',
                     'Sekretaris',
                     'Bendahara',
                     'Seksi Ibadah & Dakwah',
                     'Seksi Pembangunan / Sarpras',
                     'Seksi Remaja / TPA',
                     'Imam / Marbot Masjid'
                 ],
                 masyarakatPresets: [
                     'Pengurus RT (Ketua / Pengurus)',
                     'Pengurus RW',
                     'Anggota BPD Desa',
                     'Pengurus Paguyuban Warga',
                     'Kader Posyandu / Kesehatan Desa',
                     'Anggota Linmas / Relawan',
                     'Kelompok Tani / Gapoktan',
                     'Pengurus Rukun Kematian / Sosial',
                     'Pembina Karang Taruna',
                     'Warga Masyarakat'
                 ],

                 toggleMasjid(item) {
                     let list = this.masjidText ? this.masjidText.split(',').map(s => s.trim()).filter(Boolean) : [];
                     if (list.includes(item)) {
                         list = list.filter(x => x !== item);
                     } else {
                         list.push(item);
                     }
                     this.masjidText = list.join(', ');
                 },
                 hasMasjid(item) {
                     return this.masjidText && this.masjidText.includes(item);
                 },

                 toggleMasyarakat(item) {
                     let list = this.masyarakatText ? this.masyarakatText.split(',').map(s => s.trim()).filter(Boolean) : [];
                     if (list.includes(item)) {
                         list = list.filter(x => x !== item);
                     } else {
                         list.push(item);
                     }
                     this.masyarakatText = list.join(', ');
                 },
                 hasMasyarakat(item) {
                     return this.masyarakatText && this.masyarakatText.includes(item);
                 }
             }">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-bold text-slate-900">Kiprah Kemuhammadiyahan, Kemasjidan &amp; Kemasyarakatan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat keaktifan di persyarikatan, aktifitas kemasjidan, dan pengabdian sosial kemasyarakatan.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Langganan Majalah Suara Muhammadiyah</label>
                    <select name="langganan_suara_muhammadiyah" class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition">
                        <option value="">-- Status Langganan --</option>
                        @foreach(config('arqam.opsi_form.langganan_suara_muhammadiyah') as $v)
                        <option value="{{ $v }}" @selected(old('langganan_suara_muhammadiyah', $anggota->langganan_suara_muhammadiyah)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tokoh Muhammadiyah Panutan</label>
                    <input name="tokoh_berpengaruh" value="{{ old('tokoh_berpengaruh', $anggota->tokoh_berpengaruh) }}" placeholder="Contoh: K.H. Ahmad Dahlan, Buya Hamka"
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Organisasi Lain di Luar Muhammadiyah</label>
                <input name="organisasi_lain" value="{{ old('organisasi_lain', $anggota->organisasi_lain) }}" placeholder="Sebutkan jika ada (opsional)"
                    class="w-full px-3.5 py-2.5 text-sm bg-white border border-slate-300 rounded-lg focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition">
            </div>

            {{-- Banner Ramah Usia 40+ --}}
            <div class="p-4 rounded-2xl bg-sky-50/90 border border-sky-200/80 flex items-start gap-3 text-sky-950">
                <div class="w-9 h-9 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-xs font-bold text-base">
                    🕌
                </div>
                <div>
                    <h3 class="text-sm font-bold text-sky-900 leading-snug">Kiprah Dakwah Jamaah &amp; Sosial Kemasyarakatan</h3>
                    <p class="text-xs text-sky-800/90 mt-0.5 leading-relaxed">
                        Sentuh pilihan cepat di bawah ini sesuai rutinitas sehari-hari Bapak/Ibu. Boleh memilih lebih dari satu tanpa perlu mengetik panjang.
                    </p>
                </div>
            </div>

            {{-- 1. Aktivitas Jamaah di Masjid --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3.5">
                <div>
                    <label class="block text-sm font-extrabold text-slate-900 leading-tight">
                        1. Rutinitas Jamaah di Masjid Lingkungan Sekitar
                    </label>
                    <p class="text-xs text-slate-500 mt-1">Sentuh pilihan di bawah ini yang biasa Bapak/Ibu ikuti:</p>
                </div>

                {{-- Quick-Tap Preset Chips --}}
                <div class="flex flex-wrap gap-2 pt-0.5">
                    <template x-for="item in masjidPresets" :key="item">
                        <button type="button" @click="toggleMasjid(item)"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border shadow-2xs active:scale-95 text-left"
                            :class="hasMasjid(item) 
                                ? 'bg-sky-500 text-white border-sky-600 shadow-sky-500/20' 
                                : 'bg-white text-slate-700 border-slate-200 hover:border-sky-300 hover:bg-sky-50/50'">
                            <span x-show="hasMasjid(item)" class="text-white font-black">✓</span>
                            <span x-show="!hasMasjid(item)" class="text-slate-400 font-bold">+</span>
                            <span x-text="item"></span>
                        </button>
                    </template>
                </div>

                {{-- Textarea Sinkronisasi & Catatan Tambahan --}}
                <div class="pt-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Rincian / Catatan Tambahan &amp; Nama Masjid:
                    </label>
                    <textarea name="aktifitas_jamaah_masjid" x-model="masjidText" rows="2"
                        placeholder="Pilihan di atas otomatis tertulis di sini. Bapak/Ibu juga dapat mengetik nama masjid atau kegiatan lain (contoh: Masjid Al-Falah Jetis)..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none bg-white resize-none transition leading-relaxed"></textarea>
                    @error('aktifitas_jamaah_masjid') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 2. Keaktifan sebagai Takmir Masjid --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3.5">
                <div>
                    <label class="block text-sm font-extrabold text-slate-900 leading-tight">
                        2. Amanah sebagai Pengurus / Takmir Masjid
                    </label>
                    <p class="text-xs text-slate-500 mt-1">Apakah saat ini Bapak/Ibu diamanahi sebagai takmir atau pengurus masjid/musholla?</p>
                </div>

                {{-- Large Selection Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all flex items-center gap-3.5 bg-white shadow-2xs active:scale-[0.99]"
                        :class="isTakmir === 'Tidak' ? 'border-slate-800 bg-slate-900 text-white shadow-md' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                        <input type="radio" name="keaktifan_takmir" value="Tidak" x-model="isTakmir" class="sr-only">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                            :class="isTakmir === 'Tidak' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'">
                            👤
                        </div>
                        <div>
                            <span class="text-xs sm:text-sm font-extrabold block">Bukan Takmir</span>
                            <span class="text-[11px] block" :class="isTakmir === 'Tidak' ? 'text-slate-300' : 'text-slate-400'">Sebagai jamaah masjid / musholla</span>
                        </div>
                    </label>

                    <label class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all flex items-center gap-3.5 bg-white shadow-2xs active:scale-[0.99]"
                        :class="isTakmir === 'Ya' ? 'border-sky-500 bg-sky-50 text-sky-950 shadow-md' : 'border-slate-200 text-slate-700 hover:border-slate-300'">
                        <input type="radio" name="keaktifan_takmir" value="Ya" x-model="isTakmir" class="sr-only">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0"
                            :class="isTakmir === 'Ya' ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-500'">
                            🕌
                        </div>
                        <div>
                            <span class="text-xs sm:text-sm font-extrabold block">Ya, Pengurus Takmir</span>
                            <span class="text-[11px] block" :class="isTakmir === 'Ya' ? 'text-sky-700' : 'text-slate-400'">Aktif di kepengurusan takmir</span>
                        </div>
                    </label>
                </div>

                {{-- Conditional Role Picker if "Ya" --}}
                <div x-show="isTakmir === 'Ya'" x-transition class="pt-3 border-t border-slate-200/80 space-y-2.5">
                    <label class="block text-xs font-bold text-slate-700">
                        Sentuh Amanah / Posisi Anda di Takmir:
                    </label>
                    
                    <div class="flex flex-wrap gap-2">
                        <template x-for="role in takmirPresets" :key="role">
                            <button type="button" @click="takmirRole = role"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border shadow-2xs active:scale-95"
                                :class="takmirRole === role 
                                    ? 'bg-sky-500 text-white border-sky-600' 
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-sky-50/50 hover:border-sky-300'">
                                <span x-text="role"></span>
                            </button>
                        </template>
                    </div>

                    <div class="pt-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Rincian Jabatan &amp; Nama Masjid:
                        </label>
                        <input type="text" name="jabatan_takmir" x-model="takmirRole"
                            placeholder="Contoh: Ketua Takmir Masjid Mujahidin / Sie Dakwah..."
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none bg-white transition">
                        @error('jabatan_takmir') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Keaktifan di Kemasyarakatan --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-3.5">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <label class="block text-sm font-extrabold text-slate-900 leading-tight">
                            3. Keaktifan di Lingkungan Kemasyarakatan
                        </label>
                        <span class="text-xs text-sky-700 font-semibold bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-100 self-start sm:self-auto">RT / RW / BPD / Karang Taruna dll.</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Sentuh peran masyarakat yang pernah atau sedang Bapak/Ibu jalankan:</p>
                </div>

                {{-- Quick-Tap Preset Chips --}}
                <div class="flex flex-wrap gap-2 pt-0.5">
                    <template x-for="item in masyarakatPresets" :key="item">
                        <button type="button" @click="toggleMasyarakat(item)"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border shadow-2xs active:scale-95 text-left"
                            :class="hasMasyarakat(item) 
                                ? 'bg-sky-500 text-white border-sky-600 shadow-sky-500/20' 
                                : 'bg-white text-slate-700 border-slate-200 hover:border-sky-300 hover:bg-sky-50/50'">
                            <span x-show="hasMasyarakat(item)" class="text-white font-black">✓</span>
                            <span x-show="!hasMasyarakat(item)" class="text-slate-400 font-bold">+</span>
                            <span x-text="item"></span>
                        </button>
                    </template>
                </div>

                {{-- Textarea Sinkronisasi & Catatan Tambahan --}}
                <div class="pt-1">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                        Rincian Peran &amp; Nama Dusun/Wilayah:
                    </label>
                    <textarea name="keaktifan_kemasyarakatan" x-model="masyarakatText" rows="2"
                        placeholder="Pilihan di atas otomatis tertulis di sini. Bapak/Ibu juga dapat mengetik rincian RT/RW/Desa (contoh: Ketua RT 02 RW 04 Dusun Dagen)..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none bg-white resize-none transition leading-relaxed"></textarea>
                    @error('keaktifan_kemasyarakatan') <p class="text-rose-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="tab = 'agama'"
                    class="px-5 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-full hover:bg-slate-50 transition text-xs flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali</span>
                </button>
            </div>
        </div>

        {{-- Submission Bar --}}
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md rounded-2xl border border-sky-100 shadow-lg p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 card-lift-sm">
            <div class="text-xs text-slate-500 text-center sm:text-left flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Perubahan data profil akan langsung tersimpan di pangkalan data MPKSDI PDM Karanganyar.</span>
            </div>
            <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-[#f8bb00] hover:bg-amber-400 active:scale-[0.99] text-slate-950 font-black text-sm rounded-full border border-amber-400 shadow-md transition hover:scale-[1.02]">
                Simpan Seluruh Perubahan Profil &rarr;
            </button>
        </div>
    </form>
    @else
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-sm shadow-xs">
        Data profil anggota belum terhubung dengan akun ini. Silakan hubungi admin MPKSDI PDM Karanganyar.
    </div>
    @endif
</div>
@endsection
