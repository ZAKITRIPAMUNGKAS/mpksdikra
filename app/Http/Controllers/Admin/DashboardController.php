<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\PenilaianAkhir;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()?->isExecutive()) {
            return redirect()->route('admin.executive');
        }

        $user = auth()->user();
        $isCabang = $user?->isAdminCabang();
        $pcm = $user?->pcm;

        $anggotaQuery = Anggota::query();
        $userAnggotaQuery = User::where('role', 'anggota');

        if ($isCabang && $pcm) {
            $anggotaQuery->where(fn ($sub) => $sub->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
            $userAnggotaQuery->whereHas('anggota', fn ($aq) => $aq->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
        }

        // 1. Metric Counts
        $totalAnggota = (clone $anggotaQuery)->count();
        $pendingAnggota = (clone $userAnggotaQuery)->where('status', 'menunggu_verifikasi')->count();
        $activeEvents = $isCabang ? 0 : Event::whereIn('status', ['persiapan', 'berlangsung'])->count();
        $totalEvents = $isCabang ? 0 : Event::count();
        $completedEvents = $isCabang ? 0 : Event::where('status', 'selesai')->count();
        $totalLulus = $isCabang ? 0 : PenilaianAkhir::where('status_kelulusan', 'Lulus')->count();
        $totalPesertaAll = $isCabang ? 0 : DB::table('event_peserta')->where('status_aktif', true)->count();
        $persenKelulusan = $totalPesertaAll > 0 ? round(($totalLulus / $totalPesertaAll) * 100, 1) : 0;
        $avgNilaiAkhir = $isCabang ? 0 : PenilaianAkhir::avg('nilai_akhir');

        // 2. Tren Pendaftaran Kader (6 Bulan Terakhir)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->translatedFormat('M Y');
            $count = (clone $anggotaQuery)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyLabels[] = $key;
            $monthlyData[] = $count;
        }

        // 3. Distribusi Status Akun Kader
        $statusCounts = [
            'aktif' => (clone $userAnggotaQuery)->where('status', 'aktif')->count(),
            'menunggu' => $pendingAnggota,
            'ditolak' => (clone $userAnggotaQuery)->where('status', 'ditolak')->count(),
            'nonaktif' => (clone $userAnggotaQuery)->where('status', 'nonaktif')->count(),
        ];

        // 4. Komposisi Gender
        $genderL = (clone $anggotaQuery)->where('jenis_kelamin', 'L')->count();
        $genderP = (clone $anggotaQuery)->where('jenis_kelamin', 'P')->count();

        // 5. Sebaran Kader: Jika cabang, seluruh desa/kelurahan di PCM tsb. Jika daerah, seluruh 17 Cabang PCM se-Kabupaten
        if ($isCabang && $pcm) {
            $topKecamatan = (clone $anggotaQuery)->select('desa_kelurahan as kecamatan', DB::raw('count(*) as total'))
                ->whereNotNull('desa_kelurahan')
                ->where('desa_kelurahan', '!=', '')
                ->groupBy('desa_kelurahan')
                ->orderByDesc('total')
                ->get();
        } else {
            $cabangPcmList = config('arqam.opsi_form.cabang_pcm', []);
            $kaderCounts = Anggota::select('kecamatan', DB::raw('count(*) as total'))
                ->whereNotNull('kecamatan')
                ->where('kecamatan', '!=', '')
                ->groupBy('kecamatan')
                ->pluck('total', 'kecamatan')
                ->all();

            $matched = [];
            $sebaran = collect($cabangPcmList)->map(function ($cabang) use ($kaderCounts, &$matched) {
                $total = 0;
                foreach ($kaderCounts as $key => $count) {
                    if (strcasecmp(trim($key), trim($cabang)) === 0 || strcasecmp(trim($key), 'PCM '.trim($cabang)) === 0) {
                        $total += $count;
                        $matched[$key] = true;
                    }
                }

                return (object) [
                    'kecamatan' => $cabang,
                    'total' => $total,
                ];
            });

            foreach ($kaderCounts as $key => $count) {
                if (! isset($matched[$key])) {
                    $sebaran->push((object) [
                        'kecamatan' => $key,
                        'total' => $count,
                    ]);
                }
            }

            $topKecamatan = $sebaran->sortByDesc('total')->values();
        }

        // 6. Kegiatan Baitul Arqam Terkini (Maks 6)
        $recentEvents = $isCabang ? collect() : Event::withCount('pesertaAktif')
            ->latest('tanggal_mulai')
            ->limit(6)
            ->get();

        // 7. Pendaftar Terbaru Butuh Verifikasi
        $latestPending = (clone $anggotaQuery)->with('user')
            ->whereHas('user', fn ($q) => $q->where('status', 'menunggu_verifikasi'))
            ->latest()
            ->limit(5)
            ->get();

        // 8. Kader Terverifikasi Terbaru (Untuk Cabang)
        $latestAktif = $isCabang ? (clone $anggotaQuery)->with('user')
            ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->latest('updated_at')
            ->limit(5)
            ->get() : collect();

        // 9. Statistik Profil & Demografi Database Kader (Pekerjaan, Kemasjidan, Pendidikan, Al-Qur'an)
        $distribusiPekerjaan = (clone $anggotaQuery)
            ->select('pekerjaan', DB::raw('count(*) as total'))
            ->whereNotNull('pekerjaan')
            ->where('pekerjaan', '!=', '')
            ->groupBy('pekerjaan')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $pekerjaanDominan = $distribusiPekerjaan->first()?->pekerjaan ?? 'Belum terisi';
        $pekerjaanDominanCount = $distribusiPekerjaan->first()?->total ?? 0;
        $persenPekerjaanDominan = $totalAnggota > 0 ? round(($pekerjaanDominanCount / $totalAnggota) * 100) : 0;

        $distribusiSholatMasjid = (clone $anggotaQuery)
            ->select('aktivitas_sholat_masjid', DB::raw('count(*) as total'))
            ->whereNotNull('aktivitas_sholat_masjid')
            ->where('aktivitas_sholat_masjid', '!=', '')
            ->groupBy('aktivitas_sholat_masjid')
            ->orderByDesc('total')
            ->get();

        $totalResponMasjid = $distribusiSholatMasjid->sum('total');
        $selaluMasjidCount = (clone $anggotaQuery)
            ->where('aktivitas_sholat_masjid', 'like', '%Selalu%')
            ->count();
        $persenSelaluMasjid = $totalResponMasjid > 0 ? round(($selaluMasjidCount / $totalResponMasjid) * 100) : 0;

        $takmirCount = (clone $anggotaQuery)
            ->where(function ($q) {
                $q->where('keaktifan_takmir', 'Ya')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('jabatan_takmir')->where('jabatan_takmir', '!=', '');
                    });
            })->count();
        $persenTakmir = $totalAnggota > 0 ? round(($takmirCount / $totalAnggota) * 100) : 0;

        $distribusiPendidikan = (clone $anggotaQuery)
            ->select('pendidikan_terakhir', DB::raw('count(*) as total'))
            ->whereNotNull('pendidikan_terakhir')
            ->where('pendidikan_terakhir', '!=', '')
            ->groupBy('pendidikan_terakhir')
            ->orderByDesc('total')
            ->get();

        $pendidikanDominan = $distribusiPendidikan->first()?->pendidikan_terakhir ?? '-';

        $totalResponQuran = (clone $anggotaQuery)
            ->whereNotNull('kemampuan_baca_quran')
            ->where('kemampuan_baca_quran', '!=', '')
            ->count();
        $quranLancarCount = (clone $anggotaQuery)
            ->where('kemampuan_baca_quran', 'like', '%Lancar%')
            ->count();
        $persenQuranLancar = $totalResponQuran > 0 ? round(($quranLancarCount / $totalResponQuran) * 100) : 0;

        return view('admin.dashboard', [
            'isCabang' => $isCabang,
            'cabangNama' => $pcm,
            'totalAnggota' => $totalAnggota,
            'pendingAnggota' => $pendingAnggota,
            'activeEvents' => $activeEvents,
            'totalEvents' => $totalEvents,
            'completedEvents' => $completedEvents,
            'totalLulus' => $totalLulus,
            'totalPesertaAll' => $totalPesertaAll,
            'persenKelulusan' => $persenKelulusan,
            'avgNilaiAkhir' => $avgNilaiAkhir,
            'monthlyLabels' => $monthlyLabels,
            'monthlyData' => $monthlyData,
            'statusCounts' => $statusCounts,
            'genderL' => $genderL,
            'genderP' => $genderP,
            'topKecamatan' => $topKecamatan,
            'recentEvents' => $recentEvents,
            'latestPending' => $latestPending,
            'latestAktif' => $latestAktif,
            'distribusiPekerjaan' => $distribusiPekerjaan,
            'pekerjaanDominan' => $pekerjaanDominan,
            'pekerjaanDominanCount' => $pekerjaanDominanCount,
            'persenPekerjaanDominan' => $persenPekerjaanDominan,
            'distribusiSholatMasjid' => $distribusiSholatMasjid,
            'totalResponMasjid' => $totalResponMasjid,
            'selaluMasjidCount' => $selaluMasjidCount,
            'persenSelaluMasjid' => $persenSelaluMasjid,
            'takmirCount' => $takmirCount,
            'persenTakmir' => $persenTakmir,
            'distribusiPendidikan' => $distribusiPendidikan,
            'pendidikanDominan' => $pendidikanDominan,
            'persenQuranLancar' => $persenQuranLancar,
        ]);
    }
}
