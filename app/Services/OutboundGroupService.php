<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\OutboundKelompok;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OutboundGroupService
{
    /**
     * Acak kelompok outbound berdasarkan kriteria yang dipilih admin.
     *
     * @param  string  $kriteria  'seimbang_gender' | 'seimbang_cabang' | 'acak_murni'
     */
    public function generate(Event $event, int $jumlahKelompok, string $kriteria = 'seimbang_gender'): array
    {
        $peserta = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->get();

        if ($peserta->isEmpty()) {
            throw new InvalidArgumentException('Belum ada peserta aktif yang terdaftar pada event ini.');
        }

        if ($jumlahKelompok < 1) {
            throw new InvalidArgumentException('Jumlah kelompok minimal 1.');
        }

        $jumlahKelompok = min($jumlahKelompok, $peserta->count());

        return DB::transaction(function () use ($event, $peserta, $jumlahKelompok, $kriteria) {
            // Reset penugasan sebelumnya
            EventPeserta::where('event_id', $event->id)->update(['outbound_kelompok_id' => null]);
            OutboundKelompok::where('event_id', $event->id)->delete();

            $kelompokList = [];
            for ($i = 1; $i <= $jumlahKelompok; $i++) {
                $kelompokList[] = OutboundKelompok::create([
                    'event_id' => $event->id,
                    'nama_kelompok' => 'Kelompok '.$i,
                ]);
            }

            if ($kriteria === 'seimbang_gender') {
                $males = $peserta->filter(fn ($p) => ($p->anggota->jenis_kelamin ?? 'L') === 'L')->shuffle()->values();
                $females = $peserta->filter(fn ($p) => ($p->anggota->jenis_kelamin ?? 'P') === 'P')->shuffle()->values();

                $gIdx = 0;
                foreach ($males as $m) {
                    $m->update(['outbound_kelompok_id' => $kelompokList[$gIdx]->id]);
                    $gIdx = ($gIdx + 1) % $jumlahKelompok;
                }
                foreach ($females as $f) {
                    $f->update(['outbound_kelompok_id' => $kelompokList[$gIdx]->id]);
                    $gIdx = ($gIdx + 1) % $jumlahKelompok;
                }
            } elseif ($kriteria === 'seimbang_cabang') {
                $grouped = $peserta->groupBy(fn ($p) => $p->anggota->kecamatan ?? 'Lainnya');
                $gIdx = 0;
                foreach ($grouped as $branchGroup) {
                    foreach ($branchGroup->shuffle() as $bp) {
                        $bp->update(['outbound_kelompok_id' => $kelompokList[$gIdx]->id]);
                        $gIdx = ($gIdx + 1) % $jumlahKelompok;
                    }
                }
            } else {
                $shuffled = $peserta->shuffle()->values();
                foreach ($shuffled as $index => $p) {
                    $p->update(['outbound_kelompok_id' => $kelompokList[$index % $jumlahKelompok]->id]);
                }
            }

            return $kelompokList;
        });
    }

    public function reset(Event $event): void
    {
        DB::transaction(function () use ($event) {
            EventPeserta::where('event_id', $event->id)->update(['outbound_kelompok_id' => null]);
            OutboundKelompok::where('event_id', $event->id)->delete();
        });
    }
}
