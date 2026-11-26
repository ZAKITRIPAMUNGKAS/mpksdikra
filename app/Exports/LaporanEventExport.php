<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\PesertaTesMulai;
use App\Services\NilaiService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanEventExport implements FromView
{
    use Exportable;

    public function __construct(private readonly int $eventId) {}

    public function view(): View
    {
        $event = Event::findOrFail($this->eventId);
        $ranking = app(NilaiService::class)->rankingAktif($event);
        $totalPeserta = $ranking->count();
        $totalLulus = $ranking->where('status_kelulusan', 'Lulus')->count();
        $totalBersyarat = $ranking->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $totalTidakLulus = $ranking->where('status_kelulusan', 'Tidak Lulus')->count();
        $rerataNA = $ranking->avg('nilai_akhir') ?? 0;
        $tesKertas = PesertaTesMulai::tesKertasMap($this->eventId);

        return view('exports.laporan-event', compact(
            'ranking',
            'event',
            'totalPeserta',
            'totalLulus',
            'totalBersyarat',
            'totalTidakLulus',
            'rerataNA',
            'tesKertas'
        ));
    }
}
