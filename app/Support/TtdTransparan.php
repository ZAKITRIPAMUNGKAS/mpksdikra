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
        if ($w > self::MAKS_LEBAR) {
            $nh = max(1, (int) round($h * self::MAKS_LEBAR / $w));
            $kecil = imagecreatetruecolor(self::MAKS_LEBAR, $nh);
            imagealphablending($kecil, false);
            imagesavealpha($kecil, true);
            imagecopyresampled($kecil, $img, 0, 0, 0, 0, self::MAKS_LEBAR, $nh, $w, $h);
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
        $latar = self::kecerahanLatar($img, $w, $h);

        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 255, 255, 255, 127));

        $x0 = $w;
        $y0 = $h;
        $x1 = -1;
        $y1 = -1;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($img, $x, $y);
                $a = ($c >> 24) & 0x7F;
                if ($a >= 120) {
                    continue; // sudah transparan
                }
                $r = ($c >> 16) & 0xFF;
                $g = ($c >> 8) & 0xFF;
                $b = $c & 0xFF;
                $selisih = $latar - (0.299 * $r + 0.587 * $g + 0.114 * $b);
                if ($selisih <= self::BATAS_LATAR) {
                    continue;
                }
                $pekat = min(1.0, ($selisih - self::BATAS_LATAR) / (self::BATAS_PEKAT - self::BATAS_LATAR));
                $alpha = max($a, (int) round(127 * (1 - $pekat)));
                if ($alpha >= 120) {
                    continue;
                }
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $alpha));
                if ($x < $x0) {
                    $x0 = $x;
                }
                if ($x > $x1) {
                    $x1 = $x;
                }
                if ($y < $y0) {
                    $y0 = $y;
                }
                if ($y > $y1) {
                    $y1 = $y;
                }
            }
        }

        if ($x1 < 0) {
            return $out; // tidak ada goresan terdeteksi; simpan apa adanya (transparan)
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
     * Kecerahan latar = median kecerahan piksel di tepi gambar (kertas putih/keabu-abuan pada foto).
     * Gambar yang sudah transparan di tepinya dianggap berlatar putih.
     */
    private static function kecerahanLatar(GdImage $img, int $w, int $h): float
    {
        $nilai = [];
        $langkah = max(1, (int) floor(max($w, $h) / 200));
        $ambil = function (int $x, int $y) use ($img, &$nilai) {
            $c = imagecolorat($img, $x, $y);
            if ((($c >> 24) & 0x7F) >= 120) {
                $nilai[] = 255.0;

                return;
            }
            $nilai[] = 0.299 * (($c >> 16) & 0xFF) + 0.587 * (($c >> 8) & 0xFF) + 0.114 * ($c & 0xFF);
        };
        for ($x = 0; $x < $w; $x += $langkah) {
            $ambil($x, 0);
            $ambil($x, $h - 1);
        }
        for ($y = 0; $y < $h; $y += $langkah) {
            $ambil(0, $y);
            $ambil($w - 1, $y);
        }
        sort($nilai);

        $median = $nilai ? $nilai[intdiv(count($nilai), 2)] : 255.0;

        // Latar terlalu gelap berarti bukan kertas; pakai putih agar goresan tidak ikut terhapus.
        return $median < 140 ? 255.0 : $median;
    }
}
