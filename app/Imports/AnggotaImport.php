<?php

namespace App\Imports;

use App\Models\Anggota;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnggotaImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    public array $errors = [];

    public array $skipped = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 karena baris 1 adalah header

            $nama = trim((string) ($row['nama_lengkap'] ?? ''));
            $nik = preg_replace('/\D/', '', (string) ($row['nik'] ?? ''));
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            $rawWa = (string) ($row['no_wa'] ?? '');

            if (empty($nama) || empty($nik)) {
                $this->skipped[] = "Baris #{$rowNum}: Dilewati karena nama atau NIK kosong.";

                continue;
            }

            if (strlen($nik) !== 16) {
                $this->errors[] = "Baris #{$rowNum} ({$nama}): NIK harus 16 digit angka (ditemukan: {$nik}).";

                continue;
            }

            if (Anggota::where('nik', $nik)->exists()) {
                $this->skipped[] = "Baris #{$rowNum} ({$nama}): NIK {$nik} sudah terdaftar di sistem.";

                continue;
            }

            // Normalisasi nomor WhatsApp
            $noWa = ! empty($rawWa) ? NomorWaNormalizer::normalize($rawWa) : '';
            if (empty($noWa)) {
                $noWa = '0812'.rand(10000000, 99999999);
            }

            // Generate email jika kosong
            if (empty($email)) {
                $email = 'kader.'.$nik.'@mpksdi.test';
            }

            if (User::where('email', $email)->exists()) {
                $email = 'kader.'.$nik.'.'.rand(10, 99).'@mpksdi.test';
            }

            // Password default: 6 digit terakhir NIK
            $defaultPassword = substr($nik, -6);

            // Tanggal lahir & umur
            $tanggalLahirRaw = $row['tanggal_lahir'] ?? '1980-01-01';
            try {
                $tanggalLahir = Carbon::parse($tanggalLahirRaw)->format('Y-m-d');
                $umur = Carbon::parse($tanggalLahir)->age;
            } catch (\Throwable) {
                $tanggalLahir = '1980-01-01';
                $umur = 40;
            }

            $jk = strtoupper(trim((string) ($row['jenis_kelamin'] ?? 'L')));
            if (! in_array($jk, ['L', 'P'])) {
                $jk = 'L';
            }

            $isTakmir = in_array(strtolower(trim((string) ($row['keaktifan_takmir'] ?? ''))), ['ya', '1', 'true', 'takmir']) ? 'Ya' : 'Tidak';

            DB::transaction(function () use ($nama, $email, $defaultPassword, $nik, $noWa, $jk, $row, $tanggalLahir, $umur, $isTakmir) {
                $user = User::create([
                    'name' => $nama,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'anggota',
                    'status' => 'aktif',
                    'verified_at' => now(),
                ]);

                Anggota::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $nama,
                    'alamat_rumah' => $row['desa_kelurahan'] ?? $row['kecamatan'] ?? 'Karanganyar',
                    'jenis_kelamin' => $jk,
                    'nik' => $nik,
                    'nbm' => ! empty($row['nbm']) ? (string) $row['nbm'] : null,
                    'jabatan_aum' => ! empty($row['pekerjaan']) ? (string) $row['pekerjaan'] : null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? 'Karanganyar',
                    'tanggal_lahir' => $tanggalLahir,
                    'umur' => $umur,
                    'status_pernikahan' => 'Menikah',
                    'desa_kelurahan' => $row['desa_kelurahan'] ?? '-',
                    'kecamatan' => $row['kecamatan'] ?? 'Karanganyar',
                    'kabupaten' => 'Karanganyar',
                    'no_wa' => $noWa,
                    'email' => $email,
                    'keaktifan_takmir' => $isTakmir,
                    'jabatan_takmir' => $row['jabatan_takmir'] ?? null,
                    'aktifitas_jamaah_masjid' => $row['aktifitas_jamaah_masjid'] ?? null,
                    'keaktifan_kemasyarakatan' => $row['keaktifan_kemasyarakatan'] ?? null,
                ]);

                $this->successCount++;
            });
        }
    }
}
