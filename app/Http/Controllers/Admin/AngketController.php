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
        return redirect()->route('admin.events.show', [
            'event' => $event,
            'tab' => 'feedback',
            'sub_feedback' => 'kelola',
        ]);
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

    public function seedDefaults(Event $event): RedirectResponse
    {
        $defaultItems = [
            'A' => [
                'Materi perkaderan relevan dengan penguatan ideologi & kebutuhan persyarikatan.',
                'Alokasi waktu pemaparan materi dan forum diskusi/tanya-jawab memadai.',
                'Materi mudah dipahami dan aplikatif untuk diterapkan di Cabang/Ranting/AUM.',
            ],
            'B' => [
                'Narasumber menguasai materi secara mendalam, inspiratif, dan komunikatif.',
                'Master of Training / Tim Instruktur mendampingi dinamika kelas dengan disiplin dan solutif.',
                'Keteladanan ibadah dan kedisiplinan tim fasilitator sangat baik.',
            ],
            'C' => [
                'Kenyamanan ruangan, pencahayaan, proyektor, dan tata suara audio memuaskan.',
                'Kualitas konsumsi, kudapan, dan ketepatan jadwal istirahat/ibadah terjaga baik.',
                'Kesiapan panitia dalam melayani kebutuhan dan respon kendala peserta sangat memuaskan.',
            ],
        ];

        foreach ($defaultItems as $kategori => $teksList) {
            foreach ($teksList as $urutan => $teks) {
                AngketItem::firstOrCreate([
                    'event_id' => $event->id,
                    'kategori' => $kategori,
                    'urutan' => $urutan + 1,
                ], [
                    'teks_item' => $teks,
                ]);
            }
        }

        return back()->with('success', 'Instrumen feedback & angket standar Baitul Arqam berhasil disiapkan.');
    }
}
