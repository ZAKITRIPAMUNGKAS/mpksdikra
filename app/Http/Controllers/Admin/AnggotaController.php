<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaExport;
use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use App\Rules\ValidCutoutPng;
use App\Services\ImageCutoutService;
use App\Services\QrService;
use App\Services\WhatsAppNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $q = trim($request->string('q')->toString());
        $kategori = $request->string('kategori')->toString();
        $aum = $request->string('aum')->toString();
        $ortom = $request->string('ortom')->toString();
        $cabang = $request->string('cabang')->toString();
        $sort = $request->string('sort')->toString();

        $user = auth()->user();
        $isCabang = $user?->isAdminCabang();
        $pcm = $user?->pcm;

        $query = Anggota::with('user');

        // Scope cabang jika user adalah Admin Cabang
        if ($isCabang && $pcm) {
            $query->where(function ($sub) use ($pcm) {
                $sub->where('pcm', $pcm)->orWhere('kecamatan', $pcm);
            });
        } elseif ($cabang !== '') {
            $query->where(function ($sub) use ($cabang) {
                $sub->where('pcm', $cabang)->orWhere('kecamatan', $cabang);
            });
        }

        if (in_array($status, ['menunggu_verifikasi', 'aktif', 'nonaktif', 'ditolak'], true)) {
            $query->whereHas('user', fn ($uq) => $uq->where('status', $status));
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('nbm', 'like', "%{$q}%")
                    ->orWhere('nama_aum', 'like', "%{$q}%")
                    ->orWhere('jabatan_aum', 'like', "%{$q}%")
                    ->orWhere('kecamatan', 'like', "%{$q}%")
                    ->orWhere('desa_kelurahan', 'like', "%{$q}%");
            });
        }

        // Filter Kategori (Pilar: Muhammadiyah, AUM, ORTOM)
        if ($kategori === 'muhammadiyah') {
            $query->where(function ($sub) {
                $sub->whereNotNull('keaktifan_muhammadiyah')
                    ->where('keaktifan_muhammadiyah', '!=', '[]')
                    ->whereJsonDoesntContain('keaktifan_muhammadiyah', 'Tidak aktif');
            });
        } elseif ($kategori === 'aum') {
            $query->where(function ($sub) {
                $sub->where(function ($s2) {
                    $s2->whereNotNull('nama_aum')->where('nama_aum', '!=', '');
                })->orWhere(function ($s2) {
                    $s2->whereNotNull('jabatan_aum')->where('jabatan_aum', '!=', '');
                });
            });
        } elseif ($kategori === 'ortom') {
            $query->where(function ($sub) {
                $sub->whereNotNull('keaktifan_ortom')
                    ->where('keaktifan_ortom', '!=', '[]')
                    ->whereJsonDoesntContain('keaktifan_ortom', 'Tidak aktif');
            });
        }

        // Filter Spesifik Nama AUM
        if ($aum !== '') {
            $query->where('nama_aum', $aum);
        }

        // Filter Spesifik ORTOM
        if ($ortom !== '') {
            $query->whereJsonContains('keaktifan_ortom', $ortom);
        }

        // Sorting
        match ($sort) {
            'nama_asc' => $query->orderBy('nama_lengkap', 'asc'),
            'nama_desc' => $query->orderBy('nama_lengkap', 'desc'),
            'aum_asc' => $query->orderByRaw("CASE WHEN nama_aum IS NULL OR nama_aum = '' THEN 1 ELSE 0 END, nama_aum ASC"),
            'pcm_asc' => $query->orderByRaw("COALESCE(NULLIF(pcm, ''), kecamatan) ASC"),
            default => $query->latest(),
        };

        // Pending & Ditolak Counter
        $pendingQuery = User::where('role', 'anggota')->where('status', 'menunggu_verifikasi');
        $ditolakQuery = User::where('role', 'anggota')->where('status', 'ditolak');

        if ($isCabang && $pcm) {
            $pendingQuery->whereHas('anggota', fn ($aq) => $aq->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
            $ditolakQuery->whereHas('anggota', fn ($aq) => $aq->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
        }

        $aumList = collect(config('arqam.opsi_form.nama_aum', []))
            ->merge(Anggota::whereNotNull('nama_aum')->where('nama_aum', '!=', '')->pluck('nama_aum'))
            ->filter(fn ($x) => ! in_array($x, ['Lainnya', ''], true))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $ortomList = collect(config('arqam.opsi_form.keaktifan_ortom', []))
            ->filter(fn ($x) => $x !== 'Tidak aktif')
            ->values()
            ->all();

        $pcmList = config('arqam.opsi_form.cabang_pcm', []);

        return view('admin.anggota.index', [
            'anggotas' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'q' => $q,
            'kategori' => $kategori,
            'aum' => $aum,
            'ortom' => $ortom,
            'cabang' => $cabang,
            'sort' => $sort,
            'aumList' => $aumList,
            'ortomList' => $ortomList,
            'pcmList' => $pcmList,
            'pendingCount' => $pendingQuery->count(),
            'ditolakCount' => $ditolakQuery->count(),
            'isCabang' => $isCabang,
            'cabangNama' => $pcm,
        ]);
    }

    public function show(Anggota $anggota): View
    {
        $this->authorizeCabang($anggota);
        $anggota->load(['user', 'organisasi', 'eventPeserta.event', 'penilaianAkhir']);

        return view('admin.anggota.show', ['anggota' => $anggota]);
    }

    public function cetakPdf(Anggota $anggota, QrService $qrService)
    {
        $this->authorizeCabang($anggota);
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

    public function verify(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);
        $user = $anggota->user;

        if ($user->status === 'aktif') {
            return back()->with('success', 'Anggota sudah aktif.');
        }

        $user->update([
            'status' => 'aktif',
            'verified_at' => now(),
        ]);

        $pesan = WhatsAppNotificationService::pesanVerifikasiSukses($anggota);
        $waUrl = $this->waUrl($anggota, $pesan);

        $autoStatus = null;
        if ($anggota->no_wa) {
            $autoStatus = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        }

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Anggota diverifikasi dan diaktifkan.')
            ->with('wa_url', $waUrl);

        if ($autoStatus !== null) {
            $redirect->with('wa_auto_sent', $autoStatus['success'])
                ->with('wa_auto_message', $autoStatus['message']);
        }

        return $redirect;
    }

    public function reject(Request $request, Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        $data = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ]);

        $anggota->user->update(['status' => 'ditolak']);
        $anggota->update([
            'catatan_verifikasi' => $data['catatan_verifikasi'],
        ]);

        $pesan = WhatsAppNotificationService::pesanPerbaikanDitolak($anggota);
        $waUrl = $this->waUrl($anggota, $pesan);

        $autoStatus = null;
        if ($anggota->no_wa) {
            $autoStatus = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        }

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Pendaftaran anggota telah ditolak dengan catatan.')
            ->with('wa_url', $waUrl);

        if ($autoStatus !== null) {
            $redirect->with('wa_auto_sent', $autoStatus['success'])
                ->with('wa_auto_message', $autoStatus['message']);
        }

        return $redirect;
    }

    public function deactivate(Request $request, Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        $alasan = trim((string) $request->input('alasan_nonaktif', ''));
        if ($alasan === '') {
            $alasan = 'Penonaktifan oleh Administrator MPKSDI.';
        }

        $anggota->user->update(['status' => 'nonaktif']);
        $anggota->update([
            'catatan_verifikasi' => $alasan,
        ]);

        $pesan = WhatsAppNotificationService::pesanPenonaktifan($anggota, $alasan);
        $waUrl = $this->waUrl($anggota, $pesan);

        $autoStatus = null;
        if ($anggota->no_wa) {
            $autoStatus = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        }

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Anggota berhasil dinonaktifkan.')
            ->with('wa_url', $waUrl);

        if ($autoStatus !== null) {
            $redirect->with('wa_auto_sent', $autoStatus['success'])
                ->with('wa_auto_message', $autoStatus['message']);
        }

        return $redirect;
    }

    public function activate(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        $anggota->user->update([
            'status' => 'aktif',
            'verified_at' => $anggota->user->verified_at ?? now(),
        ]);
        $anggota->update([
            'catatan_verifikasi' => null,
        ]);

        $pesan = WhatsAppNotificationService::pesanReaktivasi($anggota);
        $waUrl = $this->waUrl($anggota, $pesan);

        $autoStatus = null;
        if ($anggota->no_wa) {
            $autoStatus = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        }

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Akun anggota telah diaktifkan kembali.')
            ->with('wa_url', $waUrl);

        if ($autoStatus !== null) {
            $redirect->with('wa_auto_sent', $autoStatus['success'])
                ->with('wa_auto_message', $autoStatus['message']);
        }

        return $redirect;
    }

    public function resetPassword(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        $defaultPassword = $anggota->tanggal_lahir
            ? Carbon::parse($anggota->tanggal_lahir)->format('dmY')
            : (strlen($anggota->nik) >= 6 ? substr($anggota->nik, -6) : '12345678');

        $anggota->user->update([
            'password' => Hash::make($defaultPassword),
        ]);

        $waUrl = null;
        $autoStatus = null;
        if ($anggota->no_wa) {
            $pesan = WhatsAppNotificationService::pesanResetPassword($anggota, $defaultPassword);
            $waUrl = WhatsAppNotificationService::makeUrl($anggota->no_wa, $pesan);
            $autoStatus = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        }

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', "Kata sandi anggota berhasil di-reset menjadi: {$defaultPassword} (Tanggal Lahir)")
            ->with('wa_reset_url', $waUrl)
            ->with('reset_password_value', $defaultPassword);

        if ($autoStatus !== null) {
            $redirect->with('wa_reset_auto_sent', $autoStatus['success'])
                ->with('wa_reset_auto_message', $autoStatus['message']);
        }

        return $redirect;
    }

    public function kirimWa(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        if (empty($anggota->no_wa)) {
            return redirect()
                ->route('admin.anggota.show', $anggota)
                ->with('error', 'Nomor WhatsApp anggota tidak tersedia.');
        }

        if ($anggota->user->status === 'ditolak') {
            $pesan = WhatsAppNotificationService::pesanPerbaikanDitolak($anggota);
            $label = 'Catatan revisi pendaftaran';
        } else {
            $pesan = WhatsAppNotificationService::pesanVerifikasiSukses($anggota);
            $label = 'Notifikasi akun aktif';
        }

        $waUrl = $this->waUrl($anggota, $pesan);
        $result = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);

        $redirect = redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('wa_url', $waUrl)
            ->with('wa_auto_sent', $result['success'])
            ->with('wa_auto_message', $result['message']);

        if ($result['success']) {
            $redirect->with('success', "{$label} berhasil dikirim otomatis via WhatsApp Gateway ke {$anggota->nama_lengkap}.");
        } else {
            $redirect->with('warning', "Gagal kirim otomatis ({$result['message']}). Silakan gunakan tombol Buka Chat WA untuk mengirim manual.");
        }

        return $redirect;
    }

    public function export(Request $request): BinaryFileResponse
    {
        $user = auth()->user();
        $isCabang = $user?->isAdminCabang();
        $pcm = $user?->pcm;

        $query = Anggota::with('user')->orderBy('nama_lengkap');

        if ($isCabang && $pcm) {
            $query->where(function ($sub) use ($pcm) {
                $sub->where('pcm', $pcm)->orWhere('kecamatan', $pcm);
            });
            $subTitle = 'PIMPINAN CABANG MUHAMMADIYAH '.strtoupper($pcm);
            $safePcm = preg_replace('/[^A-Za-z0-9_]/', '_', $pcm);
            $fileName = 'Data_Kader_PCM_'.$safePcm.'_'.date('Ymd_His').'.xlsx';
        } else {
            // Admin Daerah dapat memfilter status / pencarian jika disediakan
            $status = $request->string('status')->toString();
            $q = trim($request->string('q')->toString());

            if (in_array($status, ['menunggu_verifikasi', 'aktif', 'nonaktif', 'ditolak'], true)) {
                $query->whereHas('user', fn ($uq) => $uq->where('status', $status));
            }

            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_lengkap', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('nbm', 'like', "%{$q}%")
                        ->orWhere('kecamatan', 'like', "%{$q}%")
                        ->orWhere('desa_kelurahan', 'like', "%{$q}%");
                });
            }

            $subTitle = 'PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR';
            $fileName = 'Data_Kader_MPKSDI_Karanganyar_'.date('Ymd_His').'.xlsx';
        }

        $anggotas = $query->get();

        return Excel::download(new AnggotaExport($anggotas, $subTitle, $isCabang ? $pcm : null), $fileName);
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        if ($anggota->eventPeserta()->exists()) {
            return back()->with('error', "Kader {$anggota->nama_lengkap} sudah terdaftar dalam kegiatan Baitul Arqam. Gunakan opsi Nonaktifkan agar riwayat nilai dan sertifikat tidak hilang.");
        }

        $nama = $anggota->nama_lengkap;
        $user = $anggota->user;
        $fotoPath = $anggota->foto;
        $cutoutPath = $anggota->foto_cutout_path;

        DB::transaction(function () use ($anggota, $user, $fotoPath, $cutoutPath) {
            if ($fotoPath && Storage::disk('local')->exists($fotoPath)) {
                Storage::disk('local')->delete($fotoPath);
            }
            if ($cutoutPath && Storage::disk('local')->exists($cutoutPath)) {
                Storage::disk('local')->delete($cutoutPath);
            }

            $anggota->organisasi()->detach();
            $anggota->forceDelete();

            if ($user && $user->role === 'anggota') {
                $user->delete();
            }
        });

        return redirect()->route('admin.anggota.index')
            ->with('success', "Data kader {$nama} berhasil dihapus dari pangkalan data.");
    }

    public function updateFotoMode(Request $request, Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        $validated = $request->validate([
            'foto_mode' => ['required', 'string', 'in:cutout,original'],
        ]);

        $anggota->update([
            'foto_mode' => $validated['foto_mode'],
        ]);

        return back()->with('success', 'Mode tampilan pasfoto ID Card berhasil diperbarui menjadi '.ucfirst($validated['foto_mode']).'.');
    }

    public function updateFoto(Request $request, Anggota $anggota, ImageCutoutService $cutoutService): RedirectResponse|JsonResponse
    {
        $this->authorizeCabang($anggota);

        $request->validate([
            'foto' => ['nullable'],
            'foto_cutout' => ['nullable'],
            'foto_mode' => ['nullable', 'string', 'in:original,cutout'],
        ]);

        $newFotoPath = null;
        $newCutoutPath = null;
        $fotoMode = $request->input('foto_mode', $anggota->foto_mode ?: 'cutout');

        // 1. Proses Pasfoto (UploadedFile atau Data URI base64 dari canvas)
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $request->validate([
                'foto' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            ]);
            $newFotoPath = $file->store('anggota/foto', 'local');
        } elseif ($request->filled('foto') && str_starts_with((string) $request->input('foto'), 'data:image/')) {
            $dataUri = (string) $request->input('foto');
            $commaPos = strpos($dataUri, ',');
            if ($commaPos !== false) {
                $raw = base64_decode(substr($dataUri, $commaPos + 1));
                if ($raw !== false && strlen($raw) > 50) {
                    $img = @imagecreatefromstring($raw);
                    if ($img) {
                        ob_start();
                        imagejpeg($img, null, 92);
                        $jpgData = ob_get_clean();
                        imagedestroy($img);

                        $filename = 'foto_'.Str::random(40).'.jpg';
                        $path = 'anggota/foto/'.$filename;
                        Storage::disk('local')->put($path, $jpgData);
                        $newFotoPath = $path;
                    }
                }
            }
        }

        // 2. Proses Cutout (UploadedFile atau Data URI base64 dari canvas)
        if ($request->hasFile('foto_cutout')) {
            $cutoutFile = $request->file('foto_cutout');
            $cutoutFilename = 'cutout_'.Str::random(40).'.png';
            $targetPath = Storage::disk('local')->path('anggota/cutouts/'.$cutoutFilename);
            if (ValidCutoutPng::reencodePng($cutoutFile, $targetPath)) {
                $newCutoutPath = 'anggota/cutouts/'.$cutoutFilename;
            }
        } elseif ($request->filled('foto_cutout') && str_starts_with((string) $request->input('foto_cutout'), 'data:image/')) {
            $dataUri = (string) $request->input('foto_cutout');
            $commaPos = strpos($dataUri, ',');
            if ($commaPos !== false) {
                $raw = base64_decode(substr($dataUri, $commaPos + 1));
                if ($raw !== false && strlen($raw) > 50) {
                    $img = @imagecreatefromstring($raw);
                    if ($img) {
                        imagealphablending($img, false);
                        imagesavealpha($img, true);
                        ob_start();
                        imagepng($img, null, 6);
                        $pngData = ob_get_clean();
                        imagedestroy($img);

                        $cutoutFilename = 'cutout_'.Str::random(40).'.png';
                        $path = 'anggota/cutouts/'.$cutoutFilename;
                        Storage::disk('local')->put($path, $pngData);
                        $newCutoutPath = $path;
                    }
                }
            }
        }

        // 3. Perbarui database dan bersihkan file usang
        $updateData = ['foto_mode' => $fotoMode];

        if ($newFotoPath) {
            if ($anggota->foto && Storage::disk('local')->exists($anggota->foto)) {
                Storage::disk('local')->delete($anggota->foto);
            }
            $updateData['foto'] = $newFotoPath;
        }

        if ($newCutoutPath) {
            if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
                Storage::disk('local')->delete($anggota->foto_cutout_path);
            }
            $updateData['foto_cutout_path'] = $newCutoutPath;
        } elseif ($newFotoPath && $fotoMode === 'cutout') {
            // Jika foto baru disimpan dalam mode cutout tapi klien tidak menghasilkan cutout,
            // picu pembuatan otomatis cutout GD server
            $serverCutoutUri = $cutoutService->getCutoutDataUriForStorageFile($newFotoPath, 'local');
            if ($serverCutoutUri && str_starts_with($serverCutoutUri, 'data:image/png;base64,')) {
                $rawPng = base64_decode(substr($serverCutoutUri, 22));
                if ($rawPng !== false) {
                    $cutoutFilename = 'cutout_'.Str::random(40).'.png';
                    $path = 'anggota/cutouts/'.$cutoutFilename;
                    Storage::disk('local')->put($path, $rawPng);
                    if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
                        Storage::disk('local')->delete($anggota->foto_cutout_path);
                    }
                    $updateData['foto_cutout_path'] = $path;
                }
            }
        }

        $anggota->update($updateData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto profil dan penyesuaian ID Card berhasil disimpan.',
                'foto_url' => route('foto.anggota', $anggota).'?t='.time(),
                'cutout_url' => $anggota->foto_cutout_path ? (route('foto.anggota', [$anggota, 'type' => 'cutout']).'?t='.time()) : null,
                'foto_mode' => $anggota->foto_mode,
            ]);
        }

        return back()->with('success', 'Foto profil dan penyesuaian ID Card berhasil disimpan.');
    }

    public function destroyCutout(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);

        if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
            Storage::disk('local')->delete($anggota->foto_cutout_path);
        }

        $anggota->update([
            'foto_cutout_path' => null,
        ]);

        return back()->with('success', 'Foto cutout klien berhasil dihapus. Sistem akan menggunakan pemrosesan cutout GD otomatis.');
    }

    private function authorizeCabang(Anggota $anggota): void
    {
        $user = auth()->user();
        if ($user?->isAdminCabang()) {
            $pcm = $user->pcm;
            $match = strcasecmp($anggota->pcm ?? '', $pcm) === 0 || strcasecmp($anggota->kecamatan ?? '', $pcm) === 0;
            if (! $match) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengelola data kader di luar cabang '.$pcm.'.');
            }
        }
    }

    private function waUrl(Anggota $anggota, string $pesan): ?string
    {
        return $anggota->no_wa
            ? WhatsAppNotificationService::makeUrl($anggota->no_wa, $pesan)
            : null;
    }
}
