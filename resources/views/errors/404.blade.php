@extends('errors.layout')

@php
    $dashboardRoute = auth()->check()
        ? (auth()->user()->isAnggota() ? route('portal.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('admin.dashboard')))
        : url('/');
@endphp

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden text-center p-6 sm:p-10 relative">
    {{-- Glow background accent --}}
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-gradient-to-b from-sky-200/40 via-sky-100/20 to-transparent rounded-full blur-2xl pointer-events-none"></div>

    {{-- Status Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 border border-sky-200 text-sky-800 text-[11px] font-bold uppercase tracking-wider mb-5">
        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
        <span>Kode 404 &bull; Halaman Tidak Ditemukan</span>
    </div>

    {{-- Main Icon --}}
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-gradient-to-br from-sky-600 to-[#002b40] p-0.5 shadow-md flex items-center justify-center mb-5">
        <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center text-sky-600">
            <svg class="w-9 h-9 sm:w-10 sm:h-10 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    {{-- Headline & Subheading --}}
    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug mb-2">
        Halaman Tidak Ditemukan
    </h1>
    <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto mb-6">
        Tautan yang Anda tuju mungkin telah dipindahkan, dinonaktifkan, atau alamat URL yang dimasukkan kurang tepat.
    </p>

    {{-- Notification Callout Box --}}
    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-left flex items-start gap-3.5 mb-7 shadow-2xs">
        <div class="p-1.5 rounded-lg bg-slate-200 text-slate-700 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wide">Pemberitahuan Sistem</div>
            <div class="text-sm font-semibold text-slate-800 mt-0.5 leading-snug">
                {{ $exception->getMessage() ?: 'Halaman atau data yang Anda minta tidak tersedia di server.' }}
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <button type="button" 
                onclick="if(window.history.length > 1){ window.history.back(); } else { window.location.href='{{ $dashboardRoute }}'; }" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#002b40] hover:bg-[#003d5c] active:bg-[#001f30] text-white font-bold text-sm shadow-md transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Halaman Sebelumnya</span>
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
