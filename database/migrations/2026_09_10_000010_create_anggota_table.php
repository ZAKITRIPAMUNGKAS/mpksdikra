<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('nama_lengkap');
            $table->string('alamat_rumah');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('nik', 20)->unique();
            $table->string('nbm', 20)->nullable();
            $table->string('jabatan_aum')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->unsignedSmallInteger('umur')->nullable();
            $table->string('status_pernikahan', 30);
            $table->string('desa_kelurahan');
            $table->string('kecamatan');
            $table->string('kabupaten');
            $table->string('no_wa', 20);
            $table->string('email')->nullable();
            $table->string('foto')->nullable();
            $table->string('pendidikan_sd')->nullable();
            $table->json('bahasa_dikuasai')->nullable();
            $table->string('kemampuan_baca_quran')->nullable();
            $table->string('hafalan_quran')->nullable();
            $table->string('aktivitas_sholat_masjid')->nullable();
            $table->string('aktivitas_kajian_agama')->nullable();
            $table->string('jumlah_buku_agama')->nullable();
            $table->json('sumber_info_muhammadiyah')->nullable();
            $table->boolean('langganan_suara_muhammadiyah')->nullable();
            $table->json('lembaga_zis')->nullable();
            $table->string('tokoh_berpengaruh')->nullable();
            $table->text('alasan_pilih_tokoh')->nullable();
            $table->json('keaktifan_muhammadiyah')->nullable();
            $table->json('keaktifan_ortom')->nullable();
            $table->string('organisasi_lain')->nullable();
            $table->text('harapan_pcm')->nullable();
            $table->text('harapan_ba')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
