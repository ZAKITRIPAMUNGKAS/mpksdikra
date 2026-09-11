<?php

namespace Tests\Feature;

use App\Models\AfektifSubAspek;
use App\Models\Event;
use App\Models\PsikomotorTemplate;
use App\Models\RtlSoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiapkanMateriTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_seeds_rubric_for_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Materi',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-03',
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $this->artisan('ba:siapkan-materi', ['event' => $event->id])
            ->assertSuccessful();

        $this->assertSame(6, PsikomotorTemplate::where('event_id', $event->id)->count());
        $this->assertSame(4, AfektifSubAspek::where('event_id', $event->id)->count());
        $this->assertSame(4, RtlSoal::where('event_id', $event->id)->count());
    }

    public function test_command_is_idempotent(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $event = Event::create([
            'nama_event' => 'BA Idempotent',
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-03',
            'status' => 'persiapan',
            'created_by' => $admin->id,
        ]);

        $this->artisan('ba:siapkan-materi', ['event' => $event->id])->assertSuccessful();
        $this->artisan('ba:siapkan-materi', ['event' => $event->id])->assertSuccessful();

        $this->assertSame(6, PsikomotorTemplate::where('event_id', $event->id)->count());
        $this->assertSame(4, RtlSoal::where('event_id', $event->id)->count());
    }

    public function test_command_fails_for_missing_event(): void
    {
        $this->artisan('ba:siapkan-materi', ['event' => 9999])->assertFailed();
    }
}
