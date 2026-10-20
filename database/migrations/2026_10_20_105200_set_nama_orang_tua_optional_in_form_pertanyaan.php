<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('form_pertanyaan')) {
            DB::table('form_pertanyaan')
                ->whereIn('kode', ['nama_ayah', 'nama_ibu'])
                ->update(['is_wajib' => false]);
        }
    }

    public function down(): void
    {
        // Tetap opsional
    }
};
