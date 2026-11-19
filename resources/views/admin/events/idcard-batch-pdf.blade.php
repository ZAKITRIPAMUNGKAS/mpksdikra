<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Baitul Arqam - {{ $event->nama_event }}</title>
    <style>
        @page { size: 265.75pt 383.75pt; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { width: 265.75pt; height: 383.75pt; font-family: DejaVu Sans, sans-serif; background: #ffffff; }
        .card { width: 265.75pt; height: 383.75pt; position: relative; overflow: hidden; page-break-after: always; background: #ffffff; }
        .card:last-child { page-break-after: avoid; }
        .bg { position: absolute; inset: 0; width: 265.75pt; height: 383.75pt; z-index: 1; }
        
        /* Layer Helper */
        .layer { position: absolute; z-index: 2; text-align: center; }

        /* 1. Executive Header Plaque */
        .header-plaque {
            top: 10pt;
            left: 20pt;
            width: 225.75pt;
            background: #041c38;
            border: 1.2pt solid #f8bb00;
            border-radius: 8pt;
            padding: 5pt 8pt 6pt 8pt;
            text-align: center;
        }
        .brand-table { margin: 0 auto 3pt auto; border-collapse: collapse; }
        .brand-logo { width: 22pt; height: 22pt; display: block; margin: 0 auto; }
        .brand-cell { padding: 0 5pt; vertical-align: middle; }
        .brand-bullet { font-size: 8pt; color: #f8bb00; vertical-align: middle; padding: 0 2pt; line-height: 1; text-align: center; }
        
        .title-main { color: #ffffff; font-size: 13pt; font-weight: bold; letter-spacing: 2pt; text-transform: uppercase; margin: 1pt 0; }
        .subtitle-event { color: #f8bb00; font-size: 5.8pt; font-weight: bold; letter-spacing: 0.6pt; text-transform: uppercase; line-height: 1.25; }

        /* 2. Photo Frame (Locked 3:4 Aspect Ratio, Width: 92pt) */
        .photo-wrap {
            top: 79pt;
            left: 86.875pt;
            width: 92pt;
            height: 122.67pt;
            padding: 2.5pt;
            background: #ffffff;
            border: 2pt solid #f8bb00;
            border-radius: 9pt;
            text-align: center;
        }
        .photo-img { width: 100%; height: 100%; border-radius: 6pt; display: block; margin: 0 auto; object-fit: cover; }
        
        /* Photo Badge */
        .photo-badge { top: 193pt; left: 0; width: 265.75pt; text-align: center; }
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

        /* 3. Identity Block (Clean 2-Line Architecture) */
        .identity-wrap { top: 211pt; left: 15pt; width: 235.75pt; text-align: center; }
        .kader-name { font-weight: bold; color: #062a58; line-height: 1.2; letter-spacing: 0.1pt; }
        .kader-sub {
            margin-top: 3pt;
            font-size: 6.8pt;
            font-weight: bold;
            color: #854d0e;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
        }
        .sub-dot { color: #f8bb00; padding: 0 4pt; font-weight: bold; }

        /* 4. Unified QR Credential Card (Grid-Aligned to Photo Frame: Width 92pt, Left 86.875pt) */
        .qr-card-wrap {
            top: 247pt;
            left: 86.875pt;
            width: 92pt;
            background: #ffffff;
            border: 1.2pt solid #062a58;
            border-radius: 8pt;
            padding: 4.5pt 4pt 3.5pt 4pt;
            text-align: center;
        }
        .qr-img { width: 50pt; height: 50pt; display: block; margin: 0 auto; }
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

        /* 5. Footer (Clean White Zone) */
        .footer-wrap { top: 334pt; left: 25pt; width: 215.75pt; text-align: center; }
        .footer-box { display: inline-block; padding: 3pt 12pt; background: #ffffff; border: 0.8pt solid #e2e8f0; border-radius: 6pt; }
        .validity-text { font-size: 5.6pt; font-weight: bold; color: #062a58; letter-spacing: 0.2pt; }
        .authority-text { font-size: 4.8pt; font-weight: bold; color: #b45309; letter-spacing: 0.6pt; margin-top: 1pt; text-transform: uppercase; }
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
    $logoMuhammadiyah = file_exists(public_path('aset/idcard/badge-muhammadiyah.png'))
        ? $asDataUri(public_path('aset/idcard/badge-muhammadiyah.png'))
        : $asDataUri(public_path('aset/logo-muhammadiyah.png'));
    $logoMpksdi = file_exists(public_path('aset/idcard/badge-mpksdi.png'))
        ? $asDataUri(public_path('aset/idcard/badge-mpksdi.png'))
        : $asDataUri(public_path('aset/logo-mpksdi.png'));
    $tahun = $event->tanggal_mulai?->format('Y') ?? date('Y');
    $masaBerlaku = trim(($event->tanggal_mulai?->translatedFormat('d F Y') ?? '') . ' – ' . ($event->tanggal_selesai?->translatedFormat('d F Y') ?? ''));

    // Bersihkan judul event agar tidak terjadi redundansi "Baitul Arqam Baitul Arqam" atau tahun ganda
    $rawEventName = $event->nama_event;
    $cleanSubtitle = preg_replace('/^Baitul\s+Arqam\s+/i', '', $rawEventName);
    if (!str_contains($cleanSubtitle, $tahun)) {
        $cleanSubtitle .= ' · ' . $tahun;
    }
@endphp
@foreach($cards as $card)
    @php
        $nama = $card['nama'];
        $len = mb_strlen($nama);
        $nameFontSize = $len > 28 ? '10pt' : ($len > 22 ? '11.5pt' : '13pt');
        $pcm = $card['pcm'] ?: 'Utusan Muhammadiyah';
        $noPeserta = str_pad((string) $card['no_peserta'], 5, '0', STR_PAD_LEFT);
        $nbm = $card['nbm'] ?? null;
    @endphp
    <div class="card">
        {{-- Background Graphic Template --}}
        @if($background)<img src="{{ $background }}" class="bg" alt="">@endif

        {{-- 1. Executive Header Plaque --}}
        <div class="layer header-plaque">
            <table class="brand-table" role="presentation">
                <tr>
                    <td class="brand-cell">
                        @if($logoMuhammadiyah)<img src="{{ $logoMuhammadiyah }}" class="brand-logo" alt="Logo Muhammadiyah">@endif
                    </td>
                    <td class="brand-bullet">&bull;</td>
                    <td class="brand-cell">
                        @if($logoMpksdi)<img src="{{ $logoMpksdi }}" class="brand-logo" alt="Logo MPKSDI">@endif
                    </td>
                </tr>
            </table>
            <div class="title-main">BAITUL ARQAM</div>
            <div class="subtitle-event">{{ $cleanSubtitle }}</div>
        </div>

        {{-- 2. Photo Frame with 3:4 Lock & Kader Badge --}}
        <div class="layer photo-wrap">
            @if($card['foto'])
                <img src="{{ $card['foto'] }}" class="photo-img" alt="Foto {{ $nama }}">
            @endif
        </div>
        <div class="layer photo-badge">
            <span class="badge-kader">Peserta Baitul Arqam</span>
        </div>

        {{-- 3. Identity Block (2-Line Architecture) --}}
        <div class="layer identity-wrap">
            <div class="kader-name" style="font-size: {{ $nameFontSize }};">{{ $nama }}</div>
            <div class="kader-sub">
                <span>{{ $pcm }}</span>
                @if($nbm)
                    <span class="sub-dot">&bull;</span>
                    <span>NBM: {{ $nbm }}</span>
                @endif
            </div>
        </div>

        {{-- 4. Unified QR Credential Card --}}
        <div class="layer qr-card-wrap">
            <img src="{{ $card['qr'] }}" class="qr-img" alt="QR Presensi">
            <div class="qr-label">Presensi Kehadiran</div>
            <div class="qr-no">No. Peserta: {{ $noPeserta }}</div>
        </div>

        {{-- 5. Footer (Clean White Zone) --}}
        <div class="layer footer-wrap">
            <div class="footer-box">
                <div class="validity-text">Masa Berlaku: {{ $masaBerlaku }}</div>
                <div class="authority-text">MPKSDI PDM Karanganyar</div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
