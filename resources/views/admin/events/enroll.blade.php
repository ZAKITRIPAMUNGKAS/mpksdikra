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
}" class="space-y-5 pb-20 lg:pb-6">

    {{-- Breadcrumbs Navigation --}}
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#005c8a] transition">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke {{ $event->nama_event }}</span>
        </a>
        <span class="text-xs text-slate-400 font-medium hidden sm:inline">
            Status: <span class="capitalize font-semibold text-slate-700">{{ $event->status }}</span>
        </span>
    </div>

    {{-- Main 2-Column Workstation Grid --}}
    <div class="grid lg:grid-cols-12 gap-5 items-start">
        
        {{-- Left Column: Bento Filter & Candidate List (8 of 12 columns) --}}
        <div class="lg:col-span-8 space-y-4">
            
            {{-- Header & Filter Bento Console Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs relative overflow-hidden">
                <x-batik-corner position="top-right" class="w-36 h-36 text-[#005c8a] opacity-[0.14] pointer-events-none" />
                
                <div class="relative z-10 space-y-4">
                    {{-- Bento Header Row: Title & Mini Metric Strip --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-bold bg-[#005c8a]/10 text-[#005c8a] border border-[#005c8a]/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#005c8a] animate-pulse"></span>
                                    Registrasi &amp; Penugasan Peserta
                                </span>
                                <span class="text-[11px] text-slate-400 font-medium">
                                    Hal {{ $kandidat->currentPage() }} dari {{ max(1, $kandidat->lastPage()) }}
                                </span>
                            </div>
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Enroll Peserta Kegiatan</h1>
                            <p class="text-xs text-slate-500 font-normal">
                                Pilih kader aktif terverifikasi untuk didaftarkan secara resmi ke agenda perkaderan ini.
                            </p>
                        </div>

                        {{-- 3-Tile Micro Metric Bento Strip --}}
                        <div class="grid grid-cols-3 gap-2 shrink-0">
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl px-2.5 py-1.5 text-center min-w-[70px]">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Kandidat</span>
                                <span class="text-xs sm:text-sm font-black text-slate-800">{{ $kandidat->total() }}</span>
                            </div>
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl px-2.5 py-1.5 text-center min-w-[70px]">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Kuota</span>
                                <span class="text-xs sm:text-sm font-black {{ ($event->kuota && ($event->kuota - $event->peserta_aktif_count) <= 0) ? 'text-rose-600' : 'text-emerald-700' }}">
                                    {{ $event->kuota ? max(0, $event->kuota - $event->peserta_aktif_count) : '∞' }}
                                </span>
                            </div>
                            <div class="bg-sky-50/90 border border-sky-200/80 rounded-xl px-2.5 py-1.5 text-center min-w-[70px]">
                                <span class="text-[9px] font-bold text-[#005c8a] uppercase tracking-wider block">Terpilih</span>
                                <span class="text-xs sm:text-sm font-black text-[#005c8a]" x-text="selected.length">0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Smart Action Bento Banner --}}
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gradient-to-r from-sky-50 via-cyan-50/40 to-amber-50/40 border border-sky-200/80 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-white border border-sky-200 p-0.5 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader AI" class="w-full h-full object-contain rounded-md">
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-900 block truncate">Punya Banyak Nama dari Excel atau WhatsApp?</span>
                                <span class="text-[11px] text-slate-500 block truncate">Chat dengan <strong>SiKader</strong> untuk enroll massal dalam hitungan detik.</span>
                            </div>
                        </div>
                        <button type="button" @click="window.dispatchEvent(new CustomEvent('open-smart-enroll'))"
                                class="px-3.5 py-2 rounded-lg bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white text-xs font-bold shadow-2xs transition shrink-0 flex items-center gap-2 cursor-pointer">
                            <div class="w-4.5 h-4.5 rounded-full bg-white p-0.5 flex items-center justify-center shrink-0 overflow-hidden">
                                <img src="{{ asset('aset/sikader.jpg') }}" alt="SiKader" class="w-full h-full object-contain">
                            </div>
                            <span>Buka SiKader</span>
                        </button>
                    </div>

                    {{-- Search & Multi-Kategori Filter Bento Console --}}
                    <form method="GET" action="{{ route('admin.events.enroll', $event) }}" class="space-y-2.5 pt-2 border-t border-slate-100">
                        {{-- Integrated Search Bar --}}
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" name="q" value="{{ $query }}" placeholder="Cari nama, NIK, NBM, atau nama AUM kader…"
                                    class="w-full pl-9 pr-3.5 py-2 bg-slate-50/80 border border-slate-200 rounded-lg text-xs outline-none focus:bg-white focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs">
                                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <button type="submit" class="px-3.5 py-2 bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white text-xs font-bold rounded-lg shadow-2xs transition shrink-0 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span>Cari</span>
                            </button>
                            @if(!empty($query) || !empty($cabang) || !empty($kategori) || !empty($aum) || !empty($ortom) || !empty($sort))
                            <a href="{{ route('admin.events.enroll', $event) }}" class="px-3 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs font-semibold rounded-lg transition shrink-0 flex items-center" title="Reset Semua Filter">
                                Reset
                            </a>
                            @endif
                        </div>

                        {{-- Bento Filter Selector Grid (Compact 2-col on mobile, 5-col on desktop) --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                            {{-- Filter Kategori Basis --}}
                            <select name="kategori" onchange="this.form.submit()"
                                class="w-full px-2.5 py-1.5 bg-slate-50/80 hover:bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs cursor-pointer truncate">
                                <option value="">Semua Pilar</option>
                                <option value="aum" @selected(($kategori ?? '') === 'aum')>Amal Usaha (AUM)</option>
                                <option value="ortom" @selected(($kategori ?? '') === 'ortom')>ORTOM</option>
                                <option value="muhammadiyah" @selected(($kategori ?? '') === 'muhammadiyah')>Pimpinan</option>
                            </select>

                            {{-- Filter Spesifik Nama AUM --}}
                            <select name="aum" onchange="this.form.submit()"
                                class="w-full px-2.5 py-1.5 bg-slate-50/80 hover:bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs cursor-pointer truncate">
                                <option value="">Semua Nama AUM</option>
                                @foreach($aumList ?? [] as $item)
                                    <option value="{{ $item }}" @selected(($aum ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Filter Spesifik ORTOM --}}
                            <select name="ortom" onchange="this.form.submit()"
                                class="w-full px-2.5 py-1.5 bg-slate-50/80 hover:bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs cursor-pointer truncate">
                                <option value="">Semua ORTOM</option>
                                @foreach($ortomList ?? [] as $item)
                                    <option value="{{ $item }}" @selected(($ortom ?? '') === $item)>{{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Filter Cabang / PCM --}}
                            <select name="cabang" onchange="this.form.submit()"
                                class="w-full px-2.5 py-1.5 bg-slate-50/80 hover:bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs cursor-pointer truncate">
                                <option value="">Semua Cabang (PCM)</option>
                                @foreach($pcmList as $item)
                                    <option value="{{ $item }}" @selected(($cabang ?? '') === $item)>PCM {{ $item }}</option>
                                @endforeach
                            </select>

                            {{-- Sorting --}}
                            <select name="sort" onchange="this.form.submit()"
                                class="col-span-2 sm:col-span-1 w-full px-2.5 py-1.5 bg-slate-50/80 hover:bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-2xs cursor-pointer truncate">
                                <option value="">Urutan: Terbaru</option>
                                <option value="nama_asc" @selected(($sort ?? '') === 'nama_asc')>Nama (A - Z)</option>
                                <option value="nama_desc" @selected(($sort ?? '') === 'nama_desc')>Nama (Z - A)</option>
                                <option value="aum_asc" @selected(($sort ?? '') === 'aum_asc')>AUM (A - Z)</option>
                                <option value="cabang_asc" @selected(($sort ?? '') === 'cabang_asc')>Cabang (A - Z)</option>
                            </select>
                        </div>

                        {{-- Active Filter Pills Strip (If any active) --}}
                        @php
                            $hasActiveFilter = !empty($query) || !empty($cabang) || !empty($kategori) || !empty($aum) || !empty($ortom) || !empty($sort);
                        @endphp
                        @if($hasActiveFilter)
                        <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-slate-100 text-[11px]">
                            <span class="text-slate-400 font-medium">Filter:</span>
                            @if(!empty($query))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('q'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-700 transition border border-slate-200/80">
                                    <span>Cari: "{{ \Illuminate\Support\Str::limit($query, 16) }}"</span>
                                    <span class="font-bold text-slate-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            @if(!empty($kategori))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('kategori'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-sky-50 hover:bg-rose-50 text-[#005c8a] hover:text-rose-700 transition border border-sky-200/80">
                                    <span>Pilar: {{ ucfirst($kategori) }}</span>
                                    <span class="font-bold text-sky-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            @if(!empty($aum))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('aum'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 hover:bg-rose-50 text-amber-800 hover:text-rose-700 transition border border-amber-200/80">
                                    <span>AUM: {{ \Illuminate\Support\Str::limit($aum, 18) }}</span>
                                    <span class="font-bold text-amber-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            @if(!empty($ortom))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('ortom'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 hover:bg-rose-50 text-purple-800 hover:text-rose-700 transition border border-purple-200/80">
                                    <span>ORTOM: {{ \Illuminate\Support\Str::limit($ortom, 18) }}</span>
                                    <span class="font-bold text-purple-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            @if(!empty($cabang))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('cabang'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-rose-50 text-emerald-800 hover:text-rose-700 transition border border-emerald-200/80">
                                    <span>PCM: {{ $cabang }}</span>
                                    <span class="font-bold text-emerald-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            @if(!empty($sort))
                                <a href="{{ route('admin.events.enroll', array_merge([$event], request()->except('sort'))) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-700 transition border border-slate-200/80">
                                    <span>Urutan: {{ $sort }}</span>
                                    <span class="font-bold text-slate-400 hover:text-rose-600">&times;</span>
                                </a>
                            @endif
                            <a href="{{ route('admin.events.enroll', $event) }}" class="text-[11px] font-semibold text-rose-600 hover:underline ml-1">
                                Reset Semua
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Candidates List Container --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
                {{-- Toolbar Action Bar --}}
                <div class="p-3 sm:p-3.5 bg-slate-50/80 border-b border-slate-200/80 flex items-center justify-between gap-3">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" :checked="allOnPageSelected" @change="toggleAll"
                            class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[#005c8a]/30">
                        <span class="text-xs font-bold text-slate-700">
                            Pilih Semua di Halaman Ini (<span x-text="candidates.length"></span>)
                        </span>
                    </label>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md transition-colors"
                              :class="selected.length > 0 ? 'bg-sky-50 text-[#005c8a] border border-sky-200' : 'bg-slate-100 text-slate-500'">
                            <strong x-text="selected.length">0</strong> dipilih
                        </span>
                        <button type="button" x-show="selected.length > 0" @click="clearSelection()"
                                class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline transition"
                                style="display: none;">
                            Bersihkan
                        </button>
                    </div>
                </div>

                {{-- Candidate Bento Cards Form --}}
                <form method="POST" action="{{ route('admin.events.enroll.store', $event) }}" id="enroll-form" 
                      @submit="sessionStorage.removeItem('arqam_enroll_{{ $event->id }}')" 
                      class="p-3 sm:p-4">
                    @csrf

                    {{-- Hidden inputs for selected candidates on other pages --}}
                    <template x-for="id in selected.filter(id => !candidates.includes(id))" :key="id">
                        <input type="hidden" name="anggota_ids[]" :value="id">
                    </template>

                    {{-- Responsive Bento Grid of Candidate Tiles --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @forelse($kandidat as $k)
                        <label class="flex items-start gap-2.5 p-3 rounded-xl border transition-all duration-150 cursor-pointer select-none group relative"
                               :class="isSelected({{ $k->id }}) ? 'border-[#005c8a] bg-sky-50/60 ring-1 ring-[#005c8a]/30 shadow-2xs' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/50'">
                            
                            <input type="checkbox" name="anggota_ids[]" value="{{ $k->id }}" x-model.number="selected"
                                   class="w-4 h-4 rounded border-slate-300 text-[#005c8a] focus:ring-[#005c8a]/30 mt-0.5 shrink-0">
                            
                            {{-- Candidate Avatar / Foto Profil --}}
                            <div class="relative w-9 h-12 aspect-[3/4] rounded-lg overflow-hidden border bg-slate-100 shrink-0 shadow-2xs transition"
                                 :class="isSelected({{ $k->id }}) ? 'border-[#005c8a] ring-2 ring-[#005c8a]/40' : 'border-slate-200 group-hover:border-slate-300'">
                                <img src="{{ route('foto.anggota', [$k, 'v' => 2]) }}"
                                     alt="{{ $k->nama_lengkap }}"
                                     class="w-full h-full object-cover object-top"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                <div class="hidden w-full h-full flex items-center justify-center font-bold text-xs"
                                     :class="isSelected({{ $k->id }}) ? 'bg-[#005c8a] text-white' : 'bg-slate-100 text-slate-700'">
                                    {{ strtoupper(substr($k->nama_lengkap, 0, 1)) }}
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-900 text-xs truncate block max-w-[170px] sm:max-w-[200px]" title="{{ $k->nama_lengkap }}">
                                        {{ $k->nama_lengkap }}
                                    </span>
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
                                        Aktif
                                    </span>
                                </div>

                                {{-- Badges Strip --}}
                                <div class="flex items-center gap-1 flex-wrap mt-1">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-[#005c8a] border border-sky-200 shrink-0 truncate max-w-[130px]">
                                        PCM {{ $k->pcm ?: $k->kecamatan }}
                                    </span>
                                    
                                    @if(!empty($k->nama_aum))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shrink-0 truncate max-w-[130px]" title="{{ $k->nama_aum }}">
                                        {{ $k->nama_aum }}
                                    </span>
                                    @endif

                                    @php
                                        $kCleanOrtom = is_array($k->keaktifan_ortom) ? array_filter($k->keaktifan_ortom, fn($x) => $x !== 'Tidak aktif') : [];
                                    @endphp
                                    @if(!empty($kCleanOrtom))
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-800 border border-purple-200 shrink-0 truncate max-w-[130px]" title="{{ implode(', ', $kCleanOrtom) }}">
                                        {{ implode(', ', $kCleanOrtom) }}
                                    </span>
                                    @endif
                                </div>

                                <p class="text-[11px] text-slate-400 font-normal mt-1 truncate">
                                    {{ $k->desa_kelurahan ? $k->desa_kelurahan . ', ' : '' }}Kec. {{ $k->kecamatan }} &bull; NIK: <span class="font-mono text-slate-600 font-medium">{{ substr($k->nik, 0, 6) }}****</span>
                                </p>
                            </div>
                        </label>
                        @empty
                        <div class="col-span-full text-center py-12 px-4">
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
                            @if(!empty($query) || !empty($cabang) || !empty($kategori) || !empty($aum) || !empty($ortom))
                            <a href="{{ route('admin.events.enroll', $event) }}" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold text-[#005c8a] hover:bg-sky-50 transition">
                                Tampilkan Semua Kandidat
                            </a>
                            @endif
                        </div>
                        @endforelse
                    </div>
                </form>

                @if($kandidat->hasPages())
                <div class="px-4 py-3 border-t border-slate-200/80 bg-slate-50/60">
                    {{ $kandidat->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Sticky Summary & Action Panel (4 of 12 columns) --}}
        <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-20">
            {{-- Event Information & Capacity Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Informasi Kegiatan</span>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug">{{ $event->nama_event }}</h2>
                
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-2 text-xs text-slate-600">
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
                        <span class="truncate">{{ $event->lokasi }}</span>
                    </div>
                    @endif
                </div>

                {{-- Capacity & Quota Gauge --}}
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-bold text-slate-700">Kapasitas Peserta</span>
                        @if($event->kuota)
                        <span class="font-semibold text-slate-600">
                            {{ $event->peserta_aktif_count }} / {{ $event->kuota }} Kader
                        </span>
                        @else
                        <span class="font-semibold text-slate-600">
                            {{ $event->peserta_aktif_count }} Terdaftar
                        </span>
                        @endif
                    </div>

                    @if($event->kuota)
                    @php
                        $percentage = min(100, round(($event->peserta_aktif_count / $event->kuota) * 100));
                        $sisa = max(0, $event->kuota - $event->peserta_aktif_count);
                    @endphp
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-1.5">
                        <div class="bg-[#005c8a] h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">{{ $percentage }}% terisi</span>
                        <span class="font-bold {{ $sisa > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $sisa > 0 ? 'Sisa ' . $sisa . ' kursi' : 'Kuota Penuh' }}
                        </span>
                    </div>
                    @else
                    <div class="p-2 rounded-lg bg-sky-50 text-[#005c8a] text-[11px] font-medium border border-sky-200">
                        Event ini tidak memiliki batasan kuota.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Action Confirmation Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Eksekusi Pendaftaran</span>
                
                <div class="flex items-baseline gap-2 mb-3">
                    <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight" x-text="selected.length">0</span>
                    <span class="text-xs font-semibold text-slate-500">kader dipilih untuk di-enroll</span>
                </div>

                {{-- Quota Overflow Warning --}}
                <div x-show="isExceedingQuota" class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1" style="display: none;">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Melebihi Kuota</span>
                    </div>
                    <p class="font-normal text-[11px] leading-relaxed">
                        Anda memilih <strong x-text="selected.length"></strong> peserta, sedangkan sisa kuota yang tersedia hanya <strong x-text="remainingQuota"></strong> kursi.
                    </p>
                </div>

                {{-- Primary Action CTA (Desktop) --}}
                @if($kandidat->count())
                <button type="submit" form="enroll-form"
                        :disabled="selected.length === 0 || isExceedingQuota"
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs sm:text-sm transition duration-150 flex items-center justify-center gap-2 shadow-2xs"
                        :class="(selected.length === 0 || isExceedingQuota) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-200' : 'bg-[#005c8a] hover:bg-[#00486d] active:bg-[#003855] text-white'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Enroll <span x-text="selected.length"></span> Peserta Terpilih</span>
                </button>
                @endif

                <div class="mt-3.5 pt-3 border-t border-slate-100 text-[11px] text-slate-500 space-y-1">
                    <p class="font-bold text-slate-700">Catatan Sistem:</p>
                    <ul class="list-disc pl-4 space-y-0.5 font-normal text-slate-500">
                        <li>Kader yang di-enroll otomatis terhubung dengan lembar presensi &amp; CBT.</li>
                        <li>Dapat dicetak kartu ID Card dan sertifikat kelulusan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Floating Action Dock --}}
    <div x-show="selected.length > 0"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 p-3 z-30 shadow-xl"
         style="display: none;">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#005c8a] animate-pulse"></span>
                    <span class="text-xs sm:text-sm font-bold text-slate-900">
                        <strong class="text-[#005c8a]" x-text="selected.length">0</strong> Kader Terpilih
                    </span>
                </div>
                <div x-show="isExceedingQuota" class="text-[10px] text-rose-600 font-semibold mt-0.5" style="display: none;">
                    Melebihi sisa kuota (<span x-text="remainingQuota"></span> kursi)!
                </div>
                <div x-show="!isExceedingQuota && kuota > 0" class="text-[10px] text-slate-400 font-medium mt-0.5">
                    Sisa kuota: <span x-text="remainingQuota"></span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="clearSelection()"
                        class="px-2.5 py-1.5 text-xs font-semibold text-slate-500 hover:text-rose-600 transition">
                    Batal
                </button>
                <button type="submit" form="enroll-form"
                        :disabled="selected.length === 0 || isExceedingQuota"
                        class="px-3.5 py-2 rounded-xl font-bold text-xs transition duration-150 flex items-center gap-1.5 shadow-2xs"
                        :class="(selected.length === 0 || isExceedingQuota) ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-[#005c8a] hover:bg-[#00486d] text-white'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Enroll Terpilih</span>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Smart Enroll & Bulk Matcher Modal --}}
@include('admin.events.partials.smart-enroll-modal')
@endsection
