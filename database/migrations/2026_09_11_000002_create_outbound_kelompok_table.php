<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_kelompok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('nama_kelompok', 100);
            $table->timestamps();
        });

        Schema::table('event_peserta', function (Blueprint $table) {
            $table->foreignId('outbound_kelompok_id')->nullable()->after('qr_token')->constrained('outbound_kelompok')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_peserta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('outbound_kelompok_id');
        });

        Schema::dropIfExists('outbound_kelompok');
    }
};
