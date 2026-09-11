<?php

namespace Tests\Feature;

use App\Imports\AnggotaImport;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AnggotaImportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    public function test_admin_can_access_import_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.anggota.import'))
            ->assertOk()
            ->assertSee('Impor Pangkalan Data Kader');
    }

    public function test_non_admin_cannot_access_import(): void
    {
        $kader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($kader)
            ->get(route('admin.anggota.import'))
            ->assertForbidden();
    }

    public function test_admin_can_download_template(): void
    {
        Excel::fake();

        $this->actingAs($this->admin())
            ->get(route('admin.anggota.import.template'));

        Excel::assertDownloaded('template-impor-kader-mpksdi.xlsx');
    }

    public function test_anggota_import_collection_creates_user_and_anggota(): void
    {
        $rows = new Collection([
            [
                'nama_lengkap' => 'Kader Uji Coba',
                'nik' => '3313010101900001',
                'no_wa' => '081234567890',
                'email' => 'ujicoba@example.com',
                'jenis_kelamin' => 'L',
                'kecamatan' => 'Karanganyar',
                'desa_kelurahan' => 'Bejen',
                'nbm' => '998877',
                'pekerjaan' => 'Guru AUM',
                'keaktifan_takmir' => 'Ya',
                'jabatan_takmir' => 'Ketua Takmir',
                'aktifitas_jamaah_masjid' => 'Sholat Berjamaah 5 Waktu',
                'keaktifan_kemasyarakatan' => 'Pengurus RT 02',
            ],
        ]);

        $import = new AnggotaImport;
        $import->collection($rows);

        $this->assertSame(1, $import->successCount);
        $this->assertDatabaseHas('users', [
            'email' => 'ujicoba@example.com',
            'role' => 'anggota',
            'status' => 'aktif',
        ]);
        $this->assertDatabaseHas('anggota', [
            'nama_lengkap' => 'Kader Uji Coba',
            'nik' => '3313010101900001',
            'keaktifan_takmir' => 'Ya',
            'jabatan_takmir' => 'Ketua Takmir',
        ]);
    }

    public function test_duplicate_nik_is_skipped(): void
    {
        $existing = Anggota::factory()->create(['nik' => '3313010101900002']);

        $rows = new Collection([
            [
                'nama_lengkap' => 'Kader Duplikat',
                'nik' => '3313010101900002',
                'no_wa' => '081234567890',
                'email' => 'duplikat@example.com',
            ],
        ]);

        $import = new AnggotaImport;
        $import->collection($rows);

        $this->assertSame(0, $import->successCount);
        $this->assertCount(1, $import->skipped);
    }
}
