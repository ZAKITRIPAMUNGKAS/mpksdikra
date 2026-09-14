@extends('layouts.guest')

@section('title', 'Pendaftaran Kader — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen py-6 sm:py-12 px-3 sm:px-4 bg-[#f8fafc] text-slate-800 selection:bg-sky-500 selection:text-white">
    <div class="max-w-3xl mx-auto">
        {{-- Top Sleek Navigation Bar --}}
        <div class="flex items-center justify-between gap-2 mb-3.5 px-1">
            <a href="/" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-900 transition py-1">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Beranda</span>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-sky-50 border border-sky-200/80 text-xs font-bold text-sky-700 hover:bg-sky-100 transition shadow-2xs">
                <span>Masuk Portal</span>
                <span>&rarr;</span>
            </a>
        </div>

        @if(session('success') && !request()->has('baru'))
            {{-- Post-Registration Success & Verification Status View --}}
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-5 sm:p-8 md:p-10 mb-6 relative overflow-hidden">
                <x-batik-corner position="top-right" class="w-44 h-44 sm:w-56 sm:h-56 text-[#005c8a] opacity-[0.14]" />

                {{-- Header Sukses dengan Logo MPKSDI --}}
                <div class="relative z-10 text-center max-w-xl mx-auto mb-7">
                    <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-14 sm:h-16 w-auto mx-auto mb-3 drop-shadow-xs">

                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 uppercase tracking-wider mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Pendaftaran Selesai &amp; Tersimpan
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Alhamdulillah, Anda Telah Berhasil Terdaftar!
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed font-normal">
                        Data formulir pendaftaran dan berkas pasfoto Anda telah kami terima secara lengkap di pangkalan data MPKSDI PDM Karanganyar.
                    </p>
                </div>

                {{-- Status Card Highlight: MENUNGGU VERIFIKASI DATA --}}
                <div class="relative z-10 max-w-xl mx-auto rounded-2xl bg-amber-50/80 border border-amber-200/90 p-4 sm:p-5 mb-6 text-amber-950 shadow-2xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Status Akun Saat Ini:</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs">
                                    Menunggu Verifikasi Data
                                </span>
                            </div>
                            <p class="text-xs text-amber-900/90 leading-relaxed">
                                Berkas pendaftaran dan kesesuaian pasfoto Anda saat ini sedang dalam antrean verifikasi oleh tim sekretariat MPKSDI PDM Karanganyar. Notifikasi persetujuan akun akan disampaikan melalui WhatsApp.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Data Pendaftar --}}
                @php
                    $regData = session('registered_data', []);
                @endphp
                @if(!empty($regData))
                <div class="relative z-10 max-w-xl mx-auto rounded-xl bg-slate-50 border border-slate-200/80 p-4 sm:p-5 mb-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Ringkasan Identitas Pendaftar</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nama Lengkap:</span>
                            <span class="font-bold text-slate-800">{{ $regData['nama'] ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">NIK KTP:</span>
                            <span class="font-mono font-bold text-slate-800">
                                @if(!empty($regData['nik']))
                                    {{ substr($regData['nik'], 0, 6) }}******{{ substr($regData['nik'], -4) }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nomor WhatsApp:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $regData['no_wa'] ?? '-' }}</span>
                        </div>
                        @if(!empty($regData['kecamatan']))
                        <div>
                            <span class="text-slate-400 block text-[11px]">Kecamatan:</span>
                            <span class="font-bold text-slate-800">{{ $regData['kecamatan'] }}</span>
                        </div>
                        @endif
                        @if(!empty($regData['pcm']))
                        <div>
                            <span class="text-slate-400 block text-[11px]">Cabang (PCM):</span>
                            <span class="font-bold text-slate-800">PCM {{ $regData['pcm'] }}</span>
                        </div>
                        @endif
                        @if(!empty($regData['prm']))
                        <div>
                            <span class="text-slate-400 block text-[11px]">Ranting (PRM):</span>
                            <span class="font-bold text-slate-800">{{ $regData['prm'] }}</span>
                        </div>
                        @endif
                        @if(!empty($regData['email']))
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 block text-[11px]">Alamat Email:</span>
                            <span class="font-mono font-medium text-slate-700">{{ $regData['email'] }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Informasi Akun Login Kader --}}
                    <div class="mt-4 p-3.5 bg-amber-50/90 border border-amber-200/90 rounded-xl text-xs space-y-1 text-left">
                        <div class="font-bold text-amber-900 flex items-center gap-1.5">
                            <span aria-hidden="true">🔑</span>
                            <span>Akses Masuk Portal Kader (Setelah Diverifikasi Admin):</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-700 pt-1">
                            <div class="bg-white/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-slate-500 block text-[10px]">Username:</span>
                                <strong class="font-mono text-slate-900 text-xs">{{ $regData['username'] ?? ($regData['no_wa'] ?? '-') }}</strong>
                                <span class="text-[10px] text-slate-400 block mt-0.5">(Nomor WhatsApp)</span>
                            </div>
                            <div class="bg-white/80 p-2 rounded-lg border border-amber-200/80">
                                <span class="text-slate-500 block text-[10px]">Kata Sandi:</span>
                                <strong class="font-mono text-slate-900 text-xs">{{ $regData['password_default'] ?? 'Tanggal Lahir Anda' }}</strong>
                                <span class="text-[10px] text-slate-400 block mt-0.5">(Format: DDMMYYYY)</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Timeline 3 Langkah Verifikasi --}}
                <div class="relative z-10 max-w-xl mx-auto mb-8 border-t border-slate-100 pt-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 text-center">
                        Tahapan Pendaftaran &amp; Verifikasi
                    </h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        {{-- Step 1 --}}
                        <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs mb-1.5 shadow-2xs">
                                ✓
                            </div>
                            <span class="text-xs font-extrabold text-emerald-900 block leading-tight">1. Pendaftaran</span>
                            <span class="text-[10px] text-emerald-700 mt-0.5 font-medium">Selesai Dikirim</span>
                        </div>

                        {{-- Step 2 --}}
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-950 flex flex-col items-center ring-2 ring-amber-400/30">
                            <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs mb-1.5 shadow-2xs animate-pulse">
                                ⏳
                            </div>
                            <span class="text-xs font-extrabold text-amber-900 block leading-tight">2. Verifikasi Data</span>
                            <span class="text-[10px] text-amber-700 mt-0.5 font-semibold">Sedang Berjalan</span>
                        </div>

                        {{-- Step 3 --}}
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs mb-1.5">
                                3
                            </div>
                            <span class="text-xs font-bold text-slate-600 block leading-tight">3. Akun Aktif</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Portal Baitul Arqam</span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="relative z-10 max-w-xl mx-auto space-y-3">
                    <div class="flex flex-col sm:flex-row items-center gap-2.5">
                        @php
                            $searchIdentifier = $regData['nik'] ?? ($regData['no_wa'] ?? '');
                        @endphp
                        <a href="{{ route('cek-status', $searchIdentifier ? ['identifier' => $searchIdentifier] : []) }}"
                           class="w-full sm:flex-1 py-2.5 px-4 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold text-xs sm:text-sm rounded-xl shadow-2xs transition flex items-center justify-center gap-2 text-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Periksa Status Pendaftaran</span>
                        </a>

                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto py-2.5 px-5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-bold text-xs sm:text-sm rounded-xl shadow-2xs transition flex items-center justify-center gap-1.5">
                            <span>Masuk Portal</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-1 hover:text-slate-900 transition">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            <span>Kembali ke Beranda</span>
                        </a>

                        <a href="{{ route('anggota.register', ['baru' => 1]) }}"
                           class="font-semibold text-sky-700 hover:text-sky-900 hover:underline transition">
                            Ingin mendaftarkan kader lain? Formulir Baru &rarr;
                        </a>
                    </div>
                </div>

                {{-- Clean up draft in localStorage --}}
                <script>
                    try {
                        localStorage.removeItem('mpksdi_register_draft_v1');
                    } catch (e) {}
                </script>
            </div>
        @else
            {{-- Form Header with MPKSDI Crest & Batik Corner (Compact & Senior-Friendly) --}}
            <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-3.5 sm:p-6 mb-3.5 sm:mb-5 relative overflow-hidden">
                <x-batik-corner position="top-right" class="w-28 h-28 sm:w-40 sm:h-40 text-[#005c8a] opacity-[0.18]" />
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 relative z-10">
                    <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-10 sm:h-14 w-auto shrink-0 drop-shadow-xs self-start sm:self-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 border border-sky-200/80 text-[11px] sm:text-xs font-semibold text-sky-800 uppercase tracking-wider mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-ping"></span>
                            Pangkalan Data Kader Persyarikatan
                        </span>
                        <h1 class="text-lg sm:text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Daftar Anggota Muhammadiyah</h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 leading-relaxed font-normal">Lengkapi biodata kader secara akurat untuk verifikasi dan kepesertaan Baitul Arqam.</p>
                    </div>
                </div>
            </div>

        @if($errors->any())
            <div class="mb-4 sm:mb-5 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-950 text-sm shadow-2xs">
                <div class="font-semibold mb-1 flex items-center gap-1.5 text-xs uppercase tracking-wider text-rose-900">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mohon lengkapi atau perbaiki isian berikut:
                </div>
                <ul class="space-y-1 list-disc pl-5 text-xs text-rose-800">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('anggota.register.store') }}" enctype="multipart/form-data" novalidate
              id="form-pendaftaran"
              class="bg-white rounded-xl border border-slate-200/90 shadow-2xs p-4 sm:p-7 md:p-8"
              x-data="registrationForm()"
              @input.debounce.400ms="saveDraft()"
              @change="saveDraft()"
              @submit.prevent="submitForm($event)">
            @csrf

            {{-- Restored Draft Notification Banner --}}
            <div x-show="draftRestored" x-cloak class="mb-5 px-4 py-3 rounded-xl bg-sky-50 border border-sky-200/90 text-sky-950 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-2xs font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-bold block text-sky-900 text-xs sm:text-sm">Draf formulir Anda otomatis dipulihkan</span>
                        <span class="text-[11px] text-sky-700 font-normal">Isian formulir sebelumnya telah dimuat kembali dari memori browser ini.</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                    <button type="button" @click="clearDraft(true)" class="px-3 py-1.5 rounded-lg border border-rose-200 bg-white hover:bg-rose-50 text-rose-700 text-xs font-bold transition shadow-2xs">
                        Hapus Draf &amp; Ulangi
                    </button>
                    <button type="button" @click="draftRestored = false" class="text-sky-400 hover:text-sky-700 p-1 text-sm font-bold leading-none" title="Tutup">
                        &times;
                    </button>
                </div>
            </div>

            {{-- Dynamic Step Error Notification Banner --}}
            <div x-show="stepErrorBanner" x-cloak class="mb-5 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-950 text-xs sm:text-sm flex items-start gap-2.5 shadow-2xs">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <span class="font-bold block text-rose-900 mb-0.5">Perhatian: Isian Wajib Belum Lengkap</span>
                    <span x-text="stepErrorBanner"></span>
                </div>
                <button type="button" @click="stepErrorBanner = null" class="text-rose-400 hover:text-rose-700 font-bold px-1 text-sm">&times;</button>
            </div>

            {{-- Stepper Progress Header (High Contrast, Large Tap Target for Seniors) --}}
            <div class="mb-6 sm:mb-8 pb-5 border-b border-slate-200">
                {{-- Mobile Active Step Indicator --}}
                <div class="sm:hidden mb-3.5 p-3.5 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-600 text-white font-black text-sm flex items-center justify-center shadow-xs"
                              x-text="tab === 'diri' ? '1' : (tab === 'agama' ? '2' : (tab === 'org' ? '3' : '4'))"></span>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-sky-800 block leading-none">Langkah Formulir:</span>
                            <span class="text-sm font-extrabold text-slate-900"
                                  x-text="tab === 'diri' ? '1. Identitas & Pasfoto' : (tab === 'agama' ? '2. Keagamaan & Ibadah' : (tab === 'org' ? '3. Data Organisasi' : '4. Kemasyarakatan'))"></span>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-sky-800 bg-white px-2.5 py-1 rounded-lg border border-sky-200 shadow-2xs"
                          x-text="tab === 'diri' ? '1 dari 4' : (tab === 'agama' ? '2 dari 4' : (tab === 'org' ? '3 dari 4' : '4 dari 4'))"></span>
                </div>

                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="hidden sm:inline-block text-xs font-bold uppercase tracking-wider text-slate-500">
                        Tahapan Pengisian Formulir:
                    </span>
                    {{-- Auto-save status indicator --}}
                    <div class="flex items-center gap-2 text-xs font-medium ml-auto">
                        <template x-if="hasSavedDraft">
                            <div class="flex items-center gap-1.5 text-emerald-800 bg-emerald-50 border border-emerald-300 px-3 py-1 rounded-full shadow-2xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-bold" x-text="lastSavedText || 'Draf tersimpan'"></span>
                                <span class="text-slate-300 mx-0.5">&bull;</span>
                                <button type="button" @click="clearDraft(true)" class="text-slate-600 hover:text-rose-600 font-bold underline transition">
                                    Hapus Draf
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2 sm:gap-3">
                    @php
                        $steps = [
                            ['key' => 'diri', 'no' => '1', 'title' => 'Identitas'],
                            ['key' => 'agama', 'no' => '2', 'title' => 'Keagamaan'],
                            ['key' => 'org', 'no' => '3', 'title' => 'Organisasi'],
                            ['key' => 'kemasyarakatan', 'no' => '4', 'title' => 'Kemasyarakatan'],
                        ];
                    @endphp
                    @foreach($steps as $s)
                    <button type="button" @click="goTo('{{ $s['key'] }}')"
                        class="text-left group focus:outline-none transition p-1.5 sm:p-2 rounded-2xl hover:bg-slate-50"
                        :class="tab==='{{ $s['key'] }}' ? 'bg-sky-50/70 border border-sky-200' : ''">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl text-xs sm:text-sm font-black flex items-center justify-center transition-all duration-150 shrink-0"
                                :class="tab==='{{ $s['key'] }}' ? 'bg-sky-600 text-white font-black shadow-xs ring-2 ring-sky-300' : 'bg-slate-100 text-slate-700 group-hover:bg-slate-200'">
                                {{ $s['no'] }}
                            </span>
                            <span class="text-xs sm:text-sm font-bold hidden sm:inline truncate"
                                :class="tab==='{{ $s['key'] }}' ? 'text-sky-900 font-extrabold' : 'text-slate-600 group-hover:text-slate-900'">
                                {{ $s['title'] }}
                            </span>
                        </div>
                        <div class="w-full h-1.5 sm:h-2 rounded-full overflow-hidden bg-slate-200">
                            <div class="h-full transition-all duration-200"
                                :class="tab==='{{ $s['key'] }}' ? 'bg-amber-400 w-full' : 'bg-transparent w-0'"></div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab 1: Data Diri & Akun (Desain Ramah Lansia / 40+ Tahun) --}}
            <div x-show="tab==='diri'" class="space-y-5 sm:space-y-6">
                <div>
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                        Nama Lengkap (beserta gelar) <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                    </label>
                    <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" autocomplete="name" required placeholder="Contoh: H. Agus Sulaiman, S.Pd"
                        @input="clearError('nama_lengkap')"
                        :class="clientErrors.nama_lengkap ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('nama_lengkap') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                        class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('nama_lengkap') ? 'true' : 'false' }}">
                    <template x-if="clientErrors.nama_lengkap">
                        <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.nama_lengkap"></p>
                    </template>
                    @error('nama_lengkap') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            NIK KTP (16 digit angka) <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <input name="nik" value="{{ old('nik') }}" maxlength="16" pattern="\d{16}" autocomplete="off" required placeholder="3313xxxxxxxxxxxx"
                            @input="clearError('nik')"
                            :class="clientErrors.nik ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('nik') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-mono text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('nik') ? 'true' : 'false' }}">
                        <span class="text-xs sm:text-sm text-slate-500 font-normal mt-1 block">Nomor Induk Kependudukan sesuai KTP (16 digit angka).</span>
                        <template x-if="clientErrors.nik">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.nik"></p>
                        </template>
                        @error('nik') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Jenis Kelamin <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <select name="jenis_kelamin" autocomplete="sex" required
                            @change="clearError('jenis_kelamin')"
                            :class="clientErrors.jenis_kelamin ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('jenis_kelamin') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('jenis_kelamin') ? 'true' : 'false' }}">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            @foreach(config('arqam.opsi_form.jenis_kelamin') as $val => $label)
                            <option value="{{ $val }}" @selected(old('jenis_kelamin')===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <template x-if="clientErrors.jenis_kelamin">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.jenis_kelamin"></p>
                        </template>
                        @error('jenis_kelamin') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Tempat Lahir <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <input name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Contoh: Karanganyar"
                            @input="clearError('tempat_lahir')"
                            :class="clientErrors.tempat_lahir ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('tempat_lahir') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('tempat_lahir') ? 'true' : 'false' }}">
                        <template x-if="clientErrors.tempat_lahir">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.tempat_lahir"></p>
                        </template>
                        @error('tempat_lahir') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Tanggal Lahir <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" autocomplete="bday" required
                            @change="clearError('tanggal_lahir')"
                            :class="clientErrors.tanggal_lahir ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('tanggal_lahir') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('tanggal_lahir') ? 'true' : 'false' }}">
                        <template x-if="clientErrors.tanggal_lahir">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.tanggal_lahir"></p>
                        </template>
                        @error('tanggal_lahir') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Status Pernikahan <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <select name="status_pernikahan" required
                            @change="clearError('status_pernikahan')"
                            :class="clientErrors.status_pernikahan ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('status_pernikahan') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('status_pernikahan') ? 'true' : 'false' }}">
                            <option value="">-- Pilih Status --</option>
                            @foreach(config('arqam.opsi_form.status_pernikahan') as $v)
                            <option value="{{ $v }}" @selected(old('status_pernikahan')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                        <template x-if="clientErrors.status_pernikahan">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.status_pernikahan"></p>
                        </template>
                        @error('status_pernikahan') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                        Alamat Lengkap Rumah <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                    </label>
                    <textarea name="alamat_rumah" required rows="2" autocomplete="street-address" placeholder="Nama jalan, RT/RW, dukuh/perumahan tempat tinggal"
                        @input="clearError('alamat_rumah')"
                        :class="clientErrors.alamat_rumah ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('alamat_rumah') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                        class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white resize-none transition" aria-invalid="{{ $errors->has('alamat_rumah') ? 'true' : 'false' }}">{{ old('alamat_rumah') }}</textarea>
                    <template x-if="clientErrors.alamat_rumah">
                        <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.alamat_rumah"></p>
                    </template>
                    @error('alamat_rumah') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                </div>

                {{-- Dropdown Bertingkat: Kabupaten (fixed) → Kecamatan → Desa/Kelurahan --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">

                    {{-- Desa / Kelurahan --}}
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Desa / Kelurahan <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <select name="desa_kelurahan" x-model="selectedDesa" required
                            @change="clearError('desa_kelurahan')"
                            :disabled="!selectedKecamatan"
                            :class="clientErrors.desa_kelurahan ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('desa_kelurahan') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px] disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                            aria-invalid="{{ $errors->has('desa_kelurahan') ? 'true' : 'false' }}">
                            <option value="">-- Pilih Desa/Kelurahan --</option>
                            <template x-for="d in desaList" :key="d">
                                <option :value="d" :selected="d === selectedDesa" x-text="d"></option>
                            </template>
                        </select>
                        <template x-if="clientErrors.desa_kelurahan">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.desa_kelurahan"></p>
                        </template>
                        @error('desa_kelurahan') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-1" x-show="!selectedKecamatan">Pilih kecamatan terlebih dahulu</p>
                    </div>

                    {{-- Kecamatan --}}
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Kecamatan <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <select name="kecamatan" x-model="selectedKecamatan" required
                            @change="onKecamatanChange(); clearError('kecamatan')"
                            :class="clientErrors.kecamatan ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('kecamatan') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]"
                            aria-invalid="{{ $errors->has('kecamatan') ? 'true' : 'false' }}">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach(array_keys(config('arqam.opsi_form.wilayah_karanganyar')) as $kec)
                                <option value="{{ $kec }}" {{ old('kecamatan') === $kec ? 'selected' : '' }}>{{ $kec }}</option>
                            @endforeach
                        </select>
                        <template x-if="clientErrors.kecamatan">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.kecamatan"></p>
                        </template>
                        @error('kecamatan') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kabupaten (fixed Karanganyar) --}}
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Kabupaten <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <input type="text" name="kabupaten" value="Karanganyar" readonly
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-200 rounded-2xl bg-slate-50 text-slate-500 cursor-not-allowed min-h-[50px] sm:min-h-[52px]">
                        <span class="text-xs text-slate-400 mt-1 block">Khusus anggota Muhammadiyah Kabupaten Karanganyar</span>
                    </div>
                </div>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Nomor WhatsApp / HP Aktif <span class="text-rose-600 font-black text-base" title="Wajib diisi">*</span>
                        </label>
                        <input name="no_wa" value="{{ old('no_wa') }}" placeholder="Contoh: 081234567890" autocomplete="tel" required
                            @input="clearError('no_wa')"
                            :class="clientErrors.no_wa ? 'border-rose-500 bg-rose-50/30 ring-4 ring-rose-200/50' : '{{ $errors->has('no_wa') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }}'"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-mono text-slate-900 border-2 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('no_wa') ? 'true' : 'false' }}">
                        <span class="text-xs sm:text-sm text-slate-500 font-normal mt-1 block">Digunakan untuk Username login dan notifikasi WhatsApp.</span>
                        <template x-if="clientErrors.no_wa">
                            <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold flex items-center gap-1" x-text="clientErrors.no_wa"></p>
                        </template>
                        @error('no_wa') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                            Alamat Email (Opsional)
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Contoh: kader@gmail.com" autocomplete="email"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 {{ $errors->has('email') ? 'border-rose-500 bg-rose-50/30' : 'border-slate-300 hover:border-slate-400' }} rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px] sm:min-h-[52px]" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                        <span class="text-xs sm:text-sm text-slate-500 font-normal mt-1 block">Boleh dikosongkan jika tidak memiliki alamat email.</span>
                        @error('email') <p class="text-rose-600 text-xs sm:text-sm mt-1.5 font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Pasfoto Upload Component with Instant Preview & Proportional Face Fitter --}}
                <div @change="clearError('foto')">
                    <x-pasfoto-fitter name="foto" :required="true" inputId="foto-register-input" label="Pasfoto Formal (Wajib untuk ID Card BA)" />
                    <template x-if="clientErrors.foto">
                        <p class="text-xs sm:text-sm text-rose-600 font-bold mt-2 flex items-center gap-1" x-text="clientErrors.foto"></p>
                    </template>
                    @error('foto')
                        <p class="text-xs sm:text-sm text-rose-600 font-bold mt-2">{{ $message }}</p>
                    @enderror
                </div>



                <div class="flex items-center justify-end pt-5 border-t border-slate-200">
                    <button type="button" @click="goTo('agama')"
                        class="w-full sm:w-auto min-h-[52px] sm:min-h-[56px] px-8 sm:px-10 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-extrabold text-base sm:text-lg rounded-2xl shadow-md transition flex items-center justify-center gap-3">
                        <span>Lanjut: Data Keagamaan</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 2: Keagamaan (Desain Nyaman & Mudah Disentuh) --}}
            <div x-show="tab==='agama'" class="space-y-6" style="display: none;">
                <div>
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Pendidikan SD / Sederajat (Opsional)</label>
                    <input name="pendidikan_sd" value="{{ old('pendidikan_sd') }}" placeholder="Contoh: MIM Karanganyar / SDN 1 Karanganyar"
                        class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Bahasa yang Dikuasai</label>
                        <span class="text-xs text-sky-800 font-semibold bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mb-2.5">Sentuh bahasa yang biasa Bapak/Ibu gunakan sehari-hari:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach(config('arqam.opsi_form.bahasa_dikuasai') as $v)
                        <label class="flex items-center gap-3 text-sm sm:text-base font-bold text-slate-800 p-3.5 sm:p-4 rounded-2xl border-2 border-slate-200/90 hover:border-sky-300 hover:bg-sky-50/40 bg-white cursor-pointer select-none transition min-h-[50px] shadow-2xs">
                            <input type="checkbox" name="bahasa_dikuasai[]" value="{{ $v }}"
                                @checked(in_array($v, old('bahasa_dikuasai', []))) class="w-5 h-5 rounded-md border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Kemampuan Baca Al-Qur'an</label>
                        <select name="kemampuan_baca_quran" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Kemampuan --</option>
                            @foreach(config('arqam.opsi_form.kemampuan_baca_quran') as $v)
                            <option value="{{ $v }}" @selected(old('kemampuan_baca_quran')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Hafalan Al-Qur'an</label>
                        <select name="hafalan_quran" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Jumlah Hafalan --</option>
                            @foreach(config('arqam.opsi_form.hafalan_quran') as $v)
                            <option value="{{ $v }}" @selected(old('hafalan_quran')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Aktivitas Sholat Berjamaah di Masjid</label>
                        <select name="aktivitas_sholat_masjid" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Frekuensi Sholat --</option>
                            @foreach(config('arqam.opsi_form.aktivitas_sholat_masjid') as $v)
                            <option value="{{ $v }}" @selected(old('aktivitas_sholat_masjid')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Aktivitas Mengikuti Kajian</label>
                        <select name="aktivitas_kajian_agama" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Frekuensi Kajian --</option>
                            @foreach(config('arqam.opsi_form.aktivitas_kajian_agama') as $v)
                            <option value="{{ $v }}" @selected(old('aktivitas_kajian_agama')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Koleksi Buku Agama di Rumah</label>
                        <select name="jumlah_buku_agama" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Jumlah Buku --</option>
                            @foreach(config('arqam.opsi_form.jumlah_buku_agama') as $v)
                            <option value="{{ $v }}" @selected(old('jumlah_buku_agama')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Langganan Majalah Suara Muhammadiyah</label>
                        <select name="langganan_suara_muhammadiyah" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <option value="">-- Pilih Status Berlangganan --</option>
                            @foreach(config('arqam.opsi_form.langganan_suara_muhammadiyah') as $v)
                            <option value="{{ $v }}" @selected(old('langganan_suara_muhammadiyah')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">Penyaluran ZIS (Zakat, Infaq, Shodaqoh)</label>
                        <span class="text-xs text-sky-800 font-semibold bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mb-2.5">Sentuh lembaga ZIS tempat biasa Bapak/Ibu menyalurkan zakat/infaq:</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        @foreach(config('arqam.opsi_form.lembaga_zis') as $v)
                        <label class="flex items-center gap-3 text-sm sm:text-base font-bold text-slate-800 p-3.5 sm:p-4 rounded-2xl border-2 border-slate-200/90 hover:border-sky-300 hover:bg-sky-50/40 bg-white cursor-pointer select-none transition min-h-[50px] shadow-2xs">
                            <input type="checkbox" name="lembaga_zis[]" value="{{ $v }}"
                                @checked(in_array($v, old('lembaga_zis', []))) class="w-5 h-5 rounded-md border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-5 border-t border-slate-200">
                    <button type="button" @click="goTo('diri')"
                        class="min-h-[50px] sm:min-h-[54px] px-6 sm:px-7 border-2 border-slate-300 bg-white text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition flex items-center justify-center gap-2 text-sm sm:text-base shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="goTo('org')"
                        class="min-h-[50px] sm:min-h-[54px] px-7 sm:px-9 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-extrabold text-sm sm:text-base rounded-2xl shadow-md transition flex items-center justify-center gap-2.5 whitespace-nowrap">
                        <span>Lanjut: Data Organisasi</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 3: Organisasi & Kemuhammadiyahan (Desain Jelas & Nyaman) --}}
            <div x-show="tab==='org'" class="space-y-6 sm:space-y-7" style="display: none;">
                {{-- 1. Keaktifan di Pimpinan Muhammadiyah --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            1. Keaktifan di Pimpinan Muhammadiyah
                        </label>
                        <span class="text-xs text-sky-800 font-semibold bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500">Centang tingkatan pimpinan tempat Bapak/Ibu aktif dan isikan amanah/jabatannya:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        @foreach(config('arqam.opsi_form.keaktifan_muhammadiyah') as $v)
                        <div class="p-4 sm:p-4.5 rounded-2xl border-2 transition-all shadow-2xs"
                             :class="isMuhammadiyahChecked('{{ $v }}') ? 'bg-sky-50/70 border-sky-500 ring-2 ring-sky-200' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/70'">
                            <label class="flex items-center gap-3 text-sm sm:text-base text-slate-900 font-bold cursor-pointer select-none">
                                <input type="checkbox" name="keaktifan_muhammadiyah[]" value="{{ $v }}"
                                    @checked(in_array($v, old('keaktifan_muhammadiyah', [])))
                                    @change="toggleMuhammadiyah('{{ $v }}', $el.checked)"
                                    class="w-5 h-5 sm:w-6 sm:h-6 rounded-md border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>{{ $v }}</span>
                            </label>

                            @if($v !== 'Tidak aktif')
                            <div x-show="isMuhammadiyahChecked('{{ $v }}')" x-cloak x-transition
                                 class="mt-3 pt-3 border-t border-sky-200/80">
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">
                                    Jabatan / Amanah di {{ $v }}:
                                </label>
                                <input type="text" name="jabatan_muhammadiyah[{{ $v }}]"
                                       value="{{ old('jabatan_muhammadiyah.'.$v) }}"
                                       placeholder="Contoh: Ketua / Sekretaris / Anggota Majelis"
                                       class="w-full px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[46px]">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 2. Pimpinan Cabang (PCM) & Pimpinan Ranting (PRM) Domisili --}}
                <div class="p-5 sm:p-6 rounded-2xl bg-slate-50 border-2 border-slate-200/90 space-y-4 shadow-2xs">
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-sky-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>2. Cabang &amp; Ranting Domisili / Basis Kader</span>
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                            Pilih Cabang (Kecamatan) tempat Bapak/Ibu berdomisili/aktif dan ketikkan nama Ranting (PRM) asal Anda:
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                                Pimpinan Cabang Muhammadiyah (PCM)
                            </label>
                            <select name="pcm" class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                                <option value="">-- Pilih Cabang / Kecamatan --</option>
                                @foreach(config('arqam.opsi_form.cabang_pcm') as $cabang)
                                    <option value="{{ $cabang }}" @selected(old('pcm') === $cabang)>PCM {{ $cabang }}</option>
                                @endforeach
                                <option value="Lainnya" @selected(old('pcm') === 'Lainnya')>Lainnya (Luar Karanganyar)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                                Pimpinan Ranting Muhammadiyah (PRM)
                            </label>
                            <input name="prm" value="{{ old('prm') }}" placeholder="Contoh: PRM Lalung / PRM Papahan"
                                class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                            <span class="text-xs text-slate-500 mt-1 block">Dapat berupa nama desa, kelurahan, atau dusun/masjid basis ranting.</span>
                        </div>
                    </div>
                </div>

                {{-- 3. Keaktifan di Organisasi Otonom (ORTOM) --}}
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm sm:text-base font-bold text-slate-900">
                            3. Keaktifan di Organisasi Otonom (ORTOM)
                        </label>
                        <span class="text-xs text-sky-800 font-semibold bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-200">Boleh pilih lebih dari 1</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500">Centang ortom tempat Bapak/Ibu berkiprah dan isikan jabatan/posisi Anda:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        @foreach(config('arqam.opsi_form.keaktifan_ortom') as $v)
                        <div class="p-4 sm:p-4.5 rounded-2xl border-2 transition-all shadow-2xs"
                             :class="isOrtomChecked('{{ $v }}') ? 'bg-sky-50/70 border-sky-500 ring-2 ring-sky-200' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/70'">
                            <label class="flex items-center gap-3 text-sm sm:text-base text-slate-900 font-bold cursor-pointer select-none">
                                <input type="checkbox" name="keaktifan_ortom[]" value="{{ $v }}"
                                    @checked(in_array($v, old('keaktifan_ortom', [])))
                                    @change="toggleOrtom('{{ $v }}', $el.checked)"
                                    class="w-5 h-5 sm:w-6 sm:h-6 rounded-md border-2 border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>{{ $v }}</span>
                            </label>

                            @if($v !== 'Tidak aktif')
                            <div x-show="isOrtomChecked('{{ $v }}')" x-cloak x-transition
                                 class="mt-3 pt-3 border-t border-sky-200/80">
                                <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">
                                    Jabatan / Posisi di {{ $v }}:
                                </label>
                                <input type="text" name="jabatan_ortom[{{ $v }}]"
                                       value="{{ old('jabatan_ortom.'.$v) }}"
                                       placeholder="Contoh: Ketua / Sekretaris / Anggota Bidang"
                                       class="w-full px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-slate-300 rounded-xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[46px]">
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 4. NBM & Pekerjaan Utama --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 pt-1">
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Nomor Baku Muhammadiyah (NBM)</label>
                        <input name="nbm" value="{{ old('nbm') }}" placeholder="Contoh: 1234567 (Bila sudah ber-NBM)"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                        <span class="text-xs text-slate-500 mt-1 block">Kosongkan bila belum memiliki kartu NBM.</span>
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Pekerjaan / Profesi Utama</label>
                        <input name="pekerjaan" value="{{ old('pekerjaan') }}" placeholder="Contoh: Guru / PNS / Wiraswasta / Karyawan / Nakes"
                            class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                    </div>
                </div>

                {{-- 5. Organisasi Lain di Luar Muhammadiyah --}}
                <div>
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">Organisasi Lain di Luar Muhammadiyah (Opsional)</label>
                    <input name="organisasi_lain" value="{{ old('organisasi_lain') }}" placeholder="Contoh: PGRI, IDI, Karang Taruna dll. (kosongkan bila tidak ada)"
                        class="w-full px-4 py-3.5 sm:py-4 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                </div>

                <div class="flex items-center justify-between gap-4 pt-5 border-t border-slate-200">
                    <button type="button" @click="goTo('agama')"
                        class="min-h-[50px] sm:min-h-[54px] px-6 sm:px-7 border-2 border-slate-300 bg-white text-slate-700 font-bold rounded-2xl hover:bg-slate-50 transition flex items-center justify-center gap-2 text-sm sm:text-base shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="goTo('kemasyarakatan')"
                        class="min-h-[50px] sm:min-h-[54px] px-7 sm:px-9 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-extrabold text-sm sm:text-base rounded-2xl shadow-md transition flex items-center justify-center gap-2.5 whitespace-nowrap">
                        <span>Lanjut: Kemasyarakatan</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 4: Kemasjidan, Kemasyarakatan & Selesai (Ramah Usia 40+) --}}
            <div x-show="tab==='kemasyarakatan'" class="space-y-6" style="display: none;"
                 x-data="{ 
                     isTakmir: {{ \Illuminate\Support\Js::from(old('keaktifan_takmir', 'Tidak')) }},
                     takmirRole: {{ \Illuminate\Support\Js::from(old('jabatan_takmir', '')) }},
                     masjidText: {{ \Illuminate\Support\Js::from(old('aktifitas_jamaah_masjid', '')) }},
                     masyarakatText: {{ \Illuminate\Support\Js::from(old('keaktifan_kemasyarakatan', '')) }},
                     
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
                         this.$dispatch('change');
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
                         this.$dispatch('change');
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
                         this.$dispatch('change');
                     },
                     hasMasyarakat(item) {
                         return this.masyarakatText && this.masyarakatText.includes(item);
                     }
                 }"
                 @tab4-restore.window="if ($event.detail) {
                     if ($event.detail.isTakmir !== undefined) isTakmir = $event.detail.isTakmir;
                     if ($event.detail.takmirRole !== undefined) takmirRole = $event.detail.takmirRole;
                     if ($event.detail.masjidText !== undefined) masjidText = $event.detail.masjidText;
                     if ($event.detail.masyarakatText !== undefined) masyarakatText = $event.detail.masyarakatText;
                 }"
                 @tab4-clear.window="isTakmir = 'Tidak'; takmirRole = ''; masjidText = ''; masyarakatText = '';">
                
                {{-- Banner Ramah Informasi --}}
                <div class="p-4 sm:p-5 rounded-2xl bg-sky-50 border-2 border-sky-200 flex items-start gap-3.5 text-sky-950">
                    <div class="w-10 h-10 rounded-2xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-xs font-bold text-lg">
                        <span aria-hidden="true">🕌</span>
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
                                <span aria-hidden="true">👤</span>
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
                                <span aria-hidden="true">🕌</span>
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
                </div>

                {{-- Informasi Pembuatan Akun Otomatis (Sangat Ramah Kader Senior) --}}
                <div class="p-5 sm:p-6 rounded-2xl bg-amber-50/90 border-2 border-amber-300 text-amber-950 flex items-start gap-4 shadow-sm">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold text-2xl shadow-xs">
                        <span aria-hidden="true">🔑</span>
                    </div>
                    <div class="space-y-2 flex-1">
                        <h4 class="text-base sm:text-lg font-extrabold text-amber-950 tracking-tight">Akun Masuk Portal Dibuat Otomatis</h4>
                        <p class="text-xs sm:text-sm text-amber-900 leading-relaxed font-normal">
                            Bapak/Ibu <strong>tidak perlu membuat kata sandi baru</strong>. Setelah pendaftaran disetujui admin, akses masuk portal kader adalah:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div class="bg-white border-2 border-amber-200 rounded-xl p-3.5 shadow-2xs">
                                <span class="text-slate-500 block text-xs font-bold uppercase tracking-wider">Username:</span>
                                <strong class="text-slate-900 font-mono text-sm sm:text-base block mt-0.5">Nomor WhatsApp / HP</strong>
                                <span class="text-xs text-slate-500 block mt-0.5">Sesuai nomor yang diisikan di Tab 1</span>
                            </div>
                            <div class="bg-white border-2 border-amber-200 rounded-xl p-3.5 shadow-2xs">
                                <span class="text-slate-500 block text-xs font-bold uppercase tracking-wider">Kata Sandi (Password):</span>
                                <strong class="text-slate-900 text-sm sm:text-base block mt-0.5">Tanggal Lahir Anda</strong>
                                <span class="text-xs text-slate-500 block mt-0.5">8 Digit Angka: DDMMYYYY</span>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-amber-900/90 font-medium pt-1">
                            <span aria-hidden="true">💡</span> <em>Contoh: Jika tanggal lahir Bapak/Ibu <strong>17 Agustus 1985</strong>, kata sandi login adalah <strong>17081985</strong>.</em>
                        </p>
                    </div>
                </div>

                {{-- Kotak Konfirmasi Pengiriman --}}

                <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border-2 border-emerald-300 text-emerald-950 flex items-start gap-3.5">
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 font-black text-sm">
                        ✓
                    </div>
                    <div class="leading-relaxed">
                        <span class="font-extrabold block text-emerald-900 text-sm sm:text-base mb-1">Alhamdulillah, Seluruh Data Formulir Telah Lengkap!</span>
                        <p class="text-xs sm:text-sm text-emerald-800">Silakan periksa kembali sebelum mengirim. Setelah menekan tombol kirim di bawah, pendaftaran Bapak/Ibu akan diverifikasi oleh tim sekretariat MPKSDI PDM Karanganyar.</p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between gap-3 pt-5 border-t-2 border-slate-200">
                    <button type="button" @click="goTo('org')"
                        class="min-h-[52px] px-6 border-2 border-slate-300 text-slate-700 font-extrabold rounded-2xl hover:bg-slate-100 active:bg-slate-200 transition flex items-center justify-center gap-2 text-sm sm:text-base shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="submit" :disabled="isSubmitting"
                        class="min-h-[54px] sm:min-h-[58px] px-8 sm:px-10 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-slate-400 disabled:cursor-not-allowed text-white font-black text-base sm:text-lg rounded-2xl shadow-md transition flex items-center justify-center gap-2.5 whitespace-nowrap">
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2.5">
                                <svg class="w-5 h-5 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Mengirim Pendaftaran...</span>
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-2">
                                <span>Kirim Pendaftaran</span>
                                <span class="text-xl leading-none">&rarr;</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </form>
        @endif

        {{-- Unified Bottom Nav & Institutional Footer (Mobile-First) --}}
        <div class="mt-6 rounded-xl bg-white border border-slate-200/90 shadow-2xs p-4 sm:p-5 space-y-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="text-left">
                    <p class="text-xs font-semibold text-slate-800 leading-tight">Sudah memiliki akun kader?</p>
                    <p class="text-xs text-slate-500 font-normal mt-0.5">Masuk untuk melihat status atau profil.</p>
                </div>
                <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 hover:text-sky-800 border border-sky-200/80 text-xs font-semibold transition whitespace-nowrap shadow-2xs flex items-center gap-1.5 shrink-0">
                    <span>Masuk Portal</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs text-slate-400">
                <a href="/" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-800 transition font-normal">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Beranda Utama</span>
                </a>

                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>MPKSDI Karanganyar</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function registrationForm() {
    return {
        tab: '{{ $errors->hasAny(['nama_lengkap', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'status_pernikahan', 'alamat_rumah', 'desa_kelurahan', 'kecamatan', 'kabupaten', 'no_wa', 'email', 'password', 'password_confirmation', 'foto']) ? 'diri' : ($errors->hasAny(['pendidikan_sd', 'bahasa_dikuasai', 'kemampuan_baca_quran', 'hafalan_quran', 'aktivitas_sholat_masjid', 'aktivitas_kajian_agama', 'jumlah_buku_agama', 'langganan_suara_muhammadiyah', 'lembaga_zis']) ? 'agama' : ($errors->hasAny(['nbm', 'jabatan_aum', 'pekerjaan', 'pcm', 'prm', 'keaktifan_muhammadiyah', 'jabatan_muhammadiyah', 'keaktifan_ortom', 'jabatan_ortom', 'organisasi_lain']) ? 'org' : ($errors->any() ? 'kemasyarakatan' : 'diri'))) }}',
        showPass: false,
        showConfirmPass: false,
        isSubmitting: false,
        clientErrors: {},
        stepErrorBanner: null,

        // Reactive selections for Muhammadiyah & Ortom
        checkedMuhammadiyah: {{ \Illuminate\Support\Js::from(old('keaktifan_muhammadiyah', [])) }},
        checkedOrtom: {{ \Illuminate\Support\Js::from(old('keaktifan_ortom', [])) }},

        // Dropdown bertingkat wilayah Karanganyar
        wilayahData: @js(config('arqam.opsi_form.wilayah_karanganyar')),
        selectedKecamatan: {{ \Illuminate\Support\Js::from(old('kecamatan', '')) }},
        selectedDesa: {{ \Illuminate\Support\Js::from(old('desa_kelurahan', '')) }},
        get desaList() {
            return this.selectedKecamatan && this.wilayahData[this.selectedKecamatan]
                ? this.wilayahData[this.selectedKecamatan] : [];
        },
        onKecamatanChange() {
            this.selectedDesa = '';
        },

        toggleMuhammadiyah(val, isChecked) {
            if (val === 'Tidak aktif') {
                this.checkedMuhammadiyah = isChecked ? ['Tidak aktif'] : [];
                const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
                if (form) {
                    const cbs = form.querySelectorAll('input[name="keaktifan_muhammadiyah[]"]');
                    cbs.forEach(cb => {
                        if (cb.value !== 'Tidak aktif') cb.checked = false;
                    });
                }
                this.saveDraft();
                return;
            }
            if (isChecked) {
                this.checkedMuhammadiyah = this.checkedMuhammadiyah.filter(x => x !== 'Tidak aktif');
                if (!this.checkedMuhammadiyah.includes(val)) this.checkedMuhammadiyah.push(val);
                const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
                if (form) {
                    const tidakAktifCb = form.querySelector('input[name="keaktifan_muhammadiyah[]"][value="Tidak aktif"]');
                    if (tidakAktifCb) tidakAktifCb.checked = false;
                }
            } else {
                this.checkedMuhammadiyah = this.checkedMuhammadiyah.filter(x => x !== val);
            }
            this.saveDraft();
        },

        isMuhammadiyahChecked(val) {
            return Array.isArray(this.checkedMuhammadiyah) && this.checkedMuhammadiyah.includes(val);
        },

        toggleOrtom(val, isChecked) {
            if (val === 'Tidak aktif') {
                this.checkedOrtom = isChecked ? ['Tidak aktif'] : [];
                const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
                if (form) {
                    const cbs = form.querySelectorAll('input[name="keaktifan_ortom[]"]');
                    cbs.forEach(cb => {
                        if (cb.value !== 'Tidak aktif') cb.checked = false;
                    });
                }
                this.saveDraft();
                return;
            }
            if (isChecked) {
                this.checkedOrtom = this.checkedOrtom.filter(x => x !== 'Tidak aktif');
                if (!this.checkedOrtom.includes(val)) this.checkedOrtom.push(val);
                const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
                if (form) {
                    const tidakAktifCb = form.querySelector('input[name="keaktifan_ortom[]"][value="Tidak aktif"]');
                    if (tidakAktifCb) tidakAktifCb.checked = false;
                }
            } else {
                this.checkedOrtom = this.checkedOrtom.filter(x => x !== val);
            }
            this.saveDraft();
        },

        isOrtomChecked(val) {
            return Array.isArray(this.checkedOrtom) && this.checkedOrtom.includes(val);
        },

        // Form persistence state
        draftKey: 'mpksdi_register_draft_v1',
        draftRestored: false,
        hasSavedDraft: false,
        lastSavedText: null,
        isRestoring: false,

        init() {
            @if(session('success'))
                this.clearDraft(false);
                return;
            @endif

            try {
                const raw = localStorage.getItem(this.draftKey);
                if (raw) {
                    const draft = JSON.parse(raw);
                    if (draft && typeof draft === 'object') {
                        this.hasSavedDraft = true;
                    }
                }
            } catch (e) {}

            @if(!$errors->any())
                this.$nextTick(() => {
                    this.restoreDraft();
                });
            @endif

            // Senior-friendly auto-scroll when requested via hash or mobile
            this.$nextTick(() => {
                if (window.location.hash === '#form-pendaftaran' || window.location.hash === '#daftar') {
                    const formEl = document.getElementById('form-pendaftaran');
                    if (formEl) {
                        formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        },

        clearError(field) {
            if (this.clientErrors && this.clientErrors[field]) {
                delete this.clientErrors[field];
            }
            if (this.clientErrors && Object.keys(this.clientErrors).length === 0) {
                this.stepErrorBanner = null;
            }
        },

        goTo(newTab, validateCurrent = true) {
            if (validateCurrent && this.tab === 'diri' && newTab !== 'diri') {
                if (!this.validateStepDiri()) {
                    return;
                }
            }
            this.tab = newTab;
            this.stepErrorBanner = null;
            this.saveDraft();
            window.scrollTo({ top: 120, behavior: 'smooth' });
        },

        serializeFormData(form) {
            const data = {};
            const elements = form.querySelectorAll('input, select, textarea');
            elements.forEach(el => {
                const name = el.name;
                if (!name) return;
                // SECURITY: Never save password fields or CSRF token in browser storage!
                if (name === 'password' || name === 'password_confirmation' || name === '_token') return;
                if (el.type === 'file') return;

                if (el.type === 'checkbox') {
                    if (name.endsWith('[]')) {
                        if (!data[name]) data[name] = [];
                        if (el.checked) {
                            data[name].push(el.value);
                        }
                    } else {
                        data[name] = el.checked;
                    }
                } else if (el.type === 'radio') {
                    if (el.checked) {
                        data[name] = el.value;
                    }
                } else {
                    data[name] = el.value;
                }
            });

            data._active_tab = this.tab;

            const pasfotoImg = form.querySelector('img[alt="Preview Pasfoto"]');
            if (pasfotoImg && pasfotoImg.src && pasfotoImg.src.startsWith('data:image')) {
                data._pasfoto_data_url = pasfotoImg.src;
            }

            return data;
        },

        saveDraft() {
            if (this.isRestoring) return;
            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
            if (!form) return;

            try {
                const draft = this.serializeFormData(form);
                const hasContent = Object.keys(draft).some(k => {
                    if (k.startsWith('_')) return false;
                    const val = draft[k];
                    if (Array.isArray(val)) return val.length > 0;
                    return val !== '' && val !== null && val !== undefined;
                });

                if (!hasContent && !draft._pasfoto_data_url) {
                    return;
                }

                draft._saved_at = Date.now();
                localStorage.setItem(this.draftKey, JSON.stringify(draft));
                this.hasSavedDraft = true;
                this.lastSavedText = 'Draf tersimpan';
            } catch (e) {
                // Storage quota fallback: try saving without pasfoto data URL
                try {
                    const draft = this.serializeFormData(form);
                    delete draft._pasfoto_data_url;
                    draft._saved_at = Date.now();
                    localStorage.setItem(this.draftKey, JSON.stringify(draft));
                    this.hasSavedDraft = true;
                    this.lastSavedText = 'Draf teks tersimpan';
                } catch (innerErr) {
                    console.warn('localStorage save failed:', innerErr);
                }
            }
        },

        restoreDraft() {
            try {
                const raw = localStorage.getItem(this.draftKey);
                if (!raw) return;
                const draft = JSON.parse(raw);
                if (!draft || typeof draft !== 'object') return;

                const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
                if (!form) return;

                this.isRestoring = true;
                let hasRestoredAny = false;

                Object.keys(draft).forEach(key => {
                    if (key.startsWith('_')) return;

                    if (key.endsWith('[]')) {
                        const values = Array.isArray(draft[key]) ? draft[key] : [];
                        const checkboxes = form.querySelectorAll(`input[name="${key}"]`);
                        if (checkboxes.length > 0) {
                            checkboxes.forEach(cb => {
                                if (values.includes(cb.value)) {
                                    cb.checked = true;
                                    hasRestoredAny = true;
                                } else {
                                    cb.checked = false;
                                }
                            });
                        }
                    } else {
                        const el = form.querySelector(`[name="${key}"]`);
                        if (!el) return;

                        if (el.type === 'radio') {
                            const radios = form.querySelectorAll(`input[name="${key}"]`);
                            radios.forEach(r => {
                                if (r.value === draft[key]) {
                                    r.checked = true;
                                    r.dispatchEvent(new Event('change', { bubbles: true }));
                                    hasRestoredAny = true;
                                }
                            });
                        } else if (el.type === 'checkbox') {
                            el.checked = !!draft[key];
                            hasRestoredAny = true;
                        } else {
                            if (draft[key] !== undefined && draft[key] !== null) {
                                el.value = draft[key];
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                                if (draft[key] !== '') {
                                    hasRestoredAny = true;
                                }
                            }
                        }
                    }
                });

                // Restore Tab 4 specific reactive state
                window.dispatchEvent(new CustomEvent('tab4-restore', {
                    detail: {
                        isTakmir: draft.keaktifan_takmir || 'Tidak',
                        takmirRole: draft.jabatan_takmir || '',
                        masjidText: draft.aktifitas_jamaah_masjid || '',
                        masyarakatText: draft.keaktifan_kemasyarakatan || ''
                    }
                }));

                // Restore reactive checkbox arrays for Tab 3
                if (draft['keaktifan_muhammadiyah[]']) {
                    this.checkedMuhammadiyah = Array.isArray(draft['keaktifan_muhammadiyah[]']) ? draft['keaktifan_muhammadiyah[]'] : [];
                }
                if (draft['keaktifan_ortom[]']) {
                    this.checkedOrtom = Array.isArray(draft['keaktifan_ortom[]']) ? draft['keaktifan_ortom[]'] : [];
                }

                // Restore pasfoto if present
                if (draft._pasfoto_data_url) {
                    window.dispatchEvent(new CustomEvent('pasfoto-restore', {
                        detail: {
                            inputId: 'foto-register-input',
                            dataUrl: draft._pasfoto_data_url
                        }
                    }));
                    hasRestoredAny = true;
                }

                // Restore active tab
                if (draft._active_tab && ['diri', 'agama', 'org', 'kemasyarakatan'].includes(draft._active_tab)) {
                    this.tab = draft._active_tab;
                }

                this.isRestoring = false;

                if (hasRestoredAny) {
                    this.draftRestored = true;
                    this.hasSavedDraft = true;
                    this.lastSavedText = 'Draf dipulihkan';
                }
            } catch (e) {
                this.isRestoring = false;
                console.warn('Gagal memulihkan draf formulir:', e);
            }
        },

        clearDraft(confirmPrompt = true) {
            if (confirmPrompt) {
                if (!confirm('Apakah Anda yakin ingin menghapus draf dan mengosongkan seluruh formulir pendaftaran?')) {
                    return;
                }
            }

            try {
                localStorage.removeItem(this.draftKey);
            } catch (e) {}

            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
            if (form) {
                form.reset();
            }

            this.tab = 'diri';
            this.checkedMuhammadiyah = [];
            this.checkedOrtom = [];
            this.draftRestored = false;
            this.hasSavedDraft = false;
            this.lastSavedText = null;
            this.clientErrors = {};
            this.stepErrorBanner = null;

            window.dispatchEvent(new CustomEvent('tab4-clear'));
            window.dispatchEvent(new CustomEvent('pasfoto-clear', {
                detail: { inputId: 'foto-register-input' }
            }));

            window.scrollTo({ top: 120, behavior: 'smooth' });
        },

        validateStepDiri() {
            this.clientErrors = {};
            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');
            if (!form) return true;

            const getVal = (name) => {
                const el = form.querySelector(`[name="${name}"]`);
                return el ? el.value.trim() : '';
            };

            if (!getVal('nama_lengkap')) {
                this.clientErrors.nama_lengkap = 'Nama lengkap beserta gelar wajib diisi.';
            }

            const nik = getVal('nik');
            if (!nik) {
                this.clientErrors.nik = 'NIK KTP wajib diisi.';
            } else if (!/^\d{16}$/.test(nik)) {
                this.clientErrors.nik = 'NIK KTP harus tepat 16 digit angka.';
            }

            if (!getVal('jenis_kelamin')) {
                this.clientErrors.jenis_kelamin = 'Silakan pilih jenis kelamin.';
            }

            if (!getVal('tempat_lahir')) {
                this.clientErrors.tempat_lahir = 'Tempat lahir wajib diisi.';
            }

            if (!getVal('tanggal_lahir')) {
                this.clientErrors.tanggal_lahir = 'Tanggal lahir wajib diisi.';
            }

            if (!getVal('status_pernikahan')) {
                this.clientErrors.status_pernikahan = 'Silakan pilih status pernikahan.';
            }

            if (!getVal('alamat_rumah')) {
                this.clientErrors.alamat_rumah = 'Alamat lengkap rumah wajib diisi.';
            }

            if (!getVal('desa_kelurahan')) {
                this.clientErrors.desa_kelurahan = 'Desa / Kelurahan wajib dipilih.';
            }

            if (!getVal('kecamatan')) {
                this.clientErrors.kecamatan = 'Kecamatan wajib dipilih.';
            }

            const rawWa = getVal('no_wa');
            const noWa = rawWa.replace(/[\s\-\.]/g, '');
            if (!noWa) {
                this.clientErrors.no_wa = 'Nomor WhatsApp aktif wajib diisi.';
            } else if (!/^(0|62|\+62)8[0-9]{6,13}$/.test(noWa)) {
                this.clientErrors.no_wa = 'Format nomor WhatsApp tidak valid (contoh: 081234567890).';
            }

            const fotoInput = form.querySelector('input[name="foto"]');
            if (!fotoInput || !fotoInput.files || fotoInput.files.length === 0) {
                this.clientErrors.foto = 'Pasfoto 3:4 wajib diunggah untuk kelengkapan administrasi.';
            }

            const pass = getVal('password');
            const confirmPass = getVal('password_confirmation');
            if (pass) {
                if (pass.length < 6) {
                    this.clientErrors.password = 'Kata sandi minimal 6 karakter.';
                }
                if (!confirmPass) {
                    this.clientErrors.password_confirmation = 'Konfirmasi kata sandi wajib diisi.';
                } else if (pass !== confirmPass) {
                    this.clientErrors.password_confirmation = 'Konfirmasi kata sandi tidak cocok.';
                }
            }

            const errorKeys = Object.keys(this.clientErrors);
            if (errorKeys.length > 0) {
                this.stepErrorBanner = 'Mohon lengkapi isian wajib pada Data Identitas sebelum melanjutkan.';
                this.$nextTick(() => {
                    const firstErrKey = errorKeys[0];
                    const firstEl = form.querySelector(`[name="${firstErrKey}"]`);
                    if (firstEl) {
                        firstEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstEl.focus();
                    } else {
                        window.scrollTo({ top: 120, behavior: 'smooth' });
                    }
                });
                return false;
            }

            return true;
        },

        submitForm(e) {
            if (this.isSubmitting) return;

            const form = this.$el.tagName === 'FORM' ? this.$el : this.$el.closest('form');

            if (!this.validateStepDiri()) {
                this.tab = 'diri';
                this.stepErrorBanner = 'Pendaftaran belum dapat dikirim. Mohon lengkapi seluruh isian wajib pada Data Identitas.';
                window.scrollTo({ top: 120, behavior: 'smooth' });
                return;
            }

            this.isSubmitting = true;
            if (form) {
                form.submit();
            } else {
                this.$el.submit();
            }
        }
    };
}
</script>
@endsection
