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

class IdCardBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_batch_id_cards_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Ahmad Dahlan',
            'pcm' => 'Karanganyar',
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.idcards.batch', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_batch_id_cards_aborts_when_no_active_participants(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Kosong',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.idcards.batch', $event));

        $response->assertStatus(404);
    }

    public function test_image_cutout_service_removes_solid_background_and_caches_result(): void
    {
        $service = app(ImageCutoutService::class);

        // Create a test image: 100x100 with solid blue background and red circle in center
        $im = imagecreatetruecolor(100, 100);
        $blueBg = imagecolorallocate($im, 0, 50, 200);
        imagefill($im, 0, 0, $blueBg);
        $redCenter = imagecolorallocate($im, 220, 20, 20);
        imagefilledellipse($im, 50, 50, 40, 40, $redCenter);

        ob_start();
        imagejpeg($im);
        $binary = ob_get_clean();
        imagedestroy($im);

        $dataUri = $service->removeBackgroundDataUri($binary);

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        // Decode and inspect transparency
        $b64 = substr($dataUri, strlen('data:image/png;base64,'));
        $cutoutIm = imagecreatefromstring(base64_decode($b64));

        $cornerColor = imagecolorat($cutoutIm, 2, 2);
        $cornerAlpha = ($cornerColor >> 24) & 0x7F;
        $this->assertEquals(127, $cornerAlpha, 'Corner pixel should be fully transparent');

        $centerColor = imagecolorat($cutoutIm, 50, 50);
        $centerAlpha = ($centerColor >> 24) & 0x7F;
        $this->assertEquals(0, $centerAlpha, 'Center circle should remain fully opaque');

        imagedestroy($cutoutIm);

        // Test cache hit
        $cachedDataUri = $service->removeBackgroundDataUri($binary);
        $this->assertEquals($dataUri, $cachedDataUri);
    }

    public function test_batch_id_cards_automatically_removes_background_for_participant_photo(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Test Cutout',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        // Create a sample participant image in storage
        $im = imagecreatetruecolor(100, 100);
        $blueBg = imagecolorallocate($im, 20, 60, 220);
        imagefill($im, 0, 0, $blueBg);
        $personColor = imagecolorallocate($im, 200, 150, 100);
        imagefilledellipse($im, 50, 50, 40, 60, $personColor);

        ob_start();
        imagejpeg($im);
        $jpegData = ob_get_clean();
        imagedestroy($im);

        $photoPath = 'anggota/foto/test-peserta.jpg';
        Storage::disk('local')->put($photoPath, $jpegData);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Kader Cutout',
            'pcm' => 'Karanganyar',
            'foto' => $photoPath,
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.idcards.batch', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // Verify the cutout was cached in storage
        $hash = md5($jpegData.'|v2|15');
        $this->assertTrue(Storage::disk('local')->exists("cutouts/v2_{$hash}.png"));
    }
}
