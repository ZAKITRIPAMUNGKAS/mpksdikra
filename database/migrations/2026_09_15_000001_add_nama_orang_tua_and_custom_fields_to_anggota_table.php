<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('nama_ayah')->nullable()->after('pekerjaan');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->json('custom_fields')->nullable()->after('harapan_ba');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn(['nama_ayah', 'nama_ibu', 'custom_fields']);
        });
    }
};
