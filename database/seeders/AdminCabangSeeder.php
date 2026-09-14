<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * AdminCabangSeeder
 *
 * Membuat akun login untuk:
 *  - 1  Admin Daerah  (PDM Karanganyar)  — role: admin
 *  - 17 Admin Cabang  (per PCM)          — role: admin_cabang
 *
 * Jalankan: php artisan db:seed --class=AdminCabangSeeder
 *
 * ┌──────────────────────────────────────────────────────────────────┐
 * │  DAFTAR AKUN LOGIN                                               │
 * ├──────────────────────┬───────────────────────────┬──────────────┤
 * │  Email               │  Username / Email         │  Password    │
 * ├──────────────────────┼───────────────────────────┼──────────────┤
 * │  Admin Daerah        │  admin@mpksdi-kra.id      │  AdminPDM@25 │
 * │  PCM Colomadu        │  cabang.colomadu@...      │  CabangKra25 │
 * │  PCM Gondangrejo     │  cabang.gondangrejo@...   │  CabangKra25 │
 * │  ...dst              │  cabang.[kec]@mpksdi...   │  CabangKra25 │
 * └──────────────────────┴───────────────────────────┴──────────────┘
 */
class AdminCabangSeeder extends Seeder
{
    /** Password default admin cabang */
    private const PASS_CABANG = 'cabangjra';

    /** Password admin daerah */
    private const PASS_DAERAH = 'AdminPDM@25';

    public function run(): void
    {
        // Bersihkan data akun admin cabang lama jika ada format @
        User::where('role', 'admin_cabang')->delete();

        // ── 1. Admin Daerah (PDM) ─────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin PDM Karanganyar',
                'password' => Hash::make(self::PASS_DAERAH),
                'role' => 'admin',
                'status' => 'aktif',
                'no_wa' => '081226000001',
                'pcm' => null,
                'verified_at' => now(),
            ]
        );

        // ── 2. Admin Cabang per PCM (17 kecamatan) — Tanpa '@' ─────────
        $cabangList = [
            ['pcm' => 'Colomadu',     'wa' => '081226001001'],
            ['pcm' => 'Gondangrejo',  'wa' => '081226001002'],
            ['pcm' => 'Jaten',        'wa' => '081226001003'],
            ['pcm' => 'Jatipuro',     'wa' => '081226001004'],
            ['pcm' => 'Jatiyoso',     'wa' => '081226001005'],
            ['pcm' => 'Jenawi',       'wa' => '081226001006'],
            ['pcm' => 'Jumantono',    'wa' => '081226001007'],
            ['pcm' => 'Jumapolo',     'wa' => '081226001008'],
            ['pcm' => 'Karanganyar',  'wa' => '081226001009'],
            ['pcm' => 'Karangpandan', 'wa' => '081226001010'],
            ['pcm' => 'Kebakkramat',  'wa' => '081226001011'],
            ['pcm' => 'Kerjo',        'wa' => '081226001012'],
            ['pcm' => 'Matesih',      'wa' => '081226001013'],
            ['pcm' => 'Mojogedang',   'wa' => '081226001014'],
            ['pcm' => 'Ngargoyoso',   'wa' => '081226001015'],
            ['pcm' => 'Tasikmadu',    'wa' => '081226001016'],
            ['pcm' => 'Tawangmangu',  'wa' => '081226001017'],
        ];

        foreach ($cabangList as $cabang) {
            $username = strtolower(str_replace(' ', '', $cabang['pcm']));

            User::updateOrCreate(
                ['email' => $username],
                [
                    'name' => 'Admin PCM '.$cabang['pcm'],
                    'password' => Hash::make(self::PASS_CABANG),
                    'role' => 'admin_cabang',
                    'status' => 'aktif',
                    'no_wa' => $cabang['wa'],
                    'pcm' => $cabang['pcm'],
                    'verified_at' => now(),
                ]
            );
        }

        $this->command->info('✅  Admin Daerah (1) dan Admin Cabang (17) berhasil diperbarui (tanpa @).');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Username (Tanpa @)', 'Password', 'Wilayah PCM'],
            array_merge(
                [['Admin Daerah', 'admin', self::PASS_DAERAH, 'Semua Cabang (PDM)']],
                array_map(fn ($c) => [
                    'Admin Cabang',
                    strtolower(str_replace(' ', '', $c['pcm'])),
                    self::PASS_CABANG,
                    $c['pcm'],
                ], $cabangList)
            )
        );
    }
}
