<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Services\AngketKertasService;
use App\Support\LembarJawabanGrafik;
use App\Support\LembarJawabanLayout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Input lembar kertas untuk kuesioner sikap (afektif) dan angket kepuasan.
 */
class AngketKertasController extends Controller
{
    private const NAMA = ['afektif' => 'Kuesioner Sikap (Afektif)', 'angket' => 'Angket Kepuasan'];

    public function __construct(private readonly AngketKertasService $angket) {}

    public function index(Event $event, string $jenis): View
    {
        $this->cekJenis($jenis);

        $butir = $this->angket->butir($event, $jenis);
        $peserta = $this->peserta($event);
        $online = $this->angket->anggotaOnline($event, $jenis);
        $manual = $this->angket->anggotaManual($event, $jenis);
        $tersimpan = $this->angket->tersimpan($event, $jenis, $butir);

        $baris = $peserta->mapWithKeys(fn ($ep) => [$ep->anggota_id => [
            'status' => isset($manual[$ep->anggota_id]) ? 'manual' : (isset($online[$ep->anggota_id]) ? 'online' : 'belum'),
            'waktu' => isset($manual[$ep->anggota_id]) ? $manual[$ep->anggota_id]->updated_at : null,
            'nilai' => null,
        ]])->all();

        $layout = null;
        $layoutError = null;
        if ($butir->isNotEmpty()) {
            try {
                $layout = $this->layout($butir->count(), $jenis);
            } catch (InvalidArgumentException $e) {
                $layoutError = $e->getMessage();
            }
        }

        $nama = self::NAMA[$jenis];
        $skala = $this->angket->skala($jenis);
        $cfg = [
            'judul' => 'Input Kertas — '.$nama,
            'pengantar' => 'Untuk peserta yang mengisi '.strtolower($nama).' di lembar kertas. Ketik jawaban berurutan, satu angka per butir.'
                .($jenis === 'afektif' ? ' Nilai afektif langsung dihitung ulang (butir negatif dibalik otomatis).' : ' Angket ini tidak memengaruhi nilai akhir.'),
            'contoh' => $jenis === 'afektif' ? '34432 41344' : '45534 54445',
            'satuan' => 'butir',
            'jumlah' => $butir->count(),
            'kunci' => null,
            'skala' => $skala,
            'labels' => array_map('strval', array_keys($skala)),
            'kodeLembar' => $jenis,
            'tabs' => collect(self::NAMA)
                ->map(fn ($l, $j) => ['label' => $l, 'url' => route('admin.events.angket-kertas.index', [$event, $j]), 'aktif' => $j === $jenis])
                ->values()->all(),
            'kosong' => $jenis === 'afektif'
                ? ['pesan' => 'Kuesioner sikap belum punya butir aktif.', 'url' => route('admin.events.afektif', $event), 'link' => 'Tambahkan butir afektif']
                : ['pesan' => 'Angket kepuasan belum punya butir.', 'url' => route('admin.events.show', [$event, 'tab' => 'feedback', 'sub_feedback' => 'kelola']), 'link' => 'Siapkan butir angket'],
            'cetak' => [
                ['label' => 'Cetak Lembar Pernyataan', 'url' => route('admin.events.angket-kertas.cetak', [$event, $jenis, 'pernyataan']), 'tabBaru' => true, 'pilihPeserta' => false],
                ['label' => 'Cetak Lembar Jawaban', 'url' => route('admin.events.angket-kertas.cetak', [$event, $jenis, 'jawaban']), 'tabBaru' => false, 'pilihPeserta' => true],
            ],
            'storeUrl' => route('admin.events.angket-kertas.store', [$event, $jenis]),
            'destroyUrl' => route('admin.events.angket-kertas.destroy', [$event, $jenis, '__ID__']),
            'hapusKonfirmasi' => 'Hapus input kertas '.strtolower($nama).' untuk :nama? Jawabannya dihapus dan peserta bisa mengisi lagi.',
            'onlinePesan' => $jenis === 'afektif'
                ? 'Sudah mengisi lewat portal. Klik Ulang Pengisian bila peserta perlu mengisi ulang.'
                : 'Sudah mengisi lewat portal — jawaban online tidak ditimpa dari sini.',
            'ulangUrl' => $jenis === 'afektif' ? route('admin.events.angket-kertas.ulang', [$event, $jenis, '__ID__']) : null,
            'ulangKonfirmasi' => 'Ulang kuesioner afektif untuk :nama? Semua jawaban dan nilai afektif-nya dihapus, lalu peserta bisa mengisi lagi dari portal.',
            'ulangLabel' => 'Ulang Pengisian',
            'semuaOnlinePesan' => 'Semua peserta sudah mengisi online.',
            'simpanLabel' => $jenis === 'afektif' ? 'Simpan & Hitung Nilai' : 'Simpan',
        ];

        return view('admin.events.input-kertas', compact('event', 'cfg', 'peserta', 'baris', 'tersimpan', 'layout', 'layoutError'));
    }

