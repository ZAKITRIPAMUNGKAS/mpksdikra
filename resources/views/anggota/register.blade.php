@extends('layouts.guest')

@section('title', 'Pendaftaran Kader — MPKSDI PDM Karanganyar')

@section('body')
<div class="min-h-screen py-8 sm:py-12 px-4 bg-slate-100 selection:bg-mpksdi-600 selection:text-white">
    <div class="max-w-3xl mx-auto">
        {{-- Top Navigation & Brand Bar --}}
        <div class="flex items-center justify-between mb-6">
            <a href="/" class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-mpksdi-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>
            <div class="text-xs text-slate-600">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="font-bold text-mpksdi-700 hover:underline">Masuk Portal</a>
            </div>
        </div>

        {{-- Form Header with MPKSDI Crest --}}
        <div class="bg-white rounded-2xl border border-slate-200 border-t-4 border-t-mpksdi-600 shadow-xs p-6 sm:p-8 mb-6">
            <div class="flex items-center gap-4">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" alt="Logo MPKSDI" class="h-14 sm:h-16 w-auto shrink-0">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-mpksdi-600 block mb-0.5">Pangkalan Data Kader Persyarikatan</span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Anggota MPKSDI</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi formulir pendaftaran anggota dan biodata diri secara akurat untuk verifikasi kepesertaan Baitul Arqam.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 px-4 py-3.5 rounded-xl bg-mpksdi-50 border-l-4 border-mpksdi-600 text-mpksdi-950 text-sm flex items-start gap-2.5 shadow-2xs">
                <svg class="w-5 h-5 text-mpksdi-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 px-4 py-3.5 rounded-xl bg-red-50 border-l-4 border-red-600 text-red-950 text-sm shadow-2xs">
                <div class="font-bold mb-1 flex items-center gap-1.5 text-xs uppercase tracking-wider">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mohon lengkapi atau perbaiki isian berikut:
                </div>
                <ul class="space-y-1 list-disc pl-5 text-xs text-red-800">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('anggota.register.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8"
              x-data="{ 
                  tab: 'diri',
                  photoPreview: null,
                  goTo(newTab) {
                      this.tab = newTab;
                      window.scrollTo({ top: 120, behavior: 'smooth' });
                  }
              }">
            @csrf

            {{-- Stepper Progress Header --}}
            <div class="mb-8 pb-5 border-b border-slate-200">
                <div class="grid grid-cols-4 gap-2">
                    @php
                        $steps = [
                            ['key' => 'diri', 'no' => '1', 'title' => 'Identitas'],
                            ['key' => 'agama', 'no' => '2', 'title' => 'Keagamaan'],
                            ['key' => 'org', 'no' => '3', 'title' => 'Organisasi'],
                            ['key' => 'harapan', 'no' => '4', 'title' => 'Harapan'],
                        ];
                    @endphp
                    @foreach($steps as $s)
                    <button type="button" @click="goTo('{{ $s['key'] }}')"
                        class="text-left group focus:outline-none transition">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="w-7 h-7 rounded-lg text-xs font-bold flex items-center justify-center transition-all duration-150"
                                :class="tab==='{{ $s['key'] }}' ? 'bg-mpksdi-600 text-white font-extrabold shadow-2xs' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'">
                                {{ $s['no'] }}
                            </span>
                            <span class="text-xs font-bold hidden sm:inline"
                                :class="tab==='{{ $s['key'] }}' ? 'text-mpksdi-900' : 'text-slate-500 group-hover:text-slate-800'">
                                {{ $s['title'] }}
                            </span>
                        </div>
                        <div class="w-full h-1.5 rounded-full overflow-hidden bg-slate-100">
                            <div class="h-full transition-all duration-200"
                                :class="tab==='{{ $s['key'] }}' ? 'bg-gold-400 w-full' : 'bg-transparent w-0'"></div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Tab 1: Data Diri & Akun --}}
            <div x-show="tab==='diri'" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap (beserta gelar) <span class="text-red-500">*</span>
                    </label>
                    <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: H. Agus Sulaiman, S.Pd"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            NIK KTP (16 digit) <span class="text-red-500">*</span>
                        </label>
                        <input name="nik" value="{{ old('nik') }}" maxlength="16" pattern="\d{16}" required placeholder="3313xxxxxxxxxxxx"
                            class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select name="jenis_kelamin" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih Jenis Kelamin</option>
                            @foreach(config('arqam.opsi_form.jenis_kelamin') as $val => $label)
                            <option value="{{ $val }}" @selected(old('jenis_kelamin')===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tempat Lahir <span class="text-red-500">*</span>
                        </label>
                        <input name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Karanganyar"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Status Pernikahan <span class="text-red-500">*</span>
                        </label>
                        <select name="status_pernikahan" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.status_pernikahan') as $v)
                            <option value="{{ $v }}" @selected(old('status_pernikahan')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Lengkap Rumah <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alamat_rumah" required rows="2" placeholder="Nama jalan, RT/RW, dukuh/perumahan"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white resize-none">{{ old('alamat_rumah') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Desa/Kelurahan <span class="text-red-500">*</span></label>
                        <input name="desa_kelurahan" value="{{ old('desa_kelurahan') }}" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kecamatan (Cabang) <span class="text-red-500">*</span></label>
                        <input name="kecamatan" value="{{ old('kecamatan') }}" required placeholder="Contoh: Karanganyar / Jaten"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kabupaten <span class="text-red-500">*</span></label>
                        <input name="kabupaten" value="{{ old('kabupaten', 'Karanganyar') }}" required
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor WhatsApp Aktif <span class="text-red-500">*</span>
                        </label>
                        <input name="no_wa" value="{{ old('no_wa') }}" placeholder="08xxxxxxxxxx" required
                            class="w-full px-3.5 py-2.5 text-sm font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Email (Login Portal)
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="kader@email.com"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                </div>

                {{-- Pasfoto Upload Component with Instant Preview --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Pasfoto Formal (Wajib untuk ID Card BA) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="w-18 h-24 border-2 border-dashed border-slate-300 rounded-lg overflow-hidden bg-white flex items-center justify-center shrink-0 shadow-2xs">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <span class="text-[10px] text-slate-400 text-center font-bold px-1">Foto 3x4</span>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="foto" accept="image/*" required
                                @change="
                                    const file = $event.target.files[0];
                                    if (file) {
                                        const reader = new FileReader();
                                        reader.onload = (e) => { photoPreview = e.target.result; };
                                        reader.readAsDataURL(file);
                                    }
                                "
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-mpksdi-50 file:text-mpksdi-800 hover:file:bg-mpksdi-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Maksimal 2 MB. Format JPG atau PNG. Gunakan foto resmi berlatar belakang polos.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Akun <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Konfirmasi Sandi <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="8" placeholder="Ketik ulang sandi"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="button" @click="goTo('agama')"
                        class="px-6 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs sm:text-sm rounded-lg shadow-xs transition flex items-center gap-2">
                        <span>Lanjut: Data Keagamaan</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 2: Keagamaan --}}
            <div x-show="tab==='agama'" class="space-y-5" style="display: none;">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pendidikan SD / Sederajat</label>
                    <input name="pendidikan_sd" value="{{ old('pendidikan_sd') }}" placeholder="Contoh: MIM Karanganyar / SDN 1 Karanganyar"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Bahasa yang Dikuasai</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach(config('arqam.opsi_form.bahasa_dikuasai') as $v)
                        <label class="flex items-center gap-2 text-xs text-slate-700 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="bahasa_dikuasai[]" value="{{ $v }}"
                                @checked(in_array($v, old('bahasa_dikuasai', []))) class="rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-600">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kemampuan Baca Al-Qur'an</label>
                        <select name="kemampuan_baca_quran" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.kemampuan_baca_quran') as $v)
                            <option value="{{ $v }}" @selected(old('kemampuan_baca_quran')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Hafalan Al-Qur'an</label>
                        <select name="hafalan_quran" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.hafalan_quran') as $v)
                            <option value="{{ $v }}" @selected(old('hafalan_quran')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Aktivitas Sholat Berjamaah di Masjid</label>
                        <select name="aktivitas_sholat_masjid" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.aktivitas_sholat_masjid') as $v)
                            <option value="{{ $v }}" @selected(old('aktivitas_sholat_masjid')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Aktivitas Mengikuti Kajian</label>
                        <select name="aktivitas_kajian_agama" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.aktivitas_kajian_agama') as $v)
                            <option value="{{ $v }}" @selected(old('aktivitas_kajian_agama')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Koleksi Buku Agama</label>
                        <select name="jumlah_buku_agama" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.jumlah_buku_agama') as $v)
                            <option value="{{ $v }}" @selected(old('jumlah_buku_agama')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Langganan Majalah Suara Muhammadiyah</label>
                        <select name="langganan_suara_muhammadiyah" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                            <option value="">Pilih</option>
                            @foreach(config('arqam.opsi_form.langganan_suara_muhammadiyah') as $v)
                            <option value="{{ $v }}" @selected(old('langganan_suara_muhammadiyah')===$v)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Penyaluran ZIS (Zakat, Infaq, Shodaqoh)</label>
                    <div class="flex gap-4 flex-wrap">
                        @foreach(config('arqam.opsi_form.lembaga_zis') as $v)
                        <label class="flex items-center gap-2 text-xs text-slate-700 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="lembaga_zis[]" value="{{ $v }}"
                                @checked(in_array($v, old('lembaga_zis', []))) class="rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-600">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between pt-4 border-t border-slate-100">
                    <button type="button" @click="goTo('diri')"
                        class="px-5 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="goTo('org')"
                        class="px-6 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs sm:text-sm rounded-lg shadow-xs transition flex items-center gap-2">
                        <span>Lanjut: Organisasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 3: Organisasi & Kemuhammadiyahan --}}
            <div x-show="tab==='org'" class="space-y-5" style="display: none;">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Keaktifan di Pimpinan Muhammadiyah</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(config('arqam.opsi_form.keaktifan_muhammadiyah') as $v)
                        <label class="flex items-center gap-2 text-xs text-slate-700 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="keaktifan_muhammadiyah[]" value="{{ $v }}"
                                @checked(in_array($v, old('keaktifan_muhammadiyah', []))) class="rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-600">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Keaktifan di Organisasi Otonom (ORTOM)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach(config('arqam.opsi_form.keaktifan_ortom') as $v)
                        <label class="flex items-center gap-2 text-xs text-slate-700 p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="keaktifan_ortom[]" value="{{ $v }}"
                                @checked(in_array($v, old('keaktifan_ortom', []))) class="rounded border-slate-300 text-mpksdi-600 focus:ring-mpksdi-600">
                            <span>{{ $v }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nomor Baku Muhammadiyah (NBM)</label>
                        <input name="nbm" value="{{ old('nbm') }}" placeholder="Bila sudah memiliki NBM"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Amanah / Jabatan di AUM</label>
                        <input name="jabatan_aum" value="{{ old('jabatan_aum') }}" placeholder="Contoh: Guru SMA Muh 1 / Perawat PKU"
                            class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Organisasi Lain di Luar Muhammadiyah</label>
                    <input name="organisasi_lain" value="{{ old('organisasi_lain') }}" placeholder="Sebutkan jika ada (opsional)"
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white">
                </div>

                <div class="flex justify-between pt-4 border-t border-slate-100">
                    <button type="button" @click="goTo('agama')"
                        class="px-5 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="button" @click="goTo('harapan')"
                        class="px-6 py-2.5 bg-mpksdi-600 hover:bg-mpksdi-700 text-white font-bold text-xs sm:text-sm rounded-lg shadow-xs transition flex items-center gap-2">
                        <span>Lanjut: Harapan &amp; Pengiriman</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            {{-- Tab 4: Harapan & Selesai --}}
            <div x-show="tab==='harapan'" class="space-y-5" style="display: none;">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Harapan terhadap Pimpinan Cabang Muhammadiyah (PCM)
                    </label>
                    <textarea name="harapan_pcm" rows="3" placeholder="Tuliskan masukan atau harapan untuk kemajuan persyarikatan di tingkat cabang..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white resize-none">{{ old('harapan_pcm') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Harapan selama Mengikuti Baitul Arqam (BA)
                    </label>
                    <textarea name="harapan_ba" rows="3" placeholder="Tuliskan target pengembangan diri atau pemahaman yang ingin dicapai..."
                        class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-mpksdi-500 focus:border-mpksdi-500 outline-none bg-white resize-none">{{ old('harapan_ba') }}</textarea>
                </div>

                <div class="p-4 rounded-xl bg-mpksdi-50 border border-mpksdi-200 text-xs text-mpksdi-950">
                    <span class="font-bold block mb-1">Konfirmasi Pengiriman:</span>
                    Pastikan seluruh data yang Anda masukkan telah benar dan pasfoto yang diunggah jelas. Setelah dikirim, admin MPKSDI PDM Karanganyar akan memverifikasi berkas Anda sebelum akun diaktifkan.
                </div>

                <div class="flex justify-between pt-4 border-t border-slate-100 items-center">
                    <button type="button" @click="goTo('org')"
                        class="px-5 py-2.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-50 transition flex items-center gap-1.5 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>Kembali</span>
                    </button>
                    <button type="submit"
                        class="px-8 py-3 bg-gold-400 hover:bg-gold-500 text-mpksdi-950 font-extrabold text-sm rounded-xl border border-gold-500 shadow-md transition">
                        Kirim Pendaftaran Kader
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
