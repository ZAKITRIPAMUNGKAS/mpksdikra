<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}

