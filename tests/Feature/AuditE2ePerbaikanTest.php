<?php

namespace Tests\Feature;

use App\Exports\AnggotaExport;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Anggota;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\RtlSoal;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/** Regresi untuk temuan audit E2E Orca 27-11-2026. */
class AuditE2ePerbaikanTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'aktif']);
    }

    private function event(User $admin, array $extra = []): Event
    {
        return Event::create(array_merge([
            'nama_event' => 'Baitul Arqam Audit',
            'tanggal_mulai' => '2026-11-20',
            'tanggal_selesai' => '2026-11-21',
            'status' => 'selesai',
            'created_by' => $admin->id,
        ], $extra));
    }

    /** @return array{0: User, 1: Anggota, 2: EventPeserta} */
    private function peserta(Event $event, array $anggota = []): array
    {
        $user = User::factory()->create(['role' => 'anggota', 'status' => 'aktif']);
        $a = Anggota::factory()->create(array_merge(['user_id' => $user->id], $anggota));
        $ep = EventPeserta::create([
            'event_id' => $event->id, 'anggota_id' => $a->id,
            'qr_token' => app(QrService::class)->generateToken(), 'status_aktif' => true,
        ]);

        return [$user, $a, $ep];
    }

    // P-01 ---------------------------------------------------------------
    public function test_qr_verifikasi_menampilkan_data_sertifikat_asli(): void
    {
        $event = $this->event($this->admin());
        [, $a] = $this->peserta($event, ['nama_lengkap' => 'Peserta Verifikasi', 'pcm' => 'Tasikmadu']);
        $hash = hash('sha256', 'cek-verifikasi');
        PenilaianAkhir::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'status_kelulusan' => 'Lulus', 'verification_hash' => $hash]);

        $this->get('/?verify='.$hash)->assertOk()
            ->assertSee('data-verifikasi-sertifikat', false)
            ->assertSee('Sertifikat ASLI dan terdaftar')
            ->assertSee('Peserta Verifikasi')
            ->assertSee('Baitul Arqam Audit')
            ->assertDontSee($a->nik);
    }

    public function test_qr_verifikasi_menolak_hash_tidak_valid_tidak_lulus_dan_kegiatan_belum_selesai(): void
    {
        $admin = $this->admin();
        $selesai = $this->event($admin);
        [, $tl] = $this->peserta($selesai);
        PenilaianAkhir::create(['event_id' => $selesai->id, 'anggota_id' => $tl->id, 'status_kelulusan' => 'Tidak Lulus', 'verification_hash' => hash('sha256', 'tl')]);

        $berjalan = $this->event($admin, ['status' => 'berlangsung', 'nama_event' => 'Masih Berjalan']);
        [, $b] = $this->peserta($berjalan);
        PenilaianAkhir::create(['event_id' => $berjalan->id, 'anggota_id' => $b->id, 'status_kelulusan' => 'Lulus', 'verification_hash' => hash('sha256', 'jalan')]);

        foreach ([hash('sha256', 'tl'), hash('sha256', 'jalan'), str_repeat('a', 64), 'bukan-hash<script>'] as $h) {
            $this->get('/?verify='.urlencode($h))->assertOk()->assertSee('Sertifikat tidak ditemukan')->assertDontSee('<script>', false);
        }
        $this->get('/')->assertOk()->assertDontSee('data-verifikasi-sertifikat', false);
    }

    // P-02 / P-03 / P-04 ---------------------------------------------------
    public function test_afektif_terkunci_setelah_dikirim(): void
    {
        $event = $this->event($this->admin(), ['status' => 'berlangsung']);
        [$user, $a] = $this->peserta($event);
        $sa = AfektifSubAspek::create(['event_id' => $event->id, 'nama_sub_aspek' => 'Ideologi', 'urutan' => 1, 'aktif' => true]);
        $b1 = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Butir 1', 'is_positif' => true, 'urutan' => 1]);
        $b2 = AfektifButir::create(['sub_aspek_id' => $sa->id, 'teks_butir' => 'Butir 2', 'is_positif' => true, 'urutan' => 2]);

        $this->actingAs($user)->post(route('portal.afektif.save', $event), ['jawaban' => [$b1->id => 4, $b2->id => 3]])
            ->assertSessionHas('success');
        $this->actingAs($user)->post(route('portal.afektif.save', $event), ['jawaban' => [$b1->id => 1, $b2->id => 1]])
            ->assertSessionHas('warning');

        $this->assertSame(4, (int) AfektifJawaban::where('anggota_id', $a->id)->where('butir_id', $b1->id)->value('skor'));
        $this->actingAs($user)->get(route('portal.afektif.index', $event))->assertOk()
            ->assertSee('data-isian-terkunci', false)->assertDontSee(route('portal.afektif.save', $event));
    }

    public function test_angket_wajib_semua_butir_di_server_dan_terkunci_setelah_dikirim(): void
    {
        $event = $this->event($this->admin(), ['status' => 'berlangsung']);
        [$user, $a] = $this->peserta($event);
        $i1 = AngketItem::create(['event_id' => $event->id, 'kategori' => 'Materi', 'teks_item' => 'Materi jelas', 'urutan' => 1]);
        $i2 = AngketItem::create(['event_id' => $event->id, 'kategori' => 'Materi', 'teks_item' => 'Waktu cukup', 'urutan' => 2]);

        $this->actingAs($user)->post(route('portal.angket.save', $event), ['jawaban' => [$i1->id => 5]])
            ->assertSessionHasErrors('jawaban.'.$i2->id);
        $this->assertSame(0, AngketJawaban::where('anggota_id', $a->id)->count());

        $this->actingAs($user)->post(route('portal.angket.save', $event), ['jawaban' => [$i1->id => 5, $i2->id => 4]])
            ->assertSessionHas('success');
        $this->actingAs($user)->post(route('portal.angket.save', $event), ['jawaban' => [$i1->id => 1, $i2->id => 1]])
            ->assertSessionHas('warning');

        $this->assertSame(5, (int) AngketJawaban::where('anggota_id', $a->id)->where('item_id', $i1->id)->value('skor'));
        $this->actingAs($user)->get(route('portal.angket.index', $event))->assertOk()->assertSee('data-isian-terkunci', false);
    }

    // P-05 / P-06 / S-3 ----------------------------------------------------
    public function test_dashboard_peserta_tidak_lulus_tanpa_tombol_sertifikat_dan_rtl_opsional_konsisten(): void
    {
        $event = $this->event($this->admin(), ['rtl_wajib' => false]);
        RtlSoal::create(['event_id' => $event->id, 'pertanyaan' => 'Rencana?', 'tipe' => 'essay', 'urutan' => 1]);
        [$user, $a] = $this->peserta($event);
        PenilaianAkhir::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'status_kelulusan' => 'Tidak Lulus']);

        $this->actingAs($user)->get(route('portal.dashboard'))->assertOk()
            ->assertSee('Status: Tidak Lulus')
            ->assertDontSee(route('portal.sertifikat.download', $event))
            ->assertSee('RTL Opsional')
            ->assertDontSee('RTL Belum')
            ->assertSee('/4 Selesai');
    }

    public function test_teks_syarat_kelulusan_ranking_mengikuti_rtl_opsional(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin, ['rtl_wajib' => false]);
        RtlSoal::create(['event_id' => $event->id, 'pertanyaan' => 'Rencana?', 'tipe' => 'essay', 'urutan' => 1]);

        $this->actingAs($admin)->get(route('admin.events.ranking', $event))->assertOk()
            ->assertSee('RTL tidak menjadi syarat kelulusan')
            ->assertDontSee('RTL Terkirim (Lulus)');

        $event->update(['rtl_wajib' => true]);
        $this->actingAs($admin)->get(route('admin.events.ranking', $event))->assertOk()->assertSee('RTL Terkirim (Lulus)');
    }

    // S-4 ----------------------------------------------------------------
    public function test_pengingat_wa_dibatasi_per_jam(): void
    {
        config(['services.whatsapp.token' => 'token-dummy-test', 'arqam.wa_pengingat_per_jam' => 2]);
        RateLimiter::clear('wa-pengingat-event');
        Http::fake(['https://api.fonnte.com/*' => Http::response(['status' => true], 200)]);

        $admin = $this->admin();
        $event = $this->event($admin, ['status' => 'persiapan']);
        $eps = [];
        foreach (['081200000011', '081200000012', '081200000013'] as $wa) {
            $eps[] = $this->peserta($event, ['no_wa' => $wa])[2];
        }

        $this->actingAs($admin)->post(route('admin.events.peserta.remind-wa-batch', $event))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, '1 peserta belum dikirim'));
        Http::assertSentCount(2);

        $this->actingAs($admin)->post(route('admin.events.peserta.remind-wa', [$event, $eps[2]]))
            ->assertSessionHas('warning', fn ($m) => str_contains($m, 'Batas aman'))
            ->assertSessionHas('wa_url');
        Http::assertSentCount(2);
        RateLimiter::clear('wa-pengingat-event');
    }

    // S-2 ----------------------------------------------------------------
    public function test_hitung_ulang_gagal_tidak_membuat_500_dan_setelan_tetap_tersimpan(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        [, $a] = $this->peserta($event);
        PenilaianAkhir::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'status_kelulusan' => 'Lulus']);

        $this->mock(NilaiService::class, fn ($m) => $m->shouldReceive('hitungUlang')->andThrow(new \RuntimeException('deadlock simulasi')));

        $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'nama_event' => $event->nama_event, 'tanggal_mulai' => '2026-11-20', 'tanggal_selesai' => '2026-11-21',
            'status' => 'selesai', 'rtl_wajib' => '0',
        ])->assertRedirect()->assertSessionHas('warning');

        $this->assertFalse($event->fresh()->rtl_wajib);
    }

    // R-3 / ADM-03 -------------------------------------------------------
    public function test_excel_menyimpan_nomor_sebagai_teks_tanpa_apostrof_dan_menolak_formula(): void
    {
        $a = Anggota::factory()->create([
            'user_id' => User::factory()->create(['role' => 'anggota', 'status' => 'aktif'])->id,
            'nama_lengkap' => '=HYPERLINK("http://contoh.test","klik")',
            'no_wa' => '081234500001',
            'nik' => '3313020101900002',
        ]);

        $raw = Excel::raw(new AnggotaExport(collect([$a->load('user')])), ExcelType::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $raw);
        $sheet = IOFactory::load($path)->getActiveSheet();
        @unlink($path);

        $temu = [];
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $v = (string) $cell->getValue();
                if (in_array($v, ['081234500001', '3313020101900002', '=HYPERLINK("http://contoh.test","klik")'], true)) {
                    $temu[$v] = $cell->getDataType();
                }
                $this->assertStringNotContainsString("'0812", $v);
            }
        }

        $this->assertSame(DataType::TYPE_STRING, $temu['081234500001'] ?? null);
        $this->assertSame(DataType::TYPE_STRING, $temu['3313020101900002'] ?? null);
        $this->assertSame(DataType::TYPE_STRING, $temu['=HYPERLINK("http://contoh.test","klik")'] ?? null);
    }

    // Kontak Admin BA -----------------------------------------------------
    public function test_tombol_helpdesk_memakai_nomor_admin_ba(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('https://wa.me/6285601719683', false)
            ->assertSee('0856-0171-9683')
            ->assertDontSee('6281226309576', false);
        $this->get(route('login'))->assertOk()->assertSee('phone=6285601719683', false)->assertDontSee('6281226000000', false);
        $this->get(route('cek-status'))->assertOk()->assertSee('phone=6285601719683', false);
    }

    // S-1 ----------------------------------------------------------------
    public function test_zip_sertifikat_tidak_meninggalkan_folder_kerja(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        [, $a] = $this->peserta($event, ['nama_lengkap' => 'Peserta Zip']);
        PenilaianAkhir::create(['event_id' => $event->id, 'anggota_id' => $a->id, 'status_kelulusan' => 'Lulus', 'nilai_kehadiran' => 100, 'verification_hash' => hash('sha256', 'zip')]);

        $response = $this->actingAs($admin)->get(route('admin.events.certificates.zip', $event));
        $response->assertOk();
        $zipPath = $response->baseResponse->getFile()->getPathname();

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);
        $this->assertStringStartsWith('%PDF', $zip->getFromIndex(0));
        $zip->close();

        $this->assertSame([], glob(storage_path('app/tmp/sertifikat-*'), GLOB_ONLYDIR) ?: []);
        @unlink($zipPath);
    }
}
