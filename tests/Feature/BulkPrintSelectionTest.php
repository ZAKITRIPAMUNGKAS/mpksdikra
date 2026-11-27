<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\PilihanJawaban;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BulkPrintSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminAndEvent(string $status = 'selesai'): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Seleksi Cetak',
            'tanggal_mulai' => now()->subDays(2)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => $status,
            'rtl_wajib' => false,
            'created_by' => $admin->id,
        ]);

        return [$admin, $event];
    }

    private function enrollPeserta(Event $event, string $nama): EventPeserta
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => $nama,
            'pcm' => 'Karanganyar',
        ]);

        return EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);
    }

    public function test_id_cards_batch_supports_selected_peserta_ids(): void
    {
        [$admin, $event] = $this->createAdminAndEvent('berlangsung');
        $ep1 = $this->enrollPeserta($event, 'Peserta Alpha');
        $this->enrollPeserta($event, 'Peserta Beta');

        $response = $this->actingAs($admin)->get(route('admin.events.idcards.batch', [
            'event' => $event,
            'peserta_ids' => (string) $ep1->id,
        ]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $empty = $this->actingAs($admin)->get(route('admin.events.idcards.batch', [
            'event' => $event,
            'peserta_ids' => '999999',
        ]));
        $empty->assertNotFound();
    }

    public function test_certificates_batch_and_zip_only_include_selected_participants(): void
    {
        [$admin, $event] = $this->createAdminAndEvent('selesai');
        $sesi = EventSesi::create(['event_id' => $event->id, 'nama_sesi' => 'Materi', 'urutan' => 1, 'jenis' => 'materi']);

        $ep1 = $this->enrollPeserta($event, 'Andi Terpilih');
        $ep2 = $this->enrollPeserta($event, 'Budi Tidak Dipilih');

        foreach ([$ep1, $ep2] as $ep) {
            Absensi::create([
                'event_id' => $event->id,
                'sesi_id' => $sesi->id,
                'anggota_id' => $ep->anggota_id,
                'waktu_scan' => now(),
                'scanned_by' => $admin->id,
                'metode' => 'qr',
            ]);
            PenilaianAkhir::create([
                'event_id' => $event->id,
                'anggota_id' => $ep->anggota_id,
                'nilai_kehadiran' => 100,
                'nilai_akhir' => 88,
                'status_kelulusan' => 'Lulus',
                'verification_hash' => hash('sha256', (string) $ep->id),
            ]);
        }

        $pdfResponse = $this->actingAs($admin)->get(route('admin.events.certificates.batch', [
            'event' => $event,
            'peserta_ids' => [$ep1->id],
        ]));
        $pdfResponse->assertOk();
        $this->assertSame('application/pdf', $pdfResponse->headers->get('Content-Type'));

        $zipResponse = $this->actingAs($admin)->get(route('admin.events.certificates.zip', [
            'event' => $event,
            'peserta_ids' => (string) $ep1->id,
        ]));
        $zipResponse->assertOk();
        $path = $zipResponse->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($path);

        $this->assertSame(['001-andi-terpilih.pdf'], $names);
    }

    public function test_tes_manual_and_angket_kertas_cetak_support_selected_peserta_ids(): void
    {
        [$admin, $event] = $this->createAdminAndEvent('berlangsung');
        $ep1 = $this->enrollPeserta($event, 'Peserta Pertama');
        $this->enrollPeserta($event, 'Peserta Kedua');

        $soal = Soal::create([
            'event_id' => $event->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Contoh soal?',
            'urutan' => 1,
        ]);
        PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'A', 'teks' => 'Benar', 'is_benar' => true]);
        PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'B', 'teks' => 'Salah', 'is_benar' => false]);

        AngketItem::seedDefaultsFor($event);

        $tesPdf = $this->actingAs($admin)->get(route('admin.events.tes-manual.cetak', [
            'event' => $event,
            'tipe' => 'pretest',
            'lembar' => 'jawaban',
            'peserta_ids' => (string) $ep1->id,
        ]));
        $tesPdf->assertOk();
        $this->assertSame('application/pdf', $tesPdf->headers->get('Content-Type'));

        $angketPdf = $this->actingAs($admin)->get(route('admin.events.angket-kertas.cetak', [
            'event' => $event,
            'jenis' => 'angket',
            'lembar' => 'jawaban',
            'peserta_ids' => (string) $ep1->id,
        ]));
        $angketPdf->assertOk();
        $this->assertSame('application/pdf', $angketPdf->headers->get('Content-Type'));
    }

    public function test_admin_views_render_bulk_print_selection_dialogs(): void
    {
        [$admin, $event] = $this->createAdminAndEvent('selesai');
        $this->enrollPeserta($event, 'Kader UI');
        $soal = Soal::create(['event_id' => $event->id, 'tipe' => 'pretest', 'teks_soal' => 'Uji?', 'urutan' => 1]);
        PilihanJawaban::create(['soal_id' => $soal->id, 'label' => 'A', 'teks' => 'Ya', 'is_benar' => true]);

        $showResponse = $this->actingAs($admin)->get(route('admin.events.show', $event));
        $showResponse->assertOk();
        $showResponse->assertSee('id="dialog-idcards"', false);
        $showResponse->assertSee('id="dialog-sertifikat"', false);

        $rankingResponse = $this->actingAs($admin)->get(route('admin.events.ranking', $event));
        $rankingResponse->assertOk();
        $rankingResponse->assertSee('id="dialog-sertifikat-ranking"', false);

        $manualResponse = $this->actingAs($admin)->get(route('admin.events.tes-manual.index', [$event, 'pretest']));
        $manualResponse->assertOk();
        $manualResponse->assertSee('id="dialog-ljk-kertas"', false);
    }
}
