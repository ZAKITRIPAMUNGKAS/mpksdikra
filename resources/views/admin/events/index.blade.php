@extends('layouts.app')

@section('title', 'Kelola Event')

@section('content')
<div class="space-y-6">
    <div class="bg-[#f3f5f8] rounded-3xl border border-sky-100/80 p-6 sm:p-8 shadow-xs relative overflow-hidden card-lift-sm">
        <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                    Agenda Perkaderan
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Kegiatan Baitul Arqam</h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-xl font-medium">Daftar agenda perkaderan Baitul Arqam yang diselenggarakan MPKSDI Karanganyar.</p>
            </div>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Event Baru</span>
            </a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden card-lift-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-6">Nama Event</th>
                        <th class="py-4 px-6">Jadwal Pelaksanaan</th>
                        <th class="py-4 px-6">Peserta Terdaftar</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $e)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-4 px-6">
                            <span class="font-extrabold text-slate-900 block leading-tight">{{ $e->nama_event }}</span>
                            <span class="text-xs text-slate-400 font-medium">{{ $e->lokasi ?: 'Lokasi belum diset' }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 text-xs">
                            <span class="font-bold text-slate-800 block">{{ $e->tanggal_mulai->format('d M Y') }}</span>
                            <span class="text-slate-400 font-medium">s.d. {{ $e->tanggal_selesai->format('d M Y') }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 text-xs font-bold tabular-nums">
                            {{ $e->peserta_aktif_count }} {{ $e->kuota ? '/ ' . $e->kuota : 'Kader' }}
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
                            <a href="{{ route('admin.events.show', $e) }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 hover:border-sky-400 hover:bg-sky-50 hover:text-sky-900 text-xs font-bold text-slate-700 transition shadow-2xs">
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
