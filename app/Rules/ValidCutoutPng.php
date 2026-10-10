<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidCutoutPng implements ValidationRule
{
    public function __construct(
        protected mixed $sourcePhotoFile = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('File foto cutout tidak valid.');

            return;
        }

        $realPath = $value->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            $fail('File foto cutout tidak ditemukan.');

            return;
        }

        // 1. K4: Cek magic bytes PNG asli (89 50 4E 47 0D 0A 1A 0A)
        $handle = @fopen($realPath, 'rb');
        if (! $handle) {
            $fail('File foto cutout tidak dapat dibaca.');

            return;
        }
        $header = fread($handle, 8);
        fclose($handle);

        if ($header !== "\x89PNG\r\n\x1a\n") {
            $fail('File cutout harus berupa gambar format PNG asli.');

            return;
        }

        // 2. Baca dengan GD
        $img = @imagecreatefrompng($realPath);
        if (! $img) {
            $fail('Gambar cutout tidak dapat diproses sebagai PNG valid.');

            return;
        }

        $width = imagesx($img);
        $height = imagesy($img);

        if ($width < 100 || $height < 100 || $width > 2000 || $height > 2000) {
            imagedestroy($img);
            $fail('Dimensi foto cutout harus antara 100x100 hingga 2000x2000 piksel.');

            return;
        }

        // 3. K4: Rasio dimensi cutout sama dengan foto sumber (toleransi +-1px atau +-0.02 rasio aspek)
        $srcPath = null;
        if ($this->sourcePhotoFile instanceof UploadedFile && $this->sourcePhotoFile->isValid()) {
            $srcPath = $this->sourcePhotoFile->getRealPath();
        } elseif (is_string($this->sourcePhotoFile) && file_exists($this->sourcePhotoFile)) {
            $srcPath = $this->sourcePhotoFile;
        }

        if ($srcPath && file_exists($srcPath)) {
            $srcSize = @getimagesize($srcPath);
            if ($srcSize && $srcSize[0] > 0 && $srcSize[1] > 0) {
                $srcW = $srcSize[0];
                $srcH = $srcSize[1];

                $srcAspect = $srcW / $srcH;
                $cutoutAspect = $width / $height;

                $aspectDiff = abs($srcAspect - $cutoutAspect);
                $dimMatch = (abs($width - $srcW) <= 1 && abs($height - $srcH) <= 1);

                if (! $dimMatch && $aspectDiff > 0.02) {
                    imagedestroy($img);
                    $fail('Rasio dimensi foto cutout harus sesuai dengan foto sumber pasfoto.');

                    return;
                }
            }
        }

        // 4. K4: Proporsi piksel transparan (alpha GD >= 120) antara 5% dan 90%
        $transparencyRatio = self::calculateTransparencyRatio($img);
        imagedestroy($img);

        if ($transparencyRatio < 0.05) {
            $fail('Foto cutout belum memiliki area transparan yang cukup (minimal 5% latar belakang terpotong).');

            return;
        }

        if ($transparencyRatio > 0.90) {
            $fail('Foto cutout terlalu transparan (lebih dari 90% kosong), pastikan subjek pasfoto terlihat jelas.');

            return;
        }
    }

    /**
     * Menghitung rasio piksel transparan (alpha GD >= 120).
     * Pada GD, alpha 0 = fully opaque, 127 = fully transparent.
     */
    public static function calculateTransparencyRatio(\GdImage $img): float
    {
        $width = imagesx($img);
        $height = imagesy($img);
        $total = $width * $height;

        if ($total === 0) {
            return 0.0;
        }

        $transparentCount = 0;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($img, $x, $y);
                $alpha = ($color >> 24) & 0x7F;
                if ($alpha >= 120) {
                    $transparentCount++;
                }
            }
        }

        return $transparentCount / $total;
    }

    /**
     * K4: Server WAJIB encode ulang PNG lewat GD (imagecreatefrompng -> imagesavealpha -> imagepng)
     * sebelum disimpan ke storage. Jangan simpan byte mentah dari klien.
     */
    public static function reencodePng(string|UploadedFile $source, string $destinationPath): bool
    {
        $srcPath = $source instanceof UploadedFile ? $source->getRealPath() : $source;
        if (! $srcPath || ! file_exists($srcPath)) {
            return false;
        }

        $img = @imagecreatefrompng($srcPath);
        if (! $img) {
            return false;
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Buat direktori tujuan bila belum ada
        $dir = dirname($destinationPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $result = imagepng($img, $destinationPath, 6);
        imagedestroy($img);

        return $result;
    }
}
