<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Services\NilaiService;
use App\Services\TesService;
use App\Support\LembarJawabanGrafik;
use App\Support\LembarJawabanLayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class TesManualController extends Controller
{
    public function __construct(
        private readonly TesService $tes,
        private readonly NilaiService $nilai
    ) {}

    public function index(Event $event, string $tipe): View
    {
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $soal = $this->tes->soalBerurutan($event, $tipe);
        $kunci = $this->tes->kunciJawaban($soal);

        $peserta = $event->pesertaAktif()
            ->whereHas('anggota')
            ->with('anggota')
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        $status = PesertaTesMulai::where('event_id', $event->id)
            ->where('tipe', $tipe)
            ->get()
            ->keyBy('anggota_id');

        $kolom = $tipe === 'pretest' ? 'nilai_pretest' : 'nilai_posttest';
        $nilai = PenilaianAkhir::where('event_id', $event->id)->pluck($kolom, 'anggota_id');

        $tersimpan = $this->jawabanManualTersimpan($event, $soal, $status);

        $layout = null;
        $layoutError = null;
        if ($soal->isNotEmpty()) {
            try {
                $layout = LembarJawabanLayout::build($soal->count(), $this->labelPilihan($soal));
            } catch (InvalidArgumentException $e) {
                $layoutError = $e->getMessage();
            }
        }

        $baris = $peserta->mapWithKeys(function ($ep) use ($status, $nilai) {
            $st = $status->get($ep->anggota_id);
            $kode = match (true) {
                ! $st => 'belum',
                $st->sumber === 'manual' => 'manual',
                (bool) $st->waktu_selesai => 'online',
                default => 'sedang',
            };

            return [$ep->anggota_id => [
                'status' => $kode,
                'waktu' => $st?->updated_at,
                'nilai' => number_format((float) ($nilai[$ep->anggota_id] ?? 0), 1),
            ]];
        })->all();

        $label = $tipe === 'pretest' ? 'Pretest' : 'Posttest';
        $cfg = [
            'judul' => 'Input Jawaban Kertas — '.$label,
            'pengantar' => 'Untuk peserta yang mengerjakan '.strtolower($label).' di lembar kertas. Ketik jawaban berurutan, satu huruf per soal. Nilai dihitung otomatis dengan rumus yang sama seperti tes online.',
            'contoh' => 'ACBDA BCDAB',
            'satuan' => 'soal',
            'jumlah' => $soal->count(),
            'kunci' => $kunci,
            'skala' => [],
            'labels' => $soal->isNotEmpty() ? $this->labelPilihan($soal) : ['A', 'B', 'C', 'D'],
            'kodeLembar' => $tipe,
            'tabs' => collect(['pretest' => 'Pretest', 'posttest' => 'Posttest'])
                ->map(fn ($l, $t) => ['label' => $l, 'url' => route('admin.events.tes-manual.index', [$event, $t]), 'aktif' => $t === $tipe])
                ->values()->all(),
            'kosong' => ['pesan' => 'Bank soal '.strtolower($label).' masih kosong.', 'url' => route('admin.events.soal.index', [$event, $tipe]), 'link' => 'Tambahkan soal'],
            'cetak' => [
                ['label' => 'Cetak Lembar Soal', 'url' => route('admin.events.tes-manual.cetak', [$event, $tipe, 'soal']), 'tabBaru' => true],
                ['label' => 'Cetak Lembar Jawaban', 'url' => route('admin.events.tes-manual.cetak', [$event, $tipe, 'jawaban']), 'tabBaru' => false],
            ],
            'storeUrl' => route('admin.events.tes-manual.store', [$event, $tipe]),
            'destroyUrl' => route('admin.events.tes-manual.destroy', [$event, $tipe, '__ID__']),
            'hapusKonfirmasi' => 'Hapus input kertas '.strtolower($label).' untuk :nama? Nilainya kembali 0 dan peserta bisa mengerjakan lagi.',
            'onlinePesan' => 'Sudah mengerjakan online — nilai tidak bisa ditimpa dari sini.',
            'semuaOnlinePesan' => 'Semua peserta sudah mengerjakan online.',
            'simpanLabel' => 'Simpan & Hitung Nilai',
        ];

        return view('admin.events.input-kertas', compact('event', 'cfg', 'peserta', 'baris', 'tersimpan', 'layout', 'layoutError'));
    }

    public function store(Request $request, Event $event, string $tipe): RedirectResponse
    {
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['nullable', 'string', 'max:500'],
        ]);

        $soal = $this->tes->soalBerurutan($event, $tipe);
        if ($soal->isEmpty()) {
            return back()->with('error', 'Bank soal '.$tipe.' masih kosong. Tambahkan soal terlebih dahulu.');
        }

        $pesertaIds = $event->pesertaAktif()->pluck('anggota_id')->flip();
        $status = PesertaTesMulai::where('event_id', $event->id)->where('tipe', $tipe)->get()->keyBy('anggota_id');
        $tersimpan = $this->jawabanManualTersimpan($event, $soal, $status);

        $antrian = [];
        $errors = [];
        foreach ($data['jawaban'] as $anggotaId => $jawaban) {
            $anggotaId = (int) $anggotaId;
            $jawaban = $this->tes->normalisasiJawaban($jawaban);

            if ($jawaban === '' || ($tersimpan[$anggotaId] ?? null) === $jawaban) {
                continue;
            }

            if (! $pesertaIds->has($anggotaId)) {
                $errors["jawaban.$anggotaId"] = 'Bukan peserta aktif kegiatan ini.';

                continue;
            }

            if ($this->tes->sudahOnline($event, $anggotaId, $tipe)) {
                $errors["jawaban.$anggotaId"] = 'Peserta sudah mengerjakan online; nilai online tidak ditimpa.';

                continue;
            }

            try {
                $this->tes->petakanJawabanManual($soal, $jawaban);
                $antrian[$anggotaId] = $jawaban;
            } catch (InvalidArgumentException $e) {
                $errors["jawaban.$anggotaId"] = $e->getMessage();
            }
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        if (! $antrian) {
            return back()->with('warning', 'Tidak ada jawaban baru atau perubahan yang disimpan.');
        }

        DB::transaction(function () use ($event, $tipe, $antrian) {
            foreach ($antrian as $anggotaId => $jawaban) {
                $this->tes->simpanJawabanManual($event, $anggotaId, $tipe, $jawaban, (int) auth()->id());
            }
            $this->nilai->hitungUlang($event);
        });

        return back()->with('success', count($antrian).' lembar jawaban '.$tipe.' tersimpan dan dinilai.');
    }

    public function destroy(Event $event, string $tipe, int $anggota): RedirectResponse
    {
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $dihapus = DB::transaction(function () use ($event, $tipe, $anggota) {
            $ok = $this->tes->hapusJawabanManual($event, $anggota, $tipe, (int) auth()->id());
            if ($ok) {
                $this->nilai->hitungUlang($event);
            }

            return $ok;
        });

        return $dihapus
            ? back()->with('success', 'Input kertas '.$tipe.' dihapus. Peserta kembali berstatus belum mengerjakan.')
            : back()->with('error', 'Tidak ada input kertas untuk dihapus. Nilai online tidak bisa dihapus dari sini.');
    }

    public function cetak(Event $event, string $tipe, string $lembar): Response
    {
        abort_unless(in_array($tipe, ['pretest', 'posttest'], true), 404);

        $soal = $this->tes->soalBerurutan($event, $tipe);
        abort_if($soal->isEmpty(), 404, 'Bank soal masih kosong.');
        $label = $tipe === 'pretest' ? 'Pretest' : 'Posttest';

        // Lembar soal dicetak lewat browser: DomPDF tidak bisa menyambung huruf Arab / RTL.
        if ($lembar === 'soal') {
            return response()->view('admin.events.tes-manual-soal', compact('event', 'tipe', 'label', 'soal'));
        }

        $peserta = $event->pesertaAktif()->whereHas('anggota')->with('anggota')->get()->sortBy('anggota.nama_lengkap')->values();

        try {
            $layout = LembarJawabanLayout::build($soal->count(), $this->labelPilihan($soal));
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }
        @ini_set('memory_limit', '512M');
        $latar = LembarJawabanGrafik::latarDataUri($layout);

        $pdf = Pdf::loadView('admin.events.tes-manual-cetak', compact('event', 'tipe', 'label', 'soal', 'peserta', 'layout', 'latar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('lembar-jawaban-'.$tipe.'-'.str($event->nama_event)->slug().'.pdf');
    }

    /**
     * @return array<int, string>
     */
    private function labelPilihan($soal): array
    {
        $labels = $soal->flatMap(fn ($s) => $s->pilihan->pluck('label'))
            ->map(fn ($l) => strtoupper($l))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $labels ?: ['A', 'B', 'C', 'D'];
    }

    /**
     * Rekonstruksi string jawaban untuk peserta yang nilainya diinput manual.
     *
     * @return array<int, string> anggota_id => "ACB-D..."
     */
    private function jawabanManualTersimpan(Event $event, $soal, $status): array
    {
        $manualIds = $status->where('sumber', 'manual')->keys()->all();
        if (! $manualIds || $soal->isEmpty()) {
            return [];
        }

        $urutan = $soal->pluck('id')->flip();
        $hasil = [];

        JawabanPeserta::where('event_id', $event->id)
            ->whereIn('anggota_id', $manualIds)
            ->whereIn('soal_id', $soal->pluck('id'))
            ->with('pilihan')
            ->get()
            ->groupBy('anggota_id')
            ->each(function ($rows, $anggotaId) use ($urutan, $soal, &$hasil) {
                $huruf = array_fill(0, $soal->count(), '-');
                foreach ($rows as $row) {
                    if ($row->pilihan) {
                        $huruf[$urutan[$row->soal_id]] = strtoupper($row->pilihan->label);
                    }
                }
                $hasil[(int) $anggotaId] = implode('', $huruf);
            });

        return $hasil;
    }
}
