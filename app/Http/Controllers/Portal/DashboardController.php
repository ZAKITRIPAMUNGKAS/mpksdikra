<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
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
            ->with('event')
            ->latest()
            ->get();

        foreach ($events as $ep) {
            $ep->progress = [
                'pretest' => PesertaTesMulai::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->where('tipe', 'pretest')->whereNotNull('waktu_selesai')->exists(),
                'posttest' => PesertaTesMulai::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->where('tipe', 'posttest')->whereNotNull('waktu_selesai')->exists(),
                'rtl' => Rtl::where('event_id', $ep->event_id)->where('anggota_id', $anggota->id)->where('status', 'terkirim')->exists(),
            ];
        }

        return view('portal.dashboard', compact('events'));
    }
}
