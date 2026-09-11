<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('anggota')->after('password');
            $table->string('status', 30)->default('aktif')->after('role');
            $table->string('no_wa', 20)->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('no_wa');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'no_wa', 'verified_at']);
        });
    }
};
