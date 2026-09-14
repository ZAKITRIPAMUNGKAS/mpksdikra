<table>
    <thead>
        <tr>
            <th colspan="14" style="font-size: 14pt; font-weight: bold; text-align: center; color: #002b40;">
                {{ $subTitle }}
            </th>
        </tr>
        <tr>
            <th colspan="14" style="font-size: 12pt; font-weight: bold; text-align: center; color: #005c8a;">
                MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)
            </th>
        </tr>
        <tr>
            <th colspan="14" style="font-size: 11pt; font-weight: bold; text-align: center;">
                DATA KADER DAN ANGGOTA PERSYARIKATAN MUHAMMADIYAH
                @if($pcm)
                    - PCM {{ strtoupper($pcm) }}
                @endif
            </th>
        </tr>
        <tr>
            <th colspan="14" style="font-size: 9pt; text-align: center; font-style: italic; color: #475569;">
                Diekspor pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </th>
        </tr>
        <tr>
            <th colspan="14"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: left;">Total Kader</th>
            <th colspan="3" style="text-align: left;">: {{ $totalKader }} Orang</th>
            <th style="font-weight: bold; text-align: left;">Kader Aktif</th>
            <th colspan="2" style="text-align: left;">: {{ $totalAktif }} Orang</th>
            <th style="font-weight: bold; text-align: left;">Laki-laki</th>
            <th colspan="2" style="text-align: left;">: {{ $totalLaki }} Orang</th>
            <th style="font-weight: bold; text-align: left;">Perempuan</th>
            <th colspan="3" style="text-align: left;">: {{ $totalPerempuan }} Orang</th>
        </tr>
        <tr>
            <th colspan="14"></th>
        </tr>
        <tr style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">No</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">NIK</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">NBM</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Nama Lengkap Kader</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">L/P</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Tempat Lahir</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Tanggal Lahir</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Usia</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">No. WhatsApp / HP</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Cabang (PCM)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Ranting (PRM)</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: left;">Pekerjaan / Profesi</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Status Akun</th>
            <th style="background-color: #002b40; color: #ffffff; font-weight: bold; text-align: center;">Tgl Terdaftar</th>
        </tr>
    </thead>
    <tbody>
        @forelse($anggotas as $idx => $a)
        <tr style="{{ $idx % 2 === 1 ? 'background-color: #f8fafc;' : '' }}">
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $idx + 1 }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">'{{ $a->nik }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $a->nbm ? "'{$a->nbm}" : '-' }}</td>
            <td style="font-weight: bold; border: 1px solid #cbd5e1;">{{ $a->nama_lengkap }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">{{ $a->jenis_kelamin }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->tempat_lahir ?? '-' }}</td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->tanggal_lahir ? \Carbon\Carbon::parse($a->tanggal_lahir)->format('d/m/Y') : '-' }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->tanggal_lahir ? \Carbon\Carbon::parse($a->tanggal_lahir)->age . ' th' : '-' }}
            </td>
            <td style="text-align: left; border: 1px solid #cbd5e1;">'{{ $a->no_wa ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->pcm ?? $a->kecamatan ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->prm ?? '-' }}</td>
            <td style="border: 1px solid #cbd5e1;">{{ $a->pekerjaan ?? '-' }}</td>
            <td style="text-align: center; font-weight: bold; border: 1px solid #cbd5e1; {{ $a->user?->status === 'aktif' ? 'color: #047857;' : ($a->user?->status === 'menunggu_verifikasi' ? 'color: #b45309;' : 'color: #64748b;') }}">
                {{ strtoupper(str_replace('_', ' ', $a->user?->status ?? 'menunggu_verifikasi')) }}
            </td>
            <td style="text-align: center; border: 1px solid #cbd5e1;">
                {{ $a->created_at ? $a->created_at->format('d/m/Y') : '-' }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="14" style="text-align: center; padding: 20px; font-style: italic; color: #94a3b8; border: 1px solid #cbd5e1;">
                Tidak ada data kader yang ditemukan.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
