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

    public function test_anggota_can_update_pcm_prm_pekerjaan_and_jabatan(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('portal.profile.update'), [
                'nama_lengkap' => $a->nama_lengkap,
                'alamat_rumah' => $a->alamat_rumah,
                'tempat_lahir' => $a->tempat_lahir,
                'tanggal_lahir' => $a->tanggal_lahir->format('Y-m-d'),
                'status_pernikahan' => $a->status_pernikahan,
                'desa_kelurahan' => $a->desa_kelurahan,
                'kecamatan' => $a->kecamatan,
                'kabupaten' => $a->kabupaten,
                'nbm' => '9988776',
                'pekerjaan' => 'Guru Muhammadiyah',
                'pcm' => 'Tasikmadu',
                'prm' => 'PRM Papahan',
                'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah', 'Amal Usaha Muhammadiyah'],
                'jabatan_muhammadiyah' => [
                    'Pimpinan Cabang Muhammadiyah' => 'Wakil Ketua PCM',
                    'Amal Usaha Muhammadiyah' => 'Kepala Sekolah',
                ],
                'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
                'jabatan_ortom' => [
                    'Pemuda Muhammadiyah' => 'Ketua PCPM',
                ],
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertSame('9988776', $fresh->nbm);
        $this->assertSame('Guru Muhammadiyah', $fresh->pekerjaan);
        $this->assertSame('Tasikmadu', $fresh->pcm);
        $this->assertSame('PRM Papahan', $fresh->prm);
        $this->assertSame('Wakil Ketua PCM', $fresh->jabatan_muhammadiyah['Pimpinan Cabang Muhammadiyah'] ?? null);
        $this->assertSame('Ketua PCPM', $fresh->jabatan_ortom['Pemuda Muhammadiyah'] ?? null);
        $this->assertSame('Kepala Sekolah', $fresh->jabatan_aum);
    }

    public function test_anggota_can_update_education_background(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('portal.profile.update'), [
                'nama_lengkap' => $a->nama_lengkap,
                'alamat_rumah' => $a->alamat_rumah,
                'tempat_lahir' => $a->tempat_lahir,
                'tanggal_lahir' => $a->tanggal_lahir->format('Y-m-d'),
                'status_pernikahan' => $a->status_pernikahan,
                'desa_kelurahan' => $a->desa_kelurahan,
                'kecamatan' => $a->kecamatan,
                'kabupaten' => $a->kabupaten,
                'pendidikan_terakhir' => 'S1',
                'pendidikan_sd' => 'SDN 2 Karanganyar',
                'pendidikan_smp' => 'SMPN 1 Karanganyar',
                'pendidikan_sma' => 'SMAN 1 Karanganyar',
                'pendidikan_s1' => 'UNS - FKIP',
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertSame('S1', $fresh->pendidikan_terakhir);
        $this->assertSame('SDN 2 Karanganyar', $fresh->pendidikan_sd);
        $this->assertSame('SMPN 1 Karanganyar', $fresh->pendidikan_smp);
        $this->assertSame('SMAN 1 Karanganyar', $fresh->pendidikan_sma);
        $this->assertSame('UNS - FKIP', $fresh->pendidikan_s1);
    }
}
