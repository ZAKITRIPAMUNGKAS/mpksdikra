<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>ID Card PVC - {{ $ep->anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: 54mm 85.6mm;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: #002147;
            color: #ffffff;
            width: 54mm;
            height: 85.6mm;
            overflow: hidden;
            -webkit-print-color-adjust: exact;
        }

        .card-page {
            width: 54mm;
            height: 85.6mm;
            position: relative;
            background-color: #002147;
            overflow: hidden;
            padding: 4mm 3.5mm;
        }

        .page-break {
            page-break-after: always;
        }

        /* Front Side Styling */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }
        .header-table td {
            vertical-align: top;
        }
        .org-tag {
            font-size: 5.5pt;
            font-weight: bold;
            color: #f8bb00;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .org-sub {
            font-size: 4.2pt;
            color: #bae6fd;
            letter-spacing: 0.3px;
        }
        .muh-tag {
            text-align: right;
            font-size: 5pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .identity-box {
            margin-top: 1mm;
            margin-bottom: 2mm;
            position: relative;
            z-index: 10;
        }
        .name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.15;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            max-height: 8mm;
            overflow: hidden;
        }
        .nbm {
            font-size: 6pt;
            font-weight: bold;
            color: #f8bb00;
            margin-top: 0.6mm;
            letter-spacing: 0.3px;
        }
        .role-pill {
            display: inline-block;
            margin-top: 1mm;
            background-color: rgba(255, 255, 255, 0.15);
            border: 0.5px solid rgba(255, 255, 255, 0.25);
            padding: 0.5mm 1.8mm;
            border-radius: 2mm;
            font-size: 4.8pt;
            font-weight: bold;
            color: #e0f2fe;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .photo-container {
            position: absolute;
            bottom: 3.5mm;
            left: 3.5mm;
            right: 3.5mm;
            height: 47mm;
            text-align: center;
            z-index: 10;
        }
        .photo-frame {
            width: 35mm;
            height: 46mm;
            margin: 0 auto;
            border-radius: 4px;
            border: 1.5px solid rgba(255, 255, 255, 0.5);
            background-color: #00365a;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .photo-img {
            width: 35mm;
            height: 46mm;
            object-fit: cover;
            display: block;
        }
        .photo-placeholder {
            line-height: 46mm;
            font-size: 8pt;
            font-weight: bold;
            color: #94a3b8;
            letter-spacing: 1px;
        }

        /* Back Side Styling */
        .back-head {
            margin-bottom: 2mm;
        }
        .back-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .back-id {
            font-size: 6pt;
            font-weight: bold;
            color: #f8bb00;
            margin-top: 0.5mm;
        }

        .rules-box {
            font-size: 4.2pt;
            color: #e2e8f0;
            line-height: 1.35;
            margin-top: 1.5mm;
            margin-bottom: 2.5mm;
            text-align: justify;
        }

        .serial-strip {
            position: absolute;
            left: 2mm;
            top: 30mm;
            bottom: 12mm;
            width: 3mm;
            font-size: 4pt;
            color: rgba(255, 255, 255, 0.45);
            font-family: monospace;
            letter-spacing: 1px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
        }

        .qr-section {
            margin-left: 4mm;
            text-align: center;
            margin-top: 1mm;
        }
        .tap-header {
            font-size: 5pt;
            font-weight: bold;
            color: #f8bb00;
            letter-spacing: 0.5px;
            margin-bottom: 1mm;
        }
        .qr-card-box {
            display: inline-block;
            background: #ffffff;
            padding: 1.5mm;
            border-radius: 3px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
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
            font-size: 4.2pt;
            color: #94a3b8;
            border-top: 0.5px solid rgba(255, 255, 255, 0.15);
            padding-top: 1mm;
        }
        .back-foot b {
            color: #ffffff;
        }
    </style>
