<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AngketItem;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AngketController extends Controller
{
    public function index(Event $event): View
    {
        $items = AngketItem::where('event_id', $event->id)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kategori');

        return view('admin.events.angket', compact('event', 'items'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:5'],
            'teks_item' => ['required', 'string'],
        ]);

        $urutan = (int) AngketItem::where('event_id', $event->id)
            ->where('kategori', $data['kategori'])
            ->max('urutan') + 1;

        AngketItem::create(array_merge($data, [
            'event_id' => $event->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Item angket ditambahkan.');
    }

    public function destroy(Event $event, AngketItem $item): RedirectResponse
    {
        if ((int) $item->event_id !== (int) $event->id) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Item dihapus.');
    }
}
