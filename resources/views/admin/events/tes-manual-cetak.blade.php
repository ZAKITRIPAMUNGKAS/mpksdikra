<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $lembar === 'soal' ? 'Lembar Soal' : 'Lembar Jawaban' }} {{ $label }} — {{ $event->nama_event }}</title>
    <style>
        @if($lembar === 'soal')
        @page { size: a4 portrait; margin: 14mm 15mm 14mm 15mm; }
        @else
        @page { size: a4 portrait; margin: 0; }
        @endif
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #0f172a; margin: 0; line-height: 1.4; }
        .kop { border-bottom: 1.5pt solid #041c38; padding-bottom: 6pt; margin-bottom: 10pt; }
        .kop-org { font-size: 8.5pt; font-weight: bold; letter-spacing: 0.4pt; color: #041c38; text-transform: uppercase; }
        .kop-judul { font-family: 'DejaVu Serif', serif; font-size: 15pt; font-weight: bold; margin-top: 2pt; }
        .kop-sub { font-size: 8.5pt; color: #475569; }
        .petunjuk { font-size: 9pt; background: #f8fafc; border: 0.6pt solid #cbd5e1; padding: 6pt 8pt; margin-bottom: 12pt; }
        .soal { page-break-inside: avoid; margin-bottom: 9pt; }
        .soal-no { font-weight: bold; width: 22pt; vertical-align: top; }
        .soal-teks { vertical-align: top; }
        .pilihan { margin-top: 2pt; }
        .pilihan td { padding: 1pt 0; vertical-align: top; }
        .pil-label { width: 18pt; font-weight: bold; }
        table { border-collapse: collapse; }

        .lj-page { position: relative; width: 595pt; height: 841pt; overflow: hidden; }
        .abs { position: absolute; }
        .bit { border: 0.5pt solid #94a3b8; }
        .bit-on { background: #000; border-color: #000; }
        .lj-head { left: 64pt; width: 467pt; top: 62pt; }
        .identitas { width: 100%; font-size: 10.5pt; }
        .identitas td { padding: 5pt 0; vertical-align: bottom; }
        .identitas .lbl { width: 100pt; font-weight: bold; }
        .identitas .isi { border-bottom: 0.7pt solid #334155; }
        .no-urut { left: 470pt; width: 61pt; text-align: right; top: 62pt; font-size: 8pt; color: #64748b; }
        .catatan { left: 64pt; width: 467pt; top: 786pt; font-size: 7.5pt; color: #64748b; text-align: center; }
    </style>
</head>
<body>
@php
    $kop = function (string $judul) use ($event, $label) {
        return '<div class="kop"><div class="kop-org">MPKSDI PDM Karanganyar</div>'
            .'<div class="kop-judul">'.e($judul).' '.e($label).'</div>'
            .'<div class="kop-sub">'.e($event->nama_event).' · '.e($event->tanggal_mulai?->translatedFormat('d F Y')).'</div></div>';
    };
@endphp

@if($lembar === 'soal')
    {!! $kop('Lembar Soal') !!}
    <div class="petunjuk">
        Bacalah setiap soal dengan teliti. Tuliskan jawaban pada <b>lembar jawaban</b> dengan menghitamkan bulatan huruf A, B, C, atau D. Lembar soal ini tidak perlu ditulisi.
    </div>
    @foreach($soal as $i => $s)
        <div class="soal">
            <table style="width:100%;">
                <tr>
                    <td class="soal-no">{{ $i + 1 }}.</td>
                    <td class="soal-teks">
                        {!! nl2br(e($s->teks_soal)) !!}
                        <table class="pilihan">
                            @foreach($s->pilihan as $p)
                                <tr><td class="pil-label">{{ strtoupper($p->label) }}.</td><td>{{ $p->teks }}</td></tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
@else
    @php
        $L = $layout;
        $bs = $L['bitSize'];
        $latar ??= \App\Support\LembarJawabanGrafik::latarDataUri($L);
        $lembarList = $peserta->map(fn ($ep, $i) => ['no' => $i + 1, 'id' => $ep->anggota_id, 'nama' => $ep->anggota->nama_lengkap, 'cabang' => $ep->anggota->pcm ?: $ep->anggota->kecamatan])
            ->push(['no' => null, 'id' => 0, 'nama' => null, 'cabang' => null]);
    @endphp
    @foreach($lembarList as $lj)
        @php $kode = \App\Support\LembarJawabanLayout::kode($event->id, $tipe, $lj['id']); @endphp
        <div class="lj-page" @unless($loop->last) style="page-break-after: always;" @endunless>
            <img class="abs" src="{{ $latar }}" style="left: 0; top: 0; width: {{ $L['page']['w'] }}pt; height: {{ $L['page']['h'] }}pt;" alt="">
            @foreach($L['bits'] as $bi => $b)
                <div class="abs bit {{ $kode[$bi] ? 'bit-on' : '' }}" style="left: {{ $b['x'] - $bs / 2 }}pt; top: {{ $b['y'] - $bs / 2 }}pt; width: {{ $bs - 1 }}pt; height: {{ $bs - 1 }}pt;"></div>
            @endforeach

            @if($lj['no'])<div class="abs no-urut">No. {{ $lj['no'] }}</div>@endif
            <div class="abs lj-head">
                {!! $kop('Lembar Jawaban') !!}
                <table class="identitas">
                    <tr><td class="lbl">Nama</td><td class="isi">{{ $lj['nama'] }}</td></tr>
                    <tr><td class="lbl">Cabang (PCM)</td><td class="isi">{{ $lj['cabang'] }}</td></tr>
                </table>
                <div class="petunjuk" style="margin-top: 8pt;">
                    <b>Hitamkan penuh</b> satu bulatan untuk setiap nomor memakai pulpen atau spidol hitam. Jangan dicoret silang.
                    Jika salah, beri tahu panitia. Jangan melipat lembar ini dan jangan menulis di dekat kotak hitam di pojok.
                </div>
            </div>

            <div class="abs catatan">Jumlah soal: {{ count($L['soal']) }} · Lembar ini dibaca otomatis dengan kamera; panitia tetap memeriksa hasilnya sebelum disimpan.</div>
        </div>
    @endforeach
@endif
</body>
</html>
