<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 85.6mm 54mm; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; background: #fff; }
        .card { width: 85.6mm; height: 54mm; border: 1.2px solid #005c8a; position: relative; overflow: hidden; background: #ffffff; }
        .head { background: #002b40; color: #ffffff; padding: 2.2mm 3.5mm 1.8mm; border-bottom: 2px solid #f8bb00; }
        .head-table { width: 100%; border-collapse: collapse; }
        .org { font-size: 5.5pt; font-weight: bold; letter-spacing: 0.6px; text-transform: uppercase; color: #f8bb00; }
        .title { font-size: 8.5pt; font-weight: bold; margin-top: 0.3mm; letter-spacing: 0.4px; color: #ffffff; }
        .event { font-size: 6pt; margin-top: 0.3mm; color: #bae6fd; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 78mm; }
        
        .content-table { width: 100%; border-collapse: collapse; padding: 2.5mm 3.5mm; }
        .content-table td { vertical-align: top; }
        
        .photo-cell { width: 17mm; text-align: left; }
        .photo-box { width: 16mm; height: 21mm; border: 1px solid #cbd5e1; background: #f1f5f9; text-align: center; }
        .photo-box img { width: 16mm; height: 21mm; object-fit: cover; }
        .photo-placeholder { font-size: 5pt; color: #64748b; line-height: 21mm; text-align: center; font-weight: bold; }
        
        .info-cell { padding-left: 2.5mm; padding-right: 1.5mm; }
        .name { font-size: 8pt; font-weight: bold; color: #002b40; margin-bottom: 1.2mm; line-height: 1.2; }
        .row { font-size: 5.8pt; margin-bottom: 0.8mm; color: #334155; line-height: 1.2; }
        .row b { color: #005c8a; font-weight: bold; }
        
        .qr-cell { width: 18mm; text-align: center; }
        .qr-img { width: 17mm; height: 17mm; }
        .qr-desc { font-size: 4.8pt; color: #64748b; margin-top: 0.5mm; font-weight: 600; text-transform: uppercase; letter-spacing: 0.2px; }
        
        .foot { position: absolute; bottom: 0; left: 0; right: 0; background: #f0f9ff; border-top: 1px solid #e0f2fe; padding: 1.2mm 3.5mm; font-size: 5pt; color: #005c8a; font-weight: bold; text-align: center; letter-spacing: 0.3px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="head">
            <div class="org">MPKSDI PDM KARANGANYAR</div>
            <div class="title">ID CARD PESERTA</div>
            <div class="event">{{ $event->nama_event }}</div>
        </div>

        <table class="content-table">
            <tr>
                <td class="photo-cell">
                    <div class="photo-box">
                        @if(!empty($fotoDataUri))
                            <img src="{{ $fotoDataUri }}" alt="Pasfoto">
                        @else
                            <div class="photo-placeholder">FOTO</div>
                        @endif
                    </div>
                </td>
                <td class="info-cell">
                    <div class="name">{{ $ep->anggota->nama_lengkap }}</div>
                    <div class="row"><b>No. Peserta:</b> {{ sprintf('%03d', $ep->id) }}</div>
                    <div class="row"><b>Cabang:</b> {{ $ep->anggota->kecamatan }}</div>
                    <div class="row"><b>Daerah:</b> {{ $ep->anggota->kabupaten }}</div>
                    <div class="row"><b>Pelaksanaan:</b> {{ $event->tanggal_mulai->format('d/m/Y') }}</div>
                </td>
                <td class="qr-cell">
                    <img class="qr-img" src="{{ $qrPng }}" alt="QR">
                    <div class="qr-desc">Scan Presensi</div>
                </td>
            </tr>
        </table>

        <div class="foot">KARTU WAJIB DIKENAKAN SELAMA KEGIATAN BAITUL ARQAM</div>
    </div>
</body>
</html>
