<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnggotaRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('no_wa')) {
            $sanitized = preg_replace('/[\s\-\.]/', '', (string) $this->input('no_wa'));
            $this->merge(['no_wa' => $sanitized]);
        }
    }

    public function rules(): array
    {
        $o = config('arqam.opsi_form');

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'alamat_rumah' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(array_keys($o['jenis_kelamin']))],
            'nik' => ['required', 'digits:16', 'unique:anggota,nik'],
            'nbm' => ['nullable', 'string', 'max:20'],
            'jabatan_aum' => ['nullable', 'string', 'max:255'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'status_pernikahan' => ['required', Rule::in($o['status_pernikahan'])],
            'desa_kelurahan' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'pcm' => ['nullable', 'string', 'max:255'],
            'prm' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'regex:/^(0|62|\+62)8[0-9]{6,13}$/'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'foto' => ['required', 'image', 'max:2048'],
            'pendidikan_sd' => ['nullable', 'string', 'max:255'],
            'bahasa_dikuasai' => ['nullable', 'array'],
            'bahasa_dikuasai.*' => [Rule::in($o['bahasa_dikuasai'])],
            'kemampuan_baca_quran' => ['nullable', Rule::in($o['kemampuan_baca_quran'])],
            'hafalan_quran' => ['nullable', Rule::in($o['hafalan_quran'])],
            'aktivitas_sholat_masjid' => ['nullable', Rule::in($o['aktivitas_sholat_masjid'])],
            'aktivitas_kajian_agama' => ['nullable', Rule::in($o['aktivitas_kajian_agama'])],
            'jumlah_buku_agama' => ['nullable', Rule::in($o['jumlah_buku_agama'])],
            'sumber_info_muhammadiyah' => ['nullable', 'array'],
            'sumber_info_muhammadiyah.*' => [Rule::in($o['sumber_info_muhammadiyah'])],
            'langganan_suara_muhammadiyah' => ['nullable', Rule::in($o['langganan_suara_muhammadiyah'])],
            'lembaga_zis' => ['nullable', 'array'],
            'lembaga_zis.*' => [Rule::in($o['lembaga_zis'])],
            'tokoh_berpengaruh' => ['nullable', 'string', 'max:255'],
            'alasan_pilih_tokoh' => ['nullable', 'string', 'max:2000'],
            'keaktifan_muhammadiyah' => ['nullable', 'array'],
            'keaktifan_muhammadiyah.*' => [Rule::in($o['keaktifan_muhammadiyah'])],
            'jabatan_muhammadiyah' => ['nullable', 'array'],
            'jabatan_muhammadiyah.*' => ['nullable', 'string', 'max:255'],
            'keaktifan_ortom' => ['nullable', 'array'],
            'keaktifan_ortom.*' => [Rule::in($o['keaktifan_ortom'])],
            'jabatan_ortom' => ['nullable', 'array'],
            'jabatan_ortom.*' => ['nullable', 'string', 'max:255'],
            'organisasi_lain' => ['nullable', 'string', 'max:255'],
            'aktifitas_jamaah_masjid' => ['nullable', 'string', 'max:2000'],
            'keaktifan_takmir' => ['nullable', 'string', 'max:20'],
            'jabatan_takmir' => ['nullable', 'string', 'max:255'],
            'keaktifan_kemasyarakatan' => ['nullable', 'string', 'max:2000'],
            'harapan_pcm' => ['nullable', 'string', 'max:2000'],
            'harapan_ba' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_wa.regex' => 'Nomor WhatsApp tidak valid (contoh: 081234567890).',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar di pangkalan data.',
            'email.unique' => 'Alamat email ini sudah terdaftar sebagai akun.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'foto.required' => 'Pasfoto 3:4 wajib diunggah untuk kelengkapan administrasi.',
            'foto.image' => 'Berkas pasfoto harus berupa format gambar (JPG, PNG, atau WebP).',
            'foto.max' => 'Ukuran berkas pasfoto maksimal 2MB.',
        ];
    }
}
