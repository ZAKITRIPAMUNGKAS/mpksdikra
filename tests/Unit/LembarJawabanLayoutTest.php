<?php

namespace Tests\Unit;

use App\Support\LembarJawabanLayout;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LembarJawabanLayoutTest extends TestCase
{
    public function test_kode_encodes_participant_and_checksum(): void
    {
        $bits = LembarJawabanLayout::kode(7, 'posttest', 1234);
        $this->assertCount(32, $bits);

        $nilai = array_reduce($bits, fn ($c, $b) => $c * 2 + $b, 0);
        $this->assertSame(1234, $nilai >> 8);
        $this->assertSame(LembarJawabanLayout::checksum(7, 'posttest', 1234), $nilai & 255);
    }

    public function test_checksum_differs_between_pretest_and_posttest(): void
    {
        $this->assertNotSame(
            LembarJawabanLayout::checksum(7, 'pretest', 1234),
            LembarJawabanLayout::checksum(7, 'posttest', 1234)
        );
    }

    public function test_layout_keeps_bubbles_inside_page_and_markers(): void
    {
        $layout = LembarJawabanLayout::build(90, ['A', 'B', 'C', 'D']);
        $minX = $layout['markers'][0]['x'];
        $maxX = $layout['markers'][1]['x'];
        $maxY = $layout['markers'][2]['y'];

        $this->assertCount(90, $layout['soal']);
        foreach ($layout['soal'] as $soal) {
            foreach ($soal['opsi'] as $opsi) {
                $this->assertGreaterThan($minX, $opsi['x'] - $layout['r']);
                $this->assertLessThan($maxX, $opsi['x'] + $layout['r']);
                $this->assertLessThan($maxY - $layout['marker'], $opsi['y'] + $layout['r']);
            }
        }
    }

    public function test_too_many_questions_for_one_sheet_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LembarJawabanLayout::build(91, ['A', 'B', 'C', 'D']);
    }
}
