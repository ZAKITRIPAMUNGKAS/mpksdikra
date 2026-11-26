@php
    use App\Support\TeksSoal;

    $tanggal = $event->tanggal_mulai?->translatedFormat('l, d F Y');
    $adaArab = fn (string $t) => (bool) preg_match('/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $t);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Naskah Soal {{ $label }} — {{ $event->nama_event }}</title>
    <style>
        .soal { display: grid; grid-template-columns: 22pt 1fr; gap: 0 8pt; padding: 7pt 0 8pt; border-bottom: .5pt dashed #e2e8f0; break-inside: avoid; page-break-inside: avoid; }
        .soal:last-of-type { border-bottom: 0; }
        .no { width: 20pt; height: 20pt; border-radius: 4pt; background: #041c38; color: #fff; font-weight: 700; font-size: 9pt; display: flex; align-items: center; justify-content: center; margin-top: 1pt; }
        .tanya { font-size: 10.8pt; line-height: 1.55; }
        .pilihan { list-style: none; margin: 5pt 0 0; padding: 0; display: grid; gap: 2pt 14pt; }
        .pilihan.dua { grid-template-columns: 1fr 1fr; }
        .pilihan li { display: grid; grid-template-columns: 17pt 1fr; align-items: baseline; font-size: 10.3pt; }
        .pilihan li > span:last-child { display: block; }
        .opsi { width: 13pt; height: 13pt; border: .9pt solid #475569; border-radius: 50%; font-size: 7.5pt; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; color: #1e293b; position: relative; top: -1pt; }
    </style>
</head>
<body>
    <div class="alat">
        <span id="status-font">Menyiapkan huruf Arab…</span>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <main class="kertas">
        @include('admin.events.partials.cetak-kop', [
            'judulDokumen' => 'NASKAH SOAL',
            'jenisDokumen' => $label,
            'kakiKiri' => 'Naskah Soal '.$label.' · '.$event->nama_event,
        ])

        <table class="info">
            <tr>
                <td class="lbl">Kegiatan</td><td>{{ $event->nama_event }}</td>
                <td class="lbl">Tanggal</td><td>{{ $tanggal ?? '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Jenis Tes</td><td>{{ $label }} &middot; pilihan ganda</td>
                <td class="lbl">Jumlah / Waktu</td><td>{{ $soal->count() }} soal{{ ! empty($durasi) ? ' · '.$durasi.' menit' : '' }}</td>
            </tr>
        </table>

        <section class="petunjuk">
            <div class="h">PETUNJUK UMUM</div>
            <ol>
                <li>Awali dengan basmalah dan baca setiap soal dengan teliti.</li>
                <li>Periksa nama dan nomor peserta pada <b>lembar jawaban</b> Anda.</li>
                <li>Pilih satu jawaban paling tepat, lalu <b>hitamkan penuh</b> bulatan hurufnya di lembar jawaban.</li>
                <li>Naskah soal ini tidak perlu ditulisi dan dikembalikan kepada panitia.</li>
            </ol>
        </section>

        @foreach($soal as $i => $s)
            @php
                $opsi = $s->pilihan->map(fn ($p) => ['huruf' => strtoupper($p->label), 'teks' => TeksSoal::tanpaLabel((string) $p->teks, strtoupper($p->label))]);
                $pendek = $opsi->every(fn ($o) => mb_strlen($o['teks']) <= 34 && ! $adaArab($o['teks']));
            @endphp
            <section class="soal">
                <div class="no">{{ $i + 1 }}</div>
                <div>
                    <div class="tanya" dir="auto">{!! TeksSoal::html(TeksSoal::tanpaNomor($s->teks_soal)) !!}</div>
                    <ul class="pilihan {{ $pendek ? 'dua' : '' }}">
                        @foreach($opsi as $o)
                            <li><span class="opsi">{{ $o['huruf'] }}</span><span dir="auto">{!! TeksSoal::html($o['teks']) !!}</span></li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endforeach

        <p class="penutup">&mdash; SELAMAT MENGERJAKAN &mdash;</p>
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
