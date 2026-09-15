<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\FormPertanyaan;
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

        $data = $request->validate([
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
            'jabatan_aum' => ['nullable', 'string', 'max:255'],
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
            'jabatan_ortom.*' => ['nullable', 'string', 'max:255'],
            'organisasi_lain' => ['nullable', 'string', 'max:255'],
            'aktifitas_jamaah_masjid' => ['nullable', 'string', 'max:2000'],
            'keaktifan_takmir' => ['nullable', 'string', 'max:20'],
            'jabatan_takmir' => ['nullable', 'string', 'max:255'],
            'keaktifan_kemasyarakatan' => ['nullable', 'string', 'max:2000'],
            'harapan_pcm' => ['nullable', 'string', 'max:2000'],
            'harapan_ba' => ['nullable', 'string', 'max:2000'],
            'custom_fields' => ['nullable', 'array'],
        ] + FormPertanyaan::activeValidationRules());

        if (empty($data['jabatan_aum']) && ! empty($data['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'])) {
            $data['jabatan_aum'] = $data['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'];
        } elseif (! empty($data['jabatan_aum']) && empty($data['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'])) {
            $data['jabatan_muhammadiyah']['Amal Usaha Muhammadiyah'] = $data['jabatan_aum'];
        }

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
