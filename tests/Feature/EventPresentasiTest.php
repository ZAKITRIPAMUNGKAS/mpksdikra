<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventPresentasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_live_presentation(): void
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
            'nama_lengkap' => 'Ahmad Dahlan',
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
            'urutan' => 1,
            'nama_sesi' => 'Falsafah Gerakan',
            'jenis' => 'materi',
        ]);

        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_pretest' => 60,
            'nilai_posttest' => 85,
            'nilai_afektif' => 88,
            'nilai_psikomotor' => 90,
            'nilai_keaktifan' => 80,
            'nilai_kehadiran' => 100,
            'nilai_akhir' => 86.5,
            'ranking' => 1,
            'status_kelulusan' => 'Lulus',
            'verification_hash' => hash('sha256', 'test-hash'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.presentasi', $event));

        $response->assertOk();
        $response->assertSee('Baitul Arqam PDM Karanganyar 2026');
        $response->assertSee('Live PPT');
        $response->assertSee('Ahmad Dahlan');
        $response->assertSee('LULUS MURNI');
    }

    public function test_guest_cannot_access_live_presentation(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Test',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $response = $this->get(route('admin.events.presentasi', $event));

        $response->assertRedirect(route('login'));
    }
}
