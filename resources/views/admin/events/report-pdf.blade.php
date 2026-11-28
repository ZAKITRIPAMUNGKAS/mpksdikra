<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Evaluasi Perkaderan — {{ $event->nama_event }}</title>
    @php
        $bobot = $event->getBobotKomposisi();
        $persen = fn ($v) => rtrim(rtrim(number_format($v * 100, 1, ',', ''), '0'), ',');
        $pctLulus = $totalPeserta > 0 ? $totalLulus / $totalPeserta * 100 : 0;
        $pctBersyarat = $totalPeserta > 0 ? $totalBersyarat / $totalPeserta * 100 : 0;
        $pctTidak = $totalPeserta > 0 ? $totalTidakLulus / $totalPeserta * 100 : 0;
        $pctBelum = max(0, 100 - $pctLulus - $pctBersyarat - $pctTidak);

        // Penandatangan & TTD mengikuti pengaturan sertifikat kegiatan.
        $ttd = function (?string $path) {
            if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return 'data:image/png;base64,'.base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($path));
            }

            return '';
        };
        $ttdKiri = $ttd($event->ttd_kiri_path);
        $ttdKanan = $ttd($event->ttd_kanan_path);
        $jabatanKiri = $event->jabatan_penandatangan_kiri ?: 'Master of Training (MoT)';
        $namaKiri = $event->nama_penandatangan_kiri ?: ($event->fasilitator->first()?->name ?? '..............................');
        $ketKiri = $event->keterangan_penandatangan_kiri ?: 'Instruktur Perkaderan';
        $jabatanKanan = $event->jabatan_penandatangan_sertifikat ?: 'Ketua MPKSDI PDM Karanganyar';
        $namaKanan = $event->nama_penandatangan_sertifikat ?: '..............................';
        $ketKanan = $event->nbm_penandatangan_sertifikat ? 'NBM. '.$event->nbm_penandatangan_sertifikat : 'Pimpinan Daerah Muhammadiyah';
        $medali = [1 => ['#f5c542', '#7a5600'], 2 => ['#cfd6de', '#3f4a56'], 3 => ['#e3a272', '#6b3510']];
    @endphp
    <style>
        @page { size: a4 landscape; margin: 11mm 11mm 15mm 11mm; }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt; line-height: 1.35; color: #0f172a; margin: 0; padding: 0;
        }

        /* Footer tetap tiap halaman + nomor halaman */
        .page-footer {
            position: fixed; left: 0; right: 0; bottom: -9mm; height: 6mm;
            font-size: 6.5pt; color: #94a3b8; border-top: 0.6px solid #e2e8f0; padding-top: 1.5mm;
        }
        .page-footer .nomor:after { content: "Halaman " counter(page); }

        /* Kop */
        .kop-table { width: 100%; border-collapse: collapse; }
        .kop-table td { vertical-align: middle; padding: 0; }
        .kop-org-1 { font-size: 9pt; font-weight: bold; color: #002b40; letter-spacing: 0.5px; }
        .kop-org-2 { font-size: 13pt; font-weight: bold; color: #005c8a; letter-spacing: 0.4px; margin-top: 1px; }
        .kop-sub { font-size: 7pt; color: #64748b; margin-top: 2px; line-height: 1.3; }
        .kop-divider { border-top: 2.4px solid #002b40; border-bottom: 0.7px solid #002b40; height: 1.6px; margin: 5px 0 9px; }

        /* Judul */
        .judul-wrap { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .judul-wrap td { vertical-align: middle; padding: 0; }
        .label-dok { font-size: 7pt; font-weight: bold; color: #0284c7; letter-spacing: 1.2px; text-transform: uppercase; }
        .doc-title { font-size: 13.5pt; font-weight: bold; color: #002b40; margin: 1px 0 0; }
        .doc-event { font-size: 9pt; font-weight: bold; color: #334155; margin-top: 1px; }
        .status-pill {
            display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 7pt; font-weight: bold;
            letter-spacing: 0.8px; text-transform: uppercase;
        }

        /* Info + KPI */
        .ringkas { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 8px; }
        .ringkas > tbody > tr > td { vertical-align: top; padding: 0; }
        .info-card { border: 0.8px solid #e2e8f0; border-radius: 6px; padding: 6px 9px; background: #f8fafc; }
        .info-card table { width: 100%; border-collapse: collapse; }
        .info-card td { padding: 1.6px 2px; font-size: 7.6pt; vertical-align: top; }
        .info-label { color: #64748b; width: 82px; white-space: nowrap; }
        .info-val { font-weight: bold; color: #0f172a; }

        .kpi { width: 100%; border-collapse: separate; border-spacing: 5px 0; }
        .kpi td {
            background: #ffffff; border: 0.8px solid #e2e8f0; border-top-width: 3px; border-radius: 5px;
            text-align: center; padding: 5px 2px 4px; vertical-align: middle;
        }
        .kpi-num { font-size: 15pt; font-weight: bold; line-height: 1.05; display: block; }
        .kpi-lbl { font-size: 6.2pt; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
        .kpi-sub { font-size: 6pt; color: #94a3b8; display: block; }

        .sebaran { width: 100%; border-collapse: collapse; height: 7px; margin-top: 6px; }
        .sebaran td { height: 7px; padding: 0; }
        .legenda { font-size: 6.5pt; color: #475569; margin-top: 3px; }
        .legenda span.titik { display: inline-block; width: 6px; height: 6px; border-radius: 3px; margin: 0 2px 0 7px; }

        /* Tabel */
        .data-table { width: 100%; border-collapse: collapse; font-size: 7.6pt; }
        .data-table thead th {
            background: #002b40; color: #ffffff; font-weight: bold; font-size: 6.8pt; text-transform: uppercase;
            letter-spacing: 0.3px; padding: 4px 3px; border: 0.6px solid #0b3a52; text-align: center;
        }
        .data-table thead tr.grup th { background: #0b4a6a; font-size: 6.4pt; padding: 2.5px 3px; letter-spacing: 0.8px; }
        .data-table td {
            border-bottom: 0.6px solid #e2e8f0; padding: 4px 3px; text-align: center; color: #1e293b; vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .data-table tbody tr.top3 td { background: #fffbeb; }
        .data-table td.kiri { text-align: left; padding-left: 6px; }
        .data-table td.na { font-weight: bold; color: #005c8a; font-size: 8.3pt; background: #eff6ff; }
        .data-table tbody tr.top3 td.na { background: #fef3c7; }
        .nama { font-weight: bold; color: #0f172a; }
        .nbm { font-size: 6.3pt; color: #94a3b8; }
        .medali {
            display: inline-block; padding: 1.5px 6px; border-radius: 7px;
            font-weight: bold; font-size: 7.4pt; line-height: 1.2;
        }
        .redup { color: #94a3b8; }

        .badge {
            display: inline-block; padding: 1.5px 6px; border-radius: 8px; font-size: 6.3pt; font-weight: bold;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .badge-lulus { background: #dcfce7; color: #15803d; }
        .badge-bersyarat { background: #fef3c7; color: #b45309; }
        .badge-tidak { background: #ffe4e6; color: #be123c; }

        /* Pedoman & tanda tangan */
        .pedoman { margin-top: 8px; border: 0.8px solid #e2e8f0; border-left: 3px solid #0284c7; border-radius: 4px; padding: 5px 9px; font-size: 6.8pt; color: #475569; line-height: 1.45; page-break-inside: avoid; }
        .pedoman b.j { color: #0f172a; }
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: avoid; }
        .ttd-table td { width: 50%; text-align: center; vertical-align: top; font-size: 7.8pt; padding: 0; }
        .ttd-ruang { height: 15mm; }
        .ttd-ruang img { height: 14mm; max-width: 50mm; }
        .ttd-nama { font-weight: bold; text-decoration: underline; font-size: 8.3pt; }
        .ttd-ket { color: #64748b; font-size: 7pt; }
    </style>
</head>
<body>

    <div class="page-footer">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 0;">Laporan Evaluasi &amp; Kelulusan &bull; {{ $event->nama_event }} &bull; dicetak {{ now()->translatedFormat('d F Y H:i') }} WIB dari sistem Arqam MPKSDI PDM Karanganyar</td>
                <td style="padding: 0; text-align: right; width: 120px;" class="nomor"></td>
            </tr>
        </table>
    </div>

    {{-- 1. Kop --}}
    <table class="kop-table">
        <tr>
            <td style="width: 70px; text-align: center;">
                <img src="{{ public_path('aset/logo-mpksdi.webp') }}" style="height: 50px; width: auto;" alt="Logo MPKSDI">
            </td>
            <td style="text-align: center;">
                <div class="kop-org-1">PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR</div>
                <div class="kop-org-2">MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)</div>
                <div class="kop-sub">
                    Gedung Dakwah Muhammadiyah Karanganyar &bull; Jl. Lawu, Papahan, Tasikmadu, Karanganyar, Jawa Tengah 57722<br>
                    mpksdikra.muhammadiyah.or.id &bull; Layanan Terpadu Baitul Arqam &amp; Pangkalan Data Kader
                </div>
            </td>
            <td style="width: 70px; text-align: center;">
                <img src="{{ public_path('favicon.png') }}" style="height: 46px; width: auto;" alt="Logo Muhammadiyah">
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    {{-- 2. Judul --}}
    <table class="judul-wrap">
        <tr>
            <td>
                <div class="label-dok">Laporan Resmi &bull; Rekapitulasi Hasil Kelulusan</div>
                <div class="doc-title">Laporan Evaluasi &amp; Rekapitulasi Hasil Kelulusan</div>
                <div class="doc-event">{{ $event->nama_event }}</div>
            </td>
            <td style="text-align: right; width: 220px; white-space: nowrap;">
                @php $selesai = $event->status === 'selesai'; @endphp
                <span class="status-pill" style="background: {{ $selesai ? '#dcfce7' : '#e0f2fe' }}; color: {{ $selesai ? '#166534' : '#0369a1' }};">
                    Status: {{ $event->status }}
                </span>
            </td>
        </tr>
    </table>

    {{-- 3. Info kegiatan + KPI --}}
    <table class="ringkas">
        <tr>
            <td style="width: 36%; padding-right: 4px;">
                <div class="info-card">
                    <table>
                        <tr>
                            <td class="info-label">Sasaran</td>
                            <td class="info-val">{{ $event->sasaran ?? 'Pimpinan & Kader Persyarikatan' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Waktu</td>
                            <td class="info-val">
                                {{ $event->tanggal_mulai->translatedFormat('d F Y') }}@if($event->tanggal_selesai && ! $event->tanggal_selesai->isSameDay($event->tanggal_mulai)) &ndash; {{ $event->tanggal_selesai->translatedFormat('d F Y') }}@endif
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Tempat</td>
                            <td class="info-val">{{ $event->lokasi ?? 'Gedung Dakwah PDM Karanganyar' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Bobot Nilai</td>
                            <td class="info-val">
                                Kognitif {{ $persen($bobot['kognitif']) }}% &bull; Psikomotor {{ $persen($bobot['psikomotor']) }}% &bull; Afektif {{ $persen($bobot['afektif']) }}%{!! $bobot['keaktifan'] > 0 ? ' &bull; Keaktifan '.e($persen($bobot['keaktifan'])).'%' : '' !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 64%;">
                <table class="kpi">
                    <tr>
                        <td style="border-top-color: #005c8a;">
                            <span class="kpi-num" style="color: #005c8a;">{{ $totalPeserta }}</span>
                            <span class="kpi-lbl">Total Peserta</span>
                        </td>
                        <td style="border-top-color: #16a34a;">
                            <span class="kpi-num" style="color: #16a34a;">{{ $totalLulus }}</span>
                            <span class="kpi-lbl">Lulus</span>
                            <span class="kpi-sub">{{ round($pctLulus) }}%</span>
                        </td>
                        <td style="border-top-color: #d97706;">
                            <span class="kpi-num" style="color: #d97706;">{{ $totalBersyarat }}</span>
                            <span class="kpi-lbl">Bersyarat</span>
                            <span class="kpi-sub">{{ round($pctBersyarat) }}%</span>
                        </td>
                        <td style="border-top-color: #dc2626;">
                            <span class="kpi-num" style="color: #dc2626;">{{ $totalTidakLulus }}</span>
                            <span class="kpi-lbl">Tidak Lulus</span>
                            <span class="kpi-sub">{{ round($pctTidak) }}%</span>
                        </td>
                        <td style="border-top-color: #0284c7;">
                            <span class="kpi-num" style="color: #0284c7;">{{ number_format($rerataNA, 1) }}</span>
                            <span class="kpi-lbl">Rata-rata NA</span>
                            <span class="kpi-sub">terendah {{ number_format($terendahNA, 1) }}</span>
                        </td>
                        <td style="border-top-color: #002b40;">
                            <span class="kpi-num" style="color: #002b40;">{{ number_format($tertinggiNA, 1) }}</span>
                            <span class="kpi-lbl">NA Tertinggi</span>
                        </td>
                    </tr>
                </table>
                @if($totalPeserta > 0)
                    <div style="padding: 0 5px;">
                        <table class="sebaran">
                            <tr>
                                @if($pctLulus > 0)<td style="width: {{ $pctLulus }}%; background: #22c55e;"></td>@endif
                                @if($pctBersyarat > 0)<td style="width: {{ $pctBersyarat }}%; background: #f59e0b;"></td>@endif
                                @if($pctTidak > 0)<td style="width: {{ $pctTidak }}%; background: #ef4444;"></td>@endif
                                @if($pctBelum > 0.5)<td style="width: {{ $pctBelum }}%; background: #e2e8f0;"></td>@endif
                            </tr>
                        </table>
                        <div class="legenda">
                            Sebaran kelulusan:
                            <span class="titik" style="background: #22c55e;"></span>Lulus
                            <span class="titik" style="background: #f59e0b;"></span>Bersyarat
                            <span class="titik" style="background: #ef4444;"></span>Tidak Lulus
                            @if($pctBelum > 0.5)<span class="titik" style="background: #cbd5e1;"></span>Belum dinilai @endif
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- 4. Tabel rekap & peringkat --}}
    <table class="data-table">
        <thead>
            <tr class="grup">
                <th colspan="3" style="text-align: left; padding-left: 6px;">Peserta</th>
                <th colspan="3">Kognitif</th>
                <th colspan="3">Afektif, Psikomotor &amp; Keaktifan</th>
                <th>Kehadiran</th>
                <th colspan="2">Hasil Akhir</th>
            </tr>
            <tr>
                <th style="width: 30px;">Rank</th>
                <th style="width: 205px; text-align: left; padding-left: 6px;">Nama Lengkap Kader</th>
                <th style="width: 120px; text-align: left; padding-left: 6px;">Utusan / Cabang</th>
                <th style="width: 44px;">Pre</th>
                <th style="width: 44px;">Post</th>
                <th style="width: 44px;">N-Gain</th>
                <th style="width: 48px;">Afektif</th>
                <th style="width: 58px;">Psikomotor</th>
                <th style="width: 52px;">Keaktifan</th>
                <th style="width: 52px;">Presensi</th>
                <th style="width: 54px;">Nilai Akhir</th>
                <th style="width: 72px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $p)
                @php
                    $status = $p->status_kelulusan ?? '-';
                    $kertas = $tesKertas[$p->anggota_id] ?? [];
                    $rank = (int) ($p->ranking ?? 0);
                    $a = $p->anggota;
                    $utusan = $a->pcm ? 'PCM '.$a->pcm : ($a->kecamatan ? 'PCM '.$a->kecamatan : ($a->aum_tempat_tugas ?: 'Karanganyar'));
                @endphp
                <tr class="{{ $rank >= 1 && $rank <= 3 ? 'top3' : '' }}">
                    <td>
                        @if(isset($medali[$rank]))
                            <span class="medali" style="background: {{ $medali[$rank][0] }}; color: {{ $medali[$rank][1] }};">{{ $rank }}</span>
                        @else
                            <b>{{ $p->ranking ?? '-' }}</b>
                        @endif
                    </td>
                    <td class="kiri">
                        <span class="nama">{{ $a->nama_lengkap }}</span>
                        @if($a->nbm)<br><span class="nbm">NBM {{ $a->nbm }}</span>@endif
                    </td>
                    <td class="kiri" style="color: #334155;">{{ $utusan }}</td>
                    <td>{{ number_format($p->nilai_pretest, 1) }}@if(in_array('pretest', $kertas, true))<sup style="color:#b45309;font-weight:bold;">K</sup>@endif</td>
                    <td>{{ number_format($p->nilai_posttest, 1) }}@if(in_array('posttest', $kertas, true))<sup style="color:#b45309;font-weight:bold;">K</sup>@endif</td>
                    <td class="{{ (float) $p->n_gain == 0.0 ? 'redup' : '' }}">{{ number_format($p->n_gain, 2) }}</td>
                    <td>{{ number_format($p->nilai_afektif, 1) }}</td>
                    <td class="{{ (float) $p->nilai_psikomotor == 0.0 ? 'redup' : '' }}">{{ number_format($p->nilai_psikomotor, 1) }}</td>
                    <td class="{{ (float) ($p->nilai_keaktifan ?? 0) == 0.0 ? 'redup' : '' }}">{{ number_format($p->nilai_keaktifan ?? 0, 1) }}</td>
                    <td>{{ number_format($p->nilai_kehadiran, 1) }}%</td>
                    <td class="na">{{ number_format($p->nilai_akhir, 2) }}</td>
                    <td>
                        @if($status === 'Lulus')
                            <span class="badge badge-lulus">Lulus</span>
                        @elseif($status === 'Lulus Bersyarat')
                            <span class="badge badge-bersyarat">Bersyarat</span>
                        @elseif($status === 'Tidak Lulus')
                            <span class="badge badge-tidak">Tidak Lulus</span>
                        @else
                            <span class="redup">{{ $status }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="padding: 16px; color: #94a3b8; font-style: italic;">
                        Belum ada data nilai atau peserta terdaftar pada event ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 5. Pedoman nilai --}}
    <div class="pedoman">
        <b class="j">Pedoman Nilai &amp; Kelulusan</b><br>
        1. <b>Nilai Akhir (NA)</b> = {{ $persen($bobot['kognitif']) }}% Kognitif ({{ $persen($bobot['posttest_dalam_kognitif']) }}% Posttest + {{ $persen($bobot['ngain_dalam_kognitif']) }}% N-Gain)
        + {{ $persen($bobot['psikomotor']) }}% Psikomotor + {{ $persen($bobot['afektif']) }}% Afektif{{ $bobot['keaktifan'] > 0 ? ' + '.$persen($bobot['keaktifan']).'% Keaktifan' : '' }}.<br>
        @if($event->rtlSyaratKelulusan())
        2. <b>Lulus</b>: presensi 100%, menyelesaikan Posttest, dan menyerahkan RTL. <b>Lulus Bersyarat</b>: presensi 75&ndash;99% atau RTL/Posttest masih dalam proses. <b>Tidak Lulus</b>: presensi &lt; 75%.
        @else
        2. <b>Lulus</b>: presensi 100% dan menyelesaikan Posttest. <b>Lulus Bersyarat</b>: presensi 75&ndash;99% atau Posttest masih dalam proses. <b>Tidak Lulus</b>: presensi &lt; 75%. RTL bersifat opsional pada kegiatan ini.
        @endif
        @if($tesKertas->isNotEmpty())<br><sup style="color:#b45309;font-weight:bold;">K</sup> = nilai pretest/posttest diinput admin dari lembar jawaban kertas, dinilai dengan kunci jawaban yang sama.@endif
    </div>

    {{-- 6. Pengesahan --}}
    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                <b>{{ $jabatanKiri }}</b>
                <div class="ttd-ruang">@if($ttdKiri)<img src="{{ $ttdKiri }}" alt="TTD">@endif</div>
                <div class="ttd-nama">{{ $namaKiri }}</div>
                <div class="ttd-ket">{{ $ketKiri }}</div>
            </td>
            <td>
                Karanganyar, {{ now()->translatedFormat('d F Y') }}<br>
                <b>{{ $jabatanKanan }}</b>
                <div class="ttd-ruang">@if($ttdKanan)<img src="{{ $ttdKanan }}" alt="TTD">@endif</div>
                <div class="ttd-nama">{{ $namaKanan }}</div>
                <div class="ttd-ket">{{ $ketKanan }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
