<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peserta_tes_mulai', function (Blueprint $table) {
            $table->string('sumber', 10)->default('online')->after('tipe');
            $table->foreignId('diinput_oleh')->nullable()->after('waktu_selesai')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peserta_tes_mulai', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diinput_oleh');
            $table->dropColumn('sumber');
        });
    }
};
