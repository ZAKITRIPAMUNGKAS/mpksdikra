<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Biodata Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            color: #1f2937;
            line-height: 1.4;
            background: #fff;
        }

        /* ─── WATERMARK ─────────────────────────────────────────── */
        .wm {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1000;
            text-align: center;
        }
        .wm img {
            position: absolute;
            top: 50%; left: 50%;
            width: 170pt; height: 170pt;
            margin-top: -85pt; margin-left: -85pt;
            opacity: 0.05;
        }

        /* ─── KOP SURAT ─────────────────────────────────────────── */
        .kop { width: 100%; border-collapse: collapse; }
        .kop td { vertical-align: middle; border: none; padding: 0; }
        .kop-logo-cell { width: 52pt; text-align: center; }
        .kop-logo-cell img { height: 42pt; width: auto; display: block; margin: 0 auto; }
        .kop-center { text-align: center; padding: 0 8pt; }
        .kop-org1 {
            font-size: 8pt;
            font-weight: bold;
            color: #44546a;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .kop-org2 {
            font-size: 13pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            line-height: 1.15;
            margin-top: 2pt;
        }
        .kop-addr { font-size: 7pt; color: #6b7280; margin-top: 3pt; }

        /* ─── DIVIDER: garis navy + pita batik PNG ──────────────── */
        .divider-line { border-top: 2pt solid #002b40; margin-top: 4pt; }
        .divider-band img { width: 100%; height: 5pt; display: block; }

        /* ─── DOC BAR ───────────────────────────────────────────── */
        .doc-bar {
            width: 100%;
            border-collapse: collapse;
            margin: 6pt 0 8pt;
        }
        .doc-bar td { padding: 0; border: none; vertical-align: bottom; }
        .doc-title {
            font-size: 9.5pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .doc-meta {
            font-size: 7pt;
            color: #6b7280;
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        /* ─── LAYOUT UTAMA ──────────────────────────────────────── */
        .layout { width: 100%; border-collapse: collapse; }
        .layout > tbody > tr > td { vertical-align: top; border: none; padding: 0; }
        .col-left { width: 150pt; padding-right: 8pt; }
        .col-right { padding-left: 2pt; }

        /* ─── SIDEBAR KIRI ──────────────────────────────────────── */
        .sidebar {
            background: #f4f6f8;
            border: 0.6pt solid #d5dbe1;
            padding: 8pt;
        }
        .foto-wrap { text-align: center; margin-bottom: 6pt; }
        .foto-frame {
            display: inline-block;
            width: 62pt; height: 80pt;
            border: 0.8pt solid #b6c0ca;
            background: #fff;
            overflow: hidden;
            text-align: center;
        }
        .foto-frame img { width: 62pt; height: 80pt; }
        .foto-ph-table { width: 62pt; height: 80pt; }
        .foto-ph { font-size: 7pt; color: #94a3b8; font-weight: bold; }

        .s-name {
            font-size: 10pt;
            font-weight: 900;
            color: #002b40;
            text-align: center;
            line-height: 1.2;
            margin-bottom: 2pt;
        }
        .s-nik {
            font-size: 7pt;
            color: #6b7280;
            text-align: center;
            font-family: 'DejaVu Sans Mono', monospace;
            margin-bottom: 3pt;
        }
        .s-status {
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            padding: 1.5pt 0;
            margin-bottom: 6pt;
            border: 0.6pt solid;
        }
        .st-aktif    { color: #166534; border-color: #86efac; background: #f0fdf4; }
        .st-nonaktif { color: #991b1b; border-color: #fecaca; background: #fef2f2; }
        .st-pending  { color: #92400e; border-color: #fde68a; background: #fefce8; }

        .s-div { border-top: 0.6pt solid #d5dbe1; margin: 6pt 0 4pt; }
        .s-head {
            font-size: 7.5pt;
            font-weight: 800;
            color: #005c8a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4pt;
        }
        .s-item { margin-bottom: 4pt; }
        .s-lbl { font-size: 6.5pt; color: #6b7280; text-transform: uppercase; font-weight: 600; display: block; }
        .s-val { font-size: 8pt; color: #111827; display: block; }

        .qr-box { text-align: center; margin-top: 6pt; padding-top: 5pt; border-top: 0.6pt solid #d5dbe1; }
        .qr-box img { width: 52pt; height: 52pt; border: 0.6pt solid #b6c0ca; padding: 1.5pt; background: #fff; display: block; margin: 0 auto; }
        .qr-lbl { font-size: 6.5pt; color: #6b7280; margin-top: 2pt; line-height: 1.3; }

        /* ─── PANEL KANAN ───────────────────────────────────────── */
        .r-sec {
            font-size: 8.5pt;
            font-weight: 900;
            color: #ffffff;
            background: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 3pt 6pt;
            margin-bottom: 4pt;
        }
        .r-sec span { color: #f8bb00; margin-right: 3pt; }
        .r-sec-sub { margin-top: 8pt; }

        .dt { width: 100%; border-collapse: collapse; margin-bottom: 2pt; }
        .dt td {
            vertical-align: top;
            padding: 2.5pt 3pt;
            border-bottom: 0.4pt solid #e5e9ee;
            font-size: 8pt;
            line-height: 1.35;
        }
        .dt tr:last-child td { border-bottom: none; }
        .dl { color: #4b5563; font-weight: 600; }
        .dc { width: 7pt; text-align: center; color: #9ca3af; }
        .dv { color: #111827; }
        .dv-muted { color: #9ca3af; }

        /* ─── TANDA TANGAN ──────────────────────────────────────── */
        .sign { width: 100%; border-collapse: collapse; margin-top: 12pt; border-top: 0.8pt solid #d5dbe1; }
        .sign td { vertical-align: top; text-align: center; border: none; padding: 0 6pt; }
        .sign-date { font-size: 7.5pt; color: #4b5563; padding-top: 4pt; }
        .sign-role { font-size: 7.5pt; font-weight: bold; color: #002b40; text-transform: uppercase; line-height: 1.3; padding-top: 2pt; }
        .sign-space { height: 42pt; }
        .sign-name { font-size: 8.5pt; font-weight: bold; color: #111827; text-decoration: underline; }
        .sign-nbm  { font-size: 7pt; color: #6b7280; margin-top: 1pt; }

        .footer {
            margin-top: 8pt;
            border-top: 0.6pt solid #d5dbe1;
            padding-top: 3pt;
            font-size: 6.5pt;
            color: #6b7280;
            text-align: center;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="wm">
        <img src="{{ public_path('aset/batik-watermark.png') }}" alt="">
    </div>

    {{-- ═══ KOP SURAT ═══ --}}
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
        </tr>
    </table>
    <div class="divider-line"></div>
    <div class="divider-band"><img src="{{ public_path('aset/batik-border-band.png') }}" alt=""></div>

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
                                <table class="foto-ph-table"><tr><td style="vertical-align:middle;text-align:center;"><span class="foto-ph">FOTO<br>3&times;4</span></td></tr></table>
                            @endif
                        </div>
                    </div>

                    <div class="s-name">{{ strtoupper($anggota->nama_lengkap) }}</div>
                    <div class="s-nik">NIK: {{ $anggota->nik ?: '—' }}</div>
                    @php
                        $st = strtolower($anggota->user->status ?? 'pending');
                        $sc = match($st) {
                            'aktif'    => 'st-aktif',
                            'nonaktif' => 'st-nonaktif',
                            default    => 'st-pending',
                        };
                    @endphp
                    <div class="s-status {{ $sc }}">Status Kader: {{ ucfirst($st) }}</div>

                    <div class="s-div"></div>
                    <div class="s-head">Data Pribadi</div>
                    <div class="s-item">
                        <span class="s-lbl">NBM</span>
                        <span class="s-val">{{ $anggota->nbm ?: 'Belum Memiliki' }}</span>
                    </div>
                    <div class="s-item">
                        <span class="s-lbl">Tempat, Tanggal Lahir</span>
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
                        <div class="qr-lbl">Pindai untuk verifikasi dokumen</div>
                    </div>

                </div>
            </td>

            {{-- ─── PANEL KANAN ─── --}}
            <td class="col-right">

                <div class="r-sec"><span>I.</span> Amanah Persyarikatan, Profesi &amp; Organisasi</div>
                <table class="dt">
                    <tr>
                        <td class="dl" style="width:92pt;">Profesi / Pekerjaan</td><td class="dc">:</td>
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
                                {{ implode(', ', $anggota->keaktifan_muhammadiyah) }}
                            @else <span class="dv-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Otonom</td><td class="dc">:</td>
                        <td class="dv">
                            @if(!empty($anggota->keaktifan_ortom) && is_array($anggota->keaktifan_ortom))
                                {{ implode(', ', $anggota->keaktifan_ortom) }}
                                @if(!empty($anggota->jabatan_ortom) && is_array($anggota->jabatan_ortom))
                                    <span class="dv-muted">({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})</span>
                                @endif
                            @else <span class="dv-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="dl">Organisasi Lain</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->organisasi_lain ?: '—' }}</td>
                    </tr>
                </table>

                <div class="r-sec r-sec-sub"><span>II.</span> Profil Keagamaan, Kemasjidan &amp; Kemasyarakatan</div>
                <table class="dt">
                    <tr>
                        <td class="dl" style="width:66pt;">Baca Al-Qur'an</td><td class="dc">:</td>
                        <td class="dv"><b>{{ $anggota->kemampuan_baca_quran ?: '—' }}</b></td>
                        <td class="dl" style="width:66pt;">Penyaluran ZIS</td><td class="dc">:</td>
                        <td class="dv">@if(!empty($anggota->lembaga_zis) && is_array($anggota->lembaga_zis)){{ implode(', ', $anggota->lembaga_zis) }}@else{{ is_string($anggota->lembaga_zis) ? $anggota->lembaga_zis : '—' }}@endif</td>
                    </tr>
                    <tr>
                        <td class="dl">Hafalan</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->hafalan_quran ?: '—' }}</td>
                        <td class="dl">Masjid Binaan</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->aktifitas_jamaah_masjid ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="dl">Sholat Masjid</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->aktivitas_sholat_masjid ?: '—' }}</td>
                        <td class="dl">Pengurus Takmir</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->keaktifan_takmir ?: 'Tidak' }}@if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }})@endif</td>
                    </tr>
                    <tr>
                        <td class="dl">Kajian Rutin</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->aktivitas_kajian_agama ?: '—' }}</td>
                        <td class="dl">Kemasyarakatan</td><td class="dc">:</td>
                        <td class="dv">{{ $anggota->keaktifan_kemasyarakatan ?: '—' }}</td>
                    </tr>
                </table>

                @php
                    $riwayat    = $anggota->eventPeserta()->with('event')->get();
                    $customData = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
                @endphp
                <div class="r-sec r-sec-sub"><span>III.</span> Rekam Jejak Perkaderan &amp; Keterangan Tambahan</div>
                <table class="dt">
                    <tr>
                        <td class="dl" style="width:92pt;">Riwayat Baitul Arqam</td><td class="dc">:</td>
                        <td class="dv">
                            @if($riwayat->isNotEmpty())
                                @foreach($riwayat as $rep)
                                    <b>{{ $rep->event->nama_event }}</b>@if($rep->status_kelulusan) ({{ $rep->status_kelulusan }})@endif{{ !$loop->last ? '; ' : '' }}
                                @endforeach
                            @else
                                <span class="dv-muted">Belum tercatat riwayat pelatihan Baitul Arqam</span>
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
                        <td style="width:50%;">&nbsp;</td>
                        <td style="width:50%;" class="sign-date">Karanganyar, {{ now()->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="sign-role">Kader yang Bersangkutan,</td>
                        <td class="sign-role">PDM Karanganyar &bull; MPKSDI<br>Ketua,</td>
                    </tr>
                    <tr>
                        <td class="sign-space"></td>
                        <td class="sign-space"></td>
                    </tr>
                    <tr>
                        <td class="sign-name">{{ $anggota->nama_lengkap }}</td>
                        <td class="sign-name">H. Zainudin Ahpandi, S.E.</td>
                    </tr>
                    <tr>
                        <td class="sign-nbm">NBM: {{ $anggota->nbm ?: 'Kader Muhammadiyah' }}</td>
                        <td class="sign-nbm">NBM: 864.219 &bull; PDM Kabupaten Karanganyar</td>
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
