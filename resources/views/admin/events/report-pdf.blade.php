<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Evaluasi Perkaderan — {{ $event->nama_event }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 12mm 12mm 14mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #0f172a;
            background: #ffffff;
            margin: 0;
            padding: 0;
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
        .kop-text {
            text-align: center;
        }
        .kop-org-1 {
            font-size: 9pt;
            font-weight: bold;
            color: #002b40;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-org-2 {
            font-size: 13pt;
            font-weight: 900;
            color: #005c8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 2px 0 0;
        }
        .kop-sub {
            font-size: 7.5pt;
            color: #475569;
            margin: 2px 0 0;
            line-height: 1.25;
        }
        .kop-divider {
            border-top: 2.5px solid #002b40;
            border-bottom: 0.75px solid #002b40;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        /* Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0284c7;
            margin: 2px 0 0;
        }

        /* Metadata & KPI Box */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-table td {
            vertical-align: top;
            border: none;
            padding: 0;
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 8pt;
        }
        .info-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-card td {
            padding: 1.5px 3px;
            font-size: 7.5pt;
        }
        .info-label {
            color: #64748b;
            font-weight: bold;
            width: 85px;
        }
        .info-val {
            color: #0f172a;
            font-weight: bold;
        }

        .kpi-card {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 6px 10px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .kpi-table td {
            padding: 2px 4px;
            border-right: 1px solid #e0f2fe;
        }
        .kpi-table td:last-child {
            border-right: none;
        }
        .kpi-num {
            font-size: 11pt;
            font-weight: 900;
            display: block;
            line-height: 1.1;
        }
        .kpi-lbl {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0369a1;
            letter-spacing: 0.3px;
        }

        /* Tabel Data Rekapitulasi */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 7.5pt;
        }
        .data-table th {
            background: #002b40;
            color: #ffffff;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 3px;
            border: 1px solid #001f30;
            text-align: center;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 3px;
            text-align: center;
            color: #1e293b;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .data-table td.text-left {
            text-align: left;
            padding-left: 6px;
        }
        .data-table td.font-bold {
            font-weight: bold;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .badge-lulus {
            background-color: #dcfce7;
            color: #15803d;
            border: 0.5px solid #86efac;
        }
        .badge-bersyarat {
            background-color: #fef3c7;
            color: #b45309;
            border: 0.5px solid #fde68a;
        }
        .badge-tidak {
            background-color: #ffe4e6;
            color: #be123c;
            border: 0.5px solid #fecdd3;
        }

        /* Footer & Tanda Tangan */
        .formula-note {
            margin-top: 8px;
            padding: 4px 8px;
            background: #f1f5f9;
            border-left: 3px solid #0284c7;
            border-radius: 2px;
            font-size: 6.5pt;
            color: #475569;
            line-height: 1.3;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            border: none;
            font-size: 7.5pt;
        }
        .sign-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 45px;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
            font-size: 8pt;
        }
        .sign-nip {
            color: #64748b;
            font-size: 7pt;
            margin: 1px 0 0;
        }

        .report-footer {
            margin-top: 10px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: justify;
        }
    </style>
</head>
<body>

    {{-- 1. Kop Surat Resmi Organisasi --}}
    <table class="kop-table">
        <tr>
            <td style="width: 70px; text-align: center;">
                <img src="{{ public_path('aset/logo-mpksdi.webp') }}" style="height: 52px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td class="kop-text">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah Karanganyar &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar, Jawa Tengah 57722<br>
                    Website Resmi: <i>mpksdikra.muhammadiyah.or.id</i> &bull; Layanan Terpadu: Baitul Arqam &amp; Pangkalan Data Kader
                </div>
            </td>
            <td style="width: 70px; text-align: center;">
                <img src="{{ public_path('favicon.png') }}" style="height: 48px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- 2. Judul Dokumen --}}
    <div class="doc-header">
        <h1 class="doc-title">LAPORAN EVALUASI &amp; REKAPITULASI HASIL KELULUSAN</h1>
        <div class="doc-subtitle">{{ strtoupper($event->nama_event) }}</div>
    </div>

    {{-- 3. Metadata Kegiatan & KPI Ringkasan --}}
    <table class="meta-table">
        <tr>
            <td style="width: 48%; padding-right: 6px;">
                <div class="info-card">
                    <table>
                        <tr>
                            <td class="info-label">Sasaran Peserta</td>
                            <td style="width: 6px;">:</td>
                            <td class="info-val">{{ $event->sasaran ?? 'Pimpinan & Kader Persyarikatan' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Waktu Pelaksanaan</td>
                            <td>:</td>
                            <td class="info-val">{{ $event->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $event->tanggal_selesai->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Tempat / Lokasi</td>
                            <td>:</td>
                            <td class="info-val">{{ $event->lokasi ?? 'Gedung Dakwah PDM Karanganyar' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Status Kegiatan</td>
                            <td>:</td>
                            <td class="info-val">
                                <span style="text-transform: uppercase; color: {{ $event->status === 'selesai' ? '#166534' : '#0284c7' }};">
                                    {{ $event->status }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 52%; padding-left: 6px;">
                <div class="kpi-card">
                    <table class="kpi-table">
                        <tr>
                            <td>
                                <span class="kpi-num" style="color: #005c8a;">{{ $totalPeserta }}</span>
                                <span class="kpi-lbl">Total Peserta</span>
                            </td>
                            <td>
                                <span class="kpi-num" style="color: #16a34a;">{{ $totalLulus }}</span>
                                <span class="kpi-lbl">Lulus Murni</span>
                            </td>
                            <td>
                                <span class="kpi-num" style="color: #d97706;">{{ $totalBersyarat }}</span>
                                <span class="kpi-lbl">Bersyarat</span>
                            </td>
                            <td>
                                <span class="kpi-num" style="color: #dc2626;">{{ $totalTidakLulus }}</span>
                                <span class="kpi-lbl">Tidak Lulus</span>
                            </td>
                            <td>
                                <span class="kpi-num" style="color: #0284c7;">{{ number_format($rerataNA, 1) }}</span>
                                <span class="kpi-lbl">Rata-rata NA</span>
                            </td>
                            <td>
                                <span class="kpi-num" style="color: #002b40;">{{ number_format($tertinggiNA, 1) }}</span>
                                <span class="kpi-lbl">NA Tertinggi</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- 4. Tabel Rekapitulasi Nilai & Ranking --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 26px;">Rank</th>
                <th style="width: 100px;">NIK / Identitas</th>
                <th style="width: 160px;" class="text-left">Nama Lengkap Kader</th>
                <th style="width: 105px;" class="text-left">Utusan / Cabang</th>
                <th style="width: 44px;">Pre</th>
                <th style="width: 44px;">Post</th>
                <th style="width: 42px;">N-Gain</th>
                <th style="width: 46px;">Afektif</th>
                <th style="width: 48px;">Psikomotor</th>
                <th style="width: 44px;">Keaktifan</th>
                <th style="width: 46px;">Presensi</th>
                <th style="width: 50px;">Nilai Akhir</th>
                <th style="width: 75px;">Status Kelulusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $p)
            @php
                $status = $p->status_kelulusan ?? '-';
                $kertas = $tesKertas[$p->anggota_id] ?? [];
            @endphp
            <tr>
                <td class="font-bold">{{ $p->ranking ?? '-' }}</td>
                <td style="font-family: monospace; font-size: 6.8pt; color: #475569;">{{ $p->anggota->nik ?? '-' }}</td>
                <td class="text-left font-bold" style="color: #0f172a;">{{ $p->anggota->nama_lengkap }}</td>
                <td class="text-left" style="color: #334155;">{{ $p->anggota->kecamatan ? 'PCM ' . $p->anggota->kecamatan : ($p->anggota->aum_tempat_tugas ?? 'Karanganyar') }}</td>
                <td>{{ number_format($p->nilai_pretest, 1) }}@if(in_array('pretest', $kertas, true))<sup style="color:#b45309;font-weight:bold;">K</sup>@endif</td>
                <td>{{ number_format($p->nilai_posttest, 1) }}@if(in_array('posttest', $kertas, true))<sup style="color:#b45309;font-weight:bold;">K</sup>@endif</td>
                <td>{{ number_format($p->n_gain, 2) }}</td>
                <td>{{ number_format($p->nilai_afektif, 1) }}</td>
                <td>{{ number_format($p->nilai_psikomotor, 1) }}</td>
                <td>{{ number_format($p->nilai_keaktifan ?? 0, 1) }}</td>
                <td>{{ number_format($p->nilai_kehadiran, 1) }}%</td>
                <td class="font-bold" style="color: #005c8a; font-size: 8pt;">{{ number_format($p->nilai_akhir, 2) }}</td>
                <td>
                    @if($status === 'Lulus')
                        <span class="badge badge-lulus">Lulus</span>
                    @elseif($status === 'Lulus Bersyarat')
                        <span class="badge badge-bersyarat">Bersyarat</span>
                    @elseif($status === 'Tidak Lulus')
                        <span class="badge badge-tidak">Tidak Lulus</span>
                    @else
                        <span>{{ $status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="padding: 16px; color: #94a3b8; font-style: italic;">
                    Belum ada data nilai atau peserta terdaftar pada event ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 5. Formula & Penjelasan Nilai --}}
    <div class="formula-note">
        <b>Pedoman Standar Nilai &amp; Kelulusan Baitul Arqam MPKSDI:</b><br>
        1. <b>Nilai Akhir (NA)</b> = 40% Kognitif (70% Posttest + 30% N-Gain) + 35% Psikomotor (90% Praktik Lapangan + 10% Poin Keaktifan) + 25% Afektif (Angket Sikap &amp; Kedisiplinan).<br>
        @if($tesKertas->isNotEmpty())<sup style="color:#b45309;font-weight:bold;">K</sup> = nilai pretest/posttest diinput admin dari lembar jawaban kertas, dinilai dengan kunci jawaban yang sama.<br>@endif
        @if($event->rtlSyaratKelulusan())
        2. <b>Syarat Kelulusan Terpadu</b>: Presensi kehadiran &ge; 100%, menyelesaikan Posttest, dan menyerahkan RTL dinyatakan <i>Lulus</i>; presensi 75% s.d. 99% atau tugas RTL/Posttest dalam proses dinyatakan <i>Lulus Bersyarat</i>; kehadiran &lt; 75% dinyatakan <i>Tidak Lulus</i>.
        @else
        2. <b>Syarat Kelulusan Terpadu</b>: Presensi kehadiran &ge; 100% dan menyelesaikan Posttest dinyatakan <i>Lulus</i>; presensi 75% s.d. 99% atau Posttest dalam proses dinyatakan <i>Lulus Bersyarat</i>; kehadiran &lt; 75% dinyatakan <i>Tidak Lulus</i>. RTL bersifat opsional pada kegiatan ini.
        @endif
    </div>

    {{-- 6. Lembar Pengesahan Tanda Tangan --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="sign-title">
                    Mengetahui,<br>
                    Master of Training / Ketua Tim Fasilitator
                </div>
                <div class="sign-name">Drs. H. Sriyanto, M.Pd.</div>
                <div class="sign-nip">NBM. 842.109 / Instruktur Perkaderan</div>
            </td>
            <td>
                <div class="sign-title">
                    Karanganyar, {{ now()->translatedFormat('d F Y') }}<br>
                    Ketua MPKSDI PDM Karanganyar
                </div>
                <div class="sign-name">H. Zainudin Ahpandi, S.E</div>
                <div class="sign-nip">NBM. 719.340 / Pimpinan Daerah Muhammadiyah</div>
            </td>
        </tr>
    </table>

    {{-- 7. Footer Hash Verifikasi --}}
    <div class="report-footer">
        Dokumen resmi pangkalan data perkaderan terpadu MPKSDI PDM Karanganyar. Dicetak otomatis dari sistem Arqam pada {{ now()->translatedFormat('l, d F Y H:i:s') }} WIB.
    </div>

</body>
</html>
