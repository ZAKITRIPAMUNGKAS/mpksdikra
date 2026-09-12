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

        // 1. Metric Counts
        $totalAnggota = Anggota::count();
        $pendingAnggota = User::where('role', 'anggota')->where('status', 'menunggu_verifikasi')->count();
        $activeEvents = Event::whereIn('status', ['persiapan', 'berlangsung'])->count();
        $totalEvents = Event::count();
        $completedEvents = Event::where('status', 'selesai')->count();
        $totalLulus = PenilaianAkhir::where('status_kelulusan', 'Lulus')->count();
        $totalPesertaAll = DB::table('event_peserta')->where('status_aktif', true)->count();
        $persenKelulusan = $totalPesertaAll > 0 ? round(($totalLulus / $totalPesertaAll) * 100, 1) : 0;
        $avgNilaiAkhir = PenilaianAkhir::avg('nilai_akhir');

        // 2. Tren Pendaftaran Kader (6 Bulan Terakhir)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->translatedFormat('M Y');
            $count = Anggota::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyLabels[] = $key;
            $monthlyData[] = $count;
        }

        // 3. Distribusi Status Akun Kader
        $statusCounts = [
            'aktif' => User::where('role', 'anggota')->where('status', 'aktif')->count(),
            'menunggu' => $pendingAnggota,
            'ditolak' => User::where('role', 'anggota')->where('status', 'ditolak')->count(),
            'nonaktif' => User::where('role', 'anggota')->where('status', 'nonaktif')->count(),
        ];

        // 4. Komposisi Gender
        $genderL = Anggota::where('jenis_kelamin', 'L')->count();
        $genderP = Anggota::where('jenis_kelamin', 'P')->count();

        // 5. Sebaran Kader per Kecamatan (Top 7)
        $topKecamatan = Anggota::select('kecamatan', DB::raw('count(*) as total'))
            ->whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->groupBy('kecamatan')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        // 6. Kegiatan Baitul Arqam Terkini (Maks 6)
        $recentEvents = Event::withCount('pesertaAktif')
            ->latest('tanggal_mulai')
            ->limit(6)
            ->get();

        // 7. Pendaftar Terbaru Butuh Verifikasi
        $latestPending = Anggota::with('user')
            ->whereHas('user', fn ($q) => $q->where('status', 'menunggu_verifikasi'))
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
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
        ]);
    }
}
