<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\Soal;
use App\Models\User;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiFixAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function createEvent(User $admin): Event
    {
        return Event::create([
            'nama_event' => 'BA Uji Perbaikan UI',
            'tanggal_mulai' => '2026-11-26',
            'tanggal_selesai' => '2026-11-28',
            'lokasi' => 'Gedung Dakwah',
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
    }

    public function test_event_show_renders_edit_sesi_button_and_modal(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent($admin);
        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi Ideologi Muhammadiyah',
            'jenis' => 'materi',
            'urutan' => 1,
            'tanggal' => '2026-11-26',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'pemateri' => 'Ust. Ahmad',
            'deskripsi' => 'Materi pengantar.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.show', [$event, 'tab' => 'sesi']));

        $response->assertOk();
        $response->assertSee(route('admin.events.sesi.update', [$event, $sesi]), false);
        $response->assertSee('Edit Sesi Agenda');
        $this->assertMatchesRegularExpression('/<button[^>]*>\s*Edit\s*<\/button>/', $response->getContent());
    }

    public function test_update_sesi_updates_fields_and_preserves_absensi(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent($admin);
        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi Awal',
            'jenis' => 'materi',
            'urutan' => 1,
            'tanggal' => '2026-11-26',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:00',
            'pemateri' => 'Pemateri Lama',
        ]);

        $userPeserta = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $userPeserta->id]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        Absensi::create([
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $anggota->id,
            'waktu_scan' => now(),
            'scanned_by' => $admin->id,
            'metode' => 'qr',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.events.show', [$event, 'tab' => 'sesi']))
            ->put(route('admin.events.sesi.update', [$event, $sesi]), [
                '_sesi_form' => 'edit',
                '_sesi_id' => $sesi->id,
                'nama_sesi' => 'Sesi Diperbarui',
                'jenis' => 'outbound',
                'pemateri' => 'Pemateri Baru',
                'tanggal' => '2026-11-27',
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:30',
                'link_materi' => 'https://example.com/modul',
                'deskripsi' => 'Deskripsi sesi diperbarui',
            ]);

        $response->assertRedirect(route('admin.events.show', [$event, 'tab' => 'sesi']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('event_sesi', [
            'id' => $sesi->id,
            'nama_sesi' => 'Sesi Diperbarui',
            'jenis' => 'outbound',
            'pemateri' => 'Pemateri Baru',
            'link_materi' => 'https://example.com/modul',
            'deskripsi' => 'Deskripsi sesi diperbarui',
        ]);

        $this->assertDatabaseHas('absensi', [
            'event_id' => $event->id,
            'sesi_id' => $sesi->id,
            'anggota_id' => $anggota->id,
        ]);
        $this->assertSame(1, Absensi::where('sesi_id', $sesi->id)->count());
    }

    public function test_update_sesi_validation_errors_are_displayed_on_event_show(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent($admin);
        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi Valid',
            'jenis' => 'materi',
            'urutan' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.events.show', [$event, 'tab' => 'sesi']))
            ->followingRedirects()
            ->put(route('admin.events.sesi.update', [$event, $sesi]), [
                '_sesi_form' => 'edit',
                '_sesi_id' => $sesi->id,
                'nama_sesi' => '',
                'jenis' => 'materi',
                'link_materi' => 'bukan-url',
            ]);

        $response->assertOk();
        $response->assertSee('nama sesi', false);
        $response->assertSee('Edit Sesi Agenda');
        $this->assertDatabaseHas('event_sesi', [
            'id' => $sesi->id,
            'nama_sesi' => 'Sesi Valid',
        ]);
    }

    public function test_scan_page_renders_camera_mode_alongside_barcode_input(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent($admin);
        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi Pembukaan',
            'jenis' => 'materi',
            'urutan' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.events.absensi.scan', [$event, $sesi]));

        $response->assertOk();
        $response->assertSee('Scan dengan Kamera');
        $response->assertSee('<video', false);
        $response->assertSee('getUserMedia', false);
        $response->assertSee('facingMode', false);
        $response->assertSee('qr-scanner', false);
        $response->assertSee('Scan QR atau ketik kode peserta...');
    }

    public function test_soal_form_displays_server_validation_errors_and_preserves_old_input(): void
    {
        $admin = $this->admin();
        $event = $this->createEvent($admin);

        $response = $this->actingAs($admin)
            ->from(route('admin.events.soal.index', [$event, 'pretest']))
            ->followingRedirects()
            ->post(route('admin.events.soal.store', [$event, 'pretest']), [
                'teks_soal' => '',
                'pilihan' => [
                    ['teks' => 'Pilihan A Tersimpan', 'is_benar' => '1'],
                    ['teks' => '', 'is_benar' => '0'],
                ],
            ]);

        $response->assertOk();
        $response->assertSee('Pilihan A Tersimpan');
        $response->assertSee('teks soal', false);
        $response->assertSee('text-rose-600', false);
        $this->assertSame(0, Soal::where('event_id', $event->id)->count());
    }
}
