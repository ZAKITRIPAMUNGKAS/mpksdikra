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
        $items = AngketItem::where('event_id', $event->id)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->groupBy('kategori');
        $jawabanLama = $anggota
            ? AngketJawaban::where('event_id', $event->id)->where('anggota_id', $anggota->id)->pluck('skor', 'item_id')->toArray()
            : [];
        $komentarLama = $anggota
            ? AngketKomentar::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first()
            : null;

        $diisiKertas = $anggota && InputKertas::ada($event->id, $anggota->id, 'angket');
        $sudahDikirim = ! empty($jawabanLama);

        return view('portal.angket', compact('event', 'items', 'jawabanLama', 'komentarLama', 'diisiKertas', 'sudahDikirim'));
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $anggota = auth()->user()->anggota;

        if (InputKertas::ada($event->id, $anggota->id, 'angket')) {
            return back()->with('warning', 'Angket Anda sudah diisikan panitia dari lembar kertas.');
        }

        if (AngketJawaban::where('event_id', $event->id)->where('anggota_id', $anggota->id)->exists()) {
            return back()->with('warning', 'Angket sudah Anda kirim dan tidak dapat diubah. Terima kasih atas masukannya.');
        }

        // Semua butir angket kegiatan wajib diisi (diperiksa di server, bukan hanya browser).
        $itemIds = AngketItem::where('event_id', $event->id)->pluck('id');
        if ($itemIds->isEmpty()) {
            return back()->withErrors(['jawaban' => 'Angket belum disiapkan panitia.']);
        }
        $rules = [
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['integer', 'min:1', 'max:5'],
            'perasaan' => ['nullable', 'string', 'max:3000'],
            'komentar' => ['nullable', 'string', 'max:2000'],
        ];
        foreach ($itemIds as $itemId) {
            $rules['jawaban.'.$itemId] = ['required', 'integer', 'min:1', 'max:5'];
        }
        $data = $request->validate($rules, [
            'jawaban.required' => 'Isi semua butir angket sebelum mengirim.',
            'jawaban.*.required' => 'Isi semua butir angket sebelum mengirim.',
            'jawaban.*.integer' => 'Pilih jawaban yang tersedia untuk setiap butir.',
            'jawaban.*.min' => 'Pilih jawaban yang tersedia untuk setiap butir.',
            'jawaban.*.max' => 'Pilih jawaban yang tersedia untuk setiap butir.',
        ]);

        $perasaan = trim((string) ($data['perasaan'] ?? ''));
        $komentar = trim((string) ($data['komentar'] ?? ''));
        $perasaan = $perasaan !== '' ? $perasaan : null;
        $komentar = $komentar !== '' ? $komentar : null;

        DB::transaction(function () use ($event, $data, $anggota, $perasaan, $komentar, $itemIds) {
            foreach ($itemIds as $itemId) {
                AngketJawaban::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggota->id, 'item_id' => (int) $itemId],
                    ['skor' => (int) $data['jawaban'][$itemId]]
                );
            }

            if ($perasaan !== null || $komentar !== null) {
                AngketKomentar::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggota->id],
                    [
                        'perasaan' => $perasaan,
                        'komentar' => $komentar,
                    ]
                );
            } else {
                AngketKomentar::where('event_id', $event->id)
                    ->where('anggota_id', $anggota->id)
                    ->delete();
            }
        });

        return back()->with('success', 'Angket dan feedback perkaderan berhasil disimpan.');
    }
}
