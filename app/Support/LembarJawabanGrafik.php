<?php

namespace App\Support;

use RuntimeException;

/**
 * Menggambar bagian statis lembar jawaban (kotak pojok, bingkai kolom, nomor, bulatan) sekali
 * sebagai PNG agar PDF banyak peserta tidak kehabisan memori. Koordinat dari LembarJawabanLayout;
 * hiasan (bingkai, pita judul, arsiran) hanya memakai warna terang agar tidak mengganggu pembaca.
 */
class LembarJawabanGrafik
{
    private const PX_PER_PT = 3;

    /** Logo kop: [berkas di public/, pusat-x pt, atas pt, tinggi pt]; posisi selaras dengan tes-manual-cetak.blade.php. */
    private const LOGO_KOP = [
        ['aset/logo-muhammadiyah.png', 86.0, 52.0, 38.0],
        ['aset/idcard/logo-mpksdi-warna.png', 509.0, 52.0, 38.0],
    ];

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
        $bingkai = imagecolorallocate($img, 148, 163, 184);
        $pita = imagecolorallocate($img, 226, 232, 240);
        $arsir = imagecolorallocate($img, 243, 245, 248);
        $judul = imagecolorallocate($img, 30, 41, 59);
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $adaFont = is_file($font) && function_exists('imagettftext');

        foreach (self::LOGO_KOP as [$berkas, $cx, $atas, $tinggi]) {
            self::logo($img, public_path($berkas), $cx, $atas, $tinggi);
        }

        if ($adaFont) {
            self::contoh($img, $font, $garis, $putih, $nomor);
        }

        $m = $layout['marker'] / 2;
        foreach ($layout['markers'] as $mk) {
            self::kotak($img, $mk['x'] - $m, $mk['y'] - $m, $mk['x'] + $m, $mk['y'] + $m, $hitam);
        }

        $r = $layout['r'];
        $rowH = $layout['rowH'];
        $judulOpsi = $layout['judulOpsi'] ?? null;

        $kolom = [];
        foreach ($layout['soal'] as $s) {
            $kolom[(string) round($s['x'])][] = $s;
        }

        foreach ($kolom as $baris) {
            $pertama = $baris[0];
            $terakhir = $baris[count($baris) - 1];
            $kiri = $pertama['x'] - 24;
            $kanan = $pertama['opsi'][count($pertama['opsi']) - 1]['x'] + $r + 7;
            $atasPita = $pertama['y'] - $rowH / 2 - 15;
            $bawahPita = $pertama['y'] - $rowH / 2 - 1;
            $bawah = $terakhir['y'] + $rowH / 2 + 1;

            foreach ($baris as $i => $s) {
                if (intdiv($i, 5) % 2 === 1) {
                    self::kotak($img, $kiri, $s['y'] - $rowH / 2, $kanan, $s['y'] + $rowH / 2, $arsir);
                }
            }

            self::kotak($img, $kiri, $atasPita, $kanan, $bawahPita, $pita);
            self::bingkai($img, $kiri, $atasPita, $kanan, $bawah, $bingkai);
            imageline($img, (int) round($kiri * $k), (int) round($bawahPita * $k), (int) round($kanan * $k), (int) round($bawahPita * $k), $bingkai);

            if ($adaFont) {
                $tengahPita = ($atasPita + $bawahPita) / 2;
                self::teks($img, 6.3, $pertama['x'] - 3, $tengahPita, 'NO.', $judul, $font, 'kanan');
                foreach ($pertama['opsi'] as $j => $o) {
                    self::teks($img, 6.3, $o['x'], $tengahPita, (string) ($judulOpsi[$j] ?? $o['label']), $judul, $font, 'tengah');
                }
            }
        }

