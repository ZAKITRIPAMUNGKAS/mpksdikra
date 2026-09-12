<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Event;
use App\Services\NilaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AfektifController extends Controller
{
    public function __construct(private readonly NilaiService $nilai) {}

    public function index(Event $event): View
    {
        $this->authorize('participate', $event);
        $subAspek = AfektifSubAspek::where('event_id', $event->id)->where('aktif', true)->with('butir')->orderBy('urutan')->get();

        $existing = AfektifJawaban::where('event_id', $event->id)
            ->where('anggota_id', auth()->user()->anggota->id)
            ->pluck('skor', 'butir_id');

        return view('portal.afektif', compact('event', 'subAspek', 'existing'));
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $data = $request->validate(['jawaban' => ['required', 'array'], 'jawaban.*' => ['required', 'integer', 'min:1', 'max:4']]);
        $anggota = auth()->user()->anggota;

        DB::transaction(function () use ($event, $data, $anggota) {
            foreach ($data['jawaban'] as $butirId => $skor) {
                if (! AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $event->id)->where('aktif', true))
                    ->where('id', (int) $butirId)->exists()) {
                    continue;
                }

                AfektifJawaban::updateOrCreate(['event_id' => $event->id, 'anggota_id' => $anggota->id, 'butir_id' => (int) $butirId], ['skor' => (int) $skor]);
            }

            $this->nilai->hitungUlangPeserta($event, $anggota->id);
        });

        return back()->with('success', 'Afektif tersimpan.');
    }
}
