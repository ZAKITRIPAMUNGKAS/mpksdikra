<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Support\TtdTransparan;
use GdImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TtdTransparanTest extends TestCase
{
    use RefreshDatabase;

    /** Gambar TTD tiruan: latar (warna kertas, opsional gradasi bayangan) + goresan tinta. */
    private function gambarTtd(array $kertas, bool $bayangan = false, bool $transparan = false): GdImage
    {
        $w = 600;
        $h = 240;
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        imagealphablending($img, false);
        if ($transparan) {
            imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        } else {
            for ($x = 0; $x < $w; $x++) {
                $redup = $bayangan ? (int) (25 * $x / $w) : 0;
                imageline($img, $x, 0, $x, $h, imagecolorallocate($img, $kertas[0] - $redup, $kertas[1] - $redup, $kertas[2] - $redup));
            }
        }
        imagealphablending($img, true);
        $tinta = imagecolorallocate($img, 25, 40, 130);
        imagesetthickness($img, 6);
        imageline($img, 150, 160, 260, 70, $tinta);
        imageline($img, 260, 70, 330, 170, $tinta);
        imagearc($img, 400, 120, 120, 80, 0, 300, $tinta);

        return $img;
    }

    private function alpha(GdImage $img, int $x, int $y): int
    {
        return (imagecolorat($img, $x, $y) >> 24) & 0x7F;
    }

    /** @return array{0:int,1:int} jumlah piksel pekat & transparan */
    private function hitung(GdImage $img): array
    {
        $pekat = 0;
        $bening = 0;
        for ($y = 0; $y < imagesy($img); $y++) {
            for ($x = 0; $x < imagesx($img); $x++) {
                $a = $this->alpha($img, $x, $y);
                if ($a <= 20) {
                    $pekat++;
                } elseif ($a >= 120) {
                    $bening++;
                }
            }
        }

        return [$pekat, $bening];
    }

    public function test_latar_putih_jadi_transparan_dan_tepi_dipotong(): void
    {
        $hasil = TtdTransparan::olah($this->gambarTtd([255, 255, 255]));

        $this->assertLessThan(600, imagesx($hasil), 'Tepi kosong dipotong.');
        $this->assertLessThan(240, imagesy($hasil));
        $this->assertSame(127, $this->alpha($hasil, 0, 0), 'Pojok jadi transparan.');
        [$pekat, $bening] = $this->hitung($hasil);
        $this->assertGreaterThan(1000, $pekat, 'Goresan tinta tetap pekat.');
        $this->assertGreaterThan($pekat, $bening, 'Sebagian besar latar transparan.');
    }

    public function test_foto_kertas_keabuan_dengan_bayangan_juga_bersih(): void
    {
        $hasil = TtdTransparan::olah($this->gambarTtd([222, 218, 208], bayangan: true));

        $this->assertSame(127, $this->alpha($hasil, 0, 0));
        $this->assertSame(127, $this->alpha($hasil, imagesx($hasil) - 1, imagesy($hasil) - 1));
        [$pekat] = $this->hitung($hasil);
        $this->assertGreaterThan(1000, $pekat);
    }

    public function test_png_yang_sudah_transparan_tetap_utuh(): void
    {
        $hasil = TtdTransparan::olah($this->gambarTtd([0, 0, 0], transparan: true));

        $this->assertSame(127, $this->alpha($hasil, 0, 0));
        [$pekat] = $this->hitung($hasil);
        $this->assertGreaterThan(1000, $pekat);
    }

    public function test_perintah_proses_ulang_ttd_lama(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        ob_start();
        imagepng($this->gambarTtd([255, 255, 255]));
        Storage::disk('public')->put('signatures/lama.png', ob_get_clean());
        $event = Event::create([
            'nama_event' => 'BA TTD Lama', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02',
            'status' => 'persiapan', 'created_by' => $admin->id,
        ]);
        $event->forceFill(['ttd_kiri_path' => 'signatures/lama.png', 'ttd_kanan_path' => 'signatures/hilang.png'])->save();

        $this->artisan('sertifikat:ttd-transparan')->expectsOutputToContain('1 tanda tangan')->assertSuccessful();

        $event->refresh();
        $this->assertNotSame('signatures/lama.png', $event->ttd_kiri_path);
        Storage::disk('public')->assertMissing('signatures/lama.png');
        $this->assertSame(127, $this->alpha(imagecreatefromstring(Storage::disk('public')->get($event->ttd_kiri_path)), 0, 0));
        $this->assertSame('signatures/hilang.png', $event->ttd_kanan_path, 'File yang tidak ada dibiarkan.');
    }

    public function test_unggah_jpg_lewat_form_kegiatan_disimpan_sebagai_png_transparan(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA TTD', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02',
            'status' => 'persiapan', 'created_by' => $admin->id,
        ]);

        $img = $this->gambarTtd([250, 250, 250]);
        $tmp = tempnam(sys_get_temp_dir(), 'ttd').'.jpg';
        imagejpeg($img, $tmp, 90);
        $file = new UploadedFile($tmp, 'ttd-mot.jpg', 'image/jpeg', null, true);

        $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'nama_event' => 'BA TTD', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02',
            'status' => 'persiapan', 'bobot_kognitif' => 45, 'bobot_psikomotor' => 25, 'bobot_afektif' => 30, 'bobot_keaktifan' => 0,
            'ttd_kiri' => $file,
        ])->assertSessionHasNoErrors();

        $path = $event->fresh()->ttd_kiri_path;
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
        $hasil = imagecreatefromstring(Storage::disk('public')->get($path));
        $this->assertSame(127, $this->alpha($hasil, 0, 0));
        @unlink($tmp);
    }
}
