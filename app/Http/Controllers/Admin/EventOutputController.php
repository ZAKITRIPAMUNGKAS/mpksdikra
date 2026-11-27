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
use App\Support\KertasSertifikat;
use App\Support\SertifikatPilihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

        $pesertaSertifikat = SertifikatPilihan::items(
            $event->pesertaAktif()->with('anggota')->get(),
            PenilaianAkhir::where('event_id', $event->id)->get()->keyBy('anggota_id')
        );

        return view('admin.events.ranking', compact('event', 'ranking', 'epMap', 'tesKertas', 'pesertaSertifikat'));
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

    /**
     * Sertifikat satu peserta untuk panitia: bisa dicetak kapan saja dengan status nilai saat itu
     * (belum lulus dicetak sebagai "Peserta"). Portal peserta tetap menunggu kegiatan selesai & lulus.
     */
    public function certificate(Request $request, Event $event, EventPeserta $peserta)
    {
        abort_unless((int) $peserta->event_id === (int) $event->id, 403);

        if (! auth()->user()?->isExecutive()) {
            $this->nilai->hitungUlangPeserta($event, (int) $peserta->anggota_id);
        }
        $nilai = PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $peserta->anggota_id)->first()
            ?? new PenilaianAkhir(['event_id' => $event->id, 'anggota_id' => $peserta->anggota_id]);

        $qrPng = $this->qrSertifikat($nilai);
        $sisi = SertifikatPilihan::sisi($request);

        $event->load(['sesi', 'fasilitator']);

        $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng', 'sisi'))
            ->setPaper(KertasSertifikat::dompdf(), 'landscape');

        return $pdf->download('sertifikat-'.str($peserta->anggota->nama_lengkap)->slug().'.pdf');
    }

    public function certificatesBatch(Request $request, Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $items = $this->itemSertifikat($event, EventPeserta::selectedIdsFromRequest($request));
        $sisi = SertifikatPilihan::sisi($request);
        if ($sisi === 'belakang' && $request->boolean('balik')) {
            // Bolak-balik manual: tumpukan hasil cetak sisi depan dibalik, jadi urutan sisi belakang ikut dibalik.
            $items = $items->reverse()->values();
        }

        $pdf = Pdf::loadView('admin.events.certificates-batch-pdf', compact('event', 'items', 'sisi'))
            ->setPaper(KertasSertifikat::dompdf(), 'landscape');

        $slug = str($event->nama_event)->slug();

        if ($request->has('download')) {
            return $pdf->download("sertifikat-massal-{$slug}.pdf");
        }

        return $pdf->stream("sertifikat-massal-{$slug}.pdf");
    }

    /**
     * Unduh massal: satu file ZIP berisi satu PDF sertifikat per peserta lulus.
     */
    public function certificatesZip(Request $request, Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');

        abort_unless(class_exists(\ZipArchive::class), 500, 'Ekstensi PHP zip belum aktif di server.');

        $items = $this->itemSertifikat($event, EventPeserta::selectedIdsFromRequest($request));

        $base = storage_path('app/tmp');
        File::ensureDirectoryExists($base);
        $this->bersihkanSisaZip($base);

        $kerja = $base.'/sertifikat-'.Str::random(16);
        File::ensureDirectoryExists($kerja);
        $zipPath = $kerja.'.zip';
        $zip = new \ZipArchive;
        abort_unless($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500, 'Gagal membuat file ZIP.');

        try {
            $this->isiZipSertifikat($zip, $event, $items, $kerja);
            $zip->close();
        } catch (\Throwable $e) {
            @$zip->close();
            @unlink($zipPath);
            throw $e;
        } finally {
            File::deleteDirectory($kerja);
        }

        $slug = str($event->nama_event)->slug();

        return response()->download($zipPath, "sertifikat-{$slug}.zip", ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /**
     * Tiap PDF ditulis ke file sementara lalu ditambahkan dengan addFile(), agar memori tidak
     * menumpuk seluruh isi PDF sampai ZIP ditutup.
     */
    private function isiZipSertifikat(\ZipArchive $zip, Event $event, $items, string $kerja): void
    {
        foreach ($items as $i => $item) {
            $peserta = $item['peserta'];
            $nilai = $item['nilai'];
            $qrPng = $item['qrPng'];

            $nama = str($peserta->anggota->nama_lengkap ?? 'peserta')->slug()->value() ?: 'peserta';
            $file = sprintf('%03d-%s.pdf', $i + 1, $nama);

            $pdf = Pdf::loadView('admin.events.certificate-pdf', compact('event', 'peserta', 'nilai', 'qrPng'))
                ->setPaper(KertasSertifikat::dompdf(), 'landscape');
            file_put_contents($kerja.'/'.$file, $pdf->output());
            unset($pdf);
            $zip->addFile($kerja.'/'.$file, $file);
        }
    }

    /**
     * Hapus ZIP/folder kerja sertifikat yang tertinggal lebih dari 1 jam (unduhan terputus).
     */
    private function bersihkanSisaZip(string $base): void
    {
        foreach (glob($base.'/sertifikat-*') ?: [] as $path) {
            if (@filemtime($path) < now()->subHour()->getTimestamp()) {
                is_dir($path) ? File::deleteDirectory($path) : @unlink($path);
            }
        }
    }

    /**
     * Item sertifikat untuk dicetak panitia, kapan saja (tidak menunggu status kegiatan Selesai).
     * Tanpa pilihan: semua peserta aktif berstatus Lulus/Lulus Bersyarat. Dengan pilihan: peserta aktif
     * terpilih apa pun statusnya; yang belum lulus dicetak sebagai "Peserta".
     *
     * @param  array<int, int>  $selectedIds
     */
    private function itemSertifikat(Event $event, array $selectedIds = [])
    {
        if (! auth()->user()?->isExecutive()) {
            $this->nilai->hitungUlang($event);
        }

        $nilai = PenilaianAkhir::where('event_id', $event->id)->get()->keyBy('anggota_id');
        $idLulus = $nilai->whereIn('status_kelulusan', SertifikatPilihan::STATUS_LULUS)->keys()->all();

        $pesertas = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->whereHas('anggota')
            ->when(
                $selectedIds !== [],
                fn ($q) => $q->whereIn('id', $selectedIds),
                fn ($q) => $q->whereIn('anggota_id', $idLulus)
            )
            ->with(['anggota'])
            ->get()
            ->sortBy('anggota.nama_lengkap', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        abort_if($pesertas->isEmpty(), 404, $selectedIds !== []
            ? 'Tidak ada peserta aktif terpilih untuk dicetak sertifikatnya.'
            : 'Belum ada peserta berstatus lulus. Gunakan tombol "Pilih & Cetak Sertifikat" untuk memilih peserta sebelum nilai final.');

        $event->load(['sesi', 'fasilitator']);

        return $pesertas->map(function ($ep) use ($event, $nilai) {
            $n = $nilai->get($ep->anggota_id) ?? new PenilaianAkhir(['event_id' => $event->id, 'anggota_id' => $ep->anggota_id]);

            return [
                'peserta' => $ep,
                'nilai' => $n,
                'qrPng' => $this->qrSertifikat($n),
            ];
        });
    }

    private function qrSertifikat(PenilaianAkhir $nilai): string
    {
        $verifyUrl = route('landing').'?verify='.urlencode((string) $nilai->verification_hash);

        return $this->qr->qrPngDataUriForText($verifyUrl, 4, 1);
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

    public function idCardsBatch(Request $request, Event $event)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $selectedIds = EventPeserta::selectedIdsFromRequest($request);

        $peserta = $event->pesertaAktif()
            ->when($selectedIds !== [], fn ($q) => $q->whereIn('id', $selectedIds))
            ->whereHas('anggota')
            ->with(['anggota.user'])
            ->get()
            ->filter(fn ($ep) => $ep->anggota !== null)
            ->sortBy('anggota.nama_lengkap')
            ->values();

        abort_if($peserta->isEmpty(), 404, $selectedIds !== [] ? 'Tidak ada peserta terpilih untuk dicetak ID Card.' : 'Belum ada peserta aktif pada kegiatan ini.');

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
