<table>
    <thead>
        <tr>
            <th colspan="16" style="font-size: 14pt; font-weight: bold; text-align: center; color: #002b40;">
                PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR
            </th>
        </tr>
        <tr>
            <th colspan="16" style="font-size: 12pt; font-weight: bold; text-align: center; color: #005c8a;">
                MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)
            </th>
        </tr>
        <tr>
            <th colspan="16" style="font-size: 11pt; font-weight: bold; text-align: center;">
                REKAPITULASI HASIL KELULUSAN &amp; EVALUASI PERKADERAN BAITUL ARQAM
            </th>
        </tr>
        <tr>
            <th colspan="16" style="font-size: 10pt; text-align: center; font-style: italic; color: #475569;">
                {{ strtoupper($event->nama_event) }}
            </th>
        </tr>
        <tr>
            <th colspan="16"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Waktu Pelaksanaan</th>
            <th colspan="4" style="text-align: left;">: {{ $event->tanggal_mulai->format('d/m/Y') }} s.d. {{ $event->tanggal_selesai->format('d/m/Y') }}</th>
            <th style="font-weight: bold; text-align: left;">Total Peserta</th>
            <th colspan="2" style="text-align: left;">: {{ $totalPeserta ?? $ranking->count() }} Orang</th>
            <th style="font-weight: bold; text-align: left;">Lulus Bersyarat</th>
            <th colspan="7" style="text-align: left;">: {{ $totalBersyarat ?? 0 }} Orang</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Lokasi / Tempat</th>
            <th colspan="4" style="text-align: left;">: {{ $event->lokasi ?? 'Gedung Dakwah PDM Karanganyar' }}</th>
            <th style="font-weight: bold; text-align: left;">Lulus Murni</th>
            <th colspan="2" style="text-align: left;">: {{ $totalLulus ?? 0 }} Orang</th>
            <th style="font-weight: bold; text-align: left;">Tidak Lulus</th>
            <th colspan="7" style="text-align: left;">: {{ $totalTidakLulus ?? 0 }} Orang</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Sasaran Jenjang</th>
            <th colspan="4" style="text-align: left;">: {{ $event->sasaran ?? 'Pimpinan &amp; Kader' }}</th>
            <th style="font-weight: bold; text-align: left;">Rata-rata Nilai</th>
            <th colspan="2" style="text-align: left;">: {{ number_format($rerataNA ?? 0, 2) }}</th>
            <th style="font-weight: bold; text-align: left;">Dicetak Pada</th>
            <th colspan="7" style="text-align: left;">: {{ now()->format('d/m/Y H:i') }} WIB</th>
        </tr>
        <tr>
            <th colspan="16"></th>
        </tr>
        <tr style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Ranking</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">NIK</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">NBM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Nama Lengkap Kader</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">L/P</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Cabang (Kecamatan)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Ranting / AUM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Pretest</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Posttest</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">N-Gain</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Afektif</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Psikomotor</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Keaktifan</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Kehadiran (%)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Nilai Akhir (NA)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Status Kelulusan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ranking as $p)
        @php
            $ng = $p->nilai_pretest >= 100 ? 0 : round(($p->nilai_posttest - $p->nilai_pretest) / (100 - $p->nilai_pretest), 3);
        @endphp
        <tr>
            <td style="text-align: center; font-weight: bold;">{{ $p->ranking ?? '-' }}</td>
            <td style="text-align: center; mso-number-format:'\@';">{{ $p->anggota->nik }}</td>
            <td style="text-align: center; mso-number-format:'\@';">{{ $p->anggota->nbm ?? '-' }}</td>
            <td>{{ $p->anggota->nama_lengkap }}</td>
            <td style="text-align: center;">{{ $p->anggota->jenis_kelamin ?? '-' }}</td>
            <td>{{ $p->anggota->pcm ? 'PCM ' . $p->anggota->pcm : ($p->anggota->kecamatan ? 'PCM ' . $p->anggota->kecamatan : '-') }}</td>
            <td>{{ $p->anggota->prm ?: ($p->anggota->desa_kelurahan ?? ($p->anggota->aum_tempat_tugas ?? '-')) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_pretest, 1) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_posttest, 1) }}</td>
            <td style="text-align: center;">{{ number_format($ng, 2) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_afektif, 1) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_psikomotor, 1) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_keaktifan ?? 0, 1) }}</td>
            <td style="text-align: center;">{{ number_format($p->nilai_kehadiran, 1) }}%</td>
            <td style="text-align: center; font-weight: bold; color: #005c8a;">{{ number_format($p->nilai_akhir, 2) }}</td>
            <td style="text-align: center; font-weight: bold;">{{ $p->status_kelulusan ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="16" style="text-align: center; font-style: italic;">Belum ada data peserta terdaftar.</td>
        </tr>
        @endforelse
        <tr>
            <td colspan="16"></td>
        </tr>
        <tr>
            <td colspan="16" style="font-size: 8pt; font-style: italic; color: #64748b;">
                * NA = 40% Kognitif (70% Posttest + 30% N-Gain) + 35% Psikomotor (90% Praktik + 10% Keaktifan) + 25% Afektif. Kehadiran minimal 75%.
            </td>
        </tr>
    </tbody>
</table>
