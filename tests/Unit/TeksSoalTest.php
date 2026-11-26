<?php

namespace Tests\Unit;

use App\Support\TeksSoal;
use PHPUnit\Framework\TestCase;

class TeksSoalTest extends TestCase
{
    public function test_strips_leading_number_only_once(): void
    {
        $this->assertSame('Ayat manakah?', TeksSoal::tanpaNomor('23. Ayat manakah?'));
        $this->assertSame('Ayat 2. dan 3. manakah?', TeksSoal::tanpaNomor('1) Ayat 2. dan 3. manakah?'));
        $this->assertSame('QS 3:103 berbunyi', TeksSoal::tanpaNomor('QS 3:103 berbunyi'));
        $this->assertSame('2026 adalah tahun', TeksSoal::tanpaNomor('2026 adalah tahun'));
    }

    public function test_strips_option_letter_only_when_it_matches_label(): void
    {
        $this->assertSame('Tabligh', TeksSoal::tanpaLabel('d. Tabligh', 'D'));
        $this->assertSame('Tabligh', TeksSoal::tanpaLabel('D) Tabligh', 'D'));
        $this->assertSame('a. Tabligh', TeksSoal::tanpaLabel('a. Tabligh', 'D'));
        $this->assertSame('Dakwah', TeksSoal::tanpaLabel('Dakwah', 'D'));
    }

    public function test_wraps_arabic_runs_and_escapes_html(): void
    {
        $html = TeksSoal::html('Bacaan <b> الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ ۝ selesai');

        $this->assertStringContainsString('Bacaan &lt;b&gt; ', $html);
        $this->assertStringContainsString('<span class="ar" dir="rtl" lang="ar">الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ ۝</span>', $html);
        $this->assertStringEndsWith(' selesai', $html);
        $this->assertSame('Tanpa arab', TeksSoal::html('Tanpa arab'));
        $this->assertSame('Amar (<span class="ar" dir="rtl" lang="ar">أَمْرٌ بِالْمَعْرُوفِ</span>)', TeksSoal::html('Amar (أَمْرٌ بِالْمَعْرُوفِ)'));
    }
}
