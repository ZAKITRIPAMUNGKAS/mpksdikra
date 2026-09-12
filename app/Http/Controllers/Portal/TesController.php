<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PesertaTesMulai;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Services\TesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TesController extends Controller
{
    public function __construct(private readonly TesService $tes) {}

    public function index(): View
    {
        $anggota = auth()->user()->anggota;
        $events = EventPeserta::where('anggota_id', $anggota->id)
            ->where('status_aktif', true)
            ->with('event')
            ->latest()
            ->get();

        return view('portal.tes.index', compact('events'));
    }

    public function show(Event $event, string $tipe): View|RedirectResponse
    {
        $this->authorize('participate', $event);
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $sesi = SesiTes::where('event_id', $event->id)->where('tipe', $tipe)->first();
        $anggota = auth()->user()->anggota;

        if (! $sesi || $sesi->status !== 'aktif') {
            return view('portal.tes.closed', compact('event', 'tipe'));
        }

        if ($this->tes->hasSubmitted($event, $anggota->id, $tipe)) {
            return redirect()->route('portal.tes.index');
        }

        $questions = Soal::where('event_id', $event->id)->where('tipe', $tipe)->with('pilihan')->orderBy('urutan')->get();
        $mulai = PesertaTesMulai::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $anggota->id,
            'tipe' => $tipe,
        ], ['waktu_mulai' => now()]);
        $endsAt = $mulai->waktu_mulai->copy()->addMinutes($sesi->durasi_menit);
        $remaining = max(0, $endsAt->getTimestamp() - now()->getTimestamp());

        return view('portal.tes.take', compact('event', 'tipe', 'questions', 'remaining'));
    }

    public function submit(Request $request, Event $event, string $tipe): RedirectResponse
    {
        $this->authorize('participate', $event);
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $anggotaId = auth()->user()->anggota->id;

        $sesi = SesiTes::where('event_id', $event->id)->where('tipe', $tipe)->first();
        abort_unless($sesi && $sesi->status === 'aktif', 403, 'Sesi tes tidak aktif.');

        $mulai = PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->where('tipe', $tipe)
            ->first();
        abort_if(
            $mulai && $mulai->waktu_mulai->copy()->addMinutes($sesi->durasi_menit)->isPast(),
            403,
            'Waktu tes telah habis.'
        );

        if ($this->tes->hasSubmitted($event, $anggotaId, $tipe)) {
            return redirect()->route('portal.tes.index')->with('warning', 'Tes ini sudah diserahkan sebelumnya.');
        }

        $data = $request->validate(['answers' => ['nullable', 'array']]);
        $score = $this->tes->submitAnswers($event, $anggotaId, $tipe, $data['answers'] ?? []);

        return redirect()->route('portal.tes.index')->with('success', "Tes selesai. Nilai: {$score}");
    }
}
