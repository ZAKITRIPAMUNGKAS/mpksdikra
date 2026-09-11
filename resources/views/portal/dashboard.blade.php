@extends('layouts.app')

@section('title', 'Portal Anggota')

@section('content')
<div class="space-y-6">
    {{-- User Profile Welcome Card --}}
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-mpksdi-900 border border-mpksdi-700 flex items-center justify-center text-gold-400 text-2xl font-black shadow-2xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ auth()->user()->name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-300">
                            Kader Terverifikasi
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Pangkalan Data Kader &bull; MPKSDI PDM Karanganyar</p>
                </div>
            </div>
            <div>
                <a href="{{ route('portal.profile.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-mpksdi-600 hover:text-mpksdi-900 transition shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>Biodata Kader</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Events Section --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Kegiatan Baitul Arqam Saya</h2>
                <p class="text-xs text-slate-500">Daftar perkaderan yang sedang atau pernah Anda ikuti</p>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            @forelse($events as $ep)
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs hover:border-mpksdi-400 transition duration-150 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <h3 class="font-bold text-slate-900 text-base leading-snug">{{ $ep->event->nama_event }}</h3>
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold shrink-0 uppercase tracking-wide
                            {{ $ep->event->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-300' : '' }}
                            {{ $ep->event->status === 'berlangsung' ? 'bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-300' : '' }}
                            {{ $ep->event->status === 'selesai' ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                            {{ $ep->event->status }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $ep->event->tanggal_mulai->format('d M Y') }} &mdash; {{ $ep->event->tanggal_selesai->format('d M Y') }}</span>
                    </div>

                    {{-- Progress Chips --}}
                    <div class="mb-5 pb-4 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-700 block mb-2">Progres Tugas & Evaluasi:</span>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $ep->progress['pretest'] ? 'bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-200' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $ep->progress['pretest'] ? 'bg-gold-500' : 'bg-slate-400' }}"></span>
                                Pretest {{ $ep->progress['pretest'] ? 'Selesai' : 'Belum' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $ep->progress['posttest'] ? 'bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-200' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $ep->progress['posttest'] ? 'bg-gold-500' : 'bg-slate-400' }}"></span>
                                Posttest {{ $ep->progress['posttest'] ? 'Selesai' : 'Belum' }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium {{ $ep->progress['rtl'] ? 'bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-200' : 'bg-slate-100 text-slate-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $ep->progress['rtl'] ? 'bg-gold-500' : 'bg-slate-400' }}"></span>
                                RTL {{ $ep->progress['rtl'] ? 'Terkirim' : 'Belum' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Grid --}}
                <div class="space-y-2">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <a href="{{ route('portal.kehadiran.index', $ep->event) }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-mpksdi-50 hover:border-mpksdi-400 hover:text-mpksdi-900 font-bold text-slate-700 transition">
                            <svg class="w-4 h-4 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <span>ID Card & Sesi</span>
                        </a>
                        <a href="{{ route('portal.tes.index') }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-mpksdi-50 hover:border-mpksdi-400 hover:text-mpksdi-900 font-bold text-slate-700 transition">
                            <svg class="w-4 h-4 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span>Tes Kognitif</span>
                        </a>
                        <a href="{{ route('portal.afektif.index', $ep->event) }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-mpksdi-50 hover:border-mpksdi-400 hover:text-mpksdi-900 font-bold text-slate-700 transition">
                            <svg class="w-4 h-4 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Afektif</span>
                        </a>
                        <a href="{{ route('portal.angket.index', $ep->event) }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-slate-200 bg-slate-50/70 hover:bg-mpksdi-50 hover:border-mpksdi-400 hover:text-mpksdi-900 font-bold text-slate-700 transition">
                            <svg class="w-4 h-4 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Angket Evaluasi</span>
                        </a>
                    </div>

                    <a href="{{ route('portal.rtl.index', $ep->event) }}" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-mpksdi-50 hover:border-mpksdi-400 hover:text-mpksdi-900 font-bold text-xs text-slate-700 transition">
                        <svg class="w-4 h-4 text-mpksdi-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Rencana Tindak Lanjut (RTL)</span>
                    </a>

                    @if($ep->event->status === 'selesai')
                    <a href="{{ route('portal.sertifikat.download', $ep->event) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gold-400 hover:bg-gold-500 text-mpksdi-950 font-extrabold text-xs shadow-2xs transition border border-gold-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh Sertifikat Kelulusan</span>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-400 sm:col-span-2 shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
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
