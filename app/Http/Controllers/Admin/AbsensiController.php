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
use Illuminate\Http\RedirectResponse;
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

    public function manualPage(Event $event, EventSesi $sesi): View
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $peserta = $event->pesertaAktif()
            ->with(['anggota.user'])
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        $absensiMap = Absensi::where('sesi_id', $sesi->id)
            ->get()
            ->keyBy('anggota_id');

        $previousSessions = EventSesi::where('event_id', $event->id)
            ->where('id', '!=', $sesi->id)
            ->where('urutan', '<', $sesi->urutan)
            ->orderByDesc('urutan')
            ->get();

        $totalPeserta = $peserta->count();
        $totalHadir = $absensiMap->count();

        return view('admin.absensi.manual', compact(
            'event',
            'sesi',
            'peserta',
            'absensiMap',
            'previousSessions',
            'totalPeserta',
            'totalHadir'
        ));
    }

    public function toggle(Request $request, Event $event, EventSesi $sesi, int $anggotaId): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $existing = Absensi::where('sesi_id', $sesi->id)
            ->where('anggota_id', $anggotaId)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->absensi->recalculateAttendance($event->id, $anggotaId);
            app(NilaiService::class)->hitungUlangPeserta($event, $anggotaId);

            return back()->with('success', 'Presensi peserta berhasil dibatalkan.');
        }

        $result = $this->absensi->manual((int) $sesi->id, $anggotaId, auth()->id());
        if ($result['status'] === 'error') {
            return back()->with('error', $result['message']);
        }

        app(NilaiService::class)->hitungUlangPeserta($event, $anggotaId);

        return back()->with('success', "Peserta {$result['nama']} berhasil dicatat hadir.");
    }

    public function bulkAll(Request $request, Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $pesertaAktif = $event->pesertaAktif()->get();
        $hadirIds = Absensi::where('sesi_id', $sesi->id)->pluck('anggota_id')->toArray();
        $now = now();
        $authId = auth()->id();
        $added = 0;

        foreach ($pesertaAktif as $p) {
            if (! in_array($p->anggota_id, $hadirIds, true)) {
                Absensi::create([
                    'event_id' => $event->id,
                    'sesi_id' => $sesi->id,
                    'anggota_id' => $p->anggota_id,
                    'waktu_scan' => $now,
                    'scanned_by' => $authId,
                    'metode' => 'manual',
                ]);
                $this->absensi->recalculateAttendance($event->id, $p->anggota_id);
                app(NilaiService::class)->hitungUlangPeserta($event, $p->anggota_id);
                $added++;
            }
        }

        return back()->with('success', "Berhasil menandai {$added} peserta hadir pada sesi ini.");
    }

    public function copyFromSession(Request $request, Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $data = $request->validate([
            'source_sesi_id' => ['required', 'integer', 'exists:event_sesi,id'],
        ]);

        $sourceSesi = EventSesi::where('event_id', $event->id)->findOrFail($data['source_sesi_id']);
        $sourceHadir = Absensi::where('sesi_id', $sourceSesi->id)->pluck('anggota_id')->toArray();
        $currentHadir = Absensi::where('sesi_id', $sesi->id)->pluck('anggota_id')->toArray();

        $now = now();
        $authId = auth()->id();
        $copied = 0;

        foreach ($sourceHadir as $anggotaId) {
            if (! in_array($anggotaId, $currentHadir, true)) {
                Absensi::create([
                    'event_id' => $event->id,
                    'sesi_id' => $sesi->id,
                    'anggota_id' => $anggotaId,
                    'waktu_scan' => $now,
                    'scanned_by' => $authId,
                    'metode' => 'manual',
                ]);
                $this->absensi->recalculateAttendance($event->id, $anggotaId);
                app(NilaiService::class)->hitungUlangPeserta($event, $anggotaId);
                $copied++;
            }
        }

        return back()->with('success', "Berhasil menyalin presensi {$copied} peserta dari {$sourceSesi->nama_sesi}.");
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
