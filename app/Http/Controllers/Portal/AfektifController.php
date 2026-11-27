<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\Event;
use App\Models\InputKertas;
use App\Models\PenilaianAkhir;
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

        $diisiKertas = InputKertas::ada($event->id, auth()->user()->anggota->id, 'afektif');
        $sudahDikirim = $existing->isNotEmpty();

        return view('portal.afektif', compact('event', 'subAspek', 'existing', 'diisiKertas', 'sudahDikirim'));
    }

    public function save(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $anggota = auth()->user()->anggota;

        return DB::transaction(function () use ($event, $request, $anggota) {
            abort_unless($event->pesertaAktif()->where('anggota_id', $anggota->id)->lockForUpdate()->first(), 403);
            PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggota->id)->lockForUpdate()->first();

            if (InputKertas::ada($event->id, $anggota->id, 'afektif')) {
                return back()->with('warning', 'Kuesioner sikap Anda sudah diisikan panitia dari lembar kertas.');
            }

            // Afektif masuk nilai akhir: hanya boleh dikirim sekali.
            if (AfektifJawaban::where('event_id', $event->id)->where('anggota_id', $anggota->id)->exists()) {
                return back()->with('warning', 'Kuesioner sikap sudah Anda kirim dan tidak dapat diubah. Hubungi panitia bila ada kekeliruan.');
            }

            $butirIds = AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $event->id)->where('aktif', true))
                ->orderBy('id')
                ->pluck('id');
            if ($butirIds->isEmpty()) {
                return back()->withErrors(['jawaban' => 'Kuesioner afektif belum disiapkan panitia.']);
            }

            $rules = ['jawaban' => ['required', 'array'], 'jawaban.*' => ['required', 'integer', 'between:1,4']];
            foreach ($butirIds as $butirId) {
                $rules['jawaban.'.$butirId] = $rules['jawaban.*'];
            }

            $data = $request->validate($rules, [
                'jawaban.required' => 'Isi semua butir aktif sebelum menyimpan jawaban.',
                'jawaban.*.required' => 'Isi semua butir aktif sebelum menyimpan jawaban.',
                'jawaban.*.integer' => 'Pilih jawaban yang tersedia untuk setiap butir.',
                'jawaban.*.between' => 'Pilih jawaban yang tersedia untuk setiap butir.',
            ]);

            foreach ($butirIds as $butirId) {
                AfektifJawaban::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggota->id, 'butir_id' => $butirId],
                    ['skor' => $data['jawaban'][$butirId]]
                );
            }

            $this->nilai->hitungUlangPeserta($event, $anggota->id);

            return back()->with('success', 'Afektif tersimpan.');
        }, 3);
    }
}
