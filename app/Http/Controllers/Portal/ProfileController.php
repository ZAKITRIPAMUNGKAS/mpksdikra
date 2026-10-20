<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FormPertanyaan;
use App\Rules\ValidCutoutPng;
use App\Services\QrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $anggota = auth()->user()->anggota;
        $customQuestions = FormPertanyaan::where('is_custom', true)
            ->where('is_aktif', true)
            ->ordered()
            ->get()
            ->groupBy('seksi');

        return view('portal.profile', [
            'anggota' => $anggota,
            'customQuestions' => $customQuestions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $anggota = auth()->user()->anggota;

        $rules = [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'alamat_rumah' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'status_pernikahan' => ['required', 'string', 'max:30'],
            'desa_kelurahan' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kabupaten' => ['required', 'string', 'max:255'],
            'nbm' => ['nullable', 'string', 'max:50'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'pcm' => ['nullable', 'string', 'max:255'],
            'prm' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_cutout' => [
                'nullable',
                'file',
                'max:2048',
                new ValidCutoutPng(
                    $request->file('foto') ?: ($anggota->foto ? Storage::disk('local')->path($anggota->foto) : null)
                ),
            ],
            'foto_mode' => ['nullable', 'string', 'in:cutout,original'],
            'nama_aum' => ['nullable', 'string', 'max:255'],
            'nama_aum_custom' => ['nullable', 'string', 'max:255'],
            'jabatan_aum' => ['nullable', 'string', 'max:255'],
            'jabatan_aum_custom' => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'pendidikan_sd' => ['nullable', 'string', 'max:255'],
            'pendidikan_smp' => ['nullable', 'string', 'max:255'],
            'pendidikan_sma' => ['nullable', 'string', 'max:255'],
            'pendidikan_s1' => ['nullable', 'string', 'max:255'],
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
            'jabatan_muhammadiyah' => ['nullable', 'array'],
            'jabatan_muhammadiyah.*' => ['nullable', 'string', 'max:255'],
            'keaktifan_ortom' => ['nullable', 'array'],
            'jabatan_ortom' => ['nullable', 'array'],
            'jabatan_ortom.*' => ['nullable'],
            'jabatan_ortom.*.tingkat' => ['nullable', 'string', 'max:255'],
            'jabatan_ortom.*.periode' => ['nullable', 'string', 'max:255'],
            'jabatan_ortom.*.jabatan' => ['nullable', 'string', 'max:255'],
            'jabatan_ortom.*.jabatan_custom' => ['nullable', 'string', 'max:255'],
            'organisasi_lain' => ['nullable', 'string', 'max:255'],
            'aktifitas_jamaah_masjid' => ['nullable', 'string', 'max:2000'],
            'keaktifan_takmir' => ['nullable', 'string', 'max:20'],
            'jabatan_takmir' => ['nullable', 'string', 'max:255'],
            'keaktifan_kemasyarakatan' => ['nullable', 'string', 'max:2000'],
            'harapan_pcm' => ['nullable', 'string', 'max:2000'],
            'harapan_ba' => ['nullable', 'string', 'max:2000'],
            'custom_fields' => ['nullable', 'array'],
        ];

        $data = $request->validate(FormPertanyaan::applyStandardValidationRules($rules) + FormPertanyaan::activeValidationRules());

        $namaAum = $data['nama_aum'] ?? null;
        if ($namaAum === 'Lainnya' && ! empty($data['nama_aum_custom'])) {
            $namaAum = trim($data['nama_aum_custom']);
        }
        $jabatanAum = $data['jabatan_aum'] ?? null;
        if ($jabatanAum === 'Lainnya' && ! empty($data['jabatan_aum_custom'])) {
            $jabatanAum = trim($data['jabatan_aum_custom']);
        }
        $data['nama_aum'] = $namaAum;
        $data['jabatan_aum'] = $jabatanAum;
        unset($data['nama_aum_custom'], $data['jabatan_aum_custom']);

        if (! empty($data['jabatan_ortom']) && is_array($data['jabatan_ortom'])) {
            $cleanedOrtom = [];
            foreach ($data['jabatan_ortom'] as $ortomKey => $val) {
                if (is_array($val)) {
                    $tingkat = $val['tingkat'] ?? null;
                    $periode = $val['periode'] ?? null;
                    $jabatan = $val['jabatan'] ?? null;
                    if ($jabatan === 'Lainnya' && ! empty($val['jabatan_custom'])) {
                        $jabatan = trim($val['jabatan_custom']);
                    }
                    if ($tingkat || $periode || $jabatan) {
                        $cleanedOrtom[$ortomKey] = [
                            'tingkat' => $tingkat,
                            'periode' => $periode,
                            'jabatan' => $jabatan,
                        ];
                    }
                } elseif (is_string($val) && trim($val) !== '') {
                    $cleanedOrtom[$ortomKey] = trim($val);
                }
            }
            $data['jabatan_ortom'] = ! empty($cleanedOrtom) ? $cleanedOrtom : null;
        }

        $data['umur'] = Carbon::parse($data['tanggal_lahir'])->age;

        if ($request->hasFile('foto')) {
            if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
                Storage::disk('local')->delete($anggota->foto);
            }
            if ($anggota->foto_cutout_path && ! $request->hasFile('foto_cutout') && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
                Storage::disk('local')->delete($anggota->foto_cutout_path);
                $data['foto_cutout_path'] = null;
            }
            $data['foto'] = $request->file('foto')->store('anggota/foto', 'local');
        }

        if ($request->hasFile('foto_cutout')) {
            if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
                Storage::disk('local')->delete($anggota->foto_cutout_path);
            }
            $cutoutFilename = 'cutout_'.Str::random(40).'.png';
            $targetPath = Storage::disk('local')->path('anggota/cutouts/'.$cutoutFilename);
            if (ValidCutoutPng::reencodePng($request->file('foto_cutout'), $targetPath)) {
                $data['foto_cutout_path'] = 'anggota/cutouts/'.$cutoutFilename;
            }
        }
        unset($data['foto_cutout']);

        if ($request->filled('foto_mode')) {
            $data['foto_mode'] = $request->input('foto_mode');
        }

        $anggota->update($data);

        auth()->user()->update(['name' => $data['nama_lengkap']]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function cetakPdf(QrService $qrService)
    {
        $anggota = auth()->user()->anggota;
        abort_unless($anggota !== null, 404, 'Data anggota tidak ditemukan.');
        $anggota->load(['user', 'organisasi', 'eventPeserta.event']);

        $qrUrl = url('/cek-status?q='.urlencode($anggota->nik ?: ($anggota->no_wa ?: (string) $anggota->id)));
        $qrPng = $qrService->qrPngDataUriForText($qrUrl, 6, 2);

        $fotoDataUri = null;
        if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
            $mime = Storage::disk('local')->mimeType($anggota->foto) ?: 'image/jpeg';
            $content = Storage::disk('local')->get($anggota->foto);
            $fotoDataUri = 'data:'.$mime.';base64,'.base64_encode($content);
        }

        $pdf = Pdf::loadView('pdf.biodata-kader', compact('anggota', 'qrPng', 'fotoDataUri'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'cv-biodata-'.Str::slug($anggota->nama_lengkap).'.pdf';

        if (request()->has('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
