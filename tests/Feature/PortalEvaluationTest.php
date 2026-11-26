<?php

namespace Tests\Feature;

use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private array $ctx = [];

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Evaluasi',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-02',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $anggotaUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $anggotaUser->id]);
        $ep = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
        ]);

        $this->ctx = compact('admin', 'event', 'anggotaUser', 'anggota', 'ep');
    }

    public function test_afektif_saved_and_scored(): void
    {
        $event = $this->ctx['event'];
        $sa = AfektifSubAspek::create(['event_id' => $event->id, 'nama_sub_aspek' => 'Kedisiplinan', 'urutan' => 1, 'aktif' => true]);
        $bPositif = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Saya disiplin', 'is_positif' => true, 'urutan' => 1]);
        $bNegatif = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Saya sering terlambat', 'is_positif' => false, 'urutan' => 2]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->get(route('portal.afektif.index', $event))
            ->assertOk()
            ->assertSee('Saya disiplin');

        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.afektif.save', $event), [
                'jawaban' => [$bPositif->id => 4, $bNegatif->id => 1],
            ])
            ->assertRedirect();

        $p = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $this->ctx['anggota']->id)->first();
        // positif 4/4 + negatif (5-1)=4 -> total 8, maks 8 -> 100
        $this->assertSame('100.00', $p->nilai_afektif);
    }

    public function test_afektif_rejects_out_of_range(): void
    {
        $event = $this->ctx['event'];
        $sa = AfektifSubAspek::create(['event_id' => $event->id, 'nama_sub_aspek' => 'X', 'urutan' => 1, 'aktif' => true]);
        $b = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Y', 'is_positif' => true, 'urutan' => 1]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.afektif.save', $event), ['jawaban' => [$b->id => 9]])
            ->assertSessionHasErrors('jawaban.'.$b->id);
    }

    public function test_angket_saved(): void
    {
        $event = $this->ctx['event'];
        $item = AngketItem::create(['event_id' => $event->id, 'kategori' => 'A', 'teks_item' => 'Materi jelas', 'urutan' => 1]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->get(route('portal.angket.index', $event))
            ->assertOk()
            ->assertSee('Pertanyaan Terakhir (Isian Terbuka)')
            ->assertSee('Bagaimana perasaan Anda setelah mengikuti perkaderan Baitul Arqam ini?');

        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.angket.save', $event), [
                'jawaban' => [$item->id => 5],
                'perasaan' => 'Sangat tercerahkan dan bersemangat mengabdi di persyarikatan',
                'komentar' => 'Sangat baik, fasilitas memuaskan',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('angket_jawaban', [
            'event_id' => $event->id,
            'item_id' => $item->id,
            'skor' => 5,
        ]);

        $this->assertDatabaseHas('angket_komentar', [
            'event_id' => $event->id,
            'anggota_id' => $this->ctx['anggotaUser']->anggota->id,
            'perasaan' => 'Sangat tercerahkan dan bersemangat mengabdi di persyarikatan',
            'komentar' => 'Sangat baik, fasilitas memuaskan',
        ]);
    }

    public function test_angket_orders_categories_consistently_and_skips_blank_komentar_row(): void
    {
        $event = $this->ctx['event'];
        $itemC = AngketItem::create(['event_id' => $event->id, 'kategori' => 'C', 'teks_item' => 'Aspek C dulu', 'urutan' => 1]);
        $itemA = AngketItem::create(['event_id' => $event->id, 'kategori' => 'A', 'teks_item' => 'Aspek A belakangan', 'urutan' => 1]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->get(route('portal.angket.index', $event))
            ->assertOk()
            ->assertSeeInOrder(['Aspek A', 'Aspek C']);

        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.angket.save', $event), [
                'jawaban' => [$itemA->id => 4, $itemC->id => 5],
                'perasaan' => '   ',
                'komentar' => '',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('angket_komentar', 0);
    }

    public function test_rtl_essay_and_upload_saved_and_locked(): void
    {
        Storage::fake('local');
        $event = $this->ctx['event'];
        RtlSoal::create(['event_id' => $event->id, 'pertanyaan' => 'Rencana aksi', 'tipe' => 'essay', 'urutan' => 1]);
        $uploadSoal = RtlSoal::create(['event_id' => $event->id, 'pertanyaan' => 'Bukti KTAM', 'tipe' => 'upload', 'urutan' => 2]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.rtl.submit', $event), [
                'jawaban' => [
                    RtlSoal::where('tipe', 'essay')->first()->id => 'Saya akan aktif di ranting',
                    $uploadSoal->id => UploadedFile::fake()->create('bukti.pdf', 200, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $rtl = Rtl::where('event_id', $event->id)->first();
        $this->assertSame('terkirim', $rtl->status);
        $this->assertNotNull($rtl->jawaban()->where('soal_id', $uploadSoal->id)->first()->file_bukti);

        // Second submit blocked (already terkirim)
        $this->actingAs($this->ctx['anggotaUser'])
            ->post(route('portal.rtl.submit', $event), ['jawaban' => []])
            ->assertSessionHas('error');
    }

    public function test_participant_downloads_id_card_pdf(): void
    {
        $event = $this->ctx['event'];

        $response = $this->actingAs($this->ctx['anggotaUser'])
            ->get(route('portal.idcard.download', $event));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_downloads_report_and_certificate_pdf(): void
    {
        $event = $this->ctx['event'];
        PenilaianAkhir::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $this->ctx['anggota']->id,
        ], ['nilai_posttest' => 80, 'nilai_akhir' => 80, 'nilai_kehadiran' => 100, 'status_kelulusan' => 'Lulus']);

        $this->actingAs($this->ctx['admin'])
            ->get(route('admin.events.report', $event))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // Guard sertifikat admin butuh event selesai + peserta lulus
        $event->update(['status' => 'selesai']);

        $this->actingAs($this->ctx['admin'])
            ->get(route('admin.events.certificate', [$event, $this->ctx['ep']]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_and_executive_can_download_excel_report(): void
    {
        $event = $this->ctx['event'];
        PenilaianAkhir::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $this->ctx['anggota']->id,
        ], ['nilai_posttest' => 85, 'nilai_akhir' => 85, 'nilai_kehadiran' => 100, 'status_kelulusan' => 'Lulus']);

        // Admin downloads Excel
        $responseAdmin = $this->actingAs($this->ctx['admin'])
            ->get(route('admin.events.exportExcel', $event));
        $responseAdmin->assertOk();
        $this->assertStringContainsString('spreadsheetml', $responseAdmin->headers->get('content-type') ?? '');

        // Executive downloads Excel
        $executiveUser = User::factory()->create(['role' => 'executive', 'status' => 'aktif']);
        $responseExec = $this->actingAs($executiveUser)
            ->get(route('admin.events.exportExcel', $event));
        $responseExec->assertOk();

        // Executive dashboard view
        $this->actingAs($executiveUser)
            ->get(route('admin.executive'))
            ->assertOk()
            ->assertSee('Tingkat Kelulusan');
    }

    public function test_portal_dashboard_shows_rtl_progress_when_disetujui(): void
    {
        $event = $this->ctx['event'];
        $anggota = $this->ctx['anggota'];

        Rtl::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status' => 'disetujui',
        ]);

        $this->actingAs($this->ctx['anggotaUser'])
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('RTL Terkirim');
    }
}
