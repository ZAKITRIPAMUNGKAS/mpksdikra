<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('afektif_sub_aspek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('nama_sub_aspek');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('event_id');
        });

        Schema::create('afektif_butir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_aspek_id')->constrained('afektif_sub_aspek')->cascadeOnDelete();
            $table->text('teks_butir');
            $table->boolean('is_positif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('afektif_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('butir_id')->constrained('afektif_butir')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->timestamps();

            $table->unique(['anggota_id', 'butir_id']);
            $table->index(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('afektif_jawaban');
        Schema::dropIfExists('afektif_butir');
        Schema::dropIfExists('afektif_sub_aspek');
    }
};
