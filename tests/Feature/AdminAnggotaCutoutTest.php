<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAnggotaCutoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_cutout_and_original_thumbnails(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $cutoutPath = 'anggota/cutouts/test_cutout.png';
        Storage::disk('local')->put($cutoutPath, 'fake_png_bytes');

        $anggota = Anggota::factory()->create([
            'foto' => 'anggota/foto/test_src.jpg',
            'foto_cutout_path' => $cutoutPath,
            'foto_mode' => 'cutout',
        ]);
        Storage::disk('local')->put('anggota/foto/test_src.jpg', 'fake_jpg_bytes');

        $res = $this->actingAs($admin)->get(route('admin.anggota.show', $anggota));
        $res->assertOk();
        $res->assertSee('Pengaturan Pasfoto & Cutout ID Card', false);
        $res->assertSee('Mode Aktif:', false);
        $res->assertSee('Cutout (Transparan)', false);

        // Test FotoController serves cutout
        $fotoRes = $this->actingAs($admin)->get(route('foto.anggota', [$anggota, 'type' => 'cutout']));
        $fotoRes->assertOk();
    }

    public function test_admin_can_update_foto_mode(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['foto_mode' => 'cutout']);

        // Switch to original
        $this->actingAs($admin)
            ->patch(route('admin.anggota.foto-mode', $anggota), [
                'foto_mode' => 'original',
            ])
            ->assertRedirect();

        $this->assertSame('original', $anggota->fresh()->foto_mode);

        // Switch back to cutout
        $this->actingAs($admin)
            ->patch(route('admin.anggota.foto-mode', $anggota), [
                'foto_mode' => 'cutout',
            ])
            ->assertRedirect();

        $this->assertSame('cutout', $anggota->fresh()->foto_mode);
    }

    public function test_admin_can_destroy_cutout(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $cutoutPath = 'anggota/cutouts/to_delete.png';
        Storage::disk('local')->put($cutoutPath, 'cutout_bytes');

        $anggota = Anggota::factory()->create([
            'foto_cutout_path' => $cutoutPath,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.anggota.cutout.destroy', $anggota))
            ->assertRedirect();

        $this->assertNull($anggota->fresh()->foto_cutout_path);
        Storage::disk('local')->assertMissing($cutoutPath);
    }

    public function test_admin_destroy_anggota_removes_both_source_and_cutout(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $srcPath = 'anggota/foto/delete_me.jpg';
        $cutoutPath = 'anggota/cutouts/delete_me.png';

        Storage::disk('local')->put($srcPath, 'src_bytes');
        Storage::disk('local')->put($cutoutPath, 'cutout_bytes');

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto' => $srcPath,
            'foto_cutout_path' => $cutoutPath,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.anggota.destroy', $anggota))
            ->assertRedirect(route('admin.anggota.index'));

        Storage::disk('local')->assertMissing($srcPath);
        Storage::disk('local')->assertMissing($cutoutPath);
    }

    public function test_non_admin_cannot_manage_photo_mode_or_delete_cutout(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create();

        $this->actingAs($user)
            ->patch(route('admin.anggota.foto-mode', $anggota), ['foto_mode' => 'original'])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('admin.anggota.cutout.destroy', $anggota))
            ->assertForbidden();
    }
}
