<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Baitul Arqam - {{ $ep->anggota->nama_lengkap }}</title>
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
            vertical-align: bottom;
        }

        .photo-img-cutout {
            max-width: 100%;
            max-height: 100%;
            display: inline-block;
        }

        .photo-box-original {
            text-align: right;
        }

        .photo-img-original {
            width: 112pt;
            height: 149.3pt;
            max-width: 112pt;
            max-height: 149.3pt;
            border-radius: 8pt;
            border: 2pt solid #FFFFFF;
            margin-top: 28pt;
            margin-right: 12pt;
            display: inline-block;
            object-fit: cover;
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
    $resolveDataUri = function (array $candidates): string {
        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                $mime = str_ends_with(strtolower($path), '.jpg') || str_ends_with(strtolower($path), '.jpeg') ? 'image/jpeg' : 'image/png';
                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
            }
        }
        return '';
    };

    $bgFrontDataUri = $resolveDataUri([
        public_path('aset/idcard/front-bg.png'),
        public_path('assets/2.png'),
        public_path('aset/2.png'),
    ]);
    $fgFrontDataUri = $resolveDataUri([
        public_path('aset/idcard/front-overlay.png'),
        public_path('assets/3.png'),
        public_path('aset/3.png'),
    ]);
    $bgBackDataUri = $resolveDataUri([
        public_path('aset/idcard/back.png'),
        public_path('assets/1.png'),
        public_path('aset/1.png'),
    ]);

    $defaultPhoto = $resolveDataUri([
        public_path('aset/ketua-mpksdi-cutout.png'),
        public_path('aset/ketua-mpksdi.png'),
    ]);

    $nama = $ep->anggota->nama_lengkap;
    $namaLen = mb_strlen($nama);
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
    $photoMode = $fotoMode ?? 'cutout';
    $photoSrc = $fotoDataUri ?: $defaultPhoto;
@endphp

<!-- FRONT CARD -->
<div class="card-page">
    @if($bgFrontDataUri)
    <img src="{{ $bgFrontDataUri }}" class="card-bg" alt="">
    @endif
    <div class="photo-box photo-box-{{ $photoMode }}">
        <img src="{{ $photoSrc }}" class="photo-img photo-img-{{ $photoMode }}" alt="Foto">
    </div>
    @if($fgFrontDataUri)
    <img src="{{ $fgFrontDataUri }}" class="card-overlay" alt="">
    @endif
    
    <div class="front-status">PESERTA</div>
    <div class="front-nama" style="font-size: {{ $frontFontSize }};">{{ $nama }}</div>
    <div class="front-nbm">{{ $ep->anggota->nbm ?: '-' }}</div>
</div>

<!-- BACK CARD -->
<div class="card-page">
    @if($bgBackDataUri)
    <img src="{{ $bgBackDataUri }}" class="card-bg" alt="">
    @endif
    
    <div class="qr-box">
        <img src="{{ $qrPng }}" class="qr-img" alt="QR Code">
    </div>

    <div class="back-val back-nama" style="font-size: {{ $backFontSize }};">{{ $nama }}</div>
    <div class="back-val back-nbm">{{ $ep->anggota->nbm ?: '-' }}</div>
    <div class="back-val back-status">Peserta</div>
    <div class="back-val back-kegiatan">{{ $event->nama_event ?: 'Baitul Arqam' }}</div>
    <div class="back-val back-waktu">{{ $event->lokasi ?: 'Karanganyar' }}, {{ $event->tanggal_mulai ? $event->tanggal_mulai->format('Y') : date('Y') }}</div>
</div>
</body>
</html>