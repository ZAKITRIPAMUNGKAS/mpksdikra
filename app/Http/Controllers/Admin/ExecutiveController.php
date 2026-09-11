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

        return view('admin.executive', compact('events', 'totalPeserta', 'nilai'));
    }
}
