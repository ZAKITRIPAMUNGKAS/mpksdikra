<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Event;
use App\Models\PenilaianAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(Request $request): View
    {
        // QR sertifikat mengarah ke /?verify=<hash> (sertifikat yang sudah tercetak tetap memakai format ini).
        if ($request->filled('verify')) {
            return $this->verifikasiSertifikat((string) $request->query('verify'));
        }

        $totalKader = Anggota::count();
        $totalAktif = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))->count();
        $totalEvent = Event::count();
        $totalLulus = PenilaianAkhir::where('status_kelulusan', 'Lulus')->count();

        // 17 Kecamatan / Pimpinan Cabang Muhammadiyah resmi di Kabupaten Karanganyar
        $pcmList = [
            ['nama' => 'Karanganyar (Kota)', 'zona' => 'Pusat & Barat', 'key' => 'Karanganyar', 'lat' => -7.5964, 'lng' => 110.9515, 'ranting' => 12, 'alamat' => 'Jl. Lawu, Karanganyar Kota'],
            ['nama' => 'Colomadu', 'zona' => 'Pusat & Barat', 'key' => 'Colomadu', 'lat' => -7.5361, 'lng' => 110.7681, 'ranting' => 11, 'alamat' => 'Jl. Adi Sucipto, Colomadu'],
            ['nama' => 'Jaten', 'zona' => 'Pusat & Barat', 'key' => 'Jaten', 'lat' => -7.5794, 'lng' => 110.8973, 'ranting' => 8, 'alamat' => 'Jl. Raya Solo-Tawangmangu, Jaten'],
            ['nama' => 'Kebakkramat', 'zona' => 'Pusat & Barat', 'key' => 'Kebakkramat', 'lat' => -7.5112, 'lng' => 110.9168, 'ranting' => 10, 'alamat' => 'Jl. Raya Solo-Sragen Km. 11'],
            ['nama' => 'Tasikmadu', 'zona' => 'Pusat & Barat', 'key' => 'Tasikmadu', 'lat' => -7.5684, 'lng' => 110.9234, 'ranting' => 10, 'alamat' => 'Kompleks PG Tasikmadu'],
            ['nama' => 'Gondangrejo', 'zona' => 'Pusat & Barat', 'key' => 'Gondangrejo', 'lat' => -7.4789, 'lng' => 110.8356, 'ranting' => 13, 'alamat' => 'Jl. Solo-Purwodadi Km. 12'],
            ['nama' => 'Mojogedang', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Mojogedang', 'lat' => -7.5273, 'lng' => 111.0028, 'ranting' => 13, 'alamat' => 'Pojok, Mojogedang'],
            ['nama' => 'Karangpandan', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Karangpandan', 'lat' => -7.6083, 'lng' => 111.0718, 'ranting' => 11, 'alamat' => 'Jl. Solo-Tawangmangu Km. 28'],
            ['nama' => 'Matesih', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Matesih', 'lat' => -7.6457, 'lng' => 111.0375, 'ranting' => 9, 'alamat' => 'Jl. Matesih-Jumapolo'],
            ['nama' => 'Tawangmangu', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Tawangmangu', 'lat' => -7.6622, 'lng' => 111.1347, 'ranting' => 10, 'alamat' => 'Jl. Raya Tawangmangu'],
            ['nama' => 'Ngargoyoso', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Ngargoyoso', 'lat' => -7.5878, 'lng' => 111.1278, 'ranting' => 9, 'alamat' => 'Kemuning, Ngargoyoso'],
            ['nama' => 'Kerjo', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Kerjo', 'lat' => -7.5028, 'lng' => 111.0567, 'ranting' => 10, 'alamat' => 'Kutho, Kerjo'],
            ['nama' => 'Jenawi', 'zona' => 'Lereng Lawu & Utara', 'key' => 'Jenawi', 'lat' => -7.5342, 'lng' => 111.1364, 'ranting' => 9, 'alamat' => 'Lempong, Jenawi'],
            ['nama' => 'Jumantono', 'zona' => 'Wilayah Selatan', 'key' => 'Jumantono', 'lat' => -7.6719, 'lng' => 110.9786, 'ranting' => 11, 'alamat' => 'Ngunut, Jumantono'],
            ['nama' => 'Jumapolo', 'zona' => 'Wilayah Selatan', 'key' => 'Jumapolo', 'lat' => -7.7081, 'lng' => 111.0264, 'ranting' => 12, 'alamat' => 'Jl. Karanganyar-Jumapolo'],
            ['nama' => 'Jatipuro', 'zona' => 'Wilayah Selatan', 'key' => 'Jatipuro', 'lat' => -7.7656, 'lng' => 111.0189, 'ranting' => 10, 'alamat' => 'Pasar Jatipuro'],
            ['nama' => 'Jatiyoso', 'zona' => 'Wilayah Selatan', 'key' => 'Jatiyoso', 'lat' => -7.7333, 'lng' => 111.1256, 'ranting' => 9, 'alamat' => 'Wonosari, Jatiyoso'],
        ];

        // Hitung kader per kecamatan dari database
        $kaderPerKecamatan = Anggota::select('kecamatan', DB::raw('count(*) as total'))
            ->groupBy('kecamatan')
            ->pluck('total', 'kecamatan')
            ->all();

        $sebaranCabang = collect($pcmList)->map(function ($item) use ($kaderPerKecamatan) {
            $total = $kaderPerKecamatan[$item['key']] ?? ($kaderPerKecamatan[$item['nama']] ?? 0);

            return [
                'nama' => $item['nama'],
                'zona' => $item['zona'],
                'key' => $item['key'],
                'lat' => $item['lat'],
                'lng' => $item['lng'],
                'ranting' => $item['ranting'],
                'alamat' => $item['alamat'],
                'total' => $total,
            ];
        })->sortByDesc('total')->values();

        $maxKader = max(1, $sebaranCabang->max('total'));
        $totalRanting = collect($pcmList)->sum('ranting');

        return view('landing.index', compact(
            'totalKader',
            'totalAktif',
            'totalEvent',
            'totalLulus',
            'sebaranCabang',
            'maxKader',
            'totalRanting'
        ));
    }

    /**
     * Verifikasi publik keaslian sertifikat. Hanya menampilkan data minimum (tanpa NIK/kontak).
     */
    private function verifikasiSertifikat(string $hash): View
    {
        $hash = strtolower(trim($hash));
        $nilai = null;

        if (preg_match('/^[a-f0-9]{64}$/', $hash)) {
            $nilai = PenilaianAkhir::with(['event', 'anggota'])
                ->where('verification_hash', $hash)
                ->whereIn('status_kelulusan', ['Lulus', 'Lulus Bersyarat'])
                ->whereHas('event', fn ($q) => $q->where('status', 'selesai'))
                ->first();

            $aktif = $nilai && $nilai->event->pesertaAktif()->where('anggota_id', $nilai->anggota_id)->exists();
            if (! $aktif) {
                $nilai = null;
            }
        }

        return view('landing.verifikasi', [
            'nilai' => $nilai,
            'kode' => substr($hash, 0, 16),
        ]);
    }
}
