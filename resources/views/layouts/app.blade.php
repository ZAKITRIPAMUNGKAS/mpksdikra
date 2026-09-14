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
<body class="antialiased bg-[#f8fafc] text-slate-800 min-h-full flex selection:bg-sky-500 selection:text-white"
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
          toggleCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
          }
      }">
    {{-- Global Scroll Progress Indicator --}}
    <div id="scroll-progress-bar" class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-sky-500 via-cyan-400 to-[#f8bb00] z-[999] transition-[width] duration-75 ease-out pointer-events-none" style="width: 0%;"></div>

    {{-- Mobile Off-Canvas Backdrop --}}
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 lg:hidden" 
         style="display: none;"></div>

    {{-- SIDEBAR CONTAINER (FIXED & CLEAN MINIMALIST) --}}
    <aside :class="{
               'translate-x-0': sidebarOpen,
               '-translate-x-full': !sidebarOpen,
               'w-64 lg:w-64': !sidebarCollapsed,
               'w-64 lg:w-20': sidebarCollapsed
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white text-slate-700 transition-all duration-300 ease-in-out border-r border-slate-200/90 shadow-sm lg:translate-x-0 select-none h-screen">
        
        {{-- Sidebar Brand / Logos --}}
        <div class="h-16 flex items-center px-4 border-b border-slate-200/80 justify-between bg-white">
            <a href="{{ auth()->user()?->isAnggota() ? route('portal.dashboard') : (auth()->user()?->isExecutive() ? route('admin.executive') : route('admin.dashboard')) }}" 
               class="flex items-center gap-3 overflow-hidden group">
                <div class="flex items-center gap-2 shrink-0">
                    <img src="{{ asset('aset/logo-muhammadiyah.png') }}" alt="Logo Muhammadiyah" class="h-7 w-auto object-contain">
                    <div class="h-4 w-px bg-slate-200"></div>
                    <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-7 w-auto object-contain">
                </div>
                <div class="flex flex-col min-w-0 transition-opacity duration-200" :class="{ 'lg:hidden': sidebarCollapsed }">
                    <span class="font-bold text-slate-900 text-sm tracking-tight leading-tight">MPKSDI</span>
                    <span class="text-xs text-slate-500 font-medium truncate">
                        {{ auth()->user()?->isAdminCabang() ? ('PCM ' . auth()->user()->pcm) : 'PDM Karanganyar' }}
                    </span>
                </div>
            </a>

            {{-- Close button for mobile drawer --}}
            <button type="button" @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition" aria-label="Tutup Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Sidebar Navigation List --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4 text-xs font-semibold">
            @auth
                {{-- Menu Utama --}}
                <div class="space-y-1">
                    <div class="px-3 pt-1 pb-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider" :class="{ 'lg:hidden': sidebarCollapsed }">
                        {{ auth()->user()->isAdminCabang() ? 'Menu Cabang' : 'Menu Utama' }}
                    </div>

                    @if(auth()->user()->isAdminDaerah())
                        {{-- Admin Daerah: Dashboard --}}
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.dashboard') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Dashboard Utama">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Dashboard</span>
                        </a>

                        {{-- Admin Daerah: Event BA --}}
                        <a href="{{ route('admin.events.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.events.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Event Baitul Arqam">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.events.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Event BA</span>
                        </a>

                        {{-- Admin Daerah: Master Anggota --}}
                        <a href="{{ route('admin.anggota.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.anggota.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Pangkalan Data Kader">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.anggota.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Master Anggota</span>
                        </a>

                        {{-- Admin Daerah: Pengguna & Staf --}}
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.users.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Pengguna & Staf">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Pengguna &amp; Staf</span>
                        </a>
                    @endif

                    @if(auth()->user()->isAdminCabang())
                        {{-- Admin Cabang: Dashboard Cabang --}}
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.dashboard') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Dashboard Cabang {{ auth()->user()->pcm }}">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Dashboard Cabang</span>
                        </a>

                        {{-- Admin Cabang: Data Anggota Cabang --}}
                        <a href="{{ route('admin.anggota.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.anggota.*') && !request()->routeIs('admin.anggota.export') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Data Kader PCM {{ auth()->user()->pcm }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.anggota.*') && !request()->routeIs('admin.anggota.export') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Data Anggota Cabang</span>
                            </div>
                            @php
                                $cabangPendingCount = \App\Models\User::where('role', 'anggota')
                                    ->where('status', 'menunggu_verifikasi')
                                    ->whereHas('anggota', fn($q) => $q->where('pcm', auth()->user()->pcm)->orWhere('kecamatan', auth()->user()->pcm))
                                    ->count();
                            @endphp
                            @if($cabangPendingCount > 0)
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white shrink-0" :class="{ 'lg:hidden': sidebarCollapsed }">
                                    {{ $cabangPendingCount }}
                                </span>
                            @endif
                        </a>

                        {{-- Admin Cabang: Unduh Data Kader Excel --}}
                        <a href="{{ route('admin.anggota.export') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group text-slate-600 hover:text-emerald-700 hover:bg-emerald-50/80"
                           title="Unduh Data Kader PCM {{ auth()->user()->pcm }} (Excel)">
                            <svg class="w-5 h-5 shrink-0 text-emerald-600 group-hover:text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Unduh Data (Excel)</span>
                        </a>
                    @endif

                    @if(auth()->user()->isFasilitator())
                        <a href="{{ route('admin.events.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.events.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Tugas Event Baitul Arqam">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.events.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Tugas Event BA</span>
                        </a>
                    @endif

                    @if(auth()->user()->isExecutive())
                        <a href="{{ route('admin.executive') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.executive') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Executive Overview">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.executive') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Executive Overview</span>
                        </a>

                        <a href="{{ route('admin.events.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('admin.events.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Data Event">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.events.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Data Event</span>
                        </a>
                    @endif

                    @if(auth()->user()->isAnggota())
                        <a href="{{ route('portal.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('portal.dashboard') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Portal Saya">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('portal.dashboard') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Portal Saya</span>
                        </a>

                        <a href="{{ route('portal.tes.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('portal.tes.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Ujian & Tes Kognitif">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('portal.tes.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Ujian &amp; Tes</span>
                        </a>

                        <a href="{{ route('portal.profile.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('portal.profile.*') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Biodata & Profil Kader">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('portal.profile.*') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Profil Kader</span>
                        </a>
                    @endif
                </div>

                {{-- Bantuan & Panduan Sistem --}}
                <div class="pt-3 border-t border-slate-200/80 space-y-1">
                    <div class="px-3 pt-1 pb-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider" :class="{ 'lg:hidden': sidebarCollapsed }">
                        Bantuan &amp; Panduan
                    </div>
                    <a href="{{ route('panduan') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('panduan') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                       title="Panduan & Tutorial Sistem">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('panduan') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Panduan Sistem</span>
                    </a>
                    <a href="{{ route('updates') }}" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition duration-150 group {{ request()->routeIs('updates') ? 'bg-sky-50 text-[#005c8a] shadow-2xs border border-sky-200/70' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                       title="Pembaruan Sistem & Changelog">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('updates') ? 'text-[#005c8a]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Update Sistem</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20 shrink-0" :class="{ 'lg:hidden': sidebarCollapsed }">v2.2</span>
                    </a>
                </div>

                {{-- External / Public Portal Links --}}
                <div class="pt-3 border-t border-slate-200/80 space-y-1">
                    <div class="px-3 pt-1 pb-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider" :class="{ 'lg:hidden': sidebarCollapsed }">
                        Akses Publik
                    </div>
                    <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 transition duration-150 group" title="Buka Halaman Depan">
                        <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Landing Page MPKSDI</span>
                    </a>
                    <a href="{{ route('cek-status') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 transition duration-150 group" title="Cek Status Pendaftaran">
                        <svg class="w-5 h-5 shrink-0 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'lg:hidden': sidebarCollapsed }">Cek Status Publik</span>
                    </a>
                </div>
            @endauth
        </nav>

        {{-- Sidebar Footer: User Details & Logout Action --}}
        @auth
        <div class="p-3 border-t border-slate-200/80 bg-slate-50/80">
            <div class="flex items-center justify-between gap-2.5">
                <div class="flex items-center gap-2.5 min-w-0 overflow-hidden">
                    @if(auth()->user()->anggota?->foto)
                        <img src="{{ route('foto.anggota', auth()->user()->anggota) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover border border-sky-400/80 shadow-2xs shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-[#005c8a] flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-2xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0 transition-opacity duration-200" :class="{ 'lg:hidden': sidebarCollapsed }">
                        <p class="text-xs font-semibold text-slate-900 truncate leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">
                            {{ auth()->user()->isAdminCabang() ? ('Admin PCM ' . auth()->user()->pcm) : (auth()->user()->isAdminDaerah() ? 'Admin Daerah PDM' : ucfirst(str_replace('_', ' ', auth()->user()->role))) }}
                        </p>
                    </div>
                </div>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="inline shrink-0">
                    @csrf
                    <button type="submit" title="Keluar dari sistem" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    {{-- MAIN CONTENT WRAPPER --}}
    <div :class="{
             'lg:pl-64': !sidebarCollapsed,
             'lg:pl-20': sidebarCollapsed
         }"
         class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50/60 bg-batik-subtle transition-all duration-300 ease-in-out">
        
        {{-- Top Application Header Bar --}}
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 gap-4">
                    
                    {{-- Left Action: Toggle Buttons & Workspace Indicator --}}
                    <div class="flex items-center gap-3 sm:gap-4">
                        {{-- Mobile Drawer Trigger --}}
                        <button type="button" @click="sidebarOpen = true" 
                                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition focus:outline-none" 
                                aria-label="Buka Navigasi">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Desktop Sidebar Collapse Toggle --}}
                        <button type="button" @click="toggleCollapse()" 
                                class="hidden lg:flex p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition focus:outline-none" 
                                title="Buka/Tutup Lebar Sidebar">
                            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>

                        <div class="h-6 w-px bg-slate-200 hidden lg:block"></div>

                        {{-- Current Contextual Label --}}
                        @auth
                            <div class="flex items-center gap-2 text-xs sm:text-sm">
                                <span class="text-slate-500 font-medium">
                                    {{ auth()->user()->isAdminCabang() ? ('PCM ' . auth()->user()->pcm) : 'MPKSDI PDM Karanganyar' }}
                                </span>
                                <span class="text-slate-300">/</span>
                                <span class="font-bold text-slate-800">
                                    {{ auth()->user()->isAnggota() ? 'Portal Kader' : (auth()->user()->isExecutive() ? 'Executive Desk' : (auth()->user()->isFasilitator() ? 'Panel Fasilitator' : (auth()->user()->isAdminCabang() ? 'Panel Admin Cabang' : 'Panel Administrasi'))) }}
                                </span>
                            </div>
                        @endauth
                    </div>

                    {{-- Right Action: Date & Profile --}}
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="hidden md:flex items-center text-xs text-slate-500 font-medium">
                            <span>{{ date('d M Y') }}</span>
                        </div>

                        @auth
                            <div class="flex items-center gap-2.5 pl-3 border-l border-slate-200">
                                @if(auth()->user()->anggota?->foto)
                                    <img src="{{ route('foto.anggota', auth()->user()->anggota) }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover border border-sky-400/80 shadow-2xs shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-lg bg-[#005c8a] flex items-center justify-center text-white font-bold text-xs shadow-2xs shrink-0">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="hidden sm:block text-left leading-tight">
                                    <span class="text-xs font-bold text-slate-800 block truncate max-w-[140px]">{{ auth()->user()->name }}</span>
                                    <span class="text-xs text-slate-500 block truncate max-w-[140px]">
                                        {{ auth()->user()->isAdminCabang() ? ('Admin PCM ' . auth()->user()->pcm) : (auth()->user()->isAdminDaerah() ? 'Admin Daerah' : ucfirst(str_replace('_', ' ', auth()->user()->role))) }}
                                    </span>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        {{-- Main Page Content Shell --}}
        <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-6 sm:py-8 max-w-7xl mx-auto">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-sky-50 border border-sky-200 text-sky-950 text-sm flex items-start gap-3 shadow-2xs">
                    <svg class="w-5 h-5 text-sky-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-bold text-sky-900">Berhasil:</span> {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-950 text-sm flex items-start gap-3 shadow-2xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-bold text-rose-900">Perhatian:</span> {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Standardized Footer --}}
        <footer class="mt-auto border-t border-slate-200/80 bg-white/80 backdrop-blur-xs py-5">
            <div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-2 flex-wrap justify-center sm:justify-start">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <p>&copy; {{ date('Y') }} MPKSDI PDM Karanganyar &bull; Sistem Perkaderan Baitul Arqam</p>
                    <span class="hidden md:inline text-slate-300">|</span>
                    <div class="flex items-center gap-2 text-xs">
                        <a href="{{ route('panduan') }}" class="hover:text-[#005c8a] font-medium transition">Panduan Sistem</a>
                        <span class="text-slate-300">&bull;</span>
                        <a href="{{ route('updates') }}" class="hover:text-[#005c8a] font-medium transition">Pembaruan v2.2</a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="https://instagram.com/gemala.dev" target="_blank" rel="noopener noreferrer"
                       class="group inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 hover:bg-sky-50 border border-slate-200/80 hover:border-sky-300 text-xs text-slate-600 hover:text-sky-700 transition">
                        <img src="{{ asset('aset/logo-gemaladev.png') }}" alt="Logo gemala.dev" class="h-3.5 w-3.5 object-contain group-hover:scale-110 transition-transform">
                        <span>Developed by <strong class="text-slate-700 group-hover:text-sky-700">gemala.dev</strong></span>
                    </a>
                    <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="px-2.5 py-1 rounded-full text-slate-400 hover:text-sky-600 hover:bg-slate-100 text-xs font-semibold transition">
                        Ke Atas &uarr;
                    </button>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
