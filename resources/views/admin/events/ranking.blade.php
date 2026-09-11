@extends('layouts.app')

@section('title', 'Ranking — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-mpksdi-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke {{ $event->nama_event }}
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
    <div class="flex items-center justify-between gap-4 flex-wrap mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Ranking Nilai Akhir</h1>
            <p class="text-xs text-slate-500 mt-1">
                NA = 40% Kognitif + 35% Psikomotor + 25% Afektif.
                Kognitif = 70% posttest + 30% N-Gain&times;100. Psikomotor = 90% rubrik + 10% keaktifan.
                Kehadiran = syarat kelulusan (&ge;75% bersyarat / 100% mutlak).
            </p>
        </div>
        <a href="{{ route('admin.events.report', $event) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white text-xs font-bold rounded-lg shadow-xs transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Unduh Laporan PDF</span>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
                    <th class="py-3 px-3">#</th>
                    <th class="py-3 px-3">Nama Kader</th>
                    <th class="py-3 px-2.5 text-center">Pretest</th>
                    <th class="py-3 px-2.5 text-center">Posttest</th>
                    <th class="py-3 px-2.5 text-center">N-Gain</th>
                    <th class="py-3 px-2.5 text-center">Afektif</th>
                    <th class="py-3 px-2.5 text-center">Psikomotor</th>
                    <th class="py-3 px-2.5 text-center">Keaktifan</th>
                    <th class="py-3 px-2.5 text-center">Hadir</th>
                    <th class="py-3 px-3 text-center">NA</th>
                    <th class="py-3 px-3">Kelulusan</th>
                    <th class="py-3 px-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($ranking as $p)
                @php
                    $nGain = $p->nilai_pretest >= 100 ? 0 : round(($p->nilai_posttest - $p->nilai_pretest) / (100 - $p->nilai_pretest), 3);
                    $epId = $epMap->get($p->anggota_id);
                @endphp
                <tr class="hover:bg-slate-50/70 transition {{ $p->ranking === 1 ? 'bg-gold-50/50' : '' }}">
                    <td class="py-3 px-3 font-bold text-slate-900">
                        @if($p->ranking === 1)
                            <span class="inline-flex items-center gap-1 text-gold-700 font-extrabold">&#9733; 1</span>
                        @else
                            {{ $p->ranking ?? '-' }}
                        @endif
                    </td>
                    <td class="py-3 px-3 font-semibold text-slate-900 whitespace-nowrap">{{ $p->anggota->nama_lengkap }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($p->nilai_pretest, 1) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($p->nilai_posttest, 1) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($nGain, 2) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($p->nilai_afektif, 1) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($p->nilai_psikomotor, 1) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono">{{ number_format($p->nilai_keaktifan, 1) }}</td>
                    <td class="py-3 px-2.5 text-center font-mono font-semibold text-slate-700">{{ number_format($p->nilai_kehadiran, 1) }}%</td>
                    <td class="py-3 px-3 text-center font-bold font-mono text-mpksdi-700 text-sm">{{ number_format($p->nilai_akhir, 2) }}</td>
                    <td class="py-3 px-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider
                            {{ $p->status_kelulusan === 'Lulus' ? 'bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200' : '' }}
                            {{ $p->status_kelulusan === 'Lulus Bersyarat' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                            {{ $p->status_kelulusan === 'Tidak Lulus' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                            {{ $p->status_kelulusan ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right whitespace-nowrap">
                        @if($epId)
                        <a href="{{ route('admin.events.certificate', [$event, $epId]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-slate-200 hover:bg-mpksdi-50 hover:text-mpksdi-900 hover:border-mpksdi-300 text-[11px] font-semibold text-slate-600 transition">
                            Sertifikat
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="py-10 text-center text-slate-400">Belum ada data penilaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
