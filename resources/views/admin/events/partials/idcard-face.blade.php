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

{{-- 1. Header: pita navy penuh, rata kiri --}}
<div class="layer header-band" style="top: {{ $top }}pt; left: {{ $left }}pt;">
    <table class="brand-table" role="presentation">
        <tr>
            @if($logoMuhammadiyah)<td class="brand-cell"><img src="{{ $logoMuhammadiyah }}" class="brand-logo" alt="Logo Muhammadiyah"></td>@endif
            @if($logoMpksdi)<td class="brand-cell"><img src="{{ $logoMpksdi }}" class="brand-logo" alt="Logo MPKSDI"></td>@endif
            <td class="brand-org">MPKSDI PDM Karanganyar</td>
        </tr>
    </table>
    <div class="title-rule"></div>
    <div class="title-main">Baitul Arqam</div>
    <div class="subtitle-event">{{ $cleanSubtitle }}</div>
</div>

{{-- 2. Foto 3:4 & label --}}
<div class="layer photo-wrap" style="top: {{ $top + 86 }}pt; left: {{ $left + 88.875 }}pt;">
    @if($card['foto'])
        <img src="{{ $card['foto'] }}" class="photo-img" alt="Foto {{ $namaUtama }}">
    @endif
</div>
<div class="layer photo-badge" style="top: {{ $top + 198 }}pt; left: {{ $left }}pt;">
    <span class="badge-kader">Peserta</span>
</div>

{{-- 3. Identitas (tinggi tetap, isi di tengah) --}}
<div class="layer identity-wrap" style="top: {{ $top + 211 }}pt; left: {{ $left + 12 }}pt;">
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
<div class="layer qr-card-wrap" style="top: {{ $top + 256 }}pt; left: {{ $left + 88.875 }}pt;">
    <img src="{{ $card['qr'] }}" class="qr-img" alt="QR Presensi">
    <div class="qr-label">Presensi Kehadiran</div>
    <div class="qr-no">No. Peserta: {{ $noPeserta }}</div>
</div>

{{-- 5. Footer --}}
<div class="layer footer-wrap" style="top: {{ $top + 340 }}pt; left: {{ $left }}pt;">
    <span class="validity-text">Berlaku {{ $masaBerlaku }}</span>
</div>
