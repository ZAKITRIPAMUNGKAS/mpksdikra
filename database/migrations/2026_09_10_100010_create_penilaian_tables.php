<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->decimal('nilai_pretest', 5, 2)->default(0);
            $table->decimal('nilai_posttest', 5, 2)->default(0);
            $table->decimal('nilai_afektif', 5, 2)->default(0);
            $table->decimal('nilai_psikomotor', 5, 2)->default(0);
            $table->decimal('nilai_kehadiran', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->unsignedSmallInteger('ranking')->nullable();
            $table->string('status_kelulusan', 30)->nullable();
            $table->string('verification_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'anggota_id']);
            $table->index(['event_id', 'ranking']);
        });

        Schema::create('keaktifan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->enum('jenis', ['tanya', 'jawab', 'kultum', 'imam', 'adzan', 'juara_outbound']);
            $table->foreignId('sesi_id')->nullable()->constrained('event_sesi')->nullOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keaktifan');
        Schema::dropIfExists('penilaian_akhir');
    }
};
