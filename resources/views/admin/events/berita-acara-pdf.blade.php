<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara Yudisium — {{ $event->nama_event }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
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
            font-size: 8.5pt;
            font-weight: bold;
            color: #002b40;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .kop-org-2 {
            font-size: 12pt;
            font-weight: 900;
            color: #005c8a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 2px 0 0;
        }
        .kop-sub {
            font-size: 7pt;
            color: #475569;
            margin: 2px 0 0;
            line-height: 1.25;
        }
        .kop-divider {
            border-top: 2.5px solid #002b40;
            border-bottom: 0.75px solid #002b40;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 12px;
        }

        /* Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-nomor {
            font-size: 8pt;
            font-weight: bold;
            color: #64748b;
            margin-top: 3px;
        }
        .doc-subtitle {
            font-size: 9pt;
            font-weight: bold;
            color: #0284c7;
            margin: 3px 0 0;
            text-transform: uppercase;
        }

        /* Paragraf Narasi */
        .narasi {
            text-align: justify;
            margin-bottom: 8px;
            font-size: 8.5pt;
            line-height: 1.45;
        }

        /* Kotak Rekapitulasi Hasil */
        .box-title {
            font-size: 8.5pt;
            font-weight: 900;
            color: #002b40;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
        }

        .stat-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .stat-table th, .stat-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 8pt;
        }
        .stat-table th {
            background-color: #005c8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .stat-table td.center {
            text-align: center;
        }
        .stat-table td.right {
            text-align: right;
        }
        .stat-table tr.total-row {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        /* Tabel Kader Terbaik */
        .rank-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .rank-table th, .rank-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 7.5pt;
        }
        .rank-table th {
            background-color: #002b40;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        /* Lembar Tanda Tangan */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            border: none;
            padding: 0 5px;
            font-size: 8pt;
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
            font-size: 8.5pt;
        }
        .sign-sub {
            color: #64748b;
            font-size: 7.5pt;
            margin: 1px 0 0;
        }

        .footer-note {
            margin-top: 15px;
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
            <td style="width: 65px; text-align: center;">
                <img src="{{ public_path('aset/logo-mpksdi.webp') }}" style="height: 50px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td class="kop-text">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah Karanganyar &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar, Jawa Tengah 57722<br>
                    Website Resmi: <i>mpksdikra.muhammadiyah.or.id</i> &bull; Layanan Terpadu: Baitul Arqam &amp; Pangkalan Data Kader
                </div>
            </td>
            <td style="width: 65px; text-align: center;">
                <img src="{{ public_path('favicon.png') }}" style="height: 45px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- 2. Judul Dokumen --}}
    <div class="doc-header">
        <h1 class="doc-title">BERITA ACARA SIDANG PLENO YUDISIUM KELULUSAN</h1>
        <div class="doc-nomor">Nomor: {{ $event->id }}/BA.YUD/MPKSDI-KRA/{{ \Carbon\Carbon::parse($event->tanggal_selesai)->translatedFormat('m/Y') }}</div>
        <div class="doc-subtitle">{{ $event->nama_event }}</div>
    </div>

    {{-- 3. Narasi Pembuka --}}
    <div class="narasi">
        Pada hari ini, <strong>{{ \Carbon\Carbon::parse($event->tanggal_selesai)->translatedFormat('l') }}</strong>, 
        tanggal <strong>{{ \Carbon\Carbon::parse($event->tanggal_selesai)->translatedFormat('d F Y') }}</strong>, 
        bertempat di <strong>{{ $event->lokasi ?: 'Kabupaten Karanganyar' }}</strong>, 
        telah diselenggarakan <strong>Sidang Pleno Yudisium Penetapan Kelulusan</strong> bagi seluruh kader peserta 
        <strong>{{ $event->nama_event }}</strong> yang diselenggarakan oleh Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI) 
        Pimpinan Daerah Muhammadiyah Kabupaten Karanganyar.
    </div>

    <div class="narasi">
        Berdasarkan hasil pemantauan komprehensif seluruh instrumen perkaderan yang meliputi:
        (1) Presensi kehadiran kegiatan 100% atau bersyarat &ge; 75%, 
        (2) Evaluasi kognitif pretest, posttest, dan N-Gain, 
        (3) Uji praktik psikomotorik ibadah dan keaktifan forum, 
        (4) Angket afektif komitmen ideologi, serta 
        (5) Pengumpulan tugas Rencana Tindak Lanjut (RTL), Dewan Instruktur bersama Pimpinan MPKSDI Karanganyar menetapkan hasil sebagai berikut:
    </div>

    {{-- 4. Rekapitulasi Statistik Kelulusan --}}
    <div class="box-title">I. REKAPITULASI KEPUTUSAN KELULUSAN</div>
    <table class="stat-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Kategori Hasil Yudisium</th>
                <th style="width: 25%;">Jumlah Kader</th>
                <th style="width: 25%;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">1</td>
                <td><strong>LULUS MURNI</strong> (Memenuhi seluruh syarat kehadiran &amp; tugas)</td>
                <td class="center font-bold">{{ $totalLulus }} Orang</td>
                <td class="center">{{ $totalPeserta > 0 ? round(($totalLulus / $totalPeserta) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td class="center">2</td>
                <td><strong>LULUS BERSYARAT</strong> (Menyelesaikan perbaikan tugas/presensi)</td>
                <td class="center font-bold">{{ $totalBersyarat }} Orang</td>
                <td class="center">{{ $totalPeserta > 0 ? round(($totalBersyarat / $totalPeserta) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td class="center">3</td>
                <td><strong>TIDAK LULUS</strong> (Kehadiran &lt; 75% / Tidak memenuhi syarat)</td>
                <td class="center font-bold">{{ $totalTidakLulus }} Orang</td>
                <td class="center">{{ $totalPeserta > 0 ? round(($totalTidakLulus / $totalPeserta) * 100, 1) : 0 }}%</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align: right;"><strong>TOTAL PESERTA TERDAFTAR</strong></td>
                <td class="center"><strong>{{ $totalPeserta }} Orang</strong></td>
                <td class="center"><strong>100.0%</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Indikator Metrik Nilai --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7.5pt;">
        <tr>
            <td style="padding: 4px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
                Rerata Nilai Akhir: <strong>{{ number_format($rerataNA, 2) }}</strong> &bull; 
                Nilai Tertinggi: <strong>{{ number_format($tertinggiNA, 2) }}</strong> &bull; 
                Nilai Terendah: <strong>{{ number_format($terendahNA, 2) }}</strong>
            </td>
        </tr>
    </table>

    {{-- 5. Anugerah 5 Kader Teladan / Terbaik --}}
    <div class="box-title">II. PENGANUGERAHAN 5 KADER TELADAN TERBAIK (TOP LEADERBOARD)</div>
    <table class="rank-table">
        <thead>
            <tr>
                <th style="width: 7%;">Peringkat</th>
                <th style="width: 38%;">Nama Lengkap Kader</th>
                <th style="width: 25%;">Asal Utusan / Cabang</th>
                <th style="width: 15%;">Nilai Akhir</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($top5 as $idx => $t)
            <tr>
                <td style="text-align: center; font-weight: bold;">#{{ $idx + 1 }}</td>
                <td><strong>{{ $t->anggota->nama_lengkap ?? '-' }}</strong></td>
                <td>{{ $t->anggota->pcm ?: ($t->anggota->kecamatan ?: '-') }}</td>
                <td style="text-align: center; font-weight: bold; color: #005c8a;">{{ number_format($t->nilai_akhir, 2) }}</td>
                <td style="text-align: center;">{{ $t->status_kelulusan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #64748b;">Belum ada data nilai peserta.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 6. Pengesahan Keputusan --}}
    <div class="narasi" style="margin-top: 10px;">
        Demikian Berita Acara Sidang Yudisium ini dibuat dengan sebenar-benarnya berdasarkan musyawarah mufakat Dewan Instruktur 
        dan Pimpinan MPKSDI PDM Karanganyar, untuk dipergunakan sebagaimana mestinya dan menjadi landasan penerbitan Sertifikat Kelulusan Resmi.
    </div>

    {{-- 7. Lembar Tanda Tangan 3 Pihak --}}
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                <div class="sign-title">
                    Master of Training (MoT),<br>
                    <span style="font-weight: normal; font-size: 7.5pt;">Ketua Tim Instruktur</span>
                </div>
                <div class="sign-name">( ........................................ )</div>
                <div class="sign-sub">NBM. ............................</div>
            </td>
            <td style="width: 34%;">
                <div class="sign-title">
                    Imam Training,<br>
                    <span style="font-weight: normal; font-size: 7.5pt;">Pembina Ibadah &amp; Spiritual</span>
                </div>
                <div class="sign-name">( ........................................ )</div>
                <div class="sign-sub">NBM. ............................</div>
            </td>
            <td style="width: 33%;">
                <div class="sign-title">
                    Ketua MPKSDI PDM Karanganyar,<br>
                    <span style="font-weight: normal; font-size: 7.5pt;">Penanggung Jawab Perkaderan</span>
                </div>
                <div class="sign-name">H. Zainudin Ahpandi, S.E.</div>
                <div class="sign-sub">NBM. 883.693</div>
            </td>
        </tr>
    </table>

    {{-- Catatan Legalitas Footer --}}
    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh Sistem ARQAM MPKSDI PDM Karanganyar. Berita acara ini sah sebagai bukti legal pelaksanaan sidang yudisium perkaderan persyarikatan.
    </div>

</body>
</html>
