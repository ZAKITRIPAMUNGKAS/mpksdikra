<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('bobot_kognitif', 5, 2)->nullable()->after('format_nomor_sertifikat');
            $table->decimal('bobot_psikomotor', 5, 2)->nullable()->after('bobot_kognitif');
            $table->decimal('bobot_afektif', 5, 2)->nullable()->after('bobot_psikomotor');
            $table->decimal('bobot_keaktifan', 5, 2)->nullable()->after('bobot_afektif');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'bobot_kognitif',
                'bobot_psikomotor',
                'bobot_afektif',
                'bobot_keaktifan',
            ]);
        });
    }
};
