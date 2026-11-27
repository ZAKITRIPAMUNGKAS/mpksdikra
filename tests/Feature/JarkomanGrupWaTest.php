<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\QrService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JarkomanGrupWaTest extends TestCase
{
    use RefreshDatabase;

    private const LINK = 'https://chat.whatsapp.com/AbCdEfGh123456';

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function event(User $admin, ?string $link = self::LINK): Event
    {
        return Event::create([
            'nama_event' => 'Baitul Arqam Contoh',
            'tanggal_mulai' => '2026-12-05',
            'tanggal_selesai' => '2026-12-06',
            'lokasi' => 'Gedung Contoh',
            'status' => 'persiapan',
            'link_grup_wa' => $link,
            'created_by' => $admin->id,
        ]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'nama_event' => 'Baitul Arqam Grup',
            'tanggal_mulai' => '2026-12-01',
            'tanggal_selesai' => '2026-12-02',
            'lokasi' => 'Gedung Contoh',
            'status' => 'persiapan',
            'bobot_kognitif' => 45,
            'bobot_psikomotor' => 25,
            'bobot_afektif' => 30,
            'bobot_keaktifan' => 0,
        ], $extra);
    }

    public function test_link_grup_wa_tersimpan_saat_membuat_dan_mengubah_event(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.events.store'), $this->payload(['link_grup_wa' => self::LINK]))
            ->assertRedirect()->assertSessionHasNoErrors();
        $event = Event::where('nama_event', 'Baitul Arqam Grup')->firstOrFail();
        $this->assertSame(self::LINK, $event->link_grup_wa);

        $this->actingAs($admin)->put(route('admin.events.update', $event), $this->payload(['link_grup_wa' => '']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($event->fresh()->link_grup_wa);

        $this->actingAs($admin)->get(route('admin.events.edit', $event))
            ->assertOk()->assertSee('Link Grup WhatsApp Peserta');
    }

    public function test_link_selain_grup_whatsapp_ditolak(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.events.store'), $this->payload(['link_grup_wa' => 'https://contoh.test/grup']))
            ->assertSessionHasErrors('link_grup_wa');
        $this->assertDatabaseMissing('events', ['nama_event' => 'Baitul Arqam Grup']);
    }

    public function test_pesan_pengingat_dan_jarkoman_memuat_link_grup(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        $anggota = Anggota::factory()->create(['nama_lengkap' => 'Peserta Contoh']);

        $this->assertStringContainsString(self::LINK, WhatsAppNotificationService::pesanPengingatEvent($anggota, $event));
        $jarkoman = WhatsAppNotificationService::pesanJarkomanEvent($event);
        $this->assertStringContainsString(self::LINK, $jarkoman);
        $this->assertStringContainsString('JARKOMAN PENGINGAT', $jarkoman);

        $tanpaLink = $this->event($admin, null);
        $this->assertStringNotContainsString('grup WhatsApp', WhatsAppNotificationService::pesanJarkomanEvent($tanpaLink));
    }

    public function test_halaman_event_menampilkan_panel_jarkoman(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => Anggota::factory()->create()->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        $this->actingAs($admin)->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('data-jarkoman-panel', false)
            ->assertSee('Jarkoman Pengingat')
            ->assertSee('Kirim ke Semua Peserta (1)')
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee(rawurlencode(self::LINK), false);
    }

    public function test_ikon_wa_peserta_membuka_popup_pilihan_tanpa_mengirim(): void
    {
        Http::fake();
        $admin = $this->admin();
        $event = $this->event($admin);
        $peserta = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => Anggota::factory()->create(['nama_lengkap' => 'Peserta Popup', 'no_wa' => '081200000009'])->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        $html = $this->actingAs($admin)->get(route('admin.events.show', $event))->assertOk()->getContent();

        $this->assertStringContainsString('data-wa-peserta-modal', $html);
        $this->assertStringContainsString('data-wa-peserta', $html);
        $this->assertStringContainsString('Buka Chat WhatsApp', $html);
        $this->assertStringContainsString('Salin Nomor', $html);
        $this->assertStringContainsString('Kirim Otomatis (WA Gateway)', $html);
        // Data popup memuat nomor terformat, link chat, dan URL kirim otomatis peserta ini.
        $this->assertStringContainsString('6281200000009', $html);
        $this->assertStringContainsString('api.whatsapp.com', $html);
        $this->assertMatchesRegularExpression('#peserta\\\\+/'.$peserta->id.'\\\\+/remind-wa#', $html);
        // Ikon tidak lagi berupa form POST langsung.
        $this->assertStringNotContainsString('<form method="POST" action="'.route('admin.events.peserta.remind-wa', [$event, $peserta]).'"', $html);
        Http::assertNothingSent();
    }

    public function test_kirim_semua_mengirim_pesan_berisi_link_grup(): void
    {
        config([
            'services.whatsapp.provider' => 'fonnte',
            'services.whatsapp.token' => 'dummy-token-tes',
            'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        ]);
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $admin = $this->admin();
        $event = $this->event($admin);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => Anggota::factory()->create(['no_wa' => '081200000001'])->id,
            'qr_token' => app(QrService::class)->generateToken(),
            'status_aktif' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.events.peserta.remind-wa-batch', $event))->assertRedirect();

        Http::assertSent(fn ($request) => str_contains((string) ($request->data()['message'] ?? ''), self::LINK));
    }
}
