<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaExport;
use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use App\Services\QrService;
use App\Services\WhatsAppNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

        $user = auth()->user();
        $isCabang = $user?->isAdminCabang();
        $pcm = $user?->pcm;

        $query = Anggota::with('user')->latest();

        // Scope cabang jika user adalah Admin Cabang
        if ($isCabang && $pcm) {
            $query->where(function ($sub) use ($pcm) {
                $sub->where('pcm', $pcm)->orWhere('kecamatan', $pcm);
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
                    ->orWhere('kecamatan', 'like', "%{$q}%")
                    ->orWhere('desa_kelurahan', 'like', "%{$q}%");
            });
        }

        // Pending & Ditolak Counter
        $pendingQuery = User::where('role', 'anggota')->where('status', 'menunggu_verifikasi');
        $ditolakQuery = User::where('role', 'anggota')->where('status', 'ditolak');

        if ($isCabang && $pcm) {
            $pendingQuery->whereHas('anggota', fn ($aq) => $aq->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
            $ditolakQuery->whereHas('anggota', fn ($aq) => $aq->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
        }

        return view('admin.anggota.index', [
            'anggotas' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'q' => $q,
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
