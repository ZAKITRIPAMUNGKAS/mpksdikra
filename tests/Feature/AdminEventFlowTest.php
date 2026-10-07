<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_event' => 'Baitul Arqam Karanganyar 2026',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-03',
            'lokasi' => 'Balai Muhammadiyah Karanganyar',
            'status' => 'persiapan',
        ], $overrides);
    }

    public function test_admin_creates_event(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.events.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('events', ['nama_event' => 'Baitul Arqam Karanganyar 2026', 'status' => 'persiapan']);
    }

    public function test_anggota_cannot_create_event(): void
    {
        $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($u)->post(route('admin.events.store'), $this->payload())->assertForbidden();
        $this->assertSame(0, Event::count());
    }

    public function test_admin_enrolls_only_active_members(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload() + ['created_by' => $admin->id]);

        $aktifUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $aktif = Anggota::factory()->create(['user_id' => $aktifUser->id]);

        $pendingUser = User::factory()->create(['role' => 'anggota', 'status' => 'menunggu_verifikasi']);
        $pending = Anggota::factory()->create(['user_id' => $pendingUser->id]);

        $this->actingAs($admin)
            ->post(route('admin.events.enroll.store', $event), [
                'anggota_ids' => [$aktif->id, $pending->id],
            ])
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertSame(1, EventPeserta::where('event_id', $event->id)->count());
        $this->assertDatabaseHas('event_peserta', ['event_id' => $event->id, 'anggota_id' => $aktif->id]);
    }

    public function test_quota_enforced_on_enroll(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload(['kuota' => 1]) + ['created_by' => $admin->id]);

        $ids = [];
        for ($i = 0; $i < 2; $i++) {
            $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
            $ids[] = Anggota::factory()->create(['user_id' => $u->id])->id;
        }

        $this->actingAs($admin)
            ->post(route('admin.events.enroll.store', $event), ['anggota_ids' => $ids])
            ->assertSessionHas('error');

        $this->assertSame(0, EventPeserta::where('event_id', $event->id)->count());
    }

    public function test_admin_enroll_search_is_paginated_to_twenty_items_per_page(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload() + ['created_by' => $admin->id]);

        // Buat 25 kader aktif
        for ($i = 0; $i < 25; $i++) {
            $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
            Anggota::factory()->create(['user_id' => $u->id]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.events.enroll', $event));

        $response->assertOk();
        $response->assertViewHas('kandidat', function ($kandidat) {
            return $kandidat instanceof LengthAwarePaginator
                && $kandidat->perPage() === 20
                && $kandidat->count() === 20
                && $kandidat->total() === 25;
        });
    }

    public function test_admin_enroll_search_filters_by_cabang(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload() + ['created_by' => $admin->id]);

        $uColomadu = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create([
            'user_id' => $uColomadu->id,
            'nama_lengkap' => 'Kader Colomadu',
            'pcm' => 'Colomadu',
            'kecamatan' => 'Colomadu',
        ]);

        $uJaten = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create([
            'user_id' => $uJaten->id,
            'nama_lengkap' => 'Kader Jaten',
            'pcm' => 'Jaten',
            'kecamatan' => 'Jaten',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.events.enroll', [$event, 'cabang' => 'Colomadu']));

        $response->assertOk();
        $response->assertViewHas('kandidat', function ($kandidat) {
            return $kandidat->total() === 1
                && $kandidat->first()->nama_lengkap === 'Kader Colomadu';
        });
    }

    public function test_admin_adds_session_and_scan_page_renders(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload() + ['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.events.sesi.store', $event), [
                'nama_sesi' => 'Sholat Berjamaah',
                'jenis' => 'sholat',
            ])
            ->assertRedirect();

        $sesi = $event->sesi()->first();
        $this->assertSame('sholat', $sesi->jenis);

        $this->actingAs($admin)
            ->get(route('admin.events.absensi.scan', [$event, $sesi]))
            ->assertOk()
            ->assertSee('Sholat Berjamaah');
    }

    public function test_selesai_status_is_final(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload(['status' => 'selesai']) + ['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('admin.events.status', $event), ['status' => 'persiapan'])
            ->assertSessionHas('error');

        $this->assertSame('selesai', $event->fresh()->status);
    }

    public function test_admin_certificate_forbidden_when_event_not_finished(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload(['status' => 'berlangsung']) + ['created_by' => $admin->id]);

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);
        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_kehadiran' => 100,
            'status_kelulusan' => 'Lulus',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.certificate', [$event, $peserta]))
            ->assertForbidden();
    }

    public function test_admin_certificate_forbidden_when_not_lulus(): void
    {
        $admin = $this->admin();
        $event = Event::create($this->payload(['status' => 'selesai']) + ['created_by' => $admin->id]);

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);
        PenilaianAkhir::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'nilai_kehadiran' => 50,
            'status_kelulusan' => 'Tidak Lulus',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.certificate', [$event, $peserta]))
            ->assertForbidden();
    }
}
