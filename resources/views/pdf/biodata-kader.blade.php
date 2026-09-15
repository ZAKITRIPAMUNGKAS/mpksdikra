<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 8mm 5mm 8mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 6.8pt;
            color: #1e293b;
            line-height: 1.28;
            background: #ffffff;
        }

        /* ─── WATERMARK ─────────────────────────────────────── */
        .wm {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 200px;
            height: 200px;
            margin-top: -100px;
            margin-left: -100px;
            z-index: -100;
            opacity: 0.045;
        }
        .wm img { width: 200px; height: auto; }

        /* ─── BATIK CORNER TOP-RIGHT ─────────────────────────── */
        .batik-tr {
            position: fixed;
            top: 0;
            right: 0;
            width: 110px;
            height: 110px;
            z-index: -1;
            opacity: 0.55;
        }
        .batik-tr img { width: 110px; height: auto; }

        /* ─── BATIK CORNER BOTTOM-LEFT ───────────────────────── */
        .batik-bl {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 90px;
            height: 90px;
            z-index: -1;
            opacity: 0.40;
            transform: rotate(180deg);
        }
        .batik-bl img { width: 90px; height: auto; }

        /* ─── KOP SURAT ──────────────────────────────────────── */
        .kop {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .kop td { vertical-align: middle; border: none; padding: 0; }
        .kop-logo { width: 42px; text-align: center; }
        .kop-logo img { height: 34px; width: auto; }
        .kop-body { text-align: center; padding: 0 10px; }
        .kop-lembaga {
            font-size: 6.5pt;
            font-weight: bold;
            color: #475569;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-judul {
            font-size: 10.5pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            line-height: 1.1;
            margin-top: 1px;
        }
        .kop-alamat {
            font-size: 5.5pt;
            color: #94a3b8;
            margin-top: 1.5px;
            line-height: 1.2;
        }

        /* ─── DIVIDER GANDA ──────────────────────────────────── */
        .divider-double {
            border-top: 1.8px solid #002b40;
            border-bottom: 0.6px solid #c9a800;
            height: 1px;
            margin: 3px 0 4px;
        }

        /* ─── DOC BAR ────────────────────────────────────────── */
        .doc-bar {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 0.6px solid #e2e8f0;
            border-radius: 2px;
            margin-bottom: 5px;
        }
        .doc-bar td { padding: 2px 5px; vertical-align: middle; border: none; }
        .doc-title {
            font-size: 7.8pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .doc-meta {
            font-size: 5.2pt;
            color: #94a3b8;
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        /* ─── LAYOUT UTAMA ───────────────────────────────────── */
        .layout {
            width: 100%;
            border-collapse: collapse;
        }
        .layout > tbody > tr > td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .col-left { width: 166px; padding-right: 5px; }
        .col-right { padding-left: 5px; }

        /* ─── SIDEBAR KIRI ───────────────────────────────────── */
        .sidebar {
            background: #f8fafc;
            border: 0.6px solid #dde3ea;
            border-radius: 3px;
            padding: 7px 7px 6px;
        }

        /* Foto */
        .foto-wrap { text-align: center; margin-bottom: 4px; }
        .foto-frame {
            display: inline-block;
            width: 56px;
            height: 72px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            border-radius: 2px;
            overflow: hidden;
            text-align: center;
        }
        .foto-frame img { width: 56px; height: 72px; object-fit: cover; }
        .foto-ph {
            font-size: 5.5pt;
            color: #94a3b8;
            line-height: 72px;
            font-weight: bold;
        }

        /* Nama & Badge */
        .s-name {
            font-size: 8pt;
            font-weight: 900;
            color: #002b40;
            text-align: center;
            line-height: 1.15;
            margin-bottom: 1.5px;
        }
        .s-nik {
            font-size: 5.8pt;
            color: #64748b;
            text-align: center;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-bottom: 2px;
        }
        .s-badge-wrap { text-align: center; margin-bottom: 5px; }
        .s-badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 5.2pt;
            font-weight: bold;
            border-radius: 1px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-aktif  { background: #dbeafe; color: #1d4ed8; border: 0.5px solid #bfdbfe; }
        .badge-nonaktif { background: #fee2e2; color: #b91c1c; border: 0.5px solid #fecaca; }
        .badge-pending  { background: #fef9c3; color: #92400e; border: 0.5px solid #fde68a; }

        /* Divider Sidebar */
        .s-div { border-bottom: 0.5px solid #e2e8f0; margin: 3px 0; }

        /* Section heading sidebar */
        .s-head {
            font-size: 5.5pt;
            font-weight: 800;
            color: #005c8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        /* Item list sidebar */
        .s-item { margin-bottom: 2px; line-height: 1.2; }
        .s-lbl {
            font-size: 5.2pt;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            letter-spacing: 0.1px;
        }
        .s-val {
            font-size: 6.2pt;
            color: #0f172a;
            font-weight: 600;
            word-wrap: break-word;
        }

        /* QR Box */
        .qr-box {
            text-align: center;
            margin-top: 5px;
            padding-top: 4px;
            border-top: 0.5px dashed #cbd5e1;
        }
        .qr-box img { width: 44px; height: 44px; border: 0.5px solid #cbd5e1; padding: 1px; background: #fff; }
        .qr-lbl { font-size: 4.5pt; color: #94a3b8; text-transform: uppercase; margin-top: 1px; line-height: 1.1; letter-spacing: 0.2px; }

        /* ─── PANEL KANAN ────────────────────────────────────── */
        /* Section header */
        .r-sec {
            font-size: 6.8pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #005c8a;
            padding-bottom: 1px;
            margin-bottom: 3px;
        }
        .r-sec + .r-sec { margin-top: 4px; }
        .r-sec span { color: #c9a800; margin-right: 2px; }

        /* Tabel data 3 kolom */
        .dt {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2.5px;
        }
        .dt td {
            vertical-align: top;
            padding: 1.2px 2px;
            border-bottom: 0.4px solid #f1f5f9;
            font-size: 6.5pt;
            line-height: 1.22;
        }
        .dt tr:last-child td { border-bottom: none; }
        .dl { width: 108px; color: #64748b; font-weight: 600; }
        .dc { width: 5px; text-align: center; color: #94a3b8; }
        .dv { color: #0f172a; }

        /* Split 2 kolom di panel kanan */
        .split {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .split > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .split > tbody > tr > td:first-child { padding-right: 3px; }
        .split > tbody > tr > td:last-child  { padding-left: 3px; }

        /* Tag pill */
        .tag {
            display: inline-block;
            padding: 0.4px 3px;
            font-size: 5.8pt;
            font-weight: 600;
            border-radius: 1px;
            background: #f1f5f9;
            border: 0.4px solid #cbd5e1;
            color: #334155;
            margin: 0.4px 0.4px;
        }
        .tag-blue { background: #dbeafe; border-color: #bfdbfe; color: #1e40af; }
        .tag-sky  { background: #e0f2fe; border-color: #bae6fd; color: #0369a1; }

        /* Section margin */
        .mt { margin-top: 4px; }

        /* ─── PENGESAHAN ─────────────────────────────────────── */
        .sign {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 0.7px solid #e2e8f0;
        }
        .sign td {
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 4px;
        }
        .sign-date { font-size: 5.8pt; color: #64748b; margin-bottom: 1px; }
        .sign-role {
            font-size: 6pt;
            font-weight: bold;
            color: #002b40;
            text-transform: uppercase;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        .sign-name {
            font-size: 7pt;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .sign-nbm { font-size: 5.3pt; color: #64748b; margin-top: 0.5px; }

        /* ─── CATATAN KAKI ───────────────────────────────────── */
        .footer {
            margin-top: 2.5px;
            border-top: 0.4px dashed #cbd5e1;
            padding-top: 1.5px;
            font-size: 4.8pt;
            color: #94a3b8;
            text-align: center;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    {{-- Watermark ultra-subtle tengah halaman --}}
    <div class="wm">
        <img src="{{ public_path('aset/logo-mpksdi.png') }}" alt="">
    </div>

    {{-- Pattern batik pojok kanan atas --}}
    <div class="batik-tr">
        <img src="{{ public_path('aset/batik-web-corner.svg') }}" alt="">
    </div>

    {{-- Pattern batik pojok kiri bawah (rotasi 180°) --}}
    <div class="batik-bl">
        <img src="{{ public_path('aset/batik-web-corner.svg') }}" alt="">
    </div>

    {{-- ═══════════════════════════════════════════════════════
         KOP SURAT RESMI
    ════════════════════════════════════════════════════════ --}}
    <table class="kop">
        <tr>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-mpksdi.png') }}" alt="MPKSDI">
            </td>
            <td class="kop-body">
                <div class="kop-lembaga">Pimpinan Daerah Muhammadiyah Kabupaten Karanganyar</div>
                <div class="kop-judul">Majelis Pembinaan Kader &amp; Sumber Daya Insani</div>
                <div class="kop-alamat">Gedung Dakwah Muhammadiyah &bull; Jl. Lawu, Papahan, Tasikmadu, Kab. Karanganyar 57722 &bull; mpksdikra.muhammadiyah.or.id</div>
            </td>
            <td class="kop-logo">
                <img src="{{ public_path('aset/logo-muhammadiyah.png') }}" alt="Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="divider-double"></div>

    {{-- ═══════════════════════════════════════════════════════
         DOC BAR – Judul & Meta
    ════════════════════════════════════════════════════════ --}}
    <table class="doc-bar">
        <tr>
            <td class="doc-title">Curriculum Vitae &amp; Biodata Resmi Kader Muhammadiyah</td>
            <td class="doc-meta">
                No. {{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/MPKSDI-KRA/{{ now()->format('Y') }}
                &bull; Cetak: {{ now()->translatedFormat('d M Y') }}
            </td>
        </tr>
    </table>

    {{-- ═══════════════════════════════════════════════════════
         LAYOUT UTAMA — 2 KOLOM
    ════════════════════════════════════════════════════════ --}}
    <table class="layout">
        <tr>
            {{-- ─── KOLOM KIRI: SIDEBAR ─────────────────────── --}}
            <td class="col-left">
                <div class="sidebar">

                    {{-- Pasfoto --}}
                    <div class="foto-wrap">
                        <div class="foto-frame">
                            @if(!empty($fotoDataUri))
                                <img src="{{ $fotoDataUri }}" alt="Foto">
                            @else
                                <div class="foto-ph">FOTO<br>3×4</div>
                            @endif
                        </div>
                    </div>

                    {{-- Nama & NIK & Badge --}}
                    <div class="s-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                    <div class="s-nik">NIK: {{ $anggota->nik ?: '—' }}</div>
                    <div class="s-badge-wrap">
                        @php
                            $status = strtolower($anggota->user->status ?? 'pending');
                            $badgeClass = match($status) {
                                'aktif'    => 'badge-aktif',
                                'nonaktif' => 'badge-nonaktif',
                                default    => 'badge-pending',
                            };
                        @endphp
                        <span class="s-badge {{ $badgeClass }}">Kader {{ ucfirst($status) }}</span>
                    </div>

                    <div class="s-div"></div>

                    {{-- Data Pribadi --}}
                    <div class="s-head">Data Pribadi</div>
                    <div class="s-item">
                        <span class="s-lbl">NBM</span>
                        <span class="s-val">{{ $anggota->nbm ?: 'Belum Memiliki' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Tempat / Tanggal Lahir</span>
                        <span class="s-val">{{ $anggota->tempat_lahir ?: '—' }}, {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->translatedFormat('d M Y') : '—' }}
                        @if($anggota->tanggal_lahir) ({{ $anggota->tanggal_lahir->age }} Thn) @elseif($anggota->umur) ({{ $anggota->umur }} Thn) @endif</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Jenis Kelamin / Nikah</span>
                        <span class="s-val">{{ $anggota->jenis_kelamin ?: '—' }} &bull; {{ $anggota->status_pernikahan ?: '—' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Nama Ayah Kandung</span>
                        <span class="s-val">{{ $anggota->nama_ayah ?: '—' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Nama Ibu Kandung</span>
                        <span class="s-val">{{ $anggota->nama_ibu ?: '—' }}</span>
                    </div>

                    <div class="s-div"></div>

                    {{-- Kontak & Domisili --}}
                    <div class="s-head">Kontak &amp; Domisili</div>
                    <div class="s-item">
                        <span class="s-lbl">WhatsApp / No. HP</span>
                        <span class="s-val">{{ $anggota->no_wa ?: '—' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Email</span>
                        <span class="s-val">{{ $anggota->email ?: '—' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Alamat (KTP)</span>
                        <span class="s-val">{{ $anggota->alamat_rumah ?: '—' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Kel. / Kec. / Kab.</span>
                        <span class="s-val">{{ $anggota->desa_kelurahan ?: '—' }}, {{ $anggota->kecamatan ?: '—' }}, {{ $anggota->kabupaten ?: 'Kab. Karanganyar' }}</span>
                    </div>

                    <div class="s-div"></div>

                    {{-- Pendidikan --}}
                    <div class="s-head">Pendidikan &amp; Bahasa</div>
                    <div class="s-item">
                        <span class="s-lbl">Pendidikan Terakhir</span>
                        <span class="s-val">{{ $anggota->pendidikan_terakhir ?: '—' }}
                        @if($anggota->pendidikan_s1) — {{ $anggota->pendidikan_s1 }} @elseif($anggota->pendidikan_sma) — {{ $anggota->pendidikan_sma }} @endif</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Bahasa Dikuasai</span>
                        <span class="s-val">
                            @if(!empty($anggota->bahasa_dikuasai) && is_array($anggota->bahasa_dikuasai))
                                {{ implode(', ', $anggota->bahasa_dikuasai) }}
                            @else
                                {{ is_string($anggota->bahasa_dikuasai) ? $anggota->bahasa_dikuasai : 'Indonesia' }}
                            @endif
                        </span>
                    </div>

                    {{-- QR Verifikasi --}}
                    <div class="qr-box">
                        <img src="{{ $qrPng }}" alt="QR Verifikasi">
                        <div class="qr-lbl">Pindai untuk<br>Verifikasi Dokumen</div>
                    </div>

                </div>
            </td>

            {{-- ─── KOLOM KANAN: KONTEN UTAMA ───────────────── --}}
            <td class="col-right">

                {{-- ▸ SEKSI I: AMANAH & PROFESI --}}
                <div class="r-sec"><span>I.</span> Amanah Persyarikatan, Profesi &amp; Organisasi</div>
                <table class="dt">
                    <tr>
                        <td class="dl">Profesi / Pekerjaan</td><td class="dc">:</td>
                        <td class="dv"><b>{{ $anggota->pekerjaan ?: '—' }}</b></td>
                    </tr>
                    <tr>
                        <td class="dl">Amanah di AUM</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->jabatan_aum ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="dl">PCM (Cabang)</td><td class="dc">:</td>
                        <td class="dv"><b>{{ $anggota->pcm ?: ($anggota->kecamatan ?: '—') }}</b></td>
                    </tr>
                    <tr>
                        <td class="dl">PRM (Ranting)</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->prm ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="dl">Struktur Pimpinan</td><td class="dc">:</td>
                        <td class="dv">
                            @if(!empty($anggota->keaktifan_muhammadiyah) && is_array($anggota->keaktifan_muhammadiyah))
                                @foreach($anggota->keaktifan_muhammadiyah as $km)
                                    <span class="tag tag-sky">{{ $km }}</span>
                                @endforeach
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Otonom</td><td class="dc">:</td>
                        <td class="dv">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                @foreach($anggota->keaktifan_ortom as $ko)
                                    <span class="tag">{{ $ko }}</span>
                                @endforeach
                                @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                    <span style="color:#64748b; font-size:5.8pt;">({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Lain</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->organisasi_lain ?: '—' }}</td>
                    </tr>
                </table>

                {{-- ▸ SEKSI II: KEAGAMAAN & KEMASJIDAN --}}
                <div class="r-sec mt"><span>II.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
                <table class="split">
                    <tr>
                        <td>
                            <table class="dt">
                                <tr>
                                    <td class="dl" style="width:82px;">Baca Al-Qur'an</td><td class="dc">:</td>
                                    <td class="dv"><b>{{ $anggota->kemampuan_baca_quran ?: '—' }}</b></td>
                                </tr>
                                <tr>
                                    <td class="dl">Hafalan</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->hafalan_quran ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="dl">Sholat Masjid</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->aktivitas_sholat_masjid ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="dl">Kajian Rutin</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->aktivitas_kajian_agama ?: '—' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <table class="dt">
                                <tr>
                                    <td class="dl" style="width:72px;">Penyaluran ZIS</td><td class="dc">:</td>
                                    <td class="dv">
                                        @if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis))
                                            {{ implode(', ', $anggota->lembaga_zis) }}
                                        @else
                                            {{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '—' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="dl">Masjid Binaan</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->aktifitas_jamaah_masjid ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="dl">Pengurus Takmir</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->keaktifan_takmir ?: 'Tidak' }}@if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }}) @endif</td>
                                </tr>
                                <tr>
                                    <td class="dl">Kemasyarakatan</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->keaktifan_kemasyarakatan ?: '—' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- ▸ SEKSI III: REKAM JEJAK & KETERANGAN --}}
                @php
                    $riwayat    = $anggota->eventPeserta()->with('event')->get();
                    $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
                @endphp
                <div class="r-sec mt"><span>III.</span> Rekam Jejak Perkaderan &amp; Keterangan Tambahan</div>
                <table class="dt">
                    <tr>
                        <td class="dl" style="width:108px;">Riwayat Baitul Arqam</td><td class="dc">:</td>
                        <td class="dv">
                            @if($riwayat->isNotEmpty())
                                @foreach($riwayat as $rep)
                                    <b>{{ $rep->event->nama_event }}</b>@if($rep->status_kelulusan) <span class="tag tag-sky">{{ $rep->status_kelulusan }}</span>@endif{{ !$loop->last ? '; ' : '' }}
                                @endforeach
                            @else
                                <span style="color:#94a3b8;">Belum tercatat riwayat pelatihan Baitul Arqam</span>
                            @endif
                        </td>
                    </tr>
                    @if(!empty($customData))
                        @foreach($customData as $k => $v)
                            @php
                                $labelK = ucwords(str_replace('_', ' ', $k));
                                $pertanyaanObj = \App\Models\FormPertanyaan::where('nama_field', $k)->first();
                                if ($pertanyaanObj) { $labelK = $pertanyaanObj->pertanyaan; }
                                $vStr = is_array($v) ? implode(', ', $v) : (string) $v;
                            @endphp
                            @if(trim($vStr) !== '')
                            <tr>
                                <td class="dl">{{ $labelK }}</td><td class="dc">:</td>
                                <td class="dv"><b>{{ $vStr }}</b></td>
                            </tr>
                            @endif
                        @endforeach
                    @endif
                </table>

                {{-- ▸ SEKSI IV: LEMBAR PENGESAHAN --}}
                <table class="sign">
                    <tr>
                        <td style="width:46%;">
                            <div class="sign-date">&nbsp;</div>
                            <div class="sign-role">Kader yang Bersangkutan,</div>
                            <div class="sign-name">{{ $anggota->nama_lengkap }}</div>
                            <div class="sign-nbm">NBM: {{ $anggota->nbm ?: 'Kader Muhammadiyah' }}</div>
                        </td>
                        <td style="width:54%;">
                            <div class="sign-date">Karanganyar, {{ now()->translatedFormat('d F Y') }}</div>
                            <div class="sign-role">PDM Karanganyar &bull; MPKSDI<br>Ketua,</div>
                            <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                            <div class="sign-nbm">NBM: 864.219 &bull; PDM Kabupaten Karanganyar</div>
                        </td>
                    </tr>
                </table>

                {{-- Catatan Kaki --}}
                <div class="footer">
                    Dokumen ini diterbitkan secara resmi melalui Pangkalan Data Terpadu MPKSDI PDM Kabupaten Karanganyar &bull;
                    Keabsahan dokumen dapat diverifikasi melalui pemindaian QR Code &bull;
                    Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>

            </td>
        </tr>
    </table>

</body>
</html>
