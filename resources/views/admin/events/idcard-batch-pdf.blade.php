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

        /* 1. Header: pita navy penuh tanpa bingkai */
        .header-band {
            box-sizing: content-box;
            width: 229.75pt;
            height: 70pt;
            padding: 10pt 18pt 0 18pt;
            background: #041c38;
            border-bottom: 1.4pt solid #f8bb00;
            border-top-left-radius: 8pt;
            border-top-right-radius: 8pt;
            text-align: left;
        }
        .brand-table { border-collapse: collapse; }
        .brand-plate { display: inline-block; background: #ffffff; border-radius: 3pt; padding: 1.5pt 3pt; }
        .brand-logo { width: 22pt; height: 22pt; display: inline-block; vertical-align: middle; }
        .brand-logo + .brand-logo { margin-left: 3pt; }
        .brand-org { padding-left: 7pt; vertical-align: middle; color: #dbe3ee; font-size: 5.8pt; font-weight: bold; letter-spacing: 1pt; text-transform: uppercase; line-height: 1.35; }
        .brand-org-sub { color: #f8bb00; letter-spacing: 0.6pt; }
        .title-main { color: #ffffff; font-family: DejaVu Serif, serif; font-size: 16pt; font-weight: bold; letter-spacing: 0.3pt; line-height: 1.1; margin-top: 5pt; }
        .subtitle-event { color: #b9c6da; font-size: 6pt; line-height: 1.35; margin-top: 2pt; }

        /* 2. Foto (rasio 3:4 terkunci; gambar sudah di-crop server-side) */
        .photo-wrap {
            width: 88pt;
            height: 117.33pt;
            padding: 2pt;
            background: #ffffff;
            border: 1.2pt solid #f8bb00;
            border-radius: 3pt;
        }
        .photo-img { width: 100%; height: 100%; display: block; }

        .photo-badge { width: 265.75pt; text-align: center; }
        .badge-kader {
            display: inline-block;
            padding: 2pt 9pt;
            background: #041c38;
            border-radius: 2pt;
            color: #ffffff;
            font-size: 5.6pt;
            font-weight: bold;
            letter-spacing: 1.5pt;
            text-transform: uppercase;
        }

        /* 3. Identity Block: nama 1 baris (font menyesuaikan), gelar, lalu PCM/NBM */
        .identity-wrap { width: 241.75pt; height: 40pt; }
        .identity-table { width: 100%; height: 40pt; border-collapse: collapse; }
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

        /* 4. QR (lebar sama dengan bingkai foto) */
        .qr-card-wrap {
            width: 88pt;
            background: #ffffff;
            border: 0.8pt solid #cbd5e1;
            border-radius: 4pt;
            padding: 3.5pt 4pt 3pt 4pt;
            text-align: center;
        }
        .qr-img { width: 53pt; height: 53pt; display: block; margin: 0 auto; }
        .qr-label {
            font-size: 5.6pt;
            font-weight: bold;
            color: #062a58;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
            margin-top: 2pt;
        }
        .qr-no {
            font-size: 5pt;
            color: #64748b;
            letter-spacing: 0.3pt;
            margin-top: 0.8pt;
        }

        /* 5. Footer: teks saja */
        .footer-wrap { width: 265.75pt; text-align: center; }
        .validity-text { font-size: 5.8pt; font-weight: bold; color: #041c38; letter-spacing: 0.3pt; }
        @if($isSingle) .header-band { border-top-left-radius: 0; border-top-right-radius: 0; } @endif
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

    $logoMuhammadiyah = $asDataUri(public_path('aset/logo-muhammadiyah.png'));
    $logoMpksdi = $asDataUri(file_exists(public_path('aset/idcard/logo-mpksdi-warna.png'))
        ? public_path('aset/idcard/logo-mpksdi-warna.png')
        : public_path('aset/logo-mpksdi.png'));

    $tahun = $event->tanggal_mulai?->format('Y') ?? date('Y');
    $mulai = $event->tanggal_mulai;
    $selesai = $event->tanggal_selesai;
    if ($mulai && $selesai && $mulai->format('Y-m') === $selesai->format('Y-m')) {
        $masaBerlaku = $mulai->format('j').'–'.$selesai->translatedFormat('j F Y');
    } else {
        $masaBerlaku = trim(($mulai?->translatedFormat('j F Y') ?? '').' – '.($selesai?->translatedFormat('j F Y') ?? ''), ' –');
    }

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
