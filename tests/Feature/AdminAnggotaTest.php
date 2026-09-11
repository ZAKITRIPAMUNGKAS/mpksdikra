<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnggotaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function pendingAnggota(): Anggota
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'menunggu_verifikasi']);

        return Anggota::factory()->create(['user_id' => $user->id]);
    }

    public function test_admin_can_see_anggota_list(): void
    {
        $admin = $this->admin();
        $this->pendingAnggota();

        $this->actingAs($admin)
            ->get(route('admin.anggota.index'))
            ->assertOk()
            ->assertSee('menunggu_verifikasi');
    }

    public function test_non_admin_cannot_access_anggota_list(): void
    {
        $anggota = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($anggota)
            ->get(route('admin.anggota.index'))
            ->assertForbidden();
    }

    public function test_admin_can_verify_anggota(): void
    {
        $admin = $this->admin();
        $a = $this->pendingAnggota();

        $this->actingAs($admin)
            ->post(route('admin.anggota.verify', $a))
            ->assertRedirect(route('admin.anggota.show', $a));

        $this->assertSame('aktif', $a->user->fresh()->status);
        $this->assertNotNull($a->user->fresh()->verified_at);
    }

    public function test_admin_can_deactivate_anggota(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->post(route('admin.anggota.deactivate', $a))
            ->assertRedirect(route('admin.anggota.show', $a));

        $this->assertSame('nonaktif', $user->fresh()->status);
    }
}
