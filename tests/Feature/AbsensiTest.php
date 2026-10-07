<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    private function setupEvent(int $sesiCount = 3): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Test',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-03',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        for ($i = 1; $i <= $sesiCount; $i++) {
            EventSesi::create([
                'event_id' => $event->id,
                'nama_sesi' => "Sesi $i",
                'jenis' => 'materi',
                'urutan' => $i,
            ]);
        }

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        $ep = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        return [$admin, $event, $anggota, $ep];
    }

    public function test_qr_scan_records_attendance_once(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent(3);
        $sesi = $event->sesi()->first();

        $qr = app(QrService::class)->payload($ep);

        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.process', $event), [
                'qr_code' => $qr,
                'sesi_id' => $sesi->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success', 'nama' => $anggota->nama_lengkap]);

        $this->assertDatabaseHas('absensi', [
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $anggota->id,
            'metode' => 'qr',
        ]);
    }

    public function test_duplicate_scan_rejected(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent();
        $sesi = $event->sesi()->first();
        $qr = app(QrService::class)->payload($ep);

        $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi->id])->assertOk();
        $second = $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi->id]);

        $second->assertOk()->assertJson(['status' => 'duplicate']);
        $this->assertSame(1, Absensi::where('sesi_id', $sesi->id)->where('anggota_id', $anggota->id)->count());
    }

    public function test_invalid_qr_rejected(): void
    {
        [$admin, $event] = $this->setupEvent();
        $sesi = $event->sesi()->first();

        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.process', $event), [
                'qr_code' => base64_encode(json_encode(['e' => $event->id, 'a' => 999, 't' => 'x', 's' => 'forged'])),
                'sesi_id' => $sesi->id,
            ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    public function test_manual_attendance_by_admin(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent();
        $sesi = $event->sesi()->first();

        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.manual', $event), [
                'anggota_id' => $anggota->id,
                'sesi_id' => $sesi->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('absensi', ['sesi_id' => $sesi->id, 'anggota_id' => $anggota->id, 'metode' => 'manual']);
    }

    public function test_attendance_percentage_and_graduation_status(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent(4);
        $qr = app(QrService::class)->payload($ep);

        foreach ($event->sesi()->take(3)->get() as $sesi) {
            $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi->id])->assertOk();
        }

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $this->assertSame('75.00', $p->nilai_kehadiran);
        $this->assertSame('Lulus Bersyarat', $p->status_kelulusan);
    }

    public function test_full_attendance_yields_lulus_status_on_scan(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent(2);
        $qr = app(QrService::class)->payload($ep);

        foreach ($event->sesi()->get() as $sesi) {
            $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi->id])->assertOk();
        }

        // Tanpa membuka halaman ranking, status_kelulusan harus sudah ikut ter-update
        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $this->assertSame('100.00', $p->nilai_kehadiran);
        $this->assertSame('Lulus', $p->status_kelulusan);
    }

    public function test_admin_can_cancel_or_void_attendance(): void
    {
        [$admin, $event, $anggota, $ep] = $this->setupEvent(2);
        $sesiList = $event->sesi()->get();
        $sesi1 = $sesiList[0];
        $sesi2 = $sesiList[1];
        $qr = app(QrService::class)->payload($ep);

        // Hadir 2 sesi (100% Lulus)
        $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi1->id])->assertOk();
        $this->actingAs($admin)->postJson(route('admin.events.absensi.process', $event), ['qr_code' => $qr, 'sesi_id' => $sesi2->id])->assertOk();

        $absensi = Absensi::where('sesi_id', $sesi2->id)->where('anggota_id', $anggota->id)->first();
        $this->assertNotNull($absensi);

        // Batalkan sesi 2
        $res = $this->actingAs($admin)
            ->deleteJson(route('admin.events.absensi.destroy', [$event, $sesi2, $absensi]));
        $res->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('absensi', ['id' => $absensi->id]);

        // Kehadiran turun jadi 50% dan status kelulusan dihitung ulang jadi Tidak Lulus (<75%)
        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $this->assertSame('50.00', $p->nilai_kehadiran);
        $this->assertSame('Tidak Lulus', $p->status_kelulusan);
    }
}
