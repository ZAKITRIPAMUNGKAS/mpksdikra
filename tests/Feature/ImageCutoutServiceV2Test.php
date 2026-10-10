<?php

namespace Tests\Feature;

use App\Services\ImageCutoutService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCutoutServiceV2Test extends TestCase
{
    public function test_v2_removes_gradient_background_with_shadow_and_preserves_subject(): void
    {
        Storage::fake('local');
        $service = app(ImageCutoutService::class);

        // Create 120x160 synthetic image with vertical gradient background + shadow,
        // and a distinct dark blue subject in the center
        $w = 120;
        $h = 160;
        $im = imagecreatetruecolor($w, $h);

        // Background: Light grey gradient from top to bottom (simulating studio wall gradient & shadow)
        for ($y = 0; $y < $h; $y++) {
            $shade = (int) (230 - ($y / $h) * 50); // 230 down to 180 (gradient shadow)
            $col = imagecolorallocate($im, $shade, $shade, $shade);
            imageline($im, 0, $y, $w - 1, $y, $col);
        }

        // Small isolated noise island in top-left corner (should be cleaned up by largest component)
        $islandCol = imagecolorallocate($im, 10, 10, 10);
        imagefilledrectangle($im, 5, 5, 8, 8, $islandCol);

        // Central subject (ellipse representing head/body with sharp edge and dark color)
        $subjectCol = imagecolorallocate($im, 15, 45, 90);
        imagefilledellipse($im, 60, 80, 50, 70, $subjectCol);

        ob_start();
        imagejpeg($im, null, 95);
        $binary = ob_get_clean();
        imagedestroy($im);

        $dataUri = $service->removeBackgroundDataUri($binary);

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        // Inspect result image
        $b64 = substr($dataUri, strlen('data:image/png;base64,'));
        $resultIm = imagecreatefromstring(base64_decode($b64));

        // 1. Top corner background (with gradient) must be transparent
        $topCornerAlpha = (imagecolorat($resultIm, 2, 2) >> 24) & 0x7F;
        $this->assertGreaterThanOrEqual(120, $topCornerAlpha, 'Gradient background should be transparent');

        // 2. Bottom corner background (with shadow) must be transparent
        $bottomCornerAlpha = (imagecolorat($resultIm, 2, $h - 3) >> 24) & 0x7F;
        $this->assertGreaterThanOrEqual(120, $bottomCornerAlpha, 'Shadowed background should be transparent');

        // 3. Central subject must remain opaque
        $centerAlpha = (imagecolorat($resultIm, 60, 80) >> 24) & 0x7F;
        $this->assertEquals(0, $centerAlpha, 'Center subject should remain opaque');

        // 4. Isolated island at (6,6) should be cleaned up by largest component cleanup
        $islandAlpha = (imagecolorat($resultIm, 6, 6) >> 24) & 0x7F;
        $this->assertGreaterThanOrEqual(120, $islandAlpha, 'Isolated noise island should be removed');

        // 5. Cache key v2 is created
        $files = Storage::disk('local')->files('cutouts');
        $this->assertNotEmpty($files);
        $this->assertTrue(str_contains($files[0], 'v2_'), 'Cache path must contain v2_ prefix');

        imagedestroy($resultIm);
    }
}
