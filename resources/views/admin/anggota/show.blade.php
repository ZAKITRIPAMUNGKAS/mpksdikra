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
                        class="w-16 h-20 sm:w-20 sm:h-24 object-cover rounded-xl border border-slate-200 shadow-2xs shrink-0 bg-slate-100">
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
            <div class="flex gap-2 flex-wrap items-center" x-data="{ showRejectModal: false }">
                @if($anggota->user->status !== 'aktif')
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
                <form method="POST" action="{{ route('admin.anggota.deactivate', $anggota) }}"
                    onsubmit="return confirm('Nonaktifkan anggota ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-red-200 text-red-700 text-xs font-semibold rounded-lg bg-white hover:bg-red-50 transition shadow-2xs">
                        Nonaktifkan
                    </button>
                </form>
                @endif

                {{-- Reset Password Button --}}
                <form method="POST" action="{{ route('admin.anggota.reset-password', $anggota) }}"
                    onsubmit="return confirm('Reset kata sandi anggota ini ke sandi bawaan (6 digit NIK terakhir)?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1.5" title="Reset Kata Sandi Akun">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Reset Sandi</span>
                    </button>
                </form>
            </div>
        </div>

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
        <div class="mb-6 p-4 rounded-lg bg-emerald-50/70 border border-emerald-200/80 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                    💬
                </div>
                <div>
                    <span class="text-xs font-semibold text-emerald-950 block">Komunikasi WhatsApp Kader</span>
                    <span class="text-xs text-emerald-800 font-mono">{{ $anggota->no_wa }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, "Assalamu'alaikum wr. wb. Bapak/Ibu {$anggota->nama_lengkap}...") }}"
                   target="_blank" rel="noopener noreferrer"
                   class="px-3.5 py-1.5 bg-white hover:bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <span>Buka Chat WA</span>
                </a>
                @if($anggota->user->status === 'aktif')
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, \App\Services\WhatsAppNotificationService::pesanVerifikasiSukses($anggota)) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <span>Kirim Notifikasi Akun Aktif</span>
                </a>
                @elseif($anggota->user->status === 'ditolak')
                <a href="{{ \App\Services\WhatsAppNotificationService::makeUrl($anggota->no_wa, \App\Services\WhatsAppNotificationService::pesanPerbaikanDitolak($anggota)) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                    <span>Kirim Catatan Revisi</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        @if($anggota->catatan_verifikasi)
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-xs flex items-start gap-3 shadow-2xs">
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
        </div>
    </div>
</div>
@endsection
