@extends('layouts.app')

@section('title', 'Dashboard Pelatihan — ' . $event->nama_event)

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'peserta') }}', modalSesi: false, editSesi: null }">

    {{-- 1. TOP BAR & BREADCRUMB --}}
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

    {{-- 2. HERO CARD (PUSAT INFORMASI KEGIATAN - BERSIH TANPA TOMBOL TERCECER) --}}
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

            {{-- Tombol Tindakan Cepat Utama --}}
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
    </div>

    {{-- 3. LIVE TRAINING MISSION CONTROL METRICS GRID --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Peserta & Demografi --}}
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

        {{-- Card 2: Progres Sesi & Agenda --}}
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
                <span class="font-medium uppercase tracking-wider">Rerata Nilai &amp; Kelulusan</span>
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($rerataNilai, 1) }}</span>
                <span class="text-xs text-slate-500">Skala 100</span>
            </div>
            <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>Lulus: <b class="text-emerald-700">{{ $totalLulus }}</b></span>
                <span>Bersyarat: <b class="text-amber-700">{{ $totalBersyarat }}</b></span>
                <span>RTL: <b>{{ $rtlSelesaiCount }}</b></span>
            </div>
        </div>
    </div>

    {{-- 4. SPOTLIGHT SESI BERLANGSUNG & LEADERBOARD TOP 5 --}}
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
                <button type="button" @click="activeTab = 'unduh_data'" class="text-xs font-semibold text-sky-600 hover:underline cursor-pointer">
                    Buka Rekapitulasi &rarr;
                </button>
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

    {{-- 5. KONTROL TABS TERPADU (BERURUTAN SESUAI KRONOLOGI PELAKSANAAN BAITUL ARQAM) --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        {{-- TAB HEADERS SESUAI URUTAN PELAKSANAAN --}}
        <div class="border-b border-slate-200 bg-slate-50/80 px-2 sm:px-4 flex gap-1 sm:gap-2 overflow-x-auto text-xs font-semibold scrollbar-none">
            {{-- Tab 1: Pendataan Peserta --}}
            <button type="button" @click="activeTab = 'peserta'"
                :class="activeTab === 'peserta' ? 'border-sky-600 text-sky-900 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'peserta' ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700'">1</span>
                <span>Pendataan Peserta</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'peserta' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-600'">{{ $totalPeserta }}</span>
            </button>

            {{-- Tab 2: Jadwal & Presensi (Absensi) --}}
            <button type="button" @click="activeTab = 'sesi'"
                :class="activeTab === 'sesi' ? 'border-sky-600 text-sky-900 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'sesi' ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700'">2</span>
                <span>Jadwal &amp; Presensi</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'sesi' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-600'">{{ $totalSesi }}</span>
            </button>

            {{-- Tab 3: Keaktifan & Outbound --}}
            <button type="button" @click="activeTab = 'outbound'"
                :class="activeTab === 'outbound' ? 'border-sky-600 text-sky-900 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'outbound' ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700'">3</span>
                <span>Keaktifan &amp; Outbound</span>
                @if($outboundKelompokCount > 0)
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">{{ $outboundKelompokCount }} Regu</span>
                @endif
            </button>

            {{-- Tab 4: Asesmen & Ujian CBT --}}
            <button type="button" @click="activeTab = 'evaluasi'"
                :class="activeTab === 'evaluasi' ? 'border-sky-600 text-sky-900 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'evaluasi' ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700'">4</span>
                <span>Asesmen &amp; CBT</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'evaluasi' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-600'">{{ $posttestSelesaiCount }}/{{ $totalPeserta }}</span>
            </button>

            {{-- Tab 5: Rencana Tindak Lanjut (RTL) --}}
            <button type="button" @click="activeTab = 'rtl'"
                :class="activeTab === 'rtl' ? 'border-sky-600 text-sky-900 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'rtl' ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700'">5</span>
                <span>Tugas Akhir RTL</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px]" :class="activeTab === 'rtl' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-600'">{{ $rtlSelesaiCount }} Terkumpul</span>
            </button>

            {{-- Tab 6: Rekapitulasi & Unduh Data --}}
            <button type="button" @click="activeTab = 'unduh_data'"
                :class="activeTab === 'unduh_data' ? 'border-emerald-600 text-emerald-950 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100/60'"
                class="py-3 px-3 sm:px-4 border-b-2 transition flex items-center gap-1.5 whitespace-nowrap cursor-pointer rounded-t-lg ml-auto">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px]" :class="activeTab === 'unduh_data' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'">6</span>
                <span class="text-emerald-700 font-bold">Rekap &amp; Unduh Data</span>
            </button>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 1: PENDATAAN PESERTA & ID CARD (LANGKAH 1)             --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'peserta'" class="p-5 sm:p-6 space-y-6">
            {{-- Header Tab 1 --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">1. Pendataan Peserta &amp; Penugasan Instruktur</h2>
                    <p class="text-xs text-slate-500 font-normal">Pendaftaran kader, penerbitan tanda peserta (ID Card), dan penetapan fasilitator pendamping.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($totalPeserta > 0)
                    <a href="{{ route('admin.events.idcards.batch', $event) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-800 text-xs font-semibold hover:bg-sky-100 transition shadow-2xs">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak ID Card Massal (A4)</span>
                    </a>
                    @endif
                    @if($canManage)
                    <a href="{{ route('admin.events.enroll', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>+ Enroll Peserta</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Peserta & Sebaran Cabang --}}
            <div class="grid sm:grid-cols-3 gap-3">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Total Kader Terdaftar</span>
                    <span class="text-lg font-bold text-slate-900">{{ $totalPeserta }} <span class="text-xs text-slate-500 font-normal">/ {{ $event->kuota ?: '∞' }} Kuota</span></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Komposisi Gender</span>
                    <span class="text-lg font-bold text-slate-900">L: {{ $totalLaki }} <span class="text-slate-400">|</span> P: {{ $totalPerempuan }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Sebaran PCM (Cabang)</span>
                    <span class="text-lg font-bold text-sky-700">{{ $pcmCounts->count() }} Cabang Asal</span>
                </div>
            </div>

            {{-- List Peserta Terdaftar --}}
            <div>
                <h3 class="font-bold text-slate-900 text-sm mb-3">Daftar Kader Terdaftar ({{ $totalPeserta }})</h3>
                <div class="space-y-2 max-h-[30rem] overflow-y-auto pr-1">
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
                                <p class="text-xs text-slate-500">{{ $p->anggota->pcm ?: ($p->anggota->kecamatan ?: '-') }} &bull; NBM: {{ $p->anggota->nbm ?: 'Belum Ada' }} &bull; NIK: {{ $p->anggota->nik ?: '-' }}</p>
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
                    <div class="text-center py-10 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                        Belum ada peserta yang terdaftar pada kegiatan ini. Klik tombol <b>+ Enroll Peserta</b> di atas untuk menambahkan kader.
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Seksi Penugasan Fasilitator --}}
            @if($canManage)
            <div class="pt-6 border-t border-slate-100">
                <div class="pb-3">
                    <h3 class="font-bold text-slate-900 text-sm tracking-tight">Penugasan Fasilitator &amp; Instruktur Kegiatan</h3>
                    <p class="text-xs text-slate-500">Centang instruktur/fasilitator yang bertugas mengoperasikan scanner presensi dan menilai praktik peserta di event ini.</p>
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
                        <div class="sm:col-span-2 py-4 text-center text-slate-400 text-xs font-normal">
                            Belum ada akun dengan role fasilitator terdaftar di sistem.
                        </div>
                        @endforelse
                    </div>

                    @if($fasilitatorOptions->count())
                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                            Simpan Penugasan Fasilitator
                        </button>
                    </div>
                    @endif
                </form>
            </div>
            @endif
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 2: JADWAL SESI & PRESENSI (LANGKAH 2)                  --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'sesi'" class="p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">2. Jadwal Acara, Berkas Modul &amp; Presensi (Absensi)</h2>
                    <p class="text-xs text-slate-500">Agenda materi pelatihan, narasumber, berkas modul presentasi, dan pemindaian presensi QR.</p>
                </div>
                @if($canManage)
                <button type="button" @click="modalSesi = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold shadow-2xs transition">
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
                <div class="text-center py-10 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                    Belum ada sesi agenda yang dijadwalkan. Klik tombol <b>+ Tambah Sesi Agenda</b> di atas untuk menambahkan.
                </div>
                @endforelse
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 3: KEAKTIFAN & OUTBOUND (LANGKAH 3)                    --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'outbound'" class="p-5 sm:p-6 space-y-6">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">3. Dinamika Kelompok Outbound &amp; Rekam Keaktifan Forum</h2>
                <p class="text-xs text-slate-500 font-normal">Pembagian regu outbound yang berimbang dan pencatatan keikutsertaan aktif kader di setiap sesi perkaderan.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Panel 1: Dinamika Kelompok Outbound --}}
                <div class="bg-slate-50/60 rounded-xl border border-slate-200 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-900 border border-amber-200 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Regu Lapangan Outbound</span>
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ $outboundKelompokCount }} Kelompok Terbentuk</span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sistem membagi kader ke dalam regu outbound secara otomatis dengan menyeimbangkan rasio gender (putra/putri) dan asal cabang PCM untuk membangun ukhuwah persyarikatan.
                    </p>

                    <div class="pt-2 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.events.outbound.index', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Kelola &amp; Acak Kelompok</span>
                        </a>
                        @if($outboundKelompokCount > 0)
                        <a href="{{ route('admin.events.outbound.pdf', $event) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Cetak PDF Lembar Outbound</span>
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Panel 2: Poin Keaktifan Peserta --}}
                <div class="bg-slate-50/60 rounded-xl border border-slate-200 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-sky-50 text-sky-900 border border-sky-200 text-xs font-semibold">
                            <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span>Poin Keaktifan Forum</span>
                        </span>
                        <span class="text-xs font-bold text-slate-700">{{ $keaktifanCount }} Aksi Dicatat</span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Fasilitator mencatat keikutsertaan aktif kader di setiap sesi: bertanya, menjawab, kultum, imam, adzan, dan prestasi regu outbound (1 poin/aksi, terkap bonus maks 10 poin).
                    </p>

                    <div class="pt-2">
                        <a href="{{ route('admin.events.keaktifan', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Buka Pencatatan Poin Keaktifan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 4: ASESMEN & UJIAN CBT (LANGKAH 4)                     --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'evaluasi'" class="p-5 sm:p-6 space-y-6">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">4. Asesmen Kompetensi (Kognitif, Praktik Psikomotor &amp; Afektif)</h2>
                <p class="text-xs text-slate-500 font-normal">Evaluasi penguasaan materi (Pretest &amp; Posttest), penilaian praktik ibadah/akhlak lapangan, dan angket evaluasi.</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- 1. Pretest CBT --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-900">Pretest Kognitif</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sesiPretest?->status === 'sedang_berlangsung' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $sesiPretest?->status === 'sedang_berlangsung' ? 'Aktif' : 'Tutup' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $soalPretestCount }} Soal &bull; Selesai: <b>{{ $pretestSelesaiCount }}/{{ $totalPeserta }}</b></p>
                    </div>
                    <a href="{{ route('admin.events.soal.index', [$event, 'pretest']) }}" class="w-full text-center py-2 px-3 bg-white hover:bg-sky-50 text-sky-800 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                        Kelola Soal Pretest &rarr;
                    </a>
                </div>

                {{-- 2. Posttest CBT --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-900">Posttest Kognitif</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $sesiPosttest?->status === 'sedang_berlangsung' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $sesiPosttest?->status === 'sedang_berlangsung' ? 'Aktif' : 'Tutup' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $soalPosttestCount }} Soal &bull; Selesai: <b>{{ $posttestSelesaiCount }}/{{ $totalPeserta }}</b></p>
                    </div>
                    <a href="{{ route('admin.events.soal.index', [$event, 'posttest']) }}" class="w-full text-center py-2 px-3 bg-white hover:bg-sky-50 text-sky-800 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                        Kelola Soal Posttest &rarr;
                    </a>
                </div>

                {{-- 3. Praktik Psikomotor --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-900">Praktik Psikomotor</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                {{ $psikomotorTemplateCount }} Rubrik
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">Sholat, Wudhu, Qur'an &bull; Dinilai: <b>{{ $psikomotorDinilaiCount }}/{{ $totalPeserta }}</b></p>
                    </div>
                    <a href="{{ route('admin.events.psikomotor', $event) }}" class="w-full text-center py-2 px-3 bg-white hover:bg-sky-50 text-sky-800 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                        Input Nilai Praktik &rarr;
                    </a>
                </div>

                {{-- 4. Afektif & Angket --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-slate-900">Afektif &amp; Angket</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-100 text-sky-800">
                                Sikap &amp; Layanan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500">Survei Sikap &bull; Mengisi: <b>{{ $afektifSelesaiCount }}/{{ $totalPeserta }}</b></p>
                    </div>
                    <div class="flex gap-1.5">
                        <a href="{{ route('admin.events.afektif', $event) }}" class="w-1/2 text-center py-2 px-1 bg-white hover:bg-sky-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                            Afektif
                        </a>
                        <a href="{{ route('admin.events.angket', $event) }}" class="w-1/2 text-center py-2 px-1 bg-white hover:bg-sky-50 text-slate-700 border border-slate-200 rounded-lg text-xs font-semibold transition shadow-2xs">
                            Angket
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 5: TUGAS AKHIR RTL (LANGKAH 5)                         --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'rtl'" class="p-5 sm:p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">5. Penugasan Rencana Tindak Lanjut (RTL)</h2>
                    <p class="text-xs text-slate-500 font-normal">Monitoring penugasan rencana aksi paska pelatihan untuk mengabdi di Pimpinan Cabang, Ranting, atau AUM.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    @if($canManage)
                    <a href="{{ route('admin.events.rtl.index', $event) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
                        <span>Kelola Pertanyaan &amp; Review RTL</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Ringkasan Pengumpulan RTL --}}
            <div class="grid sm:grid-cols-3 gap-3">
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Total RTL Terkumpul</span>
                    <span class="text-lg font-bold text-slate-900">{{ $rtlSelesaiCount }} <span class="text-xs text-slate-500 font-normal">/ {{ $totalPeserta }} Peserta</span></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Belum Mengumpulkan</span>
                    <span class="text-lg font-bold text-amber-700">{{ max(0, $totalPeserta - $rtlSelesaiCount) }} Kader</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/70">
                    <span class="text-xs text-slate-500 block">Instrumen Pertanyaan</span>
                    <span class="text-lg font-bold text-sky-700">{{ $rtlSoalCount }} Butir RTL</span>
                </div>
            </div>

            {{-- Antrean Penyerahan RTL Terbaru --}}
            <div>
                <h3 class="font-bold text-slate-900 text-sm mb-3">Penyerahan Berkas RTL Terbaru</h3>
                <div class="space-y-2">
                    @forelse($recentRtls as $rtlItem)
                    <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-white transition">
                        <div class="min-w-0">
                            <p class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $rtlItem->anggota->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $rtlItem->anggota->pcm ?: ($rtlItem->anggota->kecamatan ?: '-') }} &bull; Tanggal kirim: {{ $rtlItem->updated_at?->format('d/m/Y H:i') }} WIB</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                {{ $rtlItem->status === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $rtlItem->status }}
                            </span>
                            @if($canManage)
                            <a href="{{ route('admin.events.rtl.show', [$event, $rtlItem]) }}" class="px-2.5 py-1 rounded-md border border-slate-200 hover:bg-sky-50 text-xs font-semibold text-sky-800 transition">
                                Tinjau
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-slate-400 text-xs border border-dashed border-slate-200 rounded-xl">
                        Belum ada peserta yang mengunggah tugas RTL.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- PAGE TAB 6: REKAPITULASI & UNDUH DATA (LANGKAH 6)               --}}
        {{-- ============================================================== --}}
        <div x-show="activeTab === 'unduh_data'" class="p-5 sm:p-6 space-y-6">
            <div class="pb-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-base sm:text-lg tracking-tight">6. Rekapitulasi Akhir, Kelulusan &amp; Unduh Dokumen Resmi</h2>
                <p class="text-xs text-slate-500 font-normal">Pengolahan nilai akhir terpadu, penetapan status kelulusan peserta, dan penerbitan dokumen resmi berkop MPKSDI.</p>
            </div>

            {{-- 4 Kartu Unduhan Utama --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Cetak Semua Sertifikat Massal --}}
                <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/40 flex flex-col justify-between space-y-3">
                    <div>
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-indigo-900">
                            <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            <span>Sertifikat Resmi 2 Sisi</span>
                        </span>
                        <p class="text-[11px] text-slate-600 mt-1">Unduh seluruh sertifikat kader lulus sekaligus dalam 1 PDF (Hal 1: Sertifikat QR; Hal 2: Transkrip JP).</p>
                    </div>
                    @if($event->status === 'selesai')
                    <a href="{{ route('admin.events.certificates.batch', $event) }}?download=1" class="w-full text-center py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                        Unduh Batch Sertifikat (PDF)
                    </a>
                    @else
                    <span class="w-full text-center py-1.5 px-3 bg-slate-200 text-slate-500 rounded-lg text-xs font-semibold block cursor-not-allowed">
                        Tersedia Saat Status Selesai
                    </span>
                    @endif
                </div>

                {{-- Laporan Evaluasi PDF --}}
                <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/40 flex flex-col justify-between space-y-3">
                    <div>
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-sky-900">
                            <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Laporan Evaluasi (PDF)</span>
                        </span>
                        <p class="text-[11px] text-slate-600 mt-1">Laporan resmi berkop PDM Karanganyar, metadata, rekapitulasi 14 kolom, &amp; lembar tanda tangan.</p>
                    </div>
                    <a href="{{ route('admin.events.report', $event) }}" class="w-full text-center py-2 px-3 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                        Unduh Laporan PDF
                    </a>
                </div>

                {{-- Ekspor Excel --}}
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 flex flex-col justify-between space-y-3">
                    <div>
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-900">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Ekspor Data Lengkap (Excel)</span>
                        </span>
                        <p class="text-[11px] text-slate-600 mt-1">Spreadsheet XLSX terformat rapi memuat rincian aspek nilai, presensi %, NA, dan ranking.</p>
                    </div>
                    <a href="{{ route('admin.events.exportExcel', $event) }}" class="w-full text-center py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                        Unduh Excel (XLSX)
                    </a>
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
                    <a href="{{ route('admin.events.ranking', $event) }}" class="w-full text-center py-2 px-3 bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 rounded-lg text-xs font-semibold transition shadow-2xs">
                        Buka Halaman Ranking &rarr;
                    </a>
                </div>
            </div>

            {{-- Pratinjau Tabel Peringkat (Top 10 Peserta) --}}
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Pratinjau Hasil Kelulusan &amp; Nilai Akhir (Top 10)</h3>
                    <a href="{{ route('admin.events.ranking', $event) }}" class="text-xs font-semibold text-sky-600 hover:underline">
                        Lihat Seluruh Peserta &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Nama Kader</th>
                                <th class="py-2.5 px-3">Cabang (PCM)</th>
                                <th class="py-2.5 px-2 text-center">Posttest</th>
                                <th class="py-2.5 px-2 text-center">Afektif</th>
                                <th class="py-2.5 px-2 text-center">Psikomotor</th>
                                <th class="py-2.5 px-2 text-center">Presensi</th>
                                <th class="py-2.5 px-3 text-center">NA</th>
                                <th class="py-2.5 px-3">Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rankingPreview as $r)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $r->ranking ?? '-' }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-900">{{ $r->anggota->nama_lengkap }}</td>
                                <td class="py-2.5 px-3 text-slate-600">{{ $r->anggota->pcm ?: ($r->anggota->kecamatan ?: '-') }}</td>
                                <td class="py-2.5 px-2 text-center font-mono">{{ number_format($r->nilai_posttest, 1) }}</td>
                                <td class="py-2.5 px-2 text-center font-mono">{{ number_format($r->nilai_afektif, 1) }}</td>
                                <td class="py-2.5 px-2 text-center font-mono">{{ number_format($r->nilai_psikomotor, 1) }}</td>
                                <td class="py-2.5 px-2 text-center font-mono">{{ number_format($r->nilai_kehadiran, 1) }}%</td>
                                <td class="py-2.5 px-3 text-center font-bold font-mono text-sky-800">{{ number_format($r->nilai_akhir, 2) }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider
                                        {{ $r->status_kelulusan === 'Lulus' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : '' }}
                                        {{ $r->status_kelulusan === 'Lulus Bersyarat' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                                        {{ $r->status_kelulusan === 'Tidak Lulus' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                                        {{ $r->status_kelulusan ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400 text-xs">Belum ada data nilai akhir yang terhitung.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
