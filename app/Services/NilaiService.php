<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\Event;
use App\Models\EventSesi;
use App\Models\Keaktifan;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use Illuminate\Contracts\Database\ConcurrencyErrorDetector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDOException;

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

    public function hitungUlangPeserta(Event $event, int $anggotaId, bool $jadwalkanRanking = true): void
    {
        $hitung = function () use ($event, $anggotaId, $jadwalkanRanking) {
            if (! $event->pesertaAktif()->where('anggota_id', $anggotaId)->lockForUpdate()->first()) {
                return;
            }

            $kunci = ['event_id' => $event->id, 'anggota_id' => $anggotaId];
            PenilaianAkhir::where($kunci)->lockForUpdate()->first();
            $this->absensi->recalculateAttendance($event->id, $anggotaId);
            $p = PenilaianAkhir::firstOrNew($kunci);
            $this->isiKomponen($event, $p);
            $this->isiNilaiAkhir($event, $p);
            $p->save();

            if ($jadwalkanRanking) {
                DB::afterCommit(fn () => $this->perbaruiRanking($event));
            }
        };

        if (DB::transactionLevel() > 0) {
            $hitung();
        } else {
            DB::transaction($hitung, 3);
        }
    }

    public function hitungUlang(Event $event): void
    {
        foreach ($event->pesertaAktif()->whereHas('anggota')->orderBy('anggota_id')->pluck('anggota_id') as $anggotaId) {
            $this->hitungUlangPeserta($event, $anggotaId, false);
        }

        $this->setelahSimpanNilai($event);
    }

    public function hitungNilaiAkhirDanRanking(Event $event, array $anggotaIds): void
    {
        $aktifIds = $event->pesertaAktif()->whereHas('anggota')->whereIn('anggota_id', $anggotaIds)->pluck('anggota_id');

        foreach ($aktifIds as $anggotaId) {
            $p = PenilaianAkhir::firstOrNew(['event_id' => $event->id, 'anggota_id' => $anggotaId]);
            $this->isiNilaiAkhir($event, $p);
            $p->save();
        }

        $this->setelahSimpanNilai($event);
    }

    public function rankingAktif(Event $event): Collection
    {
        return DB::transaction(function () use ($event) {
            $totalSesi = EventSesi::where('event_id', $event->id)->count();
            $nilai = PenilaianAkhir::where('event_id', $event->id)->get()->keyBy('anggota_id');
            $daftar = $this->pesertaUntukRanking($event)->with('anggota')->get()->map(function ($peserta) use ($event, $nilai, $totalSesi) {
                $p = $nilai->get($peserta->anggota_id) ?? new PenilaianAkhir([
                    'event_id' => $event->id,
                    'anggota_id' => $peserta->anggota_id,
                ]);
                $p->setRelation('anggota', $peserta->anggota);
                $p->nilai_pretest ??= 0;
                $p->nilai_posttest ??= 0;
                $p->nilai_kehadiran ??= 0;

                if ($totalSesi > 0) {
                    $hadir = Absensi::where('event_id', $event->id)->where('anggota_id', $peserta->anggota_id)->distinct('sesi_id')->count('sesi_id');
                    $p->nilai_kehadiran = round(($hadir / $totalSesi) * 100, 2);
                }

                $this->isiKomponen($event, $p);
                $p->n_gain = $this->isiNilaiAkhir($event, $p);

                return $p;
            });

            return $this->urutkanRanking($daftar);
        }, 3);
    }

    private function setelahSimpanNilai(Event $event): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => $this->perbaruiRanking($event));
        } else {
            $this->perbaruiRanking($event);
        }
    }

    private function perbaruiRanking(Event $event): void
    {
        try {
            DB::transaction(function () use ($event) {
                $nilai = PenilaianAkhir::where('event_id', $event->id)
                    ->orderBy('anggota_id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('anggota_id');
                $daftar = $this->pesertaUntukRanking($event)->pluck('event_peserta.anggota_id')
                    ->map(fn ($id) => $nilai->get($id))
                    ->filter();

                foreach ($this->urutkanRanking($daftar) as $p) {
                    if ($p->isDirty('ranking')) {
                        $p->save();
                    }
                }
            }, 3);
        } catch (PDOException $exception) {
            if (! app(ConcurrencyErrorDetector::class)->causedByConcurrencyError($exception)) {
                throw $exception;
            }

            report($exception);
        }
    }

    private function pesertaUntukRanking(Event $event)
    {
        return $event->pesertaAktif()->getQuery()
            ->join('anggota', 'anggota.id', '=', 'event_peserta.anggota_id')
            ->whereNull('anggota.deleted_at')
            ->select('event_peserta.*', 'anggota.nama_lengkap')
            ->orderBy('anggota.nama_lengkap')
            ->orderBy('event_peserta.anggota_id');
    }

    private function urutkanRanking(Collection $daftar): Collection
    {
        $ranked = $daftar->sortBy([
            fn ($a, $b) => (float) $b->nilai_akhir <=> (float) $a->nilai_akhir,
            fn ($a, $b) => (float) $b->nilai_posttest <=> (float) $a->nilai_posttest,
        ])->values();

        foreach ($ranked as $index => $p) {
            $p->ranking = $index + 1;
        }

        return $ranked;
    }

    private function isiKomponen(Event $event, PenilaianAkhir $p): void
    {
        $p->nilai_afektif = $this->hitungAfektif($event, $p->anggota_id);
        $p->nilai_psikomotor = $this->hitungPsikomotor($event, $p->anggota_id);
        $p->nilai_keaktifan = $this->hitungKeaktifan($event, $p->anggota_id);
    }

    private function isiNilaiAkhir(Event $event, PenilaianAkhir $p): float
    {
        $bobot = $event->getBobotKomposisi();
        $keaktifanCfg = config('arqam.keaktifan');
        $posttest = (float) $p->nilai_posttest;

        $nGain = $posttest > 0 || app(TesService::class)->hasSubmitted($event, $p->anggota_id, 'posttest')
            ? $this->nGain((float) $p->nilai_pretest, $posttest)
            : 0.0;

        $kognitif = ($posttest * $bobot['posttest_dalam_kognitif'])
            + ($nGain * 100 * $bobot['ngain_dalam_kognitif']);

        $psikomotor = ((float) $p->nilai_psikomotor * $keaktifanCfg['bobot_rubrik'])
            + ((float) $p->nilai_keaktifan * $keaktifanCfg['bobot_keaktifan']);

        $nilaiAkhir = ($kognitif * $bobot['kognitif'])
            + ($psikomotor * $bobot['psikomotor'])
            + ((float) $p->nilai_afektif * $bobot['afektif']);

        $p->nilai_akhir = round($nilaiAkhir, 2);
        $p->status_kelulusan = $this->tentukanStatusKelulusan($event, $p, $p->anggota_id);
        $p->verification_hash = hash('sha256', $event->id.'|'.$p->anggota_id.'|'.config('app.key'));
        $p->grade = $this->tentukanGrade($event, $p, 0, 0);

        return $nGain;
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

    public function tentukanGrade(Event $event, PenilaianAkhir $p, int $rankIndex, int $total): ?string
    {
        if (! $event->gunakan_grade) {
            return null;
        }

        if ($p->status_kelulusan !== 'Lulus') {
            return null;
        }

        $na = (float) $p->nilai_akhir;

        if ($na >= 85.0) {
            return 'A';
        }
        if ($na >= 70.0) {
            return 'B';
        }

        return 'C';
    }
}
