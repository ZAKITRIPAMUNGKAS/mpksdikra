<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PilihanJawaban;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use App\Services\TesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function makeEvent(User $admin): Event
    {
        return Event::create([
            'nama_event' => 'BA Tes Kognitif',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
    }

    private function makeQuestion(Event $event, string $tipe, bool $benarDiA = true): Soal
    {
        $soal = Soal::create([
            'event_id' => $event->id,
            'tipe' => $tipe,
            'teks_soal' => 'Soal contoh '.$tipe,
            'urutan' => 1,
        ]);

        PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'A', 'teks' => 'Benar', 'is_benar' => $benarDiA]);
        PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'B', 'teks' => 'Salah', 'is_benar' => ! $benarDiA]);

        return $soal;
    }

    public function test_participant_submits_posttest_and_gets_score(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $soal = $this->makeQuestion($event, 'posttest');
        $benar = $soal->pilihan()->where('is_benar', true)->first();

        SesiTes::create(['event_id' => $event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        $this->actingAs($anggotaUser)
            ->get(route('portal.tes.show', [$event, 'posttest']))
            ->assertOk();

        $this->actingAs($anggotaUser)
            ->post(route('portal.tes.submit', [$event, 'posttest']), [
                'answers' => [['soal_id' => $soal->id, 'pilihan_id' => $benar->id]],
            ])
            ->assertRedirect(route('portal.tes.index'));

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $this->assertSame('100.00', $p->nilai_posttest);
    }

    public function test_closed_session_shows_closed_view(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $this->makeQuestion($event, 'pretest');

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        $this->actingAs($anggotaUser)
            ->get(route('portal.tes.show', [$event, 'pretest']))
            ->assertOk()
            ->assertSee('Belum Dibuka');
    }

    public function test_non_participant_forbidden(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        SesiTes::create(['event_id' => $event->id, 'tipe' => 'posttest', 'status' => 'aktif']);

        $stranger = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        Anggota::factory()->create(['user_id' => $stranger->id]);

        $this->actingAs($stranger)
            ->get(route('portal.tes.show', [$event, 'posttest']))
            ->assertForbidden();
    }

    public function test_final_score_and_ranking_order(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent($admin);
        $tes = app(TesService::class);

        $psiko = PsikomotorTemplate::create(['event_id' => $event->id, 'jenis' => 'ibadah', 'nama_aspek' => 'Sholat', 'skor_maks' => 4]);

        $pesertaScores = [
            'Rendah' => ['posttest' => 40, 'psiko' => 2],
            'Tinggi' => ['posttest' => 90, 'psiko' => 4],
            'Sedang' => ['posttest' => 65, 'psiko' => 3],
        ];

        foreach ($pesertaScores as $nama => $cfg) {
            $u = User::factory()->create(['role' => 'anggota', 'status' => 'aktif', 'name' => $nama]);
            $a = Anggota::factory()->create(['user_id' => $u->id, 'nama_lengkap' => $nama]);
            EventPeserta::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'qr_token' => app(QrService::class)->generateToken(),
            ]);

            PenilaianAkhir::updateOrCreate(
                ['event_id' => $event->id, 'anggota_id' => $a->id],
                ['nilai_posttest' => $cfg['posttest']]
            );
            PsikomotorNilai::create([
                'event_id' => $event->id,
                'anggota_id' => $a->id,
                'template_id' => $psiko->id,
                'skor' => $cfg['psiko'],
                'dinilai_oleh' => $admin->id,
            ]);
        }

        app(NilaiService::class)->hitungUlang($event);

        $ranked = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->orderBy('ranking')
            ->get();

        $this->assertSame(
            ['Tinggi', 'Sedang', 'Rendah'],
            $ranked->pluck('anggota.nama_lengkap')->all()
        );
        $this->assertSame([1, 2, 3], $ranked->pluck('ranking')->all());
    }
}
