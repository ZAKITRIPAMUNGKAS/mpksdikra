<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Organisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_has_user_relation(): void
    {
        $u = User::factory()->create(['role' => 'anggota']);
        $a = Anggota::factory()->create(['user_id' => $u->id, 'nik' => '1234567890123456']);

        $this->assertTrue($a->user->is($u));
        $this->assertSame('1234567890123456', $a->nik);
    }

    public function test_anggota_can_have_multiple_organisasi(): void
    {
        $a = Anggota::factory()->create();
        $o1 = Organisasi::create(['nama' => 'PRM Mangkubumen', 'kategori' => 'prm']);
        $o2 = Organisasi::create(['nama' => 'PDM Surakarta', 'kategori' => 'pdm']);

        $a->organisasi()->attach($o1->id, ['jabatan' => 'Ketua', 'periode' => '2025-2030']);
        $a->organisasi()->attach($o2->id, ['jabatan' => 'Anggota', 'periode' => '2025-2030']);

        $this->assertCount(2, $a->fresh()->organisasi);
    }

    public function test_json_fields_cast_to_array(): void
    {
        $a = Anggota::factory()->create([
            'bahasa_dikuasai' => ['Indonesia', 'Jawa'],
            'lembaga_zis' => ['Lazismu'],
        ]);

        $fresh = $a->fresh();
        $this->assertSame(['Indonesia', 'Jawa'], $fresh->bahasa_dikuasai);
        $this->assertSame(['Lazismu'], $fresh->lembaga_zis);
    }
}
