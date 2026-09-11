<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anggota>
 */
class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    public function definition(): array
    {
        $lahir = fake()->dateTimeBetween('-70 years', '-18 years');

        return [
            'user_id' => User::factory(),
            'nama_lengkap' => fake()->name(),
            'alamat_rumah' => fake()->address(),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'nik' => (string) fake()->unique()->numerify('################'),
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => $lahir->format('Y-m-d'),
            'umur' => (int) $lahir->diff(now())->y,
            'status_pernikahan' => 'Nikah',
            'desa_kelurahan' => 'Mangkubumen',
            'kecamatan' => 'Banjarsari',
            'kabupaten' => 'Surakarta',
            'no_wa' => '628123456789',
        ];
    }
}
