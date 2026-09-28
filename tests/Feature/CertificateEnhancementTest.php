<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CertificateEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_batch_certificates_when_event_completed(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar Sukses',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Ideologi Muhammadiyah',
            'urutan' => 1,
            'jenis' => 'materi',
            'pemateri' => 'Dr. H. Sukro',
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Kader Lulus Hebat',
            'pcm' => 'Tasikmadu',
        ]);

        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $anggota->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_pretest' => 60,
            'nilai_posttest' => 90,
            'nilai_afektif' => 85,
            'nilai_psikomotor' => 88,
            'nilai_keaktifan' => 10,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 88.5,
            'status_kelulusan' => 'Lulus',
            'verification_hash' => hash('sha256', 'test-cert-hash'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.certificates.batch', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_single_certificate_contains_two_page_transcript_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam PWM',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Kader Bersertifikat',
        ]);

        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_posttest' => 85,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 85.0,
            'status_kelulusan' => 'Lulus',
            'verification_hash' => hash('sha256', 'cert-hash-single'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.certificate', [$event, $peserta]));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
