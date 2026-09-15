<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Curriculum Vitae / Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm 10mm 6mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7.2pt;
            color: #0f172a;
            line-height: 1.25;
            margin: 0;
            padding: 0;
            background: #ffffff;
            position: relative;
        }

        /* Watermark Tengah: Logo Resmi MPKSDI */
        .watermark-center {
            position: fixed;
            top: 33%;
            left: 27%;
            width: 330px;
            z-index: -1000;
            text-align: center;
        }
        .watermark-center img {
            width: 330px;
            height: auto;
        }

        /* SVG Batik Pattern Corners */
        .batik-pattern-tr {
            position: fixed;
            top: -25px;
            right: -25px;
            width: 135px;
            height: 135px;
            z-index: -500;
            opacity: 0.16;
        }
        .batik-pattern-bl {
            position: fixed;
            bottom: -25px;
            left: -25px;
            width: 125px;
            height: 125px;
            z-index: -500;
            opacity: 0.14;
        }

        /* Kop Surat */
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
            width: 52px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            padding: 0 6px;
        }
        .kop-org-1 {
            font-size: 7.8pt;
            font-weight: bold;
            color: #002b40;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-org-2 {
            font-size: 10.5pt;
            font-weight: 900;
            color: #005c8a;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin: 1px 0 0;
        }
        .kop-sub {
            font-size: 6pt;
            color: #475569;
            margin: 1px 0 0;
            line-height: 1.2;
        }
        .kop-divider {
            border-top: 2px solid #002b40;
            border-bottom: 0.6px solid #f8bb00;
            height: 1.5px;
            margin-top: 3px;
            margin-bottom: 3px;
        }

        /* Document Title */
        .doc-header {
            text-align: center;
            margin-bottom: 4px;
        }
        .doc-title {
            font-size: 9.5pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 6.2pt;
            font-weight: bold;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }
        .doc-meta {
            font-size: 5.8pt;
            color: #64748b;
            font-family: monospace;
        }

        /* Profile Hero Card */
        .profile-card {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 4px;
        }
        .profile-card td {
            vertical-align: middle;
            padding: 4px 6px;
        }
        .photo-cell {
            width: 52px;
            text-align: center;
        }
        .photo-frame {
            width: 48px;
            height: 62px;
            border: 1.2px solid #005c8a;
            background: #ffffff;
            border-radius: 3px;
            overflow: hidden;
            text-align: center;
        }
        .photo-frame img {
            width: 48px;
            height: 62px;
            object-fit: cover;
        }
        .photo-placeholder {
            line-height: 62px;
            font-size: 6pt;
            color: #94a3b8;
            font-weight: bold;
        }
        .profile-info {
            padding-left: 6px !important;
        }
        .kader-name {
            font-size: 10.5pt;
            font-weight: 900;
            color: #002b40;
            line-height: 1.15;
            margin-bottom: 1.5px;
        }
        .kader-meta {
            font-size: 6.8pt;
            color: #334155;
            margin-bottom: 1px;
            line-height: 1.2;
        }
        .badge-aktif {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 5.8pt;
            font-weight: bold;
            text-transform: uppercase;
            background: #e0f2fe;
            color: #0369a1;
            border: 0.5px solid #bae6fd;
        }
        .badge-edu {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 5.8pt;
            font-weight: bold;
            background: #fef3c7;
            color: #92400e;
            border: 0.5px solid #fde68a;
        }
        .qr-cell {
            width: 62px;
            text-align: center;
        }
        .qr-img {
            width: 50px;
            height: 50px;
            border: 0.5px solid #cbd5e1;
            padding: 1.5px;
            background: #ffffff;
        }
        .qr-caption {
            font-size: 4.8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-top: 1px;
            line-height: 1;
        }

        /* Section Styling */
        .sec-title {
            background-color: #002b40;
            color: #ffffff;
            padding: 2px 6px;
            font-size: 6.8pt;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            border-radius: 2px;
            margin-top: 3px;
            margin-bottom: 2px;
        }
        .sec-title span {
            color: #f8bb00;
            margin-right: 3px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            margin-bottom: 2px;
        }
        .data-table td {
            padding: 1.4px 4px;
            vertical-align: top;
            border-bottom: 0.5px solid #e2e8f0;
            line-height: 1.2;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .data-table .lbl {
            width: 110px;
            color: #475569;
            font-weight: bold;
        }
        .data-table .cln {
            width: 6px;
            text-align: center;
            color: #94a3b8;
        }
        .data-table .val {
            color: #0f172a;
        }

        /* 2-Column Split */
        .grid-split {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .grid-split > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 3px;
            border: none;
        }
        .grid-split > tbody > tr > td:first-child {
            padding-left: 0;
            padding-right: 3px;
        }
        .grid-split > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 3px;
        }

        /* Pills */
        .pill {
            display: inline-block;
            padding: 0.5px 3.5px;
            font-size: 6.2pt;
            font-weight: 600;
            border-radius: 2px;
            background: #f1f5f9;
            border: 0.5px solid #cbd5e1;
            color: #334155;
            margin: 0.5px;
        }
        .pill-sky {
            background: #e0f2fe;
            border-color: #bae6fd;
            color: #0369a1;
        }

        /* Signatures */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 6.8pt;
        }
        .sign-table td {
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 10px;
        }
        .sign-date {
            font-size: 6.5pt;
            color: #475569;
            margin-bottom: 1px;
        }
        .sign-role-title {
            font-size: 6.8pt;
            font-weight: bold;
            color: #002b40;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 22px;
        }
        .sign-name {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
            margin: 0;
        }
        .sign-nbm {
            font-size: 6pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Footer Note */
        .footer-note {
            margin-top: 4px;
            border-top: 0.5px dashed #cbd5e1;
            padding-top: 2px;
            font-size: 5.2pt;
            color: #94a3b8;
            text-align: justify;
            line-height: 1.15;
        }
    </style>
</head>
<body>

    {{-- Watermark Tengah: Logo Resmi MPKSDI --}}
    <div class="watermark-center">
        <img src="{{ public_path('aset/logo-mpksdi-watermark.png') }}" alt="Watermark MPKSDI">
    </div>

    {{-- Ornamen Pattern SVG Batik Solo --}}
    <img class="batik-pattern-tr" src="{{ public_path('aset/batik-surya-solo.svg') }}" alt="Pattern Batik">
    <img class="batik-pattern-bl" src="{{ public_path('aset/batik-surya-solo.svg') }}" alt="Pattern Batik">

    {{-- 1. KOP SURAT RESMI --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-mpksdi.png') }}" style="height: 40px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td class="kop-text">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar 57722 &bull; Web: <i>mpksdikra.muhammadiyah.or.id</i>
                </div>
            </td>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-muhammadiyah.png') }}" style="height: 38px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- 2. JUDUL DOKUMEN & NOMOR REGISTRASI --}}
    <div class="doc-header">
        <h1 class="doc-title">CURRICULUM VITAE &amp; BIODATA KADER</h1>
        <div class="doc-subtitle">Pangkalan Data Resmi Kader Persyarikatan Muhammadiyah Kabupaten Karanganyar</div>
        <div class="doc-meta">No. Registrasi: MPKSDI-KRA/{{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/{{ $anggota->pcm ? strtoupper(str_replace([' ', '/', '-'], '', $anggota->pcm)) : 'KRA' }} &bull; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
    </div>

    {{-- 3. PROFIL UTAMA (FOTO, METADATA & QR CODE) --}}
    <table class="profile-card">
        <tr>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if(!empty($fotoDataUri))
                        <img src="{{ $fotoDataUri }}" alt="Foto">
                    @else
                        <div class="photo-placeholder">FOTO 3&times;4</div>
                    @endif
                </div>
            </td>
            <td class="profile-info">
                <div class="kader-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                <div class="kader-meta">
                    <b>NIK:</b> {{ $anggota->nik }} &bull; 
                    <b>NBM:</b> {{ $anggota->nbm ?: '-' }} &bull; 
                    <b>Usia:</b> {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->age : ($anggota->umur ?: '-') }} Thn
                </div>
                <div class="kader-meta">
                    <b>PCM (Cabang):</b> {{ $anggota->pcm ?: ($anggota->kecamatan ?: '-') }} &bull; 
                    <b>PRM (Ranting):</b> {{ $anggota->prm ?: '-' }}
                </div>
                <div class="kader-meta">
                    <b>Pekerjaan / Profesi:</b> {{ $anggota->pekerjaan ?: ($anggota->jabatan_aum ?: 'Kader Persyarikatan') }}
                </div>
                <div style="margin-top: 2px;">
                    <span class="badge-aktif">STATUS: {{ strtoupper($anggota->user->status ?? 'AKTIF') }} &bull; TERVERIFIKASI</span>
                    @if($anggota->pendidikan_terakhir)
                        <span class="badge-edu">PENDIDIKAN: {{ $anggota->pendidikan_terakhir }}</span>
                    @endif
                </div>
            </td>
            <td class="qr-cell">
                <img class="qr-img" src="{{ $qrPng }}" alt="QR">
                <div class="qr-caption">Pindai Verifikasi<br>Keaslian Dokumen</div>
            </td>
        </tr>
    </table>

    {{-- 4. BAGIAN I: DATA PRIBADI, KELUARGA & PENDIDIKAN --}}
    <div class="sec-title"><span>I.</span> Data Pribadi, Keluarga &amp; Riwayat Pendidikan</div>
    <table class="grid-split">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Nama Lengkap</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->nama_lengkap }}</b></td>
                    </tr>
                    <tr>
                        <td class="lbl">NIK / NBM</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->nik }} / {{ $anggota->nbm ?: 'Belum Ada' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Tempat, Tgl Lahir</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->tempat_lahir ?: '-' }}, {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Jenis Kelamin / Nikah</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->jenis_kelamin ?: '-' }} &bull; {{ $anggota->status_pernikahan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nama Ayah Kandung</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->nama_ayah ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="lbl">Nama Ibu Kandung</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->nama_ibu ?: '-' }}</b></td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Alamat KTP</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->alamat_rumah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Kelurahan &amp; Kec.</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->desa_kelurahan ?: '-' }}, {{ $anggota->kecamatan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Kabupaten</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->kabupaten ?: 'Karanganyar' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">WhatsApp / Email</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->no_wa ?: '-' }}</b> ({{ $anggota->email ?: '-' }})</td>
                    </tr>
                    <tr>
                        <td class="lbl">Pendidikan Terakhir</td><td class="cln">:</td>
                        <td class="val">
                            <b>{{ $anggota->pendidikan_terakhir ?: '-' }}</b>
                            @if($anggota->pendidikan_s1) &bull; {{ $anggota->pendidikan_s1 }} @elseif($anggota->pendidikan_sma) &bull; {{ $anggota->pendidikan_sma }} @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Bahasa Dikuasai</td><td class="cln">:</td>
                        <td class="val">
                            @if(!empty($anggota->bahasa_dikuasai) && is_array($anggota->bahasa_dikuasai))
                                {{ implode(', ', $anggota->bahasa_dikuasai) }}
                            @else
                                {{ is_string($anggota->bahasa_dikuasai) ? $anggota->bahasa_dikuasai : 'Indonesia' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 5. BAGIAN II: AMANAH PERSYARIKATAN, PROFESI & ORGANISASI --}}
    <div class="sec-title"><span>II.</span> Amanah Persyarikatan, Profesi &amp; Organisasi</div>
    <table class="grid-split">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Pekerjaan / Profesi</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->pekerjaan ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="lbl">Amanah / Jabatan AUM</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->jabatan_aum ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Pimpinan Cabang (PCM)</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->pcm ?: ($anggota->kecamatan ?: '-') }}</b></td>
                    </tr>
                    <tr>
                        <td class="lbl">Pimpinan Ranting (PRM)</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->prm ?: '-' }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Struktur Muhammadiyah</td><td class="cln">:</td>
                        <td class="val">
                            @if(!empty($anggota->keaktifan_muhammadiyah) && is_array($anggota->keaktifan_muhammadiyah))
                                {{ implode(', ', $anggota->keaktifan_muhammadiyah) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Organisasi Otonom (Ortom)</td><td class="cln">:</td>
                        <td class="val">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                {{ implode(', ', $anggota->keaktifan_ortom) }}
                                @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                    ({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Organisasi Lainnya</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->organisasi_lain ?: '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- 6. BAGIAN III: PROFIL KEAGAMAAN, KEMASJIDAN & KEMASYARAKATAN --}}
    <div class="sec-title"><span>III.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
    <table class="grid-split">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Baca Qur'an &amp; Tajwid</td><td class="cln">:</td>
                        <td class="val"><b>{{ $anggota->kemampuan_baca_quran ?: '-' }}</b></td>
                    </tr>
                    <tr>
                        <td class="lbl">Hafalan Al-Qur'an</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->hafalan_quran ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Sholat 5 Waktu Masjid</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->aktivitas_sholat_masjid ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Kajian Rutin Agama</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->aktivitas_kajian_agama ?: '-' }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="lbl">Penyaluran ZIS</td><td class="cln">:</td>
                        <td class="val">
                            @if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis))
                                {{ implode(', ', $anggota->lembaga_zis) }}
                            @else
                                {{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '-' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Jamaah / Takmir Masjid</td><td class="cln">:</td>
                        <td class="val">
                            {{ $anggota->aktifitas_jamaah_masjid ?: '-' }}
                            @if($anggota->keaktifan_takmir) &bull; Takmir: {{ $anggota->keaktifan_takmir }} @if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }}) @endif @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="lbl">Peran Kemasyarakatan</td><td class="cln">:</td>
                        <td class="val">{{ $anggota->keaktifan_kemasyarakatan ?: '-' }}</td>
                    </tr>
                    @php
                        $riwayatEvent = $anggota->eventPeserta()->with('event')->get();
                        $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
                    @endphp
                    @if($riwayatEvent->isNotEmpty() || !empty($customData))
                    <tr>
                        <td class="lbl">Riwayat BA / Khusus</td><td class="cln">:</td>
                        <td class="val">
                            @if($riwayatEvent->isNotEmpty())
                                @foreach($riwayatEvent as $rep)
                                    <b>{{ $rep->event->nama_event }}</b>@if($rep->status_kelulusan) ({{ $rep->status_kelulusan }})@endif;
                                @endforeach
                            @endif
                            @if(!empty($customData))
                                @foreach($customData as $k => $v)
                                    @php $vStr = is_array($v) ? implode(', ', $v) : (string)$v; @endphp
                                    @if(trim($vStr) !== '') {{ ucwords(str_replace('_', ' ', $k)) }}: {{ $vStr }}; @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- 7. LEMBAR PENGESAHAN DOKUMEN (FIT PADA SATU LEMBAR) --}}
    <table class="sign-table">
        <tr>
            <td style="width: 48%;">
                <div class="sign-date">&nbsp;</div>
                <div class="sign-role-title">Kader Yang Bersangkutan,</div>
                <div class="sign-name">{{ $anggota->nama_lengkap }}</div>
                <div class="sign-nbm">NBM: {{ $anggota->nbm ?: 'Kader Muhammadiyah Karanganyar' }}</div>
            </td>
            <td style="width: 52%;">
                <div class="sign-date">Karanganyar, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="sign-role-title">
                    Pimpinan Daerah Muhammadiyah Karanganyar<br>
                    Ketua MPKSDI,
                </div>
                <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                <div class="sign-nbm">NBM: 864.219 &bull; PDM Karanganyar</div>
            </td>
        </tr>
    </table>

    {{-- 8. FOOTNOTE ARSIP SAH --}}
    <div class="footer-note">
        <b>Catatan Pengesahan:</b> Dokumen Biodata Resmi ini dicetak sah dari Sistem Informasi Pangkalan Data MPKSDI PDM Karanganyar. Keaslian data dibuktikan dengan memindai kode QR resmi di atas.
    </div>

</body>
</html>
