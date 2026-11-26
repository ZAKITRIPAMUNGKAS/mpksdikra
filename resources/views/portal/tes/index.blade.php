@extends('layouts.app')

@section('title', 'Ujian Kognitif — Pretest & Posttest')

@section('content')
<div class="space-y-4 sm:space-y-6 max-w-4xl mx-auto">
    {{-- Compact Back + Title Header --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('portal.dashboard') }}" class="min-h-11 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white border border-slate-200/90 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-28 h-28 sm:w-36 sm:h-36 text-[#005c8a] opacity-[0.16]" />
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-[11px] font-semibold text-sky-800 uppercase tracking-wider mb-1.5">
                Evaluasi Kognitif CBT
            </span>
            <h1 class="text-lg sm:text-2xl font-bold text-slate-900 tracking-tight">Pretest &amp; Posttest Perkaderan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 font-normal">Evaluasi pemahaman materi ideologi, kemuhammadiyahan, dan kepemimpinan.</p>
        </div>
    </div>

    <div class="space-y-3.5 sm:space-y-4">
        @forelse($events as $ep)
        @php
            $pre = $ep->tes_status['pretest'] ?? ['aktif' => false, 'selesai' => false, 'nilai' => null];
            $post = $ep->tes_status['posttest'] ?? ['aktif' => false, 'selesai' => false, 'nilai' => null];
        @endphp
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-6 shadow-2xs hover:border-sky-300 transition duration-200 space-y-4">
            <div>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">{{ $ep->event->nama_event }}</h2>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-normal">
                    <svg class="w-3.5 h-3.5 text-sky-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $ep->event->tanggal_mulai->format('d M Y') }} &mdash; {{ $ep->event->tanggal_selesai->format('d M Y') }}</span>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @foreach(['pretest' => ['label' => 'Pretest', 'data' => $pre, 'btn' => 'bg-sky-600 hover:bg-sky-700'], 'posttest' => ['label' => 'Posttest', 'data' => $post, 'btn' => 'bg-[#005c8a] hover:bg-[#00476b]']] as $tipe => $cfg)
                    @if($cfg['data']['selesai'])
                    <div class="min-h-12 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                        <div>
                            <span class="text-xs font-bold block">{{ $cfg['label'] }} Selesai</span>
                            @if($cfg['data']['nilai'] !== null)
                            <span class="text-[11px] text-emerald-700 font-semibold">Nilai: {{ number_format((float) $cfg['data']['nilai'], 0) }}</span>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-bold">✓ Selesai</span>
                    </div>
                    @else
                    <a href="{{ route('portal.tes.show', [$ep->event, $tipe]) }}"
                       class="min-h-12 flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl text-white text-xs sm:text-sm font-bold shadow-2xs transition {{ $cfg['data']['aktif'] ? $cfg['btn'] : 'bg-slate-600 hover:bg-slate-700' }}">
                        <span>Kerjakan {{ $cfg['label'] }}</span>
                        <span class="px-2 py-0.5 rounded-md bg-white/15 text-[10px] font-semibold">
                            {{ $cfg['data']['aktif'] ? 'Dibuka →' : 'Belum Dibuka' }}
                        </span>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-400 text-sm shadow-2xs">
            <div class="w-12 h-12 rounded-xl bg-sky-50 flex items-center justify-center mx-auto mb-3 text-sky-600 border border-sky-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="font-bold text-slate-700 text-base mb-1">Belum Ada Ujian Kognitif</h3>
            <p class="text-xs text-slate-500">Anda belum terdaftar dalam kegiatan Baitul Arqam dengan jadwal ujian aktif.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
