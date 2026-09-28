<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\Keaktifan;
use App\Services\QrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KehadiranController extends Controller
{
    public function __construct(private readonly QrService $qr) {}

    public function index(Event $event): View
    {
        $this->authorize('participate', $event);
        $ep = $this->participantOf($event);

        $sesi = EventSesi::where('event_id', $event->id)->orderBy('urutan')->get();
        $hadir = Absensi::where('event_id', $event->id)
            ->where('anggota_id', $ep->anggota_id)
            ->pluck('sesi_id')
            ->all();

        $qrSvg = $this->qr->qrFor($ep);

        $keaktifanList = Keaktifan::where('event_id', $event->id)
            ->where('anggota_id', $ep->anggota_id)
            ->latest()
            ->get();

        return view('portal.kehadiran', compact('event', 'sesi', 'hadir', 'qrSvg', 'ep', 'keaktifanList'));
    }

    public function idCard(Event $event)
    {
        $this->authorize('participate', $event);
        $ep = $this->participantOf($event);
        $ep->load('anggota');

        $qrPng = $this->qr->qrPngDataUri($ep);

        $fotoDataUri = null;
        if ($ep->anggota->foto && Storage::disk('local')->exists($ep->anggota->foto)) {
            $mime = Storage::disk('local')->mimeType($ep->anggota->foto) ?: 'image/jpeg';
            $content = Storage::disk('local')->get($ep->anggota->foto);
            $fotoDataUri = 'data:'.$mime.';base64,'.base64_encode($content);
        }

        $pdf = Pdf::loadView('portal.idcard-pdf', compact('event', 'ep', 'qrPng', 'fotoDataUri'));

        return $pdf->download('id-card-'.str($ep->anggota->nama_lengkap)->slug().'.pdf');
    }

    private function participantOf(Event $event): EventPeserta
    {
        return EventPeserta::where('event_id', $event->id)
            ->where('anggota_id', auth()->user()->anggota->id)
            ->where('status_aktif', true)
            ->firstOrFail();
    }
}
