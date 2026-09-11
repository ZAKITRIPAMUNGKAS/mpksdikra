@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@php
    $totalAnggota = \App\Models\Anggota::count();
    $pendingAnggota = \App\Models\Anggota::whereHas('user', fn($q) => $q->where('status', 'menunggu_verifikasi'))->count();
    $activeEvents = \App\Models\Event::whereIn('status', ['persiapan', 'berlangsung'])->count();
    $totalEvents = \App\Models\Event::count();
@endphp

<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-44 h-44 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                    Admin Pengelola Perkaderan
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Pusat Kendali MPKSDI Karanganyar</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl font-medium">Pangkalan data kader Muhammadiyah, koordinasi Baitul Arqam, dan evaluasi hasil perkaderan terpadu.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-bold text-xs rounded-full shadow-xs transition hover:scale-[1.01]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Event BA Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Total Kader</span>
                <span class="w-10 h-10 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ $totalAnggota }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Master data anggota terdata</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-amber-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Menunggu Verifikasi</span>
                <span class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black {{ $pendingAnggota > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-3 tabular-nums">{{ $pendingAnggota }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Pendaftar baru perlu disetujui</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Event BA Aktif</span>
                <span class="w-10 h-10 rounded-2xl bg-sky-50 text-[#005c8a] flex items-center justify-center border border-sky-100 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ $activeEvents }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Persiapan &amp; berlangsung</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Total Event Terlaksana</span>
                <span class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-3 tabular-nums">{{ $totalEvents }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Seluruh periode perkaderan</p>
        </div>
    </div>

    {{-- Quick Action Cards --}}
    <div class="grid gap-5 sm:grid-cols-2">
        <a href="{{ route('admin.anggota.index') }}" class="group bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs hover:border-sky-400 transition card-lift-sm card-sheen flex items-start gap-5">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform border border-sky-100 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 text-base group-hover:text-sky-600 transition">Verifikasi &amp; Pangkalan Data Kader</h3>
                    <span class="text-slate-400 group-hover:text-sky-600 group-hover:translate-x-1 transition-all">&rarr;</span>
                </div>
                <p class="text-xs text-slate-600 mt-1 font-medium">Review pendaftaran kader baru, verifikasi berkas dan NIK, serta kelola database pengurus.</p>
                @if($pendingAnggota > 0)
                    <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                        {{ $pendingAnggota }} kader baru perlu verifikasi
                    </span>
                @endif
            </div>
        </a>

        <a href="{{ route('admin.events.index') }}" class="group bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs hover:border-sky-400 transition card-lift-sm card-sheen flex items-start gap-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform border border-amber-100 shadow-2xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-extrabold text-slate-900 text-base group-hover:text-sky-600 transition">Manajemen Kegiatan Baitul Arqam</h3>
                    <span class="text-slate-400 group-hover:text-sky-600 group-hover:translate-x-1 transition-all">&rarr;</span>
                </div>
                <p class="text-xs text-slate-600 mt-1 font-medium">Enroll peserta dari master data, atur jadwal sesi, monitoring scanner presensi QR, dan terbitkan sertifikat resmi.</p>
                <span class="inline-flex items-center gap-1.5 mt-3 px-3 py-1 rounded-full bg-sky-50 text-sky-900 border border-sky-200 text-xs font-bold shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#f8bb00] animate-pulse"></span>
                    {{ $activeEvents }} event sedang aktif
                </span>
            </div>
        </a>
    </div>
</div>
@endsection
