@extends('layouts.guest')

@section('title', 'Masuk Portal — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen flex flex-col justify-between bg-[#f8fafc] text-slate-900 selection:bg-sky-500 selection:text-white">
    {{-- Top accent bar --}}
    <div class="h-1.5 bg-gradient-to-r from-sky-600 via-[#005c8a] to-[#f8bb00]"></div>

    <div class="flex-1 flex items-center justify-center p-3 sm:p-6 lg:p-8">
        <div class="w-full max-w-4xl bg-white rounded-xl shadow-xl border border-slate-200/90 overflow-hidden grid md:grid-cols-12 relative">
            
            {{-- Left Side: Branding & Institutional Context --}}
            <div class="md:col-span-5 bg-gradient-to-br from-slate-950 via-[#003d5c] to-[#005c8a] p-6 sm:p-8 md:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <x-batik-corner position="top-left" class="w-36 h-36 sm:w-48 sm:h-48 text-sky-400 opacity-[0.25]" />
                <x-batik-corner position="bottom-right" class="w-28 h-28 sm:w-36 sm:h-36 text-amber-400 opacity-[0.20]" />
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-2.5 sm:gap-3 group mb-4 sm:mb-6 md:mb-8">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 sm:h-12 w-auto drop-shadow-sm group-hover:scale-105 transition-transform duration-150">
                        <div>
                            <span class="font-bold text-white text-base sm:text-lg tracking-tight block leading-none">MPKSDI<span class="text-sky-400">.</span></span>
                            <span class="text-xs text-amber-300 font-semibold uppercase tracking-wider">PDM Karanganyar</span>
                        </div>
                    </a>

                    <div class="space-y-2 sm:space-y-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md bg-white/10 border border-white/15 text-xs font-semibold text-amber-300 uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-ping"></span>
                            Pangkalan Data Terintegrasi
                        </span>
                        <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-white leading-snug">
                            Portal Kader &amp; Perkaderan Baitul Arqam
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed pt-0.5 sm:pt-1 font-normal">
                            Akses terpusat untuk pimpinan persyarikatan, instruktur, fasilitator, dan kader Muhammadiyah Kabupaten Karanganyar.
                        </p>
                    </div>
                </div>

                {{-- Institutional quote (hidden on mobile to keep form readily accessible) --}}
                <div class="hidden md:block relative z-10 pt-6 md:pt-8 border-t border-white/10 mt-6 md:mt-8 text-xs text-slate-300/80">
                    <p class="italic leading-relaxed font-normal">
                        &ldquo;Membentuk kader persyarikatan yang berintegritas, berakhlak mulia, dan berwawasan berkemajuan.&rdquo;
                    </p>
                </div>
            </div>

            {{-- Right Side: Login Form --}}
            <div class="md:col-span-7 p-5 sm:p-8 md:p-10 lg:p-12 flex flex-col justify-center bg-white relative"
                 x-data="{ showPass: false, showForgotModal: false }">
                <div class="mb-5 sm:mb-6">
                    <div class="flex items-center justify-between gap-3 mb-1.5">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Masuk ke Portal</h1>
                        <a href="/" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs font-semibold transition border border-slate-200/80 shrink-0">
                            <span>Beranda</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 font-normal">Masuk dengan Nomor WhatsApp/HP dan kata sandi (tanggal &amp; tahun lahir) Anda.</p>
                </div>

                {{-- Flash Message Error / Session Expired --}}
                @if (session('error'))
                <div class="mb-5 p-3.5 sm:p-4 rounded-xl bg-amber-50 border border-amber-300 text-xs text-amber-900 space-y-1 shadow-2xs">
                    <div class="font-bold flex items-center gap-2 text-amber-950">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Pemberitahuan Sesi:</span>
                    </div>
                    <p class="text-xs text-amber-900 font-medium">{{ session('error') }}</p>
                </div>
                @endif

                {{-- Session Errors --}}
                @if ($errors->any())
                <div class="mb-5 p-3.5 sm:p-4 rounded-lg bg-red-50/90 border border-red-200 text-xs text-red-800 space-y-1 shadow-2xs">
                    <div class="font-semibold flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Gagal masuk ke akun:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 text-xs space-y-0.5 text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                    @csrf
                    <div>
                        <label for="login" class="block text-sm sm:text-base font-black text-slate-900 mb-1.5">
                            Nomor WhatsApp / HP <span class="text-xs sm:text-sm font-bold text-slate-500">(Username)</span>
                        </label>
                        <input id="login" type="text" name="login" value="{{ old('login', old('email')) }}" required autofocus
                            placeholder="Contoh: 081234567890 (atau email)"
                            autocomplete="username tel"
                            class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 bg-white transition outline-none font-medium min-h-[52px] text-slate-900 placeholder:text-slate-400">
                        <span class="text-xs sm:text-sm text-slate-600 mt-1.5 block font-medium">Kader masuk menggunakan nomor WhatsApp/HP. Admin/Fasilitator dapat menggunakan email.</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm sm:text-base font-black text-slate-900">
                                Kata Sandi <span class="text-xs sm:text-sm font-bold text-slate-500">(Tgl Lahir)</span>
                            </label>
                            <button type="button" @click="showForgotModal = true" class="text-xs sm:text-sm font-bold text-sky-700 hover:text-sky-900 underline">
                                Bantuan Sandi?
                            </button>
                        </div>
                        <div class="relative">
                            <input id="password" name="password" :type="showPass ? 'text' : 'password'" required
                                placeholder="Format: DDMMYYYY (contoh: 17081985)"
                                class="w-full px-4 py-3.5 pr-12 text-base font-mono border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 bg-white transition outline-none font-semibold min-h-[52px] text-slate-900 placeholder:text-slate-400">
                            <button type="button" @click="showPass = !showPass" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800 focus:outline-none p-2 rounded-xl" title="Tampilkan/Sembunyikan Sandi">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                        <div class="mt-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs sm:text-sm text-amber-950 font-medium">
                            💡 Ketik 8 angka tanggal lahir Anda: <strong>DDMMYYYY</strong>.<br class="hidden sm:inline"> Contoh jika lahir <strong>17 Agustus 1985</strong>, ketik <strong class="text-amber-900 font-black">17081985</strong>.
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-700 cursor-pointer select-none font-semibold">
                            <input type="checkbox" name="remember" value="1" class="w-5 h-5 rounded-lg border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>Ingat saya di perangkat HP ini</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full min-h-[54px] sm:min-h-[56px] px-6 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black rounded-2xl shadow-md transition flex items-center justify-center gap-2.5 text-base sm:text-lg">
                        <span>Masuk ke Portal</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-6 sm:mt-8 pt-4 sm:pt-5 border-t border-slate-200 text-center">
                    <p class="text-sm sm:text-base text-slate-700 font-medium">
                        Belum memiliki akun kader?
                        <a href="{{ route('anggota.register') }}" class="font-extrabold text-sky-700 hover:text-sky-900 underline block sm:inline mt-1 sm:mt-0">
                            Daftar Anggota Baru &rarr;
                        </a>
                    </p>
                </div>

                {{-- Modal Bantuan Lupa Sandi --}}
                <div x-show="showForgotModal" @keydown.escape.window="showForgotModal = false" role="dialog" aria-modal="true" aria-labelledby="forgot-title" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showForgotModal = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-7 border-2 border-slate-200 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center mx-auto mb-4 text-xl">
                            <span aria-hidden="true">🔑</span>
                        </div>
                        <h3 id="forgot-title" class="text-lg sm:text-xl font-black text-slate-900 mb-1 tracking-tight">Panduan Masuk &amp; Kata Sandi Akun</h3>
                        <p class="text-xs sm:text-sm text-slate-600 mb-4 leading-relaxed">
                            Untuk memudahkan seluruh kader Muhammadiyah (termasuk pimpinan senior), akses masuk ke portal menggunakan:
                        </p>
                        <div class="bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-left text-xs sm:text-sm space-y-2 mb-5">
                            <div>👤 <strong class="text-slate-900">Username:</strong> Nomor WhatsApp / HP aktif yang Anda daftarkan</div>
                            <div>🔑 <strong class="text-slate-900">Kata Sandi:</strong> Tanggal &amp; tahun lahir Anda (8 digit: <code>DDMMYYYY</code>, contoh lahir 17 Agustus 1985 ketik <code>17081985</code>)</div>
                        </div>
                        
                        <div class="space-y-3">
                            <a href="{{ \App\Services\WhatsAppNotificationService::urlBantuanLupaSandi() }}"
                               target="_blank" rel="noopener noreferrer"
                               class="w-full min-h-[50px] px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold rounded-2xl shadow-sm transition flex items-center justify-center gap-2 text-sm sm:text-base">
                                <span>Chat Helpdesk via WhatsApp</span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('cek-status') }}"
                               class="w-full min-h-[48px] px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-2xl border border-slate-300 text-xs sm:text-sm transition flex items-center justify-center gap-1.5">
                                <span>Cek Status Verifikasi Pendaftaran</span>
                            </a>
                            <button type="button" @click="showForgotModal = false"
                                class="w-full py-2.5 text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-800 transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom simple footer --}}
    <footer class="py-4 px-4 max-w-4xl mx-auto w-full flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 text-center sm:text-left">
        <div class="flex items-center justify-center sm:justify-start gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>&copy; {{ date('Y') }} MPKSDI PDM Karanganyar &bull; Baitul Arqam</span>
        </div>
        <a href="https://instagram.com/gemala.dev" target="_blank" rel="noopener noreferrer"
           class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 text-xs text-slate-600 hover:text-sky-700 transition">
            <img src="{{ asset('aset/logo-gemaladev.png') }}" alt="Logo gemala.dev" class="h-3.5 w-3.5 object-contain group-hover:scale-110 transition-transform">
            <span>Developed by <strong class="text-slate-700 group-hover:text-sky-700">gemala.dev</strong></span>
        </a>
    </footer>
</div>
@endsection
