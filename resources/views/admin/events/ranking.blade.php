@extends('layouts.app')

@section('title', 'Ranking — ' . $event->nama_event)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke {{ $event->nama_event }}</span>
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
    <div class="bg-slate-50/70 border-b border-slate-200/80 p-6 relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                    Rekapitulasi Capaian
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Ranking Nilai Akhir Peserta</h1>
                <p class="text-xs text-slate-600 mt-1 max-w-3xl font-normal leading-relaxed">
                    NA = 40% Kognitif (70% Posttest + 30% N-Gain) + 35% Psikomotor (90% Rubrik + 10% Keaktifan) + 25% Afektif.
                    <span class="block mt-0.5 text-slate-500">Syarat Kelulusan Terpadu: Presensi 100% + Posttest Selesai + RTL Terkirim (Lulus). Presensi 75%–99% atau tugas RTL/Posttest pending dinyatakan Lulus Bersyarat. Presensi &lt;75% dinyatakan Tidak Lulus.</span>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($event->status === 'selesai')
                <a href="{{ route('admin.events.certificates.batch', $event) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Cetak Semua Sertifikat (PDF)</span>
                </a>
                @endif
                <a href="{{ route('admin.events.exportExcel', $event) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh Excel (XLSX)</span>
                </a>
                <a href="{{ route('admin.events.report', $event) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh Laporan PDF</span>
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider text-left">
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-4">Nama Kader</th>
                    <th class="py-3 px-3 text-center">Pretest</th>
                    <th class="py-3 px-3 text-center">Posttest</th>
                    <th class="py-3 px-3 text-center">N-Gain</th>
                    <th class="py-3 px-3 text-center">Afektif</th>
                    <th class="py-3 px-3 text-center">Psikomotor</th>
                    <th class="py-3 px-3 text-center">Keaktifan</th>
                    <th class="py-3 px-3 text-center">Hadir</th>
                    <th class="py-3 px-4 text-center">NA</th>
                    <th class="py-3 px-4">Kelulusan</th>
                    <th class="py-3 px-3 text-center">Grade</th>
                    <th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($ranking as $p)
                @php
                    $epId = $epMap->get($p->anggota_id);
                @endphp
                <tr class="hover:bg-slate-50/70 transition {{ $p->ranking === 1 ? 'bg-amber-50/40' : '' }}">
                    <td class="py-3 px-4 font-bold text-slate-900">
                        @if($p->ranking === 1)
                            <span class="inline-flex items-center gap-1 text-amber-600 font-bold">&#9733; 1</span>
                        @else
                            {{ $p->ranking ?? '-' }}
                        @endif
                    </td>
                    <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">{{ $p->anggota->nama_lengkap }}</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->nilai_pretest, 1) }}@if(in_array('pretest', $tesKertas[$p->anggota_id] ?? [], true))<div class="text-[10px] font-sans font-semibold text-amber-700" title="Diinput admin dari lembar kertas">kertas</div>@endif</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->nilai_posttest, 1) }}@if(in_array('posttest', $tesKertas[$p->anggota_id] ?? [], true))<div class="text-[10px] font-sans font-semibold text-amber-700" title="Diinput admin dari lembar kertas">kertas</div>@endif</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->n_gain, 2) }}</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->nilai_afektif, 1) }}</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->nilai_psikomotor, 1) }}</td>
                    <td class="py-3 px-3 text-center font-mono">{{ number_format($p->nilai_keaktifan, 1) }}</td>
                    <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700">{{ number_format($p->nilai_kehadiran, 1) }}%</td>
                    <td class="py-3 px-4 text-center font-bold font-mono text-sky-700 text-sm">{{ number_format($p->nilai_akhir, 2) }}</td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium uppercase tracking-wider
                            {{ $p->status_kelulusan === 'Lulus' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                            {{ $p->status_kelulusan === 'Lulus Bersyarat' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                            {{ $p->status_kelulusan === 'Tidak Lulus' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}">
                            {{ $p->status_kelulusan ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-center">
                        @if($p->grade)
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-black
                            {{ $p->grade === 'A' ? 'bg-sky-100 text-sky-800 border border-sky-300' : '' }}
                            {{ $p->grade === 'B' ? 'bg-amber-100 text-amber-800 border border-amber-300' : '' }}
                            {{ $p->grade === 'C' ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                            {{ $p->grade }}
                        </span>
                        @else
                            <span class="text-slate-300">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        @if($epId)
                        <a href="{{ route('admin.events.certificate', [$event, $epId]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-slate-200 hover:bg-sky-50 hover:text-sky-900 hover:border-sky-300 text-xs font-semibold text-slate-700 transition shadow-2xs">
                            Sertifikat
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="13" class="py-10 text-center text-slate-400">Belum ada data penilaian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
