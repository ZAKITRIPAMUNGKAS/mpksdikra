<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\AngketKomentar;
use App\Models\Event;
use App\Models\InputKertas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AngketController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorize('participate', $event);
        $anggota = auth()->user()->anggota;
        $items = AngketItem::where('event_id', $event->id)->orderBy('urutan')->get()->groupBy('kategori');
        $jawabanLama = $anggota
            ? AngketJawaban::where('event_id', $event->id)->where('anggota_id', $anggota->id)->pluck('skor', 'item_id')->toArray()
            : [];
        $komentarLama = $anggota
            ? AngketKomentar::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first()
            : null;

        $diisiKertas = $anggota && InputKertas::ada($event->id, $anggota->id, 'angket');

        return view('portal.angket', compact('event', 'items', 'jawabanLama', 'komentarLama', 'diisiKertas'));
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['required', 'integer', 'min:1', 'max:5'],
            'perasaan' => ['nullable', 'string', 'max:3000'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ]);
        $anggota = auth()->user()->anggota;

        if (InputKertas::ada($event->id, $anggota->id, 'angket')) {
            return back()->with('warning', 'Angket Anda sudah diisikan panitia dari lembar kertas.');
        }

        DB::transaction(function () use ($event, $data, $anggota) {
            foreach ($data['jawaban'] as $itemId => $skor) {
                if (! AngketItem::where('event_id', $event->id)->where('id', (int) $itemId)->exists()) {
                    continue;
                }

                AngketJawaban::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggota->id, 'item_id' => (int) $itemId],
                    ['skor' => (int) $skor]
                );
            }

            AngketKomentar::updateOrCreate(
                ['event_id' => $event->id, 'anggota_id' => $anggota->id],
                [
                    'perasaan' => $data['perasaan'] ?? null,
                    'komentar' => $data['komentar'] ?? null,
                ]
            );
        });

        return back()->with('success', 'Angket dan feedback perkaderan berhasil disimpan.');
    }
}
