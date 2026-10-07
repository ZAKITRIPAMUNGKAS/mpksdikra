<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SplQueue;
use Throwable;

class ImageCutoutService
{
    /**
     * Remove background from image binary data and return PNG data URI.
     */
    public function removeBackgroundDataUri(?string $binary, int $tolerance = 15): ?string
    {
        if (empty($binary)) {
            return null;
        }

        $hash = md5($binary.'|'.$tolerance);
        $cachePath = "cutouts/{$hash}.png";

        if (Storage::disk('local')->exists($cachePath)) {
            $cached = Storage::disk('local')->get($cachePath);

            return 'data:image/png;base64,'.base64_encode($cached);
        }

        try {
            $src = @imagecreatefromstring($binary);
            if (! $src) {
                return 'data:image/jpeg;base64,'.base64_encode($binary);
            }

            $w = imagesx($src);
            $h = imagesy($src);

            // Scale down if larger than 600x800 for performance and memory optimization
            $maxW = 600;
            $maxH = 800;
            if ($w > $maxW || $h > $maxH) {
                $ratio = min($maxW / $w, $maxH / $h);
                $newW = (int) ($w * $ratio);
                $newH = (int) ($h * $ratio);
                $scaled = imagescale($src, $newW, $newH);
                imagedestroy($src);
                $src = $scaled;
                $w = $newW;
                $h = $newH;
            }

            // Check if already transparent PNG
            $c0 = imagecolorat($src, 2, 2);
            $a0 = ($c0 >> 24) & 0x7F;
            if ($a0 > 60) {
                ob_start();
                imagesavealpha($src, true);
                imagepng($src);
                $pngData = ob_get_clean();
                imagedestroy($src);

                Storage::disk('local')->put($cachePath, $pngData);

                return 'data:image/png;base64,'.base64_encode($pngData);
            }

            // 1. Detect and trim 1-2px border artifact if any edge has high contrast divider
            $trimL = 0;
            $trimT = 0;
            $trimR = 0;
            $trimB = 0;

            if ($w > 20 && $h > 20) {
                $sampleY = (int) ($h * 0.2);
                $sampleX = (int) ($w * 0.2);

                $diffL = abs(((imagecolorat($src, 0, $sampleY) >> 16) & 0xFF) - ((imagecolorat($src, 3, $sampleY) >> 16) & 0xFF));
                if ($diffL > 45) {
                    $trimL = 2;
                }

                $diffT = abs(((imagecolorat($src, $sampleX, 0) >> 16) & 0xFF) - ((imagecolorat($src, $sampleX, 3) >> 16) & 0xFF));
                if ($diffT > 45) {
                    $trimT = 2;
                }

                $diffR = abs(((imagecolorat($src, $w - 1, $sampleY) >> 16) & 0xFF) - ((imagecolorat($src, $w - 4, $sampleY) >> 16) & 0xFF));
                if ($diffR > 45) {
                    $trimR = 2;
                }
            }

            $workW = $w - $trimL - $trimR;
            $workH = $h - $trimT - $trimB;
            $clean = imagecreatetruecolor($workW, $workH);
            imagecopy($clean, $src, 0, 0, $trimL, $trimT, $workW, $workH);
            imagedestroy($src);

            // 2. Sample perimeter background points
            $samplePoints = [
                [4, 4],
                [(int) ($workW / 4), 4],
                [(int) ($workW / 2), 4],
                [(int) ($workW * 3 / 4), 4],
                [$workW - 5, 4],
                [4, (int) ($workH * 0.2)],
                [$workW - 5, (int) ($workH * 0.2)],
                [4, (int) ($workH * 0.4)],
                [$workW - 5, (int) ($workH * 0.4)],
                [4, (int) ($workH * 0.55)],
                [$workW - 5, (int) ($workH * 0.55)],
            ];

            $bgColors = [];
            foreach ($samplePoints as [$px, $py]) {
                $c = imagecolorat($clean, $px, $py);
                $bgColors[] = [
                    ($c >> 16) & 0xFF,
                    ($c >> 8) & 0xFF,
                    $c & 0xFF,
                ];
            }

            // 3. Flood-fill queue
            $visited = array_fill(0, $workW * $workH, 0);
            $isBg = array_fill(0, $workW * $workH, false);
            $queue = new SplQueue;

            for ($x = 0; $x < $workW; $x++) {
                $visited[$x] = 1;
                $queue->enqueue($x);
            }
            $maxSeedY = (int) ($workH * 0.60);
            for ($y = 1; $y < $maxSeedY; $y++) {
                $idxL = $y * $workW;
                $idxR = $y * $workW + ($workW - 1);
                $visited[$idxL] = 1;
                $queue->enqueue($idxL);
                $visited[$idxR] = 1;
                $queue->enqueue($idxR);
            }

            // 4. Protection Shields: Face/Head & Lower Torso
            $faceCx = (int) ($workW * 0.50);
            $faceCy = (int) ($workH * 0.42);
            $faceRx = (int) ($workW * 0.24);
            $faceRy = (int) ($workH * 0.25);

            $torsoMinY = (int) ($workH * 0.70);
            $torsoMinX = (int) ($workW * 0.18);
            $torsoMaxX = (int) ($workW * 0.82);

            $tolSq = $tolerance * $tolerance;

            while (! $queue->isEmpty()) {
                $idx = $queue->dequeue();
                $x = $idx % $workW;
                $y = (int) ($idx / $workW);

                // Face shield protection
                $dx = ($x - $faceCx) / $faceRx;
                $dy = ($y - $faceCy) / $faceRy;
                if (($dx * $dx + $dy * $dy) < 1.0) {
                    continue;
                }

                // Torso shield protection
                if ($y >= $torsoMinY && $x >= $torsoMinX && $x <= $torsoMaxX) {
                    continue;
                }

                $rgb = imagecolorat($clean, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $match = false;
                foreach ($bgColors as [$br, $bg, $bb]) {
                    $dr = $r - $br;
                    $dg = $g - $bg;
                    $db = ($b - $bb) * 1.5; // Blue-weighted separation for warm/cool contrast
                    if (($dr * $dr + $dg * $dg + $db * $db) <= $tolSq) {
                        $match = true;
                        break;
                    }
                }

                if ($match) {
                    $isBg[$idx] = true;

                    if ($y > 0) {
                        $n = ($y - 1) * $workW + $x;
                        if (! $visited[$n]) {
                            $visited[$n] = 1;
                            $queue->enqueue($n);
                        }
                    }
                    if ($y < $workH - 1) {
                        $n = ($y + 1) * $workW + $x;
                        if (! $visited[$n]) {
                            $visited[$n] = 1;
                            $queue->enqueue($n);
                        }
                    }
                    if ($x > 0) {
                        $n = $y * $workW + ($x - 1);
                        if (! $visited[$n]) {
                            $visited[$n] = 1;
                            $queue->enqueue($n);
                        }
                    }
                    if ($x < $workW - 1) {
                        $n = $y * $workW + ($x + 1);
                        if (! $visited[$n]) {
                            $visited[$n] = 1;
                            $queue->enqueue($n);
                        }
                    }
                }
            }

            // 5. Render destination with soft edge anti-aliasing & bottom gradient fade
            $dst = imagecreatetruecolor($workW, $workH);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            $fadeStartY = (int) ($workH * 0.90);

            for ($y = 0; $y < $workH; $y++) {
                for ($x = 0; $x < $workW; $x++) {
                    $idx = $y * $workW + $x;
                    if ($isBg[$idx]) {
                        imagesetpixel($dst, $x, $y, $transparent);
                    } else {
                        $origC = imagecolorat($clean, $x, $y);
                        $r = ($origC >> 16) & 0xFF;
                        $g = ($origC >> 8) & 0xFF;
                        $b = $origC & 0xFF;

                        // Soft perimeter anti-aliasing
                        $isEdge = false;
                        if ($x === 0 || $x === $workW - 1 || $y === 0 ||
                            ($y > 0 && $isBg[($y - 1) * $workW + $x]) ||
                            ($y < $workH - 1 && $isBg[($y + 1) * $workW + $x]) ||
                            ($x > 0 && $isBg[$y * $workW + ($x - 1)]) ||
                            ($x < $workW - 1 && $isBg[$y * $workW + ($x + 1)])) {
                            $isEdge = true;
                        }

                        $alpha = $isEdge ? 36 : 0;

                        // Soft vertical gradient fade at bottom to blend smoothly into card curve
                        if ($y >= $fadeStartY) {
                            $fadeProgress = ($y - $fadeStartY) / ($workH - $fadeStartY);
                            $fadeAlpha = (int) (127 * $fadeProgress);
                            $alpha = max($alpha, $fadeAlpha);
                        }

                        if ($alpha >= 127) {
                            imagesetpixel($dst, $x, $y, $transparent);
                        } else {
                            $cWithAlpha = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
                            imagesetpixel($dst, $x, $y, $cWithAlpha);
                        }
                    }
                }
            }

            imagedestroy($clean);

            ob_start();
            imagepng($dst);
            $pngData = ob_get_clean();
            imagedestroy($dst);

            Storage::disk('local')->put($cachePath, $pngData);

            return 'data:image/png;base64,'.base64_encode($pngData);
        } catch (Throwable) {
            return 'data:image/jpeg;base64,'.base64_encode($binary);
        }
    }

    /**
     * Get cutout data URI for an image in storage disk.
     */
    public function getCutoutDataUriForStorageFile(?string $path, string $disk = 'local', int $tolerance = 15): ?string
    {
        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $binary = Storage::disk($disk)->get($path);

        return $this->removeBackgroundDataUri($binary, $tolerance);
    }

    /**
     * Get transparent data URI for the default MPKSDI leader photo.
     */
    public function getDefaultPhotoDataUri(): ?string
    {
        $cutoutPath = public_path('aset/ketua-mpksdi-cutout.png');
        if (file_exists($cutoutPath)) {
            $content = file_get_contents($cutoutPath);

            return 'data:image/png;base64,'.base64_encode($content);
        }

        $defaultPath = public_path('aset/ketua-mpksdi.png');
        if (file_exists($defaultPath)) {
            return $this->removeBackgroundDataUri(file_get_contents($defaultPath));
        }

        return null;
    }
}
