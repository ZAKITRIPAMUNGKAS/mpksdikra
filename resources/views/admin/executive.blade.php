@extends('layouts.app')

@section('title', 'Dashboard Executive')

@section('content')
<div class="space-y-6">
    {{-- Header Banner dengan Ornamen Batik Solo Kawung --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-44 h-44 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.22] pointer-events-none" />
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                    Executive Overview
                </span>
                <span class="text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500">Pimpinan PDM Karanganyar</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">Pemantauan progres pelatihan, statistik keikutsertaan kader, dan evaluasi capaian nilai akhir perkaderan.</p>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Total Event BA</span>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $events->count() }}</p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Kegiatan perkaderan terselenggara
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Peserta Terdaftar</span>
                <p class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ $totalPeserta }}</p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Kader aktif dalam pelatihan
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Tingkat Kelulusan</span>
                <p class="text-2xl sm:text-3xl font-bold text-emerald-600 mt-2 tabular-nums">{{ $totalLulus }} <span class="text-xs font-normal text-slate-500">({{ $persenKelulusan }}%)</span></p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Kader berstatus lulus murni
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-medium text-slate-500">Rata-rata Nilai Akhir</span>
                <p class="text-2xl sm:text-3xl font-bold text-[#005c8a] mt-2 tabular-nums">{{ $nilai ? number_format($nilai, 1) : '—' }}</p>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Skala 0 s.d. 100 evaluasi
            </div>
        </div>
    </div>

    {{-- Events Overview Table --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-900 text-base tracking-tight">Daftar Kegiatan &amp; Laporan Perkaderan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Akses rekap nilai dan dokumen laporan resmi</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Event</th>
                        <th class="py-3.5 px-5">Pelaksanaan</th>
                        <th class="py-3.5 px-5">Peserta</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Laporan &amp; Ranking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-3.5 px-5 font-semibold text-slate-900 text-sm">
                            {{ $e->nama_event }}
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 text-xs">
                            {{ $e->tanggal_mulai ? $e->tanggal_mulai->format('d M Y') : '—' }}
                        </td>
                        <td class="py-3.5 px-5 text-slate-700 text-xs font-semibold tabular-nums">
                            {{ $e->peserta_aktif_count }} Kader
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium capitalize
                                {{ $e->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                                {{ $e->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                                {{ $e->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                {{ $e->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.events.ranking', $e) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                    Peringkat
                                </a>
                                <a href="{{ route('admin.events.exportExcel', $e) }}" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 transition shadow-2xs inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Excel</span>
                                </a>
                                <a href="{{ route('admin.events.report', $e) }}" class="px-2.5 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 transition shadow-2xs inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-[#005c8a] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>PDF</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
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
