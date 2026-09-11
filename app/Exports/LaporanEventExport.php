<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\PenilaianAkhir;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanEventExport implements FromView
{
    use Exportable;

    public function __construct(private readonly int $eventId) {}

    public function view(): View
    {
        $ranking = PenilaianAkhir::where('event_id', $this->eventId)
            ->with('anggota')
            ->orderBy('ranking')
            ->get();

        $event = Event::findOrFail($this->eventId);

        return view('exports.laporan-event', compact('ranking', 'event'));
    }
}
