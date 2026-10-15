<?php

namespace Tests\Feature;

use App\Console\Commands\ImportRiwayatBa;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportRiwayatBaTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_name_normalizes_honorifics_and_degrees(): void
    {
        $this->assertEquals('muhammad arqam', ImportRiwayatBa::cleanName('Drs. H. Muhammad Arqam, M.Ag.'));
        $this->assertEquals('ahmad dahlan', ImportRiwayatBa::cleanName('K.H. Ahmad Dahlan, S.Pd.I'));
        $this->assertEquals('siti walidah', ImportRiwayatBa::cleanName('Hj. Siti Walidah, S.Pd'));
        $this->assertEquals('muh yusron', ImportRiwayatBa::cleanName('Muh, Yusron'));
        $this->assertEquals('sarwana', ImportRiwayatBa::cleanName('Sarwana S,H'));
        $this->assertEquals('budi santoso', ImportRiwayatBa::cleanName('Ir. Budi Santoso, M.T.'));
    }

    public function test_import_riwayat_ba_links_existing_anggota_and_creates_event_peserta(): void
    {
        // Setup admin user
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // Setup existing anggota
        $userKader = User::factory()->create([
            'role' => 'anggota',
            'status' => 'aktif',
            'name' => 'Drs. H. Muhammad Arqam, M.Ag.',
        ]);

        $anggota = Anggota::factory()->create([
            'user_id' => $userKader->id,
            'nama_lengkap' => 'Drs. H. Muhammad Arqam, M.Ag.',
            'pcm' => 'Karanganyar',
        ]);

        // Buat file Excel mockup
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');
        $sheet->setCellValue('A1', 'REKAPITULASI PESERTA BAITUL ARQOM PIMPINAN');
        $sheet->setCellValue('A2', 'Hari/Tanggal : Sabtu - Ahad, 24-25 Agustus 2024');
        $sheet->setCellValue('A3', 'Tempat : BLK Karanganyar');
        $sheet->setCellValue('A4', 'Nama');
        $sheet->setCellValue('B4', 'Majelis/Lembaga');
        $sheet->setCellValue('A5', 'H. Muhammad Arqam, M.Ag.');
        $sheet->setCellValue('B5', 'MPKSDI');

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_ba_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $this->artisan('arqam:import-riwayat-ba', [
            'file' => $tmpFile,
        ])->assertExitCode(0);

        // Verifikasi Event dibuat
        $event = Event::where('nama_event', 'like', '%Baitul Arqam%')->first();
        $this->assertNotNull($event);

        // Verifikasi EventPeserta dibuat
        $peserta = EventPeserta::where('event_id', $event->id)
            ->where('anggota_id', $anggota->id)
            ->first();
        $this->assertNotNull($peserta);

        // Verifikasi PenilaianAkhir dibuat dan status Lulus
        $penilaian = PenilaianAkhir::where('event_id', $event->id)
            ->where('anggota_id', $anggota->id)
            ->first();
        $this->assertNotNull($penilaian);
        $this->assertEquals('Lulus', $penilaian->status_kelulusan);

        // Verifikasi custom_fields riwayat_ba terisi
        $anggota->refresh();
        $this->assertIsArray($anggota->custom_fields);
        $this->assertArrayHasKey('riwayat_ba', $anggota->custom_fields);
        $this->assertEquals($event->id, $anggota->custom_fields['riwayat_ba'][0]['event_id']);
        $this->assertEquals('Lulus', $anggota->custom_fields['riwayat_ba'][0]['status']);

        @unlink($tmpFile);
    }

    public function test_import_riwayat_ba_creates_missing_anggota_when_flag_enabled(): void
    {
        User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'Majelis/Lembaga');
        $sheet->setCellValue('A2', 'Kader Baru Belum Ada');
        $sheet->setCellValue('B2', 'Majelis Dikdasmen');

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_ba_create_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $this->artisan('arqam:import-riwayat-ba', [
            'file' => $tmpFile,
            '--create-missing' => true,
        ])->assertExitCode(0);

        $anggotaBaru = Anggota::where('nama_lengkap', 'Kader Baru Belum Ada')->first();
        $this->assertNotNull($anggotaBaru);
        $this->assertNotNull($anggotaBaru->user);
        $this->assertEquals('aktif', $anggotaBaru->user->status);

        @unlink($tmpFile);
    }
}
