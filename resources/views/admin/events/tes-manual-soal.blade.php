@php use App\Support\TeksSoal; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Soal {{ $label }} — {{ $event->nama_event }}</title>
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
        .soal { display: grid; grid-template-columns: 26pt 1fr; align-items: baseline; margin-bottom: 10pt; break-inside: avoid; page-break-inside: avoid; }
        .no { font-weight: bold; }
        .pilihan { margin: 3pt 0 0; padding: 0; list-style: none; }
        .pilihan li { display: grid; grid-template-columns: 18pt 1fr; align-items: baseline; padding: 1.5pt 0; }
        .pilihan li > span { display: block; }
        .pilihan b { font-weight: bold; }
        .ar { font-family: 'Amiri', 'Traditional Arabic', 'Noto Naskh Arabic', 'Scheherazade New', serif; font-size: 1.45em; line-height: 1.9; unicode-bidi: isolate; }
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
        <span id="status-font">Menyiapkan huruf Arab…</span>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <main class="kertas">
        <div class="kop">
            <div class="kop-org">MPKSDI PDM Karanganyar</div>
            <div class="kop-judul">Lembar Soal {{ $label }}</div>
            <div class="kop-sub">{{ $event->nama_event }} · {{ $event->tanggal_mulai?->translatedFormat('d F Y') }} · {{ $soal->count() }} soal</div>
        </div>
        <div class="petunjuk">
            Bacalah setiap soal dengan teliti. Jawab pada <b>lembar jawaban</b> dengan menghitamkan penuh bulatan huruf A, B, C, atau D. Lembar soal ini tidak perlu ditulisi.
        </div>
        @foreach($soal as $i => $s)
            <section class="soal">
                <div class="no">{{ $i + 1 }}.</div>
                <div>
                    <div dir="auto">{!! TeksSoal::html(TeksSoal::tanpaNomor($s->teks_soal)) !!}</div>
                    <ul class="pilihan">
                        @foreach($s->pilihan as $p)
                            @php $huruf = strtoupper($p->label); @endphp
                            <li><b>{{ $huruf }}.</b><span dir="auto">{!! TeksSoal::html(TeksSoal::tanpaLabel((string) $p->teks, $huruf)) !!}</span></li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endforeach
    </main>
    <script>
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () {
            var el = document.getElementById('status-font');
            var siap = !document.fonts || document.fonts.check('16px Amiri', 'بسم');
            el.textContent = siap ? 'Siap dicetak. Pilih printer atau "Simpan sebagai PDF".' : 'Huruf Arab memakai font cadangan perangkat (Amiri tidak termuat).';
        });
    </script>
</body>
</html>
