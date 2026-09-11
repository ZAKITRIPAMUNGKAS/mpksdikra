<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $anggota = auth()->user()->anggota;

        return view('portal.profile', ['anggota' => $anggota]);
    }

    public function update(Request $request): RedirectResponse
    {
        $anggota = auth()->user()->anggota;

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'alamat_rumah' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'status_pernikahan' => ['required', 'string', 'max:30'],
            'desa_kelurahan' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'jabatan_aum' => ['nullable', 'string', 'max:255'],
            'pendidikan_sd' => ['nullable', 'string', 'max:255'],
            'bahasa_dikuasai' => ['nullable', 'array'],
            'kemampuan_baca_quran' => ['nullable', 'string', 'max:255'],
            'hafalan_quran' => ['nullable', 'string', 'max:255'],
            'aktivitas_sholat_masjid' => ['nullable', 'string', 'max:255'],
            'aktivitas_kajian_agama' => ['nullable', 'string', 'max:255'],
            'jumlah_buku_agama' => ['nullable', 'string', 'max:255'],
            'sumber_info_muhammadiyah' => ['nullable', 'array'],
            'langganan_suara_muhammadiyah' => ['nullable', 'string', Rule::in(config('arqam.opsi_form.langganan_suara_muhammadiyah'))],
            'lembaga_zis' => ['nullable', 'array'],
            'tokoh_berpengaruh' => ['nullable', 'string', 'max:255'],
            'alasan_pilih_tokoh' => ['nullable', 'string', 'max:2000'],
            'keaktifan_muhammadiyah' => ['nullable', 'array'],
            'keaktifan_ortom' => ['nullable', 'array'],
            'organisasi_lain' => ['nullable', 'string', 'max:255'],
            'aktifitas_jamaah_masjid' => ['nullable', 'string', 'max:2000'],
            'keaktifan_takmir' => ['nullable', 'string', 'max:20'],
            'jabatan_takmir' => ['nullable', 'string', 'max:255'],
            'keaktifan_kemasyarakatan' => ['nullable', 'string', 'max:2000'],
            'harapan_pcm' => ['nullable', 'string', 'max:2000'],
            'harapan_ba' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['umur'] = Carbon::parse($data['tanggal_lahir'])->age;

        if ($request->hasFile('foto')) {
            if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
                Storage::disk('local')->delete($anggota->foto);
            }
            $data['foto'] = $request->file('foto')->store('anggota/foto', 'local');
        }

        $anggota->update($data);

        auth()->user()->update(['name' => $data['nama_lengkap']]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
