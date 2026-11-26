<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Tata letak lembar jawaban yang bisa dipindai (koordinat pt, origin kiri-atas A4).
 * Dipakai bersama oleh PDF cetak dan pembaca foto di browser agar selalu sinkron.
 */
class LembarJawabanLayout
{
    public const PAGE_W = 595.28;

    public const PAGE_H = 841.89;

    public const MARKER = 20.0;

    public const BITS = 32;

    public const MAX_BARIS = 30;

    /**
     * @param  array<int, string>  $labels
     */
    public static function build(int $jumlahSoal, array $labels): array
    {
        if ($jumlahSoal < 1) {
            throw new InvalidArgumentException('Bank soal masih kosong.');
        }

        $baris = (int) ceil($jumlahSoal / 3);
        if ($baris > self::MAX_BARIS) {
            throw new InvalidArgumentException('Soal terlalu banyak untuk satu lembar pindai (maksimal '.(self::MAX_BARIS * 3).' soal).');
        }

        $m = self::MARKER;
        $markers = [
            ['x' => 26 + $m / 2, 'y' => 26 + $m / 2],
            ['x' => self::PAGE_W - 26 - $m / 2, 'y' => 26 + $m / 2],
            ['x' => self::PAGE_W - 26 - $m / 2, 'y' => self::PAGE_H - 26 - $m / 2],
            ['x' => 26 + $m / 2, 'y' => self::PAGE_H - 26 - $m / 2],
        ];

        $bitSize = 7.0;
        $bitGap = 10.0;
        $bitStart = (self::PAGE_W - (self::BITS - 1) * $bitGap) / 2;
        $bits = [];
        for ($i = 0; $i < self::BITS; $i++) {
            $bits[] = ['x' => round($bitStart + $i * $bitGap, 2), 'y' => 36.0];
        }

        $gridTop = 296.0;
        $gridBottom = 772.0;
        $rowH = min(22.0, ($gridBottom - $gridTop) / $baris);
        $r = round(min(7.0, $rowH * 0.36), 2);
        $optGap = max(22.0, $r * 2 + 8);
        $colW = (self::PAGE_W - 2 * 52) / 3;

        $soal = [];
        for ($n = 0; $n < $jumlahSoal; $n++) {
            $col = intdiv($n, $baris);
            $row = $n % $baris;
            $y = round($gridTop + $row * $rowH + $rowH / 2, 2);
            $x0 = 52 + $col * $colW;
            $opsi = [];
            foreach (array_values($labels) as $k => $label) {
                $opsi[] = ['label' => $label, 'x' => round($x0 + 34 + $r + $k * $optGap, 2), 'y' => $y];
            }
            $soal[] = ['no' => $n + 1, 'x' => round($x0 + 26, 2), 'y' => $y, 'opsi' => $opsi];
        }

        return [
            'page' => ['w' => self::PAGE_W, 'h' => self::PAGE_H],
            'marker' => $m,
            'markers' => $markers,
            'bitSize' => $bitSize,
            'bits' => $bits,
            'r' => $r,
            'rowH' => $rowH,
            'soal' => $soal,
        ];
    }

    /**
     * 20 bit anggota_id + 12 bit checksum (event, tipe). anggota_id 0 = lembar kosong.
     *
     * @return array<int, int>
     */
    public static function kode(int $eventId, string $tipe, int $anggotaId): array
    {
        if ($anggotaId < 0 || $anggotaId > 0xFFFFF) {
            throw new InvalidArgumentException('ID peserta di luar jangkauan kode lembar.');
        }

        $nilai = ($anggotaId << 12) | self::checksum($eventId, $tipe, $anggotaId);
        $bits = [];
        for ($i = self::BITS - 1; $i >= 0; $i--) {
            $bits[] = ($nilai >> $i) & 1;
        }

        return $bits;
    }

    /**
     * FNV-1a 32 bit atas "event|tipe|anggota", dilipat ke 12 bit. Harus identik dengan omr-lembar.js.
     */
    public static function checksum(int $eventId, string $tipe, int $anggotaId): int
    {
        $h = 0x811C9DC5;
        foreach (str_split($eventId.'|'.$tipe.'|'.$anggotaId) as $c) {
            $h = (($h ^ ord($c)) * 0x01000193) & 0xFFFFFFFF;
        }

        return ($h ^ ($h >> 16)) & 0xFFF;
    }
}
