<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('nama_penandatangan_kiri')->nullable()->after('jabatan_penandatangan_sertifikat');
            $table->string('jabatan_penandatangan_kiri')->nullable()->after('nama_penandatangan_kiri');
            $table->string('keterangan_penandatangan_kiri')->nullable()->after('jabatan_penandatangan_kiri');
            $table->string('ttd_kiri_path')->nullable()->after('keterangan_penandatangan_kiri');
            $table->string('ttd_kanan_path')->nullable()->after('ttd_kiri_path');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'nama_penandatangan_kiri',
                'jabatan_penandatangan_kiri',
                'keterangan_penandatangan_kiri',
                'ttd_kiri_path',
                'ttd_kanan_path',
            ]);
        });
    }
};
