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
}
