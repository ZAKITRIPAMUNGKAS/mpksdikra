<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Manajemen Akun Staf');

        // Admin can create a new fasilitator
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Fasilitator Baru',
                'email' => 'fasil.baru@mpksdi.test',
                'role' => 'fasilitator',
                'no_wa' => '081234567890',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'fasil.baru@mpksdi.test')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('fasilitator', $newUser->role);
        $this->assertSame('aktif', $newUser->status);

        // Toggle status
        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $newUser))
            ->assertRedirect();

        $newUser->refresh();
        $this->assertSame('nonaktif', $newUser->status);

        // Admin cannot toggle self
        $this->actingAs($admin)
            ->post(route('admin.users.toggle', $admin))
            ->assertForbidden();
    }

    public function test_admin_can_view_and_create_admin_cabang_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $cabang = User::factory()->create([
            'role' => 'admin_cabang',
            'name' => 'Admin PCM Colomadu',
            'email' => 'colomadu',
            'pcm' => 'Colomadu',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'admin_cabang']))
            ->assertOk()
            ->assertSee('Admin PCM Colomadu')
            ->assertSee('PCM Colomadu')
            ->assertSee('Admin Cabang (PCM)');

        // Admin daerah can create admin cabang
        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Admin PCM Jaten Baru',
                'email' => 'jaten_baru',
                'role' => 'admin_cabang',
                'pcm' => 'Jaten',
                'no_wa' => '081226001003',
                'password' => 'cabangkra123',
                'password_confirmation' => 'cabangkra123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $newCabang = User::where('email', 'jaten_baru')->first();
        $this->assertNotNull($newCabang);
        $this->assertSame('admin_cabang', $newCabang->role);
        $this->assertSame('Jaten', $newCabang->pcm);
        $this->assertSame('aktif', $newCabang->status);
    }
}
