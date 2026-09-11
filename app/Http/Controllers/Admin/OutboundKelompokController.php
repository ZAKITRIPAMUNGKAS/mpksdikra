<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\OutboundKelompok;
use App\Services\OutboundGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

class OutboundKelompokController extends Controller
{
    public function index(Event $event): View
    {
        $kelompoks = OutboundKelompok::where('event_id', $event->id)
            ->with(['peserta.anggota'])
            ->get();

        $unassignedPeserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->whereNull('outbound_kelompok_id')
            ->with('anggota')
            ->get();

        $totalAktif = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->count();

        return view('admin.events.outbound', compact('event', 'kelompoks', 'unassignedPeserta', 'totalAktif'));
    }

    public function generate(Request $request, Event $event, OutboundGroupService $service): RedirectResponse
    {
        $data = $request->validate([
            'jumlah_kelompok' => ['required', 'integer', 'min:1', 'max:50'],
            'kriteria' => ['required', Rule::in(['seimbang_gender', 'seimbang_cabang', 'acak_murni'])],
        ]);

        try {
            $service->generate($event, (int) $data['jumlah_kelompok'], $data['kriteria']);

            return back()->with('success', 'Kelompok outbound berhasil diacak.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reset(Event $event, OutboundGroupService $service): RedirectResponse
    {
        $service->reset($event);

        return back()->with('success', 'Pembagian kelompok outbound berhasil direset.');
    }
}
