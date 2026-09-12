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
        $ranking = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->orderBy('ranking')
            ->get();

        $totalPeserta = $ranking->count();
        $totalLulus = $ranking->where('status_kelulusan', 'Lulus')->count();
        $totalBersyarat = $ranking->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $totalTidakLulus = $ranking->where('status_kelulusan', 'Tidak Lulus')->count();
        $rerataNA = $ranking->avg('nilai_akhir') ?? 0;
        $tertinggiNA = $ranking->max('nilai_akhir') ?? 0;
        $terendahNA = $ranking->min('nilai_akhir') ?? 0;

        $pdf = Pdf::loadView('admin.events.report-pdf', compact(
            'event',
            'ranking',
            'totalPeserta',
            'totalLulus',
            'totalBersyarat',
            'totalTidakLulus',
            'rerataNA',
            'tertinggiNA',
            'terendahNA'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-'.str($event->nama_event)->slug().'.pdf');
    }

    public function certificate(Event $event, EventPeserta $peserta)
    {
        abort_unless((int) $peserta->event_id === (int) $event->id, 403);
        abort_unless($event->status === 'selesai', 403, 'Sertifikat tersedia setelah event selesai.');
        $nilai = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $peserta->anggota_id)->firstOrFail();
        abort_unless($nilai->status_kelulusan !== 'Tidak Lulus', 403, 'Peserta tidak lulus tidak mendapat sertifikat.');
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
