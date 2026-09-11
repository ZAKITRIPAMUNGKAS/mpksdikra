@extends('layouts.app')

@section('title', 'Enroll Peserta')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs max-w-2xl">
    <h1 class="text-xl font-bold text-slate-900 mb-1">Enroll Peserta</h1>
    <p class="text-xs text-slate-500 mb-5">Hanya anggota berstatus aktif yang dapat di-enroll ke kegiatan.</p>

    <form method="GET" class="flex gap-2 mb-6">
        <input name="q" value="{{ $query }}" placeholder="Cari nama atau NIK…"
            class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-mpksdi-500 transition">
        <button class="px-5 py-2.5 bg-mpksdi-900 hover:bg-mpksdi-950 text-white text-xs font-bold rounded-lg transition">Cari</button>
    </form>

    <form method="POST" action="{{ route('admin.events.enroll.store', $event) }}">
        @csrf
        <div class="space-y-2 mb-6 max-h-[28rem] overflow-y-auto pr-1">
            @forelse($kandidat as $k)
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50/30 has-[:checked]:border-mpksdi-500 has-[:checked]:bg-mpksdi-50/40 cursor-pointer transition">
                <input type="checkbox" name="anggota_ids[]" value="{{ $k->id }}" class="w-4 h-4 rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-500">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $k->nama_lengkap }}</p>
                    <p class="text-[11px] text-slate-400">{{ $k->desa_kelurahan }}, {{ $k->kecamatan }} &bull; NIK: {{ substr($k->nik, 0, 6) }}****</p>
                </div>
            </label>
            @empty
            <p class="text-xs text-slate-400 text-center py-8">Tidak ada kandidat anggota aktif yang belum terdaftar.</p>
            @endforelse
        </div>

        @if($kandidat->count())
        <button class="w-full py-3 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs rounded-lg shadow-xs transition">
            Enroll Peserta Terpilih
        </button>
        @endif
    </form>
</div>
@endsection
