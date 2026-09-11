@extends('layouts.app')

@section('title', 'RTL — ' . $rtl->anggota->nama_lengkap)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.rtl.index', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar RTL
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs mb-6 max-w-3xl">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $rtl->anggota->nama_lengkap }}</h1>
            <p class="text-xs text-slate-500 mt-1">Status: <b class="uppercase font-semibold text-slate-800">{{ $rtl->status }}</b> &bull; Dikirim: {{ $rtl->updated_at->format('d M Y H:i') }}</p>
        </div>
        @if($rtl->status === 'terkirim')
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.events.rtl.review', [$event, $rtl]) }}">
                @csrf
                <input type="hidden" name="status" value="disetujui">
                <button class="px-5 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">Setujui RTL</button>
            </form>
            <form method="POST" action="{{ route('admin.events.rtl.review', [$event, $rtl]) }}">
                @csrf
                <input type="hidden" name="status" value="ditolak">
                <button class="px-5 py-2.5 border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold rounded-lg transition">Tolak RTL</button>
            </form>
        </div>
        @endif
    </div>
</div>

<div class="space-y-4 max-w-3xl">
    @foreach($rtl->jawaban->sortBy(fn($j) => $j->soal->urutan ?? 0) as $j)
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <p class="font-bold text-slate-900 text-xs sm:text-sm mb-3">{{ $j->soal->pertanyaan }}</p>
        @if($j->soal->tipe === 'essay')
            <p class="text-xs sm:text-sm text-slate-700 whitespace-pre-wrap leading-relaxed p-4 rounded-lg bg-slate-50 border border-slate-100">{{ $j->jawaban }}</p>
        @else
            @if($j->file_bukti)
                <a href="{{ route('admin.events.rtl.bukti', [$event, $rtl, $j->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-mpksdi-50 text-mpksdi-900 border border-mpksdi-200 text-xs font-bold hover:bg-mpksdi-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh Berkas Bukti</span>
                </a>
            @else
                <p class="text-xs text-slate-400">Tidak ada berkas bukti terunggah.</p>
            @endif
        @endif
    </div>
    @endforeach
</div>
@endsection
