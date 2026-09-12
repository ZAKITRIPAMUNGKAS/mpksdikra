<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRegistrationRequest;
use App\Models\Anggota;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AnggotaRegistrationController extends Controller
{
    public function show(): View
    {
        return view('anggota.register');
    }

    public function store(AnggotaRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $fotoPath = $request->file('foto')->store('anggota/foto', 'local');
        $noWa = NomorWaNormalizer::normalize($data['no_wa']);

        DB::transaction(function () use ($data, $fotoPath, $noWa) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['email'] ?? ('anggota+'.$data['nik'].'@placeholder.test'),
                'password' => Hash::make($data['password']),
                'role' => 'anggota',
                'status' => 'menunggu_verifikasi',
                'no_wa' => $noWa,
            ]);

            $anggotaData = collect($data)->except(['password', 'password_confirmation'])->all();
            $anggotaData['user_id'] = $user->id;
            $anggotaData['no_wa'] = $noWa;
            $anggotaData['foto'] = $fotoPath;
            $anggotaData['umur'] = Carbon::parse($data['tanggal_lahir'])->age;

            Anggota::create($anggotaData);
        });

        return redirect()->route('anggota.register')
            ->with('success', 'Pendaftaran berhasil dikirim. Anda telah terdaftar dan akun menunggu verifikasi data oleh tim admin MPKSDI.')
            ->with('registered_data', [
                'nama' => $data['nama_lengkap'],
                'nik' => $data['nik'],
                'no_wa' => $noWa,
                'email' => $data['email'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
            ]);
    }
}
