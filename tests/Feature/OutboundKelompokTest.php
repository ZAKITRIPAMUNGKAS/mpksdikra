<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\OutboundKelompok;
use App\Models\User;
use App\Services\OutboundGroupService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutboundKelompokTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_outbound_groups_balanced_by_gender(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Outbound Event',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        // Create 4 males and 4 females
        for ($i = 1; $i <= 4; $i++) {
            $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
            $a = Anggota::factory()->create(['user_id' => $u->id, 'jenis_kelamin' => 'L', 'kecamatan' => 'Karanganyar']);
            EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'qr_token' => app(QrService::class)->generateToken()]);
        }
        for ($i = 1; $i <= 4; $i++) {
            $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
            $a = Anggota::factory()->create(['user_id' => $u->id, 'jenis_kelamin' => 'P', 'kecamatan' => 'Jaten']);
            EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'qr_token' => app(QrService::class)->generateToken()]);
        }

        $this->actingAs($admin)
            ->get(route('admin.events.outbound.index', $event))
            ->assertOk()
            ->assertSee('Pengacak Kelompok Outbound');

        // Generate 2 groups with gender balance
        $this->actingAs($admin)
            ->post(route('admin.events.outbound.generate', $event), [
                'jumlah_kelompok' => 2,
                'kriteria' => 'seimbang_gender',
            ])
            ->assertRedirect();

        $this->assertSame(2, OutboundKelompok::where('event_id', $event->id)->count());

        $groups = OutboundKelompok::where('event_id', $event->id)->with('peserta.anggota')->get();
        foreach ($groups as $g) {
            $this->assertSame(4, $g->peserta->count());
            $this->assertSame(2, $g->peserta->filter(fn ($p) => $p->anggota->jenis_kelamin === 'L')->count());
            $this->assertSame(2, $g->peserta->filter(fn ($p) => $p->anggota->jenis_kelamin === 'P')->count());
        }

        // Test reset
        $this->actingAs($admin)
            ->post(route('admin.events.outbound.reset', $event))
            ->assertRedirect();

        $this->assertSame(0, OutboundKelompok::where('event_id', $event->id)->count());
        $this->assertSame(0, EventPeserta::where('event_id', $event->id)->whereNotNull('outbound_kelompok_id')->count());
    }

    public function test_pisah_putra_putri_membuat_kelompok_terpisah_sebanding(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Outbound Pisah',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
        foreach (['L' => 9, 'P' => 3] as $jk => $n) {
            for ($i = 1; $i <= $n; $i++) {
                $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
                $a = Anggota::factory()->create(['user_id' => $u->id, 'jenis_kelamin' => $jk]);
                EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'qr_token' => app(QrService::class)->generateToken()]);
            }
        }

        $this->actingAs($admin)
            ->get(route('admin.events.outbound.index', $event))
            ->assertOk()
            ->assertSee('value="pisah_gender" selected', false);

        $this->actingAs($admin)
            ->post(route('admin.events.outbound.generate', $event), ['jumlah_kelompok' => 4, 'kriteria' => 'pisah_gender'])
            ->assertSessionHas('success');

        $groups = OutboundKelompok::where('event_id', $event->id)->with('peserta.anggota')->orderBy('id')->get();
        $this->assertSame(['Kelompok Putra 1', 'Kelompok Putra 2', 'Kelompok Putra 3', 'Kelompok Putri 1'], $groups->pluck('nama_kelompok')->all());
        foreach ($groups as $g) {
            $jk = $g->peserta->pluck('anggota.jenis_kelamin')->unique()->values()->all();
            $this->assertSame([str_contains($g->nama_kelompok, 'Putri') ? 'P' : 'L'], $jk);
            $this->assertSame(3, $g->peserta->count());
        }
        $this->assertSame(0, EventPeserta::where('event_id', $event->id)->whereNull('outbound_kelompok_id')->count());

        // Halaman mengingat kriteria terakhir untuk acak ulang.
        $this->actingAs($admin)
            ->get(route('admin.events.outbound.index', $event))
            ->assertSee('value="pisah_gender" selected', false)
            ->assertSee('Kelompok Putri 1');
    }

    public function test_bagi_jumlah_kelompok_pisah_gender(): void
    {
        $this->assertSame([2, 2], OutboundGroupService::bagiJumlahKelompok(10, 10, 4));
        $this->assertSame([1, 1], OutboundGroupService::bagiJumlahKelompok(10, 2, 1));
        $this->assertSame([4, 1], OutboundGroupService::bagiJumlahKelompok(20, 1, 5));
        $this->assertSame([0, 3], OutboundGroupService::bagiJumlahKelompok(0, 9, 3));
        $this->assertSame([2, 0], OutboundGroupService::bagiJumlahKelompok(2, 0, 5));
    }
}
