<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\EventSesi;
use App\Services\AbsensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function __construct(private readonly AbsensiService $absensi) {}

    public function scanPage(Event $event, EventSesi $sesi): View
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        return view('admin.absensi.scan', compact('event', 'sesi'));
    }

    public function process(Request $request): JsonResponse
    {
        $data = $request->validate([
            'qr_code' => ['required', 'string', 'max:500'],
            'sesi_id' => ['required', 'integer'],
        ]);

        $result = $this->absensi->scan($data['qr_code'], (int) $data['sesi_id'], auth()->id());

        return response()->json($result, $result['status'] === 'error' ? 422 : 200);
    }

    public function manual(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anggota_id' => ['required', 'integer'],
            'sesi_id' => ['required', 'integer'],
        ]);

        $result = $this->absensi->manual(
            (int) $data['sesi_id'],
            (int) $data['anggota_id'],
            auth()->id()
        );

        return response()->json($result, $result['status'] === 'error' ? 422 : 200);
    }

    public function recent(Event $event, EventSesi $sesi): JsonResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $records = Absensi::where('sesi_id', $sesi->id)
            ->with('anggota:id,nama_lengkap')
            ->latest('waktu_scan')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'nama' => $a->anggota->nama_lengkap,
                'waktu' => $a->waktu_scan->format('H:i:s'),
                'metode' => $a->metode,
            ]);

        return response()->json(['records' => $records]);
    }

    public function cetakLembar(Event $event, EventSesi $sesi)
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $peserta = $event->pesertaAktif()
            ->with('anggota')
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        $pdf = Pdf::loadView('pdf.lembar-presensi', compact('event', 'sesi', 'peserta'))
            ->setPaper('a4', 'portrait');

        $slug = Str::slug($event->nama_event);

        return $pdf->download("lembar-presensi-{$slug}-sesi-{$sesi->urutan}.pdf");
    }
}
