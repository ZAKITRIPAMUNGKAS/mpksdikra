@extends('layouts.app')

@section('title', 'ID Card & Kehadiran — ' . $event->nama_event)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-mpksdi-600 transition mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $event->nama_event }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kartu Tanda Peserta & Presensi Sesi Perkaderan Baitul Arqam</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6 items-start">
        {{-- Digital Card Pass --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="bg-gradient-to-b from-mpksdi-950 to-mpksdi-900 p-6 text-white text-center relative border-b-2 border-gold-400">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 w-auto mx-auto mb-2">
                <div class="inline-block px-3 py-0.5 rounded-full bg-white/10 text-[11px] font-bold tracking-wider text-gold-300 uppercase mb-2">
                    Kartu Peserta Baitul Arqam
                </div>
                <h2 class="text-lg font-bold leading-tight">{{ $ep->anggota->nama_lengkap }}</h2>
                <p class="text-xs text-slate-300 mt-1">NIK: {{ substr($ep->anggota->nik, 0, 6) }}******</p>
            </div>

            <div class="p-6 text-center">
                <div class="inline-block p-4 bg-white border-2 border-dashed border-mpksdi-400/50 rounded-xl shadow-2xs">
                    {!! $qrSvg !!}
                </div>
                <p class="text-xs text-slate-500 mt-4 max-w-xs mx-auto">
                    Tunjukkan QR Code ini ke petugas pemindai presensi di setiap sesi kegiatan.
                </p>

                <div class="mt-6 pt-5 border-t border-slate-100">
                    <a href="{{ route('portal.idcard.download', $event) }}" class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-gold-400 hover:bg-gold-500 text-mpksdi-950 text-xs font-extrabold shadow-2xs border border-gold-500 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Unduh ID Card PDF Resmi</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Attendance Timeline --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Rekap Kehadiran Sesi</h2>
                    <p class="text-xs text-slate-500">Syarat kelulusan: 100% penuh / 75% bersyarat</p>
                </div>
                <span class="px-3 py-1 rounded-md text-xs font-bold {{ count($hadir) === $sesi->count() && $sesi->count() > 0 ? 'bg-mpksdi-100 text-mpksdi-950 border border-mpksdi-300' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ count($hadir) }} / {{ $sesi->count() }} Sesi
                </span>
            </div>

            <div class="space-y-2.5 max-h-[26rem] overflow-y-auto pr-1">
                @forelse($sesi as $s)
                <div class="flex items-center justify-between gap-3 p-3.5 rounded-lg border border-slate-100 bg-slate-50/60 hover:bg-white hover:border-slate-300 transition">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $s->urutan }}. {{ $s->nama_sesi }}</p>
                        <p class="text-[11px] text-slate-500 capitalize">{{ $s->jenis }}</p>
                    </div>
                    @if(in_array($s->id, $hadir))
                        <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-mpksdi-100 text-mpksdi-950 border border-mpksdi-200 text-xs font-bold">
                            <svg class="w-3.5 h-3.5 text-mpksdi-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Hadir</span>
                        </span>
                    @else
                        <span class="shrink-0 px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">
                            Belum Hadir
                        </span>
                    @endif
                </div>
                @empty
                <div class="text-center py-10 text-slate-400">
                    <p class="text-sm">Belum ada sesi yang dijadwalkan untuk kegiatan ini.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
