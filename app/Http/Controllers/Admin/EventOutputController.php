<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanEventExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Services\NilaiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EventOutputController extends Controller
{
    public function __construct(private readonly NilaiService $nilai) {}

    public function ranking(Event $event): View
    {
        $this->nilai->hitungUlang($event);
        $ranking = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->orderBy('ranking')
            ->get();

        $epMap = EventPeserta::where('event_id', $event->id)
            ->pluck('id', 'anggota_id');

        return view('admin.events.ranking', compact('event', 'ranking', 'epMap'));
    }

    public function report(Event $event)
    {
        $this->nilai->hitungUlang($event);
        $ranking = PenilaianAkhir::where('event_id', $event->id)->with('anggota')->orderBy('ranking')->get();
        $pdf = Pdf::loadView('admin.events.report-pdf', compact('event', 'ranking'))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-'.str($event->nama_event)->slug().'.pdf');
    }

    public function certificate(Event $event, EventPeserta $peserta)
    {
        abort_unless((int) $peserta->event_id === (int) $event->id, 403);
        $nilai = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $peserta->anggota_id)->firstOrFail();
        $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai'));

        return $pdf->download('sertifikat-'.str($peserta->anggota->nama_lengkap)->slug().'.pdf');
    }

    public function exportExcel(Event $event)
    {
        $this->nilai->hitungUlang($event);

        return Excel::download(
            new LaporanEventExport($event->id),
            'laporan-'.str($event->nama_event)->slug().'.xlsx'
        );
    }
}
