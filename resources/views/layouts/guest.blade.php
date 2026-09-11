<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/webp" href="{{ asset('aset/logo-mpksdi.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-[#f8fafc] text-slate-800 min-h-screen selection:bg-sky-500 selection:text-white">
    {{-- Global Scroll Progress Indicator --}}
    <div id="scroll-progress-bar" class="fixed top-0 left-0 h-[3px] bg-gradient-to-r from-sky-500 via-cyan-400 to-[#f8bb00] z-[999] transition-[width] duration-75 ease-out pointer-events-none" style="width: 0%;"></div>
    @yield('body')
</body>
</html>
