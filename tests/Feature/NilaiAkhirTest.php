<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\Keaktifan;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiAkhirTest extends TestCase
{
    use RefreshDatabase;

    private function makeContext(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Nilai Akhir',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-03',
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);
        $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(['user_id' => $u->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        return [$admin, $event, $a];
    }

    public function test_keaktifan_converts_to_percentage(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        for ($i = 0; $i < 5; $i++) {
            Keaktifan::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'jenis' => 'tanya',
                'dicatat_oleh' => $admin->id,
            ]);
        }

        $nilai = app(NilaiService::class)->hitungKeaktifan($event, $a->id);
        $this->assertSame(50.0, $nilai);
    }

    public function test_keaktifan_capped_at_max(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        for ($i = 0; $i < 20; $i++) {
            Keaktifan::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'jenis' => 'jawab',
                'dicatat_oleh' => $admin->id,
            ]);
        }

        $nilai = app(NilaiService::class)->hitungKeaktifan($event, $a->id);
        $this->assertSame(100.0, $nilai);
    }

    public function test_nilai_akhir_uses_weighted_formula(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        $tpl = PsikomotorTemplate::create([
            'event_id' => $event->id,
            'jenis' => 'ibadah',
            'nama_aspek' => 'Sholat',
            'skor_maks' => 4,
        ]);

        PsikomotorNilai::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'template_id' => $tpl->id,
            'skor' => 4,
            'dinilai_oleh' => $admin->id,
        ]);

        for ($i = 0; $i < 10; $i++) {
            Keaktifan::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'jenis' => 'kultum',
                'dicatat_oleh' => $admin->id,
            ]);
        }

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_pretest' => 50, 'nilai_posttest' => 100]
        );

        $sa = AfektifSubAspek::create([
            'event_id' => $event->id,
            'nama_sub_aspek' => 'Sikap',
            'urutan' => 1,
            'aktif' => true,
        ]);
        $butir = AfektifButir::create([
            'sub_aspek_id' => $sa->id,
            'teks_butir' => 'Disiplin',
            'is_positif' => true,
            'urutan' => 1,
        ]);
        // skor 4 dari maks 4 -> afektif 100
        AfektifJawaban::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'butir_id' => $butir->id,
            'skor' => 4,
        ]);

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();

        $this->assertSame(100.0, (float) $p->nilai_psikomotor);
        $this->assertSame(100.0, (float) $p->nilai_keaktifan);
        $this->assertSame(100.0, (float) $p->nilai_afektif);

        $nGain = 1.0;
        $kognitif = (100 * 0.70) + ($nGain * 100 * 0.30);
        $psikomotor = (100 * 0.90) + (100 * 0.10);
        $expected = ($kognitif * 0.40) + ($psikomotor * 0.35) + (100 * 0.25);

        $this->assertSame(round($expected, 2), (float) $p->nilai_akhir);
    }

    public function test_admin_records_keaktifan_via_route(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        $this->actingAs($admin)
            ->post(route('admin.events.keaktifan.store', $event), [
                'anggota_id' => $a->id,
                'jenis' => 'imam',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('keaktifan', [
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'jenis' => 'imam',
        ]);
    }

    public function test_admin_cannot_record_keaktifan_for_non_participant(): void
    {
        [$admin, $event] = $this->makeContext();
        $stranger = Anggota::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.events.keaktifan.store', $event), [
                'anggota_id' => $stranger->id,
                'jenis' => 'tanya',
            ])
            ->assertSessionHas('error');
    }

    public function test_unified_graduation_lulus_murni(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Materi Sesi 1',
            'urutan' => 1,
            'jenis' => 'materi',
        ]);

        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $a->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        SesiTes::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'durasi_menit' => 30,
            'is_aktif' => true,
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(10),
        ]);

        RtlSoal::create([
            'event_id' => $event->id,
            'pertanyaan' => 'Rencana Aksi Anda',
            'tipe' => 'essay',
            'urutan' => 1,
        ]);

        Rtl::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'status' => 'terkirim',
        ]);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_posttest' => 85]
        );

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();
        $this->assertEquals(100.0, (float) $p->nilai_kehadiran);
        $this->assertEquals('Lulus', $p->status_kelulusan);
    }

    public function test_unified_graduation_bersyarat_when_rtl_missing(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Materi Sesi 1',
            'urutan' => 1,
            'jenis' => 'materi',
        ]);

        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $a->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        SesiTes::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'durasi_menit' => 30,
            'is_aktif' => true,
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(10),
        ]);

        // Event punya pertanyaan RTL tapi peserta belum mengumpulkan
        RtlSoal::create([
            'event_id' => $event->id,
            'pertanyaan' => 'Rencana Aksi Anda',
            'tipe' => 'essay',
            'urutan' => 1,
        ]);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_posttest' => 85]
        );

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();
        $this->assertEquals(100.0, (float) $p->nilai_kehadiran);
        $this->assertEquals('Lulus Bersyarat', $p->status_kelulusan);
    }

    public function test_unified_graduation_tidak_lulus_when_attendance_low(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        // 2 sesi dibuat, tapi peserta hanya hadir 1 sesi (50% < 75%)
        $sesi1 = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'S1', 'urutan' => 1, 'jenis' => 'materi']);
        EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'S2', 'urutan' => 2, 'jenis' => 'materi']);

        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi1->id,
            'anggota_id' => $a->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_posttest' => 95]
        );

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();
        $this->assertEquals(50.0, (float) $p->nilai_kehadiran);
        $this->assertEquals('Tidak Lulus', $p->status_kelulusan);
    }

    public function test_portal_participant_can_download_official_certificate(): void
    {
        [$admin, $event, $a] = $this->makeContext();
        $userAnggota = $a->user;

        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'nilai_posttest' => 85,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 85.0,
            'status_kelulusan' => 'Lulus',
            'verification_hash' => hash('sha256', 'portal-cert-test'),
        ]);

        $response = $this->actingAs($userAnggota)->get(route('portal.sertifikat.download', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_grade_assigned_when_grade_enabled(): void
    {
        [$admin, $event, $a] = $this->makeContext();
        $event->update(['gunakan_grade' => true]);

        $tpl = PsikomotorTemplate::create([
            'event_id' => $event->id,
            'jenis' => 'ibadah',
            'nama_aspek' => 'Sholat',
            'skor_maks' => 4,
        ]);
        PsikomotorNilai::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'template_id' => $tpl->id,
            'skor' => 4,
            'dinilai_oleh' => $admin->id,
        ]);

        for ($i = 0; $i < 10; $i++) {
            Keaktifan::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'jenis' => 'kultum',
                'dicatat_oleh' => $admin->id,
            ]);
        }

        $sa = AfektifSubAspek::create([
            'event_id' => $event->id,
            'nama_sub_aspek' => 'Sikap',
            'urutan' => 1,
            'aktif' => true,
        ]);
        $butir = AfektifButir::create([
            'sub_aspek_id' => $sa->id,
            'teks_butir' => 'Disiplin',
            'is_positif' => true,
            'urutan' => 1,
        ]);
        AfektifJawaban::create([
            'event_id' => $event->id,
            'anggota_id' => $a->id,
            'butir_id' => $butir->id,
            'skor' => 4,
        ]);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_pretest' => 50, 'nilai_posttest' => 100]
        );

        $sesi1 = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'S1', 'urutan' => 1, 'jenis' => 'materi']);
        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi1->id,
            'anggota_id' => $a->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();

        $this->assertNotNull($p->grade);
        $this->assertSame('A', $p->grade);
    }

    public function test_grade_null_when_grade_disabled(): void
    {
        [$admin, $event, $a] = $this->makeContext();

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_pretest' => 50, 'nilai_posttest' => 100, 'nilai_kehadiran' => 100]
        );

        $sesi1 = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'S1', 'urutan' => 1, 'jenis' => 'materi']);
        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi1->id,
            'anggota_id' => $a->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();
        $this->assertNull($p->grade);
    }

    public function test_grade_null_when_tidak_lulus(): void
    {
        [$admin, $event, $a] = $this->makeContext();
        $event->update(['gunakan_grade' => true]);

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $a->id],
            ['nilai_pretest' => 50, 'nilai_posttest' => 100, 'nilai_akhir' => 90]
        );

        app(NilaiService::class)->hitungUlangPeserta($event, $a->id);

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $a->id)->first();
        $this->assertNull($p->grade);
    }
}
