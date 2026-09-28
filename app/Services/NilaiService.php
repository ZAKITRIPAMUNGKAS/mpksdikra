<?php

namespace App\Services;

use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\Keaktifan;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use Illuminate\Support\Facades\DB;

class NilaiService
{
    public function __construct(private readonly AbsensiService $absensi) {}

    public function hitungAfektif(Event $event, int $anggotaId): float
    {
        $butir = AfektifButir::whereHas('subAspek', fn ($q) => $q->where('event_id', $event->id)->where('aktif', true))
            ->get()
            ->keyBy('id');

        if ($butir->isEmpty()) {
            return 0.0;
        }

        $jawaban = AfektifJawaban::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->get();

        $skor = 0.0;
        $maks = 0.0;

        foreach ($jawaban as $j) {
            $b = $butir->get($j->butir_id);

            if (! $b) {
                continue;
            }

            $skor += $b->is_positif ? $j->skor : (5 - $j->skor);
            $maks += 4;
        }

        $nilai = $maks > 0 ? round(($skor / $maks) * 100, 2) : 0.0;

        return $nilai;
    }

    public function hitungPsikomotor(Event $event, int $anggotaId): float
    {
        $templates = PsikomotorTemplate::where('event_id', $event->id)->get();

        if ($templates->isEmpty()) {
            return 0.0;
        }

        $totalSkor = PsikomotorNilai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->sum('skor');

        $maks = $templates->sum('skor_maks');

        return $maks > 0 ? round(($totalSkor / $maks) * 100, 2) : 0.0;
    }

    public function hitungKeaktifan(Event $event, int $anggotaId): float
    {
        $poin = Keaktifan::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->count();

        $bonusMaks = (float) config('arqam.keaktifan.bonus_maks', 10);
        $poinTerkap = min((float) $poin, $bonusMaks);

        return $bonusMaks > 0 ? round(($poinTerkap / $bonusMaks) * 100, 2) : 0.0;
    }

    public function hitungUlangPeserta(Event $event, int $anggotaId): void
    {
        $this->absensi->recalculateAttendance($event->id, $anggotaId);
        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $anggotaId],
            [
                'nilai_afektif' => $this->hitungAfektif($event, $anggotaId),
                'nilai_psikomotor' => $this->hitungPsikomotor($event, $anggotaId),
                'nilai_keaktifan' => $this->hitungKeaktifan($event, $anggotaId),
            ]
        );

        $semuaAktif = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->pluck('anggota_id')
            ->all();

        $this->hitungNilaiAkhirDanRanking($event, $semuaAktif);
    }

    public function hitungUlang(Event $event): void
    {
        $anggotaIds = Anggota::whereIn(
            'id',
            EventPeserta::where('event_id', $event->id)
                ->where('status_aktif', true)
                ->pluck('anggota_id')
        )->pluck('id');

        DB::transaction(function () use ($event, $anggotaIds) {
            foreach ($anggotaIds as $anggotaId) {
                $this->absensi->recalculateAttendance($event->id, $anggotaId);

                $afektif = $this->hitungAfektif($event, $anggotaId);
                $psikomotor = $this->hitungPsikomotor($event, $anggotaId);
                $keaktifan = $this->hitungKeaktifan($event, $anggotaId);

                PenilaianAkhir::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggotaId],
                    [
                        'nilai_afektif' => $afektif,
                        'nilai_psikomotor' => $psikomotor,
                        'nilai_keaktifan' => $keaktifan,
                    ]
                );
            }

            $this->hitungNilaiAkhirDanRanking($event, $anggotaIds->all());
        });
    }

    public function hitungNilaiAkhirDanRanking(Event $event, array $anggotaIds): void
    {
        foreach ($anggotaIds as $anggotaId) {
            $p = PenilaianAkhir::firstOrNew(
                ['event_id' => $event->id, 'anggota_id' => $anggotaId]
            );

            $bobot = config('arqam.bobot');
            $keaktifanCfg = config('arqam.keaktifan');

            $ngainScale = $this->nGain((float) $p->nilai_pretest, (float) $p->nilai_posttest) * 100;

            $kognitif = ((float) $p->nilai_posttest * $bobot['posttest_dalam_kognitif'])
                + ($ngainScale * $bobot['ngain_dalam_kognitif']);

            $psikomotor = ((float) $p->nilai_psikomotor * $keaktifanCfg['bobot_rubrik'])
                + ((float) $p->nilai_keaktifan * $keaktifanCfg['bobot_keaktifan']);

            $nilaiAkhir = ($kognitif * $bobot['kognitif'])
                + ($psikomotor * $bobot['psikomotor'])
                + ((float) $p->nilai_afektif * $bobot['afektif']);

            $p->nilai_akhir = round($nilaiAkhir, 2);
            $p->status_kelulusan = $this->tentukanStatusKelulusan($event, $p, $anggotaId);
            $p->verification_hash = hash('sha256', $event->id.'|'.$anggotaId.'|'.config('app.key'));
            $p->save();
        }

        $ranked = PenilaianAkhir::where('penilaian_akhir.event_id', $event->id)
            ->join('event_peserta', function ($join) use ($event) {
                $join->on('event_peserta.anggota_id', '=', 'penilaian_akhir.anggota_id')
                    ->where('event_peserta.event_id', '=', $event->id)
                    ->where('event_peserta.status_aktif', '=', true);
            })
            ->join('anggota', function ($join) {
                $join->on('anggota.id', '=', 'penilaian_akhir.anggota_id')
                    ->whereNull('anggota.deleted_at');
            })
            ->select('penilaian_akhir.*')
            ->orderByDesc('penilaian_akhir.nilai_akhir')
            ->orderByDesc('penilaian_akhir.nilai_posttest')
            ->orderBy('anggota.nama_lengkap')
            ->get();

        foreach ($ranked as $index => $p) {
            $p->ranking = $index + 1;
            $p->save();
        }
    }

    public function nGain(float $pretest, float $posttest): float
    {
        if ($posttest === 100.0) {
            return 1.0;
        }

        $denominator = 100 - $pretest;

        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($posttest - $pretest) / $denominator, 3);
    }

    public function tentukanStatusKelulusan(Event $event, PenilaianAkhir $p, int $anggotaId): string
    {
        $kehadiran = (float) $p->nilai_kehadiran;
        $batasLulus = (float) config('arqam.kehadiran.lulus', 100);
        $batasBersyarat = (float) config('arqam.kehadiran.lulus_bersyarat', 75);

        if ($kehadiran < $batasBersyarat) {
            return 'Tidak Lulus';
        }

        $hasPosttest = SesiTes::where('event_id', $event->id)->where('tipe', 'posttest')->exists();
        $posttestSelesai = true;
        if ($hasPosttest) {
            $posttestSelesai = PesertaTesMulai::where('event_id', $event->id)
                ->where('anggota_id', $anggotaId)
                ->where('tipe', 'posttest')
                ->whereNotNull('waktu_selesai')
                ->exists();

            if (! $posttestSelesai && (float) $p->nilai_posttest > 0) {
                $posttestSelesai = true;
            }
        }

        $hasRtl = RtlSoal::where('event_id', $event->id)->exists();
        $rtlSelesai = true;
        if ($hasRtl) {
            $rtlSelesai = Rtl::where('event_id', $event->id)
                ->where('anggota_id', $anggotaId)
                ->whereIn('status', ['terkirim', 'disetujui'])
                ->exists();
        }

        if ($kehadiran >= $batasLulus && $posttestSelesai && $rtlSelesai) {
            return 'Lulus';
        }

        return 'Lulus Bersyarat';
    }
}
