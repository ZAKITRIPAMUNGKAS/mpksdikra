<?php

namespace Tests\Feature;

use App\Models\FormPertanyaan;
use App\Models\User;
use Database\Seeders\FormPertanyaanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
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

    public function test_default_questions_are_not_duplicated_as_custom_answers(): void
    {
        $this->seed(FormPertanyaanSeeder::class);

        $this->get(route('anggota.register'))
            ->assertOk()
            ->assertDontSee('name="custom_fields[nama_lengkap]"', false)
            ->assertSee('name="nama_lengkap"', false);

        $rules = FormPertanyaan::activeValidationRules();

        $this->assertArrayNotHasKey('custom_fields.nama_lengkap', $rules);
    }

    public function test_admin_required_setting_generates_required_validation_rule(): void
    {
        $question = FormPertanyaan::create([
            'kode' => 'custom_contoh', 'label' => 'Contoh', 'seksi' => 'tambahan',
            'tipe' => 'text', 'is_custom' => true, 'is_aktif' => true, 'is_wajib' => true,
        ]);

        $rules = FormPertanyaan::activeValidationRules();

        $validator = Validator::make([], $rules);
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('custom_fields.custom_contoh'));
        $question->update(['is_aktif' => false]);
        $this->assertTrue(Validator::make([], FormPertanyaan::activeValidationRules())->passes());
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

    public function test_standard_question_wajib_setting_reflects_in_registration_view_and_request(): void
    {
        $this->seed(FormPertanyaanSeeder::class);

        // Initially nama_ayah is optional
        $response = $this->get(route('anggota.register'));
        $response->assertOk();
        $response->assertSee('Nama Ayah Kandung');
        $response->assertSee('(Opsional)');

        // Now set nama_ayah to wajib
        $q = FormPertanyaan::where('kode', 'nama_ayah')->firstOrFail();
        $q->update(['is_wajib' => true]);

        $responseWajib = $this->get(route('anggota.register'));
        $responseWajib->assertOk();
        $responseWajib->assertSee('Nama Ayah Kandung');
        $responseWajib->assertSee('title="Wajib diisi"', false);

        // Submitting without nama_ayah should fail validation
        $payload = [
            'nama_lengkap' => 'Ahmad Dahlan Uji',
            'alamat_rumah' => 'Jl. Lawu No. 12',
            'jenis_kelamin' => 'L',
            'nik' => '3313998877660001',
            'tempat_lahir' => 'Karanganyar',
            'tanggal_lahir' => '1990-01-01',
            'status_pernikahan' => 'Nikah',
            'desa_kelurahan' => 'Dagen',
            'kecamatan' => 'Jaten',
            'kabupaten' => 'Karanganyar',
            'no_wa' => '081234567899',
            'foto' => UploadedFile::fake()->image('foto.jpg', 600, 800),
        ];

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors('nama_ayah');

        // Submitting with nama_ayah should pass
        $payload['nama_ayah'] = 'H. Syukri';
        $resPass = $this->post(route('anggota.register.store'), $payload);
        $resPass->assertSessionHasNoErrors();
    }

    public function test_standard_question_hidden_setting_hides_field_from_registration_view(): void
    {
        $this->seed(FormPertanyaanSeeder::class);

        // Initially nbm is active
        $this->get(route('anggota.register'))->assertSee('name="nbm"', false);

        // Hide nbm
        $q = FormPertanyaan::where('kode', 'nbm')->firstOrFail();
        $q->update(['is_aktif' => false]);

        $this->get(route('anggota.register'))->assertDontSee('name="nbm"', false);
    }

    public function test_admin_updated_options_sync_to_registration_page_and_validation(): void
    {
        $this->seed(FormPertanyaanSeeder::class);
        $admin = $this->createAdminDaerah();

        $q = FormPertanyaan::where('kode', 'keaktifan_ortom')->firstOrFail();

        // Admin updates options via form-kader update route
        $newOptions = "Pemuda Muhammadiyah\nNasyiatul Aisyiyah\nIkatan Mahasiswa Muhammadiyah (IMM)\nTidak aktif";
        $this->actingAs($admin)->put(route('admin.form-kader.update', $q), [
            'label' => 'Keaktifan di Organisasi Otonom (ORTOM)',
            'opsi_raw' => $newOptions,
            'is_wajib' => 0,
            'is_aktif' => 1,
            'seksi' => 'organisasi',
        ])->assertRedirect();

        auth()->logout();
        session()->flush();

        // Verify registration view displays the new option
        $response = $this->get(route('anggota.register'));
        $response->assertOk();
        $response->assertSee('Ikatan Mahasiswa Muhammadiyah (IMM)');

        // Submit form with the newly added option
        $payload = [
            'nama_lengkap' => 'Kader Ortom Baru',
            'alamat_rumah' => 'Jl. Lawu No. 99',
            'jenis_kelamin' => 'L',
            'nik' => '3313998877660002',
            'tempat_lahir' => 'Karanganyar',
            'tanggal_lahir' => '1995-03-20',
            'status_pernikahan' => 'Belum Nikah',
            'desa_kelurahan' => 'Dagen',
            'kecamatan' => 'Jaten',
            'kabupaten' => 'Karanganyar',
            'no_wa' => '081234567888',
            'foto' => UploadedFile::fake()->image('foto.jpg', 600, 800),
            'keaktifan_ortom' => ['Ikatan Mahasiswa Muhammadiyah (IMM)'],
        ];

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasNoErrors();

        $this->assertDatabaseHas('anggota', [
            'nik' => '3313998877660002',
        ]);
    }
}
