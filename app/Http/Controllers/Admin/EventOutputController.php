<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaExport;
use App\Exports\LaporanEventExport;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Services\ImageCutoutService;
use App\Services\NilaiService;
use App\Services\QrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EventOutputController extends Controller
{
    public function __construct(
        private readonly NilaiService $nilai,
        private readonly QrService $qr,
        private readonly ImageCutoutService $cutout
    ) {}

    public function ranking(Event $event): View
    {
        $ranking = $this->nilai->rankingAktif($event);

        $epMap = $event->pesertaAktif()->pluck('id', 'anggota_id');

        $tesKertas = PesertaTesMulai::tesKertasMap($event->id);

        return view('admin.events.ranking', compact('event', 'ranking', 'epMap', 'tesKertas'));
    }

    public function report(Event $event)
    {
        $ranking = $this->nilai->rankingAktif($event);

        $totalPeserta = $ranking->count();
        $totalLulus = $ranking->where('status_kelulusan', 'Lulus')->count();
        $totalBersyarat = $ranking->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $totalTidakLulus = $ranking->where('status_kelulusan', 'Tidak Lulus')->count();
        $rerataNA = $ranking->avg('nilai_akhir') ?? 0;
        $tertinggiNA = $ranking->max('nilai_akhir') ?? 0;
        $terendahNA = $ranking->min('nilai_akhir') ?? 0;
        $tesKertas = PesertaTesMulai::tesKertasMap($event->id);

        $pdf = Pdf::loadView('admin.events.report-pdf', compact(
            'event',
            'tesKertas',
            'ranking',
            'totalPeserta',
            'totalLulus',
            'totalBersyarat',
            'totalTidakLulus',
            'rerataNA',
            'tertinggiNA',
            'terendahNA'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-'.str($event->nama_event)->slug().'.pdf');
    }

    public function beritaAcara(Event $event)
    {
        $ranking = $this->nilai->rankingAktif($event);

        $totalPeserta = $ranking->count();
        $totalLulus = $ranking->where('status_kelulusan', 'Lulus')->count();
        $totalBersyarat = $ranking->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $totalTidakLulus = $ranking->where('status_kelulusan', 'Tidak Lulus')->count();
        $rerataNA = $ranking->avg('nilai_akhir') ?? 0;
        $tertinggiNA = $ranking->max('nilai_akhir') ?? 0;
        $terendahNA = $ranking->min('nilai_akhir') ?? 0;
        $top5 = $ranking->take(5);

        $event->load(['fasilitator', 'sesi']);

        $pdf = Pdf::loadView('admin.events.berita-acara-pdf', compact(
            'event',
            'ranking',
            'top5',
            'totalPeserta',
            'totalLulus',
            'totalBersyarat',
            'totalTidakLulus',
            'rerataNA',
            'tertinggiNA',
            'terendahNA'
        ))->setPaper('a4', 'portrait');

        $slug = str($event->nama_event)->slug();

        if (request()->has('download')) {
            return $pdf->download("berita-acara-yudisium-{$slug}.pdf");
        }

        return $pdf->stream("berita-acara-yudisium-{$slug}.pdf");
    }

    public function certificate(Event $event, EventPeserta $peserta)
    {
        abort_unless((int) $peserta->event_id === (int) $event->id, 403);
        abort_unless($event->status === 'selesai', 403, 'Sertifikat tersedia setelah event selesai.');
        $nilai = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $peserta->anggota_id)->firstOrFail();
        abort_unless($nilai->status_kelulusan !== 'Tidak Lulus', 403, 'Peserta tidak lulus tidak mendapat sertifikat.');

        $verifyUrl = route('landing').'?verify='.urlencode($nilai->verification_hash);
        $qrPng = $this->qr->qrPngDataUriForText($verifyUrl, 4, 1);

        $event->load(['sesi', 'fasilitator']);

        $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('sertifikat-'.str($peserta->anggota->nama_lengkap)->slug().'.pdf');
    }

    public function certificatesBatch(Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $items = $this->itemSertifikatLulus($event);

        $pdf = Pdf::loadView('admin.events.certificates-batch-pdf', compact('event', 'items'))
            ->setPaper('a4', 'landscape');

        $slug = str($event->nama_event)->slug();

        if (request()->has('download')) {
            return $pdf->download("sertifikat-massal-{$slug}.pdf");
        }

        return $pdf->stream("sertifikat-massal-{$slug}.pdf");
    }

    /**
     * Unduh massal: satu file ZIP berisi satu PDF sertifikat per peserta lulus.
     */
    public function certificatesZip(Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');

        abort_unless(class_exists(\ZipArchive::class), 500, 'Ekstensi PHP zip belum aktif di server.');

        $items = $this->itemSertifikatLulus($event);

        $zipPath = tempnam(sys_get_temp_dir(), 'sertifikat-');
        $zip = new \ZipArchive;
        abort_unless($zip->open($zipPath, \ZipArchive::OVERWRITE) === true, 500, 'Gagal membuat file ZIP.');

        try {
            $this->isiZipSertifikat($zip, $event, $items);
        } catch (\Throwable $e) {
            $zip->close();
            @unlink($zipPath);
            throw $e;
        }
        $zip->close();

        $slug = str($event->nama_event)->slug();

        return response()->download($zipPath, "sertifikat-{$slug}.zip", ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    private function isiZipSertifikat(\ZipArchive $zip, Event $event, $items): void
    {
        foreach ($items as $i => $item) {
            $peserta = $item['peserta'];
            $nilai = $item['nilai'];
            $qrPng = $item['qrPng'];

            $nama = str($peserta->anggota->nama_lengkap ?? 'peserta')->slug()->value() ?: 'peserta';
            $file = sprintf('%03d-%s.pdf', $i + 1, $nama);

            $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng'))
                ->setPaper('a4', 'landscape');
            $zip->addFromString($file, $pdf->output());
            unset($pdf);
        }
    }

    /**
     * Peserta aktif berstatus Lulus/Lulus Bersyarat beserta nilai & QR verifikasi, urut nama.
     */
    private function itemSertifikatLulus(Event $event)
    {
        abort_unless($event->status === 'selesai', 403, 'Sertifikat massal tersedia setelah status kegiatan selesai.');

        $this->nilai->hitungUlang($event);

        $nilaiLulus = PenilaianAkhir::where('event_id', $event->id)
            ->whereIn('status_kelulusan', ['Lulus', 'Lulus Bersyarat'])
            ->get()
            ->keyBy('anggota_id');

        abort_if($nilaiLulus->isEmpty(), 404, 'Tidak ada peserta yang dinyatakan lulus untuk dicetak sertifikatnya.');

        $pesertas = EventPeserta::where('event_id', $event->id)
            ->whereIn('anggota_id', $nilaiLulus->keys()->all())
            ->where('status_aktif', true)
            ->with(['anggota'])
            ->get()
            ->sortBy('anggota.nama_lengkap')
            ->values();

        $event->load(['sesi', 'fasilitator']);

        return $pesertas->map(function ($ep) use ($nilaiLulus) {
            $nilai = $nilaiLulus->get($ep->anggota_id);
            $verifyUrl = route('landing').'?verify='.urlencode($nilai?->verification_hash ?? '');

            return [
                'peserta' => $ep,
                'nilai' => $nilai,
                'qrPng' => $this->qr->qrPngDataUriForText($verifyUrl, 4, 1),
            ];
        });
    }

    /**
     * Biodata lengkap peserta aktif kegiatan (format sama dengan export Pangkalan Data Anggota).
     * Urut PCM lalu nama, tanpa duplikat. Berisi NIK/kontak, jadi khusus admin.
     */
    public function pesertaExcel(Event $event)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $anggotas = $event->pesertaAktif()->with('anggota.user')->get()
            ->pluck('anggota')->filter()->unique('id')
            ->sortBy(fn ($a) => mb_strtolower(($a->pcm ?: $a->kecamatan ?: 'zzz').'|'.$a->nama_lengkap))
            ->values();

        $nama = preg_replace('/[^A-Za-z0-9]+/', '_', $event->nama_event) ?: 'Kegiatan';

        return Excel::download(
            new AnggotaExport($anggotas, 'PESERTA '.mb_strtoupper($event->nama_event)),
            'Peserta_'.trim($nama, '_').'_'.date('Ymd_His').'.xlsx'
        );
    }

    public function exportExcel(Event $event)
    {
        return Excel::download(
            new LaporanEventExport($event->id),
            'laporan-'.str($event->nama_event)->slug().'.xlsx'
        );
    }

    public function idCardsBatch(Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $peserta = $event->pesertaAktif()
            ->whereHas('anggota')
            ->with(['anggota.user'])
            ->get()
            ->filter(fn ($ep) => $ep->anggota !== null)
            ->sortBy('anggota.nama_lengkap')
            ->values();

        abort_if($peserta->isEmpty(), 404, 'Belum ada peserta aktif pada kegiatan ini.');

        $cards = $peserta->map(function ($ep) {
            if (! $ep->anggota) {
                return null;
            }

            $photoInfo = $this->cutout->resolveOriginalForAnggota($ep->anggota);

            return [
                'id' => $ep->id,
                'no_peserta' => $ep->id,
                'nama' => $ep->anggota->nama_lengkap,
                'nbm' => $ep->anggota->nbm,
                'nik' => $ep->anggota->nik,
                'pcm' => $ep->anggota->pcm ?: ($ep->anggota->kecamatan ?: '-'),
                'jabatan' => $ep->anggota->jabatan_aum ?: 'Peserta Baitul Arqam',
                'qr_token' => $ep->qr_token,
                'foto' => $photoInfo['dataUri'],
                'foto_mode' => $photoInfo['mode'],
                'qr' => $this->qr->qrPngDataUri($ep, 4, 1),
            ];
        })->filter()->values();

        $isSingle = false;
        $pdf = Pdf::loadView('admin.events.idcard-batch-pdf', compact('event', 'cards', 'isSingle'))
            ->setPaper('a4', 'portrait');

        $slug = str($event->nama_event)->slug();

        if (request()->has('download')) {
            return $pdf->download("id-cards-batch-{$slug}.pdf");
        }

        return $pdf->stream("id-cards-batch-{$slug}.pdf");
    }
}
