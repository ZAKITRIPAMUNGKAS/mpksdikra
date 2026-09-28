<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat Batch - {{ $event->nama_event }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            background: #ffffff;
            font-size: 11px;
            line-height: 1.4;
        }

        .page {
            width: 100%;
            height: 188mm;
            position: relative;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        .cert-border {
            border: 3px double #002b40;
            border-radius: 8px;
            padding: 8mm 12mm;
            height: 186mm;
            position: relative;
            background: #ffffff;
        }
        .cert-inner {
            border: 1px solid #f8bb00;
            border-radius: 4px;
            height: 170mm;
            padding: 5mm 8mm;
            position: relative;
        }

        .kop-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .kop-org {
            font-size: 11pt;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #002b40;
            line-height: 1.25;
        }
        .kop-sub {
            font-size: 9.5pt;
            font-weight: 700;
            color: #005c8a;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 1mm;
        }
        .kop-line {
            border-bottom: 2px solid #002b40;
            margin-top: 2.5mm;
            margin-bottom: 0.8mm;
        }
        .kop-line-gold {
            border-bottom: 1px solid #f8bb00;
            margin-bottom: 4mm;
        }

        .cert-title {
            text-align: center;
            font-size: 22pt;
            font-weight: 900;
            letter-spacing: 5px;
            color: #002b40;
            margin-top: 1mm;
            margin-bottom: 0.5mm;
        }
        .cert-nomor {
            text-align: center;
            font-size: 8.5pt;
            color: #475569;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 3.5mm;
        }

        .cert-statement {
            text-align: center;
            font-size: 10pt;
            color: #334155;
            margin-bottom: 1.5mm;
        }
        .cert-name {
            text-align: center;
            font-size: 18pt;
            font-weight: 900;
            color: #002b40;
            letter-spacing: 0.5px;
            margin: 2mm 0 1.5mm;
            text-decoration: underline;
        }
        .cert-meta {
            text-align: center;
            font-size: 8.5pt;
            color: #475569;
            margin-bottom: 3mm;
        }

        .cert-body {
            text-align: center;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.6;
            margin: 0 auto;
            max-width: 220mm;
        }
        .cert-grade {
            display: inline-block;
            margin-top: 2mm;
            padding: 1.5mm 8mm;
            background: #f0f9ff;
            border: 1.5px solid #005c8a;
            border-radius: 999px;
            font-size: 10.5pt;
            font-weight: 800;
            color: #005c8a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            position: absolute;
            bottom: 4mm;
            left: 0;
            right: 0;
            padding: 0 10mm;
        }
        .sign-box {
            text-align: center;
            font-size: 8.5pt;
            color: #1e293b;
            vertical-align: top;
        }
        .sign-space {
            height: 16mm;
        }
        .sign-name {
            font-weight: 800;
            color: #002b40;
            font-size: 9pt;
            text-decoration: underline;
        }
        .sign-nbm {
            font-size: 7.5pt;
            color: #475569;
        }

        .qr-center {
            text-align: center;
            width: 35mm;
        }
        .qr-img {
            width: 16mm;
            height: 16mm;
            margin: 0 auto 1mm;
        }
        .qr-hash {
            font-size: 5.5pt;
            color: #94a3b8;
            font-family: monospace;
        }

        .trans-header {
            text-align: center;
            margin-bottom: 4mm;
        }
        .trans-title {
            font-size: 13pt;
            font-weight: 800;
            color: #002b40;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .trans-sub {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 1mm;
        }

        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4mm;
            font-size: 8.5pt;
        }
        .bio-table td {
            padding: 1mm 2mm;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 4mm;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 1.8mm 2.5mm;
        }
        .data-table th {
            background: #002b40;
            color: #ffffff;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }
    </style>
</head>
<body>

    @foreach($items as $item)
    @php
        $peserta = $item['peserta'];
        $nilai = $item['nilai'];
        $qrPng = $item['qrPng'];
    @endphp

    {{-- HALAMAN 1 --}}
    <div class="page">
        <div class="cert-border">
            <div class="cert-inner">
                <div class="kop-table">
                    <div class="kop-org">PIMPINAN DAERAH MUHAMMADIYAH KARANGANYAR</div>
                    <div class="kop-sub">Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI)</div>
                </div>
                <div class="kop-line"></div>
                <div class="kop-line-gold"></div>

                <div class="cert-title">SERTIFIKAT</div>
                <div class="cert-nomor">Nomor: {{ $event->generateNomorSertifikat($peserta->id) }}</div>

                <div class="cert-statement">Diberikan kepada:</div>
                <div class="cert-name">{{ strtoupper($peserta->anggota->nama_lengkap) }}</div>
                <div class="cert-meta">
                    NBM: <b>{{ $peserta->anggota->nbm ?: '-' }}</b> &bull;
                    NIK: <b>{{ substr($peserta->anggota->nik, 0, 6) }}******</b> &bull;
                    Utusan: <b>PCM {{ $peserta->anggota->pcm ?: ($peserta->anggota->kecamatan ?: 'Karanganyar') }}</b>
                </div>

                <div class="cert-body">
                    Telah mengikuti seluruh rangkaian perkaderan utama Persyarikatan Muhammadiyah pada kegiatan:
                    <br>
                    <b style="color: #002b40; font-size: 11pt;">{{ strtoupper($event->nama_event) }}</b>
                    <br>
                    yang diselenggarakan pada tanggal {{ $event->tanggal_mulai?->translatedFormat('d F Y') }} s.d. {{ $event->tanggal_selesai?->translatedFormat('d F Y') }}
                    @if($event->lokasi)bertempat di {{ $event->lokasi }}@endif,
                    dan berdasarkan evaluasi dinyatakan:
                    <br>
                    <div class="cert-grade">{{ $nilai->status_kelulusan ?? 'Lulus' }}</div>
                </div>

                <table class="sign-table">
                    <tr>
                        <td class="sign-box" style="width: 38%;">
                            Karanganyar, {{ $event->tanggal_selesai?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}<br>
                            <b>Master of Training (MoT)</b>
                            <div class="sign-space"></div>
                            <div class="sign-name">{{ $event->fasilitator->first()?->name ?? 'Tim Instruktur MPKSDI' }}</div>
                            <div class="sign-nbm">Instruktur Utama Perkaderan</div>
                        </td>
                        <td class="qr-center">
                            @if(!empty($qrPng))
                                <img src="{{ $qrPng }}" class="qr-img" alt="QR Verifikasi">
                            @endif
                            <div class="qr-hash">Verifikasi Pangkalan Data<br>{{ substr($nilai->verification_hash ?? '-', 0, 16) }}</div>
                        </td>
                        <td class="sign-box" style="width: 38%;">
                            Karanganyar, {{ $event->tanggal_selesai?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}<br>
                            <b>Ketua MPKSDI PDM Karanganyar</b>
                            <div class="sign-space"></div>
                            <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                            <div class="sign-nbm">NBM. 864.219</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- HALAMAN 2 --}}
    <div class="page">
        <div class="cert-border" style="height: 186mm; padding: 7mm 10mm;">
            <div class="trans-header">
                <div class="trans-title">TRANSKRIP MATERI &amp; CAPAIAN KOMPETENSI PERKADERAN</div>
                <div class="trans-sub">Lampiran Sertifikat Nomor: {{ $event->generateNomorSertifikat($peserta->id) }} &bull; {{ $event->nama_event }}</div>
            </div>

            <table class="bio-table">
                <tr>
                    <td style="width: 18%;"><b>Nama Lengkap</b></td>
                    <td style="width: 32%;">: {{ $peserta->anggota->nama_lengkap }}</td>
                    <td style="width: 18%;"><b>Cabang (PCM)</b></td>
                    <td style="width: 32%;">: {{ $peserta->anggota->pcm ?: ($peserta->anggota->kecamatan ?: '-') }}</td>
                </tr>
                <tr>
                    <td><b>NBM / NIK</b></td>
                    <td>: {{ $peserta->anggota->nbm ?: '-' }} / {{ substr($peserta->anggota->nik, 0, 6) }}******</td>
                    <td><b>Nomor Peserta</b></td>
                    <td>: {{ sprintf('%03d', $peserta->id) }}</td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 58%; vertical-align: top; padding-right: 3mm;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 8%;">No</th>
                                    <th>Mata Pembinaan / Materi Sesi</th>
                                    <th style="width: 25%;">Narasumber</th>
                                    <th style="width: 14%;">Jam (JP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalJp = 0; @endphp
                                @forelse($event->sesi->where('jenis', 'materi') as $idx => $s)
                                @php $jp = 2; $totalJp += $jp; @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                                    <td><b>{{ $s->nama_sesi }}</b></td>
                                    <td>{{ $s->pemateri ?: 'Instruktur PDM' }}</td>
                                    <td style="text-align: center;">{{ $jp }} JP</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #94a3b8;">Sesi materi terintegrasi dalam kurikulum Baitul Arqam.</td>
                                </tr>
                                @endforelse
                                <tr style="background: #f1f5f9; font-weight: bold;">
                                    <td colspan="3" style="text-align: right;">Total Beban Pelatihan:</td>
                                    <td style="text-align: center;">{{ max($totalJp, 16) }} JP</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                    <td style="width: 42%; vertical-align: top; padding-left: 3mm;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Aspek Penilaian Evaluasi</th>
                                    <th style="width: 25%;">Skor (0-100)</th>
                                    <th style="width: 25%;">Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <b>I. Aspek Kognitif</b><br>
                                        <span style="font-size: 7pt; color: #64748b;">Posttest &amp; N-Gain Pemahaman Ideologi</span>
                                    </td>
                                    <td style="text-align: center; font-weight: bold;">{{ number_format((float) $nilai->nilai_posttest, 1) }}</td>
                                    <td style="text-align: center;">{{ $nilai->nilai_posttest >= 80 ? 'Sangat Baik' : ($nilai->nilai_posttest >= 65 ? 'Baik' : 'Cukup') }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <b>II. Aspek Psikomotorik</b><br>
                                        <span style="font-size: 7pt; color: #64748b;">Praktik Ibadah, Sholat &amp; Keaktifan</span>
                                    </td>
                                    <td style="text-align: center; font-weight: bold;">{{ number_format((float) $nilai->nilai_psikomotor, 1) }}</td>
                                    <td style="text-align: center;">{{ $nilai->nilai_psikomotor >= 80 ? 'Sangat Baik' : ($nilai->nilai_psikomotor >= 65 ? 'Baik' : 'Cukup') }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <b>III. Aspek Afektif</b><br>
                                        <span style="font-size: 7pt; color: #64748b;">Komitmen, Integritas &amp; Sikap</span>
                                    </td>
                                    <td style="text-align: center; font-weight: bold;">{{ number_format((float) $nilai->nilai_afektif, 1) }}</td>
                                    <td style="text-align: center;">{{ $nilai->nilai_afektif >= 80 ? 'Sangat Baik' : ($nilai->nilai_afektif >= 65 ? 'Baik' : 'Cukup') }}</td>
                                </tr>
                                <tr style="background: #e0f2fe; font-weight: bold;">
                                    <td><b>NILAI AKHIR KOMPREHENSIF</b></td>
                                    <td style="text-align: center; font-size: 9pt; color: #002b40;">{{ number_format((float) $nilai->nilai_akhir, 1) }}</td>
                                    <td style="text-align: center; color: #005c8a;">{{ $nilai->status_kelulusan }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 4mm; font-size: 7.5pt; color: #475569; border: 1px dashed #cbd5e1; padding: 2.5mm; border-radius: 4px; background: #fafafa;">
                            <b>Keterangan Predikat Kelulusan:</b><br>
                            &bull; 85.0 – 100.0 : Istimewa / Sangat Baik (Mumtaz)<br>
                            &bull; 70.0 – 84.9 : Baik (Jayyid Jiddan)<br>
                            &bull; Presensi Wajib Minimal: 75% (Bersyarat) / 100% (Penuh)
                        </div>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 4mm; text-align: right; font-size: 8pt; padding-right: 5mm;">
                Karanganyar, {{ $event->tanggal_selesai?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}<br>
                <b>Ketua MPKSDI PDM Karanganyar</b><br><br><br>
                <b><u>H. Zainudin Ahpandi, S.E.</u></b><br>
                <span>NBM. 864.219</span>
            </div>
        </div>
    </div>
    @endforeach

</body>
</html>
