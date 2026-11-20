@php
    $parts = explode(',', (string) $card['nama'], 2);
    $namaUtama = mb_strtoupper(trim($parts[0]));
    $gelar = trim(preg_replace('/\s*,\s*/', ', ', $parts[1] ?? ''));
    $len = max(1, mb_strlen($namaUtama));
    $nameFontSize = max(8, min(12, round(236 / ($len * 0.8), 1)));
    $pcm = mb_strtoupper($card['pcm'] ?: 'Utusan Muhammadiyah');
    $noPeserta = str_pad((string) $card['no_peserta'], 5, '0', STR_PAD_LEFT);
    $nbm = $card['nbm'] ?? null;
@endphp

{{-- 1. Header: menyatu alami dengan ilustrasi background --}}
<div class="layer header-wrap" style="top: {{ $top + 10 }}pt; left: {{ $left + 15 }}pt;">
    <table class="brand-table" role="presentation">
        <tr>
            <td style="vertical-align: middle;">
                <img src="{{ $logoMuhammadiyah }}" class="brand-logo" alt="Logo Muhammadiyah"><img src="{{ $logoMpksdi }}" class="brand-logo" alt="Logo MPKSDI">
            </td>
            <td class="brand-org">MPKSDI<br><span class="brand-org-sub">PDM Karanganyar</span></td>
        </tr>
    </table>
    <div class="title-main">Baitul Arqam</div>
    <div class="subtitle-event">{{ $cleanSubtitle }}</div>
</div>

{{-- 2. Foto 3:4 & label --}}
<div class="layer photo-wrap" style="top: {{ $top + 88 }}pt; left: {{ $left + 91.875 }}pt;">
    @if($card['foto'])
        <img src="{{ $card['foto'] }}" class="photo-img" alt="Foto {{ $namaUtama }}">
    @endif
</div>
<div class="layer photo-badge" style="top: {{ $top + 195 }}pt; left: {{ $left }}pt;">
    <span class="badge-kader">Peserta</span>
</div>

{{-- 3. Identitas (tinggi tetap, isi di tengah) --}}
<div class="layer identity-wrap" style="top: {{ $top + 212 }}pt; left: {{ $left + 10 }}pt;">
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
                        <span class="sub-nbm">NBM {{ $nbm }}</span>
                    @endif
                    <span class="sub-dot">&bull;</span>
                    <span class="sub-no">NO. {{ $noPeserta }}</span>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- 4. QR presensi dibuat full --}}
<div class="layer qr-card-wrap" style="top: {{ $top + 252 }}pt; left: {{ $left + 91.875 }}pt;">
    <img src="{{ $card['qr'] }}" class="qr-img" alt="QR Presensi">
</div>

{{-- 5. Footer --}}
<div class="layer footer-wrap" style="top: {{ $top + 342 }}pt; left: {{ $left }}pt;">
    <div class="footer-pill">
        <span class="validity-text">Berlaku {{ $masaBerlaku }}</span>
    </div>
</div>
