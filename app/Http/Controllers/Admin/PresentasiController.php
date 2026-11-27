<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfektifJawaban;
use App\Models\AfektifSubAspek;
use App\Models\AngketJawaban;
use App\Models\AngketKomentar;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\Keaktifan;
use App\Models\PesertaTesMulai;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Services\NilaiService;
use Illuminate\View\View;

class PresentasiController extends Controller
{
    public function __construct(
        private readonly NilaiService $nilaiService
    ) {}

    public function show(Event $event): View
    {
        // 1. Data Peserta Aktif & Demografi
        $eventPeserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->whereHas('anggota')
            ->with('anggota')
            ->get();

        $aktifIds = $eventPeserta->pluck('anggota_id')->values();
        $totalPeserta = $eventPeserta->count();
        $pesertas = $eventPeserta->map->anggota->filter();

        $lakiLaki = $pesertas->where('jenis_kelamin', 'L')->count();
        $perempuan = $pesertas->where('jenis_kelamin', 'P')->count();
        $persenLaki = $totalPeserta > 0 ? (int) round(($lakiLaki / $totalPeserta) * 100) : 0;
        $persenPerempuan = $totalPeserta > 0 ? (int) round(($perempuan / $totalPeserta) * 100) : 0;

        $cabangList = $pesertas->map(fn ($p) => trim((string) ($p->pcm ?: ($p->kecamatan ?: 'Lainnya'))))
            ->filter()
            ->countBy()
            ->sortDesc();

        $aumList = $pesertas->map(fn ($p) => trim((string) $p->nama_aum))
            ->filter()
            ->countBy()
            ->sortDesc();

        $pendidikanList = $pesertas->map(fn ($p) => trim((string) $p->pendidikan_terakhir))
            ->filter()
            ->countBy()
            ->sortDesc();

        // 2. Sesi & Kedisiplinan Presensi (scoped ke peserta aktif)
        $sesiList = EventSesi::where('event_id', $event->id)
            ->withCount([
                'absensi as absensi_count' => fn ($q) => $aktifIds->isNotEmpty()
                    ? $q->whereIn('anggota_id', $aktifIds)
                    : $q->whereRaw('1 = 0'),
            ])
            ->orderBy('urutan')
            ->get();

        $totalSesi = $sesiList->count();
        $sesiMateriCount = $sesiList->where('jenis', 'materi')->count();
        $sesiLainCount = $totalSesi - $sesiMateriCount;

        $avgPresensiPersen = 0.0;
        if ($totalSesi > 0 && $totalPeserta > 0) {
            $totalKehadiran = $sesiList->sum('absensi_count');
            $avgPresensiPersen = round(($totalKehadiran / ($totalSesi * $totalPeserta)) * 100, 1);
        }

        // 3. Ranking & Penilaian Akhir Sinkron via NilaiService
        $penilaians = $this->nilaiService->rankingAktif($event);
        $bobot = $event->getBobotKomposisi();

        $lulusCount = $penilaians->where('status_kelulusan', 'Lulus')->count();
        $bersyaratCount = $penilaians->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $tidakLulusCount = $penilaians->where('status_kelulusan', 'Tidak Lulus')->count();

        $lulusPersen = $totalPeserta > 0 ? (int) round(($lulusCount / $totalPeserta) * 100) : 0;
        $bersyaratPersen = $totalPeserta > 0 ? (int) round(($bersyaratCount / $totalPeserta) * 100) : 0;
        $tidakLulusPersen = $totalPeserta > 0 ? (int) round(($tidakLulusCount / $totalPeserta) * 100) : 0;
        $hadirPenuhCount = $penilaians->filter(fn ($p) => (float) $p->nilai_kehadiran >= 100)->count();

        // Rata-rata Nilai & Kognitif
        $avgPretest = round((float) ($penilaians->avg('nilai_pretest') ?? 0), 1);
        $avgPosttest = round((float) ($penilaians->avg('nilai_posttest') ?? 0), 1);
        $avgAfektif = round((float) ($penilaians->avg('nilai_afektif') ?? 0), 1);
        $avgPsikomotor = round((float) ($penilaians->avg('nilai_psikomotor') ?? 0), 1);
        $avgKeaktifan = round((float) ($penilaians->avg('nilai_keaktifan') ?? 0), 1);
        $avgNilaiAkhir = round((float) ($penilaians->avg('nilai_akhir') ?? 0), 1);
        $avgNGain = round((float) ($penilaians->avg('n_gain') ?? 0), 2);
        $peningkatanNilai = round($avgPosttest - $avgPretest, 1);

        $maxPretest = round((float) ($penilaians->max('nilai_pretest') ?? 0), 1);
        $maxPosttest = round((float) ($penilaians->max('nilai_posttest') ?? 0), 1);
        $maxNilaiAkhir = round((float) ($penilaians->max('nilai_akhir') ?? 0), 1);
        $minNilaiAkhir = $penilaians->isNotEmpty() ? round((float) $penilaians->min('nilai_akhir'), 1) : 0.0;

        $pretestPesertaCount = $aktifIds->isNotEmpty()
            ? PesertaTesMulai::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->where('tipe', 'pretest')
                ->whereNotNull('waktu_selesai')
                ->distinct('anggota_id')
                ->count('anggota_id')
            : 0;

        $posttestPesertaCount = $aktifIds->isNotEmpty()
            ? PesertaTesMulai::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->where('tipe', 'posttest')
                ->whereNotNull('waktu_selesai')
                ->distinct('anggota_id')
                ->count('anggota_id')
            : 0;

        $kategoriNGain = $avgNGain >= 0.7 ? 'Tinggi' : ($avgNGain >= 0.3 ? 'Sedang' : 'Rendah');

        // Distribusi rentang nilai akhir
        $gradeA = $penilaians->filter(fn ($p) => (float) $p->nilai_akhir >= 85)->count();
        $gradeB = $penilaians->filter(fn ($p) => (float) $p->nilai_akhir >= 70 && (float) $p->nilai_akhir < 85)->count();
        $gradeC = $penilaians->filter(fn ($p) => (float) $p->nilai_akhir < 70)->count();

        // Top 5 & Top 10 Peserta Terbaik
        $top5 = $penilaians->take(5);
        $top10 = $penilaians->take(10);

        // 4. Rincian Psikomotor, Afektif per Sub-Aspek, dan Keaktifan per Jenis
        $psikomotorTemplates = PsikomotorTemplate::where('event_id', $event->id)->orderBy('jenis')->orderBy('id')->get();
        $psikoSkorRaw = $aktifIds->isNotEmpty()
            ? PsikomotorNilai::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->selectRaw('template_id, AVG(skor) as avg_skor, COUNT(DISTINCT anggota_id) as jumlah_dinilai')
                ->groupBy('template_id')
                ->get()
                ->keyBy('template_id')
            : collect();

        $psikomotorRincian = $psikomotorTemplates->map(function ($tpl) use ($psikoSkorRaw) {
            $stat = $psikoSkorRaw->get($tpl->id);
            $avgSkor = $stat ? (float) $stat->avg_skor : 0.0;
            $persen = $tpl->skor_maks > 0 ? round(($avgSkor / $tpl->skor_maks) * 100, 1) : 0.0;

            return [
                'nama' => $tpl->nama_aspek,
                'jenis' => $tpl->jenis,
                'skor_maks' => $tpl->skor_maks,
                'avg_skor' => round($avgSkor, 1),
                'persen' => $persen,
                'dinilai' => $stat ? (int) $stat->jumlah_dinilai : 0,
            ];
        });

        $afektifSubAspeks = AfektifSubAspek::where('event_id', $event->id)
            ->where('aktif', true)
            ->with('butir')
            ->orderBy('urutan')
            ->get();

        $afektifJawabans = $aktifIds->isNotEmpty()
            ? AfektifJawaban::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->get()
                ->groupBy('butir_id')
            : collect();

        $afektifRincian = $afektifSubAspeks->map(function ($sub) use ($afektifJawabans) {
            $skorTotal = 0.0;
            $maksTotal = 0.0;
            foreach ($sub->butir as $b) {
                foreach ($afektifJawabans->get($b->id, collect()) as $j) {
                    $skorTotal += $b->is_positif ? $j->skor : (5 - $j->skor);
                    $maksTotal += 4;
                }
            }

            return [
                'nama' => $sub->nama_sub_aspek,
                'butir_count' => $sub->butir->count(),
                'persen' => $maksTotal > 0 ? round(($skorTotal / $maksTotal) * 100, 1) : 0.0,
            ];
        });

        $keaktifanLabels = [
            'tanya' => 'Bertanya',
            'jawab' => 'Menjawab',
            'kultum' => 'Kultum/Presentasi',
            'imam' => 'Imam Shalat',
            'adzan' => 'Adzan',
            'juara_outbound' => 'Juara Outbound',
        ];
        $keaktifanRaw = $aktifIds->isNotEmpty()
            ? Keaktifan::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->selectRaw('jenis, COUNT(*) as total')
                ->groupBy('jenis')
                ->pluck('total', 'jenis')
            : collect();
        $keaktifanTotal = (int) $keaktifanRaw->sum();
        $keaktifanRincian = collect($keaktifanLabels)->map(fn ($label, $key) => [
            'label' => $label,
            'total' => (int) ($keaktifanRaw[$key] ?? 0),
        ])->values();

        // 5. Data RTL (Rencana Tindak Lanjut)
        $rtlStats = $aktifIds->isNotEmpty()
            ? Rtl::where('event_id', $event->id)
                ->whereIn('anggota_id', $aktifIds)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        $rtlDisetujui = (int) ($rtlStats['disetujui'] ?? 0);
        $rtlTerkirim = (int) ($rtlStats['terkirim'] ?? 0);
        $rtlDraf = (int) ($rtlStats['draf'] ?? 0);
        $rtlDitolak = (int) ($rtlStats['ditolak'] ?? 0);
        $rtlTotal = $rtlDisetujui + $rtlTerkirim + $rtlDraf + $rtlDitolak;
        $rtlTuntas = $rtlDisetujui + $rtlTerkirim;
        $rtlBelum = max(0, $totalPeserta - $rtlTotal);
        $rtlPersenTuntas = $totalPeserta > 0 ? (int) round(($rtlTuntas / $totalPeserta) * 100) : 0;
        $rtlSyaratKelulusan = $event->rtlSyaratKelulusan();

        // 6. Evaluasi Penyelenggaraan (Angket Kepuasan & Umpan Balik)
        $angketJawabans = AngketJawaban::where('event_id', $event->id)
            ->when($aktifIds->isNotEmpty(), fn ($q) => $q->whereIn('anggota_id', $aktifIds))
            ->with('item')
            ->get();

        $angketResponden = $angketJawabans->pluck('anggota_id')->unique()->count();
        $angketRerataTotal = $angketJawabans->isNotEmpty() ? round((float) $angketJawabans->avg('skor'), 2) : 0.0;

        $kategoriLabels = [
            'A' => 'Materi & Kurikulum',
            'B' => 'Narasumber & Instruktur',
            'C' => 'Sarana, Konsumsi & Panitia',
        ];
        $angketKategoriStats = collect($kategoriLabels)->map(function ($label, $kode) use ($angketJawabans) {
            $subset = $angketJawabans->filter(fn ($j) => $j->item?->kategori === $kode);
            $avg = $subset->isNotEmpty() ? round((float) $subset->avg('skor'), 2) : 0.0;

            return [
                'kode' => $kode,
                'label' => $label,
                'avg' => $avg,
                'persen' => round(($avg / 5) * 100),
            ];
        })->values();

        $angketKomentarList = AngketKomentar::where('event_id', $event->id)
            ->when($aktifIds->isNotEmpty(), fn ($q) => $q->whereIn('anggota_id', $aktifIds))
            ->whereNotNull('komentar')
            ->where('komentar', '!=', '')
            ->with('anggota')
            ->latest()
            ->take(4)
            ->get();

        $angketPerasaanList = AngketKomentar::where('event_id', $event->id)
            ->when($aktifIds->isNotEmpty(), fn ($q) => $q->whereIn('anggota_id', $aktifIds))
            ->whereNotNull('perasaan')
            ->where('perasaan', '!=', '')
            ->selectRaw('perasaan, COUNT(*) as total')
            ->groupBy('perasaan')
            ->orderByDesc('total')
            ->get();

        return view('admin.events.presentasi', compact(
            'event',
            'totalPeserta',
            'lakiLaki',
            'perempuan',
            'persenLaki',
            'persenPerempuan',
            'cabangList',
            'aumList',
            'pendidikanList',
            'sesiList',
            'totalSesi',
            'sesiMateriCount',
            'sesiLainCount',
            'avgPresensiPersen',
            'hadirPenuhCount',
            'penilaians',
            'bobot',
            'lulusCount',
            'bersyaratCount',
            'tidakLulusCount',
            'lulusPersen',
            'bersyaratPersen',
            'tidakLulusPersen',
            'avgPretest',
            'avgPosttest',
            'avgAfektif',
            'avgPsikomotor',
            'avgKeaktifan',
            'avgNilaiAkhir',
            'avgNGain',
            'kategoriNGain',
            'peningkatanNilai',
            'maxPretest',
            'maxPosttest',
            'maxNilaiAkhir',
            'minNilaiAkhir',
            'pretestPesertaCount',
            'posttestPesertaCount',
            'gradeA',
            'gradeB',
            'gradeC',
            'top5',
            'top10',
            'psikomotorRincian',
            'afektifRincian',
            'keaktifanTotal',
            'keaktifanRincian',
            'rtlTotal',
            'rtlDisetujui',
            'rtlTerkirim',
            'rtlDraf',
            'rtlDitolak',
            'rtlTuntas',
            'rtlBelum',
            'rtlPersenTuntas',
            'rtlSyaratKelulusan',
            'angketResponden',
            'angketRerataTotal',
            'angketKategoriStats',
            'angketKomentarList',
            'angketPerasaanList'
        ));
    }
}
