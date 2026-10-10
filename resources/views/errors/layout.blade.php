<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - MPKSDI Karanganyar</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/logo-mpksdi.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-b from-[#f8fafc] via-[#f1f5f9] to-[#e2e8f0] text-slate-800 min-h-screen flex flex-col justify-between selection:bg-[#002b40] selection:text-white">
    {{-- Global Page Transition & Smooth Preloader --}}
    <x-page-loader />

    {{-- Top Decorative Bar --}}
    <div class="h-1.5 w-full bg-gradient-to-r from-[#002b40] via-[#0869a7] to-[#f8bb00]"></div>

    {{-- Header Brand --}}
    <header class="w-full max-w-4xl mx-auto px-4 sm:px-6 pt-6 sm:pt-10 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3 group transition-transform active:scale-95">
            <div class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('aset/logo-muhammadiyah.png') }}" alt="Logo Muhammadiyah" class="h-8 sm:h-9 w-auto object-contain" onerror="this.style.display='none'">
                <div class="h-5 w-px bg-slate-300"></div>
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-8 sm:h-9 w-auto object-contain" onerror="this.src='{{ asset('aset/logo-mpksdi.png') }}'">
            </div>
            <div class="flex flex-col text-left">
                <span class="font-extrabold text-slate-900 text-sm sm:text-base tracking-tight leading-tight group-hover:text-[#0869a7] transition-colors">MPKSDI</span>
                <span class="text-[11px] text-slate-500 font-medium">PDM Kabupaten Karanganyar</span>
            </div>
        </a>

        @if(auth()->check())
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/80 border border-slate-200 text-xs font-semibold text-slate-600 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="truncate max-w-[180px]">{{ auth()->user()->name }}</span>
            </div>
        @endif
    </header>

    {{-- Main Error Content Container --}}
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="w-full max-w-xl mx-auto">
            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="w-full max-w-4xl mx-auto px-4 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} MPKSDI Pimpinan Daerah Muhammadiyah Karanganyar. Sistem Informasi Manajemen Perkaderan Baitul Arqam.</p>
    </footer>
</body>
</html>
