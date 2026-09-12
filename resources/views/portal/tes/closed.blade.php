@extends('layouts.app')

@section('title', ucfirst($tipe) . ' Belum Dibuka')

@section('content')
<div class="bg-white rounded-xl border border-slate-200/90 p-8 sm:p-10 text-center max-w-lg mx-auto shadow-2xs my-8">
    <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600 shadow-2xs">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-amber-50 border border-amber-200/80 text-xs font-semibold text-amber-800 uppercase tracking-wider mb-2">
        Sesi Ditutup / Terkunci
    </span>
    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-2">{{ ucfirst($tipe) }} Belum Dibuka</h1>
    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-sm mx-auto font-normal">Sesi ujian {{ $tipe }} untuk <strong class="text-slate-700">{{ $event->nama_event }}</strong> belum dibuka atau sudah resmi ditutup oleh instruktur perkaderan.</p>
    <div class="mt-6 pt-5 border-t border-slate-100">
        <a href="{{ route('portal.tes.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-2xs transition">
            <span>&larr; Kembali ke Daftar Ujian</span>
        </a>
    </div>
</div>
@endsection
