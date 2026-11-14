<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAnggotaFotoStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_photo_via_file_upload(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'foto' => 'anggota/foto/old_photo.jpg',
            'foto_cutout_path' => 'anggota/cutouts/old_cutout.png',
            'foto_mode' => 'original',
        ]);
        Storage::disk('local')->put('anggota/foto/old_photo.jpg', 'old_bytes');
        Storage::disk('local')->put('anggota/cutouts/old_cutout.png', 'old_cutout_bytes');

        $newPhoto = UploadedFile::fake()->image('pasfoto_baru.jpg', 600, 800);

        $response = $this->actingAs($admin)->post(route('admin.anggota.foto.update', $anggota), [
            'foto' => $newPhoto,
            'foto_mode' => 'original',
        ]);

        $response->assertRedirect();
        $fresh = $anggota->fresh();

        $this->assertNotNull($fresh->foto);
        $this->assertNotSame('anggota/foto/old_photo.jpg', $fresh->foto);
        Storage::disk('local')->assertMissing('anggota/foto/old_photo.jpg');
        Storage::disk('local')->assertExists($fresh->foto);
        $this->assertSame('original', $fresh->foto_mode);
    }

    public function test_admin_can_update_photo_via_base64_data_uri_and_ajax(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'foto' => null,
            'foto_cutout_path' => null,
            'foto_mode' => 'cutout',
        ]);

        // Generate tiny 3x4 red JPEG base64
        $img = imagecreatetruecolor(30, 40);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefilledrectangle($img, 0, 0, 30, 40, $red);
        ob_start();
        imagejpeg($img);
        $rawJpg = ob_get_clean();
        imagedestroy($img);
        $dataUri = 'data:image/jpeg;base64,'.base64_encode($rawJpg);

        $response = $this->actingAs($admin)->postJson(route('admin.anggota.foto.update', $anggota), [
            'foto' => $dataUri,
            'foto_mode' => 'cutout',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'foto_mode' => 'cutout',
        ]);

        $fresh = $anggota->fresh();
        $this->assertNotNull($fresh->foto);
        Storage::disk('local')->assertExists($fresh->foto);
    }

    public function test_admin_cabang_can_only_update_their_own_pcm_anggota(): void
    {
        Storage::fake('local');

        $adminCabang = User::factory()->create([
            'role' => 'admin_cabang',
            'status' => 'aktif',
            'pcm' => 'Karanganyar',
        ]);

        $anggotaSameBranch = Anggota::factory()->create([
            'pcm' => 'Karanganyar',
        ]);
        $anggotaOtherBranch = Anggota::factory()->create([
            'pcm' => 'Colomadu',
        ]);

        $photo = UploadedFile::fake()->image('foto.jpg', 300, 400);

        // Allowed on same branch
        $res1 = $this->actingAs($adminCabang)->post(route('admin.anggota.foto.update', $anggotaSameBranch), [
            'foto' => $photo,
            'foto_mode' => 'original',
        ]);
        $res1->assertRedirect();

        // Forbidden on other branch
        $res2 = $this->actingAs($adminCabang)->post(route('admin.anggota.foto.update', $anggotaOtherBranch), [
            'foto' => $photo,
            'foto_mode' => 'original',
        ]);
        $res2->assertForbidden();
    }

    public function test_non_admin_cannot_update_photo(): void
    {
        $regularUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create();

        $photo = UploadedFile::fake()->image('foto.jpg', 300, 400);

        $this->actingAs($regularUser)
            ->post(route('admin.anggota.foto.update', $anggota), [
                'foto' => $photo,
                'foto_mode' => 'original',
            ])
            ->assertForbidden();
    }

    public function test_admin_anggota_show_renders_studio_with_both_tabs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'nama_lengkap' => 'Ahmad Dahlan Demo',
            'foto_mode' => 'cutout',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.anggota.show', $anggota));

        $res->assertOk();
        $res->assertSee('Pengaturan Pasfoto & Cutout ID Card', false);
        $res->assertSee('Studio Pasfoto & Penyesuaian ID Card', false);
        $res->assertSee('1. Edit &amp; Reposisi Pasfoto (3:4)', false);
        $res->assertSee('2. Centering &amp; Pratinjau ID Card (Anti-Gepeng)', false);
        $res->assertSee('Mockup Kartu Tanda Peserta (Fisik)', false);
        $res->assertSee('Rasio Aspek Terkunci (100% Anti-Gepeng)', false);
        $res->assertSee('Simpan Foto &amp; Pengaturan ID Card', false);
    }
}
