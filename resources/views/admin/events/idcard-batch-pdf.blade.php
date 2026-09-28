<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Peserta (Cetak Massal) - {{ $event->nama_event }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 6mm;
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
        }

        .page {
            width: 100%;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm 4mm;
        }
        .grid-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .card {
            width: 93mm;
            height: 57mm;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            position: relative;
            overflow: hidden;
            background: #ffffff;
        }

        .head {
            background: #002b40;
            color: #ffffff;
            padding: 2mm 3mm 1.5mm;
            border-bottom: 2px solid #f8bb00;
        }
        .head-org {
            font-size: 5pt;
            font-weight: bold;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #f8bb00;
        }
        .head-title {
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: 0.4px;
            color: #ffffff;
        }
        .head-event {
            font-size: 5.5pt;
            color: #bae6fd;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 86mm;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            padding: 2mm 3mm;
        }
        .content-table td {
            vertical-align: top;
            padding: 0;
        }

        .photo-cell {
            width: 18mm;
        }
        .photo-box {
            width: 17mm;
            height: 22mm;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
            text-align: center;
            border-radius: 2px;
            overflow: hidden;
        }
        .photo-box img {
            width: 17mm;
            height: 22mm;
            object-fit: cover;
        }
        .photo-ph {
            font-size: 5pt;
            color: #64748b;
            line-height: 22mm;
            font-weight: bold;
        }

        .info-cell {
            padding-left: 2.5mm;
            padding-right: 1.5mm;
        }
        .name {
            font-size: 7.5pt;
            font-weight: bold;
            color: #002b40;
            line-height: 1.15;
            margin-bottom: 1.5mm;
            max-height: 5.5mm;
            overflow: hidden;
        }
        .row {
            font-size: 5.2pt;
            color: #334155;
            line-height: 1.25;
            margin-bottom: 0.6mm;
        }
        .row b {
            color: #005c8a;
        }

        .qr-cell {
            width: 19mm;
            text-align: center;
        }
        .qr-box {
            display: inline-block;
            border: 0.5px solid #cbd5e1;
            padding: 0.5mm;
            background: #ffffff;
            border-radius: 2px;
        }
        .qr-box img {
            width: 16mm;
            height: 16mm;
            display: block;
        }
        .qr-lbl {
            font-size: 4.2pt;
            color: #64748b;
            margin-top: 0.8mm;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.2px;
            line-height: 1;
        }

        .foot {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f0f9ff;
            border-top: 0.6px solid #e0f2fe;
            padding: 1mm 3mm;
            font-size: 4.8pt;
            color: #005c8a;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>

    @php
        $chunks = $cards->chunk(8); // 8 kartu per lembar A4 (2 kolom x 4 baris)
    @endphp

    @foreach($chunks as $chunk)
    <div class="page">
        <table class="grid-table">
            @foreach($chunk->chunk(2) as $row)
            <tr>
                @foreach($row as $card)
                <td>
                    <div class="card">
                        <div class="head">
                            <div class="head-org">MPKSDI PDM KARANGANYAR</div>
                            <div class="head-title">KARTU PESERTA BAITUL ARQAM</div>
                            <div class="head-event">{{ $event->nama_event }}</div>
                        </div>

                        <table class="content-table" style="margin-top: 1.5mm;">
                            <tr>
                                <td class="photo-cell">
                                    <div class="photo-box">
                                        @if(!empty($card['foto']))
                                            <img src="{{ $card['foto'] }}" alt="Foto">
                                        @else
                                            <div class="photo-ph">FOTO 3×4</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="info-cell">
                                    <div class="name">{{ strtoupper($card['nama']) }}</div>
                                    <div class="row"><b>No. Peserta:</b> {{ sprintf('%03d', $card['no_peserta']) }}</div>
                                    <div class="row"><b>NBM:</b> {{ $card['nbm'] ?: 'Belum Ada' }}</div>
                                    <div class="row"><b>Cabang (PCM):</b> {{ $card['pcm'] }}</div>
                                    <div class="row"><b>Pelaksanaan:</b> {{ $event->tanggal_mulai?->format('d/m/Y') }}</div>
                                </td>
                                <td class="qr-cell">
                                    <div class="qr-box">
                                        <img src="{{ $card['qr'] }}" alt="QR">
                                    </div>
                                    <div class="qr-lbl">Scan Presensi</div>
                                </td>
                            </tr>
                        </table>

                        <div class="foot">
                            Pangkalan Data Terpadu MPKSDI PDM Karanganyar &bull; Tunjukkan saat Sesi
                        </div>
                    </div>
                </td>
                @endforeach
                @if($row->count() < 2)
                <td>&nbsp;</td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach

</body>
</html>
