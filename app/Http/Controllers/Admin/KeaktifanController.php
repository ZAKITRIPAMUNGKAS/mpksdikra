<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\Keaktifan;
use App\Services\NilaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeaktifanController extends Controller
{
    public function __construct(private readonly NilaiService $nilai) {}

    public function index(Event $event): View
    {
        $peserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->get();

        $counts = Keaktifan::where('event_id', $event->id)
            ->selectRaw('anggota_id, count(*) as total')
            ->groupBy('anggota_id')
            ->pluck('total', 'anggota_id');

        return view('admin.events.keaktifan', compact('event', 'peserta', 'counts'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'anggota_id' => ['required', 'integer'],
            'jenis' => ['required', 'in:tanya,jawab,kultum,imam,adzan,juara_outbound'],
            'sesi_id' => ['nullable', 'integer', 'exists:event_sesi,id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $registered = EventPeserta::where('event_id', $event->id)
            ->where('anggota_id', $data['anggota_id'])
            ->where('status_aktif', true)
            ->exists();

        if (! $registered) {
            return back()->with('error', 'Peserta tidak terdaftar pada event ini.');
        }

        Keaktifan::create([
            'event_id' => $event->id,
            'anggota_id' => $data['anggota_id'],
            'jenis' => $data['jenis'],
            'sesi_id' => $data['sesi_id'] ?? null,
            'dicatat_oleh' => auth()->id(),
            'keterangan' => $data['keterangan'] ?? null,
        ]);

        $this->nilai->hitungUlangPeserta($event, $data['anggota_id']);

        return back()->with('success', 'Keaktifan dicatat.');
    }

    public function destroy(Event $event, Keaktifan $keaktifan): RedirectResponse
    {
        abort_unless((int) $keaktifan->event_id === (int) $event->id, 404);

        $keaktifan->delete();
        $this->nilai->hitungUlangPeserta($event, $keaktifan->anggota_id);

        return back()->with('success', 'Keaktifan dihapus.');
    }
}
