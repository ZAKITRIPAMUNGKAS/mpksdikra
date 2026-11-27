<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat Baitul Arqam - {{ $peserta->anggota->nama_lengkap }}</title>
    <style>
        @page {
            size: 330mm 215mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', Arial, sans-serif;
            background: #ffffff;
            color: #1e293b;
        }
        .bg-fixed {
            position: fixed;
            top: 0;
            left: 0;
            width: 330mm;
            height: 215mm;
            z-index: -1000;
        }
        .page-content {
            position: relative;
            z-index: 10;
            padding: 11mm 20mm 10mm 20mm;
        }
        .page-break {
            page-break-before: always;
        }
        .serif {
            font-family: 'Times New Roman', 'DejaVu Serif', 'Times', serif;
        }
        .grid-table {
            border-collapse: collapse;
            width: 100%;
        }
        .grid-table th {
            background: #064e3b;
            color: #ffffff;
            font-weight: bold;
            padding: 4px 6px;
            border: 1px solid #064e3b;
        }
        .grid-table td {
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            font-size: 7.2pt;
        }
    </style>
</head>
<body>

    @php
        $bgDataUri = file_exists(public_path('aset/bg-sertifikat-ba.jpg'))
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path('aset/bg-sertifikat-ba.jpg')))
            : '';
        $logoMuhammadiyah = file_exists(public_path('aset/logo-muhammadiyah.png'))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('aset/logo-muhammadiyah.png')))
            : '';
        $logoMpksdi = file_exists(public_path('aset/logo-mpksdi.png'))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('aset/logo-mpksdi.png')))
            : '';
        $goldDiamond = file_exists(public_path('aset/gold-diamond.png'))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('aset/gold-diamond.png')))
            : '';

        // Resolve Signature MoT (Left Signatory)
        $signatureMot = '';
        if (!empty($event->ttd_kiri_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->ttd_kiri_path)) {
            $signatureMot = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($event->ttd_kiri_path));
        } elseif (empty($event->nama_penandatangan_kiri) || str_contains(strtolower($event->nama_penandatangan_kiri), 'sriyanto')) {
            if (file_exists(public_path('aset/signature-mot.png'))) {
                $signatureMot = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('aset/signature-mot.png')));
            }
        }

        // Resolve Signature Ketua (Right Signatory)
        $signatureKetua = '';
        if (!empty($event->ttd_kanan_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->ttd_kanan_path)) {
            $signatureKetua = 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($event->ttd_kanan_path));
        } elseif (empty($event->nama_penandatangan_sertifikat) || str_contains(strtolower($event->nama_penandatangan_sertifikat), 'zainudin')) {
            if (file_exists(public_path('aset/signature-ketua.png'))) {
                $signatureKetua = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('aset/signature-ketua.png')));
            }
        }

        $jabatanKiri = $event->jabatan_penandatangan_kiri ?: 'Master of Training (MoT)';
        $namaKiri = $event->nama_penandatangan_kiri ?: ($event->fasilitator->first()?->name ?? 'Drs. H. Sriyanto, M.Pd.');
        $keteranganKiri = $event->keterangan_penandatangan_kiri ?: 'Instruktur Utama Perkaderan';

        $jabatanKanan = $event->jabatan_penandatangan_sertifikat ?: 'Ketua MPKSDI PDM Karanganyar';
        $namaKanan = $event->nama_penandatangan_sertifikat ?: '-';
        $nbmKanan = $event->nbm_penandatangan_sertifikat ? ('NBM. ' . $event->nbm_penandatangan_sertifikat) : '-';

        $tanggalMulai = $event->tanggal_mulai ? $event->tanggal_mulai->translatedFormat('d F Y') : '-';
        $tanggalSelesai = $event->tanggal_selesai ? $event->tanggal_selesai->translatedFormat('d F Y') : '-';
        $tanggalEventFormatted = ($tanggalMulai === $tanggalSelesai) ? $tanggalMulai : ($tanggalMulai . ' s.d. ' . $tanggalSelesai);
        $tanggalTerbit = $event->tanggal_terbit_sertifikat?->translatedFormat('d F Y') ?? ($event->tanggal_selesai?->translatedFormat('d F Y') ?? date('d F Y'));

        $sesiList = $event->sesi->filter(fn($s) => $s->jenis === 'materi' || !empty($s->judul_materi) || !empty($s->nama_sesi))->values();
        $totalMenit = 0;
        foreach ($sesiList as $s) {
            $menit = ($s->jam_mulai && $s->jam_selesai)
                ? max(0, (strtotime($s->jam_selesai) - strtotime($s->jam_mulai)) / 60)
                : (($s->waktu_mulai && $s->waktu_selesai) ? max(0, $s->waktu_mulai->diffInMinutes($s->waktu_selesai)) : null);
            if ($menit !== null) {
                $totalMenit += $menit;
            }
        }
        if ($totalMenit === 0 && count($sesiList) === 0) {
            $totalMenit = 525;
        }
    @endphp

    @if($bgDataUri)
        <img class="bg-fixed" src="{{ $bgDataUri }}" alt="Background">
    @endif

    @include('admin.events.partials.sertifikat-halaman')

</body>
</html>
