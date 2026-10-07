<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PengingatWaEventTest extends TestCase
{
    use RefreshDatabase;

    private function createEventWithPeserta(): array
    {
        config([
            'services.whatsapp.provider' => 'fonnte',
            'services.whatsapp.token' => 'dummy-fonnte-token-123',
            'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Pimpinan PCM',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-11',
            'lokasi' => 'Gedung Dakwah Karanganyar',
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $user1 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota1 = Anggota::factory()->create([
            'user_id' => $user1->id,
            'nama_lengkap' => 'Ahmad Dahlan',
            'no_wa' => '081234567890',
        ]);
        $peserta1 = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota1->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        $user2 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota2 = Anggota::factory()->create([
            'user_id' => $user2->id,
            'nama_lengkap' => 'Siti Walidah',
            'no_wa' => '089876543210',
        ]);
        $peserta2 = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota2->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        return [$admin, $event, $peserta1, $peserta2, $anggota1, $anggota2];
    }

    public function test_admin_can_send_wa_reminder_to_individual_participant(): void
    {
        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => true, 'target' => ['6281234567890']], 200),
        ]);

        [$admin, $event, $peserta1] = $this->createEventWithPeserta();

        $response = $this->actingAs($admin)
            ->post(route('admin.events.peserta.remind-wa', [$event, $peserta1]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($data['message'] ?? '', 'Baitul Arqam Pimpinan PCM')
                && ($data['target'] ?? '') === '6281234567890';
        });
    }

    public function test_admin_can_send_batch_wa_reminder_to_all_participants(): void
    {
        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        [$admin, $event] = $this->createEventWithPeserta();

        $response = $this->actingAs($admin)
            ->post(route('admin.events.peserta.remind-wa-batch', $event));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSentCount(2);
    }

    public function test_participant_cannot_send_wa_reminder(): void
    {
        [$admin, $event, $peserta1, , $anggota1] = $this->createEventWithPeserta();

        $response = $this->actingAs($anggota1->user)
            ->post(route('admin.events.peserta.remind-wa', [$event, $peserta1]));

        $response->assertForbidden();
    }
}
