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

            // 2. Sample perimeter background points safely
            $samplePoints = [];
            for ($step = 0; $step <= 10; $step++) {
                $sampleX = (int) min($workW - 1, max(0, (int) ($workW * $step / 10)));
                $samplePoints[] = [$sampleX, min($workH - 1, 3)];
            }
            for ($step = 1; $step <= 8; $step++) {
                $sy = (int) min($workH - 1, max(0, (int) ($workH * $step * 0.10)));
                $samplePoints[] = [min($workW - 1, 3), $sy];
                $samplePoints[] = [max(0, $workW - 4), $sy];
            }

            $bgColors = [];
            $avgR = 0;
            $avgG = 0;
            $avgB = 0;
            foreach ($samplePoints as [$px, $py]) {
                $c = imagecolorat($clean, $px, $py);
                $cr = ($c >> 16) & 0xFF;
                $cg = ($c >> 8) & 0xFF;
                $cb = $c & 0xFF;
                $bgColors[] = [$cr, $cg, $cb];
                $avgR += $cr;
                $avgG += $cg;
                $avgB += $cb;
            }
            $sampleCount = max(1, count($bgColors));
            $avgR /= $sampleCount;
            $avgG /= $sampleCount;
            $avgB /= $sampleCount;

            $isNeutralBg = (abs($avgR - $avgG) <= 22 && abs($avgR - $avgB) <= 22 && abs($avgG - $avgB) <= 22);
            $bgBrightness = ($avgR * 299 + $avgG * 587 + $avgB * 114) / 1000;

            // 3. Flood-fill queue: Seed hanya dari tepi atas dan tepi samping atas (di atas bahu)
            $visited = array_fill(0, $workW * $workH, 0);
            $isBg = array_fill(0, $workW * $workH, false);
            $queue = new SplQueue;

            for ($x = 0; $x < $workW; $x++) {
                $visited[$x] = 1;
                $queue->enqueue($x);
            }
            $maxSeedY = (int) ($workH * 0.45);
            for ($y = 1; $y < $maxSeedY; $y++) {
                $idxL = $y * $workW;
                $idxR = $y * $workW + ($workW - 1);
                $visited[$idxL] = 1;
                $queue->enqueue($idxL);
                $visited[$idxR] = 1;
                $queue->enqueue($idxR);
            }

            // 4. Proteksi Biometrik & Anatomis:
            // a) Lingkaran wajah tepat di tengah (mata & hidung)
            $faceCx = (int) ($workW * 0.50);
            $faceCy = (int) ($workH * 0.40);
            $innerCoreRx = max(1, (int) ($workW * 0.10));
            $innerCoreRy = max(1, (int) ($workH * 0.10));

            // b) Proteksi Inti Dada Tengah (mencegah kemeja/batik putih di tengah dada tertembus)
            $chestY = (int) ($workH * 0.58);
            $chestX1 = (int) ($workW * 0.28);
            $chestX2 = (int) ($workW * 0.72);

            // Toleransi adaptif: jika background netral studio (ada gradasi bayangan abu-abu),
            // gunakan toleransi 35 untuk menyapu bersih bayangan abu-abu di sekitar leher & bahu
            $effectiveTol = $isNeutralBg ? max($tolerance, 35) : $tolerance;
            $tolSq = $effectiveTol * $effectiveTol;

            while (! $queue->isEmpty()) {
                $idx = $queue->dequeue();
                $x = $idx % $workW;
                $y = (int) ($idx / $workW);

                // Lindungi pusat wajah di tengah
                $dx = ($x - $faceCx) / $innerCoreRx;
                $dy = ($y - $faceCy) / $innerCoreRy;
                if (($dx * $dx + $dy * $dy) < 1.0) {
                    continue;
                }

                // Lindungi inti dada tengah
                if ($y >= $chestY && $x >= $chestX1 && $x <= $chestX2) {
                    continue;
                }

                $rgb = imagecolorat($clean, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Proteksi Biometrik 1: Kulit manusia (hangat alami, bukan background merah pekat)
                $isSkinTone = ($r > $g && $g >= $b && ($r - $b) >= 14 && $r >= 65 && ($r - $g) <= 85 && $b < 205);
                if ($isSkinTone) {
                    continue;
                }

                // Proteksi Biometrik 2: Peci hitam / rambut hitam / jas gelap pada background terang
                if ($bgBrightness > 95 && $r < 55 && $g < 55 && $b < 55) {
                    continue;
                }

                $match = false;
                foreach ($bgColors as [$br, $bg, $bb]) {
                    // Jarak persepsi warna standar terbobot
                    $dr = $r - $br;
                    $dg = $g - $bg;
                    $db = $b - $bb;
                    $distSq = (2 * $dr * $dr) + (4 * $dg * $dg) + (3 * $db * $db);
                    if ($distSq <= ($tolSq * 9)) {
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

            // 5. Hole-filling pass di area tubuh (y >= 0.50H) untuk menutup lubang motif kain/batik
            for ($pass = 0; $pass < 2; $pass++) {
                $filledBg = $isBg;
                $minFillY = (int) ($workH * 0.50);
                for ($y = $minFillY; $y < $workH - 1; $y++) {
                    for ($x = 1; $x < $workW - 1; $x++) {
                        $idx = $y * $workW + $x;
                        if ($isBg[$idx]) {
                            $bodyCount = 0;
                            if (! $isBg[($y - 1) * $workW + ($x - 1)]) { $bodyCount++; }
                            if (! $isBg[($y - 1) * $workW + $x]) { $bodyCount++; }
                            if (! $isBg[($y - 1) * $workW + ($x + 1)]) { $bodyCount++; }
                            if (! $isBg[$y * $workW + ($x - 1)]) { $bodyCount++; }
                            if (! $isBg[$y * $workW + ($x + 1)]) { $bodyCount++; }
                            if (! $isBg[($y + 1) * $workW + ($x - 1)]) { $bodyCount++; }
                            if (! $isBg[($y + 1) * $workW + $x]) { $bodyCount++; }
                            if (! $isBg[($y + 1) * $workW + ($x + 1)]) { $bodyCount++; }
                            if ($bodyCount >= 5) {
                                $filledBg[$idx] = false;
                            }
                        }
                    }
                }
                $isBg = $filledBg;
            }

            // 6. Despeckle / Eliminasi bintik noise di area background
            $cleanedBg = $isBg;
            $despeckleLimitY = (int) ($workH * 0.75);
            for ($y = 1; $y < $despeckleLimitY; $y++) {
                for ($x = 1; $x < $workW - 1; $x++) {
                    $idx = $y * $workW + $x;
                    if (! $isBg[$idx]) {
                        $bgCount = 0;
                        if ($isBg[($y - 1) * $workW + ($x - 1)]) { $bgCount++; }
                        if ($isBg[($y - 1) * $workW + $x]) { $bgCount++; }
                        if ($isBg[($y - 1) * $workW + ($x + 1)]) { $bgCount++; }
                        if ($isBg[$y * $workW + ($x - 1)]) { $bgCount++; }
                        if ($isBg[$y * $workW + ($x + 1)]) { $bgCount++; }
                        if ($isBg[($y + 1) * $workW + ($x - 1)]) { $bgCount++; }
                        if ($isBg[($y + 1) * $workW + $x]) { $bgCount++; }
                        if ($isBg[($y + 1) * $workW + ($x + 1)]) { $bgCount++; }

                        if ($bgCount >= 5) {
                            $cleanedBg[$idx] = true;
                        }
                    }
                }
            }
            $isBg = $cleanedBg;

            // 6. Render destination dengan soft edge anti-aliasing & bottom gradient fade
            $dst = imagecreatetruecolor($workW, $workH);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            $fadeStartY = (int) ($workH * 0.88);
            $fadeRange = max(1, $workH - 1 - $fadeStartY);

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

                        $alpha = $isEdge ? 42 : 0;

                        // Soft vertical gradient fade at bottom to blend smoothly into card curve
                        if ($y >= $fadeStartY) {
                            $fadeProgress = ($y - $fadeStartY) / $fadeRange;
                            $fadeAlpha = (int) (127 * $fadeProgress);
                            $alpha = max($alpha, min(127, $fadeAlpha));
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
