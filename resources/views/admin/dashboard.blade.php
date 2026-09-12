@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">
    {{-- 1. Header Hero Banner Card dengan Ornamen Batik Solo Kawung --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-48 h-48 sm:w-64 sm:h-64 text-[#005c8a] opacity-[0.24] pointer-events-none" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">MPKSDI Karanganyar</span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-xs font-medium text-slate-500">v2.2.0 Stabil</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Dashboard Administrasi</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">Pangkalan data kader Muhammadiyah &amp; evaluasi kelulusan Baitul Arqam Karanganyar.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <a href="{{ route('panduan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 shadow-2xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Panduan</span>
                </a>
                <a href="{{ route('admin.anggota.import') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-300 shadow-2xs transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Impor Excel</span>
                </a>
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <svg class="w-4 h-4 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Event Baru</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Actionable Operational Ribbon (Jika ada pendaftar pending) --}}
    @if($pendingAnggota > 0)
    <div class="bg-amber-50/90 border border-amber-200/90 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-950">Ada {{ $pendingAnggota }} kader baru menunggu verifikasi administrasi</p>
                <p class="text-xs text-amber-900/80 mt-0.5">Periksa kelengkapan NIK, data pimpinan, dan berkas pendaftar sebelum mengaktifkan akun.</p>
            </div>
        </div>
        <a href="{{ route('admin.anggota.index', ['status' => 'menunggu_verifikasi']) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shrink-0 transition shadow-2xs">
            <span>Buka Antrean Verifikasi</span>
            <span>&rarr;</span>
        </a>
    </div>
    @endif

    {{-- 3. KPI Metric Grid (Simetris & Rapi) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
        {{-- Card 1: Total Kader --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Kader Terdaftar</span>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $totalAnggota }} <span class="text-xs font-normal text-slate-500">Kader</span></p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $statusCounts['aktif'] }} Kader Aktif</span>
                <span class="font-semibold text-[#005c8a] bg-sky-50 border border-sky-100 px-2 py-0.5 rounded-md text-xs">+{{ $monthlyData[5] ?? 0 }} Bulan Ini</span>
            </div>
        </div>

        {{-- Card 2: Menunggu Verifikasi --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $pendingAnggota > 0 ? 'text-amber-800' : 'text-slate-500' }}">Perlu Verifikasi</span>
                    @if($pendingAnggota > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>
                <p class="text-2xl sm:text-3xl font-bold {{ $pendingAnggota > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-2 tabular-nums">{{ $pendingAnggota }} <span class="text-xs font-normal text-slate-500">Antrean</span></p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Pendaftar Baru</span>
                @if($pendingAnggota > 0)
                    <a href="{{ route('admin.anggota.index', ['status' => 'menunggu_verifikasi']) }}" class="text-amber-700 hover:text-amber-900 font-semibold hover:underline flex items-center gap-0.5 text-xs">
                        Tinjau Antrean &rarr;
                    </a>
                @else
                    <span class="text-emerald-700 font-medium text-xs">Semua Terverifikasi</span>
                @endif
            </div>
        </div>

        {{-- Card 3: Event BA Aktif --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Event Baitul Arqam</span>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $activeEvents }} <span class="text-xs font-normal text-slate-500">Event Aktif</span></p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $completedEvents }} Selesai</span>
                <span>{{ $totalEvents }} Total Agenda</span>
            </div>
        </div>

        {{-- Card 4: Kelulusan Perkaderan --}}
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Tingkat Kelulusan</span>
                <p class="text-2xl sm:text-3xl font-bold text-[#005c8a] mt-2 tabular-nums">{{ $persenKelulusan }}% <span class="text-xs font-normal text-slate-500">({{ $totalLulus }} Lulus)</span></p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Rata-rata NA: <strong class="text-slate-700 font-semibold">{{ $avgNilaiAkhir ? number_format($avgNilaiAkhir, 1) : '—' }}</strong></span>
                <span class="text-slate-400 text-xs">Skala 100</span>
            </div>
        </div>
    </div>

    {{-- 4. Visual Analytics & Tren Perkaderan --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        {{-- Grafik Tren Pendaftaran Kader --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between"
             x-data="growthChartData(@js($monthlyLabels), @js($monthlyData))"
             x-init="initChart()">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 tracking-tight">Tren Pendaftaran Kader</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Statistik pendaftaran mandiri kader 6 bulan terakhir</p>
                    </div>
                    <div class="text-xs text-slate-500 font-medium">
                        Total Pendaftar: <strong class="text-slate-800">{{ $totalAnggota }}</strong>
                    </div>
                </div>

                {{-- Canvas Area --}}
                <div class="mt-4 relative h-48 sm:h-64 w-full">
                    <canvas x-ref="growthCanvas"></canvas>
                </div>
            </div>

            {{-- Tren Highlight Metrics --}}
            <div class="mt-4 pt-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                    <span>Total 6 Bulan: <strong class="text-slate-900 font-semibold">{{ array_sum($monthlyData) }} Kader</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-300 hidden sm:inline">&bull;</span>
                    <span>Bulan Ini: <strong class="text-[#005c8a] font-semibold">+{{ $monthlyData[5] ?? 0 }} Baru</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-300 hidden sm:inline">&bull;</span>
                    <span>Kelulusan Rata-rata: <strong class="text-slate-900 font-semibold">{{ $avgNilaiAkhir ? number_format($avgNilaiAkhir, 1) : '—' }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Grafik Distribusi Status Pendaftaran --}}
        <div class="lg:col-span-4 bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between"
             x-data="statusChartData(@js(array_values($statusCounts)))"
             x-init="initChart()">
            <div>
                <div class="pb-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900 tracking-tight">Status Pendaftaran</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Proporsi verifikasi akun anggota</p>
                </div>

                {{-- Canvas Doughnut --}}
                <div class="mt-4 relative h-40 sm:h-48 w-full flex items-center justify-center">
                    <canvas x-ref="statusCanvas"></canvas>
                </div>
            </div>

            {{-- Legend List --}}
            <div class="mt-4 pt-3.5 border-t border-slate-100 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 text-slate-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#005c8a]"></span>
                        <span>Aktif Terverifikasi</span>
                    </span>
                    <span class="font-semibold text-slate-900 tabular-nums">{{ $statusCounts['aktif'] }} <span class="text-xs font-normal text-slate-400">({{ $totalAnggota > 0 ? round(($statusCounts['aktif'] / $totalAnggota) * 100) : 0 }}%)</span></span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 text-slate-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#f8bb00]"></span>
                        <span>Menunggu Verifikasi</span>
                    </span>
                    <span class="font-semibold {{ $pendingAnggota > 0 ? 'text-amber-700' : 'text-slate-900' }} tabular-nums">{{ $statusCounts['menunggu'] }} <span class="text-xs font-normal text-slate-400">({{ $totalAnggota > 0 ? round(($statusCounts['menunggu'] / $totalAnggota) * 100) : 0 }}%)</span></span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 text-slate-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#e11d48]"></span>
                        <span>Ditolak / Revisi</span>
                    </span>
                    <span class="font-semibold text-slate-900 tabular-nums">{{ $statusCounts['ditolak'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="inline-flex items-center gap-2 text-slate-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                        <span>Nonaktif</span>
                    </span>
                    <span class="font-semibold text-slate-900 tabular-nums">{{ $statusCounts['nonaktif'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Baris Kegiatan Terkini & Pendaftar Baru (Operational Center) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        {{-- Event BA Terkini dengan Aksi Cepat Cetak Laporan (PDF & Excel) --}}
        <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base tracking-tight">Agenda Perkaderan &amp; Dokumen Resmi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jadwal pelaksanaan, ranking nilai, dan dokumen cetak resmi</p>
                    </div>
                    <a href="{{ route('admin.events.index') }}" class="text-xs font-semibold text-[#005c8a] hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($recentEvents as $ev)
                    <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.events.show', $ev) }}" title="{{ $ev->nama_event }}" class="font-semibold text-slate-900 hover:text-[#005c8a] text-sm truncate block transition">
                                    {{ $ev->nama_event }}
                                </a>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium capitalize shrink-0
                                    {{ $ev->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                                    {{ $ev->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                                    {{ $ev->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                    {{ $ev->status }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $ev->tanggal_mulai ? $ev->tanggal_mulai->format('d M Y') : '—' }}
                                </span>
                                <span>&bull;</span>
                                <span class="font-medium text-slate-700 tabular-nums">{{ $ev->peserta_aktif_count }} Kader</span>
                            </div>
                        </div>
                        
                        {{-- Tombol Output Cetak Cepat (PDF & Excel) atau Kelola --}}
                        <div class="flex items-center gap-1.5 shrink-0 self-stretch sm:self-center pt-1 sm:pt-0">
                            @if($ev->status === 'persiapan')
                            <a href="{{ route('admin.events.show', $ev) }}" 
                               title="Kelola Agenda & Sesi Baitul Arqam"
                               class="flex-1 sm:flex-initial text-center px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs inline-flex items-center justify-center gap-1">
                                <span>Kelola Event</span>
                                <span>&rarr;</span>
                            </a>
                            @else
                            <a href="{{ route('admin.events.ranking', $ev) }}" 
                               title="Lihat Peringkat & Rekap Nilai"
                               class="flex-1 sm:flex-initial text-center px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                <span>Peringkat</span>
                            </a>
                            <a href="{{ route('admin.events.exportExcel', $ev) }}" 
                               title="Unduh Laporan Format Excel (.xlsx)"
                               class="flex-1 sm:flex-initial text-center px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs inline-flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Excel</span>
                            </a>
                            <a href="{{ route('admin.events.report', $ev) }}" 
                               title="Unduh Laporan Resmi Format PDF"
                               class="flex-1 sm:flex-initial text-center px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold transition shadow-2xs inline-flex items-center justify-center gap-1">
                                <svg class="w-3.5 h-3.5 text-[#005c8a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>PDF</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada kegiatan Baitul Arqam yang dibuat.
                        <a href="{{ route('admin.events.create') }}" class="text-[#005c8a] hover:underline font-semibold block mt-1">Buat Event Sekarang &rarr;</a>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row gap-2">
                <a href="{{ route('admin.events.create') }}" class="flex-1 py-2 px-3.5 bg-[#005c8a] hover:bg-[#00486d] text-white font-semibold rounded-lg text-xs transition flex items-center justify-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Agenda Baru</span>
                </a>
                <a href="{{ route('admin.events.index') }}" class="py-2 px-3.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-lg border border-slate-300 text-xs transition flex items-center justify-center gap-2 shadow-2xs">
                    <span>Semua Agenda</span>
                </a>
            </div>
        </div>

        {{-- Pendaftar Baru Perlu Verifikasi --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base tracking-tight">Kader Menunggu Verifikasi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Antrean persetujuan berkas pendaftaran baru</p>
                    </div>
                    <a href="{{ route('admin.anggota.index', ['status' => 'menunggu_verifikasi']) }}" class="text-xs font-semibold text-[#005c8a] hover:underline">
                        Lihat Antrean &rarr;
                    </a>
                </div>

                <div class="mt-4 divide-y divide-slate-100">
                    @forelse($latestPending as $p)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200">
                                {{ strtoupper(substr($p->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="font-semibold text-slate-900 text-sm truncate block">
                                    {{ $p->nama_lengkap }}
                                </span>
                                <span class="text-xs text-slate-500 block truncate mt-0.5">
                                    {{ $p->kecamatan ? 'PCM ' . $p->kecamatan : 'Karanganyar' }} &bull; {{ $p->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('admin.anggota.show', $p) }}" class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 text-xs font-semibold transition shrink-0 shadow-2xs">
                            Periksa
                        </a>
                    </div>
                    @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        <p class="font-semibold text-slate-700">Semua pendaftaran telah diverifikasi</p>
                        <p class="text-xs text-slate-400 mt-0.5">Tidak ada antrean pending saat ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.anggota.index') }}" class="w-full py-2 px-3.5 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-lg border border-slate-200 text-xs transition flex items-center justify-center gap-2">
                    <span>Buka Pangkalan Data Anggota</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 6. Sebaran Kader per Wilayah & Pintasan Cepat --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
        {{-- Sebaran Kader per Cabang (Top Kecamatan) --}}
        <div class="lg:col-span-6 bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base tracking-tight">Sebaran Kader per Cabang (PCM)</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Konsentrasi kader terbanyak di Karanganyar</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">Top Wilayah</span>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($topKecamatan as $kec)
                    @php
                        $persenKec = $totalAnggota > 0 ? round(($kec->total / $totalAnggota) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-semibold text-slate-800">PCM {{ $kec->kecamatan }}</span>
                            <span class="font-medium text-slate-600 tabular-nums">{{ $kec->total }} Kader <span class="text-xs text-slate-400">({{ $persenKec }}%)</span></span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#005c8a] rounded-full" style="width: {{ max($persenKec, 4) }}%;"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada data kecamatan.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="/#sebaran" target="_blank" class="w-full py-1.5 text-center text-xs font-semibold text-[#005c8a] hover:underline block">
                    Lihat Peta Spasial 17 Cabang Karanganyar &rarr;
                </a>
            </div>
        </div>

        {{-- Pintasan Akses Operasional --}}
        <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            {{-- Card 1: Master Anggota --}}
            <a href="{{ route('admin.anggota.index') }}" class="group bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-[#005c8a]/50 transition flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-[#005c8a] flex items-center justify-center border border-sky-100 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-[#005c8a] transition">Pangkalan Data Kader</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Verifikasi berkas pendaftar, kelola profil, ekspor data, dan terbitkan KTA.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#005c8a] mt-3">
                    <span>Buka Master Anggota</span>
                    <span>&rarr;</span>
                </span>
            </a>

            {{-- Card 2: Manajemen Event BA --}}
            <a href="{{ route('admin.events.index') }}" class="group bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-[#005c8a]/50 transition flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-[#005c8a] transition">Manajemen Baitul Arqam</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Jadwal sesi, scan presensi QR, penilaian kelulusan, dan cetak sertifikat.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-800 mt-3">
                    <span>Kelola Agenda Event</span>
                    <span>&rarr;</span>
                </span>
            </a>

            {{-- Card 3: Panduan & Tutorial Sistem --}}
            <a href="{{ route('panduan') }}" class="group bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-[#005c8a]/50 transition flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm group-hover:text-[#005c8a] transition">Panduan Operasional</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Petunjuk alur kerja verifikasi, presensi, penilaian 5 aspek, dan FAQ sistem.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 mt-3">
                    <span>Buka Panduan</span>
                    <span>&rarr;</span>
                </span>
            </a>

            {{-- Card 4: Info Update & Status Sistem --}}
            <a href="{{ route('updates') }}" class="group bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs hover:border-[#005c8a]/50 transition flex flex-col justify-between">
                <div>
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <h3 class="font-bold text-slate-900 text-sm group-hover:text-[#005c8a] transition">Pusat Update Sistem</h3>
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">v2.2</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Catatan rilis fitur baru, changelog performa, keamanan, dan roadmap MPKSDI.</p>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 mt-3">
                    <span>Lihat Changelog</span>
                    <span>&rarr;</span>
                </span>
            </a>
        </div>
    </div>
</div>

<script>
function growthChartData(labels, data) {
    return {
        labels: labels,
        data: data,
        chartInstance: null,
        initChart() {
            this.$nextTick(() => {
                if (!window.Chart || !this.$refs.growthCanvas) return;
                
                const ctx = this.$refs.growthCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 240);
                gradient.addColorStop(0, 'rgba(0, 92, 138, 0.18)');
                gradient.addColorStop(1, 'rgba(0, 92, 138, 0.00)');

                this.chartInstance = new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.labels,
                        datasets: [{
                            label: 'Pendaftar Kader Baru',
                            data: this.data,
                            borderColor: '#005c8a',
                            borderWidth: 2.5,
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#005c8a',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#002b40',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyFont: { size: 12 },
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.parsed.y} kader terdaftar`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    color: '#64748b',
                                    font: { size: 11, weight: 600 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                border: { dash: [4, 4] },
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    precision: 0,
                                    color: '#64748b',
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            });
        }
    };
}

function statusChartData(counts) {
    return {
        counts: counts,
        chartInstance: null,
        initChart() {
            this.$nextTick(() => {
                if (!window.Chart || !this.$refs.statusCanvas) return;
                
                const ctx = this.$refs.statusCanvas.getContext('2d');
                const total = this.counts.reduce((a, b) => a + b, 0);

                // Warna senada dengan identitas MPKSDI: Ocean Blue (#005c8a), Gold (#f8bb00), Rose (#e11d48), Slate (#94a3b8)
                const chartData = total > 0 ? this.counts : [1];
                const chartColors = total > 0 
                    ? ['#005c8a', '#f8bb00', '#e11d48', '#94a3b8'] 
                    : ['#e2e8f0'];

                this.chartInstance = new window.Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: total > 0 ? ['Aktif', 'Menunggu', 'Ditolak', 'Nonaktif'] : ['Belum Ada Data'],
                        datasets: [{
                            data: chartData,
                            backgroundColor: chartColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: total > 0,
                                backgroundColor: '#002b40',
                                padding: 10,
                                cornerRadius: 10,
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.label}: ${context.parsed} akun`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }
    };
}
</script>
@endsection
