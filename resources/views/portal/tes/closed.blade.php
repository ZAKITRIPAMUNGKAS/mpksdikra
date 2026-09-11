@extends('layouts.app')

@section('title', ucfirst($tipe) . ' Belum Dibuka')

@section('content')
<div class="bg-white rounded-3xl border border-slate-200/80 p-8 sm:p-12 text-center max-w-lg mx-auto shadow-xs card-lift-sm my-8">
    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600 shadow-2xs">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-2">
        Sesi Ditutup / Terkunci
    </span>
    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mb-2">{{ ucfirst($tipe) }} Belum Dibuka</h1>
    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-sm mx-auto">Sesi ujian {{ $tipe }} untuk <strong class="text-slate-700">{{ $event->nama_event }}</strong> belum dibuka atau sudah resmi ditutup oleh instruktur perkaderan.</p>
    <div class="mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('portal.tes.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-sky-500 hover:bg-sky-600 text-white text-xs sm:text-sm font-bold shadow-xs transition hover:scale-[1.01]">
            <span>&larr; Kembali ke Daftar Ujian</span>
        </a>
    </div>
</div>
@endsection
