<?php

use App\Models\FormPertanyaan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('form_pertanyaan')) {
            $row = DB::table('form_pertanyaan')->where('kode', 'keaktifan_ortom')->first();
            if ($row) {
                $opsi = json_decode($row->opsi, true) ?: [];
                if (! in_array('Ikatan Mahasiswa Muhammadiyah', $opsi, true)) {
                    $pos = array_search('Hizbul Wathon', $opsi, true);
                    if ($pos === false) {
                        $pos = array_search('Tidak aktif', $opsi, true);
                    }
                    if ($pos !== false) {
                        array_splice($opsi, $pos, 0, ['Ikatan Mahasiswa Muhammadiyah']);
                    } else {
                        $opsi[] = 'Ikatan Mahasiswa Muhammadiyah';
                    }
                    DB::table('form_pertanyaan')
                        ->where('kode', 'keaktifan_ortom')
                        ->update(['opsi' => json_encode(array_values($opsi))]);
                }
            }
            if (class_exists(FormPertanyaan::class)) {
                FormPertanyaan::clearConfigCache();
            }
        }
    }

    public function down(): void {}
};
