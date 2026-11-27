<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Services\QrService;
use App\Support\KertasSertifikat;
use Barryvdh\DomPDF\Facade\Pdf;

class SertifikatController extends Controller
{
    public function download(Event $event)
    {
        $this->authorize('participate', $event);
        $ep = EventPeserta::where('event_id', $event->id)
            ->where('anggota_id', auth()->user()->anggota->id)
            ->where('status_aktif', true)
            ->firstOrFail();

        $nilai = PenilaianAkhir::where('event_id', $event->id)
            ->where('anggota_id', $ep->anggota_id)
            ->first();

        abort_unless($event->status === 'selesai', 403, 'Sertifikat tersedia setelah event selesai.');
        abort_unless($nilai && $nilai->status_kelulusan !== 'Tidak Lulus', 403, 'Sertifikat hanya untuk peserta yang lulus.');

        $ep->load('anggota');
        $event->load(['sesi', 'fasilitator']);
        $peserta = $ep;

        $verifyUrl = route('landing').'?verify='.urlencode($nilai->verification_hash ?? '');
        $qrPng = app(QrService::class)->qrPngDataUriForText($verifyUrl, 4, 1);

        $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng'))
            ->setPaper(KertasSertifikat::dompdf(), 'landscape');

        return $pdf->download('sertifikat-'.str($ep->anggota->nama_lengkap)->slug().'.pdf');
    }
}
