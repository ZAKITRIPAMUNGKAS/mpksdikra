<?php

namespace App\Services;

use App\Models\Event;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\SesiTes;
use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TesService
{
    private const GRACE_SECONDS = 45;

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

    public function startAttempt(Event $event, int $anggotaId, string $tipe): PesertaTesMulai
    {
        return PesertaTesMulai::firstOrCreate([
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'tipe' => $tipe,
        ], ['waktu_mulai' => now()]);
    }

    public function remainingSeconds(PesertaTesMulai $mulai, SesiTes $sesi): int
    {
        return $mulai->waktu_mulai->copy()->addMinutes($sesi->durasi_menit)->getTimestamp() - now()->getTimestamp();
    }

    public function submitAnswers(Event $event, PesertaTesMulai $mulai, array $answers, bool $automatic = false, bool $recoverExpired = false): ?float
    {
        return DB::transaction(function () use ($event, $mulai, $answers, $automatic, $recoverExpired) {
            $mulai = PesertaTesMulai::whereKey($mulai->id)->lockForUpdate()->firstOrFail();
            if ($mulai->waktu_selesai !== null) {
                return null;
            }

            $remaining = $this->remainingSeconds($mulai, $this->activeSession($mulai));
            if ($recoverExpired) {
                abort_if($remaining > 0, 409, 'Waktu tes masih tersedia.');
            } else {
                abort_if($automatic ? $remaining < -self::GRACE_SECONDS : $remaining <= 0, 403, 'Waktu tes telah habis.');
            }

            if ($answers !== []) {
                $this->persistAnswers($mulai, $answers);
            }
            $mulai->update(['waktu_selesai' => now()]);

            return $this->grade($event, $mulai->anggota_id, $mulai->tipe);
        });
    }

    public function saveDraft(PesertaTesMulai $mulai, array $answer): void
    {
        DB::transaction(function () use ($mulai, $answer) {
            $mulai = PesertaTesMulai::whereKey($mulai->id)->lockForUpdate()->firstOrFail();
            abort_if($mulai->waktu_selesai !== null, 403, 'Tes ini sudah diserahkan sebelumnya.');
            abort_if($this->remainingSeconds($mulai, $this->activeSession($mulai)) < -self::GRACE_SECONDS, 403, 'Waktu tes telah habis.');

            $valid = Soal::whereKey($answer['soal_id'])
                ->where('event_id', $mulai->event_id)
                ->where('tipe', $mulai->tipe)
                ->whereHas('pilihan', fn ($query) => $query->whereKey($answer['pilihan_id']))
                ->exists();

            if (! $valid) {
                throw ValidationException::withMessages(['pilihan_id' => 'Pilihan jawaban tidak sesuai dengan soal.']);
            }

            $this->storeAnswer($mulai, $answer['soal_id'], $answer['pilihan_id']);
        });
    }

    private function activeSession(PesertaTesMulai $mulai): SesiTes
    {
        $sesi = SesiTes::where('event_id', $mulai->event_id)->where('tipe', $mulai->tipe)->sharedLock()->first();
        abort_unless($sesi && $sesi->status === 'aktif', 403, 'Sesi tes tidak aktif.');

        return $sesi;
    }

    private function persistAnswers(PesertaTesMulai $mulai, array $answers): void
    {
        $soalIds = Soal::where('event_id', $mulai->event_id)->where('tipe', $mulai->tipe)->pluck('id')->flip();
        $pilihanSoal = PilihanJawaban::whereIn('soal_id', $soalIds->keys())->pluck('soal_id', 'id');

        foreach ($answers as $answer) {
            $soalId = (int) $answer['soal_id'];
            if (! $soalIds->has($soalId) || ! array_key_exists('pilihan_id', $answer)) {
                continue;
            }

            $pilihanId = $answer['pilihan_id'];
            if ($pilihanId !== null && (int) $pilihanSoal->get($pilihanId) !== $soalId) {
                continue;
            }

            $this->storeAnswer($mulai, $soalId, $pilihanId);
        }
    }

    private function storeAnswer(PesertaTesMulai $mulai, int $soalId, ?int $pilihanId): void
    {
        JawabanPeserta::updateOrCreate(
            [
                'event_id' => $mulai->event_id,
                'anggota_id' => $mulai->anggota_id,
                'soal_id' => $soalId,
            ],
            ['pilihan_id' => $pilihanId]
        );
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
            $mulai = $this->startAttempt($event, $anggotaId, $tipe);
            $mulai = PesertaTesMulai::whereKey($mulai->id)->lockForUpdate()->firstOrFail();
            if ($mulai->sumber === 'online' && $mulai->waktu_selesai !== null) {
                throw new InvalidArgumentException('Peserta sudah mengerjakan tes ini secara online; nilai online tidak ditimpa.');
            }

            foreach ($peta as $soalId => $pilihanId) {
                $this->storeAnswer($mulai, $soalId, $pilihanId);
            }

            $mulai->fill([
                'sumber' => 'manual',
                'waktu_selesai' => now(),
                'diinput_oleh' => $adminId,
            ])->save();

            return $this->grade($event, $anggotaId, $tipe);
        });
    }

    public function hapusJawabanManual(Event $event, int $anggotaId, string $tipe, int $adminId): bool
    {
        return $this->ulangTes($event, $anggotaId, $tipe, $adminId, hanyaManual: true);
    }

    /**
     * Ulang tes: hapus percobaan (online maupun kertas) beserta jawabannya dan nolkan nilai,
     * sehingga peserta dapat mengerjakan lagi selama sesi tes dibuka.
     */
    public function ulangTes(Event $event, int $anggotaId, string $tipe, int $adminId, bool $hanyaManual = false): bool
    {
        $mulai = PesertaTesMulai::where('event_id', $event->id)
            ->where('anggota_id', $anggotaId)
            ->where('tipe', $tipe)
            ->when($hanyaManual, fn ($q) => $q->where('sumber', 'manual'))
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

        Log::info('Tes peserta diulang', [
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'tipe' => $tipe,
            'sumber' => $mulai->sumber,
            'waktu_selesai' => $mulai->waktu_selesai?->toDateTimeString(),
            'diinput_oleh' => $mulai->diinput_oleh,
            'dihapus_oleh' => $adminId,
        ]);

        return true;
    }
}
