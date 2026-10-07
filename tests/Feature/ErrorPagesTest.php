<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_403_page_renders_with_certificate_context(): void
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create(['user_id' => $user->id]);

        $event = Event::create([
            'nama_event' => 'Baitul Arqam Belum Selesai',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung', // Not 'selesai'
            'created_by' => $user->id,
        ]);

        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        // Attempt to access certificate while event is still 'berlangsung'
        $response = $this->actingAs($user)->get(route('portal.sertifikat.download', $event));

        $response->assertStatus(403);
        $response->assertSee('Sertifikat Belum Dapat Diunduh');
        $response->assertSee('Sertifikat tersedia setelah event selesai.');
        $response->assertSee('MPKSDI');
        $response->assertSee('Kembali ke Halaman Sebelumnya');
    }

    public function test_custom_404_page_renders_professionally(): void
    {
        $response = $this->get('/halaman-yang-tidak-pernah-ada-12345');

        $response->assertStatus(404);
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('MPKSDI');
    }
}
