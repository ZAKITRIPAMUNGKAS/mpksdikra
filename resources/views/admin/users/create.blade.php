@extends('layouts.app')

@section('title', 'Tambah Akun Staf Baru')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumbs Navigation --}}
    <div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Staf</span>
        </a>
    </div>

    {{-- 2-Column Workstation Grid --}}
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: Staff Creation Form (8 of 12 columns) --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs relative overflow-hidden">
            <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.20] pointer-events-none" />
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#005c8a] animate-pulse"></span>
                        Registrasi Personalia
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">Tambah Akun Staf Baru</h1>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 font-normal">Buat akun untuk Fasilitator, Eksekutif (Pimpinan), atau Administrator sistem.</p>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4" x-data="{ role: '{{ old('role', 'fasilitator') }}' }">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap &amp; Gelar <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-normal rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs"
                            placeholder="Contoh: Dr. H. Ahmad Dahlan, M.Ag atau Admin PCM Colomadu">
                        @error('name') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Email / Username Login <span class="text-red-500">*</span></label>
                            <input type="text" name="email" value="{{ old('email') }}" required
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-normal rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs"
                                placeholder="Email (misal: staf@mpksdi.test) atau username cabang (misal: colomadu)">
                            @error('email') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp (Opsional)</label>
                            <input type="text" name="no_wa" value="{{ old('no_wa') }}"
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-normal rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs"
                                placeholder="08123456789">
                            @error('no_wa') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Peran / Hak Akses <span class="text-red-500">*</span></label>
                        <select name="role" x-model="role" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-semibold rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs cursor-pointer">
                            <option value="fasilitator" @selected(old('role')==='fasilitator')>Fasilitator (Penugasan event: scanner presensi, nilai observasi, keaktifan)</option>
                            <option value="executive" @selected(old('role')==='executive')>Eksekutif / Pimpinan (Akses tinjau laporan, monitoring, &amp; ekspor data)</option>
                            <option value="admin_cabang" @selected(old('role')==='admin_cabang')>Admin Cabang (Pengelola pangkalan data &amp; verifikasi kader tingkat PCM)</option>
                            <option value="admin" @selected(old('role')==='admin')>Admin Utama Daerah (Akses penuh ke konfigurasi dan manajemen sistem PDM)</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Dynamic PCM Dropdown untuk Admin Cabang --}}
                    <div x-show="role === 'admin_cabang'" x-transition class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                        <label class="block text-xs font-bold text-emerald-950 uppercase tracking-wider mb-1.5">Wilayah Pimpinan Cabang Muhammadiyah (PCM) <span class="text-red-500">*</span></label>
                        <select name="pcm" :required="role === 'admin_cabang'" class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-semibold rounded-lg border border-emerald-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 bg-white outline-none transition shadow-2xs cursor-pointer">
                            <option value="">-- Pilih Wilayah PCM se-Kabupaten Karanganyar --</option>
                            @foreach(['Colomadu', 'Gondangrejo', 'Jaten', 'Jatipuro', 'Jatiyoso', 'Jenawi', 'Jumantono', 'Jumapolo', 'Karanganyar', 'Karangpandan', 'Kebakkramat', 'Kerjo', 'Matesih', 'Mojogedang', 'Ngargoyoso', 'Tasikmadu', 'Tawangmangu'] as $pcm)
                                <option value="{{ $pcm }}" @selected(old('pcm') === $pcm)>PCM {{ $pcm }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-emerald-800 mt-1.5 font-normal">Admin cabang hanya memiliki kewenangan melihat dan memverifikasi berkas kader di PCM yang ditugaskan.</p>
                        @error('pcm') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi (Password) <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-normal rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs"
                                placeholder="Minimal 8 karakter">
                            @error('password') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Sandi <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required minlength="8"
                                class="w-full px-3.5 py-2.5 text-xs sm:text-sm font-normal rounded-lg border border-slate-200 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 bg-slate-50/70 focus:bg-white outline-none transition shadow-2xs"
                                placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate-100 flex justify-end items-center gap-3">
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-600 transition shadow-2xs">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-2xs transition">
                            Simpan &amp; Aktifkan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right: Role Matrix & Guidelines Sidebar (4 of 12 columns) --}}
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
            {{-- Role Matrix Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Matriks Wewenang Peran</span>
                <div class="space-y-3.5 text-xs">
                    <div class="p-3 rounded-lg bg-emerald-50/70 border border-emerald-200/80">
                        <strong class="text-emerald-950 block font-bold mb-1">Admin Cabang (PCM):</strong>
                        <p class="text-slate-600 leading-relaxed font-normal">
                            Akses panel pangkalan data tingkat cabang. Berwenang memverifikasi, menolak dengan catatan, mereset kata sandi, dan mengunduh rekapitulasi data kader (Excel) di PCM masing-masing.
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-sky-50/60 border border-sky-200/80">
                        <strong class="text-sky-900 block font-bold mb-1">Fasilitator Perkaderan:</strong>
                        <p class="text-slate-600 leading-relaxed font-normal">
                            Diberi akses penugasan per kegiatan. Berwenang mengoperasikan scanner presensi QR, menginput lembar observasi psikomotorik, dan mencatat poin keaktifan kader.
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-amber-50/60 border border-amber-200/80">
                        <strong class="text-amber-950 block font-bold mb-1">Eksekutif / Pimpinan:</strong>
                        <p class="text-slate-600 leading-relaxed font-normal">
                            Akses desk tinjauan eksekutif. Dapat memantau seluruh agenda perkaderan, memeriksa hasil penilaian &amp; peringkat kelulusan, serta mengunduh dokumen rekapitulasi Excel.
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <strong class="text-slate-900 block font-bold mb-1">Administrator Utama Daerah:</strong>
                        <p class="text-slate-600 leading-relaxed font-normal">
                            Hak akses penuh seluruh sistem: memverifikasi pendaftaran kader, konfigurasi 17 cabang, kelola akun personalia, bank soal CBT, dan penerbitan sertifikat.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Security Policy Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs text-xs text-slate-500 space-y-2">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Kebijakan Keamanan:</span>
                <p class="font-normal leading-relaxed">
                    Setiap staf harus menggunakan kata sandi unik minimal 8 karakter. Akun dapat dinonaktifkan sementara sewaktu-waktu oleh Administrator melalui panel daftar staf.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
