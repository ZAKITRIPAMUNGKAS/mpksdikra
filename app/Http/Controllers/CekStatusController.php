<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Services\NomorWaNormalizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CekStatusController extends Controller
{
    public function index(): View
    {
        return view('auth.cek-status');
    }

    public function check(Request $request): View
    {
        $request->validate([
            'identifier' => ['required', 'string', 'min:4', 'max:50'],
        ], [
            'identifier.required' => 'Silakan masukkan NIK atau Nomor WhatsApp Anda.',
            'identifier.min' => 'Kata kunci pencarian minimal 4 karakter.',
        ]);

        $raw = trim($request->input('identifier'));
        $digits = preg_replace('/\D/', '', $raw);

        $query = Anggota::with('user');

        if (! empty($digits)) {
            $normalizedWa = NomorWaNormalizer::normalize($digits);
            $query->where(function ($q) use ($digits, $normalizedWa) {
                $q->where('nik', $digits)
                    ->orWhere('no_wa', $digits)
                    ->orWhere('no_wa', $normalizedWa);
            });
        } else {
            $query->where('email', strtolower($raw));
        }

        $anggota = $query->first();

        return view('auth.cek-status', [
            'searched' => true,
            'identifier' => $raw,
            'anggota' => $anggota,
        ]);
    }
}
