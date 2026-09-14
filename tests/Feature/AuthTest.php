<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_admin_redirects_to_admin_dashboard(): void
    {
        $u = User::factory()->create([
            'email' => 'admin@x.test',
            'password' => Hash::make('rahasia123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->post('/login', ['email' => 'admin@x.test', 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($u);
    }

    public function test_login_anggota_redirects_to_portal(): void
    {
        User::factory()->create([
            'email' => 'a@x.test',
            'password' => Hash::make('pass123'),
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

        $this->post('/login', ['email' => 'a@x.test', 'password' => 'pass123'])
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_pending_anggota_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'p@x.test',
            'password' => Hash::make('pass123'),
            'role' => 'anggota',
            'status' => 'menunggu_verifikasi',
        ]);

        $this->post('/login', ['email' => 'p@x.test', 'password' => 'pass123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_wrong_password_blocked(): void
    {
        User::factory()->create(['email' => 'x@x.test']);

        $this->post('/login', ['email' => 'x@x.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_route_forbidden_for_anggota(): void
    {
        $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($u)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_portal_route_forbidden_for_admin(): void
    {
        $u = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $this->actingAs($u)->get('/portal/dashboard')->assertForbidden();
    }

    public function test_login_anggota_using_phone_number_and_birthdate(): void
    {
        $user = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
            'no_wa' => '6281234567890',
            'password' => Hash::make('17081985'),
        ]);

        Anggota::factory()->create([
            'user_id' => $user->id,
            'no_wa' => '6281234567890',
            'tanggal_lahir' => '1985-08-17',
        ]);

        // Login menggunakan format nomor lokal 081234567890
        $this->post('/login', [
            'login' => '081234567890',
            'password' => '17081985',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_anggota_using_formatted_phone_number_and_birthdate_with_dashes(): void
    {
        $user = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
            'no_wa' => '6289988776655',
            'password' => Hash::make('25121990'),
        ]);

        Anggota::factory()->create([
            'user_id' => $user->id,
            'no_wa' => '6289988776655',
            'tanggal_lahir' => '1990-12-25',
        ]);

        // Login menggunakan nomor dengan spasi dan password tanggal lahir dengan tanda hubung
        $this->post('/login', [
            'login' => '0899-8877-6655',
            'password' => '25-12-1990',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
