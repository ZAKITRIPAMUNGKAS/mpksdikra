<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FotoController extends Controller
{
    public function show(Anggota $anggota): Response|BinaryFileResponse|StreamedResponse
    {
        $user = auth()->user();

        $canView = $user->isAdminDaerah()
            || $user->isExecutive()
            || $user->isFasilitator()
            || ($user->isAdminCabang() && (empty($user->pcm) || strcasecmp(trim($user->pcm), trim($anggota->pcm ?? '')) === 0))
            || ($user->anggota && (int) $user->anggota->id === (int) $anggota->id);

        abort_unless($canView, 403, 'Akses foto tidak diizinkan.');

        if (request('type') === 'cutout') {
            if ($anggota->foto_cutout_path && Storage::disk('local')->exists($anggota->foto_cutout_path)) {
                return Storage::disk('local')->response($anggota->foto_cutout_path, null, [
                    'Cache-Control' => 'private, max-age=86400',
                ]);
            }
            abort(404, 'Foto cutout tidak ditemukan.');
        }

        if (! $anggota->foto || ! Storage::disk('local')->exists($anggota->foto)) {
            return $this->defaultProfileResponse($anggota);
        }

        return Storage::disk('local')->response($anggota->foto, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function defaultProfileResponse(Anggota $anggota): BinaryFileResponse|Response
    {
        $file = $anggota->jenis_kelamin === 'P'
            ? public_path('aset/default-profile-wanita.jpg')
            : public_path('aset/default-profile-pria.jpg');

        if (file_exists($file)) {
            return response()->file($file, [
                'Content-Type' => 'image/jpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        return $this->placeholderSvg($anggota->nama_lengkap);
    }

    private function placeholderSvg(string $name): Response
    {
        $initials = htmlspecialchars(strtoupper(substr($name, 0, 2)));
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
            <rect width="200" height="200" fill="#002b40"/>
            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#f8bb00" font-family="Arial, sans-serif" font-size="64" font-weight="bold">$initials</text>
        </svg>
        SVG;

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
