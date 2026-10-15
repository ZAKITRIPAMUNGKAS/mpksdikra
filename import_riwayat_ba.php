<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

/**
 * Script CLI Mandiri untuk Impor Riwayat Baitul Arqam (cPanel / Terminal)
 *
 * Cara Menjalankan di Terminal cPanel:
 *   php import_riwayat_ba.php
 * atau dengan opsi buat kader otomatis jika belum terdaftar:
 *   php import_riwayat_ba.php --create-missing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$args = $_SERVER['argv'] ?? [];
$createMissing = in_array('--create-missing', $args, true);
$filePath = 'DATA BA.xlsx';

foreach ($args as $idx => $arg) {
    if ($idx > 0 && ! str_starts_with($arg, '--')) {
        $filePath = $arg;
        break;
    }
}

$exitCode = Artisan::call('arqam:import-riwayat-ba', [
    'file' => $filePath,
    '--create-missing' => $createMissing,
]);

echo Artisan::output();
exit($exitCode);
