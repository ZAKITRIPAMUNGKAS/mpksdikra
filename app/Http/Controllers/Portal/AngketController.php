<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\AngketKomentar;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AngketController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorize('participate', $event);
        $items = AngketItem::where('event_id', $event->id)->orderBy('urutan')->get()->groupBy('kategori');

        return view('portal.angket', compact('event', 'items'));
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);
        $anggota = auth()->user()->anggota;

        DB::transaction(function () use ($event, $data, $anggota) {
            foreach ($data['jawaban'] as $itemId => $skor) {
                if (! AngketItem::where('event_id', $event->id)->where('id', (int) $itemId)->exists()) {
                    continue;
                }

                AngketJawaban::updateOrCreate(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'item_id' => (int) $itemId], ['skor' => (int) $skor]);
            }

            AngketKomentar::updateOrCreate(['event_id' => $event->id, 'anggota_id' => $anggota->id], ['komentar' => $data['komentar'] ?? null]);
        });

        return back()->with('success', 'Angket tersimpan.');
    }
}
