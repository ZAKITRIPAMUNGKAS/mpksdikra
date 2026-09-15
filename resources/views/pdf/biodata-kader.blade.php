<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 7mm 9mm 6mm 9mm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 6.8pt;
            color: #1e293b;
            line-height: 1.28;
            background: #fff;
        }

        /* ─── WATERMARK (fixed, z-index negatif, DomPDF aman) ──── */
        .wm {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
            text-align: center;
        }
        .wm img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 180pt;
            height: 180pt;
            margin-top: -90pt;
            margin-left: -90pt;
            opacity: 0.04;
        }

        /* ─── KOP SURAT ─────────────────────────────────────────── */
        .kop {
            width: 100%;
            border-collapse: collapse;
        }
        .kop td { vertical-align: middle; border: none; padding: 0; }

        .kop-logo-cell {
            width: 40pt;
            text-align: center;
        }
        .kop-logo-cell img { height: 32pt; width: auto; display: block; margin: 0 auto; }

        .kop-batik-cell {
            width: 52pt;
            text-align: right;
            vertical-align: top;
            padding-top: 0;
        }
        .kop-batik-cell img {
            width: 52pt;
            height: 52pt;
            opacity: 0.55;
            display: block;
            margin-left: auto;
        }

        .kop-center { text-align: center; padding: 0 6pt; }
        .kop-org1 {
            font-size: 6pt;
            font-weight: bold;
            color: #475569;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-org2 {
            font-size: 10pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            line-height: 1.1;
            margin-top: 1.5pt;
        }
        .kop-addr {
            font-size: 5pt;
            color: #94a3b8;
            margin-top: 2pt;
        }

        /* ─── DIVIDER GANDA ─────────────────────────────────────── */
        .divider {
            border-top: 1.8pt solid #002b40;
            border-bottom: 0.5pt solid #c9a800;
            height: 1pt;
            margin: 2.5pt 0 4pt;
        }

        /* ─── DOC BAR ───────────────────────────────────────────── */
        .doc-bar {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 0.5pt solid #e2e8f0;
            margin-bottom: 4pt;
        }
        .doc-bar td { padding: 1.8pt 5pt; vertical-align: middle; border: none; }
        .doc-title {
            font-size: 7.2pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .doc-meta {
            font-size: 4.8pt;
            color: #94a3b8;
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        /* ─── LAYOUT UTAMA ──────────────────────────────────────── */
        .layout {
            width: 100%;
            border-collapse: collapse;
        }
        .layout > tbody > tr > td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .col-left { width: 158pt; padding-right: 5pt; }
        .col-right { padding-left: 4pt; }

        /* ─── SIDEBAR KIRI ──────────────────────────────────────── */
        .sidebar {
            background: #f8fafc;
            border: 0.5pt solid #dde3ea;
            padding: 6pt 6pt 5pt;
        }

        .foto-wrap { text-align: center; margin-bottom: 3pt; }
        .foto-frame {
            display: inline-block;
            width: 50pt;
            height: 65pt;
            border: 0.8pt solid #cbd5e1;
            background: #f1f5f9;
            overflow: hidden;
            text-align: center;
        }
        .foto-frame img { width: 50pt; height: 65pt; object-fit: cover; }
        .foto-ph { font-size: 5pt; color: #94a3b8; line-height: 65pt; font-weight: bold; }

        .s-name {
            font-size: 7.5pt;
            font-weight: 900;
            color: #002b40;
            text-align: center;
            line-height: 1.15;
            margin-bottom: 1pt;
        }
        .s-nik {
            font-size: 5.5pt;
            color: #64748b;
            text-align: center;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-bottom: 2pt;
        }
        .s-badge-wrap { text-align: center; margin-bottom: 4pt; }
        .s-badge {
            display: inline-block;
            padding: 1pt 5pt;
            font-size: 5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-aktif    { background: #dbeafe; color: #1d4ed8; border: 0.5pt solid #bfdbfe; }
        .badge-nonaktif { background: #fee2e2; color: #b91c1c; border: 0.5pt solid #fecaca; }
        .badge-pending  { background: #fef9c3; color: #92400e; border: 0.5pt solid #fde68a; }

        .s-div { border-top: 0.4pt solid #e2e8f0; margin: 3pt 0 2pt; }
        .s-head {
            font-size: 5.2pt;
            font-weight: 800;
            color: #005c8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2pt;
        }
        .s-item { margin-bottom: 2pt; }
        .s-lbl { font-size: 4.8pt; color: #94a3b8; text-transform: uppercase; font-weight: 600; display: block; }
        .s-val { font-size: 5.8pt; color: #0f172a; font-weight: 600; word-wrap: break-word; display: block; }

        .qr-box { text-align: center; margin-top: 4pt; padding-top: 3pt; border-top: 0.4pt dashed #cbd5e1; }
        .qr-box img { width: 44pt; height: 44pt; border: 0.5pt solid #cbd5e1; padding: 1pt; background: #fff; display: block; margin: 0 auto; }
        .qr-lbl { font-size: 4.2pt; color: #94a3b8; text-transform: uppercase; margin-top: 1.5pt; line-height: 1.2; }

        /* ─── PANEL KANAN ───────────────────────────────────────── */
        .r-sec {
            font-size: 6.3pt;
            font-weight: 900;
            color: #ffffff;
            background: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 2pt 5pt;
            margin-bottom: 3pt;
        }
        .r-sec span { color: #f8bb00; margin-right: 2pt; }
        .r-sec-sub { margin-top: 4pt; }

        .dt {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5pt;
        }
        .dt td {
            vertical-align: top;
            padding: 1pt 2pt;
            border-bottom: 0.3pt solid #f1f5f9;
            font-size: 6.5pt;
            line-height: 1.22;
        }
        .dt tr:last-child td { border-bottom: none; }
        .dl { width: 98pt; color: #64748b; font-weight: 600; }
        .dc { width: 5pt; text-align: center; color: #94a3b8; }
        .dv { color: #0f172a; }

        .split { width: 100%; border-collapse: collapse; margin-bottom: 1.5pt; }
        .split > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; border: none; }
        .split > tbody > tr > td:first-child { padding-right: 3pt; }
        .split > tbody > tr > td:last-child  { padding-left: 3pt; }

        .tag {
            display: inline-block;
            padding: 0.4pt 3pt;
            font-size: 5.5pt;
            font-weight: 600;
            background: #f1f5f9;
            border: 0.4pt solid #cbd5e1;
            color: #334155;
            margin: 0.4pt;
        }
        .tag-sky { background: #e0f2fe; border-color: #bae6fd; color: #0369a1; }

        .sign { width: 100%; border-collapse: collapse; margin-top: 4pt; padding-top: 3pt; border-top: 0.6pt solid #e2e8f0; }
        .sign td { vertical-align: top; text-align: center; border: none; padding: 0 4pt; }
        .sign-date { font-size: 5.5pt; color: #64748b; margin-bottom: 1pt; }
        .sign-role { font-size: 5.5pt; font-weight: bold; color: #002b40; text-transform: uppercase; line-height: 1.2; margin-bottom: 18pt; }
        .sign-name { font-size: 6.5pt; font-weight: bold; color: #0f172a; text-decoration: underline; }
        .sign-nbm  { font-size: 5pt; color: #64748b; margin-top: 0.5pt; }

        .footer {
            margin-top: 2pt;
            border-top: 0.4pt dashed #cbd5e1;
            padding-top: 1.5pt;
            font-size: 4.3pt;
            color: #94a3b8;
            text-align: center;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    {{-- Watermark ultra-subtle --}}
    <div class="wm">
        <img src="{{ public_path('aset/logo-mpksdi.png') }}" alt="">
    </div>

    {{-- ═══ KOP SURAT ═══
         Batik SVG ditempatkan sebagai sel di pojok kanan kop --}}
    <table class="kop">
        <tr>
            <td class="kop-logo-cell">
                <img src="{{ public_path('aset/logo-mpksdi.png') }}" alt="MPKSDI">
            </td>
            <td class="kop-center">
                <div class="kop-org1">Pimpinan Daerah Muhammadiyah Kabupaten Karanganyar</div>
                <div class="kop-org2">Majelis Pembinaan Kader &amp; Sumber Daya Insani</div>
                <div class="kop-addr">Gedung Dakwah Muhammadiyah &bull; Jl. Lawu, Papahan, Tasikmadu, Kab. Karanganyar 57722 &bull; mpksdikra.muhammadiyah.or.id</div>
            </td>
            <td class="kop-logo-cell">
                <img src="{{ public_path('aset/logo-muhammadiyah.png') }}" alt="Muhammadiyah">
            </td>
            <td class="kop-batik-cell">
                <img src="{{ public_path('aset/batik-web-corner.svg') }}" alt="">
            </td>
        </tr>
    </table>
    <div class="divider"></div>

    {{-- ═══ DOC BAR ═══ --}}
    <table class="doc-bar">
        <tr>
            <td class="doc-title">Curriculum Vitae &amp; Biodata Resmi Kader Muhammadiyah</td>
            <td class="doc-meta">
                No. {{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/MPKSDI-KRA/{{ now()->format('Y') }}
                &bull; Cetak: {{ now()->translatedFormat('d M Y') }}
            </td>
        </tr>
    </table>

    {{-- ═══ LAYOUT UTAMA ═══ --}}
    <table class="layout">
        <tr>
            {{-- ─── SIDEBAR KIRI ─── --}}
            <td class="col-left">
                <div class="sidebar">

                    <div class="foto-wrap">
                        <div class="foto-frame">
                            @if(!empty($fotoDataUri))
                                <img src="{{ $fotoDataUri }}" alt="Foto">
                            @else
                                <div class="foto-ph">FOTO<br>3×4</div>
                            @endif
                        </div>
                    </div>

                    <div class="s-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                    <div class="s-nik">NIK: {{ $anggota->nik ?: '—' }}</div>
                    <div class="s-badge-wrap">
                        @php
                            $st = strtolower($anggota->user->status ?? 'pending');
                            $bc = match($st) {
                                'aktif'    => 'badge-aktif',
                                'nonaktif' => 'badge-nonaktif',
                                default    => 'badge-pending',
                            };
                        @endphp
                        <span class="s-badge {{ $bc }}">Kader {{ ucfirst($st) }}</span>
                    </div>

                    <div class="s-div"></div>
                    <div class="s-head">Data Pribadi</div>
                    <div class="s-item">
                        <span class="s-lbl">NBM</span>
                        <span class="s-val">{{ $anggota->nbm ?: 'Belum Memiliki' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">TTL</span>
                        <span class="s-val">{{ $anggota->tempat_lahir ?: '—' }}, {{ $anggota->tanggal_lahir ? $anggota->tanggal_lahir->translatedFormat('d M Y') : '—' }}@if($anggota->tanggal_lahir) ({{ $anggota->tanggal_lahir->age }} Thn)@elseif($anggota->umur) ({{ $anggota->umur }} Thn)@endif</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Gender / Status Nikah</span>
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
                    <div class="s-head">Kontak &amp; Domisili</div>
                    <div class="s-item">
                        <span class="s-lbl">WhatsApp / HP</span>
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
                        <span class="s-val">{{ implode(', ', array_filter([$anggota->desa_kelurahan, $anggota->kecamatan, $anggota->kabupaten ?: 'Kab. Karanganyar'])) }}</span>
                    </div>

                    <div class="s-div"></div>
                    <div class="s-head">Pendidikan &amp; Bahasa</div>
                    <div class="s-item">
                        <span class="s-lbl">Pendidikan Terakhir</span>
                        <span class="s-val">{{ $anggota->pendidikan_terakhir ?: '—' }}@if($anggota->pendidikan_s1) — {{ $anggota->pendidikan_s1 }}@elseif($anggota->pendidikan_sma) — {{ $anggota->pendidikan_sma }}@endif</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Bahasa Dikuasai</span>
                        <span class="s-val">@if(!empty($anggota->bahasa_dikuasai) && is_array($anggota->bahasa_dikuasai)){{ implode(', ', $anggota->bahasa_dikuasai) }}@else{{ is_string($anggota->bahasa_dikuasai) ? $anggota->bahasa_dikuasai : 'Indonesia' }}@endif</span>
                    </div>

                    <div class="qr-box">
                        <img src="{{ $qrPng }}" alt="QR">
                        <div class="qr-lbl">Pindai untuk<br>Verifikasi Dokumen</div>
                    </div>

                </div>
            </td>

            {{-- ─── PANEL KANAN ─── --}}
            <td class="col-right">

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
                                @foreach($anggota->keaktifan_muhammadiyah as $km)<span class="tag tag-sky">{{ $km }}</span>@endforeach
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Otonom</td><td class="dc">:</td>
                        <td class="dv">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                @foreach($anggota->keaktifan_ortom as $ko)<span class="tag">{{ $ko }}</span>@endforeach
                                @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                    <span style="color:#64748b;font-size:5.5pt;">({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})</span>
                                @endif
                            @else —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Lain</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->organisasi_lain ?: '—' }}</td>
                    </tr>
                </table>

                <div class="r-sec r-sec-sub"><span>II.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
                <table class="split">
                    <tr>
                        <td>
                            <table class="dt">
                                <tr>
                                    <td class="dl" style="width:68pt;">Baca Al-Qur'an</td><td class="dc">:</td>
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
                                    <td class="dl" style="width:60pt;">Penyaluran ZIS</td><td class="dc">:</td>
                                    <td class="dv">@if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis)){{ implode(', ', $anggota->lembaga_zis) }}@else{{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '—' }}@endif</td>
                                </tr>
                                <tr>
                                    <td class="dl">Masjid Binaan</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->aktifitas_jamaah_masjid ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="dl">Pengurus Takmir</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->keaktifan_takmir ?: 'Tidak' }}@if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }})@endif</td>
                                </tr>
                                <tr>
                                    <td class="dl">Kemasyarakatan</td><td class="dc">:</td>
                                    <td class="dv">{{ $anggota->keaktifan_kemasyarakatan ?: '—' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                @php
                    $riwayat    = $anggota->eventPeserta()->with('event')->get();
                    $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
                @endphp
                <div class="r-sec r-sec-sub"><span>III.</span> Rekam Jejak Perkaderan &amp; Keterangan Tambahan</div>
                <table class="dt">
                    <tr>
                        <td class="dl" style="width:98pt;">Riwayat Baitul Arqam</td><td class="dc">:</td>
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
                    @foreach($customData as $k => $v)
                        @php
                            $labelK = ucwords(str_replace('_', ' ', $k));
                            $pertObj = \App\Models\FormPertanyaan::where('nama_field', $k)->first();
                            if ($pertObj) $labelK = $pertObj->pertanyaan;
                            $vStr = is_array($v) ? implode(', ', $v) : (string) $v;
                        @endphp
                        @if(trim($vStr) !== '')
                        <tr>
                            <td class="dl">{{ $labelK }}</td><td class="dc">:</td>
                            <td class="dv"><b>{{ $vStr }}</b></td>
                        </tr>
                        @endif
                    @endforeach
                </table>

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

                <div class="footer">
                    Dokumen ini diterbitkan secara resmi melalui Pangkalan Data Terpadu MPKSDI PDM Kabupaten Karanganyar
                    &bull; Keabsahan dokumen dapat diverifikasi melalui pemindaian QR Code
                    &bull; Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>

            </td>
        </tr>
    </table>

</body>
</html>
