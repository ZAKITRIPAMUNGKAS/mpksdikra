<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfektifButir;
use App\Models\AfektifSubAspek;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AfektifController extends Controller
{
    public function index(Event $event): View
    {
        $subAspek = AfektifSubAspek::where('event_id', $event->id)
            ->with('butir')
            ->orderBy('urutan')
            ->get();

        $jumlahButir = AfektifButir::join('afektif_sub_aspek', 'afektif_sub_aspek.id', '=', 'afektif_butir.sub_aspek_id')
            ->where('afektif_sub_aspek.event_id', '!=', $event->id)
            ->groupBy('afektif_sub_aspek.event_id')
            ->selectRaw('afektif_sub_aspek.event_id, COUNT(*) as jumlah')
            ->pluck('jumlah', 'event_id');
        $sumberLain = Event::whereIn('id', $jumlahButir->keys())
            ->orderByDesc('tanggal_mulai')
            ->get(['id', 'nama_event'])
            ->each(fn ($e) => $e->jumlah_butir = (int) $jumlahButir[$e->id]);

        return view('admin.events.afektif', compact('event', 'subAspek', 'sumberLain'));
    }

    public function cloneFromEvent(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'source_event_id' => ['required', 'integer', 'exists:events,id', 'not_in:'.$event->id],
        ]);

        $sumber = AfektifSubAspek::where('event_id', $data['source_event_id'])
            ->with('butir')
            ->orderBy('urutan')
            ->get();

        if ($sumber->isEmpty()) {
            return back()->with('error', 'Kegiatan sumber belum punya instrumen penilaian sikap.');
        }

        $subBaru = 0;
        $butirBaru = 0;
        $dilewati = 0;

        DB::transaction(function () use ($event, $sumber, &$subBaru, &$butirBaru, &$dilewati) {
            $target = AfektifSubAspek::where('event_id', $event->id)->with('butir')->get()
                ->keyBy(fn ($sa) => mb_strtolower(trim($sa->nama_sub_aspek)));
            $urutanSub = (int) AfektifSubAspek::where('event_id', $event->id)->max('urutan');

            foreach ($sumber as $saAsal) {
                $kunci = mb_strtolower(trim($saAsal->nama_sub_aspek));
                $sa = $target->get($kunci);
                if (! $sa) {
                    $sa = AfektifSubAspek::create([
                        'event_id' => $event->id,
                        'nama_sub_aspek' => $saAsal->nama_sub_aspek,
                        'urutan' => ++$urutanSub,
                        'aktif' => $saAsal->aktif,
                    ]);
                    $sa->setRelation('butir', collect());
                    $target->put($kunci, $sa);
                    $subBaru++;
                }

                $adaTeks = $sa->butir->map(fn ($b) => mb_strtolower(trim($b->teks_butir)))->flip();
                $urutanButir = (int) $sa->butir->max('urutan');

                foreach ($saAsal->butir as $bAsal) {
                    $teks = mb_strtolower(trim($bAsal->teks_butir));
                    if ($adaTeks->has($teks)) {
                        $dilewati++;

                        continue;
                    }
                    $sa->butir->push(AfektifButir::create([
                        'sub_aspek_id' => $sa->id,
                        'teks_butir' => $bAsal->teks_butir,
                        'is_positif' => $bAsal->is_positif,
                        'urutan' => ++$urutanButir,
                    ]));
                    $adaTeks->put($teks, true);
                    $butirBaru++;
                }
            }
        });

        $nama = Event::whereKey($data['source_event_id'])->value('nama_event');
        $pesan = "Menyalin {$butirBaru} butir ({$subBaru} sub-aspek baru) dari \"{$nama}\".";
        if ($dilewati) {
            $pesan .= " {$dilewati} butir yang sudah ada dilewati.";
        }

        return back()->with($butirBaru ? 'success' : 'warning', $pesan);
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
