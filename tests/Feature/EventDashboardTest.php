<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PesertaTesMulai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_event_training_dashboard_with_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar 2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'lokasi' => 'Gedung Dakwah Karanganyar',
            'created_by' => $admin->id,
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Kader Teladan',
            'jenis_kelamin' => 'L',
            'pcm' => 'Karanganyar',
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Orientasi & Muqaddimah',
            'jenis' => 'materi',
            'urutan' => 1,
            'tanggal' => now()->toDateString(),
            'pemateri' => 'Pimpinan Daerah',
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'pretest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(30),
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', $event));

        $response->assertOk();
        $response->assertSee('Baitul Arqam PDM Karanganyar 2026');
        $response->assertSee('Pusat Komando &amp; Monitoring Pelatihan Baitul Arqam', false);
        $response->assertSee('Orientasi &amp; Muqaddimah', false);
        $response->assertSee('Kader Teladan');
        $response->assertSee('Cetak ID Card Massal (A4)');
    }
}
