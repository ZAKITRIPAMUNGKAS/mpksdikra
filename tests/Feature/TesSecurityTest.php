<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PilihanJawaban;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TesSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_cannot_resubmit_completed_exam(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Security Test',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $soal = Soal::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'teks_soal' => 'Pertanyaan Kognitif',
            'urutan' => 1,
        ]);

        $benar = PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'A', 'teks' => 'Benar', 'is_benar' => true]);
        $salah = PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'B', 'teks' => 'Salah', 'is_benar' => false]);

        SesiTes::create(['event_id' => $event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        // First submit: answer correctly (score 100)
        $this->actingAs($user)
            ->post(route('portal.tes.submit', [$event, 'posttest']), [
                'answers' => [['soal_id' => $soal->id, 'pilihan_id' => $benar->id]],
            ])
            ->assertRedirect(route('portal.tes.index'))
            ->assertSessionHas('success');

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $this->assertSame('100.00', $p->nilai_posttest);

        // Second submit attempt (e.g. attempting to change answer to wrong one)
        $this->actingAs($user)
            ->post(route('portal.tes.submit', [$event, 'posttest']), [
                'answers' => [['soal_id' => $soal->id, 'pilihan_id' => $salah->id]],
            ])
            ->assertRedirect(route('portal.tes.index'))
            ->assertSessionHas('warning');

        // Score must remain 100
        $p->refresh();
        $this->assertSame('100.00', $p->nilai_posttest);
    }
}
