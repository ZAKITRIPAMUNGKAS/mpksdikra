<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PresensiCetakTest extends TestCase
{
    use RefreshDatabase;

    private function createEventWithSession(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Uji Coba',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Manhaj Tarjih Muhammadiyah',
            'urutan' => 1,
            'jenis' => 'materi',
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $userKader->id, 'nama_lengkap' => 'Kader Peserta 1']);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        return [$admin, $event, $sesi, $userKader];
    }

    public function test_admin_can_download_lembar_presensi_pdf(): void
    {
        [$admin, $event, $sesi] = $this->createEventWithSession();

        $response = $this->actingAs($admin)
            ->get(route('admin.events.absensi.cetak', [$event, $sesi]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_non_admin_cannot_download_lembar_presensi(): void
    {
        [, $event, $sesi, $userKader] = $this->createEventWithSession();

        $this->actingAs($userKader)
            ->get(route('admin.events.absensi.cetak', [$event, $sesi]))
            ->assertForbidden();
    }
}
