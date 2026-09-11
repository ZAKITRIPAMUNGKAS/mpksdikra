<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@mpksdi.test')],
            [
                'name' => 'Admin MPKSDI',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ubah-segera')),
                'role' => 'admin',
                'status' => 'aktif',
                'verified_at' => now(),
            ]
        );
    }
}
