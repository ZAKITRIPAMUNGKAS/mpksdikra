@php
    $parts = explode(',', (string) $card['nama'], 2);
    $namaUtama = mb_strtoupper(trim($parts[0]));
    $gelar = trim(preg_replace('/\s*,\s*/', ', ', $parts[1] ?? ''));
    $len = max(1, mb_strlen($namaUtama));
    $nameFontSize = max(7, min(13, round(236 / ($len * 0.8), 1)));
    $pcm = mb_strtoupper($card['pcm'] ?: 'Utusan Muhammadiyah');
    $noPeserta = str_pad((string) $card['no_peserta'], 5, '0', STR_PAD_LEFT);
    $nbm = $card['nbm'] ?? null;
@endphp

{{-- 1. Header --}}
<div class="layer header-plaque" style="top: {{ $top + 10 }}pt; left: {{ $left + 20 }}pt;">
    <table class="header-table" role="presentation">
        <tr>
            <td class="header-cell">
                <table class="brand-table" role="presentation">
                    <tr>
                        <td class="brand-cell">
                            @if($logoMuhammadiyah)<img src="{{ $logoMuhammadiyah }}" class="brand-logo" alt="Logo Muhammadiyah">@endif
                        </td>
                        <td class="brand-bullet">&bull;</td>
                        <td class="brand-cell">
                            @if($logoMpksdi)<img src="{{ $logoMpksdi }}" class="brand-logo" alt="Logo MPKSDI">@endif
                        </td>
                    </tr>
                </table>
                <div class="title-main">BAITUL ARQAM</div>
                <div class="subtitle-event">{{ $cleanSubtitle }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- 2. Foto 3:4 & badge --}}
<div class="layer photo-wrap" style="top: {{ $top + 80 }}pt; left: {{ $left + 86.875 }}pt;">
    @if($card['foto'])
        <img src="{{ $card['foto'] }}" class="photo-img" alt="Foto {{ $namaUtama }}">
    @endif
</div>
<div class="layer photo-badge" style="top: {{ $top + 196 }}pt; left: {{ $left }}pt;">
    <span class="badge-kader">Peserta Baitul Arqam</span>
</div>

{{-- 3. Identitas (tinggi tetap, isi di tengah) --}}
<div class="layer identity-wrap" style="top: {{ $top + 212 }}pt; left: {{ $left + 12 }}pt;">
    <table class="identity-table" role="presentation">
        <tr>
            <td class="identity-cell">
                <div class="kader-name" style="font-size: {{ $nameFontSize }}pt;">{{ $namaUtama }}</div>
                @if($gelar !== '')
                    <div class="kader-gelar">{{ $gelar }}</div>
                @endif
                <div class="kader-sub">
                    <span>{{ $pcm }}</span>
                    @if($nbm)
                        <span class="sub-dot">&bull;</span>
                        <span>NBM {{ $nbm }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- 4. QR presensi --}}
<div class="layer qr-card-wrap" style="top: {{ $top + 257 }}pt; left: {{ $left + 86.875 }}pt;">
    <img src="{{ $card['qr'] }}" class="qr-img" alt="QR Presensi">
    <div class="qr-label">Presensi Kehadiran</div>
    <div class="qr-no">No. Peserta: {{ $noPeserta }}</div>
</div>

{{-- 5. Footer --}}
<div class="layer footer-wrap" style="top: {{ $top + 334 }}pt; left: {{ $left + 25 }}pt;">
    <div class="footer-box">
        <div class="validity-text">Masa Berlaku: {{ $masaBerlaku }}</div>
        <div class="authority-text">MPKSDI PDM Karanganyar</div>
    </div>
</div>
