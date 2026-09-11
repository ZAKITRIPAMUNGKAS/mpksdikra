@extends('layouts.app')

@section('title', 'Enroll Peserta')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs max-w-2xl card-lift-sm relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
            Registrasi Peserta
        </span>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight mb-1">Enroll Peserta Kegiatan</h1>
        <p class="text-xs text-slate-500 mb-5 font-medium">Hanya anggota berstatus aktif yang dapat di-enroll ke kegiatan.</p>

        <form method="GET" class="flex gap-2 mb-6">
            <input name="q" value="{{ $query }}" placeholder="Cari nama atau NIK…"
                class="flex-1 px-4 py-2.5 bg-slate-50/60 border border-slate-200 rounded-xl text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
            <button class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-xs font-bold rounded-full transition shadow-2xs">Cari</button>
        </form>

        <form method="POST" action="{{ route('admin.events.enroll.store', $event) }}">
            @csrf
            <div class="space-y-2 mb-6 max-h-[28rem] overflow-y-auto pr-1">
                @forelse($kandidat as $k)
                <label class="flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:border-sky-400 hover:bg-white has-[:checked]:border-sky-500 has-[:checked]:bg-sky-50/40 cursor-pointer transition">
                    <input type="checkbox" name="anggota_ids[]" value="{{ $k->id }}" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $k->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-400 font-medium">{{ $k->desa_kelurahan }}, {{ $k->kecamatan }} &bull; NIK: {{ substr($k->nik, 0, 6) }}****</p>
                    </div>
                </label>
                @empty
                <p class="text-xs text-slate-400 text-center py-8">Tidak ada kandidat anggota aktif yang belum terdaftar.</p>
                @endforelse
            </div>

            @if($kandidat->count())
            <button class="w-full py-3 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white font-black text-xs rounded-full shadow-xs transition hover:scale-[1.01]">
                Enroll Peserta Terpilih
            </button>
            @endif
        </form>
    </div>
</div>
@endsection
