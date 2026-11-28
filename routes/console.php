<?php

use App\Models\Event;
use App\Support\TtdTransparan;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sertifikat:ttd-transparan', function () {
    $jumlah = 0;
    foreach (Event::whereNotNull('ttd_kiri_path')->orWhereNotNull('ttd_kanan_path')->get() as $event) {
        foreach (['ttd_kiri_path', 'ttd_kanan_path'] as $kolom) {
            if ($event->{$kolom} && ($baru = TtdTransparan::prosesUlang($event->{$kolom}))) {
                $event->forceFill([$kolom => $baru])->save();
                $this->line("{$event->nama_event}: {$kolom} diproses.");
                $jumlah++;
            }
        }
    }
    $this->info("{$jumlah} tanda tangan dibuat transparan.");
})->purpose('Buat latar putih TTD sertifikat yang sudah diunggah menjadi transparan');
