<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CekStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_cek_status_page(): void
    {
        $this->get(route('cek-status'))
            ->assertOk()
            ->assertSee('Cek Status Pendaftaran');
    }

    public function test_cek_status_returns_active_member_status(): void
    {
        $user = User::factory()->create(['status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Terverifikasi',
            'nik' => '3313010101900010',
            'no_wa' => '081234567890',
        ]);

        $this->post(route('cek-status.check'), ['identifier' => '3313010101900010'])
            ->assertOk()
            ->assertSee('Kader Terverifikasi')
            ->assertSee('berkas pendaftaran Anda telah DISETUJUI');
    }

    public function test_cek_status_by_no_wa_works(): void
    {
        $user = User::factory()->create(['status' => 'menunggu_verifikasi']);
        Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Menunggu',
            'nik' => '3313010101900011',
            'no_wa' => '081299998888',
        ]);

        $this->post(route('cek-status.check'), ['identifier' => '081299998888'])
            ->assertOk()
            ->assertSee('Kader Menunggu')
            ->assertSee('sedang menunggu giliran verifikasi');
    }

    public function test_cek_status_shows_rejection_notes(): void
    {
        $user = User::factory()->create(['status' => 'ditolak']);
        Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Ditolak',
            'nik' => '3313010101900012',
            'catatan_verifikasi' => 'Foto KTP buram, mohon perbaiki.',
        ]);

        $this->post(route('cek-status.check'), ['identifier' => '3313010101900012'])
            ->assertOk()
            ->assertSee('Foto KTP buram, mohon perbaiki.');
    }

    public function test_cek_status_shows_nonaktif_status_and_notes(): void
    {
        $user = User::factory()->create(['status' => 'nonaktif']);
        Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Nonaktif',
            'nik' => '3313010101900013',
            'catatan_verifikasi' => 'Pindah domisili luar provinsi.',
        ]);

        $this->post(route('cek-status.check'), ['identifier' => '3313010101900013'])
            ->assertOk()
            ->assertSee('Kader Nonaktif')
            ->assertSee('NONAKTIF')
            ->assertSee('Pindah domisili luar provinsi.');
    }

    public function test_cek_status_shows_not_found(): void
    {
        $this->post(route('cek-status.check'), ['identifier' => '9999999999999999'])
            ->assertOk()
            ->assertSee('Data Pendaftaran Tidak Ditemukan');
    }

    public function test_cek_status_is_rate_limited(): void
    {
        Cache::flush();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('cek-status.check'), ['identifier' => "identifikator-{$i}123456789"])
                ->assertStatus(200);
        }

        $this->post(route('cek-status.check'), ['identifier' => 'identifikator-6th-12345'])
            ->assertStatus(429);
    }
}
