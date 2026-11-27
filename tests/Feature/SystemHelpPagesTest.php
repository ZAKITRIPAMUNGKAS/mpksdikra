<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHelpPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_panduan_and_updates(): void
    {
        $this->get('/panduan')->assertRedirect('/login');
        $this->get('/updates')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_panduan(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get('/panduan');
        $response->assertStatus(200);
        $response->assertSee('Panduan &amp; Tutorial Penggunaan Sistem', false);
        $response->assertSee('Verifikasi &amp; Validasi Pendaftar', false);
        $response->assertSee('Pertanyaan yang Sering Diajukan (FAQ)', false);
    }

    public function test_authenticated_anggota_can_access_panduan(): void
    {
        $anggota = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($anggota)->get('/panduan');
        $response->assertStatus(200);
        $response->assertSee('Panduan &amp; Tutorial Penggunaan Sistem', false);
    }

    public function test_authenticated_user_can_access_updates_changelog(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->get('/updates');
        $response->assertStatus(200);
        $response->assertSee('Pusat Informasi &amp; Pembaruan Sistem', false);
        $response->assertSee('Versi 2.7.1 Stable', false);
        $response->assertSee('Pilih Peserta Sebelum Cetak Massal');
        $response->assertSee('v2.7.0', false);
        $response->assertSee('Jarkoman Pengingat');
        $response->assertSee('RTL Opsional per Kegiatan');
        $response->assertSee('v2.6.0', false);
        $response->assertSee('v2.5.0', false);
        $response->assertSee('v2.4.0', false);
        $response->assertSee('v2.3.0', false);
        $response->assertSee('v2.2.0', false);
        $response->assertSee('v2.1.0', false);
        $response->assertSee('v2.0.0', false);
    }
}
