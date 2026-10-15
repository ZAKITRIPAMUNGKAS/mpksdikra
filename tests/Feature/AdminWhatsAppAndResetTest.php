<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminWhatsAppAndResetTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    public function test_admin_can_reset_password_to_birthdate(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif', 'password' => Hash::make('password-lama')]);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nik' => '3313010101900077',
            'no_wa' => '081234567890',
            'tanggal_lahir' => '1990-01-15',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.anggota.reset-password', $anggota));

        $response->assertRedirect(route('admin.anggota.show', $anggota));
        $response->assertSessionHas('success');
        $response->assertSessionHas('reset_password_value', '15011990');

        // Verifikasi password baru bisa digunakan
        $this->assertTrue(Hash::check('15011990', $user->fresh()->password));
    }

    public function test_whatsapp_notification_service_builds_correct_url(): void
    {
        $anggota = Anggota::factory()->make([
            'nama_lengkap' => 'Ahmad Test',
            'no_wa' => '0812-3456-7890',
        ]);

        $url = WhatsAppNotificationService::makeUrl($anggota->no_wa, 'Halo MPKSDI');
        $this->assertStringStartsWith('https://api.whatsapp.com/send?phone=6281234567890', $url);
        $this->assertStringContainsString('text=Halo+MPKSDI', $url);
    }

    public function test_event_generates_formal_certificate_number(): void
    {
        $admin = $this->admin();
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Instruktur',
            'tanggal_mulai' => '2026-09-15',
            'tanggal_selesai' => '2026-09-17',
            'status' => 'selesai',
            'format_nomor_sertifikat' => '{nomor}/BA-MPKSDI/{bulan_romawi}/{tahun}',
            'created_by' => $admin->id,
        ]);

        $noSertifikat = $event->generateNomorSertifikat(7);
        $this->assertSame('007/BA-MPKSDI/IX/2026', $noSertifikat);
    }

    public function test_admin_can_send_wa_notification_automatically(): void
    {
        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Uji Notifikasi',
            'no_wa' => '081234567890',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.anggota.kirim-wa', $anggota));

        $response->assertRedirect(route('admin.anggota.show', $anggota))
            ->assertSessionHas('success')
            ->assertSessionHas('wa_auto_sent', true);
    }

    public function test_admin_handles_wa_gateway_failure_gracefully(): void
    {
        Http::fake([
            'https://api.fonnte.com/*' => Http::response(['status' => false, 'reason' => 'device disconnected'], 200),
        ]);

        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'ditolak']);
        $anggota = Anggota::factory()->create([
            'user_id' => $user->id,
            'nama_lengkap' => 'Kader Uji Ditolak',
            'no_wa' => '081234567890',
            'catatan_verifikasi' => 'Foto buram',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.anggota.kirim-wa', $anggota));

        $response->assertRedirect(route('admin.anggota.show', $anggota))
            ->assertSessionHas('warning')
            ->assertSessionHas('wa_auto_sent', false)
            ->assertSessionHas('wa_url');
    }
}