</head>
<body>

    {{-- ============================================================== --}}
    {{-- MUKA DEPAN (FRONT SIDE)                                         --}}
    {{-- ============================================================== --}}
    <div class="card-page">
        {{-- Batik / Kawung Vector Motif Background --}}
        <svg style="position: absolute; bottom: -2mm; left: -2mm; width: 58mm; height: 50mm; opacity: 0.22; pointer-events: none; z-index: 1;" viewBox="0 0 200 160">
            <g fill="#38bdf8">
                <path d="M 40,20 C 40,35 25,50 10,50 C 25,50 40,65 40,80 C 40,65 55,50 70,50 C 55,50 40,35 40,20 Z" />
                <circle cx="40" cy="50" r="4" fill="#f8bb00" />
                <path d="M 160,20 C 160,35 145,50 130,50 C 145,50 160,65 160,80 C 160,65 175,50 190,50 C 175,50 160,35 160,20 Z" />
                <circle cx="160" cy="50" r="4" fill="#f8bb00" />
                <path d="M 100,70 C 100,85 85,100 70,100 C 85,100 100,115 100,130 C 100,115 115,100 130,100 C 115,100 100,85 100,70 Z" />
                <circle cx="100" cy="100" r="4" fill="#f8bb00" />
            </g>
        </svg>

        {{-- Top Header Logos --}}
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

        {{-- Name & Identity Details --}}
        <div class="identity-box">
            <div class="name">{{ $ep->anggota->nama_lengkap }}</div>
            <div class="nbm">
                @if($ep->anggota->nbm) NBM {{ $ep->anggota->nbm }} @else ID #{{ sprintf('%03d', $ep->id) }} @endif
                &bull; {{ $ep->anggota->pcm ?: ($ep->anggota->kecamatan ?: 'Karanganyar') }}
            </div>
            <div class="role-pill">PESERTA BAITUL ARQAM</div>
        </div>

        {{-- Portrait Photo Frame --}}
        <div class="photo-container">
            <div class="photo-frame">
                @if(!empty($fotoDataUri))
                    <img class="photo-img" src="{{ $fotoDataUri }}" alt="Pasfoto">
                @else
                    <div class="photo-placeholder">PASFOTO</div>
                @endif
            </div>
        </div>
    </div>

    {{-- PAGE BREAK UNTUK CETAK 2 SISI --}}
    <div class="page-break"></div>

    {{-- ============================================================== --}}
    {{-- MUKA BELAKANG (BACK SIDE)                                      --}}
    {{-- ============================================================== --}}
    <div class="card-page">
        {{-- Batik Motif Background --}}
        <svg style="position: absolute; bottom: 0; right: 0; width: 50mm; height: 45mm; opacity: 0.15; pointer-events: none; z-index: 1;" viewBox="0 0 200 160">
            <g fill="#38bdf8">
                <path d="M 100,30 C 100,50 80,70 60,70 C 80,70 100,90 100,110 C 100,90 120,70 140,70 C 120,70 100,50 100,30 Z" />
                <circle cx="100" cy="70" r="5" fill="#f8bb00" />
            </g>
        </svg>

        {{-- Header Belakang --}}
        <div class="back-head">
            <div class="back-title">{{ $ep->anggota->nama_lengkap }}</div>
            <div class="back-id">
                ID PESERTA: #{{ sprintf('%03d', $ep->id) }} @if($ep->anggota->nbm)&bull; NBM: {{ $ep->anggota->nbm }}@endif
            </div>
        </div>

        {{-- Ketentuan Penggunaan Kartu --}}
        <div class="rules-box">
            Penggunaan kartu ini diatur menurut tata tertib perkaderan Baitul Arqam MPKSDI PDM Karanganyar. Pemegang kartu wajib mengenakan tanda pengenal ini selama kegiatan. Tunjukkan kode QR di bawah untuk verifikasi kehadiran digital di setiap sesi materi dan ibadah. Jika kartu ini ditemukan, mohon diserahkan ke Panitia MPKSDI Karanganyar.
        </div>

        {{-- Vertical Token / Serial on Left --}}
        <div class="serial-strip">
            {{ $ep->qr_token ?? ('ARQAM-EV'.$event->id.'-AG'.$ep->anggota_id) }}
        </div>

        {{-- QR Code Presensi Scanner --}}
        <div class="qr-section">
            <div class="tap-header">&bull; PRESENSI DIGITAL &bull;</div>
            <div class="qr-card-box">
                <img class="qr-card-img" src="{{ $qrPng }}" alt="QR Presensi">
            </div>
        </div>

        {{-- Footer Diterbitkan oleh --}}
        <div class="back-foot">
            Diterbitkan oleh <b>MPKSDI PDM Karanganyar</b><br>
            Sistem Informasi Perkaderan Arqam
        </div>
    </div>

</body>
</html>
