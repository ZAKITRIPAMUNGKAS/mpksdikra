<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaRejectionAndSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reject_anggota_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'menunggu_verifikasi', 'email' => 'calon@mpksdi.test', 'password' => 'secret123']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => 'Calon Kader']);

        $this->actingAs($admin)
            ->post(route('admin.anggota.reject', $anggota), [
                'catatan_verifikasi' => 'Foto KTP tidak jelas, mohon perbarui.',
            ])
            ->assertRedirect(route('admin.anggota.show', $anggota));

        $user->refresh();
        $anggota->refresh();

        $this->assertSame('ditolak', $user->status);
        $this->assertSame('Foto KTP tidak jelas, mohon perbarui.', $anggota->catatan_verifikasi);

        // Logout admin and attempt login as rejected user
        auth()->logout();
        $this->post('/login', [
            'email' => 'calon@mpksdi.test',
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_search_anggota(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $u1 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create(['user_id' => $u1->id, 'nama_lengkap' => 'Ahmad Dahlan', 'kecamatan' => 'Karanganyar']);

        $u2 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create(['user_id' => $u2->id, 'nama_lengkap' => 'Siti Walidah', 'kecamatan' => 'Colomadu']);

        $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['q' => 'Dahlan']))
            ->assertOk()
            ->assertSee('Ahmad Dahlan')
            ->assertDontSee('Siti Walidah');

        $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['q' => 'Colomadu']))
            ->assertOk()
            ->assertSee('Siti Walidah')
            ->assertDontSee('Ahmad Dahlan');
    }
}
