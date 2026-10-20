<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Database\Seeders\FormPertanyaanSeeder;
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
            'nama_aum' => 'SMA Muhammadiyah 1 Karanganyar',
            'jabatan_aum' => 'Wakil Kepala Sekolah / Madrasah',
            'keaktifan_muhammadiyah' => ['Pimpinan Daerah Muhammadiyah'],
            'jabatan_muhammadiyah' => [
                'Pimpinan Daerah Muhammadiyah' => 'Anggota Majelis Dikdasmen',
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
        $this->assertSame('SMA Muhammadiyah 1 Karanganyar', $anggota->nama_aum);
        $this->assertSame('Wakil Kepala Sekolah / Madrasah', $anggota->jabatan_aum);
        $this->assertEquals(['Pimpinan Daerah Muhammadiyah'], $anggota->keaktifan_muhammadiyah);
        $this->assertSame('Anggota Majelis Dikdasmen', $anggota->jabatan_muhammadiyah['Pimpinan Daerah Muhammadiyah']);
        $this->assertSame('Ketua Bidang Perkaderan', $anggota->jabatan_ortom['Pemuda Muhammadiyah']);
    }

    public function test_registration_supports_custom_aum_and_jabatan_when_lainnya_selected(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'nik' => '3313990011223344',
            'no_wa' => '081299001122',
            'nama_aum' => 'Lainnya',
            'nama_aum_custom' => 'SD Muhammadiyah Unggulan Karanganyar',
            'jabatan_aum' => 'Lainnya',
            'jabatan_aum_custom' => 'Kepala Perpustakaan & Arsip',
            'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah'],
        ]);

        $response = $this->post(route('anggota.register.store'), $payload);
        $response->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $anggota = Anggota::where('nik', '3313990011223344')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('SD Muhammadiyah Unggulan Karanganyar', $anggota->nama_aum);
        $this->assertSame('Kepala Perpustakaan & Arsip', $anggota->jabatan_aum);
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

    public function test_registration_saves_parent_names_and_custom_fields(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'nik' => '3313030303960003',
            'no_wa' => '081388776655',
            'email' => 'ortu@example.test',
            'nama_ayah' => 'H. Ahmad Dahlan',
            'nama_ibu' => 'Hj. Siti Walidah',
            'custom_fields' => [
                'ukuran_kaos' => 'XL',
                'riwayat_penyakit' => 'Tidak Ada',
            ],
        ]);

        $this->post(route('anggota.register.store'), $payload)
            ->assertRedirect(route('anggota.register'))
            ->assertSessionHas('success');

        $anggota = Anggota::where('nik', '3313030303960003')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('H. Ahmad Dahlan', $anggota->nama_ayah);
        $this->assertSame('Hj. Siti Walidah', $anggota->nama_ibu);
        $this->assertEquals(['ukuran_kaos' => 'XL', 'riwayat_penyakit' => 'Tidak Ada'], $anggota->custom_fields);
    }

    public function test_registration_with_seeded_form_and_standard_dropdowns_passes_without_validation_in_error(): void
    {
        Storage::fake('local');
        $this->seed(FormPertanyaanSeeder::class);

        $payload = $this->payload([
            'nik' => '3313040404980004',
            'no_wa' => '081255443322',
            'email' => 'kader.lengkap@example.test',
            'pendidikan_terakhir' => 'S1',
            'pcm' => 'Colomadu',
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
            'keaktifan_muhammadiyah' => ['Pimpinan Ranting Muhammadiyah'],
            'kemampuan_baca_quran' => 'Lancar dan fasih',
            'status_pernikahan' => 'Nikah',
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasNoErrors();
        $res->assertRedirect(route('anggota.register'));

        // Also test with PCM = 'Lainnya'
        $payloadLainnya = $this->payload([
            'nik' => '3313040404980005',
            'no_wa' => '081255443323',
            'email' => 'kader.luar@example.test',
            'pendidikan_terakhir' => 'SMA',
            'pcm' => 'Lainnya',
        ]);

        $res2 = $this->post(route('anggota.register.store'), $payloadLainnya);
        $res2->assertSessionHasNoErrors();
    }

    public function test_invalid_option_shows_human_friendly_error_not_validation_in(): void
    {
        Storage::fake('local');
        $this->seed(FormPertanyaanSeeder::class);

        $payload = $this->payload([
            'nik' => '3313040404980006',
            'no_wa' => '081255443324',
            'status_pernikahan' => 'StatusPalsu123',
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors(['status_pernikahan']);

        $errors = session('errors')->all();
        $this->assertNotContains('validation.in', $errors);
        $this->assertContains('Pilihan status pernikahan tidak valid.', $errors);
    }

    public function test_registration_supports_desa_kaling_in_tasikmadu(): void
    {
        Storage::fake('local');

        $tasikmaduDesas = config('arqam.opsi_form.wilayah_karanganyar.Tasikmadu', []);
        $this->assertContains('Kaling', $tasikmaduDesas);

        $payload = $this->payload([
            'nik' => '3313101234560001',
            'no_wa' => '081234567888',
            'email' => 'kader.kaling@example.test',
            'kecamatan' => 'Tasikmadu',
            'desa_kelurahan' => 'Kaling',
            'pcm' => 'Tasikmadu',
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasNoErrors();
        $res->assertRedirect(route('anggota.register'));

        $anggota = Anggota::where('nik', '3313101234560001')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('Tasikmadu', $anggota->kecamatan);
        $this->assertSame('Kaling', $anggota->desa_kelurahan);
    }

    private function createSyntheticPng(int $width, int $height, float $transparencyRatio): UploadedFile
    {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
        $opaque = imagecolorallocatealpha($img, 20, 50, 80, 0);

        $transPixels = (int) round($width * $height * $transparencyRatio);
        $count = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                imagesetpixel($img, $x, $y, ($count < $transPixels) ? $trans : $opaque);
                $count++;
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'cutout_test_').'.png';
        imagepng($img, $tmp);
        imagedestroy($img);

        return new UploadedFile($tmp, 'cutout.png', 'image/png', null, true);
    }

    public function test_registration_with_valid_cutout_png_is_persisted(): void
    {
        Storage::fake('local');

        $sourcePhoto = UploadedFile::fake()->image('foto.jpg', 400, 600);
        $cutoutPhoto = $this->createSyntheticPng(400, 600, 0.35);

        $payload = $this->payload([
            'nik' => '3313109999990001',
            'no_wa' => '081299990001',
            'email' => 'cutout.valid@example.test',
            'foto' => $sourcePhoto,
            'foto_cutout' => $cutoutPhoto,
            'foto_mode' => 'cutout',
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasNoErrors();
        $res->assertRedirect(route('anggota.register'));

        $anggota = Anggota::where('nik', '3313109999990001')->first();
        $this->assertNotNull($anggota);
        $this->assertSame('cutout', $anggota->foto_mode);
        $this->assertNotNull($anggota->foto_cutout_path);

        Storage::disk('local')->assertExists($anggota->foto);
        Storage::disk('local')->assertExists($anggota->foto_cutout_path);
    }

    public function test_registration_rejects_fake_png_with_invalid_magic_bytes(): void
    {
        Storage::fake('local');

        $tmp = tempnam(sys_get_temp_dir(), 'fake_png_');
        file_put_contents($tmp, 'NOT_A_REAL_PNG_HEADER_TEST');
        $fakeCutout = new UploadedFile($tmp, 'fake.png', 'image/png', null, true);

        $payload = $this->payload([
            'nik' => '3313109999990002',
            'foto_cutout' => $fakeCutout,
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors(['foto_cutout']);
    }

    public function test_registration_rejects_cutout_with_too_little_transparency(): void
    {
        Storage::fake('local');

        $sourcePhoto = UploadedFile::fake()->image('foto.jpg', 400, 600);
        $solidCutout = $this->createSyntheticPng(400, 600, 0.02); // 2% transparent (< 5%)

        $payload = $this->payload([
            'nik' => '3313109999990003',
            'foto' => $sourcePhoto,
            'foto_cutout' => $solidCutout,
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors(['foto_cutout']);
    }

    public function test_registration_rejects_cutout_with_too_much_transparency(): void
    {
        Storage::fake('local');

        $sourcePhoto = UploadedFile::fake()->image('foto.jpg', 400, 600);
        $emptyCutout = $this->createSyntheticPng(400, 600, 0.95); // 95% transparent (> 90%)

        $payload = $this->payload([
            'nik' => '3313109999990004',
            'foto' => $sourcePhoto,
            'foto_cutout' => $emptyCutout,
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors(['foto_cutout']);
    }

    public function test_registration_rejects_cutout_with_mismatched_aspect_ratio(): void
    {
        Storage::fake('local');

        $sourcePhoto = UploadedFile::fake()->image('foto.jpg', 400, 600); // 2:3 aspect
        $mismatchedCutout = $this->createSyntheticPng(600, 400, 0.35); // 3:2 landscape aspect

        $payload = $this->payload([
            'nik' => '3313109999990005',
            'foto' => $sourcePhoto,
            'foto_cutout' => $mismatchedCutout,
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertSessionHasErrors(['foto_cutout']);
    }

    public function test_registration_supports_structured_ortom_with_3_dropdowns_and_custom_jabatan(): void
    {
        Storage::fake('local');

        $payload = $this->payload([
            'nik' => '3313109999990099',
            'keaktifan_ortom' => ['Pemuda Muhammadiyah', 'Tapak Suci'],
            'jabatan_ortom' => [
                'Pemuda Muhammadiyah' => [
                    'tingkat' => 'Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW)',
                    'periode' => '2022 - 2027',
                    'jabatan' => 'Ketua Umum / Ketua',
                ],
                'Tapak Suci' => [
                    'tingkat' => 'Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW)',
                    'periode' => '2018 - 2022',
                    'jabatan' => 'Lainnya',
                    'jabatan_custom' => 'Pelatih Kepala / Pendekar Muda',
                ],
            ],
        ]);

        $res = $this->post(route('anggota.register.store'), $payload);
        $res->assertRedirect(route('anggota.register'));
        $res->assertSessionHas('success');

        $anggota = Anggota::where('nik', '3313109999990099')->first();
        $this->assertNotNull($anggota);
        $this->assertIsArray($anggota->jabatan_ortom);
        $this->assertSame('Ketua Umum / Ketua', $anggota->jabatan_ortom['Pemuda Muhammadiyah']['jabatan']);
        $this->assertSame('Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW)', $anggota->jabatan_ortom['Pemuda Muhammadiyah']['tingkat']);
        $this->assertSame('2022 - 2027', $anggota->jabatan_ortom['Pemuda Muhammadiyah']['periode']);

        $this->assertSame('Pelatih Kepala / Pendekar Muda', $anggota->jabatan_ortom['Tapak Suci']['jabatan']);
        $this->assertSame('Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW)', $anggota->jabatan_ortom['Tapak Suci']['tingkat']);
        $this->assertSame('2018 - 2022', $anggota->jabatan_ortom['Tapak Suci']['periode']);

        $this->assertSame(
            'Ketua Umum / Ketua - Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW) - Periode 2022 - 2027',
            $anggota->formatJabatanOrtom('Pemuda Muhammadiyah')
        );
        $this->assertSame(
            'Pelatih Kepala / Pendekar Muda - Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW) - Periode 2018 - 2022',
            $anggota->formatJabatanOrtom('Tapak Suci')
        );
    }
}
