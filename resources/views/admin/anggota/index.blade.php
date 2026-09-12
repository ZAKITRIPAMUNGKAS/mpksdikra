@extends('layouts.app')

@section('title', 'Pangkalan Data Kader')

@section('content')
<div class="space-y-6">
    {{-- Header Banner dengan Ornamen Batik Solo Kawung --}}
    <div class="relative bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs overflow-hidden">
        <x-batik-corner position="top-right" class="w-44 h-44 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.22] pointer-events-none" />
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                        Master Data Perkaderan
                    </span>
                    @if($pendingCount > 0)
                        <span class="px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold">
                            {{ $pendingCount }} Menunggu Verifikasi
                        </span>
                    @endif
                    @if(($ditolakCount ?? 0) > 0)
                        <span class="px-2.5 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold">
                            {{ $ditolakCount }} Ditolak
                        </span>
                    @endif
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pangkalan Data Anggota &amp; Kader</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">Master database kader Muhammadiyah Karanganyar untuk penugasan dan kepesertaan Baitul Arqam.</p>
            </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <a href="{{ route('admin.anggota.import') }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-2xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Impor Excel</span>
            </a>

            <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, NIK, PCM..."
                        class="pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200/90 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 w-full sm:w-48 bg-white outline-none shadow-2xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="menunggu_verifikasi" @selected($status==='menunggu_verifikasi')>Menunggu Verifikasi</option>
                    <option value="aktif" @selected($status==='aktif')>Aktif</option>
                    <option value="ditolak" @selected($status==='ditolak')>Ditolak</option>
                    <option value="nonaktif" @selected($status==='nonaktif')>Nonaktif</option>
                </select>
                <button type="submit" class="px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] text-white rounded-lg text-xs font-semibold transition shadow-2xs">
                    Cari
                </button>
                @if(!empty($q) || !empty($status))
                    <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center justify-center px-2 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 transition">Reset</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Data Table Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Nama Kader</th>
                        <th class="py-3.5 px-5">NIK / NBM</th>
                        <th class="py-3.5 px-5">Status Akun</th>
                        <th class="py-3.5 px-5 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($anggotas as $a)
                    <tr class="hover:bg-slate-50/70 transition duration-150">
                        <td class="py-3.5 px-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ route('foto.anggota', $a) }}" alt="{{ $a->nama_lengkap }}"
                                    class="w-9 h-11 rounded-lg object-cover border border-slate-200 bg-slate-100 shrink-0 shadow-2xs">
                                <div>
                                    <span class="font-semibold text-slate-900 block leading-tight text-sm">{{ $a->nama_lengkap }}</span>
                                    <span class="text-xs text-slate-500 mt-0.5 block">{{ $a->desa_kelurahan ? $a->desa_kelurahan . ', ' . $a->kecamatan : 'Domisili belum lengkap' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-slate-600 font-mono text-xs font-medium">
                            <div>{{ substr($a->nik, 0, 6) . '****' . substr($a->nik, -4) }}</div>
                            @if($a->nbm)
                                <div class="text-xs text-slate-400 font-sans">NBM: {{ $a->nbm }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium capitalize
                                {{ $a->user->status === 'aktif' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                                {{ $a->user->status === 'menunggu_verifikasi' ? 'bg-amber-50 text-amber-800 border border-amber-200' : '' }}
                                {{ $a->user->status === 'ditolak' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                {{ $a->user->status === 'nonaktif' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}
                            ">
                                {{ $a->user->status === 'menunggu_verifikasi' ? 'Menunggu' : $a->user->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($a->no_wa)
                                    <a href="https://api.whatsapp.com/send?phone={{ \App\Services\WhatsAppNotificationService::formatNomor($a->no_wa) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       title="Hubungi via WhatsApp ({{ $a->no_wa }})"
                                       class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200 transition shadow-2xs">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.592 2.654-.696c1.004.577 1.773.847 2.806.847h.001c3.182 0 5.768-2.586 5.768-5.766 0-3.18-2.586-5.768-5.769-5.768zm3.376 8.21c-.14.394-.712.753-1.001.782-.289.028-.59.13-1.928-.423-1.338-.553-2.193-1.91-2.26-2-.067-.09-1.042-1.388-1.042-2.646 0-1.258.657-1.878.891-2.133.234-.255.511-.318.682-.318.17 0 .341.002.49.01.157.008.367-.06.574.437.213.513.725 1.767.789 1.896.064.13.106.28.021.451-.085.17-.128.277-.255.426-.128.149-.27.332-.385.446-.128.128-.261.267-.113.522.149.255.66 1.088 1.416 1.762.973.867 1.792 1.137 2.048 1.265.255.128.405.107.554-.064.149-.17.639-.745.81-1.001.17-.255.341-.213.575-.128.234.085 1.491.703 1.747.831.255.128.426.191.49.298.064.106.064.618-.076 1.012z"/>
                                        </svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.anggota.show', $a) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
                                    <span>Detail</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400 text-xs">
                            Tidak ada data anggota ditemukan dengan filter pencarian ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($anggotas->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $anggotas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
