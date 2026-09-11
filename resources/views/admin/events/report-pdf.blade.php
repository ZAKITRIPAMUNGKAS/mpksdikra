<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #12251B; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        h2 { font-size: 11px; margin: 14px 0 6px; }
        .sub { color: #555; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5d0; padding: 4px 6px; text-align: center; }
        th { background: #e7f3ec; font-size: 9px; }
        td.left { text-align: left; }
    </style>
</head>
<body>
    <h1>Laporan Baitul Arqam — {{ $event->nama_event }}</h1>
    <p class="sub">{{ $event->tanggal_mulai->format('d M Y') }} — {{ $event->tanggal_selesai->format('d M Y') }} @if($event->lokasi)· {{ $event->lokasi }} @endif</p>

    <h2>Rekapitulasi Nilai Akhir</h2>
    <table>
        <thead>
            <tr>
                <th>#</th><th class="left">Nama</th><th>Pretest</th><th>Posttest</th><th>N-Gain</th>
                <th>Afektif</th><th>Psikomotor</th><th>Kehadiran</th><th>NA</th><th>Kelulusan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ranking as $p)
            @php $ng = $p->nilai_pretest >= 100 ? 0 : round(($p->nilai_posttest - $p->nilai_pretest) / (100 - $p->nilai_pretest), 3); @endphp
            <tr>
                <td>{{ $p->ranking ?? '-' }}</td>
                <td class="left">{{ $p->anggota->nama_lengkap }}</td>
                <td>{{ number_format($p->nilai_pretest, 1) }}</td>
                <td>{{ number_format($p->nilai_posttest, 1) }}</td>
                <td>{{ number_format($ng, 2) }}</td>
                <td>{{ number_format($p->nilai_afektif, 1) }}</td>
                <td>{{ number_format($p->nilai_psikomotor, 1) }}</td>
                <td>{{ number_format($p->nilai_kehadiran, 1) }}%</td>
                <td><b>{{ number_format($p->nilai_akhir, 2) }}</b></td>
                <td>{{ $p->status_kelulusan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="sub" style="margin-top: 12px">
        NA = rerata(posttest, afektif, psikomotor) — sementara menunggu rumus resmi (TODO keputusan bisnis).
        Kehadiran = syarat kelulusan: 100% Lulus, ≥75% Lulus Bersyarat.
    </p>
</body>
</html>
