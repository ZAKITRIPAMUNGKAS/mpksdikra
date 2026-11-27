<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lembar Jawaban {{ $label }} — {{ $event->nama_event }}</title>
    <style>
        @page { size: a4 portrait; margin: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8pt; color: #0f172a; margin: 0; line-height: 1.3; }
        table { border-collapse: collapse; }
        .lj-page { position: relative; width: 595pt; height: 841pt; overflow: hidden; }
        .abs { position: absolute; }
        .bit { border: 0.5pt solid #94a3b8; }
        .bit-on { background: #000; border-color: #000; }

        .kode-ket { left: 142pt; width: 311pt; top: 41.5pt; font-size: 4.8pt; letter-spacing: 1.2pt; color: #94a3b8; text-align: center; }

        .kop { left: 64pt; width: 467pt; top: 52pt; }
        .kop td { vertical-align: middle; }
        .kop .logo { width: 44pt; height: 38pt; }
        .kop .teks { text-align: center; }
        .kop-1 { font-size: 7pt; font-weight: bold; color: #334155; letter-spacing: 0.3pt; }
        .kop-2 { font-size: 9.6pt; font-weight: bold; color: #041c38; letter-spacing: 0.2pt; margin-top: 1pt; }
        .kop-3 { font-size: 6.8pt; color: #475569; margin-top: 2pt; }
        .garis-1 { left: 64pt; width: 467pt; top: 98pt; border-top: 1.6pt solid #041c38; }
        .garis-2 { left: 64pt; width: 467pt; top: 101pt; border-top: 0.5pt solid #041c38; }

        .judul { left: 64pt; width: 467pt; top: 109pt; }
        .judul td { vertical-align: middle; }
        .judul-utama { font-size: 12.5pt; font-weight: bold; letter-spacing: 1.2pt; color: #0f172a; }
        .judul-jenis { font-size: 9pt; font-weight: bold; color: #ffffff; background: #041c38; padding: 2.5pt 7pt; letter-spacing: 0.8pt; }
        .no-box { width: 86pt; border: 0.8pt solid #334155; text-align: center; padding: 1.5pt 0 2pt; }
        .no-ket { font-size: 5.5pt; color: #475569; letter-spacing: 0.8pt; }
        .no-isi { font-size: 13pt; font-weight: bold; color: #0f172a; line-height: 1.1; }

        .identitas { left: 64pt; width: 467pt; top: 146pt; }
        .identitas td { border: 0.7pt solid #94a3b8; height: 17pt; padding: 0 6pt; font-size: 9pt; vertical-align: middle; }
        .identitas .lbl { width: 78pt; background: #f1f5f9; font-size: 6.8pt; font-weight: bold; color: #334155; letter-spacing: 0.4pt; }
        .identitas .nama { font-weight: bold; font-size: 9.5pt; }

        .petunjuk { left: 64pt; width: 467pt; top: 196pt; border: 0.7pt solid #cbd5e1; border-left: 2.4pt solid #041c38; }
        .petunjuk td { vertical-align: top; padding: 5pt 7pt; }
        .petunjuk .h { font-size: 6.3pt; font-weight: bold; letter-spacing: 1pt; color: #041c38; margin-bottom: 2.5pt; }
        .petunjuk .langkah { font-size: 7pt; color: #1e293b; line-height: 1.38; }
        .petunjuk .skala { font-size: 6.8pt; color: #334155; margin-top: 2pt; }
        .contoh { width: 150pt; border-left: 0.7pt solid #cbd5e1; }

        .kaki { left: 64pt; width: 467pt; top: 781pt; border-top: 0.5pt solid #cbd5e1; padding-top: 3pt; }
        .kaki td { font-size: 6pt; color: #64748b; }
    </style>
</head>
<body>
@php
    $L = $layout;
    $bs = $L['bitSize'];
    $latar ??= \App\Support\LembarJawabanGrafik::latarDataUri($L);
    $satuan ??= 'soal';
    $tanggal = $event->tanggal_mulai?->translatedFormat('d F Y');
    $lembarList = $peserta->map(fn ($ep, $i) => ['no' => $ep->no_urut ?? ($i + 1), 'id' => $ep->anggota_id, 'nama' => $ep->anggota->nama_lengkap, 'cabang' => $ep->anggota->pcm ?: $ep->anggota->kecamatan])
        ->push(['no' => null, 'id' => 0, 'nama' => null, 'cabang' => null]);
@endphp
@foreach($lembarList as $lj)
    @php $kode = \App\Support\LembarJawabanLayout::kode($event->id, $tipe, $lj['id']); @endphp
    <div class="lj-page" @unless($loop->last) style="page-break-after: always;" @endunless>
        <img class="abs" src="{{ $latar }}" style="left: 0; top: 0; width: {{ $L['page']['w'] }}pt; height: {{ $L['page']['h'] }}pt;" alt="">
        @foreach($L['bits'] as $bi => $b)
            <div class="abs bit {{ $kode[$bi] ? 'bit-on' : '' }}" style="left: {{ $b['x'] - $bs / 2 }}pt; top: {{ $b['y'] - $bs / 2 }}pt; width: {{ $bs - 1 }}pt; height: {{ $bs - 1 }}pt;"></div>
        @endforeach
        <div class="abs kode-ket">KODE LEMBAR &middot; JANGAN DICORET</div>

        <table class="abs kop">
            <tr>
                <td class="logo">{{-- logo digambar di latar (LembarJawabanGrafik::LOGO_KOP) --}}</td>
                <td class="teks">
                    <div class="kop-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                    <div class="kop-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI</div>
                    <div class="kop-3">{{ $event->nama_event }}@if($tanggal) &middot; {{ $tanggal }}@endif</div>
                </td>
                <td class="logo"></td>
            </tr>
        </table>
        <div class="abs garis-1"></div>
        <div class="abs garis-2"></div>

        <table class="abs judul">
            <tr>
                <td>
                    <span class="judul-utama">LEMBAR JAWABAN</span>&nbsp;&nbsp;
                    <span class="judul-jenis">{{ mb_strtoupper($label) }}</span>
                </td>
                <td class="no-box">
                    <div class="no-ket">NO. PESERTA</div>
                    <div class="no-isi">{{ $lj['no'] ? str_pad((string) $lj['no'], 2, '0', STR_PAD_LEFT) : '. . . .' }}</div>
                </td>
            </tr>
        </table>

        <table class="abs identitas">
            <tr>
                <td class="lbl">NAMA PESERTA</td>
                <td class="nama" colspan="3">{{ $lj['nama'] }}</td>
            </tr>
            <tr>
                <td class="lbl">CABANG (PCM)</td>
                <td>{{ $lj['cabang'] }}</td>
                <td class="lbl" style="width: 70pt;">TANDA TANGAN</td>
                <td style="width: 92pt;"></td>
            </tr>
        </table>

        <table class="abs petunjuk">
            <tr>
                <td>
                    <div class="h">PETUNJUK PENGISIAN</div>
                    <div class="langkah">
                        1. Pakai pulpen/spidol hitam, <b>hitamkan penuh satu bulatan</b> per nomor.<br>
                        2. Jika ingin mengganti jawaban, beri tahu panitia &mdash; jangan dicoret.<br>
                        3. Jangan melipat lembar ini dan jangan menulis di dekat kotak hitam di pojok.
                    </div>
                    @isset($skalaTeks)<div class="skala"><b>Skala:</b> {{ $skalaTeks }}</div>@endisset
                </td>
                <td class="contoh">
                    <div class="h">CONTOH CARA MENGISI</div>
                    {{-- contoh bulatan digambar di latar (LembarJawabanGrafik::contoh) --}}
                </td>
            </tr>
        </table>

        <table class="abs kaki">
            <tr>
                <td>MPKSDI PDM Karanganyar &middot; Lembar Jawaban {{ $label }} &middot; {{ count($L['soal']) }} {{ $satuan }}</td>
                <td style="text-align: right;">Dibaca dengan kamera &middot; diperiksa panitia sebelum disimpan</td>
            </tr>
        </table>
    </div>
@endforeach
</body>
</html>
