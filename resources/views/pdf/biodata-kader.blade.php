<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Curriculum Vitae / Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm 9mm 5mm 9mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #ffffff;
            position: relative;
        }

        /* Watermark Logo MPKSDI Ultra-Subtle di Tengah Halaman */
        .watermark-bg {
            position: fixed;
            top: 30%;
            left: 28%;
            width: 320px;
            z-index: -1000;
            text-align: center;
        }
        .watermark-bg img {
            width: 320px;
            height: auto;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .kop-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .kop-logo {
            width: 46px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            padding: 0 8px;
        }
        .kop-org-1 {
            font-size: 7.2pt;
            font-weight: bold;
            color: #002b40;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-org-2 {
            font-size: 10pt;
            font-weight: 900;
            color: #005c8a;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin: 1px 0 0;
        }
        .kop-sub {
            font-size: 5.8pt;
            color: #64748b;
            margin: 1px 0 0;
            line-height: 1.2;
        }
        .kop-divider {
            border-top: 1.8px solid #002b40;
            border-bottom: 0.6px solid #f8bb00;
            height: 1.2px;
            margin-top: 2.5px;
            margin-bottom: 4px;
        }

        /* Sub-Header Dokumen */
        .doc-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            padding-bottom: 2px;
            border-bottom: 1px solid #e2e8f0;
        }
        .doc-bar td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .doc-title {
            font-size: 8.5pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-meta {
            font-size: 5.6pt;
            color: #64748b;
            text-align: right;
            font-family: monospace;
        }

        /* Layout Utama: 2 Kolom Sejajar */
        .main-layout {
            width: 100%;
            border-collapse: collapse;
        }
        .main-layout > tbody > tr > td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .col-sidebar {
            width: 175px;
            padding-right: 6px;
        }
        .col-content {
            padding-left: 6px;
        }

        /* Sidebar Container (Panel Kiri) */
        .sidebar-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 7px;
        }

        /* Foto Kader */
        .photo-wrap {
            text-align: center;
            margin-bottom: 4px;
        }
        .photo-frame {
            display: inline-block;
            width: 54px;
            height: 70px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 3px;
            overflow: hidden;
            text-align: center;
        }
        .photo-frame img {
            width: 54px;
            height: 70px;
            object-fit: cover;
        }
        .photo-placeholder {
            line-height: 70px;
            font-size: 6pt;
            color: #94a3b8;
            font-weight: bold;
        }

        /* Identitas Singkat di Sidebar */
        .side-name {
            font-size: 9pt;
            font-weight: 900;
            color: #002b40;
            text-align: center;
            line-height: 1.15;
            margin-bottom: 2px;
        }
        .side-nik {
            font-size: 6.2pt;
            color: #64748b;
            text-align: center;
            font-family: monospace;
            margin-bottom: 3px;
        }
        .side-status-badge {
            text-align: center;
            margin-bottom: 5px;
        }
        .badge-pill {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 5.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-aktif {
            background: #e0f2fe;
            color: #0369a1;
            border: 0.5px solid #bae6fd;
        }

        /* Group Divider Sidebar */
        .side-divider {
            border-bottom: 0.5px solid #e2e8f0;
            margin: 4px 0;
        }
        .side-heading {
            font-size: 6.2pt;
            font-weight: 800;
            color: #005c8a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2.5px;
        }

        /* List Data di Sidebar */
        .side-item {
            margin-bottom: 2.5px;
            line-height: 1.2;
        }
        .side-label {
            font-size: 5.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
        }
        .side-val {
            font-size: 6.5pt;
            color: #0f172a;
            font-weight: 600;
            word-wrap: break-word;
        }

        /* QR Verifikasi di Sidebar Bawah */
        .side-qr-wrap {
            text-align: center;
            margin-top: 5px;
            padding-top: 4px;
            border-top: 0.5px dashed #cbd5e1;
        }
        .side-qr-img {
            width: 48px;
            height: 48px;
            border: 0.5px solid #cbd5e1;
            padding: 1px;
            background: #ffffff;
        }
        .side-qr-text {
            font-size: 4.8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 1.5px;
            line-height: 1;
        }

        /* Kolom Kanan (Konten Utama) */
        .sec-header {
            font-size: 7.2pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1.2px solid #005c8a;
            padding-bottom: 1px;
            margin-top: 0;
            margin-bottom: 3.5px;
        }
        .sec-header-subsequent {
            margin-top: 4.5px;
        }
        .sec-header span {
            color: #f8bb00;
            margin-right: 3px;
        }

        /* Grid Data Bersih di Kolom Kanan */
        .cardlet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .cardlet-table td {
            vertical-align: top;
            padding: 1.5px 3px;
            border-bottom: 0.5px solid #f1f5f9;
            font-size: 6.8pt;
            line-height: 1.22;
        }
        .cardlet-table tr:last-child td {
            border-bottom: none;
        }
        .c-label {
            width: 112px;
            color: #64748b;
            font-weight: 600;
        }
        .c-colon {
            width: 5px;
            text-align: center;
            color: #94a3b8;
        }
        .c-val {
            color: #0f172a;
        }

        /* Split 2 Kolom di Kolom Kanan */
        .right-split {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .right-split > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 2px;
            border: none;
        }
        .right-split > tbody > tr > td:first-child {
            padding-left: 0;
            padding-right: 2px;
        }
        .right-split > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 2px;
        }

        .tag-pill {
            display: inline-block;
            padding: 0.5px 3.5px;
            font-size: 6pt;
            font-weight: 600;
            border-radius: 2px;
            background: #f1f5f9;
            border: 0.5px solid #cbd5e1;
            color: #334155;
            margin: 0.5px;
        }
        .tag-sky {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        /* Lembar Pengesahan */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 0.8px solid #e2e8f0;
        }
        .sign-table td {
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 6px;
        }
        .sign-date {
            font-size: 6.2pt;
            color: #64748b;
            margin-bottom: 1px;
        }
        .sign-role {
            font-size: 6.5pt;
            font-weight: bold;
            color: #002b40;
            text-transform: uppercase;
            line-height: 1.15;
            margin-bottom: 22px;
        }
        .sign-name {
            font-size: 7.2pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            margin: 0;
        }
        .sign-meta {
            font-size: 5.6pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Catatan Kaki */
        .footer-note {
            margin-top: 3px;
            border-top: 0.5px dashed #cbd5e1;
            padding-top: 1.5px;
            font-size: 5pt;
            color: #94a3b8;
            text-align: justify;
            line-height: 1.15;
        }
    </style>
</head>
<body>

    {{-- Watermark Logo MPKSDI Halus di Tengah Halaman --}}
    <div class="watermark-bg">
        <img src="{{ public_path('aset/logo-mpksdi-watermark.png') }}" alt="Watermark MPKSDI">
    </div>

    {{-- 1. KOP SURAT RESMI MPKSDI PDM KARANGANYAR --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-mpksdi.png') }}" style="height: 38px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td class="kop-text">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar 57722 &bull; Web: <i>mpksdikra.muhammadiyah.or.id</i>
                </div>
            </td>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-muhammadiyah.png') }}" style="height: 36px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- 2. BAR SUB-HEADER DOKUMEN --}}
    <table class="doc-bar">
        <tr>
            <td class="doc-title">CURRICULUM VITAE &amp; BIODATA KADER RESMI</td>
            <td class="doc-meta">No. Registrasi: MPKSDI-KRA/{{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/{{ $anggota->pcm ? strtoupper(str_replace([' ', '/', '-'], '', $anggota->pcm)) : 'KRA' }} &bull; {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>

    {{-- 3. EXECUTIVE 2-COLUMN LAYOUT --}}
    <table class="main-layout">
        <tr>
            {{-- KOLOM KIRI: SIDEBAR KADER (33%) --}}
            <td class="col-sidebar">
                <div class="sidebar-box">
                    {{-- Pasfoto Formal --}}
                    <div class="photo-wrap">
                        <div class="photo-frame">
                            @if(!empty($fotoDataUri))
                                <img src="{{ $fotoDataUri }}" alt="Foto">
                            @else
                                <div class="photo-placeholder">FOTO 3&times;4</div>
                            @endif
                        </div>
                    </div>

                    {{-- Nama & Status --}}
                    <div class="side-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                    <div class="side-nik">NIK: {{ $anggota->nik }}</div>
                    <div class="side-status-badge">
                        <span class="badge-pill badge-aktif">KADER {{ strtoupper($anggota->user->status ?? 'AKTIF') }}</span>
                    </div>

                    {{-- Data Pribadi & Keluarga --}}
                    <div class="side-heading">DATA PRIBADI &amp; KELUARGA</div>
                    <div class="side-item">
                        <span class="side-label">NBM (No. Baku Muhammadiyah)</span>
                        <span class="side-val">{{ $anggota->nbm ?: 'Belum Memiliki' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Tempat &amp; Tanggal Lahir</span>
                        <span class="side-val">{{ $anggota->tempat_lahir ?: '-' }}, {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->translatedFormat('d M Y') : '-' }} ({{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->age : ($anggota->umur ?: '-') }} Thn)</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Jenis Kelamin / Pernikahan</span>
                        <span class="side-val">{{ $anggota->jenis_kelamin ?: '-' }} &bull; {{ $anggota->status_pernikahan ?: '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Nama Ayah Kandung</span>
                        <span class="side-val"><b>{{ $anggota->nama_ayah ?: '-' }}</b></span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Nama Ibu Kandung</span>
                        <span class="side-val"><b>{{ $anggota->nama_ibu ?: '-' }}</b></span>
                    </div>

                    <div class="side-divider"></div>

                    {{-- Kontak & Domisili --}}
                    <div class="side-heading">KONTAK &amp; DOMISILI</div>
                    <div class="side-item">
                        <span class="side-label">WhatsApp / No. HP</span>
                        <span class="side-val"><b>{{ $anggota->no_wa ?: '-' }}</b></span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Alamat Email</span>
                        <span class="side-val">{{ $anggota->email ?: '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Alamat Lengkap (KTP)</span>
                        <span class="side-val">{{ $anggota->alamat_rumah ?: '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Kelurahan &amp; Kecamatan</span>
                        <span class="side-val">{{ $anggota->desa_kelurahan ?: '-' }}, {{ $anggota->kecamatan ?: '-' }}</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Kabupaten</span>
                        <span class="side-val">{{ $anggota->kabupaten ?: 'Kab. Karanganyar' }}</span>
                    </div>

                    <div class="side-divider"></div>

                    {{-- Pendidikan & Bahasa --}}
                    <div class="side-heading">PENDIDIKAN &amp; BAHASA</div>
                    <div class="side-item">
                        <span class="side-label">Pendidikan Terakhir</span>
                        <span class="side-val"><b>{{ $anggota->pendidikan_terakhir ?: '-' }}</b> @if($anggota->pendidikan_s1) ({{ $anggota->pendidikan_s1 }}) @elseif($anggota->pendidikan_sma) ({{ $anggota->pendidikan_sma }}) @endif</span>
                    </div>
                    <div class="side-item">
                        <span class="side-label">Bahasa yang Dikuasai</span>
                        <span class="side-val">
                            @if(!empty($anggota->bahasa_dikuasai) && is_array($anggota->bahasa_dikuasai))
                                {{ implode(', ', $anggota->bahasa_dikuasai) }}
                            @else
                                {{ is_string($anggota->bahasa_dikuasai) ? $anggota->bahasa_dikuasai : 'Indonesia' }}
                            @endif
                        </span>
                    </div>

                    {{-- QR Code Verifikasi Dokumen --}}
                    <div class="side-qr-wrap">
                        <img class="side-qr-img" src="{{ $qrPng }}" alt="QR Verifikasi">
                        <div class="side-qr-text">Pindai Verifikasi<br>Keaslian Dokumen</div>
                    </div>
                </div>
            </td>

            {{-- KOLOM KANAN: KONTEN UTAMA (67%) --}}
            <td class="col-content">
                {{-- SEKSI 1: AMANAH PERSYARIKATAN & KIPRAH PROFESI --}}
                <div class="sec-header"><span>I.</span> Amanah Persyarikatan, Profesi &amp; Organisasi</div>
                <table class="cardlet-table">
                    <tr>
                        <td class="c-label">Profesi / Pekerjaan Utama</td><td class="c-colon">:</td>
                        <td class="c-val"><b>{{ $anggota->pekerjaan ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="c-label">Amanah di Amal Usaha (AUM)</td><td class="c-colon">:</td>
                        <td class="c-val">{{ $anggota->jabatan_aum ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="c-label">Pimpinan Cabang (PCM)</td><td class="c-colon">:</td>
                        <td class="c-val"><b>{{ $anggota->pcm ?: ($anggota->kecamatan ?: '-') }}</b></td>
                    </tr>
                    <tr>
                        <td class="c-label">Pimpinan Ranting (PRM)</td><td class="c-colon">:</td>
                        <td class="c-val">{{ $anggota->prm ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="c-label">Struktur Pimpinan Muhammadiyah</td><td class="c-colon">:</td>
                        <td class="c-val">
                            @if(!empty($anggota->keaktifan_muhammadiyah) && is_array($anggota->keaktifan_muhammadiyah))
                                @foreach($anggota->keaktifan_muhammadiyah as $km)
                                    <span class="tag-pill tag-sky">{{ $km }}</span>
                                @endforeach
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="c-label">Organisasi Otonom (Ortom)</td><td class="c-colon">:</td>
                        <td class="c-val">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                @foreach($anggota->keaktifan_ortom as $ko)
                                    <span class="tag-pill">{{ $ko }}</span>
                                @endforeach
                                @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                    <span style="color: #475569; font-size: 6.2pt;">({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})</span>
                                @endif
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="c-label">Organisasi Eksternal Lainnya</td><td class="c-colon">:</td>
                        <td class="c-val">{{ $anggota->organisasi_lain ?: '-' }}</td>
                    </tr>
                </table>

                {{-- SEKSI 2: PROFIL KEAGAMAAN, KEMASJIDAN & KEMASYARAKATAN --}}
                <div class="sec-header sec-header-subsequent"><span>II.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
                <table class="right-split">
                    <tr>
                        <td>
                            <table class="cardlet-table">
                                <tr>
                                    <td class="c-label" style="width: 82px;">Baca Al-Qur'an</td><td class="c-colon">:</td>
                                    <td class="c-val"><b>{{ $anggota->kemampuan_baca_quran ?: '-' }}</b></td>
                                </tr>
                                <tr>
                                    <td class="c-label">Hafalan Al-Qur'an</td><td class="c-colon">:</td>
                                    <td class="c-val">{{ $anggota->hafalan_quran ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="c-label">Sholat 5 Waktu Masjid</td><td class="c-colon">:</td>
                                    <td class="c-val">{{ $anggota->aktivitas_sholat_masjid ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="c-label">Kajian Rutin Agama</td><td class="c-colon">:</td>
                                    <td class="c-val">{{ $anggota->aktivitas_kajian_agama ?: '-' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table class="cardlet-table">
                                <tr>
                                    <td class="c-label" style="width: 78px;">Penyaluran ZIS</td><td class="c-colon">:</td>
                                    <td class="c-val">
                                        @if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis))
                                            {{ implode(', ', $anggota->lembaga_zis) }}
                                        @else
                                            {{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '-' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="c-label">Masjid Binaan</td><td class="c-colon">:</td>
                                    <td class="c-val">{{ $anggota->aktifitas_jamaah_masjid ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="c-label">Pengurus Takmir</td><td class="c-colon">:</td>
                                    <td class="c-val">
                                        {{ $anggota->keaktifan_takmir ?: 'Tidak' }}
                                        @if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }}) @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="c-label">Kemasyarakatan</td><td class="c-colon">:</td>
                                    <td class="c-val">{{ $anggota->keaktifan_kemasyarakatan ?: '-' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- SEKSI 3: REKAM JEJAK PERKADERAN & KETERANGAN DINAMIS --}}
                @php
                    $riwayatEvent = $anggota->eventPeserta()->with('event')->get();
                    $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
                @endphp
                <div class="sec-header sec-header-subsequent"><span>III.</span> Rekam Jejak Perkaderan &amp; Keterangan Tambahan</div>
                <table class="cardlet-table">
                    <tr>
                        <td class="c-label">Riwayat Baitul Arqam</td><td class="c-colon">:</td>
                        <td class="c-val">
                            @if($riwayatEvent->isNotEmpty())
                                @foreach($riwayatEvent as $rep)
                                    <b>{{ $rep->event->nama_event }}</b>@if($rep->status_kelulusan) <span class="tag-pill tag-sky">{{ $rep->status_kelulusan }}</span>@endif;
                                @endforeach
                            @else
                                <span class="text-slate-400">Belum tercatat riwayat pelatihan Baitul Arqam</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($customData))
                        @foreach($customData as $k => $v)
                            @php
                                $labelK = ucwords(str_replace('_', ' ', $k));
                                $pertanyaanObj = \App\Models\FormPertanyaan::where('nama_field', $k)->first();
                                if ($pertanyaanObj) {
                                    $labelK = $pertanyaanObj->pertanyaan;
                                }
                                $vStr = is_array($v) ? implode(', ', $v) : (string)$v;
                            @endphp
                            @if(trim($vStr) !== '')
                            <tr>
                                <td class="c-label">{{ $labelK }}</td><td class="c-colon">:</td>
                                <td class="c-val"><b>{{ $vStr }}</b></td>
                            </tr>
                            @endif
                        @endforeach
                    @endif
                </table>

                {{-- SEKSI 4: LEMBAR PENGESAHAN DOKUMEN --}}
                <table class="sign-table">
                    <tr>
                        <td style="width: 46%;">
                            <div class="sign-date">&nbsp;</div>
                            <div class="sign-role">Kader Yang Bersangkutan,</div>
                            <div class="sign-name">{{ $anggota->nama_lengkap }}</div>
                            <div class="sign-meta">NBM: {{ $anggota->nbm ?: 'Kader Muhammadiyah' }}</div>
                        </td>
                        <td style="width: 54%;">
                            <div class="sign-date">Karanganyar, {{ now()->translatedFormat('d F Y') }}</div>
                            <div class="sign-role">
                                PDM Karanganyar &bull; MPKSDI<br>
                                Ketua,
                            </div>
                            <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                            <div class="sign-meta">NBM: 864.219 &bull; PDM Karanganyar</div>
                        </td>
                    </tr>
                </table>

                {{-- CATATAN KAKI DOKUMEN --}}
                <div class="footer-note">
                    <b>Pengesahan:</b> Dokumen Curriculum Vitae / Biodata resmi diterbitkan melalui Pangkalan Data Terpadu MPKSDI PDM Karanganyar. Validitas data diverifikasi melalui pemindaian QR Code resmi.
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
