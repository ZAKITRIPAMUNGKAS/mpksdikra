<?php

namespace App\Services;

use App\Models\AfektifButir;
use App\Models\AfektifJawaban;
use App\Models\AngketItem;
use App\Models\AngketJawaban;
use App\Models\Event;
use App\Models\InputKertas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Input lembar kertas untuk instrumen Likert: kuesioner sikap (afektif, 1–4) dan angket kepuasan (1–5).
 */
class AngketKertasService
{
    public const JENIS = ['afektif', 'angket'];

    public function __construct(private readonly NilaiService $nilai) {}

    public function skala(string $jenis): array
    {
        return $jenis === 'afektif'
            ? ['1' => 'Sangat Tidak Setuju', '2' => 'Tidak Setuju', '3' => 'Setuju', '4' => 'Sangat Setuju']
            : ['1' => 'Sangat Kurang', '2' => 'Kurang', '3' => 'Cukup', '4' => 'Baik', '5' => 'Sangat Baik'];
    }

    public function singkatan(string $jenis): ?array
    {
        return $jenis === 'afektif' ? ['STS', 'TS', 'S', 'SS'] : null;
    }

    /**
     * Butir berurutan sesuai tampilan portal.
     *
     * @return Collection<int, array{id:int, teks:string, grup:string, positif:?bool}>
     */
    public function butir(Event $event, string $jenis): Collection
    {
        if ($jenis === 'afektif') {
            return AfektifButir::join('afektif_sub_aspek', 'afektif_sub_aspek.id', '=', 'afektif_butir.sub_aspek_id')
                ->where('afektif_sub_aspek.event_id', $event->id)
                ->where('afektif_sub_aspek.aktif', true)
                ->orderBy('afektif_sub_aspek.urutan')
                ->orderBy('afektif_sub_aspek.id')
                ->orderBy('afektif_butir.urutan')
                ->orderBy('afektif_butir.id')
                ->get(['afektif_butir.id', 'afektif_butir.teks_butir', 'afektif_butir.is_positif', 'afektif_sub_aspek.nama_sub_aspek'])
                ->map(fn ($b) => ['id' => (int) $b->id, 'teks' => (string) $b->teks_butir, 'grup' => (string) $b->nama_sub_aspek, 'positif' => (bool) $b->is_positif])
                ->values();
        }

        return AngketItem::where('event_id', $event->id)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get()
            ->map(fn ($i) => ['id' => (int) $i->id, 'teks' => (string) $i->teks_item, 'grup' => 'Aspek '.$i->kategori, 'positif' => null])
            ->values();
    }

    /**
     * @return array<int, ?int> butir_id => skor (null = kosong)
     *
     * @throws InvalidArgumentException
     */
    public function petakan(Collection $butir, string $jenis, string $jawaban): array
    {
        $huruf = str_split(strtoupper(preg_replace('/\s+/', '', $jawaban)));
        $maks = count($this->skala($jenis));

        if (count($huruf) !== $butir->count()) {
            throw new InvalidArgumentException('Jumlah jawaban '.count($huruf).', seharusnya '.$butir->count().' (satu angka per butir).');
        }

        $peta = [];
        foreach ($butir->values() as $i => $b) {
            $c = $huruf[$i];
            if ($c === '-') {
                $peta[$b['id']] = null;

                continue;
            }
            if ($c === '?') {
                throw new InvalidArgumentException('Butir nomor '.($i + 1).' terbaca ganda (?). Cek kertas lalu ganti dengan angka yang benar atau "-".');
            }
            if (! ctype_digit($c) || (int) $c < 1 || (int) $c > $maks) {
                throw new InvalidArgumentException('Butir nomor '.($i + 1).' berisi "'.$c.'"; isi angka 1–'.$maks.' atau "-".');
            }
            $peta[$b['id']] = (int) $c;
        }

        return $peta;
    }

