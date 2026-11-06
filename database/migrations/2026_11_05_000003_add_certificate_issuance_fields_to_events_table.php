<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->date('tanggal_terbit_sertifikat')->nullable()->after('format_nomor_sertifikat');
            $table->string('nama_penandatangan_sertifikat')->nullable()->after('tanggal_terbit_sertifikat');
            $table->string('nbm_penandatangan_sertifikat', 50)->nullable()->after('nama_penandatangan_sertifikat');
            $table->string('jabatan_penandatangan_sertifikat')->nullable()->after('nbm_penandatangan_sertifikat');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_terbit_sertifikat',
                'nama_penandatangan_sertifikat',
                'nbm_penandatangan_sertifikat',
                'jabatan_penandatangan_sertifikat',
            ]);
        });
    }
};
