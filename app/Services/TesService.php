<?php

namespace App\Services;

use App\Models\Event;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TesService
{
    public function grade(Event $event, int $anggotaId, string $tipe): float
    {
        $totalSoal = Soal::where('event_id', $event->id)->where('tipe', $tipe)->count();

        if ($totalSoal === 0) {
            return 0.0;
        }

        $jawaban = JawabanPeserta::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->whereHas('soal', fn ($q) => $q->where('tipe', $tipe))
            ->with('pilihan')
            ->get();

        $benar = $jawaban->filter(fn ($j) => $j->pilihan && $j->pilihan->is_benar)->count();

        $score = round(($benar / $totalSoal) * 100, 2);
        $kolom = $tipe === 'pretest' ? 'nilai_pretest' : 'nilai_posttest';

        PenilaianAkhir::updateOrCreate(
            ['event_id' => $event->id, 'anggota_id' => $anggotaId],
            [$kolom => $score]
        );

        return $score;
    }

    public function submitAnswers(Event $event, int $anggotaId, string $tipe, array $answers): float
    {
        if ($this->hasSubmitted($event, $anggotaId, $tipe)) {
            return $this->grade($event, $anggotaId, $tipe);
        }

        $mulai = PesertaTesMulai::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'tipe' => $tipe,
        ], ['waktu_mulai' => now()]);

        $soalIds = Soal::where('event_id', $event->id)
            ->where('tipe', $tipe)
            ->pluck('id')
            ->all();

        $validPilihan = PilihanJawaban::whereIn('soal_id', $soalIds)->pluck('id')->flip();

        foreach ($answers as $answer) {
            $soalId = (int) ($answer['soal_id'] ?? 0);
            $pilihanId = $answer['pilihan_id'] ?? null;

            if (! in_array($soalId, $soalIds, true)) {
                continue;
            }

            if ($pilihanId !== null && ! $validPilihan->has((int) $pilihanId)) {
                $pilihanId = null;
            }

            JawabanPeserta::updateOrCreate(
                [
                    'event_id' => $event->id,
                    'anggota_id' => $anggotaId,
                    'soal_id' => $soalId,
                ],
                ['pilihan_id' => $pilihanId]
            );
        }

        $mulai->update(['waktu_selesai' => now()]);

        return $this->grade($event, $anggotaId, $tipe);
    }

    public function hasSubmitted(Event $event, int $anggotaId, string $tipe): bool
    {
        return PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->where('tipe', $tipe)
            ->whereNotNull('waktu_selesai')
            ->exists();
    }

    /**
     * @return Collection<int, Soal>
     */
    public function soalBerurutan(Event $event, string $tipe): Collection
    {
        return Soal::where('event_id', $event->id)
            ->where('tipe', $tipe)
            ->with('pilihan')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
    }

    public function kunciJawaban(Collection $soal): string
    {
        return $soal->map(fn (Soal $s) => strtoupper((string) ($s->pilihan->firstWhere('is_benar', true)?->label ?? '?')))->implode('');
    }

    public function normalisasiJawaban(?string $jawaban): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $jawaban));
    }

    /**
     * Petakan string jawaban kertas (satu huruf per soal, "-" = kosong) ke pilihan_id per soal.
     *
     * @return array<int, ?int> soal_id => pilihan_id
     *
     * @throws InvalidArgumentException
     */
    public function petakanJawabanManual(Collection $soal, string $jawaban): array
    {
        $huruf = str_split($this->normalisasiJawaban($jawaban));

        if (count($huruf) !== $soal->count()) {
            throw new InvalidArgumentException('Jumlah jawaban '.count($huruf).', seharusnya '.$soal->count().' (satu huruf per soal).');
        }

        $peta = [];
        foreach ($soal->values() as $i => $s) {
            if ($huruf[$i] === '-') {
                $peta[$s->id] = null;

                continue;
            }

            if ($huruf[$i] === '?') {
                throw new InvalidArgumentException('Soal nomor '.($i + 1).' terbaca ganda (?). Cek kertas lalu ganti dengan huruf yang benar atau "-".');
            }

            $pilihan = $s->pilihan->first(fn ($p) => strtoupper($p->label) === $huruf[$i]);
            if (! $pilihan) {
                throw new InvalidArgumentException('Soal nomor '.($i + 1).' tidak punya pilihan "'.$huruf[$i].'".');
            }
            $peta[$s->id] = $pilihan->id;
        }

        return $peta;
    }

    public function sudahOnline(Event $event, int $anggotaId, string $tipe): bool
    {
        return PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->where('tipe', $tipe)
            ->where('sumber', 'online')
            ->whereNotNull('waktu_selesai')
            ->exists();
    }

    /**
     * Simpan jawaban lembar kertas yang diinput admin, lalu nilai dengan mesin yang sama seperti tes online.
     *
     * @throws InvalidArgumentException
     */
    public function simpanJawabanManual(Event $event, int $anggotaId, string $tipe, string $jawaban, int $adminId): float
    {
        if ($this->sudahOnline($event, $anggotaId, $tipe)) {
            throw new InvalidArgumentException('Peserta sudah mengerjakan tes ini secara online; nilai online tidak ditimpa.');
        }

        $peta = $this->petakanJawabanManual($this->soalBerurutan($event, $tipe), $jawaban);

        return DB::transaction(function () use ($event, $anggotaId, $tipe, $peta, $adminId) {
            foreach ($peta as $soalId => $pilihanId) {
                JawabanPeserta::updateOrCreate(
                    ['event_id' => $event->id, 'anggota_id' => $anggotaId, 'soal_id' => $soalId],
                    ['pilihan_id' => $pilihanId]
                );
            }

            $mulai = PesertaTesMulai::firstOrNew([
                'event_id' => $event->id,
                'anggota_id' => $anggotaId,
                'tipe' => $tipe,
            ]);
            $mulai->fill([
                'sumber' => 'manual',
                'waktu_mulai' => $mulai->waktu_mulai ?? now(),
                'waktu_selesai' => now(),
                'diinput_oleh' => $adminId,
            ])->save();

            return $this->grade($event, $anggotaId, $tipe);
        });
    }

    public function hapusJawabanManual(Event $event, int $anggotaId, string $tipe, int $adminId): bool
    {
        $mulai = PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->where('tipe', $tipe)
            ->where('sumber', 'manual')
            ->first();

        if (! $mulai) {
            return false;
        }

        $kolom = $tipe === 'pretest' ? 'nilai_pretest' : 'nilai_posttest';

        DB::transaction(function () use ($event, $anggotaId, $tipe, $mulai, $kolom) {
            JawabanPeserta::where('event_id', $event->id)
                ->where('anggota_id', $anggotaId)
                ->whereIn('soal_id', Soal::where('event_id', $event->id)->where('tipe', $tipe)->pluck('id'))
                ->delete();
            $mulai->delete();
            PenilaianAkhir::where('event_id', $event->id)->where('anggota_id', $anggotaId)->update([$kolom => 0]);
        });

        Log::info('Jawaban tes kertas dihapus', [
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'tipe' => $tipe,
            'diinput_oleh' => $mulai->diinput_oleh,
            'dihapus_oleh' => $adminId,
        ]);

        return true;
    }
}
