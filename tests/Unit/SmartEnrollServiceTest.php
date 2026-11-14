<?php

namespace Tests\Unit;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;
use App\Services\SmartEnrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmartEnrollServiceTest extends TestCase
{
    use RefreshDatabase;

    private SmartEnrollService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SmartEnrollService::class);
    }

    public function test_normalize_name_removes_academic_and_religious_titles(): void
    {
        $this->assertSame('ahmad dahlan', $this->service->normalizeName('Drs. H. Ahmad Dahlan, M.Pd.'));
        $this->assertSame('siti walidah', $this->service->normalizeName('Hj. Siti Walidah, S.Ag., M.Ag'));
        $this->assertSame('irfan prasetyo', $this->service->normalizeName('Ir. Irfan Prasetyo, S.T., M.T.'));
        $this->assertSame('muhammad fachruddin', $this->service->normalizeName('Ust. Muhammad Fachruddin, S.Pd.I'));
        $this->assertSame('dewi kartika', $this->service->normalizeName('dr. Dewi Kartika, Sp.A'));
    }

    public function test_parse_names_from_text_strips_numbers_bullets_and_empty_lines(): void
    {
        $raw = "1. Ahmad Dahlan\n2. Siti Walidah\n\n* Ki Bagus Hadikusumo\n- Fachruddin\n   \n5) AR Fachruddin";
        $names = $this->service->parseNamesFromText($raw);

        $this->assertCount(5, $names);
        $this->assertSame('Ahmad Dahlan', $names[0]);
        $this->assertSame('Siti Walidah', $names[1]);
        $this->assertSame('Ki Bagus Hadikusumo', $names[2]);
        $this->assertSame('Fachruddin', $names[3]);
        $this->assertSame('AR Fachruddin', $names[4]);
    }

    public function test_match_names_categorizes_into_matched_already_enrolled_and_unmatched(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Instruktur',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'lokasi' => 'Gedung Dakwah',
            'kuota' => 20,
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        // Anggota 1: Terdaftar dan sudah di-enroll ke event
        $u1 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a1 = Anggota::factory()->create([
            'user_id' => $u1->id,
            'nama_lengkap' => 'Ahmad Dahlan',
            'pcm' => 'Karanganyar',
        ]);
        EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $a1->id,
            'status_aktif' => true,
            'qr_token' => Str::random(32),
        ]);

        // Anggota 2: Aktif, belum di-enroll
        $u2 = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a2 = Anggota::factory()->create([
            'user_id' => $u2->id,
            'nama_lengkap' => 'Siti Walidah',
            'pcm' => 'Colomadu',
        ]);

        // Input nama: variasi gelar, nama yang sudah enrolled, dan nama yang belum terdaftar
        $rawNames = [
            'Drs. H. Ahmad Dahlan, M.Pd', // Sudah enrolled
            'Hj. Siti Walidah, S.Ag',     // Siap enrolled
            'Kader Fiktif Belum Terdaftar', // Unmatched
        ];

        $result = $this->service->matchNames($rawNames, $event);

        $this->assertCount(1, $result['matched']);
        $this->assertSame($a2->id, $result['matched'][0]['anggota']->id);

        $this->assertCount(1, $result['already_enrolled']);
        $this->assertSame($a1->id, $result['already_enrolled'][0]['anggota']->id);

        $this->assertCount(1, $result['unmatched']);
        $this->assertSame('Kader Fiktif Belum Terdaftar', $result['unmatched'][0]['raw_name']);
    }

    public function test_generate_whatsapp_invite_message_contains_event_name_and_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'Baitul Arqam PDM Karanganyar 2026',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'lokasi' => 'Gedung Dakwah',
            'kuota' => 20,
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $unmatched = [
            ['raw_name' => 'Fulan bin Fulan'],
            ['raw_name' => 'Fulanah binti Fulan'],
        ];

        $msg = $this->service->generateWhatsAppInvite($unmatched, $event);

        $this->assertStringContainsString('Baitul Arqam PDM Karanganyar 2026', $msg);
        $this->assertStringContainsString('Fulan bin Fulan', $msg);
        $this->assertStringContainsString('Fulanah binti Fulan', $msg);
        $this->assertStringContainsString(route('anggota.register'), $msg);
    }
}
