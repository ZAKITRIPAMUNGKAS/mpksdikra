<?php

namespace Tests\Unit;

use App\Support\AwanKata;
use PHPUnit\Framework\TestCase;

class AwanKataTest extends TestCase
{
    public function test_menghitung_kata_tanpa_stopword_dan_sekali_per_jawaban(): void
    {
        $hasil = AwanKata::dari([
            'Saya sangat bahagia, bahagia sekali!',
            'Bahagia dan termotivasi untuk berdakwah',
            'Termotivasi. Ukhuwah terasa kuat',
            '',
            null,
        ]);
        $peta = array_column($hasil, 'jumlah', 'kata');

        $this->assertSame(2, $peta['bahagia'], 'Kata berulang dalam satu jawaban dihitung sekali.');
        $this->assertSame(2, $peta['termotivasi']);
        $this->assertSame(1, $peta['ukhuwah']);
        $this->assertArrayNotHasKey('saya', $peta);
        $this->assertArrayNotHasKey('sangat', $peta);
        $this->assertArrayNotHasKey('dan', $peta);
    }

    public function test_kata_terbanyak_paling_besar_dan_di_tengah(): void
    {
        $hasil = AwanKata::dari(['senang haru', 'senang syukur', 'senang', 'haru']);
        $terbesar = collect($hasil)->sortByDesc('ukuran')->first();

        $this->assertSame('senang', $terbesar['kata']);
        $this->assertEqualsWithDelta(3.6, $terbesar['ukuran'], 0.01);
        $this->assertSame('senang', $hasil[intdiv(count($hasil), 2)]['kata'], 'Kata terbesar di tengah awan.');
        $this->assertContains($terbesar['warna'], AwanKata::WARNA);
    }

    public function test_frasa_luar_biasa_tetap_utuh(): void
    {
        $kata = array_column(AwanKata::dari(['Luar biasa berkesan', 'luar biasa, percaya diri meningkat']), 'jumlah', 'kata');

        $this->assertSame(2, $kata['luar biasa']);
        $this->assertSame(1, $kata['percaya diri']);
        $this->assertArrayNotHasKey('luar', $kata);
        $this->assertArrayNotHasKey('biasa', $kata);
    }

    public function test_kosong_menghasilkan_array_kosong(): void
    {
        $this->assertSame([], AwanKata::dari(['', 'dan yang di', null]));
    }
}
