<?php

namespace Tests\Feature;

use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesIsianBukaTutupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    private User $pesertaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'BA Buka Tutup',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);

        $this->pesertaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $this->pesertaUser->id]);
        EventPeserta::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);
    }

    public function test_default_dibuka_dan_admin_bisa_tutup_lalu_buka_kembali(): void
    {
        $this->assertTrue($this->event->fresh()->isianDibuka('afektif'));
        $this->assertTrue($this->event->fresh()->isianDibuka('angket'));

        $this->actingAs($this->admin)
            ->get(route('admin.events.show', $this->event))
            ->assertOk()
            ->assertSee('data-akses-isian="afektif"', false)
            ->assertSee('data-akses-isian="angket"', false)
            ->assertSee('Tutup Pengisian');

        $this->actingAs($this->admin)
            ->post(route('admin.events.akses-isian', [$this->event, 'afektif']), ['status' => 'tutup'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertFalse($this->event->fresh()->isianDibuka('afektif'));
        $this->assertTrue($this->event->fresh()->isianDibuka('angket'));

        $this->actingAs($this->admin)
            ->get(route('admin.events.show', $this->event))
            ->assertSee('Buka Pengisian');

        $this->actingAs($this->admin)
            ->post(route('admin.events.akses-isian', [$this->event, 'afektif']), ['status' => 'aktif'])
            ->assertSessionHas('success');
        $this->assertTrue($this->event->fresh()->isianDibuka('afektif'));
    }

    public function test_afektif_ditutup_menolak_pengisian_peserta(): void
    {
        $sa = AfektifSubAspek::create(['event_id' => $this->event->id, 'nama_sub_aspek' => 'Disiplin', 'urutan' => 1, 'aktif' => true]);
        $butir = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Saya disiplin', 'is_positif' => true, 'urutan' => 1]);
        $this->event->update(['afektif_status' => 'tutup']);

        $this->actingAs($this->pesertaUser)
            ->get(route('portal.afektif.index', $this->event))
            ->assertOk()
            ->assertSee('data-isian-ditutup', false)
            ->assertDontSee(route('portal.afektif.save', $this->event), false);

        $this->actingAs($this->pesertaUser)
            ->post(route('portal.afektif.save', $this->event), ['jawaban' => [$butir->id => 4]])
            ->assertSessionHas('warning');
        $this->assertSame(0, AfektifJawaban::count());

        $this->actingAs($this->pesertaUser)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('data-isian-ditutup-chip', false);

        $this->event->update(['afektif_status' => 'aktif']);
        $this->actingAs($this->pesertaUser)
            ->post(route('portal.afektif.save', $this->event), ['jawaban' => [$butir->id => 4]])
            ->assertSessionHas('success');
        $this->assertSame(1, AfektifJawaban::count());
    }

    public function test_angket_ditutup_menolak_pengisian_peserta(): void
    {
        $item = AngketItem::create(['event_id' => $this->event->id, 'kategori' => 'A', 'teks_item' => 'Materi jelas', 'urutan' => 1]);
        $this->event->update(['angket_status' => 'tutup']);

        $this->actingAs($this->pesertaUser)
            ->get(route('portal.angket.index', $this->event))
            ->assertOk()
            ->assertSee('data-isian-ditutup', false);

        $this->actingAs($this->pesertaUser)
            ->post(route('portal.angket.save', $this->event), ['jawaban' => [$item->id => 5]])
            ->assertSessionHas('warning');
        $this->assertSame(0, AngketJawaban::count());
    }

    public function test_hanya_admin_dan_jenis_valid(): void
    {
        $fasilitator = User::factory()->create(['role' => 'fasilitator', 'status' => 'aktif']);
        $this->actingAs($fasilitator)
            ->post(route('admin.events.akses-isian', [$this->event, 'angket']), ['status' => 'tutup'])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post('/admin/events/'.$this->event->id.'/akses-isian/pretest', ['status' => 'tutup'])
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->post(route('admin.events.akses-isian', [$this->event, 'angket']), ['status' => 'hapus'])
            ->assertSessionHasErrors('status');

        $this->assertTrue($this->event->fresh()->isianDibuka('angket'));
    }
}
