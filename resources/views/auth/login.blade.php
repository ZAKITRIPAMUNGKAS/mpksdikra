@extends('layouts.guest')

@section('title', 'Masuk Portal — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen flex flex-col justify-between bg-slate-100 text-slate-900 selection:bg-mpksdi-600 selection:text-white">
    {{-- Top accent bar --}}
    <div class="h-1.5 bg-gradient-to-r from-mpksdi-900 via-mpksdi-600 to-gold-400"></div>

    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden grid md:grid-cols-12">
            
            {{-- Left Side: Branding & Institutional Context --}}
            <div class="md:col-span-5 bg-gradient-to-br from-mpksdi-950 via-mpksdi-900 to-mpksdi-900 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-3 group mb-8">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-12 w-auto drop-shadow-sm group-hover:scale-105 transition-transform duration-150">
                        <div>
                            <span class="font-extrabold text-white text-base tracking-tight block">MPKSDI</span>
                            <span class="text-xs text-gold-300 font-semibold">PDM Karanganyar</span>
                        </div>
                    </a>

                    <div class="space-y-3">
                        <span class="inline-block px-2.5 py-1 rounded-md bg-white/10 border border-white/15 text-[11px] font-bold text-gold-300 uppercase tracking-wider">
                            Sistem Informasi Terpadu
                        </span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white leading-snug">
                            Pangkalan Data Kader &amp; Perkaderan Baitul Arqam
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed pt-1">
                            Akses terpusat untuk pimpinan persyarikatan, instruktur, fasilitator, dan kader Muhammadiyah Kabupaten Karanganyar.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 pt-8 border-t border-white/10 mt-8 text-xs text-slate-400">
                    <p class="italic leading-relaxed">
                        &ldquo;Membentuk kader persyarikatan yang berintegritas, berakhlak mulia, dan berwawasan berkemajuan.&rdquo;
                    </p>
                </div>
            </div>

            {{-- Right Side: Login Form --}}
            <div class="md:col-span-7 p-8 sm:p-12 flex flex-col justify-center bg-white">
                <div class="mb-6">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Masuk ke Portal</h1>
                        <a href="/" class="text-xs font-semibold text-slate-400 hover:text-mpksdi-700 transition">Beranda &rarr;</a>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500">Masukkan alamat email dan kata sandi akun Anda.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 focus:bg-white transition outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi (Password)
                            </label>
                        </div>
                        <input id="password" name="password" type="password" required
                            placeholder="••••••••"
                            class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-300 rounded-lg focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 focus:bg-white transition outline-none">
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-600">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold rounded-lg shadow-xs transition duration-150 flex items-center justify-center gap-2 text-xs sm:text-sm">
                        <span>Masuk ke Akun</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 pt-5 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-600">
                        Belum memiliki akun kader?
                        <a href="{{ route('anggota.register') }}" class="font-bold text-mpksdi-700 hover:underline">
                            Daftar Anggota Baru
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom simple footer --}}
    <footer class="py-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} MPKSDI PDM Karanganyar. Sistem Informasi Pengkaderan Baitul Arqam.
    </footer>
</div>
@endsection
