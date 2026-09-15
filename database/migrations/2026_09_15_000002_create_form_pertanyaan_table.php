<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->string('seksi'); // diri, agama, organisasi, kemasyarakatan, tambahan
            $table->string('kode')->unique();
            $table->string('label');
            $table->string('tipe'); // text, textarea, select, checkbox, number
            $table->json('opsi')->nullable();
            $table->string('placeholder')->nullable();
            $table->text('bantuan')->nullable();
            $table->boolean('is_wajib')->default(false);
            $table->boolean('is_aktif')->default(true);
            $table->boolean('is_custom')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_pertanyaan');
    }
};
