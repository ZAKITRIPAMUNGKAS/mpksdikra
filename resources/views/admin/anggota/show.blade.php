@extends('layouts.app')

@section('title', $anggota->nama_lengkap)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition shadow-2xs">
        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Daftar</span>
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs relative overflow-hidden">
    <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.22]" />
    <div class="relative z-10">
        <div class="flex items-start justify-between gap-4 flex-wrap pb-6 border-b border-slate-100 mb-6">
            <div>
                <div class="flex items-center gap-4">
                    <img src="{{ route('foto.anggota', $anggota) }}"
                        alt="{{ $anggota->nama_lengkap }}"
                        class="w-16 h-20 sm:w-20 sm:h-24 object-cover rounded-xl border border-slate-200 shadow-2xs shrink-0 bg-slate-100"
                        onerror="this.onerror=null;this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 200 200\'%3E%3Crect width=\'200\' height=\'200\' fill=\'%23002b40\'/%3E%3Ctext x=\'50%25\' y=\'54%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%23f8bb00\' font-family=\'Arial\' font-size=\'64\' font-weight=\'bold\'%3E{{ strtoupper(substr($anggota->nama_lengkap, 0, 2)) }}%3C/text%3E%3C/svg%3E'">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $anggota->nama_lengkap }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium uppercase tracking-wider mt-1.5
                            {{ $anggota->user->status === 'aktif' ? 'bg-sky-50 text-sky-800 border border-sky-200' : '' }}
                            {{ $anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-50 text-amber-900 border border-amber-300' : '' }}
                            {{ $anggota->user->status === 'ditolak' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                            {{ $anggota->user->status === 'nonaktif' ? 'bg-slate-100 text-slate-600 border border-slate-200' : '' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $anggota->user->status === 'aktif' ? 'bg-emerald-500' : ($anggota->user->status === 'menunggu_verifikasi' ? 'bg-amber-500 animate-pulse' : 'bg-slate-400') }}"></span>
                            {{ $anggota->user->status }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap items-center" x-data="{ showRejectModal: false, showDeactivateModal: false }">
                @if($anggota->user->status === 'menunggu_verifikasi' || $anggota->user->status === 'ditolak')
                <form method="POST" action="{{ route('admin.anggota.verify', $anggota) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 active:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                        Verifikasi &amp; Aktifkan
                    </button>
                </form>
                @endif

                @if($anggota->user->status === 'menunggu_verifikasi')
                <button type="button" @click="showRejectModal = true" class="px-4 py-2 border border-red-300 text-red-700 bg-white hover:bg-red-50 text-xs font-semibold rounded-lg shadow-2xs transition">
                    Tolak Pendaftaran
                </button>

                {{-- Modal Penolakan --}}
                <div x-show="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showRejectModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-1 tracking-tight">Tolak Pendaftaran Anggota</h3>
                        <p class="text-xs text-slate-500 mb-4 font-normal">Tuliskan alasan penolakan agar kader memahami hal yang perlu diperbaiki.</p>
                        
                        <form method="POST" action="{{ route('admin.anggota.reject', $anggota) }}">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alasan / Catatan Perbaikan:</label>
                                <textarea name="catatan_verifikasi" rows="4" required placeholder="Contoh: Pasfoto tidak jelas/bukan foto formal, silakan hubungi admin..."
                                    class="w-full text-xs p-3 rounded-lg border border-slate-200/90 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition resize-none"></textarea>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showRejectModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition shadow-2xs">Tolak Anggota</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                @if($anggota->user->status === 'aktif')
                <button type="button" @click="showDeactivateModal = true" class="px-4 py-2 border border-red-200 text-red-700 text-xs font-semibold rounded-lg bg-white hover:bg-red-50 transition shadow-2xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Nonaktifkan</span>
                </button>

                {{-- Modal Penonaktifan --}}
                <div x-show="showDeactivateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" style="display: none;">
                    <div @click.away="showDeactivateModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 border border-slate-200" x-data="{ preset: '', customText: '' }">
                        <h3 class="text-base font-bold text-slate-900 mb-1 tracking-tight">Nonaktifkan Keanggotaan Kader</h3>
                        <p class="text-xs text-slate-500 mb-4 font-normal">Kader yang dinonaktifkan tidak dapat masuk ke portal perkaderan. Keterangan penonaktifan akan dicatat di sistem dan dikirimkan via WhatsApp.</p>
                        
                        <form method="POST" action="{{ route('admin.anggota.deactivate', $anggota) }}"
                            @submit="if(preset && preset !== 'Lainnya') { $refs.alasanInput.value = preset + (customText ? ' - ' + customText : ''); } else if(customText) { $refs.alasanInput.value = customText; }">
                            @csrf
                            <input type="hidden" name="alasan_nonaktif" x-ref="alasanInput">

                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Kategori Alasan:</label>
                                <div class="grid grid-cols-1 gap-1.5 text-xs">
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition" :class="{ 'border-sky-500 bg-sky-50/50 font-semibold text-sky-900': preset === 'Pindah Domisili / Mutasi Luar Daerah' }">
                                        <input type="radio" name="preset_option" value="Pindah Domisili / Mutasi Luar Daerah" @click="preset = 'Pindah Domisili / Mutasi Luar Daerah'" class="text-sky-600">
                                        <span>Pindah Domisili / Mutasi Luar Daerah</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition" :class="{ 'border-sky-500 bg-sky-50/50 font-semibold text-sky-900': preset === 'Wafat / Meninggal Dunia' }">
                                        <input type="radio" name="preset_option" value="Wafat / Meninggal Dunia" @click="preset = 'Wafat / Meninggal Dunia'" class="text-sky-600">
                                        <span>Wafat / Meninggal Dunia</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition" :class="{ 'border-sky-500 bg-sky-50/50 font-semibold text-sky-900': preset === 'Mengundurkan Diri dari Kepengurusan / AUM' }">
                                        <input type="radio" name="preset_option" value="Mengundurkan Diri dari Kepengurusan / AUM" @click="preset = 'Mengundurkan Diri dari Kepengurusan / AUM'" class="text-sky-600">
                                        <span>Mengundurkan Diri dari Kepengurusan / AUM</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition" :class="{ 'border-sky-500 bg-sky-50/50 font-semibold text-sky-900': preset === 'Pelanggaran Disiplin / Kode Etik Persyarikatan' }">
                                        <input type="radio" name="preset_option" value="Pelanggaran Disiplin / Kode Etik Persyarikatan" @click="preset = 'Pelanggaran Disiplin / Kode Etik Persyarikatan'" class="text-sky-600">
                                        <span>Pelanggaran Disiplin / Kode Etik Persyarikatan</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer transition" :class="{ 'border-sky-500 bg-sky-50/50 font-semibold text-sky-900': preset === 'Lainnya' }">
                                        <input type="radio" name="preset_option" value="Lainnya" @click="preset = 'Lainnya'" class="text-sky-600">
                                        <span>Alasan Lainnya...</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan / Keterangan Tambahan:</label>
                                <textarea x-model="customText" rows="3" placeholder="Tuliskan catatan atau keterangan detail..."
                                    class="w-full text-xs p-3 rounded-lg border border-slate-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition resize-none"></textarea>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showDeactivateModal = false" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold bg-red-600 hover:bg-red-700 text-white rounded-lg transition shadow-2xs">Konfirmasi Nonaktifkan</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Lihat & Unduh CV / Biodata PDF --}}
                <a href="{{ route('admin.anggota.cetak-pdf', $anggota) }}" target="_blank" class="px-3.5 py-2 border border-sky-300 text-sky-800 bg-sky-50/80 hover:bg-sky-100 active:bg-sky-200 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5" title="Lihat CV / Biodata Resmi Kader (PDF)">
                    <svg class="w-3.5 h-3.5 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Lihat Biodata</span>
                </a>
                <a href="{{ route('admin.anggota.cetak-pdf', $anggota) }}?download" class="px-3.5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5" title="Unduh CV / Biodata sebagai file PDF">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh</span>
                </a>

                {{-- Reset Password Button --}}
                <form method="POST" action="{{ route('admin.anggota.reset-password', $anggota) }}"
                    onsubmit="return confirm('Reset kata sandi anggota ini ke sandi bawaan (Tanggal Lahir / 6 digit NIK terakhir)?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5" title="Reset Kata Sandi Akun">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Reset Sandi</span>
                    </button>
                </form>

                {{-- Hapus Data Kader --}}
                <form method="POST" action="{{ route('admin.anggota.destroy', $anggota) }}"
                    onsubmit="return confirm('Hapus permanen data kader {{ addslashes($anggota->nama_lengkap) }}?\n\nTindakan ini akan menghapus data profil dan akun login. Lanjutkan?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2 border border-rose-200 text-rose-600 bg-white hover:bg-rose-50 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5" title="Hapus Data Kader">
                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Hapus</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Card Pasfoto & Cutout ID Card (K5) --}}
        <div class="mb-6 p-4 sm:p-5 rounded-xl border border-slate-200/90 bg-slate-50/70 shadow-2xs">
            <div class="flex items-center justify-between flex-wrap gap-3 pb-3 border-b border-slate-200/80 mb-4">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#063A60] flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Pengaturan Pasfoto & Cutout ID Card</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pratinjau foto sumber dan hasil cutout transparan untuk kartu tanda anggota/ID Card Baitul Arqam.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-semibold text-slate-500">Mode Aktif:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold {{ $anggota->foto_mode === 'original' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $anggota->foto_mode === 'original' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        {{ $anggota->foto_mode === 'original' ? 'Original (Berbingkai)' : 'Cutout (Transparan)' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 items-start">
                {{-- 1. Foto Sumber Asli --}}
                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs text-center flex flex-col items-center">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2">1. Foto Sumber (Asli)</span>
                    <div class="w-24 h-32 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 shadow-2xs flex items-center justify-center">
                        <img src="{{ route('foto.anggota', $anggota) }}"
                             alt="Foto Sumber"
                             class="w-full h-full object-cover">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-2">Format pasfoto resmi kader</span>
                </div>

                {{-- 2. Foto Cutout Klien --}}
                <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-2xs text-center flex flex-col items-center">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2">2. Hasil Cutout Klien</span>
                    @if($anggota->foto_cutout_path)
                        <div class="w-24 h-32 rounded-lg border border-slate-300 overflow-hidden shadow-2xs flex items-center justify-center bg-[linear-gradient(45deg,#f1f5f9_25%,transparent_25%),linear-gradient(-45deg,#f1f5f9_25%,transparent_25%),linear-gradient(45deg,transparent_75%,#f1f5f9_75%),linear-gradient(-45deg,transparent_75%,#f1f5f9_75%)] [background-size:12px_12px] [background-position:0_0,0_6px,6px_-6px,-6px_0]">
                            <img src="{{ route('foto.anggota', [$anggota, 'type' => 'cutout']) }}"
                                 alt="Foto Cutout"
                                 class="w-full h-full object-contain">
                        </div>
                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Tersedia di Server</span>
                        </div>
                    @else
                        <div class="w-24 h-32 rounded-lg border border-dashed border-slate-300 flex flex-col items-center justify-center p-2 text-center bg-slate-50">
                            <svg class="w-6 h-6 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span class="text-[10px] text-slate-400 font-medium">Tidak ada cutout klien</span>
                        </div>
                        <span class="text-[10px] text-slate-400 mt-2">Fallback otomatis ke engine GD v2</span>
                    @endif
                </div>

                {{-- 3. Aksi Manajemen Admin --}}
                <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-2xs flex flex-col justify-between h-full space-y-3">
                    <div>
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Aksi Tampilan ID Card</span>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Bila cutout browser kurang rapi atau user mengenakan pakaian khusus, Admin dapat beralih ke mode <strong>Original</strong> berbingkai rapi atau menghapus cutout agar fallback ke GD.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <form method="POST" action="{{ route('admin.anggota.foto-mode', $anggota) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            @if($anggota->foto_mode === 'original')
                                <input type="hidden" name="foto_mode" value="cutout">
                                <button type="submit" class="w-full px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs flex items-center justify-center gap-1.5">
                                    <span>Ganti ke Mode Cutout</span>
                                </button>
                            @else
                                <input type="hidden" name="foto_mode" value="original">
                                <button type="submit" class="w-full px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-slate-950 transition shadow-2xs flex items-center justify-center gap-1.5">
                                    <span>Ganti ke Mode Original</span>
                                </button>
                            @endif
                        </form>

                        @if($anggota->foto_cutout_path)
                        <form method="POST" action="{{ route('admin.anggota.cutout.destroy', $anggota) }}" onsubmit="return confirm('Hapus file cutout klien? Sistem akan membuat cutout otomatis dengan GD v2 saat pencetakan ID Card.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 transition shadow-2xs flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Cutout Klien</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Banner Informasi Status Nonaktif --}}
        @if($anggota->user->status === 'nonaktif')
        <div class="mb-6 p-4 sm:p-5 rounded-xl bg-slate-900 text-white flex items-center justify-between flex-wrap gap-4 shadow-md border border-slate-800">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-400 flex items-center justify-center text-lg font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-black text-xs sm:text-sm tracking-wide text-white uppercase">Status Keanggotaan: NONAKTIF</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">Akses Portal Ditangguhkan</span>
                    </div>
                    <div class="text-xs text-slate-300 mt-1">
                        Keterangan: <strong class="text-amber-200 font-medium">"{{ $anggota->catatan_verifikasi ?: 'Penonaktifan oleh Administrator MPKSDI.' }}"</strong>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.anggota.activate', $anggota) }}" onsubmit="return confirm('Aktifkan kembali akun kader ini?')">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-lg shadow-sm flex items-center gap-2 transition text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Aktifkan Kembali Kader</span>
                </button>
            </form>
        </div>
        @endif

        {{-- Alert Notifikasi Pasca Reset Password --}}
        @if(session('wa_reset_url'))
        <div class="mb-6 p-4 rounded-xl {{ session('wa_reset_auto_sent') ? 'bg-emerald-50 border border-emerald-300' : 'bg-amber-50 border border-amber-300' }} text-xs flex items-center justify-between flex-wrap gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full {{ session('wa_reset_auto_sent') ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <div>
                    <div class="font-semibold {{ session('wa_reset_auto_sent') ? 'text-emerald-950' : 'text-amber-950' }}">
                        @if(session('wa_reset_auto_sent'))
                            ✅ Kata sandi baru berhasil di-reset dan otomatis dikirim via WhatsApp ke kader.
                        @else
                            Kata sandi baru berhasil di-reset.
                            @if(session('wa_reset_auto_message'))
                                <span class="font-normal text-amber-800">({{ session('wa_reset_auto_message') }})</span>
                            @endif
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-600 mt-0.5">
                        Kata sandi baru: <strong class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-slate-200">{{ session('reset_password_value') }}</strong>
                    </div>
                </div>
            </div>
            <a href="{{ session('wa_reset_url') }}" target="_blank" rel="noopener noreferrer"
               class="px-3.5 py-2 {{ session('wa_reset_auto_sent') ? 'bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} font-semibold rounded-lg shadow-2xs flex items-center gap-1.5 transition text-xs">
                <span>💬 {{ session('wa_reset_auto_sent') ? 'Kirim Ulang Manual' : 'Kirim Sandi via WhatsApp' }}</span>
                <span>&rarr;</span>
            </a>
        </div>
        @endif

        {{-- Alert Notifikasi Verifikasi / Penolakan via WhatsApp --}}
        @if(session('wa_url'))
        <div class="mb-6 p-4 rounded-xl {{ session('wa_auto_sent') ? 'bg-emerald-50 border border-emerald-300' : 'bg-sky-50 border border-sky-300' }} text-xs flex items-center justify-between flex-wrap gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full {{ session('wa_auto_sent') ? 'bg-emerald-500' : 'bg-sky-500' }}"></span>
                <div>
                    <div class="font-semibold {{ session('wa_auto_sent') ? 'text-emerald-950' : 'text-sky-950' }}">
                        @if(session('wa_auto_sent'))
                            ✅ Pesan notifikasi status pendaftaran berhasil dikirim otomatis ke WhatsApp kader via Fonnte.
                        @else
                            Notifikasi status pendaftaran siap dikirim ke nomor WhatsApp kader.
                            @if(session('wa_auto_message'))
                                <span class="font-normal text-slate-600">({{ session('wa_auto_message') }})</span>
                            @endif
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        Tujuan: <strong class="font-mono text-slate-700">{{ $anggota->no_wa }}</strong>
                    </div>
                </div>
            </div>
            <a href="{{ session('wa_url') }}" target="_blank" rel="noopener noreferrer"
               class="px-3.5 py-2 {{ session('wa_auto_sent') ? 'bg-white border border-emerald-300 text-emerald-800 hover:bg-emerald-100' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} font-semibold rounded-lg shadow-2xs flex items-center gap-1.5 transition text-xs">
                <span aria-hidden="true">💬</span>
                <span>{{ session('wa_auto_sent') ? 'Kirim Ulang Manual' : 'Kirim Notifikasi via WhatsApp' }}</span>
                <span>&rarr;</span>
            </a>
        </div>
        @endif

        {{-- WhatsApp Quick Action Bar --}}
        @if($anggota->no_wa)
        <div class="mb-6 p-4 rounded-xl border flex items-center justify-between flex-wrap gap-3 shadow-2xs" style="background-color:#25d3661a; border-color:#25d3664d;">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs" style="background-color:#25D366;">
                    <svg class="w-5 h-5" style="fill:currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.978-.275-.1-.476-.15-.677.15-.2.3-.777.978-.952 1.179-.176.2-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.895-.798-1.5-1.784-1.676-2.085-.175-.301-.019-.464.132-.613.136-.135.301-.351.452-.527.15-.175.2-.301.301-.501.101-.2.051-.376-.025-.527-.076-.15-.677-1.633-.928-2.235-.244-.586-.493-.506-.677-.515-.176-.009-.376-.009-.577-.009s-.527.075-.802.376c-.276.301-1.053 1.029-1.053 2.51 0 1.48 1.079 2.91 1.229 3.111.15.2 2.122 3.24 5.141 4.544.718.31 1.28.495 1.717.634.721.229 1.378.196 1.897.119.579-.086 1.78-.727 2.031-1.43.251-.703.251-1.304.176-1.43-.076-.125-.276-.2-.577-.35zM12.04 21.786c-1.78 0-3.525-.466-5.066-1.348l-.363-.209-3.766.988 1.005-3.671-.23-.367c-.968-1.542-1.479-3.327-1.478-5.158 0-5.496 4.471-9.967 9.973-9.967 2.663 0 5.166 1.038 7.047 2.922 1.88 1.884 2.914 4.39 2.912 7.054 0 5.498-4.471 9.968-9.972 9.968zm8.455-17.026C18.239 2.497 15.247 1.438 12.04 1.438c-5.83 0-10.574 4.743-10.576 10.575 0 1.863.486 3.682 1.41 5.283L1.13 23.12l6.002-1.574c1.543.841 3.284 1.285 5.048 1.286h.005c5.829 0 10.575-4.744 10.577-10.576.002-2.825-1.096-5.482-3.092-7.478z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block">Komunikasi WhatsApp Kader</span>
                    <span class="text-xs text-emerald-800 font-mono font-semibold">{{ $anggota->no_wa }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, "Assalamu'alaikum wr. wb. Bapak/Ibu {$anggota->nama_lengkap}...") }}"
                   target="_blank" rel="noopener noreferrer"
                   class="px-3.5 py-2 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <span>Buka Chat WA</span>
                </a>
                @if($anggota->user->status === 'aktif')
                <form action="{{ route('admin.anggota.kirim-wa', $anggota) }}" method="POST" class="inline" onsubmit="return confirm('Kirim notifikasi akun aktif secara otomatis via WhatsApp Gateway (Fonnte) ke {{ $anggota->nama_lengkap }}?');">
                    @csrf
                    <button type="submit" title="Kirim otomatis melalui Fonnte WhatsApp Gateway API" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.16 12.04 20.16C10.66 20.16 9.3 19.8 8.1 19.09L7.81 18.92L4.69 19.74L5.52 16.7L5.33 16.4C4.55 15.16 4.14 13.72 4.14 11.91C4.14 7.37 7.84 3.67 12.05 3.67M9.27 7.15C9.07 7.15 8.74 7.22 8.47 7.51C8.2 7.8 7.43 8.53 7.43 10.01C7.43 11.49 8.51 12.92 8.66 13.12C8.81 13.32 10.78 16.36 13.8 17.66C14.52 17.97 15.08 18.16 15.52 18.3C16.24 18.53 16.9 18.5 17.42 18.42C18 18.33 19.2 17.69 19.45 16.99C19.7 16.29 19.7 15.69 19.63 15.56C19.55 15.44 19.35 15.36 19.05 15.21C18.75 15.06 17.27 14.33 17 14.23C16.72 14.13 16.52 14.08 16.32 14.38C16.12 14.68 15.55 15.36 15.37 15.56C15.2 15.76 15.02 15.78 14.72 15.63C14.42 15.48 13.45 15.16 12.3 14.14C11.4 13.34 10.8 12.36 10.62 12.06C10.45 11.75 10.6 11.59 10.75 11.44C10.89 11.3 11.05 11.09 11.2 10.91C11.35 10.74 11.4 10.61 11.5 10.41C11.6 10.21 11.55 10.04 11.47 9.89C11.4 9.74 10.8 8.26 10.55 7.66C10.3 7.07 10.05 7.15 9.87 7.14C9.7 7.13 9.5 7.13 9.27 7.15Z"/>
                        </svg>
                        <span>Kirim Notifikasi Akun Aktif</span>
                        <span class="text-[9.5px] uppercase font-bold tracking-wider bg-emerald-800/80 text-emerald-100 px-1.5 py-0.5 rounded shadow-2xs">Fonnte</span>
                    </button>
                </form>
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, \App\Services\WhatsAppNotificationService::pesanVerifikasiSukses($anggota)) }}"
                   target="_blank" rel="noopener noreferrer"
                   title="Kirim manual via WhatsApp Web"
                   class="px-2.5 py-2 bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-medium transition flex items-center gap-1 shadow-2xs">
                    <span>Manual</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                @elseif($anggota->user->status === 'ditolak')
                <form action="{{ route('admin.anggota.kirim-wa', $anggota) }}" method="POST" class="inline" onsubmit="return confirm('Kirim notifikasi catatan revisi secara otomatis via WhatsApp Gateway (Fonnte) ke {{ $anggota->nama_lengkap }}?');">
                    @csrf
                    <button type="submit" title="Kirim otomatis melalui Fonnte WhatsApp Gateway API" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.16 12.04 20.16C10.66 20.16 9.3 19.8 8.1 19.09L7.81 18.92L4.69 19.74L5.52 16.7L5.33 16.4C4.55 15.16 4.14 13.72 4.14 11.91C4.14 7.37 7.84 3.67 12.05 3.67M9.27 7.15C9.07 7.15 8.74 7.22 8.47 7.51C8.2 7.8 7.43 8.53 7.43 10.01C7.43 11.49 8.51 12.92 8.66 13.12C8.81 13.32 10.78 16.36 13.8 17.66C14.52 17.97 15.08 18.16 15.52 18.3C16.24 18.53 16.9 18.5 17.42 18.42C18 18.33 19.2 17.69 19.45 16.99C19.7 16.29 19.7 15.69 19.63 15.56C19.55 15.44 19.35 15.36 19.05 15.21C18.75 15.06 17.27 14.33 17 14.23C16.72 14.13 16.52 14.08 16.32 14.38C16.12 14.68 15.55 15.36 15.37 15.56C15.2 15.76 15.02 15.78 14.72 15.63C14.42 15.48 13.45 15.16 12.3 14.14C11.4 13.34 10.8 12.36 10.62 12.06C10.45 11.75 10.6 11.59 10.75 11.44C10.89 11.3 11.05 11.09 11.2 10.91C11.35 10.74 11.4 10.61 11.5 10.41C11.6 10.21 11.55 10.04 11.47 9.89C11.4 9.74 10.8 8.26 10.55 7.66C10.3 7.07 10.05 7.15 9.87 7.14C9.7 7.13 9.5 7.13 9.27 7.15Z"/>
                        </svg>
                        <span>Kirim Catatan Revisi</span>
                        <span class="text-[9.5px] uppercase font-bold tracking-wider bg-red-800/80 text-red-100 px-1.5 py-0.5 rounded shadow-2xs">Fonnte</span>
                    </button>
                </form>
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, \App\Services\WhatsAppNotificationService::pesanPerbaikanDitolak($anggota)) }}"
                   target="_blank" rel="noopener noreferrer"
                   title="Kirim manual via WhatsApp Web"
                   class="px-2.5 py-2 bg-white hover:bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs font-medium transition flex items-center gap-1 shadow-2xs">
                    <span>Manual</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Catatan Penolakan (Hanya Ditampilkan Jika Status Ditolak) --}}
        @if($anggota->user->status === 'ditolak' && $anggota->catatan_verifikasi)
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold text-red-900 block mb-0.5">Catatan Verifikasi / Alasan Penolakan:</span>
                <p class="text-red-700 leading-relaxed font-normal">{{ $anggota->catatan_verifikasi }}</p>
            </div>
        </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2 text-xs">
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">NIK:</span> <span class="font-mono font-medium text-slate-900 text-sm">{{ substr($anggota->nik, 0, 6) . '****' . substr($anggota->nik, -4) }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Jenis Kelamin:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Tempat / Tanggal Lahir:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->tempat_lahir }}, {{ $anggota->tanggal_lahir?->format('d M Y') }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Status Pernikahan:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->status_pernikahan }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Nama Ayah Kandung:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->nama_ayah ?? '-' }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Nama Ibu Kandung:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->nama_ibu ?? '-' }}</span></div>
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Alamat Lengkap:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->alamat_rumah }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Desa / Kelurahan:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->desa_kelurahan }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Kecamatan:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->kecamatan }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Kabupaten:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->kabupaten }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Cabang (PCM):</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->pcm ? 'PCM ' . $anggota->pcm : '-' }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Ranting (PRM):</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->prm ?? '-' }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Pekerjaan / Profesi:</span> <span class="font-medium text-slate-900 text-sm">{{ $anggota->pekerjaan ?? ($anggota->jabatan_aum ?? '-') }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Nomor Baku Muh (NBM):</span> <span class="font-mono font-medium text-slate-900 text-sm">{{ $anggota->nbm ?? '-' }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Pendidikan Terakhir:</span> <span class="font-medium text-slate-900 text-sm">{{ config('arqam.opsi_form.pendidikan_terakhir.' . $anggota->pendidikan_terakhir, $anggota->pendidikan_terakhir ?: '-') }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">No. WhatsApp:</span> <span class="font-mono font-medium text-slate-900 text-sm">{{ $anggota->no_wa }}</span></div>
            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60"><span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Email:</span> <span class="font-mono font-medium text-slate-900 text-sm">{{ $anggota->email ?? '-' }}</span></div>

            @if($anggota->pendidikan_sd || $anggota->pendidikan_smp || $anggota->pendidikan_sma || $anggota->pendidikan_s1)
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Riwayat Institusi Pendidikan:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1.5">
                    @if($anggota->pendidikan_sd)
                        <div class="text-xs text-slate-700 bg-white p-2 rounded-lg border border-slate-200"><strong class="text-slate-500 block text-[11px] mb-0.5">SD / MI:</strong> {{ $anggota->pendidikan_sd }}</div>
                    @endif
                    @if($anggota->pendidikan_smp)
                        <div class="text-xs text-slate-700 bg-white p-2 rounded-lg border border-slate-200"><strong class="text-slate-500 block text-[11px] mb-0.5">SMP / MTs:</strong> {{ $anggota->pendidikan_smp }}</div>
                    @endif
                    @if($anggota->pendidikan_sma)
                        <div class="text-xs text-slate-700 bg-white p-2 rounded-lg border border-slate-200"><strong class="text-slate-500 block text-[11px] mb-0.5">SMA / SMK / MA:</strong> {{ $anggota->pendidikan_sma }}</div>
                    @endif
                    @if($anggota->pendidikan_s1)
                        <div class="text-xs text-slate-700 bg-white p-2 rounded-lg border border-slate-200"><strong class="text-slate-500 block text-[11px] mb-0.5">Perguruan Tinggi / S1:</strong> {{ $anggota->pendidikan_s1 }}</div>
                    @endif
                </div>
            </div>
            @endif

            @if(!empty($anggota->keaktifan_muhammadiyah))
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1.5 text-xs">Keaktifan Pimpinan Muhammadiyah:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($anggota->keaktifan_muhammadiyah as $item)
                        @php
                            $jab = $anggota->jabatan_muhammadiyah[$item] ?? null;
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white border border-slate-200 text-xs text-slate-800">
                            <strong>{{ $item }}</strong>
                            @if($jab)
                                &mdash; <span class="text-sky-700 font-semibold">{{ $jab }}</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($anggota->keaktifan_ortom))
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1.5 text-xs">Keaktifan di ORTOM:</span>
                <div class="flex flex-wrap gap-2">
                    @foreach($anggota->keaktifan_ortom as $item)
                        @php
                            $jab = $anggota->jabatan_ortom[$item] ?? null;
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white border border-slate-200 text-xs text-slate-800">
                            <strong>{{ $item }}</strong>
                            @if($jab)
                                &mdash; <span class="text-sky-700 font-semibold">{{ $jab }}</span>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if($anggota->aktifitas_jamaah_masjid)
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Aktivitas Jamaah di Masjid:</span>
                <span class="text-slate-900 text-sm leading-relaxed font-normal">{{ $anggota->aktifitas_jamaah_masjid }}</span>
            </div>
            @endif

            <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Keaktifan Takmir Masjid:</span>
                <span class="font-medium text-slate-900 text-sm">
                    {{ $anggota->keaktifan_takmir ?? 'Tidak' }}
                    @if(($anggota->keaktifan_takmir === 'Ya' || strtolower($anggota->keaktifan_takmir ?? '') === 'ya') && $anggota->jabatan_takmir)
                        &mdash; <span class="text-sky-700">({{ $anggota->jabatan_takmir }})</span>
                    @endif
                </span>
            </div>

            @if($anggota->keaktifan_kemasyarakatan)
            <div class="sm:col-span-2 p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/60">
                <span class="font-semibold text-slate-500 uppercase tracking-wider block mb-1 text-xs">Keaktifan di Kemasyarakatan (RT/RW/BPD/Karang Taruna):</span>
                <span class="text-slate-900 text-sm leading-relaxed font-normal">{{ $anggota->keaktifan_kemasyarakatan }}</span>
            </div>
            @endif

            {{-- Riwayat Keikutsertaan Baitul Arqam & Perkaderan --}}
            @php
                $riwayatEvents = $anggota->eventPeserta ?? collect();
                $riwayatCustom = $anggota->custom_fields['riwayat_ba'] ?? [];
                $penilaianMap = $anggota->penilaianAkhir ? $anggota->penilaianAkhir->keyBy('event_id') : collect();
            @endphp
            @if($riwayatEvents->isNotEmpty() || !empty($riwayatCustom))
            <div class="sm:col-span-2 pt-4 border-t border-slate-200">
                <span class="font-bold text-[#063A60] uppercase tracking-wider block mb-3 text-xs flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <span>Riwayat Keikutsertaan Perkaderan / Baitul Arqam</span>
                    </span>
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        {{ $riwayatEvents->count() ?: count($riwayatCustom) }} Kegiatan Tercatat
                    </span>
                </span>

                <div class="space-y-2.5">
                    @forelse($riwayatEvents as $ep)
                        @php
                            $ev = $ep->event;
                            $penilaian = $penilaianMap->get($ep->event_id);
                            $statusLulus = $penilaian?->status_kelulusan ?? ($ep->status_kelulusan ?? 'Terdaftar');
                            $isLulus = strtolower($statusLulus) === 'lulus';
                        @endphp
                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-2xs hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">{{ $ev?->nama_event ?? 'Kegiatan Perkaderan' }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $ev?->lokasi ?? '-' }}
                                    </span>
                                    <span>&bull;</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $ev?->tanggal_mulai?->format('d M Y') ?? '-' }}
                                        @if($ev?->tanggal_selesai && $ev->tanggal_selesai->ne($ev->tanggal_mulai))
                                            - {{ $ev->tanggal_selesai->format('d M Y') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-center">
                                @if($isLulus)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Lulus
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($statusLulus) }}
                                    </span>
                                @endif
                                @if($ev)
                                    <a href="{{ route('admin.events.show', $ev) }}" class="text-xs text-sky-600 hover:text-sky-800 hover:underline font-medium">Detail Event &rarr;</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        @foreach($riwayatCustom as $rc)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-white shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="font-bold text-slate-900 text-sm">{{ $rc['nama_event'] ?? 'Baitul Arqam' }}</div>
                                    <div class="text-xs text-slate-500">{{ $rc['lokasi'] ?? '-' }} &bull; {{ $rc['tanggal'] ?? '-' }}</div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ $rc['status'] ?? 'Lulus' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
            @endif

            @php
                $validCustomFields = collect($anggota->custom_fields ?? [])
                    ->filter(fn($val, $key) => $key !== 'riwayat_ba' && filled($val))
                    ->all();
            @endphp
            @if(!empty($validCustomFields) && count($validCustomFields) > 0)
                <div class="sm:col-span-2 pt-3 border-t border-slate-200">
                    <span class="font-bold text-sky-900 uppercase tracking-wider block mb-2 text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Data Pertanyaan Tambahan Formulir (Kustom):</span>
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($validCustomFields as $kode => $jawaban)
                            @php
                                $pertanyaanModel = \App\Models\FormPertanyaan::where('kode', $kode)->first();
                                $labelTampil = $pertanyaanModel ? $pertanyaanModel->label : ucwords(str_replace(['custom_', '_'], ['', ' '], $kode));
                            @endphp
                            <div class="p-3 rounded-lg bg-white border border-slate-200 shadow-2xs">
                                <span class="text-slate-500 block text-[11px] font-semibold uppercase tracking-wider mb-0.5">{{ $labelTampil }}:</span>
                                <span class="text-slate-900 text-xs font-bold">
                                    @if(is_array($jawaban))
                                        {{ implode(', ', $jawaban) }}
                                    @else
                                        {{ $jawaban ?: '-' }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
