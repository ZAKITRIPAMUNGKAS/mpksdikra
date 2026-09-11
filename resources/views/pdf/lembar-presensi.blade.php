<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lembar Presensi Peserta - {{ $event->nama_event }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kop-title {
            text-align: center;
        }
        .kop-org {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #002b40;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 11px;
            font-weight: 700;
            color: #005c8a;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .kop-addr {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 3px;
        }
        .kop-line {
            border-bottom: 2px solid #002b40;
            margin-top: 4px;
            margin-bottom: 1px;
        }
        .kop-line-sub {
            border-bottom: 0.5px solid #002b40;
            margin-bottom: 12px;
        }
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9.5px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 110px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th, .data-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            font-size: 9px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #002b40;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5px;
        }
        .text-center { text-align: center; }
        .ttd-col {
            width: 120px;
            height: 26px;
            vertical-align: middle;
            font-size: 8px;
            color: #64748b;
        }
        .footer-sign {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .footer-sign td {
            width: 50%;
            text-align: center;
            font-size: 9.5px;
            vertical-align: top;
        }
        .sign-space {
            height: 50px;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Resmi Persyarikatan --}}
    <table class="header-table">
        <tr>
            <td class="kop-title">
                <div class="kop-org">Pimpinan Daerah Muhammadiyah Karanganyar</div>
                <div class="kop-sub">Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI)</div>
                <div class="kop-addr">Gedung Dakwah Muhammadiyah, Jl. Solo - Tawangmangu Km. 12, Papahan, Tasikmadu, Karanganyar</div>
            </td>
        </tr>
    </table>
    <div class="kop-line"></div>
    <div class="kop-line-sub"></div>

    <div class="doc-title">Daftar Hadir Manual Peserta Baitul Arqam</div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Kegiatan</td>
            <td>: <strong>{{ $event->nama_event }}</strong></td>
            <td class="meta-label">Sesi / Materi</td>
            <td>: Sesi {{ $sesi->urutan }} — {{ $sesi->nama_sesi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Hari / Tanggal</td>
            <td>: {{ $event->tanggal_mulai?->translatedFormat('l, d F Y') }}</td>
            <td class="meta-label">Waktu Sesi</td>
            <td>: {{ $sesi->waktu_mulai?->format('H:i') ?? '-' }} — {{ $sesi->waktu_selesai?->format('H:i') ?? '-' }} WIB</td>
        </tr>
        <tr>
            <td class="meta-label">Lokasi Acara</td>
            <td colspan="3">: {{ $event->lokasi ?: 'Gedung Dakwah Muhammadiyah Karanganyar' }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nama Lengkap Peserta</th>
                <th style="width: 85px;">NBM / NIK</th>
                <th style="width: 100px;">Utusan / PCM / AUM</th>
                <th style="width: 120px;" colspan="2">Tanda Tangan Kehadiran</th>
                <th style="width: 65px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $ep)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $ep->anggota->nama_lengkap }}</strong>
                    <div style="font-size: 7.5px; color: #64748b;">{{ $ep->anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </td>
                <td class="text-center" style="font-family: monospace; font-size: 8px;">
                    @if($ep->anggota->nbm)
                        <div>NBM: {{ $ep->anggota->nbm }}</div>
                    @endif
                    <div>{{ substr($ep->anggota->nik, 0, 6) }}...{{ substr($ep->anggota->nik, -4) }}</div>
                </td>
                <td>{{ $ep->anggota->desa_kelurahan ? $ep->anggota->desa_kelurahan . ', ' : '' }}{{ $ep->anggota->kecamatan }}</td>
                @if(($index + 1) % 2 === 1)
                    <td class="ttd-col" style="text-align: left; padding-left: 8px;">
                        {{ $index + 1 }}. ......................
                    </td>
                    <td class="ttd-col"></td>
                @else
                    <td class="ttd-col"></td>
                    <td class="ttd-col" style="text-align: left; padding-left: 8px;">
                        {{ $index + 1 }}. ......................
                    </td>
                @endif
                <td class="text-center" style="font-size: 8px; color: #64748b;">
                    [ &nbsp; ] Hadir
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8;">
                    Belum ada peserta yang didaftarkan pada event ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Master of Training / Instruktur Sesi</strong>
                <div class="sign-space"></div>
                (_________________________________)
            </td>
            <td>
                Karanganyar, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Petugas Presensi / Panitia</strong>
                <div class="sign-space"></div>
                (_________________________________)
            </td>
        </tr>
    </table>
</body>
</html>
