<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnggotaRegistrationRequest;
use App\Models\Anggota;
use App\Models\FormPertanyaan;
use App\Models\User;
use App\Rules\ValidCutoutPng;
use App\Services\NomorWaNormalizer;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnggotaRegistrationController extends Controller
{
    public function show(): View
    {
        $customQuestions = FormPertanyaan::where('is_custom', true)
            ->where('is_aktif', true)
            ->ordered()
            ->get()
            ->groupBy('seksi');

        return view('anggota.register', compact('customQuestions'));
    }

    public function store(AnggotaRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $fotoPath = $request->file('foto')->store('anggota/foto', 'local');

        $fotoCutoutPath = null;
        if ($request->hasFile('foto_cutout')) {
            $cutoutFilename = 'cutout_'.Str::random(40).'.png';
            $targetPath = Storage::disk('local')->path('anggota/cutouts/'.$cutoutFilename);
            if (ValidCutoutPng::reencodePng($request->file('foto_cutout'), $targetPath)) {
                $fotoCutoutPath = 'anggota/cutouts/'.$cutoutFilename;
            }
        }
        $fotoMode = $data['foto_mode'] ?? 'cutout';

        $noWa = NomorWaNormalizer::normalize($data['no_wa']);
        $rawPassword = ! empty($data['password'])
            ? $data['password']
            : Carbon::parse($data['tanggal_lahir'])->format('dmY');

        DB::transaction(function () use ($data, $fotoPath, $fotoCutoutPath, $fotoMode, $noWa, $rawPassword) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'email' => $data['email'] ?? ('anggota+'.$data['nik'].'@placeholder.test'),
                'password' => Hash::make($rawPassword),
                'role' => 'anggota',
                'status' => 'menunggu_verifikasi',
                'no_wa' => $noWa,
            ]);

            $anggotaData = collect($data)->except(['password', 'password_confirmation', 'foto_cutout'])->all();
            $anggotaData['user_id'] = $user->id;
            $anggotaData['no_wa'] = $noWa;
            $anggotaData['foto'] = $fotoPath;
            $anggotaData['foto_cutout_path'] = $fotoCutoutPath;
            $anggotaData['foto_mode'] = $fotoMode;
            $anggotaData['umur'] = Carbon::parse($data['tanggal_lahir'])->age;

            if (empty($anggotaData['jabatan_aum']) && ! empty($anggotaData['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'])) {
                $anggotaData['jabatan_aum'] = $anggotaData['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'];
            }

            Anggota::create($anggotaData);
        });

        $noWaDisplay = str_starts_with($noWa, '62') ? ('0'.substr($noWa, 2)) : $noWa;

        return redirect()->route('anggota.register')
            ->with('success', 'Pendaftaran berhasil dikirim. Anda telah terdaftar dan akun menunggu verifikasi data oleh tim admin MPKSDI.')
            ->with('registered_data', [
                'nama' => $data['nama_lengkap'],
                'nik' => $data['nik'],
                'no_wa' => $noWa,
                'no_wa_display' => $noWaDisplay,
                'email' => $data['email'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'pcm' => $data['pcm'] ?? null,
                'prm' => $data['prm'] ?? null,
                'username' => $noWaDisplay,
                'username_alt' => $noWa,
                'password_default' => Carbon::parse($data['tanggal_lahir'])->format('dmY'),
            ]);
    }
}
