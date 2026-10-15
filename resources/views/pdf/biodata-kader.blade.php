<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Profil Kader — {{ $anggota->nama_lengkap }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 9mm 7mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172235; font-family: DejaVu Sans, sans-serif; font-size: 7.5pt; line-height: 1.28; }
        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; }
        .watermark { position: fixed; z-index: -2; top: 320pt; left: 190pt; width: 220pt; opacity: .035; }
        .topline { height: 3pt; background: #f8bb00; }
        .masthead { padding: 7pt 0 5pt; border-bottom: 1.5pt solid #002b40; }
        .logo { width: 40pt; height: 40pt; object-fit: contain; }
        .logo-right { width: 38pt; height: 38pt; object-fit: contain; }
        .brand { padding: 0 8pt; }
        .eyebrow { color: #557087; font-size: 6.5pt; font-weight: bold; letter-spacing: .7pt; text-transform: uppercase; }
        .org { color: #002b40; font-size: 12pt; font-weight: bold; line-height: 1.1; margin-top: 2pt; }
        .address { color: #64748b; font-size: 6.5pt; margin-top: 3pt; }
        .docrow { padding: 6pt 0 7pt; }
        .doc-title { color: #002b40; font-size: 10pt; font-weight: bold; text-transform: uppercase; }
        .doc-sub { color: #64748b; font-size: 6.5pt; margin-top: 2pt; }
        .docmeta { color: #526579; font-family: DejaVu Sans Mono, monospace; font-size: 6pt; text-align: right; }
        .main > tbody > tr > td { vertical-align: top; }
        .side { width: 145pt; padding-right: 8pt; }
        .content { padding-left: 8pt; border-left: .6pt solid #dbe4eb; }
        .profile { background: #f5f8fa; border: .6pt solid #d8e2e9; padding: 8pt; }
        .photo { width: 64pt; height: 82pt; margin: 0 auto 5pt; border: 1.5pt solid #005c8a; background: #fff; text-align: center; overflow: hidden; }
        .photo img { width: 64pt; height: 82pt; object-fit: cover; }
        .photo-placeholder { color: #91a3b2; font-size: 7pt; padding-top: 32pt; font-weight: bold; }
        .name { color: #002b40; font-size: 10pt; font-weight: bold; text-align: center; line-height: 1.15; }
        .identity { color: #64748b; font-family: DejaVu Sans Mono, monospace; font-size: 6.5pt; text-align: center; margin-top: 2pt; }
        .status { margin: 5pt 0 7pt; padding: 2.5pt; border: .6pt solid; font-size: 6.5pt; font-weight: bold; text-align: center; text-transform: uppercase; }
        .active { color: #166534; background: #ecfdf5; border-color: #86efac; }
        .inactive { color: #991b1b; background: #fff1f2; border-color: #fda4af; }
        .pending { color: #92400e; background: #fffbeb; border-color: #fcd34d; }
        .side-title { color: #005c8a; border-top: .6pt solid #d5e0e7; padding-top: 5pt; margin-top: 6pt; margin-bottom: 4pt; font-size: 6.8pt; font-weight: bold; letter-spacing: .45pt; text-transform: uppercase; }
        .side-item { margin-bottom: 4pt; }
        .label { display: block; color: #728396; font-size: 6pt; font-weight: bold; text-transform: uppercase; }
        .value { display: block; color: #1e293b; font-size: 7.3pt; line-height: 1.25; }
        .qr { border-top: .6pt solid #d5e0e7; margin-top: 6pt; padding-top: 6pt; text-align: center; }
        .qr img { width: 48pt; height: 48pt; padding: 1.5pt; background: #fff; border: .5pt solid #cbd5e1; }
        .qr small { display: block; color: #728396; font-size: 5.8pt; margin-top: 2pt; }
        .section { margin-bottom: 7pt; page-break-inside: avoid; }
        .section-head { background: #002b40; color: #fff; padding: 3.5pt 5pt; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: .25pt; }
        .section-head b { color: #f8bb00; margin-right: 4pt; }
        .grid { width: 100%; margin-top: 3pt; }
        .grid td { padding: 3pt 4pt; border-bottom: .45pt solid #e5ebef; }
        .grid tr:last-child td { border-bottom: 0; }
        .grid .key { width: 82pt; color: #536679; font-size: 6.8pt; font-weight: bold; }
        .grid .sep { width: 7pt; color: #a4b1bc; text-align: center; }
        .grid .val { color: #172235; font-size: 7.2pt; }
        .twocol td { width: 50%; padding-right: 5pt; }
        .twocol td + td { padding-left: 5pt; padding-right: 0; border-left: .45pt solid #e5ebef; }
        .muted { color: #82909d; }
        .sign { margin-top: 7pt; border-top: .7pt solid #cbd5df; }
        .sign td { width: 50%; padding: 4pt 8pt 0; text-align: center; }
        .sign-role { color: #526579; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; }
        .sign-space { height: 32pt; }
        .sign-name { color: #172235; font-size: 7.5pt; font-weight: bold; text-decoration: underline; }
        .sign-meta { color: #728396; font-size: 6pt; margin-top: 1pt; }
        .footer { color: #748494; border-top: .5pt solid #dbe4eb; margin-top: 6pt; padding-top: 3pt; font-size: 5.8pt; text-align: center; }
    </style>
</head>
<body>
@php
    $status = strtolower($anggota->user->status ?? 'pending');
    $statusClass = match ($status) { 'aktif' => 'active', 'nonaktif' => 'inactive', default => 'pending' };
    $list = static function ($value, $fallback = '—') {
        if (is_array($value)) return implode(', ', array_filter($value)) ?: $fallback;
        return filled($value) ? $value : $fallback;
    };
    $education = $anggota->pendidikan_terakhir ?: '—';
    $educationDetail = $anggota->pendidikan_s1 ?: ($anggota->pendidikan_sma ?: ($anggota->pendidikan_smp ?: $anggota->pendidikan_sd));
    $history = $anggota->eventPeserta()->with('event')->get();
    $custom = is_array($anggota->custom_fields) ? $anggota->custom_fields : [];
    unset($custom['riwayat_ba']);
@endphp

<img class="watermark" src="{{ public_path('aset/logo-mpksdi.png') }}" alt="">
<div class="topline"></div>
<table class="masthead">
    <tr>
        <td style="width:45pt"><img class="logo" src="{{ public_path('aset/logo-mpksdi.png') }}" alt="MPKSDI"></td>
        <td class="brand">
            <div class="eyebrow">Pimpinan Daerah Muhammadiyah Kabupaten Karanganyar</div>
            <div class="org">MPKSDI PDM KARANGANYAR</div>
            <div class="address">Gedung Dakwah Muhammadiyah · Jl. Lawu, Papahan, Tasikmadu · Kabupaten Karanganyar</div>
        </td>
        <td style="width:43pt; text-align:right"><img class="logo-right" src="{{ public_path('aset/logo-muhammadiyah.png') }}" alt="Muhammadiyah"></td>
    </tr>
</table>

<table class="docrow">
    <tr>
        <td><div class="doc-title">Corporate Executive Profile</div><div class="doc-sub">Biodata resmi kader dan rekam jejak persyarikatan</div></td>
        <td class="docmeta">REG. {{ str_pad($anggota->id, 5, '0', STR_PAD_LEFT) }}/MPKSDI-KRA/{{ now()->format('Y') }}<br>{{ now()->translatedFormat('d F Y') }}</td>
    </tr>
</table>

<table class="main"><tr>
    <td class="side"><div class="profile">
        <div class="photo">
            @if(!empty($fotoDataUri)) <img src="{{ $fotoDataUri }}" alt="Foto kader"> @else <div class="photo-placeholder">FOTO<br>3 × 4</div> @endif
        </div>
        <div class="name">{{ strtoupper($anggota->nama_lengkap) }}</div>
        <div class="identity">NIK {{ $anggota->nik ?: '—' }}</div>
        <div class="status {{ $statusClass }}">Status: {{ ucfirst($status) }}</div>

        <div class="side-title">Identitas Pribadi</div>
        <div class="side-item"><span class="label">NBM</span><span class="value">{{ $anggota->nbm ?: '—' }}</span></div>
        <div class="side-item"><span class="label">Tempat / Tanggal Lahir</span><span class="value">{{ $anggota->tempat_lahir ?: '—' }}, {{ $anggota->tanggal_lahir?->translatedFormat('d M Y') ?: '—' }}@if($anggota->tanggal_lahir) ({{ $anggota->tanggal_lahir->age }} th) @endif</span></div>
        <div class="side-item"><span class="label">Jenis Kelamin · Pernikahan</span><span class="value">{{ $anggota->jenis_kelamin ?: '—' }} · {{ $anggota->status_pernikahan ?: '—' }}</span></div>
        <div class="side-item"><span class="label">Orang Tua</span><span class="value">Ayah: {{ $anggota->nama_ayah ?: '—' }}<br>Ibu: {{ $anggota->nama_ibu ?: '—' }}</span></div>

        <div class="side-title">Kontak & Domisili</div>
        <div class="side-item"><span class="label">WhatsApp</span><span class="value">{{ $anggota->no_wa ?: '—' }}</span></div>
        <div class="side-item"><span class="label">Email</span><span class="value">{{ $anggota->email ?: '—' }}</span></div>
        <div class="side-item"><span class="label">Alamat</span><span class="value">{{ $anggota->alamat_rumah ?: '—' }}<br>{{ implode(' · ', array_filter([$anggota->desa_kelurahan, $anggota->kecamatan, $anggota->kabupaten ?: 'Karanganyar'])) }}</span></div>

        <div class="side-title">Pendidikan</div>
        <div class="side-item"><span class="label">Jenjang Terakhir</span><span class="value">{{ $education }}@if($educationDetail) — {{ $educationDetail }}@endif</span></div>
        <div class="side-item"><span class="label">Bahasa</span><span class="value">{{ $list($anggota->bahasa_dikuasai, 'Indonesia') }}</span></div>
        <div class="qr"><img src="{{ $qrPng }}" alt="QR verifikasi"><small>Pindai untuk verifikasi dokumen</small></div>
    </div></td>

    <td class="content">
        <div class="section"><div class="section-head"><b>I.</b> Amanah & Profesi</div>
            <table class="grid">
                <tr><td class="key">Profesi utama</td><td class="sep">:</td><td class="val"><b>{{ $anggota->pekerjaan ?: '—' }}</b></td></tr>
                <tr><td class="key">Amanah AUM</td><td class="sep">:</td><td class="val">{{ $anggota->jabatan_aum ?: '—' }}</td></tr>
                <tr><td class="key">PCM · PRM</td><td class="sep">:</td><td class="val"><b>{{ $anggota->pcm ?: ($anggota->kecamatan ?: '—') }}</b> · {{ $anggota->prm ?: '—' }}</td></tr>
                <tr><td class="key">Pimpinan Muhammadiyah</td><td class="sep">:</td><td class="val">{{ $list($anggota->keaktifan_muhammadiyah) }}</td></tr>
                <tr><td class="key">ORTOM & jabatan</td><td class="sep">:</td><td class="val">{{ $list($anggota->keaktifan_ortom) }}@if(is_array($anggota->jabatan_ortom) && count(array_filter($anggota->jabatan_ortom))) <span class="muted">({{ implode(', ', array_filter($anggota->jabatan_ortom)) }})</span>@endif</td></tr>
                <tr><td class="key">Organisasi lain</td><td class="sep">:</td><td class="val">{{ $anggota->organisasi_lain ?: '—' }}</td></tr>
            </table>
        </div>

        <div class="section"><div class="section-head"><b>II.</b> Keagamaan, Kemasjidan & Kemasyarakatan</div>
            <table class="grid twocol"><tr><td><table><tr><td class="key">Baca Al-Qur'an</td><td class="sep">:</td><td class="val"><b>{{ $anggota->kemampuan_baca_quran ?: '—' }}</b></td></tr><tr><td class="key">Hafalan</td><td class="sep">:</td><td class="val">{{ $anggota->hafalan_quran ?: '—' }}</td></tr><tr><td class="key">Kajian</td><td class="sep">:</td><td class="val">{{ $anggota->aktivitas_kajian_agama ?: '—' }}</td></tr></table></td><td><table><tr><td class="key">Sholat di masjid</td><td class="sep">:</td><td class="val">{{ $anggota->aktivitas_sholat_masjid ?: '—' }}</td></tr><tr><td class="key">Takmir</td><td class="sep">:</td><td class="val">{{ $anggota->keaktifan_takmir ?: 'Tidak' }}@if($anggota->jabatan_takmir) ({{ $anggota->jabatan_takmir }})@endif</td></tr><tr><td class="key">ZIS</td><td class="sep">:</td><td class="val">{{ $list($anggota->lembaga_zis) }}</td></tr></table></td></tr></table>
            <table class="grid"><tr><td class="key">Aktivitas masjid</td><td class="sep">:</td><td class="val">{{ $anggota->aktifitas_jamaah_masjid ?: '—' }}</td></tr><tr><td class="key">Kemasyarakatan</td><td class="sep">:</td><td class="val">{{ $anggota->keaktifan_kemasyarakatan ?: '—' }}</td></tr></table>
        </div>

        <div class="section"><div class="section-head"><b>III.</b> Rekam Jejak Baitul Arqam & Catatan</div>
            <table class="grid"><tr><td class="key">Riwayat BA</td><td class="sep">:</td><td class="val">@if($history->isNotEmpty()) @foreach($history as $item)<b>{{ $item->event->nama_event }}</b>@if($item->status_kelulusan) ({{ $item->status_kelulusan }})@endif{{ !$loop->last ? '; ' : '' }}@endforeach @else <span class="muted">Belum tercatat</span> @endif</td></tr>
            @foreach($custom as $key => $value) @php $text = is_array($value) ? implode(', ', array_filter($value)) : trim((string) $value); @endphp @if($text !== '')<tr><td class="key">{{ ucwords(str_replace('_', ' ', $key)) }}</td><td class="sep">:</td><td class="val">{{ $text }}</td></tr>@endif @endforeach</table>
        </div>

        <table class="sign"><tr><td><div class="sign-role">Kader yang bersangkutan</div><div class="sign-space"></div><div class="sign-name">{{ $anggota->nama_lengkap }}</div><div class="sign-meta">NBM: {{ $anggota->nbm ?: '—' }}</div></td><td><div class="sign-role">Ketua MPKSDI PDM Karanganyar</div><div class="sign-space"></div><div class="sign-name">H. Zainudin Ahpandi, S.E.</div><div class="sign-meta">PDM Kabupaten Karanganyar</div></td></tr></table>
    </td>
</tr></table>
<div class="footer">Dokumen resmi Pangkalan Data Terpadu MPKSDI PDM Kabupaten Karanganyar · Verifikasi keaslian melalui QR Code · Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
</body>
</html>
