<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('pendidikan_terakhir', 50)->nullable()->after('pendidikan_sd');
            $table->string('pendidikan_smp')->nullable()->after('pendidikan_sd');
            $table->string('pendidikan_sma')->nullable()->after('pendidikan_smp');
            $table->string('pendidikan_s1')->nullable()->after('pendidikan_sma');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn([
                'pendidikan_terakhir',
                'pendidikan_smp',
                'pendidikan_sma',
                'pendidikan_s1',
            ]);
        });
    }
};
