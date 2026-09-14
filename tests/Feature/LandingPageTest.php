<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_renders_for_guest(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('MPKSDI')
            ->assertSee('Daftar sebagai Kader')
            ->assertSee('H. Zainudin Ahpandi, S.E');
    }

    public function test_landing_shows_dashboard_link_for_authed_user(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('anggota.register'))
            ->assertOk()
            ->assertSee('Daftar Anggota Muhammadiyah');
    }
}
