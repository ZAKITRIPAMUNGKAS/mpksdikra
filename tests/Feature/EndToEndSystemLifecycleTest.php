<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\PilihanJawaban;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use App\Services\NilaiService;
use App\Services\QrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndSystemLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_baitul_arqam_system_lifecycle_from_registration_to_certification(): void
    {
        // =========================================================================
        // FASE 1: Registrasi Master Kader Mandiri & Verifikasi Admin
        // =========================================================================
        $admin = User::factory()->create([
            'name' => 'Administrator MPKSDI',
            'email' => 'admin@mpksdi.test',
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // Kader 1 mendaftar
        $kader1User = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
            'verified_at' => now(),
        ]);
        $kader1 = Anggota::factory()->create([
            'user_id' => $kader1User->id,
            'nama_lengkap' => 'Ahmad Dahlan Muda',
            'jenis_kelamin' => 'L',
        ]);

        // Kader 2 mendaftar
        $kader2User = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
            'verified_at' => now(),
        ]);
        $kader2 = Anggota::factory()->create([
            'user_id' => $kader2User->id,
            'nama_lengkap' => 'Siti Walidah Khadijah',
            'jenis_kelamin' => 'P',
        ]);

        $this->assertDatabaseHas('users', ['id' => $kader1User->id, 'status' => 'aktif']);
        $this->assertDatabaseHas('users', ['id' => $kader2User->id, 'status' => 'aktif']);
        $this->assertDatabaseHas('anggota', ['nama_lengkap' => 'Ahmad Dahlan Muda']);
        $this->assertDatabaseHas('anggota', ['nama_lengkap' => 'Siti Walidah Khadijah']);

        // =========================================================================
        // FASE 2: Inisiasi Event Baitul Arqam & Konfigurasi Bobot/Grade
        // =========================================================================
        $event = Event::create([
            'nama_event' => 'Baitul Arqam Instruktur Karanganyar 2026',
            'deskripsi' => 'Perkaderan Utama PDM Karanganyar',
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-12',
            'lokasi' => 'Pusdiklat Darul Arqam',
            'kuota' => 50,
            'status' => 'berlangsung',
            'created_by' => $admin->id,
            'bobot_posttest' => 30,
            'bobot_psikomotor' => 25,
            'bobot_afektif' => 20,
            'bobot_keaktifan' => 15,
            'bobot_pretest' => 10,
            'syarat_presensi' => 75,
            'gunakan_grade' => true,
        ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'nama_event' => 'Baitul Arqam Instruktur Karanganyar 2026',
            'gunakan_grade' => 1,
        ]);

        // =========================================================================
        // FASE 3: Enrollment Peserta & Otomasi Token QR Presensi
        // =========================================================================
        $qrService = app(QrService::class);

        $peserta1 = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $kader1->id,
            'status_kehadiran' => 'terdaftar',
            'qr_token' => $qrService->generateToken(),
        ]);

        $peserta2 = EventPeserta::create([
            'event_id' => $event->id,
            'anggota_id' => $kader2->id,
            'status_kehadiran' => 'terdaftar',
            'qr_token' => $qrService->generateToken(),
        ]);

        $this->assertNotNull($peserta1->qr_token);
        $this->assertNotNull($peserta2->qr_token);
        $this->assertSame(2, $event->peserta()->count());

        // =========================================================================
        // FASE 4: Penyiapan Sesi Agenda & Unggah Materi
        // =========================================================================
        $sesi1 = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi 1: Matan Keyakinan dan Cita-cita Hidup Muhammadiyah',
            'jenis' => 'materi',
            'tanggal' => '2026-10-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'pemateri' => 'Dr. H. Tafsir, M.Ag',
            'status' => 'aktif',
            'urutan' => 1,
        ]);

        $sesi2 = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi 2: Fiqih Ibadah Praktis Menurut Tarjih',
            'jenis' => 'materi',
            'tanggal' => '2026-10-10',
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:00',
            'pemateri' => 'Majelis Tarjih PDM',
            'status' => 'aktif',
            'urutan' => 2,
        ]);

        $sesi3 = EventSesi::create([
            'event_id' => $event->id,
            'nama_sesi' => 'Sesi 3: Dinamika Lapangan & Outbound',
            'jenis' => 'outbound',
            'tanggal' => '2026-10-11',
            'jam_mulai' => '06:00',
            'jam_selesai' => '08:00',
            'status' => 'aktif',
            'urutan' => 3,
        ]);

        $this->assertSame(3, $event->sesi()->count());

        // Peserta dapat melihat jadwal sesi di portal kehadiran
        $this->actingAs($kader1User)
            ->get(route('portal.kehadiran.index', $event))
            ->assertOk()
            ->assertSee('Matan Keyakinan');

        // =========================================================================
        // FASE 5: Presensi Live Barcode Scanner (Simulasi Presensi Hari-H)
        // =========================================================================
        $qr1 = $qrService->payload($peserta1);
        $qr2 = $qrService->payload($peserta2);

        // Peserta 1 hadir di SEMUA 3 SESI (100% kehadiran)
        foreach ([$sesi1, $sesi2, $sesi3] as $sesi) {
            $this->actingAs($admin)
                ->postJson(route('admin.events.absensi.process', $event), [
                    'sesi_id' => $sesi->id,
                    'qr_code' => $qr1,
                ])
                ->assertJson(['status' => 'success', 'nama' => 'Ahmad Dahlan Muda']);
        }

        // Peserta 2 HANYA hadir di Sesi 1 (1 dari 3 sesi = 33.3% kehadiran -> < 75%)
        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.process', $event), [
                'sesi_id' => $sesi1->id,
                'qr_code' => $qr2,
            ])
            ->assertJson(['status' => 'success', 'nama' => 'Siti Walidah Khadijah']);

        // Cek penolakan duplikasi scan
        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.process', $event), [
                'sesi_id' => $sesi1->id,
                'qr_code' => $qr1,
            ])
            ->assertJson(['status' => 'duplicate', 'nama' => 'Ahmad Dahlan Muda']);

        // Cek penolakan invalid QR token (return HTTP 422 dengan status error)
        $this->actingAs($admin)
            ->postJson(route('admin.events.absensi.process', $event), [
                'sesi_id' => $sesi1->id,
                'qr_code' => 'invalid-fake-token-12345',
            ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'QR tidak valid']);

        // Verifikasi kalkulasi kehadiran otomatis pada PenilaianAkhir
        $p1 = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $kader1->id)->first();
        $this->assertSame('100.00', $p1->nilai_kehadiran);

        $p2 = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $kader2->id)->first();
        $this->assertSame('33.33', $p2->nilai_kehadiran);

        // =========================================================================
        // FASE 6: Asesmen CBT Kognitif (Pretest & Posttest)
        // =========================================================================
        $soalPretest = Soal::create([
            'event_id' => $event->id,
            'tipe' => 'pretest',
            'teks_soal' => 'Kapan Muhammadiyah didirikan?',
            'urutan' => 1,
        ]);
        $pilihanPretestA = PilihanJawaban::create([
            'soal_id' => $soalPretest->id,
            'label' => 'A',
            'teks' => '18 November 1912',
            'is_benar' => true,
        ]);
        $pilihanPretestB = PilihanJawaban::create([
            'soal_id' => $soalPretest->id,
            'label' => 'B',
            'teks' => '17 Agustus 1945',
            'is_benar' => false,
        ]);

        $soalPosttest = Soal::create([
            'event_id' => $event->id,
            'tipe' => 'posttest',
            'teks_soal' => 'Tujuan Muhammadiyah adalah menegakkan dan menjunjung tinggi...',
            'urutan' => 1,
        ]);
        $pilihanPosttestA = PilihanJawaban::create([
            'soal_id' => $soalPosttest->id,
            'label' => 'A',
            'teks' => 'Agama Islam sehingga terwujud masyarakat Islam yang sebenar-benarnya',
            'is_benar' => true,
        ]);
        $pilihanPosttestB = PilihanJawaban::create([
            'soal_id' => $soalPosttest->id,
            'label' => 'B',
            'teks' => 'Kekuasaan politik',
            'is_benar' => false,
        ]);

        // Aktifkan sesi tes kognitif
        SesiTes::create(['event_id' => $event->id, 'tipe' => 'pretest', 'status' => 'aktif', 'durasi_menit' => 30]);
        SesiTes::create(['event_id' => $event->id, 'tipe' => 'posttest', 'status' => 'aktif', 'durasi_menit' => 30]);

        // Peserta 1 mulai & submit Pretest (jawaban benar -> 100)
        $this->actingAs($kader1User)->get(route('portal.tes.show', [$event, 'pretest']))->assertOk();
        $this->actingAs($kader1User)
            ->post(route('portal.tes.submit', [$event, 'pretest']), [
                'answers' => [['soal_id' => $soalPretest->id, 'pilihan_id' => $pilihanPretestA->id]],
            ])
            ->assertRedirect(route('portal.tes.index'));

        // Peserta 1 mulai & submit Posttest (jawaban benar -> 100)
        $this->actingAs($kader1User)->get(route('portal.tes.show', [$event, 'posttest']))->assertOk();
        $this->actingAs($kader1User)
            ->post(route('portal.tes.submit', [$event, 'posttest']), [
                'answers' => [['soal_id' => $soalPosttest->id, 'pilihan_id' => $pilihanPosttestA->id]],
            ])
            ->assertRedirect(route('portal.tes.index'));

        // Peserta 2 mulai & submit Pretest (jawaban salah -> 0)
        $this->actingAs($kader2User)->get(route('portal.tes.show', [$event, 'pretest']))->assertOk();
        $this->actingAs($kader2User)
            ->post(route('portal.tes.submit', [$event, 'pretest']), [
                'answers' => [['soal_id' => $soalPretest->id, 'pilihan_id' => $pilihanPretestB->id]],
            ])
            ->assertRedirect(route('portal.tes.index'));

        // Peserta 2 mulai & submit Posttest (jawaban salah -> 0)
        $this->actingAs($kader2User)->get(route('portal.tes.show', [$event, 'posttest']))->assertOk();
        $this->actingAs($kader2User)
            ->post(route('portal.tes.submit', [$event, 'posttest']), [
                'answers' => [['soal_id' => $soalPosttest->id, 'pilihan_id' => $pilihanPosttestB->id]],
            ])
            ->assertRedirect(route('portal.tes.index'));

        // =========================================================================
        // FASE 7: Poin Keaktifan & Penilaian Lapangan
        // =========================================================================
        // Peserta 1 aktif di forum (tanya)
        $this->actingAs($admin)->post(route('admin.events.keaktifan.store', $event), [
            'anggota_id' => $kader1->id,
            'jenis' => 'tanya',
            'keterangan' => 'Aktif bertanya di sesi Matan Keyakinan',
        ])->assertRedirect();

        // Peserta 2 pasif di forum
        $this->actingAs($admin)->post(route('admin.events.keaktifan.store', $event), [
            'anggota_id' => $kader2->id,
            'jenis' => 'tanya',
            'keterangan' => 'Bertanya sekali',
        ])->assertRedirect();

        // Admin membagi kelompok outbound secara otomatis (seimbang gender)
        $this->actingAs($admin)->post(route('admin.events.outbound.generate', $event), [
            'jumlah_kelompok' => 1,
            'kriteria' => 'seimbang_gender',
        ])->assertRedirect();

        $this->assertSame(1, $event->kelompokOutbound()->count());

        // Admin dapat mengunduh daftar kelompok outbound PDF
        $this->actingAs($admin)
            ->get(route('admin.events.outbound.pdf', $event))
            ->assertOk();

        // =========================================================================
        // FASE 8: Penilaian Psikomotorik & Sikap Afektif
        // =========================================================================
        $nilaiService = app(NilaiService::class);

        $penilaian1 = PenilaianAkhir::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $kader1->id,
        ]);
        $penilaian1->update([
            'nilai_psikomotor' => 95.0,
            'nilai_afektif' => 92.0,
        ]);

        $penilaian2 = PenilaianAkhir::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $kader2->id,
        ]);
        $penilaian2->update([
            'nilai_psikomotor' => 60.0,
            'nilai_afektif' => 65.0,
        ]);

        // Buat satu butir RTL wajib untuk event ini
        RtlSoal::create([
            'event_id' => $event->id,
            'pertanyaan' => 'Rencana Aksi Pengabdian di Cabang/Ranting',
            'tipe' => 'essay',
            'urutan' => 1,
        ]);

        // =========================================================================
        // FASE 9: Kalkulasi Yudisium & Penentuan Kelulusan Otomatis
        // =========================================================================
        $nilaiService->hitungNilaiAkhirDanRanking($event, [$kader1->id, $kader2->id]);

        $penilaian1 = $penilaian1->fresh();
        $penilaian2 = $penilaian2->fresh();

        // Peserta 1: Kehadiran 100% >= 75%, RTL belum disetujui -> status "Lulus Bersyarat", Nilai Akhir >= 85
        $this->assertGreaterThanOrEqual(85.0, (float) $penilaian1->nilai_akhir);
        $this->assertSame('Lulus Bersyarat', $penilaian1->status_kelulusan);

        // Peserta 2: Kehadiran 33.3% < 75% -> status "Tidak Lulus", Grade NULL
        $this->assertSame('Tidak Lulus', $penilaian2->status_kelulusan);
        $this->assertNull($penilaian2->grade);

        // Verifikasi Ranking: Peserta 1 peringkat 1, Peserta 2 peringkat 2
        $ranking = PenilaianAkhir::where('event_id', $event->id)
            ->orderByDesc('nilai_akhir')
            ->pluck('anggota_id')
            ->toArray();
        $this->assertSame([$kader1->id, $kader2->id], $ranking);

        // =========================================================================
        // FASE 10: Penyerahan RTL & Upgrade Menjadi Lulus Murni
        // =========================================================================
        $rtl1 = Rtl::create([
            'event_id' => $event->id,
            'anggota_id' => $kader1->id,
            'status' => 'disetujui',
        ]);

        // Hitung ulang yudisium setelah RTL disetujui
        $nilaiService->hitungNilaiAkhirDanRanking($event, [$kader1->id, $kader2->id]);
        $penilaian1 = $penilaian1->fresh();

        // Peserta 1 sekarang LULUS MURNI ('Lulus') dan mendapatkan Grade A
        $this->assertSame('Lulus', $penilaian1->status_kelulusan);
        $this->assertSame('A', $penilaian1->grade);

        // Ubah status event menjadi 'selesai' untuk penerbitan dokumen resmi
        $event->update(['status' => 'selesai']);

        // =========================================================================
        // FASE 11: Validasi Penerbitan Dokumen Resmi & Otorisasi Sertifikat
        // =========================================================================
        // Admin dapat mengakses laporan dan berita acara evaluasi
        $this->actingAs($admin)
            ->get(route('admin.events.report', $event))
            ->assertOk();

        // Admin dapat mengekspor rekap nilai lengkap ke Excel (XLSX)
        $this->actingAs($admin)
            ->get(route('admin.events.exportExcel', $event))
            ->assertOk();

        // Admin dapat mengunduh batch ID Card PDF peserta
        $this->actingAs($admin)
            ->get(route('admin.events.idcards.batch', $event))
            ->assertOk();

        // Peserta 1 (LULUS MURNI) DAPAT mengunduh sertifikat resmi
        $this->actingAs($kader1User)
            ->get(route('portal.sertifikat.download', $event))
            ->assertOk();

        // Peserta 2 (TIDAK LULUS) DITOLAK mengunduh sertifikat resmi (403 Forbidden)
        $this->actingAs($kader2User)
            ->get(route('portal.sertifikat.download', $event))
            ->assertForbidden();
    }
}
