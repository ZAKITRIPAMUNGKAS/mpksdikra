<?php

namespace Tests\Feature;

use App\Exports\AnggotaExport;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAnggotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true, 'id' => 'msg-12345'], 200),
        ]);
    }

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
            ->assertSessionHas('wa_url')
            ->assertSessionHas('wa_auto_sent', true);

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
            ->assertSessionHas('wa_auto_sent', true)
            ->assertSessionHas('success');

        $this->assertStringContainsString('api.whatsapp.com/send', session('wa_url'));
    }

    public function test_admin_reset_password_attempts_auto_whatsapp(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'no_wa' => '081234567890',
            'tanggal_lahir' => '1990-05-17',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.anggota.reset-password', $a))
            ->assertRedirect(route('admin.anggota.show', $a))
            ->assertSessionHas('wa_reset_url')
            ->assertSessionHas('wa_reset_auto_sent', true);
    }

    public function test_admin_can_deactivate_anggota(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $user->id, 'no_wa' => '081234567890']);

        $this->actingAs($admin)
            ->post(route('admin.anggota.deactivate', $a), [
                'alasan_nonaktif' => 'Pindah domisili luar daerah',
            ])
            ->assertRedirect(route('admin.anggota.show', $a))
            ->assertSessionHas('success')
            ->assertSessionHas('wa_url')
            ->assertSessionHas('wa_auto_sent', true);

        $this->assertSame('nonaktif', $user->fresh()->status);
        $this->assertSame('Pindah domisili luar daerah', $a->fresh()->catatan_verifikasi);
    }

    public function test_admin_can_activate_nonaktif_anggota(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'nonaktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'no_wa' => '081234567890',
            'catatan_verifikasi' => 'Alasan sebelumnya',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.anggota.activate', $a))
            ->assertRedirect(route('admin.anggota.show', $a))
            ->assertSessionHas('success')
            ->assertSessionHas('wa_url')
            ->assertSessionHas('wa_auto_sent', true);

        $this->assertSame('aktif', $user->fresh()->status);
        $this->assertNull($a->fresh()->catatan_verifikasi);
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
        $this->assertStringContainsString('>3313020101900001<', $html);
        $this->assertStringNotContainsString("'3313020101900001", $html);
        $this->assertStringContainsString('>1234567<', $html);
        $this->assertStringContainsString('>081234567890<', $html);
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

    public function test_admin_can_delete_anggota_without_event_participation(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Hapus Uji',
            'foto' => 'anggota/foto/test_delete.jpg',
        ]);

        Storage::disk('local')->put('anggota/foto/test_delete.jpg', 'dummy');

        $response = $this->actingAs($admin)
            ->delete(route('admin.anggota.destroy', $a));

        $response->assertRedirect(route('admin.anggota.index'))
            ->assertSessionHas('success');

        $this->assertNull(Anggota::find($a->id));
        $this->assertNull(User::find($user->id));
        $this->assertFalse(Storage::disk('local')->exists('anggota/foto/test_delete.jpg'));
    }

    public function test_admin_cannot_delete_anggota_with_event_participation(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Event Uji',
        ]);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Uji Hapus',
            'tanggal_mulai' => '2026-09-15',
            'tanggal_selesai' => '2026-09-17',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'qr_token' => 'token-test-delete-123',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.anggota.destroy', $a));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull(Anggota::find($a->id));
        $this->assertNotNull(User::find($user->id));
    }

    public function test_admin_can_filter_anggota_by_kategori_and_aum(): void
    {
        $admin = $this->admin();

        $user1 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a1 = Anggota::factory()->create([
            'user_id' => $user1->id,
            'nama_lengkap' => 'Kader AUM PKU',
            'nama_aum' => 'RS PKU Muhammadiyah Karanganyar',
            'jabatan_aum' => 'Dokter Umum',
        ]);

        $user2 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a2 = Anggota::factory()->create([
            'user_id' => $user2->id,
            'nama_lengkap' => 'Kader AUM SMK',
            'nama_aum' => 'SMK Muhammadiyah 1 Karanganyar',
            'jabatan_aum' => 'Guru',
        ]);

        $user3 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a3 = Anggota::factory()->create([
            'user_id' => $user3->id,
            'nama_lengkap' => 'Kader Murni Cabang',
            'pcm' => 'Colomadu',
            'nama_aum' => null,
            'jabatan_aum' => null,
            'keaktifan_muhammadiyah' => ['Pimpinan Cabang Muhammadiyah'],
        ]);

        // Filter kategori=aum
        $response = $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['kategori' => 'aum']));
        $response->assertOk();
        $response->assertSee('Kader AUM PKU');
        $response->assertSee('Kader AUM SMK');
        $response->assertDontSee('Kader Murni Cabang');

        // Filter aum spesifik
        $resAum = $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['aum' => 'RS PKU Muhammadiyah Karanganyar']));
        $resAum->assertOk();
        $resAum->assertSee('Kader AUM PKU');
        $resAum->assertDontSee('Kader AUM SMK');
    }

    public function test_admin_can_sort_anggota_by_aum_and_nama(): void
    {
        $admin = $this->admin();

        $userA = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create([
            'user_id' => $userA->id,
            'nama_lengkap' => 'Zainal Abidin',
            'nama_aum' => 'Klinik PKU Muhammadiyah Karanganyar',
        ]);

        $userB = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create([
            'user_id' => $userB->id,
            'nama_lengkap' => 'Anwar Sanusi',
            'nama_aum' => 'SMA Muhammadiyah 1 Karanganyar',
        ]);

        // Sort nama_asc
        $resSortNama = $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['sort' => 'nama_asc']));
        $resSortNama->assertOk();
        $resSortNama->assertSeeInOrder(['Anwar Sanusi', 'Zainal Abidin']);

        // Sort aum_asc
        $resSortAum = $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['sort' => 'aum_asc']));
        $resSortAum->assertOk();
        $resSortAum->assertSeeInOrder(['Klinik PKU Muhammadiyah Karanganyar', 'SMA Muhammadiyah 1 Karanganyar']);
    }
}
