@extends('layouts.app')

@section('title', 'Dashboard Executive')

@section('content')
<div class="space-y-6">
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-44 h-44 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                Executive Read-Only Overview
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Executive Dashboard</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl font-medium">Pemantauan progres pelatihan, statistik keikutsertaan kader, dan evaluasi capaian nilai akhir perkaderan.</p>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Event BA</span>
            <p class="text-3xl font-black text-slate-900 mt-2 tabular-nums">{{ $events->count() }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Kegiatan perkaderan terselenggara</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Peserta Aktif</span>
            <p class="text-3xl font-black text-slate-900 mt-2 tabular-nums">{{ $totalPeserta }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Kader terdaftar dalam pelatihan</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs card-lift-sm card-sheen hover:border-sky-400 transition">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rata-rata Nilai Akhir</span>
            <p class="text-3xl font-black text-sky-600 mt-2 tabular-nums">{{ $nilai ? number_format($nilai, 1) : '—' }}</p>
            <p class="text-xs text-slate-500 mt-1 font-medium">Skala 0 s.d. 100 seluruh kegiatan</p>
        </div>
    </div>

    {{-- Events Overview Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden card-lift-sm">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="font-black text-slate-900 text-lg tracking-tight">Daftar Kegiatan &amp; Laporan Perkaderan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Akses rekap nilai dan dokumen laporan resmi</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Event</th>
                        <th class="py-4 px-6">Pelaksanaan</th>
                        <th class="py-4 px-6">Peserta</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Laporan &amp; Ranking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-4 px-6 font-bold text-slate-900">
                            {{ $e->nama_event }}
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs font-medium">
                            {{ $e->tanggal_mulai->format('d M Y') }}
                        </td>
                        <td class="py-4 px-6 text-slate-700 text-xs font-bold tabular-nums">
                            {{ $e->peserta_aktif_count }} Kader
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow-2xs
                                {{ $e->status === 'persiapan' ? 'bg-amber-50 text-amber-800 border border-amber-300' : '' }}
                                {{ $e->status === 'berlangsung' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                                {{ $e->status === 'selesai' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $e->status === 'berlangsung' ? 'bg-sky-500 animate-ping' : ($e->status === 'persiapan' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                                {{ $e->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.events.ranking', $e) }}" class="px-4 py-1.5 rounded-full border border-slate-200 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 text-xs font-bold text-slate-700 transition shadow-2xs">
                                    Peringkat
                                </a>
                                <a href="{{ route('admin.events.report', $e) }}" class="px-4 py-1.5 rounded-full bg-sky-50 text-sky-800 hover:bg-sky-100 border border-sky-200 text-xs font-bold transition shadow-2xs">
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