        $rpx = $r * $k;
        $tebal = max(2, (int) round(0.9 * $k));
        $ukuranHuruf = $r * 0.95;
        foreach ($layout['soal'] as $s) {
            if ($adaFont) {
                self::teks($img, 9.0, $s['x'] - 3, $s['y'], (string) $s['no'], $nomor, $font, 'kanan');
            }
            foreach ($s['opsi'] as $o) {
                $cx = (int) round($o['x'] * $k);
                $cy = (int) round($o['y'] * $k);
                $d = (int) round($rpx * 2);
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

    /**
     * Contoh cara mengisi di kotak petunjuk (kolom kanan, lihat .contoh di tes-manual-cetak.blade.php).
     */
    private static function contoh($img, string $font, int $garis, int $putih, int $tinta): void
    {
        $k = self::PX_PER_PT;
        $x0 = 374.0;
        $r = 4.6;
        $baris = [
            ['Benar', 220.0, ['', 'penuh', '']],
            ['Salah', 235.0, ['centang', 'silang', 'lingkar']],
        ];
        foreach ($baris as [$teks, $y, $tanda]) {
            self::teks($img, 6.8, $x0, $y, $teks, $tinta, $font, 'kiri');
            foreach ($tanda as $i => $jenis) {
                $cx = $x0 + 36 + $i * 13;
                $d = (int) round($r * 2 * $k);
                $px = (int) round($cx * $k);
                $py = (int) round($y * $k);
                if ($jenis === 'lingkar') {
                    imagefilledellipse($img, $px, $py, $d + 9, $d + 9, $tinta);
                    imagefilledellipse($img, $px, $py, $d + 1, $d + 1, $putih);
                }
                imagefilledellipse($img, $px, $py, $d, $d, $jenis === 'penuh' ? $tinta : $garis);
                if ($jenis !== 'penuh') {
                    imagefilledellipse($img, $px, $py, $d - 4, $d - 4, $putih);
                }
                if ($jenis === 'centang' || $jenis === 'silang') {
                    self::teks($img, 6.5, $cx, $y, $jenis === 'centang' ? '✓' : '✗', $tinta, $font, 'tengah');
                }
            }
        }
    }

    private static function logo($img, string $berkas, float $cx, float $atas, float $tinggi): void
    {
        if (! is_file($berkas) || ! ($sumber = @imagecreatefromstring((string) file_get_contents($berkas)))) {
            return;
        }
        $k = self::PX_PER_PT;
        $sw = imagesx($sumber);
        $sh = imagesy($sumber);
        $th = (int) round($tinggi * $k);
        $tw = (int) round($sw * $th / $sh);
        imagecopyresampled($img, $sumber, (int) round($cx * $k - $tw / 2), (int) round($atas * $k), 0, 0, $tw, $th, $sw, $sh);
        imagedestroy($sumber);
    }

    private static function kotak($img, float $x1, float $y1, float $x2, float $y2, int $warna): void
    {
        $k = self::PX_PER_PT;
        imagefilledrectangle($img, (int) round($x1 * $k), (int) round($y1 * $k), (int) round($x2 * $k) - 1, (int) round($y2 * $k) - 1, $warna);
    }

    private static function bingkai($img, float $x1, float $y1, float $x2, float $y2, int $warna): void
    {
        $k = self::PX_PER_PT;
        imagesetthickness($img, 2);
        imagerectangle($img, (int) round($x1 * $k), (int) round($y1 * $k), (int) round($x2 * $k) - 1, (int) round($y2 * $k) - 1, $warna);
        imagesetthickness($img, 1);
    }

    private static function teks($img, float $pt, float $x, float $y, string $teks, int $warna, string $font, string $rata): void
    {
        $k = self::PX_PER_PT;
        $px = $pt * $k * 0.75;
        $box = imagettfbbox($px, 0, $font, $teks);
        $lebar = $box[2] - $box[0];
        $tinggi = $box[1] - $box[7];
        $x0 = match ($rata) {
            'tengah' => $x * $k - $lebar / 2 - $box[0],
            'kiri' => $x * $k - $box[0],
            default => $x * $k - $lebar - $box[0],
        };
        $y0 = $y * $k + $tinggi / 2 - $box[1];
        imagettftext($img, $px, 0, (int) round($x0), (int) round($y0), $warna, $font, $teks);
    }
}
