@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event — ' . $event->nama_event : 'Buat Kegiatan Baitul Arqam Baru')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumbs Navigation --}}
    <div>
        <a href="{{ isset($event) ? route('admin.events.show', $event) : route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke {{ isset($event) ? $event->nama_event : 'Daftar Event' }}</span>
        </a>
    </div>

    {{-- 2-Column Workstation Grid --}}
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left: Event Form (8 of 12 columns) --}}
        <div class="lg:col-span-8 bg-white rounded-xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs relative overflow-hidden">
            <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.20] pointer-events-none" />
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#005c8a] animate-pulse"></span>
                        Manajemen Konfigurasi Event
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">
                    {{ isset($event) ? 'Edit Kegiatan Baitul Arqam' : 'Buat Kegiatan Baitul Arqam Baru' }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 font-normal">
                    Lengkapi parameter kegiatan, kuota kader, jadwal pelaksanaan, dan pola nomor sertifikat resmi.
                </p>

                <form method="POST" action="{{ isset($event) ? route('admin.events.update', $event) : route('admin.events.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @isset($event) @method('PUT') @endisset

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Event <span class="text-red-500">*</span></label>
                        <input name="nama_event" value="{{ old('nama_event', $event->nama_event ?? '') }}" required placeholder="Contoh: Baitul Arqam Pimpinan Cabang & Ranting PCM Tasikmadu"
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        @error('nama_event')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', isset($event) ? $event->tanggal_mulai?->format('Y-m-d') : '') }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('tanggal_mulai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', isset($event) ? $event->tanggal_selesai?->format('Y-m-d') : '') }}" required
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('tanggal_selesai')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Lokasi Kegiatan</label>
                            <input name="lokasi" value="{{ old('lokasi', $event->lokasi ?? '') }}" placeholder="Contoh: Gedung Dakwah Muhammadiyah Karanganyar"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Kapasitas Kuota (Orang)</label>
                            <input type="number" name="kuota" min="1" value="{{ old('kuota', $event->kuota ?? '') }}" placeholder="Kosongkan jika tanpa batas"
                                inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '')"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                            @error('kuota')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Singkat Kegiatan</label>
                        <textarea name="deskripsi" rows="3" placeholder="Informasi sasaran kader, tema perkaderan, atau catatan pelaksanaan..."
                            class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition resize-none shadow-2xs">{{ old('deskripsi', $event->deskripsi ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Status Kegiatan <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs font-medium cursor-pointer">
                                @foreach(['persiapan','berlangsung','selesai'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $event->status ?? 'persiapan') === $s)>
                                    {{ ucfirst($s) }} {{ $s === 'persiapan' ? '(Pendaftaran/Persiapan)' : ($s === 'berlangsung' ? '(Pelaksanaan Aktif)' : '(Kegiatan Rampung)') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Format Nomor Sertifikat</label>
                            <input name="format_nomor_sertifikat" value="{{ old('format_nomor_sertifikat', $event->format_nomor_sertifikat ?? '{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}') }}"
                                placeholder="{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}"
                                class="w-full px-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none transition shadow-2xs">
                        </div>
                    </div>

                    <fieldset class="border border-slate-200 rounded-xl p-4 sm:p-5 space-y-4 bg-slate-50/50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-1 border-b border-slate-200">
                            <div>
                                <legend class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                    Pengesahan Sertifikat &amp; Tanda Tangan Dokumen
                                </legend>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Atur tanggal terbit, nama, jabatan, serta unggah file tanda tangan PNG (transparan) untuk tercetak pada Sertifikat &amp; Transkrip.
                                </p>
                            </div>
                        </div>

                        {{-- Tanggal Terbit --}}
                        <div class="max-w-xs">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Terbit Sertifikat</label>
                            <input type="date" name="tanggal_terbit_sertifikat" value="{{ old('tanggal_terbit_sertifikat', isset($event) && $event->tanggal_terbit_sertifikat ? $event->tanggal_terbit_sertifikat->format('Y-m-d') : '') }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                            <p class="text-[11px] text-slate-400 mt-0.5">Bila dikosongkan, default menggunakan tanggal selesai event.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                            {{-- Penandatangan Kiri (MoT / Panitia) --}}
                            <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Penandatangan 1 (Sisi Kiri)</span>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium">MoT / Instruktur</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Judul Penandatangan</label>
                                    <input name="jabatan_penandatangan_kiri" value="{{ old('jabatan_penandatangan_kiri', $event->jabatan_penandatangan_kiri ?? 'Master of Training (MoT)') }}" maxlength="255" placeholder="Master of Training (MoT)" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap &amp; Gelar</label>
                                    <input name="nama_penandatangan_kiri" value="{{ old('nama_penandatangan_kiri', $event->nama_penandatangan_kiri ?? '') }}" maxlength="255" placeholder="Contoh: Drs. H. Sriyanto, M.Pd." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                    <p class="text-[11px] text-slate-400 mt-0.5">Bila dikosongkan, otomatis mengambil nama fasilitator utama.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Sub-Jabatan</label>
                                    <input name="keterangan_penandatangan_kiri" value="{{ old('keterangan_penandatangan_kiri', $event->keterangan_penandatangan_kiri ?? 'Instruktur Utama Perkaderan') }}" maxlength="255" placeholder="Contoh: Instruktur Utama Perkaderan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Tanda Tangan (PNG Transparan)</label>
                                    @if(isset($event) && $event->ttd_kiri_path)
                                        <div class="mb-2 p-2 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('storage/' . $event->ttd_kiri_path) }}" alt="Ttd MoT" class="h-9 max-w-[120px] object-contain border border-dashed border-slate-300 bg-white p-1 rounded">
                                                <span class="text-[11px] text-slate-600 font-medium">Tanda tangan tersimpan</span>
                                            </div>
                                            <label class="flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                                                <input type="checkbox" name="hapus_ttd_kiri" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                                Hapus
                                            </label>
                                        </div>
                                    @endif
                                    <input type="file" name="ttd_kiri" accept=".png,image/png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                    <p class="text-[11px] text-slate-400 mt-1">Disarankan format PNG transparan horizontal, maks 2 MB.</p>
                                </div>
                            </div>

                            {{-- Penandatangan Kanan (Ketua MPKSDI / PDM) --}}
                            <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-3 shadow-2xs">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Penandatangan 2 (Sisi Kanan)</span>
                                    <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium">Ketua Majelis / PDM</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan Penandatangan</label>
                                    <input name="jabatan_penandatangan_sertifikat" value="{{ old('jabatan_penandatangan_sertifikat', $event->jabatan_penandatangan_sertifikat ?? 'Ketua MPKSDI PDM Karanganyar') }}" maxlength="255" placeholder="Contoh: Ketua MPKSDI PDM Karanganyar" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap &amp; Gelar</label>
                                    <input name="nama_penandatangan_sertifikat" value="{{ old('nama_penandatangan_sertifikat', $event->nama_penandatangan_sertifikat ?? '') }}" maxlength="255" placeholder="Contoh: H. Zainudin Ahpandi, S.E." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">NBM Penandatangan</label>
                                    <input name="nbm_penandatangan_sertifikat" value="{{ old('nbm_penandatangan_sertifikat', $event->nbm_penandatangan_sertifikat ?? '') }}" maxlength="50" inputmode="numeric" placeholder="Contoh: 864.219" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-900 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Tanda Tangan (PNG Transparan)</label>
                                    @if(isset($event) && $event->ttd_kanan_path)
                                        <div class="mb-2 p-2 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('storage/' . $event->ttd_kanan_path) }}" alt="Ttd Ketua" class="h-9 max-w-[120px] object-contain border border-dashed border-slate-300 bg-white p-1 rounded">
                                                <span class="text-[11px] text-slate-600 font-medium">Tanda tangan tersimpan</span>
                                            </div>
                                            <label class="flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                                                <input type="checkbox" name="hapus_ttd_kanan" value="1" class="rounded text-rose-600 focus:ring-rose-500">
                                                Hapus
                                            </label>
                                        </div>
                                    @endif
                                    <input type="file" name="ttd_kanan" accept=".png,image/png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                    <p class="text-[11px] text-slate-400 mt-1">Disarankan format PNG transparan, dicetak pada Hal. 1 &amp; Hal. 2, maks 2 MB.</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    {{-- Pengaturan Bobot Penilaian Dinamis (Opsional) --}}
                    <div x-data="{
                        open: {{ (old('bobot_kognitif', isset($event) ? $event->bobot_kognitif : '') !== '' || old('bobot_psikomotor', isset($event) ? $event->bobot_psikomotor : '') !== '') ? 'true' : 'false' }},
                        kognitif: '{{ old('bobot_kognitif', isset($event) && $event->bobot_kognitif ? ($event->bobot_kognitif > 1 ? $event->bobot_kognitif : $event->bobot_kognitif * 100) : '') }}',
                        psikomotor: '{{ old('bobot_psikomotor', isset($event) && $event->bobot_psikomotor ? ($event->bobot_psikomotor > 1 ? $event->bobot_psikomotor : $event->bobot_psikomotor * 100) : '') }}',
                        afektif: '{{ old('bobot_afektif', isset($event) && $event->bobot_afektif ? ($event->bobot_afektif > 1 ? $event->bobot_afektif : $event->bobot_afektif * 100) : '') }}',
                        setPreset(k, p, a) {
                            this.kognitif = k;
                            this.psikomotor = p;
                            this.afektif = a;
                        },
                        resetDefault() {
                            this.kognitif = '';
                            this.psikomotor = '';
                            this.afektif = '';
                        }
                    }" class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50/50">
                        <button type="button" @click="open = !open" class="w-full px-4 py-3 bg-slate-50 flex items-center justify-between text-left hover:bg-slate-100 transition">
                            <div class="flex items-center gap-2">
                                <span class="text-xs sm:text-sm font-bold text-slate-800">⚖️ Kustomisasi Bobot Nilai Akhir (Opsional)</span>
                                <span class="text-[10px] sm:text-[11px] px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-semibold" x-text="open ? 'Tutup' : 'Sesuaikan'"></span>
                            </div>
                            <svg class="w-4 h-4 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" class="p-4 bg-white border-t border-slate-200 space-y-3">
                            <p class="text-xs text-slate-500">
                                Standar default sistem: <strong>Kognitif 40%</strong> (70% Posttest + 30% N-Gain), <strong>Psikomotor 35%</strong> (90% Rubrik + 10% Keaktifan), <strong>Afektif 25%</strong>. Kosongkan jika ingin menggunakan standar sistem.
                            </p>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <span class="text-[11px] font-semibold text-slate-500 self-center mr-1">Preset Cepat:</span>
                                <button type="button" @click="setPreset(40, 35, 25)" class="px-2.5 py-1 text-xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">Standar MPKSDI (40:35:25)</button>
                                <button type="button" @click="setPreset(30, 45, 25)" class="px-2.5 py-1 text-xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">AUM / Ibadah Praktis (30:45:25)</button>
                                <button type="button" @click="setPreset(45, 25, 30)" class="px-2.5 py-1 text-xs rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition">Ideologi & Kepemimpinan (45:25:30)</button>
                                <button type="button" @click="resetDefault()" class="px-2.5 py-1 text-xs rounded-md bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium transition">Reset Default</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bobot Kognitif (%)</label>
                                    <input type="number" step="0.1" min="0" max="100" name="bobot_kognitif" x-model="kognitif" placeholder="Default: 40"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs sm:text-sm font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bobot Psikomotor (%)</label>
                                    <input type="number" step="0.1" min="0" max="100" name="bobot_psikomotor" x-model="psikomotor" placeholder="Default: 35"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs sm:text-sm font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bobot Afektif (%)</label>
                                    <input type="number" step="0.1" min="0" max="100" name="bobot_afektif" x-model="afektif" placeholder="Default: 25"
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs sm:text-sm font-mono text-slate-900 focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Toggle Grade A/B/C --}}
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <div>
                            <span class="text-xs font-bold text-slate-800">🏷️ Gunakan Grade A/B/C</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Grade A (&#8805;85), B (&#8805;70), C (&#60;70). Hanya untuk peserta lulus. Kosongkan jika tidak digunakan.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="hidden" name="gunakan_grade" value="0">
                            <input type="checkbox" name="gunakan_grade" value="1" class="sr-only peer"
                                @checked(old('gunakan_grade', isset($event) ? $event->gunakan_grade : false))>
                            <div class="w-9 h-5 bg-slate-300 rounded-full peer peer-checked:bg-sky-600 peer-focus:ring-2 peer-focus:ring-sky-300 transition-all after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ isset($event) ? route('admin.events.show', $event) : route('admin.events.index') }}"
                           class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-600 transition shadow-2xs">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white font-semibold text-xs sm:text-sm rounded-lg shadow-2xs transition">
                            {{ isset($event) ? 'Simpan Perubahan' : 'Buat Kegiatan Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right: Guidance & Best Practices Sidebar (4 of 12 columns) --}}
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
            {{-- Workflow Status Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Siklus Hidup Event</span>
                <ul class="space-y-3 text-xs">
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">1</span>
                        <div>
                            <strong class="text-slate-800 block">Persiapan:</strong>
                            <p class="text-slate-500 font-normal">Fase pendaftaran kader, penentuan soal CBT, rubrik psikomotor, dan penugasan fasilitator.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">2</span>
                        <div>
                            <strong class="text-slate-800 block">Berlangsung:</strong>
                            <p class="text-slate-500 font-normal">Sesi materi berjalan, scanner presensi QR aktif, ujian pretest/posttest dibuka, dan observasi dicatat.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-800 font-bold flex items-center justify-center shrink-0 mt-0.5 text-[11px]">3</span>
                        <div>
                            <strong class="text-slate-800 block">Selesai:</strong>
                            <p class="text-slate-500 font-normal">Nilai akhir dikunci, peringkat diterbitkan, serta laporan PDF dan sertifikat resmi dapat diunduh.</p>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- Certificate Format Tags Cheat Sheet --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Tag Format Sertifikat</span>
                <p class="text-xs text-slate-500 mb-3 font-normal">Gunakan tag dinamis berikut dalam format penomoran:</p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{nomor}</code>
                        <span class="text-slate-500">Urutan 3 digit (001, 002)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{bulan_romawi}</code>
                        <span class="text-slate-500">Bulan Romawi (I s.d. XII)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{bulan}</code>
                        <span class="text-slate-500">Bulan 2 digit (01 s.d. 12)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                        <code class="font-mono text-sky-700 font-bold">{tahun}</code>
                        <span class="text-slate-500">Tahun 4 digit ({{ date('Y') }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
