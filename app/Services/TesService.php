<?php

namespace App\Services;

use App\Models\Event;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PilihanJawaban;
use App\Models\Soal;

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
}
