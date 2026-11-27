<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\User;
use App\Support\KertasSertifikat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Sertifikat dicetak di kertas F4 landscape dan tiap peserta tepat 2 halaman
 * (sertifikat + transkrip), termasuk saat diunduh massal (PDF gabungan & ZIP).
 */
class SertifikatF4Test extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Event $event;

    /** @var array<int, EventPeserta> */
    private array $peserta = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
        $this->event = Event::create([
            'nama_event' => 'Baitul Arqam F4 dengan Nama Kegiatan yang Cukup Panjang untuk Uji Tata Letak',
            'tanggal_mulai' => now()->subDays(3)->toDateString(),
            'tanggal_selesai' => now()->subDay()->toDateString(),
            'status' => 'selesai',
            'created_by' => $this->admin->id,
        ]);

        // 12 sesi materi: lebih banyak dari kegiatan nyata (maks. 10) agar transkrip tetap muat 1 halaman.
        $sesi = collect(range(1, 12))->map(fn ($i) => EventSesi::create([
            'event_id' => $this->event->id,
            'nama_sesi' => 'Materi Kemuhammadiyahan dan Perkaderan Bagian '.$i,
            'jenis' => 'materi',
            'urutan' => $i,
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'pemateri' => 'Drs. Pemateri Contoh Bergelar Panjang, M.Pd.I.',
        ]));

        $nama = [
            'Peserta Pendek',
            'Muhammad Abdurrahman Hafizhuddin Al-Karanganyari Wicaksono Prasetyo Nugroho, S.Pd.I., M.Pd.',
            'Siti Contoh',
        ];
        foreach ($nama as $n) {
            $anggota = Anggota::factory()->create([
                'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
                'nama_lengkap' => $n,
            ]);
            $this->peserta[] = EventPeserta::create(['event_id' => $this->event->id, 'anggota_id' => $anggota->id, 'status_aktif' => true, 'qr_token' => Str::random(32)]);
            foreach ($sesi as $s) {
                Absensi::create(['event_id' => $this->event->id, 'sesi_id' => $s->id, 'anggota_id' => $anggota->id, 'waktu_scan' => now(), 'scanned_by' => $this->admin->id, 'metode' => 'qr']);
            }
            PenilaianAkhir::create([
                'event_id' => $this->event->id, 'anggota_id' => $anggota->id,
                'nilai_kehadiran' => 100, 'nilai_akhir' => 85, 'status_kelulusan' => 'Lulus',
                'verification_hash' => hash('sha256', $n),
            ]);
        }
    }

    /**
     * @return array{halaman:int, mediabox:array<int,string>}
     */
    private function bacaPdf(string $isi): array
    {
        $this->assertStringStartsWith('%PDF', $isi);
        preg_match_all('#/Type\s*/Page(?![s\w])#', $isi, $halaman);
        preg_match_all('#/MediaBox\s*\[\s*([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s*\]#', $isi, $box, PREG_SET_ORDER);

        return [
            'halaman' => count($halaman[0]),
            'mediabox' => array_values(array_unique(array_map(fn ($b) => round((float) $b[3]).'x'.round((float) $b[4]), $box))),
        ];
    }

    private function f4(): string
    {
        [, , $pendek, $panjang] = KertasSertifikat::dompdf();

        return round($panjang).'x'.round($pendek); // landscape: lebar 330 mm, tinggi 215 mm
    }

    public function test_ukuran_f4_dalam_point(): void
    {
        $this->assertSame([0.0, 0.0, 609.45, 935.43], KertasSertifikat::dompdf());
    }

    public function test_sertifikat_satuan_f4_dua_halaman(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificate', [$this->event, $this->peserta[1]]));
        $res->assertOk();

        $pdf = $this->bacaPdf($res->getContent());
        $this->assertSame(2, $pdf['halaman'], 'Nama panjang tidak boleh membuat sertifikat meluber ke halaman tambahan.');
        $this->assertSame([$this->f4()], $pdf['mediabox']);
    }

    public function test_sertifikat_portal_peserta_f4(): void
    {
        $user = $this->peserta[0]->anggota->user;
        $res = $this->actingAs($user)->get(route('portal.sertifikat.download', $this->event));
        $res->assertOk();

        $pdf = $this->bacaPdf($res->getContent());
        $this->assertSame(2, $pdf['halaman']);
        $this->assertSame([$this->f4()], $pdf['mediabox']);
    }

    public function test_cetak_massal_pdf_gabungan_dua_halaman_per_peserta(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [$this->event, 'download' => 1]));
        $res->assertOk();

        $pdf = $this->bacaPdf($res->getContent());
        $this->assertSame(2 * count($this->peserta), $pdf['halaman']);
        $this->assertSame([$this->f4()], $pdf['mediabox']);
    }

    public function test_cetak_massal_terpilih_hanya_peserta_dipilih(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificates.batch', [
            $this->event, 'download' => 1, 'peserta_ids' => $this->peserta[0]->id.','.$this->peserta[2]->id,
        ]));
        $res->assertOk();

        $this->assertSame(4, $this->bacaPdf($res->getContent())['halaman']);
    }

    public function test_zip_massal_tiap_file_f4_dua_halaman_dan_file_sementara_dibersihkan(): void
    {
        $res = $this->actingAs($this->admin)->get(route('admin.events.certificates.zip', $this->event));
        $res->assertOk();

        $path = $res->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $this->assertSame(count($this->peserta), $zip->numFiles);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $pdf = $this->bacaPdf($zip->getFromIndex($i));
            $this->assertSame(2, $pdf['halaman'], $zip->getNameIndex($i));
            $this->assertSame([$this->f4()], $pdf['mediabox'], $zip->getNameIndex($i));
        }
        $zip->close();
        @unlink($path);

        // Folder kerja per-PDF sudah dihapus; hanya ZIP yang dikirim (dan ikut dihapus setelah terkirim).
        $this->assertSame([], glob(storage_path('app/tmp/sertifikat-*'), GLOB_ONLYDIR) ?: []);
    }
}
