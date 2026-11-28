<?php

namespace Tests\Feature;

use App\Exports\LaporanEventExport;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LaporanTanpaNikTest extends TestCase
{
    use RefreshDatabase;

    private const NIK = '3313999988887777';

    private function siapkan(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Laporan Privasi', 'tanggal_mulai' => '2026-12-01', 'tanggal_selesai' => '2026-12-02',
            'status' => 'selesai', 'created_by' => $admin->id, 'bobot_kognitif' => 0.5, 'bobot_psikomotor' => 0.2,
            'bobot_afektif' => 0.3, 'bobot_keaktifan' => 0,
            'nama_penandatangan_kiri' => 'Ustadz Penanda Kiri', 'nama_penandatangan_sertifikat' => 'Ketua Penanda Kanan',
        ]);
        $anggota = Anggota::factory()->create(['nama_lengkap' => 'Kader Privasi', 'nik' => self::NIK, 'nbm' => '1122334', 'pcm' => 'Jaten']);
        EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
        PenilaianAkhir::create([
            'event_id' => $event->id, 'anggota_id' => $anggota->id, 'nilai_akhir' => 80, 'ranking' => 1,
            'status_kelulusan' => 'Lulus', 'verification_hash' => hash('sha256', 'privasi'),
        ]);

        return [$admin, $event];
    }

    public function test_laporan_pdf_tanpa_nik_dan_memakai_bobot_serta_penandatangan_kegiatan(): void
    {
        [$admin, $event] = $this->siapkan();
        $html = null;
        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function ($view, $data) use (&$html) {
            $html = view($view, $data)->render();

            return \Mockery::mock(\Barryvdh\DomPDF\PDF::class)->shouldReceive('setPaper')->andReturnSelf()
                ->getMock()->shouldReceive('download')->andReturn(response('PDF'))->getMock();
        });

        $this->actingAs($admin)->get(route('admin.events.report', $event))->assertOk();

        $this->assertStringNotContainsString(self::NIK, $html);
        $this->assertStringNotContainsString('NIK', $html);
        $this->assertStringContainsString('Kader Privasi', $html);
        $this->assertStringContainsString('NBM 1122334', $html);
        $this->assertStringContainsString('PCM Jaten', $html);
        $this->assertStringContainsString('50% Kognitif', $html, 'Rumus mengikuti bobot kegiatan.');
        $this->assertStringContainsString('Ustadz Penanda Kiri', $html);
        $this->assertStringContainsString('Ketua Penanda Kanan', $html);
        $this->assertStringNotContainsString('Zainudin', $html, 'Nama penandatangan tidak lagi di-hardcode.');
    }

    public function test_laporan_excel_tanpa_nik(): void
    {
        [, $event] = $this->siapkan();
        $html = (new LaporanEventExport($event->id))->view()->render();

        $this->assertStringNotContainsString(self::NIK, $html);
        $this->assertStringNotContainsString('>NIK<', $html);
        $this->assertStringContainsString('1122334', $html);
    }
}
