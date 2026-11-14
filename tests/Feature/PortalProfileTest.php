<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
                'nama_aum' => 'SMA Muhammadiyah 1 Karanganyar',
                'jabatan_aum' => 'Kepala Sekolah',
                'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah'],
                'jabatan_muhammadiyah' => [
                    'Pimpinan Cabang Muhammadiyah' => 'Wakil Ketua PCM',
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

    public function test_anggota_can_update_foto_cutout_and_mode(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id]);

        $cutout = $this->createSyntheticPng(400, 600, 0.35);

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
                'foto_cutout' => $cutout,
                'foto_mode' => 'original',
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertSame('original', $fresh->foto_mode);
        $this->assertNotNull($fresh->foto_cutout_path);
        Storage::disk('local')->assertExists($fresh->foto_cutout_path);
    }

    public function test_updating_source_photo_without_new_cutout_removes_old_cutout(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $oldCutoutPath = 'anggota/cutouts/old_cutout.png';
        Storage::disk('local')->put($oldCutoutPath, 'fake_png_data');

        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'foto_cutout_path' => $oldCutoutPath,
        ]);

        $newSourcePhoto = UploadedFile::fake()->image('new_source.jpg', 400, 600);

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
                'foto' => $newSourcePhoto,
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertNull($fresh->foto_cutout_path);
        Storage::disk('local')->assertMissing($oldCutoutPath);
        Storage::disk('local')->assertExists($fresh->foto);
    }

    public function test_portal_profile_updates_structured_ortom_with_dropdowns(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
            'jabatan_ortom' => ['Pemuda Muhammadiyah' => 'Ketua Ranting Lama'],
        ]);

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
                'keaktifan_ortom' => ['Pemuda Muhammadiyah', 'Hizbul Wathon'],
                'jabatan_ortom' => [
                    'Pemuda Muhammadiyah' => [
                        'tingkat' => 'Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW)',
                        'periode' => '2022 - 2027',
                        'jabatan' => 'Sekretaris Umum / Sekretaris',
                    ],
                    'Hizbul Wathon' => [
                        'tingkat' => 'Pimpinan Ranting (PR / DPR / Kolat TS / Qobilah HW)',
                        'periode' => '2018 - 2022',
                        'jabatan' => 'Lainnya',
                        'jabatan_custom' => 'Pemimpin Qabilah',
                    ],
                ],
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertIsArray($fresh->jabatan_ortom);
        $this->assertSame('Sekretaris Umum / Sekretaris', $fresh->jabatan_ortom['Pemuda Muhammadiyah']['jabatan']);
        $this->assertSame('Pemimpin Qabilah', $fresh->jabatan_ortom['Hizbul Wathon']['jabatan']);
        $this->assertSame(
            'Sekretaris Umum / Sekretaris - Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW) - Periode 2022 - 2027',
            $fresh->formatJabatanOrtom('Pemuda Muhammadiyah')
        );
        $this->assertSame(
            'Pemimpin Qabilah - Pimpinan Ranting (PR / DPR / Kolat TS / Qobilah HW) - Periode 2018 - 2022',
            $fresh->formatJabatanOrtom('Hizbul Wathon')
        );
    }

    public function test_portal_profile_updates_ortom_with_tahun_mulai_and_tahun_selesai(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
        ]);

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
                'keaktifan_ortom' => ['Pemuda Muhammadiyah', 'Nasyiatul Aisyiyah'],
                'jabatan_ortom' => [
                    'Pemuda Muhammadiyah' => [
                        'tingkat' => 'Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW)',
                        'tahun_mulai' => '2023',
                        'tahun_selesai' => '2028',
                        'jabatan' => 'Ketua Umum / Ketua',
                    ],
                    'Nasyiatul Aisyiyah' => [
                        'tingkat' => 'Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW)',
                        'tahun_mulai' => '2021',
                        'tahun_selesai' => 'Sekarang',
                        'jabatan' => 'Lainnya',
                        'jabatan_custom' => 'Ketua Bidang Kaderisasi',
                    ],
                ],
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertIsArray($fresh->jabatan_ortom);

        $pm = $fresh->jabatan_ortom['Pemuda Muhammadiyah'];
        $this->assertSame('2023', $pm['tahun_mulai']);
        $this->assertSame('2028', $pm['tahun_selesai']);
        $this->assertSame('2023 - 2028', $pm['periode']);
        $this->assertSame('Ketua Umum / Ketua', $pm['jabatan']);

        $na = $fresh->jabatan_ortom['Nasyiatul Aisyiyah'];
        $this->assertSame('2021', $na['tahun_mulai']);
        $this->assertSame('Sekarang', $na['tahun_selesai']);
        $this->assertSame('2021 - Sekarang', $na['periode']);
        $this->assertSame('Ketua Bidang Kaderisasi', $na['jabatan']);

        $this->assertSame(
            'Ketua Umum / Ketua - Pimpinan Daerah (PD / DPD / Pimda TS / Kwarda HW) - Periode 2023 - 2028',
            $fresh->formatJabatanOrtom('Pemuda Muhammadiyah')
        );
        $this->assertSame(
            'Ketua Bidang Kaderisasi - Pimpinan Cabang (PC / DPC / Pimcab TS / Kwarcab HW) - Periode 2021 - Sekarang',
            $fresh->formatJabatanOrtom('Nasyiatul Aisyiyah')
        );
    }

    public function test_portal_profile_supports_imm_and_renders_imm_tingkat_options(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'keaktifan_ortom' => ['Ikatan Mahasiswa Muhammadiyah'],
            'jabatan_ortom' => [
                'Ikatan Mahasiswa Muhammadiyah' => [
                    'tingkat' => 'Koordinator Komisariat (Korkom IMM)',
                    'tahun_mulai' => '2022',
                    'tahun_selesai' => '2023',
                    'jabatan' => 'Ketua Umum / Ketua',
                ],
            ],
        ]);

        $res = $this->actingAs($user)->get(route('portal.profile.index'));
        $res->assertOk();
        $res->assertSee('Ikatan Mahasiswa Muhammadiyah');
        $res->assertSee('Dewan Pimpinan Pusat (DPP IMM)');
        $res->assertSee('Dewan Pimpinan Daerah (DPD IMM)');
        $res->assertSee('Pimpinan Cabang (PC IMM)');
        $res->assertSee('Koordinator Komisariat (Korkom IMM)');
        $res->assertSee('Pimpinan Komisariat (PK IMM)');
    }

    public function test_portal_profile_renders_and_accepts_tahun_selesai_up_to_2040(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
        ]);

        $viewRes = $this->actingAs($user)->get(route('portal.profile.index'));
        $viewRes->assertOk();
        $viewRes->assertSee('<option value="2040"', false);

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
                'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
                'jabatan_ortom' => [
                    'Pemuda Muhammadiyah' => [
                        'tingkat' => 'Pimpinan Daerah (PD Pemuda Muhammadiyah)',
                        'tahun_mulai' => '2024',
                        'tahun_selesai' => '2040',
                        'jabatan' => 'Ketua Umum / Ketua',
                    ],
                ],
            ])
            ->assertRedirect();

        $fresh = $a->fresh();
        $this->assertIsArray($fresh->jabatan_ortom);
        $this->assertSame('2040', $fresh->jabatan_ortom['Pemuda Muhammadiyah']['tahun_selesai']);
        $this->assertSame('2024 - 2040', $fresh->jabatan_ortom['Pemuda Muhammadiyah']['periode']);
    }
}
