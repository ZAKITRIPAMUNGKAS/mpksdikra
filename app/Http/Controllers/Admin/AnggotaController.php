<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaExport;
use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $anggota->load(['user', 'organisasi']);

        return view('admin.anggota.show', ['anggota' => $anggota]);
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

    public function deactivate(Anggota $anggota): RedirectResponse
    {
        $this->authorizeCabang($anggota);
        $anggota->user->update(['status' => 'nonaktif']);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Anggota dinonaktifkan.');
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
