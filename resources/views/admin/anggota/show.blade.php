@extends('layouts.app')

@section('title', $anggota->nama_lengkap)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-600 transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke daftar</span>
    </a>
</div>

<div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs max-w-4xl card-lift-sm relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <div class="flex items-start justify-between gap-4 flex-wrap pb-6 border-b border-slate-100 mb-6">
            <div>
                <div class="flex items-center gap-4">
                    <img src="{{ route('foto.anggota', $anggota) }}"
                        alt="{{ $anggota->nama_lengkap }}"
                        class="w-16 h-20 sm:w-20 sm:h-24 object-cover rounded-2xl border-2 border-slate-200 shadow-xs shrink-0 bg-slate-100">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $anggota->nama_lengkap }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mt-1.5 shadow-2xs
                            {{ $anggota->user->status === 'aktif' ? 'bg-sky-50 text-sky-900 border border-sky-200' : '' }}
                            {{ $anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                            {{ $anggota->user->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                            {{ $anggota->user->status === 'nonaktif' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $anggota->user->status === 'aktif' ? 'bg-[#f8bb00] animate-pulse' : ($anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-500 animate-ping' : 'bg-slate-400') }}"></span>
                            {{ $anggota->user->status }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap items-center" x-data="{ showRejectModal: false }">
                @if($anggota->user->status !== 'aktif')
                <form method="POST" action="{{ route('admin.anggota.verify', $anggota) }}">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-bold rounded-full shadow-xs transition hover:scale-[1.01]">
                        Verifikasi &amp; Aktifkan
                    </button>
                </form>
                @endif

                @if($anggota->user->status === 'menunggu_verifikasi')
                <button type="button" @click="showRejectModal = true" class="px-5 py-2.5 border border-red-300 text-red-700 bg-white hover:bg-red-50 text-xs font-bold rounded-full shadow-2xs transition">
                    Tolak Pendaftaran
                </button>

                {{-- Modal Penolakan --}}
                <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showRejectModal = false" class="bg-white rounded-3xl shadow-xl max-w-md w-full p-6 sm:p-7 border border-slate-200 card-lift-sm">
                        <h3 class="text-lg font-black text-slate-900 mb-1 tracking-tight">Tolak Pendaftaran Anggota</h3>
                        <p class="text-xs text-slate-500 mb-4 font-medium">Tuliskan alasan penolakan agar kader memahami hal yang perlu diperbaiki.</p>
                        
                        <form method="POST" action="{{ route('admin.anggota.reject', $anggota) }}">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alasan / Catatan Perbaikan:</label>
                                <textarea name="catatan_verifikasi" rows="4" required placeholder="Contoh: Pasfoto tidak jelas/bukan foto formal, silakan hubungi admin..."
                                    class="w-full text-xs p-3.5 rounded-2xl border border-slate-200/90 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition resize-none"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-full">Batal</button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold bg-red-600 hover:bg-red-700 text-white rounded-full transition shadow-2xs">Tolak Anggota</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($anggota->user->status === 'aktif')
                <form method="POST" action="{{ route('admin.anggota.deactivate', $anggota) }}"
                    onsubmit="return confirm('Nonaktifkan anggota ini?')">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 border border-red-200 text-red-700 text-xs font-bold rounded-full bg-white hover:bg-red-50 transition shadow-2xs">
                        Nonaktifkan
                    </button>
                </form>
                @endif
            </div>
        </div>

        @if($anggota->catatan_verifikasi)
        <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-red-50 border border-red-200 text-xs flex items-start gap-3 shadow-xs">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold text-red-900 block mb-0.5">Catatan Verifikasi / Alasan Penolakan:</span>
                <p class="text-red-700 leading-relaxed font-medium">{{ $anggota->catatan_verifikasi }}</p>
            </div>
        </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 text-xs">
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">NIK:</span> <span class="font-mono font-bold text-slate-800 text-sm">{{ substr($anggota->nik, 0, 6) . '****' . substr($anggota->nik, -4) }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Jenis Kelamin:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Tempat/Tanggal Lahir:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->tempat_lahir }}, {{ $anggota->tanggal_lahir?->format('d M Y') }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Status Pernikahan:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->status_pernikahan }}</span></div>
            <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Alamat:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->alamat_rumah }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Desa/Kelurahan:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->desa_kelurahan }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Kecamatan:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->kecamatan }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Kabupaten:</span> <span class="font-bold text-slate-800 text-sm">{{ $anggota->kabupaten }}</span></div>
            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100"><span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">No. WhatsApp:</span> <span class="font-mono font-bold text-slate-800 text-sm">{{ $anggota->no_wa }}</span></div>
            
            @if($anggota->aktifitas_jamaah_masjid)
            <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50/60 border border-slate-100">
                <span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Aktifitas Jamaah di Masjid:</span>
                <span class="text-slate-800 text-xs leading-relaxed font-medium">{{ $anggota->aktifitas_jamaah_masjid }}</span>
            </div>
            @endif

            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-100">
                <span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Keaktifan Takmir Masjid:</span>
                <span class="font-bold text-slate-800 text-xs">
                    {{ $anggota->keaktifan_takmir ?? 'Tidak' }}
                    @if(($anggota->keaktifan_takmir === 'Ya' || strtolower($anggota->keaktifan_takmir ?? '') === 'ya') && $anggota->jabatan_takmir)
                        &mdash; <span class="text-sky-700">({{ $anggota->jabatan_takmir }})</span>
                    @endif
                </span>
            </div>

            @if($anggota->keaktifan_kemasyarakatan)
            <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50/60 border border-slate-100">
                <span class="font-bold text-slate-500 uppercase tracking-wider block mb-0.5 text-[10px]">Keaktifan di Kemasyarakatan (RT/RW/BPD/Karang Taruna):</span>
                <span class="text-slate-800 text-xs leading-relaxed font-medium">{{ $anggota->keaktifan_kemasyarakatan }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
