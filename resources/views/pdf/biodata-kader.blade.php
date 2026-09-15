<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Curriculum Vitae / Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #ffffff;
            position: relative;
        }

        /* Background Watermark */
        .watermark-container {
            position: fixed;
            top: 28%;
            left: 16%;
            width: 440px;
            height: 440px;
            z-index: -1000;
            text-align: center;
        }
        .watermark-img {
            width: 440px;
            height: 440px;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .kop-logo {
            width: 60px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            padding: 0 8px;
        }
        .kop-org-1 {
            font-size: 8.5pt;
            font-weight: bold;
            color: #002b40;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-org-2 {
            font-size: 12pt;
            font-weight: 900;
            color: #005c8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 1.5px 0 0;
        }
        .kop-sub {
            font-size: 6.8pt;
            color: #475569;
            margin: 2px 0 0;
            line-height: 1.25;
        }
        .batik-divider {
            width: 100%;
            height: 6px;
            margin-top: 4px;
            margin-bottom: 2px;
            display: block;
        }
        .kop-divider-line {
            border-bottom: 1.5px solid #002b40;
            margin-bottom: 8px;
        }

        /* Document Title */
        .doc-header {
            text-align: center;
            margin-bottom: 9px;
            position: relative;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 7pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 1.5px;
        }
        .doc-reg {
            font-size: 7pt;
            color: #64748b;
            font-family: monospace;
            margin-top: 1px;
        }

        /* Profile Card Top */
        .profile-card {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 9px;
        }
        .profile-card td {
            vertical-align: top;
            padding: 7px;
        }
        .photo-cell {
            width: 78px;
            text-align: center;
        }
        .photo-frame {
            width: 72px;
            height: 96px;
            border: 1.5px solid #005c8a;
            background: #ffffff;
            border-radius: 4px;
            overflow: hidden;
            text-align: center;
        }
        .photo-frame img {
            width: 72px;
            height: 96px;
            object-fit: cover;
        }
        .photo-placeholder {
            line-height: 96px;
            font-size: 7pt;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
        }
        .profile-info {
            padding-left: 8px !important;
        }
        .kader-name {
            font-size: 12pt;
            font-weight: 900;
            color: #002b40;
            line-height: 1.15;
            margin-bottom: 2px;
        }
        .kader-meta {
            font-size: 7.5pt;
            color: #334155;
            margin-bottom: 2px;
        }
        .kader-badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #e0f2fe;
            color: #0369a1;
            border: 0.8px solid #bae6fd;
            margin-top: 2px;
        }
        .qr-cell {
            width: 82px;
            text-align: center;
        }
        .qr-img {
            width: 68px;
            height: 68px;
            border: 1px solid #cbd5e1;
            padding: 2px;
            background: #ffffff;
        }
        .qr-caption {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 2px;
            line-height: 1.1;
        }

        /* Section Styling */
        .section-header {
            background-color: #002b40;
            color: #ffffff;
            padding: 3.5px 7px;
            font-size: 7.8pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-radius: 3px;
            margin-top: 6px;
            margin-bottom: 3px;
        }
        .section-header span {
            color: #f8bb00;
            margin-right: 4px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 7.5pt;
        }
        .data-table td {
            padding: 2.2px 5px;
            vertical-align: top;
            border-bottom: 0.5px solid #e2e8f0;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .data-table .label {
            width: 135px;
            color: #475569;
            font-weight: bold;
        }
        .data-table .colon {
            width: 8px;
            text-align: center;
            color: #94a3b8;
        }
        .data-table .val {
            color: #0f172a;
        }

        /* Two-Column Layout Table */
        .grid-2col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .grid-2col > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
            border: none;
        }
        .grid-2col > tbody > tr > td:first-child {
            padding-left: 0;
            padding-right: 4px;
        }
        .grid-2col > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 4px;
        }

        /* Mini Badges / Pills */
        .pill {
            display: inline-block;
            padding: 1px 5px;
            font-size: 6.8pt;
            font-weight: 600;
            border-radius: 2px;
            background: #f1f5f9;
            border: 0.5px solid #cbd5e1;
            color: #334155;
            margin: 1px;
        }
        .pill-gold {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e;
        }
        .pill-sky {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        /* Signature & Validation Box */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7.5pt;
        }
        .sign-table td {
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 10px;
        }
        .sign-date {
            font-size: 7.2pt;
            color: #475569;
            margin-bottom: 2px;
        }
        .sign-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #002b40;
            text-transform: uppercase;
            line-height: 1.25;
            margin-bottom: 42px;
        }
        .sign-name {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            margin: 0;
        }
        .sign-role {
            font-size: 6.8pt;
            color: #64748b;
            margin-top: 1px;
        }

        .footer-note {
            margin-top: 8px;
            border-top: 0.5px dashed #cbd5e1;
            padding-top: 3px;
            font-size: 6pt;
            color: #94a3b8;
            text-align: justify;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    {{-- Watermark Latar Belakang Batik Surya --}}
    <div class="watermark-container">
        <img class="watermark-img" src="{{ public_path('aset/batik-watermark.png') }}" alt="Watermark">
    </div>

    {{-- 1. KOP SURAT RESMI MPKSDI PDM KARANGANYAR --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-mpksdi.png') }}" style="height: 52px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td class="kop-text">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah Karanganyar &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar, Jawa Tengah 57722<br>
                    Layanan Terpadu &bull; Pangkalan Data Kader Persyarikatan &bull; Website: <i>mpksdikra.muhammadiyah.or.id</i>
                </div>
            </td>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-muhammadiyah.png') }}" style="height: 48px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>

    {{-- Ornamen Garis Batik Solo --}}
    <img class="batik-divider" src="{{ public_path('aset/batik-border-band.png') }}" alt="Ornamen Batik">
    <div class="kop-divider-line"></div>

    {{-- 2. JUDUL DOKUMEN --}}
    <div class="doc-header">
        <h1 class="doc-title">CURRICULUM VITAE &amp; BIODATA KADER</h1>
        <div class="doc-subtitle">Pangkalan Data Resmi Kader Persyarikatan Muhammadiyah Kabupaten Karanganyar</div>
        <div class="doc-reg">No. Registrasi: MPKSDI-KRA/{{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/{{ $anggota->pcm ? strtoupper(str_replace([' ', '/', '-'], '', $anggota->pcm)) : 'KRA' }} &bull; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
    </div>

    {{-- 3. PROFILE HERO CARD --}}
    <table class="profile-card">
        <tr>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if(!empty($fotoDataUri))
                        <img src="{{ $fotoDataUri }}" alt="Pasfoto {{ $anggota->nama_lengkap }}">
                    @else
                        <div class="photo-placeholder">PASFOTO 3&times;4</div>
                    @endif
                </div>
            </td>
            <td class="profile-info">
                <div class="kader-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                <div class="kader-meta">
                    <b>NIK:</b> {{ $anggota->nik }} &bull; 
                    <b>NBM:</b> {{ $anggota->nbm ?: '-' }} &bull; 
                    <b>Usia:</b> {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->age : ($anggota->umur ?: '-') }} Tahun
                </div>
                <div class="kader-meta">
                    <b>Wilayah Cabang (PCM):</b> {{ $anggota->pcm ?: ($anggota->kecamatan ?: '-') }} &bull; 
                    <b>Ranting (PRM):</b> {{ $anggota->prm ?: '-' }}
                </div>
                <div class="kader-meta">
                    <b>Profesi / Jabatan:</b> {{ $anggota->pekerjaan ?: ($anggota->jabatan_aum ?: 'Kader Persyarikatan') }}
                </div>
                <div style="margin-top: 4px;">
                    <span class="kader-badge">
                        Status: {{ strtoupper($anggota->user->status ?? 'AKTIF') }} &bull; TERVERIFIKASI MPKSDI
                    </span>
                    @if($anggota->pendidikan_terakhir)
                        <span class="kader-badge" style="background: #fef3c7; color: #92400e; border-color: #fde68a;">
                            Pendidikan: {{ $anggota->pendidikan_terakhir }}
                        </span>
                    @endif
                </div>
            </td>
            <td class="qr-cell">
                <img class="qr-img" src="{{ $qrPng }}" alt="QR Verifikasi">
                <div class="qr-caption">Pindai Verifikasi<br>Keaslian Dokumen</div>
            </td>
        </tr>
    </table>

    {{-- 4. SECTION I: DATA PRIBADI & KEPENDUDUKAN --}}
    <div class="section-header"><span>I.</span> Data Pribadi &amp; Kependudukan</div>
    <table class="grid-2col">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Nama Lengkap</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->nama_lengkap }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">NIK (No. KTP)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->nik }}</td>
                    </tr>
                    <tr>
                        <td class="label">NBM (No. Baku Muh.)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->nbm ?: 'Belum Memiliki' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tempat, Tgl Lahir</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->tempat_lahir ?: '-' }}, {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jenis Kelamin</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->jenis_kelamin ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status Pernikahan</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->status_pernikahan ?: '-' }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Nama Ayah Kandung</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->nama_ayah ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Nama Ibu Kandung</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->nama_ibu ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Alamat Rumah / KTP</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->alamat_rumah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Desa / Kelurahan</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->desa_kelurahan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kecamatan &amp; Kab.</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->kecamatan ?: '-' }}, {{ $anggota->kabupaten ?: 'Kab. Karanganyar' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Kontak WhatsApp / HP</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->no_wa ?: '-' }}</b> ({{ $anggota->email ?: '-' }})</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 5. SECTION II: RIWAYAT PENDIDIKAN --}}
    <div class="section-header"><span>II.</span> Riwayat Pendidikan &amp; Kompetensi</div>
    <table class="data-table">
        <tr>
            <td class="label" style="width: 140px;">Pendidikan Terakhir</td>
            <td class="colon">:</td>
            <td class="val">
                <span class="pill pill-gold">{{ $anggota->pendidikan_terakhir ?: 'Belum diisi' }}</span>
            </td>
            <td class="label" style="width: 130px;">Bahasa Dikuasai</td>
            <td class="colon">:</td>
            <td class="val">
                @if(!empty($anggota->bahasa_dikuasai) && is_array($anggota->bahasa_dikuasai))
                    @foreach($anggota->bahasa_dikuasai as $b)
                        <span class="pill">{{ $b }}</span>
                    @endforeach
                @else
                    {{ is_string($anggota->bahasa_dikuasai) ? $anggota->bahasa_dikuasai : 'Bahasa Indonesia' }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Sekolah Dasar (SD/MI)</td>
            <td class="colon">:</td>
            <td class="val">{{ $anggota->pendidikan_sd ?: '-' }}</td>
            <td class="label">SMP / MTs</td>
            <td class="colon">:</td>
            <td class="val">{{ $anggota->pendidikan_smp ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">SMA / SMK / MA</td>
            <td class="colon">:</td>
            <td class="val">{{ $anggota->pendidikan_sma ?: '-' }}</td>
            <td class="label">Perguruan Tinggi (S1/D3)</td>
            <td class="colon">:</td>
            <td class="val"><b>{{ $anggota->pendidikan_s1 ?: '-' }}</b></td>
        </tr>
    </table>

    {{-- 6. SECTION III: AMANAH PERSYARIKATAN & PROFESI --}}
    <div class="section-header"><span>III.</span> Amanah Persyarikatan, Profesi &amp; Organisasi</div>
    <table class="grid-2col">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Pekerjaan / Profesi</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->pekerjaan ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Amanah / Jabatan AUM</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->jabatan_aum ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pimpinan Cabang (PCM)</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->pcm ?: ($anggota->kecamatan ?: '-') }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Pimpinan Ranting (PRM)</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->prm ?: '-' }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Struktur Muhammadiyah</td>
                        <td class="colon">:</td>
                        <td class="val">
                            @if(!empty($anggota->keaktifan_muhammadiyah) && is_array($anggota->keaktifan_muhammadiyah))
                                @foreach($anggota->keaktifan_muhammadiyah as $km)
                                    <span class="pill pill-sky">{{ $km }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Organisasi Otonom (Ortom)</td>
                        <td class="colon">:</td>
                        <td class="val">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                @foreach($anggota->keaktifan_ortom as $ko)
                                    <span class="pill">{{ $ko }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan di Ortom</td>
                        <td class="colon">:</td>
                        <td class="val">
                            @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                {{ implode(', ', array_filter($anggota->jabatan_ortom)) ?: '-' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Organisasi Lainnya</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->organisasi_lain ?: '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 7. SECTION IV: PROFIL KEAGAMAAN & PENGABDIAN KEMASYARAKATAN --}}
    <div class="section-header"><span>IV.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
    <table class="grid-2col">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Kemampuan Baca Al-Qur'an</td>
                        <td class="colon">:</td>
                        <td class="val"><b>{{ $anggota->kemampuan_baca_quran ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="label">Hafalan Al-Qur'an</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->hafalan_quran ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Sholat 5 Waktu di Masjid</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->aktivitas_sholat_masjid ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pengajian / Kajian Rutin</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->aktivitas_kajian_agama ?: '-' }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="label">Penyaluran ZIS</td>
                        <td class="colon">:</td>
                        <td class="val">
                            @if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis))
                                {{ implode(', ', $anggota->lembaga_zis) }}
                            @else
                                {{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Masjid Binaan / Jamaah</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->aktifitas_jamaah_masjid ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pengurus / Takmir Masjid</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->keaktifan_takmir ?: 'Tidak' }} @if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }}) @endif</td>
                    </tr>
                    <tr>
                        <td class="label">Peran Kemasyarakatan</td>
                        <td class="colon">:</td>
                        <td class="val">{{ $anggota->keaktifan_kemasyarakatan ?: '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 8. SECTION V: RIWAYAT PERKADERAN BAITUL ARQAM & INFORMASI DINAMIS --}}
    @php
        $riwayatEvent = $anggota->eventPeserta()->with('event')->get();
        $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
    @endphp

    @if($riwayatEvent->isNotEmpty() || !empty($customData))
    <div class="section-header"><span>V.</span> Riwayat Perkaderan &amp; Informasi Tambahan</div>
    <table class="data-table">
        @if($riwayatEvent->isNotEmpty())
        <tr>
            <td class="label" style="width: 140px;">Riwayat Baitul Arqam</td>
            <td class="colon">:</td>
            <td class="val" colspan="4">
                @foreach($riwayatEvent as $rep)
                    <div style="margin-bottom: 2px;">
                        &bull; <b>{{ $rep->event->nama_event }}</b> 
                        ({{ $rep->event->tanggal_mulai ? $rep->event->tanggal_mulai->format('d/m/Y') : '-' }})
                        @if($rep->status_kelulusan) — <span class="pill pill-sky">{{ $rep->status_kelulusan }}</span> @endif
                    </div>
                @endforeach
            </td>
        </tr>
        @endif

        @if(!empty($customData))
            @foreach($customData as $key => $val)
                @php
                    $labelKustom = ucwords(str_replace('_', ' ', $key));
                    $pertanyaanObj = \App\Models\FormPertanyaan::where('nama_field', $key)->first();
                    if ($pertanyaanObj) {
                        $labelKustom = $pertanyaanObj->pertanyaan;
                    }
                    $displayVal = is_array($val) ? implode(', ', $val) : (string)$val;
                @endphp
                @if(trim($displayVal) !== '')
                <tr>
                    <td class="label" style="width: 140px;">{{ $labelKustom }}</td>
                    <td class="colon">:</td>
                    <td class="val" colspan="4"><b>{{ $displayVal }}</b></td>
                </tr>
                @endif
            @endforeach
        @endif
    </table>
    @endif

    {{-- 9. LEMBAR TANDA TANGAN & PENGESAHAN DOKUMEN --}}
    <table class="sign-table">
        <tr>
            <td style="width: 50%;">
                <div class="sign-date">&nbsp;</div>
                <div class="sign-title">Kader Yang Bersangkutan,</div>
                <div class="sign-name">{{ $anggota->nama_lengkap }}</div>
                <div class="sign-role">NBM: {{ $anggota->nbm ?: 'Kader Muhammadiyah Karanganyar' }}</div>
            </td>
            <td style="width: 50%;">
                <div class="sign-date">Karanganyar, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="sign-title">
                    Majelis Pembinaan Kader &amp; SDI (MPKSDI)<br>
                    Pimpinan Daerah Muhammadiyah Karanganyar<br>
                    Ketua,
                </div>
                <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                <div class="sign-role">NBM: 864.219 &bull; PDM Karanganyar</div>
            </td>
        </tr>
    </table>

    {{-- 10. FOOTNOTE KEASLIAN ARSIP --}}
    <div class="footer-note">
        <b>Catatan Pengesahan:</b> Dokumen Curriculum Vitae / Biodata Kader ini dicetak secara sah dan otomatis dari Sistem Informasi Pangkalan Data MPKSDI PDM Karanganyar. Data yang tercantum bersumber dari formulir pendaftaran resmi yang telah diverifikasi oleh Majelis. Keaslian dokumen dapat dibuktikan dengan memindai kode QR resmi di atas.
    </div>

</body>
</html>
