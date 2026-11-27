<?php

namespace Tests\Feature;

use App\Exports\AnggotaExport;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PesertaExcelEventTest extends TestCase
{
    use RefreshDatabase;

    private function eventDenganPeserta(User $admin): Event
    {
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Excel',
            'tanggal_mulai' => '2026-12-05',
            'tanggal_selesai' => '2026-12-06',
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $data = [
            ['Zainal Contoh', 'Tasikmadu', '081200000001', true],
            ['Budi Contoh', 'Tasikmadu', '081200000002', true],
            ['Ani Contoh', 'Colomadu', '081200000003', true],
            ['Nonaktif Contoh', 'Colomadu', '081200000004', false],
        ];
        foreach ($data as [$nama, $pcm, $wa, $aktif]) {
            $anggota = Anggota::factory()->create([
                'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
                'nama_lengkap' => $nama, 'pcm' => $pcm, 'no_wa' => $wa,
            ]);
            EventPeserta::create([
                'event_id' => $event->id, 'anggota_id' => $anggota->id,
                'qr_token' => app(QrService::class)->generateToken(), 'status_aktif' => $aktif,
            ]);
        }

        return $event;
    }

    public function test_admin_unduh_biodata_peserta_urut_pcm_dan_nama_tanpa_nonaktif(): void
    {
        Excel::fake();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->eventDenganPeserta($admin);

        $this->actingAs($admin)->get(route('admin.events.peserta.excel', $event))->assertOk();

        Excel::matchByRegex();
        Excel::assertDownloaded('/^Peserta_Baitul_Arqam_Excel_\d{8}_\d{6}\.xlsx$/', function (AnggotaExport $export) {
            $nama = $export->view()->getData()['anggotas']->pluck('nama_lengkap')->all();

            return $nama === ['Ani Contoh', 'Budi Contoh', 'Zainal Contoh'];
        });
    }

    public function test_fasilitator_tidak_boleh_unduh_biodata(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->eventDenganPeserta($admin);
        $fasil = User::factory()->create(['role' => 'fasilitator', 'status' => 'aktif']);
        $event->fasilitator()->attach($fasil->id);

        $this->actingAs($fasil)->get(route('admin.events.peserta.excel', $event))->assertForbidden();
    }

    public function test_halaman_event_punya_tombol_excel_dan_salin_semua_nomor(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->eventDenganPeserta($admin);

        $html = $this->actingAs($admin)->get(route('admin.events.show', $event))->assertOk()->getContent();

        $this->assertStringContainsString('data-peserta-excel', $html);
        $this->assertStringContainsString('data-salin-nomor-wa', $html);
        $this->assertStringContainsString('Salin Nomor WA (3)', $html);
        $this->assertStringContainsString('6281200000003', $html);
    }
}
