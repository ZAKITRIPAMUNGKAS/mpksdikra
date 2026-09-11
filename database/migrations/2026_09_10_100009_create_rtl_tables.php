<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rtl_soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->text('pertanyaan');
            $table->enum('tipe', ['essay', 'upload'])->default('essay');
            $table->unsignedSmallInteger('urutan');
            $table->timestamps();
        });

        Schema::create('rtl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->enum('status', ['draf', 'terkirim', 'disetujui', 'ditolak'])->default('draf');
            $table->timestamps();

            $table->unique(['event_id', 'anggota_id']);
        });

        Schema::create('rtl_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtl_id')->constrained('rtl')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('rtl_soal')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->string('file_bukti')->nullable();
            $table->timestamps();

            $table->unique(['rtl_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rtl_jawaban');
        Schema::dropIfExists('rtl');
        Schema::dropIfExists('rtl_soal');
    }
};
