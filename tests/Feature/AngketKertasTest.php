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
use App\Models\InputKertas;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AngketKertasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'BA Angket Kertas',
            'tanggal_mulai' => '2026-11-28',
            'tanggal_selesai' => '2026-11-29',
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);

        // Afektif: 2 sub-aspek, butir ke-3 negatif. Sub-aspek nonaktif tidak ikut.
        $sa1 = AfektifSubAspek::create(['event_id' => $this->event->id, 'nama_sub_aspek' => 'Kedisiplinan', 'urutan' => 1, 'aktif' => true]);
        $sa2 = AfektifSubAspek::create(['event_id' => $this->event->id, 'nama_sub_aspek' => 'Integritas', 'urutan' => 2, 'aktif' => true]);
        $mati = AfektifSubAspek::create(['event_id' => $this->event->id, 'nama_sub_aspek' => 'Nonaktif', 'urutan' => 3, 'aktif' => false]);
        AfektifButir::create(['sub_aspek_id' => $sa1->id, 'teks_butir' => 'Datang tepat waktu', 'is_positif' => true, 'urutan' => 1]);
        AfektifButir::create(['sub_aspek_id' => $sa1->id, 'teks_butir' => 'Mengikuti tata tertib', 'is_positif' => true, 'urutan' => 2]);
        AfektifButir::create(['sub_aspek_id' => $sa2->id, 'teks_butir' => 'Sering menunda amanah', 'is_positif' => false, 'urutan' => 1]);
        AfektifButir::create(['sub_aspek_id' => $mati->id, 'teks_butir' => 'Tidak ikut', 'is_positif' => true, 'urutan' => 1]);

        foreach (['A' => ['Materi relevan', 'Waktu cukup'], 'B' => ['Narasumber menguasai materi']] as $kategori => $list) {
            foreach ($list as $i => $teks) {
                AngketItem::create(['event_id' => $this->event->id, 'kategori' => $kategori, 'teks_item' => $teks, 'urutan' => $i + 1]);
            }
        }
    }

    private function peserta(): Anggota
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);
        EventPeserta::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        return $anggota;
    }

    private function simpan(string $jenis, array $jawaban)
    {
        return $this->actingAs($this->admin)
            ->from(route('admin.events.angket-kertas.index', [$this->event, $jenis]))
            ->post(route('admin.events.angket-kertas.store', [$this->event, $jenis]), ['jawaban' => $jawaban]);
    }

    public function test_paper_afektif_is_saved_and_scored_like_portal(): void
    {
        $a = $this->peserta();

        // 4 + 3 + (5-1=4) = 11 dari 12 -> 91.67
        $this->simpan('afektif', [$a->id => '4 3 1'])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame(3, AfektifJawaban::where('anggota_id', $a->id)->count());
        $this->assertEqualsWithDelta(91.67, (float) PenilaianAkhir::where('anggota_id', $a->id)->value('nilai_afektif'), 0.01);
        $tanda = InputKertas::where('anggota_id', $a->id)->sole();
        $this->assertSame('afektif', $tanda->instrumen);
        $this->assertSame($this->admin->id, $tanda->diinput_oleh);
    }

    public function test_paper_angket_saved_in_category_order_and_dash_is_blank(): void
    {
        $a = $this->peserta();

        $this->simpan('angket', [$a->id => '5-4'])->assertSessionHasNoErrors();

        $skor = AngketJawaban::where('anggota_id', $a->id)->join('angket_item', 'angket_item.id', '=', 'angket_jawaban.item_id')
            ->pluck('angket_jawaban.skor', 'angket_item.teks_item')->all();
        $this->assertSame(['Materi relevan' => 5, 'Narasumber menguasai materi' => 4], $skor);
    }

    public function test_out_of_scale_and_wrong_length_are_rejected_without_saving(): void
    {
        $a = $this->peserta();
        $b = $this->peserta();

        $this->simpan('afektif', [$a->id => '455', $b->id => '44'])
            ->assertSessionHasErrors(["jawaban.{$a->id}", "jawaban.{$b->id}"]);

        $this->assertSame(0, AfektifJawaban::count());
        $this->assertSame(0, InputKertas::count());
    }

    public function test_online_answers_are_locked_and_portal_cannot_overwrite_paper(): void
    {
        $online = $this->peserta();
        $butir = AfektifButir::where('teks_butir', 'Datang tepat waktu')->first();
        AfektifJawaban::create(['event_id' => $this->event->id, 'anggota_id' => $online->id, 'butir_id' => $butir->id, 'skor' => 2]);

        $this->simpan('afektif', [$online->id => '444'])->assertSessionHasErrors("jawaban.{$online->id}");
        $this->assertSame(2, AfektifJawaban::where('anggota_id', $online->id)->value('skor'));

        $kertas = $this->peserta();
        $this->simpan('angket', [$kertas->id => '555'])->assertSessionHasNoErrors();

        $item = AngketItem::first();
        $this->actingAs($kertas->user)
            ->post(route('portal.angket.save', $this->event), ['jawaban' => [$item->id => 1]])
            ->assertSessionHas('warning');
        $this->assertSame(5, AngketJawaban::where('anggota_id', $kertas->id)->where('item_id', $item->id)->value('skor'));

        $this->actingAs($kertas->user)
            ->get(route('portal.angket.index', $this->event))
            ->assertSee('sudah diisikan panitia dari');
    }

    public function test_saved_paper_answers_reload_and_can_be_deleted(): void
    {
        $a = $this->peserta();
        $this->simpan('afektif', [$a->id => '4-1']);

        $this->actingAs($this->admin)
            ->get(route('admin.events.angket-kertas.index', [$this->event, 'afektif']))
            ->assertOk()
            ->assertSee('4-1')
            ->assertSee('Kertas')
            ->assertSee('Cetak Lembar Pernyataan')
            ->assertSee('Scan Langsung dengan Kamera');

        $this->actingAs($this->admin)
            ->delete(route('admin.events.angket-kertas.destroy', [$this->event, 'afektif', $a->id]))
            ->assertSessionHas('success');

        $this->assertSame(0, AfektifJawaban::count());
        $this->assertSame(0, InputKertas::count());
        $this->assertEquals(0.0, (float) PenilaianAkhir::where('anggota_id', $a->id)->value('nilai_afektif'));
    }

    public function test_print_sheets(): void
    {
        $this->peserta();

        $this->actingAs($this->admin)
            ->get(route('admin.events.angket-kertas.cetak', [$this->event, 'afektif', 'pernyataan']))
            ->assertOk()
            ->assertSee('Kedisiplinan')
            ->assertSee('Sering menunda amanah')
            ->assertDontSee('Tidak ikut')
            ->assertSee('Sangat Tidak Setuju');

        foreach (['afektif', 'angket'] as $jenis) {
            $this->actingAs($this->admin)
                ->get(route('admin.events.angket-kertas.cetak', [$this->event, $jenis, 'jawaban']))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_only_admin_daerah_and_valid_types(): void
    {
        $fasilitator = User::factory()->create(['role' => 'fasilitator', 'status' => 'aktif']);

        $this->actingAs($fasilitator)
            ->get(route('admin.events.angket-kertas.index', [$this->event, 'angket']))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get('/admin/events/'.$this->event->id.'/angket-kertas/pretest')
            ->assertNotFound();
    }

    public function test_portal_afektif_is_locked_after_paper_input(): void
    {
        $a = $this->peserta();
        $this->simpan('afektif', [$a->id => '444'])->assertSessionHasNoErrors();
        $butir = AfektifButir::where('teks_butir', 'Datang tepat waktu')->first();

        $this->actingAs($a->user)
            ->get(route('portal.afektif.index', $this->event))
            ->assertOk()
            ->assertSee('sudah diisikan panitia dari')
            ->assertDontSee(route('portal.afektif.save', $this->event));

        $this->actingAs($a->user)
            ->post(route('portal.afektif.save', $this->event), ['jawaban' => [$butir->id => 1]])
            ->assertSessionHas('warning');
        $this->assertSame(4, AfektifJawaban::where('anggota_id', $a->id)->where('butir_id', $butir->id)->value('skor'));
    }

    public function test_portal_still_works_for_participants_without_paper_input(): void
    {
        $a = $this->peserta();
        $butirAktif = AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $this->event->id)->where('aktif', true))->pluck('id');

        $this->actingAs($a->user)
            ->get(route('portal.afektif.index', $this->event))
            ->assertOk()
            ->assertDontSee('sudah diisikan panitia dari')
            ->assertSee(route('portal.afektif.save', $this->event));

        $this->actingAs($a->user)
            ->post(route('portal.afektif.save', $this->event), ['jawaban' => $butirAktif->mapWithKeys(fn ($id) => [$id => 3])->all()])
            ->assertSessionHas('success');
    }

    public function test_double_mark_inactive_participant_and_delete_link(): void
    {
        $a = $this->peserta();
        $nonaktif = $this->peserta();
        EventPeserta::where('anggota_id', $nonaktif->id)->update(['status_aktif' => false]);

        $this->simpan('afektif', [$a->id => '4?1'])
            ->assertSessionHasErrors(["jawaban.{$a->id}" => 'Butir nomor 2 terbaca ganda (?). Cek kertas lalu ganti dengan angka yang benar atau "-".']);
        $this->simpan('afektif', [$nonaktif->id => '444'])->assertSessionHasErrors("jawaban.{$nonaktif->id}");

        $this->simpan('angket', [$a->id => '345'])->assertSessionHasNoErrors();
        $this->actingAs($this->admin)
            ->get(route('admin.events.angket-kertas.index', [$this->event, 'angket']))
            ->assertSee('form="hapus-'.$a->id.'"', false)
            ->assertSee(route('admin.events.angket-kertas.destroy', [$this->event, 'angket', $a->id]), false);
    }

    public function test_item_added_after_paper_input_does_not_force_resave(): void
    {
        $a = $this->peserta();
        $this->simpan('angket', [$a->id => '543'])->assertSessionHasNoErrors();
        AngketItem::create(['event_id' => $this->event->id, 'kategori' => 'C', 'teks_item' => 'Konsumsi baik', 'urutan' => 1]);

        $this->actingAs($this->admin)
            ->get(route('admin.events.angket-kertas.index', [$this->event, 'angket']))
            ->assertSee('543-');

        $this->simpan('angket', [$a->id => '543-'])->assertSessionHas('warning');
        $this->simpan('angket', [$a->id => '5432'])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(4, AngketJawaban::where('anggota_id', $a->id)->count());
    }
}
