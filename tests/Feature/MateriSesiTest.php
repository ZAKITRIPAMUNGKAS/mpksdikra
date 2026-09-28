<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MateriSesiTest extends TestCase
{
    use RefreshDatabase;

    private function createEvent(User $admin): Event
    {
        return Event::create([
            'nama_event' => 'Baitul Arqam Uji Coba',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_add_sesi_with_materi_file_and_link(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->createEvent($admin);

        $file = UploadedFile::fake()->create('slide-aik.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('admin.events.sesi.store', $event), [
            'nama_sesi' => 'AIK & Kemuhammadiyahan',
            'jenis' => 'materi',
            'pemateri' => 'Ust. Dr. H. Nashir',
            'link_materi' => 'https://drive.google.com/materi-aik',
            'deskripsi' => 'Pengantar ideologi gerakan Muhammadiyah',
            'file_materi' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_sesi', [
            'event_id' => $event->id,
            'nama_sesi' => 'AIK & Kemuhammadiyahan',
            'pemateri' => 'Ust. Dr. H. Nashir',
            'link_materi' => 'https://drive.google.com/materi-aik',
        ]);

        $sesi = EventSesi::first();
        $this->assertNotNull($sesi->file_materi);
        Storage::disk('local')->assertExists($sesi->file_materi);
    }

    public function test_active_participant_can_download_materi(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->createEvent($admin);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        $path = Storage::disk('local')->putFile('materi_sesi', UploadedFile::fake()->create('modul.pdf', 300, 'application/pdf'));

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Manajemen Cabang',
            'jenis' => 'materi',
            'urutan' => 1,
            'file_materi' => $path,
        ]);

        $response = $this->actingAs($user)->get(route('sesi.materi.download', $sesi));

        $response->assertOk();
    }

    public function test_non_participant_cannot_download_materi(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = $this->createEvent($admin);

        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);

        $path = Storage::disk('local')->putFile('materi_sesi', UploadedFile::fake()->create('modul.pdf', 300, 'application/pdf'));

        $sesi = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Manajemen Cabang',
            'jenis' => 'materi',
            'urutan' => 1,
            'file_materi' => $path,
        ]);

        $response = $this->actingAs($user)->get(route('sesi.materi.download', $sesi));

        $response->assertStatus(403);
    }
}
