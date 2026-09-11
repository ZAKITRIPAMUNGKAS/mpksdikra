<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_can_view_profile(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('portal.profile.index'))
            ->assertOk();
    }

    public function test_anggota_can_update_profile_without_reverification(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => 'Lama']);

        $this->actingAs($user)
            ->put(route('portal.profile.update'), [
                'nama_lengkap' => 'Baru Diperbarui',
                'alamat_rumah' => 'Jl. Baru 99',
                'tempat_lahir' => $a->tempat_lahir,
                'tanggal_lahir' => $a->tanggal_lahir->format('Y-m-d'),
                'status_pernikahan' => $a->status_pernikahan,
                'desa_kelurahan' => $a->desa_kelurahan,
                'kecamatan' => $a->kecamatan,
                'kabupaten' => $a->kabupaten,
            ])
            ->assertRedirect();

        $this->assertSame('Baru Diperbarui', $a->fresh()->nama_lengkap);
        $this->assertSame('aktif', $user->fresh()->status);
    }
}