    public function store(Request $request, Event $event, string $jenis): RedirectResponse
    {
        $this->cekJenis($jenis);

        $data = $request->validate([
            'jawaban' => ['required', 'array'],
            'jawaban.*' => ['nullable', 'string', 'max:500'],
        ]);

        $butir = $this->angket->butir($event, $jenis);
        if ($butir->isEmpty()) {
            return back()->with('error', 'Belum ada butir untuk diisi.');
        }

        $aktif = $event->pesertaAktif()->pluck('anggota_id')->flip();
        $tersimpan = $this->angket->tersimpan($event, $jenis, $butir);

        $antrian = [];
        $errors = [];
        foreach ($data['jawaban'] as $anggotaId => $jawaban) {
            $anggotaId = (int) $anggotaId;
            $jawaban = strtoupper(preg_replace('/\s+/', '', (string) $jawaban));

            if ($jawaban === '' || ($tersimpan[$anggotaId] ?? null) === $jawaban) {
                continue;
            }
            if (! $aktif->has($anggotaId)) {
                $errors["jawaban.$anggotaId"] = 'Bukan peserta aktif kegiatan ini.';

                continue;
            }
            if ($this->angket->sudahOnline($event, $anggotaId, $jenis)) {
                $errors["jawaban.$anggotaId"] = 'Peserta sudah mengisi lewat portal; jawaban online tidak ditimpa.';

                continue;
            }

            try {
                $this->angket->petakan($butir, $jenis, $jawaban);
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

        DB::transaction(function () use ($event, $jenis, $antrian) {
            foreach ($antrian as $anggotaId => $jawaban) {
                $this->angket->simpan($event, $anggotaId, $jenis, $jawaban, (int) auth()->id());
            }
            $this->angket->hitungUlangNilai($event, $jenis);
        });

        return back()->with('success', count($antrian).' lembar '.strtolower(self::NAMA[$jenis]).' tersimpan.');
    }

    public function destroy(Event $event, string $jenis, int $anggota): RedirectResponse
    {
        $this->cekJenis($jenis);

        $dihapus = DB::transaction(function () use ($event, $jenis, $anggota) {
            $ok = $this->angket->hapus($event, $anggota, $jenis, (int) auth()->id());
            if ($ok) {
                $this->angket->hitungUlangNilai($event, $jenis);
            }

            return $ok;
        });

        return $dihapus
            ? back()->with('success', 'Input kertas dihapus. Peserta bisa mengisi lagi.')
            : back()->with('error', 'Tidak ada input kertas untuk dihapus. Jawaban online tidak bisa dihapus dari sini.');
    }

    /**
     * Ulang kuesioner afektif peserta yang sudah mengisi online: jawaban & nilai afektif dihapus.
     */
    public function ulang(Event $event, string $jenis, int $anggota): RedirectResponse
    {
        abort_unless($jenis === 'afektif', 404);
        abort_unless($event->peserta()->where('anggota_id', $anggota)->exists(), 404);

        $diulang = DB::transaction(function () use ($event, $jenis, $anggota) {
            $ok = $this->angket->ulangOnline($event, $anggota, $jenis, (int) auth()->id());
            if ($ok) {
                $this->angket->hitungUlangNilai($event, $jenis);
            }

            return $ok;
        });

        if (! $diulang) {
            return back()->with('error', 'Peserta belum mengisi kuesioner afektif online, jadi belum ada yang perlu diulang.');
        }

        return back()->with('success', 'Kuesioner afektif peserta direset. Peserta bisa mengisi ulang'
            .($event->isianDibuka('afektif') ? ' sekarang dari portal.' : ' setelah pengisian afektif dibuka.'));
    }

    public function cetak(Request $request, Event $event, string $jenis, string $lembar): Response
    {
        $this->cekJenis($jenis);

        $butir = $this->angket->butir($event, $jenis);
        abort_if($butir->isEmpty(), 404, 'Belum ada butir.');
        $nama = self::NAMA[$jenis];
        $skala = $this->angket->skala($jenis);

        if ($lembar === 'pernyataan') {
            return response()->view('admin.events.angket-kertas-pernyataan', compact('event', 'jenis', 'nama', 'butir', 'skala'));
        }

        try {
            $layout = $this->layout($butir->count(), $jenis);
        } catch (InvalidArgumentException $e) {
            abort(422, $e->getMessage());
        }
        @ini_set('memory_limit', '512M');
        $latar = LembarJawabanGrafik::latarDataUri($layout);

        $semuaPeserta = $this->peserta($event);
        $semuaPeserta->each(fn ($ep, $i) => $ep->setAttribute('no_urut', $i + 1));
        $selectedIds = EventPeserta::selectedIdsFromRequest($request);
        $pesertaDipilih = $selectedIds !== []
            ? $semuaPeserta->whereIn('id', $selectedIds)->values()
            : $semuaPeserta;
        abort_if($selectedIds !== [] && $pesertaDipilih->isEmpty(), 404, 'Tidak ada peserta terpilih untuk dicetak.');

        $pdf = Pdf::loadView('admin.events.tes-manual-cetak', [
            'event' => $event,
            'tipe' => $jenis,
            'label' => $nama,
            'peserta' => $pesertaDipilih,
            'layout' => $layout,
            'latar' => $latar,
            'satuan' => 'butir',
            'skalaTeks' => collect($skala)->map(fn ($arti, $angka) => $angka.' = '.$arti)->implode(', '),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('lembar-jawaban-'.$jenis.'-'.str($event->nama_event)->slug().'.pdf');
    }

    private function layout(int $jumlah, string $jenis): array
    {
        return LembarJawabanLayout::build($jumlah, array_map('strval', array_keys($this->angket->skala($jenis))), $this->angket->singkatan($jenis));
    }

    private function peserta(Event $event)
    {
        return $event->pesertaAktif()->whereHas('anggota')->with('anggota')->get()->sortBy('anggota.nama_lengkap')->values();
    }

    private function cekJenis(string $jenis): void
    {
        abort_unless(in_array($jenis, AngketKertasService::JENIS, true), 404);
    }
}
