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
                        {{ $isCabang ? ('PCM ' . $cabangNama) : 'Master Data Perkaderan PDM' }}
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
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    {{ $isCabang ? ('Pangkalan Data Kader PCM ' . $cabangNama) : 'Pangkalan Data Anggota & Kader' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-xl">
                    {{ $isCabang ? ('Kelola verifikasi pendaftaran kader, tinjau biodata anggota cabang, dan unduh data resmi PCM ' . $cabangNama . '.') : 'Master database kader Muhammadiyah Karanganyar untuk penugasan dan kepesertaan Baitul Arqam.' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                {{-- Tombol Unduh Data Excel (Tersedia untuk Admin Cabang & Admin Daerah) --}}
                <a href="{{ route('admin.anggota.export', request()->query()) }}" 
                   title="Unduh data kader ke format Excel (.xlsx)"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-2xs shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ $isCabang ? 'Unduh Data Cabang' : 'Unduh Excel' }}</span>
                </a>

                @if(!$isCabang)
                {{-- Tombol Impor Excel hanya untuk Admin Daerah --}}
                <a href="{{ route('admin.anggota.import') }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold transition shadow-2xs shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Impor Excel</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Filter & Search Multi-Kategori Toolbar (Muhammadiyah, AUM, ORTOM) --}}
        <form method="GET" class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2.5">
            {{-- Search Box --}}
            <div class="relative flex-1 min-w-[200px] sm:min-w-[240px]">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ $isCabang ? 'Cari nama, NIK, PRM, AUM...' : 'Cari nama, NIK, PCM, AUM...' }}"
                    class="pl-8 pr-3 py-2 text-xs rounded-lg border border-slate-200/90 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 w-full bg-slate-50/70 focus:bg-white outline-none shadow-2xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Filter Kategori Basis --}}
            <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                <option value="">Semua Pilar</option>
                <option value="aum" @selected(($kategori ?? '') === 'aum')>Amal Usaha Muhammadiyah (AUM)</option>
                <option value="ortom" @selected(($kategori ?? '') === 'ortom')>Organisasi Otonom (ORTOM)</option>
                <option value="muhammadiyah" @selected(($kategori ?? '') === 'muhammadiyah')>Pimpinan Muhammadiyah</option>
            </select>

            {{-- Filter Spesifik Nama AUM --}}
            <select name="aum" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer max-w-[200px] truncate">
                <option value="">Semua Nama AUM</option>
                @foreach($aumList ?? [] as $item)
                    <option value="{{ $item }}" @selected(($aum ?? '') === $item)>{{ $item }}</option>
                @endforeach
            </select>

            {{-- Filter Spesifik ORTOM --}}
            <select name="ortom" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                <option value="">Semua ORTOM</option>
                @foreach($ortomList ?? [] as $item)
                    <option value="{{ $item }}" @selected(($ortom ?? '') === $item)>{{ $item }}</option>
                @endforeach
            </select>

            @if(!$isCabang)
            {{-- Filter PCM (Cabang) --}}
            <select name="cabang" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                <option value="">Semua Cabang (PCM)</option>
                @foreach($pcmList ?? [] as $item)
                    <option value="{{ $item }}" @selected(($cabang ?? '') === $item)>PCM {{ $item }}</option>
                @endforeach
            </select>
            @endif

            {{-- Filter Status Akun --}}
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                <option value="">Semua Status</option>
                <option value="menunggu_verifikasi" @selected(($status ?? '')==='menunggu_verifikasi')>Menunggu Verifikasi</option>
                <option value="aktif" @selected(($status ?? '')==='aktif')>Aktif</option>
                <option value="ditolak" @selected(($status ?? '')==='ditolak')>Ditolak</option>
                <option value="nonaktif" @selected(($status ?? '')==='nonaktif')>Nonaktif</option>
            </select>

            {{-- Sorting --}}
            <select name="sort" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200/90 rounded-lg text-xs font-medium text-slate-700 outline-none focus:border-sky-500 shadow-2xs cursor-pointer">
                <option value="">Urutan: Terbaru</option>
                <option value="nama_asc" @selected(($sort ?? '')==='nama_asc')>Nama Kader (A - Z)</option>
                <option value="nama_desc" @selected(($sort ?? '')==='nama_desc')>Nama Kader (Z - A)</option>
                <option value="aum_asc" @selected(($sort ?? '')==='aum_asc')>Nama AUM (A - Z)</option>
                <option value="pcm_asc" @selected(($sort ?? '')==='pcm_asc')>Cabang PCM (A - Z)</option>
            </select>

            <button type="submit" class="px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] text-white rounded-lg text-xs font-semibold transition shadow-2xs">
                Terapkan
            </button>
            @if(!empty($q) || !empty($status) || !empty($kategori) || !empty($aum) || !empty($ortom) || !empty($cabang) || !empty($sort))
                <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center justify-center px-2 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 transition">Reset</a>
            @endif
        </form>
    </div>

    {{-- Data Table Card --}}
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200/80 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Nama Kader</th>
                        <th class="py-3.5 px-5">NIK / NBM</th>
                        <th class="py-3.5 px-5">Kategori / Afiliasi</th>
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
                                    class="w-9 h-12 aspect-[3/4] rounded-lg object-cover object-top border border-slate-200 bg-slate-100 shrink-0 shadow-2xs">
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
                        <td class="py-3.5 px-5 text-xs">
                            <div class="space-y-1 max-w-[260px]">
                                {{-- AUM Badge --}}
                                @if(!empty($a->nama_aum) || !empty($a->jabatan_aum))
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 border border-sky-200 shrink-0">AUM</span>
                                        <span class="font-bold text-slate-800 truncate" title="{{ $a->nama_aum }}">{{ $a->nama_aum ?: 'Amal Usaha' }}</span>
                                        @if($a->jabatan_aum)
                                            <span class="text-slate-500 font-normal truncate block w-full text-[11px]">&bull; {{ $a->jabatan_aum }}</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- ORTOM Badge --}}
                                @php
                                    $cleanOrtom = is_array($a->keaktifan_ortom) ? array_filter($a->keaktifan_ortom, fn($x) => $x !== 'Tidak aktif') : [];
                                @endphp
                                @if(!empty($cleanOrtom))
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">ORTOM</span>
                                        <span class="text-slate-700 font-medium truncate" title="{{ implode(', ', $cleanOrtom) }}">{{ implode(', ', $cleanOrtom) }}</span>
                                    </div>
                                @endif

                                {{-- Cabang / PRM --}}
                                <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                    <span>PCM {{ $a->pcm ?: ($a->kecamatan ?: '-') }}</span>
                                    @if($a->prm)
                                        <span>&bull; {{ $a->prm }}</span>
                                    @endif
                                </div>
                            </div>
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
                        <td class="py-3.5 px-5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                {{-- Aksi Cepat Kontekstual Berdasarkan Status --}}
                                @if($a->user->status === 'menunggu_verifikasi')
                                    <form method="POST" action="{{ route('admin.anggota.verify', $a) }}" class="inline"
                                        onsubmit="return confirm('Verifikasi & aktifkan akun kader {{ $a->nama_lengkap }}?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-xs font-semibold shadow-2xs transition" title="Verifikasi & Aktifkan Pendaftaran Langsung">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Verifikasi</span>
                                        </button>
                                    </form>
                                @elseif($a->user->status === 'nonaktif')
                                    <form method="POST" action="{{ route('admin.anggota.activate', $a) }}" class="inline"
                                        onsubmit="return confirm('Aktifkan kembali akun kader {{ $a->nama_lengkap }}?')">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold shadow-2xs transition" title="Aktifkan Kembali Akun Kader">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>Aktifkan</span>
                                        </button>
                                    </form>
                                @endif

                                {{-- Icon WhatsApp Asli / Real --}}
                                @if($a->no_wa)
                                    <a href="https://api.whatsapp.com/send?phone={{ \App\Services\WhatsAppNotificationService::formatNomor($a->no_wa) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       title="Chat WhatsApp ke {{ $a->nama_lengkap }} ({{ $a->no_wa }})"
                                       class="wa-btn inline-flex items-center justify-center w-8 h-8 rounded-lg border transition shadow-2xs group"
                                       aria-label="Kirim Pesan WhatsApp">
                                        <svg class="w-4 h-4 fill-current group-hover:scale-110 transition" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.978-.275-.1-.476-.15-.677.15-.2.3-.777.978-.952 1.179-.176.2-.351.226-.652.075-.301-.15-1.272-.469-2.423-1.496-.895-.798-1.5-1.784-1.676-2.085-.175-.301-.019-.464.132-.613.136-.135.301-.351.452-.527.15-.175.2-.301.301-.501.101-.2.051-.376-.025-.527-.076-.15-.677-1.633-.928-2.235-.244-.586-.493-.506-.677-.515-.176-.009-.376-.009-.577-.009s-.527.075-.802.376c-.276.301-1.053 1.029-1.053 2.51 0 1.48 1.079 2.91 1.229 3.111.15.2 2.122 3.24 5.141 4.544.718.31 1.28.495 1.717.634.721.229 1.378.196 1.897.119.579-.086 1.78-.727 2.031-1.43.251-.703.251-1.304.176-1.43-.076-.125-.276-.2-.577-.35zM12.04 21.786c-1.78 0-3.525-.466-5.066-1.348l-.363-.209-3.766.988 1.005-3.671-.23-.367c-.968-1.542-1.479-3.327-1.478-5.158 0-5.496 4.471-9.967 9.973-9.967 2.663 0 5.166 1.038 7.047 2.922 1.88 1.884 2.914 4.39 2.912 7.054 0 5.498-4.471 9.968-9.972 9.968zm8.455-17.026C18.239 2.497 15.247 1.438 12.04 1.438c-5.83 0-10.574 4.743-10.576 10.575 0 1.863.486 3.682 1.41 5.283L1.13 23.12l6.002-1.574c1.543.841 3.284 1.285 5.048 1.286h.005c5.829 0 10.575-4.744 10.577-10.576.002-2.825-1.096-5.482-3.092-7.478z"/>
                                        </svg>
                                    </a>
                                @endif

                                {{-- Tombol Detail --}}
                                <a href="{{ route('admin.anggota.show', $a) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-xs font-semibold text-slate-700 transition shadow-2xs" title="Lihat profil dan dokumen kader">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>Detail</span>
                                </a>

                                {{-- Tombol Hapus --}}
                                <form method="POST" action="{{ route('admin.anggota.destroy', $a) }}" class="inline"
                                    onsubmit="return confirm('Hapus permanen data kader {{ addslashes($a->nama_lengkap) }}?\n\nTindakan ini akan menghapus data profil dan akun login. Lanjutkan?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 hover:border-rose-300 text-rose-600 transition shadow-2xs" title="Hapus Data Kader">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 text-xs">
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
