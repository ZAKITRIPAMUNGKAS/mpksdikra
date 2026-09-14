<table>
    <thead>
        <tr>
            <th colspan="43" style="font-size: 14pt; font-weight: bold; text-align: center; color: #002b40;">
                {{ $subTitle }}
            </th>
        </tr>
        <tr>
            <th colspan="43" style="font-size: 12pt; font-weight: bold; text-align: center; color: #005c8a;">
                MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)
            </th>
        </tr>
        <tr>
            <th colspan="43" style="font-size: 11pt; font-weight: bold; text-align: center;">
                DATA LENGKAP KADER DAN ANGGOTA PERSYARIKATAN MUHAMMADIYAH
                @if($pcm)
                    - PCM {{ strtoupper($pcm) }}
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="43" style="font-size: 9pt; text-align: center; font-style: italic; color: #475569;">
                Diekspor pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </th>
        </tr>
        <tr>
            <th colspan="43"></th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: left;">Total Kader</th>
            <th colspan="5" style="text-align: left;">: {{ $totalKader }} Orang</th>
            <th colspan="2" style="font-weight: bold; text-align: left;">Kader Aktif</th>
            <th colspan="5" style="text-align: left;">: {{ $totalAktif }} Orang</th>
            <th colspan="2" style="font-weight: bold; text-align: left;">Kader Menunggu</th>
            <th colspan="5" style="text-align: left;">: {{ $totalPending }} Orang</th>
            <th colspan="2" style="font-weight: bold; text-align: left;">Laki-laki</th>
            <th colspan="5" style="text-align: left;">: {{ $totalLaki }} Orang</th>
            <th colspan="2" style="font-weight: bold; text-align: left;">Perempuan</th>
            <th colspan="13" style="text-align: left;">: {{ $totalPerempuan }} Orang</th>
        </tr>
        <tr>
            <th colspan="43"></th>
        </tr>
        {{-- Baris Grup Header --}}
        <tr style="font-weight: bold; text-align: center; color: #ffffff;">
            <th colspan="3" style="background-color: #003853; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">ADMINISTRASI SISTEM</th>
            <th colspan="10" style="background-color: #005077; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">IDENTITAS &amp; KONTAK PRIBADI</th>
            <th colspan="6" style="background-color: #00689b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">DOMISILI &amp; WILAYAH</th>
            <th colspan="3" style="background-color: #0b78af; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">PROFESI &amp; PENDIDIKAN</th>
            <th colspan="11" style="background-color: #0284c7; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">PROFIL KEAGAMAAN &amp; LITERASI</th>
            <th colspan="3" style="background-color: #0369a1; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">KEMUHAMMADIYAHAN &amp; ORTOM</th>
            <th colspan="6" style="background-color: #075985; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">KEMASJIDAN, SOSIAL &amp; HARAPAN</th>
            <th colspan="1" style="background-color: #0c4a6e; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #002233;">CATATAN</th>
        </tr>
        {{-- Baris Header Kolom Rinci --}}
        <tr style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">No</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Status Akun</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Tgl Registrasi</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">NIK</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">NBM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Nama Lengkap</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">L/P</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Tempat Lahir</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Tanggal Lahir</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Usia</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Status Pernikahan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">No. WhatsApp / HP</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Email</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Alamat Lengkap</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Desa / Kelurahan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Kecamatan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Cabang (PCM)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Ranting (PRM)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Kabupaten</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Pekerjaan / Profesi</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Jabatan di AUM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Pendidikan SD / Sederajat</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Bahasa yang Dikuasai</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Kemampuan Baca Al-Qur'an</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Hafalan Al-Qur'an</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Aktivitas Shalat di Masjid</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Aktivitas Kajian Agama</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Jumlah Kepemilikan Buku Agama</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Sumber Info Kemuhammadiyahan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Langganan Suara Muhammadiyah</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Lembaga Penyaluran ZIS</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Tokoh Berpengaruh / Inspiratif</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Alasan Memilih Tokoh</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Keaktifan Pimpinan Muhammadiyah &amp; Jabatan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Keaktifan di ORTOM &amp; Jabatan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Organisasi di Luar Muhammadiyah</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Aktivitas Jamaah di Masjid</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #001f30;">Keaktifan Takmir Masjid</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Jabatan di Takmir Masjid</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Keaktifan Kemasyarakatan (RT/RW/dll)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Harapan untuk PCM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Harapan untuk Baitul Arqam</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left; border: 1px solid #001f30;">Catatan Verifikasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($anggotas as $idx => $a)
        @php
            // Format Bahasa
            $bahasa = is_array($a->bahasa_dikuasai) ? implode(', ', $a->bahasa_dikuasai) : ($a->bahasa_dikuasai ?? '-');

            // Format Sumber Info
            $sumber = is_array($a->sumber_info_muhammadiyah) ? implode(', ', $a->sumber_info_muhammadiyah) : ($a->sumber_info_muhammadiyah ?? '-');

            // Format ZIS
            $zis = is_array($a->lembaga_zis) ? implode(', ', $a->lembaga_zis) : ($a->lembaga_zis ?? '-');

            // Format Keaktifan Muhammadiyah + Jabatan
            $aktifMuh = [];
            if (is_array($a->keaktifan_muhammadiyah)) {
                foreach ($a->keaktifan_muhammadiyah as $item) {
                    $jab = $a->jabatan_muhammadiyah[$item] ?? null;
                    $aktifMuh[] = $jab ? "{$item} ({$jab})" : $item;
                }
            } elseif (!empty($a->keaktifan_muhammadiyah)) {
                $aktifMuh[] = $a->keaktifan_muhammadiyah;
            }

            // Format Keaktifan ORTOM + Jabatan
            $aktifOrtom = [];
            if (is_array($a->keaktifan_ortom)) {
                foreach ($a->keaktifan_ortom as $item) {
                    $jab = $a->jabatan_ortom[$item] ?? null;
                    $aktifOrtom[] = $jab ? "{$item} ({$jab})" : $item;
                }
            } elseif (!empty($a->keaktifan_ortom)) {
                $aktifOrtom[] = $a->keaktifan_ortom;
            }

            // Umur
            $usia = $a->umur ?? ($a->tanggal_lahir ? \Carbon\Carbon::parse($a->tanggal_lahir)->age : null);

            // Email (utamakan dari profil anggota, fallback ke user email bila bukan placeholder)
            $userEmail = $a->user?->email;
            $email = $a->email ?? (!empty($userEmail) && !str_contains($userEmail, '@placeholder.test') ? $userEmail : '-');
        @endphp
        <tr style="{{ $idx % 2 === 1 ? 'background-color: #f8fafc;' : '' }}">
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $idx + 1 }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #cbd5e1; {{ $a->user?->status === 'aktif' ? 'color: #047857;' : ($a->user?->status === 'menunggu_verifikasi' ? 'color: #b45309;' : 'color: #64748b;') }}">
                {{ strtoupper(str_replace('_', ' ', $a->user?->status ?? 'menunggu_verifikasi')) }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->created_at ? $a->created_at->format('d/m/Y H:i') : '-' }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">'{{ $a->nik }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">@if($a->nbm)'{{ $a->nbm }}@else-@endif</td>
            <td style="font-weight: bold; border: 1px solid #cbd5e1;">{{ $a->nama_lengkap }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : ($a->jenis_kelamin === 'P' ? 'Perempuan' : ($a->jenis_kelamin ?? '-')) }}
            </td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->tempat_lahir ?? '-' }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->tanggal_lahir ? \Carbon\Carbon::parse($a->tanggal_lahir)->format('d/m/Y') : '-' }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $usia !== null ? $usia . ' th' : '-' }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $a->status_pernikahan ?? '-' }}</td>
            <td style="text-align: left; border: 1px solid #cbd5e1;">@if($a->no_wa)'{{ $a->no_wa }}@else-@endif</td>
            <td style="text-align: left; border: 1px solid #cbd5e1;">{{ $email }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->alamat_rumah ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->desa_kelurahan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->kecamatan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->pcm ?? $a->kecamatan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->prm ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->kabupaten ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->pekerjaan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->jabatan_aum ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->pendidikan_sd ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $bahasa ?: '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->kemampuan_baca_quran ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->hafalan_quran ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->aktivitas_sholat_masjid ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->aktivitas_kajian_agama ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->jumlah_buku_agama ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $sumber ?: '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->langganan_suara_muhammadiyah ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $zis ?: '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->tokoh_berpengaruh ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->alasan_pilih_tokoh ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ count($aktifMuh) > 0 ? implode('; ', $aktifMuh) : '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ count($aktifOrtom) > 0 ? implode('; ', $aktifOrtom) : '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->organisasi_lain ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->aktifitas_jamaah_masjid ?? '-' }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $a->keaktifan_takmir ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->jabatan_takmir ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->keaktifan_kemasyarakatan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->harapan_pcm ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->harapan_ba ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->catatan_verifikasi ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="43" style="text-align: center; padding: 20px; font-style: italic; color: #94a3b8; border: 1px solid #cbd5e1;">
                Tidak ada data kader yang ditemukan.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
