<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_sesi', function (Blueprint $table) {
            $table->string('file_materi')->nullable()->after('pemateri');
            $table->string('link_materi')->nullable()->after('file_materi');
            $table->text('deskripsi')->nullable()->after('link_materi');
        });
    }

    public function down(): void
    {
        Schema::table('event_sesi', function (Blueprint $table) {
            $table->dropColumn(['file_materi', 'link_materi', 'deskripsi']);
        });
    }
};
