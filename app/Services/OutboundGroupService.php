<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\OutboundKelompok;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OutboundGroupService
{
    /**
     * Acak kelompok outbound berdasarkan kriteria yang dipilih admin.
     *
     * @param  string  $kriteria  'pisah_gender' | 'seimbang_gender' | 'seimbang_cabang' | 'acak_murni'
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

            if ($kriteria === 'pisah_gender') {
                return $this->pisahGender($event, $peserta, $jumlahKelompok);
            }

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

    /**
     * Kelompok putra dan putri dipisah. Jumlah kelompok dibagi sebanding jumlah peserta
     * tiap gender (minimal satu kelompok per gender yang ada), lalu diisi merata.
     */
    private function pisahGender(Event $event, Collection $peserta, int $jumlahKelompok): array
    {
        $putra = $peserta->filter(fn ($p) => ($p->anggota->jenis_kelamin ?? 'L') !== 'P')->shuffle()->values();
        $putri = $peserta->filter(fn ($p) => ($p->anggota->jenis_kelamin ?? 'L') === 'P')->shuffle()->values();

        [$nPutra, $nPutri] = self::bagiJumlahKelompok($putra->count(), $putri->count(), $jumlahKelompok);

        $hasil = [];
        foreach ([['Putra', $putra, $nPutra], ['Putri', $putri, $nPutri]] as [$label, $anggota, $n]) {
            $kelompok = [];
            for ($i = 1; $i <= $n; $i++) {
                $kelompok[] = OutboundKelompok::create([
                    'event_id' => $event->id,
                    'nama_kelompok' => 'Kelompok '.$label.' '.$i,
                ]);
            }
            foreach ($anggota as $idx => $p) {
                $p->update(['outbound_kelompok_id' => $kelompok[$idx % $n]->id]);
            }
            $hasil = array_merge($hasil, $kelompok);
        }

        return $hasil;
    }

    /**
     * @return array{0:int, 1:int} jumlah kelompok putra & putri
     */
    public static function bagiJumlahKelompok(int $putra, int $putri, int $jumlahKelompok): array
    {
        if ($putra === 0 || $putri === 0) {
            return $putra === 0 ? [0, min($jumlahKelompok, $putri)] : [min($jumlahKelompok, $putra), 0];
        }

        $jumlahKelompok = max(2, $jumlahKelompok);
        $nPutra = (int) round($jumlahKelompok * $putra / ($putra + $putri));
        $nPutra = max(1, min($jumlahKelompok - 1, $nPutra, $putra));
        $nPutri = max(1, min($jumlahKelompok - $nPutra, $putri));

        return [$nPutra, $nPutri];
    }

    public function reset(Event $event): void
    {
        DB::transaction(function () use ($event) {
            EventPeserta::where('event_id', $event->id)->update(['outbound_kelompok_id' => null]);
            OutboundKelompok::where('event_id', $event->id)->delete();
        });
    }
}
