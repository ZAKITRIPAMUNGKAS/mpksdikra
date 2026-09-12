<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PenilaianAkhir;
use Illuminate\View\View;

class ExecutiveController extends Controller
{
    public function index(): View
    {
        $events = Event::withCount('pesertaAktif')->latest('tanggal_mulai')->get();
        $totalPeserta = $events->sum('peserta_aktif_count');
        $nilai = PenilaianAkhir::query()->avg('nilai_akhir');
        $totalLulus = PenilaianAkhir::where('status_kelulusan', 'Lulus')->count();
        $totalEvaluasi = PenilaianAkhir::count();
        $persenKelulusan = $totalEvaluasi > 0 ? round(($totalLulus / $totalEvaluasi) * 100, 1) : 0;

        return view('admin.executive', compact('events', 'totalPeserta', 'nilai', 'totalLulus', 'persenKelulusan'));
    }
}
