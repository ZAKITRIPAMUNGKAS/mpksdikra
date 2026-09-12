@extends('layouts.app')

@section('title', 'RTL — ' . $rtl->anggota->nama_lengkap)

@section('content')
<div class="space-y-6">
    {{-- Breadcrumbs Navigation --}}
    <div>
        <a href="{{ route('admin.events.rtl.index', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Daftar RTL</span>
        </a>
    </div>

    {{-- Header Banner Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.20] pointer-events-none" />
        <div class="relative z-10 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20 mb-2">
                    Tinjauan Rencana Tindak Lanjut
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $rtl->anggota->nama_lengkap }}</h1>
                <p class="text-xs text-slate-500 mt-1 font-normal">
                    Status: 
                    <span class="uppercase font-semibold px-2 py-0.5 rounded-md text-xs
                        {{ $rtl->status === 'terkirim' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                        {{ $rtl->status === 'disetujui' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                        {{ $rtl->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                        {{ $rtl->status }}
                    </span>
                    &bull; Dikirim: {{ $rtl->updated_at->format('d M Y H:i') }}
                </p>
            </div>
            @if($rtl->status === 'terkirim')
            <div class="flex gap-2">
                <form method="POST" action="{{ route('admin.events.rtl.review', [$event, $rtl]) }}">
                    @csrf
                    <input type="hidden" name="status" value="disetujui">
                    <button class="px-5 py-2.5 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white text-xs font-semibold rounded-lg shadow-2xs transition">Setujui RTL</button>
                </form>
                <form method="POST" action="{{ route('admin.events.rtl.review', [$event, $rtl]) }}">
                    @csrf
                    <input type="hidden" name="status" value="ditolak">
                    <button class="px-5 py-2.5 border border-red-200 text-red-700 hover:bg-red-50 text-xs font-semibold rounded-lg shadow-2xs transition">Tolak RTL</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    {{-- Jawaban RTL List --}}
    <div class="space-y-4">
        @foreach($rtl->jawaban->sortBy(fn($j) => $j->soal->urutan ?? 0) as $j)
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
            <h3 class="font-bold text-slate-900 text-xs sm:text-sm mb-3">{{ $j->soal->pertanyaan }}</h3>
            @if($j->soal->tipe === 'essay')
                <p class="text-xs sm:text-sm text-slate-700 whitespace-pre-wrap leading-relaxed p-4 rounded-lg bg-slate-50/70 border border-slate-200/70">{{ $j->jawaban }}</p>
            @else
                @if($j->file_bukti)
                    <a href="{{ route('admin.events.rtl.bukti', [$event, $rtl, $j->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-sky-50 text-[#005c8a] border border-sky-200 text-xs font-semibold hover:bg-sky-100 transition shadow-2xs">
                        <svg class="w-4 h-4 text-[#005c8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh Berkas Bukti</span>
                    </a>
                @else
                    <p class="text-xs text-slate-400 font-normal">Tidak ada berkas bukti terunggah.</p>
                @endif
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
