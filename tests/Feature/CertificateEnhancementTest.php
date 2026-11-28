<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_certificate_view_uses_approved_event_data_without_exposing_nik_or_inventing_jp(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Audit',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-12',
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);
        $event->setAttribute('tanggal_terbit_sertifikat', Carbon::parse('2026-10-15'));
        $event->setAttribute('nama_penandatangan_sertifikat', 'Dr. Ahmad Amanah');
        $event->setAttribute('nbm_penandatangan_sertifikat', '123.456');
        $event->setAttribute('jabatan_penandatangan_sertifikat', 'Ketua MPKSDI PDM Karanganyar');

        EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Ideologi Muhammadiyah',
            'urutan' => 1,
            'jenis' => 'materi',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);

        $anggota = Anggota::factory()->create([
            'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
            'nama_lengkap' => 'Kader Bersyarat',
            'nik' => '1234567890123456',
        ]);
        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);
        $nilai = PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_posttest' => 80,
            'nilai_afektif' => 80,
            'nilai_psikomotor' => 80,
            'nilai_akhir' => 80,
            'status_kelulusan' => 'Lulus Bersyarat',
            'verification_hash' => hash('sha256', 'certificate-audit'),
        ]);

        $html = view('admin.events.certificate-pdf', [
            'event' => $event->load('sesi'),
            'peserta' => $peserta->load('anggota'),
            'nilai' => $nilai,
            'qrPng' => null,
        ])->render();

        $this->assertStringContainsString('Dr. Ahmad Amanah', $html);
        $this->assertStringContainsString('NBM. 123.456', $html);
        $this->assertStringContainsString('15 Oktober 2026', $html);
        $this->assertStringContainsString('Telah memenuhi persyaratan kelulusan bersyarat', $html);
        $this->assertStringContainsString('120 menit', $html);
        $this->assertStringNotContainsString('NIK:', $html);
        $this->assertStringNotContainsString('1234567890123456', $html);
        $this->assertStringNotContainsString('H. Zainudin Ahpandi, S.E.', $html);
        $this->assertStringNotContainsString('16 JP', $html);
    }

    public function test_admin_can_edit_signatories_and_upload_png_signatures(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Pimpinan Muhammadiyah Karanganyar',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-03',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $signatureMot = UploadedFile::fake()->create('ttd_mot.png', 50, 'image/png');
        $signatureKetua = UploadedFile::fake()->create('ttd_ketua.png', 50, 'image/png');

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'nama_event' => $event->nama_event,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-03',
            'status' => 'berlangsung',
            'tanggal_terbit_sertifikat' => '2026-11-05',
            'jabatan_penandatangan_kiri' => 'Master of Training Utama',
            'nama_penandatangan_kiri' => 'Ust. Muhammad Wildan, S.Pd.I.',
            'keterangan_penandatangan_kiri' => 'Korps Instruktur Wilayah',
            'ttd_kiri' => $signatureMot,
            'jabatan_penandatangan_sertifikat' => 'Ketua PDM Karanganyar',
            'nama_penandatangan_sertifikat' => 'Prof. Dr. Muh. Syamsudin',
            'nbm_penandatangan_sertifikat' => '999.888',
            'ttd_kanan' => $signatureKetua,
        ]);

        $response->assertRedirect(route('admin.events.show', $event));

        $event->refresh();
        $this->assertEquals('Master of Training Utama', $event->jabatan_penandatangan_kiri);
        $this->assertEquals('Ust. Muhammad Wildan, S.Pd.I.', $event->nama_penandatangan_kiri);
        $this->assertEquals('Korps Instruktur Wilayah', $event->keterangan_penandatangan_kiri);
        $this->assertEquals('Ketua PDM Karanganyar', $event->jabatan_penandatangan_sertifikat);
        $this->assertEquals('Prof. Dr. Muh. Syamsudin', $event->nama_penandatangan_sertifikat);
        $this->assertEquals('999.888', $event->nbm_penandatangan_sertifikat);

        $this->assertNotNull($event->ttd_kiri_path);
        $this->assertNotNull($event->ttd_kanan_path);
        Storage::disk('public')->assertExists($event->ttd_kiri_path);
        Storage::disk('public')->assertExists($event->ttd_kanan_path);

        // Test removing signatures
        $responseRemove = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'nama_event' => $event->nama_event,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-03',
            'status' => 'berlangsung',
            'hapus_ttd_kiri' => '1',
            'hapus_ttd_kanan' => '1',
        ]);

        $responseRemove->assertRedirect(route('admin.events.show', $event));

        $event->refresh();
        $this->assertNull($event->ttd_kiri_path);
        $this->assertNull($event->ttd_kanan_path);
    }

    public function test_certificate_renders_formal_typography_and_custom_signatories(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Instruktur',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-03',
            'status' => 'selesai',
            'created_by' => $admin->id,
            'nama_penandatangan_kiri' => 'Ust. Fauzi, M.Ag.',
            'jabatan_penandatangan_kiri' => 'Pimpinan Korps Instruktur',
            'keterangan_penandatangan_kiri' => 'NBM. 555.444',
            'nama_penandatangan_sertifikat' => 'K.H. Dahlan Sudirman',
            'jabatan_penandatangan_sertifikat' => 'Ketua MPKSDI PDM Karanganyar',
            'nbm_penandatangan_sertifikat' => '777.666',
        ]);

        $anggota = Anggota::factory()->create([
            'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
            'nama_lengkap' => 'Kader Teladan Persyarikatan',
        ]);
        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);
        $nilai = PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_posttest' => 95,
            'nilai_afektif' => 90,
            'nilai_psikomotor' => 92,
            'nilai_akhir' => 93,
            'status_kelulusan' => 'Lulus',
            'grade' => 'A',
            'verification_hash' => hash('sha256', 'formal-cert-test'),
        ]);

        $html = view('admin.events.certificate-pdf', [
            'event' => $event->load('sesi'),
            'peserta' => $peserta->load('anggota'),
            'nilai' => $nilai,
            'qrPng' => null,
        ])->render();

        // Must contain formal typography
        $this->assertStringContainsString('LULUS', $html);
        $this->assertStringContainsString('letter-spacing: 7px', $html);
        $this->assertStringContainsString('PREDIKAT A', $html);
        $this->assertStringContainsString('Ust. Fauzi, M.Ag.', $html);
        $this->assertStringContainsString('Pimpinan Korps Instruktur', $html);
        $this->assertStringContainsString('K.H. Dahlan Sudirman', $html);
        $this->assertStringContainsString('NBM. 777.666', $html);

        // Must NOT contain the AI-slop rounded pill classes or inline styles
        $this->assertStringNotContainsString('border-radius: 14px', $html);
        $this->assertStringNotContainsString('border: 1.5px solid #10b981', $html);
    }
}
