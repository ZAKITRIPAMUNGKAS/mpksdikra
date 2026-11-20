<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Baitul Arqam - {{ $event->nama_event }}</title>
    @php
        $cardList = collect($cards);
        $isSingle = $isSingle ?? ($cardList->count() === 1 && !request()->routeIs('admin.events.idcards.batch'));
    @endphp
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @if($isSingle)
            @page { size: 265.75pt 383.75pt; margin: 0; }
            html, body { width: 265.75pt; height: 383.75pt; font-family: DejaVu Sans, sans-serif; background: #ffffff; }
            .card { width: 265.75pt; height: 383.75pt; position: relative; overflow: hidden; page-break-after: always; background: #ffffff; }
            .card:last-child { page-break-after: avoid; }
            .single-bg { position: absolute; inset: 0; width: 265.75pt; height: 383.75pt; z-index: 1; }
        @else
            @page { size: a4 portrait; margin: 0; }
            html, body { width: 595.28pt; height: 841.89pt; font-family: DejaVu Sans, sans-serif; background: #ffffff; }
            .sheet { width: 595.28pt; height: 841.89pt; position: relative; page-break-after: always; background: #ffffff; overflow: hidden; }
            .sheet:last-child { page-break-after: avoid; }
            .card-bg { position: absolute; width: 265.75pt; height: 383.75pt; z-index: 1; border-radius: 8pt; }
            .card-cut-guide { position: absolute; width: 265.75pt; height: 383.75pt; z-index: 10; border: 0.6pt dashed #94a3b8; border-radius: 8pt; }
        @endif
        
        /* Layer Helper */
        .layer { position: absolute; z-index: 2; text-align: center; }

        /* 1. Header (tinggi tetap agar posisi foto stabil) */
        .header-plaque {
            width: 225.75pt;
            height: 64pt;
            background: #041c38;
            border: 1.2pt solid #f8bb00;
            border-radius: 8pt;
        }
        .header-table { width: 100%; height: 61.6pt; border-collapse: collapse; }
        .header-cell { vertical-align: middle; text-align: center; padding: 0 8pt; }
        .brand-table { margin: 0 auto 2pt auto; border-collapse: collapse; }
        .brand-logo { width: 20pt; height: 20pt; display: block; margin: 0 auto; }
        .brand-cell { padding: 0 5pt; vertical-align: middle; }
        .brand-bullet { font-size: 8pt; color: #f8bb00; vertical-align: middle; padding: 0 2pt; line-height: 1; text-align: center; }

        .title-main { color: #ffffff; font-size: 12.5pt; font-weight: bold; letter-spacing: 2pt; text-transform: uppercase; line-height: 1.15; }
        .subtitle-event { color: #f8bb00; font-size: 5.6pt; font-weight: bold; letter-spacing: 0.5pt; text-transform: uppercase; line-height: 1.3; margin-top: 1pt; }

        /* 2. Photo Frame (Locked 3:4 Aspect Ratio, Width: 92pt) */
        .photo-wrap {
            width: 92pt;
            height: 122.67pt;
            padding: 2.5pt;
            background: #ffffff;
            border: 2pt solid #f8bb00;
            border-radius: 9pt;
            text-align: center;
        }
        .photo-img { width: 100%; height: 100%; border-radius: 6pt; display: block; margin: 0 auto; }

        .photo-badge { width: 265.75pt; text-align: center; }
        .badge-kader {
            display: inline-block;
            padding: 2.2pt 10pt;
            background: #062a58;
            border: 1pt solid #f8bb00;
            border-radius: 4pt;
            color: #ffffff;
            font-size: 5.6pt;
            font-weight: bold;
            letter-spacing: 0.9pt;
            text-transform: uppercase;
        }

        /* 3. Identity Block: nama 1 baris (font menyesuaikan), gelar, lalu PCM/NBM */
        .identity-wrap { width: 241.75pt; height: 41pt; }
        .identity-table { width: 100%; height: 41pt; border-collapse: collapse; }
        .identity-cell { vertical-align: middle; text-align: center; }
        .kader-name { font-weight: bold; color: #062a58; line-height: 1.15; letter-spacing: 0.2pt; white-space: nowrap; }
        .kader-gelar { font-size: 6.6pt; color: #334155; line-height: 1.2; margin-top: 0; }
        .kader-sub {
            margin-top: 2pt;
            font-size: 6.4pt;
            font-weight: bold;
            color: #854d0e;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .sub-dot { color: #f8bb00; padding: 0 4pt; font-weight: bold; }

        /* 4. Unified QR Credential Card (Grid-Aligned to Photo Frame: Width 92pt) */
        .qr-card-wrap {
            width: 92pt;
            background: #ffffff;
            border: 1.2pt solid #062a58;
            border-radius: 8pt;
            padding: 3.5pt 4pt 3pt 4pt;
            text-align: center;
        }
        .qr-img { width: 44pt; height: 44pt; display: block; margin: 0 auto; }
        .qr-label {
            font-size: 5.4pt;
            font-weight: bold;
            color: #062a58;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
            margin-top: 2pt;
        }
        .qr-no {
            font-size: 4.8pt;
            color: #64748b;
            letter-spacing: 0.3pt;
            margin-top: 0.8pt;
        }

        /* 5. Footer */
        .footer-wrap { width: 215.75pt; text-align: center; }
        .footer-box { display: inline-block; padding: 3pt 12pt; background: #ffffff; border: 0.8pt solid #e2e8f0; border-radius: 6pt; }
        .validity-text { font-size: 5.6pt; font-weight: bold; color: #062a58; letter-spacing: 0.2pt; }
        .authority-text { font-size: 4.8pt; font-weight: bold; color: #b45309; letter-spacing: 0.6pt; margin-top: 1pt; text-transform: uppercase; }
    </style>
</head>
<body>
@php
    $asDataUri = function (string $path): string {
        if (! file_exists($path)) return '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png',
        };
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    };

    $bgFile = file_exists(public_path('aset/idcard/idcard-satu-sisi-bg.jpg'))
        ? public_path('aset/idcard/idcard-satu-sisi-bg.jpg')
        : public_path('aset/idcard/idcard-satu-sisi-bg.png');
    $background = $asDataUri($bgFile);

    $logoMFile = file_exists(public_path('aset/idcard/badge-muhammadiyah.png'))
        ? public_path('aset/idcard/badge-muhammadiyah.png')
        : (file_exists(public_path('aset/logo-muhammadiyah.png')) ? public_path('aset/logo-muhammadiyah.png') : public_path('assets/logo/muhammadiyah.png'));
    $logoMuhammadiyah = $asDataUri($logoMFile);

    $logoKFile = file_exists(public_path('aset/idcard/badge-mpksdi.png'))
        ? public_path('aset/idcard/badge-mpksdi.png')
        : (file_exists(public_path('aset/logo-mpksdi.png')) ? public_path('aset/logo-mpksdi.png') : public_path('assets/logo/mpksdi.png'));
    $logoMpksdi = $asDataUri($logoKFile);

    $tahun = $event->tanggal_mulai?->format('Y') ?? date('Y');
    $masaBerlaku = trim(($event->tanggal_mulai?->translatedFormat('d F Y') ?? '') . ' – ' . ($event->tanggal_selesai?->translatedFormat('d F Y') ?? ''));

    // Bersihkan judul event agar tidak terjadi redundansi "Baitul Arqam Baitul Arqam" atau tahun ganda
    $rawEventName = $event->nama_event;
    $cleanSubtitle = trim(preg_replace('/^Baitul\s+Arq[ao]m\s*/iu', '', $rawEventName));
    if (!str_contains($cleanSubtitle, $tahun)) {
        $cleanSubtitle .= ' · ' . $tahun;
    }

    // Grid 2x2 pada A4 Portrait (595.28pt x 841.89pt)
    // Dimensi kartu: 265.75pt x 383.75pt (standar B3)
    $slots = [
        0 => ['top' => 24.79, 'left' => 21.26],
        1 => ['top' => 24.79, 'left' => 308.27],
        2 => ['top' => 433.33, 'left' => 21.26],
        3 => ['top' => 433.33, 'left' => 308.27],
    ];
@endphp

@if($isSingle)
    {{-- Mode kartu tunggal (unduhan portal peserta) --}}
    @foreach($cardList as $card)
        <div class="card">
            @if($background)
                <img src="{{ $background }}" class="single-bg" alt="">
            @endif
            @include('admin.events.partials.idcard-face', ['card' => $card, 'top' => 0, 'left' => 0])
        </div>
    @endforeach
@else
    {{-- Mode massal: grid 2x2 A4 dengan garis potong --}}
    @foreach($cardList->chunk(4) as $chunk)
        <div class="sheet">
            @foreach($chunk as $card)
                @php $slot = $slots[$loop->index]; @endphp
                @if($background)
                    <img src="{{ $background }}" class="card-bg" style="top: {{ $slot['top'] }}pt; left: {{ $slot['left'] }}pt;" alt="">
                @endif
                <div class="card-cut-guide" style="top: {{ $slot['top'] }}pt; left: {{ $slot['left'] }}pt;"></div>
                @include('admin.events.partials.idcard-face', ['card' => $card, 'top' => $slot['top'], 'left' => $slot['left']])
            @endforeach
        </div>
    @endforeach
@endif
</body>
</html>
