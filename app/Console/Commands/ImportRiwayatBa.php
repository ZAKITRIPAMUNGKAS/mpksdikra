<?php

namespace App\Console\Commands;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportRiwayatBa extends Command
{
    protected $signature = 'arqam:import-riwayat-ba 
                            {file=DATA BA.xlsx : Path berkas Excel DATA BA.xlsx} 
                            {--create-missing : Buat data akun kader otomatis jika nama belum ada di pangkalan data}';

    protected $description = 'Impor riwayat keikutsertaan Baitul Arqam dari berkas Excel (DATA BA.xlsx) dan hubungkan ke data kader';

    public function handle(): int
    {
        $filePath = $this->argument('file');
        if (! file_exists($filePath)) {
            $filePath = base_path($filePath);
        }

        if (! file_exists($filePath)) {
            $this->error("Berkas Excel tidak ditemukan di: {$filePath}");

            return self::FAILURE;
        }

        $createMissing = (bool) $this->option('create-missing');

        $this->info('=== MEMULAI IMPOR RIWAYAT BAITUL ARQAM ===');
        $this->line("Berkas : {$filePath}");
        $this->line('Mode Buat Kader Baru : '.($createMissing ? '<fg=green>AKTIF (--create-missing)</>' : '<fg=yellow>NONAKTIF (Hanya tautkan kader yang cocok)</>'));
        $this->newLine();

        $adminId = User::where('role', 'admin')->first()?->id ?? 1;

        try {
            $spreadsheet = IOFactory::load($filePath);
        } catch (\Throwable $e) {
            $this->error('Gagal membaca berkas Excel: '.$e->getMessage());

            return self::FAILURE;
        }

        $sheetConfigs = [
            'Sheet1' => [
                'default_nama' => 'Baitul Arqam Pimpinan Majelis & Lembaga PDM Karanganyar',
                'default_waktu' => '24-25 Agustus 2024',
                'default_lokasi' => 'BLK Karanganyar',
                'tgl_mulai' => '2024-08-24',
                'tgl_selesai' => '2024-08-25',
            ],
            'Sheet2' => [
                'default_nama' => 'Baitul Arqam PCM-PCA se Kabupaten Karanganyar',
                'default_waktu' => '19-20 September 2025',
                'default_lokasi' => 'Parai Beach Resort, Pacitan',
                'tgl_mulai' => '2025-09-19',
                'tgl_selesai' => '2025-09-20',
            ],
            'Sheet3' => [
                'default_nama' => 'Baitul Arqam Pimpinan AUM Pendidikan PDM Karanganyar',
                'default_waktu' => '26-27 Juli 2025',
                'default_lokasi' => 'Jungwok Resort, Gunungkidul, D.I. Yogyakarta',
                'tgl_mulai' => '2025-07-26',
                'tgl_selesai' => '2025-07-27',
            ],
            'Sheet4' => [
                'default_nama' => 'Baitul Arqam PCM Karanganyar',
                'default_waktu' => '1-2 Mei 2026',
                'default_lokasi' => 'Omah Nginep Tawangmangu',
                'tgl_mulai' => '2026-05-01',
                'tgl_selesai' => '2026-05-02',
            ],
        ];

        $totalMatched = 0;
        $totalCreated = 0;
        $totalUnmatched = 0;

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) {
                continue;
            }

            $highestRow = $sheet->getHighestRow();
            $cfg = $sheetConfigs[$sheetName] ?? null;

            // Ekstrak metadata dinamis dari sheet bila ada
            $namaBa = '';
            $waktu = '';
            $tempat = '';

            for ($r = 1; $r <= min(5, $highestRow); $r++) {
                for ($c = 'B'; $c <= 'G'; $c++) {
                    $val = trim((string) $sheet->getCell($c.$r)->getValue());
                    if (stripos($val, 'nama ba') !== false) {
                        $nextCol = chr(ord($c) + 1);
                        $namaBa = trim((string) $sheet->getCell($nextCol.$r)->getValue());
                    }
                    if (stripos($val, 'waktu') !== false) {
                        $nextCol = chr(ord($c) + 1);
                        $waktu = trim((string) $sheet->getCell($nextCol.$r)->getValue());
                    }
                    if (stripos($val, 'tempat') !== false) {
                        $nextCol = chr(ord($c) + 1);
                        $tempat = trim((string) $sheet->getCell($nextCol.$r)->getValue());
                    }
                }
            }

            $namaEvent = ! empty($namaBa) ? str_ireplace('Baitul Arqom', 'Baitul Arqam', $namaBa) : ($cfg['default_nama'] ?? "Baitul Arqam {$sheetName}");
            $lokasi = ! empty($tempat) ? $tempat : ($cfg['default_lokasi'] ?? 'Karanganyar');
            $tglMulai = $cfg['tgl_mulai'] ?? now()->toDateString();
            $tglSelesai = $cfg['tgl_selesai'] ?? $tglMulai;

