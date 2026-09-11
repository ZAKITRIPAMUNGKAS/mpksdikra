<table>
    <thead>
        <tr>
            <th>Ranking</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Cabang</th>
            <th>Pretest</th>
            <th>Posttest</th>
            <th>N-Gain</th>
            <th>Afektif</th>
            <th>Psikomotor</th>
            <th>Keaktifan</th>
            <th>Kehadiran</th>
            <th>Nilai Akhir</th>
            <th>Kelulusan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ranking as $p)
        @php $ng = $p->nilai_pretest >= 100 ? 0 : round(($p->nilai_posttest - $p->nilai_pretest) / (100 - $p->nilai_pretest), 3); @endphp
        <tr>
            <td>{{ $p->ranking ?? '-' }}</td>
            <td>{{ $p->anggota->nama_lengkap }}</td>
            <td>{{ $p->anggota->nik }}</td>
            <td>{{ $p->anggota->kecamatan }}</td>
            <td>{{ number_format($p->nilai_pretest, 1) }}</td>
            <td>{{ number_format($p->nilai_posttest, 1) }}</td>
            <td>{{ number_format($ng, 2) }}</td>
            <td>{{ number_format($p->nilai_afektif, 1) }}</td>
            <td>{{ number_format($p->nilai_psikomotor, 1) }}</td>
            <td>{{ number_format($p->nilai_keaktifan ?? 0, 1) }}</td>
            <td>{{ number_format($p->nilai_kehadiran, 1) }}%</td>
            <td>{{ number_format($p->nilai_akhir, 2) }}</td>
            <td>{{ $p->status_kelulusan ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
