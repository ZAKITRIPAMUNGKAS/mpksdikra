<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CetakCvPdfTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminDaerah(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    private function createAdminCabang(string $pcm = 'Colomadu'): User
    {
        return User::factory()->create([
            'role' => 'admin_cabang',
            'pcm' => $pcm,
            'status' => 'aktif',
        ]);
    }

    private function createAnggota(string $pcm = 'Colomadu'): Anggota
    {
        $user = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

        return Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Uji Coba',
            'nik' => '3313110101900001',
            'nbm' => '1234567',
            'nama_ayah' => 'Ahmad Dahlan',
            'nama_ibu' => 'Siti Walidah',
            'pcm' => $pcm,
            'kecamatan' => $pcm,
            'desa_kelurahan' => 'Gedongan',
            'tempat_lahir' => 'Karanganyar',
            'tanggal_lahir' => '1995-05-15',
            'pendidikan_terakhir' => 'S1',
            'pendidikan_s1' => 'Universitas Muhammadiyah Surakarta',
            'pekerjaan' => 'Guru AUM',
            'no_wa' => '081234567890',
            'custom_fields' => [
                'keahlian_khusus' => 'Desain Grafis & IT',
            ],
        ]);
    }

    public function test_admin_daerah_can_download_anggota_cv_pdf(): void
    {
        $admin = $this->createAdminDaerah();
        $anggota = $this->createAnggota();

        $response = $this->actingAs($admin)
            ->get(route('admin.anggota.cetak-pdf', $anggota));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_cabang_can_download_cv_pdf_for_same_pcm(): void
    {
        $cabang = $this->createAdminCabang('Colomadu');
        $anggota = $this->createAnggota('Colomadu');

        $response = $this->actingAs($cabang)
            ->get(route('admin.anggota.cetak-pdf', $anggota));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_cabang_cannot_download_cv_pdf_for_other_pcm(): void
    {
        $cabang = $this->createAdminCabang('Colomadu');
        $anggota = $this->createAnggota('Karanganyar');

        $this->actingAs($cabang)
            ->get(route('admin.anggota.cetak-pdf', $anggota))
            ->assertForbidden();
    }

    public function test_anggota_can_download_own_cv_pdf_from_portal(): void
    {
        $anggota = $this->createAnggota();

        $response = $this->actingAs($anggota->user)
            ->get(route('portal.profile.cetak-pdf'));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_guest_cannot_download_cv_pdf(): void
    {
        $anggota = $this->createAnggota();

        $this->get(route('admin.anggota.cetak-pdf', $anggota))
            ->assertRedirect('/login');

        $this->get(route('portal.profile.cetak-pdf'))
            ->assertRedirect('/login');
    }
}