            // Pastikan event ada di database
            $event = Event::firstOrCreate(
                ['nama_event' => $namaEvent],
                [
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'lokasi' => $lokasi,
                    'deskripsi' => "Arsip keikutsertaan Baitul Arqam resmi MPKSDI PDM Karanganyar ({$waktu}).",
                    'kuota' => max(50, $highestRow),
                    'status' => 'selesai',
                    'created_by' => $adminId,
                ]
            );

            $this->info('--------------------------------------------------');
            $this->info("Event: [{$event->id}] {$event->nama_event}");
            $this->line("Lokasi: {$event->lokasi} | Tanggal: {$event->tanggal_mulai?->format('d/m/Y')} - {$event->tanggal_selesai?->format('d/m/Y')}");

            // Ambil seluruh kader terkini untuk matching cepat
            $anggotaList = Anggota::with('user')->get();

            $sheetMatched = 0;
            $sheetCreated = 0;
            $sheetUnmatched = 0;

            for ($r = 1; $r <= $highestRow; $r++) {
                $rawName = trim((string) $sheet->getCell('A'.$r)->getValue());
                if ($rawName === '' || strtolower($rawName) === 'nama' || stripos($rawName, 'rekap') !== false) {
                    continue;
                }

                // Ekstrak info tambahan per sheet
                $extraInfo = [];
                if ($sheetName === 'Sheet1') {
                    $extraInfo['majelis'] = trim((string) $sheet->getCell('B'.$r)->getValue());
                } elseif ($sheetName === 'Sheet2') {
                    $extraInfo['pcm'] = trim((string) $sheet->getCell('B'.$r)->getValue());
                    $extraInfo['gender'] = trim((string) $sheet->getCell('C'.$r)->getValue());
                    $extraInfo['unsur'] = trim((string) $sheet->getCell('E'.$r)->getValue());
                } elseif ($sheetName === 'Sheet3') {
                    $extraInfo['alamat'] = trim((string) $sheet->getCell('B'.$r)->getValue());
                    $extraInfo['gender'] = trim((string) $sheet->getCell('C'.$r)->getValue());
                    $extraInfo['aum'] = trim((string) $sheet->getCell('D'.$r)->getValue());
                }

                $cleanTarget = self::cleanName($rawName);

                // Cari kecocokan di database
                $matchedAnggota = $anggotaList->first(function ($a) use ($rawName, $cleanTarget) {
                    if (strcasecmp($a->nama_lengkap, $rawName) === 0) {
                        return true;
                    }
                    $aClean = self::cleanName($a->nama_lengkap);

                    return $aClean === $cleanTarget;
                });

                if ($matchedAnggota) {
                    $this->hubungkanKaderKeEvent($event, $matchedAnggota);
                    $sheetMatched++;
                    $totalMatched++;
                } elseif ($createMissing) {
                    $newAnggota = $this->buatKaderBaru($rawName, $extraInfo, $adminId);
                    $this->hubungkanKaderKeEvent($event, $newAnggota);
                    $sheetCreated++;
                    $totalCreated++;
                } else {
                    $sheetUnmatched++;
                    $totalUnmatched++;
                }
            }

