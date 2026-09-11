<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->isAktif()) {
            $msg = match ($user->status) {
                'ditolak' => 'Pendaftaran akun Anda belum dapat disetujui.'.($user->anggota?->catatan_verifikasi ? ' Catatan: '.$user->anggota->catatan_verifikasi : ' Silakan hubungi admin MPKSDI.'),
                'nonaktif' => 'Akun Anda berstatus nonaktif. Silakan hubungi admin MPKSDI.',
                default => 'Akun belum diverifikasi admin.',
            };

            Auth::logout();
            throw ValidationException::withMessages([
                'email' => $msg,
            ]);
        }

        if ($user->isAdmin() || $user->isFasilitator() || $user->isExecutive()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('portal.dashboard'));
    }
}
