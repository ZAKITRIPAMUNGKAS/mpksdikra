<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AfektifController extends Controller
{
    public function index(Event $event): View
    {
        $subAspek = AfektifSubAspek::where('event_id', $event->id)
            ->with('butir')
            ->orderBy('urutan')
            ->get();

        return view('admin.events.afektif', compact('event', 'subAspek'));
    }

    public function storeSubAspek(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate(['nama_sub_aspek' => ['required', 'string', 'max:255']]);

        $urutan = (int) AfektifSubAspek::where('event_id', $event->id)->max('urutan') + 1;

        AfektifSubAspek::create(array_merge($data, [
            'event_id' => $event->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Sub-aspek ditambahkan.');
    }

    public function storeButir(Request $request, Event $event, AfektifSubAspek $subAspek): RedirectResponse
    {
        if ((int) $subAspek->event_id !== (int) $event->id) {
            abort(403);
        }

        $data = $request->validate([
            'teks_butir' => ['required', 'string'],
            'is_positif' => ['required', 'boolean'],
        ]);

        $urutan = (int) AfektifButir::where('sub_aspek_id', $subAspek->id)->max('urutan') + 1;

        AfektifButir::create(array_merge($data, [
            'sub_aspek_id' => $subAspek->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Butir ditambahkan.');
    }

    public function destroyButir(Event $event, AfektifButir $butir): RedirectResponse
    {
        if ((int) $butir->subAspek->event_id !== (int) $event->id) {
            abort(403);
        }

        $butir->delete();

        return back()->with('success', 'Butir dihapus.');
    }
}
