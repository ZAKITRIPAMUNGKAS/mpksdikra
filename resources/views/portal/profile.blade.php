@extends('layouts.app')

@section('title', 'Biodata Kader — ' . ($anggota?->nama_lengkap ?? auth()->user()->name))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    tab: 'identitas',
    selectedEdu: {{ \Illuminate\Support\Js::from(old('pendidikan_terakhir', $anggota?->pendidikan_terakhir ?? '')) }},
    shouldShowEdu(lvl) {
        if (!this.selectedEdu) return false;
        if (this.selectedEdu === 'Lainnya') {
            return lvl === 'Lainnya' || lvl === 'SD';
        }
        const map = {
            'SD': 1,
            'SMP': 2,
            'SMA': 3,
            'D3': 4,
            'S1': 4,
            'S2': 5,
            'S3': 6,
        };
        const current = map[this.selectedEdu] || 0;
        const target = map[lvl] || 0;
        return current >= target;
    }
}">
    {{-- Header Banner with Avatar & Status --}}
    <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
        <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.18]" />

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                @if($anggota?->foto)
                    <img src="{{ route('foto.anggota', $anggota) }}"
                         alt="{{ $anggota->nama_lengkap }}"
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover border border-sky-400/80 shadow-2xs shrink-0"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-slate-950 to-[#005c8a] border border-sky-400/80 items-center justify-center text-[#f8bb00] text-xl font-bold shrink-0 shadow-2xs" style="display:none;">
                        {{ strtoupper(substr($anggota->nama_lengkap ?? auth()->user()->name, 0, 1)) }}
                    </div>
                @else
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gradient-to-br from-slate-950 to-[#005c8a] border border-sky-400/80 flex items-center justify-center text-[#f8bb00] text-xl font-bold shrink-0 shadow-2xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $anggota?->nama_lengkap ?? auth()->user()->name }}</h1>
                        @if($anggota?->catatan_verifikasi)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300/80">
                                Catatan Verifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300/80">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Terdaftar di MPKSDI
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                        <span>NBM: <strong class="text-slate-700 font-mono">{{ $anggota?->nbm ?: 'Belum diisi' }}</strong></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Cabang: <strong class="text-slate-700">{{ $anggota?->pcm ?: ($anggota?->kecamatan ?: '-') }}</strong></span>
                        @if($anggota?->prm)
                            <span class="text-slate-300">&bull;</span>
                            <span>Ranting: <strong class="text-slate-700">{{ $anggota->prm }}</strong></span>
                        @endif
                        <span class="text-slate-300">&bull;</span>
                        <span>Status: <strong class="text-sky-700 font-semibold">{{ $anggota?->status_pernikahan ?: '-' }}</strong></span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('portal.profile.cetak-pdf') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-sky-800 bg-sky-50 hover:bg-sky-100 border border-sky-300 transition shadow-2xs">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Lihat Biodata (PDF)</span>
                </a>
                <a href="{{ route('portal.profile.cetak-pdf') }}?download" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 transition shadow-2xs">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh PDF</span>
                </a>
                <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-slate-700 bg-white hover:bg-sky-50 hover:text-sky-800 border border-slate-200/90 transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Portal</span>
                </a>
            </div>
        </div>

        @if($anggota?->catatan_verifikasi)
            <div class="mt-4 p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold">Catatan Verifikasi Admin:</span> {{ $anggota->catatan_verifikasi }}
                </div>
            </div>
        @endif
    </div>

    @if($anggota)
    {{-- Tab Selector --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-2 flex gap-2 shadow-xs">
        <button type="button" @click="tab = 'identitas'"
            :class="tab === 'identitas' ? 'bg-sky-600 text-white shadow-sm font-extrabold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-bold'"
            class="flex-1 py-3 px-3 text-xs sm:text-sm md:text-base rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>1. Identitas &amp; Domisili</span>
        </button>
        <button type="button" @click="tab = 'agama'"
            :class="tab === 'agama' ? 'bg-sky-600 text-white shadow-sm font-extrabold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-bold'"
            class="flex-1 py-3 px-3 text-xs sm:text-sm md:text-base rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>2. Ibadah &amp; Keagamaan</span>
        </button>
        <button type="button" @click="tab = 'kemuhammadiyahan'"
            :class="tab === 'kemuhammadiyahan' ? 'bg-sky-600 text-white shadow-sm font-extrabold' : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100 font-bold'"
            class="flex-1 py-3 px-3 text-xs sm:text-sm md:text-base rounded-xl transition text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <span>3. Kemuhammadiyahan</span>
        </button>
    </div>

    {{-- Edit Form --}}
    <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Tab 1: Identitas & Domisili --}}
        <div x-show="tab === 'identitas'" class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="border-b-2 border-slate-100 pb-4">
                <h2 class="text-lg sm:text-xl font-black text-slate-900">Identitas Pribadi &amp; Kontak</h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">Informasi dasar kader untuk arsip administrasi resmi persyarikatan.</p>
            </div>

            {{-- Pasfoto Upload & Proportional Face Fitter --}}
            <x-pasfoto-fitter name="foto" :required="false" inputId="foto-profile-input" :currentPhoto="$anggota->foto ? route('foto.anggota', $anggota) : null" label="Ganti Pasfoto Resmi" />
            @error('foto')
                <p class="text-xs sm:text-sm text-red-600 font-bold mt-1">{{ $message }}</p>
            @enderror

            <div>
                <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Nama Lengkap (beserta gelar) <span class="text-red-500">*</span></label>
                <input name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}" required
                    class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                    <input name="tempat_lahir" value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}" required
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota->tanggal_lahir?->format('Y-m-d')) }}" required
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Status Pernikahan <span class="text-red-500">*</span></label>
                    <select name="status_pernikahan" required class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        @foreach(\App\Models\FormPertanyaan::opsi('status_pernikahan', config('arqam.opsi_form.status_pernikahan')) as $v)
                        <option value="{{ $v }}" @selected(old('status_pernikahan', $anggota->status_pernikahan)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Alamat Lengkap Domisili <span class="text-red-500">*</span></label>
                <textarea name="alamat_rumah" required rows="3"
                    class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none resize-none transition font-medium leading-relaxed">{{ old('alamat_rumah', $anggota->alamat_rumah) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Desa / Kelurahan <span class="text-red-500">*</span></label>
                    <input name="desa_kelurahan" value="{{ old('desa_kelurahan', $anggota->desa_kelurahan) }}" required
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Kecamatan <span class="text-red-500">*</span></label>
                    <input name="kecamatan" value="{{ old('kecamatan', $anggota->kecamatan) }}" required
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Kabupaten <span class="text-red-500">*</span></label>
                    <input name="kabupaten" value="{{ old('kabupaten', $anggota->kabupaten) }}" required
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if(\App\Models\FormPertanyaan::isAktif('nama_ayah'))
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                        {{ \App\Models\FormPertanyaan::label('nama_ayah', 'Nama Ayah Kandung') }}
                        <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                    </label>
                    <input name="nama_ayah" value="{{ old('nama_ayah', $anggota->nama_ayah) }}"
                        placeholder="{{ \App\Models\FormPertanyaan::placeholder('nama_ayah', 'Contoh: H. Ahmad Syukri') }}"
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                @endif
                @if(\App\Models\FormPertanyaan::isAktif('nama_ibu'))
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                        {{ \App\Models\FormPertanyaan::label('nama_ibu', 'Nama Ibu Kandung') }}
                        <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                    </label>
                    <input name="nama_ibu" value="{{ old('nama_ibu', $anggota->nama_ibu) }}"
                        placeholder="{{ \App\Models\FormPertanyaan::placeholder('nama_ibu', 'Contoh: Hj. Siti Maryam') }}"
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Pekerjaan / Profesi Utama</label>
                    <input name="pekerjaan" value="{{ old('pekerjaan', $anggota->pekerjaan ?: $anggota->jabatan_aum) }}" placeholder="Contoh: Guru / PNS / Wiraswasta / Karyawan"
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Nomor Baku Muhammadiyah (NBM)</label>
                    <input name="nbm" value="{{ old('nbm', $anggota->nbm) }}" placeholder="Contoh: 1084201"
                        inputmode="numeric" pattern="[0-9]*" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 13" oninput="this.value = this.value.replace(/\D/g, '')"
                        class="w-full px-4 py-3.5 text-base font-mono bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
            </div>

            {{-- Riwayat Pendidikan Kader Bertingkat --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-600"></span>
                    <h4 class="text-sm sm:text-base font-black text-slate-900">Riwayat Pendidikan Kader</h4>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5">
                        Pendidikan Terakhir <span class="text-sky-700 text-xs font-normal">(Pilih Terlebih Dahulu)</span>
                    </label>
                    <select name="pendidikan_terakhir" x-model="selectedEdu"
                        class="w-full px-4 py-3 text-base font-semibold text-slate-900 bg-white border-2 border-slate-300 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition min-h-[48px]">
                        <option value="">-- Pilih Pendidikan Terakhir --</option>
                        @foreach(config('arqam.opsi_form.pendidikan_terakhir') as $k => $label)
                            <option value="{{ $k }}" @selected(old('pendidikan_terakhir', $anggota->pendidikan_terakhir) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="selectedEdu" x-transition class="space-y-3 pt-1" style="display: none;">
                    <div x-show="shouldShowEdu('SD')" x-transition>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            <span x-show="selectedEdu !== 'Lainnya'">Nama SD / MI / Sederajat</span>
                            <span x-show="selectedEdu === 'Lainnya'">Nama Lembaga Pendidikan / Pesantren / Keterangan</span>
                        </label>
                        <input name="pendidikan_sd" value="{{ old('pendidikan_sd', $anggota->pendidikan_sd) }}"
                            :placeholder="selectedEdu === 'Lainnya' ? 'Contoh: Ponpes Isy Karima / PKBM' : 'Contoh: MIM Karanganyar / SDN 1 Karanganyar'"
                            class="w-full px-4 py-2.5 text-sm bg-white border-2 border-slate-200 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[44px]">
                    </div>

                    <div x-show="shouldShowEdu('SMP')" x-transition>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama SMP / MTs / Sederajat</label>
                        <input name="pendidikan_smp" value="{{ old('pendidikan_smp', $anggota->pendidikan_smp) }}"
                            placeholder="Contoh: SMP Muhammadiyah 1 Karanganyar / MTsN 1 Karanganyar"
                            class="w-full px-4 py-2.5 text-sm bg-white border-2 border-slate-200 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[44px]">
                    </div>

                    <div x-show="shouldShowEdu('SMA')" x-transition>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama SMA / SMK / MA / Sederajat</label>
                        <input name="pendidikan_sma" value="{{ old('pendidikan_sma', $anggota->pendidikan_sma) }}"
                            placeholder="Contoh: SMA Muhammadiyah 1 Karanganyar / SMKN 1 Karanganyar"
                            class="w-full px-4 py-2.5 text-sm bg-white border-2 border-slate-200 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[44px]">
                    </div>

                    <div x-show="shouldShowEdu('D3')" x-transition>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Perguruan Tinggi / Universitas (Kampus &amp; Program Studi)</label>
                        <input name="pendidikan_s1" value="{{ old('pendidikan_s1', $anggota->pendidikan_s1) }}"
                            placeholder="Contoh: Universitas Muhammadiyah Surakarta - Pendidikan Agama Islam"
                            class="w-full px-4 py-2.5 text-sm bg-white border-2 border-slate-200 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[44px]">
                    </div>
                </div>
            </div>

            {{-- Pertanyaan Kustom Seksi Biodata Diri --}}
            <x-form-custom-fields :questions="$customQuestions['diri'] ?? null" :values="old('custom_fields', $anggota->custom_fields ?? [])" />

            <div class="pt-5 border-t-2 border-slate-100 flex justify-end">
                <button type="button" @click="tab = 'agama'"
                    class="min-h-[50px] px-6 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-sm sm:text-base rounded-2xl transition flex items-center gap-2 shadow-sm">
                    <span>Lanjut ke Data Keagamaan</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Tab 2: Ibadah & Pemahaman Agama --}}
        <div x-show="tab === 'agama'" class="bg-white rounded-2xl border-2 border-slate-200 p-6 sm:p-8 shadow-sm space-y-6" style="display: none;">
            <div class="border-b-2 border-slate-100 pb-4">
                <h2 class="text-lg sm:text-xl font-black text-slate-900">Pembiasaan Ibadah &amp; Pemahaman Keagamaan</h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">Pemetaan kompetensi keislaman untuk standarisasi kurikulum Baitul Arqam.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Kemampuan Baca Al-Qur'an</label>
                    <select name="kemampuan_baca_quran" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Pilih Kemampuan --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('kemampuan_baca_quran', config('arqam.opsi_form.kemampuan_baca_quran')) as $v)
                        <option value="{{ $v }}" @selected(old('kemampuan_baca_quran', $anggota->kemampuan_baca_quran)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Hafalan Al-Qur'an</label>
                    <select name="hafalan_quran" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Pilih Capaian Hafalan --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('hafalan_quran', config('arqam.opsi_form.hafalan_quran')) as $v)
                        <option value="{{ $v }}" @selected(old('hafalan_quran', $anggota->hafalan_quran)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Sholat Berjamaah di Masjid</label>
                    <select name="aktivitas_sholat_masjid" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Frekuensi --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('aktivitas_sholat_masjid', config('arqam.opsi_form.aktivitas_sholat_masjid')) as $v)
                        <option value="{{ $v }}" @selected(old('aktivitas_sholat_masjid', $anggota->aktivitas_sholat_masjid)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Kehadiran Kajian Keagamaan</label>
                    <select name="aktivitas_kajian_agama" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Frekuensi --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('aktivitas_kajian_agama', config('arqam.opsi_form.aktivitas_kajian_agama')) as $v)
                        <option value="{{ $v }}" @selected(old('aktivitas_kajian_agama', $anggota->aktivitas_kajian_agama)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Koleksi Buku Agama di Rumah</label>
                    <select name="jumlah_buku_agama" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Jumlah Buku --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('jumlah_buku_agama', config('arqam.opsi_form.jumlah_buku_agama')) as $v)
                        <option value="{{ $v }}" @selected(old('jumlah_buku_agama', $anggota->jumlah_buku_agama)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Pertanyaan Kustom Seksi Keagamaan --}}
            <x-form-custom-fields :questions="$customQuestions['agama'] ?? null" :values="old('custom_fields', $anggota->custom_fields ?? [])" />

            <div class="flex items-center justify-between gap-3 pt-5 border-t-2 border-slate-100">
                <button type="button" @click="tab = 'identitas'"
                    class="min-h-[50px] px-6 border-2 border-slate-300 text-slate-700 font-extrabold rounded-2xl hover:bg-slate-100 active:bg-slate-200 transition text-sm sm:text-base flex items-center justify-center gap-2 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali</span>
                </button>
                <button type="button" @click="tab = 'kemuhammadiyahan'"
                    class="min-h-[50px] px-6 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-sm sm:text-base rounded-2xl transition flex items-center justify-center gap-2 whitespace-nowrap shadow-sm">
                    <span>Lanjut: Kemuhammadiyahan</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Tab 3: Kemuhammadiyahan & Pengabdian Kemasyarakatan --}}
        {{-- Tab 3: Kemuhammadiyahan & Pengabdian Kemasyarakatan (Ramah Usia 40+) --}}
        <div x-show="tab === 'kemuhammadiyahan'" class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6" style="display: none;"
             x-data="{ 
                 isTakmir: '{{ old('keaktifan_takmir', $anggota->keaktifan_takmir ?? 'Tidak') }}',
                 takmirRole: '{{ addslashes(old('jabatan_takmir', $anggota->jabatan_takmir ?? '')) }}',
                 masjidText: '{{ addslashes(old('aktifitas_jamaah_masjid', $anggota->aktifitas_jamaah_masjid ?? '')) }}',
                 masyarakatText: '{{ addslashes(old('keaktifan_kemasyarakatan', $anggota->keaktifan_kemasyarakatan ?? '')) }}',
                 checkedMuhammadiyah: {{ json_encode(old('keaktifan_muhammadiyah', $anggota->keaktifan_muhammadiyah ?? [])) }},
                 checkedOrtom: {{ json_encode(old('keaktifan_ortom', $anggota->keaktifan_ortom ?? [])) }},
                 selectedAum: '{{ old('nama_aum', in_array($anggota->nama_aum, config('arqam.opsi_form.nama_aum', [])) ? $anggota->nama_aum : (!empty($anggota->nama_aum) ? 'Lainnya' : '')) }}',
                 customAum: '{{ addslashes(old('nama_aum_custom', !in_array($anggota->nama_aum, config('arqam.opsi_form.nama_aum', [])) ? ($anggota->nama_aum ?? '') : '')) }}',
                 selectedJabatanAum: '{{ old('jabatan_aum', in_array($anggota->jabatan_aum, config('arqam.opsi_form.jabatan_aum', [])) ? $anggota->jabatan_aum : (!empty($anggota->jabatan_aum) ? 'Lainnya' : '')) }}',
                 customJabatanAum: '{{ addslashes(old('jabatan_aum_custom', !in_array($anggota->jabatan_aum, config('arqam.opsi_form.jabatan_aum', [])) ? ($anggota->jabatan_aum ?? '') : '')) }}',

                 isMuhammadiyahChecked(val) {
                     return Array.isArray(this.checkedMuhammadiyah) && this.checkedMuhammadiyah.includes(val);
                 },
                 toggleMuhammadiyah(val, isChecked) {
                     if (!Array.isArray(this.checkedMuhammadiyah)) this.checkedMuhammadiyah = [];
                     if (isChecked) {
                         if (!this.checkedMuhammadiyah.includes(val)) this.checkedMuhammadiyah.push(val);
                     } else {
                         this.checkedMuhammadiyah = this.checkedMuhammadiyah.filter(x => x !== val);
                     }
                 },
                 isOrtomChecked(val) {
                     return Array.isArray(this.checkedOrtom) && this.checkedOrtom.includes(val);
                 },
                 toggleOrtom(val, isChecked) {
                     if (!Array.isArray(this.checkedOrtom)) this.checkedOrtom = [];
                     if (isChecked) {
                         if (!this.checkedOrtom.includes(val)) this.checkedOrtom.push(val);
                     } else {
                         this.checkedOrtom = this.checkedOrtom.filter(x => x !== val);
                     }
                 },
                 
                 masjidPresets: [
                     'Sholat Berjamaah 5 Waktu',
                     'Sholat Subuh / Isya Berjamaah',
                     'Pengajian Rutin Subuh / Malam',
                     'Pengajian Ahad Pagi',
                     'Tadarus / Kajian Al-Qur\'an',
                     'Panitia Kegiatan Masjid / PHBI'
                 ],
                 takmirPresets: [
                     'Ketua Takmir',
                     'Sekretaris',
                     'Bendahara',
                     'Sie Ibadah & Dakwah',
                     'Sie Sarpras / Fisik',
                     'Sie Remaja & TPA',
                     'Imam / Marbot Masjid',
                     'Pengurus Lainnya'
                 ],
                 masyarakatPresets: [
                     'Pengurus RT (Ketua / Pengurus)',
                     'Pengurus RW',
                     'Anggota BPD Desa',
                     'Pengurus Paguyuban Warga',
                     'Kader Posyandu / Kesehatan Desa',
                     'Anggota Linmas / Relawan',
                     'Kelompok Tani / Gapoktan',
                     'Pengurus Rukun Kematian / Sosial',
                     'Pembina Karang Taruna',
                     'Warga Masyarakat'
                 ],

                 toggleMasjid(item) {
                     let list = this.masjidText ? this.masjidText.split(',').map(s => s.trim()).filter(Boolean) : [];
                     if (list.includes(item)) {
                         list = list.filter(x => x !== item);
                     } else {
                         list.push(item);
                     }
                     this.masjidText = list.join(', ');
                 },
                 hasMasjid(item) {
                     return this.masjidText && this.masjidText.includes(item);
                 },

                 selectTakmirRole(role) {
                     if (this.takmirRole === role) {
                         this.takmirRole = '';
                     } else {
                         this.takmirRole = role;
                     }
                 },
                 isTakmirRoleActive(role) {
                     if (!this.takmirRole) return false;
                     return this.takmirRole === role || this.takmirRole.startsWith(role);
                 },

                 toggleMasyarakat(item) {
                     let list = this.masyarakatText ? this.masyarakatText.split(',').map(s => s.trim()).filter(Boolean) : [];
                     if (list.includes(item)) {
                         list = list.filter(x => x !== item);
                     } else {
                         list.push(item);
                     }
                     this.masyarakatText = list.join(', ');
                 },
                 hasMasyarakat(item) {
                     return this.masyarakatText && this.masyarakatText.includes(item);
                 }
             }">
            <div class="border-b-2 border-slate-100 pb-4">
                <h2 class="text-lg sm:text-xl font-black text-slate-900">Kiprah Kemuhammadiyahan, Kemasjidan &amp; Kemasyarakatan</h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">Riwayat keaktifan di persyarikatan, aktifitas kemasjidan, dan pengabdian sosial kemasyarakatan.</p>
            </div>

            {{-- 1. Keaktifan di Pimpinan Muhammadiyah --}}
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-sm sm:text-base font-black text-slate-900">
                        1. Keaktifan di Pimpinan Muhammadiyah
                    </label>
                    <span class="text-xs sm:text-sm text-sky-800 font-bold bg-sky-100 px-3 py-1 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Centang tingkatan pimpinan tempat Anda aktif dan isikan amanah/jabatan Anda:</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    @foreach(\App\Models\FormPertanyaan::opsi('keaktifan_muhammadiyah', config('arqam.opsi_form.keaktifan_muhammadiyah')) as $v)
                    <div class="p-3.5 sm:p-4 rounded-2xl border-2 transition-all"
                         :class="isMuhammadiyahChecked('{{ $v }}') ? 'bg-sky-50 border-sky-500 ring-2 ring-sky-300' : 'bg-white border-slate-300 hover:border-slate-400 hover:bg-slate-50'">
                        <label class="flex items-center gap-3 text-sm sm:text-base text-slate-900 font-bold cursor-pointer select-none">
                            <input type="checkbox" name="keaktifan_muhammadiyah[]" value="{{ $v }}"
                                @checked(in_array($v, old('keaktifan_muhammadiyah', $anggota->keaktifan_muhammadiyah ?? [])))
                                @change="toggleMuhammadiyah('{{ $v }}', $el.checked)"
                                class="w-5 h-5 rounded-lg border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>{{ $v }}</span>
                        </label>

                        @if($v !== 'Tidak aktif')
                        <div x-show="isMuhammadiyahChecked('{{ $v }}')" x-cloak x-transition
                             class="mt-3 pt-3 border-t-2 border-sky-200 space-y-1.5">
                            <label class="block text-xs sm:text-sm font-extrabold text-slate-800">
                                Jabatan / Amanah di {{ $v }}:
                            </label>
                            <input type="text" name="jabatan_muhammadiyah[{{ $v }}]"
                                   value="{{ old('jabatan_muhammadiyah.'.$v, $anggota->jabatan_muhammadiyah[$v] ?? '') }}"
                                   placeholder="Contoh: Ketua / Sekretaris / Anggota Majelis"
                                   class="w-full px-4 py-2.5 text-sm sm:text-base border-2 border-slate-300 rounded-xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition font-medium min-h-[46px]">
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 2. Pimpinan Cabang (PCM) & Pimpinan Ranting (PRM) Domisili --}}
            <div class="p-4 sm:p-6 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-4">
                <div>
                    <h3 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>2. Cabang &amp; Ranting Domisili / Basis Kader</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium leading-relaxed">
                        Pilih Pimpinan Cabang tempat Anda berdomisili atau beraktivitas dan ketikkan nama Ranting (PRM) asal Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                            Pimpinan Cabang Muhammadiyah (PCM)
                        </label>
                        <select name="pcm" class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition font-medium min-h-[50px]">
                            <option value="">-- Pilih Cabang / Kecamatan --</option>
                            @foreach(\App\Models\FormPertanyaan::opsi('pcm', config('arqam.opsi_form.cabang_pcm')) as $cabang)
                                <option value="{{ $cabang }}" @selected(old('pcm', $anggota->pcm ?: $anggota->kecamatan) === $cabang)>PCM {{ $cabang }}</option>
                            @endforeach
                            <option value="Lainnya" @selected(old('pcm', $anggota->pcm) === 'Lainnya')>Lainnya (Luar Karanganyar)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                            Pimpinan Ranting Muhammadiyah (PRM)
                        </label>
                        <input name="prm" value="{{ old('prm', $anggota->prm) }}" placeholder="Contoh: PRM Lalung / PRM Papahan"
                            class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition font-medium min-h-[50px]">
                        <span class="text-xs text-slate-500 mt-1.5 block font-medium">Dapat berupa nama desa, kelurahan, atau dusun/masjid basis ranting.</span>
                    </div>
                </div>
            </div>

            {{-- 2. Amal Usaha Muhammadiyah (AUM) - 2 Dropdown (Nama AUM & Jabatan) --}}
            <div class="p-4 sm:p-6 rounded-2xl bg-sky-50/60 border-2 border-sky-200 space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-black text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>2. Amal Usaha Muhammadiyah (AUM)</span>
                        </h3>
                        <span class="text-xs text-sky-800 font-bold bg-white px-2.5 py-0.5 rounded-full border border-sky-200">2 Dropdown (AUM &amp; Jabatan)</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium leading-relaxed">
                        Khusus kader yang bertugas atau berkhidmat di Amal Usaha Muhammadiyah (Sekolah, Madrasah, RS PKU, Klinik, BTM/BMT, Lazismu, dll):
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Dropdown 1: Nama AUM --}}
                    <div>
                        <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                            Nama Amal Usaha Muhammadiyah (AUM)
                        </label>
                        <select name="nama_aum" x-model="selectedAum"
                            class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition font-medium min-h-[50px]">
                            <option value="">-- Bukan Pegawai / Tidak Aktif di AUM --</option>
                            @foreach(\App\Models\FormPertanyaan::opsi('nama_aum', config('arqam.opsi_form.nama_aum')) as $aumOption)
                                <option value="{{ $aumOption }}">{{ $aumOption }}</option>
                            @endforeach
                        </select>

                        <div x-show="selectedAum === 'Lainnya'" x-cloak x-transition class="mt-2.5">
                            <label class="block text-xs font-bold text-sky-800 mb-1">Nama AUM Lainnya:</label>
                            <input type="text" name="nama_aum_custom" x-model="customAum"
                                placeholder="Contoh: SD Muhammadiyah 2 Karanganyar"
                                class="w-full px-4 py-2.5 text-sm sm:text-base border-2 border-sky-300 rounded-xl focus:border-sky-600 outline-none bg-white transition font-medium">
                        </div>
                        <span class="text-xs text-slate-500 mt-1.5 block font-medium">Pilih nama AUM tempat Anda mengabdi atau berkhidmat.</span>
                    </div>

                    {{-- Dropdown 2: Jabatan di AUM --}}
                    <div>
                        <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">
                            Jabatan / Amanah Tugas di AUM
                        </label>
                        <select name="jabatan_aum" x-model="selectedJabatanAum"
                            class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition font-medium min-h-[50px]">
                            <option value="">-- Pilih Jabatan / Posisi di AUM --</option>
                            @foreach(\App\Models\FormPertanyaan::opsi('jabatan_aum', config('arqam.opsi_form.jabatan_aum')) as $jabOption)
                                <option value="{{ $jabOption }}">{{ $jabOption }}</option>
                            @endforeach
                        </select>

                        <div x-show="selectedJabatanAum === 'Lainnya'" x-cloak x-transition class="mt-2.5">
                            <label class="block text-xs font-bold text-sky-800 mb-1">Jabatan di AUM Lainnya:</label>
                            <input type="text" name="jabatan_aum_custom" x-model="customJabatanAum"
                                placeholder="Contoh: Koordinator Lab / Staf Humas"
                                class="w-full px-4 py-2.5 text-sm sm:text-base border-2 border-sky-300 rounded-xl focus:border-sky-600 outline-none bg-white transition font-medium">
                        </div>
                        <span class="text-xs text-slate-500 mt-1.5 block font-medium">Pilih posisi atau amanah tugas di AUM bersangkutan.</span>
                    </div>
                </div>
            </div>

            {{-- 3. Keaktifan di Organisasi Otonom (ORTOM) --}}
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-sm sm:text-base font-black text-slate-900">
                        3. Keaktifan di Organisasi Otonom (ORTOM)
                    </label>
                    <span class="text-xs sm:text-sm text-sky-800 font-bold bg-sky-100 px-3 py-1 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 font-medium">Centang ortom tempat Anda berkiprah dan isikan jabatan/posisi Anda:</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    @foreach(\App\Models\FormPertanyaan::opsi('keaktifan_ortom', config('arqam.opsi_form.keaktifan_ortom')) as $v)
                    <div class="p-3.5 sm:p-4 rounded-2xl border-2 transition-all"
                         :class="isOrtomChecked('{{ $v }}') ? 'bg-sky-50 border-sky-500 ring-2 ring-sky-300' : 'bg-white border-slate-300 hover:border-slate-400 hover:bg-slate-50'">
                        <label class="flex items-center gap-3 text-sm sm:text-base text-slate-900 font-bold cursor-pointer select-none">
                            <input type="checkbox" name="keaktifan_ortom[]" value="{{ $v }}"
                                @checked(in_array($v, old('keaktifan_ortom', $anggota->keaktifan_ortom ?? [])))
                                @change="toggleOrtom('{{ $v }}', $el.checked)"
                                class="w-5 h-5 rounded-lg border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>{{ $v }}</span>
                        </label>

                        @if($v !== 'Tidak aktif')
                        @php
                            $rawOrtomVal = old('jabatan_ortom.'.$v, $anggota->jabatan_ortom[$v] ?? null);
                            $oldTingkat = is_array($rawOrtomVal) ? ($rawOrtomVal['tingkat'] ?? '') : '';
                            $oldPeriode = is_array($rawOrtomVal) ? ($rawOrtomVal['periode'] ?? '') : '';
                            $oldTahunMulai = is_array($rawOrtomVal) ? ($rawOrtomVal['tahun_mulai'] ?? '') : '';
                            $oldTahunSelesai = is_array($rawOrtomVal) ? ($rawOrtomVal['tahun_selesai'] ?? '') : '';
                            if (empty($oldTahunMulai) && empty($oldTahunSelesai) && !empty($oldPeriode)) {
                                if (str_contains($oldPeriode, '-')) {
                                    $parts = array_map('trim', explode('-', $oldPeriode, 2));
                                    $oldTahunMulai = $parts[0] ?? '';
                                    $oldTahunSelesai = $parts[1] ?? '';
                                }
                            }
                            $oldJabatan = is_array($rawOrtomVal) ? ($rawOrtomVal['jabatan'] ?? '') : (is_string($rawOrtomVal) ? $rawOrtomVal : '');
                            $oldJabatanCustom = is_array($rawOrtomVal) ? ($rawOrtomVal['jabatan_custom'] ?? '') : '';
                            $opsiJabatan = config('arqam.opsi_form.jabatan_ortom_standar', []);
                            $isCustomJabatan = $oldJabatan && !in_array($oldJabatan, $opsiJabatan) && $oldJabatan !== '';
                            $initialSelectJabatan = $isCustomJabatan ? 'Lainnya' : $oldJabatan;
                            $initialCustomText = $isCustomJabatan ? $oldJabatan : $oldJabatanCustom;
                        @endphp
                        <div x-show="isOrtomChecked('{{ $v }}')" x-cloak x-transition
                             x-data="{ selectedJabatan: @js($initialSelectJabatan) }"
                             class="mt-3 pt-3 border-t-2 border-sky-200 space-y-2.5">
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-sky-800 bg-sky-100/60 px-2.5 py-1 rounded-lg border border-sky-200">
                                Detail Kepengurusan &amp; Jabatan ({{ $v }})
                            </div>

                            {{-- 1) Tingkat ORTOM --}}
                            @php
                                $opsiTingkatOrtom = config("arqam.opsi_form.tingkat_ortom_per_organisasi.{$v}") ?? config('arqam.opsi_form.tingkat_ortom', []);
                            @endphp
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    1. Tingkat Kepengurusan
                                </label>
                                <select name="jabatan_ortom[{{ $v }}][tingkat]"
                                        class="w-full px-3 py-2 text-xs sm:text-sm border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[42px] font-medium text-slate-800">
                                    <option value="">-- Pilih Tingkat Kepengurusan --</option>
                                    @if($oldTingkat && !in_array($oldTingkat, $opsiTingkatOrtom))
                                        <option value="{{ $oldTingkat }}" selected>{{ $oldTingkat }}</option>
                                    @endif
                                    @foreach($opsiTingkatOrtom as $to)
                                        <option value="{{ $to }}" @selected($oldTingkat === $to)>{{ $to }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- 2) Periode Kepengurusan (Tahun Mulai & Tahun Selesai) --}}
                            <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        2a. Tahun Mulai
                                    </label>
                                    <select name="jabatan_ortom[{{ $v }}][tahun_mulai]"
                                            class="w-full px-3 py-2 text-xs sm:text-sm border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[42px] font-medium text-slate-800">
                                        <option value="">-- Tahun Mulai --</option>
                                        @for($yr = (int)date('Y') + 4; $yr >= 1980; $yr--)
                                            <option value="{{ $yr }}" @selected($oldTahunMulai == $yr)>{{ $yr }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        2b. Tahun Selesai
                                    </label>
                                    <select name="jabatan_ortom[{{ $v }}][tahun_selesai]"
                                            class="w-full px-3 py-2 text-xs sm:text-sm border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[42px] font-medium text-slate-800">
                                        <option value="">-- Tahun Selesai --</option>
                                        <option value="Sekarang" @selected($oldTahunSelesai === 'Sekarang')>Sekarang (Masih Menjabat)</option>
                                        @for($yr = (int)date('Y') + 4; $yr >= 1980; $yr--)
                                            <option value="{{ $yr }}" @selected($oldTahunSelesai == $yr)>{{ $yr }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            {{-- 3) Jabatan --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    3. Jabatan / Amanah
                                </label>
                                <select name="jabatan_ortom[{{ $v }}][jabatan]"
                                        x-model="selectedJabatan"
                                        class="w-full px-3 py-2 text-xs sm:text-sm border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[42px] font-medium text-slate-800">
                                    <option value="">-- Pilih Jabatan / Posisi --</option>
                                    @foreach(config('arqam.opsi_form.jabatan_ortom_standar', []) as $jo)
                                        <option value="{{ $jo }}" @selected($initialSelectJabatan === $jo)>{{ $jo }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Custom Jabatan jika Lainnya --}}
                            <div x-show="selectedJabatan === 'Lainnya'" x-cloak class="pt-1">
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Ketikkan Jabatan Spesifik:
                                </label>
                                <input type="text" name="jabatan_ortom[{{ $v }}][jabatan_custom]"
                                       value="{{ $initialCustomText }}"
                                       placeholder="Contoh: Komandan Kokam / Pelatih / Bidang Perkaderan"
                                       class="w-full px-3 py-2 text-xs sm:text-sm border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[42px] font-medium text-slate-800">
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Langganan Majalah Suara Muhammadiyah</label>
                    <select name="langganan_suara_muhammadiyah" class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                        <option value="">-- Status Langganan --</option>
                        @foreach(\App\Models\FormPertanyaan::opsi('langganan_suara_muhammadiyah', config('arqam.opsi_form.langganan_suara_muhammadiyah')) as $v)
                        <option value="{{ $v }}" @selected(old('langganan_suara_muhammadiyah', $anggota->langganan_suara_muhammadiyah)===$v)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Tokoh Muhammadiyah Panutan</label>
                    <input name="tokoh_berpengaruh" value="{{ old('tokoh_berpengaruh', $anggota->tokoh_berpengaruh) }}" placeholder="Contoh: K.H. Ahmad Dahlan, Buya Hamka"
                        class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
                </div>
            </div>

            <div>
                <label class="block text-sm sm:text-base font-black text-slate-900 mb-2">Organisasi Lain di Luar Muhammadiyah</label>
                <input name="organisasi_lain" value="{{ old('organisasi_lain', $anggota->organisasi_lain) }}" placeholder="Sebutkan jika ada (opsional)"
                    class="w-full px-4 py-3.5 text-base bg-white border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none transition font-medium min-h-[50px]">
            </div>

            {{-- Banner Ramah Usia 40+ --}}
            <div class="p-4 sm:p-5 rounded-2xl bg-sky-50 border-2 border-sky-200 flex items-start gap-3.5 text-sky-950">
                <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-xs font-bold text-lg">
                    🕌
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-black text-sky-900 leading-snug">Kiprah Dakwah Jamaah &amp; Sosial Kemasyarakatan</h3>
                    <p class="text-xs sm:text-sm text-sky-800 mt-1 leading-relaxed">
                        Sentuh pilihan cepat di bawah ini sesuai rutinitas sehari-hari Bapak/Ibu. Boleh memilih lebih dari satu tanpa perlu mengetik panjang.
                    </p>
                </div>
            </div>

            {{-- 1. Aktivitas Jamaah di Masjid --}}
            <div class="p-4 sm:p-6 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-4">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                        <label class="block text-base sm:text-lg font-black text-slate-900 leading-tight">
                            1. Rutinitas Jamaah di Masjid Lingkungan Sekitar
                        </label>
                        <span class="text-xs sm:text-sm text-sky-800 font-bold bg-sky-100 px-3 py-1 rounded-full border border-sky-200 self-start sm:self-auto">Boleh pilih lebih dari 1</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">Sentuh pilihan di bawah ini yang biasa Bapak/Ibu ikuti:</p>
                </div>

                {{-- Structured 2-Column Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <template x-for="item in masjidPresets" :key="item">
                        <button type="button" @click="toggleMasjid(item)"
                            class="px-4 py-3.5 sm:py-4 rounded-2xl text-sm sm:text-base font-bold transition-all flex items-center justify-between border-2 shadow-xs active:scale-[0.98] text-left min-h-[52px]"
                            :class="hasMasjid(item) 
                                ? 'bg-sky-600 text-white border-sky-600 shadow-sm font-extrabold ring-2 ring-sky-300' 
                                : 'bg-white text-slate-800 border-slate-300 hover:border-sky-400 hover:bg-sky-50/40'">
                            <span class="leading-snug" x-text="item"></span>
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 ml-2.5 transition-colors"
                                :class="hasMasjid(item) ? 'bg-white text-sky-600 font-black text-sm' : 'border-2 border-slate-300 text-transparent'">
                                ✓
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Textarea Sinkronisasi & Catatan Tambahan --}}
                <div class="pt-2">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5">
                        Rincian / Catatan Tambahan &amp; Nama Masjid:
                    </label>
                    <textarea name="aktifitas_jamaah_masjid" x-model="masjidText" rows="3"
                        placeholder="Pilihan di atas otomatis tertulis di sini. Bapak/Ibu juga dapat mengetik nama masjid atau kegiatan lain (contoh: Masjid Al-Falah Jetis)..."
                        class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white resize-none transition leading-relaxed placeholder:text-slate-400 font-medium"></textarea>
                    @error('aktifitas_jamaah_masjid') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- 2. Keaktifan sebagai Takmir Masjid --}}
            <div class="p-4 sm:p-6 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-4">
                <div>
                    <label class="block text-base sm:text-lg font-black text-slate-900 leading-tight">
                        2. Amanah sebagai Pengurus / Takmir Masjid
                    </label>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">Apakah saat ini Bapak/Ibu diamanahi sebagai takmir atau pengurus masjid/musholla?</p>
                </div>

                {{-- Symmetrical Side-by-Side Cards on Mobile & Desktop --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <label class="p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col items-center text-center gap-2.5 bg-white shadow-xs active:scale-[0.98]"
                        :class="isTakmir === 'Tidak' ? 'border-sky-600 bg-sky-50 text-sky-950 ring-2 ring-sky-400/30' : 'border-slate-300 text-slate-700 hover:border-slate-400 hover:bg-slate-50'">
                        <input type="radio" name="keaktifan_takmir" value="Tidak" x-model="isTakmir" class="sr-only">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 transition-colors"
                            :class="isTakmir === 'Tidak' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500'">
                            👤
                        </div>
                        <div>
                            <span class="text-sm sm:text-base font-extrabold block leading-tight">Bukan Takmir</span>
                            <span class="text-xs sm:text-sm text-slate-500 font-medium block mt-1">Sebagai Jamaah</span>
                        </div>
                    </label>

                    <label class="p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col items-center text-center gap-2.5 bg-white shadow-xs active:scale-[0.98]"
                        :class="isTakmir === 'Ya' ? 'border-sky-600 bg-sky-50 text-sky-950 ring-2 ring-sky-400/30' : 'border-slate-300 text-slate-700 hover:border-slate-400 hover:bg-slate-50'">
                        <input type="radio" name="keaktifan_takmir" value="Ya" x-model="isTakmir" class="sr-only">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 transition-colors"
                            :class="isTakmir === 'Ya' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500'">
                            🕌
                        </div>
                        <div>
                            <span class="text-sm sm:text-base font-extrabold block leading-tight">Ya, Pengurus</span>
                            <span class="text-xs sm:text-sm text-slate-500 font-medium block mt-1">Takmir Masjid</span>
                        </div>
                    </label>
                </div>

                {{-- Conditional Role Picker if "Ya" --}}
                <div x-show="isTakmir === 'Ya'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="pt-4 border-t-2 border-slate-200 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs sm:text-sm font-extrabold text-slate-900">
                            Pilih Jabatan / Amanah di Takmir:
                        </label>
                        <span class="text-xs sm:text-sm font-semibold text-slate-500">Pilih salah satu</span>
                    </div>
                    
                    {{-- Structured 2-Column Grid (4 rows x 2 cols) --}}
                    <div class="grid grid-cols-2 gap-2.5">
                        <template x-for="role in takmirPresets" :key="role">
                            <button type="button" @click="selectTakmirRole(role)"
                                class="px-3.5 py-3 sm:py-3.5 rounded-2xl text-xs sm:text-sm font-bold transition-all border-2 shadow-xs flex items-center justify-between text-left active:scale-[0.98] min-h-[48px] sm:min-h-[52px]"
                                :class="isTakmirRoleActive(role) 
                                    ? 'bg-sky-600 text-white border-sky-600 shadow-sm font-extrabold ring-2 ring-sky-300' 
                                    : 'bg-white text-slate-800 border-slate-300 hover:border-sky-400 hover:bg-sky-50/40'">
                                <span class="truncate leading-tight" x-text="role"></span>
                                <span x-show="isTakmirRoleActive(role)" class="text-white font-black text-sm shrink-0 ml-1">✓</span>
                            </button>
                        </template>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5">
                            Nama Masjid &amp; Keterangan Tambahan:
                        </label>
                        <input type="text" name="jabatan_takmir" x-model="takmirRole"
                            placeholder="Contoh: Masjid Al-Falah Jetis / Sie Dakwah..."
                            class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white transition placeholder:text-slate-400 font-medium min-h-[50px]">
                        @error('jabatan_takmir') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Keaktifan di Kemasyarakatan --}}
            <div class="p-4 sm:p-6 rounded-2xl bg-slate-50 border-2 border-slate-200 space-y-4">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                        <label class="block text-base sm:text-lg font-black text-slate-900 leading-tight">
                            3. Keaktifan di Lingkungan Kemasyarakatan
                        </label>
                        <span class="text-xs sm:text-sm text-sky-800 font-bold bg-sky-100 px-3 py-1 rounded-full border border-sky-200 self-start sm:self-auto">RT / RW / BPD / Karang Taruna dll.</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">Sentuh peran masyarakat yang pernah atau sedang Bapak/Ibu jalankan:</p>
                </div>

                {{-- Structured 2-Column Grid (5 rows x 2 cols) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <template x-for="item in masyarakatPresets" :key="item">
                        <button type="button" @click="toggleMasyarakat(item)"
                            class="px-4 py-3.5 sm:py-4 rounded-2xl text-sm sm:text-base font-bold transition-all flex items-center justify-between border-2 shadow-xs active:scale-[0.98] text-left min-h-[52px]"
                            :class="hasMasyarakat(item) 
                                ? 'bg-sky-600 text-white border-sky-600 shadow-sm font-extrabold ring-2 ring-sky-300' 
                                : 'bg-white text-slate-800 border-slate-300 hover:border-sky-400 hover:bg-sky-50/40'">
                            <span class="leading-snug" x-text="item"></span>
                            <div class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 ml-2.5 transition-colors"
                                :class="hasMasyarakat(item) ? 'bg-white text-sky-600 font-black text-sm' : 'border-2 border-slate-300 text-transparent'">
                                ✓
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Textarea Sinkronisasi & Catatan Tambahan --}}
                <div class="pt-2">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800 mb-1.5">
                        Rincian Peran &amp; Nama Dusun/Wilayah:
                    </label>
                    <textarea name="keaktifan_kemasyarakatan" x-model="masyarakatText" rows="3"
                        placeholder="Pilihan di atas otomatis tertulis di sini. Bapak/Ibu juga dapat mengetik rincian RT/RW/Desa (contoh: Ketua RT 02 RW 04 Dusun Dagen)..."
                        class="w-full px-4 py-3.5 text-base border-2 border-slate-300 rounded-2xl focus:border-sky-600 focus:ring-4 focus:ring-sky-100 outline-none bg-white resize-none transition leading-relaxed placeholder:text-slate-400 font-medium"></textarea>
                    @error('keaktifan_kemasyarakatan') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Pertanyaan Kustom Seksi Organisasi, Kemasyarakatan & Tambahan --}}
                <x-form-custom-fields :questions="array_merge(($customQuestions['organisasi'] ?? collect())->all(), ($customQuestions['kemasyarakatan'] ?? collect())->all(), ($customQuestions['tambahan'] ?? collect())->all())" :values="old('custom_fields', $anggota->custom_fields ?? [])" />
            </div>

            <div class="flex items-center justify-between pt-5 border-t-2 border-slate-100">
                <button type="button" @click="tab = 'agama'"
                    class="min-h-[50px] px-6 border-2 border-slate-300 text-slate-700 font-extrabold rounded-2xl hover:bg-slate-100 active:bg-slate-200 transition text-sm sm:text-base flex items-center justify-center gap-2 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali</span>
                </button>
            </div>
        </div>

        {{-- Submission Bar --}}
        <div class="sticky bottom-4 z-20 bg-white/95 backdrop-blur-md rounded-2xl border-2 border-slate-300 shadow-lg p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs sm:text-sm text-slate-700 text-center sm:text-left flex items-center gap-2.5 font-medium">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span>Perubahan data profil akan langsung tersimpan di pangkalan data MPKSDI PDM Karanganyar.</span>
            </div>
            <button type="submit" class="w-full sm:w-auto min-h-[52px] sm:min-h-[54px] px-8 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-black text-base rounded-2xl shadow-md transition flex items-center justify-center gap-2 shrink-0">
                <span>Simpan Seluruh Perubahan Profil</span>
                <span class="text-lg leading-none">&rarr;</span>
            </button>
        </div>
    </form>
    @else
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-400 text-sm shadow-xs">
        Data profil anggota belum terhubung dengan akun ini. Silakan hubungi admin MPKSDI PDM Karanganyar.
    </div>
    @endif
</div>
@endsection
