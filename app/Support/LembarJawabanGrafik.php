<?php

namespace App\Support;

use RuntimeException;

/**
 * Menggambar bagian statis lembar jawaban (kotak pojok, nomor, bulatan) sekali sebagai PNG
 * agar PDF banyak peserta tidak kehabisan memori. Koordinat dari LembarJawabanLayout.
 */
class LembarJawabanGrafik
{
    private const PX_PER_PT = 3;

    public static function latarDataUri(array $layout): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('Ekstensi GD tidak tersedia.');
        }

        $k = self::PX_PER_PT;
        $w = (int) round($layout['page']['w'] * $k);
        $h = (int) round($layout['page']['h'] * $k);

        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        imagealphablending($img, false);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 255, 255, 255, 127));
        imagealphablending($img, true);

        $hitam = imagecolorallocate($img, 0, 0, 0);
        $garis = imagecolorallocate($img, 71, 85, 105);
        $putih = imagecolorallocate($img, 255, 255, 255);
        $huruf = imagecolorallocate($img, 182, 191, 204);
        $nomor = imagecolorallocate($img, 15, 23, 42);
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $adaFont = is_file($font) && function_exists('imagettftext');

        $m = $layout['marker'] / 2;
        foreach ($layout['markers'] as $mk) {
            imagefilledrectangle($img, (int) round(($mk['x'] - $m) * $k), (int) round(($mk['y'] - $m) * $k), (int) round(($mk['x'] + $m) * $k) - 1, (int) round(($mk['y'] + $m) * $k) - 1, $hitam);
        }

        $r = $layout['r'] * $k;
        $tebal = max(2, (int) round(0.9 * $k));
        $ukuranHuruf = $layout['r'] * 0.95;
        $judulOpsi = $layout['judulOpsi'] ?? null;
        $sudahJudul = [];
        foreach ($layout['soal'] as $s) {
            $kolom = (string) round($s['x']);
            if ($judulOpsi && $adaFont && ! isset($sudahJudul[$kolom])) {
                $sudahJudul[$kolom] = true;
                foreach ($s['opsi'] as $i => $o) {
                    self::teks($img, 6.5, $o['x'], $s['y'] - $layout['rowH'] / 2 - 6, (string) ($judulOpsi[$i] ?? ''), $nomor, $font, 'tengah');
                }
            }
            if ($adaFont) {
                self::teks($img, 9.0, $s['x'] - 2, $s['y'], (string) $s['no'].'.', $nomor, $font, 'kanan');
            }
            foreach ($s['opsi'] as $o) {
                $cx = (int) round($o['x'] * $k);
                $cy = (int) round($o['y'] * $k);
                $d = (int) round($r * 2);
                imagefilledellipse($img, $cx, $cy, $d, $d, $garis);
                imagefilledellipse($img, $cx, $cy, $d - 2 * $tebal, $d - 2 * $tebal, $putih);
                if ($adaFont) {
                    self::teks($img, $ukuranHuruf, $o['x'], $o['y'], $o['label'], $huruf, $font, 'tengah');
                }
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    private static function teks($img, float $pt, float $x, float $y, string $teks, int $warna, string $font, string $rata): void
    {
        $k = self::PX_PER_PT;
        $px = $pt * $k * 0.75;
        $box = imagettfbbox($px, 0, $font, $teks);
        $lebar = $box[2] - $box[0];
        $tinggi = $box[1] - $box[7];
        $x0 = $rata === 'tengah' ? $x * $k - $lebar / 2 - $box[0] : $x * $k - $lebar - $box[0];
        $y0 = $y * $k + $tinggi / 2 - $box[1];
        imagettftext($img, $px, 0, (int) round($x0), (int) round($y0), $warna, $font, $teks);
    }
}
