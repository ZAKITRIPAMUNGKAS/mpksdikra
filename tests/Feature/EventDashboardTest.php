<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\AngketKomentar;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PesertaTesMulai;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_event_training_dashboard_with_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar 2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'lokasi' => 'Gedung Dakwah Karanganyar',
            'created_by' => $admin->id,
        ]);

        $userKader = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Kader Teladan',
            'jenis_kelamin' => 'L',
            'pcm' => 'Karanganyar',
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Orientasi & Muqaddimah',
            'jenis' => 'materi',
            'urutan' => 1,
            'tanggal' => now()->toDateString(),
            'pemateri' => 'Pimpinan Daerah',
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'pretest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(30),
        ]);

        PesertaTesMulai::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'tipe' => 'posttest',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', $event));

        $response->assertOk();
        $response->assertSee('Baitul Arqam PDM Karanganyar 2026');
        $response->assertSee('Pusat Komando &amp; Monitoring Pelatihan Baitul Arqam', false);
        $response->assertSee('Orientasi &amp; Muqaddimah', false);
        $response->assertSee('Kader Teladan');
        $response->assertSee('Cetak ID Card Massal (A4)');
        $response->assertSee('Laki-laki');
        $response->assertSee('Perempuan');
        $response->assertSee('1 peserta');
        $response->assertSee('0<span class="hidden sm:inline"> peserta</span>', false);
    }

    public function test_event_dashboard_works_with_active_sesi_tes_and_soal(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam RS PKU',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        SesiTes::create([
            'event_id' => $event->id,
            'tipe' => 'pretest',
            'status' => 'tutup',
            'durasi_menit' => 30,
        ]);

        SesiTes::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'status' => 'aktif',
            'durasi_menit' => 45,
        ]);

        Soal::create([
            'event_id' => $event->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Pertanyaan Pretest 1',
            'urutan' => 1,
        ]);

        Soal::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'teks_soal' => 'Pertanyaan Posttest 1',
            'urutan' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', $event));

        $response->assertOk();
        $response->assertSee('Baitul Arqam RS PKU');
        $response->assertSee('1 Soal');
    }

    public function test_event_dashboard_displays_feedback_tab_and_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Feedback Test',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $kaderUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $kaderUser->id, 'nama_lengkap' => 'Kader Penilai']);

        $item = AngketItem::create([
            'event_id' => $event->id,
            'kategori' => 'A',
            'urutan' => 1,
            'teks_item' => 'Materi sangat aplikatif dan inspiratif.',
        ]);

        AngketJawaban::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'item_id' => $item->id,
            'skor' => 5,
        ]);

        AngketKomentar::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'komentar' => 'Sangat menginspirasi dan panitia sangat ramah.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', [$event, 'tab' => 'feedback']));

        $response->assertOk();
        $response->assertSee('Feedback');
        $response->assertSee('Feedback &amp; Evaluasi Penyelenggaraan', false);
        $response->assertSee('Materi sangat aplikatif dan inspiratif.');
        $response->assertSee('Sangat menginspirasi dan panitia sangat ramah.');
        $response->assertSee('5.00');
        $response->assertSee('Pratinjau Skala Likert');
        $response->assertSee('Sangat Kurang');
        $response->assertSee('Sangat Baik');
        $response->assertSee('Preview ulasan tertulis');
    }

    public function test_admin_can_seed_default_angket_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Seed Angket Test',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.angket.seed-defaults', $event));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(9, AngketItem::where('event_id', $event->id)->count());
    }

    public function test_angket_management_route_redirects_to_merged_feedback_workspace(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Workspace Angket',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.angket', $event))
            ->assertRedirect(route('admin.events.show', [$event, 'tab' => 'feedback', 'sub_feedback' => 'kelola']));
    }

    public function test_feedback_tab_shows_perasaan_only_entries_and_crud_stays_in_kelola_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Feedback Perasaan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);

        $kaderUser = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $kaderUser->id, 'nama_lengkap' => 'Kader Refleksi']);

        // Peserta hanya mengisi kolom perasaan dan mengosongkan komentar opsional
        AngketKomentar::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'perasaan' => 'Hati terasa tenang dan semangat bermuhammadiyah meningkat.',
            'komentar' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', [$event, 'tab' => 'feedback']));
        $response->assertOk();
        $response->assertSee('Hati terasa tenang dan semangat bermuhammadiyah meningkat.');
        $response->assertSee('1 masukan tercatat');
        $response->assertDontSee(route('portal.angket.index', $event), false);
        $response->assertDontSee('Self Assessment');
        $response->assertSee(route('admin.events.angket-kertas.index', [$event, 'angket']), false);

        $expectedRedirect = route('admin.events.show', [$event, 'tab' => 'feedback', 'sub_feedback' => 'kelola']);

        // Tambah item manual lalu seed defaults tidak menduplikasi dan tetap di tab kelola
        $this->actingAs($admin)
            ->from(route('admin.events.show', $event))
            ->post(route('admin.events.angket.item.store', $event), [
                'kategori' => 'A',
                'teks_item' => AngketItem::DEFAULT_ITEMS['A'][0],
            ])
            ->assertRedirect($expectedRedirect);

        $this->actingAs($admin)
            ->from(route('admin.events.show', $event))
            ->post(route('admin.events.angket.seed-defaults', $event))
            ->assertRedirect($expectedRedirect);

        $this->assertSame(9, AngketItem::where('event_id', $event->id)->count());

        $item = AngketItem::where('event_id', $event->id)->firstOrFail();
        $this->actingAs($admin)
            ->from(route('admin.events.show', $event))
            ->delete(route('admin.events.angket.item.destroy', [$event, $item]))
            ->assertRedirect($expectedRedirect);

        $this->assertSame(8, AngketItem::where('event_id', $event->id)->count());
    }
}
