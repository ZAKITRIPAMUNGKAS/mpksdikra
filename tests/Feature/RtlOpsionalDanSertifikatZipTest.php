<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\RtlSoal;
use App\Models\User;
use App\Services\NilaiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RtlOpsionalDanSertifikatZipTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function eventDenganRtl(User $admin, bool $rtlWajib): Event
    {
        $event = Event::create([
            'nama_event' => 'Baitul Arqam RTL Contoh',
            'tanggal_mulai' => now()->subDays(2)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'selesai',
            'rtl_wajib' => $rtlWajib,
            'created_by' => $admin->id,
        ]);
        RtlSoal::create(['event_id' => $event->id, 'pertanyaan' => 'Rencana aksi?', 'tipe' => 'essay', 'urutan' => 1]);

        return $event;
    }

    public function test_rtl_wajib_belum_kirim_menjadi_lulus_bersyarat(): void
    {
        $event = $this->eventDenganRtl($this->admin(), true);
        $p = new PenilaianAkhir(['nilai_kehadiran' => 100]);

        $this->assertSame('Lulus Bersyarat', app(NilaiService::class)->tentukanStatusKelulusan($event, $p, 999));
    }

    public function test_rtl_opsional_tidak_memengaruhi_kelulusan(): void
    {
        $event = $this->eventDenganRtl($this->admin(), false);
        $p = new PenilaianAkhir(['nilai_kehadiran' => 100]);

        $this->assertSame('Lulus', app(NilaiService::class)->tentukanStatusKelulusan($event, $p, 999));
    }

    public function test_event_baru_default_rtl_wajib_dan_bisa_dimatikan_lewat_form(): void
    {
        $admin = $this->admin();
        $payload = [
            'nama_event' => 'Baitul Arqam Toggle RTL',
            'tanggal_mulai' => '2026-12-01',
            'tanggal_selesai' => '2026-12-02',
            'status' => 'persiapan',
            'bobot_kognitif' => 45,
            'bobot_psikomotor' => 25,
            'bobot_afektif' => 30,
            'bobot_keaktifan' => 0,
        ];

        $this->actingAs($admin)->post(route('admin.events.store'), $payload)->assertSessionHasNoErrors();
        $event = Event::where('nama_event', 'Baitul Arqam Toggle RTL')->firstOrFail();
        $this->assertTrue($event->fresh()->rtl_wajib);

        $this->actingAs($admin)->put(route('admin.events.update', $event), $payload + ['rtl_wajib' => '0'])
            ->assertSessionHasNoErrors();
        $this->assertFalse($event->fresh()->rtl_wajib);

        $this->actingAs($admin)->get(route('admin.events.edit', $event))->assertOk()->assertSee('RTL Wajib untuk Kelulusan');
    }

    public function test_admin_bisa_unduh_sertifikat_massal_zip_per_peserta(): void
    {
        $admin = $this->admin();
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Zip',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);
        $sesi = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'Materi Contoh', 'urutan' => 1, 'jenis' => 'materi']);

        foreach (['Peserta Satu', 'Peserta Dua'] as $nama) {
            $anggota = Anggota::factory()->create([
                'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
                'nama_lengkap' => $nama,
            ]);
            EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
            Absensi::create(['event_id' => $event->id, 'sesi_id' => $sesi->id, 'anggota_id' => $anggota->id, 'waktu_scan' => now(), 'scanned_by' => $admin->id, 'metode' => 'qr']);
            PenilaianAkhir::create([
                'event_id' => $event->id, 'anggota_id' => $anggota->id,
                'nilai_kehadiran' => 100, 'nilai_akhir' => 85, 'status_kelulusan' => 'Lulus',
                'verification_hash' => hash('sha256', $nama),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.events.certificates.zip', $event));

        $response->assertOk();
        $this->assertSame('application/zip', $response->headers->get('Content-Type'));
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($path);
        sort($names);
        $this->assertSame(['001-peserta-dua.pdf', '002-peserta-satu.pdf'], $names);
    }

    public function test_mematikan_rtl_wajib_langsung_memperbarui_status_tersimpan(): void
    {
        $admin = $this->admin();
        $event = $this->eventDenganRtl($admin, true);
        $sesi = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'Materi', 'urutan' => 1, 'jenis' => 'materi']);
        $anggota = Anggota::factory()->create(['user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id]);
        EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
        Absensi::create(['event_id' => $event->id, 'sesi_id' => $sesi->id, 'anggota_id' => $anggota->id, 'waktu_scan' => now(), 'scanned_by' => $admin->id, 'metode' => 'qr']);

        app(NilaiService::class)->hitungUlang($event);
        $this->assertSame('Lulus Bersyarat', PenilaianAkhir::where('event_id', $event->id)->value('status_kelulusan'));

        $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'nama_event' => $event->nama_event,
            'tanggal_mulai' => $event->tanggal_mulai->toDateString(),
            'tanggal_selesai' => $event->tanggal_selesai->toDateString(),
            'status' => 'selesai',
            'rtl_wajib' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Lulus', PenilaianAkhir::where('event_id', $event->id)->value('status_kelulusan'));
    }

    public function test_zip_tanpa_peserta_lulus_atau_pilihan_memberi_404_jelas(): void
    {
        $admin = $this->admin();
        $event = Event::create([
            'nama_event' => 'Belum Selesai', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02',
            'status' => 'persiapan', 'created_by' => $admin->id,
        ]);

        // Admin boleh mencetak kapan saja; tanpa peserta lulus dan tanpa pilihan, tidak ada yang dicetak.
        $this->actingAs($admin)->get(route('admin.events.certificates.zip', $event))->assertNotFound();
    }
}
