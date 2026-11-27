@extends('layouts.app')

@section('title', 'Portal Anggota')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- User Profile Compact Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-28 h-28 sm:w-36 sm:h-36 text-[#005c8a] opacity-[0.15]" />
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 relative z-10">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                @if(auth()->user()->anggota)
                    <div class="w-12 h-16 sm:w-16 sm:h-[85px] aspect-[3/4] rounded-xl overflow-hidden border-2 border-sky-400 shadow-xs shrink-0 bg-slate-100">
                        <img src="{{ route('foto.anggota', auth()->user()->anggota) }}" 
                             alt="Pasfoto {{ auth()->user()->name }}" 
                             class="w-full h-full object-cover object-top"
                             loading="eager">
                    </div>
                @else
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-slate-950 to-[#005c8a] border border-sky-400/80 flex items-center justify-center text-[#f8bb00] text-lg sm:text-xl font-bold shadow-2xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                        <h1 class="text-base sm:text-2xl font-bold text-slate-900 tracking-tight truncate">{{ auth()->user()->name }}</h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] sm:text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Terverifikasi
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5 truncate">Pangkalan Data Kader &bull; MPKSDI Karanganyar</p>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:flex items-center gap-2 shrink-0">
                <a href="{{ route('portal.profile.cetak-pdf') }}" target="_blank" class="min-h-11 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-sky-300 bg-sky-50 text-xs font-semibold text-sky-800 hover:bg-sky-100 transition shadow-2xs">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak CV</span>
                </a>
                <a href="{{ route('portal.profile.index') }}" class="min-h-11 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200/90 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-sky-800 transition shadow-2xs">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Kelola Profil</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Events Section --}}
    <div>
        <div class="flex items-center justify-between mb-3 sm:mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Kegiatan Baitul Arqam Saya</h2>
                <p class="text-xs text-slate-500">Pilih kartu kegiatan untuk QR presensi, tes, &amp; tugas evaluasi</p>
            </div>
        </div>

        <div class="grid gap-4 sm:gap-5 sm:grid-cols-2">
            @forelse($events as $ep)
            @php
                $doneTasks = (int) $ep->progress['pretest']
                    + (int) $ep->progress['posttest']
                    + (int) ($ep->progress['afektif'] ?? false)
                    + (int) ($ep->progress['angket'] ?? false)
                    + (int) $ep->progress['rtl'];
                $totalSesi = (int) ($ep->progress['total_sesi'] ?? 0);
                $hadirCount = (int) ($ep->progress['hadir_count'] ?? 0);
            @endphp
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs hover:border-sky-300 transition duration-200 p-4 sm:p-6 flex flex-col justify-between gap-4">
                <div>
                    <div class="flex items-start justify-between gap-2.5 mb-2">
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">{{ $ep->event->nama_event }}</h3>
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold shrink-0 uppercase tracking-wide
                            {{ $ep->event->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-300/80' : '' }}
                            {{ $ep->event->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-300/80' : '' }}
                            {{ $ep->event->status === 'selesai' ? 'bg-slate-100 text-slate-700 border border-slate-200' : '' }}">
                            {{ $ep->event->status }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 mb-3.5">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $ep->event->tanggal_mulai->format('d M Y') }} &mdash; {{ $ep->event->tanggal_selesai->format('d M Y') }}</span>
                        </span>
                        @if($totalSesi > 0)
                        <span class="inline-flex items-center gap-1 text-slate-600 font-medium">
                            &bull; Hadir <strong class="text-slate-800">{{ $hadirCount }}/{{ $totalSesi }}</strong> Sesi
                        </span>
                        @endif
                    </div>

                    {{-- Overall Progress Bar --}}
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-semibold text-slate-700">Progres Tugas &amp; Evaluasi</span>
                            <span class="font-bold text-[#005c8a]">{{ $doneTasks }}/5 Selesai</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden mb-2">
                            <div class="h-full rounded-full bg-sky-600 transition-all" style="width: {{ round(($doneTasks / 5) * 100) }}%"></div>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $ep->progress['pretest'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-white text-slate-500 border border-slate-200' }}">
                                {{ $ep->progress['pretest'] ? '✓ Pretest Selesai' : 'Pretest Belum' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $ep->progress['posttest'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-white text-slate-500 border border-slate-200' }}">
                                {{ $ep->progress['posttest'] ? '✓ Posttest Selesai' : 'Posttest Belum' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium {{ $ep->progress['rtl'] ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80' : 'bg-white text-slate-500 border border-slate-200' }}">
                                {{ $ep->progress['rtl'] ? '✓ RTL Terkirim' : 'RTL Belum' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Ergonomic Thumb-Friendly Action Stack --}}
                <div class="space-y-2.5">
                    {{-- Primary CTA: QR & Jadwal Sesi --}}
                    <a href="{{ route('portal.kehadiran.index', $ep->event) }}"
                       class="min-h-12 w-full flex items-center justify-between px-4 py-2.5 rounded-xl bg-[#005c8a] hover:bg-[#004a70] active:bg-[#003b5a] text-white font-bold text-sm shadow-xs transition">
                        <span class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-sky-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>ID Card QR &amp; Jadwal Sesi</span>
                        </span>
                        <span class="text-sky-200 text-xs font-semibold">&rarr;</span>
                    </a>

                    {{-- Checklist Menu Tugas --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('portal.tes.index') }}" class="min-h-12 flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-sky-50/60 hover:border-sky-300 font-semibold text-slate-800 transition shadow-2xs">
                            <span class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span class="truncate">Tes Kognitif</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ ($ep->progress['pretest'] && $ep->progress['posttest']) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                {{ ($ep->progress['pretest'] && $ep->progress['posttest']) ? 'Selesai' : (($ep->progress['pretest'] || $ep->progress['posttest']) ? '1/2' : 'Belum') }}
                            </span>
                        </a>

                        <a href="{{ route('portal.afektif.index', $ep->event) }}" class="min-h-12 flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-sky-50/60 hover:border-sky-300 font-semibold text-slate-800 transition shadow-2xs">
                            <span class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="truncate">Afektif</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ ($ep->progress['afektif'] ?? false) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                {{ ($ep->progress['afektif'] ?? false) ? '✓ Sudah' : 'Belum' }}
                            </span>
                        </a>

                        <a href="{{ route('portal.angket.index', $ep->event) }}" class="min-h-12 flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-sky-50/60 hover:border-sky-300 font-semibold text-slate-800 transition shadow-2xs">
                            <span class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="truncate">Angket Evaluasi</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ ($ep->progress['angket'] ?? false) ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                {{ ($ep->progress['angket'] ?? false) ? '✓ Sudah' : 'Belum' }}
                            </span>
                        </a>

                        <a href="{{ route('portal.rtl.index', $ep->event) }}" class="min-h-12 flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200/90 bg-white hover:bg-sky-50/60 hover:border-sky-300 font-semibold text-slate-800 transition shadow-2xs">
                            <span class="flex items-center gap-2 min-w-0">
                                <svg class="w-4 h-4 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span class="truncate">Rencana Tindak Lanjut</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold shrink-0 {{ $ep->progress['rtl'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                {{ $ep->progress['rtl'] ? '✓ Terkirim' : (($ep->event->rtl_wajib ?? true) ? 'Belum' : 'Opsional') }}
                            </span>
                        </a>
                    </div>

                    @if($ep->event->status === 'selesai')
                    <a href="{{ route('portal.sertifikat.download', $ep->event) }}" class="min-h-12 w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs sm:text-sm shadow-xs transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh Sertifikat Kelulusan</span>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-400 sm:col-span-2 shadow-2xs">
                <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center mx-auto mb-3 text-sky-600 border border-sky-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-slate-700 text-base mb-1">Belum Terdaftar di Kegiatan</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Anda belum di-enroll pada kegiatan Baitul Arqam aktif. Panitia akan menambahkan Anda berdasarkan jadwal perkaderan cabang/daerah.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