    /**
     * Peserta yang sudah mengisi lewat portal (punya jawaban tetapi bukan dari input kertas).
     *
     * @return array<int, true>
     */
    public function anggotaOnline(Event $event, string $jenis): array
    {
        $manual = $this->anggotaManual($event, $jenis);
        $model = $jenis === 'afektif' ? AfektifJawaban::class : AngketJawaban::class;

        return $model::where('event_id', $event->id)
            ->distinct()
            ->pluck('anggota_id')
            ->reject(fn ($id) => isset($manual[(int) $id]))
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * @return array<int, InputKertas>
     */
    public function anggotaManual(Event $event, string $jenis): array
    {
        return InputKertas::where('event_id', $event->id)
            ->where('instrumen', $jenis)
            ->get()
            ->keyBy(fn ($r) => (int) $r->anggota_id)
            ->all();
    }

    public function sudahOnline(Event $event, int $anggotaId, string $jenis): bool
    {
        if (InputKertas::ada($event->id, $anggotaId, $jenis)) {
            return false;
        }
        $model = $jenis === 'afektif' ? AfektifJawaban::class : AngketJawaban::class;

        return $model::where('event_id', $event->id)->where('anggota_id', $anggotaId)->exists();
    }

    /**
     * String jawaban tersimpan untuk peserta yang diinput dari kertas.
     *
     * @return array<int, string>
     */
    public function tersimpan(Event $event, string $jenis, Collection $butir): array
    {
        $manual = array_keys($this->anggotaManual($event, $jenis));
        if (! $manual || $butir->isEmpty()) {
            return [];
        }

        [$model, $kolom] = $jenis === 'afektif' ? [AfektifJawaban::class, 'butir_id'] : [AngketJawaban::class, 'item_id'];
        $urutan = $butir->pluck('id')->flip();
        $hasil = [];
        foreach ($manual as $id) {
            $hasil[$id] = array_fill(0, $butir->count(), '-');
        }

        $model::where('event_id', $event->id)
            ->whereIn('anggota_id', $manual)
            ->whereIn($kolom, $butir->pluck('id'))
            ->get()
            ->each(function ($j) use (&$hasil, $urutan, $kolom) {
                $hasil[(int) $j->anggota_id][$urutan[$j->{$kolom}]] = (string) $j->skor;
            });

        return array_map(fn ($a) => implode('', $a), $hasil);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function simpan(Event $event, int $anggotaId, string $jenis, string $jawaban, int $adminId): void
    {
        if ($this->sudahOnline($event, $anggotaId, $jenis)) {
            throw new InvalidArgumentException('Peserta sudah mengisi lewat portal; jawaban online tidak ditimpa.');
        }

        $butir = $this->butir($event, $jenis);
        $peta = $this->petakan($butir, $jenis, $jawaban);
        [$model, $kolom] = $jenis === 'afektif' ? [AfektifJawaban::class, 'butir_id'] : [AngketJawaban::class, 'item_id'];

        DB::transaction(function () use ($event, $anggotaId, $jenis, $peta, $model, $kolom, $adminId) {
            foreach ($peta as $butirId => $skor) {
                $kunci = ['event_id' => $event->id, 'anggota_id' => $anggotaId, $kolom => $butirId];
                if ($skor === null) {
                    $model::where($kunci)->delete();
                } else {
                    $model::updateOrCreate($kunci, ['skor' => $skor]);
                }
            }

            InputKertas::updateOrCreate(
                ['event_id' => $event->id, 'anggota_id' => $anggotaId, 'instrumen' => $jenis],
                ['diinput_oleh' => $adminId]
            )->touch();
        });
    }

    public function hapus(Event $event, int $anggotaId, string $jenis, int $adminId): bool
    {
        $tanda = InputKertas::where('event_id', $event->id)->where('anggota_id', $anggotaId)->where('instrumen', $jenis)->first();
        if (! $tanda) {
            return false;
        }

        [$model, $kolom] = $jenis === 'afektif' ? [AfektifJawaban::class, 'butir_id'] : [AngketJawaban::class, 'item_id'];
        $ids = $this->butir($event, $jenis)->pluck('id');

        DB::transaction(function () use ($event, $anggotaId, $model, $kolom, $ids, $tanda) {
            $model::where('event_id', $event->id)->where('anggota_id', $anggotaId)->whereIn($kolom, $ids)->delete();
            $tanda->delete();
        });

        Log::info('Input kertas dihapus', [
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'instrumen' => $jenis,
            'diinput_oleh' => $tanda->diinput_oleh,
            'dihapus_oleh' => $adminId,
        ]);

        return true;
    }

    /**
     * Reset isian online peserta (semua jawaban kegiatan ini) agar peserta bisa mengisi ulang dari portal.
     */
    public function ulangOnline(Event $event, int $anggotaId, string $jenis, int $adminId): bool
    {
        if (! $this->sudahOnline($event, $anggotaId, $jenis)) {
            return false;
        }

        $model = $jenis === 'afektif' ? AfektifJawaban::class : AngketJawaban::class;
        $jumlah = $model::where('event_id', $event->id)->where('anggota_id', $anggotaId)->delete();

        Log::info('Isian online diulang', [
            'event_id' => $event->id,
            'anggota_id' => $anggotaId,
            'instrumen' => $jenis,
            'jawaban_dihapus' => $jumlah,
            'diulang_oleh' => $adminId,
        ]);

        return true;
    }

    public function hitungUlangNilai(Event $event, string $jenis): void
    {
        if ($jenis === 'afektif') {
            $this->nilai->hitungUlang($event);
        }
    }
}
