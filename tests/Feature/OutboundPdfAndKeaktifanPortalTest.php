<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\Keaktifan;
use App\Models\OutboundKelompok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OutboundPdfAndKeaktifanPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_outbound_groups_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Karanganyar Outbound',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $kelompok = OutboundKelompok::create([
            'event_id' => $event->id,
            'nomor' => 1,
            'nama_kelompok' => 'Kelompok KH Ahmad Dahlan',
        ]);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => 'Kader Outbound']);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
            'outbound_kelompok_id' => $kelompok->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.outbound.pdf', $event));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_participant_can_see_recorded_keaktifan_in_portal(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Keaktifan Test',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => 'Peserta Aktif']);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        Keaktifan::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'jenis' => 'tanya',
            'keterangan' => 'Pertanyaan kritis tentang ideologi',
            'dicatat_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($user)->get(route('portal.kehadiran.index', $event));

        $response->assertOk();
        $response->assertSee('Rekam Keaktifan &amp; Partisipasi', false);
        $response->assertSee('Pertanyaan kritis tentang ideologi');
        $response->assertSee('+1 Poin');
    }
}
