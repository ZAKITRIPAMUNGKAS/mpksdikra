<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Kelompok Outbound - {{ $event->nama_event }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 9pt;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 2px solid #002b40;
            padding-bottom: 3mm;
        }
        .org {
            font-size: 11pt;
            font-weight: 800;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .sub {
            font-size: 8.5pt;
            font-weight: 600;
            color: #005c8a;
            margin-top: 1mm;
        }
        .title {
            font-size: 13pt;
            font-weight: 800;
            margin-top: 2mm;
            color: #002b40;
        }
        .meta {
            font-size: 8pt;
            color: #64748b;
            margin-top: 1mm;
        }

        .kelompok-box {
            margin-bottom: 5mm;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .kelompok-header {
            background: #002b40;
            color: #ffffff;
            padding: 2mm 3.5mm;
            font-size: 9pt;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .table th, .table td {
            border: 1px solid #e2e8f0;
            padding: 1.8mm 2.5mm;
        }
        .table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
        }
        .table tr:nth-child(even) td {
            background: #f8fafc;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="org">MPKSDI PDM KABUPATEN KARANGANYAR</div>
        <div class="sub">Pangkalan Data Sistem Informasi Perkaderan Baitul Arqam</div>
        <div class="title">DAFTAR PEMBAGIAN KELOMPOK OUTBOUND</div>
        <div class="meta">Kegiatan: <b>{{ $event->nama_event }}</b> &bull; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
    </div>

    @forelse($kelompoks as $k)
    <div class="kelompok-box">
        <div class="kelompok-header">
            <span>KELOMPOK {{ $k->nomor }}: {{ strtoupper($k->nama_kelompok) }}</span>
            <span style="font-weight: normal; font-size: 8pt;">Total: {{ $k->peserta->count() }} Anggota</span>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 6%; text-align: center;">No</th>
                    <th style="width: 12%; text-align: center;">No. Peserta</th>
                    <th>Nama Lengkap Kader</th>
                    <th style="width: 10%; text-align: center;">Gender</th>
                    <th style="width: 28%;">Cabang (PCM) / Asal</th>
                    <th style="width: 15%; text-align: center;">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($k->peserta as $idx => $p)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ sprintf('%03d', $p->id) }}</td>
                    <td><b>{{ $p->anggota->nama_lengkap }}</b></td>
                    <td style="text-align: center;">{{ $p->anggota->jenis_kelamin === 'L' ? 'Ikhwan (L)' : 'Akhwat (P)' }}</td>
                    <td>{{ $p->anggota->pcm ?: ($p->anggota->kecamatan ?: '-') }}</td>
                    <td>&nbsp;</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada anggota pada kelompok ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @empty
    <div style="text-align: center; padding: 20mm; color: #94a3b8;">
        Belum ada pembagian kelompok outbound yang di-generate.
    </div>
    @endforelse

</body>
</html>
