<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FotoAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_access_permissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $u1 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a1 = Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Kader Satu']);

        $u2 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a2 = Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Kader Dua']);

        // Admin can access any photo (returns default profile photo if file not present)
        $this->actingAs($admin)
            ->get(route('foto.anggota', $a1))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');

        // Owner can access own photo
        $this->actingAs($u1)
            ->get(route('foto.anggota', $a1))
            ->assertOk();

        // Other member cannot access another member's photo
        $this->actingAs($u2)
            ->get(route('foto.anggota', $a1))
            ->assertForbidden();

        // Branch admin can access member in their branch
        $a1->update(['pcm' => 'Tawangmangu']);
        $adminCabangSame = User::factory()->create(['role' => 'admin_cabang', 'status' => 'aktif', 'pcm' => 'Tawangmangu']);
        $adminCabangOther = User::factory()->create(['role' => 'admin_cabang', 'status' => 'aktif', 'pcm' => 'Colomadu']);

        $this->actingAs($adminCabangSame)
            ->get(route('foto.anggota', $a1))
            ->assertOk();

        $this->actingAs($adminCabangOther)
            ->get(route('foto.anggota', $a1))
            ->assertForbidden();
    }
}
