<?php

namespace App\Support;

/**
 * Perapian teks soal untuk lembar cetak (tanpa mengubah data di bank soal).
 */
class TeksSoal
{
    private const ARAB = '\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}';

    /** "23. Ayat manakah…" -> "Ayat manakah…" */
    public static function tanpaNomor(string $teks): string
    {
        return preg_replace('/^\s*\d{1,3}\s*[.)]\s+/u', '', $teks, 1) ?? $teks;
    }

    /** Pilihan "d. Tabligh" berlabel D -> "Tabligh" */
    public static function tanpaLabel(string $teks, string $label): string
    {
        return preg_replace('/^\s*'.preg_quote($label, '/').'\s*[.)]\s+/iu', '', $teks, 1) ?? $teks;
    }

    /**
     * Escape HTML lalu bungkus potongan teks Arab dengan <span class="ar"> agar
     * memakai font Arab dan arah kanan-ke-kiri di browser.
     */
    public static function html(string $teks): string
    {
        $aman = e($teks);
        $pola = '/['.self::ARAB.'](?:[\s\x{FD3E}\x{FD3F}'.self::ARAB.']*['.self::ARAB.'\x{FD3E}\x{FD3F}])?/u';

        $hasil = preg_replace_callback($pola, fn ($m) => '<span class="ar" dir="rtl" lang="ar">'.$m[0].'</span>', $aman);

        return nl2br($hasil ?? $aman);
    }
}
