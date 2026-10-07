<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Event;
use App\Models\EventSesi;
use App\Services\AbsensiService;
use App\Services\NilaiService;
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

        $totalPeserta = $event->pesertaAktif()->count();
        $totalHadir = Absensi::where('sesi_id', $sesi->id)->count();

        return view('admin.absensi.scan', compact('event', 'sesi', 'totalPeserta', 'totalHadir'));
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
            ->limit(50)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'nama' => $a->anggota->nama_lengkap,
                'waktu' => $a->waktu_scan->format('H:i'),
                'metode' => $a->metode,
            ]);

        $total = Absensi::where('sesi_id', $sesi->id)->count();

        return response()->json([
            'records' => $records,
            'total' => $total,
        ]);
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

    public function destroy(Event $event, EventSesi $sesi, Absensi $absensi): JsonResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id || (int) $absensi->sesi_id !== (int) $sesi->id) {
            abort(403);
        }

        $anggotaId = (int) $absensi->anggota_id;
        $absensi->delete();

        $this->absensi->recalculateAttendance($event->id, $anggotaId);
        app(NilaiService::class)->hitungUlangPeserta($event, $anggotaId);

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil dibatalkan.',
        ]);
    }
}
