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
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-300 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-gold-400"></span>
                    Admin Pengelola Perkaderan
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pusat Kendali MPKSDI Karanganyar</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">Pangkalan data kader Muhammadiyah, koordinasi Baitul Arqam, dan evaluasi hasil perkaderan terpadu.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white font-bold text-xs rounded-lg shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Event BA Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Total Kader</span>
                <span class="w-8 h-8 rounded-lg bg-mpksdi-50 text-mpksdi-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3 tabular-nums">{{ $totalAnggota }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Master data anggota terdata</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Menunggu Verifikasi</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold {{ $pendingAnggota > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-3 tabular-nums">{{ $pendingAnggota }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Pendaftar baru perlu disetujui</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Event BA Aktif</span>
                <span class="w-8 h-8 rounded-lg bg-gold-50 text-gold-700 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3 tabular-nums">{{ $activeEvents }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Persiapan &amp; berlangsung</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Total Event Terlaksana</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3 tabular-nums">{{ $totalEvents }}</p>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh periode perkaderan</p>
        </div>
    </div>

    {{-- Quick Action Cards --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('admin.anggota.index') }}" class="group bg-white rounded-xl border border-slate-200 p-6 shadow-xs hover:border-mpksdi-400 transition duration-150 flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-mpksdi-50 text-mpksdi-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-base group-hover:text-mpksdi-700 transition">Verifikasi &amp; Pangkalan Data Kader</h3>
                    <span class="text-slate-400 group-hover:text-mpksdi-600 group-hover:translate-x-1 transition-all">&rarr;</span>
                </div>
                <p class="text-xs text-slate-600 mt-1">Review pendaftaran kader baru, verifikasi berkas dan NIK, serta kelola database pengurus.</p>
                @if($pendingAnggota > 0)
                    <span class="inline-flex items-center gap-1 mt-3 px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold">
                        {{ $pendingAnggota }} kader baru perlu verifikasi
                    </span>
                @endif
            </div>
        </a>

        <a href="{{ route('admin.events.index') }}" class="group bg-white rounded-xl border border-slate-200 p-6 shadow-xs hover:border-mpksdi-400 transition duration-150 flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg bg-gold-50 text-gold-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-base group-hover:text-gold-700 transition">Manajemen Kegiatan Baitul Arqam</h3>
                    <span class="text-slate-400 group-hover:text-gold-600 group-hover:translate-x-1 transition-all">&rarr;</span>
                </div>
                <p class="text-xs text-slate-600 mt-1">Enroll peserta dari master data, atur jadwal sesi, monitoring scanner presensi QR, dan terbitkan sertifikat resmi.</p>
                <span class="inline-flex items-center gap-1 mt-3 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-xs font-medium">
                    {{ $activeEvents }} event sedang aktif
                </span>
            </div>
        </a>
    </div>
</div>
@endsection
