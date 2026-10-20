@extends('layouts.app')

@section('title', 'Enroll Peserta — ' . $event->nama_event)

@section('content')
<div x-data="{
    selected: JSON.parse(sessionStorage.getItem('arqam_enroll_{{ $event->id }}') || '[]'),
    candidates: {{ json_encode($kandidat->pluck('id')->values()->all()) }},
    kuota: {{ (int) ($event->kuota ?? 0) }},
    currentEnrolled: {{ (int) ($event->peserta_aktif_count ?? 0) }},
    init() {
        this.$watch('selected', (val) => {
            sessionStorage.setItem('arqam_enroll_{{ $event->id }}', JSON.stringify(val));
        });
    },
    get remainingQuota() {
        if (!this.kuota) return 999999;
        return Math.max(0, this.kuota - this.currentEnrolled);
    },
    get isExceedingQuota() {
        if (!this.kuota) return false;
        return this.selected.length > this.remainingQuota;
    },
    get allOnPageSelected() {
        return this.candidates.length > 0 && this.candidates.every(id => this.selected.includes(id));
    },
    toggleAll() {
        if (this.allOnPageSelected) {
            this.selected = this.selected.filter(id => !this.candidates.includes(id));
        } else {
            const set = new Set([...this.selected, ...this.candidates]);
            this.selected = Array.from(set);
        }
    },
    isSelected(id) {
        return this.selected.includes(id);
    },
    clearSelection() {
        this.selected = [];
        sessionStorage.removeItem('arqam_enroll_{{ $event->id }}');
    }
}" class="space-y-6">

    {{-- Breadcrumbs Navigation --}}
    <div>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke {{ $event->nama_event }}</span>
        </a>
    </div>

    {{-- Main 2-Column Workstation Grid --}}
    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- Left Column: Candidate List & Controls (8 of 12 columns) --}}
        <div class="lg:col-span-8 space-y-5">
            {{-- Header Hero Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs relative overflow-hidden">
                <x-batik-corner position="top-right" class="w-40 h-40 text-[#005c8a] opacity-[0.20] pointer-events-none" />
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#005c8a] animate-pulse"></span>
                            Registrasi &amp; Penugasan Peserta
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-xs text-slate-500 font-medium">
                            {{ $kandidat->total() }} Anggota Aktif Tersedia
                            @if(!empty($cabang))
                                &bull; Wilayah PCM {{ $cabang }}
                            @endif
                            @if($kandidat->hasPages())
                                &bull; Halaman {{ $kandidat->currentPage() }} dari {{ $kandidat->lastPage() }}
                            @endif
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Enroll Peserta Kegiatan</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl font-normal">
                        Pilih kader aktif terverifikasi untuk didaftarkan secara resmi ke agenda perkaderan ini.
                    </p>

                    {{-- Search & Multi-Kategori Filter Bar (AUM, ORTOM, Muhammadiyah) --}}
                    <form method="GET" action="{{ route('admin.events.enroll', $event) }}" class="mt-5 space-y-3">
                        <div class="flex gap-2.5 flex-wrap sm:flex-nowrap">
                            <div class="relative flex-1 min-w-[200px]">
                                <input type="text" name="q" value="{{ $query }}" placeholder="Cari nama, NIK, NBM, atau nama AUM kader…"
                                    class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50/70 border border-slate-200 rounded-lg text-xs sm:text-sm outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white text-xs font-semibold rounded-lg shadow-2xs transition shrink-0">
                                Cari
                            </button>
                            @if(!empty($query) || !empty($cabang) || !empty($kategori) || !empty($aum) || !empty($ortom) || !empty($sort))
                            <a href="{{ route('admin.events.enroll', $event) }}" class="px-3.5 py-2.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-lg transition shrink-0">
                                Reset
                            </a>
                            @endif
                        </div>

                        {{-- Dropdown Filter Row --}}
                        <div class="flex items-center gap-2 flex-wrap text-xs">
                            {{-- Filter Kategori Basis --}}
                            <select name="kategori" onchange="this.form.submit()"
                                class="px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-sky-500 transition shadow-2xs font-medium text-slate-700 cursor-pointer">
                                <option value="">Semua Pilar</option>
                                <option value="aum" @selected(($kategori ?? '') === 'aum')>Amal Usaha Muhammadiyah (AUM)</option>
                                <option value="ortom" @selected(($kategori ?? '') === 'ortom')>Organisasi Otonom (ORTOM)</option>
                                <option value="muhammadiyah" @selected(($kategori ?? '') === 'muhammadiyah')>Pimpinan Muhammadiyah</option>
                            </select>

                            {{-- Filter Spesifik Nama AUM --}}
                            <select name="aum" onchange="this.form.submit()"
                                class="px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-sky-500 transition shadow-2xs font-medium text-slate-700 cursor-pointer max-w-[200px] truncate">
                                <option value="">Semua Nama AUM</option>
                                @foreach($aumList ?? [] as $item)
                                    <option value="{{ $item }}" @selected(($aum ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Filter Spesifik ORTOM --}}
                            <select name="ortom" onchange="this.form.submit()"
                                class="px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-sky-500 transition shadow-2xs font-medium text-slate-700 cursor-pointer">
                                <option value="">Semua ORTOM</option>
                                @foreach($ortomList ?? [] as $item)
                                    <option value="{{ $item }}" @selected(($ortom ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Filter Cabang / PCM --}}
                            <select name="cabang" onchange="this.form.submit()"
                                class="px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-sky-500 transition shadow-2xs font-medium text-slate-700 cursor-pointer">
                                <option value="">Semua Cabang (PCM)</option>
                                @foreach($pcmList as $item)
                                    <option value="{{ $item }}" @selected(($cabang ?? '') === $item)>PCM {{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Sorting --}}
                            <select name="sort" onchange="this.form.submit()"
                                class="px-3 py-2 bg-slate-50/70 border border-slate-200 rounded-lg outline-none focus:bg-white focus:border-sky-500 transition shadow-2xs font-medium text-slate-700 cursor-pointer">
                                <option value="">Urutan: Terbaru</option>
                                <option value="nama_asc" @selected(($sort ?? '') === 'nama_asc')>Nama Kader (A - Z)</option>
                                <option value="nama_desc" @selected(($sort ?? '') === 'nama_desc')>Nama Kader (Z - A)</option>
                                <option value="aum_asc" @selected(($sort ?? '') === 'aum_asc')>Nama AUM (A - Z)</option>
                                <option value="pcm_asc" @selected(($sort ?? '') === 'pcm_asc')>Cabang PCM (A - Z)</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Candidates List Container --}}
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
                {{-- Toolbar Action Bar --}}
                <div class="p-4 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between gap-3 flex-wrap">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" :checked="allOnPageSelected" @change="toggleAll"
                            class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[#005c8a]/30">
                        <span class="text-xs font-bold text-slate-700">
                            Pilih Semua di Halaman Ini (<span x-text="candidates.length"></span> Kandidat)
                        </span>
                    </label>

                    <div class="flex items-center gap-2.5">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md transition-colors"
                              :class="selected.length > 0 ? 'bg-sky-50 text-[#005c8a] border border-sky-200' : 'bg-slate-100 text-slate-500'">
                            <strong x-text="selected.length">0</strong> dipilih
                        </span>
                        <button type="button" x-show="selected.length > 0" @click="clearSelection()"
                                class="text-xs font-semibold text-slate-400 hover:text-rose-600 transition"
                                style="display: none;">
                            Bersihkan
                        </button>
                    </div>
                </div>

                {{-- Candidate Cards Form --}}
                <form method="POST" action="{{ route('admin.events.enroll.store', $event) }}" id="enroll-form" 
                      @submit="sessionStorage.removeItem('arqam_enroll_{{ $event->id }}')" 
                      class="p-4 space-y-2.5">
                    @csrf

                    {{-- Hidden inputs for selected candidates on other pages --}}
                    <template x-for="id in selected.filter(id => !candidates.includes(id))" :key="id">
                        <input type="hidden" name="anggota_ids[]" :value="id">
                    </template>
                    @forelse($kandidat as $k)
                    <label class="flex items-center gap-3.5 p-3.5 rounded-xl border transition-all duration-150 cursor-pointer select-none group"
                           :class="isSelected({{ $k->id }}) ? 'border-[#005c8a] bg-sky-50/50 ring-1 ring-[#005c8a]/30 shadow-2xs' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/50'">
                        <input type="checkbox" name="anggota_ids[]" value="{{ $k->id }}" x-model.number="selected"
                               class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[#005c8a]/30">
                        
                        {{-- Candidate Avatar / Foto Profil --}}
                        <div class="relative w-9 h-11 rounded-lg overflow-hidden border bg-slate-100 shrink-0 shadow-2xs transition"
                             :class="isSelected({{ $k->id }}) ? 'border-[#005c8a] ring-2 ring-[#005c8a]/40' : 'border-slate-200 group-hover:border-slate-300'">
                            <img src="{{ route('foto.anggota', $k) }}"
                                 alt="{{ $k->nama_lengkap }}"
                                 class="w-full h-full object-cover"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <div class="hidden w-full h-full flex items-center justify-center font-bold text-xs"
                                 :class="isSelected({{ $k->id }}) ? 'bg-[#005c8a] text-white' : 'bg-slate-100 text-slate-700'">
                                {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-900 text-xs sm:text-sm truncate block">{{ $k->nama_lengkap }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">Aktif</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-[#005c8a] border border-sky-200 shrink-0">PCM {{ $k->pcm ?: $k->kecamatan }}</span>
                                
                                {{-- AUM Badge --}}
                                @if(!empty($k->nama_aum) || !empty($k->jabatan_aum))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 shrink-0" title="{{ $k->nama_aum }}">
                                        AUM: {{ $k->nama_aum ?: 'Amal Usaha' }}@if($k->jabatan_aum) &bull; {{ $k->jabatan_aum }}@endif
                                    </span>
                                @endif

                                {{-- ORTOM Badge --}}
                                @php
                                    $kCleanOrtom = is_array($k->keaktifan_ortom) ? array_filter($k->keaktifan_ortom, fn($x) => $x !== 'Tidak aktif') : [];
                                @endphp
                                @if(!empty($kCleanOrtom))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                        ORTOM: {{ implode(', ', $kCleanOrtom) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 font-normal mt-0.5">
                                {{ $k->desa_kelurahan ? $k->desa_kelurahan . ', ' : '' }}Kec. {{ $k->kecamatan }} &bull; NIK: <span class="font-mono text-slate-700 font-medium">{{ substr($k->nik, 0, 6) }}****</span>
                            </p>
                        </div>
                    </label>
                    @empty
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800">Tidak ada kandidat kader yang ditemukan</h3>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto font-normal">
                            @if(!empty($cabang))
                                Tidak ada anggota aktif di PCM {{ $cabang }} yang cocok atau semua sudah terdaftar di kegiatan ini.
                            @else
                                Semua anggota aktif mungkin sudah terdaftar di kegiatan ini atau kata kunci pencarian tidak cocok.
                            @endif
                        </p>
                        @if(!empty($query) || !empty($cabang))
                        <a href="{{ route('admin.events.enroll', $event) }}" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-[#005c8a] hover:bg-sky-50 transition">
                            Tampilkan Semua Kandidat
                        </a>
                        @endif
                    </div>
                    @endforelse
                </form>

                @if($kandidat->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-200/80 bg-slate-50/60">
                    {{ $kandidat->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Sticky Summary & Action Panel (4 of 12 columns) --}}
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-20">
            {{-- Event Information & Capacity Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Informasi Kegiatan</span>
                <h2 class="text-base font-bold text-slate-900 leading-snug">{{ $event->nama_event }}</h2>
                
                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-600">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $event->tanggal_mulai?->format('d M Y') }} &mdash; {{ $event->tanggal_selesai?->format('d M Y') }}</span>
                    </div>
                    @if($event->lokasi)
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $event->lokasi }}</span>
                    </div>
                    @endif
                </div>

                {{-- Capacity & Quota Gauge --}}
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700">Kapasitas Peserta</span>
                        @if($event->kuota)
                        <span class="font-semibold text-slate-600">
                            {{ $event->peserta_aktif_count }} / {{ $event->kuota }} Kader
                        </span>
                        @else
                        <span class="font-semibold text-slate-600">
                            {{ $event->peserta_aktif_count }} Kader Terdaftar
                        </span>
                        @endif
                    </div>

                    @if($event->kuota)
                    @php
                        $percentage = min(100, round(($event->peserta_aktif_count / $event->kuota) * 100));
                        $sisa = max(0, $event->kuota - $event->peserta_aktif_count);
                    @endphp
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                        <div class="bg-[#005c8a] h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ $percentage }}% terisi</span>
                        <span class="font-bold {{ $sisa > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $sisa > 0 ? 'Sisa ' . $sisa . ' kursi' : 'Kuota Penuh' }}
                        </span>
                    </div>
                    @else
                    <div class="p-2.5 rounded-lg bg-sky-50 text-sky-800 text-xs font-medium border border-sky-200">
                        Event ini tidak memiliki batasan kuota pendaftaran.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action Confirmation Card --}}
            <div class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Eksekusi Pendaftaran</span>
                
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-3xl font-black text-slate-900 tracking-tight" x-text="selected.length">0</span>
                    <span class="text-xs font-semibold text-slate-500">kader dipilih untuk di-enroll</span>
                </div>

                {{-- Quota Overflow Warning --}}
                <div x-show="isExceedingQuota" class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1" style="display: none;">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Melebihi Kuota</span>
                    </div>
                    <p class="font-normal leading-relaxed">
                        Anda memilih <strong x-text="selected.length"></strong> peserta, sedangkan sisa kuota yang tersedia hanya <strong x-text="remainingQuota"></strong> kursi.
                    </p>
                </div>

                {{-- Primary Action CTA --}}
                @if($kandidat->count())
                <button type="submit" form="enroll-form"
                        :disabled="selected.length === 0 || isExceedingQuota"
                        class="w-full py-2.5 px-4 rounded-lg font-semibold text-xs sm:text-sm transition duration-150 flex items-center justify-center gap-2 shadow-2xs"
                        :class="(selected.length === 0 || isExceedingQuota) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-200' : 'bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Enroll <span x-text="selected.length"></span> Peserta Terpilih</span>
                </button>
                @endif

                <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1.5">
                    <p class="font-semibold text-slate-700">Catatan Sistem:</p>
                    <ul class="list-disc pl-4 space-y-1 font-normal text-slate-500">
                        <li>Kader yang di-enroll otomatis terhubung dengan lembar presensi sesi &amp; ujian CBT.</li>
                        <li>Dapat diunduh kartu ID Card dan sertifikat kelulusan saat status terpenuhi.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
