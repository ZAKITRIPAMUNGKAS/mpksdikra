<?php

namespace Tests\Feature;

use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfektifCloneTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private function event(string $nama): Event
    {
        return Event::create([
            'nama_event' => $nama,
            'tanggal_mulai' => '2026-11-27',
            'tanggal_selesai' => '2026-11-28',
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);
    }

    private function instrumen(Event $event, string $sub, array $butir): AfektifSubAspek
    {
        $sa = AfektifSubAspek::create(['event_id' => $event->id, 'nama_sub_aspek' => $sub, 'urutan' => 1, 'aktif' => true]);
        foreach ($butir as $i => [$teks, $positif]) {
            AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => $teks, 'is_positif' => $positif, 'urutan' => $i + 1]);
        }

        return $sa;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    public function test_admin_copies_attitude_instrument_from_another_event(): void
    {
        $sumber = $this->event('BA Sumber');
        $this->instrumen($sumber, 'Kedisiplinan', [['Datang tepat waktu', true], ['Sering terlambat', false]]);
        $target = $this->event('BA PCM Baru');

        $this->actingAs($this->admin)
            ->post(route('admin.events.afektif.clone', $target), ['source_event_id' => $sumber->id])
            ->assertRedirect()
            ->assertSessionHas('success');

        $sa = AfektifSubAspek::where('event_id', $target->id)->with('butir')->sole();
        $this->assertSame('Kedisiplinan', $sa->nama_sub_aspek);
        $this->assertSame(['Datang tepat waktu', 'Sering terlambat'], $sa->butir->pluck('teks_butir')->all());
        $this->assertSame([true, false], $sa->butir->pluck('is_positif')->all());
        $this->assertSame(2, AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $sumber->id))->count());
    }

    public function test_copying_twice_does_not_duplicate_items(): void
    {
        $sumber = $this->event('BA Sumber');
        $this->instrumen($sumber, 'Kedisiplinan', [['Datang tepat waktu', true]]);
        $target = $this->event('BA PCM Baru');
        $this->instrumen($target, 'kedisiplinan', [['Datang tepat waktu', true]]);

        $this->actingAs($this->admin)
            ->post(route('admin.events.afektif.clone', $target), ['source_event_id' => $sumber->id])
            ->assertSessionHas('warning');

        $this->assertSame(1, AfektifSubAspek::where('event_id', $target->id)->count());
        $this->assertSame(1, AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $target->id))->count());
    }

    public function test_cannot_copy_from_same_event_and_page_lists_sources(): void
    {
        $sumber = $this->event('BA Sumber Lama');
        $this->instrumen($sumber, 'Integritas', [['Jujur', true], ['Amanah', true]]);
        $target = $this->event('BA PCM Baru');

        $this->actingAs($this->admin)
            ->post(route('admin.events.afektif.clone', $target), ['source_event_id' => $target->id])
            ->assertSessionHasErrors('source_event_id');

        $this->actingAs($this->admin)
            ->get(route('admin.events.afektif', $target))
            ->assertOk()
            ->assertSee('BA Sumber Lama (2 butir)');
    }

    public function test_fasilitator_cannot_copy(): void
    {
        $sumber = $this->event('BA Sumber');
        $this->instrumen($sumber, 'Integritas', [['Jujur', true]]);
        $target = $this->event('BA PCM Baru');
        $fasilitator = User::factory()->create(['role' => 'fasilitator', 'status' => 'aktif']);

        $this->actingAs($fasilitator)
            ->post(route('admin.events.afektif.clone', $target), ['source_event_id' => $sumber->id])
            ->assertForbidden();
    }

    public function test_satisfaction_survey_does_not_affect_final_score(): void
    {
        $event = $this->event('BA Angket');
        $anggota = Anggota::factory()->create(['user_id' => User::factory()->create(['role' => 'anggota'])->id]);
        EventPeserta::create(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'qr_token' => app(QrService::class)->generateToken(), 'status_aktif' => true]);

        app(NilaiService::class)->hitungUlang($event);
        $sebelum = PenilaianAkhir::where('anggota_id', $anggota->id)->first()->only(['nilai_afektif', 'nilai_akhir', 'status_kelulusan']);

        $item = AngketItem::create(['event_id' => $event->id, 'kategori' => 'materi', 'teks_item' => 'Materi bermanfaat', 'urutan' => 1]);
        AngketJawaban::create(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'item_id' => $item->id, 'skor' => 4]);

        app(NilaiService::class)->hitungUlang($event);
        $sesudah = PenilaianAkhir::where('anggota_id', $anggota->id)->first()->only(['nilai_afektif', 'nilai_akhir', 'status_kelulusan']);

        $this->assertEquals($sebelum, $sesudah);
    }
}
