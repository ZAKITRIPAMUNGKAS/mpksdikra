<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Services\NilaiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PsikomotorController extends Controller
{
    public function __construct(private readonly NilaiService $nilai) {}

    public function index(Event $event): View
    {
        $templates = PsikomotorTemplate::where('event_id', $event->id)->get();
        $peserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->get();

        return view('admin.events.psikomotor', compact('event', 'templates', 'peserta'));
    }

    public function data(Event $event): JsonResponse
    {
        $templates = PsikomotorTemplate::where('event_id', $event->id)->get();
        $nilaiMap = PsikomotorNilai::where('event_id', $event->id)
            ->get()
            ->groupBy('anggota_id');

        $rows = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->get()
            ->map(function ($ep) use ($templates, $nilaiMap) {
                $scores = [];
                $total = 0;

                foreach ($templates as $t) {
                    $v = $nilaiMap->get($ep->anggota_id, collect())->firstWhere('template_id', $t->id);
                    $scores[$t->id] = $v->skor ?? null;
                    $total += $v->skor ?? 0;
                }

                $maks = $templates->sum('skor_maks');

                return [
                    'anggota_id' => $ep->anggota_id,
                    'nama' => $ep->anggota->nama_lengkap,
                    'scores' => $scores,
                    'total' => $total,
                    'persen' => $maks > 0 ? round(($total / $maks) * 100, 2) : 0,
                    'lengkap' => $nilaiMap->get($ep->anggota_id, collect())->count() >= $templates->count(),
                ];
            });

        return response()->json(['templates' => $templates, 'rows' => $rows]);
    }

    public function storeTemplate(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'nama_aspek' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:ibadah,outbound'],
            'skor_maks' => ['required', 'integer', 'min:2', 'max:10'],
        ]);

        PsikomotorTemplate::create(array_merge($data, ['event_id' => $event->id]));

        return response()->json(['status' => 'success']);
    }

    public function destroyTemplate(Event $event, PsikomotorTemplate $template): JsonResponse
    {
        if ((int) $template->event_id !== (int) $event->id) {
            abort(403);
        }

        $template->delete();

        return response()->json(['status' => 'success']);
    }

    public function saveRow(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'anggota_id' => ['required', 'integer'],
            'scores' => ['required', 'array'],
            'scores.*.template_id' => ['required', 'integer'],
            'scores.*.skor' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $templateIds = PsikomotorTemplate::where('event_id', $event->id)->pluck('skor_maks', 'id');

        DB::transaction(function () use ($event, $data, $templateIds) {
            foreach ($data['scores'] as $s) {
                $tid = (int) $s['template_id'];
                if (! $templateIds->has($tid) || (int) $s['skor'] > $templateIds[$tid]) {
                    continue;
                }

                PsikomotorNilai::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'anggota_id' => (int) $data['anggota_id'],
                        'template_id' => $tid,
                    ],
                    [
                        'skor' => (int) $s['skor'],
                        'dinilai_oleh' => auth()->id(),
                    ]
                );
            }

            $this->nilai->hitungUlangPeserta($event, (int) $data['anggota_id']);
        });

        return response()->json(['status' => 'success']);
    }
}
