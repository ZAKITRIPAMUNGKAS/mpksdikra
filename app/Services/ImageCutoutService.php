<?php

namespace App\Services;

use App\Models\Anggota;
use Illuminate\Support\Facades\Storage;
use SplQueue;
use Throwable;

class ImageCutoutService
{
    /**
     * Remove background from image binary data and return PNG data URI (GD v2 algorithm).
     */
    public function removeBackgroundDataUri(?string $binary, int $tolerance = 15): ?string
    {
        if (empty($binary)) {
            return null;
        }

        $hash = md5($binary.'|v2|'.$tolerance);
        $cachePath = "cutouts/v2_{$hash}.png";

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

            // 2. Precompute YCbCr planes
            $yPlane = array_fill(0, $workW * $workH, 0.0);
            $cbPlane = array_fill(0, $workW * $workH, 0.0);
            $crPlane = array_fill(0, $workW * $workH, 0.0);

            for ($y = 0; $y < $workH; $y++) {
                $rowOffset = $y * $workW;
                for ($x = 0; $x < $workW; $x++) {
                    $rgb = imagecolorat($clean, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $idx = $rowOffset + $x;
                    $yPlane[$idx] = 0.299 * $r + 0.587 * $g + 0.114 * $b;
                    $cbPlane[$idx] = 128.0 - 0.168736 * $r - 0.331264 * $g + 0.5 * $b;
                    $crPlane[$idx] = 128.0 + 0.5 * $r - 0.418688 * $g - 0.081312 * $b;
                }
            }

            // 3. Compute Sobel Edge Barrier on Y channel
            $sobel = array_fill(0, $workW * $workH, 0.0);
            for ($y = 1; $y < $workH - 1; $y++) {
                $rowAbove = ($y - 1) * $workW;
                $rowCurr = $y * $workW;
                $rowBelow = ($y + 1) * $workW;
                for ($x = 1; $x < $workW - 1; $x++) {
                    $gx = ($yPlane[$rowAbove + $x + 1] + 2.0 * $yPlane[$rowCurr + $x + 1] + $yPlane[$rowBelow + $x + 1])
                        - ($yPlane[$rowAbove + $x - 1] + 2.0 * $yPlane[$rowCurr + $x - 1] + $yPlane[$rowBelow + $x - 1]);
                    $gy = ($yPlane[$rowBelow + $x - 1] + 2.0 * $yPlane[$rowBelow + $x] + $yPlane[$rowBelow + $x + 1])
                        - ($yPlane[$rowAbove + $x - 1] + 2.0 * $yPlane[$rowAbove + $x] + $yPlane[$rowAbove + $x + 1]);

                    $sobel[$rowCurr + $x] = sqrt($gx * $gx + $gy * $gy) / 4.0;
                }
            }

            // 4. Sample perimeter background points
            $samplePoints = [];
            for ($step = 0; $step <= 10; $step++) {
                $sampleX = (int) min($workW - 1, max(0, (int) ($workW * $step / 10)));
                $samplePoints[] = [$sampleX, min($workH - 1, 2)];
            }
            for ($step = 1; $step <= 6; $step++) {
                $sy = (int) min($workH - 1, max(0, (int) ($workH * $step * 0.05)));
                $samplePoints[] = [min($workW - 1, 2), $sy];
                $samplePoints[] = [max(0, $workW - 3), $sy];
            }

            $bgSamples = [];
            $avgR = 0;
            $avgG = 0;
            $avgB = 0;
            foreach ($samplePoints as [$px, $py]) {
                $idx = $py * $workW + $px;
                $c = imagecolorat($clean, $px, $py);
                $cr = ($c >> 16) & 0xFF;
                $cg = ($c >> 8) & 0xFF;
                $cb = $c & 0xFF;
                $avgR += $cr;
                $avgG += $cg;
                $avgB += $cb;
                $bgSamples[] = [$yPlane[$idx], $cbPlane[$idx], $crPlane[$idx]];
            }
            $sampleCount = max(1, count($samplePoints));
            $avgR /= $sampleCount;
            $avgG /= $sampleCount;
            $avgB /= $sampleCount;

            $isNeutralBg = (abs($avgR - $avgG) <= 22 && abs($avgR - $avgB) <= 22 && abs($avgG - $avgB) <= 22);
            $bgBrightness = ($avgR * 299 + $avgG * 587 + $avgB * 114) / 1000;

            // YCbCr distance with low luminance weight
            $ycbcrDistSq = function (float $y1, float $cb1, float $cr1, float $y2, float $cb2, float $cr2): float {
                $dy = $y1 - $y2;
                $dcb = $cb1 - $cb2;
                $dcr = $cr1 - $cr2;

                return (0.25 * $dy * $dy) + (1.25 * $dcb * $dcb) + (1.25 * $dcr * $dcr);
            };

            // 5. Seeds & Flood-fill Queue (neighbour-relative region growing)
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
                if (! $visited[$idxL]) {
                    $visited[$idxL] = 1;
                    $queue->enqueue($idxL);
                }
                if (! $visited[$idxR]) {
                    $visited[$idxR] = 1;
                    $queue->enqueue($idxR);
                }
            }

            // Anatomical protection bounds
            $faceCx = (int) ($workW * 0.50);
            $faceCy = (int) ($workH * 0.40);
            $innerCoreRx = max(1, (int) ($workW * 0.10));
            $innerCoreRy = max(1, (int) ($workH * 0.10));

            $chestY = (int) ($workH * 0.58);
            $chestX1 = (int) ($workW * 0.28);
            $chestX2 = (int) ($workW * 0.72);

            $effectiveTol = $isNeutralBg ? max($tolerance, 35) : max($tolerance, 25);
            $maxSeedDistSq = $effectiveTol * $effectiveTol;
            $maxStepDistSq = 22 * 22;
            $sobelBarrierThreshold = 55.0;

            while (! $queue->isEmpty()) {
                $idx = $queue->dequeue();
                $x = $idx % $workW;
                $y = (int) ($idx / $workW);

                // Face protection
                $dx = ($x - $faceCx) / $innerCoreRx;
                $dy = ($y - $faceCy) / $innerCoreRy;
                if (($dx * $dx + $dy * $dy) < 1.0) {
                    continue;
                }

                // Chest protection
                if ($y >= $chestY && $x >= $chestX1 && $x <= $chestX2) {
                    continue;
                }

                $rgb = imagecolorat($clean, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Skin tone protection
                $isSkinTone = ($r > $g && $g >= $b && ($r - $b) >= 14 && $r >= 65 && ($r - $g) <= 85 && $b < 205);
                if ($isSkinTone) {
                    continue;
                }

                // Dark peci / hair on bright background
                if ($bgBrightness > 95 && $r < 55 && $g < 55 && $b < 55) {
                    continue;
                }

                // Match with seed samples
                $curY = $yPlane[$idx];
                $curCb = $cbPlane[$idx];
                $curCr = $crPlane[$idx];

                $match = false;
                foreach ($bgSamples as [$sy, $scb, $scr]) {
                    if ($ycbcrDistSq($curY, $curCb, $curCr, $sy, $scb, $scr) <= $maxSeedDistSq) {
                        $match = true;
                        break;
                    }
                }

                if ($match) {
                    $isBg[$idx] = true;

                    // Neighbors (up, down, left, right)
                    $neighbors = [];
                    if ($y > 0) {
                        $neighbors[] = ($y - 1) * $workW + $x;
                    }
                    if ($y < $workH - 1) {
                        $neighbors[] = ($y + 1) * $workW + $x;
                    }
                    if ($x > 0) {
                        $neighbors[] = $y * $workW + ($x - 1);
                    }
                    if ($x < $workW - 1) {
                        $neighbors[] = $y * $workW + ($x + 1);
                    }

                    foreach ($neighbors as $nIdx) {
                        if (! $visited[$nIdx]) {
                            // Check Sobel barrier: do not cross strong edge unless very close to seed
                            if ($sobel[$nIdx] >= $sobelBarrierThreshold) {
                                continue;
                            }

                            // Check neighbor relative step continuity
                            $stepDistSq = $ycbcrDistSq($curY, $curCb, $curCr, $yPlane[$nIdx], $cbPlane[$nIdx], $crPlane[$nIdx]);
                            if ($stepDistSq <= $maxStepDistSq) {
                                $visited[$nIdx] = 1;
                                $queue->enqueue($nIdx);
                            }
                        }
                    }
                }
            }

            // 6. Hole-filling pass on body region to close fabric/batik gaps
            for ($pass = 0; $pass < 2; $pass++) {
                $filledBg = $isBg;
                $minFillY = (int) ($workH * 0.50);
                for ($y = $minFillY; $y < $workH - 1; $y++) {
                    for ($x = 1; $x < $workW - 1; $x++) {
                        $idx = $y * $workW + $x;
                        if ($isBg[$idx]) {
                            $bodyCount = 0;
                            if (! $isBg[($y - 1) * $workW + ($x - 1)]) {
                                $bodyCount++;
                            }
                            if (! $isBg[($y - 1) * $workW + $x]) {
                                $bodyCount++;
                            }
                            if (! $isBg[($y - 1) * $workW + ($x + 1)]) {
                                $bodyCount++;
                            }
                            if (! $isBg[$y * $workW + ($x - 1)]) {
                                $bodyCount++;
                            }
                            if (! $isBg[$y * $workW + ($x + 1)]) {
                                $bodyCount++;
                            }
                            if (! $isBg[($y + 1) * $workW + ($x - 1)]) {
                                $bodyCount++;
                            }
                            if (! $isBg[($y + 1) * $workW + $x]) {
                                $bodyCount++;
                            }
                            if (! $isBg[($y + 1) * $workW + ($x + 1)]) {
                                $bodyCount++;
                            }
                            if ($bodyCount >= 5) {
                                $filledBg[$idx] = false;
                            }
                        }
                    }
                }
                $isBg = $filledBg;
            }

            // 7. Largest Connected Component Foreground Cleanup
            // Any foreground pixel not part of the main subject component is turned into background
            $labels = array_fill(0, $workW * $workH, 0);
            $currentLabel = 0;
            $componentSizes = [];

            for ($y = 0; $y < $workH; $y++) {
                for ($x = 0; $x < $workW; $x++) {
                    $idx = $y * $workW + $x;
                    if (! $isBg[$idx] && $labels[$idx] === 0) {
                        $currentLabel++;
                        $size = 0;
                        $compQ = new SplQueue;
                        $compQ->enqueue($idx);
                        $labels[$idx] = $currentLabel;

                        while (! $compQ->isEmpty()) {
                            $p = $compQ->dequeue();
                            $size++;
                            $px = $p % $workW;
                            $py = (int) ($p / $workW);

                            $nbrs = [];
                            if ($px > 0) {
                                $nbrs[] = $p - 1;
                            }
                            if ($px < $workW - 1) {
                                $nbrs[] = $p + 1;
                            }
                            if ($py > 0) {
                                $nbrs[] = $p - $workW;
                            }
                            if ($py < $workH - 1) {
                                $nbrs[] = $p + $workW;
                            }

                            foreach ($nbrs as $nIdx) {
                                if (! $isBg[$nIdx] && $labels[$nIdx] === 0) {
                                    $labels[$nIdx] = $currentLabel;
                                    $compQ->enqueue($nIdx);
                                }
                            }
                        }
                        $componentSizes[$currentLabel] = $size;
                    }
                }
            }

            if (! empty($componentSizes)) {
                arsort($componentSizes);
                $maxLabel = key($componentSizes);
                $maxSize = current($componentSizes);

                for ($y = 0; $y < $workH; $y++) {
                    for ($x = 0; $x < $workW; $x++) {
                        $idx = $y * $workW + $x;
                        $lbl = $labels[$idx];
                        if ($lbl > 0) {
                            $isMain = ($lbl === $maxLabel);
                            $isLargeBody = ($componentSizes[$lbl] >= (0.15 * $maxSize) && $x >= (0.2 * $workW) && $x <= (0.8 * $workW));
                            if (! $isMain && ! $isLargeBody) {
                                $isBg[$idx] = true;
                            }
                        }
                    }
                }
            }

            // 8. 2px Alpha Blur (5x5 separable box blur, without bottom fade)
            $rawAlpha = array_fill(0, $workW * $workH, 0);
            for ($i = 0; $i < $workW * $workH; $i++) {
                $rawAlpha[$i] = $isBg[$i] ? 127 : 0;
            }

            $hBlurred = array_fill(0, $workW * $workH, 0.0);
            for ($y = 0; $y < $workH; $y++) {
                $rowOffset = $y * $workW;
                for ($x = 0; $x < $workW; $x++) {
                    $sum = 0;
                    $cnt = 0;
                    for ($k = -2; $k <= 2; $k++) {
                        $kx = $x + $k;
                        if ($kx >= 0 && $kx < $workW) {
                            $sum += $rawAlpha[$rowOffset + $kx];
                            $cnt++;
                        }
                    }
                    $hBlurred[$rowOffset + $x] = $sum / $cnt;
                }
            }

            $finalAlpha = array_fill(0, $workW * $workH, 0);
            for ($x = 0; $x < $workW; $x++) {
                for ($y = 0; $y < $workH; $y++) {
                    $sum = 0;
                    $cnt = 0;
                    for ($k = -2; $k <= 2; $k++) {
                        $ky = $y + $k;
                        if ($ky >= 0 && $ky < $workH) {
                            $sum += $hBlurred[$ky * $workW + $x];
                            $cnt++;
                        }
                    }
                    $finalAlpha[$y * $workW + $x] = (int) round($sum / $cnt);
                }
            }

            // 9. Render destination PNG with alpha
            $dst = imagecreatetruecolor($workW, $workH);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            for ($y = 0; $y < $workH; $y++) {
                $rowOffset = $y * $workW;
                for ($x = 0; $x < $workW; $x++) {
                    $idx = $rowOffset + $x;
                    $alpha = max(0, min(127, $finalAlpha[$idx]));
                    if ($alpha >= 127) {
                        imagesetpixel($dst, $x, $y, $transparent);
                    } else {
                        $origC = imagecolorat($clean, $x, $y);
                        $r = ($origC >> 16) & 0xFF;
                        $g = ($origC >> 8) & 0xFF;
                        $b = $origC & 0xFF;
                        $cWithAlpha = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
                        imagesetpixel($dst, $x, $y, $cWithAlpha);
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

    /**
     * Resolve the original participant photo for the one-sided ID card.
     *
     * @return array{dataUri: ?string, mode: string}
     */
    public function resolveOriginalForAnggota(Anggota $anggota): array
    {
        if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
            $raw = Storage::disk('local')->get($anggota->foto);
            $mime = $this->detectMime($raw);

            return [
                'dataUri' => 'data:'.$mime.';base64,'.base64_encode($raw),
                'mode' => 'original',
            ];
        }

        $defaultPath = $anggota->jenis_kelamin === 'P'
            ? public_path('aset/default-profile-wanita.jpg')
            : public_path('aset/default-profile-pria.jpg');

        if (file_exists($defaultPath)) {
            $raw = file_get_contents($defaultPath);
            $mime = $this->detectMime($raw);

            return [
                'dataUri' => 'data:'.$mime.';base64,'.base64_encode($raw),
                'mode' => 'original',
            ];
        }

        return ['dataUri' => null, 'mode' => 'original'];
    }

    /**
     * Resolve photo data URI and active rendering mode for an Anggota.
     *
     * @return array{dataUri: ?string, mode: string}
     */
    public function resolveForAnggota(Anggota $anggota): array
    {
        $mode = $anggota->foto_mode === 'original' ? 'original' : 'cutout';

        if ($mode === 'original') {
            if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
                $raw = Storage::disk('local')->get($anggota->foto);
                $mime = $this->detectMime($raw);

                return [
                    'dataUri' => 'data:'.$mime.';base64,'.base64_encode($raw),
                    'mode' => 'original',
                ];
            }

            return [
                'dataUri' => $this->getDefaultPhotoDataUri(),
                'mode' => 'cutout',
            ];
        }

        // Priority 1: Client cutout PNG
        if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
            $raw = Storage::disk('local')->get($anggota->foto_cutout_path);

            return [
                'dataUri' => 'data:image/png;base64,'.base64_encode($raw),
                'mode' => 'cutout',
            ];
        }

        // Priority 2: GD v2 server cutout of source photo
        if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
            $cutoutUri = $this->getCutoutDataUriForStorageFile($anggota->foto, 'local');
            if ($cutoutUri) {
                return [
                    'dataUri' => $cutoutUri,
                    'mode' => 'cutout',
                ];
            }

            // Priority 3: Raw source photo fallback (mode: original)
            $raw = Storage::disk('local')->get($anggota->foto);
            $mime = $this->detectMime($raw);

            return [
                'dataUri' => 'data:'.$mime.';base64,'.base64_encode($raw),
                'mode' => 'original',
            ];
        }

        // Priority 4: Default leader photo
        return [
            'dataUri' => $this->getDefaultPhotoDataUri(),
            'mode' => 'cutout',
        ];
    }

    /**
     * Robust MIME detector using magic bytes without requiring ext-fileinfo.
     */
    public function detectMime(?string $raw): string
    {
        if (empty($raw)) {
            return 'image/jpeg';
        }

        if (str_starts_with($raw, "\x89PNG\r\n\x1a\n")) {
            return 'image/png';
        }
        if (str_starts_with($raw, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }
        if (str_starts_with($raw, 'RIFF') && substr($raw, 8, 4) === 'WEBP') {
            return 'image/webp';
        }
        if (str_starts_with($raw, '<svg') || str_contains(substr($raw, 0, 100), '<svg')) {
            return 'image/svg+xml';
        }

        if (class_exists(\finfo::class)) {
            try {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);

                return $finfo->buffer($raw) ?: 'image/jpeg';
            } catch (Throwable) {
                // Fallback
            }
        }

        return 'image/jpeg';
    }
}
