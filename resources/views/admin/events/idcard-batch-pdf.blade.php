<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Baitul Arqam - {{ $event->nama_event }}</title>
    <style>
        @page { size: 265.75pt 383.75pt; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { width: 265.75pt; height: 383.75pt; font-family: DejaVu Sans, sans-serif; }
        .card { width: 265.75pt; height: 383.75pt; position: relative; overflow: hidden; page-break-after: always; }
        .card:last-child { page-break-after: avoid; }
        .bg { position: absolute; inset: 0; width: 265.75pt; height: 383.75pt; z-index: 1; }
        .brand, .title, .event, .photo, .name, .meta, .qr-wrap, .scan, .valid { position: absolute; z-index: 2; text-align: center; }
        .brand { top: 10pt; left: 100pt; width: 66pt; color: #fff; }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-cell { width: 31pt; vertical-align: middle; text-align: center; }
        .brand-divider { width: 1pt; height: 17pt; background: #eab308; }
        .brand-logo { width: 18pt; height: 18pt; vertical-align: middle; }
        .title { top: 38pt; left: 20pt; width: 226pt; color: #fff; font-size: 18pt; font-weight: bold; letter-spacing: 1.1pt; }
        .event { top: 63pt; left: 42pt; width: 182pt; padding: 4pt 8pt; border: 1.2pt solid #eab308; border-radius: 10pt; background: #fff; color: #092c59; font-size: 6.8pt; font-weight: bold; }
        .photo { top: 88pt; left: 88pt; width: 90pt; height: 120pt; padding: 3pt; border: 2.5pt solid #eab308; border-radius: 17pt; background: #fff; overflow: hidden; text-align: center; }
        .photo img { max-width: 100%; max-height: 100%; width: auto; height: 100%; border-radius: 12pt; display: block; margin: 0 auto; object-fit: cover; }
        .name { top: 217pt; left: 25pt; width: 216pt; padding: 7pt 10pt; border-radius: 14pt; background: #062a58; border: 1.5pt solid #eab308; color: #fff; font-size: 13pt; font-weight: bold; line-height: 1.15; }
        .meta { top: 254pt; left: 33pt; width: 200pt; color: #062a58; font-size: 7.2pt; font-weight: bold; line-height: 1.55; }
        .meta .divider { color: #d79a00; padding: 0 5pt; }
        .qr-wrap { top: 268pt; left: 101pt; width: 64pt; height: 64pt; padding: 4pt; background: #fff; border: 1.5pt solid #062a58; border-radius: 8pt; }
        .qr-wrap img { width: 54pt; height: 54pt; }
        .scan { top: 340pt; left: 26pt; width: 214pt; color: #062a58; font-size: 6.2pt; font-weight: bold; }
        .valid { top: 365pt; left: 26pt; width: 214pt; color: #fff; font-size: 5.5pt; font-weight: bold; }
    </style>
</head>
<body>
@php
    $asDataUri = function (string $path): string {
        if (! file_exists($path)) return '';
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'image/png';
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    };
    $background = $asDataUri(public_path('aset/idcard/idcard-satu-sisi-bg.png'));
    $logoMuhammadiyah = $asDataUri(public_path('aset/idcard/logo-muhammadiyah-putih.png'));
    $logoMpksdi = $asDataUri(public_path('aset/idcard/logo-mpksdi-putih.png'));
    $tahun = $event->tanggal_mulai?->format('Y') ?? date('Y');
    $masaBerlaku = trim(($event->tanggal_mulai?->translatedFormat('d F Y') ?? '') . ' – ' . ($event->tanggal_selesai?->translatedFormat('d F Y') ?? ''));
@endphp
@foreach($cards as $card)
    @php $nama = $card['nama']; $fontSize = mb_strlen($nama) > 28 ? '9.5pt' : (mb_strlen($nama) > 20 ? '11pt' : '13pt'); @endphp
    <div class="card">
        @if($background)<img src="{{ $background }}" class="bg" alt="">@endif
        <div class="brand">
            <table class="brand-table" role="presentation">
                <tr>
                    <td class="brand-cell">
                        @if($logoMuhammadiyah)<img src="{{ $logoMuhammadiyah }}" class="brand-logo" alt="Logo PDM Karanganyar">@endif
                    </td>
                    <td class="brand-divider"></td>
                    <td class="brand-cell">
                        @if($logoMpksdi)<img src="{{ $logoMpksdi }}" class="brand-logo" alt="Logo MPKSDI">@endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="title">BAITUL ARQAM</div>
        <div class="event">{{ $event->nama_event }} · Angkatan {{ $tahun }}</div>
        <div class="photo">@if($card['foto'])<img src="{{ $card['foto'] }}" alt="Foto {{ $nama }}">@endif</div>
        <div class="name" style="font-size: {{ $fontSize }};">{{ $nama }}</div>
        <div class="meta">{{ $card['pcm'] ?: 'Utusan Muhammadiyah' }}<span class="divider">|</span>No. Peserta {{ str_pad((string) $card['no_peserta'], 5, '0', STR_PAD_LEFT) }}</div>
        <div class="qr-wrap"><img src="{{ $card['qr'] }}" alt="QR presensi"></div>
        <div class="scan">Pindai QR untuk presensi</div>
        <div class="valid">Berlaku {{ $masaBerlaku }}</div>
    </div>
@endforeach
</body>
</html>
