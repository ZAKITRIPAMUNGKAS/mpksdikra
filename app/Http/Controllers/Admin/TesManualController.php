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

        return view('admin.events.tes-manual', compact('event', 'tipe', 'soal', 'kunci', 'peserta', 'status', 'nilai', 'tersimpan', 'layout', 'layoutError'));
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

        $peserta = $lembar === 'jawaban'
            ? $event->pesertaAktif()->whereHas('anggota')->with('anggota')->get()->sortBy('anggota.nama_lengkap')->values()
            : collect();

        $layout = null;
        $latar = null;
        if ($lembar === 'jawaban') {
            try {
                $layout = LembarJawabanLayout::build($soal->count(), $this->labelPilihan($soal));
            } catch (InvalidArgumentException $e) {
                abort(422, $e->getMessage());
            }
            @ini_set('memory_limit', '512M');
            $latar = LembarJawabanGrafik::latarDataUri($layout);
        }

        $label = $tipe === 'pretest' ? 'Pretest' : 'Posttest';
        $pdf = Pdf::loadView('admin.events.tes-manual-cetak', compact('event', 'tipe', 'label', 'lembar', 'soal', 'peserta', 'layout', 'latar'))
            ->setPaper('a4', 'portrait');

        return $pdf->download(($lembar === 'soal' ? 'lembar-soal-' : 'lembar-jawaban-').$tipe.'-'.str($event->nama_event)->slug().'.pdf');
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
