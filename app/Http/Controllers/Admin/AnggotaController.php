<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $q = trim($request->string('q')->toString());

        $query = Anggota::with('user')->latest();

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

        return view('admin.anggota.index', [
            'anggotas' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'q' => $q,
            'pendingCount' => User::where('role', 'anggota')->where('status', 'menunggu_verifikasi')->count(),
            'ditolakCount' => User::where('role', 'anggota')->where('status', 'ditolak')->count(),
        ]);
    }

    public function show(Anggota $anggota): View
    {
        $anggota->load(['user', 'organisasi']);

        return view('admin.anggota.show', ['anggota' => $anggota]);
    }

    public function verify(Anggota $anggota): RedirectResponse
    {
        $user = $anggota->user;

        if ($user->status === 'aktif') {
            return back()->with('success', 'Anggota sudah aktif.');
        }

        $user->update([
            'status' => 'aktif',
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Anggota diverifikasi dan diaktifkan.');
    }

    public function reject(Request $request, Anggota $anggota): RedirectResponse
    {
        $data = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ]);

        $anggota->user->update(['status' => 'ditolak']);
        $anggota->update([
            'catatan_verifikasi' => $data['catatan_verifikasi'],
        ]);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Pendaftaran anggota telah ditolak dengan catatan.');
    }

    public function deactivate(Anggota $anggota): RedirectResponse
    {
        $anggota->user->update(['status' => 'nonaktif']);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', 'Anggota dinonaktifkan.');
    }
}
