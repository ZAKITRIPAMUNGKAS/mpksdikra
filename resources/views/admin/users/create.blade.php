@extends('layouts.app')

@section('title', 'Tambah Akun Staf')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke daftar staf
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-8 shadow-xs max-w-2xl">
    <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-1">Tambah Akun Staf Baru</h1>
    <p class="text-xs text-slate-500 mb-6">Buat akun untuk Fasilitator, Eksekutif (Pimpinan), atau Administrator sistem.</p>

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap &amp; Gelar:</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-3.5 py-2.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                placeholder="Contoh: Dr. H. Ahmad Dahlan, M.Ag">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Akun (Digunakan untuk Login):</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full px-3.5 py-2.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                placeholder="staf@mpksdi.test">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Peran / Role:</label>
            <select name="role" required class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white">
                <option value="fasilitator" @selected(old('role')==='fasilitator')>Fasilitator (Dapat ditugaskan ke event untuk presensi &amp; nilai)</option>
                <option value="executive" @selected(old('role')==='executive')>Executive / Pimpinan (Akses tinjau laporan &amp; dashboard eksekutif)</option>
                <option value="admin" @selected(old('role')==='admin')>Admin Utama (Akses penuh seluruh konfigurasi)</option>
            </select>
            @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp (Opsional):</label>
            <input type="text" name="no_wa" value="{{ old('no_wa') }}"
                class="w-full px-3.5 py-2.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                placeholder="08123456789">
            @error('no_wa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi (Password):</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-3.5 py-2.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                    placeholder="Minimal 8 karakter">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Sandi:</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-3.5 py-2.5 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 bg-white"
                    placeholder="Ulangi kata sandi">
            </div>
        </div>

        <div class="pt-5 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-600 transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
                Simpan &amp; Aktifkan Akun
            </button>
        </div>
    </form>
</div>
@endsection
