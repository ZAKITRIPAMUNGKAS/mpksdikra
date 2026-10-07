@extends('errors.layout')

@php
    $loginRoute = route('login');
@endphp

@section('title', '419 - Sesi Kedaluwarsa')

@section('content')
<div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden text-center p-6 sm:p-10 relative">
    {{-- Glow background accent --}}
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-gradient-to-b from-amber-200/40 via-amber-100/20 to-transparent rounded-full blur-2xl pointer-events-none"></div>

    {{-- Status Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold uppercase tracking-wider mb-5">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span>Kode 419 &bull; Sesi Kedaluwarsa</span>
    </div>

    {{-- Main Icon --}}
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-gradient-to-br from-amber-500 to-[#002b40] p-0.5 shadow-md flex items-center justify-center mb-5">
        <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center text-amber-600">
            <svg class="w-9 h-9 sm:w-10 sm:h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    {{-- Headline & Subheading --}}
    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug mb-2">
        Sesi Halaman Telah Berakhir
    </h1>
    <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto mb-6">
        Halaman telah lama tidak aktif demi keamanan akun. Silakan muat ulang atau masuk kembali ke akun Anda.
    </p>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <button type="button" 
                onclick="window.location.reload();" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#002b40] hover:bg-[#003d5c] active:bg-[#001f30] text-white font-bold text-sm shadow-md transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Muat Ulang Halaman</span>
        </button>

        <a href="{{ $loginRoute }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold text-sm border border-slate-200 transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span>Masuk ke Akun</span>
        </a>
    </div>
</div>
@endsection
