<?php

namespace App\Support;

/**
 * Ukuran kertas sertifikat: F4 (215 × 330 mm) landscape. Dipakai bersama oleh
 * unduhan satuan, cetak massal, ZIP, dan portal peserta agar hasilnya identik.
 */
final class KertasSertifikat
{
    public const LEBAR_MM = 330;

    public const TINGGI_MM = 215;

    /**
     * Ukuran kertas DomPDF dalam point, sisi pendek dulu (orientasi diatur lewat 'landscape').
     *
     * @return array{0:float,1:float,2:float,3:float}
     */
    public static function dompdf(): array
    {
        return [0.0, 0.0, round(self::TINGGI_MM / 25.4 * 72, 2), round(self::LEBAR_MM / 25.4 * 72, 2)];
    }
}
