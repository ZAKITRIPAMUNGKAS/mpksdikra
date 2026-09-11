<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SesiController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:materi,sholat,outbound'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable'],
            'jam_selesai' => ['nullable'],
            'pemateri' => ['nullable', 'string', 'max:255'],
        ]);

        $urutan = (int) EventSesi::where('event_id', $event->id)->max('urutan') + 1;

        EventSesi::create(array_merge($data, [
            'event_id' => $event->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Sesi ditambahkan.');
    }

    public function update(Request $request, Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $data = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:materi,sholat,outbound'],
            'tanggal' => ['nullable', 'date'],
            'pemateri' => ['nullable', 'string', 'max:255'],
        ]);

        $sesi->update($data);

        return back()->with('success', 'Sesi diperbarui.');
    }

    public function destroy(Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $sesi->delete();

        return back()->with('success', 'Sesi dihapus.');
    }
}
