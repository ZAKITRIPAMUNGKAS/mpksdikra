<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('angket_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('kategori', 5)->default('A');
            $table->text('teks_item');
            $table->unsignedSmallInteger('urutan');
            $table->timestamps();

            $table->index('event_id');
        });

        Schema::create('angket_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('angket_item')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor');
            $table->timestamps();

            $table->unique(['anggota_id', 'item_id']);
        });

        Schema::create('angket_komentar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->text('komentar')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('angket_komentar');
        Schema::dropIfExists('angket_jawaban');
        Schema::dropIfExists('angket_item');
    }
};
