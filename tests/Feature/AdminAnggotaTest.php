<?php

namespace Tests\Feature;

use App\Exports\AnggotaExport;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnggotaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function pendingAnggota(): Anggota
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'menunggu_verifikasi']);

        return Anggota::factory()->create(['user_id' => $user->id]);
    }

    public function test_admin_can_see_anggota_list(): void
    {
        $admin = $this->admin();
        $this->pendingAnggota();

        $this->actingAs($admin)
            ->get(route('admin.anggota.index'))
            ->assertOk()
            ->assertSee('menunggu_verifikasi');
    }

    public function test_non_admin_cannot_access_anggota_list(): void
    {
        $anggota = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($anggota)
            ->get(route('admin.anggota.index'))
            ->assertForbidden();
    }

    public function test_admin_can_verify_anggota(): void
    {
        $admin = $this->admin();
        $a = $this->pendingAnggota();

        $this->actingAs($admin)
            ->post(route('admin.anggota.verify', $a))
            ->assertRedirect(route('admin.anggota.show', $a));

        $this->assertSame('aktif', $a->user->fresh()->status);
        $this->assertNotNull($a->user->fresh()->verified_at);
    }

    public function test_verify_flashes_wa_notification_url(): void
    {
        $admin = $this->admin();
        $a = $this->pendingAnggota();
        $a->update(['no_wa' => '081234567890']);

        $this->actingAs($admin)
            ->post(route('admin.anggota.verify', $a))
            ->assertRedirect(route('admin.anggota.show', $a))
            ->assertSessionHas('wa_url');

        $this->assertStringContainsString('api.whatsapp.com/send', session('wa_url'));
    }

    public function test_reject_flashes_wa_notification_url(): void
    {
        $admin = $this->admin();
        $a = $this->pendingAnggota();
        $a->update(['no_wa' => '081234567890']);

        $this->actingAs($admin)
            ->post(route('admin.anggota.reject', $a), ['catatan_verifikasi' => 'Berkas kurang lengkap.'])
            ->assertRedirect(route('admin.anggota.show', $a))
            ->assertSessionHas('wa_url')
            ->assertSessionHas('success');

        $this->assertStringContainsString('api.whatsapp.com/send', session('wa_url'));
    }

    public function test_admin_can_deactivate_anggota(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->post(route('admin.anggota.deactivate', $a))
            ->assertRedirect(route('admin.anggota.show', $a));

        $this->assertSame('nonaktif', $user->fresh()->status);
    }

    public function test_admin_can_view_anggota_detail_with_pcm_prm_and_organizations(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'pcm' => 'Karanganyar',
            'prm' => 'PRM Lalung',
            'pekerjaan' => 'Guru SMA Muhammadiyah 1',
            'keaktifan_muhammadiyah' => ['Pimpinan Daerah Muhammadiyah'],
            'jabatan_muhammadiyah' => ['Pimpinan Daerah Muhammadiyah' => 'Ketua Majelis'],
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
            'jabatan_ortom' => ['Pemuda Muhammadiyah' => 'Sekretaris'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.anggota.show', $a))
            ->assertOk()
            ->assertSee('PCM Karanganyar')
            ->assertSee('PRM Lalung')
            ->assertSee('Guru SMA Muhammadiyah 1')
            ->assertSee('Ketua Majelis')
            ->assertSee('Sekretaris');
    }

    public function test_admin_export_excel_renders_all_form_fields_completely(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif', 'email' => 'kader@test.com']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Ahmad Dahlan Putra',
            'nik' => '3313020101900001',
            'nbm' => '1234567',
            'no_wa' => '081234567890',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Karanganyar',
            'tanggal_lahir' => '1990-01-01',
            'status_pernikahan' => 'Nikah',
            'alamat_rumah' => 'Jl. Lawu No. 10',
            'desa_kelurahan' => 'Lalung',
            'kecamatan' => 'Karanganyar',
            'pcm' => 'Karanganyar',
            'prm' => 'Lalung',
            'kabupaten' => 'Karanganyar',
            'pekerjaan' => 'PNS',
            'jabatan_aum' => 'Guru SD',
            'pendidikan_terakhir' => 'S1',
            'pendidikan_sd' => 'SD Muhammadiyah 1',
            'pendidikan_smp' => 'SMP Muhammadiyah 1',
            'pendidikan_sma' => 'SMA Muhammadiyah 1',
            'pendidikan_s1' => 'UMS - FAI',
            'bahasa_dikuasai' => ['Bahasa Indonesia', 'Bahasa Arab'],
            'kemampuan_baca_quran' => 'Lancar dan fasih',
            'hafalan_quran' => '1 juz',
            'aktivitas_sholat_masjid' => 'Selalu sholat 5 waktu di masjid',
            'aktivitas_kajian_agama' => 'Seminggu lebih dari 3x',
            'jumlah_buku_agama' => 'Lebih dari 20 judul buku',
            'sumber_info_muhammadiyah' => ['Kajian yang diikuti', 'Buku yang dibaca'],
            'langganan_suara_muhammadiyah' => 'Rutin beli',
            'lembaga_zis' => ['LAZISMU'],
            'tokoh_berpengaruh' => 'K.H. Ahmad Dahlan',
            'alasan_pilih_tokoh' => 'Inspirasi dakwah pencerahan',
            'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah'],
            'jabatan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah' => 'Ketua'],
            'keaktifan_ortom' => ['Pemuda Muhammadiyah'],
            'jabatan_ortom' => ['Pemuda Muhammadiyah' => 'Ketua'],
            'organisasi_lain' => 'Ketua Karang Taruna',
            'aktifitas_jamaah_masjid' => 'Imam rawatib',
            'keaktifan_takmir' => 'Ya',
            'jabatan_takmir' => 'Ketua Takmir',
            'keaktifan_kemasyarakatan' => 'Ketua RT 02',
            'harapan_pcm' => 'PCM lebih masif mengadakan pengkaderan',
            'harapan_ba' => 'Baitul Arqam terselenggara rutin',
            'catatan_verifikasi' => 'Data lengkap terverifikasi',
        ]);

        $export = new AnggotaExport(collect([$anggota]));
        $html = $export->view()->render();

        // Verifikasi Header Grup Kategori
        $this->assertStringContainsString('ADMINISTRASI SISTEM', $html);
        $this->assertStringContainsString('IDENTITAS &amp; KONTAK PRIBADI', $html);
        $this->assertStringContainsString('DOMISILI &amp; WILAYAH', $html);
        $this->assertStringContainsString('PROFESI &amp; PENDIDIKAN', $html);
        $this->assertStringContainsString('PROFIL KEAGAMAAN &amp; LITERASI', $html);
        $this->assertStringContainsString('KEMUHAMMADIYAHAN &amp; ORTOM', $html);
        $this->assertStringContainsString('KEMASJIDAN, SOSIAL &amp; HARAPAN', $html);
        $this->assertStringContainsString('CATATAN', $html);

        // Verifikasi Kolom dan Nilai Lengkap Formulir
        $this->assertStringContainsString('Ahmad Dahlan Putra', $html);
        $this->assertStringContainsString("'3313020101900001", $html);
        $this->assertStringContainsString("'1234567", $html);
        $this->assertStringContainsString("'081234567890", $html);
        $this->assertStringContainsString('kader@test.com', $html);
        $this->assertStringContainsString('S1 / D4 (Sarjana)', $html);
        $this->assertStringContainsString('SD Muhammadiyah 1', $html);
        $this->assertStringContainsString('SMP Muhammadiyah 1', $html);
        $this->assertStringContainsString('SMA Muhammadiyah 1', $html);
        $this->assertStringContainsString('UMS - FAI', $html);
        $this->assertStringContainsString('Bahasa Indonesia, Bahasa Arab', $html);
        $this->assertStringContainsString('Lancar dan fasih', $html);
        $this->assertStringContainsString('1 juz', $html);
        $this->assertStringContainsString('Selalu sholat 5 waktu di masjid', $html);
        $this->assertStringContainsString('Seminggu lebih dari 3x', $html);
        $this->assertStringContainsString('Lebih dari 20 judul buku', $html);
        $this->assertStringContainsString('Kajian yang diikuti, Buku yang dibaca', $html);
        $this->assertStringContainsString('Rutin beli', $html);
        $this->assertStringContainsString('LAZISMU', $html);
        $this->assertStringContainsString('K.H. Ahmad Dahlan', $html);
        $this->assertStringContainsString('Inspirasi dakwah pencerahan', $html);
        $this->assertStringContainsString('Pimpinan Cabang Muhammadiyah (Ketua)', $html);
        $this->assertStringContainsString('Pemuda Muhammadiyah (Ketua)', $html);
        $this->assertStringContainsString('Ketua Karang Taruna', $html);
        $this->assertStringContainsString('Imam rawatib', $html);
        $this->assertStringContainsString('Ketua Takmir', $html);
        $this->assertStringContainsString('Ketua RT 02', $html);
        $this->assertStringContainsString('PCM lebih masif mengadakan pengkaderan', $html);
        $this->assertStringContainsString('Baitul Arqam terselenggara rutin', $html);
        $this->assertStringContainsString('Data lengkap terverifikasi', $html);
    }
}
