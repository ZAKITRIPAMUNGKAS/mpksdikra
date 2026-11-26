@php
    use App\Support\TeksSoal;

    $tanggal = $event->tanggal_mulai?->translatedFormat('l, d F Y');
    $jenisDokumen = $jenis === 'afektif' ? 'Kuesioner Sikap' : 'Angket Kepuasan';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Pernyataan {{ $nama }} — {{ $event->nama_event }}</title>
    <style>
        .skala { display: flex; flex-wrap: wrap; gap: 4pt; margin-top: 5pt; }
        .skala span { border: .6pt solid #cbd5e1; background: #fff; padding: 1.5pt 6pt; font-size: 8.3pt; }
        .skala b { display: inline-block; min-width: 9pt; text-align: center; }
        table.butir { width: 100%; border-collapse: collapse; font-size: 10.3pt; }
        table.butir thead th { background: #041c38; color: #fff; font-size: 7.5pt; letter-spacing: 1pt; text-align: left; padding: 4pt 7pt; }
        table.butir thead { display: table-header-group; }
        table.butir td { border-bottom: .5pt solid #e2e8f0; padding: 5pt 7pt; vertical-align: top; }
        table.butir tr { break-inside: avoid; page-break-inside: avoid; }
        table.butir td.no { width: 30pt; text-align: center; font-weight: 700; color: #041c38; }
        table.butir tr.grup td { background: #e2e8f0; font-size: 8pt; font-weight: 700; letter-spacing: .6pt; color: #1e293b; text-transform: uppercase; border-bottom: 0; padding: 3.5pt 7pt; }
        table.butir tr.isi:nth-child(even of .isi) td { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="alat">
        <span id="status-font">Menyiapkan huruf…</span>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <main class="kertas">
        @include('admin.events.partials.cetak-kop', [
            'judulDokumen' => 'LEMBAR PERNYATAAN',
            'jenisDokumen' => $jenisDokumen,
            'kakiKiri' => 'Lembar Pernyataan '.$nama.' · '.$event->nama_event,
        ])

        <table class="info">
            <tr>
                <td class="lbl">Kegiatan</td><td>{{ $event->nama_event }}</td>
                <td class="lbl">Tanggal</td><td>{{ $tanggal ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Instrumen</td><td>{{ $nama }}</td>
                <td class="lbl">Jumlah</td><td>{{ $butir->count() }} butir &middot; skala 1&ndash;{{ count($skala) }}</td>
            </tr>
        </table>

        <section class="petunjuk">
            <div class="h">PETUNJUK PENGISIAN</div>
            <ol>
                <li>Baca setiap pernyataan dan jawab sesuai keadaan Anda yang sebenarnya; tidak ada jawaban benar atau salah.</li>
                <li>Pada <b>lembar jawaban</b>, hitamkan penuh <b>satu</b> bulatan angka di nomor yang sama.</li>
                <li>Lembar pernyataan ini tidak perlu ditulisi dan dikembalikan kepada panitia.</li>
            </ol>
            <div class="skala">
                @foreach($skala as $angka => $arti)
                    <span><b>{{ $angka }}</b> = {{ $arti }}</span>
                @endforeach
            </div>
        </section>

        <table class="butir">
            <thead><tr><th style="text-align: center;">NO.</th><th>PERNYATAAN</th></tr></thead>
            <tbody>
                @php $grupSebelum = null; @endphp
                @foreach($butir as $i => $b)
                    @if($b['grup'] !== $grupSebelum)
                        <tr class="grup"><td colspan="2">{{ $b['grup'] }}</td></tr>
                        @php $grupSebelum = $b['grup']; @endphp
                    @endif
                    <tr class="isi">
                        <td class="no">{{ $i + 1 }}</td>
                        <td dir="auto">{!! TeksSoal::html(TeksSoal::tanpaNomor($b['teks'])) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="penutup">&mdash; JAZAKUMULLAH KHAIRAN &mdash;</p>
    </main>
    <script>
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(function () {
            document.getElementById('status-font').textContent = 'Siap dicetak. Pilih printer atau "Simpan sebagai PDF".';
        });
    </script>
</body>
</html>
