<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\Rtl;
use App\Services\NilaiService;
use Illuminate\View\View;

class PresentasiController extends Controller
{
    public function __construct(
        private readonly NilaiService $nilaiService
    ) {}

    public function show(Event $event): View
    {
        // 1. Pastikan kalkulasi nilai dan ranking terpadu sinkron terkini
        $this->nilaiService->hitungUlang($event);

        // 2. Data Peserta
        $eventPeserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->get();

        $totalPeserta = $eventPeserta->count();
        $pesertas = $eventPeserta->map->anggota->filter();

        // Demografi Gender
        $lakiLaki = $pesertas->where('jenis_kelamin', 'L')->count();
        $perempuan = $pesertas->where('jenis_kelamin', 'P')->count();
        $persenLaki = $totalPeserta > 0 ? round(($lakiLaki / $totalPeserta) * 100) : 0;
        $persenPerempuan = $totalPeserta > 0 ? round(($perempuan / $totalPeserta) * 100) : 0;

        // Sebaran Cabang / PCM
        $cabangList = $pesertas->map(function ($p) {
            return $p->pcm ?: ($p->kecamatan ?: 'Lainnya');
        })->countBy()->sortDesc();

        // 3. Sesi & Presensi
        $sesiList = EventSesi::where('event_id', $event->id)
            ->withCount('absensi')
            ->orderBy('urutan')
            ->get();

        $totalSesi = $sesiList->count();
        $avgPresensiPersen = 0;
        if ($totalSesi > 0 && $totalPeserta > 0) {
            $totalKehadiran = $sesiList->sum('absensi_count');
            $avgPresensiPersen = round(($totalKehadiran / ($totalSesi * $totalPeserta)) * 100, 1);
        }

        // 4. Penilaian Akhir & Kelulusan
        $penilaians = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->orderBy('ranking')
            ->get();

        $lulusCount = $penilaians->where('status_kelulusan', 'Lulus')->count();
        $bersyaratCount = $penilaians->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $tidakLulusCount = $penilaians->where('status_kelulusan', 'Tidak Lulus')->count();

        $lulusPersen = $totalPeserta > 0 ? round(($lulusCount / $totalPeserta) * 100) : 0;
        $bersyaratPersen = $totalPeserta > 0 ? round(($bersyaratCount / $totalPeserta) * 100) : 0;
        $tidakLulusPersen = $totalPeserta > 0 ? round(($tidakLulusCount / $totalPeserta) * 100) : 0;

        // Rata-rata Nilai Aspek
        $avgPretest = round($penilaians->avg('nilai_pretest') ?? 0, 1);
        $avgPosttest = round($penilaians->avg('nilai_posttest') ?? 0, 1);
        $avgAfektif = round($penilaians->avg('nilai_afektif') ?? 0, 1);
        $avgPsikomotor = round($penilaians->avg('nilai_psikomotor') ?? 0, 1);
        $avgKeaktifan = round($penilaians->avg('nilai_keaktifan') ?? 0, 1);
        $avgNilaiAkhir = round($penilaians->avg('nilai_akhir') ?? 0, 1);

        $peningkatanNilai = round($avgPosttest - $avgPretest, 1);

        // Top 5 Peserta Terbaik
        $top5 = $penilaians->take(5);

        // 5. Data RTL
        $rtlTotal = Rtl::where('event_id', $event->id)->count();
        $rtlDisetujui = Rtl::where('event_id', $event->id)->where('status', 'disetujui')->count();
        $rtlTerkirim = Rtl::where('event_id', $event->id)->where('status', 'terkirim')->count();

        return view('admin.events.presentasi', compact(
            'event',
            'totalPeserta',
            'lakiLaki',
            'perempuan',
            'persenLaki',
            'persenPerempuan',
            'cabangList',
            'sesiList',
            'totalSesi',
            'avgPresensiPersen',
            'penilaians',
            'lulusCount',
            'bersyaratCount',
            'tidakLulusCount',
            'lulusPersen',
            'bersyaratPersen',
            'tidakLulusPersen',
            'avgPretest',
            'avgPosttest',
            'avgAfektif',
            'avgPsikomotor',
            'avgKeaktifan',
            'avgNilaiAkhir',
            'peningkatanNilai',
            'top5',
            'rtlTotal',
            'rtlDisetujui',
            'rtlTerkirim'
        ));
    }
}
