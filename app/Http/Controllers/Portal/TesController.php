<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Services\TesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $eventIds = $events->pluck('event_id');
        $sesiMap = SesiTes::whereIn('event_id', $eventIds)
            ->get()
            ->groupBy('event_id')
            ->map(fn ($items) => $items->keyBy('tipe'));

        $mulaiMap = PesertaTesMulai::whereIn('event_id', $eventIds)
            ->where('anggota_id', $anggota->id)
            ->get()
            ->groupBy('event_id')
            ->map(fn ($items) => $items->keyBy('tipe'));

        $nilaiMap = PenilaianAkhir::whereIn('event_id', $eventIds)
            ->where('anggota_id', $anggota->id)
            ->get()
            ->keyBy('event_id');

        foreach ($events as $ep) {
            $sesiEvent = $sesiMap->get($ep->event_id, collect());
            $mulaiEvent = $mulaiMap->get($ep->event_id, collect());
            $nilai = $nilaiMap->get($ep->event_id);

            $ep->tes_status = [
                'pretest' => [
                    'aktif' => ($sesiEvent->get('pretest')?->status === 'aktif'),
                    'selesai' => $mulaiEvent->get('pretest')?->waktu_selesai !== null,
                    'nilai' => $nilai?->nilai_pretest,
                ],
                'posttest' => [
                    'aktif' => ($sesiEvent->get('posttest')?->status === 'aktif'),
                    'selesai' => $mulaiEvent->get('posttest')?->waktu_selesai !== null,
                    'nilai' => $nilai?->nilai_posttest,
                ],
            ];
        }

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

        $mulai = $this->tes->startAttempt($event, $anggota->id, $tipe);
        if ($mulai->waktu_selesai !== null) {
            return redirect()->route('portal.tes.index');
        }

        $remaining = $this->tes->remainingSeconds($mulai, $sesi);
        if ($remaining <= 0) {
            $score = $this->tes->submitAnswers($event, $mulai, [], recoverExpired: true);

            return $this->submissionResponse($score);
        }

        $questions = Soal::where('event_id', $event->id)->where('tipe', $tipe)->with('pilihan')->orderBy('urutan')->get();
        $savedAnswers = JawabanPeserta::where('event_id', $event->id)
            ->where('anggota_id', $anggota->id)
            ->whereIn('soal_id', $questions->modelKeys())
            ->whereNotNull('pilihan_id')
            ->pluck('pilihan_id', 'soal_id')
            ->all();

        return view('portal.tes.take', compact('event', 'tipe', 'questions', 'remaining', 'mulai', 'savedAnswers'));
    }

    public function autosave(Request $request, Event $event, string $tipe): Response
    {
        $this->authorize('participate', $event);
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $data = $request->validate([
            'soal_id' => ['required', 'integer'],
            'pilihan_id' => ['required', 'integer'],
        ]);
        $mulai = PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', auth()->user()->anggota->id)
            ->where('tipe', $tipe)
            ->first();
        abort_unless($mulai, 403, 'Tes belum dimulai.');

        $this->tes->saveDraft($mulai, $data);

        return response()->noContent();
    }

    public function submit(Request $request, Event $event, string $tipe): RedirectResponse
    {
        $this->authorize('participate', $event);
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $sesi = SesiTes::where('event_id', $event->id)->where('tipe', $tipe)->first();
        abort_unless($sesi && $sesi->status === 'aktif', 403, 'Sesi tes tidak aktif.');

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['array:soal_id,pilihan_id'],
            'answers.*.soal_id' => ['required', 'integer'],
            'answers.*.pilihan_id' => ['nullable', 'integer'],
            'auto_submitted' => ['nullable', 'boolean'],
            'timed_out' => ['nullable', 'boolean'],
        ]);
        $autoViolation = $request->boolean('auto_submitted');
        $mulai = $this->tes->startAttempt($event, auth()->user()->anggota->id, $tipe);
        $score = $this->tes->submitAnswers($event, $mulai, $data['answers'] ?? [], $autoViolation || $request->boolean('timed_out'));

        return $this->submissionResponse($score, $autoViolation);
    }

    private function submissionResponse(?float $score, bool $autoViolation = false): RedirectResponse
    {
        if ($score === null) {
            return redirect()->route('portal.tes.index')->with('warning', 'Tes ini sudah diserahkan sebelumnya.');
        }

        if ($autoViolation) {
            return redirect()->route('portal.tes.index')
                ->with('warning', "Ujian otomatis dikumpulkan karena terdeteksi 2 kali pelanggaran (pindah halaman/tab atau menyalin soal). Nilai: {$score}");
        }

        return redirect()->route('portal.tes.index')->with('success', "Tes selesai. Nilai: {$score}");
    }
}
