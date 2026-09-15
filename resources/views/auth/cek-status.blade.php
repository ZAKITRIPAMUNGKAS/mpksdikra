@extends('layouts.guest')

@section('title', 'Cek Status Pendaftaran — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen flex flex-col justify-between py-8 px-4 sm:px-6 lg:px-8 relative overflow-hidden bg-slate-50">
    {{-- Header Mini Navbar --}}
    <div class="max-w-xl mx-auto w-full flex items-center justify-between mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="hidden sm:inline">Kembali ke </span><span>Beranda</span>
        </a>
        <a href="{{ route('login') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 transition">
            Masuk Portal &rarr;
        </a>
    </div>

    {{-- Main Container Card --}}
    <div class="max-w-xl mx-auto w-full">
        <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden relative p-6 sm:p-8">
            <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />

            {{-- Logo & Heading --}}
            <div class="text-center relative z-10 mb-6">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="w-16 h-16 mx-auto mb-3 drop-shadow-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-sky-50 text-sky-800 border border-sky-200">
                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                    Layanan Mandiri Kader
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2.5">Cek Status Pendaftaran</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1.5 max-w-md mx-auto font-medium">
                    Ketahui status verifikasi berkas pendaftaran kader Anda tanpa harus login terlebih dahulu.
                </p>
            </div>

            {{-- Search Form --}}
            <form action="{{ route('cek-status.check') }}" method="POST" class="space-y-4 sm:space-y-5 relative z-10">
                @csrf
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                        Masukkan NIK (16 Angka) atau Nomor WhatsApp
                    </label>
                    <div class="relative">
                        <input type="text" name="identifier" value="{{ old('identifier', request('identifier', $identifier ?? '')) }}"
                            required autofocus placeholder="Contoh: 331301... atau 08123456..."
                            class="w-full px-4 py-3.5 pl-12 bg-white border-2 border-slate-300 rounded-2xl text-base font-medium text-slate-900 focus:ring-4 focus:ring-sky-100 focus:border-sky-600 outline-none transition min-h-[52px]">
                        <svg class="w-5 h-5 text-slate-400 absolute left-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    @error('identifier')
                        <p class="text-xs sm:text-sm text-red-600 font-bold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full min-h-[52px] sm:min-h-[54px] bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-base rounded-2xl shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Periksa Status Berkas</span>
                </button>
            </form>

            {{-- Result Area --}}
            @if(isset($searched))
                <div class="mt-8 pt-6 border-t-2 border-slate-200 relative z-10">
                    @if($anggota)
                        <div class="rounded-2xl p-5 sm:p-6 border-2 
                            {{ $anggota->user->status === 'aktif' ? 'bg-emerald-50 border-emerald-300' : '' }}
                            {{ $anggota->user->status === 'ditolak' ? 'bg-rose-50 border-rose-300' : '' }}
                            {{ $anggota->user->status === 'nonaktif' ? 'bg-slate-100 border-slate-300' : '' }}
                            {{ $anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-50 border-amber-300' : '' }}">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 
                                    {{ $anggota->user->status === 'aktif' ? 'bg-emerald-600 text-white shadow-sm' : '' }}
                                    {{ $anggota->user->status === 'ditolak' ? 'bg-rose-600 text-white shadow-sm' : '' }}
                                    {{ $anggota->user->status === 'nonaktif' ? 'bg-slate-600 text-white shadow-sm' : '' }}
                                    {{ $anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-500 text-white shadow-sm' : '' }}">
                                    @if($anggota->user->status === 'aktif')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($anggota->user->status === 'ditolak')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @elseif($anggota->user->status === 'nonaktif')
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-7 h-7 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs sm:text-sm font-black uppercase tracking-wider 
                                        {{ $anggota->user->status === 'aktif' ? 'text-emerald-900' : '' }}
                                        {{ $anggota->user->status === 'ditolak' ? 'text-rose-900' : '' }}
                                        {{ $anggota->user->status === 'nonaktif' ? 'text-slate-800' : '' }}
                                        {{ $anggota->user->status === 'menunggu_verifikasi' ? 'text-amber-900' : '' }}">
                                        Status Pendaftaran
                                    </div>
                                    <h3 class="text-lg sm:text-xl font-black text-slate-900 mt-1 truncate">{{ $anggota->nama_lengkap }}</h3>
                                    <p class="text-xs sm:text-sm text-slate-600 font-mono mt-0.5">
                                        NIK: {{ substr($anggota->nik, 0, 6) }}******{{ substr($anggota->nik, -4) }} &bull; Cabang: {{ $anggota->kecamatan }}
                                    </p>

                                    @if($anggota->user->status === 'aktif')
                                        <div class="mt-3.5 text-xs sm:text-sm text-emerald-950 space-y-1">
                                            <p class="font-bold">Alhamdulillah, berkas pendaftaran Anda telah DISETUJUI &amp; AKTIF.</p>
                                            <p class="text-emerald-900">Anda sudah dapat masuk ke Portal Kader untuk melihat penugasan kegiatan Baitul Arqam.</p>
                                        </div>
                                        <div class="mt-4">
                                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 min-h-[48px] px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-black text-sm sm:text-base shadow-sm transition">
                                                <span>Masuk ke Portal Kader</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @elseif($anggota->user->status === 'ditolak')
                                        <div class="mt-3.5 text-xs sm:text-sm text-rose-950 space-y-1.5">
                                            <p class="font-bold">Berkas pendaftaran Anda memerlukan perbaikan:</p>
                                            <div class="p-3.5 bg-white rounded-2xl border-2 border-rose-200 text-rose-900 italic font-medium">
                                                "{{ $anggota->catatan_verifikasi ?: 'Mohon periksa kembali kelengkapan biodata Anda.' }}"
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2.5">
                                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 min-h-[48px] px-5 rounded-2xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-black text-sm sm:text-base shadow-sm transition">
                                                <span>Masuk &amp; Perbaiki Berkas</span>
                                            </a>
                                            <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}" target="_blank" class="inline-flex items-center gap-2 min-h-[48px] px-5 rounded-2xl bg-white hover:bg-slate-100 border-2 border-slate-300 text-slate-800 font-bold text-xs sm:text-sm shadow-xs transition">
                                                <span>Bantuan Helpdesk WA</span>
                                            </a>
                                        </div>
                                    @elseif($anggota->user->status === 'nonaktif')
                                        <div class="mt-3.5 text-xs sm:text-sm text-slate-800 space-y-1.5">
                                            <p class="font-bold">Akun pendaftaran / keanggotaan ini berstatus NONAKTIF:</p>
                                            <div class="p-3.5 bg-white rounded-2xl border-2 border-slate-300 text-slate-700 italic font-medium">
                                                "{{ $anggota->catatan_verifikasi ?: 'Penonaktifan oleh Administrator MPKSDI.' }}"
                                            </div>
                                            <p class="text-slate-600 text-xs mt-1">Jika memerlukan klarifikasi atau pengaktifan kembali, silakan hubungi kontak sekretariat:</p>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2.5">
                                            <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}" target="_blank" class="inline-flex items-center gap-2 min-h-[48px] px-5 rounded-2xl bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-xs transition">
                                                <span>💬 Hubungi Helpdesk MPKSDI</span>
                                            </a>
                                        </div>
                                    @else
                                        <div class="mt-3.5 text-xs sm:text-sm text-amber-950 space-y-1">
                                            <p class="font-bold">Berkas berhasil tersimpan dan sedang menunggu giliran verifikasi.</p>
                                            <p class="text-amber-900 font-medium">Admin verifikator MPKSDI sedang memeriksa kelengkapan data. Anda akan menerima pemberitahuan via WhatsApp setelah disetujui.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl p-6 bg-slate-50 border-2 border-slate-200 text-center space-y-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-slate-200 text-slate-600 flex items-center justify-center mx-auto text-xl">
                                🔍
                            </div>
                            <div>
                                <h4 class="text-base sm:text-lg font-black text-slate-900">Data Pendaftaran Tidak Ditemukan</h4>
                                <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">
                                    Tidak ditemukan berkas dengan NIK atau No WhatsApp "<strong>{{ $identifier }}</strong>".
                                </p>
                            </div>
                            <div class="pt-2 flex justify-center">
                                <a href="{{ route('anggota.register') }}" class="inline-flex items-center gap-2 min-h-[48px] px-6 rounded-2xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-sm sm:text-base shadow-sm transition">
                                    <span>Daftar Kader Baru Sekarang &rarr;</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Footer Help Link --}}
            <div class="mt-8 pt-6 border-t-2 border-slate-100 text-center relative z-10">
                <p class="text-xs sm:text-sm text-slate-600 font-medium">
                    Mengalami kesulitan saat mengecek data? Hubungi
                    <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}" target="_blank" class="font-extrabold text-emerald-700 hover:text-emerald-900 underline">
                        Helpdesk WhatsApp MPKSDI
                    </a>
                </p>
            </div>
        </div>
    </div>

    {{-- Subfooter simple --}}
    <div class="text-center mt-6 text-xs text-slate-400 font-normal">
        &copy; {{ date('Y') }} MPKSDI PDM Karanganyar &bull; Sistem Perkaderan Baitul Arqam
    </div>
</div>
@endsection
