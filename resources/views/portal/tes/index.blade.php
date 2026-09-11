@extends('layouts.app')

@section('title', 'Ujian Kognitif — Pretest & Posttest')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    {{-- Header Banner with Batik Corner --}}
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
        
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-sky-50 border border-sky-200 text-[11px] font-bold text-sky-800 uppercase tracking-wider mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                Evaluasi Kognitif CBT
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pretest &amp; Posttest Perkaderan<span class="text-sky-500">.</span></h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Evaluasi pemahaman materi ideologi, kemuhammadiyahan, dan kepemimpinan persyarikatan.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($events as $ep)
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition duration-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-extrabold text-slate-900 text-lg leading-snug">{{ $ep->event->nama_event }}</h2>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $ep->event->tanggal_mulai->format('d M Y') }} &mdash; {{ $ep->event->tanggal_selesai->format('d M Y') }}</span>
                    </p>
                </div>
                <div class="flex gap-2.5 flex-wrap">
                    <a href="{{ route('portal.tes.show', [$ep->event, 'pretest']) }}" class="px-5 py-2.5 rounded-full bg-sky-500 hover:bg-sky-600 text-white text-xs sm:text-sm font-bold shadow-xs transition hover:scale-[1.01]">
                        Kerjakan Pretest
                    </a>
                    <a href="{{ route('portal.tes.show', [$ep->event, 'posttest']) }}" class="px-5 py-2.5 rounded-full bg-[#f8bb00] hover:bg-amber-400 text-slate-950 text-xs sm:text-sm font-extrabold shadow-xs transition hover:scale-[1.01] border border-amber-400">
                        Kerjakan Posttest
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center text-slate-400 text-sm shadow-xs">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center mx-auto mb-3 text-sky-600">
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
