@extends('errors.layout')

@php
    $rawMsg = trim($exception->getMessage() ?? '');
    $isSertifikat = str_contains(strtolower($rawMsg), 'sertifikat');
    $isLulus = str_contains(strtolower($rawMsg), 'lulus');
    $displayMsg = !empty($rawMsg) ? $rawMsg : 'Akses ke laman atau dokumen ini dibatasi karena persyaratan tahapan kegiatan belum terpenuhi.';

    if ($isSertifikat) {
        $heading = 'Sertifikat Belum Dapat Diunduh';
        $subheading = 'Penerbitan dokumen sertifikat Baitul Arqam memerlukan penyelesaian seluruh agenda kegiatan serta penetapan hasil kelulusan resmi.';
    } elseif ($isLulus) {
        $heading = 'Status Kelulusan Belum Terpenuhi';
        $subheading = 'Akses dokumen atau fitur ini diperuntukkan bagi kader yang telah dinyatakan memenuhi kriteria kelulusan.';
    } else {
        $heading = 'Akses Belum Tersedia';
        $subheading = 'Anda belum memiliki izin atau persyaratan kegiatan belum lengkap untuk membuka dokumen atau tautan ini.';
    }

    $dashboardRoute = auth()->check()
        ? (auth()->user()->isAnggota() ? route('portal.dashboard') : (auth()->user()->isExecutive() ? route('admin.executive') : route('admin.dashboard')))
        : url('/');
@endphp

@section('title', '403 Akses Dibatasi - ' . $heading)

@section('content')
<div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-xl overflow-hidden text-center p-6 sm:p-10 relative">
    {{-- Glow background accent --}}
    <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-72 h-72 bg-gradient-to-b from-amber-200/40 via-amber-100/20 to-transparent rounded-full blur-2xl pointer-events-none"></div>

    {{-- Status Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-bold uppercase tracking-wider mb-5">
        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
        <span>Kode 403 &bull; Akses Dibatasi</span>
    </div>

    {{-- Main Icon --}}
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl bg-gradient-to-br from-amber-500 to-[#002b40] p-0.5 shadow-md flex items-center justify-center mb-5">
        <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center text-amber-600">
            @if($isSertifikat)
                {{-- Certificate Icon with Pending Clock --}}
                <svg class="w-9 h-9 sm:w-10 sm:h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9V5l4 4h-4z"/>
                </svg>
            @else
                {{-- Shield Lock Icon --}}
                <svg class="w-9 h-9 sm:w-10 sm:h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            @endif
        </div>
    </div>

    {{-- Headline & Subheading --}}
    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug mb-2">
        {{ $heading }}
    </h1>
    <p class="text-sm text-slate-600 leading-relaxed max-w-md mx-auto mb-6">
        {{ $subheading }}
    </p>

    {{-- Notification Callout Box --}}
    <div class="bg-amber-50/90 border border-amber-200/90 rounded-xl p-4 text-left flex items-start gap-3.5 mb-7 shadow-2xs">
        <div class="p-1.5 rounded-lg bg-amber-100 text-amber-800 shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wide">Keterangan Sistem</div>
            <div class="text-sm font-semibold text-amber-950 mt-0.5 leading-snug">
                {{ $displayMsg }}
            </div>
            @if($isSertifikat)
            <div class="text-xs text-amber-900/80 mt-1.5 leading-relaxed">
                Panitia akan membuka akses unduh sertifikat segera setelah status kegiatan diubah menjadi <strong>Selesai</strong> dan lembar pengesahan diterbitkan.
            </div>
            @endif
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

    {{-- Help Contact Footer --}}
    <div class="mt-8 pt-6 border-t border-slate-100 text-xs text-slate-500">
        Jika Anda meyakini ini adalah kekeliruan atau membutuhkan konfirmasi jadwal, silakan hubungi <span class="font-semibold text-slate-700">Fasilitator / Panitia Pelaksana Baitul Arqam</span>.
    </div>
</div>
@endsection
