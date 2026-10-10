<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\ImageCutoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImageCutoutResolveTest extends TestCase
{
    use RefreshDatabase;

    private function createSyntheticImage(int $w = 80, int $h = 100, string $format = 'jpeg'): string
    {
        $im = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($im, 200, 200, 200);
        imagefill($im, 0, 0, $bg);
        $fg = imagecolorallocate($im, 20, 40, 80);
        imagefilledellipse($im, (int) ($w / 2), (int) ($h / 2), (int) ($w * 0.6), (int) ($h * 0.6), $fg);

        ob_start();
        if ($format === 'png') {
            imagesavealpha($im, true);
            imagepng($im);
        } else {
            imagejpeg($im, null, 90);
        }
        $bin = ob_get_clean();
        imagedestroy($im);

        return $bin;
    }

    public function test_resolve_priority_client_cutout_when_present(): void
    {
        Storage::fake('local');
        $service = app(ImageCutoutService::class);

        $origPath = 'anggota/foto/original.jpg';
        $cutoutPath = 'anggota/cutouts/client.png';

        $origBin = $this->createSyntheticImage(80, 100, 'jpeg');
        $cutoutBin = $this->createSyntheticImage(80, 100, 'png');

        Storage::disk('local')->put($origPath, $origBin);
        Storage::disk('local')->put($cutoutPath, $cutoutBin);

        $user = User::factory()->create(['role' => 'anggota']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto' => $origPath,
            'foto_cutout_path' => $cutoutPath,
            'foto_mode' => 'cutout',
        ]);

        $resolved = $service->resolveForAnggota($anggota);

        $this->assertEquals('cutout', $resolved['mode']);
        $expectedUri = 'data:image/png;base64,'.base64_encode($cutoutBin);
        $this->assertEquals($expectedUri, $resolved['dataUri']);
    }

    public function test_resolve_fallback_to_gd_v2_when_client_cutout_missing(): void
    {
        Storage::fake('local');
        $service = app(ImageCutoutService::class);

        $origPath = 'anggota/foto/original.jpg';
        $origBin = $this->createSyntheticImage(80, 100, 'jpeg');
        Storage::disk('local')->put($origPath, $origBin);

        $user = User::factory()->create(['role' => 'anggota']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto' => $origPath,
            'foto_cutout_path' => null,
            'foto_mode' => 'cutout',
        ]);

        $resolved = $service->resolveForAnggota($anggota);

        $this->assertEquals('cutout', $resolved['mode']);
        $this->assertNotNull($resolved['dataUri']);
        $this->assertStringStartsWith('data:image/png;base64,', $resolved['dataUri']);

        // Check that GD v2 cache was created
        $files = Storage::disk('local')->files('cutouts');
        $this->assertNotEmpty($files);
        $this->assertTrue(str_contains($files[0], 'v2_'));
    }

    public function test_resolve_returns_original_when_mode_is_original(): void
    {
        Storage::fake('local');
        $service = app(ImageCutoutService::class);

        $origPath = 'anggota/foto/original.jpg';
        $cutoutPath = 'anggota/cutouts/client.png';

        $origBin = $this->createSyntheticImage(80, 100, 'jpeg');
        $cutoutBin = $this->createSyntheticImage(80, 100, 'png');

        Storage::disk('local')->put($origPath, $origBin);
        Storage::disk('local')->put($cutoutPath, $cutoutBin);

        $user = User::factory()->create(['role' => 'anggota']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto' => $origPath,
            'foto_cutout_path' => $cutoutPath,
            'foto_mode' => 'original',
        ]);

        $resolved = $service->resolveForAnggota($anggota);

        $this->assertEquals('original', $resolved['mode']);
        $this->assertEquals('data:image/jpeg;base64,'.base64_encode($origBin), $resolved['dataUri']);
    }

    public function test_resolve_falls_back_to_default_photo_when_no_foto(): void
    {
        Storage::fake('local');
        $service = app(ImageCutoutService::class);

        $user = User::factory()->create(['role' => 'anggota']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto' => null,
            'foto_cutout_path' => null,
            'foto_mode' => 'cutout',
        ]);

        $resolved = $service->resolveForAnggota($anggota);

        $this->assertEquals('cutout', $resolved['mode']);
        $this->assertEquals($service->getDefaultPhotoDataUri(), $resolved['dataUri']);
    }

    public function test_portal_id_card_uses_resolved_photo(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Peserta Portal Cutout',
            'foto_cutout_path' => 'anggota/cutouts/cut.png',
            'foto_mode' => 'cutout',
        ]);
        Storage::disk('local')->put('anggota/cutouts/cut.png', $this->createSyntheticImage(80, 100, 'png'));

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Portal Test',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $user->id,
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($user)->get(route('portal.idcard.download', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
