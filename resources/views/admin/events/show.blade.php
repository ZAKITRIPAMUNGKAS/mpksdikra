@extends('layouts.app')

@section('title', 'Dashboard Pelatihan — ' . $event->nama_event)

@section('content')
<div class="space-y-3.5 sm:space-y-4" x-data="{ 
    activeTab: '{{ in_array(request('tab'), ['evaluasi', 'cbt']) ? 'cbt' : (in_array(request('tab'), ['unduh_data', 'laporan']) ? 'laporan' : (in_array(request('tab'), ['angket', 'feedback']) ? 'feedback' : request('tab', 'peserta'))) }}', 
    subTabCbt: '{{ request('sub', 'tes') }}', 
    subTabOutbound: 'regu', 
    subTabFeedback: '{{ request('sub_feedback', 'rekap') }}',
    subTabLaporan: 'rekap', 
    modalSesi: false, 
    editSesi: null, 
    searchPeserta: '' 
}">

    {{-- 1. TOP BAR & BREADCRUMB COMPACT --}}
    <div class="flex items-center justify-between flex-wrap gap-2">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-sky-600 transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Kegiatan</span>
        </a>

        @if($canManage)
        <div class="flex items-center gap-1.5 flex-wrap">
            <form method="POST" action="{{ route('admin.events.status', $event) }}" class="inline-block">
                @csrf
                <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 bg-white border border-slate-200/90 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer transition shadow-2xs">
                    @foreach(['persiapan','berlangsung','selesai'] as $s)
                    <option value="{{ $s }}" @selected($event->status === $s)>Status: {{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.events.edit', $event) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">Edit Info</a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Hapus kegiatan ini secara permanen?')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1 rounded-lg border border-red-200 text-red-700 bg-white hover:bg-red-50 text-xs font-semibold transition shadow-2xs">Hapus</button>
            </form>
        </div>
        @endif
    </div>

    {{-- 2. HERO CARD COMPACT (PUSAT INFORMASI KEGIATAN) --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.16]" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-3.5">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold uppercase tracking-wider
                        {{ $event->status === 'persiapan' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                        {{ $event->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                        {{ $event->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $event->status === 'berlangsung' ? 'bg-sky-500 animate-pulse' : ($event->status === 'persiapan' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                        {{ ucfirst($event->status) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-semibold">
                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $event->tanggal_mulai?->format('d M Y') }} — {{ $event->tanggal_selesai?->format('d M Y') }}</span>
                    </span>
                    @if($event->lokasi)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-medium">
                        <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ $event->lokasi }}</span>
                    </span>
                    @endif
                </div>

                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight leading-snug">{{ $event->nama_event }}</h1>
                <p class="text-xs text-slate-500 max-w-2xl font-normal line-clamp-1">
                    Pusat Komando &amp; Monitoring Pelatihan Baitul Arqam &bull; Pimpinan Daerah Muhammadiyah Karanganyar
                </p>
            </div>

            {{-- Tombol Tindakan Cepat Ramping --}}
            <div class="flex items-center gap-2 shrink-0">
                @if($totalPeserta > 0)
                <a href="{{ route('admin.events.idcards.batch', $event) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-900 border border-sky-200 hover:bg-sky-100 text-xs font-semibold shadow-2xs transition">
                    <svg class="w-3.5 h-3.5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak ID Card Massal (A4)</span>
                </a>
                @endif
                @if($canManage)
                <a href="{{ route('admin.events.enroll', $event) }}" class="inline-flex items-center justify-center gap-1 px-3.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold shadow-2xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>+ Enroll Peserta</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. LIVE TRAINING METRICS STRIP COMPACT (4 KPIS) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
        {{-- Card 1: Total Peserta & Demografi --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-3 sm:p-3.5 shadow-2xs">
            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                <span class="font-semibold uppercase tracking-wider">Kader Peserta</span>
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalPeserta }}</span>
                <span class="text-xs text-slate-500">/ {{ $event->kuota ?: '∞' }} Kuota</span>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>L: <b class="text-slate-800">{{ $totalLaki }}</b> &bull; P: <b class="text-slate-800">{{ $totalPerempuan }}</b></span>
                <span class="text-sky-700 font-semibold">{{ $pcmCounts->count() }} Cabang</span>
            </div>
        </div>

        {{-- Card 2: Progres Sesi & Agenda --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-3 sm:p-3.5 shadow-2xs">
            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                <span class="font-semibold uppercase tracking-wider">Jadwal Sesi</span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalSesi }}</span>
                <span class="text-xs text-slate-500">Sesi Agenda</span>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Materi: <b>{{ $sesi->where('jenis', 'materi')->count() }}</b></span>
                <span>Sholat: <b>{{ $sesi->where('jenis', 'sholat')->count() }}</b></span>
                <span>Outbound: <b>{{ $sesi->where('jenis', 'outbound')->count() }}</b></span>
            </div>
        </div>

        {{-- Card 3: Evaluasi Kognitif (Pre/Post) --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-3 sm:p-3.5 shadow-2xs">
            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                <span class="font-semibold uppercase tracking-wider">Tes Kognitif</span>
                <span class="w-1.5 h-1.5 rounded-full {{ ($sesiPretest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'aktif') ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-slate-900">
                    {{ $posttestSelesaiCount }}/{{ $totalPeserta }}
                </span>
                <span class="text-xs text-slate-500">Posttest</span>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Pretest: <b>{{ $pretestSelesaiCount }}</b></span>
                <span class="text-[11px] {{ ($sesiPosttest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'aktif') ? 'text-amber-700 font-bold' : 'text-slate-500' }}">
                    {{ ($sesiPosttest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'aktif') ? 'Posttest Aktif' : 'Posttest Tertutup' }}
                </span>
            </div>
        </div>

        {{-- Card 4: RTL & Rata-rata Nilai --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-3 sm:p-3.5 shadow-2xs">
            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                <span class="font-semibold uppercase tracking-wider">Rerata Nilai &amp; Kelulusan</span>
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-xl sm:text-2xl font-black text-slate-900">{{ number_format($rerataNilai, 1) }}</span>
                <span class="text-xs text-slate-500">/ 100</span>
            </div>
            <div class="mt-1.5 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600">
                <span>Lulus: <b class="text-emerald-700">{{ $totalLulus }}</b></span>
                <span>Bersyarat: <b class="text-amber-700">{{ $totalBersyarat }}</b></span>
                <span>RTL: <b>{{ $rtlSelesaiCount }}</b></span>
            </div>
        </div>
    </div>

    {{-- WORKSPACE-STYLE HORIZONTAL MODULE NAVIGATION --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        {{-- LEVEL 1: GLOBAL MODULE NAVIGATION (STICKY) --}}
        <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 sm:px-6 flex items-center gap-6 sm:gap-8 overflow-x-auto whitespace-nowrap scrollbar-none shadow-xs">
            {{-- 1. Peserta --}}
            <button type="button" @click="activeTab = 'peserta'"
                :class="(activeTab === 'peserta') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Peserta</span>
            </button>

            {{-- 2. Sesi & Presensi --}}
            <button type="button" @click="activeTab = 'sesi'"
                :class="(activeTab === 'sesi') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Sesi &amp; Presensi</span>
            </button>

            {{-- 3. CBT & Asesmen --}}
            <button type="button" @click="activeTab = 'cbt'"
                :class="(activeTab === 'cbt' || activeTab === 'evaluasi') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>CBT &amp; Asesmen</span>
            </button>

            {{-- 4. Keaktifan & Outbound --}}
            <button type="button" @click="activeTab = 'outbound'"
                :class="(activeTab === 'outbound') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Keaktifan &amp; Outbound</span>
            </button>

            {{-- 5. RTL --}}
            <button type="button" @click="activeTab = 'rtl'"
                :class="(activeTab === 'rtl') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>RTL</span>
            </button>

            {{-- 6. Feedback --}}
            <button type="button" @click="activeTab = 'feedback'"
                :class="(activeTab === 'feedback' || activeTab === 'angket') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span>Feedback</span>
                @if(isset($totalRespondenAngket) && $totalRespondenAngket > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $totalRespondenAngket }}
                </span>
                @endif
            </button>

            {{-- 7. Laporan --}}
            <button type="button" @click="activeTab = 'laporan'"
                :class="(activeTab === 'laporan' || activeTab === 'unduh_data') ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="py-3.5 border-b-2 transition-all flex items-center gap-2 text-xs sm:text-sm cursor-pointer -mb-[1px]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Laporan</span>
            </button>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB: PESERTA (BENTO CLEAN DESIGN)                         --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'peserta'" class="p-3.5 sm:p-5 space-y-3.5 sm:space-y-4">
            {{-- Header Tab Peserta --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-extrabold text-slate-900 text-sm sm:text-base tracking-tight">Kader Peserta</h2>
                        <span class="px-2 py-0.2 rounded-full bg-sky-50 text-sky-700 border border-sky-200/80 text-[10px] font-bold">
                            {{ $totalPeserta }} Terdaftar
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-normal mt-0.5">
                        Penerbitan tanda peserta (ID Card), pengingat kegiatan WhatsApp, dan penetapan fasilitator.
                    </p>
                </div>
                
                {{-- Bento Action Cluster --}}
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-1.5 sm:gap-2">
                    @if($canManage)
                    <a href="{{ route('admin.events.enroll', $event) }}" class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>+ Enroll Peserta</span>
                    </a>
                    @endif
                    @if($totalPeserta > 0)
                    <a href="{{ route('admin.events.idcards.batch', $event) }}" target="_blank" class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span class="truncate">ID Card (A4)</span>
                    </a>
                    @if($canManage)
                    <form method="POST" action="{{ route('admin.events.peserta.remind-wa-batch', $event) }}" class="inline-block" onsubmit="return confirm('Kirim notifikasi pengingat kegiatan via WhatsApp Gateway ke seluruh peserta aktif ({{ $totalPeserta }} orang)?')">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold transition shadow-2xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            <span class="truncate">Broadcast WA</span>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>

            {{-- 3-Tile Bento Grid (Kompak & Sejajar 3 Kolom di Mobile, Menggantikan Tumpukan Vertikal) --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                {{-- Tile 1: Total & Kuota --}}
                <div class="p-2 sm:p-3 bg-slate-50/80 hover:bg-white rounded-xl border border-slate-200/80 hover:border-sky-300 shadow-2xs transition flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-600">Total Kader</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="my-0.5">
                        <div class="flex items-baseline gap-1">
                            <span class="text-base sm:text-xl font-black text-slate-900">{{ $totalPeserta }}</span>
                            <span class="text-[10px] sm:text-xs text-slate-400 font-normal">/ {{ $event->kuota ?: '∞' }}</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1 mt-1 overflow-hidden">
                            <div class="bg-sky-500 h-1 rounded-full" style="width: {{ $event->kuota ? min(100, round(($totalPeserta / $event->kuota) * 100)) : 100 }}%"></div>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                        {{ $event->kuota ? (round(($totalPeserta / $event->kuota) * 100) . '% Terisi') : 'Kuota Bebas' }}
                    </span>
                </div>

                {{-- Tile 2: Gender --}}
                <div class="p-2 sm:p-3 bg-slate-50/80 hover:bg-white rounded-xl border border-slate-200/80 hover:border-indigo-300 shadow-2xs transition flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-600">Komposisi Gender</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    </div>
                    <div class="my-1 space-y-1 text-[10px] sm:text-xs">
                        <div class="flex items-center justify-between gap-1">
                            <span class="inline-flex items-center gap-1 text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>Laki-laki</span>
                            <span class="font-bold tabular-nums text-slate-900 whitespace-nowrap">{{ $totalLaki }} peserta</span>
                        </div>
                        <div class="flex items-center justify-between gap-1">
                            <span class="inline-flex items-center gap-1 text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 shrink-0"></span>Perempuan</span>
                            <span class="font-bold tabular-nums text-slate-900 whitespace-nowrap">{{ $totalPerempuan }} peserta</span>
                        </div>
                        <div class="flex h-1.5 overflow-hidden rounded-full bg-slate-200" aria-label="Komposisi gender peserta">
                            <span class="bg-sky-500" style="width: {{ $totalPeserta > 0 ? round(($totalLaki / $totalPeserta) * 100) : 0 }}%"></span>
                            <span class="bg-rose-400" style="width: {{ $totalPeserta > 0 ? round(($totalPerempuan / $totalPeserta) * 100) : 0 }}%"></span>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                        {{ $totalPeserta > 0 ? 'Total ' . $totalPeserta . ' peserta' : 'Belum ada peserta' }}
                    </span>
                </div>

                {{-- Tile 3: Cabang --}}
                <div class="p-2 sm:p-3 bg-slate-50/80 hover:bg-white rounded-xl border border-slate-200/80 hover:border-amber-300 shadow-2xs transition flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-500">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-600">Sebaran</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="my-0.5">
                        <div class="flex items-baseline gap-1">
                            <span class="text-base sm:text-xl font-black text-slate-900">{{ $pcmCounts->count() }}</span>
                            <span class="text-[10px] sm:text-xs text-slate-500 font-medium">Cabang</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-amber-700 font-semibold truncate mt-0.5" title="{{ $pcmCounts->keys()->implode(', ') }}">
                        {{ $pcmCounts->keys()->first() ?: 'Belum Ada' }}
                    </span>
                </div>
            </div>

            {{-- List Peserta Terdaftar dengan Live Search Filter --}}
            <div class="space-y-2 pt-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Daftar Kader Terdaftar ({{ $totalPeserta }})</h3>
                    @if($totalPeserta > 0)
                    <div class="relative w-full sm:w-72">
                        <input type="text" x-model="searchPeserta" placeholder="Cari nama, NBM, cabang..."
                            class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    @endif
                </div>

                {{-- Participant Bento Cards --}}
                <div class="space-y-2 max-h-[34rem] overflow-y-auto pr-0.5">
                    @forelse($peserta as $p)
                    <div x-show="!searchPeserta || '{{ strtolower(addslashes($p->anggota->nama_lengkap . ' ' . $p->anggota->nbm . ' ' . ($p->anggota->pcm ?: $p->anggota->kecamatan))) }}'.includes(searchPeserta.toLowerCase())"
                        class="group p-2.5 sm:p-3 rounded-xl border border-slate-200/90 bg-white hover:border-sky-300 hover:shadow-xs transition-all duration-200 flex items-center justify-between gap-2.5">
                        
                        {{-- Left: Foto & Identitas Kader --}}
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-10 h-[53px] aspect-[3/4] rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200 shadow-2xs relative">
                                @if($p->anggota->foto)
                                <img src="{{ route('foto.anggota', $p->anggota) }}" alt="" class="w-full h-full object-cover object-top">
                                @else
                                <div class="w-full h-full flex items-center justify-center text-[9px] text-slate-400 font-bold bg-slate-100">FOTO</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate leading-snug">{{ $p->anggota->nama_lengkap }}</h4>
                                    @if($p->anggota->pcm)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-100 shrink-0">
                                        {{ $p->anggota->pcm }}
                                    </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5 truncate">
                                    <span class="font-mono text-slate-600 text-[10px] sm:text-[11px]">NBM: {{ $p->anggota->nbm ?: '-' }}</span>
                                    @if($p->anggota->nik)
                                    <span class="text-slate-300 hidden sm:inline">&bull;</span>
                                    <span class="font-mono text-slate-400 text-[10px] hidden sm:inline">NIK: {{ $p->anggota->nik }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right: Actions Bento Cluster --}}
                        @if($canManage)
                        <div class="flex items-center gap-1 shrink-0">
                            @if($p->anggota->no_wa)
                            <form method="POST" action="{{ route('admin.events.peserta.remind-wa', [$event, $p]) }}">
                                @csrf
                                <button type="submit" title="Kirim Pengingat WA ke peserta ini" class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.events.certificate', [$event, $p]) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-lg border border-slate-200 hover:bg-sky-50 hover:text-sky-900 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-600 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Sertifikat</span>
                            </a>
                            <form method="POST" action="{{ route('admin.events.peserta.remove', [$event, $p]) }}" onsubmit="return confirm('Keluarkan peserta {{ $p->anggota->nama_lengkap }} dari event ini?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-red-500 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Keluarkan</span>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                        Belum ada peserta yang terdaftar pada kegiatan ini. Klik tombol <b>+ Enroll Peserta</b> di atas untuk menambahkan kader.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Seksi Penugasan Fasilitator Bento Multi-Kolom --}}
            @if($canManage)
            <div class="pt-3 border-t border-slate-100">
                <form method="POST" action="{{ route('admin.events.fasilitator.update', $event) }}" 
                      x-data="{ 
                          searchFasil: '', 
                          checkedCount: {{ count($assignedFasilitatorIds) }},
                          updateCount() {
                              this.checkedCount = this.$el.querySelectorAll('input[name=\'fasilitator_ids[]\']:checked').length;
                          },
                          toggleAll(val) {
                              this.$el.querySelectorAll('input[name=\'fasilitator_ids[]\']').forEach(cb => {
                                  cb.checked = val;
                              });
                              this.updateCount();
                          }
                      }"
                      @change="updateCount()"
                      class="bg-gradient-to-br from-slate-50/70 via-white to-slate-50/40 rounded-2xl border border-slate-200/90 p-3 sm:p-4 shadow-2xs space-y-3">
                    @csrf
                    
                    {{-- Bento Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-100 text-sky-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-slate-900 text-xs sm:text-sm tracking-tight">Penugasan Fasilitator &amp; Instruktur</h3>
                                    <span class="px-2 py-0.2 rounded-full bg-sky-100 text-sky-800 text-[10px] font-bold" x-text="checkedCount + ' Bertugas'">
                                        {{ count($assignedFasilitatorIds) }} Bertugas
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-normal mt-0.5">
                                    Otorisasi instruktur pendamping untuk pemindaian presensi QR dan penilaian rubrik praktik.
                                </p>
                            </div>
                        </div>

                        @if($fasilitatorOptions->count())
                        <button type="submit" class="self-end sm:self-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-bold rounded-xl shadow-xs transition hover:scale-[1.02] active:scale-[0.98] cursor-pointer shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Penugasan</span>
                        </button>
                        @endif
                    </div>

                    {{-- Search & Quick Toggle Bar (Bento Controls) --}}
                    @if($fasilitatorOptions->count() > 2)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-0.5">
                        <div class="relative flex-1 max-w-xs">
                            <input type="text" x-model="searchFasil" placeholder="Cari nama instruktur..."
                                class="w-full pl-7 pr-3 py-1 text-xs bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-sky-500 focus:border-sky-500 outline-none transition">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-1.5 text-[11px]">
                            <button type="button" @click="toggleAll(true)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                                Pilih Semua
                            </button>
                            <span class="text-slate-300">&bull;</span>
                            <button type="button" @click="toggleAll(false)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition cursor-pointer">
                                Kosongkan
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Fasilitator Bento Multi-Column Grid (2 Kolom di Mobile, 3 di Tablet, 4 di Desktop) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-2.5 max-h-[300px] sm:max-h-[360px] overflow-y-auto pr-0.5">
                        @forelse($fasilitatorOptions as $f)
                        @php
                            $isAssigned = in_array($f->id, $assignedFasilitatorIds);
                            $cleanName = trim(preg_replace('/^(Drs\.|Dr\.|H\.|Hj\.|Ust\.|Ustadz|Prof\.|Ir\.)\s+/i', '', $f->name));
                            $words = explode(' ', $cleanName ?: trim($f->name));
                            $initials = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)) 
                                : strtoupper(substr($words[0], 0, 2));
                        @endphp
                        <label x-show="!searchFasil || '{{ strtolower(addslashes($f->name . ' ' . $f->email)) }}'.includes(searchFasil.toLowerCase())"
                               class="group relative flex flex-col justify-between p-2.5 sm:p-3 rounded-xl border border-slate-200/90 bg-white hover:border-sky-300 hover:shadow-xs has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/50 has-[:checked]:shadow-xs cursor-pointer transition-all duration-200 select-none">
                            
                            {{-- Top: Avatar + Custom Check Indicator --}}
                            <div class="flex items-start justify-between gap-1.5 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-sky-100 group-has-[:checked]:bg-sky-600 group-has-[:checked]:text-white text-slate-700 font-black text-xs flex items-center justify-center shrink-0 transition-colors shadow-2xs">
                                    <span>{{ $initials }}</span>
                                </div>
                                <div class="w-4.5 h-4.5 rounded-md border border-slate-300 group-has-[:checked]:border-sky-600 group-has-[:checked]:bg-sky-600 flex items-center justify-center transition-colors">
                                    <svg class="w-3 h-3 text-white opacity-0 group-has-[:checked]:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <input type="checkbox" name="fasilitator_ids[]" value="{{ $f->id }}"
                                    @checked($isAssigned)
                                    class="sr-only">
                            </div>

                            {{-- Middle: Name & Email --}}
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-slate-900 group-has-[:checked]:text-sky-950 leading-tight line-clamp-2" title="{{ $f->name }}">
                                    {{ $f->name }}
                                </h4>
                                <p class="text-[10px] text-slate-400 group-has-[:checked]:text-sky-700 font-mono truncate mt-0.5" title="{{ $f->email }}">
                                    {{ $f->email }}
                                </p>
                            </div>

                            {{-- Bottom: Status Pill Badge --}}
                            <div class="mt-2 pt-1.5 border-t border-slate-100 group-has-[:checked]:border-sky-200/60 flex items-center justify-between text-[10px]">
                                <span class="font-semibold text-slate-400 group-has-[:checked]:text-sky-700">
                                    <span class="inline group-has-[:checked]:hidden">Lepas Tugas</span>
                                    <span class="hidden group-has-[:checked]:inline font-bold">Bertugas</span>
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-has-[:checked]:bg-emerald-500"></span>
                            </div>
                        </label>
                        @empty
                        <div class="col-span-2 sm:col-span-3 lg:col-span-4 py-6 text-center text-slate-400 text-xs font-normal border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                            Belum ada akun dengan role fasilitator terdaftar di sistem.
                        </div>
                        @endforelse
                    </div>

                    {{-- Bottom Status Hint --}}
                    @if($fasilitatorOptions->count())
                    <div class="pt-1 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                        <span class="flex items-center gap-1 text-slate-500">
                            <svg class="w-3.5 h-3.5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Klik ubin fasilitator untuk mengatur penugasan scanner &amp; penilaian.</span>
                        </span>
                        <button type="submit" class="sm:hidden text-sky-600 font-bold hover:underline shrink-0">
                            Simpan &rarr;
                        </button>
                    </div>
                    @endif
                </form>
            </div>
            @endif
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB: SESI & PRESENSI                                      --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'sesi'" class="p-4 sm:p-5 space-y-3.5">
            <div class="flex items-center justify-between flex-wrap gap-2 pb-2.5 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">Sesi &amp; Presensi</h2>
                    <p class="text-xs text-slate-500 font-normal">Agenda materi pelatihan, narasumber, berkas modul presentasi, dan pemindaian presensi QR.</p>
                </div>
                @if($canManage)
                <button type="button" @click="modalSesi = true" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold shadow-2xs transition">
                    + Tambah Sesi
                </button>
                @endif
            </div>

            {{-- Spotlight Sesi Berjalan Terkini --}}
            @if($liveSesi)
            <div class="bg-gradient-to-br from-[#002b40] to-[#005c8a] rounded-xl p-3.5 sm:p-4 text-white shadow-2xs relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-semibold text-amber-300 border border-white/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#f8bb00] animate-ping"></span>
                        Sesi Berjalan / Terkini
                    </span>
                    <span class="text-[10px] text-slate-300">{{ now()->translatedFormat('d M Y') }}</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 my-2">
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-bold leading-snug text-white" title="{{ $liveSesi->nama_sesi }}">
                            {{ $liveSesi->urutan }}. {{ $liveSesi->nama_sesi }}
                        </h3>
                        <p class="text-xs text-slate-200 mt-0.5">
                            <span class="capitalize font-semibold text-amber-300">[{{ $liveSesi->jenis }}]</span>
                            @if($liveSesi->pemateri) &bull; {{ $liveSesi->pemateri }}@endif
                            @if($liveSesi->jam_mulai) &bull; {{ $liveSesi->jam_mulai }} @if($liveSesi->jam_selesai)- {{ $liveSesi->jam_selesai }}@endif WIB @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($canOperate)
                        <a href="{{ route('admin.events.absensi.manual-page', [$event, $liveSesi]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-white font-semibold border border-white/20 transition text-xs">
                            <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <span>Checklist Manual</span>
                        </a>
                        <a href="{{ route('admin.events.absensi.scan', [$event, $liveSesi]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#f8bb00] hover:bg-amber-400 active:bg-amber-500 text-slate-950 font-bold transition shadow-xs text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Scanner QR</span>
                        </a>
                        <a href="{{ route('admin.events.absensi.cetak', [$event, $liveSesi]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/15 hover:bg-white/25 text-white font-semibold border border-white/20 transition text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Lembar Presensi PDF</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="bg-white/10 rounded-lg p-2.5 border border-white/15 mt-2">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-slate-200">Presensi Kader Saat Ini:</span>
                        <span class="font-bold text-amber-300">{{ $liveSesi->absensi_count }} / {{ $totalPeserta }} Hadir ({{ $totalPeserta > 0 ? round(($liveSesi->absensi_count / $totalPeserta) * 100) : 0 }}%)</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#f8bb00] h-2 rounded-full transition-all" style="width: {{ $totalPeserta > 0 ? min(100, round(($liveSesi->absensi_count / $totalPeserta) * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @endif

            <div class="space-y-2">
                @forelse($sesi as $s)
                <div class="p-3 rounded-lg border border-slate-200/80 bg-slate-50/40 hover:bg-white hover:border-sky-200 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-bold text-xs sm:text-sm text-slate-900 leading-snug">{{ $s->urutan }}. {{ $s->nama_sesi }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider
                                    {{ $s->jenis === 'materi' ? 'bg-sky-100 text-sky-800' : ($s->jenis === 'sholat' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $s->jenis }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                @if($s->pemateri)<b>Narasumber:</b> {{ $s->pemateri }} &bull; @endif
                                @if($s->tanggal)<b>Tanggal:</b> {{ $s->tanggal->format('d/m/Y') }} &bull; @endif
                                @if($s->jam_mulai)<b>Pukul:</b> {{ $s->jam_mulai }} @if($s->jam_selesai)- {{ $s->jam_selesai }}@endif WIB &bull; @endif
                                <span>Kehadiran: <b class="text-slate-800">{{ $s->absensi_count }}</b>/{{ $totalPeserta }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-1 shrink-0 flex-wrap">
                            @if($s->file_materi)
                            <a href="{{ route('sesi.materi.download', $s) }}" title="Unduh Berkas Materi" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white hover:bg-sky-50 text-sky-800 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Modul</span>
                            </a>
                            @endif

                            @if($canOperate)
                            <a href="{{ route('admin.events.absensi.manual-page', [$event, $s]) }}" title="Presensi Manual / Checklist Peserta" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Checklist</span>
                            </a>
                            <a href="{{ route('admin.events.absensi.cetak', [$event, $s]) }}" title="Cetak Lembar Presensi Fisik" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Lembar</span>
                            </a>
                            <a href="{{ route('admin.events.absensi.scan', [$event, $s]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-sky-50 text-sky-900 hover:bg-sky-100 border border-sky-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                <span>Scan QR</span>
                            </a>
                            @endif

                            @if($canManage)
                            <form method="POST" action="{{ route('admin.events.sesi.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus sesi agenda ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2 py-1 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition shadow-2xs">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                    Belum ada sesi agenda yang dijadwalkan. Klik tombol <b>+ Tambah Sesi</b> di atas untuk menambahkan.
                </div>
                @endforelse
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 3: CBT & ASESMEN                                      --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'cbt' || activeTab === 'evaluasi'" class="p-4 sm:p-5 space-y-4">
            {{-- Header Tab CBT & Asesmen --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">CBT &amp; Asesmen</h2>
                    <p class="text-xs text-slate-500 font-normal">Evaluasi komprehensif kader: tes kognitif CBT, praktik psikomotorik, penilaian sikap afektif, dan pembobotan AHP–SAW.</p>
                </div>
                @php
                    $tahapSelesai = 0;
                    if ($pretestSelesaiCount > 0) $tahapSelesai++;
                    if ($posttestSelesaiCount > 0) $tahapSelesai++;
                    if ($psikomotorDinilaiCount > 0) $tahapSelesai++;
                    if ($afektifSelesaiCount > 0) $tahapSelesai++;
                @endphp
                <div class="flex items-center gap-2.5">
                    <div class="text-right">
                        <span class="text-xs font-semibold text-slate-700 block">Progress Asesmen: {{ $tahapSelesai }} dari 4 tahapan aktif</span>
                        <span class="text-[11px] text-slate-400 font-normal">{{ round(($tahapSelesai / 4) * 100) }}% kelengkapan instrumen</span>
                    </div>
                    <div class="w-16 h-2 bg-slate-100 rounded-full overflow-hidden shrink-0 border border-slate-200/80">
                        <div class="h-full bg-emerald-600 rounded-full transition-all duration-300" style="width: {{ ($tahapSelesai / 4) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            {{-- LEVEL 2: CBT & ASESMEN SUB-NAVIGATION --}}
            <div class="border-b border-slate-200/90 flex items-center gap-6 overflow-x-auto whitespace-nowrap scrollbar-none text-xs">
                <button type="button" @click="subTabCbt = 'tes'"
                    :class="subTabCbt === 'tes' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Pretest &amp; Posttest
                </button>
                <button type="button" @click="subTabCbt = 'psikomotor'"
                    :class="subTabCbt === 'psikomotor' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Psikomotor
                </button>
                <button type="button" @click="subTabCbt = 'afektif'"
                    :class="subTabCbt === 'afektif' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Afektif
                </button>
                <button type="button" @click="subTabCbt = 'angket'"
                    :class="subTabCbt === 'angket' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Self Assessment
                </button>
                <button type="button" @click="subTabCbt = 'ahp_saw'"
                    :class="subTabCbt === 'ahp_saw' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    AHP–SAW
                </button>
            </div>

            {{-- SUB-TAB 1: PRETEST & POSTTEST --}}
            <div x-show="subTabCbt === 'tes'" class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    {{-- Pretest Kognitif --}}
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition duration-150">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                CBT
                            </div>
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-sm">Pretest Kognitif</h3>
                                    @if($sesiPretest?->status === 'sedang_berlangsung' || $sesiPretest?->status === 'aktif')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Sesi Aktif"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">Evaluasi pemahaman awal sebelum pemaparan materi perkaderan</p>
                                <div class="text-[11px] text-slate-600 pt-1 flex items-center gap-2 font-medium">
                                    <span>{{ $pretestSelesaiCount }} dari {{ $totalPeserta }} peserta selesai</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-500">{{ $soalPretestCount }} Soal CBT</span>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('admin.events.soal.index', [$event, 'pretest']) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-sky-50 border border-slate-200 hover:border-sky-300 text-xs font-semibold text-slate-700 hover:text-sky-900 shadow-2xs transition">
                                <span>Kelola Pretest</span>
                                <span class="text-sky-600">&rarr;</span>
                            </a>
                        </div>
                    </div>

                    {{-- Posttest Kognitif --}}
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition duration-150">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                CBT
                            </div>
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-sm">Posttest Kognitif</h3>
                                    @if($sesiPosttest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'aktif')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Sesi Aktif"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500">Evaluasi pemahaman peserta setelah seluruh materi perkaderan selesai</p>
                                <div class="text-[11px] text-slate-600 pt-1 flex items-center gap-2 font-medium">
                                    <span>{{ $posttestSelesaiCount }} dari {{ $totalPeserta }} peserta selesai</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-slate-500">{{ $soalPosttestCount }} Soal CBT</span>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('admin.events.soal.index', [$event, 'posttest']) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-sky-50 border border-slate-200 hover:border-sky-300 text-xs font-semibold text-slate-700 hover:text-sky-900 shadow-2xs transition">
                                <span>Kelola Posttest</span>
                                <span class="text-sky-600">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 2: PSIKOMOTOR --}}
            <div x-show="subTabCbt === 'psikomotor'" class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-slate-900 text-sm">Praktik Psikomotor &amp; Keterampilan</h3>
                            <p class="text-xs text-slate-500">Penilaian praktik ibadah, keterampilan dakwah, khutbah, dan penugasan lapangan peserta</p>
                            <div class="text-[11px] text-slate-600 pt-1 flex items-center gap-2 font-medium">
                                <span>{{ $psikomotorDinilaiCount }} dari {{ $totalPeserta }} peserta dinilai</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-500">{{ $psikomotorTemplateCount }} rubrik penilaian aktif</span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('admin.events.psikomotor', $event) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition">
                            <span>Input Nilai Praktik</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                {{-- Keaktifan & Partisipasi Forum --}}
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-sm">Poin Keaktifan &amp; Partisipasi Forum</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-100 text-sky-800 border border-sky-200">Bonus Nilai</span>
                            </div>
                            <p class="text-xs text-slate-500">Pencatatan poin saat forum/materi: bertanya, menjawab, adzan, kultum/presentasi, imam sholat, atau juara outbound</p>
                            <div class="text-[11px] text-slate-600 pt-1 flex items-center gap-2 font-medium">
                                <span>Tiap aksi bernilai 1 poin &bull; Otomatis diperhitungkan ke dalam nilai akhir peserta</span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('admin.events.keaktifan', $event) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                            <span>Catat Poin Keaktifan</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 3: AFEKTIF --}}
            <div x-show="subTabCbt === 'afektif'" class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-slate-900 text-sm">Penilaian Sikap Afektif</h3>
                            <p class="text-xs text-slate-500">Observasi kedisiplinan, integritas, akhlak, dan kepatuhan ibadah kader selama karantina perkaderan</p>
                            <div class="text-[11px] text-slate-600 pt-1 flex items-center gap-2 font-medium">
                                <span>{{ $afektifSelesaiCount }} dari {{ $totalPeserta }} peserta terobservasi</span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('admin.events.afektif', $event) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-2xs transition">
                            <span>Input Nilai Sikap</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 4: SELF ASSESSMENT (ANGKET EVALUASI) --}}
            <div x-show="subTabCbt === 'angket'" class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-slate-900 text-sm">Self Assessment &amp; Angket Evaluasi</h3>
                            <p class="text-xs text-slate-500">Evaluasi mandiri peserta dan feedback komprehensif terhadap materi, narasumber, serta fasilitas Baitul Arqam</p>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <button type="button" @click="activeTab = 'feedback'; subTabFeedback = 'kelola'"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white hover:bg-sky-50 border border-slate-200 hover:border-sky-300 text-xs font-semibold text-slate-700 hover:text-sky-900 shadow-2xs transition">
                            <span>Kelola Angket Evaluasi</span>
                            <span class="text-sky-600">&rarr;</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 5: AHP-SAW --}}
            <div x-show="subTabCbt === 'ahp_saw'" class="space-y-3">
                <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <h3 class="font-bold text-slate-900 text-sm">Metode Keputusan AHP–SAW</h3>
                            <p class="text-xs text-slate-500">Kombinasi Analytical Hierarchy Process (bobot kriteria kognitif, afektif, psikomotor, presensi, keaktifan) &amp; Simple Additive Weighting (perankingan kelulusan).</p>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('admin.events.ranking', $event) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-2xs transition">
                            <span>Buka Matriks &amp; Ranking</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 4: KEAKTIFAN & OUTBOUND                               --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'outbound'" class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">Keaktifan &amp; Outbound</h2>
                    <p class="text-xs text-slate-500 font-normal">Pembagian regu outbound yang berimbang dan pencatatan keikutsertaan aktif kader di setiap sesi perkaderan.</p>
                </div>
            </div>

            {{-- LEVEL 2: KEAKTIFAN & OUTBOUND SUB-NAVIGATION --}}
            <div class="border-b border-slate-200/90 flex items-center gap-6 overflow-x-auto whitespace-nowrap scrollbar-none text-xs">
                <button type="button" @click="subTabOutbound = 'regu'"
                    :class="subTabOutbound === 'regu' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Regu Outbound
                </button>
                <button type="button" @click="subTabOutbound = 'keaktifan'"
                    :class="subTabOutbound === 'keaktifan' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Poin Keaktifan Forum
                </button>
            </div>

            {{-- SUB-TAB 1: REGU OUTBOUND --}}
            <div x-show="subTabOutbound === 'regu'" class="space-y-3">
                <div class="bg-slate-50/60 rounded-xl border border-slate-200 p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-900 border border-amber-200 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Regu Lapangan Outbound</span>
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ $outboundKelompokCount }} Kelompok Terbentuk</span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed max-w-2xl">
                        Sistem membagi kader ke dalam regu outbound secara otomatis dengan menyeimbangkan rasio gender (putra/putri) dan asal cabang PCM untuk membangun ukhuwah persyarikatan.
                    </p>

                    <div class="pt-1 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.events.outbound.index', $event) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Kelola Kelompok</span>
                        </a>
                        @if($outboundKelompokCount > 0)
                        <a href="{{ route('admin.events.outbound.pdf', $event) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>PDF Outbound</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 2: POIN KEAKTIFAN FORUM --}}
            <div x-show="subTabOutbound === 'keaktifan'" class="space-y-3">
                <div class="bg-slate-50/60 rounded-xl border border-slate-200 p-4 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 text-sky-900 border border-sky-200 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>Poin Keaktifan Forum</span>
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ $keaktifanCount }} Aksi Dicatat</span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed max-w-2xl">
                        Fasilitator mencatat keikutsertaan aktif kader di setiap sesi: bertanya, menjawab, kultum, imam, adzan, dan dinamika keaktifan forum (1 poin/aksi, terkap bonus maks 10 poin).
                    </p>

                    <div class="pt-1">
                        <a href="{{ route('admin.events.keaktifan', $event) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Catat Poin Keaktifan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 5: RTL                                                --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'rtl'" class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2.5 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">RTL</h2>
                    <p class="text-xs text-slate-500 font-normal">Rencana Tindak Lanjut Peserta — Monitoring penugasan rencana aksi paska pelatihan untuk mengabdi di Cabang, Ranting, atau AUM.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($canManage)
                    <a href="{{ route('admin.events.rtl.index', $event) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                        <span>Kelola Pertanyaan &amp; Review RTL</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Pengumpulan RTL Ramping --}}
            <div class="grid sm:grid-cols-3 gap-2.5">
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-[11px] text-slate-500 block">Total RTL Terkumpul</span>
                    <span class="text-base font-bold text-slate-900">{{ $rtlSelesaiCount }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalPeserta }} Peserta</span></span>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-[11px] text-slate-500 block">Belum Mengumpulkan</span>
                    <span class="text-base font-bold text-amber-700">{{ max(0, $totalPeserta - $rtlSelesaiCount) }} Kader</span>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-[11px] text-slate-500 block">Instrumen Pertanyaan</span>
                    <span class="text-base font-bold text-sky-700">{{ $rtlSoalCount }} Butir RTL</span>
                </div>
            </div>

            {{-- Antrean Penyerahan RTL Terbaru --}}
            <div class="space-y-2">
                <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Penyerahan Berkas RTL Terbaru</h3>
                <div class="space-y-1.5">
                    @forelse($recentRtls as $rtlItem)
                    <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200/80 bg-slate-50/50 hover:bg-white transition">
                        <div class="min-w-0">
                            <p class="font-bold text-xs text-slate-900 truncate">{{ $rtlItem->anggota->nama_lengkap }}</p>
                            <p class="text-[11px] text-slate-500">{{ $rtlItem->anggota->pcm ?: ($rtlItem->anggota->kecamatan ?: '-') }} &bull; Kirim: {{ $rtlItem->updated_at?->format('d/m/Y H:i') }} WIB</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                {{ $rtlItem->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $rtlItem->status }}
                            </span>
                            @if($canManage)
                            <a href="{{ route('admin.events.rtl.show', [$event, $rtlItem]) }}" class="px-2 py-1 rounded-md border border-slate-200 hover:bg-sky-50 text-xs font-semibold text-sky-800 transition">
                                Tinjau
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                        Belum ada peserta yang mengunggah tugas RTL.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 6: FEEDBACK & EVALUASI PENYELENGGARAAN                --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'feedback' || activeTab === 'angket'" class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">Feedback &amp; Evaluasi Penyelenggaraan</h2>
                    <p class="text-xs text-slate-500 font-normal">Instrumen angket evaluasi kepuasan kader (Skala Likert 1–5), analisis mutu sarana &amp; narasumber, serta masukan konstruktif peserta.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('portal.angket.index', $event) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 shadow-2xs transition">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Buka Form Peserta</span>
                    </a>
                    @if($canManage && $angketItems->isEmpty())
                    <form method="POST" action="{{ route('admin.events.angket.seed-defaults', $event) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#005c8a] hover:bg-[#004a70] text-white text-xs font-semibold shadow-2xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Isi Butir Standar MPKSDI</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- LEVEL 2: FEEDBACK SUB-NAVIGATION --}}
            <div class="border-b border-slate-200/90 flex items-center gap-6 overflow-x-auto whitespace-nowrap scrollbar-none text-xs">
                <button type="button" @click="subTabFeedback = 'rekap'"
                    :class="subTabFeedback === 'rekap' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer flex items-center gap-1.5">
                    <span>Rekap &amp; Statistik Skor</span>
                </button>
                <button type="button" @click="subTabFeedback = 'komentar'"
                    :class="subTabFeedback === 'komentar' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer flex items-center gap-1.5">
                    <span>Masukan &amp; Saran Kader</span>
                    @if($angketKomentarList->count() > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                        {{ $angketKomentarList->count() }}
                    </span>
                    @endif
                </button>
                <button type="button" @click="subTabFeedback = 'kelola'"
                    :class="subTabFeedback === 'kelola' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer flex items-center gap-1.5">
                    <span>Kelola Butir Angket ({{ $angketItems->count() }})</span>
                </button>
                <button type="button" @click="subTabFeedback = 'pratinjau'"
                    :class="subTabFeedback === 'pratinjau' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer flex items-center gap-1.5">
                    <span>Pratinjau Skala Likert</span>
                </button>
            </div>

            <div x-show="subTabFeedback === 'pratinjau'" class="grid gap-4 xl:grid-cols-[minmax(0,1.5fr)_minmax(280px,0.8fr)]">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="preview-angket-title">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h3 id="preview-angket-title" class="text-sm font-bold text-slate-900">Pratinjau angket peserta</h3>
                        <p class="mt-1 text-xs text-slate-600">Peserta menilai setiap pernyataan dari 1 (Sangat Kurang) sampai 5 (Sangat Baik).</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($angketItems as $item)
                        <div class="px-4 py-3">
                            <p class="mb-2 text-xs font-medium text-slate-800">{{ $item->teks_item }}</p>
                            <div class="grid grid-cols-5 gap-1.5 sm:gap-2" role="group" aria-label="Skala Likert untuk {{ $item->teks_item }}">
                                @foreach([1 => 'Sangat Kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'] as $score => $label)
                                <div class="flex min-h-14 flex-col items-center justify-center rounded-lg border border-slate-200 bg-white px-1 py-1.5 text-center {{ $score === 5 ? 'border-sky-300 bg-sky-50 text-sky-900' : 'text-slate-700' }}">
                                    <span class="text-sm font-bold">{{ $score }}</span>
                                    <span class="mt-0.5 hidden text-[10px] leading-tight sm:block">{{ $label }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-1 flex justify-between text-[10px] text-slate-500 sm:hidden"><span>Sangat Kurang</span><span>Sangat Baik</span></div>
                        </div>
                        @empty
                        <p class="px-4 py-8 text-center text-xs text-slate-500">Tambahkan butir angket agar pratinjau skala Likert tampil.</p>
                        @endforelse
                    </div>
                </section>

                <section class="flex flex-col rounded-xl border border-slate-200 bg-white" aria-labelledby="preview-feedback-title">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h3 id="preview-feedback-title" class="text-sm font-bold text-slate-900">Preview ulasan tertulis (Isian Terbuka)</h3>
                        <p class="mt-1 text-xs text-slate-600">Pertanyaan terbuka untuk refleksi perasaan kader dan masukan penyelenggaraan.</p>
                    </div>
                    <div class="flex flex-1 flex-col gap-3.5 p-4">
                        <div class="p-3 rounded-lg bg-emerald-50/50 border border-emerald-200">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 uppercase tracking-wider mb-1">
                                💖 Pertanyaan Terakhir (Isian Terbuka)
                            </span>
                            <label class="block text-xs font-bold text-slate-900 mb-1">Bagaimana perasaan Anda setelah mengikuti perkaderan Baitul Arqam ini?</label>
                            <textarea rows="3" disabled placeholder="Peserta menceritakan perasaan, kesan batin, dan motivasi ber-Muhammadiyah di sini..." class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 placeholder:text-slate-400 font-medium"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Saran &amp; Kritik Konstruktif untuk Penyelenggara (Opsional)</label>
                            <textarea rows="2" disabled placeholder="Kolom saran untuk fasilitas, konsumsi, atau panitia..." class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 placeholder:text-slate-400"></textarea>
                        </div>
                        <p class="text-[11px] leading-relaxed text-slate-500">Pratinjau ini tidak menyimpan jawaban. Peserta mengirim skor Likert dan isian refleksi melalui Form Peserta.</p>
                    </div>
                </section>
            </div>

            {{-- SUB-TAB 1: REKAP & STATISTIK SKOR --}}
            <div x-show="subTabFeedback === 'rekap'" class="space-y-4">
                {{-- 4 Metric Cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-3">
                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Responden Feedback</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $totalRespondenAngket }}</span>
                            <span class="text-xs text-slate-500 font-normal">/ {{ $totalPeserta }} peserta</span>
                        </div>
                        <span class="text-[10px] text-emerald-700 font-semibold mt-0.5 block">
                            {{ $totalPeserta > 0 ? round(($totalRespondenAngket / $totalPeserta) * 100, 1) : 0 }}% tingkat partisipasi
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Indeks Kepuasan Rata-rata</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-xl sm:text-2xl font-black text-[#005c8a]">{{ number_format($rerataSkorAngket, 2) }}</span>
                            <span class="text-xs text-slate-500 font-normal">/ 5.00 ⭐</span>
                        </div>
                        <span class="text-[10px] font-semibold mt-0.5 block
                            {{ $rerataSkorAngket >= 4.0 ? 'text-emerald-700' : ($rerataSkorAngket >= 3.0 ? 'text-sky-700' : 'text-amber-700') }}">
                            {{ $rerataSkorAngket >= 4.5 ? 'Sangat Memuaskan' : ($rerataSkorAngket >= 3.75 ? 'Memuaskan / Baik' : ($rerataSkorAngket >= 3.0 ? 'Cukup Baik' : ($rerataSkorAngket > 0 ? 'Perlu Peningkatan' : 'Belum Ada Penilaian'))) }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Instrumen Evaluasi</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $angketItems->count() }}</span>
                            <span class="text-xs text-slate-500 font-normal">butir pertanyaan</span>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">
                            {{ $angketItemsGrouped->count() }} dimensi/kategori aspek
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-200/80 shadow-2xs">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Masukan &amp; Saran</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $angketKomentarList->count() }}</span>
                            <span class="text-xs text-slate-500 font-normal">ulasan tertulis</span>
                        </div>
                        <button type="button" @click="subTabFeedback = 'komentar'" class="text-[10px] text-sky-600 hover:underline font-semibold mt-0.5 block text-left">
                            Lihat semua kritik &amp; saran &rarr;
                        </button>
                    </div>
                </div>

                {{-- Detail Skor per Kategori & Butir Likert --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-1">
                        <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Rincian Penilaian per Aspek &amp; Butir Evaluasi</h3>
                        <span class="text-[11px] text-slate-500">Skala 1 (Kurang) &ndash; 5 (Sangat Baik)</span>
                    </div>

                    @forelse($angketItemsGrouped as $kategori => $itemList)
                    <div class="bg-white rounded-xl border border-slate-200/90 p-3.5 sm:p-4 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-50 text-[#005c8a] font-bold text-xs flex items-center justify-center border border-sky-200/80">
                                    {{ $kategori }}
                                </span>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-slate-900">
                                        @if($kategori === 'A')
                                            Aspek A: Materi &amp; Kurikulum
                                        @elseif($kategori === 'B')
                                            Aspek B: Narasumber &amp; Fasilitator
                                        @elseif($kategori === 'C')
                                            Aspek C: Pelayanan &amp; Sarana Penyelenggaraan
                                        @else
                                            Aspek {{ $kategori }}
                                        @endif
                                    </h4>
                                    <p class="text-[10px] text-slate-500">{{ $itemList->count() }} butir instrumen</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($itemList as $item)
                            @php
                                $itemStat = $skorPerItem->get($item->id);
                                $avgSkor = $itemStat ? (float) $itemStat->avg_skor : 0.0;
                                $respCount = $itemStat ? (int) $itemStat->total_responden : 0;
                                $pct = round(($avgSkor / 5.0) * 100);
                            @endphp
                            <div class="p-2.5 rounded-lg bg-slate-50/70 border border-slate-200/70 hover:bg-white hover:border-sky-200 transition">
                                <div class="flex items-start justify-between gap-3 mb-1.5">
                                    <p class="text-xs text-slate-800 font-medium flex-1">
                                        <span class="font-bold text-slate-500 mr-1">{{ $item->urutan }}.</span>
                                        {{ $item->teks_item }}
                                    </p>
                                    <div class="text-right shrink-0">
                                        @if($respCount > 0)
                                        <span class="font-bold text-xs text-[#005c8a]">{{ number_format($avgSkor, 2) }}</span>
                                        <span class="text-[10px] text-slate-400">/ 5.0</span>
                                        @else
                                        <span class="text-[10px] text-slate-400 italic">Belum ada nilai</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 rounded-full bg-slate-200/80 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $avgSkor >= 4.0 ? 'bg-emerald-500' : ($avgSkor >= 3.0 ? 'bg-sky-500' : ($avgSkor > 0 ? 'bg-amber-500' : 'bg-slate-300')) }}"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-500 shrink-0 font-mono">{{ $respCount }} respon</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="p-8 rounded-xl border border-dashed border-slate-200 bg-slate-50/50 text-center space-y-3">
                        <div class="w-10 h-10 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs sm:text-sm text-slate-800">Instrumen Angket Belum Dibuat</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Tambahkan butir pertanyaan angket atau gunakan instrumen standar Baitul Arqam MPKSDI.</p>
                        </div>
                        @if($canManage)
                        <form method="POST" action="{{ route('admin.events.angket.seed-defaults', $event) }}">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#005c8a] hover:bg-[#004a70] text-white text-xs font-semibold shadow-2xs transition inline-flex items-center gap-1.5">
                                <span>Gunakan Templat Butir Standar MPKSDI</span>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- SUB-TAB 2: MASUKAN & SARAN KADER --}}
            <div x-show="subTabFeedback === 'komentar'" class="space-y-3">
                <div class="flex items-center justify-between pb-1">
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Ulasan Kualitatif, Kritik, &amp; Saran Konstruktif Peserta</h3>
                    <span class="text-[11px] text-slate-500">{{ $angketKomentarList->count() }} masukan tercatat</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($angketKomentarList as $kom)
                    <div class="p-3.5 sm:p-4 rounded-xl border border-slate-200/90 bg-white hover:border-sky-200 shadow-2xs transition flex flex-col justify-between space-y-2.5">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 text-[#005c8a] font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($kom->anggota?->nama_lengkap ?? 'K', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-xs text-slate-900 truncate">{{ $kom->anggota?->nama_lengkap ?? 'Kader Peserta' }}</p>
                                        <p class="text-[10px] text-slate-500 truncate">{{ $kom->anggota?->pcm ?: ($kom->anggota?->kecamatan ?: 'Peserta') }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-mono text-slate-400 shrink-0">
                                    {{ $kom->created_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            @if($kom->perasaan)
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    💖 Perasaan Mengikuti Perkaderan:
                                </span>
                                <p class="text-xs text-slate-800 leading-relaxed italic bg-emerald-50/50 p-2.5 rounded-lg border-l-2 border-emerald-500 font-medium">
                                    &ldquo;{{ $kom->perasaan }}&rdquo;
                                </p>
                            </div>
                            @endif
                            @if($kom->komentar)
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-800 bg-sky-50 px-2 py-0.5 rounded border border-sky-200">
                                    💡 Masukan &amp; Saran Konstruktif:
                                </span>
                                <p class="text-xs text-slate-700 leading-relaxed italic bg-slate-50/70 p-2.5 rounded-lg border-l-2 border-sky-500">
                                    &ldquo;{{ $kom->komentar }}&rdquo;
                                </p>
                            </div>
                            @endif
                            @if(! $kom->perasaan && ! $kom->komentar)
                            <p class="text-xs text-slate-400 italic">Peserta tidak meninggalkan isian tertulis.</p>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="md:col-span-2 text-center py-10 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                        Belum ada saran atau kritik tertulis dari peserta pelatihan.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- SUB-TAB 3: KELOLA INSTRUMEN ANGKET --}}
            <div x-show="subTabFeedback === 'kelola'" class="space-y-4">
                @if($canManage)
                {{-- Form Tambah Butir --}}
                <div class="p-3.5 sm:p-4 bg-slate-50/70 rounded-xl border border-slate-200/90 shadow-2xs">
                    <h3 class="font-bold text-xs sm:text-sm text-slate-900 mb-2">Tambah Butir Angket Baru</h3>
                    <form method="POST" action="{{ route('admin.events.angket.item.store', $event) }}" class="flex gap-2 flex-wrap items-end">
                        @csrf
                        <div class="w-full sm:w-44">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Kategori Aspek</label>
                            <select name="kategori" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                                <option value="A">A - Materi &amp; Kurikulum</option>
                                <option value="B">B - Narasumber &amp; Fasilitator</option>
                                <option value="C">C - Pelayanan &amp; Sarana</option>
                                <option value="D">D - Aspek Tambahan</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[240px]">
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pernyataan Evaluasi (Likert 1–5)</label>
                            <input name="teks_item" required placeholder="Contoh: Materi disampaikan dengan jelas dan aplikatif..."
                                class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-[#005c8a] hover:bg-[#004a70] text-white text-xs font-semibold rounded-lg shadow-2xs transition shrink-0 cursor-pointer">
                            + Tambah Butir
                        </button>
                    </form>
                </div>
                @endif

                {{-- Daftar Butir per Kategori --}}
                <div class="space-y-3">
                    @forelse($angketItemsGrouped as $kategori => $items)
                    <div class="bg-white rounded-xl border border-slate-200/90 p-3.5 sm:p-4 shadow-2xs">
                        <h4 class="font-bold text-xs sm:text-sm text-slate-900 mb-2.5">
                            Kategori {{ $kategori }} ({{ $items->count() }} Butir)
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($items as $item)
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-lg bg-slate-50/70 border border-slate-200/60 hover:bg-white hover:border-sky-200 transition text-xs">
                                <p class="text-slate-800 font-medium">
                                    <span class="font-bold text-slate-500 mr-1">{{ $item->urutan }}.</span>
                                    {{ $item->teks_item }}
                                </p>
                                @if($canManage)
                                <form method="POST" action="{{ route('admin.events.angket.item.destroy', [$event, $item]) }}" onsubmit="return confirm('Hapus butir angket ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-slate-400 hover:text-red-600 font-bold px-2 py-0.5 rounded transition" title="Hapus Butir">&times;</button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                        Belum ada butir angket evaluasi.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 7: LAPORAN                                            --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'laporan' || activeTab === 'unduh_data'" class="p-4 sm:p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">Laporan</h2>
                    <p class="text-xs text-slate-500 font-normal">Pengolahan nilai akhir terpadu, penetapan status kelulusan peserta, dan penerbitan dokumen resmi berkop MPKSDI.</p>
                </div>
            </div>

            {{-- LEVEL 2: LAPORAN SUB-NAVIGATION --}}
            <div class="border-b border-slate-200/90 flex items-center gap-6 overflow-x-auto whitespace-nowrap scrollbar-none text-xs">
                <button type="button" @click="subTabLaporan = 'rekap'"
                    :class="subTabLaporan === 'rekap' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Rekap
                </button>
                <button type="button" @click="subTabLaporan = 'export'"
                    :class="subTabLaporan === 'export' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Export Data
                </button>
                <button type="button" @click="subTabLaporan = 'dokumen'"
                    :class="subTabLaporan === 'dokumen' ? 'border-[#063A60] text-[#063A60] font-semibold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                    class="py-2.5 border-b-2 transition -mb-[1px] cursor-pointer">
                    Laporan &amp; Dokumen Resmi
                </button>
            </div>

                        {{-- SUB-TAB 1: REKAP --}}
            <div x-show="subTabLaporan === 'rekap'" class="space-y-4">
                {{-- Leaderboard Teratas (Top 5 Peserta) Card --}}
            <div class="bg-slate-50/70 rounded-xl border border-slate-200/90 p-3.5 sm:p-4">
                <div class="flex items-center justify-between pb-2 mb-2.5 border-b border-slate-200/80">
                    <div>
                        <h3 class="font-bold text-slate-900 text-xs sm:text-sm tracking-tight">Leaderboard Teratas (Top 5 Peserta)</h3>
                        <p class="text-[11px] text-slate-500 font-normal">Kalkulasi nilai berbobot kognitif, psikomotorik, &amp; afektif</p>
                    </div>
                    <a href="{{ route('admin.events.ranking', $event) }}" class="text-[11px] font-semibold text-sky-600 hover:underline">
                        Lihat Ranking Penuh &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
                    @forelse($leaderboard as $idx => $lead)
                    <div class="p-2.5 rounded-lg border border-slate-200 bg-white hover:border-sky-300 transition shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0
                                    {{ $idx === 0 ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($idx === 1 ? 'bg-slate-200 text-slate-800' : ($idx === 2 ? 'bg-amber-700/20 text-amber-900' : 'bg-slate-100 text-slate-600')) }}">
                                    #{{ $idx + 1 }}
                                </span>
                                <span class="text-xs sm:text-sm font-black text-sky-800">{{ number_format($lead->nilai_akhir, 1) }}</span>
                            </div>
                            <p class="text-xs font-bold text-slate-900 line-clamp-1" title="{{ $lead->anggota->nama_lengkap }}">{{ $lead->anggota->nama_lengkap }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ $lead->anggota->pcm ?: ($lead->anggota->kecamatan ?: '-') }}</p>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px]">
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded font-semibold text-[9px] uppercase
                                {{ $lead->status_kelulusan === 'Lulus' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                                {{ $lead->status_kelulusan === 'Lulus Bersyarat' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                                {{ $lead->status_kelulusan === 'Tidak Lulus' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                                {{ $lead->status_kelulusan ?? 'Diproses' }}
                            </span>
                            <span class="text-slate-400 font-medium">NA</span>
                        </div>
                    </div>
                    @empty
                    <div class="lg:col-span-5 text-center py-4 text-slate-400 text-xs">
                        Belum ada nilai akhir yang terhitung. Nilai otomatis tersinkronisasi seiring aktivitas peserta.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Pratinjau Tabel Peringkat (Top 10 Peserta) --}}
            <div class="pt-3 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-bold text-slate-900 text-xs sm:text-sm">Pratinjau Hasil Kelulusan &amp; Nilai Akhir (Top 10)</h3>
                    <a href="{{ route('admin.events.ranking', $event) }}" class="text-xs font-semibold text-sky-600 hover:underline">
                        Lihat Seluruh Peserta &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase">
                                <th class="py-2 px-3">#</th>
                                <th class="py-2 px-3">Nama Kader</th>
                                <th class="py-2 px-3">Cabang (PCM)</th>
                                <th class="py-2 px-2 text-center">Posttest</th>
                                <th class="py-2 px-2 text-center">Afektif</th>
                                <th class="py-2 px-2 text-center">Psikomotor</th>
                                <th class="py-2 px-2 text-center">Presensi</th>
                                <th class="py-2 px-3 text-center">NA</th>
                                <th class="py-2 px-3">Status Kelulusan</th>
                                <th class="py-2 px-2 text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rankingPreview as $r)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2 px-3 font-bold text-slate-900">{{ $r->ranking ?? '-' }}</td>
                                <td class="py-2 px-3 font-semibold text-slate-900">{{ $r->anggota->nama_lengkap }}</td>
                                <td class="py-2 px-3 text-slate-600">{{ $r->anggota->pcm ?: ($r->anggota->kecamatan ?: '-') }}</td>
                                <td class="py-2 px-2 text-center font-mono">{{ number_format($r->nilai_posttest, 1) }}</td>
                                <td class="py-2 px-2 text-center font-mono">{{ number_format($r->nilai_afektif, 1) }}</td>
                                <td class="py-2 px-2 text-center font-mono">{{ number_format($r->nilai_psikomotor, 1) }}</td>
                                <td class="py-2 px-2 text-center font-mono">{{ number_format($r->nilai_kehadiran, 1) }}%</td>
                                <td class="py-2 px-3 text-center font-bold font-mono text-sky-800">{{ number_format($r->nilai_akhir, 2) }}</td>
                                <td class="py-2 px-3">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold uppercase tracking-wider
                                        {{ $r->status_kelulusan === 'Lulus' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                                        {{ $r->status_kelulusan === 'Lulus Bersyarat' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                                        {{ $r->status_kelulusan === 'Tidak Lulus' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                                        {{ $r->status_kelulusan ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    @if($r->grade)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-black
                                        {{ $r->grade === 'A' ? 'bg-sky-100 text-sky-800 border border-sky-300' : '' }}
                                        {{ $r->grade === 'B' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                                        {{ $r->grade === 'C' ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                                        {{ $r->grade }}
                                    </span>
                                    @else
                                        <span class="text-slate-300 text-[10px]">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="py-6 text-center text-slate-400 text-xs">Belum ada data nilai akhir yang terhitung.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

            {{-- SUB-TAB 2: EXPORT DATA --}}
            <div x-show="subTabLaporan === 'export'" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-3.5">
                    {{-- Ekspor Excel --}}
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-900">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Ekspor Data Lengkap (Excel)</span>
                            </span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Spreadsheet XLSX terformat rapi memuat rincian aspek nilai, presensi %, nilai akhir, dan peringkat seluruh kader.</p>
                        </div>
                        <a href="{{ route('admin.events.exportExcel', $event) }}" class="w-full text-center py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                            Unduh Excel (XLSX)
                        </a>
                    </div>

                    {{-- Laporan Evaluasi PDF --}}
                    <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/40 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-900">
                                <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Laporan Evaluasi Lengkap (PDF)</span>
                            </span>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">Laporan resmi berkop PDM Karanganyar, metadata penyelenggaraan, rekapitulasi 14 kolom, dan lembar pengesahan tanda tangan.</p>
                        </div>
                        <a href="{{ route('admin.events.report', $event) }}" class="w-full text-center py-2 px-3 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                            Unduh Laporan PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- SUB-TAB 3: LAPORAN & DOKUMEN RESMI --}}
            <div x-show="subTabLaporan === 'dokumen'" class="space-y-4">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    {{-- Live PPT / Slideshow Evaluasi --}}
                    <div class="p-4 rounded-xl border border-amber-300 bg-gradient-to-br from-amber-50/70 to-amber-100/40 flex flex-col justify-between space-y-3 shadow-2xs">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-950">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                    <span>Live PPT / Slideshow</span>
                                </span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold uppercase bg-amber-200 text-amber-900 border border-amber-300">Live</span>
                            </div>
                            <p class="text-[11px] text-slate-700 mt-1">Presentasi interaktif 16:9 layar penuh untuk penutupan &amp; yudisium. Mendukung remote clicker PPT &amp; ekspor PDF.</p>
                        </div>
                        <a href="{{ route('admin.events.presentasi', $event) }}" target="_blank" class="w-full text-center py-2 px-2 bg-gradient-to-r from-amber-500 to-[#f8bb00] hover:from-amber-600 hover:to-amber-500 text-slate-950 font-bold rounded-lg text-xs shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>Buka Live PPT</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    {{-- Berita Acara Yudisium PDF --}}
                    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/40 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-950">
                                <svg class="w-4 h-4 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Berita Acara Yudisium</span>
                            </span>
                            <p class="text-[11px] text-slate-600 mt-1">Dokumen legalitas sidang pleno kelulusan resmi berkop PDM, statistik kelulusan, &amp; 3 tanda tangan.</p>
                        </div>
                        <a href="{{ route('admin.events.berita-acara', $event) }}" target="_blank" class="w-full text-center py-2 px-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                            Cetak Berita Acara
                        </a>
                    </div>

                    {{-- Cetak Semua Sertifikat Massal --}}
                    <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/40 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-900">
                                <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/></svg>
                                <span>Sertifikat Resmi 2 Sisi</span>
                            </span>
                            <p class="text-[11px] text-slate-600 mt-1">Unduh seluruh sertifikat kader lulus sekaligus dalam 1 PDF (Hal 1: Sertifikat QR; Hal 2: Transkrip JP).</p>
                        </div>
                        <div class="space-y-1.5">
                            @if($event->status === 'selesai')
                            <a href="{{ route('admin.events.certificates.batch', $event) }}?download=1" class="w-full text-center py-2 px-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition block">
                                Unduh Batch Sertifikat
                            </a>
                            @else
                            <span class="w-full text-center py-2 px-2 bg-slate-200 text-slate-500 rounded-lg text-[11px] font-semibold block cursor-not-allowed">
                                Status Selesai Diperlukan
                            </span>
                            @endif
                            @if($canManage)
                            <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex items-center justify-center gap-1 w-full text-[11px] text-indigo-700 hover:text-indigo-900 font-semibold py-1 hover:underline transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Edit Pengesahan &amp; TTD Sertifikat</span>
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- Halaman Ranking Interaktif --}}
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col justify-between space-y-3">
                        <div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-slate-900">
                                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <span>Tabel Ranking Penuh</span>
                            </span>
                            <p class="text-[11px] text-slate-600 mt-1">Buka halaman rekapitulasi ranking nilai penuh beserta aksi cetak sertifikat perorangan.</p>
                        </div>
                        <a href="{{ route('admin.events.ranking', $event) }}" class="w-full text-center py-2 px-2 bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 rounded-lg text-xs font-semibold transition shadow-2xs">
                            Buka Halaman Ranking &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- 6. MODAL TAMBAH SESI AGENDA DENGAN BERKAS MATERI --}}
    @if($canManage)
    <div x-show="modalSesi" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4" @click.away="modalSesi = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Sesi Agenda &amp; Materi</h3>
                <button type="button" @click="modalSesi = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.events.sesi.store', $event) }}" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Sesi Agenda <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_sesi" required placeholder="Contoh: AIK & Kemuhammadiyahan" class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Sesi <span class="text-red-500">*</span></label>
                        <select name="jenis" required class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500 cursor-pointer">
                            <option value="materi">Materi</option>
                            <option value="sholat">Ibadah Sholat</option>
                            <option value="outbound">Outbound</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Narasumber / Pemateri</label>
                        <input type="text" name="pemateri" placeholder="Nama Pemateri" class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $event->tanggal_mulai?->format('Y-m-d') }}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg outline-none focus:border-sky-500 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg outline-none focus:border-sky-500 text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg outline-none focus:border-sky-500 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Unggah Berkas Modul / Slide (PDF, PPT, DOCX, ZIP max 15MB)</label>
                    <input type="file" name="file_materi" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tautan Materi Eksternal (Google Drive / Video / Bahan Bacaan)</label>
                    <input type="url" name="link_materi" placeholder="https://..." class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="modalSesi = false" class="px-4 py-2 border border-slate-300 rounded-lg font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-semibold shadow-2xs cursor-pointer">Simpan Sesi</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
