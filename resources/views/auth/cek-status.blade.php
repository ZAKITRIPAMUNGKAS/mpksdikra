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
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden relative p-5 sm:p-8">
            <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />

            {{-- Logo & Heading --}}
            <div class="text-center relative z-10 mb-6">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="w-14 h-14 mx-auto mb-3 drop-shadow-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-sky-50 text-sky-800 border border-sky-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                    Layanan Mandiri Kader
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-2">Cek Status Pendaftaran</h1>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto font-normal">
                    Ketahui status verifikasi berkas pendaftaran kader Anda tanpa harus login terlebih dahulu.
                </p>
            </div>

            {{-- Search Form --}}
            <form action="{{ route('cek-status.check') }}" method="POST" class="space-y-4 relative z-10">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Masukkan NIK (16 Digit) atau Nomor WhatsApp
                    </label>
                    <div class="relative">
                        <input type="text" name="identifier" value="{{ old('identifier', request('identifier', $identifier ?? '')) }}"
                            required autofocus placeholder="Contoh: 331301... atau 08123456..."
                            class="w-full px-4 py-2.5 pl-11 bg-slate-50/60 border border-slate-200/90 rounded-lg text-sm font-medium text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    @error('identifier')
                        <p class="text-xs text-red-600 font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-xs rounded-lg shadow-2xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Periksa Status Berkas</span>
                </button>
            </form>

            {{-- Result Area --}}
            @if(isset($searched))
                <div class="mt-8 pt-6 border-t border-slate-100 relative z-10">
                    @if($anggota)
                        <div class="rounded-xl p-5 border {{ $anggota->user->status === 'aktif' ? 'bg-emerald-50/70 border-emerald-200/80' : ($anggota->user->status === 'ditolak' ? 'bg-red-50/70 border-red-200/80' : 'bg-amber-50/70 border-amber-200/80') }}">
                            <div class="flex items-start gap-3.5">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $anggota->user->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : ($anggota->user->status === 'ditolak' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    @if($anggota->user->status === 'aktif')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($anggota->user->status === 'ditolak')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold uppercase tracking-wider {{ $anggota->user->status === 'aktif' ? 'text-emerald-800' : ($anggota->user->status === 'ditolak' ? 'text-red-800' : 'text-amber-800') }}">
                                        Status Pendaftaran
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 mt-0.5 truncate">{{ $anggota->nama_lengkap }}</h3>
                                    <p class="text-xs text-slate-600 font-mono mt-0.5">
                                        NIK: {{ substr($anggota->nik, 0, 6) }}******{{ substr($anggota->nik, -4) }} &bull; Cabang: {{ $anggota->kecamatan }}
                                    </p>

                                    @if($anggota->user->status === 'aktif')
                                        <div class="mt-3 text-xs text-emerald-800 space-y-1">
                                            <p class="font-semibold">Alhamdulillah, berkas pendaftaran Anda telah DISETUJUI &amp; AKTIF.</p>
                                            <p class="text-emerald-700">Anda sudah dapat masuk ke Portal Kader untuk melihat penugasan kegiatan Baitul Arqam.</p>
                                        </div>
                                        <div class="mt-4">
                                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-2xs transition">
                                                <span>Masuk ke Portal Kader</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @elseif($anggota->user->status === 'ditolak')
                                        <div class="mt-3 text-xs text-red-800 space-y-1.5">
                                            <p class="font-semibold">Berkas pendaftaran Anda memerlukan perbaikan:</p>
                                            <div class="p-3 bg-white rounded-lg border border-red-200 text-red-900 italic font-medium">
                                                "{{ $anggota->catatan_verifikasi ?: 'Mohon periksa kembali kelengkapan biodata Anda.' }}"
                                            </div>
                                        </div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold text-xs shadow-2xs transition">
                                                <span>Masuk &amp; Perbaiki Berkas</span>
                                            </a>
                                            <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-xs shadow-2xs transition">
                                                <span>Bantuan Helpdesk WA</span>
                                            </a>
                                        </div>
                                    @else
                                        <div class="mt-3 text-xs text-amber-900 space-y-1">
                                            <p class="font-semibold">Berkas berhasil tersimpan dan sedang menunggu giliran verifikasi.</p>
                                            <p class="text-amber-800 font-normal">Admin verifikator MPKSDI sedang memeriksa kelengkapan data. Anda akan menerima pemberitahuan via WhatsApp setelah disetujui.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl p-5 bg-slate-50 border border-slate-200 text-center space-y-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-500 flex items-center justify-center mx-auto">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">Data Pendaftaran Tidak Ditemukan</h4>
                                <p class="text-xs text-slate-500 mt-0.5 font-normal">
                                    Tidak ditemukan berkas dengan NIK atau No WhatsApp "<strong>{{ $identifier }}</strong>".
                                </p>
                            </div>
                            <div class="pt-1 flex justify-center gap-2">
                                <a href="{{ route('anggota.register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs shadow-2xs transition">
                                    <span>Daftar Kader Baru Sekarang</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Footer Help Link --}}
            <div class="mt-8 pt-6 border-t border-slate-100 text-center relative z-10">
                <p class="text-xs text-slate-500 font-normal">
                    Mengalami kesulitan saat mengecek data? Hubungi
                    <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}" target="_blank" class="font-semibold text-emerald-600 hover:text-emerald-700 underline">
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
