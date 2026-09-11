<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->string('qr_token', 64)->unique();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_peserta');
    }
};
