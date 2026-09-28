<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card Peserta (PVC 2 Sisi Batch) - {{ $event->nama_event }}</title>
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
            background-color: #f8fafc;
            color: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        .page {
            width: 100%;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        .sheet-header {
            text-align: center;
            margin-bottom: 3mm;
            padding-bottom: 1.5mm;
            border-bottom: 0.5px solid #cbd5e1;
            color: #0f172a;
        }
        .sheet-title {
            font-size: 8pt;
            font-weight: bold;
            color: #002b40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sheet-sub {
            font-size: 5.5pt;
            color: #64748b;
        }

        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5mm 4mm;
            margin: 0 auto;
        }
        .grid-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
            text-align: center;
        }

        /* CR80 PVC Card Standards: 54mm x 85.6mm */
        .card-pvc {
            display: inline-block;
            width: 54mm;
            height: 85.6mm;
            background-color: #002147;
            border: 0.8px dashed #94a3b8;
            border-radius: 4mm;
            position: relative;
            overflow: hidden;
            text-align: left;
            padding: 4mm 3.5mm;
            box-sizing: border-box;
        }

        /* Front Side Styles */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2.5mm;
        }
        .header-table td {
            vertical-align: top;
        }
        .org-tag {
            font-size: 5pt;
            font-weight: bold;
            color: #f8bb00;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .org-sub {
            font-size: 3.8pt;
            color: #bae6fd;
            letter-spacing: 0.2px;
        }
        .muh-tag {
            text-align: right;
            font-size: 4.8pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.4px;
        }

        .identity-box {
            margin-top: 1mm;
            margin-bottom: 2mm;
            position: relative;
            z-index: 10;
        }
        .name {
            font-size: 8pt;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            max-height: 7.5mm;
            overflow: hidden;
        }
        .nbm {
            font-size: 5.5pt;
            font-weight: bold;
            color: #f8bb00;
            margin-top: 0.5mm;
            letter-spacing: 0.2px;
        }
        .role-pill {
            display: inline-block;
            margin-top: 0.8mm;
            background-color: rgba(255, 255, 255, 0.15);
            border: 0.5px solid rgba(255, 255, 255, 0.25);
            padding: 0.4mm 1.6mm;
            border-radius: 2mm;
            font-size: 4.5pt;
            font-weight: bold;
            color: #e0f2fe;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .photo-container {
            position: absolute;
            bottom: 3.5mm;
            left: 3.5mm;
            right: 3.5mm;
            height: 46mm;
            text-align: center;
            z-index: 10;
        }
        .photo-frame {
            width: 35mm;
            height: 45mm;
            margin: 0 auto;
            border-radius: 3px;
            border: 1.2px solid rgba(255, 255, 255, 0.5);
            background-color: #00365a;
            overflow: hidden;
        }
        .photo-img {
            width: 35mm;
            height: 45mm;
            object-fit: cover;
            display: block;
        }
        .photo-ph {
            line-height: 45mm;
            font-size: 7.5pt;
            font-weight: bold;
            color: #94a3b8;
            letter-spacing: 1px;
            text-align: center;
        }

        /* Back Side Styles */
        .back-head {
            margin-bottom: 2mm;
        }
        .back-title {
            font-size: 7pt;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .back-id {
            font-size: 5.5pt;
            font-weight: bold;
            color: #f8bb00;
            margin-top: 0.4mm;
        }

        .rules-box {
            font-size: 4pt;
            color: #e2e8f0;
            line-height: 1.35;
            margin-top: 1.5mm;
            margin-bottom: 2mm;
            text-align: justify;
        }

        .serial-strip {
            position: absolute;
            left: 1.5mm;
            top: 28mm;
            bottom: 12mm;
            width: 3mm;
            font-size: 3.8pt;
            color: rgba(255, 255, 255, 0.4);
            font-family: monospace;
            letter-spacing: 0.8px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }

        .qr-section {
            margin-left: 3.5mm;
            text-align: center;
            margin-top: 1mm;
        }
        .tap-header {
            font-size: 4.8pt;
            font-weight: bold;
            color: #f8bb00;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        .qr-card-box {
            display: inline-block;
            background: #ffffff;
            padding: 1.2mm;
            border-radius: 2.5px;
        }
        .qr-card-img {
            width: 25mm;
            height: 25mm;
            display: block;
        }

        .back-foot {
            position: absolute;
            bottom: 2.5mm;
            left: 3.5mm;
            right: 3.5mm;
            text-align: center;
            font-size: 4pt;
            color: #94a3b8;
            border-top: 0.5px solid rgba(255, 255, 255, 0.15);
            padding-top: 0.8mm;
        }
        .back-foot b {
            color: #ffffff;
        }

        .card-label {
            font-size: 5pt;
            color: #64748b;
            text-align: center;
            margin-top: 1mm;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.4px;
        }
    </style>
</head>
<body>

    @php
        $chunks = $cards->chunk(3); // 3 peserta per lembar A4 (masing-masing Depan & Belakang = 6 kartu per lembar)
    @endphp

    @foreach($chunks as $chunk)
    <div class="page">
        <div class="sheet-header">
            <div class="sheet-title">LEMBAR CETAK ID CARD BAHAN PVC (2 SISI FULL COLOR)</div>
            <div class="sheet-sub">{{ $event->nama_event }} &bull; Format Standar CR80 (54mm × 85.6mm) &bull; MPKSDI PDM Karanganyar</div>
        </div>

        <table class="grid-table">
            @foreach($chunk as $card)
            <tr>
                {{-- MUKA DEPAN PESERTA --}}
                <td>
                    <div class="card-pvc">
                        {{-- Batik Kawung Motif SVG Vector --}}
                        <svg style="position: absolute; bottom: -2mm; left: -2mm; width: 58mm; height: 48mm; opacity: 0.22; pointer-events: none; z-index: 1;" viewBox="0 0 200 160">
                            <g fill="#38bdf8">
                                <path d="M 40,20 C 40,35 25,50 10,50 C 25,50 40,65 40,80 C 40,65 55,50 70,50 C 55,50 40,35 40,20 Z" />
                                <circle cx="40" cy="50" r="4" fill="#f8bb00" />
                                <path d="M 160,20 C 160,35 145,50 130,50 C 145,50 160,65 160,80 C 160,65 175,50 190,50 C 175,50 160,35 160,20 Z" />
                                <circle cx="160" cy="50" r="4" fill="#f8bb00" />
                                <path d="M 100,70 C 100,85 85,100 70,100 C 85,100 100,115 100,130 C 100,115 115,100 130,100 C 115,100 100,85 100,70 Z" />
                                <circle cx="100" cy="100" r="4" fill="#f8bb00" />
                            </g>
                        </svg>

                        <table class="header-table">
                            <tr>
                                <td style="width: 65%;">
                                    <div class="org-tag">MPKSDI KARANGANYAR</div>
                                    <div class="org-sub">Pimpinan Daerah Muhammadiyah</div>
                                </td>
                                <td style="width: 35%; text-align: right;">
                                    <div class="muh-tag">MUHAMMADIYAH</div>
                                </td>
                            </tr>
                        </table>

                        <div class="identity-box">
                            <div class="name">{{ $card['nama'] }}</div>
                            <div class="nbm">
                                @if(!empty($card['nbm'])) NBM {{ $card['nbm'] }} @else ID #{{ sprintf('%03d', $card['no_peserta']) }} @endif
                                &bull; {{ $card['pcm'] }}
                            </div>
                            <div class="role-pill">{{ $card['jabatan'] ?? 'PESERTA BAITUL ARQAM' }}</div>
                        </div>

                        <div class="photo-container">
                            <div class="photo-frame">
                                @if(!empty($card['foto']))
                                    <img class="photo-img" src="{{ $card['foto'] }}" alt="Pasfoto">
                                @else
                                    <div class="photo-ph">FOTO 3×4</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-label">Muka Depan &bull; {{ $card['nama'] }}</div>
                </td>

                {{-- MUKA BELAKANG PESERTA --}}
                <td>
                    <div class="card-pvc">
                        {{-- Batik Motif SVG --}}
                        <svg style="position: absolute; bottom: 0; right: 0; width: 50mm; height: 42mm; opacity: 0.15; pointer-events: none; z-index: 1;" viewBox="0 0 200 160">
                            <g fill="#38bdf8">
                                <path d="M 100,30 C 100,50 80,70 60,70 C 80,70 100,90 100,110 C 100,90 120,70 140,70 C 120,70 100,50 100,30 Z" />
                                <circle cx="100" cy="70" r="5" fill="#f8bb00" />
                            </g>
                        </svg>

                        <div class="back-head">
                            <div class="back-title">{{ $card['nama'] }}</div>
                            <div class="back-id">
                                ID PESERTA #{{ sprintf('%03d', $card['no_peserta']) }} @if(!empty($card['nbm']))&bull; NBM: {{ $card['nbm'] }}@endif
                            </div>
                        </div>

                        <div class="rules-box">
                            Penggunaan kartu ini diatur menurut ketentuan perkaderan Baitul Arqam MPKSDI PDM Karanganyar. Pemegang kartu wajib mengenakan tanda pengenal ini selama kegiatan. Tunjukkan kode QR di bawah untuk verifikasi kehadiran digital di setiap sesi materi dan ibadah.
                        </div>

                        <div class="serial-strip">
                            {{ $card['qr_token'] ?? ('ARQAM-EV'.$event->id.'-AG'.$card['id']) }}
                        </div>

                        <div class="qr-section">
                            <div class="tap-header">&bull; PRESENSI DIGITAL &bull;</div>
                            <div class="qr-card-box">
                                <img class="qr-card-img" src="{{ $card['qr'] }}" alt="QR Presensi">
                            </div>
                        </div>

                        <div class="back-foot">
                            Diterbitkan oleh <b>MPKSDI PDM Karanganyar</b><br>
                            Sistem Informasi Perkaderan Arqam
                        </div>
                    </div>
                    <div class="card-label">Muka Belakang &bull; {{ $card['nama'] }}</div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
    @endforeach

</body>
</html>
