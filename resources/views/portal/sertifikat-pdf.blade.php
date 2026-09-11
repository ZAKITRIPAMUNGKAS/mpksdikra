<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #12251B; margin: 0; }
        .cert { border: 3px double #0E6B3F; border-radius: 12px; padding: 28px 40px; height: 140mm; position: relative; }
        .org { text-align: center; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #0E6B3F; font-weight: bold; }
        h1 { text-align: center; font-size: 26px; margin: 10px 0 2px; letter-spacing: 4px; }
        .num { text-align: center; font-size: 10px; color: #555; }
        .body { text-align: center; margin-top: 22px; font-size: 12px; line-height: 1.7; }
        .name { font-size: 22px; font-weight: bold; margin: 10px 0 4px; }
        .grade { display: inline-block; margin-top: 10px; padding: 6px 22px; border: 2px solid #0E6B3F; border-radius: 999px; font-weight: bold; color: #0E6B3F; font-size: 13px; }
        .foot { position: absolute; bottom: 26px; left: 0; right: 0; display: flex; justify-content: space-between; padding: 0 60px; font-size: 10px; }
        .verify { font-size: 8px; color: #888; word-break: break-all; max-width: 40%; }
    </style>
</head>
<body>
    <div class="cert">
        <div class="org">Majelis Pendidikan Kader dan Sumber Daya Insani<br>PCM Karanganyar</div>
        <h1>SERTIFIKAT</h1>
        <div class="num">Nomor: {{ sprintf('%03d', $ep->id) }}/BA-MPKSDI/{{ $event->tanggal_mulai->format('Y') }}</div>

        <div class="body">
            Diberikan kepada peserta Baitul Arqam<br>
            <b>{{ $event->nama_event }}</b><br>
            yang diselenggarakan pada {{ $event->tanggal_mulai->format('d M Y') }} — {{ $event->tanggal_selesai->format('d M Y') }}
            @if($event->lokasi)di {{ $event->lokasi }}@endif
            <div class="name">{{ $ep->anggota->nama_lengkap }}</div>
            dengan predikat kelulusan
            <div class="grade">{{ $nilai->status_kelulusan ?? 'Lulus' }}</div>
        </div>

        <div class="foot">
            <div class="verify">Kode verifikasi: {{ substr($nilai->verification_hash ?? '-', 0, 24) }}</div>
            <div style="text-align: center">
                Karanganyar, {{ $event->tanggal_selesai->format('d M Y') }}<br>
                Ketua MPKSDI PCM Karanganyar<br><br><br>
                <b>(_______________________)</b>
            </div>
        </div>
    </div>
</body>
</html>
