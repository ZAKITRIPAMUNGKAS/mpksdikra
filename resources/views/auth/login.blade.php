@extends('layouts.guest')

@section('title', 'Masuk Portal — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen flex flex-col justify-between bg-[#f8fafc] text-slate-900 selection:bg-sky-500 selection:text-white">
    {{-- Top accent bar --}}
    <div class="h-1.5 bg-gradient-to-r from-sky-600 via-[#005c8a] to-[#f8bb00]"></div>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl border border-sky-100/80 overflow-hidden grid md:grid-cols-12 relative card-lift-sm">
            
            {{-- Left Side: Branding & Institutional Context --}}
            <div class="md:col-span-5 bg-gradient-to-br from-slate-950 via-[#003d5c] to-[#005c8a] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <x-batik-corner position="top-left" class="w-48 h-48 text-sky-400 opacity-[0.25]" />
                <x-batik-corner position="bottom-right" class="w-36 h-36 text-amber-400 opacity-[0.20]" />
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-3 group mb-8">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-12 w-auto drop-shadow-sm group-hover:scale-105 transition-transform duration-150">
                        <div>
                            <span class="font-extrabold text-white text-lg tracking-tight block">MPKSDI<span class="text-sky-400">.</span></span>
                            <span class="text-[10px] text-amber-300 font-bold uppercase tracking-wider">PDM Karanganyar</span>
                        </div>
                    </a>

                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] font-bold text-amber-300 uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-ping"></span>
                            Pangkalan Data Terintegrasi
                        </span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white leading-snug">
                            Portal Kader &amp; Perkaderan Baitul Arqam
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed pt-1">
                            Akses terpusat untuk pimpinan persyarikatan, instruktur, fasilitator, dan kader Muhammadiyah Kabupaten Karanganyar.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 pt-8 border-t border-white/10 mt-8 text-xs text-slate-300/80">
                    <p class="italic leading-relaxed">
                        &ldquo;Membentuk kader persyarikatan yang berintegritas, berakhlak mulia, dan berwawasan berkemajuan.&rdquo;
                    </p>
                </div>
            </div>

            {{-- Right Side: Login Form --}}
            <div class="md:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white relative">
                <div class="mb-6">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Masuk ke Portal</h1>
                        <a href="/" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-700 text-xs font-semibold transition border border-slate-200/80">
                            <span>Beranda</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500">Masukkan alamat email dan kata sandi akun Anda.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="leading-relaxed">{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@email.com"
                            class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200/90 rounded-xl focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 focus:bg-white transition outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi (Password)
                            </label>
                        </div>
                        <input id="password" name="password" type="password" required
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200/90 rounded-xl focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 focus:bg-white transition outline-none">
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 px-5 bg-sky-500 hover:bg-sky-600 active:scale-[0.99] text-white font-bold rounded-full shadow-xs transition duration-150 flex items-center justify-center gap-2 text-xs sm:text-sm">
                        <span>Masuk ke Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-600">
                        Belum memiliki akun kader?
                        <a href="{{ route('anggota.register') }}" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">
                            Daftar Anggota Baru &rarr;
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom simple footer --}}
    <footer class="py-4 px-4 max-w-4xl mx-auto w-full flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>&copy; {{ date('Y') }} MPKSDI PDM Karanganyar &bull; Baitul Arqam</span>
        </div>
        <a href="https://instagram.com/gemala.dev" target="_blank" rel="noopener noreferrer"
           class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 text-[11px] text-slate-600 hover:text-sky-700 transition">
            <img src="{{ asset('aset/logo-gemaladev.png') }}" alt="Logo gemala.dev" class="h-3.5 w-3.5 object-contain group-hover:scale-110 transition-transform">
            <span>Developed by <strong class="text-slate-700 group-hover:text-sky-700">gemala.dev</strong></span>
        </a>
    </footer>
</div>
@endsection
