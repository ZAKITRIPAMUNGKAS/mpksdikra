<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnggotaRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Budi Santoso, S.T.',
            'alamat_rumah' => 'Jl. Contoh No. 1',
            'jenis_kelamin' => 'L',
            'nik' => '1234567890123456',
            'tempat_lahir' => 'Surakarta',
            'tanggal_lahir' => '1990-01-01',
            'status_pernikahan' => 'Nikah',
            'desa_kelurahan' => 'Mangkubumen',
            'kecamatan' => 'Banjarsari',
            'kabupaten' => 'Surakarta',
            'no_wa' => '081234567890',
            'email' => 'budi@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'foto' => UploadedFile::fake()->image('foto.jpg', 400, 600),
        ], $overrides);
    }

    public function test_public_registration_creates_pending_account(): void
    {
        Storage::fake('local');

        $this->post(route('anggota.register.store'), $this->payload())
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $user = User::where('email', 'budi@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('anggota', $user->role);
        $this->assertSame('menunggu_verifikasi', $user->status);
        $this->assertSame('6281234567890', $user->no_wa);

        $anggota = $user->anggota;
        $this->assertNotNull($anggota);
        $this->assertSame('1234567890123456', $anggota->nik);
        Storage::disk('local')->assertExists($anggota->foto);
    }

    public function test_duplicate_nik_rejected(): void
    {
        Storage::fake('local');
        Anggota::factory()->create(['nik' => '1234567890123456']);

        $this->post(route('anggota.register.store'), $this->payload())
            ->assertSessionHasErrors('nik');

        $this->assertSame(1, Anggota::where('nik', '1234567890123456')->count());
    }

    public function test_invalid_nik_length_rejected(): void
    {
        Storage::fake('local');

        $this->post(route('anggota.register.store'), $this->payload(['nik' => '123']))
            ->assertSessionHasErrors('nik');
    }

    public function test_invalid_wa_rejected(): void
    {
        Storage::fake('local');

        $this->post(route('anggota.register.store'), $this->payload(['no_wa' => 'abc123']))
            ->assertSessionHasErrors('no_wa');
    }

    public function test_registration_requires_password_confirmation(): void
    {
        Storage::fake('local');

        $this->post(route('anggota.register.store'), $this->payload(['password_confirmation' => 'berbeda123']))
            ->assertSessionHasErrors('password');
    }

    public function test_wa_normalizer_variants(): void
    {
        $this->assertSame('6281234567890', NomorWaNormalizer::normalize('081234567890'));
        $this->assertSame('6281234567890', NomorWaNormalizer::normalize('+6281234567890'));
        $this->assertSame('6281234567890', NomorWaNormalizer::normalize('6281234567890'));
        $this->assertSame('6281234567890', NomorWaNormalizer::normalize('81234567890'));
        $this->assertNull(NomorWaNormalizer::normalize(null));
    }

    public function test_registration_saves_kemasjidan_and_kemasyarakatan_fields(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'aktifitas_jamaah_masjid' => 'Sholat 5 waktu berjamaah & kajian subuh',
            'keaktifan_takmir' => 'Ya',
            'jabatan_takmir' => 'Ketua Takmir Masjid Al-Ikhlas',
            'keaktifan_kemasyarakatan' => 'Ketua RT 02 / RW 05 dan Anggota BPD',
        ]);

        $this->post(route('anggota.register.store'), $payload)
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $anggota = Anggota::where('nik', '1234567890123456')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('Sholat 5 waktu berjamaah & kajian subuh', $anggota->aktifitas_jamaah_masjid);
        $this->assertSame('Ya', $anggota->keaktifan_takmir);
        $this->assertSame('Ketua Takmir Masjid Al-Ikhlas', $anggota->jabatan_takmir);
        $this->assertSame('Ketua RT 02 / RW 05 dan Anggota BPD', $anggota->keaktifan_kemasyarakatan);
    }

    public function test_wa_with_spaces_and_hyphens_is_sanitized_and_accepted(): void
    {
        Storage::fake('local');

        $this->post(route('anggota.register.store'), $this->payload([
            'no_wa' => '0812-3456-7890',
        ]))
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $user = User::where('email', 'budi@example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('6281234567890', $user->no_wa);
    }

    public function test_duplicate_email_rejected(): void
    {
        Storage::fake('local');
        User::factory()->create(['email' => 'budi@example.test']);

        $this->post(route('anggota.register.store'), $this->payload())
            ->assertSessionHasErrors('email');
    }

    public function test_registration_redirect_renders_waiting_verification_status_card(): void
    {
        Storage::fake('local');

        $response = $this->post(route('anggota.register.store'), $this->payload([
            'nama_lengkap' => 'Kader Ahmad Dahlan, S.Kom.',
            'nik' => '3313010101990001',
            'no_wa' => '081234567899',
            'kecamatan' => 'Karanganyar',
        ]));

        $response->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success')
            ->assertSessionHas('registered_data');

        $follow = $this->followRedirects($response);
        $follow->assertOk()
            ->assertSee('Alhamdulillah, Anda Telah Berhasil Terdaftar!')
            ->assertSee('Menunggu Verifikasi Data')
            ->assertSee('Kader Ahmad Dahlan, S.Kom.')
            ->assertSee('Periksa Status Pendaftaran');
    }

    public function test_pendaftaran_url_alias_redirects_to_daftar(): void
    {
        $response = $this->get('/pendaftaran');
        $response->assertRedirect(route('anggota.register'));
    }

    public function test_registration_saves_pcm_prm_pekerjaan_and_jabatan(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'kecamatan' => 'Karanganyar',
            'pcm' => 'Karanganyar',
            'prm' => 'PRM Lalung',
            'pekerjaan' => 'Guru SMA Muhammadiyah 1',
            'keaktifan_muhammadiyah' => ['Pimpinan Daerah Muhammadiyah', 'Amal Usaha Muhammadiyah'],
            'jabatan_muhammadiyah' => [
                'Pimpinan Daerah Muhammadiyah' => 'Anggota Majelis Dikdasmen',
                'Amal Usaha Muhammadiyah' => 'Wakil Kepala Sekolah',
            ],
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
            'jabatan_ortom' => [
                'Pemuda Muhammadiyah' => 'Ketua Bidang Perkaderan',
            ],
        ]);

        $response = $this->post(route('anggota.register.store'), $payload);
        $response->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $anggota = Anggota::where('nik', '1234567890123456')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('Karanganyar', $anggota->kecamatan);
        $this->assertSame('Karanganyar', $anggota->pcm);
        $this->assertSame('PRM Lalung', $anggota->prm);
        $this->assertSame('Guru SMA Muhammadiyah 1', $anggota->pekerjaan);
        $this->assertSame('Wakil Kepala Sekolah', $anggota->jabatan_aum); // mirrored
        $this->assertEquals(['Pimpinan Daerah Muhammadiyah', 'Amal Usaha Muhammadiyah'], $anggota->keaktifan_muhammadiyah);
        $this->assertSame('Anggota Majelis Dikdasmen', $anggota->jabatan_muhammadiyah['Pimpinan Daerah Muhammadiyah']);
        $this->assertSame('Ketua Bidang Perkaderan', $anggota->jabatan_ortom['Pemuda Muhammadiyah']);
    }

    public function test_registration_auto_generates_password_from_birthdate_when_empty(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'nik' => '3313010101850001',
            'no_wa' => '081226001099',
            'email' => 'kader85@example.test',
            'tanggal_lahir' => '1985-08-17',
        ]);
        unset($payload['password'], $payload['password_confirmation']);

        $this->post(route('anggota.register.store'), $payload)
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $user = User::where('email', 'kader85@example.test')->first();
        $this->assertNotNull($user);
        // Tanggal lahir 1985-08-17 -> Password 17081985
        $this->assertTrue(Hash::check('17081985', $user->password));
    }

    public function test_registration_saves_complete_education_background(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'nik' => '3313020202950002',
            'no_wa' => '081299887766',
            'email' => 'sarjana@example.test',
            'pendidikan_terakhir' => 'S1',
            'pendidikan_sd' => 'MIM Karanganyar',
            'pendidikan_smp' => 'MTsN Karanganyar',
            'pendidikan_sma' => 'SMA Muhammadiyah 1 Karanganyar',
            'pendidikan_s1' => 'UMS - Pendidikan Agama Islam',
        ]);

        $this->post(route('anggota.register.store'), $payload)
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $anggota = Anggota::where('nik', '3313020202950002')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('S1', $anggota->pendidikan_terakhir);
        $this->assertSame('MIM Karanganyar', $anggota->pendidikan_sd);
        $this->assertSame('MTsN Karanganyar', $anggota->pendidikan_smp);
        $this->assertSame('SMA Muhammadiyah 1 Karanganyar', $anggota->pendidikan_sma);
        $this->assertSame('UMS - Pendidikan Agama Islam', $anggota->pendidikan_s1);
    }
}
