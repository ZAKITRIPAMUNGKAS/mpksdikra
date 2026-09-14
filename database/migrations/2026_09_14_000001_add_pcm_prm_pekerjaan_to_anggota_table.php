<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('pcm')->nullable()->after('kecamatan');
            $table->string('prm')->nullable()->after('pcm');
            $table->string('pekerjaan')->nullable()->after('jabatan_aum');
            $table->json('jabatan_muhammadiyah')->nullable()->after('keaktifan_muhammadiyah');
            $table->json('jabatan_ortom')->nullable()->after('keaktifan_ortom');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn([
                'pcm',
                'prm',
                'pekerjaan',
                'jabatan_muhammadiyah',
                'jabatan_ortom',
            ]);
        });
    }
};
