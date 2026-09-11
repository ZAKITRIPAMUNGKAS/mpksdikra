<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; margin: 0; }
        .cert { border: 4px double #005c8a; border-radius: 12px; padding: 26px 40px; height: 142mm; position: relative; background: #ffffff; }
        .inner-border { border: 1px solid #f8bb00; padding: 20px 30px; height: 130mm; }
        .org { text-align: center; font-size: 11.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #002b40; font-weight: bold; line-height: 1.4; }
        h1 { text-align: center; font-size: 26px; margin: 10px 0 2px; letter-spacing: 4px; color: #005c8a; font-weight: 800; }
        .num { text-align: center; font-size: 10px; color: #64748b; font-weight: 600; }
        .body { text-align: center; margin-top: 18px; font-size: 12px; line-height: 1.7; color: #334155; }
        .name { font-size: 22px; font-weight: bold; margin: 10px 0 4px; color: #002b40; }
        .grade { display: inline-block; margin-top: 8px; padding: 5px 24px; border: 1.5px solid #005c8a; background: #f0f9ff; border-radius: 999px; font-weight: bold; color: #005c8a; font-size: 13px; letter-spacing: 0.5px; }
        .foot { position: absolute; bottom: 24px; left: 0; right: 0; display: flex; justify-content: space-between; padding: 0 65px; font-size: 10px; }
        .verify { font-size: 8px; color: #94a3b8; word-break: break-all; max-width: 40%; font-family: monospace; }
        .sign { text-align: center; color: #1e293b; }
    </style>
</head>
<body>
    <div class="cert">
        <div class="inner-border">
            <div class="org">Majelis Pembinaan Kader dan Sumber Daya Insani<br>Pimpinan Daerah Muhammadiyah Karanganyar</div>
            <h1>SERTIFIKAT</h1>
            <div class="num">Nomor: {{ $event->generateNomorSertifikat($peserta->id) }}</div>

            <div class="body">
                Diberikan kepada peserta Baitul Arqam<br>
                <b style="color: #002b40;">{{ $event->nama_event }}</b><br>
                yang diselenggarakan pada {{ $event->tanggal_mulai->format('d M Y') }} — {{ $event->tanggal_selesai->format('d M Y') }}
                @if($event->lokasi)di {{ $event->lokasi }}@endif
                <div class="name">{{ $peserta->anggota->nama_lengkap }}</div>
                dengan predikat kelulusan
                <div class="grade">{{ $nilai->status_kelulusan ?? 'Lulus' }}</div>
            </div>

            <div class="foot">
                <div class="verify">Kode Verifikasi: {{ substr($nilai->verification_hash ?? '-', 0, 24) }}</div>
                <div class="sign">
                    Karanganyar, {{ now()->format('d M Y') }}<br>
                    Ketua MPKSDI PDM Karanganyar<br><br><br>
                    <b>(_______________________)</b>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
