<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_organisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignId('organisasi_id')->constrained('organisasi')->cascadeOnDelete();
            $table->string('jabatan')->nullable();
            $table->string('periode')->nullable();
            $table->timestamps();

            $table->unique(['anggota_id', 'organisasi_id', 'jabatan'], 'unique_anggota_org_jabatan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_organisasi');
    }
};
