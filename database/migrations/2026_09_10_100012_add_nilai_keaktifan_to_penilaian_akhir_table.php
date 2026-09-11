<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_akhir', function (Blueprint $table) {
            $table->decimal('nilai_keaktifan', 5, 2)->default(0)->after('nilai_psikomotor');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian_akhir', function (Blueprint $table) {
            $table->dropColumn('nilai_keaktifan');
        });
    }
};
