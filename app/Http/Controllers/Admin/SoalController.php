<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PilihanJawaban;
use App\Models\SesiTes;
use App\Models\Soal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SoalController extends Controller
{
    public function index(Event $event, string $tipe): View
    {
        if (! in_array($tipe, ['pretest', 'posttest'], true)) {
            abort(404);
        }

        $soal = Soal::where('event_id', $event->id)
            ->where('tipe', $tipe)
            ->with('pilihan')
            ->orderBy('urutan')
            ->get();

        $sesi = SesiTes::where('event_id', $event->id)->where('tipe', $tipe)->first();

        return view('admin.events.soal', compact('event', 'tipe', 'soal', 'sesi'));
    }

    public function store(Request $request, Event $event, string $tipe): RedirectResponse
    {
        if (! in_array($tipe, ['pretest', 'posttest'], true)) {
            abort(404);
        }

        $data = $request->validate([
            'teks_soal' => ['required', 'string'],
            'pilihan' => ['required', 'array', 'min:2', 'max:6'],
            'pilihan.*.teks' => ['required', 'string'],
            'pilihan.*.is_benar' => ['nullable', 'boolean'],
        ]);

        $urutan = (int) Soal::where('event_id', $event->id)->where('tipe', $tipe)->max('urutan') + 1;

        DB::transaction(function () use ($event, $tipe, $data, $urutan) {
            $soal = Soal::create([
                'event_id' => $event->id,
                'tipe' => $tipe,
                'teks_soal' => $data['teks_soal'],
                'urutan' => $urutan,
            ]);

            $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            foreach ($data['pilihan'] as $i => $pilihan) {
                PilihanJawaban::create([
                    'soal_id' => $soal->id,
                    'label' => $labels[$i] ?? chr(65 + $i),
                    'teks' => $pilihan['teks'],
                    'is_benar' => (bool) ($pilihan['is_benar'] ?? false),
                ]);
            }
        });

        return back()->with('success', 'Soal ditambahkan.');
    }

    public function destroy(Event $event, Soal $soal): RedirectResponse
    {
        if ((int) $soal->event_id !== (int) $event->id) {
            abort(403);
        }

        $soal->delete();

        return back()->with('success', 'Soal dihapus.');
    }

    public function openSession(Request $request, Event $event, string $tipe): RedirectResponse
    {
        if (! in_array($tipe, ['pretest', 'posttest'], true)) {
            abort(404);
        }

        $data = $request->validate(['durasi_menit' => ['required', 'integer', 'min:5', 'max:240']]);

        SesiTes::updateOrCreate(
            ['event_id' => $event->id, 'tipe' => $tipe],
            ['status' => 'aktif', 'durasi_menit' => $data['durasi_menit']]
        );

        return back()->with('success', ucfirst($tipe).' dibuka.');
    }

    public function closeSession(Event $event, string $tipe): RedirectResponse
    {
        SesiTes::where('event_id', $event->id)
            ->where('tipe', $tipe)
            ->update(['status' => 'tutup']);

        return back()->with('success', ucfirst($tipe).' ditutup.');
    }
}
