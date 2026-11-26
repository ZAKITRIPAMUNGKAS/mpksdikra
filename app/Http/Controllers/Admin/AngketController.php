<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AngketItem;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AngketController extends Controller
{
    public function index(Event $event): RedirectResponse
    {
        return $this->redirectToFeedbackKelola($event);
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

        return $this->redirectToFeedbackKelola($event)
            ->with('success', 'Item angket ditambahkan.');
    }

    public function destroy(Event $event, AngketItem $item): RedirectResponse
    {
        if ((int) $item->event_id !== (int) $event->id) {
            abort(403);
        }

        $item->delete();

        return $this->redirectToFeedbackKelola($event)
            ->with('success', 'Item dihapus.');
    }

    public function seedDefaults(Event $event): RedirectResponse
    {
        $added = AngketItem::seedDefaultsFor($event);

        $message = $added > 0
            ? 'Instrumen feedback & angket standar Baitul Arqam berhasil disiapkan.'
            : 'Seluruh butir standar feedback & angket sudah tersedia.';

        return $this->redirectToFeedbackKelola($event)
            ->with('success', $message);
    }

    private function redirectToFeedbackKelola(Event $event): RedirectResponse
    {
        return redirect()->route('admin.events.show', [
            'event' => $event,
            'tab' => 'feedback',
            'sub_feedback' => 'kelola',
        ]);
    }
}
