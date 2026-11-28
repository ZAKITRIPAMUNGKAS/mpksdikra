<?php

namespace App\Support;

/**
 * Awan kata (word cloud) dari jawaban terbuka peserta, untuk slide "Suara Kader" di Live PPT.
 * Kata umum (stopword) dibuang, setiap kata dihitung sekali per jawaban agar satu jawaban
 * panjang tidak mendominasi.
 */
final class AwanKata
{
    private const STOPWORD = [
        'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'untuk', 'dengan', 'pada', 'dalam', 'saya', 'aku', 'kami',
        'kita', 'kalian', 'anda', 'dia', 'mereka', 'ada', 'adalah', 'akan', 'atau', 'juga', 'karena', 'jadi', 'sangat',
        'sekali', 'lebih', 'bisa', 'dapat', 'sudah', 'telah', 'belum', 'masih', 'tidak', 'tak', 'bukan', 'agar', 'supaya',
        'oleh', 'sebagai', 'para', 'serta', 'tetapi', 'tapi', 'namun', 'lagi', 'pun', 'nya', 'kan', 'lah', 'kah', 'ya',
        'saat', 'setelah', 'sebelum', 'selama', 'ketika', 'bagi', 'tentang', 'hal', 'semua', 'banyak', 'sangatlah',
        'mengikuti', 'ikut', 'kegiatan', 'acara', 'baitul', 'arqam', 'arqom', 'perkaderan', 'pelatihan', 'yg',
        'dgn', 'utk', 'krn', 'tdk', 'sy', 'sdh', 'aja', 'saja', 'begitu', 'seperti', 'merasa', 'rasanya', 'perasaan',
        'terasa', 'semoga', 'bahwa', 'hanya', 'sama', 'secara', 'harus', 'kepada', 'antara', 'maka', 'kalau', 'jika',
        'bila', 'apa', 'siapa', 'mana', 'bagaimana', 'the', 'sih', 'dong', 'deh', 'nih', 'tuh', 'gitu', 'banget',
        'insyaallah', 'insya', 'allah', 'muhammadiyah', 'kader', 'peserta', 'materi', 'hari', 'bapak', 'ibu',
        'awalnya', 'disampaikan', 'dijalani', 'kembali', 'memberikan', 'diri', 'terus', 'menjadi', 'mendapat',
        'mendapatkan', 'membuat', 'selalu', 'tersebut', 'yaitu', 'yakni', 'sesuai', 'setiap', 'tentunya',
    ];

    /** Frasa yang bermakna bila utuh; ditampilkan sebagai satu kata di awan. */
    private const FRASA = ['luar biasa', "amar ma'ruf", 'nahi munkar', 'percaya diri', 'buka mata', 'mata hati', 'tidak sabar'];

    /** Palet ala Mentimeter: kontras tinggi di latar terang. */
    public const WARNA = ['#196cff', '#e2306c', '#13a37f', '#7b3fe4', '#f08a00', '#0aa5c2', '#d63c3c', '#3b4bd8'];

    /**
     * @param  iterable<int, string|null>  $teks
     * @return list<array{kata: string, jumlah: int, ukuran: float, warna: string}> kata terbesar di tengah
     */
    public static function dari(iterable $teks, int $maks = 36): array
    {
        $hitung = [];
        foreach ($teks as $jawaban) {
            $teksKecil = mb_strtolower((string) $jawaban);
            foreach (self::FRASA as $frasa) {
                $teksKecil = str_replace($frasa, str_replace([' ', "'"], ['_', ''], $frasa), $teksKecil);
            }
            $kata = preg_split('/[^\p{L}\p{N}\-_]+/u', $teksKecil, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach (array_unique($kata) as $k) {
                $k = trim($k, '-_');
                if (mb_strlen($k) < 3 || is_numeric($k) || in_array($k, self::STOPWORD, true)) {
                    continue;
                }
                $hitung[$k] = ($hitung[$k] ?? 0) + 1;
            }
        }
        if ($hitung === []) {
            return [];
        }

        uksort($hitung, fn ($a, $b) => [$hitung[$b], $a] <=> [$hitung[$a], $b]);
        $hitung = array_slice($hitung, 0, $maks, true);
        $tertinggi = max($hitung);
        $terendah = min($hitung);

        $hasil = [];
        $i = 0;
        foreach ($hitung as $kata => $jumlah) {
            $rasio = $tertinggi === $terendah ? 1.0 : ($jumlah - $terendah) / ($tertinggi - $terendah);
            $hasil[] = [
                'kata' => str_replace('_', ' ', (string) $kata),
                'jumlah' => $jumlah,
                'ukuran' => round(1.0 + 2.6 * sqrt($rasio), 2), // rem: 1.0 – 3.6
                'warna' => self::WARNA[$i % count(self::WARNA)],
            ];
            $i++;
        }

        // Susun selang-seling kiri/kanan agar kata paling sering berada di tengah awan.
        $tengah = [];
        foreach ($hasil as $j => $item) {
            $j % 2 === 0 ? $tengah[] = $item : array_unshift($tengah, $item);
        }

        return $tengah;
    }
}
