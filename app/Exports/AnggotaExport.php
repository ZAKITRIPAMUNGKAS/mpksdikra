<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class AnggotaExport extends DefaultValueBinder implements FromView, ShouldAutoSize, WithCustomValueBinder
{
    use Exportable;

    public function __construct(
        private readonly Collection $anggotas,
        private readonly string $subTitle = 'PIMPINAN DAERAH MUHAMMADIYAH KABUPATEN KARANGANYAR',
        private readonly ?string $pcm = null
    ) {}

    public function view(): View
    {
        return view('exports.anggota', [
            'anggotas' => $this->anggotas,
            'subTitle' => $this->subTitle,
            'pcm' => $this->pcm,
            'totalKader' => $this->anggotas->count(),
            'totalAktif' => $this->anggotas->where('user.status', 'aktif')->count(),
            'totalPending' => $this->anggotas->where('user.status', 'menunggu_verifikasi')->count(),
            'totalLaki' => $this->anggotas->where('jenis_kelamin', 'L')->count(),
            'totalPerempuan' => $this->anggotas->where('jenis_kelamin', 'P')->count(),
        ]);
    }

    /**
     * Simpan sebagai teks (tanpa apostrof literal):
     * - nomor panjang / berawalan 0 (NIK, NBM, no WA) agar nol awal & digit tidak hilang;
     * - teks berawalan = + - @ agar tidak dieksekusi sebagai formula (formula injection).
     */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $teks = trim($value);
            $nomorPanjang = preg_match('/^\+?\d{6,}$/', $teks) === 1;
            $nolAwal = strlen($teks) > 1 && $teks[0] === '0' && ctype_digit($teks);
            $miripFormula = $teks !== '' && in_array($teks[0], ['=', '+', '-', '@'], true);

            if ($nomorPanjang || $nolAwal || $miripFormula) {
                $cell->setValueExplicit($teks, DataType::TYPE_STRING);

                return true;
            }
        }

        return parent::bindValue($cell, $value);
    }
}
