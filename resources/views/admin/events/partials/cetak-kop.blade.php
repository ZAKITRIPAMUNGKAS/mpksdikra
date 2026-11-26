{{-- Gaya + kop bersama untuk dokumen cetak lewat browser (naskah soal, lembar pernyataan). --}}
@once
@php $kakiCss = addcslashes(str_replace(['<', '>', "\r", "\n"], ' ', $kakiKiri), '\\"'); @endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<style>
    @page {
        size: A4 portrait;
        margin: 15mm 16mm 18mm 16mm;
        @bottom-left { content: "{!! $kakiCss !!}"; font: 7pt 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; color: #64748b; }
        @bottom-right { content: "Halaman " counter(page) " dari " counter(pages); font: 7pt 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; color: #64748b; }
    }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif; font-size: 10.5pt; line-height: 1.5; color: #0f172a; background: #e2e8f0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .kertas { max-width: 210mm; margin: 16px auto; background: #fff; padding: 15mm 16mm; box-shadow: 0 1px 4px rgba(15, 23, 42, .15); }
    .alat { position: sticky; top: 0; z-index: 5; display: flex; gap: 10px; justify-content: center; align-items: center; padding: 10px; background: #0f172a; color: #fff; font: 13px 'Segoe UI', Arial, sans-serif; }
    .alat button { font: inherit; font-weight: 600; padding: 8px 16px; border: 0; border-radius: 8px; background: #fff; color: #0f172a; cursor: pointer; }

    .kop { display: grid; grid-template-columns: 52pt 1fr 52pt; align-items: center; gap: 8pt; }
    .kop img { height: 46pt; width: auto; justify-self: center; }
    .kop-teks { text-align: center; }
    .kop-1 { font-size: 8pt; font-weight: 700; letter-spacing: .4pt; color: #334155; }
    .kop-2 { font-size: 11.5pt; font-weight: 700; letter-spacing: .3pt; color: #041c38; margin-top: 1pt; }
    .kop-3 { font-size: 7.5pt; color: #64748b; margin-top: 2pt; }
    .garis { border-top: 1.8pt solid #041c38; margin-top: 7pt; }
    .garis + .garis { border-top-width: .6pt; margin-top: 1.6pt; }

    .judul { display: flex; align-items: center; justify-content: center; gap: 9pt; margin: 13pt 0 9pt; }
    .judul-utama { font-size: 15pt; font-weight: 800; letter-spacing: 2.2pt; }
    .judul-jenis { font-size: 9.5pt; font-weight: 700; letter-spacing: 1pt; color: #fff; background: #041c38; padding: 3pt 9pt; }

    .info { width: 100%; border-collapse: collapse; margin-bottom: 10pt; font-size: 9pt; }
    .info td { border: .7pt solid #cbd5e1; padding: 3.5pt 7pt; }
    .info .lbl { width: 22%; background: #f1f5f9; font-size: 7.5pt; font-weight: 700; letter-spacing: .5pt; color: #334155; text-transform: uppercase; }

    .petunjuk { border: .7pt solid #cbd5e1; border-left: 3pt solid #041c38; background: #f8fafc; padding: 7pt 10pt; margin-bottom: 14pt; font-size: 9pt; }
    .petunjuk .h { font-size: 7.5pt; font-weight: 700; letter-spacing: 1.2pt; color: #041c38; margin-bottom: 3pt; }
    .petunjuk ol { margin: 0; padding-left: 15pt; }
    .petunjuk li { margin: 1pt 0; }

    .ar { font-family: 'Amiri', 'Traditional Arabic', 'Noto Naskh Arabic', 'Scheherazade New', serif; font-size: 1.45em; line-height: 1.9; unicode-bidi: isolate; }
    .penutup { text-align: center; color: #64748b; font-size: 8.5pt; letter-spacing: 1pt; margin-top: 16pt; }

    @media print {
        body { background: #fff; }
        .kertas { margin: 0; padding: 0; max-width: none; box-shadow: none; }
        .alat { display: none; }
    }
</style>
@endonce
<header>
    <div class="kop">
        <img src="{{ asset('aset/logo-muhammadiyah.png') }}" alt="Logo Muhammadiyah">
        <div class="kop-teks">
            <div class="kop-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
            <div class="kop-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI</div>
            <div class="kop-3">Gedung Dakwah Muhammadiyah Karanganyar &middot; Jl. Lawu, Papahan, Tasikmadu, Karanganyar 57722</div>
        </div>
        <img src="{{ asset('aset/idcard/logo-mpksdi-warna.png') }}" alt="Logo MPKSDI">
    </div>
    <div class="garis"></div><div class="garis"></div>
    <div class="judul">
        <span class="judul-utama">{{ $judulDokumen }}</span>
        <span class="judul-jenis">{{ mb_strtoupper($jenisDokumen) }}</span>
    </div>
</header>
