<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->text('aktifitas_jamaah_masjid')->nullable()->after('organisasi_lain');
            $table->string('keaktifan_takmir', 20)->nullable()->after('aktifitas_jamaah_masjid');
            $table->string('jabatan_takmir')->nullable()->after('keaktifan_takmir');
            $table->text('keaktifan_kemasyarakatan')->nullable()->after('jabatan_takmir');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn([
                'aktifitas_jamaah_masjid',
                'keaktifan_takmir',
                'jabatan_takmir',
                'keaktifan_kemasyarakatan',
            ]);
        });
    }
};
