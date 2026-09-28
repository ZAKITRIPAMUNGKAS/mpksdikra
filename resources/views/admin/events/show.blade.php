@extends('layouts.app')

@section('title', 'Dashboard Pelatihan — ' . $event->nama_event)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'sesi', modalSesi: false, editSesi: null }">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-sky-600 transition">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar Kegiatan</span>
        </a>

        @if($canManage)
        <div class="flex items-center gap-2 flex-wrap">
            <form method="POST" action="{{ route('admin.events.status', $event) }}" class="inline-block">
                @csrf
                <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-200/90 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 cursor-pointer transition shadow-2xs">
                    @foreach(['persiapan','berlangsung','selesai'] as $s)
                    <option value="{{ $s }}" @selected($event->status === $s)>Status: {{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.events.edit', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">Edit Info</a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Hapus kegiatan ini secara permanen?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-700 bg-white hover:bg-red-50 text-xs font-semibold transition shadow-2xs">Hapus</button>
            </form>
        </div>
        @endif
    </div>

    {{-- Mission Control Hero Banner --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-7 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-48 h-48 text-[#005c8a] opacity-[0.18]" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold uppercase tracking-wider
                        {{ $event->status === 'persiapan' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                        {{ $event->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                        {{ $event->status === 'selesai' ? 'bg-emerald-50 text-emerald-800 border border-emerald-300' : '' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $event->status === 'berlangsung' ? 'bg-sky-500 animate-pulse' : ($event->status === 'persiapan' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                        {{ ucfirst($event->status) }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $event->tanggal_mulai?->format('d M Y') }} — {{ $event->tanggal_selesai?->format('d M Y') }}</span>
                    </span>
                    @if($event->lokasi)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">
                        <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ $event->lokasi }}</span>
                    </span>
                    @endif
                </div>

                <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">{{ $event->nama_event }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 max-w-2xl font-normal">
                    Pusat Komando &amp; Monitoring Pelatihan Baitul Arqam &bull; Pimpinan Daerah Muhammadiyah Karanganyar
                </p>
            </div>

            {{-- Fast Action Group --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                @if($totalPeserta > 0)
                <a href="{{ route('admin.events.idcards.batch', $event) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-sky-50 text-sky-900 border border-sky-200 hover:bg-sky-100 text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak ID Card Massal (A4)</span>
                </a>
                @endif
                @if($canManage)
                <a href="{{ route('admin.events.enroll', $event) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>+ Enroll Peserta</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Hub Navigasi Operasional --}}
        <div class="mt-6 pt-5 border-t border-slate-100 flex gap-2 flex-wrap text-xs">
            <a href="{{ route('admin.events.ranking', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap &amp; Ranking</span>
            </a>
            <a href="{{ route('admin.events.report', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Laporan Perkaderan (PDF)</span>
            </a>
            @if($canManage)
            <a href="{{ route('admin.events.exportExcel', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Ekspor Excel</span>
            </a>
            @endif
            <a href="{{ route('admin.events.outbound.index', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Kelompok Outbound</span>
            </a>
            <a href="{{ route('admin.events.keaktifan', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <span>Poin Keaktifan</span>
            </a>
            <a href="{{ route('admin.events.psikomotor', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">Rubrik Psikomotor</a>
            <a href="{{ route('admin.events.soal.index', [$event, 'pretest']) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">Pretest</a>
            <a href="{{ route('admin.events.soal.index', [$event, 'posttest']) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">Posttest</a>
            @if($canManage)
            <a href="{{ route('admin.events.afektif', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">Afektif</a>
            <a href="{{ route('admin.events.angket', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">Angket</a>
            <a href="{{ route('admin.events.rtl.index', $event) }}" class="px-3 py-1.5 rounded-lg border border-slate-200/90 bg-white hover:border-sky-300 hover:bg-sky-50/50 hover:text-sky-900 font-semibold text-slate-700 transition shadow-2xs">RTL</a>
            @endif
        </div>
    </div>

    {{-- Live Training Mission Control Metrik Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Peserta & Gender --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-medium uppercase tracking-wider">Kader Peserta</span>
                <span class="w-2 h-2 rounded-full bg-sky-500"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ $totalPeserta }}</span>
                <span class="text-xs text-slate-500">/ {{ $event->kuota ?: '∞' }} Kuota</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>L: <b class="text-slate-800">{{ $totalLaki }}</b></span>
                <span>P: <b class="text-slate-800">{{ $totalPerempuan }}</b></span>
                <span class="text-sky-700 font-semibold">{{ $pcmCounts->count() }} Cabang</span>
            </div>
        </div>

        {{-- Card 2: Progres Sesi & Presensi --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-medium uppercase tracking-wider">Jadwal Sesi</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ $totalSesi }}</span>
                <span class="text-xs text-slate-500">Sesi Agenda</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>Materi: <b>{{ $sesi->where('jenis', 'materi')->count() }}</b></span>
                <span>Sholat: <b>{{ $sesi->where('jenis', 'sholat')->count() }}</b></span>
                <span>Outbound: <b>{{ $sesi->where('jenis', 'outbound')->count() }}</b></span>
            </div>
        </div>

        {{-- Card 3: Evaluasi Kognitif (Pre/Post) --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-medium uppercase tracking-wider">Tes Kognitif</span>
                <span class="w-2 h-2 rounded-full {{ ($sesiPretest?->status === 'sedang_berlangsung' || $sesiPosttest?->status === 'sedang_berlangsung') ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-slate-900">
                    {{ $posttestSelesaiCount }}/{{ $totalPeserta }}
                </span>
                <span class="text-xs text-slate-500">Posttest Selesai</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>Pretest: <b>{{ $pretestSelesaiCount }}</b></span>
                <span class="text-xs {{ $sesiPosttest?->status === 'sedang_berlangsung' ? 'text-amber-700 font-bold' : 'text-slate-500' }}">
                    {{ $sesiPosttest?->status === 'sedang_berlangsung' ? 'Posttest Aktif' : 'Posttest Tertutup' }}
                </span>
            </div>
        </div>

        {{-- Card 4: RTL & Rata-rata Nilai --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span class="font-medium uppercase tracking-wider">Rerata Nilai &amp; RTL</span>
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($rerataNilai, 1) }}</span>
                <span class="text-xs text-slate-500">Skala 100</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>RTL Terkirim: <b>{{ $rtlSelesaiCount }}</b></span>
                <span>Afektif: <b>{{ $afektifSelesaiCount }}</b></span>
            </div>
        </div>
    </div>

    {{-- Live Sesi & Leaderboard Highlight Section --}}
    <div class="grid lg:grid-cols-3 gap-6 items-start">
        {{-- Live Session Spotlight Card (1 Kolom) --}}
        <div class="bg-gradient-to-br from-[#002b40] to-[#005c8a] rounded-xl p-5 text-white shadow-2xs relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/15 text-[11px] font-semibold text-amber-300 border border-white/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#f8bb00] animate-ping"></span>
                    Sesi Kegiatan Terkini
                </span>
                <span class="text-[11px] text-slate-300">{{ now()->translatedFormat('l, d M Y') }}</span>
            </div>

            @if($liveSesi)
            <div class="my-3">
                <h3 class="text-base sm:text-lg font-bold leading-snug text-white">{{ $liveSesi->urutan }}. {{ $liveSesi->nama_sesi }}</h3>
                <p class="text-xs text-slate-200 mt-1">
                    <span class="capitalize font-semibold text-amber-300">[{{ $liveSesi->jenis }}]</span>
                    @if($liveSesi->pemateri) &bull; {{ $liveSesi->pemateri }}@endif
                </p>
                @if($liveSesi->jam_mulai)
                <p class="text-xs text-slate-300 mt-0.5">Pukul {{ $liveSesi->jam_mulai }} @if($liveSesi->jam_selesai)- {{ $liveSesi->jam_selesai }}@endif WIB</p>
                @endif
            </div>

            <div class="bg-white/10 rounded-lg p-3 border border-white/15 my-4">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-slate-200">Presensi Kader:</span>
                    <span class="font-bold text-amber-300">{{ $liveSesi->absensi_count }} / {{ $totalPeserta }} Hadir</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-[#f8bb00] h-2 rounded-full transition-all" style="width: {{ $totalPeserta > 0 ? min(100, round(($liveSesi->absensi_count / $totalPeserta) * 100)) : 0 }}%"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                @if($canOperate)
                <a href="{{ route('admin.events.absensi.scan', [$event, $liveSesi]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-[#f8bb00] hover:bg-amber-400 active:bg-amber-500 text-slate-950 font-bold transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Buka Scanner</span>
                </a>
                <a href="{{ route('admin.events.absensi.cetak', [$event, $liveSesi]) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-white font-semibold border border-white/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Lembar PDF</span>
                </a>
                @endif
            </div>
            @else
            <div class="text-center py-8 text-slate-300">
                <p class="text-xs">Belum ada sesi yang dijadwalkan.</p>
            </div>
            @endif
        </div>

        {{-- Live Leaderboard Top 5 (2 Kolom) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base tracking-tight">Leaderboard Teratas (Top 5 Peserta)</h2>
                    <p class="text-xs text-slate-500 font-normal">Kalkulasi nilai berbobot kognitif, psikomotorik, &amp; afektif</p>
                </div>
                <a href="{{ route('admin.events.ranking', $event) }}" class="text-xs font-semibold text-sky-600 hover:underline">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="space-y-2">
                @forelse($leaderboard as $idx => $lead)
                <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-sky-50/40 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                            {{ $idx === 0 ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($idx === 1 ? 'bg-slate-200 text-slate-800' : ($idx === 2 ? 'bg-amber-700/20 text-amber-900' : 'bg-slate-100 text-slate-600')) }}">
                            {{ $idx + 1 }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-slate-900 truncate">{{ $lead->anggota->nama_lengkap }}</p>
                            <p class="text-[11px] text-slate-500">{{ $lead->anggota->pcm ?: ($lead->anggota->kecamatan ?: '-') }} &bull; {{ $lead->status_kelulusan ?? 'Diproses' }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-sm sm:text-base font-extrabold text-sky-800">{{ number_format($lead->nilai_akhir, 1) }}</span>
                        <span class="text-[10px] text-slate-400 block font-normal">Nilai Akhir</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-slate-400 text-xs">
                    Belum ada nilai akhir yang terhitung. Nilai otomatis tersinkronisasi seiring aktivitas peserta.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tabs Section: Sesi, Peserta, Fasilitator --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        {{-- Tab Headers --}}
        <div class="border-b border-slate-200 bg-slate-50/70 px-4 flex gap-4 text-xs font-semibold">
            <button type="button" @click="activeTab = 'sesi'" :class="activeTab === 'sesi' ? 'border-sky-600 text-sky-800 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'" class="py-3 px-3 border-b-2 transition">
                Jadwal &amp; Materi Sesi ({{ $totalSesi }})
            </button>
            <button type="button" @click="activeTab = 'peserta'" :class="activeTab === 'peserta' ? 'border-sky-600 text-sky-800 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'" class="py-3 px-3 border-b-2 transition">
                Daftar Peserta ({{ $totalPeserta }})
            </button>
            @if($canManage)
            <button type="button" @click="activeTab = 'fasilitator'" :class="activeTab === 'fasilitator' ? 'border-sky-600 text-sky-800 bg-white' : 'border-transparent text-slate-600 hover:text-slate-900'" class="py-3 px-3 border-b-2 transition">
                Penugasan Fasilitator ({{ count($assignedFasilitatorIds) }})
            </button>
            @endif
        </div>

        {{-- Tab Content: Sesi --}}
        <div x-show="activeTab === 'sesi'" class="p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base tracking-tight">Manajemen Sesi &amp; Modul Materi</h2>
                    <p class="text-xs text-slate-500">Kelola agenda materi, pemateri, berkas modul presentasi, dan pemindaian presensi</p>
                </div>
                @if($canManage)
                <button type="button" @click="modalSesi = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                    + Tambah Sesi Agenda
                </button>
                @endif
            </div>

            <div class="space-y-2.5">
                @forelse($sesi as $s)
                <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/40 hover:bg-white hover:border-sky-200 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs sm:text-sm text-slate-900 leading-snug">{{ $s->urutan }}. {{ $s->nama_sesi }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                    {{ $s->jenis === 'materi' ? 'bg-sky-100 text-sky-800' : ($s->jenis === 'sholat' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $s->jenis }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                @if($s->pemateri)<b>Narasumber:</b> {{ $s->pemateri }} &bull; @endif
                                @if($s->tanggal)<b>Tanggal:</b> {{ $s->tanggal->format('d/m/Y') }} &bull; @endif
                                @if($s->jam_mulai)<b>Pukul:</b> {{ $s->jam_mulai }} @if($s->jam_selesai)- {{ $s->jam_selesai }}@endif WIB &bull; @endif
                                <span>Kehadiran: <b class="text-slate-800">{{ $s->absensi_count }}</b>/{{ $totalPeserta }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                            @if($s->file_materi)
                            <a href="{{ route('sesi.materi.download', $s) }}" title="Unduh Berkas Materi" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-sky-50 text-sky-800 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Modul</span>
                            </a>
                            @endif

                            @if($canOperate)
                            <a href="{{ route('admin.events.absensi.cetak', [$event, $s]) }}" title="Cetak Lembar Presensi Fisik" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Lembar Presensi</span>
                            </a>
                            <a href="{{ route('admin.events.absensi.scan', [$event, $s]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-sky-50 text-sky-900 hover:bg-sky-100 border border-sky-200 text-xs font-semibold transition shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                <span>Scan QR</span>
                            </a>
                            @endif

                            @if($canManage)
                            <form method="POST" action="{{ route('admin.events.sesi.destroy', [$event, $s]) }}" onsubmit="return confirm('Hapus sesi agenda ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition shadow-2xs">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-10 text-slate-400 text-xs">
                    Belum ada sesi agenda yang dijadwalkan. Klik tombol di atas untuk menambahkan.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Tab Content: Peserta --}}
        <div x-show="activeTab === 'peserta'" class="p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base tracking-tight">Kader Terdaftar dalam Kegiatan</h2>
                    <p class="text-xs text-slate-500 font-normal">Total: {{ $totalPeserta }} peserta aktif</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($totalPeserta > 0)
                    <a href="{{ route('admin.events.idcards.batch', $event) }}" target="_blank" class="px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-800 text-xs font-semibold hover:bg-sky-100 transition shadow-2xs">
                        Cetak Semua ID Card PDF
                    </a>
                    @endif
                    @if($canManage)
                    <a href="{{ route('admin.events.enroll', $event) }}" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold transition shadow-2xs">
                        + Tambah Peserta
                    </a>
                    @endif
                </div>
            </div>

            <div class="space-y-2 max-h-[32rem] overflow-y-auto pr-1">
                @forelse($peserta as $p)
                <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-sky-200 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-12 rounded bg-slate-200 overflow-hidden shrink-0 border border-slate-300">
                            @if($p->anggota->foto)
                            <img src="{{ route('foto.anggota', $p->anggota) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-[10px] text-slate-500 font-bold">FOTO</div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $p->anggota->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $p->anggota->pcm ?: ($p->anggota->kecamatan ?: '-') }} &bull; NBM: {{ $p->anggota->nbm ?: 'Belum Ada' }}</p>
                        </div>
                    </div>

                    @if($canManage)
                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="{{ route('admin.events.certificate', [$event, $p]) }}" class="px-2.5 py-1 rounded-md border border-slate-200 hover:bg-sky-50 hover:text-sky-900 text-xs font-semibold text-slate-700 transition shadow-2xs">
                            Sertifikat
                        </a>
                        <form method="POST" action="{{ route('admin.events.peserta.remove', [$event, $p]) }}" onsubmit="return confirm('Keluarkan peserta dari event ini?')">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold transition shadow-2xs">
                                Keluarkan
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-10 text-slate-400 text-xs">
                    Belum ada peserta yang terdaftar pada kegiatan ini.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Tab Content: Fasilitator --}}
        @if($canManage)
        <div x-show="activeTab === 'fasilitator'" class="p-5 sm:p-6 space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-base tracking-tight">Penugasan Fasilitator &amp; Instruktur</h2>
                <p class="text-xs text-slate-500 mt-0.5">Centang fasilitator yang bertugas mengoperasikan presensi dan mengisi penilaian psikomotor event ini</p>
            </div>

            <form method="POST" action="{{ route('admin.events.fasilitator.update', $event) }}" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-3">
                    @forelse($fasilitatorOptions as $f)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-sky-400 has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/40 cursor-pointer transition">
                        <input type="checkbox" name="fasilitator_ids[]" value="{{ $f->id }}"
                            @checked(in_array($f->id, $assignedFasilitatorIds))
                            class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <div>
                            <span class="text-xs sm:text-sm font-semibold text-slate-900 block leading-tight">{{ $f->name }}</span>
                            <span class="text-xs text-slate-500 font-normal">{{ $f->email }}</span>
                        </div>
                    </label>
                    @empty
                    <div class="sm:col-span-2 py-6 text-center text-slate-400 text-xs font-normal">
                        Belum ada akun dengan role fasilitator yang terdaftar dalam sistem.
                    </div>
                    @endforelse
                </div>

                @if($fasilitatorOptions->count())
                <div class="pt-3 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                        Simpan Penugasan Fasilitator
                    </button>
                </div>
                @endif
            </form>
        </div>
        @endif
    </div>

    {{-- Modal Tambah Sesi dengan Dukungan Materi --}}
    @if($canManage)
    <div x-show="modalSesi" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4" @click.away="modalSesi = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Sesi Agenda &amp; Materi</h3>
                <button type="button" @click="modalSesi = false" class="text-slate-400 hover:text-slate-600">&times;</button>
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
                        <select name="jenis" required class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500">
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
                    <input type="file" name="file_materi" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tautan Materi Eksternal (Google Drive / Video / Bahan Bacaan)</label>
                    <input type="url" name="link_materi" placeholder="https://..." class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none focus:border-sky-500">
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="modalSesi = false" class="px-4 py-2 border border-slate-300 rounded-lg font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-semibold shadow-2xs">Simpan Sesi</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
