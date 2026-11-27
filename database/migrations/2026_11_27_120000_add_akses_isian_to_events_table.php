<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status buka/tutup pengisian online Afektif & Angket Evaluasi per kegiatan.
     * Default 'aktif' agar kegiatan yang sedang berjalan tidak tiba-tiba tertutup.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('afektif_status', 10)->default('aktif')->after('rtl_wajib');
            $table->string('angket_status', 10)->default('aktif')->after('afektif_status');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['afektif_status', 'angket_status']);
        });
    }
};
