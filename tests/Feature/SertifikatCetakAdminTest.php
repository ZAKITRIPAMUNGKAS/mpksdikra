<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Services\NilaiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Admin bisa mencetak sertifikat kapan saja, memilih peserta apa pun, dan memilih mode bolak-balik.
 */
class SertifikatCetakAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    /** @var array<string, EventPeserta> */
    private array $ep = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Nilai dikunci agar status tidak dihitung ulang dari data kosong di test ini.
        $this->mock(NilaiService::class, function ($m) {
            $m->shouldReceive('hitungUlang')->andReturnNull();
            $m->shouldReceive('hitungUlangPeserta')->andReturnNull();
        });

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'BA Cetak Kapan Saja',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDay()->toDateString(),
            'status' => 'berlangsung',
            'created_by' => $this->admin->id,
        ]);

        foreach (['Ahmad Lulus' => 'Lulus', 'Budi Bersyarat' => 'Lulus Bersyarat', 'Citra Belum' => 'Tidak Lulus', 'Dodi Kosong' => null] as $nama => $status) {
            $anggota = Anggota::factory()->create([
                'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
                'nama_lengkap' => $nama,
            ]);
            $this->ep[$nama] = EventPeserta::create(['event_id' => $this->event->id, 'anggota_id' => $anggota->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
            if ($status) {
                PenilaianAkhir::create([
                    'event_id' => $this->event->id, 'anggota_id' => $anggota->id,
                    'nilai_kehadiran' => 100, 'nilai_akhir' => 80, 'status_kelulusan' => $status,
                    'verification_hash' => hash('sha256', $nama),
                ]);
            }
        }
    }

    private function halaman(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page(?![s\w])#', $pdf);
    }

    /** Tangkap item yang dikirim ke template PDF massal. */
    private function tangkapItem(): \ArrayObject
    {
        $tangkap = new \ArrayObject;
        View::creator('admin.events.certificates-batch-pdf', function ($view) use ($tangkap) {
            $tangkap['nama'] = collect($view->getData()['items'])->map(fn ($i) => $i['peserta']->anggota->nama_lengkap)->all();
            $tangkap['sisi'] = $view->getData()['sisi'] ?? null;
        });

        return $tangkap;
    }

    public function test_halaman_kegiatan_berlangsung_menampilkan_dialog_dengan_lulus_tercentang(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.events.show', $this->event));
        $res->assertOk()
            ->assertSee('data-cetak-sertifikat', false)
            ->assertSee('data-mode-cetak', false)
            ->assertSee('Langsung Unduh 2 Peserta Lulus (1 PDF)')
            ->assertDontSee('Status Selesai Diperlukan');

        // Semua peserta aktif tampil di daftar, tetapi hanya yang lulus tercentang di awal.
        $html = $res->getContent();
        foreach (['Ahmad Lulus', 'Budi Bersyarat', 'Citra Belum', 'Dodi Kosong'] as $nama) {
            $this->assertStringContainsString($nama, $html);
        }
        $this->assertStringContainsString('Belum Dinilai', $html);
        $terpilih = [(int) $this->ep['Ahmad Lulus']->id, (int) $this->ep['Budi Bersyarat']->id];
        sort($terpilih);
        $this->assertStringContainsString('selected: JSON.parse(\'['.implode(',', $terpilih).']\')', $html);
    }

    public function test_tanpa_pilihan_hanya_peserta_lulus_dan_bisa_sebelum_selesai(): void
    {
        $tangkap = $this->tangkapItem();
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1]));

        $res->assertOk();
        $this->assertSame(['Ahmad Lulus', 'Budi Bersyarat'], $tangkap['nama']);
        $this->assertSame(4, $this->halaman($res->getContent()));
    }

    public function test_pilihan_bisa_berisi_peserta_belum_lulus_dicetak_sebagai_peserta(): void
    {
        $tangkap = $this->tangkapItem();
        $ids = $this->ep['Citra Belum']->id.','.$this->ep['Dodi Kosong']->id;
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1, 'peserta_ids' => $ids]));

        $res->assertOk();
        $this->assertSame(['Citra Belum', 'Dodi Kosong'], $tangkap['nama']);
        $this->assertSame(4, $this->halaman($res->getContent()));

        $html = view('admin.events.certificate-pdf', [
            'event' => $this->event->load(['sesi', 'fasilitator']),
            'peserta' => $this->ep['Citra Belum']->load('anggota'),
            'nilai' => PenilaianAkhir::where('anggota_id', $this->ep['Citra Belum']->anggota_id)->first(),
            'qrPng' => '',
        ])->render();
        $this->assertStringContainsString('Telah mengikuti', $html);
        $this->assertStringContainsString('PESERTA', $html);
        $this->assertStringNotContainsString('TIDAK LULUS', $html);
    }

    public function test_mode_depan_belakang_dan_urutan_dibalik(): void
    {
        $ids = collect($this->ep)->pluck('id')->implode(',');

        $depan = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1, 'peserta_ids' => $ids, 'sisi' => 'depan']));
        $this->assertSame(4, $this->halaman($depan->getContent()), 'Depan saja: 1 halaman per peserta.');

        $tangkap = $this->tangkapItem();
        $belakang = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1, 'peserta_ids' => $ids, 'sisi' => 'belakang', 'balik' => 1]));
        $this->assertSame(4, $this->halaman($belakang->getContent()), 'Belakang saja: 1 halaman per peserta.');
        $this->assertSame('belakang', $tangkap['sisi']);
        $this->assertSame(['Dodi Kosong', 'Citra Belum', 'Budi Bersyarat', 'Ahmad Lulus'], $tangkap['nama']);

        $duplex = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1, 'peserta_ids' => $ids, 'sisi' => 'ngawur']));
        $this->assertSame(8, $this->halaman($duplex->getContent()), 'Mode tidak dikenal kembali ke bolak-balik.');
    }

    public function test_sertifikat_satuan_sebelum_selesai_dan_portal_tetap_terkunci(): void
    {
        $citra = $this->ep['Citra Belum'];

        $res = $this->actingAs($this->admin)->get(route('admin.events.certificate', [$this->event, $citra]));
        $res->assertOk();
        $this->assertSame(2, $this->halaman($res->getContent()));

        $this->actingAs($citra->anggota->user)
            ->get(route('portal.sertifikat.download', $this->event))
            ->assertForbidden();
    }
}
