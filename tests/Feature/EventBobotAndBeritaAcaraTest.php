<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventBobotAndBeritaAcaraTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    public function test_event_creation_normalizes_and_stores_custom_bobot(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'nama_event' => 'Baitul Arqam Pimpinan Cabang',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-02',
            'lokasi' => 'Pusdiklat Karanganyar',
            'status' => 'persiapan',
            'bobot_kognitif' => 45,
            'bobot_psikomotor' => 25,
            'bobot_afektif' => 30,
            'bobot_keaktifan' => 0,
        ]);

        $response->assertRedirect();

        $event = Event::where('nama_event', 'Baitul Arqam Pimpinan Cabang')->first();
        $this->assertNotNull($event);
        $this->assertEquals(0.45, $event->bobot_kognitif);
        $this->assertEquals(0.25, $event->bobot_psikomotor);
        $this->assertEquals(0.30, $event->bobot_afektif);
        $this->assertEquals(0.00, $event->bobot_keaktifan);
    }

    public function test_nilai_service_calculates_final_score_with_event_custom_weights(): void
    {
        $admin = $this->createAdmin();

        $event = Event::create([
            'nama_event' => 'BA Custom Bobot',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-02',
            'status' => 'selesai',
            'created_by' => $admin->id,
            'bobot_kognitif' => 0.60,
            'bobot_psikomotor' => 0.20,
            'bobot_afektif' => 0.20,
            'bobot_keaktifan' => 0.00,
        ]);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        // Component scores in PenilaianAkhir:
        // Pretest 100 & Posttest 100 -> Kognitif = 100
        // Psikomotor 80 & Keaktifan 80 -> Psikomotor = 80
        // Afektif = 60
        // Expected with custom weights (0.60, 0.20, 0.20):
        // 100 * 0.6 + 80 * 0.2 + 60 * 0.2 = 60 + 16 + 12 = 88.0
        // (Default 0.40, 0.35, 0.25 would have yielded: 40 + 28 + 15 = 83.0)
        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_pretest' => 100,
            'nilai_posttest' => 100,
            'nilai_kognitif' => 100,
            'nilai_psikomotor' => 80,
            'nilai_afektif' => 60,
            'nilai_keaktifan' => 80,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 0,
            'lulus' => true,
        ]);

        $nilaiService = app(NilaiService::class);
        $nilaiService->hitungNilaiAkhirDanRanking($event, [$anggota->id]);

        $penilaian = PenilaianAkhir::where('event_id', $event->id)
            ->where('anggota_id', $anggota->id)
            ->first();

        $this->assertEquals(88.0, (float) $penilaian->nilai_akhir);
    }

    public function test_admin_can_download_berita_acara_pdf(): void
    {
        $admin = $this->createAdmin();

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Yudisium Test',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-02',
            'status' => 'selesai',
            'lokasi' => 'Gedung Dakwah Karanganyar',
            'created_by' => $admin->id,
        ]);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => 'Kader Teladan']);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_kognitif' => 90,
            'nilai_psikomotor' => 90,
            'nilai_afektif' => 90,
            'nilai_keaktifan' => 90,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 90,
            'predikat' => 'Sangat Baik',
            'ranking' => 1,
            'lulus' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.berita-acara', $event));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('berita-acara-yudisium-baitul-arqam-yudisium-test.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_berita_acara_access_is_restricted_to_admin(): void
    {
        $admin = $this->createAdmin();
        $event = Event::create([
            'nama_event' => 'BA Restrict Test',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-02',
            'status' => 'selesai',
            'lokasi' => 'Pusdiklat',
            'created_by' => $admin->id,
        ]);

        // Guest redirected to login
        $guestResponse = $this->get(route('admin.events.berita-acara', $event));
        $guestResponse->assertRedirect(route('login'));

        // Anggota gets 403
        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggotaResponse = $this->actingAs($anggotaUser)->get(route('admin.events.berita-acara', $event));
        $anggotaResponse->assertForbidden();
    }
}
