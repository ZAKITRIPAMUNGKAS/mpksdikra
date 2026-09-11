<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('sesi_id')->constrained('event_sesi')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->timestamp('waktu_scan');
            $table->foreignId('scanned_by')->constrained('users')->cascadeOnDelete();
            $table->enum('metode', ['qr', 'manual'])->default('qr');
            $table->timestamps();

            $table->unique(['sesi_id', 'anggota_id']);
            $table->index(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
