<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card B4 Batch - {{ $event->nama_event }}</title>
    <style>
        @page {
            size: 265.75pt 383.75pt;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 265.75pt;
            height: 383.75pt;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #FFFFFF;
            color: #063A60;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .card-page {
            width: 265.75pt;
            height: 383.75pt;
            position: relative;
            overflow: hidden;
            background: #FFFFFF;
            page-break-after: always;
            break-after: page;
        }

        .card-page:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }

        .card-bg, .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 265.75pt;
            height: 383.75pt;
            z-index: 1;
        }

        .card-overlay {
            z-index: 3;
        }

        .photo-box {
            position: absolute;
            left: 95.7pt;
            top: 55.6pt;
            width: 154.1pt;
            height: 199.5pt;
            text-align: right;
            z-index: 2;
        }

        .photo-img {
            max-width: 100%;
            max-height: 100%;
            vertical-align: bottom;
        }

        .front-status {
            position: absolute;
            left: 53.75pt;
            top: 245.5pt;
            width: 125pt;
            font-weight: bold;
            font-size: 8.5pt;
            color: #063A60;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            z-index: 4;
        }

        .front-nama {
            position: absolute;
            left: 53.75pt;
            top: 281.5pt;
            width: 138pt;
            font-weight: bold;
            color: #063A60;
            line-height: 1.1;
            z-index: 4;
        }

        .front-nbm {
            position: absolute;
            left: 53.75pt;
            top: 318.0pt;
            width: 138pt;
            font-weight: bold;
            font-size: 8.5pt;
            color: #063A60;
            z-index: 4;
        }

        /* BACK CARD */
        .qr-box {
            position: absolute;
            left: 104.125pt;
            top: 64.25pt;
            width: 57.5pt;
            height: 57.5pt;
            z-index: 2;
            text-align: center;
        }

        .qr-img {
            width: 57.5pt;
            height: 57.5pt;
        }

        .back-val {
            position: absolute;
            left: 43.75pt;
            width: 104pt;
            font-weight: bold;
            color: #063A60;
            z-index: 2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .back-nama { top: 194pt; }
        .back-nbm { top: 221.5pt; font-size: 6.5pt; }
        .back-status { top: 248.5pt; font-size: 6.5pt; }
        .back-kegiatan { top: 275.25pt; font-size: 6.5pt; }
        .back-waktu { top: 302.25pt; font-size: 6.5pt; }
    </style>
</head>
<body>
@php
    $bgFront = public_path('assets/2.png');
    $fgFront = public_path('assets/3.png');
    $bgBack = public_path('assets/1.png');
    $defaultPhoto = public_path('aset/ketua-mpksdi-cutout.png');
    if (! file_exists($defaultPhoto)) {
        $defaultPhoto = public_path('aset/ketua-mpksdi.png');
    }
@endphp

@foreach($cards as $index => $card)
    @php
        $namaLen = mb_strlen($card['nama']);
        if ($namaLen > 34) {
            $frontFontSize = '6.5pt';
            $backFontSize = '5.5pt';
        } elseif ($namaLen > 24) {
            $frontFontSize = '7.5pt';
            $backFontSize = '6pt';
        } else {
            $frontFontSize = '8.5pt';
            $backFontSize = '6.5pt';
        }
        $statusStr = $card['status'] ?? 'PESERTA';
        $statusTitle = ucfirst(strtolower($statusStr));
        $photoSrc = $card['foto'] ?: $defaultPhoto;
    @endphp

    <!-- FRONT CARD -->
    <div class="card-page">
        <img src="{{ $bgFront }}" class="card-bg" alt="Front Background">
        <div class="photo-box">
            <img src="{{ $photoSrc }}" class="photo-img" alt="Foto">
        </div>
        <img src="{{ $fgFront }}" class="card-overlay" alt="Overlay">
        
        <div class="front-status">{{ strtoupper($statusStr) }}</div>
        <div class="front-nama" style="font-size: {{ $frontFontSize }};">{{ $card['nama'] }}</div>
        <div class="front-nbm">{{ $card['nbm'] ?: '-' }}</div>
    </div>

    <!-- BACK CARD -->
    <div class="card-page">
        <img src="{{ $bgBack }}" class="card-bg" alt="Back Background">
        
        <div class="qr-box">
            <img src="{{ $card['qr'] }}" class="qr-img" alt="QR Code">
        </div>

        <div class="back-val back-nama" style="font-size: {{ $backFontSize }};">{{ $card['nama'] }}</div>
        <div class="back-val back-nbm">{{ $card['nbm'] ?: '-' }}</div>
        <div class="back-val back-status">{{ $statusTitle }}</div>
        <div class="back-val back-kegiatan">{{ $event->nama_event ?: 'Baitul Arqam' }}</div>
        <div class="back-val back-waktu">{{ $event->lokasi ?: 'Karanganyar' }}, {{ $event->tanggal_mulai ? $event->tanggal_mulai->format('Y') : date('Y') }}</div>
    </div>
@endforeach
</body>
</html>