<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AfektifJawaban;
use App\Models\AngketJawaban;
use App\Models\EventPeserta;
use App\Models\PesertaTesMulai;
use App\Models\Rtl;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $anggota = auth()->user()->anggota;

        $events = EventPeserta::where('anggota_id', $anggota->id)
            ->where('status_aktif', true)
            ->with(['event' => fn ($q) => $q->withCount('sesi')])
            ->latest()
            ->get();

        foreach ($events as $ep) {
            $ep->progress = [
                'pretest' => PesertaTesMulai::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->where('tipe', 'pretest')->whereNotNull('waktu_selesai')->exists(),
                'posttest' => PesertaTesMulai::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->where('tipe', 'posttest')->whereNotNull('waktu_selesai')->exists(),
                'afektif' => AfektifJawaban::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->exists(),
                'angket' => AngketJawaban::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->exists(),
                'rtl' => Rtl::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->whereIn('status', ['terkirim', 'disetujui'])->exists(),
                'hadir_count' => Absensi::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->count(),
                'total_sesi' => (int) ($ep->event?->sesi_count ?? 0),
            ];
        }

        return view('portal.dashboard', compact('events'));
    }
}
