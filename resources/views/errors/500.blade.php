@extends('errors.layout')

@php
    $dashboardRoute = auth()->check()
        ? (auth()->user()->isAnggota() ? route('portal.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('admin.dashboard')))
        : url('/');
@endphp

@section('title', '500 - Terjadi Kendala Server')

@section('content')
<div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden text-center p-6 sm:p-10 relative">
    {{-- Glow background accent --}}
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-gradient-to-b from-rose-200/40 via-rose-100/20 to-transparent rounded-full blur-2xl pointer-events-none"></div>

    {{-- Status Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-bold uppercase tracking-wider mb-5">
        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
        <span>Kode 500 &bull; Kendala Sistem</span>
    </div>

    {{-- Main Icon --}}
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-gradient-to-br from-rose-600 to-[#002b40] p-0.5 shadow-md flex items-center justify-center mb-5">
        <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center text-rose-600">
            <svg class="w-9 h-9 sm:w-10 sm:h-10 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
    </div>

    {{-- Headline & Subheading --}}
    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug mb-2">
        Terjadi Kendala pada Server
    </h1>
    <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto mb-6">
        Sistem sedang mengalami hambatan sementara saat memproses permintaan Anda. Tim administrator telah menerima laporan ini.
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

        <a href="{{ $dashboardRoute }}" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 font-semibold text-sm border border-slate-200 transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Buka Dashboard</span>
        </a>
    </div>
</div>
@endsection
