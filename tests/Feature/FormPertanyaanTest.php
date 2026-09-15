<?php

namespace Tests\Feature;

use App\Models\FormPertanyaan;
use App\Models\User;
use Database\Seeders\FormPertanyaanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormPertanyaanTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminDaerah(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    private function createAdminCabang(): User
    {
        return User::factory()->create([
            'role' => 'admin_cabang',
            'pcm' => 'Colomadu',
            'status' => 'aktif',
        ]);
    }

    private function createAnggota(): User
    {
        return User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_daerah_can_access_form_pertanyaan_page(): void
    {
        $this->seed(FormPertanyaanSeeder::class);
        $admin = $this->createAdminDaerah();

        $response = $this->actingAs($admin)->get(route('admin.form-kader.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan Formulir Pendaftaran Kader');
        $response->assertSee('Nama Ayah Kandung');
        $response->assertSee('Nama Ibu Kandung');
    }

    public function test_non_admin_daerah_cannot_access_form_pertanyaan_page(): void
    {
        $cabang = $this->createAdminCabang();
        $anggota = $this->createAnggota();

        $this->actingAs($cabang)->get(route('admin.form-kader.index'))->assertForbidden();
        $this->actingAs($anggota)->get(route('admin.form-kader.index'))->assertForbidden();
    }

    public function test_admin_daerah_can_create_custom_question(): void
    {
        $admin = $this->createAdminDaerah();

        $payload = [
            'seksi' => 'diri',
            'label' => 'Ukuran Kaos Seragam',
            'tipe' => 'select',
            'opsi_raw' => "S\nM\nL\nXL\nXXL",
            'placeholder' => 'Pilih ukuran kaos Anda',
            'bantuan' => 'Digunakan untuk pembuatan seragam resmi Baitul Arqam.',
            'is_wajib' => 1,
        ];

        $response = $this->actingAs($admin)->post(route('admin.form-kader.store'), $payload);

        $response->assertRedirect(route('admin.form-kader.index', ['seksi' => 'diri']));
        $response->assertSessionHas('success');

        $pertanyaan = FormPertanyaan::where('label', 'Ukuran Kaos Seragam')->first();
        $this->assertNotNull($pertanyaan);
        $this->assertSame('diri', $pertanyaan->seksi);
        $this->assertSame('select', $pertanyaan->tipe);
        $this->assertEquals(['S', 'M', 'L', 'XL', 'XXL'], $pertanyaan->opsi);
        $this->assertTrue($pertanyaan->is_wajib);
        $this->assertTrue($pertanyaan->is_custom);
        $this->assertTrue($pertanyaan->is_aktif);
    }

    public function test_admin_daerah_can_toggle_aktif_and_wajib(): void
    {
        $admin = $this->createAdminDaerah();
        $pertanyaan = FormPertanyaan::create([
            'seksi' => 'diri',
            'kode' => 'custom_test_toggle',
            'label' => 'Pertanyaan Uji Toggle',
            'tipe' => 'text',
            'is_wajib' => false,
            'is_aktif' => true,
            'is_custom' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.form-kader.toggle-wajib', $pertanyaan));
        $this->assertTrue($pertanyaan->fresh()->is_wajib);

        $this->actingAs($admin)->post(route('admin.form-kader.toggle-aktif', $pertanyaan));
        $this->assertFalse($pertanyaan->fresh()->is_aktif);
    }

    public function test_admin_daerah_can_update_and_delete_custom_question(): void
    {
        $admin = $this->createAdminDaerah();
        $pertanyaan = FormPertanyaan::create([
            'seksi' => 'agama',
            'kode' => 'custom_penyakit',
            'label' => 'Riwayat Kesehatan',
            'tipe' => 'text',
            'is_wajib' => false,
            'is_aktif' => true,
            'is_custom' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.form-kader.update', $pertanyaan), [
            'label' => 'Riwayat Penyakit & Alergi',
            'placeholder' => 'Contoh: Maag akut, asma',
            'bantuan' => 'Penting untuk tim medis panitia',
            'is_wajib' => 1,
            'is_aktif' => 1,
            'seksi' => 'agama',
        ])->assertRedirect();

        $this->assertSame('Riwayat Penyakit & Alergi', $pertanyaan->fresh()->label);
        $this->assertTrue($pertanyaan->fresh()->is_wajib);

        // Delete custom question
        $this->actingAs($admin)->delete(route('admin.form-kader.destroy', $pertanyaan))
            ->assertRedirect();
        $this->assertNull(FormPertanyaan::find($pertanyaan->id));
    }
}
