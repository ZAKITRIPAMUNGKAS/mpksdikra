@extends('layouts.app')

@section('title', 'Kelola Event')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-7 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kegiatan Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar agenda perkaderan Baitul Arqam yang diselenggarakan MPKSDI Karanganyar.</p>
            </div>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 active:bg-mpksdi-800 text-white text-xs font-bold rounded-lg shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Event Baru</span>
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Nama Event</th>
                        <th class="py-3.5 px-6">Jadwal Pelaksanaan</th>
                        <th class="py-3.5 px-6">Peserta Terdaftar</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block leading-tight">{{ $e->nama_event }}</span>
                            <span class="text-xs text-slate-400">{{ $e->lokasi ?: 'Lokasi belum diset' }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs">
                            <span class="font-medium text-slate-800 block">{{ $e->tanggal_mulai->format('d M Y') }}</span>
                            <span class="text-slate-400">s.d. {{ $e->tanggal_selesai->format('d M Y') }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 text-xs font-semibold tabular-nums">
                            {{ $e->peserta_aktif_count }} {{ $e->kuota ? '/ ' . $e->kuota : 'Kader' }}
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
                            <a href="{{ route('admin.events.show', $e) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 hover:border-mpksdi-400 hover:bg-mpksdi-50 hover:text-mpksdi-900 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                <span>Kelola</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                            Belum ada kegiatan Baitul Arqam yang dibuat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $events->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
