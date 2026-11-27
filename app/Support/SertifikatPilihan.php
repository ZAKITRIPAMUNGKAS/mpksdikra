<?php

namespace App\Support;

use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Daftar peserta untuk dialog cetak sertifikat admin dan pilihan mode cetak bolak-balik.
 * Admin boleh mencetak kapan saja untuk peserta aktif mana pun; yang Lulus/Lulus Bersyarat tercentang otomatis.
 */
final class SertifikatPilihan
{
    public const STATUS_LULUS = ['Lulus', 'Lulus Bersyarat'];

    public const SISI = ['duplex', 'depan', 'belakang'];

    /**
     * @param  Collection<int, EventPeserta>  $peserta  peserta aktif (relasi anggota dimuat)
     * @param  Collection<int, PenilaianAkhir>  $nilai  di-key anggota_id
     * @return array<int, array<string, mixed>>
     */
    public static function items(Collection $peserta, Collection $nilai): array
    {
        return $peserta
            ->filter(fn ($p) => $p->anggota !== null)
            ->map(function ($p) use ($nilai) {
                $n = $nilai->get($p->anggota_id);
                $status = $n?->status_kelulusan ?: 'Belum Dinilai';

                return [
                    'id' => (int) $p->id,
                    'nama' => $p->anggota->nama_lengkap,
                    'meta' => trim(($p->anggota->pcm ?: $p->anggota->kecamatan ?: '-').($n ? ' • NA '.number_format((float) $n->nilai_akhir, 1) : '')),
                    'badge' => $status,
                    'badgeClass' => match ($status) {
                        'Lulus' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Lulus Bersyarat' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'Tidak Lulus' => 'bg-rose-50 text-rose-700 border-rose-200',
                        default => 'bg-slate-100 text-slate-600 border-slate-200',
                    },
                ];
            })
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    public static function sisi(Request $request): string
    {
        $sisi = (string) $request->query('sisi', 'duplex');

        return in_array($sisi, self::SISI, true) ? $sisi : 'duplex';
    }
}
