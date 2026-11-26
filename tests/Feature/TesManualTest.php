<?php

namespace Tests\Feature;

use App\Exports\LaporanEventExport;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\QrService;
use App\Services\TesService;
use App\Support\LembarJawabanLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TesManualTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'BA Tes Kertas',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);

        // Kunci posttest: A, C, B, D
        foreach (['A', 'C', 'B', 'D'] as $i => $kunci) {
            $soal = Soal::create([
                'event_id' => $this->event->id,
                'tipe' => 'posttest',
                'teks_soal' => 'Soal '.($i + 1),
                'urutan' => $i + 1,
            ]);
            foreach (['A', 'B', 'C', 'D'] as $label) {
                PilihanJawaban::create([
                    'soal_id' => $soal->id,
                    'label' => $label,
                    'teks' => 'Pilihan '.$label,
                    'is_benar' => $label === $kunci,
                ]);
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

    public function test_admin_inputs_paper_answers_and_system_grades_them(): void
    {
        $anggota = $this->peserta();

        $this->actingAs($this->admin)
            ->post(route('admin.events.tes-manual.store', [$this->event, 'posttest']), [
                'jawaban' => [$anggota->id => 'a c b a'],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertEquals(75.0, (float) PenilaianAkhir::where('anggota_id', $anggota->id)->value('nilai_posttest'));
        $this->assertSame(4, JawabanPeserta::where('anggota_id', $anggota->id)->count());

        $mulai = PesertaTesMulai::where('anggota_id', $anggota->id)->where('tipe', 'posttest')->first();
        $this->assertSame('manual', $mulai->sumber);
        $this->assertSame($this->admin->id, $mulai->diinput_oleh);
        $this->assertNotNull($mulai->waktu_selesai);
    }

    public function test_dash_means_blank_answer(): void
    {
        $anggota = $this->peserta();

        $skor = app(TesService::class)->simpanJawabanManual($this->event, $anggota->id, 'posttest', 'A--D', $this->admin->id);

        $this->assertEquals(50.0, $skor);
        $this->assertSame(2, JawabanPeserta::where('anggota_id', $anggota->id)->whereNull('pilihan_id')->count());
    }

    public function test_wrong_length_or_invalid_letter_is_rejected_without_saving(): void
    {
        $a = $this->peserta();
        $b = $this->peserta();

        $this->actingAs($this->admin)
            ->from(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->post(route('admin.events.tes-manual.store', [$this->event, 'posttest']), [
                'jawaban' => [$a->id => 'ACB', $b->id => 'ACBE'],
            ])
            ->assertSessionHasErrors(["jawaban.{$a->id}", "jawaban.{$b->id}"]);

        $this->assertSame(0, JawabanPeserta::count());
        $this->assertSame(0, PesertaTesMulai::count());
    }

    public function test_online_submission_cannot_be_overwritten_by_manual_input(): void
    {
        $anggota = $this->peserta();
        PesertaTesMulai::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.events.tes-manual.store', [$this->event, 'posttest']), [
                'jawaban' => [$anggota->id => 'ACBD'],
            ])
            ->assertSessionHasErrors("jawaban.{$anggota->id}");

        $this->assertSame('online', PesertaTesMulai::first()->sumber);
        $this->assertSame(0, JawabanPeserta::count());
    }

    public function test_manual_entry_blocks_online_test_in_portal(): void
    {
        $anggota = $this->peserta();
        SesiTes::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);
        app(TesService::class)->simpanJawabanManual($this->event, $anggota->id, 'posttest', 'ACBD', $this->admin->id);

        $this->actingAs($anggota->user)
            ->post(route('portal.tes.submit', [$this->event, 'posttest']), ['answers' => []])
            ->assertRedirect(route('portal.tes.index'));

        $this->assertEquals(100.0, (float) PenilaianAkhir::where('anggota_id', $anggota->id)->value('nilai_posttest'));
    }

    public function test_manual_posttest_with_zero_score_still_counts_as_completed_for_graduation(): void
    {
        $anggota = $this->peserta();
        app(TesService::class)->simpanJawabanManual($this->event, $anggota->id, 'posttest', 'DDDA', $this->admin->id);

        $this->assertTrue(app(TesService::class)->hasSubmitted($this->event, $anggota->id, 'posttest'));
    }

    public function test_only_admin_daerah_can_access_manual_input(): void
    {
        $fasilitator = User::factory()->create(['role' => 'fasilitator', 'status' => 'aktif']);

        $this->actingAs($fasilitator)
            ->get(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->assertOk()
            ->assertSee('ACBD');
    }

    public function test_admin_can_delete_manual_entry_and_participant_can_take_test_again(): void
    {
        $anggota = $this->peserta();
        app(TesService::class)->simpanJawabanManual($this->event, $anggota->id, 'posttest', 'ACBD', $this->admin->id);

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->assertOk()
            ->assertSee('form="hapus-'.$anggota->id.'"', false)
            ->assertSee('Cetak Lembar Jawaban')
            ->assertSee('Pindai Foto Lembar Jawaban')
            ->assertSee('Scan Langsung dengan Kamera')
            ->assertSee('id="omr-video"', false)
            ->assertSee('aset/js/omr-lembar.js', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.events.tes-manual.destroy', [$this->event, 'posttest', $anggota->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, JawabanPeserta::count());
        $this->assertSame(0, PesertaTesMulai::count());
        $this->assertEquals(0.0, (float) PenilaianAkhir::where('anggota_id', $anggota->id)->value('nilai_posttest'));
        $this->assertFalse(app(TesService::class)->hasSubmitted($this->event, $anggota->id, 'posttest'));
    }

    public function test_delete_does_not_touch_online_submission(): void
    {
        $anggota = $this->peserta();
        PesertaTesMulai::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now(),
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.events.tes-manual.destroy', [$this->event, 'posttest', $anggota->id]))
            ->assertSessionHas('error');

        $this->assertSame(1, PesertaTesMulai::count());
    }

    public function test_admin_can_print_question_and_answer_sheets(): void
    {
        $this->peserta();

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.cetak', [$this->event, 'posttest', 'jawaban']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.cetak', [$this->event, 'posttest', 'soal']))
            ->assertOk()
            ->assertSee('Cetak / Simpan PDF')
            ->assertSee('Soal 4');
    }

    public function test_question_sheet_renders_arabic_for_browser_and_strips_duplicate_numbering(): void
    {
        $soal = Soal::where('event_id', $this->event->id)->where('tipe', 'posttest')->orderBy('urutan')->first();
        $soal->update(['teks_soal' => '23. Ayat manakah yang menganjurkan berorganisasi? وَاعْتَصِمُوا بِحَبْلِ اللَّهِ']);
        $soal->pilihan()->where('label', 'D')->update(['teks' => 'd. Tabligh']);

        $html = $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.cetak', [$this->event, 'posttest', 'soal']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<span class="ar" dir="rtl" lang="ar">وَاعْتَصِمُوا بِحَبْلِ اللَّهِ</span>', $html);
        $this->assertStringContainsString('family=Amiri', $html);
        $this->assertStringNotContainsString('23. Ayat', $html);
        $this->assertStringContainsString('>Ayat manakah', $html);
        $this->assertStringNotContainsString('d. Tabligh', $html);
        $this->assertStringContainsString('<span dir="auto">Tabligh</span>', $html);
    }

    public function test_answer_sheet_lists_participants_and_question_sheet_hides_key(): void
    {
        $anggota = $this->peserta();
        $soal = app(TesService::class)->soalBerurutan($this->event, 'posttest');
        $data = ['event' => $this->event, 'tipe' => 'posttest', 'label' => 'Posttest', 'soal' => $soal, 'layout' => LembarJawabanLayout::build(4, ['A', 'B', 'C', 'D'])];

        $jawaban = view('admin.events.tes-manual-cetak', $data + ['peserta' => $this->event->pesertaAktif()->with('anggota')->get()])->render();
        $this->assertStringContainsString(e($anggota->nama_lengkap), $jawaban);
        $this->assertStringContainsString('NO. PESERTA', $jawaban);
        $this->assertStringContainsString('>01</div>', $jawaban);
        $this->assertStringContainsString('MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI', $jawaban);

        $lembarSoal = view('admin.events.tes-manual-soal', $data)->render();
        $this->assertStringContainsString('Soal 4', $lembarSoal);
        $this->assertStringNotContainsString('is_benar', $lembarSoal);
        $this->assertStringNotContainsString('Kunci', $lembarSoal);
    }

    public function test_reports_mark_paper_scores(): void
    {
        $anggota = $this->peserta();
        app(TesService::class)->simpanJawabanManual($this->event, $anggota->id, 'posttest', 'ACBD', $this->admin->id);

        $html = (new LaporanEventExport($this->event->id))->view()->render();
        $this->assertStringContainsString('background-color: #fef3c7;', $html);
        $this->assertStringContainsString('lembar jawaban kertas', $html);

        $this->actingAs($this->admin)
            ->get(route('admin.events.report', $this->event))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_paper_and_online_participants_with_same_answers_get_identical_results(): void
    {
        foreach (['B', 'A', 'D', 'C'] as $i => $kunci) {
            $soal = Soal::create(['event_id' => $this->event->id, 'tipe' => 'pretest', 'teks_soal' => 'Pre '.($i + 1), 'urutan' => $i + 1]);
            foreach (['A', 'B', 'C', 'D'] as $label) {
                PilihanJawaban::create(['soal_id' => $soal->id, 'label' => $label, 'teks' => $label, 'is_benar' => $label === $kunci]);
            }
        }
        SesiTes::create(['event_id' => $this->event->id, 'tipe' => 'pretest', 'status' => 'aktif', 'durasi_menit' => 30]);
        SesiTes::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);

        $online = $this->peserta();
        $kertas = $this->peserta();
        $jawaban = ['pretest' => 'BA-A', 'posttest' => 'ACBA'];
        $tes = app(TesService::class);

        foreach ($jawaban as $tipe => $huruf) {
            $answers = $tes->petakanJawabanManual($tes->soalBerurutan($this->event, $tipe), $huruf);
            $this->actingAs($online->user)->get(route('portal.tes.show', [$this->event, $tipe]))->assertOk();
            $this->actingAs($online->user)
                ->post(route('portal.tes.submit', [$this->event, $tipe]), ['answers' => collect($answers)->map(fn ($pilihan, $soal) => ['soal_id' => $soal, 'pilihan_id' => $pilihan])->values()->all()])
                ->assertRedirect();

            $this->actingAs($this->admin)
                ->post(route('admin.events.tes-manual.store', [$this->event, $tipe]), ['jawaban' => [$kertas->id => $huruf]])
                ->assertSessionHasNoErrors();
        }

        $this->actingAs($this->admin)->get(route('admin.events.ranking', $this->event))->assertOk()->assertSee('kertas');

        $a = PenilaianAkhir::where('anggota_id', $online->id)->first();
        $b = PenilaianAkhir::where('anggota_id', $kertas->id)->first();
        $this->assertEquals(50.0, (float) $a->nilai_pretest);
        $this->assertEquals(75.0, (float) $a->nilai_posttest);
        foreach (['nilai_pretest', 'nilai_posttest', 'nilai_akhir', 'status_kelulusan'] as $kolom) {
            $this->assertEquals($a->$kolom, $b->$kolom, $kolom);
        }
        $this->assertSame(['online', 'manual'], [
            PesertaTesMulai::where('anggota_id', $online->id)->value('sumber'),
            PesertaTesMulai::where('anggota_id', $kertas->id)->value('sumber'),
        ]);
    }

    public function test_abandoned_online_attempt_can_be_replaced_by_paper_entry(): void
    {
        $anggota = $this->peserta();
        SesiTes::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);
        $this->actingAs($anggota->user)->get(route('portal.tes.show', [$this->event, 'posttest']))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.events.tes-manual.store', [$this->event, 'posttest']), ['jawaban' => [$anggota->id => 'ACBD']])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, PesertaTesMulai::count());
        $this->assertSame('manual', PesertaTesMulai::first()->sumber);
        $this->assertEquals(100.0, (float) PenilaianAkhir::where('anggota_id', $anggota->id)->value('nilai_posttest'));
    }

    public function test_double_mark_from_scan_gets_clear_error_message(): void
    {
        $anggota = $this->peserta();

        $this->actingAs($this->admin)
            ->from(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->post(route('admin.events.tes-manual.store', [$this->event, 'posttest']), ['jawaban' => [$anggota->id => 'AC?D']])
            ->assertSessionHasErrors(["jawaban.{$anggota->id}" => 'Soal nomor 3 terbaca ganda (?). Cek kertas lalu ganti dengan huruf yang benar atau "-".']);
    }

    public function test_page_flags_in_progress_online_attempt_and_hides_save_when_all_online(): void
    {
        $anggota = $this->peserta();
        PesertaTesMulai::create(['event_id' => $this->event->id, 'anggota_id' => $anggota->id, 'tipe' => 'posttest', 'waktu_mulai' => now()]);

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->assertSee('Sedang online')
            ->assertSee('Simpan &amp; Hitung Nilai', false);

        PesertaTesMulai::query()->update(['waktu_selesai' => now()]);

        $this->actingAs($this->admin)
            ->get(route('admin.events.tes-manual.index', [$this->event, 'posttest']))
            ->assertSee('Semua peserta sudah mengerjakan online.')
            ->assertDontSee('Simpan &amp; Hitung Nilai', false);
    }
}
