<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PilihanJawaban;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoalCloneTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_page_shows_copy_controls_when_another_event_has_no_questions_yet(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $target = Event::create([
            'nama_event' => 'BA Tujuan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);
        Event::create([
            'nama_event' => 'BA Sumber Tanpa Soal',
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
            'tanggal_selesai' => now()->addDays(3)->toDateString(),
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.soal.index', [$target, 'posttest']))
            ->assertOk()
            ->assertSee('Salin dari Kegiatan Lain')
            ->assertSee('Dari Pretest')
            ->assertSee('Dari Posttest');
    }

    public function test_admin_can_clone_soal_from_another_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $eventSumber = Event::create([
            'nama_event' => 'Baitul Arqam Gelombang 1',
            'tanggal_mulai' => now()->subDays(10)->toDateString(),
            'tanggal_selesai' => now()->subDays(8)->toDateString(),
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);

        $soalSumber = Soal::create([
            'event_id' => $eventSumber->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Apa tujuan didirikannya Muhammadiyah?',
            'urutan' => 1,
        ]);

        PilihanJawaban::create([
            'soal_id' => $soalSumber->id,
            'label' => 'A',
            'teks' => 'Menegakkan dan menjunjung tinggi Agama Islam',
            'is_benar' => true,
        ]);

        PilihanJawaban::create([
            'soal_id' => $soalSumber->id,
            'label' => 'B',
            'teks' => 'Partai politik',
            'is_benar' => false,
        ]);

        $eventTarget = Event::create([
            'nama_event' => 'Baitul Arqam Gelombang 2',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.soal.clone', [$eventTarget, 'pretest']), [
            'source_event_id' => $eventSumber->id,
            'source_tipe' => 'pretest',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('soal', [
            'event_id' => $eventTarget->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Apa tujuan didirikannya Muhammadiyah?',
        ]);

        $clonedSoal = Soal::where('event_id', $eventTarget->id)->first();
        $this->assertCount(2, $clonedSoal->pilihan);
        $this->assertTrue((bool) $clonedSoal->pilihan->firstWhere('label', 'A')->is_benar);
    }

    public function test_admin_can_clone_posttest_questions_into_pretest_from_another_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $source = Event::create([
            'nama_event' => 'BA Sumber',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->subDays(2)->toDateString(),
            'status' => 'selesai',
            'created_by' => $admin->id,
        ]);
        $question = Soal::create([
            'event_id' => $source->id,
            'tipe' => 'posttest',
            'teks_soal' => 'Apa makna tajdid?',
            'urutan' => 1,
        ]);
        PilihanJawaban::create(['soal_id' => $question->id, 'label' => 'A', 'teks' => 'Pembaruan', 'is_benar' => true]);
        PilihanJawaban::create(['soal_id' => $question->id, 'label' => 'B', 'teks' => 'Perpecahan', 'is_benar' => false]);
        $target = Event::create([
            'nama_event' => 'BA Tujuan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.soal.clone', [$target, 'pretest']), [
                'source_event_id' => $source->id,
                'source_tipe' => 'posttest',
            ])
            ->assertRedirect();

        $cloned = Soal::where('event_id', $target->id)->firstOrFail();
        $this->assertSame('pretest', $cloned->tipe);
        $this->assertSame('Apa makna tajdid?', $cloned->teks_soal);
        $this->assertTrue((bool) $cloned->pilihan->firstWhere('label', 'A')->is_benar);
    }

    public function test_admin_can_clone_soal_from_pretest_to_posttest_within_same_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Karyawan PKU Karanganyar',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $pretestSoal = Soal::create([
            'event_id' => $event->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Siapa pendiri Muhammadiyah?',
            'urutan' => 1,
        ]);

        PilihanJawaban::create([
            'soal_id' => $pretestSoal->id,
            'label' => 'A',
            'teks' => 'K.H. Ahmad Dahlan',
            'is_benar' => true,
        ]);

        PilihanJawaban::create([
            'soal_id' => $pretestSoal->id,
            'label' => 'B',
            'teks' => 'H.O.S Cokroaminoto',
            'is_benar' => false,
        ]);

        // Verifikasi tombol cepat muncul di halaman posttest
        $this->actingAs($admin)
            ->get(route('admin.events.soal.index', [$event, 'posttest']))
            ->assertOk()
            ->assertSee('Salin dari Pretest Kegiatan Ini (1)')
            ->assertSee('[Kegiatan Ini] '.$event->nama_event);

        // Eksekusi salin dari pretest kegiatan yang sama ke posttest
        $response = $this->actingAs($admin)
            ->post(route('admin.events.soal.clone', [$event, 'posttest']), [
                'source_event_id' => $event->id,
                'source_tipe' => 'pretest',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('soal', [
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'teks_soal' => 'Siapa pendiri Muhammadiyah?',
        ]);

        $cloned = Soal::where('event_id', $event->id)->where('tipe', 'posttest')->firstOrFail();
        $this->assertCount(2, $cloned->pilihan);
        $this->assertTrue((bool) $cloned->pilihan->firstWhere('label', 'A')->is_benar);
    }

    public function test_cannot_clone_soal_from_same_event_and_same_tipe(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Demo',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.events.soal.clone', [$event, 'posttest']), [
                'source_event_id' => $event->id,
                'source_tipe' => 'posttest',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Tidak dapat menyalin dari jenis tes yang sama pada kegiatan ini.');
    }
}