            $this->line("  ✓ Cocok & Terhubung : <fg=green>{$sheetMatched}</>");
            if ($createMissing) {
                $this->line("  + Dibuat Otomatis   : <fg=cyan>{$sheetCreated}</>");
            } else {
                $this->line("  ? Belum Terdaftar   : <fg=yellow>{$sheetUnmatched}</>");
            }
        }

        $this->newLine();
        $this->info('==================================================');
        $this->info('IMPOR RIWAYAT BAITUL ARQAM SELESAI!');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Kader Berhasil Dihubungkan', $totalMatched],
                ['Kader Baru Dibuat Otomatis', $totalCreated],
                ['Nama Belum Ada di Database', $totalUnmatched],
            ]
        );

        if ($totalUnmatched > 0 && ! $createMissing) {
            $this->comment("Tips: Jika ingin membuat akun kader otomatis untuk {$totalUnmatched} nama yang belum ada, jalankan:");
            $this->line("<fg=cyan>php artisan arqam:import-riwayat-ba \"{$filePath}\" --create-missing</>");
        }

        return self::SUCCESS;
    }

    /**
     * Hubungkan data kader ke EventPeserta & PenilaianAkhir Lulus
     */
    private function hubungkanKaderKeEvent(Event $event, Anggota $anggota): void
    {
        // 1. Catat ke tabel event_peserta
        EventPeserta::firstOrCreate(
            [
                'event_id' => $event->id,
                'anggota_id' => $anggota->id,
            ],
            [
                'qr_token' => Str::random(32),
                'status_aktif' => true,
            ]
        );

        // 2. Catat status kelulusan di penilaian_akhir
        PenilaianAkhir::updateOrCreate(
            [
                'event_id' => $event->id,
                'anggota_id' => $anggota->id,
            ],
            [
                'status_kelulusan' => 'Lulus',
                'nilai_akhir' => 85.00,
                'grade' => 'A',
            ]
        );

        // 3. Simpan riwayat terstruktur di custom_fields anggota
        $custom = $anggota->custom_fields ?? [];
        $riwayat = $custom['riwayat_ba'] ?? [];

        $entry = [
            'event_id' => $event->id,
            'nama_event' => $event->nama_event,
            'event' => $event->nama_event,
            'lokasi' => $event->lokasi,
            'tanggal' => $event->tanggal_mulai?->format('d/m/Y').' - '.$event->tanggal_selesai?->format('d/m/Y'),
            'status' => 'Lulus',
        ];

        $alreadyHas = false;
        foreach ($riwayat as $r) {
            if (isset($r['event']) && strcasecmp($r['event'], $entry['event']) === 0) {
                $alreadyHas = true;
                break;
            }
        }

        if (! $alreadyHas) {
            $riwayat[] = $entry;
            $custom['riwayat_ba'] = $riwayat;
            $anggota->custom_fields = $custom;
            $anggota->save();
        }
    }

    /**
     * Buat data Anggota dan User baru secara aman
     */
    private function buatKaderBaru(string $rawName, array $extra, int $adminId): Anggota
    {
        $uniqueSuffix = rand(1000, 9999);
        $safeUsername = 'kader_'.Str::slug(self::cleanName($rawName), '_').'_'.$uniqueSuffix;
        $email = $safeUsername.'@ba-arsip.mpksdi.id';
        $defaultPass = Hash::make('12345678');

        $user = User::create([
            'name' => $rawName,
            'email' => $email,
            'password' => $defaultPass,
            'role' => 'anggota',
            'status' => 'aktif',
            'verified_at' => now(),
        ]);

        $gender = 'L';
        if (! empty($extra['gender'])) {
            $gLower = strtolower($extra['gender']);
            if (str_contains($gLower, 'perempuan') || str_contains($gLower, 'wanita') || $gLower === 'p') {
                $gender = 'P';
            }
        }

        $pcm = ! empty($extra['pcm']) ? $extra['pcm'] : 'Karanganyar';
        $jabatanAum = ! empty($extra['aum']) ? $extra['aum'] : (! empty($extra['majelis']) ? 'Majelis/Lembaga '.$extra['majelis'] : null);
        $alamat = ! empty($extra['alamat']) ? $extra['alamat'] : 'Karanganyar';
        $dummyNik = '3313'.str_pad((string) mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);

        return Anggota::create([
            'user_id' => $user->id,
            'nama_lengkap' => $rawName,
            'nik' => $dummyNik,
            'jenis_kelamin' => $gender,
            'alamat_rumah' => $alamat,
            'tempat_lahir' => 'Karanganyar',
            'tanggal_lahir' => '1985-01-01',
            'status_pernikahan' => 'Menikah',
            'desa_kelurahan' => '-',
            'kecamatan' => $pcm,
            'kabupaten' => 'Karanganyar',
            'pcm' => $pcm,
            'no_wa' => '08'.mt_rand(1000000000, 9999999999),
            'email' => $email,
            'jabatan_aum' => $jabatanAum,
            'catatan_verifikasi' => 'Terverifikasi otomatis via arsip impor Baitul Arqam MPKSDI',
        ]);
    }

    /**
     * Bersihkan nama dari gelar dan tanda baca untuk pencocokan akurat
     */
    public static function cleanName(string $name): string
    {
        $clean = trim($name);

        // Hapus gelar depan (bisa bertumpuk misal K.H. H. Drs. ...)
        for ($i = 0; $i < 4; $i++) {
            $clean = preg_replace('/^(?:k\.?\s*h|drs|dra|prof|ustadz|ust|haji|hajjah|hj|ir|dr|h)\.?\s+/i', '', $clean);
        }

        // Hapus gelar belakang setelah koma jika diawali tanda gelar umum (S., M., Dr., SE, SH, dll.)
        $clean = preg_replace('/,\s*(?:s\.|m\.|dr\.|ph\.d|se|sh|st|spd|mpd|sag|mm|msi|ssos|skom|spsi|spdi|lc|gr|apt|ns|prof|ir|ipm|mpdsi|mh|sud).*$/i', '', $clean);

        // Hapus gelar singkatan di dalam teks (dengan atau tanpa titik)
        $clean = preg_replace('/\b(?:s\.ag|sag|s\.pd|spd|s\.pd\.i|spdi|s\.pd\.sd|spdsd|m\.pd|mpd|m\.p\.i|mpi|m\.si|msi|s\.t|st|s\.e|se|s\.kom|skom|s\.h|sh|s\.psi|spsi|s\.sos|ssos|m\.m|mm|m\.ag|mag|lc\.|lc|gr\.|gr|s\.farm|apt\.|s\.gz|s\.kep|s\.ars|s\.st|s\.s|s\.si|m\.pd\.i|mpdsi|mh\.|mh|s\.ud|sud)\b/i', '', $clean);

        // Ganti karakter non alfanumerik menjadi spasi
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $clean);

        // Buang akhiran seperti "S H" atau "S Pd" yang terpisah spasi di ujung nama
        $clean = preg_replace('/\s+(?:s|m|a|dr)\s+[a-z]{1,4}$/i', '', $clean);

        return strtolower(trim(preg_replace('/\s+/', ' ', $clean)));
    }
}
