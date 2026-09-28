<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanEventExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Services\NilaiService;
use App\Services\QrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EventOutputController extends Controller
{
    public function __construct(
        private readonly NilaiService $nilai,
        private readonly QrService $qr
    ) {}

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

        $verifyUrl = route('landing').'?verify='.urlencode($nilai->verification_hash);
        $qrPng = $this->qr->qrPngDataUriForText($verifyUrl, 4, 1);

        $event->load(['sesi', 'fasilitator']);

        $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('sertifikat-'.str($peserta->anggota->nama_lengkap)->slug().'.pdf');
    }

    public function certificatesBatch(Event $event)
    {
        abort_unless($event->status === 'selesai', 403, 'Sertifikat massal tersedia setelah status kegiatan selesai.');

        $this->nilai->hitungUlang($event);

        $lulusAnggotaIds = PenilaianAkhir::where('event_id', $event->id)
            ->whereIn('status_kelulusan', ['Lulus', 'Lulus Bersyarat'])
            ->pluck('anggota_id')
            ->all();

        abort_if(empty($lulusAnggotaIds), 404, 'Tidak ada peserta yang dinyatakan lulus untuk dicetak sertifikatnya.');

        $pesertas = EventPeserta::where('event_id', $event->id)
            ->whereIn('anggota_id', $lulusAnggotaIds)
            ->where('status_aktif', true)
            ->with(['anggota'])
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        $event->load(['sesi', 'fasilitator']);

        $items = $pesertas->map(function ($ep) use ($event) {
            $nilai = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $ep->anggota_id)->first();
            $verifyUrl = route('landing').'?verify='.urlencode($nilai?->verification_hash ?? '');
            $qrPng = $this->qr->qrPngDataUriForText($verifyUrl, 4, 1);

            return [
                'peserta' => $ep,
                'nilai' => $nilai,
                'qrPng' => $qrPng,
            ];
        });

        $pdf = Pdf::loadView('admin.events.certificates-batch-pdf', compact('event', 'items'))
            ->setPaper('a4', 'landscape');

        $slug = str($event->nama_event)->slug();

        if (request()->has('download')) {
            return $pdf->download("sertifikat-massal-{$slug}.pdf");
        }

        return $pdf->stream("sertifikat-massal-{$slug}.pdf");
    }

    public function exportExcel(Event $event)
    {
        $this->nilai->hitungUlang($event);

        return Excel::download(
            new LaporanEventExport($event->id),
            'laporan-'.str($event->nama_event)->slug().'.xlsx'
        );
    }

    public function idCardsBatch(Event $event)
    {
        $peserta = $event->pesertaAktif()
            ->with(['anggota.user'])
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        abort_if($peserta->isEmpty(), 404, 'Belum ada peserta aktif pada kegiatan ini.');

        $cards = $peserta->map(function ($ep) {
            $fotoDataUri = null;
            if ($ep->anggota->foto && Storage::disk('local')->exists($ep->anggota->foto)) {
                $mime = Storage::disk('local')->mimeType($ep->anggota->foto) ?: 'image/jpeg';
                $content = Storage::disk('local')->get($ep->anggota->foto);
                $fotoDataUri = 'data:'.$mime.';base64,'.base64_encode($content);
            }

            return [
                'id' => $ep->id,
                'no_peserta' => $ep->id,
                'nama' => $ep->anggota->nama_lengkap,
                'nbm' => $ep->anggota->nbm,
                'nik' => $ep->anggota->nik,
                'pcm' => $ep->anggota->pcm ?: ($ep->anggota->kecamatan ?: '-'),
                'jabatan' => $ep->anggota->jabatan_aum ?: 'Peserta Baitul Arqam',
                'qr_token' => $ep->qr_token,
                'foto' => $fotoDataUri,
                'qr' => $this->qr->qrPngDataUri($ep, 4, 1),
            ];
        });

        $pdf = Pdf::loadView('admin.events.idcard-batch-pdf', compact('event', 'cards'))
            ->setPaper('a4', 'portrait');

        $slug = str($event->nama_event)->slug();

        if (request()->has('download')) {
            return $pdf->download("id-cards-batch-{$slug}.pdf");
        }

        return $pdf->stream("id-cards-batch-{$slug}.pdf");
    }
}
