<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $anggota = User::factory()->create(['role' => 'anggota']);
        $fas = User::factory()->create(['role' => 'fasilitator']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isAnggota());
        $this->assertTrue($anggota->isAnggota());
        $this->assertTrue($fas->isFasilitator());
    }

    public function test_default_role_is_anggota(): void
    {
        $u = User::factory()->create();
        $this->assertSame('anggota', $u->fresh()->role);
        $this->assertTrue($u->isAktif());
    }
}
