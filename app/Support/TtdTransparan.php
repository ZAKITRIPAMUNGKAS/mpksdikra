<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Tanda tangan untuk sertifikat: latar putih/kertas dibuat transparan, tepi kosong dipotong,
 * disimpan sebagai PNG. Menerima PNG maupun foto JPG dari HP. Bila gambar tidak bisa diolah,
 * file asli disimpan apa adanya agar unggahan tidak pernah gagal karena proses ini.
 */
final class TtdTransparan
{
    private const MAKS_LEBAR = 1200;

    /** Selisih kecerahan dari latar: di bawah ini transparan penuh, di atas BATAS_PEKAT pekat penuh. */
    private const BATAS_LATAR = 28;

    private const BATAS_PEKAT = 95;

    public static function simpan(UploadedFile $file, string $folder = 'signatures'): string
    {
        $img = self::muat($file->getRealPath());
        if (! $img) {
            return $file->store($folder, 'public');
        }

        $hasil = self::olah($img);
        imagedestroy($img);

        ob_start();
        imagepng($hasil, null, 6);
        $png = (string) ob_get_clean();
        imagedestroy($hasil);

        $path = $folder.'/'.Str::random(40).'.png';
        Storage::disk('public')->put($path, $png);

        return $path;
    }

    /**
     * Proses ulang TTD yang sudah tersimpan (unggahan lama). Mengembalikan path PNG baru,
     * atau null bila file tidak ada/tidak bisa diolah (file lama tidak disentuh).
     */
    public static function prosesUlang(string $path): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }
        $img = self::muat($disk->path($path));
        if (! $img) {
            return null;
        }
        $hasil = self::olah($img);
        imagedestroy($img);
        ob_start();
        imagepng($hasil, null, 6);
        $png = (string) ob_get_clean();
        imagedestroy($hasil);

        $baru = dirname($path).'/'.Str::random(40).'.png';
        $disk->put($baru, $png);
        $disk->delete($path);

        return $baru;
    }

    private static function muat(string $path): ?GdImage
    {
        if (! function_exists('imagecreatefromstring')) {
            return null; // ekstensi GD tidak ada: simpan file asli
        }
        $isi = @file_get_contents($path);
        if ($isi === false || $isi === '') {
            return null;
        }
        $img = @imagecreatefromstring($isi);
        if (! $img instanceof GdImage) {
            return null;
        }

        // Perkecil foto besar agar cepat; TTD dicetak hanya ±13 mm tingginya.
        $w = imagesx($img);
        $h = imagesy($img);
        if (max($w, $h) > self::MAKS_LEBAR) {
            $skala = self::MAKS_LEBAR / max($w, $h);
            $nw = max(1, (int) round($w * $skala));
            $nh = max(1, (int) round($h * $skala));
            $kecil = imagecreatetruecolor($nw, $nh);
            imagealphablending($kecil, false);
            imagesavealpha($kecil, true);
            imagecopyresampled($kecil, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $kecil;
        } elseif (! imageistruecolor($img)) {
            imagepalettetotruecolor($img);
        }

        return $img;
    }

    public static function olah(GdImage $img): GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        [$grid, $blok, $gw, $gh] = self::petaLatar($img, $w, $h);

        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 255, 255, 255, 127));

        $x0 = $w;
        $y0 = $h;
        $x1 = -1;
        $y1 = -1;
        for ($y = 0; $y < $h; $y++) {
            // Interpolasi bilinear peta latar antar pusat blok.
            $fy = max(0.0, min($gh - 1.0, ($y + 0.5) / $blok - 0.5));
            $by = (int) $fy;
            $by2 = min($gh - 1, $by + 1);
            $ty = $fy - $by;
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a >= 120) {
                    continue; // sudah transparan
                }
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;

                $fx = max(0.0, min($gw - 1.0, ($x + 0.5) / $blok - 0.5));
                $bx = (int) $fx;
                $bx2 = min($gw - 1, $bx + 1);
                $tx = $fx - $bx;
                $latar = ($grid[$by][$bx] * (1 - $tx) + $grid[$by][$bx2] * $tx) * (1 - $ty)
                    + ($grid[$by2][$bx] * (1 - $tx) + $grid[$by2][$bx2] * $tx) * $ty;

                // Kontras relatif terhadap kertas setempat: foto redup tetap menghasilkan tinta pekat.
                $selisih = ($latar - (0.299 * $r + 0.587 * $g + 0.114 * $b)) * 255 / max($latar, 60.0);
                if ($selisih <= self::BATAS_LATAR) {
                    continue;
                }
                $pekat = min(1.0, ($selisih - self::BATAS_LATAR) / (self::BATAS_PEKAT - self::BATAS_LATAR));
                $alpha = max($a, (int) round(127 * (1 - $pekat)));
                if ($alpha >= 120) {
                    continue;
                }
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));
                $x0 = min($x0, $x);
                $x1 = max($x1, $x);
                $y0 = min($y0, $y);
                $y1 = max($y1, $y);
            }
        }

        if ($x1 < 0) {
            return $out; // tidak ada goresan terdeteksi; hasil transparan
        }

        // Potong tepi kosong dengan sedikit ruang.
        $pad = max(4, (int) round(min($w, $h) * 0.02));
        $cx = max(0, $x0 - $pad);
        $cy = max(0, $y0 - $pad);
        $cw = min($w, $x1 + $pad + 1) - $cx;
        $ch = min($h, $y1 + $pad + 1) - $cy;
        $potong = imagecreatetruecolor($cw, $ch);
        imagealphablending($potong, false);
        imagesavealpha($potong, true);
        imagecopy($potong, $out, 0, 0, $cx, $cy, $cw, $ch);
        imagedestroy($out);

        return $potong;
    }

    /**
     * Peta kecerahan kertas per blok (persentil ke-90 tiap blok, lalu dihaluskan), sehingga
     * foto dengan cahaya tidak rata, bayangan, atau kertas redup tetap terbaca latarnya.
     * Piksel yang sudah transparan dianggap kertas putih.
     *
     * @return array{0: array<int, array<int, float>>, 1: int, 2: int, 3: int}
     */
    private static function petaLatar(GdImage $img, int $w, int $h): array
    {
        $blok = max(8, (int) round(max($w, $h) / 24));
        $gw = (int) ceil($w / $blok);
        $gh = (int) ceil($h / $blok);
        $langkah = max(1, intdiv($blok, 8));
        $grid = [];
        for ($gy = 0; $gy < $gh; $gy++) {
            for ($gx = 0; $gx < $gw; $gx++) {
                $nilai = [];
                $yAkhir = min($h, ($gy + 1) * $blok);
                $xAkhir = min($w, ($gx + 1) * $blok);
                for ($y = $gy * $blok; $y < $yAkhir; $y += $langkah) {
                    for ($x = $gx * $blok; $x < $xAkhir; $x += $langkah) {
                        $c = imagecolorat($img, $x, $y);
                        $nilai[] = ((($c >> 24) & 0x7F) >= 120)
                            ? 255.0
                            : 0.299 * (($c >> 16) & 0xFF) + 0.587 * (($c >> 8) & 0xFF) + 0.114 * ($c & 0xFF);
                    }
                }
                sort($nilai);
                $grid[$gy][$gx] = $nilai ? $nilai[(int) floor((count($nilai) - 1) * 0.9)] : 255.0;
            }
        }

        // Haluskan: blok penuh tinta memakai kertas tetangganya (tidak dikira kertas gelap),
        // lalu rata-rata 3x3 agar transisi bayangan mulus.
        $maks = [];
        for ($gy = 0; $gy < $gh; $gy++) {
            for ($gx = 0; $gx < $gw; $gx++) {
                $m = 0.0;
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        $m = max($m, $grid[$gy + $dy][$gx + $dx] ?? 0.0);
                    }
                }
                // Hanya blok yang jauh lebih gelap dari tetangganya (penuh tinta) yang diganti;
                // gradasi bayangan biasa tetap memakai nilainya sendiri.
                $maks[$gy][$gx] = ($m - $grid[$gy][$gx] > 45) ? $m : $grid[$gy][$gx];
            }
        }
        $halus = [];
        for ($gy = 0; $gy < $gh; $gy++) {
            for ($gx = 0; $gx < $gw; $gx++) {
                $jumlah = 0.0;
                $n = 0;
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        if (isset($maks[$gy + $dy][$gx + $dx])) {
                            $jumlah += $maks[$gy + $dy][$gx + $dx];
                            $n++;
                        }
                    }
                }
                $halus[$gy][$gx] = $jumlah / $n;
            }
        }

        return [$halus, $blok, $gw, $gh];
    }
}
