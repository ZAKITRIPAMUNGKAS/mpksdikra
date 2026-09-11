<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) - MPKSDI Karanganyar</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/logo-mpksdi.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-slate-50 text-slate-800 min-h-full flex flex-col selection:bg-mpksdi-600 selection:text-white" x-data="{ mobileMenuOpen: false }">
    {{-- Institutional Top Strip with Gold Accent --}}
    <div class="h-1 bg-gradient-to-r from-mpksdi-800 via-mpksdi-600 to-gold-400"></div>

    {{-- Main Navbar --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                {{-- Logo & Brand --}}
                <div class="flex items-center gap-6">
                    <a href="{{ auth()->user()?->isAnggota() ? route('portal.dashboard') : (auth()->user()?->isExecutive() ? route('admin.executive') : route('admin.dashboard')) }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 w-auto group-hover:scale-105 transition-transform duration-150">
                        <div class="flex flex-col">
                            <span class="font-extrabold text-mpksdi-950 leading-none text-base tracking-tight group-hover:text-mpksdi-600 transition">MPKSDI</span>
                            <span class="text-[11px] text-slate-500 font-medium tracking-normal mt-0.5">PDM Karanganyar</span>
                        </div>
                    </a>

                    {{-- Desktop Navigation Links --}}
                    <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                        @if(auth()->user()?->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Dashboard</a>
                            <a href="{{ route('admin.events.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.events.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Event BA</a>
                            <a href="{{ route('admin.anggota.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.anggota.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Master Anggota</a>
                            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Pengguna &amp; Staf</a>
                        @endif

                        @if(auth()->user()?->isFasilitator())
                            <a href="{{ route('admin.events.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.events.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Tugas Event BA</a>
                        @endif

                        @if(auth()->user()?->isExecutive())
                            <a href="{{ route('admin.executive') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.executive') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Executive Overview</a>
                            <a href="{{ route('admin.events.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('admin.events.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Data Event</a>
                        @endif

                        @if(auth()->user()?->isAnggota())
                            <a href="{{ route('portal.dashboard') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('portal.dashboard') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Portal Saya</a>
                            <a href="{{ route('portal.tes.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('portal.tes.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Ujian & Tes</a>
                            <a href="{{ route('portal.profile.index') }}" class="px-3.5 py-1.5 rounded-lg transition {{ request()->routeIs('portal.profile.*') ? 'text-mpksdi-900 bg-mpksdi-50 font-bold border border-mpksdi-200/80' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">Profil Kader</a>
                        @endif
                    </nav>
                </div>

                {{-- User Profile, Muhammadiyah Crest & Actions --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('aset/logo-muhammadiyah.png') }}" alt="Logo Muhammadiyah" class="hidden lg:block h-8 w-auto opacity-80 mr-2">

                    @auth
                        {{-- Role Badge --}}
                        <div class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold tracking-wide
                            {{ auth()->user()->isAdmin() ? 'bg-mpksdi-100 text-mpksdi-900 border border-mpksdi-300' : '' }}
                            {{ auth()->user()->isFasilitator() ? 'bg-cyan-50 text-cyan-900 border border-cyan-200' : '' }}
                            {{ auth()->user()->isExecutive() ? 'bg-gold-100 text-gold-700 border border-gold-300' : '' }}
                            {{ auth()->user()->isAnggota() ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                            {{ strtoupper(auth()->user()->role) }}
                        </div>

                        {{-- User Name --}}
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-semibold text-slate-800 truncate max-w-[150px]">{{ auth()->user()->name }}</span>
                        </div>

                        {{-- Logout Button --}}
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Keluar dari sistem" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-red-700 hover:bg-red-50 border border-slate-200 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>

                        {{-- Mobile Menu Trigger Button --}}
                        <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                            </svg>
                        </button>
                    @endauth
                </div>
            </div>

            {{-- Mobile Dropdown Menu --}}
            <div x-show="mobileMenuOpen" x-transition class="md:hidden py-3 border-t border-slate-100 space-y-1" style="display: none;">
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Dashboard</a>
                    <a href="{{ route('admin.events.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Event BA</a>
                    <a href="{{ route('admin.anggota.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.anggota.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Master Anggota</a>
                    <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Pengguna &amp; Staf</a>
                @endif
                @if(auth()->user()?->isFasilitator())
                    <a href="{{ route('admin.events.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Tugas Event BA</a>
                @endif
                @if(auth()->user()?->isExecutive())
                    <a href="{{ route('admin.executive') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.executive') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Executive Overview</a>
                    <a href="{{ route('admin.events.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Data Event</a>
                @endif
                @if(auth()->user()?->isAnggota())
                    <a href="{{ route('portal.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('portal.dashboard') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Portal Saya</a>
                    <a href="{{ route('portal.tes.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('portal.tes.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Ujian & Tes</a>
                    <a href="{{ route('portal.profile.index') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('portal.profile.*') ? 'bg-mpksdi-50 text-mpksdi-900 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">Profil Kader</a>
                @endif
                <div class="pt-2 border-t border-slate-100 px-3">
                    <span class="text-xs text-slate-500 block">Masuk sebagai {{ auth()->user()?->name }} ({{ ucfirst(auth()->user()?->role) }})</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Contextual Sub-bar: Differentiation between Admin/Fasilitator vs Kader Portal --}}
    @auth
        <div class="border-b border-slate-200/90 {{ auth()->user()->isAnggota() ? 'bg-gradient-to-r from-mpksdi-950 via-mpksdi-900 to-mpksdi-800 text-white' : 'bg-slate-100/90 text-slate-700' }}">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2.5 text-xs">
                @if(auth()->user()->isAnggota())
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-gold-400 text-mpksdi-950 tracking-wider uppercase">
                            Portal Kader
                        </span>
                        <span class="text-slate-400 hidden sm:inline">&bull;</span>
                        <span class="text-slate-200 text-xs truncate max-w-[240px] sm:max-w-none">
                            Assalamu'alaikum, <strong class="text-white">{{ auth()->user()->name }}</strong>
                            @if(auth()->user()->anggota?->nbm)
                                <span class="text-gold-300 font-mono text-[11px] ml-1">(NBM: {{ auth()->user()->anggota->nbm }})</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-slate-300">
                        <span class="hidden md:inline text-[11px] text-slate-300">
                            Domisili Cabang: <strong class="text-white">{{ auth()->user()->anggota?->kecamatan ?: 'Karanganyar' }}</strong>
                        </span>
                        <a href="{{ route('portal.profile.index') }}" class="inline-flex items-center gap-1 text-gold-300 hover:text-gold-200 font-bold transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Biodata Lengkap</span>
                        </a>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold tracking-wider uppercase
                            {{ auth()->user()->isAdmin() ? 'bg-mpksdi-700 text-white' : '' }}
                            {{ auth()->user()->isFasilitator() ? 'bg-cyan-700 text-white' : '' }}
                            {{ auth()->user()->isExecutive() ? 'bg-amber-600 text-white' : '' }}">
                            {{ auth()->user()->isAdmin() ? 'Admin Workstation' : (auth()->user()->isFasilitator() ? 'Instruktur / Fasilitator' : 'Executive Desk') }}
                        </span>
                        <span class="text-slate-400 hidden sm:inline">&bull;</span>
                        <span class="text-slate-600 font-medium">
                            MPKSDI PDM Karanganyar
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-500 text-[11px]">
                        <span class="hidden sm:inline">Periode: <strong class="text-slate-700">{{ date('Y') }}</strong></span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-semibold border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Sistem Siap
                        </span>
                    </div>
                @endif
            </div>
        </div>
    @endauth

    {{-- Main Content Shell --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        {{-- Flash Messages with Crisp Borders --}}
        @if(session('success'))
            <div class="mb-6 px-4 py-3.5 rounded-lg bg-mpksdi-50 border-l-4 border-mpksdi-600 text-mpksdi-950 text-sm flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-mpksdi-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold">Berhasil:</span> {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 px-4 py-3.5 rounded-lg bg-red-50 border-l-4 border-red-600 text-red-950 text-sm flex items-start gap-3 shadow-2xs">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <span class="font-bold">Perhatian:</span> {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto border-t border-slate-200 bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI) PDM Karanganyar.</p>
            <div class="flex items-center gap-3">
                <a href="https://instagram.com/gemala.dev" target="_blank" rel="noopener noreferrer"
                   class="group inline-flex items-center gap-1.5 hover:text-sky-600 transition">
                    <img src="{{ asset('aset/logo-gemaladev.png') }}" alt="Logo gemala.dev" class="h-4 w-4 object-contain group-hover:scale-110 transition-transform">
                    <span class="text-[11px] text-slate-500">Developed by <span class="font-bold text-slate-700 group-hover:text-sky-600">gemala.dev</span></span>
                </a>
                <span class="text-slate-300">&bull;</span>
                <span class="text-slate-400">Berkemajuan &amp; Berintegritas</span>
            </div>
        </div>
    </footer>
</body>
</html>
