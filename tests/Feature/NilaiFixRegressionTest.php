<?php

namespace Tests\Feature;

use App\Exports\LaporanEventExport;
use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\InputKertas;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\User;
use App\Services\NilaiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery;
use PDOException;
use Tests\TestCase;

class NilaiFixRegressionTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'BA Regresi Nilai',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now(),
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_enroll_manual_memunculkan_dan_mengaktifkan_peserta_lama(): void
    {
        $peserta = $this->peserta('Peserta Kembali', false);
        $qrToken = $peserta->qr_token;

        $this->actingAs($this->admin)->get(route('admin.events.enroll', [$this->event, 'q' => 'Peserta Kembali']))
            ->assertOk()->assertSee('Peserta Kembali');
        $this->post(route('admin.events.enroll.store', $this->event), ['anggota_ids' => [$peserta->anggota_id]])
            ->assertRedirect(route('admin.events.show', $this->event));

        $this->assertSame($qrToken, $peserta->refresh()->qr_token);
        $this->assertTrue($peserta->status_aktif);
        $this->assertDatabaseCount('event_peserta', 1);
    }

    public function test_smart_enroll_aktifkan_peserta_lama_dan_undo_memulihkan_statusnya(): void
    {
        $peserta = $this->peserta('Peserta Kembali', false);
        $baru = $this->anggota('Peserta Baru');
        $qrToken = $peserta->qr_token;

        $result = $this->actingAs($this->admin)->postJson(route('admin.events.smart-enroll.execute', $this->event), [
            'anggota_ids' => [$peserta->anggota_id, $baru->id],
        ])->assertOk()->assertJsonPath('enrolled_count', 2);
        $this->assertTrue($peserta->refresh()->status_aktif);

        $this->postJson(route('admin.events.smart-enroll.undo', $this->event), [
            'undo_token' => $result->json('undo_token'),
        ])->assertOk()->assertJsonPath('removed_count', 2);

        $this->assertSame($qrToken, $peserta->refresh()->qr_token);
        $this->assertFalse($peserta->status_aktif);
        $this->assertDatabaseCount('event_peserta', 1);
    }

    public function test_ranking_dan_seluruh_laporan_hanya_menampilkan_peserta_aktif(): void
    {
        $aktif = [$this->peserta('Peserta Alfa'), $this->peserta('Peserta Bravo')];
        $nonaktif = $this->peserta('Peserta Nonaktif', false);
        $this->nilai($nonaktif)->update(['ranking' => 1, 'nilai_akhir' => 99]);
        $ids = array_map(fn ($peserta) => $peserta->anggota_id, $aktif);
        $assertRanking = static fn ($ranking) => $ranking->pluck('anggota_id')->all() === $ids
            && $ranking->pluck('ranking')->all() === [1, 2];

        $this->actingAs($this->admin)->get(route('admin.events.ranking', $this->event))
            ->assertOk()->assertViewHas('ranking', $assertRanking)->assertDontSee('Peserta Nonaktif');
        $this->assertTrue($assertRanking((new LaporanEventExport($this->event->id))->view()->getData()['ranking']));

        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->andReturnSelf();
        $pdf->shouldReceive('stream', 'download')->andReturn(response('PDF'));
        foreach (['report' => 'report-pdf', 'berita-acara' => 'berita-acara-pdf'] as $route => $view) {
            Pdf::shouldReceive('loadView')->once()->with(
                'admin.events.'.$view,
                Mockery::on(fn ($data) => $assertRanking($data['ranking']) && $data['totalPeserta'] === 2),
            )->andReturn($pdf);
            $this->get(route('admin.events.'.$route, $this->event))->assertOk();
        }
    }

    public function test_ranking_tetap_bisa_dibuka_saat_penulisan_nilai_konflik(): void
    {
        $peserta = $this->peserta('Peserta Aktif');
        $this->nilai($peserta);
        PenilaianAkhir::saving(fn () => throw new PDOException("Record has changed since last read in table 'penilaian_akhir'"));

        $this->actingAs($this->admin)->get(route('admin.events.ranking', $this->event))
            ->assertOk()->assertSee('Peserta Aktif');
    }

    public function test_konflik_ranking_dicoba_ulang_setelah_jawaban_dan_nilai_disimpan(): void
    {
        $peserta = $this->peserta('Peserta Aktif');
        $this->nilai($peserta);
        [$first, $second] = $this->butir();
        $attempts = 0;
        $level = DB::transactionLevel();
        PenilaianAkhir::updating(function ($nilai) use (&$attempts, $level) {
            if (! $nilai->isDirty('ranking')) {
                return;
            }

            $this->assertSame($level + 1, DB::transactionLevel());
            $this->assertDatabaseHas('penilaian_akhir', ['anggota_id' => $nilai->anggota_id, 'nilai_afektif' => 100]);
            $this->assertDatabaseCount('afektif_jawaban', 2);
            $attempts++;
            if ($attempts === 1) {
                throw new PDOException("Record has changed since last read in table 'penilaian_akhir'");
            }
            if ($attempts === 2) {
                throw new PDOException('Deadlock found when trying to get lock; try restarting transaction', 40001);
            }
        });

        $this->actingAs($peserta->anggota->user)->post(route('portal.afektif.save', $this->event), [
            'jawaban' => [$first => 4, $second => 4],
        ])->assertSessionHas('success');
        $this->assertSame(3, $attempts);
        $this->assertDatabaseHas('penilaian_akhir', ['anggota_id' => $peserta->anggota_id, 'nilai_afektif' => 100, 'ranking' => 1]);
    }

    public function test_afektif_menolak_jawaban_sebagian_dan_mengembalikan_pilihan(): void
    {
        $peserta = $this->peserta('Peserta Aktif');
        [$first, $second] = $this->butir();
        $page = route('portal.afektif.index', $this->event);

        $this->actingAs($peserta->anggota->user)->from($page)->post(route('portal.afektif.save', $this->event), [
            'jawaban' => [$first => 4],
        ])->assertRedirect($page)->assertSessionHasErrors('jawaban.'.$second)->assertSessionHasInput('jawaban.'.$first, 4);

        $html = $this->get($page)->assertOk()->assertSee('Isi semua butir aktif')->getContent();
        $this->assertMatchesRegularExpression('/name="jawaban\['.$first.'\]" value="4"\s+@change="recount\(\)"\s+checked/', $html);
        $this->assertDatabaseCount('afektif_jawaban', 0);
    }

    public function test_afektif_kertas_tetap_terkunci(): void
    {
        $peserta = $this->peserta('Peserta Aktif');
        [$first] = $this->butir();
        InputKertas::create([
            'event_id' => $this->event->id, 'anggota_id' => $peserta->anggota_id,
            'instrumen' => 'afektif', 'diinput_oleh' => $this->admin->id,
        ]);

        $this->actingAs($peserta->anggota->user)->post(route('portal.afektif.save', $this->event), [
            'jawaban' => [$first => 4],
        ])->assertSessionHas('warning');
        $this->assertDatabaseCount('afektif_jawaban', 0);
    }

    public function test_ngain_nol_sebelum_posttest_dan_tetap_negatif_setelah_posttest_nol(): void
    {
        $peserta = $this->peserta('Peserta Aktif');
        $nilai = $this->nilai($peserta);
        $nilai->update(['nilai_pretest' => 25]);
        $service = app(NilaiService::class);
        $service->hitungUlangPeserta($this->event, $peserta->anggota_id);
        $this->assertSame('0.00', $nilai->refresh()->nilai_akhir);
        $this->assertSame(0.0, $service->rankingAktif($this->event)->sole()->n_gain);

        PesertaTesMulai::create([
            'event_id' => $this->event->id, 'anggota_id' => $peserta->anggota_id,
            'tipe' => 'posttest', 'sumber' => 'manual', 'waktu_mulai' => now(), 'waktu_selesai' => now(),
        ]);
        $service->hitungUlangPeserta($this->event, $peserta->anggota_id);
        $this->assertSame('-4.00', $nilai->refresh()->nilai_akhir);
        $this->assertSame(-0.333, $service->rankingAktif($this->event)->sole()->n_gain);
    }

    private function anggota(string $nama): Anggota
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);

        return Anggota::factory()->create(['user_id' => $user->id, 'nama_lengkap' => $nama]);
    }

    private function peserta(string $nama, bool $aktif = true): EventPeserta
    {
        return EventPeserta::create([
            'event_id' => $this->event->id, 'anggota_id' => $this->anggota($nama)->id,
            'qr_token' => Str::random(32), 'status_aktif' => $aktif,
        ]);
    }

    private function nilai(EventPeserta $peserta): PenilaianAkhir
    {
        return PenilaianAkhir::firstOrCreate(['event_id' => $this->event->id, 'anggota_id' => $peserta->anggota_id]);
    }

    private function butir(): array
    {
        $subAspek = AfektifSubAspek::create([
            'event_id' => $this->event->id, 'nama_sub_aspek' => 'Sikap', 'urutan' => 1, 'aktif' => true,
        ]);

        return array_map(fn ($urutan) => AfektifButir::create([
            'sub_aspek_id' => $subAspek->id, 'teks_butir' => 'Butir '.$urutan, 'is_positif' => true, 'urutan' => $urutan,
        ])->id, [1, 2]);
    }
}
