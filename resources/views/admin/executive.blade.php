@extends('layouts.app')

@section('title', 'Dashboard Executive')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200 mb-2">
            Executive Read-Only Overview
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">Executive Dashboard</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Pemantauan progres pelatihan, statistik keikutsertaan kader, dan evaluasi capaian nilai akhir.</p>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Event BA</span>
            <p class="text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $events->count() }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Kegiatan perkaderan terselenggara</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Peserta Aktif</span>
            <p class="text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $totalPeserta }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Kader terdaftar dalam pelatihan</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rata-rata Nilai Akhir</span>
            <p class="text-3xl font-bold text-mpksdi-600 mt-2 tabular-nums">{{ $nilai ? number_format($nilai, 1) : '—' }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Skala 0 s.d. 100 seluruh kegiatan</p>
        </div>
    </div>

    {{-- Events Overview Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-base">Daftar Kegiatan &amp; Laporan Perkaderan</h2>
            <span class="text-xs text-slate-500">Akses rekap nilai dan dokumen laporan resmi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Event</th>
                        <th class="py-3.5 px-6">Pelaksanaan</th>
                        <th class="py-3.5 px-6">Peserta</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Laporan &amp; Ranking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-4 px-6 font-bold text-slate-900">
                            {{ $e->nama_event }}
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs">
                            {{ $e->tanggal_mulai->format('d M Y') }}
                        </td>
                        <td class="py-4 px-6 text-slate-700 text-xs font-semibold tabular-nums">
                            {{ $e->peserta_aktif_count }} Kader
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider
                                {{ $e->status === 'persiapan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                {{ $e->status === 'berlangsung' ? 'bg-mpksdi-50 text-mpksdi-700 border border-mpksdi-200' : '' }}
                                {{ $e->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                {{ $e->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.events.ranking', $e) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                    Peringkat
                                </a>
                                <a href="{{ route('admin.events.report', $e) }}" class="px-3 py-1.5 rounded-lg bg-mpksdi-50 text-mpksdi-800 hover:bg-mpksdi-100 border border-mpksdi-200 text-xs font-semibold transition">
                                    Laporan PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                            Belum ada kegiatan yang tersimpan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
