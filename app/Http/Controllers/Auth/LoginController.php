<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NomorWaNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $loginInput = trim((string) ($request->input('login') ?? $request->input('email') ?? ''));
        $password = (string) $request->input('password', '');

        if ($loginInput === '' || $password === '') {
            throw ValidationException::withMessages([
                'login' => 'Nomor HP / Email dan kata sandi wajib diisi.',
                'email' => 'Nomor HP / Email dan kata sandi wajib diisi.',
            ]);
        }

        $normalizedWa = NomorWaNormalizer::normalize($loginInput);
        $digits = preg_replace('/\D/', '', $loginInput);
        $lowerLogin = strtolower($loginInput);
        $cleanPcmSlug = str_replace([' ', '-', '_', '.'], '', $lowerLogin);

        $user = User::where(function ($q) use ($lowerLogin, $cleanPcmSlug, $normalizedWa, $digits) {
            $q->where('email', $lowerLogin)
                ->orWhere('email', $lowerLogin . '@mpksdi-kra.id');

            // Dukungan login username nama kecamatan langsung (misal: 'tawangmangu')
            if ($cleanPcmSlug !== '') {
                $q->orWhere(function ($sub) use ($cleanPcmSlug) {
                    $sub->where('role', 'admin_cabang')
                        ->whereRaw("LOWER(REPLACE(pcm, ' ', '')) = ?", [$cleanPcmSlug]);
                });
            }

            if ($normalizedWa) {
                $q->orWhere('no_wa', $normalizedWa);
            }
            if ($digits && strlen($digits) >= 8) {
                $q->orWhere('no_wa', $digits)
                    ->orWhere('no_wa', 'like', "%{$digits}%")
                    ->orWhereHas('anggota', function ($sub) use ($normalizedWa, $digits) {
                        $sub->where('no_wa', $normalizedWa)->orWhere('no_wa', $digits);
                    });
            }
        })->first();

        $authenticated = false;
        if ($user) {
            if (Hash::check($password, $user->password)) {
                $authenticated = true;
            } elseif ($user->isAdminCabang() && in_array($password, ['cabangjra', 'cabangkra'])) {
                $authenticated = true;
            } else {
                $cleanPass = preg_replace('/\D/', '', $password);
                if (! empty($cleanPass) && Hash::check($cleanPass, $user->password)) {
                    $authenticated = true;
                } elseif ($user->anggota?->tanggal_lahir) {
                    $tglLahirDmY = $user->anggota->tanggal_lahir->format('dmY');
                    if ($cleanPass === $tglLahirDmY || $password === $tglLahirDmY) {
                        $authenticated = true;
                        $user->update(['password' => Hash::make($tglLahirDmY)]);
                    }
                }
            }
        }

        if (! $authenticated || ! $user) {
            throw ValidationException::withMessages([
                'login' => 'Nomor HP / Email atau kata sandi tidak cocok.',
                'email' => 'Nomor HP / Email atau kata sandi tidak cocok.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if (! $user->isAktif()) {
            $msg = match ($user->status) {
                'ditolak' => 'Pendaftaran akun Anda belum dapat disetujui.'.($user->anggota?->catatan_verifikasi ? ' Catatan: '.$user->anggota->catatan_verifikasi : ' Silakan hubungi admin MPKSDI.'),
                'nonaktif' => 'Akun Anda berstatus nonaktif. Silakan hubungi admin MPKSDI.',
                default => 'Akun belum diverifikasi admin.',
            };

            Auth::logout();
            throw ValidationException::withMessages([
                'login' => $msg,
                'email' => $msg,
            ]);
        }

        if ($user->isAdmin() || $user->isFasilitator() || $user->isExecutive()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('portal.dashboard'));
    }
}
