@extends('layouts.guest')

@section('title', 'Verifikasi Sertifikat — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen flex flex-col justify-between py-8 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-xl mx-auto w-full flex items-center justify-between mb-4">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 min-h-11 text-xs font-bold text-slate-500 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('login') }}" class="inline-flex items-center min-h-11 text-xs font-bold text-sky-600 hover:text-sky-700 transition">Masuk Portal &rarr;</a>
    </div>

    <div class="max-w-xl mx-auto w-full">
        <div class="bg-white rounded-2xl border-2 border-slate-200 shadow-md overflow-hidden relative p-6 sm:p-8" data-verifikasi-sertifikat>
            <div class="text-center mb-6">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="w-16 h-16 mx-auto mb-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Verifikasi Sertifikat</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1.5">Kode verifikasi: <span class="font-mono font-semibold">{{ $kode ?: '-' }}</span></p>
            </div>

            @if($nilai)
                <div class="rounded-xl border-2 border-emerald-300 bg-emerald-50 p-4 sm:p-5">
                    <p class="flex items-center gap-2 text-emerald-800 font-black text-base sm:text-lg">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7"/></svg>
                        Sertifikat ASLI dan terdaftar
                    </p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nama Peserta</dt>
                            <dd class="font-bold text-slate-900 text-base">{{ $nilai->anggota->nama_lengkap ?? '-' }}</dd>
                        </div>
                        @if($nilai->anggota?->pcm)
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Cabang (PCM)</dt>
                            <dd class="font-semibold text-slate-800">{{ $nilai->anggota->pcm }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Kegiatan</dt>
                            <dd class="font-semibold text-slate-800">{{ $nilai->event->nama_event }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tanggal</dt>
                            <dd class="font-semibold text-slate-800">
                                {{ $nilai->event->tanggal_mulai?->translatedFormat('d F Y') }}@if($nilai->event->tanggal_selesai && ! $nilai->event->tanggal_selesai->isSameDay($nilai->event->tanggal_mulai)) – {{ $nilai->event->tanggal_selesai->translatedFormat('d F Y') }}@endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Status</dt>
                            <dd class="font-bold text-emerald-800">{{ $nilai->status_kelulusan }}@if($nilai->grade && $nilai->event->gunakan_grade) · Grade {{ $nilai->grade }}@endif</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-[11px] text-slate-500">Diterbitkan oleh MPKSDI PDM Karanganyar melalui Sistem Perkaderan Baitul Arqam.</p>
                </div>
            @else
                <div class="rounded-xl border-2 border-rose-300 bg-rose-50 p-4 sm:p-5">
                    <p class="flex items-center gap-2 text-rose-800 font-black text-base sm:text-lg">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M6 18L18 6M6 6l12 12"/></svg>
                        Sertifikat tidak ditemukan
                    </p>
                    <p class="mt-2 text-sm text-rose-900">Kode verifikasi tidak cocok dengan sertifikat resmi mana pun. Pastikan Anda memindai QR langsung dari sertifikat asli, atau hubungi panitia untuk konfirmasi.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="text-center mt-6 text-xs text-slate-400">&copy; {{ date('Y') }} MPKSDI PDM Karanganyar &bull; Sistem Perkaderan Baitul Arqam</div>
</div>
@endsection
