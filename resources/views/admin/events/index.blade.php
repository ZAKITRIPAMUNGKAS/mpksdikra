@extends('layouts.app')

@section('title', 'Kelola Event')

@section('content')
<div class="space-y-6">
    {{-- Header Banner dengan Ornamen Batik Solo Kawung --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-44 h-44 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.22] pointer-events-none" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                        Agenda Perkaderan
                    </span>
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-xs text-slate-500">{{ $events->total() }} Total Kegiatan</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kegiatan Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">Daftar agenda perkaderan Baitul Arqam yang diselenggarakan MPKSDI PDM Karanganyar.</p>
            </div>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] text-white text-xs font-semibold rounded-lg shadow-2xs transition shrink-0">
                <svg class="w-4 h-4 text-[#f8bb00]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Event Baru</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Nama Event</th>
                        <th class="py-3.5 px-5">Jadwal Pelaksanaan</th>
                        <th class="py-3.5 px-5">Peserta Terdaftar</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-3.5 px-5">
                            <a href="{{ route('admin.events.show', $e) }}" class="font-semibold text-slate-900 hover:text-[#005c8a] text-sm block leading-tight transition">
                                {{ $e->nama_event }}
                            </a>
                            <span class="text-xs text-slate-400 mt-0.5 block">{{ $e->lokasi ?: 'Lokasi belum diset' }}</span>
                        </td>
                        <td class="py-3.5 px-5 text-xs text-slate-600">
                            <span class="font-semibold text-slate-800 block">{{ $e->tanggal_mulai ? $e->tanggal_mulai->format('d M Y') : '—' }}</span>
                            <span class="text-slate-400">s.d. {{ $e->tanggal_selesai ? $e->tanggal_selesai->format('d M Y') : '—' }}</span>
                        </td>
                        <td class="py-3.5 px-5 text-xs text-slate-700 font-semibold tabular-nums">
                            {{ $e->peserta_aktif_count }} {{ $e->kuota ? '/ ' . $e->kuota : 'Kader' }}
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
                            <a href="{{ route('admin.events.show', $e) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                <span>Kelola</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
                            Belum ada kegiatan Baitul Arqam yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
