<?php

namespace Tests\Feature;

use App\Support\LembarJawabanGrafik;
use App\Support\LembarJawabanLayout;
use Tests\TestCase;

class LembarJawabanGrafikTest extends TestCase
{
    public function test_background_is_png_with_markers_at_layout_coordinates(): void
    {
        $layout = LembarJawabanLayout::build(25, ['A', 'B', 'C', 'D']);
        $uri = LembarJawabanGrafik::latarDataUri($layout);

        $this->assertStringStartsWith('data:image/png;base64,', $uri);
        $img = imagecreatefromstring(base64_decode(substr($uri, strlen('data:image/png;base64,'))));
        $this->assertSame((int) round($layout['page']['w'] * 3), imagesx($img));

        foreach ($layout['markers'] as $mk) {
            $rgba = imagecolorat($img, (int) round($mk['x'] * 3), (int) round($mk['y'] * 3));
            $this->assertSame(0, $rgba & 0xFFFFFF, 'pusat kotak pojok harus hitam');
        }

        $o = $layout['soal'][0]['opsi'][0];
        $tepi = imagecolorat($img, (int) round(($o['x'] + $layout['r'] - 0.4) * 3), (int) round($o['y'] * 3));
        $this->assertLessThan(0x808080, $tepi & 0xFFFFFF, 'garis bulatan harus gelap tepat di jari-jari layout');
    }
}
