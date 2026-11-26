@php use App\Support\TeksSoal; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Pernyataan {{ $nama }} — {{ $event->nama_event }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 portrait; margin: 14mm 15mm 16mm 15mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; font-size: 10.5pt; line-height: 1.5; color: #0f172a; background: #e2e8f0; }
        .kertas { max-width: 210mm; margin: 16px auto; background: #fff; padding: 14mm 15mm; box-shadow: 0 1px 4px rgba(15, 23, 42, .15); }
        .kop { border-bottom: 1.5pt solid #041c38; padding-bottom: 6pt; margin-bottom: 10pt; }
        .kop-org { font-size: 8.5pt; font-weight: bold; letter-spacing: .4pt; color: #041c38; text-transform: uppercase; }
        .kop-judul { font-family: 'DejaVu Serif', Georgia, serif; font-size: 16pt; font-weight: bold; margin-top: 2pt; }
        .kop-sub { font-size: 8.5pt; color: #475569; }
        .petunjuk { font-size: 9.5pt; background: #f8fafc; border: .6pt solid #cbd5e1; padding: 6pt 8pt; margin-bottom: 12pt; }
        .skala { display: flex; flex-wrap: wrap; gap: 4pt 10pt; margin-top: 4pt; }
        .skala span { white-space: nowrap; }
        h2 { font-size: 10.5pt; margin: 12pt 0 4pt; padding-bottom: 2pt; border-bottom: .6pt solid #cbd5e1; break-after: avoid; page-break-after: avoid; }
        .butir { display: grid; grid-template-columns: 26pt 1fr; align-items: baseline; padding: 3pt 0; break-inside: avoid; page-break-inside: avoid; }
        .no { font-weight: bold; }
        .ar { font-family: 'Amiri', 'Traditional Arabic', 'Noto Naskh Arabic', serif; font-size: 1.45em; line-height: 1.9; unicode-bidi: isolate; }
        .alat { position: sticky; top: 0; z-index: 5; display: flex; gap: 8px; justify-content: center; align-items: center; padding: 10px; background: #0f172a; color: #fff; font-size: 13px; }
        .alat button { font: inherit; font-weight: 600; padding: 8px 16px; border: 0; border-radius: 8px; background: #fff; color: #0f172a; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .kertas { margin: 0; padding: 0; max-width: none; box-shadow: none; }
            .alat { display: none; }
        }
    </style>
</head>
<body>
    <div class="alat">
        <span id="status-font">Menyiapkan huruf…</span>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <main class="kertas">
        <div class="kop">
            <div class="kop-org">MPKSDI PDM Karanganyar</div>
            <div class="kop-judul">Lembar Pernyataan {{ $nama }}</div>
            <div class="kop-sub">{{ $event->nama_event }} · {{ $event->tanggal_mulai?->translatedFormat('d F Y') }} · {{ $butir->count() }} butir</div>
        </div>
        <div class="petunjuk">
            Baca setiap pernyataan, lalu hitamkan <b>satu</b> bulatan angka pada <b>lembar jawaban</b> dengan nomor yang sama.
            Lembar pernyataan ini tidak perlu ditulisi.
            <div class="skala">
                @foreach($skala as $angka => $arti)
                    <span><b>{{ $angka }}</b> = {{ $arti }}</span>
                @endforeach
            </div>
        </div>
        @php $grupSebelum = null; @endphp
        @foreach($butir as $i => $b)
            @if($b['grup'] !== $grupSebelum)
                <h2>{{ $b['grup'] }}</h2>
                @php $grupSebelum = $b['grup']; @endphp
            @endif
            <div class="butir">
                <div class="no">{{ $i + 1 }}.</div>
                <div dir="auto">{!! TeksSoal::html(TeksSoal::tanpaNomor($b['teks'])) !!}</div>
            </div>
        @endforeach
    </main>
    <script>
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () {
            document.getElementById('status-font').textContent = 'Siap dicetak. Pilih printer atau "Simpan sebagai PDF".';
        });
    </script>
</body>
</html>
