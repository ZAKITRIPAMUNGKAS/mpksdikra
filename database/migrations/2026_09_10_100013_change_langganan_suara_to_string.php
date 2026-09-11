<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn('langganan_suara_muhammadiyah');
        });

        Schema::table('anggota', function (Blueprint $table) {
            $table->string('langganan_suara_muhammadiyah')->nullable()->after('sumber_info_muhammadiyah');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn('langganan_suara_muhammadiyah');
        });

        Schema::table('anggota', function (Blueprint $table) {
            $table->boolean('langganan_suara_muhammadiyah')->nullable()->after('sumber_info_muhammadiyah');
        });
    }
};
