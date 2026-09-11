<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('tipe', ['pretest', 'posttest']);
            $table->text('teks_soal');
            $table->unsignedSmallInteger('urutan');
            $table->timestamps();

            $table->index(['event_id', 'tipe']);
        });

        Schema::create('pilihan_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->string('label', 2);
            $table->text('teks');
            $table->boolean('is_benar')->default(false);
            $table->timestamps();

            $table->index('soal_id');
        });

        Schema::create('sesi_tes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('tipe', ['pretest', 'posttest']);
            $table->enum('status', ['belum_buka', 'aktif', 'tutup'])->default('belum_buka');
            $table->unsignedSmallInteger('durasi_menit')->default(30);
            $table->timestamps();

            $table->unique(['event_id', 'tipe']);
        });

        Schema::create('jawaban_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->foreignId('pilihan_id')->nullable()->constrained('pilihan_jawaban')->nullOnDelete();
            $table->timestamps();

            $table->unique(['anggota_id', 'soal_id']);
            $table->index(['event_id', 'anggota_id']);
        });

        Schema::create('peserta_tes_mulai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->enum('tipe', ['pretest', 'posttest']);
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'anggota_id', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peserta_tes_mulai');
        Schema::dropIfExists('jawaban_peserta');
        Schema::dropIfExists('sesi_tes');
        Schema::dropIfExists('pilihan_jawaban');
        Schema::dropIfExists('soal');
    }
};
