@extends('layouts.app')

@section('title', 'Pangkalan Data Kader')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 border-t-4 border-t-mpksdi-600 p-6 sm:p-8 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pangkalan Data Anggota &amp; Kader</h1>
                    @if($pendingCount > 0)
                        <span class="px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold">
                            {{ $pendingCount }} menunggu verifikasi
                        </span>
                    @endif
                    @if(($ditolakCount ?? 0) > 0)
                        <span class="px-2.5 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-xs font-bold">
                            {{ $ditolakCount }} ditolak
                        </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Master database kader Muhammadiyah Karanganyar untuk penugasan dan kepesertaan Baitul Arqam.</p>
            </div>

            <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <div class="relative">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, NIK, cabang..."
                        class="pl-9 pr-3.5 py-2 text-xs font-medium rounded-lg border border-slate-300 focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 w-full sm:w-60 bg-white">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-700 outline-none focus:border-mpksdi-600 focus:ring-2 focus:ring-mpksdi-600/20 transition cursor-pointer shadow-2xs">
                    <option value="">Semua Status</option>
                    <option value="menunggu_verifikasi" @selected($status==='menunggu_verifikasi')>Menunggu Verifikasi</option>
                    <option value="aktif" @selected($status==='aktif')>Aktif</option>
                    <option value="ditolak" @selected($status==='ditolak')>Ditolak</option>
                    <option value="nonaktif" @selected($status==='nonaktif')>Nonaktif</option>
                </select>
                <button type="submit" class="px-3.5 py-2 bg-mpksdi-600 hover:bg-mpksdi-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                    Cari
                </button>
                @if(!empty($q) || !empty($status))
                    <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center justify-center px-3 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">Reset</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Data Table Card --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700">
                        <th class="py-3.5 px-6">Nama Kader</th>
                        <th class="py-3.5 px-6">NIK / NBM</th>
                        <th class="py-3.5 px-6">Status Akun</th>
                        <th class="py-3.5 px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($anggotas as $a)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <img src="{{ route('foto.anggota', $a) }}" alt="{{ $a->nama_lengkap }}"
                                    class="w-9 h-11 rounded object-cover border border-slate-200 bg-slate-100 shrink-0">
                                <div>
                                    <span class="font-bold text-slate-900 block leading-tight">{{ $a->nama_lengkap }}</span>
                                    <span class="text-xs text-slate-500">{{ $a->desa_kelurahan ? $a->desa_kelurahan . ', ' . $a->kecamatan : 'Domisili belum lengkap' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-mono text-xs font-semibold">
                            <div>{{ substr($a->nik, 0, 6) . '****' . substr($a->nik, -4) }}</div>
                            @if($a->nbm)
                                <div class="text-[10px] text-slate-400 font-sans">NBM: {{ $a->nbm }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold
                                {{ $a->user->status === 'aktif' ? 'bg-mpksdi-50 text-mpksdi-950 border border-mpksdi-200' : '' }}
                                {{ $a->user->status === 'menunggu_verifikasi' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                                {{ $a->user->status === 'ditolak' ? 'bg-red-50 text-red-800 border border-red-200' : '' }}
                                {{ $a->user->status === 'nonaktif' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                            ">{{ $a->user->status }}</span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <a href="{{ route('admin.anggota.show', $a) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-300 hover:border-mpksdi-600 hover:bg-mpksdi-50 hover:text-mpksdi-900 text-xs font-bold text-slate-700 transition shadow-2xs">
                                <span>Detail</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 text-sm">
                            Tidak ada data anggota ditemukan dengan filter pencarian ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($anggotas->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $anggotas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
