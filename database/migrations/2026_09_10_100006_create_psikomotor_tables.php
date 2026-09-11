<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psikomotor_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('jenis', ['ibadah', 'outbound']);
            $table->string('nama_aspek');
            $table->unsignedTinyInteger('skor_maks')->default(4);
            $table->timestamps();

            $table->index('event_id');
        });

        Schema::create('psikomotor_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('psikomotor_template')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->foreignId('dinilai_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['anggota_id', 'template_id']);
            $table->index(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psikomotor_nilai');
        Schema::dropIfExists('psikomotor_template');
    }
};
