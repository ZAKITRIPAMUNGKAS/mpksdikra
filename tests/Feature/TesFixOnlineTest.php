<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\TesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class TesFixOnlineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Event $event;

    private EventPeserta $peserta;

    private SesiTes $sesi;

    private PesertaTesMulai $attempt;

    private array $answer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeSecond();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $this->user->id]);
        $this->event = Event::create([
            'nama_event' => 'BA Tes Online',
            'tanggal_mulai' => today(),
            'tanggal_selesai' => today(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
        $this->peserta = EventPeserta::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => fake()->uuid(),
        ]);
        $this->sesi = SesiTes::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 1]);
        $soal = Soal::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'teks_soal' => 'Soal uji', 'urutan' => 1]);
        $pilihan = PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'A', 'teks' => 'Benar', 'is_benar' => true]);
        $this->answer = ['soal_id' => $soal->id, 'pilihan_id' => $pilihan->id];
        $this->attempt = PesertaTesMulai::create([
            'event_id' => $this->event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now(),
        ]);
    }

    public function test_timer_submit_just_after_deadline_saves_and_grades_once(): void
    {
        $this->travel(62)->seconds();
        $this->actingAs($this->user)->post($this->url('submit'), ['timed_out' => '1', 'answers' => [$this->answer]])
            ->assertRedirect(route('portal.tes.index'))
            ->assertSessionHas('success', 'Tes selesai. Nilai: 100');

        $this->travel(1)->minute();
        $this->post($this->url('submit'), ['timed_out' => '1'])->assertSessionHas('warning', 'Tes ini sudah diserahkan sebelumnya.');
        $this->assertResult();
    }

    public function test_late_manual_submit_and_automatic_submit_past_grace_are_rejected(): void
    {
        $this->actingAs($this->user);
        $this->travel(62)->seconds();
        $this->post($this->url('submit'), ['answers' => [$this->answer]])->assertForbidden();

        $this->travel(2)->minutes();
        $this->post($this->url('submit'), ['answers' => [$this->answer]])->assertForbidden();
        $this->post($this->url('submit'), ['timed_out' => '1', 'answers' => [$this->answer]])->assertForbidden();
        $this->assertNull($this->attempt->fresh()->waktu_selesai);
        $this->assertDatabaseCount('jawaban_peserta', 0);
    }

    public function test_autosave_is_restored_and_an_expired_draft_is_finalized_on_reopen(): void
    {
        $this->actingAs($this->user)->postJson($this->url('autosave'), $this->answer)->assertNoContent();
        $this->assertNull($this->attempt->fresh()->waktu_selesai);
        $this->assertDatabaseHas('jawaban_peserta', $this->answer);
        $this->assertDatabaseCount('penilaian_akhir', 0);

        $this->get($this->url('show'))->assertOk()
            ->assertViewHas('savedAnswers', [$this->answer['soal_id'] => $this->answer['pilihan_id']]);

        $this->travel(2)->minutes();
        $this->get($this->url('show'))->assertRedirect(route('portal.tes.index'))
            ->assertSessionHas('success', 'Tes selesai. Nilai: 100');
        $this->assertResult();
    }

    public function test_autosave_is_rejected_for_inactive_participant_closed_session_and_late_attempt(): void
    {
        $this->actingAs($this->user);
        $this->peserta->update(['status_aktif' => false]);
        $this->postJson($this->url('autosave'), $this->answer)->assertForbidden();
        $this->peserta->update(['status_aktif' => true]);

        $this->sesi->update(['status' => 'tutup']);
        $this->postJson($this->url('autosave'), $this->answer)->assertForbidden();
        $this->sesi->update(['status' => 'aktif']);

        $this->travel(2)->minutes();
        $this->postJson($this->url('autosave'), $this->answer)->assertForbidden();
        $this->assertDatabaseCount('jawaban_peserta', 0);
    }

    public function test_autosave_rejects_a_choice_from_a_different_question(): void
    {
        $soal = Soal::create(['event_id' => $this->event->id, 'tipe' => 'posttest', 'teks_soal' => 'Soal lain', 'urutan' => 2]);
        $this->actingAs($this->user)->postJson($this->url('autosave'), [
            'soal_id' => $soal->id,
            'pilihan_id' => $this->answer['pilihan_id'],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('jawaban_peserta', 0);
    }

    public function test_repeated_submissions_grade_once_and_autosave_cannot_overwrite_final_answers(): void
    {
        $tes = Mockery::mock(TesService::class)->makePartial();
        $tes->shouldReceive('grade')->once()->passthru();
        $this->instance(TesService::class, $tes);

        $this->actingAs($this->user)->post($this->url('submit'), ['answers' => [$this->answer]])
            ->assertSessionHas('success', 'Tes selesai. Nilai: 100');

        for ($i = 0; $i < 4; $i++) {
            $this->post($this->url('submit'), ['answers' => []])
                ->assertSessionHas('warning', 'Tes ini sudah diserahkan sebelumnya.')
                ->assertSessionMissing('success');
        }

        $this->postJson($this->url('autosave'), $this->answer)->assertForbidden();
        $this->assertResult();
    }

    public function test_failed_grading_rolls_back_submission_and_keeps_attempt_open(): void
    {
        $tes = Mockery::mock(TesService::class)->makePartial();
        $tes->shouldReceive('grade')->once()->andThrow(new RuntimeException('Penilaian gagal.'));
        $this->instance(TesService::class, $tes);

        $this->actingAs($this->user)->post($this->url('submit'), ['answers' => [$this->answer]])
            ->assertInternalServerError();

        $this->assertNull($this->attempt->fresh()->waktu_selesai);
        $this->assertDatabaseCount('jawaban_peserta', 0);
    }

    public function test_portal_root_redirects_guests_to_login_and_members_to_dashboard(): void
    {
        $this->get('/portal')->assertRedirect(route('login'));
        $this->actingAs($this->user)->get('/portal')->assertRedirect(route('portal.dashboard'));
    }

    private function url(string $action): string
    {
        return route('portal.tes.'.$action, [$this->event, 'posttest']);
    }

    private function assertResult(): void
    {
        $this->assertDatabaseHas('jawaban_peserta', $this->answer);
        $this->assertDatabaseCount('jawaban_peserta', 1);
        $this->assertNotNull($this->attempt->fresh()->waktu_selesai);
        $this->assertDatabaseHas('penilaian_akhir', [
            'event_id' => $this->event->id,
            'anggota_id' => $this->peserta->anggota_id,
            'nilai_posttest' => 100,
        ]);
    }
}
